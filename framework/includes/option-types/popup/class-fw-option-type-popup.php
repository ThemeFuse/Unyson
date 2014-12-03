<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Popup extends FW_Option_Type {
	public function _get_backend_width_type() {
		return 'fixed';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/css/styles.css'),
			array( 'fw' )
		);

		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/js/' . $this->get_type() . '.js'),
			array( 'underscore', 'fw-events', 'jquery-ui-sortable', 'fw' ),
			false,
			true
		);

		fw()->backend->enqueue_options_static( $option['popup-options'] );

		return true;
	}

	/**
	 * Generate option's html from option array
	 *
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 *
	 * @return string HTML
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		unset( $option['attr']['name'], $option['attr']['value'] );

		$option['attr']['data-for-js'] = json_encode(array(
			'title'   => ( $option['popup-title'] ) ? $option['popup-title'] : $option['label'],
			'options' => $this->transform_options( $option['popup-options'] ),
			'button'  => $option['button']
		));

		if (!empty($data['value'])) {
			if (is_array($data['value'])) {
				$data['value'] = json_encode($data['value']);
			}
		} else {
			$data['value'] = '';
		}

		$sortable_image = fw_get_framework_directory_uri('/static/img/sort-vertically.png');

		return fw_render_view( fw_get_framework_directory('/includes/option-types/' . $this->get_type() . '/views/view.php' ), compact( 'id', 'option', 'data', 'sortable_image' ) );
	}

	/*
		 * Puts each option into a separate array
		 * to keep it's order inside the modal dialog
		 */

	/**
	 * Option's unique type, used in option array in 'type' key
	 * @return string
	 */
	public function get_type() {
		return 'popup';
	}

	private function transform_options( $options ) {
		$new_options = array();
		foreach ( $options as $id => $option ) {
			$new_options[] = array( $id => $option );
		}

		return $new_options;
	}

	/**
	 * Extract correct value for $option['value'] from input array
	 * If input value is empty, will be returned $option['value']
	 *
	 * @param array $option
	 * @param array|string|null $input_value
	 *
	 * @return string|array|int|bool Correct value
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		$values = json_decode( $input_value, true );
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
	protected function _get_defaults() {
		return array(
			'popup-options' => array(),
			'button'    => __( 'Edit', 'fw' ),
			'value' => ''
		);
	}

}

FW_Option_Type::register( 'FW_Option_Type_Popup' );
