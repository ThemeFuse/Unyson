<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Shortcode_Map extends FW_Shortcode {

	/**
	 *  @var $data = array(
	 *                  'unique_id' => array(                               // some unique id (string)  required
	 *                       'callback' => array($this, 'callable_method')  // array(stdClass, 'some_public_method') required
	 *                       'label' => 'label',                            // data provider label (string) required
	 *                       'options' => array()                           // extra options (array of options) optional
	 *                   )
	 *               )
	 */
	private static $data = array();
	private $language = '';

	public function _init() {

		$this->language = substr( get_locale(), 0, 2 );

		self::$data = array(
			'custom' => array(
				'callback'   => array($this, 'fw_theme_get_static_locations'),
				'label'      => __('Custom','unyson'),
				'options'    => array(
					'locations' => array(
						'label' => __('Locations', 'unyson'),
						'popup-title' => __('Add/Edit Location', 'unyson'),
						'type' => 'addable-popup',
						'desc' => false,
						'template' => '{{  if (location.location !== "") {  print(location.location)} else { print("' . __('Note: Please set location', 'unyson') . '")} }}',
						'popup-options' => array(

							'location' => array(
								'type' => 'map',
								'label' =>__('Location','unyson'),
							),

							'title' => array(
								'type' => 'text',
								'label' => __('Location Title', 'unyson'),
								'desc' => __('Set location title', 'unyson'),
							),

							'description' => array(
								'type'  => 'textarea',
								'label' => __('Location Description', 'unyson'),
								'desc'  => __('Set location description', 'unyson')
							),

							'url' => array(
								'type'  => 'text',
								'label' => __('Location Url', 'unyson'),
								'desc'  => __('Set page url (Ex: http://example.com)', 'unyson'),
							),

							'thumb' => array(
								'label'       => __('Location Image', 'fw'),
								'desc'        => __('Add location image', 'fw'),
								'type'        => 'upload',
								'images_only' => true,
							),

						),
					),
				)
			)
		);

		self::$data = apply_filters('fw_theme_shortcode_map_provider_init', self::$data);

		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, '_action_theme_add_static' ) );
		}

	}

	public function _action_theme_add_static()
	{
		$this->_fw_theme_inc_css();
		$this->_fw_theme_inc_js();
	}

	/**
	 * Get the list of providers
	 */
	public static function fw_theme_get_choices() {
		if ( is_array(self::$data) === false ) { return array(); };

		$result = array();
		foreach(self::$data as $unique_key => $item ) {
			$result[$unique_key] = $item['label'];
		}

		return $result;
	}

	/**
	 * Get the array of options providers
	 */
	public static function fw_theme_get_options_choices() {
		if ( is_array(self::$data) === false ) { return array(); };

		$result = array();
		foreach(self::$data as $unique_key => $item ) {
			$result[$unique_key] = (isset($item['options']) && is_array($item['options'])) ? $item['options'] : array();
		}

		return $result;
	}

	protected function handle_shortcode( $atts, $content, $tag ) {
		if (!isset($atts['data_provider']['gadget'])) {return false;}
		$provider = $atts['data_provider']['gadget'];
		if (isset(self::$data[$provider]) === false) return false;

		/**
		 * @var $locations array structure:
		 * array(
		 *      array(
		 *          'title' => 'some_string',              //some text  (string) optional
		 *          'url'   => 'http://example.com'        //some uri   (string) optional
		 *          'description' => 'some string'         //some text  (string) optional
		 *          'thumb' => array(
		 *              'attachment_id' => '1'             //Existing atachment id (int)  optional
		 *          )
		 *          'coordinates' => array(                //key 'coordinates'   required
		 *              'lat' => 150                       //latitude   (float)  required
		 *              'lng' => -33.5                     //longitude  (float)  required
		 *          )
		 *      )
		 * )
		 */
		$locations = call_user_func( self::$data[$provider]['callback'], $atts );

		if ( !empty($locations) && is_array($locations) ) {
			foreach( $locations as $key => $location ) {
				if (
					!isset($location['coordinates'])
					or !is_array($location['coordinates'])
					or !isset($location['coordinates']['lat'])
					or !isset($location['coordinates']['lng'])
					or empty($location['coordinates']['lat'])
					or empty($location['coordinates']['lng'])
					)
				{
					//remove locations which has wrong coordinates/empty
					unset($locations[$key]);
				}
			}
		}

		$div_attr = array(
			'data-locations'  => json_encode(array_values($locations)),
			'data-map-type'   => strtoupper( fw_akg('map_type', $atts, 'roadmap') ),
			'data-map-height' => fw_akg('map_height', $atts, false),
		);

		return fw_render_view( $this->get_path() . '/views/view.php', compact('atts', 'content', 'tag', 'div_attr'));
	}

	public function fw_theme_get_static_locations($atts) {
		$rows = fw_akg('data_provider/custom/locations', $atts, array());

		$result = array();
		if (!empty($rows)) {
			foreach($rows as $key => $row) {
				$result[$key]['title']       = fw_akg('title', $row);
				$result[$key]['url']         = fw_akg('url', $row);
				$result[$key]['thumb']       = fw_resize(wp_get_attachment_url(fw_akg('thumb/attachment_id', $row)), 100, 60, true);
				$result[$key]['coordinates'] = fw_akg('location/coordinates', $row);
				$result[$key]['description'] = fw_akg('description', $row);
			}
		}

		return $result;
	}

	/**
	 * Just a wrapper for the method render
	 * @param $extra array
	 * @param $data array
	 * @return string Generated shortcode html
	 *
	 * @var $extra = arrray(
	 *          'map_type'   => 'roadmap' // string any of (roadmap | terrain | satellite | hybrid )
	 *          'map_height' => '300'     // int height for map canvas block
	 * )
	 *
	 * @var $data = array(
	 *                  array(
	 *                      'description' => 'some desc'   //string
	 *                      'thumb' => array(
	 *                             'attachment_id' => '1'  //int any existing attachment id
	 *                       )
	 *                      'title' =>  'some title',      //string
	 *                      'url'   =>  'http://link.com', //string
	 *                      'location' => array(
	 *                            'coordinates' => array(
	 *                                  'lat' =>  -12,     //int
	 *                                  'lng' => 10        //int
	 *                                  )
	 *                             )
	 *                       )
	 *                   )
	 */
	public function render_custom($data, $extra = array()) {
		$atts = array(
			'map_height'    => fw_akg('map_height', $extra, false),
			'map_type'      => fw_akg('map_type', $extra, 'roadmap'),
			'data_provider' => array(
						'gadget' => 'custom',
						'custom' => array(
							'locations' => $data
						),
			)
		);
		return $this->handle_shortcode($atts, null, '');
	}

	private function _fw_theme_inc_js() {
		wp_enqueue_script(
			'google-maps-api-v3',
			'https://maps.googleapis.com/maps/api/js?v=3.15&sensor=false&libraries=places&language=' . $this->language,
			array(),
			'3.15',
			true
		);

		wp_enqueue_script(
			'fw-shortcode-map-script',
			$this->get_uri() . '/static/js/script.js',
			array('jquery', 'google-maps-api-v3', 'underscore')
		);
	}

	private function _fw_theme_inc_css(){
		wp_enqueue_style(
			'fw-theme-shortcode-map-style',
			$this->get_uri() . '/static/css/style.css'
		);
	}
}