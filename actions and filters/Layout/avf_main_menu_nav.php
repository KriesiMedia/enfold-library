<?php

/**
 * Allow to change or remove the main menu depending on pages
 * ==========================================================
 * 
 * see enfold\includes\helper-main-menu.php around line 151
 * 
 * 
 * Copy the following snippet in functions.php and modify settings to your need
 * 
 * @param string $main_nav							
 * @return string								
 * 
 * @version 1.0.0
 * @since Enfold 4.1.3
 */
function custom_avf_main_menu_nav( $main_nav )
{
	/**
	 * Example: Remove the nav for page id 22
	 *			=============================
	 */
	
	/**
	 * Use the following code if you need closer information about the currently queried object
	 */
//	$object = get_queried_object();
	$id     = get_queried_object_id();
	
//	if( $object instanceof WP_Post && ( $object->post_type == 'page' ) && ( $id == 12775 ) )
//	{
//		$main_nav = '';
//	}
	
	if( is_page()  && ( $id == 22 ) )
	{
		$main_nav = '';
	}

	return $main_nav;
}

add_filter('avf_main_menu_nav', 'custom_avf_main_menu_nav', 10, 1 );