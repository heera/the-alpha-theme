<?php
/**
 * Search form.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="heera-s"><?php esc_html_e( 'Search for:', 'the-alpha' ); ?></label>
	<input type="search" id="heera-s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'the-alpha' ); ?>" required>
	<button type="submit" aria-label="<?php esc_attr_e( 'Submit search', 'the-alpha' ); ?>">
		<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" d="M4 12h15m0 0-6-6m6 6-6 6"/></svg>
	</button>
</form>
