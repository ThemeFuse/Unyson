<?php if (!defined('FW')) die('Forbidden');

/**
 * Display extensions with updates on the Update Page
 */
class _FW_Ext_Update_Extensions_List_Table extends WP_List_Table
{
	private $items_pre_page = 1000;
	
	private $total_items = null;

	private $_extensions = array();

	private $_table_columns = array();
	private $_table_columns_count = 0;

	public function __construct($args)
	{
		parent::__construct(array(
			'screen' => 'fw-ext-update-extensions-update'
		));
		
		$this->_extensions = $args['extensions'];

		$this->_table_columns = array(
			'cb' => '<input type="checkbox" />',
			'details' => fw_html_tag(
				'a',
				array(
					'href' => '#',
					'onclick' => "jQuery(this).closest('tr').find('input[type=\"checkbox\"]:first').trigger('click'); return false;"
				),
				__('Select All', 'fw')
			),
		);
		$this->_table_columns_count = count($this->_table_columns);
	}

	public function get_columns()
	{
		return $this->_table_columns;
	}

	public function prepare_items()
	{
		if ($this->total_items !== null) {
			return;
		}

		$this->total_items = count($this->_extensions);

		$this->set_pagination_args(array(
			'total_items' => $this->total_items,
			'per_page'    => $this->items_pre_page,
		));

		$page_num = $this->get_pagenum();
		$offset = ($page_num - 1) * $this->items_pre_page;

		/**
		 * Prepare items for output
		 */
		foreach ($this->_extensions as $ext_name => $ext_update) {
			$extension = fw()->extensions->get($ext_name);

			if (is_wp_error($ext_update)) {
				$this->items[] = array(
					'cb'      => '<input type="checkbox" disabled />',
					'details' =>
						'<p>'.
							'<strong>'. fw_htmlspecialchars($extension->manifest->get_name()) .'</strong>'.
						'<br/>'.
						'<span class="wp-ui-text-notification">'. $ext_update->get_error_message() .'</span>'.
						'</p>',
				);
			} else {
				$this->items[] = array(
					'cb'      => '<input type="checkbox" name="extensions['. esc_attr($ext_name) .']" />',
					'details' =>
						'<p>'.
							'<strong>'. fw_htmlspecialchars($extension->manifest->get_name()) .'</strong>'.
							'<br/>'.
							sprintf(
								__('You have version %s installed. Update to %s.', 'fw'),
								$extension->manifest->get_version(), fw_htmlspecialchars($ext_update['fixed_latest_version'])
							).
						'</p>',
				);
			}
		}
	}

	public function has_items()
	{
		$this->prepare_items();

		return $this->total_items;
	}

	/**
	 * (override parent)
	 */
	function single_row($item)
	{
		static $row_class = '';

		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		echo '<tr' . $row_class . '>';
		echo $this->single_row_columns( $item );
		echo '</tr>';
	}

	protected function column_cb($item)
	{
		echo $item['cb'];
	}

	protected function column_default($item, $column_name)
	{
		echo $item[$column_name];
	}

	function no_items()
	{
		_e('No Extensions for update.', 'fw');
	}
}
