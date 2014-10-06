<?php if (!defined('FW')) die('Forbidden');
/**
 * @var string $id
 * @var array $option
 * @var array $data
 * @var string $sortable_image url
 */
$attr = $option['attr'];
?>
<div <?php echo fw_attr_to_html($attr); ?>>

	<div class="items-wrapper">
		<div class="item default">
			<div class="input-wrapper">
				<?php echo fw()->backend->option_type('hidden')->render('', array('value' => '{{-json}}'), array(
					'id_prefix' => $data['id_prefix'] . $id . '-' . '{{=i}}' . '-',
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
	<button type="button" class="button add-new-item" onclick="return false;">Add</button>
</div>

