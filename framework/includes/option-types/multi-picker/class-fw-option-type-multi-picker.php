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
		$uri = fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type());

		wp_enqueue_style(
			'fw-option-type' . $this->get_type(),
			$uri . '/static/css/multi-picker.css',
			array(),
			fw()->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-option-type' . $this->get_type(),
			$uri . '/static/js/multi-picker.js',
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

		/**
		 * Leave only select choice options to be rendered in the browser
		 * the rest move to attr[data-options-template] to be rendered on choice change.
		 * This should improve page loading speed.
		 */
		{
			{
				reset($option['picker']);
				$picker_key   = key($option['picker']);
				$picker_type  = $option['picker'][$picker_key]['type'];
				$picker       = $option['picker'][$picker_key];
				$picker_value = fw()->backend->option_type($picker_type)->get_value_from_input(
					$picker,
					isset($data['value'][$picker_key]) ? $data['value'][$picker_key] : null
				);
			}

			$skip_first = true;
			foreach ($options_array as $group_id => &$group) {
				if ($skip_first) {
					// first is picker
					$skip_first = false;
					continue;
				}

				if ($group_id === $id .'-'. $picker_value) {
					// skip selected choice options
					continue;
				}

				$options_array[$group_id]['attr']['data-options-template'] = fw()->backend->render_options(
					$options_array[$group_id]['options'], $data['value'], array(
					'id_prefix' => $data['id_prefix'] . $id . '-',
					'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
				));
				$options_array[$group_id]['options'] = array();
			}
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
			trigger_error(
				sprintf(__('No \'picker\' key set for multi-picker option: %s', 'fw'), $id),
				E_USER_ERROR
			);
		}

		{
			reset($option['picker']);
			$picker_key             = key($option['picker']);
			$picker                 = $option['picker'][$picker_key];
			$picker_type            = $picker['type'];
		}

		$supported_picker_types = array('select', 'short-select', 'radio', 'image-picker', 'switch',
			'color-palette' // fixme: this is a temporary hardcode for a ThemeFuse theme option-type, think a way to allow other option-types here
		);
		if (!in_array($picker_type, $supported_picker_types)) {
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
			case 'short-select':
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
		$show_borders = '';

		if (
			1 === count($picker_choices)
			&&
			isset($option['hide_picker'])
			&&
			true === $option['hide_picker']
		) {
			$hide_picker = 'fw-hidden';
		}

		if (
			isset($option['show_borders'])
			&&
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
						$key => array(
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
				'attr'    => array('class' => $show_borders .' '. $hide_picker .' picker-group picker-type-'. $picker_type),
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
		{
			reset($option['picker']);
			$picker_key  = key($option['picker']);
			$picker_type = $option['picker'][$picker_key]['type'];
			$picker      = $option['picker'][$picker_key];
		}

		$value = array();

		if (is_null($input_value) && isset($option['value'][$picker_key])) {
			$value[$picker_key] = $option['value'][$picker_key];
		} else {
			$value[$picker_key] = fw()->backend->option_type($picker_type)->get_value_from_input(
				$picker,
				isset($input_value[$picker_key]) ? $input_value[$picker_key] : null
			);
		}

		// choices
		switch($picker_type) {
			case 'switch':
				$choices = array_intersect_key($option['choices'], array(
					$picker['left-choice']['value']  => array(),
					$picker['right-choice']['value'] => array()
				));
				break;
			case 'select':
			case 'short-select':
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

		foreach ($choices as $choice_id => $choice_options) {
			if (is_null($input_value) && isset($option['value'][$choice_id])) {
				$value[$choice_id] = $option['value'][$choice_id];
			} else {
				foreach (fw_extract_only_options($choice_options) as $choice_option_id => $choice_option) {
					$value[$choice_id][$choice_option_id] = fw()->backend->option_type($choice_option['type'])->get_value_from_input(
						$choice_option,
						isset($input_value[$choice_id][$choice_option_id]) ? $input_value[$choice_id][$choice_option_id] : null
					);
				}
			}
		}

		return $value;
	}
}
FW_Option_Type::register('FW_Option_Type_Multi_Picker');
