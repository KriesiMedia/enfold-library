<?php

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


/*
 * Adjustments for the Yoast WordPress SEO Plugin
 */

if( ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'wpSEO', false ) )
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

		/**
		 * Our analytics script extends the Layout Builder script - only add it where the builder is loaded.
		 * Hooked with priority 20 so the builder (priority 10) has registered its script already.
		 *
		 * @since 8.2.1
		 */
		if( ! wp_script_is( 'avia_builder_js', 'registered' ) )
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
		add_action( 'admin_enqueue_scripts', 'avia_wpseo_register_assets', 20 );
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

		if( ! $post instanceof WP_Post || ! class_exists( 'WooCommerce', false ) )
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



if( ! function_exists( 'avia_wpseo_extract_shortcodes_attachment_ids' ) )
{
	/**
	 * Enable Yoast SEO to index ALB elements that contains images.
	 * https://github.com/KriesiMedia/wp-themes/issues/1361
	 *
	 * @since 5.4							modified
	 * @since 5.6.11						extended for more ALB elements and post content
	 * @param array $elements
	 * @param string $content
	 * @return array
	 */
	function avia_wpseo_extract_shortcodes_attachment_ids( $elements, $content )
	{
		$container = array();

		if( ! empty( $elements ) )
		{
			foreach( $elements as $key => $element )
			{
				$shortcodes = array();
				preg_match_all( $element['pattern'], $content, $shortcodes );

				foreach( $shortcodes[0] as $shortcode )
				{
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
						case 'tab':
							$src = '/tab_image=\\\'(\d+)\\\'/sim';
							break;
						case 'front':
							$src = '/front_bg_image=\\\'(\d+)\\\'/sim';
							break;
						case 'back':
							$src = '/back_bg_image=\\\'(\d+)\\\'/sim';
							break;
						default:
							continue 2;
					}

					$id = array();
					preg_match_all( $src, $shortcode, $id );

					foreach( $id[1] as $key => $value )
					{
						if( empty( $value ) )
						{
							continue;
						}

						$img_ids = explode( ',', $value );

						$container = array_merge( $container, $img_ids );
					}
				}
			}
		}

		return array_unique( $container, SORT_NUMERIC );
	}
}


if( ! function_exists( 'avia_wpseo_filter_sitemap_urlimages' ) )
{
    /**
     *
     * @param array $images
     * @param int $post_id
     * @return array
     */
    function avia_wpseo_filter_sitemap_urlimages( $images, $post_id )
    {
		$post = get_post( $post_id );

		if( $post instanceof WP_Post )
		{
			$content = $post->post_content;

			$posts_elements = array(
									'av_masonry_entries',
									'av_blog',
									'av_postslider',
									'av_magazine',
									'av_portfolio',
									'av_postslider'
								);

			$posts_images = array();

			foreach( $posts_elements as $post_element )
			{
				$shortcode_contents = avia_wpseo_get_shortcodes_by_name( $post_element, $content );

				foreach( $shortcode_contents as $shortcode_content )
				{
					if( $shortcode_content )
					{
						$thumbnails = avia_wpseo_process_shortcode_for_thumbnails( $post_element, $shortcode_content );

						if( is_array( $thumbnails ) && ! empty( $thumbnails ) )
						{
							$posts_images = array_merge( $posts_images, $thumbnails );
						}
					}
				}
			}

			$image_elements = array(
									'av_image_src'				=> array( 'pattern' => '/\[av_image [^]]*]/', 'source' => 'src' ),
									'av_image_attachment'		=> array( 'pattern' => '/\[av_image [^]]*]/', 'source' => 'attachment' ),
									'av_partner_logo'			=> array( 'pattern' => '/\[av_partner_logo [^]]*]/', 'source' => 'sid' ),
									'av_masonry'				=> array( 'source' => 'ids' ),
									'av_gallery'				=> array( 'source' => 'ids' ),
									'av_horizontal'				=> array( 'source' => 'ids' ),
									'av_accordion'				=> array( 'source' => 'sid' ),
									'av_slideshow'				=> array( 'source' => 'sid' ),
									'av_slideshow_full'			=> array( 'source' => 'sid' ),
									'av_slideshow_fullscreen'	=> array( 'source' => 'sid' ),
									'av_color_section'			=> array( 'source' => 'attachment' ),
									'av_team_member'			=> array( 'source' => 'attachment' ),
									'av_image_hotspot'			=> array( 'source' => 'attachment' ),
									'av_tab_sub_section'		=> array( 'source' => 'attachment' ),
									'av_tab_sub_section_tab'	=> array( 'source' => 'tab' ),
									'av_icongrid_item_front'	=> array( 'source' => 'front' ),
									'av_icongrid_item_back'		=> array( 'source' => 'back' )
								);

			foreach( $image_elements as $key => $image)
			{
				$pattern = '';

				if( array_key_exists( 'pattern', $image ) )
				{
					$pattern = $image['pattern'];
				}
				else
				{
					$pattern = '/\\[' . preg_quote( $key ) . '(.+?)?\\](?:(.+?)?\\[\\/' . preg_quote( $key ) . '\\])?/sim';
				}

				$elements[ str_replace( 'av_', '', $key ) ] = array(
																'pattern'	=> $pattern,
																'source'	=> $image['source']
															);
			}

			$column_elements = array(
									'av_one_full',
									'av_one_half',
									'av_one_third',
									'av_one_fourth',
									'av_one_fifth',
									'av_two_third',
									'av_three_fourth',
									'av_two_fifth',
									'av_three_fifth',
									'av_four_fifth',
									'av_cell_one_full',
									'av_cell_one_half',
									'av_cell_one_third',
									'av_cell_one_fourth',
									'av_cell_one_fifth',
									'av_cell_two_third',
									'av_cell_three_fourth',
									'av_cell_two_fifth',
									'av_cell_three_fifth',
									'av_cell_four_fifth'
								);

			foreach( $column_elements as $column )
			{
				$pattern = '/\\[' . preg_quote( $column ) . '(.+?)?\\](?:(.+?)?\\[\\/' . preg_quote( $column ) . '\\])?/sim';

				$elements[ str_replace( 'av_', '', $column ) ] = array(
																'pattern' => $pattern,
																'source' => 'attachment'
															);
			}

			/**
			 *
			 * @param array $elements
			 * @param int $post_id
			 * @return array
			 */
			$elements = apply_filters( 'avf_add_elements_wpseo_sitemap', $elements, $post_id );

			$ids = avia_wpseo_extract_shortcodes_attachment_ids( $elements, $content );

			foreach( $ids as $id )
			{
				$images[] = array(
								'src'   => wp_get_attachment_url( $id ),
								'title' => get_the_title( $id ),
								'alt'   => get_post_meta( $id, '_wp_attachment_image_alt', true )
							);
			}
		}

		return array_merge( $images, $posts_images );
    }

    add_action( 'wpseo_sitemap_urlimages', 'avia_wpseo_filter_sitemap_urlimages', 10, 2 );
}


if( ! function_exists( 'avia_wpseo_get_shortcodes_by_name' ) )
{
	/**
	 * @since 5.6.11
	 * @param string $shortcode_name
	 * @param string $content
	 * @return bool|string
	 */
	function avia_wpseo_get_shortcodes_by_name( $shortcode_name, $content )
	{
		$matches = array();

		preg_match_all( "/\[{$shortcode_name}(.*?)\]/", $content, $matches );

		if( isset( $matches[0] ) )
		{
			return $matches[0];
		}

		return false;
	}
}

if( ! function_exists( 'avia_wpseo_process_shortcode_for_thumbnails' ) )
{
	/**
	 * @since 5.6.11
	 * @param string $shortcode_name
	 * @param string $shortcode_content
	 * @return array
	 */
	function avia_wpseo_process_shortcode_for_thumbnails( $shortcode_name, $shortcode_content )
	{
		$thumbnails = array();
		$blog_type = '';
		$shortcode_content = stripslashes( $shortcode_content );
		$term_ids = '';
		$count = 10;
		$blog_count_matches = array();
		$blog_type_matches = array();
		$term_matches = array();
		$taxonomy_matches = array();

		preg_match( '/items=["\'](\d+)["\']/', $shortcode_content, $blog_count_matches );
		preg_match( '/blog_type=["\']([^"\']+)["\']/', $shortcode_content, $blog_type_matches );

		if( isset( $blog_count_matches[1] ) )
		{
			$count = $blog_count_matches[1];
		}

		if( isset( $blog_type_matches[1] ) )
		{
			$blog_type = $blog_type_matches[1];
		}

		if( $blog_type === 'posts' || $shortcode_name == 'av_portfolio' )
		{
			preg_match( '/categories=\'([\d,]+)\'/', $shortcode_content, $term_matches );
			$taxonomy = $shortcode_name == 'av_portfolio' ? 'portfolio_entries' : 'category';
		}

		if( $blog_type === 'taxonomy' || ( empty( $blog_type) && in_array( $shortcode_name, array( 'av_magazine', 'av_postslider', 'av_masonry_entries' ) ) )  )
		{
			preg_match( '/link=[\'"]([^\'"]+)[\'"]/', $shortcode_content, $taxonomy_matches );

			if( isset( $taxonomy_matches[1] ) )
			{
				$taxonomy_parts = explode( ',', $taxonomy_matches[1] );
				$taxonomy = isset( $taxonomy_parts[0] ) ? $taxonomy_parts[0] : '';

				preg_match( '/(\d+(,\d+)*)/', $taxonomy_matches[1], $term_matches );
			}
		}

		$query_args = array(
						'post_type'      => array('portfolio', 'post', 'product'),
						'posts_per_page' => $count
					);

		if( isset( $term_matches[1] ) && $taxonomy )
		{
			$term_ids = explode( ',', $term_matches[1] );
		}

		$tax_query = array(
						'taxonomy' => $taxonomy,
						'field' => 'term_id'
					);

		if( ! empty( $term_ids ) )
		{
			$tax_query['terms'] = $term_ids;
		}
		else
		{
			$terms = get_terms( array(
									'taxonomy' => $taxonomy,
									'fields'   => 'ids'
								) );

			if( ! is_wp_error( $terms ) && ! empty( $terms ) )
			{
				$tax_query['terms'] = $terms;
			}
		}

		$query_args['tax_query'] = array( $tax_query );

		$query = new WP_Query( $query_args );

		if( $query->have_posts() )
		{
			while ( $query->have_posts() )
			{
				$query->the_post();
				$url = get_the_post_thumbnail_url( get_the_ID() );
				$title = get_the_title( get_the_ID() );
				$alt = get_post_meta( get_the_ID(), '_wp_attachment_image_alt', true );

				$thumbnail = array(
								'src'	=> $url,
								'title'	=> $title,
								'alt'	=> $alt
							);

				if( $thumbnail )
				{
					$thumbnails[] = $thumbnail;
				}
			}

			wp_reset_postdata();
		}

		return $thumbnails;
	}
}

if( ! function_exists( 'avia_filter_wpseo_xml_sitemap_transient_caching' ) )
{
	/**
	 * Disable cache in WP_DEBUG mode
	 *
	 * @since 5.4
	 * @param boolean $cache
	 * @return boolean
	 */
	function avia_filter_wpseo_xml_sitemap_transient_caching( $cache )
	{
		if( defined( 'WP_DEBUG' ) && WP_DEBUG )
		{
			$cache = false;
		}

		return $cache;
	}

	add_filter( 'wpseo_enable_xml_sitemap_transient_caching', 'avia_filter_wpseo_xml_sitemap_transient_caching', 10, 1 );
}


if( ! function_exists( 'avia_wpseo_sitemap_exclude_pages' ) )
{
	add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', 'avia_wpseo_sitemap_exclude_pages', 10, 1 );

	/**
	 * Callback to theme to get a list of all pages that should be excluded from sitemap
	 *
	 * @link https://kriesi.at/support/topic/yoast-seo-fatal-error-on-post-sitemap-xml-or-page-sitemap-xml/
	 * Filter was called with null -> removed type hint array for parameter $post_ids
	 *
	 * @since 4.5.1
	 * @since 5.6.7				removed type hint array for parameter $post_ids
	 * @param array|null $post_ids
	 * @return array
	 */
	function avia_wpseo_sitemap_exclude_pages( $post_ids = array() )
	{
		if( ! is_array( $post_ids ) )
		{
			if( is_numeric( $post_ids ) )
			{
				$post_ids = array( $post_ids );
			}
			else
			{
				$post_ids = array();
			}
		}

		/**
		 *
		 * @used_by				Avia_Custom_Pages							10
		 * @used_by				enfold\config-wpml\config.php				20
		 * @since 4.5.1
		 * @param array $post_ids
		 * @param string $context
		 * @return array
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
