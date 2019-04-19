<?php

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


/* - WPML compatibility - */
if(defined('ICL_SITEPRESS_VERSION') && defined('ICL_LANGUAGE_CODE'))
{
	add_filter( 'avia_filter_base_data' , 'avia_wpml_options_language' );
	add_filter( 'avia_filter_backend_page_title' , 'avia_wpml_backend_page_title' );
	//add_action( 'init', 'avia_wpml_register_post_type_permalink', 20);
	add_action( 'avia_action_before_framework_init', 'avia_wpml_get_languages');
	//add_filter( 'icl_ls_languages' , 'avia_wpml_url_filter' );
	add_action( 'init', 'avia_wpml_backend_language_switch');
	//add_action( 'avia_wpml_backend_language_switch', 'avia_default_dynamics');
	add_action( 'avia_wpml_backend_language_switch', 'avia_wpml_copy_options');
	add_action( 'wp_enqueue_scripts', 'avia_wpml_register_assets' );
	
    add_filter( 'avf_execute_avia_meta_header', '__return_true', 10, 1);



	/*
	* This function makes it possible that all backend options can be saved several times
	* for different languages. It appends a language string to the key of the options entry
	* that is saved to the wordpress database.
	*
	* Since the Avia Framework only uses a single option array for the whole backend and
	* then serializes that array and saves it to a single database entry this is a very
	* easy and flexible method to setup your site in any way you want with muliple
	* languages, layouts, logos, dynamic templates, etc for each language
	*/

	if(!function_exists('avia_wpml_options_language'))
	{
		function avia_wpml_options_language($base_data)
		{
			global $avia_config;
			$wpml_options = $avia_config['wpml']['settings'];

			if((isset($wpml_options['default_language']) && $wpml_options['default_language'] != ICL_LANGUAGE_CODE) && 'all' != ICL_LANGUAGE_CODE && "" != ICL_LANGUAGE_CODE)
			{
				$base_data['prefix_origin'] = $base_data['prefix'];
				$base_data['prefix'] = $base_data['prefix'] . "_" . ICL_LANGUAGE_CODE;
			}

			return $base_data;
		}
	}

	/*check if we are using the default language*/
	if(!function_exists('avia_wpml_is_default_language'))
	{
		function avia_wpml_is_default_language()
		{
			global $avia_config;
			$wpml_options = $avia_config['wpml']['settings'];

			if((isset($wpml_options['default_language']) && $wpml_options['default_language'] != ICL_LANGUAGE_CODE) && 'all' != ICL_LANGUAGE_CODE && "" != ICL_LANGUAGE_CODE)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}

	/*fetch some default data necessary for the framework*/
	if(!function_exists('avia_wpml_get_languages'))
	{
		function avia_wpml_get_languages()
		{
			global $sitepress, $avia_config;
			$avia_config['wpml']['lang'] 		= $sitepress->get_active_languages();
			$avia_config['wpml']['settings'] 	= get_option('icl_sitepress_settings');
		}
	}

	/*language switch hook for the backend*/
	if(!function_exists('avia_wpml_backend_language_switch'))
	{
		function avia_wpml_backend_language_switch()
		{
			if(isset($_GET['lang']) && is_admin())
			{
				do_action('avia_wpml_backend_language_switch');
			}
		}
	}



	/*
	  get an option from the database based on the option key passed.
	  other then with the default avia_get_option function this one retrieves all language entries and passes them as array
	*/

	if(!function_exists('avia_wpml_get_options'))
	{
		function avia_wpml_get_options($option_key)
		{
			global $avia, $avia_config;

			if(!isset($avia->wpml))
			{
				$key 			= isset($avia->base_data['prefix_origin']) ? $avia->base_data['prefix_origin'] : $avia->base_data['prefix'];
				$key 			= 'avia_options_'.avia_backend_safe_string( $key );
				$wpml_options 	= $avia_config['wpml']['settings'];

				$key_array = array();
				if(is_array($avia_config['wpml']['lang'] ))
				{
					foreach($avia_config['wpml']['lang'] as $lang => $values)
					{
						if($wpml_options['default_language'] != $lang)
						{
							$key_array[$lang] = $key ."_".$lang;
						}
						else
						{
							$key_array[$lang] = $key;
						}

						$avia->wpml[$lang] = get_option($key_array[$lang]);
					}
				}
			}

			$option = array();

			if(isset($avia->wpml))
			{
				foreach($avia->wpml as $language => $option_set)
				{
					if(isset($option_set['avia']) && isset($option_set['avia'][$option_key]))
					{
						$option[$language] = $option_set['avia'][$option_key];
					}
					else
					{
						$option[$language] = false;
					}
				}
			}
			return $option;
		}
	}

	/*
	* Filters the menu entry in the backend and displays the language in addition to the theme name
	*/
	if(!function_exists('avia_wpml_backend_page_title'))
	{
		function avia_wpml_backend_page_title($title)
		{
			if(ICL_LANGUAGE_CODE == "") return $title;

			$append = "";
			if('all' != ICL_LANGUAGE_CODE)
			{
				$append = " (".strtoupper( ICL_LANGUAGE_CODE ).")";
			}
			else
			{
				global $avia_config;

				$wpml_options 	= $avia_config['wpml']['settings'];
				$append 		= " (".strtoupper( $wpml_options['default_language'] ).")";
			}
			return $title . $append;
		}
	}

	/*
	* Creates an additional dynamic slug rewrite rule for custom categories
	*/
	if(!function_exists('avia_wpml_register_post_type_permalink'))
	{
		function avia_wpml_register_post_type_permalink() {

			global $wp_post_types, $wp_rewrite, $wp, $avia_config;

			if(!isset($avia_config['custom_post'])) return false;

			$slug_array = avia_wpml_get_options('portfolio-slug');

			foreach($avia_config['wpml']['lang'] as $lang => $values)
			{
				foreach($avia_config['custom_post'] as $post_type => $arguments)
				{
					$args = (object) $arguments['args'];
					$args->rewrite['slug'] = $slug_array[$lang];
					$args->permalink_epmask = EP_PERMALINK;
					$post_type = sanitize_key($post_type);

					if ( false !== $args->rewrite && ( is_admin() || '' != get_option('permalink_structure') ) )
					{
						$wp_rewrite->add_permastruct($post_type."_$lang", "{$args->rewrite['slug']}/%$post_type%", $args->rewrite['with_front'], $args->permalink_epmask);
					}
				}
			}
		}
	}

	/*
	* Filters the links generated for the language switcher in case a user is viewing a single portfolio entry and changes the portfolio slug if necessary
	*/
	if(!function_exists('avia_wpml_url_filter'))
	{
		function avia_wpml_url_filter($lang)
		{
			$post_type	= get_post_type();

			if("portfolio" == $post_type)
			{
				$slug 		= avia_wpml_get_options('portfolio-slug');

				$current 	= isset($slug[ICL_LANGUAGE_CODE]) ? $slug[ICL_LANGUAGE_CODE] : "";
				foreach ($lang as $key => $options)
				{
					if(isset($options['url']) && $current != "" && $current != $slug[$key] && "" != $slug[$key])
					{
						$lang[$key]['url'] = str_replace("/".$current."/", "/".$slug[$key]."/", $lang[$key]['url']);
					}
				}
			}
			return $lang;
		}
	}


	/*
	* register css styles
	*/
	if(!function_exists('avia_wpml_register_assets'))
	{
		function avia_wpml_register_assets()
		{
			$theme = wp_get_theme(); 
			$version = ( false === $theme->parent() ) ? $theme->get( 'Version' ) : $theme->parent()->get( 'Version' );
			
			wp_enqueue_style( 'avia-wpml', AVIA_BASE_URL.'config-wpml/wpml-mod.css', array(), $version );
			wp_enqueue_script( 'avia-wpml-script', AVIA_BASE_URL.'config-wpml/wpml-mod.js', array( 'jquery' ), $version );
		}
	}

	/*
	* styleswitcher for the avia framework
	*/
	if( ! function_exists( 'avia_wpml_language_switch' ) )
	{
		add_action( 'avia_meta_header', 'avia_wpml_language_switch', 10 );
		add_action( 'ava_main_header_sidebar', 'avia_wpml_language_switch', 10 );

		function avia_wpml_language_switch()
		{
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
	}

	/*
	* copy the default option set to the current language if no options set for this language is available yet
	*/
	if(!function_exists('avia_wpml_copy_options'))
	{
		function avia_wpml_copy_options()
		{
			global $avia, $avia_config;

			$key 			= isset($avia->base_data['prefix_origin']) ? $avia->base_data['prefix_origin'] : $avia->base_data['prefix'];
			$original_key 	= 'avia_options_'.avia_backend_safe_string( $key );
			$language_key	= 'avia_options_'.avia_backend_safe_string( $avia->base_data['prefix'] );

			if($original_key !== $language_key)
			{
				$lang_set = get_option($language_key);

				if(empty($lang_set))
				{
					$lang_set = get_option($original_key);
					update_option($language_key, $lang_set);

					wp_redirect( $_SERVER['REQUEST_URI'] );
					exit();
				}
			}
		}
	}




	//Add all the necessary filters. There are a LOT of WordPress functions, and you may need to add more filters for your site.
	if(!function_exists('avia_wpml_correct_domain_in_url'))
	{
		// some installs require this fix: https://wpml.org/errata/enfold-theme-styles-not-loading-with-different-domains/
		if (!is_admin()) {
		
			add_filter ('home_url', 'avia_wpml_correct_domain_in_url');
			add_filter ('site_url', 'avia_wpml_correct_domain_in_url');
			add_filter ('get_option_siteurl', 'avia_wpml_correct_domain_in_url');
			add_filter ('stylesheet_directory_uri', 'avia_wpml_correct_domain_in_url');
			add_filter ('template_directory_uri', 'avia_wpml_correct_domain_in_url');
			add_filter ('post_thumbnail_html', 'avia_wpml_correct_domain_in_url');
			add_filter ('plugins_url', 'avia_wpml_correct_domain_in_url');
			add_filter ('admin_url', 'avia_wpml_correct_domain_in_url');
			add_filter ('wp_get_attachment_url', 'avia_wpml_correct_domain_in_url');

		}

		/**
		* Changes the domain for a URL so it has the correct domain for the current language
		* Designed to be used by various filters
		*
		* @param string $url
		* @return string
		*/
		function avia_wpml_correct_domain_in_url($url)
		{
			//  icl_get_home_url was deprecaated since 3.2
		    if( function_exists( 'wpml_get_home_url_filter' ) || function_exists( 'icl_get_home_url' ) )
		    {
		        // Use the language switcher object, because that contains WPML settings, and it's available globally
		        global $icl_language_switcher, $avia_config;

		        // Only make the change if we're using the languages-per-domain option
		        if (isset($icl_language_switcher->settings['language_negotiation_type']) && $icl_language_switcher->settings['language_negotiation_type'] == 2)
		        {
					if(!avia_wpml_is_default_language())
					{
						$wpml_home = function_exists( 'wpml_get_home_url_filter' ) ? wpml_get_home_url_filter() : icl_get_home_url();
		            	return str_replace( untrailingslashit( get_option( 'home' ) ), untrailingslashit( $wpml_home ), $url );
		    		}
		    	}
		    }
		    return $url;
		}
	}


	if(!function_exists('avia_append_language_code_to_ajax_url'))
	{
		add_filter ('avia_ajax_url_filter', 'avia_append_language_code_to_ajax_url');

		function avia_append_language_code_to_ajax_url($url)
		{
			//conert url in case we are using different domain
			$url = avia_wpml_correct_domain_in_url($url);

			//after converting the url in case it was necessary also append the language code
			$url .= '?lang='.ICL_LANGUAGE_CODE;

			return $url;
		}
	}






	if(!function_exists('avia_backend_language_switch'))
	{
	   add_filter( 'avia_options_page_header', 'avia_backend_language_switch' );

		function avia_backend_language_switch()
		{
		    $current_page = basename($_SERVER['SCRIPT_NAME']);
		    $query = '?';
            if(!empty($_SERVER['QUERY_STRING']))
            {
                $query .= $_SERVER['QUERY_STRING'] . '&';
            }

			// icl_get_languages deprecated since 3.2
			$languages = function_exists( 'wpml_get_active_languages_filter' ) ? wpml_get_active_languages_filter( '', 'skip_missing=0&orderby=id' ) : icl_get_languages( 'skip_missing=0&orderby=id' );
			$output = "";

			if(is_array($languages) && !empty($languages))
			{
				$output .= "<ul class='avia_wpml_language_switch'>";
			    $output .= "<li><span class='avia_cur_lang_edit'>".__('Editing:', 'avia_framework')."</span><span class='avia_cur_lang'><img title='".$languages[ICL_LANGUAGE_CODE]['native_name']."' alt='".$languages[ICL_LANGUAGE_CODE]['native_name']."' src='".$languages[ICL_LANGUAGE_CODE]['country_flag_url']."' />";
			    $output .= ICL_LANGUAGE_NAME_EN." (".__('Change', 'avia_framework').")</span>";
			    unset($languages[ICL_LANGUAGE_CODE]);
				$output .= "<ul class='avia_sublanguages'>";

				foreach($languages as $lang)
				{
	                $linkurl = admin_url($current_page . $query .'lang=' . $lang['language_code']);

					$output .= "<li class='language_".$lang['language_code']."'><a href='".$linkurl."'>";
					$output .= "	<span class='language_flag'><img title='".$lang['native_name']."' src='". esc_url( $lang['country_flag_url'] ) ."' alt='".$lang['native_name']."' /></span>";
					$output .= "	<span class='language_native'>".$lang['native_name']."</span>";
					$output .= "</a></li>";
				}

				$output .= "</ul></li></ul>";
				$output .="
				<style type='text/css'>
				.avia_wpml_language_switch {
                    z-index: 100;
                    padding: 10px;
                    position: absolute;
                    top: 13px;
                    left: 0;
                    margin:0;

                    }
				.avia_wpml_language_switch ul { display:none;
                    z-index: 100;
                    background-color: white;
                    position: absolute;
                    width: 128px;
                    padding: 57px 10px 10px;
                    left: -2px;
                    border: 1px solid #E1E1E1;
                    border-top: none;
                    margin-top: 0;
                    top:0;
                    }
				.avia_wpml_language_switch li:hover ul{display:block;}
				.avia_wpml_language_switch li a{text-decoration:none;}
				.avia_sublanguages li{
				margin:0;
				padding: 7px 0;
				border-top:1px solid #e1e1e1;
				}
				.avia_cur_lang, .avia_cur_lang_edit{ font-size:11px; padding:3px 0; z-index:300; position:relative; cursor:pointer; color: #5C951E; display:block;}
				.avia_cur_lang_edit{ color: #7D8388;}
				.avia_cur_lang img{margin:0px 4px -1px 0;}
				</style>
				";


			}

			return $output;
		}
	}



	if(!function_exists('avia_wpml_filter_dropdown_post_query'))
        {
            add_filter( 'avf_dropdown_post_query', 'avia_wpml_filter_dropdown_post_query', 10, 4);

            function avia_wpml_filter_dropdown_post_query($prepare_sql, $table_name, $limit, $element)
            {
                global $wpdb;
                $wpml_lang = ICL_LANGUAGE_CODE;
                $wpml_join = " INNER JOIN {$wpdb->prefix}icl_translations ON {$table_name}.ID = {$wpdb->prefix}icl_translations.element_id ";
                $wpml_where = " {$wpdb->prefix}icl_translations.language_code LIKE '{$wpml_lang}' AND ";

                $prepare_sql = "SELECT distinct ID, post_title FROM {$table_name} {$wpml_join} WHERE {$wpml_where} post_status = 'publish' AND post_type = '".$element['subtype']."' ORDER BY post_title ASC LIMIT {$limit}";
                return $prepare_sql;
            }
        }
        
        
        
	if( ! function_exists( 'avia_change_wpml_home_link' ) )
	{
		add_filter( 'WPML_filter_link', 'avia_change_wpml_home_link', 10, 2 );
		
		function avia_change_wpml_home_link( $url, $lang )
		{
		    global $sitepress;
			
		    if( is_front_page() ) 
			{
				$new_url = $sitepress->language_url( $lang['language_code'] );
				
				/**
				 * @since 4.5.6.1
				 * @return string
				 */
				$url = apply_filters( 'avf_wpml_change_home_link', $new_url, $url, $lang );
			}
			
		    return $url;
		}
	}


	if(!function_exists('avia_wpml_slideshow_slide_id_check'))
	{
		add_filter( 'avf_avia_builder_slideshow_filter', 'avia_wpml_slideshow_slide_id_check', 10, 2 );
		
		/**
		 * Change ID of post array back to untranslated ID to be able to recognise selected image
		 * 
		 * @param array $slideshow_data
		 * @param mixed $context_object
		 * @return array
		 */
		function avia_wpml_slideshow_slide_id_check( $slideshow_data, $context_object )
		{
		    $id_array = $slideshow_data['id_array'];
		    $slides = $slideshow_data['slides'];
		
		    if( empty( $id_array ) || empty( $slides ) ) 
			{
				return $slideshow_data;
			}
		
		    foreach($id_array as $key => $id)
		    {
		        if(!isset($slides[$id]))
		        {
					//	icl_object_id deprecated since 3.2 - backward comp only
		            $id_of_translated_attachment = function_exists( 'wpml_object_id_filter' ) ? wpml_object_id_filter( $id, "attachment", true ) : icl_object_id( $id, "attachment", true );
		
		            if($id_of_translated_attachment && isset($slides[$id_of_translated_attachment]))
		            {
		                $slides[$id] = $slides[$id_of_translated_attachment];
		                unset($slides[$id_of_translated_attachment]);
		            }
		        }
		    }
		
		    $slideshow_data['slides'] = $slides;
		    return $slideshow_data;
		}
	}
	
	
	
	if(!function_exists('avia_wpml_author_name_translation'))
	{
		add_filter( 'avf_author_name', 'avia_wpml_author_name_translation', 10, 2);
		function avia_wpml_author_name_translation($name, $author_id)
		{
			if(function_exists('icl_t')) $name = icl_t('Authors', 'display_name_'.$author_id, $name);
			return $name;
		}
	}



	if(!function_exists('avia_wpml_author_nickname_translation'))
	{
		add_filter( 'avf_author_nickname', 'avia_wpml_author_nickname_translation', 10, 2);
		function avia_wpml_author_nickname_translation($name, $author_id)
		{
			if(function_exists('icl_t')) $name = icl_t('Authors', 'nickname_'.$author_id, $name);
			return $name;
		}
	}



	if( ! function_exists( 'avia_append_lang_flags' ) )
	{
		//first append search item to main menu
		add_filter( 'wp_nav_menu_items', 'avia_append_lang_flags', 9998, 2 );
		add_filter( 'avf_fallback_menu_items', 'avia_append_lang_flags', 9998, 2 );
		
		function avia_append_lang_flags( $items, $args )
		{
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
		
						$items .= "<li class='av-language-switch-item language_{$lang['language_code']} {$currentlang}'><a href='" . esc_url( $lang['url'] ) . "'>";
		                $items .= "	<span class='language_flag'><img title='" . $lang['native_name'] . "' src='" . esc_url( $lang['country_flag_url'] ) . "' /></span>";
		                $items .= "</a></li>";
		            }
		        }
		    }
			
		    return $items;
		}
	}
    
    
    

	if(!function_exists('avia_wpml_translate_date_format'))
	{
		function avia_wpml_translate_date_format($format)
		{
			if (function_exists('icl_translate')) $format = icl_translate('Formats', $format, $format);
			return $format;
		}
	
		add_filter('option_date_format', 'avia_wpml_translate_date_format');
	}
	
	


	if(!function_exists('avia_wpml_translate_all_search_results_url'))
	{
		function avia_wpml_translate_all_search_results_url($search_messages, $search_query)
		{
			//  icl_get_home_url was deprecaated since 3.2
			$wpml_home = function_exists( 'wpml_get_home_url_filter' ) ? wpml_get_home_url_filter() : icl_get_home_url();
			$search_messages['all_results_link'] = $wpml_home . '?' . $search_messages['all_results_query'];
			return $search_messages;
		}
	
		add_filter('avf_ajax_search_messages', 'avia_wpml_translate_all_search_results_url', 10, 2);
	}
	
	


	if(!function_exists('avia_translate_ids_from_query'))
	{
		function avia_translate_ids_from_query($query, $params)
		{
			$res = array();
			
			if(!empty($query['tax_query'][0]['terms']) && !empty($query['tax_query'][0]['taxonomy']))
			{
				foreach ($query['tax_query'][0]['terms'] as $id)
				{
					//	icl_object_id deprecated since 3.2 - backward comp only
					$xlat = function_exists( 'wpml_object_id_filter' ) ?  wpml_object_id_filter( $id, $query['tax_query'][0]['taxonomy'], true ) : icl_object_id( $id, $query['tax_query'][0]['taxonomy'], true );
					if(!is_null($xlat)) $res[] = $xlat;
				}
			
				if(!empty($res)) $query['tax_query'][0]['terms'] = $res;
			}
			else if(!empty($query['post__in']) && !empty($query['post_type']))
			{
				foreach($query['post__in'] as $id)
				{
					//	icl_object_id deprecated since 3.2 - backward comp only
					$xlat = function_exists( 'wpml_object_id_filter' ) ?  wpml_object_id_filter( $id, $query['post_type'], true ) : icl_object_id(  $id, $query['post_type'], true );
					if(!is_null($xlat)) $res[] = $xlat;
				}
				
				if(!empty($res)) $query['post__in'] = $res;
			}
		
			return $query;
		}
		
		add_filter('avia_masonry_entries_query', 'avia_translate_ids_from_query', 10, 2);
		add_filter('avia_post_grid_query', 'avia_translate_ids_from_query', 10, 2);
		add_filter('avia_post_slide_query', 'avia_translate_ids_from_query', 10, 2);
		add_filter('avia_blog_post_query', 'avia_translate_ids_from_query', 10, 2);
	}
	
	
	
	if( ! function_exists( 'avia_translate_check_by_tag_values' ) )
	{
		/**
		 * Translate tag values for attachments (av-helper-mayonry.php)
		 * 
		 * @since < 4.0
		 * @param array $value
		 * @return array
		 */
	    function avia_translate_check_by_tag_values( $value )
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
	
	    add_filter( 'avf_ratio_check_by_tag_values', 'avia_translate_check_by_tag_values', 10, 1 );
	}
	
	
	if( ! function_exists( 'avia_wpml_get_special_pages_ids' ) )
	{
		add_filter( 'avf_get_special_pages_ids', 'avia_wpml_get_special_pages_ids', 20, 2 );

		/**
		 * Returns page id's that do not belong to normal page lists 
		 * like custom 404, custom maintainence mode page, custom footer page
		 * Returns an array of all languages
		 * 
		 * @since 4.5.1
		 * @param array $post_ids
		 * @param string $context
		 * @return array
		 */
		function avia_wpml_get_special_pages_ids( $post_ids = array(), $context = '' )
		{
			global $avia_config;

			/**
			 * Return anything except true to hide inactive special pages
			 * 
			 * @since 4.5.5
			 * @return boolean
			 */
			$show_inactive = apply_filters( 'avf_show_inactive_special_pages', true, $post_ids, $context ) === true ? true : false;
			
			$langs = $avia_config['wpml']['lang'];

			$maintenance_mode = avia_wpml_get_options( 'maintenance_mode' );
			$maintenance_page = avia_wpml_get_options( 'maintenance_page' );
			$error404_custom = avia_wpml_get_options( 'error404_custom' );
			$error404_page = avia_wpml_get_options( 'error404_page' );
			$display_widgets_socket = avia_wpml_get_options( 'display_widgets_socket' );
			$footer_page = avia_wpml_get_options( 'footer_page' );

			foreach( $langs as $lang_id => $lang ) 
			{	
						// Maintenance Page				
				$active = in_array( $maintenance_mode[ $lang_id ], array( 'maintenance_mode', 'maintenance_mode_redirect' ) );
				if( is_numeric( $maintenance_page[ $lang_id ] ) && ( (int) $maintenance_page[ $lang_id ] > 0 ) )
				{
					if( $active || $show_inactive )
					{	
						$post_ids[] = (int) $maintenance_page[ $lang_id ];
					}
				}

						// 404 Page
				$active = in_array( $error404_custom[ $lang_id ], array( 'error404_custom', 'error404_redirect' ) );
				if( is_numeric( $error404_page[ $lang_id ] ) && ( (int) $error404_page[ $lang_id ] > 0 ) )
				{
					if( $active || $show_inactive )
					{
						$post_ids[] = (int) $error404_page[ $lang_id ];
					}
				}		

						// Footer Page
				$active = in_array( $display_widgets_socket[ $lang_id ], array( 'page_in_footer', 'page_in_footer_socket' ) );
				if( is_numeric( $footer_page[ $lang_id ] ) && ( (int) $footer_page[ $lang_id ] > 0 ) )
				{
					if( $active || $show_inactive )
					{
						$post_ids[] = (int) $footer_page[ $lang_id ];
					}
				}
			}

			$post_ids = array_unique( $post_ids, SORT_NUMERIC );
			return $post_ids;
		}
	}


}

/*fix for: https://wpml.org/errata/translation-editor-support-avia-layout-builder-enfold/*/
/**
 * Removed with 4.2.6 by Günter
 * Replaced by function below
 * 
 * The datastructire of $fields must have changed. On testing if was always false because $fields['body']['data'] does not exist any more
 */
if( ! function_exists( 'avia_wpml_sync_avia_layout_builder' ) )
{
	/**
	 * Ensure backwards comp - structure was checked with this version - might already have been changed earlier
	 */
	if ( defined( 'WPML_TM_VERSION' ) && version_compare( WPML_TM_VERSION, '2.5.2', '<' ) )
	{
		add_action( 'wpml_translation_job_saved', 'avia_wpml_sync_avia_layout_builder', 10, 3 );

		function avia_wpml_sync_avia_layout_builder( $new_post_id, $fields, $job ) 
		{
			if( isset( $fields['body']['data'] ) ) 
			{
				if ( 'active' === get_post_meta( $new_post_id, '_aviaLayoutBuilder_active', true ) ) 
				{
					update_post_meta(
						$new_post_id,
						'_aviaLayoutBuilderCleanData',
						$fields['body']['data']
					);
				}
			}
		}
	}
}

if( ! function_exists( 'avia_wpml_sync_avia_layout_builder_meta' ) )
{
	add_action( 'wpml_pro_translation_completed', 'avia_wpml_sync_avia_layout_builder_meta', 10, 3 );

	/**
	 * This filter is called when translation management is active and a post is translated with the WPML translation screen (not directly).
	 * In this case the save_post filter is not called and we have to update our meta fields here (esp. the shortcode tree)
	 * Post has been updated in DB already.
	 * 
	 * @since 4.2.6
	 * @added_by Günter
	 * @param int $new_post_id
	 * @param array $fields
	 * @param stdClass $job
	 */
	function avia_wpml_sync_avia_layout_builder_meta( $new_post_id, $fields, $job )
	{
		$post = get_post( $new_post_id );

		if( ! $post instanceof WP_Post )
		{
			return;
		}

		$builder_status = Avia_Builder()->get_alb_builder_status( $new_post_id );

		if( 'active' != $builder_status )
		{
			$content =  $post->post_content;
			$loc = 'content';
		}
		else
		{
			$content = get_post_meta( $new_post_id, '_aviaLayoutBuilderCleanData', true );
			$loc = 'clean_data';
		}

		Avia_Builder()->get_shortcode_parser()->set_builder_save_location( $loc );
		$content = ShortcodeHelper::clean_up_shortcode( $content, 'balance_only' );

		$tree = ShortcodeHelper::build_shortcode_tree( $content );
		Avia_Builder()->save_shortcode_tree( $new_post_id, $tree );
	}
}


/*compatibility function for the portfolio problems*/
if(!function_exists('avia_portfolio_compat') && defined('ICL_SITEPRESS_VERSION') && defined('ICL_LANGUAGE_CODE'))
{
	add_action( 'avia_action_before_framework_init', 'avia_portfolio_compat', 30);
	function avia_portfolio_compat()
	{
		global $avia_config;
		if(empty($avia_config['wpml']['settings']['custom_posts_sync_option']) || empty($avia_config['wpml']['settings']['custom_posts_sync_option']['portfolio']))
		{
			$settings = get_option('icl_sitepress_settings');
			$settings['custom_posts_sync_option']['portfolio'] = 1;
			$settings['taxonomies_sync_option']['portfolio_entries'] = 1;
			update_option('icl_sitepress_settings', $settings);
		}
	}
}



/**
 * Error 404 - Custom Page
 * Hooks into 'the_posts' filter and display the defined 404 page - compatible with WPML
 * Removed by günter with 4.4.2 and replaced with wp_redirect to set 404 header code
 * 
 * Left in case we need a fallback - can be deleted in some future version
 * 
 * @author tinabillinger
 * @since 4.3
 */
//if( ! function_exists( 'av_error404_wpml' ) )
//{
//    function av_error404_wpml($posts)
//    {
//        if( defined('ICL_SITEPRESS_VERSION') && defined('ICL_LANGUAGE_CODE') && !is_admin()) {
//            if (avia_get_option('error404_custom') == "error404_custom") {
//                // prevent endless loop
//                remove_filter( 'the_posts', 'av_error404_wpml', 999 );
//                if ( empty( $posts ) &&
//                    is_main_query() &&
//                    !is_robots() &&
//                    !is_home() &&
//                    !is_feed() &&
//                    !is_search() &&
//                    !is_archive() &&
//                    ( !defined('DOING_AJAX') || !DOING_AJAX ) ) {
//                    global $wp_query;
//                    $error404_page = avia_get_option('error404_page');
//                    $wp_query = null;
//                    $wp_query = new WP_Query();
//                    $wp_query->query( 'page_id=' . $error404_page );
//                    $wp_query->the_post();
//                    $template = get_page_template();
//                    $posts = $wp_query->posts;
//                    $wp_query->rewind_posts();
//                    return $posts;
//                }
//            }
//        }
//        return $posts;
//    }
//    add_filter( 'the_posts', 'av_error404_wpml', 999 );
//}


if( ! function_exists( 'av_wpml_get_fb_language_code' ) )
{
	/**
	 * Return the current WPML facebook language code
	 * 
	 * @since 4.3.2
	 * @author Günter
	 * @param string $langcode
	 * @param string $source				'fb-page'
	 */
	function av_wpml_get_fb_language_code( $langcode, $source )
	{
		//	icl_object_id deprecated since 3.2 - backward comp only
		if( function_exists( 'wpml_object_id_filter' ) || function_exists( 'icl_object_id' ) ) 
		{
			$locale = ICL_LANGUAGE_NAME_EN;
			$fbxml = @simplexml_load_file( AVIA_BASE . '/config-wpml/FacebookLocales.xml' );

			if( is_object( $fbxml ) )
			{
				foreach( $fbxml as $loc ) 
				{
					if( $loc->englishName == $locale ) 
					{
						$langcode = $loc->codes->code->standard->representation;
						break;
					}
				}
			}
		}
		
		return $langcode;
	}
	
}

if( ! function_exists( 'av_wpml_breadcrumbs_get_parents' ) )
{
	add_filter( 'avf_breadcrumbs_get_parents', 'av_wpml_breadcrumbs_get_parents', 10, 1 );
	
	/**
	 * Allow to translate breadcrumb trail - fixes a problem with parent page for portfolio not being translated correctly
	 * https://kriesi.at/support/topic/parent-page-link-works-correct-but-translation-doesnt/
	 * https://wpml.org/forums/topic/enfold-theme-cant-copy-breadcrumb-hierarchy/#post-893784
	 * 		
	 * @since 4.5.1
	 * @param int $post_id
	 * @return int
	 */
	function av_wpml_breadcrumbs_get_parents( $post_id )
	{
		return apply_filters( 'wpml_object_id' , $post_id, 'page', true );
	}

}

