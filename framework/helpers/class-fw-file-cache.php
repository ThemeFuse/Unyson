<?php if (!defined('FW')) die('Forbidden');

/**
 * Persistent cache saved in uploads/fw-file-cache.php
 * It is reset when the user is logged in as administrator
 * @since 2.5.13
 *
 * ToDo: Skip path on backup files export
 * ToDo: Reset if logged in as administrator
 * ToDo: Reset on major actions like theme switch, because Jetpack can make changes without the user being logged in
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

	private static function load() {
		if ( is_array( self::$cache ) ) {
			return true; // already loaded
		}

		$path = self::$path;
		$code = '<?php return array();';
		$shhh = defined('DOING_AJAX') && DOING_AJAX; // prevent warning in ajax requests

		if ( file_exists($path) ) {
			if ( ! is_writable($path) ) {
				if (
					( $shhh
						? @unlink($path)
						:  unlink($path)
					)
					&&
					file_put_contents($path, $code)
				) {
					// file re-created
				} else {
					return false;
				}
			}
		} elseif ( ! ( $shhh
			? @file_put_contents($path, $code)
			:  file_put_contents($path, $code)
		) ) {
			return false; // cannot create the file
		}

		self::$cache = include $path;

		if (
			!is_array(self::$cache)
			||
		    !isset(self::$cache['created'])
			||
			self::$cache['created'] < ( time() - self::$expires )
			||
		    !isset(self::$cache['data'])
		) {
			self::$cache = array(
				'created' => time(),
				'updated' => time(),
				'data' => array(),
			);
		}

		return true;
	}

	private static function update_path() {
		$path = wp_upload_dir();
		$path = fw_fix_path($path['basedir']) .'/fw-file-cache.php';

		self::$path = $path;
	}

	/**
	 * @internal
	 */
	public static function _action_switch_blog() {
		self::update_path();
		self::$cache = null;
		self::$changed = false;
	}

	/**
	 * @internal
	 */
	public static function _action_shutdown() {
		if ( ! self::$changed ) {
			return;
		}

		self::$cache['updated'] = time();

		file_put_contents(self::$path, '<?php return '. var_export(self::$cache, true) .';');
	}

	/**
	 * @internal
	 */
	public static function _init() {
		self::update_path();

		add_action( 'switch_blog', array(__CLASS__, '_action_switch_blog') );
		add_action( 'shutdown', array(__CLASS__, '_action_shutdown') );
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

		self::$cache['data'][ $key ] = $value;

		return true;
	}
}

class FW_File_Cache_Not_Found_Exception extends Exception {}

FW_File_Cache::_init();
