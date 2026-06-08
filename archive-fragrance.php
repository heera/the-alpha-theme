<?php
/**
 * Fragrance archive — the public "scent board". Also serves the accord and
 * house taxonomy archives (faceted browse). All local data; no Fragella calls.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$ta_is_tax = is_tax( 'fragrance_accord' ) || is_tax( 'fragrance_house' );
?>
<div class="page-head">
	<div class="wrap">
		<p class="breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> /
			<a href="<?php echo esc_url( get_post_type_archive_link( 'fragrance' ) ); ?>"><?php esc_html_e( 'Fragrances', 'the-alpha' ); ?></a>
		</p>
		<h1 class="page-title">
			<?php
			if ( $ta_is_tax ) {
				single_term_title();
			} else {
				esc_html_e( 'Fragrance Journal', 'the-alpha' );
			}
			?>
		</h1>
		<hr class="page-head__rule" aria-hidden="true">
	</div>
</div>

<div class="wrap">
	<?php if ( have_posts() ) : ?>
		<div class="cards cards--frag">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/fragrance-card' );
			endwhile;
			?>
		</div>

		<?php
		the_posts_pagination( array(
			'mid_size'  => 1,
			'prev_text' => __( 'Previous', 'the-alpha' ),
			'next_text' => __( 'Next', 'the-alpha' ),
		) );
		?>
	<?php else : ?>
		<p class="prose"><?php esc_html_e( 'No fragrances yet.', 'the-alpha' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
