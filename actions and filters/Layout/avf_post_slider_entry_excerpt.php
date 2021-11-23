<?php
/**
 * Modify length of excerpt
 *
 * @param string $excerpt
 * @param string $prepare_excerpt
 * @param string $read_more
 * @param WP_Post $entry
 * @param avia_post_slider $obj_post_slider			added with 4.8.7.2 !!!
 * @return string
 */
function custom_post_slider_entry_excerpt( $excerpt, $prepare_excerpt, $read_more, $entry, $obj_post_slider = null )
{
	//	add logic to check for modify
	$modify = true;

	if( $modify )
	{
		//	reduce length of excerpt
		$new_excerpt_length = 50;
		$excerpt = substr( $excerpt, 0, $new_excerpt_length ) . $read_more;
	}

	return $excerpt;
}

add_filter( 'avf_post_slider_entry_excerpt', 'custom_post_slider_entry_excerpt', 10, 5 );
