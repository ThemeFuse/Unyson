<?php if (!defined('FW')) {
	die('Forbidden');
}

$options = array(
	'background-color' => array(
		'type' => 'color-picker',
		'label' => __('Background Color', 'unyson'),
		'desc' => __('Please select the background color', 'unyson'),
	),
	'background-image' => array(
		'type' => 'background-image',
		'label' => __('Background Image', 'unyson'),
		'desc' => __('Please select the background image', 'unyson'),
		'choices' => array(//	in future may will set predefined images
		)
	),
	'video' => array(
		'type'  => 'text',
		'label' => __('Background Video', 'fw'),
		'desc'  => __('Insert Video URL to embed this video', 'fw'),
	)
);