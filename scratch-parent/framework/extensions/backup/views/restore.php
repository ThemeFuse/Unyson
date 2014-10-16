<?php if (!defined('FW')) die('Forbidden');

/**
 * @var FW_Extension_Backup $backup
 */

$backup = fw()->extensions->get('backup');

echo $backup->get_request_filesystem_credentials();

$backup->render('restore-popup', array(
	'title' => __('Restore in Progress', 'fw'),
	'subtitle' => __("We are currently restoring your backup.\nThis may take up to a few minutes.", 'fw'),
));
