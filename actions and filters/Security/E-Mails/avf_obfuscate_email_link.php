<?php
/**
 * Filter to obfuscate E-Mail links used in ALB Element link settings
 *
 * @since 5.6.7
 * @param boolean $obfuscate
 * @param string $e_mail
 * @return boolean
 */
function custom_avf_obfuscate_email_link( $obfuscate, $e_mail )
{
	// you can add a check for $e_mail if you want to add some logic

	// return true if you want to obfuscate
	$obfuscate = true;

	return $obfuscate;
}

add_filter( 'avf_obfuscate_email_link', 'custom_avf_obfuscate_email_link', 10, 2 );


/**
 * Change the default hex_encoding = 1 for WP antispambot function
 * Possible values are 0 and 1
 *
 * @link https://developer.wordpress.org/reference/functions/antispambot/
 * @param type $hex_encoding
 * @param type $e_mail
 */
function custom_avf_obfuscate_email_link_hex_encoding( $hex_encoding, $e_mail )
{
	$hex_encoding = 1;		//	0 or 1 is possible

	return $hex_encoding;
}

add_filter( 'avf_obfuscate_email_link_hex_encoding', 'custom_avf_obfuscate_email_link_hex_encoding', 10, 2 );


/**
 * Shortcode (also accessible via magic wand button in tiny MCE):
 *
 * [av_email_spam url="your_email@domain.com" hex_encoding="1"]readable info[/av_email_spam]
 */
