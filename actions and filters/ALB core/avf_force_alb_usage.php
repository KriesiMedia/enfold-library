<?php
/**
 * Filter to force usage of ALB for a specific post. This will also hide the switch button with CSS.
 * Also works for Block Editor.
 *
 * If you do not want to force all posts of a post type to use ALB (e.g. because you have old posts using classic editor)
 * you must use a more specific logic.
 *
 * @since x.x.x
 * @param boolean $force_alb
 * @param WP_Post $post
 * @return boolean
 */
function custom_avf_force_alb_usage( $force_alb, $post )
{
	//	security check
	if( ! $post instanceof WP_Post )
	{
		return $force_alb;
	}

	/**
	 * e.g. force all posts with posttype post to use ALB
	 */
	if( 'post' == $post->post_type )
	{
		$force_alb = true;
	}

	return $force_alb;
}

add_filter( 'avf_force_alb_usage', 'custom_avf_force_alb_usage', 10, 2 );
