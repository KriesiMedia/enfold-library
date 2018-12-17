<?php

/**
 * Modify the ajax search query params by simply adding or removing needed values from array
 * 
 */

/** Add the following snippet in the functions.php file. **/

add_filter( 'avf_ajax_search_query', 'handler_avf_ajax_search_query', 10, 1 );

/**
 * Modifies the query string for ajax search.
 * 
 * @param string $query_string
 * @return string|array
 */
function handler_avf_ajax_search_exclude_pages( $query_string )
{
	$defaults = array();

	/**
	 * $query now contains all already set parameters in an array
	 */
	$query = wp_parse_args( $query_string, $defaults );

	/**
	 * Now add your parameters
	 * 
	 * e.g.
	 */
//	$query['post__not_in'] = array( 1015, 1020 );

	/**
	 * Return the modified array
	 */
	return $query;
}

