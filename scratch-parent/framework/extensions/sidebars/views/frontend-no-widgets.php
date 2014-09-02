<?php if (!defined('FW')) die('Forbidden'); ?>
<aside id="no-widget" class="widget before-widget-class widget widget_calendar">
<p class="fw-frontend-ext-sidebars-no-widget" >
	<?php _e(sprintf('The sidebar (%s) you added has no widgets. Please add some from the ', $sidebar_id), 'fw'); ?>
	<a href="<?php echo admin_url('widgets.php') ?>" target="_blank"><?php _e('Widgets Page', 'fw'); ?></a>
</p>
</aside>
