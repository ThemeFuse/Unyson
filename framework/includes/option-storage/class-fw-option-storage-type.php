<?php if (!defined('FW')) die('Forbidden');

abstract class FW_Option_Storage_Type extends FW_Type {
	/**
	 * Save the value in another place and return a value that will be save in regular place (same as before this feature)
	 *
	 * @param string $id
	 * @param array $option
	 * @param mixed $value Current option (regular) value
	 * @param array $params
	 *
	 * @return mixed
	 */
	abstract protected function _save($id, array $option, $value, array $params);

	/**
	 * Load the value saved in custom place
	 *
	 * @param string $id
	 * @param array $option
	 * @param mixed $value Current option (regular) value
	 * @param array $params
	 *
	 * @return mixed
	 */
	abstract protected function _load($id, array $option, $value, array $params);

	/**
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 * @param array $params
	 *
	 * @return mixed
	 */
	final public function save($id, array $option, $value, array $params = array()) {
		if (
			!empty($option['fw-storage'])
			&&
			($storage = is_array($option['fw-storage'])
				? $option['fw-storage']
				: array('type' => $option['fw-storage'])
			)
			&&
			!empty($storage['type'])
			&&
			$storage['type'] === $this->get_type()
		) {
			$option['fw-storage'] = $storage;
		} else {
			return $value;
		}

		return $this->_save($id, $option, $value, $params);
	}

	/**
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 * @param array $params
	 *
	 * @return mixed
	 */
	final public function load($id, array $option, $value, array $params = array()) {
		if (
			!empty($option['fw-storage'])
			&&
			($storage = is_array($option['fw-storage'])
				? $option['fw-storage']
				: array('type' => $option['fw-storage'])
			)
			&&
			!empty($storage['type'])
			&&
			$storage['type'] === $this->get_type()
		) {
			$option['fw-storage'] = $storage;
		} else {
			return $value;
		}

		// fixme: if post meta is deleted, will be returned an invalid value
		// maybe run through get_value_from_input() $option['value'] ?

		return $this->_load($id, $option, $value, $params);
	}
}
