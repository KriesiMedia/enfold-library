<?php
/**
 * Yoast Breadcrumb
 *
 * Use the "avf_title_args" filter to replace the default breadcrumb with the WP SEO plugin's breadcrumb.
 * Related thread: http://www.kriesi.at/support/topic/make-breadcrumb-path-follow-primary-category/#
 *
 **/


/** Add the following snippet in the  functions.php file. **/


function avf_enfold_yoast_breadcrumb( $args ) 
{
	ob_start();
	yoast_breadcrumb();
	$yoastb = ob_get_clean();

	$args['breadcrumb'] = false;
	$args['additions'] = '<div class="breadcrumb breadcrumbs avia-breadcrumbs">'.$yoastb.'</div>';
	
	return $args;
}

add_filter( 'avf_title_args', 'avf_enfold_yoast_breadcrumb', 10, 1 );
