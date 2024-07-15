<?php
/**
 * Font Icon
 *
 * Shortcode which displays an icon with optional hover effect
 */
// Don't load directly
if( ! defined( 'ABSPATH' ) ) { die('-1'); }

if( ! class_exists( 'av_font_icon', false ) )
{
    class av_font_icon extends aviaShortcodeTemplate
    {
		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Icon', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-icon.png';
			$this->config['order']			= 90;
			$this->config['shortcode']		= 'av_font_icon';
			$this->config['tooltip'] 	    = __( 'Display an icon with optional hover effect', 'avia_framework' );
			$this->config['target']			= 'avia-target-insert';
			//$this->config['inline']		= true;
			$this->config['tinyMCE']		= array( 'tiny_always' => true );
			$this->config['preview']		= 1;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			wp_enqueue_style( 'avia-module-icon' , AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/icon/icon{$min_css}.css" , array('avia-layout'), $ver );
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
							'template_id'	=> $this->popup_key( 'content_iconfont' )
						),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'content_tooltip' )
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
													$this->popup_key( 'styling_nettings' ),
													$this->popup_key( 'styling_color' ),
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
								'template_id'	=> $this->popup_key( 'advanced_link' ),
								'nodescription' => true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'effects_toggle',
								'lockable'		=> true,
								'required'		=> array( 'style', 'not', '' ),
								'include'		=> array( 'sonar_effect' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Animation' , 'avia_framework' ),
								'content'		=> $this->popup_templates['advanced_animation'],
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
							'name'  => __( 'Font Icon', 'avia_framework' ),
							'desc'  => __( 'Select an Icon below', 'avia_framework' ),
							'id'    => 'icon',
							'type'  => 'iconfont',
							'std'   => 'ue803',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' )
						),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_iconfont' ), $c );


			$c = array(
						array(
							'name' 	=> __( 'Optional Tooltip', 'avia_framework' ),
							'desc' 	=> __( 'Add a tooltip for this Icon. The tooltip will appear on mouse over', 'avia_framework' )
											. '<br/><small>'
											. __( 'Please note: Images within the tooltip are currently not supported', 'avia_framework' )
											. '</small>',
							'id' 	=> 'content',
							'type' 	=> 'textarea',
							'std' 	=> '',
							'lockable'	=> true,
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_tooltip' ), $c );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'	=> __( 'Icon Style', 'avia_framework' ),
							'desc'	=> __( 'Here you can set the style of the icon. Either display it inline as part of some text or let it stand alone with border and optional caption', 'avia_framework' ),
							'id'	=> 'style',
							'type'	=> 'select',
							'std'	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
										__( 'Default inline style', 'avia_framework' )	=> '',
										__( 'Standalone Icon with border and optional caption', 'avia_framework' )	=> 'border',
									)
						),

						array(
							'name'  => __( 'Icon Caption', 'avia_framework' ),
							'desc'  => __( 'A small caption below the icon', 'avia_framework' ),
							'id'    => 'caption',
							'type' 	=> 'input',
							'std' 	=> '',
							'lockable'	=> true,
							'required' 	=> array( 'style', 'not', '' )
						),

						array(
							'name'  => __( 'Icon Size', 'avia_framework' ),
							'desc'  => __( 'Enter the font size in px, em or &percnt;', 'avia_framework' ),
							'id'    => 'size',
							'type'  => 'input',
							'std'	=> '40px',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Icon Position', 'avia_framework' ),
							'desc' 	=> __( 'Choose the alignment of your icon here', 'avia_framework' ),
							'id' 	=> 'position',
							'type' 	=> 'select',
							'std' 	=> 'left',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Align Left', 'avia_framework' )	=> 'left',
											__( 'Align Center', 'avia_framework' )	=> 'center',
											__( 'Align Right', 'avia_framework' )	=> 'right',
										)
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'General Styling', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_nettings' ), $template );


			$c = array(
						array(
							'name'  => __( 'Icon Color', 'avia_framework' ),
							'desc'  => __( 'Here you can set the color of the icon. Enter no value if you want to use the standard font color.', 'avia_framework' ),
							'id'    => 'color',
							'rgba' 	=> true,
							'type'  => 'colorpicker',
							'std'		=> '',
							'lockable'	=> true
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_color' ), $template );

			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'name' 	=> __( 'Animation', 'avia_framework' ),
							'desc' 	=> __( 'Should the icons appear in an animated way?', 'avia_framework' ),
							'id' 	=> 'animation',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Animation activated', 'avia_framework' )	=> '',
											__( 'Animation deactivated', 'avia_framework' )	=> 'deactivated',
										)
						),
				);

			$this->popup_templates['advanced_animation'] = $c;

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Icon Link', 'avia_framework' ),
							'desc'			=> __( 'Where should your icon link to?', 'avia_framework' ),
							'lockable'		=> true,
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
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
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode'], $default, $locked );

			extract( av_backend_icon(  array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font'

			$inner  = "<div class='avia_icon_element avia_textblock avia_textblock_style' data-update_element_template='yes'>";
			$inner .=		'<div ' . $this->class_by_arguments_lockable( 'position', $attr, $locked ) . '>';
			$inner .=			'<div ' . $this->class_by_arguments_lockable( 'style', $attr, $locked ) . '>';
			$inner .=				'<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
			$inner .=					'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_icon_char'>{$display_char}</span>";
			$inner .=				'</span>';
			$inner .=				"<div class='avia_icon_content_wrap'>";
			$inner .=					"<h4 class='av_icon_caption' " . $this->update_option_lockable( 'caption', $locked ) . '>' . html_entity_decode( $attr['caption'] ) . '</h4>';
			$inner .=				'</div>';
			$inner .=			'</div>';
			$inner .=		'</div>';
			$inner .= '</div>';

			$params['innerHtml'] = $inner;
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

			$default = array(
						'icon'			=> '',
						'font'			=> '',
						'color'			=> '',
						'size'			=> '',
						'style'			=> '',
						'caption'		=> '',
						'use_link'		=> 'no',
						'position'		=> 'left',
						'animation'		=> '',
						'link'			=> '',
						'linktarget'	=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			Avia_Dynamic_Content()->read( $atts, $this, $shortcodename, $content );
			$atts['link'] = Avia_Dynamic_Content()->check_link( $atts['link_dynamic'], $atts['link'], [ 'no', 'manually', 'single', 'taxonomy' ] );

			/**
			 * @since 5.3
			 * @param string $class_animation
			 * @param array $atts
			 * @param aviaShortcodeTemplate $this
			 * @param string $shortcodename
			 * @return string
			 */
			$class_animation = apply_filters( 'avf_alb_element_animation', 'avia_animate_when_visible', $atts, $this, $shortcodename );

			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'av_font_icon',
						$element_id,
						$class_animation,
						'av-icon-style-' . $atts['style'],
						'avia-icon-pos-' . $atts['position']
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'custom_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			if( ! empty( $atts['color'] ) )
			{
				$colors = array(
								'color'			=> $atts['color'],
								'border-color'	=> $atts['color']
							);

				$element_styling->add_styles( 'container', $colors );
			}
			else
			{
				$element_styling->add_classes( 'container', 'av-no-color' );
			}

			if( ! empty( $atts['size'] ) )
			{
				if( is_numeric( $atts['size'] ) )
				{
					$atts['size'] .= 'px';
				}

				$sizes = array(
								'font-size'		=> $atts['size'],
								'line-height'	=> $atts['size']
							);

				$element_styling->add_styles( 'character', $sizes );
			}

			if( ! empty( $atts['style'] ) )
			{
				$element_styling->add_styles( 'character', array( 'width' => $atts['size'] ) );
			}

			 // animation
			if( empty( $atts['animation'] ) )
			{
				$element_styling->add_classes( 'container', 'avia-icon-animate' );
			}

			if( ! empty( $atts['sonar_effect_effect'] ) && ! empty( $atts['style'] ) )
			{
				$element_styling->add_classes( 'container', 'avia-sonar-shadow' );

				if( false !== strpos( $atts['sonar_effect_effect'], 'shadow' ) )
				{
					if( 'shadow_permanent' == $atts['sonar_effect_effect'] )
					{
						$element_styling->add_callback_styles( 'character-after', array( 'sonar_effect' ) );
					}
					else
					{
						$element_styling->add_callback_styles( 'character-after-hover', array( 'sonar_effect' ) );
					}
				}
				else
				{
					if( false !== strpos( $atts['sonar_effect_effect'], 'permanent' ) )
					{
						$element_styling->add_callback_styles( 'character', array( 'sonar_effect' ) );
					}
					else
					{
						$element_styling->add_callback_styles( 'character-hover', array( 'sonar_effect' ) );
					}
				}
			}

			$selectors = array(
							'container'				=> ".av_font_icon.{$element_id}",
							'character'				=> ".av_font_icon.{$element_id} .av-icon-char",
							'character-hover'		=> ".av_font_icon.{$element_id} .av-icon-char:hover",
							'character-after'		=> ".av_font_icon.{$element_id}.av-icon-style-border .av-icon-char:after",
							'character-after-hover'	=> ".av_font_icon.{$element_id}.av-icon-style-border .av-icon-char:hover::after"
				);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
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
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			//this is a fix that solves the false paragraph removal by wordpress if the dropcaps shortcode is used at the beginning of the content of single posts/pages
			global $post, $avia_add_p;

			$add_p = '';
			if( isset( $post->post_content ) && strpos( $post->post_content, '[av_font_icon' ) === 0 && $avia_add_p == false && is_singular() )
			{
				$add_p = '<p>';
				$avia_add_p = true;
			}

			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			$caption_html = '';
			if( ! empty( $style ) && ! empty( $caption ) )
			{
				$caption_html .= "<span class='av_icon_caption av-special-font'>{$caption}</span>";
			}

			$link = AviaHelper::get_url( $link );
			$blank = AviaHelper::get_link_target( $linktarget );

			$title_attr_markup = '';
			if( trim( $content ) == '' )
			{
				$title_attr_markup = AviaHelper::get_link_title_attr_markup( $title_attr );
			}

			$char = avia_font_manager::frontend_icon( $icon, $font, 'string', empty( $link ) );

			$tags = ! empty( $link ) ? array( "a href='{$link}' {$blank} {$title_attr_markup}", 'a' ) : array( 'span', 'span' );
			$tooltip = empty( $content ) ? '' : 'data-avia-icon-tooltip="' . htmlspecialchars( do_shortcode( $content ) ) . '"';
			$display_char = "<{$tags[0]} class='av-icon-char' {$char} {$tooltip}></{$tags[1]}>";

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<span {$meta['custom_el_id']} class='{$container_class}'>";
			$output .=		$display_char . $caption_html;
			$output .= '</span>';

            return $output;
        }
    }
}
