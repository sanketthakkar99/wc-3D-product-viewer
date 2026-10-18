<?php
/**
 * Clean up on uninstall. Uploaded model files stay in the Media Library.
 *
 * @package WC3D
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$wc3d_options = array(
	'wc3d_display',
	'wc3d_load',
	'wc3d_auto_rotate',
	'wc3d_ar',
	'wc3d_fullscreen',
	'wc3d_bg_color',
	'wc3d_aspect_ratio',
	'wc3d_shadow',
	'wc3d_exposure',
	'wc3d_thumb_label',
	'wc3d_gallery_mode',
	'wc3d_main_selector',
	'wc3d_thumbs_selector',
);

foreach ( $wc3d_options as $wc3d_option ) {
	delete_option( $wc3d_option );
}

delete_post_meta_by_key( '_wc3d_models' );
delete_post_meta_by_key( '_wc3d_model_id' );
delete_post_meta_by_key( '_wc3d_thumb_id' );
