<?php
/**
 * 404.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="content-grid content-grid--narrow">
	<div class="notice">
		<div class="code">404</div>
		<h1 class="section-heading"><?php esc_html_e( 'Lost in the noir', 'the-alpha' ); ?></h1>
		<p><?php esc_html_e( 'That page slipped through a null pointer. Let’s get you back.', 'the-alpha' ); ?></p>
		<?php get_search_form(); ?>
		<p style="margin-top:2rem;">
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home', 'the-alpha' ); ?></a>
		</p>
	</div>
</div>
<?php
get_footer();
