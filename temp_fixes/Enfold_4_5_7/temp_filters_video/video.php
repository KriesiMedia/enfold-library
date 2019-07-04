<?php
/**
 * Video
 * 
 * Shortcode which display a video
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_sc_video' ) ) 
{
	class avia_sc_video extends aviaShortcodeTemplate
	{
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['self_closing']	=	'yes';
				
				$this->config['name']			= __( 'Video', 'avia_framework' );
				$this->config['tab']			= __('Media Elements', 'avia_framework' );
				$this->config['icon']			= AviaBuilder::$path['imagesURL']."sc-video.png";
				$this->config['order']			= 90;
				$this->config['target']			= 'avia-target-insert';
				$this->config['shortcode'] 		= 'av_video';
//				$this->config['modal_data']     = array( 'modal_class' => 'mediumscreen' );
				$this->config['tooltip']        = __( 'Display a video', 'avia_framework' );
				$this->config['disabling_allowed'] = false; //only allowed to be disabled by extra options
				$this->config['disabled']		= array(
													'condition'	=> ( avia_get_option( 'disable_mediaelement' ) == 'disable_mediaelement' && avia_get_option( 'disable_video' ) == 'disable_video' ), 
													'text'		=> __( 'This element is disabled in your theme options. You can enable it in Enfold &raquo; Performance', 'avia_framework' )
												);
				$this->config['id_name']		= 'id';
				$this->config['id_show']		= 'yes';
			}
			
			
			function extra_assets()
			{
				//load css
				wp_enqueue_style( 'avia-module-video' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/video/video.css' , array('avia-layout'), false );
				wp_enqueue_script( 'avia-module-slideshow-video' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/slideshow/slideshow-video.js' , array('avia-shortcodes'), false, true );
				wp_enqueue_script( 'avia-module-video' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/video/video.js' , array('avia-shortcodes'), false, true );

			
			}
			
			
			/**
			 * Popup Elements
			 *
			 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
			 * opens a modal window that allows to edit the element properties
			 *
			 * @return void
			 */
			function popup_elements()
			{
				$text = "";
		
				//if the element is disabled
				if($this->config['disabled']['condition'] == true)
				{
					$this->elements = array(
						
						array(
								"name" 	=> __("Element disabled",'avia_framework' ),
								"desc" 	=> $this->config['disabled']['text'].
								'<br/><br/><a target="_blank" href="'.admin_url('admin.php?page=avia#goto_performance').'">'.__("Enable it here",'avia_framework' )."</a>",
								"type" 	=> "heading",
								"description_class" => "av-builder-note av-error",
								)
							);
					
					return;
				}
				//if self hosted is disabled
				else if(avia_get_option('disable_mediaelement') == 'disable_mediaelement')
				{
					$text = __("Please link to an external video by URL",'avia_framework' )."<br/><br/>".
							__("A list of all supported Video Services can be found on",'avia_framework' ).
							" <a target='_blank' href='http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F'>WordPress.org</a>. Youtube videos will display additional info like title, share link, related videos, ...<br/><br/>".
							__( 'Working examples:', 'avia_framework' ) . "<br/>" .
							"<strong>https://vimeo.com/1084537</strong><br/>" .
							"<strong>https://www.youtube.com/watch?v=G0k3kHtyoqc</strong><br/><br/>".
							"<strong class='av-builder-note'>" . __( 'Using self hosted videos is currently disabled. You can enable it in Enfold &raquo; Performance', 'avia_framework' ) . "</strong><br/>";

				}
				//if youtube/vimeo is disabled
				else if(avia_get_option('disable_video') == 'disable_video')
				{
					$text = __("Either upload a new video or choose an existing video from your media library",'avia_framework' )."<br/><br/>".
							__("Different Browsers support different file types (mp4, ogv, webm). If you embed an example.mp4 video the video player will automatically check if an example.ogv and example.webm video is available and display those versions in case its possible and necessary",'avia_framework' )."<br/><br/><strong class='av-builder-note'>".
							__("Using external services like Youtube or Vimeo is currently disabled. You can enable it in Enfold &raquo; Performance",'avia_framework' )."</strong><br/>";
							
				}
				//all video enabled
				else
				{
					$text = __("Either upload a new video, choose an existing video from your media library or link to a video by URL",'avia_framework' )."<br/><br/>".
										__("A list of all supported Video Services can be found on",'avia_framework' ).
										" <a target='_blank' href='http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F'>WordPress.org</a>. YouTube videos will display additional info like title, share link, related videos, ...<br/><br/>".
										__("Working examples, in case you want to use an external service:",'avia_framework' ). "<br/>".
										"<strong>https://vimeo.com/1084537</strong><br/>".
										"<strong>https://www.youtube.com/watch?v=G0k3kHtyoqc</strong><br/><br/>".
										"<strong>".__("Attention when using self hosted HTML 5 Videos",'avia_framework' ). ":</strong><br/>".
										__("Different Browsers support different file types (mp4, ogv, webm). If you embed an example.mp4 video the video player will automatically check if an example.ogv and example.webm video is available and display those versions in case its possible and necessary",'avia_framework' )."<br/>";
				}
				
				
				
				
				$this->elements = array(
					
					array(
						"type" 	=> "tab_container", 'nodescription' => true
					),
					
					array(
						"type" 	=> "tab",
						"name"  => __("Content" , 'avia_framework'),
						'nodescription' => true
					),
					
					array(	
							"name" 	=> __("Choose Video",'avia_framework' ),
							"desc" 	=> $text,
							"id" 	=> "src",
							"type" 	=> "video",
							"title" => __("Insert Video",'avia_framework' ),
							"button" => __("Insert",'avia_framework' ),
							"std" 	=> ""
						),
					
					
					array(	
									"name" 	=> __("Choose a preview/fallback image",'avia_framework' ),
									"desc" 	=> __("Either upload a new, or choose an existing image from your media library",'avia_framework' )."<br/><small>".__("Video on most mobile devices can't be controlled properly with JavaScript, so you can upload a fallback image which will be displayed instead. This image is also used if lazy loading is active.", 'avia_framework' ) ."</small>" ,
									"id" 	=> "mobile_image",
									"type" 	=> "image",
									"title" => __("Choose Image",'avia_framework' ),
									"button" => __("Choose Image",'avia_framework' ),
									"std" 	=> ""),
					
					array(	
							"name" 	=> __("Video Format", 'avia_framework' ),
							"desc" 	=> __("Choose if you want to display a modern 16:9 or classic 4:3 Video, or use a custom ratio", 'avia_framework' ),
							"id" 	=> "format",
							"type" 	=> "select",
							"std" 	=> "16:9",
							"subtype" => array( 
												__( '16:9',  'avia_framework' ) =>'16-9',
												__( '4:3', 'avia_framework' ) =>'4-3',
												__( 'Custom Ratio', 'avia_framework' ) =>'custom',
												)		
							),
							
					array(	
							"name" 	=> __("Video width", 'avia_framework' ),
							"desc" 	=> __("Enter a value for the width", 'avia_framework' ),
							"id" 	=> "width",
							"type" 	=> "input",
							"std" 	=> "16",
							"required" => array('format','equals','custom')
						),	
						
					array(	
							"name" 	=> __("Video height", 'avia_framework' ),
							"desc" 	=> __("Enter a value for the height", 'avia_framework' ),
							"id" 	=> "height",
							"type" 	=> "input",
							"std" 	=> "9",
							"required" => array('format','equals','custom')
						),
						
					array(
							"name" 	=> __("Lazy Load videos", 'avia_framework' ),
							"desc" 	=> __("Option to only load the preview image. The actual video will only be fetched once the user clicks on the image (Waiting for user interaction speeds up the inital pageload)", 'avia_framework' ),
							"id" 	=> "conditional_play",
							"type" 	=> "select",
							"std" 	=> "",
							"subtype" => array(
											__( 'Always load videos', 'avia_framework' )		=> '',
											__( 'Wait for user interaction to load the video', 'avia_framework' )	=> 'confirm_all'
										),
						),
		
														
					array(
							"type" 	=> "close_div",
							'nodescription' => true
						),
					
					
					array(	
							'type'			=> 'template',
							'template_id'	=> 'screen_options_tab'
						),

						
					array(
						"type" 	=> "close_div",
						'nodescription' => true
					),	
						
						
						);

                    if(current_theme_supports('avia_template_builder_custom_html5_video_urls'))
                    {
                        for ($i = 2; $i > 0; $i--)
                        {
                            $element = $this->elements[2];
                            $element['id'] = 'src_'.$i;
                            $element['name'] =  __("Choose Another Video (HTML5 Only)",'avia_framework');
                            $element['desc'] = __("Either upload a new video, choose an existing video from your media library or link to a video by URL.
                                                   If you want to make sure that all browser can display your video upload a mp4, an ogv and a webm version of your video.",'avia_framework' );

                            array_splice($this->elements, 3, 0, array($element));
                        }
                    }
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
			function editor_element($params)
			{
				$template = $this->update_template("src", "URL: {{src}}");
				$url = isset( $params['args']['src'] ) ? $params['args']['src'] : '';
				
				$params['content'] = null;
				$params['innerHtml'] = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
				$params['innerHtml'].= "<div class='avia-element-label'>".$this->config['name']."</div>";
				$params['innerHtml'].= "<div class='avia-element-url' {$template}> URL: ". $url ."</div>";
				$params['class'] = "avia-video-element";

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
				
				extract( AviaHelper::av_mobile_sizes( $atts ) ); //return $av_font_classes, $av_title_font_classes and $av_display_classes 
				
				extract( shortcode_atts( array(
							'src'				=> '', 
							'src_1'				=> '', 
							'src_2'				=> '', 
							'mobile_image'		=> '',
							'fallback_link'		=> '',
							'format'			=> '16:9', 
							'height'			=> '9', 
							'width'				=> '16',
							'conditional_play'	=> '',
							'attachment'		=> '',
							'attachment_size'	=> '',

					), $atts, $this->config['shortcode'] ) );
				
				$custom_class = ! empty( $meta['custom_class'] ) ? $meta['custom_class'] : '';
				$style = '';
				$html  = '';
				$fallback_img = "";
				$fallback_img_style = "";
				
				
				if( $attachment )
				{
					$fallback_img 	= wp_get_attachment_image_src( $attachment, $attachment_size );
					$fallback_img 	= $fallback_img[0];
					$style 			= "background-image:url(\"{$fallback_img}\");";
				}
				
                if( current_theme_supports( 'avia_template_builder_custom_html5_video_urls' ) )
                {
                    $sources = array();
                    if(!empty($src)) $sources['src'] = array('url' => $src, 'extension' => substr($src, strrpos($src, '.') + 1));
                    if(!empty($src_1)) $sources['src_1'] = array('url' => $src_1, 'extension' => substr($src_1, strrpos($src_1, '.') + 1));
                    if(!empty($src_2)) $sources['src_2'] = array('url' => $src_2, 'extension' => substr($src_2, strrpos($src_2, '.') + 1));

                    $html5 = false;
					$video_html_raw = '';

                    if( ! empty( $sources) )
                    {
                        foreach( $sources as $source )
                        {
                            if( in_array( $source['extension'], array( 'ogv','webm','mp4' ) ) ) //check for html 5 video
                            {
                                $html5 = true;
                            }
                            else
                            {
                                $video = $source['url'];
                                $html5 = false;
                                break;
                            }
                        }
                    }

                    if( $html5 && ! empty( $sources ) ) //check for html 5 video
                    {
	                    $poster = '';
	                    if($fallback_img) $poster = "poster='{$fallback_img}'";
	                    
                        $video = '';
                        foreach( $sources as $source )
                        {
                            $video .= $source['extension'].'="'.$source['url'].'" ';
                        }

						$video_html_raw = do_shortcode( '[video ' . $video  . $poster . ']' );
						
                        $output = $video_html_raw;
                        $html = "avia-video-html5";
                    }
                    else if( ! empty( $video ) )
                    {
                        global $wp_embed;
						
						$video_html_raw = $wp_embed->run_shortcode( "[embed]" . trim($src) . "[/embed]" );
                        $output = $video_html_raw;
                    }
                }
                else
                {
                    $file_extension = substr( $src, strrpos( $src, '.' ) + 1 );

                    if( in_array( $file_extension, array( 'ogv','webm','mp4' ) ) ) //check for html 5 video
                    {
						$video_html_raw = avia_html5_video_embed( $src, $fallback_img );
                        $output = $video_html_raw;
						
                        $html = "avia-video-html5";
                    }
                    else
                    {
                    	global $wp_embed;
						
						$video_html_raw = $wp_embed->run_shortcode( "[embed]" . trim($src) . "[/embed]" );
						$output = $video_html_raw;
                        
                        if( ! empty( $conditional_play ) )
                        {
	                        //append autoplay so the user does not need to click 2 times
	                        preg_match( '!src="(.*?)"!', $output, $match );
	                        if( isset($match[1] ) )
	                        {
		                    	if( strpos( $match[1], "?" ) === false )
		                    	{
			                    	$output = str_replace( $match[1], $match[1] . "?autoplay=1", $output );
		                    	}
		                    	else
		                    	{
			                    	$output = str_replace( $match[1], $match[1] . "&autoplay=1", $output );
		                    	}
	                        }
	                        
	                    } 
	                    else
	                    {
		                    $custom_class .= " av-lazyload-immediate ";
	                    }
	                    
	                       
						$output = "<script type='text/html' class='av-video-tmpl'>{$output}</script>";
                        $output .=	"<div class='av-click-to-play-overlay'>";
						$output .=  '<div class="avia_playpause_icon">';
						$output .=	'</div>';
						$output .=	'</div>';
						
						$custom_class .= " av-lazyload-video-embed ";
                        
                    }
                }
				
				if($format == 'custom')
				{
					$height = intval($height);
					$width  = intval($width);
					$ratio  = (100 / $width) * $height;
					$style .= "padding-bottom:{$ratio}%;";
				}
				
				if(!empty($style))
				{
					$style = "style='{$style}'";
				}
				
				if( ! empty( $output ) )
				{
                    $markup = avia_markup_helper(array('context' => 'video','echo'=>false, 'custom_markup'=>$meta['custom_markup']));
					$output = "<div class='avia-video avia-video-{$format} {$html} {$custom_class} {$av_display_classes}' {$style} {$markup} data-original_url='{$src}' >{$output}</div>";
				}
				
				
				/**
				 * Allow plugins to change output in case they want to handle it by themself.
				 * They must return the complete HTML structure.
				 * 
				 * @since 4.5.7.2
				 * @param string $output
				 * @param array $atts
				 * @param string $content
				 * @param string $shortcodename
				 * @param array|string $meta
				 * @param string $video_html_raw
				 * @return string
				 */
				$output = apply_filters( 'avf_sc_video_output', $output, $atts, $content, $shortcodename, $meta, $video_html_raw );
				
				return $output;
			}
			
			
	}
}
