<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Style extends FW_Option_Type {

	private static $settings;

	private static $extension;

	/**
	 * @internal
	 */
	public static function _init() {

		self::$settings = array(
			'typography_tags' => array(
				'h1' => 'Titles (H1)',
				'h2' => 'Subtitles (H2)',
				'h3' => 'Subtitles (H3)',
				'h4' => 'Subtitles (H4)',
				'h5' => 'Subtitles (H5)',
				'h6' => 'Subtitles (H6)',
				'p'  => 'Body (p)'
			),
			'links'           => array(
				'links'       => 'Links',
				'links_hover' => 'Links Hover'
			),
			'default_values'  => array(
				'typography' => array(
					'h1' => array(
						'size'   => 32,
						'family' => 'Arial',
						'style'  => '400',
						'color'  => '#000000'
					),
					'h2' => array(
						'size'   => 24,
						'family' => 'Arial',
						'style'  => '400',
						'color'  => '#000000'
					),
					'h3' => array(
						'size'   => 18,
						'family' => 'Arial',
						'style'  => '400',
						'color'  => '#000000'
					),
					'h4' => array(
						'size'   => 16,
						'family' => 'Arial',
						'style'  => '400',
						'color'  => '#000000'
					),
					'h5' => array(
						'size'   => 13,
						'family' => 'Arial',
						'style'  => '400',
						'color'  => '#000000'
					),
					'h6' => array(
						'size'   => 11,
						'family' => 'Arial',
						'style'  => '400',
						'color'  => '#000000'
					),
					'p'  => array(
						'size'   => 13,
						'family' => 'Arial',
						'style'  => '400',
						'color'  => '#000000'
					),
				),
				'links'      => array(
					'links'       => '#0000ff',
					'links_hover' => '#ff0000',
				),
				'background' => array(
					'background-color' => array(
						'primary'   => '#ffffff',
						'secondary' => '#ffffff',
					)
				)
			)
		);

		$ext = fw()->extensions->get( 'styling' );

		self::$extension = array(
			'path' => $ext->get_declared_path(),
			'URI'  => $ext->get_declared_URI()
		);
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type() {
		return 'full';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value'  => array(),
			'blocks' => array()
		);
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		$version = fw()->extensions->get('styling')->manifest->get_version();

		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			self::$extension['URI'] . '/includes/option-types/' . $this->get_type() . '/static/css/styles.css' ,
			array('fw-jscrollpane'),
			$version
		);
		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			self::$extension['URI'] . '/includes/option-types/' . $this->get_type() . '/static/js/scripts.js',
			array('jquery', 'underscore', 'fw-jscrollpane'),
			$version
		);
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$option = $this->prepare_options( $option );

		if ( ! isset( $option['preview'] ) || $option['preview'] !== false ) {
			$option['attr']['data-preview'] = 'yes';
		}

		return fw_render_view( self::$extension['path'] . '/includes/option-types/' . $this->get_type() . '/views/main.php', array(
			'id'        => $id,
			'option'    => $option,
			'data'      => $data,
			'settings'  => self::$settings,
			'extension' => self::$extension,
		) );
	}

	public function get_type() {
		return 'style';
	}

	private function prepare_options( $option ) {

		if ( empty( $option['blocks'] ) ) {
			$option['blocks'] = array();
		}

		if ( empty( $option['value'] ) || ! is_array( $option['value'] ) ) {
			if ( ! empty( $option['predefined'] ) && is_array( $option['predefined'] ) ) {
				reset( $option['predefined'] );
				$first_key       = key( $option['predefined'] );
				$option['value'] = ( ! empty( $option['predefined'][ $first_key ]['blocks'] ) && is_array( $option['predefined'][ $first_key ]['blocks'] ) ) ? $option['predefined'][ $first_key ]['blocks'] : array();
			} else {
				$option['value'] = array();
			}
		}

		foreach ( $option['blocks'] as $block_id => $block_settings ) {
			//Typography
			foreach ( array_intersect( array_values( $block_settings['elements'] ), array_keys( self::$settings['typography_tags'] ) ) as $element ) {
				$option['value'][ $block_id ][ $element ] = array_merge(
					self::$settings['default_values']['typography'][ $element ],
					( ! empty( $option['value'][ $block_id ][ $element ] ) && is_array( $option['value'][ $block_id ][ $element ] ) ) ? $option['value'][ $block_id ][ $element ] : array()
				);
			}
			//Links
			foreach ( array_intersect( array_values( $block_settings['elements'] ), array_keys( self::$settings['links'] ) ) as $element ) {
				$option['value'][ $block_id ][ $element ] = ( ! empty( $option['value'][ $block_id ][ $element ] ) && preg_match( '/^#[a-f0-9]{6}$/i', $option['value'][ $block_id ][ $element ] ) ) ? $option['value'][ $block_id ][ $element ] : self::$settings['default_values']['links'][ $element ];
			}
			if ( in_array( 'background', $block_settings['elements'] ) ) {
				$option['value'][ $block_id ]['background']['background-color']['primary']   = ( ! empty( $option['value'][ $block_id ]['background']['background-color']['primary'] ) && preg_match( '/^#[a-f0-9]{6}$/i', $option['value'][ $block_id ]['background']['background-color']['primary'] ) ) ? $option['value'][ $block_id ]['background']['background-color']['primary'] : self::$settings['default_values']['background']['background-color']['primary'];
				$option['value'][ $block_id ]['background']['background-color']['secondary'] = ( ! empty( $option['value'][ $block_id ]['background']['background-color']['secondary'] ) && preg_match( '/^#[a-f0-9]{6}$/i', $option['value'][ $block_id ]['background']['background-color']['secondary'] ) ) ? $option['value'][ $block_id ]['background']['background-color']['secondary'] : self::$settings['default_values']['background']['background-color']['secondary'];
				$option['value'][ $block_id ]['background']['background-image']['value']     = ( ! empty( $option['value'][ $block_id ]['background']['background-image']['value'] ) ) ? $option['value'][ $block_id ]['background']['background-image']['value'] : null;
				$option['value'][ $block_id ]['background']['background-image']['choices']   = ( ! empty( $option['value'][ $block_id ]['background']['background-image']['choices'] ) ) ? (array) $option['value'][ $block_id ]['background']['background-image']['choices'] : array();
			}
		}

		return $option;
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {

		if (!is_array($input_value)) {
			$input_value = $option['value'];
		}

		$saved               = array();
		$saved['predefined'] = ( ! empty( $input_value['predefined'] ) ) ? $input_value['predefined'] : '';

		$option = $this->prepare_options( $option );

		foreach ( $input_value as $block_id => $block_settings ) {

			if ( ! is_array( $block_settings ) ) {
				unset( $input_value[ $block_id ] );
				continue;
			}

			foreach ( $block_settings as $tag => $tag_settings ) {

				if ( in_array( $tag, array_keys( self::$settings['typography_tags'] ) ) ) { //Typography
					$tag_settings = fw()->backend->option_type( 'typography' )->get_value_from_input( array(
						'value' => $option['value'][ $block_id ][ $tag ]
					), $tag_settings );
				} elseif ( in_array( $tag, array_keys( self::$settings['links'] ) ) ) { //Links
					$tag_settings = fw()->backend->option_type( 'color-picker' )->get_value_from_input( array(
						'value' => $option['value'][ $block_id ][ $tag ]
					), $tag_settings );
				} elseif ( $tag === 'background' && ! empty( $tag_settings ) ) { //Background
					$tag_settings['background-color'] ['primary'] = fw()->backend->option_type( 'color-picker' )->get_value_from_input( array(
						'value' => $option['value'][ $block_id ]['background']['background-color']['primary']
					), $tag_settings['background-color']['primary'] );

					$tag_settings['background-image'] = fw()->backend->option_type( 'background-image' )->get_value_from_input(
						json_decode( $tag_settings['background-image']['data'], true ),
						$tag_settings['background-image']
					);

					$block_settings[ $tag ] = $tag_settings;
				} elseif ( ( $tag === 'before' || $tag === 'after' ) && ! empty( $option['blocks'][ $block_id ][ $tag ] ) ) {
					$tag_settings = fw_get_options_values_from_input(
						$option['blocks'][ $block_id ][ $tag ],
						$input_value[ $block_id ][ $tag ]
					);
				}

				$block_settings[ $tag ] = $tag_settings;
			}

			$input_value[ $block_id ] = $block_settings;

		}

		$saved['blocks'] = $input_value;

		return $saved;
	}
}

FW_Option_Type::register('FW_Option_Type_Style');
