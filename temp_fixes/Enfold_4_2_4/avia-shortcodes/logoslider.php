<?php
/**
 * Partner/Logo Element
 * 
 * Shortcode that allows to display a simple partner logo grid or slider
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( !class_exists( 'avia_sc_partner_logo' ) )
{
  class avia_sc_partner_logo extends aviaShortcodeTemplate
	{
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['self_closing']	=	'no';
				
				$this->config['name']			= __('Partner/Logo Element', 'avia_framework' );
				$this->config['tab']			= __('Media Elements', 'avia_framework' );
				$this->config['icon']			= AviaBuilder::$path['imagesURL']."sc-partner.png";
				$this->config['order']			= 7;
				$this->config['target']			= 'avia-target-insert';
				$this->config['shortcode'] 		= 'av_partner';
				$this->config['shortcode_nested'] = array('av_partner_logo');
				$this->config['tooltip'] 	    = __('Display a partner/logo Grid or Slider', 'avia_framework' );
				$this->config['preview'] 		= false;
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
							"type" 			=> "modal_group",
							"id" 			=> "content",
							'container_class' =>"avia-element-fullwidth avia-multi-img",
							"modal_title" 	=> __("Edit Form Element", 'avia_framework' ),
							"std"			=> array(),

							'creator'		=>array(

								"name" => __("Add Images", 'avia_framework' ),
								"desc" => __("Here you can add new Images to the partner/logo element.", 'avia_framework' ),
								"id" 	=> "id",
								"type" 	=> "multi_image",
								"title" => __("Add multiple Images",'avia_framework' ),
								"button" => __("Insert Images",'avia_framework' ),
								"std" 	=> ""),

							'subelements' 	=> array(
									array(
									"name" 	=> __("Choose another Image",'avia_framework' ),
									"desc" 	=> __("Either upload a new, or choose an existing image from your media library",'avia_framework' ),
									"id" 	=> "id",
									"fetch" => "id",
									"type" 	=> "image",
									"title" => __("Change Image",'avia_framework' ),
									"button" => __("Change Image",'avia_framework' ),
									"std" 	=> ""),


									 array(
                                    "name" 	=> __("Image Caption", 'avia_framework' ),
                                    "desc" 	=> __("Display a image caption on hover", 'avia_framework' ),
                                    "id" 	=> "hover",
                                    "type" 	=> "input",
                                    "std" 	=> "",
                                	),


									array(
									"name" 	=> __("Partner/Logo Link?", 'avia_framework' ),
									"desc" 	=> __("Where should the image/logo link to?", 'avia_framework' ),
									"id" 	=> "link",
									"type" 	=> "linkpicker",
									"fetchTMPL"	=> true,
									"std" 	=> "-",
									"subtype" => array(
														__('No Link', 'avia_framework' ) =>'',
														__('Lightbox', 'avia_framework' ) =>'lightbox',
														__('Set Manually', 'avia_framework' ) =>'manually',
														__('Single Entry', 'avia_framework' ) => 'single',
														__('Taxonomy Overview Page',  'avia_framework' ) => 'taxonomy',
														),
									"std" 	=> ""),

                                array(
                                    "name" 	=> __("Link Title", 'avia_framework' ),
                                    "desc" 	=> __("Enter a link title", 'avia_framework' ),
                                    "id" 	=> "linktitle",
                                    "type" 	=> "input",
                                    "required"=> array('link','equals','manually'),
                                    "std" 	=> "",
                                ),


									array(
									"name" 	=> __("Open Link in new Window?", 'avia_framework' ),
									"desc" 	=> __("Select here if you want to open the linked page in a new window", 'avia_framework' ),
									"id" 	=> "link_target",
									"type" 	=> "select",
									"std" 	=> "",
									"required"=> array('link','not_empty_and','lightbox'),
									"subtype" => AviaHtmlHelper::linking_options()),   
						)
					),

                    array(
                        "name" 	=> __("Columns", 'avia_framework' ),
                        "desc" 	=> __("How many columns should be displayed?", 'avia_framework' ),
                        "id" 	=> "columns",
                        "type" 	=> "select",
                        "std" 	=> "3",
                        "subtype" => array(	__('1 Columns', 'avia_framework' )=>'1',
                            __('2 Columns', 'avia_framework' )=>'2',
                            __('3 Columns', 'avia_framework' )=>'3',
                            __('4 Columns', 'avia_framework' )=>'4',
                            __('5 Columns', 'avia_framework' )=>'5',
                            __('6 Columns', 'avia_framework' )=>'6',
                            __('7 Columns', 'avia_framework' )=>'7',
                            __('8 Columns', 'avia_framework' )=>'8',
                        )),

					array(
							"name" 	=> __("Heading", 'avia_framework' ),
							"desc" 	=> __("Do you want to display a heading above the images?", 'avia_framework' ),
							"id" 	=> "heading",
							"type" 	=> "input",
							"std" 	=> "",
							),

					array(
							"name" 	=> __("Logo Image Size", 'avia_framework' ),
							"desc" 	=> __("Choose image size for your slideshow.", 'avia_framework' ),
							"id" 	=> "size",
							"type" 	=> "select",
							"std" 	=> "",
							"subtype" =>  AviaHelper::get_registered_image_sizes(array('thumbnail','logo','widget','slider_thumb'))
							),
					
					 array(
                        "name" 	=> __("Display Border around images?", 'avia_framework' ),
                        "desc" 	=> __("Do you want to display a light border around the images?", 'avia_framework' ),
                        "id" 	=> "border",
                        "type" 	=> "select",
                        "std" 	=> "",
                        "subtype" => array(__('Display border','avia_framework' ) =>'',__('Dont display border','avia_framework' ) =>'av-border-deactivate'),
                    ),
					
                    array(
                        "name" 	=> __("Logo Slider or Logo Grid Layout", 'avia_framework' ),
                        "desc" 	=> __("Do you want to use a grid or a slider to display the logos?", 'avia_framework' ),
                        "id" 	=> "type",
                        "type" 	=> "select",
                        "std" 	=> "slider",
                        "subtype" => array(__('Slider','avia_framework' ) =>'slider',__('Grid','avia_framework' ) =>'grid'),
                    ),

                    array(
                        "name" 	=> __("Transition", 'avia_framework' ),
                        "desc" 	=> __("Choose the transition for your logo slider.", 'avia_framework' ),
                        "id" 	=> "animation",
                        "type" 	=> "select",
                        "std" 	=> "slide",
                        "required" 	=> array('type','equals','slider'),
                        "subtype" => array(__('Slide','avia_framework' ) =>'slide',__('Fade','avia_framework' ) =>'fade'),
                    ),

                    array(
                        "name" 	=> __("Slider controls", 'avia_framework' ),
                        "desc" 	=> __("Do you want to display slider control buttons?", 'avia_framework' ),
                        "id" 	=> "navigation",
                        "type" 	=> "select",
                        "required" 	=> array('type','equals','slider'),
                        "std" 	=> "arrows",
                        "subtype" => array(
                            __('Yes, display arrow control buttons','avia_framework' ) =>'arrows',
                            __('Yes, display dot control buttons','avia_framework' ) =>'dots',
                            __('No, do not display any control buttons','avia_framework' ) =>'no'),
                    ),


					array(
						"name" 	=> __("Autorotation active?",'avia_framework' ),
						"desc" 	=> __("Check if the logo slider should rotate by default",'avia_framework' ),
						"id" 	=> "autoplay",
						"type" 	=> "select",
						"std" 	=> "false",
                        "required" 	=> array('type','equals','slider'),
						"subtype" => array(__('Yes','avia_framework' ) =>'true',__('No','avia_framework' ) =>'false')),

					array(
						"name" 	=> __("Slider autorotation duration",'avia_framework' ),
						"desc" 	=> __("Images will be shown the selected amount of seconds.",'avia_framework' ),
						"id" 	=> "interval",
						"type" 	=> "select",
						"std" 	=> "5",
                        "required" 	=> array('type','equals','slider'),
						"subtype" =>
						array('3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','15'=>'15','20'=>'20','30'=>'30','40'=>'40','60'=>'60','100'=>'100')),
						
						
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
						'nodescription' => true)
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
				$img_template 		= $this->update_template("img_fakeArg", "{{img_fakeArg}}");
				$template 			= $this->update_template("hover", "{{hover}}");

				$thumbnail = isset($params['args']['id']) ? wp_get_attachment_image($params['args']['id']) : "";


				$params['innerHtml']  = "";
				$params['innerHtml'] .= "<div class='avia_title_container'>";
				$params['innerHtml'] .= "	<span class='avia_slideshow_image' {$img_template} >{$thumbnail}</span>";
				$params['innerHtml'] .= "	<div class='avia_slideshow_content'>";
				$params['innerHtml'] .= "		<h4 class='avia_title_container_inner' {$template} >".$params['args']['hover']."</h4>";
				$params['innerHtml'] .= "	</div>";
				$params['innerHtml'] .= "</div>";



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
				extract(AviaHelper::av_mobile_sizes($atts)); //return $av_font_classes, $av_title_font_classes and $av_display_classes 
				
				$atts = shortcode_atts(array(
                'type'          => 'grid',
				'size'			=> 'featured',
				'ids'    	 	=> '',
				'autoplay'		=> 'false',
				'navigation'    => 'arrows',
                'animation'     => 'slide',
				'interval'		=> 5,
				'heading'		=> '',
				'hover'			=> '',
                'columns'       => 3,
                'border'		=> '',
				'handle'		=> $shortcodename,
				'content'		=> ShortcodeHelper::shortcode2array($content),
				'class'			=> $meta['el_class']." ".$av_display_classes,
				'custom_markup' => $meta['custom_markup']
				), $atts, $this->config['shortcode']);

				$logo = new avia_partner_logo($atts);
				return $logo->html();
			}

	}
}









if ( !class_exists( 'avia_partner_logo' ) )
{
	class avia_partner_logo
	{
		static  $slider = 0; 				//slider count for the current page
		
		protected $config;	 				//base config set on initialization
		protected $slides;	 				//attachment posts for the current slider
		protected $slide_count;				//number of slides
		protected $id_array;				//unique array of slide id's

		
		/**
		 * 
		 * @param array $config
		 */
		public function __construct( $config )
		{
			$this->slides = array();
			$this->slide_count = 0;
			$this->id_array = array();

			$this->config = array_merge(array(
                'type'          => 'grid',
				'size'			=> 'featured',
				'ids'    	 	=> '',
				'autoplay'		=> 'false',
				'navigation'    => 'arrows',
                'animation'     => 'slide',
				'handle'		=> '',
				'heading'		=> '',
				'border'		=> '',
                'columns'       => 3,
				'interval'		=> 5,
				'class'			=> "",
				'custom_markup' => "",
				'hover'			=> '',
				'css_id'		=> "",
				'content'		=> array()
				), $config);


			//if we got subslides overwrite the id array
			if(!empty($config['content']))
			{
				$this->extract_subslides($config['content']);
			}

			$this->set_slides($this->config['ids']);
		}
		
		/**
		 * 
		 * @since 4.2.5
		 * @added_by Günter
		 */
		public function __destruct() 
		{
			unset( $this->slides );
			unset( $this->config );
			unset( $this->id_array );
		}

		/**
		 * 
		 * @param string $ids
		 * @return void
		 */
		public function set_slides($ids)
		{
			if(empty($ids)) return;

			$this->slides = get_posts(array(
				'include' => $ids,
				'post_status' => 'inherit',
				'post_type' => 'attachment',
				'post_mime_type' => 'image',
				'order' => 'ASC',
				'orderby' => 'post__in')
				);



			//resort slides so the id of each slide matches the post id
			$new_slides = array();
			$new_ids = array();
			foreach($this->slides as $slide)
			{
				$new_slides[$slide->ID] = $slide;
				$new_ids[] = $slide->ID;
			}

			$this->slides 		= $new_slides;
			$this->id_array 	= $new_ids;
			$this->slide_count 	= count( explode( ',', $ids ) );
		}

		public function set_size($size)
		{
			$this->config['size'] = $size;
		}

		public function set_extra_class($class)
		{
			$this->config['class'] .= " ".$class;
		}



		public function html()
		{
			$output = "";
			$counter = 0;
            		avia_partner_logo::$slider++;
			if($this->slide_count == 0) return $output;


            extract($this->config);

            $extraClass 		= 'first';
            $grid 				= 'one_third';
            $slide_loop_count 	= 1;
            $loop_counter		= 1;
            $total				= $columns % 2 ? "odd" : "even";
			$heading 			= !empty($this->config['heading']) ? '<h3>'.$this->config['heading'].'</h3>' : "&nbsp;";

            switch($columns)
            {
                case "1": $grid = 'av_fullwidth'; break;
                case "2": $grid = 'av_one_half'; break;
                case "3": $grid = 'av_one_third'; break;
                case "4": $grid = 'av_one_fourth'; break;
                case "5": $grid = 'av_one_fifth'; break;
                case "6": $grid = 'av_one_sixth'; break;
                case "7": $grid = 'av_one_seventh'; break;
                case "8": $grid = 'av_one_eighth'; break;
            }

            $data = AviaHelper::create_data_string(array('autoplay'=>$autoplay, 'interval'=>$interval, 'animation' => $animation));

            $thumb_fallback = "";
            $output .= "<div {$data} class='avia-logo-element-container {$border} avia-logo-{$type} avia-content-slider avia-smallarrow-slider avia-content-{$type}-active noHover avia-content-slider".avia_partner_logo::$slider." avia-content-slider-{$total} {$class}' >";

				$heading_class = '';
                if($navigation == 'no') $heading_class .= ' no-logo-slider-navigation ';
                if($heading == '&nbsp;') $heading_class .= ' no-logo-slider-heading ';

				$output .= "<div class='avia-smallarrow-slider-heading $heading_class'>";
				
				if($heading != '&nbsp;' || $navigation != 'no')
				$output .= "<div class='new-special-heading'>".$heading."</div>";

				if(count($this->id_array) > $columns && $type == 'slider' && $navigation != 'no')
				{
					if($navigation == 'arrows') $output .= $this->slide_navigation_arrows();
				}

				$output .= "</div>";


                $markup_url = avia_markup_helper(array('context' => 'image_url','echo'=>false, 'custom_markup'=>$custom_markup));
                $markup = avia_markup_helper(array('context' => 'image','echo'=>false, 'custom_markup'=>$custom_markup));

				$output .= "<div class='avia-content-slider-inner'>";

                foreach( $this->subslides as $key => $slides )
                {
					$id = isset( $slides['attr']['id'] ) ? $slides['attr']['id'] : 0;
					
                    if(isset($this->slides[$id]))
                    {
                        $slide = $this->slides[$id];
                        $meta = array_merge( array('link' => '', 'link_target' => '', 'linktitle' => '', 'hover'=>'', 'custom_markup'=>''), $slides['attr'] );
                        extract($meta);

                        $markup_url = avia_markup_helper(array('context' => 'image_url','echo'=>false, 'id'=>$slide->ID, 'custom_markup'=>$custom_markup));
                		$markup = avia_markup_helper(array('context' => 'image','echo'=>false, 'id'=>$slide->ID, 'custom_markup'=>$custom_markup));

                        $img = wp_get_attachment_image($slide->ID, $size);
                        $link = aviaHelper::get_url($link, $slide->ID);
						$blank = (strpos($link_target, '_blank') !== false || $link_target == 'yes') ? ' target="_blank" ' : "";
						$blank .= strpos($link_target, 'nofollow') !== false ? ' rel="nofollow" ' : "";
                    }

                    $parity			= $loop_counter % 2 ? 'odd' : 'even';
                    $last       	= $this->slide_count == $slide_loop_count ? " post-entry-last " : "";
                    $post_class 	= "post-entry slide-entry-overview slide-loop-{$slide_loop_count} slide-parity-{$parity} {$last}";
                    $thumb_class	= "real-thumbnail";
					$single_data 	= empty($hover) ? '' : 'data-avia-tooltip="'.$hover.'"';


                    if($loop_counter == 1) $output .= "<div class='slide-entry-wrap' $markup>";

                    $img = str_replace('<img ', "<img $markup_url ", $img);

                    $output .= "<div {$single_data} class='slide-entry flex_column no_margin {$post_class} {$grid} {$extraClass} {$thumb_class}'>";
                    $output .= !empty($link) ? "<a href='{$link}' data-rel='slide-".avia_partner_logo::$slider."' class='slide-image' title='{$linktitle}' {$blank} >{$img}</a>" : $img;
                    $output .= "</div>";

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


			if(count($this->id_array) > $columns && $type == 'slider' && $navigation != 'no')
			{
				if($navigation == 'dots') $output .= $this->slide_navigation_dots();
			}

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

	            $slidenumber = $this->slide_count / (int)$this->config['columns'];
	            $slidenumber = $this->slide_count % (int)$this->config['columns'] ? ((int)$slidenumber + 1) : (int)$slidenumber;

	            for($i = 1; $i <= $slidenumber; $i++)
	            {
	                $html .= "<a href='#{$i}' class='goto-slide {$active}' >{$i}</a>";
	                $active = "";
	            }

	            $html .= "</div>";

	            return $html;
	        }

		protected function extract_subslides($slide_array)
		{
			$this->config['ids']= array();
			$this->subslides 	= array();

			foreach($slide_array as $key => $slide)
			{
				$this->subslides[$key] = $slide;
				$this->config['ids'][] = $slide['attr']['id'];
			}

			$this->config['ids'] = implode(',',$this->config['ids'] );
			unset($this->config['content']);
		}
	}
}




















