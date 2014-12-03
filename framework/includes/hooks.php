<?php if (!defined('FW')) die('Forbidden');
/**
 * Filters and Actions
 */

/**
 * Term Meta
 */
{
	if (!function_exists('_fw_term_meta_setup_blog')):

		/** @internal */
		function _fw_term_meta_setup_blog( $id = false ) {
			global $wpdb;

			if ( $id !== false ) {
				switch_to_blog( $id );
			}

			$charset_collate = '';
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$tables = $wpdb->get_results( "show tables like '{$wpdb->prefix}fw_termmeta'" );
			if ( ! count( $tables ) ) {
				$wpdb->query( "CREATE TABLE {$wpdb->prefix}fw_termmeta (
				meta_id bigint(20) unsigned NOT NULL auto_increment,
				fw_term_id bigint(20) unsigned NOT NULL default '0',
				meta_key varchar(255) default NULL,
				meta_value longtext,
				PRIMARY KEY	(meta_id),
				KEY fw_term_id (fw_term_id),
				KEY meta_key (meta_key)
				) $charset_collate;"
				);
			}
		}

	endif;

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