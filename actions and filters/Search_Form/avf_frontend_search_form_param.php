<?php

/**
 *	Allows to modify the search parameters, 
 *		for example bbpress search_id needs to be 'bbp_search' instead of 's'. 
 *	you can also deactivate ajax search by setting ajax_disable to true
 * 
 * Put the following code in functions.php of child theme or parent theme
 */
add_filter('avf_frontend_search_form_param', my_frontend_search_form_param, 10, 1 );

function my_frontend_search_form_param( array $params )
{
	/**
	 * Following are the default parameters. Remove // and adjust to your values needed
	 */
//	$params['placeholder'] = __('Search','avia_framework');
//	$params['search_id'] = 's';
//	$params['form_action'] = home_url( '/' );
//	$params['ajax_disable'] = false;				//	true to disable ajax search
	
	return $params;
}
