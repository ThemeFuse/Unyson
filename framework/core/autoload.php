<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

spl_autoload_register( '_fw_core_autoload' );
function _fw_core_autoload( $class ) {
	switch ( $class ) {
		case 'FW_Manifest' :
		case 'FW_Framework_Manifest' :
		case 'FW_Theme_Manifest' :
		case 'FW_Extension_Manifest' :
			require_once dirname( __FILE__ ) . '/class-fw-manifest.php';
			break;
	}
}

spl_autoload_register( '_fw_core_components_autoload' );
function _fw_core_components_autoload( $class ) {
	switch ( $class ) {
		case '_FW_Component_Backend' :
			require_once dirname( __FILE__ ) . '/components/backend.php';
			break;
		case '_FW_Component_Extensions' :
			require_once dirname( __FILE__ ) . '/components/extensions.php';
			break;
		case '_FW_Component_Theme' :
			require_once dirname( __FILE__ ) . '/components/theme.php';
			break;
		case 'FW_Settings_Form_Theme' :
			require_once dirname( __FILE__ ) . '/components/backend/class-fw-settings-form-theme.php';
			break;
	}
}

spl_autoload_register( '_fw_core_components_extensions_autoload' );
function _fw_core_components_extensions_autoload( $class ) {
	switch ( $class ) {
		case 'FW_Extension_Default' :
			require_once dirname( __FILE__ ) . '/components/extensions/class-fw-extension-default.php';
			break;
		case '_FW_Extensions_Manager' :
			require_once dirname( __FILE__ ) . '/components/extensions/manager/class--fw-extensions-manager.php';
			break;
		case '_FW_Extensions_Delete_Upgrader_Skin' :
			require_once dirname( __FILE__ ) . '/components/extensions/manager/includes/class--fw-extensions-delete-upgrader-skin.php';
			break;
		case '_FW_Extensions_Install_Upgrader_Skin' :
			require_once dirname( __FILE__ ) . '/components/extensions/manager/includes/class--fw-extensions-install-upgrader-skin.php';
			break;
		case 'Parsedown' :
			require_once dirname( __FILE__ ) . '/components/extensions/manager/includes/parsedown/Parsedown.php';
			break;
		case 'FW_Ext_Download_Source' :
			require_once dirname( __FILE__ ) . '/components/extensions/manager/includes/download-source/class--fw-ext-download-source.php';
			break;
		case '_FW_Ext_Download_Source_Register' :
			require_once dirname( __FILE__ ) . '/components/extensions/manager/includes/download-source/class--fw-ext-download-source-register.php';
			break;
		case 'FW_Ext_Download_Source_Github' :
			require_once dirname( __FILE__ ) . '/components/extensions/manager/includes/download-source/types/class-fw-download-source-github.php';
			break;
		case '_FW_Available_Extensions_Register' :
			require_once dirname( __FILE__ ) . '/components/extensions/manager/includes/available-ext/class--fw-available-extensions-register.php';
			break;
		case 'FW_Available_Extension' :
			require_once dirname( __FILE__ ) . '/components/extensions/manager/includes/available-ext/class-fw-available-extension.php';
			break;
	}
}

spl_autoload_register( '_fw_core_extends_autoload' );
function _fw_core_extends_autoload( $class ) {
	switch ( $class ) {
		case 'FW_Container_Type' :
			require_once dirname( __FILE__ ) . '/extends/class-fw-container-type.php';
			break;
		case 'FW_Option_Type' :
			require_once dirname( __FILE__ ) . '/extends/class-fw-option-type.php';
			break;
		case 'FW_Extension' :
			require_once dirname( __FILE__ ) . '/extends/class-fw-extension.php';
			break;
		case 'FW_Option_Handler' :
			require_once dirname( __FILE__ ) . '/extends/interface-fw-option-handler.php';
			break;
	}
}