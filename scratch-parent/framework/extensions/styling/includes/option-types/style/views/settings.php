<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }
/**
 * @var  string $id
 * @var  array $option
 * @var  array $data
 * @var  array $settings
 */
?>
<div class="fw-options-tabs-wrapper fw-option-type-style-settings fw-options-tabs-first-level"
     data-option-name="<?php echo esc_attr($id); ?>">
	<div class="fw-options-tabs-list fw-option-type-style-settings-tabs">
		<ul>
			<?php foreach ( $option['blocks'] as $block_id => $block ): ?>
				<li><a href="#fw-options-tab-<?php echo esc_attr( $block_id ) ?>"
				       class="nav-tab"><?php echo htmlspecialchars( $block['title'], ENT_COMPAT, 'UTF-8' ) ?></a></li>
			<?php endforeach; ?>
		</ul>
		<div class="fw-clear"></div>
	</div>

	<div class="fw-options-tabs-contents metabox-holder">
		<?php foreach ( $option['blocks'] as $block_id => $block ): ?>
			<?php $elements = ( ! empty( $block['elements'] ) ) ? $block['elements'] : array(); ?>
			<div class="fw-options-tab" id="fw-options-tab-<?php echo esc_attr( $block_id ); ?>"
			     data-block="<?php echo esc_attr( $block_id ); ?>">
				<?php
				// Render before options
				if ( ! empty( $block['before'] ) ):
					$before_block_values = ( isset( $data['value']['blocks'][ $block_id ]['before'] ) )
						? $data['value']['blocks'][ $block_id ]['before']
						: array();

					echo fw()->backend->render_options( $block['before'], $before_block_values, array(
						'id_prefix'   => $data['id_prefix'] . $id . '-' . $block_id . '-style-option-before-',
						'name_prefix' => $data['name_prefix'] . '[' . $id . ']' . '[' . $block_id . '][before]',
					) );
				endif;

				// Typography Tags
				foreach ( array_intersect( array_values( $elements ), array_keys( $settings['typography_tags'] ) ) as $tag ): ?>
					<div class="fw-option-type-style-option typo fw-clearfix <?php echo esc_attr($tag) ?>" data-css-selector="<?php echo esc_attr($tag) ?>">
						<?php
						$tmp_options = array(
							$tag => array(
								'type'  => 'typography',
								'label' => $settings['typography_tags'][ $tag ],
								'desc'  => false,
							)
						);

						$tmp_values = array(
							$tag => array_merge(
								$settings['default_values']['typography'][ $tag ],
								( ! empty( $option['value'][ $block_id ][ $tag ] ) )
									? $option['value'][ $block_id ][ $tag ]
									: array(),
								( ! empty( $data['value']['blocks'][ $block_id ][ $tag ] ) )
									? $data['value']['blocks'][ $block_id ][ $tag ]
									: array()
							)
						);

						echo fw()->backend->render_options(
							$tmp_options,
							$tmp_values,
							array(
								'id_prefix'   => $data['id_prefix'] . $id . '-' . $block_id .  '-typography-option-',
								'name_prefix' => $data['name_prefix'] . '[' . $id . ']' . '[' . $block_id . ']',
							)
						);

						unset($tmp_options, $tmp_values);
						?>
					</div>
				<?php
				endforeach;

				// Links
				foreach ( array_intersect( array_values( $elements ), array_keys( $settings['links'] ) ) as $link ): ?>
					<div class="fw-option-type-style-option link <?php echo esc_attr($link); ?>" data-css-selector="<?php echo $link; ?>">
						<?php
						$tmp_options = array(
							$link => array(
								'type'  => 'color-picker',
								'label' => $settings['links'][ $link ],
								'desc'  => false,
							)
						);

						$tmp_values = array(
							$link => ( ! empty( $data['value']['blocks'][ $block_id ][ $link ] ) )
								? $data['value']['blocks'][ $block_id ][ $link ]
								: $option['value'][ $block_id ][ $link ]
						);

						echo fw()->backend->render_options(
							$tmp_options,
							$tmp_values,
							array(
								'id_prefix'   => $data['id_prefix'] . $id . '-styling-option-',
								'name_prefix' => $data['name_prefix'] . '[' . $id . ']' . '[' . $block_id . ']',
							)
						);

						unset($tmp_options, $tmp_values);
						?>
					</div>
				<?php
				endforeach;

				// Background
				if ( in_array( 'background', $elements ) ):
					?>
					<div class="fw-option-type-style-option background background-color">
						<?php
						$tmp_options = array(
							'background-color' => array(
								'type'  => 'gradient',
								'label' => __( 'Background', 'fw' ),
								'desc'  => false,
								'value' => array(
									'primary' => '#ffffff',
									'secondary' => '#ffffff',
								)
							)
						);

						$tmp_values = array(
							'background-color' => array(
								'primary' => ( ! empty( $data['value']['blocks'][ $block_id ]['background']['background-color']['primary'] ) )
										? $data['value']['blocks'][ $block_id ]['background']['background-color']['primary']
										: $option['value'][ $block_id ]['background']['background-color']['primary'],
								'secondary' => ( ! empty( $data['value']['blocks'][ $block_id ]['background']['background-color']['secondary'] ) )
										? $data['value']['blocks'][ $block_id ]['background']['background-color']['secondary']
										: $option['value'][ $block_id ]['background']['background-color']['secondary']
							)
						);

						echo fw()->backend->render_options(
							$tmp_options,
							$tmp_values,
							array(
								'id_prefix'   => $data['id_prefix'] . $id . '-' . $block_id . '-background-color-',
								'name_prefix' => $data['name_prefix'] . '[' . $id . ']' . '[' . $block_id . '][background]',
							)
						);

						unset($tmp_options, $tmp_values);
						?>
					</div>

					<div class="fw-option-type-style-option background background-image">
						<?php
						if (
							isset( $data['value']['predefined'] )
							&&
							in_array( $data['value']['predefined'], array_keys( $option['predefined'] ) )
							&&
							( isset( $option['predefined'][ $data['value']['predefined'] ]['blocks'][ $block_id ]['background']['background-image'] ) )
						) {
							$background_image_option = $option['predefined'][ $data['value']['predefined'] ]['blocks'][ $block_id ]['background']['background-image'];
						} else {
							$background_image_option = $option['value'][ $block_id ]['background']['background-image'];
						}
						?>
						<input type="hidden" class="background-image-data"
						       id="<?php echo esc_attr($data['name_prefix'] . '-' . $id . '-' . $block_id . '-background-image-data'); ?>"
						       name="<?php echo esc_attr($data['name_prefix'] . '[' . $id . ']' . '[' . $block_id . '][background][background-image][data]'); ?>"
						       value="<?php echo fw_htmlspecialchars(json_encode( $background_image_option )); ?>">
						<?php
						$tmp_options = array(
							'background-image' => array_merge(
								$background_image_option,
								array(
									'label' => ' ',
									'type'  => 'background-image'
								)
							)
						);

						$tmp_values = array(
							'background-image' => ( ! empty( $data['value']['blocks'][ $block_id ]['background']['background-image'] ) )
									? $data['value']['blocks'][ $block_id ]['background']['background-image']
									: $option['value'][ $block_id ]['background']['background-image']
						);

						echo fw()->backend->render_options(
							$tmp_options,
							$tmp_values,
							array(
								'id_prefix'   => $data['id_prefix'] . $id . '-' . $block_id . '-',
								'name_prefix' => $data['name_prefix'] . '[' . $id . ']' . '[' . $block_id . '][background]',
							)
						);

						unset($tmp_options, $tmp_values);
						?>
					</div>
				<?php
				endif;

				// Render after options
				if ( ! empty( $block['after'] ) ) {
					$after_block_values = ( isset( $data['value']['blocks'][ $block_id ]['after'] ) )
						? $data['value']['blocks'][ $block_id ]['after']
						: array();

					echo fw()->backend->render_options( $block['after'], $after_block_values, array(
						'id_prefix'   => $data['id_prefix'] . $id . '-' . $block_id . '-styling-option-after-',
						'name_prefix' => $data['name_prefix'] . '[' . $id . ']' . '[' . $block_id . '][after]',
					) );
				}
				?>
			</div>
			<?php
			unset( $option['blocks'][ $block_id ] ); // free memory after printed, not needed anymore
		endforeach;
		?>
	</div>
	<div class="fw-clear"></div>
</div>
