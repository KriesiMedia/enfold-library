<?php
/**
 * Page Split Element
 *
 * Add a page split to the template. A pagination helps the user to navigate to the previous/next page.
 *
 *
 *
 * @since 4.8.7.2		modified code because WP (> 5.5) reroutes non existing singular post pages to first page
 *
 */
if( ! defined( 'ABSPATH' ) ) { exit; }		// Don't load directly

/**
 * Currently only in BETA
 * ======================
 * 
 */
if( ! current_theme_supports( 'avia_template_builder_page_split_element' ) )
{
	return;
}


if( ! class_exists( 'av_sc_page_split' ) )
{
	class av_sc_page_split extends aviaShortcodeTemplate
	{
		/**
		 * @since 4.8.7.2
		 * @var boolean
		 */
		protected $filter_content;

		/**
		 * @since 4.8.7.2
		 * @var string
		 */
		protected $split_string;

		/**
		 * @since 4.8.7.2
		 * @param AviaBuilder $builder
		 */
		public function __construct( AviaBuilder $builder )
		{
			$this->filter_content = false;
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
			$this->config['order']			= 1;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_sc_page_split';
			$this->config['tinyMCE']		= array( 'disable' => 'true' );
			$this->config['tooltip']		= __( 'Add a page split to the template. A pagination helps the user to navigate to the previous/next content part. Do not use together with other pagination on this page.', 'avia_framework' );
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
		 * @since 4.8.7.2				moved inside class
		 * @param string $content
		 * @return string
		 */
		public function handler_avf_template_builder_content( $content )
		{
			/*
			 * remove WP default paging used in wp_link_pages()
			 */
			global $multipage, $numpages;

			$numpages = 1;
			$multipage = 0;

			$page = avia_get_current_pagination_number( 'avia-element-paging' );

			if( false === strpos( $content, $this->split_string ) )
			{
				return $content;
			}

			$content = trim( $content );

			$content = str_replace( "\n{$this->split_string}\n", $this->split_string, $content );
			$content = str_replace( "\n{$this->split_string}", $this->split_string, $content );
			$content = str_replace( "{$this->split_string}\n", $this->split_string, $content );

			// Ignore nextpage at the beginning of the content.
			if ( 0 === strpos( $content, $this->split_string ) )
			{
				$content = substr( $content, strlen( $this->split_string ) );
			}

			$content_pages = explode( $this->split_string, $content );
			$total_pages = count( $content_pages );

			//check if we have at least 2 pages...
			if( $total_pages > 1 )
			{
				$index = $page - 1;

				if( isset( $content_pages[ $index ] ) )
				{
					$content = $content_pages[ $index ];
					$content .= avia_pagination( $total_pages, 'nav', 'avia-element-paging', $page );
				}
			}

			return $content;
		}
	}
}

