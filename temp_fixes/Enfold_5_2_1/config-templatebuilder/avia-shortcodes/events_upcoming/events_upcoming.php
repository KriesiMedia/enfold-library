<?php
/**
 * Upcoming Events
 *
 * Show a list of upcoming events
 * Element is in Beta and by default disabled. Todo: test with layerslider elements. currently throws error bc layerslider is only included if layerslider element is detected which is not the case with the post/page element
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'Tribe__Events__Main' ) )
{
	if( ! function_exists( 'av_upcoming_events_fallback' ) )
	{
		function av_upcoming_events_fallback()
		{
			return "<p>Please install the <a href='https://wordpress.org/plugins/the-events-calendar/'>The Events Calendar</a> or <a href='https://theeventscalendar.pxf.io/pro'>The Events Calendar Pro</a> Plugin to display a list of upcoming Events</p>";
		}

		add_shortcode( 'av_upcoming_events', 'av_upcoming_events_fallback' );
	}

	return;
}



if ( ! class_exists( 'avia_sc_upcoming_events' ) )
{
	class avia_sc_upcoming_events extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @since 4.7.6.4
		 * @var int
		 */
		protected $current_page;

		public function __construct( \AviaBuilder $builder )
		{
			parent::__construct( $builder );

			$this->current_page = 1;
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'yes';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Upcoming Events', 'avia_framework' );
			$this->config['tab']			= __( 'Plugin Additions', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-blog.png';
			$this->config['order']			= 30;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_upcoming_events';
			$this->config['tooltip']		= __( 'Show a list of upcoming events', 'avia_framework' );
			$this->config['drag-level']		= 3;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}

		/**
		 *
		 */
		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-events-upcoming', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/events_upcoming/events_upcoming{$min_css}.css", array( 'avia-layout' ), $ver );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @return void
		 */
		protected function popup_elements()
		{
			$this->elements = array(

				array(
						'type' 	=> 'tab_container',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),
						array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'content_upcoming' )
							),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Styling', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'styling_spacing' ),
													$this->popup_key( 'styling_upcoming' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type' 	=> 'toggle_container',
							'nodescription' => true
						),

						array(
								'type'			=> 'template',
								'template_id'	=> 'screen_options_toggle',
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'developer_options_toggle',
								'args'			=> array( 'sc' => $this )
							),

					array(
							'type' 	=> 'toggle_container_close',
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array( 'sc' => $this )
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)

				);
		}

		/**
		 * Create and register templates for easier maintainance
		 *
		 * @since 4.6.4
		 */
		protected function register_dynamic_templates()
		{

			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(

						array(
							'name'		=> __( 'Which Entries?', 'avia_framework' ),
							'desc'		=> __( 'Select which entries should be displayed by selecting a taxonomy. If none are selected all events are shown.', 'avia_framework' ),
							'id'		=> 'categories',
							'type'		=> 'select',
							'taxonomy'	=> Tribe__Events__Main::TAXONOMY,
							'subtype'	=> 'cat',
							'multiple'	=> 6,
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Entry Number', 'avia_framework' ),
							'desc'		=> __( 'How many items should be displayed?', 'avia_framework' ),
							'id'		=> 'items',
							'type'		=> 'select',
							'std'		=> '3',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( __( 'All', 'avia_framework' ) => '-1' ) )
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_upcoming' ), $c );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'margin_padding',
								'lockable'		=> true
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Spacing', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_spacing' ), $template );


			$c = array(
						array(
							'name' 	=> __( 'Pagination', 'avia_framework' ),
							'desc' 	=> __( 'Should a pagination be displayed?', 'avia_framework' ),
							'id' 	=> 'paginate',
							'type' 	=> 'select',
							'std' 	=> 'no',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Yes', 'avia_framework' )	=> 'yes',
												__( 'No', 'avia_framework' )	=> 'no'
											)
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Pagination', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_upcoming' ), $template );

		}

		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_element( $params )
		{
			$params = parent::editor_element( $params );
			$params['content'] = null;	//remove to allow content elements

			return $params;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.9
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
						'categories' 	=> '',
						'items' 		=> '3',
						'paginate'		=> 'no',
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );


			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'av-upcoming-events',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			$element_styling->add_responsive_styles( 'container-top', 'margin', $atts, $this );
			$element_styling->add_responsive_styles( 'container', 'padding', $atts, $this );


			$selectors = array(
						'container'		=> ".av-upcoming-events.{$element_id}",
						'container-top'	=> "#top .av-upcoming-events.{$element_id}"
					);

			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['meta'] = $meta;

			return $result;
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
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );


			$output = '';
			$this->current_page = 1;

			$posts = $this->query_entries( $atts );
			$entries = $posts->posts;

			if( class_exists( 'Tribe__Events__Pro__Main' ) && Tribe__Events__Pro__Main::VERSION <= '5.9.9' )
			{
					$ecp = Tribe__Events__Pro__Main::instance();
					$ecp->disable_recurring_info_tooltip();
			}

			if ( ! empty( $entries ) )
			{
				global $post;

				$default_id = $post->ID;


				$style_tag = $element_styling->get_style_tag( $element_id );
				$container_class = $element_styling->get_class_string( 'container' );

				$output .= $style_tag;
				$output .= "<div {$meta['custom_el_id']} class='{$container_class}'>";

				foreach( $entries as $index => $entry )
				{
					$class = 'av-upcoming-event-entry';
					$image = get_the_post_thumbnail( $entry->ID, 'square', array( 'class' => 'av-upcoming-event-image' ) );
					$class .= ! empty( $image ) ? ' av-upcoming-event-with-image' : ' av-upcoming-event-without-image';
					$title = get_the_title( $entry->ID );
					$link = get_permalink( $entry->ID );

					$post->ID = $entry->ID; //temp set of the post id so that tribe fetches the correct price symbol
					$price = tribe_get_cost( $entry->ID, true );
					$venue = tribe_get_venue( $entry->ID );
					$post->ID = $default_id;

					$event  = '';
					$event .=	"<a href='{$link}' class='{$class}'>";

					if( $image )
					{
						$event .=	$image;
					}

					$event .=		'<span class="av-upcoming-event-data">';
					$event .=			"<h4 class='av-upcoming-event-title'>{$title}</h4>";
					$event .=			'<span class="av-upcoming-event-meta">';
					$event .=				'<span class="av-upcoming-event-schedule">' . tribe_events_event_schedule_details( $entry ) . '</span>';

					if( $price )
					{
						$event .=			"<span class='av-upcoming-event-cost'>{$price}</span>";
					}
					if( $price && $venue )
					{
						$event .=				' - ';
					}
					if( $venue )
					{
						$event .=			"<span class='av-upcoming-event-venue'>{$venue}</span>";
					}

					$event .=				apply_filters( 'avf_upcoming_event_extra_data', '', $entry );
					$event .=			'</span>';
					$event .=		'</span>';
					$event .=	'</a>';

					/**
					 * Allows to change the output
					 *
					 * @since 4.5.6.1
					 * @param string $event
					 * @param array $entries		WP_Post
					 * @param int $index
					 * @return string
					 */
					$output .= apply_filters( 'avf_single_event_upcoming_html', $event, $entries, $index );
				}

				if( $atts['paginate'] == 'yes' && $avia_pagination = avia_pagination( $posts->max_num_pages, 'nav', 'avia-element-paging', $this->current_page ) )
				{
					$output .= "<div class='pagination-wrap pagination-" . Tribe__Events__Main::POSTTYPE . "'>{$avia_pagination}</div>";
				}

				$output .= '</div>';
			}

			if( class_exists( 'Tribe__Events__Pro__Main' ) && Tribe__Events__Pro__Main::VERSION <= '5.9.9' )
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

			//	get the event categories
			$terms =( ! empty( $params['categories'] ) ) ? explode( ',', $params['categories'] ) : array();
			$this->current_page = ( $params['paginate'] != 'no' ) ? avia_get_current_pagination_number( 'avia-element-paging' ) : 1;
			$start_date = date( 'Y-m-d' );

			$query = array(
							'paged'				=> $this->current_page,
							'posts_per_page'	=> $params['items'],
							'start_date'		=> $start_date,
							'eventDisplay'		=> 'list'
						);

			//if we find categories perform complex query
			if( isset( $terms[0] ) && ! empty( $terms[0] ) && ! is_null( $terms[0] ) && $terms[0] != 'null' )
			{
				$query['tax_query'] = array(
										array( 	'taxonomy' 	=> Tribe__Events__Main::TAXONOMY,
												'field' 	=> 'id',
												'terms' 	=> $terms,
												'operator' 	=> 'IN'
											)
										);
			}


			/**
			 * @since < 4.0
			 * @return array
			 */
			$query = apply_filters( 'avia_tribe_events_upcoming', $query, $params );

			return tribe_get_events( $query , true );
		}

	}
}
