<?php
/**
 * Post Slider
 *
 * Display a Slideshow of Post Entries
 * Element is in Beta and by default disabled. Todo: test with layerslider elements. currently throws error bc layerslider is only included if layerslider element is detected which is not the case with the post/page element
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_sc_postslider' ) )
{
	class avia_sc_postslider extends aviaShortcodeTemplate
	{

		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'yes';
			$this->config['base_element']	= 'yes';
			
			$this->config['name']			= __( 'Post Slider', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-postslider.png';
			$this->config['order']			= 30;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_postslider';
			$this->config['tooltip']		= __( 'Display a Slideshow of Post Entries', 'avia_framework' );
			$this->config['drag-level']		= 3;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}
		
		function extra_assets()
		{
			//load css
			wp_enqueue_style( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/slideshow/slideshow.css', array( 'avia-layout' ), false );
			wp_enqueue_style( 'avia-module-postslider', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/postslider/postslider.css', array( 'avia-module-slideshow' ), false );
			
				//load js
			wp_enqueue_script( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/slideshow/slideshow.js', array( 'avia-shortcodes' ), false, true );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @return void
		 */
		function popup_elements()
		{
			$this->elements = array(
				
				array(
						'type' 	=> 'tab_container', 
						'nodescription' => true
					),
						
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),
				
					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array( 
													$this->popup_key( 'content_slides' ),
													$this->popup_key( 'content_filter' ),
													$this->popup_key( 'content_excerpt' ),
												),
							'nodescription' => true
						),
				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Styling', 'avia_framework' ),
						'nodescription' => true
					),
				
					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array( 
													$this->popup_key( 'styling_columns' ),
													$this->popup_key( 'styling_image' )
												),
							'nodescription' => true
						),
				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced', 'avia_framework' ),
						'nodescription' => true
					),
				
					array(
							'type' 	=> 'toggle_container',
							'nodescription' => true
						),
				
						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_animation_slider' ),
								'nodescription' => true
							),
				
						array(
								'type'			=> 'template',
								'template_id'	=> 'lazy_loading_toggle',
								'lockable'		=> true
							),
				
						array(	
								'type'			=> 'template',
								'template_id'	=> 'screen_options_toggle',
								'lockable'		=> true
							),

						array(	
								'type'			=> 'template',
								'template_id'	=> 'developer_options_toggle',
								'args'			=> array( 'sc' => $this )
							),
				
					array(
							'type' 	=> 'toggle_container_close',
							'nodescription' => true
						),
				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				array(	
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array( 'sc' => $this )
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)
				
				);
			
		}
		
		/**
		 * Create and register templates for easier maintainance
		 * 
		 * @since 4.6.4
		 */
		protected function register_dynamic_templates()
		{
			
			/**
			 * Content Tab
			 * ===========
			 */
			
			$c = array(
						array(
							'name' 	=> __( 'Which Entries?', 'avia_framework' ),
							'desc' 	=> __( 'Select which entries should be displayed by selecting a taxonomy', 'avia_framework' ),
							'id' 	=> 'link',
							'type' 	=> 'linkpicker',
							'fetchTMPL'	=> true,
							'multiple'	=> 6,
							'std'		=> 'category',
							'lockable'	=> true,
							'subtype'	=> array( __( 'Display Entries from:', 'avia_framework' ) => 'taxonomy' )
						)
				
				);
			
			if( current_theme_supports( 'add_avia_builder_post_type_option' ) )
			{
				$element = array(	
								'type'			=> 'template',
								'template_id'	=> 'avia_builder_post_type_option',
								'lockable'		=> true,
							);
						
				array_unshift( $c, $element );
			}
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Select Slide Content', 'avia_framework' ),
								'content'		=> $c 
							),
					);
				
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_slides' ), $template );
			
			$c = array(
						array(	
							'type'			=> 'template',
							'template_id' 	=> 'wc_options_non_products',
							'lockable'		=> true
						),
				
				
						array(	
							'type'			=> 'template',
							'template_id' 	=> 'date_query',
							'lockable'		=> true
						),
				
						array(
							'name' 	=> __( 'Entry Number', 'avia_framework' ),
							'desc' 	=> __( 'How many items should be displayed?', 'avia_framework' ),
							'id' 	=> 'items',
							'type' 	=> 'select',
							'std' 	=> '9',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( 'All' => '-1' ) ) 
						),

						array(
							'name' 	=> __( 'Offset Number', 'avia_framework' ),
							'desc' 	=> __( 'The offset determines where the query begins pulling posts. Useful if you want to remove a certain number of posts because you already query them with another post slider element.', 'avia_framework' ),
							'id' 	=> 'offset',
							'type' 	=> 'select',
							'std' 	=> '0',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( __( 'Deactivate offset', 'avia_framework') => '0', __( 'Do not allow duplicate posts on the entire page (set offset automatically)', 'avia_framework' ) => 'no_duplicates' ) )
						)
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Filters', 'avia_framework' ),
								'content'		=> $c 
							),
					);
				
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_filter' ), $template );
			
			$c = array(
						array(
							'name' 	=> __( 'Title and Excerpt', 'avia_framework' ),
							'desc' 	=> __( 'Choose if you want to only display the post title or title and excerpt', 'avia_framework' ),
							'id' 	=> 'contents',
							'type' 	=> 'select',
							'std' 	=> 'excerpt',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Title and Excerpt', 'avia_framework' )						=> 'excerpt',
												__( 'Title and Excerpt + Read More Link', 'avia_framework' )	=> 'excerpt_read_more',
												__( 'Only Title', 'avia_framework' )							=> 'title',
												__( 'Only Title + Read More Link', 'avia_framework' )			=> 'title_read_more',
												__( 'Only excerpt', 'avia_framework' )							=> 'only_excerpt',
												__( 'Only excerpt + Read More Link', 'avia_framework' )			=> 'only_excerpt_read_more',
												__( 'No Title and no excerpt', 'avia_framework' )				=> 'no'
											)
						),
				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Excerpt', 'avia_framework' ),
								'content'		=> $c 
							),
					);
				
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_excerpt' ), $template );
			
			
			/**
			 * Styling Tab
			 * ===========
			 */
			
			$c = array(
						array(
							'name' 	=> __( 'Columns', 'avia_framework' ),
							'desc' 	=> __( 'How many columns should be displayed?', 'avia_framework' ),
							'id' 	=> 'columns',
							'type' 	=> 'select',
							'std' 	=> '3',
							'lockable'	=> true,
							'subtype'	=> array(	
												__( '1 Columns', 'avia_framework' )	=> '1',
												__( '2 Columns', 'avia_framework' )	=> '2',
												__( '3 Columns', 'avia_framework' )	=> '3',
												__( '4 Columns', 'avia_framework' )	=> '4',
												__( '5 Columns', 'avia_framework' )	=> '5',
											)
						)
				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Columns', 'avia_framework' ),
								'content'		=> $c 
							),
					);
				
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_columns' ), $template );
			
			$c = array(
						array(
							'name' 	=> __( 'Preview Image Size', 'avia_framework' ),
							'desc' 	=> __( 'Set the image size of the preview images', 'avia_framework' ),
							'id' 	=> 'preview_mode',
							'type' 	=> 'select',
							'std' 	=> 'auto',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Set the preview image size automatically based on column width', 'avia_framework' )	=> 'auto',
												__( 'Choose the preview image size manually (select thumbnail size)', 'avia_framework' )	=> 'custom'
											)
						),

						array(
							'name' 	=> __( 'Select custom preview image size', 'avia_framework' ),
							'desc' 	=> __( 'Choose image size for Preview Image', 'avia_framework' ),
							'id' 	=> 'image_size',
							'type' 	=> 'select',
							'std' 	=> 'portfolio',
							'lockable'	=> true,
							'required' 	=> array( 'preview_mode', 'equals', 'custom' ),
							'subtype'	=>  AviaHelper::get_registered_image_sizes( array( 'logo' ) )
						)
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Preview Image', 'avia_framework' ),
								'content'		=> $c 
							),
					);
				
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_image' ), $template );
			
			/**
			 * Advanced Tab
			 * ===========
			 */
			
			$c = array(
/*				
						array(
							'name' 	=> __( 'Post Slider Transition', 'avia_framework' ),
							'desc' 	=> __( 'Choose the transition for your Post Slider.', 'avia_framework' ),
							'id' 	=> 'animation',
							'type' 	=> 'select',
							'std' 	=> 'fade',
							'subtype'	=> array(
												__( 'Slide', 'avia_framework' )	=> 'slide',
												__( 'Fade', 'avia_framework' )	=> 'fade'
											),
						),
*/
				
				
						array(
							'name' 	=> __( 'Autorotation active?', 'avia_framework' ),
							'desc' 	=> __( 'Check if the slideshow should rotate by default', 'avia_framework' ),
							'id' 	=> 'autoplay',
							'type' 	=> 'select',
							'std' 	=> 'no',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Yes', 'avia_framework' )	=> 'yes',
												__( 'No', 'avia_framework' )	=> 'no'
											)
						),

						array(
							'name' 	=> __( 'Slideshow autorotation duration', 'avia_framework' ),
							'desc' 	=> __( 'Slideshow will rotate every X seconds', 'avia_framework' ),
							'id' 	=> 'interval',
							'type' 	=> 'select',
							'std' 	=> '5',
							'lockable'	=> true,
							'required'	=> array( 'autoplay', 'equals', 'yes' ),
							'subtype'	=> array( '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10', '15'=>'15', '20'=>'20', '30'=>'30', '40'=>'40', '60'=>'60', '100'=>'100' )
						)
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Slider Animation', 'avia_framework' ),
								'content'		=> $c 
							),
					);
				
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation_slider' ), $template );
			
		}

		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 *
		 * @param array $params this array holds the default values for $content and $args.
		 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
		 */
		function editor_element( $params )
		{
			$params = parent::editor_element( $params );
			$params['content'] = null; //remove to allow content elements
			
			return $params;
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @return string $output returns the modified html string
		 */
		function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{	
			$default = avia_post_slider::get_defaults();
			
			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );
			
			$screen_sizes = AviaHelper::av_mobile_sizes( $atts );
			
			if( isset( $atts['img_scrset'] ) && 'disabled' == $atts['img_scrset'] )
			{
				Av_Responsive_Images()->force_disable( 'disabled' );
			}
			
			if( isset( $atts['link'] ) )
			{
				$atts['link'] = explode(',', $atts['link'], 2 );
				$atts['taxonomy'] = $atts['link'][0];

				if( isset( $atts['link'][1] ) )
				{
					$atts['categories'] = $atts['link'][1];
				}
			}

			$atts['class'] = $meta['el_class'];
			$atts['el_id'] = $meta['custom_el_id'];
			
			$atts = array_merge( $atts, $screen_sizes );
			
			/**
			 * @since 4.5.5
			 * @return array
			 */
			$atts = apply_filters( 'avf_post_slider_args', $atts, $this->config['shortcode'], $this );
				   
			$slider = new avia_post_slider( $atts );
			$slider->query_entries();
			$html = $slider->html();
			
			Av_Responsive_Images()->force_disable( 'reset' );
			
			return $html;
		}

	}
}




if ( ! class_exists( 'avia_post_slider' ) )
{
	class avia_post_slider
	{	
		/**
		 * @since < 4.0
		 * @var int 
		 */
		static public $slide = 0;
		
		/**
		 *
		 * @since < 4.0
		 * @var array 
		 */
		protected $atts;
		
		/**
		 *
		 * @since < 4.0
		 * @var WP_Query 
		 */
		protected $entries;
		
		/**
		 *
		 * @since 4.7.6.4
		 * @var int 
		 */
		protected $current_page;

		/**
		 * @since < 4.0
		 * @param array $atts
		 */
		public function __construct( $atts = array() )
		{
			$this->entries = array();
			$this->current_page = 1;

			$this->atts = shortcode_atts( avia_post_slider::get_defaults(), $atts, 'av_postslider' );
		}
		
		
		/**
		 * @since 4.5.5
		 */
		public function __destruct() 
		{
			unset( $this->atts );
			unset( $this->entries );
		}
		
		/**
		 * Returns the defaults array
		 * 
		 * @since 4.8
		 * @return array
		 */
		static public function get_defaults() 
		{
			$defaults = array(
							'type'				=> 'slider', // can also be used as grid
							'style'				=> '', //no_margin
							'columns'			=> '4',
							'items'				=> '16',
							'taxonomy'			=> 'category',
							'wc_prod_visible'	=> '',
							'wc_prod_hidden'	=> '',
							'wc_prod_featured'	=> '',
							'prod_order_by'		=> '',
							'prod_order'		=> '',
							'show_meta_data'	=> '',		//	'' | 'always' | 'on_empty_title' | 'on_empty_content' (use filter to change)
							'post_type'			=> get_post_types(),
							'contents'			=> 'excerpt',
							'preview_mode'		=> 'auto',
							'image_size'		=> 'portfolio',
							'autoplay'			=> 'no',
							'animation'			=> 'fade',
							'paginate'			=> 'no',
							'use_main_query_pagination' => 'no',
							'interval'			=> 5,
							'class'				=> '',
							'el_id'				=> '',
							'categories'		=> array(),
							'custom_query'		=> array(),
							'offset'			=> 0,
							'custom_markup'		=> '',
							'av_display_classes'	=> '',
							'date_filter'		=> '',	
							'date_filter_start'	=> '',
							'date_filter_end'	=> '',
							'date_filter_format'	=> 'yy/mm/dd',		//	'yy/mm/dd' | 'dd-mm-yy'	| yyyymmdd
							'lazy_loading'			=> 'disabled',
							'img_scrset'			=> ''
			);
			
			return $defaults;
		}

		/**
		 * 
		 * @since < 4.0
		 * @return string
		 */
		public function html()
		{
			$output = '';

			if( empty( $this->entries ) || empty( $this->entries->posts ) ) 
			{
				return $output;
			}
			
			avia_post_slider::$slide ++;
			extract( $this->atts );

			if( $preview_mode == 'auto' ) 
			{
				$image_size = 'portfolio';
			}
			
			$extraClass 		= 'first';
			$grid 				= 'one_third';
			$post_loop_count 	= 1;
			$loop_counter		= 1;
			$autoplay 			= $autoplay == 'no' ? false : true;
			$total				= $columns % 2 ? 'odd' : 'even';
			$blogstyle 			= function_exists( 'avia_get_option' ) ? avia_get_option( 'blog_global_style', '' ) : '';
			$excerpt_length 	= 60;
			
			
			if( $blogstyle !== '' )
			{
				$excerpt_length = 240;
			}
			
			switch( $columns )
			{
				case '1': 
					$grid = 'av_fullwidth';  
					if( $preview_mode == 'auto' ) 
					{
						$image_size = 'large'; 
					}
					break;
				case '2': 
					$grid = 'av_one_half';   
					break;
				case '3': 
					$grid = 'av_one_third';  
					break;
				case '4': 
					$grid = 'av_one_fourth'; 
					if( $preview_mode == 'auto' ) 
					{
						$image_size = 'portfolio_small'; 
					}
					break;
				case '5': 
					$grid = 'av_one_fifth';  
					if( $preview_mode == 'auto' ) 
					{
						$image_size = 'portfolio_small'; 
					}
					break;
			}


			$data = AviaHelper::create_data_string( array( 'autoplay' => $autoplay, 'interval' => $interval, 'animation' => $animation, 'show_slide_delay' => 90 ) );

			$thumb_fallback = '';
            $markup = avia_markup_helper( array( 'context' => 'blog', 'echo' => false, 'custom_markup' => $custom_markup ) );
			$output .= "<div {$el_id} {$data} class='avia-content-slider avia-content-{$type}-active avia-content-slider" . avia_post_slider::$slide . " avia-content-slider-{$total} {$class} {$av_display_classes}' $markup>";
			$output .= 		"<div class='avia-content-slider-inner'>";

			foreach( $this->entries->posts as $index => $entry )
			{
				$the_id 	= $entry->ID;
				$parity		= $loop_counter % 2 ? 'odd' : 'even';
				$last       = $this->entries->post_count == $post_loop_count ? ' post-entry-last ' : '';
				$post_class = "post-entry post-entry-{$the_id} slide-entry-overview slide-loop-{$post_loop_count} slide-parity-{$parity} {$last}";
				$link		= get_post_meta( $the_id , '_portfolio_custom_link', true ) != '' ? get_post_meta( $the_id , '_portfolio_custom_link_url', true ) : get_permalink( $the_id );
				$excerpt	= '';
				$title  	= '';
				$show_meta  = ! is_post_type_hierarchical( $entry->post_type );
				$commentCount = get_comments_number( $the_id );
				$format 	= get_post_format( $the_id );
				
				$post_thumbnail_id = get_post_thumbnail_id( $the_id );
				if( $lazy_loading != 'enabled' )
				{
					Av_Responsive_Images()->add_attachment_id_to_not_lazy_loading( $post_thumbnail_id );
				}
				
				$thumbnail = get_the_post_thumbnail( $the_id, $image_size );

				if( empty( $format ) ) 
				{
					$format = 'standard';
				}

				if( $thumbnail )
				{
					$thumb_fallback = $thumbnail;
					$thumb_class	= 'real-thumbnail';
				}
				else
				{
					$thumbnail = "<span class=' fallback-post-type-icon' " . av_icon_string($format).  "></span><span class='slider-fallback-image'>{{thumbnail}}</span>";
					$thumb_class = 'fake-thumbnail';
				}


				$permalink = '<div class="read-more-link"><a href="' . get_permalink( $the_id ) . '" class="more-link">' . __( 'Read more', 'avia_framework' ) . '<span class="more-link-arrow"></span></a></div>';
				$prepare_excerpt = ! empty( $entry->post_excerpt ) ? $entry->post_excerpt : avia_backend_truncate( $entry->post_content, apply_filters( 'avf_postgrid_excerpt_length', $excerpt_length ) , apply_filters( 'avf_postgrid_excerpt_delimiter' , ' ' ), '…', true, '' );

				if( $format == 'link' )
				{
					$current_post = array();
					$current_post['content'] = $entry->post_content;
					$current_post['title'] = avia_wp_get_the_title( $entry );

					if( function_exists( 'avia_link_content_filter' ) )
					{
						$current_post = avia_link_content_filter( $current_post );
					}

					$link = $current_post['url'];
				}

				switch( $contents )
				{
					case 'excerpt':
							$excerpt = $prepare_excerpt;
							$title = avia_wp_get_the_title( $entry );
							break;
					case 'excerpt_read_more':
							$excerpt = $prepare_excerpt;
							$excerpt .= $permalink;
							$title = avia_wp_get_the_title( $entry );
							break;
					case 'title':
							$excerpt = '';
							$title = avia_wp_get_the_title( $entry );
							break;
					case 'title_read_more':
							$excerpt = $permalink;
							$title = avia_wp_get_the_title( $entry );
							break;
					case 'only_excerpt':
							$excerpt = $prepare_excerpt;
							$title = '';
							break;
					case 'only_excerpt_read_more':
							$excerpt = $prepare_excerpt;
							$excerpt .= $permalink;
							$title = '';
							break;
					case 'no':
							$excerpt = '';
							$title = '';
							break;
				}

				/**
				 * @since < 4.0
				 * @param string $title
				 * @param WP_Post $entry
				 * @return string
				 */
				$title = apply_filters( 'avf_postslider_title', $title, $entry );
				
				
				/**
				 * @since 4.7.3.1
				 * @param string $image_link_title
				 * @param WP_Post $entry
				 * @return string
				 */
				$image_link_title = apply_filters( 'avf_postslider_link_title_attr', esc_attr( avia_wp_get_the_title( $entry ) ), $entry );
				
				

				if( $loop_counter == 1 ) 
				{
					$output .= "<div class='slide-entry-wrap'>";
				}
				
				$post_format = get_post_format( $the_id ) ? get_post_format( $the_id ) : 'standard';

				$markup = avia_markup_helper( array( 'context' => 'entry', 'echo' => false, 'id' => $the_id, 'custom_markup' => $custom_markup ) );
				$output .= "<article class='slide-entry flex_column {$style} {$post_class} {$grid} {$extraClass} {$thumb_class} post-format-{$post_format}' $markup>";
				$output .= $thumbnail ? "<a href='{$link}' data-rel='slide-" . avia_post_slider::$slide . "' class='slide-image' title='{$image_link_title}'>{$thumbnail}</a>" : '';
					
				if( $post_format == 'audio' )
				{	
					$current_post = array();
					$current_post['content'] = $entry->post_content;
					$current_post['title'] = avia_wp_get_the_title( $entry );
					$current_post['id'] = $entry->ID;

					$current_post = apply_filters( 'post-format-' . $post_format, $current_post, $entry );

					if( ! empty( $current_post['before_content'] ) ) 
					{
						$output .= '<div class="big-preview single-big audio-preview">' . $current_post['before_content'] . '</div>';
					}
				}
					
				$output .= "<div class='slide-content'>";

				$markup = avia_markup_helper( array( 'context' => 'entry_title', 'echo' => false, 'id' => $the_id, 'custom_markup' => $custom_markup ) );
				$output .= '<header class="entry-content-header">';
				$meta_out = '';

				if( ! empty( $title ) || in_array( $show_meta_data, array( 'always', 'on_empty_title' ) ) )
				{
					if( $show_meta )
					{
						$taxonomies  = get_object_taxonomies( get_post_type( $the_id ) );
						$cats = '';
						$excluded_taxonomies = array_merge( get_taxonomies( array( 'public' => false ) ), array( 'post_tag', 'post_format' ) );
						$excluded_taxonomies = apply_filters( 'avf_exclude_taxonomies', $excluded_taxonomies, get_post_type( $the_id ), $the_id );

						if( ! empty( $taxonomies ) )
						{
							foreach( $taxonomies as $taxonomy )
							{
								if( ! in_array( $taxonomy, $excluded_taxonomies ) )
								{
									$cats .= get_the_term_list( $the_id, $taxonomy, '', ', ', '' ) . ' ';
								}
							}
						}

						if( ! empty( $cats ) )
						{
							$meta_out .= '<span class="blog-categories minor-meta">';
							$meta_out .=	$cats;
							$meta_out .= '</span>';
						}
					}
						
					/**
					 * Allow to change default output of categories - by default supressed for setting Default(Business) blog style
					 * 
					 * @since 4.0.6
					 * @param string $blogstyle						'' | 'elegant-blog' | 'elegant-blog modern-blog'
					 * @param avia_post_slider $this
					 * @return string								'show_elegant' | 'show_business' | 'use_theme_default' | 'no_show_cats' 
					 */
					$show_cats = apply_filters( 'avf_postslider_show_catergories', 'use_theme_default', $blogstyle, $this );

					switch( $show_cats )
					{
						case 'no_show_cats':
							$new_blogstyle = '';
							break;
						case 'show_elegant':
							$new_blogstyle = 'elegant-blog';
							break;
						case 'show_business':
							$new_blogstyle = 'elegant-blog modern-blog';
							break;
						case 'use_theme_default':
						default:
							$new_blogstyle = $blogstyle;
							break;
					}
						
						//	elegant style
					if( ( strpos( $new_blogstyle, 'modern-blog' ) === false ) && ( $new_blogstyle != '' ) )
					{
						$output .= $meta_out;
					}

					$default_heading = 'h3';
					$args = array(
								'heading'		=> $default_heading,
								'extra_class'	=> ''
							);

					$extra_args = array( $this, $index, $entry );

					/**
					 * @since 4.5.5
					 * @return array
					 */
					$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

					$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
					$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';


					$output .=  "<{$heading} class='slide-entry-title entry-title {$css}' $markup><a href='{$link}' title='" . esc_attr( strip_tags( $title ) ) . "'>{$title}</a></{$heading}>";

						//	modern business style
					if( ( strpos( $new_blogstyle, 'modern-blog' ) !== false ) && ( $new_blogstyle != '' ) ) 
					{
						$output .= $meta_out;
					}

					$output .= '<span class="av-vertical-delimiter"></span>';
				}
                    
				$output .= '</header>';

				if( ( $show_meta && ! empty( $excerpt ) ) || in_array( $show_meta_data, array( 'always', 'on_empty_content' ) ) )
				{
					$meta  = "<div class='slide-meta'>";
					if ( $commentCount != '0' || comments_open( $the_id ) && $entry->post_type != 'portfolio' )
					{
						$link_add = $commentCount === '0' ? '#respond' : '#comments';
						$text_add = $commentCount === '1' ? __( 'Comment', 'avia_framework' ) : __( 'Comments', 'avia_framework' );

						$meta .= "<div class='slide-meta-comments'><a href='{$link}{$link_add}'>{$commentCount} {$text_add}</a></div><div class='slide-meta-del'>/</div>";
					}
					$markup = avia_markup_helper( array( 'context' => 'entry_time', 'echo' => false, 'id' => $the_id, 'custom_markup' => $custom_markup ) );
					$meta .= "<time class='slide-meta-time updated' $markup>" . get_the_time( get_option( 'date_format' ), $the_id ) . '</time>';
					$meta .= '</div>';

					if( strpos( $blogstyle, 'elegant-blog' ) === false )
					{
						$output .= $meta;
						$meta = '';
					}
				}
				
				$markup = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'id' => $the_id, 'custom_markup' => $custom_markup ) );
				$excerpt = apply_filters( 'avf_post_slider_entry_excerpt', $excerpt, $prepare_excerpt, $permalink, $entry );
				$output .= ! empty( $excerpt ) ? "<div class='slide-entry-excerpt entry-content' {$markup}>{$excerpt}</div>" : '';

				$output .= '</div>';
				$output .= '<footer class="entry-footer">';
				if( ! empty( $meta ) ) 
				{
					$output .= $meta;
				}
				$output .= '</footer>';

				$output .= av_blog_entry_markup_helper( $the_id );

				$output .= '</article>';

				$loop_counter ++;
				$post_loop_count ++;
				$extraClass = '';

				if( $loop_counter > $columns )
				{
					$loop_counter = 1;
					$extraClass = 'first';
				}

				if( $loop_counter == 1 || ! empty( $last ) )
				{
					$output .= '</div>';
				}
			}

			$output .= 		'</div>';

			if( $post_loop_count -1 > $columns && $type == 'slider' )
			{
				$output .= $this->slide_navigation_arrows();
			}
			
			global $wp_query;
			
			$avia_pagination = '';
			
            if( $use_main_query_pagination == 'yes' && $paginate == 'yes' )
            {
                $avia_pagination = avia_pagination( $wp_query->max_num_pages, 'nav' );
            }
            else if( $paginate == 'yes' )
            {
                $avia_pagination = avia_pagination( $this->entries, 'nav', 'avia-element-paging', $this->current_page );
            }

            if( ! empty( $avia_pagination ) ) 
			{
				$output .= "<div class='pagination-wrap pagination-slider'>{$avia_pagination}</div>";
			}


            $output .= '</div>';

			$output = str_replace( '{{thumbnail}}', $thumb_fallback, $output );

			wp_reset_query();
			return $output;
		}

		/**
		 * Create arrows to scroll slides
		 * 
		 * @since 4.8.3			reroute to aviaFrontTemplates
		 * @return string
		 */
		protected function slide_navigation_arrows()
		{
			$args = array(
						'context'		=> get_class(),
						'params'		=> $this->atts
					);
			
			return aviaFrontTemplates::slide_navigation_arrows( $args );
		}

		/**
		 * Fetch new entries
		 * 
		 * @since < 4.0
		 * @param array $params
		 */
		public function query_entries( $params = array() )
		{	
			global $avia_config;

			if( empty( $params ) ) 
			{
				$params = $this->atts;
			}

			if( empty( $params['custom_query'] ) )
            {
				$query = array();

				if( ! empty( $params['categories'] ) )
				{
					//get the portfolio categories
					$terms 	= explode( ',', $params['categories'] );
				}
				
				if( $params['use_main_query_pagination'] == 'yes' )
				{
					$this->current_page = ( $params['paginate'] != 'no' ) ? avia_get_current_pagination_number() : 1;
				}
				else
				{
					$this->current_page = ( $params['paginate'] != 'no' ) ? avia_get_current_pagination_number( 'avia-element-paging' ) : 1;
				}

				//if we find no terms for the taxonomy fetch all taxonomy terms
				if( empty( $terms[0] ) || is_null( $terms[0] ) || $terms[0] === 'null' )
				{
					
					$term_args = array( 
								'taxonomy'		=> $params['taxonomy'],
								'hide_empty'	=> true
							);
					/**
					 * To display private posts you need to set 'hide_empty' to false, 
					 * otherwise a category with ONLY private posts will not be returned !!
					 * 
					 * You also need to add post_status 'private' to the query params with filter avia_post_slide_query.
					 * 
					 * @since 4.4.2
					 * @added_by Günter
					 * @param array $term_args 
					 * @param array $params 
					 * @return array
					 */
					$term_args = apply_filters( 'avf_av_postslider_term_args', $term_args, $params );

					$allTax = AviaHelper::get_terms( $term_args );
					
					$terms = array();
					foreach( $allTax as $tax )
					{
						$terms[] = $tax->term_id;
					}

				}

				if( $params['offset'] == 'no_duplicates' )
                {
                    $params['offset'] = false;
                    $no_duplicates = true;
                }
                
                
				//wordpress 4.4 offset fix
				if( $params['offset'] == 0 )
				{
					$params['offset'] = false;
				}
				else
				{	
					//if the offset is set the paged param is ignored. therefore we need to factor in the page number
					$params['offset'] = $params['offset'] + ( ( $this->current_page - 1 ) * $params['items'] );
				}
				
				
                if( empty( $params['post_type'] ) ) 
				{
					$params['post_type'] = get_post_types();
				}
				
                if( is_string($params['post_type'] ) ) 
				{
					$params['post_type'] = explode( ',', $params['post_type'] );
				}

				$orderby = 'date';
				$order = 'DESC';
				
				$date_query = array();
				if( 'date_filter' == $params['date_filter'] )
				{
					$date_query = AviaHelper::add_date_query( $date_query, $params['date_filter_start'], $params['date_filter_end'], $params['date_filter_format'] );
				}
				
				// Meta query - replaced by Tax query in WC 3.0.0
				$meta_query = array();
				$tax_query = array();
				

				// check if taxonomy are set to product or product attributes
				$tax = get_taxonomy( $params['taxonomy'] );
				
				if( class_exists( 'WooCommerce' ) && is_object( $tax ) && isset( $tax->object_type ) && in_array( 'product', (array) $tax->object_type ) )
				{
					$avia_config['woocommerce']['disable_sorting_options'] = true;
					
					avia_wc_set_out_of_stock_query_params( $meta_query, $tax_query, $params['wc_prod_visible'] );
					avia_wc_set_hidden_prod_query_params( $meta_query, $tax_query, $params['wc_prod_hidden'] );
					avia_wc_set_featured_prod_query_params( $meta_query, $tax_query, $params['wc_prod_featured'] );
					
						//	sets filter hooks !!
					$ordering_args = avia_wc_get_product_query_order_args( $params['prod_order_by'], $params['prod_order'] );
							
					$orderby = $ordering_args['orderby'];
					$order = $ordering_args['order'];
					$params['meta_key'] = $ordering_args['meta_key'];
				}

				if( ! empty( $terms ) )
				{
					$tax_query[] =  array(
										'taxonomy' 	=>	$params['taxonomy'],
										'field' 	=>	'id',
										'terms' 	=>	$terms,
										'operator' 	=>	'IN'
								);
				}				
				
				$query = array(	
								'orderby'		=> $orderby,
								'order'			=> $order,
								'paged'			=> $this->current_page,
								'post_type'		=> $params['post_type'],
//								'post_status'	=> 'publish',
								'offset'		=> $params['offset'],
								'posts_per_page' => $params['items'],
								'post__not_in'	=> ( ! empty( $no_duplicates ) ) ? $avia_config['posts_on_current_page'] : array(),
								'meta_query'	=> $meta_query,
								'tax_query'		=> $tax_query,
								'date_query'	=> $date_query
							);
																
			}
			else
			{
				$query = $params['custom_query'];
			}
			
			if( ! empty( $params['meta_key'] ) )
			{
				$query['meta_key'] = $params['meta_key'];
			}

			/**
			 * @used_by				config-bbpress\config.php	avia_remove_bbpress_post_type_from_query()			10
			 * @used_by				config-wpml\config.php		avia_translate_ids_from_query						10
			 * 
			 * @since < 4.0
			 * @param array $query
			 * @param array $params
			 * @return array 
			 */
			$query = apply_filters( 'avia_post_slide_query', $query, $params );

			@$this->entries = new WP_Query( $query ); //@ is used to prevent errors caused by wpml

		    // store the queried post ids in
            if( $this->entries->post_count > 0 )
            {
				foreach( $this->entries->posts as $entry )
                {
                    $avia_config['posts_on_current_page'][] = $entry->ID;
                }
            }
			
			if( function_exists( 'WC' ) )
			{
				avia_wc_clear_catalog_ordering_args_filters();
				$avia_config['woocommerce']['disable_sorting_options'] = false;
			}
		}
	}
}
