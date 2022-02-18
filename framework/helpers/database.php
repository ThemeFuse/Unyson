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
		return array(
			'settings' => true
		);
	}

	protected function _after_set($item_id, $option_id, $sub_keys, $old_value, array $extra_data = array()) {
		/**
		 * @since 2.6.0
		 */
		do_action('fw_settings_options_update', array(
			/**
			 * Option id
			 * First level multi-key
			 * For e.g. if $option_id is 'hello/world/7' this will be 'hello'
			 */
			'option_id' => $option_id,
			/**
			 * The remaining sub-keys
			 * For e.g.
			 * if $option_id is 'hello/world/7' this will be array('world', '7')
			 * if $option_id is 'hello' this will be array()
			 */
			'sub_keys' => explode('/', $sub_keys),
			/**
			 * Old option(s) value
			 */
			'old_value' => $old_value
		));
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
		function fw_set_db_settings_option( $option_id = null, $value = '' ) {
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

	private function get_cache_key($key, $item_id = null, array $extra_data = array()) {
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
		return FW_WP_Meta::get( 'post', $this->get_post_id($item_id), 'fw_options', array() );
	}

	protected function set_values($item_id, $values, array $extra_data = array()) {
		FW_WP_Meta::set( 'post', $this->get_post_id($item_id), 'fw_options', $values );
	}

	protected function get_fw_storage_params($item_id, array $extra_data = array()) {
		return array( 'post-id' => $this->get_post_id($item_id) );
	}

	protected function _get_cache_key($key, $item_id, array $extra_data = array()) {
		if ($key === 'options') {
			// Cache options grouped by post-type, not by post id
			return ($post_type = $this->get_post_type($item_id)) ? $post_type : '?';
		} else {
			return $this->get_post_id($item_id);
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
			 * For e.g. if $option_id is 'hello/world/7' this will be 'hello'
			 */
			$option_id,
			/**
			 * The remaining sub-keys
			 * For e.g.
			 * if $option_id is 'hello/world/7' this will be array('world', '7')
			 * if $option_id is 'hello' this will be array()
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
		function fw_set_db_post_option( $post_id = null, $option_id = null, $value = '' ) {
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
		self::migrate($item_id);

		return (array)get_term_meta( $item_id, 'fw_options', true);
	}

	protected function set_values($item_id, $values, array $extra_data = array()) {
		self::migrate($item_id);

		update_term_meta($item_id, 'fw_options', $values);
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

	/**
	 * Cache termmeta table name if exists
	 * @var string|false
	 */
	private static $old_table_name;

	/**
	 * @return string|false
	 */
	private static function get_old_table_name() {
		if (is_null(self::$old_table_name)) {
			/** @var WPDB $wpdb */
			global $wpdb;

			$table_name = $wpdb->get_results( "show tables like '{$wpdb->prefix}fw_termmeta'", ARRAY_A );
			$table_name = $table_name ? array_pop($table_name[0]) : false;

			if ( $table_name && ! $wpdb->get_results( "SELECT 1 FROM `{$table_name}` LIMIT 1" ) ) {
				// The table is empty, delete it
				$wpdb->query( "DROP TABLE `{$table_name}`" );
				$table_name = false;
			}

			self::$old_table_name = $table_name;
		}

		return self::$old_table_name;
	}

	/**
	 * @internal
	 */
	public static function _action_switch_blog() {
		self::$old_table_name = null; // reset
	}

	/**
	 * When a term is deleted, delete its meta from old fw_termmeta table
	 *
	 * @param mixed $term_id
	 *
	 * @return void
	 * @internal
	 */
	public static function _action_fw_delete_term( $term_id ) {
		if ( ! ( $table_name = self::get_old_table_name() ) ) {
			return;
		}

		$term_id = (int) $term_id;

		if ( ! $term_id ) {
			return;
		}

		/** @var WPDB $wpdb */
		global $wpdb;

		$wpdb->delete( $table_name, array( 'fw_term_id' => $term_id ), array( '%d' ) );
	}

	/**
	 * In WP 4.4 was introduced native term meta https://codex.wordpress.org/Version_4.4#For_Developers
	 * All data from old table must be migrated to native term meta
	 * @param int $term_id
	 * @return bool
	 */
	private static function migrate($term_id) {
		global $wpdb; /** @var wpdb $wpdb */

		if (
			( $old_table_name = self::get_old_table_name() )
			&&
			( $value = $wpdb->get_col( $wpdb->prepare(
				"SELECT meta_value FROM `{$old_table_name}` WHERE fw_term_id = %d AND meta_key = 'fw_options' LIMIT 1",
				$term_id
			) ) )
			&&
			( $value = unserialize( $value[0] ) )
		) {
			$wpdb->delete( $old_table_name, array( 'fw_term_id' => $term_id ), array( '%d' ) );

			update_term_meta( $term_id, 'fw_options', $value );

			return true;
		} else {
			return false;
		}
	}

	protected function _after_set($item_id, $option_id, $sub_keys, $old_value, array $extra_data = array()) {
		/**
		 * @since 2.6.0
		 */
		do_action('fw_term_options_update', array(
			'term_id' => $item_id,
			'taxonomy' => $extra_data['taxonomy'],
			/**
			 * Option id
			 * First level multi-key
			 * For e.g. if $option_id is 'hello/world/7' this will be 'hello'
			 */
			'option_id' => $option_id,
			/**
			 * The remaining sub-keys
			 * For e.g.
			 * if $option_id is 'hello/world/7' this will be array('world', '7')
			 * if $option_id is 'hello' this will be array()
			 */
			'sub_keys' => explode('/', $sub_keys),
			/**
			 * Old option(s) value
			 */
			'old_value' => $old_value
		));
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
		function fw_set_db_term_option( $term_id, $taxonomy, $option_id = null, $value = '' ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				return null;
			}

			FW_Db_Options_Model::_get_instance('term')->set(intval($term_id), $option_id, $value, array(
				'taxonomy' => $taxonomy
			));
		}

		add_action( 'switch_blog', array( __CLASS__, '_action_switch_blog' ) );
		add_action( 'delete_term', array( __CLASS__, '_action_fw_delete_term' ) );
	}
}
new FW_Db_Options_Model_Term();

// Extensions Settings Options
class FW_Db_Options_Model_Extension extends FW_Db_Options_Model {
	protected function get_id() {
		return 'extension';
	}

	protected function get_values($item_id, array $extra_data = array()) {
		return FW_WP_Option::get( 'fw_ext_settings_options:' . $item_id, null, array() );
	}

	protected function set_values($item_id, $values, array $extra_data = array()) {
		FW_WP_Option::set( 'fw_ext_settings_options:' . $item_id, null, $values );
	}

	protected function get_options($item_id, array $extra_data = array()) {
		return fw_ext($item_id)->get_settings_options();
	}

	protected function get_fw_storage_params($item_id, array $extra_data = array()) {
		return array(
			'extension' => $item_id,
		);
	}

	protected function _init() {
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
			if ( ! ( $extension = fw_ext( $extension_name ) ) ) {
				trigger_error( 'Invalid extension: ' . $extension_name, E_USER_WARNING );
				return null;
			}

			return FW_Db_Options_Model::_get_instance('extension')->get($extension_name, $option_id, $default_value);
		}

		/**
		 * Set extension's setting option value in database
		 *
		 * @param string $extension_name
		 * @param string|null $option_id
		 * @param mixed $value
		 */
		function fw_set_db_ext_settings_option( $extension_name, $option_id = null, $value = '' ) {
			if ( ! fw_ext( $extension_name ) ) {
				trigger_error( 'Invalid extension: ' . $extension_name, E_USER_WARNING );

				return;
			}

			FW_Db_Options_Model::_get_instance('extension')->set($extension_name, $option_id, $value);
		}
	}
}
new FW_Db_Options_Model_Extension();

// Customizer Options
class FW_Db_Options_Model_Customizer extends FW_Db_Options_Model {
	protected function get_id() {
		return 'customizer';
	}

	protected function get_values($item_id, array $extra_data = array()) {
		return get_theme_mod('fw_options', array());
	}

	protected function set_values($item_id, $values, array $extra_data = array()) {
		set_theme_mod('fw_options', $values);
	}

	protected function get_options($item_id, array $extra_data = array()) {
		return fw()->theme->get_customizer_options();
	}

	protected function get_fw_storage_params($item_id, array $extra_data = array()) {
		return array(
			'customizer' => true
		);
	}

	protected function _after_set($item_id, $option_id, $sub_keys, $old_value, array $extra_data = array()) {
		/**
		 * @since 2.6.0
		 */
		do_action('fw_customizer_options_update', array(
			/**
			 * Option id
			 * First level multi-key
			 * For e.g. if $option_id is 'hello/world/7' this will be 'hello'
			 */
			'option_id' => $option_id,
			/**
			 * The remaining sub-keys
			 * For e.g.
			 * if $option_id is 'hello/world/7' this will be array('world', '7')
			 * if $option_id is 'hello' this will be array()
			 */
			'sub_keys' => explode('/', $sub_keys),
			/**
			 * Old option(s) value
			 */
			'old_value' => $old_value
		));
	}

	/**
	 * @internal
	 */
	public function _reset_cache() {
		FW_Cache::del($this->get_main_cache_key());
	}

	protected function _init() {
		/**
		 * Get a customizer framework option value from the database
		 *
		 * @param string|null $option_id Specific option id (accepts multikey). null - all options
		 * @param null|mixed $default_value If no option found in the database, this value will be returned
		 *
		 * @return mixed|null
		 */
		function fw_get_db_customizer_option( $option_id = null, $default_value = null ) {
			return FW_Db_Options_Model::_get_instance('customizer')->get(null, $option_id, $default_value);
		}

		/**
		 * Set a theme customizer option value in database
		 *
		 * @param null $option_id Specific option id (accepts multikey). null - all options
		 * @param mixed $value
		 */
		function fw_set_db_customizer_option( $option_id = null, $value = '' ) {
			FW_Db_Options_Model::_get_instance('customizer')->set(null, $option_id, $value);
		}

		// Fixes https://github.com/ThemeFuse/Unyson/issues/2053
		add_action('customize_preview_init', array($this, '_reset_cache'),
			1 // Fixes https://github.com/ThemeFuse/Unyson/issues/2104
		);
	}
}
new FW_Db_Options_Model_Customizer();

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

		$data = get_user_meta( $user_id, 'fw_data', true );

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
	function fw_set_db_extension_data( $extension_name, $multi_key = null, $value = '' ) {
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
