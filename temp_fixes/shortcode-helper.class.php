<?php
/**
* ShortcodeHelper class that holds information on allowed shortcodes, formating functions etc
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if ( !class_exists( 'ShortcodeHelper' ) ) {
	
	class ShortcodeHelper
	{
	
        static $allowed_shortcodes = array();
        static $manually_allowed_shortcodes = array();
        static $manually_disallowed_shortcodes = array();
		static $nested_shortcodes = array();
        static $pattern = "";
        static $tree = array();
        static $shortcode_index = 0; //tells us which index the currently rendered shortcode has
        
		static $direct_calls = 0;			//	adds direct calls to theme shortcodes (e.g. from codeblocks to update $shortcode_index correctly)
        static $is_direct_call = false;		//	set to true if shortcodes are eecuted inside elements and removed from final code to execute like in codeblocks (shortcode tree is incorrect in that case) 
        
        /**
		 *Converts a shortcode into an array
		 **/
        static function shortcode2array($content, $depth = 1000)
        {	
        	$pattern = empty(ShortcodeHelper::$pattern) ? ShortcodeHelper::build_pattern() : ShortcodeHelper::$pattern;
        	$depth --;

        	preg_match_all( "/$pattern/s", $content , $matches);
        	
        	$return = array();
        	foreach($matches[3] as $key => $match)
        	{
        		$return[$key]['shortcode'] 	= $matches[2][$key];
        		$return[$key]['attr'] 		= shortcode_parse_atts( $match ); 
        		
        		if(preg_match("/$pattern/s", $matches[5][$key]) && $depth)
        		{
        			$return[$key]['content'] 	= self::shortcode2array($matches[5][$key], $depth);
        		}
        		else
        		{
        			$return[$key]['content'] 	= $matches[5][$key];
        		}
        	}
    
        	return $return;
        }

        
        
        /**
		 *set the allowed shortcodes
		 **/
		static function allowed_shortcodes($params, $remove = false)
	 	{
	 		if(!$remove)
	 		{
	 			self::$manually_allowed_shortcodes = $params;
	 			if(!in_array('av_textblock', self::$manually_allowed_shortcodes)) self::$manually_allowed_shortcodes[] = 'av_textblock';
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
		 *creates the shortcode pattern that only matches Avia Builder Shortcodes
		 **/
		static function build_pattern($predefined_tags = false)
	 	{
	 		global $shortcode_tags;
	 		
	 		//save the "real" shortcode array
	 		$old_sc = $shortcode_tags;
	 		
	 		//if we got allowed shortcodes build the pattern. nested shortcodes are also considered but within a separate array
	 		if(!empty(ShortcodeHelper::$allowed_shortcodes))
			{
				$shortcode_tags = array_flip(array_merge(ShortcodeHelper::$allowed_shortcodes, ShortcodeHelper::$nested_shortcodes));
			}
			
			//filter out all elements that are not in the predefined tags array. this is necessary for nested shortcode modal to work properly
			if(is_array($predefined_tags))
			{
				$predefined_tags = array_flip($predefined_tags);
				$shortcode_tags = shortcode_atts($predefined_tags, $shortcode_tags);
			}

			//create the pattern and store it 
			ShortcodeHelper::$pattern = get_shortcode_regex();
			
			//restore original shortcode tags
			$shortcode_tags = $old_sc;
			
			return ShortcodeHelper::$pattern;
	 	}
	 	
	 	
	 	/**
		 *create a fake pattern on the fly that makes us able to check a post for shortcodes upfront, just so we know if we need to load any special resources
		 **/
		static function get_fake_pattern($nested = false, $shortcode_tags = false)
	 	{
	 		if(!is_array($shortcode_tags))
	 		{
	            if(!empty(ShortcodeHelper::$allowed_shortcodes))
				{
					//usually we dont want the nested fake shortcodes to be included in the count
					if($nested)
					{
						$shortcode_tags = array_merge(ShortcodeHelper::$allowed_shortcodes, ShortcodeHelper::$nested_shortcodes);
					}
					else
					{
						$shortcode_tags = array_merge(ShortcodeHelper::$allowed_shortcodes);
					}
				}
			}
			
			$pattern = "\[".implode('[\s|\]]|\[', $shortcode_tags)."[\s|\]]|\[\/".implode('\]|\[\/', $shortcode_tags)."\]";

			return $pattern;
        }

        
        /**
		 *build a shortcode tree out of an array that was extracted from the content with get_fake_pattern
		 **/
		static function build_shortcode_tree($matches)
        { 
            if( is_array( $matches[0] ) ) 
			{
				$matches = $matches[0];
			}
            
            $matches = explode( ',', str_replace( ']', '', implode( ',', $matches ) ) );
            
            //	close all elements that are not self closing to generate a valid xml string			
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

            $matches = explode( ',', str_replace( '[', '', implode( ',', $matches ) ) );

            $temp_index = 0;
            $tree       = array('content' => array());
            $pointers   = array(&$tree);
            
            foreach ($matches as $index => $line) 
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
		 *remove all unnecessary tags around shortcodes that are added by the editor
		 **/
		static function clean_up_shortcode($text)
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
	        
	        //remove all <br/> tags that are not needed. those that follow immediatly after a shortode or are located just before one
	        $shortcode_tags = array_merge(ShortcodeHelper::$allowed_shortcodes, ShortcodeHelper::$nested_shortcodes);
	    
	     
	        $tagregexp = join( '|', array_map('preg_quote', $shortcode_tags) );
	        
            $regex = "!(\s*?\<br.?/?>.?)*?(\[\/?($tagregexp).*?\])(\s*?\<br.?/?>.?)*!s";
            $text = preg_replace($regex, '${2}', $text);
            
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
		* holds the function that creates the shortcode based on variables that are passed
		*/
		static function create_shortcode_by_array($name, $content = NULL, $args = array())
		{
			$sc = "[".$name;
		
			if(is_array($args))
			{
				foreach($args as $key => $arg)
				{
					if(is_numeric($key))
					{
						$sc .= " ".$arg;
					}
					else
					{
						if(strpos($arg , "'") === false && strpos($arg,"&#039;") === false)
						{
							$sc .= " ".$key."='".$arg."'";
						}
						else
						{
							$sc .= ' '.$key.'="'.$arg.'"';
						}
					}
				}
			}
			
			$sc .= "]";
			
			if(!is_null($content))
			{
				
				//strip slashes and trim the content
				$content = "\n".trim(stripslashes($content)) ."\n"; 
				
				// $content = htmlentities( $content , ENT_QUOTES, get_bloginfo( 'charset' ) ); //entity-test: added htmlentities
				
				//if the content is empty without tabs and line breaks remove it completly
				if(trim($content) == "") $content = "";
				
				$sc .= $content."[/$name]";
			}
			
			$sc .= "\n\n";
            //$sc = str_replace("\n",'',$sc);
			return $sc;
		}
	
	}
	
	
}