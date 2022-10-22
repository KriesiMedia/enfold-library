<?php
/**
 * Fullwidth Easy Slider
 *
 * Shortcode dDisplay a simple fullwidth slideshow element
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_slider_full' ) )
{
	class avia_sc_slider_full extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @var int
		 */
		static public $slide_count = 0;

		/**
		 * Save avia_slideshow objects for reuse. As we need to access the same object when creating the post css file in header,
		 * create the styles and HTML creation. Makes sure to get the same id.
		 *
		 *			$element_id	=> avia_slideshow
		 *
		 * @since 4.8.9
		 * @var array
		 */
		protected $obj_slideshow = array();

		/**
		 * @since 4.8.9
		 * @param AviaBuilder $builder
		 */
		public function __construct( AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->obj_slideshow = array();
		}

		/**
		 * @since 4.8.9
		 */
		public function __destruct()
		{
			unset( $this->obj_slideshow );

			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['is_fullwidth']	= 'yes';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Fullwidth Easy Slider', 'avia_framework' );
			$this->config['tab']			= __( 'Media Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-slideshow-full.png';
			$this->config['order']			= 80;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_slideshow_full';
			$this->config['shortcode_nested'] = array( 'av_slide_full' );
			$this->config['tooltip'] 	    = __( 'Display a simple fullwidth slideshow element', 'avia_framework' );
			$this->config['tinyMCE'] 		= array( 'disable' => 'true' );
			$this->config['drag-level'] 	= 1;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['name_item']		= __( 'Fullwidth Easy Slider Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'A Fullwidth Easy Slider image or video item', 'avia_framework' );
		}

		function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow{$min_css}.css", array( 'avia-layout' ), $ver );
			wp_enqueue_style( 'avia-module-slideshow-fullsize', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow_fullsize/slideshow_fullsize{$min_css}.css" , array( 'avia-module-slideshow' ), $ver );

				//load js
			wp_enqueue_script( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
			wp_enqueue_script( 'avia-module-slideshow-video', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/slideshow/slideshow-video{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
								'template_id'	=> $this->popup_key( 'content_entries' )
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
													$this->popup_key( 'styling_slide' ),
													$this->popup_key( 'styling_navigation' ),
													$this->popup_key( 'styling_nav_colors' ),
													$this->popup_key( 'styling_background' )
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
								'template_id'	=> $this->popup_key( 'advanced_animation_slider' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_animation_image' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_privacy' )
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

			$this->register_modal_group_templates();

			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Form Element', 'avia_framework' ),
							'add_label'		=>  __( 'Add single image or video', 'avia_framework' ),
							'container_class'	=> 'avia-element-fullwidth avia-multi-img',
							'std'			=> array(),
							'editable_item'	=> true,
							'lockable'		=> true,
							'tmpl_set_default'	=> false,
							'creator'		=> array(
													'name'		=> __( 'Add Images', 'avia_framework' ),
													'desc'		=> __( 'Here you can add new Images to the slideshow.', 'avia_framework' ),
													'id'		=> 'id',
													'type'		=> 'multi_image',
													'title'		=> __( 'Add multiple Images', 'avia_framework' ),
													'button'	=> __( 'Insert Images', 'avia_framework' ),
													'std'		=> ''
												),
							'subelements'	=> $this->create_modal()
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_entries' ), $c );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Slideshow Image and Video Size', 'avia_framework' ),
							'desc'		=> __( 'Choose image and Video size for your slideshow.', 'avia_framework' ),
							'id'		=> 'size',
							'type'		=> 'select',
							'std'		=> 'featured',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true,
							'subtype'	=>  AviaHelper::get_registered_image_sizes( 1000 )
						),

						array(
							'name'		=> __( 'Slider minimum height in pixel', 'avia_framework' ),
							'desc'		=> __( 'This is helpful on smaller screens if you got a lot of text in your slider', 'avia_framework' ),
							'id'		=> 'min_height',
							'type'		=> 'input',
							'container_class' => 'av_half',
							'std'		=> '0px',
							'lockable'	=> true,
						),

						array(
							'name'		=> __( 'Stretch image to fit the slideshow size?', 'avia_framework' ),
							'desc'		=> __( 'By default the image stretches across the full width of the screen. You can deactivate this behavior and simply align it in the center of the slider', 'avia_framework' ),
							'id'		=> 'stretch',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Yes, stretch the image', 'avia_framework' ) => '',
												__( 'No, dont stretch the image. If the browser window is bigger than the image simply align it centered', 'avia_framework' ) => 'image_no_stretch'
											)
						),

						array(
							'name'		=> __( 'Use first slides caption as permanent caption', 'avia_framework' ),
							'desc'		=> __( 'If checked the caption will be placed on top of the slider. Please be aware that all slideshow link settings and other captions will be ignored then', 'avia_framework' ) ,
							'id'		=> 'perma_caption',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true
						)

					);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Slides', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_slide' ), $template );


			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_controls',
							'lockable'		=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Navigation Controls', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_navigation' ), $template );


			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_navigation_colors',
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Navigation Control Colors', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_nav_colors' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Slideshow Background Image', 'avia_framework' ),
							'desc'		=> __( 'If you are displaying transparent images like pngs you can set a static background image or pattern that will appear behind those pngs.', 'avia_framework' ),
							'id'		=> 'src',
							'type'		=> 'image',
							'title'		=> __( 'Insert Image', 'avia_framework' ),
							'button'	=> __( 'Insert', 'avia_framework' ),
							'std'		=> '',
							'lockable'	=> true,
							'locked'	=> array( 'src', 'attachment', 'attachment_size' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'background_image_position',
							'args'			=> array(
													'id_pos'		=> 'position',
													'id_repeat'		=> 'repeat',
													'repeat_remove'	=> array( 'contain' )
												),
							'lockable'		=> true
						),

						array(
							'name'		=> __( 'Background Attachment', 'avia_framework' ),
							'id'		=> 'attach',
							'type'		=> 'select',
							'std'		=> 'scroll',
							'lockable'	=> true,
							'required'	=> array( 'src', 'not', '' ),
							'subtype'	=> array(
												__( 'Scroll', 'avia_framework' )	=> 'scroll',
												__( 'Fixed', 'avia_framework' )		=> 'fixed'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Slideshow Background', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background' ), $template );

			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_transition',
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_rotation',
							'stop_id'		=> 'autoplay_stopper',
							'lockable'		=> true
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


			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_image_scale',
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Image Animation', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation_image' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Lazy Load Videos', 'avia_framework' ),
							'desc'		=> __( 'Option to only load the preview image of a video slide. The actual videos will only be fetched once the user clicks on the image (Waiting for user interaction speeds up the inital pageload)', 'avia_framework' ),
							'id'		=> 'conditional_play',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Always load videos', 'avia_framework' )		=> '',
												__( 'Wait for user interaction or for a slide with active autoplay to load the video', 'avia_framework' )	=> 'confirm_all'
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Privacy', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_privacy' ), $template );
		}

		/**
		 * Creates the modal popup for a single entry
		 *
		 * @since 4.6.4
		 * @return array
		 */
		protected function create_modal()
		{
			$elements = array(

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
													$this->popup_key( 'modal_content_slidecontent' ),
													$this->popup_key( 'modal_content_fallback' ),
													$this->popup_key( 'modal_content_caption' )
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
													$this->popup_key( 'modal_styling_video' ),
													$this->popup_key( 'modal_styling_caption' ),
													$this->popup_key( 'modal_styling_fonts' ),
													$this->popup_key( 'modal_styling_colors' ),
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
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'modal_advanced_heading' ),
													$this->popup_key( 'modal_advanced_link' ),
													$this->popup_key( 'modal_advanced_overlay' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array(
												'sc'			=> $this,
												'modal_group'	=> true
											)
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)
				);

			return $elements;
		}

		/**
		 * Register all templates for the modal group popup
		 *
		 * @since 4.6.4
		 */
		protected function register_modal_group_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Which type of slide is this?', 'avia_framework' ),
							'id'		=> 'slide_type',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Image Slide', 'avia_framework' )	=> 'image',
												__( 'Video Slide', 'avia_framework' )	=> 'video',
											)
						),

						array(
							'name'		=> __( 'Choose another Image', 'avia_framework' ),
							'desc'		=> __( 'Either upload a new, or choose an existing image from your media library', 'avia_framework' ),
							'id'		=> 'id',
							'type'		=> 'image',
							'fetch'		=> 'id',
							'title'		=> __( 'Change Image', 'avia_framework' ),
							'button'	=> __( 'Change Image', 'avia_framework' ),
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'slide_type', 'is_empty_or', 'image' ),
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'video',
							'required'		=> array( 'slide_type', 'equals', 'video' ),
							'id'			=> 'video',
							'args'			=> array(
													'sc'	=> $this
												),
							'lockable'		=> true,
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Select Slide Content', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_slidecontent' ), $template );

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_fallback_image',
							'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Fallback images', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_fallback' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Caption Title', 'avia_framework' ),
							'desc'		=> __( 'Enter a caption title for the slide here', 'avia_framework' ) ,
							'id'		=> 'title',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Caption Text', 'avia_framework' ),
							'desc'		=> __( 'Enter some additional caption text', 'avia_framework' ) ,
							'id'		=> 'content',
							'type'		=> 'textarea',
							'std'		=> '',
							'lockable'	=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Caption', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_caption' ), $template );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Video Size', 'avia_framework' ),
							'desc'		=> __( "By default the video will try to match the default slideshow size that was selected in the slider settings at 'Slideshow Image and Video Size'", 'avia_framework' ),
							'id'		=> 'video_format',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'slide_type', 'equals', 'video' ),
							'subtype'	=> array(
												__( 'Try to match the default slideshow size (Video will not be cropped, but black borders will be visible at each side)', 'avia_framework' )				=> '',
												__( 'Try to match the default slideshow size but stretch the video to fill the whole slider (video will be cropped at top and bottom)', 'avia_framework' )	=> 'stretch',
												__( 'Show the full Video without cropping',  'avia_framework' ) =>'full',
											)
						),

						array(
							'name'		=> __( 'Video Aspect Ratio', 'avia_framework' ),
							'desc'		=> __( 'In order to calculate the correct height and width for the video slide you need to enter a aspect ratio (width:height). usually: 16:9 or 4:3.', 'avia_framework' ) . '<br/>' . __( 'If left empty 16:9 will be used', 'avia_framework' ),
							'id'		=> 'video_ratio',
							'type'		=> 'input',
							'std'		=> '16:9',
							'lockable'	=> true,
							'required'	=> array( 'video_format', 'not', '' )
						),


						array(
								'type'			=> 'template',
								'template_id'	=> 'slideshow_player',
								'required'		=> array( 'slide_type', 'equals', 'video' ),
								'lockable'		=> true
							),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Video Settings', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_video' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Caption Positioning', 'avia_framework' ),
							'id'		=> 'caption_pos',
							'type'		=> 'select',
							'std'		=> 'caption_bottom',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Right Framed', 'avia_framework' )			=> 'caption_right caption_right_framed caption_framed',
												__( 'Left Framed', 'avia_framework' )			=> 'caption_left caption_left_framed caption_framed',
												__( 'Bottom Framed', 'avia_framework' )			=> 'caption_bottom caption_bottom_framed caption_framed',
												__( 'Center Framed', 'avia_framework' )			=> 'caption_center caption_center_framed caption_framed',
												__( 'Right without Frame', 'avia_framework' )	=> 'caption_right',
												__( 'Left without Frame', 'avia_framework' )	=> 'caption_left',
												__( 'Bottom without Frame', 'avia_framework' )	=> 'caption_bottom',
												__( 'Center without Frame', 'avia_framework' )	=> 'caption_center'
											),
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Caption', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_caption' ), $template );

			$c = array(
						array(
							'name'			=> __( 'Caption Title Font Size', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the titles.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_title_size',
												'desktop'	=> 'av-desktop-font-size-title',
												'medium'	=> 'av-medium-font-size-title',
												'small'		=> 'av-small-font-size-title',
												'mini'		=> 'av-mini-font-size-title'
											)
						),

						array(
							'name'			=> __( 'Caption Content Font Size', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the titles.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 90, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 90, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 90, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 90, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 90, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_content_size',
												'desktop'	=> 'av-desktop-font-size',
												'medium'	=> 'av-medium-font-size',
												'small'		=> 'av-small-font-size',
												'mini'		=> 'av-mini-font-size'
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Sizes', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_fonts' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Font Colors', 'avia_framework' ),
							'desc'		=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id'		=> 'font_color',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )	=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name'		=> __( 'Custom Caption Title Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_title',
							'type'		=> 'colorpicker',
							'std'	=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Custom Caption Content Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_content',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_colors' ), $template );

			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'h2',
							'context'			=> __CLASS__,
							'lockable'			=> true
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Heading Tag', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_heading' ), $template );

			$c = array(

						array(
							'type'				=> 'template',
							'template_id'		=> 'slideshow_button_links',
							'lockable'			=> true
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $template );

			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'slideshow_overlay',
								'lockable'		=> true
							),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Overlay', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_overlay' ), $template );

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
			return $params;
		}

		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 * @param array $params this array holds the default values for $content and $args.
		 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
		 */
		function editor_sub_element( $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			$content = $params['content'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked, $content );

			$img_template = $this->update_option_lockable( array( 'id', 'img_fakeArg' ), $locked );
			$title_templ = $this->update_option_lockable( 'title', $locked );
			$content_tmpl = $this->update_option_lockable( 'content', $locked );
			$video_tmpl = $this->update_option_lockable( 'video', $locked );

			$thumbnail = isset( $attr['id'] ) ? wp_get_attachment_image( $attr['id'] ) : '';

			$params['innerHtml']  = '';
			$params['innerHtml'] .= '<div class="avia_title_container" data-update_element_template="yes">';
			$params['innerHtml'] .=		'<div ' . $this->class_by_arguments_lockable( 'slide_type', $attr, $locked ) . '>';
			$params['innerHtml'] .=			"<span class='avia_slideshow_image' {$img_template} >{$thumbnail}</span>";
			$params['innerHtml'] .=			'<div class="avia_slideshow_content">';
			$params['innerHtml'] .=				"<h4 class='avia_title_container_inner' {$title_templ} >{$attr['title']}</h4>";
			$params['innerHtml'] .=				"<p class='avia_content_container' {$content_tmpl}>" . stripslashes( $content ) . '</p>';
			$params['innerHtml'] .=				"<small class='avia_video_url' {$video_tmpl}>" . stripslashes( $attr['video'] ) . '</small>';
			$params['innerHtml'] .=			'</div>';
			$params['innerHtml'] .=		'</div>';
			$params['innerHtml'] .= '</div>';

			return $params;
		}

		/**
		 *
		 * @since 4.8.9
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			//	@since 4.9 - bugfix for cloned element where av_uid was not changed
			if( current_theme_supports( 'avia_post_css_slideshow_fix' ) )
			{
				$result['element_id'] = 'av-' . md5( $result['element_id'] . $result['content'] );
			}

			extract( $result );

			$default = array(
						'size'				=> 'featured',
						'animation'			=> 'slide',
						'transition_speed'	=> '',
						'ids'				=> '',
						'autoplay'			=> 'false',
						'interval'			=> 5,
						'src'				=> '',
						'position'			=> 'top left',
						'repeat'			=> 'no-repeat',
						'attach'			=> 'scroll',
						//'easing'			=> 'easeInOutQuint',
						'stretch'			=> '',
						'control_layout'	=> 'av-control-default',
						'perma_caption'		=> '',
						'autoplay_stopper'	=> '',
						'min_height'		=> '0px',
						'lazy_loading'		=> 'disabled',
						'img_scrset'		=> ''
				);

			// Backwards comp. - make sure to provide "old" defaults for options not set and override with default options provided
			$default = array_merge( avia_slideshow::default_args(), $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' ) );


			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$add = array(
						'handle'		=> $shortcodename,
						'content'		=> ShortcodeHelper::shortcode2array( $content, 1 ),
						'class'			=> '',
						'custom_markup'	=> '',
						'el_id'			=> '',
						'heading_tag'	=> '',
						'heading_class'	=> ''
				);

			$defaults = array_merge( $default, $add );

			$atts = shortcode_atts( $defaults, $atts, $this->config['shortcode'] );

			foreach( $atts['content'] as $key => &$item )
			{
				$item_def = $this->get_default_modal_group_args();
				Avia_Element_Templates()->set_locked_attributes( $item['attr'], $this, $this->config['shortcode_nested'][0], $item_def, $locked, $item['content'] );
			}

			unset( $item );

			if( ! isset( $this->obj_slideshow[ $element_id ] ) )
			{
				$this->obj_slideshow[ $element_id ] = new avia_slideshow( $atts, $this );
			}

			$slideshow = $this->obj_slideshow[ $element_id ];

			$update = array(
							'class'				=> ! empty( $meta['custom_class'] ) ? $meta['custom_class'] : '',
							'custom_markup'		=> ! empty( $meta['custom_markup'] ) ? $meta['custom_markup'] : '',
//							'el_id'				=> ! empty( $meta['custom_el_id'] ) ? $meta['custom_el_id'] : '',
//							'heading_tag'		=> ! empty( $meta['heading_tag'] ) ? $meta['heading_tag'] : '',
//							'heading_class'		=> ! empty( $meta['heading_class'] ) ? $meta['heading_class'] : '',
						);

			$atts = $slideshow->update_config( $update );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;
			$result['meta'] = $meta;

			$result = $slideshow->get_element_styles( $result );

			return $result;
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

			$background = '';
			$class = '';

			if( $src != '' )
			{
				if( $repeat == 'stretch' )
				{
					$background .= 'background-repeat: no-repeat; ';
					$class .= ' avia-full-stretch';
				}
				else
				{
					$background .= "background-repeat: {$repeat}; ";
				}

				$background .= "background-image: url({$src}); ";
				$background .= "background-attachment: {$attach}; ";
				$background .= "background-position: {$position}; ";
			}

			if( $background )
			{
				$params['bg'] = $background;
			}

			$skipSecond = false;
			avia_sc_slider_full::$slide_count ++;
			$av_display_classes = $element_styling->responsive_classes_string( 'hide_element', $atts );

			$params['class'] = "avia-fullwidth-slider main_color avia-shadow {$av_display_classes} {$meta['el_class']} {$class}";
			$params['open_structure'] = false;

			//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
			if( isset( $meta['index'] ) && $meta['index'] == 0 )
			{
				$params['close'] = false;
			}

			if( ! empty( $meta['siblings']['prev']['tag'] ) && in_array( $meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section ) )
			{
				$params['close'] = false;
			}

			if( isset( $meta['index'] ) && $meta['index'] > 0 )
			{
				$params['class'] .= ' slider-not-first';
			}

			$params['id'] = AviaHelper::save_string( $meta['custom_id_val'], '-', 'full_slider_' . avia_sc_slider_full::$slide_count );

			$slideshow = $this->obj_slideshow[ $element_id ];
			$slideshow->set_extra_class( $atts['stretch'] );


			$output  = '';
			$output .= avia_new_section( $params );
			$output .=		$slideshow->html();
			$output .= '</div>'; //close section

			Av_Responsive_Images()->force_disable( 'reset' );

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
				$output .= avia_new_section( array( 'close' => false, 'id' => 'after_full_slider_' . avia_sc_slider_full::$slide_count ) );
			}

			return $output;
		}
	}
}



