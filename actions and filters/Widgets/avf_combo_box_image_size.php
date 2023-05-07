<?php

/**
 * Allows to change image size in widget Latest News
 *
 * @since 5.6.1
 * @param string $image_size
 * @param array $query_args
 * @param array $widget_args
 * @param array $instance
 * @return string
 */
function my_avf_combo_box_image_size( $image_size, array $query_args = [], array $widget_args = [], array $instance = [] )
{
	if( $widget_args['widget_id'] == 'avia_combo_widget-2' )
	{
		$image_size = 'square';
	}

	return $image_size;
}

add_filter( 'avf_combo_box_image_size', 'my_avf_combo_box_image_size', 10, 4 );

