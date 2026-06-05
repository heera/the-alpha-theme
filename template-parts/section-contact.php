<?php
/**
 * Section: Contact.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="contact" class="section section--alt" aria-label="<?php esc_attr_e( 'Contact', 'the-alpha' ); ?>">
	<?php
	/*
	 * Honeypot: invisible to humans, harvested only by auto-scrapers.
	 * Any inbound mail to this address is provably bot-driven — set up a
	 * Cloudflare Email Routing rule for `web-trap-2026@heera.it` and a
	 * Gmail filter to auto-mark-as-spam (or drop at the router). Rotate
	 * the year suffix when noise starts coming in to keep lists fresh.
	 */
	?>
	<a href="mailto:web-trap-2026@heera.it" rel="nofollow" aria-hidden="true" tabindex="-1"
	   style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;opacity:0">.</a>
	<div class="wrap">
		<div class="reveal" style="margin-bottom:2.4rem;">
			<p class="eyebrow"><?php esc_html_e( 'Contact', 'the-alpha' ); ?></p>
			<h2 class="section-heading"><?php esc_html_e( 'Get in Touch', 'the-alpha' ); ?></h2>
		</div>

		<div class="contact__grid reveal">
			<div class="contact__card">
				<p class="kicker"><?php esc_html_e( 'I’m kind of an introvert — but not always, though!', 'the-alpha' ); ?></p>
				<h3><?php esc_html_e( 'Want to connect? Just saying.', 'the-alpha' ); ?></h3>
				<p><?php esc_html_e( 'Outside of work stuff, you’re welcome to reach out — but fair warning, I run on my own clock. A quick call first is a good idea. Dropping by is fine too, just don’t expect military precision. Punctuality? Not exactly my superpower.', 'the-alpha' ); ?></p>
				<ul class="contact__list">
					<li><span><?php esc_html_e( 'Dial', 'the-alpha' ); ?></span><a class="js-tel" data-p="Kzg4MCAxNzE3IDU0MjYwOA==" href="#" aria-label="<?php esc_attr_e( 'Reveal phone number', 'the-alpha' ); ?>"><?php esc_html_e( 'Tap to call', 'the-alpha' ); ?></a></li>
					<li><span><?php esc_html_e( 'Personal', 'the-alpha' ); ?></span><a class="js-mail" data-m="bWFpbEBoZWVyYS5pdA==" href="#" aria-label="<?php esc_attr_e( 'Reveal email address', 'the-alpha' ); ?>"><?php esc_html_e( 'Tap to email', 'the-alpha' ); ?></a></li>
					<li><span><?php esc_html_e( 'Company', 'the-alpha' ); ?></span><a class="js-mail" data-m="aGVlcmFAYXV0aGxhYi5pbw==" href="#" aria-label="<?php esc_attr_e( 'Reveal email address', 'the-alpha' ); ?>"><?php esc_html_e( 'Tap to email', 'the-alpha' ); ?></a></li>
					<li><span><?php esc_html_e( 'Public', 'the-alpha' ); ?></span><a class="js-mail" data-m="aGVlcmEuc2hlaWtoNzdAZ21haWwuY29t" href="#" aria-label="<?php esc_attr_e( 'Reveal email address', 'the-alpha' ); ?>"><?php esc_html_e( 'Tap to email', 'the-alpha' ); ?></a></li>
					<li><span><?php esc_html_e( 'Hideout', 'the-alpha' ); ?></span><a href="https://maps.app.goo.gl/Lfb2kVSmxukend5f7" target="_blank" rel="noopener">14 Duel, Masimpur (Modern Bari), Sylhet &mdash; 3100</a></li>
					<li><span><?php esc_html_e( 'Hours', 'the-alpha' ); ?></span><?php esc_html_e( 'I live by mood, not the clock — calls may go unanswered', 'the-alpha' ); ?></li>
				</ul>
			</div>

			<div class="contact__card">
				<p class="kicker"><?php esc_html_e( 'Up for business — or just coffee with startups!', 'the-alpha' ); ?></p>
				<h3><?php esc_html_e( 'The “office” side of things', 'the-alpha' ); ?></h3>
				<p><?php esc_html_e( 'This is where the real stuff happens (or at least where we pretend it does). Whether you’re looking to discuss a project, collaborate on something cool, or just curious about the space we call “work” — here’s how to reach us.', 'the-alpha' ); ?></p>
				<?php
				/*
				 * Live office availability — derived from the posted hours so the
				 * "Hours" row reflects reality instead of a static string. Office
				 * sits in Sylhet (Asia/Dhaka); open Mon–Fri, 09:00–18:00, minus any
				 * holidays. JS keeps it honest if the page stays open across a
				 * boundary (theme.js).
				 */
				$office_open_h  = 9;
				$office_close_h = 18;
				/*
				 * Days the office is shut even though they fall on a weekday — each a
				 * Y-m-d date in the office timezone. Edit this list, or hook the
				 * `the_alpha_office_holidays` filter, e.g.
				 *   add_filter( 'the_alpha_office_holidays', fn() => array( '2026-12-16' ) );
				 */
				$office_holidays = array_values( (array) apply_filters( 'the_alpha_office_holidays', array() ) );

				$office_now = new DateTimeImmutable( 'now', new DateTimeZone( 'Asia/Dhaka' ) );

				// A working day = Mon–Fri AND not a listed holiday.
				$office_is_working = static function ( DateTimeImmutable $d ) use ( $office_holidays ) {
					return (int) $d->format( 'N' ) <= 5 && ! in_array( $d->format( 'Y-m-d' ), $office_holidays, true );
				};

				$office_min           = (int) $office_now->format( 'G' ) * 60 + (int) $office_now->format( 'i' );
				$office_today_holiday = in_array( $office_now->format( 'Y-m-d' ), $office_holidays, true );
				$office_is_open       = ( $office_is_working( $office_now )
					&& $office_min >= $office_open_h * 60 && $office_min < $office_close_h * 60 );

				if ( $office_is_open ) {
					$office_status_label = __( 'Available now', 'the-alpha' );
				} elseif ( $office_today_holiday ) {
					$office_status_label = __( 'Holiday', 'the-alpha' );
				} else {
					$office_status_label = __( 'Away', 'the-alpha' );
				}

				$office_reopen = '';
				if ( ! $office_is_open ) {
					// Walk forward to the next working day's opening (skips weekends
					// and holidays — up to a fortnight to clear a holiday cluster).
					for ( $i = 0; $i <= 14; $i++ ) {
						$cand = $office_now->modify( "+{$i} day" )->setTime( $office_open_h, 0 );
						if ( $office_is_working( $cand ) && $cand > $office_now ) {
							$days = (int) $office_now->setTime( 0, 0 )->diff( $cand->setTime( 0, 0 ) )->format( '%a' );
							if ( 0 === $days ) {
								$office_reopen = __( 'opens 09:00', 'the-alpha' );
							} elseif ( 1 === $days ) {
								$office_reopen = __( 'opens tomorrow, 09:00', 'the-alpha' );
							} else {
								/* translators: %s: weekday name, e.g. Monday. */
								$office_reopen = sprintf( __( 'opens %s, 09:00', 'the-alpha' ), $cand->format( 'l' ) );
							}
							break;
						}
					}
				}
				?>
				<ul class="contact__list">
					<li><span><?php esc_html_e( 'Dial', 'the-alpha' ); ?></span><a class="js-tel" data-p="Kzg4MCAxOTc5IDc5MTAwMQ==" href="#" aria-label="<?php esc_attr_e( 'Reveal phone number', 'the-alpha' ); ?>"><?php esc_html_e( 'Tap to call', 'the-alpha' ); ?></a></li>
					<li><span><?php esc_html_e( 'Web', 'the-alpha' ); ?></span><a href="https://authlab.io/" target="_blank" rel="noopener">authlab.io</a></li>
					<li><span><?php esc_html_e( 'Social', 'the-alpha' ); ?></span><a href="https://www.facebook.com/authLab" target="_blank" rel="noopener">authLab on Facebook</a></li>
					<li><span><?php esc_html_e( 'WP Brand', 'the-alpha' ); ?></span><a href="https://x.com/WPManageNinja" target="_blank" rel="noopener">WPManageNinja on X</a></li>
					<li><span><?php esc_html_e( 'HQ', 'the-alpha' ); ?></span><a href="https://goo.gl/maps/sarZNHkiQtkrXtf5A" target="_blank" rel="noopener">24/A Shah Farid Rd, Sylhet</a></li>
					<li>
						<span><?php esc_html_e( 'Hours', 'the-alpha' ); ?></span>
						<span class="contact__hours">
							<span class="contact__status <?php echo $office_is_open ? 'is-open' : 'is-closed'; ?>"
								data-tz="Asia/Dhaka" data-open="<?php echo esc_attr( $office_open_h ); ?>" data-close="<?php echo esc_attr( $office_close_h ); ?>"
								data-holidays="<?php echo esc_attr( implode( ',', $office_holidays ) ); ?>"
								data-label-open="<?php esc_attr_e( 'Available now', 'the-alpha' ); ?>"
								data-label-away="<?php esc_attr_e( 'Away', 'the-alpha' ); ?>"
								data-label-holiday="<?php esc_attr_e( 'Holiday', 'the-alpha' ); ?>">
								<span class="contact__status-dot" aria-hidden="true"></span>
								<span class="contact__status-text"><?php echo esc_html( $office_status_label ); ?></span>
								<?php if ( ! $office_is_open && $office_reopen ) : ?>
									<span class="contact__status-detail">· <?php echo esc_html( $office_reopen ); ?></span>
								<?php endif; ?>
							</span>
							<span class="contact__hours-sched">· <?php esc_html_e( 'Mon–Fri, 9 AM–6 PM (hopefully)', 'the-alpha' ); ?></span>
						</span>
					</li>
				</ul>
			</div>
		</div>
	</div>
</section>
