<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Storage_Type_Post_Meta extends FW_Option_Storage_Type {
	public function get_type() {
		return 'post-meta';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _save( $id, array $option, $value, array $params ) {
		if ($post_id = $this->get_post_id($option, $params)) {
			// ok
		} else {
			return $value;
		}

		$meta_id = $this->get_meta_id($id, $option, $params);

		update_post_meta($post_id, $meta_id, $value);

		return fw()->backend->option_type($option['type'])->get_value_from_input(array('type' => $option['type']), null);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _load( $id, array $option, $value, array $params ) {
		if ($post_id = $this->get_post_id($option, $params)) {
			// ok
		} else {
			return $value;
		}

		$meta_id = $this->get_meta_id($id, $option, $params);

		$meta_value = get_post_meta($post_id, $meta_id, true);

		if ($meta_value === '' && is_array($value)) {
			return $value;
		} else {
			return $meta_value;
		}
	}

	private function get_post_id($option, $params) {
		$post_id = null;

		if (!empty($option['fw-storage']['post_id'])) {
			$post_id = $option['fw-storage']['post_id'];
		} elseif (!empty($params['post_id'])) {
			$post_id = $params['post_id'];
		}

		$post_id = intval($post_id);

		if ($post_id > 0) {
			return $post_id;
		} else {
			return false;
		}
	}

	private function get_meta_id($id, $option, $params) {
		return 'fw:opt:'. (
			empty($option['fw-storage']['meta_prefix']) ? '' : $option['fw-storage']['meta_prefix'] .':'
		) . $id;
	}
}