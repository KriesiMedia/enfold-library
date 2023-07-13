<?php
/**
 * Slideshow Section
 *
 * Add a fullwidth section with fullwidth slides that can contain any content like columns and other elements
 * Based on Tab Section. js and css is extended in tab_section.
 * Slide Title Buttons are based on Tab Section Tabs.
 *
 * @added_by Günter
 * @since x.x.x
 */
if( ! defined( 'ABSPATH' ) ) { exit; }			// Don't load directly


if( ! class_exists( 'avia_sc_slide_section' ) )
{
	if( ! class_exists( 'avia_sc_slide_sub_section' ) )
	{
		//load the subsection shhortcode
		include_once( 'slideshow_sub_section.php' );
	}

	class avia_sc_slide_section extends aviaShortcodeTemplate
	{
		use \aviaBuilder\traits\scSlideshowUIControls;

		/**
		 * @since x.x.x
		 * @var int
		 */
		static public $count = 0;

		/**
		 * Counter for tabs (= index)
		 *
		 * @since x.x.x
		 * @var int
		 */
		static public $tab = 0;

		/**
		 * @since x.x.x
		 * @var int
		 */
		static public $admin_active = 1;

		/**
		 * Single tab titles
		 *
		 *		'index'		=> tab title
		 *
		 * @since x.x.x
		 * @var array
		 */
		static public $tab_titles = array();

		/**
		 * HTML for tab icons
		 *
		 *		'index'		=> html code
		 *
		 * @since x.x.x
		 * @var array
		 */
		static public $tab_icons = array();

		/**
		 * HTML for tab images
		 *
		 *		'index'		=> html code
		 *
		 * @since x.x.x
		 * @var array
		 */
		static public $tab_images = array();

		/**
		 * @since x.x.x
		 * @var array
		 */
		static public $tab_atts = array();

		/**
		 * Holds the element id for the current tab section
		 *
		 * @since x.x.x
		 * @var string
		 */
		static public $tab_element_id = '';

		/**
		 * Hold the element id's for the tabs
		 *
		 *		'index'		=> element_id
		 *
		 * @since x.x.x
		 * @var array
		 */
		static public $sub_tab_element_id = array();

		/**
		 * Create the config array for the tab section
		 *
		 * @since x.x.x
		 */
		function shortcode_insert_button()
		{
			$this->config['version']			= '1.0';
			$this->config['is_fullwidth']		= 'yes';
			$this->config['type']				= 'layout';
			$this->config['self_closing']		= 'no';
			$this->config['contains_text']		= 'no';
			$this->config['layout_children']	= array( 'av_slide_sub_section' );

			$this->config['name']				= __( 'Slideshow Section', 'avia_framework' );
			$this->config['icon']				= AviaBuilder::$path['imagesURL'] . 'sc-slideshow-section.png';
			$this->config['tab']				= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']				= 13;
			$this->config['shortcode']			= 'av_slide_section';
			$this->config['html_renderer']		= false;
			$this->config['tinyMCE']			= array( 'disable' => 'true' );
			$this->config['tooltip']			= __( 'Add a fullwidth section with slides that can contain columns and other elements', 'avia_framework' );
			$this->config['drag-level']			= 1;
			$this->config['drop-level']			= 100;
			$this->config['disabling_allowed']	= true;

			$this->config['id_name']			= 'id';
			$this->config['id_show']			= 'always';				//	we use original code - not $meta
			$this->config['aria_label']			= 'yes';
		}

		/**
		 * @since x.x.x
		 */
		function admin_assets()
		{
			$ver = AviaBuilder::VERSION;

			wp_register_script( 'avia_tab_section_js', AviaBuilder::$path['assetsURL'] . 'js/avia-tab-section.js', array( 'avia_builder_js', 'avia_modal_js' ), $ver, true );
			Avia_Builder()->add_registered_admin_script( 'avia_tab_section_js' );
		}

		/**
		 * @since x.x.x
		 */
		function extra_assets()
		{
			//load css
			wp_enqueue_style( 'avia-module-slideshow', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/slideshow/slideshow.css', array( 'avia-layout' ), false );
			wp_enqueue_style( 'avia-module-tabsection', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/tab_section/tab_section.css', array( 'avia-layout' ), false );

			//load js
			wp_enqueue_script( 'avia-module-tabsection', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/tab_section/tab_section.js', array( 'avia-shortcodes' ), false, true );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @since x.x.x
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
						'name'  => __( 'Layout' , 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'name' 	=> __( 'ELEMENT IN ACTIVE BETA since x.x.x', 'avia_framework' ),
							'desc' 	=> __( 'Be spare in using animations in slide content - and please check frontend. Slide transitions might break element behaviour.', 'avia_framework' ),
							'type' 	=> 'heading',
							'description_class' => 'av-builder-note av-notice'
						),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'layout_general' ),
													$this->popup_key( 'layout_section_height' ),
													$this->popup_key( 'layout_margin_padding' )

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
													$this->popup_key( 'styling_tab_colors' ),
													$this->popup_key( 'styling_navigation' ),
													$this->popup_key( 'styling_nav_colors' )
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
								'template_id'	=> 'screen_options_toggle'
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
						'id'	=> 'av_element_hidden_in_editor',
						'type'	=> 'hidden',
						'std'	=> '0'
					),

				array(
						'id'	=> 'av_admin_tab_active',
						'type'	=> 'hidden',
						'std'	=> '1'
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
		 * @since x.x.x
		 */
		protected function register_dynamic_templates()
		{
			/**
			 * Layout Tab
			 * ===========
			 */

			$c = array(

						array(
							'name'		=> __( 'Slideshow Section Appearance', 'avia_framework' ),
							'desc'		=> __( 'Select the general appearance of the slideshow section. The tabs do not have any function.', 'avia_framework' ),
							'id'		=> 'element_layout',
							'type'		=> 'select',
							'std'		=> '',
							'subtype'	=> array(
												__( 'Slides only', 'avia_framework' )						=> '',
												__( 'Slide title in tabs above slides', 'avia_framework' )	=> 'tabs',
											)
						),

						array(
							'name'	=> __( 'Initial Open Slide', 'avia_framework' ),
							'desc' 	=> __( 'Enter the number of the slide that should be shown initially (starting with 1). If slide number does not exist the first slide is taken.', 'avia_framework' ),
							'id' 	=> 'initial',
							'type' 	=> 'input_number',
							'min'	=> 1,
							'step'	=> 1,
							'std' 	=> '1'
						),

						array(
							'name'		=> __( 'Slideshow Container Width', 'avia_framework' ),
							'desc'		=> __( 'Select to strech container for slides fullwidth (remove any margin or padding)', 'avia_framework' ),
							'id'		=> 'slides_container',
							'type'		=> 'select',
							'std'		=> '',
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )			=> '',
												__( 'Strech fullwidth', 'avia_framework' )	=> 'av-strech-full'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'General', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_general' ), $template );

			$c = array(

						array(
							'name'		=> __( 'Slides Height', 'avia_framework' ),
							'id'		=> 'content_height',
							'desc'		=> __( 'Define the size of the slides when switching between the slides. We recommend to use &quot;Same Height&quot;. Content alignment can be set for each slide when &quot;Same Height&quot; is selected.', 'avia_framework' ),
							'type'		=> 'select',
							'std'		=> '',
							'subtype'	=> array(
												__( 'Same height for all slides', 'avia_framework' )	=> '',
												__( 'Auto adjust height to content', 'avia_framework' )	=> 'av-tab-content-auto'
											)
						),

						array(
							'name'		=> __( 'Slides Minimum Height', 'avia_framework' ),
							'id'		=> 'min_height',
							'desc'		=> __( 'Define a minimum height for the slides.', 'avia_framework' ),
							'type'		=> 'select',
							'std'		=> '',
							'required'	=> array( 'content_height', 'equals', '' ),
							'subtype'	=> array(
											__( 'No minimum height, use content within slides to define section height', 'avia_framework' )	=> '',
											__( 'At least 100&percnt; of browser window height', 'avia_framework' )								=> '100',
											__( 'At least 75&percnt; of browser window height', 'avia_framework' )								=> '75',
											__( 'At least 50&percnt; of browser window height', 'avia_framework' )								=> '50',
											__( 'At least 25&percnt; of browser window height', 'avia_framework' )								=> '25',
											__( 'Minimum custom height in &percnt; based on browser windows height', 'avia_framework' )			=> 'percent',
											__( 'Minimum custom height in pixel', 'avia_framework' )											=> 'custom',
										)
						),

						array(
							'name'		=> __( 'Section Minimum Custom Height In &percnt;', 'avia_framework' ),
							'desc'		=> __( 'Define a minimum height for the section in &percnt; based on the browser windows height', 'avia_framework' ),
							'id'		=> 'min_height_pc',
							'type'		=> 'select',
							'std'		=> '25',
							'required'	=> array( 'min_height', 'equals', 'percent' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array(), ' &percnt;' )
						),

						array(
							'name'		=> __( 'Section Minimum Custom Height In px', 'avia_framework' ),
							'desc'		=> __( 'Define a minimum height for the section. Use a pixel value. eg: 500px', 'avia_framework' ) ,
							'id'		=> 'min_height_px',
							'type'		=> 'input',
							'std'		=> '500px',
							'required'	=> array( 'min_height', 'equals', 'custom' ),
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Section Height', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_section_height' ), $template );

			$c = array(

						array(
								'type'			=> 'template',
								'template_id'	=> 'slideshow_section_margin_padding',
							),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_margin_padding' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Slide Title Tabs Bar Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color of the slide title tabs bar. Enter no value if you want to use the default color.', 'avia_framework' ),
							'id'		=> 'bg_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'required'	=> array( 'element_layout', 'equals', 'tabs' )
						),

						array(
							'name'		=> __( 'Slide Title Tabs Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom text color for all slide titles. Enter no value if you want to use the default font color.', 'avia_framework' ),
							'id'		=> 'color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'required'	=> array( 'element_layout', 'equals', 'tabs' )
						),

						array(
							'name'		=> __( 'Active Slide Title Tabs Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom text color for the active slide title. Enter no value if you want to use the default font color.', 'avia_framework' ),
							'id'		=> 'active_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'required'	=> array( 'element_layout', 'equals', 'tabs' )
						),

						array(
							'name'		=> __( 'Slide Title Tabs Font Color On Hover', 'avia_framework' ),
							'desc'		=> __( 'Select a custom text color for all slide title on hover. Enter no value if you want to use the default font color.', 'avia_framework' ),
							'id'		=> 'color_hover',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'required'	=> array( 'element_layout', 'equals', 'tabs' )
						),

						array(
							'name'		=> __( 'Active Slide Title Tabs Font Color On Hover', 'avia_framework' ),
							'desc'		=> __( 'Select a custom text color for the active slide title on hover. Enter no value if you want to use the default font color.', 'avia_framework' ),
							'id'		=> 'active_color_hover',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'required'	=> array( 'element_layout', 'equals', 'tabs' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Slide Title Tabs Colors', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_tab_colors' ), $template );

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_controls',
							'std_nav'		=> 'av-navigate-arrows'
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
							'template_id'	=> 'slideshow_navigation_colors'
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


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'name'		=> __( 'Slide Transition', 'avia_framework' ),
							'desc'		=> __( 'Define a transition when switching between the slides. Fade forces &quot;Same Height&quot; for all slides. We recommend this setting in general. Slide Up/Down disables animations on elements in slides.', 'avia_framework' ),
							'id'		=> 'transition',
							'type'		=> 'select',
							'std'		=> 'av-tab-slide-transition',
							'subtype'	=> array(
												__( 'Slide sidewards', 'avia_framework' )	=> 'av-tab-slide-transition',
												__( 'Slide Up/Down', 'avia_framework' )		=> 'av-tab-slide-up-transition',
												__( 'Fade', 'avia_framework' )				=> 'av-tab-fade-transition',
												__( 'No special effect', 'avia_framework' )	=> '',
											)
						),

						array(
							'name'		=> __( 'Transition Speed', 'avia_framework' ),
							'desc'		=> __( 'Selected speed in milliseconds for transition effect. Default for fade is 800, else 400.', 'avia_framework' ),
							'id'		=> 'transition_speed',
							'type'		=> 'select',
							'std'		=> '',
							'required'	=> array( 'transition', 'not', '' ),
							'subtype'	=> AviaHtmlHelper::number_array( 100, 5000, 100, array( __( 'Use Default (400/800 ms)', 'avia_framework' ) => '' ), ' ms', '', '', array( '6000' => '6000', '7000' => '7000', '8000' => '8000', '9000' => '9000', '10000' => '10000', '15000' => '15000' ) )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'slideshow_rotation',
							'stop_id'		=> 'autoplay_stopper'
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Animation', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation' ), $template );
		}


		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 *
		 * @since x.x.x
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		function editor_element( $params )
		{
			extract( $params );

			avia_sc_slide_section::$tab = 0;
			avia_sc_slide_section::$tab_titles = array();
			avia_sc_slide_section::$admin_active = ! empty( $args['av_admin_tab_active'] ) ? $args['av_admin_tab_active'] : 1;


			$name = $this->config['shortcode'];
			$data['shortcodehandler'] 	= $this->config['shortcode'];
			$data['modal_title'] 		= $this->config['name'];
			$data['modal_ajax_hook'] 	= $this->config['shortcode'];
			$data['dragdrop-level'] 	= $this->config['drag-level'];
			$data['allowed-shortcodes']	= $this->config['shortcode'];

			if( ! empty( $this->config['modal_on_load'] ) )
			{
				$data['modal_on_load'] 	= $this->config['modal_on_load'];
			}

			$dataString  = AviaHelper::create_data_string( $data );


			if( $content )
			{
				$final_content = $this->builder->do_shortcode_backend( $content );
				$text_area = ShortcodeHelper::create_shortcode_by_array( $name, $content, $args );
			}
			else
			{
				$tab = new avia_sc_slide_sub_section( $this->builder );
				$params = array(
								'content'	=> '',
								'args'		=> array(),
								'data'		=> ''
							);

				$final_content  = '';
				$final_content .= $tab->editor_element( $params );
				$final_content .= $tab->editor_element( $params );
				$final_content .= $tab->editor_element( $params );
				$final_content .= $tab->editor_element( $params );
				$text_area = ShortcodeHelper::create_shortcode_by_array( $name, '[av_slide_sub_section][/av_slide_sub_section][av_slide_sub_section][/av_slide_sub_section][av_slide_sub_section][/av_slide_sub_section][av_slide_sub_section][/av_slide_sub_section]', $args );
			}

			$title_id = ! empty( $args['id'] ) ? ': ' . ucfirst( $args['id'] ) : '';
			$hidden_el_active = ! empty( $args['av_element_hidden_in_editor'] ) ? 'av-layout-element-closed' : '';


			$output  = "<div class='avia_tab_section avia-slideshow-section {$hidden_el_active} avia_layout_section avia_pop_class avia-no-visual-updates {$name} av_drag' {$dataString}>";

			$output .=		'<div class="avia_sorthandle menu-item-handle">';
			$output .=			"<span class='avia-element-title'>{$this->config['name']}<span class='avia-element-title-id'>{$title_id}</span></span>";
			$output .=			'<a class="avia-delete" href="#delete" title="' . __( 'Delete Slideshow Section', 'avia_framework' ) . '">x</a>';
			$output .=			'<a class="avia-toggle-visibility" href="#toggle" title="' . __( 'Show/Hide Slideshow Section', 'avia_framework' ) . '"></a>';

			if( ! empty( $this->config['popup_editor'] ) )
			{
				$output .=		'<a class="avia-edit-element" href="#edit-element" title="' . __( 'Edit Slideshow Section', 'avia_framework' ) . '">' . __( 'edit', 'avia_framework' ) . '</a>';
			}

			$output .=			'<a class="avia-save-element" href="#save-element" title="' . __( 'Save Element as Template', 'avia_framework' ) . '">+</a>';
			$output .=			'<a class="avia-clone" href="#clone" title="' . __( 'Clone Slideshow Section', 'avia_framework' ) . '">' . __( 'Clone Tab Section', 'avia_framework' ) . '</a>';
			$output .=		'</div>';

			$output .=		"<div class='avia_inner_shortcode avia_connect_sort av_drop' data-dragdrop-level='{$this->config['drop-level']}'>";
			$output  .=			'<div class="avia_tab_section_titles">';

			//create tabs
			for( $i = 1; $i <= avia_sc_slide_section::$tab; $i++ )
			{
				$active_tab = $i == avia_sc_slide_section::$admin_active ? 'av-admin-section-tab-active' : '';
				$tab_title = isset( avia_sc_slide_section::$tab_titles[ $i ] ) ? avia_sc_slide_section::$tab_titles[ $i ] : '';

				$output  .=			"<a href='#' data-av-tab-section-title='{$i}' class='av-admin-section-tab {$active_tab}'>";
				$output  .=				'<span class="av-admin-section-tab-move-handle"></span>';
				$output  .=				'<span class="av-tab-title-text-wrap-full">' . __( 'Slide', 'avia_framework' ) . ' ';
				$output  .=					"<span class='av-tab-nr'>{$i}</span>";
				$output  .=					"<span class='av-tab-custom-title'>{$tab_title}</span>";
				$output  .=				'</span>';
				$output  .=			'</a>';
			}

			//$output .=			"<a class='avia-clone-tab avia-add'  href='#clone-tab' title='".__('Clone Last Slide', 'avia_framework' )."'>".__('Clone Last Tab', 'avia_framework' )."</a>";
			$output .=				"<a class='avia-add-tab avia-add'  href='#add-tab' title='" . __( 'Add Slide', 'avia_framework' ) . "'>" . __( 'Add Slide', 'avia_framework' ) . '</a>';
			$output .=			'</div>';
			$output .=			"<textarea data-name='text-shortcode' cols='20' rows='4'>{$text_area}</textarea>";
			$output .=			$final_content;
			$output .=		'</div>';

			$output .=		"<a class='avia-layout-element-hidden' href='#'>" . __( 'Slideshow Section content hidden. Click here to show it', 'avia_framework' ) . '</a>';

			$output .= '</div>';

			return $output;
		}

		/**
		 * Create custom stylings
		 *
		 * @since x.x.x
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
						'element_layout'		=> '',
						'initial'				=> 1,
						'tab_pos'				=> 'av-tab-above-content',		// for comp.with tab section - needed to move
						'tab_arrows'			=> '',
						'content_height'		=> '',
						'min_height'			=> '',
						'min_height_pc'			=> 25,
						'min_height_px'			=> '',
						'padding'				=> '',
						'slides_container'		=> '',
						'tab_padding'			=> '',
						'bg_color'				=> '',
						'color'					=> '',
						'active_color'			=> '',
						'color_hover'			=> '',
						'active_color_hover'	=> '',
						'control_layout'		=> 'av-control-default',
						'slider_navigation'		=> 'av-navigate-arrows',
						'nav_arrow_color'		=> '',
						'nav_arrow_bg_color'	=> '',
						'nav_dots_color'		=> '',
						'nav_dot_active_color'	=> '',
						'transition'			=> 'av-tab-slide-transition',
						'transition_speed'		=> '',
						'autoplay'				=> '',
						'interval'				=> '',
						'id'					=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			if( '' == $atts['content_height'] )
			{
				if( 'percent' == $atts['min_height'] )
				{
					$atts['min_height'] = $atts['min_height_pc'];
				}
			}
			else
			{
				$atts['min_height'] = '';
			}

			if( empty( $atts['transition'] ) )
			{
				$atts['transition'] = 'av-tab-slide-no-transition';
			}

			if( 'av-tab-fade-transition' == $atts['transition'] )
			{
				$atts['content_height'] = '';
			}

			//	store atts to be accessible by tab subsections
			avia_sc_slide_section::$tab_element_id = $element_id;
			avia_sc_slide_section::$tab = 0;
			avia_sc_slide_sub_section::$attr = $atts;

			avia_sc_slide_section::$tab_atts = array();
			avia_sc_slide_section::$sub_tab_element_id[] = array();

			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'av-slideshow-section',
						'av-is-slideshow',
						$atts['transition'],
						$atts['content_height'] != '' ? $atts['content_height'] : 'av-tab-content-fixed',
						$atts['tab_pos'],
						$atts['slides_container']
					);

			switch( $atts['element_layout'] )
			{
				case 'tabs':
					$classes[] = 'av-show-tabs';
					break;
				case '':
				default:
					$classes[] = 'av-hide-tabs';
					break;
			}


			$element_styling->add_classes( 'section', $classes );
			$element_styling->add_classes_from_array( 'section', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'section', 'hide_element', $atts );


			$classes = array(
						'av-tab-section-outer-container',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );


			if( $atts['transition_speed'] != '' )
			{
				$speed = $atts['transition_speed'] / 1000.0;
				$duration = $element_styling->transition_duration_rules( $speed );

				switch( $atts['transition'] )
				{
					case 'av-tab-fade-transition':
						$element_styling->add_styles( 'layout-tab-animate', $duration );
						break;
					case 'av-tab-slide-transition':
						$element_styling->add_styles( 'inner-container-animate', $duration );
						break;
					case 'av-tab-slide-up-transition':
						$element_styling->add_styles( 'layout-tab-animate', $duration );
						$element_styling->add_styles( 'inner-container-animate', $duration );
						break;
					default:
						$speed = 0;
				}
			}
			else
			{
				switch( $atts['transition'] )
				{
					case 'av-tab-fade-transition':
						$speed = 0.8;
						break;
					case 'av-tab-slide-transition':
					case 'av-tab-slide-up-transition':
						$speed = 0.4;
						break;
					default:
						$speed = 0;
				}
			}

			//	prepare for slideshow options
			$atts['animation'] = $atts['transition'];
			$atts['interval'] = floatval( $atts['interval'] ) + $speed;

			$ui_args = array(
						'element_id'		=> $element_id,
						'element_styling'	=> $element_styling,
						'atts'				=> $atts,
						'autoplay_option'	=> 'true',
						'context'			=> __CLASS__,
					);

			$this->addSlideshowAttributes( $ui_args );

			$classes = array(
						'av-tab-section-inner-container',
						'avia-section-' . $atts['padding']
					);

			$element_styling->add_classes( 'inner-container', $classes );


			$classes = array(
						'av-tab-section-tab-title-container',
						'avia-tab-title-padding-' . $atts['tab_padding']	//	set in
					);

			$element_styling->add_classes( 'tab-title-container', $classes );

			$element_styling->add_responsive_styles( 'container', 'margin', $atts, $this );
			$element_styling->add_responsive_styles( 'tab-outer-tab-title', 'tab_padding', $atts, $this );
			$element_styling->add_responsive_styles( 'layout-tab', 'padding', $atts, $this );

			$element_styling->add_styles( 'tab-title-container', array( 'background-color' => $atts['bg_color'] ) );

			if( ! empty( $atts['color'] ) )
			{
				$element_styling->add_styles( 'tab-title', array( 'color' => $atts['color'] ) );
				$element_styling->add_classes( 'tab-title-container', 'av-custom-tab-color' );
			}

			$element_styling->add_styles( 'tab-title-active', array( 'color' => $atts['active_color'] ) );
			$element_styling->add_styles( 'tab-title-hover', array( 'color' => $atts['color_hover'] ) );
			$element_styling->add_styles( 'tab-title-active-hover', array( 'color' => $atts['active_color_hover'] ) );

			$selectors = array(
						'container'					=> ".av-tab-section-outer-container.{$element_id}",
						'layout-tab'				=> ".av-tab-section-outer-container.{$element_id} .av-layout-tab",
						'inner-container'			=> ".av-tab-section-outer-container.{$element_id} .av-tab-section-inner-container",
						'tab-title-container'		=> ".av-tab-section-outer-container.{$element_id} .av-tab-section-tab-title-container",
						'tab-outer-tab-title'		=> ".av-tab-section-outer-container.{$element_id} .av-outer-tab-title",
						'tab-title'					=> "#top .av-tab-section-outer-container.{$element_id} .av-section-tab-title",
						'tab-title-active'			=> "#top .av-tab-section-outer-container.{$element_id} .av-active-tab-title.av-section-tab-title",
						'tab-title-hover'			=> "#top .av-tab-section-outer-container.{$element_id} .av-section-tab-title:hover",
						'tab-title-active-hover'	=> "#top .av-tab-section-outer-container.{$element_id} .av-active-tab-title.av-section-tab-title:hover",
						'inner-container-animate'	=> ".{$atts['transition']} .{$element_id} .av-tab-section-inner-container",
						'layout-tab-animate'		=> ".{$atts['transition']} .av-tab-section-outer-container.{$element_id} .av-layout-tab",
					);

			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['meta'] = $meta;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @since x.x.x
		 * @param array $atts				array of attributes
		 * @param string $content			text within enclosing form of shortcode element
		 * @param string $shortcodename		the shortcode found, when == callback name
		 * @return string					the modified html string
		 */
		function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			//	are filled in avia_sc_slide_sub_section
			avia_sc_slide_section::$tab = 0;
			avia_sc_slide_section::$tab_titles = array();
			avia_sc_slide_section::$tab_icons = array();
			avia_sc_slide_section::$tab_images = array();
			avia_sc_slide_section::$count++;

			$final_content = ShortcodeHelper::avia_remove_autop( $content, true ) ;

			$tabs = '';
			$arrow = '<span class="av-tab-arrow-container"><span></span></span>';

			if( ! is_numeric( $atts['initial'] ) || $atts['initial'] <= 0 )
			{
				$atts['initial'] = 1;
			}
			else if( $atts['initial'] > avia_sc_slide_section::$tab )
			{
				$atts['initial'] = avia_sc_slide_section::$tab;
			}

			//	avoids flicker and wrong positioning on pageload
			$style = '';
			if( in_array( $atts['transition'], array( '', 'av-tab-slide-no-transition', 'av-tab-slide-transition' ) ) )
			{
				$style = 'style="width: ' . avia_sc_slide_section::$tab * 100 . 'vw; left: ' . ( ( $atts['initial'] -1 ) * -100 ). '%;"';
			}

			for( $i = 1; $i <= avia_sc_slide_section::$tab; $i ++ )
			{
				$icon 	= ! empty( avia_sc_slide_section::$tab_icons[ $i ] ) ? avia_sc_slide_section::$tab_icons[ $i ] : '';
				$image  = ! empty( avia_sc_slide_section::$tab_images[ $i ] ) ? avia_sc_slide_section::$tab_images[ $i ] : '';

				$extraClass  = avia_sc_slide_section::$sub_tab_element_id[ $i ] . ' ';
				$extraClass .= ! empty( $icon ) ? 'av-tab-with-icon ' : 'av-tab-no-icon ';
				$extraClass .= ! empty( $image ) ? 'av-tab-with-image noHover ' : 'av-tab-no-image ';
				$extraClass .= avia_sc_slide_section::$tab_atts[ $i ]['tab_image_style'];

				/**
				 * Bugfix: Set no-scroll to avoid auto smooth scroll when initialising tab section and multiple tab sections are on a page - removed in js.
				 */
				$active_tab = $i == $atts['initial'] ? 'av-active-tab-title no-scroll' : '';

				$tab_title = ! empty( avia_sc_slide_section::$tab_titles[ $i ] ) ? avia_sc_slide_section::$tab_titles[ $i ] : '';
				if( $tab_title == '' && empty( $image ) && empty( $icon ) )
				{
					$tab_title = __( 'Slide', 'avia_framework' ) . ' ' . $i;
				}

				$tab_link = AviaHelper::valid_href( $tab_title, '-', 'av-tab-section-' . avia_sc_slide_section::$count . '-' . $i );
				$tab_id = 'av-tab-section-' . avia_sc_slide_section::$count . '-' . $i;

				/**
				 * layout is broken since adding aria-controls $tab_id with 4.7.6
				 * Fixes problem with non latin letters like greek
				 */
				if( $tab_id == $tab_link )
				{
					$tab_link .= '-link';
				}

				if( $tab_title == '' )
				{
					$extraClass .= ' av-tab-without-text ';
				}

				/**
				 * @since 4.8
				 * @param string $tab_link
				 * @param string $tab_title
				 * @return string
				 */
				$tab_link = apply_filters( 'avf_tab_section_link_hash', $tab_link, $tab_title );

				$tabs .= "<a href='#{$tab_link}' data-av-tab-section-title='{$i}' class='av-section-tab-title {$active_tab} {$extraClass}' role='tab' tabindex='0' aria-controls='{$tab_id}'>";
				$tabs .=	$icon;
				$tabs .=	$image;
				$tabs .=	"<span class='av-outer-tab-title'>";
				$tabs .=		"<span class='av-inner-tab-title'>{$tab_title}</span>";
				$tabs .=	'</span>';
				$tabs .=	$arrow;
				$tabs .= '</a>';
			}


			$style_tag = $element_styling->get_style_tag( $element_id );
			$section_class = $element_styling->get_class_string( 'section' );
			$container_class = $element_styling->get_class_string( 'container' );
			$container_data = $element_styling->get_data_attributes_json_string( 'container', 'slideshow-data' );
			$inner_container_class = $element_styling->get_class_string( 'inner-container' );
			$title_container_class = $element_styling->get_class_string( 'tab-title-container' );


			$params['class'] = "av-tab-section-container entry-content-wrapper main_color {$section_class}";
			$params['min_height'] = $min_height;
			$params['min_height_px'] = $min_height_px;
			$params['open_structure'] = false;
			$params['id'] = AviaHelper::save_string( $id, '-', 'av-tab-section-' . avia_sc_slide_section::$count );
			$params['custom_markup'] = $meta['custom_markup'];
			$params['aria_label'] = $meta['aria_label'];

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
				$params['class'] .= ' tab-section-not-first';
			}


			$tabs_final  = "<div class='{$title_container_class}' role='tablist'>{$tabs}</div>";

			$output  = '';
			$output .= $style_tag;
			$output .= avia_new_section( $params );
			$output .= "<div class='{$container_class}' {$container_data}>";

			$output .=		$tabs_final;

			if( avia_sc_slide_section::$tab > 1 )
			{
				//	we need this to loop through slides - hidden by CSS if not needed
				$output .=	$this->slide_navigation_arrows( $atts );

				if( false !== strpos( $atts['slider_navigation'], 'av-navigate-dots' ) )
				{
					$output .=	$this->slide_navigation_dots( $atts );
				}
			}

			$output .=		'<div class="av-slide-section-container-wrap">';

			$output .=			"<div class='{$inner_container_class}' {$style}>";

			if( avia_sc_slide_section::$tab > 1 )
			{
				/**
				 * This is a fallback to original tab section to implement js navigation and swipe event
				 */
				$output .=			'<span class="av_prev_tab_section av_tab_navigation"></span>';
				$output .=			'<span class="av_next_tab_section av_tab_navigation"></span>';
			}

			$output .=			$final_content;
			$output .=			'</div>';

			$output .=		'</div>';

			$output .= '</div>';
			$output .= avia_section_after_element_content( $meta , 'after_tab_section_' . avia_sc_slide_section::$count, false );

			// added to fix https://kriesi.at/support/topic/footer-disseapearing/#post-427764
			avia_sc_section::$close_overlay = '';

			return $output;
		}

		/**
		 * Create arrows to scroll tabs
		 *
		 * @since x.x.x
		 * @param array $atts
		 * @return string
		 */
		protected function slide_navigation_arrows( array $atts )
		{
			$class_main = 'av-tabsection-slides-arrow';
			$class = 'av-tab-section-slide-content';

			$args = array(
						'class_main'	=> "avia-slideshow-arrows avia-slideshow-controls {$class_main} av-animated-when-visible fade-in",
						'class_prev'	=> "av_prev_tab_section {$class}",
						'class_next'	=> "av_next_tab_section {$class}",
						'context'		=> get_class(),
						'params'		=> $atts
					);

			return aviaFrontTemplates::slide_navigation_arrows( $args );
		}

		/**
		 * Create dots to scroll tabs
		 *
		 * @since x.x.x
		 * @param array $atts
		 * @return string
		 */
		protected function slide_navigation_dots( array $atts )
		{
			$args = array(
						'class_main'		=> 'avia-slideshow-dots avia-slideshow-controls av-tabsection-slides-dots fade-in',
						'total_entries'		=> avia_sc_slide_section::$tab,
						'container_entries'	=> 1,
						'context'			=> get_class(),
						'params'			=> $atts
					);


			return aviaFrontTemplates::slide_navigation_dots( $args );
		}

	}
}
