<?php
namespace aviaBuilder\base;

/**
 * Base class implements modal popup templates for extended styling options that need callback handlers
 *
 * @added_by Günter
 * @since 4.8.4
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( __NAMESPACE__ . '\aviaPopupTemplatesCallback' ) )
{
	class aviaPopupTemplatesCallback extends \aviaBuilder\base\aviaPopupTemplatesBase
	{
		/**
		 * @since 4.8.4
		 * @var array
		 */
		protected $border_styles_options;

		/**
		 * @since 4.6.4
		 */
		protected function __construct()
		{
			parent::__construct();

			$this->border_styles_options = array();
		}

		/**
		 * @since 4.6.4
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->border_styles_options );
		}


		/**
		 * Border Options toggle
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function border_toggle( array $element )
		{
			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'border',
							'default_check'	=> true,
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border_radius',
							'lockable'		=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Border', 'avia_framework' ),
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * Returns a filtered array of available border styles
		 *
		 * @since 4.8.4
		 * @return array
		 */
		public function get_border_styles_options()
		{
			if( empty( $this->border_styles_options ) )
			{
				$opt = array(
							__( 'None', 'avia_framework' )			=> 'none',
							__( 'Hidden', 'avia_framework' )		=> 'hidden',
							__( 'Solid', 'avia_framework' )			=> 'solid',
							__( 'Dashed', 'avia_framework' )		=> 'dashed',
							__( 'Dotted', 'avia_framework' )		=> 'dotted',
							__( 'Double', 'avia_framework' )		=> 'double',
							__( 'Groove', 'avia_framework' )		=> 'groove',
							__( 'Ridge', 'avia_framework' )			=> 'ridge',
							__( 'Inset', 'avia_framework' )			=> 'inset',
							__( 'Outset', 'avia_framework' )		=> 'outset'
						);

				/**
				 * @since 4.8.4
				 * @param array $opt
				 * @return array
				 */
				$this->border_styles_options = (array) apply_filters( 'avf_available_border_styles_options', $opt );
			}

			return $this->border_styles_options;
		}


		/**
		 * Border Options
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function border( array $element )
		{
			$default_check = isset( $element['default_check'] ) && true === $element['default_check'];
			$id = isset( $element['id'] ) ? $element['id'] : 'border';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();

			$sub_border = $this->get_border_styles_options();
			
			if( $default_check )
			{
				$default = array(
							__( 'Theme Default', 'avia_framework' )	=> '',
						);

				$sub_border = array_merge( $default, $sub_border );
			}
			
			$std_border = reset( $sub_border );

			$template = array(

					array(
							'name'		=> __( 'Border Style', 'avia_framework' ),
							'desc'		=> __( 'Choose the border style for your element here', 'avia_framework' ),
							'id'		=> $id,
							'type'		=> 'select',
							'std'		=> $std_border,
							'lockable'	=> $lockable,
							'styles_cb'	=> array(
											'method'	=> 'border',
											'id'		=> $id
										),
							'required'	=> $required,
							'subtype'	=> $sub_border
						),

					array(
							'name'		=> __( 'Border Width', 'avia_framework' ),
							'desc'		=> __( 'Select your border width. Leave empty for theme default setting.', 'avia_framework' ),
							'id'		=> $id . '_width',
							'type'		=> 'multi_input',
							'sync'		=> true,
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'parent_not_in_array', ',none,hidden' ),
							'multi'		=> array(
											'top'		=> __( 'Top', 'avia_framework' ),
											'right'		=> __( 'Right', 'avia_framework' ),
											'bottom'	=> __( 'Bottom', 'avia_framework' ),
											'left'		=> __( 'left', 'avia_framework' )
										)
						),

					array(
							'name'		=> __( 'Border Color', 'avia_framework' ),
							'desc'		=> __( 'Select the border color for this element here. Leave empty for theme default setting.', 'avia_framework' ),
							'id'		=> $id . '_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'parent_not_in_array', ',none,hidden' )
						)

				);

			return $template;
		}

		/**
		 * Border Radius option
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function border_radius( array $element )
		{
			$id = isset( $element['id'] ) ? $element['id'] : 'border_radius';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();

			$template = array(

					array(
							'name'		=> __( 'Border Radius', 'avia_framework' ),
							'desc'		=> __( 'Select your border radius(e.g. 5px). Leave empty to use theme default setting.', 'avia_framework' ),
							'id'		=> $id,
							'type'		=> 'multi_input',
							'sync'		=> true,
							'std'		=> '',
							'lockable'	=> $lockable,
							'styles_cb'	=> array(
											'method'	=> 'border_radius',
											'id'		=> $id
										),
							'required'	=> $required,
							'multi'		=> array(
											'top'		=> __( 'Top Left', 'avia_framework' ),
											'right'		=> __( 'Top Right', 'avia_framework' ),
											'bottom'	=> __( 'Bottom Right', 'avia_framework' ),
											'left'		=> __( 'Bottom Left', 'avia_framework' )
										)
						)

				);

			return $template;
		}

		/**
		 * Box Shadow Options toggle
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function box_shadow_toggle( array $element )
		{
			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'default_check'	=> true,
							'lockable'		=> true
						)

			);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Box Shadow', 'avia_framework' ),
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * Box Shadow Options
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function box_shadow( array $element )
		{
			$default_check = isset( $element['default_check'] ) && true === $element['default_check'];
			$id = isset( $element['id'] ) ? $element['id'] : 'box_shadow';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$names = isset( $element['names'] ) ? $element['names'] : array();

			$sub_shadow = array(
							__( 'No shadow', 'avia_framework' )		=> 'none',
							__( 'Outside', 'avia_framework' )		=> 'outside',
							__( 'Inset', 'avia_framework' )			=> 'inset',
						);

			$std_shadow = 'none';

			if( $default_check )
			{
				$default = array(
								__( 'Theme Default', 'avia_framework' )	=> ''
							);
				$sub_shadow = array_merge( $default, $sub_shadow );
				$std_shadow = '';
			}

			if( empty( $names ) )
			{
				$names[0] = __( 'Box Shadow', 'avia_framework' );
				$names[1] = __( 'Box Shadow Styling', 'avia_framework' );
				$names[2] = __( 'Box Shadow Color', 'avia_framework' );
			}

			$template = array(

					array(
							'name'		=> $names[0],
							'desc'		=> __( 'Select to add a customized shadow and style', 'avia_framework' ),
							'id'		=> $id,
							'type'		=> 'select',
							'std'		=> $std_shadow,
							'lockable'	=> $lockable,
							'styles_cb'	=> array(
												'method'	=> 'box_shadow',
												'id'		=> $id
											),
							'required'	=> $required,
							'subtype'	=> $sub_shadow
						),

					array(
							'name'		=> $names[1],
							'desc'		=> __( 'Set the shadow styling values, you can use em or px, negative values move in opposite direction. If left empty 0 px is assumed', 'avia_framework' ),
							'id'		=> $id . '_style',
							'type'		=> 'multi_input',
//							'sync'		=> true,
							'std'		=> '0px',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'parent_not_in_array', ',none' ),
							'multi'		=> array(
											'offset_x'	=> __( 'Offset X-axis', 'avia_framework' ),
											'offset_y'	=> __( 'Offset Y-axis', 'avia_framework' ),
											'blur'		=> __( 'Blur-Radius', 'avia_framework' ),
											'spread'	=> __( 'Spread-Radius', 'avia_framework' )
										)
						),

					array(
							'name'		=> $names[2],
							'desc'		=> __( 'Select a shadow color for this element here. Leave empty for default color', 'avia_framework' ),
							'id'		=> $id . '_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'parent_not_in_array', ',none' ),
						)

				);

			return $template;
		}

		/**
		 * Gradient Colors Options - Simple Styling
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function gradient_colors( array $element )
		{
			$id = isset( $element['id'] ) ? $element['id'] : 'gradient_color';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$container_class = isset( $element['container_class'] ) ? $element['container_class'] : array();

			if( ! is_array( $container_class ) || empty( $container_class ) )
			{
				$container_class = array( 'av_third', 'av_third av_third_first', 'av_third', 'av_third' );
			}

			if( ! is_array( $id ) )
			{
				$id_array = array(
							$id . '_direction',
							$id . '_1',
							$id . '_2',
							$id . '_3',
						);
			}
			else
			{
				$id_array = $id;
			}

			$template = array(

					array(
							'name'		=> __( 'Background Gradient Direction', 'avia_framework' ),
							'desc'		=> __( 'Define the gradient direction for background of the element', 'avia_framework' ),
							'id'		=> $id_array[0],
							'type'		=> 'select',
							'container_class' => $container_class[0],
							'std'		=> 'vertical',
							'lockable'	=> $lockable,
							'required'	=> $required,
							'styles_cb'	=> array(
												'method'	=> 'gradient_colors',
												'id'		=> $id
											),
							'subtype'	=> array(
											__( 'Top To Bottom', 'avia_framework' )				=> 'vertical',
											__( 'Bottom To Top', 'avia_framework' )				=> 'vertical_rev',
											__( 'Left To Right', 'avia_framework' )				=> 'horizontal',
											__( 'Right To Left', 'avia_framework' )				=> 'horizontal_rev',
											__( 'Left Top To Right Bottom', 'avia_framework' )	=> 'diagonal_tb',
											__( 'Right Bottom To Left Top', 'avia_framework' )	=> 'diagonal_tb_rev',
											__( 'Left Bottom To Right Top', 'avia_framework' )	=> 'diagonal_bt',
											__( 'Right Top To Left Bottom', 'avia_framework' )	=> 'diagonal_bt_rev',
											__( 'Radial Inside Outside', 'avia_framework' )		=> 'radial',
											__( 'Radial Outside Inside', 'avia_framework' )		=> 'radial_rev'
										)
						),

					array(
							'name'		=> __( 'Gradient Color 1', 'avia_framework' ),
							'desc'		=> __( 'Select the first color for the gradient. Please select the first 2 colors, otherwise this option will be ignored.', 'avia_framework' ),
							'id'		=> $id_array[1],
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '#000000',
							'container_class' => $container_class[1],
							'lockable'	=> $lockable,
							'required'	=> $required
						),

					array(
							'name'		=> __( 'Gradient Color 2', 'avia_framework' ),
							'desc'		=> __( 'Select the second color for the gradient. Please select the first 2 colors, otherwise this option will be ignored.', 'avia_framework' ),
							'id'		=> $id_array[2],
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '#ffffff',
							'container_class' => $container_class[2],
							'lockable'	=> $lockable,
							'required'	=> $required
						),

					array(
							'name'		=> __( 'Gradient Color 3', 'avia_framework' ),
							'desc'		=> __( 'Select an optional third color for the gradient. Leave empty if not needed.', 'avia_framework' ),
							'id'		=> $id_array[3],
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => $container_class[3],
							'lockable'	=> $lockable,
							'required'	=> $required
						)
				);

			if( isset( $element['hover'] ) && true === $element['hover'] )
			{
				$template[] = array(
							'name'		=> __( 'Opacity For Background On Hover', 'avia_framework' ),
							'desc'		=> __( 'When using gradient colors it is only possible to select the opacity when you hover over the button. Background colors are not supported. This setting will override any other opacity settings.', 'avia_framework' ),
							'id'		=> $id . '_opacity',
							'type'		=> 'select',
							'std'		=> '0.7',
							'lockable'	=> $lockable,
							'required'	=> $required,
							'subtype'	=> \AviaHtmlHelper::number_array( 0.0, 1.0, 0.1 )
						);
			}

			return $template;
		}


		/**
		 * Button Effects Options toggle
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function effects_toggle( array $element )
		{
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$subtypes = isset( $element['subtypes'] ) ? $element['subtypes'] : array();
			$include = isset( $element['include'] ) ? $element['include'] : array();
			$ids = isset( $element['ids'] ) ? $element['ids'] : array();
			$names = isset( $element['names'] ) ? $element['names'] : array();
			$container_class = isset( $element['container_class'] ) ? $element['container_class'] : array();

			if( empty( $include ) )
			{
				$include = array( 'sonar_effect', 'hover_opacity' );
			}

			$c = array();

			if( in_array( 'hover_opacity', $include ) )
			{
				$hover = array(
							'type'			=> 'template',
							'template_id'	=> 'hover_opacity',
							'lockable'		=> $lockable,
							'required'		=> $required
						);

				if( isset( $ids['hover_opacity'] ) && ! empty( $ids['hover_opacity'] ) )
				{
					$hover['id'] = $ids['hover_opacity'];
				}

				if( isset( $names['hover_opacity'] ) && ! empty( $names['hover_opacity'] ) )
				{
					$hover['name'] = $names['hover_opacity'];
				}

				if( isset( $container_class['hover_opacity'] ) && ! empty( $container_class['hover_opacity'] ) )
				{
					$hover['container_class'] = $container_class['hover_opacity'];
				}

				$c[] = $hover;
			}

			if( in_array( 'sonar_effect', $include ) )
			{
				$sonar = array(
							'type'			=> 'template',
							'template_id'	=> 'sonar_effect',
							'lockable'		=> $lockable,
							'required'		=> $required,
							'subtypes'		=> $subtypes
						);

				if( isset( $ids['sonar_effect'] ) && ! empty( $ids['sonar_effect'] ) )
				{
					$sonar['id'] = $ids['sonar_effect'];
				}

				if( isset( $names['sonar_effect'] ) && ! empty( $names['sonar_effect'] ) )
				{
					$sonar['name'] = $names['sonar_effect'];
				}

				if( isset( $container_class['sonar_effect'] ) && ! empty( $container_class['sonar_effect'] ) )
				{
					$sonar['container_class'] = $container_class['sonar_effect'];
				}

				$c[] = $sonar;
			}

			if( empty( $c ) )
			{
				return array();
			}

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Effects', 'avia_framework' ),
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * Sonar Effect Options - Simple Styling
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function sonar_effect( array $element )
		{
			$id = isset( $element['id'] ) ? $element['id'] : 'sonar_effect';
			$name = isset( $element['name'] ) ? $element['name'] : __( 'Sonar/Pulsate Effect', 'avia_framework' );
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$subtypes = isset( $element['subtypes'] ) ? $element['subtypes'] : array();
			$container_class = isset( $element['container_class'] ) ? $element['container_class'] : array();

			if( ! is_array( $container_class ) || empty( $container_class ) )
			{
				$container_class = array( '', 'av_third av_third_first', 'av_third', 'av_third', 'av_third av_third_first' );
			}

			$subtype = array(
							__( 'No effect', 'avia_framework' )	=> '',
						);

			$shadow = array(
							__( 'Shadow only - permanent', 'avia_framework' )			=> 'shadow_permanent',
							__( 'Shadow only - on hover once', 'avia_framework' )		=> 'shadow_hover_once',
							__( 'Shadow only - on hover permanent', 'avia_framework' )	=> 'shadow_hover_perm',
						);

			$pulsate = array(
							__( 'Shadow and element  - permanent', 'avia_framework' )			=> 'pulsate_permanent',
							__( 'Shadow and element- on hover once', 'avia_framework' )			=> 'pulsate_hover_once',
							__( 'Shadow and element- on hover permanent', 'avia_framework' )	=> 'pulsate_hover_perm',
						);

			$element = array(
							__( 'Element only - permanent', 'avia_framework' )			=> 'element_permanent',
							__( 'Element only - on hover once', 'avia_framework' )		=> 'element_hover_once',
							__( 'Element only - on hover permanent', 'avia_framework' )	=> 'element_hover_perm',
						);

			if( empty( $subtypes ) )
			{
				$subtype = array_merge( $subtype, $shadow, $pulsate, $element );
			}
			else
			{
				if( in_array( 'shadow', $subtypes ) )
				{
					$subtype = array_merge( $subtype, $shadow );
				}

				if( in_array( 'pulsate', $subtypes ) )
				{
					$subtype = array_merge( $subtype, $pulsate );
				}

				if( in_array( 'element', $subtypes ) )
				{
					$subtype = array_merge( $subtype, $element );
				}
			}

			$template = array(

					array(
							'name'		=> $name,
							'desc'		=> __( 'Select a sonar/pulsate effect for the element. This effect might not always work as expected due to layout structure(e.g. for fullwidth elements). This is not a bug.', 'avia_framework' ),
							'id'		=> $id . '_effect',
							'type'		=> 'select',
							'std'		=> '',
							'container_class' => $container_class[0],
							'lockable'	=> $lockable,
							'required'	=> $required,
							'styles_cb'	=> array(
												'method'	=> 'sonar_effect',
												'id'		=> $id
											),
							'subtype'	=> $subtype
						),

						array(
							'name'		=> __( 'Sonar Shadow Color', 'avia_framework' ),
							'desc'		=> __( 'Select the color for the sonar shadow. Leave empty for theme default.', 'avia_framework' ),
							'id'		=> $id . '_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => $container_class[1],
							'lockable'	=> $lockable,
							'required'	=> array( $id . '_effect', 'not', '' )
						),

						array(
							'name'		=> __( 'Sonar effect duration', 'avia_framework' ),
							'desc'		=> __( 'Select approx. length of one effect, larger value slows down', 'avia_framework' ),
							'id'		=> $id . '_duration',
							'type'		=> 'select',
							'std'		=> '1',
							'container_class' => $container_class[2],
							'subtype'	=> \AviaHtmlHelper::number_array( 0.1, 10, 0.1, array( __( 'Theme default', 'avia_framework' ) => '' ) ),
							'required' 	=> array( $id . '_effect', 'not', '' ),
						),

						array(
							'name'		=> __( 'Expand Scale', 'avia_framework' ),
							'desc'		=> __( 'Select the expand value for the sonar effect', 'avia_framework' ),
							'id'		=> $id . '_scale',
							'type'		=> 'select',
							'std'		=> '',
							'container_class' => $container_class[3],
							'subtype'	=> \AviaHtmlHelper::number_array( 1.01, 2, 0.01, array( __( 'Theme default', 'avia_framework' ) => '' ) ),
							'required' 	=> array( $id . '_effect', 'not', '' ),
						),

						array(
							'name'		=> __( 'Element Opacity', 'avia_framework' ),
							'desc'		=> __( 'Select the opacity of the element when expanding', 'avia_framework' ),
							'id'		=> $id . '_opac',
							'type'		=> 'select',
							'std'		=> '0.5',
							'container_class' => $container_class[4],
							'subtype'	=> \AviaHtmlHelper::number_array( 0.1, 1, 0.1 ),
							'required' 	=> array( $id . '_effect', 'parent_in_array', 'pulsate_permanent,pulsate_hover_once,pulsate_hover_perm,element_permanent,element_hover_once,element_hover_perm' ),
						)
				);

			return $template;
		}



		/**
		 * SVG Divider Toggle
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function svg_divider_toggle( array $element )
		{
			$title = isset( $element['title'] ) ? $element['title'] : __( 'SVG Dividers', 'avia_framework' );
			$id = isset( $element['id'] ) ? $element['id'] : 'svg_div';

			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();


			$c = array(

						array(
								'type'			=> 'template',
								'template_id'	=> 'svg_divider',
								'id'			=> $id . '_top',
								'lockable'		=> $lockable,
								'location'		=> 'top'
							),

						array(
								'type'		=> 'hr',
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'svg_divider',
								'id'			=> $id . '_bottom',
								'lockable'		=> $lockable,
								'location'		=> 'bottom'
							),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> $title,
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * SVG Divider Options - Defines a single SVG divider
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function svg_divider( array $element )
		{
			$id = isset( $element['id'] ) ? $element['id'] : 'svg_div';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$location = isset( $element['location'] ) && in_array( $element['location'] , array( 'top', 'bottom' ) ) ? $element['location'] : 'bottom';

			if( 'top' == $location )
			{
				$name = __( 'Top SVG Divider', 'avia_framework' );
			}
			else
			{
				$name = __( 'Bottom SVG Divider', 'avia_framework' );
			}

			$template = array(

						array(
								'name'		=> $name,
								'desc'		=> __( 'Choose to use a svg divider here', 'avia_framework' ),
								'id'		=> $id ,
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'styles_cb'	=> array(
												'method'	=> 'svg_divider',
												'id'		=> $id,
												'location'	=> $location
											),
								'required'	=> $required,
								'subtype'	=> AviaSvgShapes()->modal_popup_select_dividers()
							),

						array(
								'name'		=> __( 'Divider Color', 'avia_framework' ),
								'desc'		=> __( 'Select the color for this divider here. Leave empty for theme default.', 'avia_framework' ),
								'id'		=> $id . '_color',
								'type'		=> 'colorpicker',
								'rgba'		=> true,
								'std'		=> '#333333',
								'container_class'	=> 'av_third av_third_first',
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'not', '' )
							),

						array(
								'name'		=> __( 'Divider Height', 'avia_framework' ),
								'desc'		=> __( 'Select the height of the divider', 'avia_framework' ),
								'id'		=> $id . '_height',
								'type'		=> 'select',
								'std'		=> '50',
								'container_class'	=> 'av_third',
								'subtype'	=> \AviaHtmlHelper::number_array( 0, 1000, 1, array(), 'px' ),
								'required'	=> array( $id, 'not', '' )
							),

						array(
								'name'		=> __( 'Divider Width', 'avia_framework' ),
								'desc'		=> __( 'Select the width of the divider.', 'avia_framework' ),
								'id'		=> $id . '_width',
								'type'		=> 'select',
								'std'		=> '100',
								'container_class'	=> 'av_third',
								'subtype'	=> \AviaHtmlHelper::number_array( 0, 500, 1, array(), '%' ),
								'required'	=> AviaSvgShapes()->modal_popup_required( 'width', $id )
							),

						array(
								'name'		=> __( 'Flip', 'avia_framework' ),
								'desc'		=> __( 'Check if you want to horizontal flip the divider.', 'avia_framework' ) ,
								'id'		=> $id . '_flip',
								'type'		=> 'checkbox',
								'std'		=> '',
								'container_class'	=> 'av_third av_third_first',
								'lockable'	=> $lockable,
								'required'	=> AviaSvgShapes()->modal_popup_required( 'flip', $id )
							),

						array(
								'name'		=> __( 'Invert', 'avia_framework' ),
								'desc'		=> __( 'Check if you want an inverted divider image.', 'avia_framework' ) ,
								'id'		=> $id . '_invert',
								'type'		=> 'checkbox',
								'std'		=> '',
								'container_class'	=> 'av_third',
								'lockable'	=> $lockable,
								'required'	=> AviaSvgShapes()->modal_popup_required( 'invert', $id )
							),

						array(
								'name'		=> __( 'Bring To Front', 'avia_framework' ),
								'desc'		=> __( 'Check if you want to bring the divider to front.', 'avia_framework' ) ,
								'id'		=> $id . '_front',
								'type'		=> 'checkbox',
								'std'		=> '',
								'container_class'	=> 'av_third',
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'not', '' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'opacity',
								'id'			=> $id . '_opacity',
								'container_class'	=> 'av_third  av_third_first',
								'lockable'		=> $lockable,
								'required'		=> array( $id, 'not', '' )
							),

						array(
								'name'		=> __( 'Hide Preview', 'avia_framework' ),
								'desc'		=> __( 'Check to hide the rough preview of the svg divider.', 'avia_framework' ) ,
								'id'		=> $id . '_preview',
								'type'		=> 'checkbox',
								'std'		=> '',
								'container_class'	=> 'av_third',
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'not', '' )
							),

						array(
								'name'		=> __( 'Preview Window', 'avia_framework' ),
								'id'		=> $id . '_window',
								'type'		=> 'divider_preview',
								'required'	=> array( $id . '_preview', 'equals', '' ),
								'base_id'	=> $id,
								'location'	=> $location
							)

				);

			return $template;
		}
	}
}
