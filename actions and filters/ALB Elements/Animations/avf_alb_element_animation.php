<?php
/**
 * Allows to replace the waypoint animation class with a custom class
 *
 * Return '' (or an invalid class) to cancel any animation
 * If you return a custom class you have to bind $.avia_waypoints()
 * similar to enfold\js\shortcodes.js function activate_waypoints().
 *
 * There you also find all theme predefined classes (extended in 5.3).
 *
 *
 * @since 5.3
 * @added_by Günter
 * @param string $class_animation
 * @param array $atts
 * @param aviaShortcodeTemplate $sc
 * @param string $shortcodename
 * @return string
 */
function custom_alb_element_animation( $class_animation, array $atts, aviaShortcodeTemplate $sc, $shortcodename )
{
	//	change animation for icon circles
	if( $shortcodename != 'av_icon_circles' )
	{
		return $class_animation;
	}

	//	return a new waypoint animation class
	return '.av-animated-when-visible-95';
}

add_filter( 'avf_alb_element_animation', 'custom_alb_element_animation', 10, 4 );
