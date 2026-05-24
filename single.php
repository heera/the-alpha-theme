<?php
/**
 * Single post.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$ta_cover_id  = the_alpha_cover_id();
	$ta_has_cover = $ta_cover_id && get_theme_mod( 'the_alpha_show_featured_single', true );
	?>
<div class="page-head">
	<div class="wrap">
		<p class="breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> /
			<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'the-alpha' ); ?></a>
		</p>
		<h1 class="page-title"><?php the_title(); ?></h1>
		<div style="margin-top:1rem;"><?php the_alpha_post_meta_single(); ?></div>
		<hr class="page-head__rule" aria-hidden="true">
	</div>
</div>

<div class="content-grid content-grid--tight">
	<article <?php post_class( 'entry' ); ?>>
		<?php if ( $ta_has_cover ) : ?>
			<figure class="entry__cover">
				<?php echo wp_get_attachment_image( $ta_cover_id, 'the_alpha_banner', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => '' ) ); ?>
			</figure>
		<?php endif; ?>

		<div class="prose entry-content">
			<?php
			the_content();
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'the-alpha' ) . ' ',
				'after'  => '</div>',
			) );
			?>
		</div>

		<?php
		$ta_tags = get_the_tags();
		if ( $ta_tags ) :
			?>
			<footer class="entry__foot">
				<?php
				foreach ( (array) $ta_tags as $t ) {
					printf(
						'<a class="tag-link" href="%s">#%s</a>',
						esc_url( get_tag_link( $t->term_id ) ),
						esc_html( $t->name )
					);
				}
				?>
			</footer>
		<?php endif; ?>

		<nav class="post-nav" aria-label="<?php esc_attr_e( 'Post navigation', 'the-alpha' ); ?>">
			<?php
			$ta_prev = get_previous_post();
			$ta_next = get_next_post();
			if ( $ta_prev ) {
				printf(
					'<a class="prev" href="%s"><small>%s</small>%s</a>',
					esc_url( get_permalink( $ta_prev ) ),
					esc_html__( 'Previous', 'the-alpha' ),
					esc_html( get_the_title( $ta_prev ) )
				);
			}
			if ( $ta_next ) {
				printf(
					'<a class="next" href="%s"><small>%s</small>%s</a>',
					esc_url( get_permalink( $ta_next ) ),
					esc_html__( 'Next', 'the-alpha' ),
					esc_html( get_the_title( $ta_next ) )
				);
			}
			?>
		</nav>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</article>

	<?php get_sidebar(); ?>
</div>
	<?php
endwhile;

get_footer();
