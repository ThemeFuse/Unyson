<?php if (!defined('FW')) die('Forbidden'); ?>
<div class="fw-ext-sidebars-box-holder" data-tab-name="specific">
	<div class="fw-ext-sidebars-option-label fw-backend-option-specific-pages-wrap fw-col-sm-4 fw-col-md-3 fw-col-lg-2">
		<div class="fw-inner">
			<label for="fw-select-sidebar-for-<?php echo $id ?>"><?php _e('For specific','fw') ?></label>
			<div class="fw-clear"></div>
		</div>
	</div>

	<div class="fw-ext-sidebars-specific-input fw-ext-sidebars-selector fw-col-sm-8 fw-col-md-9 fw-col-lg-10">
		<div class="fw-inner fw-row fw-backend-option-fixed-width">
			<div class="fw-col-xs-4">
			<?php
				echo fw()->backend->option_type('select')->render($id, $specific_options, array(
					'id_prefix' => 'fw-option-sidebars-for-',
					'value' => ''
				));
			?>
			</div>

			<div class="fw-col-xs-8">
				<div class="ui-widget fw-border-box-sizing" >
					<input id="specific-field-id" type="text" class="autocomplete-ui fw-option" name="specific-field-id" placeholder="<?php echo esc_attr(__('Type to search ...', 'fw')) ?>" />
				</div>
			</div>
		</div>
	</div>

	<div class="fw-col-sm-8 fw-col-sm-offset-4 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2">
		<div class="fw-ext-sidebars-desc"><?php _e('Search for a specific page you want to set a sidebar for','fw')?></div>
	</div>
	<div class="fw-clear"></div>

	<div class="sidebars-specific-pages fw-col-sm-8 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2"><!-- Here will be appear specific pages --></div>
	<div class="fw-clear"></div>

	<?php echo fw_render_view(fw()->extensions->get('sidebars')->get_declared_path('/views/backend-sidebars-positions.php'), array(
		'data_positions_options' => $data_positions_options,
		'id' => $id. '-positions',
		'sidebars' => $sidebars,
	)); ?>
</div>
