<?php
/**
 * Allow to force execution of shortcodes e.g. for plugins who call shortcode via an ajax call.
 * Enfold uses this to execute the shortcode for modal popup preview in backend
 * 
 * @since 4.5.7.2
 * @param boolean
 * @param aviaShortcodeTemplate $obj_sc
 * @param array $atts
 * @param string $content
 * @param string $shortcodename
 * @param boolean $fake
 * @return boolean				true if sc should be executd regardless of page structure - same as popup preview
 */
function my_custom_exec_sc_only( $exec_sc_only, $obj_sc, $atts, $content, $shortcodename, $fake )
{
	/**
	 * Return if true - Enfold already requested an execution because of preview in backend
	 * Otherwise this is likley to be false.
	 */
	if( true === $exec_sc_only )
	{
		return $exec_sc_only;
	}
	
	//	Make your checks here - make sure to return boolean true if you want to force execution
	
	return $exec_sc_only;
}

add_filter( 'avf_alb_exec_sc_only', 'my_custom_exec_sc_only', 10, 6 );
