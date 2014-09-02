<?php if (!defined('FW')) die('Forbidden');

class FW_Theme_Menu_Walker extends Walker_Nav_Menu
{
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
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
				if ($depth == 0 && get_mega_menu_meta($id, 'enabled') && get_mega_menu_meta($child, 'new-row')) {
					if (isset($newlevel) && $newlevel) {
						$cb_args = array_merge( array(&$output, $depth), $args);
						call_user_func_array(array($this, 'end_lvl'), $cb_args);
						unset($newlevel);
					}
				}
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
				if ( !isset($newlevel) ) {
					$newlevel = true;
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
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
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
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

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if (isset($mega_menu_container)) {
			$output .= '</div>';
		}
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

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
}
