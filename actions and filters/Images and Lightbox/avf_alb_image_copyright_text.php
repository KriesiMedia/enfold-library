<?php

/**
 * Filter Copyright text e.g. to allow HTML tag
 * 
* @since 4.8.6.3
* @param string $copyright_text_escaped
* @param string $copyright_text
* @return string
*/
add_filter('avf_alb_image_copyright_text', 'new_avf_alb_image_copyright_text');

function new_avf_alb_image_copyright_text($copyright_text)
{
  
	return html_entity_decode($copyright_text);
  
}
