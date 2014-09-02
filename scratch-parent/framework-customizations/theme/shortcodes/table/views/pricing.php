<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

if ( empty( $atts['table']['cols'] ) || empty( $atts['table']['rows'] ) ) {
	return;
}
?>

<div class="pricing shortcode-container">
	<?php foreach( $atts['table']['cols'] as $col_key => $col ) : ?>
		<?php if( $col == 'desc-col' ) : ?>
		    <div class="clear"></div>
		<?php endif ?>
		<div class="package <?php echo $col ?>">
			<?php
			foreach( $atts['table']['rows'] as $row_key => $row ) :
			if( $row == 'heading-row' ) : ?>
				<div class="<?php echo $row ?>">
					<?php $value = $atts['table']['textarea'][$row_key][$col_key] ?>
					<span class="type"><?php echo ( $col == 'desc-col' && empty($value) ? '&nbsp;' : $value ) ?></span>
				</div>
			<?php elseif( $row == 'pricing-row' ) : ?>
				<div class="<?php echo $row ?>">
					<?php $value = $atts['table']['textarea'][$row_key][$col_key] ?>
					<span class="price"><?php echo ( $col == 'desc-col' && empty($value) ? '&nbsp;' : $value ) ?></span>
				</div>
			<?php else :?>
				<div class="<?php echo $row ?> col-row"><?php echo $atts['table']['textarea'][$row_key][$col_key] ?></div>
			<?php endif ?>
			<?php endforeach ?>
		</div>
	<?php endforeach ?>
</div>