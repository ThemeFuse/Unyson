<?php if (!defined('FW')) die('Forbidden');

/**
 * ISSUE
 * =====
 *
 * There is an issue on dumping database and the importing it.
 * Just filtering tables by its prefix is not enough. If there are
 * two WP sites on a single database. The first WP starts with wp40_
 * and the second WP starts with wp40_aa_, then on exporting tables
 * from first WP the dump will contain tables from both WP.
 *
 * On importing tables with wp40_aa_ will be renamed to wp40_aa_aa_.
 *
 * There is no generic solution for this issue. Just restricting tables
 * to those from WP will exclude any tables from third party plugins. If
 * just filtering by prefix then *ghost* tables will appear on importing.
 */
class FW_Backup_Export_Database implements FW_Backup_Interface_Export
{
	public function export(FW_Backup_Interface_Feedback $feedback)
	{
		$zip_file = sprintf('%s/backup-database-%s.zip', sys_get_temp_dir(), date('Y_m_d-H_i_s'));
		touch($zip_file);

		$zip = new ZipArchive();
		if ($zip->open($zip_file) !== true) {
			throw new FW_Backup_Exception(__('Could not create .zip file', 'fw'));
		}

		try {

			$sql_file = $this->export_sql($feedback);
			$zip->addFile($sql_file, 'database.sql');

			$feedback->set_task(__('Compressing...', 'fw'));
			$zip->close();

			unlink($sql_file);

		}
		catch (FW_Backup_Exception $exception) {
			unset($zip);
			unlink($zip_file);
			if (isset($sql_file) && file_exists($sql_file)) {
				unlink($sql_file);
			}
			throw $exception;
		}

		return $zip_file;
	}

    public function export_sql(FW_Backup_Interface_Feedback $feedback, $options_where = "WHERE option_name NOT LIKE 'fw_backup.%%'", $exclude_table = array())
    {
        /**
         * @var FW_Extension_Backup $backup
         */

        global $wpdb, $table_prefix;

        $db = new FW_Backup_Helper_Database();
        $backup = fw()->extensions->get('backup');

        $filename = sprintf('%s/backup-database-%s.sql', sys_get_temp_dir(), date('Y_m_d-H_i_s'));
        $fp = fopen($filename, 'w');

        $feedback->set_task(__('Querying database...', 'fw'));

        list($table_list, $view_list) = $db->query_schema();

        $feedback->set_task(sprintf(__('%d tables found', 'fw'), count($table_list)));
        $feedback->set_task(sprintf(__('%d views found', 'fw'), count($view_list)));

        $create_table_list = array_map(array($db, 'show_create_table'), $table_list);
        $create_view_list = array_map(array($db, 'show_create_view'), $view_list);

        $feedback->set_task(__('Writing meta info...', 'fw'));

	    // After all headers should come empty line. This is necessary
	    // for be able to run the following code with expected result:
	    //
	    //     $headers = $this->get_headers_fp()
	    //     $db->foreach_statement($fp, FW_Backup_Callable::make(array($this, '_query'), $headers, $db));
		//
	    fwrite($fp, '-- date: ' . current_time('mysql') . PHP_EOL);
	    fwrite($fp, '-- table_prefix: ' . $table_prefix . PHP_EOL);
	    fwrite($fp, PHP_EOL.PHP_EOL);

	    $a = array_combine($table_list, $create_table_list);
	    $a = array_diff_key($a, array_flip($exclude_table));

        $feedback->set_task(__('Dumping tables...', 'fw'), count($a));

        $first = true;
        $index = 0;
        foreach ($a as $table => $create_table) {

            $feedback->set_progress($index, $table);

            fwrite($fp, ($first ? $first = false : PHP_EOL.PHP_EOL));
            fwrite($fp, $db->close_mysql_statement($create_table));

            // get rid of backup history as well as backup settings
            switch ($table) {
            case $wpdb->posts:
                $query = sprintf('SELECT * FROM %s WHERE post_type != %s',
                    $db->escape_mysql_identifier($table),
                    $db->escape_mysql_value($backup->post_type()->get_post_type())
                );
                break;
            case $wpdb->postmeta:
                $query = sprintf('SELECT a.* FROM %s AS a INNER JOIN %s AS b WHERE a.post_id = b.ID AND b.post_type != %s',
                    $db->escape_mysql_identifier($table),
                    $db->escape_mysql_identifier($wpdb->posts),
                    $db->escape_mysql_value($backup->post_type()->get_post_type())
                );
                break;
            case $wpdb->options:
                $query = sprintf("SELECT * FROM %s $options_where",
                    $db->escape_mysql_identifier($table)
                );
                break;
            default:
                $query = sprintf('SELECT * FROM %s', $db->escape_mysql_identifier($table));
                break;
            }

            $db->dump_query($fp, $query, $table);
            $feedback->set_progress(++$index, $table);
        }

        $feedback->set_task(__('Dumping views...', 'fw'));

        foreach ($create_view_list as $create_view) {
            fwrite($fp, ($first ? $first = false : PHP_EOL.PHP_EOL));
            fwrite($fp, $db->close_mysql_statement($create_view));
        }

        fclose($fp);
        return $filename;
    }

	public function import_sql_file($file, $exclude_table = array())
	{
		$fp = fopen($file, 'r');

		try {
			$table_prefix = $this->import_fp($fp, $exclude_table);
		}
		catch (FW_Backup_Exception $exception) {
		}

		// Cleanup
		fclose($fp);

		if (isset($exception)) {
			throw $exception;
		}

		return $table_prefix;
	}

	public function import_fp($fp, $exclude_table = array())
	{
		// Warning: fseek(): stream does not support seeking in .../framework/extensions/backup/includes/exports/class-fw-backup-export-database.php
		// This is due to the fact that $fp is coming from .zip archive
		// which does not supports seeking
		//
		// $fpos = ftell($fp);
		// $headers = $this->get_headers_fp($fp);
		// fseek($fp, $fpos);
		$headers = $this->get_headers_fp($fp);
		if (empty($headers) || empty($headers['date']) || !array_key_exists('table_prefix', $headers)) {
			throw new FW_Backup_Exception('.sql file of wrong format');
		}

		$db = new FW_Backup_Helper_Database();

		list ($table_list, $view_list) = $db->query_schema();

		array_map(array($db, 'drop_table'), array_diff($table_list, $exclude_table));
		array_map(array($db, 'drop_view'), array_diff($view_list, $exclude_table));

		$db->foreach_statement($fp, FW_Backup_Callable::make(array($this, '_query'), $headers, $db));

		wp_cache_flush();

		return $headers['table_prefix'];
	}

	/**
	 * @internal
	 */
	public function _query($headers, FW_Backup_Helper_Database $db, $stmt)
	{
		$_translate_table_prefix = FW_Backup_Callable::make(array($this, '_translate_table_prefix'), $headers);
		$sql = preg_replace_callback('/^(CREATE TABLE|INSERT INTO) `(\w+)`/m', $_translate_table_prefix, $stmt);
		$db->query($sql);
	}

	/**
	 * @internal
	 */
	public function _translate_table_prefix($headers, array $m)
	{
		global $table_prefix;

		$table_name = preg_replace('/^' . preg_quote($headers['table_prefix']) . '/', $table_prefix, $m[2]);

		return $m[1] . " `$table_name`";
	}

	/**
	 * @important $fp should point to .sql dump constructed by $this->export_sql(),
	 * @important in other cases this might not work.
	 */
	public function get_headers_fp($fp)
	{
		$headers = array();

		while (!feof($fp)) {
			$line = fgets($fp);
			if (strpos($line, '-- ') === false) {
				break;
			}
			$colon = strpos($line, ':');
			$key = trim(substr($line, 3, $colon - 3));
			$value = trim(substr($line, $colon + 1));
			$headers[$key] = $value;
		}

		return $headers;
	}





	public function export_history()
	{
		/**
		 * @var FW_Extension_Backup $backup
		 */

		$backup = fw()->extensions->get('backup');
		$post_type = $backup->post_type()->get_post_type();

		$posts_per_page = 50;
		$count = wp_count_posts($post_type);

		$history = array();

		foreach (array('publish', 'trash') as $post_status) {
			for ($offset = 0; $offset < $count->$post_status; $offset += $posts_per_page) {
				foreach (get_posts(compact('post_type', 'post_status', 'posts_per_page', 'offset')) as $post) {
					$history[] = array($post->post_title, $post->post_status, $post->post_date, $post->post_content, get_post_meta($post->ID));
				}
			}
		}

		return $history;
	}

	public function import_history($history)
	{
		/**
		 * @var FW_Extension_Backup $backup
		 * @var int $post_id
		 */

		$backup = fw()->extensions->get('backup');
		$post_type = $backup->post_type()->get_post_type();

		foreach ($history as $row) {
			list ($post_title, $post_status, $post_date, $post_content, $post_meta) = $row;

			$post_id = wp_insert_post(compact('post_type', 'post_title', 'post_status', 'post_date', 'post_content'));
			if ($post_id == 0 || $post_id instanceof WP_Error) {
				throw new FW_Backup_Exception("Could not create post of type $post_type");
			}

			foreach ($post_meta as $meta_key => $meta_value_list) {
				update_post_meta($post_id, $meta_key, maybe_unserialize($meta_value_list[0]));
			}
		}
	}





	public function export_settings()
	{
		// Warning: Invalid argument supplied for foreach() in .../framework/extensions/backup/includes/exports/class-fw-backup-export-database.php on line 277
		return (array) fw_get_db_extension_data(fw()->extensions->get('backup')->get_name());
	}

	public function import_settings($settings)
	{
		foreach ($settings as $key => $value) {
			fw_set_db_extension_data(fw()->extensions->get('backup')->get_name(), $key, $value);
		}
	}
}
