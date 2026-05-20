<?php
/**
 * Fallback / blog index.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="page-head page-head--bare">
	<div class="wrap">
		<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> / <?php esc_html_e( 'Blog', 'the-alpha' ); ?></p>
		<h1 class="page-title screen-reader-text">
			<?php
			$ta_posts_page = (int) get_option( 'page_for_posts' );
			echo esc_html( $ta_posts_page ? get_the_title( $ta_posts_page ) : __( 'Blog', 'the-alpha' ) );
			?>
		</h1>
	</div>
</div>

<div class="content-grid">
	<div>
		<?php if ( have_posts() ) : ?>
			<div class="post-list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-row' );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination( array(
				'mid_size'  => 1,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'class'     => 'pagination',
			) );
			?>
		<?php else : ?>
			<div class="notice">
				<p><?php esc_html_e( 'Nothing here yet.', 'the-alpha' ); ?></p>
				<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home', 'the-alpha' ); ?></a>
			</div>
		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
