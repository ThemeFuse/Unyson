<?php if (!defined('FW')) die('Forbidden');

/**
 * Description of FW_Extension_Sidebars_config
 * Working with config
 * @internal
 */
class _FW_Extension_Sidebars_Config
{
	private $config = array();

	const TAXONOMIES_PREFIX       = 'tx';
	const POST_TYPES_PREFIX       = 'pt';
	const CONDITIONAL_TAGS_PREFIX = 'ct';
	const DEFAULT_PREFIX          = 'df';
	const DEFAULT_SUB_TYPE        = 'all';
	const DEFAULT_SLUG            = 'df_all';
	const SIDEBARS_NR_KEY         = 'sidebars_number';

	private static $config_keys = array(
		self::POST_TYPES_PREFIX         => 'post_types',
		self::TAXONOMIES_PREFIX         => 'taxonomies',
		self::CONDITIONAL_TAGS_PREFIX   => 'conditional_tags',
		self::DEFAULT_PREFIX            => 'default'
	);


	/*
	 * Conditional tag with params example
	 *
	'front_page_slug'  => array(
		'order_option'  => 1,
		'name'                  => 'Pages with Front-Page template',
		'conditional_tag'		=> array(
			'callback'				=> 'is_page_template',
			'params'                => array('page-templates/front-page.php')
		)
	),*/
	private function get_config_defaults() {
		return array(
			'select_options' => array(
				'post_types' => array(
					'attachment'    => false,
					'revision'      => false,
					'nav_menu_item' => false,
					'post'  => array(
						'name' => array(
							'singular' => __('Blog Post','fw'),
							'plural'   => __('Blog Posts', 'fw')
						)
					),
					'page'  => array(
						'name' => array(
							'singular' => __('Page','fw'),
							'plural'   => __('Pages', 'fw')
						),
					),
					'fw-portfolio'=> array(
						'name' => array(
							'singular' => __('Portfolio Project', 'fw'),
							'plural'   => __('Portfolio Projects', 'fw'),
						)
					)
				),
				'taxonomies' => array(
					'post_tag'      => false,
					'post_format'   => false,
					'nav_menu'      => false,
					'link_category' => false,
					'category'      => array(
						'name'      => array(
							'singular' => __('Blog Category', 'fw'),
							'plural'    =>__('Blog Categories', 'fw')
						)
					),
					'fw-project-category'=> array(
						'name' => array(
							'singular' => __('Portfolio Category','fw')
						)
					)
				),
				'conditional_tags' => array(
					'is_front_page'     => array(
						'name'          => __('Home Page', 'fw'),
						'order_option'  => 3
					),
					'is_search'         => array(
						'name'          => __('Search Page', 'fw'),
						'order_option'  => 2
					),
					'is_404'            => array(
						'name'          => __('404 Page', 'fw'),
						'order_option'  => 4
					),
					'is_author'         => array(
						'name'          => __('Author Page', 'fw'),
						'order_option'  => 1
					),
					'is_archive'        => array(
						'name'          => __('Archive Page','fw'),
						'order_option'  => 5
					)
				)
			)
		);
	}

	public static $allowed_colors = array( 'blue', 'yellow', 'green', 'red' );

	public function __construct()
	{
		$user_config  = fw()->extensions->get('sidebars')->get_config();
		$this->config['sidebar_positions']    = fw_akg('sidebar_positions', $user_config, array() );
		$this->config['dynamic_sidebar_args'] = fw_akg('dynamic_sidebar_args', $user_config, array() );
		$this->config = array_merge($this->config, $this->get_config_defaults());

	}

	public function get_dynamic_sidebar_args()
	{
		return fw_akg('dynamic_sidebar_args', $this->config, array());
	}

	/**
	 * Collect data of choices for grouped pages tab
	 */
	public function get_grouped_labels()
	{
		$result = array();
		$result[self::DEFAULT_SLUG] = __('All Pages','fw');

		{
			$result['posts'] = array(
				'attr'    => array('label' => __('Pages', 'fw')),
				'choices' => array()
			);

			$post_types = $this->get_post_types();
			foreach($post_types as $post_type) {
				$result['posts']['choices'] = array_merge(
					$result['posts']['choices'],
					$this->get_label_grouped('post_types', $post_type)
				);
			}
		}

		{
			$result['taxonomies'] = array(
				'attr'    => array('label' => __('Categories', 'fw')),
				'choices' => array()
			);

			$taxonomies = $this->get_taxonomies();
			foreach($taxonomies as $taxonomy) {
				$result['taxonomies']['choices'] = array_merge(
					$result['taxonomies']['choices'],
					$this->get_label_grouped('taxonomies',$taxonomy)
				);
			}
		}

		// array_merge conditional_tags from config
		{
			$result['conditional-tags'] = array(
				'attr'    => array('label' => __('Others', 'fw')),
				'choices' => array()
			);

			$result['conditional-tags']['choices'] = array_merge(
				$result['conditional-tags']['choices'],
				$this->get_conditional_tags_labels(true)
			);
		}

		return $result;
	}

	private function conditional_tag_cmp($a, $b){
		if ($a['order_option'] < $b['order_option']){
			return -1;
		} elseif ($a['order_option'] > $b['order_option']) {
			return 1;
		}
		return 0;
	}

	private function get_taxonomies(){
		return apply_filters( 'fw_ext_sidebars_taxonomies',
			get_taxonomies(array(
				'public' => true
			))
		);
	}

	private function get_post_types() {
		return apply_filters( 'fw_ext_sidebars_post_types',
			get_post_types(array(
				'public' => true
			))
		);
	}

	/**
	 * Get conditional_tags from config
	 */
	public function get_conditional_tags_labels($with_ordering = false)
	{
		$cts = fw_akg('select_options/conditional_tags', $this->config, array());

		if ($with_ordering) {
			uasort($cts, array($this, 'conditional_tag_cmp'));
		}

		if (empty($cts))
			return array();

		$result = array();
		foreach($cts as $sub_type => $ct) {
			// ct_is_home => Home page
			if ($ct !== false)
				$result[self::CONDITIONAL_TAGS_PREFIX . '_' . $sub_type] = empty($ct['name']) ? ucfirst($sub_type) : ucfirst($ct['name']);
		}

		return $result;
	}

	/**
	 * Collect data of choices for specific pages tab
	 */
	public function get_specific_labels()
	{
		{
			$result['posts'] = array(
				'attr'    => array('label' => __('Page', 'fw')),
				'choices' => array()
			);

			$post_types = $this->get_post_types();
			foreach($post_types as $post_type) {
				$result['posts']['choices'] = array_merge(
					$result['posts']['choices'],
					$this->get_label_singular('post_types',$post_type)
				);
			}
		}

		{
			$result['taxonomies'] = array(
				'attr'    => array('label' => __('Category', 'fw')),
				'choices' => array()
			);

			$taxonomies = $this->get_taxonomies();
			foreach($taxonomies as $taxonomy) {
				$result['taxonomies']['choices'] = array_merge(
					$result['taxonomies']['choices'],
					$this->get_label_singular('taxonomies',$taxonomy)
				);
			}
		}

		return $result;
	}

	/**
	 * Get singular name by type and sub_type (e.g. type = post_types / sub_type =  post)
	 */
	public function get_label_singular($type, $sub_type)
	{
		$result = array();
		$type_key = $this->get_prefix_by_type($type);
		$default = ucfirst($this->get_name_from_db($sub_type, $type_key,true));

		if ( $this->is_enabled_select_option($type, $sub_type) )
			$result[$type_key . '_' . $sub_type] = isset($this->config['select_options'][$type][$sub_type]['name']['singular']) ? ucfirst($this->config['select_options'][$type][$sub_type]['name']['singular']) : $default;

		return $result;
	}

	/**
	 * Get plural name by type and sub_type (e.g. type = post_types / sub_type =  post)
	 */
	public function get_label_grouped($type, $sub_type)
	{
		$result = array();
		$prefix = $this->get_prefix_by_type($type);

		if ( $type === $this->get_type_by_prefix(self::DEFAULT_PREFIX) and $sub_type === self::DEFAULT_SUB_TYPE )
			return array(self::DEFAULT_SLUG => __('All Pages', 'fw'));

		if($type === 'post_types' or $type === 'taxonomies')
			$default_label = $this->get_name_from_db($sub_type, $prefix, false);
		else
			$default_label = fw_akg('select_options/' . $type . '/' . $sub_type . '/name', $this->config, $type . '_' . $sub_type);

		$default_label = ucfirst($default_label);

		if ( $this->is_enabled_select_option($type, $sub_type) )
			$result[$prefix . '_' . $sub_type] =  isset($this->config['select_options'][$type][$sub_type]['name']['plural']) ? ucfirst($this->config['select_options'][$type][$sub_type]['name']['plural']) : $default_label;

		return $result;
	}

	/**
	 * Retrieve info about registered post_types or taxonomies
	 */
	protected function get_name_from_db($slug, $type_key, $is_singular)
	{
		$result = $slug;
		$get_object = ($type_key === self::POST_TYPES_PREFIX ? 'get_post_type_object' : 'get_taxonomy' );
		$obj = call_user_func_array($get_object, array($slug));
		if($obj) {
			if($is_singular) {
				//singular name
				$result = $obj->labels->singular_name;
			} else {
				//plural name
				$result = $obj->labels->name;
			}
		}
		return $result;
	}

	public function get_prefix_by_type($type) //static
	{
		return array_search($type, self::$config_keys);
	}

	public function get_type_by_prefix($prefix) //static
	{
		return isset(self::$config_keys[$prefix]) ? self::$config_keys[$prefix] : null;
	}

	public function parse_prefix($val) //static
	{
		return substr($val, 0, 2);
	}

	public function parse_sub_type($val) //static
	{
		return substr($val, 3, strlen($val)-3);
	}

	static function get_images_path()
	{
		return fw_get_template_customizations_directory_uri('/extensions/sidebars/static/images/');
	}

	/**
	 * Get valid sidebar positions from config
	 */
	public function get_sidebar_positions()
	{
		$url = self::get_images_path();

		$sidebars_positions = fw_akg('sidebar_positions', $this->config, array());
		foreach($sidebars_positions as $position_key => $position)
		{
			$icon_url = fw_akg('icon_url', $position);
			if (empty($icon_url)) {
				fw_aku($position_key, $sidebars_positions);
				continue;
			}

			$sidebars_positions[$position_key]['icon_url'] = $url . $icon_url;
		}
		return $sidebars_positions;
	}

	/**
	 * Get valid sidebar positions from config
	 */
	public function get_allowed_color_by_position($sidebar_position)
	{
		$colorsCnt = fw_akg('sidebar_positions/' . $sidebar_position . '/' . _FW_Extension_Sidebars_Config::SIDEBARS_NR_KEY, $this->config);

		if (empty($colorsCnt))
			return array();

		return array_slice(self::$allowed_colors, 0 , $colorsCnt);
	}

	/**
	 * Check position exists in config
	 */
	public function has_position($position)
	{
		return (isset($this->config['sidebar_positions'][$position]) and is_array($this->config['sidebar_positions'][$position]));
	}

	public function get_conditional_tags()
	{
		return fw_akg('select_options/conditional_tags', $this->config, array());
	}

	/**
	 * Check if option is not disabled in config
	 */
	public function is_enabled_select_option($type, $sub_type)
	{
		if (self::DEFAULT_SUB_TYPE === $sub_type and $this->get_type_by_prefix(self::DEFAULT_PREFIX) === $type)
			return true;

		if (isset($this->config['select_options'][$type][$sub_type]) and $this->config['select_options'][$type][$sub_type] === false)
			return false;
		return true;
	}
}
