<?php
/**
 * Product edit screen: "3D Model" meta box.
 *
 * @package WC3D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin.
 */
class WC3D_Admin {

	/**
	 * Hook in.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ), 40 );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'manage_edit-product_columns', array( __CLASS__, 'column' ), 20 );
		add_action( 'manage_product_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
	}

	/**
	 * Register the meta box under "Product gallery".
	 */
	public static function add_meta_box() {
		add_meta_box(
			'wc3d-product-model',
			__( '3D Models', 'wc-3d-product-viewer' ),
			array( __CLASS__, 'render' ),
			'product',
			'side',
			'low'
		);
	}

	/**
	 * Meta box HTML.
	 *
	 * @param WP_Post $post Product post.
	 */
	public static function render( $post ) {
		$product = wc_get_product( $post->ID );
		$models  = $product ? wc3d_get_models( $product ) : array();

		wp_nonce_field( 'wc3d_save_model', 'wc3d_nonce' );
		?>
		<div class="wc3d-box">
			<input type="hidden" name="wc3d_models_present" value="1" />
			<ul class="wc3d-models<?php echo $models ? '' : ' is-empty'; ?>">
				<?php
				foreach ( $models as $model ) {
					self::render_row( $model );
				}
				?>
			</ul>

			<p class="wc3d-empty"<?php echo $models ? ' hidden' : ''; ?>><?php esc_html_e( 'No 3D models yet.', 'wc-3d-product-viewer' ); ?></p>

			<p>
				<button type="button" class="button wc3d-add-models"><?php esc_html_e( 'Add 3D models (GLB / glTF)', 'wc-3d-product-viewer' ); ?></button>
			</p>

			<p class="description">
				<?php
				printf(
					/* translators: %d: maximum number of models */
					esc_html__( 'Each model is shown as its own slide at the end of the product gallery, in this order. Drag to reorder. Up to %d models.', 'wc-3d-product-viewer' ),
					(int) WC3D_MAX_MODELS
				);
				?>
			</p>
			<p class="description">
				<?php esc_html_e( 'GLB is recommended: it is a single file with textures included. A .gltf file must have its textures embedded.', 'wc-3d-product-viewer' ); ?>
			</p>

			<template id="wc3d-row-template">
				<?php self::render_row( array( 'id' => 0, 'thumb' => 0, 'label' => '' ) ); ?>
			</template>
		</div>
		<?php
	}

	/**
	 * One model row.
	 *
	 * @param array $model Model item.
	 */
	protected static function render_row( $model ) {
		$id      = absint( $model['id'] );
		$thumb   = absint( $model['thumb'] );
		$file    = $id ? get_attached_file( $id ) : '';
		$name    = $file ? wp_basename( $file ) : '';
		$size    = ( $file && file_exists( $file ) ) ? size_format( (int) filesize( $file ) ) : '';
		$img     = $thumb ? wp_get_attachment_image_url( $thumb, 'thumbnail' ) : '';
		$default = wc3d_default_thumb_url();
		?>
		<li class="wc3d-model">
			<input type="hidden" class="wc3d-model-id" name="wc3d_models[id][]" value="<?php echo esc_attr( $id ? $id : '' ); ?>" />
			<input type="hidden" class="wc3d-model-thumb" name="wc3d_models[thumb][]" value="<?php echo esc_attr( $thumb ? $thumb : '' ); ?>" />

			<span class="wc3d-model__handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'wc-3d-product-viewer' ); ?>" aria-hidden="true"></span>

			<button type="button" class="wc3d-model__thumb<?php echo $img ? ' has-image' : ''; ?>" title="<?php esc_attr_e( 'Choose gallery thumbnail', 'wc-3d-product-viewer' ); ?>">
				<img src="<?php echo esc_url( $img ? $img : $default ); ?>" data-default="<?php echo esc_url( $default ); ?>" alt="" />
			</button>

			<span class="wc3d-model__body">
				<strong class="wc3d-model__name" title="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></strong>
				<span class="wc3d-model__size"><?php echo esc_html( $size ); ?></span>
				<input type="text" class="wc3d-model__label" name="wc3d_models[label][]" value="<?php echo esc_attr( $model['label'] ); ?>" placeholder="<?php esc_attr_e( 'Label (optional)', 'wc-3d-product-viewer' ); ?>" maxlength="60" />
				<span class="wc3d-model__links">
					<button type="button" class="button-link wc3d-thumb-choose"><?php esc_html_e( 'Thumbnail', 'wc-3d-product-viewer' ); ?></button>
					<button type="button" class="button-link wc3d-thumb-reset"<?php echo $img ? '' : ' hidden'; ?>><?php esc_html_e( 'Default icon', 'wc-3d-product-viewer' ); ?></button>
				</span>
			</span>

			<button type="button" class="button-link wc3d-model__remove" aria-label="<?php esc_attr_e( 'Remove this 3D model', 'wc-3d-product-viewer' ); ?>"><span class="dashicons dashicons-no-alt"></span></button>
		</li>
		<?php
	}

	/**
	 * Save meta via the WC product object.
	 *
	 * @param WC_Product $product Product being saved.
	 */
	public static function save( $product ) {
		if ( ! isset( $_POST['wc3d_nonce'], $_POST['wc3d_models_present'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wc3d_nonce'] ) ), 'wc3d_save_model' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_product', $product->get_id() ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.
		$posted = isset( $_POST['wc3d_models'] ) && is_array( $_POST['wc3d_models'] ) ? wp_unslash( $_POST['wc3d_models'] ) : array();
		$ids    = isset( $posted['id'] ) ? (array) $posted['id'] : array();
		$thumbs = isset( $posted['thumb'] ) ? (array) $posted['thumb'] : array();
		$labels = isset( $posted['label'] ) ? (array) $posted['label'] : array();

		$models = array();
		$seen   = array();
		foreach ( array_values( $ids ) as $i => $raw_id ) {
			$id = absint( $raw_id );
			if ( ! $id || isset( $seen[ $id ] ) || ! wc3d_is_model_attachment( $id ) ) {
				continue;
			}
			$seen[ $id ] = true;

			$thumb = isset( $thumbs[ $i ] ) ? absint( $thumbs[ $i ] ) : 0;
			$label = isset( $labels[ $i ] ) ? sanitize_text_field( $labels[ $i ] ) : '';

			$models[] = array(
				'id'    => $id,
				'thumb' => ( $thumb && wp_attachment_is_image( $thumb ) ) ? $thumb : 0,
				'label' => function_exists( 'mb_substr' ) ? mb_substr( $label, 0, 60 ) : substr( $label, 0, 60 ),
			);

			if ( count( $models ) >= WC3D_MAX_MODELS ) {
				break;
			}
		}

		if ( $models ) {
			$product->update_meta_data( WC3D_META_MODELS, $models );
		} else {
			$product->delete_meta_data( WC3D_META_MODELS );
		}

		// Data now lives in the new key; drop 1.0 meta.
		$product->delete_meta_data( WC3D_META_MODEL );
		$product->delete_meta_data( WC3D_META_THUMB );
	}

	/**
	 * Admin assets on the product editor only.
	 *
	 * @param string $hook Screen hook.
	 */
	public static function assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		if ( 'edit-product' === $screen->id ) {
			wp_enqueue_style( 'wc3d-admin', WC3D_URL . 'assets/css/admin.css', array(), WC3D_VERSION );
			return;
		}
		if ( 'product' !== $screen->id || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wc3d-admin', WC3D_URL . 'assets/css/admin.css', array(), WC3D_VERSION );
		wp_enqueue_script( 'wc3d-admin', WC3D_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'media-upload' ), WC3D_VERSION, true );
		wp_localize_script(
			'wc3d-admin',
			'wc3dAdmin',
			array(
				'extensions' => wc3d_allowed_extensions(),
				'max'        => WC3D_MAX_MODELS,
				'i18n'       => array(
					'modelTitle'  => __( 'Select 3D models (GLB or glTF)', 'wc-3d-product-viewer' ),
					'modelButton' => __( 'Add selected models', 'wc-3d-product-viewer' ),
					'thumbTitle'  => __( 'Select a gallery thumbnail', 'wc-3d-product-viewer' ),
					'thumbButton' => __( 'Use this image', 'wc-3d-product-viewer' ),
					/* translators: %s: list of file names */
					'wrongType'   => __( 'These files were skipped because they are not .glb or .gltf: %s', 'wc-3d-product-viewer' ),
					/* translators: %d: maximum number of models */
					'tooMany'     => __( 'A product can have up to %d 3D models.', 'wc-3d-product-viewer' ),
				),
			)
		);
	}

	/**
	 * "3D" column in the products list.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function column( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'name' === $key ) {
				$new['wc3d'] = '<span class="wc3d-col" title="' . esc_attr__( '3D models', 'wc-3d-product-viewer' ) . '">3D</span>';
			}
		}
		return $new;
	}

	/**
	 * Column content.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Product ID.
	 */
	public static function column_content( $column, $post_id ) {
		if ( 'wc3d' !== $column ) {
			return;
		}
		$count = count( wc3d_get_models( $post_id ) );
		echo $count ? '<span class="wc3d-col-count" title="' . esc_attr(
			/* translators: %d: number of 3D models */
			sprintf( _n( '%d 3D model', '%d 3D models', $count, 'wc-3d-product-viewer' ), $count )
		) . '">' . esc_html( (string) $count ) . '</span>' : '<span aria-hidden="true">–</span>';
	}
}
