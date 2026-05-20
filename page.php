<?php
/**
 * Generic page (e.g. Terms).
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
<div class="page-head">
	<div class="wrap">
		<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> / <?php the_title(); ?></p>
		<h1 class="page-title"><?php the_title(); ?></h1>
	</div>
</div>

<div class="content-grid content-grid--narrow">
	<article <?php post_class( 'entry' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="entry__cover"><?php the_post_thumbnail( 'the_alpha_hero' ); ?></figure>
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
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</article>
</div>
	<?php
endwhile;

get_footer();
