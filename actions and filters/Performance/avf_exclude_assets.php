<?php

/**
 * Add your files you want to exclude from compression
 * 
 * @since 4.5.6
 * @param array $exclude_files
 * @return array
 */
function my_avf_exclude_assets( array $exclude_files )
{
	/**
	 * Add CSS to exclude
	 */
	$exclude_files['css'][] = 'my-css-file';		//	use your enqueue name
	
	/**
	 * Add js to exclude
	 */
	$exclude_files['js'][] = 'my-js-file';			//	use your enqueue name
	
	return $exclude_files;
}

add_filter( 'avf_exclude_assets', 'my_avf_exclude_assets', 10, 1 );
		 
