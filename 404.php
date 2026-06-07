<?php
/**
 * 404 — "Wrong turn" / lost-in-the-woods.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Quick-escape links shown along the bottom. Icons are inline below by key.
$lost_links = array(
	array(
		'k'     => 'home',
		'href'  => home_url( '/' ),
		'title' => __( 'Go Home', 'the-alpha' ),
		'desc'  => __( 'Return to the main path.', 'the-alpha' ),
	),
	array(
		'k'     => 'blog',
		'href'  => home_url( '/#blog' ),
		'title' => __( 'Read Blog', 'the-alpha' ),
		'desc'  => __( 'Explore stories, thoughts & more.', 'the-alpha' ),
	),
	array(
		'k'     => 'about',
		'href'  => home_url( '/#about' ),
		'title' => __( 'About Me', 'the-alpha' ),
		'desc'  => __( 'Learn more about the journey.', 'the-alpha' ),
	),
	array(
		'k'     => 'contact',
		'href'  => home_url( '/#contact' ),
		'title' => __( 'Contact', 'the-alpha' ),
		'desc'  => __( 'Reach out, I don’t bite.', 'the-alpha' ),
	),
);

/**
 * Inline icon for a lost-link key.
 *
 * @param string $key One of home|blog|about|contact.
 */
function the_alpha_lost_icon( $key ) {
	$icons = array(
		'home'    => '<circle cx="12" cy="12" r="4.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" d="M12 3v2.5m0 13V21M3 12h2.5m13 0H21"/><path fill="currentColor" d="m12 9.4 1.1 1.5 1.5 1.1-1.5 1.1L12 14.6l-1.1-1.5L9.4 12l1.5-1.1z"/>',
		'blog'    => '<rect x="5" y="3.5" width="14" height="17" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" d="M8.5 8h7M8.5 12h7M8.5 16h4"/>',
		'about'   => '<circle cx="12" cy="8" r="3.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" d="M5.5 19.5a6.5 6.5 0 0 1 13 0"/>',
		'contact' => '<rect x="3.5" y="5.5" width="17" height="13" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" d="m4.5 7 7.5 5.5L19.5 7"/>',
	);
	$path = isset( $icons[ $key ] ) ? $icons[ $key ] : '';
	echo '<svg class="lost-link__icon" viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false">' . $path . '</svg>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG markup.
}
?>
<section class="lost" aria-labelledby="lost-title">
	<div class="lost__bg" aria-hidden="true"></div>

	<div class="lost__inner">
		<p class="lost__eyebrow"><?php esc_html_e( 'You are lost in the woods', 'the-alpha' ); ?></p>
		<p class="lost__code" aria-hidden="true">404</p>
		<h1 class="lost__turn" id="lost-title"><span><?php esc_html_e( 'Wrong Turn', 'the-alpha' ); ?></span></h1>
		<p class="lost__lede">
			<?php esc_html_e( 'The path you’re looking for doesn’t exist.', 'the-alpha' ); ?><br>
			<?php esc_html_e( 'But don’t worry, you can find your way back.', 'the-alpha' ); ?>
		</p>
		<a class="btn btn--ghost lost__btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Back to safety', 'the-alpha' ); ?>
			<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M4 12h15m0 0-6-6m6 6-6 6"/></svg>
		</a>
	</div>

	<div class="lost__foot">
		<p class="eyebrow lost__foot-label"><?php esc_html_e( 'Choose the right path', 'the-alpha' ); ?></p>
		<ul class="lost__links" role="list">
			<?php foreach ( $lost_links as $l ) : ?>
				<li>
					<a class="lost-link" href="<?php echo esc_url( $l['href'] ); ?>">
						<?php the_alpha_lost_icon( $l['k'] ); ?>
						<span class="lost-link__title"><?php echo esc_html( $l['title'] ); ?></span>
						<span class="lost-link__desc"><?php echo esc_html( $l['desc'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<blockquote class="lost__quote">
			<p><?php esc_html_e( 'In the woods of code and scent, some paths are better unseen.', 'the-alpha' ); ?></p>
			<cite>&mdash; <?php echo esc_html( get_bloginfo( 'name' ) ); ?></cite>
		</blockquote>
	</div>
</section>
<?php
get_footer();
