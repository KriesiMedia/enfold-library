<?php

/**
 * Allow to change default output of categories
 * ============================================
 * 
 * by default supressed for setting Default(Business) blog style
 * 
 * 
 * Copy the following snippet in functions.php and modify output to your need
 * 
 * @param string $show						
 * @param string $blogstyle						'' | 'elegant-blog' | 'elegant-blog modern-blog'
 * @param avia_post_slider $postslider		
 * @return string								'show_elegant' | 'show_business' | 'use_theme_default' | 'no_show_cats' 
 * 
 * @version 1.0.0
 * @requires Enfold 4.0.6
 */
function custom_autoresponse_email( $show, $blogstyle, avia_post_slider $postslider )
{
	/*
	 * Modify $show to your need - see @return - defaults to 'use_theme_default'
	 */
	
	return $show;
}

add_filter( 'avf_postslider_show_catergories', 'custom_postslider_show_catergories', 10, 3 );
