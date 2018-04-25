<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Multi_Upload extends FW_Option_Type
{

	private $views_path;
	private $js_uri;
	private $css_uri;

	public function get_type()
	{
		return 'multi-upload';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'images_only'      => true,
			'texts'            => array(),
			'value'            => array(),
			'files_ext'        => array(),
			'extra_mime_types' => array()
		);
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'auto';
	}

	protected function _get_data_for_js($id, $option, $data = array()) {
		return false;
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
				array('jquery', 'fw-events', 'underscore', 'jquery-ui-sortable'),
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
		// attributes for the hidden input
		$input_attr = array();
		$input_attr['name']  = $option['attr']['name'];

		$input_attr['value'] = $this->get_processed_value($data['value']);

		unset($option['attr']['name'], $option['attr']['value']);

		// attributes for the option wrapper
		$wrapper_attr = $option['attr'];

		$l10n = $option['texts'];

		if ( ! empty( $option['files_ext'] ) ) {
			$ext_files  = is_array( $option['files_ext'] ) ? $option['files_ext'] : explode( ',', $option['files_ext'] );
			$ext_files  = array_unique( $ext_files );
			$mime_types = fw_get_mime_type_by_ext( $ext_files );
			$collector  = array(
				'ext_files'  => $ext_files,
				'mime_types' => $mime_types,
				'extra_mime_types' => $option['extra_mime_types']
			);

			$wrapper_attr['data-files-details'] = json_encode( $collector );

			$check_file_types      = fw_multi_ext2type( $ext_files );
			$option['images_only'] = ( ! empty( $check_file_types ) && count( $check_file_types ) === 1 && $check_file_types[0] === 'image' );
		}

		if ($option['images_only']) {
			return $this->render_images_only($input_attr, $wrapper_attr, $l10n);
		} else {
			return $this->render_any_files($input_attr, $wrapper_attr, $l10n);
		}
	}

	private function get_processed_value($value)
	{
		if (
			!is_array($value) ||
			empty($value)
		) {
			$defaults = $this->get_defaults();
			return json_encode($defaults['value']);
		}

		$ids = array();

		foreach ($value as $attachment) {
			$ids[] = $attachment['attachment_id'];
		}

		return json_encode($ids);
	}

	private function render_images_only($input_attr, $wrapper_attr, $l10n)
	{
		$l10n = array_merge(
			array(
				'button_add'    => __('Add Images', 'fw'), // TODO: add context ?
				'button_edit'   => __('Edit', 'fw'), // TODO: add context ?
				'modal_title'   => __('Select Images', 'fw'),
			),
			$l10n
		);
		$wrapper_attr = array_merge($wrapper_attr, array(
			'data-l10n-button-add'  => $l10n['button_add'],
			'data-l10n-button-edit' => $l10n['button_edit'],
			'data-l10n-modal-title' => $l10n['modal_title'],
		));

		$wrapper_attr['class'] .= ' images-only';
		$is_empty               = $input_attr['value'] === '[]'; // check for empty json array
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
				'button_add'  => __('Upload', 'fw'), // TODO: add context ?
				'button_edit' => __('Edit', 'fw'), // TODO: add context ?
				'files_one'   => __('1 File', 'fw'), // TODO: maybe add context ?
				'files_more'  => __('%u Files', 'fw'), // TODO: maybe add context ?
			),
			$l10n
		);
		$wrapper_attr = array_merge($wrapper_attr, array(
			'data-l10n-button-add'  => $l10n['button_add'],
			'data-l10n-button-edit' => $l10n['button_edit'],
			'data-l10n-files-one'   => $l10n['files_one'],
			'data-l10n-files-more'  => $l10n['files_more'],
		));

		$wrapper_attr['class'] .= ' any-files';
		$is_empty               = $input_attr['value'] === '[]'; // check for empty json array
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
			return $option['value'];
		} else {
			return $this->get_attachments_info($input_value, $option);
		}
	}

	private function get_attachments_info($attachment_ids, $option)
	{
		$decoded_ids = $attachment_ids;

		if ( is_string( $attachment_ids ) ) {
			$decoded_ids = json_decode( $attachment_ids, true );

			if ( ! is_array( $decoded_ids ) ) {
				return $option['value'];
			}
		}

		$return_arr = array();

		foreach ( $decoded_ids as $id ) {

			if ( isset( $id['attachment_id'] ) ) {
				$id = $id['attachment_id'];
			}

			$url = wp_get_attachment_url( $id );

			if ( $url ) {

				$data = array(
					'attachment_id' => $id,
					'url'           => preg_replace( '/^https?:\/\//', '//', $url )
				);

				if ( isset( $option['sizes'] ) && is_array( $option['sizes'] ) ) {

					$sizes = array();
					$info  = wp_prepare_attachment_for_js( $id );

					foreach ( $option['sizes'] as $size ) {

						if ( isset( $info['sizes'][ $size ] ) ) {
							$sizes[ $size ] = $info['sizes'][ $size ];
						}
					}

					if ( ! empty( $sizes ) ) {
						$data['sizes'] = $sizes;
					}
				}

				$return_arr[] = $data;
			}
		}

		return $return_arr;
	}
}
