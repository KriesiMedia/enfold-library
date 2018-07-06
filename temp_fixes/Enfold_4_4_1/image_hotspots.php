<?php
/**
 * Image with Hotspots
 * 
 * Shortcode which inserts an image with one or many hotspots that show tooltips
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( !class_exists( 'avia_sc_image_hotspots' ) )
{
	class avia_sc_image_hotspots extends aviaShortcodeTemplate
	{
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['self_closing']	=	'no';
				
				$this->config['name']			= __('Image with Hotspots', 'avia_framework' );
				$this->config['tab']			= __('Media Elements', 'avia_framework' );
				$this->config['icon']			= AviaBuilder::$path['imagesURL']."sc-image-hotspot.png";
				$this->config['order']			= 95;
				$this->config['target']			= 'avia-target-insert';
				$this->config['shortcode'] 		= 'av_image_hotspot';
				$this->config['shortcode_nested'] = array('av_image_spot');
				$this->config['modal_data'] 	= array('modal_class' => 'bigscreen');
				$this->config['tooltip'] 	    = __('Inserts an image with one or many hotspots that show tooltips', 'avia_framework' );
				$this->config['disabling_allowed'] = true;
			}
			
			function extra_assets()
			{
				//load css
				wp_enqueue_style( 'avia-module-hotspot' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/image_hotspots/image_hotspots.css' , array('avia-layout'), false );
				
					//load js
				wp_enqueue_script( 'avia-module-hotspot' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/image_hotspots/image_hotspots.js' , array('avia-shortcodes'), false, TRUE );

			
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
							"name" 	=> __("Choose Image",'avia_framework' ),
							"desc" 	=> __("Either upload a new, or choose an existing image from your media library. Once an Image has been selected you can add your Hotspots",'avia_framework' ),
							"id" 	=> "src",
							"type" 	=> "image",
							"container_class" => "av-hotspot-container",
							"title" => __("Insert Image",'avia_framework' ),
							"button" => __("Insert",'avia_framework' ),
							"std" 	=> AviaBuilder::$path['imagesURL']."placeholder-full.jpg"),
					
					array(
							"name" => __("Add/Edit your hotspots.", 'avia_framework' ),
							"desc" => __("Here you can add, remove and edit the locations, tooltips and appearance for your hotspots.", 'avia_framework' )."<br/>",
							"type" 			=> "modal_group",
							"id" 			=> "content",
							"modal_title" 	=> __("Edit Hotspot Tooltip", 'avia_framework' ),
							"add_label"		=> __("Add Hotspot", 'avia_framework' ),
							"std"			=> array(  ),
							"special_modal"	=> array(
								"type" => "hotspot",
								"image_container_class" => "av-hotspot-container"
							),
							'subelements' 	=> array(
									
									array(
											"type" 	=> "tab_container", 'nodescription' => true
										),
										
									array(
											"type" 	=> "tab",
											"name"  => __("Tooltip" , 'avia_framework'),
											'nodescription' => true
										),
									
									array(
									"name" 	=> __("Tooltip Position", 'avia_framework' ),
									"desc" 	=> __("Select where to display the tooltip in relation to the hotspot", 'avia_framework' ),
									"id" 	=> "tooltip_pos",
									"type" 	=> "select",
									"std" 	=> "above",
									"container_class" => 'av_half av_half_first',
									"subtype" => array(
														'Above'=> array(
															__('Top Left',  	'avia_framework' ) =>'av-tt-pos-above av-tt-align-left',
															__('Top Right',  	'avia_framework' ) =>'av-tt-pos-above av-tt-align-right',
															__('Top Centered',  'avia_framework' ) =>'av-tt-pos-above av-tt-align-centered',
														),
														'Below'=> array(
															__('Bottom Left',  		'avia_framework' ) =>'av-tt-pos-below av-tt-align-left',
															__('Bottom Right',  	'avia_framework' ) =>'av-tt-pos-below av-tt-align-right',
															__('Bottom Centered', 	'avia_framework' ) =>'av-tt-pos-below av-tt-align-centered',
														),
														'Left'=> array(
															__('Left Top',  	'avia_framework' ) =>'av-tt-pos-left av-tt-align-top',
															__('Left Bottom',  	'avia_framework' ) =>'av-tt-pos-left av-tt-align-bottom',
															__('Left Centered', 'avia_framework' ) =>'av-tt-pos-left av-tt-align-centered',
														),
														'Right'=> array(
															__('Right Top',  	'avia_framework' ) =>'av-tt-pos-right av-tt-align-top',
															__('Right Bottom',  'avia_framework' ) =>'av-tt-pos-right av-tt-align-bottom',
															__('Right Centered','avia_framework' ) =>'av-tt-pos-right av-tt-align-centered',
														),
														
														
														)
									),
									
									array(
									"name" 	=> __("Tooltip Width", 'avia_framework' ),
									"desc" 	=> __("Select the width of the tooltip. Height is based on the content", 'avia_framework' ),
									"id" 	=> "tooltip_width",
									"type" 	=> "select",
									"std" 	=> "av-tt-default-width",
									"container_class" => 'av_half',
									"subtype" => array(
														__('Default',  		'avia_framework' ) =>'av-tt-default-width',
														__('Large',  		'avia_framework' ) =>'av-tt-large-width',
														__('Extra Large',  	'avia_framework' ) =>'av-tt-xlarge-width',
													),
									),
									
									array(	
									"name" 	=> __("Tooltip", 'avia_framework' ),
									"desc" 	=> __("Enter a short descriptive text that appears if the user places his mouse above the hotspot", 'avia_framework' ) ,
									"id" 	=> "content",
									"type" 	=> "tiny_mce",
									"std" 	=> "",
									),
									
									array(	
									"name" 	=> __("Tooltip Style", 'avia_framework' ),
									"desc" 	=> __("Choose the style of your tooltip", 'avia_framework' ) ,
									"id" 	=> "tooltip_style",
									"type" 	=> "select",
									"std" 	=> "main_color",
									"subtype" => array(
														__('Default',  'avia_framework' ) =>'main_color',
														__('Default with drop shadow',  'avia_framework' ) =>'main_color av-tooltip-shadow',
														__('Transparent Dark',  'avia_framework' ) =>'transparent_dark',
														
														)
											),
											
											
									array(
									"name" 	=> __("Hotspot Link?", 'avia_framework' ),
									"desc" 	=> __("Where should your hotspot link to?", 'avia_framework' ),
									"id" 	=> "link",
									"type" 	=> "linkpicker",
									"fetchTMPL"	=> true,
									"subtype" => array(
														__('No Link', 'avia_framework' ) =>'',
														__('Set Manually', 'avia_framework' ) =>'manually',
														__('Single Entry', 'avia_framework' ) =>'single',
														__('Taxonomy Overview Page',  'avia_framework' )=>'taxonomy',
														),
									"std" 	=> ""),		
									
									array(	
									"name" 	=> __("Open Link in new Window?", 'avia_framework' ),
									"desc" 	=> __("Select here if you want to open the linked page in a new window", 'avia_framework' ),
									"id" 	=> "link_target",
									"required" 	=> array('link', 'not', ''),
									"type" 	=> "select",
									"std" 	=> "",
									"subtype" => AviaHtmlHelper::linking_options()),
									
									array(
									"type" 	=> "close_div",
											'nodescription' => true
										),
									
									array(
											"type" 	=> "tab",
											"name"	=> __("Hotspot Colors",'avia_framework' ),
											'nodescription' => true
										),
									array(
									"name" 	=> __("Hotspot Color", 'avia_framework' ),
									"desc" 	=> __("Set the colors of your hotspot", 'avia_framework' ),
									"id" 	=> "hotspot_color",
									"type" 	=> "select",
									"std" 	=> "",
									"subtype" => array(
														__('Default',  		'avia_framework' ) =>'',
														__('Custom',  		'avia_framework' ) =>'custom',
													),
									),
									array(	
											"name" 	=> __("Custom Background Color", 'avia_framework' ),
											"desc" 	=> __("Select a custom background color here", 'avia_framework' ),
											"id" 	=> "custom_bg",
											"type" 	=> "colorpicker",
											"std" 	=> "#ffffff",
											"required" => array('hotspot_color','equals','custom')
										),	
										
									array(	
											"name" 	=> __("Custom Font Color", 'avia_framework' ),
											"desc" 	=> __("Select a custom font color here", 'avia_framework' ),
											"id" 	=> "custom_font",
											"type" 	=> "colorpicker",
											"std" 	=> "#888888",
											"required" => array('hotspot_color','equals','custom')
										),
									
									array(	
											"name" 	=> __("Custom Pulse Color", 'avia_framework' ),
											"desc" 	=> __("Select a custom pulse color here", 'avia_framework' ),
											"id" 	=> "custom_pulse",
											"type" 	=> "colorpicker",
											"std" 	=> "#ffffff",
											"required" => array('hotspot_color','equals','custom')
										),
									
									
									array(
									"id" 	=> "hotspot_pos",
									"std" 	=> "",
									"type" 	=> "hidden"),
									
									array(
											"type" 	=> "close_div",
											'nodescription' => true
										),
										
									array(
											"type" 	=> "close_div",
											'nodescription' => true
										),
									
									
								),
						
						),

					array(
							"name" 	=> __("Image Fade in Animation", 'avia_framework' ),
							"desc" 	=> __("Add a small animation to the image when the user first scrolls to the image position. This is only to add some 'spice' to the site and only works in modern browsers", 'avia_framework' ),
							"id" 	=> "animation",
							"type" 	=> "select",
							"std" 	=> "no-animation",
							"subtype" => array(
												__('None',  'avia_framework' ) =>'no-animation',
												__('Simple Fade in',  'avia_framework' ) =>'fade-in',
												__('Pop up',  'avia_framework' ) =>'pop-up',
												__('Top to Bottom',  'avia_framework' ) =>'top-to-bottom',
												__('Bottom to Top',  'avia_framework' ) =>'bottom-to-top',
												__('Left to Right',  'avia_framework' ) =>'left-to-right',
												__('Right to Left',  'avia_framework' ) =>'right-to-left',
												)
							),
							
					array(
							"name" 	=> __("Hotspot Layout", 'avia_framework' ),
							"desc" 	=> __("Select the hotspot layout", 'avia_framework' ),
							"id" 	=> "hotspot_layout",
							"type" 	=> "select",
							"std" 	=> "numbered",
							"subtype" => array(
												__('Numbered Hotspot',	'avia_framework' ) 	=>'numbered',
												__('Blank Hotspot',		'avia_framework' ) 	=>'blank',
												)
							),
					
					array(
							"name" 	=> __("Show Tooltips", 'avia_framework' ),
							"desc" 	=> __("Select when to display the tooltips", 'avia_framework' ),
							"id" 	=> "hotspot_tooltip_display",
							"type" 	=> "select",
							"std" 	=> "",
							"subtype" => array(
												__('On Mouse Hover', 'avia_framework' ) =>'',
												__('Always',		 'avia_framework' ) =>'av-permanent-tooltip',
												)
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
									"name" 	=> __("Hotspot on mobile devices", 'avia_framework' ),
									"desc" 	=> __("Check if you always want to show the tooltips on mobile phones below the image. Recommended if your tooltips contain a lot of text", 'avia_framework' ) ,
									"id" 	=> "hotspot_mobile",
									"std" 	=> "true",
									"type" 	=> "checkbox"),
									
									
									
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
				$img = "";
				$template = $this->update_template("src", "<img src='{{src}}' alt=''/>");
				
				if(!empty($params['args']['attachment']) && !empty($params['args']['attachment_size']))
				{
					$img = wp_get_attachment_image($params['args']['attachment'],$params['args']['attachment_size']);
				}
				else if(isset($params['args']['src']) && is_numeric($params['args']['src']))
				{
					$img = wp_get_attachment_image($params['args']['src'],'large');
				}
				else if(!empty($params['args']['src']))
				{
					$img = "<img src='".$params['args']['src']."' alt=''  />";
				}


				$params['innerHtml']  = "<div class='avia_image avia_hotspot_image avia_image_style '>";
				
				$params['innerHtml'] .= "<div class='avia_image_container avia-align-center ' {$template}>{$img}</div>";
				
				$params['innerHtml'].= "<div class='avia-flex-element'>"; 
				
				$params['innerHtml'].= 		__('This element will stretch across the whole screen by default.','avia_framework')."<br/>";
				$params['innerHtml'].= 		__('If you put it inside a color section or column it will only take up the available space','avia_framework');
				$params['innerHtml'].= "	<div class='avia-flex-element-2nd'>".__('Currently:','avia_framework');
				$params['innerHtml'].= "	<span class='avia-flex-element-stretched'>&laquo; ".__('Stretch fullwidth','avia_framework')." &raquo;</span>";
				$params['innerHtml'].= "	<span class='avia-flex-element-content'>| ".__('Adjust to content width','avia_framework')." |</span>";
				$params['innerHtml'].= "	</div>";
				$params['innerHtml'].= "	<span class='av_hotspot_image_caption button button-primary button-large'>".__('Image with Hotspots - Click to insert image and hotspots','avia_framework')."</span>";
				
				$params['innerHtml'].= "</div>";
				
				
				
				$params['innerHtml'] .= "</div>";
				$params['class'] = "";

				return $params;
			}
			
			/**
			 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
			 * Works in the same way as Editor Element
			 * @param array $params this array holds the default values for $content and $args. 
			 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
			 */
			function editor_sub_element($params)
			{
				$params['innerHtml']  = "";
				$params['innerHtml'] .= "<div class='avia_title_container' data-hotspot_pos='".$params['args']['hotspot_pos']."'>".__("Hotspot", 'avia_framework' )." </div>";

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
				$output 	= "";
				$class  	= "";
				$alt 		= "";
				$title 		= "";
				$src   		= "";
				$markup 	= avia_markup_helper(array('context' => 'image','echo'=>false, 'custom_markup'=>$meta['custom_markup']));
				$markup_url = avia_markup_helper(array('context' => 'image_url','echo'=>false, 'custom_markup'=>$meta['custom_markup']));
				$hotspots 	= ShortcodeHelper::shortcode2array($content, 1);
				
				
				extract(AviaHelper::av_mobile_sizes($atts)); //return $av_font_classes, $av_title_font_classes and $av_display_classes 

				extract(shortcode_atts(array('animation'=>'no-animation', 'attachment'=>'', 'attachment_size' =>'', 'hotspot_layout'=>'numbered', 'hotspot_mobile'=>'', 'hotspot_tooltip_display' => ''), $atts, $this->config['shortcode']));
				
				$img_h = "";
				$img_w = "";
				
				if( ! empty( $attachment ) )
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
						
						$alt = get_post_meta($attachment_entry->ID, '_wp_attachment_image_alt', true);
	                	$alt = !empty($alt) ? esc_attr($alt) : '';
	                	$title = trim($attachment_entry->post_title) ? esc_attr($attachment_entry->post_title) : "";
	                	
	                	if(!empty($attachment_size))
						{
							$src = wp_get_attachment_image_src($attachment_entry->ID, $attachment_size);
							$img_h= !empty($src[2]) ? $src[2] : "";
							$img_w= !empty($src[1]) ? $src[1] : "";
							$src  = !empty($src[0]) ? $src[0] : "";
						}
					}
				}
				
				
				//no src? return
				if(!empty($src))
				{
					if(!ShortcodeHelper::is_top_level()) $meta['el_class'] .= " av-non-fullwidth-hotspot-image";
					
					$hotspot_html 	= "";
					$tooltip_html 	= "";
					$counter 		= 1;
					
					foreach($hotspots as $hotspot)
					{ 
						if(!empty($hotspot_mobile)) $tooltip_html .= $this->add_fallback_tooltip($hotspot, $counter);
						$extraClass  = !empty($hotspot_mobile) ? " av-mobile-fallback-active " : "";
						$extraClass .= !empty($hotspot_tooltip_display) ? " {$hotspot_tooltip_display}-single " : "";
						
						$hotspot_html .= $this->add_hotspot($hotspot, $counter, $extraClass);
						$counter ++; 
					}
					
					//some custom classes
					$class .= $animation == "no-animation" ? "" :" avia_animated_image avia_animate_when_almost_visible ".$animation;
					$class .= " av-hotspot-".$hotspot_layout;
					$class .= !empty($hotspot_mobile) ? " av-mobile-fallback-active " : "";
					$class .= " ".$hotspot_tooltip_display;
					
					
					$hw = "";
					if(!empty($img_h)) $hw .= 'height="'.$img_h.'"';
					if(!empty($img_w)) $hw .= 'width="'.$img_w.'"';
					
					
	                $output .= "<div class='av-hotspot-image-container avia_animate_when_almost_visible {$av_display_classes} {$class} ".$meta['el_class']." ' $markup >";
					$output .= 		"<div class='av-hotspot-container'>";
					$output .= 			"<div class='av-hotspot-container-inner-cell'>";
					$output .= 				"<div class='av-hotspot-container-inner-wrap'>";
					$output .= 					$hotspot_html;
					$output .= 					"<img class='avia_image ' src='{$src}' alt='{$alt}' title='{$title}'  {$hw} $markup_url />";
					$output .= 				"</div>";
					$output .= 			"</div>";
					$output .= 		"</div>";
					$output .=		$tooltip_html;
					$output .= "</div>";				
				}
				
				
				
				
				if(!ShortcodeHelper::is_top_level()) return $output;
				
				$skipSecond = false;
				$params['class'] = "main_color av-fullwidth-hotspots ".$meta['el_class']." {$av_display_classes}";
				$params['open_structure'] = false;
				$params['id'] = !empty($atts['id']) ? AviaHelper::save_string($atts['id'],'-') : "";
				$params['custom_markup'] = $meta['custom_markup'];
				
				//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
				if($meta['index'] == 0) $params['close'] = false;
				if(!empty($meta['siblings']['prev']['tag']) && in_array($meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section )) $params['close'] = false;
				
				$image = $output;
				
				$output  =  avia_new_section($params);
				$output .= $image;
				$output .= "</div>"; //close section
				
				
				//if the next tag is a section dont create a new section from this shortcode
				if(!empty($meta['siblings']['next']['tag']) && in_array($meta['siblings']['next']['tag'], AviaBuilder::$full_el ))
				{
				    $skipSecond = true;
				}

				//if there is no next element dont create a new section.
				if(empty($meta['siblings']['next']['tag']))
				{
				    $skipSecond = true;
				}
				
				if(empty($skipSecond)) {
				
				$output .= avia_new_section(array('close'=>false, 'id' => "after_image_hotspots"));
				
				}
				
				return $output;
			}
			
			function add_hotspot($hotspot, $counter, $extraClass = "")
			{
				extract(shortcode_atts(array('tooltip_width' => 'av-tt-default-width', 'tooltip_pos'=>'av-tt-pos-above av-tt-align-left', 'hotspot_pos'=>'50,50', 'output'=>'', 'hotspot_color'=>'', 'custom_bg'=>'', 'custom_font'=>'', 'custom_pulse'=>'', 'tooltip_style'=>'main_color', 'link' => '', 'link_target'=>''), $hotspot['attr']));
				$content = ShortcodeHelper::avia_remove_autop($hotspot['content']);
				
				$tags = array('div', 'div');
				if(!empty($link)) 
				{
					$blank  = strpos($link_target, '_blank') !== false ? ' target="_blank" ' : "";
					$blank .= strpos($link_target, 'nofollow') !== false ? ' rel="nofollow" ' : "";
					
					$link = aviaHelper::get_url($link, false);
					$tags = array("a href={$link} {$blank}", 'a');
				}
				
				if(empty($hotspot_pos)) $hotspot_pos = '50,50';
				
				$layout 		= explode(' ', $tooltip_pos);
				$hotspot_pos 	= explode(',', $hotspot_pos);
				$top 			= $hotspot_pos[0];
				$left			= $hotspot_pos[1];
				$position		= $layout[0];
				$align			= isset($layout[1]) ? str_replace('av-tt-align-', '', $layout[1]) : "centered";
				$pos_string 	= "top: {$top}%; left: {$left}%; ";
				$data_pos		= "";
				
				if(strpos($position, 'above') !== false) $data_pos 	= "top";
				if(strpos($position, 'below') !== false) $data_pos 	= "bottom";
				if(strpos($position, 'left') !== false) $data_pos 	= "left";
				if(strpos($position, 'right') !== false) $data_pos 	= "right";
				
				
				if($hotspot_color == "custom")
				{
					if($custom_bg) $custom_bg = "background-color: {$custom_bg};";
					if($custom_font) $custom_font = "color: {$custom_font};";
					if($custom_pulse) $custom_pulse = "style='background-color:{$custom_pulse};'";
				}
				else
				{
					$custom_bg = $custom_font = $custom_pulse = "";
				}
				
				
				$output .= "<div class='av-image-hotspot' data-avia-tooltip-position='{$data_pos}' data-avia-tooltip-alignment='{$align}' data-avia-tooltip-class='{$tooltip_width} {$tooltip_pos} {$extraClass} {$tooltip_style} av-tt-hotspot' data-avia-tooltip='".esc_attr(ShortcodeHelper::avia_apply_autop($content))."' style='{$pos_string}'>";
				$output .= "<".$tags[0]." class='av-image-hotspot_inner' style='{$custom_bg} {$custom_font}'>{$counter}</".$tags[1].">";
				$output .= "<div class='av-image-hotspot-pulse' {$custom_pulse}></div>";
				$output .= "</div>";
				
				
				return $output;
			}
			
			function add_fallback_tooltip($hotspot, $counter)
			{
				$content = $hotspot['content'];
				
				if(empty($content)) return;
				
				$output  = "";
				$output .= "<div class='av-hotspot-fallback-tooltip'>";
				$output .= "<div class='av-hotspot-fallback-tooltip-count'>";
				$output .= $counter;
				$output .= "<div class='avia-arrow'></div></div>";
				$output .= "<div class='av-hotspot-fallback-tooltip-inner clearfix'>";
				$output .= ShortcodeHelper::avia_apply_autop($content);
				$output .= "</div>";
				$output .= "</div>";
				
				return $output;
			}


	}
}








