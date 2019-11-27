<?php

/**
 * This file contains code snippets to add or alter tabs of the option page:	
 * 
 * Copy the required following snippets in functions.php and modify output to your need
 * 
 * @version 1.0.0
 * @requires Enfold 4.0.0
 */

/**
 * Example: Hide Google tab
 * 
 * @param array $avia_pages
 * @return array
 */
function my_custom_option_page_modify_tab( $avia_pages )
{
	$found = -1;
	
	foreach( $avia_pages as $index => $page ) 
	{
		if( 'google' == $page['slug'] )
		{
			$found = $index;
			break;
		}
	}
	
	if( $found >= 0 )
	{
		$page = $avia_pages[ $found ];
		
		/**
		 * add hidden to class to hide tab
		 */
		$page['class'] = ! empty( $page['class'] ) ? "{$page['class']} hidden" : ' hidden';
		
		$avia_pages[ $found ] = $page;
	}
	
	return $avia_pages;
}


add_filter( 'avf_option_page_init', 'my_custom_option_page_modify_tab', 10, 1 );


/**
 * Example: Add your tab after Blog Layout tab
 * 
 * @param array $avia_pages
 * @return array
 */
function my_custom_option_page_add_tab( $avia_pages )
{
	$slug = 'blog';
	
	$new_element = array( 
						'slug'		=> 'your_slug', 		
						'parent'	=> 'avia', 
						'icon'		=> 'new/note-write-7@3x.png', 			
						'title'		=>  __( 'Your Page', 'avia_framework' )
					);
	
	$found = -1;
	
	foreach( $avia_pages as $index => $page ) 
	{
		if( $slug == $page['slug'] )
		{
			$found = $index + 1;
			break;
		}
	}
	
	/**
	 * If slug not found, add at the end
	 */
	if( $found < 0 )
	{
		$avia_pages[] = $new_element;
	}
	else
	{
		$avia_pages = array_merge( array_slice( $avia_pages, 0, $found ), array( $new_element ), array_slice( $avia_pages, $found  ) );
	}
	
	return $avia_pages;
}

add_filter( 'avf_option_page_init', 'my_custom_option_page_add_tab', 10, 1 );