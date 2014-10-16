<?php if (!defined('FW')) die('Forbidden');

/**
 * Alternative to WordPress get_option() and update_option() functions
 *
 * Features:
 * - Works with "multi keys"
 * - The value is stored in two formats: original and prepared.
 *   Prepared is used for frontend because it is translated (+ maybe other preparations in the future)
 */
class FW_WP_Option
{
	/**
	 * Store all this class data in cache within this key
	 * @var string
	 */
	private static $cache_key = 'wp_option';

	/**
	 * @param string $option_name
	 * @param string|null $specific_multi_key 'ab/c/def'
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param bool|null $get_original_value Original value from db, no changes and translations
	 * @return mixed|null
	 */
	public static function get($option_name, $specific_multi_key = null, $default_value = null, $get_original_value = null)
	{
		if ($get_original_value === null) {
			$get_original_value = is_admin();
		}

		$cache_key = self::$cache_key .'/'. $option_name;

		try {
			$values = FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$values = array();

			$values['original'] = get_option($option_name, null);
			$values['prepared'] = fw_prepare_option_value($values['original']);

			FW_Cache::set($cache_key, $values);
		}

		if (empty($specific_multi_key)) {
			return $values[$get_original_value ? 'original' : 'prepared'];
		} else {
			return fw_akg($specific_multi_key, $values[$get_original_value ? 'original' : 'prepared'], $default_value);
		}
	}

	/**
	 * Alternative for update_option()
	 * @param string $option_name
	 * @param string|null $specific_multi_key
	 * @param array|string|int|bool $set_value
	 */
	public static function set($option_name, $specific_multi_key = null, $set_value)
	{
		$cache_key = self::$cache_key .'/'. $option_name;

		if ($specific_multi_key === null) {
			/** Replace entire option */

			update_option($option_name, $set_value);

			FW_Cache::del($cache_key);
		} else {
			/** Change only specified key */

			$values = array();

			$values['original'] = self::get($option_name, null, true);
			$values['prepared'] = self::get($option_name, null, false);

			fw_aks($specific_multi_key, $set_value,                          $values['original']);
			fw_aks($specific_multi_key, fw_prepare_option_value($set_value), $values['prepared']);

			FW_Cache::set($cache_key, $values);

			update_option($option_name, $values['original']);
		}
	}
}
