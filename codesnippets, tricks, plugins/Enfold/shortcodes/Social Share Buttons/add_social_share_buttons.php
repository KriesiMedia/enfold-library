<?php
/* 
 * This snippet shows how to add a social share button
 */


/**
 * Register new icon as a theme icon 
 * 
 * @param array $icons
 * @return array
 */
function avia_add_custom_icon( $icons ) 
{
	$icons['telegram'] = array( 
						'font' => 'entypo-fontello', 
						'icon' => 'ue8b7' 
					);
	return $icons;
}

add_filter( 'avf_default_icons', 'avia_add_custom_icon', 10, 1 );

/**
 * Add items on the social share section
 * 
 * @param type $args
 * @return type
 */
function avia_add_social_share_link_arguments( $args )
{
	$args['telegram'] = array( 
						'encode'		=> true, 
						'encode_urls'	=> false, 
						'pattern'		=> 'https://telegram.me/share/url?text=&url=[permalink]', 
						'label'			=> __( 'Share on Telegram', 'avia_framework' ) 
					);
	return $args;
}

add_filter( 'avia_social_share_link_arguments', 'avia_add_social_share_link_arguments', 10, 1 );