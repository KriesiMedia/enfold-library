<?php

/**
 * WARNING: This is a filter for developers only. Be careful making modifications as this might break the output,
 * 
 * @since 4.6.4 	$context added
 * @param array $header
 * @param string $context		'setting_header' | 'setting_sidebar'
 * @return array
 */
function my_header_setting_filter( $header, $context )
{
	/**
	 * Example:
	 * 
	 * To modify the id for an alternate menu to 51:
	 */
	$header['alternate_menu'] = 51;
	
	return $header;
}

add_filter( 'avf_header_setting_filter', 'my_header_setting_filter', 10, 2 );
