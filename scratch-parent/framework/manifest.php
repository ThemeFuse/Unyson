<?php if (!defined('FW')) die('Forbidden');

$manifest = array();

$manifest['name'] = __('Unyson', 'fw');

$manifest['version'] = '1.4.5';

$manifest['requirements'] = array(
	'wordpress' => array(
		'min_version' => '4.0',
	),
);

$manifest['github_update'] = 'ThemeFuse/Unyson-Framework';
