<?php
/**
 * This class handles integration of WPML. Functions fro config.php will be moved here in future started with 4.8
 *
 * @author guenter
 * @since 4.8
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_WPML' ) )
{
	class avia_WPML
	{
		const MIN_TM_VERSION = '2.10.5';

		/**
		 * Holds db option name for each language
		 *			'lang'	=>  db_option_name
		 *
		 * @since 4.8
		 * @var array
		 */
		protected $option_langs;

		/**
		 * Stores the WPML translateable attributes for a shortcode:
		 *
		 *			Shortcode_name	=> array ( attribute_name, ....  )
		 *
		 * @since 4.8
		 * @var array
		 */
		protected $wpml_sc_config;

		/**
		 * Holds the instance of this class
		 *
		 * @since 4.8
		 * @var avia_WPML
		 */
		static private $_instance = null;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.8
		 * @return avia_WPML
		 */
		static public function instance()
		{
			if( is_null( avia_WPML::$_instance ) )
			{
				avia_WPML::$_instance = new avia_WPML();
			}

			return avia_WPML::$_instance;
		}

		/**
		 * @since 4.8
		 */
		public function __construct()
		{
			$this->option_langs = array();
			$this->wpml_sc_config = array();

			//	Theme Options and Options Page
			add_filter( 'avia_filter_base_data', array( $this, 'handler_avf_options_languages' ), 10, 1 );
			add_action( 'avia_action_before_framework_init', array( $this, 'handler_ava_get_languages' ), 10, 0 );
			add_action( 'avia_wpml_backend_language_switch', array( $this, 'handler_ava_copy_options' ), 10, 0 );
			add_action( 'ava_theme_options_elements_tab', array( $this, 'handler_ava_theme_options_elements_tab' ), 10, 1 );

			add_filter( 'avf_theme_options_element_name', array( $this, 'handler_avf_theme_options_element_name' ), 10, 2 );
			add_action( 'avia_ajax_after_save_options_page', array( $this, 'handler_ava_after_save_options_page' ), 10, 1 );
			add_action( 'avia_ajax_reset_options_page', array( $this, 'handler_ava_reset_options_page' ), 10, 1 );

			add_filter( 'avf_dropdown_post_query', array( $this, 'handler_avf_dropdown_post_query' ), 10, 4 );

			add_action( 'wp_enqueue_scripts', array( $this, 'handler_wp_enqueue_scripts' ), 500, 0 );

			//	frontend
			add_filter( 'wp_nav_menu_items', array( $this, 'handler_append_lang_flags' ), 9998, 2 );
			add_filter( 'avf_fallback_menu_items', array( $this, 'handler_append_lang_flags' ), 9998, 2 );
			add_action( 'avia_meta_header', array( $this, 'handler_append_lang_flags_extra_menu' ), 10 );
			add_action( 'ava_main_header_sidebar', array( $this, 'handler_append_lang_flags_extra_menu' ), 10 );

			add_filter( 'avia_post_slide_query', array( $this, 'handler_avf_post_slide_query' ), 20, 2 );
			add_filter( 'avf_ratio_check_by_tag_values', array( $this, 'handler_avf_ratio_check_by_tag_values' ), 10, 1 );


			//	Custom Element Templates handling
			//	=================================
			//

			//	hooked-by:  sitepress-multilingual-cms\compatibility\enfold\class-wpml-compatibility-theme-enfold.php
			add_filter( 'wpml_pb_shortcode_content_for_translation', array( $this, 'handler_wpml_pb_shortcode_content_for_translation' ), 20, 2 );

			add_filter( 'avf_element_templates_enabled', array( $this, 'handler_avf_element_templates_enabled' ), 10, 1 );
			add_filter( 'avf_theme_options_heading_desc', array( $this, 'handler_avf_theme_options_heading_desc' ), 10, 2 );
			add_filter( 'avf_cet_additional_sc_action_btn', array( $this, 'handler_avf_cet_additional_sc_action_btn' ), 10, 2 );
			add_filter( 'avf_alb_metabox_title_prefix_cet', array( $this, 'handler_avf_alb_metabox_title_prefix_cet' ), 10, 1 );
			add_filter( 'avf_custom_element_template_id', array( $this, 'handler_avf_custom_element_template_id' ), 10, 1 );

			add_filter( 'wpml_document_view_item_link', array( $this, 'handler_wpml_document_view_item_link' ), 10, 5 );
			add_filter( 'wpml_document_edit_item_link', array( $this, 'handler_wpml_document_edit_item_link' ), 10, 5 );
			add_action( 'icl_post_languages_options_after', array( $this, 'handler_icl_post_languages_options_after' ), 10, 0 );

			add_filter( 'avf_cookie_consent_for_md5', array( $this, 'handler_avf_cookie_consent_for_md5' ), 10, 1 );
		}


		/**
		 * @since 4.8
		 */
		public function __destruct()
		{
			unset( $this->option_langs );
			unset( $this->wpml_sc_config );
		}

		/**
		 * Fetch some default data necessary for the framework
		 *
		 * @since ????			moved from config-wpml\config.php
		 * @since 4.8
		 */
		public function handler_ava_get_languages()
		{
			global $sitepress, $avia_config;

			$avia_config['wpml']['lang'] = $sitepress->get_active_languages();
			$avia_config['wpml']['settings'] = get_option( 'icl_sitepress_settings' );
		}

		/*
		 * This function makes it possible that all backend options can be saved several times
		 * for different languages. It appends a language string to the key of the options entry
		 * that is saved to the wordpress database.
		 *
		 * Since the Avia Framework only uses a single option array for the whole backend and
		 * then serializes that array and saves it to a single database entry this is a very
		 * easy and flexible method to setup your site in any way you want with muliple
		 * languages, layouts, logos, dynamic templates, etc for each language
		 *
		 * @since ????			moved from config-wpml\config.php
		 * @since 4.8
		 * @param array $base_data
		 * @return array
		 */
		public function handler_avf_options_languages( array $base_data )
		{
			global $avia_config;

			$wpml_options = $avia_config['wpml']['settings'];

			if( ( isset( $wpml_options['default_language'] ) && $wpml_options['default_language'] != ICL_LANGUAGE_CODE ) && 'all' != ICL_LANGUAGE_CODE && '' != ICL_LANGUAGE_CODE )
			{
				$base_data['prefix_origin'] = $base_data['prefix'];
				$base_data['prefix'] = $base_data['prefix'] . '_' . ICL_LANGUAGE_CODE;
			}

			return $base_data;
		}

		/**
		 * Copy the default option set to the current language if no options set for this language is available yet
		 * Called when switching languages in backend
		 *
		 * @since ????			moved from config-wpml\config.php
		 * @since 4.8
		 */
		public function handler_ava_copy_options()
		{
			global $avia;

			$key = isset( $avia->base_data['prefix_origin'] ) ? $avia->base_data['prefix_origin'] : $avia->base_data['prefix'];

			$original_key = 'avia_options_' . avia_backend_safe_string( $key );
			$language_key = 'avia_options_' . avia_backend_safe_string( $avia->base_data['prefix'] );

			if( $original_key === $language_key )
			{
				return;
			}

			$option_set = get_option( $original_key );
			$lang_set = get_option( $language_key );

			//	new language - copy options
			if( empty( $lang_set ) || ! is_array( $lang_set ) )
			{
				update_option( $language_key, $option_set );

				wp_redirect( $_SERVER['REQUEST_URI'] );
				exit();
			}

			//	@since 4.8: Make sure that all top level options from main language exist in other language
			$added = false;

			foreach( $option_set as $page => $options )
			{
				if( ! isset( $lang_set[ $page ] ) )
				{
					$added = true;
					$lang_set[ $page ] = $options;
					continue;
				}

				foreach( $options as $key => $value )
				{
					if( ! isset( $lang_set[ $page ][ $key ] ) )
					{
						$added = true;

						//	if nested, we take all options
						$lang_set[ $page ][ $key ] = $value;
					}
				}
			}

			if( $added )
			{
				update_option( $language_key, $lang_set );

				wp_redirect( $_SERVER['REQUEST_URI'] );
				exit();
			}
		}

		/**
		 * Add Additional info to theme options name field for global and global layout options
		 *
		 * @since 4.8
		 * @param string $name
		 * @param array $element
		 * @return string
		 */
		public function handler_avf_theme_options_element_name( $name, array $element )
		{
			if( isset( $element['id'] ) )
			{
				if( AviaSuperobject()->is_global_option( $element['id'], 'global' ) )
				{
					$name .= ' (' . __( 'Global Setting', 'avia_framework' ) . ')';
				}

				if( AviaSuperobject()->is_global_option( $element['id'], 'globalcss' ) && $this->is_global_layout_enabled() )
				{
					$name .= ' (' . __( 'Global Layout', 'avia_framework' ) . ')';
				}
			}

			return $name;
		}

		/**
		 * Copy global settings to all languages
		 *
		 * @since 4.8
		 * @param array $current_options
		 */
		public function handler_ava_after_save_options_page( array $current_options )
		{
			global $avia;

			if( AviaSuperobject()->global_options_count( 'global' ) == 0 && AviaSuperobject()->global_options_count( 'globalcss' ) == 0 )
			{
				return;
			}

			$all_opts = $this->wpml_get_options();

			$global_keys = AviaSuperobject()->global_option_keys( 'global' );

			if( $this->is_global_layout_enabled( $current_options ) )
			{
				$global_keys = array_merge( $global_keys, AviaSuperobject()->global_option_keys( 'globalcss' ) );
			}

			$current_lang = ICL_LANGUAGE_CODE;

			//	save options to allow the use of default functions
			$old_opt = $avia->options;
			$avia->options = $current_options;

			foreach( $global_keys as $global_key => $parent_page )
			{
				$value = avia_get_option( $global_key, '' );

				foreach( $all_opts as $lang => &$settings )
				{
					if( $lang != $current_lang )
					{
						$found = false;
						foreach( $settings as $page => &$page_options )
						{
							//	Option pages must be array - remove wrong entries
							if( ! is_array( $page_options ) )
							{
								unset( $settings[ $page ] );
								continue;
							}

							if( array_key_exists( $global_key, $page_options ) )
							{
								$settings[ $page ][ $global_key ] = $value;
								$found = true;
								break;
							}
						}

						unset( $page_options );

						if( ! $found )
						{
							$settings[ $global_keys[ $global_key ] ][ $global_key ] = $value;
						}
					}
				}

				unset( $settings );
			}

			foreach( $all_opts as $lang => $settings )
			{
				if( $lang != $current_lang )
				{
					update_option( $this->option_langs[ $lang ], $settings );
				}
			}

			$avia->options = $old_opt;
		}

		/**
		 * @since 4.8
		 * @param array $options
		 */
		public function handler_ava_reset_options_page( array $options )
		{
			/**
			 * Copy global settings to all languages
			 *
			 * @since 5.0
			 */
			$this->handler_ava_after_save_options_page( $options );
		}

		/**
		 * Append language flags.
		 *
		 * @since ???			moved from config.php
		 * @since 4.8			added theme option to disable
		 * @param string $items
		 * @param object $args
		 */
		public function handler_append_lang_flags( $items, $args )
		{
			$option = avia_get_option( 'wpml_header_lang_flags' );

			if( 'hide_all' == $option )
			{
				return $items;
			}

			if ( ( is_object( $args ) && $args->theme_location == 'avia' ) )
		    {
		        global $avia_config, $sitepress;

		        if( empty( $avia_config['wpml_language_menu_position'] ) )
				{
					$avia_config['wpml_language_menu_position'] = apply_filters( 'avf_wpml_language_switcher_position', 'main_menu' );
				}

		        if( $avia_config['wpml_language_menu_position'] != 'main_menu' )
				{
					return $items;
				}

				// icl_get_languages deprecated since 3.2
		        $languages = function_exists( 'wpml_get_active_languages_filter' ) ? wpml_get_active_languages_filter( '', 'skip_missing=0&orderby=custom' ) : icl_get_languages( 'skip_missing=0&orderby=custom' );

		        if( is_array( $languages ) )
		        {
		            foreach( $languages as $lang )
		            {
		                $currentlang = ( ICL_LANGUAGE_CODE == $lang['language_code'] ) ? 'avia_current_lang' : '';

						if( is_front_page() )
						{
							$url = $sitepress->language_url( $lang['language_code'] );

							/**
							 * @since 4.5.6.1
							 * @return string
							 */
							$lang['url'] = apply_filters( 'avf_wpml_language_switcher_url', $url, $lang['language_code'], $avia_config['wpml_language_menu_position'] );
						}

						$items .= "<li class='av-language-switch-item language_{$lang['language_code']} {$currentlang}'>";
						$items .=		'<a href="' . esc_url( $lang['url'] ) . '">';
		                $items .=			"<span class='language_flag'><img title='" . $lang['native_name'] . "' src='" . esc_url( $lang['country_flag_url'] ) . "' /></span>";
		                $items .=		'</a>';
						$items .= '</li>';
		            }
		        }
		    }

		    return $items;
		}

		/**
		 * Append language flags to extra element in top bar menu.
		 *
		 * @since ???			moved from config.php
		 * @since 5.3			added theme option to disable
		 */
		public function handler_append_lang_flags_extra_menu()
		{
			$option = avia_get_option( 'wpml_header_lang_flags' );

			if( 'hide_all' == $option )
			{
				return;
			}

			global $sitepress, $avia_config;

			if( empty( $avia_config['wpml_language_menu_position'] ) )
			{
				$avia_config['wpml_language_menu_position'] = apply_filters( 'avf_wpml_language_switcher_position', 'sub_menu' );
			}

			if( $avia_config['wpml_language_menu_position'] != 'sub_menu' )
			{
				return;
			}

			// icl_get_languages deprecated since 3.2
			$languages = function_exists( 'wpml_get_active_languages_filter' ) ? wpml_get_active_languages_filter( '', 'skip_missing=0&orderby=custom' ) : icl_get_languages( 'skip_missing=0&orderby=custom' );

			$output = '';

			if( is_array( $languages ) )
			{
				$output .= "<ul class='avia_wpml_language_switch avia_wpml_language_switch_extra'>";

				foreach( $languages as $lang )
				{
					$currentlang = ( ICL_LANGUAGE_CODE == $lang['language_code'] ) ? 'avia_current_lang' : '';

					if( ! avia_is_overview() && ( is_home() || is_front_page() ) )
					{
						$url = $sitepress->language_url( $lang['language_code'] );

						/**
						 * @since 4.5.6.1
						 * @param string $url
						 * @param string $lang['language_code']
						 * @param string $avia_config['wpml_language_menu_position']
						 * @return string
						 */
						$lang['url'] = apply_filters( 'avf_wpml_language_switcher_url', $url, $lang['language_code'], $avia_config['wpml_language_menu_position'] );
					}

					$output .= "<li class='language_" . $lang['language_code'] . " $currentlang'><a href='" . esc_url( $lang['url'] ) . "'>";
					$output .= "	<span class='language_flag'><img title='" . $lang['native_name'] . "' src='". esc_url( $lang['country_flag_url'] ) . "' alt='" . $lang['native_name'] . "' /></span>";
					$output .= "	<span class='language_native'>{$lang['native_name']}</span>";
					$output .= "	<span class='language_translated'>{$lang['translated_name']}</span>";
					$output .= "	<span class='language_code'>{$lang['language_code']}</span>";
					$output .= "</a></li>";
				}

				$output .= "</ul>";
			}

			echo $output;
		}

		/**
		 * Bugfix - ensure to display categories if no category or term is selected (may occur in translations )
		 * https://kriesi.at/support/topic/enfold-wpml-posts-without-category-nav_menu_item-shown-in-blog/#post-1338632
		 *
		 * @param array $query
		 * @param array $params
		 * @return array
		 */
		public function handler_avf_post_slide_query( array $query, array $params )
		{
			if( ! isset( $query['tax_query'] ) || count( $query['tax_query'] ) == 0 )
			{
				$query['post_type'] = get_post_types( array( 'public' => true ) );
			}

			return $query;
		}

		/**
		 * Check if Translation Manager is aactiv and for minimum version
		 * and disable theme option
		 *
		 * @since 4.8
		 * @param boolean $enabled
		 * @return boolean
		 */
		public function handler_avf_element_templates_enabled( $enabled )
		{
			if( ! $enabled )
			{
				return $enabled;
			}

			$tm_version = $this->translation_manager_version();

			if( false === $tm_version )
			{
				return false;
			}

			return version_compare( $tm_version, avia_WPML::MIN_TM_VERSION, '>=' );
		}

		/**
		 * Add a message that Translation Manager is required to work with WPML
		 *
		 * @since 4.8
		 * @param string $desc
		 * @param string $context
		 * @return string
		 */
		public function handler_avf_theme_options_heading_desc( $desc, $context )
		{
			if( $context != 'alb_element_templates_header' )
			{
				return $desc;
			}

			$tm  = '<a href="' . esc_url( 'https://wpml.org/documentation/translating-your-contents/' ) . '" target="_blank" rel="noopener noreferrer">';
			$tm .=		__( 'WPML Translation Management', 'avia_framework' );
			$tm .= '</a> ';
			$tm .= sprintf( __( 'minimum Version %s', 'avia_framework' ), avia_WPML::MIN_TM_VERSION );

			$disabled = '<br />' . __( 'Custom Elements will be disabled.', 'avia_framework' );

			$desc .= '<br /><br />';
			$desc .= '<h4 class="avia-wpml-header">' . __( 'Important Info for WPML:', 'avia_framework' ) . '</h4>';

			$tm_version = $this->translation_manager_version();

			if( false === $tm_version )
			{
				$desc .= sprintf( __( 'To translate and work with Custom Elements you must install and activate %s.', 'avia_framework' ), $tm );
				$desc .= $disabled;

				return $desc;
			}

			if( version_compare( $tm_version, avia_WPML::MIN_TM_VERSION, '<' ) )
			{
				$desc .= sprintf( __( 'To translate Custom Elements you need %s. Your current version is %s. Please update.', 'avia_framework' ), $tm, $tm_version );
				$desc .= $disabled;

				return $desc;
			}

			$desc .= __( 'To translate Custom Elements and Pages/Posts containing Custom Elements always use the Translation Editor. Do not switch the option &quot;Use WPMLs Translation Editor&quot; to off. This might break translations.', 'avia_framework' );

			$desc .= '<br />';

			$desc .= '<strong class="av-text-notice">';
			$desc .=	__( 'Whenever you create or make changes to a custom element it is important that you always translate it into all languages even if you only made changes to non translateable settings, because WPML must copy all your settings, translateable and non translateable, to the destination languages.', 'avia_framework' ) . ' ';
			$desc .=	__( 'Failing to do this will result in a broken layout.', 'avia_framework' );
			$desc .= '</strong><br />';

			return $desc;
		}

		/**
		 * Add WPML specific options
		 *
		 * @since 4.8
		 * @param string $context
		 */
		public function handler_ava_theme_options_elements_tab( $context = '' )
		{
			global $avia_elements;

			if( $context == 'avia_element_templates' )
			{
				$avia_elements[] = array(
						'slug'		=> 'avia_element_templates',
						'id'		=> 'alb_element_templates_management_wpml_start',
						'type'		=> 'visual_group_start',
						'nodescription' => true
					);

				$avia_elements[] = array(
						'slug'		=> 'avia_element_templates',
						'name'		=> __( 'WPML Specific Options', 'avia_framework' ),
	//					'desc'		=> __( 'WPML Specific Options', 'avia_framework' ),
						'id'		=> 'alb_element_templates_wpml_header',
						'type'		=> 'heading',
						'nodescription' => true
					);

				$avia_elements[] =	array(
						'slug'		=> 'avia_element_templates',
						'name'		=> __( 'Additional Translate Icon', 'avia_framework' ),
						'desc'		=> __( 'By default WPML allows translating Custom Elements from &quot;WPML-&gt; Translation Management&quot; dashboard. Check to add a translate icon to the shortcode buttons, which allows to skip use of the dashboard. Visible when using &quot;Edit Custom Elements&quot; button', 'avia_framework' ),
						'id'		=> 'custom_el_wpml_translate_icon',
						'type'		=> 'checkbox',
						'std'		=> false,
						'global'	=> true
					);


				$avia_elements[] = array(
						'slug'		=> 'avia_element_templates',
						'id'		=> 'alb_element_templates_management_wpml_end',
						'type'		=> 'visual_group_end',
						'nodescription' => true
					);

				return;
			}

			if( $context == 'header' )
			{
				$flag_element = array(
						'slug'		=> 'header',
						'name'		=> __( 'Enfold WPML Language Flags', 'avia_framework' ),
						'desc'		=> __( 'Choose if you want to hide the language flags that are added by default to the header menus.', 'avia_framework' ),
						'id'		=> 'wpml_header_lang_flags',
						'type'		=> 'select',
						'std'		=> '',
						'global'	=> true,
						'no_first'	=> true,
						'subtype'	=> array(
											__( 'Show the default language flags', 'avia_framework' )	=> '',
											__( 'Hide the default language flags', 'avia_framework' )	=> 'hide_all'
										)
					);

				$split = 0;
				foreach( $avia_elements as $index => $element )
				{
					$split ++;
					if( $element['slug'] != 'header' )
					{
						continue;
					}

					if( $element['id'] != 'phone' )
					{
						continue;
					}

					$first = array_slice( $avia_elements, 0, $split );
					$last = array_slice( $avia_elements, $split );

					$avia_elements = array_merge( $first, array( $flag_element ), $last );

					return;
				}

				//	fallback, add at end
				$avia_elements[] = $flag_element;

				return;
			}

			if( $context == 'avia' )
			{
				$desc  = '<ul class="avia-opt-desc">';
				$desc .=	'<li>';
				$desc .=		__( 'By default all options are copied to a new added language', 'avia_framework' );
				$desc .=	'</li>';
				$desc .=	'<li>';
				$desc .=		'<strong>&quot;' . __( 'Global Setting', 'avia_framework' ) . '&quot;: </strong>';
				$desc .=		__( 'Option settings are always used for all languages.', 'avia_framework' );
				$desc .=	'</li>';
				$desc .=	'<li>';
				$desc .=		'<strong>&quot;' . __( 'Global Layout', 'avia_framework' ) . '&quot;: </strong>';
				$desc .=		__( 'If checkbox below is selected, option settings are used for all languages and also copied (!!!) to all languages. Deselecting will stop this behaviour and options can then be set for each language separately.', 'avia_framework' );
				$desc .=	'</li>';
				$desc .=	'<li>';
				$desc .=		__( 'All other options have to be set for each language separately.', 'avia_framework' );
				$desc .=	'</li>';
				$desc .= '</ul>';
				$desc .= '<strong>' . __( 'Please reload the page after saving theme options whenever you change the checkbox below to update description text for the options.', 'avia_framework' ). '</strong>';

				$avia_elements[] = array(
						'slug'			=> 'avia',
						'type'			=> 'visual_group_start',
						'id'			=> 'avia_wpml_global_start',
						'nodescription'	=> true
					);

				$avia_elements[] = array(
						'slug'			=> 'avia',
						'name'			=> __( 'WPML Options Management', 'avia_framework' ),
						'desc'			=> $desc,
						'id'			=> 'wpml_avia_global_header',
						'type'			=> 'heading',
						'std'			=> '',
						'nodescription' => true
					);

				$avia_elements[] =	array(
						'slug'		=> 'avia',
						'name'		=> __( 'Keep Global Layout Options Synchronised', 'avia_framework' ),
						'desc'		=> __( 'Select to synchronise options marked with global layout for all languages. This will also override any settings in the other languages !!', 'avia_framework' ),
						'id'		=> 'wpml_sync_layout',
						'type'		=> 'checkbox',
						'std'		=> false,
						'global'	=> true
					);

				$avia_elements[] = array(
						'slug'          => 'avia',
						'type'          => 'visual_group_end',
						'id'            => 'avia_wpml_global_end',
						'nodescription' => true
					);

				return;
			}

		}

		/**
		 * Translate CET to current language. Returns untranslated if no translation exists.
		 *
		 * @since 4.8
		 * @param string|int $element_template_id
		 * @return string|int
		 */
		public function handler_avf_custom_element_template_id( $element_template_id = '' )
		{
			if( Avia_Element_Templates()->element_templates_enabled() && ! empty( $element_template_id ) && is_numeric( $element_template_id ) )
			{
				$element_template_id = avia_wpml_translate_object_ids( $element_template_id, Avia_Element_Templates()->get_post_type() );
			}

			return $element_template_id;
		}

		/**
		 * Add translate action button and link
		 *
		 * @since 4.8
		 * @param string $button
		 * @param int $element_id
		 * @return string
		 */
		public function handler_avf_cet_additional_sc_action_btn( $button, $element_id )
		{
			global $sitepress;

			if( avia_get_option( 'custom_el_wpml_translate_icon' ) != 'custom_el_wpml_translate_icon' )
			{
				return $button;
			}

			$post_link_factory = new WPML_TM_Post_Link_Factory( $sitepress );
			$post_edit_link = $post_link_factory->edit_link_anchor( $element_id, __( 'Edit', 'avia_framework' ) );

			$match = array();

			$result = preg_match( '/href="([^"]*)"/', $post_edit_link, $match );

			if( ! $result || ! isset( $match[1] ) || empty( $match[1] ) )
			{
				return $button;
			}

			$translate = av_backend_icon( array( 'args' => array( 'icon' => 'ue84f', 'font' => 'entypo-fontello' ) ) );
			$link = 'data-external_link="' . $match[1] . '"';

			$button .= '<div class="element-sc-action-button element-wpml-translate element-custom-action" title="' . esc_html__( 'Translate Custom Element with WPML', 'avia_framework' ) . '" ' . $link . '><span>' . $translate['display_char'] . '</span></div>';

			return $button;
		}

		/**
		 * Change ALB metabox title
		 *
		 * @since 4.8
		 * @param string $prefix
		 * @return string
		 */
		public function handler_avf_alb_metabox_title_prefix_cet( $prefix )
		{
			return __( 'Translate Element Template:', 'avia_framework' );
		}

		/**
		 * Remove the 'View' link from translation jobs because CET don't have a link to 'View' them.
		 *
		 * @since 4.8
		 * @param string $link   The complete link.
		 * @param string $text   The text to link.
		 * @param object $job    The corresponding translation job.
		 * @param string $prefix The prefix of the element type.
		 * @param string $type   The element type.
		 * @return string
		 */
		public function handler_wpml_document_view_item_link( $link, $text, $job, $prefix, $type )
		{
			if( $type == Avia_Element_Templates()->get_post_type() )
			{
				$link = '';
			}

			return $link;
		}

		/**
		 * Remove the 'View' link from translation jobs because CET don't have a link to 'View' them.
		 *
		 * @since 4.8
		 * @param string $link					The complete link.
		 * @param string $text					The text to link.
		 * @param object $current_document		The document to translate.
		 * @param string $prefix				The prefix of the element type.
		 * @param string $type					The element type.
		 * @return string
		 */
		public function handler_wpml_document_edit_item_link( $link, $text, $current_document, $prefix, $type )
		{
			if( $type != Avia_Element_Templates()->get_post_type() )
			{
				return $link;
			}

			$info = __( 'Translate', 'avia_framework' );

			$item = '';

			$terms = get_the_terms( $current_document->ID, Avia_Element_Templates()->get_taxonomy() );

			if( ! is_array( $terms ) || empty( $terms ) )
			{
				$item .= ' ' .  __( 'Unknown Custom Element Type', 'avia_framework' );
			}
			else
			{
				$item .= ' ' . sprintf( __( '%s', 'avia_framework' ), $terms[0]->name );
			}

			$content = Avia_Builder()->get_posts_alb_content( $current_document->ID );
			if( ! empty( trim( $content) ) )
			{
				$sc_array = Avia_Element_Templates()->get_element_template_info_from_content( $content );

				if( array_key_exists( 'select_element_template', $sc_array[0]['attr'] ) && ( 'item' == $sc_array[0]['attr']['select_element_template'] ) )
				{
					$term = get_term_by( 'slug', $sc_array[0]['shortcode'], Avia_Element_Templates()->get_taxonomy() );
					if( $term instanceof WP_Term )
					{
						$item .= ', ' . sprintf( __( 'a subitem of %s', 'avia_framework' ), $term->name );
					}
					else
					{
						$item .= ', ' .  __( 'subitem of an unknown Custom Element type', 'avia_framework' );
					}
				}
			}

			if( ! empty( $item ) )
			{
				$info .= ' (= ' . $item . ')';
			}

			return str_replace( $text, $info, $link );
		}

		/**
		 * Add a button to translate modal subitem
		 *
		 * @since 4.8
		 */
		public function handler_icl_post_languages_options_after()
		{
			global $post, $sitepress;

			if( ! $post instanceof WP_Post )
			{
				if( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'wpml_get_meta_boxes_html' || ! isset( $_REQUEST['post_id'] ) )
				{
					return;
				}

				$result = get_post( $_REQUEST['post_id'] );

				if( ! $result instanceof WP_Post )
				{
					return;
				}

				$post = $result;
			}

			if( ! Avia_Element_Templates()->element_templates_enabled() )
			{
				return;
			}

			//	not editing a custom element template
			if( $post->post_type != Avia_Element_Templates()->get_post_type() )
			{
				return;
			}

			if( Avia_Element_Templates()->subitem_custom_element_handling() != 'first' )
			{
				return;
			}

			//	add a button to allow to translate the first subitem
			$sc_array = Avia_Element_Templates()->get_element_template_info_from_content( $post->post_content );

			$shortcode = $sc_array[0]['shortcode'];

			if( ! isset( Avia_Builder()->shortcode[ $shortcode ] ) )
			{
				return;
			}

			//	already a subitem shortcode
			if( $shortcode != $sc_array[0]['template_sc'] )
			{
				return;
			}

			$sc = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $shortcode ] ];

			if( ! Avia_Element_Templates()->is_editable_modal_group_element( $sc ) )
			{
				return;
			}

			if( ! isset( $sc_array[0]['template_attr']['one_element_template'] ) || empty( $sc_array[0]['template_attr']['one_element_template'] ) )
			{
				return;
			}

			$link_text = sprintf( __( 'Translate: %s', 'avia_framework' ), $sc->config['name_item'] );

			$post_link_factory = new WPML_TM_Post_Link_Factory( $sitepress );
			$post_edit_link = $post_link_factory->edit_link_anchor( $sc_array[0]['template_attr']['one_element_template'], $link_text );

			$insert = '<a class="button button-primary button-large avia-translate-cet-subitem" target="_blank" rel="noopener noreferrer" ';
			$button = str_replace( '<a ', $insert, $post_edit_link );

			echo $button;
		}

		/**
		 * Translation manager specific:
		 *
		 * If post is built with ALB ( based on $post_id from original post ) strip locked strings from the shortcode content and return the new content
		 * Locked content does not need translation.
		 * Is called from "normal pages/posts/.." and from "custom elements management"
		 *
		 * @since 4.8
		 * @param string $post_content
		 * @param int $post_id
		 * @return tystringpe
		 */
		public function handler_wpml_pb_shortcode_content_for_translation( $post_content, $post_id )
		{
			if( ! Avia_Element_Templates()->element_templates_enabled() )
			{
				return $post_content;
			}

			$edit_cet = false;
			$update = false;
			$post = get_post( $post_id );

			if( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'avia_alb_element_template_cpt_actions', 'avia_alb_element_template_update_content' ) ) )
			{
				//	Fixes a problem in translation job calling filter before post meta is updated -> results in not recognizing CET has changed
				$post_content = $post instanceof WP_Post ? $post->post_content : $post_content;
				$edit_cet = true;
				$update = true;
			}

			if( Avia_Builder()->get_alb_builder_status( $post_id ) == 'active' )
			{
				$edit_cet = $post->post_type == Avia_Element_Templates()->get_post_type();
				$update = true;
			}

			if( ! $update )
			{
				return $post_content;
			}

			$content = $this->cet_remove_locked_values( wp_unslash( $post_content ), $post_id, $edit_cet );

			return $content;
		}

		/**
		 * @since ???					moved from config-wpml\config.php
		 * @since 4.8
		 */
		public function handler_wp_enqueue_scripts()
		{
			$version = avia_get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			wp_enqueue_style( 'avia-wpml', AVIA_BASE_URL . "config-wpml/wpml-mod{$min_css}.css", array(), $version );
			wp_enqueue_script( 'avia-wpml-script', AVIA_BASE_URL . "config-wpml/wpml-mod{$min_js}.js", array( 'jquery' ), $version );
		}

		/**
		 * Fix provided by WPML comp. team https://wpml.org/forums/topic/issue-with-cookie-pop-up/
		 * Cookie Consent Box pops up when language is changed but without changing anything.
		 *
		 * Modified fix to invalidate $cookie_contents if a content part in any language changes.
		 *
		 * @since 4.8.2
		 * @param string $cookie_contents
		 * @return string
		 */
		public function handler_avf_cookie_consent_for_md5( $cookie_contents )
		{
			$new_cookie_contents = '';

			$messages = $this->wpml_get_options( 'cookie_content' );
			if( is_array( $messages ) )
			{
				foreach( $messages as $message_lang )
				{
					$new_cookie_contents .= do_shortcode( $message_lang );
				}
			}

			$buttons = $this->wpml_get_options( 'msg_bar_buttons' );
			if( ! is_array( $buttons ) )
			{
				$buttons = array();
			}

			foreach( $buttons as $buttons_lang )
			{
				if( is_array( $buttons_lang ) )
				{
					foreach( $buttons_lang as $button )
					{
						if( isset( $button['msg_bar_button_label'] ) )
						{
							$new_cookie_contents .= $button['msg_bar_button_label'];
						}
					}
				}
			}

			return $new_cookie_contents;
		}

		/**
		 *
		 * @since ????			moved from config-wpml\config.php
		 * @param string $prepare_sql
		 * @param string $table_name
		 * @param int $limit
		 * @param array $element
		 * @return string
		 */
		function handler_avf_dropdown_post_query( $prepare_sql, $table_name, $limit, $element )
		{
			global $wpdb;

			$wpml_lang = ICL_LANGUAGE_CODE;

			$wpml_join = " INNER JOIN {$wpdb->prefix}icl_translations ON {$table_name}.ID = {$wpdb->prefix}icl_translations.element_id ";
			$wpml_where = " {$wpdb->prefix}icl_translations.language_code LIKE '{$wpml_lang}' AND ";

			$prepare_sql = "SELECT distinct ID, post_title FROM {$table_name} {$wpml_join} WHERE {$wpml_where} post_status = 'publish' AND post_type = '{$element['subtype']}' ORDER BY post_title ASC LIMIT {$limit}";

			return $prepare_sql;
		}

		/**
		 * Checks if Translation Manager Plugin is activated and returns the version
		 *
		 * @since 4.8
		 * @return boolean|string
		 */
		public function translation_manager_version()
		{
			return defined( 'WPML_TM_VERSION' ) ? WPML_TM_VERSION : false;
		}

		/**
		 * check if we are using the default language
		 *
		 * @since ????			moved from config-wpml\config.php
		 * @since 4.8
		 * @return boolean
		 */
		public function is_default_language()
		{
			global $avia_config;

			$wpml_options = $avia_config['wpml']['settings'];

			if( ( isset( $wpml_options['default_language'] ) && $wpml_options['default_language'] != ICL_LANGUAGE_CODE ) && 'all' != ICL_LANGUAGE_CODE && '' != ICL_LANGUAGE_CODE )
			{
				return false;
			}

			return true;
		}

		/**
		 * Return if sync of global layout options is enabled
		 *
		 * @since 5.0
		 * @param array|null $options			current options array when updating options page
		 * @return boolean
		 */
		public function is_global_layout_enabled( $options = null )
		{
			if( ! is_array( $options ) )
			{
				return avia_get_option( 'wpml_sync_layout' ) == 'wpml_sync_layout';
			}

			return isset( $options['avia']['wpml_sync_layout'] ) && 'wpml_sync_layout' == $options['avia']['wpml_sync_layout'];
		}

		/**
		 * Get an option from the database based on the option key passed.
		 * Other then the default avia_get_option function this one retrieves all language entries and passes them as array:
		 *
		 *		language => options value
		 *
		 * In case of multiple options pages (see $option_set) the value is fetched from the first page that contains the key.
		 * So make sure to have unique id's across all option pages
		 *
		 * @since ???					moved from config-wpml\config.php
		 * @since 4.8					support for multiple options pages $option_set
		 * @param string $option_key	'' to get all options
		 * @return array
		 */
		public function wpml_get_options( $option_key = '' )
		{
			global $avia, $avia_config;

			if( ! isset( $avia->wpml ) )
			{
				$avia->createProperty( 'wpml', [] );

				$key = isset( $avia->base_data['prefix_origin'] ) ? $avia->base_data['prefix_origin'] : $avia->base_data['prefix'];
				$key = 'avia_options_' . avia_backend_safe_string( $key );
				$wpml_options = $avia_config['wpml']['settings'];

				$this->option_langs = array();
				if( is_array( $avia_config['wpml']['lang'] ) )
				{
					foreach( $avia_config['wpml']['lang'] as $lang => $values )
					{
						if( $wpml_options['default_language'] != $lang )
						{
							$this->option_langs[ $lang ] = $key . '_' . $lang;
						}
						else
						{
							$this->option_langs[ $lang ] = $key;
						}

						$avia->wpml[ $lang ] = get_option( $this->option_langs[ $lang ] );
					}
				}
			}

			if( empty( $option_key ) )
			{
				return $avia->wpml;
			}

			$option = array();

			if( isset( $avia->wpml ) )
			{
				foreach( $avia->wpml as $language => $option_set )
				{
					$value = false;

					if( is_array( $option_set ) )
					{
						foreach( $option_set as $set => $options )
						{
							if( array_key_exists( $option_key, $options ) )
							{
								$value = $options[ $option_key ];
								break;
							}
						}
					}

					$option[ $language ] = $value;
				}
			}

			return $option;
		}

		/**
		 * Removes (clears) locked translateable attribute and content from shortcode by setting to "".
		 * Translation Manager removes empty attributes from translation.
		 *
		 * @since 4.8
		 * @param string $content
		 * @param int $post_id
		 * @param boolean $edit_cet
		 * @param boolean $clear_attributes
		 * @return string
		 */
		protected function cet_remove_locked_values( $content, $post_id, $edit_cet, $clear_attributes = false )
		{
			$pattern = empty( ShortcodeHelper::$pattern) ? ShortcodeHelper::build_pattern() : ShortcodeHelper::$pattern;

			$matches = array();
			preg_match_all( "/$pattern/s", $content , $matches, PREG_OFFSET_CAPTURE );

			$cnt_sc = count( $matches[0] ) - 1;

			if( $cnt_sc < 0 )
			{
				return $content;
			}

			//	scan backwards so we can replace strings in content
			for( $i = $cnt_sc; $i >= 0;  $i-- )
			{
				$attr = shortcode_parse_atts( $matches[3][ $i ][0] );
				$shortcodename = $matches[2][ $i ][0];
				$sc_content = $matches[5][ $i ][0];

				if( ! empty( $sc_content ) )
				{
					$clear = false;

					if( $edit_cet && isset( $attr['select_element_template'] ) && $attr['select_element_template'] != 'item' )
					{
						if( 'first' == Avia_Element_Templates()->subitem_custom_element_handling() )
						{
							$clear = true;
						}
					}

					$sc_content = $this->cet_remove_locked_values( $sc_content, $post_id, $edit_cet, $clear );
				}

				$shortcode_class = null;
				$self_closing = false;

				//	check for subitem shortcode or fallback to ignore shortcode
				if( isset( Avia_Builder()->shortcode[ $shortcodename ] ) )
				{
					$shortcode_class = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $shortcodename ] ];
					$default = $shortcode_class->get_default_sc_args();
					$self_closing = $shortcode_class->is_self_closing();
				}
				else if( isset( Avia_Builder()->shortcode_parents[ $shortcodename ] ) && ! empty( Avia_Builder()->shortcode_parents[ $shortcodename ] ) )
				{
					$parent = Avia_Builder()->shortcode_parents[ $shortcodename ][0];
					if( isset( Avia_Builder()->shortcode[ $parent ] ) )
					{
						$shortcode_class = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $parent ] ];
						$default = $shortcode_class->get_default_modal_group_args();
						$self_closing = $shortcode_class->is_nested_self_closing( $shortcodename );
					}
				}

				if( $edit_cet && isset( $attr['select_element_template'] ) && $attr['select_element_template'] == 'item' )
				{
					if( 'first' == Avia_Element_Templates()->subitem_custom_element_handling() )
					{
						$clear_attributes = true;
					}
				}

				$atts = $attr;
				$locked = array();

				if( $shortcode_class instanceof aviaShortcodeTemplate )
				{
					Avia_Element_Templates()->set_locked_attributes( $atts, $shortcode_class, $shortcodename, $default, $locked, $sc_content );
				}

				/**
				 * As nested shortcodes could have locked values we must recreate and update even if outer shortcode has no locked values !!!
				 * ==========================================================================================================================
				 */
				foreach( $locked as $key => $value )
				{
					$attr[ $key ] = '';
				}

				unset( $attr['content'] );

				if( $edit_cet && $clear_attributes )
				{
					$this->clear_attributes_for_translation( $attr, $shortcodename );
				}

				if( $shortcode_class instanceof aviaShortcodeTemplate )
				{
					$inner_content = ! $self_closing ? $sc_content : null;
				}
				else
				{
					$inner_content = ( false !== strpos( $matches[0][ $i ][0], "[/{$shortcodename}]" ) ) ? $sc_content : null;
				}

				$new_sc = trim( ShortcodeHelper::create_shortcode_by_array( $shortcodename, $inner_content, $attr ) );

				$start = $matches[0][ $i ][1];
				$len = strlen( $matches[0][ $i ][0] );

				$content = substr_replace( $content, $new_sc, $start, $len );
			}

			return $content;
		}

		/**
		 * Remove all editable attribute values for translation as they are not needed
		 *
		 * @since 4.8
		 * @param array $attr
		 * @param string $shortcodename
		 */
		protected function clear_attributes_for_translation( array &$attr, $shortcodename )
		{
			$wpml = $this->wpml_translateable_attributes();

			if( ! isset( $wpml[ $shortcodename ] ) || empty( $wpml[ $shortcodename ] ) )
			{
				return;
			}

			$translate = $wpml[ $shortcodename ];

			foreach( $attr as $attr_name => &$value )
			{
				if( in_array( $attr_name, $translate ) )
				{
					$value = '';
				}
			}

			unset( $value );
		}

		/**
		 * Initialises and returns the array of translateable attributes for all shortcodes
		 *
		 * @since 4.8
		 * @return array
		 */
		public function wpml_translateable_attributes()
		{
			if( ! empty( $this->wpml_sc_config ) )
			{
				return $this->wpml_sc_config;
			}

			$wpml_option = get_option( 'icl_st_settings' );

			if( ! is_array( $wpml_option ) || ! isset( $wpml_option['pb_shortcode'] ) || ! is_array( $wpml_option['pb_shortcode'] ) )
			{
				return $this->wpml_sc_config;
			}

			foreach( $wpml_option['pb_shortcode'] as $info )
			{
				$sc = isset( $info['tag'] ) && isset( $info['tag']['value'] ) ? $info['tag']['value'] : '';

				if( empty( $sc ) )
				{
					continue;
				}

				$this->wpml_sc_config[ $sc ] = array();

				$atts = isset( $info['attributes'] ) && is_array( $info['attributes'] ) ? $info['attributes'] : array();

				foreach( $atts as $att )
				{
					if( isset( $att['value'] ) && ! empty( $att['value'] ) )
					{
						$this->wpml_sc_config[ $sc ][] = $att['value'];
					}
				}
			}

			return $this->wpml_sc_config;
		}

		/**
		 * Translate tag values for attachments (av-helper-masonry.php)
		 *
		 * @since 4.9.2.2			modified version becuase get_term_by returns translated term object
		 * @param array $value
		 * @return array
		 */
	    function handler_avf_ratio_check_by_tag_values( $value )
	    {
	        if( ! empty( $value ) && is_array( $value ) )
	        {
	            foreach( $value as $key => $data )
	            {
	                $orig_term = get_term_by( 'slug', $data, 'post_tag' );
	                if( false === $orig_term )
					{
						continue;
					}

					$value[ $key ] = $orig_term->slug;

					/**
					 * Fallback filter in case old WPML versions do not return translated term objects
					 *
					 * @since 4.9.2.2
					 * @param boolean $needs_translation
					 * @param array $value
					 * @return false|mixed			anything not false will translate
					 */
					$needs_translation = apply_filters( 'avf_wpml_ratio_check_by_tag_needs_translate', false, $value );

					if( $needs_translation === false )
					{
						continue;
					}

					//	icl_object_id deprecated since 3.2 - backward comp only
					$translated_id = function_exists( 'wpml_object_id_filter' ) ? wpml_object_id_filter( $orig_term->term_id, 'post_tag', true ) : icl_object_id( $orig_term->term_id, 'post_tag', true );
					if( is_null( $translated_id ) || ( ! is_numeric( $translated_id ) ) )
					{
						continue;
					}

					if( $orig_term->term_id == $translated_id )
					{
						continue;
					}

					$translated_term = get_term_by( 'id', $translated_id, 'post_tag' );
					if( false === $translated_term )
					{
						continue;
					}

					$value[ $key ] = $translated_term->slug;
	            }
	        }

	        return $value;
	    }
	}

	/**
	 * Returns the main instance of aviaWPML to prevent the need to use globals
	 *
	 * @since 4.8
	 * @return avia_WPML
	 */
	function Avia_WPML()
	{
		return avia_WPML::instance();
	}

	/**
	 * Activate filter and action hooks
	 */
	Avia_WPML();

}
