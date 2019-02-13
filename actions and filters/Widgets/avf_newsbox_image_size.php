<?php


/**
 * Allows to change image size in widget Latest News
 * 
 * @param string $image_size
 * @param array $args
 * @param array $instance
 * @return string
 */
function my_avf_newsbox_image_size( $image_size, array $args, array $instance )
{
	if( $args['widget_id'] == 'newsbox-2' )
	{
		$image_size = 'square';
	}
	
	return $image_size;
}

add_filter( 'avf_newsbox_image_size', 'my_avf_newsbox_image_size', 10, 3 );

