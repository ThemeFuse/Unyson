<?php if (!defined('FW')) die('Forbidden'); ?>
<?php if (isset($data['slides'])): ?>
	<script type="text/javascript">
		jQuery('document').ready(function () {
			jQuery('.bxslider').bxSlider();
		});
	</script>
	<ul class="bxslider">
		<?php foreach ($data['slides'] as $slide): ?>
			<li>
				<?php if ($slide['multimedia_type'] === 'video') : ?>
					<?php echo fw_oembed_get($slide['src'], $dimensions); ?>
				<?php else: ?>
					<img src="<?php echo fw_resize($slide['src'], $dimensions['width'], $dimensions['height'], true); ?>"
					     alt="<?php echo $slide['title'] ?>" width="<?php echo $dimensions['width']; ?>"
					     height="<?php echo $dimensions['height']; ?>"/>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
