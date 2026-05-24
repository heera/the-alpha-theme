<?php
/**
 * Per-post "Banner image" — a wide cover image for the single-post header and
 * the blog listing rows, kept separate from the Featured image (which drives the
 * grid card). This lets a tall poster stay whole in the card while a purpose-made
 * wide banner fills the header without cropping.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the cover image id for a post: the dedicated banner if set, otherwise
 * the featured image.
 *
 * @param int|null $post_id Optional post id; defaults to the current post.
 * @return int Attachment id, or 0 if neither is set.
 */
function the_alpha_cover_id( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$banner  = (int) get_post_meta( $post_id, '_the_alpha_banner_id', true );
	// Use the banner only if it still points to a real image; otherwise fall back
	// to the featured image (the banner attachment may have been deleted on a
	// re-upload, which would otherwise blank the cover).
	if ( $banner && wp_attachment_is_image( $banner ) ) {
		return $banner;
	}
	return the_alpha_thumb_id( $post_id );
}

/**
 * The featured image id, but only if it still points to a real image — returns 0
 * otherwise. Guards against a deleted attachment leaving a dangling
 * _thumbnail_id, which makes has_post_thumbnail() true while rendering nothing
 * (so the placeholder never shows).
 *
 * @param int|null $post_id Optional post id; defaults to the current post.
 * @return int Valid attachment id, or 0.
 */
function the_alpha_thumb_id( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$id      = (int) get_post_thumbnail_id( $post_id );
	return ( $id && wp_attachment_is_image( $id ) ) ? $id : 0;
}

/**
 * Register the Banner image meta box on posts and pages.
 */
function the_alpha_banner_meta_box() {
	foreach ( array( 'post', 'page' ) as $screen ) {
		add_meta_box(
			'the_alpha_banner',
			__( 'Banner image', 'the-alpha' ),
			'the_alpha_banner_meta_box_cb',
			$screen,
			'side',
			'low'
		);
	}
}
add_action( 'add_meta_boxes', 'the_alpha_banner_meta_box' );

/**
 * Meta box UI: image preview + select/remove buttons backed by a hidden id.
 *
 * @param WP_Post $post Current post.
 */
function the_alpha_banner_meta_box_cb( $post ) {
	wp_nonce_field( 'the_alpha_banner_save', 'the_alpha_banner_nonce' );
	$id  = (int) get_post_meta( $post->ID, '_the_alpha_banner_id', true );
	$src = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	?>
	<p class="description" style="margin:0 0 .6rem;">
		<?php esc_html_e( 'Wide image for the single-post header and blog listing. The Featured image is still used for the grid card. If empty, the Featured image is used here too.', 'the-alpha' ); ?>
	</p>
	<div class="the-alpha-banner-preview" style="margin:0 0 .6rem;">
		<?php if ( $src ) : ?>
			<img src="<?php echo esc_url( $src ); ?>" alt="" style="max-width:100%;height:auto;display:block;border-radius:4px;" />
		<?php endif; ?>
	</div>
	<input type="hidden" id="the_alpha_banner_id" name="the_alpha_banner_id" value="<?php echo esc_attr( (string) $id ); ?>" />
	<button type="button" class="button the-alpha-banner-select"><?php esc_html_e( 'Select banner', 'the-alpha' ); ?></button>
	<button type="button" class="button-link the-alpha-banner-remove" style="margin-left:.6rem;color:#b32d2e;<?php echo $id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'the-alpha' ); ?></button>
	<?php
}

/**
 * Persist the selected banner id.
 *
 * @param int $post_id Post being saved.
 */
function the_alpha_banner_save( $post_id ) {
	if ( ! isset( $_POST['the_alpha_banner_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['the_alpha_banner_nonce'] ) ), 'the_alpha_banner_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$id = isset( $_POST['the_alpha_banner_id'] ) ? absint( $_POST['the_alpha_banner_id'] ) : 0;
	if ( $id ) {
		update_post_meta( $post_id, '_the_alpha_banner_id', $id );
	} else {
		delete_post_meta( $post_id, '_the_alpha_banner_id' );
	}
}
add_action( 'save_post', 'the_alpha_banner_save' );

/**
 * Load the media frame + a small picker script on the post editor only.
 *
 * @param string $hook Current admin page.
 */
function the_alpha_banner_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_script(
		'jquery-core',
		<<<'JS'
jQuery(function($){
	$(document).on('click','.the-alpha-banner-select',function(e){
		e.preventDefault();
		var frame = wp.media({ title:'Select banner image', multiple:false, library:{ type:'image' }, button:{ text:'Use this image' } });
		frame.on('select', function(){
			var a = frame.state().get('selection').first().toJSON();
			var url = (a.sizes && a.sizes.medium) ? a.sizes.medium.url : a.url;
			$('#the_alpha_banner_id').val(a.id);
			$('.the-alpha-banner-preview').html('<img src="'+url+'" alt="" style="max-width:100%;height:auto;display:block;border-radius:4px;" />');
			$('.the-alpha-banner-remove').show();
		});
		frame.open();
	});
	$(document).on('click','.the-alpha-banner-remove',function(e){
		e.preventDefault();
		$('#the_alpha_banner_id').val('');
		$('.the-alpha-banner-preview').empty();
		$(this).hide();
	});
});
JS
	);
}
add_action( 'admin_enqueue_scripts', 'the_alpha_banner_admin_assets' );
