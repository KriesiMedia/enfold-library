<?php
/**
 * Change default ajax search results when using relevanssi
 *
 * @link https://kriesi.at/support/topic/enfold-relevanssi-ajax-dropdown-limit/
 *
 * @since 5.6.6
 * @param null $post_count
 * @param string $search_query
 * @param array $search_parameters
 * @param array $defaults
 * @return null|int                  return null to use theme default
 */
function my_avf_relevanssi_ajax_search( $post_count, $search_query, $search_parameters, $defaults )
{
	//	change to e.g. 25
	return 25;
}

add_filter( 'avf_relevanssi_ajax_search', 'my_avf_relevanssi_ajax_search', 10, 4 );
