<?php if (!defined('FW')) die('Forbidden');

/**
 * Exception can collect not allowed colors
 * @internal
 */
class _FW_Extension_Sidebars_MissingSidebar_Exception extends Exception
{
	private $colors = array();

	public function set_colors($colors)
	{
		$this->colors = $colors;

		return $this;
	}

	public function add_color($color)
	{
		$this->colors[] = $color;

		return $this;
	}

	public function get_colors()
	{
		return $this->colors;
	}

	public function has_colors(){
		return empty($this->colors) ? false : true;
	}
}
