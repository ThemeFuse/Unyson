<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var $div_attr
 * @var $atts
 * @var $content
 * @var $tag
 */

$div_attr['class'] =  fw_akg('class', $div_attr, '') . ' fw-shortcode-map-wrapper shortcode-container';
?>

<div <?php echo fw_attr_to_html($div_attr) ?>>
<div class="unyson_map_canvas"></div>
</div>