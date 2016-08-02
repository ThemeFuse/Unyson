<?php if (! defined('FW')) { die('Forbidden'); }

class _FW_Ext_Download_Source_Register extends FW_Type_Register
{
	protected function validate_type( FW_Type $type ) {
		return $type instanceof FW_Ext_Download_Source;
	}

	public function by_source(FW_Access_Key $access_key, $source) {
		if (!isset($this->access_keys[$access_key->get_key()])) {
			trigger_error('Method call denied', E_USER_ERROR);
		}

		$download_source = null;

		foreach ($this->types as $download_source) {
			if ($download_source->get_type() === $source) {
				$download_source = $download_source;
			}
		}

		if (! $download_source) {
			trigger_error(
				"There's no such download source " . $source,
				E_USER_ERROR
			);
		}

		return $download_source;
	}
}

