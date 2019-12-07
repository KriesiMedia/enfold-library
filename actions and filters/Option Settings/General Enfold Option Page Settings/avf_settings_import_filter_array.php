<?php
/* 
 * In this file we provide code snippets that help you to create and add your own customized import theme settings button
 * 
 * @since 4.6.4
 */

/**
 * Add your button to the theme settings tab "Import/Export" before the Iconfont Manager button
 * 
 * The button is a frame that uses filter avf_settings_import_filter_array to add the actual filters
 * 
 * @param array $elements
 * @return array
 */
function my_option_page_data_init_buttons( array $elements )
{
	
	$button = array(
			'slug'		=> 'upload',
			'name' 		=> __( 'Import Theme Settings - add your title', 'avia_framework' ),
			'desc' 		=> __( "Upload a theme configuration file here to import settings - add your description", 'avia_framework' ),
			'id' 		=> 'config_file_upload_my_custom',
			'title' 	=> __( 'Upload Theme Settings File', 'avia_framework' ),
			'button' 	=> __( 'Insert Theme Settings', 'avia_framework' ),
			'trigger' 	=> 'av_config_file_insert',
			'std'	  	=> '',
			'file_extension' => 'txt',
			'file_type'	=> 'text/plain',
			'type' 		=> 'file_upload'     
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
			
			if( isset( $element['id'] ) && 'iconfont_upload' == $element['id'] )
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

apply_filters( 'avf_option_page_data_init', 'my_option_page_data_init_buttons', 10, 1 );