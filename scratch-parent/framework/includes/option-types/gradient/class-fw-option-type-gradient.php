<?php if (!defined('FW')) {
	die('Forbidden');
}

/**
 * Background Color
 */
class FW_Option_Type_Gradient extends FW_Option_Type
{
	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
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
			array(),
			fw()->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/scripts.js'),
			array('jquery'),
			fw()->manifest->get_version()
		);
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$output = fw_render_view(fw_get_framework_directory('/includes/option-types/' . $this->get_type() . '/view.php'), array(
			'id' => $id,
			'option' => $option,
			'data' => $data
		));

		return $output;
	}

	public function get_type()
	{
		return 'gradient';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (is_array($input_value)) {
			if (!isset($input_value['primary']) || !preg_match('/^#[a-f0-9]{6}$/i', $input_value['primary'])) {
				$input_value['primary'] = $option['value']['primary'];
			}

			if (!isset($input_value['secondary']) || !preg_match('/^#[a-f0-9]{6}$/i', $input_value['secondary'])) {
				$input_value['secondary'] = (isset($option['value']['secondary'])) ? $option['value']['secondary'] : false;
			}
		} else {
			$input_value = $option['value'];
		}

		return $input_value;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array(
				'primary'   => '#FF0000',
				'secondary' => '#0000FF',
			)
		);
	}
}

FW_Option_Type::register('FW_Option_Type_Gradient');
