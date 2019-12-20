<?php

/* 
 * The following snippet shows how to add a shortcode to title and have a correct markup
 */

add_shortcode( 'sc_avia_test_head', 'avia_test_head' );
add_filter( 'the_title', 'avia_add_shortcode', 999999, 2 );

function avia_test_head( $atts, $content = '', $shortcodename = '' )
{
	return 'text of sc_av_test_head';
}

function avia_add_shortcode( $title, $post_id )
{
	return $title . ' - ' . do_shortcode( '[sc_avia_test_head]' );
}
