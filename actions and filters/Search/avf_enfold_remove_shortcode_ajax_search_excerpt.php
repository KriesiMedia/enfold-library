/**
 * AJAX Search Excerpt 
 *
 * Use the "avf_ajax_search_excerpt" filter to remove shortcodes in the ajax search excerpt.
 * Related thread: http://www.kriesi.at/support/topic/search-results-preview-showing-shortcode/
 *
 **/

/** Add the following snippet in the functions.php file. **/


function avf_enfold_remove_shortcode_ajax_search_excerpt( $excerpt ) 
{
	preg_match_all( "^\[(.*?)\]^", $excerpt, $matches, PREG_PATTERN_ORDER );

	$excerpt = str_replace( $matches[0], '', $excerpt );
	return $excerpt;
}

add_filter( 'avf_ajax_search_excerpt', 'avf_enfold_remove_shortcode_ajax_search_excerpt', 10, 1 );
