<?php

/**
 * Allow to overwrite the option setting for using the standard lightbox
 * Make sure to return 'disabled' to deactivate the standard lightbox - all checks are done against this string
 * 
 * @param string $use_standard_lightbox			'lightbox_active' | 'disabled'
 * @return string								'lightbox_active' | 'disabled'
 */
function my_filter_use_standard_lightbox( $use_standard_lightbox )
{
//	$use_standard_lightbox = 'disabled';
	
	return $use_standard_lightbox;
}

add_filter( 'avf_use_standard_lightbox', 'my_filter_use_standard_lightbox', 10, 1 );

