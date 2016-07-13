<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Theme Settings Options
class FW_Db_Options_Model_Settings extends FW_Db_Options_Model {
	protected function get_id() {
		return 'settings';
	}

	protected function get_options($item_id, array $extra_data = array()) {
		return fw()->theme->get_settings_options();
	}

	protected function get_values($item_id, array $extra_data = array()) {
		return FW_WP_Option::get('fw_theme_settings_options:'. fw()->theme->manifest->get_id(), null, array());
	}

	protected function set_values($item_id, $values, array $extra_data = array()) {
		FW_WP_Option::set('fw_theme_settings_options:' . fw()->theme->manifest->get_id(), null, $values);
	}

	protected function get_fw_storage_params($item_id, array $extra_data = array()) {
		return array();
	}

	protected function _init() {
		/**
		 * Get a theme settings option value from the database
		 *
		 * @param string|null $option_id Specific option id (accepts multikey). null - all options
		 * @param null|mixed $default_value If no option found in the database, this value will be returned
		 * @param null|bool $get_original_value REMOVED https://github.com/ThemeFuse/Unyson/issues/1676
		 *
		 * @return mixed|null
		 */
		function fw_get_db_settings_option( $option_id = null, $default_value = null, $get_original_value = null ) {
			return FW_Db_Options_Model_Settings::_get_instance('settings')->get(null, $option_id, $default_value);
		}

		/**
		 * Set a theme settings option value in database
		 *
		 * @param null $option_id Specific option id (accepts multikey). null - all options
		 * @param mixed $value
		 */
		function fw_set_db_settings_option( $option_id = null, $value ) {
			FW_Db_Options_Model_Settings::_get_instance('settings')->set(null, $option_id, $value);
		}
	}
}
new FW_Db_Options_Model_Settings();

// Post Options
class FW_Db_Options_Model_Post extends FW_Db_Options_Model {
	protected function get_id() {
		return 'post';
	}

	private function get_cache_key($key) {
		return 'fw-options-model:'. $this->get_id() .'/'. $key;
	}

	private function get_post_id($post_id) {
		$post_id = intval($post_id);

		try {
			// Prevent too often execution of wp_get_post_autosave() because it does WP Query
			return FW_Cache::get($cache_key = $this->get_cache_key('id/'. $post_id));
		} catch (FW_Cache_Not_Found_Exception $e) {
			if ( ! $post_id ) {
				/** @var WP_Post $post */
				global $post;

				if ( ! $post ) {
					return null;
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
				if ( is_preview() && is_object($preview = wp_get_post_autosave($post->ID)) ) {
					$post_id = $preview->ID;
				}
			}

			FW_Cache::set($cache_key, $post_id);

			return $post_id;
		}
	}

	private function get_post_type($post_id) {
		$post_id = $this->get_post_id($post_id);

		try {
			return FW_Cache::get($cache_key = $this->get_cache_key('type/'. $post_id));
		} catch (FW_Cache_Not_Found_Exception $e) {
			FW_Cache::set(
				$cache_key,
				$post_type = get_post_type(
					($post_revision_id = wp_is_post_revision($post_id)) ? $post_revision_id : $post_id
				)
			);

			return $post_type;
		}
	}

	protected function get_options($item_id, array $extra_data = array()) {
		$post_type = $this->get_post_type($item_id);

		if (apply_filters('fw_get_db_post_option:fw-storage-enabled',
			/**
			 * Slider extension has too many fw_get_db_post_option()
			 * inside post options altering filter and it creates recursive mess.
			 * add_filter() was added in Slider extension
			 * but this hardcode can be replaced with `true`
			 * only after all users will install new version 1.1.15.
			 */
			$post_type !== 'fw-slider',
			$post_type
		)) {
			return fw()->theme->get_post_options( $post_type );
		} else {
			return array();
		}
	}

	protected function get_values($item_id, array $extra_data = array()) {
		return FW_WP_Meta::get( 'post', $item_id, 'fw_options', array() );
	}

	protected function set_values($item_id, $values, array $extra_data = array()) {
		FW_WP_Meta::set( 'post', $item_id, 'fw_options', $values );
	}

	protected function get_fw_storage_params($item_id, array $extra_data = array()) {
		return array( 'post-id' => $item_id );
	}

	protected function _get_cache_key($key, $item_id, array $extra_data = array()) {
		if ($key === 'options') {
			return $this->get_post_type($item_id); // Cache options grouped by post-type, not by post id
		} else {
			return $item_id;
		}
	}

	protected function _after_set($post_id, $option_id, $sub_keys, $old_value, array $extra_data = array()) {
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

	protected function _init() {
		/**
		 * Get post option value from the database
		 *
		 * @param null|int $post_id
		 * @param string|null $option_id Specific option id (accepts multikey). null - all options
		 * @param null|mixed $default_value If no option found in the database, this value will be returned
		 * @param null|bool $get_original_value REMOVED https://github.com/ThemeFuse/Unyson/issues/1676
		 *
		 * @return mixed|null
		 */
		function fw_get_db_post_option($post_id = null, $option_id = null, $default_value = null, $get_original_value = null) {
			return FW_Db_Options_Model::_get_instance('post')->get(intval($post_id), $option_id, $default_value);
		}

		/**
		 * Set post option value in database
		 *
		 * @param null|int $post_id
		 * @param string|null $option_id Specific option id (accepts multikey). null - all options
		 * @param $value
		 */
		function fw_set_db_post_option( $post_id = null, $option_id = null, $value ) {
			FW_Db_Options_Model::_get_instance('post')->set(intval($post_id), $option_id, $value);
		}

		// todo: add_action() to clear the FW_Cache
	}
}
new FW_Db_Options_Model_Post();

// Term Options
class FW_Db_Options_Model_Term extends FW_Db_Options_Model {
	protected function get_id() {
		return 'term';
	}

	protected function get_values($item_id, array $extra_data = array()) {
		return FW_WP_Meta::get( 'fw_term', $item_id, 'fw_options', array(), null );
	}

	protected function set_values($item_id, $values, array $extra_data = array()) {
		FW_WP_Meta::set( 'fw_term', $item_id, 'fw_options', $values );
	}

	protected function get_options($item_id, array $extra_data = array()) {
		return fw()->theme->get_taxonomy_options($extra_data['taxonomy']);
	}

	protected function get_fw_storage_params($item_id, array $extra_data = array()) {
		return array(
			'term-id' => $item_id,
			'taxonomy' => $extra_data['taxonomy'],
		);
	}

	protected function _get_cache_key($key, $item_id, array $extra_data = array()) {
		if ($key === 'options') {
			return $extra_data['taxonomy']; // Cache options grouped by taxonomy, not by term id
		} else {
			return $item_id;
		}
	}

	protected function _init() {
		/**
		 * Get term option value from the database
		 *
		 * @param int $term_id
		 * @param string $taxonomy
		 * @param string|null $option_id Specific option id (accepts multikey). null - all options
		 * @param null|mixed $default_value If no option found in the database, this value will be returned
		 * @param null|bool $get_original_value REMOVED https://github.com/ThemeFuse/Unyson/issues/1676
		 *
		 * @return mixed|null
		 */
		function fw_get_db_term_option( $term_id, $taxonomy, $option_id = null, $default_value = null, $get_original_value = null ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				return null;
			}

			return FW_Db_Options_Model::_get_instance('term')->get(intval($term_id), $option_id, $default_value, array(
				'taxonomy' => $taxonomy
			));
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

			FW_Db_Options_Model::_get_instance('term')->set(intval($term_id), $option_id, $value, array(
				'taxonomy' => $taxonomy
			));
		}
	}
}
new FW_Db_Options_Model_Term();

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
	 * @param null|bool $get_original_value REMOVED https://github.com/ThemeFuse/Unyson/issues/1676
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

/** Customizer Framework Options */
{
	/**
	 * Get a customizer framework option value from the database
	 *
	 * @param string|null $option_id Specific option id (accepts multikey). null - all options
	 * @param null|mixed $default_value If no option found in the database, this value will be returned
	 *
	 * @return mixed|null
	 */
	function fw_get_db_customizer_option( $option_id = null, $default_value = null ) {
		static $merge_values_with_defaults = false;

		if (empty($option_id)) {
			$sub_keys = null;
		} else {
			$option_id = explode('/', $option_id); // 'option_id/sub/keys'
			$_option_id = array_shift($option_id); // 'option_id'
			$sub_keys = empty($option_id) ? null : implode('/', $option_id); // 'sub/keys'
			$option_id = $_option_id;
			unset($_option_id);
		}

		try {
			/**
			 * Cached because values are merged with extracted default values
			 */
			$values = FW_Cache::get($cache_key = 'fw_customizer_options/values');
		} catch (FW_Cache_Not_Found_Exception $e) {
			FW_Cache::set(
				$cache_key,
				// note: this contains only changed controls/options
				$values = (array)get_theme_mod(FW_Option_Type::get_default_name_prefix(), array())
			);

			$merge_values_with_defaults = true;
		}

		/**
		 * If db value is not found and default value is provided
		 * return default value before loading options file
		 * to prevent infinite recursion in case if this function is called in options file
		 */
		if ( ! is_null($default_value) ) {
			if ( empty( $option_id ) ) {
				if ( empty( $values ) && is_array( $default_value ) ) {
					return $default_value;
				}
			} else {
				if ( is_null( $sub_keys ) ) {
					if ( ! isset( $values[ $option_id ] ) ) {
						return $default_value;
					}
				} else {
					if ( is_null( fw_akg( $sub_keys, $values[ $option_id ] ) ) ) {
						return $default_value;
					}
				}
			}
		}

		try {
			$options = FW_Cache::get( $cache_key = 'fw_only_options/customizer' );
		} catch (FW_Cache_Not_Found_Exception $e) {
			FW_Cache::set($cache_key, array()); // prevent recursion
			FW_Cache::set(
				$cache_key,
				$options = fw_extract_only_options(fw()->theme->get_customizer_options())
			);
		}

		/**
		 * Complete missing db values with default values from options array
		 */
		if ($merge_values_with_defaults) {
			$merge_values_with_defaults = false;
			FW_Cache::set(
				'fw_customizer_options/values',
				$values = array_merge(fw_get_options_values_from_input($options, array()), $values)
			);
		}

		if (empty($option_id)) {
			foreach ($options as $id => $option) {
				$values[$id] = fw()->backend->option_type($options[$id]['type'])->storage_load(
					$id, $options[$id], isset($values[$id]) ? $values[$id] : null, array()
				);
			}
		} else {
			if (isset($options[$option_id])) {
				$values[ $option_id ] = fw()->backend->option_type( $options[ $option_id ]['type'] )->storage_load(
					$option_id,
					$options[ $option_id ],
					isset($values[ $option_id ]) ? $values[ $option_id ] : null,
					array()
				);
			}
		}

		if (empty($option_id)) {
			return (empty($values) && is_array($default_value)) ? $default_value : $values;
		} else {
			if (is_null($sub_keys)) {
				return isset($values[$option_id]) ? $values[$option_id] : $default_value;
			} else {
				return fw_akg($sub_keys, $values[$option_id], $default_value);
			}
		}

		// todo: create a general helper to get/set db options, which will care about cache and fw-storage processing
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
	 * @param null|bool $get_original_value REMOVED https://github.com/ThemeFuse/Unyson/issues/1676
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
