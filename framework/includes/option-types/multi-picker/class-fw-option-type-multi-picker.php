<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Multi_Picker extends FW_Option_Type
{
	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'full';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'picker' => array(
				'default' => array(
					'type' => 'select',
					'choices' => array()
				)
			),
			'choices' => array(),
			'hide_picker' => false,
			/**
			 * Display separators between options
			 */
			'show_borders' => false,
			'value' => array()
		);
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		$css_path = fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/css/');
		$js_path  = fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/');

		wp_enqueue_style(
			'fw-option-type' . $this->get_type(),
			$css_path . 'multi-picker.css',
			array(),
			fw()->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-option-type' . $this->get_type(),
			$js_path . 'multi-picker.js',
			array('jquery', 'fw-events'),
			fw()->manifest->get_version(),
			true
		);

		fw()->backend->enqueue_options_static($this->prepare_option($id, $option));

		return true;
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _render($id, $option, $data)
	{
		$options_array = $this->prepare_option($id, $option);
		unset($option['attr']['name'], $option['attr']['value']);

		if ($option['show_borders']) {
			$option['attr']['class'] .= ' fw-option-type-multi-picker-with-borders';
		} else {
			$option['attr']['class'] .= ' fw-option-type-multi-picker-without-borders';
		}

		return '<div ' . fw_attr_to_html($option['attr']) . '>' .
			fw()->backend->render_options($options_array, $data['value'], array(
				'id_prefix' => $data['id_prefix'] . $id . '-',
				'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
			)) .
		'</div>';
	}

	public function get_type()
	{
		return 'multi-picker';
	}

	private function prepare_option($id, $option)
	{
		if (empty($option['picker'])) {
			// TODO: think of text for error when no picker is set
			trigger_error(
				sprintf(__('No \'picker\' key set for multi-picker option: %s', 'fw'), $id),
				E_USER_ERROR
			);
		}

		reset($option['picker']);
		$picker_key             = key($option['picker']);
		$picker                 = $option['picker'][$picker_key];
		$picker_type            = $picker['type'];
		$supported_picker_types = array('select', 'radio', 'image-picker', 'switch');
		if (!in_array($picker_type, $supported_picker_types)) {
			// TODO: think of text for error when incorrect picker type is used
			trigger_error(
				sprintf(
					__('Invalid picker type for multi-picker option %s, only pickers of types %s are supported', 'fw'),
					$id,
					implode(', ', $supported_picker_types)
				),
				E_USER_ERROR
			);
		}

		switch($picker_type) {
			case 'switch':
				$picker_choices = array_intersect_key($option['choices'], array(
					$picker['left-choice']['value']  => array(),
					$picker['right-choice']['value'] => array()
				));
				break;
			case 'select':
				// we need to treat the case with optgroups
				$collected_choices = array();
				foreach ($picker['choices'] as $key => $value) {
					if (is_array($value) && isset($value['choices'])) {
						// we have an optgroup
						$collected_choices = array_merge($collected_choices, $value['choices']);
					} else {
						$collected_choices[$key] = $value;
					}
				}
				$picker_choices = array_intersect_key($option['choices'], $collected_choices);
				break;
			default:
				$picker_choices = array_intersect_key($option['choices'], $picker['choices']);
		}

		$hide_picker = '';
		$show_borders  = '';

		//set default value if nothing isset
//		if (empty($option['controls']['value'])) {
//			reset($groups);
//			$option['controls']['value'] = key($groups);
//		}

		if (
			1 === count($picker_choices)      &&
			isset($option['hide_picker'])   &&
			true === $option['hide_picker']
		) {
			$hide_picker = 'fw-hidden ';
		}

		if (
			isset($option['show_borders']) &&
			true === $option['show_borders']
		) {
			$show_borders = 'fw-show-borders';
		}

		$choices_groups = array();
		foreach ($picker_choices as $key => $set) {
			if (!empty($set)) {
				$choices_groups[$id . '-' . $key] = array(
					'type'    => 'group',
					'attr'    => array('class' => 'choice-group choice-' . $key),
					'options' => array(
						$key    => array(
							'type'          => 'multi',
							'attr'          => array('class' => $show_borders),
							'label'         => false,
							'desc'          => false,
							'inner-options' => $set
						)
					)
				);
			}
		}

		$picker_group = array(
			$id . '-picker' => array(
				'type'    => 'group',
				'desc'    => false,
				'label'   => false,
				'attr'    => array('class' => $show_borders.' '.$hide_picker . ' picker-group picker-type-' . $picker_type),
				'options' => array($picker_key => $picker)
			)
		);

		return array_merge($picker_group, $choices_groups);
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		$value = array();

		// picker
		reset($option['picker']);
		$picker_key  = key($option['picker']);
		$picker_type = $option['picker'][$picker_key]['type'];
		$picker      = $option['picker'][$picker_key];
		$value[$picker_key] = fw()->backend->option_type($picker_type)->get_value_from_input(
			$picker,
			isset($input_value[$picker_key]) ? $input_value[$picker_key] : null
		);

		// choices
		switch($picker_type) {
			case 'switch':
				$choices = array_intersect_key($option['choices'], array(
					$picker['left-choice']['value']  => array(),
					$picker['right-choice']['value'] => array()
				));
				break;
			case 'select':
				// we need to treat the case with optgroups
				$collected_choices = array();
				foreach ($picker['choices'] as $key => $choice_value) {
					if (is_array($choice_value) && isset($choice_value['choices'])) {
						// we have an optgroup
						$collected_choices = array_merge($collected_choices, $choice_value['choices']);
					} else {
						$collected_choices[$key] = $choice_value;
					}
				}
				$choices = array_intersect_key($option['choices'], $collected_choices);
				break;
			default:
				$choices = array_intersect_key($option['choices'], $picker['choices']);
		}

		foreach ($choices as $choice_id => $options) {

			foreach ($options as $option_id => $option) {
				$value[$choice_id][$option_id] = fw()->backend->option_type($option['type'])->get_value_from_input(
					$option,
					isset($input_value[$choice_id][$option_id]) ? $input_value[$choice_id][$option_id] : null
				);
			}

		}

		return $value;
	}
}
FW_Option_Type::register('FW_Option_Type_Multi_Picker');
