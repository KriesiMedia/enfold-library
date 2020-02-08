<?php

/* 
 * Some server configurations seem to cache WP options and do not return changed options - so we generate a new file again and again
 * This filter allows to return the same value (or a custom value) for each file. "---" is added to seperate and identify as added value.
 * Return empty string to avoid adding.
 * 
 * The following snippet shows what to return.
 * 
 * @since 4.7.2.1
 * @param string $uniqid
 * @param array $data
 * @param WP_Scripts $enqueued
 * @param string $file_group_name
 * @param array $conditions
 * @return string				
 */
function my_custom_merged_files_unique_id( $uniqid, $file_type, $data, $enqueued, $file_group_name, $conditions )
{
	/**
	 * If you want to return it unmodified:
	 */
	return $uniqid;
	
	/**
	 * If you want to remove it completly:
	 */
	return '';
	
	/**
	 * If you want to return a custom value, add some logic
	 */
	$uniqid = 'your custom value';
	return $uniqid;
}


add_filter( 'avf_merged_files_unique_id', 'my_custom_merged_files_unique_id', 10, 6 );