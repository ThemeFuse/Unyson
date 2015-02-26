<?php if (!defined('FW')) {
	die('Forbidden');
}


/**
 * Webfonts
 */
 
class FW_Option_Type_Webfonts extends FW_Option_Type
{
	/*
	 * Allowed fonts
	 */
	private $fonts;

	public function dnp_get_google_fonts() {
		$cache_key = 'dnp_google_fonts';

		try {
			return FW_Cache::get($cache_key);
		} catch (FW_Cache_Not_Found_Exception $e) {
			$fonts = apply_filters('dnp_google_fonts',
				include( fw_get_framework_directory( '/includes/option-types/'. $this->get_type() . '/static/fonts/google-fonts.php') )
			);

			//return revamped $fonts array;		
			$google_fonts = array(); 
			foreach($fonts as $key=>$fontArray) {
				$google_fonts[$fontArray['family']] = $fontArray;
			}
			
			FW_Cache::set($cache_key, $google_fonts);

			return $google_fonts;		
		}
	}
	
	
	/**
	 * Returns fonts
	 * @return array
	 */
	public function get_fonts()
	{
		if($this->fonts === null) {
			$this->fonts = array(
				'google' => $this->dnp_get_google_fonts()
			);
		}

		return $this->fonts;
	}
	
	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_style(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/'. $this->get_type() . '/static/css/styles.css'),
			array('fw-selectize'),
			fw()->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-option-' . $this->get_type(),
			fw_get_framework_directory_uri( '/includes/option-types/'. $this->get_type() . '/static/js/scripts.js'),
			array('jquery', 'underscore', 'fw', 'fw-selectize'),
			fw()->manifest->get_version()
		);
		$dnp_typography_fonts = $this->get_fonts();
		
		wp_localize_script('fw-option-' . $this->get_type(), 'dnp_typography_fonts', $dnp_typography_fonts);
	}

	
	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		return fw_render_view( fw_get_framework_directory( '/includes/option-types/'. $this->get_type() . '/view.php'), array(
			'id' => $id,
			'option' => $option,
			'data' => $data,
			'fonts' => $this->get_fonts()
		));
	}

	public function get_type()
	{
		return 'webfonts';
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
			'family' => true,
			'style' => true,
			'subsets' => true,
		), $components);

		$values = array(
			'family' => ($components['family']) ? (isset($input_value['family'])) ? $input_value['family'] : $option['value']['family'] : false,
			'style' => ($components['family']) ? (isset($input_value['style'])) ? $input_value['style'] : $option['value']['style'] : false,
			'subsets' => ($components['family']) ? (isset($input_value['subsets'])) ? $input_value['subsets'] : $option['value']['subsets'] : false
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
				'family' => 'Oswald',
				'style'  => '400',
				'subsets'  => 'latin'
			),
			'components' => array(
				'family' => true,
				'style' => true,
				'subsets'  => true
			)
		);
	}

	public function _get_backend_width_type()
	{
		return 'auto';
	}
}

FW_Option_Type::register('FW_Option_Type_Webfonts');
