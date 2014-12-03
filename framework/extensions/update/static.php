<?php if (!defined('FW')) die('Forbidden');

$extension = fw()->extensions->get('update');

if (fw_current_screen_match(array('only' => array(array('id' => 'update-core'))))) {
	// Include only on update page

	wp_enqueue_style(
		'fw-ext-'. $extension->get_name() .'-update-page',
		$extension->get_declared_URI('/static/css/admin-update-page.css'),
		array(),
		$extension->manifest->get_version()
	);
}
