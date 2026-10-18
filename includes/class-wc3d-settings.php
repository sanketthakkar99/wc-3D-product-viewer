<?php
/**
 * Settings: WooCommerce → Settings → Products → 3D Viewer.
 *
 * @package WC3D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings section.
 */
class WC3D_Settings {

	/**
	 * Hook in.
	 */
	public static function init() {
		add_filter( 'woocommerce_get_sections_products', array( __CLASS__, 'add_section' ) );
		add_filter( 'woocommerce_get_settings_products', array( __CLASS__, 'settings' ), 10, 2 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_wc3d_aspect_ratio', 'wc3d_sanitize_ratio' );
		add_filter( 'woocommerce_admin_settings_sanitize_option_wc3d_shadow', array( __CLASS__, 'sanitize_float' ) );
		add_filter( 'woocommerce_admin_settings_sanitize_option_wc3d_exposure', array( __CLASS__, 'sanitize_float' ) );
		add_filter( 'woocommerce_admin_settings_sanitize_option_wc3d_main_selector', 'wc3d_sanitize_selector' );
		add_filter( 'woocommerce_admin_settings_sanitize_option_wc3d_thumbs_selector', 'wc3d_sanitize_selector' );
	}

	/**
	 * Register the section.
	 *
	 * @param array $sections Sections.
	 * @return array
	 */
	public static function add_section( $sections ) {
		$sections['wc3d'] = __( '3D Viewer', 'wc-3d-product-viewer' );
		return $sections;
	}

	/**
	 * Keep numbers between 0 and 5.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_float( $value ) {
		return (string) max( 0, min( 5, (float) $value ) );
	}

	/**
	 * Settings fields.
	 *
	 * @param array  $settings        Existing settings.
	 * @param string $current_section Section ID.
	 * @return array
	 */
	public static function settings( $settings, $current_section ) {
		if ( 'wc3d' !== $current_section ) {
			return $settings;
		}

		$d = wc3d_defaults();

		return array(
			array(
				'title' => __( '3D Product Viewer', 'wc-3d-product-viewer' ),
				'type'  => 'title',
				'desc'  => __( 'Add one or more GLB / glTF files in the "3D Models" box on any product. Each model appears as its own slide at the end of the product gallery. For themes with a custom gallery, use the shortcode <code>[wc_3d_viewer]</code> on the product template.', 'wc-3d-product-viewer' ),
				'id'    => 'wc3d_options',
			),
			array(
				'title'   => __( 'Where to show the models', 'wc-3d-product-viewer' ),
				'id'      => 'wc3d_display',
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => $d['wc3d_display'],
				'options' => array(
					'gallery'   => __( 'Separate slides at the end of the product gallery', 'wc-3d-product-viewer' ),
					'shortcode' => __( 'Only where I place the [wc_3d_viewer] shortcode', 'wc-3d-product-viewer' ),
				),
			),
			array(
				'title'    => __( 'Gallery type', 'wc-3d-product-viewer' ),
				'id'       => 'wc3d_gallery_mode',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'desc'     => sprintf(
					/* translators: %s: detected mode */
					__( 'Currently using: %s', 'wc-3d-product-viewer' ),
					'<strong>' . ( 'theme' === wc3d_gallery_mode() ? __( "theme's own slider", 'wc-3d-product-viewer' ) : __( 'WooCommerce standard gallery', 'wc-3d-product-viewer' ) ) . '</strong>'
				),
				'default'  => $d['wc3d_gallery_mode'],
				'options'  => array(
					'auto'        => __( 'Auto-detect (recommended)', 'wc-3d-product-viewer' ),
					'woocommerce' => __( 'WooCommerce standard gallery', 'wc-3d-product-viewer' ),
					'theme'       => __( "Theme's own slider (Porto, Owl Carousel, …)", 'wc-3d-product-viewer' ),
				),
			),
			array(
				'title'    => __( 'Theme slider selector', 'wc-3d-product-viewer' ),
				'desc_tip' => __( "Only for \"Theme's own slider\": CSS selector of the element that holds the main product image slides. Porto: .product-image-slider", 'wc-3d-product-viewer' ),
				'id'       => 'wc3d_main_selector',
				'type'     => 'text',
				'css'      => 'min-width:24em;',
				'default'  => $d['wc3d_main_selector'],
			),
			array(
				'title'    => __( 'Theme thumbnails selector', 'wc-3d-product-viewer' ),
				'desc_tip' => __( "Only for \"Theme's own slider\": CSS selector of the element that holds the thumbnails. Type none if the gallery has no thumbnails.", 'wc-3d-product-viewer' ),
				'id'       => 'wc3d_thumbs_selector',
				'type'     => 'text',
				'css'      => 'min-width:24em;',
				'default'  => $d['wc3d_thumbs_selector'],
			),
			array(
				'title'   => __( 'Load the 3D engine', 'wc-3d-product-viewer' ),
				'id'      => 'wc3d_load',
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'desc_tip' => __( 'The 3D engine is about 1 MB. Loading it only when the shopper opens the 3D slide keeps product pages fast.', 'wc-3d-product-viewer' ),
				'default' => $d['wc3d_load'],
				'options' => array(
					'visible' => __( 'When the 3D slide is opened (recommended)', 'wc-3d-product-viewer' ),
					'page'    => __( 'As soon as the page loads', 'wc-3d-product-viewer' ),
				),
			),
			array(
				'title'   => __( 'Auto-rotate', 'wc-3d-product-viewer' ),
				'desc'    => __( 'Slowly spin the model until the shopper interacts with it', 'wc-3d-product-viewer' ),
				'id'      => 'wc3d_auto_rotate',
				'type'    => 'checkbox',
				'default' => $d['wc3d_auto_rotate'],
			),
			array(
				'title'   => __( 'Augmented reality', 'wc-3d-product-viewer' ),
				'desc'    => __( 'Show a "View in your space" button on supported phones (Android Scene Viewer, iOS Quick Look)', 'wc-3d-product-viewer' ),
				'id'      => 'wc3d_ar',
				'type'    => 'checkbox',
				'default' => $d['wc3d_ar'],
			),
			array(
				'title'   => __( 'Full-screen button', 'wc-3d-product-viewer' ),
				'desc'    => __( 'Let shoppers open the 3D viewer full screen', 'wc-3d-product-viewer' ),
				'id'      => 'wc3d_fullscreen',
				'type'    => 'checkbox',
				'default' => $d['wc3d_fullscreen'],
			),
			array(
				'title'   => __( 'Background color', 'wc-3d-product-viewer' ),
				'id'      => 'wc3d_bg_color',
				'type'    => 'color',
				'css'     => 'width:7em;',
				'default' => $d['wc3d_bg_color'],
			),
			array(
				'title'    => __( 'Viewer aspect ratio', 'wc-3d-product-viewer' ),
				'desc_tip' => __( 'Width/height, e.g. 1/1 to match square product images, or 4/3.', 'wc-3d-product-viewer' ),
				'id'       => 'wc3d_aspect_ratio',
				'type'     => 'text',
				'css'      => 'width:7em;',
				'default'  => $d['wc3d_aspect_ratio'],
			),
			array(
				'title'             => __( 'Shadow intensity', 'wc-3d-product-viewer' ),
				'desc_tip'          => __( '0 = no shadow, 1 = normal.', 'wc-3d-product-viewer' ),
				'id'                => 'wc3d_shadow',
				'type'              => 'number',
				'css'               => 'width:7em;',
				'custom_attributes' => array( 'min' => 0, 'max' => 5, 'step' => 0.1 ),
				'default'           => $d['wc3d_shadow'],
			),
			array(
				'title'             => __( 'Exposure (brightness)', 'wc-3d-product-viewer' ),
				'desc_tip'          => __( '1 = normal. Raise it if models look dark.', 'wc-3d-product-viewer' ),
				'id'                => 'wc3d_exposure',
				'type'              => 'number',
				'css'               => 'width:7em;',
				'custom_attributes' => array( 'min' => 0, 'max' => 5, 'step' => 0.1 ),
				'default'           => $d['wc3d_exposure'],
			),
			array(
				'title'    => __( 'Thumbnail badge text', 'wc-3d-product-viewer' ),
				'desc_tip' => __( 'Shown on a 3D gallery thumbnail that uses a custom image and has no label of its own.', 'wc-3d-product-viewer' ),
				'id'       => 'wc3d_thumb_label',
				'type'     => 'text',
				'css'      => 'width:7em;',
				'default'  => $d['wc3d_thumb_label'],
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wc3d_options',
			),
		);
	}
}
