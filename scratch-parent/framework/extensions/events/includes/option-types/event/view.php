<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array  $option
 * @var array  $data
 * @var string $id
 * @var array  $internal_options

 */

?>



<?php

	$wrapper_attr = $option['attr'];
	unset($wrapper_attr['name'], $wrapper_attr['value']);

	echo '<div ' . fw_attr_to_html($wrapper_attr) . '>';
	echo fw()->backend->render_options($internal_options, $data['value'], array(
		'id_prefix'   => $data['id_prefix'] . $id . '-',
		'name_prefix' => $data['name_prefix'] . '[' . $id . ']'
	));
	echo '</div>';

?>
