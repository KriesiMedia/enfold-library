<?php
/**
 * COLUMNS
 * 
 * Shortcode which creates columns for better content separation
 */

 // Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }



if ( !class_exists( 'avia_sc_columns' ) )
{
	class avia_sc_columns extends aviaShortcodeTemplate
	{

			static $extraClass 		= "";
			static $calculated_size = 0;
			static $first_atts  = array(); 
			static $size_array = array(	'av_one_full' 		=> 1.0, 
										'av_one_half' 		=> 0.5, 
										'av_one_third' 		=> 0.33, 
										'av_one_fourth' 	=> 0.25, 
										'av_one_fifth' 		=> 0.2, 
										'av_two_third' 		=> 0.66, 
										'av_three_fourth' 	=> 0.75, 
										'av_two_fifth' 		=> 0.4, 
										'av_three_fifth' 	=> 0.6, 
										'av_four_fifth' 	=> 0.8
									);

			
			/**
			 * This constructor is implicity called by all derived classes
			 * To avoid duplicating code we put this in the constructor
			 * 
			 * @since 4.2.1
			 * @param AviaBuilder $builder
			 */
			public function __construct( $builder ) 
			{
				parent::__construct( $builder );
				
				$this->config['type']				=	'layout';
				$this->config['self_closing']		=	'no';
				$this->config['contains_content']	=	'yes';
				$this->config['contains_text']		=	'no';
				$this->config['first_in_row']		=	'first';
			}
			
			/**
			 * Returns the width of the column. As this is the base class for all columns we only need to implement it here.
			 * 
			 * @since 4.2.1
			 * @return float
			 */
			public function get_element_width()
			{
				return isset( avia_sc_columns::$size_array[ $this->config['shortcode'] ] ) ? avia_sc_columns::$size_array[ $this->config['shortcode'] ] : 1.0;
			}


			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['name']		= '1/1';
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-full.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 100;
				$this->config['target']		= "avia-section-drop";
				$this->config['shortcode'] 	= 'av_one_full';
				$this->config['html_renderer'] 	= false;
				$this->config['tinyMCE'] 	= array('instantInsert' => "[av_one_full first]Add Content here[/av_one_full]");
				$this->config['tooltip'] 	= __('Creates a single full width column', 'avia_framework' );
				$this->config['drag-level'] = 2;
				$this->config['drop-level'] = 2;

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
				extract($params);
				
				$name 		= $this->config['shortcode'];
				$drag 		= $this->config['drag-level'];
				$drop 		= $this->config['drop-level'];
				
				$size = array('av_one_full' => '1/1', 'av_one_half' => '1/2', 'av_one_third' => '1/3', 'av_one_fourth' => '1/4', 'av_one_fifth' => '1/5', 'av_two_third' => '2/3', 'av_three_fourth' => '3/4', 'av_two_fifth' => '2/5', 'av_three_fifth' => '3/5', 'av_four_fifth' => '4/5');
				
				
				$data['shortcodehandler'] 	= $this->config['shortcode'];
				$data['modal_title'] 		= __('Edit Column','avia_framework' );
				$data['modal_ajax_hook'] 	= $this->config['shortcode'];
				$data['dragdrop-level']		= $this->config['drag-level'];
				$data['allowed-shortcodes'] = $this->config['shortcode'];
				
				if(!empty($this->config['modal_on_load']))
				{
					$data['modal_on_load'] 	= $this->config['modal_on_load'];
				}
	
				$dataString  = AviaHelper::create_data_string($data);
				
				$el_bg = !empty($args['background_color']) ? " style='background:".$args['background_color'].";'" : "";
				
				$extraClass = isset($args[0]) ? $args[0] == 'first' ? ' avia-first-col' : "" : "";

				$output  = "<div class='avia_layout_column avia_layout_column_no_cell avia_pop_class avia-no-visual-updates ".$name.$extraClass." av_drag' {$dataString} data-width='{$name}'>";
				$output .= "<div class='avia_sorthandle menu-item-handle'>";

				$output .= "<a class='avia-smaller avia-change-col-size' href='#smaller' title='".__('Decrease Column Size','avia_framework' )."'>-</a>";
				$output .= "<span class='avia-col-size'>".$size[$name]."</span>";
				$output .= "<a class='avia-bigger avia-change-col-size'  href='#bigger' title='".__('Increase Column Size','avia_framework' )."'>+</a>";
				$output .= "<a class='avia-delete'  href='#delete' title='".__('Delete Column','avia_framework' )."'>x</a>";
				$output .= "<a class='avia-save-element'  href='#save-element' title='".__('Save Element as Template','avia_framework' )."'>+</a>";
			    //$output .= "<a class='avia-new-target'  href='#new-target' title='".__('Move Element','avia_framework' )."'>+</a>";
				$output .= "<a class='avia-clone'  href='#clone' title='".__('Clone Column','avia_framework' )."' >".__('Clone Column','avia_framework' )."</a><span class='avia-element-bg-color' ".$el_bg."></span>";
				
				if(!empty($this->config['popup_editor']))
    			{
    				$output .= "    <a class='avia-edit-element'  href='#edit-element' title='".__('Edit Cell','avia_framework' )."'>edit</a>";
    			}
    			
    			$output .= "</div>";
				$output .= "<div class='avia_inner_shortcode avia_connect_sort av_drop ' data-dragdrop-level='{$drop}'>";
				$output .= "<textarea data-name='text-shortcode' cols='20' rows='4'>".ShortcodeHelper::create_shortcode_by_array($name, $content, $args)."</textarea>";
				if($content)
				{
					$content = $this->builder->do_shortcode_backend($content);
				}
				$output .= $content;
				$output .= "</div>";
				$output .= "<div class='avia-layout-element-bg' ".$this->get_bg_string($args)."></div>";
				$output .= "</div>";

				return $output;
			}
			
			function get_bg_string($args)
			{
				$style = "";
			
				if(!empty($args['attachment']))
				{
					$image = false;
					$src = wp_get_attachment_image_src($args['attachment'], $args['attachment_size']);
					if(!empty($src[0])) $image = $src[0];
					
					
					if($image)
					{
						$bg 	= !empty($args['background_color']) ? 		$args['background_color'] : "transparent"; $bg = "transparent";
						$pos 	= !empty($args['background_position'])  ? 	$args['background_position'] : "center center";
						$repeat = !empty($args['background_repeat']) ?		$args['background_repeat'] : "no-repeat";
						$extra	= "";
						
						if($repeat == "stretch")
						{
							$repeat = "no-repeat";
							$extra = "background-size: cover;";
						}
						
						if($repeat == "contain")
						{
							$repeat = "no-repeat";
							$extra = "background-size: contain;";
						}
						
						
						
						$style = "style='background: $bg url($image) $repeat $pos; $extra'";
					}
					
				}
				
				return $style;
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
					
					array( /*stores the "first" variable that removes margin from the first column*/
							"id"    => 0,
							"std"   => '',
							"type"  => "hidden"
						),
					
					array(
							"type" 	=> "tab_container", 'nodescription' => true
						),
						
					array(
							"type" 	=> "tab",
							"name"  => __("Row Settings" , 'avia_framework'),
							'nodescription' => true,
							
						),
					
					array(
						"name" 	=> __("Row Settings",'avia_framework' ),
						"desc" 	=> __("Row Settings apply to all columns in this row but can only be set in the first column", 'avia_framework' ),
						"type" 	=> "heading",
						"description_class" => "av-builder-note av-notice",
						"required" => array('0','equals',''),
						),
					
					array(
						"name" 	=> __("Row Settings",'avia_framework' ),
						"desc" 	=> __("These setting apply to all columns in this row and can only be set in the first column.", 'avia_framework' )
								 ."<br/><strong>"
								 . __("Please note:", 'avia_framework' )
								 ."</strong> "
								 . __("If you move another column into first position you will need to re-apply these settings.", 'avia_framework' ),
						"type" 	=> "heading",
						"description_class" => "av-builder-note av-notice",
						"required" => array('0','not',''),
						),
					
					array(
						"name" 	=> __("Equal Height Columns",'avia_framework' ),
						"desc" 	=> __("Columns in this row can either have a height based on their content or all be of equal height based on the largest column ", 'avia_framework' ),
						"id" 	=> "min_height",
						"type" 	=> "select",
						"std" 	=> "",
						"required" => array('0','not',''),
						"subtype" => array(
							__('Individual height','avia_framework' )=>'',
							__('Equal height','avia_framework' ) =>'av-equal-height-column',
							)
						),
					
					array(
						"name" 	=> __("Vertical Alignment",'avia_framework' ),
						"desc" 	=> __("If a column is larger than its content, were do you want to align the content vertically?", 'avia_framework' ),
						"id" 	=> "vertical_alignment",
						"type" 	=> "select",
						"std" 	=> "",
						"required" => array('min_height','not',''),
						"subtype" => array(
							__('Top','avia_framework' )=>'av-align-top',
							__('Middle','avia_framework' ) =>'av-align-middle',
							__('Bottom','avia_framework' ) =>'av-align-bottom',
							)
						),
					
					array(
						"name" 	=> __("Space between columns",'avia_framework' ),
						"desc" 	=> __("You can remove the default space between columns here.", 'avia_framework' ),
						"id" 	=> "space",
						"type" 	=> "select",
						"std" 	=> "",
						"required" => array('0','not',''),
						"subtype" => array(
							__('Space between columns','avia_framework' )=>'',
							__('No space between columns','avia_framework' ) =>'no_margin',
							)
						),
					
					array(
						"name" 	=> __("Custom top and bottom margin",'avia_framework' ),
						"desc" 	=> __("If checked allows you to set a custom top and bottom margin. Otherwise the margin is calculated by the theme based on surrounding elements",'avia_framework' ),
						"required" => array('0','not',''),
						"id" 	=> "custom_margin",
						"type" 	=> "checkbox",
						"std" 	=> "",
						),
					
					array(	
						"name" 	=> __("Custom top and bottom margin", 'avia_framework' ),
						"desc" 	=> __("Set a custom top or bottom margin. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;", 'avia_framework' ),
						"id" 	=> "margin",
						"type" 	=> "multi_input",
						"required" => array('custom_margin','not',''),
						"std" 	=> "0px",
						"sync" 	=> true,
						"multi" => array(	'top' 	=> __('Margin-Top','avia_framework'), 
											'bottom'=> __('Margin-Bottom','avia_framework'),
											)
						),
						

					array(
							"name"			=> __( "Column Link", 'avia_framework' ),
							"desc"			=> __( "Select where this column should link to", 'avia_framework' ),
							"id"			=> "link",
							"type"			=> "linkpicker",
							"fetchTMPL"		=> true,
							"std"			=> "",
							"subtype"		=> array(
												__( 'No Link', 'avia_framework' )					=> '',
												__( 'Set Manually', 'avia_framework' )				=> 'manually',
												__( 'Single Entry', 'avia_framework' )				=> 'single',
												__( 'Taxonomy Overview Page',  'avia_framework' )	=> 'taxonomy',
											),
							"std" 	=> ""
						),
					
					array(
							"name"		=> __( "Open in new window", 'avia_framework' ),
							"desc"		=> __( "Do you want to open the link in a new window", 'avia_framework' ),
							"id"		=> "linktarget",
							"required" 	=> array( 'link', 'not', '' ),
							"type"		=> "select",
							"std"		=> "",
							"subtype"	=> AviaHtmlHelper::linking_options()
						),   

					
					array(
							"type" 	=> "close_div",
							'nodescription' => true
						),
					array(
							"type" 	=> "tab",
							"name"  => __("Layout" , 'avia_framework'),
							'nodescription' => true
						),
					
					
					array(	
							"name" 	=> __("Inner Padding", 'avia_framework' ),
							"desc" 	=> __("Set the distance from the column content to the border here. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;", 'avia_framework' ),
							"id" 	=> "padding",
							"type" 	=> "multi_input",
							"std" 	=> "0px",
							"sync" 	=> true,
							"multi" => array(	'top' 	=> __('Padding-Top','avia_framework'), 
												'right'	=> __('Padding-Right','avia_framework'), 
												'bottom'=> __('Padding-Bottom','avia_framework'),
												'left'	=> __('Padding-Left','avia_framework'), 
												)
						),
					

					
					array(
							"type" 	=> "close_div",
							'nodescription' => true
						),
					
					array(
							"type" 	=> "tab",
							"name"	=> __("Border" , 'avia_framework' ),
							'nodescription' => true
						),
					
					
					
					array(
						"name" 	=> __("Border",'avia_framework' ),
						"desc" 	=> __("Set the border of the column here", 'avia_framework' ),
						"id" 	=> "border",
						"type" 	=> "select",
						"std" 	=> "",
						"subtype" => AviaHtmlHelper::number_array(1,40,1, array( __("None", 'avia_framework' )=>'') , 'px'),
						),
						
					array(	
							"name" 	=> __("Border Color", 'avia_framework' ),
							"desc" 	=> __("Set a border color for this column", 'avia_framework' ),
							"id" 	=> "border_color",
							"type" 	=> "colorpicker",
							"rgba" 	=> true,
							"required" => array('border','not',''),
							"std" 	=> "",
						),
						
					array(	
							"name" 	=> __("Border Radius", 'avia_framework' ),
							"desc" 	=> __("Set the border radius of the column", 'avia_framework' ),
							"id" 	=> "radius",
							"type" 	=> "multi_input",
							"std" 	=> "0px",
							"sync" 	=> true,
							"multi" => array(	'top' 	=> __('Top-Left-Radius','avia_framework'), 
												'right'	=> __('Top-Right-Radius','avia_framework'), 
												'bottom'=> __('Bottom-Right-Radius','avia_framework'),
												'left'	=> __('Bottom-Left-Radius','avia_framework'),
												)
						),
					
					
					
					array(
							"type" 	=> "close_div",
							'nodescription' => true
						),
					
					array(
							"type" 	=> "tab",
							"name"	=> __("Colors" , 'avia_framework' ),
							'nodescription' => true
						),
					
					
					array(	
							"name" 	=> __("Custom Background Color", 'avia_framework' ),
							"desc" 	=> __("Select a custom background color for this cell here. Leave empty for default color", 'avia_framework' ),
							"id" 	=> "background_color",
							"type" 	=> "colorpicker",
							"rgba" 	=> true,
							"std" 	=> "",
						),
						
					array(
							"name" 	=> __("Custom Background Image",'avia_framework' ),
							"desc" 	=> __("Either upload a new, or choose an existing image from your media library. Leave empty if you don't want to use a background image ",'avia_framework' ),
							"id" 	=> "src",
							"type" 	=> "image",
							"title" => __("Insert Image",'avia_framework' ),
							"button" => __("Insert",'avia_framework' ),
							"std" 	=> ""),
					
					/*
array(
						"name" 	=> __("Background Attachment",'avia_framework' ),
						"desc" 	=> __("Background can either scroll with the page or be fixed", 'avia_framework' ),
						"id" 	=> "background_attachment",
						"type" 	=> "select",
						"std" 	=> "scroll",
                        "required" => array('src','not',''),
						"subtype" => array(
							__('Scroll','avia_framework' )=>'scroll',
							__('Fixed','avia_framework' ) =>'fixed',
							)
						),
*/
					
                    array(
						"name" 	=> __("Background Image Position",'avia_framework' ),
						"id" 	=> "background_position",
						"type" 	=> "select",
						"std" 	=> "top left",
                        "required" => array('src','not',''),
						"subtype" => array(   __('Top Left','avia_framework' )       =>'top left',
						                      __('Top Center','avia_framework' )     =>'top center',
						                      __('Top Right','avia_framework' )      =>'top right',
						                      __('Bottom Left','avia_framework' )    =>'bottom left',
						                      __('Bottom Center','avia_framework' )  =>'bottom center',
						                      __('Bottom Right','avia_framework' )   =>'bottom right',
						                      __('Center Left','avia_framework' )    =>'center left',
						                      __('Center Center','avia_framework' )  =>'center center',
						                      __('Center Right','avia_framework' )   =>'center right'
						                      )
				    ),

	               array(
						"name" 	=> __("Background Repeat",'avia_framework' ),
						"id" 	=> "background_repeat",
						"type" 	=> "select",
						"std" 	=> "no-repeat",
                        "required" => array('src','not',''),
						"subtype" => array(   __('No Repeat','avia_framework' )          =>'no-repeat',
						                      __('Repeat','avia_framework' )             =>'repeat',
						                      __('Tile Horizontally','avia_framework' )  =>'repeat-x',
						                      __('Tile Vertically','avia_framework' )    =>'repeat-y',
						                      __('Stretch to fit (stretches image to cover the element)','avia_framework' )     =>'stretch',
						                      __('Scale to fit (scales image so the whole image is always visible)','avia_framework' )     =>'contain'
						                      )
				  ),
				  
				  
				array(
							"type" 	=> "close_div",
							'nodescription' => true
				),
					
				
				array(
							"type" 	=> "tab",
							"name"	=> __("Animation" , 'avia_framework' ),
							'nodescription' => true
						),
						
				array(
						"name" 	=> __("Animation",'avia_framework' ),
							"desc" 	=> __("Set an animation for this element. The animation will be shown once the element appears first on screen. Animations only work in modern browsers and only on desktop computers to keep page rendering as fast as possible.",'avia_framework' ),
						"id" 	=> "animation",
						"type" 	=> "select",
						"std" 	=> "",
						"subtype" => array(
							__('None',  'avia_framework' ) =>'',
							
							__('Fade Animations',  'avia_framework') => array(
								__('Fade in',  'avia_framework' ) =>'fade-in',
								__('Pop up',  'avia_framework' ) =>'pop-up',
							),
							__('Slide Animations',  'avia_framework') => array(
								__('Top to Bottom',  'avia_framework' ) =>'top-to-bottom',
								__('Bottom to Top',  'avia_framework' ) =>'bottom-to-top',
								__('Left to Right',  'avia_framework' ) =>'left-to-right',
								__('Right to Left',  'avia_framework' ) =>'right-to-left',
								),
							__('Rotate',  'avia_framework') => array(
								__('Full rotation',  'avia_framework' ) =>'av-rotateIn',
								__('Bottom left rotation',  'avia_framework' ) =>'av-rotateInUpLeft',
								__('Bottom right rotation',  'avia_framework' ) =>'av-rotateInUpRight',
								)	
								
								
								
							)
				  ),		
				
				
				
				array(
							"type" 	=> "close_div",
							'nodescription' => true
				),
				
				
				
				
				array(
						"type" 	=> "tab",
						"name"  => __("Screen Options" , 'avia_framework'),
						'nodescription' => true
					),
					
				array(
							"name" 	=> __("ROW SETTING: Mobile Breaking Point",'avia_framework' ),
							"desc" 	=> __("Set the screen width when columns in this row should switch to full width", 'avia_framework' ),
							"type" 	=> "heading",
							"description_class" => "av-builder-note av-notice",
							"required" => array('0','not',''),
					),
					
					
				array(	
						"name" 	=> __("Fullwidth Break Point", 'avia_framework' ),
						"desc" 	=> __("The columns in this row will switch to fullwidth at this screen width ", 'avia_framework' ),
						"id" 	=> "mobile_breaking",
						"type" 	=> "select",
						"std" 	=> "",
						"required" => array('0','not',''),
						"subtype" => array(	
								__('On mobile devices (at a screen width of 767px or lower)','avia_framework' ) =>'',
								__('On tablets (at a screen width of 989px or lower)',  'avia_framework' ) =>'av-break-at-tablet',
									)
					),	
					
				array(
								"name" 	=> __("Element Visibility",'avia_framework' ),
								"desc" 	=> 
								__("Set the visibility for this element, based on the device screensize.", 'avia_framework' )."<br><small>".
								__("In order to prevent breaking the layout it is only possible to change the visibility settings for columns once they take up the full screen width, which means only on mobile devices", 'avia_framework' )."</small>",
								
								"type" 	=> "heading",
								"description_class" => "av-builder-note av-neutral",
					),
								
				array(	
						"name" 	=> __("Mobile display", 'avia_framework' ),
						"desc" 	=> __("Display settings for this element when viewed on smaller screens", 'avia_framework' ),
						"id" 	=> "mobile_display",
						"type" 	=> "select",
						"std" 	=> "",
						"subtype" => array(	
								__('Always display','avia_framework' ) =>'',
								__('Hide on mobile devices',  'avia_framework' ) =>'av-hide-on-mobile',
									)
					),
				
				
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
			 * Frontend Shortcode Handler
			 *
			 * @param array $atts array of attributes
			 * @param string $content text within enclosing form of shortcode element
			 * @param string $shortcodename the shortcode found, when == callback name
			 * @return string $output returns the modified html string
			 */
			function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = "")
			{
				global $avia_config;

				$avia_config['current_column'] = $shortcodename;


				$first = '';
				if (isset($atts[0]) && trim($atts[0]) == 'first')  $first = 'first';
				
				$atts = shortcode_atts( array(
								'padding'				=> '',
								'background_color'		=> '',
								'background_position' 	=> '',
								'background_repeat' 	=> '',
								'background_attachment' => '',
								'fetch_image'			=> '',
								'attachment_size'		=> '',
								'attachment'			=> 'scroll',
								'radius'				=> '',
								'space'					=> '',
								'border'				=> '',
								'border_color'			=> '',
								'border_style'			=> 'solid',
								'margin'				=> '',
								'custom_margin'			=> '',
								'min_height'			=> '',
								'vertical_alignment'	=> 'av-align-top',
								'animation'				=> '',
								'link'					=> '',
								'linktarget'			=> '',
								'mobile_display'		=> '',
								'mobile_breaking'		=> ''
					
							), $atts, $this->config['shortcode'] );
				
				
				if($first)
				{
					avia_sc_columns::$first_atts = $atts;
				}
				
				
				$extraClass	 = "";
				$outer_style = "";
				$inner_style = "";
				$margin_style= "";
				$output		 = "";
				$extra_table_class = "";
				$anim_class  = empty($atts['animation']) ? "" : " av-animated-generic ".$atts['animation']." ";
				$extraClass .= $anim_class;
				$extraClass .= empty($atts['mobile_display']) ? "" : " ".$atts['mobile_display']." ";
				
			
				if(!empty($atts['attachment']))
				{
					$src = wp_get_attachment_image_src($atts['attachment'], $atts['attachment_size']);
					if(!empty($src[0])) $atts['fetch_image'] = $src[0];
				}
				
				if($atts['background_repeat'] == "stretch")
				{
					$extraClass .= " avia-full-stretch";
					$atts['background_repeat'] = "no-repeat";
				}
				
				if($atts['background_repeat'] == "contain")
				{
					$extraClass .= " avia-full-contain";
					$atts['background_repeat'] = "no-repeat";
				}
				
				if( !empty( avia_sc_columns::$first_atts['space'] ) )
				{
					$extraClass .= " ".avia_sc_columns::$first_atts['space'];
				}
				
				if( !empty( avia_sc_columns::$first_atts['mobile_breaking'] ) )
				{
					$extraClass .= " ".avia_sc_columns::$first_atts['mobile_breaking'];
					$extra_table_class = " av-break-at-tablet-table";
				}
				
				
				if( !empty( avia_sc_columns::$first_atts['min_height'] ) )
				{
					$extraClass .= " flex_column_table_cell";
					$extraClass .= " ".avia_sc_columns::$first_atts['min_height']." ".avia_sc_columns::$first_atts['vertical_alignment'];
				}
				else
				{
					$extraClass .= " flex_column_div";
				}
				
				if( !empty( avia_sc_columns::$first_atts['custom_margin'] ) )
				{
					$explode_margin = explode(',',avia_sc_columns::$first_atts['margin']);
					if(count($explode_margin) <= 1)
					{
						$explode_margin[1] = $explode_margin[0];
					}
					
					$atts['margin-top'] = $explode_margin[0];
					$atts['margin-bottom'] = $explode_margin[1];
					
					$margins = "";
					$margins .= AviaHelper::style_string($atts, 'margin-top');
					$margins .= AviaHelper::style_string($atts, 'margin-bottom');
					
					if( !empty( avia_sc_columns::$first_atts['min_height'] ) )
					{
						$margin_style = AviaHelper::style_string( $margins );
					}
					else
					{
						$outer_style .= $margins;
					}
				}
				
				
				
				$explode_padding = explode(',',$atts['padding']);
				if(count($explode_padding) > 1)
				{
					$atts['padding'] = "";
					foreach($explode_padding as $value)
					{
						if(empty($value)) $value = "0";
						$atts['padding'] .= $value ." ";
					}
				}
				
				if($atts['padding'] == "0px" || $atts['padding'] == "0" || $atts['padding'] == "0%")
				{
					$extraClass .= " av-zero-column-padding";
					$atts['padding'] = "";
				}
				
				
				$explode_radius = explode(',',$atts['radius']);
				if(count($explode_radius) > 1)
				{
					$atts['radius'] = "";
					foreach($explode_radius as $value)
					{
						if(empty($value)) $value = "0";
						$atts['radius'] .= $value ." ";
					}
				}
				
				if($atts['padding'] == "0px" || $atts['padding'] == "0" || $atts['padding'] == "0%")
				{
					$extraClass .= " av-zero-column-padding";
					$atts['padding'] = "";
				}
				
				
				
				
				
				if(!empty($atts['fetch_image']))
				{
					$outer_style .= AviaHelper::style_string($atts, 'fetch_image', 'background-image');
					$outer_style .= AviaHelper::style_string($atts, 'background_position', 'background-position');
					$outer_style .= AviaHelper::style_string($atts, 'background_repeat', 'background-repeat');
					$outer_style .= AviaHelper::style_string($atts, 'background_attachment', 'background-attachment');
				}
				
				if(!empty($atts['border']))
				{
					$outer_style .= AviaHelper::style_string($atts, 'border', 'border-width', 'px');
					$outer_style .= AviaHelper::style_string($atts, 'border_color', 'border-color');
					$outer_style .= AviaHelper::style_string($atts, 'border_style', 'border-style');
				}
				
				$outer_style .= AviaHelper::style_string($atts, 'padding');
				$outer_style .= AviaHelper::style_string($atts, 'background_color', 'background-color');
				$outer_style .= AviaHelper::style_string($atts, 'radius', 'border-radius');
				$outer_style  = AviaHelper::style_string($outer_style);
				
				
				
				
				if( $first )
				{	
					avia_sc_columns::$calculated_size = 0;
					
					if(!empty($meta['siblings']['prev']['tag']) &&
					in_array($meta['siblings']['prev']['tag'], array('av_one_full','av_one_half', 'av_one_third', 'av_two_third', 'av_three_fourth' , 'av_one_fourth' , 'av_one_fifth' ,'av_textblock')))
					{
						avia_sc_columns::$extraClass = "column-top-margin";
					}
					else
					{
						avia_sc_columns::$extraClass = "";
					}
				}
				
				if(!empty( avia_sc_columns::$first_atts['min_height'] ) && avia_sc_columns::$calculated_size == 0)
				{
					$output .= "<div class='flex_column_table ".avia_sc_columns::$first_atts['min_height']."-flextable ".avia_sc_columns::$first_atts['mobile_breaking']."-flextable' {$margin_style}>";
				}	
				
				if(!$first && empty( avia_sc_columns::$first_atts['space'] ) && !empty( avia_sc_columns::$first_atts['min_height'] ))
				{
					$output .= "<div class='av-flex-placeholder'></div>";
				}
				
				avia_sc_columns::$calculated_size += avia_sc_columns::$size_array[ $this->config['shortcode'] ];
				
				$link = aviaHelper::get_url( $atts['link'] );
				$linktarget = '';
				
				if( ! empty( $link ) )
				{
					$linktarget .=  ( strpos( $atts['linktarget'], '_blank' ) !== false ) ? ' target="_blank" ' : '';
					$linktarget .= ( strpos( $atts['linktarget'], 'nofollow' ) !== false ) ? ' rel="nofollow" ' : '';
				}
				
				
				$tag = ! empty( $link ) ? array( 'a href="' . $link . '" ' . $linktarget, 'a') : array( 'div', 'div' );
				
				$output  .= '<' . $tag[0] . ' class="flex_column ' . $shortcodename . ' ' . $extraClass . ' ' . $first . ' ' . $meta['el_class'] . ' ' . avia_sc_columns::$extraClass . '" ' . $outer_style . '>';
				
				//if the user uses the column shortcode without the layout builder make sure that paragraphs are applied to the text
				$content =  ( empty( $avia_config['conditionals']['is_builder_template'] ) ) ? ShortcodeHelper::avia_apply_autop(ShortcodeHelper::avia_remove_autop($content)) : ShortcodeHelper::avia_remove_autop($content, true);

				$output .= trim( $content ) . '</' . $tag[1] . '>';			

				
				$force_close = false;
				
				if( isset($meta['siblings']) && isset($meta['siblings']['next']) && isset( $meta['siblings']['next']['tag'] ) )
				{
					if(!array_key_exists($meta['siblings']['next']['tag'], avia_sc_columns::$size_array))
					{
						$force_close = true;
					}
				}
				
				/**
				 * check if row will break into next column 
				 */
				if( ( false === $force_close ) && ! empty( avia_sc_columns::$first_atts['min_height'] ) && ( 'av-equal-height-column' ==  avia_sc_columns::$first_atts['min_height'] ) )
				{
					if( ! isset( $meta['siblings']['next']['tag'] ) )
					{
						$force_close = true;
					}
					else if( ( avia_sc_columns::$calculated_size + avia_sc_columns::$size_array[ $meta['siblings']['next']['tag'] ] ) > 1.0 )
					{
						$force_close = true;
					}
				}
				

				if( !empty( avia_sc_columns::$first_atts['min_height']) && (avia_sc_columns::$calculated_size >= 0.95 || $force_close) )
				{
					$output .= "</div><!--close column table wrapper. Autoclose: {$force_close} -->";
					avia_sc_columns::$calculated_size = 0;
				}

				unset($avia_config['current_column']);

				return $output;
			}
	}
}









if ( !class_exists( 'avia_sc_columns_one_half' ) )
{
	class avia_sc_columns_one_half extends avia_sc_columns{

			function shortcode_insert_button()
			{
				$this->config['name']		= '1/2';
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-half.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 90;
				$this->config['target']		= "avia-section-drop";
				$this->config['shortcode'] 	= 'av_one_half';
				$this->config['html_renderer'] 	= false;
				$this->config['tinyMCE'] 	= array('name' => '1/2 + 1/2', 'instantInsert' => "[av_one_half first]Add Content here[/av_one_half]\n\n\n[av_one_half]Add Content here[/av_one_half]");
				$this->config['tooltip'] 	= __('Creates a single column with 50&percnt; width', 'avia_framework' );
				$this->config['drag-level'] = 2;
				$this->config['drop-level'] = 2;
		}
	}
}


if ( !class_exists( 'avia_sc_columns_one_third' ) )
{
	class avia_sc_columns_one_third extends avia_sc_columns{

			function shortcode_insert_button()
			{
				$this->config['name']		= '1/3';
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-third.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 80;
				$this->config['target']		= "avia-section-drop";
				$this->config['shortcode'] 	= 'av_one_third';
				$this->config['html_renderer'] 	= false;
				$this->config['tooltip'] 	= __('Creates a single column with 33&percnt; width', 'avia_framework' );
				$this->config['drag-level'] = 2;
				$this->config['drop-level'] = 2;
				$this->config['tinyMCE'] 	= array(
		      		'name' => '1/3 + 1/3 + 1/3',
				    'instantInsert' => "[av_one_third first]Add Content here[/av_one_third]\n\n\n[av_one_third]Add Content here[/av_one_third]\n\n\n[av_one_third]Add Content here[/av_one_third]"
				                                    );
			}
	}
}

if ( !class_exists( 'avia_sc_columns_two_third' ) )
{
	class avia_sc_columns_two_third extends avia_sc_columns{

			function shortcode_insert_button()
			{
				$this->config['name']		= '2/3';
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-two_third.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 70;
				$this->config['target']		= "avia-section-drop";
				$this->config['shortcode'] 	= 'av_two_third';
				$this->config['html_renderer'] 	= false;
				$this->config['tooltip'] 	= __('Creates a single column with 67&percnt; width', 'avia_framework' );
				$this->config['drag-level'] = 2;
				$this->config['drop-level'] = 2;
				$this->config['tinyMCE'] 	= array(
				    'name' => '2/3 + 1/3',
                    'instantInsert' => "[av_two_third first]Add 2/3 Content here[/av_two_third]\n\n\n[av_one_third]Add 1/3 Content here[/av_one_third]"
				                                    );
			}
	}
}

if ( !class_exists( 'avia_sc_columns_one_fourth' ) )
{
	class avia_sc_columns_one_fourth extends avia_sc_columns{

			function shortcode_insert_button()
			{
				$this->config['name']		= '1/4';
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-fourth.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 60;
				$this->config['target']		= "avia-section-drop";
				$this->config['shortcode'] 	= 'av_one_fourth';
				$this->config['tooltip'] 	= __('Creates a single column with 25&percnt; width', 'avia_framework' );
				$this->config['html_renderer'] 	= false;
				$this->config['drag-level'] = 2;
				$this->config['drop-level'] = 2;
				$this->config['tinyMCE'] 	= array(
                    'name' => '1/4 + 1/4 + 1/4 + 1/4',
                    'instantInsert' => "[av_one_fourth first]Add Content here[/av_one_fourth]\n\n\n[av_one_fourth]Add Content here[/av_one_fourth]\n\n\n[av_one_fourth]Add Content here[/av_one_fourth]\n\n\n[av_one_fourth]Add Content here[/av_one_fourth]"
				                                    );
			}
	}
}

if ( !class_exists( 'avia_sc_columns_three_fourth' ) )
{
	class avia_sc_columns_three_fourth extends avia_sc_columns{

			function shortcode_insert_button()
			{
				$this->config['name']		= '3/4';
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-three_fourth.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 50;
				$this->config['target']		= "avia-section-drop";
				$this->config['shortcode'] 	= 'av_three_fourth';
				$this->config['tooltip'] 	= __('Creates a single column with 75&percnt; width', 'avia_framework' );
				$this->config['html_renderer'] 	= false;
				$this->config['drag-level'] = 2;
				$this->config['drop-level'] = 2;
				$this->config['tinyMCE'] 	= array(
                    'name' => '3/4 + 1/4',
                    'instantInsert' => "[av_three_fourth first]Add 3/4 Content here[/av_three_fourth]\n\n\n[av_one_fourth]Add 1/4 Content here[/av_one_fourth]"
				                                    );
			}
	}
}

if ( !class_exists( 'avia_sc_columns_one_fifth' ) )
{
	class avia_sc_columns_one_fifth extends avia_sc_columns{

			function shortcode_insert_button()
			{
				$this->config['name']		= '1/5';
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-fifth.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 40;
				$this->config['target']		= "avia-section-drop";
				$this->config['shortcode'] 	= 'av_one_fifth';
				$this->config['html_renderer'] 	= false;
				$this->config['tooltip'] 	= __('Creates a single column with 20&percnt; width', 'avia_framework' );
				$this->config['drag-level'] = 2;
				$this->config['drop-level'] = 2;
				$this->config['tinyMCE'] 	= array(
                    'name' => '1/5 + 1/5 + 1/5 + 1/5 + 1/5',
                    'instantInsert' => "[av_one_fifth first]1/5[/av_one_fifth]\n\n\n[av_one_fifth]2/5[/av_one_fifth]\n\n\n[av_one_fifth]3/5[/av_one_fifth]\n\n\n[av_one_fifth]4/5[/av_one_fifth]\n\n\n[av_one_fifth]5/5[/av_one_fifth]"
				                                    );
			}
	}
}

if ( !class_exists( 'avia_sc_columns_two_fifth' ) )
{
	class avia_sc_columns_two_fifth extends avia_sc_columns{

			function shortcode_insert_button()
			{
				$this->config['name']		= '2/5';
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-two_fifth.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 39;
				$this->config['target']		= "avia-section-drop";
				$this->config['shortcode'] 	= 'av_two_fifth';
				$this->config['html_renderer'] 	= false;
				$this->config['tooltip'] 	= __('Creates a single column with 40&percnt; width', 'avia_framework' );
				$this->config['drag-level'] = 2;
				$this->config['drop-level'] = 2;
				$this->config['tinyMCE'] 	= array(
                    'name' => '2/5',
                    'instantInsert' => "[av_two_fifth first]2/5[/av_two_fifth]"
				                                    );
			}
	}
}

if ( !class_exists( 'avia_sc_columns_three_fifth' ) )
{
	class avia_sc_columns_three_fifth extends avia_sc_columns{

			function shortcode_insert_button()
			{
				$this->config['name']		= '3/5';
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-three_fifth.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 38;
				$this->config['target']		= "avia-section-drop";
				$this->config['shortcode'] 	= 'av_three_fifth';
				$this->config['html_renderer'] 	= false;
				$this->config['tooltip'] 	= __('Creates a single column with 60&percnt; width', 'avia_framework' );
				$this->config['drag-level'] = 2;
				$this->config['drop-level'] = 2;
				$this->config['tinyMCE'] 	= array(
                    'name' => '3/5',
                    'instantInsert' => "[av_three_fifth first]3/5[/av_three_fifth]"
				                                    );
			}
	}
}

if ( !class_exists( 'avia_sc_columns_four_fifth' ) )
{
	class avia_sc_columns_four_fifth extends avia_sc_columns{

			function shortcode_insert_button()
			{
				$this->config['name']		= '4/5';
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-four_fifth.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 37;
				$this->config['target']		= "avia-section-drop";
				$this->config['shortcode'] 	= 'av_four_fifth';
				$this->config['html_renderer'] 	= false;
				$this->config['tooltip'] 	= __('Creates a single column with 80&percnt; width', 'avia_framework' );
				$this->config['drag-level'] = 2;
				$this->config['drop-level'] = 2;
				$this->config['tinyMCE'] 	= array(
                    'name' => '4/5',
                    'instantInsert' => "[av_four_fifth first]4/5[/av_four_fifth]"
				                                    );
			}
	}
}


