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
				'label'   => __('Population Method', 'fw'),
				'desc'    => __( 'Select calendar population method (Ex: events, custom)', 'fw' ),
				'type'    => 'select',
				'choices' => FW_Shortcode_Calendar::fw_theme_get_choices(),
			)
		),
		'choices' => FW_Shortcode_Calendar::fw_theme_get_options_choices(),
		'show_borders' => true,
	),


	'template' => array(
		'label'   => __('Calendar Type', 'fw' ),
		'desc'    => __('Select calendar type', 'fw'),
		'type'    => 'select',
		'choices' => array(
			'day'   => __('Daily', 'fw'),
			'week'  => __('Weekly', 'fw'),
			'month' => __('Monthly', 'fw')
		),
	),
	'first_week_day' => array(
		'label' => __('Start Week On', 'fw'),
		'desc'    => __( 'Select first day of week', 'fw' ),
		'type'    => 'select',
		'choices' => array(
			'1' => __('Monday', 'fw'),
			'2' => __('Sunday', 'fw')
		),
		'value' => 1
	),
);