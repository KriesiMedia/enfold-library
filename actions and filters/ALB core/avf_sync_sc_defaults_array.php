<?php
/**
 * Allows to modify the default shortcode values if an attribute is missing in shortcode.
 * This will not change set attributes in shortcode.
 *
 * @since 5.6.11
 * @param array $defaults
 * @param aviaShortcodeTemplate $sc_class		shortcode class
 * @param false|string $is_modal_item			'modal_item' | 'no_modal_item' | false
 * @param false|string $content					'content' | 'no_content' | false
 * @return array
 */
function custom_avf_sync_sc_defaults_array( $defaults, $sc_class, $is_modal_item, $content )
{
	/*
	 * Example: change attribute 'seperator' in class avia_sc_post_metadata from / to |
	 */
	if( $sc_class instanceof avia_sc_post_metadata )
	{
		$defaults['seperator'] = '|';
	}

	return $defaults;
}

add_filter( 'avf_sync_sc_defaults_array', 'custom_avf_sync_sc_defaults_array', 10, 4 );
