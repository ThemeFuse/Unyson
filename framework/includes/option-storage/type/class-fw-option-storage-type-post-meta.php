<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Storage_Type_Post_Meta extends FW_Option_Storage_Type {
	public function get_type() {
		return 'post-meta';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _save( $id, array $option, $value ) {
		// TODO: Implement _save() method.
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _load( $id, array $option, $value ) {
		// TODO: Implement _load() method.
	}
}
