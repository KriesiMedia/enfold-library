<?php
/**
 * The ALB Element Manager class handles information about usage of elements in posts and other additional info needed about the elements.
 * 
 * In a first step it handles shortcodes in ALB and non ALB pages, but will also handle oncoming implementations of ALB elements.
 *
 * @author		Günter
 * @since		4.3
 */

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'aviaElementManager' ) )
{
	
	class aviaElementManager 
	{
		const VERSION			= '1.0.1';				//	Main version - needed to check for main global updates
		const USAGE_VERSION		= '1.0';				//	Stored data structure version - change for minor updates only
		const USAGE_PREFIX		= 'av_alb_usage_';	
		const ELEMENT_UID		= 'av_uid';				//	unique ID attribute key used in shortcode (or jsoin datastructure later)
		
		/**
		 * Accumulate added id's used as prefix
		 * 
		 * @since 4.3
		 * @var int 
		 */
		static private $added_uid = 1;

	
		/**
		 *
		 * @since 4.3
		 * @var array 
		 */
		protected $registered_alb_elements;
		
		/**
		 * Single page info - currently only containing count info
		 * 
		 *		$shortcode =>  array (  'count'		=>	$count,
		 *								'version'	=>	$version
		 *						   )
		 * @since 4.3
		 * @var array 
		 */
		protected $usage_info;
		
		
		/**
		 * Is filled by default in get_header action with the ALB elements of the current post
		 * false, if an error occured generating the elements, null if could not be filled (needs function get_the_ID()
		 * 
		 *		[$shortcode_name] =>  array(
		 *								'element'	=> $shortcode_name
		 *								'count		=> $nr_of_occurrence
		 *						)
		 * @since 4.3
		 * @var array|false|null
		 */
		protected $current_post_elements;
		
		
		/**
		 * 
		 * @since 4.3
		 * @var array|false					$post_id	=>		array(  $element  =>  true|false  )
		 */
		protected $post_elements_state;
		
		
		/**
		 *
		 * @since 4.3
		 * @var array|false					$element  =>  true|false
		 */
		protected $blog_elements_state;

		
		/**
		 *
		 * @since 4.3
		 * @var array						$element	=>	'checked' | 'new' | 'initialising'
		 */
		protected $checked_elements;

		
		/**
		 * 
		 * @since 4.3
		 */
		public function __construct() 
		{
			$this->registered_alb_elements = null;
			$this->usage_info = array();
			$this->current_post_elements = null;
			$this->blog_elements_state = array();
			$this->post_elements_state = array();
			$this->checked_elements = null;
			
			add_action( 'get_header', array( $this, 'handler_wp_get_header' ), 10, 1 );
			
			if( is_admin() )
			{
				add_action( 'admin_init', array( $this, 'handler_check_for_version_updates' ), 10 );
				add_action( 'trashed_post', array( $this, 'handler_wp_trashed_post' ), 10, 1 );
				add_action( 'untrash_post', array( $this, 'handler_wp_untrash_post' ), 10, 1 );
				
				add_action( 'ava_after_import_demo_settings', array( $this, 'handler_after_import_demo' ), 10 );
			}
			
			return $this;
		}

		/**
		 * 
		 * @since 4.3
		 */
		public function __destruct() 
		{
			unset( $this->registered_alb_elements );
			unset( $this->usage_info );
			unset( $this->current_post_elements );
			unset( $this->blog_elements_state );
			unset( $this->post_elements_state );
			unset( $this->checked_elements );
		}
		
		
		/**
		 * Returns all registered ALB element names (currently this is a wrapper for the shortcode names)
		 * 
		 * @since 4.3
		 * @return array
		 */
		public function registered_elements()
		{
			if( is_null( $this->registered_alb_elements ) )
			{
				$this->registered_alb_elements = apply_filters( 'avf_get_all_alb_element_names', array_merge( ShortcodeHelper::$allowed_shortcodes, ShortcodeHelper::$nested_shortcodes ) );
				$this->registered_alb_elements = array_merge( array_unique( $this->registered_alb_elements ) );
			}
			
			return $this->registered_alb_elements;
		}
		
		
		/**
		 * Returns an array of elements and the check status
		 * 
		 * @since 4.3
		 * @return array
		 */
		public function get_checked_elements()
		{
			if( is_null( $this->checked_elements ) )
			{
				$this->checked_elements = get_option( 'av_alb_element_check_stat', array() );
			}
			
			return $this->checked_elements;
		}
		
		
		/**
		 * Update the checked elements array in DB
		 * 
		 * @since 4.3
		 * @param array $elements
		 */
		public function update_checked_elements( array $elements )
		{
			if( serialize( $elements ) == serialize( $this->checked_elements ) )
			{
				return true;
			}
			
			$this->checked_elements = $elements;
			
			return update_option( 'av_alb_element_check_stat', $elements );
		}

		/**
		 * Compares the registered elements with the already existing checked element list.
		 * Adds new elements to the list (marked 'new').
		 * Checks, if all elements have been marked 'checked'
		 * 
		 * We assume new elements as checked, if we find one post that uses that element. Under normal working conditions we catch the first 
		 * use on the post update and add it to the element usage info. 
		 * As long as user does not manipulate post content outside normal WP workflow or uses the shortcode before registering the element
		 * this assumption will work.
		 * 
		 * If we only have new elements (= pageload
		 * 
		 * @since 4.3
		 * @param string $initialise			'' | 'initialise'
		 * @return array						'new' | 'initialising' | 'only_new' | empty array
		 */
		public function all_elements_checked( $initialise = '' )
		{
			static $checked = null;
			
			if( ! is_null( $checked ) && ( 'initialise' != $initialise ) )
			{
				return $checked;
			}
			
			$chk = $this->get_checked_elements();
			
			/**
			 * Only check for new elements in backend
			 */
			if( is_admin() || ( 'initialise' == $initialise ) )
			{
				$update = false;
				$els = $this->registered_elements();
			
				foreach ( $els as $el ) 
				{
					if( ! array_key_exists( $el, $chk ) )
					{
						$chk[ $el ] = 'new';
						$update = true;
					}
				}
			
				foreach( $chk as $elem => $value ) 
				{
					if( 'new' == $value )
					{
						$key = aviaElementManager::USAGE_PREFIX . $elem;
						$entry = get_option( $key, array() );
						/**
						 * set to checked if used at least once and assume checked
						 */
						if( ! empty( $entry ) )
						{
							$chk[ $elem ] = 'checked';
							$update = true;
						}
					}
				}
			
				if( $update )
				{
					$this->update_checked_elements( $chk );
				}
			}
			
			$checked = array_values( array_unique( $chk ) );
			
			if( ( 1 == count( $checked ) ) && ( 'new' == $checked[0] ) )
			{
				$checked = array( 'only_new' );
			}
			else
			{
				$checked = array_diff( $checked, array( 'checked' ) );
			}
			
			return $checked;
		}

		/**
		 * Returns the array with infos about elements used for the current post
		 * Needs the function get_the_ID() to fill the array
		 * 
		 * @since 4.3
		 * @return array|false|null 
		 */
		public function get_current_post_elements()
		{
			if( is_null( $this->current_post_elements ) && ( false !== get_the_ID() ) )
			{
				$this->current_post_elements = $this->get_posts_detail_element_info( get_the_ID() );
			}
			
			return $this->current_post_elements;
		}
		
		
		/**
		 * Returns an array with all registered ALB elements as key and info true|false
		 * To speed up frontend we save infos in options/postmeta once we have the info.
		 * From backend on update post we force a writing to DB.
		 * 
		 * @since 4.3
		 * @param string $source			'post' | 'blog'
		 * @param int $post_id				defaults to the value of get_the_ID();
		 * @param string $initialise		'' | 'initialise'
		 * @return array|false
		 */
		public function get_elements_state( $source = 'post', $post_id = 0, $initialise = '' )
		{
			if( ( 'post' == $source ) && ( 0 == $post_id ) )
			{
				$post_id = get_the_ID();
				if( false === $post_id )
				{
					return false;
				}
			}
			
			$intersect = array_intersect( array( 'initialising', 'only_new' ), $this->all_elements_checked( $initialise ) );
			
			if( ! empty( $intersect ) )
			{
				if( 'initialise' != $initialise )
				{
					return false;
				}
			}
			
			$elements = $this->registered_elements();
			
			/**
			 * In frontend try to get cached values
			 */
			if( ! is_admin() )
			{
				if( 'post' == $source )
				{
					if( isset( $this->post_elements_state[ $post_id ] ) )
					{
						return $this->post_elements_state[ $post_id ];
					}
				
					/**
					 * To save memory we only saved used elements - we need to fill up the whole array
					 */
					$stored = get_post_meta( $post_id, '_av_alb_posts_elements_state', true );
					
					if( ! empty( $stored ) && is_array( $stored ) )
					{
						$all_elements = array_flip( $elements );
						foreach( $all_elements as $e => $value ) 
						{
							$all_elements[ $e ] = ( isset( $stored[ $e ] ) && ( true === $stored[ $e ] ) ) ? true : false;
						}
					
						$this->post_elements_state[ $post_id ] = $all_elements;
						return $all_elements;
					}
				}
			
				else 
				{
					if( ! empty( $this->blog_elements_state ) )
					{
						return $this->blog_elements_state;
					}

					$stored = get_option( 'av_alb_blog_elements_state', array() );
					if( ! empty( $stored ) )
					{
						$this->blog_elements_state = $stored;
						return $this->blog_elements_state;
					}
				}
			}
		
			$states = array();

			foreach ( $elements as $element )
			{
				$key = aviaElementManager::USAGE_PREFIX . $element;
				$entry = get_option( $key, array() );
				
				if( 'post' == $source )
				{
					if( ! isset( $entry[ $post_id ] ) )
					{
						$states[ $element ] = false;
					}
					else 
					{
						$states[ $element ] = ( $entry[ $post_id ]['count'] > 0 );
					}
					continue;
				}
				
				$states[ $element ] = false;
				
				if( ! empty( $entry ) )
				{
					foreach ( $entry as $key => $value ) 
					{
						if( $value['count'] > 0 )
						{
							$states[ $element ] = true;
							break;
						}
					}
				}
			}
			
			if( 'post' == $source )
			{
				/**
				 * To save storage we remove elements that are not used in the post
				 */
				$save_states = array();
				foreach( $states as $e => $used ) 
				{
					if( true === $used )
					{
						$save_states[ $e ] = true;
					}
				}
				
				update_post_meta( $post_id, '_av_alb_posts_elements_state', $save_states );
				$this->post_elements_state[ $post_id ] = $states;
			}
			else
			{
				update_option( 'av_alb_blog_elements_state', $states );
				$this->blog_elements_state = $states;
			}
			
			return $states;
		}

		/**
		 * 
		 * @since 4.3
		 * @param string $template
		 */
		public function handler_wp_get_header( $template = null )
		{
			if( is_null( $this->current_post_elements ) )
			{
				$this->current_post_elements = $this->get_posts_detail_element_info( get_the_ID() );
			}
			
			do_action( 'ava_current_post_element_info_available', $this );
		}
		
		
		/**
		 * After import of demos we need to force an update of elements settings
		 * 
		 * @since 4.3
		 */
		public function handler_after_import_demo()
		{
			update_option( 'av_alb_element_mgr', '' );
			update_option( 'av_alb_element_mgr_update', '' );
			
			$this->exec_version_update();
		}

		/**
		 * Check if we need to update our class internal data
		 * 
		 * @since 4.3
		 */
		public function handler_check_for_version_updates()
		{
			$version = get_option( 'av_alb_element_mgr', '' );
			$update_state = get_option( 'av_alb_element_mgr_update', '' );
			
			if( ( aviaElementManager::VERSION == $version ) && ( '' == $update_state ) )
			{
				return;
			}
			
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			{
				return;
			}

				//	allow user to login/logout
			if( is_admin() && ( ! is_user_logged_in() ) )
			{
				return;
			}

			/**
			 * Allow to filter function for a custom login page.
			 * Return true or false (boolean value) if you used a custom page, else any numeric value for standard WP login page.
			 * 
			 * @used_by:		currently unused
			 * 
			 * @since 4.3
			 */
			$result = apply_filters( 'avf_is_custom_admin_login_page', -1 );

			if( is_bool( $result ) )
			{
				return;
			}

			if( false !== stripos( $_SERVER["SCRIPT_NAME"], strrchr( wp_login_url(), '/') ) )
			{
				return;
			}
			
			$this->exec_version_update();
		}
		
		
		/**
		 * Simple update function.
		 * 
		 * As most user applications only have a few hundred pages/posts we do the update on the fly.
		 * For larger databases we add state flags that recognise unfinished post updates and skip already
		 * finished.
		 * 
		 * Might be extended to a background process in future releases.
		 * 
		 * IMPORTANT:
		 * ==========
		 * 
		 * It is possible to filter the predefined post types and status.
		 * If this is done you have to change the version number (by adding .a ) to force a rebuild of the internal index.
		 * We will always increment the version number using x.x only, so setting it to x.x.a is a save method to be
		 * able to follow future updates without any further intervention.
		 * 
		 * @since 4.3
		 */
		protected function exec_version_update()
		{
			
			$update_state = get_option( 'av_alb_element_mgr_update', '' );
			
			$query_args = array(
						'post_type'		=> apply_filters( 'avf_alb_supported_post_types_elmgr', Avia_Builder()->get_supported_post_types() ),
						'post_status'	=> apply_filters( 'avf_alb_supported_post_status_elmgr', Avia_Builder()->get_supported_post_status() ),
						'nopaging'		=> true,
						'fields'		=> 'ids'
					);
			
			if( '' != $update_state )
			{
				/**
				 * Filter not updated posts
				 */
				$query_args['meta_query'] = array(
						array(
								'key'     => '_av_alb_element_mgr_version',
								'value'   => aviaElementManager::VERSION,
								'compare' => '!=',
							),
					);
			}
						
			$query = new WP_Query( $query_args );
			
			if( '' == $update_state )
			{
				/**
				 * Reset all internal data
				 */
				$chk = $this->get_checked_elements();

				foreach( $chk as $el => $value ) 
				{
					$chk[ $el ] = 'initialising';
				}

				$this->update_checked_elements( $chk );
				
				$this->usage_info = array();
				$this->update_option_usage_info();
				
				$this->blog_elements_state = array();
				update_option( 'av_alb_blog_elements_state', $this->blog_elements_state );
				
				$this->post_elements_state = array();
			}
			
			update_option( 'av_alb_element_mgr_update', 'in_update' );

			
			foreach ( $query->posts as $post_id ) 
			{
				set_time_limit( 0 );
				
				/**
				 * Check if an update had been interrupted - skip already updated posts
				 */
				$updated = get_post_meta( $post_id, '_av_alb_element_mgr_version', true );
				if( ( $updated == aviaElementManager::VERSION ) && ( '' != $update_state ) )
				{
					continue;
				}
				
				update_post_meta( $post_id, '_av_alb_posts_elements_state', array() );
				
				$content = Avia_Builder()->get_post_content( $post_id );
				
				$content_new = $this->set_element_ids_in_content( $content, $post_id, 'no_escape' );
				aviaElementManager::$added_uid = 1;
				
				if( $content != $content_new )
				{
					Avia_Builder()->update_post_content( $post_id, $content_new );
				}
				
				$this->update_usage_from_post_content( $content_new, $post_id, 'initialise' );
				
				update_post_meta( $post_id, '_av_alb_element_mgr_version', aviaElementManager::VERSION );
				
				/**
				 * Reset no longer needed elements - free memory
				 */
				$this->post_elements_state = array();
			}
			
			unset( $query );
			
			$chk = $this->get_checked_elements();
			
			foreach ( $chk as $el => $value ) 
			{
				$chk[ $el ] = 'checked';
			}
			
			$this->update_checked_elements( $chk );
			
			$this->blog_elements_state = array();
			$this->get_elements_state( 'blog', 0, 'initialise' );
			
			update_option( 'av_alb_element_mgr', aviaElementManager::VERSION );		
			update_option( 'av_alb_element_mgr_update', '' );
		}

		
		/**
		 * Remove all element info for this post from options
		 * 
		 * @since 4.3
		 * @param int $post_id
		 */
		public function handler_wp_trashed_post( $post_id )
		{
			global $post;
			
			if( $post->ID != $post_id )
			{
				return;
			}
			
			/**
			 * Remove all index entries by rendering an empty content
			 */
			$this->updated_post_content( '', $post_id, 'trash' );
		}
		
		
		/**
		 * Restore all element info for this post from options
		 * 
		 * @since 4.4.1
		 * @param int $post_id
		 */
		public function handler_wp_untrash_post( $post_id )
		{
			global $post;
			
			if( $post->ID != $post_id )
			{
				return;
			}
			
			/**
			 * Restore all index entries by rendering content
			 */
			$this->updated_post_content( $post->post_content, $post_id, 'untrash' );
		}
		

		/**
		 * Returns an array of all used ALB shortcodes in given post:
		 * 
		 *		[$shortcode_name] =>  array(
		 *								'element'	=> $shortcode_name
		 *								'count		=> $nr_of_occurrence
		 *						)
		 * 
		 * @since 4.3
		 * @param int $post_id
		 * @return array|false			false, if we could not get a valid result for the post( = updateing the options is DB failed / not a valid post object )				
		 */
		public function get_posts_detail_element_info( $post_id )
		{
			$state = get_post_meta( $post_id, '_av_el_mgr_version', true );
			
			/**
			 * We access a non checked post or last check failed
			 * ( this is a fallback only under normal workflow )
			 */
			if( $state != aviaElementManager::USAGE_VERSION )
			{
				$content = Avia_Builder()->get_post_content( $post_id );
				
				if( false === $content )
				{
					return false;
				}
				
				if( false === $this->update_usage_from_post_content( $content, $post_id ) )
				{
					return false;
				}
			}
			
			$elements = $this->registered_elements();
			
			$used = array();
			
			foreach ( $elements as $element ) 
			{
				$key = aviaElementManager::USAGE_PREFIX . $element;
				$entry = get_option( $key, array() );
				
				if( ! isset( $entry[ $post_id ] ) )
				{
					continue;
				}
				
				$used[ $element ] = array(
										'element'	=> $element,
										'count'		=> $entry[ $post_id ]['count']
									);
			}
			
			return $used;
		}

		
		/**
		 * Called when a new post is added or content is updated. Checks if we have a post we need to deal with.
		 * 
		 * @since 4.4.1
		 * @param string $content
		 * @param int $post_id
		 * @param string $action				'update' | 'trash' | 'untrash'
		 * @return boolean
		 */
		public function updated_post_content( $content, $post_id, $action = 'update' )
		{
			global $post;
			
			/**
			 * See comment for function exec_version_update 
			 */
			$post_types = apply_filters( 'avf_alb_supported_post_types_elmgr', Avia_Builder()->get_supported_post_types() );
			$post_status = apply_filters( 'avf_alb_supported_post_status_elmgr', Avia_Builder()->get_supported_post_status() );
			
			if( ! in_array( $post->post_type, $post_types ) )
			{
				return;
			}
			
			$check_status = $post->post_status;
			
			switch( $action )
			{
				case 'trash':
					/**
					 * Post status is still original statut - WP does not update $post
					 */
					$content = '';
					break;
				case 'untrash':
					/**
					 * Post status is trash - WP does not update $post (not even on a later hook)
					 */
					$check_status = get_post_meta( $post_id, '_wp_trash_meta_status', true );
					break;
				case 'update':
				default:
					break;
			}
			
			if( ! in_array( $check_status, $post_status ) )
			{
				return;
			}
			
			return $this->update_usage_from_post_content( $content, $post_id );
		}

		/**
		 * Updates the usage info for ALB elements from the post content.
		 * Also sets a postmeta that we know we updated the usage info and with wich version it was updated
		 * 
		 * @since 4.3
		 * @param string $content
		 * @param int $post_id
		 * @param string $initialise			'' | 'initialise'
		 * @return boolean
		 */
		public function update_usage_from_post_content( $content, $post_id, $initialise = '' )
		{
			
			$this->usage_info = array();
			
			$matches = array();
				
			preg_match_all( "/" . ShortcodeHelper::get_fake_pattern( true, $this->registered_elements(), 'fake' ) . "/s", $content, $matches );
			if( is_array( $matches ) && is_array( $matches[0] ) && ( ! empty( $matches[0] ) ) )
			{
				$elements = explode( ',', str_replace( array( '[', ']' ), '', implode( ',', $matches[0] ) ) );
			}
			else
			{
				$elements = array();
			}

			$this->add_usage_info( $elements, $post_id );
			
			$success = $this->update_option_usage_info( $post_id );
			
			$pm = $success ? aviaElementManager::USAGE_VERSION : 'failed';
			update_post_meta( $post_id, '_av_el_mgr_version', $pm );
			
			/**
			 * Force update of cache data
			 */
			unset( $this->post_elements_state[ $post_id ] );
			$this->get_elements_state( 'post', $post_id, $initialise );
			
			if( 'initialise' != $initialise )
			{
				$this->blog_elements_state = array();
				$this->get_elements_state( 'blog' );
			}
			
			return $success;
		}
		
		
		/**
		 * Updates the array $this->usage_info
		 * 
		 * @since 4.3
		 * @param array $elements
		 */
		protected function add_usage_info( array $elements )
		{
			if( empty( $elements ) )
			{
				return;
			}
			
			/**
			 * If an element is marked as new and we find it in the post we assume that this is the first and only occurence and we can
			 * mark this element as checked
			 */
			$new = array();
			$found = array();
			$check = $this->all_elements_checked();
			
			if( ! empty( $check ) )
			{
				$new = array_intersect( $this->get_checked_elements(), array( 'new' ) );
			}
			
			foreach ( $elements as $element ) 
			{
				$element = trim( $element );

				/**
				 * Don't count closing tags
				 */
				if( strpos( $element, '/' ) !== false )
				{
					continue;
				}
				
				if( array_key_exists( $element, $new ) )
				{
					$found[] = $element;
					unset( $new[ $element ] );
				}
				
				if( array_key_exists( $element, $this->usage_info ) )
				{
					$this->usage_info[ $element ]['count']++;
				}
				else
				{
					$this->usage_info[ $element ] = array(
												'version'	=> aviaElementManager::USAGE_VERSION,
												'count'		=> 1
											);
				}
			}
			
			if( empty( $found ) )
			{
				return;
			}
			
			$chk = $this->get_checked_elements();
			
			foreach( $found as $el ) 
			{
				$chk[ $el ] = 'checked';
			}
			
			$this->update_checked_elements( $chk );
		}
		
		
		/**
		 * Update the element usage entries in options.
		 * Tries to update all options (also if an error occurs)
		 * 
		 * @since 4.3
		 * @param int $post_id			0 to clear all entries
		 * @return boolean				false, if the update failed and we have an inconsistent info
		 */
		protected function update_option_usage_info( $post_id = 0 )
		{
			$all_elements = array_flip( $this->registered_elements() );
			
			$success = true;
			
			foreach( $this->usage_info as $element => $info ) 
			{
				$key = aviaElementManager::USAGE_PREFIX . $element;
				$entry = get_option( $key, array() );
				
				/**
				 * As we also get false when values are not changed we have to check manually before update
				 */
				$need_update = true;
				if( isset( $entry[ $post_id ] ) )
				{
					$diff1 = array_diff_assoc( $entry[ $post_id ], $info );
					$diff2 = array_diff_assoc( $info, $entry[ $post_id ] );
					
					$need_update = ! ( empty( $diff1 ) && empty( $diff2 ) );
				}
				
				if( $need_update )
				{
					$entry[ $post_id ] = $info;
					if( ! update_option( $key, $entry ) )
					{
						$success = false;
					}
				}
				
				unset( $all_elements[ $element ] );
			}
			
			/**
			 * Remove entries that were removed
			 */
			foreach( $all_elements as $element => $dummy )
			{
				$key = aviaElementManager::USAGE_PREFIX . $element;
				$entry = get_option( $key, array() );
				
				if( array_key_exists( $post_id, $entry ) || ( 0 == $post_id ) )
				{
					if( 0 == $post_id )
					{
						$entry = array();
					}
					else 
					{
						unset( $entry[ $post_id ] );
					}
					
					if( ! update_option( $key, $entry ) )
					{
						$success = false;
					}
				}
			}
			
			return $success;
		}
		
		/**
		 * Scans all ALB shortcodes and checks, if they have a unique id.
		 * 
		 * @since 4.3
		 * @param string $content
		 * @param int $post_id
		 * @param string $escspe			'escape' | 'no_escape' 
		 * @return string
		 */
		public function set_element_ids_in_content( $content, $post_id, $escspe = 'escape' )
		{	
			$all_elements = $this->registered_elements();
			
			$elements = array();
			preg_match_all( "/" . get_shortcode_regex( $all_elements ) . "/s", $content, $elements, PREG_OFFSET_CAPTURE );
			if( empty( $elements ) || ! is_array( $elements ) || empty( $elements[0] ) )
			{
				return $content;
			}
			
			$count = count( $elements[0] );
			
			for( $i = $count - 1; $i >= 0; $i-- )
			{
				/**
				 * Check for nested shortcodes
				 */
				if( ! empty( $elements[5][ $i ][0] ) )
				{
					$new_content = $this->set_element_ids_in_content( $elements[5][ $i ][0], $post_id, $escspe );
					if( $elements[5][ $i ][0] != $new_content )
					{
						$content = substr_replace( $content, $new_content, $elements[5][ $i ][1], strlen( $elements[5][ $i ][0] ) );
					}
				}
				
				$atts = shortcode_parse_atts( stripslashes( $elements[3][ $i ][0] ) );
				if( ! is_array( $atts ) )
				{
					$atts = array();
				}
				
				if( array_key_exists( aviaElementManager::ELEMENT_UID, $atts ) && ( '' != trim( $atts[ aviaElementManager::ELEMENT_UID ] ) ) )
				{
					continue;
				}
				
				$atts[ aviaElementManager::ELEMENT_UID ] = 'av-' . base_convert( aviaElementManager::$added_uid . mt_rand( 10, 9999 ) . $post_id , 10, 36 );
				aviaElementManager::$added_uid ++;
				
				$new_atts = '';
				foreach( $atts as $att => $value) 
				{
					$new_atts .= ( is_numeric( $att ) ) ?  " {$value}" : " {$att}='{$value}'";
				}
				
				if( 'escape' == $escspe )
				{
					$new_atts = addslashes( $new_atts );
				}
				
				$content = substr_replace( $content, $new_atts, $elements[3][ $i ][1], strlen( $elements[3][ $i ][0]) );
			}
			
			return $content;
		}
		
		/**
		 * Add debug info to shortcode parser debug page
		 * 
		 * @since 4.3
		 * @return string
		 */
		public function debug_element_usage_info()
		{
			
			$blog = $this->get_elements_state( 'blog' );
			if( ! is_array( $blog )  )
			{
				$blog = __( 'Blog status of elements is currently not available', 'avia_framework' );
			}
			else
			{
				ksort( $blog );
				$blog = $this->esc_boolean( $blog );
			}
			
			$post = $this->get_elements_state( 'post' );
			if( ! is_array( $post )  )
			{
				$post = __( 'Post status of elements is currently not available', 'avia_framework' );
			}
			else
			{
				ksort( $post );
				
				foreach( $post as $element => $value ) 
				{
					if( false === $value )
					{
						unset( $post[ $element ] );
					}
				}
				
				$post = $this->esc_boolean( $post );
			}
			
			$chk = $this->get_checked_elements();
			
			$detail = array();
			
			$reg_elements = $this->registered_elements();
			sort( $reg_elements );
			
			foreach( $reg_elements as $element ) 
			{
				$key = aviaElementManager::USAGE_PREFIX . $element;
				
				$value = get_option( $key, array() );
				ksort( $value );
				$detail[ $element ] = $value;
			}
			
			$update_state = get_option( 'av_alb_element_mgr_update', '' );
			$update_state = ( '' != $update_state ) ? __( 'currently updating database', 'avia_framework' ) : __( 'is up to date', 'avia_framework' );
				
			$out = '';
			
			$out .=		"[av_tab title='" . __( 'Shortcode Usage Overview', 'avia_framework' ) . "' icon_select='yes' icon='ue823' font='entypo-fontello']";
			
			$out .=			"[av_toggle_container initial='0' mode='toggle' sort='true' styling='' colors='' font_color='' background_color='' border_color='' custom_class='']";
		
			$out .=				"[av_toggle title='" . __( 'General Element Manager Info', 'avia_framework' ) . "' tags='blog']";
			
			$out .=					'<p class="av-el-mgr-info av-version">' . __( 'Element Manager Version: ', 'avia_framework' ) . aviaElementManager::VERSION . '</p>';
			$out .=					'<p class="av-el-mgr-info av-update">' . __( 'Element Manager Update State: ', 'avia_framework' ) . $update_state . '</p>';
				
			$out .=				"[/av_toggle]";
			
			$out .=				"[av_toggle title='" . __( 'Blog Usage - which elements are used in the blog', 'avia_framework' ) . "' tags='blog']";
			
			$out .=					'<pre><code>';
			$out .=						$this->esc_shortcode( print_r( $blog, true ) );
			$out .=					'</code></pre>';
			
			$out .=				"[/av_toggle]";
			
			$out .=				"[av_toggle title='" . __( 'Post Usage - which elements are used in this post', 'avia_framework' ) . "' tags='post']";
			
			$out .=					'<pre><code>';
			$out .=						$this->esc_shortcode( print_r( $post, true ) );
			$out .=					'</code></pre>';
			
			$out .=				"[/av_toggle]";
			
			$out .=				"[av_toggle title='" . __( 'Check State - shows, which elements are recognised or new or in update', 'avia_framework' ) . "' tags='check']";
			
			$out .=					'<pre><code>';
			$out .=						$this->esc_shortcode( print_r( $chk, true ) );
			$out .=					'</code></pre>';
			
			$out .=				"[/av_toggle]";
			
			$out .=			"[/av_toggle_container]";
			
			$out .=		"[/av_tab]";
			
			
			$out .=		"[av_tab title='" . __( 'Detailed Shortcode Usage', 'avia_framework' ) . "' icon_select='yes' icon='ue826' font='entypo-fontello']";
			
			$out .=			"[av_toggle_container initial='0' mode='toggle' sort='true' styling='' colors='' font_color='' background_color='' border_color='' custom_class='']";
			
			foreach( $detail as $element => $usage ) 
			{
				$element_desc = $element . ' ( ' . count( $usage ) . ' )';
				$out .=				"[av_toggle title='{$element_desc}' tags='{$element_desc}']";
				
				$out .=					'<pre><code>';
				$out .=						$this->esc_shortcode( print_r( $usage, true ) );
				$out .=					'</code></pre>';
				
				$out .=				"[/av_toggle]";
			}
			
			$out .=			"[/av_toggle_container]";
			
			$out .=		"[/av_tab]";
			
			return $out;
		}
		
		/**
		 * Remove  [ and ]
		 * 
		 * @since 4.3
		 * @param string $text
		 * @return string
		 */
		private function esc_shortcode( $text )
		{
			return str_replace( array( '[', ']' ), '"', $text );
		}
		
		
		/**
		 * Replace a boolean value by string
		 * 
		 * @since 4.3
		 * @param array $values
		 * @return array
		 */
		private function esc_boolean( array $values )
		{
			foreach( $values as $key => $value ) 
			{
				if( is_bool( $value ) )
				{
					$values[ $key ] = $value ? 'true' : 'false';
				}
			}
			
			return $values;
		}
	
	}
	
}

