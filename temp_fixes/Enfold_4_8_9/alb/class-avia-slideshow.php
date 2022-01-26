<?php
/**
 * Helper for slideshows
 *
 * @since ???
 * @since 4.8.9		extended to support post css files
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_slideshow' ) )
{
	class avia_slideshow extends \aviaBuilder\base\aviaSubItemQueryBase
	{
		/**
		 *
		 * @var int
		 */
		static protected $slider = 0; 				//slider count for the current page

		/**
		 * attachment posts for the current slider
		 *
		 * @var array
		 */
		protected $slides;

		/**
		 *
		 * @since 4.8.9
		 * @var array
		 */
		protected $subslides;

		/**
		 * number of slides
		 *
		 * @var int
		 */
		protected $slide_count;

		/**
		 *
		 * @var array
		 */
		protected $id_array;

		/**
		 *
		 * @var boolean
		 */
		protected $need_conditional_load;

		/**
		 *
		 * @since 4.8.9						added $sc_context
		 * @param array $atts
		 * @param aviaShortcodeTemplate $sc_context
		 */
		public function __construct( $atts = array(), aviaShortcodeTemplate $sc_context = null )
		{
			parent::__construct( $atts, $sc_context, avia_slideshow::default_args() );

			$this->slides = array();
			$this->subslides = array();
			$this->slide_count = 0;
			$this->id_array = array();
			$this->need_conditional_load = false;

			/**
			 *
			 * @param array $this->config
			 * @return array
			 */
			$this->config = apply_filters( 'avf_slideshow_config', $this->config );

			/**
			 * Use this filter additionally
			 *
			 * @since 4.8.2
			 * @param string $image_size
			 * @param string $context
			 * @param array $atts
			 * @param string $content
			 * @return string
			 */
			$this->config['lightbox_size'] = apply_filters( 'avf_alb_lightbox_image_size', $this->config['lightbox_size'], 'avia_slideshow', $this->config, '' );

			//	reset to use default setting
			if( empty( $this->config['transition_speed'] ) || ! is_numeric( $this->config['transition_speed'] ) )
			{
				unset( $this->config['transition_speed'] );
			}

			$this->config['autoplay_stopper'] = 'aviaTBautoplay_stopper' == $this->config['autoplay_stopper'];

			$this->get_height();

			//if we got subslides overwrite the id array
			if( ! empty( $this->config['content'] ) )
			{
				$this->extract_subslides( $this->config['content'] );
			}

			$this->set_slides( $this->config['ids'] );
		}

		/**
		 * @since 4.4
		 */
		public function __destruct()
		{
			unset( $this->slides );
			unset( $this->subslides );
			unset( $this->id_array );

			parent::__destruct();
		}

		/**
		 * Returns the defaults array.
		 *
		 * ATTENTION: Backwards compatibilty different behaviour !!!
		 * ==========
		 *
		 * Allows shortcodes using this class to get the default values used before,
		 * merge them into shortcode generated defaults
		 *
		 * @since 4.8
		 * @param array
		 * @return array
		 */
		static public function default_args( array $sc_defaults = array() )
		{
			$default = array(
						'size'				=> 'featured',
						'lightbox_size'		=> 'large',
						'animation'			=> 'slide',
						'transition_speed'	=> '',				//	in ms - empty for default
						'conditional_play'	=> '',
						'ids'				=> '',
						'video_counter'		=> 0,
						'autoplay'			=> 'false',
						'bg_slider'			=> 'false',
						'slide_height'		=> '',
						'handle'			=> '',
						'interval'			=> 5,
						'class'				=> '',
						'extra_class'		=> '',				// additional container class that can be added before call to html()
						'el_id'				=> '',
						'css_id'			=> '',
						'scroll_down'		=> '',
						'control_layout'	=> '',
						'content'			=> array(),
						'custom_markup'		=> '',
						'perma_caption'		=> '',
						'autoplay_stopper'	=> '',
						'image_attachment'	=> '',
						'min_height'		=> '0px',
						'lazy_loading'		=> 'disabled',
						'default-height'	=> ''
				);

			/**
			 * Override the defaults from shortcode options for backwards comp.
			 */
			$default = array_merge( $sc_defaults, $default );

			/**
			 * @since 4.8.9
			 * @param array $default
			 * @return array
			 */
			return apply_filters( 'avf_avia_slideshow_defaults', $default );
		}

		/**
		 * Returns the argument array for a subslide
		 *
		 * Returns the
		 * @since 4.8.9
		 * @param int $key
		 * @return array
		 */
		public function args_item_advanced( $key )
		{
			$default = array(
							'content'				=> $this->subslides[ $key ]['content'],
							'title'					=> '',
							'link_apply'			=> '',
							//	direct link from image
							'link'					=> '',
							'link_target'			=> '',
							//	button link 1
							'button_label'			=> '',
							'button_color'			=> 'light',
							'link1'					=> '',
							'link_target1'			=> '',
							//	button link 2
							'button_label2'			=> '',
							'button_color2'			=> 'light',
							'link2'					=> '',
							'link_target2'			=> '',

							'position'				=> 'center center',
							'caption_pos'			=> 'capt-bottom capt-left',
							'video_cover'			=> '',
							'video_controls'		=> '',
							'video_mute'			=> '',
							'video_loop'			=> '',
							'video_format'			=> '',
							'video_autoplay'		=> '',
							'video_ratio'			=> '16:9',
							'video_mobile_disabled'	=> '',
							'video_mobile'			=> 'mobile-fallback-image',
							'mobile_image'			=> '',
							'fallback_link'			=> '',
							'slide_type'			=>'',
							'custom_markup'			=> '',
							'custom_title_size'		=> '',
							'custom_content_size'	=> '',
							'font_color'			=> '',
							'custom_title'			=> '',
							'custom_content'		=> '',
							'overlay_enable'		=> '',
							'overlay_opacity'		=> '',
							'overlay_color'			=> '',
							'overlay_pattern'		=> '',
							'overlay_custom_pattern'	=> '',
							'preload'				=> $this->need_conditional_load ? 'none' : ''
						);

			$default = array_merge( $default, $this->subslides[ $key ]['attr'] );

			/**
			 * @since 4.8.9
			 * @param array $default
			 * @return array
			 */
			return apply_filters( 'avf_avia_slideshow_item_advanced_defaults', $default );
		}

		/**
		 * Create custom stylings
		 *
		 * Attention: Due to paging we cannot add any backgrouund images to selectors !!!!
		 * =========
		 *
		 * @since 4.8.9
		 * @param array $result
		 * @return array
		 */
		public function get_element_styles( array $result )
		{
			extract( $result );

			$classes = array(
							'avia-slideshow',
							$element_id,
							$this->config['control_layout'],
							'avia-slideshow-' . $this->config['size'],
							$this->config['handle'],
							"avia-{$this->config['animation']}-slider"
						);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes( 'container', $this->config['class'] );

			$element_styling->add_responsive_classes( 'container', 'hide_element', $this->config );

			if( ! empty( $this->config['scroll_down'] ) )
			{
				$element_styling->add_classes( 'container', 'av-slider-scroll-down-active' );
			}

			if( ! empty( $this->config['conditional_play'] ) && $this->need_conditional_load )
			{
				$element_styling->add_classes( 'ul-inner', 'av-show-video-on-click' );
			}

			$selectors = array(
						'container'		=> ".avia-slideshow.{$element_id}",
						'ul-inner'		=> ".avia-slideshow.{$element_id} .avia-slideshow-inner"

					);

			$element_styling->add_selectors( $selectors );

			foreach( $this->id_array as $key => $id )
			{
				$result_item = array();
				$result_item['key'] = $key;
				$result_item['content'] = isset( $this->slides[ $id ] ) ? $this->slides[ $id ] : array();
				$result_item['element_id'] = $element_id . '__' . $key;
				$result_item['element_styling'] = new aviaElementStyling( $this->sc_context, $result_item['element_id'] );

				if( empty( $this->subslides ) )
				{
					$result_item = $this->get_element_styles_item_default( $result_item );
				}
				else
				{
					$result_item = $this->get_element_styles_item_advanced( $result_item );
				}

				$element_styling->add_subitem_styling( $result_item['element_id'], $result_item['element_styling'] );
			}

			//	save data for later HTML output
			$this->element_id = $element_id;
			$this->element_styles = $element_styling;

			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Create custom item stylings
		 * Items are called in object of main shortcode attributes
		 *
		 * @since 4.8.9
		 * @param array $result
		 * @return array
		 */
		protected function get_element_styles_item_default( array $result )
		{
			$result = $this->sc_context->get_element_styles_query_item( $result );

			extract( $result );

			$classes = array(
							'avia-slideshow-slide',
							$element_id
						);

			$element_styling->add_classes( 'container', $classes );

			$selectors = array(
						'container'			=> ".avia-slideshow .avia-slideshow-slide.{$element_id}"
					);

			$element_styling->add_selectors( $selectors );

			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Create custom item stylings
		 * Items are called in object of main shortcode attributes
		 *
		 * @since 4.8.9
		 * @param array $result
		 * @return array
		 */
		protected function get_element_styles_item_advanced( array $result )
		{
			$result = $this->sc_context->get_element_styles_query_item( $result );

			extract( $result );

			$atts = $this->args_item_advanced( $key );

			//	backwards comp.
			$atts['size-title'] = $atts['custom_title_size'];

			if( isset( $atts['custom_content_size'] ) )
			{
				$atts['size'] = $atts['custom_content_size'];
			}
			else
			{
				$atts['size'] = $atts['custom_size'];
			}

			$classes = array(
							'avia-slideshow-slide',
							$element_id
						);

			$element_styling->add_classes( 'container', $classes );

			$classes = array(
							'av-slideshow-caption',
							$element_id
						);

			$element_styling->add_classes( 'caption', $classes );

			$important = '';
			$obj_class = get_class( $this->sc_context );
			if( in_array( $obj_class, array( 'avia_sc_slider_fullscreen', 'avia_sc_slider_full' ) ) )
			{
				$important = '!important';
			}

			$element_styling->add_responsive_font_sizes( 'title', 'size-title', $atts, $this->sc_context, $important );
			$element_styling->add_responsive_font_sizes( 'content', 'size', $atts, $this->sc_context, $important );

			if( 'custom' == $atts['font_color'] )
			{
				$element_styling->add_styles( 'title', array( 'color' => $atts['custom_title'] ) );
				$element_styling->add_styles( 'content', array( 'color' => $atts['custom_content'] ) );
				$element_styling->add_classes( 'content', 'av_inherit_color' );
			}


			$selectors = array(
						'container'		=> ".avia-slideshow .avia-slideshow-slide.{$element_id}",
						'caption'		=> ".avia-slideshow .av-slideshow-caption.{$element_id}",
						'title'			=> "#top #wrap_all .avia-slideshow .av-slideshow-caption.{$element_id} .avia-caption-title",
						'content'		=> "#top .avia-slideshow .av-slideshow-caption.{$element_id} .avia-caption-content"
					);

			$element_styling->add_selectors( $selectors );

			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Check how large the slider is and change the classname accordingly
		 *
		 * @since 4.8.9
		 * @return void
		 */
		protected function get_height()
		{
			global $_wp_additional_image_sizes;

			$width = 1500;

			if( isset( $_wp_additional_image_sizes[ $this->config['size'] ]['width'] ) )
			{
				$width = $_wp_additional_image_sizes[ $this->config['size'] ]['width'];
				$height = $_wp_additional_image_sizes[ $this->config['size'] ]['height'];

				/**
				 * Avoid notices in case user manipulated image settings to 0
				 */
				$height = is_numeric( $height ) && $height > 0 ? $height : get_option( 'medium_size_h', 300 );
				$width = is_numeric( $width ) && $width > 0 ? $width : get_option( 'medium_size_w', $height );

				$this->config['default-height'] = ( 100 / $width ) * $height;
			}
			else if( get_option( $this->config['size'] . '_size_w' ) )
			{
				$width = get_option( $this->config['size'] . '_size_w' );
			}

			if( $width < 600 )
			{
				$this->config['class'] .= ' avia-small-width-slider';
			}

			if( $width < 305 )
			{
				$this->config['class'] .= ' avia-super-small-width-slider';
			}
		}

		/**
		 *
		 * @param string $ids
		 * @return void
		 */
		protected function set_slides( $ids )
		{
			$ids = trim( $ids );

			if( empty( $ids ) && empty( $this->config['video_counter'] ) )
			{
				return;
			}

			/**
			 * video slides have no id and return empty string - avoid an unnecessary db query if only video slides
			 */
			$post_ids = explode( ',', $ids );
			$post_ids = array_unique( $post_ids );
			if( ( 1 == count( $post_ids ) ) && empty( $post_ids[0] ) )
			{
				$post_ids = '';
			}
			else
			{
				$post_ids = implode( ',', $post_ids );
			}

			if( ! empty( $post_ids ) )
			{
				$this->slides = get_posts( array(
									'include'		=> $ids,
									'post_status'	=> 'inherit',
									'post_type'		=> 'attachment',
									'post_mime_type' => 'image',
									'order'			=> 'ASC',
									'orderby'		=> 'post__in'
								)
							);
			}
			else
			{
				$this->slides = array();
			}

			//resort slides so the id of each slide matches the post id
			$new_slides = array();
			foreach( $this->slides as $slide )
			{
				$new_slides[ $slide->ID ] = $slide;
			}

			$slideshow_data = array();
			$slideshow_data['slides'] = $new_slides;
			$slideshow_data['id_array'] = explode( ',', $this->config['ids'] );
			$slideshow_data['slide_count'] = count( array_filter( $slideshow_data['id_array'] ) ) + $this->config['video_counter'];

			/**
			 * @used_by				config-wpml\config.php				10
			 * @since 4.4.2
			 */
			$slideshow_data = apply_filters( 'avf_avia_builder_slideshow_filter', $slideshow_data, $this );

			$this->slides = $slideshow_data['slides'];
			$this->id_array = $slideshow_data['id_array'];
			$this->slide_count = $slideshow_data['slide_count'];
		}

		/**
		 *
		 * @deprecated since 4.8.9
		 * @param string $size
		 */
		public function set_size( $size )
		{
			_deprecated_function( 'avia_slideshow::set_size', '4.8.9', 'not uses - no replacement. Set in constructor.' );

			$this->config['size'] = $size;
		}

		/**
		 * Add a class that is added to container on html()
		 *
		 * @since 4.8.9					modified to 'extra_class'
		 * @param string $class
		 */
		public function set_extra_class( $class )
		{
			$this->config['extra_class'] .= ' ' . $class;
		}

		/**
		 * Create the HTML output
		 *
		 * @return string
		 */
		public function html()
		{
			avia_slideshow::$slider++;

			if( $this->slide_count == 0 )
			{
				return '';
			}

			$scroll_down_html = '';
			if( ! empty( $this->config['scroll_down'] ) )
			{
				$scroll_down_html .= "<a href='#next-section' title='' class='scroll-down-link " . $this->config['control_layout'] . "' " . av_icon_string( 'scrolldown' ) . "></a>";
			}

			$slide_html = empty( $this->subslides ) ? $this->html_default_slide() : $this->html_advanced_slide();

			//	must be set here because changed in html_advanced_slide()
			$style = '';
			if( ! empty( $this->config['default-height'] ) )
			{
				$style = "style='padding-bottom: {$this->config['default-height']}%;'";
				$this->element_styles->add_classes( 'container', 'av-default-height-applied' );
			}

			$this->element_styles->add_classes( 'container', $this->config['extra_class'] );

			$data = AviaHelper::create_data_string( $this->config );
            $markup = avia_markup_helper( array( 'context' => 'image', 'echo' => false, 'custom_markup' => $this->config['custom_markup'] ) );

			$style_tag = $this->element_styles->get_style_tag( $this->element_id );
			$container_class = $this->element_styles->get_class_string( 'container' );
			$inner_class = $this->element_styles->get_class_string( 'ul-inner' );

			$output  = '';
			$output .= $style_tag;
			$output .= $scroll_down_html;
			$output .= "<div {$this->config['el_id']} class='{$container_class} avia-slideshow-" . avia_slideshow::$slider . "' {$data} {$markup}>";

			$output .=		"<ul class='avia-slideshow-inner {$inner_class}' {$style}>";
			$output .=			$slide_html;
			$output .=		'</ul>';

			if( $this->slide_count > 1 )
			{
				$output .= $this->slide_navigation_arrows();
				$output .= $this->slide_navigation_dots();
			}

			if( ! empty( $this->config['caption_override'] ) )
			{
				$output .= $this->config['caption_override'];
			}

			$output .= '</div>';

			return $output;
		}

		/**
		 * renders the usual slides. use when we didnt use sub-shorcodes to define the images but ids
		 *
		 * @return string
		 */
		protected function html_default_slide()
		{
			$html = '';
			$counter = 0;

            $markup_url = avia_markup_helper( array( 'context' => 'image_url', 'echo' => false, 'custom_markup' => $this->config['custom_markup'] ) );

			foreach( $this->id_array as $key => $id )
			{
				if( isset( $this->slides[ $id ] ) )
				{
					$slide = $this->slides[ $id ];

					$counter ++;
					$img = wp_get_attachment_image_src( $slide->ID, $this->config['size'] );

					$lightbox_img_src = Av_Responsive_Images()->responsive_image_src( $slide->ID, $this->config['lightbox_size'] );

					$caption = trim( $slide->post_excerpt ) ? '<div class="avia-caption capt-bottom capt-left"><div class="avia-inner-caption">' . wptexturize( $slide->post_excerpt ) . "</div></div>": '';

                    $imgalt = get_post_meta( $slide->ID, '_wp_attachment_image_alt', true );
                    $imgalt = ! empty( $imgalt ) ? esc_attr( $imgalt ) : '';
                    $imgtitle = trim( $slide->post_title ) ? esc_attr( $slide->post_title ) : '';
                  	if( $imgtitle == '-' )
					{
						$imgtitle = '';
					}
                    $imgdescription = trim( $slide->post_content ) ? esc_attr( $slide->post_content ) : '';

					$lightbox_attr = Av_Responsive_Images()->html_attr_image_src( $lightbox_img_src, false );

					/**
					 * can be filtered and for example be replaced by array('div','div')
					 *
					 * @param array $tags
					 * @return array
					 */
					$tags = apply_filters( 'avf_slideshow_link_tags', array( "a {$lightbox_attr} title='{$imgdescription}'", 'a' ) );

					$img_tag = "<img src='{$img[0]}' width='{$img[1]}' height='{$img[2]}' title='{$imgtitle}' alt='{$imgalt}' {$markup_url} />";
					$img_tag = Av_Responsive_Images()->make_image_responsive( $img_tag, $slide->ID, $this->config['lazy_loading'] );

					//	add item container data
					$item_info = $this->element_styles->get_subitem_styling_info( $key );
					$container_class = $item_info['element_styling']->get_class_string( 'container' );

					$html .= "<li class='{$container_class} slide-{$counter} slide-id-{$slide->ID}'>";
					$html .=	"<{$tags[0]}>{$caption}{$img_tag}</{$tags[1]}>";
					$html .= '</li>';
				}
				else
				{
					$this->slide_count --;
				}
			}

			return $html;
		}

		/**
		 * Renders the slides. use when we did use sub-shortcodes to define the images
		 *
		 * @return string
		 */
		protected function html_advanced_slide()
		{
			$html = '';
			$counter = 0;
//			$this->ie8_fallback = '';

			foreach( $this->id_array as $key => $id )
			{
				$dev_tags = aviaShortcodeTemplate::set_frontend_developer_heading_tag( $this->subslides[ $key ]['attr'] );

				$meta = $this->args_item_advanced( $key );

				//	Autoplay videos must be muted to work on several browsers (e.g. FF, Chrome)
				if( empty( $meta['video_autoplay'] ) )
				{
					$meta['video_mute'] = 'aviaTBaviaTBvideo_mute';
				}

				extract( $meta );

				if( isset( $this->slides[ $id ] ) || 'video' == $slide_type )
				{
					$img = array( '' );
					$slide = '';
					$attachment_id = isset( $this->slides[ $id ] ) ? $id : false;
					$link = $slide_type == 'video' ? AviaHelper::get_url( $link, $attachment_id ) : AviaHelper::get_url( $link, $attachment_id, true );
					$extra_class = '';
					$linkdescription = '';
					$linkalt = '';
					$this->service = false;
					$slider_data = '';
					$stretch_height = false;
					$final_ratio = '';
					$viewport = 16/9;

					$fallback_img_style = '';
					$fallback_img_class = '';

					$markup_video = '';
            		$markup_url = avia_markup_helper( array( 'context' => 'image_url', 'echo' => false, 'id' => $attachment_id, 'custom_markup' => $custom_markup ) );

					if( $slide_type == 'video' )
					{
						$this->service = avia_slideshow_video_helper::which_video_service( $video );
						$video = avia_slideshow_video_helper::set_video_slide( $video, $this->service, $meta, $this->config, $markup_video );
						$video_class  = ! empty( $video_controls ) ? ' av-hide-video-controls' : '';
						$video_class .= ! empty( $video_mute ) ? ' av-mute-video' : '';
						$video_class .= ! empty( $video_loop ) ? ' av-loop-video' : '';
						$video_class .= ! empty( $video_mobile ) ? ' av-' . $video_mobile : '';

						$extra_class .= " av-video-slide {$video_cover} av-video-service-{$this->service} {$video_class} ";
						$slider_data .= " data-controls='{$video_controls}' data-mute='{$video_mute}' data-loop='{$video_loop}' data-disable-autoplay='{$video_autoplay}' ";

						if( $mobile_image )
						{
							$fallback_img = wp_get_attachment_image_src( $mobile_image, $this->config['size'] );

							if( is_array( $fallback_img ) )
							{
								$fallback_img_style = "style='background-image:url(\"{$fallback_img[0]}\");'";

								$slider_data .= " data-mobile-img='{$fallback_img[0]}'";

								if( $fallback_link )
								{
									$slider_data .= " data-fallback-link='" . esc_attr( $fallback_link ) . "'";
								}
							}
						}

						//if we dont use a fullscreen slider pass the video ratio to the slider
						if( $this->config['bg_slider'] != 'true' )
						{
							global $avia_config;

							//if we use the small slideshow only allow the 'full' $video_format
							if( $this->config['handle'] == 'av_slideshow' )
							{
								$video_format = 'full';
							}

							//calculate the viewport ratio
							if( ! empty( $avia_config['imgSize'][ $this->config['size'] ] ) )
							{
								$viewport = $avia_config['imgSize'][ $this->config['size'] ]['width'] / $avia_config['imgSize'][ $this->config['size'] ]['height'];
							}


							//calculate the ratio when passed as a string (eg: 16:9, 4:3). fallback is 16:9
							$video_ratio = explode( ':', trim( $video_ratio ) );
							if( empty( $video_ratio[0] ) )
							{
								$video_ratio[0] = 16;
							}
							if( empty( $video_ratio[1] ) )
							{
								$video_ratio[1] = 9;
							}

							$final_ratio = ( (int) $video_ratio[0] / (int) $video_ratio[1] );

							switch( $video_format )
							{
								case '':
									$final_ratio = $viewport;
								break;
								case 'stretch':
									$final_ratio 	 = $viewport;
									$stretch_height  = ceil( $viewport / ( $video_ratio[0] / $video_ratio[1] ) * 100 );
									$stretch_pos 	 = ( ( $stretch_height - 100 ) / 2 ) * -1;
									$slider_data 	.= " data-video-height='{$stretch_height}'";
									$slider_data 	.= " data-video-toppos='{$stretch_pos}'";
									$extra_class 	.= ' av-video-stretch';
								break;
								case 'full':
									// do nothing and apply the entered ratio
								break;
							}

							$slider_data .= " data-video-ratio='{$final_ratio}'";
						}

					}
					else //img slide
					{
						$slide = $this->slides[ $id ];
						$linktitle = trim( $slide->post_title ) ? esc_attr( $slide->post_title ) : '';
						if( $linktitle == '-' )
						{
							$linktitle = '';
						}

						$linkdescription = ( trim( $slide->post_content ) && empty( $link ) ) ? "title='" . esc_attr( $slide->post_content ) . "'" : '';
						$linkalt = get_post_meta( $slide->ID, '_wp_attachment_image_alt', true );
						$linkalt = ! empty( $linkalt ) ? esc_attr( $linkalt ) : '';
						$img = wp_get_attachment_image_src( $slide->ID, $this->config['size'] );
						$video = '';
					}

					if( $this->slide_count === 1 )
					{
						$extra_class .= ' av-single-slide';
					}

					$blank = AviaHelper::get_link_target( $link_target );

					$lightbox_attr = Av_Responsive_Images()->html_attr_image_src( $link, false );

					$tags = ( ! empty( $link ) && $link_apply == 'image' ) ? array( "a {$lightbox_attr} {$blank}", 'a' ) : array( 'div', 'div' );
					$caption = '';
					$button_html = '';
					$counter ++;

					$button_count = '';
					if( strpos( $link_apply, 'button-two' ) !== false )
					{
						$button_count = 'avia-multi-slideshow-button';
					}

					//if we got a CTA button apply the link to the button instead of the slide
					if( strpos( $link_apply, 'button' ) !== false )
					{
						$button_html .= $this->slideshow_cta_button( $link1, $link_target1, $button_color, $button_label, $button_count );
						$tags = array( 'div', 'div' );
					}

					if( strpos( $link_apply, 'button-two' ) !== false )
					{
						$button_count .= ' avia-slideshow-button-2';
						$button_html .= $this->slideshow_cta_button( $link2, $link_target2, $button_color2, $button_label2, $button_count );
					}

					//	add item container data
					$item_info = $this->element_styles->get_subitem_styling_info( $key );
					$container_class = $item_info['element_styling']->get_class_string( 'container' );
					$caption_class = $item_info['element_styling']->get_class_string( 'caption' );
					$content_class = $item_info['element_styling']->get_class_string( 'content' );

					//check if we got a caption
                    $markup_description = avia_markup_helper( array( 'context' => 'description', 'echo' => false, 'id' => $attachment_id, 'custom_markup' => $custom_markup ) );
                    $markup_name = avia_markup_helper( array( 'context' => 'name', 'echo' => false, 'id' => $attachment_id, 'custom_markup' => $custom_markup ) );

					if( trim( $title ) != '' )
					{
						$default_heading = ! empty( $dev_tags['heading_tag'] ) ? $dev_tags['heading_tag'] : 'h2';
						$args = array(
									'heading'		=> $default_heading,
									'extra_class'	=> $dev_tags['heading_class']
								);

						$extra_args = array( $this, $key );

						/**
						 * @since 4.5.5
						 * @return array
						 */
						$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );

						$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
						$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : $dev_tags['heading_class'];

						$title = "<{$heading} class='avia-caption-title {$css}' {$markup_name}>" . trim( apply_filters( 'avf_slideshow_title', $title ) ) . "</{$heading}>";
					}

					if( is_array( $content ) )
					{
						$content = implode( ' ', $content ); //temp fix for trim() expects string warning until I can actually reproduce the problem
					}

					if( trim( $content ) != '' )
					{
						$content 	= "<div class='avia-caption-content {$content_class}' {$markup_description}>" . ShortcodeHelper::avia_apply_autop( ShortcodeHelper::avia_remove_autop( trim( $content ) ) ) . '</div>';
					}

					if( trim( $title . $content . $button_html ) != '' )
					{
						if( trim( $title ) != '' && trim( $button_html ) != '' && trim( $content ) == '' )
						{
							$content = '<br/>';
						}

						if( $this->config['handle'] == 'av_slideshow_full' || $this->config['handle'] == 'av_fullscreen' )
						{
							$caption .= "<div class='{$caption_class} caption_fullwidth {$caption_pos}'>";
							$caption .= 	'<div class="container caption_container">';
							$caption .= 			'<div class="slideshow_caption">';
							$caption .= 				'<div class="slideshow_inner_caption">';
							$caption .= 					'<div class="slideshow_align_caption">';
							$caption .=							$title;
							$caption .=							$content;
							$caption .=							$button_html;
							$caption .= 					'</div>';
							$caption .= 				'</div>';
							$caption .= 			'</div>';
							$caption .= 	'</div>';
							$caption .= '</div>';
						}
						else
						{
							$caption  = "<div class='{$caption_class} avia-caption'>";
							$caption .=		'<div class="avia-inner-caption">';
							$caption .=			$title;
							$caption .=			$content;
							$caption .=		'</div>';
							$caption .= '</div>';

						}
					}

					if( ! empty( $this->config['perma_caption'] ) && empty( $this->config['caption_override'] ) )
					{
						$this->config['caption_override'] = $caption;
					}

                   	if( ! empty( $this->config['caption_override'] ) )
					{
						$caption = '';
					}

					if( ! empty( $img[0] ) )
					{
						$slider_data .= $this->config['bg_slider'] == 'true' ? "style='background-position:{$position};' data-img-url='{$img[0]}'" : '';

//						if( $slider_data )
//						{
//							if( empty( $this->ie8_fallback ) )
//							{
//						    	$this->ie8_fallback .= "<!--[if lte IE 8]>";
//								$this->ie8_fallback .= "<style type='text/css'>";
//							}
//							$this->ie8_fallback .= "\n #{$this->config['css_id']} .slide-{$counter}{";
//							$this->ie8_fallback .= "\n -ms-filter: \"progid:DXImageTransform.Microsoft.AlphaImageLoader(src='{$img[0]}', sizingMethod='scale')\"; ";
//						    $this->ie8_fallback .= "\n filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='{$img[0]}', sizingMethod='scale'); ";
//							$this->ie8_fallback .= "\n } \n";
//						}
					}

					// $img[0] = 'https://kriesi.at/themes/enfold-photography/files/2014/08/darkened_girl.jpg';


					$html .= "<li {$slider_data} class='{$container_class} {$extra_class} slide-{$counter} ' >";
					$html .=	"<{$tags[0]} data-rel='slideshow-" . avia_slideshow::$slider . "' class='avia-slide-wrap {$fallback_img_class}' {$fallback_img_style} {$linkdescription} {$markup_video}>{$caption}";

					if( $this->config['bg_slider'] != 'true' && empty( $video ) )
					{
						$img_style = '';
						if( ! empty( $this->config['min_height'] ) && $this->config['min_height'] != '0px' )
						{
							$percent = 100 / ( 100 / $img[2] * (int) $this->config['min_height'] );
							$this->config['min_width'] = ceil( ( $img[1] / $percent ) ) . 'px';

							$img_style .= AviaHelper::style_string( $this->config, 'min_height', 'min-height' );
							$img_style .= AviaHelper::style_string( $this->config, 'min_width', 'min-width' );
							$img_style  = AviaHelper::style_string( $img_style );
						}

						$img_tag = "<img src='{$img[0]}' width='{$img[1]}' height='{$img[2]}' title='{$linktitle}' alt='{$linkalt}' {$markup_url} {$img_style} />";
						$img_tag = Av_Responsive_Images()->make_image_responsive( $img_tag, $slide->ID, $this->config['lazy_loading'] );

						$html .= $img_tag;
					}

					$html .= $video;
					$html .= $this->create_overlay( $meta );
					$html .= $this->create_click_to_play_overlay();

					$html .=	'</' . $tags[1]. '>';
					$html .= '</li>';

					if( $counter === 1 )
					{
						if( ! empty( $img[1] ) && ! empty( $img[2] ) )
						{
							$this->config['default-height'] = ( 100 / $img[1] ) * $img[2];
						}
					}
				}
				else
				{
					$this->slide_count --;
				}
			}

//			if( ! empty( $this->ie8_fallback ) )
//			{
//				$this->ie8_fallback .= '</style> <![endif]-->';
//				add_action( 'wp_footer', array( $this, 'add_ie8_fallback_to_footer' ) );
//			}

			return $html;
		}

//		public function add_ie8_fallback_to_footer()
//		{
//			// echo $this->ie8_fallback;
//		}

		/**
		 * Action button link (currently added to fullscreen slideshows, not linking to lightbox)
		 *
		 * @since ??????
		 * @param string $link
		 * @param string $link_target
		 * @param string $button_color
		 * @param string $button_label
		 * @param string $button_count
		 * @return string
		 */
		protected function slideshow_cta_button( $link, $link_target, $button_color, $button_label, $button_count )
		{
			$button_html = '';

			$link = AviaHelper::get_url( $link );
			$blank = AviaHelper::get_link_target( $link_target );

			$button_html .= "<a href='{$link}' {$blank} class='avia-slideshow-button avia-button avia-color-{$button_color} {$button_count}' data-duration='800' data-easing='easeInOutQuad'>";
			$button_html .=		$button_label;
			$button_html .= '</a>';

			return $button_html;
		}

		/**
		 * Create arrows to scroll slides
		 *
		 * @since 4.8.3			reroute to aviaFrontTemplates
		 * @return string
		 */
		protected function slide_navigation_arrows()
		{
			$args = array(
						'context'	=> get_class(),
						'params'	=> $this
					);

			return aviaFrontTemplates::slide_navigation_arrows( $args );
		}

		/**
		 *
		 * @return string
		 */
		protected function slide_navigation_dots()
		{
			$args = array(
						'total_entries'		=> $this->slide_count,
						'container_entries'	=> 1,
						'context'			=> get_class(),
						'params'			=> $this
					);


			return aviaFrontTemplates::slide_navigation_dots( $args );
		}

		/**
		 *
		 * @param array $slide_array
		 */
		protected function extract_subslides( array $slide_array )
		{
			$this->config['ids'] = array();
			$this->subslides = array();

			foreach( $slide_array as $key => $slide )
			{
				$this->subslides[ $key ] = $slide;
				$this->config['ids'][] = $slide['attr']['id'];

				if( empty( $slide['attr']['id'] ) && ! empty( $slide['attr']['video'] ) && $slide['attr']['slide_type'] === 'video' )
				{
					$this->config['video_counter'] ++ ;

					if( avia_slideshow_video_helper::is_extern_service( $slide['attr']['video'] ) )
					{
						$this->need_conditional_load = true;
					}
					else
					{
						if( ! $this->need_conditional_load )
						{
							/**
							 * Allow to change default behaviour to lazy load all video files
							 *
							 * @since 4.4
							 */
							$this->need_conditional_load = apply_filters( 'avf_video_slide_conditional_load_html5', true, $slide_array, $this );
						}
					}
				}
			}

			$this->config['ids'] = implode( ',', $this->config['ids'] );
			unset( $this->config['content'] );
		}

		/**
		 *
		 * @param array $meta
		 * @return string
		 */
		protected function create_overlay( array $meta )
		{
			extract( $meta );

			/*check/create overlay*/
			$overlay = '';

			if( ! empty( $overlay_enable ) )
			{
				$overlay_src = '';
				$overlay = "opacity: {$overlay_opacity}; ";
				if( ! empty( $overlay_color ) )
				{
					$overlay .= "background-color: {$overlay_color}; ";
				}

				if( ! empty( $overlay_pattern ) )
				{
					if( $overlay_pattern == 'custom' )
					{
						$overlay_src = $overlay_custom_pattern;
					}
					else
					{
						$overlay_src = str_replace('{{AVIA_BASE_URL}}', AVIA_BASE_URL, $overlay_pattern );
					}
				}

				if( ! empty( $overlay_src ) )
				{
					$overlay .= "background-image: url({$overlay_src}); background-repeat: repeat;";
				}

				$overlay = "<div class='av-section-color-overlay' style='{$overlay}'></div>";
			}

			return $overlay;
		}

		/**
		 * Returns an overlay div if we need late loading of videos
		 *
		 * @since 4.4
		 * @return string
		 */
		protected function create_click_to_play_overlay()
		{
			if( ! $this->need_conditional_load )
			{
				return '';
			}


			$overlay =	'<div class="av-click-to-play-overlay">';
			$overlay .=		'<div class="avia_playpause_icon">';
			$overlay .=		'</div>';
			$overlay .=	'</div>';

			return $overlay;
		}

	}
}


if( ! class_exists( 'avia_slideshow_video_helper' ) )
{
	class avia_slideshow_video_helper
	{

		/**
		 * Define extern services that need to be confirmed by user
		 *
		 * @var array
		 */
		static protected $extern_services = array( 'youtube', 'vimeo' );

		/**
		 *
		 * @param string $video_url
		 * @param string $service
		 * @param array|false $meta
		 * @param array|false $config
		 * @param string $markup_video				added 4.8.7  returns markup if not set in HTML
		 * @return string
		 */
		static public function set_video_slide( $video_url, $service = false, $meta = false, $config = false, &$markup_video = '' )
		{
			$video = '';
			$origin_url = $video_url;

			if( empty( $service ) )
			{
				$service = self::which_video_service( $video_url );
			}

			$uid = 'player_' . get_the_ID() . '_' . mt_rand() . '_' . mt_rand();
			$controls = empty( $meta['video_controls'] ) ? 1 : 0;
			$atts = array();
			$atts['loop'] = empty( $meta['video_loop'] ) ? 0 : 1;
			$atts['autoplay'] = empty( $meta['video_autoplay'] ) ? 1 : 0;
			$atts['muted'] = empty( $meta['video_mute'] ) ? 0 : 1;
			$atts['controls'] = $controls;

			//was previously only used for mobile,now for everything
			$fallback_img = ! empty( $meta['mobile_image'] ) ? $meta['mobile_image'] : '';

			if( is_numeric( $fallback_img ) )
			{
				$fallback_size = isset( $config['size'] ) ? $config['size'] : 'thumbnail';
				$fallback_img = wp_get_attachment_image_src( $fallback_img, $fallback_size );
				$fallback_img = ( is_array( $fallback_img ) ) ? $fallback_img[0] : '';
			}

			$markup_video = avia_markup_helper( array( 'context' => 'video', 'echo' => false, 'custom_markup' => ( isset( $meta['custom_markup'] ) ? $meta['custom_markup'] : '' ) ) );

			switch( $service )
			{
				case 'html5':
					$types = array( 'webm' => 'type="video/webm"', 'mp4' => 'type="video/mp4"', 'ogv' => 'type="video/ogg"' );
					$video = "<div class='av-click-overlay'></div>" . avia_html5_video_embed( $video_url,  $fallback_img, $types, $atts );
					break;
				case 'iframe':
					$video = $video_url;
					break;
				case 'youtube':
					$explode_at = strpos( $video_url, 'youtu.be/' ) !== false ? '/' : 'v=';
					$video_url = explode( $explode_at, trim( $video_url ) );
					$video_url = end( $video_url );
					$video_id = $video_url;

					//if parameters are appended make sure to create the correct video id
					if( strpos( $video_url, '?' ) !== false || strpos( $video_url, '?' ) !== false )
					{
					    preg_match( '!(.+)[&?]!', $video_url, $video_id );
						$video_id = isset( $video_id[1] ) ? $video_id[1] : $video_id[0];
					}

					$video_data = apply_filters( 'avf_youtube_video_data', array(
							'autoplay' 		=> 0,
							'videoid'		=> $video_id,
							'hd'			=> 1,
							'rel'			=> 0,
							'wmode'			=> 'opaque',
							'loop'			=> 0,
							'version'		=> 3,
							'autohide'		=> 1,
							'color'			=> 'white',
							'controls'		=> $controls,
							'iv_load_policy'=> 3
						));

					$data = AviaHelper::create_data_string( $video_data );

					$video = "<div class='av-click-overlay'></div><div class='mejs-mediaelement' {$markup_video}><div height='1600' width='900' class='av_youtube_frame' id='{$uid}' {$data} data-original_url='{$origin_url}' ></div></div>";
					$markup_video = '';
					break;
				case 'vimeo':
					$color = ltrim( avia_get_option('colorset-main_color-primary'), '#');
					$autopause = empty( $meta['video_section_bg'] ) ? 1 : 0; //pause if another vimeo video plays?
					$video_url = explode( '/', trim( $video_url ) );
					$video_url = end( $video_url );
					$video_url = esc_url( add_query_arg(
						array(
							'portrait' 	=> 0,
							'byline'	=> 0,
							'title'		=> 0,
							'badge'		=> 0,
							'loop'		=> $atts['loop'],
							'autopause'	=> $autopause,
							'api'		=> 1,
							'rel'		=> 0,
							'player_id'	=> $uid,
							'color'		=> $color
							),
						'//player.vimeo.com/video/' . $video_url
						));

					$video_url = apply_filters( 'avf_vimeo_video_url' , $video_url );
					$video = "<div class='av-click-overlay'></div><div class='mejs-mediaelement' {$markup_video}><div data-src='{$video_url}' data-original_url='{$origin_url}' height='1600' width='900' class='av_vimeo_frame' id='{$uid}'></div></div>";
					$markup_video = '';
					break;
			}

			return $video;
		}

		/**
		 * get the video service based on the url string fo the video
		 *
		 * @param strint $video_url
		 * @return string
		 */
		static public function which_video_service( $video_url )
		{
			$service = '';

			if( avia_backend_is_file( $video_url, 'html5video' ) )
			{
				$service = 'html5';
			}
			else if( strpos( $video_url, '<iframe' ) !== false )
			{
				$service = 'iframe';
			}
			else
			{
				if( strpos( $video_url, 'youtube.com/watch' ) !== false || strpos( $video_url, 'youtu.be/' ) !== false )
				{
					$service = 'youtube';
				}
				else if( strpos( $video_url, 'vimeo.com' ) !== false )
				{
					$service = 'vimeo';
				}
			}

			return $service;
		}

		/**
		 * Checks, if the video is an external service
		 *
		 * @since 4.4
		 * @param string $video_url
		 * @return boolean
		 */
		static public function is_extern_service( $video_url )
		{
			$ervice = avia_slideshow_video_helper::which_video_service( $video_url );

			return in_array( $ervice, avia_slideshow_video_helper::$extern_services );
		}
	}
}
