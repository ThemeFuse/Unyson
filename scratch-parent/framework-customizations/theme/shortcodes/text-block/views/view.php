<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
} ?>
<div class="text-block shortcode-container">
	<?php echo do_shortcode( $atts['text'] ); ?>
</div>