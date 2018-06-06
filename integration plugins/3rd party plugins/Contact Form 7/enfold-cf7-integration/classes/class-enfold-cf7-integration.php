<?php
/* 
 * Add support for contact form 7 plugin
 * 
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'Enfold_CF7_Integration' ) )
{
	/**
	 * @since 1.0.0
	 */
	class Enfold_CF7_Integration
	{
		
		/**
		 * Defines the areas we support with dynamic CSS
		 * 
		 * @since 1.0.0
		 * @var array 
		 */
		protected $supported_areas;
		
		/**
		 * @since 1.0.0
		 */
		public function __construct()
		{
			$this->supported_areas = array();
			
			add_action( 'init', array( $this, 'handler_wp_register_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'handler_wp_enqueue_scripts' ), 510 );
			
			add_filter( 'avia_dynamic_css_output', array( $this, 'handler_avia_dynamic_css_output' ), 10, 2 );
			add_filter( 'wpcf7_form_class_attr', array( $this, 'handler_form_class_attr' ), 10, 1 );
		}
		
		/**
		 * 
		 * @since 1.0.0
		 */
		public function __destruct()
		{
			unset( $this->supported_areas );
		}
		
		/**
		 * @since 1.0.0
		 */
		public function handler_wp_register_scripts()
		{
			global $enfold_cf7_integration_globals;
			
			$theme = wp_get_theme();
			if( false !== $theme->parent() )
			{
				$theme = $theme->parent();
			}
			$vn = $theme->get( 'Version' );
		
			wp_register_style( 'avia-cf7-style', $enfold_cf7_integration_globals['plugin_url'] . "css/cf7-mod.css", array( 'avia-scs' ), $vn, 'all' );
		}
		
		/**
		 * @since 1.0.0
		 */
		public function handler_wp_enqueue_scripts()
		{
			wp_enqueue_style( 'avia-cf7-style');
		}
		
		
		/**
		 * Add our custom class to form class string to inherit CSS from template builder contact form shortcode
		 * 
		 * @since 1.0.0
		 * @param string $class
		 * @return string
		 */
		public function handler_form_class_attr( $class )
		{
			$class .= ' avia_ajax_form avia-disable-default-ajax';
			return $class;
		}
		
		/**
		 * Customize global CSS
		 * 
		 * This plugin creates HTML structures different from Enfold - we need to hook into this structure
		 * 
		 * @since 1.0.0
		 * @param string $output
		 * @param array $color_set
		 * @return string
		 */
		public function handler_avia_dynamic_css_output( $output, $color_set )
		{
			if( empty( $this->supported_areas ) )
			{
				$this->supported_areas = apply_filters( 'avf_cf7_supported_areas', 
								array(
										'main_color',
										'alternate_color',
										'footer_color',
										'socket_color'
									));
				
			}
			
			/**
			 * iterates over the color sets: usually $key is either:
			 * 
			 * header_color, main_color, alternate_color, footer_color, socket_color
			 */
			foreach ( $color_set as $key => $colors )
			{
				if( ! in_array( $key, $this->supported_areas ) )
				{
					continue;
				}
				
				$key = "." . $key;
				extract( $colors );
			
				$constant_font 	= avia_backend_calc_preceived_brightness( $primary, 230 ) ?  '#ffffff' : $bg;
				$button_border  = avia_backend_calculate_similar_color( $primary, 'darker', 2 );
				$button_border2 = avia_backend_calculate_similar_color( $secondary, 'darker', 2 );
				$bg3 			= avia_backend_calculate_similar_color( $bg2, 'darker', 1 );
				
				$output .= "
					
					#top $key .wpcf7 .avia_ajax_form input[type='text'],
					#top $key .wpcf7 .avia_ajax_form input[type='input'],
					#top $key .wpcf7 .avia_ajax_form input[type='password'],
					#top $key .wpcf7 .avia_ajax_form input[type='email'],
					#top $key .wpcf7 .avia_ajax_form input[type='number'],
					#top $key .wpcf7 .avia_ajax_form input[type='url'],
					#top $key .wpcf7 .avia_ajax_form input[type='tel'],
					#top $key .wpcf7 .avia_ajax_form input[type='search'],
					#top $key .wpcf7 .avia_ajax_form input[type='date'],
					#top $key .wpcf7 .avia_ajax_form textarea,
					#top $key .wpcf7 .avia_ajax_form select
					{
						border-color: $border;
						background-color: $bg2;
						color: $meta;
					}
					#top $key .wpcf7 .avia_ajax_form input[type='date']
					{
						border-style: solid;
						border-width: 1px;
					}
					
					#top $key .wpcf7 .avia_ajax_form,
					#top $key .wpcf7 .avia_ajax_form	
					{
						border-color: $border;
						color: $color;
					}
					
					#top $key .wpcf7 .avia_ajax_form p span.wpcf7-quiz-label,
					#top $key .wpcf7 .avia_ajax_form p span.wpcf7-list-item-label	
					{
						border-color: $border;
						color: $color;
					}
				
				

				";
				
				//unset all vars with the help of variable vars :)
				foreach( $colors as $key => $val )
				{ 
					unset( $$key ); 
				}
			
			}
			
			
			return $output;
		}
	
	}
}


