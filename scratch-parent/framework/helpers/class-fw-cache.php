<?php if (!defined('FW')) die('Forbidden');

/**
 * Memory Cache
 * Only for internal usage in other functions/methods, because it throws exceptions
 *
 * Recommended usage example:
 *  try {
 *      $value = FW_Cache::get('some/key');
 *  } catch(FW_Cache_Not_Found_Exception $e) {
 *      $value = get_value_from_somewhere();
 *
 *      FW_Cache::set('some/key', $value);
 *
 *      // (!) after set, do not do this:
 *      $value = FW_Cache::get('some/key');
 *      // because there is no guaranty that FW_Cache::set('some/key', $value); succeeded
 *      // trust only your $value, cache can do clean-up right after set() and remove the value you tried to set
 *  }
 *
 *  // use $value ...
 */
class FW_Cache
{
	protected static $cache = array();

	/**
	 * @var bool
	 */
	protected static $is_enabled;

	/**
	 * If the PHP will have less that this memory, the cache will try to delete parts from its array to free memory
	 *
	 * (1024 * 1024 = 1048576 = 1 Mb) * 10
	 */
	protected static $min_free_memory = 10485760;

	/**
	 * Max allowed memory for PHP
	 */
	protected static $memory_limit = null;

	protected static $not_found_value;

	protected static function get_memory_limit()
	{
		if (self::$memory_limit === null) {
			$memory_limit = ini_get('memory_limit');

			if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
				if ($matches[2] == 'M') {
					$memory_limit = $matches[1] * 1024 * 1024; // nnn_m -> nnn MB
				} else if ($matches[2] == 'K') {
					$memory_limit = $matches[1] * 1024; // nnn_k -> nnn KB
				}
			}

			self::$memory_limit = $memory_limit;
		}

		return self::$memory_limit;
	}

	protected static function memory_exceeded()
	{
		return memory_get_usage(false) >= self::get_memory_limit() - self::$min_free_memory;

		// about memory_get_usage(false) http://stackoverflow.com/a/16239377/1794248
	}

	/**
	 * @internal
	 */
	public static function _init()
	{
		self::$is_enabled = function_exists('register_tick_function');
		self::$not_found_value = new FW_Cache_Not_Found_Exception();
	}

	public static function is_enabled()
	{
		return self::$is_enabled;
	}

	public static function free_memory()
	{
		while (self::memory_exceeded() && !empty(self::$cache)) {
			reset(self::$cache);

			$key = key(self::$cache);

			unset(self::$cache[$key]);
		}
	}

	/**
	 * @param $keys
	 * @param $value
	 * @param $keys_delimiter
	 */
	public static function set($keys, $value, $keys_delimiter = '/')
	{
		if (!self::$is_enabled) {
			return;
		}

		self::free_memory();

		fw_aks($keys, $value, self::$cache, $keys_delimiter);

		self::free_memory();
	}

	/**
	 * Unset key from cache
	 * @param $keys
	 * @param $keys_delimiter
	 */
	public static function del($keys, $keys_delimiter = '/')
	{
		fw_aku($keys, self::$cache, $keys_delimiter);

		self::free_memory();
	}

	/**
	 * @param $keys
	 * @param $keys_delimiter
	 * @return mixed
	 * @throws FW_Cache_Not_Found_Exception
	 */
	public static function get($keys, $keys_delimiter = '/')
	{
		$keys = (string)$keys;
		$keys_arr = explode($keys_delimiter, $keys);

		$key = $keys_arr;
		$key = array_shift($key);

		if ($key === '' || $key === null) {
			trigger_error('First key must not be empty', E_USER_ERROR);
		}

		self::free_memory();

		$value = fw_akg($keys, self::$cache, self::$not_found_value, $keys_delimiter);

		self::free_memory();

		if ($value === self::$not_found_value) {
			throw new FW_Cache_Not_Found_Exception();
		}

		return $value;
	}

	/**
	 * Empty the cache
	 */
	public static function clear()
	{
		self::$cache = array();
	}
}

class FW_Cache_Not_Found_Exception extends Exception {}

FW_Cache::_init();

// auto free_memory() every X ticks
if (FW_Cache::is_enabled()) {
	declare(ticks=3000);

	register_tick_function(array('FW_Cache', 'free_memory'));
}
