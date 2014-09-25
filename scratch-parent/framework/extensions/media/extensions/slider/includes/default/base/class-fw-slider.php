<?php if (!defined('FW')) die('Forbidden');

class FW_Slider extends FW_Extension
{
	/**
	 * @internal
	 */
	public function _init()
	{
	}

	public function get_slider_type()
	{
		return array(
			'label' => $this->get_name(),
			'small' => array(
				'height' => 100,
				'src' => $this->get_declared_uri('/static/images/thumb.jpg'),
			),
			'large' => array(
				'height' => 208,
				'src' => $this->get_declared_uri('/static/images/preview.jpg')
			)
		);
	}

	public function get_multimedia_types()
	{
		return $this->get_config('multimedia_types');
	}

	public function get_population_methods()
	{
		$population_methods = fw()->extensions->get('population-method')->get_population_methods($this->get_multimedia_types());
		$config_population_methods = $this->get_config('population_methods');
		$final = is_null($config_population_methods) ? $population_methods : array_intersect_key($population_methods, array_flip($config_population_methods));
		return $final;

	}

	public function get_population_method($type){
		$population_methods = $this->get_population_methods();
		return isset($population_methods[$type]) ? $population_methods[$type] : array();
	}

	private function get_frontend_data($post_id)
	{
		return fw()->extensions->get('population-method')->get_frontend_data($post_id);
	}

	private function list_files($path, $ext){
		$suffix = '.'. trim($ext, '.');

		if ($glob = glob($path .'/*'. $suffix)) {
			return array_map('basename', $glob, array_fill_keys($glob, $suffix));
		} else {
			return array();
		}


	}

	private function add_static()
	{
		if ($js_path = $this->locate_path('/static/js')) {
			foreach($this->list_files($js_path, 'js') as $js){
				wp_enqueue_script(
					'fw-ext-'. $this->get_name() .'-'. $js,
					$this->locate_js_URI($js),
					array(),
					fw()->manifest->get_version()
				);
			}
		}

		if ($js_path = $this->locate_path('/static/css')) {
			foreach($this->list_files($js_path, 'css') as $css){
				wp_enqueue_style(
					'fw-ext-'. $this->get_name() .'-'. $css,
					$this->locate_css_URI($css),
					array(),
					fw()->manifest->get_version()
				);
			}
		}
	}

	public function render_slider($post_id, $dimensions)
	{
		$this->add_static();
		$data = $this->get_frontend_data($post_id);
		return $this->render_view($this->get_name(), compact('data', 'dimensions'));
	}

	public function get_slider_options()
	{
		return  $this->get_options('options');
	}

	public function get_population_method_options($population_method)
	{
		return $this->get_options($population_method);
	}
}
