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

	<?php
	/*
	 * Centre seal — a signature emblem engraved with the daily loop as four
	 * words at the compass points: CODE (N), SNIFF (E), EAT (S), SLEEP (W), so
	 * clockwise from the top it reads as a cycle. Each word rides its own rim arc
	 * (top/bottom upright, sides curving along the rim). Inside: concentric
	 * rings, a compass crosshair, drifting fragrance motes and a glowing core.
	 * Purely decorative, static — no motion.
	 */
	?>
	<div class="site-footer__seal" aria-hidden="true">
		<svg viewBox="-16 -16 132 132" width="124" height="124" fill="none" stroke="currentColor" focusable="false">
			<defs>
				<radialGradient id="sealHaze">
					<stop offset="0%" stop-color="currentColor" stop-opacity="0.13"/>
					<stop offset="100%" stop-color="currentColor" stop-opacity="0"/>
				</radialGradient>
				<!-- One rim arc per word. Top/bottom render upright; the side arcs
				     curve along the rim (Sniff reads down the right, Sleep up the
				     left), each centred on its compass point at 50% offset. -->
				<path id="sealRimN" d="M 9.4 15.9 A 53 53 0 0 1 90.6 15.9"/>
				<path id="sealRimS" d="M 9.4 84.1 A 53 53 0 0 0 90.6 84.1"/>
				<path id="sealRimE" d="M 95.9 23.5 A 53 53 0 0 1 95.9 76.5"/>
				<path id="sealRimW" d="M 4.1 76.5 A 53 53 0 0 1 4.1 23.5"/>
			</defs>
			<circle cx="50" cy="50" r="32" fill="url(#sealHaze)" stroke="none"/>
			<circle class="site-footer__seal-ring" cx="50" cy="50" r="60" stroke-width="0.7" stroke-opacity="0.18"/>
			<circle class="site-footer__seal-ring" cx="50" cy="50" r="44" stroke-width="0.7" stroke-opacity="0.22"/>
			<circle class="site-footer__seal-ring" cx="50" cy="50" r="33" stroke-width="0.7" stroke-opacity="0.3"/>
			<g stroke-width="0.6" stroke-opacity="0.2">
				<line x1="50" y1="17" x2="50" y2="26"/>
				<line x1="50" y1="74" x2="50" y2="83"/>
				<line x1="17" y1="50" x2="26" y2="50"/>
				<line x1="74" y1="50" x2="83" y2="50"/>
			</g>
			<circle class="site-footer__seal-ring" cx="50" cy="50" r="14" stroke-width="0.7" stroke-opacity="0.42"/>
			<g fill="currentColor" stroke="none">
				<circle cx="50" cy="23" r="0.9" fill-opacity="0.5"/>
				<circle cx="66" cy="42" r="0.7" fill-opacity="0.32"/>
				<circle cx="37" cy="61" r="0.8" fill-opacity="0.38"/>
				<circle cx="40" cy="36" r="0.7" fill-opacity="0.3"/>
			</g>
			<circle class="site-footer__seal-ring" cx="50" cy="50" r="6" stroke-width="0.8" stroke-opacity="0.6"/>
			<circle class="site-footer__seal-core" cx="50" cy="50" r="2.4" fill="currentColor" stroke="none"/>
			<!-- Cycle arrows: a small clockwise arrow rides the rim in each gap
			     between words, so the loop reads as a flow (Code → Sniff → Eat →
			     Sleep → …). Generated at the four intercardinal points. -->
			<g stroke-width="0.8" stroke-opacity="0.4" stroke-linecap="round" stroke-linejoin="round">
				<?php
				$ar = 53;
				foreach ( array( 45, 135, 225, 315 ) as $c ) {
					$a0  = deg2rad( $c - 13 );
					$a1  = deg2rad( $c + 13 );
					$x0  = 50 + $ar * sin( $a0 );
					$y0  = 50 - $ar * cos( $a0 );
					$x1  = 50 + $ar * sin( $a1 );
					$y1  = 50 - $ar * cos( $a1 );
					$tx  = cos( $a1 );   // tangent (clockwise) at the tip
					$ty  = sin( $a1 );
					$rx  = sin( $a1 );   // outward radial at the tip
					$ry  = -cos( $a1 );
					printf(
						'<path d="M %.2f %.2f A %d %d 0 0 1 %.2f %.2f"/><path d="M %.2f %.2f L %.2f %.2f L %.2f %.2f"/>',
						$x0, $y0, $ar, $ar, $x1, $y1,
						$x1 - 3.4 * $tx + 2.4 * $rx, $y1 - 3.4 * $ty + 2.4 * $ry,
						$x1, $y1,
						$x1 - 3.4 * $tx - 2.4 * $rx, $y1 - 3.4 * $ty - 2.4 * $ry
					);
				}
				?>
			</g>
			<text class="site-footer__seal-text"><textPath href="#sealRimN" startOffset="50%" text-anchor="middle">CODE</textPath></text>
			<text class="site-footer__seal-text"><textPath href="#sealRimE" startOffset="50%" text-anchor="middle">SNIFF</textPath></text>
			<text class="site-footer__seal-text"><textPath href="#sealRimS" startOffset="50%" text-anchor="middle">EAT</textPath></text>
			<text class="site-footer__seal-text"><textPath href="#sealRimW" startOffset="50%" text-anchor="middle">SLEEP</textPath></text>
		</svg>
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
