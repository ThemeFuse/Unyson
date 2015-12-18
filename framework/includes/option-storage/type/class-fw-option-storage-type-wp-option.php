<?php if (!defined('FW')) die('Forbidden');

/**
 * array(
 *  'wp-option' => 'custom_wp_option_name'
 * )
 */
class FW_Option_Storage_Type_WP_Option extends FW_Option_Storage_Type {
	public function get_type() {
		return 'wp-option';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _save( $id, array $option, $value, array $params ) {
		if ($wp_option = $this->get_wp_option($option, $params)) {
			update_option($wp_option, $value, false);

			return fw()->backend->option_type($option['type'])->get_value_from_input(
				array('type' => $option['type']), null
			);
		} else {
			return $value;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _load( $id, array $option, $value, array $params ) {
		if ($wp_option = $this->get_wp_option($option, $params)) {
			return get_option( $wp_option, $value );
		} else {
			return $value;
		}
	}

	private function get_wp_option($option, $params) {
		$wp_option = null;

		if (!empty($option['fw-storage']['wp-option'])) {
			$wp_option = $option['fw-storage']['wp-option'];
		} elseif (!empty($params['wp-option'])) {
			$wp_option = $params['wp-option'];
		}

		if ($wp_option) {
			return $wp_option;
		} else {
			return false;
		}
	}
}
