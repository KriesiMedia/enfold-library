<?php
/**
 * Add or modify your own predefined palette colors to the popup colorpicker in ALB elements
 * 
 * @since 4.5.7.2
 * @param array $colors
 * @return array
 */
function custom_colorpicker_colors( array $colors )
{
	/**
	 * These are the default colors - change or extend them as needed
	 * Keep in mind that the more colors you use the smaller the boxes will be
	 */
	$colors = array( '#000000', '#ffffff', '#B02B2C', '#edae44', '#eeee22', '#83a846', '#7bb0e7', '#745f7e', '#5f8789', '#d65799', '#4ecac2' );
	
	return $colors;
}

	
add_filter( 'avf_colorpicker_colors', 'custom_colorpicker_colors', 10, 1 );


