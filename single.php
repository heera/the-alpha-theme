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
			<a href="<?php echo esc_url( the_alpha_page_url( 'blog' ) ); ?>"><?php esc_html_e( 'Blog', 'the-alpha' ); ?></a>
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
				<?php
				// The cover is CONTENT, not decoration — a hard-coded alt="" here
				// discarded the attachment's stored alt text for every post. Use
				// the stored alt, and fall back to the post title so the banner
				// is never nameless. (The listing thumbnails in card.php and
				// post-row.php deliberately keep alt="": an image beside its own
				// title link is the a11y-recommended decorative pattern.)
				echo wp_get_attachment_image( $ta_cover_id, 'the_alpha_banner', false, array(
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'alt'           => ( trim( (string) get_post_meta( $ta_cover_id, '_wp_attachment_image_alt', true ) ) ?: get_the_title() ),
				) );
				?>
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

		<?php
		// "Continue reading" — three same-category posts, replacing the old
		// prev/next pair. A post's chronological neighbours are rarely its most
		// relevant follow-ups; see template-parts/related.php for the picking.
		get_template_part( 'template-parts/related' );
		?>

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
