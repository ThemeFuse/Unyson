<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['image_sizes'] = array(
	'featured-image' => array(
		'width'  => 223,
		'height' => 139,
		'crop'   => true
	),
	'gallery-image'  => array(
		'width'  => 700,
		'height' => 455,
		'crop'   => true
	)
);
