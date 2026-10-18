<?php
/**
 * Front end: 3D slide in the product gallery + shortcode.
 *
 * @package WC3D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front end.
 */
class WC3D_Frontend {

	/**
	 * Product IDs whose gallery slide was already printed.
	 *
	 * @var int[]
	 */
	protected static $printed = array();

	/**
	 * Hook in.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );

		/*
		 * woocommerce_product_thumbnails fires inside .woocommerce-product-gallery__wrapper,
		 * after the main image. The gallery images are printed at priority 20, so priority 99
		 * makes the 3D model the LAST slide (and last thumbnail).
		 */
		add_action( 'woocommerce_product_thumbnails', array( __CLASS__, 'gallery_slide' ), apply_filters( 'wc3d_gallery_slide_priority', 99 ) );
		add_filter( 'woocommerce_single_product_image_gallery_classes', array( __CLASS__, 'gallery_classes' ) );

		/*
		 * Themes with their own slider (Porto, …): the product images are printed by
		 * woocommerce_show_product_images at priority 20 of this hook. Printing right after it
		 * (priority 25) lets the inline injector add the 3D slides before the theme starts its slider.
		 */
		add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'theme_slider_slides' ), apply_filters( 'wc3d_theme_slider_priority', 25 ) );

		add_shortcode( 'wc_3d_viewer', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Register (and on product pages, enqueue) assets.
	 */
	public static function register_assets() {
		wp_register_style( 'wc3d-frontend', WC3D_URL . 'assets/css/frontend.css', array(), WC3D_VERSION );
		wp_register_script( 'wc3d-frontend', WC3D_URL . 'assets/js/frontend.js', array(), WC3D_VERSION, true );
		wp_localize_script(
			'wc3d-frontend',
			'wc3dSettings',
			array(
				'viewerUrl' => apply_filters(
					'wc3d_model_viewer_script_url',
					WC3D_URL . 'assets/vendor/model-viewer/model-viewer.min.js?ver=' . WC3D_MODEL_VIEWER_VERSION
				),
				'load'      => wc3d_option( 'wc3d_load' ),
				'i18n'      => array(
					'error' => __( 'Sorry, the 3D model could not be loaded.', 'wc-3d-product-viewer' ),
				),
			)
		);

		if ( is_product() ) {
			$product = wc_get_product( get_queried_object_id() );
			if ( $product && wc3d_product_has_model( $product ) ) {
				self::enqueue();
			}
		}
	}

	/**
	 * Enqueue front-end assets.
	 */
	public static function enqueue() {
		wp_enqueue_style( 'wc3d-frontend' );
		wp_enqueue_script( 'wc3d-frontend' );
	}

	/**
	 * Add a class to the gallery wrapper when a model exists.
	 *
	 * @param array $classes Classes.
	 * @return array
	 */
	public static function gallery_classes( $classes ) {
		global $product;
		if ( 'gallery' === wc3d_option( 'wc3d_display' ) && 'woocommerce' === wc3d_gallery_mode() && $product instanceof WC_Product && wc3d_product_has_model( $product ) ) {
			$classes[] = 'wc3d-has-model';
		}
		return $classes;
	}

	/**
	 * Should the gallery slides be printed for this product right now?
	 *
	 * @param string $mode Required gallery mode.
	 * @return WC_Product|null
	 */
	protected static function product_for_gallery( $mode ) {
		global $product;

		if ( 'gallery' !== wc3d_option( 'wc3d_display' ) || $mode !== wc3d_gallery_mode() || ! $product instanceof WC_Product ) {
			return null;
		}

		$id = $product->get_id();
		if ( in_array( $id, self::$printed, true ) || ! wc3d_product_has_model( $product ) ) {
			return null;
		}

		/**
		 * Short-circuit the gallery slides for a product.
		 *
		 * @param bool       $show    Show the slides.
		 * @param WC_Product $product Product.
		 */
		if ( ! apply_filters( 'wc3d_show_gallery_slide', true, $product ) ) {
			return null;
		}

		self::$printed[] = $id;
		self::enqueue();
		return $product;
	}

	/**
	 * Data for every 3D slide of a product.
	 *
	 * @param WC_Product $product Product.
	 * @return array[] Each: thumb, alt, badge, index, html.
	 */
	protected static function slides( $product ) {
		$models = wc3d_get_models( $product );
		$total  = count( $models );
		$slides = array();

		foreach ( $models as $i => $model ) {
			$thumb_url  = $model['thumb'] ? wp_get_attachment_image_url( $model['thumb'], 'woocommerce_gallery_thumbnail' ) : '';
			$has_custom = (bool) $thumb_url;
			if ( ! $thumb_url ) {
				$thumb_url = wc3d_default_thumb_url();
			}

			// Badge on the thumbnail: the label if set, otherwise "3D" (custom image) or the number (several models).
			$badge = $model['label'];
			if ( '' === $badge ) {
				if ( $has_custom ) {
					$badge = wc3d_option( 'wc3d_thumb_label' ) . ( $total > 1 ? ' ' . ( $i + 1 ) : '' );
				} elseif ( $total > 1 ) {
					$badge = (string) ( $i + 1 );
				}
			}

			$slides[] = array(
				'model' => $model,
				'thumb' => $thumb_url,
				'alt'   => wc3d_model_alt( $product, $model, $i + 1, $total ),
				'badge' => $badge,
				'index' => (int) $i,
				'html'  => wc3d_get_viewer_html(
					$product,
					$model,
					array(
						'context' => 'gallery',
						'index'   => $i + 1,
						'total'   => $total,
					)
				),
			);
		}
		return $slides;
	}

	/**
	 * Slide HTML.
	 *
	 * @param array      $slide   Slide data.
	 * @param string     $classes Extra classes.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	protected static function slide_html( $slide, $classes, $product ) {
		$html = sprintf(
			'<div data-thumb="%1$s" data-thumb-alt="%2$s" data-wc3d-badge="%3$s" data-wc3d-index="%4$d" class="%5$s">%6$s</div>',
			esc_url( $slide['thumb'] ),
			esc_attr( $slide['alt'] ),
			esc_attr( $slide['badge'] ),
			$slide['index'],
			esc_attr( $classes ),
			$slide['html']
		);

		/**
		 * Filter one 3D gallery slide's HTML.
		 *
		 * @param string     $html    HTML.
		 * @param WC_Product $product Product.
		 * @param array      $model   Model item.
		 * @param int        $i       0-based index.
		 */
		return (string) apply_filters( 'wc3d_gallery_slide_html', $html, $product, $slide['model'], $slide['index'] );
	}

	/**
	 * WooCommerce standard gallery (FlexSlider): print the slides as the last gallery items.
	 */
	public static function gallery_slide() {
		$product = self::product_for_gallery( 'woocommerce' );
		if ( ! $product ) {
			return;
		}
		foreach ( self::slides( $product ) as $slide ) {
			echo self::slide_html( $slide, 'woocommerce-product-gallery__image wc3d-gallery-slide', $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped when built.
		}
	}

	/**
	 * Theme slider (Porto, …): print hidden slides + the inline injector right after the gallery.
	 */
	public static function theme_slider_slides() {
		$product = self::product_for_gallery( 'theme' );
		if ( ! $product ) {
			return;
		}

		$html = '';
		foreach ( self::slides( $product ) as $slide ) {
			$html .= self::slide_html( $slide, 'wc3d-src', $product );
		}

		printf(
			'<div class="wc3d-pending" hidden data-main="%1$s" data-thumbs="%2$s">%3$s</div>',
			esc_attr( wc3d_option( 'wc3d_main_selector' ) ),
			esc_attr( 'none' === strtolower( trim( wc3d_option( 'wc3d_thumbs_selector' ) ) ) ? '' : wc3d_option( 'wc3d_thumbs_selector' ) ),
			$html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped when built.
		);

		$js = file_get_contents( WC3D_PATH . 'assets/js/theme-gallery.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( function_exists( 'wp_print_inline_script_tag' ) ) {
			wp_print_inline_script_tag( $js, array( 'id' => 'wc3d-theme-gallery-' . $product->get_id() ) );
		} else {
			echo '<script>' . $js . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static plugin file.
		}
	}

	/**
	 * [wc_3d_viewer product_id="123" model="2" height="480px" auto_rotate="yes" ar="yes" bg_color="#ffffff"]
	 *
	 * Without product_id it uses the current product. Without model it shows every model, one after another.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'product_id'  => 0,
				'model'       => '',
				'height'      => '',
				'auto_rotate' => wc3d_option( 'wc3d_auto_rotate' ),
				'ar'          => wc3d_option( 'wc3d_ar' ),
				'bg_color'    => wc3d_option( 'wc3d_bg_color' ),
			),
			$atts,
			'wc_3d_viewer'
		);

		$product_id = absint( $atts['product_id'] );
		if ( ! $product_id ) {
			global $product;
			$product_id = $product instanceof WC_Product ? $product->get_id() : ( is_singular( 'product' ) ? get_queried_object_id() : 0 );
		}

		$the_product = $product_id ? wc_get_product( $product_id ) : null;
		$models      = $the_product ? wc3d_get_models( $the_product ) : array();
		if ( ! $models ) {
			return '';
		}

		$total = count( $models );
		$pick  = absint( $atts['model'] );
		if ( $pick ) {
			if ( ! isset( $models[ $pick - 1 ] ) ) {
				return '';
			}
			$models = array( $pick - 1 => $models[ $pick - 1 ] );
		}

		self::enqueue();

		$html = '';
		foreach ( $models as $i => $model ) {
			$html .= wc3d_get_viewer_html(
				$the_product,
				$model,
				array(
					'context'     => 'standalone',
					'height'      => sanitize_text_field( $atts['height'] ),
					'auto_rotate' => wc_string_to_bool( $atts['auto_rotate'] ) ? 'yes' : 'no',
					'ar'          => wc_string_to_bool( $atts['ar'] ) ? 'yes' : 'no',
					'bg_color'    => $atts['bg_color'],
					'index'       => $i + 1,
					'total'       => $total,
				)
			);
		}

		return count( $models ) > 1 ? '<div class="wc3d-stack">' . $html . '</div>' : $html;
	}
}
