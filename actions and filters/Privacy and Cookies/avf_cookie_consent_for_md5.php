<?php
/* 
 * Allow to customize md5 value to check if content has changed.
 * Can be useful on multisite installs within same domain 
 * https://kriesi.at/support/topic/cookie-consent-value-of-the-cookie-md5-multisite/
 * 
 * @since 4.6.4
 * @param string $cookie_contents
 * @param string $message
 * @param array $buttons
 * @return string
 */

function custom_avf_cookie_consent_for_md5( $cookie_contents, $message, $buttons )
{
	/**
	 * Add a logic here to get the same $cookie_contents for all sites.
	 * 
	 * Whenever you need a new popup of the message bar you need to chenge the output here to a different value.
	 */
	
//	$cookie_contents = 'your value to be same for all sites';
	
	return $cookie_contents;
}

add_filter( 'avf_cookie_consent_for_md5', 'custom_avf_cookie_consent_for_md5', 10, 3 );
