<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Seo_Webmasters extends FW_Extension {

	private $webmasters = array();

	private $settings_options = null;

	/**
	 * @internal
	 */
	public function _init() {
		$this->define_webmasters();

		if ( is_admin() ) {
			$this->add_admin_filters();
		} else {
			$this->add_theme_actions();
		}
	}

	/**
	 * @internal
	 *
	 * Defines the array with the available webmasters
	 */
	private function define_webmasters() {
		$this->webmasters = array(
			'google' => array(
				'id'       => 'google',
				'name'     => __( 'Google Webmasters', 'fw' ),
				'desc'     => __( 'Insert Google Webmasters verification code', 'fw' ),
				'settings' => array(
					'meta-name' => 'google-site-verification'
				)
			),
			'bing'   => array(
				'id'       => 'bing',
				'name'     => __( 'Bing Webmasters', 'fw' ),
				'desc'     => __( 'Insert Bing Webmasters verification code', 'fw' ),
				'settings' => array(
					'meta-name' => 'msvalidate.01'
				)
			),
		);
	}

	/**
	 * Init admin area filters
	 */
	private function add_admin_filters() {
		add_filter( 'fw_ext_seo_general_tab_admin_options', array( $this, '_admin_filter_set_framework_options' ) );
	}

	/**
	 * Init frontend are actions
	 */
	private function add_theme_actions() {
		add_action( 'wp_head', array( $this, '_theme_action_add_webmasters_meta' ) );
	}

	/**
	 * @internal
	 *
	 * @param null|string $index
	 *
	 * @return mixed|null
	 */
	private function get_admin_options( $index = null ) {
		if ( is_null( $this->settings_options ) ) {
			$this->settings_options = fw_get_db_extension_data( $this->get_parent()->get_name(), 'options' );
		}

		if ( is_null( $index ) ) {
			return $this->settings_options;
		}

		if ( ! isset( $this->settings_options[ $index ] ) ) {
			return null;
		}

		return $this->settings_options[ $index ];
	}

	/**
	 * Adds the extension settings box in Framework in SEO extension
	 *
	 * @param $options , holds the general options from extension config file
	 *
	 * @return array
	 * @internal
	 */
	public function _admin_filter_set_framework_options( $options ) {
		$webmasters = $this->get_config( 'webmasters' );
		if ( empty( $webmasters ) ) {
			return $options;
		}

		$general_options = array(
			$this->get_name() => array(
				'title'   => __( 'Webmasters', 'fw' ),
				'type'    => 'box',
				'options' => array()
			)
		);

		foreach ( $webmasters as $webmaster ) {
			if ( ! isset( $this->webmasters[ $webmaster ] ) ) {
				FW_Flash_Messages::add( 'fw-ext-seo-add-tabs', sprintf( __( 'Webmaster %s already exists', 'fw' ), $webmaster ), 'warning' );
				continue;
			}

			$prefix                                                     = $this->get_name() . '-' . $webmaster;
			$general_options[ $this->get_name() ]['options'][ $prefix ] = array(
				'label' => $this->webmasters[ $webmaster ]['name'],
				'desc'  => $this->webmasters[ $webmaster ]['desc'],
				'type'  => 'text',
				'value' => ''
			);
		}

		if ( empty( $general_options[ $this->get_name() ]['options'] ) ) {
			return $options;
		}

		$options = array_merge( $options, $general_options );

		return $options;
	}

	/**
	 * Adds webmasters meta tags in front-end
	 * @internal
	 */
	public function _theme_action_add_webmasters_meta() {
		$webmasters = $this->get_config( 'webmasters' );

		foreach ( $webmasters as $webmaster ) {

			if ( ! isset( $this->webmasters[ $webmaster ] ) ) {
				continue;
			}

			$data  = array();
			$value = $this->get_admin_options( $this->get_name() . '-' . $webmaster );

			if ( empty( $value ) ) {
				continue;
			}

			$data['name']    = $this->webmasters[ $webmaster ]['settings']['meta-name'];
			$data['content'] = $value;

			echo $this->render_view( 'meta', $data );
		}
	}
}