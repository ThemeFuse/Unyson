<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * This option type let you run a callback by click on Run button.
 * example of $option array
 * array(
 *   'type'  => 'runnable',
 *   'value' => 'This script have no runs',
 *   'label' => __('Convert data', 'fw'),
 *   'desc'  => __('There are posts, pages, categories or tags without language set. Do you want to set them all to default language.', 'fw'),
 *   'help'  => __('Help tip', 'fw'),
 *   'content'=>__('Run this script'),
 *   'callback'=> array('translation' , 'convert_data_to_default_language')
 *  )
 */
/**
 * Class FW_Option_Type_Runnable
 */
class FW_Option_Type_Runnable extends FW_Option_Type {

	/**
	 * Option's unique type, used in option array in 'type' key
	 * @return string
	 */
	public function get_type() {
		return 'runnable';


	}

	/**
	 * Run callback.
	 */
	public function run_callback() {

		$callback = explode( '.', FW_Request::POST( 'callback' ) );
		$length   = count( $callback );

		if ( $length === 1 && is_callable( $callback[0] ) ) {
			$handler = $callback[0];
		} elseif ( $length === 2 && is_callable( $callback ) ) {
			$handler = $callback;
		} elseif ( $length === 2 && is_callable( $fw_ext_callback = array( fw_ext( $callback[0] ), $callback[1] ) ) ) {
			$handler = $fw_ext_callback;
		} else {
			wp_send_json_error( 'Your callback is not callable' );
		}

		call_user_func( $handler );
	}

	/**
	 * Generate option's html from option array.
	 *
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 *
	 * @return string HTML
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {

		$callback                = implode( '.', (array) $option['callback'] );
		$option['attr']['value'] = (string) $data['value'];

		return '<div class="runnable-wrapper">
		<button data-callback="' . $callback . '" type="button" class="runnable-button button-primary">' . $option['content'] . '</button>
		<div class="runnable-last-run">' . $option['attr']['value'] . '</div>
		<input type="hidden" ' . fw_attr_to_html( $option['attr'] ) . '/>
		</div>';
	}

	/**
	 * Enqueue static.
	 * @param string $id
	 * @param array $option
	 * @param array $data
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		$js_path  = fw_get_framework_directory_uri( '/includes/option-types/runnable/static/js/runnable.js' );
		$css_path = fw_get_framework_directory_uri( '/includes/option-types/runnable/static/css/runnable.css' );

		wp_enqueue_script(
			'fw-option-' . $this->get_type() . '-js',
			$js_path,
			array( 'jquery', 'fw-moment' ),
			fw()->manifest->get_version()
		);

		wp_enqueue_style(
			'fw-option-' . $this->get_type() . '-css',
			$css_path,
			array(),
			fw()->manifest->get_version()
		);
	}

	/**
	 * Extract correct value for $option['value'] from input array
	 * If input value is empty, will be returned $option['value']
	 *
	 * @param array $option
	 * @param array|string|null $input_value
	 *
	 * @return string|array|int|bool Correct value
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {
		return (string) ( is_null( $input_value ) ? $option['value'] : $input_value );
	}

	/**
	 * Default option array
	 *
	 * This makes possible an option array to have required only one parameter: array('type' => '...')
	 * Other parameters are merged with array returned from this method
	 *
	 * @return array
	 *
	 * array(
	 *     'value' => '',
	 *     ...
	 * )
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value'   => '',
			'content' => 'Run'
		);
	}

}

FW_Option_Type::register( 'FW_Option_Type_Runnable' );

add_action( 'wp_ajax_fw_runnable', array( 'FW_Option_Type_Runnable', 'run_callback' ) );
