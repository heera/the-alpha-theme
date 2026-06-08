<?php
/**
 * Fragrance card (grid item) — used on the /fragrances/ archive, accord/house
 * archives, and the single-page "you may also like" rail. Reads only cached
 * local data; never touches Fragella.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ta_f      = the_alpha_fragrance_data();
$ta_thumb  = the_alpha_thumb_id();
$ta_brand  = $ta_f['brand'] ?? '';
$ta_rating = ! empty( $ta_f['rating'] ) ? number_format( (float) $ta_f['rating'], 2 ) : '';
?>
<article <?php post_class( 'card card--fragrance' ); ?>>
	<a class="card__media<?php echo $ta_thumb ? '' : ' card__media--ph'; ?>" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( $ta_thumb ) : ?>
			<?php echo wp_get_attachment_image( $ta_thumb, 'the_alpha_card', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
		<?php else : ?>
			<span class="card__ph-kicker"><?php echo esc_html( $ta_brand ?: get_bloginfo( 'name' ) ); ?></span>
			<span class="card__ph-mark" aria-hidden="true">&#9826;</span>
		<?php endif; ?>
	</a>
	<div class="card__body">
		<?php if ( $ta_brand ) : ?>
			<p class="card__kicker"><?php echo esc_html( $ta_brand ); ?></p>
		<?php endif; ?>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( $ta_rating ) : ?>
			<p class="card__meta"><span class="frag-rating">&#9733; <?php echo esc_html( $ta_rating ); ?></span></p>
		<?php endif; ?>
	</div>
</article>
