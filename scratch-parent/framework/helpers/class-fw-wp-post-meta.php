<?php if (!defined('FW')) die('Forbidden');

/**
 * Alternative to WordPress get_post_meta() and update_post_meta()
 *
 * Features:
 * - Works with "multi keys"
 * - The value is stored in two formats: original and prepared.
 *   Prepared is used for frontend because it is translated (+ maybe other preparations in the future)
 */
class FW_WP_Post_Meta
{
	/**
	 * Store all this class data in cache within this key
	 * @var string
	 */
	private static $cache_key = 'wp_post_meta';

	/**
	 * @param int $post_id
	 * @param string $multi_key 'abc' or 'ab/c/def'
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param bool|null $get_original_value Original value from db, no changes and translations
	 * @return mixed|null
	 */
	public static function get($post_id, $multi_key, $default_value = null, $get_original_value = null)
	{
		if ($get_original_value === null) {
			$get_original_value = is_admin();
		}

		if (empty($multi_key)) {
			trigger_error('Key not specified', E_USER_WARNING);
			return null;
		}

		$multi_key = explode('/', $multi_key);
		$key       = array_shift($multi_key);
		$multi_key = implode('/', $multi_key);

		$cache_key = self::$cache_key .'/'. $post_id .'/'. $key;

		try {
			$values = FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$values = array();

			$values['original'] = get_post_meta($post_id, $key, true);
			$values['prepared'] = fw_prepare_option_value($values['original']);

			FW_Cache::set($cache_key, $values);
		}

		if (empty($multi_key)) {
			return $values[$get_original_value ? 'original' : 'prepared'];
		} else {
			return fw_akg($multi_key, $values[$get_original_value ? 'original' : 'prepared'], $default_value);
		}
	}

	/**
	 * @param int $post_id
	 * @param string $multi_key 'abc' or 'ab/c/def'
	 * @param array|string|int|bool $set_value
	 */
	public static function set($post_id, $multi_key, $set_value)
	{
		if (empty($multi_key)) {
			trigger_error('Key not specified', E_USER_WARNING);
			return;
		}

		$multi_key = explode('/', $multi_key);
		$key       = array_shift($multi_key);
		$multi_key = implode('/', $multi_key);

		$cache_key = self::$cache_key .'/'. $post_id .'/'. $key;

		if (empty($multi_key) && $multi_key !== '0') {
			/** Replace entire meta */

			fw_update_post_meta($post_id, $key, $set_value);

			FW_Cache::del($cache_key);
		} else {
			/** Change only specified key */

			$values = array();

			$values['original'] = self::get($post_id, $key, true);
			$values['prepared'] = self::get($post_id, $key, false);

			fw_aks($multi_key, $set_value,                          $values['original']);
			fw_aks($multi_key, fw_prepare_option_value($set_value), $values['prepared']);

			FW_Cache::set($cache_key, $values);

			fw_update_post_meta($post_id, $key, $values['original']);
		}
	}
}
