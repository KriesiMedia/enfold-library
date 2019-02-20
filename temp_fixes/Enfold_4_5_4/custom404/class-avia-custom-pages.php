<?php
/**
 * Adds logic for special pages like 404, maintainance mode, footer page
 *
 * @since 4.5.2
 * @author guenter
 */
if ( ! defined( 'ABSPATH' ) )   { exit; }		    // Exit if accessed directly

if( ! class_exists( 'Avia_Custom_Pages' ) )
{
	class Avia_Custom_Pages 
	{
		/**
		 * @since 4.5.2
		 * @var Avia_Custom_Pages 
		 */
		static private $_instance = null;
		
		
		/**
		 * Returns the only instance
		 * 
		 * @since 4.5.2
		 * @return Avia_Custom_Pages
		 */
		static public function instance()
		{
			if( is_null( Avia_Custom_Pages::$_instance ) )
			{
				Avia_Custom_Pages::$_instance = new Avia_Custom_Pages();
			}
			
			return Avia_Custom_Pages::$_instance;
		}
		
		/**
		 * @since 4.5.2
		 */
		protected function __construct() 
		{
			/**
			 * Callback filter to request special pages id's (e.g. used by config-yoast, config-wpml)
			 */
			add_filter( 'avf_get_special_pages_ids', array( $this, 'handler_get_special_pages_ids' ), 10, 2 );
			
			add_filter( '404_template', array( $this, 'handler_404_template' ), 999, 1 );
			add_action( 'wp', array( $this, 'handler_wp_custom_404_page' ), 1 );
			
			add_action( 'template_include', array( $this, 'handler_maintenance_mode' ), 4000, 1 );
			add_filter( 'template_include', array( $this, 'handler_force_reroute_to_404' ), 5000, 1 );
			
			add_action( 'admin_bar_menu', array( $this, 'handler_maintenance_mode_admin_info' ), 100 );
			add_filter( 'display_post_states', array( $this, 'handler_display_post_state' ), 10, 2 );
			add_filter( 'avf_builder_button_params', array( $this, 'handler_special_page_message' ), 10000, 1 );
			
			add_action( 'pre_get_posts', array( $this, 'handler_hide_special_pages' ), 10, 1 );
			add_filter( 'wp_list_pages_excludes', array( $this, 'handler_wp_list_pages_excludes' ), 10, 1 );
			add_filter( 'avf_ajax_search_query',  array( $this, 'handler_avf_ajax_search_exclude_pages' ), 10, 1 );
			
			add_filter( 'ava_builder_template_after_header', array( $this, 'handler_modified_main_query' ), 999 );
			add_filter( 'ava_page_template_after_header', array( $this, 'handler_modified_main_query' ), 999 );
		}
				
		
		/**
		 * Error 404 - Reroute to a Custom Page
		 * Hooks into the 404_template filter and performs a redirect to that page.
		 * 
		 * @author tinabillinger - modified by günter
		 * @since 4.3 - 4.4.2 - 4.5.2
		 * 
		 * @param string $template
		 * @return string
		 */
		public function handler_404_template( $template )
		{
			if( true === $this->get_custom_page_object( 'maintenance', $template, 'option_set' ) )
			{
				return get_page_template();
			}
			
			$error404 = $this->get_custom_page_object( '404', $template );
			if( false === $error404 )
			{
				return $template;
			}
			
			if( 'error404_redirect' == avia_get_option( 'error404_custom' ) )
			{
				$error404_url = get_permalink( $error404->ID );
				$error404_url = add_query_arg( 'avia_forced_reroute', '1', $error404_url );
				
				/**
				 * @since 4.5.5
				 * @return int
				 */
				$status = apply_filters( 'avf_404_redirect_status', 301 );
				
				if( wp_redirect( $error404_url, $status ) )
				{
					exit;
				}
				
				return template;
			}

			return $this->modify_page_query( $error404->ID, '404' );
		}
		
		/**
		 * Check if a redirect to a custom 404 page has happened
		 * 
		 * @since 4.5.4
		 */
		public function handler_wp_custom_404_page()
		{
			$error404 = $this->get_custom_page_object( '404' );
			if( false === $error404 )
			{
				return;
			}
			
			if( ! is_page( $error404->ID ) )
			{
				return;
			}
			
			$forced = isset( $_REQUEST['avia_forced_reroute'] ) && ( '1' == $_REQUEST['avia_forced_reroute'] );
			
			/**
			 * Returning anything except false displays the normal page content without changeing the status to 404
			 * 
			 * @since 4.5.5
			 * @param WP_Post $error404
			 * @param boolean $forced
			 * @return true|mixed			
			 */
			if( false !== apply_filters( 'avf_404_supress_status_code', false, $error404, $forced ) )
			{
				return;
			}
			
			/**
			 * Do not call $wp_query->set_404(); as suggested in some stackoverflow posts -
			 * that leads to an endless loop with handler_404_template  !!!!!
			 */
			status_header(404);
			nocache_headers();
		}
		
		/**
		 * Returns the custom page object. Should return a translated object if that plugin hooks correctly into get_posts()
		 * 
		 * @since 4.5.4
		 * @param string $which					'404' | 'maintenance' | 'footer_page'
		 * @param string $template 
		 * @param string $return				'object' | 'option_set'
		 * @return WP_Post|boolean
		 */
		public function get_custom_page_object( $which, $template = '', $return = 'object' )
		{
			switch( $which )
			{
				case '404':
					$option = avia_get_option( 'error404_custom', '' );
					if( ! in_array( $option, array( 'error404_custom', 'error404_redirect' ) ) )
					{
						return false;
					}
					$page_id = avia_get_option( 'error404_page', 0 );
					break;
				case 'maintenance':
					$option = avia_get_option( 'maintenance_mode', '' );
					if( ! in_array( $option, array( 'maintenance_mode', 'maintenance_mode_redirect' ) ) )
					{
						return false;
					}
					$page_id = avia_get_option( 'maintenance_page', 0 );
					break;
				case 'footer_page':
					$option = avia_get_option( 'display_widgets_socket', '' );
					if( ! in_array( $option, array( 'page_in_footer_socket', 'page_in_footer' ) ) )
					{
						return false;
					}
					$page_id = avia_get_option( 'footer_page', 0 );
					break;
				default:
					return false;
			}
			
			if( 'option_set' == $return )
			{
				return true;
			}
			
			/**
			 * Allow 3rd party (e.g. translation plugins) to change the page id
			 * (WPML already returns correct ID from avia_get_option !)
			 * 
			 * @used_by					currently unused
			 * @since 4.5.2
			 * @param int $page_id
			 * @param string $template
			 * @return int|false
			 */
			$page_id = apply_filters( 'avf_custom_page_id', $page_id, $which, $template );
			
			return $this->query_page( $page_id, $which );
		}

		/**
		 * Reroute to 404 if user wants to access a page he is not allowed to
		 * Currently only a page that is selected to be used as footer
		 *  
		 * @since 4.2.7
		 * @added_by Günter
		 * @param string $original_template 
		 * @return string 
		 */
		public function handler_force_reroute_to_404( $original_template )
		{
			global $avia_config, $wp_query;

			if( isset( $_REQUEST['avia_forced_reroute'] ) && ( '1' == $_REQUEST['avia_forced_reroute'] ) )
			{
				return $original_template;
			}
			
			if( isset( $avia_config['modified_main_query'] ) && $avia_config['modified_main_query'] instanceof WP_Query )
			{
				return $original_template;
			}
			
			/**
			 * Get all pages that are not allowed to be accessed directly
			 * 
			 * @used_by					Avia_Custom_Pages						10
			 * @used_by					enfold\config-wpml\config.php			20
			 * @since 4.5.1
			 */
			$special_pages = apply_filters( 'avf_get_special_pages_ids', array(), 'page_load' );

			if( empty( $special_pages ) )
			{
				return $original_template;
			}

			$id = get_the_ID();

			if( ( false === $id ) || ! in_array( $id, $special_pages ) )
			{
				return $original_template;
			}

			if( is_user_logged_in() && current_user_can( 'edit_pages' ) )
			{
				return $original_template;
			}
			
			/**
			 * Returning anything except true prohibits reroute and displays the normal page content
			 * 
			 * @since 4.5.5
			 * @param string $original_template
			 * @param int $id
			 * @param array $special_pages
			 * @return true|mixed			
			 */
			if( true !== apply_filters( 'avf_forced_reroute_to_404', true, $original_template, $id, $special_pages ) )
			{
				return $original_template;
			}
			
			status_header( 404 );
			$new_template = $this->handler_404_template( $original_template );

			if( isset( $avia_config['modified_main_query'] ) && $avia_config['modified_main_query'] instanceof WP_Query )
			{
				return $new_template;
			}

			$wp_query->set_404();
			status_header( 404 );
			get_template_part( '404' );
			exit;
		}
		
		/**
		 * Custom Maintenance Mode Page
		 * 
		 * Returns a 503 (temporary unavailable) status header.
		 * If user forgets to set a page we return a simple message.
		 * 
		 * Logged in users with "edit_published_posts" capability are still able to view the site
		 * 
		 * @author tinabillinger  modified by günter
		 * @since 4.3 / 4.4.2 / 4.5.2
		 * @param string $template
		 * @return string
		 */
		public function handler_maintenance_mode( $template )
		{
			if( is_user_logged_in() && current_user_can( 'edit_published_posts' ) )
			{
				return $template;
			}
			
			if( false === $this->get_custom_page_object( 'maintenance', $template, 'option_set' ) )
			{
				return $template;
			}
			
			/**
			 * Make sure to exclude login form from maintenance mode
			 * https://wordpress.stackexchange.com/questions/12863/check-if-wp-login-is-current-page
			 */
			$abspath = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, ABSPATH );
			$included_files = get_included_files();
			if( in_array( $abspath . 'wp-login.php', $included_files ) || 
				in_array( $abspath . 'wp-register.php', $included_files ) || 
				( isset( $_GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] == 'wp-login.php' ) || 
				$_SERVER['PHP_SELF'] == '/wp-login.php'  )
			{
				return $template;
			}
			
			$fallback = '';
			$fallback .=	'<div style="width:80%; margin:150px auto; font-weight:900;font-size: 40px;text-align: center;">';
			$fallback .=		__( 'Sorry, we are currently updating our site - please try again later.', 'avia_framework' );
			$fallback .=	'</div>';
			
			
			
			$maintenance_page = $this->get_custom_page_object( 'maintenance', $template );
			if( false === $maintenance_page )
			{
				/**
				 * Output a fallback message - no page selected
				 */
				status_header( 503 );
				nocache_headers();
				exit( $fallback );
			}

			$option = avia_get_option( 'maintenance_mode', '' );
			if( 'maintenance_mode_redirect' == $option )
			{
				if( is_page( $maintenance_page->ID ) )
				{
					status_header( 503 );
					nocache_headers();
					return $template;
				}

				$maintenance_url = get_permalink( $maintenance_page->ID );
				$maintenance_url = add_query_arg( 'avia_forced_reroute', '1', $maintenance_url );
				
				/**
				 * @since 4.5.5
				 * @return int
				 */
				$status = apply_filters( 'avf_maintenance_redirect_status', 302 );
				
				if( wp_redirect( $maintenance_url, $status ) )
				{
					exit;
				}
				
				/**
				 * Output a fallback message if redirect failed
				 */
				status_header( 503 );
				nocache_headers();
				exit( $fallback );
			}
			
			status_header( 503 );
			return $this->modify_page_query( $maintenance_page->ID, 'maintenance' );
		}
		
		/**
		 * Reset modified_main_query - needed because e.g. WPML manipulates the post query in get_headers()
		 * Should help to solve problems with plugins changing the main query.
		 * 
		 * @since 4.5.1/4.5.4		(moved from enfold\config-wpml\config.php)
		 */
		public function handler_modified_main_query()
		{
			global $avia_config, $wp_query;

			if( ! isset( $avia_config['modified_main_query'] ) || ( ! $avia_config['modified_main_query'] instanceof WP_Query ) )
			{
				return;
			}
			
			/**
			 * Restore modified query and reset in_the_loop
			 */
			$wp_query = null;
			$wp_query = $avia_config['modified_main_query'];
			$wp_query->rewind_posts();
			$wp_query->in_the_loop = false;

			return;
		}
		
		/**
		 * Maintenance Mode Admin Bar Info
		 * If maintenance mode is active, an info is displayed in the admin bar
		 *
		 * @author tinabillinger
		 * @since 4.3
		 * @param WP_Admin_Bar $admin_bar
		 * @return type
		 */		
		public function handler_maintenance_mode_admin_info( WP_Admin_Bar $admin_bar )
		{
			$maintenance_page = $this->get_custom_page_object( 'maintenance' );
			
			if( $maintenance_page instanceof WP_Post ) 
			{
				$admin_bar->add_menu( array(
								'id'		=> 'av-maintenance',
								'title'		=> __( '<span style="color: #ff33ff; font-weight: bold;">Maintenance Mode Enabled</span>', 'avia_framework' ),
								'parent'	=> 'top-secondary',
								'href'		=> admin_url( 'admin.php?page=avia#goto_avia' ),
								'meta'		=> array(),
							));
			}

			return $admin_bar;
		}
		
		/**
		 * Returns page id's that do not belong to normal page lists 
		 * like custom 404, custom maintainence mode page, custom footer page
		 * 
		 * @since 4.5.2
		 * @param array $post_ids
		 * @param string $context				'page_load' | 'pre_get_posts_filter' | 'wp_list_pages_excludes' | 'avia_ajax_search' | 'sitemap'
		 * @return array
		 */
		public function handler_get_special_pages_ids( $post_ids = array(), $context = '' )
		{
			/**
			 * Return anything except true to hide inactive special pages
			 * 
			 * @since 4.5.5
			 * @return boolean
			 */
			$show_inactive = apply_filters( 'avf_show_inactive_special_pages', true, $post_ids, $context ) === true ? true : false;
			
					// Maintenance Page
			$active = in_array( avia_get_option( 'maintenance_mode' ), array( 'maintenance_mode', 'maintenance_mode_redirect' ) );
			$id = avia_get_option( 'maintenance_page', 0 );
			if( is_numeric( $id ) && (int) $id > 0 )
			{
				if( $active || $show_inactive )
				{
					$post_ids[] = (int) $id;
				}
			}

					// 404 Page
			$active = in_array( avia_get_option( 'error404_custom' ), array( 'error404_custom', 'error404_redirect' ) );
			$id = avia_get_option( 'error404_page', 0 );
			if( is_numeric( $id ) && (int) $id > 0 )
			{
				if( $active || $show_inactive )
				{
					$post_ids[] = (int) $id;
				}
			}

					// Footer Page
			$active = in_array( avia_get_option( 'display_widgets_socket' ), array( 'page_in_footer', 'page_in_footer_socket' ) );
			$id = avia_get_option( 'footer_page', 0 );
			if( is_numeric( $id ) && (int) $id > 0 )
			{
				if( $active || $show_inactive )
				{
					$post_ids[] = (int) $id;
				}
			}

			$post_ids = array_unique( $post_ids, SORT_NUMERIC );
			return $post_ids;
		}		
		
		/**
		 * Remove special pages from page lists in frontend and search results list
		 * 
		 * @since 4.5.2
		 * @param WP_Query $query
		 */
		public function handler_hide_special_pages( WP_Query $query )
		{
			if( is_admin() )
			{
				return;
			}

			/**
			 * @used_by					Avia_Custom_Pages					10
			 * @used_by					config-wpml\config.php				20
			 * @since 4.5.2
			 */
			$pages = apply_filters( 'avf_get_special_pages_ids', array(), 'pre_get_posts_filter' );
			if( empty( $pages ) )
			{
				return;
			}

			if( ! is_search() )
			{
				$post_type = (array) $query->get( 'post_type' );
				if( empty( $post_type ) )
				{
					return;
				}

				$result = array_intersect( $post_type, array( 'page', 'any' ) );
				if( empty( $result ) )
				{
					return;
				}
			}

			$not_in = (array) $query->get( 'post__not_in', array() );
			$not_in = array_unique( array_merge( $not_in, $pages ), SORT_NUMERIC );

			$query->set( 'post__not_in', $not_in );
		}

		/**
		 * Exclude our special pages from page list
		 * 
		 * @since 4.5.2
		 * @param array $exclude_array
		 * @return array
		 */
		public function handler_wp_list_pages_excludes( array $exclude_array )
		{
			if( is_admin() )
			{
				return $exclude_array;
			}

			/**
			 * @used_by					Avia_Custom_Pages					10
			 * @used_by					config-wpml\config.php				20
			 * @since 4.5.2
			 */
			$pages = apply_filters( 'avf_get_special_pages_ids', array(), 'wp_list_pages_excludes' );
			
			$exclude_array = array_unique( array_merge( $exclude_array, (array)$pages ), SORT_NUMERIC );
			
			return $exclude_array;
		}
		
		/**
		 * 
		 * @since 4.5.2
		 * @param string $query_string
		 * @return string|array
		 */
		public function handler_avf_ajax_search_exclude_pages( $query_string )
		{
			$defaults = array();
			
			$query = wp_parse_args( $query_string, $defaults );
					
			/**
			 * @used_by					Avia_Custom_Pages					10
			 * @used_by					config-wpml\config.php				20
			 * @since 4.5.2
			 */
			$pages = apply_filters( 'avf_get_special_pages_ids', array(), 'avia_ajax_search' );
			if( empty( $pages ) )
			{
				return $query_string;
			}
			
			$not_in = isset( $query['post__not_in'] ) ? (array) $query['post__not_in'] : array();
			$not_in = array_unique( array_merge( $not_in, $pages ), SORT_NUMERIC );

			$query['post__not_in'] = $not_in;
			
			return $query;
		}


		/**
		 * Display a notice that a page is used as a special page (e.g. 404, maintenance, footer) and 
		 * cannot be accessed in frontend by non logged in users
		 * 
		 * @since 4.2.7
		 * @added_by Günter
		 * @param array $params
		 * @return array
		 */
		public function handler_special_page_message( array $params )
		{
			$id = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : 0;
			
			if( 0 == $id )
			{
				return $params;
			}
			
			$maintenance_page = avia_get_option( 'maintenance_page', 0 );
			$error404_page = avia_get_option( 'error404_page', 0 );
			$footer_page = avia_get_option( 'footer_page', 0 );
			
			$note = '';
			$add_visibility = true;
			
			$link = admin_url( '?page=avia#goto_' );
			$label = __( 'Change', 'avia_framework' );
			$change = " <a target='_blank' href='{$link}avia'><small>({$label})</small></a>";
			
			if( $maintenance_page == $id )
			{
				if( true === $this->get_custom_page_object( 'maintenance', '', 'option_set' ) )
				{
					$note .= __( 'This page is currently selected to be displayed as maintenance mode page. (Set in Enfold &raquo; Theme Options).', 'avia_framework' );
				}
				else
				{
					$note .= __( 'This page is selected to be displayed as maintenance mode page but is not active at the moment. (Set in Enfold &raquo; Theme Options).', 'avia_framework' );
				}
				
				$note .= $change;
			}
			else if( $error404_page == $id )
			{
				if( true === $this->get_custom_page_object( '404', '', 'option_set' ) )
				{
					$note .= __( 'This page is currently selected to be displayed as custom 404 page. (Set in Enfold &raquo; Theme Options).', 'avia_framework' );
				}
				else
				{
					$note .= __( 'This page is selected to be displayed as custom 404 page but is not active at the moment. (Set in Enfold &raquo; Theme Options).', 'avia_framework' );
				}
				
				$note .= $change;
				$add_visibility = false;
			}
			else if( $footer_page == $id )
			{
				if( true === $this->get_custom_page_object( 'footer_page', '', 'option_set' ) )
				{
					$note .= __( 'This page is currently selected to be displayed as footer. (Set in Enfold &raquo; Footer).', 'avia_framework' );
				}
				else
				{
					$note .= __( 'This page is selected to be displayed as footer but is not active at the moment. (Set in Enfold &raquo; Footer).', 'avia_framework' );
				}
				
				$note .= " <a target='_blank' href='{$link}avia'><small>({$label})</small></a>";
			}
			else
			{
				return $params;
			}

			if( $add_visibility )
			{
				$note .= ' ' . __( 'Therefore it cannot be accessed directly by the general public in your frontend. (Logged in users who are able to edit this page can still see it)', 'avia_framework' );
			}
			
			if( ! empty( $params['note'] ) )
			{
				$note = $params['note'] . '<br /><br />' . $note;
			}

			$params['note'] = $note;
			$params['noteclass'] = '';

			return $params;
		}


		/**
		 * Post state filter
		 * On the Page Overview screen in the backend ( wp-admin/edit.php?post_type=page ) this functions appends a descriptive post state to a page for easier recognition
		 * 
		 * @since 4.3 / 4.5.2
		 * @author Kriesi / Günter
		 * @param array $post_states
		 * @param WP_Post $post
		 * @return array
		 */
		public function handler_display_post_state( $post_states, $post )
		{
			$link = admin_url( '?page=avia#goto_' );
			$label = __( 'Change', 'avia_framework' );
			$change = " <a href='{$link}avia'><small>({$label})</small></a>";

			// Maintenance Page
			if( avia_get_option( 'maintenance_page', 0 ) == $post->ID ) 
			{
				$info = ( true === $this->get_custom_page_object( 'maintenance', '', 'option_set' ) ) ? __( 'Active Maintenance Mode Page', 'avia_framework' ) : __( 'Inactive Maintenance Mode Page', 'avia_framework' );
				$post_states['av_maintain'] = $info . $change;
			}

			// 404 Page
			if ( avia_get_option( 'error404_page', 0 ) == $post->ID ) 
			{
				$info = ( true === $this->get_custom_page_object( '404', '', 'option_set' ) ) ? __( 'Active Custom 404 Page', 'avia_framework' ) : __( 'Inactive Custom 404 Page', 'avia_framework' );
				$post_states['av_404'] = $info . $change;
			}

			// Footer Page
			if ( avia_get_option( 'footer_page', 0 ) == $post->ID ) 
			{
				$info = ( true === $this->get_custom_page_object( 'footer_page', '', 'option_set' ) ) ? __( 'Active Custom Footer Page', 'avia_framework' ) : __( 'Inactive Custom Footer Page', 'avia_framework' );
				$post_states['av_footer'] = $info . " <a href='{$link}footer'><small>({$label})</small></a>";
			}

			return $post_states;
		}
		
		/**
		 * Query the requested page, replace $wp_query and save in $avia_config['modified_main_query']
		 * Does not check, if page exists.
		 * 
		 * @since 4.5.2
		 * @param int $page_id
		 * @param string $context				'404' | 'maintenance' | ''
		 * @return string
		 */
		protected function modify_page_query( $page_id, $context )
		{
			global $wp_query, $avia_config;
			
			/**
			 * Modify existing query to custom 404 page
			 */
			$wp_query = null;
			$wp_query = new WP_Query();
			$wp_query->query( 'page_id=' . $page_id );
			$wp_query->the_post();
			$wp_query->rewind_posts();

			/**
			 * Save query to be able to be restored later - needed e.g. when WPML is active to restore query
			 */
			$avia_config['modified_main_query'] = $wp_query;
//			$avia_config['builder_redirect_id'] = $page_id;
			
			return get_page_template();
		}
		
		/**
		 * Query the page - this allows translation pluginsto hook into and return a translated page
		 * 
		 * @since 4.5.4
		 * @param mixed $page_id
		 * @param string $context			'404' | 'maintenance' | ''
		 * @return WP_Post|false
		 */
		protected function query_page( $page_id = null, $context = '' )
		{
			if( empty( $page_id ) || ! is_numeric( $page_id ) )
			{
				return false;
			}

			$args = array(
							'include'	=> array( $page_id ),
							'post_type' => 'page',
						);
			$posts = get_posts( $args );
			$post = count( $posts ) > 0 ? $posts[0] : false;
			
			/**
			 * Filter result
			 * 
			 * @used_by			currently unused
			 * @since 4.5.4
			 * @return WP_Post|false
			 */
			return apply_filters( 'avf_custom_pages_query_page', $post, $page_id, $context );
		}
	}
	
	/**
	 * Returns the main instance of Avia_Custom_Pages to prevent the need to use globals
	 * 
	 * @since 4.5.2
	 * @return Avia_Custom_Pages
	 */
	function AviaCustomPages() 
	{
		return Avia_Custom_Pages::instance();
	}
}

AviaCustomPages();


		
/**
 * Kept in case we need a fallback - can be removed in future versions
 * removed < 4.5 ??
 * ===================================================================
 */
//        if (avia_get_option('maintenance_mode') == "maintenance_mode") {
//            global $wp_query;
//            $maintenance_page = avia_get_option('maintenance_page');
//            
//            // check if maintenance page is defined
//            if ($maintenance_page) {
//                $maintenance_url = get_permalink($maintenance_page);
//                $current_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//                // make sure site is accessible for logged in users, and the login page is not redirected
//                if ( ($GLOBALS['pagenow'] !== 'wp-login.php') && !current_user_can('edit_published_posts')) {
//                    // avoid infinite loop by making sure that maintenance page is NOT curently viewed
//                    if ($maintenance_url !== $current_url) {
//
//                        // do a simple redirect if WPML or Yoast is active
//                        $use_wp_redirect = false;
//
//                        if( defined('ICL_SITEPRESS_VERSION') && defined('ICL_LANGUAGE_CODE')) {
//                            $use_wp_redirect = true;
//                        }
//
//                        if( (defined('ICL_SITEPRESS_VERSION') && defined('ICL_LANGUAGE_CODE')) || defined('WPSEO_VERSION')) {
//                            $use_wp_redirect = true;
//                        }
//
//                        if( $use_wp_redirect ) {
//                            if (wp_redirect($maintenance_url)) {
//                                exit();
//                            }
//                        }
//                        else {
//                            // hook into the query
//                            $wp_query = null;
//                            $wp_query = new WP_Query();
//                            $wp_query->query('page_id=' . $maintenance_page);
//                            $wp_query->the_post();
//                            $template = get_page_template();
//                            rewind_posts();
//                            status_header(503);
//                            return $template;
//                        }
//                    }
//                }
//            }
//        }
//    }
	
