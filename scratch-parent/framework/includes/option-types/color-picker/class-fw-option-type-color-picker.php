<?php if (!defined('FW')) die('Forbidden');

/**
 * Color Picker
 */
class FW_Option_Type_Color_Picker extends FW_Option_Type
{
	public function get_type()
	{
		return 'color-picker';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'fw-option-'. $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/css/styles.css'),
			array(),
			fw()->manifest->get_version()
		);

		wp_enqueue_script('wp-color-picker');

		wp_enqueue_script(
			'fw-option-'. $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/js/scripts.js'),
			array('fw-events'),
			fw()->manifest->get_version(),
			true
		);
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['attr']['value']  = (string)$data['value'];
		$option['attr']['class'] .= ' code';
		$option['attr']['size']   = '7';
		$option['attr']['maxlength'] = '7';
		$option['attr']['onclick'] = 'this.select()';

		return '<input type="text" '. fw_attr_to_html($option['attr']) .'>';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!isset($input_value) || !preg_match('/^#[a-f0-9]{3}([a-f0-9]{3})?$/i', $input_value)) {
			$input_value = $option['value'];
		}

		return (string)$input_value;
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => ''
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Color_Picker');
