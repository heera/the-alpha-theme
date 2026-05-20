<?php
/**
 * Template Name: Blog
 *
 * Full, paginated list of posts. Assign this template to a page named "Blog"
 * (the front section's "View all posts" button links to /blog/).
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$ta_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$ta_q     = new WP_Query( array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => (int) get_option( 'posts_per_page' ),
	'paged'          => $ta_paged,
) );
?>
<div class="page-head page-head--bare">
	<div class="wrap">
		<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> / <?php esc_html_e( 'Blog', 'the-alpha' ); ?></p>
		<h1 class="page-title screen-reader-text"><?php the_title(); ?></h1>
		<?php if ( get_the_content() ) : ?>
			<div class="lede" style="margin-top:1rem;"><?php the_content(); ?></div>
		<?php endif; ?>
	</div>
</div>

<div class="content-grid">
	<div>
		<?php if ( $ta_q->have_posts() ) : ?>
			<div class="post-list">
				<?php
				while ( $ta_q->have_posts() ) :
					$ta_q->the_post();
					get_template_part( 'template-parts/post-row' );
				endwhile;
				?>
			</div>

			<?php
			$ta_links = paginate_links( array(
				'total'     => $ta_q->max_num_pages,
				'current'   => $ta_paged,
				'mid_size'  => 1,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
			) );
			if ( $ta_links ) {
				echo '<nav class="pagination" aria-label="' . esc_attr__( 'Posts', 'the-alpha' ) . '">' . wp_kses_post( $ta_links ) . '</nav>';
			}
			wp_reset_postdata();
			?>
		<?php else : ?>
			<div class="notice"><p><?php esc_html_e( 'No posts found.', 'the-alpha' ); ?></p></div>
		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
