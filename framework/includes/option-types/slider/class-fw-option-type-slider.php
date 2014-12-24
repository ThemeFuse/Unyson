<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Slider
 */
class FW_Option_Type_Slider extends FW_Option_Type {

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		{
			wp_enqueue_style(
				'fw-option-' . $this->get_type() . 'ion-range-slider',
				fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/libs/ion-range-slider/ion.rangeSlider.css' ),
				fw()->manifest->get_version()
			);

			wp_enqueue_script(
				'fw-option-' . $this->get_type() . 'ion-range-slider',
				fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/libs/ion-range-slider/ion.rangeSlider.min.js' ),
				array( 'jquery', 'fw-moment' ),
				fw()->manifest->get_version()
			);
		}

		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/css/styles.css' ),
			fw()->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/js/scripts.js' ),
			array( 'jquery', 'underscore', 'fw-option-' . $this->get_type() . 'ion-range-slider' ),
			fw()->manifest->get_version()
		);
	}

	public function get_type() {
		return 'slider';
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		if ( gettype( $option['value'] ) === 'array' ) {
			$option['properties']['type'] = 'double';
			$option['properties']['from'] = ( isset( $data['value']['from'] ) ) ? $data['value']['from'] : $option['value']['from'];
			$option['properties']['to']   = ( isset( $data['value']['to'] ) ) ? $data['value']['to'] : $option['value']['to'];
		} else {
			$option['attr']['data-fw-irs-options']['type'] = 'single';
			$option['properties']['from']                  = ( isset( $data['value'] ) ) ? $data['value'] : $option['value'];
		}
		$option['attr']['data-fw-irs-options'] = ( ! empty( $option['properties'] ) ) ? json_encode( $option['properties'] ) : array();

		return fw_render_view( fw_get_framework_directory( '/includes/option-types/' . $this->get_type() . '/view.php' ), array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data,
			'value'  => $this->get_processed_value( $data['value'] )
		) );
	}

	private function get_processed_value( $data ) {
		if ( is_array( $data ) ) {
			$data = implode( ';', $data );
		}

		return $data;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value'      => 0,
			'properties' => array()
		);
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		$input_values = array_map( 'intval', explode( ';', $input_value ) );
		if ( isset( $option['value'] ) && gettype( $option['value'] ) === 'array' ) {
			$value = array(
				'from' => $input_values[0],
				'to'   => $input_values[1],
			);
		} else {
			$value = $input_values[0];
		}

		return $value;
	}

}

FW_Option_Type::register( 'FW_Option_Type_Slider' );
