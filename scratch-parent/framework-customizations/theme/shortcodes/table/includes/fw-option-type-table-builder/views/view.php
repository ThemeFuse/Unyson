<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * @var string $id
 * @var  array $option
 * @var  array $data
 */

$last_row = max( array_keys( $data['value']['textarea'] ) );
$last_col = max( array_keys( $data['value']['textarea'][ $last_row ] ) );

$wrapper_attr = $option['attr'];
unset(
	$wrapper_attr['name'],
	$wrapper_attr['value']
);

?>

<div <?php echo fw_attr_to_html( $wrapper_attr ) ?>>

	<div class="fw-table">
		<input type="hidden" class="fw-table-last-row" value="<?php echo $last_row ?>"/>
		<input type="hidden" class="fw-table-last-col" value="<?php echo $last_col ?>"/>

		<!--start heading row -->
		<div class="fw-table-row fw-table-col-options">

			<div class="fw-table-cell fw-table-cell-options empty-cell">&nbsp;</div>

			<?php foreach ( reset( $data['value']['textarea'] ) as $key_col => $val ) : { ?>
				<?php $data_cols = array(
					'value'       => $data['value']['cols'][ $key_col ],
					'id_prefix'   => $option['attr']['id'] . '-cols-',
					'name_prefix' => $option['attr']['name'] . '[cols]'
				);
				?>

				<div class="fw-table-cell fw-table-col-option <?php echo $data['value']['cols'][ $key_col ] ?>"
				     data-col="<?php echo $key_col ?>">
					<?php echo fw()->backend->option_type( 'select' )->render( $key_col, $option['columns_options'], $data_cols ); ?>
					<a href="#"
					   class="fw-table-add-column button button-large"><?php echo __( 'Add Column', 'fw' ) ?></a>
				</div>
			<?php } endforeach; ?>

			<div class="fw-table-cell fw-table-row-delete empty-cell">&nbsp;</div>

		</div>
		<!--end heading row -->


		<!--start data rows -->
		<?php foreach ( $data['value']['textarea'] as $key_row => $row ) : { ?>

			<?php $data_rows = array(
				'value'       => $data['value']['rows'][ $key_row ],
				'id_prefix'   => $option['attr']['id'] . '-rows-',
				'name_prefix' => $option['attr']['name'] . '[rows]'
			);?>

			<div class="fw-table-row <?php echo $data['value']['rows'][ $key_row ] ?>"
			     data-row="<?php echo $key_row ?>">
				<div class='fw-table-cell fw-table-cell-options <?php echo $data['value']['rows'][ $key_row ] ?>'>
					<?php echo fw()->backend->option_type( 'select' )->render( $key_row, $option['row_options'], $data_rows ); ?>
				</div>

				<?php foreach ( $row as $key_col => $cell_value ): { ?>
					<div class='fw-table-cell fw-table-cell-worksheet <?php echo $data['value']['cols'][ $key_col ] ?>'
					     data-col="<?php echo $key_col ?>">
						<div class="fw-table-cell-content"><?php echo $cell_value ?></div>
						<?php echo '<textarea id="' . $option['attr']['id'] . '-textarea-' . $key_row . '-' . $key_col . '" name="' . $option['attr']['name'] . '[textarea][' . $key_row . '][' . $key_col . ']" value="' . $cell_value . '">' . $cell_value . '</textarea>' ?>
					</div>
				<?php } endforeach; ?>

				<div class="fw-table-cell fw-table-row-delete">
					<i class="fw-table-row-delete-btn fw-x-button dashicons fw-x"></i>
				</div>

			</div>

		<?php } endforeach; ?>
		<!--end data rows -->

		<!--start template row-->
		<div class="fw-table-row fw-template-row">

			<div class='fw-table-cell fw-table-cell-options'>
				<?php $data_rows = array(
					'value'       => '',
					'id_prefix'   => $option['attr']['id'] . '-rows-',
					'name_prefix' => $option['attr']['name'] . '[rows]'
				);

				?>
				<?php echo fw()->backend->option_type( 'select' )->render( '_template_key_row_', $option['row_options'], $data_rows ); ?>
			</div>

			<?php foreach ( reset( $data['value']['textarea'] ) as $key_col => $val ) : { ?>
				<?php $data_cols = array(
					'value'       => $data['value']['cols'][ $key_col ],
					'id_prefix'   => $option['attr']['id'] . '-cols-',
					'name_prefix' => $option['attr']['name'] . '[cols]'
				);
				?>
				<div class='fw-table-cell fw-table-cell-worksheet <?php echo $data['value']['cols'][ $key_col ] ?>'
				     data-col="<?php echo $key_col ?>">
					<div class="fw-table-cell-content"></div>

					<?php echo '<textarea id="' . $option['attr']['id'] . '-textarea-_template_key_row_-_template_key_col_" name="' . $option['attr']['name'] . '[textarea][_template_key_row_][_template_key_col_]" value=""></textarea>' ?>
				</div>
			<?php } endforeach; ?>

			<div class="fw-table-cell fw-table-row-delete">
				<i class="dashicons fw-x fw-table-row-delete-btn"></i>
			</div>

		</div>
		<!--end template row-->

		<!--start delete buttons row -->
		<div class="fw-table-row fw-table-cols-delete">

			<div class="fw-table-cell fw-table-cell-options"><a href="#"
			                                                    class="fw-table-add-row button button-large"><?php echo __( 'Add Row', 'fw' ) ?></a>
			</div>

			<?php foreach ( reset( $data['value']['textarea'] ) as $key_col => $val ) : { ?>
				<?php $data_cols = array(
					'value'       => $data['value']['cols'][ $key_col ],
					'id_prefix'   => $option['attr']['id'] . '-cols-',
					'name_prefix' => $option['attr']['name'] . '[cols]'
				);
				?>
				<div class="fw-table-cell fw-table-col-delete <?php echo $data['value']['cols'][ $key_col ] ?>"
				     data-col="<?php echo $key_col ?>">
					<i class="dashicons fw-x"></i>
				</div>
			<?php } endforeach; ?>

			<div class="fw-table-cell fw-table-row-delete empty-cell">&nbsp;</div>

		</div>
		<!--end delete buttons row -->

	</div>

</div>