<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$thumbnails_uri = fw_get_framework_directory_uri( '/core/components/extensions/manager/static/img/thumbnails' );
$github_account = 'ThemeFuse';

$extensions = array(
	'slider' => array(
		'display'     => true,
		'parent'      => 'media',
		'name'        => __( 'Sliders', 'fw' ),
		'description' => __( 'Adds a sliders module to your website from where you\'ll be able to create different built in jQuery sliders for your homepage and rest of the pages.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/sliders.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Sliders-Extension',
			),
		),
	),
	'media' => array(
		'display'     => false,
		'parent'      => null,
		'name'        => __( 'Media', 'fw' ),
		'description' => '',
		'thumbnail'   => 'about:blank',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Empty-Extension',
			),
		),
	),
	'population-method' => array(
		'display'     => false,
		'parent'      => 'media',
		'name'        => __( 'Population method', 'fw' ),
		'description' => '',
		'thumbnail'   => 'about:blank',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-PopulationMethods-Extension',
			),
		),
	),
	'styling' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Styling', 'fw' ),
		'description' => __( 'This extension lets you control the website visual style. Starting from predefined styles to changing specific fonts and colors across the website.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/styling.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Styling-Extension',
			),
		),
	),
	'megamenu' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Mega Menu', 'fw' ),
		'description' => __( 'The Mega Menu extension adds a user-friendly drop down menu that will let you easily create highly customized menu configurations.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/mega-menu.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-MegaMenu-Extension',
			),
		),
	),
	'portfolio' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Portfolio', 'fw' ),
		'description' => __( 'This extension will add a fully fledged portfolio module that will let you display your projects using the built in portfolio pages.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/portfolio.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Portfolio-Extension',
			),
		),
	),
	'page-builder' => array(
		'display'     => true,
		'parent'      => 'shortcodes',
		'name'        => __( 'Page Builder', 'fw' ),
		'description' => __( "Let's you easily build countless pages with the help of the drag and drop visual page builder that comes with a lot of already created shortcodes.", 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/page-builder.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-PageBuilder-Extension',
			),
		),
	),
	'shortcodes' => array(
		'display'     => false,
		'parent'      => null,
		'name'        => __( 'Shortcodes', 'fw' ),
		'description' => '',
		'thumbnail'   => 'about:blank',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Shortcodes-Extension',
			),
		),
	),
	'breadcrumbs' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Breadcrumbs', 'fw' ),
		'description' => __( 'Creates a simplified navigation menu for the pages that can be placed anywhere in the theme. This will make navigating the website much easier.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/breadcrumbs.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Breadcrumbs-Extension',
			),
		),
	),
	'seo' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'SEO', 'fw' ),
		'description' => __( 'This extension will enable you to have a fully optimized WordPress website by adding optimized meta titles, keywords and descriptions.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/seo.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-SEO-Extension',
			),
		),
	),
	'sidebars' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Sidebars', 'fw' ),
		'description' => __( 'Brings a new layer of customization freedom to your website by letting you add more than one sidebar to a page, or different sidebars on different pages.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/sidebars.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Sidebars-Extension',
			),
		),
	),
	'feedback' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Feedback', 'fw' ),
		'description' => __( 'Adds the possibility to leave feedback (comments, reviews and rating) about your products, articles, etc. This replaces the default comments system.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/feedback.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Feedback-Extension',
			),
		),
	),
	'backup' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Backup', 'fw' ),
		'description' => __( 'This extension lets you set up daily, weekly or monthly backup schedule. You can choose between a full backup or a data base only backup.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/backup.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Backup-Extension',
			),
		),
	),
	'events' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Events', 'fw' ),
		'description' => __( 'This extension adds a fully fledged Events module to your theme. It comes with built in pages that contain a calendar where events can be added.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/events.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Events-Extension',
			),
		),
	),
	'analytics' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Analytics', 'fw' ),
		'description' => __( 'Enables the possibility to add the Google Analytics tracking code that will let you get all the analytics about visitors, page views and more.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/analytics.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Analytics-Extension',
			),
		),
	),
	'builder' => array(
		'display'     => false,
		'parent'      => null,
		'name'        => __( 'Builder', 'fw' ),
		'description' => '',
		'thumbnail'   => 'about:blank',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Builder-Extension',
			),
		),
	),
	'learning' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Learning', 'fw' ),
		'description' => __( 'This extension adds a Learning module to your theme. Using this extension you can add courses, lessons and tests for your users to take.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/learning.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Learning-Extension',
			),
		),
	),
	'forms' => array(
		'display'     => false,
		'parent'      => null,
		'name'        => __( 'Forms', 'fw' ),
		'description' => __( 'This extension adds the possibility to create a contact form. Use the drag & drop form builder to create any contact form you\'ll ever want or need.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/forms.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Forms-Extension',
			),
		),
	),
	'mailer' => array(
		'display'     => false,
		'parent'      => null,
		'name'        => __( 'Mailer', 'fw' ),
		'description' => __( 'This extension will let you set some global email options and it is used by other extensions (like Forms) to send emails.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/mailer.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Mailer-Extension',
			),
		),
	),
	'social' => array(
		'display'     => true,
		'parent'      => null,
		'name'        => __( 'Social', 'fw' ),
		'description' => __( 'Use this extension to configure all your social related APIs. Other extensions will use the Social extension to connect to your social accounts.', 'fw' ),
		'thumbnail'   => $thumbnails_uri . '/social.jpg',
		'download'    => array(
			'github' => array(
				'user_repo' => $github_account . '/Unyson-Social-Extension',
			),
		),
	),
);
