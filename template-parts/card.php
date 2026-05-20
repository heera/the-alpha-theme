<?php
/**
 * Post card (grid item) — used in the front "Blog" section and search results.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'card' ); ?>>
	<a class="card__media<?php echo has_post_thumbnail() ? '' : ' card__media--ph'; ?>" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'the_alpha_card', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
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
		<?php the_alpha_post_meta(); ?>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '&hellip;' ) ); ?></p>
		<a class="card__more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read', 'the-alpha' ); ?></a>
	</div>
</article>
