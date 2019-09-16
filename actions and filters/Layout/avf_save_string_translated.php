<?php

/**
 * Allow to manipulate the translated string 
 * Intended to overrule sanitizations for custom id's and custom classes for ALB elements.
 *
 * @since 4.6.3
 * @author Günter
 * @param string $converted_string
 * @param string $string_to_convert
 * @param string $replace
 * @param string $fallback
 * @param string $context				'' | 'id' | 'class'	
 * @return string
 */
function custom_save_string_translated( $converted_string, $string_to_convert, $replace, $fallback, $context )
{
	//	Skip if not an id or class
	if( ! in_array( $context, array( 'id', 'class'	) ) )
	{
		return $converted_string;
	}
	
	//	Example: Return unmodified original class names
	if( 'class' == $context )
	{
		return $string_to_convert;
	}
	
	//	Example: Return unmodified original id values
	if( 'id' == $context )
	{
		return $string_to_convert;
	}
	
	return $converted_string;
}

add_filter( 'avf_save_string_translated', 'custom_save_string_translated', 10, 5 );
