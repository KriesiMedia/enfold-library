<?php
/**
 * Fullwidth Revolution Slider
 * 
 * Display a fullwidth Revolution Slider
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_sc_revolutionslider' ) && function_exists( 'rev_slider_shortcode' ) )
{
	class avia_sc_revolutionslider extends aviaShortcodeTemplate
	{
			/**
			 *
			 * @var int 
			 */
			static $slide_count = 0;
			
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['self_closing']	=	'yes';
				
				$this->config['name']		= __( 'Fullwidth Revolution Slider', 'avia_framework' );
				$this->config['tab']		= __( 'Plugin Additions', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-slideshow-layer.png";
				$this->config['order']		= 10;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_revolutionslider';
				$this->config['tooltip'] 	= __( 'Display a fullwidth Revolution Slider', 'avia_framework' );
				$this->config['tinyMCE'] 	= array( 'disable' => "true" );
				$this->config['drag-level'] = 1;
				$this->config['custom_css_show']	= 'never';
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
				//fetch all registered slides and save them to the slides array

				$slider = new RevSlider();
				
				if( method_exists( $slider, 'get_sliders_short' ) )
				{
					$arrSliders = $slider->get_sliders_short();
				}
				else if( method_exists( $slider, 'getArrSlidersShort' ) )
				{
					//	fallback for < 6.0
					$arrSliders = $slider->getArrSlidersShort();
				}
				else
				{
					$arrSliders = array();
				}
					
				$slides = array_flip( $arrSliders );

				if( empty( $params['args']['id'] ) && is_array( $slides ) ) 
				{
					$params['args']['id'] = reset( $slides );
				}

				$element = array(
							'subtype'	=> $slides, 
							'type'		=>'select', 
							'std'		=> $params['args']['id'],
							'class'		=> 'avia-recalc-shortcode',
							'data'		=> array( 'attr' => 'id' )
						);
				
				
				$inner = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
				
				
				if( empty( $slides ) )
				{
					$inner.= "<div><a target='_blank' href='".admin_url( 'admin.php?page=revslider' )."'>".__('No Revolution Slider Found. Click here to create one','avia_framework' )."</a></div>";
				}
				else
				{
					$inner .= "<div class='avia-element-label'>".$this->config['name']."</div>";
					$inner .= AviaHtmlHelper::render_element( $element );
					$inner .= "<a target='_blank' href='".admin_url( 'admin.php?page=revslider' )."'>".__('Edit Revolution Slider here','avia_framework' )."</a>";
				}
				
				
				$params['class'] = "av_sidebar";
				$params['content']	 = null;
				$params['innerHtml'] = $inner;
				
				return $params;
			}
			
			/**
			 * Frontend Shortcode Handler
			 *
			 * @param array $atts array of attributes
			 * @param string $content text within enclosing form of shortcode element 
			 * @param string $shortcodename the shortcode found, when == callback name
			 * @return string $output returns the modified html string 
			 */
			function shortcode_handler( $atts, $content = "", $shortcodename = "", $meta = "" )
			{
				$output  = '';
				
				$skipSecond = false;
				avia_sc_revolutionslider::$slide_count++;
				
				//check if we got a Revolution Slider
				global $wpdb;

				// Table name
				$table_name = $wpdb->prefix . "revslider_sliders";

				// Get slider
				$slider = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = '".(int)$atts['id']."'", ARRAY_A );

				if( ! empty( $slider ) )
				{		
					$slides = json_decode( $slider['params'], true );
					
					//	fallback for < 6.0
					if( isset( $slides['height'] ) )
					{
						$height = is_numeric( $slides['height'] ) ? (int) $slides['height'] + 1 : 901;
					}
					else
					{
						//	provided by revslider support (https://kriesi.at/support/topic/revolution-slider-9/#post-1118183)
						$height = ( isset( $slides['size']['height']['d'] ) && is_numeric( $slides['size']['height']['d'] ) ) ? (int) $slides['size']['height']['d'] + 1 : false;
						if( false === $height )
						{
							$height = ( isset( $slides['size']['minHeight'] ) && is_numeric( $slides['size']['minHeight'] ) ) ? (int) $slides['size']['minHeight'] + 1 : 901;
						}
					}

					$params['style'] = " style='min-height: {$height}px;' ";
				}
				
				$params['class'] = "avia-layerslider avia-revolutionslider main_color avia-shadow {$meta['el_class']} ";
				$params['open_structure'] = false;
				
				//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
				if($meta['index'] == 0) $params['close'] = false;
				if(!empty($meta['siblings']['prev']['tag']) && in_array($meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section )) $params['close'] = false;
				
				if($meta['index'] > 0) $params['class'] .= " slider-not-first";
				$params['id'] = 'revolutionslider_' . avia_sc_revolutionslider::$slide_count;
				
				
				$output .= avia_new_section( $params );
				
				if( ! empty( $slider ) )
				{
					$alias = $slider['alias'];
					$output .= do_shortcode( '[rev_slider alias="' . $alias . '"][/rev_slider]' );
				}
				
				$output .= '</div>'; //close section
				
				
				//if the next tag is a section dont create a new section from this shortcode
				if(!empty($meta['siblings']['next']['tag']) && in_array($meta['siblings']['next']['tag'],  AviaBuilder::$full_el))
				{
				    $skipSecond = true;
				}

				//if there is no next element dont create a new section.
				if(empty($meta['siblings']['next']['tag']))
				{
				    $skipSecond = true;
				}
				
				if( empty( $skipSecond ) ) 
				{
					$output .= avia_new_section(array('close'=>false, 'id' => "after_revolutionslider_" . avia_sc_revolutionslider::$slide_count));
				}
				
				return $output;
			}
	
	}
}
