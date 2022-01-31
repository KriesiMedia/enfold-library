<?php
/**
 * Single Tab
 *
 * Shortcode creates a single tab for the tab section element
 */

 // Don't load directly
if( ! defined( 'ABSPATH' ) ) { die( '-1' ); }



if( ! class_exists( 'avia_sc_tab_sub_section' ) )
{
	class avia_sc_tab_sub_section extends aviaShortcodeTemplate
	{
		/**
		 * @since ???
		 * @var string
		 */
		static public $extraClass = '';

		/**
		 * Attribute array of outer tabsection container
		 *
		 * @since ???
		 * @var array
		 */
		static public $attr = array();


		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['version']			= '1.0';
			$this->config['type']				= 'layout';
			$this->config['self_closing']		= 'no';
			$this->config['contains_text']		= 'no';
			$this->config['contains_layout']	= 'yes';
			$this->config['contains_content']	= 'yes';

			$this->config['invisible']		= true;
			$this->config['name']			= 'Single Tab';
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-full.png';
			$this->config['tab']			= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']			= 100;
			$this->config['target']			= 'avia-section-drop';
			$this->config['shortcode']		= 'av_tab_sub_section';
			$this->config['html_renderer']	= false;
			$this->config['tinyMCE']		= array( 'disable' => 'true' );
			$this->config['tooltip']		= __( 'Creates a single tab for the tab section element', 'avia_framework' );
			$this->config['drag-level']		= 2;
			$this->config['drop-level']		= 1;
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
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'content_tab' )
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
													$this->popup_key( 'styling_alignment' ),
													$this->popup_key( 'styling_colors' ),
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
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Tab Title', 'avia_framework' ),
							'desc' 	=> __( 'Set a tab title', 'avia_framework' ),
							'id' 	=> 'tab_title',
							'type' 	=> 'input',
							'std' 	=> '',
						),

						array(
							'name' 	=> __( 'Tab Symbol', 'avia_framework' ),
							'desc' 	=> __( 'Should an icon or image be displayed at the top of the tab title?', 'avia_framework' ),
							'id' 	=> 'icon_select',
							'type' 	=> 'select',
							'std' 	=> 'no',
							'subtype'	=> array(
												__( 'No icon or image', 'avia_framework' )	=> 'no',
												__( 'Display icon', 'avia_framework' )		=> 'icon_top',
												__( 'Display image', 'avia_framework' )		=> 'image_top'
											)
						),

						array(
							'name' 	=> __( 'Tab Icon', 'avia_framework' ),
							'desc' 	=> __( 'Select an icon for your tab title below', 'avia_framework' ),
							'id' 	=> 'icon',
							'type' 	=> 'iconfont',
							'std' 	=> '',
							'required'	=> array( 'icon_select', 'equals', 'icon_top' )
                        ),

						array(
							'name'		=> __( 'Tab Image', 'avia_framework' ),
							'desc'		=> __( 'Either upload a new, or choose an existing image from your media library', 'avia_framework' ),
							'id'		=> 'tab_image',
							'type'		=> 'image',
							'fetch'		=> 'id',
							'secondary_img'  => true,
							'force_id_fetch' => true,
							'title'		=>  __( 'Insert Image', 'avia_framework' ),
							'button'	=> __( 'Insert', 'avia_framework' ),
							'std'		=> '',
							'required'	=> array( 'icon_select', 'equals', 'image_top' )
						),

						array(
							'name' 	=> __( 'Tab Image Style', 'avia_framework' ),
							'id' 	=> 'tab_image_style',
							'type' 	=> 'select',
							'std' 	=> '',
							'required' => array( 'icon_select', 'equals', 'image_top' ),
							'subtype'	=> array(
												__( 'No special style', 'avia_framework' )	=> '',
												__( 'Rounded Borders', 'avia_framework' )	=> 'av-tab-image-rounded',
												__( 'Circle', 'avia_framework' )			=> 'av-tab-image-circle',
											)
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_tab' ), $c, true );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Vertical align', 'avia_framework' ),
							'desc' 	=> __( 'Choose the vertical alignment of your tab content. (only applies if tabs are set to fixed height)', 'avia_framework' ),
							'id' 	=> 'vertical_align',
							'type' 	=> 'select',
							'std' 	=> 'middle',
							'subtype'	=> array(
												__( 'Top', 'avia_framework' )		=> 'top',
												__( 'Middle', 'avia_framework' )	=> 'middle',
												__( 'Bottom', 'avia_framework' )	=> 'bottom',
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Alignment', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_alignment' ), $template, true );


			$c = array(
						array(
							'name' 	=> __( 'Active Tab Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color for this tab when active. Leave empty for default color', 'avia_framework' ),
							'id' 	=> 'color',
							'type' 	=> 'colorpicker',
							'rgba'	=> true,
							'std' 	=> ''
						),

						array(
							'name' 	=> __( 'Inactive Tab Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color for this tab when inactive. Leave empty for default color', 'avia_framework' ),
							'id' 	=> 'inactive_color',
							'type' 	=> 'colorpicker',
							'rgba'	=> true,
							'std' 	=> ''
						),

						array(
							'name' 	=> __( 'Active Tab Font Color On Hover', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color for this tab when active. Leave empty for default color', 'avia_framework' ),
							'id' 	=> 'color_hover',
							'type' 	=> 'colorpicker',
							'rgba'	=> true,
							'std' 	=> ''
						),

						array(
							'name' 	=> __( 'Inactive Tab Font Color On Hover', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color for this tab when inactive. Leave empty for default color', 'avia_framework' ),
							'id' 	=> 'inactive_color_hover',
							'type' 	=> 'colorpicker',
							'rgba'	=> true,
							'std' 	=> ''
						),

						array(
							'name' 	=> __( 'Custom Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color for this section here. Leave empty for default color', 'avia_framework' ),
							'id' 	=> 'background_color',
							'type' 	=> 'colorpicker',
							'rgba'	=> true,
							'std' 	=> ''
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors' ), $template, true );

			$c = array(
						array(
							'name'		=> __( 'Custom Background Image', 'avia_framework' ),
							'desc'		=> __( "Either upload a new, or choose an existing image from your media library. Leave empty if you don't want to use a background image ", 'avia_framework' ),
							'id'		=> 'src',
							'type'		=> 'image',
							'title'		=> __( 'Insert Image', 'avia_framework' ),
							'button'	=> __( 'Insert', 'avia_framework' ),
							'std'		=> '',
						),

						array(
							'name' 	=> __( 'Background Attachment', 'avia_framework' ),
							'desc' 	=> __( 'Background can either scroll with the page or be fixed', 'avia_framework' ),
							'id' 	=> 'background_attachment',
							'type' 	=> 'select',
							'std' 	=> 'scroll',
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
								'title'			=> __( 'Background Image', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background' ), $template, true );

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
		function editor_element( $params )
		{
			avia_sc_tab_section::$tab ++;

			extract( $params );

			if( empty( $data ) )
			{
				$data = array();
			}

			$name = $this->config['shortcode'];
			$drag = $this->config['drag-level'];
			$drop = $this->config['drop-level'];


			$data['shortcodehandler'] = $this->config['shortcode'];
			$data['modal_title'] = __( 'Edit Tab', 'avia_framework' );
			$data['modal_ajax_hook'] = $this->config['shortcode'];
			$data['dragdrop-level'] = $this->config['drag-level'];
			$data['allowed-shortcodes'] = $this->config['shortcode'];

			if( ! empty( $this->config['modal_on_load'] ) )
			{
				$data['modal_on_load'] = $this->config['modal_on_load'];
			}

			$dataString  = AviaHelper::create_data_string( $data );

			$el_bg = ! empty( $args['background_color'] ) ? " style='background:{$args['background_color']};'" : '';
			$active_tab = avia_sc_tab_section::$tab == avia_sc_tab_section::$admin_active ? 'av-admin-section-tab-content-active' : '';
			avia_sc_tab_section::$tab_titles[ avia_sc_tab_section::$tab ] = ! empty( $args['tab_title'] ) ? ": {$args['tab_title']}" : '';


			$output  = "<div  class='avia_layout_column avia_layout_tab {$active_tab} avia-no-visual-updates {$name} av_drag' {$dataString} data-width='{$name}' data-av-tab-section-content='" . avia_sc_tab_section::$tab . "' >";
			$output .=		"<div class='avia_sorthandle'>";

			//$output .=	"<span class='avia-element-title'>".$this->config['name']."<span class='avia-element-title-id'>".$title_id."</span></span>";
			$output .=			"<a class='avia-delete avia-tab-delete av-special-delete'  href='#delete' title='" . __( 'Delete Tab', 'avia_framework' ) . "'>x</a>";
			$output .=			"<a class='avia-clone avia-tab-clone av-special-clone'  href='#clone' title='" . __( 'Clone Tab', 'avia_framework' ) . "' >" . __( 'Clone Cell', 'avia_framework' ) . '</a>';

			if( ! empty( $this->config['popup_editor'] ) )
			{
				$output .=		"<a class='avia-edit-element'  href='#edit-element' title='" . __( 'Edit Tab', 'avia_framework' ) . "'>edit</a>";
			}

			$output .=		'</div>';

			$output .=		"<div class='avia_inner_shortcode avia_connect_sort av_drop ' data-dragdrop-level='{$drop}'>";
			$output .=			"<textarea data-name='text-shortcode' cols='20' rows='4'>" . ShortcodeHelper::create_shortcode_by_array( $name, $content, $args ) . '</textarea>';

			if( $content )
			{
				$content = $this->builder->do_shortcode_backend( $content );
			}

			$output .=			$content;

			$output .=		'</div>';
			$output .=		"<div class='avia-layout-element-bg' " . $this->get_bg_string( $args ) . '></div>';
			$output .= '</div>';


			return $output;
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
						'tab_title'				=> '',
						'vertical_align'		=> '',
						'color'					=> '',
						'inactive_color'		=> '',
						'color_hover'			=> '',
						'inactive_color_hover'	=> '',
						'background_color'		=> '',
						'background_position' 	=> '',
						'background_repeat' 	=> '',
						'background_attachment' => '',
						'fetch_image'			=> '',
						'attachment_size'		=> '',
						'attachment'			=> '',
						'icon'					=> '',
						'font'					=> '',
						'icon_select'			=> 'no',
						'tab_image'				=> '',
						'tab_image_style'		=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );


			if( avia_sc_tab_sub_section::$attr['content_height'] == 'av-tab-content-auto' )
			{
				$atts['vertical_align'] = 'top';
			}

			avia_sc_tab_section::$sub_tab_element_id[] = $element_id;

			$classes = array(
						'av-layout-tab',
						$element_id,
						'av-animation-delay-container'
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );


			$element_styling->add_styles( 'tab-title', array( 'color' => $atts['inactive_color'] ) );
			$element_styling->add_styles( 'tab-title-active', array( 'color' => $atts['color'] ) );
			$element_styling->add_styles( 'tab-title-hover', array( 'color' => $atts['inactive_color_hover'] ) );
			$element_styling->add_styles( 'tab-title-active-hover', array( 'color' => $atts['color_hover'] ) );

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

			if( ! empty( $atts['fetch_image'] ) )
			{
				$image = array(
							'background-image'		=> "url({$atts['fetch_image']})",
							'background-position'	=> $atts['background_position'],
							'background-repeat'		=> $atts['background_repeat'],
							'background-attachment'	=> $atts['background_attachment'],
						);

				$element_styling->add_styles( 'container', $image );
			}

			$content_styles = array(
						'vertical-align'	=> $atts['vertical_align'],
						'background-color'	=> $atts['background_color'],
					);

			$element_styling->add_styles( 'container', $content_styles );
			$element_styling->add_styles( 'tab-title-arrow', array( 'background-color' => $atts['background_color'] ) );


			$outer_element_id = avia_sc_tab_section::$tab_element_id;

			$selectors = array(
						'container'					=> ".av-layout-tab.{$element_id}",
						'tab-title'					=> "#top .av-tab-section-outer-container.{$outer_element_id} .av-section-tab-title.{$element_id}",
						'tab-title-active'			=> "#top .av-tab-section-outer-container.{$outer_element_id} .av-active-tab-title.av-section-tab-title.{$element_id}",
						'tab-title-hover'			=> "#top .av-tab-section-outer-container.{$outer_element_id} .av-section-tab-title.{$element_id}:hover",
						'tab-title-active-hover'	=> "#top .av-tab-section-outer-container.{$outer_element_id} .av-active-tab-title.av-section-tab-title.{$element_id}:hover",
						'tab-title-arrow'			=> "#top .av-tab-section-outer-container.{$outer_element_id} .av-active-tab-title.av-section-tab-title.{$element_id} .av-tab-arrow-container span"
					);

			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['meta'] = $meta;
			$result['element_styling'] = $element_styling;

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
		function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			global $avia_config;

			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );


			avia_sc_tab_section::$tab += 1;
			avia_sc_tab_section::$tab_titles[ avia_sc_tab_section::$tab ] = ! empty( $atts['tab_title'] ) ? $atts['tab_title'] : '';
			avia_sc_tab_section::$tab_atts[ avia_sc_tab_section::$tab ] = $atts;


			$data = '';
			$display_char = av_icon( $atts['icon'], $atts['font'] );

			if( $atts['icon_select'] == 'icon_top' )
			{
				avia_sc_tab_section::$tab_icons[ avia_sc_tab_section::$tab ] = "<span class='av-tab-section-icon' {$display_char}></span>";
			}

			if( $atts['icon_select'] == 'image_top' )
			{
				if( ! empty( $atts['tab_image'] ) )
				{
					$src = wp_get_attachment_image_src( $atts['tab_image'], 'square' );

					if( ! empty( $src[0] ) )
					{
						avia_sc_tab_section::$tab_images[ avia_sc_tab_section::$tab ] = "<span class='av-tab-section-image' style='background-image: url({$src[0]});'></span>";
					}
				}
			}

			$avia_config['current_column'] = $shortcodename;

			if( ! isset( avia_sc_tab_sub_section::$attr['initial'] ) )
			{
				avia_sc_tab_sub_section::$attr['initial'] = 1;
			}
			else if( avia_sc_tab_sub_section::$attr['initial'] <= 0 )
			{
				avia_sc_tab_sub_section::$attr['initial'] = 1;
			}
			else if( avia_sc_tab_sub_section::$attr['initial'] > avia_sc_tab_section::$tab )
			{
				avia_sc_tab_sub_section::$attr['initial'] = avia_sc_tab_section::$tab;
			}

			$active_tab = avia_sc_tab_section::$tab == avia_sc_tab_sub_section::$attr['initial'] ? 'av-active-tab-content __av_init_open' : '';

			$tab_link = AviaHelper::valid_href( $atts['tab_title'], '-', 'av-tab-section-' . avia_sc_tab_section::$count . '-' . avia_sc_tab_section::$tab );
			$tab_id = 'av-tab-section-' . avia_sc_tab_section::$count . '-' . avia_sc_tab_section::$tab;


			$data .= ' data-av-tab-section-content="' . avia_sc_tab_section::$tab . '"';
			$data .= " data-tab-section-id='$tab_link'";


			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div id='{$tab_id}' class='{$container_class} {$active_tab} " . avia_sc_tab_sub_section::$extraClass . "' {$data}>";

			$output .=		'<div class="av-layout-tab-inner">';
			$output .=			'<div class="container">';

			//if the user uses the column shortcode without the layout builder make sure that paragraphs are applied to the text
			$content = ( empty( $avia_config['conditionals']['is_builder_template'] ) ) ? ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) ) : ShortcodeHelper::avia_remove_autop( $content, true );

			$output .=				$content;
			$output .=			'</div>';
			$output .=		'</div>';
			$output .= '</div>';

			unset( $avia_config['current_column'] );

			return $output;
		}

		/**
		 * Only needed for backend canvas
		 *
		 * @param array $args
		 * @return string
		 */
		protected function get_bg_string( array $args )
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
	}
}
