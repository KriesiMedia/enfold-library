<?php
/**
 * Change one or more options in the option page
 * 
 * Copy the following snippet in functions.php and modify output to your need
 * 
 * @param array $avia_elements
 * @version 1.0.0
 * @requires Enfold 4.0.0
 */
function my_avf_option_page_data_init( array $avia_elements = array() )
{
	/**
	 * Example: Customize "Header Custom Height" array
	 */
	$slug = "header";
	$id = 'header_custom_size';
	
	$index = -1;
	
	/*
	 * Find index of element to change
	 */
	foreach( $avia_elements as $key => $element )
	{
		if( isset( $element['id'] ) &&  ( $element['id'] == $id ) && isset( $element['slug'] ) && ( $element['slug'] == $slug ) )
		{
			$index = $key;
			break;
		}
	}
	
	/**
	 * If key not found, return unmodified array
	 */
	if( $index < 0 )
	{
		return $avia_elements;
	}
	
	/**
	 * Make your customizations
	 */
	$customsize = array();
	for ($x = 45; $x <= 500; $x++ )
	{ 
		$customsize[ $x.'px' ] = $x; 
	}
	
	$avia_elements[ $index ]['subtype'] = $customsize;
	
	/**
	 * Return modified array
	 */
	return $avia_elements;
}

add_filter( 'avf_option_page_data_init', 'my_avf_option_page_data_init', 10, 1 );