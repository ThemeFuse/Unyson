<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Wp_Editor extends FW_Option_Type {
	public function get_type() {
		return 'wp-editor';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			/**
			 * boolean | array
			 */
			'tinymce'       => true,
			/**
			 * boolean
			 */
			'media_buttons' => true,
			/**
			 * boolean
			 */
			'teeny'         => false,
			/**
			 * boolean
			 */
			'wpautop'       => true,
			/**
			 * string
			 * Additional CSS styling applied for both visual and HTML editors buttons, needs to include <style> tags, can use "scoped"
			 */
			'editor_css'    => '',
			/**
			 * boolean
			 * if smth wrong try change true
			 */
			'reinit'        => false,
			/**
			 * string
			 */
			'value'         => '',
			/**
			 * Set the editor size: small - small box, large - full size
			 * string
			 */
			'size'          => 'small', // small, large
			/**
			 * Set editor type : 'tinymce' or 'html'
			 */
			'editor_type' => wp_default_editor(),
			/**
			 * Set the editor height, must be int
			 */
			'editor_height' => 400
		);
	}

	/**
	 * @internal
	 */
	protected function _init() {}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$settings = $this->get_option_settings($id, $option, $data);

		unset( $option['attr']['name'], $option['attr']['value'] );

		echo '<div '. fw_attr_to_html($option['attr']) .' >';

		wp_editor( $settings['value'], $settings['id'], $settings['settings'] );

		echo '</div>';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		$uri = fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static' );

		wp_enqueue_script( 'quicktags' );
		wp_enqueue_style( 'buttons' );

		wp_enqueue_script(
			'fw-option-type-' . $this->get_type(),
			$uri . '/scripts.js',
			array( 'jquery', 'fw-events', 'editor', 'fw' ),
			fw()->manifest->get_version(),
			true
		);

		wp_enqueue_style(
			'fw-option-type-' . $this->get_type(),
			$uri . '/styles.css',
			array(),
			fw()->manifest->get_version()
		);

		/**
		 * Make editor settings available in javascript tinyMCEPreInit.qtInit[ {$settings['id']} ]
		 */
		{
			$settings = $this->get_option_settings($id, $option, $data);

			ob_start();
			wp_editor( $settings['value'], $settings['id'], $settings['settings'] );
			ob_end_clean();
		}

		return true;
	}

	private function get_option_settings($id, $option, $data) {
		{
			$_option = $option;

			ksort($_option); // keys must be in same order to obtain the same hash

			unset($_option['attr'], $_option['value']);

			$id = 'fw_wp_editor_'. md5( $id .'|'. json_encode($_option) );

			unset($_option);
		}

		return array(
			'id' => $id,
			'settings' => array(
				'teeny'         => $option['teeny'],
				'media_buttons' => $option['media_buttons'],
				'tinymce'       => $option['tinymce'],
				'editor_css'    => $option['editor_css'],
				'editor_height' => (int) $option['editor_height'],
				'textarea_name' => $option['attr']['name'],
			),
			// replace \u00a0 char to &nbsp;
			'value' => str_replace( chr( 194 ) . chr( 160 ), '&nbsp;', (string) $data['value'] )
		);
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		if ( is_null( $input_value ) ) {
			return $option['value'];
		}

		$value = (string) $input_value;

		if ( $option['wpautop'] === true ) {
			$value = preg_replace( "/\n/i", '', wpautop( $value ) );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function _get_backend_width_type() {
		return 'auto';
	}
}

FW_Option_Type::register( 'FW_Option_Type_Wp_Editor' );
