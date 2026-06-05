<?php
/**
 * Reusable template helpers.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site copyright line.
 *
 * Full (footer):  "Copyright © {start}–{current} {site name}".
 * Short (sidebar, $short = true): "© {current} {site name}" — a compact
 * persistent credit that doesn't duplicate the footer's formal line.
 *
 * End year is dynamic (wp_date, site timezone); the full form collapses to a
 * single year until the current year passes the start. Start year filterable.
 *
 * @param bool $short Output the compact sidebar variant.
 */
function the_alpha_copyright( $short = false ) {
	$now = (int) wp_date( 'Y' );

	if ( $short ) {
		/* translators: 1: current year, 2: site name. */
		printf( '&copy; %1$d %2$s', $now, esc_html( get_bloginfo( 'name' ) ) );
		return;
	}

	$start = (int) apply_filters( 'the_alpha_copyright_start_year', 2011 );
	$years = ( $now > $start ) ? $start . '&ndash;' . $now : (string) $now;
	printf(
		/* translators: 1: year or year range, 2: site name. */
		esc_html__( 'Copyright', 'the-alpha' ) . ' &copy; %1$s %2$s',
		$years, // safe: ints + literal entity built above.
		esc_html( get_bloginfo( 'name' ) )
	);
}

/**
 * Social profiles (sourced from the previous site). Edit here to update everywhere.
 *
 * @return array<string,array{label:string,url:string,icon:string}>
 */
function the_alpha_socials() {
	return array(
		'stackoverflow' => array(
			'label' => 'Stack Overflow',
			'url'   => 'https://stackoverflow.com/users/741747/the-alpha',
			'icon'  => 'M21 21v-7h-2v5H5v-5H3v7h18zM7.5 14.6l8.1 1.7.4-2-8.1-1.7-.4 2zm1-4.7 7.5 3.5.9-1.8-7.5-3.5-.9 1.8zm2-4.3 6.4 5.3 1.3-1.5-6.4-5.3-1.3 1.5zM15 2l-1.6 1.2 5 6.6L20 8.6 15 2zM7 19h9v-2H7v2z',
		),
		'github'        => array(
			'label' => 'GitHub',
			'url'   => 'https://github.com/heera',
			'icon'  => 'M12 2a10 10 0 0 0-3.16 19.49c.5.09.68-.22.68-.48l-.01-1.7c-2.78.6-3.37-1.34-3.37-1.34-.46-1.16-1.11-1.47-1.11-1.47-.9-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.9 1.52 2.34 1.08 2.91.83.09-.65.35-1.09.63-1.34-2.22-.25-4.55-1.11-4.55-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.64 0 0 .84-.27 2.75 1.02a9.56 9.56 0 0 1 5 0c1.91-1.29 2.75-1.02 2.75-1.02.55 1.37.2 2.39.1 2.64.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.68-4.57 4.93.36.31.68.92.68 1.85l-.01 2.75c0 .27.18.58.69.48A10 10 0 0 0 12 2z',
		),
		'linkedin'      => array(
			'label' => 'LinkedIn',
			'url'   => 'https://www.linkedin.com/in/sheikh-heera/',
			'icon'  => 'M6.94 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM3.2 8.5h3.5V21H3.2V8.5zm5.96 0h3.36v1.7h.05c.47-.85 1.6-1.75 3.3-1.75 3.53 0 4.18 2.23 4.18 5.13V21h-3.5v-5.55c0-1.32-.02-3.02-1.85-3.02-1.85 0-2.13 1.43-2.13 2.92V21H9.16V8.5z',
		),
		'x'             => array(
			'label' => 'X',
			'url'   => 'https://x.com/heerasheikh',
			'icon'  => 'M17.53 3H20.5l-6.49 7.41L21.75 21h-6l-4.7-6.14L5.66 21H2.69l6.94-7.93L2.25 3h6.15l4.25 5.62L17.53 3zm-1.05 16.2h1.65L7.6 4.71H5.83L16.48 19.2z',
		),
		'facebook'      => array(
			'label' => 'Facebook',
			'url'   => 'https://www.facebook.com/sheikh.heera',
			'icon'  => 'M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z',
		),
		'pinterest'     => array(
			'label' => 'Pinterest',
			'url'   => 'https://www.pinterest.com/sheikhheera/',
			'icon'  => 'M12 2a10 10 0 0 0-3.65 19.31c-.09-.78-.17-1.98.03-2.83.19-.81 1.2-5.13 1.2-5.13s-.31-.61-.31-1.52c0-1.42.83-2.48 1.86-2.48.88 0 1.3.66 1.3 1.45 0 .88-.56 2.2-.85 3.42-.24 1.02.51 1.85 1.52 1.85 1.82 0 3.22-1.92 3.22-4.7 0-2.46-1.77-4.18-4.29-4.18-2.92 0-4.64 2.19-4.64 4.46 0 .88.34 1.83.76 2.34a.3.3 0 0 1 .07.29c-.08.32-.25 1.02-.29 1.16-.04.19-.15.23-.35.14-1.3-.61-2.11-2.5-2.11-4.02 0-3.28 2.38-6.29 6.86-6.29 3.6 0 6.4 2.57 6.4 6 0 3.58-2.26 6.46-5.39 6.46-1.05 0-2.04-.55-2.38-1.19l-.65 2.47c-.23.91-.86 2.04-1.29 2.74A10 10 0 1 0 12 2z',
		),
		'fragrantica'   => array(
			'label' => 'Fragrantica',
			'url'   => 'https://www.fragrantica.com/member/1374163',
			// Kept here so the profile still feeds the JSON-LD sameAs signal
			// (see functions.php), but hidden from the visible icon row — it
			// gets a contextual home in the About fragrance copy instead.
			'hidden' => true,
			// Official Fragrantica hummingbird mark; carries its own 0 0 100 100
			// viewBox + fill-rule (bird is negative space), so it ships as full
			// inline SVG rather than the shared 24x24 single-path template.
			'svg'   => '<svg viewBox="0 0 100 100" width="18" height="18" aria-hidden="true" focusable="false"><path fill="currentColor" d="M50,0a50,50,0,1,0,50,50A50,50,0,0,0,50,0ZM15.16,36.43a130.39,130.39,0,0,1,13.19-1.34,46.9,46.9,0,0,1,6.14.41c12.83,1.66,28,11.81,32,19.31a13.2,13.2,0,0,0-1.27-1.39c-6.59-5.85-18.37-11.51-29.37-12.24-5.56-.37-11.34.74-18.2,2.5A44.56,44.56,0,0,1,15.16,36.43ZM19,46.29c4.11-1.24,8-2.85,16.62-3.3,9.43-.5,21,4.37,29.39,11a6.34,6.34,0,0,1,1.48,1.59c-19.32-9.85-36-3.83-42-.47A49,49,0,0,1,19,46.29Zm7.53,10.78c6-2.92,12.14-4.93,18.3-5.24a41,41,0,0,1,19.86,3.62c1.22.47,1.46.84,1.46.84-.48-.11-5.63-.44-7.2-.44-.76,0-1.53,0-2.28,0-8.1.42-15.73,2.08-23.23,7.44A50.1,50.1,0,0,1,26.52,57.07Zm8.74,7.58c7-5.23,13.58-7,21.56-7.76,1.53-.14,5.26-.12,6.42-.06,0,0,2.47.1,2.84.16a41.24,41.24,0,0,0-25,11.08A48.63,48.63,0,0,1,35.26,64.65Zm8,4.47C47.17,65.34,49.62,63,56,60.25a33.16,33.16,0,0,1,9.88-2.56,21.58,21.58,0,0,0-3.21,5.65A36.33,36.33,0,0,0,61,70C54.25,71.43,48.67,71.49,43.29,69.12Zm50-25.77a55.65,55.65,0,0,0-10.17,4.11,4.55,4.55,0,0,0-2.71,3c-.9,2.37-1,6.28-.86,9.8a21.39,21.39,0,0,1,.06,4.28c-.67,11.37-9.54,18.11-18,25.63-2.66,2.38-8.55,7.23-8.92,7.38-.86.34-.18-.8-.18-.8l9.43-16.42c0-13.41,3-19.34,6.29-23.5a5,5,0,0,1,1.12-1.07,4,4,0,0,1-.64-.65c-1.16-1.44-1.72-3.82-.9-5.34a8.38,8.38,0,0,1,6.46-4.32,7.8,7.8,0,0,1,1.9.06c1.33.06,2.8.35,4.15.49s4.37-1.5,8-2.49a30.79,30.79,0,0,1,7.66-1.15c.33,0,1.37,0,1.5.2a4.51,4.51,0,0,1-1.23.15A25.74,25.74,0,0,0,93.28,43.35Z"/></svg>',
		),
	);
}

/**
 * Render the social icon list.
 *
 * @param string $classname Wrapper class.
 */
function the_alpha_social_links( $classname = 'socials' ) {
	$socials = the_alpha_socials();
	echo '<ul class="' . esc_attr( $classname ) . '">';
	foreach ( $socials as $key => $s ) {
		// Entries flagged 'hidden' stay in the data (for sameAs / SEO) but
		// are not rendered as visible icons.
		if ( ! empty( $s['hidden'] ) ) {
			continue;
		}
		// Most entries carry an 'icon' path drawn into a shared 24x24 svg;
		// an entry may instead supply ready-made 'svg' markup (e.g. a brand
		// mark with its own viewBox). Both are theme-authored, not user input.
		if ( ! empty( $s['svg'] ) ) {
			$glyph = $s['svg'];
		} else {
			$glyph = sprintf(
				'<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path fill="currentColor" d="%s"/></svg>',
				esc_attr( $s['icon'] )
			);
		}
		printf(
			'<li><a href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s" title="%2$s">%3$s</a></li>',
			esc_url( $s['url'] ),
			esc_attr( $s['label'] ),
			$glyph
		);
	}
	echo '</ul>';
}

/**
 * Estimated reading time for the current post.
 *
 * @return string e.g. "4 min read"
 */
function the_alpha_reading_time() {
	$words   = str_word_count( wp_strip_all_tags( get_the_content() ) );
	$minutes = max( 1, (int) ceil( $words / 200 ) );
	/* translators: %d: number of minutes. */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'the-alpha' ), $minutes );
}

/**
 * Single-post meta bar: author · date · comments-count link. Author and
 * date are static (informational); the comments item is an anchor link
 * to #comments and only renders when there is a comments section to
 * scroll to (open or has existing comments).
 */
function the_alpha_post_meta_single() {
	$author_icon = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><circle cx="12" cy="8" r="3.6" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M4.5 19.2c1.4-3.2 4.2-5 7.5-5s6.1 1.8 7.5 5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
	$date_icon   = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><rect x="3.2" y="5" width="17.6" height="15" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M3.2 9.5h17.6M8 3.5v3M16 3.5v3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
	$cat_icon    = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M3.5 7.5a2 2 0 0 1 2-2h4l2 2.5h7a2 2 0 0 1 2 2V17a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2V7.5z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';
	$comm_icon   = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M4.5 5.5h15a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H10l-4.5 3.2v-3.2H4.5A1.5 1.5 0 0 1 3 16V7a1.5 1.5 0 0 1 1.5-1.5z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';

	echo '<div class="post-meta-single">';

	printf(
		'<span class="post-meta-single__item">%s<span>%s</span></span>',
		$author_icon, // safe: hand-authored inline SVG above.
		esc_html( get_the_author() )
	);

	printf(
		'<span class="post-meta-single__item">%s<time datetime="%s">%s</time></span>',
		$date_icon,
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);

	$cats = get_the_category();
	if ( ! empty( $cats ) ) {
		$cat_links = array();
		foreach ( $cats as $c ) {
			$cat_links[] = sprintf(
				'<a href="%s" rel="category">%s</a>',
				esc_url( get_category_link( $c->term_id ) ),
				esc_html( $c->name )
			);
		}
		printf(
			'<span class="post-meta-single__item post-meta-single__cats">%s<span>%s</span></span>',
			$cat_icon, // safe: hand-authored inline SVG above.
			implode( ', ', $cat_links ) // category names already escaped above.
		);
	}

	if ( comments_open() || get_comments_number() ) {
		echo '<a class="post-meta-single__item post-meta-single__comments" href="#comments">';
		echo $comm_icon; // safe: hand-authored inline SVG above.
		comments_number(
			esc_html__( 'No Comments', 'the-alpha' ),
			esc_html__( '1 Comment', 'the-alpha' ),
			esc_html__( '% Comments', 'the-alpha' )
		);
		echo '</a>';
	}

	echo '</div>';
}

/**
 * Compact post meta line: date · category · reading time.
 */
function the_alpha_post_meta() {
	$cats = get_the_category();
	echo '<div class="post-meta">';
	printf(
		'<time datetime="%s">%s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
	if ( ! empty( $cats ) ) {
		printf(
			'<span class="dot" aria-hidden="true">&middot;</span><a href="%s" rel="category">%s</a>',
			esc_url( get_category_link( $cats[0]->term_id ) ),
			esc_html( $cats[0]->name )
		);
	}
	printf(
		'<span class="dot" aria-hidden="true">&middot;</span><span>%s</span>',
		esc_html( the_alpha_reading_time() )
	);
	echo '</div>';
}

/**
 * The four single-page sections used for the sidebar nav + scroll-spy.
 * On non-front views the links jump back to the front page anchor.
 *
 * @return array<string,string> slug => label
 */
function the_alpha_sections() {
	return array(
		'home'    => __( 'Home', 'the-alpha' ),
		'about'   => __( 'About', 'the-alpha' ),
		'blog'    => __( 'Blog', 'the-alpha' ),
		'contact' => __( 'Contact', 'the-alpha' ),
	);
}

/**
 * Output the primary navigation. Uses a WP menu if one is assigned to the
 * "primary" location, otherwise falls back to the single-page section anchors.
 */
function the_alpha_primary_nav() {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'nav__list',
			'fallback_cb'    => false,
			'depth'          => 1,
		) );
		return;
	}

	$on_front = is_front_page();
	$base     = $on_front ? '' : home_url( '/' );

	echo '<ul class="nav__list" role="list">';
	foreach ( the_alpha_sections() as $slug => $label ) {
		printf(
			'<li><a class="nav__link" href="%1$s#%2$s" data-section="%2$s">%3$s</a></li>',
			esc_url( $base ),
			esc_attr( $slug ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Featured image URL with a graceful theme placeholder fallback.
 *
 * @param string $size Image size.
 * @return string
 */
function the_alpha_thumb_url( $size = 'the_alpha_card' ) {
	if ( has_post_thumbnail() ) {
		$url = get_the_post_thumbnail_url( get_the_ID(), $size );
		if ( $url ) {
			return $url;
		}
	}
	return THE_ALPHA_URI . '/assets/img/placeholder.webp';
}
