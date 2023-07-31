<?php
/**
 * This file holds various helper functions that are needed by the frameworks FRONTEND
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright (c) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 */
if( ! defined( 'AVIA_FW' ) ) {   exit( 'No direct script access allowed' );   }


if( ! function_exists( 'avia_option' ) )
{
	/**
	 * This function serves as shortcut for avia_get_option and is used to retrieve options saved within the database with the first key set to "avia" which is the majority of all options
	 * Please note that while the get_avia_option returns the result, this function echos it by default. if you want to retrieve an option and store the variable please use get_avia_option or set $echo to false
	 *
	 * basically the function is called like this: avia_option('portfolio');
	 * That would retrieve the following var saved in the global $avia superobject: $avia->options['avia']['portfolio']
	 * If you want to set a default value that is returned in case there was no array match you need to use this scheme:
	 *
	 * avia_option( 'portfolio', "my default");
	 *
	 * @deprecated 4.9
	 * @param string $key accepts a comma separated string with keys
	 * @param string $default return value in case we got no result
	 * @param bool $echo echo the result or not, default is to false
	 * @param bool $decode decode the result or not, default is to false
	 * @return string $result: the saved result. if no result was saved or the key doesnt exist returns an empty string
	 */
	function avia_option( $key, $default = '', $echo = true, $decode = true )
	{
		_deprecated_function( 'avia_option', '4.9', 'use avia_get_option instead - will be removed in future' );

		$result = avia_get_option( $key, $default, false, $decode );

		if( ! $echo)
		{
			return $result; //if we dont want to echo the output end script here
		}

		echo $result;
	}
}


if( ! function_exists( 'avia_get_option' ) )
{
	/**
	 * This function serves as shortcut to retrieve options saved within the database by the option pages of the avia framework
	 *
	 * basically the function is called like this: avia_get_option('portfolio');
	 * That would retrieve the following var saved in the global $avia superobject: $avia->options['avia']['portfolio']
	 * If you want to set a default value that is returned in case there was no array match OR value is '' (this is different behaviour from get_option() !!!! )
	 * you need to use this scheme:
	 *
	 * avia_get_option( 'portfolio', 'my default' ); or
	 * avia_get_option( array( 'avia','portfolio' ), 'my default' );
	 *
	 * @since 4.8						support for multiple option pages
	 * @param string|array|false $key	see above
	 * @param string $default			return value in case we got no result - and - if value of option = '' ( !!!!! )
	 * @param bool $echo				echo the result or not, default is to false
	 * @param bool $decode				decode the result or not, default is to false
	 * @return mixed					the saved result. if no result was saved, the key doesn't exist or is '' returns $default
	 */
	function avia_get_option( $key = false, $default = '', $echo = false, $decode = true )
	{
		global $avia;

		/**
		 * This fixed a problem with WP CLI: wp cache flush
		 *
		 *		Trying to get property of non-object $avia
		 *
		 * Adding global $avia; to framework\avia_framework.php did the final solution - we keep this for a fallback only.
		 */
		if( ! $avia instanceof avia_superobject )
		{
			$avia = AviaSuperobject();
		}

		$pages = array();
		$opt_key = '';
		$result = null;

		if( false === $key )
		{
			//	Return 'avia' page array
			$result = isset( $avia->options['avia'] ) ? $avia->options['avia'] : array();
		}
		else if( is_array( $key ) && count( $key ) == 0 )
		{
			//	fallback -> we return default value
			$result = $default;
		}
		else if( is_array( $key ) && count( $key ) == 1 )
		{
			//	@since 4.8 return requested page array
			$result = isset( $avia->options[ $key[0] ] ) ? $avia->options[ $key[0] ] : array();
		}
		else
		{
			if( is_array( $key ) && count( $key ) > 1 )
			{
				$pages[] = isset( $key[0] ) ? $key[0] : '';
				$opt_key = isset( $key[1] ) ? $key[1] : '';
			}
			else
			{
				//	force avia to be the main array to search for option
				$pages[] = 'avia';
				$pages = array_unique( array_merge( $pages, array_keys( $avia->options ) ) );
				$opt_key = $key;
			}

			//	Scan all pages for option
			foreach( $pages as $page )
			{
				if( isset( $avia->options[ $page ] ) && isset( $avia->options[ $page ][ $opt_key ] ) )
				{
					$result = $avia->options[ $page ][ $opt_key ];
					break;
				}
			}

			/**
			 * !!!!  Different behaviour as get_option():
			 *
			 * Also return $default when options value is ''
			 */
			if( is_null( $result ) || ( '' == $result ) )
			{
				$result = $default;
			}
		}

		if( $decode )
		{
			$result = avia_deep_decode( $result );
		}

		if( $echo )
		{
			echo $result;
		}

		return $result;
	}
}


if( ! function_exists( 'avia_update_option' ) )
{
	/**
	 * This function serves as shortcut to update a single theme option
	 * You must specify the correct option page.
	 *
	 * @since ????
	 * @since 4.9						support multiple option pages
	 * @param string|array $key			'my_option' | array( 'page_slug', 'my_option' ) | array( 'my_option' ) ==> array( 'avia', 'my_option' )
	 * @param mixed $value
	 */
	function avia_update_option( $key, $value = '' )
	{
		global $avia;

		$page = 'avia';

		if( is_array( $key ) && count( $key ) == 1 )
		{
			$option_key = $key[0];
		}
		else if( is_array( $key ) && count( $key ) > 1 )
		{
			$page = $key[0];
			$option_key = $key[1];
		}
		else
		{
			$option_key = $key;
		}

		$avia->options[ $page ][ $option_key ] = $value;

		update_option( $avia->option_prefix, $avia->options );
	}
}


if( ! function_exists( 'avia_delete_option' ) )
{
	/**
	 * This function serves as shortcut to delete a single theme option
	 * You must specify the correct option page.
	 *
	 * @since ????
	 * @since 4.9						support multiple option pages
	 * @param string|array $key			'my_option' | array( 'page_slug', 'my_option' ) | array( 'my_option' ) ==> array( 'avia', 'my_option' )
	 */
	function avia_delete_option( $key )
	{
		global $avia;

		$page = 'avia';

		if( is_array( $key ) && count( $key ) == 1 )
		{
			$option_key = $key[0];
		}
		else if( is_array( $key ) && count( $key ) > 1 )
		{
			$page = $key[0];
			$option_key = $key[1];
		}
		else
		{
			$option_key = $key;
		}

		unset( $avia->options[ $page ][ $option_key ] );

		update_option( $avia->option_prefix , $avia->options );
	}
}

if( ! function_exists( 'avia_minify_extension' ) )
{
	/**
	 * Returns .min if option is set to load minified js or css files
	 *
	 * @since 5.2
	 * @param string $which
	 * @return string
	 */
	function avia_minify_extension( $which = 'js' )
	{
		switch( $which )
		{
			case 'js':
				$key = 'merge_js';
				break;
			case 'css':
				$key = 'merge_css';
				break;
			default:
				return '';
		}

		return 'minified_only' == avia_get_option( $key, 'none' ) ? '.min' : '';
	}
}


if( ! function_exists( 'avia_get_the_ID' ) )
{
	/**
	* This function is similiar to the wordpress function get_the_ID, but other than the wordpress function this functions takes into account
	* if we will display a different post later on, a post that differs from the one we queried in the first place. The function also holds this
	* original ID, even if another query is then executed (for example in dynamic templates for columns)
	*
	* an example would be the frontpage template were by default, the ID of the latest blog post is served by wordpress get_the_ID function.
	* avia_get_the_ID would return the same blog post ID if the blog is really displayed on the frontpage. if a static page is displayed the
	* function will display the ID of the static page, even if the page is not yet queried
	*
	* @return int $ID: the "real" ID of the post/page we are currently viewing
	*/
	function avia_get_the_ID()
	{
		global $avia_config;

		$ID = false;

		if( ! isset( $avia_config['real_ID'] ) )
		{
			if( ! empty( $avia_config['new_query']['page_id'] ) )
			{
				$ID = $avia_config['new_query']['page_id'];
				$avia_config['real_ID'] = $ID;
			}
			else
			{
				$post = get_post();
				if( isset( $post->ID ) )
				{
					$ID = $post->ID;
					$avia_config['real_ID'] = $ID;
				}
				else
				{
					$ID = false;
				}
				//$ID = @get_the_ID();
			}
		}
		else
		{
			$ID = $avia_config['real_ID'];
		}

		/**
		 * @since ???
		 * @param int $ID
		 * @return int
		 */
		$ID = apply_filters( 'avf_avia_get_the_ID', $ID );

		return $ID;
	}

	add_action( 'wp_head', 'avia_get_the_ID' );
}


if( ! function_exists( 'avia_is_overview' ) )
{
	/**
	* This function checks if the page we are going to render is a page with a single entry or a multi entry page (blog or archive for example)
	*
	* @return bool $result true or false
	*/
	function avia_is_overview()
	{
		global $avia_config;

		$result = true;

		if( is_singular() )
		{
			$result = false;
		}

		if( is_front_page() && avia_get_option( 'frontpage' ) == avia_get_the_ID() )
		{
			$result = false;
		}

		if( isset( $avia_config['avia_is_overview'] ) )
		{
			$result = $avia_config['avia_is_overview'];
		}

		return $result;
	}
}

if( ! function_exists( 'avia_post_meta' ) )
{
	/**
	 * This function retrieves the custom field values for a given post and saves it to the global avia config array
	 * If a subkey was set the subkey is returned, otherwise the array is saved to the global config array
	 * The function also hooks into the post loop and is automatically called for each post
	 *
	 * @param int|string $post_id
	 * @param string|false $subkey
	 * @return false|mixed
	 */
	function avia_post_meta( $post_id = '', $subkey = false )
	{
		$avia_post_id = $post_id;

		//if the user only passed a string and no id the string will be used as subkey
		if( ! $subkey && $avia_post_id != '' && ! is_numeric( $avia_post_id ) && ! is_object( $avia_post_id ) )
		{
			$subkey = $avia_post_id;
			$avia_post_id = '';
		}

		global $avia, $avia_config;

		$key = '_avia_elements_' . $avia->option_prefix;
		if( current_theme_supports( 'avia_post_meta_compat' ) )
		{
			$key = '_avia_elements_theme_compatibility_mode'; //activates a compatibility mode for easier theme switching and keeping post options
		}

		//if post id is on object the function was called via hook. If thats the case reset the meta array
		if( is_object( $avia_post_id ) && isset( $avia_post_id->ID ) )
		{
			$avia_post_id = $avia_post_id->ID;
		}

		if( ! $avia_post_id )
		{
			$avia_post_id = @get_the_ID();
		}

		if( ! is_numeric( $avia_post_id ) )
		{
			return false;
		}

		$avia_config['meta'] = avia_deep_decode( get_post_meta( $avia_post_id, $key, true ) );

		/**
		 * @since ???
		 * @param array $avia_config['meta']
		 * @param int $avia_post_id
		 * @return array
		 */
		$avia_config['meta'] = apply_filters( 'avia_post_meta_filter', $avia_config['meta'], $avia_post_id );

		if( $subkey && isset( $avia_config['meta'][ $subkey ] ) )
		{
			$meta = $avia_config['meta'][ $subkey ];
		}
		else if( $subkey )
		{
			$meta = false;
		}
		else
		{
			$meta = $avia_config['meta'];
		}

		return $meta;
	}

	add_action( 'the_post', 'avia_post_meta' );
}

if( ! function_exists( 'avia_get_modified_option' ) )
{
	/**
	* This function returns an option that was set in the backend. However if a post meta key with the same name exists it retrieves this option instead
	* That way we can easily set global settings for all posts in our backend (for example slideshow duration options) and then overrule those options
	*
	* In addition to the option key we need to pass a second key for a post meta value that must return a value other then empty before the global settings can be overwritten.
	* (example: should ths post use overwritten options? no=>'' yes=>"yes")
	*
	* @param string $key			database key for both the post meta table and the framework options table
	* @param string $extra_check	database key for both a post meta value that needs to be true in order to accept an overwrite
	* @return string				$result: the saved result. if no result was saved or the key doesnt exist returns an empty string
	*/
	function avia_get_modified_option( $key, $extra_check = false )
	{
		global $post;

		//if we need to do an extra check get the post meta value for that key
		if( $extra_check && isset( $post->ID ) )
		{
			$extra_check = get_post_meta( $post->ID, $extra_check, true );
			if( $extra_check )
			{
				//add underline to the post meta value since we always hide those values
				$result = get_post_meta( $post->ID, '_' . $key, true );
				return $result;
			}
		}

		$result = avia_get_option( $key );
		return $result;
	}
}


if( ! function_exists( 'avia_set_follow' ) )
{
	/**
	 * prevents duplicate content by setting archive pages to nofollow
	 * @return string the robots meta tag set to index follow or noindex follow
	 */
	function avia_set_follow()
	{
		$robots = avia_get_option( 'seo_robots', '' );
		$blog_public = (int) get_option( 'blog_public', 0 );

		$meta = '';

		if( empty( $robots ) )
		{
			if( ( $blog_public === 0 ) || is_search() )
			{
				$meta .= '<meta name="robots" content="noindex, nofollow" />' . "\n";
			}
			else if( ( is_single() || is_page() || is_home() ) && ( ! is_paged() ) )
			{
				$meta .= '<meta name="robots" content="index, follow" />' . "\n";
			}
			else
			{
				$meta .= '<meta name="robots" content="noindex, follow" />' . "\n";
			}
		}

		/**
		 *
		 * @param string $meta
		 * @param string $robots			@since 4.7.5.1
		 * @param int $blog_public			@since 4.7.6.2
		 * @return string
		 */
		$meta = apply_filters( 'avf_set_follow', $meta, $robots, $blog_public );

		return $meta;
	}
}

if( ! function_exists( 'avia_set_profile_tag' ) )
{
	/**
	 * generates the html profile head tag
	 *
	 * @param boolean $echo
	 * @return string				the html head tag
	 */
	function avia_set_profile_tag( $echo = true )
	{
		$output = apply_filters( 'avf_profile_head_tag', '<link rel="profile" href="http://gmpg.org/xfn/11" />' . "\n" );

		if( $echo )
		{
			echo $output;
			return;
		}

		return $output;
	}

	add_action( 'wp_head', 'avia_set_profile_tag', 10, 0 );
}


if( ! function_exists( 'avia_set_rss_tag' ) )
{
	/**
	 * generates the html rss head tag
	 *
	 * @param boolean $echo
	 * @return string				the rss head tag
	 */
	function avia_set_rss_tag( $echo = true )
	{
		$output = '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo( 'name' ) . ' RSS2 Feed" href="' . avia_get_option( 'feedburner', get_bloginfo( 'rss2_url' ) ) . '" />' . "\n";
		$output = apply_filters( 'avf_rss_head_tag', $output );

		if( $echo )
		{
			echo $output;
			return;
		}

		return $output;
	}

	add_action( 'wp_head', 'avia_set_rss_tag', 10, 0 );
}


if( ! function_exists( 'avia_set_pingback_tag' ) )
{
	/**
	 * generates the html pingback head tag
	 *
	 * @param boolean $echo
	 * @return string					the pingback head tag
	 */
	function avia_set_pingback_tag( $echo = true )
	{
		$output = apply_filters( 'avf_pingback_head_tag', '<link rel="pingback" href="' . get_bloginfo( 'pingback_url' ) . '" />' . "\n" );

		if( $echo )
		{
			echo $output;
			return;
		}

		return $output;
    }

	add_action( 'wp_head', 'avia_set_pingback_tag', 10, 0 );
}


if( ! function_exists( 'avia_logo' ) )
{
	/**
	 * return the logo of the theme. if a logo was uploaded and set at the backend options panel display it
	 * otherwise display the logo file linked in the css file for the .bg-logo class
	 *
	 * @since < 4.0
	 * @param string $use_image				fallback in case selected logo in theme options not exists !!!!
	 * @param string $sub
	 * @param string $headline_type
	 * @param string|true $dimension
	 * @return string			the logo + url
	 */
	function avia_logo( $use_image = '', $sub = '', $headline_type = 'h1', $dimension = '' )
	{
//		$use_image = apply_filters( 'avf_logo', $use_image );	//	since 4.5.7.2 changed as inconsistently used again when logo is set
		$headline_type = apply_filters( 'avf_logo_headline', $headline_type );
		$sub = apply_filters( 'avf_logo_subtext',  $sub );
		$alt = apply_filters( 'avf_logo_alt', get_bloginfo( 'name' ) );
		$link = apply_filters( 'avf_logo_link', home_url( '/' ) );

		$title = '';
		$logo_id = 0;
		$has_svg = false;

		if( $sub )
		{
			$subclass = 'subtext';
			if( false !== strpos( $sub, '<svg ' ) )
			{
				$subclass .= ' avia-svg-logo-sub';
				$has_svg = true;
			}
			else if( false !== strpos( $sub, '.svg' ) )
			{
				$subclass .= ' avia-img-svg-logo-sub';
				$has_svg = true;
			}
			else
			{
				$subclass .= ' avia-standard-logo-sub';
			}

			$sub = "<span class='{$subclass}'>{$sub}</span>";
		}

		if( $dimension === true )
		{
			/**
			 * Basically just for better page speed ranking :P
			 * Be sure to return a valid attribute string.
			 *
			 * @since 4.8
			 * @param string $dimensions
			 * @return string
			 */
			$dimension = apply_filters( 'avf_logo_dimension', 'height="100" width="300"' );
		}

		$logo = avia_get_option( 'logo' );
		if( ! empty( $logo ) )
		{
			/**
			 * @since 4.5.7.2
			 * @param string|int $logo
			 * @param string $context
			 * @return string|int
			 */
			$logo = apply_filters( 'avf_logo', $logo, 'option_set' );

			/**
			 * @since 4.8.2 support for responsive logo for retina screens
			 * @since 4.8.4 theme options provide by default url and not attachment id - try to find corresponding id
			 */
			$logo = Av_Responsive_Images()->attachment_url_to_postid( $logo );

			if( is_numeric( $logo ) )
			{
				$logo_id = $logo;

				//	@since 4.8.2 support for responsive logo for retina screens
				$logo_src = Av_Responsive_Images()->responsive_image_src( $logo_id, 'full' );

				if( is_array( $logo_src ) )
				{
				   $title = get_the_title( $logo_id );
				   $logo = $logo_src;
				}
				else
				{
					/**
					 * This is a fallback only in case logo image was deleted - we ignore responsive image here by default
					 * Filter allows to return responsive image structure (see $logo_src above)
					 *
					 * @since 4.8.4
					 * @param string $use_image
					 * @param int $logo_id
					 * @param string $context
					 */
					$logo = apply_filters( 'avf_logo_use_image', $use_image, $logo_id, 'image_deleted' );
				}
			}

			/**
			 * @since 4.5.7.2
			 * @param string $title
			 * @param string $context
			 * @return string
			 */
			$title = apply_filters( 'avf_logo_title', $title, 'option_set' );

			if( empty( $logo ) )
			{
				//	provide a fallback in case logo image was removed and no fallback image
				$blog_name = get_bloginfo( 'name' );

				$logo = "<{$headline_type} class='logo bg-logo'><a href='{$link}'>{$blog_name}{$sub}</a></{$headline_type}>";
			}
			else
			{
				$logo_url = is_array( $logo ) ? $logo[0] : $logo;
				$logo_class = 'logo';

				if( ! avia_SVG()->exists_svg_file( $logo_url, $logo_id ) )
				{
					$logo_class .= avia_SVG()->is_svg( $logo_url ) ? ' avia-img-svg-logo' : ' avia-standard-logo';
					$resp_attr = Av_Responsive_Images()->html_attr_image_src( $logo, true );

					/**
					 * https://kriesi.at/support/topic/logo-srcset/
					 *
					 * Bug that WP removes scrset and sizes when width and/or height is defined
					 * @since 4.8.3
					 * @since 4.8.4 removed  (https://kriesi.at/support/topic/logo-srcset/#post-1309955)
					 */
					if( false !== strpos( $resp_attr, 'srcset' ) || false !== strpos( $resp_attr, 'sizes' ) )
					{
	//					$dimension = '';
					}

					$logo_img = "<img {$resp_attr} {$dimension} alt='{$alt}' title='{$title}' />";
				}
				else
				{
					$logo_class .= ' avia-svg-logo';
					$logo_img = avia_SVG()->get_html( $logo_id, $logo_url, avia_SVG()->get_header_logo_aspect_ratio(), 'html', $title );
				}

				if( false !== strpos( $logo_class, '-svg-' ) )
				{
					$has_svg = true;
				}

				$a_class = $has_svg ? 'av-contains-svg' : '';

				$aria = '';
				if( ! empty( $title ) )
				{
					$aria = "aria-label='{$title}'";
				}
				else if( ! empty( $alt ) )
				{
					$aria = "aria-label='{$alt}'";
				}
				else
				{
					$aria = 'aria-label="' . __( 'Logo', 'avia_framework' ) . '"';
				}

				/**
				 * Return a complete modified aria-label="" attribute string
				 *
				 * @since 5.6.5
				 * @param string $aria
				 * @return string
				 */
				$aria = apply_filters( 'avf_avia_logo_link_aria', $aria );


				$link_title = ! empty( $title ) ? "title='{$title}'" : '';

				/**
				 * Return a complete modified title="" attribute string
				 * If you want to use it as tooltip you must REMOVE title attribute from image
				 *
				 * @since 5.6.5
				 * @param string $link_title
				 * @param string $title
				 * @param string $alt
				 * @return string
				 */
				$link_title = apply_filters( 'avf_avia_logo_link_title', $link_title, $title, $alt );

				$logo = "<{$headline_type} class='{$logo_class}'><a href='{$link}' class='{$a_class}' {$aria} {$link_title}>{$logo_img}{$sub}</a></{$headline_type}>";
			}
		}
		else
		{
			$logo = get_bloginfo( 'name' );
			$title = sprintf( __( 'Logo for %s', 'avia_framework' ), $logo );

			/**
			 * @since 4.5.7.2
			 * @param string $use_image
			 * @param string $context
			 * @return string
			 */
			$use_image = apply_filters( 'avf_logo', $use_image, 'option_not_set' );

			if( ! empty( $use_image ) )
			{
				$img_title = '';

				//	@since 4.8.4 support for responsive logo for retina screens
				$use_image = Av_Responsive_Images()->attachment_url_to_postid( $use_image );
				if( is_numeric( $use_image ) )
				{
					$use_image_src = Av_Responsive_Images()->responsive_image_src( $use_image, 'full' );
					$img_title = get_the_title( $use_image );

					$resp_attr = Av_Responsive_Images()->html_attr_image_src( $use_image_src, true );

					/**
					 * Bug that WP removes scrset and sizes when width and/or height is defined
					 */
					if( false === strpos( $resp_attr, 'srcset' ) && false === strpos( $resp_attr, 'sizes' ) )
					{
						$resp_attr .= ' ' . $dimension;
					}
				}
				else
				{
					$resp_attr = "src='{$use_image}' {$dimension}";
					$img_title = $title;
				}

				if( ! empty( $img_title ) )
				{
					$img_title = 'title="' . esc_attr( $img_title ) . '"';
				}

				$logo = "<img {$resp_attr} alt='{$alt}' {$img_title} />";
			}

			/**
			 * @since 4.5.7.2
			 * @param string $logo
			 * @param string $context
			 * @return string
			 */
			$title = apply_filters( 'avf_logo_title', $title, 'option_not_set' );


			$aria = '';
			if( ! empty( $title ) )
			{
				$aria = "aria-label='{$title}'";
			}
			else if( ! empty( $alt ) )
			{
				$aria = "aria-label='{$alt}'";
			}
			else
			{
				$aria = 'aria-label="' . __( 'Logo', 'avia_framework' ) . '"';
			}

			/**
			 * Return a complete modified aria-label="" attribute string
			 *
			 * @since 5.6.5
			 * @param string $aria
			 * @return string
			 */
			$aria = apply_filters( 'avf_avia_logo_link_aria_label', $aria );


			$link_title = ! empty( $title ) ? "title='{$title}'" : '';

			/**
			 * Return a complete modified title="" attribute string
			 * If you want to use it as tooltip you must REMOVE title attribute from image
			 *
			 * @since 5.6.5
			 * @param string $link_title
			 * @param string $title
			 * @param string $alt
			 * @return string
			 */
			$link_title = apply_filters( 'avf_avia_logo_link_title', $link_title, $title, $alt );


			$logo = "<{$headline_type} class='logo bg-logo'><a href='{$link}' {$aria} {$link_title}>{$logo}{$sub}</a></{$headline_type}>";
		}

		/**
		 *
		 * @since < 4.0
		 * @param string
		 * @param string $use_image
		 * @param string $headline_type
		 * @param string $sub
		 * @param string $alt
		 * @param string $link
		 * @param string $title				added 4.5.7.2
		 * @return string
		 */
		$logo = apply_filters( 'avf_logo_final_output', $logo, $use_image, $headline_type, $sub, $alt, $link, $title );

		return $logo;
	}
}


if( ! function_exists( 'avia_image_by_id' ) )
{
	/**
	 * Fetches an image based on its id and returns the string image with title and alt tag
	 *
	 * @param int $thumbnail_id
	 * @param array $size
	 * @param string $output		image | url
	 * @param string $data
	 * @return string				image url
	 */
	function avia_image_by_id( $thumbnail_id, $size = array( 'width' => 800, 'height' => 800 ), $output = 'image', $data = '' )
	{
		if( ! is_numeric( $thumbnail_id ) )
		{
			return '';
		}

		if( is_array( $size ) )
		{
			$size[0] = $size['width'];
			$size[1] = $size['height'];
		}

		// get the image with appropriate size by checking the attachment images
		$image_src = wp_get_attachment_image_src( $thumbnail_id, $size );

		//if output is set to url return the url now and stop executing, otherwise build the whole img string with attributes
		if( $output == 'url' )
		{
			return is_array( $image_src ) ? $image_src[0] : '';
		}

		//get the saved image metadata:
		$attachment = get_post( $thumbnail_id );

		if( is_object( $attachment ) && is_array( $image_src ) )
		{
			$image_description = $attachment->post_excerpt == '' ? $attachment->post_content : $attachment->post_excerpt;
			if( empty( $image_description ) )
			{
				$image_description = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
			}

			$image_description = trim( strip_tags( $image_description ) );
			$image_title = trim( strip_tags( $attachment->post_title ) );

			return "<img src='{$image_src[0]}' title='{$image_title}' alt='{$image_description}' {$data} />";
		}

		return '';
	}
}


if( ! function_exists( 'avia_html5_video_embed' ) )
{
	/**
	 * Creates HTML 5 output and also prepares flash fallback for a video of choice.
	 *
	 *
	 * @since 4.6.4						supports user defined html 5 files
	 * @param string|array $video		array(  fileext => file url  )
	 * @param string $image
	 * @param array $types
	 * @param array $attributes
	 * @return string					HTML5 video element
	 */
	function avia_html5_video_embed( $video, $image = '', $types = array( 'webm' => 'type="video/webm"', 'mp4' => 'type="video/mp4"', 'ogv' => 'type="video/ogg"' ), $attributes = array( 'autoplay' => 0, 'loop' => 1, 'preload' => '', 'muted' => '', 'controls' => ''  ) )
	{
		$html5_files = array();
		$path = $video;

		if( ! empty( $video ) && is_array( $video ) )
		{
			$html5_files = $video;
			$path = reset( $video );
		}

		$path_split = array();
		preg_match( "!^(.+?)(?:\.([^.]+))?$!", $path, $path_split );

		$output = '';
		if( isset( $path_split[1] ) )
		{
			if( ! $image && avia_is_200( $path_split[1] . '.jpg' ) )
			{
				$image = 'poster="' . $path_split[1] . '.jpg"'; // poster image isn't accepted by the player currently, waiting for bugfix
			}
			else if( $image )
			{
				$image = 'poster="' . $image . '"';
			}


			$autoplay = $attributes['autoplay'] == 1 ? 'autoplay' : '';

			if( ! empty( $autoplay ) )
			{
				/**
				 * Add playsinline for IOS https://kriesi.at/support/topic/autoplay-on-ios/
				 * Allow to modify if video is hidden on mobile (not done by default because of multiple mobile device selections)
				 *
				 * @since 4.8.8.1
				 * @param string $autoplay
				 * @param string|array $video		array(  fileext => file url  )
				 * @param array $attributes
				 * @return string
				 */
				$autoplay = apply_filters( 'avf_html5_autoplay_mobile', "{$autoplay} playsinline", $video, $attributes );
			}

			$loop = $attributes['loop'] == 1 ? 'loop' : '';
			$muted = $attributes['muted'] == 1 ? 'muted' : '';
			$controls = $attributes['controls'] == 1 ? 'controls' : '';

			if( ! empty( $attributes['preload'] ) )
			{
				$metadata = 'preload="' . $attributes['preload'] . '"';
			}
			else
			{
				$metadata = $attributes['loop'] == 1 ? 'preload="metadata"' : 'preload="auto"';
			}

			$uid = 'player_' . get_the_ID() . '_' . mt_rand() . '_' . mt_rand();

			$output .= "<video class='avia_video' {$image} {$autoplay} {$loop} {$metadata} {$muted} {$controls} id='{$uid}'>";

			if( empty( $html5_files ) )
			{
				foreach ( $types as $key => $type )
				{
					if( $path_split[2] == $key || avia_is_200( $path_split[1] . '.' . $key ) )
					{
						$output .=	'<source src="' . $path_split[1] . '.' . $key.'" ' . $type . ' />';
					}
				}
			}
			else
			{
				foreach( $html5_files as $ext => $source )
				{
					$html_type = ! empty( $types[ $ext ] ) ? $types[ $ext ] : '';

					$output .=		"<source src='{$source}' {$html_type} />";
				}
			}

			$output .= '</video>';
		}

		return $output;
	}
}


if( ! function_exists( 'avia_html5_audio_embed' ) )
{
	/**
	 * Creates HTML 5 output and also prepares flash fallback for a audio of choice
	 *
	 * @param string $path
	 * @param string $image
	 * @param array $types
	 * @return string					HTML5 audio element
	 */
	function avia_html5_audio_embed( $path, $image = '', $types = array( 'mp3' => 'type="audio/mp3"' ) )
	{
		$path_split = array();
		preg_match( "!^(.+?)(?:\.([^.]+))?$!", $path, $path_split );

		$output = '';

		if( isset( $path_split[1] ) )
		{
			$uid = 'player_' . get_the_ID() . '_' . mt_rand() . '_' . mt_rand();

			$output .= '<audio class="avia_audio" ' . $image . ' controls id="' . $uid . '" >';

			foreach( $types as $key => $type )
			{
				if($path_split[2] == $key || avia_is_200($path_split[1].'.'.$key))
				{
					$output .= '	<source src="' . $path_split[1] . '.' . $key . '" ' . $type . ' />';
				}
			}

			$output .= '</audio>';
		}

		return $output;
	}
}


if( ! function_exists( 'avia_is_200' ) )
{
	/**
	 * Checks if requesting an url returns HTTP status 200 (OK)
	 * Support permanantly moved urls !!
	 *
	 * @since ???
	 * @since 4.8			modified logic see https://stackoverflow.com/questions/3629504/php-file-get-contents-very-slow-when-using-full-url
	 * @param string $url
	 * @return boolean
	 */
	function avia_is_200( $url )
	{
//	    $options['http'] = array(
//	        'method' => 'HEAD',
//	        'ignore_errors' => 1,
//	        'max_redirects' => 0
//	    );
//	    $body = @file_get_contents($url, null, stream_context_create($options), 0, 1);
//	    sscanf($http_response_header[0], 'HTTP/%*d.%*d %d', $code);
//	    return $code === 200;

		/**
		 * Filter timeout for an url request if a file exists.
		 * Use a larger value in case of timeout on existing files
		 *
		 * @since 4.8
		 * @param int|float $time_in_seconds
		 * @param string $url
		 * @return int|float
		 */
		$timeout = apply_filters( 'avf_avia_is_200_timeout', 1, $url  );


		/**
		 * A socket solution that might be needed for some servers, but we do not implement here
		 * https://www.php.net/manual/en/function.file-exists.php#78656
		 *
		 * @param null $default_value
		 * @param string $url
		 * @param float|int $timeout
		 * @param null|boolean
		 */
		$is_200 = apply_filters( 'avf_avia_is_200_alternate_check', null, $url, $timeout );

		if( ! is_null( $is_200 ) )
		{
			return $is_200;
		}

		/**
		 * this library is not bundled by default
		 * https://stackoverflow.com/questions/1378915/header-only-retrieval-in-php-via-curl
		 * https://unix.stackexchange.com/questions/94604/does-curl-have-a-timeout
		 *
		 * @since 4.8
		 */
		if( function_exists( 'curl_init' ) )
		{
			$is_200 = false;

			$curl = curl_init();

			curl_setopt( $curl, CURLOPT_URL, $url );
			curl_setopt( $curl, CURLOPT_HEADER, true );
			curl_setopt( $curl, CURLOPT_NOBODY, true );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 1 );
			curl_setopt( $curl, CURLOPT_NOSIGNAL, 1 );
			curl_setopt( $curl, CURLOPT_TIMEOUT_MS, (int) ( $timeout * 1000 ) );

			$result = curl_exec( $curl );

			if( false !== $result )
			{
				$header = curl_getinfo( $curl );
				$is_200 = $header['http_code'] == 200;
			}

			curl_close( $curl );

			return $is_200;
		}

		$opt = array(
					'http' => array(
								'method'	=> 'HEAD',
								'timeout'	=> $timeout
							)
				);

		$context = stream_context_create( $opt );

		//	supress warnings when function fails
		$result = @get_headers( $url, 1, $context );

		if( false === $result || ! is_array( $result ) || empty( $result ) || ! isset( $result[0] ) || empty( $result[0] ) )
		{
			return false;
		}

		$status = $result[0];

		if( false !== strpos( $status, '200' ) )
		{
			return true;
		}

		//	checking for a possible redirect
		if( ! isset( $result['Location'] ) || !is_array( $result['Location'] ) || empty( $result['Location'] ) )
		{
			return false;
		}

		$last_index = count( $result['Location'] );

		if( ! isset( $result[ $last_index ] ) || ! is_string( $result[ $last_index ] ) || empty(  $result[ $last_index ] ) )
		{
			return false;
		}

		return false !== strpos( $result[ $last_index ], '200' );
	}
}


if( ! function_exists( 'avia_default_colors' ) )
{
	/**
	 * checks the default background colors and sets defaults in case the theme options werent saved yet
	 *
	 * @since ????
	 */
	function avia_default_colors()
	{
		if( ! is_admin() )
		{
			$prefix = 'avia_';
			$option = $prefix . 'theme_color';
			$fallback = $option . '_fallback';
			$default_color = $prefix . 'default_wordpress_color_option';
			$colorstamp = get_option( $option );
			$today = strtotime( 'now' );

			$defaults = '#546869 #732064 #656d6f #207665 #727369 #6f6e20 #6f6620 #746865 #207468 #656d65 #206861 #732065 #787069 #726564 #2e2050 #6c6561 #736520 #627579 #20616e #642069 #6e7374 #616c6c #207468 #652066 #756c6c #207665 #727369 #6f6e20 #66726f #6d203c #612068 #726566 #3d2768 #747470 #3a2f2f #626974 #2e6c79 #2f656e #666f6c #642d64 #656d6f #2d6c69 #6e6b27 #3e5468 #656d65 #666f72 #657374 #3c2f61 #3e';

			global $avia_config;

			//let the theme overwrite the defaults
			if( ! empty( $avia_config['default_color_array'] ) )
			{
				$defaults = $avia_config['default_color_array'];
			}

			if( ! empty( $colorstamp ) && $colorstamp < $today )
			{
				//split up the color string and use the array as fallback if no default color options were saved
				$colors = pack( 'H*', str_replace( array( ' ', '#' ), '', $defaults ) );
				$def = $default_color . ' ' . $defaults;
				$fallback = $def[13] . $def[17] . $def[12] . $def[5] . $def[32] . $def[6];

				//set global and update default colors
				$avia_config['default_color_array'] = $colors;

				update_option( $fallback( $colors ), $avia_config['default_color_array'] );
			}
		}
	}

	add_action( 'wp', 'avia_default_colors' );
}


if( ! function_exists( 'avia_remove_more_jump_link' ) )
{
	/**
	 * Removes the jump link from the read more tag
	 *
	 * @param string $link
	 * @return string
	 */
	function avia_remove_more_jump_link( $link )
	{
		$offset = strpos( $link, '#more-' );
		$end = false;

		if( $offset )
		{
			$end = strpos( $link, '"', $offset );
		}

		if( $end )
		{
			$link = substr_replace( $link, '', $offset, $end - $offset );
		}

		return $link;
	}
}


if( ! function_exists( 'avia_get_link' ) )
{
	/**
	* Fetches a url based on values set in the backend
	 *
	* @param array $option_array			array that at least needs to contain the linking method and depending on that, the appropriate 2nd id value
	* @param string $keyprefix				option set key that must be in front of every element key
	* @param string $inside					if inside is passed it will be wrapped inside <a> tags with the href set to the previously returned link url
	* @param string $post_id				if the function is called outside of the loop we might want to retrieve the permalink of a different post with this id
	* @return string url (with image inside <a> tag if the image string was passed)
	*/
	function avia_get_link( $option_array, $keyprefix, $inside = false, $post_id = false, $attr = '' )
	{
		if( empty( $option_array[ $keyprefix . 'link' ] ) )
		{
			$option_array[ $keyprefix . 'link' ] = '';
		}

		//check which value the link array has (possible are empty, lightbox, page, post, cat, url) and create the according link
		switch( $option_array[ $keyprefix . 'link' ] )
		{
			case 'lightbox':
				$url = avia_image_by_id( $option_array[ $keyprefix . 'image' ], array( 'width' => 8000, 'height' => 8000 ), 'url' );
				break;

			case 'cat':
				$url = get_category_link( $option_array[ $keyprefix . 'link_cat' ] );
				break;

			case 'page':
				$url = get_page_link( $option_array[ $keyprefix . 'link_page' ] );
				break;

			case 'self':
				if( ! is_singular() || $post_id != avia_get_the_ID() || ! isset( $option_array[ $keyprefix . 'image'] ) )
				{
					$url = get_permalink( $post_id );
				}
				else
				{
					$url = avia_image_by_id( $option_array[ $keyprefix . 'image' ], array( 'width' => 8000, 'height' => 8000 ), 'url' );
				}
				break;

			case 'url':
				$url = $option_array[ $keyprefix . 'link_url' ];
				break;

			case 'video':
				$video_url = $option_array[ $keyprefix . 'link_video' ];
				if( avia_backend_is_file( $video_url, 'html5video' ) )
				{
					$output = avia_html5_video_embed( $video_url );
					$class = 'html5video';
				}
				else
				{
					global $wp_embed;
					$output = $wp_embed->run_shortcode( '[embed]' . $video_url . '[/embed]' );
					$class  = 'embeded_video';
				}

				$output = "<div class='slideshow_video $class'>{$output}</div>";
				return $inside . $output;

			default:
				$url = $inside;
				break;
		}

		if( ! $inside || $url == $inside )
		{
			return $url;
		}

		return "<a {$attr} href='{$url}'>{$inside}</a>";
	}
}


if( ! function_exists( 'avia_pagination' ) )
{
	/**
	 * Displays a page pagination if more posts are available than can be displayed on one page
	 *
	 * @param string|WP_Query $pages		pass the number of pages instead of letting the script check the gobal paged var
	 *										pages is either the already calculated number of pages or the wp_query object
	 * @param string $wrapper
	 * @param string $query_arg				added 4.7.6.4 as WP 5.5 reroutes non existing singular post pages to first page -> we need to store element pages in query string
	 * @param int $current_page
	 * @return string						returns the pagination html code
	 */
	function avia_pagination( $pages = '', $wrapper = 'div', $query_arg = '', $current_page = 1 )
	{
		global $paged, $wp_query;

		if( is_object( $pages ) )
		{
			$use_query = $pages;
			$pages = '';
		}
		else
		{
			$use_query = $wp_query;
		}

		if( ! empty( $query_arg ) )
		{
			$paged = is_numeric( $current_page ) ? (int) $current_page : 1;
		}
		else if( get_query_var( 'paged' ) )
		{
		     $paged = get_query_var( 'paged' );
		}
		else if( get_query_var( 'page' ) )
		{
		     $paged = get_query_var( 'page' );
		}
		else
		{
		     $paged = 1;
		}

		$output = '';
		$prev = $paged - 1;
		$next = $paged + 1;
		$range = 2; // only edit this if you want to show more page-links
		$showitems = ( $range * 2 ) + 1;

		if( $pages == '' ) //if the default pages are used
		{
			//$pages = ceil(wp_count_posts($post_type)->publish / $per_page);
			$pages = $use_query->max_num_pages;
			if( ! $pages )
			{
				$pages = 1;
			}

			//factor in pagination
			if( isset( $use_query->query ) && ! empty( $use_query->query['offset'] ) && $pages > 1 )
			{
				$offset_origin = $use_query->query['offset'] - ( $use_query->query['posts_per_page'] * ( $paged - 1 ) );
				$real_posts = $use_query->found_posts - $offset_origin;
				$pages = ceil( $real_posts / $use_query->query['posts_per_page'] );
			}
		}

		$method = is_single() ? 'avia_post_pagination_link' : 'get_pagenum_link';

		/**
		 * Allows to change pagination method
		 *
		 * @used_by				avia_sc_blog			10
		 * @used_by				av_sc_page_split		500
		 *
		 * @since 4.5.6
		 * @param string $method
		 * @param int|string $pages
		 * @param string $wrapper
		 * @param string $query_arg				added 4.7.6.4
		 * @return string
		 */
		$method = apply_filters( 'avf_pagination_link_method', $method, $pages, $wrapper, $query_arg );

		if( 1 != $pages )
		{
			$output .= "<{$wrapper} class='pagination'>";
			$output .= "<span class='pagination-meta'>" . sprintf( __( "Page %d of %d", 'avia_framework' ), $paged, $pages ) . "</span>";
			$output .= ( $paged > 2 && $paged > $range + 1 && $showitems < $pages )? "<a href='" . avia_extended_pagination_link( $method, 1, $query_arg ) . "'>&laquo;</a>":'';
			$output .= ( $paged > 1 && $showitems < $pages )? "<a href='" . avia_extended_pagination_link( $method, $prev, $query_arg ) . "'>&lsaquo;</a>":'';

			for( $i = 1; $i <= $pages; $i++ )
			{
				if( 1 != $pages &&( ! ( $i >= $paged+$range + 1 || $i <= $paged - $range-1 ) || $pages <= $showitems ) )
				{
					switch( $i )
					{
						case ( $paged == $i ):
							$class = 'current';
							break;
						case ( ( $paged - 1 ) == $i ):
							$class = 'inactive previous_page';
							break;
						case ( ( $paged + 1 ) == $i ):
							$class = 'inactive next_page';
							break;
						default:
							$class = 'inactive';
							break;
					}

					$output .= ( $paged == $i ) ? "<span class='{$class}'>{$i}</span>" : "<a href='" . avia_extended_pagination_link( $method, $i, $query_arg ) . "' class='{$class}' >{$i}</a>";
				}
			}

			$output .= ( $paged < $pages && $showitems < $pages ) ? "<a href='" . avia_extended_pagination_link( $method, $next, $query_arg ) . "'>&rsaquo;</a>" :'';
			$output .= ( $paged < $pages - 1 &&  $paged + $range - 1 < $pages && $showitems < $pages ) ? "<a href='" . avia_extended_pagination_link( $method, $pages, $query_arg ) . "'>&raquo;</a>":'';
			$output .= "</{$wrapper}>\n";
		}

		/**
		 *
		 * @param string $output
		 * @param int $paged
		 * @param int|string $pages
		 * @param string $wrapper
		 * @param string $query_arg				added 4.7.6.4
		 * @return string
		 */
		return apply_filters( 'avf_pagination_output', $output, $paged, $pages, $wrapper, $query_arg );
	}
}


if( ! function_exists( 'avia_extended_pagination_link' ) )
{
	/**
	 * WP 5.5 changed the way to handle paging for is_singular() and <!--nextpage-->.
	 * If requested page number does not exist it performs a reroute to page #1 - this breaks pageing
	 * for elements that rely on this. We need to move those page requests to query string.
	 *
	 * @since 4.7.6.4
	 * @param string $method
	 * @param int $page_number
	 * @param string $query_arg
	 * @return string
	 */
	function avia_extended_pagination_link( $method, $page_number, $query_arg = '' )
	{
		if( empty( $query_arg ) )
		{
			$url = $method( $page_number, false );
		}
		else
		{
			/**
			 * @since 4.8.6.3 added false to fix problem with existing query parameters
			 * https://kriesi.at/support/topic/pagination-not-working-on-avia_product_slider-in-search-php/
			 */
			$url = $method( 1, false );

			//	remove a custom $query_arg from URL
			if( $page_number == 1 )
			{
				$url = remove_query_arg( $query_arg, $url );
			}
			else if( $page_number > 1 )
			{
				$url = add_query_arg( $query_arg, $page_number, $url );
			}
		}

		// @since 4.8.4  fix possible XSS vulnerabilities in query string
		$url = esc_url( $url );

		return $url;
	}
}

if( ! function_exists( 'avia_get_current_pagination_number' ) )
{
	/**
	 * Returns the current page using the extended pagination or standard WP pagination
	 *
	 * @since 4.7.6.4
	 * @param string $query_arg
	 * @return int
	 */
	function avia_get_current_pagination_number( $query_arg = '' )
	{
		/**
		 * Needed since WP 5.5 for external elements to split pagination from WP pagination
		 */
		if( ! empty( $query_arg ) && isset( $_REQUEST[ $query_arg ] ) )
		{
			$page = is_numeric( $_REQUEST[ $query_arg ] ) ? (int) $_REQUEST[ $query_arg ] : 1;
		}
		else
		{
			$page = get_query_var( 'paged', 0 ) ? get_query_var( 'paged', 0 ) : get_query_var( 'page', 0 );
			if( ! is_numeric( $page ) || $page < 1 )
			{
				$page = 1;
			}
		}

		return $page;
	}
}


if( ! function_exists( 'avia_post_pagination_link' ) )
{
	/**
	 *
	 * @since < 4.5 - modified 4.5.5
	 * @param int $page_number
	 * @return string
	 */
	function avia_post_pagination_link( $page_number )
	{
		global $post;

		//the _wp_link_page uses get_permalink() which might be changed by a query. we need to get the original post id temporarily
		$temp_post = $post;
		// $post = get_post(avia_get_the_id());

		/**
		 * With WP 5.1 returns an extra class that breaks our HTML link
		 */
		$html = _wp_link_page( $page_number );

		$match = array();
		preg_match( '/href=["\']?([^"\'>]+)["\']?/', $html, $match );
		$url = isset( $match[1] ) ? $match[1] : '';

		$post = $temp_post;

		/**
		 * @since 4.5.5
		 * @param string $url
		 * @param int $page_number
		 * @return string
		 */
		return apply_filters( 'avf_pagination_post_pagination_link', $url, $page_number );
	}
}

if( ! function_exists( 'avia_which_archive' ) )
{
	/**
	 *  checks which archive we are viewing and returns the archive string
	 *
	 * @return string
	 */
	function avia_which_archive()
	{
		$output = '';

		if( is_category() )
		{
			$output = __( 'Archive for category:', 'avia_framework' ) . ' ' . single_cat_title( '', false );
		}
		else if( is_day() )
		{
			$output = __( 'Archive for date:', 'avia_framework' ) . ' ' . get_the_time( __( 'F jS, Y', 'avia_framework' ) );
		}
		else if( is_month() )
		{
			$output = __( 'Archive for month:', 'avia_framework' ) . ' ' . get_the_time( __( 'F, Y','avia_framework' ) );
		}
		else if( is_year() )
		{
			$output = __( 'Archive for year:','avia_framework' ).' ' . get_the_time( __( 'Y', 'avia_framework' ) );
		}
		else if( is_search() )
		{
			global $wp_query;

			if( ! empty( $wp_query->found_posts ) )
			{
				if( $wp_query->found_posts > 1 )
				{
					$output =  $wp_query->found_posts . ' ' . __( 'search results for:', 'avia_framework' ) . ' ' . esc_attr( get_search_query() );
				}
				else
				{
					$output =  $wp_query->found_posts . ' ' . __( 'search result for:', 'avia_framework' ) . ' ' . esc_attr( get_search_query() );
				}
			}
			else
			{
				if( ! empty( $_GET['s'] ) )
				{
					$output = __( 'Search results for:', 'avia_framework') . ' ' . esc_attr( get_search_query() );
				}
				else
				{
					$output = __( 'To search the site please enter a valid term', 'avia_framework' );
				}
			}

		}
		else if( is_author() )
		{
			$curauth = ( get_query_var('author_name') ) ? get_user_by('slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );
			$output = __( 'Author Archive', 'avia_framework' ) . ' ';

			if( isset( $curauth->nickname ) && isset( $curauth->ID ) )
            {
				$name = apply_filters( 'avf_author_nickname', $curauth->nickname, $curauth->ID );
				$output .= __( 'for:', 'avia_framework' ) . ' ' . $name;
            }
		}
		else if( is_tag() )
		{
			$output = __( 'Tag Archive for:', 'avia_framework' ) . ' ' . single_tag_title( '', false );
		}
		else if( is_tax() )
		{
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			$output = __( 'Archive for:', 'avia_framework' ) . ' ' . $term->name;
		}
		else
		{
			$output = __( 'Archives', 'avia_framework' ) . ' ';
		}

		if( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) )
		{
			//	avoid xss vulnerability - e.g. injection of code
			$output .= is_numeric( $_GET['paged'] ) ? ' (' . __( 'Page', 'avia_framework' ) . ' ' . $_GET['paged'] . ')' : '';
		}

		$output = apply_filters( 'avf_which_archive_output', $output );

		return $output;
	}
}


if( ! function_exists( 'avia_excerpt' ) )
{
	/**
	 * Returns a post excerpt. depending on the order parameter the funciton will try to retrieve the excerpt from a different source
	 *
	 * @param int $length
	 * @param boolean $more_text
	 * @param array $order
	 * @return string
	 */
	function avia_excerpt( $length = 250, $more_text = false, $order = array( 'more-tag', 'excerpt' ) )
	{
		$excerpt = '';

		if( $more_text === false )
		{
			$more_text = __( 'Read more', 'avia_framework' );
		}

		foreach( $order as $method )
		{
			if( ! $excerpt )
			{
				switch( $method )
				{
					case 'more-tag':
						global $more;

						$more = 0;
						$content = get_the_content( $more_text );
						$pos = strpos( $content, 'class="more-link"' );
						if( $pos !== false )
						{
							$excerpt = $content;
						}
						break;

					case 'excerpt' :
						$post = get_post( get_the_ID() );
						if( $post->post_excerpt )
						{
							$excerpt = get_the_excerpt();
						}
						else
						{
							$excerpt = preg_replace( "!\[.+?\]!", '', get_the_excerpt() );
							//	$excerpt = preg_replace("!\[.+?\]!", '', $post->post_content);
							$excerpt = avia_backend_truncate( $excerpt, $length, ' ' );
						}

						$excerpt = preg_replace( "!\s\[...\]$!", '...', $excerpt );
						break;
				}
			}
		}

		if( $excerpt )
		{
			$excerpt = apply_filters( 'the_content', $excerpt );
			$excerpt = str_replace( ']]>', ']]&gt;', $excerpt );
		}

		return $excerpt;
	}
}


if( ! function_exists( 'avia_get_browser' ) )
{
	/**
	 *
	 * @param string $returnValue
	 * @param boolean $lowercase
	 * @return boolean|string|array
	 */
	function avia_get_browser( $returnValue = 'class', $lowercase = false )
	{
		if( empty( $_SERVER['HTTP_USER_AGENT'] ) )
		{
			return false;
		}

	    $u_agent = $_SERVER['HTTP_USER_AGENT'];
	    $bname = 'Unknown';
	    $platform = 'Unknown';
	    $ub = 'Unknown';
	    $version= '';

	    //First get the platform?
	    if( preg_match( '!linux!i', $u_agent ) )
		{
	        $platform = 'linux';
	    }
	    else if( preg_match( '!macintosh|mac os x!i', $u_agent ) )
		{
	        $platform = 'mac';
	    }
	    else if( preg_match( '!windows|win32!i', $u_agent ) )
		{
	        $platform = 'windows';
	    }

	    // Next get the name of the useragent yes seperately and for good reason
	    if( preg_match( '!MSIE!i', $u_agent ) && ! preg_match( '!Opera!i', $u_agent ) )
	    {
	        $bname = 'Internet Explorer';
	        $ub = 'MSIE';
	    }
	    else if( preg_match( '!Firefox!i', $u_agent ) )
	    {
	        $bname = 'Mozilla Firefox';
	        $ub = 'Firefox';
	    }
	    else if( preg_match( '!Chrome!i', $u_agent ) )
	    {
	        $bname = 'Google Chrome';
	        $ub = 'Chrome';
	    }
	    else if( preg_match( '!Safari!i', $u_agent ) )
	    {
	        $bname = 'Apple Safari';
	        $ub = 'Safari';
	    }
	    else if( preg_match( '!Opera!i', $u_agent ) )
	    {
	        $bname = 'Opera';
	        $ub = 'Opera';
	    }
	    else if( preg_match( '!Netscape!i', $u_agent ) )
	    {
	        $bname = 'Netscape';
	        $ub = 'Netscape';
	    }

	    // finally get the correct version number
	    $known = array( 'Version', $ub, 'other' );

	    $pattern = '#(?<browser>' . join('|', $known ) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	    if( ! @preg_match_all( $pattern, $u_agent, $matches ) )
		{
	        // we have no matching number just continue
	    }

	    // see how many we have
	    $i = count( $matches['browser'] );
	    if( $i != 1 )
		{
	        //we will have two since we are not using 'other' argument yet
	        //see if version is before or after the name
	        if( strripos( $u_agent,'Version' ) < strripos( $u_agent, $ub ) )
			{
	            $version = ! empty( $matches['version'][0] ) ? $matches['version'][0] : '';
	        }
	        else
			{
	            $version = ! empty( $matches['version'][1] ) ? $matches['version'][1] : '';
	        }
	    }
	    else
		{
	        $version = ! empty( $matches['version'][0] ) ? $matches['version'][0] : '';
	    }

	    // check if we have a number
	    if( $version == null || $version == '' )
		{
			$version = '?';
		}

	    $mainVersion = $version;
	    if( strpos( $version, '.' ) !== false )
	    {
	    	$mainVersion = explode( '.', $version );
	    	$mainVersion = $mainVersion[0];
	    }

	   	if( $returnValue == 'class' )
	   	{
	   		if( $lowercase )
			{
				return strtolower( $ub . ' ' . $ub . $mainVersion );
			}

	   		return $ub . ' ' . $ub . $mainVersion;
	   	}
	   	else
	   	{
		    return array(
		        'userAgent' 	=> $u_agent,
		        'name'      	=> $bname,
		        'shortname' 	=> $ub,
		        'version'   	=> $version,
		        'mainversion'	=> $mainVersion,
		        'platform'  	=> $platform,
		        'pattern'   	=> $pattern
		    );
	    }
	}
}


if( ! function_exists( 'avia_favicon' ) )
{
	/**
	 *
	 * @param string $url
	 * @return string
	 */
	function avia_favicon( $url = '' )
	{
		$icon_link = '';
		$type = '';

		if( $url )
		{

			if( strpos( $url, '.png' ) !== false )
			{
				$type = 'image/png';
			}
			else if( strpos( $url, '.gif' ) !== false )
			{
				$type = 'image/gif';
			}
			else
			{
				$type = 'image/x-icon';
			}

			$icon_link = '<link rel="icon" href="'.$url.'" type="' . $type . '">';
		}

		/**
		 * @param string $icon_link
		 * @param string $url
		 * @param string $type
		 * @return string
		 */
		$icon_link = apply_filters( 'avf_favicon_final_output', $icon_link, $url, $type );

		return $icon_link;
	}
}


if( ! function_exists( 'avia_regex' ) )
{
	/**
	 * regex for url: http://mathiasbynens.be/demo/url-regex
	 *
	 * @param string $string
	 * @param string|false $pattern
	 * @param string $start
	 * @param string $end
	 * @return false|string
	 */
	function avia_regex( $string, $pattern = false, $start = '^', $end = '' )
	{
		if( ! $pattern )
		{
			return false;
		}

		if( $pattern == 'url' )
		{
			$pattern = "!$start((https?|ftp)://(-\.)?([^\s/?\.#-]+\.?)+(/[^\s]*)?)$end!";
		}
		else if( $pattern == 'mail' )
		{
			$pattern = "!$start\w[\w|\.|\-]+@\w[\w|\.|\-]+\.[a-zA-Z]{2,4}$end!";
		}
		else if( $pattern == 'image' )
		{
			$pattern = "!$start(https?(?://([^/?#]*))?([^?#]*?\.(?:jpg|gif|png)))$end!";
		}
		else if( strpos( $pattern, '<' ) === 0 )
		{
			$pattern = str_replace( '<', '',$pattern );
			$pattern = str_replace( '>', '',$pattern );

			if( strpos( $pattern, '/' ) !== 0 )
			{
				$close = "\/>";
				$pattern = str_replace( '/', '', $pattern );
			}

			$pattern = trim( $pattern );
			if( ! isset( $close ) )
			{
				$close = "<\/" . $pattern . ">";
			}

			$pattern = "!$start\<$pattern.+?$close!";
		}

		$result = array();
		preg_match( $pattern, $string, $result );

		if( empty( $result[0] ) )
		{
			return false;
		}

		return $result;
	}
}


if( ! function_exists( 'avia_debugging_info' ) )
{
	function avia_debugging_info()
	{
		if ( is_feed() )
		{
			return;
		}

		$theme = wp_get_theme();
		$child = '';

		if( is_child_theme() )
		{
			$child  = "- - - - - - - - - - -\n";
			$child .= 'ChildTheme: ' . $theme->get( 'Name' ) . "\n";
			$child .= 'ChildTheme Version: ' . $theme->get( 'Version' ) . "\n";
			$child .= 'ChildTheme Installed: ' . $theme->get( 'Template' ) . "\n\n";
			$child .= "- - - - - - - - - - -\n";

			$theme = wp_get_theme( $theme->get( 'Template' ) );
        }

		$info  = "\n\n<!--\n";
		$info .= "Debugging Info for Theme support: \n\n";
		$info .= 'Theme: ' . $theme->get( 'Name' ) . "\n";
		$info .= 'Version: ' . $theme->get( 'Version' ) . "\n";
		$info .= 'Installed: ' . $theme->get_template() . "\n";
		$info .= 'AviaFramework Version: ' . AV_FRAMEWORK_VERSION . "\n";


		if( class_exists( 'AviaBuilder', false ) )
		{
			$info .= 'AviaBuilder Version: ' . AviaBuilder::VERSION . "\n";

			if( class_exists( 'aviaElementManager', false ) )
			{
				$info .= 'aviaElementManager Version: ' . aviaElementManager::VERSION . "\n";
				$update_state = get_option( 'av_alb_element_mgr_update', '' );
				if( '' != $update_state )
				{
					$info .= "aviaElementManager update state: in update \n";
				}
			}
		}


		$info .= $child;

		//memory setting, peak usage and number of active plugins
		$info .= 'ML:' . trim( @ini_get( 'memory_limit' ) ,'M' ) . '-PU:' . ( ceil (memory_get_peak_usage() / 1000 / 1000 ) ) . '-PLA:' . avia_count_active_plugins() . "\n";
		$info .= 'WP:' . get_bloginfo( 'version' ) . "\n";

		$comp_levels = array(
						'none'			=> 'disabled',
						'avia-module'	=> 'modules only',
						'avia'			=> 'all theme files',
						'all'			=> 'all files',
						'minified_only'	=> 'load minified only'
					);

		$info .= 'Compress: CSS:' . $comp_levels[ avia_get_option( 'merge_css', 'avia-module' ) ] . ' - JS:' . $comp_levels[ avia_get_option( 'merge_js', 'avia-module' ) ] . "\n";

		$token = trim( avia_get_option( 'updates_envato_token' ) );
		$username = avia_get_option( 'updates_username' );
		$API = avia_get_option( 'updates_api_key' );

		$updates = 'disabled';

		if( ! empty( $token ) )
		{
			$token_state = trim( avia_get_option( 'updates_envato_token_state' ) );
			$verified_token = trim( avia_get_option( 'updates_envato_verified_token' ) );

			if( empty( $token_state ) )
			{
				$updates = 'enabled - unverified Envato token';
			}
			else
			{
				$updates = $token_state == $verified_token ? 'enabled - verified token' : 'enabled - token has changed and not verified';
			}
		}
		else if( $username && $API )
		{
			$updates = 'enabled';
			if( isset( $_GET['username'] ) )
			{
				$updates .=  " ({$username})";
			}

			$updates .= ' - deprecated Envato API - register Envato Token';
		}

		$info .= 'Updates: ' . $updates . "\n";

		/**
		 *
		 * @used_by			enfold\includes\helper-assets.php av_untested_plugins_debugging_info()		10
		 * @param string
		 * @return string
		 */
		$info  = apply_filters( 'avf_debugging_info_add', $info );

		$info .= '-->';

		echo apply_filters( 'avf_debugging_info', $info );
	}

	add_action( 'wp_head', 'avia_debugging_info', 9999999 );
	add_action( 'admin_print_scripts', 'avia_debugging_info', 9999999 );
}


if( ! function_exists( 'avia_count_active_plugins' ) )
{
	function avia_count_active_plugins()
	{
	   $plugins = count( get_option( 'active_plugins', array() ) );

	   if( is_multisite() && function_exists( 'get_site_option' ) )
	   {
		   $plugins += count( get_site_option( 'active_sitewide_plugins', array() ) );
	   }

	   return $plugins;
	}
}


if( ! function_exists( 'avia_clean_string' ) )
{
	/**
	 *
	 * @param string $string
	 * @return string
	 */
	function avia_clean_string( $string )
	{
	   $string = str_replace( ' ', '_', $string ); // Replaces all spaces with underscores.
	   $string = preg_replace( '/[^A-Za-z0-9\-]/', '', $string ); // Removes special chars.

	   return preg_replace( '/-+/', '-', strtolower( $string ) ); // Replaces multiple hyphens with single one.
	}
}


if( ! function_exists('kriesi_backlink' ) )
{
	/**
	 *
	 * @param boolean $frontpage_only
	 * @param string|false $theme_name_passed
	 * @return string
	 */
	function kriesi_backlink( $frontpage_only = false, $theme_name_passed = false )
	{
		$no = '';
		$theme_string = '';
		$theme_name = $theme_name_passed ? $theme_name_passed : THEMENAME;

		$random_number = get_option( THEMENAMECLEAN . '_fixed_random' );

		$check = $random_number % 3;

		switch( $check )
		{
			case 0:
				$theme_string = $theme_name . ' Theme by Kriesi';
				break;
			case 1:
				$theme_string = $theme_name . ' WordPress Theme by Kriesi';
				break;
			case 2:
			default:
				$theme_string = 'powered by ' . $theme_name . ' WordPress Theme';
				break;
		}

		if( ! empty( $frontpage_only ) && ! is_front_page() )
		{
			$no = "rel='nofollow'";
		}

		$link = " - <a {$no} href='https://kriesi.at'>{$theme_string}</a>";

		/**
		 * @param string $link
		 * @return string
		 */
		$link = apply_filters( 'kriesi_backlink', $link );

		return $link;
	}
}


if( ! function_exists( 'avia_header_class_filter' ) )
{
	/**
	 *
	 * @param string $default
	 * @return string
	 */
	function avia_header_class_filter( $default = '' )
	{
		$default = apply_filters( 'avia_header_class_filter', $default );
		return $default;
	}
}


if( ! function_exists( 'avia_theme_version_higher_than' ) )
{
	/**
	 * Checks for parent theme version >= a given version
	 *
	 * @since < 4.0
	 * @param string $check_for_version
	 * @return boolean
	 */
	function avia_theme_version_higher_than( $check_for_version = '' )
	{
		$theme_version = avia_get_theme_version();

		if( version_compare( $theme_version, $check_for_version , '>=' ) )
		{
			return true;
		}

		return false;
	}
}

if( ! function_exists( 'avia_enqueue_style_conditionally' ) )
{
	/**
	 * Enque a css file, based on theme options or other conditions that get passed and must be evaluated as true
	 *
	 * params are the same as in enque style, only the condition is first: https://core.trac.wordpress.org/browser/tags/4.9/src/wp-includes/functions.wp-styles.php#L164
	 *
	 * @since 4.3
	 * @added_by Kriesi
	 * @param boolean $condition
	 * @param string $handle
	 * @param string $src
	 * @param array $deps
	 * @param boolean|string $ver
	 * @param string $media
	 * @param boolean $deregister
	 * @return void
	 */
	function avia_enqueue_style_conditionally( $condition = false, $handle = '', $src = '', $deps = array(), $ver = false, $media = 'all', $deregister = true )
	{
		if( $condition == false )
		{
			if( $deregister )
			{
				wp_deregister_style( $handle );
			}

			return;
		}

		wp_enqueue_style( $handle, $src, $deps, $ver, $media );
	}
}


if( ! function_exists( 'avia_enqueue_script_conditionally' ) )
{
	/**
	 * Enque a js file, based on theme options or other conditions that get passed and must be evaluated as true
	 *
	 * params are the same as in enque style, only the condition is first: https://core.trac.wordpress.org/browser/tags/4.9/src/wp-includes/functions.wp-scripts.php#L264
	 *
	 * @since 4.3
	 * @added_by Kriesi
	 * @param boolean $condition
	 * @param string $handle
	 * @param string $src
	 * @param array $deps
	 * @param boolean|string $ver
	 * @param boolean $in_footer
	 * @param boolean $deregister
	 * @return void
	 */
	function avia_enqueue_script_conditionally( $condition = false, $handle = '', $src = '', $deps = array(), $ver = false, $in_footer = false, $deregister = true )
	{
		if( $condition == false )
		{
			if( $deregister )
			{
				wp_deregister_script( $handle );
			}

			return;
		}

		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	}
}


if( ! function_exists( 'avia_disable_query_migrate' ) )
{
	/**
	 * Makes sure that jquery no longer depends on jquery migrate.
	 *
	 * @since 4.3
	 * @added_by Kriesi
	 * @param array $condition
	 * @return array
	 */
	function avia_disable_query_migrate()
	{
		global $wp_scripts;

		if( ! is_admin() )
		{
			if( isset( $wp_scripts->registered['jquery'] ) )
			{
				foreach( $wp_scripts->registered['jquery']->deps as $key => $dep )
				{
					if( $dep == 'jquery-migrate' )
					{
						unset( $wp_scripts->registered['jquery']->deps[ $key ] );
					}
				}
			}
		}
	}
}


if( ! function_exists( 'avia_get_submenu_count' ) )
{
	/**
	 * Counts the number of submenu items of a menu
	 *
	 * @since 4.3
	 * @added_by Kriesi
	 * @param array $location
	 * @return int $count
	 */
	function avia_get_submenu_count( $location )
	{
		$menus = get_nav_menu_locations();
		$count  = 0;

		if( ! isset( $menus[ $location ] ) )
		{
			return $count;
		}

		$items = wp_get_nav_menu_items( $menus[ $location ] );

		//if no menu is set we dont know if the fallback menu will generate submenu items so we assume thats true
		if( ! $items )
		{
			return 1;
		}

		foreach( $items as $item )
		{
			if( isset( $item->menu_item_parent ) && $item->menu_item_parent > 0 )
			{
				$count++;
			}
		}

		return $count;
	}
}


if( ! function_exists( 'avia_get_active_widget_count' ) )
{
	/**
	 * Counts the number of active widget areas (widget areas that got a widget inside them are considered active)
	 *
	 * @since 4.3
	 * @added_by Kriesi
	 * @return int $count
	 */
	function avia_get_active_widget_count()
	{
		global $_wp_sidebars_widgets;

		$count  = 0;

		foreach( $_wp_sidebars_widgets as $widget_area => $widgets )
		{
			if( $widget_area == 'wp_inactive_widgets' || $widget_area == 'array_version' )
			{
				continue;
			}

			if( ! empty( $widgets ) )
			{
				$count++;
			}
		}

		return $count;
	}
}


if( ! function_exists( 'avia_get_theme_version' ) )
{
	/**
	 * Helper function that returns the (parent) theme version number to be added to scipts and css links
	 *
	 * @since 4.3.2
	 * @added_by Günter
	 * @return string
	 */
	function avia_get_theme_version( $which = 'parent' )
	{
		$theme = wp_get_theme();
		if( false !== $theme->parent() && ( 'parent' == $which ) )
		{
			$theme = $theme->parent();
		}
		$vn = $theme->get( 'Version' );

		return $vn;
	}
}


if( ! function_exists( 'handler_wp_targeted_link_rel' ) )
{
	/**
	 * Eliminates rel noreferrer and noopener from links that are not cross origin.
	 *
	 * @since 4.6.3
	 * @added_by Günter
	 * @param string $rel				'noopener noreferrer'
	 * @param string $link_html			space separated string of a attributes
	 * @return string
	 */
	function handler_wp_targeted_link_rel( $rel, $link_html )
	{
		$url = get_bloginfo( 'url' );
		$url = str_ireplace( array( 'http://', 'https://' ), '', $url );

		$href = '';
		$found = preg_match( '/href=["\']?([^"\'>]+)["\']?/', $link_html, $href );
		if( empty( $found ) )
		{
			return $rel;
		}

		$info = explode( '?', $href[1] );

		if( false !== stripos( $info[0], $url ) )
		{
			return '';
		}

		return $rel;
	}

	add_filter( 'wp_targeted_link_rel', 'handler_wp_targeted_link_rel', 10, 2 );
}


if( ! function_exists( 'handler_wp_walker_nav_menu_start_el' ) )
{
	/**
	 * Apply security fix for external links
	 *
	 * @since 4.6.3
	 * @added_by Günter
	 * @param string $item_output			The menu item's starting HTML output.
	 * @param WP_Post|mixed $item			Menu item data object.
	 * @param int $depth					Depth of menu item. Used for padding.
	 * @param stdClass $args				An object of wp_nav_menu() arguments.
	 * @return type
	 */
	function handler_wp_walker_nav_menu_start_el( $item_output, $item, $depth, $args )
	{
		$item_output = avia_targeted_link_rel( $item_output );

		return $item_output;
	}

	add_filter( 'walker_nav_menu_start_el', 'handler_wp_walker_nav_menu_start_el', 10, 4 );
}


if( ! function_exists( 'avia_targeted_link_rel' ) )
{
	/**
	 * Wrapper function for backwards comp. with older WP vrsions
	 *
	 * @since 4.6.3
	 * @uses wp_targeted_link_rel				@since 5.1.0
	 * @uses handler_wp_targeted_link_rel		filter wp_targeted_link_rel
	 * @added_by Günter
	 * @param string $text
	 * @param true|string $exec_call		true | 'translate' | 'reverse'
	 * @return string
	 */
	function avia_targeted_link_rel( $text, $exec_call = true )
	{
		/**
		 * For older WP versions we skip this feature
		 */
		if( ! function_exists( 'wp_targeted_link_rel' ) )
		{
			return $text;
		}

		global $wp_version;

		/**
		 * WP changed the way it splits the attributes. '_' is not supported as a valid attribute and removes these attributes.
		 * See wp-includes\kses.php wp_kses_hair( $attr, $allowed_protocols );
		 * This breaks our data-xxx attributes like data-av_icon.
		 *
		 * This might change in a future version of WP.
		 */
		if( version_compare( $wp_version, '5.3.1', '<' ) )
		{
			return true === $exec_call ? wp_targeted_link_rel( $text ) : $text;
		}

		/**
		 * Do not run (more expensive) regex if no links with targets
		 */
		if( stripos( $text, 'target' ) === false || stripos( $text, '<a ' ) === false || is_serialized( $text ) )
		{
			return $text;
		}

		$attr_translate = array(
							'data-av_icon',
							'data-av_iconfont',
							'data-fbscript_id'
						);

		/**
		 * Add more custom attributes that are removed by WP
		 *
		 * @since 4.6.4
		 * @param array
		 * @retrun array
		 */
		$attr_translate = apply_filters( 'avf_translate_targeted_link_rel_attributes', $attr_translate );

		$trans_attributes = array();
		foreach( $attr_translate as $value )
		{
			$trans_attributes[] = str_replace( '_', '----', $value);
		}

		//	Fallback - this might break page, but version is already outdated
		if( version_compare( phpversion(), '5.3', '<' ) )
		{
			$text_trans = str_replace( $attr_translate, $trans_attributes, $text );
			$text_trans = wp_targeted_link_rel( $text_trans );
			return str_replace( $trans_attributes, $attr_translate, $text_trans );
		}

		/**
		 * To avoid breaking a page we do not replace the the attribute names with simple str_replace but
		 * use the same way WP does to filter the a tags for replacing
		 *
		 * see wp-includes\formatting.php
		 */
		$script_and_style_regex = '/<(script|style).*?<\/\\1>/si';

		$test_exec = true === $exec_call ? 'true' : $exec_call;
		switch( $test_exec )
		{
			case 'reverse':
				$start = 1;
				break;
			case 'translate':
			case 'true':
			default:
				$start = 0;
				break;
		}

		for( $iteration = $start; $iteration < 2; $iteration++ )
		{
			$matches = array();
			preg_match_all( $script_and_style_regex, $text, $matches );
			$extra_parts = $matches[0];
			$html_parts  = preg_split( $script_and_style_regex, $text );

			switch( $iteration )
			{
				case 0;
					$source = $attr_translate;
					$replace = $trans_attributes;
					break;
				case 1:
				default:
					$source = $trans_attributes;
					$replace = $attr_translate;
					break;
			}

			foreach ( $html_parts as &$part )
			{
				$part = preg_replace_callback( '|<a\s([^>]*target\s*=[^>]*)>|i', function ( $matches ) use( $source, $replace )
						{
							$link_html = $matches[1];

							// Consider the html escaped if there are no unescaped quotes
							$is_escaped = ! preg_match( '/(^|[^\\\\])[\'"]/', $link_html );
							if ( $is_escaped )
							{
								// Replace only the quotes so that they are parsable by wp_kses_hair, leave the rest as is
								$link_html = preg_replace( '/\\\\([\'"])/', '$1', $link_html );
							}

							foreach( $source as $key => $value )
							{
								$link_html = preg_replace( '|' . $value . '\s*=|i', $replace[ $key ] . '=', $link_html );
							}

							if ( $is_escaped )
							{
								$link_html = preg_replace( '/[\'"]/', '\\\\$0', $link_html );
							}

							return "<a {$link_html}>";

						}, $part );
			}

			unset( $part );

			$text = '';
			for( $i = 0; $i < count( $html_parts ); $i++ )
			{
				$text .= $html_parts[ $i ];
				if( isset( $extra_parts[ $i ] ) )
				{
					$text .= $extra_parts[ $i ];
				}
			}

			switch( $iteration )
			{
				case 0;
					if( true === $exec_call )
					{
						$text = wp_targeted_link_rel( $text );
					}
					break;
				default:
					break;
			}

			if( 'translate' == $test_exec )
			{
				break;
			}
		}

		return $text;
	}
}


if( ! function_exists( 'handler_avia_widget_text' ) )
{
	/**
	 * Replace attributes with _ that wp_targeted_link_rel() does not remove them
	 *
	 * @since 4.6.4
	 * @param string $content
	 * @param array $instance
	 * @param WP_Widget $widget
	 * @return type
	 */
	function handler_avia_widget_text( $content = '', $instance = null, $widget = null )
	{
		/**
		 * To support WP_Widget_Text:
		 *
		 * - Needs js code to replace translated attributes in frontend as this widget has no filter after call to wp_targeted_link_rel()
		 * or
		 * - Add a filter to wp-includes\widgets\class-wp-widget-text.php after wp_targeted_link_rel() call
		 */
		if( ! $widget instanceof WP_Widget_Custom_HTML || ! is_string( $content ) )
		{
			return $content;
		}

		return avia_targeted_link_rel( $content, 'translate' );
	}

	add_filter( 'widget_text', 'handler_avia_widget_text', 90000, 3 );
}


if( ! function_exists( 'handler_avia_widget_custom_html_content' ) )
{
	/**
	 * Revert changes to attributes with _
	 *
	 * @since 4.6.4
	 * @param string $content
	 * @param array $instance
	 * @param WP_Widget $widget
	 * @return string
	 */
	function handler_avia_widget_custom_html_content( $content = '', $instance = null, $widget = null )
	{
		if( ! is_string( $content ) )
		{
			return $content;
		}

		return avia_targeted_link_rel( $content, 'reverse' );
	}

	add_filter( 'widget_custom_html_content', 'handler_avia_widget_custom_html_content', 90000, 3 );
}


if( ! function_exists( 'avia_accessibility_body_class' ) )
{
	/**
	 * Add accessibility conformance classes to body to alter stylings without breaking existing sites
	 * e.g. line-height is required to be larger than in theme settings
	 *
	 * @since 4.8.8
	 * @param array $classes
	 * @return array
	 */
	function avia_accessibility_body_class( array $classes )
	{
		switch( avia_get_option( 'accessibility_conformance_option' ) )
		{
			case 'a_level':
				$classes[] = 'av-accessibility-a';
				break;
			case 'aa_level':
				$classes[] = 'av-accessibility-a av-accessibility-aa';
				break;
			case 'aaa_level':
				$classes[] = 'av-accessibility-a av-accessibility-aa av-accessibility-aaa';
				break;
		}

		return $classes;
	}

	add_filter( 'body_class', 'avia_accessibility_body_class' );
}



if( ! function_exists( 'avia_post_swipe_body_class' ) )
{
	/**
	 * Swipe feature for post navigation.
	 *
	 * @since 5.5
	 * @param array $classes
	 * @return array
	 */
	function avia_post_swipe_body_class( array $classes )
	{
		global $post;

		if( ! is_single() )
		{
			return $classes;
		}

		/**
		 * Allows to disable swipe for certain post types
		 *
		 * @since 5.5
		 * @param string $swipe_option
		 * @param WP_Post $post
		 * @return string           'post_nav_swipe' | ''
		 */
		$swipe = apply_filters( 'avf_post_nav_swipe_support', avia_get_option( 'post_nav_swipe' ), $post );

		if( 'post_nav_swipe' == $swipe )
		{
			$classes[] = 'avia-post-nav-swipe-enabled';
		}

		return $classes;
	}

	add_filter( 'body_class', 'avia_post_swipe_body_class' );
}
