<?php
/**
 * Upcoming Events
 *
 * Show a list of upcoming events
 * Element is in Beta and by default disabled. Todo: test with layerslider elements. currently throws error bc layerslider is only included if layerslider element is detected which is not the case with the post/page element
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'Tribe__Events__Main' ) )
{
	function av_upcoming_events_fallback()
	{
		return "<p>Please install the <a href='https://wordpress.org/plugins/the-events-calendar/'>The Events Calendar</a> or <a href='http://mbsy.co/6cr37'>The Events Calendar Pro</a> Plugin to display a list of upcoming Events</p>";
	}
	
	add_shortcode( 'av_upcoming_events', 'av_upcoming_events_fallback' );
	return;
}



if ( ! class_exists( 'avia_sc_upcoming_events' ) )
{
	class avia_sc_upcoming_events extends aviaShortcodeTemplate
	{
		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['self_closing']	=	'yes';
			
			$this->config['name']		= __('Upcoming Events', 'avia_framework' );
			$this->config['tab']		= __('Plugin Additions', 'avia_framework' );
			$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-blog.png";
			$this->config['order']		= 30;
			$this->config['target']		= 'avia-target-insert';
			$this->config['shortcode'] 	= 'av_upcoming_events';
			$this->config['tooltip'] 	= __('Show a list of upcoming events', 'avia_framework' );
			$this->config['drag-level'] = 3;
			$this->config['disabling_allowed'] = true;
		}
		
		/**
		 * 
		 */
		function extra_assets()
		{
			//load css
			wp_enqueue_style( 'avia-module-events-upcoming' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/events_upcoming/events_upcoming.css' , array('avia-layout'), false );
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
						"name" 	=> __("Which Entries?", 'avia_framework' ),
						"desc" 	=> __("Select which entries should be displayed by selecting a taxonomy", 'avia_framework' ),
						"id" 	=> "categories",
						"type" 	=> "select",
						"taxonomy" => Tribe__Events__Main::TAXONOMY,
					    "subtype" => "cat",
						"multiple"	=> 6
				),

				array(
						"name" 	=> __("Entry Number", 'avia_framework' ),
						"desc" 	=> __("How many items should be displayed?", 'avia_framework' ),
						"id" 	=> "items",
						"type" 	=> "select",
						"std" 	=> "3",
						"subtype" => AviaHtmlHelper::number_array(1,100,1, array('All'=>'-1'))),

				array(
							"name" 	=> __("Pagination", 'avia_framework' ),
							"desc" 	=> __("Should a pagination be displayed?", 'avia_framework' ),
							"id" 	=> "paginate",
							"type" 	=> "select",
							"std" 	=> "no",
							"subtype" => array(
								__('yes',  'avia_framework' ) =>'yes',
								__('no',  'avia_framework' ) =>'no')),

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
		function shortcode_handler( $atts, $content = "", $shortcodename = "", $meta = "" )
		{
			$atts =  shortcode_atts(array(
											'categories' 	=> "",
											'items' 		=> "3",
											'paginate'		=> "no",
										), $atts, $this->config['shortcode'] );
			
			$output = "";
			$posts 	= $this->query_entries( $atts );
			$entries = $posts->posts;
			
			if( class_exists( 'Tribe__Events__Pro__Main' ) )
			{
				$ecp = Tribe__Events__Pro__Main::instance();
				$ecp->disable_recurring_info_tooltip();
			}
			
			if ( ! empty( $entries ) )
			{	
				global $post;
				
				$default_id = $post->ID;
				$output .= "<div class='av-upcoming-events " . $meta['el_class'] . "'>";
				
				foreach( $entries as $entry )
				{	
					$class  = "av-upcoming-event-entry";
					$image  = get_the_post_thumbnail( $entry->ID, 'square', array( 'class' => 'av-upcoming-event-image' ) );
					$class .= ! empty( $image ) ? " av-upcoming-event-with-image" : " av-upcoming-event-without-image";
					$title  = get_the_title( $entry->ID );
					$link	= get_permalink( $entry->ID );
					
					$post->ID = $entry->ID; //temp set of the post id so that tribe fetches the correct price symbol
					$price  = tribe_get_cost( $entry->ID, true );
					$venue  = tribe_get_venue( $entry->ID );
					$post->ID = $default_id;
					
					$output .=	"<a href='{$link}' class='{$class}'>";
					
					if( $image )  
					{
						$output .=	$image;
					}
					
					$output .=		"<span class='av-upcoming-event-data'>";
					$output .=			"<h4 class='av-upcoming-event-title'>{$title}</h4>";
					$output .=			"<span class='av-upcoming-event-meta'>";
					$output .=				"<span class='av-upcoming-event-schedule'>" . tribe_events_event_schedule_details($entry) . "</span>";
					
					if( $price )	
					{
						$output .=			"<span class='av-upcoming-event-cost'>{$price}</span>";
					}
					if( $price && $venue )	
					{
						$output .=				" - ";	
					}
					if( $venue )	
					{
						$output .=			"<span class='av-upcoming-event-venue'>{$venue}</span>";
					}
							
					$output .=				apply_filters( 'avf_upcoming_event_extra_data', '', $entry );
					$output .=			"</span>";
					$output .=		"</span>";
					$output .=	"</a>";
				}
				
				if( $atts['paginate'] == "yes" && $avia_pagination = avia_pagination( $posts->max_num_pages, 'nav' ) )
				{
					$output .= "<div class='pagination-wrap pagination-" . Tribe__Events__Main::POSTTYPE . "'>{$avia_pagination}</div>";
				}
				
				$output .= "</div>";
			}
			
			if( class_exists( 'Tribe__Events__Pro__Main' ) )
			{
				// Re-enable recurring event info
				$ecp->enable_recurring_info_tooltip();	
			}
			
			return $output;
		}
		
		/**
		 * 
		 * @since < 4.0
		 * @param array $params
		 * @return WP_Query
		 */
		protected function query_entries( $params = array() )
		{
			$query = array();
			
			if( empty( $params ) ) 
			{
				$params = $this->atts;
			}

			if( ! empty( $params['categories'] ) )
			{
				//get the portfolio categories
				$terms 	= explode( ',', $params['categories'] );
			}

			$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
			if( ! $page || $params['paginate'] == 'no') 
			{
				$page = 1;
			}
			
			$start_date = date( 'Y-m-d' );

			//if we find categories perform complex query, otherwise simple one
			if( isset( $terms[0] ) && ! empty( $terms[0] ) && ! is_null( $terms[0] ) && $terms[0] != "null" )
			{
				$query = array(	
								'paged'				=> $page,
								'eventDisplay'		=> 'list',
								'posts_per_page'	=> $params['items'],
								'start_date'		=> $start_date,
								'tax_query'			=> array( 	
														array( 	'taxonomy' 	=> Tribe__Events__Main::TAXONOMY,
																'field' 	=> 'id',
																'terms' 	=> $terms,
																'operator' 	=> 'IN'
															)
														)
							);
			}
			else
			{
				$query = array(	
								'paged'				=> $page, 
								'posts_per_page'	=> $params['items'],
								'start_date'		=> $start_date,
								'eventDisplay'		=> 'list'
							);
			}

			/**
			 * @since < 4.0
			 * @return array 
			 */
			$query = apply_filters( 'avia_tribe_events_upcoming', $query, $params );

			return tribe_get_events( $query , true);
		}
		
	}
}



