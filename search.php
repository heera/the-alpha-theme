<?php
/**
 * Search results.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="page-head">
	<div class="wrap">
		<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> / <?php esc_html_e( 'Search', 'the-alpha' ); ?></p>
		<h1 class="page-title">
			<?php
			/* translators: %s: search query. */
			printf( esc_html__( 'Results for &ldquo;%s&rdquo;', 'the-alpha' ), esc_html( get_search_query() ) );
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
				<p><?php esc_html_e( 'No matches. Try another phrase.', 'the-alpha' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
