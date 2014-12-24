<?php if (!defined('FW')) {
	die('Forbidden');
}
/**
 * @var  string $id
 * @var  array $option
 * @var  array $data
 * @var  $value
 */

{
	$wrapper_attr = $option['attr'];

	unset(
	$wrapper_attr['value'],
	$wrapper_attr['name']
	);
}

{
	$input_attr['value'] = $value;
	$input_attr['name']  = $option['attr']['name'];
}

?>
<div <?php echo fw_attr_to_html($wrapper_attr); ?>>
	<div class="fw-irs-range-slider"></div>
	<input class="fw-irs-range-slider-hidden-input" type="hidden" <?php echo fw_attr_to_html($input_attr); ?>/>
</div>
