<?php
/**
 * Agent readiness — make the site legible to AI agents and crawlers.
 *
 * Three things, all self-contained (no plugin, no SEO bloat):
 *
 *   1. /llms.txt          — an llmstxt.org index of the site (H1 + summary +
 *                           `##` sections of markdown links).
 *   2. Markdown delivery  — the same post/page served as clean text/markdown,
 *                           either by content negotiation (`Accept: text/markdown`)
 *                           or via a parallel `.md` URL (e.g. /some-post.md).
 *   3. Link headers       — advertise the REST API as an api-catalog and point
 *                           agents at /llms.txt via rel="describedby".
 *
 * Note on caching: the `.md` URLs have their own cache key and are CDN-safe.
 * The `Accept`-header negotiation hits the origin only if the upstream cache is
 * told to bypass on that header — see the nginx snippet in the theme README.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Route the agent-facing endpoints early, before the normal template loads.
 *
 * Order matters: explicit paths (/llms.txt, *.md) win first, then we fall back
 * to `Accept: text/markdown` negotiation on whatever the main query resolved to.
 */
function the_alpha_ar_route() {
	if ( is_admin() || is_feed() || is_embed()
		|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		|| ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		|| ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
		return;
	}

	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( $_SERVER['REQUEST_METHOD'] ) : 'GET';
	if ( 'GET' !== $method && 'HEAD' !== $method ) {
		return;
	}

	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$path = '/' . ltrim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' );

	// 1. /llms.txt
	if ( '/llms.txt' === $path ) {
		the_alpha_ar_send( the_alpha_ar_llms_txt(), 'text/plain' );
	}

	// 2a. Parallel markdown URL: /slug.md, /index.md (home), etc.
	if ( '.md' === substr( $path, -3 ) ) {
		$clean = substr( $path, 0, -3 );

		if ( '' === $clean || '/' === $clean || '/index' === $clean ) {
			the_alpha_ar_send( the_alpha_ar_index_markdown(), 'text/markdown' );
		}

		$post_id = url_to_postid( home_url( trailingslashit( $clean ) ) );
		if ( ! $post_id ) {
			$post_id = url_to_postid( home_url( $clean ) );
		}
		if ( $post_id ) {
			the_alpha_ar_send( the_alpha_ar_post_markdown( $post_id ), 'text/markdown' );
		}
		// Unknown .md path: let WordPress 404 normally.
		return;
	}

	// 2b. Content negotiation on the resolved view.
	$accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? (string) $_SERVER['HTTP_ACCEPT'] : '';
	if ( false === stripos( $accept, 'text/markdown' ) ) {
		return;
	}

	if ( is_singular() ) {
		the_alpha_ar_send( the_alpha_ar_post_markdown( get_queried_object_id() ), 'text/markdown' );
	}
	if ( is_front_page() || is_home() || is_archive() || is_search() ) {
		the_alpha_ar_send( the_alpha_ar_index_markdown(), 'text/markdown' );
	}
}
add_action( 'template_redirect', 'the_alpha_ar_route', 0 );

/**
 * Emit a plain-text/markdown body with sane headers, then stop.
 *
 * Markdown negotiation responses are marked uncacheable so a crawler can never
 * poison a URL-keyed page cache and serve markdown to a human visitor.
 */
function the_alpha_ar_send( $body, $content_type ) {
	if ( ! headers_sent() ) {
		status_header( 200 );
		header( 'Content-Type: ' . $content_type . '; charset=UTF-8' );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Vary: Accept', false );

		if ( 'text/plain' === $content_type ) {
			// /llms.txt is stable — let the CDN cache it.
			header( 'Cache-Control: public, max-age=3600' );
		} else {
			// Negotiated markdown shares a URL with HTML; never let it be cached.
			header( 'Cache-Control: no-store, max-age=0' );
		}
	}

	if ( ! ( isset( $_SERVER['REQUEST_METHOD'] ) && 'HEAD' === strtoupper( $_SERVER['REQUEST_METHOD'] ) ) ) {
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput -- plain-text/markdown payload.
	}
	exit;
}

/**
 * Advertise discovery endpoints on every front-end response.
 */
function the_alpha_ar_link_headers() {
	if ( is_admin() ) {
		return;
	}
	header( 'Link: <' . esc_url_raw( rest_url() ) . '>; rel="api-catalog"', false );
	header( 'Link: <' . esc_url_raw( home_url( '/llms.txt' ) ) . '>; rel="describedby"; type="text/plain"', false );
}
add_action( 'send_headers', 'the_alpha_ar_link_headers' );

/* -------------------------------------------------------------------------- *
 *  /llms.txt
 * -------------------------------------------------------------------------- */

/**
 * Build the llms.txt body (cached for an hour; busted on content changes).
 */
function the_alpha_ar_llms_txt() {
	$cached = get_transient( 'the_alpha_llms_txt' );
	if ( is_string( $cached ) && '' !== $cached ) {
		return $cached;
	}

	$name    = the_alpha_ar_text( get_bloginfo( 'name' ) );
	$tagline = the_alpha_ar_text( get_bloginfo( 'description' ) );
	$home    = home_url( '/' );

	$out  = '# ' . ( '' !== $name ? $name : $home ) . "\n\n";
	if ( '' !== $tagline ) {
		$out .= '> ' . $tagline . "\n\n";
	}
	$out .= sprintf(
		"%s. Plain-text and markdown versions of any page are available by appending `.md` to its URL, or by requesting it with `Accept: text/markdown`.\n",
		'' !== $tagline ? $tagline : 'A personal site'
	);

	// Pages.
	$pages = get_pages(
		array(
			'sort_column' => 'menu_order,post_title',
			'number'      => 50,
		)
	);
	if ( ! empty( $pages ) ) {
		$out .= "\n## Pages\n\n";
		foreach ( $pages as $page ) {
			$out .= the_alpha_ar_link_line( $page );
		}
	}

	// Recent posts.
	$posts = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'numberposts'      => 50,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		)
	);
	if ( ! empty( $posts ) ) {
		$out .= "\n## Recent posts\n\n";
		foreach ( $posts as $post ) {
			$out .= the_alpha_ar_link_line( $post );
		}
	}

	$out .= "\n## Optional\n\n";
	$out .= '- [Feed](' . esc_url_raw( get_feed_link() ) . "): RSS of recent posts\n";
	$sitemap = the_alpha_ar_sitemap_url();
	if ( $sitemap ) {
		$out .= '- [Sitemap](' . esc_url_raw( $sitemap ) . "): full XML sitemap\n";
	}

	$out = rtrim( $out ) . "\n";
	set_transient( 'the_alpha_llms_txt', $out, HOUR_IN_SECONDS );
	return $out;
}

/**
 * Plain text from HTML: strip tags, decode entities, collapse whitespace.
 */
function the_alpha_ar_text( $html ) {
	$text = wp_strip_all_tags( (string) $html );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	return trim( $text );
}

/**
 * One `- [Title](url): excerpt` line for a post or page.
 */
function the_alpha_ar_link_line( $post ) {
	$title   = the_alpha_ar_text( get_the_title( $post ) );
	$url     = get_permalink( $post );
	$excerpt = the_alpha_ar_text( get_the_excerpt( $post ) );
	$excerpt = trim( preg_replace( '/\s+/', ' ', $excerpt ) );
	if ( '' !== $excerpt ) {
		$excerpt = ': ' . wp_html_excerpt( $excerpt, 160, '…' );
	}
	return '- [' . $title . '](' . esc_url_raw( $url ) . ')' . $excerpt . "\n";
}

/**
 * Best-effort sitemap URL (core sitemaps or a common plugin location).
 */
function the_alpha_ar_sitemap_url() {
	if ( function_exists( 'wp_sitemaps_get_server' ) ) {
		$server = wp_sitemaps_get_server();
		if ( $server && $server->sitemaps_enabled() ) {
			return home_url( '/wp-sitemap.xml' );
		}
	}
	return home_url( '/sitemap_index.xml' );
}

/**
 * Flush the cached llms.txt when content changes.
 */
function the_alpha_ar_flush_llms() {
	delete_transient( 'the_alpha_llms_txt' );
}
add_action( 'save_post', 'the_alpha_ar_flush_llms' );
add_action( 'deleted_post', 'the_alpha_ar_flush_llms' );
add_action( 'trashed_post', 'the_alpha_ar_flush_llms' );

/* -------------------------------------------------------------------------- *
 *  Markdown rendering
 * -------------------------------------------------------------------------- */

/**
 * A single post/page rendered as standalone markdown with a small front matter.
 */
function the_alpha_ar_post_markdown( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return "# Not found\n";
	}

	$title = the_alpha_ar_text( get_the_title( $post ) );
	$url   = get_permalink( $post );

	$out  = '# ' . $title . "\n\n";

	$meta = array();
	if ( 'post' === $post->post_type ) {
		$meta[] = get_the_date( 'Y-m-d', $post );
		$author = get_the_author_meta( 'display_name', $post->post_author );
		if ( $author ) {
			$meta[] = 'by ' . $author;
		}
		$cats = wp_get_post_terms( $post->ID, 'category', array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) {
			$meta[] = 'in ' . implode( ', ', $cats );
		}
	}
	$meta[] = '<' . $url . '>';
	$out   .= '*' . implode( ' · ', $meta ) . "*\n\n";

	$content = apply_filters( 'the_content', $post->post_content );
	$out    .= the_alpha_ar_html_to_markdown( $content );

	return rtrim( $out ) . "\n";
}

/**
 * The home/archive view as a markdown index — reuses the llms.txt body.
 */
function the_alpha_ar_index_markdown() {
	return the_alpha_ar_llms_txt();
}

/**
 * Convert a fragment of HTML to clean markdown.
 *
 * Walks the DOM rather than running regexes so nested lists, links inside
 * emphasis, code blocks, etc. survive. Good enough for prose; not a full
 * CommonMark serializer.
 */
function the_alpha_ar_html_to_markdown( $html ) {
	$html = trim( (string) $html );
	if ( '' === $html ) {
		return '';
	}
	if ( ! class_exists( 'DOMDocument' ) ) {
		return trim( wp_strip_all_tags( $html ) ) . "\n";
	}

	$dom = new DOMDocument();
	$prev = libxml_use_internal_errors( true );
	$dom->loadHTML(
		'<?xml encoding="UTF-8"><div id="the-alpha-md-root">' . $html . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );

	$root = $dom->getElementById( 'the-alpha-md-root' );
	$md   = $root ? the_alpha_ar_md_children( $root ) : '';

	$md = preg_replace( "/[ \t]+\n/", "\n", $md );
	$md = preg_replace( "/\n{3,}/", "\n\n", $md );
	return trim( $md ) . "\n";
}

/**
 * Concatenate the markdown of a node's children.
 */
function the_alpha_ar_md_children( $node ) {
	$out = '';
	foreach ( $node->childNodes as $child ) {
		$out .= the_alpha_ar_md_node( $child );
	}
	return $out;
}

/**
 * Render one DOM node to markdown.
 */
function the_alpha_ar_md_node( $node ) {
	if ( XML_TEXT_NODE === $node->nodeType ) {
		return preg_replace( '/\s+/', ' ', $node->nodeValue );
	}
	if ( XML_ELEMENT_NODE !== $node->nodeType ) {
		return '';
	}

	$tag   = strtolower( $node->nodeName );
	$inner = the_alpha_ar_md_children( $node );

	switch ( $tag ) {
		case 'h1':
		case 'h2':
		case 'h3':
		case 'h4':
		case 'h5':
		case 'h6':
			$level = (int) substr( $tag, 1 );
			return "\n\n" . str_repeat( '#', $level ) . ' ' . trim( $inner ) . "\n\n";

		case 'p':
			return "\n\n" . trim( $inner ) . "\n\n";

		case 'br':
			return "  \n";

		case 'hr':
			return "\n\n---\n\n";

		case 'strong':
		case 'b':
			$t = trim( $inner );
			return '' === $t ? '' : '**' . $t . '**';

		case 'em':
		case 'i':
			$t = trim( $inner );
			return '' === $t ? '' : '*' . $t . '*';

		case 'del':
		case 's':
			$t = trim( $inner );
			return '' === $t ? '' : '~~' . $t . '~~';

		case 'code':
			return '`' . trim( $node->textContent ) . '`';

		case 'pre':
			return "\n\n```\n" . rtrim( $node->textContent ) . "\n```\n\n";

		case 'a':
			$href = trim( (string) $node->getAttribute( 'href' ) );
			$text = trim( $inner );
			if ( '' === $href ) {
				return $text;
			}
			return '[' . ( '' !== $text ? $text : $href ) . '](' . $href . ')';

		case 'img':
			$src = trim( (string) $node->getAttribute( 'src' ) );
			$alt = trim( (string) $node->getAttribute( 'alt' ) );
			return '' === $src ? '' : '![' . $alt . '](' . $src . ')';

		case 'blockquote':
			$t = trim( $inner );
			return "\n\n" . preg_replace( '/^/m', '> ', $t ) . "\n\n";

		case 'ul':
		case 'ol':
			return "\n\n" . the_alpha_ar_md_list( $node, $tag ) . "\n\n";

		case 'li':
			return $inner; // Composed by the_alpha_ar_md_list().

		case 'figure':
		case 'figcaption':
		case 'div':
		case 'section':
		case 'article':
		case 'header':
		case 'footer':
		case 'main':
			$t = trim( $inner );
			return '' === $t ? '' : "\n\n" . $t . "\n\n";

		default:
			return $inner;
	}
}

/**
 * Render a <ul>/<ol> to markdown, handling nesting via indentation.
 */
function the_alpha_ar_md_list( $node, $type ) {
	$lines = array();
	$i     = 1;
	foreach ( $node->childNodes as $child ) {
		if ( XML_ELEMENT_NODE !== $child->nodeType || 'li' !== strtolower( $child->nodeName ) ) {
			continue;
		}
		$marker  = 'ol' === $type ? ( $i++ . '.' ) : '-';
		$content = trim( the_alpha_ar_md_children( $child ) );
		$content = preg_replace( "/\n/", "\n  ", $content ); // Indent nested blocks.
		$lines[] = $marker . ' ' . $content;
	}
	return implode( "\n", $lines );
}
