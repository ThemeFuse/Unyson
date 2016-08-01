<?php if (!defined('FW')) die('Forbidden');

class _FW_Available_Extensions_Register extends FW_Type_Register {
	protected function validate_type( FW_Type $type ) {
		return $type instanceof FW_Available_Extension;
	}
}
