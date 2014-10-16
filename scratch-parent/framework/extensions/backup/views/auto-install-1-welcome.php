<?php if (!defined('FW')) die('Forbidden');

/**
 * @var FW_Extension_Backup $backup
 */

$backup = fw()->extensions->get('backup');

?>
<div class="wrap">

	<div id="icon-tools" class="icon32"></div>

	<h2><?php echo esc_html(fw()->theme->manifest->get_name()) ?> WordPress Theme</h2>

	<h3>Make you theme look exactly like our demo</h3>

	<p>By importing the demo content, your theme will look like the one
		you see on <a href="<?php echo esc_attr($backup->get_config('demo_page_link')) ?>">our demo</a>.
		This install is not necessary but will help you get the core pages,
		categories and meta setup correctly.
		This action will also let you understand how the theme works by
		allowing you to modify a content that is already there rather than
		creating it from scratch.</p>

	<div class="error">
		<p>
			<strong>Important</strong>: The demo content <strong>will replace</strong>
			all of your content (i.e. all of your content <strong>will be deleted</strong>).
		</p>
	</div>

	<p>
		<a href="<?php echo esc_attr($backup->action()->url_backup_auto_install()) ?>" class="button button-primary">Import Demo Content</a>
		<a href="<?php echo esc_attr(admin_url()) ?>" class="button">Skip Import (Not Recommended)</a>
	</p>

</div>
