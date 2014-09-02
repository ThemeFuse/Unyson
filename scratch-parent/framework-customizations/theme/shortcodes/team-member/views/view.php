<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( empty( $atts['image'] ) ) {
	$atts['image'] = fw_locate_theme_path_uri( '/images/no-photo-max-size.jpg' );
} else {
	$atts['image'] = $atts['image']['url'];
}
?>
<div class="our-team shortcode-container">
	<img src="<?php echo $atts['image'] ?>" alt="<?php echo $atts['name'] ?>">
	<span class="member-name"><?php echo do_shortcode( $atts['name'] ) ?></span>
	<?php if ( ! empty( $atts['job'] ) ) : { ?>
		<span
			class="member-function"><?php echo do_shortcode( $atts['job'] ) ?><?php if ( ! empty( $atts['site'] ) ) : { ?> -
				<a href="<?php echo $atts['link'] ?>"
				   target="_blank" ><?php echo $atts['site'] ?></a><?php } endif ?></span>
	<?php } endif ?>
	<div class="our-team-text"><p><?php echo do_shortcode( $atts['desc'] ) ?></p></div>
</div>