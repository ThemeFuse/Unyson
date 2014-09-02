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
	 */
	protected function _render($id, $option, $data)
	{
		// static
		{
			// adaptive switch
			{
				wp_enqueue_style(
					'fw-option-'. $this->get_type() .'-adaptive-switch',
					FW_URI .'/includes/option-types/'. $this->get_type() .'/static/adaptive-switch/styles.css',
					array(),
					fw()->manifest->get_version()
				);

				wp_enqueue_script(
					'fw-option-'. $this->get_type() .'-adaptive-switch',
					FW_URI .'/includes/option-types/'. $this->get_type() .'/static/adaptive-switch/jquery.adaptive-switch.js',
					array('jquery'),
					fw()->manifest->get_version(),
					true
				);
			}

			wp_enqueue_style(
				'fw-option-'. $this->get_type(),
				FW_URI .'/includes/option-types/'. $this->get_type() .'/static/css/styles.css',
				array('fw-option-'. $this->get_type() .'-adaptive-switch'),
				fw()->manifest->get_version()
			);

			wp_enqueue_script(
				'fw-option-'. $this->get_type(),
				FW_URI .'/includes/option-types/'. $this->get_type() .'/static/js/scripts.js',
				array('fw-events', 'fw-option-'. $this->get_type() .'-adaptive-switch'),
				fw()->manifest->get_version(),
				true
			);
		}

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
		}

		return '<div '. fw_attr_to_html($option['attr']) .'>'.
			'<input type="checkbox" '. fw_attr_to_html($input_attr) .' />'.
		'</div>';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if ($input_value) {
			return $option['right-choice']['value'];
		} else {
			return $option['left-choice']['value'];
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
