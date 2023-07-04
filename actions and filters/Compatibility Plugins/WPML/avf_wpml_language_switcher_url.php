<?php

/**
 * Filter the URL in language switcher flags
 * 
 * @since 4.5.6.1
 * @param string $url
 * @param string $lang_code
 * @param string $menu_position				'sub_menu' | 'main_menu'
 * @return string
 */
function my_wpml_language_switcher_url( $url, $lang_code, $menu_position )
{
	if( $lang_code == 'cs' )
	{
		$url = 'your_url';
	}
	
	return $url;
}

add_filter( 'avf_wpml_language_switcher_url', 'my_wpml_language_switcher_url', 10, 3 );

