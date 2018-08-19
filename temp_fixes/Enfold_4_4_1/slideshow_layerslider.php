<?php
/**
 * Advanced Layerslider
 * 
 * Display a Layerslider Slideshow
 * @modified 4.2.1, 4.2.7
 * @modified_by Günter
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! Avia_Config_LayerSlider()->is_active() )
{
	return;
}

if ( ! class_exists( 'avia_sc_layerslider' ) ) 
{
	class avia_sc_layerslider extends aviaShortcodeTemplate
	{		
			static $slide_count = 0;
			
			/**
			 * Create the config array for the shortcode button
			 */
			public function shortcode_insert_button()
			{
				$this->config['self_closing']			=	'yes';
				$class->config['forced_load_objects']	=	array( 'layerslider' );		//	fallback only to make sure we load in case user overwrites this class and direct checks for shortcode might fail
				
				$this->config['name']		= __('Advanced Layerslider', 'avia_framework' );
				$this->config['tab']		= __('Media Elements', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-slideshow-layer.png";
				$this->config['order']		= 10;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_layerslider';
				$this->config['tooltip'] 	= __('Display a Layerslider Slideshow', 'avia_framework' );
				$this->config['tinyMCE'] 	= array('disable' => "true");
				$this->config['drag-level'] = 1;
			}
			
			function extra_assets()
			{
				//load css
				wp_enqueue_style( 'avia-module-slideshow-ls' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/slideshow_layerslider/slideshow_layerslider.css' , array('avia-layout'), false );
				
					//load js
				wp_enqueue_script( 'avia-module-slideshow-ls' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/slideshow_layerslider/slideshow_layerslider.js' , array('avia-shortcodes'), false, TRUE );

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
			public function editor_element($params)
			{	
				//fetch all registered slides and save them to the slides array
				$slides = Avia_Config_LayerSlider()->find_layersliders( true );
				if(empty($params['args']['id']) && is_array($slides)) $params['args']['id'] = reset($slides);				
				if(empty($params['args']['id'])) $params['args']['id'] = "";				
				
				$element = array(
					'subtype' => $slides, 
					'type'=>'select', 
					'std' => $params['args']['id'], 
					'class' => 'avia-recalc-shortcode',
					'data'	=> array('attr'=>'id')
				);
				
				$inner		 = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
				
				
				if(empty($slides))
				{
					$inner.= "<div><a target='_blank' href='".admin_url( 'admin.php?page=layerslider' )."'>".__('No Layer Slider Found. Click here to create one','avia_framework' )."</a></div>";
				}
				else
				{
					$inner .= "<div class='avia-element-label'>".$this->config['name']."</div>";
					$inner .= AviaHtmlHelper::render_element($element);
					$inner .= "<a target='_blank' href='".admin_url( 'admin.php?page=layerslider' )."'>".__('Edit Layer Slider here','avia_framework' )."</a>";
				}
				
				$params['class'] = "av_sidebar";
				$params['innerHtml'] = $inner;
				
				return $params;
			}
			
			/**
			 * Frontend Shortcode Handler
			 *
			 * @param array $atts array of attributes
			 * @param string $content text within enclosing form of shortcode element 
			 * @param string $shortcodename the shortcode found, when == callback name
			 * @param array $meta
			 * @return string $output returns the modified html string 
			 */
			public function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = array() )
			{
				 $atts = shortcode_atts( array(
												'id'	=>	0
											), $atts, $this->config['shortcode'] );
				
				$output  = "";
				
				avia_sc_layerslider::$slide_count++;
				
				$skipSecond = false;
				$params = array();

				/**
				 * Get selected slider or last slider
				 */
				$slider = Avia_Config_LayerSlider()->get_default_slider( $atts['id'] );
				
				if( ! empty( $slider ) )
				{		
					$atts['id'] = $slider['id'];
					$slides = json_decode( $slider['data'], true );	
					$height = $slides['properties']['height'];	
					$width  = $slides['properties']['width'];	
					$responsive = ! empty( $slides['properties']['responsive'] ) ? $slides['properties']['responsive'] : '';
					$responsiveunder = ! empty( $slides['properties']['responsiveunder'] ) ? $slides['properties']['responsiveunder'] : '';

					
					if( ! empty( $slides['properties']['type'] ) && ( 'responsive' == $slides['properties']['type'] ) )
					{
						$params['style'] = " style='height: " . ($height+1) . "px; max-width: {$width}px; margin: 0 auto;' ";
					}
					else
					{
						$params['style'] = " style='height: " . ($height+1) . "px;' ";
					}
				}
				else
				{
					/**
					 * Force an error message in frontend for admins
					 */
					$atts['id'] = 0;
				}
				
				
				$params['class'] = "avia-layerslider main_color avia-shadow ".$meta['el_class'];
				$params['open_structure'] = false;
				
				//we dont need a closing structure if the element is the first one or if a previous fullwidth element was displayed before
				if(empty($meta['index'])) $params['close'] = false;
				if(!empty($meta['siblings']['prev']['tag']) && in_array($meta['siblings']['prev']['tag'], AviaBuilder::$full_el_no_section )) $params['close'] = false;
				
				if(!empty($meta['index'])) $params['class'] .= " slider-not-first";
				$params['id'] = "layer_slider_".( avia_sc_layerslider::$slide_count );
				
				
				$output .=  avia_new_section($params);
				
				if(class_exists('LS_Shortcode') && method_exists('LS_Shortcode', 'handleShortcode')) //fix for search results page - only works with latest LayerSlider versions
				{
					$output .= LS_Shortcode::handleShortcode( $atts );
				}
				else if(function_exists('layerslider_init')) //fix for search results page - only works with older LayerSlider versions
				{
					$output .= layerslider_init( $atts );
				}
				
				$output .= "</div>"; //close section
				
				
				//if the next tag is a section dont create a new section from this shortcode
				if(!empty($meta['siblings']['next']['tag']) && in_array($meta['siblings']['next']['tag'], AviaBuilder::$full_el ))
				{
				    $skipSecond = true;
				}

				//if there is no next element dont create a new section.
				if(empty($meta['siblings']['next']['tag']))
				{
				    $skipSecond = true;
				}
				
				if(empty($skipSecond)) {
				
				$output .= avia_new_section(array('close'=>false, 'id' => "after_layer_slider_".avia_sc_layerslider::$slide_count));
				
				}
				
				return $output;
			}
			
	}
}

