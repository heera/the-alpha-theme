<?php
/**
 * Missed-schedule healer — publish an overdue scheduled post on arrival
 * instead of serving a 404.
 *
 * WP-Cron only runs when traffic happens to arrive, so a scheduled post whose
 * time has passed can sit in `future` status while its permalink 404s — worst
 * exactly when a launch link is being shared. This intercepts the would-be
 * 404: when the requested slug belongs to an overdue `future` post, the post
 * is published on the spot, the main query re-runs, and the visitor gets the
 * post. It complements real cron (which fixes punctuality); this rescues the
 * visitor who arrives in the gap.
 *
 * The trap this deliberately avoids: `pre_handle_404` fires at the TOP of
 * WP::handle_404(), BEFORE set_404() — so is_404() is ALWAYS false inside the
 * filter, and any `! is_404()` guard turns the whole handler into dead code.
 * The real "this is about to 404" signal is an empty main-query result set.
 *
 * Caveats honoured here:
 * - Cost: 404-scanner storms are cheap — a transient caches the next
 *   scheduled post's time, so when nothing is due the handler bails without
 *   touching the posts table.
 * - Scope: only the visited slug is healed, so spawn_cron() is pinged
 *   (non-blocking) to flush any other overdue events too.
 * - Coexistence: the $preempt guard plus the `future`-only lookup make a
 *   second healer (e.g. a future plugin version of this) a harmless no-op —
 *   whichever runs first publishes, the other finds nothing left to do.
 *
 * One caveat lives outside PHP: a full-page cache that stores 404 HTML
 * defeats this, because the request never reaches PHP. On Cloudflare, give
 * the caching rule a Status-code Edge TTL of "404 → No store".
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class The_Alpha_Missed_Schedule_Healer {

	/**
	 * Transient caching the GMT timestamp of the soonest scheduled post as a
	 * string ('0' = nothing scheduled), so a burst of 404s costs one cache
	 * read each instead of a posts-table query each.
	 */
	const NEXT_DUE_KEY = 'the_alpha_next_scheduled_gmt';

	/**
	 * Hook up the healer. Called once from functions.php.
	 */
	public static function boot() {
		add_filter( 'pre_handle_404', array( __CLASS__, 'heal_missed_schedule' ), 10, 2 );
		// Any schedule change (created, rescheduled, published — including by
		// this healer — or unscheduled) invalidates the next-due cache.
		add_action( 'transition_post_status', array( __CLASS__, 'flush_next_due' ), 10, 2 );
	}

	/**
	 * pre_handle_404 handler: publish the overdue scheduled post behind a
	 * would-be 404 and serve it.
	 *
	 * @param bool     $preempt  Whether another handler already claimed the request.
	 * @param WP_Query $wp_query The main query.
	 * @return bool True when the post was published and the re-run query found
	 *              it (WP then skips the 404 entirely); $preempt otherwise.
	 */
	public static function heal_missed_schedule( $preempt, $wp_query ) {
		if ( false !== $preempt ) {
			return $preempt;
		}

		// NOT is_404() — that's still false here (set_404() runs after this
		// filter). An empty result set is the real signal.
		if ( ! empty( $wp_query->posts ) ) {
			return $preempt;
		}

		$slug = self::requested_slug( $wp_query );
		if ( '' === $slug ) {
			return $preempt;
		}

		// Cheap short-circuit: no scheduled post is due, so this 404 can't be
		// a missed schedule — bail before the targeted query below.
		if ( ! self::schedule_is_overdue() ) {
			return $preempt;
		}

		$overdue = get_posts( array(
			'name'           => $slug,
			'post_type'      => 'any',
			'post_status'    => 'future',
			'posts_per_page' => 1,
			'date_query'     => array(
				array(
					'before'    => current_time( 'mysql' ),
					'inclusive' => true,
				),
			),
		) );
		if ( empty( $overdue ) ) {
			return $preempt;
		}

		wp_publish_post( $overdue[0] );

		// Non-blocking cron ping so any OTHER overdue events (more posts,
		// purges, emails) flush too — this handler only heals the visited slug.
		spawn_cron();

		// Re-run the main query against the now-published post; on success WP
		// serves it as a normal 200 single view.
		$wp_query->query( $wp_query->query_vars );

		return ! empty( $wp_query->posts );
	}

	/**
	 * The slug the request asked for, from the parsed query vars.
	 *
	 * @param WP_Query $wp_query The main query.
	 * @return string Slug, or '' when the request isn't slug-shaped (home,
	 *                archives, feeds — nothing a healer could publish).
	 */
	protected static function requested_slug( $wp_query ) {
		if ( ! empty( $wp_query->query_vars['name'] ) ) {
			return (string) $wp_query->query_vars['name'];
		}
		// Hierarchical pages arrive as a parent/child path; the leaf is the slug.
		if ( ! empty( $wp_query->query_vars['pagename'] ) ) {
			$parts = explode( '/', trim( (string) $wp_query->query_vars['pagename'], '/' ) );
			return (string) end( $parts );
		}
		return '';
	}

	/**
	 * Whether any scheduled post is past due, via the cached next-due time.
	 *
	 * @return bool
	 */
	protected static function schedule_is_overdue() {
		$next = get_transient( self::NEXT_DUE_KEY );
		if ( false === $next ) {
			$soonest = get_posts( array(
				'post_type'      => 'any',
				'post_status'    => 'future',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'ASC',
			) );
			// Stored as a string so '0' (nothing scheduled) survives object
			// caches that conflate falsy values with a cache miss.
			$next = $soonest ? (string) get_post_time( 'U', true, $soonest[0] ) : '0';
			set_transient( self::NEXT_DUE_KEY, $next, DAY_IN_SECONDS );
		}
		$next = (int) $next;
		return $next > 0 && $next <= time();
	}

	/**
	 * Drop the next-due cache whenever a post enters or leaves `future`.
	 *
	 * @param string $new_status New post status.
	 * @param string $old_status Old post status.
	 */
	public static function flush_next_due( $new_status, $old_status ) {
		if ( 'future' === $new_status || 'future' === $old_status ) {
			delete_transient( self::NEXT_DUE_KEY );
		}
	}
}

The_Alpha_Missed_Schedule_Healer::boot();
