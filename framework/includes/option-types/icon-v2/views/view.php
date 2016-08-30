<?php

if (! defined('FW')) { die('Forbidden'); }

/*
echo 'ID';
fw_print($id);
echo 'OPTION';
fw_print($option);
echo 'DATA';
fw_print($data);
echo 'JSON';
fw_print($json);
 */

$wrapper_attr = array(
	'class' => $option['attr']['class'] . ' fw-icon-v2-preview-' . $option['preview_size'],
	'id'    => $option['attr']['id'],
	'data-fw-modal-size' => $option['popup_size']
);

unset($option['attr']['class'], $option['attr']['id']);

?>

<div <?php echo fw_attr_to_html($wrapper_attr) ?>>
	<input <?php echo fw_attr_to_html($option['attr']) ?> type="hidden" />
</div>

