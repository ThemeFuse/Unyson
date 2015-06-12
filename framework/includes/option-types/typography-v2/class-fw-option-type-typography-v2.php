<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Typography
 */
class FW_Option_Type_Typography_v2 extends FW_Option_Type {
	/*
	 * Allowed fonts
	 */
	private $fonts;

	public function _get_backend_width_type() {
		return 'full';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/css/styles.css' ),
			array( 'fw-selectize' ),
			fw()->manifest->get_version()
		);

		fw()->backend->option_type( 'color-picker' )->enqueue_static();

		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/js/scripts.js' ),
			array( 'jquery', 'underscore', 'fw', 'fw-selectize' ),
			fw()->manifest->get_version()
		);

		$fw_typography_v2_fonts = $this->get_fonts();
		wp_localize_script( 'fw-option-' . $this->get_type(), 'fw_typography_v2_fonts', $fw_typography_v2_fonts );
	}

	public function get_type() {
		return 'typography-v2';
	}

	/**
	 * Returns fonts
	 * @return array
	 */
	public function get_fonts() {
		if ( $this->fonts === null ) {
			$this->fonts = array(
				'standard' => apply_filters( 'fw_option_type_typography_v2_standard_fonts', array(
					"Arial",
					"Verdana",
					"Trebuchet",
					"Georgia",
					"Times New Roman",
					"Tahoma",
					"Palatino",
					"Helvetica",
					"Calibri",
					"Myriad Pro",
					"Lucida",
					"Arial Black",
					"Gill Sans",
					"Geneva",
					"Impact",
					"Serif"
				) ),
				'google'   => fw_get_google_fonts_v2()
			);
		}

		return $this->fonts;
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		return fw_render_view( fw_get_framework_directory( '/includes/option-types/' . $this->get_type() . '/view.php' ), array(
			'typography_v2' => $this,
			'id'            => $id,
			'option'        => $option,
			'data'          => $data,
			'fonts'         => $this->get_fonts(),
			'defaults'      => $this->get_defaults()
		) );
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		if ( ! is_array( $input_value ) ) {
			return $option['value'];
		}
		$default = $this->get_defaults();
		$values  = array_merge( $default['value'], $option['value'], $input_value );

		if ( ! preg_match( '/^#[a-f0-9]{6}$/i', $values['color'] ) ) {
			$values = ( isset( $option['value']['color'] ) ) ? $option['value']['color'] : $default['color'];
		}

		if ( $this->get_google_font( $values['family'] ) ) {
			$values['google_font'] = true;
			$values['style']       = false;
			$values['weight']      = false;
		} else {
			$values['google_font'] = false;
			$values['subset']      = false;
			$values['variation']   = false;
		}

		return $values;

	}

	public function get_google_font( $font ) {
		$google_fonts = fw_get_google_fonts_v2();
		$google_fonts = ( false === $google_fonts ) ? array() : json_decode( $google_fonts );
		foreach ( $google_fonts->items as $g_font ) {
			if ( $font === $g_font->family ) {
				return $g_font;
			}
		}

		return false;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => array(
				'google_font'    => false,
				'subset'         => false,
				'variation'      => false,
				'family'         => 'Arial',
				'style'          => 'normal',
				'weight'         => '400',
				'size'           => 12,
				'line-height'    => 15,
				'letter-spacing' => - 1,
				'color'          => '#000000'
			)
		);
	}

}

FW_Option_Type::register( 'FW_Option_Type_Typography_v2' );
