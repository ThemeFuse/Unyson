<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Wp_Editor extends FW_Option_Type {
	// prevent useless calls of wp_enqueue_*()
	private static $enqueued = false;

	public function get_type() {
		return 'wp-editor';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => '',
			'size' => 'small', // small, large
			'editor_height' => 400,

			/**
			 * Also available
			 * https://github.com/WordPress/WordPress/blob/4.4.2/wp-includes/class-wp-editor.php#L80-L94
			 */
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

		{
			unset( $option['attr']['name'], $option['attr']['value'] );

			$option['attr']['data-size'] = $option['size'];
		}

		echo '<div '. fw_attr_to_html($option['attr']) .' >';

		wp_editor( $settings['value'], $settings['id'], $settings['settings'] );

		echo '</div>';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		if (!self::$enqueued) {
			/**
			 * The below styles usually are included directly in html when wp_editor() is called
			 * but since we call it (below) wrapped in ob_start()...ob_end_clean() the html is not printed.
			 * So included the styles manually.
			 */
			{
				wp_enqueue_style(
					/**
					 * https://github.com/WordPress/WordPress/blob/4.4.2/wp-includes/script-loader.php#L731
					 * without prefix it won't enqueue
					 */
					'fw-option-type-' . $this->get_type() .'-dashicons',
					includes_url('css/dashicons.min.css'),
					array(),
					fw()->manifest->get_version()
				);
				wp_enqueue_style(
					/**
					 * https://github.com/WordPress/WordPress/blob/4.4.2/wp-includes/script-loader.php#L737
					 * without prefix it won't enqueue
					 */
					'fw-option-type-' . $this->get_type() .'-editor-buttons',
					includes_url('/css/editor.min.css'),
					array('dashicons'),
					fw()->manifest->get_version()
				);
			}

			$uri = fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static');

			wp_enqueue_script(
				'fw-option-type-' . $this->get_type(),
				$uri . '/scripts.js',
				array('jquery', 'fw-events', 'editor', 'fw'),
				fw()->manifest->get_version(),
				true
			);

			wp_enqueue_style(
				'fw-option-type-' . $this->get_type(),
				$uri . '/styles.css',
				array('dashicons', 'editor-buttons'),
				fw()->manifest->get_version()
			);

			self::$enqueued = true;
		}

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

			/**
			 * This must be unique for option
			 * it will be in editor html and in javascript tinyMCEPreInit.qtInit[ {$id} ]
			 */
			$id = 'fw_wp_editor_'. md5( $id .'|'. json_encode($_option) );

			unset($_option);
		}

		{
			$settings = array();

			foreach ( // https://github.com/WordPress/WordPress/blob/4.4.2/wp-includes/class-wp-editor.php#L80-L94
				array(
					'wpautop',
					'media_buttons',
					'default_editor',
					'drag_drop_upload',
					'textarea_name',
					'textarea_rows',
					'tabindex',
					'tabfocus_elements',
					'editor_css',
					'editor_class',
					'teeny',
					'dfw',
					'_content_editor_dfw',
					'tinymce',
					'quicktags',
				) as $key
			) {
				if (isset($option[$key])) {
					$settings[$key] = $option[$key];
				}
			}

			$settings['editor_height'] = (int) $option['editor_height'];
			$settings['textarea_name'] = $option['attr']['name'];
		}

		return array(
			'id' => $id,
			'settings' => $settings,
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
