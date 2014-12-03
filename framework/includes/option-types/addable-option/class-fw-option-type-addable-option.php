<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Addable_Option extends FW_Option_Type
{
	public function get_type()
	{
		return 'addable-option';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value'  => array(),
			'option' => array(
				'type' => 'text',
			)
		);
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

		wp_enqueue_script(
			'fw-option-'. $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/js/scripts.js'),
			array('fw-events', 'jquery-ui-sortable'),
			fw()->manifest->get_version(),
			true
		);

		fw()->backend->option_type($option['option']['type'])->enqueue_static();

		return true;
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		return fw_render_view(fw_get_framework_directory('/includes/option-types/'. $this->get_type() .'/view.php'), array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data,
			'move_img_src' => fw_get_framework_directory_uri('/static/img/sort-vertically.png'),
		));
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!is_array($input_value)) {
			return $option['value'];
		}

		$option_type = fw()->backend->option_type($option['option']['type']);

		$value = array();

		foreach ($input_value as $option_input_value) {
			$value[] = $option_type->get_value_from_input(
				$option['option'],
				$option_input_value
			);
		}

		return $value;
	}
}
FW_Option_Type::register('FW_Option_Type_Addable_Option');
