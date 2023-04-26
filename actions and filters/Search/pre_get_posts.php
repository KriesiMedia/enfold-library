<?php
/**
 * Modify search query
 *
 * @param WP_Query $query
 * @return WP_Query
 */
function custom_selective_post_type_search( $query )
{
	if( $query->is_search )
	{
		//	include the post types you want to query
		$query->set( 'post_type', array( 'post', 'page', 'portfolio', 'attachment' ) );
		$query->set( 'post_status', array( 'publish', 'inherit' ) );

		/**
		 * modify number of posts per search page
		 *
		 * When using grid layout this value is also used by avia_post_slider (since 5.6)
		 */
		$query->set( 'posts_per_page', 6 );
	}

	return $query;
}

add_filter( 'pre_get_posts', 'custom_selective_post_type_search', 10, 1 );

