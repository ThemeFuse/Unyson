<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

if( empty($atts['title']) ) {
	return;
}
?>

<?php if( empty($atts['subtitle']) ) : ?>
	<<?php echo $atts['heading'] ?> class="shortcode-container"><?php echo $atts['title'] ?></<?php echo $atts['heading'] ?>>
<?php endif ?>
<?php if( !empty($atts['subtitle']) ) : ?>
	<<?php echo $atts['heading'] ?>><?php echo $atts['title'] ?></<?php echo $atts['heading'] ?>>
	<span class="subtitle-article shortcode-container"><?php echo $atts['subtitle'] ?></span>
<?php endif ?>