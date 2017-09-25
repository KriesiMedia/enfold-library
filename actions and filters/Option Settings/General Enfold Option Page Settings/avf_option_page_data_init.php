<?php
/**
 * This file contains code snippets to add or alter elements of the option page:	
 * 
 * Copy the required following snippets in functions.php and modify output to your need
 * 
 * @param array $avia_elements
 * @version 1.0.1
 * @requires Enfold 4.0.0
 */

/**
 * Modify an element
 * 
 * @param array $avia_elements
 * @return array
 */
function my_avf_option_page_data_change_elements( array $avia_elements = array() )
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

add_filter( 'avf_option_page_data_init', 'my_avf_option_page_data_change_elements', 10, 1 );


/**
 * Add an element
 * 
 * @param array $avia_elements
 * @return array
 */
function my_avf_option_page_data_add_elements( array $avia_elements = array() )
{
	/**
	 * Example: Add after "Specify a favicon for your site" array.
	 *			Leave blank to add at the end
	 */
	$slug = "avia";
	$id = 'favicon';
	
	/**
	 * Define your new element to add (see enfold\includes\admin\register-admin-options.php what elements are possible)
	 */
	$new_element =	array(
					"slug"	=> "avia",
					"name" 	=> __( "Apple Icon", 'avia_framework' ),
					"desc" 	=> __( "Upload an Apple Icon to use", 'avia_framework' ),
					"id" 	=> "avia_appleicon",
					"type" 	=> "upload",
					"label"	=> __( "Use Image as Apple Icon", 'avia_framework' ) );
	
	$found = false;
	$index = 0;
	
	/*
	 * Find index of element to change
	 */
	foreach( $avia_elements as $key => $element )
	{
		$index++;
		
		if( isset( $element['id'] ) &&  ( $element['id'] == $id ) && isset( $element['slug'] ) && ( $element['slug'] == $slug ) )
		{
			$found = true;
			break;
		}
	}
	
	/**
	 * If key not found, add at the end
	 */
	if( ! $found )
	{
		$avia_elements[] = $new_element;
	}
	else
	{
		$avia_elements = array_merge( array_slice( $avia_elements, 0, $index ), array( $new_element ), array_slice( $avia_elements, $index  ) );
	}
	
	/**
	 * Return modified array
	 */
	return $avia_elements;
}

add_filter( 'avf_option_page_data_init', 'my_avf_option_page_data_add_elements', 10, 1 );

