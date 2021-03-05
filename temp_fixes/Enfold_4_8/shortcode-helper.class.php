<?php
/**
* ShortcodeHelper class that holds information on allowed shortcodes, formating functions etc
*/

// Don't load directly
if ( ! defined('ABSPATH') ) { die('-1'); }

if ( ! class_exists( 'ShortcodeHelper' ) ) {
	
	class ShortcodeHelper
	{
	
        static $allowed_shortcodes = array();
        static $manually_allowed_shortcodes = array();
        static $manually_disallowed_shortcodes = array();
		static $nested_shortcodes = array();
        static $pattern = "";
        static $tree = array();
        static $shortcode_index = 0; //tells us which index the currently rendered shortcode has
		
		/**
		 * Stores the post that had been base for the tree. In loops of posts we have to make sure we have the appropriate tree loaded for the post.
		 * Otherwise this can result in $meta['index'] undefined
		 *  
		 * @since 4.5.5
		 * @var WP_Post|null
		 */
		static public $current_post_in_tree = null;

        /**
		 *
		 * @var int 
		 */
		static public $direct_calls = 0;			//	adds direct calls to theme shortcodes (e.g. from codeblocks to update $shortcode_index correctly)
        
		/**
		 *
		 * @var boolean 
		 */
		static public $is_direct_call = false;		//	set to true if shortcodes are eecuted inside elements and removed from final code to execute like in codeblocks (shortcode tree is incorrect in that case) 
        
		
		/**
		 * Converts a shortcode string into an array. Allows nested shortcodes.
		 * Make sure that content is unslashed ( call wp_unslash() )
		 * 
		 * @param string $content		
		 * @param int $depth
		 * @return array
		 */
        static public function shortcode2array( $content, $depth = 1000 )
        {	
        	$pattern = empty( ShortcodeHelper::$pattern) ? ShortcodeHelper::build_pattern() : ShortcodeHelper::$pattern;
        	$depth --;

			$matches = array();
        	preg_match_all( "/$pattern/s", $content , $matches );
        	
        	$return = array();
			
        	foreach( $matches[3] as $key => $match )
        	{
        		$return[ $key ]['shortcode'] = $matches[2][ $key ];
        		$return[ $key ]['attr'] = shortcode_parse_atts( $match ); 
				$return[ $key ]['raw_content'] = $matches[5][ $key ];
        		
        		if( preg_match( "/$pattern/s", $matches[5][ $key ] ) && $depth )
        		{
        			$return[ $key ]['content'] = self::shortcode2array( $matches[5][ $key ], $depth );
        		}
        		else
        		{
        			$return[ $key ]['content'] = $matches[5][ $key ];
        		}
        	}
    
        	return $return;
        }

        
        /**
		 * Set the allowed shortcodes
		 **/
		static function allowed_shortcodes( $params, $remove = false )
	 	{
	 		if( ! $remove )
	 		{
	 			self::$manually_allowed_shortcodes = $params;
	 			if( ! in_array( 'av_textblock', self::$manually_allowed_shortcodes ) ) 
				{
					self::$manually_allowed_shortcodes[] = 'av_textblock';
				}
	 		}
	 		else
	 		{
	 			self::$manually_disallowed_shortcodes = $params;
	 		}
	 	}
	 	
	 	
	 	 /**
		 *check if the current element is nested within column or section
		 **/
		static function is_top_level()
	 	{
	 		global $avia_config;
	 		
	 		if(!isset($avia_config['conditionals']['is_builder'])) return false;
	 		if(isset($avia_config['current_column']) || isset($avia_config['layout_container'])) return false;
	 		
	 		return true;
	 	}
	 	
	 	
	 	/**
		 * creates the shortcode pattern that only matches Avia Builder Shortcodes
		 * 
		 * @param array|false $predefined_tags
		 * @return string
		 **/
		static function build_pattern( $predefined_tags = false )
	 	{
	 		global $shortcode_tags;
	 		
	 		//save the "real" shortcode array
	 		$old_sc = $shortcode_tags;
	 		
	 		//if we got allowed shortcodes build the pattern. nested shortcodes are also considered but within a separate array
	 		if( ! empty( ShortcodeHelper::$allowed_shortcodes ) )
			{
				$shortcode_tags = array_flip( array_merge( ShortcodeHelper::$allowed_shortcodes, ShortcodeHelper::$nested_shortcodes ) );
			}
			
			// filter out all elements that are not in the predefined tags array. this is necessary for nested shortcode modal to work properly
			if( is_array( $predefined_tags ) )
			{
				$predefined_tags = array_flip( $predefined_tags );
				$shortcode_tags = shortcode_atts( $predefined_tags, $shortcode_tags );
			}

			//create the pattern and store it 
			ShortcodeHelper::$pattern = get_shortcode_regex();
			
			//restore original shortcode tags
			$shortcode_tags = $old_sc;
			
			return ShortcodeHelper::$pattern;
	 	}
	 	
	 	
	 	/**
		 * Create a fake pattern on the fly that makes us able to check a post for shortcodes upfront, 
		 * just so we know if we need to load any special resources. Can also returns the complete 
		 * shortcode tags (open or close, not the content) to check and balance the shortcodes.
		 * 
		 * With 4.2.1 this function also returns the escaped shortcodes.
		 * 
		 * @param boolean $nested 
		 * @param array|false $shortcode_tags
		 * @param string $return					'fake' | 'tags' (added with 4.3) | 'complete'  (added with 4.1.2)
		 * @return string 
		 **/
		static function get_fake_pattern( $nested = false, $shortcode_tags = false, $return = 'fake' )
	 	{
	 		if( ! is_array( $shortcode_tags ) )
	 		{
	            if( ! empty( ShortcodeHelper::$allowed_shortcodes ) )
				{
					//usually we dont want the nested fake shortcodes to be included in the count
					if( $nested )
					{
						$shortcode_tags = array_merge( ShortcodeHelper::$allowed_shortcodes, ShortcodeHelper::$nested_shortcodes );
					}
					else
					{
						$shortcode_tags = array_merge( ShortcodeHelper::$allowed_shortcodes );
					}
				}
			}
			
			if( 'fake' == $return )
			{
				//	match all shortcodes and escaped shortcodes start and end tags:  [xxx  [[xxx  [/xxx]  [/xxx]]
				$pattern = "\[{1,2}" . implode( '[\s|\]]|\[{1,2}', $shortcode_tags ) . "[\s|\]]|\[\/" . implode( '\]{1,2}|\[\/', $shortcode_tags ) . "\]{1,2}";
			}
			else
			{			
				$arr_patt = array();
				
				foreach( $shortcode_tags as $key => $shortcode_tag ) 
				{	
					/**
					 *	captures unclosed tags like "[fooo ...  [ " and  [/foo .... [  - must be placed first otherwise will be included in standard match
					 * 
					 * (?=\[) includes everything up to next [ (but not including [)
					 */
					if( 'complete' == $return )
					{
						$arr_patt[] = '\[{1,2}[\/]?' . $shortcode_tag . '[^\]]*?(?=\[)';
					}

					//	captures complete opening and closing shortcode tags
					$arr_patt[] = '\[{1,2}[\/]?' . $shortcode_tag . '[^\]]*\]{1,2}';
					
				}
				
				$pattern = implode( '|', $arr_patt );
			}
			
			return $pattern;
        }

		/**
		 * Builds a shortcode tree out of an array that was extracted from the content with get_fake_pattern and a call to 
		 * preg_match_all( ...., PREG_OFFSET_CAPTURE ).
		 * 
		 * Also removes shortcodes inside escaped shortcodes and escaped shortcodes as WP ignores these.
		 * 
		 * Ensure that the HTML shortcode structure in page content is balanced with all registered shortcodes before calling this function, 
		 * otherwise this might lead to a broken layout because WP might work with a different shortcode structure
		 * https://codex.wordpress.org/Shortcode_API#Unclosed_Shortcodes
		 *   
		 * ( also see ShortcodeHelper::balance_shortcode() )
		 * 
		 * @param string $content	
		 * @return array
		 */
		static public function &build_shortcode_tree( $content )
        { 
			
			//	see avia_sc_postcontent (currently experimental only)
//          if( is_array( $matches[0] ) ) 
//			{
//				$matches = $matches[0];
//			}
			
			$tree = array();
			$wp_result = array();
			
			$parser_state = Avia_Builder()->get_posts_shortcode_parser_state();
			
			if( in_array( $parser_state, array( 'disabled' ) ) || ! in_array( Avia_Builder()->get_shortcode_parser()->get_shortode_state(), array( 'valid' ) ) )
			{
				/**
				 * This is for a fallback situation when user does not want to or cannot use the autorepair function because it breaks.
				 * We use the old style from version <= 4.2
				 * 
				 * Extract all shortcodes from the post array and store them so we know what we are dealing with when the user opens a page.
				 * Usesfull for special elements that we might need to render outside of the default loop like fullscreen slideshows
				 */
				
				$matches = array();
				
				preg_match_all( "/" . ShortcodeHelper::get_fake_pattern() . "/s", $content, $matches );
				if( is_array( $matches ) && is_array( $matches[0] ) && ( ! empty( $matches[0] ) ) )
				{
					$matches = $matches[0];
				}
				else
				{
					return $tree;
				}
				
				$matches = explode( ',', str_replace( ']', '', implode( ',', $matches ) ) );

				/**
				 * Close all elements that are not self closing to generate a valid xml string
				 */
				$current = 0;

				while ( $current < count( $matches ) )
				{
					$match = trim( $matches[ $current ] );

					if( strpos( $match, '/' ) !== false )
					{
						$current ++;
						continue;
					}

					$closing = trim(str_replace('[','[/',$match));

					$search = $current + 1;
					$auto_close = true;

					while ( $search < count( $matches ) )
					{
						$comp = trim( $matches[ $search ] );
						
						if( $match == $comp )
						{
							break;
						}

						if( $closing == $comp )
						{
							$auto_close = false;
							break;
						}

						$search ++;
					}

					if( $auto_close ) //if we got no closing tag add a temp one
					{
						array_splice( $matches, $current + 1, 0, array( $closing ) ); 
					}

					$current ++;
				}				
				/**
				 * Reset to new style array
				 */
				foreach( $matches as $sc ) 
				{
					$wp_result[] = array( $sc );
				}
			}
			else
			{
				$parser = Avia_Builder()->get_shortcode_parser();
				
				/**
				 * This is the preferred way since 4.2.1
				 * 
				 * To be on the safe side we use pure WP regex to extract the shortcodes.
				 * As content should have been balanced before calling this function we can rely on a
				 * valid shortcode structure 
				 */
				$wp_result = $parser->get_wp_result( $content );


				/**
				 * Ensure we have a valid shortcode structure before calling this function !!!
				 * Now we scan for escaped shortcodes and add missing closing tags to self closing tags so we have a valid XML structure
				 * for easier building of the tree.
				 */
				$wp_result = $parser->balance_shortcode_array( $wp_result, 'forced' );

				/**
				 * Remove escaped shortcodes and extract only ALB shortcodes that have a valid callback 
				 */
				$parser->remove_escape_shortcodes_in_array( $wp_result );
				$parser->filter_shortcodes_in_array( $wp_result, ShortcodeHelper::$allowed_shortcodes );
			}
		
			/*
			 * As we can rely on valid shortcode structure now we can safely use the truncated shortcode without attributes
			 */
			$sc = array();
			foreach ( $wp_result as $code ) 
			{
				$sc[] = $code[0];
			}
			
            $shortcodes = explode( ',', str_replace( array( ']', '[' ), '', implode( ',', $sc ) ) );
 
            $temp_index = 0;
            $tree       = array('content' => array());
            $pointers   = array(&$tree);
            
            foreach( $shortcodes as $index => $line ) 
            {
            	if(!empty($line[0]))
            	{
	                $close = '/' === $line[0];
	                $count = count($pointers);
	                
	                if(!$close)
	                {
	                    $pointers[$count]                  = array('tag' => trim($line), 'content' => array(), 'index' => $temp_index);
	                    $pointers[$count - 1]['content'][] = & $pointers[$count];
	                    $temp_index ++ ;
	                    continue;
	                }
	                else
	                {
	                    array_pop($pointers);
	                }
                }
            }
            
            $result = &$tree['content'];
            unset($tree);
           
            return $result;
        }
        

        
        static function find_tree_item($index, $sibling = false, $tree = false)
        {
            if(empty(self::$tree)) return false;
            if($tree === false) $tree = self::$tree;
			$return = array();
			
			
            foreach($tree as $key => $t)
            {
               if(!$return)
               {
	               if($t['index'] == $index)
	               {
	                    if($sibling !== false)
	                    {
	                        $return = isset($tree[$key + $sibling]) ? $tree[$key + $sibling] : false;
	                    }
	                    else
	                    {
	                        $return = $tree[$key];
	                    }
	                    
	                    return $return;
	               }
	               else if(!empty($tree[$key]['content']))
	               {
	                    $return = self::find_tree_item($index, $sibling, $tree[$key]['content']); 
	               }
               }
            }
            
            return $return;
        }
        
		/**
		 * Clean up the rendered text and ensure balanced shortcode tags (depending on shortcode parser setting). 
		 * $text can be content of an ALB paga, a non ALB page, _aviaLayoutBuilderCleanData or ALB template
		 * 
		 *		- Checks, that existing closing shortcode tags are applied to all opening shortcode tags ( nesting of same name shortcode is not supported )
		 *		- Removes all unnecessary html tags around shortcodes that are added by the editor
		 * 
		 * @param string $text
		 * @param string $action			'content' | 'balance_only'
		 * @return string
		 */
		static public function clean_up_shortcode( $text, $action = 'balance_only' )
	 	{
/*
			self::build_pattern();
            $text_nodes = preg_split("/".self::$pattern."/s", $text);
            
            //usually removes all <p> tags that are not needed before shorttcode and in between them like </p><p>
            foreach($text_nodes as $node ) 
			{			
	            if( strlen( trim( $node ) ) == 0 || strlen( trim( strip_tags($node) ) ) == 0) 
	            {
	               $text = preg_replace("/(".preg_quote($node, '/')."(?!\[\/))/", '', $text);
	            }
	        }
	        
*/
			
			/**
			 * Activates the shortcode parser depending on shortcode parser flag setting and generates the error output to DB
			 */
			$text = Avia_Builder()->get_shortcode_parser()->parse_shortcode( $text );
			
			
			/**
			 * On a non ALB page, _aviaLayoutBuilderCleanData and template we only need to add the missing shortcode tags and do not
			 * touch the structure of the page
			 */
			if( 'balance_only' == $action )
			{
				return $text;
			}
			
			/**
			 * On ALB page content:
			 * 
			 * remove all <br/> tags that are not needed. those that follow immediately after a shortode or are located just before one
			 */
			$shortcode_tags = array_merge( ShortcodeHelper::$allowed_shortcodes, ShortcodeHelper::$nested_shortcodes );
	    
			$tagregexp = join( '|', array_map( 'preg_quote', $shortcode_tags ) );
	        
			$regex = "!(\s*?\<br.?/?>.?)*?(\[\/?($tagregexp).*?\])(\s*?\<br.?/?>.?)*!s";
			$text = preg_replace( $regex, '${2}', $text );
            
			return $text;
		}
			
		
		/**
         * Removes wordpress autop and invalid nesting of p tags, as well as br tags
         *
         * @param string $content html content by the wordpress editor
         * @return string $content
         */
        static function avia_remove_autop($content,$do_shortcode = false)
        {
            $shortcode_tags = array_merge(ShortcodeHelper::$allowed_shortcodes, ShortcodeHelper::$nested_shortcodes);
            $tagregexp = join( '|', array_map('preg_quote', $shortcode_tags) );

            // opening tag
            $content = preg_replace("/(<p>)?\[($tagregexp)(\s[^\]]+)?\](<\/p>|<br \/>)?/","[$2$3]",$content);

            // closing tag
            $content = preg_replace("/(<p>)?\[\/($tagregexp)](<\/p>|<br \/>)?/","[/$2]",$content);


            if($do_shortcode) $content = do_shortcode( shortcode_unautop($content) );
            $content = preg_replace('#^<\/p>|^<br\s?\/?>|<p>$|<p>\s*(&nbsp;)?\s*<\/p>#', '', $content);

            return $content;
        }


        /**
         * Applies wordpress autop filter
         *
         * @param string $content html content by the wordpress editor
         * @return string $content
         */

        static function avia_apply_autop($content,$do_shortcode = true)
        {
            $content = wpautop($content);
            if($do_shortcode) $content = do_shortcode( shortcode_unautop($content) );

            return $content;
        }

		/** 
		 * Creates the shortcode based on variables that are passed
		 * 
		 * @param string $name
		 * @param string|null $content
		 * @param array $args
		 * @return string
		 */
		static public function create_shortcode_by_array( $name, $content = null, $args = array() )
		{
			$sc = '[' . $name;
		
			if( is_array( $args ) )
			{
				foreach( $args as $key => $arg )
				{
					if( is_numeric( $key ) )
					{
						$sc .= ' ' . $arg;
					}
					else
					{
						//	if attributes array is coming from frontend we can have arrays (e.g. link) 
						if( is_array( $arg ) )
						{
							$arg = implode( ',', $arg );
						}
						
						if( strpos( $arg, "'" ) === false && strpos( $arg, "&#039;" ) === false )
						{
							$sc .= ' ' . $key . "='" . $arg . "'";
						}
						else
						{
							$sc .= ' ' . $key . '="' . $arg . '"';
						}
					}
				}
			}
			
			$sc .= ']';
			
			if( ! is_null( $content ) )
			{
				//strip slashes and trim the content
				$content = "\n" . trim( stripslashes( $content ) ) . "\n"; 
				
				// $content = htmlentities( $content , ENT_QUOTES, get_bloginfo( 'charset' ) ); //entity-test: added htmlentities
				
				//if the content is empty without tabs and line breaks remove it completly
				if( trim( $content ) == '' ) 
				{
					$content = '';
				}
				
				$sc .= "{$content}[/{$name}]";
			}
			
			$sc .= "\n\n";
            //$sc = str_replace("\n",'',$sc);
			return $sc;
		}
		
		/**
		 * Creates the complete shortcode string of an ALB element from an attribute array.
		 * Content can be an array of attributes or a string.
		 * Checks for self closing shortcode.
		 * If 'content' is an array, then nested shortcodes will be created for each entry, otherwise content is copied 1:1
		 * 
		 * @since 4.8
		 * @param aviaShortcodeTemplate $shortcode
		 * @param array $attr
		 * @return string
		 */
		static public function create_shortcode_from_attributes_array( aviaShortcodeTemplate $shortcode, array $attr = array() )
		{
			$item_is_self_closing = $shortcode->has_modal_group_template() && $shortcode->is_nested_self_closing( $shortcode->config['shortcode_nested'][0] );
			
			$inner_content = '';
			
			if( $shortcode->has_modal_group_template() && isset( $attr['content'] ) )
			{
				if( ! is_array( $attr['content'] ) )
				{
					$inner_content = $attr['content'];
				}
				else
				{
					$inner_content = array();
					
					foreach( $attr['content'] as $item ) 
					{
						if( ! is_array( $item ) )
						{
							$inner_content[] = $item;
							continue;
						}
					
						$item_cont = '';
						if( isset( $item['content'] ) )
						{
							$item_cont = $item['content'];
							unset( $item['content'] );
						}
						
						if( $item_is_self_closing )
						{
							$item_cont = null;
						}
						
						$inner_content[] = ShortcodeHelper::create_shortcode_by_array( $shortcode->config['shortcode_nested'][0], $item_cont, $item );
					}
					
					$inner_content = implode( "\n", $inner_content );
				}
			}
			else if( isset( $attr['content'] ) )
			{
				$inner_content = $attr['content'];
			}
			
			if( $shortcode->is_self_closing() )
			{
				$inner_content = null;
			}
			
			unset( $attr['content'] );
			
			return ShortcodeHelper::create_shortcode_by_array( $shortcode->config['shortcode'], $inner_content, $attr );
		}
	}
	
}