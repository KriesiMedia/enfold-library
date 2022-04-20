<?php
/**
 * Modify which structured data you want to exclude from output
 *
 * @since 4.9.2.2
 * @param array $exclude
 * @param int $id				post id
 * @return array
 */
function custom_avf_blog_entry_markup_helper_exclude( array $exclude = array(), $id = 0 )
{
	//	you may add some logic to only modify on certain post id's
	//
	//	e.g. you want to remove 'date' and 'date_modified' for all posts
	$exclude = array_merge( $exclude, array( 'date', 'date_modified' ) );

	return $exclude;
}

add_filter( 'avf_blog_entry_markup_helper_exclude', 'custom_avf_blog_entry_markup_helper_exclude', 10, 2 );
