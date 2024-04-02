<?php
/**
 * Icon List Shortcode
 *
 * Creates a list with nice icons beside
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( 'avia_sc_iconlist', false ) )
{
	class avia_sc_iconlist extends aviaShortcodeTemplate
	{
		/**
		 *
		 * @since 4.8.8
		 * @var boolean
		 */
		protected $in_sc_exec;

		/**
		 * @since 4.5.5
		 * @var string
		 */
		protected $iconlist_styling_class;

		/**
		 * @since 4.5.5
		 * @var string
		 */
		protected $title_class;

		/**
		 * @since 4.5.5
		 * @var string
		 */
		protected $content_class;

		/**
		 * @since 5.7
		 * @var array
		 */
		protected $element_atts;


		/**
		 *
		 * @since 4.5.5
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder )
		{
			$this->in_sc_exec = false;
			$this->iconlist_styling_class = '';
			$this->title_class = '';
			$this->content_class = '';

			parent::__construct( $builder );
		}

		/**
		 * @since 4.5.5
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->element_atts );
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Icon List', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-iconlist.png';
			$this->config['order']			= 90;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_iconlist';
			$this->config['shortcode_nested'] = array( 'av_iconlist_item' );
			$this->config['tooltip']		= __( 'Creates a list with nice icons beside', 'avia_framework' );
			$this->config['preview']		= true;
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
			$this->config['name_item']		= __( 'Icon List Item', 'avia_framework' );
			$this->config['tooltip_item']	= __( 'An Icon List Element Item', 'avia_framework' );
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			wp_enqueue_style( 'avia-module-icon', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/icon/icon{$min_css}.css", array( 'avia-layout' ), $ver );
			wp_enqueue_style( 'avia-module-iconlist', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/iconlist/iconlist{$min_css}.css", array( 'avia-layout' ), $ver );

			wp_enqueue_script( 'avia-module-iconlist', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/iconlist/iconlist{$min_js}.js", array( 'avia-shortcodes' ), $ver, true );
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
													$this->popup_key( 'styling_general' ),
													$this->popup_key( 'styling_font_sizes' ),
													$this->popup_key( 'styling_font_colors' ),
													$this->popup_key( 'styling_icon_font_colors' )
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
								'template_id'	=> $this->popup_key( 'advanced_animation' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'effects_toggle',
								'lockable'		=> true,
								'include'		=> array( 'sonar_effect' )
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

			$this->register_modal_group_templates();

			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'			=> __( 'Add/Edit List items', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the items of your item list.', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit List Item', 'avia_framework' ),
							'editable_item'	=> true,
							'lockable'		=> true,
							'std'			=> array(
													array(
														'title'		=> __( 'List Title 1', 'avia_framework' ),
														'icon'		=> 'ue856',
														'content'	=> __( 'Enter content here', 'avia_framework' )
													),
													array(
														'title'		=> __( 'List Title 2', 'avia_framework' ),
														'icon'		=> 'ue8c0',
														'content'	=> __( 'Enter content here', 'avia_framework' )
													),
													array(
														'title'		=> __( 'List Title 3', 'avia_framework' ),
														'icon'		=> 'ue809',
														'content'	=> __( 'Enter content here', 'avia_framework' )
													),
												),
							'subelements'	=> $this->create_modal()
						)
				);


			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_iconfont' ), $c );


			/**
			 * Styling Tab
			 * ============
			 */


			$c = array(
						array(
							'name' 	=> __( 'Icon Position', 'avia_framework' ),
							'desc' 	=> __( 'Set the position of the icons', 'avia_framework' ),
							'id' 	=> 'position',
							'type' 	=> 'select',
							'std' 	=> 'left',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Left', 'avia_framework' )	=> 'left',
											__( 'Right', 'avia_framework' )	=> 'right',
										)
							),

						array(
							'name' 	=> __( 'List Styling', 'avia_framework' ),
							'desc' 	=> __( 'Change the styling of your iconlist', 'avia_framework' ),
							'id' 	=> 'iconlist_styling',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Default (Big List)', 'avia_framework' )	=> '',
											__( 'Minimal small list', 'avia_framework' )	=> 'av-iconlist-small',
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

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_general' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Font Colors', 'avia_framework' ),
							'desc' 	=> __( 'Either use the themes default colors or apply some custom ones', 'avia_framework' ),
							'id' 	=> 'font_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
											__( 'Default', 'avia_framework' )	=> '',
											__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
										),
						),

						array(
							'name' 	=> __( 'Custom Title Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_title',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'container_class' => 'av_half av_half_first',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Custom Content Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_content',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'container_class' => 'av_half',
							'lockable'	=> true,
							'required'	=> array( 'font_color', 'equals','custom'	)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_font_colors' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Icon Colors', 'avia_framework' ),
							'desc' 	=> __( 'Select to use the themes default colors or apply custom ones', 'avia_framework' ),
							'id' 	=> 'color',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Default', 'avia_framework' )	=> '',
												__( 'Few settings only', 'avia_framework' )	=> 'custom',
												__( 'Extended, simple background', 'avia_framework' )	=> 'ext_simple',
												__( 'Extended, gradient background', 'avia_framework' )	=> 'ext_grad',
											),

						),

						array(
							'name' 	=> __( 'Icon Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom icon font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_font',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'container_class' => 'av_third av_third_first',
							'lockable'	=> true,
							'required' => array( 'color', 'not', '' )
						),

						array(
							'name' 	=> __( 'Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_bg',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required'	=> array( 'color', 'parent_in_array', 'custom,ext_simple' )
						),

						array(
							'name' 	=> __( 'Custom Border Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom border color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_border',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'container_class' => 'av_third',
							'lockable'	=> true,
							'required' => array( 'color', 'equals', 'custom' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'gradient_colors',
							'lockable'		=> true,
							'required'		=> array( 'color', 'equals', 'ext_grad' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border',
							'lockable'		=> true,
							'default_check'	=> true,
							'required'		=> array( 'color', 'parent_in_array', 'ext_simple,ext_grad' )
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'lockable'		=> true,
							'default_check'	=> true,
							'required'		=> array( 'color', 'parent_in_array', 'ext_simple,ext_grad' )
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Icon Colors', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_icon_font_colors' ), $template );

			$c = array(
						array(
							'name'			=> __( 'Title Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the titles.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_title_size',
												'desktop'	=> 'av-desktop-font-size-title',
												'medium'	=> 'av-medium-font-size-title',
												'small'		=> 'av-small-font-size-title',
												'mini'		=> 'av-mini-font-size-title'
											)
						),

						array(
							'name'			=> __( 'Content Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the content.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'custom_content_size',
												'desktop'	=> 'av-desktop-font-size',
												'medium'	=> 'av-medium-font-size',
												'small'		=> 'av-small-font-size',
												'mini'		=> 'av-mini-font-size'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Sizes', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_font_sizes' ), $template );

			/**
			 * Advanced Tab
			 * ============
			 */

			$c = array(
						 array(
							'name' 	=> __( 'Animation', 'avia_framework' ),
							'desc' 	=> __( 'Should the items appear in an animated way?', 'avia_framework' ),
							'id' 	=> 'animation',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'subtype' => array(
											__( 'Animation activated', 'avia_framework' )	=> '',
											__( 'Animation deactivated', 'avia_framework' )	=> 'deactivated',
										)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Animation', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'advanced_animation' ), $template );
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
							'template_id'	=> $this->popup_key( 'modal_content_content' )
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
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'modal_advanced_heading' ),
													$this->popup_key( 'modal_advanced_link' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array(
												'sc'			=> $this,
												'modal_group'	=> true
											)
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
								'name' 	=> __( 'List Item Title', 'avia_framework' ),
								'desc' 	=> __( 'Enter the list item title here (Better keep it short)', 'avia_framework' ) ,
								'id' 	=> 'title',
								'type' 	=> 'input',
								'std' 	=> 'List Title',
								'lockable'	=> true
						),

						array(
							'name' 	=> __( 'List Item Icon', 'avia_framework' ),
							'desc' 	=> __( 'Select an icon for your list item below', 'avia_framework' ),
							'id' 	=> 'icon',
							'type' 	=> 'iconfont',
							'std' 	=> '',
							'lockable'	=> true,
							'locked'	=> array( 'icon', 'font' )
						),

						array(
							'name' 	=> __( 'List Item Content', 'avia_framework' ),
							'desc' 	=> __( 'Enter some content here', 'avia_framework' ) ,
							'id' 	=> 'content',
							'type' 	=> 'tiny_mce',
							'std' 	=> __( 'List Content goes here', 'avia_framework' ),
							'lockable'	=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_content' ), $c );


			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Link Setting', 'avia_framework' ),
							'desc'			=> __( 'Do you want to apply a link to the element?', 'avia_framework' ),
							'lockable'		=> true,
							'subtypes'		=> array( 'no', 'manually', 'single', 'taxonomy' ),
							'no_toggle'		=> true,
							'title_attr'	=> true
						),

						array(
							'name' 	=> __( 'Link Location', 'avia_framework' ),
							'desc' 	=> __( 'Select where to apply the link', 'avia_framework' ),
							'id' 	=> 'linkelement',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'required' 	=> array( 'link', 'not', '' ),
							'subtype'	=> array(
											__( 'Apply link to title only', 'avia_framework' )		=> '',
											__( 'Apply link to icon and title', 'avia_framework' )	=> 'both',
											__( 'Apply link to icon only', 'avia_framework' )		=> 'only_icon'
										)
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Link Behaviour', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $template );

			$c = array(
						array(
							'type'				=> 'template',
							'template_id'		=> 'heading_tag',
							'theme_default'		=> 'h4',
							'context'			=> __CLASS__,
							'lockable'			=> true
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Heading Tag', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_heading' ), $template );
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
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked );

			$template = $this->update_template_lockable( 'title', __( 'Element', 'avia_framework' ) . ': {{title}}', $locked );

			extract( av_backend_icon( array( 'args' => $attr ) ) ); // creates $font and $display_char if the icon was passed as param 'icon' and the font as 'font'

			$params['innerHtml']  = '';
			$params['innerHtml'] .=		"<div class='avia_title_container' data-update_element_template='yes'>";
			$params['innerHtml'] .=			'<span ' . $this->class_by_arguments_lockable( 'font', $font, $locked ) . '>';
			$params['innerHtml'] .=				'<span ' . $this->update_option_lockable( array( 'icon', 'icon_fakeArg' ), $locked ) . " class='avia_tab_icon'>{$display_char}</span>";
			$params['innerHtml'] .=			'</span>';
			$params['innerHtml'] .=			"<span {$template} >" . __( 'Element', 'avia_framework' ) . ": {$attr['title']}</span>";
			$params['innerHtml'] .=		'</div>';

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
						'position'				=> 'left',
						'color'					=> '',
						'custom_bg'				=> '',
						'custom_border'			=> '',
						'custom_font'			=> '',
						'font_color'			=> '',
						'custom_title'			=> '',
						'custom_content'		=> '',
						'custom_title_size'		=> '',
						'custom_content_size'	=> '',
						'iconlist_styling'		=> '',
						'animation'				=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$this->in_sc_exec = true;

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			//	backwards comp. - prepare responsive font sizes for media query
			$atts['size'] = $atts['custom_content_size'];
			$atts['size-title'] = $atts['custom_title_size'];

			if( $atts['iconlist_styling'] == '' )
			{
				$atts['iconlist_styling'] = 'av-iconlist-big';
			}

			$this->element_atts = $atts;
			$this->iconlist_styling_class = $atts['iconlist_styling'];
			$this->title_class = '';
			$this->content_class = '';

			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'avia-icon-list-container',
						$element_id
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );
			$element_styling->add_responsive_font_sizes( 'item-content', 'size', $atts, $this );
			$element_styling->add_responsive_font_sizes( 'item-title', 'size-title', $atts, $this );


			$classes = array(
						'avia-icon-list',
						'avia_animate_when_almost_visible',
						'avia-icon-list-' . $atts['position'],
						$atts['iconlist_styling'],
						$element_id
					);

			$element_styling->add_classes( 'container-ul', $classes );

			// animation
			if( $atts['animation'] == '' )
			{
				$element_styling->add_classes( 'container-ul', 'avia-iconlist-animate' );
			}

			if( $atts['color'] == 'custom' )
			{
				if( ! empty( $atts['custom_font'] ) )
				{
					$element_styling->add_styles( 'item-icon', array( 'color' => $atts['custom_font'] ) );
				}

				if( ! empty( $atts['custom_bg'] ) )
				{
					$element_styling->add_styles( 'item-icon', array( 'background-color' => $atts['custom_bg'] ) );
				}

				if( ! empty( $atts['custom_border'] ) )
				{
					$element_styling->add_styles( 'item-icon', array( 'border' => '1px solid ' . $atts['custom_border'] ) );
				}
			}
			else if( in_array( $atts['color'], array( 'ext_simple', 'ext_grad' ) ) )
			{
				if( ! empty( $atts['custom_font'] ) )
				{
					$element_styling->add_styles( 'item-icon', array( 'color' => $atts['custom_font'] ) );
				}

				if( $atts['color'] == 'ext_simple' &&  ! empty( $atts['custom_bg'] ) )
				{
					$element_styling->add_styles( 'item-icon', array( 'background-color' => $atts['custom_bg'] ) );
				}
				else if( $atts['color'] == 'ext_grad' )
				{
					$element_styling->add_callback_styles( 'item-icon', array( 'gradient_color' ) );
				}

				$element_styling->add_callback_styles( 'item-icon', array( 'border', 'box_shadow' ) );
			}

			if( $atts['font_color'] == 'custom' )
			{
				if( ! empty( $atts['custom_title'] ) )
				{
					$element_styling->add_styles( 'item-title', array( 'color' => $atts['custom_title'] ) );
					$this->title_class = 'av_inherit_color';
				}

				if( ! empty( $atts['custom_content'] ) )
				{
					$element_styling->add_styles( 'item-content', array( 'color' => $atts['custom_content'] ) );
					$this->content_class = 'av_inherit_color';
				}
			}

			if( $atts['custom_title_size'] != '' )
			{
				if( $atts['iconlist_styling'] == 'av-iconlist-small' )
				{
					$element_styling->add_styles( 'item-icon', array( 'font-size' => $atts['custom_title_size'] . 'px' ) );
				}
			}

			if( ! empty( $atts['sonar_effect_effect'] ) )
			{
				$element_styling->add_classes( 'container-ul', 'avia-sonar-shadow' );

				if( false !== strpos( $atts['sonar_effect_effect'], 'shadow' ) )
				{
					if( 'shadow_permanent' == $atts['sonar_effect_effect'] )
					{
						$element_styling->add_callback_styles( 'item-icon-after', array( 'sonar_effect' ) );
					}
					else
					{
						$element_styling->add_callback_styles( 'item-icon-after-hover', array( 'sonar_effect' ) );
					}
				}
				else
				{
					if( false !== strpos( $atts['sonar_effect_effect'], 'permanent' ) )
					{
						$element_styling->add_callback_styles( 'item-icon', array( 'sonar_effect' ) );
					}
					else
					{
						$element_styling->add_callback_styles( 'item-icon-hover', array( 'sonar_effect' ) );
					}
				}
			}

			$selectors = array(
						'container'				=> ".avia-icon-list-container.{$element_id}",
						'container-ul'			=> ".avia-icon-list-container.{$element_id} .avia-icon-list",
						'item-icon'				=> "#top .avia-icon-list-container.{$element_id} .iconlist_icon",
						'item-icon-hover'		=> "#top .avia-icon-list-container.{$element_id} .iconlist_icon:hover",
						'item-icon-after'		=> "#top .avia-icon-list-container.{$element_id} .iconlist_icon:after",
						'item-icon-after-hover'	=> "#top .avia-icon-list-container.{$element_id} .iconlist_icon:hover:after",
						'item-title'			=> "#top #wrap_all .avia-icon-list-container.{$element_id} .av_iconlist_title",
						'item-content'			=> ".avia-icon-list-container.{$element_id} .iconlist_content"
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
		 * @since 4.8.4
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles_item( array $args )
		{
			$result = parent::get_element_styles_item( $args );

			extract( $result );

			$default = array(
						'title'			=> '',
						'link'			=> '',
						'icon'			=> '',
						'font'			=> '',
						'linkelement'	=> '',
						'linktarget'	=> '',
						'custom_markup' => '',
					);

			$default = $this->sync_sc_defaults_array( $default, 'modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $this->config['shortcode_nested'][0], $default, $locked, $content );
			$meta = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $atts );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode_nested'][0] );

			$classes = array(
						'iconlist_icon',
						$element_id,
						"avia-font-{$atts['font']}"
					);

			$element_styling->add_classes( 'container', $classes );



			$selectors = array(
						'container'			=> ".avia-icon-list-container .iconlist_icon.{$element_id}"
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

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );
			$container_class_ul = $element_styling->get_class_string( 'container-ul' );

			$output	 =	'';
			$output .= $style_tag;
			$output .=	"<div {$meta['custom_el_id']} class='{$container_class}'>";
			$output .=		"<ul class='{$container_class_ul}'>";
			$output .=			ShortcodeHelper::avia_remove_autop( $content, true );
			$output .=		'</ul>';
			$output .=	'</div>';

			$this->in_sc_exec = false;

			return $output;
		}

		/**
		 * Shortcode Handler
		 *
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_iconlist_item( $atts, $content = '', $shortcodename = '' )
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

			$display_char = av_icon( $atts['icon'], $atts['font'] );
			$display_char_wrapper = array();

			$blank = AviaHelper::get_link_target( $atts['linktarget'] );
			if( ! empty( $atts['link'] ) )
			{
				$atts['link'] = AviaHelper::get_url( $atts['link'] );

				if( ! empty( $atts['link'] ) )
				{
					$linktitle = '' == trim( $atts['title_attr'] ) ? $atts['title'] : $atts['title_attr'];

					switch( $atts['linkelement'] )
					{
						case 'both':
							if( $atts['title'] )
							{
								$atts['title'] = "<a href='{$atts['link']}' title='" . esc_attr( $linktitle ) . "'{$blank}>{$atts['title']}</a>";
							}

							$display_char_wrapper['start'] = "a href='{$atts['link']}' title='" . esc_attr( $linktitle ) . "' {$blank}";
							$display_char_wrapper['end'] = 'a';
							break;
						case 'only_icon':
							$display_char_wrapper['start'] = "a href='{$atts['link']}' title='" . esc_attr( $linktitle ) . "' {$blank}";
							$display_char_wrapper['end'] = 'a';
							break;
						default:
							if( $atts['title'] )
							{
								$atts['title'] = "<a href='{$atts['link']}' title='" . esc_attr( $linktitle ) . "'{$blank}>{$atts['title']}</a>";
							}

							$display_char_wrapper['start'] = 'div';
							$display_char_wrapper['end'] = 'div';
							break;
					}
				}
			}

			if( empty( $display_char_wrapper ) )
			{
				$display_char_wrapper['start'] = 'div';
				$display_char_wrapper['end'] = 'div';
			}

			$contentClass = '';
			if( trim( $content ) == '' )
			{
				$contentClass = 'av-iconlist-empty';
			}

			$default_heading = ( $this->iconlist_styling_class == 'av-iconlist-small' ) ? 'div' : 'h4';
			$default_heading = ! empty( $meta['heading_tag'] ) ? $meta['heading_tag'] : $default_heading;

			$args = array(
						'heading'		=> $default_heading,
						'extra_class'	=> $meta['heading_class']
					);

			$extra_args = array( $this, $atts, $content, $shortcodename, $this->element_atts );

			/**
			 * @since 4.5.7.2
			 * @param array $args
			 * @param string $classname
			 * @param array $extra_args
			 * @return array
			 */
			$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

			$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
			$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : $meta['heading_class'];

			$title_el = $heading;
			$iconlist_title = ( $this->iconlist_styling_class == 'av-iconlist-small' ) ? 'iconlist_title_small' : 'iconlist_title';

			$markup_entry = avia_markup_helper( array( 'context' => 'entry','echo' => false, 'custom_markup' => $atts['custom_markup'] ) );
			$markup_title = avia_markup_helper( array( 'context' => 'entry_title', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );
			$markup_content = avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false, 'custom_markup' => $atts['custom_markup'] ) );

//			$this->subitem_inline_styles .= $element_styling->get_style_tag( $element_id, 'rules_only' );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .=		'<li>';
			$output .=			"<{$display_char_wrapper['start']} class='{$container_class}'>";
			$output .=				"<span class='iconlist-char' {$display_char}></span>";
			$output .=			"</{$display_char_wrapper['end']}>";
			$output .=          '<article class="article-icon-entry ' . $contentClass . '" ' . $markup_entry . '>';
			$output .=              '<div class="iconlist_content_wrap">';

			$output .=                  '<header class="entry-content-header">';

			if( ! empty( $atts['title'] ) )
			{
				$output .=					"<{$title_el} class='av_iconlist_title {$iconlist_title} {$css} {$this->title_class}' {$markup_title}>{$atts['title']}</{$title_el}>";
			}
			$output .=                  '</header>';

			$output .=                  "<div class='iconlist_content {$this->content_class}' {$markup_content}>" . ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( $content ) ) . '</div>';
			$output .=              '</div>';
			$output .=              '<footer class="entry-footer"></footer>';
			$output .=          '</article>';
			$output .=			'<div class="iconlist-timeline"></div>';
			$output .=		'</li>';

			return $output;
		}

	}
}
