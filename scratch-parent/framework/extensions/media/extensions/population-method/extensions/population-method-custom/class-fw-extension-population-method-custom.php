<?php if (!defined('FW')) die('Forbidden');

class FW_Extension_Population_Method_Custom extends FW_Extension implements Population_Method_Interface
{
	private $multimedia_types = array('image', 'video');

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
		return array('custom' => __('Manually, I\'ll upload the images myself'));
	}

	public function get_population_options($multimedia_types, $custom_options)
	{
		$media_type_choices = $this->transform_multimedia_types_array($multimedia_types);
		$media_type_values = array_keys($media_type_choices);
		$media_type_values = array_shift($media_type_values);

		$options = array(
			'wrapper-population-method-custom' => array(
				'title' => __('Click to edit / Drag to reorder <span class="fw-slide-spinner spinner"></span>', 'fw'),
				'type' => 'box',
				'options' => array(
					'custom-slides' =>
						array(
							'label' => false,
							'desc' => false,
							'type' => 'slides',
							'multimedia_type' => $media_type_values,
							'thumb_size' => array('height' => 75, 'width' => 138),
							'slides_options' => array(
								'multimedia' => array(
									'type' => 'multi-picker',
									'desc' => false,
									'label' => false,
									'hide_picker' => true,
									'picker' => array(
										'selected' => array(
											'type' => 'radio',
											'attr' => array('class' => 'multimedia-radio-controls'),
											'label' => __('Choose ', 'fw'),
											'choices' => $media_type_choices,
											'value' => $media_type_values
										)),
									'choices' => $this->get_multimedia_types_sets($multimedia_types)
								),
								'title' => array(
									'type' => 'text',
									'label' => __('Title', 'fw'),
								),
								'desc' => array(
									'type' => 'textarea',
									'label' => __('Description', 'fw'),
									'value' => ''
								),
							)
						)
				)
			)
		);

		if (!empty($custom_options)) {
			$options['wrapper-population-method-custom']['options']['custom-slides']['slides_options']['extra-options'] =
				array(
					'type' => 'multi',
					'attr' => array('class' => 'fw-no-border'),
					'label' => false,
					'desc' => false,
					'inner-options' => $custom_options,
				);
		}

		return $options;
	}

	private function transform_multimedia_types_array($multimedia_types)
	{
		return array_combine(
			array_values($multimedia_types),
			array_map('ucfirst', $multimedia_types)
		);
	}

	private function get_multimedia_types_sets($multimedia_types)
	{
		$options = array(
			'image' => array(
				'src' => array(
					'label' => __('Image', 'fw'),
					'type' => 'upload',
				)
			),
			'video' => array(
				'src' => array(
					'label' => __('Video', 'fw'),
					'type' => 'text'
				)
			),
		);

		$filtered_options = array();

		$filtered_multimedia_types = array_intersect($this->multimedia_types, $multimedia_types);

		foreach ($filtered_multimedia_types as $multimedia_type) {
			$filtered_options[$multimedia_type] = $options[$multimedia_type];
		}

		return $filtered_options;
	}

	public function get_number_of_images($post_id)
	{
		return count(fw_get_db_post_option($post_id, 'custom-slides', array()));
	}

	public function get_frontend_data($post_id)
	{
		$meta = fw_get_db_post_option($post_id);
		$post_status = get_post_status($post_id);

		$collector = array();

		if ('publish' === $post_status and isset($meta['populated'])) {

			$slider_name = $meta['slider']['selected'];
			$population_method = $meta['slider'][$slider_name]['population-method'];

			$collector = array(
				'slides' => array(),
				'settings' => array(
					'title' => $meta['title'],
					'slider_type' => $slider_name,
					'population_method' => $population_method,
					'post_id' => $post_id,
					'extra' => isset($meta['custom-settings']) ? $meta['custom-settings'] : array(),
				)
			);

			foreach ($meta['custom-slides'] as $slide) {
				array_push($collector['slides'], array(
					'title' => $slide['title'],
					'multimedia_type' => $slide['multimedia']['selected'],
					'src' => ($slide['multimedia']['selected'] === 'video') ? $slide['multimedia'][$slide['multimedia']['selected']]['src'] : $slide['multimedia'][$slide['multimedia']['selected']]['src']['url'],
					'desc' => $slide['desc'],
					'extra' => isset($slide['extra-options']) ? $slide['extra-options'] : array()
				));
			}
		}

		return $collector;
	}

}
