<?php

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


//if either calendar plugin or modified version of the plugin that is included in the theme is available we can make use of it, otherwise return

if ( !class_exists( 'Tribe__Events__Main' ) ) return false;

define( 'AVIA_EVENT_PATH', AVIA_BASE . 'config-events-calendar/' );

include( 'event-mod-css-dynamic.php');


//register my own styles
if(!function_exists('avia_events_register_assets'))
{
	if(!is_admin()){ add_action('wp_enqueue_scripts', 'avia_events_register_assets',15); }
	
	function avia_events_register_assets($styleUrl)
	{
		wp_enqueue_style( 'avia-events-cal', AVIA_BASE_URL.'config-events-calendar/event-mod.css');
	}
}


//register own default template
if(!function_exists('avia_events_tempalte_paths'))
{
	add_action('tribe_events_template', 'avia_events_tempalte_paths', 10, 2);
	
	function avia_events_tempalte_paths($file, $template)
	{
		$redirect = array('default-template.php' , 'single-event.php' , 'pro/map.php' );
		
		if(in_array($template, $redirect))
		{
			$file = AVIA_EVENT_PATH . "views/" . $template;
			
			/**
			 * https://github.com/KriesiMedia/wp-themes/issues/1676
			 * 
			 * with 4.2.5 we added a better support for mobile view, which broke output of this plugin. We revert to old style.
			 * 
			 * @since 4.2.7
			 */
			if( class_exists( 'Tribe__Tickets_Plus__Main' ) )
			{
				if( 'single-event.php' == $template )
				{
					$file = AVIA_EVENT_PATH . 'views/single-event-no-mobile.php';
				}
			}
		}
		
		return $file;
	}
}


//remove ability to change some of the avialble options (eg: template choice)

if(!function_exists('avia_events_general_tab'))
{
	add_action('option_tribe_events_calendar_options', 'avia_events_perma_options', 10);
	
	function avia_events_perma_options($options)
	{
		$edit_elements 	= array('tribeEventsTemplate' => '', 'stylesheetOption' => 'full' , 'tribeDisableTribeBar'=>false); // stylesheetOption: skeleton, full, tribe
		$options= array_merge($options, $edit_elements);
		
		return $options;
	}
}

//edit/remove some of the options from general tab
if(!function_exists('avia_events_general_tab'))
{
	add_action('tribe_general_settings_tab_fields', 'avia_events_general_tab', 10);
	
	function avia_events_general_tab($options)
	{
		$edit_elements 	= array('info-start' => array('html' => '<div id="modern-tribe-info">'), 
				'upsell-info', 'upsell-info', 'donate-link-info', 'donate-link-pro-info', 'donate-link-heading', 'donate-link', 'info-end'=> array('html'=> avia_tribe_ref()."</div>"));
				
		$options 		= avia_events_modify_options($options, $edit_elements);
		return $options;
	}
}

//edit/remove some of the options from display tab
if(!function_exists('avia_events_display_tab'))
{
	add_action('tribe_display_settings_tab_fields', 'avia_events_display_tab', 10);
	
	function avia_events_display_tab($options)
	{
		$edit_elements 	= array('info-start', 'info-box-title', 'info-box-description', 'info-end', 'stylesheetOption', 'tribeEventsTemplate', 'tribeDisableTribeBar'); 
		$options 		= avia_events_modify_options($options, $edit_elements);
		
		return $options;
	}
}



if(!function_exists('avia_events_modify_options'))
{
	function avia_events_modify_options($options, $edit_elements)
	{
		foreach($edit_elements as $key => $element)
			{
				if(is_array($element))
				{
					$options[$key] = array_merge_recursive($options, $element);
				}
				else
				{
					if(array_key_exists($element, $options)) unset($options[$element]);
				}
			}
			
			return $options;
	}
}


if(!function_exists('avia_events_upsell'))
{
	$tec = Tribe__Events__Main::instance();
	remove_action( 'tribe_events_cost_table', array($tec, 'maybeShowMetaUpsell'));
	add_action( 'tribe_events_cost_table', 'avia_events_upsell', 10);

	function avia_events_upsell()
	{	
		if(!class_exists( 'Tribe__Events__Pro__Main' )){
	
		?><tr class="eventBritePluginPlug">
		<td colspan="2" class="tribe_sectionheader">
		<h4><?php _e('Additional Functionality', 'avia_framework'); ?></h4>
		</td>
		</tr>
		<tr class="eventBritePluginPlug">
		<td colspan="2">
		<?php echo avia_tribe_ref(); ?>
		</td>
		</tr><?php
		
		}
	}
}

if(!function_exists('avia_tribe_ref'))
{

function avia_tribe_ref()
{
	if(class_exists( 'Tribe__Events__Pro__Main' )) return "";

	$output = "<p>";
	$output .= __('Looking for additional functionality including recurring events, ticket sales, publicly submitted events, new views and more?', 'avia_framework' )." ";
	$output .=  __('Check out the', 'avia_framework' ).
				" <a href='http://mbsy.co/6cr37'>".
				__('available add-ons', 'avia_framework' ).
				"</a>"; 
	
	$output .= "</p>";
	return $output;
	
	}
}


if( ! function_exists( 'avia_events_custom_post_nav' ) )
{
	add_filter( 'avf_post_nav_entries', 'avia_events_custom_post_nav', 10, 3 );

	/**
	 * Modfiy post navigation
	 * 
	 * @since < 4.0    modified 4.5.6
	 * @param array $entry
	 * @param array $settings
	 * @param array $queried_entries
	 * @return array
	 */
	function avia_events_custom_post_nav( array $entry, array $settings, array $queried_entries )
	{
		if( tribe_is_event() )
		{
			$final = $links = array();
			$entry = array(
							'prev'	=> '',
							'next'	=> ''
						);
			
			if( version_compare( Tribe__Events__Main::VERSION, '4.6.22', '>=' ) )
			{
				$old_prev = tribe( 'tec.adjacent-events' )->previous_event_link;
				$old_next = tribe( 'tec.adjacent-events' )->next_event_link;

				tribe( 'tec.adjacent-events' )->previous_event_link = '';
				tribe( 'tec.adjacent-events' )->next_event_link = '';
			}
			
			$links['prev'] = tribe_get_prev_event_link( '{-{%title%}-}' );
			$links['next'] = tribe_get_next_event_link( '{-{%title%}-}' );
						
			foreach( $links as $key => $link )
			{
				if( empty( $link ) )
				{
					continue;
				}
				
				preg_match( '/^<a.*?href=(["\'])(.*?)\1.*$/', $link, $m );
				$final[ $key ]['link_url'] = ! empty( $m[2] ) ? $m[2] : '';
				
				preg_match( '/\{\-\{(.+)\}\-\}/', $link, $m2 );
				$final[ $key ]['link_text'] = ! empty( $m2[1] ) ? $m2[1] : '';
				
				if( ! empty( $final[ $key ]['link_text'] ) )
				{
					$mode = 'prev' == $key ? 'previous' : 'next';
					$event = tribe( 'tec.adjacent-events' )->get_closest_event( $mode );
					
					$entry[ $key ] = new stdClass();
					$entry[ $key ]->av_custom_link  = $final[ $key ]['link_url'];
					$entry[ $key ]->av_custom_title = $final[ $key ]['link_text'];
					$entry[ $key ]->av_custom_image = get_the_post_thumbnail( $event->ID, 'thumbnail' );
				}
			}
			
			if( version_compare( Tribe__Events__Main::VERSION, '4.6.22', '>=' ) )
			{
				tribe( 'tec.adjacent-events' )->previous_event_link = $old_prev;
				tribe( 'tec.adjacent-events' )->next_event_link = $old_next;
			}
		}
		
		return $entry;
	}
}

/*modfiy breadcrumb navigation*/
if(!function_exists('avia_events_breadcrumb'))
{
	add_filter('avia_breadcrumbs_trail','avia_events_breadcrumb');

	function avia_events_breadcrumb($trail)
	{ 
		global $avia_config, $wp_query;
		
		if(is_404() && isset($wp_query) && !empty($wp_query->tribe_is_event))
		{
			$events = __('Events','avia_framework');
			$events_link = '<a href="'.tribe_get_events_link().'">'.$events.'</a>';
			$last = array_pop($trail);
			$trail[] = $events_link;
			$trail['trail_end'] = __('No Events Found','avia_framework');
		}
		
		if((isset($avia_config['currently_viewing']) && $avia_config['currently_viewing'] == 'events') || tribe_is_month() || get_post_type() === Tribe__Events__Main::POSTTYPE || is_tax(Tribe__Events__Main::TAXONOMY) )
		{	
			$events = __('Events','avia_framework');
			$events_link = '<a href="'.tribe_get_events_link().'" title="' . $events . '">' . $events . '</a>';
			
			if(is_tax(Tribe__Events__Main::TAXONOMY) )
			{
				$last = array_pop($trail);
				$trail[] = $events_link;
				$trail[] = $last;
			}
			else if(tribe_is_month() || (tribe_is_upcoming() && !is_singular())) 
			{
				$trail[] = $events_link;
			}
			else if(tribe_is_event()) 
			{
				$last = array_pop($trail);
				$trail[] = $events_link;
				$trail[] = $last;
			}

			if(isset($avia_config['events_trail'] )) $trail = $avia_config['events_trail'] ;
		}
			
		return $trail;
	}

}


/*additional markup*/
if(!function_exists('avia_events_content_wrap'))
{
	add_action( 'tribe_events_before_the_event_title', 'avia_events_content_wrap', 10);

	function avia_events_content_wrap()
	{
		echo "<div class='av-tribe-events-content-wrap'>";
	}
}

if(!function_exists('avia_events_open_outer_wrap'))
{
	add_action( 'tribe_events_after_the_event_title', 'avia_events_open_outer_wrap', 10);

	function avia_events_open_outer_wrap()
	{
		echo "<div class='av-tribe-events-outer-content-wrap'>";
	}
}

if(!function_exists('avia_events_open_inner_wrap'))
{
	add_action( 'tribe_events_after_the_meta', 'avia_events_open_inner_wrap', 10);

	function avia_events_open_inner_wrap()
	{
		echo "<div class='av-tribe-events-inner-content-wrap'>";
	}
}


if(!function_exists('avia_events_close_div'))
{
	/*call 3 times, once for wrappper, outer and inner wrap*/
	add_action( 'tribe_events_after_the_content', 'avia_events_close_div', 1000);
	add_action( 'tribe_events_after_the_content', 'avia_events_close_div', 1001);
	add_action( 'tribe_events_after_the_content', 'avia_events_close_div', 1003);

	function avia_events_close_div()
	{
		echo "</div>";
	}
}





/*PRO PLUGIN*/
if ( !class_exists( 'Tribe__Events__Pro__Main' ) ) return false;

/*move related events*/
$tec = Tribe__Events__Pro__Main::instance();
remove_action( 'tribe_events_single_event_after_the_meta', array( $tec, 'register_related_events_view' ) );
add_action( 'tribe_events_single_event_after_the_content', array( $tec, 'register_related_events_view' ) );


if( ! function_exists( 'avia_events_modify_recurring_event_query' ) )
{
	/**
	 * Selecting checkbox Recurring event instances in Events -> Settings -> General might might break our queries because of GROUP BY clause.
	 * Reason is probably if multiple posttypes are part of the query.
	 * 
	 * @added_by Günter
	 * @since 4.2.4
	 * @param array $query
	 * @param array $params
	 * @return array
	 */
	function avia_events_modify_recurring_event_query( array $query, array $params )
	{
		remove_filter( 'posts_request', array( 'Tribe__Events__Pro__Recurrence__Queries', 'collapse_sql' ), 10, 2 );

		return $query;
	}
}

if( ! function_exists( 'avia_events_reset_recurring_event_query' ) )
{
	/**
	 * Add the previously removed filter again
	 * 
	 * @added_by Günter
	 * @since 4.2.4
	 */
	function avia_events_reset_recurring_event_query()
	{
		if( false === has_filter( 'posts_request', array( 'Tribe__Events__Pro__Recurrence__Queries', 'collapse_sql' ) ) )
		{
			add_filter( 'posts_request', array( 'Tribe__Events__Pro__Recurrence__Queries', 'collapse_sql' ), 10, 2 );
		}
	}
}
	
add_filter( 'avia_masonry_entries_query', 'avia_events_modify_recurring_event_query', 10, 2 );
add_action( 'ava_after_masonry_entries_query', 'avia_events_reset_recurring_event_query', 10 );
	
