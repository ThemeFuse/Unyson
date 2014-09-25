<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * SEO Sitemap extension
 * Is sa a sub-extension of the SEO extension.
 */
class FW_Extension_Seo_Sitemap extends FW_Extension {

	/**
	 * Sets value tu true and all the sitemap parameters was set
	 *
	 * @var bool
	 */
	private static $set_parameters = false;

	/**
	 * Contains an array of all available search engines
	 * @var array
	 */
	private $serach_engies = array();

	/**
	 * Contains the list of the allowed custom post types
	 * @var array
	 */
	private $allowed_post_types = array();

	private $settings_options = null;

	/**
	 * Contains the list of the allowed taxonomies
	 * @var array
	 */
	private $allowed_taxonomies = array();

	/**
	 * Contains the sitemap object
	 * @var null | _FW_Ext_Seo_Sitemap_Builder
	 */
	private $sitemap = null;

	/**
	 * @internal
	 */
	public function _init() {
		///TODO: attach to the disable extension action to delete the sitemap
		$this->add_actions();
		$this->add_filters();
		if ( is_admin() ) {
			$this->add_admin_actions();
			$this->add_admin_filters();
		}
	}

	/**
	 * Init the necessary action hooks for the extension functionality
	 */
	private function add_actions() {
		add_action( 'init', array( $this, '_action_init' ), 9999 );

		add_action( 'fw_deleted_sitemap', array( $this, '_action_delete_xsl_file' ), 9999 );

		add_action( 'fw_sitemap_updated', array( $this, '_action_ping_to_search_engines' ), 9999 );
		add_action( 'fw_sitemap_updated', array( $this, '_action_create_xsl_file' ), 9999 );
	}

	/**
	 * Init the necessary filter hooks for the extension functionality
	 */
	private function add_filters() {
		add_filter( 'set_sitemap_xml_header', array( $this, '_filter_xsl_header' ) );
	}

	private function add_admin_actions() {
		add_action( 'wp_ajax_fw_update_sitemap', array( $this, '_action_ajax_update_sitemap' ), 9999 );
		add_action( 'wp_ajax_fw_delete_sitemap', array( $this, '_action_ajax_delete_sitemap' ), 9999 );

		add_action( 'admin_enqueue_scripts', array( $this, '_admin_action_css' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_action_js' ) );
	}

	private function add_admin_filters() {
		add_filter( 'fw_ext_seo_admin_options', array( $this, '_filter_set_framework_sitemap_tab' ) );
	}

	/**
	 * Returns an array with allowed custom post types for this extension
	 * @return array
	 */
	public function get_allowed_post_types() {
		return $this->allowed_post_types;
	}

	/**
	 * Retuns an array with allows taxonomies for this extension
	 * @return array
	 */
	public function get_allowed_taxonomies() {
		return $this->allowed_taxonomies;
	}

	public function get_search_engines() {
		return $this->serach_engies;
	}

	/**
	 * Necessary to build the extension after wordpress init action
	 * @internal
	 */
	public function _action_init() {
		$this->check_for_sitemap_auto_build();
	}

	/**
	 * Check when the sitemap was updated last time and if the time is logner then the sitemap refresh rate time,
	 * updates it
	 */
	private function check_for_sitemap_auto_build() {
		$last_modif   = fw_get_db_extension_data( $this->get_name(), 'last_modif' );
		$refresh_rate = $this->get_config( 'sitemap_refresh_rate' );
		if ( empty( $refresh_rate ) ) {
			$refresh_rate = 2;
		}

		if ( ! isset( $last_modif['last_sitemap_update'] ) || empty( $last_modif['last_sitemap_update'] ) ) {
			$this->set_parameters();
			$this->update_sitemap();

			return;
		}

		$prev_date    = strtotime( date( 'Y-m-d', $last_modif['last_sitemap_update'] ) );
		$current_date = strtotime( date( 'Y-m-d' ) );

		$interval = intval( ( $current_date - $prev_date ) / 86400 );

		if ( $interval >= $refresh_rate ) {
			$this->update_sitemap();
		}
	}

	private function set_parameters() {
		if ( self::$set_parameters ) {
			return;
		}

		$this->define_search_engines();
		$this->set_custom_posts();
		$this->set_taxonomies();
		$this->create_sitemap_object();

		self::$set_parameters = true;
	}

	private function get_admin_options() {
		if ( is_null( $this->settings_options ) ) {
			$this->settings_options = fw_get_db_extension_data( $this->get_parent()->get_name(), 'options' );
		}

		return $this->settings_options;
	}

	private function define_search_engines() {
		$this->serach_engies = array(
			'google' => array(
				'name' => __( 'Google', 'fw' ),
				'url'  => 'http://www.google.com/webmasters/tools/ping?sitemap='
			),
			'bing'   => array(
				'name' => __( 'Bing', 'fw' ),
				'url'  => 'http://www.bing.com/webmaster/ping.aspx?sitemap='
			)
		);
	}

	/**
	 * Defines the allowed custom post types for this extension
	 */
	private function set_custom_posts() {
		$custom_posts   = get_post_types( array( 'public' => true ) );
		$excluded_types = $this->get_config( 'excluded_post_types' );

		unset( $custom_posts['nav_menu_item'] );
		unset( $custom_posts['revision'] );

		foreach ( $excluded_types as $type ) {
			if ( isset( $custom_posts[ $type ] ) ) {
				unset( $custom_posts[ $type ] );
			}
		}
		$this->allowed_post_types = $custom_posts;
	}

	/**
	 * Defines the allowed taxonomies for this extension
	 */
	private function set_taxonomies() {
		$taxonomies = get_taxonomies();

		$excluded_types = $this->get_config( 'excluded_taxonomies' );

		unset( $taxonomies['nav_menu'] );
		unset( $taxonomies['link_category'] );
		unset( $taxonomies['post_format'] );

		foreach ( $excluded_types as $type ) {
			if ( isset( $taxonomies[ $type ] ) ) {
				unset( $taxonomies[ $type ] );
			}
		}

		$this->allowed_taxonomies = $taxonomies;
	}

	private function create_sitemap_object() {
		$settings = array(
			'posts'        => $this->get_workable_custom_post_types(),
			'taxonomies'   => $this->get_workable_taxonomies(),
			'views-path'   => $this->get_declared_path() . '/views/',
			'url_settings' => $this->get_config( 'url_settings' )
		);

		$this->sitemap = new _FW_Ext_Seo_Sitemap_Builder( $settings );
	}

	/**
	 * Returns an array with allowed custom post types for this extension, this doesn't include post types that was disabled from admin area
	 * @return array
	 */
	public function get_workable_custom_post_types() {
		$custom_post_types = array();
		$custom_posts      = $this->allowed_post_types;

		foreach ( $custom_posts as $custom_post ) {
			$allowed = $this->get_admin_options();
			$id      = $this->get_name() . '-exclude-custom-post-' . $custom_post;
			if ( isset( $allowed[ $id ] ) && $allowed[ $id ] === true ) {
				continue;
			}

			array_push( $custom_post_types, $custom_post );
		}

		return $custom_post_types;
	}

	/**
	 * Returns an array with allowed taxonomies for this extension, this doesn't include post types that was disabled from admin area
	 * @return array
	 */
	public function get_workable_taxonomies() {
		$taxonomies_types = array();
		$taxonomies       = $this->allowed_taxonomies;

		foreach ( $taxonomies as $taxonomy ) {
			$allowed = $this->get_admin_options();
			$id      = $this->get_name() . '-exclude-taxonomy-' . $taxonomy;

			if ( isset( $allowed[ $id ] ) && $allowed[ $id ] === true ) {
				continue;
			}

			array_push( $taxonomies_types, $taxonomy );
		}

		return $taxonomies_types;
	}

	/**
	 * Update sitemap.xml file
	 */
	public function update_sitemap() {
		if ( empty( $this->sitemap ) ) {
			return false;
		}
		do_action( 'fw_ext_seo_sitemap_pre_update' );

		$response = $this->sitemap->update_sitemap();
		$options  = array(
			'last_sitemap_update' => time()
		);
		fw_set_db_extension_data( $this->get_name(), 'last_modif', $options );
		do_action( 'fw_ext_seo_sitemap_updated' );

		return $response;
	}

	/**
	 * Load scripts for the admin area
	 * @internal
	 */
	public function _admin_action_js() {
		wp_enqueue_script(
			'fw-ext-'. $this->get_name() . '-admin-scripts',
			$this->get_declared_URI( '/static/js/admin-scripts.js' ),
			array( 'jquery' ),
			fw()->theme->manifest->get_version(),
			true
		);
	}

	/**
	 * Load styles for the admin area
	 * @internal
	 */
	public function _admin_action_css() {
		wp_enqueue_style(
			'fw-ext-'. $this->get_name() . '-admin-style',
			$this->get_declared_URI( '/static/css/admin-style.css' ),
			array(),
			fw()->theme->manifest->get_version()
		);
	}

	/**
	 * Triggers the sitemap update method, made for ajax calls
	 * @internal
	 */
	public function _action_ajax_update_sitemap() {
		if (!current_user_can('edit_files')) {
			wp_send_json_error();
		}

		$this->set_parameters();
		die( $this->update_sitemap() );
	}

	/**
	 * Triggers the sitemap delete method
	 * @internal
	 */
	public function _action_delete_sitemap() {
		$this->delete_sitemap();
	}

	/**
	 * Deletes the xml sitemap
	 * Returns true if the sitemap was deleted successfully
	 * @return bool
	 */
	public function delete_sitemap() {
		do_action( 'fw_ext_seo_sitemap_pre_delete' );
		$response = $this->sitemap->delete_xml_sitemap();

		if ( $response ) {
			do_action( 'fw_ext_seo_sitemap_deleted' );
		}

		return $response;
	}

	/**
	 * Triggers the sitemap delete method, made for ajax calls
	 * @internal
	 */
	public function _action_ajax_delete_sitemap() {
		if (!current_user_can('edit_files')) {
			wp_send_json_error();
		}

		$this->set_parameters();
		die( $this->delete_sitemap() );
	}

	/**
	 * Pings to the search engines the presence of the sitemap
	 * @internal
	 */
	public function _action_ping_to_search_engines() {
		$search_engines = $this->get_config( 'search_engines' );

		if ( empty( $search_engines ) ) {
			return;
		}

		foreach ( $search_engines as $search_engine ) {
			if ( ! isset( $this->serach_engies[ $search_engine ] ) ) {
				continue;
			}

			wp_remote_post( $this->serach_engies[ $search_engine ]['url'] . $this->get_sitemap_uri() );
		}
	}

	/**
	 * Get sitemap.xml file URI
	 * @return string
	 */
	public function get_sitemap_uri() {
		if ( empty( $this->sitemap ) ) {
			return '';
		}

		return $this->sitemap->get_sitemap_uri();
	}

	/**
	 * Deletes the XSL file
	 * @internal
	 */
	public function _action_delete_xsl_file() {
		$this->delete_xsl();
	}

	/**
	 * Deletes the sitemap XSL style file
	 * @return bool
	 */
	private function delete_xsl() {
		$file_path = fw_ext_seo_sitemap_get_home_path() . 'sitemap-xsl.xsl';
		if ( ! file_exists( $file_path ) ) {
			return true;
		}

		if ( ! fw_ext_seo_sitemap_try_make_file_writable( $file_path ) ) {
			if ( is_admin() ) {
				FW_Flash_Messages::add( 'fw-ext-seo-sitemap-delete-file', sprintf( __( 'Could not delete the %s. File is not writable', 'fw' ), $file_path ), 'warning' );
			}

			return false;
		}

		return ( unlink( $file_path ) );
	}

	/**
	 * Creates the XSL file
	 * @internal
	 */
	public function _action_create_xsl_file() {
		$this->create_xsl();
	}

	/**
	 * Create the simep XSL style file
	 */
	private function create_xsl() {
		$file_path = fw_ext_seo_sitemap_get_home_path() . 'sitemap-xsl.xsl';

		if ( ! fw_ext_seo_sitemap_try_make_file_writable( $file_path ) ) {
			if ( is_admin() ) {
				FW_Flash_Messages::add( 'fw-ext-seo-sitemap-try-modify-file', sprintf( __( 'Could not create/write the %s. File is not writable', 'fw' ), $file_path ), 'warning' );
			}

			return;
		}

		$file = fopen( $file_path, 'w' );
		fwrite( $file, fw_render_view( $this->locate_view_path( 'sitemap-style' ) ) );
	}

	/**
	 * Adds the extension settings tab in Framework in SEO extension
	 *
	 * @param $seo_options , holds the general options from extension config file
	 *
	 * @return array
	 * @internal
	 */
	public function _filter_set_framework_sitemap_tab( $seo_options ) {
		$this->set_parameters();
		$sitemap_options = fw_ext_seo_sitemap_get_settings_options();
		if ( is_array( $sitemap_options ) && ! empty( $sitemap_options ) ) {
			return array_merge( $seo_options, $sitemap_options );
		}

		return $seo_options;
	}

	/**
	 * Adds the in the sitemap XML header the link to the XSL styling file
	 * @return string
	 * @internal
	 */
	public function _filter_xsl_header() {
		return '<?xml-stylesheet type="text/xsl" href="' . home_url( '/' ) . 'sitemap-xsl.xsl"?>';
	}
}