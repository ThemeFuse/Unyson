<?php if (!defined('FW')) die('Forbidden');

class FW_Container_Type_Group extends FW_Container_Type {
	public function get_type() {
		return 'group';
	}

	protected function _get_defaults() {
		return array();
	}

	protected function _enqueue_static($id, $option, $values, $data) {
		//
	}

	protected function _render($containers, $values, $data) {
		$html = '';

		foreach ( $containers as $id => &$group ) {
			// prepare attributes
			{
				$attr = isset( $group['attr'] ) ? $group['attr'] : array();

				$attr['id'] = 'fw-backend-options-group-' . $id;

				if ( ! isset( $attr['class'] ) ) {
					$attr['class'] = 'fw-backend-options-group';
				} else {
					$attr['class'] = 'fw-backend-options-group ' . $attr['class'];
				}
			}

			$html .= '<div ' . fw_attr_to_html( $attr ) . '>';
			$html .= fw()->backend->render_options( $group['options'], $values, $data );
			$html .= '</div>';
		}

		return $html;
	}
}
