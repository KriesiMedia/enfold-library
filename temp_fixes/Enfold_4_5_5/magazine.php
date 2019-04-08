<?php
/**
 * Magazine
 *
 * Display entries in a magazine like fashion
 * Element is in Beta and by default disabled. Todo: test with layerslider elements. currently throws error bc layerslider is only included if layerslider element is detected which is not the case with the post/page element
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( !class_exists( 'avia_sc_magazine' ))
{
	class avia_sc_magazine extends aviaShortcodeTemplate
	{

		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['self_closing']	=	'yes';
			
			$this->config['name']		= __('Magazine', 'avia_framework' );
			$this->config['tab']		= __('Content Elements', 'avia_framework' );
			$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-magazine.png";
			$this->config['order']		= 39;
			$this->config['target']		= 'avia-target-insert';
			$this->config['shortcode'] 	= 'av_magazine';
			$this->config['tooltip'] 	= __('Display entries in a magazine like fashion', 'avia_framework' );
			$this->config['drag-level'] = 3;
			$this->config['preview'] 	= 1;
			$this->config['disabling_allowed'] = true;
		}
		
		function extra_assets()
		{
			//load css
			wp_enqueue_style( 'avia-module-magazine' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/magazine/magazine.css' , array('avia-layout'), false );
			
			//load js
			wp_enqueue_script( 'avia-module-magazine' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/magazine/magazine.js' , array('avia-shortcodes'), false, TRUE );

		
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
						"name" 	=> __("Which Entries?", 'avia_framework' ),
						"desc" 	=> __("Select which entries should be displayed by selecting a taxonomy", 'avia_framework' ),
						"id" 	=> "link",
						"fetchTMPL"	=> true,
						"type" 	=> "linkpicker",
						"subtype"  => array( __('Display Entries from:',  'avia_framework' )=>'taxonomy'),
						"multiple"	=> 6,
						"std" 	=> "category"
				),
				
				array(
						'name'		=> __( 'Number of entries per page', 'avia_framework' ),
						'desc'		=> __( 'How many entries should be displayed?', 'avia_framework' ),
						'id'		=> 'items',
						'type'		=> 'select',
						'std'		=> '5',
						'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( 'All' => '-1' ) ) ),
				
				array(
						'name'		=> __( 'Pagination', 'avia_framework' ),
						'desc'		=> __( 'Should a pagination be displayed to view additional entries? This disables &quot;Display Tabs for each category&quot;.', 'avia_framework' ),
						'id'		=> 'paginate',
						'type'		=> 'select',
						'std'		=> '',
						'required'	=> array( 'items', 'not', '-1' ),
						'subtype'	=> array(
											__( 'Display Pagination', 'avia_framework' )	=> 'pagination',
											__( 'No Pagination', 'avia_framework' )			=> ''
										)
					),
				
				
				array(
                        "name" 	=> __("Offset Number", 'avia_framework' ),
                        "desc" 	=> __("The offset determines where the query begins pulling posts. Useful if you want to remove a certain number of posts because you already query them with another blog or magazine element.", 'avia_framework' ),
                        "id" 	=> "offset",
                        "type" 	=> "select",
                        "std" 	=> "0",
						'required'	=> array( 'paginate', 'equals', '' ),
                        "subtype" => AviaHtmlHelper::number_array( 1, 100, 1, array( __( 'Deactivate offset','avia_framework')=>'0', __('Do not allow duplicate posts on the entire page (set offset automatically)', 'avia_framework' ) =>'no_duplicates'))),


				
				array(	
						'name' 	=> __( 'Display Tabs for each category selected above?', 'avia_framework' ),
						'desc' 	=> __( 'If checked and you have selected more than one taxonomy above, a tab will be displayed for each of them. Will be ignored when using Pagination.', 'avia_framework' ),
						'id' 	=> 'tabs',
						'std' 	=> 'true',
						'type' 	=> 'checkbox' ),
				
				
				array(	
						"name" 	=> __("Display Thumbnails?", 'avia_framework' ),
						"desc" 	=> __("If checked all entries that got a feature image will show it", 'avia_framework' ),
						"id" 	=> "thumbnails",
						"std" 	=> "true",
						"type" 	=> "checkbox"),
				
				array(	
						"name" 	=> __("Display Element Heading?", 'avia_framework' ),
						"desc" 	=> __("If checked you can enter a title with link for this element", 'avia_framework' ),
						"id" 	=> "heading_active",
						"std" 	=> "",
						"type" 	=> "checkbox"),
				
				array(	
						"name" 	=> __("Heading Text", 'avia_framework' ),
						"id" 	=> "heading",
						"required"=> array('heading_active','not',''),
						"std" 	=> "",
						"type" 	=> "input"),
						
				array(	
						"name" 	=> __("Heading Link?", 'avia_framework' ),
						"desc" 	=> __("Where should the heading link to?", 'avia_framework' ),
						"id" 	=> "heading_link",
						"type" 	=> "linkpicker",
						"required"=> array('heading_active','not',''),
						"fetchTMPL"	=> true,
						"subtype" => array(	
											__('Set Manually', 'avia_framework' ) =>'manually',
											__('Single Entry', 'avia_framework' ) =>'single',
											__('Taxonomy Overview Page',  'avia_framework' ) =>'taxonomy',
											),
						"std" 	=> ""),
				
				array(	
							"name" 	=> __("Heading Area Color", 'avia_framework' ),
							"desc" 	=> __("Choose a color for your heading area here", 'avia_framework' ),
							"id" 	=> "heading_color",
							"type" 	=> "select",
							"std" 	=> "theme-color",
							"required"=> array('heading_active','not',''),
							"subtype" => array(	
												__('Theme Color', 'avia_framework' )=>'theme-color',
												__('Blue', 'avia_framework' )=>'blue',
												__('Red',  'avia_framework' )=>'red',
												__('Green', 'avia_framework' )=>'green',
												__('Orange', 'avia_framework' )=>'orange',
												__('Aqua', 'avia_framework' )=>'aqua',
												__('Teal', 'avia_framework' )=>'teal',
												__('Purple', 'avia_framework' )=>'purple',
												__('Pink', 'avia_framework' )=>'pink',
												__('Silver', 'avia_framework' )=>'silver',
												__('Grey', 'avia_framework' )=>'grey',
												__('Black', 'avia_framework' )=>'black',
												__('Custom Color', 'avia_framework' )=>'custom',
												)),
						
					array(	
							"name" 	=> __("Custom Font Color", 'avia_framework' ),
							"desc" 	=> __("Select a custom font color for your Heading area here", 'avia_framework' ),
							"id" 	=> "heading_custom_color",
							"type" 	=> "colorpicker",
							"std" 	=> "#ffffff",
							"required" => array('heading_color','equals','custom')
						),	
				
				
				array(	
						"name" 	=> __("Should the first entry be displayed bigger?", 'avia_framework' ),
						"desc" 	=> __("If checked the first entry will stand out with big image", 'avia_framework' ) ."</small>" ,
						"id" 	=> "first_big",
						"std" 	=> "",
						"type" 	=> "checkbox"),
				
				array(
						"name" 	=> __("First entry position", 'avia_framework' ),
						"desc" 	=> __("Where do you want to display the first entry?", 'avia_framework' ),
						"id" 	=> "first_big_pos",
						"type" 	=> "select",
						"std" 	=> "top",
						"required"=> array('first_big','not',''),
						"subtype" => array(	__('Display the first entry at the top of the others','avia_framework' ) =>'top',
											__('Display the first entry beside the others','avia_framework' ) =>'left')),
				
				
				
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
			
			if(current_theme_supports('add_avia_builder_post_type_option'))
			{
			    $element = array(
				"name" 	=> __("Select Post Type", 'avia_framework' ),
				"desc" 	=> __("Select which post types should be used. Note that your taxonomy will be ignored if you do not select an assign post type.
					      If you don't select post type all registered post types will be used", 'avia_framework' ),
				"id" 	=> "post_type",
				"type" 	=> "select",
				"multiple"	=> 6,
				"std" 	=> "",
				"subtype" => AviaHtmlHelper::get_registered_post_type_array()
			    );

			    array_splice($this->elements, 2, 0, array($element));
			}
			
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
			$params['innerHtml'] = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
			$params['innerHtml'].= "<div class='avia-element-label'>".$this->config['name']."</div>";
			$params['content'] 	 = NULL; //remove to allow content elements
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
			
			$atts['class'] = $meta['el_class'];
			$atts['custom_markup'] = $meta['custom_markup'];

			$mag = new avia_magazine($atts);
			$mag -> query_entries();
			return $mag->html();
			
		}

	}
}





if ( ! class_exists( 'avia_magazine' ) )
{
	class avia_magazine
	{
		/**
		 * @since < 4.0
		 * @var int 
		 */
		static protected $magazine = 0;
		
		/**
		 * @since < 4.0
		 * @var array 
		 */
		protected $atts;
		
		/**
		 * @since < 4.0
		 * @var WP_Query 
		 */
		protected $entries;
		
		/**
		 * @since < 4.5.6
		 * @var array 
		 */
		protected $screen_options;


		/**
		 * @since < 4.0
		 * @param array $atts
		 */
		public function __construct( $atts = array() )
		{	
			$this->entries = null;
			$this->screen_options = AviaHelper::av_mobile_sizes( $atts );
			
			self::$magazine += 1;
			$this->atts = shortcode_atts(array(	'class'					=> '',
												'custom_markup' 		=> "",
												'items' 				=> '16',
												'paginate'				=> '',
												'tabs' 					=> false,
												'thumbnails'			=> true,
												'heading_active'		=> false,
												'heading'				=> "",
												'heading_link'			=> "",
												'heading_color'			=> "",
												'heading_custom_color'	=> "",
												'first_big'				=> false,
												'first_big_pos'			=> 'top',
		                                 		'taxonomy'  			=> 'category',
		                                 		'link'					=> '',
		                                 		'categories'			=> array(),
		                                 		'extra_categories'		=> array(),
												'post_type'				=> array(),
		                                 		'offset'				=> 0,
		                                 		'image_size'			=> array( 'small'=> 'thumbnail', 'big' => 'magazine')
		                                 		
		                                 		), $atts, 'av_magazine' );
			
			/**
			 * When pagination, tabs are not possible
			 */
			if( ! empty( $this->atts['paginate'] ) )
			{
				$this->atts['tabs'] = false;
				$this->atts['offset'] = 0;
			}

			// fetch the taxonomy and the taxonomy ids
		    $this->extract_terms();                             		
			
			//convert checkbox to true/false
			$this->atts['tabs'] = $this->atts['tabs'] === "aviaTBtabs" ? true : false;
			$this->atts['thumbnails'] = $this->atts['thumbnails'] === "aviaTBthumbnails" ? true : false;
			
			
			//filter the attributes
		    $this->atts = apply_filters('avf_magazine_settings', $this->atts, self::$magazine);
		    
		    //set small or big
		    if(empty($this->atts['first_big'])) $this->atts['first_big_pos'] = "";
		    
		    //set heading text
		    if(empty($this->atts['heading_active'])) $this->atts['heading'] = "";
		    
		    //set if top bar is active
		    $this->atts['top_bar'] = !empty($this->atts['heading']) || !empty($this->atts['tabs'])  ? "av-magazine-top-bar-active" : "";
		}
		
		/**
		 * 
		 * @since 4.5.6
		 */
		public function __destruct() 
		{
			unset( $this->screen_options );
			unset( $this->atts );
			unset( $this->entries );
		}
		
		/**
		 * 
		 * @since < 4.0
		 */
		protected function extract_terms()
		{
			if(isset($this->atts['link']))
			{
				$this->atts['link'] = explode(',', $this->atts['link'], 2 );
				$this->atts['taxonomy'] = $this->atts['link'][0];

				if(isset($this->atts['link'][1]))
				{
					$this->atts['categories'] = $this->atts['link'][1];
				}
				else
				{
					$this->atts['categories'] = array();
				}
			}
		}
		
		/**
		 * 
		 * @since < 4.0
		 * @return string
		 */
		protected function sort_buttons()
		{
			$term_args = array( 
								'taxonomy'		=> $this->atts['taxonomy'],
								'hide_empty'	=> true
							);
			/**
			 * To display private posts you need to set 'hide_empty' to false, 
			 * otherwise a category with ONLY private posts will not be returned !!
			 * 
			 * You also need to add post_status "private" to the query params with filter avf_magazine_entries_query.
			 * 
			 * @since 4.4.2
			 * @added_by Günter
			 * @param array $term_args 
			 * @param string $context 
			 * @return array
			 */
			$term_args = apply_filters( 'avf_av_magazine_term_args', $term_args, 'sort_button' );
				
			$sort_terms = AviaHelper::get_terms( $term_args );
			
			$current_page_terms	= array();
			$term_count 		= array();
			$display_terms 		= is_array($this->atts['categories']) ? $this->atts['categories'] : array_filter(explode(',',$this->atts['categories']));

			$output = "<div class='av-magazine-sort ' data-magazine-id='".self::$magazine."' >";
			
			$first_item_name = apply_filters('avf_magazine_sort_first_label', __('All','avia_framework' ), $this->atts);
			$output .= "<div class='av-sort-by-term'>";
			$output .= '<a href="#" data-filter="sort_all" class="all_sort_button active_sort"><span class="inner_sort_button"><span>'.$first_item_name.'</span></span></a>';

			foreach($sort_terms as $term)
			{ 	
				if (!in_array($term->term_id, $display_terms)) continue;
				
                if(!isset($term_count[$term->term_id])) $term_count[$term->term_id] = 0;
				$term->slug = str_replace('%', '', $term->slug);
				
				$output .= 	"<span class='text-sep {$term->slug}_sort_sep'>/</span>";
				$output .= 	'<a href="#" data-filter="sort_'.$term->term_id.'" class="'.$term->slug.'_sort_button " ><span class="inner_sort_button">';
				$output .= 		"<span>".esc_html(trim($term->name))."</span>";
				$output .= 		"</span>";
				$output .= 	"</a>";
				
				$this->atts['extra_categories'][] = $term->term_id;
			}

			$output .= "</div></div>";
			
			if(count($this->atts['extra_categories']) <= 1) return "";
			
			return $output;
		}
		
		/**
		 * Fetch new entries
		 * 
		 * @since < 4.0
		 * @param array $params
		 * @param boolean $return
		 * @return WP_Query
		 */
		public function query_entries( $params = array(), $return = false )
		{
			global $avia_config;

			if( empty( $params ) ) 
			{
				$params = $this->atts;
			}

			if( empty( $params['custom_query'] ) )
            {
				$query = array();

				if(!empty($params['categories']))
				{
					//get the portfolio categories
					$terms 	= explode(',', $params['categories']);
				}

				$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
				if( ! $page || $params['paginate'] == '' ) 
				{
					$page = 1;
				}

				//if we find no terms for the taxonomy fetch all taxonomy terms
				if( empty($terms[0] ) || is_null( $terms[0] ) || $terms[0] === "null" )
				{
					$term_args = array( 
								'taxonomy'		=> $params['taxonomy'],
								'hide_empty'	=> true
							);
					/**
					 * To display private posts you need to set 'hide_empty' to false, 
					 * otherwise a category with ONLY private posts will not be returned !!
					 * 
					 * You also need to add post_status "private" to the query params with filter avf_magazine_entries_query.
					 * 
					 * @since 4.4.2
					 * @added_by Günter
					 * @param array $term_args 
					 * @param string $context 
					 * @return array
					 */
					$term_args = apply_filters( 'avf_av_magazine_term_args', $term_args, 'query_entries' );

					$allTax = AviaHelper::get_terms( $term_args );

					$terms = array();
					foreach( $allTax as $tax )
					{
						$terms[] = $tax->term_id;
					}
				}
				
				if( $params['offset'] == 'no_duplicates' )
                {
                    $params['offset'] = 0;
                    if( empty($params['ignore_duplicate_rule'] ) ) 
					{
						$no_duplicates = true;
					}
                }
					
				if( empty( $params['post_type'] ) ) 
				{
					$params['post_type'] = get_post_types();
				}
				
				if( is_string( $params['post_type'] ) ) 
				{
					$params['post_type'] = explode( ',', $params['post_type'] );
				}
						
				$query = array(	
								'orderby'		=> 'date',
								'order'			=> 'DESC',
								'paged'			=> $page,
								'post_type'		=> $params['post_type'],
								'post__not_in'	=> ( ! empty($no_duplicates ) ) ? $avia_config['posts_on_current_page'] : array(),
								'offset'		=> $params['offset'] != 0 ? $params['offset'] : false,
								'posts_per_page' => $params['items'],
								'tax_query'		=> array( array( 	
																'taxonomy' 	=> $params['taxonomy'],
																'field' 	=> 'id',
																'terms' 	=> $terms,
																'operator' 	=> 'IN'
																)
															)
							);

			}
			else
			{
				$query = $params['custom_query'];
			}


			$query   = apply_filters('avf_magazine_entries_query', $query, $params);
			
			$entries = new WP_Query( $query );
			
			
			if( ( $entries->post_count > 0 ) && empty( $params['ignore_duplicate_rule'] ) )
			{
				foreach( $entries->posts as $entry )
	            {
					 $avia_config['posts_on_current_page'][] = $entry->ID;
	            }
			}
			
			if( $return )
			{
				return $entries;
			}
			else
			{
				$this->entries = $entries;
			}
		}
		
		/**
		 * 
		 * @since < 4.0
		 * @return string
		 */
		public function html()
		{
			$output = "";
			$class	= !empty($this->atts['first_big_pos'])   ? " av-magazine-hero-".$this->atts['first_big_pos'] : "";
			$class	.= " ".$this->atts['top_bar'];
			if(!empty($this->atts['tabs'])) $class	.= " av-magazine-tabs-active";
			
			
			extract($this->screen_options); //return $av_font_classes, $av_title_font_classes and $av_display_classes 
			
			
			$output .= "<div id='av-magazine-".self::$magazine."' class='av-magazine {$av_display_classes} ".$this->atts['class']." {$class}' >";
			
			if($this->atts['top_bar'])
			{	
				$link 	 = AviaHelper::get_url($this->atts['heading_link']);
				$heading = $this->atts['heading'];
				$b_class = "";
				$b_style = "";
				
				if($this->atts['heading_color'] != "theme-color")
				{
					if($this->atts['heading_color'] == "custom") $b_style = "style='color: ".$this->atts['heading_custom_color'].";'";
					$b_class .= "avia-font-color-".$this->atts['heading_color']." avia-inherit-font-color";
				}
				
				$output .= "<div class='av-magazine-top-bar {$b_class}' {$b_style}>";
				
				if($heading)
				{
					$output .= "<a href='{$link}' class='av-magazine-top-heading'>{$heading}</a>";
				}
				
				if(!empty($this->atts['tabs']))
				{
					$output .= $this->sort_buttons();
				}
				
				$output .="</div>";
			}
			
			
			//magazine main loop
			$output .= $this->magazine_loop( $this->entries->posts );
			
			
			//magazine sub loops
			$output .= $this->magazine_sub_loop();
			
			//append pagination
			if( $this->atts['paginate'] == "pagination" && $avia_pagination = avia_pagination( $this->entries->max_num_pages, 'nav' ) )
			{
				$output .= "<div class='av-masonry-pagination av-masonry-pagination-{$this->atts['paginate']}'>{$avia_pagination}</div>";
			}
			
			$output .="</div>";
			return $output;
		}
		
		/**
		 * 
		 * @since < 4.0
		 * @return string
		 */
		protected function magazine_sub_loop()
		{
			$output = "";
			
			if( ! empty($this->atts['extra_categories']) && count( $this->atts['extra_categories'] ) > 1 )
			{
				foreach( $this->atts['extra_categories'] as $category )
				{
					$params = $this->atts;
					$params['ignore_duplicate_rule'] = true;
					$params['categories'] = $category;
					$params['sort_var'] = $category;		
					
					$entries = $this->query_entries( $params, true );
					$output .= $this->magazine_loop( $entries->posts, $params );
					
				}
			}
			
			return $output;
		}
		
		
		
		/**
		 * 
		 * @since < 4.0
		 * @param array $entries		WP_Post objects
		 * @param array $params
		 * @return string
		 */
		protected function magazine_loop( array $entries, $params = array())
		{
			$output = "";
			$loop 	= 0;
			$grid	= $this->atts['first_big_pos'] == "left" ? "flex_column av_one_half " : "";
			$html   = !empty($this->atts['first_big_pos'])   ? array("before"=>"<div class='av-magazine-hero first {$grid}'>","after"=>'</div>') : array("before"=>'',"after"=>'');
			$css 	= empty($params['sort_var']) ? "sort_all" : "av-hidden-mag sort_".$params['sort_var'];
			
			if( ! empty( $entries ) )
			{
				$output .= "<div class='av-magazine-group {$css}'>";
			
				foreach( $entries as $entry )
				{
					$loop ++;
					$entry->loop = $loop;
					
					$style = ($loop === 1 && !empty($this->atts['first_big'])) ? "big" : "small";
					if($loop == 2 && !empty($html['before'])) $html = array("before"=>"<div class='av-magazine-sideshow {$grid}'>","after"=>'');
					if($loop == 3) $html = array("before"=>'',"after"=>'');
					
					
					$output .= $html['before'];
					$output .= $this->render_entry($entry, $style);
					$output .= $html['after'];
				}
				
				if($loop !== 1 && !empty($this->atts['first_big_pos'])) $output .= "</div>";
				
				$output .= "</div>";
			}
			
			return $output;
		}
		
		
		/**
		 * 
		 * @since < 4.0
		 * @param WP_Post $entry
		 * @param string $style
		 * @return string
		 */
		protected function render_entry( WP_Post $entry, $style )
		{
				
			$output			= "";
			$image	 		= get_the_post_thumbnail( $entry->ID, $this->atts['image_size'][$style] );
			$link			= get_post_meta( $entry->ID ,'_portfolio_custom_link', true ) != "" ? get_post_meta( $entry->ID ,'_portfolio_custom_link_url', true ) : get_permalink( $entry->ID );
			$titleAttr		= "title='".__('Link to:','avia_framework')." ".the_title_attribute(array('echo' => 0, 'post' => $entry->ID))."'";
			$title	 		= "<a href='{$link}' {$titleAttr}>". apply_filters( 'avf_magazine_title', get_the_title( $entry->ID ), $entry ) ."</a>";
			$titleTag		= "h3";
			$excerpt		= "";
			$time			= get_the_time(get_option('date_format'), $entry->ID);
			$separator      = "<span class='av-magazine-text-sep text-sep-date'>/</span>";
			
			$author_link    = get_author_posts_url($entry->post_author);
			$author_name    = apply_filters('avf_author_name', get_the_author_meta('display_name', $entry->post_author), $entry->post_author);
			$author         = '<a href='.$author_link.' title="'.__('by','avia_framework').' '.$author_name.'" rel="author">'.$author_name.'</a>';
			$author_output  = '<span class="av-magazine-author minor-meta">'.__('by','avia_framework')." ";
			$author_output .= '<span class="av-magazine-author-link" '.avia_markup_helper(array('context' => 'author_name','echo'=>false)).'>';
 			$author_output .= "<span class='av-magazine-author meta-color vcard author'><span class='fn'>";
 			$author_output .= $author;
 			$author_output .= '</span></span>';
 			$author_output .= '</span>';
 			$author_output .= '</span>';

			$markupEntry  	= avia_markup_helper(array('context' => 'entry','echo'=>false, 'id'=>$entry->ID, 'custom_markup'=>$this->atts['custom_markup']));
			$markupTitle 	= avia_markup_helper(array('context' => 'entry_title','echo'=>false, 'id'=>$entry->ID, 'custom_markup'=>$this->atts['custom_markup']));
			$markupContent 	= avia_markup_helper(array('context' => 'entry_content','echo'=>false, 'id'=>$entry->ID, 'custom_markup'=>$this->atts['custom_markup']));
			$markupTime 	= avia_markup_helper(array('context' => 'entry_time','echo'=>false, 'id'=>$entry->ID, 'custom_markup'=>$this->atts['custom_markup']));
			$format			= get_post_format($entry->ID) ? get_post_format($entry->ID) : 'standard';
			$type			= get_post_type($entry->ID);
			$icontype		= $type == 'post' ?  $format : $type;
			$icon 			=  "<a href='{$link}' {$titleAttr} class='iconfont av-magazine-entry-icon' ".av_icon_string($icontype)."></a>";
			$extraClass		= "";
			
			if($style == 'small')
			{
				if(empty($this->atts['thumbnails']))
				{
					 $image = "";
					 $extraClass = "av-magazine-no-thumb";
				}
			}
			else
			{
				$excerpt = !empty($entry->post_excerpt) ? $entry->post_excerpt : avia_backend_truncate($entry->post_content, apply_filters( 'avf_magazine_excerpt_length' , 60) , apply_filters( 'avf_magazine_excerpt_delimiter' , " "), "…", true, '');
			}
			
					
			
			$output .= "<article class='hentry av-magazine-entry av-magazine-entry-id-".$entry->ID." av-magazine-format-{$format} av-magazine-type-{$type} av-magazine-entry-".$entry->loop." av-magazine-entry-".$style." {$extraClass}' {$markupEntry}>";
			
			if($this->atts['thumbnails'] || ($style == 'big' && $image))
			{
							$output .="<div class='av-magazine-thumbnail'>";
				if($image)	$output .="<a href='{$link}' {$titleAttr} class='av-magazine-thumbnail-link '>{$image}</a>";
				if(!$image)	$output .= $icon;
							$output .="</div>";
			}
		
			$output .= 		"<div class='av-magazine-content-wrap'>";
			$output .=		"<header class='entry-content-header'>";
			$output .=			"<time class='av-magazine-time updated' {$markupTime}>".$time."</time>";
			$output .=			$separator.$author_output;
			$output .=			"<{$titleTag} class='av-magazine-title entry-title' {$markupTitle}>{$title}</{$titleTag}>";
			$output .= 		"</header>";
if($excerpt)$output .=		"<div class='av-magazine-content entry-content' {$markupContent}>{$excerpt}</div>";
			$output .= 		"</div>";
			$output .= 		"<footer class='entry-footer'></footer>";
			$output .= "</article>";
			
			return $output;
		}
	}
}

