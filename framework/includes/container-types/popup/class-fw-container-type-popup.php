<?php if (!defined('FW')) die('Forbidden');

class FW_Container_Type_Popup extends FW_Container_Type {
	public function get_type() {
		return 'popup';
	}

	protected function _get_defaults() {
		return array(
			'modal-size' => 'small', // small, medium, large
			'desc' => '',
		);
	}

	protected function _enqueue_static($id, $option, $values, $data) {
		$uri = fw_get_framework_directory_uri('/includes/container-types/popup');

		wp_enqueue_script(
			'fw-container-type-'. $this->get_type(),
			$uri .'/scripts.js',
			array('jquery', 'fw-events', 'fw'),
			fw()->manifest->get_version()
		);

		wp_enqueue_style('fw');

		wp_enqueue_style(
			'fw-container-type-'. $this->get_type(),
			$uri .'/styles.css',
			array(),
			fw()->manifest->get_version()
		);
	}

	protected function _render($containers, $values, $data) {
		$html = '';

		$defaults = $this->get_defaults();

		foreach ($containers as $id => &$option) {
			{
				$attr = $option['attr'];

				$attr['data-modal-title'] = $option['title'];

				if (in_array($option['modal-size'], array('small', 'medium', 'large'))) {
					$attr['data-modal-size'] = $option['modal-size'];
				} else {
					$attr['data-modal-size'] = $defaults['modal-size'];
				}

				$attr['id'] = $data['id_prefix'] . $id;
			}

			$html .=
				'<div '. fw_attr_to_html($attr) .'>'
				. '<p class="popup-button-wrapper">'
				. fw_html_tag(
					'button',
					array(
						'type' => 'button',
						'class' => 'button button-secondary popup-button',
					),
					$option['title']
				)
				. '</p>'
				. (empty($option['desc']) ? '' : ('<div class="popup-desc">'. $option['desc'] .'</div>'))
				. '<div class="popup-options fw-hidden">'
				. fw()->backend->render_options($option['options'], $values, $data)
				. '</div>'
				. '</div>';
		}

		return $html;
	}
}
