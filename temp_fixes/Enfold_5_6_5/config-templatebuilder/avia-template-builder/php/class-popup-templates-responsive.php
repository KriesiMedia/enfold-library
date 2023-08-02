<?php
namespace aviaBuilder\base;

use \AviaHtmlHelper;

/**
 * Class implements modal popup templates for elements that are responsive.
 * Added to keep code of elements slim and better readable
 *
 * @added_by Günter
 * @since 5.0
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( __NAMESPACE__ . '\aviaPopupTemplatesResppnsive', false ) )
{
	class aviaPopupTemplatesResponsive extends \aviaBuilder\base\aviaPopupTemplatesCallback
	{
		/**
		 * array of default option keys for respnsive options
		 *
		 * @var array
		 * @since 5.0
		 */
		protected $resp_sizes_pre;

		/**
		 * array of default descriptions for respnsive options
		 *
		 * @var array
		 * @since 5.0
		 */
		protected $resp_sizes_desc;

		/**
		 * array of default titles for respnsive options
		 *
		 * @var array
		 * @since 5.0
		 */
		protected $resp_title;

		/**
		 * array of default icons for respnsive options
		 *
		 * @var array
		 * @since 5.0
		 */
		protected $resp_icons;

		/**
		 *
		 * @var string
		 * @since 5.0
		 */
		protected $limited_css;

		/**
		 * @since 5.0
		 */
		protected function __construct()
		{
			parent::__construct();

			$this->resp_sizes_options = array(
								'default'	=> '',
								'desktop'	=> 'av-desktop-',
								'medium'	=> 'av-medium-',
								'small'		=> 'av-small-',
								'mini'		=> 'av-mini-'
							);

			$this->resp_sizes_desc = array(
							'default'	=> __( 'default setting - used for all screen sizes (no media query)', 'avia_framework' ),
							'desktop'	=> __( 'for large screens (wider than 990px - Desktop)', 'avia_framework' ),
							'medium'	=> __( 'for medium sized screens (between 768px and 989px - eg: Tablet Landscape)', 'avia_framework' ),
							'small'		=> __( 'for small screens (between 480px and 767px - eg: Tablet Portrait)', 'avia_framework' ),
							'mini'		=> __( 'for very small screens (smaller than 479px - eg: Smartphone Portrait)', 'avia_framework' ),
						);

			$this->resp_titles = array(
							'default'	=> __( 'Default', 'avia_framework' ),
							'desktop'	=> __( 'Desktop', 'avia_framework' ),
							'medium'	=> __( 'Tablet Landscape', 'avia_framework' ),
							'small'		=> __( 'Tablet Portrait', 'avia_framework' ),
							'mini'		=> __( 'Mobile', 'avia_framework' ),
						);

			$this->resp_icons = array(
							'default'	=> 'default',
							'desktop'	=> 'desktop',
							'medium'	=> 'tablet-landscape',
							'small'		=> 'tablet-portrait',
							'mini'		=> 'mobile'
						);

			$this->limited_css = __( 'Please keep in mind: Due to limitations in CSS it is not possible to combine all possible advanced settings like animations, transforms,...  They might not work as expected - this is not a bug. Please be selective and check the frontend.', 'avia_framework' );
		}

		/**
		 * @since 5.0
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->resp_sizes_options );
			unset( $this->resp_sizes_desc  );
			unset( $this->resp_titles  );
			unset( $this->resp_icons  );
		}

		/**
		 * Returns a font sizes icon switcher section.
		 *
		 * @since 4.6.4
		 * @since 5.1					added desktop screensize and moved to this class
		 * @param array $element
		 * @return array
		 */
		protected function font_sizes_icon_switcher( array $element )
		{
			//	fallback where 'desktop' is not set - add desktop at correct position
			$sort_arrays = false;
			$sort_keys = array_keys( $this->resp_icons );
			$sort_data = array( 'subtype', 'id_sizes', 'desc_sizes' );

			if( isset( $element['subtype'] ) && is_array( $element['subtype'] ) )
			{
				$subtype = $element['subtype'];
			}
			else
			{
				$subtype = array(
							'default'	=> AviaHtmlHelper::number_array( 8, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
							'desktop'	=> AviaHtmlHelper::number_array( 8, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
							'medium'	=> AviaHtmlHelper::number_array( 8, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
							'small'		=> AviaHtmlHelper::number_array( 8, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
							'mini'		=> AviaHtmlHelper::number_array( 8, 120, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
						);
			}

			//	fallback where 'desktop' is not set
			if( ! isset( $subtype['desktop'] ) )
			{
				$subtype['desktop'] = $subtype['default'];
				$sort_arrays = true;
			}

			if( isset( $element['id_sizes'] ) && is_array( $element['id_sizes'] ) )
			{
				$id_sizes = $element['id_sizes'];
			}
			else
			{
				$id_sizes = array(
							'default'	=> 'size',
							'desktop'	=> 'av-desktop-font-size',
							'medium'	=> 'av-medium-font-size',
							'small'		=> 'av-small-font-size',
							'mini'		=> 'av-mini-font-size'
						);
			}

			//	fallback where 'desktop' is not set
			if( ! isset( $id_sizes['desktop'] ) )
			{
				$id_sizes['desktop'] = str_replace( 'av-medium-', 'av-desktop-', $id_sizes['medium'] );
				$sort_arrays = true;
			}

			if( isset( $element['desc_sizes'] ) && is_array( $element['desc_sizes'] ) )
			{
				$desc_sizes = $element['desc_sizes'];
			}
			else
			{
				$desc_sizes = array(
							'default'	=> __( 'Font Size default - used for all screen sizes(no media query)', 'avia_framework' ),
							'desktop'	=> __( 'Font Size for large screens (wider than 990px - eg: Desktop)', 'avia_framework' ),
							'medium'	=> __( 'Font Size for medium sized screens (between 768px and 989px - eg: Tablet Landscape)', 'avia_framework' ),
							'small'		=> __( 'Font Size for small screens (between 480px and 767px - eg: Tablet Portrait)', 'avia_framework' ),
							'mini'		=> __( 'Font Size for very small screens (smaller than 479px - eg: Smartphone Portrait)', 'avia_framework' ),
						);
			}

			//	fallback where 'desktop' is not set
			if( ! isset( $desc_sizes['desktop'] ) )
			{
				$desc_sizes['desktop'] =  __( 'Font Size for large screens (wider than 990px - Desktop)', 'avia_framework' );
				$sort_arrays = true;
			}

			$titles = array(
							'default'	=> __( 'Default', 'avia_framework' ),
							'desktop'	=> __( 'Desktop', 'avia_framework' ),
							'medium'	=> __( 'Tablet Landscape', 'avia_framework' ),
							'small'		=> __( 'Tablet Portrait', 'avia_framework' ),
							'mini'		=> __( 'Mobile', 'avia_framework' ),
						);

			$icons = array(
							'default'	=> 'default',
							'desktop'	=> 'desktop',
							'medium'	=> 'tablet-landscape',
							'small'		=> 'tablet-portrait',
							'mini'		=> 'mobile'
						);

			//	resort array in case desktop was added
			if( $sort_arrays )
			{
				foreach( $sort_data as $data_name )
				{
					//	some PHP versions seem to have problems with $$data_name[ $key ]
					$temp = $$data_name;
					$resorted = array();

					foreach( $sort_keys as $key )
					{
						$resorted[ $key ] = isset( $temp[ $key ] ) ? $temp[ $key ] : '';
					}

					$$data_name = $resorted;
				}
			}

			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$textfield = isset( $element['textfield'] ) ? $element['textfield'] : false;
			$use_textfield = avia_get_option( 'alb_developer_ext_typo' ) == 'alb_developer_ext_typo';
			$hide_desktop = isset( $element['hide_desktop'] ) ? $element['hide_desktop'] : false;

			$template = array(
							array(
								'type'		=> 'icon_switcher_container',
								'name'		=> ! empty( $element['name'] ) ? $element['name'] : '',
								'desc'		=> ! empty( $element['desc'] ) ? $element['desc'] : '',
//								'icon'		=> __( 'Content', 'avia_framework' ),
								'nodescription' => true,
								'required'	=> isset( $element['required'] ) ? $element['required'] : array()
							)
						);

			foreach( $id_sizes as $size => $id )
			{
				$template[] = array(
								'type' 	=> 'icon_switcher',
								'name'	=> $titles[ $size ],
								'icon'	=> $icons[ $size ],
								'nodescription' => true
							);

				if( $textfield === true && $use_textfield )
				{
					$desc = __( 'Enter size of the font, only use valid CSS units. Unit defaults to px if missing.', 'avia_framework' );

					if( 'default' == $size )
					{
						$desc .= ' ' . __( 'Leave empty for default.', 'avia_framework' );
					}
					else
					{
						$desc .= ' ' . __( 'Leave empty to use default setting.', 'avia_framework' );
					}

					if( ! $hide_desktop && 'default' == $size )
					{
						//	hiding the desktop HTML might break layout - needs to be activated with $shortcode->config['hide_desktop_fonts']
						$desc .= ' ' . __( 'Hiding this text on large screens by default is currently not supported and will be ignored.', 'avia_framework' );
					}
					else if( ! $hide_desktop && 'desktop' == $size )
					{
						//	hiding the desktop HTML might break layout - needs to be activated with $shortcode->config['hide_desktop_fonts'] for responsive devices
						$desc .= ' ' . __( 'Hiding this text on large screens is currently not supported and will be ignored.', 'avia_framework' );
					}
					else
					{
						$desc .= ' ' . __( 'To hide text on this device enter &quot;hidden&quot;.', 'avia_framework' );
					}


					$template[] = array(
									'name'		=> $desc_sizes[ $size ],
									'desc'		=> $desc,
									'id'		=> $id_sizes[ $size],
									'type'		=> 'input',
									'std'		=> '',
									'lockable'	=> $lockable
								);
				}
				else
				{

					$template[] = array(
									'name'		=> $desc_sizes[ $size ],
									'desc'		=> __( 'Font size for the text in px', 'avia_framework' ),
									'id'		=> $id_sizes[ $size],
									'type'		=> 'select',
									'std'		=> '',
									'lockable'	=> $lockable,
									'subtype'	=> $subtype[ $size]
								);
				}

				$template[] = array(
								'type' 	=> 'icon_switcher_close',
								'nodescription' => true
						);
			}

			$template[] = array(
								'type' 	=> 'icon_switcher_container_close',
								'nodescription' => true
							);

			return $template;
		}

		/**
		 * Returns a columns count icon switcher section.
		 *
		 * @since 4.6.4
		 * @since 5.1					moved to this class
		 * @param array $element
		 * @return array
		 */
		protected function columns_count_icon_switcher( array $element )
		{
			if( isset( $element['heading'] ) && is_array( $element['heading'] ) )
			{
				$heading = $element['heading'];
			}
			else
			{
				$info  = __( 'Set the column count for this element, based on the device screensize.', 'avia_framework' ) . '<br/><small>';
				$info .= __( 'Please note that changing the default will overwrite any individual &quot;landscape&quot; width settings. Each item will have the same width', 'avia_framework' ) . '</small>';

				$heading = array(
								'name' 	=> __( 'Element Columns', 'avia_framework' ),
								'desc' 	=> $info,
								'type' 	=> 'heading',
								'description_class' => 'av-builder-note av-neutral',
							);
			}

			if( isset( $element['subtype'] ) && is_array( $element['subtype'] ) )
			{
				$subtype = $element['subtype'];
			}
			else
			{
				$subtype = array(
							'default'	=> AviaHtmlHelper::number_array( 2, 6, 1, array( __( 'Automatic, based on screen width', 'avia_framework' )	=> 'flexible', __( '1 column', 'avia_framework' ) => '1' ), ' ' . __( 'columns', 'avia_framework' ) ),
							'desktop'	=> AviaHtmlHelper::number_array( 2, 6, 1, array( __( 'Use default', 'avia_framework' )	=> '', __( '1 column', 'avia_framework' ) => '1' ), ' ' . __( 'columns', 'avia_framework' ) ),
							'medium'	=> AviaHtmlHelper::number_array( 2, 4, 1, array( __( 'Use default', 'avia_framework' )	=> '', __( '1 column', 'avia_framework' ) => '1' ), ' ' . __( 'columns', 'avia_framework' ) ),
							'small'		=> AviaHtmlHelper::number_array( 2, 4, 1, array( __( 'Use default', 'avia_framework' )	=> '', __( '1 column', 'avia_framework' ) => '1' ), ' ' . __( 'columns', 'avia_framework' ) ),
							'mini'		=> AviaHtmlHelper::number_array( 2, 4, 1, array( __( 'Use default', 'avia_framework' )	=> '', __( '1 column', 'avia_framework' ) => '1' ), ' ' . __( 'columns', 'avia_framework' ) ),
						);
			}

			if( isset( $element['std'] ) && is_array( $element['std'] ) )
			{
				$std = $element['std'];
			}
			else
			{
				$std = array(
							'default'	=> 'flexible',
							'desktop'	=> '',
							'medium'	=> '',
							'small'		=> '',
							'mini'		=> ''
						);
			}

			if( isset( $element['id_sizes'] ) && is_array( $element['id_sizes'] ) )
			{
				$id_sizes = $element['id_sizes'];
			}
			else
			{
				$id_sizes = array(
							'default'	=> 'columns',
							'desktop'	=> 'av-desktop-columns',
							'medium'	=> 'av-medium-columns',
							'small'		=> 'av-small-columns',
							'mini'		=> 'av-mini-columns'
						);
			}

			if( isset( $element['desc_sizes'] ) && is_array( $element['desc_sizes'] ) )
			{
				$desc_sizes = $element['desc_sizes'];
			}
			else
			{
				$desc_sizes = array(
							'default'	=> __( 'Column count - default, used for all screen sizes(no media query)', 'avia_framework' ),
							'desktop'	=> __( 'Column count for large screens (wider than 990px - eg: Desktop)', 'avia_framework' ),
							'medium'	=> __( 'Column count for medium sized screens (between 768px and 989px - eg: Tablet Landscape)', 'avia_framework' ),
							'small'		=> __( 'Column count for small screens (between 480px and 767px - eg: Tablet Portrait)', 'avia_framework' ),
							'mini'		=> __( 'Column count for very small screens (smaller than 479px - eg: Smartphone Portrait)', 'avia_framework' ),
						);
			}

			$titles = array(
							'default'	=> __( 'Default', 'avia_framework' ),
							'desktop'	=> __( 'Desktop', 'avia_framework' ),
							'medium'	=> __( 'Tablet Landscape', 'avia_framework' ),
							'small'		=> __( 'Tablet Portrait', 'avia_framework' ),
							'mini'		=> __( 'Mobile', 'avia_framework' ),
						);

			$icons = array(
							'default'	=> 'default',
							'desktop'	=> 'desktop',
							'medium'	=> 'tablet-landscape',
							'small'		=> 'tablet-portrait',
							'mini'		=> 'mobile'
						);

			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;

			$template = array();

			if( ! empty( $heading ) )
			{
				$template[] = $heading;
			}

			$template[] = array(
								'type' 	=> 'icon_switcher_container',
								'name'  => ! empty( $element['name'] ) ? $element['name'] : '',
								'desc' 	=> ! empty( $element['desc'] ) ? $element['desc'] : '',
//								'icon'  => __( 'Content', 'avia_framework' ),
								'nodescription' => true,
								'required'	=> isset( $element['required'] ) ? $element['required'] : array()
							);

			$desc_cols = __( 'How many columns do you want to use.', 'avia_framework' );
			if( count( $id_sizes ) <= 1 )
			{
				$desc_cols .= ' ' . __( 'Responsive behaviour is handled by default from theme.', 'avia_framework' );
			}

			foreach( $id_sizes as $size => $id )
			{
				$template[] = array(
								'type' 	=> 'icon_switcher',
								'name'	=> $titles[ $size ],
								'icon'	=> $icons[ $size ],
								'nodescription' => true
							);

				$template[] = array(
								'name'		=> $desc_sizes[ $size ],
								'desc'		=> $desc_cols,
								'id'		=> $id_sizes[ $size ],
								'type'		=> 'select',
								'std'		=> $std[ $size ],
								'lockable'	=> $lockable,
								'subtype'	=> $subtype[ $size ]
							);

				$template[] = array(
								'type' 	=> 'icon_switcher_close',
								'nodescription' => true
						);
			}

			$template[] = array(
								'type' 	=> 'icon_switcher_container_close',
								'nodescription' => true
							);

			return $template;
		}

		/**
		 * Simple checkboxes for whole element visibility.
		 * Is a wrapper for backwards comp. to hide all element via css class
		 *
		 * @since 4.5.6.1
		 * @since 5.1					moved to this class and modified to call responsive_visibility
		 * @param array $element
		 * @return array
		 */
		protected function screen_options_visibility( array $element )
		{

			$element['id'] = 'hide';
			$element['toggle'] = false;
			$element['styles_cb'] = array();

			return $this->responsive_visibility( $element );
		}

		/**
		 * Add a checkbox list for responsive hiding of any container in an element
		 * (works with css rules in post css files)
		 *
		 * @since 5.1
		 * @param array $element
		 * @return array
		 */
		protected function responsive_visibility( array $element )
		{
			$def_name = __( 'Element Visibility', 'avia_framework' );
			$def_desc = __( 'Set the visibility for this element, based on the device screensize.', 'avia_framework' );

			$id =  isset( $element['id'] ) ? $element['id'] : 'container';
			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$desc = isset( $element['desc'] ) ? $element['desc'] : $def_desc;
			$toggle = isset( $element['toggle'] ) ? $element['toggle'] : true;

			$required = isset( $element['required'] ) ? $element['required'] : array();
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;

			$def_styles_cb = array(
								'method'	=> 'responsive_visibility',
								'id'		=> $id
							);

			$styles_cb = isset( $element['styles_cb'] ) ? $element['styles_cb'] : $def_styles_cb;

			$template = array(

							array(
									'type' 				=> 'heading',
									'name'              => $name,
									'desc'              => $desc,
									'styles_cb'			=> $styles_cb,
									'required'			=> $required,
							),

							array(
									'desc'              => __( 'Hide on large screens (wider than 990px - eg: Desktop)', 'avia_framework' ),
									'id'                => 'av-desktop-' . $id,
									'type'              => 'checkbox',
									'std'               => '',
									'container_class'   => 'av-multi-checkbox',
									'lockable'			=> $lockable,
									'required'			=> $required
								),

							array(
									'desc'              => __( 'Hide on medium sized screens (between 768px and 989px - eg: Tablet Landscape)', 'avia_framework' ),
									'id'                => 'av-medium-' . $id,
									'type'              => 'checkbox',
									'std'               => '',
									'container_class'   => 'av-multi-checkbox',
									'lockable'			=> $lockable,
									'required'			=> $required
								),

							array(
									'desc'              => __( 'Hide on small screens (between 480px and 767px - eg: Tablet Portrait)', 'avia_framework' ),
									'id'                => 'av-small-' . $id,
									'type'              => 'checkbox',
									'std'               => '',
									'container_class'   => 'av-multi-checkbox',
									'lockable'			=> $lockable,
									'required'			=> $required
								),

							array(
									'desc'              => __( 'Hide on very small screens (smaller than 479px - eg: Smartphone Portrait)', 'avia_framework' ),
									'id'                => 'av-mini-' . $id,
									'type'              => 'checkbox',
									'std'               => '',
									'container_class'   => 'av-multi-checkbox',
									'lockable'			=> $lockable,
									'required'			=> $required
								)

						);

			if( true !== $toggle )
			{
				return $template;
			}

			$return = array(
							array(
								'type'          => 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Responsive', 'avia_framework' ),
								'content'		=> $template,
								'nodescription'	=> true
							)
						);

			return $return;
		}

		/**
		 * Parallax
		 *
		 * @since 5.0
		 * @param array $element
		 * @return array
		 */
		protected function parallax( array $element )
		{
			$def_name = __( 'Parallax Rules', 'avia_framework' );

			$id = isset( $element['id'] ) ? $element['id'] : 'parallax';
			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$desc = isset( $element['desc'] ) ? $element['desc'] : '';

			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : true;
			$required = isset( $element['required'] ) ? $element['required'] : array();

			$resp_sizes_opts = ( isset( $element['resp_sizes_opts'] ) && is_array( $element['resp_sizes_opts'] ) ) ? $element['resp_sizes_opts'] : $this->resp_sizes_options;
			$resp_sizes_desc = ( isset( $element['resp_sizes_desc'] ) && is_array( $element['resp_sizes_desc'] ) ) ? $element['resp_sizes_desc'] : $this->resp_sizes_desc;

			if( ! empty( $desc ) )
			{
				$desc .= '<br />';
			}
			$desc .= $this->limited_css;

			$template = array(
							array(
								'type'		=> 'icon_switcher_container',
								'name'		=> $name,
								'desc_html'	=> $desc,
//								'icon'		=> __( 'Content', 'avia_framework' ),
								'nodescription' => true,
								'required'	=> $required
							)
						);

			foreach( $resp_sizes_opts as $size => $resp_size_key )
			{
				$template[] = array(
								'type' 	=> 'icon_switcher',
								'name'	=> $this->resp_titles[ $size ],
								'icon'	=> $this->resp_icons[ $size ],
								'nodescription' => true
							);


				if( 'default' == $size )
				{
					$first = array( __( 'None', 'avia_framework' )	=> '' );
				}
				else
				{
					$first = array(
								__( 'Use default setting', 'avia_framework' )	=> '',
								__( 'None', 'avia_framework' )					=> 'none'
							);
				}

				$subtype_pos = array(
									__( 'Bottom to top', 'avia_framework' )	=> 'bottom_top',
									__( 'Left to right', 'avia_framework' )	=> 'left_right',
									__( 'Right to left', 'avia_framework' )	=> 'right_left',
								);

				$desc  = __( 'Select a parallax effect for the element when scrolling the page. Parallax is supported in modern browsers supporting transform and ignored in older.', 'avia_framework' ) . '<br />';
				$desc .= __( 'Do not forget to set z-index in &quot;Position Tab&quot;.', 'avia_framework' );

				$el = array(
							'name'		=> __( 'Parallax', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
							'desc'		=> $desc,
							'id'		=> $resp_size_key . $id. '_parallax',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> $lockable,
							'subtype'	=> $first + $subtype_pos
						);

				//	we only set callback method for default
				if( 'default' == $size )
				{
					$el['styles_cb'] = array(
											'method'		=> 'parallax',
											'id'			=> $id
										);
				}

				$template[] = $el;

				$template[] = array(
								'name'		=> __( 'Parallax Speed', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> __( 'Select the speed the element is moved different to content when the page is scrolled. A positiv value will move it faster, a negative will slow it down.', 'avia_framework' ),
								'id'		=> $resp_size_key . $id . '_parallax_speed',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'required'	=> array( $resp_size_key . $id. '_parallax', 'parent_not_in_array', ',none' ),
								'subtype'	=> AviaHtmlHelper::number_array( -30, 200, 10, array( __( 'Default (= 50%)', 'avia_framework' ) => '' ), ' %' )
						);


				$template[] = array(
								'type' 	=> 'icon_switcher_close',
								'nodescription' => true
						);

			}

			$template[] = array(
								'type' 	=> 'icon_switcher_container_close',
								'nodescription' => true
							);


			return $template;
		}

		/**
		 * Transform Options
		 *
		 * @since 5.0
		 * @param array $element
		 * @return array
		 */
		protected function transform( array $element )
		{
			$def_name = __( 'Transform Rules', 'avia_framework' );

			$id = isset( $element['id'] ) ? $element['id'] : 'transform';
			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$desc = isset( $element['desc'] ) ? $element['desc'] : '';

			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : true;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$toggle = isset( $element['toggle'] ) && true === $element['toggle'];

			$resp_sizes_opts = ( isset( $element['resp_sizes_opts'] ) && is_array( $element['resp_sizes_opts'] ) ) ? $element['resp_sizes_opts'] : $this->resp_sizes_options;
			$resp_sizes_desc = ( isset( $element['resp_sizes_desc'] ) && is_array( $element['resp_sizes_desc'] ) ) ? $element['resp_sizes_desc'] : $this->resp_sizes_desc;

			if( ! empty( $desc ) )
			{
				$desc .= '<br />';
			}
			$desc .= $this->limited_css;

			$subtype_multi_rotate = array(
								'x'		=> __( 'X-Axis', 'avia_framework' ),
								'y'		=> __( 'Y-Axis', 'avia_framework' ),
								'z'		=> __( 'Z-Axis', 'avia_framework' ),
								'angle'	=> __( 'Rotation Angle', 'avia_framework' )
							);

			$subtype_multi_scale = array(
								'x'		=> __( 'Scale X-Axis', 'avia_framework' ),
								'y'		=> __( 'Scale Y-Axis', 'avia_framework' ),
								'z'		=> __( 'Scale Z-Axis', 'avia_framework' )
							);

			$subtype_multi_skew = array(
								'x'		=> __( 'X-Axis distortion Angle', 'avia_framework' ),
								'y'		=> __( 'Y-Axis distortion Angle', 'avia_framework' )
							);

			$subtype_multi_translate = array(
								'x'		=> __( 'X-Axis', 'avia_framework' ),
								'y'		=> __( 'Y-Axis', 'avia_framework' ),
								'z'		=> __( 'Z-Axis', 'avia_framework' )
							);

			$content = isset( $element['content'] ) ? $element['content'] : array( 'perspective', 'rotation', 'scale', 'skew', 'translate' );

			$perspective = in_array( 'perspective', $content );
			$rotate = in_array( 'rotation', $content );
			$scale = in_array( 'scale', $content );
			$skew = in_array( 'skew', $content );
			$translate = in_array( 'translate', $content );

			$styles_cb = array(
								'method'	=> 'transform',
								'id'		=> $id,
								'content'	=> $content
							);

			$template = array(
							array(
								'type'		=> 'icon_switcher_container',
								'name'		=> $name,
								'desc_html'	=> $desc,
//								'icon'		=> __( 'Content', 'avia_framework' ),
								'nodescription' => true,
								'required'	=> $required
							)
						);

			foreach( $resp_sizes_opts as $size => $resp_size_key )
			{
				$template[] = array(
								'type' 	=> 'icon_switcher',
								'name'	=> $this->resp_titles[ $size ],
								'icon'	=> $this->resp_icons[ $size ],
								'nodescription' => true
							);

				if( $perspective )
				{
					$el = array(
								'name'		=> __( 'Perspective', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> __( 'Enter a valid value (including a CSS unit like px, em, vw,..) to set a perspective, used to apply a perspective transform (to the children of the element). Negative values are syntax errors. Leave empty to use default or inherit desktop setting.', 'avia_framework' ),
								'id'		=> $resp_size_key . $id . '_perspective',
								'type'		=> 'input',
								'std'		=> '',
								'lockable'	=> $lockable
							);

					//	we only set callback method for default
					if( 'default' == $size && is_array( $styles_cb ) )
					{
						$el['styles_cb'] = $styles_cb;
						$styles_cb = null;
					}

					$template[] = $el;
				}

				if( $rotate )
				{
					$desc  = __( 'Enter valid values to rotate the element. Leave all empty for no rotation or to inherit desktop setting.', 'avia_framework' ) . '<br /><br />';
					$desc .= __( 'Valid values x,y,z: 0-1, Angle: e.g. 45deg', 'avia_framework' ). '<br />';
					$desc .= __( 'Unset values default to rotate3d( 0, 0, 1, 0 ).', 'avia_framework' );

					$el = array(
								'name'		=> __( 'Rotation', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> $desc,
								'id'		=> $resp_size_key . $id . '_rotation',
								'type'		=> 'multi_input',
//								'sync'		=> true,
								'std'		=> '',
								'lockable'	=> $lockable,
								'multi'		=> $subtype_multi_rotate
							);

					//	we only set callback method for default
					if( 'default' == $size && is_array( $styles_cb ) )
					{
						$el['styles_cb'] = $styles_cb;
						$styles_cb = null;
					}

					$template[] = $el;
				}

				if( $scale )
				{
					$desc  = __( 'Enter valid values to scale the element. Leave all empty for no scale or to inherit desktop setting.', 'avia_framework' ) . '<br /><br />';
					$desc .= __( 'Unset values default to scale3d( 1, 1, 1 ).', 'avia_framework' );

					$el = array(
								'name'		=> __( 'Scaling', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> $desc,
								'id'		=> $resp_size_key . $id . '_scale',
								'type'		=> 'multi_input',
//								'sync'		=> true,
								'std'		=> '',
								'lockable'	=> $lockable,
								'multi'		=> $subtype_multi_scale
							);

					//	we only set callback method for default
					if( 'default' == $size && is_array( $styles_cb ) )
					{
						$el['styles_cb'] = $styles_cb;
						$styles_cb = null;
					}

					$template[] = $el;
				}

				if( $skew )
				{
					$desc  = __( 'Enter valid values to skew the element. Leave all empty for no skew or to inherit desktop setting.', 'avia_framework' ) . '<br /><br />';
					$desc .= __( 'Valid values Angle: e.g. 45deg', 'avia_framework' ). '<br />';
					$desc .= __( 'Unset values default to skew( 0, 0 ).', 'avia_framework' );

					$el = array(
								'name'		=> __( 'Skewing', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> $desc,
								'id'		=> $resp_size_key . $id . '_skew',
								'type'		=> 'multi_input',
//								'sync'		=> true,
								'std'		=> '',
								'lockable'	=> $lockable,
								'multi'		=> $subtype_multi_skew
							);

					//	we only set callback method for default
					if( 'default' == $size && is_array( $styles_cb ) )
					{
						$el['styles_cb'] = $styles_cb;
						$styles_cb = null;
					}

					$template[] = $el;
				}


				if( $translate )
				{
					$desc  = __( 'Enter valid values to translate coordinates of the element. Leave all empty for no translation or to inherit desktop setting.', 'avia_framework' ) . '<br /><br />';
					$desc .= __( 'Unset values default to translate3d( 0, 0, 0 ).', 'avia_framework' );

					$el = array(
								'name'		=> __( 'Translate Coordinates', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> $desc,
								'id'		=> $resp_size_key . $id . '_translate',
								'type'		=> 'multi_input',
//								'sync'		=> true,
								'std'		=> '',
								'lockable'	=> $lockable,
								'multi'		=> $subtype_multi_translate
							);

					//	we only set callback method for default
					if( 'default' == $size && is_array( $styles_cb ) )
					{
						$el['styles_cb'] = $styles_cb;
						$styles_cb = null;
					}

					$template[] = $el;
				}

				$template[] = array(
								'type' 	=> 'icon_switcher_close',
								'nodescription' => true
						);

			}

			$template[] = array(
								'type' 	=> 'icon_switcher_container_close',
								'nodescription' => true
							);

			if( ! $toggle )
			{
				return $template;
			}

			$return = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Transformations', 'avia_framework' ),
								'content'		=> $template
							),
					);

			return $return;
		}

		/**
		 * Positioning Toggle
		 *
		 * @since 5.0
		 * @param array $element
		 * @return array
		 */
		protected function position( array $element )
		{
			$def_name = __( 'Position Rules', 'avia_framework' );

			$id = isset( $element['id'] ) ? $element['id'] : 'css_position';
			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$desc = isset( $element['desc'] ) ? $element['desc'] : '';

			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : true;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$toggle = isset( $element['toggle'] ) && true === $element['toggle'];
			$no_limited = isset( $element['no_limited'] ) && true === $element['no_limited'];

			$content = isset( $element['content'] ) ? (array) $element['content'] : array( 'position', 'z_index' );

			$resp_sizes_opts = ( isset( $element['resp_sizes_opts'] ) && is_array( $element['resp_sizes_opts'] ) ) ? $element['resp_sizes_opts'] : $this->resp_sizes_options;
			$resp_sizes_desc = ( isset( $element['resp_sizes_desc'] ) && is_array( $element['resp_sizes_desc'] ) ) ? $element['resp_sizes_desc'] : $this->resp_sizes_desc;

			if( ! empty( $desc ) )
			{
				$desc .= '<br />';
			}

			if( ! $no_limited )
			{
				$desc .= $this->limited_css;
			}

			if( isset( $element['subtype_loc'] ) && is_array( $element['subtype_loc'] ) )
			{
				$subtype_multi = $element['subtype_loc'];
			}
			else
			{
				$subtype_multi = $this->multi_input4_options;
			}

			//	first entry (none / desktop setting) will be added in responsive loop
			$subtype_pos = array(
								__( 'Relative', 'avia_framework' )	=> 'relative',
								__( 'Absolute', 'avia_framework' )	=> 'absolute'
							);

			$position = in_array( 'position', $content );
			$z_index = in_array( 'z_index', $content );

			$template = array(
							array(
								'type'		=> 'icon_switcher_container',
								'name'		=> $name,
								'desc_html'	=> $desc,
//								'icon'		=> __( 'Content', 'avia_framework' ),
								'nodescription' => true,
								'required'	=> $required
							)
						);

			$styles_cb = array(
								'method'	=> 'position',
								'id'		=> $id,
								'content'	=> $content
							);

			foreach( $resp_sizes_opts as $size => $resp_size_key )
			{
				$template[] = array(
								'type' 	=> 'icon_switcher',
								'name'	=> $this->resp_titles[ $size ],
								'icon'	=> $this->resp_icons[ $size ],
								'nodescription' => true
							);

				if( $position )
				{
					if( 'default' == $size )
					{
						$first = array( __( 'Use theme default', 'avia_framework' )	=> '' );
					}
					else
					{
						$first = array( __( 'Use default setting', 'avia_framework' )	=> '' );
					}

					$desc  = __( 'Select a css position for the element.', 'avia_framework' ) . '<br /><br />';
					$desc .= __( 'Be aware that the result of position is very much depending on the CSS rules of the surrounding containers. Check the CSS and HTML layout if it is not as expected.', 'avia_framework' );

					$el = array(
								'name'		=> __( 'Element Position', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> $desc,
								'id'		=> $resp_size_key . $id,
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'subtype'	=> $first + $subtype_pos
							);

					//	we only set callback method for default
					if( 'default' == $size )
					{
						$el['styles_cb'] = $styles_cb;
						$styles_cb = null;
					}

					$template[] = $el;

					$template[] = array(
									'name'		=> __( 'Location', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
									'desc'		=> __( 'Select the location for the element. Leave not needed values empty and make sure to use correct combinations and css units. Defaults to px. Do not forget to check responsive appearance.', 'avia_framework' ),
									'id'		=> $resp_size_key . $id . '_location',
									'type'		=> 'multi_input',
	//								'sync'		=> true,
									'std'		=> '',
									'lockable'	=> $lockable,
									'required'	=> array( $resp_size_key . $id, 'parent_in_array', 'relative,absolute' ),
									'multi'		=> $subtype_multi
							);
				}

				if( $z_index )
				{
					$el = array(
								'name'		=> __( 'Z-Index', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> __( 'Set the z-index for this element. Leave empty to use default or inherit desktop setting.', 'avia_framework' ),
								'id'		=> $resp_size_key . $id . '_z_index',
								'type'		=> 'input_number',
								'step'		=> 1,
								'std'		=> '',
								'lockable'	=> $lockable
							);

					//	we only set callback method for default
					if( 'default' == $size && is_array( $styles_cb ) )
					{
						$el['styles_cb'] = $styles_cb;
						$styles_cb = null;
					}

					$template[] = $el;
				}

				$template[] = array(
								'type' 	=> 'icon_switcher_close',
								'nodescription' => true
						);
			}


			$template[] = array(
								'type' 	=> 'icon_switcher_container_close',
								'nodescription' => true
							);

			if( ! $toggle )
			{
				return $template;
			}

			$return = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Position', 'avia_framework' ),
								'content'		=> $template
							),
					);

			return $return;
		}

		/**
		 * Responsive Margin and Padding Toggle
		 *
		 * @since 5.1
		 * @param array $element
		 * @return array
		 */
		protected function margin_padding( array $element )
		{
			$def_name = __( 'Margin And Padding', 'avia_framework' );
			$default_desc_margin = __( 'Set your custom margin.  Valid CSS units are accepted, eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' );
			$default_desc_padding = __( 'Set your custom padding. Valid CSS units are accepted, eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' );

			$first_desc = __( 'Leave empty for theme default settings.', 'avia_framework' );
			$resp_desc = __( 'Leave empty to use default settings.', 'avia_framework' );

			$id_margin = isset( $element['id_margin'] ) ? $element['id_margin'] : 'margin';
			$id_padding = isset( $element['id_padding'] ) ? $element['id_padding'] : 'padding';

			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$name_toggle = isset( $element['name_toggle'] ) ? $element['name_toggle'] : $def_name;
			$name_margin = isset( $element['name_margin'] ) ? $element['name_margin'] : __( 'Margin', 'avia_framework' );
			$name_padding = isset( $element['name_padding'] ) ? $element['name_padding'] : __( 'Padding', 'avia_framework' );

			$desc = isset( $element['desc'] ) ? $element['desc'] : '';
			$desc_margin = isset( $element['desc_margin'] ) ? $element['desc_margin'] : $default_desc_margin;
			$desc_padding = isset( $element['desc_padding'] ) ? $element['desc_padding'] : $default_desc_padding;

			$std_margin = isset( $element['std_margin'] ) ? $element['std_margin'] : '';
			$std_padding = isset( $element['std_padding'] ) ? $element['std_padding'] : '';

			$sync_margin = isset( $element['sync_margin'] ) ? $element['sync_margin'] : true;
			$sync_padding = isset( $element['sync_padding'] ) ? $element['sync_padding'] : true;

			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : true;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$toggle = isset( $element['toggle'] ) && true === $element['toggle'];

			$content = isset( $element['content'] ) ? (array) $element['content'] : array( 'margin', 'padding' );

			$resp_sizes_opts = ( isset( $element['resp_sizes_opts'] ) && is_array( $element['resp_sizes_opts'] ) ) ? $element['resp_sizes_opts'] : $this->resp_sizes_options;
			$resp_sizes_desc = ( isset( $element['resp_sizes_desc'] ) && is_array( $element['resp_sizes_desc'] ) ) ? $element['resp_sizes_desc'] : $this->resp_sizes_desc;

			$margin = in_array( 'margin', $content );
			$padding = in_array( 'padding', $content );

			$template = array(
							array(
								'type'		=> 'icon_switcher_container',
								'name'		=> $name,
								'desc_html'	=> $desc,
//								'icon'		=> __( 'Content', 'avia_framework' ),
								'nodescription' => true,
								'required'	=> $required
							)
						);

			$styles_cb = array(
								'method'	=> 'margin_padding',
								'id'		=> array(
													'margin'	=> $id_margin,
													'padding'	=> $id_padding
												),
								'content'	=> $content,
								'multi'		=> array(
													'margin'	=> isset( $element['multi_margin'] ) ? $element['multi_margin'] : null,
													'padding'	=> isset( $element['multi_padding'] ) ? $element['multi_padding'] : null
												)
							);


			foreach( $resp_sizes_opts as $size => $resp_size_key )
			{
				$template[] = array(
								'type' 	=> 'icon_switcher',
								'name'	=> $this->resp_titles[ $size ],
								'icon'	=> $this->resp_icons[ $size ],
								'nodescription' => true
							);

				if( $margin )
				{
					if( 'default' == $size )
					{
						$d = $desc_margin . ' ' . $first_desc;
					}
					else
					{
						$d = $desc_margin . ' ' . $resp_desc;
					}

					$el = array(
							'name'		=> $name_margin  . ' - '. $resp_sizes_desc[ $size ],
							'desc'		=> $d,
							'id'		=> $resp_size_key . $id_margin,
							'type'		=> 'multi_input',
							'sync'		=> $sync_margin,
							'std'		=> 'default' == $size ? $std_margin : '',
							'lockable'	=> $lockable,
							'styles_cb'	=> $styles_cb,
							'required'	=> $required,
							'multi'		=> isset( $element['multi_margin'] ) ? $element['multi_margin'] : $this->multi_margins_options
						);

					//	we only set callback method for default
					if( 'default' == $size )
					{
						$el['styles_cb'] = $styles_cb;
						$styles_cb = null;
					}

					$template[] = $el;
				}

				if( $padding )
				{
					if( 'default' == $size )
					{
						$d = $desc_padding . ' ' . $first_desc;
					}
					else
					{
						$d = $desc_padding . ' ' . $resp_desc;
					}

					$el = array(
							'name'		=> $name_padding  . ' - '. $resp_sizes_desc[ $size ],
							'desc'		=> $d,
							'id'		=> $resp_size_key . $id_padding,
							'type'		=> 'multi_input',
							'sync'		=> $sync_padding,
							'std'		=> 'default' == $size ? $std_padding : '',
							'lockable'	=> $lockable,
							'styles_cb'	=> $styles_cb,
							'required'	=> $required,
							'multi'		=> isset( $element['multi_padding'] ) ? $element['multi_padding'] : $this->multi_padding_options
						);

					//	we only set callback method for default
					if( 'default' == $size && is_array( $styles_cb ) )
					{
						$el['styles_cb'] = $styles_cb;
						$styles_cb = null;
					}

					$template[] = $el;
				}

				$template[] = array(
								'type' 	=> 'icon_switcher_close',
								'nodescription' => true
						);
			}

			$template[] = array(
								'type' 	=> 'icon_switcher_container_close',
								'nodescription' => true
							);

			if( ! $toggle )
			{
				return $template;
			}

			$return = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> $name_toggle,
								'content'		=> $template
							),
					);

			return $return;
		}

		/**
		 * Slideshow Section - Margin and Padding Settings
		 *
		 * @since 5.0
		 * @param array $element
		 * @return array
		 */
		protected function slideshow_section_margin_padding( array $element )
		{
			$required = isset( $element['required'] ) ? $element['required'] : array();

			$resp_sizes_opts = ( isset( $element['resp_sizes_opts'] ) && is_array( $element['resp_sizes_opts'] ) ) ? $element['resp_sizes_opts'] : $this->resp_sizes_options;
			$resp_sizes_desc = ( isset( $element['resp_sizes_desc'] ) && is_array( $element['resp_sizes_desc'] ) ) ? $element['resp_sizes_desc'] : $this->resp_sizes_desc;

			$styles_cb = array(
								'method'	=> 'slideshow_section_margin_padding',
//								'id'		=> $id			//	hardcoded implementation
							);

			$template = array(
							array(
								'type'		=> 'icon_switcher_container',
//								'icon'		=> __( 'Content', 'avia_framework' ),
								'nodescription' => true,
								'required'	=> $required
							)
						);

			foreach( $resp_sizes_opts as $size => $resp_size_key )
			{
				$template[] = array(
								'type' 	=> 'icon_switcher',
								'name'	=> $this->resp_titles[ $size ],
								'icon'	=> $this->resp_icons[ $size ],
								'nodescription' => true
							);

				$el = array(
								'name'		=> __( 'Slideshow Section Top And Bottom Margin', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> __( 'Set a custom top or bottom margin. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;. Leave empty for default or inherit desktop setting.', 'avia_framework' ),
								'id'		=> $resp_size_key . 'margin',
								'type'		=> 'multi_input',
								'sync'		=> true,
								'std'		=> '',
								'multi'		=> array(
													'top'		=> __( 'Margin Top', 'avia_framework' ),
													'bottom'	=> __( 'Margin Bottom', 'avia_framework' ),
												)
							);

				//	we only set callback method for default
				if( 'default' == $size )
				{
					$el['styles_cb'] = $styles_cb;
					$styles_cb = null;
				}

				$template[] = $el;


				$template[] = array(
								'name'		=> __( 'Slide Title Tabs Padding', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> __( 'Define the slide title tabs top padding (only works if no icon is displayed at the top of the slide title)', 'avia_framework' ),
								'id'		=> $resp_size_key . 'tab_padding',
								'type'		=> 'select',
								'std'		=> '',
								'required'	=> array( 'element_layout', 'equals', 'tabs' ),
								'subtype'	=> array(
													__( 'Default Padding (10px)', 'avia_framework' )	=> '',
													__( 'No Padding', 'avia_framework' )				=> 'none',
													__( 'Small Padding (0)', 'avia_framework' )			=> 'small',
													__( 'Large Padding (20px)', 'avia_framework' )		=> 'large',
													__( 'Custom Padding', 'avia_framework' )			=> 'custom'
												)
							);

				$template[] = array(
								'name'		=> __( 'Custom Slide Title Tabs Padding', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> __( 'Set a custom padding. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;. Leave empty for default or inherit desktop setting.', 'avia_framework' ),
								'id'		=> $resp_size_key . 'tab_padding_custom',
								'type'		=> 'multi_input',
//								'sync'		=> true,
								'std'		=> '',
								'required'	=> array( $resp_size_key . 'tab_padding', 'equals', 'custom' ),
								'multi'		=> array(
												'top'		=> __( 'Padding Top ', 'avia_framework' )
											)
								);

				$template[] = array(
								'name'		=> __( 'Slides Content Padding', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> __( 'Define the slide sections top and bottom padding', 'avia_framework' ),
								'id'		=> $resp_size_key . 'padding',
								'type'		=> 'select',
								'std'		=> '',
								'subtype'	=> array(
													__( 'Default Padding (50px 50px)', 'avia_framework' )	=> '',
													__( 'No Padding', 'avia_framework' )					=> 'no-padding',
													__( 'Small Padding (20px 20px)', 'avia_framework' )		=> 'small',
													__( 'Large Padding (70px 70px)', 'avia_framework' )		=> 'large',
													__( 'Huge Padding (130px 130px)', 'avia_framework' )	=> 'huge',
													__( 'Custom Padding', 'avia_framework' )				=> 'custom'
												)
							);

				$template[] = array(
								'name'		=> __( 'Custom Slides Content Padding', 'avia_framework' ) . ' - '. $resp_sizes_desc[ $size ],
								'desc'		=> __( 'Set a custom padding. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;. Leave empty for default or inherit desktop setting.', 'avia_framework' ),
								'id'		=> $resp_size_key . 'padding_custom',
								'type'		=> 'multi_input',
								'sync'		=> true,
								'std'		=> '',
								'required'	=> array( $resp_size_key . 'padding', 'equals', 'custom' ),
								'multi'		=> array(
												'top'		=> __( 'Padding Top ', 'avia_framework' ),
												'right'		=> __( 'Padding Right', 'avia_framework' ),
												'bottom'	=> __( 'Padding Bottom', 'avia_framework' ),
												'left'		=> __( 'Padding Left', 'avia_framework' )
											)
								);

				$template[] = array(
								'type' 	=> 'icon_switcher_close',
								'nodescription' => true
						);

			}

			$template[] = array(
								'type' 	=> 'icon_switcher_container_close',
								'nodescription' => true
							);

			$return = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Margin And Padding', 'avia_framework' ),
								'content'		=> $template
							),
					);

			return $return;
		}
	}

}
