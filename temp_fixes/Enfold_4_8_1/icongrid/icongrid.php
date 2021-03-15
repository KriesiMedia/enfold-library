<?php
/**
 * Icon Grid Shortcode
 *
 * @author tinabillinger
 * @since 4.5
 * Creates an icon grid with toolips or flip content
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if ( ! class_exists( 'avia_sc_icongrid' ) )
{
	class avia_sc_icongrid extends aviaShortcodeTemplate
	{
		
		/**
		 * @since 4.5
		 * @var array 
		 */
		protected $screen_options;

		/**
		 * @since 4.5
		 * @var array 
		 */
		protected $icon_styling;

		/**
		 * @since 4.5
		 * @var array 
		 */
		protected $title_styling;

		/**
		 * @since 4.5
		 * @var array 
		 */
		protected $subtitle_styling;

		/**
		 * @since 4.5
		 * @var array 
		 */
		protected $content_styling;

		/**
		 * @since 4.5
		 * @var array 
		 */
		protected $flipbox_front_styling;

		/**
		 * @since 4.5
		 * @var array 
		 */
		protected $flipbox_back_styling;

		/**
		 * @since 4.5
		 * @var array 
		 */
		protected $wrapper_styling;

		/**
		 * @since 4.5
		 * @var array 
		 */
		protected $list_styling;

		/**
		 * @since 4.5
		 * @var string 
		 */
		protected $icongrid_styling;

		/**
		 * @since 4.5
		 * @var string 
		 */
		protected $icongrid_numrow;

		/**
		 * @since 4.5
		 * @var string 
		 */
		protected $icongrid_borders;

		/**
		 * @since 4.5.1
		 * @var string 
		 */
		protected $custom_title_size;

		/**
		 * @since 4.5.1
		 * @var string 
		 */
		protected $custom_subtitle_size;

		/**
		 * @since 4.5.1
		 * @var string 
		 */
		protected $custom_content_size;

		/**
		 * @since 4.5.1
		 * @var string 
		 */
		protected $custom_icon_size;

		/**
		 * 
		 * @since 4.5.1
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder ) 
		{
			parent::__construct( $builder );

			$this->screen_options = array();
			$this->icon_styling = array();
			$this->title_styling = array();
			$this->subtitle_styling = array();
			$this->content_styling = array();
			$this->flipbox_front_styling = array();
			$this->flipbox_back_styling = array();
			$this->wrapper_styling = array();
			$this->list_styling = array();

			$this->icongrid_styling = '';
			$this->icongrid_numrow = '';
			$this->icongrid_borders = '';
			$this->custom_title_size = '';
			$this->custom_subtitle_size = '';
			$this->custom_content_size = '';
			$this->custom_icon_size = '';
		}

		/**
		 * @since 4.5.1
		 */
		public function __destruct() 
		{
			parent::__destruct();

			unset( $this->screen_options );
			unset( $this->icon_styling );
			unset( $this->title_styling );
			unset( $this->subtitle_styling );
			unset( $this->content_styling );
			unset( $this->flipbox_front_styling );
			unset( $this->flipbox_back_styling );
			unset( $this->wrapper_styling );
			unset( $this->list_styling );
		}


		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Icon Grid', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-icongrid.png';
			$this->config['order']			= 90;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_icongrid';
			$this->config['shortcode_nested'] = array( 'av_icongrid_item' );
			$this->config['tooltip']		= __( 'Creates an icon grid with toolips or flip content', 'avia_framework' );
			$this->config['preview']		= false;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Icon Grid Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'An Icon Grid Element Item', 'avia_framework' );
		}

		function extra_assets()
		{
			wp_enqueue_style( 'avia-module-icon', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/icon/icon.css' , array( 'avia-layout' ), false );
			wp_enqueue_style( 'avia-module-icongrid', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/icongrid/icongrid.css', array( 'avia-layout' ), false );

			wp_enqueue_script( 'avia-module-icongrid', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/icongrid/icongrid.js', array( 'avia-shortcodes' ), false, true );
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
							'template_id'	=> $this->popup_key( 'content_elements' ),
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
													$this->popup_key( 'styling_grid' ),
													$this->popup_key( 'styling_fonts' ),
													$this->popup_key( 'styling_padding' ),
													$this->popup_key( 'styling_font_colors' ),
													$this->popup_key( 'styling_background_colors' ),
													$this->popup_key( 'styling_border_colors' )
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
							'name'			=> __( 'Add/Edit Grid items', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the items of your item grid.', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Grid Item', 'avia_framework' ),
							'editable_item'	=> true,
							'lockable'		=> true,
							'std'			=> array(
													array(
														'title'	=> __( 'Grid Title 1', 'avia_framework' ), 
														'icon'	=> '43', 
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
													array(
														'title'	=> __('Grid Title 2', 'avia_framework' ), 
														'icon'	=> '25', 
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
													array(
														'title'	=>__('Grid Title 3', 'avia_framework' ), 
														'icon'	=>'64', 
														'content'	=> __( 'Enter content here', 'avia_framework' ),
													),
												),
							'subelements' 	=> $this->create_modal()
						),
				
						array(
							'name' 	=> __( 'Content Appearance', 'avia_framework' ),
							'desc' 	=> __( 'Change the appearance of your icon grid', 'avia_framework' ),
							'id' 	=> 'icongrid_styling',
							'type' 	=> 'select',
							'std' 	=> 'flipbox',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Content appears in Flip Box', 'avia_framework' )	=> 'flipbox',
												__( 'Content appears in Tooltip', 'avia_framework' )	=> 'tooltip',
											)
						),
				
						array(
							'name' 	=> __( 'Mobile Flip Box Behaviour', 'avia_framework' ),
							'desc' 	=> __( 'Select the behaviour of an open flib box on mobile devices and touch screens', 'avia_framework' ),
							'id' 	=> 'flipbox_force_close',
							'type' 	=> 'select',
							'std' 	=> 'flipbox',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Close when visitor clicks on flipbox ', 'avia_framework' )		=> '',
												__( 'Close when user clicks outside icongrid', 'avia_framework' )	=> 'avia_flip_force_close',
											)
						),
				);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_elements' ), $c );
			
			/**
			 * Styling Tab
			 * ============
			 */
			$c = array(
						array(
							'name' 	=> __( 'Columns', 'avia_framework' ),
							'desc' 	=> __( 'Define the number of columns, depending on the amount of text you want to add.', 'avia_framework' ),
							'id' 	=> 'icongrid_numrow',
							'type' 	=> 'select',
							'std' 	=> '3',
							'lockable'	=> true,
							'subtype'	=> array(
												__( '3 Items', 'avia_framework' )	=> '3',
												__( '4 Items', 'avia_framework' )	=> '4',
												__( '5 Items', 'avia_framework' )	=> '5',
											)
						),
				
						array(
							'name'		=> __( 'Grid Borders', 'avia_framework' ),
							'desc'		=> __( 'Define the appearance of the grid borders here.', 'avia_framework' ),
							'id'		=> 'icongrid_borders',
							'type'		=> 'select',
							'std'		=> 'none',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'No Borders', 'avia_framework' )	=> 'none',
												__( 'Borders between elements', 'avia_framework' )	=> 'between',
												__( 'All Borders', 'avia_framework' )	=> 'all',
											)
						),
				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Grid Styling', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_grid' ), $template );
			
			
			$c = array(
						array(
							'name'			=> __( 'Title Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the titles.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_title_size',
												'medium'	=> 'av-medium-font-size-title',
												'small'		=> 'av-small-font-size-title',
												'mini'		=> 'av-mini-font-size-title'
											)
						),
				
						array(
							'name'			=> __( 'Sub-Title Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the sub titles.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_subtitle_size',
												'medium'	=> 'av-medium-font-size-1',
												'small'		=> 'av-small-font-size-1',
												'mini'		=> 'av-mini-font-size-1'
											)
						),
				
						array(
							'name'			=> __( 'Content Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the content on the backside of the flipbox or the tooltip.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_content_size',
												'medium'	=> 'av-medium-font-size',
												'small'		=> 'av-small-font-size',
												'mini'		=> 'av-mini-font-size'
											)
						),
				
						array(
							'name'			=> __( 'Icon Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the icon.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_icon_size',
												'medium'	=> 'av-medium-font-size-2',
												'small'		=> 'av-small-font-size-2',
												'mini'		=> 'av-mini-font-size-2'
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
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_fonts' ), $template );
			
			$c = array(
						array(
							'name' 	=> __( 'Items Padding', 'avia_framework' ),
							'desc' 	=> __( 'Set the padding for the icongrid container', 'avia_framework' ),
							'id' 	=> 'icongrid_padding',
							'type' 	=> 'multi_input',
							'std' 	=> '',
							'sync' 	=> true,
							'lockable'	=> true,
							'multi'		=> array(
												'top'		=> __( 'Top-Left-Padding', 'avia_framework' ),
												'right'		=> __( 'Top-Right-Padding', 'avia_framework' ),
												'bottom'	=> __( 'Bottom-Right-Padding', 'avia_framework' ),
												'left'		=> __( 'Bottom-Left-Padding', 'avia_framework' ),
											)
						),
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Padding', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_padding' ), $template );
			
			$c = array(
						array(
							'name' 	=> __( 'Font Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'font_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )	=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Icon Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_icon',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'container_class'	=> 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Title Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_title',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Sub-Title Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_subtitle',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Content Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_content',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						)
				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Colors', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_font_colors' ), $template );
			
			$c = array(
						array(
							'name' 	=> __( 'Background Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'bg_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )	=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Background Front','avia_framework' ),
							'desc' 	=> __( 'Select the type of background.', 'avia_framework' ),
							'id' 	=> 'custom_front_bg_type',
							'type' 	=> 'select',
							'std' 	=> 'bg_color',
							'lockable'	=> true,
							'required'	=> array( 'bg_color', 'equals', 'custom' ),
							'subtype'	=> array(
												__( 'Background Color', 'avia_framework' )		=> 'bg_color',
												__( 'Background Gradient', 'avia_framework' )	=> 'bg_gradient',
											)
						),

						array(
							'name' 	=> __( 'Custom Background Color Front', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_front_bg',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'custom_front_bg_type', 'equals', 'bg_color' )
						),

						array(
							'name' 	=> __( 'Front Gradient Color 1', 'avia_framework' ),
							'desc' 	=> __( 'Select the first color for the gradient.', 'avia_framework' ),
							'id' 	=> 'custom_front_gradient_color1',
							'type' 	=> 'colorpicker',
							'rgba' 	=> true,
							'std' 	=> '',
							'container_class' => 'av_third av_third_first',
							'lockable'	=> true,
							'required'	=> array( 'custom_front_bg_type', 'equals', 'bg_gradient' ),
						),
				
						array(
							'name' 	=> __( 'Front Gradient Color 2', 'avia_framework' ),
							'desc' 	=> __( 'Select the second color for the gradient.', 'avia_framework' ),
							'id' 	=> 'custom_front_gradient_color2',
							'type' 	=> 'colorpicker',
							'rgba' 	=> true,
							'std' 	=> '',
							'container_class'	=> 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'custom_front_bg_type', 'equals', 'bg_gradient' ),
						),

						array(
							'name' 	=> __( 'Front Gradient Direction','avia_framework' ),
							'desc' 	=> __( 'Define the gradient direction', 'avia_framework' ),
							'id' 	=> 'custom_front_gradient_direction',
							'type' 	=> 'select',
							'std' 	=> 'vertical',
							'container_class'	=> 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'custom_front_bg_type', 'equals', 'bg_gradient' ),
							'subtype'	=> array(
												__( 'Vertical', 'avia_framework' )		=> 'vertical',
												__( 'Horizontal', 'avia_framework' )	=> 'horizontal',
												__( 'Radial', 'avia_framework' )		=> 'radial',
												__( 'Diagonal Top Left to Bottom Right', 'avia_framework' )	=> 'diagonal_tb',
												__( 'Diagonal Bottom Left to Top Right', 'avia_framework' )	=> 'diagonal_bt',
											)
						),

						array(
							'name' 	=> __( 'Custom Background Back / Tooltip', 'avia_framework' ),
							'desc' 	=> __( 'Select the type of background.', 'avia_framework' ),
							'id' 	=> 'custom_back_bg_type',
							'type' 	=> 'select',
							'std' 	=> 'bg_color',
							'lockable'	=> true,
							'required'	=> array( 'bg_color', 'equals', 'custom' ),
							'subtype'	=> array(
												__( 'Background Color','avia_framework' )		=> 'bg_color',
												__( 'Background Gradient','avia_framework' )	=> 'bg_gradient',
											)
						),

						array(
							'name' 	=> __( 'Custom Background Color Back / Tooltip', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_back_bg',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'custom_back_bg_type', 'equals', 'bg_color' )
						),

						array(
							'name' 	=> __( 'Back Gradient Color 1', 'avia_framework' ),
							'desc' 	=> __( 'Select the first color for the gradient.', 'avia_framework' ),
							'id' 	=> 'custom_back_gradient_color1',
							'type' 	=> 'colorpicker',
							'rgba' 	=> true,
							'std' 	=> '',
							'container_class'	=> 'av_third av_third_first',
							'lockable'	=> true,
							'required'	=> array( 'custom_back_bg_type', 'equals', 'bg_gradient' )
						),
				
						array(
							'name' 	=> __( 'Back Gradient Color 2', 'avia_framework' ),
							'desc' 	=> __( 'Select the second color for the gradient.', 'avia_framework' ),
							'id' 	=> 'custom_back_gradient_color2',
							'type' 	=> 'colorpicker',
							'rgba' 	=> true,
							'std' 	=> '',
							'container_class'	=> 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'custom_back_bg_type', 'equals', 'bg_gradient' )
						),

						array(
							'name' 	=> __( 'Back Gradient Direction', 'avia_framework' ),
							'desc' 	=> __( 'Define the gradient direction', 'avia_framework' ),
							'id' 	=> 'custom_back_gradient_direction',
							'type' 	=> 'select',
							'container_class'	=> 'av_third',
							'std' 	=> 'vertical',
							'lockable'	=> true,
							'required'	=> array( 'custom_back_bg_type', 'equals', 'bg_gradient' ),
							'subtype'	=> array(
												__( 'Vertical', 'avia_framework' )		=> 'vertical',
												__( 'Horizontal', 'avia_framework' )	=> 'horizontal',
												__( 'Radial', 'avia_framework' )		=> 'radial',
												__( 'Diagonal Top Left to Bottom Right', 'avia_framework' )	=> 'diagonal_tb',
												__( 'Diagonal Bottom Left to Top Right', 'avia_framework' )	=> 'diagonal_bt',
											)
						),
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background Colors', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background_colors' ), $template );
			
			$c = array(
						array(
							'name' 	=> __( 'Border Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'border_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Default', 'avia_framework' )	=> '',
											__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
										),
						),

						array(
							'name' 	=> __( 'Custom Grid Border Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom grid color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_grid',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'border_color', 'equals', 'custom' )
						),
				
						array(
							'name' 	=> __( 'Custom Tooltip Border Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_tooltip_border',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'border_color', 'equals', 'custom' )
						)

				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Border Colors', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_border_colors' ), $template );
			
			/**
			 * Animation Tab
			 * =============
			 */
			
			$c = array(
						array(
							'name' 	=> __( 'Rotation Of Flip Box', 'avia_framework' ),
							'desc' 	=> __( 'Select the rotation axis for the flip box', 'avia_framework' ),
							'id' 	=> 'flip_axis',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'icongrid_styling', 'equals', 'flipbox' ),
							'subtype'	=> array(
												__( 'Rotate Y-axis', 'avia_framework' )	=> '',
												__( 'Rotate X-axis', 'avia_framework' )	=> 'avia-flip-x',
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
					
		}
		
		/**
		 * Creates the modal popup for a single icongrid entry
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
													$this->popup_key( 'modal_content_front' ),
													$this->popup_key( 'modal_content_back' ),
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
													$this->popup_key( 'modal_styling_heading' ),
													$this->popup_key( 'modal_styling_font_colors' ),
													$this->popup_key( 'modal_styling_background_colors' ),
													$this->popup_key( 'modal_styling_border_colors' ),
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
							'name' 	=> __( 'Grid Item Title', 'avia_framework' ),
							'desc' 	=> __( 'Enter the grid item title here (Better keep it short)', 'avia_framework' ) ,
							'id' 	=> 'title',
							'type' 	=> 'input',
							'std' 	=> 'Grid Title',
							'lockable'	=> true
						),
				
						array(
							'name' 	=> __( 'Grid Item Sub-Title', 'avia_framework' ),
							'desc' 	=> __( 'Enter the grid item sub-title here', 'avia_framework' ) ,
							'id' 	=> 'subtitle',
							'type' 	=> 'input',
							'std' 	=> 'Grid Sub-Title',
							'lockable'	=> true
						),
				
						array(
							'name' 	=> __( 'Grid Item Icon', 'avia_framework' ),
							'desc' 	=> __( 'Select an icon for your grid item below', 'avia_framework' ),
							'id' 	=> 'icon',
							'type' 	=> 'iconfont',
							'std' 	=> '',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' )
						)
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Grid Element Front Content', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_front' ), $template );
			
			
			$c = array(
						array(
							'name' 	=> __( 'Grid Item Content', 'avia_framework' ),
							'desc' 	=> __( 'Enter some content here. Will be used as backside of flipbox or tooltip popup.', 'avia_framework' ) ,
							'id' 	=> 'content',
							'type' 	=> 'tiny_mce',
							'std' 	=> __( 'Grid Content goes here', 'avia_framework' ),
							'lockable'	=> true
						)
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Grid Element Backside/Tooltip Content', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_back' ), $template );
			
			
			$c = array(
						array(	
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Title Link?', 'avia_framework' ),
							'desc'			=> __( 'Do you want to apply a link to the title?', 'avia_framework' ),
							'lockable'		=> true,
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'no_toggle'		=> true
						)
				
				);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $c );
			
			/**
			 * Styling Tab
			 * ===========
			 */
			$c = array(
						array(	
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'h4',
							'context'			=> __CLASS__,
							'lockable'			=> true,
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
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_heading' ), $template );
			
			
			$c = array(
						array(
							'name' 	=> __( 'Font Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'item_font_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Default', 'avia_framework' )		=> '',
											__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
										),
						),

						array(
							'name' 	=> __( 'Custom Icon Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'item_custom_icon',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'item_font_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Title Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'item_custom_title',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'item_font_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Sub-Title Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'item_custom_subtitle',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'item_font_color', 'equals','custom' )
						),

						array(
							'name' 	=> __( 'Custom Content Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'item_custom_content',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'item_font_color', 'equals', 'custom' )
						)

				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Colors', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_font_colors' ), $template );
			
			$c = array(
						array(
							'name' 	=> __( 'Background Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'item_bg_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Default', 'avia_framework' )	=> '',
											__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
										),
						),

						array(
							'name' 	=> __( 'Custom Background Front', 'avia_framework' ),
							'desc' 	=> __( 'Select the type of background.', 'avia_framework' ),
							'id' 	=> 'item_custom_front_bg_type',
							'type' 	=> 'select',
							'std' 	=> 'bg_color',
							'lockable'	=> true,
							'required'	=> array( 'item_bg_color', 'equals', 'custom' ),
							'subtype'	=> array(
											__( 'Background Color', 'avia_framework' )		=> 'bg_color',
											__( 'Background Gradient', 'avia_framework' )	=> 'bg_gradient',
										)
						),

						array(
							'name' 	=> __( 'Custom Background Color Front', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'item_custom_front_bg',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'item_custom_front_bg_type', 'equals', 'bg_color' )
						),

						array(
							'name' 	=> __( 'Front Gradient Color 1', 'avia_framework' ),
							'desc' 	=> __( 'Select the first color for the gradient.', 'avia_framework' ),
							'id' 	=> 'item_custom_front_gradient_color1',
							'type' 	=> 'colorpicker',
							'rgba' 	=> true,
							'std' 	=> '',
							'container_class' => 'av_third av_third_first',
							'lockable'	=> true,
							'required'	=> array( 'item_custom_front_bg_type', 'equals', 'bg_gradient' )
						),
				
						array(
							'name' 	=> __( 'Front Gradient Color 2', 'avia_framework' ),
							'desc' 	=> __( 'Select the second color for the gradient.', 'avia_framework' ),
							'id' 	=> 'item_custom_front_gradient_color2',
							'type' 	=> 'colorpicker',
							'rgba' 	=> true,
							'std' 	=> '',
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'item_custom_front_bg_type', 'equals', 'bg_gradient' )
						),

						array(
							'name' 	=> __( 'Front Gradient Direction', 'avia_framework' ),
							'desc' 	=> __( 'Define the gradient direction', 'avia_framework' ),
							'id' 	=> 'item_custom_front_gradient_direction',
							'type' 	=> 'select',
							'std' 	=> 'vertical',
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'item_custom_front_bg_type', 'equals', 'bg_gradient' ),
							'subtype'	=> array(
											__( 'Vertical', 'avia_framework' )		=> 'vertical',
											__( 'Horizontal', 'avia_framework' )	=> 'horizontal',
											__( 'Radial', 'avia_framework' )		=>'radial',
											__( 'Diagonal Top Left to Bottom Right', 'avia_framework' )	=> 'diagonal_tb',
											__( 'Diagonal Bottom Left to Top Right', 'avia_framework' )	=> 'diagonal_bt',
										)
						),

						array(
							'name' 	=> __( 'Custom Background Back / Tooltip','avia_framework' ),
							'desc' 	=> __( 'Select the type of background.', 'avia_framework' ),
							'id' 	=> 'item_custom_back_bg_type',
							'type' 	=> 'select',
							'std' 	=> 'bg_color',
							'lockable'	=> true,
							'required'	=> array( 'item_bg_color', 'equals', 'custom' ),
							'subtype'	=> array(
											__( 'Background Color', 'avia_framework' )		=> 'bg_color',
											__( 'Background Gradient', 'avia_framework' )	=> 'bg_gradient',
										)
                                    ),
						array(
							'name' 	=> __( 'Custom Background Color Back / Tooltip', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'item_custom_back_bg',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'item_custom_back_bg_type', 'equals', 'bg_color' )
						),

						array(
							'name' 	=> __( 'Back Gradient Color 1', 'avia_framework' ),
							'desc' 	=> __( 'Select the first color for the gradient.', 'avia_framework' ),
							'id' 	=> 'item_custom_back_gradient_color1',
							'type' 	=> 'colorpicker',
							'rgba' 	=> true,
							'std' 	=> '',
							'container_class' => 'av_third av_third_first',
							'lockable'	=> true,
							'required'	=> array( 'item_custom_back_bg_type', 'equals', 'bg_gradient' )
						),
				
						array(
							'name' 	=> __( 'Back Gradient Color 2', 'avia_framework' ),
							'desc' 	=> __( 'Select the second color for the gradient.', 'avia_framework' ),
							'id' 	=> 'item_custom_back_gradient_color2',
							'type' 	=> 'colorpicker',
							'rgba' 	=> true,
							'std' 	=> '',
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required' => array( 'item_custom_back_bg_type', 'equals', 'bg_gradient' )
						),
	
						array(
							'name' 	=> __( 'Back Gradient Direction', 'avia_framework' ),
							'desc' 	=> __( 'Define the gradient direction', 'avia_framework' ),
							'id' 	=> 'item_custom_back_gradient_direction',
							'type' 	=> 'select',
							'container_class' => 'av_third',
							'std' 	=> 'vertical',
							'lockable'	=> true,
							'required'	=> array( 'item_custom_back_bg_type', 'equals', 'bg_gradient' ),
							'subtype'	=> array(
											__( 'Vertical', 'avia_framework' )		=> 'vertical',
											__( 'Horizontal', 'avia_framework' )	=> 'horizontal',
											__( 'Radial', 'avia_framework' )		=> 'radial',
											__( 'Diagonal Top Left to Bottom Right', 'avia_framework' )	=> 'diagonal_tb',
											__( 'Diagonal Bottom Left to Top Right', 'avia_framework' )	=> 'diagonal_bt',
										)
						)
				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background Colors', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_background_colors' ), $template );
			
			$c = array(
						array(
							'name' 	=> __( 'Border Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'item_border_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Default', 'avia_framework' )	=> '',
											__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
										),
						),
				
						array(
							'name' 	=> __( 'Custom Tooltip Border Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'item_custom_tooltip_border',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'item_border_color', 'equals', 'custom' )
						)
				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Border Colors', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_border_colors' ), $template );
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
			
			$template = $this->update_template_lockable( 'title', __( 'Element', 'avia_framework' ). ': {{title}}', $locked );

			extract( av_backend_icon( array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon" and the font as "font" 

			$params['innerHtml']  = '';
			$params['innerHtml'] .=		"<div class='avia_title_container' data-update_element_template='yes'>";
			$params['innerHtml'] .=			'<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
			$params['innerHtml'] .=				'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_tab_icon'>{$display_char}</span>";
			$params['innerHtml'] .=			'</span>';
			$params['innerHtml'] .=			"<span {$template} >" . __( 'Element', 'avia_framework' ) . ": {$attr['title']}</span>";
			$params['innerHtml'] .=		'</div>';

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
			$default = array(
						'font_color'			=> '',
						'custom_icon'			=> '',
						'custom_title'			=> '',
						'custom_subtitle'		=> '',
						'custom_content'		=> '',
						'icongrid_padding'      => '',
						'bg_color'				=> '',
						'custom_front_bg_type'	=> '',
						'custom_front_bg'		=> '',
						'custom_front_gradient_color1'		=> '',
						'custom_front_gradient_color2'		=> '',
						'custom_front_gradient_direction'	=> '',
						'custom_back_bg_type'	=> '',
						'custom_back_bg'		=> '',
						'custom_back_gradient_color1'		=> '',
						'custom_back_gradient_color2'		=> '',
						'custom_back_gradient_direction'	=> '',
						'custom_tooltip_border' => '',
						'border_color'			=> '',
						'custom_grid'			=> '',
						'icongrid_styling'		=> '',
						'flipbox_force_close'	=> '',
						'flip_axis'				=> '',
						'icongrid_numrow'		=> '',
						'icongrid_borders'		=> '',
						'custom_title_size'		=> '',
						'custom_subtitle_size'	=> '',
						'custom_content_size'	=> '',
						'custom_icon_size'		=> ''
					);
			
			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );
			
			$this->screen_options = AviaHelper::av_mobile_sizes( $atts );
			extract( $this->screen_options ); //return $av_font_classes, $av_title_font_classes and $av_display_classes
			
			extract( shortcode_atts( $default, $atts, $this->config['shortcode'] ) );
			

			$this->icon_styling = array();
			$this->title_styling = array();
			$this->subtitle_styling = array();
			$this->content_styling = array();
			$this->flipbox_front_styling = array();
			$this->flipbox_back_styling = array();
			$this->wrapper_styling = array();
			$this->list_styling = array();

			$this->icongrid_styling = 'avia-icongrid-' . $icongrid_styling;
			$this->icongrid_numrow = 'avia-icongrid-numrow-' . $icongrid_numrow;
			$this->icongrid_borders = 'avia-icongrid-borders-' . $icongrid_borders;

			$this->custom_title_size = $custom_title_size;
			$this->custom_subtitle_size = $custom_subtitle_size;
			$this->custom_content_size = $custom_content_size;
			$this->custom_icon_size = $custom_icon_size;

			if( $font_color == 'custom' ) 
			{
				if ( ! empty( $custom_icon ) ) 
				{
					$this->icon_styling['color'] = $custom_icon;
				}
				
				if ( ! empty( $custom_title) ) 
				{
					$this->title_styling['color'] = $custom_title;
				}
				
				if ( ! empty( $custom_subtitle ) ) 
				{
					$this->subtitle_styling['color'] = $custom_subtitle;
				}
				
				if ( ! empty( $custom_content ) ) 
				{
					$this->content_styling['color'] = $custom_content;
				}
			}

			if( $bg_color == 'custom' ) 
			{
				// front
				if( $custom_front_bg_type == 'bg_color' ) 
				{
					if( ! empty($custom_front_bg) ) 
					{
						$this->flipbox_front_styling['background_color'] = $custom_front_bg;
					}
				}
				else 
				{
					// gradient
					$front_gradient_settings = array(
												$custom_front_gradient_direction,
												$custom_front_gradient_color1,
												$custom_front_gradient_color2
											);

					// fallback
					$this->flipbox_front_styling['background_color'] = $custom_front_gradient_color1;
					// gradient
					$this->flipbox_front_styling['background'] = AviaHelper::css_background_string( array(), $front_gradient_settings );
				}

				// back
				if( $custom_back_bg_type == 'bg_color' ) 
				{
					if ( ! empty( $custom_back_bg ) ) 
					{
						$this->flipbox_back_styling['background_color'] = $custom_back_bg;
					}
				}
				else 
				{
					$back_gradient_settings = array(
												$custom_back_gradient_direction,
												$custom_back_gradient_color1,
												$custom_back_gradient_color2
											);

					// fallback
					$this->flipbox_back_styling['background_color'] = $custom_back_gradient_color1;
					// gradient
					$this->flipbox_back_styling['background'] = AviaHelper::css_background_string( array(), $back_gradient_settings );
				}

			}

			if( 'custom' == $border_color )
			{
				if ( ! empty( $custom_grid ) )
				{
				   $this->wrapper_styling['color'] = $custom_grid;
				   $this->list_styling['border-color'] = $custom_grid;
				}

				// tooltip border color
				if( ! empty( $custom_tooltip_border ) )
				{
				   $this->flipbox_back_styling['border_color'] = $custom_tooltip_border;
				}
			}

			$this->flipbox_front_styling['padding'] = AviaHelper::css_4value_helper( $icongrid_padding );
			$this->flipbox_back_styling['padding'] = AviaHelper::css_4value_helper( $icongrid_padding );

			$list_styling_str = '';
			if( ! empty( $this->list_styling ) ) 
			{
				if( array_key_exists( 'border-color', $this->list_styling ) )
				{
					$list_styling_str = AviaHelper::style_string( $this->list_styling, 'border-color', 'border-color' );
				}
			}
			$list_styling_str = ( $list_styling_str !== '' ) ? AviaHelper::style_string( $list_styling_str ) : '';

			$output	 = '';
			$output .=	"<div {$meta['custom_el_id']} class='avia-icon-grid-container {$flip_axis} {$av_display_classes} {$meta['el_class']}'>";
			$output .=		"<ul id='avia-icongrid-" . uniqid() . "' class='clearfix avia-icongrid {$this->icongrid_styling} {$flipbox_force_close} {$this->icongrid_numrow} {$this->icongrid_borders} avia_animate_when_almost_visible' {$list_styling_str}>";
			$output .=			ShortcodeHelper::avia_remove_autop( $content, true );
			$output .=		'</ul>';
			$output .=	'</div>';

			return $output;
		}

		/**
		 * Shortcode Handler
		 * 
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_icongrid_item( $atts, $content = '', $shortcodename = '' )
		{
			/**
			 * Fixes a problem when 3-rd party plugins call nested shortcodes without executing main shortcode  (like YOAST in wpseo-filter-shortcodes)
			 */
			if( empty( $this->screen_options ) )
			{
				return '';
			}
			
			$default = array(
							'title'				=> '',
							'subtitle'			=> '',
							'link'				=> '',
							'icon'				=> '',
							'font'				=>'',
							'linktarget'		=> '',
							'custom_markup'		=> '',
							'item_font_color'	=> '',
							'item_custom_icon'	=> '',
							'item_custom_title'	=> '',
							'item_custom_subtitle'				=> '',
							'item_custom_content'				=> '',
							'item_bg_color'						=> '',
							'item_custom_front_bg_type'			=> '',
							'item_custom_front_gradient_color1'	=> '',
							'item_custom_front_gradient_color2'	=> '',
							'item_custom_front_gradient_direction' => '',
							'item_custom_front_bg'				=> '',
							'item_custom_back_bg_type'			=> '',
							'item_custom_back_bg'				=> '',
							'item_custom_back_gradient_color1'	=> '',
							'item_custom_back_gradient_color2'	=> '',
							'item_custom_back_gradient_direction' => '',
							'item_border_color'					=> '',
							'item_custom_tooltip_border'		=> ''
			);
			
			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $this->config['shortcode_nested'][0], $default, $locked, $content );
			$meta = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $atts );
			
			$atts = shortcode_atts( $default, $atts, 'av_icongrid_item' );
                
			extract( $this->screen_options ); //return $av_font_classes, $av_title_font_classes and $av_display_classes
				
			

         
			$icon_styling = ! empty( $this->icon_styling ) ? $this->icon_styling : array();
			$title_styling = ! empty( $this->title_styling ) ? $this->title_styling : array();
			$subtitle_styling = ! empty( $this->subtitle_styling ) ? $this->subtitle_styling : array();
			$content_styling = ! empty( $this->content_styling ) ? $this->content_styling : array();
			$flipbox_front_styling = ! empty( $this->flipbox_front_styling ) ? $this->flipbox_front_styling : array();
			$flipbox_back_styling = ! empty( $this->flipbox_back_styling ) ? $this->flipbox_back_styling : array();

			$icon_styling_str = '';
			$title_styling_str = '';
			$subtitle_styling_str = '';
			$content_styling_str = '';
			$flipbox_front_styling_str = '';
			$item_bg_str = '';
			$flipbox_back_styling_str = '';
			$wrapper_styling_str = '';

			/* item specific styling */
			if( $atts['item_font_color'] == 'custom' ) 
			{
				if( ! empty( $atts['item_custom_icon'] ) ) 
				{
					$icon_styling['color'] = $atts['item_custom_icon'];
				}
				
				if( ! empty( $atts['item_custom_title']) ) 
				{
					$title_styling['color'] = $atts['item_custom_title'];
				}
				
				if( ! empty( $atts['item_custom_subtitle'] ) ) 
				{
					$subtitle_styling['color'] = $atts['item_custom_subtitle'];
				}
				
				if( ! empty( $atts['item_custom_content'] ) ) 
				{
					$content_styling['color'] = $atts['item_custom_content'];
				}
			}

			if( $atts['item_bg_color'] == 'custom' ) 
			{
				// front
				if( $atts['item_custom_front_bg_type'] == 'bg_color' ) 
				{
					if( ! empty($atts['item_custom_front_bg']) ) 
					{
						$flipbox_front_styling['background_color'] = $atts['item_custom_front_bg'];
					}
					
					// remove gradient if any
					if( array_key_exists( 'background', $flipbox_front_styling ) ) 
					{
						unset( $flipbox_front_styling['background'] );
					}
				}
				else 
				{
					$item_front_gradient_settings = array(
														$atts['item_custom_front_gradient_direction'],
														$atts['item_custom_front_gradient_color1'],
														$atts['item_custom_front_gradient_color2']
													);
					
					// fallback
					$flipbox_front_styling['background_color'] = $atts['item_custom_front_gradient_color1'];
					// gradient
					$flipbox_front_styling['background'] = AviaHelper::css_background_string( array(), $item_front_gradient_settings );
				}
				
				// back
				if( $atts['item_custom_back_bg_type'] == 'bg_color' ) 
				{
					if( ! empty($atts['item_custom_back_bg']) ) 
					{
						$flipbox_back_styling['background_color'] = $atts['item_custom_back_bg'];
					}
					
					// remove gradient if any
					if( array_key_exists( 'background', $flipbox_back_styling ) ) 
					{
						unset( $flipbox_back_styling['background'] );
					}
				}
				else 
				{
					$item_back_gradient_settings = array(
														$atts['item_custom_back_gradient_direction'],
														$atts['item_custom_back_gradient_color1'],
														$atts['item_custom_back_gradient_color2']
													);
					// fallback
					$flipbox_back_styling['background_color'] = $atts['item_custom_back_gradient_color1'];
					// gradient
					$flipbox_back_styling['background'] = AviaHelper::css_background_string( array(), $item_back_gradient_settings );
				}
			}
				
			if( 'custom' == $atts['item_border_color'] )
			{
				if ( ! empty( $atts['item_custom_tooltip_border'] ) ) 
				{
					$flipbox_back_styling['border_color'] = $atts['item_custom_tooltip_border'];
				}
			}

			if( is_numeric( $this->custom_title_size ) )
			{
				$title_styling['font-size'] = $this->custom_title_size . 'px';
			}

			if( is_numeric( $this->custom_subtitle_size ) )
			{
				$subtitle_styling['font-size'] = $this->custom_subtitle_size . 'px';
			}
				
			if( is_numeric( $this->custom_icon_size ) )
			{
				$icon_styling['font-size'] = $this->custom_icon_size . 'px';
			}

			if( is_numeric( $this->custom_content_size ) )
			{
				$content_styling['font-size'] = $this->custom_content_size . 'px';
			}

			foreach( $title_styling as $key => $value ) 
			{
				$title_styling_str .= AviaHelper::style_string( $title_styling, $key, $key );
			}
				
			foreach( $subtitle_styling as $key => $value ) 
			{
				$subtitle_styling_str .= AviaHelper::style_string( $subtitle_styling, $key, $key );
			}

			foreach( $icon_styling as $key => $value ) 
			{
				$icon_styling_str .= AviaHelper::style_string( $icon_styling, $key, $key );
			}
                
			foreach( $content_styling as $key => $value ) 
			{
				$content_styling_str .= AviaHelper::style_string( $content_styling, $key, $key );
			}
                
			if( ! empty( $flipbox_front_styling ) )
			{
				// flipbox                    
				if( $this->icongrid_styling == 'avia-icongrid-flipbox')
				{
					// gradients
					if( array_key_exists( 'background_color', $flipbox_front_styling ) && array_key_exists( 'background', $flipbox_front_styling ) ) 
					{
						$flipbox_front_styling_str .= AviaHelper::style_string( $flipbox_front_styling, 'background', 'background' );
						$flipbox_front_styling_str .= AviaHelper::style_string( $flipbox_front_styling, 'background_color', 'background-color' );
					}
					// solid bg color
					else if( array_key_exists( 'background_color', $flipbox_front_styling ) ) 
					{
						$flipbox_front_styling_str .= AviaHelper::style_string( $flipbox_front_styling, 'background_color', 'background-color' );
					}
				}    
				
				// tooltip
				if( $this->icongrid_styling == 'avia-icongrid-tooltip' )
				{
					// gradients
					if( array_key_exists( 'background_color', $flipbox_front_styling ) && array_key_exists( 'background', $flipbox_front_styling ) ) 
					{
						$item_bg_str .= AviaHelper::style_string( $flipbox_front_styling, 'background', 'background' );
						$item_bg_str .= AviaHelper::style_string( $flipbox_front_styling, 'background_color', 'background-color' );
					}
					// solid bg color
					elseif( array_key_exists( 'background_color', $flipbox_front_styling ) ) 
					{
						$item_bg_str .= AviaHelper::style_string( $flipbox_front_styling, 'background_color', 'background-color' );
					}
				}    

				if( array_key_exists( 'padding', $this->flipbox_front_styling ) )
				{
					$flipbox_front_styling_str .= AviaHelper::style_string( $this->flipbox_front_styling, 'padding', 'padding' );
				}                    
			}

			if( ! empty( $flipbox_back_styling ) )
			{
				// gradients
				if( array_key_exists( 'background_color', $flipbox_back_styling ) && array_key_exists( 'background', $flipbox_back_styling ) ) 
				{
					$flipbox_back_styling_str .= AviaHelper::style_string( $flipbox_back_styling, 'background', 'background' );
					$flipbox_back_styling_str .= AviaHelper::style_string( $flipbox_back_styling, 'background_color', 'background-color' );
				}
				// solid bg color
				else if( array_key_exists( 'background_color', $flipbox_back_styling ) ) 
				{
					$flipbox_back_styling_str .= AviaHelper::style_string( $flipbox_back_styling, 'background_color', 'background-color' );
				}

				// tooltip border color
				if( $this->icongrid_styling == 'avia-icongrid-tooltip' )
				{
					if( array_key_exists( 'border_color', $this->flipbox_back_styling ) )
					{
						$flipbox_back_styling_str .= AviaHelper::style_string( $flipbox_back_styling, 'border_color', 'border-color' );
					}
				}

				if( array_key_exists( 'padding', $this->flipbox_back_styling ) )
				{
					$flipbox_back_styling_str .= AviaHelper::style_string( $this->flipbox_back_styling, 'padding', 'padding' );
				}                    
			}

			if( ! empty( $this->wrapper_styling ) ) 
			{
				if( array_key_exists( 'color', $this->wrapper_styling ) )
				{
					$wrapper_styling_str = AviaHelper::style_string( $this->wrapper_styling, 'color', 'color' );
				}
			}

			/* element wide styling */
			$icon_styling_str = ( $icon_styling_str !== '' ) ? AviaHelper::style_string( $icon_styling_str ) : '';
			$title_styling_str = ( $title_styling_str !== '' ) ? AviaHelper::style_string( $title_styling_str ) : '';
			$subtitle_styling_str = ( $subtitle_styling_str !== '' ) ? AviaHelper::style_string( $subtitle_styling_str ) : '';
			$content_styling_str = ( $content_styling_str !== '' ) ? AviaHelper::style_string( $content_styling_str ) : '';
			$flipbox_front_styling_str = ( $flipbox_front_styling_str !== '' ) ? AviaHelper::style_string( $flipbox_front_styling_str ) : '';

			$item_bg_str = ( $item_bg_str !== '' ) ? AviaHelper::style_string( $item_bg_str ) : '';
			$flipbox_back_styling_str = ( $flipbox_back_styling_str !== '' ) ? AviaHelper::style_string( $flipbox_back_styling_str ) : '';
			$wrapper_styling_str = ( $wrapper_styling_str !== '' ) ? AviaHelper::style_string( $wrapper_styling_str ) : '';

			$display_char = av_icon( $atts['icon'], $atts['font'] );
			$display_char_wrapper = array();

			$blank = AviaHelper::get_link_target( $atts['linktarget'] );
			
			$avia_icongrid_wrapper = array(
										'start'	=> 'div',
										'end'	=> 'div'
									);

			if( ! empty( $atts['link'] ) )
			{
				$atts['link'] = AviaHelper::get_url( $atts['link'] );

				if( ! empty( $atts['link'] ) )
				{
					$linktitle = $atts['title'];

					$avia_icongrid_wrapper['start'] = "a href='{$atts['link']}' title='" . esc_attr( $linktitle ) . "' {$blank}";
					$avia_icongrid_wrapper['end'] = 'a';
				}
			}
               
			$contentClass = '';
			if( trim( $content ) == '' )
			{
				$contentClass = 'av-icongrid-empty';
			}

			$title_el = ! empty( $meta['heading_tag'] ) ? $meta['heading_tag'] : 'h4';
			$title_el_cls = ! empty( $meta['heading_class'] ) ? $meta['heading_class'] : '';

			$subtitle_el = 'h6';
			$icongrid_title = '';
			$icongrid_subtitle = '';
			$touch_js = " ontouchstart='this.classList.toggle(\"av-flip\");'";

			$output  = '<li>';
			$output .=	"<{$avia_icongrid_wrapper['start']} class='avia-icongrid-wrapper' {$wrapper_styling_str}>";
			$output .=		'<article ' . $item_bg_str . ' class="article-icon-entry ' . $contentClass . '" ' . avia_markup_helper( array('context' => 'entry', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) ) . '>';
			$output .=			"<div class='avia-icongrid-front' {$flipbox_front_styling_str}>";
			$output .=				"<div class='avia-icongrid-inner'>";
			$output .=					"<div {$icon_styling_str} class='avia-icongrid-icon {$av_font_classes_2} avia-font-{$atts['font']}'><span class='icongrid-char ' {$display_char}></span></div>";
			$output .=					'<header class="entry-content-header">';

			$markup = avia_markup_helper( array( 'context' => 'entry_title', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );
			$submarkup = avia_markup_helper( array( 'context' => 'entry_subtitle', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );
			
			if( ! empty( $atts['title'] ) ) 
			{
				$output .=					"<{$title_el} class='av_icongrid_title icongrid_title{$icongrid_title} {$av_title_font_classes} {$title_el_cls}' {$markup} {$title_styling_str}>" . esc_html( $atts['title'] ). "</{$title_el}>";
			}
			if( ! empty( $atts['subtitle'] ) ) 
			{
				$output .=					"<{$subtitle_el} class='av_icongrid_subtitle icongrid_subtitle{$icongrid_subtitle} {$av_font_classes_1}' {$submarkup} {$subtitle_styling_str}>" . esc_html( $atts['subtitle'] ) . "</{$subtitle_el}>";
			}
				
			$output .=					'</header>';
			$output .=				'</div>';
			$output .=			'</div>';
			$output .=			"<div class='avia-icongrid-content' {$flipbox_back_styling_str}>";
			$output .=				"<div class='avia-icongrid-inner' {$content_styling_str}>";
				
			$markup  = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );

			$output .=					"<div class='avia-icongrid-text {$av_font_classes}' {$markup}>" . ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) ) . '</div>';
			$output .=				'</div>';
			$output .=			'</div>';
			$output .=		'</article>';
			$output .=	"</{$avia_icongrid_wrapper['end']}>";
			$output .= '</li>';

			return $output;
		}

	}
}
