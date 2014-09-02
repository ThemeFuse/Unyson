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
	 * If the PHP will have less that this memory, the cache will try to delete parts from it's array to free memory
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
		return memory_get_usage() >= self::get_memory_limit() - self::$min_free_memory;
	}

	/**
	 * @internal
	 */
	public static function _init()
	{
		self::$not_found_value = new FW_Cache_Not_Found_Exception();
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
		fw_aks($keys, $value, self::$cache, $keys_delimiter);

		self::free_memory(); // call it every time to take care about memory
	}

	/**
	 * Unset key from cache
	 * @param $keys
	 * @param $keys_delimiter
	 */
	public static function del($keys, $keys_delimiter = '/')
	{
		fw_aku($keys, self::$cache, $keys_delimiter);

		self::free_memory(); // call it every time to take care about memory
	}

	/**
	 * @param $keys
	 * @param $keys_delimiter
	 * @param $load_callback
	 * @return mixed
	 * @throws FW_Cache_Not_Found_Exception
	 */
	public static function get($keys, $load_callback = null, $keys_delimiter = '/')
	{
		$keys = (string)$keys;
		$keys_arr = explode($keys_delimiter, $keys);

		$key = $keys_arr;
		$key = array_shift($key);

		if ($key === '' || $key === null) {
			trigger_error('First key must not be empty', E_USER_ERROR);
		}

		$value = fw_akg($keys, self::$cache, self::$not_found_value, $keys_delimiter);

		self::free_memory(); // call it every time to take care about memory

		if ($value === self::$not_found_value) {
			// others can load values for keys with TFC::set()
			{
				$parameters = array(
					'key'      => $key,
					'keys'     => $keys,
					'keys_arr' => $keys_arr,
				);

				if (is_callable($load_callback)) {
					call_user_func_array($load_callback, array($parameters));
				} else {
					do_action('fw_cache_load', $parameters);
				}

				unset($parameters);
			}

			// try again to get value (maybe someone loaded it)
			$value = fw_akg($keys, self::$cache, self::$not_found_value, $keys_delimiter);

			if ($value === self::$not_found_value) {
				throw new FW_Cache_Not_Found_Exception('Cache key not found: '. $keys);
			}
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
FW_Cache::_init();

class FW_Cache_Not_Found_Exception extends Exception {}

// auto free_memory() every X ticks
{
	/**
	 * 3000: ~15 times
	 */
	declare(ticks=3000);

	register_tick_function(array('FW_Cache', 'free_memory'));
}
