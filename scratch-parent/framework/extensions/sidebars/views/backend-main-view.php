<?php if (!defined('FW')) die('Forbidden'); ?>
<?php $cnt_created_sidebars = count($created_sidebars); ?>
<?php if (!empty($data_positions_options['choices']) or $cnt_created_sidebars) : ?>
<div class="fw-ext-sidebars-wrap-container">
	<div class="fw-ext-sidebars-wrap">

		<h3 class="hndle">
			<span><?php _e('Manage Sidebars', 'fw');?></span>
		</h3>
		<div class="fw-ext-sidebars-desc"><?php _e('Use this section to create and/or set different sidebar(s) for different page(s)','fw')?></div>

		<div class="fw-sidebars-tabs-wrapper" style="opacity: 0;" >
			<div class="fw-sidebars-tabs-list">
				<ul>
					<?php if (!empty($data_positions_options['choices'])) : ?>
					<li><a href="#fw-sidebars-tab-1" class="nav-tab" ><span class="spinner"></span><?php echo __('For Grouped Pages','fw'); ?></a></li>
					<li><a href="#fw-sidebars-tab-2" class="nav-tab" ><span class="spinner"></span><?php echo __('For Specific Pages','fw'); ?></a></li>
					<?php endif ?>
					<li <?php echo $cnt_created_sidebars ? '' : 'style="display: none"'; ?> ><a href="#fw-sidebars-tab-3" class="nav-tab" ><?php echo  $cnt_created_sidebars . ' ' . __('Created','fw'); ?></a></li>
				</ul>
				<div class="fw-clear"></div>
			</div>

			<div class="fw-sidebars-tabs">
				<div class="fw-inner">

					<?php if (!empty($data_positions_options['choices'])) : ?>
					<div id="fw-sidebars-tab-1" role="tabpanel" >
						<?php  echo fw_render_view(fw()->extensions->get('sidebars')->get_declared_path('/views/backend-tab-grouped.php'), array(
							'grouped_options' => $grouped_options,
							'data_positions_options' => $data_positions_options,
							'id' => 'grouped',
							'sidebars' => $sidebars,
						)); ?>
					</div>

					<div id="fw-sidebars-tab-2" role="tabpanel" >
						<?php  echo fw_render_view(fw()->extensions->get('sidebars')->get_declared_path('/views/backend-tab-specific.php'), array(
							'specific_options' => $specific_options,
							'data_positions_options' => $data_positions_options,
							'id' => 'specific',
							'sidebars' => $sidebars,
						)); ?>
					</div>
					<?php endif ?>

					<div id="fw-sidebars-tab-3" role="tabpanel" <?php echo ($cnt_created_sidebars ? '' : 'style="display: none;"') ?> >
						<?php  echo fw_render_view(fw()->extensions->get('sidebars')->get_declared_path('/views/backend-tab-created-sidebars.php'), array(
							'created_sidebars' => $created_sidebars,
						)); ?>
					</div>

				</div>
			</div>
			<div class="fw-clear"></div>
		</div>

	</div>
</div>
<?php endif; ?>
