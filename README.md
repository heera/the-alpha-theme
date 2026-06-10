# The Alpha

A fast, dependency-free single-page WordPress theme for a software developer & fragrance enthusiast. Dark-first "Tech Noir" design with a flicker-free dark/light toggle, scroll-spy navigation, and zero bloat — no Bootstrap, no Elementor, no jQuery.

## Highlights

- **Single-page front page** with anchored sections (Home / About / Blog / Contact) plus standard blog/archive/single routes.
- **Dark/light theme toggle**, no-FOUC bootstrap, persistent visitor preference + admin-configurable default.
- **Custom block editor palette + font sizes** that map to live CSS variables, so colors honor the active theme.
- **Open Graph, Twitter Cards, and JSON-LD** (WebSite, BlogPosting, BreadcrumbList) baked in — self-defers when Yoast / Rank Math / SEO Press is active.
- **Customizer settings**: default colour scheme, featured-image toggles for blog listings and single posts.
- **Disqus-friendly**: works out-of-the-box with native WP comments; the official Disqus plugin overrides automatically when installed.
- **Smoke + bakhoor atmospherics** on the About section, themed gradients painted onto section backgrounds.
- **Smooth page-load reveal** with a custom spinner + shimmer label + cross-fade.
- **Agent-readable** (`inc/agent-readiness.php`): publishes `/llms.txt`, serves any page as
  markdown (via `Accept: text/markdown` or a parallel `.md` URL), and advertises the REST API +
  `/llms.txt` through `Link` headers. See *Agent readiness* below.

## Agent readiness

`inc/agent-readiness.php` makes the site legible to AI agents (improves the aiscan.site grade):

- `GET /llms.txt` — an [llmstxt.org](https://llmstxt.org) index (H1, summary, `##` link sections),
  cached for an hour and busted on any post change.
- Any post/page as clean markdown two ways:
  - **Content negotiation** — `curl -H "Accept: text/markdown" https://heera.it/some-post/`
  - **Parallel URL** — `https://heera.it/some-post.md` (and `/index.md` for the home/index view).
- `Link: …; rel="api-catalog"` (REST root) and `Link: …; rel="describedby"` (→ `/llms.txt`).

The `.md` URLs have their own cache key and are CDN-safe out of the box. The `Accept`-header
negotiation, however, hits the origin **only if the upstream FastCGI/nginx page cache is told to
bypass on that header** — otherwise a cached HTML page is served regardless of `Accept`. Add this to
the nginx `server` block (alongside the existing `fastcgi_cache` rules):

```nginx
# Serve fresh markdown to agents that ask for it; never cache it.
set $skip_cache 0;
if ($http_accept ~* "text/markdown") { set $skip_cache 1; }
fastcgi_cache_bypass $skip_cache;
fastcgi_no_cache     $skip_cache;
```

After deploying, purge **both** Cloudflare and the nginx page cache so `/llms.txt` and the new
headers aren't masked by stale entries.

## Tech

- Vanilla PHP, CSS (custom-properties driven), and ~3 KB of vanilla JS.
- Web fonts via a single Google Fonts request (Fraunces, Space Grotesk, Inter, JetBrains Mono, Rambla).
- All assets cache-busted via filemtime.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## License

GPL v2 or later — see the header in `style.css`.
