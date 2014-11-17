<?php if (!defined('FW')) die('Forbidden');

/**
 * Rows with options
 */
class FW_Option_Type_Addable_Box extends FW_Option_Type
{
	public function get_type()
	{
		return 'addable-box';
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

		fw()->backend->enqueue_options_static($option['box-options']);

		return true;
	}

	/*
	 * Puts each option into a separate array
	 * to keep their order inside the modal dialog
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
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _render($id, $option, $data)
	{
		if (empty($data['value']) || !is_array($data['value'])) {
			$data['value'] = array();
		}

		/** Prepare controls */
		{
			$controls = array_merge(
				array(
					'delete' => '<small class="dashicons dashicons-no-alt" title="'. esc_attr__('Remove', 'fw') .'"></small>'
				),
				$option['box-controls']
			);

			// move 'delete' control to end
			{
				if (isset($controls['delete'])) {
					$_delete = $controls['delete'];
					unset($controls['delete']);
					$controls['delete'] = $_delete;
					unset($_delete);
				}
			}
		}

		// Use only groups and options
		{
			$collected = array();
			fw_collect_first_level_options($collected, $option['box-options']);
			$box_options =& $collected['groups_and_options'];
			unset($collected);
		}

		$option['attr']['data-for-js'] = base64_encode(json_encode(array(
			'options'     => $this->transform_options($box_options),
			'template'    => $option['template'],
		)));

		return fw_render_view(fw_get_framework_directory('/includes/option-types/'. $this->get_type() .'/view.php'), array(
			'id'          => $id,
			'option'      => $option,
			'data'        => $data,
			'controls'    => $controls,
			'box_options' => $box_options,
		));
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!is_array($input_value)) {
			return $option['value'];
		}

		$option['limit'] = intval($option['limit']);

		$value = array();

		$box_options = fw_extract_only_options($option['box-options']);

		foreach ($input_value as &$list_item_value) {
			$current_value = array();

			foreach ($box_options as $id => $input_option) {
				$current_value[$id] = fw()->backend->option_type($input_option['type'])->get_value_from_input(
					$input_option,
					isset($list_item_value[$id]) ? $list_item_value[$id] : null
				);
			}

			$value[] = $current_value;

			if ($option['limit'] && count($value) === $option['limit']) {
				break;
			}
		}

		return $value;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array(),
			'box-controls' => array(),
			'box-options' => array(),
			'limit' => 0,
			'template' => '',
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Addable_Box');
