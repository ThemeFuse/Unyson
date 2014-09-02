<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Styling_Css_Generator {

	private static $tags = array(
		'typography' => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p' ),
		'links'      => array(
			'links'       => 'a',
			'links_hover' => 'a:hover'
		),
	);

	private static $initialized = false;

	private static $google_fonts;

	private static $remote_fonts = array();

	static public function get_css( $theme_options, $saved_data ) {

		if ( ! self::$initialized ) {
			self::$google_fonts = fw_get_google_fonts();
			self::$initialized  = true;
		}

		//generate css
		$css = '';
		foreach ( $theme_options as $option_name => $option_settings ) {
			if ( $option_settings['type'] !== 'style' ) {
				unset ( $theme_options[ $option_name ] );
				continue;
			}

			$css .= self::generate_option_css( $option_settings['blocks'], $saved_data[$option_name] );
			break;
		}

		if ( ! empty( $css ) ) {
			$css = "<style type='text/css'>\n" . $css . "\n</style>";
			$css .= self::get_remote_fonts() . $css;
		}

		return $css;
	}

	private static function generate_option_css( $blocks, $saved_settings ) {

		$css = '';

		foreach ( $blocks as $block_id => $block_settings ) {
			if ( empty( $block_settings['css_selector'] ) ) {
				continue;
			}
			$css_selectors  = (array) $block_settings['css_selector'];
			$block_elements = (array) $block_settings['elements'];

			//Typography
			$css .= self::generate_typography_css( $css_selectors, $block_elements, $saved_settings['blocks'][ $block_id ] );

			//Links
			$links = array_intersect( $block_elements, array_keys( self::$tags['links'] ) );
			foreach ( $links as $link ) {
				foreach ( $css_selectors as $selector ) {
					$css .= $selector . ' ' . self::$tags['links'][ $link ] . "{ color: " . $saved_settings['blocks'][ $block_id ][ $link ] . ";}\n";
				}
			}

			//Background
			$css .= self::generate_background_css( $css_selectors, $block_elements, $saved_settings['blocks'][ $block_id ] );
		}

		return $css;
	}

	private static function generate_typography_css( $css_selectors, $elements, $settings ) {
		$css = '';

		$typography_tags = array_intersect( (array) $elements, self::$tags['typography'] );
		foreach ( $typography_tags as $tag ) {

			$current_family = $settings[ $tag ]['family'];

			$current_style = $settings[ $tag ]['style'];

			if ( $current_style === 'regular' ) {
				$current_style = '400';
			}
			if ( $current_style == 'italic' ) {
				$current_style = '400italic';
			}

			$font_style  = ( strpos( $current_style, 'italic' ) ) ? 'font-style: italic;' : '';
			$font_weight = 'font-weight: ' . intval( $current_style ) . ';';

			self::insert_remote_font( $current_family, $current_style );

			foreach ( $css_selectors as $selector ) {
				$css .= $selector . ' ' . $tag . "{
							color: " . $settings[ $tag ]['color'] . ";
							font-size: " . $settings[ $tag ]['size'] . "px;
							font-family: '" . $current_family . "';"
				        . $font_style . "" . $font_weight . "

						 }\n";
			}
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

	private static function generate_background_css( $css_selectors, $elements, $settings ) {

		$css = '';
		if ( ! in_array( 'background', $elements ) ) {
			return $css;
		}

		$bgImageCss = '';
		if ( ! empty( $settings['background']['background-image']['data']['css']['background-image'] ) ) {
			$bgImageCss .= $settings['background']['background-image']['data']['css']['background-image'];
			if ( ! empty( $settings['background']['background-image']['data']['css']['background-repeat'] ) ) {
				$bgImageCss .= ' ' . $settings['background']['background-image']['data']['css']['background-repeat'];
			}
			$bgImageCss .= ', ';
		}
		$fallback = 'background-color: ' . $settings['background']['background-color']['primary'] . ';';
		$fallback .= ( ! empty( $settings['background']['background-image']['data']['css']['background-image'] ) ) ? 'background-image: ' . $settings['background']['background-image']['data']['css']['background-image'] . ';' : '';
		$fallback .= ( ! empty( $settings['background']['background-image']['data']['css']['background-repeat'] ) ) ? 'background-repeat: ' . $settings['background']['background-image']['data']['css']['background-repeat'] . ';' : '';

		foreach ( $css_selectors as $selector ) {
			//Gradient http://css-tricks.com/examples/CSS3Gradient/
			$css .= $selector . ' ' . '{
						 /* fallback  */
						 ' . $fallback . '
						  /* Safari 4-5, Chrome 1-9 */
						  background: ' . $bgImageCss . '-webkit-gradient(linear, left top, right top, from(' . $settings['background']['background-color']['primary'] . '), to(' . $settings['background']['background-color']['secondary'] . '));

						  /* Safari 5.1, Chrome 10+ */
						  background: ' . $bgImageCss . '-webkit-linear-gradient(left, ' . $settings['background']['background-color']['primary'] . ', ' . $settings['background']['background-color']['secondary'] . ');

						  /* Firefox 3.6+ */
						  background: ' . $bgImageCss . '-moz-linear-gradient(left, ' . $settings['background']['background-color']['primary'] . ', ' . $settings['background']['background-color']['secondary'] . ');

						  /* IE 10 */
						  background: ' . $bgImageCss . '-ms-linear-gradient(left, ' . $settings['background']['background-color']['primary'] . ', ' . $settings['background']['background-color']['secondary'] . ');

						  /* Opera 11.10+ */
						  background: ' . $bgImageCss . '-o-linear-gradient(left, ' . $settings['background']['background-color']['primary'] . ', ' . $settings['background']['background-color']['secondary'] . ');
					}';

			//Background-image
			unset( $settings['background']['background-image']['data']['css']['background-image'] );
			unset( $settings['background']['background-image']['data']['css']['background-repeat'] );
			if ( sizeof( $settings['background']['background-image']['data']['css'] ) ) {
				$css .= $selector . ' ' . '{';
				foreach ( $settings['background']['background-image']['data']['css'] as $css_property => $css_value ) {
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