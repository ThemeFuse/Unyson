<?php if (!defined('FW')) die('Forbidden');

/**
 * This will be returned when tried to get a not existing container type
 * to prevent fatal errors for cases when just one container type was typed wrong
 * or any other minor bug that has no sense to crash the whole site
 */
class FW_Container_Type_Undefined extends FW_Container_Type {
	public function get_type() {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $values, $data) {}

	/**
	 * {@inheritdoc}
	 */
	protected function _render($containers, $values, $data) {
		return '<p>/* Undefined Container Type */</p>';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _get_defaults() {
		return array();
	}
}
