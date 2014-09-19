<?php

/**
 * Deprecated constants
 * Do not use them
 * Will be removed in the next major release
 */
{
	/**
	 * Child theme constants
	 *
	 * Was replaced with
	 * fw_get_stylesheet_customizations_directory()
	 * fw_get_stylesheet_customizations_directory_uri()
	 */
	{
		/** @deprecated */
		define('FW_CT', is_child_theme());

		if (FW_CT) {
			/** @deprecated */
			define('FW_CT_DIR', fw_fix_path(get_stylesheet_directory()));
			/** @deprecated */
			define('FW_CT_CUSTOM_DIR', FW_CT_DIR .'/framework-customizations');
			/** @deprecated */
			define('FW_CT_THEME_DIR', FW_CT_CUSTOM_DIR .'/theme');
			/** @deprecated */
			define('FW_CT_EXTENSIONS_DIR', FW_CT_CUSTOM_DIR .'/extensions');
			/** @deprecated */
			define('FW_CT_URI', get_stylesheet_directory_uri());
			/** @deprecated */
			define('FW_CT_CUSTOM_URI', FW_CT_URI .'/framework-customizations');
			/** @deprecated */
			define('FW_CT_THEME_URI', FW_CT_CUSTOM_URI .'/theme');
			/** @deprecated */
			define('FW_CT_EXTENSIONS_URI', FW_CT_CUSTOM_URI .'/extensions');
		}
	}

	/**
	 * Parent theme constants
	 *
	 * Was replaced with
	 * fw_get_template_customizations_directory()
	 * fw_get_template_customizations_directory_uri()
	 */
	{
		/** @deprecated */
		define('FW_PT_DIR', fw_fix_path(get_template_directory()));
		/** @deprecated */
		define('FW_PT_CUSTOM_DIR', FW_PT_DIR .'/framework-customizations');
		/** @deprecated */
		define('FW_PT_THEME_DIR', FW_PT_CUSTOM_DIR .'/theme');
		/** @deprecated */
		define('FW_PT_EXTENSIONS_DIR', FW_PT_CUSTOM_DIR .'/extensions');
		/** @deprecated */
		define('FW_PT_URI', get_template_directory_uri());
		/** @deprecated */
		define('FW_PT_CUSTOM_URI', FW_PT_URI .'/framework-customizations');
		/** @deprecated */
		define('FW_PT_THEME_URI', FW_PT_CUSTOM_URI .'/theme');
		/** @deprecated */
		define('FW_PT_EXTENSIONS_URI', FW_PT_CUSTOM_URI .'/extensions');
	}

	/**
	 * Framework constants
	 *
	 * Was replaced with
	 * fw_get_framework_directory()
	 * fw_get_framework_directory_uri()
	 */
	{
		/** @deprecated */
		define('FW_DIR', FW_PT_DIR .'/framework');
		/** @deprecated */
		define('FW_EXTENSIONS_DIR', FW_DIR .'/extensions');
		/** @deprecated */
		define('FW_URI', FW_PT_URI .'/framework');
		/** @deprecated */
		define('FW_EXTENSIONS_URI', FW_URI .'/extensions');
	}

	/** Cache */
	{
		/** @deprecated */
		define('FW_CACHE_DIR', fw_fix_path(WP_CONTENT_DIR) .'/cache/framework');
		/** @deprecated */
		define('FW_CACHE_URI', WP_CONTENT_URL .'/cache/framework');
	}
}

/**
 * After the 1.2.1 release https://github.com/ThemeFuse/Unyson/releases/tag/v1.2.1
 * the framework is loaded on 'after_setup_theme' action,
 * but in theme remains an important add_action('after_setup_theme', '...')
 * https://github.com/ThemeFuse/Unyson/blob/0e3f5563a7f0748c2b83b9c8820a58e7ff1e8406/scratch-parent/framework-customizations/theme/hooks.php#L70
 * that is not executed anymore.
 * So we execute it for the users that downloaded the default framework heme before the 1.2.3 release
 */
{
	/**
	 * @internal
	 */
	function _action_check_for_deprecated_theme_after_setup_theme_action() {
		if (has_action('after_setup_theme', '_action_theme_setup')) {
			remove_action('after_setup_theme', '_action_theme_setup');

			call_user_func('_action_theme_setup');
		}
	}
	add_action('fw_init', '_action_check_for_deprecated_theme_after_setup_theme_action');
}