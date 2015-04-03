<?php if (!defined('FW')) die('Forbidden');

$dir = dirname(__FILE__);

require $dir . '/simple.php';

require $dir . '/icon/class-fw-option-type-icon.php';
require $dir . '/image-picker/class-fw-option-type-image-picker.php';
require $dir . '/upload/class-fw-option-type-upload.php';
require $dir . '/color-picker/class-fw-option-type-color-picker.php';
require $dir . '/gradient/class-fw-option-type-gradient.php';
require $dir . '/background-image/class-fw-option-type-background-image.php';
require $dir . '/multi/class-fw-option-type-multi.php';
require $dir . '/switch/class-fw-option-type-switch.php';
require $dir . '/typography/class-fw-option-type-typography.php';
require $dir . '/multi-upload/class-fw-option-type-multi-upload.php';
require $dir . '/multi-picker/class-fw-option-type-multi-picker.php';
require $dir . '/wp-editor/class-fw-option-type-wp-editor.php';
require $dir . '/date-picker/class-fw-option-type-wp-date-picker.php';
require $dir . '/addable-option/class-fw-option-type-addable-option.php';
require $dir . '/addable-box/class-fw-option-type-addable-box.php';
require $dir . '/addable-popup/class-fw-option-type-addable-popup.php';
require $dir . '/map/class-fw-option-type-map.php';
require $dir . '/datetime-range/class-fw-option-type-datetime-range.php';
require $dir . '/datetime-picker/class-fw-option-type-datetime-picker.php';
require $dir . '/radio-text/class-fw-option-type-radio-text.php';
require $dir . '/popup/class-fw-option-type-popup.php';
require $dir . '/slider/class-fw-option-type-slider.php';
require $dir . '/range-slider/class-fw-option-type-range-slider.php';
require $dir . '/rgba-color-picker/class-fw-option-type-rgba-color-picker.php';
if (!class_exists('FW_Option_Type_Multi_Select')) {
  require $dir . '/multi-select/class-fw-option-type-multi-select.php';
}
