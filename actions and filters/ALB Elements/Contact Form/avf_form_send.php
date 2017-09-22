<?php

/**
 * Allow to 
 *		-	stop execution here 
 *		-	do something different with the data
 *
 * Copy the following snippet in functions.php and modify output to your need
 * 
 */
add_filter('avf_form_send', 'my_avf_form_send', 10, 4 );

/**
 * 
 * @param boolean $send
 * @param array $post			all $_POST with "avia_" removed
 * @param array $form_params	all form parameters defined in shortcode
 * @param avia_form $avia_form
 * @return boolean				true to continue, false to stop sending email
 */
function my_avf_form_send( $send, $post, $form_params, $avia_form )
{
	//	Do something here
	
	return $send;
}
