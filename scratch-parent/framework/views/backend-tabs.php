<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array $tabs
 * @var array $values
 * @var array $options_data
 */
?>
<div class="fw-options-tabs-wrapper" style="opacity:0">
	<div class="fw-options-tabs-list">
		<ul>
		<?php foreach ($tabs as $tab_id => &$tab): ?>
			<li><a href="#fw-options-tab-<?php echo esc_attr($tab_id) ?>" class="nav-tab fw-wp-link" ><?php echo htmlspecialchars($tab['title'], ENT_COMPAT, 'UTF-8') ?></a></li>
		<?php endforeach; ?>
		</ul>
		<div class="fw-clear"></div>
	</div>
	<div class="fw-options-tabs-contents metabox-holder">
		<div class="fw-inner">
		<?php
		foreach ($tabs as $tab_id => &$tab):
			// prepare attributes
			{
				$attr = isset($tab['attr']) ? $tab['attr'] : array();

				$attr['id'] = 'fw-options-tab-'. esc_attr($tab_id);

				if (!isset($attr['class'])) {
					$attr['class'] = 'fw-options-tab';
				} else {
					$attr['class'] = 'fw-options-tab '. $attr['class'];
				}
			}
		?>
			<div <?php echo fw_attr_to_html($attr) ?>><?php echo fw()->backend->render_options($tab['options'], $values, $options_data) ?></div>
		<?php
		unset($tabs[$tab_id]); // free memory after printed and not needed anymore
		endforeach;
		?>
		</div>
	</div>
	<div class="fw-clear"></div>
</div>
