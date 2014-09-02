<?php if (!defined('FW')) die('Forbidden'); ?>
<div class="fw-ext-sidebars-box-holder fw-ext-sidebars-created" >

	<div class="fw-ext-sidebars-created-tab-title"><?php _e('Sidebars for', 'fw') ?></div>

	<div class="fw-ext-sidebars-preset-list">
		<?php if (is_array($created_sidebars)) : ?>
			<?php foreach($created_sidebars as $item) : ?>
				<div class="fw-ext-sidebars-created-tab-preset"  data-type="<?php echo isset($item['type']) ? $item['type'] : ''?>" data-preset-id="<?php echo isset($item['preset_id']) ? $item['preset_id'] : ''?>">

					<span class="fw-ext-sidebars-preset-edit-span">
							<span class="spinner fw-ext-sidebars-preset-editing" style="display: none;"></span>
							<a href="#" class="fw-ext-sidebars-preset-edit">
								<?php echo isset($item['page_names']) ? $item['page_names'] : $item['label']?>
							</a>
							<span class="fw-ext-sidebars-desc">&nbsp;(<?php echo isset($item['page_names']) ? __('For Specific Page', 'fw') : __('For Grouped Page', 'fw') ?>)</span>
						</span>

					<span class="fw-ext-sidebars-preset-remove-span">
						<a href="#" class="fw-ext-sidebars-preset-remove dashicons fw-x"></a>
					</span>
					<span class="spinner fw-ext-sidebars-preset-removing" style="display: none;"></span>
				</div>
			<?php endforeach;?>
		<?php endif;?>
	</div>

</div>


