<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Icon extends FW_Option_Type
{
	public function get_type()
	{
		return 'icon';
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'full';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'fw-option-type-'. $this->get_type() .'-if',
			fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/css/styles.css'),
			array('fw-font-awesome'),
			fw()->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-option-type-'. $this->get_type() .'-dialog',
			fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/js/scripts.js'),
			array('jquery', 'fw-events'),
			fw()->manifest->get_version()
		);
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['value'] = (string)$data['value'];
		unset($option['attr']['value']); // be sure to remove value from attributes

		return fw_render_view(dirname(__FILE__) . '/view.php', compact('id', 'option', 'data'));
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (is_null($input_value)) {
			$input_value = $option['value'];
		}

		return (string)$input_value;
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
FW_Option_Type::register('FW_Option_Type_Icon');
