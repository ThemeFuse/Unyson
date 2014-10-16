<?php if ( ! defined( 'FW' ) ) die( 'Forbidden' );


class FW_Option_Type_Event extends FW_Option_Type {
	private $internal_options = array();

	private static $extension;
	private $only_date_format = 'Y/m/d';
	private $date_time_format = 'Y/m/d H:i';
	private $min_date = '1970/01/01';
	private $max_date = '2038/01/19';

	public function get_type()
	{
		return 'event';
	}

	/**
	 * @internal
	 */
	public function _init() {
		$ext = fw()->extensions->get( 'events' );
		self::$extension = array(
			'path' => $ext->get_declared_path(),
			'URI'  => $ext->get_declared_URI()
		);

		$this->internal_options = array(
			'event_location' => array(
				'label' => __('Event Location', 'fw'),
				'type'  => 'map',
				'desc'  => __('Where does the event take place?', 'fw'),
			),

			'all_day' => array(
				'label' => __('All Day Event?', 'fw'),
				'desc'  => __('Is your event an all day event?', 'fw'),
				'type'  => 'switch',
				'right-choice' => array(
					'value' => 'yes',
					'label' => __('Yes', 'fw')
				),
				'left-choice' => array(
					'value' => 'no',
					'label' => __('No', 'fw')
				),
				'value' => 'no',
			),

			'event_children' => array(
				'label' => __('Date & Time', 'fw'),
				'popup-title' => __('Add/Edit Date & Time', 'fw'),
				'type' => 'addable-popup',
				'desc' => false,
				'attr' => array('class' => 'fw-event-datetime'),
				'template' => '{{  if (event_date_range.from !== "" || event_date_range.to !== "") {  print(event_date_range.from + " - " + event_date_range.to)} else { print("' . __('Note: Please set start & end event datetime', 'fw') . '")} }}',
				'popup-options' => array(
					'event_date_range' => array(
						'type'  => 'datetime-range',
						'label' => 'Start & End of Event',
						'desc'  => 'Set start and end events datetime',
						'datetime-pickers' => array(
							'from' => array(
								'maxDate' => $this->max_date,
								'minDate' => $this->min_date,
								'extra-formats'=> array($this->only_date_format),
								'fixed' => true,
								'timepicker' => true,
								'datepicker' => true,
								'defaultTime' => '08:00'
							),
							'to' => array(
								'maxDate' => $this->max_date,
								'minDate' => $this->min_date,
								'extra-formats'=> array($this->only_date_format),
								'fixed' => true,
								'timepicker' => true,
								'datepicker' => true,
								'defaultTime' => '18:00'
							)
						),
						'value' => array(
							'from' => '',
							'to'   => ''
						)
					),

					'event-user' => array(
						'type'       => 'multi-select',
						'label'      =>__('Associated User','fw'),
						'population' => 'users',
						'desc'       => __('Link this event to a specific user', 'fw'),
						'value'      => array()
					),
				),
			),


		);
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static($id, $option, $data)
	{
		wp_enqueue_script('fw-option-' . $this->get_type(),
			self::$extension['URI'] . '/includes/option-types/' . $this->get_type() . '/static/js/scripts.js',
			array('jquery', 'fw-events', 'editor', 'fw'),
			fw()->manifest->get_version()
		);
		wp_enqueue_style('fw-option-' . $this->get_type(),
			self::$extension['URI'] . '/includes/option-types/' . $this->get_type() . '/static/css/styles.css',
			array(),
			fw()->manifest->get_version()
		);
	}


	/**
	 * @internal
	 */
	protected function _render($id, $option, $data)
	{
		return fw_render_view( dirname(__FILE__) . '/view.php', array(
			'id'     => $id,
			'option' => $option,
			'data'   => $data,
			'internal_options' => $this->internal_options,
		) );
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input($option, $input_value)
	{

		if (is_null($input_value)) {
			return $option['value'];
		} else {
			$value = fw_get_options_values_from_input(
				$this->internal_options,
				$input_value
			);

			//remove time, if all_day selected
			$all_day = fw_akg('event_durability', $value);
			if ($all_day === 'yes') {
				foreach($value['event_datetime'] as $key => &$row) {
					if (isset($row['event_date_range']['from'])) {
						$row['event_date_range']['from'] = date($this->only_date_format, strtotime($row['event_date_range']['from']));
					}
					if (isset($row['event_date_range']['to'])) {
						$row['event_date_range']['to'] = date($this->only_date_format, strtotime($row['event_date_range']['to']));
					}
				}
			}

			return $value;
		}
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
	 */
	protected function _get_defaults()
	{
		return array(
			'value' => array(
			)
		);
	}

}
FW_Option_Type::register('FW_Option_Type_Event');
