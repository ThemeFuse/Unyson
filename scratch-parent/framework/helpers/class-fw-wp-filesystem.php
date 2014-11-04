<?php if (!defined('FW')) die('Forbidden');

class FW_WP_Filesystem
{
	/**
	 * Request WP Filesystem access
	 * @param string $context
	 * @param string $url
	 * @param array $extra_fields
	 * @return null|bool
	 *      null  - if has no access and the input credentials form was displayed
	 *      false - if user submitted wrong credentials
	 *      true  - if we have filesystem access
	 */
	final public static function request_access($context, $url, $extra_fields = array())
	{
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ($wp_filesystem) {
			// already initialized (has access)
			return true;
		}

		if (get_filesystem_method() === 'direct') {
			// in case if direct access is available

			/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
			$creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, null);

			/* initialize the API */
			if ( ! WP_Filesystem($creds) ) {
				/* any problems and we exit */
				trigger_error(__('Cannot connect to Filesystem directly', 'fw'), E_USER_WARNING);
				return false;
			}
		} else {
			$creds = request_filesystem_credentials($url, '', false, $context, $extra_fields);

			if (!$creds) {
				// the form was printed to the user
				return null;
			}

			/* initialize the API */
			if ( ! WP_Filesystem($creds) ) {
				/* any problems and we exit */
				request_filesystem_credentials($url, '', true, $context, $extra_fields); // the third parameter is true to show error to the user
				return false;
			}
		}

		global $wp_filesystem;

		if ( ! is_object($wp_filesystem) ) {
			return false;
		}

		if ( is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->get_error_code() ) {
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
	 * Convert real file path to WP Filesystem path
	 * @param string $path
	 * @return string
	 */
	final public static function real_path_to_filesystem_path($path) {
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!$wp_filesystem) {
			trigger_error('Filesystem is not available', E_USER_ERROR);
		}

		$path = fw_fix_path($path);

		$real_abspath = untrailingslashit(fw_fix_path(ABSPATH));
		$wp_filesystem_abspath = untrailingslashit($wp_filesystem->abspath());
		$relative_path = preg_replace('/^'. preg_quote($real_abspath, '/') .'/', '', $path);

		return $wp_filesystem_abspath . $relative_path;
	}

	/**
	 * Convert WP Filesystem path to real file path
	 * @param string $wp_filesystem_path
	 * @return string
	 */
	final public static function filesystem_path_to_real_path($wp_filesystem_path) {
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!$wp_filesystem) {
			trigger_error('Filesystem is not available', E_USER_ERROR);
		}

		$wp_filesystem_path = fw_fix_path($wp_filesystem_path);

		$real_abspath = untrailingslashit(fw_fix_path(ABSPATH));
		$wp_filesystem_abspath = untrailingslashit($wp_filesystem->abspath());
		$relative_path = preg_replace('/^'. preg_quote($wp_filesystem_abspath, '/') .'/', '', $wp_filesystem_path);

		return $real_abspath . $relative_path;
	}

	/**
	 * Check if there is direct filesystem access, so we can make changes without asking the credentials via form
	 * @return bool
	 */
	final public static function has_direct_access()
	{
		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if ($wp_filesystem) {
			return $wp_filesystem->method === 'direct';
		}

		if (get_filesystem_method() === 'direct') {
			ob_start();
			{
				$creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, null);
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
		}

		$wp_filesystem_dir_path = fw_fix_path($wp_filesystem_dir_path);

		$path = '';
		$check_if_exists = true;
		$firs_loop = true;
		foreach (explode('/', $wp_filesystem_dir_path) as $dir_name) {
			if (empty($dir_name)) {
				if ($firs_loop) {
					/**
					 * It's a unix style path staring with '/'
					 * (On windows it starts with 'C:/')
					 */
					$path = '/';
				} else {
					trigger_error('Invalid path: '. $wp_filesystem_dir_path, E_USER_WARNING);
					return false;
				}
			}

			$path .= ($firs_loop ? '' : '/') . $dir_name;

			$firs_loop = false;

			if ($check_if_exists) {
				if ($wp_filesystem->is_dir($path)) {
					// do nothing if exists
					continue;
				} else {
					// do not check anymore, next directories sure does not exists
					$check_if_exists = false;
				}
			}

			if (!$wp_filesystem->mkdir($path, FS_CHMOD_DIR)) {
				return false;
			}
		}

		return true;
	}
}
