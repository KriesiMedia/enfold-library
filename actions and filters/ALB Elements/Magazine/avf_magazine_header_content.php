<?php

/**
* Filter to change the order of Magazine element header content e.g. to display author name below title
* 
* @since 4.8.7.2
* @param array $header_content
* @param WP_Post $entry
* @return array
*/
add_filter('avf_magazine_header_content', 'new_avf_magazine_header_content', 10, 2);

function new_avf_magazine_header_content($header_content, $entry){
	
	$author_field = $header_content['author'];

	unset( $header_content['author'] );
	
	$header_content['author'] = $author_field;
	
	return $header_content;
	
}
