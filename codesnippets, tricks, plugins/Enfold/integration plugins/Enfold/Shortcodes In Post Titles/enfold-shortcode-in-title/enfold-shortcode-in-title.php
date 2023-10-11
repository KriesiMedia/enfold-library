<?php
/*
Plugin Name: Enfold Shortcodes in Titles
Plugin URI: www.kriesi.at
Description: Allows to use shortcodes in post titles. Be aware that titles might be wrapped in &lt;a&gt; tags and nesting of &lt;a&gt; tags is not allowed and will break layout. Check layout of your site carefully when using this feature.
Version: 1.0.0
Author: Guenter for www.kriesi.at
Author URI: www.kriesi.at
Text Domain:

@requires:	PHP 5.6   
@requires:  WP 5.3
@requires:	Enfold 4.7.3.1
*/

/*  
 * Copyright 2020
*/


/**
 * Execute shortcode in post title
 * 
 * To pass function parameter to shortcode use the following syntax:
 *		{{{-post_id-}}}	=> $post_id
 * 
 * Example:
 *	This is a shortcode title [your_sc "{{{-post_id-}}}"]
 * 
 * @since 1.0.0
 * @param string $title
 * @param int $post_id			can also be e.g. a menu-item id
 * @return string
 */
function handler_wp_the_title_add_shortcode( $title, $post_id )
{

	/**
	 * In backend we do not execute shortcode
	 * (edit page uses esc_html to display title)
	 */
	if( is_admin() )
	{
		return $title;
	}

	$new_title = str_replace( '{{{-post_id-}}}', ' post_id="' . $post_id . '" ', $title );


	/**
	 * Filters title before shortcode execution and replace your parameters
	 * 
	 * @since 1.0.0
	 * @param string $new_title
	 * @param string $title
	 * @param int $post_id		can also be e.g. a menu-item id
	 * @return string
	 */
	$new_title = apply_filters( 'avf_the_title_before_shortcode', $new_title, $title, $post_id );

	return do_shortcode( $new_title );
}


/**
 * Filters the page title for a single post.
 * 
 * @since 1.0.0
 * @param string $title
 * @param WP_Post $post
 * @return string
 */
function handler_wp_single_post_title_add_shortcode( $title, $post )
{
	if( ! isset( $post->ID ) )
	{
		return $title;
	}

	/**
	 * Allows to supress output of shortcode e.g. in browser tab
	 * 
	 * @since 1.0.0
	 * @param boolean
	 * @param string $title
	 * @param object $post
	 * @return boolean
	 */
	if( true !== apply_filters( 'avf_process_shortcode_single_post_title', true, $title, $post ) )
	{
		return strip_shortcodes( $title );
	}

	/**
	 * This WP handler is also called during rendering header - we need to enable shortcode processing for ALB
	 */
	add_filter( 'avf_preprocess_shortcode_in_header', 'handler_avia_frontend_preprocess_sc_header', 10, 6  );

	$return = handler_wp_the_title_add_shortcode( $title, $post->ID );

	/**
	 * Reset filter
	 */
	remove_filter( 'avf_preprocess_shortcode_in_header', 'handler_avia_frontend_preprocess_sc_header', 10, 6 );

	return $return;
}

/**
 * 
 * @param string
 * @param aviaShortcodeTemplate $shortcode
 * @param array $atts
 * @param string $content
 * @param string $shortcodename
 * @param boolean $fake
 */
function handler_avia_frontend_preprocess_sc_header( $preprocess, $shortcode, $atts, $content, $shortcodename, $fake )
{
	return 'preprocess_shortcodes_in_header';
}


add_filter( 'the_title', 'handler_wp_the_title_add_shortcode', 999999, 2 );
add_filter( 'single_post_title', 'handler_wp_single_post_title_add_shortcode', 999999, 2 );



