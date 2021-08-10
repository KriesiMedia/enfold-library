<?php
/**
 * Filter to skip css file generation.
 * You can add logic to skip for certain pages/posts only.
 * 
 * @since 4.8.6.1
 * @param boolean $create
 * @return boolean					true | false or anything else to skip generation of css file
 */
function custom_avf_post_css_create_file( $create )
{
	return false;
}

add_filter( 'avf_post_css_create_file', 'custom_avf_post_css_create_file', 10, 1 );

