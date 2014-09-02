<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Switch_Style_Panel_Css_Generator {

	private static $tags = array(
		'typography' => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p' ),
		'links'      => array( 'links', 'links_hover' ),
	);

	private static $initialized = false;

	private static $google_fonts;

	private static $remote_fonts = array();

	static public function get_css( $blocks, $style_options ) {

		if ( ! self::$initialized ) {
			self::$google_fonts = fw_get_google_fonts();
			self::$initialized  = true;
		}

		return array(
			'css'          => self::generate_css( $blocks, $style_options['blocks'] ),
			'google_fonts' => self::get_remote_fonts()
		);
	}

	private static function generate_css( $blocks, $style_options ) {

		$css = '';

		foreach ( $blocks as $block_id => $block_settings ) {
			if ( empty( $block_settings['css_selector'] ) || empty($style_options[ $block_id ])) {
				continue;
			}
			$css_selectors  = (array) $block_settings['css_selector'];
			$block_elements = (array) $block_settings['elements'];

			foreach ( $block_elements as $element ) {
				if ( in_array( $element, self::$tags['typography'] ) ) {
					$css .= self::generate_typography_css( $css_selectors, $element, $style_options[ $block_id ][ $element ] );
				} elseif ( in_array( $element, self::$tags['links'] ) ) {
					$css .= self::generate_links_css( $css_selectors, $element, $style_options[ $block_id ][ $element ] );
				} elseif ( $element === 'background' ) {
					$css .= self::generate_background_css( $css_selectors, $style_options[ $block_id ][ $element ] );
				}
			}
		}

		return $css;
	}

	private static function generate_typography_css( $selectors, $tag, $options ) {
		$css = '';

		$current_family = $options['family'];

		$current_style = $options['style'];

		if ( $current_style === 'regular' ) {
			$current_style = '400';
		}
		if ( $current_style == 'italic' ) {
			$current_style = '400italic';
		}

		$font_style  = ( strpos( $current_style, 'italic' ) ) ? 'font-style: italic;' : '';
		$font_weight = 'font-weight: ' . intval( $current_style ) . ';';

		self::insert_remote_font( $current_family, $current_style );

		foreach ( $selectors as $selector ) {
			$css .= $selector . ' ' . $tag . "{
						color: " . $options['color'] . ";
						font-size: " . $options['size'] . "px;
						font-family: '" . $current_family . "';"
			        . $font_style . "" . $font_weight . "

					 }\n";
		}

		return $css;
	}

	private static function insert_remote_font( $font, $style ) {

		if ( ! isset( self::$google_fonts[ $font ] ) ) {
			return false;
		}

		if ( ! isset( self::$remote_fonts[ $font ] ) ) {
			self::$remote_fonts[ $font ] = array();
		}

		if ( ! in_array( $style, self::$remote_fonts[ $font ] ) ) {
			self::$remote_fonts[ $font ][] = $style;
		}

		return true;
	}

	private static function generate_links_css( $selectors, $tag, $color ) {
		$css = '';
		if ( ! is_string( $color ) || ! self::is_valid_hex_color( $color ) ) {
			return $css;
		}
		$tag = ( $tag === 'links' ) ? 'a' : 'a:hover';
		foreach ( $selectors as $selector ) {
			$css .= $selector . ' ' . $tag . '{color: ' . $color . '}';
		}

		return $css;
	}

	private static function is_valid_hex_color( $color ) {
		return preg_match( '/^#[a-f0-9]{6}$/i', $color );
	}

	private static function generate_background_css( $selectors, $options ) {
		$css        = '';
		$bgImageCss = '';
		if ( ! empty( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-image'] ) ) {
			$bgImageCss .= $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-image'];
			if ( ! empty( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-repeat'] ) ) {
				$bgImageCss .= ' ' . $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-repeat'];
			}
			$bgImageCss .= ', ';
		}
		$fallback = 'background-color: ' . $options['background-color']['primary'] . ';';
		$fallback .= ( ! empty( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-image'] ) ) ? 'background-image: ' . $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-image'] . ';' : '';
		$fallback .= ( ! empty( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-repeat'] ) ) ? 'background-repeat: ' . $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-repeat'] . ';' : '';

		foreach ( $selectors as $selector ) {
			//Gradient http://css-tricks.com/examples/CSS3Gradient/
			$css .= $selector . ' ' . '{
						 /* fallback  */
						 ' . $fallback . '
						  /* Safari 4-5, Chrome 1-9 */
						  background: ' . $bgImageCss . '-webkit-gradient(linear, left top, right top, from(' . $options['background-color']['primary'] . '), to(' . $options['background-color']['secondary'] . '));

						  /* Safari 5.1, Chrome 10+ */
						  background: ' . $bgImageCss . '-webkit-linear-gradient(left, ' . $options['background-color']['primary'] . ', ' . $options['background-color']['secondary'] . ');

						  /* Firefox 3.6+ */
						  background: ' . $bgImageCss . '-moz-linear-gradient(left, ' . $options['background-color']['primary'] . ', ' . $options['background-color']['secondary'] . ');

						  /* IE 10 */
						  background: ' . $bgImageCss . '-ms-linear-gradient(left, ' . $options['background-color']['primary'] . ', ' . $options['background-color']['secondary'] . ');

						  /* Opera 11.10+ */
						  background: ' . $bgImageCss . '-o-linear-gradient(left, ' . $options['background-color']['primary'] . ', ' . $options['background-color']['secondary'] . ');
					}';

			//Background-image
			unset( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-image'] );
			unset( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-repeat'] );
			if ( sizeof( $options['background-image']['choices'][ $options['background-image']['value'] ]['css'] ) ) {
				$css .= $selector . ' ' . '{';
				foreach ( $options['background-image']['choices'][ $options['background-image']['value'] ]['css'] as $css_property => $css_value ) {
					$css .= $css_property . ': ' . $css_value . ';';
				}
				$css .= '}';
			}
		}


		return $css;
	}

	private static function get_remote_fonts() {
		if ( ! sizeof( self::$remote_fonts ) ) {
			return '';
		}

		$html = "<link href='http://fonts.googleapis.com/css?family=";

		foreach ( self::$remote_fonts as $font => $styles ) {
			$html .= str_replace( ' ', '+', $font ) . ':' . implode( ',', $styles ) . '|';
		}

		$html = substr( $html, 0, - 1 );
		$html .= "' rel='stylesheet' type='text/css'>";

		return $html;
	}
}