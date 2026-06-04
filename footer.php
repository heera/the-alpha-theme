<?php
/**
 * Footer — closes .main / .layout, site colophon.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</main><!-- .main -->
</div><!-- .layout -->

<footer class="site-footer">
	<div class="site-footer__col site-footer__col--left">
		<p class="site-footer__copy"><?php the_alpha_copyright(); ?></p>
		<p class="site-footer__sub">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: WP Manage Ninja link. */
					__( 'Proudly powered by %s', 'the-alpha' ),
					'<a href="https://wpmanageninja.com/" target="_blank" rel="noopener">WP Manage Ninja</a>'
				)
			);
			?>
		</p>
	</div>

	<div class="site-footer__col site-footer__col--right">
		<a class="site-footer__rss" href="<?php echo esc_url( home_url( '/subscribe/' ) ); ?>" data-drawer>
			<?php esc_html_e( 'Subscribe to my', 'the-alpha' ); ?>
			<span class="site-footer__rss-slug">&nbsp;/rss</span>
		</a>
		<a class="site-footer__terms" href="<?php echo esc_url( home_url( '/terms/' ) ); ?>" data-drawer><?php esc_html_e( 'Terms and Conditions', 'the-alpha' ); ?></a>
	</div>
</footer>

<?php
/*
 * Slide-in drawer for Terms / Subscribe. Progressive enhancement: the footer
 * links above navigate normally without JS; theme.js intercepts [data-drawer]
 * clicks and loads the target page's content in here instead. The real pages
 * stay the canonical, shareable, no-JS fallback.
 */
?>
<div class="drawer" id="site-drawer" hidden>
	<div class="drawer__scrim" data-drawer-close></div>
	<aside class="drawer__panel" role="dialog" aria-modal="true" aria-labelledby="drawer-title" tabindex="-1">
		<header class="drawer__bar">
			<button class="drawer__close" type="button" data-drawer-close aria-label="<?php esc_attr_e( 'Close', 'the-alpha' ); ?>">
				<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
			</button>
		</header>
		<div class="drawer__scroll">
			<h2 class="drawer__title" id="drawer-title" tabindex="-1"></h2>
			<div class="drawer__content prose"></div>
		</div>
	</aside>
</div>

<button class="to-top" type="button" aria-label="<?php esc_attr_e( 'Back to top', 'the-alpha' ); ?>">
	<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M12 7.4 5.7 13.7a1 1 0 0 0 1.4 1.4L12 10.2l4.9 4.9a1 1 0 0 0 1.4-1.4L12 7.4z"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
