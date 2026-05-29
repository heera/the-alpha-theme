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
		width="960" height="1200"
		loading="lazy" decoding="async">

	<div class="wrap">
		<header class="about__head reveal">
			<p class="eyebrow"><?php esc_html_e( 'Software Developer, Dreamer & Fragrance Enthusiast', 'the-alpha' ); ?></p>
			<h2 class="section-heading about__heading"><?php esc_html_e( 'Building tech and chasing scents', 'the-alpha' ); ?></h2>
		</header>

		<div class="about__grid reveal">
			<figure class="portrait">
				<img
					src="<?php echo esc_url( THE_ALPHA_URI . '/assets/img/portrait.webp?v=' . the_alpha_asset_ver( 'assets/img/portrait.webp' ) ); ?>"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
					width="768" height="768" loading="lazy" decoding="async">
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

				<ul class="about__links" role="list">
					<li><span class="k">Stack Overflow:</span> <span class="v">/the-alpha</span></li>
					<li><span class="k">Facebook:</span> <span class="v">/sheikh.heera</span></li>
					<li><span class="k">LinkedIn:</span> <span class="v">/sheikh-heera</span></li>
					<li><span class="k">X (Twitter):</span> <span class="v">/@heerasheikh</span></li>
					<li><span class="k">GitHub:</span> <span class="v">/heera</span></li>
					<li><span class="k">Pinterest:</span> <span class="v">/sheikhheera</span></li>
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
