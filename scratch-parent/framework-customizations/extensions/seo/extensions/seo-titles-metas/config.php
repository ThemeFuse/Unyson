<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['excluded_post_types'] = array( 'attachment' );
$cfg['excluded_taxonomies'] = array( 'post_tag' );