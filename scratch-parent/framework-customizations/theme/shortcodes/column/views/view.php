<?php if (!defined('FW')) die('Forbidden');

/**
 * @var $atts The column shortcode attributes
 * @var $content The column shortcode content
 * @var $tag
 */

$first_row = '';

if( isset( $atts['first_in_row'] ) && $atts['first_in_row'] == 'true' ) {
	$first_row = 'first';
}

?>

<div class="shortcode shortcode-column column-<?php echo $atts['type']; ?> <?php echo $first_row ?>"><?php echo do_shortcode($content); ?></div>