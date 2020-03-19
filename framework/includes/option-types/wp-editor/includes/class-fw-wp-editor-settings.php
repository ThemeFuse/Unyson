<?php

/**
 * The code is crazy. You better leave.
 */

if (! defined('FW')) { die('Forbidden'); }

class FW_WP_Editor_Manager {
	private $id = null;
	private $option = null;
	private $data = null;

	private $qt_settings = null;
	private $mce_settings = null;

	private $priority = 999999999999999;

	public function __construct($id, $option, $data) {
		$this->id = $id;
		$this->option = $option;
		$this->data = $data;

		/**
		 * Generate random editor_id that will be used.
		 * Using a hash of combination of $option and $id is not enough. It
		 * usually repeats itself on the page pretty often.
		 */
		$this->editor_id = fw()->backend->option_type('wp-editor')->get_id_prefix() . fw_rand_md5();
	}


	public function get_html() {
		ob_start();

		/**
		 * This call will write something in _WP_Editors::$qt_settings and
		 * _WP_Editors::$mce_settings. Let's keep them there, we'll rewrite any
		 * data we need on the client side anyway later.
		 */
		wp_editor(
			$this->get_value_for_render(),
			$this->editor_id,
			$this->get_settings()
		);

		$editor_html = ob_get_contents();

		ob_end_clean();

		$option = $this->option;

		{
			unset( $option['attr']['name'], $option['attr']['value'] );

			$preinit_data = $this->get_preinit_data_for_editor();

			$option['attr']['data-fw-editor-id'] = $this->editor_id;
			$option['attr']['data-fw-mce-settings'] = json_encode($preinit_data['mce_settings']);
			$option['attr']['data-fw-qt-settings'] = json_encode($preinit_data['qt_settings']);

			if ($option['shortcodes']) {
				$option['attr']['data-fw-shortcodes-list'] = json_encode(
					$option['shortcodes']
				);
			}

			$option['attr']['data-size'] = $option['size'];
			$option['attr']['data-mode'] = in_array($option['editor_type'], array('html', 'tinymce'))
				? $option['editor_type'] : false;
		}

		return fw_html_tag(
			'div',
			$option['attr'],
			$editor_html
		);
	}

	public function get_value_for_render() {
		return str_replace(
			chr( 194 ) . chr( 160 ),
			'&nbsp;',
			(string) $this->data['value']
		);
	}

	public function get_settings() {
		$settings = array();

		foreach ( // https://github.com/WordPress/WordPress/blob/4.4.2/wp-includes/class-wp-editor.php#L80-L94
			array(
				'wpautop',
				'media_buttons',
				'default_editor',
				'drag_drop_upload',
				'textarea_name',
				'textarea_rows',
				'tabindex',
				'tabfocus_elements',
				'editor_css',
				'editor_class',
				'teeny',
				'dfw',
				'_content_editor_dfw',
				'tinymce',
				'quicktags',
			) as $key
		) {
			if (isset($this->option[$key])) {
				$settings[$key] = $this->option[$key];
			}
		}

		if (isset($settings['teeny'])) {
			unset($settings['teeny']);
		}

		$settings['editor_height'] = (int) $this->option['editor_height'];
		$settings['textarea_name'] = $this->option['attr']['name'];

		return $settings;
	}

	public function get_set() {
		$set = _WP_Editors::parse_settings(
			$this->editor_id,
			$this->get_settings()
		);

		if ( ! current_user_can( 'upload_files' ) ) {
			$set['media_buttons'] = false;
		}

		return $set;
	}

	/**
	 * We need to get
	 * _WP_Editors::$qt_settings and _WP_Editors::$mce_settings, after calling
	 * it's editor_settings method. And it needs to be done without any mutation
	 * to the class state itself. I've tryied HARD to not leave any
	 * fingerprints there.
	 */
	public function get_preinit_data_for_editor() {
		$this->attach_filters();

		/**
		 * Set:
		 * _WP_Editors::$qt_settings: https://github.com/WordPress/WordPress/blob/master/wp-includes/class-wp-editor.php#L345
		 *
		 * _WP_Editors::$mce_settings: https://github.com/WordPress/WordPress/blob/master/wp-includes/class-wp-editor.php#L740
		 *
		 * Then get them back using reflection class
		 */
		_WP_Editors::editor_settings(
			$this->editor_id,
			$this->get_set()
		);

		$mce_settings = $this->mce_settings;
		$qt_settings = $this->qt_settings;

		if ( isset($mce_settings['formats']) ) {
			/**
			 * https://github.com/WordPress/WordPress/blob/master/wp-includes/class-wp-editor.php#L522
			 *
			 * _WP_Editors outputs JavaScript notation object, we want a valid JSON.
			 *
			 * Replace this:
			 * {a: 1}
			 * to
			 * {"a": 1}
			 */
			$mce_settings['formats'] = preg_replace(
				"/(\w+)\:/",
				'"$1":',
				$mce_settings['formats']
			);
		}

		/**
		 * Loop thought all settings and decode json values
		 */
		if ($mce_settings) {
			foreach ($mce_settings as &$setting) {
				if (
					is_string($setting)
					&&
					!empty($setting)
					&&
					in_array($setting[0], array('[', '{'), true) // [0] fixes https://github.com/ThemeFuse/Unyson/issues/3915
					&&
					! is_null($decoded = json_decode($setting))
				) {
					$setting = $decoded;
				}
			}
		}

		$preinit_data = array(
			'mce_settings' => $mce_settings,
			'qt_settings' => $qt_settings
		);

		$this->dettach_filters();

		return $preinit_data;
	}

	public function attach_filters() {
		add_filter(
			'tiny_mce_before_init',
			array($this, 'mce_settings_callback'),
			$this->priority
		);

		add_filter(
			'quicktags_settings',
			array($this, 'quicktags_settings_callback'),
			$this->priority
		);

		add_filter(
			'teeny_mce_before_init',
			array($this, 'mce_settings_callback'),
			$this->priority
		);
	}

	public function dettach_filters() {
		remove_filter(
			'quicktags_settings',
			array($this, 'quicktags_settings_callback'),
			$this->priority
		);

		remove_filter(
			'tiny_mce_before_init',
			array($this, 'mce_settings_callback'),
			$this->priority
		);

		remove_filter(
			'teeny_mce_before_init',
			array($this, 'mce_settings_callback'),
			$this->priority
		);
	}

	public function quicktags_settings_callback($qt_settings) {
		$this->qt_settings = $qt_settings;
		return $qt_settings;
	}

	public function mce_settings_callback($mce_settings) {
		$this->mce_settings = $mce_settings;
		return $mce_settings;
	}
}

