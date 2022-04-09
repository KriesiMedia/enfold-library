<?php
/**
 * Filter to change image size for lightbox images
 *
 * Value of $this->config['shortcode'] is found in shortcode class (e.g. gallery.php) in function shortcode_insert_button()
 *
 * @since 4.8.2
 * @param string $size
 * @param string $context			'avia_masonry' | 'avia_slideshow' | $this->config['shortcode']
 * @param mixed $args1				depend on $context
 * @param mixed $args2				depend on $context
 */
function custom_alb_lightbox_image_size( $size, $context, $args1 = null, $args2 = null )
{
	/*
	 * Example: to change image size for gallery to full
	 */
	if( 'av_gallery' == $context )
	{
		return 'full';
	}

	return $size;
}

add_filter( 'avf_alb_lightbox_image_size', 'custom_alb_lightbox_image_size', 10, 4 );
