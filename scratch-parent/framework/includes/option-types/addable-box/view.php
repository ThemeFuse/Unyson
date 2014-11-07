<?php if (!defined('FW')) die('Forbidden');
/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 * @var  array $controls
 */

$attr = $option['attr'];
unset($attr['name']);
unset($attr['value']);

// Use only groups and options
{
	$collected = array();
	fw_collect_first_level_options($collected, $option['box-options']);
	$box_options =& $collected['groups_and_options'];
	unset($collected);
}

// generate controls html
{
	ob_start(); ?>
	<small class="fw-option-box-controls">
		<?php foreach ($controls as $c_id => $control): ?>
			<small class="fw-option-box-control-wrapper"><a href="#" class="fw-option-box-control" data-control-id="<?php echo esc_attr($c_id) ?>" onclick="return false"><?php echo $control ?></a></small>
		<?php endforeach; ?>
	</small>
	<?php $controls_html = ob_get_clean();
}
?>
<div <?php echo fw_attr_to_html($attr) ?>>
	<?php $i = 0; ?>
	<div class="fw-option-boxes metabox-holder">
		<?php foreach ($data['value'] as $value_index => &$values): ?>
			<?php $i++; ?>
			<div class="fw-option-box">
				<?php ob_start() ?>
				<div class="fw-option-box-options fw-force-xs">
					<?php echo fw()->backend->render_options($box_options, $values, array(
						'id_prefix'   => $data['id_prefix'] . $id .'-'. $i .'-',
						'name_prefix' => $data['name_prefix'] .'['. $id .']['. $i .']',
					)) ?>
				</div>
				<?php
				echo fw()->backend->render_box(
					$data['id_prefix'] . $id .'-'. $i .'-box',
					'͏&nbsp;',
					ob_get_clean(),
					array(
						'html_after_title' => $controls_html
					)
				);
				?>
			</div>
		<?php endforeach; ?>
	</div>
	<br class="default-box-template fw-hidden" data-template="<?php
		/**
		 * Place template in attribute to prevent it to be treated as html
		 * when this option will be used inside another option template
		 */

		/**
		 * This is a reference.
		 * Unset before replacing with new value
		 * to prevent changing value to what it refers
		 */
		unset($values);

		$values = array();

		// must contain characters that will remain the same after htmlspecialchars()
		$increment_template = '###-addable-box-increment-###';

		echo fw_htmlspecialchars(
			'<div class="fw-option-box">'.
				fw()->backend->render_box(
					$data['id_prefix'] . $id .'-'. $increment_template .'-box',
					'͏&nbsp;',
					'<div class="fw-option-box-options fw-force-xs">'.
						fw()->backend->render_options($box_options, $values, array(
							'id_prefix'   => $data['id_prefix'] . $id .'-'. $increment_template .'-',
							'name_prefix' => $data['name_prefix'] .'['. $id .']['. $increment_template .']',
						)).
					'</div>',
					array(
						'html_after_title' => $controls_html
					)
				).
			'</div>'
		);
	?>">
	<div class="fw-option-boxes-controls">
		<?php
		echo fw_html_tag('button', array(
			'type'    => 'button',
			'onclick' => 'return false;',
			'class'   => 'button fw-option-boxes-add-button',
			'data-increment' => ++$i,
			'data-limit'     => intval($option['limit'])
		), __('Add', 'fw'));
		?>
	</div>
</div>