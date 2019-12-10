<?php
/* 
 * In this file we provide code snippets that help you to create and add your own customized import theme settings button
 * 
 * @since 4.6.4
 */

/**
 * Add your button to the theme settings tab "Import/Export" after the default "Import Theme Settings File" button
 * 
 * The first button is a frame that uses filter avf_settings_import_filter_array to add the actual filters
 * The second adds the filters directly - no need for avf_settings_import_filter_array
 * 
 * @param array $elements
 * @return array
 */
function my_option_page_data_init_buttons( array $elements )
{
	/**
	 * e.g. add 2 custom buttons:
	 * 
	 * Button 1: add filters with php
	 * Button 2: add filters directly here
	 */
	$button = array(
					array(
						'slug'	=> 'upload',
						'name' 	=> __( 'Custom Reset Options 1 - add your title', 'avia_framework' ),
						'desc' 	=> __( 'Click the button to reset selected options to theme default values - add your description', 'avia_framework' ),
						'id' 	=> 'reset_selected_button_my_custom_button_1',
						'type' 	=> 'reset_selected_button',
					),
		
					array(
						'slug'	=> 'upload',
						'name' 	=> __( 'Custom Reset Options 2 skip Quick CSS - add your title', 'avia_framework' ),
						'desc' 	=> __( 'Click the button to reset selected options to theme default values - keep your Quick CSS - add your description', 'avia_framework' ),
						'id' 	=> 'reset_selected_button_my_custom_button_2',
						'type' 	=> 'reset_selected_button',
						'skip_values'	=> 'avia:quick_css',
						'filter_tabs'	=> 'avia:cookie'
					),
		
		);
	
	$index = false;
	$index_fallback = false;
	
	/**
	 * Find index where to insert button
	 */
	foreach( $elements as $key => $element ) 
	{
		if( isset( $element['slug'] ) && 'upload' == $element['slug'] )
		{
			if( ! $index_fallback )
			{
				$index_fallback = $key;
			}
			
			if( isset( $element['id'] ) && 'config_file_upload' == $element['id'] )
			{
				$index = $key;
				break;
			}
		}
	}
	
	if( false === $index && false === $index_fallback )
	{
		//	This might break output !!!
		$elements[] = $button;
		return $elements;
	}
	
	$index = false !== $index ? $index : $index_fallback;
	
	$elements = array_merge( array_slice( $elements, 0, $index + 1 ), $button, array_slice( $elements, $index + 1 ) );
	
	return $elements;
}

add_filter( 'avf_option_page_data_init', 'my_option_page_data_init_buttons', 10, 1 );

/**
 * Modify the filter array to filter or skip settings
 * 
 * @since 4.6.4
 * @param.array $filter
 * @param string $button_id
 * @return array
 */
function my_avf_settings_reset_options_filter_array( $filter, $button_id )
{
	/**
	 * Define an array of all your buttons
	 */
	$my_ids = array( 'reset_selected_button_my_custom_button_1' );
	
	/**
	 * Check if it is a button we need to add our filters
	 */
	if( ! in_array( $button_id, $my_ids ) )
	{
		return $filter;
	}
	
	/**
	 * Possible keys for filter
	 */
//	$filter_keys = array( 'filter_tabs', 'filter_values', 'skip_tabs', 'skip_values' );
	
	/**
	 * Add your filters depending on $button_id
	 * 
	 * Take care that 3-rd party might have added something already
	 */
	switch( $button_id )
	{
		case 'reset_selected_button_my_custom_button_1':
			//	Only import "Blog Layout" and "Privacy and Cookies" tab
			$filter_tabs = 'avia:blog,avia:cookie';
			$filter['filter_tabs'] = isset( $filter['filter_tabs'] ) && ! empty( $filter['filter_tabs'] ) ? trim( $filter['filter_tabs'] ) . ',' . $filter_tabs : $filter_tabs;
			
			//	Keep options "Quick CSS" and "CSS file merging and compression"
			$skip_values = 'avia:quick_css,avia:merge_css';
			$filter['skip_values'] = isset( $filter['skip_values'] ) && ! empty( $filter['skip_values'] ) ? trim( $filter['skip_values'] ) . ',' . $skip_values : $skip_values;
			
			break;
		case 'reset_selected_button_my_custom_button_xx':
			//	add your filters for this button
			break;
	}
	
	return $filter;
}
				
add_filter( 'avf_settings_reset_options_filter_array', 'my_avf_settings_reset_options_filter_array', 10, 3 );

