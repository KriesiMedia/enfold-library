<?php
/**
 * Color Section
 *
 * Shortcode creates a section with unique background image and colors for better content sepearation
 */

 // Don't load directly
if( ! defined('ABSPATH') ) { die('-1'); }



if( ! class_exists( 'avia_sc_section', false ) )
{
	class avia_sc_section extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @var int
		 */
		static protected $section_count = 0;

		/**
		 *
		 * @var string
		 */
		static public $add_to_closing = '';

		/**
		 *
		 * @var string
		 */
		static public $close_overlay = '';

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['is_fullwidth']	= 'yes';
			$this->config['type']			= 'layout';
			$this->config['self_closing']	= 'no';
			$this->config['contains_text']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Color Section', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-section.png';
			$this->config['tab']			= __( 'Layout Elements', 'avia_framework' );
			$this->config['order']			= 20;
			$this->config['shortcode']		= 'av_section';
			$this->config['html_renderer'] 	= false;
			$this->config['tinyMCE']		= array( 'disable' => 'true' );
			$this->config['tooltip']		= __( 'Creates a section with unique background image and colors', 'avia_framework' );
			$this->config['drag-level']		= 1;
			$this->config['drop-level']		= 1;
			$this->config['preview']		= false;

			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'always';				//	we use original code - not $meta
			$this->config['aria_label']		= 'yes';
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
						'name'  => __( 'Layout' , 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'layout_section_height' ),
													$this->popup_key( 'layout_borders' ),
													$this->popup_key( 'layout_margin_padding' ),
													$this->popup_key( 'layout_svg_dividers' ),
													'fold_unfold_container_toggle'
												),
							'nodescription' => true
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
													$this->popup_key( 'styling_background_colors' ),
													$this->popup_key( 'styling_background_image' ),
													$this->popup_key( 'styling_background_video' ),
													$this->popup_key( 'styling_background_overlay' ),
													$this->popup_key( 'styling_arrow' ),
													'fold_styling_toggle'
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
								'template_id'	=> 'fold_animation_toggle',
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'position',
								'content'		=> array( 'z_index' ),
								'name'			=> '',
								'no_limited'	=> true,
								'toggle'		=> true,
								'lockable'		=> true
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
					),



				array(
						'id'	=> 'av_element_hidden_in_editor',
						'type'	=> 'hidden',
						'std'	=> '0'
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
			global $avia_config;

			/**
			 * Layout Tab
			 * ===========
			 */

			$desc  = __( 'Define a minimum height for the section. Content within the section will be centered vertically within the section.', 'avia_framework' ) . '<br /><br />';
			$desc .= __( 'When using &quot;Responsive Section&quot; and background image use aspect ratio of the image for &quot;Minimum Height&quot; (e.g. 1200 * 200 = 16%).', 'avia_framework' );

			$c = array(
						array(
							'name'		=> __( 'Section Minimum Height', 'avia_framework' ),
							'id'		=> 'min_height',
							'desc'		=> $desc,
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'No minimum height, use content within section to define section height', 'avia_framework' )					=> '',
											__( 'At least 100&percnt; of browser window height', 'avia_framework' )												=> '100',
											__( 'At least 75&percnt; of browser window height', 'avia_framework' )												=> '75',
											__( 'At least 50&percnt; of browser window height', 'avia_framework' )												=> '50',
											__( 'At least 25&percnt; of browser window height', 'avia_framework' )												=> '25',
											__( 'Minimum custom height in pixel', 'avia_framework' )															=> 'custom',
											__( 'Minimum custom height in &percnt; based on browser windows height', 'avia_framework' )							=> 'percent',
											__( 'Minimum Custom height in &percnt; based on browser windows width (= responsive section)', 'avia_framework' )	=> 'percent_width'
										)
						),

						array(
							'name'		=> __( 'Section Minimum Custom Height In &percnt;', 'avia_framework' ),
							'desc'		=> __( 'Define a minimum height for the section in &percnt; based on the browser windows height or width. Width is currently ignored for slideshows and videos.', 'avia_framework' ),
							'id'		=> 'min_height_pc',
							'type'		=> 'select',
							'std'		=> '25',
							'lockable'	=> true,
							'required'	=> array( 'min_height', 'parent_in_array', 'percent,percent_width' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 99, 1, array(), ' &percnt;' )
						),

						array(
							'name'		=> __( 'Section Minimum Custom Height In px', 'avia_framework' ),
							'desc'		=> __( 'Define a minimum height for the section. Use a pixel value. eg: 500px', 'avia_framework' ) ,
							'id'		=> 'min_height_px',
							'type'		=> 'input',
							'std'		=> '500px',
							'lockable'	=> true,
							'required'	=> array( 'min_height', 'equals', 'custom' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Section Height', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_section_height' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Section Top Border Styling', 'avia_framework' ),
							'desc'		=> __( 'Choose a border styling for the top of your section', 'avia_framework' ),
							'id'		=> 'shadow',
							'type'		=> 'select',
							'std'		=> 'no-border-styling',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Display a simple 1px top border', 'avia_framework' )	=> 'no-shadow',
											__( 'Display a small styling shadow at the top of the section', 'avia_framework' )	=> 'shadow',
											__( 'No border styling', 'avia_framework' )					=> 'no-border-styling',
										)
						),

						array(
							'name'		=> __( 'Section Bottom Border Styling', 'avia_framework' ),
							'desc'		=> __( 'Choose a border styling for the bottom of your section', 'avia_framework' ),
							'id'		=> 'bottom_border',
							'type'		=> 'select',
							'std'		=> 'no-border-styling',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'No border styling', 'avia_framework' )	=> 'no-border-styling',
											__( 'Display a small arrow that points down to the next section', 'avia_framework' )	=> 'border-extra-arrow-down',
											__( 'Diagonal section border', 'avia_framework' )	=> 'border-extra-diagonal',
										)
						),

						array(
							'name' 		=> __( 'Diagonal Border: Color', 'avia_framework' ),
							'desc' 		=> __( 'Select a custom background color for your Section border here.', 'avia_framework' ),
							'id' 		=> 'bottom_border_diagonal_color',
							'type' 		=> 'colorpicker',
							'std' 		=> '#333333',
							'container_class' 	=> 'av_third av_third_first',
							'lockable'	=> true,
							'required'	=> array( 'bottom_border', 'contains', 'diagonal' )
						),

						array(
							'name'		=> __( 'Diagonal Border: Direction','avia_framework' ),
							'desc'		=> __( 'Set the direction of the diagonal border', 'avia_framework' ),
							'id'		=> 'bottom_border_diagonal_direction',
							'type'		=> 'select',
							'std'		=> '',
							'container_class' 	=> 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'bottom_border', 'contains', 'diagonal' ),
							'subtype'	=> array(
											__( 'Slanting from left to right', 'avia_framework' )	=> '',
											__( 'Slanting from right to left', 'avia_framework' )	=> 'border-extra-diagonal-inverse'
										)
						),

						array(
							'name'		=> __( 'Diagonal Border Box Style', 'avia_framework' ),
							'desc'		=> __( 'Set the style shadow of the border', 'avia_framework' ),
							'id'		=> 'bottom_border_style',
							'type'		=> 'select',
							'std'		=> '',
							'container_class' 	=> 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'bottom_border', 'contains', 'diagonal' ),
							'subtype'	=> array(
											__( 'Minimal', 'avia_framework' )		=> '',
											__( 'Box shadow', 'avia_framework' )	=> 'diagonal-box-shadow'
										)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Borders', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_borders' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Section Padding', 'avia_framework' ),
							'id'		=> 'padding',
							'desc'		=> __( 'Define the sections top and bottom padding', 'avia_framework' ),
							'type'		=> 'select',
							'std'		=> 'default',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'No Padding (0px)', 'avia_framework' )		=> 'no-padding',
											__( 'Small Padding (20px)', 'avia_framework' )	=> 'small',
											__( 'Default Padding', 'avia_framework' )		=> 'default',
											__( 'Large Padding (70px)', 'avia_framework' )	=> 'large',
											__( 'Huge Padding (130px)', 'avia_framework' )	=> 'huge',
										)
						),

						array(
							'name'		=> __( 'Custom Margins', 'avia_framework' ),
							'desc'		=> __( 'If checked allows you to set a custom top and bottom margin. Otherwise the margin is calculated by the theme based on surrounding elements', 'avia_framework' ),
							'id'		=> 'margin',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
								'type'			=> 'template',
								'template_id'	=> 'margin_padding',
								'content'		=> array( 'margin' ),
								'name'			=> __( 'Custom Top And Bottom Margin', 'avia_framework' ),
								'desc'			=> __( 'Set a responsive top and bottom margin', 'avia_framework' ),
								'id_margin'		=> 'custom_margin',
								'std_margin'	=> '0px',
								'lockable'		=> true,
								'required'		=> array( 'margin', 'not', '' ),
								'multi_margin'	=> array(
														'top'		=> __( 'Top Margin', 'avia_framework' ),
														'bottom'	=> __( 'Bottom Margin', 'avia_framework' )
													)
							)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Margin and Padding', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_margin_padding' ), $template );


			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'svg_divider_toggle',
								'lockable'		=> true
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_svg_dividers' ), $template );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Section Colors', 'avia_framework' ),
							'desc'		=> __( 'The section will use the color scheme you select. Color schemes are defined on your styling page', 'avia_framework' ) .
												'<br/><a target="_blank" href="' . admin_url( 'admin.php?page=avia#goto_styling' ) . '">' . __( '(Show Styling Page)', 'avia_framework' ) . '</a>',
							'id'		=> 'color',
							'type'		=> 'select',
							'std'		=> 'main_color',
							'lockable'	=> true,
							'subtype'	=>  array_flip( $avia_config['color_sets'] )
						),

						array(
							'name'		=> __( 'Background', 'avia_framework' ),
							'desc'		=> __( 'Select the type of background for the column.', 'avia_framework' ),
							'id'		=> 'background',
							'type'		=> 'select',
							'std'		=> 'bg_color',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Background Color', 'avia_framework' )		=> 'bg_color',
												__( 'Background Gradient', 'avia_framework' )	=> 'bg_gradient',
											)
						),

						array(
							'name'		=> __( 'Custom Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color for this cell here. Leave empty for default color', 'avia_framework' ),
							'id'		=> 'custom_bg',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'background', 'equals', 'bg_color' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'gradient_colors',
							'id'			=> array( 'background_gradient_direction', 'background_gradient_color1', 'background_gradient_color2', 'background_gradient_color3' ),
							'lockable'		=> true,
							'required'		=> array( 'background', 'equals', 'bg_gradient' ),
							'container_class'	=> array( '', 'av_third av_third_first', 'av_third', 'av_third' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background Colors', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background_colors' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Custom Background Image', 'avia_framework' ),
							'desc'		=> __( 'Either upload a new, or choose an existing image from your media library. Leave empty if you want to use the background image of the color scheme defined above', 'avia_framework' ),
							'id'		=> 'src',
							'type'		=> 'image',
							'title'		=> __( 'Insert Image', 'avia_framework' ),
							'button'	=> __( 'Insert', 'avia_framework' ),
							'std'		=> '',
							'lockable'	=> true,
							'locked'	=> array( 'src', 'attachment', 'attachment_size' )
						),

						array(
							'name'		=> __( 'Background Attachment', 'avia_framework' ),
							'desc'		=> __( 'Background can either scroll with the page, be fixed or scroll with a parallax motion', 'avia_framework' ),
							'id'		=> 'attach',
							'type'		=> 'select',
							'std'		=> 'scroll',
							'lockable'	=> true,
							'required'	=> array( 'src', 'not', '' ),
							'subtype'	=> array(
											__( 'Scroll', 'avia_framework' )	=> 'scroll',
											__( 'Fixed', 'avia_framework' )		=> 'fixed',
											__( 'Parallax', 'avia_framework' )	=> 'parallax'
										)
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'background_image_position',
							'args'			=> array(
													'id_pos'		=> 'position',
													'id_repeat'		=> 'repeat'
												),
							'lockable'		=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background Image', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background_image' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Background Video', 'avia_framework' ),
							'desc'		=> __( 'You can also place a video as background for your section. Enter the URL to the Video. Currently supported are Youtube, Vimeo and direct linking of web-video files (mp4, webm, ogv)', 'avia_framework' ) . '<br/><br/>' .
											__( 'Working examples Youtube & Vimeo:', 'avia_framework' ) . '<br/>
											<strong>https://vimeo.com/1084537</strong><br/>
											<strong>https://www.youtube.com/watch?v=5guMumPFBag</strong><br/><br/>',
							'id'		=> 'video',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Video Aspect Ratio', 'avia_framework' ),
							'desc'		=> __( 'In order to calculate the correct height and width for the video slide you need to enter a aspect ratio (width:height). usually: 16:9 or 4:3.', 'avia_framework' ) . '<br/>' . __( 'If left empty 16:9 will be used', 'avia_framework' ) ,
							'id'		=> 'video_ratio',
							'type'		=> 'input',
							'std'		=> '16:9',
							'lockable'	=> true,
							'required'	=> array( 'video', 'not', '' )
						),

						array(
							'name'		=> __( 'Hide Video On Mobile Devices?', 'avia_framework' ),
							'desc'		=> __( 'You can choose to hide the video entirely on Mobile devices and instead display the Section Background image', 'avia_framework' ) . '<br/><small>' . __( "Most mobile devices can't autoplay videos to prevent bandwidth problems for the user", 'avia_framework' ) . '</small>' ,
							'id'		=> 'video_mobile_disabled',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'video', 'not', '' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background Video', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background_video' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Enable Overlay?', 'avia_framework' ),
							'desc'		=> __( 'Check if you want to display a transparent color and/or pattern overlay above your section background image/video', 'avia_framework' ),
							'id'		=> 'overlay_enable',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true,
						),

						array(
							'name'		=> __( 'Overlay Opacity', 'avia_framework' ),
							'desc'		=> __( 'Set the opacity of your overlay: 0.1 is barely visible, 1.0 is opaque ', 'avia_framework' ),
							'id'		=> 'overlay_opacity',
							'type'		=> 'select',
							'std'		=> '0.5',
							'lockable'	=> true,
							'required'	=> array( 'overlay_enable', 'not', '' ),
							'subtype'	=> array(
											__( '0.1', 'avia_framework' )	=> '0.1',
											__( '0.2', 'avia_framework' )	=> '0.2',
											__( '0.3', 'avia_framework' )	=> '0.3',
											__( '0.4', 'avia_framework' )	=> '0.4',
											__( '0.5', 'avia_framework' )	=> '0.5',
											__( '0.6', 'avia_framework' )	=> '0.6',
											__( '0.7', 'avia_framework' )	=> '0.7',
											__( '0.8', 'avia_framework' )	=> '0.8',
											__( '0.9', 'avia_framework' )	=> '0.9',
											__( '1.0', 'avia_framework' )	=> '1',
										)
						),

						array(
							'name'		=> __( 'Overlay Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for your overlay here. Leave empty if you want no color overlay', 'avia_framework' ),
							'id'		=> 'overlay_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'overlay_enable', 'not', '' )
						),

						array(
							'name'		=> __( 'Background Image', 'avia_framework' ),
							'desc'		=> __( 'Select an existing or upload a new background image.', 'avia_framework' ),
							'id'		=> 'overlay_pattern',
							'type'		=> 'select',
							'std'		=> '',
							'folder'	=> 'images/background-images/',
							'folderlabel'	=> '',
							'group'		=> __( 'Select predefined pattern', 'avia_framework' ),
							'exclude'	=> array( 'fullsize-', 'gradient' ),
							'lockable'	=> true,
							'required'	=> array( 'overlay_enable', 'not', '' ),
							'subtype'	=> array(
												__( 'No Background Image', 'avia_framework' )	=> '',
												__( 'Upload custom image', 'avia_framework' )	=> 'custom'
											)
						),

						array(
							'name'		=> __( 'Custom Pattern', 'avia_framework' ),
							'desc'		=> __( 'Upload your own seamless pattern', 'avia_framework' ),
							'id'		=> 'overlay_custom_pattern',
							'type'		=> 'image',
							'fetch'		=> 'url',
							'secondary_img'	=> true,
							'title'		=> __( 'Insert Pattern', 'avia_framework' ),
							'button'	=> __( 'Insert', 'avia_framework' ),
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'overlay_pattern', 'equals', 'custom' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Background Overlay', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_background_overlay' ), $template );


			$c = array(
						array(
							'name'		=> __( 'Display A Scroll Down Arrow', 'avia_framework' ),
							'desc'		=> __( 'Check if you want to show a button at the bottom of the section that takes the user to the next section by scrolling down', 'avia_framework' ) ,
							'id'		=> 'scroll_down',
							'type'		=> 'checkbox',
							'std'		=> '',
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Custom Arrow Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom arrow color. Leave empty if you want to use the default arrow color and style', 'avia_framework' ),
							'id'		=> 'custom_arrow_bg',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'scroll_down', 'not', '' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Scroll Down Arrow', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_arrow' ), $template );

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
			$default = array();
			$locked = array();
			$attr = $params['args'];
			$content = $params['content'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode'], $default, $locked, $content );

			$args = $attr;			//		=>  extract( $params );
			$data = isset( $params['data'] ) && is_array( $params['data'] ) ? $params['data'] : array();
			$data_locked = array();

			$extraClass = isset( $args['element_template'] ) && $args['element_template'] > 0 ? ' element_template_selected' : '  no_element_template';
			$name = $this->config['shortcode'];
			$data['shortcodehandler'] 	= $this->config['shortcode'];
			$data['modal_title'] 		= $this->config['name'];
			$data['modal_ajax_hook'] 	= $this->config['shortcode'];
			$data['dragdrop-level'] 	= $this->config['drag-level'];
			$data['allowed-shortcodes']	= $this->config['shortcode'];
			$data['base_shortcode']		= $this->config['shortcode'];
			$data['element_title']		= $this->config['name'];
			$data['element_tooltip']	= $this->config['tooltip'];
			$data['preview'] 			= ! empty( $this->config['preview'] ) ? $this->config['preview'] : 0;

			$title_id = ! empty( $args['id'] ) ? ': ' . ucfirst( $args['id'] ) : '';

			foreach( $locked as $key => $value )
			{
				$data_locked[ 'locked_' . $key ] = $value;
			}

			// add background color or gradient to indicator
			$el_bg = '';

			if( empty( $args['background'] ) || ( $args['background'] == 'bg_color' ) )
			{
				$el_bg = ! empty( $args['custom_bg'] ) ? " background:{$args['custom_bg']};" : '';
			}
			else
			{
				if( $args['background_gradient_color1'] && $args['background_gradient_color2'] )
				{
					$el_bg = "background:linear-gradient({$args['background_gradient_color1']},{$args['background_gradient_color2']});";
				}
			}

			$hidden_el_active = ! empty( $args['av_element_hidden_in_editor'] ) ? 'av-layout-element-closed' : '';

			if( ! empty( $this->config['modal_on_load'] ) )
			{
				$data['modal_on_load'] 	= $this->config['modal_on_load'];
			}

			$data_locked['initial_el_bg'] = $el_bg;
			$data_locked['initial_layout_element_bg'] = $this->get_bg_string( $args );

			$dataString = AviaHelper::create_data_string( $data );
			$dataStringLocked = AviaHelper::create_data_string( $data_locked );

			$output  = "<div class='avia_layout_section {$hidden_el_active} {$extraClass} avia_pop_class avia-no-visual-updates {$name} av_drag' {$dataString}>";

			$output .=		'<div class="avia_data_locked_container" ' . $dataStringLocked . ' data-update_element_template="yes"></div>';

			$output .=		"<div class='avia_sorthandle menu-item-handle'>";
			$output .=			"<span class='avia-element-title'>";
			$output .=				"<span class='avia-element-bg-color' style='{$el_bg}'></span>";
			$output .=				$this->config['name'];
			$output .=				"<span class='avia-element-title-id'>{$title_id}</span>";
			$output .=			'</span>';
//			$output .=			"<a class='avia-new-target'  href='#new-target' title='" . __( 'Move Section','avia_framework' ) . "'>+</a>";
			$output .=			"<a class='avia-delete'  href='#delete' title='" . __( 'Delete Section', 'avia_framework' ) . "'>x</a>";
			$output .=			"<a class='avia-toggle-visibility'  href='#toggle' title='" . __( 'Show/Hide Section', 'avia_framework' ) . "'></a>";

			if( ! empty( $this->config['popup_editor'] ) )
			{
				$output .=		"<a class='avia-edit-element'  href='#edit-element' title='" . __( 'Edit Section', 'avia_framework' ) . "'>edit</a>";
			}

			$output .=			"<a class='avia-save-element'  href='#save-element' title='" . __( 'Save Element as Template','avia_framework' ) . "'>+</a>";
			$output .= "        <a class='avia-clone'  href='#clone' title='" . __( 'Clone Section', 'avia_framework' ) . "' >" . __( 'Clone Section', 'avia_framework' ) . '</a>';
			$output .=		'</div>';
			$output .=		"<div class='avia_inner_shortcode avia_connect_sort av_drop' data-dragdrop-level='{$this->config['drop-level']}'>";
			$output .=			"<textarea data-name='text-shortcode' cols='20' rows='4'>" . ShortcodeHelper::create_shortcode_by_array( $name, $content, $params['args'] ) . '</textarea>';

			if( $content )
			{
				$content = $this->builder->do_shortcode_backend( $content );
			}

			$output .=			$content;
			$output .=		'</div>';

			$output .=		"<div class='avia-layout-element-bg' style='{$data_locked['initial_layout_element_bg']}'></div>";

			$output .=		"<a class='avia-layout-element-hidden' href='#'>" . __( 'Section content hidden. Click here to show it', 'avia_framework' ) . '</a>';

			$output .= '</div>';

			return $output;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.4
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			global $avia_config;

			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
						'src'				=> '',
						'position'			=> 'top left',
						'repeat'			=> 'no-repeat',
						'attach'			=> 'scroll',
						'color'				=> 'main_color',
						'background'		=> 'bg_color',
						'custom_bg'			=> '',
						'background_gradient_color1'		=> '',
						'background_gradient_color2'		=> '',
						'background_gradient_direction'		=> '',
						'padding'				=> 'default' ,
						'margin'				=> '',
						'custom_margin'			=> '',
						'shadow'				=> 'shadow',
						'id'					=> '',
						'min_height'			=> '',
						'min_height_option'		=> '',			//	dummy attr to save % in browser width/height (added 5.6.3)
						'min_height_pc'			=> 25,
						'min_height_px'			=> '',
						'video'					=> '',
						'video_ratio'			=>' 16:9',
						'video_mobile_disabled'	=>'',
						'custom_markup'			=> '',
						'attachment'			=> '',
						'attachment_size'		=> '',
						'bottom_border'			=> '',
						'overlay_enable'		=> '',
						'overlay_opacity'		=> '',
						'overlay_color'			=> '',
						'overlay_pattern'		=> '',
						'overlay_custom_pattern' => '',
						'scroll_down'			=> '',
						'bottom_border_diagonal_color'		=> '',
						'bottom_border_diagonal_direction'	=> '',
						'bottom_border_style'				=> '',
						'custom_arrow_bg'					=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			//	we skip $content override as we only allow styling of section to be locked
			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$atts['min_height_option'] = $atts['min_height'];
			if( false !== strpos( $atts['min_height'], 'percent' ) )
			{
				$atts['min_height'] = $atts['min_height_pc'];
			}

			if( empty( $atts['fold_height'] ) )
			{
				$atts['fold_height'] = 80;
			}

			/**
			 * Removed option, allows to place top of folded container from screen top when top of container unvisible
			 *
			 * @since 5.6
			 * @param int $avf_fold_top_offset
			 * @param array $atts
			 * @param aviaShortcodeTemplate $this
			 * @return int
			 */
			$atts['fold_top_offset'] = apply_filters( 'avf_fold_top_offset', 50, $atts, $this );

			$atts['fold_element_class'] = "av-fold-section-{$element_id}";

			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'avia-section',
						$element_id,
						$atts['color'],
						'avia-section-' . $atts['padding'],
						'avia-' . $atts['shadow']
					);

			$element_styling->add_classes( 'section-outer', $classes );
			$element_styling->add_classes_from_array( 'section-outer', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'section-outer', 'hide_element', $atts );

			//	Style background
			if( empty( $atts['attachment'] ) )
			{
				//	manual added shortcodes may contain an url only
				$atts['src'] = trim( $atts['src'] );
			}
			else if( ! empty( $atts['attachment'] ) && ! empty( $atts['attachment_size'] ) )
			{
				$atts['src'] = '';

				/**
				 * Allows e.g. WPML to reroute to translated image
				 */
				$posts = get_posts( array(
										'include'			=> $atts['attachment'],
										'post_status'		=> 'inherit',
										'post_type'			=> 'attachment',
										'post_mime_type'	=> 'image',
										'order'				=> 'ASC',
										'orderby'			=> 'post__in'
									)
								);

				if( is_array( $posts ) && ! empty( $posts ) )
				{
					$attachment_entry = $posts[0];

					$src = wp_get_attachment_image_src( $attachment_entry->ID, $atts['attachment_size'] );
					$atts['src'] = ! empty( $src[0] ) ? $src[0] : '';
				}
				else
				{
					$atts['attachment'] = false;
				}
			}
			else
			{
				$atts['src'] = '';
				$atts['attachment'] = false;
			}

			$selector_background = 'section-outer';
			if( ! empty( $atts['src'] ) && 'parallax' == $atts['attach'] )
			{
				$selector_background = 'section-parallax-inner';
			}

			// background gradient
			$gradient_val = '';
			$attach_class_outer = $atts['attach'];

			if( $atts['background'] == 'bg_gradient' )
			{
				$fallback_color = '';

				if( ! empty( $atts['background_gradient_color1'] ) && ! empty( $atts['background_gradient_color2'] ) )
				{
					$gradient_val_array = $element_styling->get_callback_settings( 'background_gradient_direction', 'styles' );
					$gradient_val = isset( $gradient_val_array['background'] ) ? $gradient_val_array['background'] : '';

					$fallback_color = $atts['background_gradient_color1'];
				}
				else
				{
					// Fallback background color for IE9
					if( ! empty( $atts['background_gradient_color1'] ) )
					{
						$fallback_color = $atts['background_gradient_color1'];
					}
					else if( ! empty( $atts['custom_bg'] ) )
					{
						$fallback_color = $atts['custom_bg'];
					}
				}

				if( ! empty( $fallback_color ) )
				{
					$element_styling->add_styles( $selector_background, array( 'background-color' => $fallback_color ) );
				}
			}
			else if( ! empty( $atts['custom_bg'] ) )
			{
				/**
				 * background image from Theme Options -> "General Styling" overrides background-color
				 *
				 * https://kriesi.at/support/topic/coloursection-colour-is-not-shown/
				 *
				 * @since 4.9.1
				 */
				$custom_bg_styles = array(
										'background-color'	=> $atts['custom_bg'],
										'background-image'	=> 'unset'
								);

				$element_styling->add_styles( $selector_background, $custom_bg_styles );
			}

			/*set background image*/
			if( $atts['src'] != '' )
			{
				if( $atts['repeat'] == 'stretch' )
				{
					$element_styling->add_styles( $selector_background, array( 'background-repeat' => 'no-repeat' ) );
					$element_styling->add_classes( 'section-outer', 'avia-full-stretch' );
				}
				else if( $atts['repeat'] == 'contain' )
				{
					$element_styling->add_styles( $selector_background, array( 'background-repeat' => 'no-repeat' ) );
					$element_styling->add_classes( 'section-outer', 'avia-full-contain' );
				}
				else
				{
					$element_styling->add_styles( $selector_background, array( 'background-repeat' => $atts['repeat'] ) );
				}

				//		',' is needed !!!
				$bg_img_grad = $gradient_val != '' ? "url({$atts['src']}), {$gradient_val}" : "url({$atts['src']})";

				$styles = array(
							'background-image' => $bg_img_grad,
							'background-position' => $element_styling->background_position_string( $atts['position'] ),
							'background-attachment'	=> $atts['attach'] == 'parallax' ? 'scroll' : $atts['attach']
				);

				$element_styling->add_styles( $selector_background, $styles );

				if( $atts['attach'] == 'parallax' )
				{
					$element_styling->add_classes( 'section-outer', 'av-parallax-section' );

					if( $atts['repeat'] == 'stretch' || $atts['repeat'] == 'no-repeat' )
					{
						$element_styling->add_classes( 'section-parallax-inner', 'avia-full-stretch' );
					}

					if( $atts['repeat'] == 'contain' )
					{
						$element_styling->add_classes( 'section-parallax-inner', 'avia-full-contain' );
					}
				}
			}
			else if( ! empty( $gradient_val ) )
			{
				$attach_class_outer = 'scroll';

				$element_styling->add_styles( $selector_background, array( 'background' => $gradient_val ) );
			}

			$element_styling->add_classes( 'section-outer', 'avia-bg-style-' . $attach_class_outer );


			/* custom margin */
			if( ! empty( $atts['margin'] ) )
			{
				$element_styling->add_responsive_styles( 'section-outer', 'custom_margin', $atts, $this );
			}

			//	Create Styles for overlay
			if( ! empty( $atts['overlay_enable'] ) )
			{
				$element_styling->add_styles( 'overlay', array( 'opacity' => $atts['overlay_opacity'] ) );

				if( ! empty( $atts['overlay_color'] ) )
				{
					$element_styling->add_styles( 'overlay', array( 'background-color' => $atts['overlay_color'] ) );
//					$overlay .= "background-color: {$overlay_color}; ";
				}

				$overlay_src = '';
				if( ! empty( $atts['overlay_pattern'] ) )
				{
					if( $atts['overlay_pattern'] == 'custom' )
					{
						$overlay_src = $atts['overlay_custom_pattern'];
					}
					else
					{
						$overlay_src = str_replace( '{{AVIA_BASE_URL}}', AVIA_BASE_URL, $atts['overlay_pattern'] );
					}
				}

				if( ! empty( $overlay_src ) )
				{
					$element_styling->add_styles( 'overlay', array(
														'background-image'	=> "url({$overlay_src})" ,
														'background-repeat'	=> 'repeat'
													)
												);
				}

				$element_styling->add_classes( 'section-outer', 'av-section-color-overlay-active' );
			}

			//	Scroll Down Arrow
			if( ! empty( $atts['scroll_down'] ) )
			{
				if( ! empty( $atts['custom_arrow_bg'] ) )
				{
					$element_styling->add_styles( 'scroll-down', array( 'color' => $atts['custom_arrow_bg'] ) );
					$element_styling->add_classes( 'scroll-down', 'av-custom-scroll-down-color' );
				}
			}

			//	Extra border element: Diagonal or small arrow
			if( strpos( $atts['bottom_border'], 'border-extra' ) !== false )
			{
				$backgroundElColor = ! empty( $atts['custom_bg'] ) ? $atts['custom_bg'] : $avia_config['backend_colors']['color_set'][ $atts['color'] ]['bg'];

				if( strpos( $atts['bottom_border'], 'diagonal' ) !== false )
				{
					$backgroundElColor = '#333333';
					if( isset( $atts['bottom_border_diagonal_color'] ) )
					{
						$backgroundElColor = $atts['bottom_border_diagonal_color'];
					}
				}

				if( ! empty( $backgroundElColor ) )
				{
					$element_styling->add_styles( 'extra-border-inner', array( 'background-color' => $backgroundElColor ) );
				}
			}

			//	SVG Dividers
			$element_styling->add_classes( 'divider-top', array( 'avia-divider-svg', 'avia-divider-svg-' . $atts['svg_div_top'] ) );
			$element_styling->add_classes( 'divider-bottom', array( 'avia-divider-svg', 'avia-divider-svg-' . $atts['svg_div_bottom'] ) );

			$element_styling->add_callback_styles( 'divider-top-svg', array( 'svg_div_top_svg', 'svg_div_top_color' ) );
			$element_styling->add_callback_styles( 'divider-bottom-svg', array( 'svg_div_bottom_svg', 'svg_div_bottom_color' ) );

			$element_styling->add_callback_classes( 'divider-top', array( 'svg_div_top' ) );
			$element_styling->add_callback_classes( 'divider-bottom', array( 'svg_div_bottom' ) );

			if( ! empty( $atts['svg_div_top'] ) || ! empty( $atts['svg_div_bottom'] ) )
			{
				//	This is a fix that svg shapes can be placed top or bottom. By default avia-section is position: static;
				$element_styling->add_styles( 'section-outer', array( 'position' => 'relative' ) );
			}

			//	z-index
			if( $element_styling->add_responsive_styles( 'section-outer', 'css_position', $atts, $this ) > 0 )
			{
				$element_styling->add_responsive_styles( 'section-outer-curtain', 'css_position', $atts, $this );

				$element_styling->add_classes( 'section-outer', array( 'av-custom-positioned' ) );
				//	needed to allow z-index because by default static
				$element_styling->add_styles( 'section-outer', array( 'position' => 'relative' ) );
			}

			if( ! empty( $atts['fold_type'] ) )
			{
				//	fold / unfold section
				$f_classes = array(
							$atts['fold_type'],
							'avia-fold-section-wrap',
							'avia-fold-init',
							$atts['fold_element_class'],
							$atts['fold_text_style'],
							empty( $atts['fold_btn_align'] ) ? 'align-left' : $atts['fold_btn_align']
						);

				$element_styling->add_classes( 'fold-section', $f_classes );

				if( $atts['fold_text_style'] == '' )
				{
					$element_styling->add_styles( 'fold-button', array( 'color' => $atts['fold_text_color'] ) );
				}

				if( $atts['fold_text_style'] != '' && $atts['fold_btn_color'] == 'custom' )
				{
					$element_styling->add_styles( 'fold-button', array(
													'background-color'	=> $atts['fold_btn_bg_color'],
													'color'				=> $atts['fold_btn_font_color'],
												) );
				}

				$element_styling->add_responsive_font_sizes( 'fold-button', 'size-btn-text', $atts, $this );

				if( ! empty( $atts['fold_overlay_color'] ) )
				{
					$bg_rgb = avia_backend_hex_to_rgb_array( $atts['fold_overlay_color'] );

					$element_styling->add_styles( 'fold-unfold-after', array(
													'background'	=> "linear-gradient( to bottom, rgba({$bg_rgb[0]},{$bg_rgb[1]},{$bg_rgb[2]},0), rgba({$bg_rgb[0]},{$bg_rgb[1]},{$bg_rgb[2]},1) )"
												) );
				}

				$element_styling->add_styles( 'fold-unfold', array( 'max-height' => $atts['fold_height'] . 'px' ) );

				if( ! empty( $atts['fold_timer'] ) )
				{
					$rules = $element_styling->transition_duration_rules( $atts['fold_timer'] );

					$element_styling->add_styles( 'fold-unfold', $rules );
					$element_styling->add_styles( 'fold-unfold-after', $rules );
				}

				$element_styling->add_styles( 'fold-unfold-folded-after', array( 'z-index' => $atts['z_index_fold'] ) );

				//	prepare attributes for frontend
				$element_styling->add_data_attributes( 'fold-section', array(
												'type'		=> $atts['fold_type'],
												'height'	=> $atts['fold_height'],
												'more'		=> $atts['fold_more'],
												'less'		=> $atts['fold_less'],
												'context'	=> __CLASS__
											) );
			}


			$selectors = array(
							'section-outer'				=> ".avia-section.{$element_id}",
							'section-outer-curtain'		=> ".av-curtain-footer.av-curtain-activated #main .avia-section.{$element_id}",
							'section-container'			=> ".avia-section.{$element_id} .container.av-section-cont-open",
							'section-parallax-inner'	=> ".avia-section.{$element_id} .av-parallax .av-parallax-inner",
							'overlay'					=> ".avia-section.{$element_id} .av-section-color-overlay",
							'scroll-down'				=> "#top .avia-section.{$element_id} .scroll-down-link",
							'extra-border-inner'		=> ".avia-section.{$element_id} .av-extra-border-element .av-extra-border-inner",
							'divider-top-div'			=> ".avia-section.{$element_id} .avia-divider-svg-top",
							'divider-bottom-div'		=> ".avia-section.{$element_id} .avia-divider-svg-bottom",
							'divider-top-svg'			=> ".avia-section.{$element_id} .avia-divider-svg-top svg",
							'divider-bottom-svg'		=> ".avia-section.{$element_id} .avia-divider-svg-bottom svg",

							'fold-section'				=> ".avia-fold-unfold-section.{$atts['fold_element_class']}",
							'fold-unfold'				=> ".avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-unfold-container",
							'fold-unfold-after'			=> "#top .avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-unfold-container:after",
							'fold-unfold-folded-after'	=> ".avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-unfold-container.folded::after",
							'fold-button'				=> "#top .avia-fold-unfold-section.{$atts['fold_element_class']} .av-fold-button-container"
				);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Returns the modal popups svg divider preview windows in $svg_list
		 *
		 * @since 4.8.4
		 * @param array $args
		 * @param array $svg_list
		 * @return array
		 */
		public function build_svg_divider_preview( array $args, array $svg_list )
		{
			//	clear content and not needed settings - we only need minimal stylings
			$args['content'] = '';
			unset( $args['atts']['content'] );

//			$args['atts']['padding'] = '';
			$args['atts']['custom_margin'] = '';


			$result = $this->get_element_styles( $args );

			extract( $result );

			$dummy_text = __( 'Dummy Content to demonstrate &quot;Bring To Front&quot; option', 'avia_framework' );

			foreach( $svg_list as $id => $info )
			{
				$svg_height = $atts[ $id . '_height' ];
				$svg_max_height = $atts[ $id . '_max_height' ];

				if( ! is_numeric( $svg_height ) )
				{
					$svg_height = is_numeric( $svg_max_height ) ? $svg_max_height : 100;
				}

				$dummy_dist = $svg_height > 30 ? 20 : 5;
				$dummy_height = $svg_height + 20;

				$style_col = array(
								'height'	=> max( $svg_height + 120, 150 ) . 'px'
							);

				$style_dummy = array(
								'height'		=> $dummy_height . 'px',
								'line-height'	=> $dummy_height . 'px'
							);

				if( 'top' == $info['location'] )
				{
					$class = 'av-top-divider';
					$element_styling->add_styles( 'preview-column-top', $style_col );
					$element_styling->add_styles( 'preview-dummy-top', array( 'top' => $dummy_dist . 'px' ) );
					$element_styling->add_styles( 'preview-dummy-top', $style_dummy );
				}
				else
				{
					$class = 'av-bottom-divider';
					$element_styling->add_styles( 'preview-column-bottom', $style_col );
					$element_styling->add_styles( 'preview-dummy-bottom', array( 'bottom' => $dummy_dist . 'px' ) );
					$element_styling->add_styles( 'preview-dummy-bottom', $style_dummy );
				}

				$selectors = array(
								'preview-column-top'	=> ".avia-section.{$element_id}.av-top-divider",
								'preview-column-bottom'	=> ".avia-section.{$element_id}.av-bottom-divider",
								'preview-dummy-top'		=> ".avia-section.{$element_id}.av-top-divider .av-dummy-text",
								'preview-dummy-bottom'	=> ".avia-section.{$element_id}.av-bottom-divider .av-dummy-text"
							);


				$element_styling->add_selectors( $selectors );

				$style_tag = $element_styling->get_style_tag( $element_id );

				$html  = '';
				$html .= $style_tag;
				$html .= "<div class='svg-shape-container avia-section {$element_id} {$class}'>";

				if( ! empty( $atts[ $id ] ) )
				{
					$html .= AviaSvgShapes()->get_svg_dividers( $atts, $element_styling, $info['location'] );
				}

				$html .=		'<div class="av-dummy-text">';
				$html .=			'<p>' . esc_html( $dummy_text ) . '</p>';
				$html .=		'</div>';

				$html .= '</div>';

				$svg_list[ $id ]['html'] = $html;
			}


			return $svg_list;
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
			global $avia_config;

			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			avia_sc_section::$section_count ++;


			$params	= array();

			$params['id'] = AviaHelper::save_string( $id, '-', 'av_section_' . avia_sc_section::$section_count );
			$params['custom_markup'] = $meta['custom_markup'];
			$params['aria_label'] = $meta['aria_label'];
			$params['attach'] = '';


			/*set background image*/
			if( $src != '' )
			{
				if( $attach == 'parallax' )
				{
					$attachment_class = $element_styling->get_class_string( 'section-parallax-inner' );
					$speed = apply_filters( 'avf_parallax_speed', '0.3', $params['id'] );

					$params['attach'] .= "<div class='av-parallax' data-avia-parallax-ratio='{$speed}' >";
					$params['attach'] .=	"<div class='av-parallax-inner {$color} {$attachment_class}'>";
					$params['attach'] .=	'</div>';
					$params['attach'] .= '</div>';
				}

				$params['data'] = "data-section-bg-repeat='{$repeat}'";
			}

			$pre_wrap = '<div class="av-section-color-overlay-wrap">' ;

			/*check/create overlay*/
			$overlay = '';
			if( ! empty( $overlay_enable ) )
			{
				$overlay = '<div class="av-section-color-overlay"></div>';

				$params['attach'] .= $pre_wrap . $overlay;
			}

			if( ! empty( $scroll_down ) )
			{
				$arrow_class = $element_styling->get_class_string( 'scroll-down' );

				if( empty( $overlay ) )
				{
					$params['attach'] .= $pre_wrap;
				}

				$params['attach'] .= "<a href='#next-section' title='' class='scroll-down-link {$arrow_class}' " . av_icon_string( 'scrolldown' ) . '></a>';
			}


			$params['class'] = $element_styling->get_class_string( 'section-outer' );

			$params['min_height'] = $min_height;
			$params['min_height_option'] = $min_height_option;
			$params['min_height_px'] = $min_height_px;
			$params['video'] = $video;
			$params['video_ratio'] = $video_ratio;
			$params['video_mobile_disabled'] = $video_mobile_disabled;

			if( isset( $meta['index'] ) && $meta['index'] >= 0 )
			{
				if( $meta['index'] == 0 )
				{
					$params['main_container'] = true;
				}

				if( $meta['index'] == 0 || ( isset( $meta['siblings']['prev']['tag'] ) && in_array( $meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section ) ) )
				{
					$params['close'] = false;
				}
			}

			if( $bottom_border == 'border-extra-arrow-down' )
			{
				$params['class'] .= ' av-arrow-down-section';
			}

			$params['container_style_tag'] = $element_styling->get_style_tag( $element_id );
			$params['svg_dividers'] = AviaSvgShapes()->get_svg_dividers( $atts, $element_styling );


			$fold_after = '';
			if( $fold_type != '' )
			{
				$fold_section_class = $element_styling->get_class_string( 'fold-section' );
				$fold_section_data = $element_styling->get_data_attributes_json_string( 'fold-section', 'fold_unfold' );
				$args = [
						'atts'			=> $atts,
						'wrapper_class'	=> 'av-section-fold-btn-wrap av-fold-btn-padding',
						'context'		=> __CLASS__
					];

				$params['before_new']  =	"<div id='{$params['id']}-fold-unfold' class='{$fold_section_class} {$atts['color']}' {$fold_section_data}>";
				$params['before_new'] .=		'<div class="av-fold-unfold-container folded"></div>';
				$params['before_new'] .=		aviaFrontTemplates::fold_unfold_button( $args );
				$params['before_new'] .=	'</div>';
			}

			$avia_config['layout_container'] = 'section';

			$output  = '';
			$output .= avia_new_section( $params );
			$output .= ShortcodeHelper::avia_remove_autop( $content, true ) ;

			/*set extra arrow element*/
			if( strpos( $bottom_border, 'border-extra' ) !== false )
			{
				if( strpos( $bottom_border, 'diagonal') !== false )
				{
					// bottom_border_diagonal_direction // bottom_border_diagonal_color
					$bottom_border .= ' ' . $bottom_border_diagonal_direction . ' ' . $bottom_border_style;
				}

				avia_sc_section::$add_to_closing = "<div class='av-extra-border-element {$bottom_border}'><div class='av-extra-border-outer'><div class='av-extra-border-inner'></div></div></div>";
			}
			else
			{
				avia_sc_section::$add_to_closing = '';
			}


			//next section needs an extra closing tag if overlay with wrapper was added:
			if( $overlay || ! empty( $scroll_down ) )
			{
				avia_sc_section::$close_overlay = '</div>';
			}
			else
			{
				avia_sc_section::$close_overlay = '';
			}

			$skipSecond = false;
			
			//if the next tag is a section dont create a new section from this shortcode
			if( ! empty( $meta['siblings']['next']['tag'] ) && in_array( $meta['siblings']['next']['tag'], AviaBuilder::$full_el ) )
			{
				$skipSecond = true;
			}

			//if there is no next element dont create a new section. if we got a sidebar always create a next section at the bottom
			if( empty( $meta['siblings']['next']['tag'] ) && ! avia_has_sidebar() )
			{
				$skipSecond = true;
			}

			/**
			 *
			 * @since 5.6.11
			 * @param string $output			HTML content
			 * @param array $atts				shortcode attributes
			 * @param array $params				rendered to build section HTML
			 * @param boolean $skipSecond		false, if a new section is added
			 * @param avia_sc_section $this
			 * @return string
			 */
			$output = apply_filters( 'avf_sc_section_before_close', $output, $atts, $params, $skipSecond, $this );

			if( empty( $skipSecond ) )
			{
				$new_params['id'] = 'after_section_' . avia_sc_section::$section_count;
				$new_params['before_new'] = $fold_after;

				$output .= avia_new_section( $new_params );
			}
			else
			{
				$output .= $fold_after;
			}

			unset( $avia_config['layout_container'] );

			return $output;
		}

		/**
		 * Get background image for ALB editor canvas only
		 *
		 * @param array $args
		 * @return string
		 */
		protected function get_bg_string( array $args )
		{
			$style = '';

			if( ! empty( $args['attachment'] ) || ! empty( $args['src'] ) )
			{
				$image = false;

				if( empty( $args['attachment'] ) )
				{
					//	manually added image link
					$image = $args['src'];
				}
				else
				{
					$src = wp_get_attachment_image_src( $args['attachment'], $args['attachment_size'] );
					if( ! empty( $src[0] ) )
					{
						$image = $src[0];
					}
				}

				if( $image )
				{
					$element_styling = new aviaElementStyling( $this, 'xxx' );

//					$bg = ! empty( $args['custom_bg'] ) ? $args['custom_bg'] : 'transparent';
					$bg = 'transparent';
					$pos = $element_styling->background_position_string( $args['position'], 'center center' );
					$repeat = ! empty( $args['repeat'] ) ? $args['repeat'] : 'no-repeat';
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

					$style = "background: {$bg} url({$image}) {$repeat} {$pos}; {$extra}";
				}
			}

			return $style;
		}
	}
}


if( ! function_exists( 'avia_new_section' ) )
{
	/**
	 *
	 * @param array $params
	 * @return string
	 */
	function avia_new_section( $params = array() )
	{
		global $avia_section_markup, $avia_config;

		$defaults = array(
						'class'				=> 'main_color',
						'bg'				=> '',
						'custom_margin'		=> '',
						'close'				=> true,
						'open'				=> true,
						'open_structure'	=> true,
						'open_color_wrap'	=> true,
						'data'				=> '',
						'style'				=> '',
						'id'				=> '',
						'main_container'	=> false,
						'min_height'		=> '',
						'min_height_option'	=> '',
						'min_height_px'		=> '',
						'video'				=> '',
						'video_ratio'		=> '16:9',
						'video_mobile_disabled'	=> '',
						'attach'			=> '',
						'before_new'		=> '',
						'custom_markup'		=> '',
						'aria_label'		=> '',			//	set to true to force id as label
						'container_style_tag'	=> '',		//	added with 4.8.4 - styles from post CSS management
						'svg_dividers'			=> ''		//	added with 4.8.4
					);

		$defaults = array_merge( $defaults, $params );

		extract( $defaults );

		$post_class = '';
		$output = '';
		$bg_slider_html = '';
		$container_style = '';
		$id_val = $id;

		$id = ! empty( $id_val ) ? "id='{$id_val}'" : '';

		if( ! empty( $aria_label ) || ! empty( $id_val ) )
		{
			if( true === $aria_label )
			{
				$label = $id_val;
			}
			else if ( ! empty( $aria_label ) )
			{
				$label = $aria_label;
			}
			else
			{
				$label = '';
			}

			$aria_label = ! empty( $label ) ? "aria-label='{$label}'" : '';
		}
		else
		{
			$aria_label = '';
		}


		//close old content structure. only necessary when previous element was a section. other fullwidth elements dont need this
		if( $close )
		{
			$cm = avia_section_close_markup();

			$output .= "</div></div>{$cm}</div>" . avia_sc_section::$add_to_closing . avia_sc_section::$close_overlay . '</div>';
			avia_sc_section::$add_to_closing = '';
			avia_sc_section::$close_overlay = '';
		}

		//start new
		if( $open )
		{
			if( function_exists( 'avia_get_the_id' ) )
			{
				$post_class = 'post-entry-' . avia_get_the_id();
			}

			if( $open_color_wrap )
			{
				if( ! empty( $min_height ) )
				{
					$cls_vw = 'percent_width' == $min_height_option ? 'vw' : '';

					$class .= " av-minimum-height av-minimum-height-{$min_height}{$cls_vw} av-height-{$min_height_option} ";

					if( is_numeric( $min_height ) )
					{
						$data .= " data-av_minimum_height_pc='{$min_height}' data-av_min_height_opt='{$min_height_option}'";
					}

					if( $min_height == 'custom' && $min_height_px != '' )
					{
						$min_height_px = (int) $min_height_px;
						$container_style = "style='height:{$min_height_px}px'";

						//	added for slideshow section
						$data .= " data-av_minimum_height_px='{$min_height_px}'";
					}
				}

				if( ! empty( $video ) )
				{
					$slide = array(
								'shortcode'	=> 'av_slide',
								'content'	=> '',
								'attr'		=> array(
													'id'				=> '',
													'video'				=> $video ,
													'slide_type'		=> 'video',
													'video_mute'		=> true,
													'video_loop'		=> true,
													'video_ratio'		=> $video_ratio,
													'video_controls'	=> 'disabled',
													'video_section_bg'	=> true,
													'video_format'		=> '',
													'video_mobile'		=> '',
													'video_mobile_disabled'	=> $video_mobile_disabled
												)
								);

					$sc_class = Avia_Builder()->get_shortcode_class( 'av_slideshow' );

					$video_atts = array();
					$video_content = array( $slide );

					$bg_slider = $sc_class->get_avia_slideshow_object( $video_atts, $video_content, 'av_slideshow', array() );
					if( $bg_slider instanceof avia_slideshow )
					{
						$bg_slider->set_extra_class( 'av-section-video-bg' );
						$bg_slider_html = $bg_slider->html();
					}

					$class .= ' av-section-with-video-bg';
					$class .= ! empty( $video_mobile_disabled ) ? ' av-section-mobile-video-disabled' : '';
					$data .= " data-section-video-ratio='{$video_ratio}'";
				}

	        	$output .= $before_new;

				/**
				 * We can't just overwrite style since it might be passed by a function. eg the menu element passes z-index. need to merge the style strings
				 *
				 * @since 4.5.1 by Kriesi
				 */
				$extra_style = trim( "{$bg} {$custom_margin}" );
				if( ! empty( $extra_style ) )
				{
					$style = trim( $style );
					if( empty( $style ) )
					{
						$style = "style='{$extra_style}' ";
					}
					else
					{
						$style = str_replace( "style='", "style='{$extra_style} ", $style );
						$style = str_replace( 'style="', 'style="' . $extra_style . ' ', $style );
					}
				}

				if( $class == 'main_color' )
				{
					$class .= ' av_default_container_wrap';
				}

				$output .= $container_style_tag;
				$output .= "<div {$id} {$aria_label} class='{$class} container_wrap " . avia_layout_class( 'main' , false ) . "' {$style} {$data}>";
				$output .=		$svg_dividers;
				$output .=		$bg_slider_html;
				$output .=		$attach;

				$output .= apply_filters( 'avf_section_container_add', '', $defaults );
			}


			//this applies only for sections. other fullwidth elements dont need the container for centering
			if( $open_structure )
			{
				if( ! empty( $main_container ) )
				{
					$markup = 'main ' . avia_markup_helper( array( 'context' => 'content', 'echo' => false, 'custom_markup' => $custom_markup ) );
					$avia_section_markup = 'main';
				}
				else
				{
					$markup = 'div';
				}

				$output .= "<div class='container av-section-cont-open' {$container_style}>";
				$output .=		"<{$markup} class='template-page content  " . avia_layout_class( 'content', false ) . " units'>";
				$output .=			"<div class='post-entry post-entry-type-page {$post_class}'>";
				$output .=				"<div class='entry-content-wrapper clearfix'>";
			}
	    }

		return $output;

	}
}


if( ! function_exists( 'avia_section_close_markup' ) )
{
	/**
	 *
	 * @return string
	 */
	function avia_section_close_markup()
	{
		global $avia_section_markup;

		if( ! empty( $avia_section_markup ) )
		{
			$avia_section_markup = false;
			$close_markup = '</main><!-- close content main element -->';

		}
		else
		{
			$close_markup = '</div><!-- close content main div -->';
		}

		return $close_markup;
	}
}

if( ! function_exists( 'avia_section_after_element_content' ) )
{
	/**
	 *
	 * @param array $meta
	 * @param string $second_id
	 * @param boolean $skipSecond
	 * @param string $extra
	 * @return string
	 */
	function avia_section_after_element_content( $meta, $second_id = '', $skipSecond = false, $extra = '' )
	{
		$output = '</div>'; //close section
		$output .= $extra;

		//if the next tag is a section dont create a new section from this shortcode
		if( ! empty( $meta['siblings']['next']['tag'] ) && in_array( $meta['siblings']['next']['tag'], AviaBuilder::$full_el ) )
		{
			$skipSecond = true;
		}

		//if there is no next element dont create a new section.
		if( empty( $meta['siblings']['next']['tag'] ) )
		{
			$skipSecond = true;
		}

		if( empty( $skipSecond ) )
		{
			$output .= avia_new_section( array( 'close' => false, 'id' => $second_id ) );
		}

		return $output;
	}
}

