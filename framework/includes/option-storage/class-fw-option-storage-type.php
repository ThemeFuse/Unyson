<?php if (!defined('FW')) die('Forbidden');

abstract class FW_Option_Storage_Type extends FW_Type {
	/**
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	abstract protected function _save($id, array $option, $value);

	/**
	 * @param string $id
	 * @param array $option
	 * @param mixed $value
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
		return $this->_load($id, $option, $value);
	}
}
