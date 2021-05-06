<?php
/**
 * Return an array in the desired order. Missing buttons are appended at the end in the default order of $obj
 * 
 * @since 4.8.3
 * @param array $sort
 * @param array $options
 * @param avia_social_share_links $obj
 * @return array
 */
function custom_avf_social_share_buttons_order( array $sort, array $options, avia_social_share_links $obj )
{
	//	Define the order of buttons that you want to have in front
	return [ 'twitter', 'pinterest', 'facebook', 'whatsapp', 'five_100_px', 'behance', 'linkedin' ];
}


add_filter( 'avf_social_share_buttons_order', 'custom_avf_social_share_buttons_order', 10, 3 );

