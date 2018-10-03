<?php if (!defined('FW')) die('Forbidden');

/**
 * Used in fw()->backend
 * @internal
 */
class FW_Settings_Form_Theme extends FW_Settings_Form {
	protected function _init() {
		$this
			->set_is_ajax_submit( fw()->theme->get_config('settings_form_ajax_submit') )
			->set_is_side_tabs( fw()->theme->get_config('settings_form_side_tabs') )
			->set_string( 'title', __('Theme Settings', 'fw') );

		{
			add_action('admin_init', array($this, '_action_get_title_from_menu'));
			add_action('admin_menu', array($this, '_action_admin_menu'));
			add_action('admin_enqueue_scripts', array($this, '_action_admin_enqueue_scripts'),
				/**
				 * In case some custom defined option types are using script/styles registered
				 * in actions with default priority 10 (make sure the enqueue is executed after register)
				 * @see _FW_Component_Backend::add_actions()
				 */
				11
			);
		}
	}

	public function get_options() {
		return fw()->theme->get_settings_options();
	}

	public function set_values($values) {
		fw_set_db_settings_option(null, $values);

		return $this;
	}

	public function get_values() {
		return fw_get_db_settings_option();
	}

	/**
	 * User can overwrite Theme Settings menu, move it and change its title
	 * extract that title from WP menu
	 * @internal
	 */
	public function _action_get_title_from_menu() {
		if ($this->get_is_side_tabs()) {
			$title = fw()->theme->manifest->get_name();

			if (fw()->theme->manifest->get('author')) {
				if (fw()->theme->manifest->get('author_uri')) {
					$title .= ' '. fw_html_tag('a', array(
							'href' => fw()->theme->manifest->get('author_uri'),
							'target' => '_blank'
						), '<small>' . __('by', 'fw') . ' ' . fw()->theme->manifest->get('author') . '</small>');
				} else {
					$title .= ' <small>' . fw()->theme->manifest->get('author') . '</small>';
				}
			}

			$this->set_string('title', $title);
		} else {
			// Extract page title from menu title
			do {
				global $menu, $submenu;

				if (is_array($menu)) {
					foreach ($menu as $_menu) {
						if ($_menu[2] === fw()->backend->_get_settings_page_slug()) {
							$title = $_menu[0];
							break 2;
						}
					}
				}

				if (is_array($submenu)) {
					foreach ($submenu as $_menu) {
						foreach ($_menu as $_submenu) {
							if ($_submenu[2] === fw()->backend->_get_settings_page_slug()) {
								$title = $_submenu[0];
								break 3;
							}
						}
					}
				}
			} while(false);

			if (isset($title)) {
				$this->set_string('title', $title);
			}
		}
	}

	/**
	 * @internal
	 */
	public function _action_admin_menu() {
		$data = array(
			'capability'       => 'manage_options',
			'slug'             => fw()->backend->_get_settings_page_slug(),
			'content_callback' => array( $this, 'render' ),
		);

		if ( ! current_user_can( $data['capability'] ) ) {
			return;
		}

		if (fw()->theme->get_config('disable_theme_settings_page', false)) {
			return;
		}

		if ( ! fw()->theme->locate_path('/options/settings.php') ) {
			return;
		}

		/**
		 * Collect $hookname that contains $data['slug'] before the action
		 * and skip them in verification after action
		 */
		{
			global $_registered_pages;

			$found_hooknames = array();

			if ( ! empty( $_registered_pages ) ) {
				foreach ( $_registered_pages as $hookname => $b ) {
					if ( strpos( $hookname, $data['slug'] ) !== false ) {
						$found_hooknames[ $hookname ] = true;
					}
				}
			}
		}

		/**
		 * Use this action if you what to add the settings page in a custom place in menu
		 * Usage example http://pastebin.com/gvAjGRm1
		 */
		do_action( 'fw_backend_add_custom_settings_menu', $data );

		/**
		 * Check if settings menu was added in the action above
		 */
		{
			$menu_exists = false;

			if ( ! empty( $_registered_pages ) ) {
				foreach ( $_registered_pages as $hookname => $b ) {
					if ( isset( $found_hooknames[ $hookname ] ) ) {
						continue;
					}

					if ( strpos( $hookname, $data['slug'] ) !== false ) {
						$menu_exists = true;
						break;
					}
				}
			}
		}

		if ( $menu_exists ) {
			return;
		}

		add_theme_page(
			__( 'Theme Settings', 'fw' ),
			__( 'Theme Settings', 'fw' ),
			$data['capability'],
			$data['slug'],
			$data['content_callback']
		);

		add_action( 'admin_menu', array( $this, '_action_admin_change_theme_settings_order' ), 9999 );
	}

	/**
	 * @internal
	 */
	public function _action_admin_change_theme_settings_order() {
		global $submenu;

		if ( ! isset( $submenu['themes.php'] ) ) {
			// probably current user doesn't have this item in menu
			return;
		}

		$id    = fw()->backend->_get_settings_page_slug();
		$index = null;

		foreach ( $submenu['themes.php'] as $key => $sm ) {
			if ( $sm[2] == $id ) {
				$index = $key;
				break;
			}
		}

		if ( ! empty( $index ) ) {
			$item = $submenu['themes.php'][ $index ];
			unset( $submenu['themes.php'][ $index ] );
			array_unshift( $submenu['themes.php'], $item );
		}
	}

	/**
	 * @internal
	 */
	public function _action_admin_enqueue_scripts()
	{
		global $plugin_page;

		/**
		 * Enqueue settings options static in <head>
		 */
		{
			if (fw()->backend->_get_settings_page_slug() === $plugin_page) {
				$this->enqueue_static();

				do_action('fw_admin_enqueue_scripts:settings');
			}
		}
	}
}
