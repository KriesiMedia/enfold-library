<?php
/*
 * Modify the post meta output array.
 * Array is filled only with values that are selected in Theme Options -> Blog Layout -> Blog meta elements.
 *
 * You must set correct HTML for each key. Seperators are added before output between existing meta data.
 *
 * Array keys:
 *		'author'
 *		'comments'
 *		'categories'
 *		'date'
 *		'html-info'
 *		'tag'
 *
 *
 *
 * @since 4.8.7.2
 * @param array $meta_info
 * @param string $context		'loop-index' | 'loop-author' | 'loop-search'
 * @return array
 */
function avf_post_metadata_array( array $meta_info, $context )
{
	//	Add logic to check if you want to change
	$no_change = true;

	if( $no_change )
	{
		return $meta_info;
	}

	//	e.g. modify date output
	$meta_time  = '<span class="date-container minor-meta updated">';
	$meta_time .=		__( 'Created: ', 'avia_framework' ) . get_the_time( get_option( 'date_format' ) );
	$meta_time .= '</span>';

	$meta_info['date'] = $meta_time;

	return $meta_info;
}

add_filter( 'avf_post_metadata_array', 'custom_avf_post_metadata_array', 10, 2 );
