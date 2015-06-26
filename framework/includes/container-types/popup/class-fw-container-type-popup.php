<?php if (!defined('FW')) die('Forbidden');

class FW_Container_Type_Popup extends FW_Container_Type {
	public function get_type() {
		return 'popup';
	}

	protected function _get_defaults() {
		return array(
			'title' => __('More Options', 'fw'),
		);
	}

	protected function _enqueue_static($id, $option, $values, $data) {
		$uri = fw_get_framework_directory_uri('/includes/container-types/popup');

		wp_enqueue_script(
			'fw-container-type-popup',
			$uri .'/scripts.js',
			array('jquery', 'fw-events', 'fw'),
			fw()->manifest->get_version()
		);

		wp_enqueue_style('fw');
	}

	protected function _render($containers, $values, $data) {
		$html = '';

		foreach ($containers as $id => &$option) {
			$attr = $option['attr'];
			$attr['data-modal-title'] = $option['title'];
			$attr['style'] = 'padding:15px;';

			$html .=
				'<div '. fw_attr_to_html($attr) .'>'
				. fw_html_tag(
					'button',
					array(
						'type' => 'button',
						'class' => 'button button-secondary popup-button',
					),
					$option['title']
				)
				. '<div class="popup-options fw-hidden">'
				. fw()->backend->render_options($option['options'], $values, $data)
				. '</div>'
				. '</div>';
		}

		return $html;
	}
}
FW_Container_Type::register('FW_Container_Type_Popup');
