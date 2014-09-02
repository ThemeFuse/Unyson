<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
} ?>

<div class="info shortcode-container">
	<p><?php echo do_shortcode( $atts['content'] ) ?></p>
	<a href="<?php echo $atts['button_link'] ?>" class="button"
	   target="<?php echo $atts['button_target'] ?>"><?php echo $atts['button_label'] ?></a>
</div>