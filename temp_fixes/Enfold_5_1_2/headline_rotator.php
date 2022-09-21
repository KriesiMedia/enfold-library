<?php
/**
 * Headline Rotator
 *
 * Creates a text rotator for dynamic headings
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_headline_rotator' ) )
{
	class avia_sc_headline_rotator extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @since 4.8.8
		 * @var boolean
		 */
		protected $in_sc_exec;

		/**
		 * @since < 4.5.5
		 * @var int
		 */
		protected $count;

		/**
		 *
		 * @since 4.5.5
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			$this->in_sc_exec = false;
			$this->count = 0;

			parent::__construct( $builder );
		}

		/**
		 * @since 4.5.5
		 */
		public function __destruct()
		{
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

			$this->config['name']			= __( 'Headline Rotator', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-heading.png';
			$this->config['order']			= 83;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_headline_rotator';
			$this->config['shortcode_nested'] = array( 'av_rotator_item' );
			$this->config['tooltip']		= __( 'Creates a text rotator for dynamic headings', 'avia_framework' );
			$this->config['preview']		= 'large';
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Headline Rotator Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'A Headline Rotator Headline Element', 'avia_framework' );

		}

		function extra_assets()
		{
			//load css
			wp_enqueue_style( 'avia-module-rotator', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/headline_rotator/headline_rotator.css', array( 'avia-layout' ), false );

				//load js
			wp_enqueue_script( 'avia-module-rotator', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/headline_rotator/headline_rotator.js', array( 'avia-shortcodes' ), false, true );

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
							'template_id'	=> $this->popup_key( 'content_text' ),
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
													$this->popup_key( 'styling_spacing' ),
													$this->popup_key( 'styling_alignment' ),
													$this->popup_key( 'styling_color' ),
													$this->popup_key( 'styling_font' )
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
								'template_id'	=> $this->popup_key( 'advanced_heading' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'advanced_animation' )
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
							'name' 	=> __( 'Prepended static text', 'avia_framework' ),
							'desc' 	=> __( 'Enter static text that should be displayed before the rotating text', 'avia_framework' ) ,
							'id' 	=> 'before_rotating',
							'std' 	=> __( 'We are ', 'avia_framework' ),
							'type' 	=> 'input',
							'lockable'	=> true,
							'tmpl_set_default'	=> false
						),

						array(
							'name'			=> __( 'Add/Edit rotating text', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the rotating text', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Text Element', 'avia_framework' ),
							'editable_item'	=> true,
							'lockable'		=> true,
							'tmpl_set_default'	=> false,
							'std'			=> array(
													array( 'title' => __( 'great', 'avia_framework' ) ),
													array( 'title' => __( 'smart', 'avia_framework' ) ),
													array( 'title' => __( 'fast', 'avia_framework' ) ),
												),
							'subelements'	=> $this->create_modal()
						),

						array(
							'name' 	=> __( 'Appended static text', 'avia_framework' ),
							'desc' 	=> __( 'Enter static text that should be displayed after the rotating text', 'avia_framework' ) ,
							'id' 	=> 'after_rotating',
							'std' 	=> '',
							'type' 	=> 'input',
							'lockable'	=> true,
							'tmpl_set_default'	=> false
						),

						array(
							'name' 	=> __( 'Activate Multiline?','avia_framework' ),
							'desc' 	=> __( 'Check if prepended, rotating and appended text should each be displayed on its own line', 'avia_framework' ),
							'id' 	=> 'multiline',
							'type' 	=> 'checkbox',
							'std' 	=> '',
							'lockable'	=> true
						),


				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_text' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'margin_padding',
								'name'			=> '',
								'desc'			=> '',
								'content'		=> 'margin',
								'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Spacing', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_spacing' ), $template );


			$c = array(
						array(
							'name' 	=> __( 'Text align', 'avia_framework' ),
							'desc' 	=> __( 'Alignment of the text', 'avia_framework' ),
							'id' 	=> 'align',
							'type' 	=> 'select',
							'std' 	=> 'left',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Center', 'avia_framework' )	=> 'center',
												__( 'Left', 'avia_framework' )		=> 'left',
												__( 'Right', 'avia_framework' )		=> 'right',
											)
						),


				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Alignment', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_alignment' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Custom Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_title',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'lockable'	=> true
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Color', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_color' ), $template );

			$font_size_array = array(
									__( 'Default Size', 'avia_framework' )									=> '',
									__( 'Flexible font size (adjusts to screen width)' , 'avia_framework' )	=> AviaHtmlHelper::number_array( 3, 7, 0.5 , array(), 'vw', '', 'vw' ),
									__( 'Fixed font size' , 'avia_framework' )								=> AviaHtmlHelper::number_array( 10, 150, 1, array(), 'px', '', '' )
								);


			$c = array(
						array(
							'name'			=> __( 'Headline Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the headline in pixel or viewport width.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> $font_size_array,
												'desktop'	=> $font_size_array,
												'medium'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'size',
												'desktop'	=> 'av-desktop-font-size-title',
												'medium'	=> 'av-medium-font-size-title',
												'small'		=> 'av-small-font-size-title',
												'mini'		=> 'av-mini-font-size-title'
											)
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Size', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_font' ), $template );

			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Autorotation duration', 'avia_framework' ),
							'desc' 	=> __( 'Each rotating textblock will be shown the selected amount of seconds.', 'avia_framework' ),
							'id' 	=> 'interval',
							'type' 	=> 'select',
							'std' 	=> '5',
							'lockable'	=> true,
							'subtype'	=> array( '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6', '7'=>'7', '8'=>'8', '9'=>'9', '10'=>'10', '15'=>'15', '20'=>'20', '30'=>'30', '40'=>'40', '60'=>'60', '100'=>'100' )
						),

						array(
							'name' 	=> __( 'Rotation Animation', 'avia_framework' ),
							'desc' 	=> __( 'Select the rotation animation', 'avia_framework' ),
							'id' 	=> 'animation',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Top to bottom', 'avia_framework' )	=> '',
												__( 'Bottom to top', 'avia_framework' )	=> 'reverse',
												__( 'Fade only', 'avia_framework' )		=> 'fade',
												__( 'Typewriter', 'avia_framework' )	=> 'typewriter',
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
							'name' 	=> __( 'HTML Markup', 'avia_framework' ),
							'desc' 	=> __( 'Select which kind of HTML markup you want to apply to set the importance of the headline for search engines', 'avia_framework' ),
							'id' 	=> 'tag',
							'type' 	=> 'select',
							'std' 	=> 'h3',
							'lockable'	=> true,
							'subtype'	=> array( 'H1'=>'h1', 'H2'=>'h2', 'H3'=>'h3', 'H4'=>'h4', 'H5'=>'h5', 'H6'=>'h6', __( 'Paragraph', 'avia_framework' ) => 'p' )
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
							'template_id'	=> $this->popup_key( 'modal_content_text' )
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
							'template_id'	=> $this->popup_key( 'modal_styling_color' )
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
							'template_id'	=> $this->popup_key( 'modal_advanced_link' )
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
							'name'		=> __( 'Rotating Text', 'avia_framework' ),
							'desc'		=> __( 'Enter the rotating text here (Better keep it short)', 'avia_framework' ) ,
							'id'		=> 'title',
							'std'		=> '',
							'type'		=> 'input',
							'lockable'	=> true,
						),


				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_text' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Custom Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id'		=> 'custom_title',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'lockable'	=> true,
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_color' ), $c );

			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Text Link?', 'avia_framework' ),
							'desc'			=> __( 'Do you want to apply a link to the text?', 'avia_framework' ),
							'lockable'		=> true,
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'no_toggle'		=> true
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $c );

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
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked );

			$template = $this->update_template_lockable( 'title', '{{title}}', $locked );

			$params['innerHtml']  = '';
			$params['innerHtml'] .= "<div class='avia_title_container' data-update_element_template='yes'>";
			$params['innerHtml'] .=		"<span {$template} >{$attr['title']}</span>";
			$params['innerHtml'] .= '</div>';

			return $params;
		}

		/**
		 * Returns false by default.
		 * Override in a child class if you need to change this behaviour.
		 *
		 * @since 4.2.1
		 * @param string $shortcode
		 * @return boolean
		 */
		public function is_nested_self_closing( $shortcode )
		{
			if( in_array( $shortcode, $this->config['shortcode_nested'] ) )
			{
				return true;
			}

			return false;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.4
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
							'align'				=> 'left',
							'before_rotating'	=> '',
							'after_rotating'	=> '',
							'interval'			=> '5',
							'tag'				=> 'h3',
							'size'				=> '',
							'custom_title'		=> '',
							'multiline'			=> 'disabled',
							'animation'			=> '',
							'margin'			=> ''
						);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$this->in_sc_exec = true;

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );


			if( is_numeric( $atts['size'] ) )
			{
				$atts['size'] .= 'px';
			}

			//	backwards comp. - prepare responsive font sizes for media query
			$atts['size-title'] = $atts['size'];


			$element_styling->create_callback_styles( $atts );


			$classes = array(
						'av-rotator-container',
						"av-rotation-container-{$atts['align']}",
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );
			$element_styling->add_responsive_font_sizes( 'container-text', 'size-title', $atts, $this );

			if( empty( $atts['after_rotating'] ) && $atts['align'] == 'center' )
			{
				$element_styling->add_classes( 'container', 'av-fixed-rotator-width' );
			}

			if( $atts['animation']  == 'typewriter' )
			{
				$element_styling->add_classes( 'container', 'av-typewriter' );
			}

			if( ! empty( $atts['after_rotating'] ) )
			{
				$element_styling->add_classes( 'container', 'av-after-rotation-text-active' );
			}

			$styles = array(
						'text-align'	=> $atts['align'],
						'color'			=> $atts['custom_title']
					);

			$element_styling->add_styles( 'container', $styles );

			$element_styling->add_responsive_styles( 'container', 'margin', $atts, $this );


			$selectors = array(
						'container'			=> "#top .av-rotator-container.{$element_id}",
						'container-text'	=> "#top #wrap_all .av-rotator-container.{$element_id} .av-rotator-container-inner"
					);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Create custom stylings for items
		 *
		 * @since 4.8.4
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles_item( array $args )
		{
			$result = parent::get_element_styles_item( $args );

			/**
			 * Fixes a problem when 3-rd party plugins call nested shortcodes without executing main shortcode  (like YOAST in wpseo-filter-shortcodes)
			 */
			if( ! $this->in_sc_exec )
			{
				return $result;
			}

			extract( $result );

			$default = array(
							'title' 		=> '',
							'link' 			=> '',
							'linktarget' 	=> '',
							'custom_title' 	=> '',
						);

			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );


			$classes = array(
						'av-rotator-text-single',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );

			$element_styling->add_styles( 'container', array( 'color' => $atts['custom_title'] ) );

			//	#top needed to override <a> tag color setting
			$selectors = array(
						'container'	=> "#top .av-rotator-container .av-rotator-text-single.{$element_id}"
					);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

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

			$this->count = 0;
			$this->subitem_inline_styles = '';

			$multiline = $multiline == 'disabled' ? 'off' : 'on';

			switch( $animation )
			{
				case 'typewriter':
					$animation = 'typewriter';
					break;
				case 'reverse':
					$animation = -1;
					break;
				case 'fade':
					$animation = 0;
					break;
				default:
					$animation = 1;
					break;
			}

			$data = "data-interval='{$interval}' data-animation='{$animation}'";

			if( empty( $after_rotating ) && $align == 'center' )
			{
				$data .= " data-fixWidth='1'";
			}

			//	Process subitems before to get possible inline styles because js code breaks
			$inside = ShortcodeHelper::avia_remove_autop( $content, true );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$item_tag = $element_styling->style_tag_html( $this->subitem_inline_styles, 'sub-' . $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output	 = '';
			$output .= $style_tag;
			$output .= $item_tag;
			$output .=	"<div {$meta['custom_el_id']} class='{$container_class}' {$data}>";
			$output .=		"<{$tag} class='av-rotator-container-inner'>";
			$output .=			apply_filters( 'avia_ampersand', $before_rotating );
			$output .=			"<span class='av-rotator-text av-rotator-multiline-{$multiline}'>";
			$output .=				$inside;
			$output .=			'</span>';
			$output .=			apply_filters( 'avia_ampersand', $after_rotating );
			$output .=		"</{$tag}>";
			$output .=	'</div>';

			$this->subitem_inline_styles = '';
			$this->in_sc_exec = false;

			return $output;
		}

		/**
		 * Shortcode handler
		 *
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_rotator_item( $atts, $content = '', $shortcodename = '' )
		{
			/**
			 * Fixes a problem when 3-rd party plugins call nested shortcodes without executing main shortcode  (like YOAST in wpseo-filter-shortcodes)
			 */
			if( ! $this->in_sc_exec )
			{
				return '';
			}

			$result = $this->get_element_styles_item( compact( array( 'atts', 'content', 'shortcodename' ) ) );

			extract( $result );
			extract( $atts );

			$this->count++;

			$link = AviaHelper::get_url( $link );
			$blank = AviaHelper::get_link_target( $linktarget );

			$tags = ! empty( $link ) ? array( "a href='{$link}' {$blank} ", 'a' ) : array( 'span','span' );

			$this->subitem_inline_styles .= $element_styling->get_style_tag( $element_id, 'rules_only' );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .=	"<{$tags[0]} class='{$container_class} av-rotator-text-single-{$this->count}'>";
			$output .=		ShortcodeHelper::avia_remove_autop( $title , true );
			$output .=	"</{$tags[1]}>";

			return $output;
		}

	}
}
