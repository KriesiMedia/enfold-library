<?php
/**
 * HORIZONTAL RULERS
 * 
 * Creates a horizontal ruler that provides whitespace for the layout and helps with content separation
 */
 
// Don't load directly
if ( ! defined('ABSPATH') ) { die('-1'); }



if ( ! class_exists( 'avia_sc_hr' ) ) 
{
	class avia_sc_hr extends aviaShortcodeTemplate
	{
			
		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'yes';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Separator / Whitespace', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-hr.png';
			$this->config['order']			= 94;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']	 	= 'av_hr';
			$this->config['modal_data']		= array( 'modal_class' => 'highscreen' );
			$this->config['tooltip']		= __( 'Creates a delimiter/whitespace to separate elements', 'avia_framework' );
			$this->config['tinyMCE']		= array( 'tiny_always' => true );
			$this->config['preview']		= 1;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
		}

		function extra_assets()
		{
			wp_enqueue_style( 'avia-module-hr', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/hr/hr.css', array( 'avia-layout' ), false );
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
						'name'  => __( 'Styling', 'avia_framework' ),
						'nodescription' => true
					),
				
					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'styling_general' )
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
			 * Styling Tab
			 * ===========
			 */
			
			$c = array(
						array(	
							'name' 	=> __( 'Horizontal Ruler Styling', 'avia_framework' ),
							'desc' 	=> __( 'Here you can set the styling and size of the HR element', 'avia_framework' ),
							'id' 	=> 'class',
							'type' 	=> 'select',
							'std' 	=> 'default',
							'lockable'	=> true,
							'subtype'	=> array(	
												__( 'Predefined Separators', 'avia_framework' )	=> array(
																	__( 'Default', 'avia_framework' )						=> 'default',
																	__( 'Big Top and Bottom Margins', 'avia_framework' )	=> 'big',
																	__( 'Fullwidth Separator', 'avia_framework' )			=> 'full',
																	__( 'Whitespace', 'avia_framework' )					=> 'invisible',
																	__( 'Short Separator', 'avia_framework' )				=> 'short',
															),
												__( 'Custom Separator', 'avia_framework' )		=> array(
																	__( 'Custom', 'avia_framework' )	=> 'custom',
															),
											)
						),
				
						array(	
							'name' 	=> __( 'Icon', 'avia_framework' ),
							'desc' 	=> __( 'Should an icon be displayed at the center?', 'avia_framework' ),
							'id' 	=> 'icon_select',
							'type' 	=> 'select',
							'std' 	=> 'yes',
							'lockable'	=> true,
							'required'	=> array( 'class', 'equals', 'custom' ),
							'subtype'	=> array(
												__( 'No Icon', 'avia_framework' )			=> 'no',
												__( 'Yes, display Icon', 'avia_framework' )	=> 'yes',	
											)
						),	
					
						array(	
							'name' 	=> __( 'Icon','avia_framework' ),
							'desc' 	=> __( 'Select an icon below','avia_framework' ),
							'id' 	=> 'icon',
							'type' 	=> 'iconfont',
							'std' 	=> 'ue808',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' ),
							'required'	=> array( 'icon_select', 'not_empty_and', 'no' )
						),
												
						array(	
							'name' 	=> __( 'Position', 'avia_framework' ),
							'desc' 	=> __( 'Set the position of the short ruler', 'avia_framework' ),
							'id' 	=> 'position',
							'type' 	=> 'select',
							'std' 	=> 'center',
							'lockable'	=> true,
							'required'	=> array( 'class', 'contains','o' ),
							'subtype'	=> array(	
												__( 'Center', 'avia_framework' )	=> 'center',
												__( 'Left', 'avia_framework' )		=> 'left',
												__( 'Right', 'avia_framework' )		=> 'right',
											)
						),
				
						array(	
							'name' 	=> __( 'Section Top Shadow', 'avia_framework' ),
							'desc'  => __( 'Display a small styling shadow at the top of the section', 'avia_framework' ),
							'id' 	=> 'shadow',
							'type' 	=> 'select',
							'std' 	=> 'no-shadow',
							'lockable'	=> true,
							'required'	=> array( 'class', 'equals', 'full' ),
							'subtype'	=> array(   
												__( 'Display shadow', 'avia_framework' )		=> 'shadow',
												__( 'Do not display shadow', 'avia_framework' )	=> 'no-shadow',
											)
						),
				            
						array(	
							'name' 	=> __( 'Height', 'avia_framework' ),
							'desc' 	=> __( "How much whitespace do you need? Enter a pixel value. Positive value will increase the whitespace, negative value will reduce it. eg: '50', '-25', '200'", 'avia_framework' ),
							'id' 	=> 'height',
							'type' 	=> 'input',
							'std'		=> '50',
							'lockable'	=> true,
							'required'	=> array( 'class', 'equals', 'invisible' )
						),

						array(	
							'name' 	=> __( 'Border', 'avia_framework' ),
							'id' 	=> 'custom_border',
							'type' 	=> 'select',
							'std' 	=> 'av-border-thin',
							'lockable'	=> true,
							'required'	=> array( 'class', 'equals', 'custom' ),
							'subtype'	=> array(
												__( 'none', 'avia_framework' )	=> 'av-border-none',
												__( 'thin', 'avia_framework' )	=> 'av-border-thin',	
												__( 'fat', 'avia_framework' )	=> 'av-border-fat',	
											)
						),
							
						array(	
							'name' 	=> __( 'Width', 'avia_framework' ),
							'desc' 	=> __( 'Enter a custom width. Both, px and &percnt; values are allowed. When using an icon width for rulers are limited to 45%.', 'avia_framework' ),
							'id' 	=> 'custom_width',
							'type' 	=> 'input',
							'std'		=> '50px',
							'lockable'	=> true,
							'required'	=> array( 'custom_border', 'not', 'av-border-none' )
						),

						array(	
							'name' 	=> __( 'Top Margin in px', 'avia_framework' ),
							'id' 	=> 'custom_margin_top',
							'type' 	=> 'input',
							'std'	=> '30px',
							'container_class' 	=> 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'class', 'equals', 'custom' )
						),
				            
						array(	
							'name' 	=> __( 'Bottom Margin in px', 'avia_framework' ),
							'id' 	=> 'custom_margin_bottom',
							'type' 	=> 'input',
							'std'	=> '30px',
							'container_class' 	=> 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'class', 'equals', 'custom' )
						),
				
						array(	
							'name' 	=> __( 'Custom Border Color', 'avia_framework' ),
							'desc' 	=> __( 'Leave empty for default theme color', 'avia_framework' ),
							'id' 	=> 'custom_border_color',
							'type' 	=> 'colorpicker',
							'rgba' 	=> true,
							'std' 	=> '',
							'lockable'	=> true,
							'required' => array( 'custom_border', 'not', 'av-border-none' )
						),
					
						array(	
							'name' 	=> __( 'Custom Icon Color', 'avia_framework' ),
							'desc' 	=> __( 'Leave empty for default theme color', 'avia_framework' ),
							'id' 	=> 'custom_icon_color',
							'type' 	=> 'colorpicker',
							'rgba' 	=> true,
							'std' 	=> '',
							'lockable'	=> true,
							'required' => array( 'icon_select', 'not_empty_and', 'no' )
						)
				
				);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_general' ), $c );
			
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
			
			$params['innerHtml'] = "<span class='avia-divider'></span>";
			
			$params['class'] = '';
			$params['content'] = null;

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
						'class'					=> 'default', 
						'height'				=> '50', 
						'shadow'				=> 'no-shadow',
						'position'				=> 'center', 
						'custom_border'			=> 'thin', 
						'custom_width'			=> '30%', 
						'custom_margin_top'		=> '30px', 
						'custom_margin_bottom'	=> '30px', 
						'icon_select'			=> 'no', 
						'custom_border_color'	=> '', 
						'custom_icon_color'		=> '', 
						'icon'					=> '', 
						'font'					=> ''
					);
			
			$default = $this->sync_sc_defaults_array( $default );
			
			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );
			
			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );
			
			//	clean input
			$atts['height'] = trim( $atts['height'], 'px% ' );
			
			$element_styling->create_callback_styles( $atts );
			
			
			$classes = array(
						'hr',
						$element_id,
						'hr-' . $atts['class']
					);
			
			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			
			
			if( 'invisible' == $atts['class'] )
			{
				if( $atts['height'] > 0 )
				{
					$element_styling->add_styles( 'container-invisible', array( 'height' => "{$atts['height']}px" ) );
				}
				else
				{
					$element_styling->add_styles( 'container-invisible', array( 
															'margin-top'	=> "{$atts['height']}px",
															'height'		=> '1px'
														) );
				}
			}
			else if( 'short' == $atts['class'] )
			{
				$element_styling->add_classes( 'container', "hr-{$atts['position']}" );
			}
			else if( 'full' == $atts['class'] )
			{
				$element_styling->add_classes( 'container', "hr-{$atts['shadow']}" );
			}
			else if( 'custom' == $atts['class'] )
			{
				$element_styling->add_classes( 'container', array( "hr-{$atts['position']}", "hr-icon-{$atts['icon_select']}" ) );
				$element_styling->add_classes( 'container-inner', "inner-border-{$atts['custom_border']}" );
				
				$element_styling->add_styles( 'container', array( 
															'margin-top'	=> $atts['custom_margin_top'],
															'margin-bottom'	=> $atts['custom_margin_bottom'],
														) );
				
				$element_styling->add_styles( 'container-inner', array( 
															'width'			=> $atts['custom_width'],
															'border-color'	=> $atts['custom_border_color']
														) );
				
				if( 'no' != $atts['icon_select'] )
				{
					$element_styling->add_styles( 'container-inner', array( 'max-width'	=> '45%' ) );
					$element_styling->add_styles( 'container-icon', array( 'color'	=> $atts['custom_icon_color'] ) );
				}
			}
			
			
			$selectors = array(
						'container'				=> "#top .hr.{$element_id}",
						'container-invisible'	=> "#top .hr.hr-invisible.{$element_id}",
						'container-inner'		=> ".hr.{$element_id} .hr-inner",
						'container-icon'		=> ".hr.{$element_id} .av-seperator-icon"
					);
			
			$element_styling->add_selectors( $selectors );
			
			
			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['meta'] = $meta;
			
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
			
			extract( AviaHelper::av_mobile_sizes( $atts ) ); //return $av_font_classes, $av_title_font_classes and $av_display_classes 
			
			extract( $atts );

			
			$display_char = '';

			if( 'custom' == $class && 'no' != $icon_select )
			{
				$display_char = av_icon( $icon, $font );
				$display_char = "<span class='av-seperator-icon' {$display_char}></span>";
			}
			
			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );
			$inner_class = $element_styling->get_class_string( 'container-inner' );
			
			$outputInner  =	"<span class='hr-inner {$inner_class}'>";
			$outputInner .=		'<span class="hr-inner-style"></span>';
			$outputInner .= '</span>';
			
			$output  = '';
			$output .= $style_tag;
			$output .=	"<div {$meta['custom_el_id']} class='{$container_class} {$av_display_classes}'>";
			$output .=		$outputInner;

			if( $display_char )
			{
				$output .=	$display_char;
				$output .=	$outputInner;
			}

			$output .=	'</div>';

			return $output;
		}
			
	}
}
