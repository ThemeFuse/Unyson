<?php if (!defined('FW')) die('Forbidden');

$cfg = array();

/**
 * @see fw_ext_builder_get_item_width()
 */
$cfg['default_item_widths'] = array(
	'1_5' => array(
		'title'          => '1/5',
		'backend_class'  => 'fw-col-sm-2',
		'frontend_class' => 'col-xs-12 col-sm-2',
	),
	'1_4' => array(
		'title'          => '1/4',
		'backend_class'  => 'fw-col-sm-3',
		'frontend_class' => 'col-xs-12 col-sm-3',
	),
	'1_3' => array(
		'title'          => '1/3',
		'backend_class'  => 'fw-col-sm-4',
		'frontend_class' => 'col-xs-12 col-sm-4',
	),
	'1_2' => array(
		'title'          => '1/2',
		'backend_class'  => 'fw-col-sm-6',
		'frontend_class' => 'col-xs-12 col-sm-6',
	),
	'2_3' => array(
		'title'          => '2/3',
		'backend_class'  => 'fw-col-sm-8',
		'frontend_class' => 'col-xs-12 col-sm-8',
	),
	'3_4' => array(
		'title'          => '3/4',
		'backend_class'  => 'fw-col-sm-9',
		'frontend_class' => 'col-xs-12 col-sm-9',
	),
	'1_1' => array(
		'title'          => '1/1',
		'backend_class'  => 'fw-col-sm-12',
		'frontend_class' => 'col-xs-12',
	),
);
