<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * @var array $atts
 */
?>

<div class="icon-box shortcode-container <?php echo $atts['type'] ?>">
	<i class="fa <?php echo $atts['icon'] ?>"></i>
	<?php if ( $atts['title'] ) : ?>
		<span class="list-title"><?php echo $atts['title'] ?></span>
	<?php endif ?>
	<?php if ( $atts['content'] ) : ?>
		<div class="list-separator"></div>
		<p class="text-list"><?php echo $atts['content'] ?></p>
	<?php endif ?>
</div>
