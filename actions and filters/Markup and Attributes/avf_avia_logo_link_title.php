<?php
/**
 * Return a complete modified title="" attribute string
 * If you want to use it as tooltip you must REMOVE title attribute from image
 * ( e.g. use filter 'avf_logo_final_output' )
 *
 * @since 5.6.6
 * @param string $link_title
 * @param string $title
 * @param string $alt
 * @return string
 */
function custom_avf_avia_logo_link_title( $link_title, $title, $alt )
{
	if( ! $some_condition )
	{
		return $link_title;
	}

	return 'title="your custom link title"';
}

add_filter( 'avf_avia_logo_link_title', 'custom_avf_avia_logo_link_title', 10, 3 );

