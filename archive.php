<?php
/**
 * Archive — category / tag / author / date.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="page-head">
	<div class="wrap">
		<?php if ( is_author() ) : ?>
			<?php
			$ta_author   = get_queried_object();
			$ta_bio      = get_the_author_meta( 'description', $ta_author->ID );
			$ta_count    = count_user_posts( $ta_author->ID, 'post', true );
			$ta_photo_id = (int) get_theme_mod( 'the_alpha_author_card_photo', 0 );
			?>
			<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> / <?php esc_html_e( 'Author', 'the-alpha' ); ?></p>
			<div class="author-card<?php echo $ta_photo_id ? '' : ' author-card--default-photo'; ?>">
				<div class="author-card__media">
					<?php if ( $ta_photo_id ) : ?>
						<?php echo wp_get_attachment_image( $ta_photo_id, 'medium', false, array( 'alt' => $ta_author->display_name, 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
					<?php else : ?>
						<img
							src="<?php echo esc_url( add_query_arg( 'v', the_alpha_asset_ver( 'assets/img/avatar.webp' ), THE_ALPHA_URI . '/assets/img/avatar.webp' ) ); ?>"
							width="140" height="140" alt="<?php echo esc_attr( $ta_author->display_name ); ?>"
							loading="lazy" decoding="async">
					<?php endif; ?>
				</div>

				<div class="author-card__body">
					<p class="eyebrow"><?php esc_html_e( 'Posts by', 'the-alpha' ); ?></p>
					<h1 class="page-title author-card__name"><?php echo esc_html( $ta_author->display_name ); ?></h1>
					<?php if ( $ta_bio ) : ?>
						<div class="author-card__bio"><?php echo wp_kses_post( wpautop( $ta_bio ) ); ?></div>
					<?php endif; ?>
					<?php the_alpha_social_links( 'socials author-card__socials' ); ?>
				</div>

				<div class="author-card__stat">
					<svg class="author-card__glyph" viewBox="0 0 48 48" width="42" height="42" aria-hidden="true" focusable="false">
						<defs>
							<linearGradient id="ta-author-glyph-grad" x1="0" y1="0" x2="1" y2="1">
								<stop offset="0" style="stop-color: var(--accent, #66cdb9)"/>
								<stop offset="1" style="stop-color: var(--accent-2, #8b7cff)"/>
							</linearGradient>
						</defs>
						<g fill="none" stroke="url(#ta-author-glyph-grad)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
							<path d="M33 42H13a4 4 0 0 1-4-4V10a4 4 0 0 1 4-4h16l8 8v8"/>
							<path d="M29 6v8h8"/>
							<path d="M15 20h10M15 27h8M15 34h6"/>
							<path d="m28 40 .9-4.1L39.3 25.5a2.7 2.7 0 0 1 3.8 3.8L32.7 39.7 28 40z"/>
						</g>
					</svg>
					<span class="author-card__count"><?php echo esc_html( number_format_i18n( $ta_count ) ); ?></span>
					<span class="author-card__stat-label"><?php echo esc_html( _n( 'Post', 'Posts', $ta_count, 'the-alpha' ) ); ?></span>
				</div>
			</div>
		<?php else : ?>
			<p class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> / <?php esc_html_e( 'Archive', 'the-alpha' ); ?></p>
			<h1 class="page-title"><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
			<?php
			$ta_desc = get_the_archive_description();
			if ( $ta_desc ) {
				echo '<div class="lede" style="margin-top:1rem;">' . wp_kses_post( $ta_desc ) . '</div>';
			}
			?>
		<?php endif; ?>
	</div>
</div>

<div class="content-grid">
	<div>
		<?php if ( have_posts() ) : ?>
			<div class="post-list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-row' );
				endwhile;
				?>
			</div>
			<?php
			the_posts_pagination( array(
				'mid_size'  => 1,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'class'     => 'pagination',
			) );
			?>
		<?php else : ?>
			<div class="notice"><p><?php esc_html_e( 'Nothing found in this archive.', 'the-alpha' ); ?></p></div>
		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
