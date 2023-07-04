// https://kriesi.at/support/topic/recaptcha-v2-wpml/#post-1374230
add_filter('avf_google_recaptcha_apiurl_lang', function($lang, $context) {
	if($context == 'frontend')
	{
		$lang = substr(get_locale(), 0, 2);
	}

	return $lang;
}, 10, 2);
