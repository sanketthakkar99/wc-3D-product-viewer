<?php
/**
 * Helper functions.
 *
 * @package WC3D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for every plugin option.
 *
 * @return array
 */
function wc3d_defaults() {
	return array(
		'wc3d_display'      => 'gallery',  // gallery | shortcode.
		'wc3d_load'         => 'visible',  // visible | page.
		'wc3d_auto_rotate'  => 'yes',
		'wc3d_ar'           => 'yes',
		'wc3d_fullscreen'   => 'yes',
		'wc3d_bg_color'     => '#f4f4f4',
		'wc3d_aspect_ratio' => '1/1',
		'wc3d_shadow'       => '1',
		'wc3d_exposure'     => '1',
		'wc3d_thumb_label'  => '3D',
		'wc3d_gallery_mode' => 'auto',     // auto | woocommerce | theme.
		'wc3d_main_selector'   => '.product-image-slider',
		'wc3d_thumbs_selector' => '.product-thumbs-slider, .product-thumbs-vertical-slider',
	);
}

/**
 * Get a plugin option with its default.
 *
 * @param string $key Option name.
 * @return string
 */
function wc3d_option( $key ) {
	$defaults = wc3d_defaults();
	$value    = get_option( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
	return ( '' === $value && isset( $defaults[ $key ] ) ) ? $defaults[ $key ] : $value;
}

/**
 * Themes that replace the WooCommerce gallery with their own slider.
 *
 * @return string[] Theme (template) folder names.
 */
function wc3d_theme_slider_themes() {
	return (array) apply_filters( 'wc3d_theme_slider_themes', array( 'porto' ) );
}

/**
 * Which gallery integration to use: "woocommerce" (standard FlexSlider gallery) or
 * "theme" (the theme's own slider, e.g. Porto's Owl Carousel).
 *
 * @return string
 */
function wc3d_gallery_mode() {
	$mode = wc3d_option( 'wc3d_gallery_mode' );
	if ( 'auto' === $mode || ! in_array( $mode, array( 'woocommerce', 'theme' ), true ) ) {
		$mode = in_array( get_template(), wc3d_theme_slider_themes(), true ) ? 'theme' : 'woocommerce';
	}
	return (string) apply_filters( 'wc3d_gallery_mode', $mode );
}

/**
 * Keep CSS selectors safe for an HTML attribute / querySelector.
 *
 * @param string $selector Raw selector.
 * @return string
 */
function wc3d_sanitize_selector( $selector ) {
	$selector = wp_strip_all_tags( (string) $selector );
	return trim( preg_replace( '/[<>{};\\`]/', '', $selector ) );
}

/**
 * Allowed 3D file extensions.
 *
 * @return string[]
 */
function wc3d_allowed_extensions() {
	return array( 'glb', 'gltf' );
}

/**
 * Is the given attachment a GLB / glTF file?
 *
 * @param int $attachment_id Attachment ID.
 * @return bool
 */
function wc3d_is_model_attachment( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
		return false;
	}
	$file = get_attached_file( $attachment_id );
	$ext  = strtolower( pathinfo( (string) $file, PATHINFO_EXTENSION ) );
	return in_array( $ext, wc3d_allowed_extensions(), true );
}

/**
 * Resolve the product that owns the models (variations use their parent).
 *
 * @param WC_Product|int $product Product or ID.
 * @return WC_Product|null
 */
function wc3d_resolve_product( $product ) {
	$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
	if ( ! $product instanceof WC_Product ) {
		return null;
	}
	if ( $product->is_type( 'variation' ) ) {
		$parent = wc_get_product( $product->get_parent_id() );
		return $parent ? $parent : null;
	}
	return $product;
}

/**
 * All 3D models of a product, in display order.
 *
 * Each item: array( 'id' => int, 'thumb' => int, 'label' => string ).
 * Products saved with version 1.0 (single model) are read transparently.
 *
 * @param WC_Product|int $product Product or ID.
 * @return array[]
 */
function wc3d_get_models( $product ) {
	$product = wc3d_resolve_product( $product );
	if ( ! $product ) {
		return array();
	}

	$raw = $product->get_meta( WC3D_META_MODELS, true );
	if ( ! is_array( $raw ) || ! $raw ) {
		// Legacy (1.0.x) single-model meta.
		$legacy = absint( $product->get_meta( WC3D_META_MODEL, true ) );
		$raw    = $legacy ? array(
			array(
				'id'    => $legacy,
				'thumb' => absint( $product->get_meta( WC3D_META_THUMB, true ) ),
				'label' => '',
			),
		) : array();
	}

	$models = array();
	foreach ( $raw as $item ) {
		$id = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
		if ( ! wc3d_is_model_attachment( $id ) ) {
			continue;
		}
		$models[] = array(
			'id'    => $id,
			'thumb' => isset( $item['thumb'] ) ? absint( $item['thumb'] ) : 0,
			'label' => isset( $item['label'] ) ? (string) $item['label'] : '',
		);
	}

	/**
	 * Filter a product's 3D models.
	 *
	 * @param array[]    $models  Models.
	 * @param WC_Product $product Product.
	 */
	return (array) apply_filters( 'wc3d_product_models', $models, $product );
}

/**
 * First model attachment ID (0 if none). Kept for backward compatibility.
 *
 * @param WC_Product|int $product Product or ID.
 * @return int
 */
function wc3d_get_model_id( $product ) {
	$models = wc3d_get_models( $product );
	return $models ? $models[0]['id'] : 0;
}

/**
 * Does the product have at least one 3D model?
 *
 * @param WC_Product|int $product Product or ID.
 * @return bool
 */
function wc3d_product_has_model( $product ) {
	return (bool) wc3d_get_models( $product );
}

/**
 * URL of the default "3D" thumbnail icon.
 *
 * @return string
 */
function wc3d_default_thumb_url() {
	return WC3D_URL . 'assets/images/3d-thumb.svg';
}

/**
 * Build the <model-viewer> markup for one model of a product.
 *
 * @param WC_Product $product Product.
 * @param array      $model   Model item from wc3d_get_models().
 * @param array      $args    Optional overrides: context (gallery|standalone), height, auto_rotate, ar, bg_color, index, total.
 * @return string HTML (empty string when the model is invalid).
 */
function wc3d_get_viewer_html( $product, $model, $args = array() ) {
	$model_id = isset( $model['id'] ) ? absint( $model['id'] ) : 0;
	if ( ! wc3d_is_model_attachment( $model_id ) ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'context'     => 'gallery',
			'height'      => '',
			'auto_rotate' => wc3d_option( 'wc3d_auto_rotate' ),
			'ar'          => wc3d_option( 'wc3d_ar' ),
			'bg_color'    => wc3d_option( 'wc3d_bg_color' ),
			'index'       => 1,
			'total'       => 1,
		)
	);

	$src      = wp_get_attachment_url( $model_id );
	$thumb_id = absint( $model['thumb'] );
	$poster   = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'woocommerce_single' ) : '';
	$bg       = sanitize_hex_color( $args['bg_color'] );
	$ratio    = wc3d_sanitize_ratio( wc3d_option( 'wc3d_aspect_ratio' ) );

	$style = '--wc3d-bg:' . ( $bg ? $bg : '#f4f4f4' ) . ';--wc3d-ratio:' . str_replace( '/', ' / ', $ratio ) . ';';
	if ( $args['height'] ) {
		$style .= '--wc3d-height:' . preg_replace( '/[^0-9a-z.%]/i', '', $args['height'] ) . ';';
	}

	$attrs = array(
		'class'            => 'wc3d-viewer',
		'src'              => esc_url_raw( $src ),
		'alt'              => wc3d_model_alt( $product, $model, $args['index'], $args['total'] ),
		'camera-controls'  => null,
		'touch-action'     => 'pan-y',
		'loading'          => 'lazy',
		'shadow-intensity'   => (string) (float) wc3d_option( 'wc3d_shadow' ),
		'exposure'           => (string) (float) wc3d_option( 'wc3d_exposure' ),
		'interaction-prompt' => 'auto',
	);
	if ( 'yes' === $args['auto_rotate'] ) {
		$attrs['auto-rotate'] = null;
	}
	if ( 'yes' === $args['ar'] ) {
		$attrs['ar']       = null;
		$attrs['ar-modes'] = 'webxr scene-viewer quick-look';
	}

	/**
	 * Filter <model-viewer> attributes. Use null as the value for boolean attributes.
	 *
	 * @param array      $attrs   Attributes.
	 * @param WC_Product $product Product.
	 * @param array      $args    Render args.
	 * @param array      $model   Model item.
	 */
	$attrs = apply_filters( 'wc3d_model_viewer_attributes', $attrs, $product, $args, $model );

	$attr_html = '';
	foreach ( $attrs as $name => $value ) {
		$name = preg_replace( '/[^a-z0-9\-]/', '', strtolower( $name ) );
		$attr_html .= null === $value ? ' ' . $name : sprintf( ' %s="%s"', $name, esc_attr( $value ) );
	}

	ob_start();
	?>
	<div class="wc3d-wrap wc3d-wrap--<?php echo esc_attr( $args['context'] ); ?>" style="<?php echo esc_attr( $style ); ?>">
		<model-viewer<?php echo $attr_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>>
			<div slot="poster" class="wc3d-poster<?php echo $poster ? ' has-image' : ''; ?>"<?php echo $poster ? ' style="background-image:url(' . esc_url( $poster ) . ')"' : ''; ?>>
				<span class="wc3d-poster__badge"><?php esc_html_e( 'Loading 3D model…', 'wc-3d-product-viewer' ); ?></span>
			</div>
			<div slot="progress-bar" class="wc3d-progress" aria-hidden="true"><div class="wc3d-progress__bar"></div></div>
			<?php if ( 'yes' === $args['ar'] ) : ?>
				<button slot="ar-button" type="button" class="wc3d-ar-button">
					<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 2 3 7v10l9 5 9-5V7l-9-5Zm0 2.3L18.6 8 12 11.7 5.4 8 12 4.3ZM5 9.7l6 3.4v6.6l-6-3.3V9.7Zm8 10v-6.6l6-3.4v6.7l-6 3.3Z"/></svg>
					<?php esc_html_e( 'View in your space', 'wc-3d-product-viewer' ); ?>
				</button>
			<?php endif; ?>
		</model-viewer>
		<div class="wc3d-toolbar">
			<button type="button" class="wc3d-btn wc3d-reset" title="<?php esc_attr_e( 'Reset view', 'wc-3d-product-viewer' ); ?>" aria-label="<?php esc_attr_e( 'Reset view', 'wc-3d-product-viewer' ); ?>">
				<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 5V2L7 6l5 4V7a5 5 0 1 1-5 5H5a7 7 0 1 0 7-7Z"/></svg>
			</button>
			<?php if ( 'yes' === wc3d_option( 'wc3d_fullscreen' ) ) : ?>
				<button type="button" class="wc3d-btn wc3d-fullscreen" title="<?php esc_attr_e( 'Full screen', 'wc-3d-product-viewer' ); ?>" aria-label="<?php esc_attr_e( 'Full screen', 'wc-3d-product-viewer' ); ?>">
					<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M4 4h6v2H6v4H4V4Zm10 0h6v6h-2V6h-4V4ZM4 14h2v4h4v2H4v-6Zm14 0h2v6h-6v-2h4v-4Z"/></svg>
				</button>
			<?php endif; ?>
		</div>
		<span class="wc3d-hint"><?php esc_html_e( 'Drag to rotate · Scroll or pinch to zoom', 'wc-3d-product-viewer' ); ?></span>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Keep aspect ratio values like "1/1", "4/3" or "16/9".
 *
 * @param string $ratio Raw ratio.
 * @return string
 */
function wc3d_sanitize_ratio( $ratio ) {
	$ratio = preg_replace( '/\s+/', '', (string) $ratio );
	return preg_match( '#^\d+(\.\d+)?/\d+(\.\d+)?$#', $ratio ) ? $ratio : '1/1';
}

/**
 * Accessible name for a model.
 *
 * @param WC_Product $product Product.
 * @param array      $model   Model item.
 * @param int        $index   1-based position.
 * @param int        $total   Number of models.
 * @return string
 */
function wc3d_model_alt( $product, $model, $index = 1, $total = 1 ) {
	if ( ! empty( $model['label'] ) ) {
		/* translators: 1: model label, 2: product name */
		return sprintf( __( '3D model: %1$s – %2$s', 'wc-3d-product-viewer' ), $model['label'], $product->get_name() );
	}
	if ( $total > 1 ) {
		/* translators: 1: model number, 2: total models, 3: product name */
		return sprintf( __( '3D model %1$d of %2$d – %3$s', 'wc-3d-product-viewer' ), $index, $total, $product->get_name() );
	}
	/* translators: %s: product name */
	return sprintf( __( '3D model of %s', 'wc-3d-product-viewer' ), $product->get_name() );
}
