<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Multi_Select extends FW_Option_Type {
	private $internal_options = array();

	public function get_type() {
		return 'multi-select';
	}

	/**
	 * @internal
	 */
	public function _init() {
		$this->internal_options = array(
			'label' => false,
			'type'  => 'text',
			'value' => '',
		);
	}

	/**
	 * @internal
	 */
	public  static function _admin_action_get_ajax_response() {
		global $wpdb;

		$type  = $_POST['data']['type'];
		$names = stripslashes( $_POST['data']['names'] );
		$title = $_POST['data']['string'];

		$items = array();

		if ( $type == 'posts' ) {
			$items = $wpdb->get_results(
				"SELECT ID val, post_title title " .
				"FROM $wpdb->posts " .
				"WHERE post_title LIKE '%$title%' " .
				"AND post_status = 'publish' " .
				"AND NULLIF(post_password, '') IS NULL " .
				"AND post_type IN ( '$names' ) LIMIT 100"
			);
		}

		if ( $type == 'taxonomy' ) {
			$items = $wpdb->get_results(
				"SELECT terms.term_id val, terms.name title " .
				"FROM $wpdb->terms as terms, $wpdb->term_taxonomy as taxonomies " .
				"WHERE terms.name LIKE '%$title%' AND taxonomies.taxonomy IN ( '$names' )' " .
				"AND terms.term_id = taxonomies.term_id " .
				"AND taxonomies.term_id = taxonomies.term_taxonomy_id LIMIT 100"
			);
		}
		if ( $type == 'users' ) {
			if ( empty( $names ) ) {
				$items = $wpdb->get_results(
					"SELECT users.id val, users.display_name title " .
					"FROM $wpdb->users as users " .
					"WHERE users.display_name LIKE '%$title%' " .
					"LIMIT 100"
				);
			} else {
				$names = explode( ",", $names );
				$names = implode( "%' OR usermeta.meta_value LIKE '%", $names );
				$items = $wpdb->get_results(
					"SELECT users.id val, users.display_name title " .
					"FROM $wpdb->users as users, $wpdb->usermeta usermeta " .
					"WHERE users.display_name LIKE '%$title%' AND ( usermeta.meta_key = 'wp_capabilities' AND usermeta.meta_value LIKE '%$names%' ) " .
					"AND usermeta.user_id = users.ID"
				);
			}
		}

		wp_send_json_success( $items );
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type() {
		return 'fixed';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value'      => array(),
			'population' => 'array',
			'source'     => '',
			'limit'      => 100
		);
	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$this->add_css();
		$this->add_js();

		$items      = '';
		$population = 'array';
		$source     = '';

		if ( isset( $option['population'] ) ) {
			switch ( $option['population'] ) {
				case 'array' :
					if ( isset( $option['choices'] ) && is_array( $option['choices'] ) ) {
						$items = $option['choices'];
					}
					break;
				case 'posts' :
					if ( isset( $option['source'] ) ) {
						global $wpdb;
						$source     = ( is_array( $option['source'] ) ) ? implode( "', '", $option['source'] ) : $option['source'];
						$population = 'posts';

						if ( empty( $data['value'] ) ) {
							break;
						}

						$ids = $data['value'];

						foreach ( $ids as $key => $post_id ) {
							$ids[ $key ] = intval( $post_id );
						}

						$ids = implode( ', ', $ids );

						//$query = new WP_Query( array( 'post__in' => $ids ) );

						$query = $wpdb->get_results(
							"SELECT posts.ID, posts.post_title " .
							"FROM $wpdb->posts as posts " .
							"WHERE posts.ID IN ( $ids )"
						);

						if ( is_wp_error( $query ) ) {
							break;
						}

						$items = array();

						foreach ( $query as $post ) {
							$items[ $post->ID ] = $post->post_title;
						}
					}
					break;
				case 'taxonomy' :
					if ( isset( $option['source'] ) ) {
						$population = 'taxonomy';
						$source     = ( is_array( $option['source'] ) ) ? implode( "', '", $option['source'] ) : $option['source'];

						if ( empty( $data['value'] ) ) {
							break;
						}

						global $wpdb;

						$ids = implode( ',', $data['value'] );

						$query = $wpdb->get_results(
							"SELECT terms.term_id id, terms.name title " .
							"FROM $wpdb->terms as terms, $wpdb->term_taxonomy as taxonomies " .
							"WHERE terms.term_id IN ($ids) AND taxonomies.taxonomy IN ( $source ) " .
							"AND terms.term_id = taxonomies.term_id " .
							"AND taxonomies.term_id = taxonomies.term_taxonomy_id"
						);

						if ( is_wp_error( $query ) || empty( $query ) ) {
							break;
						}

						$items = array();

						foreach ( $query as $term ) {
							$items[ $term->id ] = $term->title;
						}
					}
					break;
				case 'users' :
					global $wpdb;
					$population = 'users';

					if ( isset( $option['source'] ) && ! empty( $option['source'] ) ) {
						;
						$source         = ( is_array( $option['source'] ) ) ? implode( ",", $option['source'] ) : $option['source'];
						$current_source = ( is_array( $option['source'] ) ) ? implode( "%' OR usermeta.meta_value LIKE '%", $option['source'] ) : $option['source'];

						if ( empty( $data['value'] ) ) {
							break;
						}

						$ids = implode( ',', $data['value'] );

						$query = $wpdb->get_results(
							"SELECT users.id, users.display_name title " .
							"FROM $wpdb->users as users, $wpdb->usermeta usermeta " .
							"WHERE users.ID IN ($ids) AND usermeta.meta_key = 'wp_capabilities' AND ( usermeta.meta_value LIKE '%$current_source%' ) " .
							"AND usermeta.user_id = users.ID"
						);

					} else {
						$source = '';

						if ( empty( $data['value'] ) ) {
							break;
						}

						$ids = implode( ',', $data['value'] );

						$query = $wpdb->get_results(
							"SELECT users.id, users.display_name title " .
							"FROM $wpdb->users as users " .
							"WHERE users.ID IN ($ids)"
						);
					}

					if ( is_wp_error( $query ) || empty( $query ) ) {
						break;
					}

					$items = array();

					foreach ( $query as $term ) {
						$items[ $term->id ] = $term->title;
					}

					break;
				default :
					$items = '';
			}

			$option['attr']['data-options']    = json_encode( $this->convert_array( $items ) );
			$option['attr']['data-population'] = $population;
			$option['attr']['data-source']     = $source;
			$option['attr']['data-limit']      = ( intval( $option['limit'] ) > 0 ) ? $option['limit'] : 0;
		} else {
			return '';
		}
		if ( ! empty( $data['value'] ) ) {
			$data['value'] = implode( '/*/', $data['value'] );
		} else {
			$data['value'] = '';
		}

		return fw()->backend->option_type( 'text' )->render( $id, $option, $data );
	}

	/**
	 * @internal
	 */
	private function add_css() {
		wp_enqueue_style(
			$this->get_type() . '-styles',
			FW_URI . '/includes/option-types/' . $this->get_type() . '/static/css/style.css',
			array('fw-selectize'),
			fw()->manifest->get_version()
		);
	}

	/**
	 * @internal
	 */
	private function add_js() {
		wp_enqueue_script(
			$this->get_type() . '-styles',
			FW_URI . '/includes/option-types/' . $this->get_type() . '/static/js/scripts.js',
			array( 'jquery', 'fw-events', 'fw-selectize' ),
			fw()->manifest->get_version(),
			true
		);
	}

	/**
	 * @internal
	 */
	private function convert_array( $array = array() ) {
		if ( ! is_array( $array ) || empty( $array ) ) {
			return array();
		}

		$return = array();
		foreach ( $array as $key => $item ) {
			$return[] = array(
				'val'   => $key,
				'title' => $item,
			);
		}

		return $return;
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		$value = explode( '/*/', $input_value );

		return empty( $input_value ) ? array() : $value;
	}
}

FW_Option_Type::register( 'FW_Option_Type_Multi_Select' );

add_action( 'wp_ajax_admin_action_get_ajax_response', array( "FW_Option_Type_Multi_Select", '_admin_action_get_ajax_response' ) );
