<?php

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


//if either calendar plugin or modified version of the plugin that is included in the theme is available we can make use of it, otherwise return

if( ! class_exists( 'Tribe__Events__Main', false ) )
{
	return false;
}

define( 'AVIA_EVENT_PATH', AVIA_BASE . 'config-events-calendar/' );

include( 'event-mod-css-dynamic.php');


//register my own styles
if( ! function_exists( 'avia_events_register_assets' ) )
{
	if( ! is_admin() )
	{
		add_action( 'wp_enqueue_scripts', 'avia_events_register_assets', 15 );
	}

	function avia_events_register_assets( $styleUrl )
	{
		$vn = avia_get_theme_version();
		$min_css = avia_minify_extension( 'css' );

		wp_enqueue_style( 'avia-events-cal', AVIA_BASE_URL . "config-events-calendar/event-mod{$min_css}.css", [], $vn );
	}
}


//register own default template
if( ! function_exists( 'avia_events_template_paths' ) )
{
	add_action( 'tribe_events_template', 'avia_events_template_paths', 10, 2 );

	function avia_events_template_paths( $file, $template )
	{
		$redirect = array( 'default-template.php', 'single-event.php', 'pro/map.php' );

		if( in_array( $template, $redirect ) )
		{
			$file = AVIA_EVENT_PATH . 'views/' . $template;

			/**
			 * https://github.com/KriesiMedia/wp-themes/issues/1676
			 *
			 * with 4.2.5 we added a better support for mobile view, which broke output of this plugin. We revert to old style.
			 *
			 * @since 4.2.7
			 */
			if( class_exists( 'Tribe__Tickets_Plus__Main', false ) )
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

if( ! function_exists( 'avia_events_v2_template_paths' ) )
{
	/**
	 *
	 * @param array $folders
	 * @return array
	 */
	function avia_events_v2_template_paths( $folders )
	{
		$tec_path = AVIA_EVENT_PATH . 'views/v2/';

		/*
		 * Custom loading location for overriding The Events Calendar's templates from the theme.
		 */
		$folders['av_tec_v2_templates'] = [
							'id'       => 'av_tec_v2_templates',
							'priority' => 5, // TEC is 20, ET is 17, so do something earlier, like 5
							'path'     => $tec_path,
						];

		return $folders;
	}

	add_filter( 'tribe_template_path_list', 'avia_events_v2_template_paths' );
}

/*
if( ! function_exists( 'avia_events_template_paths_v2' ) )
{
	add_filter( 'tribe_template_file', 'avia_events_template_paths_v2', 10, 2 );

	/**
	 * Register default v2 templates
	 * https://github.com/KriesiMedia/wp-themes/issues/3088
	 *
	 * @since 4.8.2
	 * @param string $found_file
	 * @param array $name
	 * @return string
	 *//*
	function avia_events_template_paths_v2( $found_file, $name )
	{
		if( ! is_array( $name ) || empty( $name ) )
		{
			return $found_file;
		}

		if( $name[0] == 'default-template' && is_single() )
		{
			$found_file = AVIA_EVENT_PATH . 'views/' . $name[0] . '.php';
		}

		return $found_file;
	}
}
*/




//remove ability to change some of the avialble options (eg: template choice)

if( ! function_exists( 'avia_events_perma_options' ) )
{
	add_action( 'option_tribe_events_calendar_options', 'avia_events_perma_options', 10 );

	function avia_events_perma_options( $options )
	{
		$edit_elements = array(
							'tribeEventsTemplate'	=> '',
							'stylesheetOption'		=> 'full' ,
							'tribeDisableTribeBar'	=> false
						);				// stylesheetOption: skeleton, full, tribe

		$options = array_merge( $options, $edit_elements );

		return $options;
	}
}

//edit/remove some of the options from general tab
if( ! function_exists( 'avia_events_general_tab' ) )
{
	add_action( 'tribe_general_settings_tab_fields', 'avia_events_general_tab', 10 );

	function avia_events_general_tab( $options )
	{
		$edit_elements = array(
							'info-start'	=> array( 'html' => '<div id="modern-tribe-info">' ),
							'upsell-info',
							'upsell-info',
							'donate-link-info',
							'donate-link-pro-info',
							'donate-link-heading',
							'donate-link',
							'info-end'		=> array( 'html' => avia_tribe_ref() . '</div>' ) );

		$options = avia_events_modify_options( $options, $edit_elements );

		return $options;
	}
}

//edit/remove some of the options from display tab
if( ! function_exists( 'avia_events_display_tab' ) )
{
	add_action( 'tribe_display_settings_tab_fields', 'avia_events_display_tab', 10 );

	function avia_events_display_tab( $options )
	{
		$edit_elements = array(
							'info-start',
							'info-box-title',
							'info-box-description',
							'info-end',
							'stylesheetOption',
							'tribeEventsTemplate',
							'tribeDisableTribeBar'
						);

		$options = avia_events_modify_options( $options, $edit_elements );

		return $options;
	}
}


if( ! function_exists( 'avia_events_modify_options' ) )
{
	function avia_events_modify_options( $options, $edit_elements )
	{
		foreach( $edit_elements as $key => $element )
			{
				if( is_array( $element ) )
				{
					$options[ $key ] = array_merge_recursive( $options, $element );
				}
				else
				{
					if( array_key_exists( $element, $options ) )
					{
						unset( $options[ $element ] );
					}
				}
			}

			return $options;
	}
}


if( ! function_exists( 'avia_events_upsell' ) )
{
	$tec = Tribe__Events__Main::instance();

	remove_action( 'tribe_events_cost_table', array( $tec, 'maybeShowMetaUpsell' ) );
	add_action( 'tribe_events_cost_table', 'avia_events_upsell', 10 );

	function avia_events_upsell()
	{
		if( ! class_exists( 'Tribe__Events__Pro__Main', false ) )
		{

		?><tr class="eventBritePluginPlug">
		<td colspan="2" class="tribe_sectionheader">
		<h4><?php _e( 'Additional Functionality', 'avia_framework' ); ?></h4>
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

if( ! function_exists( 'avia_tribe_ref' ) )
{
	function avia_tribe_ref()
	{
		if( class_exists( 'Tribe__Events__Pro__Main', false ) )
		{
			return '';
		}

		$output = '<p>';
		$output .= __( 'Looking for additional functionality including recurring events, ticket sales, publicly submitted events, new views and more?', 'avia_framework' ) . ' ';
		$output .=  __( 'Check out the', 'avia_framework' ).
					' <a href="https://theeventscalendar.pxf.io/pro">' .
					__( 'available add-ons', 'avia_framework' ).
					'</a>';

		$output .= '</p>';

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
					$entry[ $key ]->ID = $event->ID;
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


if( ! function_exists( 'avia_events_breadcrumb') )
{
	add_filter( 'avia_breadcrumbs_trail', 'avia_events_breadcrumb' );

	/**
	 * modfiy breadcrumb navigation
	 *
	 * @param array $trail
	 * @return array
	 */
	function avia_events_breadcrumb( $trail )
	{
		global $avia_config, $wp_query;

		if( is_404() && isset( $wp_query ) && ! empty( $wp_query->tribe_is_event ) )
		{
			$events = __( 'Events', 'avia_framework' );
			$events_link = '<a href="' . tribe_get_events_link() . '">' . $events . '</a>';
			$last = array_pop( $trail );
			$trail[] = $events_link;
			$trail['trail_end'] = __( 'No Events Found', 'avia_framework' );
		}

		if( ( isset( $avia_config['currently_viewing'] ) && $avia_config['currently_viewing'] == 'events' ) || tribe_is_month() || get_post_type() === Tribe__Events__Main::POSTTYPE || is_tax( Tribe__Events__Main::TAXONOMY ) )
		{
			$events = __( 'Events', 'avia_framework' );
			$events_link = '<a href="' . tribe_get_events_link() . '" title="' . $events . '">' . $events . '</a>';

			if( is_tax( Tribe__Events__Main::TAXONOMY ) )
			{
				$last = array_pop( $trail );
				$trail[] = $events_link;
				$trail[] = $last;
			}
			else if( tribe_is_month() || ( tribe_is_upcoming() && ! is_singular() ) )
			{
				$trail[] = $events_link;
			}
			else if( tribe_is_event() )
			{
				$last = array_pop( $trail );
				$trail[] = $events_link;
				$trail[] = $last;
			}

			if( isset( $avia_config['events_trail'] ) )
			{
				$trail = $avia_config['events_trail'] ;
			}
		}

		return $trail;
	}

}


/*additional markup*/
if( ! function_exists( 'avia_events_content_wrap' ) )
{
	add_action( 'tribe_events_before_the_event_title', 'avia_events_content_wrap', 10 );

	function avia_events_content_wrap()
	{
		echo "<div class='av-tribe-events-content-wrap'>";
	}
}

if( ! function_exists( 'avia_events_open_outer_wrap' ) )
{
	add_action( 'tribe_events_after_the_event_title', 'avia_events_open_outer_wrap', 10 );

	function avia_events_open_outer_wrap()
	{
		echo "<div class='av-tribe-events-outer-content-wrap'>";
	}
}

if( ! function_exists( 'avia_events_open_inner_wrap' ) )
{
	add_action( 'tribe_events_after_the_meta', 'avia_events_open_inner_wrap', 10 );

	function avia_events_open_inner_wrap()
	{
		echo "<div class='av-tribe-events-inner-content-wrap'>";
	}
}


if( ! function_exists( 'avia_events_close_div' ) )
{
	/*call 3 times, once for wrappper, outer and inner wrap*/
	add_action( 'tribe_events_after_the_content', 'avia_events_close_div', 1000 );
	add_action( 'tribe_events_after_the_content', 'avia_events_close_div', 1001 );
	add_action( 'tribe_events_after_the_content', 'avia_events_close_div', 1003 );

	function avia_events_close_div()
	{
		echo '</div>';
	}
}

if( ! function_exists( 'avia_events_modify_event_publish_date' ) )
{
	/**
	 * With Tribe 6.0 the events date is no longer shown.
	 *
	 * @since 5.3
	 * @param string $date
	 * @param int $post_id
	 * @param string $date_format
	 * @return string
	 */
	function avia_events_modify_event_publish_date( $date, $post_id, $date_format = '' )
	{
		if( ! tribe_is_event( $post_id ) )
		{
			return $date;
		}

		/**
		 *
		 * @since 5.3
		 * @param string $date_format
		 * @param int $post_id
		 * @return string
		 */
		$date_format = apply_filters( 'avf_events_single_event_publish_date_format', $date_format, $post_id );

		//	fallback: only display date if no $date_format
		$display_time = ! empty( $date_format );

		$event_date = tribe_get_start_date( $post_id, $display_time, $date_format );

		return is_null( $event_date ) ? $date : $event_date;
	}

	add_filter( 'avf_loop_index_meta_time', 'avia_events_modify_event_publish_date', 10, 3 );
}

if( ! function_exists( 'avia_events_cpt_support_post_types' ) )
{
	/**
	 * Remove CPT tribe_events from list to allow modify edit table and default term settings
	 *
	 * @since 6.0
	 * @param WP_Post_Type[] $pt_objs
	 * @return array
	 */
	function avia_events_cpt_support_post_types( $pt_objs )
	{
		if( ! empty( $pt_objs ) )
		{
			unset( $pt_objs['tribe_events'] );
		}

		return $pt_objs;
	}
}

add_filter( 'avf_cpt_support_post_types', 'avia_events_cpt_support_post_types', 10, 1 );


/*PRO PLUGIN*/
if ( ! class_exists( 'Tribe__Events__Pro__Main', false ) )
{
	return false;
}

/*move related events*/

if( ! class_exists( 'Tribe__Tickets_Plus__Main', false ) )
{
$tec = Tribe__Events__Pro__Main::instance();

remove_action( 'tribe_events_single_event_after_the_meta', array( $tec, 'register_related_events_view' ) );
add_action( 'tribe_events_single_event_after_the_content', array( $tec, 'register_related_events_view' ) );
}

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

	add_filter( 'avia_masonry_entries_query', 'avia_events_modify_recurring_event_query', 10, 2 );
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

	add_action( 'ava_after_masonry_entries_query', 'avia_events_reset_recurring_event_query', 10 );
}

