<?php if (!defined('FW')) die('Forbidden');

/**
 * Persistent cache saved in uploads/fw-file-cache.php
 *
 * It is reset when:
 * - the user is logged in as administrator
 * - important action related to theme switch or file changes is triggered (for e.g. JetPack can do changes remotely)
 *
 * Usage:
 * try {
 *     return FW_File_Cache::get($cache_key = '...'. $rel_path);
 * } catch (FW_File_Cache_Not_Found_Exception $e) {
 *     $result = ...;
 *
 *     FW_File_Cache::set($cache_key, $result);
 *
 *     return $result; // IMPORTANT: Do not use FW_File_Cache::get($cache_key) again
 * }
 *
 * @since 2.6.0
 */
class FW_File_Cache {
	/**
	 * @var int
	 */
	private static $expires = 3600;

	/**
	 * @var bool
	 */
	private static $changed = false;

	/**
	 * @var string
	 */
	private static $path;

	/**
	 * @var array
	 */
	private static $cache;

	/**
	 * If this was changed then cache must reset
	 * Fixes https://github.com/ThemeFuse/Unyson/issues/1986
	 * @var int
	 */
	private static $blog_id;

	private static function get_defaults() {
		return array(
			'created' => time(),
			'updated' => time(),
			'data' => array(),
		);
	}

	private static function load() {
		if ( is_null(self::$blog_id) ) {
			self::$blog_id = get_current_blog_id();
			self::reset();
		} else {
			if (is_array(self::$cache)) {
				return true; // already loaded
			} elseif (false === self::$cache) {
				return false;
			}
		}

		$dir  = dirname(self::get_path());
		$path = self::get_path();
		$code = '<?php return array();';
		$shhh = defined('DOING_AJAX') && DOING_AJAX; // prevent warning in ajax requests

		// prevent useless multiple execution of this method when uploads/ is not writable
		self::$cache = false;

		// check directory
		if ( ! file_exists($dir) ) {
			if ( ! is_writable( dirname( $dir ) )) { // check parent dir if writable ( should be uploads/ )
				return false;
			} elseif ( ! ( $shhh
				? @mkdir($dir, 0755, true)
				:  mkdir($dir, 0755, true)
			) ) {
				return false;
			}
		}

		// check file
		if ( file_exists($path) ) {
			if ( ! is_writable($path) ) {
				if (
					( $shhh
						? @unlink($path)
						:  unlink($path)
					)
					&&
					( $shhh
						? @file_put_contents($path, $code, LOCK_EX)
						:  file_put_contents($path, $code, LOCK_EX)
					)
				) {
					// file re-created
				} else {
					return false;
				}
			}
		} elseif ( ! ( $shhh
			? @file_put_contents($path, $code, LOCK_EX)
			:  file_put_contents($path, $code, LOCK_EX)
		) ) {
			return false; // cannot create the file
		}

		self::$cache = include $path;

		// check the loaded cache
		{
			$reset = false;

			do {
				foreach ( self::get_defaults() as $def_key => $def_val ) {
					if (
						!isset( self::$cache[ $def_key ] )
						||
						gettype( self::$cache[ $def_key ] ) !== gettype($def_val)
					) {
						$reset = true;
						break 2;
					}
				}

				if ( self::$cache['created'] < ( time() - self::$expires ) ) {
					$reset = true;
					break;
				}
			} while(false);

			if ($reset) {
				self::$cache = self::get_defaults();
				self::$changed = true;
			}
		}

		return true;
	}

	private static function get_path() {
		if (is_null(self::$path)) {
			self::$path = wp_upload_dir();
			self::$path = fw_fix_path(self::$path['basedir']) . '/fw/file-cache.php';
		}

		return self::$path;
	}

	/**
	 * @param mixed $filter_value When this method is used in filter, it must return the unchanged filter value
	 * @return bool
	 */
	public static function reset($filter_value = null) {
		if ( ! self::load() ) {
			return is_null($filter_value) ? true : $filter_value;
		}

		self::save();
		self::$path = null;
		self::$cache = self::get_defaults();
		self::$changed = true;

		return is_null($filter_value) ? true : $filter_value;
	}

	public static function save() {
		if ( ! self::$changed ) {
			return;
		}

		$shhh = defined('DOING_AJAX') && DOING_AJAX; // prevent warning in ajax requests

		if (!(
			$shhh
			? @file_put_contents(self::get_path(), '<?php return ' . var_export(self::$cache, true) . ';', LOCK_EX)
			:  file_put_contents(self::get_path(), '<?php return ' . var_export(self::$cache, true) . ';', LOCK_EX)
		)) {
			@file_put_contents(self::get_path(), '<?php return array();', LOCK_EX);
		}

		self::$changed = false;
	}

	/**
	 * @internal
	 */
	public static function _init() {
		/**
		 * Reset when current user is administrator
		 * because it can be a developer that added/removed some files
		 */
		if ( current_user_can('manage_options') ) {
			self::reset();
		} else {
			/**
			 * Reset on actions which may change something related to files
			 * - themes/plugins activation (new files must be loaded from other paths)
			 * - after some files was added/deleted
			 */
			foreach (array(
				'fw_extensions_before_activation' => true,
				'fw_extensions_after_activation' => true,
				'fw_extensions_before_deactivation' => true,
				'fw_extensions_after_deactivation' => true,
				'fw_extensions_install' => true,
				'fw_extensions_uninstall' => true,
				'activated_plugin' => true,
				'deactivated_plugin' => true,
				'switch_theme' => true,
				'after_switch_theme' => true,
				'upgrader_post_install' => true,
				'automatic_updates_complete' => true,
				'upgrader_process_complete' => true,
				// 'switch_blog' => true, // fixes https://github.com/ThemeFuse/Unyson/issues/1986
			) as $action => $x) {
				add_action( $action, array(__CLASS__, 'reset') );
			}
		}

		add_action( 'shutdown', array(__CLASS__, 'save') );
		add_action( 'switch_blog', array(__CLASS__, '_reset_blog_id') );
	}

	/**
	 * @param string $key No multiKey because it must be fast
	 * @return mixed
	 * @throws FW_File_Cache_Not_Found_Exception
	 */
	public static function get($key) {
		if ( ! self::load() ) {
			throw new FW_File_Cache_Not_Found_Exception();
		}

		if (array_key_exists($key, self::$cache['data'])) {
			return self::$cache['data'][$key];
		} else {
			throw new FW_File_Cache_Not_Found_Exception();
		}
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public static function set($key, $value) {
		if ( ! self::load() ) {
			return false;
		}

		self::$changed = true;
		self::$cache['updated'] = time();
		self::$cache['data'][ $key ] = $value;

		return true;
	}

	/**
	 * @internal
	 */
	public static function _reset_blog_id() {
		self::$blog_id = null;
	}
}

class FW_File_Cache_Not_Found_Exception extends Exception {}

FW_File_Cache::_init();
