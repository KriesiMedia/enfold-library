<?php
/**
 * @since 6.0
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'aviaDynamicContent', false ) )
{
	class aviaDynamicContent
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 6.0
		 * @var aviaDynamicContent
		 */
		static private $_instance = null;

		/**
		 * Flag if this feature has been enabled
		 *
		 * @since 6.0
		 * @var boolean|null
		 */
		protected $enabled;

		/**
		 * Array of field groups (human readable text)
		 *
		 *			'short_name'	=> 'readable'
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $groups;

		/**
		 * Grouped array of fields
		 *
		 *			'group'	=> [   'name'  =>  'key'   ]
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $fields;

		/**
		 * Array of data formats
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $data_formats;

		/**
		 * Array of predefined "shortcodes" in { .... } for $fields keys
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $shortcodes;

		/**
		 * Cached custom fields to avoid multiple queries
		 *
		 * @since 6.0
		 * @var array|null
		 */
		protected $custom_fields_cache;

		/**
		 * @since 6.0
		 * @var string
		 */
		protected $modal_link_message;

		/**
		 * Post to read data from
		 * On ALB modal preview in CPT it is set to a dummy post selected by user
		 *
		 * @since 6.0
		 * @var WP_Post|null
		 */
		protected $source_post;

		/**
		 * Cache for posts to read data from (for WPML,... $post is translated post)
		 *
		 *		Post_ID => $post
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $source_post_cache;

		/**
		 * Return the instance of this class
		 *
		 * @since 6.0
		 * @return aviaDynamicContent
		 */
		static public function instance()
		{
			if( is_null( aviaDynamicContent::$_instance ) )
			{
				aviaDynamicContent::$_instance = new aviaDynamicContent();
			}

			return aviaDynamicContent::$_instance;
		}

		/**
		 *
		 * @since 6.0
		 */
		protected function __construct()
		{
			$this->enabled = null;
			$this->groups = null;
			$this->fields = null;
			$this->data_formats = null;
			$this->shortcodes = null;
			$this->source_post = null;
			$this->source_post_cache = [];
			$this->custom_fields_cache = null;

			$this->modal_link_message = '<br /><strong>' . __( 'Link settings will be ignored when dynamic content contains a link to avoid breaking of layout.', 'avia_framework' ) . '</strong>';

			if( ! $this->dynamic_content_enabled() )
			{
				return;
			}

			$this->register_dynamic_data_sources();
			$this->activate_filters();
		}

		/**
		 *
		 * @since 6.0
		 */
		public function __destruct()
		{
			unset( $this->groups );
			unset( $this->fields );
			unset( $this->data_formats );
			unset( $this->shortcodes );
			unset( $this->custom_fields_cache );
			unset( $this->source_post );
			unset( $this->source_post_cache );
		}

		/**
		 * Fills the datastructures
		 *
		 * @since 6.0
		 */
		protected function register_dynamic_data_sources()
		{
			$this->groups = [
							'wp_post'			=> __( 'WP Post Data', 'avia_framework' ),
							'wp_post_advanced'	=> __( 'WP Post Advanced', 'avia_framework' ),
							'wp_custom_field'	=> __( 'WP Custom Fields', 'avia_framework' )
						];

			$post = [
					'wp_post_ID'			=> __( 'ID', 'avia_framework' ),
					'wp_post_title'			=> __( 'Title', 'avia_framework' ),
					'wp_post_excerpt'		=> __( 'Excerpt', 'avia_framework' ),
					'wp_post_date'			=> __( 'Publish Date', 'avia_framework' ),
					'wp_post_date_time'		=> __( 'Publish Date And Time', 'avia_framework' ),
					'wp_post_modified'		=> __( 'Modified Date', 'avia_framework' ),
					'wp_post_modified_time'	=> __( 'Modified Date And Time', 'avia_framework' ),
					'wp_post_author'		=> __( 'Author Link', 'avia_framework' ),
					'wp_post_author_name'	=> __( 'Author Name', 'avia_framework' ),
					'wp_post_comment_count'	=> __( 'Comment Count', 'avia_framework' ),
					'wp_post_revisions'		=> __( 'Revisions Count', 'avia_framework' )
				];

			$post_advanced = [
					'wp_post_type'				=> __( 'Post Type Link (= Archive)', 'avia_framework' ),
					'wp_post_categories'		=> __( 'Category/Term Links', 'avia_framework' ),
					'wp_post_categories_name'	=> __( 'Category/Term Names', 'avia_framework' ),
					'wp_post_tags'				=> __( 'Tag Links', 'avia_framework' ),
					'wp_post_tags_name'			=> __( 'Tag Names', 'avia_framework' ),
				];

			$this->fields = [
						'wp_post'			=> $post,
						'wp_post_advanced'	=> $post_advanced,
						'wp_custom_field'	=> $this->get_default_custom_fields()
					];

			$this->data_formats = [
							''				=> __( 'Unformatted text', 'avia_framework' ),
							'no_html'		=> __( 'Text (HTML escaped)', 'avia_framework' ),
							'autoformat'	=> __( 'Text (autoformat)', 'avia_framework' ),
							'integer'		=> __( 'Integer number', 'avia_framework' ),
							'float'			=> __( 'Floating point number', 'avia_framework' ),
							'date'			=> __( 'Date', 'avia_framework' ),
							'date_time'		=> __( 'Date and time', 'avia_framework' ),
							'link'			=> __( 'Link', 'avia_framework' )
						];

			/**
			 * To add a special custom field shortcode styling via filter 'avf_register_dynamic_data_sources' (see function create_sc_data_string()):
			 *
			 *				'your custom field name'	=> '{av_dynamic_el src="wp_custom_field" key="your custom field name" default="" link="" linktext="" format=""}',
			 */

			$this->shortcodes = [
							'wp_custom_field'			=> '{av_dynamic_el src="wp_custom_field" key="%metakey%" default="" link="" linktext="" format=""}',
							'wp_post_ID'				=> '{av_dynamic_el src="wp_post_ID"}',
							'wp_post_title'				=> '{av_dynamic_el src="wp_post_title"}',
							'wp_post_excerpt'			=> '{av_dynamic_el src="wp_post_excerpt"}',
							'wp_post_date'				=> '{av_dynamic_el src="wp_post_date"}',
							'wp_post_date_time'			=> '{av_dynamic_el src="wp_post_date" format="date_time"}',
							'wp_post_modified'			=> '{av_dynamic_el src="wp_post_modified"}',
							'wp_post_modified_time'		=> '{av_dynamic_el src="wp_post_modified" format="date_time"}',
							'wp_post_author'			=> '{av_dynamic_el src="wp_post_author"}',
							'wp_post_author_name'		=> '{av_dynamic_el src="wp_post_author" link="no_link"}',
							'wp_post_comment_count'		=> '{av_dynamic_el src="wp_post_comment_count"}',
							'wp_post_revisions'			=> '{av_dynamic_el src="wp_post_revisions"}',
							'wp_post_type'				=> '{av_dynamic_el src="wp_post_type"}',
							'wp_post_categories'		=> '{av_dynamic_el src="wp_post_categories"}',
							'wp_post_categories_name'	=> '{av_dynamic_el src="wp_post_categories" link="no_link"}',
							'wp_post_tags'				=> '{av_dynamic_el src="wp_post_tags"}',
							'wp_post_tags_name'			=> '{av_dynamic_el src="wp_post_tags" link="no_link"}'
						];

			/**
			 * Allows to add more groups to support 3rd party plugins
			 *
			 * @since 6.0
			 * @param array $this->groups
			 * @param string $context					'groups' | 'fields' | 'data_formats' | 'shortcodes'
			 * @return array
			 */
			$this->groups = (array) apply_filters( 'avf_register_dynamic_data_sources', $this->groups, 'groups' );

			/**
			 * Allows to add more fields to support 3rd party plugins
			 *
			 * @since 6.0
			 * @param array $this->groups
			 * @param string $context					'groups' | 'fields' | 'data_formats' | 'shortcodes'
			 * @return array
			 */
			$this->fields = (array) apply_filters( 'avf_register_dynamic_data_sources', $this->fields, 'fields' );

			/**
			 * Allows to add more fields to support 3rd party plugins
			 *
			 * @since 6.0
			 * @param array $this->data_formats
			 * @param string $context					'groups' | 'fields' | 'data_formats' | 'shortcodes'
			 * @return array
			 */
			$this->data_formats = apply_filters( 'avf_register_dynamic_data_sources', $this->data_formats, 'data_formats' );

			/**
			 * Allows to add more fields to support 3rd party plugins
			 *
			 * @since 6.0
			 * @param array $this->shortcodes
			 * @param string $context					'groups' | 'fields' | 'data_formats' | 'shortcodes'
			 * @return array
			 */
			$this->shortcodes = apply_filters( 'avf_register_dynamic_data_sources', $this->shortcodes, 'shortcodes' );
		}

		/**
		 * Attach to filters
		 *
		 * @since 6.0
		 */
		protected function activate_filters()
		{
			add_filter( 'admin_body_class', array( $this, 'handler_admin_body_class' ) );

			add_filter( 'avf_alb_magic_wand_button', [ $this, 'handler_avf_alb_magic_wand_button' ], 10, 2 );
			add_filter( 'the_content', [ $this, 'handler_wp_the_content' ], 1, 1 );

			add_shortcode( 'av_dynamic_el', [ $this, 'sc_handler_av_dynamic_el' ] );
		}

		/**
		 * Add extra classes
		 *
		 * @since 6.0
		 * @param string $classes
		 * @return string
		 */
		public function handler_admin_body_class( $classes )
		{
			if( ! $this->dynamic_content_enabled() )
			{
				$classes .= ' avia-dynamic-content-disabled';
			}
			else
			{
				$classes .= ' avia-dynamic-content-enabled';
			}

			return $classes;
		}

		/**
		 * Add dynamic shortcodes to tinyMCE menu
		 *
		 * @since 6.0
		 * @param array $tiny
		 * @param array $shortcode_class
		 * @return array
		 */
		public function handler_avf_alb_magic_wand_button( array $tiny, array $shortcode_class )
		{
			if( ! $this->dynamic_content_enabled() )
			{
				return $tiny;
			}

			foreach( $this->groups as $group_key => $value )
			{
				if( is_array( $this->fields[ $group_key ] ) && ! empty( $this->fields[ $group_key ] ) )
				{
					/**
					 * Filter select list dropdown. Does not work - called too early - $this->source_post is null
					 *
					 * @used_by				avia_ACF::handler_avf_dynamic_filter_select_list		10
					 * since 6.0
					 * @param array $this->fields
					 * @param string $group_key
					 * @param WP_Post|null $this->source_post
					 * @param array $element
					 * @return array
					 */
					$fields_filtered = apply_filters( 'avf_dynamic_filter_select_list', $this->fields[ $group_key ], $group_key, $this->source_post, [] );

					$tab = __( 'Dynamic', 'avia_framework' ) . ' ' . $value;

					foreach( $fields_filtered as $field_key => $field_value )
					{
						$insert = $this->create_sc_data_string( $group_key, $field_key, 'pseudo_code' );

						$config = [
								'php_class'		=> "av_dynamic_{$field_key}",
								'tab'			=> $tab,
								'name'			=> $field_value,
								'shortcode'		=> 'av_dynamic_el',
								'inline'		=> true,
								'html_renderer'	=> false,
								'tinyMCE'		=> [
													'tiny_always'	=> true,
													'instantInsert'	=> $insert
												]
							];

						$tiny['shortcodes']["av_dynamic_{$field_key}"] = $config;
					}
				}
			}

			return $tiny;
		}

		/**
		 *
		 * @since 6.0
		 * @param string $content
		 * @return string
		 */
		public function handler_wp_the_content( $content )
		{
			global $post;

			if( ! $this->dynamic_content_enabled() || ! $post instanceof WP_Post )
			{
				return $content;
			}

			if( Avia_Builder()->get_alb_builder_status( $post->ID ) == 'active' )
			{
				return $content;
			}

			$new_content = $this->replace_pseudo_shortcode( $content, 'auto' );

			return $new_content;
		}

		/**
		 * Shortcode handler
		 *
		 * @since 6.0
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function sc_handler_av_dynamic_el( $atts, $content = '', $shortcodename = '' )
		{
			if( ! $this->dynamic_content_enabled() )
			{
				return '';
			}

			$default = [
					'src'		=> '',
					'key'		=> '',			// custom field meta key
					'default'	=> '',
					'link'		=> '',			// '' | 'blank' | 'no_link'
					'linktext'	=> '',			//	Text to display inside <a> tags
					'format'	=> ''			//	'' | 'date' | 'date_time' | 'integer' | 'float' | 'no_html'
				];

			$atts = shortcode_atts( $default, $atts, $shortcodename );

			$this->set_source_post();

			if( ! $this->source_post instanceof WP_Post )
			{
				return is_admin() || wp_doing_ajax() ? $atts['src'] : $atts['default'];
			}

			if( ! is_admin() && ! wp_doing_ajax() )
			{
				foreach( $atts as &$value )
				{
					if( 0 === strpos( $value, '&#8221;' ) )
					{
						$value = str_replace( '&#8221;', '', $value );
					}
				}

				unset( $value );
			}

			$html = '';

			if( 0 === strpos( $atts['src'], 'wp_post_' ) )
			{
				$html .= $this->read_wp_post_data( $atts );
			}
			else if( 0 === strpos( $atts['src'], 'wp_custom_field' ) )
			{
				$html .= $this->read_wp_custom_field( $atts );
			}
			else
			{
				/**
				 * apply filter for 3-rd party integration
				 *
				 * @since 6.0
				 * @used_by				avia_ACF::handler_avf_sc_av_dynamic_el		10
				 * @param string $html
				 * @param array $atts
				 * @param string $content
				 * @param string $shortcodename
				 * @param WP_Post $this->source_post
				 * @param aviaDynamicContent $this
				 * @return string
				 */
				$html .= apply_filters( 'avf_sc_av_dynamic_el', $html, $atts, $content, $shortcodename, $this->source_post, $this );
			}

			return $html;
		}

		/**
		 * Returns, if the feature has been enabled in theme options
		 *
		 * @since 6.0
		 * @return boolean
		 */
		public function dynamic_content_enabled()
		{
			if( is_null( $this->enabled ) )
			{
				$enabled = false !== strpos( avia_get_option( 'alb_dynamic_content' ), 'alb_dynamic_content' );

				/**
				 * @used_by			might be avia_WPML ?????
				 * @since 6.0
				 * @param boolean
				 * @return boolean
				 */
				$this->enabled = apply_filters( 'avf_dynamic_content_enabled', $enabled );
			}

			return $this->enabled;
		}

		/**
		 * Scans the shortcode attributes and fills the dynamic data fields
		 *
		 * @since 6.0
		 * @param array $atts
		 * @param aviaShortcodeTemplate $sc_templ
		 * @param string $shortcode
		 * @param string $content
		 * @param int|null $post_id
		 */
		public function read( array &$atts, aviaShortcodeTemplate $sc_templ, $shortcode, &$content, $post_id = null )
		{
			if( ! $this->dynamic_content_enabled() )
			{
				return;
			}

			$this->set_source_post( $post_id );

			$elements = null;
			$is_modal = false;
			$format_original = isset( $atts['format'] ) ? $atts['format'] : 'undefined';
			$format = isset( $atts['format'] ) ? $atts['format'] : '';

			if( $sc_templ->config['shortcode'] != $shortcode )
			{
				foreach( $sc_templ->elements as $index => $element )
				{
					if( isset( $element['type'] ) && 'modal_group' == $element['type'] && is_array( $element['subelements'] ) )
					{
						$elements = $element['subelements'];
						$is_modal = true;
						break;
					}
				}
			}

			if( is_null( $elements ) )
			{
				$elements = $sc_templ->elements;
			}

			foreach( $elements as $index => $element )
			{
				if( empty( $element['id'] ) )
				{
					continue;
				}

				if( ! isset( $element['dynamic'] ) || ! is_array( $element['dynamic'] ) )
				{
					continue;
				}

				$format_current = $format;

				$value = '';
				if( 'content' == $element['id'] )
				{
					$value = $content;

					if( 'undefined' == $format_original )
					{
						//	set to allow e.g.acf to use auto format
						$format_current = $format_original;
					}
				}
				else if( isset( $atts[ $element['id'] ] ) )
				{
					$value = $atts[ $element['id'] ];
				}

				if( '' == trim( $value ) )
				{
					continue;
				}

				$orig_val = $value;

				$value = $this->replace_pseudo_shortcode( $value, $format_current );

				if( false !== strpos( $value, '[av_dynamic_el ' ) )
				{
					/**
					 * Allows to display undecoded dynamic content (e.g. for debugging, development, ...)
					 *
					 * @since 6.0
					 * @param boolean $show_undecoded_content
					 * @return boolean
					 */
					$show_undecoded_content = apply_filters( 'avf_show_undecoded_dynamic_content', false );

					if( $show_undecoded_content || ! $this->source_post instanceof WP_Post )
					{
						$result = $orig_val;
					}
					else
					{
						$result = do_shortcode( $value );
					}

					if( 'content' == $element['id'] )
					{
						$content = $result;
					}
					else
					{
						$atts[ $element['id'] ] = $result;
					}
				}
			}
		}

		/**
		 * Replaces the pseudo shortcodes with real shortcode.
		 * This also replaces it in the attributes of the shortcode attributes.
		 *
		 * @since 6.0
		 * @param string $content
		 * @param string $format					'' | 'auto' | 'undefined' | ......
		 * @return string
		 */
		public function replace_pseudo_shortcode( $content, $format = 'undefined' )
		{
			if( false !== strpos( $content, '{wp_post_' ) )
			{
				$content = preg_replace_callback( '!{(wp_post_[^}]*)}!mi', [ $this, 'cb_replace_pseudo_sc_post' ], $content );
			}

			if( false !== strpos( $content, '{wp_custom_field:' ) )
			{
				$content = preg_replace_callback( '!{(wp_custom_field:[^}]*)}!mi', [ $this, 'cb_replace_pseudo_sc_custom' ], $content );
			}

			/**
			 * Allow to hook 3-rd party to replace pseudo shortcodes
			 *
			 * @used_by				avia_ACF::handler_avf_replace_pseudo_shortcode		10
			 * @since 6.0
			 * @param string $content
			 * @param string $format			'' | 'auto' | 'undefined' | .....
			 * @return string
			 */
			$content = apply_filters( 'avf_replace_pseudo_shortcode', $content, $format );

			if( false !== strpos( $content, '{av_dynamic_el ' ) )
			{
				$content = str_replace( [ '{', '}', '&#8221;' ], [ '[', ']', '"' ], $content );
			}

			return $content;
		}

		/**
		 * Callback to replace shorthand pseudo shortcode with real shortcode
		 *
		 * @since 6.0
		 * @param array $matches
		 * @return string
		 */
		protected function cb_replace_pseudo_sc_post( array $matches )
		{
			if( ! isset( $matches[1] ) )
			{
				return $matches[0];
			}

			$format = '';

			if( in_array( $matches[1], [ 'wp_post_date_time', 'wp_post_modified_time' ] ) )
			{
				$format = ' format="date_time"';
				$matches[1] = str_replace( '_time', '', $matches[1] );
			}

			if( in_array( $matches[1], [ 'wp_post_author_name', 'wp_post_categories_name', 'wp_post_tags_name' ] ) )
			{
				$format = ' link="no_link"';
				$matches[1] = str_replace( '_name', '', $matches[1] );
			}

			return '[av_dynamic_el src="' . $matches[1] . '"' . $format . ']';
		}

		/**
		 * Callback to replace shorthand pseudo shortcode with real shortcode for a custom field
		 *
		 * @since 6.0
		 * @param array $matches
		 * @return string
		 */
		protected function cb_replace_pseudo_sc_custom( array $matches )
		{
			if( ! isset( $matches[1] ) )
			{
				return $matches[0];
			}

			$key = trim( str_replace( 'wp_custom_field:', '', $matches[1] ) );

			return '[av_dynamic_el src="wp_custom_field" key="' . $key . '"]';
		}

		/**
		 * Checks if dynamic string contains a link
		 *
		 * @since 6.0
		 * @param array $atts
		 * @param array|string $keys
		 * @return boolean
		 */
		public function contains_link( array &$atts, $keys )
		{
			if( ! $this->dynamic_content_enabled() )
			{
				return false;
			}

			if( ! is_array( $keys ) )
			{
				$keys = [ $keys ];
			}

			foreach( $keys as $key )
			{
				if( ! isset( $atts[ $key ] ) && 'content' != $key )
				{
					continue;
				}

				if( ! empty( $atts[ $key ] ) )
				{
					$matches = [];
					$result = preg_match( '/<a[ ]+[^>]*href=[\"\'](.*?)[\"\'][^>]*>(.*?)<\/a>/mi', $atts[ $key ], $matches, PREG_OFFSET_CAPTURE );

					if( $result !== false && $result > 0 )
					{
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Sets the post ID to read data from
		 *
		 * @since 6.0
		 * @param int|null $post_id   ?????
		 */
		protected function set_source_post( $post_id = null )
		{
			global $post;

			$source_post = null;
			$current_post_id = null;

			if( Avia_Builder()->in_text_to_preview_mode() || Avia_Builder()->in_text_to_interface_mode() )
			{
				$current_post_id = Avia_Builder()->in_text_to_preview_mode() ? Avia_Builder()->get_text_to_preview_post_id() : Avia_Builder()->get_text_to_interface_post_id();
			}
			else if( ! $post instanceof WP_Post )
			{
				//	open of modal popup
				if( isset( $_POST['avia_request'] ) && 'true' == $_POST['avia_request'] && isset( $_POST['post_id'] ) )
				{
					$current_post_id = intval( $_POST['post_id'] );
				}
				else
				{
					$this->source_post = null;
					return;
				}
			}
			else
			{
				$current_post_id = $post->ID;
				if( ! isset( $this->source_post_cache[ $current_post_id ] ) )
				{
					$this->source_post_cache[ $current_post_id ] = $post;
				}
			}

			if( isset( $this->source_post_cache[ $current_post_id ] ) )
			{
				$source_post = $this->source_post_cache[ $current_post_id ];
			}
			else
			{
				$source_post = get_post( $current_post_id );
			}

			if( $source_post instanceof WP_Post && Avia_Custom_Layout()->get_post_type() == $source_post->post_type )
			{
				$id = get_post_meta( $source_post->ID, '_custom_layout_post_id', true );
				$source_post = null;

				if( ! empty( $id ) )
				{
					if( ! isset( $this->source_post_cache[ $id ] ) )
					{
						$dummy_post = get_post( $id );

						if( $dummy_post instanceof WP_Post )
						{
							$this->source_post_cache[ $id ] = $dummy_post;
							$source_post = $dummy_post;
						}
					}
					else
					{
						$source_post = $this->source_post_cache[ $id ];
					}
				}
			}
			else if( $source_post instanceof WP_Post && ! isset( $this->source_post_cache[ $source_post->ID ] ) )
			{
				$this->source_post_cache[ $source_post->ID ] = $source_post;
			}

			if( ! $source_post instanceof WP_Post )
			{
				$this->source_post = null;
			}
			else
			{
				$this->source_post = $source_post;
			}

			return;
		}

		/**
		 * Reads dynamic post date
		 *
		 * @since 6.0
		 * @param array $atts
		 * @return string
		 */
		protected function read_wp_post_data( array $atts )
		{
			$data = null;

			$source = substr( $atts['src'], strlen( 'wp_' ) );

			switch( $source )
			{
				case 'post_type':
					$pt = get_post_type( $this->source_post->ID );
					$post_type_obj = get_post_type_object( $pt );
					$data = $post_type_obj->labels->name;

					if( $atts['link'] != 'no_link' )
					{
						$target = $this->html_target( $atts['link'] );
						$title = __( 'Link to archive:', 'avia_framework' ) . " {$data}";

						if( 'post' == $pt )
						{
							$page_id = '';
							$href = '';

							//	check if we selected a blogpage
							if( avia_get_option( 'blogpage' ) )
							{
								$page_id = avia_page_for_posts_filter( '' );
							}

							if( $page_id )
							{
								$href = get_permalink( $page_id );
							}

							if( empty( $href ) )
							{
								$href = get_post_type_archive_link( $pt );
							}
						}
						else
						{
							$href = get_post_type_archive_link( $pt );
						}

						if( false !== $href )
						{
							$data = "<a href='{$href}' class='av-dynamic-content-added av-dynamic-{$source}' title='{$title}' {$target}>{$data}</a>";
						}
						else
						{
							if( defined( 'WP_DEBUG' ) && WP_DEBUG && current_user_can( 'edit_posts' ) )
							{
								$data .= ' ( ' . __( 'No archive link available', 'avia_framework' ) . ' )';
							}
						}
					}
					break;

				case 'post_author':
					$data = get_the_author_meta( 'display_name', $this->source_post->post_author );

					if( $atts['link'] != 'no_link' )
					{
						$target = $this->html_target( $atts['link'] );
						$href = get_author_posts_url( $this->source_post->post_author );
						$title = __( 'Author:', 'avia_framework' ) . " {$data}";

						$data = "<a href='{$href}' class='av-dynamic-content-added av-dynamic-{$source}' title='{$title}' {$target}>{$data}</a>";
					}
					break;

				case 'post_categories':
				case 'post_tags':
					//	based on config-templatebuilder\avia-shortcodes\post_metadata\post_metadata.php
					//
					// Get post type taxonomies.
					$taxonomies = get_object_taxonomies( $this->source_post->post_type, 'objects' );
					$names = array();
					$links = array();

					/**
					 * Filter excluded taxonomies
					 *
					 * @since 4.8.9.1
					 * @param array $taxonomies
					 * @return array
					 */
					$excluded_taxonomies = apply_filters( 'avf_dynamic_content_excluded_taxonomies', array( 'post_tag', 'post_format' ), $taxonomies );

					foreach ( $taxonomies as $taxonomy_slug => $taxonomy )
					{
						if( $source == 'post_tags' )
						{
							if( $taxonomy_slug != 'post_tag' )
							{
								continue;
							}
						}
						else if( in_array( $taxonomy_slug, $excluded_taxonomies ) )
						{
							continue;
						}

						// Get the terms related to post.
						$terms = get_the_terms( $this->source_post->ID, $taxonomy_slug );

						if( ! is_array( $terms ) )
						{
							continue;
						}

						foreach ( $terms as $term )
						{
							$names[ $term->slug ] = $term->name;

							if( $atts['link'] != 'no_link' )
							{
								$links[ $term->slug ] = esc_url( get_term_link( $term ) );
							}
						}
					}

					if( empty( $names ) )
					{
						return '';
					}

					asort( $names );

					$sep = '';
					$target = $this->html_target( $atts['link'] );
					$pre_title = 'post_categories' == $source ? _n( 'Category:', 'Categories:', 1, 'avia_framework' ) : _n( 'Tag:', 'Tags:', 1, 'avia_framework' );

					$output	 = '';
					$output	.= '<span class="av-dynamic-content-wrap av-dynamic-content-category">';

					foreach( $names as $slug => $name )
					{
						if( $sep != '' )
						{
							$output	.= $sep;
						}

						if( $atts['link'] != 'no_link' )
						{
							$title = "{$pre_title} {$name}";

							$output	.=	'<span class="av-dynamic-content-category-link" >';
							$output	.=		"<a href='{$links[ $slug ]}' {$target} title='{$title}'>{$name}</a>";
							$output	.=	'</span>';
						}
						else
						{
							$output	.=	'<span class="av-dynamic-content-category-name">';
							$output	.=		$name;
							$output	.=	'</span>';
						}

						$sep = ', ';
					}

					$output .= '</span>';

					$data = $output;
					break;

				case 'post_revisions':
					$revisions = wp_get_post_revisions( $this->source_post->ID );
					$count = count( $revisions );

					$data = $this->format_output_data( $count, $atts, $source, 'integer' );
					break;

				case 'post_ID':
				case 'post_comment_count':
					$db_source = str_replace( 'post_', '', $source );
					$data = $this->format_output_data( $this->source_post->{$db_source}, $atts, $source, 'integer' );
					break;

				case 'post_title':
				case 'post_content':
				case 'post_excerpt':
					$data = $this->format_output_data( $this->source_post->{$source}, $atts, $source );
					break;

				case 'post_date':
				case 'post_modified':
					$format = $atts['format'] != 'date_time' ? 'date' : $atts['format'];
					$data = $this->format_output_data( $this->source_post->{$source}, $atts, $source, $format );
					break;

				default:
					$new_source = $source;
					if( Avia_Builder()->in_text_to_preview_mode() || is_preview() )
					{
						$new_source = __( 'Unknown WP key', 'avia_framework' ) . " [[{$new_source}]]: ";
					}

					$data = $this->format_output_data( $new_source . $atts['default'], $atts, $source );
			}

			return $data;
		}

		/**
		 * Reads default WP postmeta data
		 *
		 * @since 6.0
		 * @param array $atts
		 * @return string
		 */
		protected function read_wp_custom_field( array $atts )
		{
			$data = null;
			$key = trim( $atts['key'] );

			if( $key != '' && $this->source_post instanceof WP_Post )
			{
				$values = get_post_meta( $this->source_post->ID, $key );

				if( is_array( $values ) && ! empty( $values ) )
				{
					$result = [];

					foreach( $values as $index => $value )
					{
						/**
						 * Filter custom field format - by default we must use string because some numbers might be interpreted wrong
						 * (e.g. ID 2024 is returned as date_time) with
						 *
						 *		$format = $this->get_data_format( $value, $atts['format'] );
						 *
						 * @since 6.0
						 * @param string $format
						 * @param string $key
						 * @param string $value
						 * @param int $index
						 * @param WP_Post $this->source_post
						 * @param array $atts
						 * @return string
						 */
						$format = apply_filters( 'avf_custom_field_format', $atts['format'], $key, $value, $index, $this->source_post, $atts );

						$result[] = $this->format_output_data( $value, $atts, $key, $format );
					}

					$data = implode( ', ', $result );
				}
				else
				{
					$data = null;
				}
			}

			if( is_null( $data ) )
			{
				$data = $this->format_output_data( $atts['default'], $atts, $key, $atts['format'] );
			}

			/**
			 * Filter and alter the final output of custom field content
			 *
			 * @since 6.0
			 * @param string $data
			 * @param array $atts
			 * @param WP_Post|mixed $this->source_post
			 * @return string
			 */
			return apply_filters( 'avf_custom_field_final_data', $data, $atts, $this->source_post );
		}

		/**
		 * Tries to decode format of $data
		 *
		 * @since 6.0
		 * @param mixed $data
		 * @param string $format
		 * @return string
		 */
		protected function get_data_format( $data, $format = '' )
		{
			if( $format != 'autoformat' )
			{
				return $format;
			}

			if( is_object( $data ) || is_array( $data ) )
			{
				return $format;
			}

			if( is_numeric( $data ) )
			{
				if( intval( $data ) . '' == trim( $data ) )
				{
					return 'integer';
				}

				return 'float';
			}

			if( is_string( $data ) )
			{
				$date = strtotime( $data );
				if( false !== $date )
				{
					return 'date_time';
				}
			}

			return '';
		}

		/**
		 * Returns the formatted string
		 *
		 * @since 6.0
		 * @param string $source_data
		 * @param array $atts
		 * @param string $source
		 * @param string|null $force_format
		 * @return string
		 */
		protected function format_output_data( $source_data, $atts, $source, $force_format = '' )
		{
			$format = $atts['format'];

			if( ! empty( $force_format ) )
			{
				$format = $force_format;
			}

			$format = $this->get_data_format( $source_data, $format );

			$ret_val = null;

			switch( $format )
			{
				case 'no_html':
					$ret_val = esc_html( $source_data );
					break;
				case 'integer':
				case 'float':
					if( ! is_numeric( $source_data ) )
					{
						$ret_val = $source_data;
						break;
					}

					$locale_info = localeconv();

					$separators = [
							'thousands_sep'	=> $locale_info['thousands_sep'],
							'decimal_point'	=> $locale_info['decimal_point']
						];

					$dec = 0;

					if( '' == $separators['thousands_sep'] )
					{
						$separators['thousands_sep'] = '.' == $separators['decimal_point'] ? ',' : '.';
					}

					/**
					 * @since 6.0
					 * @param array $separators
					 * @param string $source_data
					 * @param array $atts
					 * @param string $source
					 * @return array
					 */
					$separators = apply_filters( 'avf_dynamic_format_numeric_separators', $separators, $source_data, $atts, $source );

					if( 'float' == $format )
					{
						$dec_pos = strpos( $source_data, $separators['decimal_point'] );
						if( false !== $dec_pos )
						{
							$dec = strlen( $source_data ) - $dec_pos - 1;
							if( $dec < 0 )
							{
								$dec = 0;
							}
						}

						$source_data = floatval( $source_data );
					}
					else
					{
						$source_data = intval( $source_data );
					}

					$ret_val = number_format( $source_data, $dec, $separators['decimal_point'], $separators['thousands_sep'] );
					break;
				case 'date':
				case 'date_time':
					$date_format = get_option( 'date_format' );
					$time_format = get_option( 'time_format' );
					$date = strtotime( $source_data );

					if( $format == 'date_time' )
					{
						$date_format .= ' ' . $time_format;
					}

					if( false !== $date )
					{
						return date( $date_format, $date );
					}

					$ret_val = ( Avia_Builder()->in_text_to_preview_mode() || is_preview() ) ? "### invalid {$format}: {$source_data} ###" : null;
					break;
				case 'link':
					$check_link = ! empty( $source_data ) ? $source_data : $atts['default'];

					if( false !== strpos( $check_link, 'http://') || false !== strpos( $check_link, 'https://' ) || false !== strpos( $check_link, 'localhost/' ) || false !== strpos( $check_link, 'www.' ) )
					{
						$target = $this->html_target( $atts['link'] );
						$text = ! empty( $atts['linktext'] ) ? $atts['linktext'] : esc_url( $check_link );
						$href = esc_url( $check_link );
						$title = ! empty( $atts['linktext'] ) ? $atts['linktext'] : esc_attr( $check_link );
						$title = __( 'Link to:', 'avia_framework' ) . " {$title}";

						$ret_val = "<a class='av-dynamic-content-added av-dynamic-{$source}' href='{$href}' title='{$title}' {$target}>{$text}</a>";
					}
					else
					{
						$ret_val = $check_link;
					}
					break;
				case '':
					$ret_val = $source_data;
					break;
				default:
					$ret_val = $source_data;
					break;
			}

			/**
			 * Filter the formatted output
			 *
			 * @since 6.0
			 * @param string $ret_val
			 * @param string $source_data
			 * @param array $atts
			 * @param string $source
			 * @param string|null $force_format
			 * @return string
			 */
			return apply_filters( 'avf_dynamic_format_output_data', $ret_val, $source_data, $atts, $source, $force_format );
		}

		/**
		 * Returns the HTML string for target based on selectbox entry
		 *
		 * @since 6.0
		 * @param string $link
		 * @return string
		 */
		protected function html_target( $link = '' )
		{
			return 'blank' == $link ? ' target="_blank" rel="noopener noreferrer" ' : '';
		}

		/**
		 * Wrapper function for default WP function get_post that is not hooked by e.g. WPML
		 *
		 * @since 6.0
		 * @param int $post_id
		 * @param boolean $force_original			force to load requested ID and not a translated
		 * @return WP_Post|false
		 */
		protected function get_post( $post_id, $force_original = false )
		{
			global $wp_post_types;

			if( post_type_exists( Avia_Custom_Layout()->get_post_type() ) )
			{
				$wp_post_types[ Avia_Custom_Layout()->get_post_type() ]->exclude_from_search = false;
			}

			$args = [
						'numberposts'		=> 1,
						'include'			=> [ $post_id ],
						'post_type'			=> 'any',
						'suppress_filters'	=> false
					];

			/**
			 * Allows e.g. WPML to reroute to translated object
			 */
			if( false === $force_original )
			{
				$posts = get_posts( $args );
				$post = is_array( $posts ) && count( $posts ) > 0 ? $posts[0] : false;
			}
			else
			{
				$post = get_post( $post_id );
			}

			if( post_type_exists( Avia_Custom_Layout()->get_post_type() ) )
			{
				$wp_post_types[ Avia_Custom_Layout()->get_post_type() ]->exclude_from_search = true;
			}

			return $post instanceof WP_Post ? $post : false;
		}

		/**
		 * Based on WP function meta_form() in ..\wp-admin\includes\template.php
		 *
		 * @since 6.0
		 * @return array
		 */
		protected function get_default_custom_fields()
		{
			global $wpdb;

			if( ! is_admin() && ! is_ajax() )
			{
				return [];
			}

			if( is_array( $this->custom_fields_cache ) )
			{
				return $this->custom_fields_cache;
			}

			/**
			 * Filters to limit custom fields to public only
			 *
			 * @since 6.0
			 * @param boolean $filter
			 * @return boolean
			 */
			$filter = apply_filters( 'avf_query_default_custom_fields_filter', true );

			/**
			 * Filters the number of custom fields to retrieve. If 0 there is no limit
			 *
			 * @since 6.0
			 * @param int $limit Number of custom fields to retrieve.
			 * @return int
			 */
			$limit = apply_filters( 'avf_query_default_custom_fields_limit', 0 );


			/**
			 * Allow to short circuit the query against post meta table.
			 * Return an array filled with the keys.
			 * Query for _ only might get much longer than other queries
			 *
			 * @since 6.0
			 * @param array|null $keys
			 * @param bool $filter
			 * @param int $limit
			 * @return array|null
			 */
			$wp_custom_fields = apply_filters( 'avf_before_query_wp_default_custom_fields', null, $filter, $limit );

			if( ! is_array( $wp_custom_fields ) )
			{
				$sql  = "SELECT DISTINCT meta_key FROM $wpdb->postmeta ";

				if( false !== $filter )
				{
					$sql .= 'WHERE meta_key NOT LIKE "' . $wpdb->esc_like( '_' ) . '%" ';
				}

				$sql .= 'ORDER BY meta_key ';

				if( ! empty( $limit ) && is_numeric( $limit ) )
				{
					$sql .= 'LIMIT ' . intval( $limit );
				}

				$wp_custom_fields = $wpdb->get_col( $sql );
			}

			if( is_array( $wp_custom_fields ) && ! empty( $wp_custom_fields ) )
			{
				natcasesort( $wp_custom_fields );
			}
			else
			{
				$wp_custom_fields = [];
			}

			/**
			 * Filter the query against post meta table.
			 * Return an array filled with the keys.
			 *
			 * @since 6.0
			 * @used_by						avia_ACF::handler_avf_query_wp_default_custom_fields		10
			 * @param array $wp_custom_fields
			 * @param bool $filter
			 * @param int $limit
			 * @return array|null
			 */
			$wp_custom_fields = apply_filters( 'avf_query_wp_default_custom_fields', $wp_custom_fields, $filter, $limit );

			$custom_fields_cache = [];

			$custom_fields_cache[ 'wp_custom_field' ] = __( 'Theme Custom Field Shortcode', 'avia_framework' );

			//	add WP special custom fields
			if( current_theme_supports( 'post-thumbnails' ) )
			{
				$custom_fields_cache[ "wp_custom_field__thumbnail_id"] = __( 'Posts featured image ID', 'avia_framework' );
			}

			foreach( $wp_custom_fields as $custom_field )
			{
				$custom_fields_cache[ "wp_custom_field_{$custom_field}" ] = $custom_field;
			}

			/**
			 *
			 * @since 6.0
			 * @param array $custom_fields_cache
			 * @return array
			 */
			$this->custom_fields_cache = apply_filters( 'avf_query_custom_fields_cache', $custom_fields_cache );

			return $this->custom_fields_cache;
		}

		/**
		 * Returns class for surrounding container to style selectlist element
		 *
		 * @since 6.0
		 * @param array $element
		 * @return string
		 */
		public function modal_container_select_class( array $element )
		{
			return ( $this->dynamic_content_enabled() && isset( $element['dynamic'] ) ) ? 'avia-dynamic-select-container' : '';
		}

		/**
		 * Adds the dynamic part to modal popup window element
		 *
		 * @since 6.0
		 * @param array $element
		 * @return string
		 */
		public function get_modal_selectlist( array $element )
		{
			if( ! $this->dynamic_content_enabled() || ! isset( $element['dynamic'] ) )
			{
				return '';
			}

			if( isset( $element['type'] ) && 'tiny_mce' == $element['type'] )
			{
				return '';
			}

			if( ! $this->source_post instanceof WP_Post )
			{
				$this->set_source_post();
			}

			$title = __( 'At cursor position insert a dynamic content replacement from dropdown', 'avia_framework' );

			$output = '';

			$char = \avia_font_manager::get_display_char( 'ue8d3', 'entypo-fontello' );
			$output .= '<span class="avia-dynamic-char avia-font-entypo-fontello" title="' . esc_attr( $title ) . '">' . $char . '</span>';
			$output .= $this->create_select_list( $element );

			return $output;
		}

		/**
		 * Creates the popup select list to insert replaceable dynamic content string
		 *
		 * @since 6.0
		 * @param array $element
		 * @return string
		 */
		protected function create_select_list( array $element )
		{
			$dynamic = $element['dynamic'];

			if( empty( $dynamic ) || ! is_array( $dynamic ) )
			{
				$dynamic = array_keys( $this->groups );
			}

			$clear = ( isset( $element['dynamic_clear'] ) && true === $element['dynamic_clear'] ) ? 'av-dynamic-clear' : '';

			$output  = '';
			$output .= "<ul class='av-dynamic-select {$clear}'>";

			foreach( $this->groups as $group_key => $value )
			{
				if( ! in_array( $group_key, $dynamic ) )
				{
					continue;
				}

				/**
				 * Filter select list dropdown
				 *
				 * @used_by				avia_ACF::handler_avf_dynamic_filter_select_list		10
				 * @since 6.0
				 * @param array $this->fields
				 * @param string $group_key
				 * @param WP_Post|null $this->source_post
				 * @param array $element
				 * @return array
				 */
				$fields_filtered = apply_filters( 'avf_dynamic_filter_select_list', $this->fields[ $group_key ], $group_key, $this->source_post, $element );

				if( is_array( $fields_filtered ) && ! empty( $fields_filtered ) )
				{
					$output .= "<li class='av-dynamic-select-group {$group_key}'>";
					$output .=		$value;
					$output .= '</li>';

					foreach( $fields_filtered as $field_key => $field_value )
					{
						$data = esc_attr( json_encode( $this->create_sc_data_string( $group_key, $field_key, 'pseudo_code', $element ) ) );

						$output .= "<li class='av-dynamic-select-element {$field_key}' data-dynamic='{$data}'>";
						$output .=		$field_value;
						$output .= '</li>';
					}
				}
			}

			$output .= '</ul>';

			/**
			 * @since 6.0
			 * @param string $output
			 * @param array $element
			 * @return string
			 */
			$output = apply_filters( 'avf_dynamic_create_select_list', $output, $element );

			return $output;
		}

		/**
		 * Creates the data attribute string to insert as replaceable dynamic content or as shortcode
		 *
		 * key
		 *
		 * or
		 *
		 * Key => value | key => value
		 *
		 * @since 6.0
		 * @param string $group_key
		 * @param string $field_key
		 * @param string $context			'pseudo_code' | 'shortcode'
		 * @param array $element
		 * @return string
		 */
		protected function create_sc_data_string( $group_key, $field_key, $context = 'pseudo_code', array $element = []  )
		{
			$data = '';

			if( 'wp_custom_field' == $group_key )
			{
				if( ! isset( $this->shortcodes[ $field_key ] ) )
				{
					$meta_key = str_replace( 'wp_custom_field_', '', $field_key );

					if( 'pseudo_code' == $context )
					{
						$data = "{wp_custom_field:$meta_key}";
					}
					else
					{
						$data = $this->shortcodes[ 'wp_custom_field' ];
						$data = str_replace( '%metakey%', $meta_key, $data );
					}
				}
				else
				{
					$data = $this->shortcodes[ $field_key ];
				}
			}
			else if( false !== strpos( $group_key, 'wp_post' ) )
			{
				if( isset( $this->shortcodes[ $field_key ] ) )
				{
					$data =  'shortcode' == $context ? $this->shortcodes[ $field_key ] : '{' . $field_key . '}';
				}
			}

			/**
			 * Allow 3rd party to modify defaults or react to own groups or fields
			 *
			 * @used_by					avia_ACF::handler_avf_create_sc_data_string		10
			 * @since 6.0
			 * @param string $data
			 * @param string $group_key
			 * @param string $field_key
			 * @param array $this->shortcodes
			 * @param string $context			'pseudo_code' | 'shortcode'
			 * @param array $element
			 * @return string
			 */
			$data = apply_filters( 'avf_create_sc_data_string', $data, $group_key, $field_key, $this->shortcodes, $context, $element );

			if( 'shortcode' == $context )
			{
				$data = str_replace( [ '{', '}' ], [ '[', ']' ], $data );
			}
			else
			{
//				$data = str_replace( [  "'" ], [  '"' ], $data );
			}

			return $data;
		}

		/**
		 * Returns an info string when enabled
		 *
		 * @since 6.0
		 * @return string
		 */
		public function modal_link_message_info()
		{
			if( $this->dynamic_content_enabled() )
			{
				return $this->modal_link_message;
			}

			return '';
		}

		/**
		 * Gets a comma separated string of ID's from a custom field, checks for integer and returns as comma separated string.
		 * As fallback $fallback is returned.
		 *
		 * @since 6.0
		 * @param string $check
		 * @param string $fallback
		 * @return string
		 */
		public function check_id_list( $check, $fallback = '' )
		{
			if( ! $this->dynamic_content_enabled() )
			{
				return $fallback;
			}

			if( empty( $check ) )
			{
				return $fallback;
			}

			$split = explode( ',', $check );

			foreach( $split as &$value )
			{
				if( ! is_numeric( $value ) )
				{
					return $fallback;
				}

				$value = intval( $value );
			}

			unset( $value );


			return implode( ',', $split );
		}

		/**
		 * Checks if the content of a custom field is a valid link.
		 * As fallback $fallback is returned.
		 *
		 * @since 6.0
		 * @param string $check
		 * @param string $fallback
		 * @param array $allowed
		 * @return string
		 */
		public function check_link( $check, $fallback = '', array $allowed = [ 'no', 'lightbox', 'manually', 'single', 'taxonomy' ] )
		{
			if( ! $this->dynamic_content_enabled() )
			{
				return $fallback;
			}

			$check = trim( $check );

			if( empty( $check ) )
			{
				return $fallback;
			}

			$this->set_source_post();

			if( 'no-link' == $check )
			{
				return in_array( 'no', $allowed ) ? '' : $fallback;
			}

			if( 'lightbox' == $check )
			{
				return in_array( 'lightbox', $allowed ) ? $check : $fallback;
			}

			$pattern = "!http:\/\/|https:\/\/|mailto:|@!si";
			$matches = [];

			if( preg_match( $pattern, $check, $matches, PREG_OFFSET_CAPTURE ) )
			{
				return "manually,{$check}";
			}

			$test = explode( ',', $check, 2 );

			if( count( $test ) < 2 || ! is_numeric( $test[1] ) )
			{
				return $fallback;
			}

			return $test[0] . ',' . intval( $test[1] );
		}
	}

	/**
	 * Returns the main instance of aviaDynamicContent to prevent the need to use globals
	 *
	 * @since 6.0
	 * @return aviaDynamicContent
	 */
	function Avia_Dynamic_Content()
	{
		return aviaDynamicContent::instance();
	}

	/**
	 * Activate filter and action hooks
	 *
	 * @since 6.0
	 */
	Avia_Dynamic_Content();

}
