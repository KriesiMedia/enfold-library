<?php
/**
 * Return a complete modified aria-label="" attribute string
 *
 * @since 5.6.6
 * @param string $aria
 * @return string
 */
function custom_avf_avia_logo_link_aria( $aria )
{
	if( ! $some_condition )
	{
		return $aria;
	}

	return 'aria-label="your custom aria label"';
}

add_filter( 'avf_avia_logo_link_aria', 'custom_avf_avia_logo_link_aria', 10, 1 );


