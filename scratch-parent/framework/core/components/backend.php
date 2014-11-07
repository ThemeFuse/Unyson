<?php if (!defined('FW')) die('Forbidden');

/**
 * Backend functionality
 */
final class _FW_Component_Backend
{
	/** @var callable */
	private $print_meta_box_content_callback;

	/** @var FW_Form */
	private $settings_form;

	private $available_render_designs = array('default', 'taxonomy');

	/**
	 * Store option types for registration, until they will be required
	 * @var array|false
	 *      array Can have some pending option types in it
	 *      false Option types already requested and was registered, so do not use pending anymore
	 */
	private $option_types_pending_registration = array();

	/**
	 * Contains all option types
	 * @var FW_Option_Type[]
	 */
	private $option_types = array();

	/**
	 * @var FW_Option_Type_Undefined
	 */
	private $undefined_option_type;

	private $static_registered = false;

	/**
	 * @internal
	 */
	public function _get_settings_page_slug()
	{
		return 'fw-settings';
	}

	private function get_current_edit_taxonomy()
	{
		static $cache_current_taxonomy_data = null;

		if ($cache_current_taxonomy_data !== null)
			return $cache_current_taxonomy_data;

		$result = array(
			'taxonomy' => null,
			'term_id'  => 0,
		);

		do {
			if (!is_admin()) {
				break;
			}

			// code from /wp-admin/admin.php line 110
			{
				if ( isset( $_REQUEST['taxonomy'] ) && taxonomy_exists( $_REQUEST['taxonomy'] ) )
					$taxnow = $_REQUEST['taxonomy'];
				else
					$taxnow = '';
			}

			if (empty($taxnow))
				break;

			$result['taxonomy'] = $taxnow;

			if (empty($_REQUEST['tag_ID']))
				return $result;

			// code from /wp-admin/edit-tags.php
			{
				$tag_ID = (int) $_REQUEST['tag_ID'];
			}

			$result['term_id'] = $tag_ID;
		} while(false);

		$cache_current_taxonomy_data = $result;

		return $cache_current_taxonomy_data;
	}

	public function __construct()
	{
		$this->print_meta_box_content_callback = create_function('$post,$args', 'echo $args["args"];');

		{
			$this->undefined_option_type = new FW_Option_Type_Undefined();

			$this->option_types[$this->undefined_option_type->get_type()] = $this->undefined_option_type;
		}
	}

	/**
	 * @internal
	 */
	public function _init()
	{
		if (!is_admin()) {
			return;
		}

		$this->settings_form = new FW_Form('fw_settings', array(
			'render'   => array($this, '_settings_form_render'),
			'validate' => array($this, '_settings_form_validate'),
			'save'     => array($this, '_settings_form_save'),
		));

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * @internal
	 */
	public function _after_components_init()
	{
	}

	private function add_actions()
	{
		add_action('admin_menu',            array($this, '_action_admin_menu'));
		add_action('admin_menu',            array($this, '_action_admin_change_theme_settings_order'), 9999);
		add_action('add_meta_boxes',        array($this, '_action_create_post_meta_boxes'), 10, 2);
		add_action('save_post',             array($this, '_action_save_post'), 10, 2);
		add_action('init',                  array($this, '_action_init'), 20);
		add_action('admin_enqueue_scripts', array($this, '_action_admin_enqueue_scripts'), 8);

		// render and submit options from javascript
		{
			add_action('wp_ajax_fw_backend_options_render',     array($this, '_action_ajax_options_render'));
			add_action('wp_ajax_fw_backend_options_get_values', array($this, '_action_ajax_options_get_values'));
		}
	}

	private function add_filters()
	{
		add_filter('update_footer', array($this, '_filter_footer_version'), 11);
	}

	/**
	 * @param string|FW_Option_Type $option_type_class
	 * @internal
	 */
	private function register_option_type($option_type_class)
	{
		if (is_array($this->option_types_pending_registration)) {
			// Option types never requested. Continue adding to pending
			$this->option_types_pending_registration[] = $option_type_class;
		} else {
			if (is_string($option_type_class)) {
				$option_type_class = new $option_type_class;
			}

			if (!is_subclass_of($option_type_class, 'FW_Option_Type')) {
				trigger_error('Invalid option type class '. get_class($option_type_class), E_USER_WARNING);
				return;
			}

			/**
			 * @var FW_Option_Type $option_type_class
			 */

			$type = $option_type_class->get_type();

			if (isset($this->option_types[$type])) {
				trigger_error('Option type "'. $type .'" already registered', E_USER_WARNING);
				return;
			}

			$this->option_types[$type] = $option_type_class;
		}
	}

	private function register_static()
	{
		if ($this->static_registered) {
			return;
		}

		/**
		 * Register styles/scripts only in admin area, on frontend it's not allowed to use styles/scripts from framework backend core
		 * because they are meant to be used only in backend and can be changed in the future.
		 * If you want to use a style/script from framework backend core, copy it to your theme and enqueue as a theme style/script.
		 */
		if (!is_admin()) {
			$this->static_registered = true;
			return;
		}

		wp_register_script(
			'fw-events',
			fw_get_framework_directory_uri('/static/js/fw-events.js'),
			array('backbone'),
			fw()->manifest->get_version(),
			true
		);

		wp_register_script(
			'fw-ie-fixes',
			fw_get_framework_directory_uri('/static/js/ie-fixes.js'),
			array(),
			fw()->manifest->get_version(),
			true
		);

		{
			wp_register_style(
				'qtip',
				fw_get_framework_directory_uri('/static/libs/qtip/css/jquery.qtip.min.css'),
				array(),
				fw()->manifest->get_version()
			);
			wp_register_script(
				'qtip',
				fw_get_framework_directory_uri('/static/libs/qtip/jquery.qtip.min.js'),
				array('jquery'),
				fw()->manifest->get_version()
			);
		}

		{
			wp_register_style(
				'fw',
				fw_get_framework_directory_uri('/static/css/fw.css'),
				array('qtip'),
				fw()->manifest->get_version()
			);

			wp_register_script(
				'fw',
				fw_get_framework_directory_uri('/static/js/fw.js'),
				array('jquery', 'fw-events', 'backbone', 'qtip'),
				fw()->manifest->get_version(),
				true
			);

			wp_localize_script('fw', '_fw_localized', array(
				'FW_URI'   => fw_get_framework_directory_uri(),
				'SITE_URI' => site_url(),
			));
		}

		{
			wp_register_style(
				'fw-backend-options',
				fw_get_framework_directory_uri('/static/css/backend-options.css'),
				array('fw'),
				fw()->manifest->get_version()
			);

			wp_register_script(
				'fw-backend-options',
				fw_get_framework_directory_uri('/static/js/backend-options.js'),
				array('fw-events', 'postbox', 'jquery-ui-tabs', 'fw'),
				fw()->manifest->get_version(),
				true
			);
		}

		{
			wp_register_style(
				'fw-selectize',
				fw_get_framework_directory_uri('/static/libs/selectize/selectize.css'),
				array(),
				fw()->manifest->get_version()
			);
			wp_register_script(
				'fw-selectize',
				fw_get_framework_directory_uri('/static/libs/selectize/selectize.min.js'),
				array('jquery', 'fw-ie-fixes'),
				fw()->manifest->get_version(),
				true
			);
		}

		{
			wp_register_script(
				'fw-mousewheel',
				fw_get_framework_directory_uri('/static/libs/mousewheel/jquery.mousewheel.min.js'),
				array('jquery'),
				fw()->manifest->get_version(),
				true
			);
		}

		{
			wp_register_style(
				'fw-jscrollpane',
				fw_get_framework_directory_uri('/static/libs/jscrollpane/jquery.jscrollpane.css'),
				array(),
				fw()->manifest->get_version()
			);
			wp_register_script( 'fw-jscrollpane',
				fw_get_framework_directory_uri('/static/libs/jscrollpane/jquery.jscrollpane.min.js'),
				array( 'jquery', 'fw-mousewheel' ),
				fw()->manifest->get_version(),
				true
			);
		}

		{
			wp_register_style(
				'fw-font-awesome',
				fw_get_framework_directory_uri('/static/libs/font-awesome/css/font-awesome.min.css'),
				array(),
				fw()->manifest->get_version()
			);
		}

		{
			wp_register_script(
				'backbone-relational',
				fw_get_framework_directory_uri('/static/libs/backbone-relational/backbone-relational.js'),
				array('backbone'),
				fw()->manifest->get_version(),
				true
			);
		}

		{
			wp_register_script(
				'fw-uri',
				fw_get_framework_directory_uri('/static/libs/uri/URI.js'),
				fw()->manifest->get_version(),
				true
			);
		}

		$this->static_registered = true;
	}

	/**
	 * @internal
	 */
	public function _action_admin_menu()
	{
		$data = array(
			'capability'       => 'manage_options',
			'slug'             => $this->_get_settings_page_slug(),
			'content_callback' => array($this, '_print_settings_page'),
		);

		/**
		 * Use this action if you what to add the settings page in a custom place in menu
		 * Usage example http://pastebin.com/0KQXLPZj
		 */
		do_action('fw_backend_add_custom_settings_menu', $data);

		/**
		 * check if settings menu was added in the action above
		 */
		{
			global $_registered_pages;

			$menu_exists = false;

			if (!empty($_registered_pages)) {
				foreach ( $_registered_pages as $hookname => $b ) {
					if ( strpos( $hookname, $data['slug'] ) !== false ) {
						$menu_exists = true;
						break;
					}
				}
			}
		}

		if ($menu_exists) {
			remove_action('admin_menu', array($this, '_action_admin_change_theme_settings_order'), 9999);
		} else {
			add_theme_page(
				__( 'Theme Settings', 'fw' ),
				__( 'Theme Settings', 'fw' ),
				$data['capability'],
				$data['slug'],
				$data['content_callback']
			);
		}
	}

	/**
	 * Print framework version in the admin footer
	 * @param string $value
	 * @return string
	 * @internal
	 */
	public function _filter_footer_version($value)
	{
		if (current_user_can('update_themes') || current_user_can('update_plugins')) {
			return (empty($value) ? '' : $value .' | ') . fw()->manifest->get_name() .' '. fw()->manifest->get_version();
		} else {
			return $value;
		}
	}

	public function _action_admin_change_theme_settings_order() {
		global $submenu;

		if (!isset($submenu['themes.php'])) {
			// probably current user doesn't have this item in menu
			return;
		}

		$id    = $this->_get_settings_page_slug();
		$index = null;

		foreach ( $submenu['themes.php'] as $key => $sm ) {
			if ( $sm[2] == $id ) {
				$index = $key;
				break;
			}
		}

		if ( ! empty( $index ) ) {
			$item = $submenu['themes.php'][ $index ];
			unset($submenu['themes.php'][$index]);
			array_unshift( $submenu['themes.php'], $item );
		}
	}

	public function _print_settings_page()
	{
		echo '<h2>'. __('Theme Settings', 'fw') .'</h2><p></p>';

		echo '<div class="wrap">';

		$this->settings_form->render();

		echo '</div>';
	}

	/**
	 * @param string $post_type
	 * @param WP_Post $post
	 */
	public function _action_create_post_meta_boxes($post_type, $post)
	{
		$options = fw()->theme->get_post_options($post_type);

		if (empty($options)) {
			return;
		}

		$collected = array();

		fw_collect_first_level_options($collected, $options);

		unset($options); // free memory

		if (empty($collected['boxes'])) {
			return; // only boxes are allowed on edit post page
		}

		$boxes =& $collected['boxes'];

		unset($collected); // free memory

		$values = fw_get_db_post_option($post->ID);

		foreach ($boxes as $id => &$box) {
			$context  = isset($box['context'])  ? $box['context'] : 'normal';
			$priority = isset($box['priority']) ? $box['priority'] : 'default';

			add_meta_box(
				'fw-options-box-'. $id,
				empty($box['title']) ? ' ' : $box['title'],
				$this->print_meta_box_content_callback,
				$post_type,
				$context,
				$priority,
				$this->render_options($box['options'], $values)
			);

			unset($box[$id]); // free memory
		}
	}

	/**
	 * @param object $term
	 */
	public function _action_create_taxonomy_options($term)
	{
		$options = fw()->theme->get_taxonomy_options($term->taxonomy);

		if (empty($options))
			return;

		$collected = array();

		fw_collect_first_level_options($collected, $options);

		unset($options);

		if (empty($collected['options']))
			return; // only simple options are allowed on taxonomy edit page

		$values = fw_get_db_term_option($term->term_id, $term->taxonomy);

		// fixes word_press style: .form-field input { width: 95% }
		echo '<style type="text/css">.fw-option-type-radio input, .fw-option-type-checkbox input { width: auto; }</style>';

		echo $this->render_options($collected['options'], $values, array(), 'taxonomy');

		unset($options);
	}

	public function _action_init()
	{
		$current_edit_taxonomy = $this->get_current_edit_taxonomy();

		if ($current_edit_taxonomy['taxonomy']) {
			add_action($current_edit_taxonomy['taxonomy'] .'_edit_form_fields', array($this, '_action_create_taxonomy_options'), 10);
		}

		if (!empty($_POST)) {
			// is form submit
			add_action('edited_term', array($this, '_action_term_edit'), 10, 3); // fixme: remove options on term delete
		}
	}

	/**
	 * @param int  $post_id
	 * @param WP_Post $post
	 */
	public function _action_save_post($post_id, $post)
	{
		if (!fw_is_real_post_save($post_id)) {
			return;
		}

		$options_values = array_merge(
			(array)fw_get_db_post_option($post_id),
			fw_get_options_values_from_input(
				fw()->theme->get_post_options($post->post_type)
			)
		);

		fw_set_db_post_option($post_id, null, $options_values);

		/**
		 * find options that requested to be saved in separate meta
		 */
		foreach (fw_extract_only_options(fw()->theme->get_post_options($post->post_type)) as $option_id => $option) {
			if (isset($option['save-in-separate-meta']) && $option['save-in-separate-meta']) {
				fw_update_post_meta($post_id, 'fw_option:'. $option_id, $options_values[$option_id]);
			}
		}

		do_action('fw_save_post_options', $post_id, $post, $options_values);
	}

	public function _action_term_edit($term_id, $tt_id, $taxonomy)
	{
		if (!isset($_POST['action']) || !isset($_POST['taxonomy'])) {
			return; // this is not real term form submit, abort save
		}

		fw_set_db_term_option($term_id, $taxonomy, null,
			fw_get_options_values_from_input(
				fw()->theme->get_taxonomy_options($taxonomy)
			)
		);
	}

	public function _action_admin_enqueue_scripts()
	{
		$this->register_static();
	}

	/**
	 * Render options html from input json
	 *
	 * POST vars:
	 * - options: '[{option_id: {...}}, {option_id: {...}}, ...]'                  // Required // String JSON
	 * - values:  {option_id: value, option_id: {...}, ...}                        // Optional // Object
	 * - data:    {id_prefix: 'fw_options-a-b-', name_prefix: 'fw_options[a][b]'}  // Optional // Object
	 */
	public function _action_ajax_options_render()
	{
		// options
		{
			if (!isset($_POST['options'])) {
				wp_send_json_error(array(
					'message' => 'No options'
				));
			}

			$options = json_decode(FW_Request::POST('options'), true);

			if (!$options) {
				wp_send_json_error(array(
					'message' => 'Wrong options'
				));
			}
		}

		// values
		{
			if (isset($_POST['values'])) {
				$values = FW_Request::POST('values');
			} else {
				$values = array();
			}
		}

		// data
		{
			if (isset($_POST['data'])) {
				$data = FW_Request::POST('data');
			} else {
				$data = array();
			}
		}

		/**
		 * Fix booleans
		 *
		 * In POST, booleans are transformed to strings: 'true' and 'false'
		 * Transform them back to booleans
		 */
		{
			foreach (fw_extract_only_options($options) as $option_id => $option) {
				if (!isset($values[$option_id])) {
					continue;
				}

				/**
				 * We detect if option is using booleans by sending it a boolean input value
				 * If it returns a boolean, then it works with booleans
				 */
				if (!is_bool(
					fw()->backend->option_type($option['type'])->get_value_from_input($option, true)
				)) {
					continue;
				}

				if (is_bool($values[$option_id])) {
					// value is already boolean, does not need to fix
					continue;
				}

				$values[$option_id] = ($values[$option_id] === 'true');
			}
		}

		wp_send_json_success(array(
			'html' => fw()->backend->render_options($options, $values, $data)
		));
	}

	/**
	 * Get options values from html generated with 'fw_backend_options_render' ajax action
	 *
	 * POST vars:
	 * - options: '[{option_id: {...}}, {option_id: {...}}, ...]' // Required // String JSON
	 * - fw_options... // Use a jQuery "ajax form submit" to emulate real form submit
	 *
	 * Tip: Inside form html, add: <input type="hidden" name="options" value="[...json...]">
	 */
	public function _action_ajax_options_get_values()
	{
		// options
		{
			if (!isset($_POST['options'])) {
				wp_send_json_error(array(
					'message' => 'No options'
				));
			}

			$options = json_decode(FW_Request::POST('options'), true);

			if (!$options) {
				wp_send_json_error(array(
					'message' => 'Wrong options'
				));
			}
		}

		// name_prefix
		{
			if (isset($_POST['name_prefix'])) {
				$name_prefix = FW_Request::POST('name_prefix');
			} else {
				$name_prefix = FW_Option_Type::get_default_name_prefix();
			}
		}

		wp_send_json_success(array(
			'values' => fw_get_options_values_from_input(
				$options,
				FW_Request::POST($name_prefix, array())
			)
		));
	}

	public function _settings_form_render($data)
	{
		/**
		 * wp moves flash message error with js after first h2
		 * if there are no h2 on the page it shows them wrong
		 */
		echo '<h2 class="fw-hidden"></h2>';

		$options = fw()->theme->get_settings_options();

		if (empty($options)) {
			return $data;
		}

		$values = FW_Request::POST(FW_Option_Type::get_default_name_prefix(), fw_get_db_settings_option());

		echo fw()->backend->render_options($options, $values);

		$data['submit']['html'] = '<button class="button-primary button-large">'. __('Save', 'fw') .'</button>';

		return $data;
	}

	public function _settings_form_validate($errors)
	{
		if (!current_user_can('manage_options')) {
			$errors['_no_permission'] = __('You have no permissions to change settings options', 'fw');;
		}

		return $errors;
	}

	public function _settings_form_save($data)
	{
		fw_set_db_settings_option(
			null,
			array_merge(
				(array)fw_get_db_settings_option(),
				fw_get_options_values_from_input(
					fw()->theme->get_settings_options()
				)
			)
		);

		FW_Flash_Messages::add('fw_settings_form_saved', __('Options successfully saved', 'fw'), 'success');

		$data['redirect'] = fw_current_url();

		do_action('fw_settings_form_saved');

		return $data;
	}

	/**
	 * Render options array and return the generated HTML
	 *
	 * @param array $options
	 * @param array $values
	 * @param array $options_data
	 * @param string $design
	 *
	 * @return string HTML
	 */
	public function render_options($options, $values = array(), $options_data = array(), $design = 'default')
	{
		{
			/**
			 * register scripts and styles
			 * in case if this method is called before enqueue_scripts action
			 * and option types has some of these in their dependencies
			 */
			$this->register_static();

			wp_enqueue_style('fw-backend-options');
			wp_enqueue_script('fw-backend-options');
		}

		$collected = array();

		fw_collect_first_level_options($collected, $options);

		ob_start();

		if (!empty($collected['tabs'])) {
			echo fw_render_view(fw_get_framework_directory('/views/backend-tabs.php'), array(
				'tabs'   => &$collected['tabs'],
				'values' => &$values,
				'options_data' => $options_data,
			));
		}
		unset($collected['tabs']);

		if (!empty($collected['boxes'])) {
			echo '<div class="fw-postboxes metabox-holder">';

			foreach ($collected['boxes'] as $id => &$box) {
				// prepare attributes
				{
					$attr = isset($box['attr']) ? $box['attr'] : array();

					unset($attr['id']); // do not allow id overwrite, it is sent in first argument of render_box()
				}

				echo $this->render_box(
					'fw-options-box-'. $id,
					empty($box['title']) ? ' ' : $box['title'],
					$this->render_options($box['options'], $values, $options_data),
					array(
						'attr' => $attr
					)
				);
			}

			echo '</div>';
		}
		unset($collected['boxes']);

		if (!empty($collected['groups_and_options'])) {
			foreach ($collected['groups_and_options'] as $id => &$option) {
				if (isset($option['options'])) { // group
					// prepare attributes
					{
						$attr = isset($option['attr']) ? $option['attr'] : array();

						$attr['id'] = 'fw-backend-options-group-'. esc_attr($id);

						if (!isset($attr['class'])) {
							$attr['class'] = 'fw-backend-options-group';
						} else {
							$attr['class'] = 'fw-backend-options-group '. $attr['class'];
						}
					}

					echo '<div '. fw_attr_to_html($attr) .'>';
					echo $this->render_options($option['options'], $values, $options_data);
					echo '</div>';
				} else { // option
					$data = $options_data;

					$data['value'] = isset($values[$id]) ? $values[$id] : null;

					echo $this->render_option(
						$id,
						$option,
						$data,
						$design
					);
				}
			}
		}
		unset($collected['options']);

		return ob_get_clean();
	}

	/**
	 * Enqueue options static
	 *
	 * Useful when you have dynamic options html on the page (for e.g. options modal)
	 * and in order to initialize that html properly, the option types scripts styles must be enqueued on the page
	 *
	 * @param array $options
	 */
	public function enqueue_options_static($options)
	{
		{
			/**
			 * register scripts and styles
			 * in case if this method is called before enqueue_scripts action
			 * and option types has some of these in their dependencies
			 */
			$this->register_static();

			wp_enqueue_style('fw-backend-options');
			wp_enqueue_script('fw-backend-options');
		}

		foreach (fw_extract_only_options($options) as $option_id => $option) {
			fw()->backend->option_type($option['type'])->enqueue_static($option_id, $option);
		}
	}

	/**
	 * Render option enclosed in backend design
	 *
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 * @param string $design default or taxonomy
	 *
	 * @return string
	 */
	public function render_option($id, $option, $data = array(), $design = 'default')
	{
		/**
		 * register scripts and styles
		 * in case if this method is called before enqueue_scripts action
		 * and option types has some of these in their dependencies
		 */
		$this->register_static();

		if (!in_array($design, $this->available_render_designs)) {
			trigger_error('Invalid render design specified: '. $design, E_USER_WARNING);
			$design = 'post';
		}

		if (!isset($data['id_prefix'])) {
			$data['id_prefix'] = FW_Option_Type::get_default_id_prefix();
		}

		return fw_render_view(fw_get_framework_directory('/views/backend-option-design-'. $design .'.php'), array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data,
		));
	}

	/**
	 * Render a meta box
	 * @param string $id
	 * @param string $title
	 * @param string $content HTML
	 * @param array  $other Optional elements
	 * @return string Generated meta box html
	 */
	public function render_box($id, $title, $content, $other = array())
	{
		if (!function_exists('add_meta_box')) {
			trigger_error('Try call this method later (\'admin_init\' action), add_meta_box() function does not exists yet.', E_USER_WARNING);
			return '';
		}

		$other = array_merge(array(
			'html_before_title' => false,
			'html_after_title'  => false,
			'attr'              => array(),
		), $other);

		{
			$placeholders = array(
				'id'      => '{{meta_box_id}}',
				'title'   => '{{meta_box_title}}',
				'content' => '{{meta_box_content}}',
			);

			// other placeholders
			{
				$placeholders['html_before_title'] = '{{meta_box_html_before_title}}';
				$placeholders['html_after_title']  = '{{meta_box_html_after_title}}';
				$placeholders['attr']              = '{{meta_box_attr}}';
				$placeholders['attr_class']        = '{{meta_box_attr_class}}';
			}
		}

		$cache_key = 'fw_meta_box_template';

		try {
			$meta_box_template = FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$temp_screen_id = 'fw-temp-meta-box-screen-id-'. fw_unique_increment();
			$context = 'normal';

			add_meta_box(
				$placeholders['id'],
				$placeholders['title'],
				$this->print_meta_box_content_callback,
				$temp_screen_id,
				$context,
				'default',
				$placeholders['content']
			);

			ob_start();

			do_meta_boxes($temp_screen_id, $context, null);

			$meta_box_template = ob_get_clean();

			remove_meta_box($id, $temp_screen_id, $context);

			// remove wrapper div, leave only meta box div
			{
				// <div ...>
				{
					$meta_box_template = str_replace(
						'<div id="'. $context .'-sortables" class="meta-box-sortables">',
						'',
						$meta_box_template
					);
				}

				// </div>
				{
					$meta_box_template = explode('</div>', $meta_box_template);
					array_pop($meta_box_template);
					$meta_box_template = implode('</div>', $meta_box_template);
				}
			}

			// add 'fw-postbox' class and some attr related placeholders
			$meta_box_template = str_replace(
				'class="postbox',
				$placeholders['attr'] .' class="postbox fw-postbox'. $placeholders['attr_class'],
				$meta_box_template
			);

			// add html_before|after_title placeholders
			{
				$meta_box_template = str_replace(
					'<span>'. $placeholders['title'] .'</span>',

					/**
					 * used <small> not <span> because there is a lot of css and js
					 * that thinks inside <h3 class="hndle"> there is only one <span>
					 * so do not brake their logic
					 */
					'<small class="fw-html-before-title">'. $placeholders['html_before_title'] .'</small>'.
					'<span>'. $placeholders['title'] .'</span>'.
					'<small class="fw-html-after-title">'. $placeholders['html_after_title'] .'</small>',

					$meta_box_template
				);
			}

			FW_Cache::set($cache_key, $meta_box_template);
		}

		// prepare attributes
		{
			$attr_class = '';
			if (isset($other['attr']['class'])) {
				$attr_class = ' '. $other['attr']['class'];

				unset($other['attr']['class']);
			}

			unset($other['attr']['id']);
		}

		// replace placeholders with data/content
		return str_replace(
			array(
				$placeholders['id'],
				$placeholders['title'],
				$placeholders['content'],

				$placeholders['html_before_title'],
				$placeholders['html_after_title'],
				$placeholders['attr'],
				$placeholders['attr_class'],
			),
			array(
				esc_attr($id),
				$title,
				$content,

				$other['html_before_title'],
				$other['html_after_title'],
				fw_attr_to_html($other['attr']),
				esc_attr($attr_class)
			),
			$meta_box_template
		);
	}

	/**
	 * @param FW_Access_Key $access_key
	 * @param string|FW_Option_Type $option_type_class
	 * @internal
	 */
	public function _register_option_type(FW_Access_Key $access_key, $option_type_class)
	{
		if ($access_key->get_key() !== 'register_option_type') {
			trigger_error('Call denied', E_USER_ERROR);
		}

		$this->register_option_type($option_type_class);
	}

	/**
	 * @param string $option_type
	 * @return FW_Option_Type|FW_Option_Type_Undefined
	 */
	public function option_type($option_type)
	{
		if (is_array($this->option_types_pending_registration)) {
			// This method is called first time. Register pending option types
			$pending_option_types = $this->option_types_pending_registration;

			// clear this property, so register_option_type() will not add option types to pending anymore
			$this->option_types_pending_registration = false;

			foreach ($pending_option_types as $option_type_class) {
				$this->register_option_type($option_type_class);
			}

			unset($pending_option_types);
		}

		if (isset($this->option_types[$option_type])) {
			return $this->option_types[$option_type];
		} else {
			if (is_admin()) {
				FW_Flash_Messages::add(
					'fw-get-option-type-undefined-'. $option_type,
					sprintf(__('Undefined option type: %s', 'fw'), $option_type),
					'warning'
				);
			}

			return $this->undefined_option_type;
		}
	}
}

/**
 * This will be returned when tried to get not existing option type
 * to prevent fatal errors for cases when just one option type was typed wrong
 * or any other minor bug that has no sense to crash the whole site
 */
final class FW_Option_Type_Undefined extends FW_Option_Type
{
	public function get_type()
	{
		return '';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _render($id, $name, $data)
	{
		return '/* '. __('UNDEFINED OPTION TYPE', 'fw') .' */';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		return $option['value'];
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array()
		);
	}
}