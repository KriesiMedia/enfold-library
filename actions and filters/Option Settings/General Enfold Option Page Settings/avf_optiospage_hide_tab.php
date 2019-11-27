<?php

/**
 * This file contains code snippet to hide a tab on the option page.
 * 
 * Copy the required following snippets in functions.php and modify output to your need
 * 
 * @version 1.0.0
 * @requires Enfold 4.6.4
 */

/**
 * Hide a tab specified in $context
 * 
 * @since 4.6.4
 * @param boolean $hide 
 * @param string $context				only 'updates_theme_tab' supported
 * @retrun boolean						anything except false to hide the tab in context
 */
function my_optiospage_hide_tab( $hide, $context )
{
	if( 'updates_theme_tab' == $context )
	{
		$hide = true;
	}
	
	return $hide;
}

add_filter( 'avf_optiospage_hide_tab', 'my_optiospage_hide_tab', 10, 2 );
