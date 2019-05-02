<?php

/**
 * Use the following as a frame for customization
 * 
 * @since 4.5.6
 * @param array $settings
 * @return array
 */
function my_avf_post_nav_settings( array $settings )
{
	/**
	 * Skips e.g. pages.
	 * 
	 * get_previous_post() and get_next_post does not support hierarchical post types by default.
	 * You need to implement your own logic.
	 */
	if( true === $settings['is_hierarchical'] )
	{
		$settings['skip_output'] = true;
		return $settings;
	}
	
	/**
	 * Limit post types you want to have navigation
	 */
	if( ! in_array( $settings['type'], array( 'post', 'portfolio' ) ) )
	{
		$settings['skip_output'] = true;
		return $settings;
	}
		
	/**
	 * Add other settings
	 */
	$settings['same_category'] = true;
	$settings['is_fullwidth'] = false;
	
	/**
	 * Make sure we show navigation
	 */
	$settings['skip_output'] = false;
	
	return $settings;
}

add_filter( 'avf_post_nav_settings', 'my_avf_post_nav_settings', 10, 1 );
