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

/*
 * Stand down when the Agentimus plugin is handling agent readiness.
 *
 * Agentimus owns the same surfaces this file does — /llms.txt, /llms-full.txt,
 * markdown delivery (.md + `Accept: text/markdown`) and the robots.txt
 * content-signals — and registers them on the *same* hooks and priorities
 * (template_redirect:0, robots_txt:20), plus the /.well-known discovery layer
 * the theme never had. Running both is a registration-order race: the plugin
 * wins the template_redirect routes (it registers first and exits), but the
 * theme's robots_txt filter runs last and rebuilds from scratch, silently
 * clobbering the plugin's robots.txt. Rather than depend on load order, the
 * theme cedes the whole feature to the plugin whenever it is active.
 *
 * Deactivate Agentimus and this file resumes automatically as the
 * self-contained, no-plugin fallback it was built to be.
 */
if ( defined( 'AGENTIMUS_VERSION' ) ) {
	return;
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

	// 1b. /llms-full.txt — the full-text edition (whole pages + recent posts
	// concatenated), for agents that want the content, not just the index.
	if ( '/llms-full.txt' === $path ) {
		the_alpha_ar_send( the_alpha_ar_llms_full_txt(), 'text/plain' );
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

/**
 * robots.txt — minimal, correct, and self-maintaining.
 *
 * Replaces WordPress's virtual robots body with an explicit allow-all `*`
 * group that keeps the conventional wp-admin guard and advertises the live
 * sitemap. The Sitemap URL is resolved dynamically (the_alpha_ar_sitemap_url)
 * so it can never drift to a 404.
 *
 * The `Content-Signal` line states usage intent under the content-signals
 * convention (see the preamble Cloudflare et al. emit): search and AI input
 * (RAG / grounding / citation) are welcome, training is not. We declare this
 * ourselves so robots.txt stays a single source of truth in the repo rather
 * than being prepended at the edge.
 *
 * We then add one grouped `Disallow: /` for the known model-training crawlers.
 * This is the *legitimate* use of per-bot groups — those crawlers are being
 * treated differently (refused), not redundantly re-allowed — and unlike the
 * advisory Content-Signal, the major trainers (GPTBot, ClaudeBot, CCBot, …)
 * actually honor a robots.txt Disallow, so this enforces the no-training
 * stance. The read/cite bots (Claude-SearchBot, Claude-User, OAI-SearchBot,
 * ChatGPT-User, PerplexityBot, …) are deliberately absent from the list, so
 * they keep matching the allow-all `*` group and can read and cite the site.
 *
 * Runs at priority 20 so it wins over earlier filters, and only when the site
 * is public — if crawlers are discouraged we leave WP's `Disallow: /` intact.
 * Note: a *static* robots.txt at the web root, or an edge-managed robots.txt
 * (e.g. Cloudflare's), would bypass this entirely.
 *
 * @param string $output The robots.txt content built so far.
 * @param bool   $public Whether the site is set to be indexed.
 * @return string
 */
function the_alpha_ar_robots_txt( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}

	$signal = apply_filters( 'the_alpha_ar_content_signal', 'search=yes, ai-input=yes, ai-train=no' );

	$lines = array( 'User-agent: *' );
	if ( is_string( $signal ) && '' !== trim( $signal ) ) {
		$lines[] = 'Content-Signal: ' . trim( $signal );
	}
	$lines[] = 'Disallow: /wp-admin/';
	$lines[] = 'Allow: /wp-admin/admin-ajax.php';

	// Refuse the known model-training crawlers outright. They honor robots.txt,
	// so this actually stops training collection (the Content-Signal above is
	// only advisory). Read/cite bots are intentionally NOT listed, so they keep
	// matching the `*` group and stay free to read and cite the site.
	$trainers = apply_filters(
		'the_alpha_ar_blocked_ai_trainers',
		array(
			'Amazonbot',
			'Applebot-Extended',
			'Bytespider',
			'CCBot',
			'ClaudeBot',
			'GPTBot',
			'Google-Extended',
			'meta-externalagent',
		)
	);
	$trainers = array_values( array_filter( array_map( 'trim', (array) $trainers ) ) );
	if ( ! empty( $trainers ) ) {
		$lines[] = '';
		foreach ( $trainers as $agent ) {
			$lines[] = 'User-agent: ' . $agent;
		}
		$lines[] = 'Disallow: /';
	}

	$sitemap = the_alpha_ar_sitemap_url();
	if ( $sitemap ) {
		$lines[] = '';
		$lines[] = 'Sitemap: ' . esc_url_raw( $sitemap );
	}

	return implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'the_alpha_ar_robots_txt', 20, 2 );

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

	// About — the highest-signal lines in the file. One factual sentence that
	// states who the author is and an explicit expertise list, so a retrieval
	// system gets entity + topical authority before it consumes any links.
	$about = the_alpha_ar_about();
	if ( '' !== $about ) {
		$out .= "\n## About\n\n" . $about . "\n";
		$expertise = the_alpha_ar_expertise();
		if ( ! empty( $expertise ) ) {
			$out .= "\nExpertise:\n\n";
			foreach ( $expertise as $topic ) {
				$out .= '- ' . $topic . "\n";
			}
		}
	}

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

	// Topics — category archives, the expertise clustering agents retrieve on.
	$topics = the_alpha_ar_topics();
	if ( '' !== $topics ) {
		$out .= "\n## Topics\n\n" . $topics;
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
	$out .= '- [Full text](' . esc_url_raw( home_url( '/llms-full.txt' ) ) . "): every page and recent post concatenated into one document\n";
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
 * Build the llms-full.txt body — the full-text edition.
 *
 * Same identity header as /llms.txt, then the complete markdown of every page
 * (template-only pages with no prose body are skipped) followed by the most
 * recent posts, each a self-contained document separated by a horizontal rule.
 * This is the single-document an agent can ingest in one pass; /llms.txt stays
 * the lightweight link index. Cached for an hour, busted on the same content
 * hooks as /llms.txt. The post count is filterable so the file can't grow
 * unbounded on a large blog.
 */
function the_alpha_ar_llms_full_txt() {
	$cached = get_transient( 'the_alpha_llms_full_txt' );
	if ( is_string( $cached ) && '' !== $cached ) {
		return $cached;
	}

	$name    = the_alpha_ar_text( get_bloginfo( 'name' ) );
	$tagline = the_alpha_ar_text( get_bloginfo( 'description' ) );

	$out = '# ' . ( '' !== $name ? $name : home_url( '/' ) ) . "\n\n";
	if ( '' !== $tagline ) {
		$out .= '> ' . $tagline . "\n\n";
	}
	$out .= "Full-text edition: the author profile followed by the complete content of each page and recent article, concatenated for ingestion in a single pass. The link-only index is at /llms.txt; any one page is also available by appending `.md` to its URL.\n";

	// Identity block — same About + Expertise as /llms.txt.
	$about = the_alpha_ar_about();
	if ( '' !== $about ) {
		$out .= "\n## About\n\n" . $about . "\n";
		$expertise = the_alpha_ar_expertise();
		if ( ! empty( $expertise ) ) {
			$out .= "\nExpertise:\n\n";
			foreach ( $expertise as $topic ) {
				$out .= '- ' . $topic . "\n";
			}
		}
	}

	// Full content of every page (About, Contact, AuthLab, fragrance, …).
	$pages = get_pages(
		array(
			'sort_column' => 'menu_order,post_title',
			'number'      => 50,
		)
	);
	foreach ( (array) $pages as $page ) {
		if ( '' === trim( (string) $page->post_content ) ) {
			continue; // Template-driven page (e.g. front page, subscribe) — no prose body.
		}
		$out .= "\n---\n\n" . the_alpha_ar_post_markdown( $page->ID ) . "\n";
	}

	// Full content of recent posts, newest first.
	$count = (int) apply_filters( 'the_alpha_ar_llms_full_posts', 50 );
	$posts = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'numberposts'      => $count > 0 ? $count : 50,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		)
	);
	foreach ( $posts as $post ) {
		$out .= "\n---\n\n" . the_alpha_ar_post_markdown( $post->ID ) . "\n";
	}

	$out = rtrim( $out ) . "\n";
	set_transient( 'the_alpha_llms_full_txt', $out, HOUR_IN_SECONDS );
	return $out;
}

/**
 * `- [Topic](archive): description` lines for the non-empty categories,
 * heaviest first. Skips catch-all / noise buckets so the list reads as a
 * clean expertise map. Uses each category's description when set, otherwise a
 * post count.
 */
function the_alpha_ar_topics() {
	$exclude = apply_filters(
		'the_alpha_ar_topic_exclude',
		array( 'uncategorized', 'miscellaneous', 'random-ideas', 'best-parctice' )
	);

	$cats = get_categories(
		array(
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => 30,
		)
	);
	if ( empty( $cats ) || is_wp_error( $cats ) ) {
		return '';
	}

	$lines = '';
	foreach ( $cats as $cat ) {
		if ( in_array( $cat->slug, $exclude, true ) ) {
			continue;
		}
		$name = the_alpha_ar_text( $cat->name );
		$desc = the_alpha_ar_text( $cat->description );
		if ( '' === $desc ) {
			/* translators: %d: number of posts in the category. */
			$desc = sprintf( _n( '%d article', '%d articles', $cat->count, 'the-alpha' ), number_format_i18n( $cat->count ) );
		}
		$lines .= '- [' . $name . '](' . esc_url_raw( get_category_link( $cat ) ) . '): ' . $desc . "\n";
	}
	return $lines;
}

/**
 * The one-sentence author profile — entity + topical authority in a single
 * factual line. Shared by /llms.txt and /llms-full.txt (and exposed to any
 * JSON-LD via the `the_alpha_ar_about` filter), so the identity claim never
 * drifts between surfaces.
 *
 * @return string
 */
function the_alpha_ar_about() {
	return apply_filters(
		'the_alpha_ar_about',
		'Sheikh Heera is a software architect and CTO of Authlab, based in Sylhet, Bangladesh, with 16+ years building web applications and software systems. Outside engineering he is a dedicated fraghead who collects perfumes, with a personal collection of nearly 2,000 fragrances.'
	);
}

/**
 * Curated expertise topics, most authoritative first.
 *
 * Shared source of truth for the Person `knowsAbout` JSON-LD and the llms.txt
 * identity block, so the two never drift. Hand-picked rather than derived from
 * category counts — skills are an identity claim, not a function of how many
 * posts happen to sit in each bucket.
 *
 * @param int $limit Optional cap on the number of topics (0 = all).
 * @return string[] Expertise topics in priority order.
 */
function the_alpha_ar_expertise( $limit = 0 ) {
	$topics = apply_filters(
		'the_alpha_ar_expertise',
		array(
			'Software Architecture',
			'AI Engineering',
			'Security',
			'PHP',
			'JavaScript',
			'WordPress',
			'Laravel',
			'Vue',
			'Fragrance',
		)
	);

	$topics = array_values( array_filter( array_map( 'trim', (array) $topics ) ) );
	if ( $limit > 0 ) {
		$topics = array_slice( $topics, 0, $limit );
	}
	return $topics;
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
	delete_transient( 'the_alpha_llms_full_txt' );
}
add_action( 'save_post', 'the_alpha_ar_flush_llms' );
add_action( 'deleted_post', 'the_alpha_ar_flush_llms' );
add_action( 'trashed_post', 'the_alpha_ar_flush_llms' );
add_action( 'created_term', 'the_alpha_ar_flush_llms' );
add_action( 'edited_term', 'the_alpha_ar_flush_llms' );
add_action( 'delete_term', 'the_alpha_ar_flush_llms' );

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
