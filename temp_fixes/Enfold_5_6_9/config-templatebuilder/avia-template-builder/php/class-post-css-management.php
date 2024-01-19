<?php
use aviaBuilder\base\aviaCSSMediaQueries;

/**
 * This class provides methods for creation and managing CSS files that can be added to header of a page/post/... and contain CSS styling rules
 * for ALB shortcodes on this page. This avoids using inline style attributes.
 * Shortcodes have a unique id to identify the preporcessed.
 *
 * Files are created in wp_enqueue once on first page load with a low priority
 *
 * @author		Günter
 * @since 4.8.4
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'aviaPostCssManagement', false ) )
{

	class aviaPostCssManagement
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 4.8.4
		 * @var aviaPostCssManagement
		 */
		static private $_instance = null;

		/**
		 * Contains the subfolder where we put our css files. Is located in uploads folder.
		 *
		 * @since 4.8.4
		 * @var string
		 */
		protected $css_files_directory;

		/**
		 * Stores post ID
		 *
		 * @since 4.8.4
		 * @var int
		 */
		protected $post_id;

		/**
		 * Array of styling rules for a post - is reused in recursive function calls !!
		 *
		 *		'element_id' => (string) selector-rules
		 *
		 * @since 4.8.4
		 * @var array
		 */
		protected $styling_rules;

		/**
		 * Summarize and group media queries
		 *
		 * @since 4.8.8
		 * @var aviaCSSMediaQueries
		 */
		protected $media_rules;

		/**
		 * Element id's already written/used in CSS file or already added to content - is reused in recursive function calls !!
		 *
		 * @since 4.8.4
		 * @var array
		 */
		protected $processed_element_ids;

		/**
		 * @since 4.8.4
		 * @var string
		 */
		protected $new_ln;

		/**
		 *
		 * @since 4.3.1
		 * @var boolean
		 */
		protected $activate_cron;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.8.4
		 * @return aviaPostCssManagement
		 */
		static public function instance()
		{
			if( is_null( aviaPostCssManagement::$_instance ) )
			{
				aviaPostCssManagement::$_instance = new aviaPostCssManagement();
			}

			return aviaPostCssManagement::$_instance;
		}

		/**
		 * @since 4.8.4
		 */
		protected function __construct()
		{
			$this->css_files_directory = null;
			$this->post_id = 0;
			$this->styling_rules = array();
			$this->media_rules = new aviaCSSMediaQueries();
			$this->processed_element_ids = array();
			$this->new_ln = "\n";

			$this->activate_cron = ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON );

			/**
			 * Activate/Deactivate cron job to delete CSS files
			 *
			 * @since 4.8.4
			 * @param boolean $this->activate_cron
			 * @return boolean
			 */
			$this->activate_cron = apply_filters( 'avf_post_css_management_activate_cron', $this->activate_cron );

			/**
			 * WP Cron job events
			 */
			if( $this->activate_cron )
			{
				add_action( 'ava_cron_post_css_delete_files', array( $this, 'handler_cron_post_css_delete_files' ), 10 );
			}

			//	we have to use wp_enqueue_scripts to hook after when using file compression
			add_action( 'wp_enqueue_scripts', array( $this, 'handler_enqueue_post_styles' ), 999999 + 1 );

			add_action( 'trashed_post', array( $this, 'handler_wp_trashed_post' ), 10, 1 );
			add_action( 'save_post', array( $this, 'handler_wp_save_post' ), 10, 3 );

			//	Custom Element integration
			add_action( 'ava_ajax_cet_update_content', array( $this, 'handler_ava_reset_css_files' ), 10, 1 );
			add_action( 'ava_ajax_cet_delete', array( $this, 'handler_ava_reset_css_files' ), 10, 1 );

			//	reset postmeta and delete all css files
			add_action( 'ava_after_theme_update', array( $this, 'handler_ava_reset_css_files' ), 100, 1 );
			add_action( 'ava_after_import_demo_settings', array( $this, 'handler_ava_reset_css_files'), 100, 1 );
			add_action( 'avia_ajax_after_save_options_page', array( $this, 'handler_ava_reset_css_files'), 100, 1 );
		}

		/**
		 * @since 4.8.4
		 */
		public function __destruct()
		{
			unset( $this->styling_rules );
			unset( $this->media_rules );
			unset( $this->processed_element_ids );
		}

		/**
		 * Check if we need to enqueue a stylesheet
		 *
		 * @since 4.8.4
		 */
		public function handler_enqueue_post_styles()
		{
			global $post;

			if( ! $post instanceof WP_Post )
			{
				return;
			}

			$this->post_id = $post->ID;

			//	in this case we output the styles inline right in front of the shortcode as post_content does not have preview content and might create wrong styles
			if( is_preview() )
			{
				return;
			}

			/**
			 * some cache plugin render different CSS for logged in and not logged in users
			 * (e.g. https://wordpress.org/plugins/sg-cachepress/ reported https://kriesi.at/support/topic/color-section-disappeared-after-update/)
			 *
			 * @since 4.8.6.1
			 */
			if( avia_get_option( 'post_css_file_handling' ) == 'html_style_tag' )
			{
				return;
			}

			/**
			 * Filter to allow skipping of creating post css file for specific pages only.
			 * Return anything != true to skip.
			 *
			 * @since 4.8.6.1
			 * @param boolean $check_create
			 * @return boolean
			 */
			$check_create = apply_filters( 'avf_post_css_create_file', true );
			if( true !== $check_create )
			{
				return;
			}

			$this->check_create_file( $this->post_id );

			//	reset from possible recursive function calls creating css files (e.g. Page Content element)
			$this->processed_element_ids = array();

			$this->enqueue_files( $this->post_id );
		}

		/**
		 * @since 4.8.4
		 * @param type $post_id
		 * @return type
		 */
		protected function check_create_file( $post_id )
		{
			$meta = $this->get_meta( $post_id );

			if( in_array( $meta['status'], array( 'no_css', 'success' ) ) )
			{
				$include_ids = $meta['include_posts'];

				foreach( $include_ids as $include_id )
				{
					$this->check_create_file( $include_id );
				}

				return;
			}

			$processed_element_ids = $this->processed_element_ids;
			$styling_rules = $this->styling_rules;
			$media_rules = $this->media_rules;

			$this->processed_element_ids = array();
			$this->styling_rules = array();
			$this->media_rules = new aviaCSSMediaQueries();

			$include_ids = $this->include_post_ids_from_post( $post_id );

			foreach( $include_ids as $include_id )
			{
				$this->check_create_file( $include_id );
			}

			$css_rules = $this->get_css_rules_from_post( $post_id );

			if( empty( $css_rules ) )
			{
				$meta['status'] = 'no_css';
				$meta['processed_ids'] = array();
				$meta['include_posts'] = $include_ids;

				$this->update_meta( $meta, $post_id );
				return;
			}

			$meta['processed_ids'] = $this->processed_element_ids;
			$meta['include_posts'] = $include_ids;

			$this->create_file( $css_rules, $meta, $post_id );
			$this->update_meta( $meta, $post_id );

			$this->processed_element_ids = $processed_element_ids;
			$this->styling_rules = $styling_rules;
			$this->media_rules = $media_rules;
		}

		/**
		 * Recursive function - enqueue css file and all dynamic added posts css files
		 *
		 * @since 4.8.4
		 * @param int $post_id
		 * @return type
		 */
		protected function enqueue_files( $post_id )
		{
			$meta = $this->get_meta( $post_id );

			if( 'success' == $meta['status'] )
			{
				$this->processed_element_ids = array_merge( $this->processed_element_ids, $meta['processed_ids'] );
				$file_url = $this->css_file_url() . $meta['css_file'];

				wp_enqueue_style( 'avia-single-post-' . $meta['post_id'], $file_url, array(), $meta['timestamp'], 'all' );
			}

			if( isset( $meta['include_posts'] ) && is_array( $meta['include_posts'] ) )
			{
				$ids = array_unique( $meta['include_posts'] );
				foreach( $ids as $id )
				{
					$this->enqueue_files( $id );
				}
			}
		}

		/**
		 * Called when ALB modal popup preview window is updated. Returns the styles wrapped in <style> tag to be used inline.
		 *
		 * @since 4.8.4
		 * @param type $shortcode
		 * @param int $post_id
		 * @return string
		 */
		public function alb_preview_callback( $shortcode, $post_id )
		{
			if( empty( $shortcode ) )
			{
				return '';
			}

			$this->post_id = $post_id;

			$css_rules = $this->get_css_rules_from_content( $shortcode, $post_id, 'modal_preview' );

			$this->processed_element_ids = array_keys( $this->styling_rules );

			return $css_rules;
		}

		/**
		 * Called when ALB modal popup preview windows for svg dividers are updated.
		 * Returns a list for each divider window with the styles wrapped in <style> tag to be used inline and HTML content to display the preview
		 * depending on shortcode element.
		 *
		 * @since 4.8.4
		 * @param type $text
		 * @param int $post_id
		 * @param array $svg_list					$id => array ( id => ..., location => ..., html => ....  )
		 * @return array
		 */
		public function alb_preview_svg_dividers_callback( $text, $post_id, array $svg_list )
		{
			if( empty( $svg_list ) )
			{
				return array();
			}

			if( empty( $text ) )
			{
				return $svg_list;
			}

			//	get all shortcodes with attributes
			$shortcodes = ShortcodeHelper::shortcode2array( wp_unslash( $text ) );

			if( empty( $shortcodes ) )
			{
				return $svg_list;
			}

			$first_sc = array(
						'atts'			=> $shortcodes[0]['attr'],
						'content'		=> $shortcodes[0]['content'],
						'shortcodename'	=> $shortcodes[0]['shortcode'],
						'meta'			=> array()
				);

			$class = Avia_Builder()->get_shortcode_class( $first_sc['shortcodename'] );
			if( false === $class )
			{
				return $svg_list;
			}

			if( ! method_exists( $class, 'build_svg_divider_preview' ) )
			{
				return $svg_list;
			}

			return $class->build_svg_divider_preview( $first_sc, $svg_list );
		}

		/**
		 * Post has been saved - remove css files and reset meta data
		 * @since 4.8.4
   		 * @since 5.6.10		removed WP_Post parameter type - https://kriesi.at/support/topic/deprecated-error-class-avia_style_generator-nach-umzug/
		 * @param int $post_id
		 * @param WP_Post|null $post_saved
		 * @param boolean $update
		 */
		public function handler_wp_save_post( $post_id, $post_saved, $update )
		{
			global $post;

			//	skip revisions
			if( ! $post instanceof WP_Post )
			{
				return;
			}

			if( $post->ID != $post_id )
			{
				return;
			}

			if( Avia_Element_Templates()->get_post_type() == $post->post_type )
			{
				$this->handler_ava_ajax_cet_changed();
			}

			$this->remove_css_file( $post_id );
		}

		/**
		 * Post has been trashed - remove css files and reset meta data
		 *
		 * @since 4.8.4
		 * @param int $post_id
		 */
		public function handler_wp_trashed_post( $post_id )
		{
			global $post;

			//	skip revisions
			if( ! $post instanceof WP_Post )
			{
				return;
			}

			if( $post->ID != $post_id )
			{
				return;
			}

			$this->remove_css_file( $post_id );
		}

		/**
		 * Clears all post meta and all css files in folder
		 *
		 * @since 4.8.4
		 * @param array|false $options
		 */
		public function handler_ava_reset_css_files( $options = false )
		{
			$modified = $this->reset_all_meta();
			$this->delete_css_files( $modified );
		}

		/**
		 * Returns the filtered subdirectories below WP uploads directory that holds the css files for a post
		 *
		 * @since 4.8.4
		 * @since 5.3			moved to dynamic_avia/
		 * @return string
		 */
		public function css_files_directory()
		{
			global $avia_config;

			$dynamic = ltrim( $avia_config['dynamic_files_upload_folder'], ' /\\' );

			if( is_null( $this->css_files_directory ) )
			{
				$this->css_files_directory = apply_filters( 'avf_post_css_management_files_directory', trailingslashit( $dynamic ) . 'avia_posts_css' );
			}

			return $this->css_files_directory;
		}

		/**
		 * Return path to folder where uploaded files are stored - including /
		 *
		 * @since 4.8.4
		 * @return string
		 */
		public function css_file_path()
		{
			$wp_upload_dir = wp_upload_dir();

		    $dir = trailingslashit( trailingslashit( $wp_upload_dir['basedir'] ) . $this->css_files_directory() );
		    $dir = str_replace( '\\', '/', $dir );

			return $dir;
		}

		/**
		 * Return URL to folder where uploaded files are stored - including /
		 *
		 * @since 4.8.4
		 * @return string
		 */
		public function css_file_url()
		{
			$avia_upload_dir = wp_upload_dir();
			if( is_ssl() )
			{
				$avia_upload_dir['baseurl'] = str_replace( 'http://', 'https://', $avia_upload_dir['baseurl'] );
			}

			$url = trailingslashit( trailingslashit( $avia_upload_dir['baseurl'] ) . $this->css_files_directory() );

			return $url;
		}

		/**
		 * Returns an initialized metadata array of the CSS file for the post
		 *
		 * @since 4.8.4
		 * @param int $post_id
		 * @return array
		 */
		protected function get_meta( $post_id )
		{
			$meta = get_post_meta( $post_id, '_av_css_styles', true );
			if( ! is_array( $meta ) )
			{
				$meta = array();
			}

			$default = array(
						'post_id'		=> $post_id,
						'css_file'		=> 'post-' . $post_id . '.css',
						'timestamp'		=> '',
						'status'		=> '',
						'processed_ids'	=> array(),				//	element id's that are included in css styles
						'include_posts'	=> array()				//	post id's that need to be included in enqueue
				);

			return array_merge( $default, $meta );
		}

		/**
		 * Update the metadata for the CSS file for the post
		 *
		 * @since 4.8.4
		 * @param array $meta
		 * @param int $post_id
		 */
		protected function update_meta( array $meta, $post_id )
		{
			update_post_meta( $post_id, '_av_css_styles', $meta );
		}

		/**
		 * Removes all meta data by updateing with an empty array
		 *
		 * @since 4.8.4
		 * @return int|false
		 */
		protected function reset_all_meta()
		{
			global $wpdb;

			$empty = serialize( array() );
			$sql = "UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key='_av_css_styles'";

			return $wpdb->query( $wpdb->prepare( $sql, $empty ) );
		}

		/**
		 * Deletes all CSS files. Make sure to call reset_all_meta() before to invalidate and force a rebuild.
		 * For large sites a filter can be used to disable delete
		 *
		 * @since 4.8.4
		 * @param int $meta_modified
		 * @return boolean
		 */
		protected function delete_css_files( $meta_modified )
		{
			if( $this->activate_cron )
			{
				if( $this->init_cron_delete() )
				{
					return false;
				}
			}

			$pattern = $this->css_file_path() . 'post-*.css';
			$files = glob( $pattern, GLOB_NOSORT );
			$found = count( $files );

			if( 0 === $found )
			{
				return true;
			}

			foreach( $files as $file )
			{
				unlink( $file );
			}

			return true;
		}

		/**
		 * Try to init a cron job for deleting files
		 *
		 * @since 4.8.4
		 * @return boolean
		 */
		protected function init_cron_delete()
		{
			if( ! $this->activate_cron )
			{
				return false;
			}

			$started = wp_schedule_single_event( time(), 'ava_cron_post_css_delete_files' );

			return ( true === $started ) ? $started : false;
		}

		/**
		 * Remove all CSS files where meta has no valid timestamp
		 *
		 * @since 4.8.4
		 */
		public function handler_cron_post_css_delete_files()
		{
			if( current_theme_supports( 'avia_log_cron_job_messages' ) )
			{
				error_log( '******************  In aviaPostCssManagement::handler_cron_post_css_delete_files started' );
			}

			$pattern = $this->css_file_path() . 'post-*.css';
			$files = glob( $pattern, GLOB_NOSORT );
			$found = count( $files );

			if( 0 === $found )
			{
				if( current_theme_supports( 'avia_log_cron_job_messages' ) )
				{
					error_log( '******************  In aviaPostCssManagement::handler_cron_post_css_delete_files ended - no files found' );
				}

				return;
			}

			//	allow to run unlimited
			set_time_limit( 0 );

			foreach( $files as $file )
			{
				$match = array();
				preg_match( '/post-([0-9]*).css/', $file, $match );

				$post_id = isset( $match[1] ) ? $match[1] : '';

				if( ! is_numeric( $post_id ) )
				{
					if( current_theme_supports( 'avia_log_cron_job_messages' ) )
					{
						error_log( '*******    Skipped wrong filename: ' . $file );
					}
					continue;
				}

				$meta = $this->get_meta( $post_id );

				if( empty( $meta['timestamp'] ) )
				{
					$deleted = unlink( $file );
					if( current_theme_supports( 'avia_log_cron_job_messages' ) )
					{
						if( $deleted )
						{
							error_log( 'Deleted: ' . $file );
						}
						else
						{
							error_log( '*******    Could not delete: ' . $file );
						}
					}
				}
				else
				{
					if( current_theme_supports( 'avia_log_cron_job_messages' ) )
					{
						error_log( 'Skipped - already up to date again: ' . $file );
					}
				}
			}

			if( current_theme_supports( 'avia_log_cron_job_messages' ) )
			{
				error_log( '******************  In aviaPostCssManagement::handler_cron_post_css_delete_files ended. Deleted: ' . $found );
			}
		}

		/**
		 * Remove CSS files and info for post
		 *
		 * @since 4.8.4
		 * @param int $post_id
		 */
		protected function remove_css_file( $post_id )
		{
			$meta = $this->get_meta( $post_id );

			if( isset( $meta['css_file'] ) && ! empty( $meta['css_file'] ) )
			{
				$file = $this->css_file_path() . $meta['css_file'];
				if( file_exists( $file ) )
				{
					unlink( $file );
				}
			}

			if( isset( $meta['preview_file'] ) && ! empty( $meta['preview_file'] ) )
			{
				$file = $this->css_file_path() . $meta['preview_file'];
				if( file_exists( $file ) )
				{
					unlink( $file );
				}
			}

			$meta = array();
			$this->update_meta( $meta, $post_id );
		}

		/**
		 * Gets the post content
		 *
		 * @since 4.8.4
		 * @param int $post_id
		 * @return string
		 */
		protected function post_content( $post_id )
		{
			if( is_preview() )
			{
				$post = get_post( $post_id );
				$content = $post instanceof WP_Post ? $post->post_content : '';
			}
			else
			{
				$content = Avia_Builder()->get_post_content( $post_id );
			}

			if( false === $content || empty( $content ) )
			{
				return '';
			}

			return $content;
		}

		/**
		 * Checks post content for shortcodes that dynamically add post content
		 *
		 * @since 4.8.4
		 * @param int $post_id
		 * @return array
		 */
		protected function include_post_ids_from_post( $post_id )
		{
			$content = $this->post_content( $post_id );

			if( empty( $content ) )
			{
				return array();
			}

			//	get all shortcodes with attributes
			$shortcodes = ShortcodeHelper::shortcode2array( wp_unslash( $content ) );

			if( empty( $shortcodes ) )
			{
				return array();
			}

			return $this->include_post_ids_from_shortcodes( $shortcodes );
		}

		/**
		 * @since 4.8.4
		 * @param array $shortcodes
		 * @return array
		 */
		protected function include_post_ids_from_shortcodes( array $shortcodes )
		{
			$post_ids = array();

			foreach( $shortcodes as $shortcode )
			{
				$item = false;
				$sc = null;

				if( isset( Avia_Builder()->shortcode[ $shortcode['shortcode'] ] ) )
				{
					$sc = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $shortcode['shortcode'] ] ];
					$ids = $sc->has_global_attributes();
				}
				else
				{
					if( isset( Avia_Builder()->shortcode_parents[ $shortcode['shortcode'] ] ) && ! empty( Avia_Builder()->shortcode_parents[ $shortcode['shortcode'] ] ) )
					{
						$parent = Avia_Builder()->shortcode_parents[ $shortcode['shortcode'] ][0];
						$sc = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $parent ] ];
						$item = true;
					}
				}

				if( $sc instanceof aviaShortcodeTemplate && method_exists( $sc, 'includes_dynamic_posts' ) )
				{
					$ids = $sc->includes_dynamic_posts( $shortcode, $item );
					$post_ids = array_merge( $post_ids, $ids );
				}

				if( is_array( $shortcode['content'] ) )
				{
					$ids = $this->include_post_ids_from_shortcodes( $shortcode['content'] );
					$post_ids = array_merge( $post_ids, $ids );
				}

			}

			return array_unique( $post_ids );
		}



		/**
		 * Get post content and generate CSS rules.
		 * As our shortcodes are also used in posts of all post types we have to take care of all post content
		 *
		 * @since 4.8.4
		 * @param int $post_id
		 * @return string
		 */
		protected function get_css_rules_from_post( $post_id )
		{
			$content = $this->post_content( $post_id );

			if( empty( $content ) )
			{
				return '';
			}

			$result = is_preview() ? 'modal_preview' : 'file';

			$this->media_rules->reset_rules();

			$css_rules = $this->get_css_rules_from_content( $content, $post_id, $result );


			$this->processed_element_ids = array_keys( $this->styling_rules );

			$media_ids = $this->media_rules->get_processed_element_ids();
			if( ! empty( $media_ids ) )
			{
				$this->processed_element_ids = array_unique( array_merge( $this->processed_element_ids, $media_ids ) );
			}

			return $css_rules;
		}

		/**
		 * Checks if styles for a shortcode element has been processed (either in CSS or direct output in header)
		 *
		 * @since 4.8.4
		 * @param string $element_id
		 * @return boolean
		 */
		public function shortcode_styles_processed( $element_id )
		{
			return in_array( $element_id, $this->processed_element_ids );
		}

		/**
		 * Generate CSS rules for a content
		 *
		 * @since 4.8.4
		 * @param string $content
		 * @param int $post_id
		 * @param string $destination				'file' | 'modal_preview'
		 * @return string
		 */
		public function get_css_rules_from_content( $content, $post_id, $destination = 'file' )
		{
			//	get all shortcodes with attributes
			$shortcodes = ShortcodeHelper::shortcode2array( wp_unslash( $content ) );

			if( empty( $shortcodes ) )
			{
				return '';
			}

			$this->css_rules_from_shortcodes( $shortcodes, $destination );

			$css = $this->get_content_style( $destination );
			$css .= $this->media_rules->get_rules();

			return $css;
		}

		/**
		 * Returns the style for the parsed content
		 *
		 * @since 4.8.4
		 * @param string $destination
		 * @return string
		 */
		protected function get_content_style( $destination = 'file' )
		{
			if( empty( $this->styling_rules ) )
			{
				return '';
			}

			$rules = implode( $this->new_ln, array_filter( $this->styling_rules ) );

			if( empty( $rules ) )
			{
				return '';
			}

			if( 'file' == $destination )
			{
				return $rules . $this->new_ln;
			}

			return '<style type="text/css">' . $this->new_ln . $rules . '</style>' . $this->new_ln;
		}

		/**
		 * Recursive function that loops over all shortcodes of content
		 *
		 * @since 4.8.4
		 * @param array $shortcodes
		 * @param string $destination			'file' | 'modal_preview'
		 */
		protected function css_rules_from_shortcodes( array $shortcodes, $destination = 'file' )
		{
			foreach( $shortcodes as $shortcode )
			{
				$item = false;
				$global_settings = false;
				$result = array();

				$sc = Avia_Builder()->get_shortcode_class( $shortcode['shortcode'] );

				if( $sc instanceof aviaShortcodeTemplate )
				{
					$global_settings = $sc->has_global_attributes();
				}
				else
				{
					$sc = Avia_Builder()->get_parent_shortcode_class( $shortcode['shortcode'] );
					$item = $sc instanceof aviaShortcodeTemplate;
				}

				if( $sc instanceof aviaShortcodeTemplate )
				{
					$result = $sc->create_header_styles( $shortcode, $item );
				}

				if( ! empty( $result ) )
				{
					$rules = $result['element_styling']->get_style_rules( $destination, $this->media_rules );
					if( ! empty( $rules ) )
					{
						if( ! isset( $this->styling_rules[ $result['element_id'] ] ) )
						{
							$this->styling_rules[ $result['element_id'] ] = $rules;
						}
						else
						{
							$this->styling_rules[ $result['element_id'] ] .= $this->new_ln . $rules;
						}
					}
				}

				if( is_array( $shortcode['content'] ) )
				{
					//	content might be changed by CET -> need to rebuild
					$shortcode['content'] = ShortcodeHelper::shortcode2array( wp_unslash( $result['content'] ) );

					if( $global_settings )
					{
						//	add current shortcode attributes in case they are needed in subitems (e.g. button row)
						foreach( $shortcode['content'] as &$sub_sc )
						{
							$sub_sc['attr']['parent_atts'] = $shortcode['attr'];
						}

						unset( $sub_sc );
					}

					$this->css_rules_from_shortcodes( $shortcode['content'] );
				}
			}
		}

		/**
		 * Creates the CSS file
		 *
		 * @since 4.8.4
		 * @param string $css_rules
		 * @param array $meta
		 * @return boolean
		 */
		protected function create_file( $css_rules, array &$meta )
		{
			//try to create a new folder if necessary
			$css_dir = $this->css_file_path();
			$isdir = avia_backend_create_folder( $css_dir );

			//check if we got a folder (either created one or there already was one)
			if( ! $isdir )
			{
				$meta['status'] = 'no_css';
				return false;
			}

			$minify = true;

			if( defined( 'WP_DEBUG' ) && WP_DEBUG === true )
			{
				$minify = false;
			}
			else
			{
				$opt_merge = avia_get_option( 'merge_css', 'avia' );
				$minify = ! in_array( $opt_merge, array( 'none' ) );
			}

			/**
			 * Allows to supress in production environments
			 *
			 * @since 5.3
			 * @param boolean $css_strip_whitespace
			 * @param string $context
			 * @param boolean
			 */
			if( true !== apply_filters( 'avf_css_strip_whitespace_dynamic_files', $minify, __CLASS__ ) )
			{
				$minify = false;
			}

			if( true === $minify )
			{
				$css_rules = aviaAssetManager::css_strip_whitespace( $css_rules );
			}

			$file = $css_dir . $meta['css_file'];
			$file_created = avia_backend_create_file( $file, $css_rules );

			//	to speed up we do not double check readability
			if( ! is_preview() )
			{
				$meta['status'] = $file_created ? 'success' : 'error';
			}

			$meta['timestamp'] = 'ver-' . time();

			return $file_created;
		}
	}

	/**
	 * Returns the main instance of aviaPostCssManagement to prevent the need to use globals
	 *
	 * @since 4.8.4
	 * @return aviaPostCssManagement
	 */
	function AviaPostCss()
	{
		return aviaPostCssManagement::instance();
	}

	/**
	 * Activate filter and action hooks
	 */
	AviaPostCss();

}
