<?php if (!defined('FW')) die('Forbidden');

$options = array(
	'demo' => array( 
		'title' => __('Demo Options', 'fw'),
		'type'  => 'tab',
		'options' => array(
			'sub_tab_1' => array(
				'title' => __('Without Box', 'fw'),
				'type'  => 'tab',
				'options' => array(
					fw()->theme->get_options('demo-2'),
				),
			),
			'sub_tab_2' => array(
				'title' => __('With Box', 'fw'),
				'type'  => 'tab',
				'options' => array(
					'demo_box' => array(
						'title' => __('Box', 'fw'),
						'type'  => 'box',
						'options' => array(
							fw()->theme->get_options('demo'),
						),
					),
				),
			),
		),
	),
);