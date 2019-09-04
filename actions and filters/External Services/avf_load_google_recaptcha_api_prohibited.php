<?php
/*
 * The following snippet shows some possibilities how to use reCaptcha filter
 * 
 * Remove what you do not need !!
 * 
 * @since 4.6
 * @param boolean $prohibited
 * @return boolean					true for not to load | false to load
 */
function my_avf_load_google_recaptcha_api_prohibited( $prohibited )
{
	global $post;
	
	if( ! $post instanceof WP_Post )
	{
		return $prohibited;
	}
	
	/**
	 * Array of page id's where you want to load or not load reCaptcha
	 */
	$page_ids = array( 124, 245, 350 );
	
	/**
	 * Check to load recaptcha on given page ID's only
	 */
	$prohibited = in_array( $post->ID, $page_ids ) ? false : true;
	
	/**
	 * Check to not load recaptcha on given page ID's - load on all others
	 */
	$prohibited = in_array( $post->ID, $page_ids ) ? true : false;
	
	/**
	 * Get content to check (ALB or normal content)
	 * 
	 * Example: check for Enfold contact form shortcode to load, do not load on other pages
	 */
	$content = Avia_Builder()->get_post_content( $post->ID );
	$prohibited = ( false !== strpos( $content, '[av_contact ' ) ) ? false : true;
	
	return $prohibited;
}

add_filter( 'avf_load_google_recaptcha_api_prohibited', 'my_avf_load_google_recaptcha_api_prohibited', 10, 1 );
