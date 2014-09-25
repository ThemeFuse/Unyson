<?php if (!defined('FW')) die('Forbidden');

/** Sub extensions will extend this class */
require dirname(__FILE__) .'/includes/extends/population-method-interface.php';

class FW_Extension_Population_Method extends FW_Extension
{
	/**
	 * @internal
	 */
	public function _init()
	{
		if (is_admin()) {
			add_action('admin_enqueue_scripts', array($this, '_admin_action_enqueue_static'));
		}
	}

	/**
	 * @internal
	 */
	public function _admin_action_enqueue_static()
	{
		$screen = get_current_screen();
		if ($screen->id === fw()->extensions->get('slider')->get_post_type()) {
			wp_enqueue_style('fw-selectize');
			wp_enqueue_script(
				'fw-population-method-categories',
				$this->get_declared_URI('/static/js/'. $this->get_name() .'.js'),
				array('fw-selectize'),
				fw()->manifest->get_version()
			);
		}
	}

	public function get_population_methods($media_types)
	{
		$collector = array();
		foreach ($this->get_children() as $instance) {
			$intersection = array_intersect($media_types, $instance->get_multimedia_types());

			if (!empty($intersection)) {
				$collector = array_merge($collector, $instance->get_population_method());
			}
		}

		return $collector;
	}

	public function get_population_options($population_method, $multimedia_types = array(), $options = array())
	{
		$population_method_instance = $this->get_child('population-method-' . $population_method);

		if (empty($population_method)) {
			FW_Flash_Messages::add(
				'fw-ext-'. $this->get_name() .'-wrong-method',
				sprintf(__('Specified population method does not exists: %s', 'fw'), $population_method),
				'error'
			);

			return array();
		}

		return $population_method_instance->get_population_options($multimedia_types, $options);
	}

	public function get_population_method($post_id)
	{
		$selected = fw_get_db_post_option($post_id, 'slider/selected');
		$population_method = fw_get_db_post_option($post_id, 'slider/'.$selected.'/population-method');

		return $this->get_child('population-method-' . $population_method)->get_population_method();
	}

	public function get_number_of_images($post_id)
	{
		$selected = fw_get_db_post_option($post_id, 'slider/selected');
		$population_method = fw_get_db_post_option($post_id, 'slider/'.$selected.'/population-method');

		return $this->get_child('population-method-' . $population_method)->get_number_of_images($post_id);
	}

	public function get_frontend_data($post_id)
	{
		$selected = fw_get_db_post_option($post_id, 'slider/selected');
		$population_method = fw_get_db_post_option($post_id, 'slider/'.$selected.'/population-method');

		return $this->get_child('population-method-' . $population_method)->get_frontend_data($post_id);
	}

}