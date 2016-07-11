<?php

if (! defined('FW')) { die('Forbidden'); }

require_once dirname(__FILE__) . '/includes/class-fw-icon-v2-packs-loader.php';
require_once dirname(__FILE__) . '/includes/class-fw-icon-v2-favorites.php';

class FW_Option_Type_Icon_v2 extends FW_Option_Type
{
	private $enqueued_font_styles = array();
	public $packs_loader = null;

	public function get_type()
	{
		return 'icon-v2';
	}

	public function _init()
	{
		$this->packs_loader = new FW_Icon_V2_Packs_Loader();
		$this->favorites = new FW_Icon_V2_Favorites_Manager();

		/**
		 * CSS for each pack is not loaded by default in frontend.
		 *
		 * You should load it by yourself in your theme, like this:
		 *
		 * fw()->backend->option_type('icon-v2')->packs_loader->enqueue_frontend_css()
		 */
	}

	protected function _enqueue_static($id, $option, $data)
	{
		$this->packs_loader->enqueue_admin_css();

		$static_URI = fw_get_framework_directory_uri(
			'/includes/option-types/' . $this->get_type() . '/static/'
		);

		wp_enqueue_style(
			'fw-selectize'
		);

		wp_enqueue_script(
			'fw-option-type-'. $this->get_type() .'-backend-previews',
			$static_URI . 'js/render-icon-previews.js',
			array('jquery', 'fw', 'fw-events', 'fw-selectize'),
			fw()->manifest->get_version()
		);

		wp_enqueue_script(
			'fw-option-type-'. $this->get_type() .'-backend-picker',
			$static_URI . 'js/icon-picker.js',
			array(
				'fw-option-type-'. $this->get_type() .'-backend-previews'
			),
			fw()->manifest->get_version()
		);

		wp_enqueue_style(
			'fw-option-type-'. $this->get_type() .'-backend-picker',
			$static_URI . 'css/picker.css',
			array(),
			fw()->manifest->get_version()
		);

		wp_localize_script(
			'fw-option-type-'. $this->get_type() .'-backend-previews',
			'fw_icon_v2_data',
			array(
				'edit_icon_label' => __('Change Icon', 'fw'),
				'add_icon_label' => __('Add Icon', 'fw'),
				'icon_fonts_label' => __('Icons', 'fw'),
				'custom_upload_label' => __('Upload', 'fw'),
				'favorites_label' => __('Favorites', 'fw'),
				'search_label' => __('Search Icon', 'fw'),
				'select_pack_label' => __('Select Pack', 'fw'),
				'all_packs_label' => __('All Packs', 'fw'),
				'favorites_empty_label' => __(
					'<h4>You have no favorite icons yet.</h4> To add icons here, simply click on the star (<i class="fw-icon-v2-info dashicons dashicons-star-filled"></i>) button that\'s on top of each icon.',
					'fw'
				),
				'icons' => $this->packs_loader->get_packs()
			)
		);
	}

	protected function _render($id, $option, $data)
	{
		$json = $this->_get_json_value_to_insert_in_html($data);

		$option['attr']['value'] = $json;

		return fw_render_view(
			dirname(__FILE__) . '/view.php',
			compact('id', 'option', 'data', 'json')
		);
	}

	protected function _get_value_from_input($option, $input_value)
	{
		if (is_null( $input_value )) {
			return $option['value'];
		}

		return $this->_get_db_value_from_json($input_value);
	}

	protected function _get_db_value_from_json($input_value)
	{
		$input = $input_value;

		/**
		 * When icon-v2 is used as a multi-picker picker it, the value
		 * comes straight as array, you should parse it.
		 */
		if (! is_array($input_value)) {
			$input = json_decode($input_value, true);
		}

		$result = array();

		$result['type'] = $input['type'];

		if ($input['type'] === 'icon-font') {
			$result['icon-class'] = $input['icon-class'];

			$result['icon-class-without-root'] = $this->packs_loader->class_without_root_for(
				$input['icon-class']
			);

			$pack = $this->packs_loader->pack_name_for(
				$input['icon-class']
			);

			$result['pack-name'] = $pack['name'];
			$result['pack-css-uri'] = $pack['css_file_uri'];
		}

		if ($input['type'] === 'custom-upload') {
			$result['attachment-id'] = $input['attachment-id'];
			$result['url'] = $input['url'];
		}

		return $result;
	}

	protected function _get_json_value_to_insert_in_html($data)
	{
		$result = array();

		$result['type'] = $data['value']['type'];

		if ($data['value']['type'] === 'icon-font') {
			$result['icon-class'] = $data['value']['icon-class'];
		}

		if ($data['value']['type'] === 'custom-upload') {
			$result['attachment-id'] = $data['value']['attachment-id'];
			$result['url'] = $data['value']['url'];
		}

		return json_encode($result);
	}

	protected function _get_defaults()
	{
		return array(
			'value' => array(
				'type' => 'icon-font', // icon-font | custom-upload

				// ONLY IF icon-font
				'icon-class' => '',
				'icon-class-without-root' => false,
				'pack-name' => false,
				'pack-css-uri' => false

				// ONLY IF custom-upload
				// 'attachment-id' => false,
				// 'url' => false
			),

			'preview_size' => 'medium',
			'popup_size' => 'medium'
		);
	}

	public function _get_backend_width_type()
	{
		return 'full';
	}
}

$favorites = new FW_Icon_V2_Favorites_Manager();
$favorites->attach_ajax_actions();

FW_Option_Type::register( 'FW_Option_Type_Icon_v2' );
