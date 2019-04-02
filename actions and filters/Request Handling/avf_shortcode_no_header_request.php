<?php

/**
 * See enfold/config-templatebuilder/avia-template-builder/php/shortcode-template.class.php
 * ========================================================================================
 * 
 * function shortcode_handler_prepare()
 * 
 * 
 * In frontend we ignore requests to shortcodes before header is finished
 * Fixes problems with plugins that run shortcodes in header (like All In One SEO)
 * Running shortcodes twice might break the behaviour and layout.
 * 
 * But there are frontend requests that do not need of a header. To allow 3rd party plugins to hook
 * we add a filter (e.g. GraphQL).
 * 
 * @param boolean $no_header_request
 * @param aviaShortcodeTemplate $this
 * @param array $atts
 * @param string $content
 * @param string $shortcodename
 * @param boolean $fake
 * @return boolean						false | true if no header is needed in request
 */
function custom_shortcode_no_header_request( $no_header_request, $this, $atts, $content, $shortcodename, $fake )
{
	if( ( defined( 'GRAPHQL_REQUEST' ) && true === GRAPHQL_REQUEST ) )
	{
		$no_header_request = true;
	}
	
	return $no_header_request;
}

add_filter( 'avf_shortcode_no_header_request', 'custom_shortcode_no_header_request', 10, 6 );

