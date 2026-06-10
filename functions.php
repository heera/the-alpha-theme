<?php
/**
 * The Alpha — theme bootstrap.
 *
 * No frameworks, no jQuery. One small stylesheet, one small deferred script.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'THE_ALPHA_VERSION', '1.0.0' );
define( 'THE_ALPHA_DIR', get_template_directory() );
define( 'THE_ALPHA_URI', get_template_directory_uri() );

/**
 * Cache-busting version based on file mtime so deploys never serve stale assets.
 */
function the_alpha_asset_ver( $relative_path ) {
	$file = THE_ALPHA_DIR . '/' . ltrim( $relative_path, '/' );
	return file_exists( $file ) ? (string) filemtime( $file ) : THE_ALPHA_VERSION;
}

/**
 * Resolve an asset to its minified twin (`foo.min.css`) when one exists and
 * SCRIPT_DEBUG is off, otherwise return the source path. The minified files are
 * produced by `npm run minify`; if they are missing the theme still works by
 * serving the unminified source. Returns a theme-relative path.
 */
function the_alpha_asset( $relative_path ) {
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		return $relative_path;
	}
	$min = preg_replace( '/\.(css|js)$/', '.min.$1', $relative_path );
	if ( $min !== $relative_path && file_exists( THE_ALPHA_DIR . '/' . ltrim( $min, '/' ) ) ) {
		return $min;
	}
	return $relative_path;
}

/**
 * Theme supports.
 */
function the_alpha_setup() {
	load_theme_textdomain( 'the-alpha', THE_ALPHA_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 80,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
		'navigation-widgets',
	) );

	// Block editor — restrict color picker to the theme palette. Frontend
	// CSS maps these slugs to CSS variables so dark/light themes resolve
	// correctly regardless of the static swatch value shown in the editor.
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => __( 'Ink',      'the-alpha' ), 'slug' => 'ink',      'color' => '#e8eaed' ),
		array( 'name' => __( 'Soft',     'the-alpha' ), 'slug' => 'soft',     'color' => '#aab2bd' ),
		array( 'name' => __( 'Accent',   'the-alpha' ), 'slug' => 'accent',   'color' => '#5ee6d0' ),
		array( 'name' => __( 'Accent 2', 'the-alpha' ), 'slug' => 'accent-2', 'color' => '#8b7cff' ),
		array( 'name' => __( 'Surface',  'the-alpha' ), 'slug' => 'surface',  'color' => '#14181d' ),
		array( 'name' => __( 'Bg',       'the-alpha' ), 'slug' => 'bg',       'color' => '#0b0d10' ),
	) );

	// Preset font sizes that match the frontend type scale.
	add_theme_support( 'editor-font-sizes', array(
		array( 'name' => __( 'Small',   'the-alpha' ), 'slug' => 'small',   'size' => 14 ),
		array( 'name' => __( 'Normal',  'the-alpha' ), 'slug' => 'normal',  'size' => 16 ),
		array( 'name' => __( 'Medium',  'the-alpha' ), 'slug' => 'medium',  'size' => 18 ),
		array( 'name' => __( 'Large',   'the-alpha' ), 'slug' => 'large',   'size' => 22 ),
		array( 'name' => __( 'X-Large', 'the-alpha' ), 'slug' => 'x-large', 'size' => 32 ),
	) );

	// Load a custom block-editor stylesheet so the visual editor previews
	// posts with the same typography and prose treatment as the frontend.
	add_editor_style( the_alpha_asset( 'assets/css/editor.css' ) );

	add_image_size( 'the_alpha_card', 760, 480, true );
	add_image_size( 'the_alpha_hero', 1600, 900, true );
	// Cover banner for the single-post header and listing rows (see
	// inc/banner-image.php). Uncropped + width-bounded so the post's wide Banner
	// image shows in full at its natural ratio — no slicing. Displayed via the
	// `.entry__cover` / `.post-row__media` rules in main.css.
	add_image_size( 'the_alpha_banner', 1600, 9999, false );

	register_nav_menus( array(
		'primary' => __( 'Primary (single-page sections)', 'the-alpha' ),
	) );
}
add_action( 'after_setup_theme', 'the_alpha_setup' );

/**
 * Content width for embeds.
 */
function the_alpha_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'the_alpha_content_width', 0 );

/**
 * Blog sidebar widget area (used on the blog/archive/single views).
 */
function the_alpha_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'the-alpha' ),
		'id'            => 'blog-sidebar',
		'description'   => __( 'Appears beside posts on the blog, archive and single views. Add Search, Categories, Recent Posts or any widget; remove all to hide the sidebar.', 'the-alpha' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget__title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'the_alpha_widgets_init' );

/**
 * Preload the two render-critical font faces (latin subset): the Fraunces
 * brand/heading face and the Inter body face. Fonts now ship from this origin
 * (see the_alpha_assets), so no font-CDN preconnect is needed any more.
 */
function the_alpha_preload_fonts( $resources ) {
	foreach ( array( 'fraunces-n-400-700-latin.woff2', 'inter-n-400-latin.woff2' ) as $face ) {
		$resources[] = array(
			'href'        => THE_ALPHA_URI . '/assets/fonts/' . $face,
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}
	return $resources;
}
add_filter( 'wp_preload_resources', 'the_alpha_preload_fonts' );

/**
 * Enqueue styles & scripts.
 */
function the_alpha_assets() {
	// Self-hosted fonts (assets/css/fonts.css → assets/fonts/*.woff2): subset to
	// latin + latin-ext, font-display: swap, no third-party request.
	$fonts = the_alpha_asset( 'assets/css/fonts.css' );
	wp_enqueue_style(
		'the-alpha-fonts',
		THE_ALPHA_URI . '/' . $fonts,
		array(),
		the_alpha_asset_ver( $fonts )
	);

	$main = the_alpha_asset( 'assets/css/main.css' );
	wp_enqueue_style(
		'the-alpha',
		THE_ALPHA_URI . '/' . $main,
		array( 'the-alpha-fonts' ),
		the_alpha_asset_ver( $main )
	);

	$theme_js = the_alpha_asset( 'assets/js/theme.js' );
	wp_enqueue_script(
		'the-alpha',
		THE_ALPHA_URI . '/' . $theme_js,
		array(),
		the_alpha_asset_ver( $theme_js ),
		true
	);
	wp_script_add_data( 'the-alpha', 'defer', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'the_alpha_assets' );

/**
 * Add defer to our script tag.
 */
function the_alpha_defer_script( $tag, $handle ) {
	if ( 'the-alpha' === $handle ) {
		return str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'the_alpha_defer_script', 10, 2 );

/**
 * Theme options exposed in the Customizer (Appearance → Customize →
 * "The Alpha — Theme Options").
 */
function the_alpha_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'the_alpha_options', array(
		'title'    => __( 'The Alpha — Theme Options', 'the-alpha' ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'the_alpha_default_scheme', array(
		'default'           => 'auto',
		'sanitize_callback' => 'the_alpha_sanitize_scheme',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'the_alpha_default_scheme', array(
		'label'       => __( 'Default colour scheme', 'the-alpha' ),
		'description' => __( 'Cold-start default for first-time visitors. Visitors can still toggle and their choice persists in their browser.', 'the-alpha' ),
		'section'     => 'the_alpha_options',
		'type'        => 'select',
		'choices'     => array(
			'auto'  => __( 'Auto (follow visitor\'s system preference)', 'the-alpha' ),
			'dark'  => __( 'Dark', 'the-alpha' ),
			'light' => __( 'Light', 'the-alpha' ),
		),
	) );

	$wp_customize->add_setting( 'the_alpha_mobile_light', array(
		'default'           => true,
		'sanitize_callback' => 'the_alpha_sanitize_bool',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'the_alpha_mobile_light', array(
		'label'       => __( 'Light theme by default on phones', 'the-alpha' ),
		'description' => __( 'First-time visitors on small screens (≤760px) land on the light theme regardless of the default above. Visitors can still toggle, and their choice persists.', 'the-alpha' ),
		'section'     => 'the_alpha_options',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'the_alpha_show_featured_listing', array(
		'default'           => true,
		'sanitize_callback' => 'the_alpha_sanitize_bool',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'the_alpha_show_featured_listing', array(
		'label'       => __( 'Featured image on blog listings', 'the-alpha' ),
		'description' => __( 'Banner thumbnails on the blog index, archives, and search results.', 'the-alpha' ),
		'section'     => 'the_alpha_options',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'the_alpha_show_featured_single', array(
		'default'           => true,
		'sanitize_callback' => 'the_alpha_sanitize_bool',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'the_alpha_show_featured_single', array(
		'label'       => __( 'Featured image on single posts', 'the-alpha' ),
		'description' => __( 'Hero cover image at the top of each single post.', 'the-alpha' ),
		'section'     => 'the_alpha_options',
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'the_alpha_fb_admins', array(
		'default'           => '',
		'sanitize_callback' => 'the_alpha_sanitize_fb_ids',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'the_alpha_fb_admins', array(
		'label'       => __( 'Facebook profile ID (fb:admins)', 'the-alpha' ),
		'description' => __( 'Your numeric Facebook profile ID — claims the site for Facebook Domain Insights. Comma-separate multiple IDs.', 'the-alpha' ),
		'section'     => 'the_alpha_options',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'the_alpha_fb_app_id', array(
		'default'           => '',
		'sanitize_callback' => 'the_alpha_sanitize_fb_ids',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'the_alpha_fb_app_id', array(
		'label'       => __( 'Facebook App ID (fb:app_id)', 'the-alpha' ),
		'description' => __( 'Optional Meta App ID. The only value that clears the Sharing Debugger\'s "fb:app_id" warning.', 'the-alpha' ),
		'section'     => 'the_alpha_options',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'the_alpha_home_description', array(
		'default'           => the_alpha_default_home_description(),
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'the_alpha_home_description', array(
		'label'       => __( 'Homepage description', 'the-alpha' ),
		'description' => __( 'Used as the homepage meta description and Open Graph / Twitter description. Keep it under ~160 characters.', 'the-alpha' ),
		'section'     => 'the_alpha_options',
		'type'        => 'textarea',
	) );
}
add_action( 'customize_register', 'the_alpha_customize_register' );

function the_alpha_sanitize_scheme( $value ) {
	$allowed = array( 'auto', 'dark', 'light' );
	return in_array( $value, $allowed, true ) ? $value : 'auto';
}

function the_alpha_sanitize_bool( $value ) {
	return (bool) $value;
}

/**
 * Sanitize a Facebook ID field: digits only, with commas allowed so
 * fb:admins can hold a comma-separated list of profile IDs.
 */
function the_alpha_sanitize_fb_ids( $value ) {
	return preg_replace( '/[^0-9,]/', '', (string) $value );
}

/**
 * Default homepage meta/Open Graph description — a brief line drawn from the
 * About section that conveys both the professional work and the fragrance
 * passion. Editable in the Customizer.
 */
function the_alpha_default_home_description() {
	return __( 'Programmer, system architect & CTO at authLab who builds scalable systems by day and layers oud, smoke & memory by night — architecture in both.', 'the-alpha' );
}

/**
 * Flicker-free theme: set the colour scheme on <html> before first paint.
 * Resolution order:
 *   1. localStorage (visitor's explicit choice from the toggle) — always wins
 *   2. Phones (viewport <= 760px) land on light by default, when the
 *      "Light theme by default on phones" Customizer option is enabled
 *   3. Otherwise the site default from Customizer (dark / light / auto)
 *   4. If "auto": OS preference via prefers-color-scheme
 *   5. Final fallback: dark
 * Printed as early as possible inside <head>.
 */
function the_alpha_no_fouc() {
	$default_scheme = the_alpha_sanitize_scheme( get_theme_mod( 'the_alpha_default_scheme', 'auto' ) );
	$mobile_light   = (bool) the_alpha_sanitize_bool( get_theme_mod( 'the_alpha_mobile_light', true ) );
	?>
<script>
(function(){var d=document.documentElement;d.classList.add('js');try{var s=localStorage.getItem('the-alpha-theme');if(!s){var mq=window.matchMedia;if(<?php echo $mobile_light ? 'true' : 'false'; ?>&&mq&&mq('(max-width: 760px)').matches){s='light';}else{var def=<?php echo wp_json_encode( $default_scheme ); ?>;s=(def==='auto')?(mq&&mq('(prefers-color-scheme: light)').matches?'light':'dark'):def;}}d.setAttribute('data-theme',s);}catch(e){d.setAttribute('data-theme','dark');}})();
</script>
	<?php
}
add_action( 'wp_head', 'the_alpha_no_fouc', 1 );

/**
 * Browser UI colour matches the active theme.
 */
function the_alpha_meta_tags() {
	echo '<meta name="theme-color" content="#0B0D10" media="(prefers-color-scheme: dark)">' . "\n";
	echo '<meta name="theme-color" content="#FAFAFA" media="(prefers-color-scheme: light)">' . "\n";
	echo '<meta name="color-scheme" content="dark light">' . "\n";
}
add_action( 'wp_head', 'the_alpha_meta_tags', 2 );

/**
 * Truncate text for meta description / OG description (single-line, ~160 chars).
 */
function the_alpha_seo_truncate( $text, $length = 160 ) {
	$text = wp_strip_all_tags( (string) $text );
	$text = preg_replace( '/\s+/', ' ', trim( $text ) );
	if ( mb_strlen( $text ) <= $length ) {
		return $text;
	}
	return rtrim( mb_substr( $text, 0, $length - 1 ) ) . '…';
}

/**
 * SEO meta: Open Graph + Twitter Cards + JSON-LD structured data.
 * Self-contained, no plugin required. Auto-deferred when Yoast SEO or
 * Rank Math is active (those plugins emit their own equivalent tags and
 * we don't want duplicates).
 */
function the_alpha_seo() {
	// Defer entirely if a dedicated SEO plugin is handling head output.
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath', false ) || defined( 'SEOPRESS_VERSION' ) ) {
		return;
	}

	$site_name    = get_bloginfo( 'name' );
	$site_tagline = get_bloginfo( 'description' );
	$site_url     = home_url( '/' );
	$locale       = str_replace( '-', '_', get_bloginfo( 'language' ) );

	// Context-aware title, URL, type, description, image. On the homepage the
	// tagline is carried by og:description (below), so og:title stays the bare
	// site name — appending the tagline here too made it appear twice in the
	// share card (once as the title, once as the description).
	$title = $site_name;
	$url   = $site_url;
	$type  = 'website';
	$desc  = $site_tagline;
	$img   = '';
	$img_w = 0;
	$img_h = 0;

	// Front-page check wins even when the front page is a static Page —
	// otherwise is_singular() would grab the bare page title.
	if ( is_front_page() ) {
		// Homepage title: "{name} — {tagline}". Not a duplicate of the
		// description any more, since the description below is the longer
		// About-derived line.
		if ( $site_tagline ) {
			$title = $site_name . ' — ' . $site_tagline;
		}
		// Homepage description: use the curated line (Customizer, defaulting to
		// the theme's About-derived line) instead of the bare tagline, so the
		// search/share snippet conveys who I am and what I do.
		$desc = (string) get_theme_mod( 'the_alpha_home_description', the_alpha_default_home_description() );
	} elseif ( is_singular() ) {
		$title = wp_strip_all_tags( get_the_title() );
		$url   = get_permalink();
		$type  = is_singular( 'post' ) ? 'article' : 'website';
		$desc  = has_excerpt()
			? wp_strip_all_tags( get_the_excerpt() )
			: wp_trim_words( wp_strip_all_tags( get_the_content() ), 32, '…' );

		if ( has_post_thumbnail() ) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'the_alpha_hero' );
			if ( $src ) {
				list( $img, $img_w, $img_h ) = $src;
			}
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$qo    = get_queried_object();
		$title = single_term_title( '', false );
		$url   = get_term_link( $qo );
		$desc  = wp_strip_all_tags( term_description() );
	} elseif ( is_author() ) {
		$qid   = get_queried_object_id();
		$title = get_the_author_meta( 'display_name', $qid );
		$url   = get_author_posts_url( $qid );
		$type  = 'profile';
		$desc  = get_the_author_meta( 'description', $qid );
		$img   = get_avatar_url( $qid, array( 'size' => 512 ) );
	} elseif ( is_search() ) {
		/* translators: %s: search query. */
		$title = sprintf( esc_html__( 'Search results for "%s"', 'the-alpha' ), get_search_query() );
		$url   = home_url( '/?s=' . rawurlencode( get_search_query() ) );
	}

	// Fall back to a brand image when context-resolution didn't yield one
	// (homepage, category/tag, search, etc.). A custom logo is only used if
	// it's wide enough to render as a social card — small/square logos and
	// site icons get upscaled into a blurry mess by Facebook's ~1080px-wide
	// preview, so for everything else we serve the dedicated 1200×630 card.
	if ( ! $img ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$src = wp_get_attachment_image_src( $logo_id, 'full' );
			if ( $src && (int) $src[1] >= 600 ) {
				list( $img, $img_w, $img_h ) = $src;
			}
		}
	}
	if ( ! $img ) {
		// Append the file's mtime as a version so a new card image busts
		// any CDN/Facebook cache without needing to rename the file.
		$og_path = get_template_directory() . '/assets/img/og-default.jpg';
		$og_ver  = file_exists( $og_path ) ? '?v=' . filemtime( $og_path ) : '';
		$img     = THE_ALPHA_URI . '/assets/img/og-default.jpg' . $og_ver;
		$img_w   = 1200;
		$img_h   = 630;
	}

	$desc  = the_alpha_seo_truncate( $desc ?: get_bloginfo( 'description' ) );
	$title = (string) $title;
	// JSON-LD wants raw text, not HTML entities, so keep a decoded copy.
	$title_text = html_entity_decode( $title, ENT_QUOTES, get_bloginfo( 'charset' ) );
	$desc_text  = html_entity_decode( $desc, ENT_QUOTES, get_bloginfo( 'charset' ) );

	// ---- Meta description -----------------------------------------------
	if ( $desc ) {
		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $desc ) );
	}

	// ---- Open Graph -----------------------------------------------------
	printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( $site_name ) );
	printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );
	printf( "<meta property=\"og:type\" content=\"%s\">\n", esc_attr( $type ) );
	printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $url ) );
	printf( "<meta property=\"og:locale\" content=\"%s\">\n", esc_attr( $locale ) );
	if ( $desc ) {
		printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $desc ) );
	}
	if ( $img ) {
		printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $img ) );
		printf( "<meta property=\"og:image:alt\" content=\"%s\">\n", esc_attr( $title ) );
		if ( $img_w && $img_h ) {
			printf( "<meta property=\"og:image:width\" content=\"%d\">\n", (int) $img_w );
			printf( "<meta property=\"og:image:height\" content=\"%d\">\n", (int) $img_h );
		}
	}

	// Facebook ownership: links the page to a Facebook profile/app so Domain
	// Insights work and the Sharing Debugger stops warning about a missing
	// fb:app_id. Resolution order: Customizer field (Appearance → Customize →
	// "The Alpha — Theme Options") → wp-config.php constant → filter.
	// fb:admins = your numeric FB profile ID (no app needed); fb:app_id =
	// a Meta App ID (the only value that silences the debugger's exact
	// "fb:app_id" warning text).
	$fb_admins = (string) get_theme_mod( 'the_alpha_fb_admins', '' );
	if ( '' === $fb_admins && defined( 'THE_ALPHA_FB_ADMINS' ) ) {
		$fb_admins = THE_ALPHA_FB_ADMINS;
	}
	$fb_admins = apply_filters( 'the_alpha_fb_admins', $fb_admins );
	if ( $fb_admins ) {
		printf( "<meta property=\"fb:admins\" content=\"%s\">\n", esc_attr( $fb_admins ) );
	}

	$fb_app_id = (string) get_theme_mod( 'the_alpha_fb_app_id', '' );
	if ( '' === $fb_app_id && defined( 'THE_ALPHA_FB_APP_ID' ) ) {
		$fb_app_id = THE_ALPHA_FB_APP_ID;
	}
	$fb_app_id = apply_filters( 'the_alpha_fb_app_id', $fb_app_id );
	if ( $fb_app_id ) {
		printf( "<meta property=\"fb:app_id\" content=\"%s\">\n", esc_attr( $fb_app_id ) );
	}

	// Article-specific OG (only for posts).
	if ( is_singular( 'post' ) ) {
		printf( "<meta property=\"article:published_time\" content=\"%s\">\n", esc_attr( get_the_date( DATE_W3C ) ) );
		printf( "<meta property=\"article:modified_time\" content=\"%s\">\n", esc_attr( get_the_modified_date( DATE_W3C ) ) );
		$author_name = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', get_the_ID() ) );
		if ( $author_name ) {
			printf( "<meta property=\"article:author\" content=\"%s\">\n", esc_attr( $author_name ) );
		}
		foreach ( (array) get_the_category() as $cat ) {
			printf( "<meta property=\"article:section\" content=\"%s\">\n", esc_attr( $cat->name ) );
		}
		foreach ( (array) get_the_tags() as $tag ) {
			$tag_name = trim( (string) $tag->name );
			if ( '' === $tag_name ) {
				continue;
			}
			printf( "<meta property=\"article:tag\" content=\"%s\">\n", esc_attr( $tag_name ) );
		}
	}

	// ---- Twitter Cards --------------------------------------------------
	printf( "<meta name=\"twitter:card\" content=\"%s\">\n", $img ? 'summary_large_image' : 'summary' );
	printf( "<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr( $title ) );
	if ( $desc ) {
		printf( "<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( $desc ) );
	}
	if ( $img ) {
		printf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_url( $img ) );
	}

	// ---- JSON-LD: WebSite (sitewide, enables SiteLinks SearchBox) ------
	$website_ld = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'name'            => $site_name,
		'alternateName'   => apply_filters( 'the_alpha_site_alt_names', array( 'Heera', 'Hira' ) ),
		'url'             => $site_url,
		'publisher'       => array( '@id' => $site_url . '#person' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => $site_url . '?s={search_term_string}',
			'query-input' => 'required name=search_term_string',
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $website_ld, JSON_UNESCAPED_SLASHES ) . "</script>\n";

	// ---- JSON-LD: Person (entity home — ties name variants together) ---
	// alternateName declares the spelling/transliteration variants (Heera /
	// Hira) and short forms as the same person, so Google can associate them
	// with this identity without any of them appearing in the visible page.
	// sameAs points at corroborating profiles (the strongest signal here),
	// reused from the theme's configured social links.
	$same_as = array();
	foreach ( the_alpha_socials() as $s ) {
		if ( ! empty( $s['url'] ) ) {
			$same_as[] = $s['url'];
		}
	}
	$person_id   = $site_url . '#person';
	$person_name = apply_filters( 'the_alpha_person_name', 'Sheikh Heera' );
	$person_ld   = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'Person',
		'@id'           => $person_id,
		'name'          => $person_name,
		'alternateName' => apply_filters( 'the_alpha_person_alt_names', array( 'Heera', 'Hira', 'Sheikh Hira', 'Heera Sheikh' ) ),
		'url'           => $site_url,
	);
	if ( $same_as ) {
		$person_ld['sameAs'] = array_values( array_unique( $same_as ) );
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $person_ld, JSON_UNESCAPED_SLASHES ) . "</script>\n";

	// ---- JSON-LD: BlogPosting + BreadcrumbList (single posts) ----------
	if ( is_singular( 'post' ) ) {
		$author_id   = (int) get_post_field( 'post_author', get_the_ID() );
		$author_name = get_the_author_meta( 'display_name', $author_id );
		$author_url  = get_author_posts_url( $author_id );

		// When the post is by the site's primary Person, reference that single
		// entity by @id so Google merges the post author with the sitewide
		// Person node (name, alternateName, sameAs) instead of treating them as
		// two separate people. Guest/co-authors keep an inline Person so we
		// never misattribute their work to the owner.
		if ( 0 === strcasecmp( trim( $author_name ), trim( $person_name ) ) ) {
			$author_node = array( '@id' => $person_id );
		} else {
			$author_node = array(
				'@type' => 'Person',
				'name'  => $author_name,
				'url'   => $author_url,
			);
		}

		$article_ld = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => $url ),
			'headline'         => $title_text,
			'description'      => $desc_text,
			'datePublished'    => get_the_date( DATE_W3C ),
			'dateModified'     => get_the_modified_date( DATE_W3C ),
			'author'           => $author_node,
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => $site_name,
				'url'   => $site_url,
			),
		);
		if ( $img ) {
			$article_ld['image'] = $img;
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $article_ld, JSON_UNESCAPED_SLASHES ) . "</script>\n";

		$crumbs = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => __( 'Home', 'the-alpha' ), 'item' => $site_url ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => __( 'Blog', 'the-alpha' ), 'item' => home_url( '/blog/' ) ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => $title_text, 'item' => $url ),
			),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $crumbs, JSON_UNESCAPED_SLASHES ) . "</script>\n";
	}
}
add_action( 'wp_head', 'the_alpha_seo', 5 );

/**
 * Performance: drop the emoji detection script/styles (not needed here).
 */
function the_alpha_trim_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
}
add_action( 'init', 'the_alpha_trim_head' );

/**
 * Cleaner excerpts.
 */
function the_alpha_excerpt_length() {
	// Auto-excerpt word count. Listing rows (post-row.php) cap to a matching
	// length; cards trim shorter (22) on their own.
	return 45;
}
add_filter( 'excerpt_length', 'the_alpha_excerpt_length' );

function the_alpha_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'the_alpha_excerpt_more' );

/**
 * Add a tidy class to the body so CSS can target the single-page front.
 */
function the_alpha_body_classes( $classes ) {
	if ( is_front_page() && ! is_paged() ) {
		$classes[] = 'is-onepage';
	}
	return $classes;
}
add_filter( 'body_class', 'the_alpha_body_classes' );

require THE_ALPHA_DIR . '/inc/template-tags.php';
require THE_ALPHA_DIR . '/inc/banner-image.php';
require THE_ALPHA_DIR . '/inc/agent-readiness.php';
