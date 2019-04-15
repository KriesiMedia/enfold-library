<?php
/**
 * Fullwidth Sub Menu
 * 
 * Shortcode that allows to display a fullwidth Sub Menu
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_sc_submenu' ) ) 
{
	class avia_sc_submenu extends aviaShortcodeTemplate
	{
			/**
			 *
			 * @var int 
			 */
			static protected $count = 0;
			
			/**
			 *
			 * @var int 
			 */
			static protected $custom_items = 0;
	
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['self_closing']	=	'no';
				
				$this->config['name']			= __('Fullwidth Sub Menu', 'avia_framework' );
				$this->config['tab']			= __('Content Elements', 'avia_framework' );
				$this->config['icon']			= AviaBuilder::$path['imagesURL']."sc-submenu.png";
				$this->config['order']			= 30;
				$this->config['target']			= 'avia-target-insert';
				$this->config['shortcode'] 		= 'av_submenu';
				$this->config['shortcode_nested'] = array('av_submenu_item');
				$this->config['tooltip'] 	    = __('Display a sub menu', 'avia_framework' );
				$this->config['tinyMCE'] 		= array('disable' => "true");
				$this->config['drag-level'] 	= 1;
				$this->config['preview'] 		= false;
				$this->config['disabling_allowed'] = true;
			}
			
			function extra_assets()
			{
				//load css
				wp_enqueue_style( 'avia-module-menu' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/menu/menu.css' , array('avia-layout'), false );
				
					//load js
				wp_enqueue_script( 'avia-module-menu' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/menu/menu.js' , array('avia-shortcodes'), false, TRUE );
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
				global $avia_config;
				
				$menus = array();
				if(!empty($_POST) && !empty($_POST['action']) && $_POST['action'] == "avia_ajax_av_submenu")
				{
					$menus = AviaHelper::list_menus();
				}
				
				$this->elements = array(
					
					array(	
						"name" 	=> __("Which kind of menu do you want to display",'avia_framework' ),
						"name" 	=> __("Either use an existing menu, built in Appearance -> Menus or create a simple custom menu here",'avia_framework' ),
						"id" 	=> "which_menu",
						"type" 	=> "select",
						"std" 	=> "center",
						"subtype" => array(   __('Use existing menu','avia_framework' ) =>'',
						                      __('Build simple custom menu','avia_framework' ) =>'custom',
						                      )
				    ),
					
					
					array(	
							"name" 	=> __("Select menu to display", 'avia_framework' ),
							"desc" 	=> __("You can create new menus in ", 'avia_framework' )."<a target='_blank' href='".admin_url('nav-menus.php?action=edit&menu=0')."'>".__('Appearance -> Menus', 'avia_framework' )."</a><br/>".__("Please note that Mega Menus are not supported for this element ", 'avia_framework' ),
							"id" 	=> "menu",
							"type" 	=> "select",
							"std" 	=> "",
							"required" 	=> array('which_menu', 'not', 'custom'),
							"subtype" =>  $menus		
							),
							
					array(
							"name" => __("Add/Edit submenu item text", 'avia_framework' ),
							"desc" => __("Here you can add, remove and edit the submenu item text", 'avia_framework' ),
							"type" 			=> "modal_group",
							"id" 			=> "content",
							"required" 		=> array('which_menu', 'equals', 'custom'),
							"modal_title" 	=> __("Edit Text Element", 'avia_framework' ),
							"std"			=> array(

													array('title'=>__('Menu Item 1', 'avia_framework' )),
													array('title'=>__('Menu Item 2', 'avia_framework' )),

													),


							'subelements' 	=> array(

									array(
									"name" 	=> __("Menu Text", 'avia_framework' ),
									"desc" 	=> __("Enter the menu text here", 'avia_framework' ) ,
									"id" 	=> "title",
									"std" 	=> "",
									"type" 	=> "input"),


                                array(
                                    "name" 	=> __("Text Link?", 'avia_framework' ),
                                    "desc" 	=> __("Apply  a link to the menu text?", 'avia_framework' ),
                                    "id" 	=> "link",
                                    "type" 	=> "linkpicker",
                                    "fetchTMPL"	=> true,
                                    "std"	=> "",
                                    "subtype" => array(
                                        __('Set Manually', 'avia_framework' ) =>'manually',
                                        __('Single Entry', 'avia_framework' ) =>'single',
                                        __('Taxonomy Overview Page',  'avia_framework' )=>'taxonomy',
                                    ),
                                    ),

                                array(
                                    "name" 	=> __("Open in new window", 'avia_framework' ),
                                    "desc" 	=> __("Do you want to open the link in a new window", 'avia_framework' ),
                                    "id" 	=> "linktarget",
                                    "required" 	=> array('link', 'not', ''),
                                    "type" 	=> "select",
                                    "std" 	=> "no",
									"subtype" => AviaHtmlHelper::linking_options()),
									
								array(
                                    "name" 	=> __("Style", 'avia_framework' ),
                                    "desc" 	=> __("Select the styling of your menu item", 'avia_framework' ),
                                    "id" 	=> "button_style",
                                    "type" 	=> "select",
                                    "std" 	=> "",
									"subtype" => array(
                                        __('Default Style', 'avia_framework' ) 	=>'',
                                        __('Button Style (Colored)', 'avia_framework' ) 	=>'av-menu-button av-menu-button-colored',
                                        __('Button Style (Bordered)',  'avia_framework' )	=>'av-menu-button av-menu-button-bordered',
                                    ),
							
							),),	


						
					),
					
                    array(	
						"name" 	=> __("Menu Position",'avia_framework' ),
						"name" 	=> __("Aligns the menu either to the left, the right or centers it",'avia_framework' ),
						"id" 	=> "position",
						"type" 	=> "select",
						"std" 	=> "center",
						"subtype" => array(   __('Left','avia_framework' )       =>'left',
						                      __('Center','avia_framework' )     =>'center',
						                      __('Right','avia_framework' )      =>'right', 
						                      )
				    ),
				    
				     array(
						"name" 	=> __("Menu Colors",'avia_framework' ),
						"id" 	=> "color",
						"desc"  => __("The menu will use the color scheme you select. Color schemes are defined on your styling page",'avia_framework' ) .
						           '<br/><a target="_blank" href="'.admin_url('admin.php?page=avia#goto_styling').'">'.__("(Show Styling Page)",'avia_framework' )."</a>",
						"type" 	=> "select",
						"std" 	=> "main_color",
						"subtype" =>  array_flip($avia_config['color_sets'])
				    ),
				    
				    array(	
						"name" 	=> __("Sticky Submenu", 'avia_framework' ),
						"desc" 	=> __("If checked the menu will stick at the top of the page once it touches it.", 'avia_framework' ),
						"id" 	=> "sticky",
						"std" 	=> "true",
						"type" 	=> "checkbox"),
						
					array(	
						"name" 	=> __("Mobile Menu Display",'avia_framework' ),
						"desc" 	=> __("How do you want to display the menu on mobile devices",'avia_framework' ),
						"id" 	=> "mobile",
						"type" 	=> "select",
						"std" 	=> "disabled",
						"subtype" => array(   __('Display full menu (works best if you only got a few menu items)','avia_framework' )       			=>'disabled',
						                      __('Display a button to open menu (works best for menus with a lot of menu items)','avia_framework' )     =>'active',
						                      )
						),
					
					array(
							'name'		=> __( 'Screenwidth for burger menu button', 'avia_framework' ),
							'desc'		=> __( 'Select the maximum screenwidth to use a burger menu button instead of full menu. Above that the full menu is displayed', 'avia_framework' ),
							'id'		=> 'mobile_switch',
							'type'		=> 'select',
							'std'		=> 'av-switch-768',
							'required' 	=> array( 'mobile', 'equals', 'active' ),
							'subtype'	=> array(
													__( 'Switch at 990px (tablet landscape)','avia_framework' ) => 'av-switch-990',
													__( 'Switch at 768px (tablet portrait)','avia_framework' ) => 'av-switch-768',
													__( 'Switch at 480px (smartphone portrait)','avia_framework' ) => 'av-switch-480',
											)
						),
				    
				    array(	
						"name" 	=> __("Hide Mobile Menu Submenu Items", 'avia_framework'),
						"desc" 	=> __("By default all menu items of the mobile menu are visible. If you activate this option they will be hidden and a user needs to click on the parent menu item to display the submenus", 'avia_framework'),
						"id" 	=> "mobile_submenu",
						"required" 	=> array('mobile', 'equals', 'active'),
						"type" 	=> "checkbox",
						"std" 	=> ""
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
				$template = $this->update_template("title", "{{title}}");

				$params['innerHtml']  = "";
				$params['innerHtml'] .= "<div class='avia_title_container'>";
				$params['innerHtml'] .= "<span {$template} >".$params['args']['title']."</span></div>";

				return $params;
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
				$term_args = array( 
								'taxonomy'		=> 'nav_menu',
								'hide_empty'	=> false
							);
				
				$menus = AviaHelper::get_terms( $term_args );
			
				$params['innerHtml'] = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
				$params['innerHtml'].= "<div class='avia-element-label'>".$this->config['name']."</div>";
				return $params;
			}
			
			
			/**
			 * Returns false by default.
			 * Override in a child class if you need to change this behaviour.
			 * 
			 * @since 4.2.1
			 * @param string $shortcode
			 * @return boolean
			 */
			public function is_nested_self_closing( $shortcode )
			{
				if( in_array( $shortcode, $this->config['shortcode_nested'] ) )
				{
					return true;
				}

				return false;
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
				$atts = shortcode_atts( array(
										'style'			=> '',
										'menu'			=> '',
										'position'	 	=> 'center',
										'sticky'		=> '',
										'color'			=> 'main_color',
										'mobile'		=> 'disabled',
										'mobile_switch'	=> 'av-switch-768',
										'mobile_submenu'=> '',
										'which_menu'	=> ''
				
				), $atts, $this->config['shortcode'] );
				
				if( 'disabled' == $atts['mobile'] )
				{
					$atts['mobile_switch'] = '';
				}
				else if( empty( $atts['mobile_switch'] ) )
				{
					$atts['mobile_switch'] = 'av-switch-768';
				}
				
				
				
				extract( $atts );
				
				$output  	= "";
				$sticky_div = "";
				avia_sc_submenu::$count++;
				avia_sc_submenu::$custom_items = 0;
				
				
				$params['class'] = "av-submenu-container {$color} ".$meta['el_class'];
				$params['open_structure'] = false; 
				$params['id'] = "sub_menu".avia_sc_submenu::$count;
				$params['custom_markup'] = $meta['custom_markup'];
				$params['style'] = "style='z-index:".(avia_sc_submenu::$count + 300)."'";
				
				if($sticky && $sticky != "disabled") 
				{
					$params['class'] .= " av-sticky-submenu";
					$params['before_new'] = "<div class='clear'></div>";
					$sticky_div = "<div class='sticky_placeholder'></div>";
				}
				
				$params['class'] .= ' ' . $mobile_switch;
				
				//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
				if(isset($meta['index']) && $meta['index'] == 0) $params['close'] = false;
				if(!empty($meta['siblings']['prev']['tag']) && in_array($meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section )) $params['close'] = false;
				
				if(isset($meta['index']) && $meta['index'] != 0) $params['class'] .= " submenu-not-first";
				
				
				if($which_menu == "custom")
				{
					$element = "";
					$custom_menu = ShortcodeHelper::avia_remove_autop( $content, true );
					if(!empty($custom_menu))
					{
						$element .= "<ul id='av-custom-submenu-".avia_sc_submenu::$count."' class='av-subnav-menu av-submenu-pos-{$position}'>";
						$element .= $custom_menu;
						$element .= "</ul>";
					}
				}
				else
				{
					$element = wp_nav_menu(
						array(
		                	'menu' 			=> wp_get_nav_menu_object( $menu ),
		                    'menu_class' 	=>"av-subnav-menu av-submenu-pos-{$position}",
		                    'fallback_cb' 	=> '',
		                    'container'		=>false,
		                    'echo' 			=>false,
		                    'walker' 		=> new avia_responsive_mega_menu(array('megamenu'=>'disabled'))
		                )
					);
				}
				
				
				$submenu_hidden = ""; 
				$mobile_button = $mobile == "active" ? "<a href='#' class='mobile_menu_toggle' ".av_icon_string('mobile_menu')."><span class='av-current-placeholder'>".__('Menu', 'avia_framework')."</span></a>" : "";
				if(!empty($mobile_button) && !empty($mobile_submenu) && $mobile_submenu != "disabled")
				{
					$submenu_hidden = "av-submenu-hidden";
				}
				
				// if(!ShortcodeHelper::is_top_level()) return $element;
				$output .=  avia_new_section($params);
				$output .= "<div class='container av-menu-mobile-{$mobile} {$submenu_hidden}'>{$mobile_button}".$element."</div>";
				$output .= avia_section_after_element_content( $meta , 'after_submenu', false, $sticky_div);
				return $output;

			}
			
			function av_submenu_item($atts, $content = "", $shortcodename = "", $meta = "")
			{
				/**
				 * Fixes a problem when 3-rd party plugins call nested shortcodes without executing main shortcode  (like YOAST in wpseo-filter-shortcodes)
				 */
				if( avia_sc_submenu::$count == 0 )
				{
					return '';
				}
				
				$output = "";
				$atts = shortcode_atts(
                array(	
                	'title' 		=> '',
                	'link' 			=> '',
                	'linktarget' 	=> '',
                	'button_style' 	=> '',
                ), 
                $atts, 'av_submenu_item');
                
                extract($atts);
                
                if(!empty($title))
                {
	                avia_sc_submenu::$custom_items++;
	                $link = AviaHelper::get_url($link);
					$blank = (strpos($linktarget, '_blank') !== false || $linktarget == 'yes') ? ' target="_blank" ' : "";
					$blank .= strpos($linktarget, 'nofollow') !== false ? ' rel="nofollow" ' : "";
	                
	                $output .= "<li class='menu-item menu-item-top-level {$button_style} menu-item-top-level-".avia_sc_submenu::$custom_items."'>";
	                $output .= "<a href='{$link}' {$blank}><span class='avia-bullet'></span>";
	                $output .= "<span class='avia-menu-text'>".$title."</span>";
	                //$output .= "<span class='avia-menu-fx'><span class='avia-arrow-wrap'><span class='avia-arrow'></span></span></span>";
	                $output .= "</a>";
	                $output .= "</li>";
	                
	                
                }
                
                return $output;
                
			}
			
	}
}



