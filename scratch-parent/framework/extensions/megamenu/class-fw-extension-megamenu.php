<?php if (!defined('FW')) die('Forbidden');

class FW_Extension_Megamenu extends FW_Extension
{
	public function render_str($rel, $param = array())
	{
		return $this->render_view($rel, $param);
	}

	public function render($rel, $param = array())
	{
		echo $this->render_view($rel, $param);
	}

	public function show_icon()
	{
		return !in_array('icon', (array) get_user_option('manage' . 'nav-menus' . 'columnshidden'));
	}





	/**
	 * @internal
	 */
	public function _init()
	{
		if (is_admin()) {
			$this->add_admin_actions();
			$this->add_admin_filters();
		}
		else {
			$this->add_theme_actions();
			$this->add_theme_filters();
		}
	}

	protected function add_admin_actions()
	{
		add_action('admin_enqueue_scripts', array($this, '_admin_action_admin_enqueue_scripts'));
		add_action('wp_update_nav_menu_item', array($this, '_admin_action_wp_update_nav_menu_item'), 10, 3);
	}

	protected function add_admin_filters()
	{
		add_filter('wp_edit_nav_menu_walker', array($this, '_admin_filter_wp_edit_nav_menu_walker'));
		add_filter('manage_nav-menus_columns', array($this, '_admin_filter_manage_nav_menus_columns'), 20);
	}

	protected function add_theme_actions()
	{
		add_action('wp_enqueue_scripts', array($this, '_theme_action_wp_enqueue_scripts'));
	}

	protected function add_theme_filters()
	{
		add_filter('wp_page_menu_args', array($this, '_theme_filter_wp_page_menu_args'));
		add_filter('wp_nav_menu_args', array($this, '_theme_filter_wp_nav_menu_args'));
		add_filter('wp_nav_menu_objects', array($this, '_theme_filter_wp_nav_menu_objects'), 10, 2);
		add_filter('nav_menu_link_attributes', array($this, '_theme_filter_nav_menu_link_attributes'), 10, 3);
		add_filter('walker_nav_menu_start_el', array($this, '_theme_filter_walker_nav_menu_start_el'), 10, 4);
	}

	/**
	 * @internal
	 */
	public function _admin_action_admin_enqueue_scripts($hook)
	{
		if ($hook == 'nav-menus.php') {

			wp_enqueue_media();
			wp_enqueue_style(
				"fw-ext-{$this->get_name()}-admin",
				$this->get_declared_URI('/static/css/admin.css'),
				array(),
				fw()->manifest->get_version()
			);
			wp_enqueue_script(
				"fw-ext-{$this->get_name()}-admin",
				$this->get_declared_URI('/static/js/admin.js'),
				array('fw'),
				fw()->manifest->get_version()
			);

			// Enqueue all the necessary files for Icon dialog
			$options = array(
				'hello' => array(
					'type' => 'icon',
					'label' => __('Select Icon', 'fw'),
				),
			);
			fw()->backend->enqueue_options_static($options);

		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_wp_update_nav_menu_item($menu_id, $menu_item_db_id, $args)
	{
		$flags = array('enabled', 'title-off', 'new-row');

		$meta = request_mega_menu_meta($menu_item_db_id);
		foreach ($flags as $flag) {
			$meta[$flag] = isset($meta[$flag]);
		}

		update_mega_menu_meta($menu_item_db_id, $meta);
	}

	/**
	 * @internal
	 */
	public function _admin_filter_wp_edit_nav_menu_walker()
	{
		return 'FW_Admin_Menu_Walker';
	}

	/**
	 * @internal
	 */
	public function _admin_filter_manage_nav_menus_columns($columns)
	{
		$columns['icon'] = __('Icon', 'fw');
		return $columns;
	}

	/**
	 * @internal
	 */
	public function _theme_action_wp_enqueue_scripts()
	{
		wp_enqueue_style('fw-font-awesome');
	}

	/**
	 * Just for removing FW_Theme_Menu_Walker set in the previous
	 * filter when fallback menu is in action.
	 *
	 * @internal
	 */
	public function _theme_filter_wp_page_menu_args($args)
	{
		if ($args['walker'] instanceof FW_Theme_Menu_Walker) {
			$args['walker'] = '';
		}
		return $args;
	}

	/**
	 * @internal
	 */
	public function _theme_filter_wp_nav_menu_args($args)
	{
		// nav-menu-template.php L271
		// $args['menu'] = ...

		// nav-menu-template.php L363
		// $args['menu_id'] = 'xxx-menu-id';
		// $args['menu_class'] = 'xxx-menu-class';

		// nav-menu-template.php L311
		// $args['container'] = 'xxx-container'; // should be in apply_filters('wp_nav_menu_container_allowedtags')
		// $args['container_id'] = 'xxx-container-id';
		// $args['container_class'] = 'xxx-container-class';

		// nav-menu-template.php L151
		// $args['before'] = 'xxx-before';
		// $args['after'] = 'xxx-after';
		// $args['link_before'] = 'xxx-link-before';
		// $args['link_after'] = 'xxx-link-after';

		// nav-menu-template.php L405
		// $args['items_wrap'] = '<ul id="%1$s" class="%2$s">%3$s</ul>';

		$args['walker'] = new FW_Theme_Menu_Walker();
		return $args;
	}

	/**
	 * @internal
	 */
	public function _theme_filter_wp_nav_menu_objects($sorted_menu_items, $args)
	{
		// <li id="menu-item-1234" class="menu-item menu-item-type-post_type ... mega-menu">
		//     ....
		// </li>

		$mega_menu = array();
		foreach ($sorted_menu_items as $item) {
			if ($item->menu_item_parent == 0 && get_mega_menu_meta($item, 'enabled')) {
				$mega_menu[$item->ID] = true;
			}
		}

		foreach ($sorted_menu_items as $item) {
			if (isset($mega_menu[$item->ID])) {
				$item->classes[] = 'menu-item-has-mega-menu';
			}
			if (isset($mega_menu[$item->menu_item_parent])) {
				$item->classes[] = 'mega-menu-col';
			}
			if (get_mega_menu_meta($item, 'icon')) {
				$item->classes[] = 'menu-item-has-icon';
			}
		}

		return $sorted_menu_items;
	}

	/**
	 * @internal
	 *
	 * nav-menu-template.php L141
	 * Walker_Nav_Menu::start_el
	 */
	public function _theme_filter_nav_menu_link_attributes($attr, $item, $args)
	{
		// item_output = {{before}}<a {{ attr }}>{{ link_before }}{% the_title %}{{ link_after }}</a>{{ after }}

		return $attr;
	}

	/**
	 * @internal
	 *
	 * nav-menu-template.php L174
	 * Walker_Nav_Menu::start_el
	 */
	public function _theme_filter_walker_nav_menu_start_el($item_output, $item, $depth, $args)
	{
		// <li>
		//     {{ item_output }}
		//     <p>{{ item.description }}</p>
		//     <div class="mega-menu">
		//         <ul class="sub-menu"></ul>
		//     </div>
		// </li>

		if ($depth > 0 && get_mega_menu_meta($item, 'title-off')) {
			$item_output = '';
		}

		if ($depth > 0 && $a = trim($item->description)) {
			$item_output .= '<p>' . esc_html($a) . '</p>';
		}

		return $item_output;
	}
}
