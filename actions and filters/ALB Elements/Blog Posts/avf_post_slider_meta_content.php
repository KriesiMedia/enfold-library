<?php

/**
* Filter to change meta content on blog posts element e.g. to display autor name 
* 
* @since 4.8.7.2
* @param string $meta_content
* @param WP_Post $entry
* @param int $index
* @param array $this->atts
*/
add_filter('avf_post_slider_meta_content', 'new_avf_post_slider_meta_content', 10, 2);

function new_avf_post_slider_meta_content( $meta_content, $entry ){

	$author = get_the_author_meta( 'display_name', $entry->post_author );

	$meta_content = "<span class='slide-meta-author updated'>" . $author ."</span>";

	return $meta_content;

}
