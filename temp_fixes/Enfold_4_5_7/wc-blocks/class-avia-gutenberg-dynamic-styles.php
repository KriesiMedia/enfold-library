<?php
/**
 * Class that integrates dynamic theme settings in gutenberg editor.
 * Creates a css file.
 * 
 * @since 4.5.5
 * @added_by Günter
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'Gutenberg_Dynamic_Styles' ) )
{
	class Avia_Gutenberg_Dynamic_Styles 
	{

		/**
		 * Holds the instance of this class
		 * 
		 * @since 4.5.5
		 * @var Avia_Gutenberg_Dynamic_Styles 
		 */
		static private $_instance = null;
		
		/**
		 * New Line value for dynamic CSS output
		 * 
		 * @since 4.5.5
		 * @var string 
		 */
		protected $nl;


		/**
		 * Holds a copy of the global option array
		 * 
		 * @since 4.5.5
		 * @var array 
		 */
		protected $options;
		
		/**
		 *
		 * @since 4.5.5
		 * @var string 
		 */
		protected $dynamic_css;
		
		/**
		 * CSS rules built from "General Styling" theme options (css\dynamic-css.php)
		 * 
		 * @since 4.5.5
		 * @var array 
		 */
		protected $rules;

		/**
		 * Holds a copy of extracted option array from includes\admin\register-dynamic-styles.php
		 * 
		 * @since 4.5.5
		 * @var array 
		 */
		protected $color_set;
		
		/**
		 * Holds a copy of extracted option array from includes\admin\register-dynamic-styles.php
		 * 
		 * @since 4.5.5
		 * @var array 
		 */
		protected $styles;


		/**
		 * Return the instance of this class
		 * 
		 * @since 4.5.5
		 * @return Avia_Gutenberg_Dynamic_Styles
		 */
		static public function instance()
		{
			if( is_null( Avia_Gutenberg_Dynamic_Styles::$_instance ) )
			{
				Avia_Gutenberg_Dynamic_Styles::$_instance = new Avia_Gutenberg_Dynamic_Styles();
			}
			
			return Avia_Gutenberg_Dynamic_Styles::$_instance;
		}
		
		
		/**
		 * @since 4.5.5
		 */
		protected function __construct() 
		{
			$this->nl = "\r\n";
			$this->options = array();
			$this->dynamic_css = '';
			$this->rules = array();
			$this->color_set = array();
			$this->styles = array();
		}
		
		/**
		 * @since 4.5.5
		 */
		public function __destruct() 
		{
			unset( $this->options );
			unset( $this->rules );
			unset( $this->color_set );
			unset( $this->styles );
		}
		
		/**
		 * Returns the generated CSS
		 * 
		 * @since 4.5.5
		 * @return string
		 */
		public function get_dynamic_css_content()
		{
			return apply_filters( 'avf_gutenberg_dynamic_css_content', $this->dynamic_css );
		}
		
		/**
		 * Returns the generated CSS wrapped in <script> tags for inline integration
		 * 
		 * @since 4.5.5
		 * @return string
		 */
		public function get_head_css()
		{
			$out = ! empty( $this->dynamic_css ) ? "<style type='text/css'>\n" . $this->dynamic_css . "\n</style>\n" : '';
			return apply_filters( 'avf_gutenberg_dynamic_head_css', $out );
		}

		/**
		 * This function is called after includes\admin\register-dynamic-styles.php is executed
		 * 
		 * @since 4.5.5
		 * @param array $options
		 */
		public function create_styles( array $options = array() )
		{
			global $avia_config;
			

			$this->options = empty( $options ) ? avia_get_option() : $options;
			
			$this->color_set = $avia_config['backend_colors']['color_set'];
			$this->styles = $avia_config['backend_colors']['style'];
			$this->rules = array();
			
			/**
			 * Content font color, background color and font size
			 */
			$sel = array( 
						'.editor-block-list__block',
						'.editor-block-list__block p'
					);
			$styles = array();
			if( ! empty( $this->color_set['main_color']['bg'] ) )
			{
				$styles[] = "background-color:{$this->color_set['main_color']['bg']}";
			}
			if( ! empty( $this->color_set['main_color']['color'] ) )
			{
				$styles[] = "color:{$this->color_set['main_color']['color']}";
			}
			if( ! empty( $this->styles['default_font_size'] ) )
			{
				$styles[] = "font-size:{$this->styles['default_font_size']}";
			}
				
			if( ! empty( $styles ) )
			{
				$this->rules[] = array(
									'key'		=> 'block_direct_input',
									'selectors'	=> $sel,
									'styles'	=> $styles
									);
				
			}
			
			/**
			 * Heading color
			 */
			$sel = array( 
						'.editor-block-list__block h1',
						'.editor-block-list__block h2',
						'.editor-block-list__block h3',
						'.editor-block-list__block h4',
						'.editor-block-list__block h5',
						'.editor-block-list__block h6',
					);
			$styles = array();
			if( ! empty( $this->color_set['main_color']['heading'] ) )
			{
				$styles[] = "color:{$this->color_set['main_color']['heading']}";
			}
			if( ! empty( $styles ) )
			{
				$this->rules[] = array(
										'key'		=> 'block_direct_input',
										'selectors'	=> $sel,
										'styles'	=> $styles
									);
			}
			
			/**
			 * Title colors
			 */

			$sel = array( 
						'.editor-post-title__block textarea',
					);
			$styles = array();
			if( ! empty( $this->color_set['alternate_color']['color'] ) )
			{
				$styles[] = "color:{$this->color_set['alternate_color']['color']}";
			}
			
			if( ! empty( $this->color_set['alternate_color']['bg'] ) )
			{
				$styles[] = "background-color:{$this->color_set['alternate_color']['bg']}";
			}
			
			if( ! empty( $styles ) )
			{
				$this->rules[] = array(
										'key'		=> 'block_direct_input',
										'selectors'	=> $sel,
										'styles'	=> $styles
									);
			}


			/**
			 * Primary color for special elements
			 */
			$sel = array( 
						'.editor-block-list__block a',
						'.editor-block-list__block strong',
						'.editor-block-list__block b',
						'.editor-block-list__block b a',
						'.editor-block-list__block strong a',
					);
			$styles = array();
			if( ! empty( $this->color_set['main_color']['primary'] ) )
			{
				$styles[] = "color:{$this->color_set['main_color']['primary']}";
			}
			if( ! empty( $styles ) )
			{
				$this->rules[] = array(
										'key'		=> 'block_direct_input',
										'selectors'	=> $sel,
										'styles'	=> $styles
									);
			}
			
			
			/**
			 * Fix for WC product block for page
			 */
			
			$sel = array(
						'.wc-block-featured-product__wrapper p',
						'.wc-block-featured-product__wrapper .block-editor-block-list__block'
					);
			
			$styles = array(
						'background:transparent'
					);
			
			$this->rules[] = array(
										'key'		=> 'block_direct_input',
										'selectors'	=> $sel,
										'styles'	=> $styles
									);
			
			$this->rules = array_merge( $this->rules, $avia_config['style'] );
			
			/**
			 * @used_by		currently unused
			 * @since 4.5.5
			 * @return array
			 */
			$this->rules = apply_filters( 'avf_gutenberg_create_styles_rules', $this->rules, $this );
			
			$this->create_dynamic_css_content();
		}
		
		/**
		 * Creates the CSS out of the options and settings
		 * 
		 * @since 4.5.5
		 */
		protected function create_dynamic_css_content() 
		{

			$this->dynamic_css = '';
						
			/**
			 * Get all custom uploaded fonts
			 * 
			 * @used_by				AviaTypeFonts			10
			 * @since 5.5
			 */
			$sg = AviaGutenbergThemeIntegration()->get_style_generator();
			$custom_fonts = apply_filters( 'avf_create_dynamic_stylesheet', '', $sg, 'before' );
			
			/**
			 * fonts are already in <head> - supress when stylesheet is printed to <head>
			 */
			if( ! empty( $custom_fonts ) && AviaGutenbergThemeIntegration()->use_dynamic_stylesheet() )
			{
				$this->dynamic_css .= $custom_fonts . $this->nl;
			}
			
			/**
			 * Add all main option rules
			 */
			foreach( $this->rules as $index => $rule ) 
			{
				if( ! isset( $rule['key'] ) )
				{
					continue;
				}
				
				if( 'direct_input' == $rule['key'] )
				{
					continue;
				}
				
				if( 'block_direct_input' == $rule['key'] && ! empty( $rule['selectors'] ) )
				{
					$s = ! empty( $rule['styles'] ) ? $rule['styles'] : array();
					$this->add_style_arrays( $rule['selectors'], $s );
				}
				else if( method_exists( $this, $rule['key'] ) )
				{
					$this->{$rule['key']}( $rule, $index );
				}
			}
			
			/**
			 * Add wizzard stylings in a future release here
			 */
			
			
			/**
			 * @used_by			currently unused
			 * @since 4.5.5
			 * @return string
			 */
			$this->dynamic_css = apply_filters( 'avf_gutenberg_create_dynamic_css', $this->dynamic_css, $this );
			
			/**
			 * Set a dummy content to avoid problems with output of empty CSS file
			 */
			if( empty( $this->dynamic_css ) )
			{
				$this->dynamic_css = '.root{}' . $this->nl;
			}
			
		}
		
		/**
		 * Adds a "google_webfont" to styling
		 * 
		 * @since 4.5.5
		 * @param array $rule
		 * @param int $index
		 */
		public function google_webfont( array $rule, $index )
		{
			if( empty( $rule['font_info'] ) )
			{
				return;
			}
			
			$selectors = array( 
							'default_font'		=> array( 
														'.editor-block-list__block'
													),
							'google_webfont'	=> array( 
														'.editor-block-list__block .wp-block-heading',
														'.editor-post-title__block textarea',
													)
						);
			
			
			$sel = $selectors[ $rule['font_source'] ];
			$styles = array();
			
			if( 'google_webfont' == $rule['font_source'] )
			{
				$bodycls = ! empty( $rule['font_info']['font_css'] ) ? '.' . $rule['font_info']['font_css'] : '';
				foreach ( $sel as $key => $s) 
				{
					$sel[ $key ] = str_replace( '{{AVIA_BODY_CLASS}}', $bodycls, $s );
				}
			}
			
			unset( $rule['font_info']['font_css'] );
			
			foreach( $rule['font_info'] as $info ) 
			{
				if( ! empty( $info ) )
				{
					$styles[] = rtrim( rtrim( $info ), ';' );
				}
			}
			
			$this->add_style_arrays( $sel, $styles );
		}
		
		/**
		 * Combines each entry of selector array with all $styles entries and adds output to dynamic css content
		 * if $styles is empty  $selectors must contain a valid CSS rule.
		 * 
		 * @since 4.5.5
		 * @param array $selectors
		 * @param array $styles
		 */
		protected function add_style_arrays( array $selectors, array $styles = array() )
		{
			if( empty( $styles ) )
			{
				$this->dynamic_css .= $selectors . $this->nl;
			}
			else
			{
				$sel = implode( ",{$this->nl}", $selectors );
				
				/**
				 * Gutenberg might override settings
				 */
				if( ! AviaGutenbergThemeIntegration()->use_dynamic_stylesheet() )
				{
					foreach( $styles as $key => $style ) 
					{
						if( false === strpos( $style, '!important' ) )
						{
							$style = rtrim( rtrim( $style), ';' );
							$styles[ $key ] = "{$style} !important";
						}
					}
				}
				
				$style = implode( '; ', $styles ) . ';';
				$this->dynamic_css .= "{$sel} {{$style}}" . $this->nl;
			}
		}
	
	}
	
	/**
	 * Returns the main instance of Gutenberg_Dynamic_Styles to prevent the need to use globals
	 * 
	 * @since 4.5.5
	 * @return Avia_Gutenberg_Dynamic_Styles
	 */
	function AviaGutenbergDynamicStyles()
	{
		return Avia_Gutenberg_Dynamic_Styles::instance();
	}
}
