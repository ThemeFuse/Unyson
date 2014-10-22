<?php if (!defined('FW')) die('Forbidden');

/**
 * Working with DB models and config on Backend
 * @internal
 */
class _FW_Extension_Sidebars_Backend
{
	/** @var _FW_Extension_Sidebars_Model_Sidebar[] */
	private $sidebars;

	/** @var _FW_Extension_Sidebars_Config */
	public $config;

	/** @var _FW_Extension_Sidebars_Model_Sidebar[] */
	public $static_sidebars;

	//Key for update_option/get_option
	private $wp_option_sidebar_settings;

	public function __construct()
	{
		//get static registered sidebars, which are registered before fw loaded
		global $wp_registered_sidebars;
		if (is_array($wp_registered_sidebars)){
			foreach($wp_registered_sidebars as $sidebar_args) {
				$this->set_static_sidebars($sidebar_args);
			}
		}

		$this->wp_option_sidebar_settings = FW_Extension_Sidebars::get_fw_option_sidebars_settings_key();
		$this->config = new _FW_Extension_Sidebars_Config();
	}

	public function init_sidebars(){
		$this->init_dynamic_sidebars();
		add_action('register_sidebar', array($this, 'set_static_sidebars'));
	}

	/**
	 * Register dynamic sidebars
	 */
	private function init_dynamic_sidebars()
	{
		$sidebars = (array)$this->get_sidebars();
		if (is_array($sidebars)) {
			foreach($sidebars as $sidebar) {
				$sidebar->register();
			}
		}
	}

	/**
	 * Get dynamic & static sidebars
	 */
	public function get_all_sidebars() {
		$dynamic_sidebars = $this->get_sidebars();
		return array_merge((array)$dynamic_sidebars,(array)$this->static_sidebars);
	}

	/**
	 * Get array of saved settings from db
	 */
	public function get_db()
	{
		$db = get_option($this->wp_option_sidebar_settings);
		if (empty($db))
			$db = array();
		return $db;
	}

	/**
	 * Fill static_sidebars array with new sidebar model
	 */
	public function set_static_sidebars($sidebar_args)
	{
		if ($sidebar_args['id'] === 'wp_inactive_widgets')
			return;
		if(preg_match('(inactive-sidebar)', $sidebar_args['class']))
			return;
		$this->static_sidebars[$sidebar_args['id']] =  new _FW_Extension_Sidebars_Model_Sidebar($sidebar_args);
	}

	/**
	 * Get array of dynamic sidebars models
	 */
	public function get_sidebars()
	{
		$dynamic_sidebars_args = $this->config->get_dynamic_sidebar_args();
		if ($this->sidebars === NULL) {
			$sidebars_array = $this->get_db();
			if (isset($sidebars_array['sidebars']) && count($sidebars_array['sidebars'])) {
				foreach($sidebars_array['sidebars'] as $sidebar_args)
				{
					$sidebar_args = array_merge($dynamic_sidebars_args, $sidebar_args); //merge DB settings with config settings
					$this->sidebars[$sidebar_args['id']] = new _FW_Extension_Sidebars_Model_Sidebar($sidebar_args);
				}
				unset($sidebars_array);
				return $this->sidebars;
			}
		}
		return $this->sidebars;
	}

	/**
	 * Get dynamic sidebars id's
	 */
	public function get_sidebars_list()
	{
		return is_array($this->get_sidebars()) ? array_keys($this->get_sidebars()) : array();
	}


	public function get_preset($params)
	{
		if (preg_match('/^\d+$/',$params['preset']))
			return $this->get_preset_specific($params);
		else
			return $this->get_preset_grouped($params);
	}

	/**
	 * Get preset for grouped pages with ajax request params
	 */
	private function get_preset_grouped($params)
	{
		//get preset data
		$sub_type = $this->config->parse_sub_type($params['slug']);
		$prefix = $this->config->parse_prefix($params['slug']);
		$type = $this->config->get_type_by_prefix($prefix);

		$path = 'settings/' . $type . '/' . $sub_type .'/common';

		$db = $this->get_db();
		$result['preset'] = fw_akg($path, $db, null);

		if (is_array($result['preset'])) {
			$result['status'] = true;
			$result['preset'] = array_merge($result['preset'], $params);
			$ids = fw_akg('preset/ids', $result);
			if (is_array($ids)){
				$result['preset']['ids'] = $this->build_preset_ids_list($prefix, $ids, $sub_type);
			}
		}
		else
			$result['status'] = false;

		return $result;
	}

	/**
	 * Get preset for specific pages by ajax request params
	 */
	private function get_preset_specific($args)
	{
		$db = $this->get_db();
		$saved_presets = fw_akg('settings/saved_presets', $db, array());
		if (!in_array($args['preset'],$saved_presets))
			return array('status' => false);

		$db_keys = $this->get_db_keys($db);
		$result = array();
		foreach($db_keys as $db_key){
			$preset = fw_akg($db_key['path'] . '/by_ids/' . $args['preset'], $db);
			if(!empty($preset)){
				$singular_label = $this->config->get_label_singular($db_key['type'], $db_key['sub_type']);
				$ids_list = $this->build_preset_ids_list($db_key['prefix'], $preset['ids'], $db_key['sub_type']);

				$result['by_ids'][] = array(
						'slug' => $singular_label,
						'ids' => $ids_list
					);
				unset($preset['ids']);
				$result['preset'] = $preset;
				$result['preset']['preset'] = $args['preset'];
				$result['status'] = true;
			}
		}
		return $result;
	}

	/**
	 * @param $id
	 * @return type _FW_Extension_Sidebars_Model_Sidebar or FALSE
	 */
	public function get_sidebar_by_id($id)
	{
		$sidebars = $this->get_sidebars();
		if (!is_array($sidebars))
			return FALSE;

		if (array_key_exists($id, $sidebars))
			return $sidebars[$id];
		else
			return FALSE;
	}

	/**
	 * Generate valid unique sidebar id from name
	 */
	public function generate_sidebar_id($name)
	{
		$result = preg_replace(array('/\s+/', '/[^a-z0-9\-]/i', ), array('-', ''), $name);

		if (!strlen($result)){
			$result = 'fw-sidebar';
		}

		if (preg_match('/^(aa|jj|hh|\d+)$/i', $name)) {
			$result = 'fw-sidebar-' . $name;
		}

		$k = 0;
		do {
			$new_id = $result . ($k++ == 0 ? '' : ('-' . ($k - 1)));
			if ($this->get_sidebar_by_id($new_id) === FALSE && !isset($this->static_sidebars[$new_id] )) {
				$result = $new_id;
				break;
			}
		} while (1);

		return $result;
	}

	/**
	 * Save new sidebar to DB
	 * @param $save_sidebar_fields  'general' - save only sidebar id and sidebar name, 'all' - save all info
	 * @param $name string - sidebar name
	 * @return array
	 */
	public function save_new_sidebar($name, $save_sidebar_fields = 'general') 
	{
		if(!strlen(trim($name))) {
			return array('status' => false, 'message' => __('No sidebar name specified.','fw') );
		}

		$sidebar_args = $this->config->get_dynamic_sidebar_args();
		$sidebar_args['name'] = $name;
		$sidebar = new _FW_Extension_Sidebars_Model_Sidebar($sidebar_args);

		$db = $this->get_db();

		$sidebar->set_id($this->generate_sidebar_id($sidebar->get_name()));

		$db['sidebars'][$sidebar->get_id()] = $sidebar->to_array($save_sidebar_fields);

		$result['status'] = update_option($this->wp_option_sidebar_settings, array_filter($db));
		if ($result['status']){
			$result['sidebar'] = $sidebar->to_array($save_sidebar_fields);
		}

		return $result;
	}

	/**
	 * Remove sidebar from db's sidebars list
	 */
	public function delete_sidebar($sidebar_id) {

		$db = $this->get_db();

		if (!isset($db['sidebars'][$sidebar_id])){
			return array('status' => false, 'message' => __('Dynamic sidebar doesn\'t exixt', 'fw'));
		}

		$sidebar_used_key = $this->recursive_array_search($sidebar_id, fw_akg('settings', $db, array()));
		if ($sidebar_used_key !== false){
			return array('status' => false, 'message' => __('The placeholder can\'t be deleted because it is used in one of sidebars below.<br/> <br/> <b>Please replace it first so that you will not have visual gaps in your layout.<b/>','fw') );
		}

		fw_aku('sidebars/' . $sidebar_id, $db);

		update_option($this->wp_option_sidebar_settings, array_filter($db));

		$sidebar_obj = new _FW_Extension_Sidebars_Model_Sidebar(array('id'=>$sidebar_id));
		$sidebar_obj->remove_widgets();

		return array('status' => true, 'message' => __('Successfully removed','fw'));
	}

	/**
	 *  Get results by user query for autocomplete
	 */
	public function get_autocomplete_results($search_slug, $search_term, $max_autocomplete_results = 50)
	{
		$search_type      = $this->config->get_type_by_prefix($this->config->parse_prefix($search_slug));
		$search_sub_type  = $this->config->parse_sub_type($search_slug);

		$result = array(
			'status' => false,
			'items'  => array(),
		);

		switch ($search_type)
		{
			case 'post_types':
				$wp_query = new WP_Query(array('post_type' => $search_sub_type , 'fw_ext_sidebars_post_title_like' => $search_term, 'numberposts' => $max_autocomplete_results, 'post_status' => 'any'));
				$items = $wp_query->get_posts();
				wp_reset_query();
				foreach($items as $item){
					$result['items'][$item->ID] = $item->post_title;
				}
				$result['status'] = true;
				unset($items);
				break;

			case 'taxonomies':
				$items = get_terms($search_sub_type, array('name__like'=>$search_term, 'hide_empty' => false, 'number' => $max_autocomplete_results));
				foreach($items as $item){
					$result['items'][$item->term_id] = $item->name;
				}
				$result['status'] = true;
				unset($items);
				break;
			default: 
				$result['status'] = false;
				break;
		}
		
		return $result;
	}

	public function recursive_array_search($needle, $haystack) {
		foreach($haystack as $key=>$value) {
			$current_key = $key;
			if($needle === $value OR (is_array($value) && $this->recursive_array_search($needle,$value) !== false)) {
				return $current_key;
			}
		}
		return false;
	}

	/**
	 * Specifies the type of preset by ajax arguments and calls the appropriate method.
	 */
	public function remove_preset($args)
	{
		if($args['slug']){
			return array(
				'status' => $this->remove_preset_grouped($args)
			);
		}else{
			return array(
				'status' => $this->remove_preset_specific($args)
			);
		}
	}

	/**
	 * Remove preset from DB, which has ids inside
	 */
	private function remove_preset_specific($args)
	{
		if(!preg_match('/^\d+$/', $args['preset']))
			return false;

		$db = $this->get_db();
		$saved_presets = fw_akg('settings/saved_presets', $db, array());
		if (!in_array($args['preset'],$saved_presets ))
			return false;

		$db_keys = $this->get_db_keys($db);

		fw_aku('settings/saved_presets/' . array_search($args['preset'], $saved_presets), $db);
		foreach($db_keys as $db_key){
			fw_aku($db_key['path'] . '/by_ids/' . $args['preset'], $db);
			$this->recalculate_saved_ids( $db, $db_key['type'], $db_key['sub_type'] );
			$this->clean_unused_arrays( $db, $db_key['type'], $db_key['sub_type'] );
		}

		return update_option($this->wp_option_sidebar_settings, $db);
	}

	/**
	 * Remove common preset from DB
	 */
	private  function remove_preset_grouped($item)
	{
		$db = $this->get_db();
		$sub_type = $this->config->parse_sub_type($item['slug']);
		$prefix = $this->config->parse_prefix($item['slug']);
		$type = $this->config->get_type_by_prefix($prefix);

		if(empty($type) or empty($sub_type)){
			return false;
		}

		unset($db['settings'][$type][$sub_type]['common']);
		$this->clean_unused_arrays($db, $type, $sub_type);

		return update_option($this->wp_option_sidebar_settings, $db);
	}

	protected function clean_unused_arrays(&$db, $type, $sub_type)
	{

		if ($type === 'saved_presets')
			return false;

		if (isset($db['settings'][$type][$sub_type]['by_ids']) and empty($db['settings'][$type][$sub_type]['by_ids']) ) {
			unset($db['settings'][$type][$sub_type]['by_ids']);
		}

		if (isset($db['settings'][$type][$sub_type]['saved_ids']) and empty($db['settings'][$type][$sub_type]['saved_ids']) ) {
			unset($db['settings'][$type][$sub_type]['saved_ids']);
		}

		if (isset($db['settings'][$type][$sub_type]) and empty($db['settings'][$type][$sub_type]) ) {
				unset($db['settings'][$type][$sub_type]);
		}

		if (isset($db['settings'][$type]) and empty($db['settings'][$type]) ){
				unset($db['settings'][$type]);
		}

	}

	public function get_saved_ids($type, $sub_type)
	{
		$db = $this->get_db();
		return fw_akg('settings/' . $type . '/' . $sub_type . '/saved_ids', $db, array());
	}

	/*
	*   Make unique array with ids in &$db parameter
	*/
	public function recalculate_saved_ids(&$db, $type, $sub_type)
	{
		if ($type === $this->config->get_type_by_prefix( _FW_Extension_Sidebars_Config::CONDITIONAL_TAGS_PREFIX ) or $type === 'saved_presets' )
			return false;

		$result = array();
		$presets = fw_akg('settings/' . $type . '/' . $sub_type . '/by_ids', $db);
		if (is_array($presets))
		{
			foreach($presets as $preset)
			{
				$result = array_merge($result, $preset['ids']);
			}
		}

		$result = array_unique($result);
		fw_aks('settings/' . $type . '/' . $sub_type . '/saved_ids', $result, $db);
		return true;
	}

	/**
	 * GET presets settings for Created Tab
	 */
	public function get_presets_sidebars()
	{
		$result = array();

		$settings = $this->get_db();

		if (!isset($settings['settings']))
			return $result;

		$db_keys = $this->get_db_keys($settings);

		//build by_ids presets
		if (isset($settings['settings']['saved_presets'])) {
			foreach($settings['settings']['saved_presets'] as $preset_id){
				foreach($db_keys as $db_key){
					if(count(fw_akg($db_key['path'] . '/saved_ids', $settings, array()))){
						$preset = fw_akg($db_key['path'] . '/by_ids/' . $preset_id , $settings);
						if (is_array($preset)){
							$singular_label = $this->config->get_label_singular($db_key['type'], $db_key['sub_type']);
							$page_names = reset($singular_label) . ' - ' . implode(', ', $this->build_preset_ids_list($db_key['prefix'], $preset['ids'], $db_key['sub_type']));

							$result[$preset_id] = array(
								'label'      => reset($singular_label),
								'preset_id'  => $preset_id,
								'page_names' => (isset($result[$preset_id]['page_names']) ? $result[$preset_id]['page_names'] : '') . $page_names . ' ',
								'timestamp'  => isset($preset['timestamp']) ? $preset['timestamp'] : 0
							);
						}
					}
				}
			}
		}

		//build common presets
		foreach($db_keys as $slug => $db_key){
			$preset = fw_akg($db_key['path'] . '/common' , $settings);
			if (is_array($preset)){
				$grouped_label = $this->config->get_label_grouped($db_key['type'], $db_key['sub_type']);
				$result[] = array(
					'type'       => $slug,
					'label'      => $db_key['sub_type'] === 'common'? __('Default for all pages', 'fw') : reset($grouped_label),
					'timestamp'  => isset($preset['timestamp']) ? $preset['timestamp'] : 0
				);
			}
		}

		usort($result, array($this, 'preset_timestamp_cmp'));

		return $result;
	}

	public function preset_timestamp_cmp($a, $b)
	{
		if ($a['timestamp'] === $b['timestamp'])
			return 0;
		return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
	}

	/**
	 * Generate existing keys in array for fw_akg/fw_aks/fw_aku helpers
	 */
	public function get_db_keys(&$settings)
	{
		$result = array();
		if (!isset($settings['settings']))
			return $result;

		foreach($settings['settings'] as $type => $sub_types)
		{
			if ($type === 'saved_presets') {
				continue;
			}

			$prefix = $this->config->get_prefix_by_type($type);
			foreach($sub_types as $sub_type => $presets )
			{
				$result[$prefix . '_' . $sub_type] = array( 'path'      => "settings/$type/$sub_type",
															'prefix'    => $prefix,
															'type'      => $type,
															'sub_type'  => $sub_type
														);
			}
		}

		return $result;
	}

	/**
	 * Make array with [id] => (post / term) name
	 */
	public function build_preset_ids_list($prefix, &$ids, $sub_type)
	{
		$result = array();
		foreach($ids as $id) {
			if ( $prefix === _FW_Extension_Sidebars_Config::POST_TYPES_PREFIX ) {
				$obj = get_post($id);
				$this->_set_title_from_object($obj, $id, 'post_title', $result);
			}	else if( $prefix === _FW_Extension_Sidebars_Config::TAXONOMIES_PREFIX ) {
				$obj = get_term($id, $sub_type);
				$this->_set_title_from_object($obj, $id, 'name', $result);
			}
		}

		return $result;
	}

	private function _set_title_from_object($obj, $id, $field_name, &$result) {
		if (!empty($obj)) {
			$vars = get_object_vars($obj);
			if (isset($vars[$field_name])) {
				$result[$id] = empty($vars[$field_name]) ? '#' . $id . __(' (no title)', 'fw') : $vars[$field_name];
			} else {
				$result[$id] = $prefix . '_' . $sub_type . '_' . $id; // fixme
			}
		}
	}

	/**
	 * Save settings for specific pages/grouped pages/conditional tags
	 */
	public function update_preset($preset)
	{
		$db = $this->get_db();
		$path = 'settings/' . $preset['type'] . '/' . $preset['sub_type'];
		$value['position'] = $preset['position'];
		$value['sidebars'] = $preset['sidebars'];
		$value['timestamp'] = time();

		if (is_array($preset['ids']) and !empty($preset['ids'])) {
			$value['ids']      = $preset['ids'];
		}

		if (is_array($preset['ids'])){
			$path .= '/by_ids/' . $preset['preset'];
		}else {
			$path .= '/common';
		}

		fw_aks($path, $value, $db);

		if (is_array($preset['ids'])) {
			$this->recalculate_saved_ids($db, $preset['type'], $preset['sub_type']);
			$this->clean_unused_arrays($db, $preset['type'], $preset['sub_type']);

			$saved_presets = fw_akg('settings/saved_presets', $db, array());
			if (!in_array($preset['preset'], $saved_presets)) {
				$saved_presets[] = $preset['preset'];
				fw_aks('settings/saved_presets', $saved_presets, $db);
			}
		}

		update_option($this->wp_option_sidebar_settings, $db);

		return true;
	}

	/**
	 * Remove presets from db which was removed on specific pages
	 */
	public function clean_unused_presets($slugs, $preset_id){
		if ($preset_id === null) return false;
		$db = $this->get_db();

		foreach($db['settings'] as $type => $data){
			foreach($data as $sub_type => $data2) {
				$prefix = $this->config->get_prefix_by_type($type);
				if(!in_array($prefix . '_' . $sub_type, $slugs)){
					fw_aku('settings/' . $type . '/' . $sub_type . '/by_ids/' . $preset_id, $db);

					$this->recalculate_saved_ids($db, $type, $sub_type);
					$this->clean_unused_arrays($db, $type, $sub_type);

				}
			}
		}

		update_option($this->wp_option_sidebar_settings, $db);
		return true;
	}

	/**
	 * Generate unique preset id for specific pages tab
	 */
	public function generate_preset_id(){
		$saved_presets = $this->get_db();
		$saved_presets = fw_akg('settings/saved_presets', $saved_presets, array());
		$key = 0;
		while(in_array($key, $saved_presets)){
			$key++;
		}

		return $key;
	}

	/**
	 * Save grouped pages tab or specific page tab settings
	 */
	public function save_sidebar_settings($settings)
	{
		try{
			//Preset validate & saving for grouped pages tab
			if (isset($settings['slug']) and $settings['slug']){
				$preset           = $this->validate_preset($settings);
				$result['status'] = $this->update_preset($preset);
				if($result['status']){
					$grouped_label   = $this->config->get_label_grouped($preset['type'], $preset['sub_type']);
					$result['label'] = reset($grouped_label);
					$result['slug']  = key($grouped_label);
				}
			} else {
				//Preset validate & saving for specific tab
				$new_key_preset = $this->generate_preset_id();
				$slugs = array();
				$preset_id = null;

				$db = $this->get_db();
				$saved_settings = fw_akg('settings/saved_presets', $db, array());

				if ( !isset($settings['selected']) or empty($settings['selected']) )
					throw new Exception('Error: Selection is empty');

				foreach($settings['selected'] as $preset){

					$preset['sidebars'] = isset($settings['sidebars']) ? $settings['sidebars'] : array();
					$preset['position'] = isset($settings['position']) ? $settings['position'] : null;
					$preset['preset']   = isset($settings['preset'])   ? $settings['preset']   : null;

					if (!isset($preset['preset']) or !preg_match('/^\d+$/',$preset['preset']) or !in_array($preset['preset'], $saved_settings)) {
						$preset['preset'] = $new_key_preset;
					}

					$preset           = $this->validate_preset($preset);
					$result['status'] = $this->update_preset($preset);
					$slugs[]          = $preset['slug'];
					$preset_id        = $preset['preset'];

					if($result['status']){
						$singular_label     = $this->config->get_label_singular($preset['type'], $preset['sub_type']);
						$prefix             = $this->config->get_prefix_by_type($preset['type']);
						$page_names         = reset($singular_label) . ' - ' . implode(', ', $this->build_preset_ids_list($prefix, $preset['ids'], $preset['sub_type']));
						$result['preset']   = $preset_id;
						$result['label']    = (isset($result['label']) ? $result['label'] : '' ) . $page_names . ' ';
					}

				}

				$slugs = array_unique($slugs);
				$this->clean_unused_presets($slugs, $preset_id);
			}

		}catch (Exception $e){
			$result['status']  = false;
			$result['message'] = $e->getMessage();
			if ($e instanceof _FW_Extension_Sidebars_MissingSidebar_Exception ) {
				$result['colors'] = $e->get_colors();
			}
		}

		return $result;
	}


	/**
	 * validate ajax params
	 */
	private function validate_preset($preset)
	{
		$prefix = $this->config->parse_prefix($preset['slug']);
		$sub_type = $this->config->parse_sub_type($preset['slug']);
		$type = $this->config->get_type_by_prefix($prefix);

		if (!$sub_type or !$type) {
			throw new Exception(__('Error: Type or sub_type error','fw'));
		}

		$preset['type'] = $type;
		$preset['sub_type'] = $sub_type;

		if ($this->config->is_enabled_select_option($type, $sub_type) === false) {
			throw new Exception(__(sprintf('Error this option (%s) is disabled', $type . '-' . $sub_type ),'fw'));
		}

		if (!isset($preset['position']) or !$this->config->has_position($preset['position'])){
			throw new Exception(__("Error: Position doesn't exists. Please check config file.",'fw'));
		}

		$position_allowed_colors = $this->config->get_allowed_color_by_position($preset['position']);

		//removing invalid data from $preset['sidebars']
		$exceptionMsg = __('Error: Sidebars not set','fw');
		if ( isset($preset['sidebars']) and is_array($preset['sidebars']) ) {
			$exception = new _FW_Extension_Sidebars_MissingSidebar_Exception($exceptionMsg);
			array_walk($preset['sidebars'], array($this, 'clear_preset_sidebars'), $position_allowed_colors);
			foreach($preset['sidebars'] as $color => $sidebar_id)
			{
				if (empty($sidebar_id)) {
					//if added invalid sidebarId, add 'color' to exception
					unset($preset['sidebars'][$color]);
					$exception->add_color($color);
				}
			}

			if ($exception->has_colors()) {
				throw $exception;
			}

		} else if (!empty($position_allowed_colors)) {
			//if not set sidebars, but it is in position throw exception
			throw new Exception($exceptionMsg);
		} else {
			$preset['sidebars'] = array();
		}

		//remove duplicates in array
		if(is_array($preset['ids']))
		{
			$preset['ids'] = array_unique($preset['ids']);
		}

		return $preset;
	}

	//Used in validate_preset method
	private function clear_preset_sidebars(&$sidebar_id, $color, $position_allowed_colors)
	{
		//check if allowed color
		if (!in_array($color, $position_allowed_colors)){
			$sidebar_id = false;
		}

		//check if existing sidebar id
		if ($sidebar_id) {
			$sidebar_static = isset($this->static_sidebars[$sidebar_id]) ? $this->static_sidebars[$sidebar_id] : false;
			$sidebar_obj = $this->get_sidebar_by_id($sidebar_id) ? $this->get_sidebar_by_id($sidebar_id) : $sidebar_static;
			if (!$sidebar_obj)
			{
				$sidebar_id = false;
			}
		}
	}

}

