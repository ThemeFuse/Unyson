<?php if (!defined('FW')) die('Forbidden');
/**
 * Filters and Actions
 */

/**
 * Term Meta
 */
{
	/**
	 * Prepare $wpdb as soon as it's possible
	 * @internal
	 */
	function _action_term_meta_wpdb_fix() {
		global $wpdb;

		$wpdb->fw_termmeta = $wpdb->prefix . 'fw_termmeta';
	}
	add_action( 'switch_blog', '_action_term_meta_wpdb_fix', 3 );

	_action_term_meta_wpdb_fix();

	/**
	 * When a term is deleted, delete its meta.
	 *
	 * @param mixed $term_id
	 *
	 * @return void
	 */
	function _action_fw_delete_term( $term_id ) {

		$term_id = (int) $term_id;

		if ( ! $term_id ) {
			return;
		}

		global $wpdb;
		$wpdb->delete( $wpdb->fw_termmeta, array( 'fw_term_id' => $term_id ), array( '%d' ) );
	}
	add_action( 'delete_term', '_action_fw_delete_term' );
}