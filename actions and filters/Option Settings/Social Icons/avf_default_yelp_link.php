<?php

/* 
 * Modify the yelp link on a single blog post page to link to the post specific page on yelp
 * 
 * This snippet is only an example.
 * 
 * To make it easier to maintain you can add a custom field (e.g. yelp_page_link) to the post and add the link there.
 * Both ways are shown below.
 * 
 * @since 4.6.4
 * @param array $default
 * @param array $args
 * @param array|false $options
 * @param string|false $title
 * @return array
 */
function custom_avf_default_yelp_link( $default, $args, $options, $title )
{
	if( ! is_single() )
	{
		return $default;
	}
	
	$current_id = get_the_ID();
	

	/**
	 * Use fixed post ID's (remove if not used)
	 */
	$post_ids = array( 124, 250, 350 );
	
	if( in_array( $current_id, $post_ids ) )
	{
		switch( $current_id )
		{
			case 124:
				$default['pattern'] = 'add your link for 124 here';
				$default['label'] = 'add a custom tooltip for 124 here';
				break;
			case 250:
				$default['pattern'] = 'add your link for 250 here';
				$default['label'] = 'add a custom tooltip for 250 here';
				break;
			case 350:
				$default['pattern'] = 'add your link for 350 here';
				$default['label'] = 'add a custom tooltip for 350 here';
				break;
		}
	}
	
	return $default;
	
	/**
	 * End Use fixed post ID's
	 * =======================
	 */
	
	/**
	 * Using custom field yelp_page_link (remove if not used)
	 */
	$link = get_post_meta( $current_id, 'yelp_page_link', true );
	
	if( ! empty( $link ) )
	{
		$default['pattern'] = $link;
	}
	
	return $default;
	
	/**
	 * End Use custom field
	 * =======================
	 */
}

add_filter( 'avf_default_yelp_link', 'custom_avf_default_yelp_link', 10, 4 );