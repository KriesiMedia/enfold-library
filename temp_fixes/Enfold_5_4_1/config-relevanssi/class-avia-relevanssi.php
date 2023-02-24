<?php
/**
 * Adds support for plugin Relevanssi
 * Plugin URI: https://www.relevanssi.com/
 *
 * @added_by Günter
 * @since 4.5.7.1
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $relevanssi_variables;

/**
 * Bail if plugin not active
 */
if( ! isset( $relevanssi_variables ) || ! isset( $relevanssi_variables['file'] ) )
{
	return;
}

if( ! class_exists( 'Avia_Relevanssi' ) )
{
	class Avia_Relevanssi
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 4.5.7.1
		 * @var Avia_Relevanssi
		 */
		static private $_instance = null;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.5.7.1
		 * @return Avia_Relevanssi
		 */
		static public function instance()
		{
			if( is_null( Avia_Relevanssi::$_instance ) )
			{
				Avia_Relevanssi::$_instance = new Avia_Relevanssi();
			}

			return Avia_Relevanssi::$_instance;
		}


		/**
		 * @since 4.5.7.1
		 */
		protected function __construct()
		{
			add_filter( 'avf_ajax_search_function', array( $this, 'handler_init_ajax_search' ), 10, 4 );
			add_filter( 'avf_process_shortcode_in_backend', array( $this, 'handler_process_shortcode_in_backend' ), 10, 6 );

			add_filter( 'avia_product_slide_query', array( $this, 'handler_avia_product_slide_query' ), 10, 3 );
			add_filter( 'avia_masonry_entries_query', array( $this, 'handler_avia_masonry_entries_query' ), 10, 2 );

		}

		/**
		 * Returns the function to call for ajax search
		 *
		 * @since 4.5.7.1
		 * @param string $function_name
		 * @param array $search_query
		 * @param array $search_parameters
		 * @param array $defaults
		 * @return string
		 */
		public function handler_init_ajax_search( $function_name, $search_query, $search_parameters, array $defaults )
		{
			return 'avia_ajax_relevanssi_search';
		}

		/**
		 * Process shortcode in backend to be able to initialise index
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
		public function handler_process_shortcode_in_backend( $process, $class, $atts, $content, $shortcodename, $fake )
		{
			if( defined( 'DOING_AJAX' ) && DOING_AJAX )
			{
				if( ! isset( $_REQUEST['action'] ) || ! in_array( $_REQUEST['action'], array( 'relevanssi_index_posts' ) ) )
				{
					return $process;
				}
			}

			return 'process_shortcode_in_backend';
		}

		/**
		 *
		 *
		 * @since x.x.x
		 * @param array $query
		 * @param array $params
		 * @param array $ordering_args
		 * @return array
		 */
		public function handler_avia_product_slide_query( $query, $params, $ordering_args )
		{
			return $this->modify_search_query( $query );
		}

		/**
		 * @since x.x.x
		 * @param array $query
		 * @param array $params
		 */
		public function handler_avia_masonry_entries_query( $query, $params )
		{
			return $this->modify_search_query( $query );
		}

		/**
		 * Modify search query - based on relevansi support
		 * https://kriesi.at/support/topic/search-results-page-avia_product_slider-get_style_tag-on-null-error/#post-1399098
		 *
		 * @since x.x.x
		 * @param array $query
		 * @return array
		 */
		protected function modify_search_query( $query )
		{
			if( ! is_array( $query ) )
			{
				return $query;
			}

			if( is_search() && isset( $_REQUEST['s'] ) )
			{
				$query['s'] = $_REQUEST['s'];
				$query['relevanssi'] = true;
			}

			return $query;
		}

		/**
		 *
		 * @since 4.5.7
		 * @param string $search_query
		 * @param array $search_parameters
		 * @param array $defaults
		 * @return array					WP_Post objects
		 */
		static public function ajax_search( $search_query, array $search_parameters, array $defaults )
		{
			global $query;

			$tempquery = $query;

			if( empty( $tempquery ) )
			{
				$tempquery = new WP_Query();
			}

			$tempquery->query_vars = $search_parameters;
			relevanssi_do_query( $tempquery );

			$posts = is_array( $tempquery->posts ) ? $tempquery->posts : array();

			return $posts;
		}

	}

	/**
	 * Returns the main instance of Avia_Relevanssi to prevent the need to use globals
	 *
	 * @since 4.5.7
	 * @return Avia_Relevanssi
	 */
	function AviaRelevanssi()
	{
		return Avia_Relevanssi::instance();
	}

	AviaRelevanssi();


	if( ! function_exists( 'avia_ajax_relevanssi_search' ) )
	{
		/**
		 * Wrapper to call static method.
		 * Returns the search result array
		 *
		 * @since 4.5.7
		 * @param type $search_query
		 * @param array $search_parameters
		 * @param array $defaults
		 * @return array					WP_Post objects
		 */
		function avia_ajax_relevanssi_search( $search_query, array $search_parameters, array $defaults )
		{
			return Avia_Relevanssi::ajax_search( $search_query, $search_parameters, $defaults );
		}
	}

}
