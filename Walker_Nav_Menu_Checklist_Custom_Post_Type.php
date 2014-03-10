<?php
class Walker_Nav_Menu_Checklist_Custom_Post_Type extends Walker_Nav_Menu_Checklist {
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $_nav_menu_placeholder;

		$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;

		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$output .= $indent . '<li>';
		$output .= '<label class="menu-item-title">';
		$output .= '<input type="checkbox" class="menu-item-checkbox';

		if ( ! empty( $item->front_or_home ) )
			$output .= ' add-to-top';

		$output .= '" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-object-id]" value="'. esc_attr( $item->name ) .'" /> ';

		$output .= $item->label;
		$output .= '</label>';

		// Menu item hidden fields
		$output .= '<input type="hidden" class="menu-item-db-id" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-db-id]" value="-1" />';
		$output .= '<input type="hidden" class="menu-item-object" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-object]" value="' . $item->name . '" />';
		$output .= '<input type="hidden" class="menu-item-parent-id" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-parent-id]" value="0" />';
		$output .= '<input type="hidden" class="menu-item-type" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-type]" value="custom_post_type" />';
		$output .= '<input type="hidden" class="menu-item-title" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-title]" value="'. esc_attr( $item->label ) .'" />';
		$output .= '<input type="hidden" class="menu-item-url" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-url]" value="'. esc_attr( get_post_type_archive_link($item->name) ) .'" />';
		$output .= '<input type="hidden" class="menu-item-target" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-target]" value="" />';
		$output .= '<input type="hidden" class="menu-item-attr_title" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-attr_title]" value="" />';
		$output .= '<input type="hidden" class="menu-item-classes" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-classes]" value="" />';
		$output .= '<input type="hidden" class="menu-item-xfn" name="menu-item[' . $_nav_menu_placeholder . '][menu-item-xfn]" value="" />';
	}
}