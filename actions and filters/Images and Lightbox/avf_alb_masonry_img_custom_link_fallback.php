<?php

/**
 * Allow a fallback to behaviour up to 4.7.6.3 where a set $custom_url was used regardess of option selection.
 * This changed in 4.7.6.4 where it is possible to use a custom_url for an image and select lightbox.
 * 
 * @since 4.7.6.5
 * @param boolean
 * @param array $loop
 * @param string $key
 * @param avia_masonry $object
 * @return false|mixed				anything except false will activate the fallback
 */
function custom_avf_alb_masonry_img_custom_link_fallback( $fallback, $loop, $key, $object )
{
	// add your conditions to change $fallback
	
	//	$fallback = true;
	
	return $fallback;
}

add_filter( 'avf_alb_masonry_img_custom_link_fallback', 'custom_avf_alb_masonry_img_custom_link_fallback', 10, 4 );