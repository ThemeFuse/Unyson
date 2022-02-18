<?php if (!defined('FW')) die('Forbidden');

/**
 * Alternative to WordPress get_option() and update_option() functions
 *
 * Features:
 * - Works with "multi keys"
 */
class FW_WP_Option
{
	/**
	 * @param string $option_name
	 * @param string|null $specific_multi_key 'ab/c/def'
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param bool|null $get_original_value REMOVED https://github.com/ThemeFuse/Unyson/issues/1676
	 * @return mixed|null
	 */
	public static function get($option_name, $specific_multi_key = null, $default_value = null, $get_original_value = null)
	{
		if ( ! is_null($get_original_value) ) {
			_doing_it_wrong(__FUNCTION__, '$get_original_value parameter was removed', 'Unyson 2.5.8');
		}

		$value = get_option($option_name, null);

		if (empty($specific_multi_key) && $specific_multi_key !== '0') {
			return is_null($value) ? fw_call( $default_value ) : $value;
		} else {
			return fw_akg($specific_multi_key, $value, $default_value);
		}
	}

	/**
	 * Alternative for update_option()
	 * @param string $option_name
	 * @param string|null $specific_multi_key
	 * @param array|string|int|bool $set_value
	 */
	public static function set($option_name = '', $specific_multi_key = null, $set_value = '')
	{
		if ($specific_multi_key === null) { // Replace entire option
			update_option($option_name, $set_value, false);
		} else { // Change only specified key
			$value = self::get($option_name, null, true);
			fw_aks($specific_multi_key, $set_value, $value);
			update_option($option_name, $value, false);
		}
	}
}
