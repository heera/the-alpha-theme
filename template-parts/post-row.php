<?php
/**
 * Post (blog / archive / search listings) — full-width banner image on top,
 * meta, title, excerpt. The whole row is clickable via a stretched link on
 * the title (single accessible link, no separate "Read More").
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'post-row' ); ?>>
	<?php if ( get_theme_mod( 'the_alpha_show_featured_listing', true ) ) : ?>
		<?php $ta_cover_id = the_alpha_thumb_id(); ?>
		<div class="post-row__media card__media<?php echo $ta_cover_id ? '' : ' card__media--ph'; ?>">
			<?php if ( $ta_cover_id ) : ?>
				<?php echo wp_get_attachment_image( $ta_cover_id, 'the_alpha_banner', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
			<?php else : ?>
				<?php
				$ta_cats = get_the_category();
				$ta_kick = $ta_cats ? $ta_cats[0]->name : get_bloginfo( 'name' );
				?>
				<span class="card__ph-kicker"><?php echo esc_html( $ta_kick ); ?></span>
				<span class="card__ph-mark" aria-hidden="true">&lt;/&gt;</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<p class="post-row__meta">
		<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		<?php
		$ta_cat_links = get_the_category_list( ', ' );
		if ( $ta_cat_links ) {
			echo ' <span class="sep" aria-hidden="true">|</span> ' . wp_kses_post( $ta_cat_links );
		}
		?>
	</p>

	<h2 class="post-row__title">
		<a class="post-row__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>

	<p class="post-row__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 45, '&hellip;' ) ); ?></p>

	<a class="post-row__more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'the-alpha' ); ?></a>
</article>
