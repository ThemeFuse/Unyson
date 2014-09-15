<?php if (!defined('FW')) die('Forbidden');

/**
 * @internal
 */
class _FW_Extension_Sidebars_Model_Sidebar
{
	private  $id = null;
	private  $name;
	private  $description;
	private  $class;
	private  $before_widget;
	private  $after_widget;
	private  $before_title;
	private  $after_title;

	protected  $wp_widget_prefix_option = 'widget_';
	protected  $wp_sidebars_widgets_option = 'sidebars_widgets';
	protected  $split_pattern = '/-(?=[^-]+$)/';

	/**
	 * Fill object with sidebar args
	 */
	public function __construct($sidebar)
	{
		foreach ($sidebar as $key => $value)
		{
			$method = 'set_' . $key;
			if(method_exists($this, $method)) {
				call_user_func_array(array($this, $method), array($value));
			}

		}
	}

	public  function set_class($class = null)
	{
		$this->class = $class;
		return $this;
	}

	public  function set_id($id = null)
	{
		$this->id = $id;
		return $this;
	}

	public function get_class()
	{
		return $this->class;
	}

	public function get_id()
	{
		return $this->id;
	}

	public function set_name($name)
	{
		$this->name  = trim(stripslashes($name));
		return $this;
	}

	public function get_name()
	{
		return $this->name;
	}

	public function set_description($description)
	{
		$this->description = $description;
		return $this;
	}

	public function get_description()
	{
		return $this->description;
	}

	public function set_before_widget($before_widget)
	{
		$this->before_widget = $before_widget;
		return $this;
	}

	public function get_before_widget()
	{
		return $this->before_widget;
	}

	public function set_after_widget($after_widget)
	{
		$this->after_widget = $after_widget;
		return $this;
	}

	public function get_after_widget()
	{
		return $this->after_widget;
	}

	public function set_before_title($before_title)
	{
		$this->before_title = $before_title;
		return $this;
	}

	public function get_before_title()
	{
		return $this->before_title;
	}

	public function set_after_title($after_title)
	{
		$this->after_title = $after_title;
		return $this;
	}

	public function get_after_title()
	{
		return $this->$after_title;
	}


	/**
	 * register sidebar with current object proprieties
	 */
	public function register()
	{
		add_action( 'widgets_init', array($this, '_action_register_sidebar') );
	}

	/**
	 * @internal
	 */
	public function _action_register_sidebar()
	{
		if (!$this->id) {
			return false;
		}

		return register_sidebar($this->to_array());
	}

	public function unregister()
	{
		return unregister_sidebar($this->id);
	}

	/**
	 * Convert current object proprieties to array
	 * @var $sidebar_info 'general' = get only name and id, empty retrive all info
	 */
	public function to_array($sidebar_info = false)
	{
		
		$result = array(
			'id'            => $this->id,
			'name'          => $this->name,
			);
		
		if ($sidebar_info === 'general')
			return $result;

		$result = array_merge($result, array(
			'description'   => $this->description,
			'class'         => $this->class,
			'before_widget' => $this->before_widget,
			'after_widget'  => $this->after_widget,
			'before_title'  => $this->before_title,
			'after_title'   => $this->after_title,
		));

		return $result;
	}

	/**
	 * Remove widgets from DB for current sidebar
	 */
	public function remove_widgets()
	{
		$widgets = get_option($this->wp_sidebars_widgets_option);

		if (isset($widgets[$this->id]) and is_array($widgets[$this->id])) {
			foreach($widgets[$this->id] as $widget) {
				$widget_data = preg_split($this->split_pattern, $widget);
				$widget_slug = $widget_data[0];
				$widget_id = $widget_data[1];
				$db = get_option( $this->wp_widget_prefix_option . $widget_slug);

				if (is_array($db)) {
					unset($db[$widget_id]);
				}

				update_option($this->wp_widget_prefix_option . $widget_slug, $db);
			}
		}
		unset($widgets[$this->id]);
		update_option($this->wp_sidebars_widgets_option, $widgets);
	}
}