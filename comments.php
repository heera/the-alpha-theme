<?php
/**
 * Comments — native WordPress rendering. Designed to be transparently
 * replaced by a third-party comments plugin (e.g. the official Disqus
 * Comment System) via the `comments_template` filter when installed.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<section class="comments" id="comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments__title">
			<?php
			$ta_count = (int) get_comments_number();
			/* translators: %d: comment count. */
			printf( esc_html( _n( '%d Comment', '%d Comments', $ta_count, 'the-alpha' ) ), (int) $ta_count );
			?>
		</h2>

		<ol class="commentlist">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'avatar_size' => 44,
				'short_ping'  => true,
			) );
			?>
		</ol>

		<?php
		the_comments_pagination( array(
			'prev_text' => __( '&larr; Older', 'the-alpha' ),
			'next_text' => __( 'Newer &rarr;', 'the-alpha' ),
			'class'     => 'pagination',
		) );
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="lede"><?php esc_html_e( 'Comments are closed.', 'the-alpha' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'class_submit' => 'submit btn btn--primary',
		'title_reply'  => __( 'Leave a reply', 'the-alpha' ),
	) );
	?>
</section>
