<?php
/**
 * Allow GLB / glTF uploads to the Media Library.
 *
 * WordPress rejects unknown file types, and its content sniffing sees GLB as
 * application/octet-stream and glTF as JSON/text, so both filters are needed.
 *
 * @package WC3D
 */

defined( 'ABSPATH' ) || exit;

/**
 * Upload handling.
 */
class WC3D_Uploads {

	/**
	 * Extension => MIME type.
	 *
	 * @var array
	 */
	const MIMES = array(
		'glb'  => 'model/gltf-binary',
		'gltf' => 'model/gltf+json',
	);

	/**
	 * Hook in.
	 */
	public static function init() {
		add_filter( 'upload_mimes', array( __CLASS__, 'upload_mimes' ), 10, 2 );
		add_filter( 'wp_check_filetype_and_ext', array( __CLASS__, 'check_filetype' ), 10, 5 );
	}

	/**
	 * Can this user upload 3D models? Only people who can edit products.
	 *
	 * @param int|WP_User|null $user User.
	 * @return bool
	 */
	protected static function user_can_upload( $user = null ) {
		$user_id = $user instanceof WP_User ? $user->ID : ( $user ? absint( $user ) : get_current_user_id() );
		$can     = $user_id && ( user_can( $user_id, 'edit_products' ) || user_can( $user_id, 'manage_woocommerce' ) );

		/**
		 * Filter whether a user may upload GLB/glTF files.
		 *
		 * @param bool $can     Allowed.
		 * @param int  $user_id User ID.
		 */
		return (bool) apply_filters( 'wc3d_user_can_upload_models', $can, $user_id );
	}

	/**
	 * Add GLB / glTF to the allowed MIME list.
	 *
	 * @param array            $mimes Allowed types.
	 * @param int|WP_User|null $user  User.
	 * @return array
	 */
	public static function upload_mimes( $mimes, $user = null ) {
		if ( self::user_can_upload( $user ) ) {
			$mimes = array_merge( $mimes, self::MIMES );
		}
		return $mimes;
	}

	/**
	 * Fix the real-MIME check for 3D files.
	 *
	 * @param array       $data      ext, type, proper_filename.
	 * @param string      $file      Full path to the file.
	 * @param string      $filename  File name.
	 * @param array|null  $mimes     Allowed MIME types.
	 * @param string|bool $real_mime Detected MIME.
	 * @return array
	 */
	public static function check_filetype( $data, $file, $filename, $mimes = null, $real_mime = false ) {
		$ext = strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) );
		if ( ! isset( self::MIMES[ $ext ] ) || ! self::user_can_upload() ) {
			return $data;
		}

		if ( ! self::looks_valid( $file, $ext ) ) {
			$data['ext']  = false;
			$data['type'] = false;
			return $data;
		}

		$data['ext']  = $ext;
		$data['type'] = self::MIMES[ $ext ];
		return $data;
	}

	/**
	 * Light content check so random files can't be renamed to .glb/.gltf.
	 *
	 * @param string $file Path.
	 * @param string $ext  Extension.
	 * @return bool
	 */
	protected static function looks_valid( $file, $ext ) {
		if ( ! is_readable( $file ) ) {
			return false;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$head = (string) file_get_contents( $file, false, null, 0, 512 );

		if ( 'glb' === $ext ) {
			return 0 === strncmp( $head, 'glTF', 4 ); // GLB magic bytes.
		}

		// glTF is JSON with an "asset" object.
		$head = ltrim( $head, "\xEF\xBB\xBF \t\r\n" );
		return '{' === substr( $head, 0, 1 ) && false === stripos( $head, '<?php' ) && false === stripos( $head, '<script' );
	}
}
