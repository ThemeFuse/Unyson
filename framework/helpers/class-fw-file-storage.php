<?php
/**
 * Cache is saved in php files in /uploads/fw/storage/
 *
 * It is reset when:
 * - After 12 hours (modify $wipe_cache variable).
 *
 * Usage:
 *
 * if( FW_File_Storage::has('my-key') ) {
 *	   FW_File_Storage::put('my-key', 'All Data', 5); // 5 min cache.
 * } else {
 *     echo FW_File_Storage::get('my-key');
 * }
 *
 * @since 2.6.9
 */

class FW_File_Storage {

	/**
	 * @var int
	 */
	private static $expires = 3600;

	/**
	 * @var string
	 */
	private static $path;

	/**
	 * @var string $wipe_cache Minutes.
	 */
	private static $wipe_cache = 720;


	public static function _init() {
		self::folders_init();

		if (self::have_access()) {
			if (!self::has('clean-cache')) {
				self::remove_old_cache();
				// Wipe cache after 12 hours.
				self::put('clean-cache', 'wait', self::$wipe_cache);
			}
		}
	}

	/**
	 * Remove old cache.
	 */
	protected static function remove_old_cache() {
		$all_files = self::list_of_files();
		if(is_array($all_files)) {
			foreach($all_files as $path) {
				$parts = explode('/', $path);
				$filename = $parts[ count($parts) - 1 ];

				if( 'index.php' !== $filename ) {
					$data = self::get_content($path);
					if(isset($data['expire'])) {
						if( time() < $data['expire'] ) {
							@unlink($path);
						}
					} else {
						@unlink($path);
					}
				}
			}
		}
	}

	/**
	 * Create default cache system structure.
	 * @return bool
	 */
	protected static function folders_init() {
		$upload_dir = wp_upload_dir();
		$fw_dir = fw_fix_path($upload_dir['basedir']) . '/fw/';

		// Create `fw` folder.
		if (!file_exists($fw_dir)) {
			if (is_writable(dirname($fw_dir))) {
				mkdir($fw_dir, 0777);
			} else {
				return false;
			}
		}

		// Create empty index.php file.
		self::create_empty_index($fw_dir);

		// Create `fw/storage` folder.
		$storage_dir = $fw_dir . 'storage/';
		if (!file_exists($storage_dir)) {
			if (is_writable($fw_dir)) {
				mkdir($storage_dir, 0777);
			} else {
				return false;
			}
		}

		self::$path = $storage_dir;

		// Create empty index.php file.
		self::create_empty_index($storage_dir);

		return true;
	}

	/**
	 * Get full path to storage directory.
	 * @param string $add Additional suffix.
	 * @return string
	 */
	public static function get_path($add='') {
		if (is_null(self::$path)) {
			return false;
		}

		if ($add) {
			return fw_fix_path(self::$path . '/' . $add);
		} else {
			return self::$path;
		}
	}

	/**
	 * Check if we have write access to storage folder.
	 * @return bool
	 */
	protected static function have_access() {
		if (file_exists(self::$path)) {
			if (is_writable(self::$path)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Create empty index.php file in folder.
	 * @param string $path Directory
	 */
	protected static function create_empty_index($path) {
		if (is_writable($path)) {
			$file = fw_fix_path($path . '/index.php');
			if (!file_exists($file)) {
				$empty_file = fopen($file, 'w');
				fclose($empty_file);
			}
		}

		return false;
	}

	/**
	 * All files from storage directory.
	 * @return array
	 */
	protected static function list_of_files() {
		return glob(self::get_path('*.php'));
	}

	/**
	 * Hash the cache key (file name).
	 * @param string $key Cache key
	 * @return string
	 */
	protected static function hash($key) {
		return md5($key);
	}

	/**
	 * Get full path to file by cache key.
	 * @param string $key Cache key
	 * @return string
	 */
	protected static function prepare_filename($key) {
		return self::get_path( self::hash($key) . '.php' );
	}

	/**
	 * Check if cache file exists.
	 * @param string $key Cache key
	 * @return bool
	 */
	public static function has($key) {
		$path = self::prepare_filename($key);
		$content = self::get_content($path);

		if (false !== $content) {
			if (isset($content['expire'])) {
				if (time() > $content['expire']) {
					if (self::have_access()) {
						// Delete file.
						@unlink($path);
					}
					return false;
				}
			} else {
				// Delete file.
				@unlink($path);
			}
		}

		return true;
	}

	/**
	 * Add the cache life-time to current unix time.
	 * @param int $expire Minutes
	 * @return int
	 */
	protected static function prepare_expire_time($expire) {
		if (!$expire) {
			$expire = self::$expires;
		} else {
			$expire = (int)$expire * 60;
		}

		return time() + $expire;
	}

	/**
	 * Get cache-file default structure.
	 * @return array
	 */
	protected static function get_defaults() {
		return array(
			'expire' => 0,
			'content' => '',
		);
	}

	/**
	 * Create new cache-file with content.
	 * @param string $key Cache key
	 * @param mixed $data Cache content
	 * @param int $expire Cache life-time
	 * @return bool
	 */
	public static function put($key, $data, $expire=0) {
		if (!$key || !self::have_access()) {
			return false;
		}

		$filename = self::prepare_filename($key);

		if (self::has($key)) {
			@unlink($filename);
		}

		$prepare_data = self::get_defaults();
		$prepare_data['content'] = $data;
		$prepare_data['expire'] = self::prepare_expire_time($expire);

		$prepare_data = '<?php return \''.$prepare_data.'\'; ?>';
		$write = @file_put_contents($filename, $prepare_data);

		return ($write) ? true : false;
	}

	/**
	 * Get file content.
	 * @param string Path to stored file.
	 * @return array
	 */
	protected static function get_content($path) {
		if (file_exists($path)) {
			$content = require($path);

			$decode = @json_decode($content, true);
			if (is_array($decode)) {
				return $decode;
			}

			return self::get_defaults();
		}

		return self::get_defaults();
	}

	/**
	 * Get content from cache.
	 * @param string $key Cache key
	 * @param mixed $data Cache content
	 * @param int $expire Cache life-time
	 * @return string
	 */
	public static function get($key, $data='', $expire=0) {
		if (!$key) {
			return false;
		}

		$filename = self::prepare_filename($key);
		$has = self::has($key);

		if(!$has) {
			if('' !== $data) {
				return self::put($key, $data, $expire);
			}
		} else {
			if (file_exists($filename)) {
				$content = require($filename);
				$decode = @json_decode($content, true);
				if (is_array($decode)) {
					if (isset($decode['content'])) {
						return $decode['content'];
					}
				}
			}
		}

		return false;
	}
}

FW_File_Storage::_init();