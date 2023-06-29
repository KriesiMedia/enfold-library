<?php
/**
 * Theme option must be enabled for old browser support otherwise
 * filter has no function because legacy file functions-legacy-browser.php is not loaded
 *
 * Allows to deactivate/activate old browser support on certain pages.
 *
 * @since 5.6.3
 * @param boolean $fallback
 * @param string $context				'css' | 'js'
 * @return boolean
 */
function avia_old_browser_support( $fallback, $context )
{
	global $post;

	/**
	 * This is only an example - allow for following pages only
	 */
	if( $post instanceof WP_Post && in_array( $post->ID, [ 150, 160, 175 ] ) )
	{
		return true;
	}

	return false;
}

//	use a higher priority than 10 to hook after theme
add_filter( 'avf_old_browser_support', '\enfoldLegacyFunctions\avia_old_browser_support', 99, 2 );
