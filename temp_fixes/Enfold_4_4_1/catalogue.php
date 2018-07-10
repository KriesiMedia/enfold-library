<?php
/**
 * Catalogue
 * 
 * Creates a pricing list
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( !class_exists( 'avia_sc_catalogue' ) )
{
	class avia_sc_catalogue extends aviaShortcodeTemplate
	{
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['self_closing']	=	'no';
				
				$this->config['name']		= __('Catalogue', 'avia_framework' );
				$this->config['tab']		= __('Content Elements', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-catalogue.png";
				$this->config['order']		= 20;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_catalogue';
				$this->config['shortcode_nested'] = array('av_catalogue_item');
				$this->config['tooltip'] 	= __('Creates a pricing list', 'avia_framework' );
				$this->config['preview'] 	= true;
				$this->config['disabling_allowed'] = true;

			}
			
			
			function extra_assets()
			{
				//load css
				wp_enqueue_style( 'avia-module-catalogue' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/catalogue/catalogue.css' , array('avia-layout'), false );
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
							"name" => __("Add/Edit List items", 'avia_framework' ),
							"desc" => __("Here you can add, remove and edit the items of your item list.", 'avia_framework' ),
							"type" 			=> "modal_group",
							"id" 			=> "content",
							"modal_title" 	=> __("Edit List Item", 'avia_framework' ),
							"std"			=> array(),
							'subelements' 	=> array(

									array(
									"name" 	=> __("List Item Title", 'avia_framework' ),
									"desc" 	=> __("Enter the list item title here", 'avia_framework' ) ,
									"id" 	=> "title",
									"std" 	=> "List Title",
									"type" 	=> "input"),
									
									array(
									"name" 	=> __("List Item Description", 'avia_framework' ),
									"desc" 	=> __("Enter the item description here", 'avia_framework' ) ,
									"id" 	=> "content",
									"std" 	=> "",
									"type" 	=> "tiny_mce"),
									
									array(
									"name" 	=> __("Pricing", 'avia_framework' ),
									"desc" 	=> __("Enter the price for the item here. Eg:", 'avia_framework' )." 34$, 55.5€, £12" ,
									"id" 	=> "price",
									"std" 	=> "",
									"type" 	=> "input"),
									
									array(	
									"name" 	=> __("Thumbnail Image",'avia_framework' ),
									"desc" 	=> __("Either upload a new, or choose an existing image from your media library",'avia_framework' ),
									"id" 	=> "id",
									"fetch" => "id",
									"type" 	=> "image",
									"title" => __("Change Image",'avia_framework' ),
									"button" => __("Change Image",'avia_framework' ),
									"std" 	=> ""),
									
									array(
									"name" 	=> __("Item Link?", 'avia_framework' ),
									"desc" 	=> __("Where should your item link to?", 'avia_framework' ),
									"id" 	=> "link",
									"type" 	=> "linkpicker",
									"fetchTMPL"	=> true,
									"subtype" => array(
														__('No Link', 'avia_framework' ) =>'',
														__('Open bigger version of Thumbnail Image in lightbox (image needs to be set)', 'avia_framework' ) =>'lightbox',
														__('Set Manually', 'avia_framework' ) =>'manually',
														__('Single Entry', 'avia_framework' ) =>'single',
														__('Taxonomy Overview Page',  'avia_framework' )=>'taxonomy',
														),
									"std" 	=> ""),

				                    array(
				                        "name" 	=> __("Open new tab/window", 'avia_framework' ),
				                        "desc" 	=> __("Do you want to open the link url in a new tab/window?", 'avia_framework' ),
				                        "id" 	=> "target",
				                        "type" 	=> "select",
				                        "std"	=> "",
				                        "required"=> array('link','not_empty_and','lightbox'),
				                        "subtype" => AviaHtmlHelper::linking_options()
				                        ),
				                        
				                    array(
				                        "name" 	=> __("Disable Item?", 'avia_framework' ),
				                        "desc" 	=> __("Temporarily disable and hide the item without deleting it, if its out of stock", 'avia_framework' ),
				                        "id" 	=> "disabled",
				                        "type" 	=> "checkbox",
				                        "std"	=> "",
				                        ),
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
			 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
			 * Works in the same way as Editor Element
			 * @param array $params this array holds the default values for $content and $args.
			 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
			 */
			function editor_sub_element($params)
			{
				$template = $this->update_template("title", __("Item", 'avia_framework' ). ": {{title}}");


				$params['innerHtml']  = "";
				$params['innerHtml'] .= "<div class='avia_title_container'>";
				$params['innerHtml'] .=	"<div ".$this->class_by_arguments('disabled' ,$params['args']).">";
				$params['innerHtml'] .= "<span {$template} >".__("Item", 'avia_framework' ).": ".$params['args']['title']."</span></div>";
				$params['innerHtml'] .= "</div>";

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
				extract(shortcode_atts(array('title'=>''), $atts, $this->config['shortcode']));

				$output	 = "";
				$output .= "<div class='av-catalogue-container {$av_display_classes} ".$meta['el_class']."'>";
				
				$output .= "<ul class='av-catalogue-list'>";
				$output .= ShortcodeHelper::avia_remove_autop( $content, true );
				$output .= "</ul>";
				$output .= "</div>";


				return $output;
			}

			function av_catalogue_item($atts, $content = "", $shortcodename = "")
			{
				extract(shortcode_atts(array('title'=>'', 'price'=>'', 'link'=>'', 'target'=>'', 'disabled'=>'', 'id' => ''), $atts, $this->config['shortcode_nested'][0]));
				
				if($disabled) return;
				
				$item_markup = array("open"=>"div", "close" => "div");
				$image 		 = "";
				$blank		 = "";
				
				if($link)
				{
					if($link == 'lightbox' && $id)
					{
						$link 	= aviaHelper::get_url($link, $id);
					}
					else
					{
						$link 	= aviaHelper::get_url($link);
						$blank = (strpos($target, '_blank') !== false || $target == 'yes') ? ' target="_blank" ' : "";
						$blank .= strpos($target, 'nofollow') !== false ? ' rel="nofollow" ' : "";
					}
					
					$item_markup = array("open"=>"a href='{$link}' {$blank}", "close" => "a");
				}
				
				if( ! empty( $id ) )
				{
					/**
					 * Allows e.g. WPML to reroute to translated image
					 */
					$posts = get_posts( array(
											'include'			=> $id,
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
	                	$img_title = trim($attachment_entry->post_title) ? esc_attr($attachment_entry->post_title) : "";
		                $src = wp_get_attachment_image_src($attachment_entry->ID, 'square');
						$src = !empty($src[0]) ? $src[0] : "";
						
						$image = "<img src='{$src}' title='{$img_title}' alt='{$alt}' class='av-catalogue-image' />";
						
					}
				}
				
				
				
               	$output = "";
				$output .= "<li>";
				$output .= "<".$item_markup['open']." class='av-catalogue-item'>";
				$output .=		$image;
				$output .= 		"<div class='av-catalogue-item-inner'>";
				$output .= 			"<div class='av-catalogue-title-container'><div class='av-catalogue-title'>{$title}</div><div class='av-catalogue-price'>{$price}</div></div>";
				$output .= 			"<div class='av-catalogue-content'>".do_shortcode($content)."</div>";
				$output .= 		"</div>";
				$output .= "</".$item_markup['close'].">";
				$output .= "</li>";
				return $output;
			}


	}
}
