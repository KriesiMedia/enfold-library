<?php
/**
 * Color Section
 * 
 * Shortcode creates a section with unique background image and colors for better content sepearation
 */

 // Don't load directly
if ( ! defined('ABSPATH') ) { die('-1'); }



if ( ! class_exists( 'avia_sc_section' ) )
{
	class avia_sc_section extends aviaShortcodeTemplate
	{

			static $section_count = 0;
			static $add_to_closing = "";
			static $close_overlay = "";
			

			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['type']			=	'layout';
				$this->config['self_closing']	=	'no';
				$this->config['contains_text']	=	'no';
				
				$this->config['name']		= __('Color Section', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-section.png";
				$this->config['tab']		= __('Layout Elements', 'avia_framework' );
				$this->config['order']		= 20;
				$this->config['shortcode'] 	= 'av_section';
				$this->config['html_renderer'] 	= false;
				$this->config['tinyMCE'] 	= array('disable' => "true");
				$this->config['tooltip'] 	= __('Creates a section with unique background image and colors', 'avia_framework' );
				$this->config['drag-level'] = 1;
				$this->config['drop-level'] = 1;
				$this->config['preview'] = false;

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
				$name = $this->config['shortcode'];
				$data['shortcodehandler'] 	= $this->config['shortcode'];
    			$data['modal_title'] 		= $this->config['name'];
    			$data['modal_ajax_hook'] 	= $this->config['shortcode'];
				$data['dragdrop-level'] 	= $this->config['drag-level'];
				$data['allowed-shortcodes']	= $this->config['shortcode'];
				$data['preview'] 			= !empty($this->config['preview']) ? $this->config['preview'] : 0;
				
				$title_id = !empty($args['id']) ? ": ".ucfirst($args['id']) : "";

                // add background color or gradient to indicator
                $el_bg = "";

                if( empty( $args['background'] ) || ( $args['background'] == 'bg_color' ) )
                {
                    $el_bg = !empty($args['custom_bg']) ? " style='background:".$args['custom_bg'].";'" : "";
                }
                else {
                    if ($args['background_gradient_color1'] && $args['background_gradient_color2']) {
                        $el_bg = "style='background:linear-gradient(".$args['background_gradient_color1'].",".$args['background_gradient_color2'].");'";
                    }
                }



				$hidden_el_active = !empty($args['av_element_hidden_in_editor']) ? "av-layout-element-closed" : "";
				
    			if(!empty($this->config['modal_on_load']))
    			{
    				$data['modal_on_load'] 	= $this->config['modal_on_load'];
    			}

    			$dataString  = AviaHelper::create_data_string($data);

				$output  = "<div class='avia_layout_section {$hidden_el_active} avia_pop_class avia-no-visual-updates ".$name." av_drag' ".$dataString.">";

				$output .= "    <div class='avia_sorthandle menu-item-handle'>";
				$output .= "        <span class='avia-element-title'><span class='avia-element-bg-color' ".$el_bg."></span>".$this->config['name']."<span class='avia-element-title-id'>".$title_id."</span></span>";
			    //$output .= "        <a class='avia-new-target'  href='#new-target' title='".__('Move Section','avia_framework' )."'>+</a>";
				$output .= "        <a class='avia-delete'  href='#delete' title='".__('Delete Section','avia_framework' )."'>x</a>";
				$output .= "        <a class='avia-toggle-visibility'  href='#toggle' title='".__('Show/Hide Section','avia_framework' )."'></a>";

				if(!empty($this->config['popup_editor']))
    			{
    				$output .= "    <a class='avia-edit-element'  href='#edit-element' title='".__('Edit Section','avia_framework' )."'>edit</a>";
    			}
				$output .= "<a class='avia-save-element'  href='#save-element' title='".__('Save Element as Template','avia_framework' )."'>+</a>";
				$output .= "        <a class='avia-clone'  href='#clone' title='".__('Clone Section','avia_framework' )."' >".__('Clone Section','avia_framework' )."</a></div>";
				$output .= "    <div class='avia_inner_shortcode avia_connect_sort av_drop' data-dragdrop-level='".$this->config['drop-level']."'>";
				$output .= "<textarea data-name='text-shortcode' cols='20' rows='4'>".ShortcodeHelper::create_shortcode_by_array($name, $content, $args)."</textarea>";
				if($content)
				{
					$content = $this->builder->do_shortcode_backend($content);
				}
				$output .= $content;
				$output .= "</div>";
				
				$output .= "<div class='avia-layout-element-bg' ".$this->get_bg_string($args)."></div>";
				
				
				$output .= "<a class='avia-layout-element-hidden' href='#'>".__('Section content hidden. Click here to show it','avia_framework')."</a>";
				
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
						$bg 	= !empty($args['custom_bg']) ? 	$args['custom_bg'] : "transparent"; $bg = "transparent";
						$pos 	= !empty($args['position'])  ? 	$args['position'] : "center center";
						$repeat = !empty($args['repeat']) ?		$args['repeat'] : "no-repeat";
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
			    global  $avia_config;

				$this->elements = array(

					array(
							"type" 	=> "tab_container", 'nodescription' => true
						),
											
					array(
							"type" 	=> "tab",
							"name"	=> __("Section Layout",'avia_framework' ),
							'nodescription' => true
						),
					
					
					array(
							'name' 	=> __( 'Section Minimum Height','avia_framework' ),
							'id' 	=> 'min_height',
							'desc'  => __( 'Define a minimum height for the section. Content within the section will be centered vertically within the section', 'avia_framework' ),
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype' => array(   
											__( 'No minimum height, use content within section to define Section height', 'avia_framework' )	=> '',
											__( 'At least 100&percnt; of Browser Window height', 'avia_framework' )					=> '100',
											__( 'At least 75&percnt; of Browser Window height', 'avia_framework' )					=> '75',
											__( 'At least 50&percnt; of Browser Window height', 'avia_framework' )					=> '50',
											__( 'At least 25&percnt; of Browser Window height', 'avia_framework' )					=> '25',
											__( 'Custom height at least in &percnt; of Browser Window height', 'avia_framework' )	=> 'percent',
											__( 'Custom height in pixel', 'avia_framework' )										=> 'custom',
						                  )
						),
					
					array(	
							'name' 	=> __( 'Section minimum custom height in &percnt; of Browser Window height', 'avia_framework' ),
							'desc' 	=> __( 'Define a minimum height for the section in &percnt; of Browser Window height', 'avia_framework' ),
							'id' 	=> 'min_height_pc',
							'required'	=> array( 'min_height', 'equals', 'percent' ),
							'std' 	=> '25',
							'type' 	=> 'select',
							'subtype' => AviaHtmlHelper::number_array( 1, 99, 1 )
						),
				    
				    array(	
							"name" 	=> __("Section custom height", 'avia_framework' ),
							"desc" 	=> __("Define a minimum height for the section. Use a pixel value. eg: 500px", 'avia_framework' ) ,
							"id" 	=> "min_height_px",
							"required"=> array('min_height','equals','custom'),
							"std" 	=> "500px",
							"type" 	=> "input"),
					
				
				    array(
						"name" 	=> __("Section Padding",'avia_framework' ),
						"id" 	=> "padding",
						"desc"  => __("Define the sections top and bottom padding",'avia_framework' ),
						"type" 	=> "select",
						"std" 	=> "default",
						"subtype" => array(   __('No Padding','avia_framework' )	=>'no-padding',
											  __('Small Padding','avia_framework' )	=>'small',
						                      __('Default Padding','avia_framework' )	=>'default',
						                      __('Large Padding','avia_framework' )	=>'large',
						                      __('Huge Padding','avia_framework' )	=>'huge',
						                  )
				    ),


				   array(
						"name" 	=> __("Section Top Border Styling",'avia_framework' ),
						"id" 	=> "shadow",
						"desc"  => __("Choose a border styling for the top of your section",'avia_framework' ),
						"type" 	=> "select",
						"std" 	=> "no-border-styling",
						"subtype" => array( __('Display a simple 1px top border','avia_framework' )	=>'no-shadow',  
											__('Display a small styling shadow at the top of the section','avia_framework' )	=>'shadow',
											__('No border styling','avia_framework' )	=>'no-border-styling',
						                  )
				    ),
				    
				    
				    array(
						"name" 	=> __("Section Bottom Border Styling",'avia_framework' ),
						"id" 	=> "bottom_border",
						"desc"  => __("Choose a border styling for the bottom of your section",'avia_framework' ),
						"type" 	=> "select",
						"std" 	=> "no-border-styling",
						"subtype" => array(   
											__('No border styling','avia_framework' )	=>'no-border-styling',
											__('Display a small arrow that points down to the next section','avia_framework' )	=>'border-extra-arrow-down',
											__('Diagonal section border','avia_framework' )	=>'border-extra-diagonal',
						                  )
				    ),
				    
				    
				     array(
						"name" 		=> __("Diagonal Border: Color", 'avia_framework' ),
						"desc" 		=> __("Select a custom background color for your Section border here. ", 'avia_framework' ),
						"id" 		=> "bottom_border_diagonal_color",
						"type" 		=> "colorpicker",
						"container_class" 	=> "av_third av_third_first",
                        "required" => array('bottom_border','contains','diagonal'),
						"std" 		=> "#333333",
					),
					
					array(
						"name" 	=> __("Diagonal Border: Direction",'avia_framework' ),
						"desc" 	=> __("Set the direction of the diagonal border", 'avia_framework' ),
						"id" 	=> "bottom_border_diagonal_direction",
						"type" 	=> "select",
						"std" 	=> "scroll",
						"container_class" 	=> "av_third",
                        "required" => array('bottom_border','contains','diagonal'),
						"subtype" => array(
							__('Slanting from left to right','avia_framework' )=>'',
							__('Slanting from right to left' ) =>'border-extra-diagonal-inverse',
							
							)
						),
				    
				    array(
						"name" 	=> __("Diagonal Border Box Style",'avia_framework' ),
						"desc" 	=> __("Set the style shadow of the border", 'avia_framework' ),
						"id" 	=> "bottom_border_style",
						"type" 	=> "select",
						"std" 	=> "scroll",
						"container_class" 	=> "av_third",
                        "required" => array('bottom_border','contains','diagonal'),
						"subtype" => array(
							__('Minimal','avia_framework' )=>'',
							__('Box shadow' ) =>'diagonal-box-shadow',
							
							)
						),


                    array(
                        "name" 	=> __("Custom top and bottom margin",'avia_framework' ),
                        "desc" 	=> __("If checked allows you to set a custom top and bottom margin. Otherwise the margin is calculated by the theme based on surrounding elements",'avia_framework' ),
                        "id" 	=> "margin",
                        "type" 	=> "checkbox",
                        "std" 	=> "",
                    ),

                    array(
                        "name" 	=> __("Custom top and bottom margin", 'avia_framework' ),
                        "desc" 	=> __("Set a custom top or bottom margin. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;", 'avia_framework' ),
                        "id" 	=> "custom_margin",
                        "type" 	=> "multi_input",
                        "required" => array('margin','not',''),
                        "std" 	=> "0px",
                        "sync" 	=> true,
                        "multi" => array(
                            'top' 	=> __('Margin-Top','avia_framework'),
                            'bottom'=> __('Margin-Bottom','avia_framework'),
                        )
                    ),


                    array(
						"name" 	=> __("Display a scroll down arrow", 'avia_framework' ),
						"desc" 	=> __("Check if you want to show a button at the bottom of the section that takes the user to the next section by scrolling down", 'avia_framework' ) ,
						"id" 	=> "scroll_down",
						"std" 	=> "",
						"type" 	=> "checkbox"),
						
					
					array(
							"name" 	=> __("Custom Arrow Color", 'avia_framework' ),
							"desc" 	=> __("Select a custom arrow color. Leave empty if you want to use the default arrow color and style", 'avia_framework' ),
							"id" 	=> "custom_arrow_bg",
							"type" 	=> "colorpicker",
							"std" 	=> "",
							"required" => array('scroll_down','not',''),
						),
					
					


				  array(	"name" 	=> __("For Developers: Section ID", 'avia_framework' ),
							"desc" 	=> __("Apply a custom ID Attribute to the section, so you can apply a unique style via CSS. This option is also helpful if you want to use anchor links to scroll to a sections when a link is clicked", 'avia_framework' )."<br/><br/>".
									   __("Use with caution and make sure to only use allowed characters. No special characters can be used.", 'avia_framework' ),
				            "id" 	=> "id",
				            "type" 	=> "input",
				            "std" => ""),
				            
					array(
							"type" 	=> "close_div",
							'nodescription' => true
						),
					array(
							"type" 	=> "tab",
							"name"  => __("Section Background" , 'avia_framework'),
							'nodescription' => true
						),
						
						
			        array(
						"name" 	=> __("Section Colors",'avia_framework' ),
						"id" 	=> "color",
						"desc"  => __("The section will use the color scheme you select. Color schemes are defined on your styling page",'avia_framework' ) .
						           '<br/><a target="_blank" href="'.admin_url('admin.php?page=avia#goto_styling').'">'.__("(Show Styling Page)",'avia_framework' )."</a>",
						"type" 	=> "select",
						"std" 	=> "main_color",
						"subtype" =>  array_flip($avia_config['color_sets'])
				    ),

                    array(
                        "name" 	=> __("Background",'avia_framework' ),
                        "desc" 	=> __("Select the type of background for the column.", 'avia_framework' ),
                        "id" 	=> "background",
                        "type" 	=> "select",
                        "std" 	=> "bg_color",
                        "subtype" => array(
                            __('Background Color','avia_framework' )=>'bg_color',
                            __('Background Gradient','avia_framework' ) =>'bg_gradient',
                        )
                    ),

                    array(
                        "name" 	=> __("Custom Background Color", 'avia_framework' ),
                        "desc" 	=> __("Select a custom background color for this cell here. Leave empty for default color", 'avia_framework' ),
                        "id" 	=> "custom_bg",
                        "type" 	=> "colorpicker",
                        "required" => array('background','equals','bg_color'),
                        "rgba" 	=> true,
                        "std" 	=> "",
                    ),

                    array(
                        "name" 	=> __("Background Gradient Color 1", 'avia_framework' ),
                        "desc" 	=> __("Select the first color for the gradient.", 'avia_framework' ),
                        "id" 	=> "background_gradient_color1",
                        "type" 	=> "colorpicker",
                        "container_class" => 'av_third av_third_first',
                        "required" => array('background','equals','bg_gradient'),
                        "rgba" 	=> true,
                        "std" 	=> "",
                    ),
                    array(
                        "name" 	=> __("Background Gradient Color 2", 'avia_framework' ),
                        "desc" 	=> __("Select the second color for the gradient.", 'avia_framework' ),
                        "id" 	=> "background_gradient_color2",
                        "type" 	=> "colorpicker",
                        "container_class" => 'av_third',
                        "required" => array('background','equals','bg_gradient'),
                        "rgba" 	=> true,
                        "std" 	=> "",
                    ),

                    array(
                        "name" 	=> __("Background Gradient Direction",'avia_framework' ),
                        "desc" 	=> __("Define the gradient direction", 'avia_framework' ),
                        "id" 	=> "background_gradient_direction",
                        "type" 	=> "select",
                        "container_class" => 'av_third',
                        "std" 	=> "vertical",
                        "required" => array('background','equals','bg_gradient'),
                        "subtype" => array(
                            __('Vertical','avia_framework' )=>'vertical',
                            __('Horizontal','avia_framework' ) =>'horizontal',
                            __('Radial','avia_framework' ) =>'radial',
                            __('Diagonal Top Left to Bottom Right','avia_framework' ) =>'diagonal_tb',
                            __('Diagonal Bottom Left to Top Right','avia_framework' ) =>'diagonal_bt',
                        )
                    ),

					array(
							"name" 	=> __("Custom Background Image",'avia_framework' ),
							"desc" 	=> __("Either upload a new, or choose an existing image from your media library. Leave empty if you want to use the background image of the color scheme defined above",'avia_framework' ),
							"id" 	=> "src",
							"type" 	=> "image",
							"title" => __("Insert Image",'avia_framework' ),
							"button" => __("Insert",'avia_framework' ),
							"std" 	=> ""),
					
					array(
						"name" 	=> __("Background Attachment",'avia_framework' ),
						"desc" 	=> __("Background can either scroll with the page, be fixed or scroll with a parallax motion", 'avia_framework' ),
						"id" 	=> "attach",
						"type" 	=> "select",
						"std" 	=> "scroll",
                        "required" => array('src','not',''),
						"subtype" => array(
							__('Scroll','avia_framework' )=>'scroll',
							__('Fixed','avia_framework' ) =>'fixed',
							__('Parallax','avia_framework' ) =>'parallax'
							
							)
						),
					
                    array(
						"name" 	=> __("Background Image Position",'avia_framework' ),
						"id" 	=> "position",
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
						"id" 	=> "repeat",
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
						"name" 	=> __("Background Video", 'avia_framework' ),
						"desc" 	=> __('You can also place a video as background for your section. Enter the URL to the Video. Currently supported are Youtube, Vimeo and direct linking of web-video files (mp4, webm, ogv)', 'avia_framework' ) .'<br/><br/>'.
						__('Working examples Youtube & Vimeo:', 'avia_framework' ).'<br/>
					<strong>http://vimeo.com/1084537</strong><br/> 
					<strong>http://www.youtube.com/watch?v=5guMumPFBag</strong><br/><br/>',
						"id" 	=> "video",
						"std" 	=> "",
						"type" 	=> "input"),
						
					array(	
							"name" 	=> __("Video Aspect Ratio", 'avia_framework' ),
							"desc" 	=> __("In order to calculate the correct height and width for the video slide you need to enter a aspect ratio (width:height). usually: 16:9 or 4:3.", 'avia_framework' )."<br/>".__("If left empty 16:9 will be used", 'avia_framework' ) ,
							"id" 	=> "video_ratio",
							"required"=> array('video','not',''),
							"std" 	=> "16:9",
							"type" 	=> "input"),
					
					array(	
							"name" 	=> __("Hide video on Mobile Devices?", 'avia_framework' ),
							"desc" 	=> __("You can choose to hide the video entirely on Mobile devices and instead display the Section Background image", 'avia_framework' )."<br/><small>".__("Most mobile devices can't autoplay videos to prevent bandwidth problems for the user", 'avia_framework' ) ."</small>" ,
							"id" 	=> "video_mobile_disabled",
							"required"=> array('video','not',''),
							"std" 	=> "",
							"type" 	=> "checkbox"),
					
					
					array(
							"type" 	=> "close_div",
							'nodescription' => true
						),
					

					
array(
							"type" 	=> "tab",
							"name"  => __("Section Background Overlay" , 'avia_framework'),
							'nodescription' => true
						),
					
					array(	
							"name" 	=> __("Enable Overlay?", 'avia_framework' ),
							"desc" 	=> __("Check if you want to display a transparent color and/or pattern overlay above your section background image/video", 'avia_framework' ),
							"id" 	=> "overlay_enable",
							"std" 	=> "",
							"type" 	=> "checkbox"),
					
					 array(
						"name" 	=> __("Overlay Opacity",'avia_framework' ),
						"desc" 	=> __("Set the opacity of your overlay: 0.1 is barely visible, 1.0 is opaque ", 'avia_framework' ),
						"id" 	=> "overlay_opacity",
						"type" 	=> "select",
						"std" 	=> "0.5",
                        "required" => array('overlay_enable','not',''),
						"subtype" => array(   __('0.1','avia_framework' )=>'0.1',
						                      __('0.2','avia_framework' )=>'0.2',
						                      __('0.3','avia_framework' )=>'0.3',
						                      __('0.4','avia_framework' )=>'0.4',
						                      __('0.5','avia_framework' )=>'0.5',
						                      __('0.6','avia_framework' )=>'0.6',
						                      __('0.7','avia_framework' )=>'0.7',
						                      __('0.8','avia_framework' )=>'0.8',
						                      __('0.9','avia_framework' )=>'0.9',
						                      __('1.0','avia_framework' )=>'1',
						                      )
				  		),
				  		
				  	array(
							"name" 	=> __("Overlay Color", 'avia_framework' ),
							"desc" 	=> __("Select a custom  color for your overlay here. Leave empty if you want no color overlay", 'avia_framework' ),
							"id" 	=> "overlay_color",
							"type" 	=> "colorpicker",
                        	"required" => array('overlay_enable','not',''),
							"std" 	=> "",
						),
				  	
				  	array(
                        "required" => array('overlay_enable','not',''),
						"id" 	=> "overlay_pattern",
						"name" 	=> __("Background Image", 'avia_framework'),
						"desc" 	=> __("Select an existing or upload a new background image", 'avia_framework'),
						"type" 	=> "select",
						"subtype" => array(__('No Background Image', 'avia_framework')=>'',__('Upload custom image', 'avia_framework')=>'custom'),
						"std" 	=> "",
						"folder" => "images/background-images/",
						"folderlabel" => "",
						"group" => "Select predefined pattern",
						"exclude" => array('fullsize-', 'gradient')
					),
				  	
				  	
				  	array(
							"name" 	=> __("Custom Pattern",'avia_framework' ),
							"desc" 	=> __("Upload your own seamless pattern",'avia_framework' ),
							"id" 	=> "overlay_custom_pattern",
							"type" 	=> "image",
							"fetch" => "url",
							"secondary_img"=>true,
                        	"required" => array('overlay_pattern','equals','custom'),
							"title" => __("Insert Pattern",'avia_framework' ),
							"button" => __("Insert",'avia_framework' ),
							"std" 	=> ""),
					
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
						
						
						
					array(	"id" 	=> "av_element_hidden_in_editor",
				            "type" 	=> "hidden",
				            "std" => "0"),
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
				
				extract(AviaHelper::av_mobile_sizes($atts)); //return $av_font_classes, $av_title_font_classes and $av_display_classes 

				avia_sc_section::$section_count ++;
			    $atts = shortcode_atts( array(	'src'				=> '', 
			    								'position'			=> 'top left', 
			    								'repeat'			=> 'no-repeat', 
			    								'attach'			=> 'scroll', 
			    								'color'				=> 'main_color',
                                                'background'		=> '',
                                                'custom_bg'			=> '',
                                                'background_gradient_color1'		=> '',
                                                'background_gradient_color2'		=> '',
                                                'background_gradient_direction'		=> '',
			    								'padding'			=> 'default' ,
			    								'margin'			=> '',
			    								'custom_margin'		=> '',
			    								'shadow'			=> 'shadow', 
			    								'id'				=> '', 
			    								'min_height'		=> '', 
												'min_height_pc'		=> 25,
			    								'min_height_px'		=> '', 
			    								'video'				=> '', 
			    								'video_ratio'		=>' 16:9', 
			    								'video_mobile_disabled'			=>'',
			    								'custom_markup'		=> '',
			    								'attachment'		=> '',
			    								'attachment_size'	=> '',
			    								'bottom_border'		=> '',
			    								'overlay_enable'	=> '',
			    								'overlay_opacity'	=> '',
			    								'overlay_color'		=> '',
			    								'overlay_pattern'	=> '',
			    								'overlay_custom_pattern'	=> '',
			    								'scroll_down'		=> '',
			    								'bottom_border_diagonal_color'		=> '',
			    								'bottom_border_diagonal_direction'	=> '',
			    								'bottom_border_style'				=> '',
			    								'custom_arrow_bg'					=> ''
			    								
			    								), 
			    							$atts, $this->config['shortcode'] );
							    							
			    							
				if( 'percent' == $atts['min_height'] )
				{
					$atts['min_height'] = $atts['min_height_pc'];
				}
				
				extract($atts);
				
			    $output      = "";
			    $class       = "avia-section ".$color." avia-section-".$padding." avia-".$shadow;
			    $background  = "";
				$src		 = '';
				
				
				$params['id'] = !empty($id) ? AviaHelper::save_string($id,'-') :"av_section_".avia_sc_section::$section_count;
				$params['custom_markup'] = $meta['custom_markup'];
				$params['attach'] = "";
				
				if( ! empty( $attachment ) && ! empty( $attachment_size ) )
				{
					/**
					 * Allows e.g. WPML to reroute to translated image
					 */
					$posts = get_posts( array(
											'include'			=> $attachment,
											'post_status'		=> 'inherit',
											'post_type'			=> 'attachment',
											'post_mime_type'	=> 'image',
											'order'				=> 'ASC',
											'orderby'			=> 'post__in' )
										);
					
					if( is_array( $posts ) && ! empty( $posts ) )
					{
						$attachment_entry = $posts[0];
	                	
						$src = wp_get_attachment_image_src( $attachment_entry->ID, $attachment_size );
						$src = !empty($src[0]) ? $src[0] : "";
					}
				}
				else
				{
					$attachment = false;
				}


                // background gradient

                $gradient_val = "";

                if ($atts['background'] == 'bg_gradient'){
                    if ( $atts['background_gradient_color1'] && $atts['background_gradient_color2']) {

                        switch ($atts['background_gradient_direction']) {
                            case 'vertical':
                                $gradient_val .= 'linear-gradient(';
                                break;
                            case 'horizontal':
                                $gradient_val .= 'linear-gradient(to right,';
                                break;
                            case 'radial':
                                $gradient_val .= 'radial-gradient(';
                                break;
                            case 'diagonal_tb':
                                $gradient_val .= 'linear-gradient(to bottom right,';
                                break;
                            case 'diagonal_bt':
                                $gradient_val .= 'linear-gradient(45deg,';
                                break;
                        }

                        $gradient_val .= $atts['background_gradient_color1'].','.$atts['background_gradient_color2'].')';

                        // Fallback background color for IE9
                        if($custom_bg == "") $background .= "background-color: {$atts['background_gradient_color1']};";

                    }
                }

				
				if($custom_bg != "")
			    {
			         $background .= "background-color: {$custom_bg}; ";
			    }


				/*set background image*/
				if($src != "")
				{
					if($repeat == 'stretch')
					{
						$background .= "background-repeat: no-repeat; ";
						$class .= " avia-full-stretch";
					} 
					else if($repeat == "contain")
					{
						$background .= "background-repeat: no-repeat; ";
						$class .= " avia-full-contain";
					}
					else
					{
						$background .= "background-repeat: {$repeat}; ";
					}
				
				     $background .= "background-image: url({$src})";
					 if ($gradient_val !== '') {
                         $background .= ", {$gradient_val}";
                     }
					 $background .= ";";
				     $background .= $attach == 'parallax' ? "background-attachment: scroll; " : "background-attachment: {$attach}; ";
				     $background .= "background-position: {$position}; ";
				     
				     
				     
				    if($attach == 'parallax')
					{
						$attachment_class = "";
						if($repeat == 'stretch' || $repeat == 'no-repeat' ){ $attachment_class .= " avia-full-stretch"; }
						if($repeat == 'contain'  ){ $attachment_class .= " avia-full-contain"; }
					
						$class .= " av-parallax-section";
						$speed = apply_filters('avf_parallax_speed', "0.3", $params['id']); 
						$params['attach'] .= "<div class='av-parallax' data-avia-parallax-ratio='{$speed}' >";
						$params['attach'] .= "<div class='av-parallax-inner {$color} {$attachment_class}' style = '{$background}' >";
						$params['attach'] .= "</div>";
						$params['attach'] .= "</div>";
						$background = "";
					}
					
					
					$params['data'] = "data-section-bg-repeat='{$repeat}'";
					
				}
				else if( ! empty( $gradient_val ) )
				{
					$attach = "scroll";
					if ($gradient_val !== ""){
                        $background .= "background-image: {$gradient_val};";
                    }
				}
				
				
				if($custom_bg != "" && $background == "")
			    {
			         $background .= "background-color: {$custom_bg}; ";
			    }

                /* custom margin */
                if (!empty($atts['margin'])){
                    $explode_custom_margin = explode(',',$atts['custom_margin']);
                    if(count($explode_custom_margin) > 1)
                    {
                        $atts['margin-top'] = $explode_custom_margin['0'];
                        $atts['margin-bottom'] = $explode_custom_margin['1'];
                    }
                    else {
                        $atts['margin-top'] = $atts['custom_margin'];
                        $atts['margin-bottom'] = $atts['custom_margin'];
                    }
                }

                $custom_margin_style = "";
                $custom_margin_style .= AviaHelper::style_string($atts, 'margin-top');
                $custom_margin_style .= AviaHelper::style_string($atts, 'margin-bottom');


                /*check/create overlay*/
				$overlay 	= "";
				$pre_wrap 	= "<div class='av-section-color-overlay-wrap'>" ;
				if(!empty($overlay_enable))
				{
					$overlay_src = "";
					$overlay = "opacity: {$overlay_opacity}; ";
					if(!empty($overlay_color)) $overlay .= "background-color: {$overlay_color}; ";
					if(!empty($overlay_pattern))
					{
						if($overlay_pattern == "custom")
						{
							$overlay_src = $overlay_custom_pattern;
						}
						else
						{
							$overlay_src = str_replace('{{AVIA_BASE_URL}}', AVIA_BASE_URL, $overlay_pattern);
						}
					}
					
					if(!empty($overlay_src)) $overlay .= "background-image: url({$overlay_src}); background-repeat: repeat;";
					$overlay = "<div class='av-section-color-overlay' style='{$overlay}'></div>";
					$class .= " av-section-color-overlay-active";
					
					$params['attach'] .= $pre_wrap . $overlay;
					
				}
				

				
				if(!empty($scroll_down))
				{	
					$arrow_style = "";
					$arrow_class = "";
					
					if(!$overlay)
					{
						$params['attach'] .= $pre_wrap;	
					}
					
					if(!empty($custom_arrow_bg))
					{
						$arrow_style = "style='color: {$custom_arrow_bg};'";
						$arrow_class = " av-custom-scroll-down-color";
					}
					
					$params['attach'] .= "<a href='#next-section' title='' class='scroll-down-link {$arrow_class}' {$arrow_style} ". av_icon_string( 'scrolldown' ). "></a>";
				}
			    
			    
			    
				$class .= " avia-bg-style-".$attach;
			    $params['class'] = $class." ".$meta['el_class']." ".$av_display_classes;
			    $params['bg']    = $background;
                $params['custom_margin'] = $custom_margin_style;
				$params['min_height'] = $min_height;
				$params['min_height_px'] = $min_height_px;
				$params['video'] = $video;
				$params['video_ratio'] = $video_ratio;
				$params['video_mobile_disabled'] = $video_mobile_disabled;
				
			    if(isset($meta['index']))
			    {
			    	if($meta['index'] == 0) 
			    	{
			    		$params['main_container'] = true;
			    	}
			    	
			    	if($meta['index'] == 0 || (isset($meta['siblings']['prev']['tag']) && in_array($meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section )))
			    	{
			    		$params['close'] = false;
			    	}
			    }
			    
			    if($bottom_border == 'border-extra-arrow-down')
			    {
				    $params['class'] .= " av-arrow-down-section";
			    }
			    
		
				
				$avia_config['layout_container'] = "section";
				
				$output .= avia_new_section($params);
				$output .=  ShortcodeHelper::avia_remove_autop($content,true) ;
				
				/*set extra arrow element*/
				if(strpos($bottom_border, 'border-extra') !== false)
				{
					$backgroundEl = "";
					$backgroundElColor = !empty($custom_bg) ? $custom_bg : $avia_config['backend_colors']['color_set'][$color]['bg'];
					
					if(strpos($bottom_border, 'diagonal') !== false)
					{
						// bottom_border_diagonal_direction // bottom_border_diagonal_color
						$backgroundElColor = "#333333";
						if(isset($bottom_border_diagonal_color)) $backgroundElColor = $bottom_border_diagonal_color;
						
						$bottom_border .= " " . $bottom_border_diagonal_direction . " " .$bottom_border_style;
					}
					
					
					if($backgroundElColor) $backgroundEl = " style='background-color:{$backgroundElColor};' ";
					
					avia_sc_section::$add_to_closing = "<div class='av-extra-border-element {$bottom_border}'><div class='av-extra-border-outer'><div class='av-extra-border-inner' {$backgroundEl}></div></div></div>";
				}
				else
				{
					avia_sc_section::$add_to_closing = "";
				}
				
				
				//next section needs an extra closing tag if overlay with wrapper was added:
				if($overlay || !empty($scroll_down)) 
				{ 
					avia_sc_section::$close_overlay = "</div>";
				}
				else
				{
					avia_sc_section::$close_overlay = "";
				}
				
				//if the next tag is a section dont create a new section from this shortcode
				if(!empty($meta['siblings']['next']['tag']) && in_array($meta['siblings']['next']['tag'], AviaBuilder::$full_el))
				{
				    $skipSecond = true;
				}

				//if there is no next element dont create a new section. if we got a sidebar always create a next section at the bottom
				if(empty($meta['siblings']['next']['tag']) && !avia_has_sidebar())
				{
				    $skipSecond = true;
				}

				if(empty($skipSecond))
				{
					$new_params['id'] = "after_section_".( avia_sc_section::$section_count );
					$output .= avia_new_section($new_params);
				}
				
				unset($avia_config['layout_container']);
				return $output;
			}
	}
}



if(!function_exists('avia_new_section'))
{
	function avia_new_section($params = array())
	{
		global $avia_section_markup, $avia_config;
		
	    $defaults = array(	'class'=>'main_color', 
	    					'bg'=>'',
	    					'custom_margin' => '',
	    					'close'=>true,
	    					'open'=>true, 
	    					'open_structure' => true, 
	    					'open_color_wrap' => true, 
	    					'data'=>'', 
	    					"style"=>'', 
	    					'id' => "", 
	    					'main_container' => false, 
	    					'min_height' => '',
	    					'min_height_px' => '',
	    					'video' => '',
	    					'video_ratio' => '16:9',
	    					'video_mobile_disabled' => '',
	    					'attach' => "",
	    					'before_new' => "",
	    					'custom_markup' => ''
	    					);
	    
	    
	    
	    $defaults = array_merge($defaults, $params);
		
	    extract($defaults);

	    $post_class = "";
	    $output     = "";
	    $bg_slider  = "";
	    $container_style = "";
	    if($id) $id = "id='{$id}'";
	
	    //close old content structure. only necessary when previous element was a section. other fullwidth elements dont need this
	    if($close) 
	    {
	    	$cm		 = avia_section_close_markup();
			$output .= "</div></div>{$cm}</div>".avia_sc_section::$add_to_closing.avia_sc_section::$close_overlay."</div>";
			avia_sc_section::$add_to_closing = '';
	    	avia_sc_section::$close_overlay = "";
		}
	    //start new
	    if($open)
	    {	
	        if(function_exists('avia_get_the_id')) $post_class = "post-entry-".avia_get_the_id();
	
	        if($open_color_wrap)
	        {
	        	if( ! empty( $min_height ) ) 
	        	{
					$class .= " av-minimum-height av-minimum-height-{$min_height} ";
					
					if( is_numeric( $min_height ) )
					{
						$data .= " data-av_minimum_height_pc='{$min_height}'";
					}
					
	        		if( $min_height == 'custom' && $min_height_px != '' )
	        		{
	        			$min_height_px 		= (int)$min_height_px;
	        			$container_style 	= "style='height:{$min_height_px}px'";
	        		}
	        	}
	        	
	        	if(!empty($video)) 
	        	{
	        		$slide = array( 
	        						'shortcode' => 'av_slideshow',  
	        						'content' => '', 
	        						'attr' => array( 	'id'=>'', 
	        											'video'=>$video , 
	        											'slide_type' => 'video', 
	        											'video_mute' => true,
	        											'video_loop' => true,
	        											'video_ratio' => $video_ratio,
	        											'video_controls' => 'disabled',
	        											'video_section_bg' => true,
	        											'video_format'=> '',
	        											'video_mobile'	=>'',
	        											'video_mobile_disabled'=> $video_mobile_disabled
	        										)  
	        						);
	        		
	        		
	        		$bg_slider = new avia_slideshow( array('content' => array($slide) ) );
	        		$bg_slider->set_extra_class('av-section-video-bg');
	        		$class .= " av-section-with-video-bg";
	        		$class .= !empty($video_mobile_disabled) ? " av-section-mobile-video-disabled" : "";
	        		$data .= " data-section-video-ratio='{$video_ratio}'";
	        		
	        	}
	        	$output .= $before_new;

	        	
	        	//fix version 4.5.1 by Kriesi
	        	//we cant just overwrite style since it might be passed by a function. eg the menu element passes z-index. need to merge the style strings
	        	
	        	$extra_style = "{$bg} {$custom_margin}";
	        	$style = trim($style);
	        	if(empty($style)) 
	        	{
		        	$style = "style='{$extra_style}' ";
		        }
		        else
		        {
			        $style = str_replace("style='", "style='{$extra_style} ", $style);
			        $style = str_replace('style="', 'style="'.$extra_style.' ', $style);
		        }
	        	
	        	
	        	if($class == "main_color") $class .= " av_default_container_wrap";
	        	
	        	$output .= "<div {$id} class='{$class} container_wrap ".avia_layout_class( 'main' , false )."' {$style} {$data}>";
	        	$output .= !empty($bg_slider) ? $bg_slider->html() : "";
	        	$output .= $attach;
	        	$output .= apply_filters('avf_section_container_add','',$defaults);
	        }
	
			
			//this applies only for sections. other fullwidth elements dont need the container for centering
	        if($open_structure)
	        {
	        	if(!empty($main_container))
	        	{
					$markup = 'main '.avia_markup_helper(array('context' =>'content','echo'=>false, 'custom_markup'=>$custom_markup));
					$avia_section_markup = 'main';
				}
				else
				{
					$markup = "div";
				}
				
		        $output .= "<div class='container' {$container_style}>";
		        $output .= "<{$markup} class='template-page content  ".avia_layout_class( 'content' , false )." units'>";
		        $output .= "<div class='post-entry post-entry-type-page {$post_class}'>";
		        $output .= "<div class='entry-content-wrapper clearfix'>";
	        }
	    }
	    return $output;
	
	}
}



if(!function_exists('avia_section_close_markup'))
{
	function avia_section_close_markup()
	{
		global $avia_section_markup, $avia_config;
		
		if(!empty($avia_section_markup))
		{
			$avia_section_markup = false;
			$close_markup = "</main><!-- close content main element -->";
			
		}
		else
		{
			$close_markup = "</div><!-- close content main div -->"; 
		}
		
		return $close_markup;
	}
}

if(!function_exists('avia_section_after_element_content'))
{
	function avia_section_after_element_content($meta, $second_id = "", $skipSecond = false, $extra = "")
	{
		$output = "</div>"; //close section
		$output .= $extra;
					
		//if the next tag is a section dont create a new section from this shortcode
		if(!empty($meta['siblings']['next']['tag']) && in_array($meta['siblings']['next']['tag'], AviaBuilder::$full_el )){ $skipSecond = true; }
	
		//if there is no next element dont create a new section.
		if(empty($meta['siblings']['next']['tag'])) { $skipSecond = true; }
		
		if(empty($skipSecond)) { $output .= avia_new_section(array('close'=>false, 'id' => $second_id)); }
		
		return $output;
	}
}


