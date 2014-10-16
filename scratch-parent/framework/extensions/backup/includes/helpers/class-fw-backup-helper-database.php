<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Helper_Database
{
	public function check_permissions()
	{
	}

	public function query($sql, $mode = ARRAY_A)
	{
		/**
		 * @var wpdb $wpdb
		 */

		global $wpdb;

		return $wpdb->get_results($sql, $mode);
	}

	public function query_column($sql, $column)
	{
		foreach ($this->query($sql, ARRAY_N) as $row) {
			return $row[$column];
		}

		throw new FW_Backup_Exception('Database Error');
	}

	public function close_mysql_statement($stmt)
	{
		// 1. Without **end of statement** restoration process seems to be much complicated.
		// 2. Using PHP_EOL will break foreach_statement method on Windows
		return $stmt . "; -- end of statement\n";
	}

	// https://github.com/vrana/adminer/blob/master/adminer/drivers/mysql.inc.php#L273
	// https://github.com/vrana/adminer/blob/73629178d8fa1bf08f10e0b86f85c4e1f6307c39/adminer/drivers/mysql.inc.php#L273
	public function escape_mysql_identifier($identifier)
	{
		return '`' . str_replace('`', '``', $identifier) . '`';
	}

	public function escape_mysql_value($value)
	{
		global $wpdb; /** @var wpdb $wpdb */
		return "'" . $wpdb->_real_escape($value) . "'";
	}

	// NOTE It yeilds only tables starting with $table_prefix
	public function query_schema()
	{
		$table = array();
		$view = array();

		foreach ($this->query('SHOW FULL TABLES', ARRAY_N) as $row) {
			list ($table_or_view, $type) = $row;
			switch ($type) {
			case 'BASE TABLE':
				$table[] = $table_or_view;
				break;
			case 'VIEW':
				$view[] = $table_or_view;
				break;
			default:
				throw new FW_Backup_Exception("Invalid table type: $type");
			}
		}

		// Use only tables starting with $table_prefix
		$table = $this->filter_by_table_prefix($table);
		$view = $this->filter_by_table_prefix($view);

		return array($table, $view);
	}

	public function show_create_table($table)
	{
		return $this->query_column('SHOW CREATE TABLE '.$this->escape_mysql_identifier($table), 1);
	}

	public function show_create_view($view)
	{
		return $this->query_column('SHOW CREATE VIEW '.$this->escape_mysql_identifier($view), 1);
	}

	// https://github.com/vrana/adminer/blob/master/adminer/drivers/mysql.inc.php#L454
	// https://github.com/vrana/adminer/blob/73629178d8fa1bf08f10e0b86f85c4e1f6307c39/adminer/drivers/mysql.inc.php#L454
	public function show_columns($table)
	{
		$field = array();

		$result = $this->query('SHOW FULL COLUMNS FROM '.$this->escape_mysql_identifier($table));
		if (empty($result)) {
			return null;
		}

		foreach ($result as $row) {
			preg_match('~^([^( ]+)(?:\\((.+)\\))?( unsigned)?( zerofill)?$~', $row['Type'], $m);
			$field[$row['Field']] = array(
				'field' => $row['Field'],
				'full_type' => $row['Type'],
				'collation' => $row['Collation'],
				'null' => ($row['Null'] == 'YES'),
				'primary' => ($row['Key'] == 'PRI'),
				'default' => ($row['Default'] != '' || preg_match('~char|set~', @$m[1]) ? $row['Default'] : null),
				'auto_increment' => ($row['Extra'] == 'auto_increment'),
				'privileges' => preg_split('~, *~', $row['Privileges']),
				'comment' => $row['Comment'],
				'type' => @$m[1],
				'length' => @$m[2],
				'unsigned' => ltrim(@$m[3] . @$m[4]),
				'on_update' => (preg_match('~^on update (.+)~i', $row['Extra'], $m) ? $m[1] : ''), //! available since MySQL 5.1.23
			);
		}

		return $field;
	}

	public function show_privileges()
	{
		$privileges = array();
		foreach ($this->query('SHOW PRIVILEGES', ARRAY_N) as $row) {
			$privileges[strtoupper($row[0])] = explode(',', strtoupper($row[1]));
		}
		return $privileges;
	}

	public function drop_table($table)
	{
		$this->query('DROP TABLE '.$this->escape_mysql_identifier($table));
	}

	public function drop_view($view)
	{
		$this->query('DROP VIEW '.$this->escape_mysql_identifier($view));
	}

//	public function dump_table($fp, $table, $max_packet = 1048576)
//	{
//		$this->dump_query($fp, 'SELECT * FROM '.$this->escape_mysql_identifier($table), $table, $max_packet);
//	}

	// https://github.com/vrana/adminer/blob/master/adminer/include/adminer.inc.php#L666
	// https://github.com/vrana/adminer/blob/06f4346cfeec0e9f67a375708f9265557a738141/adminer/include/adminer.inc.php#L666
	public function dump_query($fp, $query, $table, $max_packet = 1048576)
	{
		$insert_into = '';
		$buffer = '';
		$end_of_stmt = $this->close_mysql_statement('');

		$field = $this->show_columns($table);
		foreach ($this->query($query) as $row) {
			$row_escaped = array();
			foreach ($row as $key => $value) {
				if ($value === null) {
					$row_escaped[$key] = 'NULL';
				}
				// https://github.com/vrana/adminer/blob/master/adminer/include/adminer.inc.php#L707
				// https://github.com/vrana/adminer/blob/06f4346cfeec0e9f67a375708f9265557a738141/adminer/include/adminer.inc.php#L707
				else if (preg_match('~(^|[^o])int|float|double|decimal~', $field[$key]['type']) && $value != '') {
					$row_escaped[$key] = $value;
				}
				else {
					$row_escaped[$key] = $this->escape_mysql_value($value);
				}
			}
			if (!$insert_into) {
				$insert_into = 'INSERT INTO '.$this->escape_mysql_identifier($table). ' (' . implode(', ', array_map(array($this, 'escape_mysql_identifier'), array_keys($row_escaped))) . ') VALUES';
			}
			$value_list = ($max_packet == 0 ? ' ' : "\n  ") . '(' . implode(', ', $row_escaped) . ')';
			if (!$buffer) {
				$buffer = $insert_into . $value_list;
			}
			elseif (strlen($buffer) + 4 + strlen($value_list) + strlen($end_of_stmt) < $max_packet) { // 4 - length specification
				$buffer .= ',' . $value_list;
			}
			else {
				fwrite($fp, $buffer . $end_of_stmt);
				$buffer = $insert_into . $value_list;
			}
		}

		if ($buffer) {
			fwrite($fp, $buffer . $end_of_stmt);
		}
	}

	public function foreach_statement($fp, $callback, $read_size = 102400)
	{
		$delimiter = $this->close_mysql_statement('');
		$buf = '';

		while (!feof($fp)) {
			$buf .= fread($fp, $read_size);
			while (true) {
				$pos = strpos($buf, $delimiter);
				if ($pos === false) {
					break;
				}
				$statement = substr($buf, 0, $pos);
				call_user_func($callback, $statement);
				$buf = substr($buf, $pos + strlen($delimiter));
			}
		}
		if ($buf) {
			call_user_func($callback, $buf);
		}
	}

	public function search_replace($search_replace)
	{
		list ($table_list) = $this->query_schema();

		$args = array(
			'name' 				=> DB_NAME,
			'user' 				=> DB_USER,
			'pass' 				=> DB_PASSWORD,
			'host' 				=> DB_HOST,
			'search' 			=> '',
			'replace' 			=> '',
			'tables'			=> $table_list,
			'dry_run' 			=> false,
			'regex' 			=> false,
			'pagesize' 			=> 50000,
			'alter_engine' 		=> false,
			'alter_collation' 	=> false,
			'verbose'			=> false
		);

		$srdb = new icit_srdb($args);

		foreach ($search_replace as $search => $replace) {
			$srdb->replacer($search, $replace, $table_list);
		}
	}

	private function filter_by_table_prefix($table_list)
	{
		global $table_prefix;

		$ret = array();

		foreach ($table_list as $name) {
			if (strpos($name, $table_prefix) === 0) {
				$ret[] = $name;
			}
		}

		return $ret;
	}

	/**
	 * This method should be called after importing .sql dump from
	 * different host
	 */
	public function fix_foreign_database($search_replace, $foreign_prefix)
	{
		/**
		 * @var wpdb $wpdb
		 */

		global $wpdb;

		$this->search_replace($search_replace);

		$esc_like = array($wpdb, 'esc_like');
		if (!is_callable($esc_like)) {
			$esc_like = 'like_escape';
		}

		// After importing foreign .sql dump in case where table prefixes
		// was different the user ends up with
		//
		//     You do not have sufficient permissions to access this page.
		//
		// The following code should fix that.
		foreach (array($wpdb->usermeta => 'meta_key', $wpdb->options => 'option_name') as $table => $field) {
			$query = $wpdb->prepare("
				UPDATE
					$table
				SET
					$field = INSERT($field, 1, %d, %s)
				WHERE
					$field LIKE %s
			",
				strlen($foreign_prefix), $wpdb->prefix, call_user_func($esc_like, $foreign_prefix) . '%'
			);
			$wpdb->query($query);
		}
	}

	public function import($sql_file, $keep_users_table = false, $fix_foreign_database = false, $keep_options = false)
	{
		$fp = fopen($sql_file, 'r');
		$this->import_fp($fp, $keep_users_table, $fix_foreign_database, $keep_options);
		fclose($fp);
	}

	public function import_fp($fp, $keep_users_table = false, $fix_foreign_database = false, $keep_options = false)
	{
		/**
		 * @var wpdb $wpdb
		 */

		global $wpdb;

		$helper = new FW_Backup_Helper_Database();
		$exporter = new FW_Backup_Export_Database();

		$option_list = array(
			$wpdb->prefix . 'user_roles',
			'siteurl',
			'blogname', 'blog_charset', 'blogdescription',
			'admin_email',
			'mailserver_url', 'mailserver_login', 'mailserver_pass', 'mailserver_port',
			'ftp_credentials',
			'use_ssl',
			// Saving the following three options then restoring them allows theme
			// directory to be renamed in auto-install archives
			'template',
			'stylesheet',
			'current_theme'
		);

		// Preserve some options
		$before = array_map('get_option', $option_list);
		$before = array_combine($option_list, $before);

		// Preserve Backup History and Backup Settings
		$history = $exporter->export_history();
		$settings = $exporter->export_settings();

		// Import database (preserve user related tables)
		// ==============================================
		if ($keep_users_table) {
			$foreign_prefix = $exporter->import_fp($fp, array($wpdb->users));
		}
		else {
			$foreign_prefix = $exporter->import_fp($fp);
		}

		wp_cache_flush();

		// Fix database
		if ($fix_foreign_database) {
			$helper->fix_foreign_database(array(site_url() => $before['siteurl']), $foreign_prefix);
		}

		wp_cache_flush();

		// Restore Backup History and Settings
		$exporter->import_history($history);
		$exporter->import_settings($settings);

		// Restore options
		if ($keep_options) {

			// WP keeps stylesheet settings in theme_mods_{stylesheet} option,
			// that means that if stylesheet option has different value in dump file and in database
			// new theme_mods_{stylesheet} should be rename to old theme_mods_{stylesheet}
			$stylesheet = get_option('stylesheet');
			if ($before['stylesheet'] != $stylesheet) {

				$theme_mods_before = 'theme_mods_' . $before['stylesheet'];
				$theme_mods_after = 'theme_mods_' . $stylesheet;

				$query = $wpdb->prepare("
					DELETE FROM
						$wpdb->options
					WHERE
					    option_name = %s
			    ", $theme_mods_before);
				$wpdb->query($query);

				$query = $wpdb->prepare("
					UPDATE
						$wpdb->options
					SET
						option_name = %s
					WHERE
						option_name = %s
				", $theme_mods_before, $theme_mods_after);
				$wpdb->query($query);

			}

			// Restore all saved options
			array_map('update_option', array_keys($before), $before);
		}

		// Actualize settings
		$this->backup()->cron()->reschedule();

		wp_cache_flush();
	}

	/**
	 * @return FW_Extension_Backup
	 */
	private function backup()
	{
		return fw()->extensions->get('backup');
	}
}
