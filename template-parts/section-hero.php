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
		<img
			src="<?php echo esc_url( add_query_arg( 'v', the_alpha_asset_ver( 'assets/img/hero.webp' ), THE_ALPHA_URI . '/assets/img/hero.webp' ) ); ?>"
			alt="" width="1614" height="975" fetchpriority="high" decoding="async">
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

			<p class="hero__roles">
				<span class="hero__role"><?php esc_html_e( 'Software Developer', 'the-alpha' ); ?></span>
				<span class="hero__role"><?php esc_html_e( 'Dreamer', 'the-alpha' ); ?></span>
				<span class="hero__role"><?php esc_html_e( 'Fragrance Lover', 'the-alpha' ); ?></span>
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
