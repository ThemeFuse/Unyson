<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_IE_Database implements FW_Backup_Interface_IE
{
	private $post_type;
	private $db;
	private $feedback;

	public function __construct($post_type, FW_Backup_Service_Database $db, FW_Backup_Interface_Feedback $feedback)
	{
		$this->post_type = $post_type;
		$this->db = $db;
		$this->feedback = $feedback;
	}

	public function import($fp)
	{
		list ($table, $view) = $this->db->query_schema();
		array_map(array($this->db, 'drop_table'), $table);
		array_map(array($this->db, 'drop_view'), $view);

		$this->db->foreach_statement($fp, array($this->db, 'query'));

		wp_cache_flush();
	}

	public function export($fp)
	{
		global $wpdb;

		$this->feedback->set_task(__('Querying database...', 'fw'));

		list($table, $view) = $this->db->query_schema();

		$this->feedback->set_task(sprintf(__('%d tables found', 'fw'), count($table)));
		$this->feedback->set_task(sprintf(__('%d views found', 'fw'), count($view)));

		$table_sql = array_map(array($this->db, 'show_create_table'), $table);
		$view_sql = array_map(array($this->db, 'show_create_view'), $view);

		$this->feedback->set_task(__('Dumping tables...', 'fw'), count($table));

		$first = true;
		$index = 0;
		foreach (array_combine($table, $table_sql) as $name => $sql) {

			$this->feedback->set_task_progress($index, $name);

			fwrite($fp, ($first ? $first = false : PHP_EOL.PHP_EOL));
			fwrite($fp, $this->db->close_mysql_statement($sql));

			// get rid of backup history as well as backup settings
			switch ($name) {
			case $wpdb->posts:
				$query = sprintf('SELECT * FROM %s WHERE post_type != %s',
					$this->db->escape_mysql_identifier($name),
					$this->db->escape_mysql_value($this->post_type));
				break;
			case $wpdb->postmeta:
				$query = sprintf('SELECT a.* FROM %s AS a INNER JOIN %s AS b WHERE a.post_id = b.ID AND b.post_type != %s',
					$this->db->escape_mysql_identifier($name),
					$this->db->escape_mysql_identifier($wpdb->posts),
					$this->db->escape_mysql_value($this->post_type)
				);
				break;
			case $wpdb->options:
				$query = sprintf("SELECT * FROM %s WHERE option_name NOT LIKE 'fw_backup.%%'",
					$this->db->escape_mysql_identifier($name)
				);
				break;
			default:
				$query = sprintf('SELECT * FROM %s', $this->db->escape_mysql_identifier($name));
				break;
			}

			$this->db->dump_query($fp, $query, $name);
			$this->feedback->set_task_progress(++$index, $name);
		}

		$this->feedback->set_task(__('Dumping views...', 'fw'));

		foreach (array_combine($view, $view_sql) as $name => $sql) {
			// $log->append('dumping view '.$name);
			fwrite($fp, ($first ? $first = false : PHP_EOL.PHP_EOL));
			fwrite($fp, $this->db->close_mysql_statement($sql));
		}
	}
}
