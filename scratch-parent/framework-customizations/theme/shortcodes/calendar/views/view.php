<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 *
 * @var $wrapper_atts
 * @var $default_template
 * @var $atts
 * @var $content
 * @var $tag
 */
?>

<?php $wrapper_atts['class'] =  fw_akg('class', $wrapper_atts, '') . ' container fw-shortcode-calendar-wrapper shortcode-container' ?>

<div <?php echo fw_attr_to_html($wrapper_atts); ?>>

	<div class="clearfix"></div>
	<div class="page-header hidden-header">

	<div class="pull-right form-inline">
		<div class="btn-group">
			<button data-calendar-nav="prev"><i class="fa fa-angle-left"></i></button>
			<button data-calendar-nav="today"><?php echo __('Today','fw')?></button>
			<button data-calendar-nav="next"><i class="fa fa-angle-right"></i></button>
		</div>
	</div>

	<h3><!-- Here will be set the title --></h3>

	</div>

	<div class="row">
		<div class="col-xs-12 col-lg-12 col-xl-12 col-sm-12">
			<div class="fw-shortcode-calendar"></div>
		</div>
	</div>

</div>
