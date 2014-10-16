<?php if (!defined('FW')) die('Forbidden');

class FW_Extension_Events_Tags extends FW_Extension {

	private $post_type_slug;
	private $post_type;
	private $data_provider_id    = 'events';
	private $event_to_date_tag   = 'event-to-date';
	private $event_from_date_tag = 'event-from-date';
	private $event_from_time_tag = 'event-from-time';
	private $event_to_time_tag   = 'event-to-time';
	private $all_day_tag         = 'all_day_event';


	private function _fw_define_slugs() {
		$this->post_type = apply_filters( 'fw_ext_' . $this->get_name() . '_post_slug', $this->get_parent()->get_post_type_name() . '-search');
		$this->post_type_slug = apply_filters( 'fw_ext_' . $this->get_name() . '_post_slug', $this->get_parent()->fw_get_post_type_slug() . '-search');
	}

	public function _init(){
		$this->_fw_define_slugs();

		add_action('init',                                      array($this,'_action_register_post_type_tags'));
		add_filter('fw_theme_shortcode_calendar_provider_init', array($this, '_filter_theme_shortcode_calendar_set_provider'));
		add_filter('fw_theme_shortcode_map_provider_init',      array($this, '_filter_theme_shortcode_map_set_provider'));

		if (is_admin()) {
			$this->_fw_add_admin_actions();
		} else {
			$this->_fw_add_theme_filters();
		}
	}

	private function _fw_add_theme_filters() {
		add_filter('fw_theme_shortcode_calendar_ajax_params',   array($this, '_filter_theme_shortcode_calendar_ajax_params' ), 10, 3);
	}

	/**
	 * Register extension events in shortcode "Map", with initial data
	 * @param $value type array
	 * @return mixed
	 */
	public function _filter_theme_shortcode_map_set_provider($value) {
		$value[$this->data_provider_id] = array(
					'label' => __('Events', 'fw'),
					'callback' => array($this, 'fw_get_events_locations'),
					'options' => array(
						'events_category' => array(
							'label' => __('Event Categories', 'fw'),
							'desc'  => __('Select an event category', 'fw'),
							'type'  => 'select',
							'choices' => array('' => __('All Events','fw')) + $this->_fw_get_event_terms_choices()
						)
					)
				);

		return $value;
	}

	/**
	 * Get all/by_category event's locations from db
	 * @param $atts type array
	 * @return array
	 */
	public function fw_get_events_locations($atts) {

		$category =  fw_akg('data_provider/' . $this->data_provider_id . '/events_category', $atts);

		$args = array(
			'post_type' => $this->get_parent()->get_post_type_name(),
			'posts_per_page' => -1,
			'post_status' => 'publish'
		);

		// add taxonomy term query args
		{
			$terms_ids = array();
			$with_category = false;
			if (preg_match('/^\d+$/', $category) ) {
				$terms_ids = get_term_children( $category, $this->get_parent()->get_taxonomy_name());
				if ( is_array($terms_ids) and false === empty($terms_ids) and false === is_wp_error($terms_ids) ){
					$terms_ids[] = (int)$category;
				} else {
					$terms_ids = array($category);
				}
				$with_category = true;
			}

			if ($with_category){
				$args['tax_query'] = array(
					array(
						'taxonomy' => $this->get_parent()->get_taxonomy_name(),
						'field'    => 'id',
						'terms'    => $terms_ids,
						'operator' => 'IN'
					),
				);
			}
		}

		$query = new WP_Query($args);
		$posts = $query->get_posts();
		wp_reset_query();

		$result = array();
		if (is_array($posts) && count($posts) > 0) {
			foreach($posts as $key => $post) {
				$meta                            = fw_get_db_post_option( $post->ID, $this->get_parent()->get_event_option_id() );
				$location = trim(fw_akg('event_location/location', $meta, ''));
				if (false === empty($location)) {
					$result[$key]['title']       = $post->post_title;
					$result[$key]['coordinates'] = fw_akg('event_location/coordinates', $meta, array());
					$result[$key]['url']         = get_permalink( $post->ID );
					$result[$key]['thumb']       = fw_resize(wp_get_attachment_url(get_post_thumbnail_id($post->ID)), 100, 60, true);
					$result[$key]['description'] = $location;
				}
			}
		}

		return $result;
	}

	/**
	 * Fill shortcode Calendar with initial data
	 * @param $value - list of data providers
	 * @return mixed
	 */
	public function _filter_theme_shortcode_calendar_set_provider($value){
		$value[$this->data_provider_id] = array(
					'label'    => __('Events', 'fw'),
					'callback' => array($this, 'fw_get_events_by_range'),
					'options'  => array(
						'events_category' => array(
							'label' => __('Event Categories', 'fw'),
							'desc'  => __('Select an event category', 'fw'),
							'type'  => 'select',
							'choices' => array('' => __('All Events','fw')) + $this->_fw_get_event_terms_choices()
						)
					)
			);
		return $value;
	}

	/**
	 * Saved option 'events_category' sets as ajax parameter
	 * @param $value type array()           - presetted ajax parameters (e.g. array('ajax_post_param' => 'string value') )
	 * @param $provider type string         - choosen data provider
	 * @param $option_values type array()   - user saved option values
	 * @return array                        - ajax parameters (e.g. array('ajax_post_param' => 'string value') )
	 */
	public function _filter_theme_shortcode_calendar_ajax_params($value, $provider, $option_values ){
		if ( $provider === $this->data_provider_id ) {
			if (is_array($value)) {
				return array_merge($value, $option_values);
			}
			return $option_values;
		}
		return $value;
	}

	/**
	 * Generate array of terms for option choices
	 */
	private function _fw_get_event_terms_choices(){
		$terms = get_terms($this->get_parent()->get_taxonomy_name(), array(
			'hide_empty' => 0
		));

		if (is_wp_error($terms)) {
			return array();
		}

		$result = array();
		if (is_array($terms) && !empty($terms)) {
			foreach($terms as $term) {
				$name = trim($term->name);
				$result[$term->term_id] = empty($name) ? $term->slug : $name;
			}
		}

		return $result;
	}

	private function _fw_add_admin_actions(){
		add_action( 'fw_save_post_options', array($this, '_action_admin_on_save_event'));
		add_action( 'before_delete_post',   array($this, '_action_admin_on_delete_event'));
	}

	public function _action_admin_on_save_event($post_id){
		if ( get_post_type($post_id) !== $this->get_parent()->get_post_type_name() or !fw_is_real_post_save($post_id) ) return;

		$this->_fw_remove_all_event_children_data($post_id);
		$this->_fw_insert_all_event_children_data($post_id);
	}

	public function _action_admin_on_delete_event($post_id) {
		if ( get_post_type($post_id) !== $this->get_parent()->get_post_type_name() ) return;
		$this->_fw_remove_all_event_children_data($post_id);
	}

	public function _action_register_post_type_tags(){

		register_post_type( $this->post_type, array(
			'labels'                => false,
			'description'           => false,
			'public'                => false,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'publicly_queryable'    => false,
			'exclude_from_search'   => true,
			'show_in_admin_bar'     => false,
			'has_archive'           => false,
			'rewrite'               => array(
				'slug' => $this->post_type_slug
			),
			'show_in_nav_menus'     => false,
			'hierarchical'          => true,
			'query_var'             => false,
			'supports'              => array(
				'author'
			)
		) );

	}

	/**
	 * Remove fw-event-tags posts from db related with fw-event post.
	 * @internal
	 */
	private function _fw_remove_all_event_children_data($post_id) {

		$args = array(
			'post_parent' => $post_id,
			'post_type' => $this->post_type,
			'post_status' => 'any'
		);

		$posts = get_posts( $args );

		if (is_array($posts) && count($posts) > 0) {

			foreach($posts as $post){
				wp_delete_post($post->ID, true);
			}

		}

	}

	/**
	 * For even datetime range row create custom 'fw-events-tags' post. Also save search query tags as meta values.
	 * @internal
	 */
	private function _fw_insert_all_event_children_data($post_id) {
		$options_values = fw_get_db_post_option($post_id);
		if ( is_array($options_values) === false ) {return false;}

		$container_id = $this->get_parent()->get_event_option_id();
		$meta_rows_data = fw_akg( $container_id . '/event_children', $options_values);
		$all_day_event  = fw_akg( $container_id . '/all_day', $options_values);

		if (is_array($meta_rows_data) && count($meta_rows_data) > 0) {
			foreach ($meta_rows_data as $meta_row) {

				$start_date = fw_akg('event_date_range/from', $meta_row);
				$end_date = fw_akg('event_date_range/to', $meta_row);

				$from_timestamp = strtotime($start_date);
				$to_timestamp   = strtotime($end_date);

				if ( !$from_timestamp || !$to_timestamp || -1 === $from_timestamp || -1 === $to_timestamp ) {
					continue;
				}

				$event_post_tag_id = wp_insert_post(
				array(
					'post_parent' => $post_id,
					'post_type'   => $this->post_type,
					'post_status' => 'publish'
				), true);

				if ($event_post_tag_id == 0 || $event_post_tag_id instanceof WP_Error) {
					throw new Exception(sprintf(__('wp_insert_post(post_type=%s) failed', 'fw'), $this->post_type));
				}

				add_post_meta($event_post_tag_id, $this->event_from_date_tag, $from_timestamp - (date('H', $from_timestamp)*3600 + date('i', $from_timestamp)*60) );
				add_post_meta($event_post_tag_id, $this->event_to_date_tag,   $to_timestamp - (date('H', $to_timestamp)*3600 + date('i', $to_timestamp)*60) );
				add_post_meta($event_post_tag_id, $this->event_from_time_tag, date('H', $from_timestamp)*3600 + date('i', $from_timestamp)*60 );
				add_post_meta($event_post_tag_id, $this->event_to_time_tag,   date('H', $to_timestamp)*3600 + date('i', $to_timestamp)*60 );
				add_post_meta($event_post_tag_id, $this->all_day_tag,         $all_day_event );

				$users = fw_akg('event-user', $meta_row);
				if (is_array($users) && count($users) > 0) {
					foreach($users as $user) {
						add_post_meta($event_post_tag_id, 'event-user', $user);
					}
				}
			}
		}
	}

	/**
	 * @param $params array(
	 *                  'from' => 123234455      - (int) start (unixtime) range query
	 *                  'to'   => 435455645      - (int) end (unixtime) range query
	 *                  'template' => 'day'      - (string) group dates for template (day grouped sensitivity equal minutes, else )
	 *                  'events_category'        - (int) parent Events term id
	 *                )
	 * @return array
	 */
	public function fw_get_events_by_range($params){
		$from = fw_akg('from', $params);
		$to   = fw_akg('to', $params);

		if ( empty($from) or empty($to) or !preg_match('/^\d+$/', $from) or !preg_match('/^\d+$/', $to) or ($to < $from) ) {
			return array();
		}

		$group_for = fw_akg('template', $params);
		$category  = fw_akg('events_category', $params);

		global $wpdb;

		$terms_ids = array();
		$with_category = false;
		if (preg_match('/^\d+$/', $category) ) {
			$terms_ids = get_term_children( $category, $this->get_parent()->get_taxonomy_name());
			if ( is_array($terms_ids) and false === empty($terms_ids) and false === is_wp_error($terms_ids) ){
				$terms_ids[] = (int)$category;
			} else {
				$terms_ids = array($category);
			}
			$with_category = true;
		}

			$sql = 'SELECT distinct '. $wpdb->postmeta .'.post_id as \'id\', from_date.meta_value as \'start_date\', to_date.meta_value as \'end_date\', from_time.meta_value as \'start_time\', all_day.meta_value as \'all_day\' , to_time.meta_value as \'end_time\', ' .  $wpdb->posts . '.post_parent';

			if ($with_category) {
				$sql .= ' , ' . $wpdb->term_taxonomy . '.term_id as \'term_id\'';
			}

			$sql .= ' FROM '. $wpdb->postmeta .' INNER JOIN wp_posts ON ( ' . $wpdb->posts . '.ID=' . $wpdb->postmeta . '.post_id AND '. $wpdb->posts .'.post_type=\'' .$this->post_type .'\') ' .
			'INNER JOIN '. $wpdb->postmeta .' AS to_date   ON ( to_date.meta_key=\'' . $this->event_to_date_tag . '\'   AND to_date.post_id   = '. $wpdb->postmeta .'.post_id ) ' .
			'INNER JOIN '. $wpdb->postmeta .' AS from_date ON ( from_date.meta_key=\''. $this->event_from_date_tag .'\' AND from_date.post_id = '. $wpdb->postmeta .'.post_id ) ' .
			'LEFT JOIN '. $wpdb->postmeta .'  AS all_day   ON ( all_day.meta_key=\''. $this->all_day_tag .'\'           AND all_day.post_id   = '. $wpdb->postmeta .'.post_id ) ' .
			'LEFT JOIN '. $wpdb->postmeta .'  AS to_time   ON ( to_time.meta_key=\'' . $this->event_to_time_tag . '\'   AND to_time.post_id   = '. $wpdb->postmeta .'.post_id ) ' .
			'LEFT JOIN '. $wpdb->postmeta .'  AS from_time ON ( from_time.meta_key=\''. $this->event_from_time_tag .'\' AND from_time.post_id = '. $wpdb->postmeta .'.post_id ) ';

			if ($with_category) {
				$sql .= 'INNER JOIN ' . $wpdb->term_relationships . ' ON (' . $wpdb->posts . '.post_parent = '. $wpdb->term_relationships.'.object_id) ' .
				' INNER JOIN ' . $wpdb->term_taxonomy . ' ON (' . $wpdb->term_relationships . '.term_taxonomy_id = ' . $wpdb->term_taxonomy . '.term_taxonomy_id ) ';
			}

			$sql .= ' WHERE 1=1 ';

			$sql .= 'AND (
				(from_date.meta_value >= ' . $from . ' AND from_date.meta_value <= ' . $to . ' AND  to_date.meta_value >= ' . $to . ' AND to_date.meta_value >= ' . $from . ' )
				OR
				(from_date.meta_value >= ' . $from . ' AND from_date.meta_value <= ' . $to . ' AND  to_date.meta_value >= ' . $from . ' AND to_date.meta_value <= ' . $to . ' )
				OR
				(from_date.meta_value <= ' . $from . ' AND from_date.meta_value <= ' . $to . ' AND  to_date.meta_value >= ' . $from . ' AND to_date.meta_value >= ' . $to . ' )
				OR
				(from_date.meta_value >= ' . $from . ' AND from_date.meta_value <= ' . $to . ' AND  to_date.meta_value >= ' . $from . ' AND to_date.meta_value <= ' . $to . ' )
				OR
				(from_date.meta_value <= ' . $from . ' AND to_date.meta_value <= ' . $to . ' AND to_date.meta_value >= ' . $from . ' )
			)';

			if ($with_category) {
				$sql .= 'AND ' . $wpdb->term_taxonomy . '.term_id IN (' . implode(',', $terms_ids) .')';
			}


		$items = $wpdb->get_results($sql);

		$result = array();
		if (is_array($items) and count($items) > 0) {

			if ($group_for === 'day') {
				$result = $this->_fw_prepare_data($items, 'YmdHi');
			} else {
				$result = $this->_fw_prepare_data($items, 'Ymd');
			}

		}

		return $result;
	}


	/**
	 * Prepare data structure compatible with shortcode Calendar
	 * @internal
	 */
	private function _fw_prepare_data($items, $format = null) {
		foreach($items as $key => $item) {

			//start datetime
			{
				$timestamp_start_date = $item->start_date;
				$timestamp_start_time = $item->start_time;
				$result[$item->post_parent][$key]['start'] = ($timestamp_start_date + $timestamp_start_time);
			}

			//end datetime
			{
				$timestamp_end_date = $item->end_date;
				$timestamp_end_time = (strtolower($item->all_day) === 'yes' ? 86399 : $item->end_time);  // 23:59:59 86399 //86400 24:00:00
				$result[$item->post_parent][$key]['end'] = ($timestamp_end_date + $timestamp_end_time);
			}

		}

		$result = $this->_fw_grouped_calendar_dates($result, $format);
		$return_value = array();
		$i = 0;
		//fill return value with shrortcode Calendar supported data structure
		foreach($result as $event_id => $intervals) {
			$title = get_the_title($event_id);
			$url   = get_permalink($event_id);
			foreach($intervals as $interval) {
				$return_value[$i]['start'] = $interval['start'];
				$return_value[$i]['end']   = $interval['end'];
				$return_value[$i]['id']    = $event_id;
				$return_value[$i]['title'] = $title;
				$return_value[$i]['url']   = $url;
				$i++;
			}
		}
		return $return_value;
	}

	/**
	 * Merge event dates by datetime format
	 *
	 * @param $format string                                     - group accuracy (e.g. 'YmdHis' compatible with datetime formats)
	 * @param $main_event  array(
	 *                         '123' => array(                   - (int) post_parent sub event
	 *                             '333' => array(               - (int) any unique id
	 *                                 'start' => '1355270400'   - (int) unixtimestamp
	 *                                 'end'   => '1355270700'   - (int) unixtimestamp
	 *                              )
	 *                              ***
	 *                          )
	 *                          ***
	 *                      )
	 * @return array
	 */
	private function _fw_grouped_calendar_dates($main_event, $format = 'Ymd'){
		foreach($main_event as &$sub_events_array){
			//sort sub events date ranges by 'start' ascending
			uasort($sub_events_array, array($this, 'fw_compare_event_dates'));

			$i = 0;
			$remove_items_keys = array();
			foreach($sub_events_array as &$sub_event) {
				$i++;

				//get next sub event date ranges
				$events_sliced = array_slice($sub_events_array, $i, null, true);

				if (empty($events_sliced)) {
					continue;
				}

				//merge date ranges by date format
				foreach($events_sliced as $key_sliced => $sub_event_sliced){
					if (date($format, $sub_event_sliced['start']) <= date($format, $sub_event['end'])){
						if ( date($format, $sub_event_sliced['end']) >= date($format, $sub_event['end']) ) {
							$sub_event['end'] = $sub_event_sliced['end'];
						}
						//save elements keys which will be removed
						$remove_items_keys[] = $key_sliced;
					}
				}
			}

			//clean not actual sub event date ranges
			if (!empty($remove_items_keys)) {
				foreach($remove_items_keys as $key) {
					unset($sub_events_array[$key]);
				}
			}
		}

		return $main_event;
	}

	/**
	 * @internal
	 */
	public function fw_compare_event_dates($a, $b){
		if ($a['start'] == $b['start']) {
			return 0;
		} elseif ($a['start'] > $b['start']) {
			return 1;
		}

		return -1;
	}

}