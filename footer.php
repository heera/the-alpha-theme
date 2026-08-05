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
		<?php
		/*
		 * The site's machine-readable twins, stated plainly for human readers —
		 * agents already discover these from the Link headers and <head> links
		 * Agentimus emits, so the anchors are nofollow: no reason to hand search
		 * engines a site-wide crawl path into a plain-text file they'd index as a
		 * thin page. Empty (and silent) when the plugin isn't active.
		 */
		$ta_machine = the_alpha_machine_links();
		if ( $ta_machine ) :
			?>
			<p class="site-footer__machine">
				<?php
				$ta_first = true;
				foreach ( $ta_machine as $ta_label => $ta_url ) {
					if ( ! $ta_first ) {
						// Separator lives outside the anchor, so it is neither
						// underlined nor part of the link's hit area.
						echo '<span class="site-footer__machine-sep" aria-hidden="true">&middot;</span>';
					}
					$ta_first = false;
					printf(
						'<a href="%s" target="_blank" rel="nofollow noopener">%s</a>',
						esc_url( $ta_url ),
						esc_html( $ta_label )
					);
				}
				?>
			</p>
		<?php endif; ?>
	</div>

	<?php
	/*
	 * Centre seal — a signature emblem engraved with the daily loop as four
	 * words at the compass points: CODE (N), SNIFF (E), EAT (S), SLEEP (W), so
	 * clockwise from the top it reads as a cycle. Each word rides its own rim arc
	 * (top/bottom upright, sides curving along the rim). Inside: concentric
	 * rings, a compass crosshair, drifting fragrance motes and a glowing core.
	 * Purely decorative, static — no motion.
	 *
	 * Quiet easter egg. Deliberately NOT a real <a href> — that would be crawlable
	 * and bots would follow it straight to the 404. Instead theme.js navigates to
	 * /wrong-turn on click / Enter / Space (a path that doesn't exist, so the
	 * "lost in the woods" 404 answers). role="button" + tabindex keep it
	 * keyboard-reachable; the seal <div> keeps its own accent colour. Hovering
	 * swaps the rim words to WRONG TURN / DON'T CLICK.
	 */
	?>
	<a class="site-footer__seal-link" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Wrong turn', 'the-alpha' ); ?>">
	<div class="site-footer__seal" aria-hidden="true">
		<svg viewBox="-16 -16 132 132" width="124" height="124" fill="none" stroke="currentColor" focusable="false">
			<defs>
				<radialGradient id="sealHaze">
					<stop offset="0%" stop-color="currentColor" stop-opacity="0.13"/>
					<stop offset="100%" stop-color="currentColor" stop-opacity="0"/>
				</radialGradient>
				<!-- One rim arc per word. Top/bottom render upright; the side arcs
				     curve along the rim (Turn reads down the right, Click up the
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
			<g class="site-footer__seal-arrows" stroke-width="0.8" stroke-opacity="0.4" stroke-linecap="round" stroke-linejoin="round">
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
			<g class="site-footer__seal-arrows site-footer__seal-arrows--sides" stroke-width="0.8" stroke-opacity="0.4" stroke-linecap="round" stroke-linejoin="round">
				<?php
				// Left & right arrows — hidden at rest, faded in on hover to flank the
				// WRONG TURN / DON'T CLICK message while the four corner arrows fade out.
				foreach ( array( 90, 270 ) as $c ) {
					$a0  = deg2rad( $c - 13 );
					$a1  = deg2rad( $c + 13 );
					$x0  = 50 + $ar * sin( $a0 );
					$y0  = 50 - $ar * cos( $a0 );
					$x1  = 50 + $ar * sin( $a1 );
					$y1  = 50 - $ar * cos( $a1 );
					$tx  = cos( $a1 );
					$ty  = sin( $a1 );
					$rx  = sin( $a1 );
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
	</a>

	<div class="site-footer__col site-footer__col--right">
		<a class="site-footer__rss" href="<?php echo esc_url( the_alpha_page_url( 'subscribe' ) ); ?>" data-drawer>
			<?php esc_html_e( 'Subscribe to my', 'the-alpha' ); ?>
			<span class="site-footer__rss-slug">&nbsp;/rss</span>
		</a>
		<a class="site-footer__terms" href="<?php echo esc_url( the_alpha_page_url( 'terms' ) ); ?>" data-drawer><?php esc_html_e( 'Terms and Conditions', 'the-alpha' ); ?></a>
		<?php
		/*
		 * Whatever page is set under Settings → Privacy, not a hard-coded slug —
		 * core already owns that pointer, and get_privacy_policy_url() returns ''
		 * when no page is set or the assigned one isn't published, so the line is
		 * simply absent until there's a real page behind it.
		 */
		$ta_privacy = get_privacy_policy_url();
		if ( $ta_privacy ) :
			?>
			<a class="site-footer__privacy" href="<?php echo esc_url( $ta_privacy ); ?>" data-drawer><?php esc_html_e( 'Privacy Policy', 'the-alpha' ); ?></a>
		<?php endif; ?>
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
	<?php /* A <div>, not <aside>: role="dialog" overrides the landmark anyway,
	         and aside's implicit complementary role conflicts with it. */ ?>
	<div class="drawer__panel" role="dialog" aria-modal="true" aria-labelledby="drawer-title" tabindex="-1">
		<header class="drawer__bar">
			<button class="drawer__close" type="button" data-drawer-close aria-label="<?php esc_attr_e( 'Close', 'the-alpha' ); ?>">
				<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
			</button>
		</header>
		<div class="drawer__scroll">
			<h2 class="drawer__title" id="drawer-title" tabindex="-1"></h2>
			<div class="drawer__content prose"></div>
		</div>
	</div>
</div>

<button class="to-top" type="button" aria-label="<?php esc_attr_e( 'Back to top', 'the-alpha' ); ?>">
	<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M12 7.4 5.7 13.7a1 1 0 0 0 1.4 1.4L12 10.2l4.9 4.9a1 1 0 0 0 1.4-1.4L12 7.4z"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
