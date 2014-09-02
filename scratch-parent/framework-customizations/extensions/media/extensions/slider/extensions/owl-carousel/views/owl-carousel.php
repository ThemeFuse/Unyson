<?php if (!defined('FW')) die('Forbidden'); ?>

<?php if (isset($data['slides'])): ?>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			$('.owl-carousel').owlCarousel({
				items: 1,
				loop: true,
				margin: 10,
				video: true,
				center: true
			})
		});
	</script>

	<div class="owl-carousel owl-theme">
		<?php foreach ($data['slides'] as $slide): ?>
			<?php if ($slide['multimedia_type'] === 'video') : ?>
				<div class="item-video" style="height:<?php echo $dimensions['height'];?>px; width:<?php echo $dimensions['width'];?>px;"><a class="owl-video" href="<?php echo $slide['src'] ?>"></a></div>
			<?php endif; ?>

		<?php endforeach; ?>
	</div>

<?php endif; ?>
