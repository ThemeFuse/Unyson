<?php if (!defined('FW')) die('Forbidden');
require dirname(__FILE__) . '/includes/default/class-fw-extension-slider-default.php';

class FW_Extension_Slider extends FW_Extension
{
	private $post_type = 'fw-slider';

	/**
	 * @internal
	 */
	public function _init()
	{
		if (is_admin()) {
			$this->add_admin_filters();
			$this->add_admin_actions();
		}

	}

	private function add_admin_filters()
	{
		add_filter('fw_post_options', array($this, '_admin_filter_load_options'), 10, 2);
		add_filter('wp_insert_post_data', array($this, '_admin_filter_pre_save_slider_title'), 99, 2);
		add_filter('post_updated_messages', array($this, '_admin_filter_change_updated_messages'));
		add_filter('manage_' . $this->get_post_type() . '_posts_columns', array($this, '_admin_filter_add_columns'), 10, 1);
		add_filter('post_row_actions', array($this, '_admin_filter_post_row_actions'), 10, 2);
		add_filter('bulk_actions-edit-' . $this->get_post_type(), array($this, '_admin_filter_customize_bulk_actions'));
		add_filter('post_updated_messages', array($this, '_admin_filter_remove_notices'));
		add_filter('parent_file', array($this, '_set_active_submenu'));
	}

	public function get_post_type()
	{
		return $this->post_type;
	}

	private function add_admin_actions()
	{
		add_action('admin_enqueue_scripts', array($this, '_admin_action_enqueue_static'));
		add_action('admin_menu', array($this, '_admin_action_replace_submit_meta_box'));
		add_action('manage_' . $this->get_post_type() . '_posts_custom_column', array($this, '_admin_action_manage_custom_column'), 10, 2);
	}

	function _set_active_submenu($parent_file)
	{
		global $submenu_file, $current_screen;

		// Set correct active/current submenu in the WordPress Admin menu
		if ($current_screen->post_type == $this->post_type) {
			$submenu_file = 'edit.php?post_type=' . $this->post_type;
		}
		return $parent_file;
	}

	/**
	 * @internal
	 */
	public function _admin_filter_remove_notices($messages)
	{
		if (get_post_type() === $this->post_type) {
			foreach ($messages[$this->post_type] as $key => $message) {
				$messages[$this->post_type][$key] = preg_replace('/<a[^>]*>([\s\S]*?)<\/a[^>]*>/', '', $message);
			}
		}

		return $messages;
	}

	/*Hide edit bulk action from table*/

	/**
	 * @internal
	 */
	public function _admin_action_manage_custom_column($column, $post_id)
	{
		switch ($column) {
			case 'slider_design' :
				$image = $this->get_slider_type($post_id);
				$link = get_edit_post_link($post_id);
				echo '<a href="' . $link . '"><img height="100" src="' . $image['small']['src'] . '"/></a>';
				break;
			case 'number_of_images' :
				echo fw()->extensions->get('population-method')->get_number_of_images($post_id);
				break;
			case 'population_method' :
				$population_method = fw()->extensions->get('population-method')->get_population_method($post_id);
				echo '<p><a>' . array_shift($population_method) . '</a></p>';
				break;
			default :
				break;
		}
	}

	/*Hide actions from rows in table (Quick Edit and View)*/

	private function get_slider_type($post_id)
	{
		$slider_name = fw_get_db_post_option($post_id, $this->get_name() . '/selected');
		$sliders_types = $this->get_sliders_types();
		return isset($sliders_types[$slider_name]) ? $sliders_types[$slider_name] : array();
	}

	//TODO must return to normal method
	private function get_sliders_types()
	{
		$choices = array();
		foreach ($this->get_active_sliders() as $instance_name) {
			$choices[$instance_name] = $this->get_child($instance_name)->get_slider_type();
		}
		return $choices;
	}

	private function get_active_sliders()
	{
		$active_sliders = array();
		foreach ($this->get_children() as $slider_instance) {
			$slider_population_methods = $slider_instance->get_population_methods();
			if (!empty($slider_population_methods)) {
				$active_sliders[] = $slider_instance->get_name();
			}
		}
		return $active_sliders;
	}

	/**
	 * @internal
	 */
	public function _admin_filter_customize_bulk_actions($actions)
	{
		unset($actions['edit']);
		return $actions;
	}

	/**
	 * @internal
	 */
	public function _admin_filter_post_row_actions($actions, $post)
	{
		if ($post->post_type === $this->get_post_type()) {
			unset($actions['inline hide-if-no-js'], $actions['view']);
		}
		return $actions;
	}

	/**
	 * @internal
	 */
	public function _admin_filter_add_columns($columns)
	{
		return array(
			'cb' => $columns['cb'],
			'slider_design' => __('Slider Design', 'fw'),
			'title' => $columns['title'],
			'number_of_images' => __('Number of Images', 'fw'),
			'population_method' => __('Population Method', 'fw'),
		);
	}

	/**
	 * @internal
	 */
	function _admin_filter_change_updated_messages($messages)
	{
		global $post;
		$post_type = get_post_type($post->ID);

		if ($post_type === $this->get_post_type()) {
			$obj = get_post_type_object($post_type);
			$singular = $obj->labels->singular_name;

			$messages[$post_type] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf(__('%s updated. <a href="%s">View %s</a>', 'fw'), $singular, esc_url(get_permalink($post->ID)), strtolower($singular)),
				2 => __('Custom field updated.', 'fw'),
				3 => __('Custom field deleted.', 'fw'),
				4 => sprintf(__('%s updated.', 'fw'), $singular),
				5 => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s', 'fw'), $singular, wp_post_revision_title((int)$_GET['revision'], false)) : false,
				6 => sprintf(__('%s published. <a href="%s">View %s</a>'), $singular, esc_url(get_permalink($post->ID)), strtolower($singular)),
				7 => __('Page saved.', 'fw'),
				8 => sprintf(__('%s submitted. <a target="_blank" href="%s">Preview %s</a>'), $singular, esc_url(add_query_arg('preview', 'true', get_permalink($post->ID))), strtolower($singular)),
				9 => sprintf(__('%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>'), $singular, date_i18n(__('M j, Y @ G:i', 'fw'), strtotime($post->post_date)), esc_url(get_permalink($post->ID)), strtolower($singular)),
				10 => sprintf(__('%s draft updated. <a target="_blank" href="%s">Preview %s</a>'), $singular, esc_url(add_query_arg('preview', 'true', get_permalink($post->ID))), strtolower($singular)),
			);
		}

		return $messages;
	}

	/**
	 * @internal
	 */
	public function _admin_filter_pre_save_slider_title($data, $postarr)
	{
		if ($data['post_type'] === $this->get_post_type()) {
			if (isset($postarr['fw_options']['slider']['selected'])) {
				$active_slider = $postarr['fw_options']['slider']['selected'];
				$data['post_title'] = $postarr['fw_options']['slider'][$active_slider]['title'];
			}

			if (isset($postarr['fw_options']['title'])) {
				$data['post_title'] = $postarr['fw_options']['title'];
			}
		}

		return $data;
	}

	/**
	 * @internal
	 */
	public function _admin_action_replace_submit_meta_box()
	{
		remove_meta_box('submitdiv', $this->get_post_type(), 'core');
		add_meta_box('submitdiv', __('Publish', 'fw'), array($this, 'render_submit_meta_box'), $this->get_post_type(), 'side');
	}

	public function render_submit_meta_box($post, $args = array())
	{
		// a modified version of post_submit_meta_box() (wp-admin/includes/meta-boxes.php, line 12)
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);
		$meta = fw_get_db_post_option($post->ID);

		if (isset($_GET['action']) && $_GET['action'] === 'edit') {
			$slider_name = $meta['slider']['selected'];
			$population_method = $this->get_child($slider_name)->get_population_method($meta['slider'][$slider_name]['population-method']);
			$slider_type = $this->get_slider_type($post->ID);
			echo $this->render_view('backend/submit-box-edit', compact('post', 'population_method', 'meta', 'post_type', 'post_type_object', 'can_publish', 'slider_type'));
		} else {
			echo $this->render_view('backend/submit-box-raw', compact('post', 'meta', 'post_type', 'post_type_object', 'can_publish'));
		}
	}

	/**
	 * @internal
	 */
	public function _admin_filter_load_options($options, $post_type)
	{
		if ($post_type === $this->get_post_type()) {
			if (fw_is_post_edit()) {
				return $this->load_post_edit_options();
			} else {
				return $this->load_post_new_options();
			}
		}

		return $options;
	}

	public function load_post_edit_options()
	{
		global $post;
		$selected = fw_get_db_post_option($post->ID, $this->get_name().'/selected');
		$title_value = fw_get_db_post_option($post->ID, $this->get_name() . '/'.$selected.'/title');

		$options = array_merge(
			array(
				'slider-sidebar-metabox' => array(
					'context' => 'side',
					'title' => __('Slider Configuration', 'fw'),
					'type' => 'box',
					'options' => array(
						'populated' => array(
							'type' => 'hidden',
							'value' => true
						),
						'title' => array(
							'type' => 'text',
							'label' => __('Slider Title', 'fw'),
							'value' => $title_value,
							'desc' => 'Choose a title for your slider only for internal use: Ex: "Homepage".'
						)
					)
				)),
			$this->get_slider_population_method_options()
		);

		$custom_settings = $this->get_slider_options();

		if (!empty($custom_settings)) {
			$selected = fw_get_db_post_option($post->ID, $this->get_name() . '/selected');
			$custom_settings_value = fw_get_db_post_option($post->ID, $this->get_name() . '/'.$selected.'/custom-settings');
			$options['slider-sidebar-metabox']['options']['custom-settings'] = array(
				'label' => false,
				'desc' => false,
				'type' => 'multi',
				'value' => $custom_settings_value,
				'inner-options' => $this->get_slider_options()
			);
		}

		return $options;
	}

	private function get_slider_population_method_options()
	{
		global $post;

		$slider_name = fw_get_db_post_option($post->ID, $this->get_name() . '/selected');
		$population_method = fw_get_db_post_option($post->ID, $this->get_name() . '/'.$slider_name.'/population-method');
		$slider_instance = $this->get_child($slider_name);
		$multimedia_types = $slider_instance->get_multimedia_types();
		$options = $slider_instance->get_population_method_options($population_method);

		return fw()->extensions->get('population-method')->get_population_options($population_method, $multimedia_types, $options);

	}

	private function get_slider_options()
	{
		global $post;

		$slider_type = fw_get_db_post_option($post->ID, $this->get_name() . '/selected');

		return $this->get_child($slider_type)->get_slider_options();
	}

	public function load_post_new_options()
	{
		return array(
			'general' => array(
				'title' => __('Slider Settings', 'fw'),
				'type' => 'box',
				'options' => array(
					$this->get_name() => array(
						'type' => 'multi-picker',
						'value' => '',
						'show_borders' => true,
						'label' => false,
						'desc' => false,
						'picker' => array(
							'selected' => array(
								'label' => __('Type', 'fw'),
								'type' => 'image-picker',
								'choices' => $this->get_sliders_types()
							)
						),
						'choices' => $this->get_sliders_sets_options()
					)
				)
			)
		);
	}

	private function get_sliders_sets_options()
	{
		$options = array();
		foreach ($this->get_active_sliders() as $instance_name) {

			$slider_options = $this->get_child($instance_name)->get_slider_options();

			$options[$instance_name] = array(
				'population-method' => array(
					'type' => 'select',
					'label' => __('Population Method', 'fw'),
					'desc' => __('Choose the population method for your slider', 'fw'),
					'value' => '',
					'choices' => $this->get_child($instance_name)->get_population_methods()
				),
				'title' => array(
					'type' => 'text',
					'label' => __('Title', 'fw'),
					'value' => '',
					'desc' => 'Choose the ' . $this->get_name() . ' title (for internal use)'
				)
			);

			if (!empty($slider_options)) {
				$options[$instance_name]['custom-settings'] = array(
					'label' => false,
					'desc' => false,
					'type' => 'multi',
					'inner-options' => $slider_options
				);
			}
		}

		return $options;
	}

	public function render_slider($post_id, $dimensions)
	{
		$slider_name = fw_get_db_post_option($post_id, $this->get_name() . '/selected');

		if (!is_null($slider_name)) {
			return $this->get_child($slider_name)->render_slider($post_id, $dimensions);
		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_enqueue_static()
	{
		$match_current_screen = fw_current_screen_match(
			array(
				'only' => array(
					array(
						'post_type' => $this->post_type,
					)
				)
			));

		if ($match_current_screen) {
			wp_enqueue_style(
				'fw-extension-' . $this->get_name() . '-css',
				$this->get_declared_URI('/static/css/style.css'),
				array(),
				fw()->manifest->get_version()
			);
		}
	}

	public function get_populated_sliders_choices()
	{
		$choices = array();

		foreach ($this->get_populated_sliders() as $slider) {

			$choices[$slider->ID] = empty($slider->post_title) ? __('(no title)', 'fw') : $slider->post_title;
		}

		return $choices;
	}

	public function get_populated_sliders()
	{
		$posts = get_posts(array(
			'post_type' => $this->post_type,
			'numberposts' => -1
		));

		foreach ($posts as $key => $post) {
			$data =fw()->extensions->get('population-method')->get_frontend_data($post->ID);
			if(empty($data['slides'])){
				unset($posts[$key]);
			}

		}
		return $posts;
	}

}
