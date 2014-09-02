<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
?>

<table class="table-performance shortcode-container">
	<?php foreach( $atts['table']['rows'] as $row_key => $row ) : ?>
	    <?php if( $row == 'heading-row' ) : ?>
	        <thead>
	        <tr class="<?php echo $row ?>">
		        <?php foreach( $atts['table']['cols'] as $col_key => $col ) : ?>
		            <th class="<?php echo $col ?>"><?php echo $atts['table']['textarea'][$row_key][$col_key] ?></th>
		        <?php endforeach ?>
	        </tr>
	        </thead>
	    <?php else : ?>
			<tr class="<?php echo $row ?>">
				<?php foreach( $atts['table']['cols'] as $col_key => $col ) : ?>
					<td class="<?php echo $col ?>"><?php echo $atts['table']['textarea'][$row_key][$col_key] ?></td>
				<?php endforeach ?>
			</tr>
		<?php endif ?>
	<?php endforeach ?>
</table>