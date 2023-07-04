/**
 * WPML Ajax Search Query
 *
 * Use the "avf_ajax_search_query" filter to adjust the language parameter in the ajax search query when using the WPML plugin.
 * Related thread: http://www.kriesi.at/support/topic/enfold-portfolio-grid-with-polylang/
 *
 **/

/** Add the following snippet in the functions.php file. **/

// Ajax Search Query & WPML
function avf_enfold_wpml_ajax_search_query($search_parameters)
{
	$language = ICL_LANGUAGE_CODE;
	parse_str($search_parameters, $params);
	$params['lang'] = $language;
	$search_parameters = http_build_query($params);
	
	return $search_parameters;
}

add_filter('avf_ajax_search_query', 'avf_enfold_wpml_ajax_search_query', 10, 1);
