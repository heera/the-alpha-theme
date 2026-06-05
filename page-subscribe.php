<?php
/**
 * Subscribe — landing page for the RSS feed with copy + one-click reader buttons.
 * Picked up automatically by WP for the page with slug `subscribe`.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$feed_url     = get_bloginfo( 'rss2_url' );
$feed_enc     = rawurlencode( $feed_url );
$feed_alt     = preg_replace( '#^https?:#', 'feed:', $feed_url );  // for default-reader handler

$readers = array(
	array(
		'label' => __( 'Feedly', 'the-alpha' ),
		'href'  => 'https://feedly.com/i/subscription/feed/' . $feed_enc,
		'hint'  => __( 'Web · free', 'the-alpha' ),
	),
	array(
		'label' => __( 'Inoreader', 'the-alpha' ),
		'href'  => 'https://www.inoreader.com/?add_feed=' . $feed_enc,
		'hint'  => __( 'Web · free', 'the-alpha' ),
	),
	array(
		'label' => __( 'NewsBlur', 'the-alpha' ),
		'href'  => 'https://newsblur.com/?url=' . $feed_enc,
		'hint'  => __( 'Web · free', 'the-alpha' ),
	),
	array(
		'label' => __( 'Default reader', 'the-alpha' ),
		'href'  => $feed_alt,
		'hint'  => __( 'macOS / iOS', 'the-alpha' ),
	),
);
?>
<div class="page-head">
	<div class="wrap">
		<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> / <?php esc_html_e( 'Subscribe', 'the-alpha' ); ?></p>
		<h1 class="page-title"><?php esc_html_e( 'Subscribe', 'the-alpha' ); ?></h1>
	</div>
</div>

<div class="content-grid content-grid--single">
	<article class="entry subscribe">
		<div class="prose">
			<p class="lede">
				<?php esc_html_e( 'Follow this blog in a reader app — no email, no algorithm. New posts land in your inbox of choice the moment they’re published.', 'the-alpha' ); ?>
			</p>

			<div class="subscribe__feed">
				<label class="subscribe__feed-label" for="subscribe-feed-url"><?php esc_html_e( 'RSS feed URL', 'the-alpha' ); ?></label>
				<div class="subscribe__feed-row">
					<input id="subscribe-feed-url" class="subscribe__feed-input" type="text" readonly value="<?php echo esc_attr( $feed_url ); ?>">
					<button type="button" class="subscribe__copy btn btn--ghost" data-copy-target="#subscribe-feed-url" title="<?php esc_attr_e( 'Copy feed URL', 'the-alpha' ); ?>" aria-label="<?php esc_attr_e( 'Copy feed URL', 'the-alpha' ); ?>">
						<svg class="subscribe__copy-icon subscribe__copy-icon--copy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg>
						<svg class="subscribe__copy-icon subscribe__copy-icon--check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
					</button>
				</div>
			</div>

			<p class="subscribe__or"><?php esc_html_e( 'Or open in a reader', 'the-alpha' ); ?></p>

			<ul class="subscribe__readers" role="list">
				<?php foreach ( $readers as $r ) : ?>
					<li>
						<a class="subscribe__reader" href="<?php echo esc_url( $r['href'] ); ?>" target="_blank" rel="noopener">
							<span class="subscribe__reader-name"><?php echo esc_html( $r['label'] ); ?></span>
							<span class="subscribe__reader-hint"><?php echo esc_html( $r['hint'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<p class="subscribe__newbie">
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: 1: Feedly link, 2: NetNewsWire link */
						__( 'New to RSS? Start with %1$s (web, works anywhere) or %2$s (Mac &amp; iOS, native). Both are free.', 'the-alpha' ),
						'<a href="https://feedly.com" target="_blank" rel="noopener">Feedly</a>',
						'<a href="https://netnewswire.com" target="_blank" rel="noopener">NetNewsWire</a>'
					)
				);
				?>
			</p>
		</div>
	</article>
</div>
<?php
/* The copy-to-clipboard handler lives in theme.js as a delegated listener so
 * it also works when this page's content is loaded into the footer drawer
 * (injected HTML never runs its own inline <script>). */
get_footer();
