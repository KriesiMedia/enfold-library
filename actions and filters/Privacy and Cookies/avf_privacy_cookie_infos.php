<?php

/* 
 * Allows to add info for 3rd party cookies not handled by Enfold.
 * 
 * To display cookie info in default privacy modal popup window add the following to functions.php:
 * 
 * add_theme_support( 'avia_privacy_show_cookie_info' );
 * 
 * or use shortcode wherever you want to display it:
 * 
 * [av_privacy_cookie_info]
 * 
 * To allow wildcards at beginning or end use *:
 *		wp-settings-time-*		will get all cookies starting with wp-settings-time-
 *		*settings*				will get all cookies containing settings
 *		*-settings-time			will get all cookies ending with -settings-time
 * 
 * The following lines show you examples how to use wildcards and this filter
 * 
 * @since 4.5.7.2
 * @param array $infos
 * @param av_privacy_class $av_privacy_object
 * @return array
 */

function my_custom_privacy_cookie_infos( $infos, $av_privacy_object )
{
	$infos['your_cookie_name' ] = __( 'An exactly match', 'avia_framework' );
	
	$user = get_current_user_id();
	
//	$infos['wp-settings-time-' . $user ] = __( 'A WP settings time cookie', 'avia_framework' );
//	$infos['wp-settings-' . $user ] = __( 'A WP settings cookie', 'avia_framework' );
	
	$infos['your_cookie_name*'] =  __( 'A cookie ends with', 'avia_framework' );
	$infos['*your_cookie_name'] =  __( 'A cookie starts with', 'avia_framework' );
	$infos['*your_cookie_name*'] =  __( 'A cookie contains', 'avia_framework' );
			
	return $infos;
}


add_filter( 'avf_privacy_cookie_infos', 'my_custom_privacy_cookie_infos', 10, 2 );
