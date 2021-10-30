<?php
/**
 * COLUMNS
 *
 * Shortcode which creates columns for better content separation
 */

 // Don't load directly
if( ! defined( 'ABSPATH' ) ) { die( '-1' ); }



if( ! class_exists( 'avia_sc_cell' ) )
{
	class avia_sc_cell extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @var array
		 */
		static $attr = array();

		/**
		 * All available column width sizes
		 *
		 * @since 4.2.1
		 * @var array
		 */
		static private $size_array = array(
									'av_cell_one_full'		=> '1/1',
									'av_cell_one_half'		=> '1/2',
									'av_cell_one_third'		=> '1/3',
									'av_cell_one_fourth'	=> '1/4',
									'av_cell_one_fifth'		=> '1/5',
									'av_cell_two_third'		=> '2/3',
									'av_cell_three_fourth'	=> '3/4',
									'av_cell_two_fifth'		=> '2/5',
									'av_cell_three_fifth'	=> '3/5',
									'av_cell_four_fifth'	=> '4/5'
								);

		/**
		 * Define the width for a cell
		 *
		 * @since 4.2.1
		 * @var array
		 */
		static protected $size_width = array(
									'av_cell_one_full' 		=> 1.0,
									'av_cell_one_half' 		=> 0.5,
									'av_cell_one_third' 	=> 0.33,
									'av_cell_one_fourth' 	=> 0.25,
									'av_cell_one_fifth' 	=> 0.2,
									'av_cell_two_third' 	=> 0.66,
									'av_cell_three_fourth' 	=> 0.75,
									'av_cell_two_fifth' 	=> 0.4,
									'av_cell_three_fifth' 	=> 0.6,
									'av_cell_four_fifth' 	=> 0.8
								);

		/**
		 * This constructor is implicity called by all derived classes
		 * To avoid duplicating code we put this in the constructor
		 *
		 * @since 4.2.1
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			parent::__construct( $builder );

			$this->config['version']			= '1.0';
			$this->config['type']				= 'layout';
			$this->config['self_closing']		= 'no';
			$this->config['contains_text']		= 'no';
			$this->config['contains_layout']	= 'yes';
			$this->config['contains_content']	= 'yes';
//			$this->config['first_in_row']		= 'first';
		}

		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['invisible']	= true;
			$this->config['name']		= '1/1';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-full.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 100;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_cell_one_full';
			$this->config['html_renderer'] 	= false;
			$this->config['tinyMCE'] 	= array( 'disable' => 'true' );
			$this->config['tooltip'] 	= __( 'Creates a single full width column', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 1;
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
						'type' 	=> 'tab_container',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Layout' , 'avia_framework' ),
						'nodescription' => true
					),

					array(
								'type'			=> 'template',
								'template_id'	=> $this->popup_key( 'layout_alignment' )
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
													$this->popup_key( 'styling_padding' ),
													$this->popup_key( 'styling_background' )
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
								'template_id'	=> $this->popup_key( 'advanced_link' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'columns_visibility_toggle'
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
			 * Layout Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Vertical align', 'avia_framework' ),
							'desc' 	=> __( 'Choose the vertical alignment of your cells content.', 'avia_framework' ),
							'id' 	=> 'vertical_align',
							'type' 	=> 'select',
							'std' 	=> 'top',
							'subtype'	=> array(
												__( 'Top', 'avia_framework' )		=> 'top',
												__( 'Middle', 'avia_framework' )	=> 'middle',
												__( 'Bottom', 'avia_framework' )	=> 'bottom',
											)
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_alignment' ), $c, true );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'padding',
							'name'			=> __( 'Cell Padding', 'avia_framework' ),
							'desc'			=> __( 'Set the distance from the cell content to the border here. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;. Leave empty to use theme default 30px.', 'avia_framework' ),
							'std'			=> '',
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Padding', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_padding' ), $template, true );


			$c = array(
						 array(
							'name'		=> __( 'Background', 'avia_framework' ),
							'desc'		=> __( 'Select the type of background for the column.', 'avia_framework' ),
							'id'		=> 'background',
							'type'		=> 'select',
							'std'		=> 'bg_color',
							'subtype'	=> array(
												__( 'Background Color', 'avia_framework' )		=> 'bg_color',
												__( 'Background Gradient', 'avia_framework' )	=> 'bg_gradient',
											)
						),

						array(
							'name'		=> __( 'Custom Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color for this cell here. Leave empty for default color', 'avia_framework' ),
							'id'		=> 'background_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'required'	=> array( 'background', 'equals', 'bg_color' ),
							'std'		=> '',
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'gradient_colors',
							'id'			=> array( 'background_gradient_direction', 'background_gradient_color1', 'background_gradient_color2', 'background_gradient_color3' ),
							'lockable'		=> true,
							'required'		=> array( 'background', 'equals', 'bg_gradient' ),
							'container_class'	=> array( '', 'av_third av_third_first', 'av_third', 'av_third' ),
						),

						array(
							'name'		=> __( 'Custom Background Image', 'avia_framework' ),
							'desc'		=> __( "Either upload a new, or choose an existing image from your media library. Leave empty if you don't want to use a background image.", 'avia_framework' ),
							'id'		=> 'src',
							'type'		=> 'image',
							'title'		=> __( 'Insert Image', 'avia_framework' ),
							'button'	=> __( 'Insert', 'avia_framework' ),
							'std'		=> ''
						),

						array(
							'name'		=> __( 'Background Attachment', 'avia_framework' ),
							'desc'		=> __( 'Background can either scroll with the page or be fixed', 'avia_framework' ),
							'id'		=> 'background_attachment',
							'type'		=> 'select',
							'std'		=> 'scroll',
							'required'	=> array( 'src', 'not', '' ),
							'subtype'	=> array(
												__( 'Scroll', 'avia_framework' )	=> 'scroll',
												__( 'Fixed', 'avia_framework' )		=> 'fixed',
							)
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'background_image_position'
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background' ), $template, true );


			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Cell Link', 'avia_framework' ),
							'desc'			=> __( 'Select where this cell should link to', 'avia_framework' ),
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'no_toggle'		=> true
						),

						array(
							'name'			=> __( 'Hover Effect', 'avia_framework' ),
							'desc'			=> __( 'Choose if you want to have a hover effect on the column', 'avia_framework' ),
							'id'			=> 'link_hover',
							'type'			=> 'select',
							'required'		=> array( 'link', 'not', '' ),
							'std'			=> '',
							'subtype'		=> array(
													__( 'No', 'avia_framework' )	=> '',
													__( 'Yes', 'avia_framework' )	=> 'opacity80'
											)
						)


				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Column Link', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_link' ), $template, true );


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
		public function editor_element( $params )
		{

			extract( $params );

			if( empty( $data ) || ! is_array( $data ) )
			{
				$data = array();
			}

			$name = $this->config['shortcode'];
			$drag = $this->config['drag-level'];
			$drop = $this->config['drop-level'];

			$data['shortcodehandler'] 	= $this->config['shortcode'];
			$data['modal_title'] 		= __( 'Edit Cell','avia_framework' );
			$data['modal_ajax_hook'] 	= $this->config['shortcode'];
			$data['dragdrop-level']		= $this->config['drag-level'];
			$data['allowed-shortcodes'] = $this->config['shortcode'];
			$data['closing_tag']		= $this->is_self_closing() ? 'no' : 'yes';

			if( ! empty( $this->config['modal_on_load'] ) )
			{
				$data['modal_on_load'] 	= $this->config['modal_on_load'];
			}

			$dataString  = AviaHelper::create_data_string( $data );

			// add background color or gradient to indicator
			$el_bg = '';

			if( empty( $args['background'] ) || ( $args['background'] == 'bg_color' ) )
			{
				$el_bg = ! empty( $args['background_color'] ) ? " style='background:{$args['background_color']};'" : '';
			}
			else
			{
				if( $args['background_gradient_color1'] && $args['background_gradient_color2'] )
				{
					$el_bg = "style='background:linear-gradient({$args['background_gradient_color1']},{$args['background_gradient_color2']});'";
				}
			}

			$output  = "<div class='avia_layout_column avia_layout_cell avia_pop_class avia-no-visual-updates {$name} av_drag' {$dataString} data-width='{$name}'>";
			$output .=		"<div class='avia_sorthandle'>";

			$output .=			"<span class='avia-col-size'><span class='avia-element-bg-color' {$el_bg}></span>" . avia_sc_cell::$size_array[ $name ] . '</span>';
			$output .=			"<a class='avia-delete'  href='#delete' title='" . __( 'Delete Cell', 'avia_framework' ) . "'>x</a>";
			$output .=			"<a class='avia-clone'  href='#clone' title='" . __( 'Clone Cell', 'avia_framework' ) . "' >" . __( 'Clone Cell', 'avia_framework' ) . '</a>';

			if( ! empty( $this->config['popup_editor'] ) )
			{
				$output .= "    <a class='avia-edit-element'  href='#edit-element' title='" . __( 'Edit Cell', 'avia_framework' ) . "'>" . __( 'edit', 'avia_framework' ) . '</a>';
			}

			$output .=		'</div>';
			$output .=		"<div class='avia_inner_shortcode avia_connect_sort av_drop ' data-dragdrop-level='{$drop}'><span class='av-fake-cellborder'></span>";
			$output .=			"<textarea data-name='text-shortcode' cols='20' rows='4'>" . ShortcodeHelper::create_shortcode_by_array( $name, $content, $args ) . '</textarea>';

			if( $content )
			{
				$output .=		$this->builder->do_shortcode_backend( $content );
			}

			$output .=		'</div>';
			$output .=		"<div class='avia-layout-element-bg' " . $this->get_bg_string( $args ) . "></div>";
			$output .=	'</div>';

			return $output;
		}

		/**
		 * Returns the width of the cells. As this is the base class for all cells we only need to implement it here.
		 *
		 * @since 4.2.1
		 * @return float
		 */
		public function get_element_width()
		{
			return isset( avia_sc_cell::$size_width[ $this->config['shortcode'] ] ) ? avia_sc_cell::$size_width[ $this->config['shortcode'] ] : 1.0;
		}

		/**
		 * Only needed for backend canvas
		 *
		 * @param array $args
		 * @return string
		 */
		protected function get_bg_string( $args )
		{
			$style = '';

			if( ! empty( $args['attachment'] ) )
			{
				$image = false;
				$src = wp_get_attachment_image_src( $args['attachment'], $args['attachment_size'] );
				if( ! empty( $src[0] ) )
				{
					$image = $src[0];
				}

				if( $image )
				{
					$element_styling = new aviaElementStyling( $this, 'xxx' );

					$bg = ! empty( $args['background_color'] ) ? $args['background_color'] : 'transparent';
					$pos = $element_styling->background_position_string( $args['background_position'], 'center center' );
					$repeat = ! empty( $args['background_repeat'] ) ? $args['background_repeat'] : 'no-repeat';
					$extra = '';

					if( $repeat == 'stretch' )
					{
						$repeat = 'no-repeat';
						$extra = 'background-size: cover;';
					}

					if( $repeat == 'contain' )
					{
						$repeat = 'no-repeat';
						$extra = 'background-size: contain;';
					}

					$style = "style='background: {$bg} url($image) {$repeat} {$pos}; {$extra}'";
				}

			}

			return $style;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.7
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
						'vertical_align'		=> '',
						'padding'				=> '',
						'background'		                => 'bg_color',
						'background_gradient_color1'		=> '',
						'background_gradient_color2'	   	=> '',
						'background_gradient_direction'	   	=> '',
						'background_color'	            	=> '',
						'background_position' 	=> '',
						'background_repeat' 	=> '',
						'background_attachment' => '',
						'fetch_image'			=> '',
						'attachment_size'		=> '',
						'attachment'			=> '',
						'link'					=> '',
						'linktarget'			=> '',
						'link_hover'			=> '',
						'mobile_display'		=> '',
						'mobile_col_pos'		=> 0
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			//	fallback - prior 4.8.7 '0px' was handled this way
			if( $atts['padding'] == '0px' || $atts['padding'] == '0' || $atts['padding'] == '0%' )
			{
				$atts['padding'] = '0px';
			}

			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'flex_cell',
						str_replace( 'av_cell_', 'av_', $shortcodename ),
						$element_id,
						'no_margin',
						$atts['mobile_display']
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );

			if( ! empty( $atts['vertical_align'] ) )
			{
				$element_styling->add_styles( 'container', array(
												'vertical-align'	=> $atts['vertical_align']
										) );
			}

			if( ! empty( avia_sc_cell::$attr['mobile_column_order'] ) && 'individual' == avia_sc_cell::$attr['mobile_column_order'] )
			{
				$element_styling->add_styles( 'container', array( 'order' => $atts['mobile_col_pos'] ) );
			}

			if( ! empty( avia_sc_cell::$attr['min_height'] ) && empty( avia_sc_cell::$attr['min_height_percent'] ) )
			{
				$min = (int) avia_sc_cell::$attr['min_height'];
				$element_styling->add_styles( 'container', array(
												'height'		=> $min . 'px',
												'min-height'	=> $min . 'px'
										) );
			}

			if( trim( $atts['padding'] ) != '' )
			{
				//	fallback - prior 4.8.7 '0px' was handled this way
				if( $atts['padding'] == '0px' )
				{
					$element_styling->add_classes( 'container', 'av-zero-padding' );
				}

				$element_styling->add_callback_styles( 'container', array( 'padding' ) );
			}

			/**
			 * Style Background
			 * ================
			 */
			if( ! empty( $atts['attachment'] ) )
			{
				$src = wp_get_attachment_image_src( $atts['attachment'], $atts['attachment_size'] );
				if( ! empty( $src[0] ) )
				{
					$atts['fetch_image'] = $src[0];
				}
			}

			if( $atts['background_repeat'] == 'stretch' )
			{
				$element_styling->add_classes( 'container', 'avia-full-stretch' );
				$atts['background_repeat'] = 'no-repeat';
			}

			if( $atts['background_repeat'] == 'contain' )
			{
				$element_styling->add_classes( 'container', 'avia-full-contain' );
				$atts['background_repeat'] = 'no-repeat';
			}

			// background image, color and gradient
			$bg_image = '';

			if( ! empty( $atts['fetch_image'] ) )
			{
				$bg_pos = $element_styling->background_position_string( $atts['background_position'] );
				$bg_image = "url({$atts['fetch_image']}) {$bg_pos} {$atts['background_repeat']} {$atts['background_attachment']}";
			}

			if( $atts['background'] != 'bg_gradient' )
			{
				if( ! empty( $bg_image ) )
				{
					$element_styling->add_styles( 'container', array( 'background' => "{$bg_image} {$atts['background_color']}" ) );
				}
				else if( ! empty( $atts['background_color'] ) )
				{
					$element_styling->add_styles( 'container', array( 'background-color' => $atts['background_color'] ) );
				}
			}
			// assemble gradient declaration
			else if( ! empty( $atts['background_gradient_color1'] ) && ! empty( $atts['background_gradient_color2'] ) )
			{
				// fallback background color for IE9
				$element_styling->add_styles( 'container', array( 'background-color' => $atts['background_gradient_color1'] ) );

				if( empty( $bg_image ) )
				{
					$element_styling->add_callback_styles( 'container', array( 'background_gradient_direction' ) );
				}
				else
				{
					$gradient_val_array = $element_styling->get_callback_settings( 'background_gradient_direction', 'styles' );
					$gradient_val = isset( $gradient_val_array['background'] ) ? $gradient_val_array['background'] : '';

					//	',' is needed !!!
					$gradient_style = ! empty( $gradient_val ) ? "{$bg_image}, {$gradient_val}" : $bg_image;

					$element_styling->add_styles( 'container', array( 'background' => $gradient_style ) );
				}
			}
			else
			{
				//	fallback to image and first gradient color
				if( ! empty( $bg_image ) )
				{
					$element_styling->add_styles( 'container', array( 'background' => "{$bg_image} {$atts['background_gradient_color1']}" ) );
				}
				else if( ! empty( $atts['background_gradient_color1'] ) )
				{
					$element_styling->add_styles( 'container', array( 'background-color' => $atts['background_gradient_color1'] ) );
				}
			}


			$selectors = array(
						'container'			=> ".flex_cell.{$element_id}"
					);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;
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
		function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '')
		{
			global $avia_config;

			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );

			$avia_config['current_column'] = str_replace( 'av_cell_', 'av_', $shortcodename );

			$link = AviaHelper::get_url( $atts['link'] );
			$link_data = '';
			$reader_html = '';

			if( ! empty( $link ) )
			{
				$element_styling->add_classes( 'container', array( 'avia-link-column', 'av-cell-link' ) );
				if( ! empty( $atts['link_hover'] ) )
				{
					$element_styling->add_classes( 'container', 'avia-link-column-hover' );
				}

				$screen_reader = '';

				$link_data .= ' data-link-column-url="' . esc_attr( $link ) . '" ';

				if( ( strpos( $atts['linktarget'], '_blank' ) !== false ) )
				{
					$link_data .= ' data-link-column-target="_blank" ';
					$screen_reader .= ' target="_blank" ';
				}

				//	we add this, but currently not supported in js
				if( strpos( $atts['linktarget'], 'nofollow' ) !== false )
				{
					$link_data .= ' data-link-column-rel="nofollow" ';
					$screen_reader .= ' rel="nofollow" ';
				}

				/**
				 * Add an invisible link also for screen readers
				 */
				$reader_html .=	'<a class="av-screen-reader-only" href="' . esc_attr( $link ) . '" ' . $screen_reader . '>';
				$reader_html .=		AviaHelper::get_screen_reader_url_text( $atts['link'] );
				$reader_html .=	'</a>';
			}


			//	if the user uses the column shortcode without the layout builder make sure that paragraphs are applied to the text
			$inner_content = ( empty( $avia_config['conditionals']['is_builder_template'] ) ) ? ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) ) : ShortcodeHelper::avia_remove_autop( $content, true );


			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= $reader_html;
			$output .= "<div class='{$container_class}' {$link_data}>";
			$output .=		"<div class='flex_cell_inner'>";
			$output .=			$inner_content;
			$output .=		'</div>';
			$output .= '</div>';

			unset( $avia_config['current_column'] );

			return $output;
		}

	}
}



if( ! class_exists( 'avia_sc_cell_one_half' ) )
{
	class avia_sc_cell_one_half extends avia_sc_cell
	{

		function shortcode_insert_button()
		{
			$this->config['invisible'] = true;
			$this->config['name']		= '1/2';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-half.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 90;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_cell_one_half';
			$this->config['html_renderer'] 	= false;
			$this->config['tinyMCE'] 	= array( 'disable' => 'true' );
			$this->config['tooltip'] 	= __( 'Creates a single column with 50&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 1;
		}
	}
}


if( ! class_exists( 'avia_sc_cell_one_third' ) )
{
	class avia_sc_cell_one_third extends avia_sc_cell
	{

		function shortcode_insert_button()
		{
			$this->config['invisible'] = true;
			$this->config['name']		= '1/3';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-third.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 80;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_cell_one_third';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 33&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 1;
			$this->config['tinyMCE'] 	= array( 'disable' => 'true' );
		}
	}
}

if( ! class_exists( 'avia_sc_cell_two_third' ) )
{
	class avia_sc_cell_two_third extends avia_sc_cell
	{

		function shortcode_insert_button()
		{
			$this->config['invisible'] = true;
			$this->config['name']		= '2/3';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-two_third.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 70;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_cell_two_third';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 67&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 1;
			$this->config['tinyMCE'] 	= array( 'disable' => 'true' );
		}
	}
}

if( ! class_exists( 'avia_sc_cell_one_fourth' ) )
{
	class avia_sc_cell_one_fourth extends avia_sc_cell
	{

		function shortcode_insert_button()
		{
			$this->config['invisible'] = true;
			$this->config['name']		= '1/4';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-fourth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 60;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_cell_one_fourth';
			$this->config['tooltip'] 	= __( 'Creates a single column with 25&percnt; width', 'avia_framework' );
			$this->config['html_renderer'] 	= false;
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 1;
			$this->config['tinyMCE'] 	= array( 'disable' => 'true' );
		}
	}
}

if( ! class_exists( 'avia_sc_cell_three_fourth' ) )
{
	class avia_sc_cell_three_fourth extends avia_sc_cell
	{

		function shortcode_insert_button()
		{
			$this->config['invisible'] = true;
			$this->config['name']		= '3/4';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-three_fourth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 50;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_cell_three_fourth';
			$this->config['tooltip'] 	= __( 'Creates a single column with 75&percnt; width', 'avia_framework' );
			$this->config['html_renderer'] 	= false;
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 1;
			$this->config['tinyMCE'] 	= array( 'disable' => 'true' );
		}
	}
}

if( ! class_exists( 'avia_sc_cell_one_fifth' ) )
{
	class avia_sc_cell_one_fifth extends avia_sc_cell
	{

		function shortcode_insert_button()
		{
			$this->config['invisible'] = true;
			$this->config['name']		= '1/5';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-fifth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 40;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_cell_one_fifth';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 20&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 1;
			$this->config['tinyMCE'] 	= array( 'disable' => 'true' );
		}
	}
}

if( ! class_exists( 'avia_sc_cell_two_fifth' ) )
{
	class avia_sc_cell_two_fifth extends avia_sc_cell
	{

		function shortcode_insert_button()
		{
			$this->config['invisible'] = true;
			$this->config['name']		= '2/5';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-two_fifth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 39;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_cell_two_fifth';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 40&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 1;
			$this->config['tinyMCE'] 	= array( 'disable' => 'true' );
		}
	}
}

if( ! class_exists( 'avia_sc_cell_three_fifth' ) )
{
	class avia_sc_cell_three_fifth extends avia_sc_cell
	{

		function shortcode_insert_button()
		{
			$this->config['invisible'] = true;
			$this->config['name']		= '3/5';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-three_fifth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 38;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_cell_three_fifth';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 60&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 1;
			$this->config['tinyMCE'] 	= array( 'disable' => 'true' );
		}
	}
}

if( ! class_exists( 'avia_sc_cell_four_fifth' ) )
{
	class avia_sc_cell_four_fifth extends avia_sc_cell
	{

		function shortcode_insert_button()
		{
			$this->config['invisible'] = true;
			$this->config['name']		= '4/5';
			$this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'sc-four_fifth.png';
			$this->config['tab']		= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']		= 37;
			$this->config['target']		= 'avia-section-drop';
			$this->config['shortcode'] 	= 'av_cell_four_fifth';
			$this->config['html_renderer'] 	= false;
			$this->config['tooltip'] 	= __( 'Creates a single column with 80&percnt; width', 'avia_framework' );
			$this->config['drag-level'] = 2;
			$this->config['drop-level'] = 1;
			$this->config['tinyMCE'] 	= array( 'disable' => 'true' );
		}
	}
}


