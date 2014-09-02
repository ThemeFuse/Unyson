<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
?>
<span class="shortcode-icon shortcode-container">
	<i class="<?php echo $atts['icon'] ?>"></i>
<?php if ( ! empty( $atts['tooltip'] ) ) : { ?>
	<br/>
	<span class="list-title"><?php echo $atts['tooltip'] ?></span>
<?php } endif ?>
</span>