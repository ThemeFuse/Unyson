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
			require_once dirname( __FILE__ ) . '/core/class-fw-manifest.php';
			break;
	}
}

spl_autoload_register( '_fw_core_components_autoload' );
function _fw_core_components_autoload( $class ) {
	switch ( $class ) {
		case '_FW_Component_Backend' :
			require_once dirname( __FILE__ ) . '/core/components/backend.php';
			break;
		case '_FW_Component_Extensions' :
			require_once dirname( __FILE__ ) . '/core/components/extensions.php';
			break;
		case '_FW_Component_Theme' :
			require_once dirname( __FILE__ ) . '/core/components/theme.php';
			break;
		case 'FW_Settings_Form_Theme' :
			require_once dirname( __FILE__ ) . '/core/components/backend/class-fw-settings-form-theme.php';
			break;
	}
}

spl_autoload_register( '_fw_core_components_extensions_autoload' );
function _fw_core_components_extensions_autoload( $class ) {
	switch ( $class ) {
		case 'FW_Extension_Default' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/class-fw-extension-default.php';
			break;
		case '_FW_Extensions_Manager' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/class--fw-extensions-manager.php';
			break;
		case '_FW_Extensions_Delete_Upgrader_Skin' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/includes/class--fw-extensions-delete-upgrader-skin.php';
			break;
		case '_FW_Extensions_Install_Upgrader_Skin' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/includes/class--fw-extensions-install-upgrader-skin.php';
			break;
		case 'Parsedown' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/includes/parsedown/Parsedown.php';
			break;
		case 'FW_Ext_Download_Source' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/includes/download-source/class--fw-ext-download-source.php';
			break;
		case '_FW_Ext_Download_Source_Register' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/includes/download-source/class--fw-ext-download-source-register.php';
			break;
		case 'FW_Ext_Download_Source_Github' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/includes/download-source/types/class-fw-download-source-github.php';
			break;
		case 'FW_Ext_Download_Source_Bitbucket' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/includes/download-source/types/class-fw-download-source-bitbucket.php';
			break;
		case 'FW_Ext_Download_Source_Custom' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/includes/download-source/types/class-fw-download-source-custom.php';
			break;
		case '_FW_Available_Extensions_Register' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/includes/available-ext/class--fw-available-extensions-register.php';
			break;
		case 'FW_Available_Extension' :
			require_once dirname( __FILE__ ) . '/core/components/extensions/manager/includes/available-ext/class-fw-available-extension.php';
			break;
	}
}

spl_autoload_register( '_fw_core_extends_autoload' );
function _fw_core_extends_autoload( $class ) {
	switch ( $class ) {
		case 'FW_Container_Type' :
			require_once dirname( __FILE__ ) . '/core/extends/class-fw-container-type.php';
			break;
		case 'FW_Option_Type' :
			require_once dirname( __FILE__ ) . '/core/extends/class-fw-option-type.php';
			break;
		case 'FW_Extension' :
			require_once dirname( __FILE__ ) . '/core/extends/class-fw-extension.php';
			break;
		case 'FW_Option_Handler' :
			require_once dirname( __FILE__ ) . '/core/extends/interface-fw-option-handler.php';
			break;
	}
}

spl_autoload_register( '_fw_code_exceptions_autoload' );
function _fw_code_exceptions_autoload( $class ) {
	switch ( $class ) {
		case 'FW_Option_Type_Exception' :
		case 'FW_Option_Type_Exception_Not_Found' :
		case 'FW_Option_Type_Exception_Invalid_Class' :
		case 'FW_Option_Type_Exception_Already_Registered' :
			require_once dirname( __FILE__ ) . '/core/exceptions/class-fw-option-type-exception.php';
			break;
	}
}

// Autoload helper classes
function _fw_autoload_helper_classes($class) {
	static $class_to_file = array(
		'FW_Dumper' => 'class-fw-dumper',
		'FW_Cache' => 'class-fw-cache',
		'FW_Callback' => 'class-fw-callback',
		'FW_Access_Key' => 'class-fw-access-key',
		'FW_WP_Filesystem' => 'class-fw-wp-filesystem',
		'FW_Form' => 'class-fw-form',
		'FW_Form_Not_Found_Exception' => 'exceptions/class-fw-form-not-found-exception',
		'FW_Form_Invalid_Submission_Exception' => 'exceptions/class-fw-form-invalid-submission-exception',
		'FW_Settings_Form' => 'class-fw-settings-form',
		'FW_Request' => 'class-fw-request',
		'FW_Session' => 'class-fw-session',
		'FW_WP_Option' => 'class-fw-wp-option',
		'FW_WP_Meta' => 'class-fw-wp-meta',
		'FW_Db_Options_Model' => 'class-fw-db-options-model',
		'FW_Flash_Messages' => 'class-fw-flash-messages',
		'FW_Resize' => 'class-fw-resize',
		'FW_WP_List_Table' => 'class-fw-wp-list-table',
		'FW_Type' => 'type/class-fw-type',
		'FW_Type_Register' => 'type/class-fw-type-register',
	);

	if (isset($class_to_file[$class])) {
		require dirname(__FILE__) .'/helpers/'. $class_to_file[$class] .'.php';
	}
}
spl_autoload_register('_fw_autoload_helper_classes');

spl_autoload_register( '_fw_includes_container_types_autoload' );
function _fw_includes_container_types_autoload( $class ) {
	switch ( $class ) {
		case 'FW_Container_Type_Undefined' :
			require_once dirname( __FILE__ ) . '/includes/container-types/class-fw-container-type-undefined.php';
			break;
		case 'FW_Container_Type_Group' :
			require_once dirname( __FILE__ ) . '/includes/container-types/simple.php';
			break;
		case 'FW_Container_Type_Box' :
			require_once dirname( __FILE__ ) . '/includes/container-types/box/class-fw-container-type-box.php';
			break;
		case 'FW_Container_Type_Popup' :
			require_once dirname( __FILE__ ) . '/includes/container-types/popup/class-fw-container-type-popup.php';
			break;
		case 'FW_Container_Type_Tab' :
			require_once dirname( __FILE__ ) . '/includes/container-types/tab/class-fw-container-type-tab.php';
			break;
	}
}

spl_autoload_register( '_fw_includes_customizer_autoload' );
function _fw_includes_customizer_autoload( $class ) {
	switch ( $class ) {
		case '_FW_Customizer_Control_Option_Wrapper' :
			require_once dirname( __FILE__ ) . '/includes/customizer/class--fw-customizer-control-option-wrapper.php';
			break;
		case '_FW_Customizer_Setting_Option' :
			require_once dirname( __FILE__ ) . '/includes/customizer/class--fw-customizer-setting-option.php';
			break;
	}
}

spl_autoload_register( '_fw_includes_option_storage_autoload' );
function _fw_includes_option_storage_autoload( $class ) {
	switch ( $class ) {
		case '_FW_Option_Storage_Type_Register' :
			require_once dirname( __FILE__ ) . '/includes/option-storage/class--fw-option-storage-type-register.php';
			break;
		case 'FW_Option_Storage_Type' :
			require_once dirname( __FILE__ ) . '/includes/option-storage/class-fw-option-storage-type.php';
			break;
		case 'FW_Option_Storage_Type_Post_Meta' :
			require_once dirname( __FILE__ ) . '/includes/option-storage/type/class-fw-option-storage-type-post-meta.php';
			break;
		case 'FW_Option_Storage_Type_Term_Meta' :
			require_once dirname( __FILE__ ) . '/includes/option-storage/type/class-fw-option-storage-type-term-meta.php';
			break;
		case 'FW_Option_Storage_Type_WP_Option' :
			require_once dirname( __FILE__ ) . '/includes/option-storage/type/class-fw-option-storage-type-wp-option.php';
			break;
	}
}

spl_autoload_register( '_fw_includes_option_types_autoload' );
function _fw_includes_option_types_autoload( $class ) {
	switch ( $class ) {
		case 'FW_Option_Type_Undefined' :
			require_once dirname( __FILE__ ) . '/includes/option-types/class-fw-option-type-undefined.php';
			break;
		case 'FW_Option_Type_Hidden' :
		case 'FW_Option_Type_Text' :
		case 'FW_Option_Type_Short_Text' :
		case 'FW_Option_Type_Number' :
		case 'FW_Option_Type_Password' :
		case 'FW_Option_Type_Textarea' :
		case 'FW_Option_Type_Html' :
		case 'FW_Option_Type_Html_Fixed' :
		case 'FW_Option_Type_Html_Full' :
		case 'FW_Option_Type_Checkbox' :
		case 'FW_Option_Type_Checkboxes' :
		case 'FW_Option_Type_Radio' :
		case 'FW_Option_Type_Select' :
		case 'FW_Option_Type_Short_Select' :
		case 'FW_Option_Type_Select_Multiple' :
		case 'FW_Option_Type_Unique' :
		case 'FW_Option_Type_GMap_Key' :
			require_once dirname( __FILE__ ) . '/includes/option-types/simple.php';
			break;
		case 'FW_Option_Type_Addable_Box' :
			require_once dirname( __FILE__ ) . '/includes/option-types/addable-box/class-fw-option-type-addable-box.php';
			break;
		case 'FW_Option_Type_Addable_Popup' :
		case 'FW_Option_Type_Addable_Popup_Full' :
			require_once dirname( __FILE__ ) . '/includes/option-types/addable-popup/class-fw-option-type-addable-popup.php';
			break;
		case 'FW_Option_Type_Addable_Option' :
			require_once dirname( __FILE__ ) . '/includes/option-types/addable-option/class-fw-option-type-addable-option.php';
			break;
		case 'FW_Option_Type_Background_Image' :
			require_once dirname( __FILE__ ) . '/includes/option-types/background-image/class-fw-option-type-background-image.php';
			break;
		case 'FW_Option_Type_Color_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/color-picker/class-fw-option-type-color-picker.php';
			break;
		case 'FW_Option_Type_Date_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/date-picker/class-fw-option-type-wp-date-picker.php';
			break;
		case 'FW_Option_Type_Datetime_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/datetime-picker/class-fw-option-type-datetime-picker.php';
			break;
		case 'FW_Option_Type_Datetime_Range' :
			require_once dirname( __FILE__ ) . '/includes/option-types/datetime-range/class-fw-option-type-datetime-range.php';
			break;
		case 'FW_Option_Type_Gradient' :
			require_once dirname( __FILE__ ) . '/includes/option-types/gradient/class-fw-option-type-gradient.php';
			break;
		case 'FW_Option_Type_Icon' :
			require_once dirname( __FILE__ ) . '/includes/option-types/icon/class-fw-option-type-icon.php';
			break;
		case 'FW_Option_Type_Icon_v2' :
			require_once dirname( __FILE__ ) . '/includes/option-types/icon-v2/class-fw-option-type-icon-v2.php';
			break;
		case 'FW_Option_Type_Image_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/image-picker/class-fw-option-type-image-picker.php';
			break;
		case 'FW_Option_Type_Map' :
			require_once dirname( __FILE__ ) . '/includes/option-types/map/class-fw-option-type-map.php';
			break;
		case 'FW_Option_Type_Multi' :
			require_once dirname( __FILE__ ) . '/includes/option-types/multi/class-fw-option-type-multi.php';
			break;
		case 'FW_Option_Type_Multi_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/multi-picker/class-fw-option-type-multi-picker.php';
			break;
		case 'FW_Option_Type_Multi_Select' :
			require_once dirname( __FILE__ ) . '/includes/option-types/multi-select/class-fw-option-type-multi-select.php';
			break;
		case 'FW_Option_Type_Multi_Upload' :
			require_once dirname( __FILE__ ) . '/includes/option-types/multi-upload/class-fw-option-type-multi-upload.php';
			break;
		case 'FW_Option_Type_Oembed' :
			require_once dirname( __FILE__ ) . '/includes/option-types/oembed/class-fw-option-type-oembed.php';
			break;
		case 'FW_Option_Type_Popup' :
			require_once dirname( __FILE__ ) . '/includes/option-types/popup/class-fw-option-type-popup.php';
			break;
		case 'FW_Option_Type_Radio_Text' :
			require_once dirname( __FILE__ ) . '/includes/option-types/radio-text/class-fw-option-type-radio-text.php';
			break;
		case 'FW_Option_Type_Range_Slider' :
			require_once dirname( __FILE__ ) . '/includes/option-types/range-slider/class-fw-option-type-range-slider.php';
			break;
		case 'FW_Option_Type_Rgba_Color_Picker' :
			require_once dirname( __FILE__ ) . '/includes/option-types/rgba-color-picker/class-fw-option-type-rgba-color-picker.php';
			break;
		case 'FW_Option_Type_Slider' :
			require_once dirname( __FILE__ ) . '/includes/option-types/slider/class-fw-option-type-slider.php';
			break;
		case 'FW_Option_Type_Slider_Short' :
			require_once dirname( __FILE__ ) . '/includes/option-types/slider/class-fw-option-type-short-slider.php';
			break;
		case 'FW_Option_Type_Switch' :
			require_once dirname( __FILE__ ) . '/includes/option-types/switch/class-fw-option-type-switch.php';
			break;
		case 'FW_Option_Type_Typography' :
			require_once dirname( __FILE__ ) . '/includes/option-types/typography/class-fw-option-type-typography.php';
			break;
		case 'FW_Option_Type_Typography_v2' :
			require_once dirname( __FILE__ ) . '/includes/option-types/typography-v2/class-fw-option-type-typography-v2.php';
			break;
		case 'FW_Option_Type_Upload' :
			require_once dirname( __FILE__ ) . '/includes/option-types/upload/class-fw-option-type-upload.php';
			break;
		case 'FW_Option_Type_Wp_Editor' :
			require_once dirname( __FILE__ ) . '/includes/option-types/wp-editor/class-fw-option-type-wp-editor.php';
			break;
		case 'FW_Icon_V2_Favorites_Manager' :
			require_once dirname( __FILE__ ) . '/includes/option-types/icon-v2/includes/class-fw-icon-v2-favorites.php';
			break;
		case 'FW_Icon_V2_Packs_Loader' :
			require_once dirname( __FILE__ ) . '/includes/option-types/icon-v2/includes/class-fw-icon-v2-packs-loader.php';
			break;
	}
}