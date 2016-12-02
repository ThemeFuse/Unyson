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
		static $enqueue = true;

		if ($enqueue) {
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

			$enqueue = false;
		}

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
			if (is_int($id)) {
				/**
				 * this happens when in options array are loaded external options using fw()->theme->get_options()
				 * and the array looks like this
				 * array(
				 *    'hello' => array('type' => 'text'), // this has string key
				 *    array('hi' => array('type' => 'text')) // this has int key
				 * )
				 */
				$new_options[] = $option;
			} else {
				$new_options[] = array($id => $option);
			}
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

		{
			$box_options = array();

			fw_collect_options( $box_options, $option['box-options'], array(
				'limit_option_types' => false,
				'limit_container_types' => array('group'), // Use only groups and options
				'limit_level' => 1,
			) );
		}

		$option['attr']['data-for-js'] = json_encode(array(
			'options'     => $this->transform_options($box_options),
			'template'    => $option['template'],
		));

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
		if (is_null($input_value)) {
			$value = $option['value'];
		} elseif (is_array($input_value)) {
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
		} else {
			$value = array();
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
			/**
			 * Buttons on box head ( near delete button X )
			 *
			 * On control click will be triggered a custom event 'fw:option-type:addable-box:control:click'
			 * on wrapper div (the one that has `.fw-option-type-addable-box` class)
			 * data about control will be in event data
			 */
			'box-controls' => array(
				// 'control_id' => '<small class="dashicons dashicons-..." title="Some action"></small>'
			),
			'box-options' => array(),
			/**
			 * Limit boxes that can be added
			 */
			'limit' => 0,
			/**
			 * Box title backbonejs template
			 * All box options are available as variables {{- box_option_id }}
			 *
			 * Note: Ids with - (instead of underscore _ ) will throw errors
			 * because 'box-option-id' can't be converted to variable name
			 *
			 * Example: 'Hello {{- box_option_id }}'
			 */
			'template' => '',
			'add-button-text' => __('Add', 'fw'),
			/**
			 * Makes the boxes sortable
			 *
			 * You can disable this in case the boxes order doesn't matter,
			 * to not confuse the user that if changing the order will affect something.
			 */
			'sortable' => true,
			/**
			 * Width type. Supported types:
			 * - fixed
			 * - full
			 */
			'width' => 'fixed',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}
}
