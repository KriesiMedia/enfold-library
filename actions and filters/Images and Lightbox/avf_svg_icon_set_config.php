<?php
/**
 * Example code how to modify icon set config file
 * 
 *
 * @param array $config_json
 * @param string $font_name
 * @return array
 */
function custom_svg_icon_set_config( array $config_json = [], $font_name = '' )
{
	/**
	 * Datastructure for config info for an svg icon
	 */
	$default_info = [
            'file_name'		=> '',		//	should not be changed unless you know ehat you are doing
            'path'			=> '',		//	should not be changed unless you know ehat you are doing
            'title'			=> '',		//	added to title tag of inline svg icon (used for aria support)
            'description'	=> '',		//	added to desc tag of inline svg icon (used for aria support)
            'alt'			=> '',		//
            'search_text'	=> ''		//	used to filter icons in icon selector in modal popup window
	];

	/**
	 * This is an example list to modify rendered svg icons config
	 */
	$example_list = [

		'angle-circled-up'	=> [
							'file_name'		=> 'angle-circled-up.svg',
							'path'			=> 'svg/angle-circled-up.svg',
							'title'			=> 'Angle Circled Up Icon',
							'description'	=> 'Description for Angle Circled Up Icon',
							'alt'			=> 'Angle Circled Up Icon Image',
							'search_text'	=> 'Angle Circled Up'
						],

		'attention'	=> [
							'file_name'		=> 'attention.svg',
							'path'			=> 'svg/attention.svg',
							'title'			=> 'Attention Icon',
							'description'	=> 'Description for Attention Icon',
							'alt'			=> 'Attention Icon Image',
							'search_text'	=> 'Attention'
						]
		];


	if( empty( $config_json ) )
	{
		return $config_json;
	}

	/**
	 * Example: your uploaded svg icon set name is 'svg_icomoon'
	 *
	 */
	if( $font_name != 'svg_icomoon' )
	{
		return $config_json;
	}

	if( empty( $config_json[ $font_name ] ) )
	{
		return $config_json;
	}

	/**
	 * Update values in rendered list
	 */
	foreach( $example_list as $icon_name => $config )
	{
		if( ! isset( $config_json[ $font_name ][ $icon_name ] ) )
		{
			continue;
		}

		$config_json[ $font_name ][ $icon_name ]['title'] = $config['title'];
		$config_json[ $font_name ][ $icon_name ]['description'] = $config['description'];
		$config_json[ $font_name ][ $icon_name ]['alt'] = $config['alt'];
		$config_json[ $font_name ][ $icon_name ]['search_text'] = $config['search_text'];
	}

	return $config_json;
}

add_filter( 'avf_svg_icon_set_config', 'custom_svg_icon_set_config', 10, 2 );

