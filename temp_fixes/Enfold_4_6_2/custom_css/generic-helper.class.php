<?php
/**
* Central AviaHelper class which holds quite a few unrelated functions
*/

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( ! class_exists( 'AviaHelper' ) ) 
{
	
	class AviaHelper
	{
		static $cache = array(); 		//holds database requests or results of complex functions
		static $templates = array(); 	//an array that holds all the templates that should be created when the print_media_templates hook is called
		static $mobile_styles = array(); 		//an array that holds mobile styling rules that are appened to the end of the page
		
		/**
		 * Returns the result of a multi input field that works with 2-4 values eg: margin, padding, border-radius etc
		 * 
		 * @since 4.3
		 * @added_by Kriesi
		 * @return array
		 */
		static public function multi_value_result( $value_array , $attr_name , $directions = array('top' , 'right' , 'bottom' , 'left') )
		{
			$explode = explode( ',', $value_array );
			
    		$minifed = "";
			$writen  = "";
			$comp_count = 0;
			//make sure that the explode array has the correct amount of entries. if only one is set apply it to all direction sets
			if(count($explode) == 1)
			{
				$new_explode = array();
				foreach($directions as $key => $value)
				{
					$new_explode[] = $explode[0];
				}
				
				$explode = $new_explode;
			}
			
			foreach($explode as $key => $value)
			{
				if(!isset($value) && is_numeric($value))
				{
					$value = $value ."px";
				}
				
				if(empty($value) && ( isset($value) && $value !== "0") )
				{
					$value = "0";
				}
				else
				{
					$writen .= $attr_name ."-". $directions[$key] . ":".$value."; ";
					$comp_count ++;
				}
				
				$minifed .= $value ." ";
			}
			
			$minifed = $attr_name.":".trim($minifed)."; ";
			if($comp_count == 4) $writen = $minifed;
			
			//overwrite sets all values, those not set by the user are set to 0. complement only creates rules for set elements and skips unset rules
			$result = array( 'overwrite' => $minifed , 'complement' => $writen ); 			
			
			return $result;
		}
		
		
		/**
    	 * get_url - Returns a url based on a string that holds either post type and id or taxonomy and id
    	 */
    	static function get_url($link, $post_id = false) 
    	{
    		$link = explode(',', $link, 2);
    		
    		if($link[0] == 'lightbox')        
    		{
    			$link = wp_get_attachment_image_src($post_id, apply_filters('avf_avia_builder_helper_lightbox_size','large'));
    			return $link[0];
    		}
    		
    		if(empty($link[1]))
    		{
    			return $link[0];
    		}
    		
    		if($link[0] == 'manually')
    		{
    			if(strpos($link[1], "@") !== false && strpos($link[1], "://") === false){ $link[1] = "mailto:".$link[1]; }
    			return $link[1];
    		}
    		
            if(post_type_exists( $link[0] ))
            {
            	return get_permalink($link[1]);
            }
            
            if(taxonomy_exists( $link[0]  ))  
            {
            	$return = get_term_link(get_term($link[1], $link[0]));
            	if(is_object($return)) $return = ""; //if an object is returned it is a WP_Error object and something was not found
            	return $return;
            } 
    	}
		
		/**
		 * Returns a user friendly text that can be rendered to a screen reader output
		 * Based on same input as to AviaHelper::get_url
		 * 
		 * @since 4.2.7
		 * @added_by Günter
		 * @param string $link
		 * @param int|null $post_id
		 * @return string
		 */
		static public function get_screen_reader_url_text( $link, $post_id = false )
		{
			$link = explode( ',', $link, 2 );
			
			if( $link[0] == 'lightbox' )        
    		{
				$post = get_post( $post_id );
				if( ! $post instanceof WP_Post )
				{
					return __( 'No attachment image available', 'avia_framework' );
				}
				
    			$link = wp_get_attachment_image_src( $post_id, apply_filters( 'avf_avia_builder_helper_lightbox_size', 'large' ) );
				
				if( false === $link )
				{
					return __( 'No attachment image available for: ', 'avia_framework' ) . esc_html( $post->post_title );
				}
				
    			return __( 'Attachment image for: ', 'avia_framework' ) . esc_html( $post->post_title );
    		}
			
			if( empty( $link[1] ) )
    		{
    			return __( 'Follow a manual added link', 'avia_framework' );
    		}
			
			if( $link[0] == 'manually' )
    		{
    			if( strpos( $link[1], "@" ) !== false && strpos( $link[1], "://" ) === false )
				{
					return __( 'Send an E-Mail to: ', 'avia_framework' ) . $link[1];
				}		

    			return __( 'Follow a manual added link', 'avia_framework' );
    		}
			
			if( post_type_exists( $link[0] ) )
            {
				$post = get_post( $link[1] );
				if( ! $post instanceof WP_Post )
				{
					return __( 'Wrong link - page does not exist', 'avia_framework' );
				}
				
				return __( 'Link to: ', 'avia_framework' ) . esc_html( $post->post_title );
            }
			
			if( taxonomy_exists( $link[0] ) )  
            {
				$term = get_term( $link[1], $link[0] );
							
				if( ! $term instanceof WP_Term)
				{
					return __( 'Wrong link - page does not exist', 'avia_framework' );
				}
					
            	return sprintf( __( 'Link to %s in %s', 'avia_framework' ), $term->name, $term->taxonomy );
            } 
			
			return '';
		}
				
		/**
    	 * get_entry - fetches an entry based on a post type and id
    	 */
    	static function get_entry($entry) 
    	{
    		$entry = explode(',', $entry);
    		
    		if(empty($entry[1]))              return false;
    		if($entry[0] == 'manually')        return false;
            if(post_type_exists( $entry[0] ))  return get_post($entry[1]);
    	}
    	
    	/**
    	 * fetch all available sidebars
    	 */
    	static function get_registered_sidebars($sidebars = array(), $exclude = array())
    	{
    		//fetch all registered sidebars and save them to the sidebars array
			global $wp_registered_sidebars;
			
			foreach($wp_registered_sidebars as $sidebar)
			{
				if( !in_array($sidebar['name'], $exclude))
				{
					$sidebars[$sidebar['name']] = $sidebar['name']; 
				}
			}
			
			return $sidebars;
    	}
    	
    	static function get_registered_image_sizes($exclude = array(), $enforce_both = false, $exclude_default = false)
    	{
    		global $_wp_additional_image_sizes;
    		
    		 // Standard sizes
	        $image_sizes = array(   'no scaling'=> array("width"=>"Original Width ", "height"=>" Original Height"),
	        						'thumbnail' => array("width"=>get_option('thumbnail_size_w'), "height"=>get_option('thumbnail_size_h')),
	        						'medium' 	=> array("width"=>get_option('medium_size_w'), "height"=>get_option('medium_size_h')), 
	        						'large' 	=> array("width"=>get_option('large_size_w'), "height"=>get_option('large_size_h')));
	        
	        
	        if(!empty($exclude_default)) unset($image_sizes['no scaling']);
	        
	        if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) )
	                $image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes  );
	                
    		$result = array();
    		foreach($image_sizes as $key => $image)
			{
				if( (is_array($exclude) && !in_array($key, $exclude)) || (is_numeric($exclude) && ($image['width'] > $exclude || $image['height'] > $exclude)) || !is_numeric($image['height']))
				{
					if($enforce_both == true && is_numeric($image['height']))
					{
						if($image['width'] < $exclude || $image['height'] < $exclude) continue;
					}
					
					
					$title = str_replace("_",' ', $key) ." (".$image['width']."x".$image['height'].")";
					
					$result[ucwords( $title )] =  $key; 
				}
			}
    		
    		return $result;
    	}
    	
    	static function list_menus()
    	{
			$term_args = array( 
							'taxonomy'		=> 'nav_menu',
							'hide_empty'	=> false
						);
			
			$menus = AviaHelper::get_terms( $term_args );
				
    		$result = array();
    		
    		if(!empty($menus))
    		{
	    		foreach ($menus as $menu)
	    		{
	    			$result[$menu->name] = $menu->term_id;
	    		}
    		}
    		
    		return $result;
    	}


    	/**
    	 * is_ajax - Returns true when the page is loaded via ajax.
    	 */
    	static function is_ajax() 
    	{
    		if ( defined('DOING_AJAX') )
    			return true;
    
    		return ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) ? true : false;
    	}
		
		
		/**
		 * function that gets called on backend pages and hooks other functions into wordpress
		 *
		 * @return void
		 */
		static function backend()
		{
			add_action( 'print_media_templates', array('AviaHelper', 'print_templates' )); 		//create js templates for AviaBuilder Canvas Elements
		}
		
		
		/**
		 * Helper function that prints an array as js object. can call itself in case of nested arrays
		 *
		 * @return void
		 */
		
		static function print_javascript($objects = array(), $print = true, $passed = "")
		{	
			$output = "";
			if($print) $output .=  "\n<script type='text/javascript' class='av-php-sent-to-frontend'>/* <![CDATA[ */ \n";
			
			foreach($objects as $key => $object)
			{
				if(is_array($object))
				{	
					if(empty($passed))
					{
						$output .= "var {$key} = {};\n";
						$pass    = $key;
					}
					else
					{
						$output .= "{$passed}['{$key}'] = {};\n";
						$pass    = "{$passed}['{$key}']";
					}
					$output .= AviaHelper::print_javascript($object, false, $pass);
				}
				else
				{
					if(!is_numeric($object) && !is_bool($object)) $object = json_encode($object);
					if(empty($object)) $object = "false";
					
					if(empty($passed))
					{
						$output .= "var {$key} = {$object};\n";	
					}
					else
					{
						$output .= "{$passed}['{$key}'] = {$object};\n";
					}
				}
			}
			
			if($print) 
			{
				$output .=  "\n /* ]]> */</script>\n\n";
				echo $output;
			}
			
			return  $output;
		}
		
		
		/**
		 * Helper function that prints all the javascript templates
		 *
		 * @return void
		 */
		
		static function print_templates()
		{
			foreach (self::$templates as $key => $template)
			{
				echo "\n<script type='text/html' id='avia-tmpl-{$key}'>\n";
				echo $template;
				echo "\n</script>\n\n";
			}
			
			//reset the array
			self::$templates = array();
		}
		
		/**
		 * Helper function that creates a new javascript template to be called
		 *
		 * @return void
		 */
		
		static function register_template($key, $html)
		{
			self::$templates[$key] = $html;
		}
		
		
		
		/**
		 * Helper function that fetches all "public" post types.
		 *
		 * @return array $post_types example output: data-modal='true'
		 */
		
		static function public_post_types()
		{
			$post_types 		= get_post_types(array('public' => false, 'name' => 'attachment', 'show_ui'=>false, 'publicly_queryable'=>false), 'names', 'NOT');
			$post_types['page'] = 'page';
			$post_types 		= array_map("ucfirst", $post_types);
			$post_types			= apply_filters('avia_public_post_types', $post_types);
			self::$cache['post_types'] = $post_types;
			
			return $post_types;
		}
		
				
		
		/**
		 * Helper function that fetches all taxonomies attached to public post types.
		 *
		 * @return array $taxonomies
		 */
		
		static function public_taxonomies($post_types = false, $merged = false)
		{	
			$taxonomies = array();
			
			if(!$post_types)
				$post_types = empty(self::$cache['post_types']) ? self::public_post_types() : self::$cache['post_types'];
				
			if(!is_array($post_types))
				$post_types = array($post_types => ucfirst($post_types));
				
			foreach($post_types as $type => $post)
			{
				$taxonomies[$type] = get_object_taxonomies($type);
			}	
			
			$taxonomies = apply_filters('avia_public_taxonomies', $taxonomies);
			self::$cache['taxonomies'] = $taxonomies;
			
			if($merged)
			{
				$new = array();
				foreach($taxonomies as $taxonomy)
				{
					foreach($taxonomy as $tax)
					{
						$new[$tax] = ucwords(str_replace("_", " ",$tax));
					}
				}
				
				$taxonomies = $new;
			}
			
			return $taxonomies;
		}
		
		
		/**
		 * Helper function to ensure backwards comp. for WP version < 4.5
		 * 
		 * @since 4.4.2
		 * @added_by Günter
		 * @param array $term_args
		 * @return array|int|WP_Error		List of WP_Term instances and their children. Will return WP_Error, if any of $taxonomies do not exist.
		 */
		static public function get_terms( array $term_args )
		{
			global $wp_version;
			
			if( version_compare( $wp_version, '4.5.0', '>=' ) )
			{
				$terms = get_terms( $term_args );
			}
			else
			{
				$depr = $term_args;
				unset( $depr['taxonomy'] );
				$terms = get_terms( $term_args['taxonomy'], $depr );
			}
			
			return $terms;
		}

		
		/**
		 * Helper function that converts an array into a html data string
		 *
		 * @param array $data example input: array('modal'=>'true')
		 * @return string $data_string example output: data-modal='true'
		 */
		 
		static function create_data_string($data = array())
		{
			$data_string = "";
			
			foreach($data as $key=>$value)
			{
				if(is_array($value)) $value = implode(", ",$value);
				$data_string .= " data-$key='$value' ";
			}
		
			return $data_string;
		}
		
		/**
		 * Create a lower case version of a string and sanatize it to be valid classnames.
		 * Space seperated strings are kept as seperate to allow several classes
		 * 
		 * @since 4.5.7.2
		 * @param string $string
		 * @param string $replace
		 * @param string $fallback
		 * @return string
		 */
		static public function save_classes_string( $string , $replace = '_', $fallback = '' )
		{
			$parts = explode( ' ', $string );
			
			foreach( $parts as $key => $value ) 
			{
				$parts[ $key ] = AviaHelper::save_string( $value, $replace, $fallback, 'class' );
			}
			
			$new_string = implode( ' ', $parts );
			
			if( empty( $new_string ) )
			{
				$new_string = $fallback;
			}
			
			return $new_string;
		}

		/**
    	 * Create a lower case version of a string without spaces so we can use that string for database settings.
		 * Returns a fallback if empty is rendered or resulting string would be empty.
    	 * 
		 * @since 4.5.7.2 extended
		 * @since 4.6.3 added $context to allow case sensitive
    	 * @param string|mixed $string_to_convert
		 * @param string $replace
		 * @param string $fallback
		 * @param string $context				'' | 'id' | 'class'		
    	 * @return string the converted string
    	 */
    	static public function save_string( $string_to_convert, $replace = '_', $fallback = '', $context = '' )
    	{
			$string = ! empty( $string_to_convert ) ? (string) $string_to_convert : '';
			$string = trim( $string );
			
			if( empty( $string ) )
			{
				return $fallback;
			}
			
			if( ! in_array( $context, array( 'id', 'class' ) ) )
			{
				$string = strtolower( $string );
    	
				$trans = array(
    					'&\#\d+?;'				=> '',
    					'&\S+?;'				=> '',
    					'\s+'					=> $replace,
    					'ä'						=> 'ae',
    					'ö'						=> 'oe',
    					'ü'						=> 'ue',
    					'Ä'						=> 'Ae',
    					'Ö'						=> 'Oe',
    					'Ü'						=> 'Ue',
    					'ß'						=> 'ss',
    					'[^a-z0-9\-\._]'		=> '',
    					//$replace.'+'			=> $replace, //allow doubles like -- or __
    					$replace.'$'			=> $replace,
    					'^'.$replace			=> $replace,
    					'\.+$'					=> ''
					);
			}
			else
			{
				/**
				 * Restrictions to CSS selectors https://www.w3.org/TR/CSS21/syndata.html#characters
				 */
				$trans = array(
//						'&\#\d+?;'				=> '',
//    					'&\S+?;'				=> '',
    					'\s+'					=> $replace,
    					'ä'						=> 'ae',
    					'ö'						=> 'oe',
    					'ü'						=> 'ue',
    					'Ä'						=> 'Ae',
    					'Ö'						=> 'Oe',
    					'Ü'						=> 'Ue',
    					'ß'						=> 'ss',
    					'[^a-zA-Z0-9\-_]'		=> '',
    					//$replace.'+'			=> $replace, //allow doubles like -- or __
//    					$replace.'$'			=> $replace,
//    					'^'.$replace			=> $replace,
//    					'\.+$'					=> '', 
						'^[0-9\-]*'				=> ''		//	do not start with hyphen or numbers
					);
			}
    				  
			/**
			 * @since < 4.0
			 * @param array $trans
			 * @param string $string
			 * @param string $replace
			 * @param string $context		added 4.6.3 
			 * @return array
			 */
    		$trans = apply_filters( 'avf_save_string_translations', $trans, $string, $replace, $context );
    
    		$string = strip_tags( $string );
    
    		foreach( $trans as $key => $val )
    		{
    			$string = preg_replace( "#" . $key . "#i", $val, $string );
    		}
    		
			$string = stripslashes( $string );
			
			if( '' == $string )
			{
				$string = $fallback;
			}
			
			/**
			 * @since 4.6.3
			 * @param string $string
			 * @param string $string_to_convert
			 * @param string $replace
			 * @param string $fallback
			 * @param string $context
			 * @return string
			 */
			$string = apply_filters( 'avf_save_string_translated', $string, $string_to_convert, $replace, $fallback, $context );
			
    		return $string;
    	}
		
		/**
		 * Create a lower case version of a string without spaces and special characters so we can use that string for a href anchor link.
		 * Returns the default if the remaining string is empty or invalid (not at least one a-z, 0-9).
		 * 
		 * @param string $link
		 * @param string $replace
		 * @param string $default
		 * @return string
		 */
		static public function valid_href( $link, $replace = '_', $default = '' )
		{
			/**
			 * Create a unique default value for the link if none provided
			 */
			if( '' == trim( $default ) )
			{
				$default = uniqid( '', true );
				$default = strtolower( str_replace( '.', '-', $default ) );
			}
			
			$new_link = AviaHelper::save_string( $link, $replace );
			
			if( '' == trim( $new_link ) )
			{
				$new_link = $default;
			}
			else
			{
				/**
				 * non latin letters in $link might return an invalid link from AviaHelper::save_string (e.g. ---)
				 * Also make sure link starts with [a-z0-9]
				 */
				$sc_found = array();
				preg_match_all( "/[a-z0-9]/s", $new_link, $sc_found, PREG_OFFSET_CAPTURE );

				if( empty( $sc_found ) || ! is_array( $sc_found ) || empty( $sc_found[0]) || ( $sc_found[0][0][1] != 0 ) )
				{
					$new_link = $default;
				}
			}
			
			return $new_link;
		}
		
    	
    	/**
		 * Helper function that fetches the active value of the builder. also adds a filter
		 *
		 * @deprecated since version 4.2.1
		 */
		static function builder_status($post_ID)
		{
			_deprecated_function( 'builder_status', '4.2.1', 'AviaBuilder::get_alb_builder_status()');
			
			$status = get_post_meta($post_ID, '_aviaLayoutBuilder_active', true);
			$status = apply_filters('avf_builder_active', $status, $post_ID);
			
			return $status;
		}
		
    	/**
		 * Helper function that builds css styling strings which are applied to html elements
		 *
		 */
		static function style_string($atts, $key = false, $new_key = false, $append_value = "")
		{
			$style_string = "";
			
			//finish the style string by wrapping the arguments into a style string
			if((is_string($atts) || ! $atts ) && false == $key)
			{
				if(!empty($atts))
				{
					$style_string = "style='".$atts."'";
				}
			}
			else //otherwise build only the styling argument
			{
				if(empty($new_key)) $new_key = $key;
				
				if(isset($atts[$key]) && $atts[$key] !== "")
				{
					switch($new_key)
					{
						case "background-image": $style_string = $new_key.":url(".$atts[$key].$append_value."); "; break;
						case "background-repeat": if($atts[$key] == "stretch") $atts[$key] = "no-repeat"; $style_string = $new_key.":".$atts[$key].$append_value."; "; break;
						default: $style_string = $new_key.":".$atts[$key].$append_value."; "; break;
					}
				}
			}
			
			return $style_string;
		}

		
        /**
		 * Helper function for css declaration with 4 values such as margin/padding/border-radius
		 * @param $value string padding/margin/border-radius
		 */

		static function css_4value_helper( $value = "" ){

			if ( !empty ( $value ) ) {

				$css = "";
				$explode_value = explode( ',', $value );

				foreach ( $explode_value as $v ) {
					if ( !empty( $v ) ) {
						$css .= $v.' ';
					}
					else {
						$css .= '0 ';
					}
				}
				return $css;
			}
		}





        /**
         * Helper function that builds background css styling strings
         * Useful when there are multiple background settings like an image and a gradient
         * Returns a string to be used with the background property, e.g. style="background: $string";
         *
         * @param array $bg_image = array('url','position','repeat','attachment')
         * @param array $bg_gradient = array('direction','color1','color2')
         *        direction: vertical, horizontal, radial, diagonal_tb, diagonal_bt
         *
         */
        static public function css_background_string( $bg_image = array(), $bg_gradient = array() )
		{


            $background = array();

            // bg image
            if ( ! empty( $bg_image ) ) {


                if ( $bg_image['0'] !== '' ) {

                    $background['image_string'] = array();

                    $background['image_string'][] = 'url("'.$bg_image['0'].'")';

                    // bg image position
                    if ( array_key_exists('1', $bg_image) ) {
                        $background['image_string'][] = $bg_image['1'];
                    }
                    else {
                        $background['image_string'][] = 'center';
                    }

                    // bg image repeat
                    if ( array_key_exists('2', $bg_image) ) {
                        $background['image_string'][] = $bg_image['2'];
                    }
                    else {
                        $background['image_string'][] = 'no-repeat';
                    }

                    // bg image attachment
                    if ( array_key_exists('3', $bg_image) ) {
                        $background['image_string'][] = $bg_image['3'];
                    }
                }

            }

            // bg image css string
            if ( ! empty ( $background['image_string'] ) ) {
                $background['image_string'] = implode( ' ', $background['image_string'] );
            }

            // gradient
            if ( ! empty( $bg_gradient ) && count ( $bg_gradient ) == 3 ) {

                if ($bg_gradient['0'] !== '') {

                    $background['gradient_string'] = array();

                    switch ($bg_gradient['0']) {
                        case 'vertical':
                            $background['gradient_string'][] = 'linear-gradient(';
                            break;
                        case 'horizontal':
                            $background['gradient_string'][] = 'linear-gradient(to right,';
                            break;
                        case 'radial':
                            $background['gradient_string'][] = 'radial-gradient(';
                            break;
                        case 'diagonal_tb':
                            $background['gradient_string'][] = 'linear-gradient(to bottom right,';
                            break;
                        case 'diagonal_bt':
                            $background['gradient_string'][] = 'linear-gradient(45deg,';
                            break;
                    }

                    // gradient css string
                    if ( ! empty( $background['gradient_string'] ) ) {
                        $background['gradient_string'][]  .= $bg_gradient['1'] . ', ' . $bg_gradient['2'] . ')';
                    }

                }

            }

            // bg gradient css string
            if ( ! empty ( $background['gradient_string'] ) ) {
                $background['gradient_string'] = implode( ' ', $background['gradient_string'] );
            }

            if ( ! empty( $background ) ) {

                $background = implode(', ', $background );

                return $background;
            }

            else {

                return false;

            }
        }

		/**
		 * 
		 * @since < 4.0
		 * @return string
		 */
        static public function backend_post_type()
		{
			global $post, $typenow, $current_screen;

			$posttype = "";

			//we have a post so we can just get the post type from that
			if ($post && $post->post_type)
			{
				$posttype = $post->post_type;
			}
			//check the global $typenow - set in admin.php
			elseif($typenow)
			{
				$posttype = $typenow;
			}
			//check the global $current_screen object - set in sceen.php
			elseif($current_screen && $current_screen->post_type)
			{
				$posttype = $current_screen->post_type;
			}
			//lastly check the post_type querystring
			elseif(isset($_REQUEST['post_type']))
			{
				$posttype = sanitize_key($_REQUEST['post_type']);
			}

			return $posttype;	
		}
	
		/**
		 * Returns an array of ALB font size classes
		 * 
		 * @since < 4.0
		 * @param array $atts
		 * @return array
		 */
		static public function av_mobile_sizes( $atts = array() )
		{
			$result		= array('av_font_classes'=>'', 'av_title_font_classes'=>'', 'av_display_classes' => '', 'av_column_classes' => '');
			$fonts 		= array('av-medium-font-size', 'av-small-font-size', 'av-mini-font-size'); 
			$title_fonts= array('av-medium-font-size-title', 'av-small-font-size-title', 'av-mini-font-size-title'); 
			$displays	= array('av-desktop-hide', 'av-medium-hide', 'av-small-hide', 'av-mini-hide');
			$columns	= array('av-medium-columns', 'av-small-columns', 'av-mini-columns');


			if(empty($atts)) $atts = array();

			foreach($atts as $key => $attribute)
			{
				if(in_array($key, $fonts) && $attribute != "")
				{
					$result['av_font_classes'] .= " ".$key."-overwrite";
					$result['av_font_classes'] .= " ".$key."-".$attribute;

					if($attribute != "hidden") self::$mobile_styles['av_font_classes'][$key][$attribute] = $attribute;
				}

				if(in_array($key, $title_fonts) && $attribute != "")
				{
					$newkey = str_ireplace('-title', "", $key);

					$result['av_title_font_classes'] .= " ".$newkey."-overwrite";
					$result['av_title_font_classes'] .= " ".$newkey."-".$attribute;


					if($attribute != "hidden") 
					{ 
						self::$mobile_styles['av_font_classes'][$newkey][$attribute] = $attribute;
					}
				}

				if(in_array($key, $displays) && $attribute != "")
				{
					$result['av_display_classes'] .= " ".$key;
				}

				if(in_array($key, $columns) && $attribute != "")
				{
					$result['av_column_classes'] .= " ".$key."-overwrite";
					$result['av_column_classes'] .= " ".$key."-".$attribute;
				}
			}

			return $result;
		}
	
		/**
		 * Return CSS for media queries
		 * 
		 * @since < 4.0
		 * @return string
		 */
		static public function av_print_mobile_sizes()
		{
			$print 			= "";

			//rules are created dynamically, otherwise we would need to predefine more than 500 csss rules of which probably only 2-3 would be used per page
			$media_queries 	= apply_filters('avf_mobile_font_size_queries' , array(

				"av-medium-font-size" 	=> "only screen and (min-width: 768px) and (max-width: 989px)",
				"av-small-font-size" 	=> "only screen and (min-width: 480px) and (max-width: 767px)",
				"av-mini-font-size" 	=> "only screen and (max-width: 479px)",  

			));


			if(isset(self::$mobile_styles['av_font_classes']) && is_array(self::$mobile_styles['av_font_classes']))
			{
				$print .= "<style type='text/css'>\n";

				foreach($media_queries as $key => $query)
				{
					if( isset(self::$mobile_styles['av_font_classes'][$key]) )
					{
						$print .="@media {$query} { \n";

						if( isset(self::$mobile_styles['av_font_classes'][$key]))
						{
							foreach(self::$mobile_styles['av_font_classes'][$key] as $size)
							{
								$print .= ".responsive #top #wrap_all .{$key}-{$size}{font-size:{$size}px !important;} \n";
							}
						}

						$print .= "} \n";
					}
				}

				$print .="</style>";
			}

			return $print; 
		}
		
		/**
		 * Creates a date query for a standard WP query with the given dates and adds it to an existing query
		 * 
		 * @since 4.5.6.1
		 * @param array $query
		 * @param string $start_date
		 * @param string $end_date
		 * @param string $format
		 * @param string $relation				'AND' | 'OR' | ''
		 * @return array
		 */
		static public function add_date_query( array $query, $start_date, $end_date = '', $format = 'yy/mm/dd', $relation = '' )
		{
			if( empty( $start_date ) && empty( $end_date ) )
			{
				return $query;
			}
			
			if( empty( $start_date ) )
			{
				$start_date = $end_date;
				$end_date = '';
			}
			
			if( ! empty( $start_date ) )
			{
				$start_date = AviaHelper::default_date_string( $start_date, $format );
			}
			
			if( ! empty( $end_date ) )
			{
				$end_date = AviaHelper::default_date_string( $end_date, $format );
			}
			
			if( ! empty( $end_date ) && $start_date > $end_date )
			{
				$temp = $start_date;
				$start_date = $end_date;
				$end_date = $temp;
			}
			
			$q = array(
						'after'		=> array(
									'year'	=> substr( $start_date, 0, 4 ),
									'month'	=> substr( $start_date, 4, 2 ),
									'day'	=> substr( $start_date, 6, 2 ),
								),
						'inclusive'	=> true
					);
			
			if( ! empty( $end_date ) )
			{
				$q['before'] = array(
									'year'	=> substr( $end_date, 0, 4 ),
									'month'	=> substr( $end_date, 4, 2 ),
									'day'	=> substr( $end_date, 6, 2 ),
								);
			}
			
			if( ! empty( $relation ) )
			{
				$q['relation '] = $relation;
			}
					
			$query[] = $q;
					
			return $query;
		}
		
		/**
		 * Accespts a formatted datestring. In case of empty format uses default php function strtotime
		 * https://www.php.net/manual/de/function.strtotime.php
		 * 
		 * Returns time() if invalid
		 * 
		 * @since 4.5.6.1
		 * @param string $date_string		'yy/mm/dd' | 'dd-mm-yy' | 'yyyymmdd' | any valid php date format
		 * @param string $format			
		 * @return string					YYYYMMDD | ''
		 */
		static public function default_date_string( $date_string, $format = '' )
		{
			if( empty( $format ) || ( false === strpos( $format, '/' ) && false === strpos( $format, '-' ) ) )
			{
				$time = strtotime( $date_string );
				if( false == $time )
				{
					$time = time();
				}
				
				return date( 'Ymd', $time );
			}
			
			$sep = false === strpos( $format, '/' ) ? '-' : '/';
			
			$date_parts = explode( $sep, $date_string );
			$format_parts = explode( $sep, $format );
			$date = array();
			
			foreach( $format_parts as $key => $value ) 
			{
				$value = substr( trim( strtolower( $value ) ), 0, 2 );
				
				if( ! isset( $date_parts[ $key ] ) || ! is_numeric( $date_parts[ $key ] ) )
				{
					continue;
				}
				
				switch( $value )
				{
					case 'yy':
						if( strlen( $date_parts[ $key ] ) == 4 )
						{
							$date[ $value ] = $date_parts[ $key ];
						}
						break;
					case 'dd':
					case 'mm':
						if( strlen( $date_parts[ $key ] ) == 2 )
						{
							$date[ $value ] = $date_parts[ $key ];
						}
						break;
				}
			}
			
			if( count( $date ) != 3 )
			{
				return date( 'Ymd' );
			}
			
			return $date['yy'] . $date['mm'] . $date['dd'];
		}
	
	}
	
}
