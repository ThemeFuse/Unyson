<?php if (!defined('FW')) die('Forbidden');
/**
 * @var string $type
 * @var string $html
 */

{
	$classes = array(
		'option' => array(
			'form-field',
			'fw-backend-container',
			'fw-backend-container-type-'. $type
		),
		'content' => array(
			'fw-backend-container-content',
		),
	);

	foreach ($classes as $key => $_classes) {
		$classes[$key] = implode(' ', $_classes);
	}
	unset($key, $_classes);
}

?>
<tr class="<?php echo esc_attr($classes['option']) ?>">
	<td colspan="2" class="<?php echo esc_attr($classes['content']) ?>"><?php echo $html ?></td>
</tr>