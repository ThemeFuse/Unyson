<?php

/**
 * The code is crazy. You better leave.
 */

if (! defined('FW')) { die('Forbidden'); }

class FW_WP_Editor_Manager {
	private $id = null;
	private $option = null;
	private $data = null;

	/**
	 * https://github.com/WordPress/WordPress/blob/master/wp-includes/class-wp-editor.php#L305
	 * An _WP_Editors::editor_settings call mutates those fields I list below.
	 *
	 * I need them to be mocked and after the manipulations are done to be
	 * safely restored. I don't to cause troubles to WordPress execution engine.

	 * 1. self::$first_init set to array() in order to skip add_action(), then restore
	 * 2. preserve self::$qt_settings value
	 * 3. preserve self::$qt_buttons
	 * 4. preserve self::$baseurl
	 * 5. preserve self::$mce_locale
	 * 6. preserve self::$first_init
	 * 7. $set['teeny'] should be not set
	 * 8. preserve self::$mce_settings
	 */
	private $fields_to_mock_in_wp_editors = array(
		'first_init', 'qt_settings', 'qt_buttons', 'baseurl',
		'mce_locale', 'first_init', 'mce_settings'
	);
	private $mock_data = null;
	private $reflection_class = null;

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
		$this->mock_wp_editors_class();

		/**
		 * We are safe to do any modifications to the _WP_Editors here.
		 * Any mocked data will be restored after our manipulations.
		 */

		/**
		 * Skip add_action()
		 * https://github.com/WordPress/WordPress/blob/master/wp-includes/class-wp-editor.php#L308
		 */
		$this->set_static_field(
			'first_init',
			array()
		);

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

		$mce_settings = $this->get_static_field('mce_settings');
		$qt_settings = $this->get_static_field('qt_settings');

		$mce_settings = fw_akg(
			$this->editor_id,
			$mce_settings,
			array()
		);

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

		$mce_settings['formats'] = json_decode($mce_settings['formats'], true);
		$mce_settings['external_plugins'] = json_decode($mce_settings['external_plugins'], true);

		$qt_settings = fw_akg(
			$this->editor_id,
			$qt_settings,
			array()
		);

		$preinit_data = array(
			'mce_settings' => $mce_settings,
			'qt_settings' => $qt_settings
		);

		$this->restore_wp_editors_class();

		return $preinit_data;
	}

	public function mock_wp_editors_class() {
		$this->reflection_class = new ReflectionClass('_WP_Editors');
		$this->mock_data = array();

		array_map(
			array($this, 'backup_static_field'),
			$this->fields_to_mock_in_wp_editors
		);
	}

	public function restore_wp_editors_class() {
		array_map(
			array($this, 'restore_static_field'),
			$this->fields_to_mock_in_wp_editors
		);

		$this->reflection_class = null;
		$this->mock_data = null;
	}

	public function backup_static_field($field) {
		$this->mock_data[$field] = $this->get_static_field($field);
	}

	public function restore_static_field($field) {
		$this->set_static_field($field, $this->mock_data[$field]);
	}

	public function set_static_field($field, $value) {
		$property = $this->reflection_class->getProperty($field);
		$property->setAccessible(true);
		$property->setValue($value);
	}

	public function get_static_field($field) {
		$property = $this->reflection_class->getProperty($field);
		$property->setAccessible(true);
		return $property->getValue();
	}
}

