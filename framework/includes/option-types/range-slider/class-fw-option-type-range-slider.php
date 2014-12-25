<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Slider
 * -*==*---
 */
class FW_Option_Type_Range_Slider extends FW_Option_Type {

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
		return 'range-slider';
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$option['properties']['type'] = 'double';
		$option['properties']['from'] = ( isset( $data['value']['from'] ) ) ? $data['value']['from'] : $option['value']['from'];
		$option['properties']['to']   = ( isset( $data['value']['to'] ) ) ? $data['value']['to'] : $option['value']['to'];

		if ($option['properties']['from'] > $option['properties']['to']) {
			$option['properties']['from'] = $option['properties']['to'];
		}

		$option['attr']['data-fw-irs-options'] = json_encode(
			$this->default_properties($option['properties'])
		);

		return fw_render_view( fw_get_framework_directory( '/includes/option-types/' . $this->get_type() . '/view.php' ), array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data,
			'value'  => implode(';', (array)$data['value'])
		) );
	}

	private function default_properties($properties = array()) {
		return array_merge(array(
			'min' => 0,
			'max' => 100,
			'step' => 1,
		), $properties);
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => array(
				'from' => 0,
				'to'   => 0,
			),
			'properties' => $this->default_properties(), // https://github.com/IonDen/ion.rangeSlider#settings
		);
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		$input_values = array_map( 'intval', explode( ';', $input_value ) );

		return array(
			'from' => $input_values[0],
			'to'   => $input_values[1],
		);
	}

}

FW_Option_Type::register( 'FW_Option_Type_Range_Slider' );
