<?php

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


/*
 * Adjustments for the Yoast WordPress SEO Plugin
 */

if( ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'wpSEO' ) )
{
	return;
}

if( ! function_exists( 'avia_wpseo_register_assets' ) )
{
	function avia_wpseo_register_assets()
	{
		$screen = get_current_screen();
		$vn = avia_get_theme_version();
		$min_js = avia_minify_extension( 'js' );

		if( is_null( $screen ) || $screen->post_type == '' )
		{
			return;
		}

		wp_enqueue_script(
			'avia_analytics_js',
			AVIA_BASE_URL . "config-templatebuilder/avia-template-builder/assets/js/avia-analytics{$min_js}.js",
			[ 'avia_builder_js' ],
			$vn,
			true
		);

		wp_enqueue_script(
			'avia_yoast_seo_js',
			AVIA_BASE_URL . "config-wordpress-seo/wpseo-mod{$min_js}.js",
			[ 'avia_analytics_js' ],
			$vn,
			true
		);
	}

	if( is_admin() )
	{
		add_action( 'admin_enqueue_scripts', 'avia_wpseo_register_assets' );
	}
}


if( ! function_exists( 'avia_wpseo_deactivate_avia_set_follow' ) )
{
	/**
	 * There's no need for the default set follow function. Yoast SEO takes care of it
	 * and user can set custom robot meta values for each post/page.
	 *
	 * @param string $meta
	 * @return string
	 */
	function avia_wpseo_deactivate_avia_set_follow( $meta )
	{
		return '';
	}

	add_filter( 'avf_set_follow', 'avia_wpseo_deactivate_avia_set_follow', 10, 1 );
}

if( ! function_exists( 'avia_wpseo_change_title_adjustment' ) )
{
	/**
	 * Yoast SEO takes care of the title. It uses the wp_title() hook and the output data is stored in $wptitle.
	 * So just return $wptitle and leave everything else to Yoast.
	 *
	 * This filter has been deprecated with WP 4.1 - function _wp_render_title_tag() is used instead
	 *
	 * @param type $title
	 * @param type $wptitle
	 * @return type
	 */
	function avia_wpseo_change_title_adjustment( $title, $wptitle )
	{
		return $wptitle;
	}

	add_filter( 'avf_title_tag', 'avia_wpseo_change_title_adjustment', 10, 2 );
}

if( ! function_exists( 'avia_wpseo_pre_get_document_title_before' ) )
{
	/**
	 * Checks, if we are on an ALB shop page
	 *
	 * @since 4.5.5
	 * @return boolean
	 */
	function avia_wpseo_alb_shop_page()
	{
		global $post;

		if( ! $post instanceof WP_Post || ! class_exists( 'WooCommerce' ) )
		{
			return false;
		}

		$shop_page = wc_get_page_id( 'shop' );

		if( $post->ID != $shop_page )
		{
			return false;
		}

		if( 'active' != Avia_Builder()->get_alb_builder_status( $shop_page ) )
		{
			return false;
		}

		return true;
	}

	/**
	 * YOAST takes care of title in normal situations.
	 * Only when WC is active and we have a ALB shop page the title is not recognised correctly (because this is no archive page)
	 * In that case we simulate this.
	 *
	 * @since 4.5.5
	 * @param string $title
	 * @return string
	 */
	function avia_wpseo_pre_get_document_title_before( $title )
	{
		global $wp_query, $avia_wp_query_archive_state;

		if( avia_wpseo_alb_shop_page() )
		{
			$avia_wp_query_archive_state = $wp_query->is_archive;
			$wp_query->is_archive = true;
		}

		return $title;
	}

	/**
	 * Reset is_archive state
	 *
	 * @since 4.5.5
	 * @param string $title
	 * @return string
	 */
	function avia_wpseo_pre_get_document_title_after( $title )
	{
		global $wp_query, $avia_wp_query_archive_state;

		if( avia_wpseo_alb_shop_page() )
		{
			$wp_query->is_archive = $avia_wp_query_archive_state;
		}

		return $title;
	}

	add_filter( 'pre_get_document_title', 'avia_wpseo_pre_get_document_title_before', 1, 1 );
	add_filter( 'pre_get_document_title', 'avia_wpseo_pre_get_document_title_after', 99999, 1 );
}


if( ! function_exists( 'avia_extract_shortcodes_attachment_ids' ) )
{
	/**
	 * Enable Yoast SEO to index ALB elements that contains images.
	 * https://github.com/KriesiMedia/wp-themes/issues/1361
	 *
	 * @param array $elements
	 * @param string $content
	 * @return array
	 */
	function avia_extract_shortcodes_attachment_ids( $elements, $content )
	{
		$container = array();

		if( ! empty( $elements ) )
		{
			foreach( $elements as $key => $element )
			{
				preg_match_all( $element['pattern'], $content, $shortcodes );

				foreach( $shortcodes[0] as $shortcode )
				{
					//$src = $element['source'] == 'ids' ? '/ids=\\\'(\d+(,\d+)*)\\\'/' : '/attachment=\\\'(\d+)\\\'/';

					switch( $element['source'] )
					{
						case 'ids':
							$src = '/ids=\\\'(\d+(,\d+)*)\\\'/';
							break;
						case 'attachment':
							$src = '/attachment=\\\'(\d+)\\\'/';
							break;
						case 'sid':
							$src = '/id=\\\'(\d+)\\\'/sim';
							break;
						default:
							return;
					}

					$sid = array();

					preg_match_all( $src, $shortcode, $id );

					if( $src = 'sid' )
					{
						foreach( $id[1] as $key => $value )
						{
							$sid[] = $value;
						}

						$sid = implode( ',', $sid );
						$id[1] = $sid;
					}

					$container[] = $id[1];
				}
			}
		}

		if( ! empty( $container ) )
		{
			foreach( $container as $key => $value )
			{
				$container[ $key ] = explode( ',', $value );
			}
		}

		if( count( $container ) > 0 )
		{
			$container = call_user_func_array( 'array_merge', $container );
		}

		return $container;
	}
}

if( ! function_exists( 'avia_filter_wpseo_sitemap_urlimages' ) )
{
	add_filter( 'wpseo_sitemap_urlimages', 'avia_filter_wpseo_sitemap_urlimages', 10, 2 );

	function avia_filter_wpseo_sitemap_urlimages( $images, $post_id )
	{
		$post = get_post( $post_id );

		if( is_object( $post ) )
		{
			$content = $post->post_content;

			$elements = apply_filters( 'avf_add_elements_wpseo_sitemap',
				array(
					'masonry' => array(
									'pattern'	=> '/\[av_masonry_gallery [^]]*]/',
									'source'	=> 'ids'
								),
					'gallery' => array(
									'pattern'	=> '/\[av_gallery [^]]*]/',
									'source'	=> 'ids'
								),
					'horizontal' => array(
									'pattern'	=> '/\[av_horizontal_gallery [^]]*]/',
									'source'	=> 'ids'
								)
/*
					'accordion' => array(
									'pattern'	=> '/\[av_slideshow_accordion(.+?)?\](?:(.+?)?\[\/av_slideshow_accordion\])?/sim',
									'source'	=> 'sid'
								),
					'slideshow' => array(
									'pattern'	=> '/\[av_slideshow(.+?)?\](?:(.+?)?\[\/av_slideshow\])?/sim',
									'source'	=> 'sid'
								),
					'slideshow_full' => array(
									'pattern'	=> '/\[av_slideshow_full(.+?)?\](?:(.+?)?\[\/av_slideshow_full\])?/sim',
									'source'	=> 'sid'
								),
					'slideshow_fullscreen' => array(
									'pattern'	=> '/\[av_fullscreen(.+?)?\](?:(.+?)?\[\/av_fullscreen\])?/sim',
									'source'	=> 'sid'
								)
*/
					), $post_id );

			$ids = avia_extract_shortcodes_attachment_ids( $elements, $content );

			foreach( $ids as $id )
			{
				$title = get_the_title( $id );
				$alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
				$src = wp_get_attachment_url( $id );

				$images[] = array(
								'src'	=> $src,
								'title'	=> $title,
								'alt'	=> $alt
							);
			}
	  }

	  return $images;
	}
}

/*
// include more image elements to be indexed
// https://kriesi.at/support/topic/how-to-make-images-in-masonry-gallery-to-be-indexed-in-seo-yoast-sitemap/
add_filter('avf_add_elements_wpseo_sitemap', function($elements, $postid) {
	$image = array(
		'image' => array(
			'pattern' => '/\[av_image [^]]*]/',
			'source' => 'src')
	);

	return array_merge($image, $elements);
}, 10, 2);
*/


if( ! function_exists( 'avia_wpseo_sitemap_exclude_pages' ) )
{
	add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'avia_wpseo_sitemap_exclude_pages', 10, 1 );

	/**
	 * Callback to theme to get a list of all pages that should be excluded from sitemap
	 *
	 * @since 4.5.1
	 * @param array $post_ids
	 * @return array
	 */
	function avia_wpseo_sitemap_exclude_pages( array $post_ids = array() )
	{
		/**
		 *
		 * @used_by				Avia_Custom_Pages							10
		 * @used_by				enfold\config-wpml\config.php				20
		 * @since 4.5.1
		 */
		$post_ids = apply_filters( 'avf_get_special_pages_ids', $post_ids, 'sitemap' );

		$post_ids = array_unique( $post_ids, SORT_NUMERIC );

		return $post_ids;
	}
}

if( ! function_exists( 'avia_wpseo_process_shortcode_in_backend' ) )
{
	/**
	 * This is a beta trial only.
	 * Process shortcode in backend if not called with ajax
	 * Ajax call wpseo_filter_shortcodes has only opening tags of shortcodes. Processing makes no sense.
	 *
	 * @since 4.5.7.1
	 * @param string $process
	 * @param aviaShortcodeTemplate $class
	 * @param array $atts
	 * @param string $content
	 * @param string $shortcodename
	 * @param boolean $fake
	 * @return string						'' | 'process_shortcode_in_backend'
	 */
	function avia_wpseo_process_shortcode_in_backend( $process, $class, $atts, $content, $shortcodename, $fake )
	{
		if( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && ( 'wpseo_filter_shortcodes' == $_REQUEST['action'] ) )
		{
//			return '';		//	as a try we evaluate shortcodes even if we have no content
			return 'process_shortcode_in_backend';
		}

		/**
		 * Currently we do not alter this
		 */
		if( defined( 'DOING_AJAX' ) && DOING_AJAX )
		{
			return $process;
		}

		return 'process_shortcode_in_backend';
	}

	add_filter( 'avf_process_shortcode_in_backend', 'avia_wpseo_process_shortcode_in_backend', 20, 6 );
}
