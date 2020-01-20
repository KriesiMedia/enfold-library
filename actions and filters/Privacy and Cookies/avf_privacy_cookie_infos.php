<?php

/* 
 * Allows to add info for custom cookies or cookies not handled by Enfold
 * 
 * @since 4.5.7.2
 * @param array $infos
 * @param av_privacy_class $this
 * @return array
 */

function my_custom_privacy_cookie_infos( $infos, $av_privacy_object )
{
	$user = get_current_user_id();
	
	$infos['wp-settings-' . $user ] = __( 'A WP settings cookie', 'avia_framework' );
			
	return $infos;
}


add_filter( 'avf_privacy_cookie_infos', 'my_custom_privacy_cookie_infos', 10, 2 );