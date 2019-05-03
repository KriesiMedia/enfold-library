<?php
/**
 * Content Slider
 * 
 * Shortcode that display a content slider element
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( !class_exists( 'avia_sc_content_slider' ) )
{
  class avia_sc_content_slider extends aviaShortcodeTemplate
  {
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['self_closing']	=	'no';
				
				$this->config['name']			= __('Content Slider', 'avia_framework' );
				$this->config['tab']			= __('Content Elements', 'avia_framework' );
				$this->config['icon']			= AviaBuilder::$path['imagesURL']."sc-contentslider.png";
				$this->config['order']			= 83;
				$this->config['target']			= 'avia-target-insert';
				$this->config['shortcode'] 		= 'av_content_slider';
				$this->config['shortcode_nested'] = array('av_content_slide');
				$this->config['tooltip'] 	    = __('Display a content slider element', 'avia_framework' );
				$this->config['preview'] 		= false;
				$this->config['disabling_allowed'] = true;
			}
			
			function extra_assets()
			{
				//load css
				wp_enqueue_style( 'avia-module-slideshow' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/slideshow/slideshow.css' , array('avia-layout'), false );
				wp_enqueue_style( 'avia-module-postslider' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/postslider/postslider.css' , array('avia-layout'), false );
				wp_enqueue_style( 'avia-module-slideshow-contentpartner' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/contentslider/contentslider.css' , array('avia-module-slideshow'), false );
				
					//load js
				wp_enqueue_script( 'avia-module-slideshow' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/slideshow/slideshow.js' , array('avia-shortcodes'), false, TRUE );

			
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
                        "name" => __("Add/Edit Slides", 'avia_framework' ),
                        "desc" => __("Here you can add, remove and edit the slides you want to display.", 'avia_framework' ),
                        "type" 			=> "modal_group",
                        "id" 			=> "content",
                        "modal_title" 	=> __("Edit Form Element", 'avia_framework' ),
                        "std"			=> array(
                            array('title'=>__('Slide 1', 'avia_framework' ), 'tags'=>''),
                            array('title'=>__('Slide 2', 'avia_framework' ), 'tags'=>''),

                        ),


                        'subelements' 	=> array(

                            array(
                                "name" 	=> __("Slide Title", 'avia_framework' ),
                                "desc" 	=> __("Enter the slide title here (Better keep it short)", 'avia_framework' ) ,
                                "id" 	=> "title",
                                "std" 	=> "Slide Title",
                                "type" 	=> "input"),


                            array(
                                "name" 	=> __("Title Link?", 'avia_framework' ),
                                "desc" 	=> __("Where should your title link to?", 'avia_framework' ),
                                "id" 	=> "link",
                                "type" 	=> "linkpicker",
                                "fetchTMPL"	=> true,
                                "std"	=> "",
                                "subtype" => array(
                                    __('No Link', 'avia_framework' ) =>'',
                                    __('Set Manually', 'avia_framework' ) =>'manually',
                                    __('Single Entry', 'avia_framework' ) =>'single',
                                    __('Taxonomy Overview Page',  'avia_framework' )=>'taxonomy',
                                ),
                                "std" 	=> ""),

                            array(
                                "name" 	=> __("Open in new window", 'avia_framework' ),
                                "desc" 	=> __("Do you want to open the link in a new window", 'avia_framework' ),
                                "id" 	=> "linktarget",
                                "required" 	=> array('link', 'not', ''),
                                "type" 	=> "select",
                                "std" 	=> "",
                                "subtype" => AviaHtmlHelper::linking_options()),   



                            array(
                                "name" 	=> __("Slide Content", 'avia_framework' ),
                                "desc" 	=> __("Enter some content here", 'avia_framework' ) ,
                                "id" 	=> "content",
                                "type" 	=> "tiny_mce",
                                "std" 	=> __("Slide Content goes here", 'avia_framework' ) ,
                            ),

                        )
                    ),

                    array(
                        "name"  => __("Heading", 'avia_framework' ),
                        "desc"  => __("Do you want to display a heading above the images?", 'avia_framework' ),
                        "id"    => "heading",
                        "type"  => "input",
                        "std"   => "",
                    ),

                    array(
                        "name" 	=> __("Columns", 'avia_framework' ),
                        "desc" 	=> __("How many Slide columns should be displayed?", 'avia_framework' ),
                        "id" 	=> "columns",
                        "type" 	=> "select",
                        "std" 	=> "1",
                        "subtype" => array(	__('1 Columns', 'avia_framework' )=>'1',
                            __('2 Columns', 'avia_framework' )=>'2',
                            __('3 Columns', 'avia_framework' )=>'3',
                            __('4 Columns', 'avia_framework' )=>'4',
                            __('5 Columns', 'avia_framework' )=>'5',
                            __('6 Columns', 'avia_framework' )=>'6'
                        )),


                    array(
                        "name" 	=> __("Transition", 'avia_framework' ),
                        "desc" 	=> __("Choose the transition for your content slider.", 'avia_framework' ),
                        "id" 	=> "animation",
                        "type" 	=> "select",
                        "std" 	=> "slide",
                        "subtype" => array(__('Slide','avia_framework' ) =>'slide',__('Fade','avia_framework' ) =>'fade'),
                    ),

                    array(
                        "name" 	=> __("Slider controls", 'avia_framework' ),
                        "desc" 	=> __("Do you want to display slider control buttons?", 'avia_framework' ),
                        "id" 	=> "navigation",
                        "type" 	=> "select",
                        "std" 	=> "arrows",
                        "subtype" => array(
                            __('Yes, display arrow control buttons','avia_framework' ) =>'arrows',
                            __('Yes, display dot control buttons','avia_framework' ) =>'dots',
                            __('No, do not display any control buttons','avia_framework' ) =>'no'),
                    ),


					array(
						"name" 	=> __("Autorotation active?",'avia_framework' ),
						"desc" 	=> __("Check if the content slider should rotate by default",'avia_framework' ),
						"id" 	=> "autoplay",
						"type" 	=> "select",
						"std" 	=> "false",
						"subtype" => array(__('Yes','avia_framework' ) =>'true',__('No','avia_framework' ) =>'false')),

					array(
						"name" 	=> __("Slider autorotation duration",'avia_framework' ),
						"desc" 	=> __("Images will be shown the selected amount of seconds.",'avia_framework' ),
						"id" 	=> "interval",
						"type" 	=> "select",
						"std" 	=> "5",
						"subtype" =>
						array('3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','15'=>'15','20'=>'20','30'=>'30','40'=>'40','60'=>'60','100'=>'100')
						),
						
					array(
							"type" 	=> "close_div",
							'nodescription' => true
						),
					
					array(
							"type" 	=> "tab",
							"name"	=> __("Colors",'avia_framework' ),
							'nodescription' => true
						),
					
					
					array(
							"name" 	=> __("Font Colors", 'avia_framework' ),
							"desc" 	=> __("Either use the themes default colors or apply some custom ones", 'avia_framework' ),
							"id" 	=> "font_color",
							"type" 	=> "select",
							"std" 	=> "",
							"subtype" => array( __('Default', 'avia_framework' )=>'',
												__('Define Custom Colors', 'avia_framework' )=>'custom'),
					),
					
					array(	
							"name" 	=> __("Custom Font Color", 'avia_framework' ),
							"desc" 	=> __("Select a custom font color. Leave empty to use the default", 'avia_framework' ),
							"id" 	=> "color",
							"type" 	=> "colorpicker",
							"std" 	=> "",
							"container_class" => 'av_half av_half_first',
							"required" => array('font_color','equals','custom')
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
				$heading  = "";
				$template = $this->update_template("heading", " - <strong>{{heading}}</strong>");
				if(!empty($params['args']['heading'])) $heading = "- <strong>".$params['args']['heading']."</strong>";

				$params['innerHtml'] = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
				$params['innerHtml'].= "<div class='avia-element-label'>".$this->config['name']."</div>";
				$params['innerHtml'].= "<div class='avia-element-label' {$template}>".$heading."</div>";
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
                  $template = $this->update_template("title", "{{title}}");

                  $params['innerHtml']  = "";
                  $params['innerHtml'] .= "<div class='avia_title_container' {$template}>".$params['args']['title']."</div>";


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
			
				$atts = shortcode_atts(array(
                'type'          => 'slider',
				'autoplay'		=> 'false',
                'animation'     => 'fade',
				'interval'		=> 5,
                'navigation'    => 'arrows',
				'heading'		=> '',
                'columns'       => 3,
				'handle'		=> $shortcodename,
				'content'		=> ShortcodeHelper::shortcode2array($content, 1),
				'class'			=> $meta['el_class'],
                'custom_markup' => $meta['custom_markup'],
                'font_color' 	=> '',
                'color' 		=> '',
                'styling'		=> '',
                'av-desktop-hide'=>'',
                'av-medium-hide'=>'',
                'av-small-hide'=>'',
                'av-mini-hide'=>'',
                
				), $atts, $this->config['shortcode']);
				
				
				if($atts['font_color'] == "custom")
				{
					$atts['class']    .= " av_inherit_color";
					$atts['styling']  .= !empty($atts['color']) ? " color:".$atts['color']."; " : "";
					if($atts['styling']) $atts['styling'] = " style='".$atts['styling']."'" ;
				}
				
				
				$slider  = new avia_content_slider($atts);
				return $slider->html();
			}

	}
}









if ( !class_exists( 'avia_content_slider' ) )
{
	class avia_content_slider
	{
		static  $slider = 0; 				//slider count for the current page
		protected $config;	 				//base config set on initialization

		function __construct($config)
		{
            global $avia_config;
            $output = "";

			$this->config = array_merge(array(
                'type'          => 'grid',
				'autoplay'		=> 'false',
                'animation'     => 'fade',
				'handle'		=> '',
				'heading'		=> '',
                'navigation'    => 'arrows',
                'columns'       => 3,
				'interval'		=> 5,
				'class'			=> "",
                'custom_markup' => "",
				'css_id'		=> "",
				'content'		=> array(),
				'styling'		=> ""
				), $config);
		}


		public function html()
		{
			$output = "";
			$counter = 0;
            avia_content_slider::$slider++;
			if(empty($this->config['content'])) return $output;

            //$html .= empty($this->subslides) ? $this->default_slide() : $this->advanced_slide();
			
			extract(AviaHelper::av_mobile_sizes($this->config)); //return $av_font_classes, $av_title_font_classes and $av_display_classes 
            extract($this->config);
			
			$default_heading = 'h3';
			$args = array(
						'heading'		=> $default_heading,
						'extra_class'	=> ''
					);

			$extra_args = array( $this, 'slider_title' );

			/**
			 * @since 4.5.5
			 * @return array
			 */
			$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

			$heading1 = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
			$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';

            $extraClass 		= 'first';
            $grid 				= 'one_third';
            $slide_loop_count 	= 1;
            $loop_counter		= 1;
            $total				= $columns % 2 ? "odd" : "even";
			$heading 			= !empty($this->config['heading']) ? "<{$heading1} class='{$css}'>{$this->config['heading']}</{$heading1}>" : "&nbsp;";
            $slide_count = count($content);

            switch($columns)
            {
                case "1": $grid = 'av_fullwidth'; break;
                case "2": $grid = 'av_one_half'; break;
                case "3": $grid = 'av_one_third'; break;
                case "4": $grid = 'av_one_fourth'; break;
                case "5": $grid = 'av_one_fifth'; break;
                case "6": $grid = 'av_one_sixth'; break;
            }

            $data = AviaHelper::create_data_string(array('autoplay'=>$autoplay, 'interval'=>$interval, 'animation' => $animation, 'show_slide_delay'=>30));

            $thumb_fallback = "";
            $output .= "<div {$data} class='avia-content-slider-element-container avia-content-slider-element-{$type} avia-content-slider avia-smallarrow-slider avia-content-{$type}-active avia-content-slider".avia_content_slider::$slider." avia-content-slider-{$total} {$class} {$av_display_classes}' {$styling}>";

                $heading_class = '';
                if($navigation == 'no') $heading_class .= ' no-content-slider-navigation ';
                if($heading == '&nbsp;') $heading_class .= ' no-content-slider-heading ';

				$output .= "<div class='avia-smallarrow-slider-heading $heading_class'>";
				$output .= "<div class='new-special-heading'>".$heading."</div>";



				if($slide_count > $columns && $type == 'slider' && $navigation != 'no')
	            {
	                if($navigation == 'dots') $output .= $this->slide_navigation_dots();
                    if($navigation == 'arrows') $output .= $this->slide_navigation_arrows();
	            }
				$output .= "</div>";


				$output .= "<div class='avia-content-slider-inner'>";

                foreach($content as $key => $value)
                {
					$link = $linktarget = "";

                    extract($value['attr']);

                    $link = aviaHelper::get_url($link);
                    $blank = (strpos($linktarget, '_blank') !== false || $linktarget == 'yes') ? ' target="_blank" ' : "";
                    $blank .= strpos($linktarget, 'nofollow') !== false ? ' rel="nofollow" ' : "";

                    $parity			= $loop_counter % 2 ? 'odd' : 'even';
                    $last       	= $slide_count == $slide_loop_count ? " post-entry-last " : "";
                    $post_class 	= "post-entry slide-entry-overview slide-loop-{$slide_loop_count} slide-parity-{$parity} {$last}";

                    if($loop_counter == 1) $output .= "<div class='slide-entry-wrap'>";

                    $markup = avia_markup_helper(array('context' => 'entry','echo'=>false, 'custom_markup'=>$custom_markup));
                    $output .= "<section class='slide-entry flex_column {$post_class} {$grid} {$extraClass}' $markup>";

                    $markup = avia_markup_helper(array('context' => 'entry_title','echo'=>false, 'custom_markup'=>$custom_markup));
					
					$default_heading = 'h3';
					$args = array(
								'heading'		=> $default_heading,
								'extra_class'	=> ''
							);

					$extra_args = array( $this, 'slider_entry' );

					/**
					 * @since 4.5.5
					 * @return array
					 */
					$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

					$heading1 = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
					$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';
					
                    $output .= !empty($title) ? "<{$heading1} class='slide-entry-title entry-title {$css}' $markup>" : '';
                    $output .= (!empty($link) && !empty($title)) ? "<a href='{$link}' $blank title='".esc_attr($title)."'>".$title."</a>" : $title;
                    $output .= !empty($title) ? "</{$heading1}>" : '';

                    $markup = avia_markup_helper(array('context' => 'entry_content','echo'=>false, 'custom_markup'=>$custom_markup));
                    $output .= !empty($value['content']) ? "<div class='slide-entry-excerpt entry-content' $markup>".ShortcodeHelper::avia_apply_autop(ShortcodeHelper::avia_remove_autop($value['content']))."</div>" : "";

                    $output .= '</section>';

                    $loop_counter ++;
                    $slide_loop_count ++;
                    $extraClass = "";

                    if($loop_counter > $columns)
                    {
                        $loop_counter = 1;
                        $extraClass = 'first';
                    }

                    if($loop_counter == 1 || !empty($last))
                    {
                        $output .="</div>";
                    }
                }

			    $output .= "</div>";

			$output .= "</div>";

			return $output;
		}


        protected function slide_navigation_arrows()
        {
            $html  = "";
            $html .= "<div class='avia-slideshow-arrows avia-slideshow-controls'>";
			$html .= 	"<a href='#prev' class='prev-slide' ".av_icon_string('prev_big').">".__('Previous','avia_framework' )."</a>";
			$html .= 	"<a href='#next' class='next-slide' ".av_icon_string('next_big').">".__('Next','avia_framework' )."</a>";
            $html .= "</div>";

            return $html;
        }


        protected function slide_navigation_dots()
        {
            $html   = "";
            $html  .= "<div class='avia-slideshow-dots avia-slideshow-controls'>";
            $active = "active";

            $entry_count = count($this->config['content']);
            $slidenumber = $entry_count / (int)$this->config['columns'];
            $slidenumber = $entry_count % (int)$this->config['columns'] ? ((int)$slidenumber + 1) : (int)$slidenumber;

            for($i = 1; $i <= $slidenumber; $i++)
            {
                $html .= "<a href='#{$i}' class='goto-slide {$active}' >{$i}</a>";
                $active = "";
            }

            $html .= "</div>";

            return $html;
        }
	}
}


