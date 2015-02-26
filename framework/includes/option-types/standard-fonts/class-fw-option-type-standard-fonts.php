<?php if (!defined('FW')) {
	die('Forbidden');
}


/**
 * Standard fonts
 */
 
class FW_Option_Type_Standard_Fonts extends FW_Option_Type
{
	/*
	 * Allowed fonts
	 */
	private $fonts;

	/**
	 * Returns fonts
	 * @return array
	 */
	public function get_fonts()
	{
		if($this->fonts === null) {
			$this->fonts = array(
				'standard' => array(
					"Arial" => "Arial",
					"Verdana" => "Verdana",
					"Trebuchet" => "Trebuchet",
					"Georgia" => "Georgia",
					"Times New Roman" => "Times New Roman",
					"Tahoma" => "Tahoma",
					"Palatino" => "Palatino",
					"Helvetica" => "Helvetica",
					"Calibri" => "Calibri",
					"Myriad Pro" => "Myriad Pro",
					"Lucida" => "Lucida",
					"Arial Black" => "Arial Black",
					"Gill Sans" => "Gill Sans",
					"Geneva" => "Geneva",
					"Impact" => "Impact",
					"Serif" => "Serif"
				)
			);
		}

		return $this->fonts;
	}
	
	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/'. $this->get_type() . '/static/css/styles.css'),
			array('fw-selectize'),
			fw()->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/'. $this->get_type() . '/static/js/scripts.js'),
			array('jquery', 'underscore', 'fw', 'fw-selectize'),
			fw()->manifest->get_version()
		);
	}

	
	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		return fw_render_view(fw_get_framework_directory('/includes/option-types/' . $this->get_type() . '/view.php'), array(
			'id' => $id,
			'option' => $option,
			'data' => $data,
			'fonts' => $this->get_fonts()
		));
	}

	public function get_type()
	{
		return 'standard-fonts';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!is_array($input_value)) {
			return $option['value'];
		}

		$components = (isset($option['components']) && is_array($option['components'])) ? $option['components'] : array();
		$components = array_merge(array(
			'family' => true,
			'style' => true,
			'weight' => true,
		), $components);

		$values = array(
			'family' => ($components['family']) ? (isset($input_value['family'])) ? $input_value['family'] : $option['value']['family'] : false,
			'style' => ($components['style']) ? (isset($input_value['style'])) ? $input_value['style'] : $option['value']['style'] : false,
			'weight' => ($components['weight']) ? (isset($input_value['weight'])) ? $input_value['weight'] : $option['value']['weight'] : false,
		);

		return $values;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array(
				'family' => 'Helvetica',
				'style'  => 'regular',
				'weight'  => '400'
			),
			'components' => array(
				'family' => true,
				'style' => true,
				'weight'  => true
			)
		);
	}

	public function _get_backend_width_type()
	{
		return 'auto';
	}
}

FW_Option_Type::register('FW_Option_Type_Standard_Fonts');
