<?php
// **********************************************************************// 
// Shortcode override 
//
// http://www.kriesi.at/support/topic/portfolio-meta/#post-134009
// **********************************************************************// 

function avia_include_shortcode_template( $paths )
{
	$template_url = get_stylesheet_directory();
	array_unshift( $paths, $template_url . '/shortcodes/' );
	
	return $paths;
}

add_filter( 'avia_load_shortcodes', 'avia_include_shortcode_template', 15, 1 );
