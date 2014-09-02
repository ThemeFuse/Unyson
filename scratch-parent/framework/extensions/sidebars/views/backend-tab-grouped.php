<?php if (!defined('FW')) die('Forbidden'); ?>
<div class="fw-ext-sidebars-box-holder" data-tab-name="grouped">

	<div class="fw-ext-sidebars-option-label fw-backend-option-grouped-pages-wrap fw-col-sm-4 fw-col-md-3 fw-col-lg-2">
		<div class="fw-inner">
			<label for="fw-option-sidebars-for-<?php echo $id ?>"><?php _e('For group','fw') ?></label>
			<div class="fw-clear"></div>
		</div>
	</div>

	<div class="fw-ext-sidebars-selector fw-col-sm-8 fw-col-md-9 fw-col-lg-10">
		<div class="fw-inner fw-backend-option-fixed-width">
			<?php
			echo fw()->backend->option_type('select')->render($id, $grouped_options, array(
				'id_prefix' => 'fw-option-sidebars-for-',
				'value' => ''
			));
			?>
		</div>
	</div>

	<div class="fw-clear"></div>

	<div class="fw-col-sm-8 fw-col-sm-offset-4 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2">
		<div class="fw-ext-sidebars-desc"><?php _e('Select group of pages you want to set a sidebar for.','fw')?></div>
	</div>

	<div class="fw-clear"></div>

	<?php echo fw_render_view(fw()->extensions->get('sidebars')->get_declared_path('/views/backend-sidebars-positions.php'), array(
		'data_positions_options' => $data_positions_options,
		'id' => $id. '-positions',
		'sidebars' => $sidebars,
	)); ?>

</div>
