<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Addable_Popup extends FW_Option_Type
{
	public function _get_backend_width_type()
	{
		return 'fixed';
	}

	/**
	 * Generate option's html from option array
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 * @return string HTML
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		unset($option['attr']['name'], $option['attr']['value']);

		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/css/styles.css'),
			array('fw'),
			fw()->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/' . $this->get_type() . '.js'),
			array('underscore', 'fw-events', 'jquery-ui-sortable', 'fw'),
			fw()->manifest->get_version(),
			true
		);

		{
			$transformed_options = $this->transform_options($option['popup-options']);
			$localize_var_name = 'fw_option_type_' . str_replace('-', '_', $this->get_type()) . '_localize_' .
				// this way prevents undefined variable errors when addable popup is used inside another addable popup
				md5(json_encode($transformed_options));

			wp_localize_script(
				'fw-option-' . $this->get_type(),
				$localize_var_name,
				array(
					'title' => empty($option['popup-title']) ? $option['label'] : $option['popup-title'],
					'options' => $this->transform_options($option['popup-options']),
					'template' => $option['template']
				)
			);

			$option['attr']['data-localize-var-name'] = $localize_var_name;
		}

		fw()->backend->render_options($option['popup-options']); // This makes sure that the option's static is enqueued

		$sortable_image = fw_get_framework_directory_uri('/static/img/sort-vertically.png');

		return fw_render_view(fw_get_framework_directory('/includes/option-types/' . $this->get_type() . '/views/view.php'), compact('id', 'option', 'data', 'sortable_image'));
	}

	/*
	 * Puts each option into a separate array
	 * to keep it's order inside the modal dialog
	 */
	private function transform_options($options)
	{
		$new_options = array();
		foreach ($options as $id => $option) {
			$new_options[] = array($id => $option);
		}
		return $new_options;
	}

	/**
	 * Option's unique type, used in option array in 'type' key
	 * @return string
	 */
	public function get_type()
	{
		return 'addable-popup';
	}

	/**
	 * Extract correct value for $option['value'] from input array
	 * If input value is empty, will be returned $option['value']
	 * @param array $option
	 * @param array|string|null $input_value
	 * @return string|array|int|bool Correct value
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!is_array($input_value)) {
			return $option['value'];
		}

		$values = array_map('json_decode', $input_value, array_fill(0, count($input_value), true));

		return $values;
	}

	/**
	 * Default option array
	 *
	 * This makes possible an option array to have required only one parameter: array('type' => '...')
	 * Other parameters are merged with array returned from this method
	 *
	 * @return array
	 *
	 * array(
	 *     'value' => '',
	 *     ...
	 * )
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array(),
			'popup-options' => array(
				'default' => array('type' => 'text'),
			),
			'template' => '',
			'popup-title' => null,
		);
	}

}

FW_Option_Type::register('FW_Option_Type_Addable_Popup');
