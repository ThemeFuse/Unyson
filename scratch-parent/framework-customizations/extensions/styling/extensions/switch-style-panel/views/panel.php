<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * @var  array $options
 * @var  string $description
 */
?>
<!--Style Panel-->
<div class="wrap-style-panel close">
	<h2 class="title-panel"><?php echo $description; ?></h2>
	<ul class="list-style" data-blocks='<?php echo json_encode( $options['blocks'] ); ?>'>
		<?php foreach ( $options['predefined'] as $key => $style ) : ?>
			<li><a data-key="<?php echo $key; ?>" data-settings='<?php echo json_encode( $style ) ?>'
			       href="#"><?php echo $style['name']; ?></a></li>
		<?php endforeach; ?>
	</ul>
	<a href="#" class="open-close-panel"><i class="fa-sliders"></i></a>
</div>
<script type="text/javascript">
	(function ($) {
		$('.wrap-style-panel.close ul.list-style li a').each(function () {
			$(this).data('settings', JSON.parse($(this).attr('data-settings')));
			$(this).removeAttr('data-settings');
		});
	})(jQuery);
</script>
<!--/Style Panel-->
