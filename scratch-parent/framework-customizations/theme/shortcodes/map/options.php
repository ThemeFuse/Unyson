<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = array(
	'data_provider' => array(
		'type'  => 'multi-picker',
		'label' => false,
		'desc'  => false,
		'picker' => array(
			'gadget' => array(
				'label'   => __('Population Method', 'unyson'),
				'desc'    => __( 'Select map population method (Ex: events, custom)', 'unyson' ),
				'type'    => 'select',
				'choices' => FW_Shortcode_Map::fw_theme_get_choices(),
			)
		),
		'choices' => FW_Shortcode_Map::fw_theme_get_options_choices(),
		'show_borders' => true,
	),
	'map_type' => array(
		'type'  => 'select',
		'label' => __('Map Type', 'unyson'),
		'desc'  => __('Select map type', 'unyson'),
		'choices' => array(
			'roadmap'   => __('Roadmap', 'unyson'),
			'terrain' => __('Terrain', 'unyson'),
			'satellite' => __('Satellite', 'unyson'),
			'hybrid'    => __('Hybrid', 'unyson')
		)
	),
	'map_height' => array(
		'label' => __('Map Height', 'fw'),
		'desc'  => __('Set map height (Ex: 300)', 'fw'),
		'type'  => 'text'
	)


);