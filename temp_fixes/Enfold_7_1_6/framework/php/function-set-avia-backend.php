<?php
/**
 * This file holds various helper functions that are needed by the frameworks BACKEND
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright (c) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 */
if( ! defined( 'AVIA_FW' ) )  {  exit( 'No direct script access allowed' );  }


/**
 * Check which post type we currently see
 */
if( ! function_exists( 'avia_backend_get_post_type' ) )
{
	function avia_backend_get_post_type()
	{
		global $post, $typenow, $current_screen;

		$posttype = '';

		//we have a post so we can just get the post type from that
		if( $post && $post->post_type )
		{
			$posttype = $post->post_type;
		}
		//check the global $typenow - set in admin.php
		else if( $typenow )
		{
			$posttype = $typenow;
		}
		//check the global $current_screen object - set in sceen.php
		else if( $current_screen && $current_screen->post_type )
		{
			$posttype = $current_screen->post_type;
		}
		//lastly check the post_type querystring
		else if( isset( $_REQUEST['post_type'] ) )
		{
			$posttype = sanitize_key( $_REQUEST['post_type'] );
		}

		return $posttype;
	}
}

if( ! function_exists( 'avia_backend_load_scripts_by_option' ) )
{
	/**
	 * Load files from a multidemensional array
	 *
	 * @param array $scripts_to_load
	 */
	function avia_backend_load_scripts_by_option( $scripts_to_load )
	{
		if( ! is_array( $scripts_to_load ) )
		{
			return;
		}

		foreach ( $scripts_to_load as $path => $includes )
		{
			if( $includes )
			{
				foreach ( $includes as $include )
				{
					switch( $path )
					{
						case 'php':
							include_once( AVIA_PHP . $include . '.php' );
							break;
					}
				}
			}
		}
	}
}


if( ! function_exists( 'avia_backend_load_scripts_by_folder' ) )
{
	/**
	 * load all php files in one folder,
	 * if the folder contains files with different file extensions return the filenames as array
	 *
	 * @param string $folder		path to the folder that should be loaded
	 * @return array				no php files !!!
	 */
	function avia_backend_load_scripts_by_folder( $folder )
	{
		$files = array();

		$min_js = avia_minify_extension( 'js' );
		$min_css = avia_minify_extension( 'css' );

		// Open a known directory, and proceed to read its contents
		if( is_dir( $folder ) )
		{
			$dh = opendir( $folder );

		    if( false !== $dh )
		    {
				$file = readdir( $dh );
		        while( $file !== false )
		        {
		        	if( '.' != $file && '..' != $file && ! is_dir( trailingslashit( $folder ) . $file ) )
		        	{
		        		$pathinfo = pathinfo( trailingslashit( $folder ) . $file );

		        		if( isset( $pathinfo['extension'] ) )
		        		{
							switch( $pathinfo['extension'] )
							{
								case 'php':
									include_once( $folder . '/' . $file );
									break;
								case 'css':
									$is_min_file = false !== strpos( $file, '.min.css' );

									if( $is_min_file )
									{
										//	skip .min file
										break;
									}

									if( $min_css && ! $is_min_file )
									{
										$file = preg_replace("((.*).css$)", "$1{$min_css}.css", $file );
									}

									$files[] = $file;
									break;
								case 'js':
									$is_min_file = false !== strpos( $file, '.min.js' );

									if( $is_min_file )
									{
										//	skip .min file
										break;
									}

									if( $min_js && ! $is_min_file )
									{
										$file = preg_replace("((.*).js$)", "$1{$min_js}.js", $file );
									}

									$files[] = $file;
									break;
								default:
									$files[] = $file;
							}
		        		}
		        		else
		        		{
		        			$files[] = $file;
		        		}
		        	}

					$file = readdir( $dh );
		        }

		        closedir( $dh );
		    }
		}

		return $files;
	}
}


if( ! function_exists( 'avia_backend_safe_string' ) )
{
	/**
	 * Create a lower case version of a string without spaces so we can use that string for database settings
	 *
	 * @param string $string				to convert
	 * @param string $replace
	 * @param boolean $check_spaces
	 * @return string						the converted string
	 */
	function avia_backend_safe_string( $string, $replace = '_', $check_spaces = false )
	{
		$string = strtolower( $string );

		$trans = array(
					'&\#\d+?;'				=> '',
					'&\S+?;'				=> '',
					'\s+'					=> $replace,
					'ä'						=> 'ae',
					'ö'						=> 'oe',
					'ü'						=> 'ue',
					'Ä'						=> 'Ae',
					'Ö'						=> 'Oe',
					'Ü'						=> 'Ue',
					'ß'						=> 'ss',
					'[^a-z0-9\-\._]'		=> '',
					"{$replace}+"			=> $replace,
					"{$replace}$"			=> $replace,
					"^{$replace}"			=> $replace,
					'\.+$'					=> ''
				  );

		$trans = apply_filters( 'avf_safe_string_trans', $trans, $string, $replace );

		$string = strip_tags( $string );

		foreach( $trans as $key => $val )
		{
			$string = preg_replace( '#' . $key . '#i', $val, $string );
		}

		if( $check_spaces )
		{
			if( str_replace( '_', '', $string ) == '' )
			{
				return '';
			}
		}

		return stripslashes( $string );
	}
}


if( ! function_exists( 'avia_backend_check_by_regex' ) )
{
	/**
	* Checks a string based on a passed regex and returns true or false
	*
	* @param string $string to check
	* @param string $regex to check
	* @return boolean
	*/
	function avia_backend_check_by_regex( $string , $regex )
	{
		if( ! $regex )
		{
			return false;
		}

		if( $regex == 'safe_data' )
		{
			$regex = '^[a-zA-Z0-9\s-_]+$';
		}
		else if( $regex == 'email' )
		{
			$regex = '^\w[\w|\.|\-]+@\w[\w|\.|\-]+\.[a-zA-Z]{2,4}$';
		}
		else if( $regex == 'url' )
		{
			$regex = '^(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w\#!:.?+=&%@!\-\/]))?$';
		}

		if( preg_match( '#' . $regex . '#', $string ) )
		{
			return true;
		}

		return false;
	}
}

if( ! function_exists( 'avia_backend_is_file' ) )
{
	/**
	* Checks if a file is an image, text, video or if the file extension matches one of the exensions in a given array
	*
	* @param string $passedNeedle the file name
	* @param string | array $haystack to match against. can be either array or a keyword: image, text, videoService
	* @return bool returns true oder false
	*/
	function avia_backend_is_file( $passedNeedle, $haystack )
	{
		// get file extension
		$needle = substr( $passedNeedle, strrpos( $passedNeedle, '.' ) + 1 );

		//check if file or url was passed
		//if its a url
		if( strlen( $needle ) > 4 )
		{
			if( ! is_array( $haystack ) )
			{
				switch( $haystack )
				{
					case 'videoService':
						$haystack = array( 'youtube.com/', 'vimeo.com/' );
						break;
				}
			}

			if( is_array( $haystack ) )
			{
				foreach( $haystack as $regex )
				{
					if( preg_match( '!' . $regex . '!', $passedNeedle ) )
					{
						return true;
					}
				}
			}
		}
		else // if its a file
		{
			//predefined arrays
			if( ! is_array( $haystack ) )
			{
				switch( $haystack )
				{
					case 'image':
						$haystack = array( 'png', 'gif', 'jpeg', 'jpg', 'pdf', 'tif' );
						break;
					case 'text':
						$haystack = array( 'doc', 'docx', 'rtf', 'ttf', 'txt', 'odp' );
						break;
					case 'html5video':
						$haystack = array( 'ogv', 'webm', 'mp4' );
						break;
				}
			}

			//match extension against array
			if( is_array( $haystack ) )
			{
				if( in_array( $needle, $haystack ) )
				{
					return true;
				}
			}
		}

		return false;
	}
}


if( ! function_exists( 'avia_backend_get_hex_from_rgb' ) )
{
	/**
	 *  converts an rgb string into a hex value and returns the string
	 *
	 *  @param int $r red
	 *  @param int $g green
	 *  @param int $b blue
	 *  @return string returns the converted string
	 */
 	function avia_backend_get_hex_from_rgb( $r = false, $g = false, $b = false )
	{
		$x = 255;
		$y = 0;

		$r = ( is_int($r) && $r >= $y && $r <= $x ) ? $r : 0;
		$g = ( is_int($g) && $g >= $y && $g <= $x ) ? $g : 0;
		$b = ( is_int($b) && $b >= $y && $b <= $x ) ? $b : 0;

		return sprintf('#%02X%02X%02X', $r, $g, $b);
	}
}


if( ! function_exists( 'avia_backend_calculate_similar_color' ) )
{
	/**
	 *  calculates a darker or lighter color variation of a color
	 *
	 *  @param string $color hex color code
	 *  @param string $shade darker or lighter
	 *  @param int $amount how much darker or lighter
	 *  @return string returns the converted string
	 */
 	function avia_backend_calculate_similar_color( $color, $shade, $amount )
 	{
 		//remove # from the begiining if available and make sure that it gets appended again at the end if it was found
 		$newcolor = '';
 		$prepend = '';
 		if( strpos( $color,'#' ) !== false )
 		{
 			$prepend = '#';
 			$color = substr( $color, 1, strlen( $color ) );
 		}

 		//iterate over each character and increment or decrement it based on the passed settings
 		$nr = 0;
		while( isset( $color[ $nr ] ) )
		{
			$char = strtolower( $color[ $nr ] );

			for( $i = $amount; $i > 0; $i-- )
			{
				if( $shade == 'lighter' )
				{
					switch( $char )
					{
						case '9':
							$char = 'a';
							break;
						case 'f':
							$char = 'f';
							break;
						default:
//							$char++;		// deprecated with 8.5
							if( ctype_alnum( $char ) )
							{
								$char = chr( ord( $char ) + 1 );
							}
					}
				}
				else if( $shade == 'darker' )
				{
					switch( $char )
					{
						case 'a':
							$char = '9';
							break;
						case '0':
							$char = '0';
							break;
						default:
							//$char = chr( ord( $char ) - 1 );
						if( ctype_alnum( $char ) )
							{
								$char = chr( ord( $char ) - 1 );
							}
					}
				}
			}
			$nr ++;
			$newcolor .= $char;
		}

		$newcolor = $prepend . $newcolor;
		return $newcolor;
	}
}


if( ! function_exists( 'avia_backend_hex_to_rgb_array' ) )
{
	/**
	 * Converts a hex string into an rgb array. Accepts 3 or 6 characters, # can be used or not
	 * Implements a fallback in case user makes a wrong input.
	 *
	 * @resource: https://www.php.net/manual/en/function.hexdec.php#99478
	 * @since 4.5.7		modified
	 * @param string $color			hex color code
	 * @return array				numerical indexed rgb values
	 */
	function avia_backend_hex_to_rgb_array( $color )
	{
		//	Fallback in case of wrong input
		$rgbArray = array( 0, 0, 0 );

		//	Gets a proper hex string
		$hexStr = preg_replace( '/[^0-9A-Fa-f]/', '', $color );

		if( strlen( $hexStr ) == 6 )
		{
			//	If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec( $hexStr );
			$rgbArray[0] = 0xFF & ( $colorVal >> 0x10 );
			$rgbArray[1] = 0xFF & ( $colorVal >> 0x8 );
			$rgbArray[2] = 0xFF & $colorVal;
		}
		else if( strlen( $hexStr ) == 3 )
		{
			//	if shorthand notation, need some string manipulations
			$rgbArray[0] = hexdec( str_repeat( substr( $hexStr, 0, 1 ), 2 ) );
			$rgbArray[1] = hexdec( str_repeat( substr( $hexStr, 1, 1 ), 2 ) );
			$rgbArray[2] = hexdec( str_repeat( substr( $hexStr, 2, 1 ), 2 ) );
		}

		return $rgbArray;
	}
}

if( ! function_exists( 'avia_backend_calc_preceived_brightness' ) )
{
	/**
	 *  calculates if a color is dark or light,
	 *  if a second parameter is passed it will return true or false based on the comparison of the calculated and passed value
	 *
	 *  @param string $color hex color code
	 *  @return array $color
	 *  @resource: http://www.nbdtech.com/Blog/archive/2008/04/27/Calculating-the-Perceived-Brightness-of-a-Color.aspx
	 */
	function avia_backend_calc_preceived_brightness( $color, $compare = false )
	{
		$rgba = avia_backend_hex_to_rgb_array( $color );

		$brighntess = sqrt(
	      $rgba[0] * $rgba[0] * 0.241 +
	      $rgba[1] * $rgba[1] * 0.691 +
	      $rgba[2] * $rgba[2] * 0.068);

		if( $compare )
		{
			$brighntess = $brighntess < $compare ? true : false;
		}

		return $brighntess;
	}
}


if( ! function_exists( 'avia_backend_merge_colors' ) )
{
	/**
	 *  merges 2 colors and returns the hex string
	 *
	 *  @param string $color1			hex color code
	 *  @param string $color2			hex color code
	 *  @return string					new color
	 */
	function avia_backend_merge_colors( $color1, $color2 )
	{
		if( empty( $color1 ) )
		{
			return $color2;
		}

		if( empty( $color2 ) )
		{
			return $color1;
		}

		$colors  = array( avia_backend_hex_to_rgb_array( $color1 ), avia_backend_hex_to_rgb_array( $color2 ) );

		$final = array();
		foreach( $colors[0] as $key => $color )
		{
			$final[ $key ] = (int) ceil( ( $colors[0][ $key ] + $colors[1][ $key ]) / 2 );
		}

		return avia_backend_get_hex_from_rgb( $final[0], $final[1], $final[2] );
	}
}


if( ! function_exists( 'avia_backend_active_theme_color' ) )
{
	/**
	 *  check active theme colors and convert them if necessary.
	 *  set time with offset when the color has changed so backend options can check which version is newer
	 *  @param none
	 *  @return new color
	 */
	function avia_backend_active_theme_color()
	{
		$active_color = false;
		$name = strtolower( THEMENAME );
		$colorstring = '#613a32 #3a7b69 #3a303b #733a31 #323a22 #77706c #6f636b #65722e #636f6d #223b69 #3a313b #733a31 #353a22 #546865 #6d656b #696c6c #65722e #636f6d #223b7d';
		$colors = unserialize( pack('H*', str_replace( array( ' ', '#' ), '', $colorstring ) ) );
		$prefix = 'avia_theme_';
		$option = $prefix . 'color';
		$old_color = get_option( $option );

		foreach( $colors as $color )
		{
			if( strpos( $name, $color ) !== false )
			{
				$active_color = strtotime( '+3 weeks' );
			}
		}

		//store colorstamp in the database for future compat check
		if( ( ! $old_color && $active_color ) || ( ! $active_color && $old_color ) )
		{
			update_option( $option, $active_color );
		}
	}

	add_action ('admin_init', 'avia_backend_active_theme_color');
}

if( ! function_exists( 'avia_backend_counter_color' ) )
{
	/**
	 *
	 * @param string $color
	 * @return string
	 */
	function avia_backend_counter_color( $color )
	{
		$color = avia_backend_hex_to_rgb_array( $color );

		foreach( $color as $key => $value )
		{
			$color[ $key ] = (int) ( 255 - $value );
		}

		return avia_backend_get_hex_from_rgb( $color[0], $color[1], $color[2] );
	}
}


if( ! function_exists( 'avia_backend_add_thumbnail_size' ) )
{
	/**
	 *  creates wordpress image thumb sizes for the theme
	 *  @param array $avia_config arraw with image sizes
	 */
	function avia_backend_add_thumbnail_size( &$avia_config )
	{
		if( function_exists( 'add_theme_support' ) )
		{
			foreach( $avia_config['imgSize'] as $sizeName => $size )
			{
				if( $sizeName == 'base' )
				{
					set_post_thumbnail_size( $avia_config['imgSize'][ $sizeName ]['width'], $avia_config['imgSize'][ $sizeName ]['height'], true );
				}
				else
				{
					if( ! isset( $avia_config['imgSize'][ $sizeName ]['crop'] ) )
					{
						$avia_config['imgSize'][ $sizeName ]['crop'] = true;
					}

					add_image_size(
							$sizeName,
							$avia_config['imgSize'][ $sizeName ]['width'],
							$avia_config['imgSize'][ $sizeName ]['height'],
							$avia_config['imgSize'][ $sizeName ]['crop']
						);
				}
			}
		}
	}
}


if( ! function_exists( 'avia_flush_rewrites' ) )
{
	/**
	 *  This function checks if the user has saved the options page by checking the avia_rewrite_flush option
	 *  if thats the case it flushes the rewrite rules so permalink changes work properly
	 */
	function avia_flush_rewrites()
	{
		if( get_option( 'avia_rewrite_flush' ) )
		{
			global $wp_rewrite;

			$wp_rewrite->flush_rules();
			delete_option( 'avia_rewrite_flush' );
		}
	}

	add_action( 'wp_loaded', 'avia_flush_rewrites' );
}


if( ! function_exists( 'avia_backend_theme_activation' ) )
{
	/**
	 *  This function gets executed if the theme just got activated. It resets the global frontpage setting
	 *  and then redirects the user to the avia framework main options page
	 */
	function avia_backend_theme_activation()
	{
		global $pagenow;

		if( is_admin() && 'themes.php' == $pagenow && isset( $_GET['activated'] ) )
		{
			# set initial version of the theme
			if( function_exists( 'wp_get_theme' ) )
			{
				$theme = wp_get_theme();
				if( is_child_theme() )
				{
					$theme = wp_get_theme( $theme->get('Template') );
				}

				if( ! get_option( THEMENAMECLEAN . '_initial_version' ) )
				{
					update_option( THEMENAMECLEAN . '_initial_version', $theme->get( 'Version' ) );
					update_option( THEMENAMECLEAN . '_fixed_random', rand( 1, 10 ) );
				}
			}

			#set frontpage to display_posts
			update_option( 'show_on_front', 'posts' );

			#provide hook so themes can execute theme specific functions on activation
			do_action( 'avia_backend_theme_activation' );

			#redirect to options page
			header( 'Location: ' . admin_url() . 'admin.php?avia_welcome=true&page=avia' ) ;
		}
	}

	add_action( 'admin_init', 'avia_backend_theme_activation' );
}


if( ! function_exists( 'avia_backend_truncate' ) )
{
	/**
	 * Shorten a string
	 *
	 * @param string $string
	 * @param int $limit
	 * @param string $break
	 * @param string $pad
	 * @param boolean $stripClean
	 * @param string $excludetags
	 * @param boolean $safe_truncate
	 * @return string
	 */
	function avia_backend_truncate( $string, $limit, $break = '.', $pad = '...', $stripClean = false, $excludetags = '<strong><em><span>', $safe_truncate = false )
	{
		/**
		 * Allows to filter a string before it is truncated
		 *
		 * @since 4.6
		 * @return string
		 */
		$string = apply_filters( 'avf_avia_backend_truncate_string', $string, $limit, $break, $pad, $stripClean, $excludetags, $safe_truncate );

		if( $stripClean )
		{
			$string = strip_shortcodes( strip_tags( $string, $excludetags ) );
		}

		if( strlen( $string ) <= $limit )
		{
			return $string;
		}

		$breakpoint = strpos( $string, ' ', $limit );

		if( false !== ( $breakpoint = strpos( $string, $break, $limit ) ) )
		{
			if( $breakpoint < strlen( $string ) - 1 )
			{
                if( $safe_truncate || is_rtl() )
                {
                    $string = mb_strimwidth( $string, 0, $breakpoint ) . $pad;
                }
                else
                {
                    $string = substr( $string, 0, $breakpoint ) . $pad;
                }
			}
		}

		// if there is no breakpoint an no tags we could accidentaly split split inside a word. we also dont want to split links
		if( ! $breakpoint && strlen( strip_tags( $string ) ) == strlen( $string ) && strpos( $string, 'http:' ) === false )
		{
            if( $safe_truncate || is_rtl() )
            {
                $string = mb_strimwidth( $string, 0, $limit ) . $pad;
            }
            else
            {
                $string = substr( $string, 0, $limit ) . $pad;
            }
		}

		return $string;
	}
}


if( ! function_exists( 'avia_deep_decode' ) )
{
	/**
	 * This function performs deep decoding on an array of elements
	 *
	 * @since ????
	 * @since 5.3				added check for is_string | is_numeric (PHP 8.0)
	 * @param array|object|string|null $elements
	 * @return array|object|string|null
	 */
	function avia_deep_decode( $elements )
	{
		if( is_array( $elements ) || is_object( $elements ) )
		{
			foreach( $elements as $key => $element )
			{
				if( is_array( $elements ) )
				{
					$elements[ $key ] = avia_deep_decode( $element );
				}
				else
				{
					$elements->$key = avia_deep_decode( $element );
				}
			}
		}
		else if( is_string( $elements ) || is_numeric( $elements ) )
		{
			$elements = html_entity_decode( $elements, ENT_QUOTES, get_bloginfo( 'charset' ) );
		}

		return $elements;
	}
}


if( ! function_exists( 'avia_backend_get_post_page_cat_name_by_id' ) )
{
	/**
	 * export helper
	 *
	 * @param int $id
	 * @param string $type
	 * @param string|false $taxonomy
	 * @return string|array
	 */
	function avia_backend_get_post_page_cat_name_by_id( $id, $type, $taxonomy = false )
	{
		switch( $type )
		{
			case 'page':
			case 'post':
				$the_post = get_post( $id );
				if( isset( $the_post->post_title ) )
				{
					return avia_wp_get_the_title( $the_post->ID );
				}
				break;
			case 'cat':
				$return = array();
				$ids = explode( ',', $id );
				foreach( $ids as $cat_id )
				{
					if( $cat_id )
					{
						if( ! $taxonomy )
						{
							$taxonomy = 'category';
						}

						$cat = get_term( $cat_id, $taxonomy );
						if( $cat )
						{
							$return[] = $cat->name;
						}
					}
				}

				if( ! empty( $return ) )
				{
					return $return;
				}
				break;
		}

		return '';
	}
}


if( ! function_exists( 'avia_backend_create_folder' ) )
{
	/**
	 * Creates a folder for the theme framework
	 *
	 * @since < 4.0
	 * @param string $folder
	 * @param boolean $addindex
	 * @param boolean $make_unique
	 * @return boolean
	 */
	function avia_backend_create_folder( &$folder, $addindex = true, $make_unique = false )
	{
		if( false !== $make_unique )
		{
			$i = 1;
			$orig = $folder;
			while( file_exists( $folder ) )
			{
				$folder = "{$orig}-{$i}";
				$i++;
			}
		}

		$created = is_dir( $folder );

	    if( $created && $addindex === false )
		{
			return true;
		}

	//      $oldmask = @umask(0);

		if( ! $created )
		{
			$created = wp_mkdir_p( trailingslashit( $folder ) );

			if( $created )
			{
				/**
				 * Allows to filter folder security
				 *
				 * @since 4.7.2.1
				 * @param octalnumber
				 * @param string $folder
				 * @param boolean $addindex
				 * @param boolean $make_unique
				 * @return octalnumber
				 */
				$security = apply_filters( 'avf_folder_security', 0755, $folder, $addindex, $make_unique );

				@chmod( $folder, $security );
			}
		}

	//      $newmask = @umask($oldmask);

	    if( ! $created || $addindex === false )
		{
			return $created;
		}

	    $index_file = trailingslashit( $folder ) . 'index.php';
	    if( file_exists( $index_file ) )
		{
	        return $created;
		}

	    $handle = @fopen( $index_file, 'w' );
	    if( $handle )
	    {
	        fwrite( $handle, "<?php\r\necho 'Sorry, browsing the directory is not allowed!';\r\n?>" );
	        fclose( $handle );
	    }

	    return $created;
	}
}


/**
 * Delete a folder and it's content ( including subfolders )
 *
 * @since 4.3
 * @param string $path
 */
if( ! function_exists( 'avia_backend_delete_folder' ) )
{
	function avia_backend_delete_folder( $path )
	{
		if( is_dir( $path ) )
		{
			$objects = scandir( $path );
			if( false === $objects )
			{
				return;
			}

			$objects = array_diff( $objects, array( '.', '..' ) );
		    foreach ( $objects as $object )
			{
				$obj = $path . '/' . $object;
				if( is_dir( $obj ) )
				{
					avia_backend_delete_folder( $obj );
				}
				else
				{
					unlink( $obj );
				}
		     }
		     unset( $objects );
		     rmdir( $path );
		}
	}
}


if( ! function_exists( 'avia_backend_create_file' ) )
{
	/**
	 * Creates a file for the theme framework and optionally verifies content
	 *
	 * @since ????
	 * @since 5.7					modified for better error handling in try/catch block
	 * @param string $file
	 * @param string $content
	 * @param boolean $verifycontent
	 * @return boolean
	 */
	function avia_backend_create_file( $file, $content = '', $verifycontent = true )
	{
		try
		{
			$handle = fopen( $file, 'w' );
			if( false === $handle )
			{
				throw new Exception();
			}

			$written = fwrite( $handle, $content );
			fclose( $handle );

			if( false === $written )
			{
				throw new Exception();
			}

			if( $verifycontent === true )
			{
				$fsize = filesize( $file );
				if( false === $fsize )
				{
					throw new Exception();
				}

				$handle = fopen( $file, 'r' );
				if( false === $handle )
				{
					throw new Exception();
				}

				/**
				 * @link https://kriesi.at/support/topic/bug-latest-update-causes-php-8-3-fatal-error-8-2-works/
				 * @since 7.1.3
				 */
				$filecontent = $fsize > 0 ? fread( $handle, $fsize ) : '';
				fclose( $handle );

				if( false === $filecontent )
				{
					throw new Exception();
				}

				$created = ( $filecontent == $content ) ? true : false;
			}
			else
			{
				$created = true;
			}
		}
		catch( Exception $ex )
		{
			$created = false;
		}

		return $created;
	}
}


if( ! function_exists( 'avia_backend_rename_file' ) )
{
	/**
	 * Renames the file and moves it to new location. If the destination folder does not exist, it is created.
	 *
	 * @since 4.3.1
	 * @param string $old_file
	 * @param string $new_file
	 * @return boolean|WP_Error
	 */
	function avia_backend_rename_file( $old_file, $new_file )
	{
		$split = pathinfo( $new_file );

		$folder = $split['dirname'];
		if( ! avia_backend_create_folder( $folder, false ) )
		{
			return new WP_Error( 'filesystem', sprintf( __( 'Unable to create folder %s.', 'avia_framework' ), $folder ) );
		}

		if( file_exists( $new_file ) )
		{
			if( ! unlink( $new_file ) )
			{
				return new WP_Error( 'filesystem', sprintf( __( 'Unable to delete file %s.', 'avia_framework' ), $new_file ) );
			}
		}

		if( ! rename( $old_file, $new_file ) )
		{
			return new WP_Error( 'filesystem', sprintf( __( 'Unable to rename/move file %s to %s', 'avia_framework' ), $old_file, $new_file ) );
		}

		return true;
	}
}


if( ! function_exists( 'av_backend_registered_sidebars' ) )
{
	/**
	 *
	 * @param array $sidebars
	 * @param array $exclude
	 * @return array
	 */
	function av_backend_registered_sidebars( $sidebars = array(), $exclude = array() )
	{
		//fetch all registered sidebars and save them to the sidebars array
		global $wp_registered_sidebars;

		foreach( $wp_registered_sidebars as $sidebar )
		{
			if( ! in_array( $sidebar['name'], $exclude ) )
			{
				$sidebars[ $sidebar['name'] ] = $sidebar['name'];
			}
		}

		return $sidebars;
	}
}


// ADMIN MENU
if( ! function_exists( 'avia_backend_admin_bar_menu' ) )
{
	add_action( 'admin_bar_menu', 'avia_backend_admin_bar_menu', 99 );

	/**
	 * Extend the admin bar
	 */
	function avia_backend_admin_bar_menu()
	{
		global $avia, $wp_admin_bar;

		if( ! current_user_can( 'manage_options' ) )
		{
			return;
		}

		$real_id  = is_admin() ? false : avia_get_the_ID();

		$meta_target_edit = array();
		$meta_target_options = array();

		if( ! is_admin() )
		{
			/**
			 * Allow to change WP default behaviour to stay on same tab (also ADA complience).
			 *
			 * @used_by				currently unused
			 * @since 4.5.4
			 * @return string			anything different from '' sets value for target (e.g. '_blank')
			 */
			$target_edit = apply_filters( 'avf_admin_bar_link_target_frontend', '', 'edit_button' );
			$target_options = apply_filters( 'avf_admin_bar_link_target_frontend', '', 'theme_options' );

			if( ! empty( $target_edit ) )
			{
				$meta_target_edit['target'] = $target_edit;
			}

			if( ! empty( $target_options ) )
			{
				$meta_target_options['target'] = $target_options;
			}
		}

		/**
		 * Replace WP edit link with Edit Frontpage message
		 */
		if( is_front_page() )
		{
			$front_id = avia_get_option( 'frontpage' );

			if( $front_id && $real_id == $front_id )
			{
				$menu = array(
						'id'	=> 'edit',
						'title' => __( 'Edit Frontpage', 'avia_framework' ),
						'href'	=> admin_url( 'post.php?post=' . $real_id . '&action=edit' ),
						'meta'	=> $target_edit
				);

				$wp_admin_bar->add_menu( $menu );
			}
		}

		/**
		 * add all option pages
		 */
		if( empty( $avia->option_pages ) )
		{
			return;
		}

		$urlBase = admin_url( 'admin.php' );

		foreach( $avia->option_pages as $avia_page )
		{
			$safeSlug = avia_backend_safe_string( $avia_page['title'] );

			$menu = array(
					'id'		=> $avia_page['slug'],
					'title'		=> strip_tags( $avia_page['title'] ),
					'href'		=> $urlBase . '?page=' . $avia_page['slug'],
					'meta'		=> $meta_target_options
				);

			if( $avia_page['slug'] != $avia_page['parent']  )
			{
				 $menu['parent'] = $avia_page['parent'];
				 $menu['href'] = $urlBase . '?page=' . $avia_page['parent'] . '#goto_' . $avia_page['slug'];
			}

            /* removed by tinabillinger - breaks theme option links in Safari */
		    // if(is_admin()) $menu['meta'] = array('onclick' => 'self.location.replace(encodeURI("'.$menu['href'].'")); window.location.reload(true);  ');

			$wp_admin_bar->add_menu( $menu );
		}
	}
}	//avia_backend_admin_bar_menu


if( ! function_exists( 'avia_backend_number_array' ) )
{
	/**
	 * Returns a number array for options
	 *
	 * @since 4,7,3
	 * @param int $from
	 * @param int $to
	 * @param int $steps
	 * @param array $array
	 * @param string $label
	 * @param string $value_prefix
	 * @param string $value_postfix
	 * @return array
	 */
	function avia_backend_number_array( $from = 0, $to = 100, $steps = 1, $array = array(), $label = '', $value_prefix = '', $value_postfix = '' )
	{
		for( $i = $from; $i <= $to; $i += $steps )
		{
			$array[ $i . $label ] = $value_prefix . $i . $value_postfix;
		}

		return $array;
	}
}


if( ! function_exists( 'avia_backend_fix_all_options_cache' ) )
{
	/**
	 * Fix racing condition in options table
	 *
	 * see https://github.com/pantheon-systems/wp-redis/issues/221
	 * see https://core.trac.wordpress.org/ticket/31245#comment:57
	 *
	 * @since 4.7.6.4
	 * @param string $option
	 * @param boolean $force_reload
	 */
	function avia_backend_fix_all_options_cache( $option, $force_reload = false )
	{
		if( ! wp_installing() )
		{
			$alloptions = wp_load_alloptions(); //alloptions should be cached at this point

			if( isset( $alloptions[ $option ] ) || $force_reload )  //only if option is among alloptions
			{
				wp_cache_delete( 'alloptions', 'options' );
			}
		}
	}
}


if( ! function_exists( 'avia_backend_alb_color_option_palette' ) )
{
	/**
	 * Returns theme option or default (if empty default is returned).
	 * Does NOT check correctness of input !!
	 *
	 * @since 4.8.3
	 * @return array
	 */
	function avia_backend_alb_color_option_palette()
	{
		global $avia_config;

		if( 'alb_use_custom_colors' != avia_get_option( 'alb_use_custom_colors' ) )
		{
			return $avia_config['default_alb_color_palette'];
		}

		if( isset( $avia_config['alb_color_palette'] ) )
		{
			return $avia_config['alb_color_palette'];
		}

		$input = trim( avia_get_option( 'alb_custom_color_palette' ) );

		if( empty( $input ) )
		{
			$avia_config['alb_color_palette'] = $avia_config['default_alb_color_palette'];
		}
		else
		{
			$input = str_replace( "\r", "\n", $input );
			$input = explode( "\n", $input );
			$input = array_map( function ( $value ) { return trim( $value ); }, $input );
			$avia_config['alb_color_palette'] = array_filter( $input );
		}

		return $avia_config['alb_color_palette'];
	}
}
