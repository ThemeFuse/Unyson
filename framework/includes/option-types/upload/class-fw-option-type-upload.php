<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Upload extends FW_Option_Type
{
	private $views_path;
	private $js_uri;
	private $css_uri;

	public function get_type()
	{
		return 'upload';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'images_only' => true,
			'texts'       => array(),
			'value'       => '',
		);
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
	protected function _init()
	{
		$this->views_path   = dirname(__FILE__) . '/views/';
		$static_uri         = fw_get_framework_directory_uri('/includes/option-types/' . $this->get_type() . '/static/');
		$this->js_uri       = $static_uri . 'js/';
		$this->css_uri      = $static_uri . 'css/';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_media();

		{
			wp_enqueue_style(
				'fw-option-type-'. $this->get_type() . '-modal',
				$this->css_uri . 'modal.css',
				array(),
				fw()->manifest->get_version()
			);
			wp_enqueue_style(
				'fw-option-type-'. $this->get_type() . '-images-only',
				$this->css_uri . 'images-only.css',
				array(),
				fw()->manifest->get_version()
			);
			wp_enqueue_script(
				'fw-option-type-'. $this->get_type() . '-images-only',
				$this->js_uri . 'images-only.js',
				array('jquery', 'fw-events', 'underscore'),
				fw()->manifest->get_version(),
				true
			);
		}

		{
			wp_enqueue_style(
				'fw-option-type-'. $this->get_type() . '-modal',
				$this->css_uri . 'modal.css',
				array(),
				fw()->manifest->get_version()
			);
			wp_enqueue_style(
				'fw-option-type-'. $this->get_type() . '-any-files',
				$this->css_uri . 'any-files.css',
				array(),
				fw()->manifest->get_version()
			);
			wp_enqueue_script(
				'fw-option-type-'. $this->get_type() . '-any-files',
				$this->js_uri . 'any-files.js',
				array('jquery', 'fw-events'),
				fw()->manifest->get_version(),
				true
			);
		}
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		// separate attributes for the hidden input
		$input_attr = array();
		$input_attr['name']  = $option['attr']['name'];

		$input_attr['value'] = '';
		if (!empty($data['value']['attachment_id'])) {
			$input_attr['value'] = $data['value']['attachment_id'];
		} else if (!empty($option['value']['attachment_id'])) {
			$input_attr['value'] = $option['value']['attachment_id'];
		} else if (is_numeric($option['value'])) {
			$input_attr['value'] = (int) $option['value'];
		}

		unset($option['attr']['name'], $option['attr']['value']);
		$wrapper_attr = $option['attr'];

		$l10n = $option['texts'];

		if ($option['images_only']) {
			return $this->render_images_only($input_attr, $wrapper_attr, $l10n);
		} else {
			return $this->render_any_files($input_attr, $wrapper_attr, $l10n);
		}
	}

	private function render_images_only($input_attr, $wrapper_attr, $l10n)
	{
		$l10n = array_merge(
			array(
				'button_add'    => __('Add Image', 'fw'), // TODO: add context ?
				'button_edit'   => __('Edit', 'fw') // TODO: add context ?
			),
			$l10n
		);
		$wrapper_attr = array_merge($wrapper_attr, array(
			'data-l10n-button-add'  => $l10n['button_add'],
			'data-l10n-button-edit' => $l10n['button_edit'],
		));

		$wrapper_attr['class'] .= ' images-only';
		$is_empty               = empty($input_attr['value']);
		$wrapper_attr['class'] .= $is_empty ? ' empty' : '';

		return fw_render_view($this->views_path . 'images-only.php', array(
			'wrapper_attr' => $wrapper_attr,
			'input_attr'   => $input_attr,
			'is_empty'     => $is_empty,
			'l10n'         => $l10n
		));
	}

	private function render_any_files($input_attr, $wrapper_attr, $l10n)
	{
		$l10n = array_merge(
			array(
				'button_add'    => __('Upload', 'fw'), // TODO: add context ?
				'button_edit'   => __('Edit', 'fw') // TODO: add context ?
			),
			$l10n
		);
		$wrapper_attr = array_merge($wrapper_attr, array(
			'data-l10n-button-add'  => $l10n['button_add'],
			'data-l10n-button-edit' => $l10n['button_edit'],
		));

		$wrapper_attr['class'] .= ' any-files';
		$is_empty               = empty($input_attr['value']);;
		$wrapper_attr['class'] .= $is_empty ? ' empty' : '';

		return fw_render_view($this->views_path . 'any-files.php', array(
			'wrapper_attr' => $wrapper_attr,
			'input_attr'   => $input_attr,
			'is_empty'     => $is_empty,
			'l10n'         => $l10n
		));
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (empty($input_value)) {
			$defaults = $this->get_defaults();
			return $defaults['value'];
		} else {
			return $this->get_attachment_info($input_value);
		}
	}

	private function get_attachment_info($attachment_id)
	{
		$url = wp_get_attachment_url($attachment_id);
		if ($url) {
			return array(
				'attachment_id' => $attachment_id,
				'url'           => preg_replace('/^https?:\/\//', '//', $url)
			);
		} else {
			$defaults = $this->get_defaults();
			return $defaults['value'];
		}
	}
}
FW_Option_Type::register('FW_Option_Type_Upload');
