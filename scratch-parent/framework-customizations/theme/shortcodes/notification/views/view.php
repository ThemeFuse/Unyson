<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
} ?>

<div class="notification shortcode-container <?php echo $atts['type'] ?>">
	<?php
	switch ( $atts['type'] ) {
		case 'success' :
			echo '<i class="fa-check-circle"></i>';
			break;
		case 'info' :
			echo '<i class="fa-exclamation-circle"></i>';
			break;
		case 'alert' :
			echo '<i class="fa-warning"></i>';
			break;
		case 'error' :
			echo '<i class="fa-times-circle"></i>';
			break;
	}
	?>
	<span><?php echo $atts['label'] ?></span>
</div>