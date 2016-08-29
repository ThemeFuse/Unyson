<?php if (!defined('FW')) die('Forbidden');
/**
 * Filters and Actions
 */

/**
 * Option types
 */
{
	/**
	 * @internal
	 */
	function _action_fw_init_option_types() {
		require_once dirname(__FILE__) .'/option-types/init.php';
	}
	add_action('fw_option_types_init', '_action_fw_init_option_types');

	/**
	 * This option type has `add_action('wp_ajax_...`
	 */
	if (is_admin()) {
		require_once dirname(__FILE__) . '/option-types/multi-select/class-fw-option-type-multi-select.php';

		if (!class_exists('FW_Option_Type_Icon_v2')) {
			require_once dirname(__FILE__) .
				'/option-types/icon-v2/class-fw-option-type-icon-v2.php';
		}
	}
}

/**
 * Container types
 */
{
	/**
	 * @internal
	 */
	function _action_fw_init_container_types() {
		require_once dirname(__FILE__) .'/container-types/init.php';
	}
	add_action('fw_container_types_init', '_action_fw_init_container_types');
}

/**
 * Custom Github API service
 * Provides the same responses but is "unlimited"
 * To prevent error: Github API rate limit exceeded 60 requests per hour
 * https://github.com/ThemeFuse/Unyson/issues/138
 * @internal
 */
function _fw_filter_github_api_url($url) {
	return 'http://github-api-cache.unyson.io';
}
add_filter('fw_github_api_url', '_fw_filter_github_api_url');

/**
 * Javascript events related to tinymce init
 * @since 2.6.0
 */
{
	add_action('wp_tiny_mce_init', '_fw_action_tiny_mce_init');
	function _fw_action_tiny_mce_init($mce_settings) {
?>
<script type="text/javascript">
	if (typeof fwEvents != 'undefined') { fwEvents.trigger('fw:tinymce:init:before'); }
</script>
<?php
	}

	add_action('after_wp_tiny_mce', '_fw_action_after_wp_tiny_mce');
	function _fw_action_after_wp_tiny_mce($mce_settings) {
?>
<script type="text/javascript">
	if (typeof fwEvents != 'undefined') { fwEvents.trigger('fw:tinymce:init:after'); }
</script>
<?php
	}
}
