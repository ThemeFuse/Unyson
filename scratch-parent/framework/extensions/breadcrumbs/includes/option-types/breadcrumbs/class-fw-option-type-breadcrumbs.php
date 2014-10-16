<?php if (!defined('FW')) { die('Forbidden'); }

class FW_Option_Type_Breadcrumbs extends FW_Option_Type
{
	public function get_type()
	{
		return 'breadcrumbs';
	}

	private $internal_options = array();

	/**
	 * @internal
	 */
	public function _init()
	{
		$this->internal_options = array(
			'label' => false,
			'desc'  => false,
			'type'  => 'multi',
			'inner-options'  => array(
				'homepage-title'      => array(
					'label' => __( 'Text for Homepage', 'fw' ),
					'desc'  => __( 'The homepage anchor will have this text', 'fw' ),
					'type'  => 'text',
					'value' => __( 'Homepage', 'fw' )
				),
				'blogpage-title'      => array(
					'label' => __( 'Text for Blog Page', 'fw' ),
					'desc'  => __( 'The blog page anchor will have this text. In case homepage will be set as blog page, will be taken the homepage text', 'fw' ),
					'type'  => 'text',
					'value' => __( 'Blog', 'fw' )
				),
				'404-title'           => array(
					'label' => __( 'Text for 404 Page', 'fw' ),
					'desc'  => __( 'The 404 anchor will have this text', 'fw' ),
					'type'  => 'text',
					'value' => '404 Not Found'
				)
			),
		);
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type()
	{
		return 'full';
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		$uri = fw()->extensions->get('breadcrumbs')->get_declared_URI('/includes/option-types/' . $this->get_type() . '/static/css/style.css');

		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			$uri,
			array(),
			fw()->extensions->get('breadcrumbs')->manifest->get_version()
		);

		fw()->backend->option_type('multi')->enqueue_static();
	}

	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		$id = fw()->extensions->get('breadcrumbs')->get_option_id();

		return fw()->backend->option_type('multi')->render($id, $this->internal_options, $data);
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		return fw()->backend->option_type('multi')->get_value_from_input($this->internal_options, $input_value);
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array()
		);
	}
}

FW_Option_Type::register('FW_Option_Type_Breadcrumbs');
