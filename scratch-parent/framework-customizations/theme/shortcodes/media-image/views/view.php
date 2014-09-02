<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

if ( empty( $atts['image'] ) ) {
	return;
}

$width  = ( is_numeric( $atts['width'] ) && ( $atts['width'] > 0 ) ) ? $atts['width'] : '';
$height = ( is_numeric( $atts['height'] ) && ( $atts['height'] > 0 ) ) ? $atts['height'] : '';
if ( ! empty( $width ) && ! empty( $height ) ) {
	$image = fw_resize( $atts['image']['attachment_id'], $width, $height, true );
} else {
	$image = $atts['image']['url'];
}
?>

<?php if ( empty( $atts['link'] ) ) : ?>
	<img src="<?php echo $image ?>" alt="<?php echo $image ?>" width="<?php echo $width ?>"
	     height="<?php echo $height ?>" class="shortcode-container"/>
<?php else : ?>
	<a href="<?php echo $atts['link'] ?>" target="<?php echo $atts['target'] ?>" class="shortcode-container">
		<img src="<?php echo $image ?>" alt="<?php echo $image ?>" width="<?php echo $width ?>"
		     height="<?php echo $height ?>"/>
	</a>
<?php endif ?>
