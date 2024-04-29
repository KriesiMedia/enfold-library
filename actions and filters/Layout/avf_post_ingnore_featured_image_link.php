<?php
/**
 * Allows to ignore any link setting on featured image
 * @link https://kriesi.at/support/topic/remove-featured-image-expand-in-posts/
 *
 * Filter is called in context of ..\enfold\includes\loop-index.php, you can access all global WP variables inside a loop
 *
 * @since 5.7.1
 * @param boolean $ignore_image_links
 * @param array $current_post
 * @return boolean
 */
function custom_avf_post_ingnore_featured_image_link( $ignore_image_links = false, array $current_post = [] )
{
	//	e.g. available variables
	global $avia_config, $post_loop_count;

	//	e.g. remove link for post ID 125
	if( $current_post['the_id'] == 125 )
	{
		$ignore_image_links = true;
	}

	return $ignore_image_links;
}

add_filter( 'avf_post_ingnore_featured_image_link', 'custom_avf_post_ingnore_featured_image_link', 10, 2 );


