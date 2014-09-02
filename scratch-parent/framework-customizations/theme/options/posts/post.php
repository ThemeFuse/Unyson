<?php if (!defined('FW')) die('Forbidden');

$options = array(
	'main' => array(
		'title' => false,
		'type'  => 'box',
		'options' => array(
			fw()->theme->get_options('demo-box'),
		),
	),
);