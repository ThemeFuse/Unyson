<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Addable_Popup extends FW_Option_Type
{
	public function get_type()
	{
		return 'addable-popup';
	}

	public function _get_backend_width_type()
	{
		return 'fixed';
	}

	protected function _get_data_for_js($id, $option, $data = array()) {
		return false;
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		static $enqueue = true;

		/**
		 * Use hardcoded type because this class is extended and type is changed, but the paths must be the same
		 * Fixes https://github.com/ThemeFuse/Unyson/issues/1769#issuecomment-247054955
		 */
		$option_type = 'addable-popup';

		if ($enqueue) {
			wp_enqueue_style(
				'fw-option-' . $option_type,
				fw_get_framework_directory_uri('/includes/option-types/' . $option_type . '/static/css/styles.css'),
				array('fw'),
				fw()->manifest->get_version()
			);

			wp_enqueue_script(
				'fw-option-' . $option_type,
				fw_get_framework_directory_uri('/includes/option-types/' . $option_type . '/static/js/scripts.js'),
				array('underscore', 'fw-events', 'jquery-ui-sortable', 'fw'),
				fw()->manifest->get_version(),
				true
			);

			$enqueue = false;
		}

		fw()->backend->enqueue_options_static($option['popup-options']);

		return true;
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

		$option['attr']['data-for-js'] =
			/**
			 * Prevent js error when the generated html is used in another option type js template with {{...}}
			 * Do this trick because {{ is not escaped/encoded by fw_htmlspecialchars()
			 * Fixes https://github.com/ThemeFuse/Unyson/issues/1877
			 */
			json_encode(explode('{{',
			json_encode(array(
				'title' => empty($option['popup-title']) ? $option['label'] : $option['popup-title'],
				'options' => $this->transform_options($option['popup-options']),
				'template' => $option['template'],
				'size' => $option['size'],
				'limit' => $option['limit']
			))
		));

		$sortable_image = fw_get_framework_directory_uri('/static/img/sort-vertically.png');

		return fw_render_view(
			fw_get_framework_directory('/includes/option-types/addable-popup/view.php'),
			compact('id', 'option', 'data', 'sortable_image')
		);
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
	 * Extract correct value for $option['value'] from input array
	 * If input value is empty, will be returned $option['value']
	 * @param array $option
	 * @param array|string|null $input_value
	 * @return string|array|int|bool Correct value
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (is_null($input_value)) {
			$values = $option['value'];
		} elseif (is_array($input_value)) {
			$values = array();

			foreach ($input_value as $elem){
				/**
				 * Do JSON deconding only if $elem is not already parsed.
				 * json_decode will throw an error when passing him anything
				 * but a string.
				 */
				if (is_array($elem)) {
					$values[] = $elem;
				} else {
					$values[] = json_decode($elem, true);
				}
			}

			if ( $option['limit'] = intval( $option['limit'] ) ) {
				$values = array_slice( $values, 0, $option['limit'] );
			}
		} else {
			$values = array();
		}

		/**
		 * For e.g. option type 'unique' needs to execute _get_value_from_input() for each option
		 * to prevent duplicate values
		 */
		return apply_filters('fw:option-type:addable-popup:value-from-input', $values, $option);
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
			'limit' => 0,
			'size' => 'small', // small, medium, large
			'add-button-text' => __('Add', 'fw'),
			/**
			 * Makes the items sortable
			 *
			 * You can disable this in case the items order doesn't matter,
			 * to not confuse the user that if changing the order will affect something.
			 */
			'sortable' => true,
		);
	}

}

class FW_Option_Type_Addable_Popup_Full extends FW_Option_Type_Addable_Popup
{
	public function get_type()
	{
		return 'addable-popup-full';
	}

	public function _get_backend_width_type()
	{
		return 'full';
	}

	protected function _render($id, $option, $data)
	{
		// Use styles and scripts from parent option
		$option['attr']['class'] .= ' fw-option-type-addable-popup';

		return parent::_render($id, $option, $data);
	}
}
