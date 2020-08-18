<?php
/**
 * Fullwidth Button
 * 
 * Displays a a colored button that stretches across the full width and links to any url of your choice
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( !class_exists( 'avia_sc_button_full' ) ) 
{
	class avia_sc_button_full extends aviaShortcodeTemplate
	{
		static $button_count = 0;

		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';

			$this->config['name']		= __( 'Fullwidth Button', 'avia_framework' );
			$this->config['tab']		= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-button.png';
			$this->config['order']		= 84;
			$this->config['target']		= 'avia-target-insert';
			$this->config['shortcode'] 	= 'av_button_big';
			$this->config['tooltip'] 	= __( 'Creates a colored button that stretches across the full width', 'avia_framework' );
			$this->config['tinyMCE']    = array( 'tiny_always' => true );
			$this->config['preview'] 	= true;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
		}


		function extra_assets()
		{
			//load css
			wp_enqueue_style( 'avia-module-button', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/buttons/buttons.css', array( 'avia-layout' ), false );
			wp_enqueue_style( 'avia-module-button-fullwidth', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/buttons_fullwidth/buttons_fullwidth.css', array( 'avia-layout' ), false );
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
													$this->popup_key( 'content_button' ),
													$this->popup_key( 'advanced_link' )
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
													$this->popup_key( 'styling_appearance' ),
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
							'name' 	=> __( 'Button Title', 'avia_framework' ),
							'desc' 	=> __( 'This is the text that appears on your button.', 'avia_framework' ),
							'id' 	=> 'label',
							'type' 	=> 'input',
							'std'	=> __( 'Click me', 'avia_framework' )
						),
				
						array(
							'name' 	=> __( 'Additional Description', 'avia_framework' ),
							'desc' 	=> __( 'Enter an additional description', 'avia_framework' ),
							'id' 	=> 'content',
							'type' 	=> 'textarea',
							'std' 	=> ''
						),
				
						array(	
							'name' 	=> __( 'Description position', 'avia_framework' ),
							'desc' 	=> __( 'Show the description above or below the title?', 'avia_framework' ),
							'id' 	=> 'description_pos',
							'type' 	=> 'select',
							'std' 	=> 'below',
							'subtype'	=> array(	
												__( 'Description above title', 'avia_framework' )	=> 'above',
												__( 'Description below title', 'avia_framework' )	=> 'below',
											),
						),
				
						array(	
							'name' 	=> __( 'Button Icon', 'avia_framework' ),
							'desc' 	=> __( 'Should an icon be displayed at the left side of the button', 'avia_framework' ),
							'id' 	=> 'icon_select',
							'type' 	=> 'select',
							'std' 	=> 'yes-left-icon',
							'subtype'	=> array(
												__( 'No Icon', 'avia_framework' )										=> 'no',
												__( 'Yes, display Icon to the left of the title', 'avia_framework' )	=> 'yes-left-icon' ,	
												__( 'Yes, display Icon to the right of the title', 'avia_framework' )	=> 'yes-right-icon',
											)
						),
				
						array(	
							'name' 	=> __( 'Button Icon', 'avia_framework' ),
							'desc' 	=> __( 'Select an icon for your Button below', 'avia_framework' ),
							'id' 	=> 'icon',
							'type' 	=> 'iconfont',
							'std' 	=> '',
							'required'	=> array( 'icon_select', 'not_empty_and', 'no' )
							),
				
						array(	
							'name' 	=> __( 'Icon Visibility', 'avia_framework' ),
							'desc' 	=> __( 'Check to only display icon on hover', 'avia_framework' ),
							'id' 	=> 'icon_hover',
							'type' 	=> 'checkbox',
							'std' 	=> '',
							'required'	=> array( 'icon_select', 'not_empty_and', 'no' )
						)
				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Button', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_button' ), $template );
			
			/**
			 * Styling Tab
			 * ===========
			 */
			
			$c = array(
						array(	
							'name'		=> __( 'Button Title Attribute', 'avia_framework' ),
							'desc'		=> __( 'Add a title attribute for this button.', 'avia_framework' ),
							'id'		=> 'title_attr',
							'type'		=> 'input',
							'std'		=> ''
						),
					
				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Appearance', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_appearance' ), $template );
			
			$c = array(
				
						array(	
							'type'			=> 'template',
							'template_id'	=> 'button_colors',
							'ids'			=> array(
												'bg'		=> array(
																'color'		=> 'color',
																'custom'	=> 'custom',
																'custom_id'	=> 'custom_bg',
															),
												'bg_hover'	=> array(
																'color'		=> 'color_hover',
																'custom'	=> 'custom',
																'custom_id'	=> 'custom_bg_hover',
															),
												'font'		=> array(
																'color'		=> 'color_font',
																'custom'	=> 'custom',
																'custom_id'	=> 'custom_font',
															),
												'font_hover' => array(
																'color'		=> 'color_font_hover',
																'custom'	=> 'custom',
																'custom_id'	=> 'custom_font_hover',
															),
												)
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
			 * Advanced Tab
			 * ===========
			 */
			
			$c = array(
						array(	
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Button Link?', 'avia_framework' ),
							'desc'			=> __( 'Where should your button link to?', 'avia_framework' ),
							'subtypes'		=> array( 'manually', 'single', 'taxonomy' ),
							'target_id'		=> 'link_target'
						),
						
				);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_link' ), $c );
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
			/**
			 * Fix a bug in 4.7 and 4.7.1 renaming option id (no longer backwards comp.) - can be removed in a future version again
			 */
			if( isset( $params['args']['linktarget'] ) )
			{
				$params['args']['link_target'] = $params['args']['linktarget'];
			}
			
			extract( av_backend_icon( $params ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font' 

			$inner  = "<div class='avia_button_box avia_hidden_bg_box avia_textblock avia_textblock_style'>";
			$inner .=		'<div ' . $this->class_by_arguments( 'icon_select, color', $params['args'] ) . '>';
			$inner .=			'<span ' . $this->class_by_arguments( 'font', $font ) . '>';
			$inner .=				"<span data-update_with='icon_fakeArg' class='avia_button_icon avia_button_icon_left'>{$display_char}</span> ";
			$inner .=			'</span> ';
			$inner .=			"<span data-update_with='label' class='avia_iconbox_title' >{$params['args']['label']}</span> ";
			$inner .=			'<span ' . $this->class_by_arguments( 'font', $font ) . '>';
			$inner .=				"<span data-update_with='icon_fakeArg' class='avia_button_icon avia_button_icon_right'>{$display_char}</span>";
			$inner .=			'</span>';
			$inner .=		'</div>';
			$inner .= '</div>';

			$params['innerHtml'] = $inner;
			$params['class'] = '';

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
			avia_sc_button_full::$button_count++;
			
			/**
			 * Fix a bug in 4.7 and 4.7.1 renaming option id (no longer backwards comp.) - can be removed in a future version again
			 */
			if( isset( $atts['linktarget'] ) )
			{
				$atts['link_target'] = $atts['linktarget'];
			}

			extract( AviaHelper::av_mobile_sizes( $atts ) ); //return $av_font_classes, $av_title_font_classes and $av_display_classes 

			$atts = shortcode_atts( array(
							 'label'			=> 'Click me', 
							 'link'				=> '', 
							 'link_target'		=> '',
							 'title_attr'		=> '',
							 'color'			=> 'theme-color',
							 'color_hover'		=> 'theme-color-highlight',
							 'custom_bg'		=> '#444444',
							 'custom_bg_hover'	=> '#444444',
							 'color_font'		=> 'custom',
							 'custom_font'		=> '#ffffff',
							 'position'			=> 'center',
							 'icon_select'		=> 'no',
							 'icon'				=> '', 
							 'font'				=> '',
							 'icon_hover'		=> '',
							 'description_pos'	=> ''
						 ), $atts, $this->config['shortcode'] );


			$display_char = av_icon( $atts['icon'], $atts['font'] );
			$style = '';
			$style_hover = '';
			$extraClass = '';

			if( 'custom' == $atts['color_font'] )
			{
				$style .= AviaHelper::style_string( $atts, 'custom_font', 'color' );
			}
			else
			{
				$extraClass .= 'avia-font-color-' . $atts['color_font'];
			}
			
			if( 'custom' == $atts['color'] ) 
			{
				$style .= AviaHelper::style_string( $atts, 'custom_bg', 'background-color' );
			}

			if( 'custom' == $atts['color_hover'] ) 
			{
				$style_hover = "style='background-color:{$atts['custom_bg_hover']};'";
			}
			
			$style = AviaHelper::style_string( $style );

			$extraClass .= $atts['icon_hover'] ? ' av-icon-on-hover' : '';

			$blank = AviaHelper::get_link_target( $atts['link_target'] );

			$link = AviaHelper::get_url( $atts['link'] );
			$link = $link == 'http://' ? '' : $link;

			$title_attr = ! empty( $atts['title_attr'] ) ? 'title="' . esc_attr( $atts['title_attr'] ) . '"' : '';
			    
			$content_html = '';

			if( $content && $atts['description_pos'] == 'above' ) 
			{
				$content_html .= "<div class='av-button-description av-button-description-above'>" . ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) ) . '</div>';
			}

			if( 'yes-left-icon' == $atts['icon_select'] ) 
			{
				$content_html .= "<span class='avia_button_icon avia_button_icon_left' {$display_char}></span>";
			}
				
			$content_html .= "<span class='avia_iconbox_title' >{$atts['label']}</span>";

			if( 'yes-right-icon' == $atts['icon_select'] ) 
			{
				$content_html .= "<span class='avia_button_icon avia_button_icon_right' {$display_char}></span>";
			}

			if( $content && $atts['description_pos'] == 'below' ) 
			{
				$content_html .= "<div class='av-button-description av-button-description-below'>" . ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) ) . '</div>';
			}

			$output  = '';
			$output .=	"<a href='{$link}' class='avia-button avia-button-fullwidth {$av_display_classes} {$extraClass} " . $this->class_by_arguments( 'icon_select, color', $atts, true ) . "' {$blank} {$style} >";
			$output .=		$content_html;
			$output .=		"<span class='avia_button_background avia-button avia-button-fullwidth avia-color-" . $atts['color_hover'] . "' {$style_hover}></span>";
			$output .=	'</a>';

			$output =  "<div {$meta['custom_el_id']} class='avia-button-wrap avia-button-{$atts['position']} {$meta['el_class']}' {$title_attr}>{$output}</div>";


			$params['class'] = 'main_color av-fullscreen-button avia-no-border-styling ' . $meta['el_class'];
			$params['open_structure'] = false;

			$id = AviaHelper::save_string( $atts['label'], '-' );
			$params['id'] = AviaHelper::save_string( $id, '-', 'av-fullwidth-button-' . avia_sc_button_full::$button_count );
			$params['custom_markup'] = $meta['custom_markup'];

			//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
			if( $meta['index'] == 0 ) 
			{
				$params['close'] = false;
			}
			
			if( ! empty( $meta['siblings']['prev']['tag'] ) && in_array( $meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section ) ) 
			{
				$params['close'] = false;
			}

			if( ! ShortcodeHelper::is_top_level() ) 
			{
				return $output;
			}
			
			global $avia_config;
			if( isset( $avia_config['portfolio_preview_template'] ) && $avia_config['portfolio_preview_template'] > 0 )
			{
				return $output;
			}

			$html  = avia_new_section( $params );
			$html .= $output;
			$html .= avia_section_after_element_content( $meta , 'after_fullwidth_button' );

			return $html;
		}
			
	}
}
