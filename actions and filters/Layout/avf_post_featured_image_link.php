<?php
/*
 * Change default theme image
 *
 * @param string $image_link
 * @param array $current_post
 * @param string $size
 * @return string
 */
function custom_post_featured_image_link( $image_link, array $current_post, $size )
{
	//	Add logic to change image size
	//	
	//	e.g.
	if( is_tag() )
	{
		$image_link = get_the_post_thumbnail( $current_post['the_id'], 'medium' );
	}

	return $image_link;
}

add_filter( 'avf_post_featured_image_link', 'custom_post_featured_image_link', 10, 3 );
