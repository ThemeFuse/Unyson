<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 * @var  array $custom_choice_key
 */

{
	$wrapper_attr = $option['attr'];

	unset(
		$wrapper_attr['value'],
		$wrapper_attr['name']
	);
}

?>
<div <?php echo fw_attr_to_html($wrapper_attr) ?>>
	<div class="predefined">
		<?php
		echo fw()->backend->option_type( 'radio' )->render(
			'predefined',
			array(
				'value'   => '',
				'choices' => $option['choices']
			),
			array(
				'value'       => isset($option['choices'][ $data['value'] ]) ? $data['value'] : $custom_choice_key,
				'id_prefix'   => $data['id_prefix'] . $id . '-',
				'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
			)
		);
		?>
	</div>

	<div class="custom">
		<?php
		echo fw()->backend->option_type( 'text' )->render(
			'custom',
			array(),
			array(
				'value'       => isset($option['choices'][ $data['value'] ]) ? '' : $data['value'],
				'id_prefix'   => $data['id_prefix'] . $id . '-',
				'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
			)
		);
		?>
	</div>
</div>