<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

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


if ( true ) {
	// if activated on a particular blog, just set it up there.
	_fw_term_meta_setup_blog();
} else {
	global $wpdb;

	foreach (
		$wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}'" )
		as $blog_id
	) {
		_fw_term_meta_setup_blog( $blog_id );
	}

	while ( restore_current_blog() ) { };
}