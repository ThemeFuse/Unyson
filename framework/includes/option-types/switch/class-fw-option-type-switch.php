<?php if (!defined('FW')) die('Forbidden');

/**
 * Rows with options
 */
class FW_Option_Type_Switch extends FW_Option_Type
{
	public function get_type()
	{
		return 'switch';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		// adaptive switch
		{
			wp_enqueue_style(
				'fw-option-'. $this->get_type() .'-adaptive-switch',
				fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/adaptive-switch/styles.css'),
				array(),
				fw()->manifest->get_version()
			);

			wp_enqueue_script(
				'fw-option-'. $this->get_type() .'-adaptive-switch',
				fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/adaptive-switch/jquery.adaptive-switch.js'),
				array('jquery'),
				fw()->manifest->get_version(),
				true
			);
		}

		wp_enqueue_style(
			'fw-option-'. $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/css/styles.css'),
			array('fw-option-'. $this->get_type() .'-adaptive-switch'),
			fw()->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-option-'. $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/'. $this->get_type() .'/static/js/scripts.js'),
			array('fw-events', 'fw-option-'. $this->get_type() .'-adaptive-switch'),
			fw()->manifest->get_version(),
			true
		);
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		{
			$input_attr = array(
				'name' => $option['attr']['name'],
				'id'   => $option['attr']['id'] .'--checkbox',
				'data-switch-left'  => $option['left-choice']['label'],
				'data-switch-right' => $option['right-choice']['label'],
			);

			foreach (array('left', 'right') as $value_type) {
				if (is_bool($option[$value_type .'-choice']['value'])) {
					$input_attr['data-switch-'. $value_type .'-bool-value'] = $option[$value_type. '-choice']['value']
						? 'true'
						: 'false';
				} else {
					$input_attr['data-switch-'. $value_type .'-value'] = $option[$value_type .'-choice']['value'];
				}
			}
		}

		if ($data['value'] === $option['right-choice']['value']) {
			// right choice means checked
			$input_attr['checked'] = 'checked';
		}

		unset(
			$option['attr']['name'],
			$option['attr']['value'],
			$option['attr']['checked'],
			$option['attr']['type']
		);

		return '<div '. fw_attr_to_html($option['attr']) .'>'.
			/**
			 * On submit, a value must be present in the POST for _get_value_from_input() to work properly
			 * If no value is present, then the default $option['value'] will be used
			 */
			'<input type="hidden" value="" '. (empty($input_attr['checked']) ? 'name="'. esc_attr($input_attr['name']) .'"' : '') .' />'.

			'<input type="checkbox" '. fw_attr_to_html($input_attr) .' />'.
		'</div>';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (is_null($input_value)) {
			// input value is not present
			return $option['value'];
		} else {
			if ($input_value) {
				// checked
				return $option['right-choice']['value'];
			} else {
				// unchecked
				return $option['left-choice']['value'];
			}
		}
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
			'value' => false,
			'left-choice' => array(
				'value' => false,
				'label' => __('No', 'fw'),
			),
			'right-choice' => array(
				'value' => true,
				'label' => __('Yes', 'fw'),
			),
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Switch');
