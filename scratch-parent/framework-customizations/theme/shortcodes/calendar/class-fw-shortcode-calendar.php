<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Shortcode_Calendar extends FW_Shortcode {

	/**
	 * array(
	 *    'unique_id' => array(                                     // some unique id (string)
	 *              'callback' => array($this, 'callable_method')   // callback array(stdClass, 'public_method')
	 *              'label' => 'label',                             //data provider label (string)
	 *              'options' => array()                            //extra options (array of options)
	 *          )
	 *    )
	 */
	private static $data = array();

	public function _init() {

		self::$data = array(
			'custom' => array(
				'callback' => false,
				'label'    => __('Custom', 'unyson'),
				'options'  => array(
					'custom_events' => array(
						'label' => __('Date & Time', 'unyson'),
						'popup-title' => __('Add/Edit Date & Time', 'unyson'),
						'type' => 'addable-popup',
						'desc' => false,
						'template' => '{{  if (calendar_date_range.from !== "") {  print(calendar_date_range.from + " - " + calendar_date_range.to)} else { print("' . __('Note: Please set start & end event datetime', 'unyson') . '")} }}',
						'popup-options' => array(

							'title' => array(
								'type' => 'text',
								'label' =>__('Event Title','unyson'),
								'desc' => __('Set event title', 'unyson'),
							),

							'url' => array(
								'type' => 'text',
								'label' =>__('Event Url','unyson'),
								'desc' => __('Set evnt url (Ex: http://example.com)', 'unyson'),
							),

							'calendar_date_range' => array(
								'type'  => 'datetime-range',
								'label' => __('Date & Time','unyson'),
								'desc'  => __('Set start and end calendar datetime','unyson'),
								'datetime-pickers' => array(
									'from' => array(
										'maxDate' => '2038/01/19',
										'minDate' => '1970/01/01',
										'timepicker' => true,
										'datepicker' => true,
										'defaultTime' => '08:00',
									),
									'to' => array(
										'maxDate' => '2038/01/19',
										'minDate' => '1970/01/01',
										'timepicker' => true,
										'datepicker' => true,
										'defaultTime' => '18:00',
									)
								),
								'value' => array(
									'from' => '',
									'to' => ''
								)
							),

						),
					),
				)
			)
		);

		self::$data = apply_filters('fw_theme_shortcode_calendar_provider_init', self::$data);

		$this->_fw_theme_register_ajax();

		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, '_action_theme_add_static' ), 1 );
		}
	}

	private function _fw_theme_register_ajax(){
		add_action( 'wp_ajax_shortcode_calendar_get_events',        array($this, '_action_theme_get_results_json_ajax'));
		add_action( 'wp_ajax_nopriv_shortcode_calendar_get_events', array($this, '_action_theme_get_results_json_ajax'));
	}

	public function _action_theme_get_results_json_ajax() {
		if ( is_array(self::$data) === false ) {wp_send_json_error();}

		$data_provider = FW_Request::POST('data_provider');
		$result = call_user_func(self::$data[$data_provider]['callback'], FW_Request::POST());
		wp_send_json_success($result);

	}

	public static function fw_theme_get_options_choices() {
		if ( is_array(self::$data) === false ) { return array(); };

		$result = array();
		foreach(self::$data as $unique_filter_key => $item ) {
			$result[$unique_filter_key] = (isset($item['options']) && is_array($item['options'])) ? $item['options'] : array();
		}

		return $result;
	}

	public static function fw_theme_get_choices() {
		if ( is_array(self::$data) === false ) { return array(); };

		foreach(self::$data as $unique_filter_key => $item ) {
			$result[$unique_filter_key] = $item['label'];
		}

		return $result;
	}

	public function _action_theme_add_static(){

		wp_enqueue_style('fw-shortcode-calendar-plugin-component-bootstrap3-grid', $this->get_uri() . '/static/components/bootstrap3/css/bootstrap-grid.css' );
		wp_enqueue_style('fw-shortcode-calendar-css', $this->get_uri() . '/static/css/calendar.css' );
		wp_enqueue_style('fw-shortcode-calendar-style-css', $this->get_uri() . '/static/css/style.css' );


		wp_enqueue_script('fw-shortcode-calendar-plugin-component-bootstrap3',
			$this->get_uri() . '/static/components/bootstrap3/js/bootstrap.min.js',
			array('jquery', 'underscore'),
			fw()->theme->manifest->get_version(),
			true
		);

		wp_enqueue_script('fw-shortcode-calendar-plugin-component-timezone',
			$this->get_uri() . '/static/components/jstimezonedetect/jstz.min.js',
			array('jquery', 'underscore'),
			fw()->theme->manifest->get_version(),
			true
		);


		wp_enqueue_script('fw-shortcode-calendar-plugin',
			$this->get_uri() . '/static/js/calendar.js',
			array('jquery', 'underscore', 'fw-shortcode-calendar-plugin-component-timezone', 'fw-shortcode-calendar-plugin-component-bootstrap3'),
			fw()->theme->manifest->get_version(),
			true
		);

		wp_enqueue_script('fw-shortcode-calendar-app',
			$this->get_uri() . '/static/js/script.js',
			array('jquery', 'underscore', 'fw-shortcode-calendar-plugin'),
			fw()->theme->manifest->get_version(),
			true
		);

		$locale = get_locale();
		wp_localize_script('fw-shortcode-calendar-app', 'fwShortcodeCalendarLocalize',  array(
			'event'  => __('Event', 'fw'),
			'events' => __('Events', 'fw'),
			'today'  => __('Today', 'fw'),
			'locale' => $locale
		));

		wp_localize_script('fw-shortcode-calendar-app', 'calendar_languages',  array(
				$locale => array(
					'error_noview' => __('Calendar: View {0} not found', 'unyson'),
					'error_dateformat' => __('Calendar: Wrong date format {0}. Should be either "now" or "yyyy-mm-dd"', 'unyson'),
					'error_loadurl' => __('Calendar: Event URL is not set', 'unyson'),
					'error_where' => __('Calendar: Wrong navigation direction {0}. Can be only "next" or "prev" or "today"', 'unyson'),
					'error_timedevide' => __('Calendar: Time split parameter should divide 60 without decimals. Something like 10, 15, 30', 'unyson'),
					'no_events_in_day' => __('No events in this day.', 'unyson'),
					'title_year' => __('{0}', 'unyson'),
					'title_month' => __('{0} {1}', 'unyson'),
					'title_week' => __('week {0} of {1}', 'unyson'),
					'title_day' => __('{0} {1} {2}, {3}', 'unyson'),
					'week' => __('Week {0}', 'unyson'),
					'all_day' => __('All day', 'unyson'),
					'time' => __('Time', 'unyson'),
					'events' => __('Events', 'unyson'),
					'before_time' => __('Ends before timeline', 'unyson'),
					'after_time' => __('Starts after timeline', 'unyson'),
					'm0' => __('January', 'unyson'),
					'm1' => __('February', 'unyson'),
					'm2' => __('March', 'unyson'),
					'm3' => __('April', 'unyson'),
					'm4' => __('May', 'unyson'),
					'm5' => __('June', 'unyson'),
					'm6' => __('July', 'unyson'),
					'm7' => __('August', 'unyson'),
					'm8' => __('September', 'unyson'),
					'm9' => __('October', 'unyson'),
					'm10' => __('November', 'unyson'),
					'm11' => __('December', 'unyson'),
					'ms0' => __('Jan', 'unyson'),
					'ms1' => __('Feb', 'unyson'),
					'ms2' => __('Mar', 'unyson'),
					'ms3' => __('Apr', 'unyson'),
					'ms4' => __('May', 'unyson'),
					'ms5' => __('Jun', 'unyson'),
					'ms6' => __('Jul', 'unyson'),
					'ms7' => __('Aug', 'unyson'),
					'ms8' => __('Sep', 'unyson'),
					'ms9' => __('Oct', 'unyson'),
					'ms10' => __('Nov', 'unyson'),
					'ms11' => __('Dec', 'unyson'),
					'd0' => __('Sunday', 'unyson'),
					'd1' => __('Monday', 'unyson'),
					'd2' => __('Tuesday', 'unyson'),
					'd3' => __('Wednesday', 'unyson'),
					'd4' => __('Thursday', 'unyson'),
					'd5' => __('Friday', 'unyson'),
					'd6' => __('Saturday', 'unyson'),
				)
			)
		);
	}

	protected function handle_shortcode( $atts, $content, $tag ) {
		if (!isset($atts['data_provider']['gadget'])) {return false;}
		$provider = $atts['data_provider']['gadget'];
		if (isset(self::$data[$provider]) === false) return false;

		$ajax_params = apply_filters('fw_theme_shortcode_calendar_ajax_params', array(), $provider, fw_akg( 'data_provider/' . $provider, $atts ) );

		if (is_array($ajax_params)) {
			$ajax_params = array_merge($ajax_params, array('data_provider' => $provider));
		} else {
			$ajax_params = array('data_provider' => $provider );
		}

		$wrapper_atts = array(
			'data-extends-ajax-params'  => json_encode($ajax_params),
			'data-ajax-url'             => admin_url( 'admin-ajax.php' ),
			'data-template'             => $atts['template'],
			'data-template-path'        => $this->get_uri() . '/views/',
			'data-first-day'            => $atts['first_week_day'],
		);


		if ($provider === 'custom'){
			$rows = fw_akg('data_provider/custom/custom_events', $atts, array());
			$event_sources = array();

			if (empty($rows) === false) {
				$key = 0;
				foreach($rows as $row) {
					if (empty($row['calendar_date_range']['from']) or empty($row['calendar_date_range']['to'])) {
						continue;
					}
					$event_sources[$key]['id'] = $key;
					$start = new DateTime($row['calendar_date_range']['from'], new DateTimeZone('GMT'));
					$end   = new DateTime($row['calendar_date_range']['to'], new DateTimeZone('GMT'));

					//set end of all_day event time 23:59:59
					if ($start == $end and $end->format('H:i') === '00:00') {
						$end->modify('+23 hour');
						$end->modify('+59 minutes');
						$end->modify('+59 second');
					}

					$event_sources[$key]['start'] = $start->format('U');
					$event_sources[$key]['end']   = $end->format('U');
					$event_sources[$key]['title'] = $row['title'];
					$event_sources[$key]['url']   = $row['url'];
					$key++;
				}
			}

			$wrapper_atts['data-event-source'] = json_encode($event_sources);
		}

		return fw_render_view( $this->get_path() . '/views/view.php', compact('atts', 'content', 'tag', 'wrapper_atts'));
	}

}