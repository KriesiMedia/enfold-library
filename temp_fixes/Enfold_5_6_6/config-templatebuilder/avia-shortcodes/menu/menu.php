<?php
/**
 * Fullwidth Sub Menu
 *
 * Shortcode that allows to display a fullwidth Sub Menu
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_submenu', false ) )
{
	class avia_sc_submenu extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @var int
		 */
		static protected $count = 0;

		/**
		 *
		 * @var int
		 */
		static protected $custom_items = 0;

		/**
		 *
		 * @since 4.8.8
		 * @var boolean
		 */
		protected $in_sc_exec;

		/**
		 *
		 * @since 4.8.7
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			$this->in_sc_exec = false;

			parent::__construct( $builder );
		}

		/**
		 * @since 4.8.7
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['is_fullwidth']	= 'yes';
			$this->config['self_closing']	= 'no';

			$this->config['name']			= __( 'Fullwidth Sub Menu', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-submenu.png';
			$this->config['order']			= 30;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_submenu';
			$this->config['shortcode_nested'] = array( 'av_submenu_item' );
			$this->config['tooltip'] 	    = __( 'Display a sub menu', 'avia_framework' );
			$this->config['tinyMCE'] 		= array( 'disable' => 'true' );
			$this->config['drag-level'] 	= 1;
			$this->config['preview'] 		= false;
			$this->config['disabling_allowed'] = true;

			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-menu', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/menu/menu{$min_css}.css", array( 'avia-layout' ), $ver );

				//load js
			wp_enqueue_script( 'avia-module-menu', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/menu/menu{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
							'template_id'	=> $this->popup_key( 'content_menus' ),
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
							'template_id'	=> $this->popup_key( 'styling_colors' ),
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
									'template_id'	=> $this->popup_key( 'advanced_responsive' ),
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
			global $avia_config;

			$this->register_modal_group_templates();

			/**
			 * Content Tab
			 * ===========
			 */

			$menus = array();
			if( ! empty( $_POST ) && ! empty( $_POST['action'] ) && $_POST['action'] == 'avia_ajax_av_submenu' )
			{
				$menus = AviaHelper::list_menus();
			}

			$c = array(
						array(
							'name' 	=> __( 'Which kind of menu do you want to display','avia_framework' ),
							'desc' 	=> __( 'Either use an existing menu, built in Appearance -> Menus or create a simple custom menu here', 'avia_framework' ),
							'id' 	=> 'which_menu',
							'type' 	=> 'select',
							'std' 	=> 'center',
							'subtype'	=> array(
												__( 'Use existing menu', 'avia_framework' )			=> '',
												__( 'Build simple custom menu', 'avia_framework' )	=> 'custom',
											)
						),


						array(
							'name' 	=> __( 'Select menu to display', 'avia_framework' ),
							'desc' 	=> __( 'You can create new menus in ', 'avia_framework' ) . "<a target='_blank' href='" . admin_url( 'nav-menus.php?action=edit&menu=0' ) . "'>" . __( 'Appearance -> Menus', 'avia_framework' ) . '</a><br/>' . __( 'Please note that Mega Menus are not supported for this element ', 'avia_framework' ),
							'id' 	=> 'menu',
							'type' 	=> 'select',
							'std' 	=> '',
							'required'	=> array( 'which_menu', 'not', 'custom' ),
							'subtype'	=>  $menus
						),

						array(
							'name'			=> __( 'Add/Edit submenu item text', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the submenu item text', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'required'		=> array( 'which_menu', 'equals', 'custom' ),
							'modal_title'	=> __( 'Edit Text Element', 'avia_framework' ),
							'std'			=> array(
													array( 'title' => __( 'Menu Item 1', 'avia_framework' ) ),
													array( 'title' => __( 'Menu Item 2', 'avia_framework' ) ),
												),
							'subelements'	=> $this->create_modal()
						),

						array(
							'name' 	=> __( 'Menu Position', 'avia_framework' ),
							'desc' 	=> __( 'Aligns the menu either to the left, the right or centers it', 'avia_framework' ),
							'id' 	=> 'position',
							'type' 	=> 'select',
							'std' 	=> 'center',
							'subtype'	=> array(
												__( 'Left', 'avia_framework' )		=> 'left',
												__( 'Center', 'avia_framework' )	=> 'center',
												__( 'Right', 'avia_framework' )		=> 'right',
											)
						),

						array(
							'name' 	=> __( 'Sticky Submenu', 'avia_framework' ),
							'desc' 	=> __( 'If checked the menu will stick at the top of the page once it touches it. This option is ignored when burger menu icon is shown.', 'avia_framework' ),
							'id' 	=> 'sticky',
							'std' 	=> 'true',
							'type' 	=> 'checkbox'
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_menus' ), $c );


			/**
			 * Styling Tab
			 * ===========
			 */

			$desc  = __('The menu will use the color scheme you select. Color schemes are defined on your styling page', 'avia_framework' );
			$desc .= '<br/><a target="_blank" href="' . admin_url( 'admin.php?page=avia#goto_styling' ) . '">';
			$desc .= __( '(Show Styling Page)', 'avia_framework' ) . '</a>';


			$c = array(
						array(
							'name' 	=> __( 'Menu Colors', 'avia_framework' ),
							'id' 	=> 'color',
							'desc'  => $desc,
							'type' 	=> 'select',
							'std' 	=> 'main_color',
							'subtype' =>  array_flip( $avia_config['color_sets'] )
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors' ), $c );

			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						array(
							'name' 	=> __( 'Mobile Menu Display','avia_framework' ),
							'desc' 	=> __( 'How do you want to display the menu on mobile devices','avia_framework' ),
							'id' 	=> 'mobile',
							'type' 	=> 'select',
							'std' 	=> 'disabled',
							'subtype'	=> array(
												__( 'Display full menu (works best if you only got a few menu items)', 'avia_framework' )				=> 'disabled',
												__( 'Display a button to open menu (works best for menus with a lot of menu items)', 'avia_framework' )	=> 'active',
											)
						),

						array(
							'name'		=> __( 'Screenwidth for burger menu button', 'avia_framework' ),
							'desc'		=> __( 'Select the maximum screenwidth to use a burger menu button instead of full menu. Above that the full menu is displayed', 'avia_framework' ),
							'id'		=> 'mobile_switch',
							'type'		=> 'select',
							'std'		=> 'av-switch-768',
							'required' 	=> array( 'mobile', 'equals', 'active' ),
							'subtype'	=> array(
												__( 'Switch at 990px (tablet landscape)','avia_framework' )		=> 'av-switch-990',
												__( 'Switch at 768px (tablet portrait)','avia_framework' )		=> 'av-switch-768',
												__( 'Switch at 480px (smartphone portrait)','avia_framework' )	=> 'av-switch-480',
											)
						),

						array(
							'name' 	=> __( 'Hide Mobile Menu Submenu Items', 'avia_framework'),
							'desc' 	=> __( 'By default all menu items of the mobile menu are visible. If you activate this option they will be hidden and a user needs to click on the parent menu item to display the submenus', 'avia_framework'),
							'id' 	=> 'mobile_submenu',
							'required' 	=> array( 'mobile', 'equals', 'active' ),
							'type' 	=> 'checkbox',
							'std' 	=> ''
						),


				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Responsive', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_responsive' ), $template );

		}

		/**
		 * Creates the modal popup for a single entry
		 *
		 * @since 4.6.4
		 * @return array
		 */
		protected function create_modal()
		{
			$elements = array(

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
							'template_id'	=> $this->popup_key( 'modal_content_menu' )
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
							'template_id'	=> $this->popup_key( 'modal_styling_style' )
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
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'modal_advanced_link' )
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

			return $elements;
		}

		/**
		 * Register all templates for the modal group popup
		 *
		 * @since 4.6.4
		 */
		protected function register_modal_group_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */
			$c = array(
						array(
							'name' 	=> __( 'Menu Text', 'avia_framework' ),
							'desc' 	=> __( 'Enter the menu text here', 'avia_framework' ) ,
							'id' 	=> 'title',
							'std' 	=> '',
							'type' 	=> 'input'
						),


				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_menu' ), $c );


			/**
			 * Styling Tab
			 * ===========
			 */
			$c = array(
						array(
							'name' 	=> __( 'Style', 'avia_framework' ),
							'desc' 	=> __( 'Select the styling of your menu item', 'avia_framework' ),
							'id' 	=> 'button_style',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array(
												__( 'Default Style', 'avia_framework' )				=> '',
												__( 'Button Style (Colored)', 'avia_framework' )	=> 'av-menu-button av-menu-button-colored',
												__( 'Button Style (Bordered)', 'avia_framework' )	=> 'av-menu-button av-menu-button-bordered',
											),
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_styling_style' ), $c );

			/**
			 * Advanced Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Menu Link', 'avia_framework' ),
							'desc'			=> __( 'Apply a link to the menu text?', 'avia_framework' ),
							'subtypes'		=> array( 'manually', 'single', 'taxonomy' ),
							'no_toggle'		=> true,
							'title_attr'	=> true
						),
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $c );

		}


		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 *
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_sub_element( $params )
		{
			$template = $this->update_template( 'title', '{{title}}' );

			$params['innerHtml']  = '';
			$params['innerHtml'] .= "<div class='avia_title_container'>";
			$params['innerHtml'] .= "<span {$template} >{$params['args']['title']}</span></div>";

			return $params;
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
//			$term_args = array(
//							'taxonomy'		=> 'nav_menu',
//							'hide_empty'	=> false
//						);
//
//			$menus = AviaHelper::get_terms( $term_args );


			$params = parent::editor_element( $params );

			return $params;
		}


		/**
		 * Returns false by default.
		 * Override in a child class if you need to change this behaviour.
		 *
		 * @since 4.2.1
		 * @param string $shortcode
		 * @return boolean
		 */
		public function is_nested_self_closing( $shortcode )
		{
			if( in_array( $shortcode, $this->config['shortcode_nested'] ) )
			{
				return true;
			}

			return false;
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
							'style'			=> '',
							'menu'			=> '',
							'position'	 	=> 'center',
							'sticky'		=> '',
							'color'			=> 'main_color',
							'mobile'		=> 'disabled',
							'mobile_switch'	=> 'av-switch-768',
							'mobile_submenu'=> '',
							'which_menu'	=> ''

					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$this->in_sc_exec = true;


			if( 'disabled' == $atts['mobile'] )
			{
				$atts['mobile_switch'] = '';
			}
			else if( empty( $atts['mobile_switch'] ) )
			{
				$atts['mobile_switch'] = 'av-switch-768';
			}


			$classes = array(
						'av-submenu-container',
						$element_id,
						$atts['color'],
						$atts['mobile_switch']
					);

			$element_styling->add_classes( 'section', $classes );
			$element_styling->add_classes_from_array( 'section', $meta, 'el_class' );

			if( ! empty( $atts['sticky'] ) && $atts['sticky'] != 'disabled' )
			{
				$element_styling->add_classes( 'section', 'av-sticky-submenu' );
			}

			if( isset( $meta['index'] ) && $meta['index'] > 0 )
			{
				$element_styling->add_classes( 'section', 'submenu-not-first' );
			}


			$classes = array(
						'container',
						"av-menu-mobile-{$atts['mobile']}",
						"av-submenu-pos-{$atts['position']}"
					);

			$element_styling->add_classes( 'container-menu', $classes );

			if( ! empty( $atts['mobile'] ) && 'active' == $atts['mobile'] && ! empty( $atts['mobile_submenu'] ) && $atts['mobile_submenu'] != 'disabled' )
			{
				$element_styling->add_classes( 'container-menu', 'av-submenu-hidden' );
			}

			$classes = array(
						'av-subnav-menu',
//						"av-submenu-pos-{$atts['position']}"
					);

			$element_styling->add_classes( 'container-submenu', $classes );


			$selectors = array(
						'section'			=> ".av-submenu-container.{$element_id}",
						'container-menu'	=> ".av-submenu-container.{$element_id} .container",
						'container-submenu'	=> ".av-submenu-container.{$element_id} .av-subnav-menu",
					);

			$element_styling->add_selectors( $selectors );


			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Create custom stylings for items
		 * (also called when creating header implicit)
		 *
		 * @since 4.8.7
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles_item( array $args )
		{
			$result = parent::get_element_styles_item( $args );

			extract( $result );

			$default = array(
						'title' 		=> '',
						'link' 			=> '',
						'linktarget' 	=> '',
						'button_style' 	=> '',
					);

			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );

			$classes = array(
						'menu-item',
						$element_id,
						'menu-item-top-level',
						$atts['button_style']
					);

			$element_styling->add_classes( 'container', $classes );



			$selectors = array(
						'container'			=> ".menu-item.{$element_id}"
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
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );

			extract( $atts );


			avia_sc_submenu::$count ++;
			avia_sc_submenu::$custom_items = 0;

			$params = array();

			$element = '';
			$sticky_div = '';
			$mobile_button = '';
			$submenu_class = $element_styling->get_class_string( 'container-submenu' );

			if( $which_menu == 'custom' )
			{
				$custom_menu = ShortcodeHelper::avia_remove_autop( $content, true );

				if( ! empty( $custom_menu ) )
				{
					$element .= "<ul id='av-custom-submenu-" . avia_sc_submenu::$count . "' class='{$submenu_class}' role='menu'>";
					$element .=		$custom_menu;
					$element .= '</ul>';
				}
			}
			else
			{
				$element .= wp_nav_menu(
					array(
						'menu' 			=> wp_get_nav_menu_object( $menu ),
						'menu_class' 	=> $submenu_class,
						'fallback_cb' 	=> '',
						'container'		=> false,
						'echo' 			=> false,
						'items_wrap'	=> '<ul id="%1$s" class="%2$s" role="menu">%3$s</ul>',
						'walker' 		=> new avia_responsive_mega_menu( array( 'megamenu' => 'disabled' ) )
					)
				);


			}

			if( ! empty( $mobile ) && 'active' == $mobile )
			{
				$mobile_button  = '<a href="#" class="mobile_menu_toggle" ' . av_icon_string( 'mobile_menu' ) . '>';
				$mobile_button .=		'<span class="av-current-placeholder">' . __( 'Menu', 'avia_framework' ) . '</span>';
				$mobile_button .= '</a>';
			}


			$params['open_structure'] = false;
			$params['id'] = ! empty( $meta['custom_id_val'] ) ? $meta['custom_id_val'] : 'sub_menu' . avia_sc_submenu::$count;
			$params['custom_markup'] = $meta['custom_markup'];
			$params['style'] = "style='z-index:" . ( avia_sc_submenu::$count + 300 ) . "'";

			if( $sticky && $sticky != 'disabled' )
			{
				$params['before_new'] = "<div class='clear'></div>";
				$sticky_div = "<div class='sticky_placeholder'></div>";
			}

			//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
			if( isset( $meta['index'] ) && $meta['index'] == 0 )
			{
				$params['close'] = false;
			}

			if( ! empty( $meta['siblings']['prev']['tag'] ) && in_array( $meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section  ) )
			{
				$params['close'] = false;
			}

			$params['class'] = $element_styling->get_class_string( 'section' );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$menu_class = $element_styling->get_class_string( 'container-menu' );

			$output  = '';
			$output .= $style_tag;
			$output .= avia_new_section( $params );
			$output .=		"<div class='{$menu_class}'>{$mobile_button}{$element}</div>";
			$output .= avia_section_after_element_content( $meta , 'after_submenu_' . avia_sc_submenu::$count, false, $sticky_div );

			$this->in_sc_exec = false;

			return $output;
		}

		/**
		 * Shortcode handler
		 *
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @param array $meta
		 * @return string
		 */
		public function av_submenu_item( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			/**
			 * Fixes a problem when 3-rd party plugins call nested shortcodes without executing main shortcode  (like YOAST in wpseo-filter-shortcodes)
			 */
			if( ! $this->in_sc_exec )
			{
				return '';
			}

			$result = $this->get_element_styles_item( compact( array( 'atts', 'content', 'shortcodename' ) ) );

			extract( $result );

			extract( $atts );

			if( empty( $title) )
			{
				return '';
			}

			avia_sc_submenu::$custom_items++;

			$link = AviaHelper::get_url( $link );
			$blank = AviaHelper::get_link_target( $linktarget );
			$title_attr_markup = AviaHelper::get_link_title_attr_markup( $title_attr );

			$element_styling->add_classes( 'container', 'menu-item-top-level-' . avia_sc_submenu::$custom_items );


			$style_tag = $element_styling->get_style_tag( $element_id );
			//			$this->subitem_inline_styles .= $element_styling->get_style_tag( $element_id, 'rules_only' );
			$container_class = $element_styling->get_class_string( 'container' );


			$output  = '';
			$output .= $style_tag;
			$output .= "<li class='{$container_class}' role='menuitem'>";
			$output .=		"<a href='{$link}' {$blank} {$title_attr_markup}><span class='avia-bullet'></span>";
			$output .=			"<span class='avia-menu-text'>{$title}</span>";
			//$output .=		"<span class='avia-menu-fx'><span class='avia-arrow-wrap'><span class='avia-arrow'></span></span></span>";
			$output .=		'</a>';
			$output .= '</li>';

			return $output;
		}
	}

}

