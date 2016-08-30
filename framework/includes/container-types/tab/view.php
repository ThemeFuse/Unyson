<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array $tabs
 * @var array $values
 * @var array $options_data
 */

$global_lazy_tabs = fw()->theme->get_config('lazy_tabs');

?>
<div class="fw-options-tabs-wrapper">
	<div class="fw-options-tabs-list">
		<ul>
			<?php foreach ($tabs as $tab_id => &$tab): ?>
				<li <?php echo isset($tab['li-attr']) ? fw_attr_to_html($tab['li-attr']) : ''; ?> >
					<a href="#fw-options-tab-<?php echo esc_attr($tab_id) ?>" class="nav-tab fw-wp-link" ><?php
						echo htmlspecialchars($tab['title'], ENT_COMPAT, 'UTF-8') ?></a>
				</li>
			<?php endforeach; unset($tab); ?>
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

					$lazy_tabs = isset($tab['lazy_tabs']) ? $tab['lazy_tabs'] : $global_lazy_tabs;

					$attr['id'] = 'fw-options-tab-'. esc_attr($tab_id);

					if (!isset($attr['class'])) {
						$attr['class'] = 'fw-options-tab';
					} else {
						$attr['class'] = 'fw-options-tab '. $attr['class'];
					}

					if ($lazy_tabs) {
						$attr['data-fw-tab-html'] = fw()->backend->render_options(
							$tab['options'], $values, $options_data
						);
					}
				}
				?><div <?php echo fw_attr_to_html($attr) ?>><?php
					echo $lazy_tabs ? '' : fw()->backend->render_options($tab['options'], $values, $options_data);
				?></div><?php
				unset($tabs[$tab_id]); // free memory after printed and not needed anymore
			endforeach;
			unset($tab);
			?>
		</div>
	</div>
	<div class="fw-clear"></div>
</div>
