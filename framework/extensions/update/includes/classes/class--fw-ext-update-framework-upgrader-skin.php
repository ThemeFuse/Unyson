<?php if (!defined('FW')) die('Forbidden');

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

class _FW_Ext_Update_Framework_Upgrader_Skin extends WP_Upgrader_Skin
{
	public function after()
	{
		$this->decrement_update_count('fw');

		$update_actions = array(
			'updates_page' => fw_html_tag(
				'a',
				array(
					'href' => self_admin_url('update-core.php'),
					'title' => __('Go to updates page', 'fw'),
					'target' => '_parent',
				),
				__('Return to Updates page', 'fw')
			)
		);

		/**
		 * Filter the list of action links available following framework update.
		 * @param array $update_actions Array of plugin action links.
		 */
		$update_actions = apply_filters('fw_ext_update_framework_complete_actions', $update_actions);

		if (!empty($update_actions)) {
			$this->feedback(implode(' | ', (array)$update_actions));
		}
	}
}
