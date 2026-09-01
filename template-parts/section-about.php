<?php
/**
 * Section: About + Fragrance.
 * Copy carried over (and lightly tidied) from the previous site.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="about" class="section section--alt" aria-label="<?php esc_attr_e( 'About', 'the-alpha' ); ?>">
	<img
		class="section-smoke"
		src="<?php echo esc_url( THE_ALPHA_URI . '/assets/img/bakhoor.webp' ); ?>"
		alt=""
		aria-hidden="true"
		width="760" height="950"
		loading="lazy" decoding="async">

	<div class="wrap">
		<header class="about__head reveal">
			<p class="eyebrow"><?php esc_html_e( 'Software Developer, Fragrance Lover & Dreamer', 'the-alpha' ); ?></p>
			<h2 class="section-heading about__heading"><?php esc_html_e( 'Building tech and chasing scents', 'the-alpha' ); ?></h2>
		</header>

		<div class="about__grid reveal">
			<figure class="portrait">
				<?php
				$portrait_ver   = the_alpha_asset_ver( 'assets/img/portrait.webp' );
				$portrait_lver  = the_alpha_asset_ver( 'assets/img/portrait-light.webp' );
				$portrait_url   = THE_ALPHA_URI . '/assets/img/portrait';
				$portrait_sizes = '(max-width: 760px) 92vw, 340px';
				// Two purpose-graded photos: a dark studio shot for dark mode and a
				// bright high-key shot for light mode, swapped by [data-theme] in CSS
				// (the theme toggle is manual, so prefers-color-scheme can't drive it).
				?>
				<img class="portrait__photo portrait__photo--dark"
					src="<?php echo esc_url( $portrait_url . '.webp?v=' . $portrait_ver ); ?>"
					srcset="<?php echo esc_url( $portrait_url . '-480.webp?v=' . $portrait_ver ) . ' 480w, ' . esc_url( $portrait_url . '-768.webp?v=' . $portrait_ver ) . ' 768w, ' . esc_url( $portrait_url . '.webp?v=' . $portrait_ver ) . ' 880w'; ?>"
					sizes="<?php echo esc_attr( $portrait_sizes ); ?>"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
					width="880" height="880" loading="lazy" decoding="async">
				<img class="portrait__photo portrait__photo--light"
					src="<?php echo esc_url( $portrait_url . '-light.webp?v=' . $portrait_lver ); ?>"
					srcset="<?php echo esc_url( $portrait_url . '-light-480.webp?v=' . $portrait_lver ) . ' 480w, ' . esc_url( $portrait_url . '-light-768.webp?v=' . $portrait_lver ) . ' 768w, ' . esc_url( $portrait_url . '-light.webp?v=' . $portrait_lver ) . ' 880w'; ?>"
					sizes="<?php echo esc_attr( $portrait_sizes ); ?>"
					alt="" aria-hidden="true"
					width="880" height="880" loading="lazy" decoding="async">
					<?php // One-shot scanner sweep, fired by .about__grid.in (see main.css). ?>
					<span class="portrait__scan" aria-hidden="true"></span>
					<?php // HUD status: "Verifying…" rides the sweep, then resolves into the typed identity "The Alpha" (top-left, see main.css). ?>
					<span class="portrait__authing" aria-hidden="true"><?php echo esc_html_x( 'Verifying…', 'portrait HUD scan in progress', 'the-alpha' ); ?></span>
					<span class="portrait__auth" aria-hidden="true"><?php echo esc_html_x( 'The Alpha', 'portrait HUD identified subject', 'the-alpha' ); ?></span>
					<?php // Counter-stamp at the opposite top corner: a dossier "verified" mark (tick + label) that strikes in once the subject resolves. ?>
					<span class="portrait__verified" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="12" height="12" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" d="M4 12.6 9.4 18 20 6.4"/></svg>
						<span><?php echo esc_html_x( 'Verified', 'portrait HUD verification stamp', 'the-alpha' ); ?></span>
					</span>
				<?php
				// Cryptic HUD readout overlaid on the photo: each digit of the year (see bio
				// below), is encoded as its own 8-bit byte, echoing the artwork's baked-in scanner
				// motif: 1 9 7 7 -> 00000001 00001001 00000111 00000111.
				$since_year = 1977;
				?>
				<?php // role="img": aria-label is prohibited on a generic <span> — with the
				      // image role the label ("Since 1977") is announced instead of the binary. ?>
				<span class="portrait__code" role="img"
					aria-label="<?php /* translators: %d: year. */ printf( esc_attr__( 'Since %d', 'the-alpha' ), $since_year ); ?>">
					<?php echo esc_html( implode( '-', array_map( static function ( $d ) { return str_pad( decbin( (int) $d ), 8, '0', STR_PAD_LEFT ); }, str_split( (string) $since_year ) ) ) ); ?>
				</span>
				<?php // Dossier HUD overlays (decorative): readout, status gauge, waveform — all --portrait-hud, revealed after the scan (see main.css). ?>
				<div class="portrait__readout" aria-hidden="true">
					<span><?php // Same spot the portrait's edge text names — Machimpur, Sylhet (Plus Code VVMH+F3F). ?><?php echo esc_html_x( 'LOC: N24.8868° E91.8795°', 'portrait HUD readout', 'the-alpha' ); ?></span>
					<span><?php echo esc_html_x( 'ID: [REDACTED]', 'portrait HUD readout', 'the-alpha' ); ?></span>
					<span><?php echo esc_html_x( 'PSYCH: STABLE', 'portrait HUD readout', 'the-alpha' ); ?> <i class="portrait__bars">▂▄▆▅</i></span>
					<span><?php echo esc_html_x( 'CLASS: PRIORITY', 'portrait HUD readout', 'the-alpha' ); ?> <i class="portrait__bars">▁▃▅▇</i></span>
				</div>
				<svg class="portrait__gauge" viewBox="0 0 100 100" aria-hidden="true" focusable="false">
					<circle cx="50" cy="50" r="46" fill="none" stroke="currentColor" stroke-opacity="0.32" stroke-width="2.5" stroke-dasharray="1.4 6.2"/>
					<circle cx="50" cy="50" r="39" fill="none" stroke="currentColor" stroke-opacity="0.16" stroke-width="3.5"/>
					<circle cx="50" cy="50" r="39" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-dasharray="184 245" transform="rotate(-90 50 50)"/>
					<text class="portrait__gauge-cap" x="50" y="46" text-anchor="middle" fill="currentColor"><?php echo esc_html_x( 'Status', 'portrait HUD gauge caption', 'the-alpha' ); ?></text>
					<text class="portrait__gauge-val" x="50" y="63" text-anchor="middle" fill="currentColor"><?php echo esc_html_x( 'STABLE', 'portrait HUD gauge value', 'the-alpha' ); ?></text>
				</svg>
				<?php // Voice-print waveform, framed by an open HUD corner-bracket on the left (line-art, no box)
				      // rather than a frosted panel. The bars are Morse — short bar = dot, tall bar = dash, wide
				      // gap = next letter — and spell SYLHETI: ··· −·−− ·−·· ···· · − ·· ?>
				<svg class="portrait__wave" viewBox="-22 -8 173 56" aria-hidden="true" focusable="false">
					<path class="portrait__wave-frame" d="M-6 -3 L-13 -3 L-18 2 L-18 38 L-13 43 L-6 43" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
					<path d="M5 14 L5 26 M11 15 L11 25 M17 16 L17 24 M29 3 L29 37 M35 14 L35 26 M41 4 L41 36 M47 5 L47 35 M59 15 L59 25 M65 3 L65 37 M71 16 L71 24 M77 14 L77 26 M89 15 L89 25 M95 16 L95 24 M101 14 L101 26 M107 15 L107 25 M119 16 L119 24 M131 4 L131 36 M143 14 L143 26 M149 15 L149 25" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
				</svg>
			</figure>

			<div>
				<div class="bio">
					<p>
						<?php
						echo wp_kses_post(
							__( 'I&rsquo;m Heera (Sheikh Heera) &mdash; aka &ldquo;The Alpha&rdquo; &mdash; a programmer and system architect with a deep-rooted passion for building scalable, high-performance applications. I currently serve as <strong>CTO</strong> of <a href="https://authlab.io/" target="_blank" rel="noopener">authLab</a>, where I lead the company&rsquo;s technical vision and oversee the infrastructure behind next-generation solutions.', 'the-alpha' )
						);
						?>
					</p>
					<p>
						<?php
						echo wp_kses_post(
							__( 'I began learning programming in 1995, and over the decades my work has spanned everything from architecting complex systems to mentoring emerging developers. As one of the top contributors from Bangladesh on <a href="https://stackoverflow.com/users/741747/the-alpha" target="_blank" rel="noopener">Stack Overflow</a>, I&rsquo;ve stayed active sharing knowledge with the global developer community.', 'the-alpha' )
						);
						?>
					</p>
					<p>
						<?php esc_html_e( 'Outside the screens, I bring the same architectural attention to scent — layering oud, smoke, and memory the way I’d layer systems.', 'the-alpha' ); ?>
					</p>
				</div>

				<?php
				// Social manifest — real, clickable identity links rendered as a
				// monospace "credentials" block (terminal motif, see main.css).
				$socials = array(
					array( 'k' => 'Stack Overflow', 'v' => '/the-alpha',    'href' => 'https://stackoverflow.com/users/741747/the-alpha' ),
					array( 'k' => 'Facebook',       'v' => '/sheikh.heera',  'href' => 'https://www.facebook.com/sheikh.heera' ),
					array( 'k' => 'LinkedIn',       'v' => '/sheikh-heera',  'href' => 'https://www.linkedin.com/in/sheikh-heera' ),
					array( 'k' => 'X (Twitter)',    'v' => '/@heerasheikh',  'href' => 'https://x.com/heerasheikh' ),
					array( 'k' => 'GitHub',         'v' => '/heera',         'href' => 'https://github.com/heera' ),
					array( 'k' => 'Pinterest',      'v' => '/sheikhheera',   'href' => 'https://www.pinterest.com/sheikhheera' ),
				);
				?>
				<p class="about__links-label"><?php esc_html_e( 'Find me on the web', 'the-alpha' ); ?></p>
				<ul class="about__links" role="list">
					<?php foreach ( $socials as $s ) : ?>
						<li>
							<a class="about__link" href="<?php echo esc_url( $s['href'] ); ?>" target="_blank" rel="me noopener">
								<span class="k"><?php echo esc_html( $s['k'] ); ?>:</span>
								<span class="v"><?php echo esc_html( $s['v'] ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

		<div class="frag reveal">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Passion', 'the-alpha' ); ?></p>
				<h2 class="section-heading"><?php esc_html_e( 'Where scent meets architecture', 'the-alpha' ); ?></h2>
				<p class="lede">
					<?php esc_html_e( 'Fragrance became another form of architecture for me — built not with frameworks and servers, but with smoke, wood, resin, and memory. What started in childhood, sparked by my father, became a long-running ritual: burning bakhoor, layering oils, returning to the same notes the way I return to the same systems.', 'the-alpha' ); ?>
				</p>
				<p class="lede" style="margin-top:1rem;">
					<?php
					echo esc_html__( 'My world leans toward deep, cold-weather compositions — ', 'the-alpha' );
					foreach ( array( 'Patchouli', 'Vanilla', 'Leather', 'Wood', 'Tobacco', 'Oud', 'Whiskey' ) as $note ) {
						echo '<span class="chip">' . esc_html( $note ) . '</span>';
					}
					echo esc_html__( ' — layered with incense, drifting smoke, and the kind of resin that hangs in a room long after you leave it.', 'the-alpha' );
					// Fragrantica woven into the fragrance prose (not the About
					// "Find me" developer-identity grid). Worded as a presence —
					// the public wardrobe there isn't kept current.
					printf(
						/* translators: %s: linked "Fragrantica" profile. */
						' ' . wp_kses( __( 'You&rsquo;ll also find me over on %s.', 'the-alpha' ), array() ),
						'<a class="frag__profile-link" href="' . esc_url( 'https://www.fragrantica.com/member/1374163' ) . '" target="_blank" rel="me noopener">' . esc_html__( 'Fragrantica', 'the-alpha' ) . '</a>'
					);
					?>
				</p>
			</div>

			<div class="frag__media">
				<p class="frag__quote"><?php esc_html_e( 'Fragrance is the silent poetry of soul', 'the-alpha' ); ?></p>
			</div>
		</div>
	</div>
</section>
