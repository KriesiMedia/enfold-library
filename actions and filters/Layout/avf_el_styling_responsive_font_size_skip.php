<?php
/**
 * Skip default font size handling for an option of an ALB element
 *
 * Search for $element_styling->add_responsive_font_sizes in php code of shortcodes to locate usage and parameter values
 * to check for conditions
 *
 * @since 4.8.8.1
 * @param boolean $skip
 * @param array $atts
 * @param \aviaShortcodeTemplate|null $sc_context
 * @param string $font_id
 * @param string $selector_container
 * @return boolean				anything except false will skip handling
 */
function custom_responsive_font_size_skip( $skip, array $atts, \aviaShortcodeTemplate $sc_context, $font_id, $selector_container )
{
	//	e.g. skip for all 'Special Heading'
	if( $sc_context instanceof avia_sc_heading )
	{
		return true;
	}

	//	e.g.skip for 'Animated Countdown' option 'Number Font Sizes'
	if( $sc_context instanceof avia_sc_countdown )
	{
		if( 'size-title' == $font_id )
		{
			return true;
		}
	}

	return $skip;
}

add_filter( 'avf_el_styling_responsive_font_size_skip', 'custom_responsive_font_size_skip', 10, 5 );
