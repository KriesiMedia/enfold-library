<?php

/**
 * Allow to change default behaviour of browsers when loading external fonts
 * https://developers.google.com/web/updates/2016/02/font-display
 * 
 * @param string $font_display
 * @param string $font_name
 * @return string					auto | block | swap | fallback | optional
 */
function my_custom_font_display( $font_display, $font_name )
{
	//	Check for a font and change the default theme setting
	if( 'entypo-fontello' == $font_name )
	{
		return 'block';
	}
	
	return $font_display;
}
	
add_filter( 'avf_font_display', 'my_custom_font_display', 10, 2 );