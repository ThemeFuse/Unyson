<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( ! is_admin() ) {
	global $post;
	/** @var $instance FW_Extension_FeedBack_Stars */
	$instance = fw()->extensions->get( 'feedback-stars' );
	if ((!empty($post)) && (!empty($post->post_type)) && post_type_supports( $post->post_type, $instance->get_parent()->supports_feature_name ) ) {
		{
			wp_register_style( 'qtip',
				fw_get_framework_directory_uri( '/static/libs/qtip/css/jquery.qtip.min.css' ),
				array(),
				fw()->manifest->get_version()
			);
			wp_register_script(
				'qtip',
				fw_get_framework_directory_uri( '/static/libs/qtip/jquery.qtip.min.js' ),
				array( 'jquery' ),
				fw()->manifest->get_version()
			);
		}

		{
			wp_register_style(
				'fw-font-awesome',
				fw_get_framework_directory_uri('/static/libs/font-awesome/css/font-awesome.min.css'),
				array(),
				fw()->manifest->get_version()
			);
		}

		wp_enqueue_style( 'fw-extension-' . $instance->get_name() . '-styles', $instance->locate_css_URI( 'styles' ), array( 'fw-font-awesome', 'qtip' ),
			$instance->manifest->get_version() );

		wp_enqueue_script( 'fw-extension-' . $instance->get_name() . '-scripts', $instance->locate_js_URI( 'scripts' ), array(
				'jquery',
				'qtip'
			),
			$instance->manifest->get_version() );
	}
}
