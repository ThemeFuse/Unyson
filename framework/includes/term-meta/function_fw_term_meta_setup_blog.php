<?php if (!defined('FW')) die('Forbidden');

if (!function_exists('_fw_term_meta_setup_blog')):

	/**
	 * Setup term meta storage for current blog
	 * @internal
	 */
	function _fw_term_meta_setup_blog() {
		/** @var WPDB $wpdb */
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		$table_name = $wpdb->prefix .'fw_termmeta'; // note: same table name is used in hooks.php for $wpdb->fw_termmeta

		$tables = $wpdb->get_results( "show tables like '{$table_name}'" );
		if ( empty( $tables ) ) {
			$wpdb->query( "CREATE TABLE {$table_name} (
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
