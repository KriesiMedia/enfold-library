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
 * @param array $sizes							
 * @return array								
 * 
 * @version 1.0.0
 * @since Enfold 3.0  ??
 */
function custom_modified_thumb_sizes( $sizes )
{
	/**
	 * Example: Change sizes for 'masonry'
	 * 
	 * Remove // at beginning of the line and make your changes.
	 * 
	 */
						//	set to true to crop images (= default value if not set)
	$size['masonry'] = array('width'=>1024, 'height'=>1024, 'crop' => false);
				
	return $sizes;
}

add_filter('avf_modify_thumb_size', 'custom_modified_thumb_sizes', 10, 1 );
