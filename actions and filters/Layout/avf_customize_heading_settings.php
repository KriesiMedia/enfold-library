<?php

/**
 * Allows to change the default <h1> - <h3> tags used by Enfold
 * 
 * @param array $args
 * @param string $context			where the filter is called from
 * @param array $extra_args			contains extra info depending on $context
 * @return array
 */
function my_avf_customize_heading_settings( array $args, $context, array $extra_args = array() )
{
	/**
	 * This is an example of the usage for "avia_sc_timeline"
	 */
	if( $context == 'avia_sc_timeline' )
	{
		$args['heading'] = 'h1';						//	change heading from h3 to h1
		$args['extra_class'] = 'my-timeline-class';		//	add an extra class for styling
	}
	
	/**
	 * This is an example of the usage for "avia_content_slider" and slider title
	 */
	if( 'avia_content_slider' == $context && is_array( $extra_args ) && in_array( 'slider_title', $extra_args ) )
	{
		$args['heading'] = 'h2';						//	change heading from h3 to h2
		$args['extra_class'] = 'my-extra-class';		//	add an extra class for styling
	}
	
	/**
	 * This is an example of the usage for "avia_content_slider" and slider title for the entries
	 */
	if( 'avia_content_slider' == $context && is_array( $extra_args ) && in_array( 'slider_entry', $extra_args ) )
	{
		$args['heading'] = 'h6';						//	change heading from h3 to h6
		$args['extra_class'] = 'my-extra-class';		//	add an extra class for styling
	}
	
	/**
	 * This is an example of the usage for "avia_sc_icon_box"
	 */
	if( $context == 'avia_sc_icon_box' )
	{
		$args['heading'] = 'h1';						//	change heading from h3 to h1
		$args['extra_class'] = 'my-icon-box-class';		//	add an extra class for styling
	}
	
	return $args;
}

add_filter( 'avf_customize_heading_settings', 'my_avf_customize_heading_settings', 10, 3 );


