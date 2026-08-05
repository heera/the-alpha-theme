<?php
/**
 * "Continue reading" — up to three more posts, shown under a single post.
 *
 * Replaces the old two-link prev/next nav. Picks the most recent posts sharing
 * a category with the current one, then tops the row up from the archive so the
 * section is either full or absent — a single orphan card reads like a bug
 * rather than a recommendation.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ta_rel_limit = 3;
$ta_rel_this  = (int) get_the_ID();
$ta_rel_ids   = array();

$ta_rel_cats = wp_get_post_categories( $ta_rel_this );
if ( $ta_rel_cats ) {
	$ta_rel_ids = get_posts( array(
		'fields'              => 'ids',
		'category__in'        => $ta_rel_cats,
		'post__not_in'        => array( $ta_rel_this ),
		'posts_per_page'      => $ta_rel_limit,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'suppress_filters'    => false,
	) );
}

// Top-up: a post filed in a thin category still deserves a complete row.
$ta_rel_short = $ta_rel_limit - count( $ta_rel_ids );
if ( $ta_rel_short > 0 ) {
	$ta_rel_ids = array_merge(
		$ta_rel_ids,
		get_posts( array(
			'fields'              => 'ids',
			'post__not_in'        => array_merge( array( $ta_rel_this ), $ta_rel_ids ),
			'posts_per_page'      => $ta_rel_short,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'suppress_filters'    => false,
		) )
	);
}

// Nothing else published yet — print no heading over an empty row.
if ( ! $ta_rel_ids ) {
	return;
}

$ta_rel_query = new WP_Query( array(
	'post__in'            => $ta_rel_ids,
	// Preserve the order built above: same-category matches first, then fillers.
	'orderby'             => 'post__in',
	'posts_per_page'      => $ta_rel_limit,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
?>
<section class="related" aria-labelledby="related-heading">
	<div class="related__head">
		<h2 class="related__title" id="related-heading"><?php esc_html_e( 'Continue reading', 'the-alpha' ); ?></h2>
		<span class="related__rule" aria-hidden="true"></span>
	</div>
	<p class="related__lede"><?php esc_html_e( 'More articles you might find useful.', 'the-alpha' ); ?></p>

	<div class="cards related__cards">
		<?php
		while ( $ta_rel_query->have_posts() ) :
			$ta_rel_query->the_post();
			get_template_part( 'template-parts/card', null, array( 'variant' => 'related' ) );
		endwhile;
		?>
	</div>
</section>
<?php
// Restore the global $post to the single post, so the comments template below
// (and anything else after this part) still reads the article being viewed.
wp_reset_postdata();
