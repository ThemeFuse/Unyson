<?php if (!defined('FW')) die('Forbidden');

abstract class FW_Option_Storage_Type extends FW_Type {
	/**
	 * Save the value in another place and return a value that will be save in regular place (same as before this feature)
	 *
	 * @param string $id
	 * @param array $option
	 * @param mixed $value Current option (regular) value
	 *
	 * @return mixed
	 */
	abstract protected function _save($id, array $option, $value);

	/**
	 * Load the value saved in custom place
	 *
	 * @param string $id
	 * @param array $option
	 * @param mixed $value Current option (regular) value
	 *
	 * @return mixed
	 */
	abstract protected function _load($id, array $option, $value);

	/**
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	final public function save($id, array $option, $value = null) {
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

		return $this->_save($id, $option, $value);
	}

	/**
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	final public function load($id, array $option, $value = null) {
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

		return $this->_load($id, $option, $value);
	}
}
