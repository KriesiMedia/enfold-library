<?php
/**
 * Filter to return svg in <img src=""> tag - could be needed for colored icons with same named classes
 * in multiple svg. It might be necessary to modify CSS.
 *
 * @since 7.0
 * @param boolean $svg_as_img
 * @param string $icon_char				name of icon
 * @param string $font					font name
 * @param boolean $isColoredSVG
 * @param string $svg					original svg tag
 * @return boolean						anything except false to use img tag
 */
function custom_svg_images_icon_as_img( $svg_as_img, $icon_char, $font, $isColoredSVG, $svg )
{
	$my_font = 'your_svg_font';
	$my_chars = [ 'icon1', 'icon2' ];		// array of icons to replace

	if( $font != $my_font )
	{
		return $svg_as_img;
	}

	if( in_array( $icon_char, $my_chars ) )
	{
		return true;
	}

	return $svg_as_img;
}

add_filter( 'avf_svg_images_icon_as_img', 'custom_svg_images_icon_as_img', 10, 5 );
