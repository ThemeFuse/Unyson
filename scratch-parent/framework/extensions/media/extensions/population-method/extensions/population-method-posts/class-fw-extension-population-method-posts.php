<?php if (!defined('FW')) die('Forbidden');

class FW_Extension_Population_Method_Posts extends FW_Extension implements Population_Method_Interface
{
	private $multimedia_types = array('image');

	/**
	 * @internal
	 */
	public function _init()
	{
	}

	public function get_multimedia_types()
	{
		return $this->multimedia_types;
	}

	public function get_population_method()
	{
		return array('posts' => __('Automatically, fetch images from posts', 'fw'));
	}

	public function get_population_options($multimedia_types, $custom_options)
	{
		$population_options = array();
		$post_categories = $this->get_post_categories();
		if (empty($post_categories)) {
			$message = sprintf(__('%s extension needs configured post categories in post types ', 'fw'), ucwords(str_replace('-', ' ', $this->get_name())));
			wp_die($message);
		} else {
			$population_options = array(
				'wrapper-population-method-posts' => array(
					'title' => __('Posts Population Method', 'fw'),
					'type' => 'box',
					'options' => array(
						'post_types' => array(
							'type' => 'multi-picker',
							'label' => false,
							'desc' => false,
							'picker' => array(
								'selected' => array(
									'label' => __('Choose Tag', 'fw'),
									'type' => 'select',
									'choices' => $this->get_post_categories()
								)
							),
							'choices' => $this->get_posts_sets()
						),
					))
			);
		}

		return $population_options;
	}

	public function get_post_categories()
	{
		$collector = array();
		$post_types = get_post_types(array('public' => true), 'objects');
		foreach ($post_types as $post_type => $post_type_obj) {
			$have_posts = $this->get_posts($post_type);
			if (!empty($have_posts)) {
				$collector[$post_type] = empty($post_type_obj->labels->name) ? $post_type_obj->label : $post_type_obj->labels->name;
			}
		}

		return $collector;
	}

	private function get_posts($post_type)
	{
		return get_posts(
			array(
				'post_type' => $post_type,
				'post_status' => 'publish',
				'meta_key' => '_thumbnail_id',
			));
	}

	private function get_posts_sets()
	{
		$post_types = get_post_types(array('public' => true));
		$collector = array();

		foreach ($post_types as $post_type) {
			$posts_collector = array();
			$posts = $this->get_posts($post_type);

			if (!empty($posts)) {
				foreach ($posts as $post) {
					$posts_collector[$post->ID] = empty($post->post_title) ? __('(no title)', 'fw') : $post->post_title;
				}

				$collector[$post_type] = array(
					'posts_id' => array(
						'type' => 'select-multiple',
						'attr' => array('class' => 'selectize fw-selectize'),
						'label' => __('Select Specific posts', 'fw'),
						'choices' => $posts_collector,
					)
				);
			}

		}

		return $collector;
	}

	public function get_number_of_images($post_id)
	{
		return (int)fw_get_db_post_option($post_id, 'number_of_images');
	}

	public function get_frontend_data($post_id)
	{
		$collector = array();
		$meta = fw_get_db_post_option($post_id);
		$post_status = get_post_status($post_id);

		if ('publish' === $post_status and isset($meta['populated'])) {
			$slider_name = $meta['slider']['selected'];
			$population_method = $meta['slider'][$slider_name]['population-method'];
			$posts_id = $meta['post_types'][$meta['post_types']['selected']]['posts_id'];

			$collector = array(
				'slides' => array(),
				'settings' => array(
					'title' => $meta['title'],
					'slider_type' => $slider_name,
					'population_method' => $population_method,
					'post_id' => $post_id,
					'extra' => array(),
				)
			);

			$posts = get_posts(array(
				'post_in' => $posts_id
			));

			foreach ($posts as $post) {
				setup_postdata($post);
				array_push($collector['slides'], array(
					'title' => get_the_title(),
					'src' => wp_get_attachment_url(get_post_thumbnail_id($post->ID)),
					'desc' => get_the_excerpt(),
					'extra' => array(
						'post_id' => $post->ID
					)
				));
			}
			wp_reset_postdata();
		}

		return $collector;
	}
}