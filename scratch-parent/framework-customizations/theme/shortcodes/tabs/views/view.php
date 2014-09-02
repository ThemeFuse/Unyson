<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( empty( $atts['tabs'] ) ) {
	return;
}

$id = uniqid( 'tabs-' );
?>
<script>
	jQuery(document).ready(function ($) {
		$(function () {
			$("#<?php echo $id ?>").tabs();
		});
	});
</script>
<div class="wrap-content-tabs shortcode-container" id="<?php echo $id ?>">
	<div class="content-tabs">
		<ul>
			<?php $counter = 1;
			foreach ( $atts['tabs'] as $tab ) : { ?>
				<li><a href="#<?php echo $id . '-' . $counter ++ ?>"><?php echo $tab['tab_title'] ?></a></li>
			<?php } endforeach ?>
		</ul>
	</div>
	<?php $counter = 1;
	foreach ( $atts['tabs'] as $tab ) : { ?>
		<div class="content-tabs-text" id="<?php echo $id . '-' . $counter ++ ?>">
			<p><?php echo do_shortcode( $tab['tab_content'] ) ?></p>
		</div>
	<?php } endforeach ?>
</div>