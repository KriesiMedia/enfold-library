<?php
/**
 * @since 4.9
 * @param string $img_html
 * @param array $attachment
 * @param array $entry
 * @param array $config
 * @return string
 */
function custom_avf_masonry_flex_image_html( $img_html, array $attachment, array $entry, array $config )
{
	// add some logic to check if you want to change default behaviour
	$add_hw = true;

	if( ! $add_hw )
	{
		return $img_html;
	}

	$width = isset( $attachment[1] ) && is_numeric( $attachment[1] ) ? "width='{$attachment[1]}'" : '';
	$height = isset( $attachment[2] ) && is_numeric( $attachment[2] ) ? "height='{$attachment[2]}'" : '';

	return str_replace( '<img ', "<img {$width} {$height} ", $img_html );
}

add_filter( 'avf_masonry_flex_image_html', 'custom_avf_masonry_flex_image_html', 10, 4 );