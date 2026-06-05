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
			<p class="eyebrow"><?php esc_html_e( 'Software Developer, Dreamer & Fragrance Enthusiast', 'the-alpha' ); ?></p>
			<h2 class="section-heading about__heading"><?php esc_html_e( 'Building tech and chasing scents', 'the-alpha' ); ?></h2>
		</header>

		<div class="about__grid reveal">
			<figure class="portrait">
				<?php
				$portrait_ver = the_alpha_asset_ver( 'assets/img/portrait.webp' );
				$portrait_url = THE_ALPHA_URI . '/assets/img/portrait';
				?>
				<img
					src="<?php echo esc_url( $portrait_url . '.webp?v=' . $portrait_ver ); ?>"
					srcset="<?php echo esc_url( $portrait_url . '-480.webp?v=' . $portrait_ver ) . ' 480w, ' . esc_url( $portrait_url . '-768.webp?v=' . $portrait_ver ) . ' 768w, ' . esc_url( $portrait_url . '.webp?v=' . $portrait_ver ) . ' 880w'; ?>"
					sizes="(max-width: 760px) 92vw, 340px"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
					width="880" height="880" loading="lazy" decoding="async">
					<?php // One-shot scanner sweep, fired by .about__grid.in (see main.css). ?>
					<span class="portrait__scan" aria-hidden="true"></span>
					<?php // "AUTHORIZED" confirmation, fades in after the sweep (see main.css). ?>
					<span class="portrait__auth" aria-hidden="true"><?php echo esc_html_x( 'Authorized', 'portrait HUD scan confirmation', 'the-alpha' ); ?></span>
				<?php
				// Cryptic HUD readout overlaid on the photo: each digit of the year (see bio
				// below), is encoded as its own 8-bit byte, echoing the artwork's baked-in scanner
				// motif: 1 9 7 7 -> 00000001 00001001 00000111 00000111.
				$since_year = 1977;
				?>
				<span class="portrait__code"
					aria-label="<?php /* translators: %d: year. */ printf( esc_attr__( 'Since %d', 'the-alpha' ), $since_year ); ?>">
					<?php echo esc_html( implode( '-', array_map( static function ( $d ) { return str_pad( decbin( (int) $d ), 8, '0', STR_PAD_LEFT ); }, str_split( (string) $since_year ) ) ) ); ?>
				</span>
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
				<p class="about__links-label"><?php esc_html_e( 'Find me', 'the-alpha' ); ?></p>
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
					foreach ( array( 'Whiskey', 'Oud', 'Leather', 'Patchouli', 'Vanilla', 'Tobacco' ) as $note ) {
						echo '<span class="chip">' . esc_html( $note ) . '</span>';
					}
					echo esc_html__( ' — layered with incense, drifting smoke, and the kind of resin that hangs in a room long after you leave it.', 'the-alpha' );
					?>
				</p>
			</div>

			<div class="frag__media">
				<p class="frag__quote"><?php esc_html_e( 'Fragrance is the silent poetry of soul', 'the-alpha' ); ?></p>
			</div>
		</div>
	</div>
</section>
