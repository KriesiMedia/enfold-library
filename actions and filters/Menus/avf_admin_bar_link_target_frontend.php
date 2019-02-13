<?php

/**
 * Set link targets for admin bar elements added by Enfold in frontend
 * By default link target is the same page. Return _blank to change this 
 * (or any other valid value: https://www.w3schools.com/tags/att_a_target.asp)
 * 
 * @param string $target
 * @param string $context
 * @return string					'' | '_blank' | '_self' | '_parent' | '_top' | framename
 */
function my_avf_admin_bar_link_target_frontend( $target, $context )
{
	switch( $context )
	{
		case 'edit_button':
		case 'dynamic_template':
		case 'theme_options':
		case 'edit_button_gutenberg':
			$target = '_blank';
			break;
	}
	
	return $target;
}

add_filter( 'avf_admin_bar_link_target_frontend', 'my_avf_admin_bar_link_target_frontend', 10, 2 );

