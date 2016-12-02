<?php if (! defined('FW')) { die('Forbidden'); }

if ( ! function_exists( '_action_fw_register_ext_download_sources' ) ) {
	function _action_fw_register_ext_download_sources(_FW_Ext_Download_Source_Register $download_sources) {
		$download_sources->register(new FW_Ext_Download_Source_Github());
	}
}

add_action(
	'fw_register_ext_download_sources',
	'_action_fw_register_ext_download_sources'
);
