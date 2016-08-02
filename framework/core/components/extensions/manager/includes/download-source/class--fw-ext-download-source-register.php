<?php if (! defined('FW')) { die('Forbidden'); }

class _FW_Ext_Download_Source_Register extends FW_Type_Register
{
	protected function validate_type( FW_Type $type ) {
		return $type instanceof FW_Ext_Download_Source;
	}
}

