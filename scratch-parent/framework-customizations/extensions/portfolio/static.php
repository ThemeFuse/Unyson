<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( !is_admin() ) {
	$ext_instance = fw()->extensions->get( 'portfolio' );
	$settings     = $ext_instance->get_settings();

	if ( is_singular( $settings['post_type'] ) ) {

		wp_enqueue_style( 
			'fw-extension-'. $ext_instance->get_name() .'-nivo-default', 
			$ext_instance->locate_css_URI( 'NivoSlider/themes/default/default' ),
			array(),
			$ext_instance->manifest->get_version()
		);
		wp_enqueue_style(
			'fw-extension-'. $ext_instance->get_name() .'-nivo-bar',
			$ext_instance->locate_css_URI( 'NivoSlider/themes/bar/bar' ),
			array(),
			$ext_instance->manifest->get_version()
		);
		wp_enqueue_style(
			'fw-extension-'. $ext_instance->get_name() .'-nivo-dark',
			$ext_instance->locate_css_URI( 'NivoSlider/themes/dark/dark' ),
			array(),
			$ext_instance->manifest->get_version()
		);
		wp_enqueue_style(
			'fw-extension-'. $ext_instance->get_name() .'-nivo-light',
			$ext_instance->locate_css_URI( 'NivoSlider/themes/light/light' ),
			array(),
			$ext_instance->manifest->get_version()
		);
		wp_enqueue_style(
			'fw-extension-'. $ext_instance->get_name() .'-nivo-slider',
			$ext_instance->locate_css_URI( 'nivo-slider' ),
			array(),
			$ext_instance->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-extension-'. $ext_instance->get_name() .'-nivoslider',
			$ext_instance->locate_js_URI( 'jquery.nivo.slider' ),
			array( 'jquery' ),
			$ext_instance->manifest->get_version(),
			true
		);
		wp_enqueue_script(
			'fw-extension-'. $ext_instance->get_name() .'-script',
			$ext_instance->locate_js_URI( 'projects-script' ),
			array( 'fw-extension-'. $ext_instance->get_name() .'-nivoslider' ),
			$ext_instance->manifest->get_version(),
			true
		);

	} elseif ( is_tax( $settings['taxonomy_name'] ) || is_post_type_archive( $settings['post_type'] ) ) {
		wp_enqueue_style(
			'fw-extension-'. $ext_instance->get_name() .'-style',
			$ext_instance->locate_css_URI( 'style' ),
			array(),
			$ext_instance->manifest->get_version()
		);
		wp_enqueue_script(
			'fw-extension-'. $ext_instance->get_name() .'-mixitup',
			$ext_instance->locate_js_URI( 'jquery.mixitup.min' ),
			array( 'jquery' ),
			$ext_instance->manifest->get_version(),
			true
		);
		wp_enqueue_script(
			'fw-extension-'. $ext_instance->get_name() .'-script',
			$ext_instance->locate_js_URI( 'portfolio-script' ),
			array( 'fw-extension-'. $ext_instance->get_name() .'-mixitup' ),
			$ext_instance->manifest->get_version(),
			true
		);

	}
}



