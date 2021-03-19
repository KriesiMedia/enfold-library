<?php

/**
 * As of WP 5.6 (or maybe already earlier) when adding an image to standard WP tinyMCE editor
 * with the "Add Media" button the title tag is skipped and no longer supported:
 * 
 * "For both accessibility and search engine optimization (SEO), alt text is more important than title text. 
 * This is why we strongly recommend including alt text for all your images."
 * 
 * Following snippet adds the title attribute to new added images.
 */

/**
 * Add title attribute to new inserted image via "Add Media" button
 * 
 * @since 4.8.2
 * @param string $html
 * @param int $id
 * @param string $caption
 * @param string $title				is always empty because removed by WP
 * @param string $align
 * @param string $url
 * @param string $size
 * @param string $alt
 * @return string
 */
function handler_image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt ) 
{
	//	if title attr already exist, return unmodified
	if( false === strpos( '<img ', $html ) || false !== strpos( 'title=', $html ) )
	{
		return $html;
	}
	
	$html = str_replace( '<img ', '<img title="' . esc_attr( get_the_title( $id ) ) . '" ' , $html );
	return $html;
}

add_filter( 'image_send_to_editor', 'html_insert_image', 10, 8 );
