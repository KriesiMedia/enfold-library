<?php
/**
 * Page Split Element - in BETA only
 *
 * Add a page split to the template. A pagination helps the user to navigate to the previous/next page.
 *
 * ATTENTION:
 * ==========
 * Due to WP routing not all permalink structures are currently supported. Please check this when using this element.
 *
 *
 * @since 4.8.7.1		modified code because WP (> 5.5) reroutes non existing singular post pages to first page
 *
 * ToDo:
 * =====
 * Switch to avia_pagination() when we get more reports
 * 
 */

// Don't load directly
if( ! defined( 'ABSPATH' ) ) { exit; }

if( current_theme_supports( 'avia_template_builder_page_split_element' ) )
{
	if( ! class_exists( 'av_sc_page_split' ) )
	{
		class av_sc_page_split extends aviaShortcodeTemplate
		{
			/**
			 * @since 4.8.7.1
			 * @var boolean
			 */
			protected $filter_content;

			/**
			 * @since 4.8.7.1
			 * @var boolean
			 */
			protected $filter_user;

			/**
			 * @since 4.8.7.1
			 * @var string
			 */
			protected $split_string;

			/**
			 * @since 4.8.7.1
			 * @param AviaBuilder $builder
			 */
			public function __construct( AviaBuilder $builder )
			{
				$this->filter_content = false;
				$this->filter_user = false;
				$this->split_string = '<!--avia_template_builder_nextpage-->';

				parent::__construct( $builder );
			}

			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['self_closing']	= 'yes';

				$this->config['name']			= __( 'Page Split', 'avia_framework' );
				$this->config['tab']			= __( 'Layout Elements', 'avia_framework' );
				$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-heading.png';
				$this->config['order']			= 5;
				$this->config['target']			= 'avia-target-insert';
				$this->config['shortcode']		= 'av_sc_page_split';
				$this->config['tinyMCE']		= array( 'disable' => 'true' );
				$this->config['tooltip']		= __( 'IN BETA ONLY: Add a page split to the template. A pagination helps the user to navigate to the previous/next page. Please check, that your permalink structure is supported by this element.', 'avia_framework' );
				$this->config['drag-level']		= 1;
			}

			/**
			 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
			 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
			 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
			 *
			 *
			 * @param array $params this array holds the default values for $content and $args.
			 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
			 */
			function editor_element( $params )
			{
				$params = parent::editor_element( $params );

				$params['content'] = null; //remove to allow content elements
				return $params;
			}

			/**
			 * Frontend Shortcode Handler
			 *
			 * @param array $atts array of attributes
			 * @param string $content text within enclosing form of shortcode element
			 * @param string $shortcodename the shortcode found, when == callback name
			 * @return string $output returns the modified html string
			 */
			function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
			{
				if( ! $this->filter_content )
				{
					add_filter( 'avf_template_builder_content', array( $this, 'handler_avf_template_builder_content' ), 10, 1 );

					$this->filter_content = true;
				}

				return $this->split_string;
			}

			/**
			 * Get page content according to requested page
			 *
			 * @since 4.8.7.1				moved inside class
			 * @param string $content
			 * @return string
			 */
			public function handler_avf_template_builder_content( $content )
			{
				/*
				 * multipage support - adds page split to content if user uses the element in the page builder
				 * nextpage code taken from /wp-includes/query.php and slightly modified
				 */
				global $id, $page, $pages, $multipage, $numpages, $paged;

				$numpages = 1;
				$multipage = 0;

				if( get_query_var( 'paged' ) )
				{
					$page = get_query_var( 'paged' );
				}
				else if( get_query_var( 'page' ) )
				{
					$page = get_query_var( 'page' );
				}
				else
				{
					$page = 1;
				}

				if( false !== strpos( $content, $this->split_string ) )
				{
					$content = str_replace( "\n{$this->split_string}\n", $this->split_string, $content );
					$content = str_replace( "\n{$this->split_string}", $this->split_string, $content );
					$content = str_replace( "{$this->split_string}\n", $this->split_string, $content );

					// Ignore nextpage at the beginning of the content.
					if ( 0 === strpos( $content, $this->split_string ) )
					{
						$content = substr( $content, 15 );
					}

					$pages = explode( $this->split_string, $content );
					$numpages = count( $pages );
					if ( $numpages > 1 )
					{
						$multipage = 1;
					}
				}

				//check if we have at least 2 pages...
				if( count( $pages ) > 1 )
				{
					$current_page = (int) $page - 1;

					if( isset( $pages[ $current_page ] ) )
					{
						$content = $pages[ $current_page ];
					}

					if( ! $this->filter_user )
					{
						//	since WP 5.5 reroutes non existing singular post pages to first page
						add_filter( 'user_trailingslashit', array( $this, 'handler_wp_user_trailingslashit' ), 10, 2 );

						$this->filter_user = true;
					}
				}

				return $content;
			}

			/**
			 * WP reroutes non existing singular post pages to first page if page/ is missing
			 *
			 * @since 4.8.7.1
			 * @param string $url
			 * @param string $type_of_url
			 * @return string
			 */
			public function handler_wp_user_trailingslashit( $url, $type_of_url )
			{
				if( 'single_paged' != $type_of_url )
				{
					return $url;
				}

				global $wp_rewrite;

				$page_base = $wp_rewrite->pagination_base . '/';

				//	frontpage has already set correctly - see wp-includes\post-template.php  _wp_link_page()
				if( false !== strpos( $url, $page_base ) )
				{
					return $url;
				}

				$page = untrailingslashit( $url );
				if( ! is_numeric( $page ) )
				{
					return $url;
				}

				$new_url = $page_base . $url;

				return $new_url;
			}
		}
	}
}
