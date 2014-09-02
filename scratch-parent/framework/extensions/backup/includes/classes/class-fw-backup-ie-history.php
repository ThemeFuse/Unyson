<?php if (!defined('FW')) die('Forbidden');

// FIXME This method is slow for large amount of backups
class FW_Backup_IE_History implements FW_Backup_Interface_IE
{
	private $post_type;

	public function __construct($post_type)
	{
		$this->post_type = $post_type;
	}

	public function import($fp)
	{
		/**
		 * @var int $post_id
		 */

		$post_type = $this->post_type;

		while ($csv = fgetcsv($fp)) {
			list ($post_title, $post_status, $post_date, $post_content, $post_meta) = $csv;

			$post_id = wp_insert_post(compact('post_type', 'post_title', 'post_status', 'post_date', 'post_content'));
			if ($post_id == 0 || $post_id instanceof WP_Error) {
				throw new FW_Backup_Exception('Could not create post of type '.$this->post_type);
			}

			foreach (unserialize($post_meta) as $meta_key => $meta_value_list) {
				update_post_meta($post_id, $meta_key, maybe_unserialize($meta_value_list[0]));
			}
		}
	}

	public function export($fp)
	{
		$post_type = $this->post_type;
		$posts_per_page = 50;
		$count = wp_count_posts($this->post_type);

		foreach (array('publish', 'trash') as $post_status) {
			for ($offset = 0; $offset < $count->$post_status; $offset += $posts_per_page) {
				foreach (get_posts(compact('post_type', 'post_status', 'posts_per_page', 'offset')) as $post) {
					fputcsv($fp, array($post->post_title, $post->post_status, $post->post_date, $post->post_content, serialize(get_post_meta($post->ID))));
				}
			}
		}
	}
}
