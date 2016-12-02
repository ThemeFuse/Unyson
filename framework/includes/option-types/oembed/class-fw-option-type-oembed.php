<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Oembed extends FW_Option_Type {

	/**
	 * Option's unique type, used in option array in 'type' key
	 * @return string
	 */
	public function get_type() {
		return 'oembed';
	}

	/**
	 * Generate html
	 *
	 * @param string $id
	 * @param array $option Option array merged with _get_defaults()
	 * @param array $data {value => _get_value_from_input(), id_prefix => ..., name_prefix => ...}
	 *
	 * @return string HTML
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {

		$defaults                   = $this->_get_defaults();
		$option['preview']          = array_merge( $defaults['preview'], $option['preview'] );
		$option['attr']             =array_merge($defaults['attr'], $option['attr']);

		return fw_render_view(
			fw_get_framework_directory( '/includes/option-types/' . $this->get_type() . '/view.php' ),
			compact( 'id', 'option', 'data' )
		);
	}

	/**
	 * Extract correct value for $option['value'] from input array
	 * If input value is empty, will be returned $option['value']
	 *
	 * @param array $option Option array merged with _get_defaults()
	 * @param array|string|null $input_value
	 *
	 * @return string|array|int|bool Correct value
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		return (string) ( is_null( $input_value ) ? $option['value'] : $input_value );
	}

	/**
	 * Default option array
	 *
	 * This makes possible an option array to have required only one parameter: array('type' => '...')
	 * Other parameters are merged with the array returned by this method.
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
			'value'   => '',
			'attr' => array(
				'placeholder' => 'https://www.youtube.com'
			),
			'preview' => array(
				'width'      => 428,
				'height'     => 320,
				/**
				 * by default wp_get_embed maintain ratio and return changed width and height values of the iframe,
				 * if you set it to false , the dimensions will be forced to change as in preview.width and preview.height
				 */
				'keep_ratio' => true
			)
		);
	}

	protected function _enqueue_static( $id, $option, $data ) {
		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/css/styles.css' ),
			array( 'fw' )
		);

		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/js/' . $this->get_type() . '.js' ),
			array( 'underscore', 'fw-events', 'fw', 'wp-util' ),
			false,
			true
		);
	}

	public static function _action_get_oembed_response() {

		if ( wp_verify_nonce( FW_Request::POST( '_nonce' ), '_action_get_oembed_response' ) ) {

			$url        = FW_Request::POST( 'url' );
			$width      = FW_Request::POST( 'preview/width' );
			$height     = FW_Request::POST( 'preview/height' );
			$keep_ratio = ( FW_Request::POST( 'preview/keep_ratio' ) === 'true' );

			$iframe = empty( $keep_ratio ) ?
				fw_oembed_get( $url, compact( 'width', 'height' ) ) :
				wp_oembed_get( $url, compact( 'width', 'height' ) );

			wp_send_json_success( array( 'response' => $iframe ) );
		}

		wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
	}
}

add_action(
	'wp_ajax_get_oembed_response',
	array( "FW_Option_Type_Oembed", '_action_get_oembed_response' )
);