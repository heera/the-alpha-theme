<?php
/**
 * Fragella integration — quota-safe fragrance journal.
 *
 * Design rule: Fragella's API is a *write-time* source, never a *read-time*
 * one. The secret key (FRAGELLA_API_KEY, defined in wp-config.php) is rationed
 * by a daily limit, so the public site must never trigger a live call. Instead:
 *
 *   1. You add a `fragrance` post and type the bottle's name.
 *   2. On save, the theme calls Fragella ONCE, normalises the record, and
 *      stores everything (accords, notes, season/occasion, images, rating) in
 *      post meta + taxonomy terms.
 *   3. Visitors read 100% from the local DB. Browsing, faceted accord/house
 *      archives, and "you may also like" similarity are all computed locally
 *      from the cached vectors — zero API calls at visitor time.
 *
 * Two guards keep you from ever blowing the quota by accident, even in admin:
 *   - every response is cached in a long-lived transient (repeat saves are free)
 *   - a per-day call counter refuses to call once the cap is reached
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Config
 * ---------------------------------------------------------------------- */

/**
 * The secret API key. Read from the wp-config.php constant so it never lives
 * in the theme or the database. Returns '' when unset.
 */
function the_alpha_fragella_key() {
	return defined( 'FRAGELLA_API_KEY' ) ? (string) FRAGELLA_API_KEY : '';
}

/**
 * API base. Filterable so a future provider switch / staging host is one line.
 */
function the_alpha_fragella_base() {
	return apply_filters( 'the_alpha_fragella_base', 'https://api.fragella.com/api/v1' );
}

/**
 * Self-imposed daily call cap. A backstop *below* Fragella's real daily limit
 * so a runaway save loop can never drain the day's quota. Override with the
 * FRAGELLA_DAILY_CAP constant or the filter.
 */
function the_alpha_fragella_daily_cap() {
	$cap = defined( 'FRAGELLA_DAILY_CAP' ) ? (int) FRAGELLA_DAILY_CAP : 100;
	return (int) apply_filters( 'the_alpha_fragella_daily_cap', $cap );
}

/**
 * How long a successful API response is cached. Fragrance records are
 * effectively static, so cache hard (30 days) — repeat enrichments of the
 * same bottle cost nothing.
 */
function the_alpha_fragella_cache_ttl() {
	return (int) apply_filters( 'the_alpha_fragella_cache_ttl', 30 * DAY_IN_SECONDS );
}

/* -------------------------------------------------------------------------
 * Daily budget tracking
 * ---------------------------------------------------------------------- */

/**
 * Option key for today's call counter (date-stamped in the site's timezone so
 * it naturally resets at local midnight).
 */
function the_alpha_fragella_counter_key() {
	return 'the_alpha_fragella_calls_' . wp_date( 'Y-m-d' );
}

/**
 * Calls made today.
 */
function the_alpha_fragella_calls_today() {
	return (int) get_option( the_alpha_fragella_counter_key(), 0 );
}

/**
 * Record one call against today's budget. Autoload off — these keys are
 * transient by nature and we sweep stale ones below.
 */
function the_alpha_fragella_bump_counter() {
	$key = the_alpha_fragella_counter_key();
	update_option( $key, (int) get_option( $key, 0 ) + 1, false );
}

/**
 * True when today's self-imposed cap is spent.
 */
function the_alpha_fragella_budget_exhausted() {
	return the_alpha_fragella_calls_today() >= the_alpha_fragella_daily_cap();
}

/* -------------------------------------------------------------------------
 * Core request (the ONLY place the secret key touches the network)
 * ---------------------------------------------------------------------- */

/**
 * GET a Fragella endpoint with caching + budget guard.
 *
 * @param string $path  Endpoint path, e.g. 'fragrances'.
 * @param array  $query Query args.
 * @return array|WP_Error Decoded JSON (array) or a WP_Error.
 */
function the_alpha_fragella_request( $path, array $query = array() ) {
	$key = the_alpha_fragella_key();
	if ( '' === $key ) {
		return new WP_Error( 'fragella_no_key', __( 'FRAGELLA_API_KEY is not defined in wp-config.php.', 'the-alpha' ) );
	}

	ksort( $query ); // stable cache key regardless of arg order.
	$url       = add_query_arg( array_map( 'rawurlencode', $query ), trailingslashit( the_alpha_fragella_base() ) . ltrim( $path, '/' ) );
	$cache_key = 'the_alpha_frag_' . md5( $path . '|' . wp_json_encode( $query ) );

	// Cache first — a hit costs no quota.
	$cached = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	// Budget guard — refuse rather than risk the daily limit.
	if ( the_alpha_fragella_budget_exhausted() ) {
		return new WP_Error(
			'fragella_budget',
			sprintf(
				/* translators: %d: daily call cap. */
				__( 'Fragella daily call budget (%d) reached. Try again after local midnight, or raise FRAGELLA_DAILY_CAP.', 'the-alpha' ),
				the_alpha_fragella_daily_cap()
			)
		);
	}

	$res = wp_remote_get( $url, array(
		'timeout' => 12,
		'headers' => array(
			'x-api-key' => $key,
			'accept'    => 'application/json',
		),
	) );
	the_alpha_fragella_bump_counter();

	if ( is_wp_error( $res ) ) {
		return $res;
	}

	$code = (int) wp_remote_retrieve_response_code( $res );
	$body = json_decode( wp_remote_retrieve_body( $res ), true );

	if ( 200 !== $code ) {
		$msg = is_array( $body ) && ! empty( $body['error'] ) ? $body['error'] : wp_remote_retrieve_response_message( $res );
		return new WP_Error( 'fragella_http_' . $code, sprintf( 'Fragella API %d: %s', $code, $msg ) );
	}
	if ( null === $body ) {
		return new WP_Error( 'fragella_decode', __( 'Fragella returned a non-JSON response.', 'the-alpha' ) );
	}

	set_transient( $cache_key, $body, the_alpha_fragella_cache_ttl() );
	return $body;
}

/**
 * Fuzzy-search fragrances by name; returns the array of matches (or WP_Error).
 *
 * @param string $name  Search term.
 * @param int    $limit Max results.
 */
function the_alpha_fragella_search( $name, $limit = 3 ) {
	$name = trim( (string) $name );
	if ( '' === $name ) {
		return new WP_Error( 'fragella_empty', __( 'Empty search term.', 'the-alpha' ) );
	}
	return the_alpha_fragella_request( 'fragrances', array(
		'search' => $name,
		'limit'  => max( 1, (int) $limit ),
	) );
}

/* -------------------------------------------------------------------------
 * Custom post type + taxonomies
 * ---------------------------------------------------------------------- */

/**
 * The `fragrance` CPT — one post per bottle in your collection. `has_archive`
 * gives a public /fragrances/ "scent board" with zero API calls.
 */
function the_alpha_register_fragrance_cpt() {
	register_post_type( 'fragrance', array(
		'labels' => array(
			'name'               => __( 'Fragrances', 'the-alpha' ),
			'singular_name'      => __( 'Fragrance', 'the-alpha' ),
			'add_new_item'       => __( 'Add Fragrance', 'the-alpha' ),
			'edit_item'          => __( 'Edit Fragrance', 'the-alpha' ),
			'search_items'       => __( 'Search Fragrances', 'the-alpha' ),
			'menu_name'          => __( 'Fragrances', 'the-alpha' ),
		),
		'public'       => true,
		'has_archive'  => 'fragrances',
		'menu_icon'    => 'dashicons-art',
		'menu_position'=> 5,
		'rewrite'      => array( 'slug' => 'fragrance', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest' => true,
	) );

	// Faceted public browse, built from cached data — no API at read time.
	register_taxonomy( 'fragrance_accord', 'fragrance', array(
		'labels'            => array(
			'name'          => __( 'Accords', 'the-alpha' ),
			'singular_name' => __( 'Accord', 'the-alpha' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'accord', 'with_front' => false ),
		'show_in_rest'      => true,
	) );

	register_taxonomy( 'fragrance_house', 'fragrance', array(
		'labels'            => array(
			'name'          => __( 'Houses', 'the-alpha' ),
			'singular_name' => __( 'House', 'the-alpha' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'house', 'with_front' => false ),
		'show_in_rest'      => true,
	) );
}
add_action( 'init', 'the_alpha_register_fragrance_cpt' );

/* -------------------------------------------------------------------------
 * Edit screen: name field + enrich-on-save
 * ---------------------------------------------------------------------- */

/**
 * Meta box: the Fragella source name + a "re-sync" toggle. Enrichment uses
 * this name (falling back to the post title) to find the bottle.
 */
function the_alpha_fragrance_metabox() {
	add_meta_box(
		'the_alpha_fragella',
		__( 'Fragella sync', 'the-alpha' ),
		'the_alpha_fragrance_metabox_render',
		'fragrance',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes_fragrance', 'the_alpha_fragrance_metabox' );

/**
 * Render the meta box.
 */
function the_alpha_fragrance_metabox_render( $post ) {
	wp_nonce_field( 'the_alpha_fragella_save', 'the_alpha_fragella_nonce' );
	$name   = (string) get_post_meta( $post->ID, '_fragella_query', true );
	$synced = (int) get_post_meta( $post->ID, '_fragella_synced', true );
	$status = (string) get_post_meta( $post->ID, '_fragella_status', true );
	?>
	<?php
	$pick_id = (string) get_post_meta( $post->ID, '_fragella_id', true );
	?>
	<p>
		<label for="the_alpha_fragella_query"><strong><?php esc_html_e( 'Fragrance name', 'the-alpha' ); ?></strong></label>
		<input type="text" id="the_alpha_fragella_query" name="the_alpha_fragella_query"
			value="<?php echo esc_attr( $name ); ?>" class="widefat"
			placeholder="<?php esc_attr_e( 'e.g. Ombre Nomade', 'the-alpha' ); ?>">
		<span class="description"><?php esc_html_e( 'Blank = use the post title. Saved once, then cached.', 'the-alpha' ); ?></span>
	</p>

	<p>
		<button type="button" class="button" id="the_alpha_fragella_search_btn">
			<?php esc_html_e( 'Search Fragella…', 'the-alpha' ); ?>
		</button>
		<span class="spinner" id="the_alpha_fragella_spinner" style="float:none;margin:0 0 0 4px;"></span>
	</p>
	<input type="hidden" name="the_alpha_fragella_pick_id" id="the_alpha_fragella_pick_id" value="">
	<div id="the_alpha_fragella_results" class="the-alpha-frag-results" aria-live="polite"></div>
	<?php if ( $pick_id ) : ?>
		<p class="description"><?php
			/* translators: %s: matched Fragella record id. */
			printf( esc_html__( 'Matched record: %s', 'the-alpha' ), '<code>' . esc_html( $pick_id ) . '</code>' );
		?></p>
	<?php endif; ?>

	<p>
		<label>
			<input type="checkbox" name="the_alpha_fragella_resync" value="1">
			<?php esc_html_e( 'Re-sync from Fragella on save', 'the-alpha' ); ?>
		</label>
	</p>
	<p class="description">
		<?php
		if ( $synced ) {
			printf(
				/* translators: 1: status, 2: human date. */
				esc_html__( 'Last sync: %1$s, %2$s ago.', 'the-alpha' ),
				esc_html( $status ?: 'ok' ),
				esc_html( human_time_diff( $synced ) )
			);
		} else {
			esc_html_e( 'Not yet synced.', 'the-alpha' );
		}
		?>
		<br>
		<?php
		printf(
			/* translators: 1: calls used, 2: daily cap. */
			esc_html__( 'API budget today: %1$d / %2$d.', 'the-alpha' ),
			(int) the_alpha_fragella_calls_today(),
			(int) the_alpha_fragella_daily_cap()
		);
		?>
	</p>
	<?php
}

/**
 * On save: enrich from Fragella exactly once per bottle (or when re-sync is
 * ticked / the source name changed). Stores normalised meta + assigns
 * accord/house terms. Sets the featured image only if you haven't set one.
 */
function the_alpha_fragrance_save( $post_id, $post ) {
	// Bail on the noise: autosave, revisions, wrong type, bad nonce, no caps.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'fragrance' !== $post->post_type || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['the_alpha_fragella_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['the_alpha_fragella_nonce'] ), 'the_alpha_fragella_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$query = isset( $_POST['the_alpha_fragella_query'] )
		? sanitize_text_field( wp_unslash( $_POST['the_alpha_fragella_query'] ) )
		: '';
	update_post_meta( $post_id, '_fragella_query', $query );

	$source   = $query !== '' ? $query : get_the_title( $post_id );
	$resync   = ! empty( $_POST['the_alpha_fragella_resync'] );
	$pick_id  = isset( $_POST['the_alpha_fragella_pick_id'] )
		? sanitize_text_field( wp_unslash( $_POST['the_alpha_fragella_pick_id'] ) )
		: '';
	$synced   = (int) get_post_meta( $post_id, '_fragella_synced', true );
	$prev_src = (string) get_post_meta( $post_id, '_fragella_source', true );

	// Only spend a call when there's a reason to: a fresh pick, a re-sync, a
	// changed name, or a never-synced post. An explicit pick always wins.
	if ( ! $pick_id && ! $resync && $synced && $prev_src === $source ) {
		return;
	}
	if ( '' === trim( $source ) ) {
		return;
	}

	// Searching for several candidates also primes the cache the admin search
	// already populated, so a pick re-uses that response for free.
	$results = the_alpha_fragella_search( $source, 10 );
	if ( is_wp_error( $results ) ) {
		update_post_meta( $post_id, '_fragella_status', 'error: ' . $results->get_error_message() );
		update_post_meta( $post_id, '_fragella_synced', time() );
		return;
	}

	$record = null;
	if ( is_array( $results ) ) {
		if ( $pick_id ) {
			// Honour the chosen candidate instead of the fuzzy top-1.
			foreach ( $results as $cand ) {
				if ( isset( $cand['_id'] ) && (string) $cand['_id'] === $pick_id ) {
					$record = $cand;
					break;
				}
			}
		}
		if ( ! $record && isset( $results[0] ) ) {
			$record = $results[0]; // fall back to best fuzzy match.
		}
	}

	if ( ! $record ) {
		update_post_meta( $post_id, '_fragella_status', 'no match for "' . $source . '"' );
		update_post_meta( $post_id, '_fragella_synced', time() );
		return;
	}

	// Persist the resolved id so it can re-key future syncs.
	if ( isset( $record['_id'] ) ) {
		update_post_meta( $post_id, '_fragella_id', sanitize_text_field( $record['_id'] ) );
	}

	the_alpha_fragella_store( $post_id, $record, $source );
}
add_action( 'save_post_fragrance', 'the_alpha_fragrance_save', 10, 2 );

/* -------------------------------------------------------------------------
 * Normalise + persist
 * ---------------------------------------------------------------------- */

/**
 * Map Fragella's verbose strength labels to a 0–1 weight so accords become a
 * comparable vector for local similarity.
 */
function the_alpha_fragella_accord_weight( $label ) {
	switch ( strtolower( (string) $label ) ) {
		case 'dominant':  return 1.0;
		case 'prominent': return 0.7;
		case 'moderate':  return 0.4;
		default:          return 0.25;
	}
}

/**
 * Reduce a raw Fragella record to the fields the theme renders + ranks on, and
 * write them to post meta. Also assigns accord/house terms and adopts the
 * transparent bottle image as the featured image when none is set.
 *
 * @param int    $post_id Post.
 * @param array  $r       Raw Fragella record.
 * @param string $source  The search term that produced it (change-detection).
 */
function the_alpha_fragella_store( $post_id, array $r, $source ) {
	// Accord vector: name => weight (0–1), from the percentage map when present.
	$accords = array();
	if ( ! empty( $r['Main Accords Percentage'] ) && is_array( $r['Main Accords Percentage'] ) ) {
		foreach ( $r['Main Accords Percentage'] as $name => $strength ) {
			$accords[ sanitize_text_field( $name ) ] = the_alpha_fragella_accord_weight( $strength );
		}
	} elseif ( ! empty( $r['Main Accords'] ) && is_array( $r['Main Accords'] ) ) {
		// Fall back to rank-based weights when no percentages are given.
		$n = count( $r['Main Accords'] );
		foreach ( array_values( $r['Main Accords'] ) as $i => $name ) {
			$accords[ sanitize_text_field( $name ) ] = round( 1 - ( $i / max( 1, $n ) ) * 0.75, 3 );
		}
	}

	$data = array(
		'id'        => isset( $r['_id'] ) ? sanitize_text_field( $r['_id'] ) : '',
		'name'      => isset( $r['Name'] ) ? sanitize_text_field( $r['Name'] ) : '',
		'brand'     => isset( $r['Brand'] ) ? sanitize_text_field( $r['Brand'] ) : '',
		'year'      => isset( $r['Year'] ) ? sanitize_text_field( $r['Year'] ) : '',
		'rating'    => isset( $r['rating'] ) ? (float) $r['rating'] : 0,
		'gender'    => isset( $r['Gender'] ) ? sanitize_text_field( $r['Gender'] ) : '',
		'oiltype'   => isset( $r['OilType'] ) ? sanitize_text_field( $r['OilType'] ) : '',
		'longevity' => isset( $r['Longevity'] ) ? sanitize_text_field( $r['Longevity'] ) : '',
		'sillage'   => isset( $r['Sillage'] ) ? sanitize_text_field( $r['Sillage'] ) : '',
		'image'     => isset( $r['Image URL Transparent'] ) ? esc_url_raw( $r['Image URL Transparent'] )
		               : ( isset( $r['Image URL'] ) ? esc_url_raw( $r['Image URL'] ) : '' ),
		'accords'   => $accords,
		'notes'     => isset( $r['Notes'] ) && is_array( $r['Notes'] ) ? $r['Notes'] : array(),
		'seasons'   => isset( $r['Season Ranking'] ) && is_array( $r['Season Ranking'] ) ? $r['Season Ranking'] : array(),
		'occasions' => isset( $r['Occasion Ranking'] ) && is_array( $r['Occasion Ranking'] ) ? $r['Occasion Ranking'] : array(),
		'purchase'  => isset( $r['Purchase URL'] ) ? esc_url_raw( $r['Purchase URL'] ) : '',
	);

	update_post_meta( $post_id, '_fragella_data', $data );          // the render bundle
	update_post_meta( $post_id, '_fragella_accords', $accords );    // the similarity vector
	update_post_meta( $post_id, '_fragella_source', $source );
	update_post_meta( $post_id, '_fragella_status', 'ok' );
	update_post_meta( $post_id, '_fragella_synced', time() );

	// Faceted taxonomies for public browse (dominant/prominent accords only,
	// so archives stay meaningful rather than every trace accord).
	$accord_terms = array();
	foreach ( $accords as $name => $w ) {
		if ( $w >= 0.7 ) {
			$accord_terms[] = $name;
		}
	}
	if ( $accord_terms ) {
		wp_set_object_terms( $post_id, $accord_terms, 'fragrance_accord', false );
	}
	if ( $data['brand'] ) {
		wp_set_object_terms( $post_id, $data['brand'], 'fragrance_house', false );
	}

	// Local-render image: sideload the transparent bottle once so visitors
	// never hit Fragella's CDN and you get a real attachment to style.
	if ( $data['image'] && ! has_post_thumbnail( $post_id ) ) {
		the_alpha_fragella_sideload_image( $post_id, $data['image'], $data['name'] );
	}

	// New data invalidates the cached similarity graph.
	delete_transient( 'the_alpha_frag_simgraph' );
}

/**
 * Sideload a bottle image and set it as the featured image.
 */
function the_alpha_fragella_sideload_image( $post_id, $url, $alt ) {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$att_id = media_sideload_image( $url, $post_id, $alt, 'id' );
	if ( ! is_wp_error( $att_id ) ) {
		set_post_thumbnail( $post_id, $att_id );
	}
}

/* -------------------------------------------------------------------------
 * Read accessors (used by templates — 100% local)
 * ---------------------------------------------------------------------- */

/**
 * The normalised render bundle for a fragrance, or null.
 */
function the_alpha_fragrance_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$data    = get_post_meta( $post_id, '_fragella_data', true );
	return is_array( $data ) ? $data : null;
}

/* -------------------------------------------------------------------------
 * Local recommendation engine (the public "you may also like")
 * ---------------------------------------------------------------------- */

/**
 * Cosine similarity between two accord vectors (name => weight maps).
 */
function the_alpha_fragella_cosine( array $a, array $b ) {
	if ( ! $a || ! $b ) {
		return 0.0;
	}
	$dot = 0.0;
	foreach ( $a as $k => $v ) {
		if ( isset( $b[ $k ] ) ) {
			$dot += $v * $b[ $k ];
		}
	}
	$na = sqrt( array_sum( array_map( function ( $v ) { return $v * $v; }, $a ) ) );
	$nb = sqrt( array_sum( array_map( function ( $v ) { return $v * $v; }, $b ) ) );
	return ( $na && $nb ) ? $dot / ( $na * $nb ) : 0.0;
}

/**
 * "You may also like" — the N most scent-similar fragrances in YOUR collection,
 * ranked by accord-vector cosine similarity. Pure local computation over cached
 * data: no Fragella call, works for unlimited visitors.
 *
 * @param int $post_id Reference fragrance.
 * @param int $limit   How many neighbours to return.
 * @return int[] Post IDs, most similar first.
 */
function the_alpha_fragrance_similar( $post_id = 0, $limit = 4 ) {
	$post_id = $post_id ?: get_the_ID();
	$base    = get_post_meta( $post_id, '_fragella_accords', true );
	if ( ! is_array( $base ) || ! $base ) {
		return array();
	}

	$ids = get_posts( array(
		'post_type'      => 'fragrance',
		'post_status'    => 'publish',
		'posts_per_page' => 200,
		'post__not_in'   => array( $post_id ),
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	$scored = array();
	foreach ( $ids as $id ) {
		$vec = get_post_meta( $id, '_fragella_accords', true );
		if ( is_array( $vec ) && $vec ) {
			$score = the_alpha_fragella_cosine( $base, $vec );
			if ( $score > 0 ) {
				$scored[ $id ] = $score;
			}
		}
	}
	arsort( $scored );
	return array_slice( array_keys( $scored ), 0, max( 1, (int) $limit ), true );
}

/* -------------------------------------------------------------------------
 * Admin notice when the budget is spent
 * ---------------------------------------------------------------------- */

/**
 * Warn on fragrance screens once the daily budget is exhausted, so a failed
 * enrichment is explained rather than silent.
 */
function the_alpha_fragella_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'fragrance' !== $screen->post_type ) {
		return;
	}
	if ( '' === the_alpha_fragella_key() ) {
		echo '<div class="notice notice-error"><p>' .
			esc_html__( 'FRAGELLA_API_KEY is not defined in wp-config.php — fragrances cannot be enriched.', 'the-alpha' ) .
			'</p></div>';
		return;
	}
	if ( the_alpha_fragella_budget_exhausted() ) {
		echo '<div class="notice notice-warning"><p>' .
			esc_html__( 'Fragella daily API budget reached. New enrichments will resume after local midnight.', 'the-alpha' ) .
			'</p></div>';
	}
}
add_action( 'admin_notices', 'the_alpha_fragella_admin_notice' );

/* -------------------------------------------------------------------------
 * "Pick the match" — admin AJAX search
 * ---------------------------------------------------------------------- */

/**
 * Load the meta-box search script on the fragrance edit screens only.
 */
function the_alpha_fragella_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'fragrance' !== $screen->post_type ) {
		return;
	}
	$rel = the_alpha_asset( 'assets/js/fragella-admin.js' );
	wp_enqueue_script(
		'the-alpha-fragella-admin',
		THE_ALPHA_URI . '/' . $rel,
		array(),
		the_alpha_asset_ver( $rel ),
		true
	);
	wp_localize_script( 'the-alpha-fragella-admin', 'TheAlphaFragella', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'the_alpha_fragella_search' ),
		'i18n'    => array(
			'searching' => __( 'Searching…', 'the-alpha' ),
			'none'      => __( 'No matches.', 'the-alpha' ),
			'empty'     => __( 'Type a name first.', 'the-alpha' ),
			'error'     => __( 'Search failed.', 'the-alpha' ),
			'pick'      => __( 'Use this', 'the-alpha' ),
			'picked'    => __( 'Selected — save the post to enrich.', 'the-alpha' ),
		),
	) );

	$css = '.the-alpha-frag-candidates{list-style:none;margin:.6rem 0 0;padding:0;display:grid;gap:.4rem}'
		. '.the-alpha-frag-candidates li{display:grid;grid-template-columns:34px 1fr auto;align-items:center;gap:.6rem;'
		. 'padding:.4rem;border:1px solid #dcdcde;border-radius:6px;background:#fff}'
		. '.the-alpha-frag-candidates li.is-picked{border-color:#2271b1;box-shadow:0 0 0 1px #2271b1}'
		. '.the-alpha-frag-candidates img{width:34px;height:42px;object-fit:contain}'
		. '.the-alpha-frag-candidates .cand-meta{display:flex;flex-direction:column;line-height:1.25;min-width:0}'
		. '.the-alpha-frag-candidates .cand-meta small{color:#646970}';
	wp_add_inline_style( 'common', $css );
}
add_action( 'admin_enqueue_scripts', 'the_alpha_fragella_admin_assets' );

/**
 * AJAX: return up to 8 Fragella candidates for a name, so the editor can pick
 * the exact bottle instead of auto-taking the fuzzy top-1. Caches the response
 * (shared with save-time enrichment), so the subsequent save costs no quota.
 */
function the_alpha_fragella_ajax_search() {
	check_ajax_referer( 'the_alpha_fragella_search', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'Not allowed.', 'the-alpha' ) ), 403 );
	}

	$q = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
	if ( '' === $q ) {
		wp_send_json_error( array( 'message' => __( 'Empty query.', 'the-alpha' ) ), 400 );
	}

	$results = the_alpha_fragella_search( $q, 8 );
	if ( is_wp_error( $results ) ) {
		wp_send_json_error( array( 'message' => $results->get_error_message() ), 400 );
	}

	$out = array();
	foreach ( (array) $results as $r ) {
		if ( ! is_array( $r ) ) {
			continue;
		}
		$out[] = array(
			'id'    => isset( $r['_id'] ) ? (string) $r['_id'] : '',
			'name'  => isset( $r['Name'] ) ? (string) $r['Name'] : '',
			'brand' => isset( $r['Brand'] ) ? (string) $r['Brand'] : '',
			'year'  => isset( $r['Year'] ) ? (string) $r['Year'] : '',
			'image' => isset( $r['Image URL Transparent'] ) ? (string) $r['Image URL Transparent']
			           : ( isset( $r['Image URL'] ) ? (string) $r['Image URL'] : '' ),
		);
	}
	wp_send_json_success( array( 'candidates' => $out ) );
}
add_action( 'wp_ajax_the_alpha_fragella_search', 'the_alpha_fragella_ajax_search' );
