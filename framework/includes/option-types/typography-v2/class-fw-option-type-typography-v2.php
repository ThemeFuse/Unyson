<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Typography
 */
class FW_Option_Type_Typography_v2 extends FW_Option_Type {
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

		wp_localize_script(
			'fw-option-' . $this->get_type(),
			'fw_typography_v2_fonts',
			$this->get_fonts()
		);
	}

	public function get_type() {
		return 'typography-v2';
	}

	/**
	 * Returns fonts
	 * @return array
	 */
	public function get_fonts() {
		$cache_key = 'fw_option_type/'. $this->get_type();

		try {
			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$fonts = array(
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
				'google' => json_decode(fw_get_google_fonts_v2(), true)
			);

			FW_Cache::set($cache_key, $fonts);

			return $fonts;
		}
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		return fw_render_view( dirname(__FILE__) . '/view.php', array(
			'typography_v2' => $this,
			'id'            => $id,
			'option'        => $option,
			'data'          => $data,
			'defaults'      => $this->get_defaults()
		) );
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {

		$default = $this->get_defaults();
		$values  = array_merge( $default['value'], $option['value'], is_array($input_value) ? $input_value : array());

		if ( ! empty($values['color']) && ! preg_match( '/^#([a-f0-9]{3}){1,2}$/i', $values['color'] ) ) {
			$values['color'] = isset( $option['value']['color'] ) ? $option['value']['color'] : $default['value']['color'];
		}

		$components = array_merge( $default['components'], $option['components'] );
		foreach ( $components as $component => $enabled ) {
			if ( ! $enabled ) {
				$values[ $component ] = false;
			}
		}

		if ( $values['family'] === false ) {
			$values = array_merge( $values, array(
				'google_font' => false,
				'style'       => false,
				'weight'      => false,
				'subset'      => false,
				'variation'   => false
			) );
		} elseif ( $this->get_google_font( $values['family'] ) ) {
			$values = array_merge( $values, array(
				'google_font' => true,
				'style'       => false,
				'weight'      => false
			) );
		} else {
			$values = array_merge( $values, array(
				'google_font' => false,
				'subset'      => false,
				'variation'   => false

			) );
		}

		return $values;

	}

	public function get_google_font( $font ) {
		$fonts = $this->get_fonts();

		foreach ( $fonts['google']['items'] as $g_font ) {
			if ( $font === $g_font['family'] ) {
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
			),
			'components' => array(
				'family'         => true,
				'size'           => true,
				'line-height'    => true,
				'letter-spacing' => true,
				'color'          => true,
				'weight'         => true,
				'style'          => true,
				'variation'      => true,
			)
		);
	}

}