<?php if (!defined('FW')) die('Forbidden');

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

class _FW_Extensions_Delete_Upgrader_Skin extends WP_Upgrader_Skin
{
	public function after($data = array())
	{
		$update_actions = array(
			'extensions_page' => fw_html_tag(
				'a',
				array(
					'href' => fw_akg('extensions_page_link', $data, '#'),
					'title' => __('Go to extensions page', 'fw'),
					'target' => '_parent',
				),
				__('Return to Extensions page', 'fw')
			)
		);

		$this->feedback(implode(' | ', (array)$update_actions));

		if ($this->result) {
			// used for popup ajax form submit result
			$this->feedback('<span success></span>');
		}
	}
}
