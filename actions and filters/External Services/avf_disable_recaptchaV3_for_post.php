<?php

/*
 * The following snippet shows some possibilities how to use reCaptcha filter
 * 
 * Remove what you do not need !!
 * 
 * @since 4.6
 * @param boolean $disable
 * @return boolean					true for not to load | false to load
 */
function my_avf_disable_recaptchaV3_for_post( $disable )
{
	global $post;
	
	if( ! $post instanceof WP_Post )
	{
		return $disable;
	}
	
	/**
	 * Array of page id's where you want to show or hide V3 reCaptcha
	 */
	$page_ids = array( 124, 245, 350 );
	
	/**
	 * Check to show recaptcha on given page ID's
	 */
	$disable = in_array( $post->ID, $page_ids ) ? false : true;
	
	/**
	 * Check to hide recaptcha on given page ID's 
	 */
	$disable = in_array( $post->ID, $page_ids ) ? true : false;
	
	/**
	 * Get content to check (ALB or normal content)
	 * 
	 * Example: check for Enfold contact form shortcode to load, do not load on other pages
	 */
	$content = Avia_Builder()->get_post_content( $post->ID );
	$disable = ( false !== strpos( $content, '[av_contact ' ) ) ? false : true;
	
	return $disable;
}
	
add_filter( 'avf_disable_recaptchaV3_for_post', 'my_avf_disable_recaptchaV3_for_post', 10, 1 );




