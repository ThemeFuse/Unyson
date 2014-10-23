<?php if (!defined('FW')) die('Forbidden'); ?>
<div class="fw-row fw-ext-sidebars-image-picker-box-<?php echo $id ?>">
	<div class="fw-ext-sidebars-option-label fw-col-sm-4 fw-col-md-3 fw-col-lg-2">
		<div class="fw-inner">
			<label for="fw-select-sidebar-for-<?php echo $id ?>"><?php _e('Sidebar','fw') ?></label>
			<div class="fw-clear"></div>
		</div>
	</div>

	<div class="fw-col-sm-8 fw-col-md-9 fw-col-lg-10">
		<div class="fw-backend-option-fixed-width">
			<?php echo fw()->backend->option_type('image-picker')->render('positions', $data_positions_options, array('value' => '')); ?>
		</div>
	</div>
	<div class="fw-clear"></div>

	<div class="fw-ext-sidebars-desc fw-col-sm-8 fw-col-sm-offset-4 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2">
		<?php _e('Choose the position for your sidebar(s)', 'fw')?>
	</div>
</div>

<div class="fw-clear"></div>

<?php $colors = _FW_Extension_Sidebars_Config::$allowed_colors ?>

<div class="placeholders fw-col-sm-8 fw-col-sm-offset-4 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2">
	<?php foreach($colors as $color) : ?>
		<?php echo fw_render_view(fw()->extensions->get('sidebars')->get_declared_path('/views/backend-select-sidebar.php'), array(
			'id' => $id ,
			'color' => $color,
			'sidebars' => $sidebars,
		)); ?>
	<?php endforeach; ?>
</div>

<div class="fw-clear"></div>

	<div id="fw-add-button" class="fw-col-sm-8 fw-col-sm-offset-4 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2">
		<input id="submit-settings-<?php echo $id ?>" type="button" class="button button-primary button-large" value="<?php _e('Add Sidebar','fw')?>" />
		<span class="spinner fw-ext-sidebars-submiting-<?php echo $id?>"></span>
	</div>

<div class="fw-clear"></div>