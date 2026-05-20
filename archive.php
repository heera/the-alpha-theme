<?php
/**
 * Archive — category / tag / author / date.
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
		<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> / <?php esc_html_e( 'Archive', 'the-alpha' ); ?></p>
		<h1 class="page-title"><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
		<?php
		$ta_desc = get_the_archive_description();
		if ( $ta_desc ) {
			echo '<div class="lede" style="margin-top:1rem;">' . wp_kses_post( $ta_desc ) . '</div>';
		}
		?>
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
			<div class="notice"><p><?php esc_html_e( 'Nothing found in this archive.', 'the-alpha' ); ?></p></div>
		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
