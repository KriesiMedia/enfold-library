<?php

/**
 * This is an example only
 * =======================
 * 
 * @param array $args
 * @param string $context
 * @param array $extra_args
 * @return array
 */
function my_avf_customize_heading_settings( array $args, $context, array $extra_args = array() )
{
	
	if( $context == 'avia_sc_timeline' )
	{
		$args = array(
								'heading'		=> 'h1',
								'extra_class'	=> ''
							);
	}
	return $args;
}

add_filter( 'avf_customize_heading_settings', 'my_avf_customize_heading_settings', 10, 3 );


