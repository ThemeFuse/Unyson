<?php if (!defined('FW')) die('Forbidden');
/**
 * Filters and Actions
 */

/**
 * Load option types
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
}

/**
 * Term Meta
 */
{
	/**
	 * Prepare $wpdb as soon as it's possible
	 * @internal
	 */
	function _action_term_meta_wpdb_fix() {
		/** @var WPDB $wpdb */
		global $wpdb;

		$wpdb->fw_termmeta = $wpdb->prefix . 'fw_termmeta';

		{
			require_once dirname(__FILE__) .'/term-meta/function_fw_term_meta_setup_blog.php';
			_fw_term_meta_setup_blog();
		}
	}
	add_action( 'switch_blog', '_action_term_meta_wpdb_fix', 3 );

	_action_term_meta_wpdb_fix();

	/**
	 * When a term is deleted, delete its meta.
	 *
	 * @param mixed $term_id
	 *
	 * @return void
	 * @internal
	 */
	function _action_fw_delete_term( $term_id ) {
		$term_id = (int) $term_id;

		if ( ! $term_id ) {
			return;
		}

		/** @var WPDB $wpdb */
		global $wpdb;

		$wpdb->delete( $wpdb->fw_termmeta, array( 'fw_term_id' => $term_id ), array( '%d' ) );
	}
	add_action( 'delete_term', '_action_fw_delete_term' );

	/**
	 * Make sure to setup the fw_termmeta table
	 * (useful in cases when the framework is used not as a plugin)
	 * @internal
	 */
	function _action_fw_setup_term_meta_after_theme_switch() {
		require_once dirname(__FILE__) .'/term-meta/function_fw_term_meta_setup_blog.php';
		_fw_term_meta_setup_blog();
	}
	add_action('after_switch_theme', '_action_fw_setup_term_meta_after_theme_switch', 7);
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