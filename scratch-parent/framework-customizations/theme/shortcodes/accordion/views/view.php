<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( empty( $atts['tabs'] ) ) {
	return;
}
?>
<div class="accordion shortcode-container">
	<?php foreach ( $atts['tabs'] as $tab ) : ?>
		<h3 class="accordion-title"><?php echo $tab['tab_title']; ?></h3>
		<div class="accordion-content">
			<p><?php echo do_shortcode( $tab['tab_content'] ); ?></p>
		</div>
	<?php endforeach; ?>
</div>