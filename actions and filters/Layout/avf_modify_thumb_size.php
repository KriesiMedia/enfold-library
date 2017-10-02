<?php

/**
 * Allow to change or add definition of themes registered image sizes
 * ==================================================================
 * 
 * see enfold\functions.php around line 146ff for theme defined image sizes
 * 
 * You need to regenerate the WP generated thumbnails with a plugin like 
 * 
 * https://wordpress.org/plugins/regenerate-thumbnails/
 * https://de.wordpress.org/plugins/regenerate-thumbnails/
 * 
 * when you modify image sizes.
 * 
 * 
 * Copy the following snippet in functions.php and modify settings to your need
 * 
 * @param array $avia_config							
 * @return array								
 * 
 * @version 1.0.0
 * @since Enfold 3.0  ??
 */
function custom_modified_thumb_sizes( $avia_config )
{
	/**
	 * Example: Change sizes for 'masonry'
	 */
//	$avia_config['masonry']['width'] = 705;
//	$avia_config['masonry']['height'] = 705;
//	$avia_config['masonry']['crop'] = false;			//	set to true to crop images (= default value if not set)
	
	return $avia_config;
}

add_filter('avf_modify_thumb_size', 'custom_modified_thumb_sizes', 10, 1 );