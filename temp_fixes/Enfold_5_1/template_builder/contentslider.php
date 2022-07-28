<?php
/**
 * Content Slider
 *
 * Shortcode that display a content slider element
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_sc_content_slider' ) )
{
	class avia_sc_content_slider extends aviaShortcodeTemplate
	{
		/**
		 * Save avia_content_slider objects for reuse. As we need to access the same object when creating the post css file in header,
		 * create the styles and HTML creation. Makes sure to get the same id.
		 *
		 *			$element_id	=> avia_content_slider
		 *
		 * @since 4.8.9
		 * @var array
		 */
		protected $obj_content_slider = array();

		/**
		 * @since 4.8.9
		 * @param \AviaBuilder $builder
		 */
		public function __construct( \AviaBuilder $builder )
		{
			parent::__construct($builder);

			$this->obj_content_slider = array();
		}

		/**
		 * @since 4.8.9
		 */
		public function __destruct()
		{
			unset( $this->obj_content_slider );

			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Content Slider', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-contentslider.png';
			$this->config['order']			= 83;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_content_slider';
			$this->config['shortcode_nested'] = array( 'av_content_slide' );
			$this->config['tooltip'] 	    = __( 'Display a content slider element', 'avia_framework' );
			$this->config['preview'] 		= false;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['name_item']		= __( 'Content Slider Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'A Content Slider Element Item', 'avia_framework' );
		}

		function extra_assets()
		{
			//load css
			wp_enqueue_style( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/slideshow/slideshow.css', array( 'avia-layout' ), false );
			wp_enqueue_style( 'avia-module-postslider', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/postslider/postslider.css', array( 'avia-layout' ), false );
			wp_enqueue_style( 'avia-module-slideshow-contentpartner', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/contentslider/contentslider.css', array( 'avia-module-slideshow' ), false );

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
								'template_id'	=> $this->popup_key( 'content_slides' ),
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
													$this->popup_key( 'styling_slider' ),
													$this->popup_key( 'styling_controls' ),
													$this->popup_key( 'styling_nav_colors' ),
													$this->popup_key( 'styling_margin' ),
													$this->popup_key( 'styling_colors' ),
													$this->popup_key( 'styling_background' ),
													'border_toggle',
													'box_shadow_toggle'
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
								'template_id'	=> $this->popup_key( 'advanced_animation' ),
								'nodescription' => true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_heading' ),
								'nodescription' => true
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
							'name'		=> __( 'Heading', 'avia_framework' ),
							'desc'		=> __( 'Do you want to display a heading above the slides?', 'avia_framework' ),
							'id'		=> 'heading',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
						),

						array(
							'name'			=> __( 'Add/Edit Slides', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the slides you want to display.', 'avia_framework' ),
							'id'			=> 'content',
							'type'			=> 'modal_group',
							'modal_title'	=> __( 'Edit Form Element', 'avia_framework' ),
							'std'			=> array(
													array( 'title' => __( 'Slide 1', 'avia_framework' ), 'tags' => '' ),
													array( 'title' => __( 'Slide 2', 'avia_framework' ), 'tags' => '' ),

												),
							'editable_item'	=> true,
							'lockable'		=> true,
							'tmpl_set_default'	=> false,
							'subelements'	=> $this->create_modal()
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_slides' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Columns', 'avia_framework' ),
							'desc'		=> __( 'How many slide columns should be displayed?', 'avia_framework' ),
							'id'		=> 'columns',
							'type'		=> 'select',
							'std'		=> '1',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 2, 6, 1, array( __( '1 Column', 'avia_framework' )	=> '1', ), ' ' . __( 'Columns', 'avia_framework' ) ),
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Slider', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_slider' ), $template );


			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_controls_small',
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_controls' ), $template );

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_navigation_colors',
							'lockable'		=> true,
							'required'		=> array( 'control_layout', 'parent_in_array', ',av-control-default' ),
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
								'type'			=> 'template',
								'template_id'	=> 'margin_padding',
								'name'			=> '',
								'name_margin'	=> __( 'Slide Margin', 'avia_framework' ),
								'name_padding'	=> __( 'Content Padding', 'avia_framework' ),
								'desc_margin'	=> __( 'Set a distance between the slides - needed when using box shadow outside.', 'avia_framework' ),
								'desc_margin'	=> __( 'Set a distance between the content and slide borders.', 'avia_framework' ),
								'lockable'		=> true
							)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Margin And Padding', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_margin' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Font Colors', 'avia_framework' ),
							'desc'		=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id'		=> 'font_color',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name'		=> __( 'Custom Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => 'av_half av_half_first',
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Background Color Setting', 'avia_framework' ),
							'desc'		=> __( 'Select to use the themes default color or apply custom ones', 'avia_framework' ),
							'id'		=> 'background_color',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )					=> '',
												__( 'Simple background color', 'avia_framework' )	=> 'bg_simple',
												__( 'Gradient background', 'avia_framework' )		=> 'bg_grad',
										),
						),

						array(
							'name'		=> __( 'Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select the background color', 'avia_framework' ),
							'id'		=> 'bg_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '#ffffff',
							'lockable'	=> true,
							'required'	=> array( 'background_color', 'equals', 'bg_simple' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'gradient_colors',
							'lockable'		=> true,
							'container_class'	=> array( '', 'av_third av_third_first', 'av_third', 'av_third' ),
							'required'		=> array( 'background_color', 'equals', 'bg_grad' )
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background' ), $template );


			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_transition',
							'desc'			=> __( 'Select the transition for your content slider', 'avia_framework' ),
							'select_trans'	=> array( 'slide', 'fade' ),
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_rotation',
							'desc'			=> __( 'Select if the content slider should rotate by default', 'avia_framework' ),
							'stop_id'		=> 'autoplay_stopper',
							'lockable'		=> true
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
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'h3',
							'context'			=> __CLASS__,
							'lockable'			=> true,
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Heading Tag', 'avia_framework' ),
								'content'		=> $c
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_heading' ), $template );

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
							'template_id'	=> $this->popup_key( 'modal_content_slide' )
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
													$this->popup_key( 'modal_advanced_link' )
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
							'name'		=> __( 'Slide Title', 'avia_framework' ),
							'desc'		=> __( 'Enter the slide title here (Better keep it short)', 'avia_framework' ),
							'id'		=> 'title',
							'type'		=> 'input',
							'std'		=> 'Slide Title',
							'lockable'	=> true,
							'tmpl_set_default'	=> false
						),

						array(
							'name'		=> __( 'Slide Content', 'avia_framework' ),
							'desc'		=> __( 'Enter some content here', 'avia_framework' ),
							'id'		=> 'content',
							'type'		=> 'tiny_mce',
							'std'		=> __( 'Slide Content goes here', 'avia_framework' ),
							'lockable'	=> true,
							'tmpl_set_default'	=> false
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_slide' ), $c );

			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'h3',
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
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Title Link?', 'avia_framework' ),
							'desc'			=> __( 'Where should your title link to?', 'avia_framework' ),
							'lockable'		=> true,
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' )
						),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $c );

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
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode'], $default, $locked );

			$template = $this->update_template_lockable( 'heading', ' - <strong>{{heading}}</strong>', $locked );
			$heading = ! empty( $attr['heading'] ) ? "- <strong>{$attr['heading']}</strong>" : '';

			$params = parent::editor_element( $params );

			$params['innerHtml'] .= "<div class='avia-element-label' {$template} data-update_element_template='yes'>{$heading}</div>";

			return $params;
		}

		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 *
		 * @param array $params this array holds the default values for $content and $args.
		 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
		 */
		function editor_sub_element( $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked );

			$template = $this->update_template_lockable( 'title', '{{title}}', $locked );

			$params['innerHtml']  = '<div data-update_element_template="yes">';
			$params['innerHtml'] .=		"<div class='avia_title_container' {$template}>{$attr['title']}</div>";
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

			//	@since 4.9.1 - bugfix for cloned element where av_uid was not changed
			if( current_theme_supports( 'avia_post_css_slideshow_fix' ) )
			{
				$result['element_id'] = 'av-' . md5( $result['element_id'] . $result['content'] );
			}

			extract( $result );

			$default = array(
						'type'						=> 'slider',
						'navigation'				=> 'arrows',
						'control_layout'			=> 'av-control-default',
						'nav_visibility_desktop'	=> '',
						'animation'					=> 'fade',
						'autoplay'					=> 'false',
						'interval'					=> 5,

						'heading'			=> '',
						'columns'			=> 3,
						'font_color'		=> '',
						'color'				=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );
			$meta = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $atts, $meta );

			$add = array(
					'handle'			=> $shortcodename,
					'content'			=> ShortcodeHelper::shortcode2array( $content, 1 ),
					'class'				=> '',
					'custom_markup'		=> '',
					'el_id'				=> '',
					'heading_tag'		=> '',
					'heading_class'		=> '',
					'caller'			=> $this
				);

			$defaults = array_merge( $default, $add );

			$atts = shortcode_atts( $defaults, $atts, $this->config['shortcode'] );

			foreach( $atts['content'] as $key => &$item )
			{
				$item_def = $this->get_default_modal_group_args();
				Avia_Element_Templates()->set_locked_attributes( $item['attr'], $this, $this->config['shortcode_nested'][0], $item_def, $locked, $item['content'] );
			}

			unset( $item );

			if( ! isset( $this->obj_content_slider[ $element_id ] ) )
			{
				$this->obj_content_slider[ $element_id ] = new avia_content_slider( $atts, $this );
			}

			$content_slider = $this->obj_content_slider[ $element_id ];

			$update = array(
							'class'				=> ! empty( $meta['el_class'] ) ? $meta['el_class'] : '',
							'custom_markup'		=> ! empty( $meta['custom_markup'] ) ? $meta['custom_markup'] : '',
							'el_id'				=> ! empty( $meta['custom_el_id'] ) ? $meta['custom_el_id'] : '',
							'heading_tag'		=> ! empty( $meta['heading_tag'] ) ? $meta['heading_tag'] : '',
							'heading_class'		=> ! empty( $meta['heading_class'] ) ? $meta['heading_class'] : '',
						);

			$atts = $content_slider->update_config( $update );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;
			$result['meta'] = $meta;

			$result = $content_slider->get_element_styles( $result );

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

			$content_slider = $this->obj_content_slider[ $result['element_id'] ];
			$html = $content_slider->html();

			return $html;
		}

	}
}


if ( ! class_exists( 'avia_content_slider' ) )
{
	class avia_content_slider extends \aviaBuilder\base\aviaSubItemQueryBase
	{
		use \aviaBuilder\traits\scSlideshowUIControls;

		/**
		 * slider count for the current page
		 *
		 * @var int
		 */
		static public $slider = 0;

		/**
		 * Grid layout class string - avoid multiple init in subitem loop
		 *
		 * @since 4.8.9
		 * @var string
		 */
		protected $grid_class;

		/**
		 * @since 4.8.9					added $sc_context
		 * @param array $atts
		 * @param aviaShortcodeTemplate $sc_context
		 */
		public function __construct( array $atts, aviaShortcodeTemplate $sc_context = null )
		{
			parent::__construct( $atts, $sc_context, avia_content_slider::default_args() );

			$this->grid_class = null;
		}

		/**
		 *
		 * @since 4.5.7.2
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 * Returns the defaults array for this class
		 *
		 * @since 4.8.9
		 * @return array
		 */
		static public function default_args( array $args = array() )
		{
			$default = array(
							'type'						=> 'grid',
							'navigation'				=> 'arrows',
							'control_layout'			=> 'av-control-default',
							'nav_visibility_desktop'	=> '',
							'animation'					=> 'fade',
							'autoplay'					=> 'false',
							'interval'					=> 5,
							'autoplay_stopper'			=> '',
							'manual_stopper'			=> '',
							'show_slide_delay'			=> 30,			//	hardcoded for slider
							'handle'					=> '',
							'heading'					=> '',
							'border'					=> '',
							'columns'					=> 3,
							'class'						=> '',
							'custom_markup'				=> '',
							'css_id'					=> '',
							'content'					=> array(),
							'styling'					=> '',
							'el_id'						=> '',
							'heading_tag'				=> '',
							'heading_class'				=> '',
							'caller'					=> null
						);

			$default = array_merge( $default, $args );

			/**
			 * @since 4.8.9
			 * @param array $default
			 * @return array
			 */
			return apply_filters( 'avf_content_slider_defaults', $default );
		}

		/**
		 * Create custom stylings
		 *
		 * Attention: Due to paging we cannot add any backgrouund images to selectors !!!!
		 * =========
		 *
		 * @since 4.8.9
		 * @param array $result
		 * @return array
		 */
		public function get_element_styles( array $result )
		{
			extract( $result );

			$element_styling->create_callback_styles( $this->config );

			$classes = array(
						'avia-content-slider-element-container',
						$element_id,
						$this->config['border'],
						'avia-content-slider-element-' . $this->config['type'],
						'avia-content-slider',
						'avia-smallarrow-slider',
						"avia-content-{$this->config['type']}-active"
					);

			$classes[] = $this->config['columns'] % 2 ? 'avia-content-slider-odd' : 'avia-content-slider-even';

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes( 'container', $this->config['class'] );

			$element_styling->add_responsive_classes( 'container', 'hide_element', $this->config );


			$ui_args = array(
						'element_id'		=> $element_id,
						'element_styling'	=> $element_styling,
						'atts'				=> $this->config,
						'autoplay_option'	=> 'true',
						'context'			=> __CLASS__,
						'slider_nav'		=> 'small'
					);

			$this->addSlideshowAttributes( $ui_args );


			if( $this->config['font_color'] == 'custom' )
			{
				$element_styling->add_classes( 'container', 'av_inherit_color' );
				$element_styling->add_styles( 'container', array( 'color' => $this->config['color'] ) );
			}

			switch( $this->config['background_color'] )
			{
				case 'bg_simple':
					$element_styling->add_styles( 'slide', array( 'background-color' => $this->config['bg_color'] ) );
					break;
				case 'bg_grad';
					$element_styling->add_callback_styles( 'slide', array( 'gradient_color' ) );
					break;
			}

			$element_styling->add_callback_styles( 'slide', array( 'border', 'border_radius', 'box_shadow' ) );
			$element_styling->add_responsive_styles( 'slide', 'margin', $this->config, $this->sc_context );
			$element_styling->add_responsive_styles( 'slide', 'padding', $this->config, $this->sc_context );

			// gets cut off !!
			if( 1 == $this->config['columns'] && 'outside' ==  $this->config['box_shadow'] )
			{
				$element_styling->add_styles( 'inner', array( 'overflow' => 'visible' ) );
			}

			$selectors = array(
						'container'			=> ".avia-content-slider-element-container.{$element_id}",
						'inner'				=> ".avia-content-slider-element-container.{$element_id} .avia-content-slider-inner",
						'slide'				=> ".avia-content-slider-element-container.{$element_id} .slide-entry"
					);

			$element_styling->add_selectors( $selectors );

			foreach( $this->config['content'] as $index => $slide )
			{
				$args = array(
							'atts'			=> $slide['attr'],
							'content'		=> $slide['content'],
							'shortcodename'	=> $slide['shortcode'],
							'default'		=> $this->default_item_atts
						);

				$result_item = $this->get_element_styles_item( $args );

				$element_styling->add_subitem_styling( $result_item['element_id'], $result_item['element_styling'] );
			}

			//	save data for later HTML output
			$this->element_id = $element_id;
			$this->element_styles = $element_styling;

			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Create custom item stylings
		 * Items are called in object of main shortcode attributes
		 *
		 * @since 4.8.9
		 * @param array $args
		 * @return array
		 */
		public function get_element_styles_item( array $args )
		{
			$result = $this->sc_context->get_element_styles_query_item( $args );

			extract( $result );


			if( is_null( $this->grid_class ) )
			{
				switch( $this->config['columns'] )
				{
					case '1':
						$this->grid_class = 'av_fullwidth';
						break;
					case '2':
						$this->grid_class = 'av_one_half';
						break;
					case '3':
						$this->grid_class = 'av_one_third';
						break;
					case '4':
						$this->grid_class = 'av_one_fourth';
						break;
					case '5':
						$this->grid_class = 'av_one_fifth';
						break;
					case '6':
						$this->grid_class = 'av_one_sixth';
						break;
					default:
						$this->grid_class = 'av_one_third';
						break;
				}
			}


			$classes = array(
						'slide-entry',
						$element_id,
						'flex_column',
						$this->grid_class,
						'post-entry',
						'slide-entry-overview'
					);

			$element_styling->add_classes( 'container', $classes );



			$selectors = array(
						'container'			=> ".avia-content-slider-element-container .slide-entry.{$element_id}"
					);

			$element_styling->add_selectors( $selectors );

			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 *
		 * @return string
		 */
		public function html()
		{
			avia_content_slider::$slider++;

			if( empty( $this->config['content'] ) )
			{
				return '';
			}

			//	fallback - code no longer supported since 4.8.8
			if( is_null( $this->element_styles ) )
			{
				_deprecated_function( 'avia_content_slider::html()', '4.8.9', 'Calling this function without post css support does not work any longer.' );

				return '';
			}

			extract( $this->config );

			$default_heading = ! empty( $heading_tag ) ? $heading_tag : 'h3';
			$args = array(
						'heading'		=> $default_heading,
						'extra_class'	=> $heading_class
					);

			$extra_args = array( $this, 'slider_title' );

			/**
			 * @since 4.5.5
			 * @return array
			 */
			$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

			$heading1 = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
			$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : $heading_class;

			$extraClass = 'first';
			$slide_loop_count = 1;
			$loop_counter = 1;
			$heading = ! empty( $this->config['heading'] ) ? "<{$heading1} class='{$css}'>{$this->config['heading']}</{$heading1}>" : '&nbsp;';
			$slide_count = count( $content );

			$heading_class = '';
			if( $heading == '&nbsp;' )
			{
				$heading_class .= ' no-content-slider-heading ';
			}

			$style_tag = $this->element_styles->get_style_tag( $this->element_id );
			$container_class = $this->element_styles->get_class_string( 'container' );
			$data_slideshow_options = $this->element_styles->get_data_attributes_json_string( 'container', 'slideshow-options' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div {$el_id} class='{$container_class} avia-content-slider" . avia_content_slider::$slider . "' {$data_slideshow_options}>";
			$output .=		"<div class='avia-smallarrow-slider-heading {$heading_class}'>";
			$output .=			"<div class='new-special-heading'>{$heading}</div>";

			if( $slide_count > $columns && $type == 'slider' && $navigation != 'no' )
			{
				//	we need this for swipe event - hidden with css
				$output .= $this->slide_navigation_arrows();

				if( $navigation == 'dots' )
				{
					$output .= $this->slide_navigation_dots();
				}
			}

			$output .=			'</div>';

			$output .=			'<div class="avia-content-slider-inner">';

			$markup_entry = avia_markup_helper( array( 'context' => 'entry', 'echo' => false, 'custom_markup' => $custom_markup ) );
			$markup_title = avia_markup_helper( array( 'context' => 'entry_title', 'echo' => false, 'custom_markup' => $custom_markup ) );
			$markup_content = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'custom_markup' => $custom_markup ) );

			foreach( $content as $key => $value )
			{
				$link = '';
				$linktarget = '';

				$meta = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $value['attr'] );

				extract( $value['attr'] );

				$link = AviaHelper::get_url( $link );
				$blank = AviaHelper::get_link_target( $linktarget );

				$parity = $loop_counter % 2 ? 'odd' : 'even';
				$last = $slide_count == $slide_loop_count ? ' post-entry-last ' : '';
				$post_class = "slide-loop-{$slide_loop_count} slide-parity-{$parity} {$last}";

				if( $loop_counter == 1 )
				{
					$output .= '<div class="slide-entry-wrap">';
				}

				//	add item container data
				$item_info = $this->element_styles->get_subitem_styling_info( $slide_loop_count - 1 );
				$container_class = $item_info['element_styling']->get_class_string( 'container' );

				$output .=		"<section class='{$container_class} {$post_class} {$extraClass}' {$markup_entry}>";

				$default_heading = ! empty( $meta['heading_tag'] ) ? $meta['heading_tag'] : 'h3';
				$args = array(
							'heading'		=> $default_heading,
							'extra_class'	=> $meta['heading_class']
						);

				$extra_args = array( $this, 'slider_entry' );

				/**
				 * @since 4.5.5
				 * @return array
				 */
				$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

				$heading1 = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
				$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : $meta['heading_class'];

				$output .= ! empty( $title ) ? "<{$heading1} class='slide-entry-title entry-title {$css}' {$markup_title}>" : '';
				$output .= ( ! empty( $link ) && ! empty( $title ) ) ? "<a href='{$link}' $blank title='" . esc_attr( $title ) . "'>{$title}</a>" : $title;
				$output .= ! empty( $title ) ? "</{$heading1}>" : '';
				$output .= ! empty( $value['content'] ) ? "<div class='slide-entry-excerpt entry-content' {$markup_content}>" . ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $value['content'] ) ) . '</div>' : '';

				$output .=		'</section>';

				$loop_counter ++;
				$slide_loop_count ++;
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

			$output .=		'</div>';
			$output .= '</div>';

			return $output;
		}

		/**
		 * Create arrows to scroll content slides
		 *
		 * @since 4.8.3			reroute to aviaFrontTemplates
		 * @return string
		 */
		protected function slide_navigation_arrows()
		{
			$args = array(
						'context'	=> get_class(),
						'params'	=> $this->config
					);

			return aviaFrontTemplates::slide_navigation_arrows( $args );
		}

		/**
		 *
		 * @return string
		 */
		protected function slide_navigation_dots()
		{
			$args = array(
						'total_entries'		=> count( $this->config['content'] ),
						'container_entries'	=> $this->config['columns'],
						'context'			=> get_class(),
						'params'			=> $this
					);

			return aviaFrontTemplates::slide_navigation_dots( $args );
		}
	}
}
