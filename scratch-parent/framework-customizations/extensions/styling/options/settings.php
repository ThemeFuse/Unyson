<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'theme_style' => array(
		'label'      => false,
		'type'       => 'style',
		'predefined' => $predefined = include( 'includes/predefined-styles.php' ),
		'value'      => $predefined['black']['blocks'],
		'blocks'     => array(
			'header'  => array(
				'title'        => __( 'Header', 'fw' ),
				'elements'     => array( 'h1', 'links', 'links_hover', 'background' ),
				//all allowed array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'links', 'links_hover', 'background')
				'css_selector' => array(
					'#masthead',
					'.primary-navigation .mega-menu',
					'.primary-navigation .mega-col',
					'.primary-navigation .mega-row',
				),
				//css selectors (string|array)
			),
			'content' => array(
				'title'        => __( 'Content', 'fw' ),
				'elements'     => array( 'h2', 'h3', 'p', 'links', 'links_hover', 'background' ),
				'css_selector' => array(
					'#primary.content-area',
					'#primary.portfolio-content',
					'#content header',
					'#content article .entry-content',
					'#content article .entry-meta'
				)
			),
			'sidebar' => array(
				'title'        => __( 'Sidebar', 'fw' ),
				'elements'     => array( 'h1', 'links', 'links_hover', 'background' ),
				'css_selector' => array( '#secondary', '.site:before' )
			),
			'footer'  => array(
				'title'        => __( 'Footer', 'fw' ),
				'elements'     => array( 'h1', 'links', 'links_hover', 'background' ),
				'css_selector' => '#colophon'
			),
		),
	),
	'quick_css'   => array(
		'label' => __( 'Quick CSS', 'fw' ),
		'desc'  => __( 'Just want to do some quick CSS changes? Enter them here, they will be<br>applied to the theme. If you need to change major portions of the theme<br>please use the custom.css file.', 'fw' ),
		'type'  => 'textarea',
		'value' => '',
	),
);