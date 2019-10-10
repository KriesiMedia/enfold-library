<?php

/* 
 * Returns option settings for developer options.
 * Allows to distinguish settings for individual input fields.
 * This implementation was used prior 4.6.4 where each input field had an own option
 * 
 * @since 4.5.7.2
 * @param string $value				
 * @param string $setting			
 * @param string $option_value		'show' | 'hide'
 * @return string|false				'deactivate' = hide and ignore input values  (is removed from options)
 *									'hide'       = hide input fields but use values
 *									'developer_options' | 'developer_id_attribute' | 'developer_seo_heading_tags' | 'developer_aria_label'       
 *									              = show input fields and use values
 */
function custom_alb_get_developer_settings( $value, $setting, $option_value )
{
	
	switch( $setting )
	{
		case 'custom_css':
			/**
			 * allowed return values
			 */
//			$value = 'developer_options';
//			$value = 'hide';
//			$value = 'deactivate';
			
			break;
		case 'custom_id':
			/**
			 * allowed return values
			 */
//			$value = 'developer_id_attribute';
//			$value = 'hide';
//			$value = 'deactivate';
			
			break;
		case 'heading_tags':
			/**
			 * allowed return values
			 */
//			$value = 'developer_seo_heading_tags';
//			$value = 'hide';
//			$value = 'deactivate';
			
			break;
		case 'aria_label':
			/**
			 * allowed return values
			 */
//			$value = 'developer_aria_label';
//			$value = 'hide';
//			$value = 'deactivate';
			
			break;
		case 'alb_desc_id':
			/**
			 * allowed return values
			 */
//			$value = 'developer_alb_desc_id';
//			$value = 'hide';
//			$value = 'deactivate';
			
			break;
		default:
			$value = false;
	}
			
	return $value;
}

add_filter( 'avf_alb_get_developer_settings', 'custom_alb_get_developer_settings', 10, 3 );
