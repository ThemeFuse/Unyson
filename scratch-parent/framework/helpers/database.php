<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Framework Settings Options */
{
	/**
	 * Get a framework settings option value from database
	 *
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param null|bool $get_original_value Original value is that with no translations and other changes
	 *
	 * @return mixed|null
	 */
	function fw_get_db_settings_option( $option_id = null, $default_value = null, $get_original_value = null ) {
		$value = FW_WP_Option::get( 'fw_options', $option_id, $default_value, $get_original_value );

		if ( is_null( $value ) ) {
			/**
			 * Maybe the options was never saved or the given option id does not exists
			 * Extract the default values from the options array and try to find there the option id
			 */

			$cache_key = 'fw_default_options_values/settings';

			try {
				$all_options_values = FW_Cache::get( $cache_key );
			} catch ( FW_Cache_Not_Found_Exception $e ) {
				// extract the default values from options array
				$all_options_values = fw_get_options_values_from_input(
					fw()->theme->get_settings_options(),
					array()
				);

				FW_Cache::set( $cache_key, $all_options_values );
			}

			if ( empty( $option_id ) ) {
				// option id not specified, return all options values
				return $all_options_values;
			} else {
				return fw_akg( $option_id, $all_options_values, $default_value );
			}
		} else {
			return $value;
		}
	}

	/**
	 * Set a framework settings option value in database
	 *
	 * @param null $option_id Specific option id (accepts multikey). null - all options
	 * @param mixed $value
	 */
	function fw_set_db_settings_option( $option_id = null, $value ) {
		FW_WP_Option::set( 'fw_options', $option_id, $value );
	}
}

/** Post Options */
{
	/**
	 * Get post option value from database
	 *
	 * @param int $post_id
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param null|bool $get_original_value Original value is that with no translations and other changes
	 *
	 * @return mixed|null
	 */
	function fw_get_db_post_option( $post_id, $option_id = null, $default_value = null, $get_original_value = null ) {
		$option_id = 'fw_options' . ( $option_id !== null ? '/' . $option_id : '' );

		return FW_WP_Post_Meta::get( $post_id, $option_id, $default_value, $get_original_value );
	}

	/**
	 * Set post option value in database
	 *
	 * @param int $post_id
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param $value
	 */
	function fw_set_db_post_option( $post_id, $option_id = null, $value ) {
		$option_id = 'fw_options' . ( $option_id !== null ? '/' . $option_id : '' );

		FW_WP_Post_Meta::set( $post_id, $option_id, $value );
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
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param null|bool $get_original_value Original value is that with no translations and other changes
	 *
	 * @return mixed|null
	 */
	function fw_get_db_term_option( $term_id, $taxonomy, $option_id = null, $default_value = null, $get_original_value = null ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return null;
		}

		$option_name = 'fw_taxonomy_' . $taxonomy . '_options';
		$term_option_id   = $term_id . ( $option_id === null ? null : '/' . $option_id );

		$value = FW_WP_Option::get( $option_name, $term_option_id, $default_value, $get_original_value );

		if ( is_null( $value ) ) {
			/**
			 * Maybe the options was never saved or the given option id does not exists
			 * Extract the default values from the options array and try to find there the option id
			 */

			$cache_key = 'fw_default_options_values/taxonomy/'. $taxonomy;

			try {
				$all_options_values = FW_Cache::get( $cache_key );
			} catch ( FW_Cache_Not_Found_Exception $e ) {
				// extract the default values from options array
				$all_options_values = fw_get_options_values_from_input(
					fw()->theme->get_taxonomy_options($taxonomy),
					array()
				);

				FW_Cache::set( $cache_key, $all_options_values );
			}

			if ( empty( $option_id ) ) {
				// option id not specified, return all options values
				return $all_options_values;
			} else {
				return fw_akg( $option_id, $all_options_values, $default_value );
			}
		} else {
			return $value;
		}
	}

	/**
	 * Set term option value in database
	 *
	 * @param int $term_id
	 * @param string $taxonomy
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param mixed $value
	 *
	 * @return null
	 */
	function fw_set_db_term_option( $term_id, $taxonomy, $option_id = null, $value ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return null;
		}

		$option_name = 'fw_taxonomy_' . $taxonomy . '_options';
		$option_id   = $term_id . ( $option_id === null ? null : '/' . $option_id );

		FW_WP_Option::set( $option_name, $option_id, $value );
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
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param null|bool $get_original_value Original value is that with no translations and other changes
	 *
	 * @return mixed|null
	 */
	function fw_get_db_extension_data( $extension_name, $multi_key = null, $default_value = null, $get_original_value = null ) {
		if ( ! fw()->extensions->get( $extension_name ) ) {
			trigger_error( 'Invalid extension: ' . $extension_name, E_USER_WARNING );

			return null;
		}

		if ( $multi_key ) {
			$multi_key = $extension_name . '/' . $multi_key;
		} else {
			$multi_key = $extension_name;
		}

		return FW_WP_Option::get( 'fw_extensions', $multi_key, $default_value, $get_original_value );
	}

	/**
	 * Set some extension's data in database
	 *
	 * @param string $extension_name Name of the extension that owns the data
	 * @param string|null $multi_key The key of the data you want to set. null - all data
	 * @param mixed $value
	 */
	function fw_set_db_extension_data( $extension_name, $multi_key = null, $value ) {
		if ( ! fw()->extensions->get( $extension_name ) ) {
			trigger_error( 'Invalid extension: ' . $extension_name, E_USER_WARNING );

			return;
		}

		if ( $multi_key ) {
			$multi_key = $extension_name . '/' . $multi_key;
		} else {
			$multi_key = $extension_name;
		}

		FW_WP_Option::set( 'fw_extensions', $multi_key, $value );
	}
}

{
	/**
	 * Get user meta set by w specific extension
	 * @param int $user_id
	 * @param string $extension_name
	 *
	 * If the extension doesn't exist or is disabled, or meta key doesn't exist, returns null,
	 * else returns the meta key value
	 * @return mixed|null
	 */
	function fw_get_db_extension_user_data( $user_id, $extension_name ) {
		if ( ! fw()->extensions->get( $extension_name ) ) {
			trigger_error( 'Invalid extension: ' . $extension_name, E_USER_WARNING );

			return null;
		}
		$data = get_user_meta( $user_id, 'fw_data', true );
		if ( isset( $data[ $extension_name ] ) ) {
			return $data[ $extension_name ];
		}

		return null;
	}

	/**
	 * @param int $user_id
	 * @param string $extension_name
	 * @param mixed $value
	 *
	 * In case the extension doesn't exist or is disabled, or the value is equal to previous, returns false
	 * @return bool|int
	 */
	function fw_set_db_extension_user_data( $user_id, $extension_name, $value ) {
		if ( ! fw()->extensions->get( $extension_name ) ) {
			trigger_error( 'Invalid extension: ' . $extension_name, E_USER_WARNING );

			return false;
		}
		$data                    = get_user_meta( $user_id, 'fw_data', true );
		$data[ $extension_name ] = $value;
		return fw_update_user_meta( $user_id, 'fw_data', $data );
	}
}
