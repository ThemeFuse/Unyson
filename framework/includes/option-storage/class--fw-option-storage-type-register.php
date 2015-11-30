<?php if (!defined('FW')) die('Forbidden');

/**
 * @internal
 */
class _FW_Option_Storage_Type_Register extends FW_Type_Register {
	protected function validate_type(FW_Type $type) {
		return $type instanceof FW_Option_Storage_Type;
	}
}
