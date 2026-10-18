<?php
/**
 * Plugin Name:       WC 3D Product Viewer
 * Plugin URI:        https://github.com/sanketthakkar99/
 * Description:       Attach one or more GLB / glTF 3D models to any WooCommerce product and show each as its own interactive 3D slide at the end of the product image gallery – the WooCommerce gallery or the theme's own slider such as Porto (with AR on supported phones).
 * Version:           1.2.0
 * Author:            Sanket Thakkar
 * Author URI:        https://github.com/sanketthakkar99/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc-3d-product-viewer
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 7.0
 * WC tested up to:   11.2
 *
 * @package WC3D
 */

defined( 'ABSPATH' ) || exit;

define( 'WC3D_VERSION', '1.2.0' );
define( 'WC3D_MODEL_VIEWER_VERSION', '4.4.0' );
define( 'WC3D_FILE', __FILE__ );
define( 'WC3D_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC3D_URL', plugin_dir_url( __FILE__ ) );

/** Product meta keys. */
define( 'WC3D_META_MODELS', '_wc3d_models' );   // Array of models (1.1+).
define( 'WC3D_META_MODEL', '_wc3d_model_id' );  // Legacy single model (1.0).
define( 'WC3D_META_THUMB', '_wc3d_thumb_id' );  // Legacy single thumbnail (1.0).
define( 'WC3D_MAX_MODELS', 20 );

/**
 * Declare compatibility with WooCommerce features (HPOS, cart/checkout blocks).
 * The plugin never touches orders, so both are safe.
 */
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WC3D_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', WC3D_FILE, true );
		}
	}
);

/**
 * Boot once all plugins are loaded so WooCommerce is available.
 */
add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'wc-3d-product-viewer', false, dirname( plugin_basename( WC3D_FILE ) ) . '/languages' );

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>' .
						esc_html__( 'WC 3D Product Viewer needs WooCommerce to be installed and active.', 'wc-3d-product-viewer' ) .
						'</p></div>';
				}
			);
			return;
		}

		require_once WC3D_PATH . 'includes/functions.php';
		require_once WC3D_PATH . 'includes/class-wc3d-uploads.php';
		require_once WC3D_PATH . 'includes/class-wc3d-settings.php';
		require_once WC3D_PATH . 'includes/class-wc3d-admin.php';
		require_once WC3D_PATH . 'includes/class-wc3d-frontend.php';

		WC3D_Uploads::init();
		WC3D_Settings::init();
		WC3D_Admin::init();
		WC3D_Frontend::init();
	}
);

/**
 * "Settings" link on the Plugins screen.
 */
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	static function ( $links ) {
		$url = admin_url( 'admin.php?page=wc-settings&tab=products&section=wc3d' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'wc-3d-product-viewer' ) . '</a>' );
		return $links;
	}
);
