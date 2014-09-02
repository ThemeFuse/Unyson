<?php if (!defined('FW')) die('Forbidden');

class FW_Extension_Shortcodes extends FW_Extension
{
	/** @var  FW_Shortcode[] $shortcodes */
	private $shortcodes;

	/**
	 * @internal
	 */
	protected function _init()
	{
		$this->add_actions();
	}

	private function add_actions()
	{
		add_action('fw_extensions_init', array($this, '_action_fw_extensions_init'));
	}

	/**
	 * @internal
	 */
	public function _action_fw_extensions_init()
	{
		$this->load_shortcodes();

		if (!is_admin()) {
			$this->register_shortcodes();
		}
	}

	private function load_shortcodes()
	{
		if ($this->shortcodes) {
			return;
		}
		$this->shortcodes = _FW_Shortcodes_Loader::load();
	}

	private function register_shortcodes()
	{
		foreach ($this->shortcodes as $tag => $instance) {
			add_shortcode($tag, array($instance, 'render'));
		}
	}

	/**
	 * Gets a certain shortcode by a given tag
	 *
	 * @param string $tag The shortcode tag
	 * @return FW_Shortcode|null
	 */
	public function get_shortcode($tag)
	{
		$this->load_shortcodes();
		return isset($this->shortcodes[$tag]) ? $this->shortcodes[$tag] : null;
	}

	/**
	 * Gets all shortcodes
	 *
	 * @return FW_Shortcode[]
	 */
	public function get_shortcodes()
	{
		$this->load_shortcodes();
		return $this->shortcodes;
	}
}
