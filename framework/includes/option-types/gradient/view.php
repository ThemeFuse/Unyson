<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 */

{
	$div_attr = $option['attr'];

	unset(
		$div_attr['value'],
		$div_attr['name']
	);
}

$color_regex = '/^#[a-f0-9]{6}$/i';

?>
<div <?php echo fw_attr_to_html($div_attr) ?> >
	<div class="primary-color">
		<?php
		echo fw()->backend->option_type( 'color-picker' )->render(
			'primary',
			array(
				'type'  => 'color-picker',
				'value' => ( isset( $option['value']['primary'] ) && preg_match( $color_regex, $option['value']['primary'] ) )
						? $option['value']['primary']
						: '#ffffff',
				'attr'  => array(
					'class' => 'primary'
				)
			),
			array(
				'value'       => ( isset( $data['value']['primary'] ) && preg_match( $color_regex, $data['value']['primary'] ) )
						? $data['value']['primary']
						: $option['value']['primary'],
				'id_prefix'   => $data['id_prefix'] . $id . '-',
				'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
			)
		);
		?>
	</div>
	<?php if ( ! empty( $option['value']['secondary'] ) ): ?>
		<div class="delimiter"><?php echo __( 'to', 'fw' ) ?></div>
		<div class="secondary-color">
			<?php
			echo fw()->backend->option_type( 'color-picker' )->render(
				'secondary',
				array(
					'type'  => 'color-picker',
					'value' => ( isset( $option['value']['secondary'] ) && preg_match( $color_regex, $option['value']['secondary'] ) )
							? $option['value']['secondary']
							: '#ffffff',
					'attr'  => array(
						'class' => 'secondary'
					)
				),
				array(
					'value' => ( isset( $data['value']['secondary'] ) && preg_match( $color_regex, $data['value']['secondary'] ) )
							? $data['value']['secondary']
							: $option['value']['secondary'],
					'id_prefix'   => $data['id_prefix'] . $id . '-',
					'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
				)
			);
			?>
		</div>
	<?php endif; ?>
</div>
