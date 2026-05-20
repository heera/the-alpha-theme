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
		<p class="site-footer__copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		<p class="site-footer__sub">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: WP Manage Ninja link. */
					__( 'Proudly powered by - %s', 'the-alpha' ),
					'<a href="https://wpmanageninja.com/" target="_blank" rel="noopener">WP Manage Ninja</a>'
				)
			);
			?>
		</p>
	</div>

	<div class="site-footer__col site-footer__col--right">
		<a class="site-footer__rss" href="<?php echo esc_url( home_url( '/subscribe/' ) ); ?>">
			<?php esc_html_e( 'Subscribe', 'the-alpha' ); ?>
			<span class="site-footer__rss-slug">&nbsp;/rss</span>
		</a>
		<a class="site-footer__terms" href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms and Conditions', 'the-alpha' ); ?></a>
	</div>
</footer>

<button class="to-top" type="button" aria-label="<?php esc_attr_e( 'Back to top', 'the-alpha' ); ?>">
	<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M12 7.4 5.7 13.7a1 1 0 0 0 1.4 1.4L12 10.2l4.9 4.9a1 1 0 0 0 1.4-1.4L12 7.4z"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
