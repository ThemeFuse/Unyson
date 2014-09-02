<?php if (!defined('FW')) die('Forbidden'); ?>

<?php if (isset($data['slides'])): ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.nivoSlider').nivoSlider({effect:'fade'});
		});
	</script>
	<!--Slider-->
	<section class="wrap-nivoslider theme-default">
		<div class="nivoSlider">
			<?php foreach ($data['slides'] as $id => $slide): ?>
			<img  width='<?php echo $dimensions['width'];?>' height="<?php echo $dimensions['height']?>" src="<?php echo fw_resize($slide['src'], $dimensions['width'], $dimensions['height'], true); ?>" alt="<?php echo $slide['title'] ?>" title="#nivo-<?php echo $id ?>"/>
			<?php endforeach; ?>
		</div>
		<?php foreach ($data['slides'] as $id => $slide): ?>
		<div id="nivo-<?php echo $id ?>" class="nivo-html-caption">
			<span><?php echo $slide['title'] ?></span>
		</div>
		<?php endforeach; ?>
	</section>
	<!--/Slider-->
<?php endif; ?>
