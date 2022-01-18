<?php
/**
 * This is an example code how to replace and remove a default shortcode
 *
 * @since 4.8.9
 */


/**
 * In e.g. functions.php use filter to deactivate a default or add a custom shortcode file
 *
 * @param boolean $loaded
 * @param string $file
 * @return boolean
 */
function custom_avf_load_single_shortcode_file( $loaded, $file )
{
	// check for ...\...\promobox.php to remove from loading and load your custom one
	if( true === strpos( $file, 'promobox.php' ) )
	{
		//	add your custom (skip line if you only want to remove default)
		require_once( 'your_path/custom-promobox.php' );

		return true;
	}

	return $loaded;
}

add_filter( 'avf_load_single_shortcode_file', 'custom_avf_load_single_shortcode_file', 10, 2 );

