<?php if (!defined('FW')) die('Forbidden');

class FW_WP_Filesystem
{
	/**
	 * Request WP Filesystem access
	 * @param string $context
	 * @param string $url
	 * @param array $extra_fields
	 * @return null|bool // todo: Create a new method that will return WP_Error with message on failure
	 *      null  - if has no access and the input credentials form was displayed
	 *      false - if user submitted wrong credentials
	 *      true  - if we have filesystem access
	 */
	final public static function request_access($context = null, $url = null, $extra_fields = array())
	{
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ($wp_filesystem) {
			if (
				is_object($wp_filesystem)
				&&
				!(is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code())
			) {
				return true; // already initialized
			}
		}

		if ( empty( $url ) ) {
			$url = fw_current_url();
		}

		if ( get_filesystem_method() === 'direct' ) {
			// in case if direct access is available

			/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
			$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, null );

			/* initialize the API */
			if ( ! WP_Filesystem( $creds ) ) {
				/* any problems and we exit */
				trigger_error( __( 'Cannot connect to Filesystem directly', 'fw' ), E_USER_WARNING );

				return false;
			}
		} else {
			$creds = request_filesystem_credentials( $url, '', false, $context, $extra_fields );

			if ( ! $creds ) {
				// the form was printed to the user
				return null;
			}

			/* initialize the API */
			if ( ! WP_Filesystem( $creds ) ) {
				/* any problems and we exit */
				request_filesystem_credentials( $url, '', true, $context, $extra_fields ); // the third parameter is true to show error to the user
				return false;
			}
		}

		if (
			! is_object($wp_filesystem)
			||
			(is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code())
		) {
			return false;
		}

		if (
			$wp_filesystem->abspath()
			&&
			$wp_filesystem->wp_content_dir()
			&&
			$wp_filesystem->wp_plugins_dir()
			&&
			$wp_filesystem->wp_themes_dir()
			&&
			$wp_filesystem->find_folder($context)
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return array {base_dir_real_path => base_dir_wp_filesystem_path}
	 */
	public static function get_base_dirs_map()
	{
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!$wp_filesystem) {
			trigger_error('Filesystem is not available', E_USER_ERROR);
		} elseif (is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
			trigger_error('Filesystem: '. $wp_filesystem->errors->get_error_message(), E_USER_ERROR);
		}

		try {
			$cache_key = 'fw_wp_filesystem/base_dirs_map';

			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			// code from $wp_filesystem->wp_themes_dir()
			{
				$themes_dir = get_theme_root();

				// Account for relative theme roots
				if ( '/themes' == $themes_dir || ! is_dir( $themes_dir ) ) {
					$themes_dir = WP_CONTENT_DIR . $themes_dir;
				}
			}

			$dirs = array(
				fw_fix_path(ABSPATH)        => fw_fix_path($wp_filesystem->abspath()),
				fw_fix_path(WP_CONTENT_DIR) => fw_fix_path($wp_filesystem->wp_content_dir()),
				fw_fix_path(WP_PLUGIN_DIR)  => fw_fix_path($wp_filesystem->wp_plugins_dir()),
				fw_fix_path($themes_dir)    => fw_fix_path($wp_filesystem->wp_themes_dir()),
			);

			FW_Cache::set($cache_key, $dirs);

			return $dirs;
		}
	}

	/**
	 * Convert real file path to WP Filesystem path
	 * @param string $real_path
	 * @return string|false
	 */
	final public static function real_path_to_filesystem_path($real_path) {
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!$wp_filesystem) {
			trigger_error('Filesystem is not available', E_USER_ERROR);
		} elseif (is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
			trigger_error('Filesystem: '. $wp_filesystem->errors->get_error_message(), E_USER_ERROR);
		}

		$real_path = fw_fix_path($real_path);

		foreach (self::get_base_dirs_map() as $base_real_path => $base_wp_filesystem_path) {
			$prefix_regex = '/^'. preg_quote($base_real_path, '/') .'($|\/.*)/';

			// check if path is inside base path
			if (!preg_match($prefix_regex, $real_path)) {
				continue;
			}

			if ($base_real_path === '/') {
				$relative_path = $real_path;
			} else {
				$relative_path = preg_replace($prefix_regex, '$1', $real_path);
			}

			return $base_wp_filesystem_path . $relative_path;
		}

		return false;
	}

	/**
	 * Convert WP Filesystem path to real file path
	 * @param string $wp_filesystem_path
	 * @return string|false
	 */
	final public static function filesystem_path_to_real_path($wp_filesystem_path) {
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!$wp_filesystem) {
			trigger_error('Filesystem is not available', E_USER_ERROR);
		} elseif (is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
			trigger_error('Filesystem: '. $wp_filesystem->errors->get_error_message(), E_USER_ERROR);
		}

		$wp_filesystem_path = fw_fix_path($wp_filesystem_path);

		foreach (self::get_base_dirs_map() as $base_real_path => $base_wp_filesystem_path) {
			$prefix_regex = '/^'. preg_quote($base_wp_filesystem_path, '/') .'($|\/.*)/';

			// check if path is inside base path
			if (!preg_match($prefix_regex, $wp_filesystem_path)) {
				continue;
			}

			if ($base_wp_filesystem_path === '/') {
				$relative_path = $wp_filesystem_path;
			} else {
				$relative_path = preg_replace($prefix_regex, '$1', $wp_filesystem_path);
			}

			return $base_real_path . $relative_path;
		}

		return false;
	}

	/**
	 * Check if there is direct filesystem access, so we can make changes without asking the credentials via form
	 * @param string|null $context
	 * @return bool
	 */
	final public static function has_direct_access($context = null)
	{
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ($wp_filesystem) {
			if (is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
				return false;
			} else {
				return $wp_filesystem->method === 'direct';
			}
		}

		if (get_filesystem_method(array(), $context) === 'direct') {
			ob_start();
			{
				$creds = request_filesystem_credentials(admin_url(), '', false, $context, null);
			}
			ob_end_clean();

			if ( WP_Filesystem($creds) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Create wp filesystem directory recursive
	 * @param string $wp_filesystem_dir_path
	 * @return bool
	 */
	final public static function mkdir_recursive($wp_filesystem_dir_path) {
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!$wp_filesystem) {
			trigger_error('Filesystem is not available', E_USER_ERROR);
		} elseif (is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code()) {
			trigger_error('Filesystem: '. $wp_filesystem->errors->get_error_message(), E_USER_ERROR);
		}

		$wp_filesystem_dir_path = fw_fix_path($wp_filesystem_dir_path);

		$path = false;

		foreach (self::get_base_dirs_map() as $base_real_path => $base_wp_filesystem_path) {
			$prefix_regex = '/^'. preg_quote($base_wp_filesystem_path, '/') .'($|\/)/';

			// check if path is inside base path
			if (!preg_match($prefix_regex, $wp_filesystem_dir_path)) {
				continue;
			}

			$path = $base_wp_filesystem_path;
			break;
		}

		if (!$path) {
			trigger_error(
				sprintf(
					__('Cannot create directory "%s". It must be inside "%s"', 'fw'),
					$wp_filesystem_dir_path,
					implode(__('" or "', 'fw'), self::get_base_dirs_map())
				),
				E_USER_WARNING
			);
			return false;
		}

		if ($path === '/') {
			$rel_path = $wp_filesystem_dir_path;
		} else {
			$rel_path = preg_replace('/^'. preg_quote($path, '/') .'($|\/.*)/', '$1', $wp_filesystem_dir_path);
		}

		// improvement: do not check directory for existence if it's known that sure it doesn't exist
		$check_if_exists = true;

		foreach (explode('/', ltrim($rel_path, '/')) as $dir_name) {
			$path .= '/' . $dir_name;

			// When WP FS abspath is '/', $path can be '//wp-content'. Fix it '/wp-content'
			$path = fw_fix_path($path);

			if ($check_if_exists) {
				if ($wp_filesystem->is_dir($path)) {
					// do nothing if exists
					continue;
				} else {
					// do not check anymore, next directories sure doesn't exist
					$check_if_exists = false;
				}
			}

			if (!$wp_filesystem->mkdir($path)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param $file_path
	 * @param $content
	 *
	 * @return bool|WP_Error
	 */
	public static function put( $file_path, $content ) {

		self::init_file_system();

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ( ! $wp_filesystem->put_contents( $file_path, $content ) ) {
			return new WP_Error( 'fs_error_put_content', esc_html__( 'Error writing to file: ', 'fw' ) . wp_basename( $file_path ) );
		}

		return true;
	}

	/**
	 * @param $file_path
	 *
	 * @return bool|mixed|WP_Error
	 */
	public static function get( $file_path ) {

		self::init_file_system();

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$content = $wp_filesystem->get_contents( $file_path );

		if ( false === $content ) {
			return new WP_Error( 'fs_error_get_content', esc_html__( 'Error to get content from file: ', 'fw' ) . wp_basename( $file_path ) );
		}

		return $content;
	}

	/**
	 *  Initialize wp files system.
	 */
	public static function init_file_system() {
		if ( self::is_ready() ) {
			return;
		}

		include_once( ABSPATH . '/wp-admin/includes/file.php' );

		WP_Filesystem();
	}

	/**
	 * If is initialized and has no errors
	 * @return bool
	 * @since 2.6.8
	 */
	public static function is_ready() {
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		return $wp_filesystem && is_wp_error($wp_filesystem->errors) && !$wp_filesystem->errors->get_error_code();
	}
}
