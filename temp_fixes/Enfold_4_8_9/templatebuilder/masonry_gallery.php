<?php
/**
 * Masonry Gallery
 *
 * Shortcode that allows to display a fullwidth masonry of any post type
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_masonry_gallery' ) )
{
	class avia_sc_masonry_gallery extends aviaShortcodeTemplate
	{
		/**
		 * @since 4.5.7.2
		 * @var int
		 */
		static protected $gallery_count = 0;

		/**
		 * Save avia_masonry objects for reuse. As we need to access the same object when creating the post css file in header,
		 * create the styles and HTML creation. Makes sure to get the same id.
		 *
		 *			$element_id	=> avia_masonry
		 *
		 * @since 4.8.4
		 * @var array
		 */
		protected $obj_masonry = array();

		/**
		 * @since 4.8.9
		 * @param AviaBuilder $builder
		 */
		public function __construct( AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->obj_masonry = array();
		}

		/**
		 * @since 4.8.9
		 */
		public function __destruct()
		{
			unset( $this->obj_masonry );

			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['is_fullwidth']	= 'yes';
			$this->config['base_element']	= 'yes';

			/**
			 * inconsistent behaviour up to 4.2: a new element was created with a close tag, after editing it was self closing !!!
			 * @since 4.2.1: We make new element self closing now because no id='content' exists.
			 */
			$this->config['self_closing']	= 'yes';

			$this->config['name']			= __( 'Masonry Gallery', 'avia_framework' );
			$this->config['tab']			= __( 'Media Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-masonry-gallery.png';
			$this->config['order']			= 5;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_masonry_gallery';
			$this->config['tooltip'] 	    = __( 'Display a fullwidth masonry/grid gallery', 'avia_framework' );
			$this->config['drag-level'] 	= 3;
			$this->config['preview'] 		= false;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'always';
		}

		function admin_assets()
		{
			add_action( 'wp_ajax_avia_ajax_masonry_more', array( 'avia_masonry', 'handler_ajax_load_more' ) );
			add_action( 'wp_ajax_nopriv_avia_ajax_masonry_more', array( 'avia_masonry', 'handler_ajax_load_more' ) );
		}


		function extra_assets()
		{
			wp_enqueue_style( 'avia-module-masonry', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/masonry_entries/masonry_entries.css', array( 'avia-layout' ), false );

			wp_enqueue_style( 'avia-siteloader', get_template_directory_uri() . '/css/avia-snippet-site-preloader.css', array( 'avia-layout' ), false );

			wp_enqueue_script( 'avia-module-isotope', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/portfolio/isotope.js', array( 'avia-shortcodes' ), false , true );

			wp_enqueue_script( 'avia-module-masonry', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/masonry_entries/masonry_entries.js', array( 'avia-module-isotope' ), false, true );
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
													$this->popup_key( 'content_entries' ),
													$this->popup_key( 'content_captions' )
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
													$this->popup_key( 'styling_masonry' ),
													$this->popup_key( 'styling_columns' ),
													$this->popup_key( 'styling_pagination' ),
													$this->popup_key( 'styling_colors' )
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
								'template_id'	=> $this->popup_key( 'advanced_animation' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_link' )
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
							'name'		=> __( 'Edit Gallery', 'avia_framework' ),
							'desc'		=> __( 'Create a new Gallery by selecting existing or uploading new images', 'avia_framework' ),
							'id'		=> 'ids',
							'type'		=> 'gallery',
							'title'		=> __( 'Add/Edit Gallery', 'avia_framework' ),
							'button'	=> __( 'Insert Images', 'avia_framework' ),
							'std'		=> '',
							'modal_class' => 'av-show-image-custom-link',
							'lockable'	=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Select Images', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_entries' ), $template );

			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'masonry_captions',
								'lockable'		=> true,
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Captions', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_captions' ), $template );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Size Settings', 'avia_framework' ),
							'desc'		=> __( 'Here you can select how the masonry should behave and handle the images', 'avia_framework' ),
							'id'		=> 'size',
							'type'		=> 'radio',
							'std'		=> 'flex',
							'lockable'	=> true,
							'options'	=> array(
												'flex'			=> __( 'Flexible Masonry: All images get the same width but are displayed with their original height and width ratio', 'avia_framework' ),
												'fixed'			=> __( 'Perfect Grid: Display a perfect grid where each image has exactly the same size. Images get cropped/stretched if they don\'t fit', 'avia_framework' ),
												'fixed masonry'	=> __( 'Perfect Automatic Masonry: Display a grid where most images get the same size, only very wide images get twice the width and very high images get twice the height. To qualify for &quot;very wide&quot; or &quot;very high&quot; the image must have a aspect ratio of 16:9 or higher', 'avia_framework' ),
											)
						),

						array(
							'name'		=> __( 'Orientation', 'avia_framework' ),
							'desc'		=> __( 'Set the orientation of the cropped preview images', 'avia_framework' ),
							'id'		=> 'orientation',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'size', 'equals', 'fixed' ),
							'subtype'	=> array(
												__( 'Wide Landscape', 'avia_framework' )	=> 'av-orientation-landscape-large',
												__( 'Landscape', 'avia_framework' )			=> '',
												__( 'Square', 'avia_framework' )			=> 'av-orientation-square',
												__( 'Portrait', 'avia_framework' )			=> 'av-orientation-portrait',
												__( 'High Portrait', 'avia_framework' )		=> 'av-orientation-portrait-large',
											)
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'image_size_select',
							'std'			=> 'masonry',
							'lockable'		=> true,
							'multi'			=> true
						),

						array(
							'name'		=> __( 'Gap between elements', 'avia_framework' ),
							'desc'		=> __( 'Select the gap between the elements', 'avia_framework' ),
							'id'		=> 'gap',
							'type'		=> 'select',
							'std'		=> 'large',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No Gap', 'avia_framework' )		=> 'no',
												__( '1 Pixel Gap', 'avia_framework' )	=> '1px',
												__( 'Large Gap', 'avia_framework' )		=> 'large',
											)
						),


				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Masonry Settings', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_masonry' ), $template );

			$c = array(

						array(
								'type'			=> 'template',
								'template_id'	=> 'columns_count_icon_switcher',
								'lockable'		=> true
							),

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
							'name' 	=> __( 'Image Number', 'avia_framework' ),
							'desc' 	=> __( 'How many images should be displayed per page?', 'avia_framework' ),
							'id' 	=> 'items',
							'type' 	=> 'select',
							'std' 	=> '24',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( __( 'All', 'avia_framework' ) => '-1' ) )
						),

						array(
							'name' 	=> __( 'Pagination', 'avia_framework' ),
							'desc' 	=> __( 'Should a pagination or load more option be displayed to view additional images?', 'avia_framework' ),
							'id' 	=> 'paginate',
							'type' 	=> 'select',
							'std' 	=> 'none',
							'lockable'	=> true,
							'required'	=> array( 'items', 'not', '-1' ),
							'subtype'	=> array(
												__( 'Display Pagination', 'avia_framework' )					=> 'pagination',
												__( 'Display "Load More" Button', 'avia_framework' )			=> 'load_more',
												__( 'No option to view additional images', 'avia_framework' )	=> 'none'
											)
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Pagination', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_pagination' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Custom Colors', 'avia_framework' ),
							'desc'		=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id'		=> 'color',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											)
						),

						array(
							'name'		=> __( 'Custom Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_bg',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							//'container_class' => 'av_third av_third_first',
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'custom' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors' ), $template );

			/**
			 * Animation Tab
			 * =============
			 */

			$c = array(
						array(
							'name' 	=> __( 'Image effect', 'avia_framework' ),
							'desc' 	=> __( 'Do you want to add an image overlay effect that gets applied or removed on mouseover?', 'avia_framework' ),
							'id' 	=> 'overlay_fx',
							'type' 	=> 'select',
							'std' 	=> 'active',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Slightly fade in effect', 'avia_framework' )	=> 'active',
												__( 'Fade out effect', 'avia_framework' )			=> 'fade_out',
												__( 'Greyscale effect', 'avia_framework' )			=> 'grayscale',
												__( 'Desaturation effect', 'avia_framework' )		=> 'desaturation',
												__( 'Blur on hover effect', 'avia_framework' )		=> 'bluronhover',
												__( 'No effect', 'avia_framework' )					=> ''
											)
						),

						array(
							'name' 	=> __( 'Animation on load', 'avia_framework' ),
							'desc' 	=> __( 'Should the masonry items load in an animated way?', 'avia_framework' ),
							'id' 	=> 'animation',
							'type' 	=> 'select',
							'std' 	=> 'active',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Animation activated', 'avia_framework' )	=> 'active',
												__( 'Animation deactivated', 'avia_framework' )	=> '',
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Animation', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Image Link', 'avia_framework' ),
							'desc' 	=> __( 'By default images link to a larger image version in a lightbox. You can change this here. A custom link can be added when editing the images in the gallery.', 'avia_framework' ),
							'id' 	=> 'container_links',
							'type' 	=> 'select',
							'std' 	=> 'active',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Lightbox linking active', 'avia_framework' )						=> 'active',
												__( 'Use custom link (fallback is no link)', 'avia_framework' )			=> '',
												__( 'No, don\'t add a link to the images at all', 'avia_framework' )	=> 'no_links',
											)
						),

						array(
							'name'		=> __( 'Custom link destination', 'avia_framework' ),
							'desc'		=> __( 'Select where an existing custom link should be opened.', 'avia_framework' ),
							'id'		=> 'link_dest',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'container_links', 'equals', '' ),
							'subtype'	=> array(
												__( 'Open in same window', 'avia_framework' )		=> '',
												__( 'Open in a new window', 'avia_framework' )		=> '_blank'
											)
						),

						array(
							'name'		=> __( 'Lightbox image description text', 'avia_framework' ),
							'desc'		=> __( 'Select which text defined in the media gallery is displayed below the lightbox image.', 'avia_framework' ),
							'id'		=> 'lightbox_text',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'container_links', 'equals', 'active' ),
							'subtype'	=> array(
												__( 'No text', 'avia_framework' )										=> 'no_text',
												__( 'Image title', 'avia_framework' )									=> '',
												__ ('Image description (or image title if empty)', 'avia_framework' )	=> 'description',
												__( 'Image caption (or image title if empty)', 'avia_framework' )		=> 'caption'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Link Settings', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_link' ), $template );

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
			$params['innerHtml'] .=	AviaPopupTemplates()->get_html_template( 'alb_element_fullwidth_stretch' );

			return $params;
		}

		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 *
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		function editor_sub_element( $params )
		{
			/**
			 * Currently not used because we have no modal_group defined for this element
			 */

//			$img_template 		= $this->update_template( 'img_fakeArg', '{{img_fakeArg}}' );
//			$template 			= $this->update_template( 'title', '{{title}}' );
//			$content 			= $this->update_template( 'content', '{{content}}' );
//
//			$thumbnail = isset( $params['args']['id'] ) ? wp_get_attachment_image( $params['args']['id'] ) : '';
//
//
//			$params['innerHtml']  = '';
//			$params['innerHtml'] .= "<div class='avia_title_container' data-update_element_template='yes'>";
//			$params['innerHtml'] .=		"<span class='avia_slideshow_image' {$img_template} >{$thumbnail}</span>";
//			$params['innerHtml'] .=		"<div class='avia_slideshow_content'>";
//			$params['innerHtml'] .=			"<h4 class='avia_title_container_inner' {$template} >{$params['args']['title']}</h4>";
//			$params['innerHtml'] .=			"<p class='avia_content_container' {$content}>" . stripslashes( $params['content'] ) . '</p>';
//			$params['innerHtml'] .=		'</div>';
//			$params['innerHtml'] .= '</div>';

			return $params;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.4
		 * @since 4.8.9.1					added $ajax
		 * @param array $args
		 * @param boolean $ajax
		 * @return array
		 */
		protected function get_element_styles( array $args, $ajax = false )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			//	Backwards comp. - make sure to provide "old" defaults for options not set
			$default = avia_masonry::default_args( $this->get_default_sc_args() );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$atts['container_class'] = 'av-masonry-gallery' . trim( " {$atts['container_class']}" );

			if( ! isset( $this->obj_masonry[ $element_id ] ) )
			{
				$this->obj_masonry[ $element_id ] = new avia_masonry( $atts, $this );
			}

			$masonry = $this->obj_masonry[ $element_id ];
			$masonry->query_entries_by_id( array(), $ajax );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;
			$result['meta'] = $meta;

			$result = $masonry->get_element_styles( $result, $this );

			return $result;
		}

		/**
		 * Returns output for ajax callback "Load More" button.
		 * Called as callback from class avia_masonry
		 *
		 * @since 4.8.5.1
		 * @param array $atts
		 * @return string
		 */
		public function ajax_load_more( array $atts )
		{
			$args = array(
						'atts'			=> $atts,
						'content'		=> '',
						'shortcodename'	=> $this->config['shortcode']
					);

			$ajax = true;
			$result = $this->get_element_styles( $args, $ajax );

			extract( $result );

			if( 'disabled' == $atts['img_scrset'] )
			{
				Av_Responsive_Images()->force_disable( 'disabled' );
			}

			$masonry = $this->obj_masonry[ $element_id ];
			$output = $masonry->html();

			Av_Responsive_Images()->force_disable( 'reset' );

			return $output;
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
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			if( 'disabled' == $atts['img_scrset'] )
			{
				Av_Responsive_Images()->force_disable( 'disabled' );
			}

			//	Needed to add to surrounding section
			$av_display_classes = $element_styling->responsive_classes_string( 'hide_element', $atts );

			avia_sc_masonry_gallery::$gallery_count ++;
			$skipSecond = false;

			$params['class'] = "main_color {$av_display_classes} {$meta['el_class']}";
			$params['open_structure'] = false;
			$params['id'] = AviaHelper::save_string( $meta['custom_id_val'] , '-', 'av-sc-masonry-gallery-' . avia_sc_masonry_gallery::$gallery_count );

			//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
			if( $meta['index'] == 0 )
			{
				$params['close'] = false;
			}

			if( ! empty( $meta['siblings']['prev']['tag']) && in_array( $meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section ) )
			{
				$params['close'] = false;
			}

			if( $meta['index'] > 0 )
			{
				$params['class'] .= ' masonry-not-first';
			}

			if( $meta['index'] == 0 && get_post_meta( get_the_ID(), 'header', true ) != 'no' )
			{
				$params['class'] .= ' masonry-not-first';
			}

			if( $atts['gap'] == 'no' )
			{
				$params['class'] .= ' avia-no-border-styling';
			}

			$masonry = $this->obj_masonry[ $element_id ];

			/**
			 * Remove custom CSS from element if it is top level (otherwise added twice - $meta['el_class'] )
			 */
			if( ShortcodeHelper::is_top_level() )
			{
				$update = array(
							'custom_class'	=> '',
							'id'			=> ''
				);

				$masonry->update_config( $update );
			}

			$masonry_html = $masonry->html();

			Av_Responsive_Images()->force_disable( 'reset' );

			if( ! ShortcodeHelper::is_top_level() )
			{
				return $masonry_html;
			}

			if( ! empty( $atts['color'] ) && ! empty( $atts['custom_bg'] ) )
			{
				$params['class'] .= ' masonry-no-border';
			}

			$output  = '';
			$output .= avia_new_section( $params );
			$output .=		$masonry_html;
			$output .= '</div><!-- close section -->'; //close section


			//if the next tag is a section dont create a new section from this shortcode
			if( ! empty( $meta['siblings']['next']['tag'] ) && in_array( $meta['siblings']['next']['tag'], AviaBuilder::$full_el ) )
			{
				$skipSecond = true;
			}

			//if there is no next element dont create a new section.
			if( empty( $meta['siblings']['next']['tag'] ) )
			{
				$skipSecond = true;
			}

			if( empty( $skipSecond ) )
			{
				$output .= avia_new_section( array( 'close' => false, 'id' => 'after_masonry' ) );
			}

			return $output;
		}

	}
}
