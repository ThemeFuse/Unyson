<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Sitemap extension configuration file
 */

$cfg = array(
	'search_engines'       => array( 'google', 'bing' ),
	'sitemap_refresh_rate' => 2,
	'excluded_post_types'  => array( 'attachment' ),
	'excluded_taxonomies'  => array( 'post_tag' ),
	'url_settings'  => array(
		'home'  => array(
			'priority'  => 1,
			'frequency' => 'daily',
		),
		'posts' => array(
			'priority'  => 0.6,
			'frequency' => 'daily',
			'type'      => array(
				'page' => array(
					'priority'  => 0.5,
					'frequency' => 'weekly',
				)
			)
		),
		'taxonomies'     => array(
			'priority'  => 0.4,
			'frequency' => 'weekly',
			'type'  => array(
				'post_tag'  => array(
					'priority'  => 0.3,
					'frequency' => 'weekly',
				)
			)
		)
	)
);