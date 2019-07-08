<?php


/**
 * 
 * @since 4.5.6
 * @param array $data
 * @param int $enqueued_to_do_index		$enqueued->to_do index
 * @param string $file_type				'js' | 'css'
 * @param string $file_group_name		'avia-head-scripts' | 'avia-footer-scripts' | 'avia-merged-styles'
 * @param WP_Scripts $enqueued
 * @param array $conditions
 */
function custom_asset_mgr_get_file_data( array $data, $enqueued_to_do_index, $file_type, $file_group_name, WP_Scripts $enqueued, array $conditions )
{
	
	/**
	 * This is only an example how to alter the path to a file.
	 * Make sure to add the correct path otherwise merging will fail
	 */
	$file = $enqueued->to_do[ $enqueued_to_do_index ];
	$key = $file . '-' . $file_type;
	
	if( 'avia-compat-js' == $key )
	{
		$new_path = $data['remove'][ $key ]['path'];	// enter your changed path here  !!!!
		$data['remove'][ $key ]['path'] = $new_path;
	}
	
	/**
	 * ******************   End of example   ***********************
	 */

	return $data;
}
						
add_filter( 'avf_asset_mgr_get_file_data', 'custom_asset_mgr_get_file_data', 10, 6 );

