<?php if (!defined('FW')) die('Forbidden');

/**
 * Working with DB models and config on Frontend
 * @internal
 */
class _FW_Extension_Sidebars_Frontend
{
	/** @var _FW_Extension_Sidebars_Config */
	private $config;

	private $current_page_preset;

	private $wp_option_sidebar_settings;

	public function __construct()
	{
		$this->wp_option_sidebar_settings = FW_Extension_Sidebars::get_fw_option_sidebars_settings_key();
		$this->config = new _FW_Extension_Sidebars_Config();
	}

	/**
	 * @return array database data
	 */
	private function get_db()
	{
		$db = get_option($this->wp_option_sidebar_settings);
		return !empty($db) ? $db : array();
	}


	/**
	 * If DB has preset for current page position
	 * @return string | false
	 */
	public function get_preset_position(){
		$preset = $this->get_current_page_preset();
		return $preset['position'];
	}

	/**
	 * @param $color string
	 * @return html string of rendered widgets for current page
	 */
	public function render_sidebar($color)
	{
		if(!in_array($color, _FW_Extension_Sidebars_Config::$allowed_colors))
			return false;

		$preset = $this->get_current_page_preset();

		//get available sidebar by color
		$sidebar = isset($preset['sidebars'][$color]) ? $preset['sidebars'][$color] : null;

		ob_start();

		//check if sidebar is active
		if ( ! empty( $sidebar ) ) {
			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( ! empty( $sidebars_widgets[$sidebar] ) ){
				dynamic_sidebar( $sidebar );
			} else {
				echo fw_render_view(fw_get_framework_directory('/extensions/sidebars/views/frontend-no-widgets.php'), array('sidebar_id' => $sidebar) );
			}
		}

		return ob_get_clean();
	}

	/**
	 * Generate current page requirements and return array with available sidebars for current page
	 */
	public function get_current_page_preset()
	{
		// check if current_page_preset doesn't get before
		if ($this->current_page_preset !== null) {
			return $this->current_page_preset;
		}

		if (is_singular()){
			$data['type']     = $this->config->get_type_by_prefix(_FW_Extension_Sidebars_Config::POST_TYPES_PREFIX);
			$data['sub_type'] = get_post_type();
			$data['id']       = get_the_id();

			$result = $this->get_preset_sidebars($data);
			if ( $result )
					return $result;
		}

		if (is_category()) {
			$data['type']     = $this->config->get_type_by_prefix(_FW_Extension_Sidebars_Config::TAXONOMIES_PREFIX);
			$data['sub_type'] = 'category';
			$data['id']       = get_query_var('cat');

			$result           = $this->get_preset_sidebars($data);
			if ( $result )
				return $result;
		}

		//was disabled from config
		{
			if (is_tag()) {
				$term_obj         = get_term_by('slug', get_query_var('tag'), 'post_tag');
				$data['type']     = $this->config->get_type_by_prefix(_FW_Extension_Sidebars_Config::TAXONOMIES_PREFIX);
				$data['sub_type'] = $term_obj->taxonomy;
				$data['id']       = $term_obj->term_id;

				$result           = $this->get_preset_sidebars($data);
				if ( $result )
					return $result;
			}
		}

		if (is_tax()) {
			$term_obj         = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
			$data['type']     = $this->config->get_type_by_prefix(_FW_Extension_Sidebars_Config::TAXONOMIES_PREFIX);
			$data['sub_type'] = $term_obj->taxonomy;
			$data['id']       = $term_obj->term_id;

			$result           = $this->get_preset_sidebars($data);
			if ( $result )
				return $result;
		}

		$conditional_tags = $this->config->get_conditional_tags();

		foreach($conditional_tags as $key => $cond_tag)
		{
			$function = null;
			if (isset($cond_tag['conditional_tag']))
			{
				$function = isset($cond_tag['conditional_tag']['callback']) ? $cond_tag['conditional_tag']['callback'] : '';

				if (is_callable($function)) {
					$params = array();

					if (isset($cond_tag['conditional_tag']['params']) and is_array($cond_tag['conditional_tag']['params']) ) {
						$params = $cond_tag['conditional_tag']['params'];
					}

					if ( call_user_func_array($function, $params) ) {
						$data['type'] = $this->config->get_type_by_prefix(_FW_Extension_Sidebars_Config::CONDITIONAL_TAGS_PREFIX);
						$data['sub_type'] = $key;

						$result = $this->get_preset_sidebars($data);

						if ($result)
							return $result;
					}
				}
			} else {
				$function = $key;
				if (is_callable($function)) {
					if (call_user_func($function)){
						$data['type'] = $this->config->get_type_by_prefix(_FW_Extension_Sidebars_Config::CONDITIONAL_TAGS_PREFIX);
						$data['sub_type'] = $key;

						$result = $this->get_preset_sidebars($data);

						if ($result)
							return $result;
					}
				}
			}

		}

		$data['type'] = $this->config->get_type_by_prefix(_FW_Extension_Sidebars_Config::DEFAULT_PREFIX);
		$data['sub_type'] = _FW_Extension_Sidebars_Config::DEFAULT_SUB_TYPE;
		$result = $this->get_preset_sidebars($data); //return preset default for all pages
		return $result;
	}

	/**
	 * Get avaible preset from DB by current page requirements
	 */
	private function get_preset_sidebars($data){
		if ($this->config->is_enabled_select_option($data['type'], $data['sub_type'])) {
			$settings = $this->get_db();
			if (!empty($data['id'])){   //get by ids preset
				if (isset($settings['settings'][$data['type']][$data['sub_type']]['saved_ids'])){ //check if id in saved_ids
					if (in_array($data['id'],$settings['settings'][$data['type']][$data['sub_type']]['saved_ids'])){
						$by_ids_presets = $settings['settings'][$data['type']][$data['sub_type']]['by_ids'];
						foreach($by_ids_presets as $preset_key => $preset)
						{
							if (in_array($data['id'],$preset['ids'])){
								$this->current_page_preset = $settings['settings'][$data['type']][$data['sub_type']]['by_ids'][$preset_key];
								if (isset($this->current_page_preset['timestamp'])) {
									unset($this->current_page_preset['timestamp']);
								}
								return $this->current_page_preset;
							}

						}
					}
				}
			}

			$this->current_page_preset = fw_akg('settings/'. $data['type'] . '/' . $data['sub_type'] . '/common', $settings, false);
			if (isset($this->current_page_preset['timestamp'])) {
				unset($this->current_page_preset['timestamp']);
			}
			return $this->current_page_preset;
		}

		return false;
	}

}
