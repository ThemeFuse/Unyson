<?php if (!defined('FW')) die('Forbidden');
/**
 * @var string $id
 * @var array $option
 * @var array $data
 * @var string $sortable_image url
 */
$attr = $option['attr'];

// must contain characters that will remain the same after htmlspecialchars()
$increment_placeholder = '###-addable-popup-increment-'. fw_rand_md5() .'-###';
?>
<div <?php echo fw_attr_to_html($attr); ?>>

	<div class="items-wrapper">
		<div class="item default">
			<div class="input-wrapper">
				<?php echo fw()->backend->option_type('hidden')->render('', array('value' => '[]'), array(
					'id_prefix' => $data['id_prefix'] . $id . '-' . $increment_placeholder . '-',
					'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
				));?>
			</div>
			<img src="<?php echo $sortable_image; ?>" class="sort-item"/>

			<div class="content"></div>
			<a href="#" class="dashicons fw-x delete-item"></a>
		</div>
		<?php foreach ($data['value'] as $key => $value): ?>
			<div class="item">
				<div class="input-wrapper">
					<?php echo fw()->backend->option_type('hidden')->render('', array('value' => json_encode($value)), array(
						'id_prefix' => $data['id_prefix'] . $id . '-' . $key . '-',
						'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
					));?>
				</div>
				<img src="<?php echo $sortable_image; ?>" class="sort-item"/>

				<div class="content"><!-- will be populated from js --></div>
				<a href="#" class="dashicons fw-x delete-item"></a>
			</div>
		<?php endforeach; ?>
	</div>
	<!--<div class="dashicons dashicons-plus add-new-item"></div>-->
	<?php
	echo fw_html_tag('button', array(
		'type' => 'button',
		'class' => 'button add-new-item',
		'onclick' => 'return false;',
		'data-increment-placeholder' => $increment_placeholder,
	), __('Add', 'fw'));
	?>
</div>

