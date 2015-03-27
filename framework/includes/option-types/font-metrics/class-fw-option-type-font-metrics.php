<?php if (!defined('FW')) {
	die('Forbidden');
}


/**
 * Font metrics
 */
 
 /* Basic structure 
 
'option_id' => array(
	'label' => __( 'Typography metrics', 'unyson' ),
	'type'  => 'font-metrics',
	'value' => array(
		'font-size'   => array(
			'value' => 14, 
			'properties' => array('min' => 11, 'max' => 16, 'step' => 1),
		),	
		'line-height' => array( 
			'value' => 20, 
			'properties' => array('min' => 18, 'max' => 32, 'step' => 1),
		),	
		'letter-spacing'  => array( 
			'value' => 0, 
			'properties' => array('min' => -15, 'max' => 5, 'step' => 1),
		),
		'transform'  => 'none'
	),
	// 'components' => array('font-size' => true, 'line-height' => false,  'letter-spacing' => false ),
	'desc'  => '',
	'help'  => 'Here\'s for the metrics',
),
 
 */
 
 
 
class FW_Option_Type_Font_Metrics extends FW_Option_Type
{
	
	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		//slider field resources
		{
			wp_enqueue_style(
				'fw-option-slider ion-range-slider',
				fw_get_framework_directory_uri( '/includes/option-types/slider/static/libs/ion-range-slider/ion.rangeSlider.css' ),
				'2.0.3'
			);

			wp_enqueue_script(
				'fw-option-slider ion-range-slider',
				fw_get_framework_directory_uri( '/includes/option-types/slider/static/libs/ion-range-slider/ion.rangeSlider.min.js' ),
				array( 'jquery', 'fw-moment' ),
				'2.0.3'
			);
		}

		wp_enqueue_style(
			'fw-option-slider',
			fw_get_framework_directory_uri( '/includes/option-types/slider/static/css/styles.css' ),
			fw()->manifest->get_version()
		);
		
		//field specific resources
		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/css/styles.css'),
			fw()->manifest->get_version()
		);
		
		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/' . $this->get_type() . '/static/js/scripts.js'),
			array('jquery', 'fw-events', 'underscore', 'fw-option-slider ion-range-slider'),
			fw()->manifest->get_version()
		);
	}

	
	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		//  $data['value']['font-size']['value'] OR  $data['value']['font-size'] depends if there is actually some db saved $data
		$option['value']['font-size']['properties']['type'] = 'single';		
		$option['value']['font-size']['properties']['from'] = ( isset($data['value']['font-size']) ) ? ( isset($data['value']['font-size']['value']) ? $data['value']['font-size']['value'] : $data['value']['font-size']  ) : $option['value']['font-size']['value'];
		
		$option['value']['line-height']['properties']['type'] = 'single';		
		$option['value']['line-height']['properties']['from'] = ( isset($data['value']['line-height']) ) ? ( isset($data['value']['line-height']['value']) ? $data['value']['line-height']['value'] : $data['value']['line-height'] ) : $option['value']['line-height']['value'];
		
		$option['value']['letter-spacing']['properties']['type'] = 'single';		
		$option['value']['letter-spacing']['properties']['from'] = ( isset($data['value']['letter-spacing']) ) ? ( isset($data['value']['letter-spacing']['value']) ? $data['value']['letter-spacing']['value'] : $data['value']['letter-spacing'] ) : $option['value']['letter-spacing']['value'];
		
		$defaults = $this->_get_defaults();
		
		if ( isset($option['value']['font-size']['properties']) ) {
			$attr1 = array_merge( $defaults['value']['font-size']['properties'], $option['value']['font-size']['properties'] );
		} else {
			$attr1 = $defaults['value']['font-size']['properties'];
		}
		
		$option['value']['font-size']['attr']['data-fw-irs-options'] = json_encode( $attr1 );	
		
		if ( isset($option['value']['line-height']['properties']) ) {
			$attr2 = array_merge( $defaults['value']['line-height']['properties'], $option['value']['line-height']['properties'] );
		} else {
			$attr2 = $defaults['value']['line-height']['properties'];
		}
		
		$option['value']['line-height']['attr']['data-fw-irs-options'] = json_encode( $attr2 );
		
		if ( isset($option['value']['letter-spacing']['properties']) ) {
			$attr3 = array_merge( $defaults['value']['letter-spacing']['properties'], $option['value']['letter-spacing']['properties'] );
		} else {
			$attr3 = $defaults['value']['letter-spacing']['properties'];
		}
		
		$option['value']['letter-spacing']['attr']['data-fw-irs-options'] = json_encode( $attr3 );
		
		
		return fw_render_view( fw_get_framework_directory( '/includes/option-types/'. $this->get_type() . '/view.php'), array(
			'id' => $id,
			'option' => $option,
			'data' => $data,
		));
	}

	public function get_type()
	{
		return 'font-metrics';
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{
		if (!is_array($input_value)) {
			return $option['value'];
		}
				
		$components = (isset($option['components']) && is_array($option['components'])) ? $option['components'] : array();
		$components = array_merge(array(
			'font-size' => true,
			'line-height' => true,
			'letter-spacing' => true,
			'transform' => true,
		), $components);

		$values = array(
			'font-size' => ($components['font-size']) ? (isset($input_value['font-size'])) ? intval($input_value['font-size']) : intval($option['value']['font-size']['value']) : false,
			'line-height' => ($components['line-height']) ? (isset($input_value['line-height'])) ? intval($input_value['line-height']) : intval($option['value']['line-height']['value']) : false,
			'letter-spacing' => ($components['letter-spacing']) ? (isset($input_value['letter-spacing'])) ? intval($input_value['letter-spacing']) : intval($option['value']['letter-spacing']['value']) : false,
			'transform' => ($components['transform']) ? (isset($input_value['transform'])) ? $input_value['transform'] : $option['value']['transform'] : false,
		);
		return $values;		
	}

	/**
	 * @internal
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array(
				'font-size' => array(
					'value' => 14,
					'properties' => array(
						'min' => 14,
						'max' => 72,
						'step' => 1
					)
				),
				'line-height'  => array(
					'value' => 20,
					'properties' => array(
						'min' => 18,
						'max' => 32,
						'step' => 1
					)
				),
				'letter-spacing'  => array(
					'value' => 0,
					'properties' => array(
						'min' => -16,
						'max' => 5,
						'step' => 1
					)
				),
				'transform'  => 'none'
			),
			'components' => array(
				'font-size' => true,
				'line-height' => true,
				'letter-spacing'  => true,
				'transform'  => true
			)
		);
	}

	public function _get_backend_width_type()
	{
		return 'auto';
	}
}

FW_Option_Type::register('FW_Option_Type_Font_Metrics');
