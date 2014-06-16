<?php
/*
Plugin Name: Custom Post Menu
Description: add custom post type to custom menu
Version: 0.3
Author: jhonyspicy
license: GPL v2
*/
add_filter('wp_setup_nav_menu_item', array('CustomPostMenu',
										   'wp_setup_nav_menu_item'));
add_action('admin_head-nav-menus.php', array('CustomPostMenu',
											 'admin_menu'));
add_action('wp_ajax_add-menu-item', array('CustomPostMenu',
										  'wp_ajax_add_menu_item'), 1);
add_filter('wp_edit_nav_menu_walker', array('CustomPostMenu',
											'wp_edit_nav_menu_walker'));
add_filter('wp_nav_menu_args', array('CustomPostMenu',
									 'wp_nav_menu_args'));



require_once(ABSPATH . 'wp-content/plugins/custom-post-menu/Walker_Nav_Menu_Custom_Post_Type.php');

class CustomPostMenu {
	static function wp_setup_nav_menu_item($menu_item) {
		if (property_exists($menu_item, 'type') && $menu_item->type == 'custom_post_type') {
			$menu_item->type_label = 'カスタム投稿タイプ';
			$menu_item->url = get_post_type_archive_link($menu_item->object);
		}


		return $menu_item;
	}

	static function admin_menu() {
		$post_types = get_post_types(array('show_in_nav_menus' => true,
										   'has_archive'       => true), 'objects');

		if (0 == count($post_types)) {
			return;
		}

		add_meta_box('add_post_type', 'カスタム投稿タイプ', array(__CLASS__,
														   'add_post_type_inner'), 'nav-menus', 'side', 'low');

		require_once(ABSPATH . 'wp-content/plugins/custom-post-menu/Walker_Nav_Menu_Checklist_Custom_Post_Type.php');

	}

	static function add_post_type_inner($object, $info) {
		global $nav_menu_selected_id;
		$post_types = get_post_types(array('publicly_queryable' => true,
										   'has_archive'        => true), 'object');
		foreach ($post_types as $post_type) {
			$post_type->menu_item_parent = 0;
			$post_type->db_id            = 0;
		}

		if (!empty($post_types)) {
			$walker         = new Walker_Nav_Menu_Checklist_Custom_Post_Type();
			$args['walker'] = $walker;

			echo '<div id="post-type-archives">';
			echo '<div class="tabs-panel-active">';
			echo '<ul id="page-all" class="categorychecklist form-no-clear">';

			echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $post_types), 0, (object)$args);

			echo '</ul>';
			echo '</div>';

			?>
			<p class="button-controls">
				<span class="add-to-menu">
					<input type="submit"<?php wp_nav_menu_disabled_check($nav_menu_selected_id); ?>  class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-taxonomy-menu-item" id="submit-post-type-archives"/>
					<span class="spinner"></span>
				</span>
			</p>
			<?php
			echo '</div>';
		}
	}

	static function wp_edit_nav_menu_walker($walker_class_name) {
		require_once(ABSPATH . 'wp-content/plugins/custom-post-menu/Walker_Nav_Menu_Edit_Custom_Post_Type.php');
		return 'Walker_Nav_Menu_Edit_Custom_Post_Type';
	}

	static function wp_ajax_add_menu_item() {
		remove_action('wp_ajax_add-menu-item', 'wp_ajax_add_menu_item');

		check_ajax_referer('add-menu_item', 'menu-settings-column-nonce');

		if (!current_user_can('edit_theme_options')) {
			wp_die(-1);
		}

		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
		require_once(ABSPATH . 'wp-content/plugins/custom-post-menu/Walker_Nav_Menu_Edit_Custom_Post_Type.php');

		// For performance reasons, we omit some object properties from the checklist.
		// The following is a hacky way to restore them when adding non-custom items.

		$menu_items_data = array();
		foreach ((array)$_POST['menu-item'] as $menu_item_data) {
			if (!empty($menu_item_data['menu-item-type']) && 'custom' != $menu_item_data['menu-item-type'] && !empty($menu_item_data['menu-item-object-id'])
			) {
				switch ($menu_item_data['menu-item-type']) {
					case 'post_type' :
						$_object = get_post($menu_item_data['menu-item-object-id']);
						break;

					case 'custom_post_type':
						$_object = get_post_type_object($menu_item_data['menu-item-object']);
						break;

					case 'taxonomy' :
						$_object = get_term($menu_item_data['menu-item-object-id'], $menu_item_data['menu-item-object']);
						break;
				}

				$_menu_items = array_map('wp_setup_nav_menu_item', array($_object));
				$_menu_item  = array_shift($_menu_items);

				// Restore the missing menu item properties
				$menu_item_data['menu-item-description'] = $_menu_item->description;
			}

			$menu_items_data[] = $menu_item_data;
		}

		$item_ids = wp_save_nav_menu_items(0, $menu_items_data);
		if (is_wp_error($item_ids)) {
			wp_die(0);
		}

		$menu_items = array();

		foreach ((array)$item_ids as $menu_item_id) {
			$menu_obj = get_post($menu_item_id);
			if (!empty($menu_obj->ID)) {
				$menu_obj        = wp_setup_nav_menu_item($menu_obj);
				$menu_obj->label = $menu_obj->title; // don't show "(pending)" in ajax-added items
				$menu_items[]    = $menu_obj;
			}
		}

		if (!empty($menu_items)) {
			$args = array('after'       => '',
						  'before'      => '',
						  'link_after'  => '',
						  'link_before' => '',
						  'walker'      => new Walker_Nav_Menu_Edit_Custom_Post_Type,);
			echo walk_nav_menu_tree($menu_items, 0, (object)$args);
		}
		wp_die();
	}

	static function wp_nav_menu_args($args) {
		if (!$args['walker']) {
			$locations = get_nav_menu_locations();
			$menu      = wp_get_nav_menu_object($args['menu']);
			if ($menu || (array_key_exists('theme_location', $args) && $args['theme_location'] && array_key_exists($args['theme_location'], $locations) && $locations[ $args['theme_location'] ])) {
				$args['walker'] = new Walker_Nav_Menu_Custom_Post_Type();
			}
		}

		return $args;
	}
}
