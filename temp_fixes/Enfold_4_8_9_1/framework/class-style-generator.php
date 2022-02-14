<?php
/**
 * This file holds the class that creates styles for the theme based on the backend options
 *
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright (c) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 */
if( ! defined( 'AVIA_FW' ) )  {  exit( 'No direct script access allowed' );  }


if( ! class_exists( 'avia_style_generator' ) )
{
	/**
	 *  The avia_style_generator class holds all methods necessary to create and overwrite the default css styles with those set in the wordpress backend
	 *  @package 	AviaFramework
	 */

	class avia_style_generator
	{
		/**
		 * This array hold all styledata defined for the theme that should be overwriten dynamically
		 *
		 * @since < 4.0
		 * @var array
		 */
		public $rules;

		/**
		 * $output contains the html string that is printed in the frontend
		 *
		 * @since < 4.0
		 * @var string
		 */
		public $output;

		/**
		 * $extra_output contains html content that should be printed after the actual css rules. for example a javascript with cufon rules
		 *
		 * @since < 4.0
		 * @var string
		 */
		public $extra_output;

		/*
		 * Add print var to check if we need to output style tags or not
		 *
		 * @since < 4.0
		 * @var boolean
		 */
		public $print_styles;

		/**
		 * Add print var to check if we need to output extra data in header
		 *
		 * @since < 4.0
		 * @var boolean
		 */
		public $print_extra_output;

		/**
		 * @since < 4.0
		 * @var array
		 */
		public $used_fonts;

		/**
		 * @since < 4.0
		 * @var string
		 */
		public $google_fontlist;

		/**
		 * holds all available styling rules that are defined in the config files
		 *
		 * @since < 4.0
		 * @var array
		 */
		public $stylewizard;

		/**
		 * holds all saved elements that contain rules
		 *
		 * @since < 4.0
		 * @var array
		 */
		public $stylewizardIDs;

		/**
		 *
		 * @since 4.5.1
		 * @var array
		 */
		protected $fallback_fonts;

		/**
		 *
		 * @since < 4.5
		 * @param avia_superobject $avia_superobject
		 * @param boolean $print_styles
		 * @param boolean $print_extra_output
		 * @param boolean $addaction
		 */
        public function __construct( avia_superobject $avia_superobject, $print_styles = true, $print_extra_output = true, $addaction = true )
        {
			$this->rules = array();
			$this->output = '';
			$this->extra_output = '';
            $this->print_styles = $print_styles;
            $this->print_extra_output = $print_extra_output;
			$this->used_fonts = array();
			$this->google_fontlist = '';
			$this->stylewizard = array();
			$this->stylewizardIDs = array();
			$this->fallback_fonts = array(
										'Helvetica',
										'Arial',
										'sans-serif'
								);


            //check if stylesheet exists...
			$safe_name = avia_backend_safe_string( $avia_superobject->base_data['prefix'] );

			if( get_option( 'avia_stylesheet_exists' . $safe_name ) == 'true' )
			{
				$this->print_styles = false;
			}

			$this->get_style_wizard_additions( $avia_superobject->option_page_data );

			if( $addaction )
			{
				add_action( 'wp_head', array( $this, 'create_styles'), 5 );
				add_action( 'wp_head', array( $this, 'print_extra_output'), 6 );
				add_action( 'wp_head', array( $this, 'print_styles'), 1000 );
				add_action( 'wp_footer', array( $this, 'print_footer'), 1000 );
        	}
		}

		/**
		 * @since < 4.5
		 */
		public function __destruct()
		{
			unset( $this->rules );
			unset( $this->used_fonts );
			unset( $this->stylewizard );
			unset( $this->stylewizardIDs );
			unset( $this->fallback_fonts );
		}

		/**
		 * gather styling wizard elements so the rules can be converted as well
		 *
		 * @since < 4.0
		 * @param array $option_page_data
		 */
		protected function get_style_wizard_additions( $option_page_data )
		{
        	foreach( $option_page_data as $data )
        	{
        		if( $data['type'] == 'styling_wizard' )
        		{
        			$this->stylewizardIDs[] = $data['id'];
        			$this->stylewizard = array_merge( $this->stylewizard, $data['elements'] );
        		}
			}
		}

		/**
		 * Returns a filterable array of fallback fonts that can be used to add to the font-family.
		 * Default is Helvetia family
		 *
		 * @since 4.5.1
		 * @param string $selected_font_family
		 * @return array
		 */
		public function get_fallback_fonts_array( $selected_font_family )
		{
			return apply_filters( 'avf_fallback_fonts_array', $this->fallback_fonts, $selected_font_family );
		}

		/**
		 * Returns the font family string extended with the fallback fonts.
		 * Tries to filter double entries.
		 *
		 * @since 4.5.1
		 * @param string $selected_font_family
		 * @param string $rule
		 * @return string
		 */
		public function font_family_string( $selected_font_family, $rule = '' )
		{
			$fallback = $this->get_fallback_fonts_array( $selected_font_family );

			$plain_selected = strtolower( str_replace( array( '"', "'" ), '', $selected_font_family ) );

			/*
			 * @since 4.8.9.1 - fix bug with websafe fonts containing '-'
			 * @since 4.9 - removed again and changed values in AviaSuperobject()->type_fonts()->websafe_fonts_select_list()
			 */
//			$plain_selected = strtolower( str_replace( '-', ' ', $selected_font_family ) );

			foreach( $fallback as $key => $font )
			{
				$plain_font = strtolower( str_replace( array( '"', "'" ), '', $font ) );
				if( ( stripos( $rule, $font ) !== false ) || ( $plain_selected == $plain_font ) )
				{
					unset( $fallback[ $key ] );
				}
			}

			$family = "'{$plain_selected}'";

			if( ! empty( $fallback ) )
			{
				$family .= ', ';
				$family .= implode( ', ', $fallback );
			}

			return $family;
		}

		/**
		 * @since < 4.0
		 * @return string
		 */
		public function create_styles()
		{
			global $avia_config;

			if( ! isset( $avia_config['font_stack'] ) )
			{
				$avia_config['font_stack'] = '';
			}

			if( ! isset( $avia_config['style'] ) )
			{
				return '';
			}

			/**
			 * @used_by			config-bbpress\config.php avia_bbpress_forum_colors()			10
			 * @since < 4.0
			 * @return array
			 */
			$avia_config['style'] = apply_filters( 'avia_style_filter', $avia_config['style'] );

			$this->rules = $avia_config['style'];
			$this->output = '';

			/**
			 * @used_by				AviaTypeFonts			10
			 * @since 4.3
			 */
			$this->output = apply_filters( 'avf_create_dynamic_stylesheet', $this->output, $this, 'before' );

			//default styling rules
			if( is_array( $this->rules ) )
			{
				foreach( $this->rules as $index => $rule )
				{
					$rule['value'] = str_replace( '{{AVIA_BASE_URL}}', AVIA_BASE_URL, $rule['value'] );
					$rule['value'] = preg_replace( '/(http|https):\/\//', '//', $rule['value'] );

					//check if a executing method was passed, if not simply put the string together based on the key and value array
					if( isset( $rule['key'] ) && method_exists( $this, $rule['key'] ) && $rule['value'] != '' )
					{
						$this->output .= $this->{$rule['key']}( $rule, $index ) . "\n";
					}
					else if( $rule['value'] != '' )
					{
						$this->output .= $rule['elements'] . "{\n" . $rule['key'] . ':' . $rule['value'] . ";\n}\n\n";
					}
				}
			}

			//css wizard styling rules( e.g. includes\admin\register-backend-advanced-styles.php )
			$this->create_wizard_styles();

			/**
			 * @since 4.3
			 */
			$this->output = apply_filters( 'avf_create_dynamic_stylesheet', $this->output, $this, 'after' );

            //output inline css in head section or return the style code
			$return = '';

            if( ! empty( $this->output ) )
            {
                if( ! empty( $this->print_styles ) )
                {

                }
                else
                {
                    $return = $this->output;
                }
            }

			return $return;
		}

		/**
		 * Add all additional styles to output string (like defined in includes\admin\register-backend-advanced-styles.php)
		 *
		 * @since < 4.0
		 */
		protected function create_wizard_styles()
		{
			if( empty( $this->stylewizardIDs ) )
			{
				return;
			}

			global $avia_config;

			foreach( $this->stylewizardIDs as $id )
			{
				$options = avia_get_option( $id );
				if( empty( $options ) )
				{
					continue;
				}

				foreach( $options as $style )
				{
					if( empty( $this->stylewizard[ $style['id'] ]['selector'] ) )
					{
						continue;
					}

					//first of all we need to build the selector string
					$selectorArray = $this->stylewizard[ $style['id'] ]['selector'];
					$sectionCheck = $this->stylewizard[ $style['id'] ]['sections'];

					foreach( $selectorArray as $selector => $ruleset )
					{
						$temp_selector = '';
						$rules = '';
						$sectionActive = strpos( $selector, '[sections]' ) !== false ? true : false;

						//hover check
						if( isset( $style['hover_active'] ) && $style['hover_active'] != 'disabled' )
						{
							$selector = str_replace( '[hover]', ':hover', $selector );
						}
						else
						{
							$selector = str_replace( '[hover]', '', $selector );
						}

						//active check
						if( isset($style['item_active'] ) && $style['item_active'] != 'disabled' && isset( $this->stylewizard[ $style['id'] ]['active'] ) )
						{
							$selector = str_replace( '[active]', $this->stylewizard[ $style['id'] ]['active'], $selector );
						}
						else
						{
							$selector = str_replace( '[active]', '', $selector );
						}

						//if sections are enabled make sure that the selector string gets generated for each section
						if( $sectionActive && $sectionCheck && isset( $avia_config['color_sets'] ) )
						{
							//check if all color sections are selected. if so we dont need to loop several times but only once
							$all_sets_selected = true;
							$color_sets_to_iterate = $avia_config['color_sets'];

							foreach( $avia_config['color_sets'] as $key => $name )
							{
								if( empty( $style[ $key ] ) || ( isset( $style[ $key ] ) && $style[ $key ] == 'disabled' ) )
								{
									$all_sets_selected = false;
								}
							}

							if( $all_sets_selected )
							{
								$color_sets_to_iterate = array( 'all_colors' => '' );
							}

							foreach( $color_sets_to_iterate as $key => $name )
							{
								if( ( isset( $style[ $key ] ) && $style[ $key ] != 'disabled' ) || $key == 'all_colors' )
								{
									if( ! empty( $temp_selector ) )
									{
										$temp_selector .= ', ';
									}

									$temp_selector .= str_replace( '[sections]', '.' . $key, $selector );
								}
							}

							if( empty( $temp_selector ) )
							{
								continue;
							}
						}

						//apply modified rules to the selector
						if( ! empty( $temp_selector ) )
						{
							$selector = $temp_selector;
						}

						//we got the selector stored in $selector, now we need to generate the rules
						foreach( $style as $key => $value )
						{
							if( $value != '' && $value != 'true' && $value != 'disabled' && $key != 'id' )
							{
								if( is_array( $ruleset ) )
								{
									foreach( $ruleset as $rule_key => $rule_val )
									{
										//if the $rule_val is an array we only apply the rules if the user selected value is the same as the first rule_val entry
										if( is_array( $rule_val ) )
										{
											if( $rule_val[0] !== $value )
											{
												continue;
											}
											else
											{
												$rule_val = $rule_val[1];
											}
										}

										if( $rule_key == $key )
										{
											if( str_replace( '_', '-', $rule_key ) == 'font-family' )
											{
												$typefont = AviaSuperobject()->type_fonts();
												$font = $typefont->split_font_info( $value );

												if( 'google' != $typefont->get_selected_font_type( $font ) )
												{
													$font = $typefont->set_font_family( $font );
												}
												else
												{
													$this->add_google_font( $font['family'], $font['weight'] );
												}

//	replaced in 4.5.1 - can be removed in future releases:
//												$value = "'" . $font['family'] . "', 'Helvetica Neue', Helvetica, Arial, sans-serif";

												$value = $this->font_family_string( $font['family'], $rule_val );
											}

											$rules .= str_replace( "%{$key}%", $value, $rule_val );

										}
									}
								}
								else
								{
									$key = str_replace( '_', '-',$key );

									switch( $key )
									{
										case 'font-family':
											$typefont = AviaSuperobject()->type_fonts();
											$font = $typefont->split_font_info( $value );

											if( 'google' != $typefont->get_selected_font_type( $font ) )
											{
												$font = $typefont->set_font_family( $font );
											}
											else
											{
												$this->add_google_font( $font['family'], $font['weight'] );
											}

//	replaced in 4.5.1 - can be removed in future releases:
//											$rules .= "font-family: '{$font['family']}', 'Helvetica Neue', Helvetica, Arial, sans-serif;";

											$family = $this->font_family_string( $font['family'] );
											$rules .= "font-family: {$family};";
											break;
										default:
											$rules .= "{$key}:{$value};";
											break;
									}
								}
							}
						}

						if( ! empty( $rules ) )
						{
							$this->output .= $selector . '{' . $rules . '}' . "\r\n";
						}
					}
				}
			}
		}

		/**
		 *
		 * @since < 4.0
		 */
		public function print_styles()
		{
			if( empty( $this->print_styles ) )
			{
				return;
			}

			echo "\n<!-- custom styles set at your backend-->\n";
			echo	"<style type='text/css' id='dynamic-styles'>\n";
			echo		$this->output;
			echo	"</style>\n";
			echo "\n<!-- end custom styles-->\n\n";
		}

		/**
		 *
		 * @since < 4.0
		 */
		public function print_extra_output()
		{
        	if( $this->print_extra_output )
        	{
	        	$fonts = avia_get_option( 'gfonts_in_footer' );
        		if( empty( $fonts ) || $fonts == 'disabled' )
				{
					$this->extra_output .= $this->link_google_font();
				}

        		echo $this->extra_output;
        	}
		}

		/**
		 *
		 * @since < 4.0
		 */
		public function print_footer()
		{
	        $fonts = avia_get_option( 'gfonts_in_footer' );
        	if( ! empty( $fonts ) && $fonts == 'gfonts_in_footer' )
			{
				$this->footer = $this->link_google_font();
			}

        	if( ! empty( $this->footer ) )
        	{
        		echo $this->footer;
        	}
		}

		/**
		 * @since < 4.0
		 * @param array $rule
		 */
		public function cufon( $rule )
		{
			if( empty( $this->footer ) )
			{
				$this->footer = '';
			}

			$rule_split = explode( '__', $rule['value'] );
			if( ! isset( $rule_split[1] ) )
			{
				$rule_split[1] = 1;
			}

			$this->footer .= "\n<!-- cufon font replacement -->\n";
			$this->footer .= "<script type='text/javascript' src='" . AVIA_BASE_URL . "fonts/cufon.js'></script>\n";
			$this->footer .= "<script type='text/javascript' src='" . AVIA_BASE_URL . "fonts/" . $rule_split[0] . ".font.js'></script>\n";
			$this->footer .= "<script type='text/javascript'>\n\t";
			$this->footer .=		"var avia_cufon_size_mod = '" . $rule_split[1] . "'; \n\tCufon.replace('" . $rule['elements'] . "',{  fontFamily: 'cufon', hover:'true' });\n";
			$this->footer .= "</script>\n";
		}

		/**
		 * Add custom font settings
		 *
		 * @since < 4.0
		 * @param array $rule
		 * @param int $index
		 */
		public function google_webfont( array $rule, $index )
		{
			global $avia_config;

			/**
			 * check if the font has a weight applied to it and extract it. eg: 'Yanone Kaffeesatz:200'
			 */
			$typefont = AviaSuperobject()->type_fonts();
			$font = $typefont->split_font_info( $rule['value'] );
			$type = $typefont->get_selected_font_type( $font );

			/**
			 * Allow to specify a specific font size in select box
			 * e.g. myfont__1.5, google_font__1.5::100,300,500
			 */
			$rule_split = explode( '__', $font['family'] );
			$font['family'] = $rule_split[0];
			if( ! isset( $rule_split[1] ) )
			{
				$rule_split[1] = 1;
			}
			$font_weight = '';

			if( 'google' != $type )
			{
				$font = $typefont->set_font_family( $font );

				/**
				 * FF does not recognise when ' ' at beginning !!
				 */
				if( ! empty( $avia_config['font_stack'] ) )
				{
					$avia_config['font_stack'] .= ' ';
				}

				$avia_config['font_stack'] .= $font['family'] . '-' . $type;

				if( 'websave' == $type )
				{
					$font['family'] = str_replace( '-', ' ' , $font['family'] );
				}
			}
			else
			{
				$this->add_google_font( $font['family'], $font['weight'] );

				if( ! empty( $font_weight ) && strpos( $font_weight, ',' ) === false )
				{
					$font_weight = "font-weight:{$font_weight};";
				}
			}

			$font_css = strtolower( str_replace( ' ', '_' , $font['family'] ) );

//	replaced in 4.5.1 - can be removed in future releases:
//			$this->output .= $rule['elements'] . ".{$font_css} {font-family:'" . $font['family'] ."', 'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif;" . $font_weight . "}";

			$family = $this->font_family_string( $font['family'] );

			$font_info = array();
			$font_info['font_css'] = $font_css;
			$font_info['family'] = "font-family:{$family};";
			$font_info['weight'] = $font_weight;
			$font_info['size'] = '';

			/**
			 * Apply font class to all selectors
			 * Bug prior 4.5.5: font class was only applied to last selector
			 */
			$css = $rule['elements'];
			if( ! empty( $rule['add_font_class'] ) )
			{
				$selectors = explode( ',', $css );
				foreach ( $selectors as $i => $selector )
				{
					$selector = trim( $selector );
					$selectors[ $i ] = "{$selector}.{$font_css}";
				}

				$css = implode( ',', $selectors );
			}

			$this->output .= "{$css} {font-family:{$family}; {$font_weight}}";

			if( $rule_split[1] !== 1 && $rule_split[1] )
			{
				$font_info['size'] = "font-size:{$rule_split[1]}em;";
				$this->output .= "\r\n{$rule['elements']} {{$font_info['size']}}";
			}

			$avia_config['style'][ $index ]['font_info'] = $font_info;

			$avia_config['font_stack'] .= ' ' . $font_css . ' ';
		}

		/**
		 * add the font to the query string
		 *
		 * @since < 4.0
		 * @param string $font_family
		 * @param string $font_weight
		 */
		public function add_google_font( $font_family, $font_weight = '' )
		{
			if( ! in_array( $font_family . $font_weight, $this->used_fonts ) )
			{
				$this->used_fonts[] = $font_family . $font_weight;
				if( ! empty( $this->google_fontlist ) )
				{
					$this->google_fontlist .= '%7C';
				}

				if( ! empty( $font_weight ) )
				{
					$font_weight = ':' . $font_weight ;
				}

				$this->google_fontlist .= str_replace( ' ', '+', $font_family ) . $font_weight;
			}
		}

		/**
		 * Get the link tag with the $this->google_fontlist
		 *
		 * @since < 4.4
		 * @return string
		 */
		public function link_google_font()
		{
			if( empty( $this->google_fontlist ) )
			{
				return '';
			}

			if( true != apply_filters( 'avf_output_google_webfonts_script', true ) )
			{
				return '';
			}

			/**
			 * Allow to change default behaviour of browsers when loading external fonts
			 * https://developers.google.com/web/updates/2016/02/font-display
			 *
			 * @since 4.9
			 * @param string $font_display
			 * @return string			auto | block | swap | fallback | optional
			 */
			$font_display = apply_filters( 'avf_font_display_google_fonts', avia_get_option( 'custom_font_display', '' ) );
			$font_display = empty( $font_display ) ? 'auto' : $font_display;

			/**
			 * https://kriesi.at/support/topic/enfold-fontdisplay/
			 *
			 * @since 4.9
			 */
			$google_fontlist = $this->google_fontlist;
			$google_fontlist .= '&display=' . $font_display;

			/**
			 *
			 * @since ???
			 * @since 4.9							added $this->google_fontlist, $font_display
			 * @param string $google_fontlist
			 * @param string $this->google_fontlist,
			 * @param string $font_display
			 * @return string
			 */
			$google_fontlist = apply_filters( 'avf_google_fontlist', $google_fontlist, $this->google_fontlist, $font_display );


			$output  = '';
			$output .= "\n<!-- google webfont font replacement -->\n";
			$output .= "
			<script type='text/javascript'>

				(function() {

					/*	check if webfonts are disabled by user setting via cookie - or user must opt in.	*/
					var html = document.getElementsByTagName('html')[0];
					var cookie_check = html.className.indexOf('av-cookies-needs-opt-in') >= 0 || html.className.indexOf('av-cookies-can-opt-out') >= 0;
					var allow_continue = true;
					var silent_accept_cookie = html.className.indexOf('av-cookies-user-silent-accept') >= 0;

					if( cookie_check && ! silent_accept_cookie )
					{
						if( ! document.cookie.match(/aviaCookieConsent/) || html.className.indexOf('av-cookies-session-refused') >= 0 )
						{
							allow_continue = false;
						}
						else
						{
							if( ! document.cookie.match(/aviaPrivacyRefuseCookiesHideBar/) )
							{
								allow_continue = false;
							}
							else if( ! document.cookie.match(/aviaPrivacyEssentialCookiesEnabled/) )
							{
								allow_continue = false;
							}
							else if( document.cookie.match(/aviaPrivacyGoogleWebfontsDisabled/) )
							{
								allow_continue = false;
							}
						}
					}

					if( allow_continue )
					{
						var f = document.createElement('link');

						f.type 	= 'text/css';
						f.rel 	= 'stylesheet';
						f.href 	= '//fonts.googleapis.com/css?family={$google_fontlist}';
						f.id 	= 'avia-google-webfont';

						document.getElementsByTagName('head')[0].appendChild(f);
					}
				})();

			</script>
			";

			return $output;
		}


		/**
		 *
		 * @since < 4.0
		 * @param array $rule
		 * @return string
		 */
		public function direct_input( $rule )
		{
			return $rule['value'];
		}

		/**
		 *
		 * @since < 4.0
		 * @param array $rule
		 * @return string
		 */
		public function backgroundImage( $rule )
		{
			return "{$rule['elements']}{\nbackground-image:url({$rule['value']});\n}\n\n";
		}

	}
}

