<?php if (!defined('FW')) die('Forbidden');
/**
 * Include static files: javascript and css
 */

if (is_admin()) {
	return;
}

/**
 * Enqueue scripts and styles for the front end.
 */

// Add Lato font, used in the main stylesheet.
wp_enqueue_style(
	'fw-theme-lato',
	fw_theme_font_url(),
	array(),
	fw()->theme->manifest->get_version()
);

// Add Genericons font, used in the main stylesheet.
wp_enqueue_style(
	'genericons',
	get_template_directory_uri() . '/genericons/genericons.css',
	array(),
	fw()->theme->manifest->get_version()
);

// Load our main stylesheet.
wp_enqueue_style(
	'fw-theme-style',
	get_stylesheet_uri(),
	array( 'genericons' ),
	fw()->theme->manifest->get_version()
);

// Load the Internet Explorer specific stylesheet.
wp_enqueue_style(
	'fw-theme-ie',
	get_template_directory_uri() . '/css/ie.css',
	array( 'fw-theme-style', 'genericons' ),
	fw()->theme->manifest->get_version()
);
wp_style_add_data( 'fw-theme-ie', 'conditional', 'lt IE 9' );

if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
	wp_enqueue_script( 'comment-reply' );
}

if ( is_singular() && wp_attachment_is_image() ) {
	wp_enqueue_script(
		'fw-theme-keyboard-image-navigation',
		get_template_directory_uri() . '/js/keyboard-image-navigation.js',
		array( 'jquery' ),
		fw()->theme->manifest->get_version()
	);
}

if ( is_active_sidebar( 'sidebar-1' ) ) {
	wp_enqueue_script( 'jquery-masonry' );
}

if ( is_front_page() && 'slider' == get_theme_mod( 'featured_content_layout' ) ) {
	wp_enqueue_script(
		'fw-theme-slider',
		get_template_directory_uri() . '/js/slider.js',
		array( 'jquery' ),
		fw()->theme->manifest->get_version(),
		true
	);
	wp_localize_script( 'fw-theme-slider', 'featuredSliderDefaults', array(
		'prevText' => __( 'Previous', 'unyson' ),
		'nextText' => __( 'Next', 'unyson' )
	) );
}

wp_enqueue_script(
	'jquery-ui-tabs',
	get_template_directory_uri() . '/js/jquery-ui-1.10.4.custom.js',
	array( 'jquery' ),
	fw()->theme->manifest->get_version(),
	true
);

wp_enqueue_script(
	'fw-theme-script',
	get_template_directory_uri() . '/js/functions.js',
	array( 'jquery' ),
	fw()->theme->manifest->get_version(),
	true
);

// Font Awesome stylesheet
wp_enqueue_style(
	'font-awesome',
	get_template_directory_uri() . '/css/font-awesome/css/font-awesome.min.css',
	array(),
	fw()->theme->manifest->get_version()
);
