<?php

/* Compatibility fix for Enfold Main logo with WP-Rocket Lazy-Load */
function mip_fix_enfold_logo_wp_rocket( $logo ) 
{
     return str_replace( "<img", "<img data-no-lazy=\"1\"", $logo );
}

add_filter( 'avf_logo_final_output', 'mip_fix_enfold_logo_wp_rocket' );
