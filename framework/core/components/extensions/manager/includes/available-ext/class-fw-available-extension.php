<?php if (!defined('FW')) die('Forbidden');

/**
 * Used to define extension in framework Available Extensions list
 * @since 2.5.12
 */
class FW_Available_Extension extends FW_Type {
	/**
	 * Extension (directory) name
	 */
	private $name;

	/**
	 * @var null|string Parent extension name
	 */
	private $parent = null;

	/**
	 * @var bool If visible in extensions list
	 */
	private $display = true;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var string Image url
	 */
	private $thumbnail = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIW2PUsHf9DwAC8AGtfm5YCAAAAABJRU5ErkJgggAA';

	/**
	 * @var array {source: id, opts: {...}}
	 * @see FW_Ext_Download_Source::get_type() is id
	 * @see FW_Ext_Download_Source
	 */
	private $download_source = array();

	/**
	 * @return bool
	 * @since 2.6.0
	 */
	public function is_valid() {
		return (
			!empty($this->name) && is_string($this->name)
			&&
			!empty($this->title) && is_string($this->title)
			&&
			!empty($this->description) && is_string($this->description)
			&&
			!empty($this->download_source)
			&&
		    is_bool($this->display)
			&&
			(is_null($this->parent) || is_string($this->parent))
		);
	}

	/**
	 * @return string
	 * @internal
	 */
	final public function get_type() {
		return $this->get_name();
	}

	public function get_name() {
		return $this->name;
	}

	public function set_name($name) {
		$this->name = $name;
	}

	public function get_parent() {
		return $this->parent;
	}

	public function set_parent($parent) {
		$this->parent = $parent;
	}

	public function get_display() {
		return $this->display;
	}

	public function set_display($display) {
		$this->display = $display;
	}

	public function get_title() {
		return $this->title;
	}

	public function set_title($title) {
		$this->title = $title;
	}

	public function get_description() {
		return $this->description;
	}

	public function set_description($description) {
		$this->description = $description;
	}

	public function get_thumbnail() {
		return $this->thumbnail;
	}

	public function set_thumbnail($thumbnail) {
		$this->thumbnail = $thumbnail;
	}

	public function get_download_source() {
		return $this->download_source;
	}

	public function set_download_source($id, $data) {
		$this->download_source = array(
			'source' => $id,
			'opts' => $data
		);
	}
}
