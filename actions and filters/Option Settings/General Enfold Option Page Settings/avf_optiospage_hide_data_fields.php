<?php

/**
 * This file contains code snippet to hide a specific data field on the option page.
 * 
 * Copy the required following snippets in functions.php and modify output to your need
 * 
 * @version 1.0.0
 * @requires Enfold 4.6.4
 */

/**
 * Hide a data field specified in $context
 * 
 * Example: Hide tab for non admins
 * 
 * @since 4.6.4
 * @param boolean $hide 
 * @param string $context				only 'updates_envato_token' supported
 * @retrun boolean						anything except false to hide the tab in context
 */
function my_optiospage_hide_data_fields( $hide, $context )
{
	if( 'updates_envato_token' == $context )
	{
		if( ! current_user_can( 'manage_options' ) )
		{
			$hide = true;
		}
	}
	
	return $hide;
}

add_filter( 'avf_optiospage_hide_data_fields', 'my_optiospage_hide_data_fields', 10, 2 );

