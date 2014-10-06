<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 */

$wrapper_attr = $option['attr'];
unset($wrapper_attr['name']);
unset($wrapper_attr['value']);

?>

<div <?php echo fw_attr_to_html($wrapper_attr) ?> >

	<?php echo fw()->backend->option_type( 'datetime-picker' )->render( 'from', array(
			'type'  => 'datetime-picker',
			'value' =>  isset( $option['value']['from'] )
					? $option['value']['from']
					: '',
			'desc' => isset($option['descriptions']['from'])
					? $option['descriptions']['from']
					: false,

			'datetime-picker' => isset($option['datetime-pickers']['from'])
					? $option['datetime-pickers']['from']
					: array(),

			'attr'  => array(
				'class' => 'from'
			)
		), array(
			'value' =>  isset( $data['value']['from'] )
					? $data['value']['from']
					: $option['value']['from'],
			'id_prefix'   => $data['id_prefix'] . $id . '-',
			'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
		)); ?>

	<div class="delimiter"><?php echo __('to', 'fw') ?></div>

	<?php echo fw()->backend->option_type( 'datetime-picker' )->render( 'to', array(
		'type'  => 'datetime-picker',
		'value' =>  isset( $option['value']['to'] )
		? $option['value']['to']
		: '',

		'datetime-picker' => isset($option['datetime-pickers']['to'])
				? $option['datetime-pickers']['to']
				: array(),

		'desc' => isset($option['descriptions']['from'])
				? $option['descriptions']['from']
				: false,

		'attr'  => array(
		'class' => 'to'
		)
	),
		array(
			'value' =>  isset( $data['value']['to'] )
					? $data['value']['to']
					: $option['value']['to'],
			'id_prefix'   => $data['id_prefix'] . $id . '-',
			'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
		)
	); ?>

</div>