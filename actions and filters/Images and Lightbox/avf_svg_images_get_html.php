<?php
/**
 * Customize the svg html.
 * Return false if you want to render the svg in an <img> tag in frontend
 *
 * @since 4.8.7.2
 * @param string $svg
 * @param int $attachment_id
 * @param string $url
 * @param string $preserve_aspect_ratio
 * @param aviaSVGImages $obj_svg_img
 * @param string $svg_original
 * @return string|false
 */
function custom_svg_images_get_html( $svg, $attachment_id, $url, $preserve_aspect_ratio, aviaSVGImages $obj_svg_img, $svg_original )
{
	//	add some logic to decide to change svg
	$return_default_svg = true;

	if( $return_default_svg )
	{
		return $svg;
	}

	// e.g. you want to return the unmodified svg html
	return $svg_original;
}

add_filter( 'avf_svg_images_get_html', 'custom_svg_images_get_html', 10, 6 );
