<?php
/**
 * Allows especially for ALB posts to change output to 'content'
 * Supported since 4.5.5
 *
 * ATTENTION:
 * ==========
 *
 * Displaying ALB content may lead to nested shortcodes which is not allowed and circular reference
 * breaking the layout and/or displaying the page. 
 *
 * @since 4.5.5
 * @added by Günter
 * @param string $blog_content
 * @param array $current_post
 * @param string $blog_style
 * @param string $blog_global_style
 * @return string
 */
function custom_avf_blog_content_in_loop( $blog_content, $current_post, $blog_style, $blog_global_style )
{
	global $avia_config;

	/**
	 * Example:
	 * ========
	 *
	 * Check if we are on e.g. page 124 and force ALB content to be shown
	 */


	if( ! is_numeric( $avia_config['real_ID'] ) )
	{
		return $blog_content;
	}

	//	Array of pages where ALB content can be displayed
	$pages = [ 124, 6667 ];

	if( ! in_array( $avia_config['real_ID'], $pages ) )
	{
		return $blog_content;
	}

	//	Check for ALB post
	if( Avia_Builder()->get_alb_builder_status( $avia_config['real_ID'] ) != 'active' )
	{
		return $blog_content;
	}

	//	Array of post id's that cannot show content because it breaks layout
	$exclude = [];

	if( in_array( $current_post['the_id'], $exclude ) )
	{
		return $blog_content;
	}

	//	force to show post content
	return 'content';
}


add_filter( 'avf_blog_content_in_loop', 'custom_avf_blog_content_in_loop', 10, 4 );


