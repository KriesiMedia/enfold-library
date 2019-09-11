<?php
/**
 * Code Block
 * 
 * Shortcode which creates a code element wrapped in a div - useful for text withour formatting like pre/code tags or scripts.
 * Also supports most ALB shortcodes (since 4.2.1) as long as they are not nested same named shortcodes.
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_sc_codeblock' ) )
{
    class avia_sc_codeblock extends aviaShortcodeTemplate
    {
		/**
		 * @since < 4.0
		 * @var int
		 */
        static public $codeblock_id = 0;
		
		/**
		 * @since < 4.0
		 * @var array 
		 */
        static public $codeblocks = array();
		
		/**
		 * @since < 4.0
		 * @var array 
		 */
		static public $shortcodes_executed = array();
        
        /**
         * Create the config array for the shortcode button
         */
        public function shortcode_insert_button()
        {
			$this->config['self_closing']	=	'no';
			
            $this->config['name']           = __( 'Code Block', 'avia_framework' );
            $this->config['tab']            = __( 'Content Elements', 'avia_framework' );
            $this->config['icon']           = AviaBuilder::$path['imagesURL']."sc-codeblock.png";
            $this->config['order']          = 1;
            $this->config['target']         = 'avia-target-insert';
            $this->config['shortcode']      = 'av_codeblock';
            $this->config['tinyMCE']        = array( 'disable' => true );
            $this->config['tooltip']        = __( 'Add text, shortcodes, HTML, CSS, JavaScript and non executeable codesnippets to your website (without any formatting or text optimization).', 'avia_framework' );
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
        }

        /**
         * Popup Elements
         *
         * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
         * opens a modal window that allows to edit the element properties
         *
         * @return void
         */
        public function popup_elements()
        {
            $this->elements =  array(
	            
	            array(
						"type" 	=> "tab_container", 'nodescription' => true
					),
					
				array(
						"type" 	=> "tab",
						"name"  => __("Content" , 'avia_framework'),
						'nodescription' => true),
	            
                array(
                    "name"  => __("Code Block Content. Add your own HTML/CSS/Javascript here", 'avia_framework'),
                    "desc"  => __("Enter some text/code/shortcode. You can also add plugin shortcodes here. Adding theme shortcodes is supported now for many elements. Be carefull not to nest same named shortcodes because this is not supported by WordPress ([foo] [foo]  [/foo] [/foo] will break layout !!! )", 'avia_framework'),
                    "id"    => "content",
					'container_class' =>"avia-element-fullwidth",
                    "type"  => "textarea",
                    "std"   => "",
                ),

                array(
                    "name"  => __("Code Wrapper Element", 'avia_framework' ),
                    "desc"  => __("Wrap your code into a html tag (i.e. pre or code tag). Insert the tag without <>", 'avia_framework' ) ,
                    "id"    => "wrapper_element",
                    "std"   => '',
                    "type"  => "input"),

                array(
                    "name"  => __("Code Wrapper Element Attributes", 'avia_framework' ),
                    "desc"  => __("Enter one or more attribute values which should be applied to the wrapper element. Leave the field empty if no attributes are required.", 'avia_framework' ) ,
                    "id"    => "wrapper_element_attributes",
                    "std"   => '',
                    "required" => array('wrapper_element', 'not', ''),
                    "type"  => "input"),
				
				array(	
					"name" 	=> __( "Action with codeblock", 'avia_framework' ),
					"desc" 	=> __( "Select if you want to execute codeblock or display it to the user only.", 'avia_framework' ),
					"id" 	=> "codeblock_type",
					"type" 	=> "select",
					"std" 	=> "",
					"subtype" => array(
								__( "Add codeblock to content", 'avia_framework' ) => '',
								__( "Display codeblock as code snippet", 'avia_framework' ) => 'snippet'
						)
					), 

                array(
                    "name"  => __("Escape HTML Code", 'avia_framework' ),
                    "desc"  => __("WordPress will convert the html tags to readable text.", 'avia_framework' ) ,
                    "id"    => "escape_html",
                    "std"   => false,
					"required" => array( 'codeblock_type', 'equals', '' ),
                    "type"  => "checkbox"),

                array(
                    "name"  => __("Disable Shortcode Processing", 'avia_framework' ),
                    "desc"  => __("Check if you want to disable the shortcode processing for this code block", 'avia_framework' ) ,
                    "id"    => "deactivate_shortcode",
                    "std"   => false,
					"required" => array('escape_html', 'equals', '' ),
                    "type"  => "checkbox"),

                array(
                    "name"  => __("Deactivate schema.org markup", 'avia_framework' ),
                    "desc"  => __("Output the code without any additional wrapper elements. (not recommended)", 'avia_framework' ) ,
                    "id"    => "deactivate_wrapper",
                    "std"   => false,
                    "type"  => "checkbox"),
                    
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
            $params['innerHtml'] = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
            $params['innerHtml'].= "<div class='avia-element-label'>".$this->config['name']."</div>";
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
        public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
        {
			/**
			 * Fix in case shortcode is called before extraction has taken place. Occurs if post content is
			 * preprocessed by 3rd party plugins e.g. in get_header().
			 * 
			 * @since 4.5.3
			 */
			if( empty( avia_sc_codeblock::$codeblocks ) || ( ( count( avia_sc_codeblock::$codeblocks ) - 1 ) < avia_sc_codeblock::$codeblock_id ) )
			{
				return '';
			}
			
	        extract( AviaHelper::av_mobile_sizes( $atts ) ); //return $av_font_classes, $av_title_font_classes and $av_display_classes 

            $custom_class = ! empty( $meta['custom_class'] ) ? $meta['custom_class'] : '';

            $atts = shortcode_atts( array(
							'wrapper_element'				=> '',
							'wrapper_element_attributes'	=> '',
							'codeblock_type'				=> '',
							'escape_html'					=> false,
							'deactivate_shortcode'			=> false,
							'deactivate_wrapper'			=> false,
							
					), $atts, $this->config['shortcode'] );

            $content = ' [avia_codeblock_placeholder uid="' . avia_sc_codeblock::$codeblock_id . '"] ';
			
			if( ! empty( $atts['codeblock_type'] ) && ( 'snippet' == $atts['codeblock_type'] ) )
			{
				$content = '<pre class="avia_codeblock-snippet"><code>' . trim( $content ) . '</code></pre>';
			}
			
            if(!empty($atts['wrapper_element'])) $content = "<{$atts['wrapper_element']} {$atts['wrapper_element_attributes']}>{$content}</{$atts['wrapper_element']}>";

            if(empty($atts['deactivate_wrapper']))
            {
                $output = '';
                $markup = avia_markup_helper(array('context' => 'entry', 'echo' => false, 'custom_markup'=>$meta['custom_markup']));
                $markup_text = avia_markup_helper(array('context' => 'entry_content', 'echo' => false, 'custom_markup'=>$meta['custom_markup']));

                $output .= '<section class="avia_codeblock_section '.$av_display_classes.' avia_code_block_' . avia_sc_codeblock::$codeblock_id . '" ' . $markup . $meta['custom_el_id'] . '>';
                $output .=		"<div class='avia_codeblock {$custom_class}' $markup_text>" . $content . "</div>";
                $output .= '</section>';
                $content = $output;
            }

			ShortcodeHelper::$shortcode_index += avia_sc_codeblock::$shortcodes_executed[ avia_sc_codeblock::$codeblock_id ];
			
            avia_sc_codeblock::$codeblock_id++;
            return $content;
        }
        
        public function extra_assets()
		{
			add_filter('avia_builder_precompile', array($this, 'code_block_extraction'), 10, 1);
    		add_filter('avf_template_builder_content', array($this, 'code_block_injection'), 10, 1);
		}
        
		/**
		 * Get all codeblocks from content, execute shortcodes inside the content (if requested) and save result into an array. 
		 * These results will replace placeholders created by the shortcode handler before rendering content to output 
		 * ( see avia_sc_codeblock::code_block_injection() ).
		 * 
		 * @param string $content
		 * @return string
		 */
		public function code_block_extraction($content)
		{	
			if ( strpos( $content, '[av_codeblock' ) === false ) 
			{
				return $content;
			}
			
			$pattern = '\[(\[?)(av_codeblock)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
			$matches = array();
			preg_match_all( '/' . $pattern . '/s', $content, $matches, PREG_OFFSET_CAPTURE );
			
			/**
			 * Fallback only, if something went wrong with shortcode structure
			 */
			if( ! is_array( $matches ) || empty( $matches ) || empty( $matches[0] ) )
			{
				return $content;
			}
			
				//	save value to be able to restore
			$old_direct_calls = ShortcodeHelper::$direct_calls;
			$old_is_direct_call = ShortcodeHelper::$is_direct_call;
			
			ShortcodeHelper::$is_direct_call = true;
			
			foreach( $matches[0] as $key => $data )
			{
				ShortcodeHelper::$direct_calls = 0;
				
				if( empty( $matches[5][ $key ] ) )
				{
					continue;
				}

				$codeblock = trim( $matches[5][ $key ][0] );

				if( ! empty( $matches[3][ $key ][0] ) )
				{
					$atts = shortcode_parse_atts( $matches[3][ $key ][0]);
					
					/**
					 * To keep our internal shortcode counter ShortcodeHelper::$shortcode_index synchronised with our shortcode tree 
					 * we need to execute the shortcodes inside the codeblock section
					 * 
					 * As our ALB shortcodes also support nesting this allows to increment our shortcode counter correctly even if user only wants to output
					 * the shortcodes inside the codeblock
					 */
					$sc_content = do_shortcode( $codeblock );
					
					if( isset( $atts['codeblock_type'] ) && ( 'snippet' == $atts['codeblock_type'] ) )
					{
						$codeblock = esc_html($codeblock);
					}
					else if( ! empty( $atts['escape_html'] ) )
					{
						/**
						 * esc_html breaks all shortcode syntax because '," are converted to hex value
						 */
						$codeblock = esc_html($codeblock);
						$codeblock = empty( $atts['wrapper_element'] ) ? nl2br( $codeblock ) : $codeblock;
					}
					else
					{
						$codeblock = empty( $atts['deactivate_shortcode'] ) ? $sc_content : $codeblock;
					}
				}

				self::$codeblocks[] = $codeblock;
				avia_sc_codeblock::$shortcodes_executed[] = ShortcodeHelper::$direct_calls;
			}
			
			/**
			 * Problem encountered with WP shortcode parser not recognising js code as content.
			 * We remove all content inside and insert our saved codeblocks later
			 * 
			 * @since 4.2.2
			 */
			for( $key = count( $matches[0] ) - 1; $key >= 0; $key-- )
			{
				if( empty( $matches[5][ $key ] ) )
				{
					continue;
				}
				
				$code_length = strlen( $matches[5][ $key ][0] );
				$code_start = $matches[5][ $key ][1];
				
				$content = substr_replace( $content, '', $code_start, $code_length );
			}

			ShortcodeHelper::$direct_calls = $old_direct_calls;
			ShortcodeHelper::$is_direct_call = $old_is_direct_call;
			
			return $content;
		}
	    
		/**
		 * Replaces the placeholders inserted by the shortcodehandler with the generated results from 
		 * avia_sc_codeblock::code_block_extraction.
		 * 
		 * @param string $content
		 * @return string
		 */
		public function code_block_injection( $content )
		{	
			if( empty( avia_sc_codeblock::$codeblocks ) ) 
			{
				return $content;
			}
			
			$pattern = '\[(\[?)(avia_codeblock_placeholder)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
			$matches = array();
			preg_match_all( '/' . $pattern . '/s', $content, $matches );
			
			if( ! empty( $matches) && is_array( $matches ) )
			{
			    foreach( $matches[0] as $key => $placeholder )
			    {
			        if( ! empty( $matches[3][ $key ] ) ) 
					{
						$atts = shortcode_parse_atts( $matches[3][ $key ] );
					}
					
			        $id = ! empty( $atts['uid'] ) ? $atts['uid'] : 0;
			
			        $codeblock = ! empty( self::$codeblocks[ $id ] ) ? self::$codeblocks[ $id ] : '';
			        $content = str_replace( $placeholder, $codeblock, $content );
			    }
			}
			
			return $content;
		}
	}
  }

