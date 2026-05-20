<?php
/**
 * Front page — the single-page experience (Home / About / Blog / Contact).
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/section', 'hero' );
get_template_part( 'template-parts/section', 'about' );
get_template_part( 'template-parts/section', 'blog' );
get_template_part( 'template-parts/section', 'contact' );

get_footer();
