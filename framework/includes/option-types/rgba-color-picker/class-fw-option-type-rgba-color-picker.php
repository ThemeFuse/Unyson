<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * RGBA Color Picker
 */
class FW_Option_Type_Rgba_Color_Picker extends FW_Option_Type {
	/**
	 * @internal
	 */
	public function _get_backend_width_type() {
		return 'auto';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/css/styles.css' ),
			array(),
			fw()->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/js/scripts.js' ),
			array( 'fw-events', 'iris' ),
			fw()->manifest->get_version(),
			true
		);

		wp_localize_script(
			'fw-option-' . $this->get_type(),
			'_fw_option_type_' . str_replace( '-', '_', $this->get_type() ) . '_localized',
			array( 'l10n' => array( 'reset_to_default' => esc_html__( 'Reset', 'fw' ) ) )
		);
	}

	public function get_type() {
		return 'rgba-color-picker';
	}

	/**
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 *
	 * @return string
	 */
	protected function _render( $id, $option, $data ) {
		$option['attr']['value'] = $data['value'];
		$option['attr']['data-default'] = $option['value'];

		$option['attr']['data-palettes'] = ! empty( $option['palettes'] ) && is_array( $option['palettes'] ) ? json_encode( $option['palettes'] ) : '';

		return '<input type="text" ' . fw_attr_to_html( $option['attr'] ) . ' data-alpha="true">';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		if (
			is_null($input_value)
			||
			(
				// do not use `!is_null()` allow empty values https://github.com/ThemeFuse/Unyson/issues/2025
				!empty($input_value)
				&&
				!(
					preg_match( '/^#([a-f0-9]{3}){1,2}$/i', $input_value )
					||
					preg_match( '/^rgba\( *([01]?\d\d?|2[0-4]\d|25[0-5]) *\, *([01]?\d\d?|2[0-4]\d|25[0-5]) *\, *([01]?\d\d?|2[0-4]\d|25[0-5]) *\, *(1|0|0?.\d+) *\)$/', $input_value )
				)
			)
		) {
			return (string)$option['value'];
		} else {
			return (string)$input_value;
		}
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => '',
			'palettes'=> true
		);
	}
}