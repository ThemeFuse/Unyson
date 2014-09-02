<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( 'line' === $atts['style']['ruler_type'] ): ?>
	<div class="shortcode-container hr">
		<hr/>
	</div>
<?php endif; ?>

<?php if ( 'space' === $atts['style']['ruler_type'] ): ?>
	<div class="divider no-border shortcode-container"
	     style="padding-bottom: <?php echo (int) $atts['style']['space']['height'] ?>px;"></div>
<?php endif; ?>