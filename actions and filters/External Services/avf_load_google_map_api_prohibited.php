<?php

/**
 * DEPRECATED WITH 4.6 - use theme option instead
 * ==============================================
 *
 *
 * Unconditionally exclude Google Maps from site
 * 
 * @param boolean $no_load
 * @return boolean
 */
function custom_no_load_google_map_api( $no_load )
{
	return true;
}

add_filter( 'avf_load_google_map_api_prohibited', 'custom_no_load_google_map_api', 10, 1 );

