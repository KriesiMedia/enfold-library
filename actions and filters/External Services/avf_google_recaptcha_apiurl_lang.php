<?php
/**
 * Add language string to translate Google ReCaptcha
 *
 * @since 5.3
 * @param string $lang
 * @param string $context			'backend' | 'frontend'
 * @return string
 */
function custom_google_recaptcha_apiurl_lang( $lang, $context )
{
	//	if only in frontend
	if( $context == 'frontend' )
	{
		$lang = substr( get_locale(), 0, 2 );
	}

	return $lang;
}

add_filter( 'avf_google_recaptcha_apiurl_lang', 'custom_google_recaptcha_apiurl_lang', 10, 2 );
