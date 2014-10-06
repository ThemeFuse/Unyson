<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( !is_admin() ) {
	$ext_instance = fw()->extensions->get( 'events' );

	if ( is_singular($ext_instance->get_post_type_name()) ) {
		wp_enqueue_style(
			'fw-extension-'. $ext_instance->get_name() .'-single-styles',
			$ext_instance->locate_css_URI( 'single-styles' ),
			array(),
			$ext_instance->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-extension-'. $ext_instance->get_name() .'-single-scripts',
			$ext_instance->locate_js_URI( 'single-scripts' ),
			array( 'jquery'),
			$ext_instance->manifest->get_version(),
			true
		);

	}
}