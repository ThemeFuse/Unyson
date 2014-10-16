<?php if (!defined('FW')) die('Forbidden');

class FW_Theme_Menu_Walker extends Walker_Nav_Menu
{
	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 * @param int    $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		/**
		 * Filter the CSS class(es) applied to a menu item's <li>.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param array  $classes The CSS classes that are applied to the menu item's <li>.
		 * @param object $item    The current menu item.
		 * @param array  $args    An array of wp_nav_menu() arguments.
		 */
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		/**
		 * Filter the ID applied to a menu item's <li>.
		 *
		 * @since 3.0.1
		 *
		 * @see wp_nav_menu()
		 *
		 * @param string $menu_id The ID that is applied to the menu item's <li>.
		 * @param object $item    The current menu item.
		 * @param array  $args    An array of wp_nav_menu() arguments.
		 */
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names .'>';

		$atts = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';

		/**
		 * Filter the HTML attributes applied to a menu item's <a>.
		 *
		 * @since 3.6.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param array $atts {
		 *     The HTML attributes applied to the menu item's <a>, empty strings are ignored.
		 *
		 *     @type string $title  Title attribute.
		 *     @type string $target Target attribute.
		 *     @type string $rel    The rel attribute.
		 *     @type string $href   The href attribute.
		 * }
		 * @param object $item The current menu item.
		 * @param array  $args An array of wp_nav_menu() arguments.
		 */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

# BEGIN - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// $item_output = $args->before;
		// $item_output .= '<a'. $attributes .'>';
		// /** This filter is documented in wp-includes/post-template.php */
		// $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		// $item_output .= '</a>';
		// $item_output .= $args->after;
		$title = apply_filters('the_title', $item->title, $item->ID);
		$attributes = array_filter($atts);
		$item_output = $this->megamenu()->render_str('item-link', compact('item', 'attributes', 'title', 'args', 'depth'));
# END - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

		/**
		 * Filter a menu item's starting output.
		 *
		 * The menu item's starting output only includes $args->before, the opening <a>,
		 * the menu item's title, the closing </a>, and $args->after. Currently, there is
		 * no filter for modifying the opening and closing <li> for a menu item.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param string $item_output The menu item's starting HTML output.
		 * @param object $item        Menu item data object.
		 * @param int    $depth       Depth of menu item. Used for padding.
		 * @param array  $args        An array of wp_nav_menu() arguments.
		 */
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {

		if ( !$element )
			return;

		$id_field = $this->db_fields['id'];

		//display this element
		if ( isset( $args[0] ) && is_array( $args[0] ) )
			$args[0]['has_children'] = ! empty( $children_elements[$element->$id_field] );
		$cb_args = array_merge( array(&$output, $element, $depth), $args);
		call_user_func_array(array($this, 'start_el'), $cb_args);

		$id = $element->$id_field;

		// descend only when the depth is right and there are childrens for this element
		if ( ($max_depth == 0 || $max_depth > $depth+1 ) && isset( $children_elements[$id]) ) {

			foreach( $children_elements[ $id ] as $child ){
# BEGIN - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
				if ($depth == 0 && get_mega_menu_meta($id, 'enabled') && get_mega_menu_meta($child, 'new-row')) {
					if (isset($newlevel) && $newlevel) {
						$cb_args = array_merge( array(&$output, $depth), $args);
						call_user_func_array(array($this, 'end_lvl'), $cb_args);
						unset($newlevel);
					}
				}
# END - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
				if ( !isset($newlevel) ) {
					$newlevel = true;
# BEGIN - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
					if (!isset($mega_menu_container) && $depth == 0 && get_mega_menu_meta($id, 'enabled')) {
						$mega_menu_container = true;
						$output .= '<div class="mega-menu">';
					}
					$class = 'sub-menu';
					if (isset($mega_menu_container)) {
						if ($this->row_has_icons($element, $child, $children_elements)) {
							$class .= ' sub-menu-has-icons';
						}
						$class .= ' mega-menu-row';
					}
					else {
						if ($this->sub_menu_has_icons($element, $children_elements)) {
							$class .= ' sub-menu-has-icons';
						}
					}
# END - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
					//start the child delimiter
					$cb_args = array_merge( array(&$output, $depth), $args, array($class));
					call_user_func_array(array($this, 'start_lvl'), $cb_args);
				}
				$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
			}
			unset( $children_elements[ $id ] );
		}

		if ( isset($newlevel) && $newlevel ){
			//end the child delimiter
			$cb_args = array_merge( array(&$output, $depth), $args);
			call_user_func_array(array($this, 'end_lvl'), $cb_args);
		}

# BEGIN - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if (isset($mega_menu_container)) {
			$output .= '</div>';
		}
# END - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		//end this element
		$cb_args = array_merge( array(&$output, $element, $depth), $args);
		call_user_func_array(array($this, 'end_el'), $cb_args);
	}

	function start_lvl( &$output, $depth = 0, $args = array(), $class = 'sub-menu' ) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<ul class=\"$class\">\n";
	}

	private function sub_menu_has_icons($element, $children_elements) {
		$id_field = $this->db_fields['id'];
		$id = $element->$id_field;
		foreach ($children_elements[$id] as $child) {
			if (get_mega_menu_meta($child, 'icon')) {
				return true;
			}
		}
		return false;
	}

	private function row_has_icons($row, $first_column, $children_elements) {

		$id_field = $this->db_fields['id'];
		$row_id = $row->$id_field;

		reset($children_elements[$row_id]);

		// navigate to $first_column
		while ($child = next($children_elements[$row_id])) {
			if ($child->$id_field == $first_column->$id_field) {
				break;
			}
		}

		// scan row
		while (true) {
			if (get_mega_menu_meta($child, 'icon')) {
				return true;
			}
			$child = next($children_elements[$row_id]);
			if ($child === false || get_mega_menu_meta($child, 'new-row')) {
				break;
			}
		}

		return false;
	}

	/**
	 * @return FW_Extension_Megamenu
	 */
	private function megamenu()
	{
		return fw()->extensions->get('megamenu');
	}
}
