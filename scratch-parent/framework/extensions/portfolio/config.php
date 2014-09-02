<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['image_sizes'] = array(
	'featured-image' => array(
		'width'  => 200,
		'height' => 200,
		'crop'   => true
	),
	'gallery-image'  => array(
		'width'  => 500,
		'height' => 500,
		'crop'   => true
	)
);
