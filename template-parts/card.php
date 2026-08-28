<?php
/**
 * Post card (grid item) — used in the front "Blog" section, search results and
 * the "Continue reading" row under a single post.
 *
 * Pass array( 'variant' => 'related' ) to get the related-row dressing: the
 * category as a pill above the title, and a reading-time/date line at the foot
 * in place of the READ link. Everything else — media, placeholder, stretched
 * title link, hover behaviour — is shared, so there is one card to maintain.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ta_related = isset( $args['variant'] ) && 'related' === $args['variant'];
?>
<article <?php post_class( $ta_related ? 'card card--related' : 'card' ); ?>>
	<?php $ta_thumb = the_alpha_thumb_id(); ?>
	<a class="card__media<?php echo $ta_thumb ? '' : ' card__media--ph'; ?>" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( $ta_thumb ) : ?>
			<?php // The wrapping link is aria-hidden, so this alt never reaches a screen reader (the title link below names the post) — it exists for image search and scanners. ?>
			<?php echo wp_get_attachment_image( $ta_thumb, 'the_alpha_card', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => get_the_title() ) ); ?>
		<?php else : ?>
			<?php
			$ta_cats = get_the_category();
			$ta_kick = $ta_cats ? $ta_cats[0]->name : get_bloginfo( 'name' );
			?>
			<span class="card__ph-kicker"><?php echo esc_html( $ta_kick ); ?></span>
			<span class="card__ph-mark" aria-hidden="true">&lt;/&gt;</span>
		<?php endif; ?>
	</a>
	<div class="card__body">
		<?php
		if ( $ta_related ) {
			the_alpha_card_category();
		} else {
			the_alpha_post_meta();
		}
		?>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php // Related cards run narrower, so they take a shorter excerpt to keep the row from towering. ?>
		<p class="card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $ta_related ? 16 : 22, '&hellip;' ) ); ?></p>
		<?php if ( $ta_related ) : ?>
			<?php the_alpha_card_meta(); ?>
		<?php else : ?>
			<?php // The visible "Read" is identical on every card; the sr-only suffix
			      // names the destination without changing the visible label (2.5.3-safe:
			      // the visible word stays contained in the accessible name). ?>
			<a class="card__more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read', 'the-alpha' ); ?><span class="screen-reader-text"> &mdash; <?php the_title(); ?></span></a>
		<?php endif; ?>
	</div>
</article>
