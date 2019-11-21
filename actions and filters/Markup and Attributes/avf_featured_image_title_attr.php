<?php

/* 
 * Allows to change the title attribute text for the featured image. 
 * If '' is returned, then no title attribute is added.
 * 
 * @since 4.6.4
 * @param string $title_attr
 * @param string $context				'loop_index'
 * @param WP_Post $thumb_post
 */
function custom_avf_featured_image_title_attr( $title_attr, $context, WP_Post $thumb_post )
{
	//	return "Caption" from media gallery
//	$title_attr = $thumb_post->post_excerpt;
	
	//	return "Title" from media gallery
//	$title_attr = $thumb_post->post_title;
	
	//	return "Description" from media gallery
//	$title_attr = $thumb_post->post_content;
	
	//	return " Alternative Text" from media gallery
//	$title_attr = get_post_meta( $thumb_post->ID, '_wp_attachment_image_alt', true );
	
	return $title_attr;
}

add_filter( 'avf_featured_image_title_attr', 'custom_avf_featured_image_title_attr', 10, 3 );

