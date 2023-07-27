<?php
/**
 * Advance Layout Builder Debug Mode
 *
 * Display the actual shortcodes of the elements below the advance layout builder. 
 * Documentation: http://www.kriesi.at/documentation/enfold/enable-advanced-layout-builder-debug/
 *
 * @since 5.6     deprecated, replaced by theme option
 **/


/** Add the following snippet in the child theme's functions.php file. **/

// set the advance layout builder to debug mode 
function ava_enfold_debug_mode() 
{
  return "debug";
}

add_action( "avia_builder_mode", "ava_enfold_debug_mode" );
