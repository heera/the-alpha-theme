<?php
/**
 * Blog sidebar — fully driven by widgets registered to "blog-sidebar".
 * Configure via Appearance → Widgets → Blog Sidebar. If no widgets are
 * present, the aside is omitted entirely so the content column can use
 * the full grid width without leaving a reserved-but-empty rail.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'blog-sidebar' ) ) {
	return;
}
?>
<aside class="blog-aside" aria-label="<?php esc_attr_e( 'Blog sidebar', 'the-alpha' ); ?>">
	<?php dynamic_sidebar( 'blog-sidebar' ); ?>
</aside>
