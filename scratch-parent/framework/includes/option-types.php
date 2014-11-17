<?php if (!defined('FW')) die('Forbidden');
/**
 * Define default framework option types
 *
 * Convention: Simple options like text|select|input|textarea, should always generate only input, without div wrappers
 */

class FW_Option_Type_Hidden extends FW_Option_Type
{
	public function get_type()
	{
		return 'hidden';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['attr']['value'] = (string)$data['value'];

		return '<input '. fw_attr_to_html($option['attr']) .' type="hidden" />';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		return (string)(is_null($input_value) ? $option['value'] : $input_value);
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => ''
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Hidden');

class FW_Option_Type_Text extends FW_Option_Type
{
	public function get_type()
	{
		return 'text';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['attr']['value'] = (string)$data['value'];

		return '<input '. fw_attr_to_html($option['attr']) .' type="text" />';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		return (string)(is_null($input_value) ? $option['value'] : $input_value);
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => ''
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Text');

class FW_Option_Type_Short_Text extends FW_Option_Type_Text
{
	public function get_type()
	{
		return 'short-text';
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['attr']['class'] .= ' fw-option-width-short';

		return parent::_render($id, $option, $data);
	}

	/**
	 * {@inheritdoc}
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}
}
FW_Option_Type::register('FW_Option_Type_Short_Text');

class FW_Option_Type_Password extends FW_Option_Type
{
	public function get_type()
	{
		return 'password';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['attr']['value'] = (string)$data['value'];

		return '<input '. fw_attr_to_html($option['attr']) .' type="password" />';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		return (string)(is_null($input_value) ? $option['value'] : $input_value);
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => ''
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Password');

class FW_Option_Type_Textarea extends FW_Option_Type
{
	public function get_type()
	{
		return 'textarea';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['value'] = (string)$data['value'];

		unset($option['attr']['value']); // be sure to remove value from attributes

		$option['attr'] = array_merge(array('rows' => '6'), $option['attr']);
		$option['attr']['class'] .= ' code';

		return '<textarea '. fw_attr_to_html($option['attr']) .'>'.
			htmlspecialchars($option['value'], ENT_COMPAT, 'UTF-8').
		'</textarea>';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		return (string)(is_null($input_value) ? $option['value'] : $input_value);
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => ''
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Textarea');

class FW_Option_Type_Html extends FW_Option_Type
{
	public function get_type()
	{
		return 'html';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['attr']['value'] = (string)$data['value'];

		$div_attr = $option['attr'];
		unset($div_attr['name']);
		unset($div_attr['value']);

		return '<div '. fw_attr_to_html($div_attr) .'>'.
			fw()->backend->option_type('hidden')->render($id, array(
					'attr' => array(
						'class' => 'fw-option-html-value',
					),
					'value' => $option['attr']['value'],
				),
				array(
					'id_prefix' => $data['id_prefix'],
					'name_prefix' => $data['name_prefix']
				)
			).
			'<div class="fw-option-html">'. $option['html'] .'</div>'.
		'</div>';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		return (string)(is_null($input_value) ? $option['value'] : $input_value);
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => '',
			'html'  => '<em>default html</em>',
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Html');

/**
 * Same html but displayed in fixed width
 */
class FW_Option_Type_Html_Fixed extends FW_Option_Type_Html
{
	public function get_type()
	{
		return 'html-fixed';
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'fixed';
	}
}
FW_Option_Type::register('FW_Option_Type_Html_Fixed');

/**
 * Same html but displayed in full width
 */
class FW_Option_Type_Html_Full extends FW_Option_Type_Html
{
	public function get_type()
	{
		return 'html-full';
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'full';
	}
}
FW_Option_Type::register('FW_Option_Type_Html_Full');

class FW_Option_Type_Checkbox extends FW_Option_Type
{
	public function get_type()
	{
		return 'checkbox';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['value'] = (bool)$data['value'];

		unset($option['attr']['value']);

		return '<input type="checkbox" name="'. esc_attr($option['attr']['name']) .'" value="" checked="checked" style="display: none">'.
			'<!-- used for "'. esc_attr($id) .'" to be present in _POST -->'.
			''.
			'<label for="'. esc_attr($option['attr']['id']) .'">'.
				'<input '. fw_attr_to_html($option['attr']) .' type="checkbox" value="true" '.
					($option['value'] ? 'checked="checked" ' : '').
				'> '. htmlspecialchars($option['text'], ENT_COMPAT, 'UTF-8').
			'</label>';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		return (bool)$input_value;
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => false,
			'text'  => __('Yes', 'fw'),
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Checkbox');

/**
 * Checkboxes list
 */
class FW_Option_Type_Checkboxes extends FW_Option_Type
{
	public function get_type()
	{
		return 'checkboxes';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['value'] = is_array($data['value']) ? $data['value'] : array();

		$div_attr = $option['attr'];
		unset($div_attr['name']);
		unset($div_attr['value']);

		$html = '<div '. fw_attr_to_html($div_attr) .'>';

		$html .= '<input type="checkbox" name="'. esc_attr($option['attr']['name']) .'[]" value="" checked="checked" style="display: none">'.
			'<!-- used for "'. esc_attr($id) .'" to be present in _POST -->';

		foreach ($option['choices'] as $value => $text) {
			$choice_id = $option['attr']['id'] .'-'. $value;

			$html .= '<div>'.
				'<label for="'. esc_attr($choice_id) .'">'.
					'<input type="checkbox" '.
						'name="'. esc_attr($option['attr']['name']) .'['. esc_attr($value) .']" '.
						'value="true" '.
						'id="'. esc_attr($choice_id) .'" '.
						(isset($option['value'][$value]) && $option['value'][$value] ? 'checked="checked" ' : '').
					'> '. htmlspecialchars($text, ENT_COMPAT, 'UTF-8').
				'</label>'.
			'</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (is_array($input_value)) {
			$value = array();

			foreach ($input_value as $choice => $val) {
				if ($val === '')
					continue;

				if (!isset($option['choices'][$choice]))
					continue;

				$value[$choice] = true;
			}
		} else {
			$value = $option['value'];
		}

		return $value;
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value'   => array(),
			'choices' => array()
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Checkboxes');

/**
 * Radio list
 */
class FW_Option_Type_Radio extends FW_Option_Type
{
	public function get_type()
	{
		return 'radio';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['value'] = (string)$data['value'];

		$div_attr = $option['attr'];
		unset($div_attr['name']);
		unset($div_attr['value']);

		$html = '<div '. fw_attr_to_html($div_attr) .'>';

		foreach ($option['choices'] as $value => $text) {
			$choice_id = $option['attr']['id'] .'-'. $value;

			$html .= '<div>'.
				'<label for="'. esc_attr($choice_id) .'">'.
					'<input type="radio" '.
						'name="'. esc_attr($option['attr']['name']) .'" '.
						'value="'. esc_attr($value) .'" '.
						'id="'. esc_attr($choice_id) .'" '.
						($option['value'] == $value ? 'checked="checked" ' : '').
					'> '. htmlspecialchars($text, ENT_COMPAT, 'UTF-8').
				'</label>'.
			'</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!isset($option['choices'][$input_value])) {
			if (
				empty($option['choices']) ||
				isset($option['choices'][ $option['value'] ])
			) {
				$input_value = $option['value'];
			} else {
				reset($option['choices']);
				$input_value = key($option['choices']);
			}
		}

		return (string)$input_value;
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value'   => '',
			'choices' => array()
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Radio');

/**
 * Select
 */
class FW_Option_Type_Select extends FW_Option_Type
{
	public function get_type()
	{
		return 'select';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['value'] = $data['value'];

		$option['attr']['data-saved-value'] = $data['value'];
		unset(
			$option['attr']['value'],
			$option['attr']['multiple']
		);

		if (!isset($option['choices'])) {
			$option['choices'] = array();
		}

		$html = '<select '. fw_attr_to_html($option['attr']) .'>'.
			$this->render_choices($option['choices'], $option['value']).
		'</select>';

		return $html;
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (empty($option['no-validate'])) {
			$all_choices = $this->get_choices($option['choices']);

			if (!isset($all_choices[$input_value])) {
				if (
					empty($all_choices) ||
					isset($all_choices[ $option['value'] ])
				) {
					$input_value = $option['value'];
				} else {
					reset($all_choices);
					$input_value = key($all_choices);
				}
			}

			unset($all_choices);
		}

		return (string)$input_value;
	}

	/**
	 * Extract recursive from optgroups all choices as one level array
	 */
	protected function get_choices($choices)
	{
		$result = array();

		foreach ($choices as $cid => $choice) {
			if (is_array($choice) && isset($choice['choices'])) {
				// optgroup
				$result += $this->get_choices($choice['choices']);
			} else {
				$result[$cid] = $choice;
			}
		}

		return $result;
	}

	protected function render_choices(&$choices, &$value)
	{
		if (empty($choices) || !is_array($choices))
			return '';

		$html = '';

		foreach ($choices as $c_value => $choice) {
			if (is_array($choice)) {
				if (!isset($choice['attr'])) {
					$choice['attr'] = array();
				}

				if (isset($choice['choices'])) { // optgroup
					$html .= '<optgroup '. fw_attr_to_html($choice['attr']) .'>'.
						$this->render_choices($choice['choices'], $value).
					'</optgroup>';
				} else { // choice as array (with custom attributes)
					$choice['attr']['value'] = $c_value;

					unset($choice['attr']['selected']); // this is not allowed

					$html .= '<option '. fw_attr_to_html($choice['attr']) .' '.
						($c_value == $value ? 'selected="selected" ' : '') .'>'.
						htmlspecialchars(isset($choice['text']) ? $choice['text'] : '', ENT_COMPAT, 'UTF-8').
					'</option>';
				}
			} else { // simple choice
				$html .= '<option value="'. esc_attr($c_value) .'" '.
					($c_value == $value ? 'selected="selected" ' : '') .'>'.
					htmlspecialchars($choice, ENT_COMPAT, 'UTF-8').
				'</option>';
			}
		}

		return $html;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value'   => '',
			'choices' => array()
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Select');

class FW_Option_Type_Short_Select extends FW_Option_Type_Select
{
	public function get_type()
	{
		return 'short-select';
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['attr']['class'] .= ' fw-option-width-short';

		return parent::_render($id, $option, $data);
	}

	/**
	 * {@inheritdoc}
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}
}
FW_Option_Type::register('FW_Option_Type_Short_Select');

/**
 * Select Multiple
 */
class FW_Option_Type_Select_Multiple extends FW_Option_Type_Select
{
	public function get_type()
	{
		return 'select-multiple';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data) {}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$option['value'] = $data['value'];

		unset($option['attr']['value']);

		$option['attr']['name'] .= '[]';
		$option['attr']['multiple'] = 'multiple';

		if (!isset($option['attr']['size'])) {
			$option['attr']['size'] = '7';
		}

		$html = '<select '. fw_attr_to_html($option['attr']) .'>'.
			$this->render_choices($option['choices'], $option['value']).
		'</select>';

		return $html;
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!is_array($input_value)) {
			$input_value = $option['value'];
		}

		if (empty($option['no-validate'])) {
			$all_choices = $this->get_choices($option['choices']);

			foreach ($input_value as $key => $value) {
				if (!isset($all_choices[$value])) {
					unset($input_value[$key]);
				}
			}

			unset($all_choices);
		}

		return $input_value;
	}

	protected function render_choices(&$choices, &$value)
	{
		if (empty($choices) || !is_array($choices))
			return '';

		$html = '';

		foreach ($choices as $c_value => $choice) {
			if (is_array($choice)) {
				if (!isset($choice['attr'])) {
					$choice['attr'] = array();
				}

				if (isset($choice['choices'])) { // optgroup
					$html .= '<optgroup '. fw_attr_to_html($choice['attr']) .'>'.
						$this->render_choices($choice['choices'], $value).
					'</optgroup>';
				} else { // choice as array (with custom attributes)
					$choice['attr']['value'] = $c_value;

					unset($choice['attr']['selected']); // this is not allowed

					$html .= '<option '. fw_attr_to_html($choice['attr']) .' '.
						(in_array($c_value, $value) ? 'selected="selected" ' : '') .'>'.
						htmlspecialchars(isset($choice['text']) ? $choice['text'] : '', ENT_COMPAT, 'UTF-8').
					'</option>';
				}
			} else { // simple choice
				$html .= '<option value="'. esc_attr($c_value) .'" '.
					(in_array($c_value, $value) ? 'selected="selected" ' : '') .'>'.
					htmlspecialchars($choice, ENT_COMPAT, 'UTF-8').
				'</option>';
			}
		}

		return $html;
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value'   => array(),
			'choices' => array()
		);
	}
}
FW_Option_Type::register('FW_Option_Type_Select_Multiple');


$dir = dirname(__FILE__);

require $dir .'/option-types/icon/class-fw-option-type-icon.php';
require $dir .'/option-types/image-picker/class-fw-option-type-image-picker.php';
require $dir .'/option-types/upload/class-fw-option-type-upload.php';
require $dir .'/option-types/color-picker/class-fw-option-type-color-picker.php';
require $dir .'/option-types/gradient/class-fw-option-type-gradient.php';
require $dir .'/option-types/background-image/class-fw-option-type-background-image.php';
require $dir .'/option-types/multi/class-fw-option-type-multi.php';
require $dir .'/option-types/switch/class-fw-option-type-switch.php';
require $dir .'/option-types/typography/class-fw-option-type-typography.php';
require $dir .'/option-types/multi-upload/class-fw-option-type-multi-upload.php';
require $dir .'/option-types/multi-picker/class-fw-option-type-multi-picker.php';
require $dir .'/option-types/wp-editor/class-fw-option-type-wp-editor.php';
require $dir .'/option-types/date-picker/class-fw-option-type-wp-date-picker.php';
require $dir .'/option-types/addable-option/class-fw-option-type-addable-option.php';
require $dir .'/option-types/addable-box/class-fw-option-type-addable-box.php';
require $dir .'/option-types/addable-popup/class-fw-option-type-addable-popup.php';
require $dir .'/option-types/multi-select/class-fw-option-type-multi-select.php';
require $dir .'/option-types/map/class-fw-option-type-map.php';
require $dir .'/option-types/datetime-range/class-fw-option-type-datetime-range.php';
require $dir .'/option-types/datetime-picker/class-fw-option-type-datetime-picker.php';
require $dir .'/option-types/radio-text/class-fw-option-type-radio-text.php';
