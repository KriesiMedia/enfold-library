<?php
/**
 * Special Heading
 *
 * Creates a special Heading
 */

// Don't load directly
if( ! defined( 'ABSPATH' ) ) { exit; }



if( ! class_exists( 'avia_sc_heading', false ) )
{
	class avia_sc_heading extends aviaShortcodeTemplate
	{

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Special Heading', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-heading.png';
			$this->config['order']			= 93;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_heading';
			$this->config['modal_data']		= array( 'modal_class' => 'mediumscreen' );
			$this->config['tooltip']		= __( 'Creates a special Heading', 'avia_framework' );
			$this->config['preview']		= true;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['hide_desktop_fonts']	= 'block';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-heading', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/heading/heading{$min_css}.css", array( 'avia-layout' ), $ver );
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
							'template_id'	=> $this->popup_key( 'content_heading' )
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
													$this->popup_key( 'styling_fonts' ),
													$this->popup_key( 'styling_colors' ),
													$this->popup_key( 'styling_spacing' )
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
							'name'				=> __( 'Heading Text', 'avia_framework' ),
							'id'				=> 'heading',
							'type'				=> 'input',
							'std'				=> __( 'Hello', 'avia_framework' ),
							'container_class'	=> 'avia-element-fullwidth',
							'lockable'			=> true,
							'tmpl_set_default'	=> false,
							'dynamic'			=> []
						),

						array(
							'name'		=> __( 'Heading Type', 'avia_framework' ),
							'desc'		=> __( 'Select which kind of heading you want to display.', 'avia_framework' ),
							'id'		=> 'tag',
							'type'		=> 'select',
							'std'		=> 'h3',
							'lockable'	=> true,
							'subtype'	=> array( 'H1' => 'h1', 'H2' => 'h2', 'H3' => 'h3', 'H4' => 'h4', 'H5' => 'h5', 'H6' => 'h6' )
						),

						array(
							'name'		=> __( 'Heading Style', 'avia_framework' ),
							'desc'		=> __( 'Select a heading style', 'avia_framework' ),
							'id'		=> 'style',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default Style', 'avia_framework' )										=> '',
												__( 'Heading Style Modern (left)', 'avia_framework' )						=> 'blockquote modern-quote' ,
												__( 'Heading Style Modern (centered)', 'avia_framework' )					=> 'blockquote modern-quote modern-centered',
												__( 'Heading Style Modern (right)', 'avia_framework' )						=> 'blockquote modern-quote modern-right',
												__( 'Heading Style Classic (left, italic)', 'avia_framework' )				=> 'blockquote classic-quote classic-quote-left',
												__( 'Heading Style Classic (centered, italic)', 'avia_framework' )			=> 'blockquote classic-quote',
												__( 'Heading Style Classic (right, italic)', 'avia_framework' )				=> 'blockquote classic-quote classic-quote-right',
												__( 'Heading Style Elegant (centered, optional icon)', 'avia_framework' )	=> 'blockquote elegant-quote elegant-centered'
											)
						),

						array(
							'name'		=> __( 'Subheading', 'avia_framework' ),
							'desc'		=> __( 'Add an extra descriptive subheading above or below the actual heading', 'avia_framework' ),
							'id'		=> 'subheading_active',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
				            'required'	=> array( 'style', 'not', '' ),
							'subtype'	=> array(
												__( 'No Subheading', 'avia_framework' )				=> '',
												__( 'Display subheading above', 'avia_framework' )	=> 'subheading_above',
												__( 'Display subheading below', 'avia_framework' )	=> 'subheading_below'
											)
							),

						array(
							'name'		=> __( 'Subheading Text', 'avia_framework' ),
							'desc'		=> __( 'Add your subheading here', 'avia_framework' ),
							'id'		=> 'content',
							'type'		=> 'textarea',
							'std'		=> '',
							'lockable'	=> true,
							'dynamic'	=> [],
							'required'	=> array( 'subheading_active', 'not', '' ),
							'tmpl_set_default'	=> false,
						),

						array(
							'name'		=> __( 'Icon', 'avia_framework' ),
							'desc'		=> __( 'Select to show an additional icon above headline', 'avia_framework' ),
							'id'		=> 'show_icon',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'style', 'equals', 'blockquote elegant-quote elegant-centered' ),
							'subtype'	=> array(
												__( 'No Icon', 'avia_framework' )		=> '',
												__( 'Display Icon', 'avia_framework' )	=> 'custom_icon'
											)
						),

						array(
							'name'		=> __( 'Icon', 'avia_framework' ),
							'desc'		=> __( 'Select an icon to display above the headline', 'avia_framework' ),
							'id'		=> 'icon',
							'type'		=> 'iconfont',
							'std'		=> '',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' ),
							'required'	=> array( 'show_icon', 'equals', 'custom_icon' )
						)

				);

			if( current_theme_supports( 'avia_builder_add_heading_type_size_class' ) )
			{
				$element = array(
							'name'		=> __( 'Heading Type Font Size', 'avia_framework' ),
							'desc'		=> __( 'Select a font size for the heading. This will add a class (e.g. av-is-h1) to the heading and allows easier styling with custom CSS. Styling is not supported by default from theme.', 'avia_framework' ),
							'id'		=> 'tag_extra_class',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array( __( 'Use default', 'avia_framework' ) => '', 'H1' => 'h1', 'H2' => 'h2', 'H3' => 'h3', 'H4' => 'h4', 'H5' => 'h5', 'H6' => 'h6' )
						);

				array_splice( $c, 2, 0, array( $element ) );
			}


			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_heading' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$font_size_array = array(
						__( 'Default Size', 'avia_framework' ) => '',
						__( 'Flexible font size (adjusts to screen width)' , 'avia_framework' )	=> AviaHtmlHelper::number_array( 3, 7, 0.5, array(), 'vw', '', 'vw' ),
						__( 'Fixed font size' , 'avia_framework' )								=> AviaHtmlHelper::number_array( 10, 150, 1, array(), 'px', '', '' )
					);

			$c = array(
						array(
							'name'			=> __( 'Heading Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the heading.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'textfield'		=> true,
							'hide_desktop'	=> true,
							'lockable'		=> true,
							'subtype'		=> array(
												'default'	=> $font_size_array,
												'desktop'	=> $font_size_array,
												'medium'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'size',
												'desktop'	=> 'av-desktop-font-size-title',
												'medium'	=> 'av-medium-font-size-title',
												'small'		=> 'av-small-font-size-title',
												'mini'		=> 'av-mini-font-size-title'
											)
						),

						array(
							'name'			=> __( 'Subheading Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the subheading.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'textfield'		=> true,
							'hide_desktop'	=> true,
							'lockable'		=> true,
							'required'		=> array( 'subheading_active', 'not', '' ),
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'subheading_size',
												'desktop'	=> 'av-desktop-font-size',
												'medium'	=> 'av-medium-font-size',
												'small'		=> 'av-small-font-size',
												'mini'		=> 'av-mini-font-size'
											)
						),

						array(
							'name'			=> __( 'Icon Font Size', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the icon', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'textfield'		=> true,
							'hide_desktop'	=> true,
							'lockable'		=> true,
							'required'		=> array( 'show_icon', 'equals', 'custom_icon' ),
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'icon_size',
												'desktop'	=> 'av-desktop-font-size-1',
												'medium'	=> 'av-medium-font-size-1',
												'small'		=> 'av-small-font-size-1',
												'mini'		=> 'av-mini-font-size-1'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Sizes', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_fonts' ), $template );

			$c = array(
						array(
							'name'		=> __( 'Heading Color', 'avia_framework' ),
							'desc'		=> __( 'Select a heading color', 'avia_framework' ),
							'id'		=> 'color',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default Color', 'avia_framework' )	=> '',
												__( 'Meta Color', 'avia_framework' )	=> 'meta-heading',
												__( 'Custom Color', 'avia_framework' )	=> 'custom-color-heading'
											)
							),

						array(
							'name'		=> __( 'Custom Font Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom font color for your Heading here', 'avia_framework' ),
							'id'		=> 'custom_font',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'custom-color-heading' )
						),

						array(
							'name'		=> __( 'Custom Subheading Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for your subheading here', 'avia_framework' ),
							'id'		=> 'subheading_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'custom-color-heading' )
						),

						array(
							'name'		=> __( 'Custom Separator Line Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom color for the separator line beside the title here (only available on some styles)', 'avia_framework' ),
							'id'		=> 'seperator_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'custom-color-heading' )
						),

						array(
							'name'		=> __( 'Custom Icon Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom icon color for your Heading here', 'avia_framework' ),
							'id'		=> 'icon_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( 'color', 'equals', 'custom-color-heading' )
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Colors', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors' ), $template );


			$c = array(
						array(
								'type'			=> 'template',
								'template_id'	=> 'margin_padding',
								'name'			=> '',
								'desc'			=> '',
								'content'		=> 'margin',
								'name_margin' 	=> __( 'Element Margin', 'avia_framework' ),
								'desc_margin' 	=> __( 'Set the margin to other elements here. Valid CSS units are accepted, eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' ),
								'id_margin'		=> 'margin',
								'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'margin_padding',
							'name'			=> '',
							'desc'			=> '',
							'content'		=> 'padding',
							'name_padding' 	=> __( 'Headline Text (And Icon) Padding', 'avia_framework' ),
							'desc_padding' 	=> __( 'Set a distance around the headline text and a possible icon. Valid CSS units are accepted, eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' ),
							'id_padding'	=> 'headline_padding',
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'margin_padding',
							'name'			=> '',
							'desc'			=> '',
							'content'		=> 'padding',
							'sync_padding'	=> false,
							'name_padding' 	=> __( 'Element Bottom Padding', 'avia_framework' ),
							'desc_padding' 	=> __( 'Set the bottom padding for the element. Valid CSS units are accepted, eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' ),
							'id_padding'	=> 'padding',
							'std_padding'	=> '10',
							'lockable'		=> true,
							'multi_padding'	=> array(
													'bottom'	=> __( 'Bottom Padding', 'avia_framework' )
												)
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'margin_padding',
							'name'			=> '',
							'desc'			=> '',
							'content'		=> 'padding',
							'sync_padding'	=> false,
							'name_padding' 	=> __( 'Icon Bottom Padding', 'avia_framework' ),
							'desc_padding' 	=> __( 'Set the bottom padding for the icon. Valid CSS units are accepted, eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' ),
							'id_padding'	=> 'icon_padding',
							'std_padding'	=> '10',
							'lockable'		=> true,
							'required'		=> array( 'show_icon', 'equals', 'custom_icon' ),
							'multi_padding'	=> array(
													'bottom'	=> __( 'Bottom Padding', 'avia_framework' )
												)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Spacing', 'avia_framework' ),
								'content'		=> $c
							)
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_spacing' ), $template );

			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Header Text Link', 'avia_framework' ),
							'desc'			=> __( 'Do you want to apply a link to the header text?', 'avia_framework' ) . Avia_Dynamic_Content()->modal_link_message_info(),
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'target_id'		=> 'link_target',
							'lockable'		=> true,
							'title_attr'	=> true,
							'dynamic'		=> [ 'wp_custom_field' ],
							'dynamic_clear'	=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_link' ), $c );

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
			/**
			 * Fix a bug in 4.7 and 4.7.1 renaming option id (no longer backwards comp.) - can be removed in a future version again
			 */
			if( isset( $params['args']['linktarget'] ) )
			{
				$params['args']['link_target'] = $params['args']['linktarget'];
			}

			$default = array();
			$locked = array();
			$attr = $params['args'];
			$content = $params['content'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode'], $default, $locked, $content );

			extract( av_backend_icon( array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font'

			$content = stripslashes( wpautop( trim( html_entity_decode( $content ) ) ) );


			$params['innerHtml']  = "<div class='avia_textblock avia_textblock_style avia-special-heading' data-update_element_template='yes'>";
			$params['innerHtml'] .= 	'<div ' . $this->class_by_arguments_lockable( 'tag, style, color, subheading_active, show_icon', $attr, $locked ) . '>';
			$params['innerHtml'] .= 		'<div ' . $this->update_option_lockable( 'content', $locked ) . " class='av-subheading-top av-subheading'>{$content}</div>";
			$params['innerHtml'] .=			'<span class="avia-heading-icon">';
			$params['innerHtml'] .=				'<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
			$params['innerHtml'] .=					'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_icon_char'>{$display_char}</span>";
			$params['innerHtml'] .=				'</span>';
			$params['innerHtml'] .=			'</span>';
			$params['innerHtml'] .= 		'<div ' . $this->update_option_lockable( 'heading', $locked ) . '>';
			$params['innerHtml'] .=				stripslashes( trim( htmlspecialchars_decode( $attr['heading'] ) ) );
			$params['innerHtml'] .= 		'</div>';
			$params['innerHtml'] .= 		'<div ' . $this->update_option_lockable( 'content', $locked ) . " class='av-subheading-bottom av-subheading'>{$content}</div>";
			$params['innerHtml'] .= 	'</div>';
			$params['innerHtml'] .= '</div>';

			$params['class'] = '';

			return $params;
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
			$result = parent::get_element_styles( $args );

			extract( $result );

			/**
			 * Fix a bug in 4.7 and 4.7.1 renaming option id (no longer backwards comp.) - can be removed in a future version again
			 */
			if( isset( $atts['linktarget'] ) )
			{
				$atts['link_target'] = $atts['linktarget'];
			}

			$default = array(
						'heading'			=> '',
						'tag'				=> 'h3',
						'link_apply'		=> null,		//	backwards comp. < version 1.0
						'link'				=> '',
						'link_target'		=> '',
						'style'				=> '',
						'show_icon'			=> '',
						'icon'				=> '',
						'font'				=> '',
						'icon_size'			=> '',
						'icon_padding'		=> 10,
						'icon_color'		=> '',
						'size'				=> '',
						'subheading_active' => '',
						'subheading_size'	=> '',
						'margin'			=> '',
						'padding'			=> '5',
						'headline_padding'	=> '',
						'color'				=> '',
						'custom_font'		=> '',
						'seperator_color'	=> '',
						'subheading_color'	=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );


			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			Avia_Dynamic_Content()->read( $atts, $this, $shortcodename, $content );
			$atts['link'] = Avia_Dynamic_Content()->check_link( $atts['link_dynamic'], $atts['link'], [ 'no', 'manually', 'single', 'taxonomy' ] );

			if( empty( $atts['subheading_size'] ) )
			{
				$atts['subheading_size'] = '15';
			}

			if( empty( $atts['icon_size'] ) )
			{
				$atts['icon_size'] = '25';
			}

			//	backwards comp. - prepare responsive font sizes for media query
			$atts['size-title'] = $atts['size'];
			$atts['size'] = $atts['subheading_size'];
			$atts['size-1'] = $atts['icon_size'];

			//	backwards comp. < version 1.0
			if( ! is_null( $atts['link_apply'] ) )
			{
				if( empty( $atts['link_apply'] ) )
				{
					$atts['link'] = '';
					$atts['link_target'] = '';
				}
			}


			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'av-special-heading',
						$element_id,
						'av-special-heading-' . $atts['tag']
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $atts, array( 'color', 'style' ) );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			$element_styling->add_responsive_font_sizes( 'heading-text', 'size-title', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'subheading', 'size', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'heading-icon', 'size-1', $atts, $this );


			//if the heading contains a strong tag make apply a custom class that makes the rest of the font appear smaller for a better effect
			if( strpos( $atts['heading'], '<strong>' ) !== false )
			{
				$element_styling->add_classes( 'container', 'av-thin-font' );
			}

			$element_styling->add_responsive_styles( 'container', 'margin', $atts, $this );
			$element_styling->add_responsive_styles( 'heading-text', 'headline_padding', $atts, $this );
			$element_styling->add_responsive_styles( 'container', 'padding', $atts, $this );

			/**
			 * @link https://github.com/KriesiMedia/Enfold-Feature-Requests/issues/93
			 * @since 6.0.1
			 */
			if( current_theme_supports( 'avia_builder_add_heading_type_size_class' ) && ! empty( $atts['tag_extra_class'] ) )
			{
				$element_styling->add_classes( 'container', "av-font-size-is-{$atts['tag_extra_class']}" );
				$element_styling->add_classes( 'heading-text', "av-is-{$atts['tag_extra_class']}" );
			}

			// if the color is a custom hex value add the styling for both border and font
			if( $atts['color'] == 'custom-color-heading' && $atts['custom_font'] )
			{
				$element_styling->add_styles( 'container', array( 'color' => $atts['custom_font'] ) );
				$element_styling->add_styles( 'heading-border', array( 'border-color' => $atts['custom_font'] ) );
				$element_styling->add_classes( 'subheading', 'av_custom_color' );
			}

			if( $atts['seperator_color'] != '' && 'custom-color-heading' == $atts['color'] )
			{
				$element_styling->add_styles( 'heading-border', array( 'border-color' => $atts['seperator_color'] ) );
				$element_styling->add_styles( 'heading-before', array( 'border-color' => $atts['seperator_color'] ) );
				$element_styling->add_styles( 'heading-after', array( 'border-color' => $atts['seperator_color'] ) );
			}

			// if a custom font size is set apply it to the container and also apply the inherit class so the actual heading uses the size
			if( ! empty( $atts['style'] ) && ! empty( $atts['size-title'] ) )
			{
				if( 'hidden' != $atts['size-title'] )
				{
					if( is_numeric( $atts['size-title'] ) )
					{
						$atts['size-title'] .= 'px';
					}

					$element_styling->add_styles( 'container', array( 'font-size' => $atts['size-title'] ) );
					$element_styling->add_classes( 'container', 'av-inherit-size' );
				}

				/**
				 * responsive behaviour for "default" is font-size: 0.8em; - media query prior post css implementation
				 * https://kriesi.at/support/topic/bug-new-typography-tools-clash-with-settings-on-the-page/
				 *
				 * @since 5.0.1
				 */
				if( '' == $atts['av-small-font-size-title'] )
				{
					$element_styling->add_media_queries( 'heading-text', array( 'screen' => array( '480;767' => array( 'font-size' => '0.8em' ) ) ) );
				}

				if( '' == $atts['av-mini-font-size-title'] )
				{
					$element_styling->add_media_queries( 'heading-text', array( 'screen' => array( '0;479' => array( 'font-size' => '0.8em' ) ) ) );
				}
			}

			if( ! empty( $atts['link'] ) )
			{
				$element_styling->add_classes( 'container', 'av-linked-heading' );
			}

			//check subheading
			$element_styling->add_classes( 'subheading', 'av-subheading' );
			if( ! empty( $atts['subheading_active'] ) )
			{
				$element_styling->add_classes( 'subheading', 'av-' . $atts['subheading_active'] );

				if( ! empty( $atts['subheading_color'] ) && 'custom-color-heading' == $atts['color'] )
				{
					$element_styling->add_styles( 'subheading', array( 'color' => $atts['subheading_color'] ) );
				}
			}

			// special styles for 'elegant' style
			if( $atts['style'] == 'blockquote elegant-quote elegant-centered' )
			{
				if( $atts['show_icon'] == 'custom_icon' && $atts['icon'] !== '' )
				{
					$element_styling->add_classes( 'container', 'av-icon' );

					if( $atts['icon_color'] !== '' && 'custom-color-heading' == $atts['color'] )
					{
						$element_styling->add_styles( 'heading-icon', array( 'color' => $atts['icon_color'] ) );
					}

					$element_styling->add_responsive_styles( 'heading-icon', 'icon_padding', $atts, $this );
				}
			}

			$selectors = array(
						'container'			=> "#top .av-special-heading.{$element_id}",
						'heading-icon'		=> "body .av-special-heading.{$element_id} .av-special-heading-tag .heading-char",
						'heading-text'		=> "#top #wrap_all .av-special-heading.{$element_id} .av-special-heading-tag",
						'heading-border'	=> ".av-special-heading.{$element_id} .special-heading-inner-border",
						'subheading'		=> ".av-special-heading.{$element_id} .av-subheading",
						'heading-before'	=> "body .av-special-heading.{$element_id} .av-special-heading-tag .heading-wrap:before",
						'heading-after'		=> "body .av-special-heading.{$element_id} .av-special-heading-tag .heading-wrap:after"
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

			$atts['link'] = trim( $atts['link'] );
			if( ( 'manually,http://' == $atts['link'] ) || ( 'manually,https://' == $atts['link'] ) )
			{
				$atts['link'] = '';
				$atts['link_target'] = '';
			}

			if( Avia_Dynamic_Content()->contains_link( $atts, 'heading' ) )
			{
				$atts['link'] = '';
				$atts['link_target'] = '';
				$element_styling->add_classes( 'container', 'av-linked-heading' );
			}

			extract( $atts );

			if( trim( $heading ) == '' )
			{
				return '';
			}

			$before = '';
			$after = '';
			$link_before = '';
			$link_after = '';

			// add seo markup
			$markup = avia_markup_helper( array( 'context' => 'entry_title', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );

			// filter heading for & symbol and convert them
			$heading = apply_filters( 'avia_ampersand', wptexturize( $heading ) );

			//check if we need to apply a link
			if( ! empty( $link ) )
			{
				$href = AviaHelper::get_url( $link );
				$title_attr_markup = AviaHelper::get_link_title_attr_markup( $title_attr );

				if( '' != $href )
				{
					$link_before .= '<a class="av-heading-link" href="' . esc_url( $href ) . '"' . AviaHelper::get_link_target( $link_target ) . ' ' . $title_attr_markup . '>';
					$link_after .= '</a>';
				}
			}

			// special markup for 'elegant' style
			if( $style == 'blockquote elegant-quote elegant-centered' )
			{
				$output_before = '';

				if( $show_icon == 'custom_icon' && $icon !== '' )
				{
					$display_char = av_icon( $icon, $font );
					$output_before = "<span class='heading-char avia-font-{$font}' {$display_char}></span>";
				}

				$output_before .= '<span class="heading-wrap">';
				$output_after = '</span>';

				$heading = $output_before . $heading . $output_after;
			}

			//check if we got a subheading
			if( ! empty( $style ) && ! empty( $subheading_active ) && ! empty( $content ) )
			{
				$subheading_class = $element_styling->get_class_string( 'subheading' );

				$content = "<div class='{$subheading_class}'>" . ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) ) . '</div>';

				if( $subheading_active == 'subheading_above' )
				{
					$before = $content;
				}
				else
				{
					$after = $content;
				}
			}

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );
			$heading_class = $element_styling->get_class_string( 'heading-text' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}'>";
			$output .= 		$before;
			$output .= 		"<{$tag} class='av-special-heading-tag {$heading_class}' {$markup} >{$link_before}{$heading}{$link_after}</{$tag}>";
			$output .= 		$after;
			$output .= 		'<div class="special-heading-border">';
			$output .=			'<div class="special-heading-inner-border"></div>';
			$output .=		'</div>';
			$output .= '</div>';

			return $output;
		}
	}
}
