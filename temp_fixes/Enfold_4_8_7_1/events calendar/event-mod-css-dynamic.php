<?php

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

/**
 * IMPORTANT:
 * ==========
 *
 * class vevent hentry has been removed with 4.5.5.  https://github.com/KriesiMedia/wp-themes/issues/2157
 * If we do not encounter any problems related CSS can also be removed later.
 *
 * @added_by guenter
 */

//add color styles
if( ! function_exists( 'avia_events_dynamic_css' ) )
{
	add_filter( 'avia_dynamic_css_output', 'avia_events_dynamic_css', 10, 2 );

	function avia_events_dynamic_css( $output, $color_set )
	{
		/*color sets*/
		foreach( $color_set as $key => $colors ) // iterates over the color sets: usually $key is either: header_color, main_color, footer_color, socket_color
		{
			$key = '.' . $key;
			extract( $colors );

			//	elements that will only appear in the main content area
			if( $key == '.main_color' )
			{
				$constant_font 	= avia_backend_calc_preceived_brightness( $primary, 230 ) ? '#ffffff' : $bg;
				$button_border  = avia_backend_calculate_similar_color( $primary, 'darker', 2 );
				$button_border2 = avia_backend_calculate_similar_color( $secondary, 'darker', 2 );
				$bg3 			= avia_backend_calculate_similar_color( $bg2, 'darker', 1 );

				$output .= "

				$key .tribe-events-single ul.tribe-related-events li, $key .tribe-week-grid-block div, $key .tribe-events-grid .tribe-grid-content-wrap .column,
				$key .tribe-events-loop .tribe-events-event-meta,  #top $key .tribe-events-list-event-title.entry-title, #top $key .tribe-events-list-event-title,
				$key #tribe-events-content table.tribe-events-calendar, $key #tribe-events-content .tribe-events-calendar td, body .datepicker.dropdown-menu,
				#top $key .tribe-events-tooltip, $key .recurring-info-tooltip, $key .vevent.tribe-events-photo-event .tribe-events-photo-event-wrap,
				body .datepicker table, body .datepicker table td, body .datepicker table th, #top #tribe-mobile-container .hentry.vevent,
				$key .tribe-events-venue .tribe-events-venue-meta, $key .tribe-mini-calendar-dayofweek, $key .tribe-mini-calendar td, $key .tribe-mini-calendar th
				{
					border-color: $border;
				}

				.single-tribe_events  $key #tribe-events-content .tribe-events-event-meta dt, $key .tribe-events-list-separator-month,
				$key .tribe-grid-allday .hentry.vevent>div, $key .tribe-grid-body div[id*='tribe-events-event-'] .hentry.vevent,
				#top.tribe-theme-enfold.single-tribe_events .tribe-events-single-event-title,
				.tribe-events-related-events-title,
				.tribe-events-single ul.tribe-related-events li .tribe-related-events-title,
				.tribe-common--breakpoint-medium.tribe-common .tribe-common-h4--min-medium, .tribe-events-single ul.tribe-related-events li,
				.tribe-events-meta-group .tribe-events-single-section-title, #top.tribe-theme-enfold.single-tribe_events .tribe-events-schedule h3
				{color:$heading;}

				#top $key .tribe-week-grid-hours{color: $color;}

				#top .tribe-events-content p,
				.tribe-events-content ol,
				.tribe-events-content p,
				.tribe-events-content ul,
				.tribe-common .tribe-common-b2 {
					color: $color;
				}

				$key #tribe-events-pg-template .tribe-events-notices, $key #tribe-events-pg-template .tribe-events-notices strong{
					background: $primary;
					color: $constant_font;
				}

				$key #tribe-events-bar, $key #tribe-events-bar:before, $key #tribe-events-bar:after, $key .tribe-grid-allday .hentry.vevent>div, $key .tribe-grid-body div[id*='tribe-events-event-'] .hentry.vevent, $key .tribe-mini-calendar-dayofweek, $key .tribe-mini-calendar-event .list-date{
					background: $bg3;
					border-color: $border;
				}

				body .datepicker.dropdown-menu,
				#top $key #tribe-bar-form input[type='text'], $key .tribe-mini-calendar td, $key .tribe-mini-calendar-event .list-date .list-dayname,
				#top $key .tribe-events-tooltip, $key .recurring-info-tooltip, $key .tribe-events-tooltip .tribe-events-arrow
				{
					background: $bg;
					color: $meta;
				}

				body .datepicker-dropdown:after{
					border-bottom-color:$bg;
				}

				$key .vevent.tribe-events-photo-event .tribe-events-photo-event-wrap, $key .av-tribe-events-content-wrap, $key #tribe-events .av-single-event-meta-bar-inner{
					background: $bg2;
				}

				$key .tribe-grid-allday, $key .tribe-week-grid-hours, $key .tribe-events-distance,
				body .datepicker table tr td.day, .datepicker table tr td span, #top #wrap_all $key td.tribe-events-othermonth,
				$key .tribe-events-calendar td.tribe-events-past div[id*='tribe-events-daynum-'], $key .tribe-events-calendar td.tribe-events-past div[id*='tribe-events-daynum-']>a
				{
					background: $bg2;
					color: $meta;
				}

				$key .tribe-bar-views-inner, $key #tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option a,
				$key .tribe-events-calendar div[id*='tribe-events-daynum-'], $key .tribe-events-calendar div[id*='tribe-events-daynum-'] a
				{
					background:$bg3;
					color: $color;
				}

				$key #tribe-events-content .tribe-events-calendar td, $key .tribe-week-grid-block,
				$key #tribe-bar-views .tribe-bar-views-list .tribe-bar-views-option a:hover{
					background:$bg;
				}

				.single-tribe_events $key  .tribe-events-cost, $key #tribe-events-content .tribe-events-calendar td,
				$key .tribe-events-adv-list-widget .tribe-events-event-cost, #top #wrap_all $key .tribe-events-event-meta a,
				$key .tribe-events-schedule .recurringinfo a, $key .tribe-related-event-info .recurringinfo a,
				#top .tribe-events-pro .tribe-events-pro-venue__meta-address-directions-link,
				#top .tribe-events-pro .tribe-events-pro-venue__meta-website-link,
				#top .tribe-events-pro .tribe-events-pro-organizer__meta-email-link,
				#top .tribe-events-pro .tribe-events-pro-organizer__meta-website-link{
					color: $primary;
				}

				body .datepicker table tr td span:hover,
				body .datepicker table tr td.day:hover, body .datepicker table tr td.day.focused,
				#top #wrap_all $key .tribe-events-button:hover, $key .tribe-events .tribe-events-c-ical__link:hover {
					background-color: $secondary;
					color:$constant_font;
					border-color:$button_border2;
				}

				$key .tribe-mini-calendar .tribe-events-has-events div[id*='daynum-'] a:before, $key .tribe-venue-widget-venue-name,
				$key .tribe-mini-calendar-nav span,
				body .datepicker table tr td.active.active, body .datepicker table tr td span.active.active,
				body .datepicker table tr td.active.active:hover, body .datepicker table tr td span.active.active:hover,
				body .datepicker thead tr:first-child th:hover, body .datepicker tfoot tr th:hover,
				#top $key .tribe-events-tooltip h3, $key div.tribe-countdown-text, $key .tribe-mini-calendar-nav td,
				$key .tribe-events-calendar th, $key .tribe-events-grid .tribe-grid-header , $key .tribe-events-grid .tribe-grid-header .column,
				#top #wrap_all $key .tribe-events-button, $key .tribe-events-list .tribe-events-event-cost span,
				$key .tribe-events-calendar td.tribe-events-present div[id*='tribe-events-daynum-'],
				$key .tribe-events-calendar td.tribe-events-present div[id*='tribe-events-daynum-']>a,
				$key .tribe-grid-allday .hentry.vevent>div, $key .tribe-grid-body div[id*='tribe-events-event-'] .hentry.vevent,
				$key .tribe-mini-calendar .tribe-events-has-events:hover a, $key .tribe-mini-calendar .tribe-events-has-events:hover a:hover,
				$key .tribe-mini-calendar .tribe-events-has-events.tribe-mini-calendar-today a, $key .tribe-events .tribe-events-c-ical__link
				{
					background-color: $primary;
					color:$constant_font;
					border-color:$button_border;
				}

				$key #tribe-events .time-details, $key .single-tribe_events .tribe-events-schedule,  .single-tribe_events $key .tribe-events-schedule h3,
				.single-tribe_events $key .tribe-events-event-meta dd, $key .recurringinfo, $key .tribe-mini-calendar-no-event, $key .tribe-mini-calendar-dayofweek,
				$key .tribe-mini-calendar-event .list-date .list-daynumber, $key .av-upcoming-event-data
				{
				color: $meta;
				}

				$key .tribe-mini-calendar .tribe-events-has-events div[id*='daynum-']:hover a:before{background-color: $constant_font;}

				.datepicker .datepicker-switch{color:$heading !important;}


				@media only screen and (max-width: 768px) {
					$key .tribe-events-sub-nav li a{
					background-color: $primary;
					color:$constant_font;
					}


					#top $key #tribe-events-content .tribe-events-calendar td, $key .tribe-events-calendar td div[id*='tribe-events-daynum-'], $key .tribe-events-calendar td div[id*='tribe-events-daynum-']>a{background:$bg;}
					#top $key #tribe-events-content td.tribe-events-past, #top $key #tribe-events-content td.tribe-events-othermonth{background:$bg2;}
					#top $key #tribe-events-content .tribe-events-calendar td.tribe-events-present{background:$primary;}
					#top $key #tribe-events-content td.tribe-events-has-events, #top $key #tribe-events-content td.tribe-events-has-events div[id*='tribe-events-daynum-'],
					#top $key #tribe-events-content td.tribe-events-has-events div[id*='tribe-events-daynum-']>a, $key .tribe-mobile-day-date
					{background:$primary; color: $constant_font; }

					#top $key .tribe-events-calendar .tribe-events-has-events:after{background: $constant_font; }



				}

				@media only screen and (max-width: 768px) {

				$key .tribe-events-loop .tribe-events-event-meta, $key .tribe-events-list .tribe-events-venue-details{border-color: $border; background-color:$bg2; }

				}


				";
			}

			//unset all vars with the help of variable vars :)
			foreach( $colors as $key => $val )
			{
				unset( $$key );
			}
		}

		$fallback_fonts = array(
								'Helvetica',
								'Arial',
								'sans-serif'
						);

		//	fontfamily or fontfamily:300,500
		$heading_font = avia_get_option( 'google_webfont' );
		if( ! empty( $heading_font ) )
		{
			$heading_font = explode( ':', $heading_font );
			$heading_font = array_merge( array( "'{$heading_font[0]}'" ), $fallback_fonts );
		}
		else
		{
			$heading_font = $fallback_fonts;
		}

		$heading_font = implode( ',', $heading_font );

		$content_font = avia_get_option( 'default_font' );
		if( ! empty( $content_font ) )
		{
			$content_font = explode( ':', $content_font );
			$content_font = array_merge( array( "'{$content_font[0]}'" ), $fallback_fonts );
		}
		else
		{
			$content_font = $fallback_fonts;
		}

		$content_font = implode( ',', $content_font );

		$content_font_size = avia_get_option( 'color-default_font_size' );
		if( ! empty( $content_font_size ) )
		{
			$content_font_size = "font-size: $content_font_size;";
		}

		$output .= "
		#top.tribe-theme-enfold.single-tribe_events .tribe-events-single-event-title,
		.tribe-events-related-events-title,
		.tribe-events-single ul.tribe-related-events li .tribe-related-events-title,
		.tribe-common--breakpoint-medium.tribe-common .tribe-common-h4--min-medium {
			font-family: $heading_font;
		}

		.tribe-events-content ol,
		.tribe-events-content p,
		.tribe-events-content ul,
		.tribe-common .tribe-common-b2{
			font-family: $content_font;
			$content_font_size
		}
		";

		return $output;
	}
}
