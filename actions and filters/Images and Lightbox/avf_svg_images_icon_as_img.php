<?php
/**
 * Filter to return svg in <img src=""> tag - could be needed for colored icons with same named classes
 * in multiple svg overriding each other. It might be necessary to modify CSS to position the img tag.
 *  - <img> tag will have additional attribute is-svg-img='true'
 *  - CSS selector:  .avia-svg-icon img[is-svg-img="true"]
 * 
 * THIS FILTER IS NOT INTENDED TO MODIFY ALL SVG ICONS (especially not the included font svg_entypo-fontello) - Be selective which icons to replace.
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
	$my_fonts = [ 'your_svg_font', 'your_svg_font1' ];
	$my_chars = [ 'icon1', 'icon2' ];		// array of icons to replace

	// bail if not a svg font we want to change
	if( ! in_array( $font, $my_fonts ) )
	{
		return $svg_as_img;
	}

	// in case you want to change all icons of a font
	// return true;

	// check for single icons you want to change
	if( in_array( $icon_char, $my_chars ) )
	{
		return true;
	}

	return $svg_as_img;
}

add_filter( 'avf_svg_images_icon_as_img', 'custom_svg_images_icon_as_img', 10, 5 );
