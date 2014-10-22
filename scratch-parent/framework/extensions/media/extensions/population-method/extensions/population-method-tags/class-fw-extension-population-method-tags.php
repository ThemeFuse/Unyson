<?php if (!defined('FW')) die('Forbidden');

class FW_Extension_Population_Method_Tags extends FW_Extension implements Population_Method_Interface
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
		return array('tags' => __('Automatically, fetch images from tags', 'fw'));
	}

	public function get_population_options($multimedia_types, $custom_options)
	{
		$population_options = array();
		$post_categories = $this->get_post_categories();
		if (empty($post_categories)) {
			$message = sprintf(__('%s extension needs configured tags in post types ', 'fw'), ucwords(str_replace('-', ' ', $this->get_name())));
			wp_die($message);
		} else {
			$population_options = array(
				'wrapper-population-method-tags' => array(
					'title' => __('Tags Population Method', 'fw'),
					'type' => 'box',
					'options' => array(
						'tags' => array(
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
							'choices' => $this->get_post_tags_sets()
						),
						'number_of_images' => array(
							'type' => 'text',
							'label' => __('Number of Images in the slider', 'fw')
						)
					))
			);
		}

		return $population_options;
	}

	public function get_post_categories()
	{
		$collector = array();
		$post_types = get_post_types(array('public' => true), 'objects');

		foreach ($post_types as $key => $post_type) {

			$have_terms = $this->get_terms($key);

			if (!empty($have_terms)) {
				$collector[$key] = empty($post_type->labels->name) ? $post_type->label : $post_type->labels->name;
			}
		}

		return $collector;
	}

	private function get_terms($post_type)
	{
		$taxonomies = get_taxonomies(array('object_type' => array($post_type), 'hierarchical' => false));

		return get_terms($taxonomies);
	}

	private function get_post_tags_sets()
	{
		$post_types = get_post_types(array('public' => true));
		$terms_collector = array();

		foreach ($post_types as $post_type) {

			$terms = $this->get_terms($post_type);

			if (!empty($terms) && !is_wp_error($terms)) {
				$terms_collector = array();
				foreach ($terms as $term) {
					$key = json_encode(array('term_id' => $term->term_id, 'taxonomy' => $term->taxonomy));
					$terms_collector[$key] = $term->name;
				}
			}

			$collector[$post_type] = array(
				'terms' => array(
					'type' => 'select-multiple',
					'attr' => array('class' => 'selectize fw-selectize'),
					'label' => __('Select Specific tags', 'fw'),
					'choices' => $terms_collector,
				)
			);
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
			$number_of_images = (int)$meta['number_of_images'];

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

			$query_data = array();
			$post_type = $meta['tags']['selected'];
			$terms = $meta['tags'][$post_type]['terms'];

			$tax_query = array(
				'tax_query' => array(
					'relation' => 'OR'
				)
			);

			foreach ($terms as $term) {
				$decoded_data = json_decode($term, true);
				$query_data[$decoded_data['taxonomy']][] = $decoded_data['term_id'];
			}

			foreach ($query_data as $taxonomy => $terms) {
				$tax_query['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field' => 'id',
					'terms' => $terms
				);
			}

			$final_query = array_merge(array(
					'post_status' => 'publish',
					'posts_per_page' => $number_of_images,
					'post_type' => $post_type,
					'meta_key' => '_thumbnail_id',
				), $tax_query
			);

			global $post;
			$original_post = $post;

			$the_query = new WP_Query($final_query);

			while ($the_query->have_posts()) {
				$the_query->the_post();

				array_push($collector['slides'], array(
					'title' => get_the_title(),
					'src' => wp_get_attachment_url(get_post_thumbnail_id(get_the_ID())),
					'desc' => get_the_excerpt(),
					'extra' => array()
				));
			}

			wp_reset_postdata();

			$post = $original_post;
			unset($original_post);
		}

		return $collector;
	}
}