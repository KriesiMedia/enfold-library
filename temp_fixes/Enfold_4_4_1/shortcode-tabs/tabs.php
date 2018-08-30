<?php
/**
 * Tabs
 * 
 * Creates a tabbed content area
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( !class_exists( 'avia_sc_tab' ) )
{
    class avia_sc_tab extends aviaShortcodeTemplate
    {
        static $tab_id = 1;
        static $counter = 1;
        static $initial = 1;

        /**
         * Create the config array for the shortcode button
         */
        function shortcode_insert_button()
        {
			$this->config['self_closing']	=	'no';
			
            $this->config['name']		= __('Tabs', 'avia_framework' );
            $this->config['tab']		= __('Content Elements', 'avia_framework' );
            $this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-tabs.png";
            $this->config['order']		= 75;
            $this->config['target']		= 'avia-target-insert';
            $this->config['shortcode'] 	= 'av_tab_container';
            $this->config['shortcode_nested'] = array('av_tab');
            $this->config['tooltip'] 	= __('Creates a tabbed content area', 'avia_framework' );
            $this->config['disabling_allowed'] = true; 
        }
		
		
		function admin_assets()
		{
			$ver = AviaBuilder::VERSION;
            wp_enqueue_script('avia_tab_toggle_js' , AviaBuilder::$path['assetsURL'].'js/avia-tab-toggle.js' , array('avia_modal_js'), $ver, TRUE );
		}

        function extra_assets()
        {
            //load css
			wp_enqueue_style( 'avia-module-tabs' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/tabs/tabs.css' , array('avia-layout'), false );
				
			//load js
			wp_enqueue_script( 'avia-module-tabs' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/tabs/tabs.js' , array('avia-shortcodes'), false, TRUE );
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
                    "name" 	=> __("Tab Position", 'avia_framework' ),
                    "desc" 	=> __("Where should the tabs be displayed", 'avia_framework' ),
                    "id" 	=> "position",
                    "type" 	=> "select",
                    "std" 	=> "top_tab",
                    'container_class' =>"avia-element-fullwidth",
                    "target"  => array('#aviaTBcontent-form-container', 'class'),
                    "subtype" => array( __('Display tabs at the top', 'avia_framework' ) =>'top_tab',
                        __("Display Tabs on the left", 'avia_framework' ) => 'sidebar_tab sidebar_tab_left',
                        __("Display Tabs on the right", 'avia_framework' ) => 'sidebar_tab sidebar_tab_right')
                ),


                array(
                    "name" 	=> __("Boxed Tabs", 'avia_framework' ),
                    "desc" 	=> __("Do you want to display a border around your tabs or without border", 'avia_framework' ),
                    "id" 	=> "boxed",
                    "type" 	=> "select",
                    "std" 	=> "no",
                    "required" => array('position','contains','sidebar_tab'),
                    "subtype" => array(
                        __('With border',  'avia_framework' ) =>'border_tabs',
                        __('Without border',  'avia_framework' ) =>'noborder_tabs')),


                array(
                    "name" => __("Add/Edit Tabs", 'avia_framework' ),
                    "desc" => __("Here you can add, remove and edit the Tabs you want to display.", 'avia_framework' ),
                    "type" 			=> "modal_group",
                    "id" 			=> "content",
                    'container_class' =>"avia-element-fullwidth avia-tab-container",
                    "modal_title" 	=> __("Edit Form Element", 'avia_framework' ),
                    "std"			=> array(

                        array('title'=>__('Tab 1', 'avia_framework' )),
                        array('title'=>__('Tab 2', 'avia_framework' )),

                    ),


                    'subelements' 	=> array(

                        array(
                            "name" 	=> __("Tab Title", 'avia_framework' ),
                            "desc" 	=> __("Enter the tab title here (Better keep it short)", 'avia_framework' ) ,
                            "id" 	=> "title",
                            "std" 	=> "Tab Title",
                            "type" 	=> "input"),



                        array(
                            "name" 	=> __("Tab Icon", 'avia_framework' ),
                            "desc" 	=> __("Should an icon be displayed at the left side of the tab title?", 'avia_framework' ),
                            "id" 	=> "icon_select",
                            "type" 	=> "select",
                            "std" 	=> "no",
                            "subtype" => array(
                                __('No Icon',  'avia_framework' ) =>'no',
                                __('Yes, display Icon',  'avia_framework' ) =>'yes')),

                        array(
                            "name" 	=> __("Tab Icon",'avia_framework' ),
                            "desc" 	=> __("Select an icon for your tab title below",'avia_framework' ),
                            "id" 	=> "icon",
                            "type" 	=> "iconfont",
                            "std" 	=> "",
                            "required" => array('icon_select','equals','yes')
                        ),


                        array(
                            "name" 	=> __("Tab Content", 'avia_framework' ),
                            "desc" 	=> __("Enter some content here", 'avia_framework' ) ,
                            "id" 	=> "content",
                            "type" 	=> "tiny_mce",
                            "std" 	=> __("Tab Content goes here", 'avia_framework' ),
                        ),

                    )
                ),

                array(
                    "name" 	=> __("Initial Open", 'avia_framework' ),
                    "desc" 	=> __("Enter the Number of the Tab that should be open initially.", 'avia_framework' ),
                    "id" 	=> "initial",
                    "std" 	=> "1",
                    "type" 	=> "input"),

            


           
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
			
			if(current_theme_supports('avia_template_builder_custom_tab_toogle_id'))
            {
                $this->elements[4]['subelements'][] = array(
                    "name" 	=> __("For Developers: Custom Tab ID",'avia_framework' ),
                    "desc" 	=> __("Insert a custom ID for the element here. Make sure to only use allowed characters.",'avia_framework' ),
                    "id" 	=> "custom_id",
                    "type" 	=> "input",
                    "std" 	=> "");
            }

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
            $content  = $this->update_template("content", "{{content}}");

			extract(av_backend_icon($params)); // creates $font and $display_char if the icon was passed as param "icon" and the font as "font" 
            
			
            $params['innerHtml']  = "";
            $params['innerHtml'] .= "<div class='avia_title_container'>";
            $params['innerHtml'] .= "	<span ".$this->class_by_arguments('icon_select' ,$params['args']).">";
			$params['innerHtml'] .= "		<span ".$this->class_by_arguments('font' ,$font).">";
            $params['innerHtml'] .= "			<span data-update_with='icon_fakeArg' class='avia_tab_icon' >{$display_char}</span>";
            $params['innerHtml'] .= "		</span>";
            $params['innerHtml'] .= "	</span>";
            $params['innerHtml'] .= "	<span class='avia_title_container_inner' {$template} >".$params['args']['title']."</span>";
            $params['innerHtml'] .= "</div>";

            $params['innerHtml'] .= "<div class='avia_content_container' {$content}>".stripcslashes( $params['content'] )."</div>";


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
	        
            $atts =  shortcode_atts(array('initial' => '1', 'position' => 'top_tab', 'boxed'=>'border_tabs'), $atts, $this->config['shortcode']);
            extract($atts);

            $boxed   = $position != "top_tab" ? $boxed : "";
            $output  = '<div class="tabcontainer  '.$av_display_classes.' '.$position.' '.$boxed.' '.$meta['el_class'].'">'."\n";
            $counter = 1;

            avia_sc_tab::$counter = 1;
            avia_sc_tab::$initial = $initial;

            $output .= ShortcodeHelper::avia_remove_autop($content, true);

            $output .= '</div>'."\n";

            return $output;
        }

        function av_tab($atts, $content = "", $shortcodename = "")
        {
            
            $output = $titleClass  = $contentClass = $icon = "";
            $tab_atts = shortcode_atts(array('title' => '', 'icon_select'=>'no', 'icon' =>"", 'custom_id' =>'', 'font' =>'', 'custom_markup' => ''), $atts, 'av_tab');
            
            $display_char = av_icon($tab_atts['icon'], $tab_atts['font']);
			
			$aria_content = 'aria-hidden="true"';

            if(is_numeric(avia_sc_tab::$initial) && avia_sc_tab::$counter == avia_sc_tab::$initial)
            {
                $titleClass   = "active_tab";
                $contentClass = "active_tab_content";
				$aria_content = 'aria-hidden="false"';
            }

            if(empty($tab_atts['title']))
            {
                $tab_atts['title'] = avia_sc_toggle::$counter;
            }

            if($tab_atts['icon_select'] == "yes")
            {
                $icon = "<span class='tab_icon' {$display_char}></span>";
            }

            if(empty($tab_atts['custom_id']))
            {
                $tab_atts['custom_id'] = 'tab-id-'.avia_sc_tab::$tab_id++;
            }


            $markup_tab = avia_markup_helper(array('context' => 'entry','echo'=>false, 'custom_markup'=>$tab_atts['custom_markup']));
            $markup_title = avia_markup_helper(array('context' => 'entry_title','echo'=>false, 'custom_markup'=>$tab_atts['custom_markup']));
            $markup_text = avia_markup_helper(array('context' => 'entry_content','echo'=>false, 'custom_markup'=>$tab_atts['custom_markup']));

            $output .= '<section class="av_tab_section" '.$markup_tab.'>';
            $output .= '    <div aria-controls="' . $tab_atts['custom_id'] . '" role="tab" tabindex="0" data-fake-id="#'.$tab_atts['custom_id'].'" class="tab '.$titleClass.'" '.$markup_title.'>'.$icon.$tab_atts['title'].'</div>'."\n";
            $output .= '    <div id="'.$tab_atts['custom_id'].'-container" class="tab_content '.$contentClass.'" ' . $aria_content . '>'."\n";
            $output .= '        <div class="tab_inner_content invers-color" '.$markup_text.'>'."\n";
            $output .= ShortcodeHelper::avia_apply_autop(ShortcodeHelper::avia_remove_autop($content))."\n";
            $output .= '        </div>'."\n";
            $output .= '    </div>'."\n";
            $output .= '</section>'."\n";

            avia_sc_tab::$counter ++;

            return $output;
        }




    }
}
