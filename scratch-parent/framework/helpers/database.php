<?php if (!defined('FW')) die('Forbidden');

/** Framework Settings Options */
{
	/**
	 * Get a framework settings option value from database
	 *
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param null|bool $get_original_value Original value is that with no translations and other changes
	 * @return mixed|null
	 */
	function fw_get_db_settings_option($option_id = null, $get_original_value = null) {
		return FW_WP_Option::get('fw_options', $option_id, $get_original_value);
	}

	/**
	 * Set a framework settings option value in database
	 *
	 * @param null $option_id Specific option id (accepts multikey). null - all options
	 * @param mixed $value
	 */
	function fw_set_db_settings_option($option_id = null, $value) {
		FW_WP_Option::set('fw_options', $option_id, $value);
	}
}

/** Post Options */
{
	/**
	 * Get post option value from database
	 *
	 * @param int $post_id
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param null|bool $get_original_value Original value is that with no translations and other changes
	 * @return mixed|null
	 */
	function fw_get_db_post_option($post_id, $option_id = null, $get_original_value = null) {
		$option_id = 'fw_options'. ($option_id !== null ? '/'. $option_id : '');

		return FW_WP_Post_Meta::get($post_id, $option_id, $get_original_value);
	}

	/**
	 * Set post option value in database
	 *
	 * @param int $post_id
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param $value
	 */
	function fw_set_db_post_option($post_id, $option_id = null, $value) {
		$option_id = 'fw_options'. ($option_id !== null ? '/'. $option_id : '');

		FW_WP_Post_Meta::set($post_id, $option_id, $value);
	}
}

/** Terms Options */
{
	/**
	 * Get term option value from database
	 *
	 * @param int $term_id
	 * @param string $taxonomy
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param null|bool $get_original_value Original value is that with no translations and other changes
	 * @return mixed|null
	 */
	function fw_get_db_term_option($term_id, $taxonomy, $option_id = null, $get_original_value = null) {
		if (!taxonomy_exists($taxonomy)) {
			return null;
		}

		$option_name = 'fw_taxonomy_'. $taxonomy .'_options';
		$option_id = $term_id . ($option_id === null ? null : '/'. $option_id);

		return FW_WP_Option::get($option_name, $option_id, $get_original_value);
	}

	/**
	 * Set term option value in database
	 *
	 * @param int $term_id
	 * @param string $taxonomy
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param mixed $value
	 * @return null
	 */
	function fw_set_db_term_option($term_id, $taxonomy, $option_id = null, $value) {
		if (!taxonomy_exists($taxonomy)) {
			return null;
		}

		$option_name = 'fw_taxonomy_'. $taxonomy .'_options';
		$option_id = $term_id . ($option_id === null ? null : '/'. $option_id);

		FW_WP_Option::set($option_name, $option_id, $value);
	}
}

/**
 * Extensions Data
 *
 * Used by extensions to store custom data in database.
 * By using these functions, extension prevent database spam with wp options for each extension,
 * because these functions store all data in one wp option.
 */
{
	/**
	 * Get some extension's data from database
	 *
	 * @param string $extension_name Name of the extension that owns the data
	 * @param string|null $multi_key The key of the data you want to get. null - all data
	 * @param null|bool $get_original_value  Original value is that with no translations and other changes
	 * @return mixed|null
	 */
	function fw_get_db_extension_data($extension_name, $multi_key = null, $get_original_value = null) {
		if (!fw()->extensions->get($extension_name)) {
			trigger_error('Invalid extension: '. $extension_name, E_USER_WARNING);
			return;
		}

		if ($multi_key) {
			$multi_key = $extension_name .'/'. $multi_key;
		} else {
			$multi_key = $extension_name;
		}

		return FW_WP_Option::get('fw_extensions', $multi_key, $get_original_value);
	}

	/**
	 * Set some extension's data in database
	 *
	 * @param string $extension_name Name of the extension that owns the data
	 * @param string|null $multi_key The key of the data you want to set. null - all data
	 * @param mixed $value
	 */
	function fw_set_db_extension_data($extension_name, $multi_key = null, $value) {
		if (!fw()->extensions->get($extension_name)) {
			trigger_error('Invalid extension: '. $extension_name, E_USER_WARNING);
			return;
		}

		if ($multi_key) {
			$multi_key = $extension_name .'/'. $multi_key;
		} else {
			$multi_key = $extension_name;
		}

		FW_WP_Option::set('fw_extensions', $multi_key, $value);
	}
}
