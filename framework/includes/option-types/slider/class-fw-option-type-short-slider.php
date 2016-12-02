<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Slider_Short extends FW_Option_Type_Slider {
	public function get_type() {
		return 'short-slider';
	}

	protected function _render( $id, $option, $data ) {
		$option['attr']['class'] .= ' short-slider fw-option-type-slider';

		return parent::_render( $id, $option, $data );
	}
}