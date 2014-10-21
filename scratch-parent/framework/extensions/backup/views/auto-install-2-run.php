<?php if (!defined('FW')) die('Forbidden');

/**
 * @var FW_Extension_Backup $backup
 */

$backup = fw()->extensions->get('backup');
$theme_name = fw()->theme->manifest->get_name();

?>
<div class="wrap">

	<div id="icon-tools" class="icon32"></div>

	<h2><?php strtr(__('{theme_name} WordPress Theme', 'fw'), array('{theme_name}' => esc_html($theme_name))) ?></h2>

	<?php

		echo $backup->get_request_filesystem_credentials();

		$backup->render('restore-popup', array(
			'title' => __('Auto-install in Progress', 'fw'),
			'subtitle' => __("We are currently configuring your template to look like our demo.\nThis may take up to a few minutes.", 'fw'),
		));

	?>

</div>
