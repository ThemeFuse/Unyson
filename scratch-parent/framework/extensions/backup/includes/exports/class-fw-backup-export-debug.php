<?php if (!defined('FW')) die('Forbidden');

class FW_Backup_Export_Debug implements FW_Backup_Interface_Export
{
	private $seconds;

	public function __construct($seconds = 5)
	{
		$this->seconds = $seconds;
	}

    public function export(FW_Backup_Interface_Feedback $feedback)
    {
	    $time_start = microtime(true);
	    $time_end = microtime(true) + $this->seconds;

	    $sleep = 0.1;

	    // Do not localize task names, they are used for debugging
	    $feedback->set_task('Debugging Export Layer');

	    while (microtime(true) < $time_end) {
		    usleep($sleep*1000000);
		    $total = $time_end - $time_start;
		    $complete = microtime(true) - $time_start;
		    $feedback->set_progress(min(100, number_format($complete/$total*100)) . '% -- ' . number_format($complete, 2) . 'sec');
	    }

	    $feedback->set_progress('100%');
	    $feedback->set_task('Debugging Export Layer Done');

	    $filename = sprintf('%s/backup-debug-%s.txt', sys_get_temp_dir(), date('Y_m_d-H_i_s'));
	    file_put_contents($filename, __FILE__);
	    return $filename;
    }
}
