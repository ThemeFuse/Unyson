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

			/**
			 * Check if is Preview and use the preview post_id instead of real/current post id
			 *
			 * Note: WordPress changes the global $post content on preview:
			 * 1. https://github.com/WordPress/WordPress/blob/2096b451c704715db3c4faf699a1184260deade9/wp-includes/query.php#L3573-L3583
			 * 2. https://github.com/WordPress/WordPress/blob/4a31dd6fe8b774d56f901a29e72dcf9523e9ce85/wp-includes/revision.php#L485-L528
			 */
			if (
				is_preview()
				&&
			    is_object($preview = wp_get_post_autosave($post->ID))
			) {
				$post_id = $preview->ID;
			}
		}

		$post_type = get_post_type( $post_id );

		/**
		 * Before fw_db_option_storage_load() feature
		 * there was possible to call fw_get_db_post_option() and it worked fine
		 * but after v2.5.0 it's not possible anymore (it creates an infinite recursion)
		 * but the Slider extension does that and maybe other extensions,
		 * so the solution is to check if it is recursion, to not load the options array (disable the storage feature)
		 */
		static $recursion = array();

		if (!isset($recursion[$post_type])) {
			$recursion[$post_type] = false;
		}

		if ($recursion[$post_type]) {
			/**
			 * Allow known post types that sure don't have options with 'fw-storage' parameter
			 */
			if (!in_array($post_type, array('fw-slider'))) {
				trigger_error(
					'Infinite recursion detected in post type "'. $post_type .'" options caused by '. __FUNCTION__ .'()',
					E_USER_WARNING
				);
			}

			$options = array();
		} else {
			$recursion[$post_type] = true;

			$options = fw_extract_only_options( // todo: cache this (by post type)
				fw()->theme->get_post_options( $post_type )
			);

			$recursion[$post_type] = false;
		}

		if ($option_id) {
			$option_id = explode('/', $option_id); // 'option_id/sub/keys'
			$_option_id = array_shift($option_id); // 'option_id'
			$sub_keys  = implode('/', $option_id); // 'sub/keys'
			$option_id = $_option_id;
			unset($_option_id);

			$value = FW_WP_Meta::get(
				'post',
				$post_id,
				'fw_options/' . $option_id,
				null,
				$get_original_value
			);

			if (isset($options[$option_id])) {
				$value = fw()->backend->option_type($options[$option_id]['type'])->storage_load(
					$option_id,
					$options[$option_id],
					$value,
					array( 'post-id' => $post_id, )
				);
			}

			if ($sub_keys) {
				return fw_akg($sub_keys, $value, $default_value);
			} else {
				return is_null($value) ? $default_value : $value;
			}
		} else {
			$value = FW_WP_Meta::get(
				'post',
				$post_id,
				'fw_options',
				$default_value,
				$get_original_value
			);

			if (!is_array($value)) {
				$value = array();
			}

			foreach ($options as $_option_id => $_option) {
				$value[$_option_id] = fw()->backend->option_type($_option['type'])->storage_load(
					$_option_id,
					$_option,
					isset($value[$_option_id]) ? $value[$_option_id] : null,
					array( 'post-id' => $post_id, )
				);
			}

			return $value;
		}
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

		$options = fw_extract_only_options( // todo: cache this (by post type)
			fw()->theme->get_post_options(get_post_type($post_id))
		);

		$sub_keys = null;

		if ($option_id) {
			$option_id = explode('/', $option_id); // 'option_id/sub/keys'
			$_option_id = array_shift($option_id); // 'option_id'
			$sub_keys  = implode('/', $option_id); // 'sub/keys'
			$option_id = $_option_id;
			unset($_option_id);

			$old_value = fw_get_db_post_option($post_id, $option_id);

			if ($sub_keys) { // update sub_key in old_value and use the entire value
				$new_value = $old_value;
				fw_aks($sub_keys, $value, $new_value);
				$value = $new_value;
				unset($new_value);

				$old_value = fw_akg($sub_keys, $old_value);
			}

			if (isset($options[$option_id])) {
				$value = fw()->backend->option_type($options[$option_id]['type'])->storage_save(
					$option_id,
					$options[$option_id],
					$value,
					array( 'post-id' => $post_id, )
				);
			}

			FW_WP_Meta::set( 'post', $post_id, 'fw_options/'. $option_id, $value );
		} else {
			$old_value = fw_get_db_post_option($post_id);

			if (!is_array($value)) {
				$value = array();
			}

			foreach ($value as $_option_id => $_option_value) {
				if (isset($options[$_option_id])) {
					$value[$_option_id] = fw()->backend->option_type($options[$_option_id]['type'])->storage_save(
						$_option_id,
						$options[$_option_id],
						$_option_value,
						array( 'post-id' => $post_id, )
					);
				}
			}

			FW_WP_Meta::set( 'post', $post_id, 'fw_options', $value );
		}

		/**
		 * @deprecated
		 */
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
			$option_id,
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
			explode('/', $sub_keys),
			/**
			 * Old post option(s) value
			 * @since 2.3.3
			 */
			$old_value
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
	 * @param string $keys
	 *
	 * If the extension doesn't exist or is disabled, or meta key doesn't exist, returns null,
	 * else returns the meta key value
	 *
	 * @return mixed|null
	 */
	function fw_get_db_extension_user_data( $user_id, $extension_name, $keys = null ) {
		if ( ! fw()->extensions->get( $extension_name ) ) {
			trigger_error( 'Invalid extension: ' . $extension_name, E_USER_WARNING );

			return null;
		}
		$data = get_user_meta( $user_id, 'fw_data', true );

		if ( is_null( $keys ) ) {
			return fw_akg( $extension_name, $data );
		}

		return fw_akg( $extension_name . '/' . $keys, $data );
	}

	/**
	 * @param int $user_id
	 * @param string $extension_name
	 * @param mixed $value
	 * @param string $keys
	 *
	 * In case the extension doesn't exist or is disabled, or the value is equal to previous, returns false
	 *
	 * @return bool|int
	 */
	function fw_set_db_extension_user_data( $user_id, $extension_name, $value, $keys = null ) {
		if ( ! fw()->extensions->get( $extension_name ) ) {
			trigger_error( 'Invalid extension: ' . $extension_name, E_USER_WARNING );

			return false;
		}
		$data                    = get_user_meta( $user_id, 'fw_data', true );

		if ( $keys == null ) {
			fw_aks( $extension_name, $value, $data );
		} else {
			fw_aks( $extension_name . '/' . $keys, $value, $data );
		}

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
		$db_values = get_theme_mod(FW_Option_Type::get_default_name_prefix(), null);

		if (
			!is_null($default_value)
			&&
			is_null($option_id ? fw_akg($option_id, $db_values) : $db_values)
		) {
			/**
			 * Default value was provided in case db value is empty.
			 *
			 * Do not extract default values from options files (below)
			 * maybe this function was called from options files and it will cause infinite loop,
			 * that's why the developer provided a default value to prevent that.
			 */
			return $default_value;
		}

		if (is_null($db_values)) {
			$db_values = array();
		}

		if (
			is_null($option_id)
			||
			(
				($base_key = explode('/', $option_id)) // note: option_id can be a multi-key 'a/b/c'
				&&
				($base_key = array_shift($base_key))
				&&
				!array_key_exists($base_key, $db_values)
			)
		) {
			// extract options default values
			{
				$cache_key = 'fw_default_options_values/customizer';

				try {
					$default_values = FW_Cache::get( $cache_key );
				} catch ( FW_Cache_Not_Found_Exception $e ) {
					// extract the default values from options array
					$default_values = fw_get_options_values_from_input(
						fw()->theme->get_customizer_options(),
						array()
					);

					FW_Cache::set( $cache_key, $default_values );
				}
			}

			$db_values = array_merge($default_values, $db_values);
		}

		return is_null($option_id)
			? $db_values
			: fw_akg($option_id, $db_values, $default_value);
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

{
	/**
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 * @param array $params
	 *
	 * @return mixed
	 *
	 * @since 2.5.0
	 */
	function fw_db_option_storage_save($id, array $option, $value, array $params = array()) {
		if (
			!empty($option['fw-storage'])
			&&
			($storage = is_array($option['fw-storage'])
				? $option['fw-storage']
				: array('type' => $option['fw-storage'])
			)
			&&
			!empty($storage['type'])
			&&
			($storage_type = fw_db_option_storage_type($storage['type']))
		) {
			$option['fw-storage'] = $storage;
		} else {
			return $value;
		}

		/** @var FW_Option_Storage_Type $storage_type */

		return $storage_type->save($id, $option, $value, $params);
	}

	/**
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 * @param array $params
	 *
	 * @return mixed
	 *
	 * @since 2.5.0
	 */
	function fw_db_option_storage_load($id, array $option, $value, array $params = array()) {
		if (
			!empty($option['fw-storage'])
			&&
			($storage = is_array($option['fw-storage'])
				? $option['fw-storage']
				: array('type' => $option['fw-storage'])
			)
			&&
			!empty($storage['type'])
			&&
			($storage_type = fw_db_option_storage_type($storage['type']))
		) {
			$option['fw-storage'] = $storage;
		} else {
			return $value;
		}

		/** @var FW_Option_Storage_Type $storage_type */

		return $storage_type->load($id, $option, $value, $params);
	}

	/**
	 * @param null|string $type
	 * @return FW_Option_Storage_Type|FW_Option_Storage_Type[]|null
	 * @since 2.5.0
	 */
	function fw_db_option_storage_type($type = null) {
		static $types = null;

		if (is_null($types)) {
			$dir = fw_get_framework_directory('/includes/option-storage');

			if (!class_exists('FW_Option_Storage_Type')) {
				require_once $dir .'/class-fw-option-storage-type.php';
			}
			if (!class_exists('_FW_Option_Storage_Type_Register')) {
				require_once $dir .'/class--fw-option-storage-type-register.php';
			}

			$access_key = new FW_Access_Key('fw:option-storage-register');
			$register = new _FW_Option_Storage_Type_Register($access_key->get_key());

			{
				require_once $dir .'/type/class-fw-option-storage-type-post-meta.php';
				$register->register(new FW_Option_Storage_Type_Post_Meta());

				require_once $dir .'/type/class-fw-option-storage-type-wp-option.php';
				$register->register(new FW_Option_Storage_Type_WP_Option());
			}

			do_action('fw:option-storage-types:register', $register);

			$types = $register->_get_types($access_key);
		}

		if (empty($type)) {
			return $types;
		} elseif (isset($types[$type])) {
			return $types[$type];
		} else {
			return null;
		}
	}
}

/**
 * "UPDATE ... SET foo = 'very big string' WHERE ..."
 * will throw mysql errors (mysql gone away or packet limit reached)
 *
 * This function does:
 * "UPDATE ... SET foo = 'very' WHERE ..."
 * "UPDATE ... SET foo = CONCAT(foo, ' big') WHERE ..."
 * "UPDATE ... SET foo = CONCAT(foo, ' string') WHERE ..."
 *
 * @param string $table
 * @param array $cols {'col_name' => 'value'}
 * @param array $where {'col_name' => 'value'}
 *
 * @return bool
 */
function fw_db_update_big_data($table, array $cols, array $where) {
	/** @var WPDB $wpdb */
	global $wpdb;

	/**
	 * This feature is disabled by default because it's slower than a regular one query update.
	 * If your theme has a lot of shortcode options, the post content is almost always big
	 * and you get mysql errors on post save, then enable this feature.
	 */
	if (!apply_filters('fw_db_big_data_update_enable', false)) {
		return $wpdb->update($table, $cols, $where);
	}

	/**
	 * Total length of all columns allowed per one update
	 */
	$max_length = 900000;

	/**
	 * Sort columns by length
	 */
	{
		$cols_total_length = 0;
		$cols_lengths = array();

		foreach (array_keys($cols) as $col_name) {
			$col_length = mb_strlen( $cols[ $col_name ] );
			$cols_lengths[ $col_length ] = $col_name;
			$cols_total_length += $col_length;
		}

		if ($cols_total_length <= $max_length) {
			/**
			 * Length limit not reached, do regular update
			 */
			return $wpdb->update($table, $cols, $where);
		}

		ksort($cols_lengths, SORT_NUMERIC);

		foreach ($cols_lengths as $col_name) {
			$col_val = $cols[$col_name];
			unset($cols[$col_name]);
			$cols[$col_name] = $col_val;
		}
	}

	// fixme: use $wpdb->process_fields(), but it's protected ...
	{
		foreach ( array_keys($where) as $field ) {
			$where[] = "`$field` = " . $wpdb->prepare('%s', $where[$field]);
			unset($where[$field]);
		}

		$where = implode(' AND ', $where);
	}

	$first_extract = true;
	$first_update = true;

	while ($cols) {
		$row = array();
		$available_length = $max_length;
		$length_per_column = abs($available_length / count($cols));
		$length_per_column_extra = 0; // not used length, available for the next columns
		$col_names = array_keys($cols);

		while ($col_name = array_shift($col_names)) {
			$row[ $col_name ] = mb_substr( $cols[ $col_name ], 0, $length_per_column + $length_per_column_extra );
			$column_length = mb_strlen($row[ $col_name ]);
			$cols[ $col_name ] = mb_substr( $cols[ $col_name ], $column_length );

			/**
			 * If the string was cut between a slashed character, for e.g. 'hi\"' was cut 'hi\'
			 * Append next characters until the slashing is closed
			 */
			if (($last_char = mb_substr($row[ $col_name ], -1)) === '\\') {
				$slashes_length = 0;

				while ( $last_char === '\\' && ($last_char = mb_substr( $cols[ $col_name ], $slashes_length, 1 )) ) {
					$row[ $col_name ] .= $last_char;
					++$slashes_length;
				}

				if ($slashes_length) {
					$column_length += $slashes_length;
					$cols[ $col_name ] = mb_substr( $cols[ $col_name ], $slashes_length );
				}
			}

			$length_per_column_extra += $length_per_column + $length_per_column_extra - $column_length;

			if (empty($cols[ $col_name ])) {
				unset($cols[ $col_name ]);
			}

			if (($available_length = $available_length - $column_length) < 1 && !empty($col_names)) {
				if ($first_extract) { // should not happen, anyway check just in case
					//trigger_error('Initial update failed (table name: '. $table .')', E_USER_WARNING);
					return false;
				} else {
					break;
				}
			}
		}

		$first_extract = false;

		if ($first_update) {
			$sql = array();
			foreach ( array_keys( $row ) as $col_name ) {
				$sql[] = '`' . esc_sql( $col_name ) . '` = ' . $wpdb->prepare( '%s', $row[ $col_name ] );
			}
			$sql = implode( ', ', $sql );
		} else {
			$sql = array();
			foreach ( array_keys( $row ) as $col_name ) {
				$sql[] = '`' . esc_sql( $col_name ) . '` = CONCAT( `' . esc_sql( $col_name ) . '`'
				         . ' , ' . $wpdb->prepare( '%s', $row[ $col_name ] ) . ')';
			}
			$sql = implode( ', ', $sql );
		}

		$sql = implode( " \n", array( "UPDATE {$table} SET", $sql, 'WHERE ' . $where ) );

		if ( false === $wpdb->query($sql) ) {
			//trigger_error('Update failed (table name: '. $table .')', E_USER_WARNING);
			return false;
		}

		unset($sql);

		$first_update = false;
	}

	return true;
}
