<?php
/**
 * Events Countdown
 * 
 * Display a countdown to the next upcoming event
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'Tribe__Events__Main' ) )
{
	if(!function_exists('av_countdown_events_fallback'))
	{
		function av_countdown_events_fallback()
		{
			return "<p>Please install the <a href='https://wordpress.org/plugins/the-events-calendar/'>The Events Calendar</a> or <a href='http://mbsy.co/6cr37'>The Events Calendar Pro</a> Plugin to display the countdown</p>";
		}
		
		add_shortcode('av_events_countdown', 'av_countdown_events_fallback');
	}
	
	return;
}

 
if ( ! class_exists( 'avia_sc_events_countdown' ) ) 
{
	
	class avia_sc_events_countdown extends aviaShortcodeTemplate
	{
		
			/**
			 *
			 * @var array 
			 */
			protected $time_array;
			
			
			/**
			 * UTC startdate of first event
			 * 
			 * @since 4.5.6
			 * @var string 
			 */
			protected $start_date_utc;


			/**
			 * 
			 * @since 4.2.1
			 */
			public function __destruct() 
			{
				parent::__destruct();
				
				unset( $this->time_array );
			}
			
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				/**
				 * inconsistent behaviour up to 4.2: a new element was created with a close tag, after editing it was self closing !!!
				 * @since 4.2.1: We make new element self closing now because no id='content' exists.
				 */
				$this->config['self_closing']	=	'yes';
				
				$this->config['name']		= __( 'Events Countdown', 'avia_framework' );
				$this->config['tab']		= __( 'Plugin Additions', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-countdown.png';
				$this->config['order']		= 14;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_events_countdown';
				$this->config['tooltip'] 	= __( 'Display a countdown to the next upcoming event', 'avia_framework' );
				$this->config['disabling_allowed'] = true;
				
				$this->time_array = array(
								__( 'Second',  	'avia_framework' ) 	=> '1',
								__( 'Minute',  	'avia_framework' ) 	=> '2',	
								__( 'Hour',  	'avia_framework' ) 	=> '3',
								__( 'Day',  	'avia_framework' ) 	=> '4',
								__( 'Week',  	'avia_framework' ) 	=> '5',
								/*
								__( 'Month',  	'avia_framework' ) 	=>'6',
								__( 'Year',  	'avia_framework' ) 	=>'7'
								*/
							);
							
				$this->start_date_utc = '';
			}
			
			function extra_assets()
			{
				//load css
				wp_enqueue_style( 'avia-module-countdown' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/countdown/countdown.css' , array('avia-layout'), false );
				
				//load js
				wp_enqueue_script( 'avia-module-countdown' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/countdown/countdown.js' , array('avia-shortcodes'), false, TRUE );
			}
			
			/**
			 * 
			 * @since < 4.0
			 * @param int $offset
			 * @return WP_Query
			 */
			protected function fetch_upcoming( $offset = 0 )
			{
				$query = array(
								'paged'				=> 1, 
								'posts_per_page'	=> 1, 
								'eventDisplay'		=> 'list', 
								'offset'			=> $offset,
								'start_date'		=> date( 'Y-m-d' )
							);
				
				$upcoming = Tribe__Events__Query::getEvents( $query, true );
				
				return $upcoming;
			}
			
			/**
			 * 
			 * @since < 4.0
			 * @param WP_Query $next
			 * @return boolean
			 */
			protected function already_started( WP_Query $next )
			{
				$this->start_date_utc = '';
				
				//	backwards compatibility
				if( empty( $next->posts[0]->EventStartDate ) && empty( $next->posts[0]->event_date ) ) 
				{
					return true;
				}
				
				/**
				 * Compare UTC times ( https://www.php.net/manual/en/function.time.php#100220 )
				 */
				$today = date( 'Y-m-d H:i:s' );
				$this->start_date_utc = get_post_meta( $next->posts[0]->ID, '_EventStartDateUTC', true );
				
				if( empty( $this->start_date_utc ) )
				{
					return true;
				}
				 
				if( $today < $this->start_date_utc )
				{
					return false;
				}
				
				return true;
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
							"name" 	=> __("Smallest time unit", 'avia_framework' ),
							"desc" 	=> __("The smallest unit that will be displayed", 'avia_framework' ),
							"id" 	=> "min",
							"type" 	=> "select",
							"std" 	=> "1",
							"subtype" => $this->time_array),
					
					
					array(	
							"name" 	=> __("Largest time unit", 'avia_framework' ),
							"desc" 	=> __("The largest unit that will be displayed", 'avia_framework' ),
							"id" 	=> "max",
							"type" 	=> "select",
							"std" 	=> "5",
							"subtype" => $this->time_array),
					
					
					
							
					array(
							"name" 	=> __("Text Alignment", 'avia_framework' ),
							"desc" 	=> __("Choose here, how to align your text", 'avia_framework' ),
							"id" 	=> "align",
							"type" 	=> "select",
							"std" 	=> "center",
							"subtype" => array(
												__('Center',  'avia_framework' ) =>'av-align-center',
												__('Right',  'avia_framework' ) =>'av-align-right',
												__('Left',  'avia_framework' ) =>'av-align-left',
												)
							),
							
					array(	"name" 	=> __("Number Font Size", 'avia_framework' ),
							"desc" 	=> __("Size of your numbers in Pixel", 'avia_framework' ),
				            "id" 	=> "size",
				            "type" 	=> "select",
				            "subtype" => AviaHtmlHelper::number_array(20,90,1, array( __("Default Size", 'avia_framework' )=>'')),
				            "std" => ""),
				   
				   array(
							"name" 	=> __("Display Event Title?", 'avia_framework' ),
							"desc" 	=> __("Choose here, if you want to display the event title", 'avia_framework' ),
							"id" 	=> "title",
							"type" 	=> "select",
							"std" 	=> "",
							"subtype" => array(
												__('No Title, timer only',  'avia_framework' ) =>'',
												__('Title on top',  'avia_framework' ) 	=>'top',
												__('Title below',  'avia_framework' ) 	=>'bottom',
												)
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
							"name" 	=> __("Colors", 'avia_framework' ),
							"desc" 	=> __("Choose the colors here", 'avia_framework' ),
							"id" 	=> "style",
							"type" 	=> "select",
							"std" 	=> "center",
							"subtype" => array(
												__('Default',	'avia_framework' ) 	=>'av-default-style',
												__('Theme colors',	'avia_framework' ) 	=>'av-colored-style',
												__('Transparent Light', 'avia_framework' ) 	=>'av-trans-light-style',
												__('Transparent Dark',  'avia_framework' )  =>'av-trans-dark-style',
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
			public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
			{
				$find_post = true;
				$offset = 0;
				
				while( $find_post )
				{
					$next = $this->fetch_upcoming( $offset );
					$offset ++;
					
					if( empty( $next->posts[0] ) || ! $this->already_started( $next ) )
					{
						$find_post = false;
					}
				}
				
				if( ! empty( $next->posts[0]->EventStartDate ) )
				{
					//	backwards compatibility
					$event_date = $next->posts[0]->EventStartDate;
				}
				else if( ! empty( $next->posts[0]->event_date ) )
				{
					$event_date = $next->posts[0]->event_date;
				}
				else
				{
					$event_date = '';
				}
				
				if( empty( $next->posts[0] ) || empty( $event_date ) || empty( $this->start_date_utc ) ) 
				{
					return '';
				}
				
				$events_date = explode( ' ', $this->start_date_utc );
				
				if( isset( $events_date[0] ) )
				{
					$atts['date'] = date( 'm/d/Y', strtotime( $events_date[0] ) );
				}
				
				if( isset( $events_date[1] ) )
				{
					$events_date = explode( ':', $events_date[1] );
					$atts['hour'] = $events_date[0];
					$atts['minute'] = $events_date[1];
				}
				
				$atts['link'] 	= get_permalink( $next->posts[0]->ID );
				$title 			= get_the_title( $next->posts[0]->ID );
				
				if( ! empty( $atts['title'] ) )
				{
					$atts['title']  = array( $atts['title'] => __( 'Upcoming','avia_framework' ) . ': ' . $title );
				}
				
				$atts['timezone'] = 'UTC';
				
				$timer  = new avia_sc_countdown( $this->builder );
				$output = $timer->shortcode_handler( $atts , $content, $shortcodename, $meta );
				
				return $output;
			}
	}
}

