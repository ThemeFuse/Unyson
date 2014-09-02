<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array(
	'excluded_post_types' => array( 'attachment' ),
	'excluded_taxonomies'   => array( 'post_tag' ),
);