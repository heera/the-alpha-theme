<?php
/**
 * Section: Home / Hero.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="home" class="hero" aria-label="<?php esc_attr_e( 'Introduction', 'the-alpha' ); ?>">
	<div class="hero__bg">
		<?php // One full landscape desk shot everywhere. Desktop fills it edge-to-edge (cover); mobile shows the WHOLE scene scaled down (contain) so nothing is cropped — see .hero__bg img in the max-width:760px block. ?>
		<img
			src="<?php echo esc_url( add_query_arg( 'v', the_alpha_asset_ver( 'assets/img/hero.webp' ), THE_ALPHA_URI . '/assets/img/hero.webp' ) ); ?>"
			alt="" width="1324" height="882" fetchpriority="high" decoding="async">
	</div>

	<div class="hero__inner">
		<p class="hero__hi"><?php esc_html_e( 'Hello, I build & I write', 'the-alpha' ); ?></p>
		<div class="hero__head">
			<h1 class="hero__title">
				<?php
				$name  = (string) get_bloginfo( 'name' );
				$parts = explode( ' ', $name, 2 );
				echo esc_html( $parts[0] );
				if ( ! empty( $parts[1] ) ) {
					echo ' <span class="grad">' . esc_html( $parts[1] ) . '</span>';
				}
				?>
			</h1>

			<?php
			// Roles read as one tracked line on desktop; on phones (where the hero
			// has spare vertical room) they stack into an icon list — see the
			// max-width:760px block in main.css. Icons are decorative (aria-hidden)
			// and hidden on desktop.
			?>
			<p class="hero__roles">
				<span class="hero__role">
					<svg class="hero__role-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m8 8-4 4 4 4"/><path d="m16 8 4 4-4 4"/><path d="m13.5 5.5-3 13"/></svg>
					<?php esc_html_e( 'Software Developer', 'the-alpha' ); ?>
				</span>
				<span class="hero__role">
					<svg class="hero__role-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="8" y="10.5" width="8" height="9.5" rx="2.2"/><path d="M10.5 10.5V8h3v2.5"/><rect x="10" y="5" width="4" height="3" rx="0.8"/><path d="M16.5 5.5h2M16.8 8h2.2"/></svg>
					<?php esc_html_e( 'Fragrance Lover', 'the-alpha' ); ?>
				</span>
				<span class="hero__role">
					<svg class="hero__role-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 18h9.5a3.5 3.5 0 0 0 .4-6.98 5 5 0 0 0-9.55-1.3A4 4 0 0 0 7 18Z"/></svg>
					<?php esc_html_e( 'Dreamer', 'the-alpha' ); ?>
				</span>
			</p>
		</div>

		<div class="hero__cta">
			<a class="btn btn--primary" href="#blog"><?php esc_html_e( 'Read the blog', 'the-alpha' ); ?></a>
			<a class="btn btn--ghost" href="#about"><?php esc_html_e( 'About me', 'the-alpha' ); ?></a>
		</div>
	</div>

	<a class="hero__scroll" href="#about" aria-label="<?php esc_attr_e( 'Scroll down', 'the-alpha' ); ?>">
		<span></span>
		<?php esc_html_e( 'Scroll', 'the-alpha' ); ?>
	</a>
</section>
