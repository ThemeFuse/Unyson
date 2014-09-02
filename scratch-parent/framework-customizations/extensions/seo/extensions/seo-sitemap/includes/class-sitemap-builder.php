<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @internal
 */
class _FW_Ext_Seo_Sitemap_Builder {
	/**
	 * Contains the current state state, is active or not
	 * @var bool
	 */
	static private $active = false;

	private $custom_posts = array();
	private $taxonomies = array();
	private $file_name = 'sitemap.xml';
	private $views_path = __DIR__;
	private $url_settings = array(
		'home'  => array(
			'priority'  => 1,
			'frequency' => 'daily',
		),
		'posts' => array(
			'priority'  => 0.6,
			'frequency' => 'daily',
			'type'      => array(
				'attachment' => array(
					'priority'  => 0.3,
					'frequency' => 'daily',
				)
			)
		),
		'taxonomies'     => array(
			'priority'  => 0.4,
			'frequency' => 'weekly',
			'type'      => array(
				'post_tag'   => array(
					'priority'  => 0.3,
					'frequency' => 'weekly',
				)
			)
		)
	);
	private $xml_file = null;

	/**
	 * @param array $settings , array of the sitemap settings
	 */
	public function __construct( $settings = array() ) {
		//Check if the class wasn't initialized already
		if ( self::$active === true ) {
			return;
		}
		self::$active = true;

		$this->set_up_defaults( $settings );
	}

	private function set_up_defaults( $settings = array() ) {

		if ( isset( $settings['posts'] ) ) {
			$this->custom_posts = $settings['posts'];
		}

		if ( isset( $settings['taxonomies'] ) ) {
			$this->taxonomies = $settings['taxonomies'];
		}

		if ( isset( $settings['file-name'] ) ) {
			$this->file_name = $settings['file-name'];
		}

		if ( isset( $settings['views-path'] ) ) {
			$this->views_path = $settings['views-path'];
		}

		if ( isset( $settings['url_settings'] ) ) {
			$url_settings = $settings['url_settings'];

			if ( isset( $url_settings['home'] ) ) {
				if ( isset( $url_settings['home']['priority'] ) ) {
					$this->url_settings['home']['priority'] = $url_settings['home']['priority'];
				}

				if ( isset( $url_settings['home']['frequency'] ) ) {
					$this->url_settings['home']['frequency'] = $url_settings['home']['frequency'];
				}
			}

			if ( isset( $url_settings['posts'] ) ) {
				if ( isset( $url_settings['posts']['priority'] ) ) {
					$this->url_settings['posts']['priority'] = $url_settings['posts']['priority'];
				}

				if ( isset( $url_settings['posts']['frequency'] ) ) {
					$this->url_settings['posts']['frequency'] = $url_settings['posts']['frequency'];
				}

				if ( isset( $url_settings['posts']['type'] ) ) {
					$types = $url_settings['posts']['type'];
					$this->url_settings['posts']['type']
						   = array_merge( $this->url_settings['posts']['type'], $types );
				}
			}

			if ( isset( $url_settings['taxonomies'] ) ) {
				if ( isset( $url_settings['taxonomies']['priority'] ) ) {
					$this->url_settings['taxonomies']['priority'] = $url_settings['taxonomies']['priority'];
				}

				if ( isset( $url_settings['taxonomies']['frequency'] ) ) {
					$this->url_settings['taxonomies']['frequency'] = $url_settings['taxonomies']['frequency'];
				}

				if ( isset( $url_settings['taxonomies']['type'] ) ) {
					$types = $url_settings['taxonomies']['type'];
					$this->url_settings['taxonomies']['type']
						   = array_merge( $this->url_settings['taxonomies']['type'], $types );
				}
			}
		}
	}

	/**
	 * Public function to create the sitemap
	 */
	public function create_sitemap() {
		$this->sitemap_build_prepare();
	}

	/**
	 * Preparing XML file to be written
	 * @return bool
	 */
	private function sitemap_build_prepare() {
		$file_path = $this->get_path();

		if ( ! $this->check_path() ) {
			return false;
		}
		$this->xml_file = fopen( $file_path, 'w' );
		$this->build_sitemap();

		return true;
	}

	/**
	 * Get the sitemap XML file path
	 * @return string
	 */
	private function get_path() {
		return fw_ext_seo_sitemap_get_home_path() . $this->file_name;
	}

	private function check_path() {
		if ( ! fw_ext_seo_sitemap_try_make_file_writable( $this->get_path() ) ) {
			if ( is_admin() ) {
				FW_Flash_Messages::add( 'fw-ext-seo-sitemap-try-edit-file', sprintf( __( 'Could not create/write the %s. File is not writable', 'fw' ), $this->get_path() ), 'warning' );
			}

			return false;
		}

		return true;
	}

	/**
	 * Main function that builds the sitemap.xml file
	 */
	private function build_sitemap() {
		$this->add_element( $this->get_header() );

		$this->add_home_page();
		$this->add_custom_posts();
		$this->add_taxonomies();

		$this->add_element( $this->get_footer() );
		fclose( $this->xml_file );
	}

	/**
	 * Adds new element in XML file
	 *
	 * @param string $element - element needs to be added
	 */
	private function add_element( $element = '' ) {
		fwrite( $this->xml_file, $element );
	}

	/**
	 * Returns the XML file header
	 * @return string
	 */
	private function get_header() {
		$data['content'] = apply_filters( 'set_sitemap_xml_header', '' );

		return fw_render_view( $this->views_path . '/sitemap-header.php', $data );
	}

	/**
	 * Adds all custom posts in the sitemap.xml
	 */
	private function add_home_page() {
		$items = array();

		$item['url']       = home_url( '/' );
		$item['priority']  = $this->get_homepage_url_details( 'priority' );
		$item['frequency'] = $this->get_homepage_url_details( 'frequency' );

		$id = get_option( 'page_on_front' );

		if ( intval( $id ) == 0 ) {
			global $wpdb;

			$post = $wpdb->get_row( 'SELECT post_modified_gmt modified FROM ' . $wpdb->posts . ' WHERE post_type = \'post\' ORDER BY post_modified_gmt DESC LIMIT 1', ARRAY_A );
			$date = $post['modified'];
		} else {
			$post = get_post( $id );
			$date = $post->post_modified_gmt;
		}

		$item['modified'] = date( apply_filters( 'fw_ext_seo_sitemap_date_format', 'Y-m-d' ), strtotime( $date ) );

		$items[] = $item;

		$this->add_element( fw_render_view( $this->views_path . 'sitemap.php', array( 'items' => $items ) ) );
	}

	/**
	 * Returns the homepage details: priority or change frequency
	 *
	 * @param string $need the index you need: priority or frequency
	 *
	 * @return array
	 */
	private function get_homepage_url_details( $need = 'priority' ) {
		$details = $this->url_settings['home'];

		return $details[ $need ];
	}

	/**
	 * Adds all custom posts in the sitemap.xml
	 */
	private function add_custom_posts() {
		global $wpdb;

		if( empty( $this->custom_posts ) ) {
			return;
		}

		foreach ( $this->custom_posts as $post_type ) {
			$items = $wpdb->get_results( $wpdb->prepare(
					"SELECT id, post_type, post_modified_gmt modified
					FROM $wpdb->posts
					WHERE post_type = '%s' AND post_status IN ('publish', 'inherit')",
					$post_type ),
				ARRAY_A );

			foreach ( $items as $key => $item ) {
				$items[ $key ]['url']       = get_permalink( $item['id'] );
				$items[ $key ]['modified']  = date( apply_filters( 'fw_ext_seo_sitemap_date_format', 'Y-m-d' ), strtotime( $items[ $key ]['modified'] ) );
				$items[ $key ]['priority']  = $this->get_post_type_url_details( $item['post_type'] );
				$items[ $key ]['frequency'] = $this->get_post_type_url_details( $item['post_type'], 'frequency' );

				unset( $items[ $key ]['id'] );
				unset( $items[ $key ]['post_type'] );
			}

			$this->add_element( fw_render_view( $this->views_path . 'sitemap.php', array( 'items' => $items ) ) );
		}
	}

	/**
	 * Adds all custom posts in the sitemap.xml
	 */
	private function add_taxonomies() {
		global $wpdb;

		if( empty( $this->taxonomies ) ) {
			return;
		}

		foreach ( $this->taxonomies as $taxonomy ) {
			$items = array();
			$terms = get_terms( $taxonomy, array( 'hide_empty' => true, 'hierarchical' => false ) );

			foreach ( $terms as $term ) {
				$item              = array();
				$sql               = $wpdb->prepare( "SELECT MAX(p.post_modified_gmt) AS modified
					FROM	$wpdb->posts AS p
					INNER JOIN $wpdb->term_relationships AS term_rel
					ON		term_rel.object_id = p.ID
					INNER JOIN $wpdb->term_taxonomy AS term_tax
					ON		term_tax.term_taxonomy_id = term_rel.term_taxonomy_id
					AND		term_tax.taxonomy = '%s'
					AND		term_tax.term_id = %d
					WHERE	p.post_status IN ('publish','inherit')", $taxonomy, $term->term_id );
				$item['modified']  = date( apply_filters( 'fw_ext_seo_sitemap_date_format', 'Y-m-d' ), strtotime( $wpdb->get_var( $sql ) ) );
				$item['url']       = get_term_link( $term, $taxonomy );
				$item['priority']  = $this->get_taxonomy_url_details( $taxonomy );
				$item['frequency'] = $this->get_taxonomy_url_details( $taxonomy, 'frequency' );

				unset( $item['id'] );

				$items[] = $item;
			}
			$this->add_element( fw_render_view( $this->views_path . 'sitemap.php', array( 'items' => $items ) ) );
		}

	}

	/**
	 * Returns the posts details: priority or change frequency
	 *
	 * @param string $post_type , the custom post type name
	 * @param string $need the index you need: priority or frequency
	 *
	 * @return array
	 */
	private function get_post_type_url_details( $post_type, $need = 'priority' ) {
		$details = $this->url_settings['posts'];
		if ( in_array( $post_type, $details['type'] ) ) {
			return $details['type'][ $need ];
		}

		return $details[ $need ];
	}

	/**
	 * Returns the taxonomy details: priority or change frequency
	 *
	 * @param string $taxonomy , the taxonomy name
	 * @param string $need the index you need: priority or frequency
	 *
	 * @return array
	 */
	private function get_taxonomy_url_details( $taxonomy, $need = 'priority' ) {
		$details = $this->url_settings['taxonomies'];
		if ( in_array( $taxonomy, $details['type'] ) ) {
			return $details['type'][ $need ];
		}

		return $details[ $need ];
	}

	/**
	 * Returns the XML file footer
	 * @return string
	 */
	private function get_footer() {
		return '</urlset>';
	}

	/**
	 * Public function to update the sitemap
	 * @return bool
	 */
	public function update_sitemap() {
		return $this->sitemap_build_prepare();
	}

	public function delete_xml_sitemap() {
		return $this->delete_sitemap();
	}

	/**
	 * Deletes the XML sitemap
	 * TRUE if the sitemap was deleted
	 * @return bool
	 */
	private function delete_sitemap() {
		if ( ! file_exists( $this->get_path() ) ) {
			return true;
		}

		if ( ! $this->check_path() ) {
			return false;
		}

		return ( unlink( $this->get_path() ) );
	}

	/**
	 * Returns the sitemap file uri
	 * @return string
	 */
	public function get_sitemap_uri() {
		return home_url( '/' ) . $this->file_name;
	}
}