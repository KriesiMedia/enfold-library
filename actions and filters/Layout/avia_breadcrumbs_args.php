<?php

/**
 * Use the "avia_breadcrumbs_args" filter to adjust breadcrumb arguments 
 *
 **/

/* Following code changes "Home" in breadcrumbs */ 
add_filter( 'avia_breadcrumbs_args', 'avia_breadcrumbs_args_mod', 10, 1 );
function avia_breadcrumbs_args_mod( $args ) {
	$args['show_home'] = __( 'New Home', 'avia_framework' );
	return $args;
}


/* Following code removes "You are here:" in front of breadcrumbs */ 
add_filter('avia_breadcrumbs_args', 'avia_remove_you_are_here_breadcrumb', 10, 1);
function avia_remove_you_are_here_breadcrumb($args){
$args['before'] = "";
return $args;
}
