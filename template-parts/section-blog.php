<?php
/**
 * Section: Latest blog posts (3 most recent).
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ta_blog = get_page_by_path( 'blog' );
$ta_blog_url = $ta_blog ? get_permalink( $ta_blog ) : home_url( '/blog/' );

$ta_latest = new WP_Query( array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 3,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
?>
<section id="blog" class="section" aria-label="<?php esc_attr_e( 'Latest writing', 'the-alpha' ); ?>">
	<div class="wrap">
		<div class="reveal" style="margin-bottom:2.4rem;">
			<p class="eyebrow"><?php esc_html_e( 'Read', 'the-alpha' ); ?></p>
			<h2 class="section-heading"><?php esc_html_e( 'Latest from the blog', 'the-alpha' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Essays on software architecture, scalable systems, and the engineering decisions behind shipping production-grade products — with parallel reflections on fragrance, a long-standing craft pursued with the same rigour.', 'the-alpha' ); ?></p>
		</div>

		<?php if ( $ta_latest->have_posts() ) : ?>
			<div class="cards reveal">
				<?php
				while ( $ta_latest->have_posts() ) :
					$ta_latest->the_post();
					get_template_part( 'template-parts/card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<div class="section__cta">
				<a class="btn btn--ghost" href="<?php echo esc_url( $ta_blog_url ); ?>"><?php esc_html_e( 'View all posts', 'the-alpha' ); ?></a>
			</div>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'No posts published yet — check back soon.', 'the-alpha' ); ?></p>
		<?php endif; ?>
	</div>
</section>
