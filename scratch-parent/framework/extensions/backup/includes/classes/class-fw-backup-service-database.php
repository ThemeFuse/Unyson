<?php if (!defined('FW')) die('Forbidden');

/**
 * Class FW_Backup_Service_Database
 *
 * Generic functionality for working with database.
 */
class FW_Backup_Service_Database
{
	public function query($sql, $mode = ARRAY_A)
	{
		global $wpdb; /** @var wpdb $wpdb */
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
		// Without **end of statement** restoration process seems to be much complicated.
		return $stmt . '; -- end of statement' . PHP_EOL;
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

	public function dump_table($fp, $table, $max_packet = 1048576)
	{
		$this->dump_query($fp, 'SELECT * FROM '.$this->escape_mysql_identifier($table), $table, $max_packet);
	}

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
			$value_list = ($max_packet == 0 ? ' ' : PHP_EOL.'  ') . '(' . implode(', ', $row_escaped) . ')';
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
}
