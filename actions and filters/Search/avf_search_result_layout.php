<?

/**
 *
 * Use the "avf_search_result_layout" filter to display the search results in Grid layout
 * Related thread: https://kriesi.at/support/topic/search-products-pages-and-posts-enfold/
 *
 * Added in 5.6
 **/

/** Add the following snippet in the functions.php file. **/

add_filter('avf_search_result_layout', 'grid_search_result_layout');
function grid_search_result_layout(){
  
	return 'grid';
  
}
