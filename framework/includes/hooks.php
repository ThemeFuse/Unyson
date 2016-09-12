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
	 * Some option-types have add_action('wp_ajax_...')
	 * so init all option-types if current request is ajax
	 * @since 2.6.1
	 */
	if (defined('DOING_AJAX') && DOING_AJAX) {
		function _action_fw_init_option_types_on_ajax() {
			fw()->backend->option_type('text');
		}
		add_action('fw_init', '_action_fw_init_option_types_on_ajax');
	}

	/**
	 * Prevent Fatal Error if someone is registering option-types in old way (right away)
	 * not in 'fw_option_types_init' action
	 * @param string $class
	 */
	function _fw_autoload_option_types($class) {
		if ('FW_Option_Type' === $class) {
			require_once dirname(__FILE__) .'/../core/extends/class-fw-option-type.php';

			if (is_admin() && defined('WP_DEBUG') && WP_DEBUG) {
				FW_Flash_Messages::add(
					'option-type-register-wrong',
					__("Please register option-types on 'fw_option_types_init' action", 'fw'),
					'warning'
				);
			}
		} elseif ('FW_Container_Type' === $class) {
			require_once dirname(__FILE__) .'/../core/extends/class-fw-container-type.php';

			if (is_admin() && defined('WP_DEBUG') && WP_DEBUG) {
				FW_Flash_Messages::add(
					'container-type-register-wrong',
					__("Please register container-types on 'fw_container_types_init' action", 'fw'),
					'warning'
				);
			}
		}
	}
	spl_autoload_register('_fw_autoload_option_types');
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
