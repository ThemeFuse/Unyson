<?php if (!defined('FW')) die('Forbidden');


class FW_Extension_Events extends FW_Extension {

	private $post_type_name = 'fw-event';
	private $taxonomy_name  = 'fw-event-taxonomy-name';

	private $post_type_slug = 'fw-event-slug';
	private $taxonomy_slug  = 'fw-event-taxonomy-slug';

	/**
	 * @var string main option key
	 */
	private $event_option_id = 'general-event';

	/**
	 * @internal
	 */
	protected function _init()
	{
		$this->_fw_define_slug();

		$this->_fw_register_post_type();
		$this->_fw_register_taxonomy();

		if (is_admin()) {
			$this->_fw_add_admin_filters();
			$this->_fw_add_admin_actions();
		} else {
			$this->_fw_theme_add_filters();
			$this->_fw_theme_add_actions();
		}
	}

	private function _fw_theme_add_actions(){
		add_action('wp', array($this,'_action_theme_calendar_export'));
	}

	public function _action_theme_calendar_export(){
		global $post;
		if (empty($post) or $post->post_type !== $this->post_type_name) {
			return;
		}

		if(FW_Request::GET('calendar')){
			$calendar = FW_Request::GET('calendar');
			$row_id   = FW_Request::GET('row_id');
			$offset   = FW_Request::GET('offset');
			$options  = fw_get_db_post_option($post->ID, $this->get_event_option_id());

			if (!is_array(fw_akg('event_children/' . $row_id, $options)) or !preg_match('/^\d+$/', $row_id) ) {
				wp_redirect(site_url() . '?error=404');
			}

			if (!preg_match('/^(\-|\d)\d+$/', $offset)) {
				$offset = 0;
			}

			switch ($calendar){
				case 'google':
					wp_redirect($this->_fw_ext_events_get_google_uri($post, $options, $row_id, $offset));
					break;
				default:
					$this->_fw_ext_events_set_ics_headers($post);
					echo $this->_fw_ext_events_set_ics_content($post, $options, $row_id, $offset);
					die();
			}
		}

	}

	private function _fw_ext_events_get_google_uri($post, $options, $row_id, $offset) {
		$all_day  = fw_akg('all_day', $options, 'yes');

		$date_template = 'Ymd';
		if ($all_day === 'no') {
			$date_template = 'Ymd\THis\Z';
		}

		$start    = date($date_template, $offset + strtotime(fw_akg('event_children/' . $row_id . '/event_date_range/from', $options, 'now')));
		$end      = date($date_template, $offset + strtotime(fw_akg('event_children/' . $row_id . '/event_date_range/to', $options, 'now')));
		$location = fw_akg('event_location/location', $options, '');

		return  'https://www.google.com/calendar/render?action=TEMPLATE&text=' . $post->post_title .
				'&dates=' . $start . '/' . $end .
				'&details=For+details,+link+here:+' . get_permalink($post->ID) .
				'&location=' . $location;
	}


	private function _fw_ext_events_set_ics_content($post, $options, $row_id, $offset ) {
		$all_day  = fw_akg('all_day', $options, 'yes');

		$date_template = 'Ymd\T000000';
		if ($all_day === 'no') {
			$date_template = 'Ymd\THis\Z';
		}

		$start    = date($date_template, $offset + strtotime(fw_akg('event_children/' . $row_id . '/event_date_range/from', $options, 'now')));
		$end      = date($date_template, $offset + strtotime(fw_akg('event_children/' . $row_id . '/event_date_range/to', $options, 'now')));
		$location = fw_akg('event_location/location', $options, '');
		return  "BEGIN:VCALENDAR\n" .
				"VERSION:1.0\n" .
				"BEGIN:VEVENT\n" .
				"URL:" . get_permalink($post->ID) . "\n" .
				"DTSTART:" . $start . "\n" .
				"DTEND:" . $end . "\n" .
				"SUMMARY:" . $post->post_title  . "\n" .
				"DESCRIPTION:For details, click here:" . get_permalink($post->ID)  . "\n" .
				"LOCATION:" . $location  . "\n" .
				"END:VEVENT\n" .
				"END:VCALENDAR";
	}


	private function _fw_ext_events_set_ics_headers($post){
		header('Content-type: text/calendar');
		header('Content-Disposition: attachment; filename=' . urlencode($post->post_title) . '-' . time() . '.ics');
		header('Pragma: no-cache');
		header('Expires: 0');
	}



	private function _fw_add_admin_filters(){
		add_filter('manage_' . $this->get_post_type_name() . '_posts_columns', array($this, '_filter_admin_add_columns'), 10, 1);
		add_filter( 'fw_post_options', array( $this, '_filter_admin_set_custom_posts_events_options' ),10 ,2);
		add_action('admin_head', array($this, '_filter_admin_remove_date_dropdown'));
	}

	/**
	 * Remove post creation date filter dropdown on the event list
	 */
	public function _filter_admin_remove_date_dropdown() {
		$current_screen = array(
			'only'  => array(
				array( 'post_type'   => $this->post_type_name )
			)
		);

		if (fw_current_screen_match($current_screen)) {
			add_filter('months_dropdown_results', '__return_empty_array');
		}
	}

	/**
	 * Enquee backend styles on events pages
	 * @param $hook
	 */
	public function _action_admin_enqueue_scripts($hook){
		$current_screen = array(
			'only'  => array(
				array( 'post_type'   => $this->post_type_name )
			)
		);

		if (fw_current_screen_match($current_screen)) {
			wp_enqueue_style('fw-ext-events-css',
				$this->get_declared_URI('/static/css/backend-events-style.css'),
				array(),
				fw()->manifest->get_version()
			);
		}

	}

	private function _fw_theme_add_filters(){
		add_filter( 'template_include', array( $this, '_filter_theme_template_include' ) );
	}

	public function get_event_option_id() {
		return $this->event_option_id;
	}

	/**
	 * Select custom page template on frontend
	 *
	 * @internal
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function _filter_theme_template_include( $template ) {

		if ( is_singular( $this->post_type_name ) ) {
			return $this->locate_path( '/views/single.php' );
		} else if ( is_tax( $this->taxonomy_name ) ) {
			return $this->locate_path( '/views/taxonomy.php' );
		}

		return $template;
	}

	public function fw_get_post_type_slug(){
		return $this->post_type_slug;
	}

	private function _fw_define_slug() {
		$this->post_type_slug          = apply_filters( 'fw_ext_' . $this->get_name() . '_post_slug', $this->post_type_slug );
		$this->taxonomy_slug           = apply_filters( 'fw_ext_' . $this->get_name() . '_taxonomy_slug', $this->taxonomy_slug );
	}

	private function _fw_add_admin_actions() {
		add_action('manage_' . $this->get_post_type_name() . '_posts_custom_column', array($this, '_action_admin_manage_custom_column'), 10, 2);
		add_action( 'admin_enqueue_scripts',                array($this, '_action_admin_enqueue_scripts'));

		add_action( 'admin_head', array( $this, '_action_admin_initial_nav_menu_meta_boxes' ), 999 );
	}

	/**
	 * Adding custom columns in events list page (All Events)
	 * @param $columns
	 * @return array
	 */
	public function _filter_admin_add_columns($columns) {
		return array(
			'cb' => $columns['cb'],
			'title' => $columns['title'],
			'event_date' => __('Date','fw'),
			'event_location' => __('Location', 'fw'),
		);
	}

	public function get_post_type_name(){
		return $this->post_type_name;
	}

	public function get_taxonomy_name(){
		return $this->taxonomy_name;
	}

	/**
	 * Add event option type in edit Event page
	 * @param $post_options
	 * @param $post_type
	 * @return mixed
	 */
	public function _filter_admin_set_custom_posts_events_options( $post_options, $post_type ) {
		if( $post_type != $this->post_type_name ) {
			return $post_options;
		}

		$post_options[ 'main' ] = array(
			'title' => false,
			'desc'  => false,
			'type'  => 'box',
			'options' => array(
				'events_tab' => array(
					'title' => __('Event Options', 'fw'),
					'type'  => 'tab',
					'options' => array(
						$this->event_option_id => array(
							'type' => 'event',
							'desc' => false,
							'label' => false,
						)
					)
				),
			)
		);

		return $post_options;
	}

	/**
	 * @internal
	 */
	private function _fw_register_post_type() {

		$post_names = apply_filters( 'fw_ext_' . $this->get_name() . '_post_type_name', array(
			'singular' => __( 'Event', 'fw' ),
			'plural'   => __( 'Events', 'fw' )
		) );

		register_post_type( $this->post_type_name, array(
			'labels'                => array(
				'name'               => __( 'Events', 'fw' ),
				'singular_name'      => __( 'Event', 'fw' ),
				'add_new'            => __( 'Add New', 'fw' ),
				'add_new_item'       => sprintf( __( 'Add New %s', 'fw' ), $post_names['singular'] ),
				'edit'               => __( 'Edit', 'fw' ),
				'edit_item'          => sprintf( __( 'Edit %s', 'fw' ), $post_names['singular'] ),
				'new_item'           => sprintf( __( 'New %s', 'fw' ), $post_names['singular'] ),
				'all_items'          => sprintf( __( 'All %s', 'fw' ), $post_names['plural'] ),
				'view'               => sprintf( __( 'View %s', 'fw' ), $post_names['singular'] ),
				'view_item'          => sprintf( __( 'View %s', 'fw' ), $post_names['singular'] ),
				'search_items'       => sprintf( __( 'Search %s', 'fw' ), $post_names['plural'] ),
				'not_found'          => sprintf( __( 'No %s Found', 'fw' ), $post_names['plural'] ),
				'not_found_in_trash' => sprintf( __( 'No %s Found In Trash', 'fw' ), $post_names['plural'] ),
				'parent_item_colon'  => '' /* text for parent types */
			),
			'description'           => __( 'Create a event item', 'fw' ),
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'publicly_queryable'    => true,
			/* queries can be performed on the front end */
			'has_archive'           => true,
			'rewrite'               => array(
				'slug' => $this->post_type_slug
			),
			'menu_position'         => 5,
			'show_in_nav_menus'     => false,
			'menu_icon'             => 'dashicons-calendar',
			'hierarchical'          => false,
			'query_var'             => true,
			/* Sets the query_var key for this post type. Default: true - set to $post_type */
			'supports'              => array(
				'title', /* Text input field to create a post title. */
				'editor',
				'thumbnail', /* Displays a box for featured image. */
			)
		) );

	}

	/**
	 * @internal
	 */
	private function _fw_register_taxonomy() {

		$category_names = apply_filters( 'fw_ext_' . $this->get_name() . '_category_name', array(
			'singular' => __( 'Category', 'fw' ),
			'plural'   => __( 'Categories', 'fw' )
		) );

		$labels = array(
			'name'              => sprintf( _x( 'Event %s', 'taxonomy general name', 'fw' ), $category_names['plural'] ),
			'singular_name'     => sprintf( _x( 'Event %s', 'taxonomy singular name', 'fw' ), $category_names['singular'] ),
			'search_items'      => sprintf( __( 'Search %s', 'fw' ), $category_names['plural'] ),
			'all_items'         => sprintf( __( 'All %s', 'fw' ), $category_names['plural'] ),
			'parent_item'       => sprintf( __( 'Parent %s', 'fw' ), $category_names['singular'] ),
			'parent_item_colon' => sprintf( __( 'Parent %s:', 'fw' ), $category_names['singular'] ),
			'edit_item'         => sprintf( __( 'Edit %s', 'fw' ), $category_names['singular'] ),
			'update_item'       => sprintf( __( 'Update %s', 'fw' ), $category_names['singular'] ),
			'add_new_item'      => sprintf( __( 'Add New %s', 'fw' ), $category_names['singular'] ),
			'new_item_name'     => sprintf( __( 'New %s Name', 'fw' ), $category_names['singular'] ),
			'menu_name'         => sprintf( __( '%s', 'fw' ), $category_names['plural'] )
		);

		$args   = array(
			'labels'            => $labels,
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'rewrite'           => array(
				'slug' => $this->taxonomy_slug
			),
		);

		register_taxonomy( $this->taxonomy_name, esc_attr( $this->post_type_name ), $args );
	}

	/**
	 * Fill custom column
	 * @internal
	 */
	public function _action_admin_manage_custom_column($column, $post_id)
	{
		switch ($column) {
			case 'event_location' :
				echo $this->_fw_get_event_location($post_id);
				break;
			case 'event_date' :
				echo $this->_fw_get_event_datetime_date($post_id);
				break;
			default :
				break;
		}
	}

	/**
	 * A way to find out event start date
	 * @param $post_id int
	 * @return string
	 */
	private function _fw_get_event_datetime_date($post_id) {
		$meta = fw_get_db_post_option($post_id, $this->event_option_id);
		$empty_msg = '&#8212;';

		$result = $empty_msg;
		if (isset($meta['event_children']) && is_array($meta['event_children'])) {
			$meta_rows = fw_akg('event_children', $meta);
			if (is_array($meta_rows) && count($meta_rows) > 0) {
				$min_date = null;
				$cnt = 0;
				//search event's minimal from_date (also check if exists)
				foreach($meta_rows as $meta_row) {
					$from_date = fw_akg('event_date_range/from', $meta_row);

					if (empty($from_date)) {
						continue;
					}

					try {
						$from_date = new DateTime($from_date, new DateTimeZone('GMT'));
						if ($min_date === null or $from_date->format('U') < $min_date->format('U')) {
							$min_date = $from_date;
						}
						$cnt++;
					} catch(Exception $e) {
						//if was saved wrong format
					}

				}

				if ($cnt > 1 ) {
					$result = __('Multi Interval Event', 'fw');
				} else {
					$result = empty($min_date) ? $empty_msg : $min_date->format('Y/m/d');
				}
			}
		}

		return $result;
	}

	/**
	 * Get saved event location array from db
	 * @param $post_id
	 * @return string
	 */
	private function _fw_get_event_location($post_id){
		$meta = fw_get_db_post_option($post_id, $this->event_option_id);
		return ((isset($meta['event_location']['location']) and false === empty($meta['event_location']['location'])) ? $meta['event_location']['location'] : '&#8212;');
	}

	/**
	 * @internal
	 */
	public function _action_admin_initial_nav_menu_meta_boxes() {
		$screen = array(
			'only' => array(
				'base' => 'nav-menus'
			)
		);

		if ( ! fw_current_screen_match( $screen ) ) {
			return;
		}

		$user_ID = get_current_user_id();
		$meta    = fw_get_db_extension_user_data( $user_ID, $this->get_name() );

		if ( isset( $meta['metaboxhidden_nav-menus'] ) && $meta['metaboxhidden_nav-menus'] == true ) {
			return;
		}

		$hidden_meta_boxes = get_user_meta( $user_ID, 'metaboxhidden_nav-menus' );
		if ( $key = array_search( 'add-' . $this->taxonomy_name, $hidden_meta_boxes[0] ) ) {
			unset( $hidden_meta_boxes[0][ $key ] );
		}

		update_user_option( $user_ID, 'metaboxhidden_nav-menus', $hidden_meta_boxes[0], true );

		if ( ! is_array( $meta ) ) {
			$meta = array();
		}

		if ( ! isset( $meta['metaboxhidden_nav-menus'] ) ) {
			$meta['metaboxhidden_nav-menus'] = true;
		}

		fw_set_db_extension_user_data( $user_ID, $this->get_name(), $meta );
	}
}
