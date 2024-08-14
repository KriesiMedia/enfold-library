<?php
/**
 * Accordion and toggles
 *
 * Creates toggles or accordions
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_toggle', false ) )
{
	class avia_sc_toggle extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @var int
		 */
		static protected $toggle_id = 1;

		/**
		 *
		 * @var int
		 */
		static protected $counter = 1;

		/**
		 *
		 * @var int
		 */
		static protected $initial = 0;

		/**
		 *
		 * @var array
		 */
		static protected $tags = array();

		/**
		 * Google search only accepts 1 Tag “FAQPage”
		 *
		 * @since 5.0
		 * @var int
		 */
		static protected $faq_count = 0;

		/**
		 *
		 * @since 4.8.8
		 * @var boolean
		 */
		protected $in_sc_exec;

		/**
		 *
		 * @since 4.9
		 * @var string
		 */
		protected $heading_tag;

		/**
		 *
		 * @since 4.9
		 * @var string
		 */
		protected $heading_class;

		/**
		 *
		 * @since 4.5.5
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			$this->in_sc_exec = false;
			$this->heading_tag = '';
			$this->heading_class = '';

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
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Accordion', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-accordion.png';
			$this->config['order']			= 70;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_toggle_container';
			$this->config['shortcode_nested'] = array( 'av_toggle' );
			$this->config['tooltip']		= __( 'Creates toggles or accordions (can be used for FAQ)', 'avia_framework' );
			$this->config['preview']		= 'large';
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Accordion Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'An Accordion Item (toggle, accordions, FAQ)', 'avia_framework' );
		}

		protected function admin_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );

			wp_register_script('avia_tab_toggle_js', AviaBuilder::$path['assetsURL'] . "js/avia-tab-toggle{$min_js}.js", array( 'avia_modal_js' ), $ver, true );
			Avia_Builder()->add_registered_admin_script( 'avia_tab_toggle_js' );
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-toggles', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/toggles/toggles{$min_css}.css", array( 'avia-layout' ), $ver );

				//load js
			wp_enqueue_script( 'avia-module-toggles', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/toggles/toggles{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @return void
		 */
		protected function popup_elements()
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
													$this->popup_key( 'content_togles' ),
													$this->popup_key( 'content_behaviour' )
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
													$this->popup_key( 'styling_toggles' ),
													$this->popup_key( 'styling_colors' ),
													$this->popup_key( 'styling_font_sizes' )
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
							'name'			=> __( 'Add/Edit Toggles', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the toggles you want to display.', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Form Element', 'avia_framework' ),
							'editable_item'	=> true,
							'lockable'		=> true,
							'tmpl_set_default'	=> false,
							'std'			=> array(
													array( 'title' => __( 'Toggle 1', 'avia_framework' ), 'tags' => '' ),
													array( 'title' => __( 'Toggle 2', 'avia_framework' ), 'tags' => '' ),
												),
							'subelements'	=> $this->create_modal()
						),

						array(
							'name' 	=> __( 'Use as FAQ Page (SEO improvement)', 'avia_framework' ),
							'desc' 	=> __( 'Select if content is used as FAQ and add schema.org markup to support Google Search. You must enable theme option &quot;Automated Schema.org HTML Markup&quot; (SEO tab). For valid structured HTML only one FAQ section allowed per page - you can activate &quot;Sorting&quot; and group questions if needed.', 'avia_framework' ),
							'id' 	=> 'faq_markup',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'tmpl_set_default'	=> false,
							'subtype'	=> array(
												__( 'No markup needed', 'avia_framework' )	=> '',
												__( 'Add FAQ markup', 'avia_framework' )	=> 'faq_markup'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Toggles', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_togles' ), $template );


			$c = array(
						array(
							'name' 	=> __( 'Initial Open', 'avia_framework' ),
							'desc' 	=> __( 'Enter the Number of the Accordion Item that should be open initially. Set to Zero if all should be close on page load', 'avia_framework' ),
							'id' 	=> 'initial',
							'type' 	=> 'input',
							'std' 	=> '0',
							'lockable'	=> true,
						),

						array(
							'name' 	=> __( 'Behavior', 'avia_framework' ),
							'desc' 	=> __( 'Should only one toggle be active at a time and the others be hidden or can multiple toggles be open at the same time?', 'avia_framework' ),
							'id' 	=> 'mode',
							'type' 	=> 'select',
							'std' 	=> 'accordion',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Only one toggle open at a time (Accordion Mode)', 'avia_framework' )	=> 'accordion',
												__( 'Multiple toggles open allowed (Toggle Mode)', 'avia_framework' )		=> 'toggle'
											)
						),

						array(
							'name' 	=> __( 'Sorting', 'avia_framework' ),
							'desc' 	=> __( 'Display the toggle sorting menu? (You also need to add a number of tags to each toggle to make sorting possible)', 'avia_framework' ),
							'id' 	=> 'sort',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No Sorting', 'avia_framework' )		=> '',
												__( 'Sorting Active', 'avia_framework' )	=> 'true'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Behaviour', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_behaviour' ), $template );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Styling', 'avia_framework' ),
							'desc' 	=> __( 'Select the styling of the toggles', 'avia_framework' ),
							'id' 	=> 'styling',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )	=> '',
												__( 'Minimal', 'avia_framework' )	=> 'av-minimal-toggle',
												__( 'Elegant', 'avia_framework' )	=> 'av-elegant-toggle'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Toggles Styling', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_toggles' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'colors',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'font_color',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'required'	=> array( 'colors', 'equals', 'custom' ),
							'container_class'	=> 'av_third av_third_first'
						),

						array(
							'name' 	=> __( 'Custom Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'background_color',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'required'	=> array( 'colors', 'equals', 'custom' ),
							'container_class'	=> 'av_third',
						),

						array(
							'name' 	=> __( 'Custom Border Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom border color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'border_color',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'required'	=> array( 'colors', 'equals', 'custom' ),
							'container_class'	=> 'av_third',
						),

						array(
							'name' 	=> __( 'Custom +/- Icon Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom color for the toggle icon. Leave empty to use default', 'avia_framework' ),
							'id' 	=> 'toggle_icon_color',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'required'	=> array( 'colors', 'equals', 'custom' ),
							'container_class'	=> 'av_third av_third_first',
						),

						array(
							'name' 	=> __( 'Current Toggle Appearance', 'avia_framework' ),
							'desc' 	=> __( 'Highlight title bar of open toggles', 'avia_framework' ),
							'id' 	=> 'colors_current',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Font Color Current Toggle', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color for the current active toggle. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'font_color_current',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'required'	=> array( 'colors_current', 'equals', 'custom' ),
							'container_class'	=> 'av_half av_half_first',
						),

						array(
							'name' 	=> __( 'Custom +/- Icon Color Current Toggle', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom color for the current active toggle icon. Leave empty to use default', 'avia_framework' ),
							'id' 	=> 'toggle_icon_color_current',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'required'	=> array( 'colors_current', 'equals', 'custom' ),
							'container_class'	=> 'av_half',
						),

						array(
							'name' 	=> __( 'Background Current Toggle', 'avia_framework' ),
							'desc' 	=> __( 'Select the type of background for the current active toggle title bar.', 'avia_framework' ),
							'id' 	=> 'background_current',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'colors_current', 'equals', 'custom' ),
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Background Color', 'avia_framework' )		=> 'bg_color',
												__( 'Background Gradient', 'avia_framework' )	=> 'bg_gradient',
											)
						),

						array(
							'name' 	=> __( 'Title Bar Custom Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty for default color', 'avia_framework' ),
							'id' 	=> 'background_color_current',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'required'	=> array( 'background_current', 'equals', 'bg_color' ),
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'gradient_colors',
							'id'			=> array( 'background_gradient_current_direction', 'background_gradient_current_color1', 'background_gradient_current_color2', 'background_gradient_current_color3' ),
							'lockable'		=> true,
							'required'		=> array( 'background_current', 'equals', 'bg_gradient' ),
							'container_class'	=> array( '', 'av_third av_third_first', 'av_third', 'av_third' )
						),

						array(
							'name' 	=> __( 'Hover Toggle Appearance', 'avia_framework' ),
							'desc' 	=> __( 'Appearance of toggles on mouse hover', 'avia_framework' ),
							'id' 	=> 'hover_colors',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Hover Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom hover font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'hover_font_color',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'container_class' => 'av_third av_third_first',
							'required'	=> array( 'hover_colors', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Hover Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom hover background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'hover_background_color',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'container_class' => 'av_third',
							'required'	=> array( 'hover_colors', 'equals', 'custom')
						),

						array(
							'name' 	=> __( 'Custom Hover +/- Icon Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom hover color for the toggle icon. Leave empty to use &quot;Custom Hover Font Color&quot;', 'avia_framework' ),
							'id' 	=> 'hover_toggle_icon_color',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'rgba' 	=> true,
							'lockable'	=> true,
							'container_class' => 'av_third',
							'required'	=> array( 'hover_colors', 'equals', 'custom')
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Colors', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors' ), $template );

			$c = array(
						array(
							'name'			=> __( 'Toggle Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the toggle title. Using non default values might need CSS styling.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'size-toggle',
												'desktop'	=> 'av-desktop-font-size-toggle',
												'medium'	=> 'av-medium-font-size-toggle',
												'small'		=> 'av-small-font-size-toggle',
												'mini'		=> 'av-mini-font-size-toggle'
											)
						),

						array(
							'name'			=> __( 'Content Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the content.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'size-content',
												'desktop'	=> 'av-desktop-font-size-content',
												'medium'	=> 'av-medium-font-size-content',
												'small'		=> 'av-small-font-size-content',
												'mini'		=> 'av-mini-font-size-content'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Sizes', 'avia_framework' ),
								'content'		=> $c
							)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_font_sizes' ), $template );


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'p',
							'name'				=> __( 'Toggle Title Tag (Theme Default is &lt;%s&gt;)', 'avia_framework' ),
							'desc'				=> __( 'Select a html tag for the toggle titles of this element.', 'avia_framework' ),
							'context'			=> __CLASS__,
							'lockable'			=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Toggle Titles', 'avia_framework' ),
								'content'		=> $c
							)
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
							'template_id'	=> $this->popup_key( 'modal_content_toggle' )
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
													$this->popup_key( 'modal_advanced_developer' )
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

			$desc  = __( 'Enter a toggle title here when it is open. Leave empty if not needed (better keep it short). ', 'avia_framework' );
			$desc .= '<br /><br /><strong>';
			$desc .=	__( 'LIMITATION: Will be ignored when HTML markup is used in Toggle Title and do not use HTML markup !!!', 'avia_framework' );
			$desc .= '</strong>';

			$c = array(
						array(
							'name'		=> __( 'Toggle Title', 'avia_framework' ),
							'desc'		=> __( 'Enter the toggle title here (better keep it short)', 'avia_framework' ),
							'id'		=> 'title',
							'type'		=> 'input',
							'std'		=> 'Toggle Title',
							'lockable'	=> true,
							'dynamic'	=> []
						),

						array(
							'name'		=> __( 'Open Toggle Title', 'avia_framework' ),
							'desc'		=> $desc,
							'id'		=> 'title_open',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'dynamic'	=> []
						),

						array(
							'name'		=> __( 'Toggle Content', 'avia_framework' ),
							'desc'		=> __( 'Enter some content here', 'avia_framework' ),
							'id'		=> 'content',
							'type'		=> 'tiny_mce',
							'std'		=> __( 'Toggle Content goes here', 'avia_framework' ),
							'lockable'	=> true,
							'dynamic'	=> []
                        ),

						array(
							'name'		=> __( 'Toggle Sorting Tags', 'avia_framework' ),
							'desc'		=> __( 'Enter any number of comma separated tags here. If sorting is active the user can filter the visible toggles with the help of these tags', 'avia_framework' ),
							'id'		=> 'tags',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'dynamic'	=> []
						),

						array(
							'name'		=> __( 'Toggle Title Position', 'avia_framework' ),
							'desc'		=> __( 'Select where to see the title when toggle is open. Positioning the title below content can e.g. be used to create a &quot;Read More&quot; section.', 'avia_framework' ),
							'id'		=> 'title_pos',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Above content (= default)', 'avia_framework' )	=> '',
												__( 'Below content', 'avia_framework' )				=> 'av-title-below'
											)
						),

						array(
							'name'		=> __( 'Toggle Content Slide Speed', 'avia_framework' ),
							'desc'		=> __( 'Select the slide down/slide up speed when opening or closing the toggle. Default is 200 ms', 'avia_framework' ),
							'id'		=> 'slide_speed',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 100, 1500, 50, array( __( 'Use Default', 'avia_framework' ) => '' ), ' ms' )
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_toggle' ), $c );

			$setting_id = Avia_Builder()->get_developer_settings( 'custom_id' );
			$class = in_array( $setting_id, array( 'deactivate', 'hide' ) ) ? 'avia-hidden' : '';

			$c = array(
						array(
							'name'		=> __( 'For Developers: Custom Toggle ID', 'avia_framework' ),
							'desc'		=> __( 'Insert a custom ID for the element here. Make sure to only use allowed characters (latin characters, underscores, dashes and numbers, no special characters can be used)','avia_framework' ),
							'id'		=> 'custom_id',
							'type'		=> 'input',
							'std'		=> '',
							'container_class'	=> $class,
						),

						array(
							'name'		=> __( 'Collapsed Aria-Label Text', 'avia_framework' ),
							'desc'		=> __( 'Enter a complete text to be used in aria-label attribute when the toggle is collapsed. Leave empty to use toggle title.','avia_framework' ),
							'id'		=> 'aria_collapsed',
							'type'		=> 'input',
							'std'		=> ''
						),

						array(
							'name'		=> __( 'Expanded Aria-Label Text', 'avia_framework' ),
							'desc'		=> __( 'Enter a complete text to be used in aria-label attribute when the toggle is expanded. Leave empty to use toggle title.','avia_framework' ),
							'id'		=> 'aria_expanded',
							'type'		=> 'input',
							'std'		=> ''
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Developer Settings', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_developer' ), $template );

		}


		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 *
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_sub_element( $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked );

			$template = $this->update_option_lockable( 'title', $locked );

			$params['innerHtml']  = '';
			$params['innerHtml'] .= "<div class='avia_title_container' {$template} data-update_element_template='yes'>{$attr['title']}</div>";

			return $params;
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
						'initial'			=> '0',
						'mode'				=> 'accordion',
						'sort'				=> '',
						'faq_markup'		=> '',
						'styling'			=> '',
						'colors'			=> '',
						'border_color'		=> '',
						'toggle_icon_color'	=> '',
						'font_color'		=> '',
						'background_color'	=> '',
						'colors_current'	=> '',
						'font_color_current'		=> '',
						'toggle_icon_color_current'	=> '',
						'background_current'		=> '',
						'background_color_current'	=> '',
						'background_gradient_current_color1'	=> '',
						'background_gradient_current_color2'	=> '',
						'background_gradient_current_color3'	=> '',
						'background_gradient_current_direction'	=> '',
						'hover_colors'				=> '',
						'hover_background_color'	=> '',
						'hover_font_color'			=> ''
				);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );


			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );
			$meta = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $atts, $meta );

			$this->in_sc_exec = true;

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );


			if( ! is_numeric( $atts['initial'] ) || $atts['initial'] < 0 )
			{
				$atts['initial'] = 0;
			}
			else
			{
				$atts['initial'] = (int) $atts['initial'];
				$nr_toggles = substr_count( $content, '[av_toggle ' );

				if( $atts['initial'] > $nr_toggles )
				{
					$atts['initial'] = $nr_toggles;
				}
			}

			//	set heading tag for all titles - save global
			$default_heading = ! empty( $meta['heading_tag'] ) ? $meta['heading_tag'] : 'p';
			$args = array(
						'heading'		=> $default_heading,
						'extra_class'	=> $meta['heading_class']
					);

			$extra_args = array( $this, $atts, $content, 'title' );

			/**
			 * @since 4.9
			 * @return array
			 */
			$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

			$this->heading_tag = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
			$this->heading_class = ! empty( $args['extra_class'] ) ? $args['extra_class'] : $meta['heading_class'];

			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'togglecontainer',
						$element_id,
						$atts['styling']
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );
			$element_styling->add_responsive_font_sizes( 'toggle', 'size-toggle', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'toggle-content', 'size-content', $atts, $this );

			if( $atts['mode'] == 'accordion' )
			{
				$element_styling->add_classes( 'container', 'toggle_close_all' );
			}

			$cols = array(
						'color'				=> $atts['font_color'],
						'background-color'	=> $atts['background_color'],
						'border-color'		=> $atts['border_color']
					);

			if( 'custom' == $atts['colors'] )
			{
				/**
				 * Styling: when font color is set, then icon gets font color, else border color
				 */
				$element_styling->add_styles( 'toggle', $cols );
				$element_styling->add_styles( 'toggle-content', $cols );

				$icon_color = '';
				if( ! empty( $atts['font_color'] ) || ! empty( $atts['toggle_icon_color'] ) )
				{
					$icon_color = ! empty( $atts['toggle_icon_color'] ) ? $atts['toggle_icon_color'] : $atts['font_color'];
				}

				if( ! empty( $icon_color ) )
				{
					$element_styling->add_styles( 'toggle-icon', array(
															'color'			=> $icon_color,
															'border-color'	=> $icon_color
														) );

					if( empty( $atts['toggle_icon_color'] ) )
					{
						$element_styling->add_classes( 'item_inherit', array( 'av-inherit-font-color', 'hasCustomColor' ) );
					}
					else
					{
						$element_styling->add_styles( 'toggle-icon-inner', array(
															'color'			=> $icon_color,
															'border-color'	=> $icon_color
														) );
					}
				}

				if( ! empty( $atts['border_color'] ) )
				{
					$element_styling->add_classes( 'item_inherit', 'av-inherit-border-color' );
				}
			}

			if( 'custom' == $atts['colors_current'] )
			{
				if( ! empty( $atts['font_color_current'] ) )
				{
					$element_styling->add_styles( 'toggle-current', array(
																	'color'			=> $atts['font_color_current'],
																	'border-color'	=> $atts['font_color_current']
																) );

					if( ! ( 'custom' == $atts['colors'] && ! empty( $atts['toggle_icon_color'] ) ) )
					{
						$element_styling->add_classes( 'container', 'hasCurrentStyle' );
					}
				}

				if( ! empty( $atts['toggle_icon_color_current'] ) )
				{
					$element_styling->add_styles( 'toggle-icon-current', array(
																	'color'			=> $atts['toggle_icon_color_current'],
																	'border-color'	=> $atts['toggle_icon_color_current']
																) );
				}

				if( 'bg_color' == $atts['background_current'] )
				{
					$element_styling->add_styles( 'toggle-current', array( 'background-color' => $atts['background_color_current'] ) );
				}
				else if( 'bg_gradient' == $atts['background_current'] )
				{
					$element_styling->add_callback_styles( 'toggle-current', array( 'background_gradient_current_direction' ) );
				}
			}

			if( 'custom' == $atts['hover_colors'] )
			{
				$element_styling->add_styles( 'toggle-hover-not', array(
																	'color'				=> $atts['hover_font_color'],
																	'background-color'	=> $atts['hover_background_color']
																) );

				$icon_color = ! empty( $atts['hover_toggle_icon_color'] ) ? $atts['hover_toggle_icon_color'] : $atts['hover_font_color'];

				//	must be important due to shortcode.css !!
				$element_styling->add_styles( 'toggle-icon-hover-not', array( 'border-color' => $icon_color . ' !important' ) );
			}

			//	#top needed when placed inside section
			$selectors = array(
						'container'			=> "#top .togglecontainer.{$element_id}",
						'toggle'			=> "#top .togglecontainer.{$element_id} {$this->heading_tag}.toggler",
						'toggle-current'	=> "#top .togglecontainer.{$element_id} {$this->heading_tag}.toggler.activeTitle",
						'toggle-hover'		=> "#top .togglecontainer.{$element_id} {$this->heading_tag}.toggler:hover",
						'toggle-hover-not'	=> "#top .togglecontainer.{$element_id} {$this->heading_tag}.toggler:not(.activeTitle):hover",
						'toggle-icon-hover-not'	=> "#top .togglecontainer.{$element_id} {$this->heading_tag}.toggler:not(.activeTitle):hover .toggle_icon, #top .togglecontainer.{$element_id} p.toggler:not(.activeTitle):hover .toggle_icon *",
						'toggle-icon'		=> "#top .togglecontainer.{$element_id} {$this->heading_tag}.toggler .toggle_icon",
						'toggle-icon-inner'	=> "#top .togglecontainer.{$element_id} {$this->heading_tag}.toggler .toggle_icon > span",
						'toggle-icon-current'	=> "#top .togglecontainer.{$element_id} {$this->heading_tag}.toggler.activeTitle .toggle_icon, #top .togglecontainer.{$element_id} p.toggler.activeTitle .toggle_icon > span",
						'toggle-content'	=> "#top .togglecontainer.{$element_id} .toggle_wrap .toggle_content",
					);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['meta'] = $meta;
			$result['element_styling'] = $element_styling;

			$this->parent_atts = $atts;
			avia_sc_toggle::$initial = $atts['initial'];
			avia_sc_toggle::$tags = array();

			$this->el_styling = $element_styling;

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
						'title'			=> '',
						'tags'			=> '',
						'custom_id'		=> '',
						'custom_markup'	=> ''
				);

			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );


			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );

			Avia_Dynamic_Content()->read( $atts, $this, $this->config['shortcode_nested'][0], $content );


			$classes = array(
						'av_toggle_section',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );



			$selectors = array(
						'container'	=> ".togglecontainer .av_toggle_section.{$element_id}",
						'toggler'	=> ".togglecontainer .av_toggle_section.{$element_id} .toggler"
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
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			$this->subitem_inline_styles = '';

			$markup_faq = '';

			//	https://kriesi.at/support/topic/google-search-console-error-duplicate-field-faqpage/
			if( ! empty( $atts['faq_markup'] ) && 0 == avia_sc_toggle::$faq_count )
			{
				$markup_faq = avia_markup_helper( array( 'context' => 'faq_section', 'echo' => false ) );
				avia_sc_toggle::$faq_count ++;
			}

			avia_sc_toggle::$counter = 1;

			$content = ShortcodeHelper::avia_remove_autop( $content, true );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$item_tag = $element_styling->style_tag_html( $this->subitem_inline_styles, 'sub-' . $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= $item_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}' {$markup_faq}>";
			$output .=		! empty( $sort ) ? $this->sort_list( $atts ) : '';
			$output .=		$content;
			$output .= '</div>';

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
		public function av_toggle( $atts, $content = '', $shortcodename = '' )
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

			$toggle_atts = $atts;


			/**
			 * We keep class settings in shortcode handler for better readability
			 */
			$element_styling->add_classes( 'titleClass', $this->heading_class );

			$toggle_init_open_style = '';

			if( is_numeric( avia_sc_toggle::$initial ) && avia_sc_toggle::$counter == avia_sc_toggle::$initial )
			{
				$element_styling->add_classes( 'titleClass', 'activeTitle' );
				$element_styling->add_classes( 'contentClass', 'active_tc' );

				//	must be set inline to avoid jumping to wrong tab in frontend
				$toggle_init_open_style = "style='display:block;'";
			}

			$title_pos_styling = empty( $toggle_atts['title_pos'] ) ? 'av-title-above' : $toggle_atts['title_pos'];
			$element_styling->add_classes( 'titleClass', $title_pos_styling );
			$element_styling->add_classes( 'contentClass', $title_pos_styling );

			if( ! isset( $toggle_atts['title'] ) || trim( $toggle_atts['title'] ) == '' )
			{
				$toggle_atts['title'] = avia_sc_toggle::$counter;
			}

			$setting_id = Avia_Builder()->get_developer_settings( 'custom_id' );
			if( empty( $toggle_atts['custom_id'] ) || in_array( $setting_id, array( 'deactivate' ) ) )
			{
				$toggle_atts['custom_id'] = 'toggle-id-' . avia_sc_toggle::$toggle_id++;
			}
			else
			{
				$toggle_atts['custom_id'] = AviaHelper::save_string( $toggle_atts['custom_id'], '-' );
			}

			if( trim( $toggle_atts['aria_collapsed'] ) == '' )
			{
				$toggle_atts['aria_collapsed'] = __( 'Click to expand:','avia_framework' ) . " {$toggle_atts['title']}";
			}

			if( trim( $toggle_atts['aria_expanded'] ) == '' )
			{
				$toggle_atts['aria_expanded'] = __( 'Click to collapse:','avia_framework' ) . " {$toggle_atts['title']}";
			}


			if( '' == $this->parent_atts['faq_markup'] )
			{
				$markup_tab = avia_markup_helper( array( 'context' => 'entry', 'echo' => false, 'custom_markup' => $toggle_atts['custom_markup'] ) );
				$markup_title = avia_markup_helper( array( 'context' => 'entry_title', 'echo' => false, 'custom_markup' => $toggle_atts['custom_markup'] ) );
				$markup_answer = '';
				$markup_text = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'custom_markup' => $toggle_atts['custom_markup'] ) );
			}
			else
			{
				$markup_tab = avia_markup_helper( array( 'context' => 'faq_question_container', 'echo' => false, 'custom_markup' => $toggle_atts['custom_markup'] ) );
				$markup_title = avia_markup_helper( array( 'context' => 'faq_question_title', 'echo' => false, 'custom_markup' => $toggle_atts['custom_markup'] ) );
				$markup_answer = avia_markup_helper( array( 'context' => 'faq_question_answer', 'echo' => false, 'custom_markup' => $toggle_atts['custom_markup'] ) );
				$markup_text = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'custom_markup' => $toggle_atts['custom_markup'] ) );
			}

			$data_slide_speed = empty( $toggle_atts['slide_speed'] ) ? '200' : $toggle_atts['slide_speed'];

			$element_styling->add_data_attributes( 'item_titleClass', [ 'slide-speed' => $data_slide_speed ] );
			$element_styling->add_data_attributes( 'item_titleClass', [ 'title' => $toggle_atts['title'] ] );
			$element_styling->add_data_attributes( 'item_titleClass', [ 'title-open' => $toggle_atts['title_open'] ] );
			$element_styling->add_data_attributes( 'item_titleClass', [ 'aria_collapsed' => $toggle_atts['aria_collapsed'] ] );
			$element_styling->add_data_attributes( 'item_titleClass', [ 'aria_expanded' => $toggle_atts['aria_expanded'] ] );


			$this->subitem_inline_styles .= $element_styling->get_style_tag( $element_id, 'rules_only' );

			$item_titleClass = $this->el_styling->get_class_string( 'item_titleClass' );
			$item_contentClass = $this->el_styling->get_class_string( 'item_contentClass' );
			$item_inherit = $this->el_styling->get_class_string( 'item_inherit' );
			$item_data = $element_styling->get_data_attributes_string( 'item_titleClass' );

			$section_class = $element_styling->get_class_string( 'container' );
			$titleClass = $element_styling->get_class_string( 'titleClass' );
			$contentClass = $element_styling->get_class_string( 'contentClass' );

			$title_html  = '';
			$title_html .=	"<{$this->heading_tag} id='toggle-{$toggle_atts['custom_id']}' data-fake-id='#{$toggle_atts['custom_id']}' class='toggler {$item_titleClass} {$titleClass} {$item_inherit}' {$markup_title} role='tab' tabindex='0' aria-controls='{$toggle_atts['custom_id']}' {$item_data}>";
			$title_html .=		$toggle_atts['title'];
			$title_html .=		'<span class="toggle_icon">';
			$title_html .=			'<span class="vert_icon"></span>';
			$title_html .=			'<span class="hor_icon"></span>';
			$title_html .=		'</span>';
			$title_html .=	"</{$this->heading_tag}>";


			$output  = '';
			$output .= "<section class='{$section_class}' {$markup_tab}>";
			$output .=		'<div role="tablist" class="single_toggle" ' . $this->create_tag_string( $toggle_atts['tags'], $toggle_atts ) . '  >';

			if( 'av-title-below' != $title_pos_styling )
			{
				$output .=		$title_html;
			}

			$output .=			"<div id='{$toggle_atts['custom_id']}' aria-labelledby='toggle-{$toggle_atts['custom_id']}' role='region' class='toggle_wrap {$item_contentClass} {$contentClass}' {$toggle_init_open_style} {$markup_answer}>";
			$output .=				"<div class='toggle_content invers-color {$item_inherit}' {$markup_text}>";
			$output .=					ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) );
			$output .=				'</div>';
			$output .=			'</div>';

			if( 'av-title-below' == $title_pos_styling )
			{
				$output .=		$title_html;
			}

			$output .=		'</div>';
			$output .= '</section>';

			avia_sc_toggle::$counter ++;

			return $output;
		}

		/**
		 * Returns the data string for the tags
		 *
		 * @param string $tags
		 * @param array $toggle_atts
		 * @return string
		 */
		protected function create_tag_string( $tags, $toggle_atts )
		{
			$first_item_text = apply_filters( 'avf_toggle_sort_first_label', __( 'All', 'avia_framework' ), $toggle_atts );

			$tag_string = '{' . $first_item_text . '} ';
			if( trim( $tags ) != '' )
			{
				$tags = explode( ',', $tags );

				foreach( $tags as $tag )
				{
					$tag = esc_html( trim( $tag ) );
					if( ! empty( $tag ) )
					{
						$tag_string .= '{' . $tag . '} ';
						avia_sc_toggle::$tags[ $tag ] = true;
					}
				}
			}

			$tag_string = 'data-tags="' . $tag_string . '"';

			return $tag_string;
		}

		/**
		 * Returns the HTML for the sort tags
		 *
		 * @param array $toggle_atts
		 * @return string
		 */
		protected function sort_list( $toggle_atts )
		{
			$output = '';
			$first = 'activeFilter';

			if( ! empty( avia_sc_toggle::$tags ) )
			{
				ksort( avia_sc_toggle::$tags );

				/**
				 * Allow to reorder sorted tags (e.g. in case special characters are not ordered correctly)
				 *
				 * @since 5.6.10
				 * @param array $avia_sc_toggle::$tags
				 * @param array $toggle_atts
				 * @return array
				 */
				avia_sc_toggle::$tags = apply_filters( 'avf_toggle_sorted_tags', avia_sc_toggle::$tags, $toggle_atts );

				/**
				 * Filter first label text
				 *
				 * @since ???
				 * @param string $first_item_text
				 * @param array $toggle_atts
				 * @return string
				 */
				$first_item_text = apply_filters( 'avf_toggle_sort_first_label', __( 'All', 'avia_framework' ), $toggle_atts );

				$start = array( $first_item_text => true );
				avia_sc_toggle::$tags = $start + avia_sc_toggle::$tags;

				/**
				 * Filter toggles separator
				 *
				 * @since ???
				 * @param string $sep
				 * @param array $toggle_atts
				 * @return string
				 */
				$sep = apply_filters( 'avf_toggle_sort_seperator', '/', $toggle_atts );

				foreach( avia_sc_toggle::$tags as $key => $value )
				{
					$output .= '<div class="tag-tab">';
					$output .=		'<a href="#" data-tag="{' . $key . '}" class="' . $first . '">' . $key . '</a>';
					$output .=		"<span class='tag-seperator'>{$sep}</span>";
					$output .= '</div>';
					$first = '';
				}
			}

			if( ! empty( $output ) )
			{
				$output = "<div class='taglist'>{$output}</div>";
			}

			return $output;
		}

    }
}
