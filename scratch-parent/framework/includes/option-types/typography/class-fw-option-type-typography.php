<?php if (!defined('FW')) {
	die('Forbidden');
}

/**
 * Typography
 */
class FW_Option_Type_Typography extends FW_Option_Type
{
	/*
	 * Allowed fonts
	 */
	private static $fonts;

	/**
	 * @internal
	 */
	public static function _init()
	{
		self::$fonts = array(
			'standard' => array(
				"Arial",
				"Verdana",
				"Trebuchet",
				"Georgia",
				"Times New Roman",
				"Tohama",
				"Palatino",
				"Helvetica",
				"Calibri",
				"Myriad Pro",
				"Lucida",
				"Arial Black",
				"Gill Sans",
				"Geneva",
				"Impact",
				"Serif"
			),
			'google' => fw_get_google_fonts()
		);
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/css/styles.css'),
			array('fw-selectize'),
			fw()->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/scripts.js'),
			array('jquery', 'underscore', 'fw', 'fw-selectize'),
			fw()->manifest->get_version()
		);

		wp_localize_script('fw-option-' . $this->get_type(), 'googleFonts', self::$fonts['google']);
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
			'fonts' => self::$fonts
		));
	}

	public function get_type()
	{
		return 'typography';
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
			'size' => true,
			'family' => true,
			'color' => true,
		), $components);

		$values = array(
			'size' => ($components['size']) ? (isset($input_value['size'])) ? intval($input_value['size']) : intval($option['value']['size']) : false,
			'family' => ($components['family']) ? (isset($input_value['family'])) ? $input_value['family'] : $option['value']['family'] : false,
			'style' => ($components['family']) ? (isset($input_value['style'])) ? $input_value['style'] : $option['value']['style'] : false,
			'color' => ($components['color']) ? (isset($input_value['color']) && preg_match('/^#[a-f0-9]{6}$/i', $input_value['color'])) ? $input_value['color'] : $option['value']['color'] : false,
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
				'size'   => 12,
				'family' => 'Arial',
				'style'  => '400',
				'color'  => '#000000'
			)
		);
	}
}

FW_Option_Type::register('FW_Option_Type_Typography');
