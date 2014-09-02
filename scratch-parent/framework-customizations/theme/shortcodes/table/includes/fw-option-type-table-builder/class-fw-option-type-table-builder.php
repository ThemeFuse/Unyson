<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Table_Builder extends FW_Option_Type {
	public function get_type() {
		return 'table-builder';
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$table_shortcode = fw()->extensions->get( 'shortcodes' )->get_shortcode( 'table' );
		if ( ! $table_shortcode ) {
			trigger_error(
				__( 'table-builder option type must be inside the table shortcode', 'fw' ),
				E_USER_ERROR
			);
		}

		$static_uri = $table_shortcode->get_uri() . '/includes/fw-option-type-table-builder/static/';
		wp_enqueue_style(
			'fw-option-' . $this->get_type() . '-default',
			$static_uri . 'css/default-styles.css',
			array(),
			fw()->theme->manifest->get_version()
		);
		wp_enqueue_style(
			'fw-option-' . $this->get_type() . '-extended',
			$static_uri . 'css/extended-styles.css',
			array(),
			fw()->theme->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			$static_uri . 'js/scripts.js',
			array( 'jquery', 'fw-events' ),
			fw()->theme->manifest->get_version(),
			true
		);

		if ( ! isset( $data['value'] ) || empty( $data['value'] ) ) {
			$data['value'] = $option['value'];
		}

		$this->replace_with_defaults( $option );
		$views_path = $table_shortcode->get_path() . '/includes/fw-option-type-table-builder/views/';

		return fw_render_view( $views_path . 'view.php', array(
			'id'     => $option['attr']['id'],
			'option' => $option,
			'data'   => $data
		) );
	}

	protected function replace_with_defaults( &$option ) {
		$option['row_options']['attr']['class']     = 'fw-table-builder-row-style';
		$option['columns_options']['attr']['class'] = 'fw-table-builder-col-style';
		$defaults                                   = $this->_get_defaults();
		$option['row_options']['choices']           = $defaults['row_options']['choices'];
		$option['columns_options']['choices']       = $defaults['columns_options']['choices'];
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		if ( ! is_array( $input_value ) ) {
			return $option['value'];
		}

		$this->set_default_values( $input_value, $option );

		if ( isset( $input_value['textarea']['_template_key_row_'] ) ) {
			unset( $input_value['textarea']['_template_key_row_'] );
		}

		if ( isset( $input_value['rows']['_template_key_row_'] ) ) {
			unset( $input_value['rows']['_template_key_row_'] );
		}

		return $input_value;
	}

	private function set_default_values( &$input_value, &$option ) {
		if ( ! isset( $input_value['textarea'] ) || empty( $input_value['textarea'] ) ) {
			$input_value['textarea'] = $option['value']['textarea'];
		}

		if ( ! isset( $input_value['rows'] ) || empty( $input_value['rows'] ) ) {
			$input_value['rows'] = $option['value']['rows'];
		}

		if ( ! isset( $input_value['cols'] ) || empty( $input_value['cols'] ) ) {
			$input_value['cols'] = $option['value']['cols'];
		}
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'row_options'     => array(
				'choices' => array(
					''            => __( 'Default row', 'fw' ),
					'heading-row' => __( 'Heading row', 'fw' ),
					'pricing-row' => __( 'Pricing row', 'fw' ),
					//'button-row'    => __('Button  Row', 'fw')
				)
			),
			'columns_options' => array(
				'choices' => array(
					''              => __( 'Default column', 'fw' ),
					'highlight-col' => __( 'Highlight column', 'fw' ),
					'desc-col'      => __( 'Description column', 'fw' ),
					'center-col'    => __( 'Center text column', 'fw' )
				)
			),
			'value'           => array(
				'cols'     => array( '', '', '' ),
				'rows'     => array( '', '', '' ),
				'textarea' => array( array( '', '', '' ), array( '', '', '' ), array( '', '', '' ) )
			)
		);
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type() {
		return 'full';
	}
}

FW_Option_Type::register( 'FW_Option_Type_Table_Builder' );
