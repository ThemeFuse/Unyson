<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Theme Settings Options */
{
	/**
	 * Get a theme settings option value from the database
	 *
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param null|bool $get_original_value Original value is that with no translations and other changes
	 *
	 * @return mixed|null
	 */
	function fw_get_db_settings_option( $option_id = null, $default_value = null, $get_original_value = null ) {
		$value = FW_WP_Option::get(
			'fw_theme_settings_options:' . fw()->theme->manifest->get_id(),
			$option_id, $default_value, $get_original_value
		);

		if (
			(!is_null($option_id) && is_null($value)) // a specific option_id was requested
			||
			(is_null($option_id) && empty($value)) // all options were requested but the db value is empty (this can happen after Reset)
		) {
			/**
			 * Maybe the options was never saved or the given option id does not exist
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
	 * Set a theme settings option value in database
	 *
	 * @param null $option_id Specific option id (accepts multikey). null - all options
	 * @param mixed $value
	 */
	function fw_set_db_settings_option( $option_id = null, $value ) {
		FW_WP_Option::set(
			'fw_theme_settings_options:' . fw()->theme->manifest->get_id(),
			$option_id, $value
		);
	}
}

/** Post Options */
{
	/**
	 * Get post option value from the database
	 *
	 * @param null|int $post_id
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param null|bool $get_original_value Original value is that with no translations and other changes
	 *
	 * @return mixed|null
	 */
	function fw_get_db_post_option( $post_id = null, $option_id = null, $default_value = null, $get_original_value = null ) {
		if ( ! $post_id ) {
			/** @var WP_Post $post */
			global $post;

			if ( ! $post ) {
				return $default_value;
			} else {
				$post_id = $post->ID;
			}
		}

		$option_id = 'fw_options' . ( $option_id !== null ? '/' . $option_id : '' );

		return FW_WP_Meta::get( 'post', $post_id, $option_id, $default_value, $get_original_value );
	}

	/**
	 * Set post option value in database
	 *
	 * @param null|int $post_id
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param $value
	 */
	function fw_set_db_post_option( $post_id = null, $option_id = null, $value ) {
		$post_id = intval($post_id);

		if ( ! $post_id ) {
			/** @var WP_Post $post */
			global $post;

			if ( ! $post ) {
				return;
			} else {
				$post_id = $post->ID;
			}
		}

		$sub_keys = explode('/', $option_id);
		$base_key = array_shift($sub_keys);

		$option_id = 'fw_options' . ( $option_id !== null ? '/' . $option_id : '' );

		FW_WP_Meta::set( 'post', $post_id, $option_id, $value );

		fw()->backend->_sync_post_separate_meta($post_id);

		/**
		 * @since 2.2.8
		 */
		do_action('fw_post_options_update',
			$post_id,
			/**
			 * Option id
			 * First level multi-key
			 *
			 * For e.g.
			 *
			 * if $option_id is 'hello/world/7'
			 * this will be 'hello'
			 */
			$base_key,
			/**
			 * The remaining sub-keys
			 *
			 * For e.g.
			 *
			 * if $option_id is 'hello/world/7'
			 * $option_id_keys will be array('world', '7')
			 *
			 * if $option_id is 'hello'
			 * $option_id_keys will be array()
			 */
			$sub_keys
		);
	}
}

/** Terms Options */
{
	/**
	 * Get term option value from the database
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

		$option_id = 'fw_options' . ( $option_id !== null ? '/' . $option_id : '' );

		return FW_WP_Meta::get( 'fw_term', $term_id, $option_id, $default_value, $get_original_value );
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
		$option_id = 'fw_options' . ( $option_id !== null ? '/' . $option_id : '' );

		FW_WP_Meta::set( 'fw_term', $term_id, $option_id, $value );
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
	 * Get extension's data from the database
	 *
	 * @param string $extension_name
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
	 * @param string $extension_name
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

/**
 * Extensions Settings Options
 */
{
	/**
	 * Get extension's settings option value from the database
	 *
	 * @param string $extension_name
	 * @param string|null $option_id
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * @param null|bool $get_original_value Original value is that with no translations and other changes
	 *
	 * @return mixed|null
	 */
	function fw_get_db_ext_settings_option( $extension_name, $option_id = null, $default_value = null, $get_original_value = null ) {
		if ( ! ( $extension = fw()->extensions->get( $extension_name ) ) ) {
			trigger_error( 'Invalid extension: ' . $extension_name, E_USER_WARNING );

			return null;
		}

		$value = FW_WP_Option::get( 'fw_ext_settings_options:' . $extension_name, $option_id, $default_value, $get_original_value );

		if ( is_null( $value ) ) {
			/**
			 * Maybe the options was never saved or the given option id does not exists
			 * Extract the default values from the options array and try to find there the option id
			 */

			$cache_key = 'fw_default_options_values/ext_settings:' . $extension_name;

			try {
				$all_options_values = FW_Cache::get( $cache_key );
			} catch ( FW_Cache_Not_Found_Exception $e ) {
				// extract the default values from options array
				$all_options_values = fw_get_options_values_from_input(
					$extension->get_settings_options(),
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
	 * Set extension's setting option value in database
	 *
	 * @param string $extension_name
	 * @param string|null $option_id
	 * @param mixed $value
	 */
	function fw_set_db_ext_settings_option( $extension_name, $option_id = null, $value ) {
		if ( ! fw()->extensions->get( $extension_name ) ) {
			trigger_error( 'Invalid extension: ' . $extension_name, E_USER_WARNING );

			return;
		}

		FW_WP_Option::set( 'fw_ext_settings_options:' . $extension_name, $option_id, $value );
	}
}

{
	/**
	 * Get user meta set by specific extension
	 *
	 * @param int $user_id
	 * @param string $extension_name
	 *
	 * If the extension doesn't exist or is disabled, or meta key doesn't exist, returns null,
	 * else returns the meta key value
	 *
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
	 *
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

/** Customizer Framework Options */
{
	/**
	 * Get a customizer framework option value from the database
	 *
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 * // fixme: Maybe add this parameter? @ param null|bool $get_original_value Original value is that with no translations and other changes
	 *
	 * @return mixed|null
	 */
	function fw_get_db_customizer_option( $option_id = null, $default_value = null ) {
		// note: this contains only changed controls/options
		$all_db_values = get_theme_mod(FW_Option_Type::get_default_name_prefix(), array());

		// extract options default values
		{
			$cache_key = 'fw_default_options_values/customizer';

			try {
				$all_default_values = FW_Cache::get( $cache_key );
			} catch ( FW_Cache_Not_Found_Exception $e ) {
				// extract the default values from options array
				$all_default_values = fw_get_options_values_from_input(
					fw()->theme->get_customizer_options(),
					array()
				);

				FW_Cache::set( $cache_key, $all_default_values );
			}
		}

		if (is_null($option_id)) {
			return array_merge(
				$all_default_values,
				$all_db_values
			);
		} else {
			$base_key = explode('/', $option_id); // note: option_id can be a multi-key 'a/b/c'
			$base_key = array_shift($base_key);

			$all_db_values = array_key_exists($base_key, $all_db_values)
				? $all_db_values
				: $all_default_values;

			return fw_akg(
				$option_id,
				$all_db_values,
				$default_value
			);
		}
	}

	/**
	 * Set a theme customizer option value in database
	 *
	 * @param null $option_id Specific option id (accepts multikey). null - all options
	 * @param mixed $value
	 */
	function fw_set_db_customizer_option( $option_id = null, $value ) {
		$db_value = get_theme_mod(FW_Option_Type::get_default_name_prefix(), array());

		if (is_null($option_id)) {
			$db_value = $value;
		} else {
			fw_aks($option_id, $value, $db_value);
		}

		set_theme_mod(
			FW_Option_Type::get_default_name_prefix(),
			$db_value
		);
	}
}
