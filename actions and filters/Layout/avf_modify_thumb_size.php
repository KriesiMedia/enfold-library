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
 * Copy the following snippets in functions.php and modify to your needs
 * 
 * @param array $sizes							
 * @return array								
 * 
 * @version 1.1.0
 * @since Enfold 3.0  ??
 */

/**
 * Add or modify image sizes
 * 
 * @param array $sizes
 * @return array
 */
function custom_modified_thumb_sizes( array $sizes )
{
	/**
	 * Example: Change sizes for 'masonry'
	 * 
	 */
						//	set to true to crop images (= default value if not set)
	$sizes['masonry'] = array( 'width' => 1024, 'height' => 1024, 'crop' => false );
	
	/**
	 * Example: Add new size for 'my_custom_size'
	 */
	$sizes['my_custom_size'] = array( 'width' => 730, 'height' => 730, 'crop' => true );
	
	return $sizes;
}

/**
 * Add or remove selectable image sizes
 * 
 * @since 4.7.5.1
 * @param array $selectableImgSize
 * @param array $imgSizes
 * @return array
 */
function custom_modify_selectable_image_sizes( array $selectableImgSize, array $imgSizes ) 
{
	/**
	 * Example: Add my_custom_size registered before
	 */
	$selectableImgSize['my_custom_size'] = 'My custom size';
	
	/**
	 * Example: Remove an image size 
	 */
	unset( $selectableImgSize['extra_large'] );
	
	return $selectableImgSize;
}

/**
 * Add additional human readable image sizes (prepared in case we allow to add custom image sizes by theme)
 * 
 * @since 4.7.5.1
 * @param array $readableImgSizes
 * @param array $imgSizes
 * @return array
 */
function custom_modify_readable_image_sizes( array $readableImgSizes, array $imgSizes ) 
{
	/**
	 * Example: Add my_custom_size registered before
	 */
	$readableImgSizes['my_custom_size'] = 'My custom size';
	
	return $readableImgSizes;
}

add_filter( 'avf_modify_thumb_size', 'custom_modified_thumb_sizes', 10, 1 );
add_filter( 'avf_modify_selectable_image_sizes', 'custom_modify_selectable_image_sizes', 10, 2 );
add_filter( 'avf_modify_readable_image_sizes', 'custom_modify_readable_image_sizes', 10, 2 );
