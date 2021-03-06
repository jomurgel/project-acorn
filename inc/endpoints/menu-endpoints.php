<?php
/**
 * Custom endpoints.
 *
 * @package Acorn Theme
 * @since 1.0.0
 */

/**
 * Get all registered menus
 *
 * @return array List of menus with slug and description
 * @since 1.0.0
 */
function acorn_theme_get_all_menus() {

	// Setup empty array.
	$menus = [];

	foreach ( get_nav_menu_locations() as $slug => $id ) {

		// Get currently mentioned menu.
		$menu = wp_get_nav_menu_items( $id );

		// Get modified date from first menu item.
		$date = $menu[0]->post_modified;

		// Setup Anonymous Object.
		$object = new stdClass();

		// Add Modified Date.
		$object->menu_modified = $date;

		// Get slug and Description.
		$object->slug = $slug;
		$object->id   = $id;

		$items = [];

		// Inject items array into output.
		$menu        = get_term( $id );
		$menu->items = wp_get_nav_menu_items( $menu->term_id );

		$items = acorn_theme_get_menu_and_slug( $menu->items );

		// Ensure we have menu items.
		if ( ! empty( $items ) ) {
			$object->items = $items;
		}

		// Add objects to array.
		$menus[] = $object;
	}

	return $menus;
}

/**
 * Adds object slug to items object.
 *
 * @param array $items menu items.
 * @return array new array with item slug.
 * @since 1.0.0
 */
function acorn_theme_get_menu_and_slug( $items ) {

	$new_items = [];

	foreach ( $items as $item ) {

		$post_id     = $item->ID;
		$post_object = get_post( $post_id );
		$slug        = '';

		// Only output a value if we're NOT the homepage.
		if ( get_option( 'page_on_front' ) !== $item->object_id ) {
			$slug = basename( $item->url );
		}

		$item->object_slug = $slug;
	}

	return $items;
}


/**
 * Get menu data from by id
 *
 * @param  array $data WP REST API data variable.
 * @return object Menu's data with his items.
 * @since 0.0.1
 */
function acorn_theme_get_menu_json_object_by_id( $data ) {

	// Setup anonymous object.
	$menu = new stdClass();

	// Setup array for items.
	$menu->items = [];

	$locations = get_nav_menu_locations();

	if ( ! empty( $locations[ $location ] ) && isset( $locations[ $data['id'] ] ) ) {

		$menu        = get_term( $locations[ $data['id'] ] );
		$menu->items = wp_get_nav_menu_items( $menu->term_id );
	}

	// Return object.
	return $menu;
}

/**
 * Get menu data from by slug
 *
 * @param  array $data WP REST API data variable.
 * @return object Menu's data with his items
 * @since 1.0.0
 */
function acorn_theme_get_menu_json_object_by_slug( $data ) {

	// Setup anonymous object.
	$menu = new stdClass();

	// Setup array for items.
	$menu->items = [];

	$locations = get_nav_menu_locations();

	if ( ! empty( $locations[ $location ] ) && isset( $locations[ $data['slug'] ] ) ) {

		$menu        = get_term( $locations[ $data['slug'] ] );
		$menu_slug   = get_nav_menu_locations()->$menu->slug;
		$menu->items = wp_get_nav_menu_items( $menu_slug );
	}

	// Return object.
	return $menu;
}

/**
 * Setup rest_api_init.
 *
 * @since 1.0.0
 */
add_action( 'rest_api_init', function() {

	// Register root route.
	register_rest_route( 'wp-api-menus/v2', '/menus', array(
		'methods'  => 'GET',
		'callback' => 'acorn_theme_get_all_menus',
	) );

	// Register route object by id.
	register_rest_route( 'wp-api-menus/v2', '/menus/(?P<id>[a-zA-Z(-]+)', array(
		'methods'  => 'GET',
		'callback' => 'acorn_theme_get_menu_json_object_by_id',
	) );

	// Register route object by slug.
	register_rest_route( 'wp-api-menus/v2', '/menus/(?P<slug>[a-zA-Z(-]+)', array(
		'methods'  => 'GET',
		'callback' => 'acorn_theme_get_menu_json_object_by_slug',
	) );

} );
