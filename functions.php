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

define( 'THE_ALPHA_VERSION', '1.2.12' );
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
 * Lazy-load the Disqus comment thread.
 *
 * The official Disqus plugin enqueues comment_embed.js (handle `disqus_embed`),
 * which injects the full comment iframe on page load — dozens of third-party
 * requests (disquscdn.com fonts, recommendations) that keep the tab spinner
 * running for seconds even though our HTML has already painted. We neutralise
 * the auto-loading <script> tag (src → data-disqus-src, type → text/plain so
 * the browser neither runs nor fetches it) and let the footer loader pull it in
 * only when #disqus_thread nears the viewport. Readers who never scroll to the
 * comments never pay for Disqus at all. The inline `disqus_embed-js-extra`
 * config still runs normally, so the globals are ready when the script loads.
 */
function the_alpha_lazy_disqus_tag( $tag, $handle ) {
	if ( 'disqus_embed' !== $handle || is_admin() ) {
		return $tag;
	}
	$tag = preg_replace( '/\ssrc=/', ' data-disqus-src=', $tag, 1 );
	$tag = preg_replace( '/<script /', '<script type="text/plain" data-disqus-embed ', $tag, 1 );
	return $tag;
}
add_filter( 'script_loader_tag', 'the_alpha_lazy_disqus_tag', 10, 2 );

/**
 * Footer loader that swaps the neutralised Disqus tag back to a real <script>
 * once the comments scroll near the viewport (600px head-start so it feels
 * instant). Falls back to an immediate load where IntersectionObserver is
 * unavailable.
 */
function the_alpha_lazy_disqus_loader() {
	if ( ! is_singular() ) {
		return;
	}
	?>
<script>
(function () {
	var thread = document.getElementById('disqus_thread');
	var tag = document.querySelector('script[data-disqus-embed]');
	if (!thread || !tag) { return; }
	var done = false;
	function load() {
		if (done) { return; }
		done = true;
		var s = document.createElement('script');
		s.src = tag.getAttribute('data-disqus-src');
		s.async = true;
		document.body.appendChild(s);
	}
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			if (entries[0].isIntersecting) { io.disconnect(); load(); }
		}, { rootMargin: '600px 0px' });
		io.observe(thread);
	} else {
		load();
	}
})();
</script>
	<?php
}
add_action( 'wp_footer', 'the_alpha_lazy_disqus_loader', 99 );

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
		'description' => __( 'Cold-start default for first-time visitors. The sidebar control cycles System / Light / Dark: System follows the visitor\'s device (including its day/night switching), an explicit Light or Dark pick persists in their browser.', 'the-alpha' ),
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

	$wp_customize->add_setting( 'the_alpha_og_default_image', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'the_alpha_og_default_image', array(
		'label'       => __( 'Default share image', 'the-alpha' ),
		'description' => __( 'Set an image and the theme renders the social share cards, using it when a page has no image of its own (recommended 1200×630). Leave empty and the theme prints no share-card tags at all, so a plugin (e.g. Agentimus) can provide them.', 'the-alpha' ),
		'section'     => 'the_alpha_options',
		'mime_type'   => 'image',
	) ) );

	$wp_customize->add_setting( 'the_alpha_author_card_photo', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'the_alpha_author_card_photo', array(
		'label'       => __( 'Author card photo', 'the-alpha' ),
		'description' => __( 'Portrait on the author archive card. Leave empty to reuse the sidebar portrait.', 'the-alpha' ),
		'section'     => 'the_alpha_options',
		'mime_type'   => 'image',
	) ) );

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
 * Two attributes come out of this:
 *   data-theme      — the resolved palette, 'light' | 'dark'; ALL theme CSS
 *                     keys off it, exactly as before.
 *   data-theme-mode — the visitor's selection, 'system' | 'light' | 'dark';
 *                     only the tri-state control's icon/label keys off it.
 * Resolution:
 *   1. localStorage 'the-alpha-theme': 'light' / 'dark' pin that palette;
 *      'system' follows the OS (and keeps following it live — theme.js
 *      listens for prefers-color-scheme flips in system mode only). Visitors
 *      from the old two-state toggle stored the same 'light'/'dark' strings,
 *      so their pick carries over as a pinned mode unchanged.
 *   2. Nothing stored (cold start): phones (viewport <= 760px) land on light
 *      when the "Light theme by default on phones" Customizer option is on;
 *      otherwise the Customizer default (dark / light / auto — auto shows as
 *      "system"). The cold-start mode is displayed but NOT persisted; only a
 *      click on the control writes the key.
 *   3. Final fallback: dark.
 * Printed as early as possible inside <head>.
 */
function the_alpha_no_fouc() {
	$default_scheme = the_alpha_sanitize_scheme( get_theme_mod( 'the_alpha_default_scheme', 'auto' ) );
	$mobile_light   = (bool) the_alpha_sanitize_bool( get_theme_mod( 'the_alpha_mobile_light', true ) );
	?>
<script>
(function(){var d=document.documentElement;d.classList.add('js');try{var mq=window.matchMedia;var os=mq&&mq('(prefers-color-scheme: light)').matches?'light':'dark';var m=localStorage.getItem('the-alpha-theme');if(m!=='light'&&m!=='dark'&&m!=='system'){if(<?php echo $mobile_light ? 'true' : 'false'; ?>&&mq&&mq('(max-width: 760px)').matches){m='light';}else{var def=<?php echo wp_json_encode( $default_scheme ); ?>;m=(def==='auto')?'system':def;}}d.setAttribute('data-theme-mode',m);d.setAttribute('data-theme',m==='system'?os:m);}catch(e){d.setAttribute('data-theme-mode','dark');d.setAttribute('data-theme','dark');}})();
</script>
	<?php
}
add_action( 'wp_head', 'the_alpha_no_fouc', 1 );

/**
 * Preload the 404 backdrop photo.
 *
 * `.lost__bg` paints its photo from CSS, so the browser cannot discover the URL
 * until main.css has been fetched, parsed and matched — the photo then lands
 * after first paint and visibly pops in over the gradient fallback underneath
 * it. A plain <link rel="preload"> in the markup cannot fix that on its own:
 * which of the two photos is needed depends on `data-theme`, and printing both
 * would waste a whole unused image on every visit.
 *
 * So the job goes to a second inline script. It runs at wp_head priority 2 —
 * after the no-FOUC script above has set the attribute, and still before
 * wp_print_styles (8) has emitted the stylesheet <link> at all — reads the
 * theme that was just resolved, and injects the one matching preload. The
 * download starts at the top of <head> instead of a CSS round-trip later.
 *
 * The href must match the CSS `url()` exactly, with no ?ver appended: a
 * different URL is a different resource to the browser, and the photo would be
 * downloaded twice instead of once.
 */
function the_alpha_preload_lost_backdrop() {
	if ( ! is_404() ) {
		return;
	}
	$base = THE_ALPHA_URI . '/assets/img/404-';
	?>
<script>
(function(){try{var t=document.documentElement.getAttribute('data-theme');if(t!=='dark'&&t!=='light'){return;}var l=document.createElement('link');l.rel='preload';l.as='image';l.type='image/webp';l.href=<?php echo wp_json_encode( $base ); ?>+t+'.webp';l.setAttribute('fetchpriority','high');document.head.appendChild(l);}catch(e){}})();
</script>
	<?php
}
add_action( 'wp_head', 'the_alpha_preload_lost_backdrop', 2 );

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
	} elseif ( is_home() ) {
		// The posts page, when it lives apart from the front page. Without this
		// branch $url stays the homepage address, and the canonical below would
		// claim the blog index is a duplicate of the front page.
		$blog_id = (int) get_option( 'page_for_posts' );
		if ( $blog_id ) {
			$title = wp_strip_all_tags( get_the_title( $blog_id ) );
			$url   = get_permalink( $blog_id );
			$desc  = has_excerpt( $blog_id ) ? wp_strip_all_tags( get_the_excerpt( $blog_id ) ) : '';
		}
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
		if ( ! $desc ) {
			// No hand-written description on this term (wp-admin → the term's
			// Description field wins when set): build an honest one, so term
			// archives don't all fall back to the site tagline — search
			// engines flag pages that share one meta description.
			//
			// Tags say "tagged", categories say "about". Several topics here
			// exist as both a category and a tag under the same name (AI, PHP,
			// WordPress…), and one shared sentence made those two archives
			// exact duplicates of each other — which is the one case where a
			// generated description is worse than none at all.
			$desc = is_tag()
				/* translators: 1: tag name, 2: site name. */
				? sprintf( __( 'Posts tagged %1$s on %2$s.', 'the-alpha' ), $title, $site_name )
				/* translators: 1: topic name, 2: site name. */
				: sprintf( __( 'Posts about %1$s on %2$s.', 'the-alpha' ), $title, $site_name );
		}

		// Same disambiguation the <title> gets — see the_alpha_tag_document_title().
		// Applied after the description above, which wants the bare term name.
		if ( is_tag() ) {
			/* translators: %s: tag name. */
			$title = sprintf( __( 'Tagged: %s', 'the-alpha' ), $title );
		}
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

	// The Customizer's "Default share image" is the master switch for the
	// theme's share-card tags. Set: the theme prints the og:/fb:/twitter: card
	// block, with this image standing in wherever a page has no image of its
	// own. Empty: the theme prints NO card tags at all, leaving the whole card
	// to whoever fills the gap (Agentimus's Social share cards, which stand
	// down whenever any og: tag is already present). Canonical, the meta
	// description and JSON-LD are not card tags — they print either way.
	$default_id  = (int) get_theme_mod( 'the_alpha_og_default_image', 0 );
	$default_src = $default_id ? wp_get_attachment_image_src( $default_id, 'full' ) : false;
	$theme_cards = false !== $default_src;
	if ( $theme_cards && ! $img ) {
		list( $img, $img_w, $img_h ) = $default_src;
	}

	$desc = the_alpha_seo_truncate( $desc ?: $site_tagline );

	// Paginated views (an archive's /page/2/, a multi-page post's second page):
	// name the page number, so no two pages of one view share a description.
	// Appended after truncation so the suffix always survives.
	$the_alpha_pg = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	if ( $the_alpha_pg > 1 && $desc ) {
		/* translators: %d: page number. */
		$desc .= ' ' . sprintf( __( 'Page %d.', 'the-alpha' ), $the_alpha_pg );
	}

	$title = (string) $title;
	// JSON-LD wants raw text, not HTML entities, so keep a decoded copy.
	$title_text = html_entity_decode( $title, ENT_QUOTES, get_bloginfo( 'charset' ) );
	$desc_text  = html_entity_decode( $desc, ENT_QUOTES, get_bloginfo( 'charset' ) );

	// ---- Canonical ------------------------------------------------------
	// WordPress core emits rel=canonical on singular views only (rel_canonical),
	// so the homepage and archives have none — which AI/search crawlers flag as
	// an ambiguous-duplicate risk. Fill that gap here; leave singular to core so
	// the tag is never emitted twice. Page 2+ of an archive gets NO canonical:
	// $url here is always the page-1 address, and pointing page 2 at page 1
	// tells crawlers the page is a duplicate — no tag is the honest choice.
	if ( $url && ! is_singular() && ! is_paged() ) {
		printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $url ) );
	}

	// ---- Meta description -----------------------------------------------
	if ( $desc ) {
		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $desc ) );
	}

	if ( $theme_cards ) {
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
			// get_the_tags() returns false (or WP_Error) when a post has no tags,
			// and `(array) false` is array( false ) — a lone non-object element
			// whose ->name read triggers a notice. Only loop over a real array.
			$post_tags = get_the_tags();
			foreach ( is_array( $post_tags ) ? $post_tags : array() as $tag ) {
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
	}

	// ---- One voice for structured data ---------------------------------
	// When Agentimus is active, its Schema module is the site's single
	// JSON-LD emitter (it defers to SEO suites on its own). Two WebSites,
	// two Persons and two BlogPostings on one page read as noise, not
	// corroboration — so the theme stands its four blocks down and pours
	// its unique entity knowledge (name variants, employer, socials) into
	// the plugin's graph instead ({@see the_alpha_enrich_agentimus_graph}).
	// The meta/OG/Twitter tags above stay the theme's. With the plugin
	// gone, the blocks below return untouched.
	if ( apply_filters( 'the_alpha_defer_schema', class_exists( '\\Agentimus\\Schema' ) ) ) {
		return;
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
		'jobTitle'      => apply_filters( 'the_alpha_person_job_title', 'CTO' ),
	);
	$works_for = apply_filters( 'the_alpha_person_works_for', 'Authlab' );
	if ( $works_for ) {
		$person_ld['worksFor'] = array(
			'@type' => 'Organization',
			'name'  => $works_for,
		);
	}
	if ( $same_as ) {
		$person_ld['sameAs'] = array_values( array_unique( $same_as ) );
	}
	// knowsAbout — declares the topics this person is authoritative on, derived
	// from the heaviest content categories (same source as the llms.txt About
	// block) so the expertise graph stays accurate as the blog grows.
	if ( function_exists( 'the_alpha_ar_expertise' ) ) {
		$knows_about = apply_filters( 'the_alpha_person_knows_about', the_alpha_ar_expertise() );
		if ( ! empty( $knows_about ) ) {
			$person_ld['knowsAbout'] = array_values( $knows_about );
		}
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
				array( '@type' => 'ListItem', 'position' => 2, 'name' => __( 'Blog', 'the-alpha' ), 'item' => the_alpha_page_url( 'blog' ) ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => $title_text, 'item' => $url ),
			),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $crumbs, JSON_UNESCAPED_SLASHES ) . "</script>\n";
	}
}
add_action( 'wp_head', 'the_alpha_seo', 5 );

/**
 * The theme's entity knowledge, poured into Agentimus's JSON-LD graph — the
 * other half of the stand-down above. The plugin knows the site's settings;
 * the theme knows the person: the spelling/transliteration variants (Heera /
 * Hira / Sheikh Hira / Heera Sheikh) that tie search queries to one identity,
 * the employer, and the social profiles. Scalar knowledge only fills gaps —
 * the plugin's own settings stay authoritative — while sameAs is a union,
 * because every extra corroborating profile is strictly more proof.
 *
 * @param array $graph The plugin's assembled @graph nodes.
 * @return array
 */
function the_alpha_enrich_agentimus_graph( $graph ) {
	if ( ! is_array( $graph ) ) {
		return $graph;
	}

	foreach ( $graph as $i => $node ) {
		if ( ! is_array( $node ) || empty( $node['@type'] ) ) {
			continue;
		}

		if ( 'WebSite' === $node['@type'] && empty( $node['alternateName'] ) ) {
			$graph[ $i ]['alternateName'] = apply_filters( 'the_alpha_site_alt_names', array( 'Heera', 'Hira' ) );
		}

		// The site identity only (…#identity) — a guest author's inline Person
		// node must never inherit the owner's name variants or employer.
		if ( 'Person' === $node['@type'] && false !== strpos( (string) ( isset( $node['@id'] ) ? $node['@id'] : '' ), '#identity' ) ) {
			if ( empty( $node['alternateName'] ) ) {
				$graph[ $i ]['alternateName'] = apply_filters( 'the_alpha_person_alt_names', array( 'Heera', 'Hira', 'Sheikh Hira', 'Heera Sheikh' ) );
			}

			$works_for = apply_filters( 'the_alpha_person_works_for', 'Authlab' );
			if ( $works_for && empty( $node['worksFor'] ) ) {
				$graph[ $i ]['worksFor'] = array(
					'@type' => 'Organization',
					'name'  => $works_for,
				);
			}

			$same_as = array();
			foreach ( the_alpha_socials() as $s ) {
				if ( ! empty( $s['url'] ) ) {
					$same_as[] = $s['url'];
				}
			}
			if ( $same_as ) {
				$graph[ $i ]['sameAs'] = array_values( array_unique( array_merge(
					isset( $node['sameAs'] ) ? (array) $node['sameAs'] : array(),
					$same_as
				) ) );
			}
		}
	}

	return $graph;
}
add_filter( 'agentimus_schema_graph', 'the_alpha_enrich_agentimus_graph' );

/**
 * Core canonicalises every page of a paginated Page template (the Blog page's
 * /page/2/, /page/3/…) back to page 1 — wp_get_canonical_url() only knows
 * about <!--nextpage--> pagination, not a template's own paged loop. Pointing
 * page 2 at page 1 tells crawlers the page is a duplicate; a canonical there
 * must be self-referential or absent, and absent is the honest choice.
 * Returning false makes rel_canonical print nothing.
 *
 * @param string $canonical_url The canonical URL core resolved.
 * @return string|false
 */
function the_alpha_canonical_not_on_paged( $canonical_url ) {
	return is_paged() ? false : $canonical_url;
}
add_filter( 'get_canonical_url', 'the_alpha_canonical_not_on_paged' );

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
 * Editor: a per-image switch out of the dark-mode image filter. A toggle
 * (not a block style — styles are mutually exclusive and would knock out
 * Rounded) that simply adds/removes the `no-invert` class the dark-theme
 * CSS already honours on the figure. No new attribute, so saved markup
 * stays plain core/image.
 */
function the_alpha_editor_assets() {
	wp_register_script(
		'the-alpha-editor',
		false,
		array( 'wp-hooks', 'wp-compose', 'wp-element', 'wp-block-editor', 'wp-components' ),
		THE_ALPHA_VERSION,
		true
	);
	wp_enqueue_script( 'the-alpha-editor' );
	wp_add_inline_script(
		'the-alpha-editor',
		<<<'JS'
(function () {
	if (!window.wp || !wp.hooks || !wp.compose || !wp.element || !wp.blockEditor || !wp.components) { return; }
	var el = wp.element.createElement;
	var CLASS = 'no-invert';
	var classes = function (cn) { return (cn || '').split(/\s+/).filter(Boolean); };
	wp.hooks.addFilter('editor.BlockEdit', 'the-alpha/no-invert',
		wp.compose.createHigherOrderComponent(function (BlockEdit) {
			return function (props) {
				if ('core/image' !== props.name) { return el(BlockEdit, props); }
				var list = classes(props.attributes.className);
				var on = -1 !== list.indexOf(CLASS);
				return el(wp.element.Fragment, null,
					el(BlockEdit, props),
					el(wp.blockEditor.InspectorControls, null,
						el(wp.components.PanelBody, { title: 'Dark mode', initialOpen: true },
							el(wp.components.ToggleControl, {
								label: 'Keep original colors',
								help: 'The dark theme dims bright images. This one stays exactly as uploaded.',
								checked: on,
								onChange: function (v) {
									var next = list.filter(function (c) { return c !== CLASS; });
									if (v) { next.push(CLASS); }
									props.setAttributes({ className: next.join(' ') || undefined });
								}
							})
						)
					)
				);
			};
		}, 'withNoInvert')
	);
})();
JS
	);
}
add_action( 'enqueue_block_editor_assets', 'the_alpha_editor_assets' );

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

/**
 * Send a retired tag archive to the category that replaced it.
 *
 * Tags that merely repeated a category's name have been deleted, but search
 * engines and old links still ask for /tag/php. Left alone that is a 404 for a
 * topic the site very much still covers, so the request goes to the category
 * of the same name instead — the page it would have wanted.
 *
 * Only ever fires on a request that is already a 404 and only when a matching
 * category exists, so it cannot shadow a live tag. The numeric-suffix retry
 * catches the tags WordPress had renamed to avoid a slug clash with their own
 * category (/tag/javascript-2 → /category/javascript).
 */
function the_alpha_retired_tag_redirects() {
	if ( is_admin() || ! is_404() ) {
		return;
	}

	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$path = trim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' );
	$base = trim( (string) get_option( 'tag_base' ), '/' );
	$base = $base ? $base : 'tag';

	if ( 0 !== strpos( $path, $base . '/' ) ) {
		return;
	}

	$slug = sanitize_title( substr( $path, strlen( $base ) + 1 ) );
	if ( ! $slug ) {
		return;
	}

	$cat = get_term_by( 'slug', $slug, 'category' );
	if ( ! $cat ) {
		// "javascript-2" was only ever a slug WordPress invented to keep the
		// tag out of the category's way; the topic is the same one.
		$stripped = preg_replace( '/-\d+$/', '', $slug );
		$cat      = ( $stripped && $stripped !== $slug ) ? get_term_by( 'slug', $stripped, 'category' ) : false;
	}
	if ( ! $cat ) {
		return;
	}

	$link = get_term_link( $cat );
	if ( is_wp_error( $link ) ) {
		return;
	}

	wp_safe_redirect( $link, 301 );
	exit;
}
// Same priority as the retired-page redirects: ahead of the 404 template.
add_action( 'template_redirect', 'the_alpha_retired_tag_redirects', 1 );

/**
 * Say "Tagged:" in a tag archive's title, so it can't collide with a category's.
 *
 * WordPress titles both archives with the bare term name, and several topics
 * here exist as both a category and a tag under the same name — so /category/ai
 * and /tag/ai shipped a byte-identical <title>. Search engines read identical
 * titles as one page worth keeping and one worth dropping, and they pick which.
 *
 * The category is the curated home for a topic, so it keeps the clean name; the
 * tag archive names itself, exactly as its own H1 already does ("Tag: AI").
 *
 * @param array $parts Document title parts.
 * @return array
 */
function the_alpha_tag_document_title( $parts ) {
	if ( is_tag() && ! empty( $parts['title'] ) ) {
		/* translators: %s: tag name. */
		$parts['title'] = sprintf( __( 'Tagged: %s', 'the-alpha' ), $parts['title'] );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'the_alpha_tag_document_title' );

/**
 * Fold old standalone page URLs into the single-page front.
 *
 * These pages are still indexed by search engines (and get typed/shared
 * directly), but the site now presents them on the home page instead:
 *
 *   - /who   → #about    (content moved into the About section)
 *   - /touch → #contact  (content moved into the Contact section)
 *   - /terms → #terms    (opens the Terms drawer over the home page)
 *
 * A permanent (301) redirect sends anyone who lands on the old URL to the
 * matching home-page anchor.
 *
 * Matching is by URL path, not is_page(), so /who and /touch keep redirecting
 * even after their (now content-less) pages are deleted. /terms must keep its
 * page — the Terms drawer reads it — but the redirect still applies to direct
 * visits all the same.
 *
 * Exception: the footer's Terms/Subscribe drawer fills itself by fetching the
 * real page over XHR (theme.js `load()` sends `X-Requested-With: fetch`). That
 * request MUST reach the actual page, or the drawer has nothing to show — so we
 * bail out before redirecting whenever the drawer is the one asking.
 *
 * Map is slug => home-page anchor. Add future retirees here.
 */
function the_alpha_retired_page_redirects() {
	if ( is_admin() ) {
		return;
	}

	// Let the drawer's own fetch through to the real page (see docblock).
	if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] )
		&& 'fetch' === strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) ) ) {
		return;
	}

	$retired = array(
		'who'   => '/#about',
		'touch' => '/#contact',
		'terms' => '/#terms',
	);

	// Match the top-level slug from the request path: /who and /who/ both map to
	// "who", but a nested /blog/who/ stays "blog/who" and is left alone.
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$slug = strtolower( trim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' ) );

	if ( isset( $retired[ $slug ] ) ) {
		wp_safe_redirect( home_url( $retired[ $slug ] ), 301 );
		exit;
	}
}
// Priority 1: run before redirect_canonical (10) so a deleted /who redirects
// cleanly to #about instead of WordPress guessing a 404 fallback first.
add_action( 'template_redirect', 'the_alpha_retired_page_redirects', 1 );

/**
 * Open outgoing links in a new tab.
 *
 * Template links set target="_blank" by hand, but nothing ever touched links
 * typed into a post body, so every source cited inside an article navigated
 * the reader off the site. This closes that gap for posts already published
 * as well as new ones: it rewrites on output, so nothing in the database
 * changes and removing this filter restores the old markup exactly.
 *
 * Only genuinely outgoing http(s) links are touched. In-page anchors, mailto:,
 * tel: and same-host URLs are left alone, www. counts as the same host, and an
 * anchor that already carries a target of its own is never overridden.
 *
 * @param string $content The post content.
 * @return string
 */
function the_alpha_external_links_new_tab( $content ) {
	if ( is_feed() || false === strpos( $content, '<a ' ) ) {
		return $content;
	}

	$strip_www = static function ( $host ) {
		return preg_replace( '/^www\\./i', '', strtolower( (string) $host ) );
	};
	$site_host = $strip_www( wp_parse_url( home_url(), PHP_URL_HOST ) );

	return (string) preg_replace_callback(
		'/<a\\s[^>]*>/i',
		static function ( $m ) use ( $site_host, $strip_www ) {
			$tag = $m[0];

			// An explicit target was somebody's decision. Leave it.
			if ( preg_match( '/\\starget\\s*=/i', $tag ) ) {
				return $tag;
			}

			if ( ! preg_match( '/\\shref\\s*=\\s*(["\'])(.*?)\\1/i', $tag, $href ) ) {
				return $tag;
			}

			$url = trim( html_entity_decode( $href[2], ENT_QUOTES, 'UTF-8' ) );

			// Anything that is not an absolute http(s) URL is not outgoing.
			if ( '' === $url || ! preg_match( '#^https?://#i', $url ) ) {
				return $tag;
			}

			$link_host = $strip_www( wp_parse_url( $url, PHP_URL_HOST ) );
			if ( '' === $link_host || $link_host === $site_host ) {
				return $tag;
			}

			// Keep whatever rel the author wrote; add noopener only when missing.
			if ( preg_match( '/\\srel\\s*=\\s*(["\'])(.*?)\\1/i', $tag, $rel ) ) {
				if ( ! preg_match( '/\\bnoopener\\b/i', $rel[2] ) ) {
					$tag = str_replace(
						$rel[0],
						' rel="' . esc_attr( trim( $rel[2] . ' noopener' ) ) . '"',
						$tag
					);
				}
			} else {
				$tag = rtrim( $tag, '>' ) . ' rel="noopener">';
			}

			return rtrim( $tag, '>' ) . ' target="_blank">';
		},
		$content
	);
}
// Priority 20: after wpautop (10) has finished shaping the markup.
add_filter( 'the_content', 'the_alpha_external_links_new_tab', 20 );

require THE_ALPHA_DIR . '/inc/template-tags.php';
require THE_ALPHA_DIR . '/inc/banner-image.php';
require THE_ALPHA_DIR . '/inc/agent-readiness.php';
require THE_ALPHA_DIR . '/inc/missed-schedule-healer.php';
