<?php
/**
 * Single fragrance — a journal entry rendered entirely from cached Fragella
 * data (accords, note pyramid, season/occasion) plus your own prose. The
 * "you may also like" rail is computed locally by accord similarity, so this
 * page never calls Fragella no matter how much traffic it gets.
 *
 * This is a deliberately plain scaffold: real markup hooks are here, the
 * cinematic styling (accord bars, note pyramid, season radar) is step 2.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$f = the_alpha_fragrance_data();
	?>
<div class="page-head">
	<div class="wrap">
		<p class="breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'the-alpha' ); ?></a> /
			<a href="<?php echo esc_url( get_post_type_archive_link( 'fragrance' ) ); ?>"><?php esc_html_e( 'Fragrances', 'the-alpha' ); ?></a>
		</p>
		<?php if ( ! empty( $f['brand'] ) ) : ?>
			<p class="kicker"><?php echo esc_html( $f['brand'] ); ?><?php echo ! empty( $f['year'] ) ? ' &middot; ' . esc_html( $f['year'] ) : ''; ?></p>
		<?php endif; ?>
		<h1 class="page-title"><?php the_title(); ?></h1>
		<hr class="page-head__rule" aria-hidden="true">
	</div>
</div>

<div class="content-grid content-grid--tight">
	<article <?php post_class( 'entry entry--fragrance' ); ?>>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="entry__cover entry__cover--bottle">
				<?php the_post_thumbnail( 'the_alpha_hero', array( 'loading' => 'eager', 'alt' => esc_attr( get_the_title() ) ) ); ?>
			</figure>
		<?php endif; ?>

		<?php if ( $f ) : ?>
			<ul class="frag-facts">
				<?php
				$facts = array(
					__( 'Rating', 'the-alpha' )    => ! empty( $f['rating'] ) ? '&#9733; ' . number_format( (float) $f['rating'], 2 ) : '',
					__( 'Type', 'the-alpha' )      => $f['oiltype'] ?? '',
					__( 'Longevity', 'the-alpha' ) => $f['longevity'] ?? '',
					__( 'Sillage', 'the-alpha' )   => $f['sillage'] ?? '',
					__( 'Gender', 'the-alpha' )    => $f['gender'] ?? '',
				);
				foreach ( $facts as $label => $val ) {
					if ( $val === '' ) {
						continue;
					}
					printf(
						'<li class="frag-fact"><span class="frag-fact__k">%s</span><span class="frag-fact__v">%s</span></li>',
						esc_html( $label ),
						wp_kses_post( $val )
					);
				}
				?>
			</ul>

			<?php if ( ! empty( $f['accords'] ) ) : ?>
				<section class="frag-accords" aria-label="<?php esc_attr_e( 'Main accords', 'the-alpha' ); ?>">
					<h2 class="frag-h"><?php esc_html_e( 'Accords', 'the-alpha' ); ?></h2>
					<ul class="frag-accords__list">
						<?php foreach ( $f['accords'] as $name => $w ) : ?>
							<li class="frag-accord">
								<span class="frag-accord__name"><?php echo esc_html( ucfirst( $name ) ); ?></span>
								<span class="frag-accord__bar" style="--w:<?php echo esc_attr( round( (float) $w * 100 ) ); ?>%"></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $f['notes'] ) ) : ?>
				<section class="frag-notes" aria-label="<?php esc_attr_e( 'Note pyramid', 'the-alpha' ); ?>">
					<h2 class="frag-h"><?php esc_html_e( 'Notes', 'the-alpha' ); ?></h2>
					<?php foreach ( array( 'Top', 'Middle', 'Base' ) as $tier ) : ?>
						<?php if ( ! empty( $f['notes'][ $tier ] ) ) : ?>
							<div class="frag-notes__tier">
								<h3 class="frag-notes__label"><?php echo esc_html( $tier ); ?></h3>
								<ul class="frag-notes__row">
									<?php foreach ( $f['notes'][ $tier ] as $note ) : ?>
										<li class="frag-note"><?php echo esc_html( is_array( $note ) ? ( $note['name'] ?? '' ) : $note ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>

			<?php
			// Seasons + occasions: normalised horizontal bars from the cached
			// ranking scores (restrained, matches the accord bars — no radar gimmick).
			foreach ( array(
				'seasons'   => __( 'Best seasons', 'the-alpha' ),
				'occasions' => __( 'Occasions', 'the-alpha' ),
			) as $key => $heading ) :
				if ( empty( $f[ $key ] ) ) {
					continue;
				}
				$rows = $f[ $key ];
				$max  = 0;
				foreach ( $rows as $row ) {
					$max = max( $max, (float) ( $row['score'] ?? 0 ) );
				}
				if ( $max <= 0 ) {
					continue;
				}
				?>
				<section class="frag-rank" aria-label="<?php echo esc_attr( $heading ); ?>">
					<h2 class="frag-h"><?php echo esc_html( $heading ); ?></h2>
					<ul class="frag-rank__list">
						<?php foreach ( $rows as $row ) : ?>
							<li class="frag-rank__item">
								<span class="frag-rank__name"><?php echo esc_html( ucfirst( (string) ( $row['name'] ?? '' ) ) ); ?></span>
								<span class="frag-rank__bar" style="--w:<?php echo esc_attr( round( (float) ( $row['score'] ?? 0 ) / $max * 100 ) ); ?>%"></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if ( get_the_content() ) : ?>
			<div class="prose entry-content frag-journal">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $f['purchase'] ) ) : ?>
			<p class="frag-buy"><a class="btn btn--ghost" href="<?php echo esc_url( $f['purchase'] ); ?>" rel="nofollow noopener" target="_blank"><?php esc_html_e( 'Where to buy', 'the-alpha' ); ?></a></p>
		<?php endif; ?>

		<?php
		// Local "you may also like" — accord similarity over your own
		// collection. No API call.
		$similar = the_alpha_fragrance_similar( get_the_ID(), 4 );
		if ( $similar ) :
			$rail = new WP_Query( array(
				'post_type'      => 'fragrance',
				'post__in'       => $similar,
				'orderby'        => 'post__in',
				'posts_per_page' => count( $similar ),
				'no_found_rows'  => true,
			) );
			if ( $rail->have_posts() ) :
				?>
				<section class="frag-similar" aria-label="<?php esc_attr_e( 'You may also like', 'the-alpha' ); ?>">
					<h2 class="frag-h"><?php esc_html_e( 'Smells similar', 'the-alpha' ); ?></h2>
					<div class="cards cards--frag">
						<?php while ( $rail->have_posts() ) : $rail->the_post(); get_template_part( 'template-parts/fragrance-card' ); endwhile; ?>
					</div>
				</section>
				<?php
				wp_reset_postdata();
			endif;
		endif;
		?>
	</article>

	<?php get_sidebar(); ?>
</div>
	<?php
endwhile;

get_footer();
