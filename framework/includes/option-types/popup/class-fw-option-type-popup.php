<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Unyson option type that allows to group option in a popup window
 *
 * Class FW_Option_Type_Popup
 */
class FW_Option_Type_Popup extends FW_Option_Type {
	public function _get_backend_width_type() {
		return 'fixed';
	}

	protected function _get_data_for_js($id, $option, $data = array()) {
		return false;
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		static $enqueue = true;

		if ($enqueue) {
			wp_enqueue_style(
				'fw-option-' . $this->get_type(),
				fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/css/styles.css' ),
				array( 'fw' )
			);

			wp_enqueue_script(
				'fw-option-' . $this->get_type(),
				fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/js/' . $this->get_type() . '.js' ),
				array( 'underscore', 'fw-events', 'jquery-ui-sortable', 'fw' ),
				false,
				true
			);

			$enqueue = false;
		}

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

		$option['attr']['data-for-js'] = json_encode( array(
			'title'   => ( isset( $option['popup-title'] ) ) ? $option['popup-title'] : ( string ) $option['label'],
			'options' => $this->transform_options( $option['popup-options'] ),
			'button'  => $option['button'],
			'size'    => $option['size'],
			'custom-events' => $option['custom-events']
		) );

		if ( ! empty( $data['value'] ) ) {
			if ( is_array( $data['value'] ) ) {
				$data['value'] = json_encode( $data['value'] );
			}
		} else {
			$data['value'] = '';
		}

		$sortable_image = fw_get_framework_directory_uri( '/static/img/sort-vertically.png' );

		return fw_render_view( fw_get_framework_directory( '/includes/option-types/' . $this->get_type() . '/views/view.php' ),
			compact( 'id', 'option', 'data', 'sortable_image' ) );
	}

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
			if ( is_int( $id ) ) {
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
				$new_options[] = array( $id => $option );
			}
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
		if ( empty( $option['popup-options'] ) ) {
			return array();
		}

		if (is_null($input_value)) {
			$input_value = $option['value'];
		} else {
			if (is_array($input_value)) {
				// Don't decode if we have already an array
			} else {
				$input_value = json_decode($input_value, true);
			}
		}

		/**
		 * Move each option value in option array default values
		 * because popup <input> contains options db values got from fw.OptionsModal
		 * which can't be used as $input_value in second parameter of fw_get_options_values_from_input()
		 */
		$popup_options = array();
		foreach (fw_extract_only_options($option['popup-options']) as $popup_option_id => $popup_option) {
			if (isset($input_value[$popup_option_id])) {
				$popup_option['value'] = $input_value[$popup_option_id];
			}
			$popup_options[ $popup_option_id ] = $popup_option;
		}

		return fw_get_options_values_from_input($popup_options, array());
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
			/*
			 * Popup button text
			 */
			'button'        => __( 'Edit', 'fw' ),
			/*
			 * Title text that will appear in popup header
			 */
			'popup-title'   => null,
			/*
			 * Array of options that you need to add in the popup
			 */
			'popup-options' => array(),

			/*
			 * Popup size
			 */
			'size' => 'medium',

			/*
			 * Array of default values for the popup options
			 */
			'value'         => array(),

			'custom-events' => array(
				'open' => false,
				'close' => false,
				'render' => false
			)
		);
	}

}
