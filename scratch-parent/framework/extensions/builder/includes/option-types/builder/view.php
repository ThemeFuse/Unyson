<?php if (!defined('FW')) die('Forbidden');

/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 * @var  array $thumbnails
 * @var string $option_type
 */

{
	$div_attr = $option['attr'];

	unset(
		$div_attr['value'],
		$div_attr['name']
	);

	if (!(isset($option['fullscreen']) and $option['fullscreen'] === false)) {
		$div_attr['class'] .= apply_filters('fw_builder_fullscreen_add_classes', ' fw-option-type-builder');
	} else {
		$div_attr['class'] .= ' fw-option-type-builder';
	}

	$div_attr['data-builder-option-type'] = $option_type;
}

{
	$tabs_options = array();
	foreach ($thumbnails as $thumbnails_tab_title => &$thumbnails_tab_thumbnails) {
		$tabs_options[ 'random-'. fw_unique_increment() ] = array(
			'type'    => 'tab',
			'title'   => $thumbnails_tab_title,
			'attr'    => array(
				'class' => 'fw-option-type-builder-thumbnails-tab',
			),
			'options' => array(
				'random-'. fw_unique_increment() => array(
					'type'  => 'html',
					'label' => false,
					'desc'  => false,
					'html'  => implode("\n", $thumbnails_tab_thumbnails),
				),
			),
		);
	}
}
?>
<div <?php echo fw_attr_to_html($div_attr) ?>>
	<?php
		echo fw()->backend->option_type('hidden')->render(
			$id,
			array(),
			array(
				'value' => $data['value']['json'],
				'id_prefix' => $data['id_prefix'] .'input--',
				'name_prefix' => $data['name_prefix']
			)
		);
	?>
	<div class="builder-items-types fw-clearfix">
		<?php echo fw()->backend->render_options($tabs_options) ?>
	</div>
	<div class="builder-root-items"></div>
</div>

<?php
//do action once to add one backdrop for all builders in page
if (!(isset($option['fullscreen']) and $option['fullscreen'] === false and !did_action('fw_builder_fullscreen_add_backdrop'))) {
	do_action('fw_builder_fullscreen_add_backdrop');
}
?>

