<?php
/**
 * Comments Element
 * 
 * Add a comment form and comments list to the template
 */
 
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }



if ( ! class_exists( 'avia_sc_comments_list' ) )
{
	class avia_sc_comments_list extends aviaShortcodeTemplate
	{		
		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['self_closing']	=	'yes';

			$this->config['name']			= __('Comments', 'avia_framework' );
			$this->config['tab']			= __('Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL']."sc-comments.png";
			$this->config['order']			= 5;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_comments_list';
			$this->config['tinyMCE']		= array('disable' => "true");
			$this->config['tooltip']		= __('Add a comment form and comments list to the template', 'avia_framework' );
			//$this->config['drag-level']	= 1;
			$this->config['disabling_allowed'] = "manually";
			$this->config['disabled']		= array(
										'condition'	=> ( avia_get_option( 'disable_blog' ) == 'disable_blog' ), 
										'text'		=> __( 'This element is disabled in your theme options. You can enable it in Enfold &raquo; Performance', 'avia_framework' )
											);
		}
			
			
		function extra_assets()
		{
			//load css
			wp_enqueue_style( 'avia-module-comments' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/comments/comments.css' , array('avia-layout'), false );
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
	            $this->elements = array(
					
					array(
							"type" 	=> "tab_container", 'nodescription' => true
						),
					 
									array(
										"type" 	=> "tab",
										"name"	=> __("Screen Options",'avia_framework' ),
										'nodescription' => true
									),
									
									
									array(
									"name" 	=> __("Element Visibility",'avia_framework' ),
									"desc" 	=> __("Set the visibility for this element, based on the device screensize.", 'avia_framework' ),
									"type" 	=> "heading",
									"description_class" => "av-builder-note av-neutral",
									),
								
									array(	
											"desc" 	=> __("Hide on large screens (wider than 990px - eg: Desktop)", 'avia_framework'),
											"id" 	=> "av-desktop-hide",
											"std" 	=> "",
											"container_class" => 'av-multi-checkbox',
											"type" 	=> "checkbox"),
									
									array(	
										
											"desc" 	=> __("Hide on medium sized screens (between 768px and 989px - eg: Tablet Landscape)", 'avia_framework'),
											"id" 	=> "av-medium-hide",
											"std" 	=> "",
											"container_class" => 'av-multi-checkbox',
											"type" 	=> "checkbox"),
											
									array(	
										
											"desc" 	=> __("Hide on small screens (between 480px and 767px - eg: Tablet Portrait)", 'avia_framework'),
											"id" 	=> "av-small-hide",
											"std" 	=> "",
											"container_class" => 'av-multi-checkbox',
											"type" 	=> "checkbox"),
											
									array(	
										
											"desc" 	=> __("Hide on very small screens (smaller than 479px - eg: Smartphone Portrait)", 'avia_framework'),
											"id" 	=> "av-mini-hide",
											"std" 	=> "",
											"container_class" => 'av-multi-checkbox',
											"type" 	=> "checkbox"),
		
									
								array(
										"type" 	=> "close_div",
										'nodescription' => true
									),	
									
									
							
							
						array(
							"type" 	=> "close_div",
							'nodescription' => true
						),	
				);
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
                $params['innerHtml'] = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
                $params['innerHtml'].= "<div class='avia-element-label'>".$this->config['name']."</div>";
                $params['content'] 	 = NULL; //remove to allow content elements
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
			function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = "")
			{
				global $post;
				
	        	extract(AviaHelper::av_mobile_sizes($atts)); //return $av_font_classes, $av_title_font_classes and $av_display_classes 
				
				$output = '';
				
				$need_moderation = get_option( 'comment_moderation', 0 );
				if( is_numeric( $need_moderation ) && ( 1 == (int) $need_moderation ) )
				{
					$comment_entries = get_comments( array( 'type' => 'comment', 'post_id' => $post->ID ) );
					
					$total = 0;
					$first = 0;
					
					foreach( $comment_entries as $index => $entry ) 
					{
						if( is_numeric( $entry->comment_approved ) && ( 0 === (int) $entry->comment_approved ) )
						{
							( 0 == $index ) ? $first ++ : $total ++;
						}
					}
					
					if( ( $first != 0 ) || ( $total != 0 ) )
					{
						if( ( $first != 0 ) && ( $total != 0 ) )
						{
							$info = sprintf( __( 'The last comment and %d other comment(s) need to be approved.', 'avia_framework' ), $total );
						}
						else if( $first != 0 )
						{
							$info = __( 'The last comment needs to be approved.', 'avia_framework' );
						}
						else
						{
							$info = sprintf( __( '%d comment(s) need to be approved.', 'avia_framework' ), $total );
						}
						
						$output .=	'<div class="av-buildercomment-unapproved">';
						$output .=		'<span>' . $info . '</span>';
						$output .=	'</div>';
					}
				}
				
                ob_start(); //start buffering the output instead of echoing it
                comments_template(); //wordpress function that loads the comments template "comments.php"
                $output .= ob_get_clean();
				$class  = "";
				
				if(function_exists('avia_blog_class_string'))
				{
					$class = avia_blog_class_string();
				}
				$output = "<div class='av-buildercomment {$class} {$av_display_classes}'>{$output}</div>";
				
        		return $output;
        	}

	}
}
