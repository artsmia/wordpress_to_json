<?php
/*
Plugin Name: To JSON
Description: Spits out JSON for the site (currently tailored to TDX Africa).
Version: 1.0
Author: Minneapolis Institute of Arts
*/

// Register Custom Post Type
add_action( 'init', 'add_json_posttype', 0 );
function add_json_posttype() {
	$labels = array(
		'name'                => 'JSON',
		'singular_name'       => 'JSON',
		'menu_name'           => '',
		'parent_item_colon'   => '',
		'all_items'           => '',
		'view_item'           => '',
		'add_new_item'        => '',
		'add_new'             => '',
		'edit_item'           => '',
		'update_item'         => '',
		'search_items'        => '',
		'not_found'           => '',
		'not_found_in_trash'  => '',
	);
	$args = array(
		'label'               => 'json',
		'description'         => 'JSON',
		'labels'              => $labels,
		'supports'            => array( ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => false,
		'show_in_menu'        => false,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => false,
		'menu_position'       => 5,
		'menu_icon'           => '',
		'can_export'          => false,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'json', $args );
}

// Intercept request for post type archive
// Redirect to JSON template (tdx_africa for now)
add_filter('template_redirect', 'json_template');
function json_template(){
	if(is_post_type_archive('json')){
		include('templates/tdx_africa.php');
		exit;
	}
}

?>
