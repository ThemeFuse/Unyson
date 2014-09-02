<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$id = uniqid( 'testimonials-' );
?>
<script>
	jQuery(document).ready(function () {
		function testimonialsInit() {
			jQuery('#<?php echo $id ?>').carouFredSel({
				swipe: {
					onTouch: true
				},
				next: "#<?php echo $id ?>-next",
				prev: "#<?php echo $id ?>-prev",
				pagination: "#<?php echo $id ?>-controls",
				infinite: false,
				items: 1,
				auto: {
					play: false,
					timeoutDuration: 10000
				},
				scroll: {
					items: 1,
					fx: "crossfade",
					easing: "linear",
					pauseOnHover: true,
					duration: 300
				}
			});
		}

		testimonialsInit();
		jQuery(window).resize(function () {
			testimonialsInit();
		});

		var tControlsHeight = jQuery('.testimonials-controls').innerHeight();
		jQuery('.testimonials-controls').css('margin-top', -tControlsHeight / 2);
	});
</script>
<div class="testimonials shortcode shortcode-container">
	<ul id="<?php echo $id ?>">
		<?php
		$counter = 1;
		foreach ( $atts['testimonials'] as $testimonial ) : {
			if ( empty( $testimonial['site_name'] ) ) {
				$testimonial['site_name'] = $testimonial['site_url'];
			}

			if ( empty( $testimonial['author_avatar'] ) ) {
				$testimonial['author_avatar'] = fw_locate_theme_path_uri( '/images/no-photo.jpg' );
			} else {
				$testimonial['author_avatar'] = wp_get_attachment_image_src( $testimonial['author_avatar']['attachment_id'], 'thumbnail' );
				$testimonial['author_avatar'] = $testimonial['author_avatar'][0];
			}
			?>
			<li data-testimonial="<?php echo $counter ++ ?>">
				<div class="testimonials-author">
					<div class="avatar"><img src="<?php echo $testimonial['author_avatar'] ?>"
					                         alt="<?php echo $testimonial['author_name'] ?>"></div>
					<span class="name"><?php echo $testimonial['author_name'] ?></span>
				<span
					class="function"><?php echo $testimonial['author_job'] ?><?php if ( ! empty( $testimonial['site_url'] ) ) : { ?> -
						<a href="<?php echo $testimonial['site_url'] ?>"
						   target="_blank" ><?php echo $testimonial['site_name'] ?></a><?php } endif ?></span>
				</div>
				<div class="testimonials-text"><p><?php echo do_shortcode( $testimonial['content'] ); ?></p></div>
			</li>
		<?php } endforeach ?>
	</ul>
	<div id="<?php echo $id ?>-controls" class="testimonials-controls"></div>
	<a id="<?php echo $id ?>-prev" class="prev" href="#"><i class="fa-angle-left"></i></a>
	<a id="<?php echo $id ?>-next" class="next" href="#"><i class="fa-angle-right"></i></a>
</div>
