<?php
/**
 * HORIZONTAL RULERS
 * Creates a horizontal ruler that provides whitespace for the layout and helps with content separation.
 * 
 * When using fixed width for the seperator this changes to responsive when the container becomes too small
 * 
 * Based on avia_sc_hr class of enfold 
 */
 
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }



if ( ! class_exists( 'avia_sc_hr' ) ) 
{
	class avia_sc_hr extends aviaShortcodeTemplate{
			
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['name']		= __('Dynamic Separator / Whitespace', 'avia_framework' );
				$this->config['tab']		= __('Content Elements', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-hr.png";
				$this->config['order']		= 94;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_hr';
				$this->config['modal_data'] = array('modal_class' => 'highscreen');
				$this->config['tooltip'] 	= __('Creates a delimiter/whitespace to separate elements', 'avia_framework' );
				$this->config['tinyMCE']    = array('tiny_always'=>true);
				$this->config['preview'] 	= 1;
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
							"type" 	=> "tab_container", 'nodescription' => true
						),
						
					array(
							"type" 	=> "tab",
							"name"  => __("Content" , 'avia_framework'),
							'nodescription' => true
						),
			
			
					array(	
							"name" 	=> __("Horizontal Ruler Styling", 'avia_framework' ),
							"desc" 	=> __("Here you can set the styling and size of the HR element", 'avia_framework' ),
							"id" 	=> "class",
							"type" 	=> "select",
							"std" 	=> "default",
							"subtype" => array(	
												'Predefined Separators'=>array(
												__('Default', 'avia_framework' ) 							=>'default',
												__('Big Top and Bottom Margins', 'avia_framework' )		=>'big',
												__('Fullwidth Separator', 'avia_framework' )		        =>'full',
												__('Whitespace', 'avia_framework' ) 						=>'invisible',
												__('Short Separator', 'avia_framework' ) 					=>'short',
												),
												'Custom Separator'=>array(
												__('Custom', 'avia_framework' ) 							=>'custom',
												),
												
												
												)
												
												)
												,
												
                    array(	"name" 	=> __("Height", 'avia_framework' ),
							"desc" 	=> __("How much whitespace do you need? Enter a pixel value. Positive value will increase the whitespace, negative value will reduce it. eg: '50', '-25', '200'", 'avia_framework' ),
				            "id" 	=> "height",
				            "type" 	=> "input",
				            "required" => array('class','equals','invisible'),
				            "std" => "50"),
				   
				     array(	
						"name" 	=> __("Section Top Shadow",'avia_framework' ),
						"id" 	=> "shadow",
						"desc"  => __("Display a small styling shadow at the top of the section",'avia_framework' ),
						"type" 	=> "select",
						"std" 	=> "no-shadow",
				        "required" => array('class','equals','full'),
						"subtype" => array(   __('Display shadow','avia_framework' )	=>'shadow',
						                      __('Do not display shadow','avia_framework' )	=>'no-shadow',
						                  )),
				            
				   array(	
							"name" 	=> __("Position", 'avia_framework' ),
							"desc" 	=> __("Set the position of the short ruler", 'avia_framework' ),
							"id" 	=> "position",
							"type" 	=> "select",
							"std" 	=> "center",
				            "required" => array('class','contains','o'),
							"subtype" => array(	__('Center', 'avia_framework' ) =>'center',
												__('Left', 'avia_framework' )  =>'left',
												__('Right', 'avia_framework' ) =>'right',
												)),
												
				
				
					array(	
							"name" 	=> __("Border", 'avia_framework' ),
							"id" 	=> "custom_border",
							"type" 	=> "select",
							"std" 	=> "av-border-thin",
				            "required" => array('class','equals','custom'),
							"subtype" => array(
								__('none',  'avia_framework' ) =>'av-border-none',
								__('thin',  'avia_framework' ) =>'av-border-thin' ,	
								__('fat',  'avia_framework' )  =>'av-border-fat' ,	
							)),
							
					array(	"name" 	=> __("Width", 'avia_framework' ),
							"desc" 	=> __("Enter a custom width. Both, px and &percnt; values are allowed. When you choose a px value, the value will be adjusted to a smaller value if necessary to support responsive layout.", 'avia_framework' ),
				            "id" 	=> "custom_width",
				            "type" 	=> "input",
				            "required" => array('custom_border','not','av-border-none'),
				            "std" => "50px"),
				    
				    array(	
							"name" 	=> __("Custom Border Color", 'avia_framework' ),
							"desc" 	=> __("Leave empty for default theme color", 'avia_framework' ),
							"id" 	=> "custom_border_color",
							"type" 	=> "colorpicker",
							"rgba" 	=> true,
				            "required" => array('custom_border','not','av-border-none'),
							"std" 	=> "",
						),
					
							
					array(	"name" 	=> __("Top Margin in px", 'avia_framework' ),
				            "id" 	=> "custom_margin_top",
				            "type" 	=> "input",
				            "container_class" 	=> "av_half av_half_first",
				            "required" => array('class','equals','custom'),
				            "std" => "30px"),
				            
				    array(	"name" 	=> __("Bottom Margin  in px", 'avia_framework' ),
				            "id" 	=> "custom_margin_bottom",
				            "type" 	=> "input",
				            "container_class" 	=> "av_half",
				            "required" => array('class','equals','custom'),
				            "std" => "30px"),
				            
				        				
							
					array(	
							"name" 	=> __("Icon", 'avia_framework' ),
							"desc" 	=> __("Should an icon be displayed at the center?", 'avia_framework' ),
							"id" 	=> "icon_select",
							"type" 	=> "select",
							"std" 	=> "yes",
				            "required" => array('class','equals','custom'),
							"subtype" => array(
								__('No Icon',  'avia_framework' ) =>'no',
								__('Yes, display Icon',  'avia_framework' ) => 'yes' ,	
							)),	
					
					
					array(	
							"name" 	=> __("Custom Icon Color", 'avia_framework' ),
							"desc" 	=> __("Leave empty for default theme color", 'avia_framework' ),
							"id" 	=> "custom_icon_color",
							"type" 	=> "colorpicker",
							"rgba" 	=> true,
							"required" => array('icon_select','not_empty_and','no'),
							"std" 	=> "",
						),
					
					array(	
							"name" 	=> __("Icon",'avia_framework' ),
							"desc" 	=> __("Select an icon below",'avia_framework' ),
							"id" 	=> "icon",
							"type" 	=> "iconfont",
							"std" 	=> "ue808",
							"required" => array('icon_select','not_empty_and','no')
							),
				array(
							"type" 	=> "close_div",
							'nodescription' => true
						),
						
						
								array(
									"type" 	=> "tab",
									"name"	=> __("Screen Options",'avia_framework' ),
									'nodescription' => true
								),
								
								
								array(
								"name" 	=> __("Element Visibility",'avia_framework' ),
								"desc" 	=> __("Set the visibility for this element, based on the device screensize.", 'avia_framework' ),
								"type" 	=> "heading",
								"description_class" => "av-builder-note av-neutral",
								),
							
								array(	
										"desc" 	=> __("Hide on large screens (wider than 990px - eg: Desktop)", 'avia_framework'),
										"id" 	=> "av-desktop-hide",
										"std" 	=> "",
										"container_class" => 'av-multi-checkbox',
										"type" 	=> "checkbox"),
								
								array(	
									
										"desc" 	=> __("Hide on medium sized screens (between 768px and 989px - eg: Tablet Landscape)", 'avia_framework'),
										"id" 	=> "av-medium-hide",
										"std" 	=> "",
										"container_class" => 'av-multi-checkbox',
										"type" 	=> "checkbox"),
										
								array(	
									
										"desc" 	=> __("Hide on small screens (between 480px and 767px - eg: Tablet Portrait)", 'avia_framework'),
										"id" 	=> "av-small-hide",
										"std" 	=> "",
										"container_class" => 'av-multi-checkbox',
										"type" 	=> "checkbox"),
										
								array(	
									
										"desc" 	=> __("Hide on very small screens (smaller than 479px - eg: Smartphone Portrait)", 'avia_framework'),
										"id" 	=> "av-mini-hide",
										"std" 	=> "",
										"container_class" => 'av-multi-checkbox',
										"type" 	=> "checkbox"),
									
								
							  
				
							
								
							array(
									"type" 	=> "close_div",
									'nodescription' => true
								),	
								
								
						
						
					array(
						"type" 	=> "close_div",
						'nodescription' => true
					),
				      
				);

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
			function editor_element($params)
			{
				$params['content'] 		= NULL;
				$params['innerHtml']  	= "<span class='avia-divider'></span>";
				$params['class'] = "";

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
			function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = "")
			{	
				extract(AviaHelper::av_mobile_sizes($atts)); //return $av_font_classes, $av_title_font_classes and $av_display_classes 
				
			    extract(shortcode_atts(array(
			    
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
						'font'					=> '', 
			    
			    ), $atts, $this->config['shortcode']));
			
        		$output  = "";
        		$style	 = "";
        		$height  = trim($height, 'px% ');
        		$inner_style = "";
        		$inner_class = "";
        		$display_char= "";
        		$outputInner = "";
        		
        		if($class == 'invisible')
        		{
        			$style	 = $height > 0 ? "style='height:{$height}px'" : "style='height:1px; margin-top:{$height}px' ";
        		}
        		
        		$class  .= $class == 'short' ? " hr-{$position}" : "";
        		$class  .= $class == 'full'  ? " hr-{$shadow}" : "";

				$av_custom_width = '';
				
				if($class == 'custom')
        		{
        			
        			$class .= " hr-{$position}";
        			$class .= " hr-icon-{$icon_select}";
        			$inner_class .= "  inner-border-{$custom_border}";
        			
        			$style .= " margin-top:{$custom_margin_top};";
        			$style .= " margin-bottom:{$custom_margin_bottom};";
        			
						//	only fixed width have to be adjusted by js if container gets too small
					if( false !== stripos( $custom_width, 'px' ) )
					{
						$av_custom_width = $custom_width;
					}

        			$inner_style .= " width:{$custom_width};";
        			$inner_style .= $custom_border_color ? " border-color:{$custom_border_color};" : "";
        			
        			
        			$inner_style = "style='{$inner_style}' ";
        			$style = "style='{$style}' ";
        			
        			if("no" != $icon_select)
        			{
        				$icon_color   = $custom_icon_color ? "style='color:{$custom_icon_color};'" : "";
        				$display_char = av_icon($icon, $font);
        				$display_char = "<span class='av-seperator-icon' {$icon_color} {$display_char}></span>";
        			}
        		}
				
				
        		$output 		.= "<div {$style} class='hr avia-hr hr-{$class} {$av_display_classes} ".$meta['el_class']."' data-av_custom_width='{$av_custom_width}'>";
        		$outputInner  	.= "<span class='hr-inner {$inner_class}' {$inner_style}><span class='hr-inner-style'></span></span>";
        		$output 		.= $outputInner;
        		if($display_char)
        		{
	        		$output .= $display_char . $outputInner;
        		}
        		
        		$output .= "</div>";
        		
        		
        		return $output;
        	}
			
			
	}
}
