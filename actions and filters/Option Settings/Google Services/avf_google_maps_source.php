<?php

/**
 * Set new values for accessing the Google Maps API.
 *                                  ================
 * 
 * Needed, because Google updates the version number and this results in a retired version warning in the js console.
 * 
 * Add the content of this file to functions.php of the child theme (if used) or the parent theme.
 * 
 * Remove // at the beginning ot the line you want to modify
 * 
 * @param array $api_src
 * @return array
 */
function set_custom_google_maps_source( array $api_src )
{
	/*
	 * Update the source url for the API, without any parameters after ?
	 * e.g. $api_src['source'] = 'https://maps.googleapis.com/maps/api/js';
	 */
//	$api_src['source'] = '';
	
	/**
	 * Update the version number to the current valid version 
	 * see 
	 * 
	 * e.g.  $api_src['version'] = '3.30';
	 */
//	 $api_src['version'] = '';
	 
	return $api_src;
}

add_filter( 'avf_google_maps_source', 'set_custom_google_maps_source', 10, 1 );

