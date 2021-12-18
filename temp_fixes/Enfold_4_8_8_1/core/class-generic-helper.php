<?php
/**
 * Central AviaHelper class which holds unrelated helper functions
 *
 */

// Don't load directly
if( ! defined( 'ABSPATH' ) )	{	exit;	}

if( ! class_exists( 'AviaHelper' ) )
{

	class AviaHelper
	{
		/**
		 * Holds database requests or results of complex functions
		 *
		 * @var array
		 */
		static protected $cache = array();

		/**
		 * Holds all the templates that should be created when the print_media_templates hook is called
		 *
		 * @var array
		 */
		static protected $templates = array();

		/**
		 * Holds mobile styling rules that are appened to the end of the page
		 *
		 * @var array
		 */
		static protected $mobile_styles = array();

		/**
		 * Returns the result of a multi input field that works with 2-4 values eg: margin, padding, border-radius etc
		 *
		 * @since 4.3
		 * @deprecated @since 4.8
		 * @added_by Kriesi
		 * @return array
		 */
		static public function multi_value_result( $value_array , $attr_name , $directions = array('top' , 'right' , 'bottom' , 'left') )
		{
			_deprecated_function( 'AviaHelper::multi_value_result', '4.8', 'AviaHelper::multi_value_result_lockable' );

			$explode = explode( ',', $value_array );

			$minifed = '';
			$writen  = '';
			$comp_count = 0;
			//make sure that the explode array has the correct amount of entries. if only one is set apply it to all direction sets
			if( count( $explode ) == 1 )
			{
				$new_explode = array();
				foreach( $directions as $key => $value )
				{
					$new_explode[] = $explode[0];
				}

				$explode = $new_explode;
			}

			foreach( $explode as $key => $value )
			{
				if( ! isset( $value ) && is_numeric( $value ) )
				{
					$value = $value . 'px';
				}

				if( empty( $value ) && ( isset( $value ) && $value !== '0' ) )
				{
					$value = '0';
				}
				else
				{
					$writen .= $attr_name . '-' . $directions[ $key ] . ':' . $value . '; ';
					$comp_count ++;
				}

				$minifed .= $value .' ';
			}

			$minifed = $attr_name . ':' . trim( $minifed ) . '; ';
			if( $comp_count == 4 )
			{
				$writen = $minifed;
			}

			//overwrite sets all values, those not set by the user are set to 0. complement only creates rules for set elements and skips unset rules
			$result = array( 'overwrite' => $minifed , 'complement' => $writen );

			return $result;
		}

		/**
		 * Returns the result of a multi input field that works with 2-4 values eg: margin, padding, border-radius etc.
		 *  - 1 value: this value is used for all directions
		 *	- 4 values: return is always minified
		 *
		 * @since 4.8
		 * @added_by Günter
		 * @param string $multi_value				1 value only -> apply to all | comma seperated to leave empty
		 * @param string $attr_name					e.g. 'margin', 'padding' ...., if empty only rules are returned
		 * @param array $directions
		 * @param boolean $one_value				true if first value applies to all
		 * @param string|false $values_only			'values_only' | false   return plain values only e.g. for locked values in element templates
		 * @return array
		 */
		static public function multi_value_result_lockable( $multi_value, $attr_name = '', $directions = array( 'top', 'right', 'bottom', 'left' ) )
		{
			$attr_name = str_replace( 'aviaTB', '', $attr_name );

			$one_value = false === strpos( $multi_value, ',' );

			//	Real filled input fields - empty will be skipped on css rules
			$explode = $one_value ? array( $multi_value ) : explode( ',', $multi_value );

			//	Return option values
			$locked_option_info = $one_value ? "{$multi_value},{$multi_value},{$multi_value},{$multi_value}" : $multi_value;


			$explode_css = $explode;
			for( $i = count( $explode) - 1; $i >= 0; $i-- )
			{
				if( $explode_css[ $i ] != '' )
				{
					break;
				}

				unset( $explode_css[ $i ] );
			}

			$sync_input = array();

			/**
			 * If only 1 value is in $value_array apply it to all direction sets
			 */
			if( count( $explode ) == 1 )
			{
				foreach( $directions as $key => $value )
				{
					$sync_input[] = $explode[0];
				}
			}
			else
			{
				foreach( $directions as $key => $value )
				{
					$sync_input[] = isset( $explode[ $key ] ) ? $explode[ $key ] : '';
				}
			}

			$fill_with_0_val = array();
			$set_values_only_val = array();
			$css_rules_val = array();

			foreach( $sync_input as $key => $value )
			{
				$is_empty = false;

				if( is_numeric( $value ) )
				{
					$val = (int) $value != 0 ? $value . 'px' : '0';
				}
				else if( empty( $value ) )
				{
					$val = '0';
					$is_empty = true;
				}
				else
				{
					$val = $value;
				}

				$fill_with_0_val[ $key ] = $val;

				if( isset( $explode_css[ $key ] ) )
				{
					$css_rules_val[ $key ] = $val;
				}

				if( ! $is_empty )
				{
					$set_values_only_val[ $key ] = $attr_name . '-' . $directions[ $key ] . ': ' . $val;
				}
			}


			$rules_complete = array();

			for( $i = 0; $i < 4; $i++ )
			{
				if( isset( $explode_css[ $i ] ) )
				{
					$rules_complete[ $i ] = $fill_with_0_val[ $i ];
					continue;
				}

				switch( $i )
				{
					case 3:
						if( isset( $explode_css[1] ) )
						{
							$rules_complete[3] =  $fill_with_0_val[1];
						}
						else
						{
							$rules_complete[3] =  $fill_with_0_val[0];
						}
						break;
					case 2:
						$rules_complete[2] =  $fill_with_0_val[0];
						break;
					case 1:
						$rules_complete[1] =  $fill_with_0_val[0];
						break;
					case 0:
						$rules_complete[0] =  $fill_with_0_val[0];
						break;
				}
			}

			$fill_with_0 = '';
			$set_values_only = '';

			$comma = '';
			if( ! empty( $attr_name ) )
			{
				$attr_name .= ': ';
				$comma = ';';
			}

			$css_rules = ! empty( $css_rules_val ) ? $attr_name . implode( ' ', $css_rules_val ) . $comma : '';
			$css_rules_with_0 = ! empty( $css_rules_val ) ? $css_rules : $attr_name . '0' . $comma;

			if( count( $fill_with_0_val ) < 4 )
			{
				$fill_with_0_val = array_merge( array( '0', '0', '0', '0' ), $fill_with_0_val );
			}

			$fill_with_0 = $attr_name . ': ' . implode( ' ', $fill_with_0_val ) . ';';

			if( count( $set_values_only_val ) == 4 )
			{
				$set_values_only = $fill_with_0;
			}
			else
			{
				$set_values_only = ! empty( $set_values_only_val ) ? implode( '; ', $set_values_only_val ) . ';' : '';
			}

			/**
			 * 'fill_with_0':			sets all values, those not set by the user are set to 0, minified string
			 * 'set_values_only':		creates rules for set elements and skips unset rules, minified string if all set, can be empty
			 * 'css_rules':				minified string following minified CSS rules containing only set values ( e.g. margin: 15px 10px; ), can be empty
			 * 'css_rules_with_0':		like 'css_rules', but containing 0 for not set values before last value ( e.g. margin: 15px 0 25px; )
			 * 'opt_values':			option values as entered in string
			 * 'locked_opt_info':		(array) option values as entered in array to show as locked option values ( 1 entry -> copied to others )
			 * 'fill_with_0_val':		(array) sets all values, those not set by the user are set to 0 (defaults to px)
			 * 'fill_with_0_style		(string) space seperated fill_with_0_val
			 * 'rules_complete':		(array) all rules are set, missing are added
			 */
			$result = array(
						'fill_with_0'		=> $fill_with_0,
						'set_values_only'	=> $set_values_only,
						'css_rules'			=> $css_rules,
						'css_rules_with_0'	=> $css_rules_with_0,
						'opt_values'		=> $multi_value,
						'locked_opt_info'	=> explode( ',', $locked_option_info ),
						'fill_with_0_val'	=> $fill_with_0_val,
						'fill_with_0_style'	=> implode( ' ', $fill_with_0_val ),
						'rules_complete'	=> $rules_complete
					);

			return $result;
		}

		/**
		 * Checks for _blank and nofollow and returns the html markup.
		 *
		 * @since 4.7.5.1
		 * @since 4.7.6.3					'noopener noreferrer' added for target="_blank"
		 * @since 4.8.6.3					SEO support for sponsored, ugc
		 * @param string $target
		 * @param string $link_type		added 4.7.6.3 - if 'manually' force adding 'noopener noreferrer' for rel
		 * @param array $rel_attr		added 4.7.6.3 - additional attr for rel
		 * @return string
		 */
		static public function get_link_target( $target, $link_type = '', $rel_attr = array() )
		{
			$markup = '';
			$rel = is_array( $rel_attr ) ? $rel_attr : array();

			if( false !== strpos( $target, '_blank' ) )
			{
				$markup .= ' target="_blank" ';
				$rel[] = 'noopener';
				$rel[] = 'noreferrer';
			}

			if( false !== strpos( $link_type, 'manually' ) )
			{
				$rel[] = 'noopener';
				$rel[] = 'noreferrer';
			}

			if( false !== strpos( $target, 'nofollow' ) )
			{
				$rel[] = 'nofollow';
			}

			if( false !== strpos( $target, 'sponsored' ) )
			{
				$rel[] = 'sponsored';
			}

			if( false !== strpos( $target, 'ugc' ) )
			{
				$rel[] = 'ugc';
			}

			/**
			 * Allows to filter rel attribute (e.g. remove noreferrer which has been removed since WordPress 5.6 to allow cross site analytics)
			 *
			 * @since 4.8.6.3
			 * @param array $rel
			 * @param string $target
			 * @param string $link_type
			 * @return array
			 */
			$rel = apply_filters( 'avf_alb_rel_attr_for_link', $rel, $target, $link_type );

			if( ! empty( $rel ) )
			{
				$rel = array_unique( $rel );
				$markup .= ' rel="' . implode( ' ', $rel ). '"';
			}

			return $markup;
		}


		/**
		 * get_url - Returns a url based on a string that holds either post type and id or taxonomy and id
		 * If $responsive_lightbox = true it returns an extended array with 0 => url, srcset, sizes
		 *
		 * @param string $link
		 * @param int|false $post_id
		 * @param boolean $responsive_lightbox				@added 4.8.2
		 * @return string|array
		 */
		static public function get_url( $link, $post_id = false, $responsive_lightbox = false )
		{
			$link = explode( ',', $link, 2 );

			if( $link[0] == 'lightbox' )
			{
				/**
				 *
				 * @used_by			config-wpml\config.php  avia_wpml_get_attachment_id()		10
				 * @since 4.8
				 * @param int $post_id;
				 * @return int
				 */
				$post_id = apply_filters( 'avf_alb_attachment_id', $post_id );


				/**
				 * @since ???
				 * @param string $image_size
				 * @param string $link						@added 4.8.2
				 * @param int|false $post_id				@added 4.8.2
				 * @param boolean $responsive_lightbox		@added 4.8.2
				 * @return string
				 */
				$lightbox_size = apply_filters( 'avf_avia_builder_helper_lightbox_size', 'large', $link, $post_id, $responsive_lightbox );


				if( true !== $responsive_lightbox )
				{
					$link = wp_get_attachment_image_src( $post_id, $lightbox_size );
					return is_array( $link ) ? $link[0] : '';
				}

				//	create array with responsive info for lightbox
				$link = Av_Responsive_Images()->responsive_image_src( $post_id, $lightbox_size );

				if( ! is_array( $link ) )
				{
					$img_link = '';
				}
				else
				{
					$img_link = array(
								0			=> esc_url( $link[0] ),
								'srcset'	=> $link['srcset'],
								'sizes'		=> $link['sizes']
							);
				}

				return $img_link;
			}

			if( $link[0] == 'manually' )
			{
				if( empty( $link[1] ) )
				{
					return '';
				}

				//	check for e-mail
				if( strpos( $link[1], '@' ) !== false && strpos( $link[1], '://' ) === false )
				{
					return 'mailto:' . $link[1];
				}

				$link_1 = strtolower( trim( $link[1] ) );

				if( 'http://' == $link_1 || 'https://' == $link_1 )
				{
					return '';
				}

				return $link[1];
			}

			if( empty( $link[1] ) )
			{
				return $link[0];
			}

			if( post_type_exists( $link[0] ) )
			{
				return get_permalink( $link[1] );
			}

			if( taxonomy_exists( $link[0] ) )
			{
				$return = get_term_link( get_term( $link[1], $link[0] ) );
				if( is_object( $return ) )
				{
					$return = ''; //if an object is returned it is a WP_Error object and something was not found
				}

				return $return;
			}

			return '';
		}

		/**
		 * Returns a human readable info about the link setting.
		 * Based on same input as to AviaHelper::get_url.
		 * Can be used to show "locked" link setting and supports multiple selections.
		 *
		 * @since 4.8
		 * @param string $link_string
		 * @return string|array
		 */
		static public function get_url_info( $link_string )
		{
			$link = explode( ',', $link_string );
			$result = '';

			if( empty( $link ) )
			{
				return __( 'No link selected', 'avia_framework' );
			}

			if( $link[0] == 'lightbox' )
			{
				return __( 'Lightbox', 'avia_framework' );
			}

			//	Fallback situation
			if( count( $link ) == 1 )
			{
				$result = $link[0];
			}

			if( $link[0] == 'manually' )
			{
				if( strpos( $link[1], '@' ) !== false && strpos( $link[1], '://' ) === false )
				{
					return __( 'E-Mail to:', 'avia_framework' ) . ' ' . $link[1];
				}

				return $link[1];
			}

			if( post_type_exists( $link[0] ) )
			{
				$obj = get_post_type_object( $link[0] );
				if( ! $obj instanceof WP_Post_Type )
				{
					return sprintf( __( 'Error: Post Type %s has been removed', 'avia_framework' ), $link[0] );
				}

				$include = $link;
				unset( $include[0] );

				$args = array(
						'orderby'	=> 'title',
						'order'		=> 'ASC',
						'include'	=> $include
					);

				$posts = get_posts( $args );

				$label = $obj->labels->singular_name . ':';
				if( empty( $include ) )
				{
					$label = $obj->labels->all_items . ':';
				}

				$result = array( $label );
				$ids = array_flip( $include );
				$prepend = count( $posts ) > 1 ? ' --- ' : '';

				foreach( $posts as $post )
				{
					unset( $ids[ $post->ID ] );
					$result[] = $prepend . avia_wp_get_the_title( $post );
				}

				foreach( $ids as $id => $value )
				{
					$result[] = $prepend . sprintf( __( ' *** Error: Post %d missing', 'avia_framework' ), $id );
				}
			}
			else if( taxonomy_exists( $link[0] ) )
			{
				$obj = get_taxonomy( $link[0] );
				if( ! $obj instanceof WP_Taxonomy )
				{
					return sprintf( __( 'Error: Taxonomy %s has been removed', 'avia_framework' ), $link[0] );
				}

				$include = $link;
				unset( $include[0] );

				$args = array(
						'taxonomy'		=> $link[0],
						'include'		=> $include,
						'hide_empty'	=> false
					);

				$terms = AviaHelper::get_terms( $args );

				$label = $obj->labels->singular_name . ':';
				if( empty( $include ) )
				{
					$label = $obj->labels->all_items . ':';
				}

				$result = array( $label );
				$ids = array_flip( $include );
				$prepend = count( $terms ) > 1 ? ' --- ' : '';

				foreach( $terms as $term )
				{
					unset( $ids[ $term->term_id ] );
					$result[] = $prepend . $term->name;
				}

				foreach( $ids as $id => $value )
				{
					$result[] = $prepend . sprintf( __( ' *** Error:  Term %d missing', 'avia_framework' ), $id );
				}
			}

			if( is_array( $result ) )
			{
				switch( count( $result ) )
				{
					case 0:
						$result = __( 'No link selected', 'avia_framework' );
						break;
					case 1:
						$result = $result[0];
						break;
					case 2:
						$result = $result[0] . ' ' . $result[1];
						break;
				}
			}

			return $result;
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
					return __( 'No attachment image available for: ', 'avia_framework' ) . esc_html( avia_wp_get_the_title( $post ) );
				}

				return __( 'Attachment image for: ', 'avia_framework' ) . esc_html( avia_wp_get_the_title( $post ) );
			}

			if( empty( $link[1] ) )
			{
				return __( 'Follow a manual added link', 'avia_framework' );
			}

			if( $link[0] == 'manually' )
			{
				if( strpos( $link[1], '@' ) !== false && strpos( $link[1], '://' ) === false )
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

				return __( 'Link to: ', 'avia_framework' ) . esc_html( avia_wp_get_the_title( $post ) );
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
		 * Fetches an entry based on a post type and id
		 *
		 * @since ???
		 * @since 4.8						added $additional_args
		 * @param string $entry
		 * @param array $additional_args
		 * @return WP_Post|false
		 */
		static public function get_entry( $entry, array $additional_args = array() )
		{
			$entry = explode( ',', $entry );

			if( empty( $entry[1] ) || 'manually' == $entry[0] || ! post_type_exists( $entry[0] ) )
			{
				return false;
			}

			$args = array(
						'numberposts'		=> 1,
						'include'			=> array( $entry[1] ),
						'post_type'			=> $entry[0],
						'suppress_filters'	=> false
					);

			if( ! empty( $additional_args ) )
			{
				$args = array_merge( $args, $additional_args );
			}

			/**
			 * Allows e.g. WPML to reroute to translated object
			 */
			$posts = get_posts( $args );

			if( empty( $posts ) )
			{
				return false;
			}

			return $posts[0];
		}

    	/**
    	 * Fetch all available sidebars
		 *
		 * @param array $sidebars
		 * @param array $exclude
		 * @return array
    	 */
    	static function get_registered_sidebars( $sidebars = array(), $exclude = array() )
    	{
    		//fetch all registered sidebars and save them to the sidebars array
			global $wp_registered_sidebars;

			foreach( $wp_registered_sidebars as $sidebar )
			{
				if( ! in_array( $sidebar['name'], $exclude ) )
				{
					$sidebars[ $sidebar['name'] ] = $sidebar['name'];
				}
			}

			return $sidebars;
    	}

		/**
		 *
		 * @since ????
		 * @param array|int $exclude
		 * @param boolean $enforce_both
		 * @param boolean $exclude_default
		 * @param boolean $equal_only
		 * @return array
		 */
    	static public function get_registered_image_sizes( $exclude = array(), $enforce_both = false, $exclude_default = false, $equal_only = false )
    	{
    		global $_wp_additional_image_sizes;

    		 // Standard sizes
	        $image_sizes = array(
						'no scaling'	=> array( 'width' => __( 'Original Width', 'avia_framework' ) . ' ', 'height' => ' ' . __( 'Original Height', 'avia_framework' ) ),
						'thumbnail'		=> array( 'width' => get_option( 'thumbnail_size_w' ), 'height' => get_option( 'thumbnail_size_h' ) ),
						'medium'		=> array( 'width' => get_option( 'medium_size_w' ), 'height' => get_option( 'medium_size_h' ) ),
						'large'			=> array( 'width' => get_option( 'large_size_w' ), 'height' => get_option( 'large_size_h' ) )
					);

	        if( ! empty( $exclude_default ) )
			{
				unset( $image_sizes['no scaling'] );
			}

	        if( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) )
			{
				$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes  );
			}

    		$result = array();
    		foreach( $image_sizes as $key => $image )
			{
				if( ( is_array( $exclude) && ! in_array( $key, $exclude ) ) || ( is_numeric( $exclude ) && ( $image['width'] > $exclude || $image['height'] > $exclude ) ) || ! is_numeric( $image['height'] ) )
				{
					if( $enforce_both === true && is_numeric( $image['height'] ) )
					{
						if( $image['width'] < $exclude || $image['height'] < $exclude)
						{
							continue;
						}
					}

					if( $equal_only === true )
					{
						if( ! is_numeric( $image['width'] ) || ! is_numeric( $image['height'] ) || (int) $image['width'] != (int) $image['height'] )
						{
							continue;
						}
					}

					$title = str_replace( '_', ' ', $key ) . ' (' . $image['width'] . 'x' . $image['height'] . ')';

					$result[ ucwords( $title ) ] = $key;
				}
			}

    		return $result;
    	}

		/**
		 * Wrapper for get_registered_image_sizes - adds an option to use image size selected in media library as a fallback
		 * (used to have a fallback for elements where this option had been added with and after 4.8.6.3)
		 *
		 * @since 4.8.6.3
		 * @param array|int $exclude
		 * @param boolean $enforce_both
		 * @param boolean $exclude_default
		 * @param boolean $equal_only
		 * @return array
		 */
    	static public function get_registered_img_sizes_media( $exclude = array(), $enforce_both = false, $exclude_default = false, $equal_only = false )
		{
			$media = array(
						__( 'Use size from media libary &quot;Attachment Display Settings&quot; ', 'avia_framework' )	=> ''
					);

			$sizes = AviaHelper::get_registered_image_sizes( $exclude, $enforce_both, $exclude_default, $equal_only );

			return array_merge( $media, $sizes );
		}

    	/**
		 *
		 * @return array
		 */
    	static public function list_menus()
    	{
			$term_args = array(
							'taxonomy'		=> 'nav_menu',
							'hide_empty'	=> false
						);

			$menus = AviaHelper::get_terms( $term_args );

    		$result = array();

    		if( ! empty( $menus ) )
    		{
	    		foreach( $menus as $menu )
	    		{
	    			$result[ $menu->name ] = $menu->term_id;
	    		}
    		}

    		return $result;
    	}


    	/**
    	 * is_ajax - Returns true when the page is loaded via ajax.
    	 */
    	static public function is_ajax()
    	{
    		if( defined( 'DOING_AJAX' ) )
			{
    			return true;
			}

    		return ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) ? true : false;
    	}


		/**
		 * function that gets called on backend pages and hooks other functions into wordpress
		 *
		 * @return void
		 */
		static public function backend()
		{
			add_action( 'print_media_templates', array( 'AviaHelper', 'print_templates' ) ); 		//create js templates for AviaBuilder Canvas Elements
		}

		/**
		 * Prints an array as js object. can call itself in case of nested arrays
		 *
		 * @param array $objects
		 * @param boolean $print
		 * @param string $passed
		 * @return string
		 */
		static public function print_javascript( $objects = array(), $print = true, $passed = '' )
		{
			$output = '';

			if( $print )
			{
				$output .=  "\n<script type='text/javascript' class='av-php-sent-to-frontend'>/* <![CDATA[ */ \n";
			}

			foreach( $objects as $key => $object )
			{
				if( is_array( $object ) )
					{
					if( empty( $passed ) )
					{
						$output .= "var {$key} = {};\n";
						$pass    = $key;
					}
					else
					{
						$output .= "{$passed}['{$key}'] = {};\n";
						$pass    = "{$passed}['{$key}']";
					}

					$output .= AviaHelper::print_javascript( $object, false, $pass );
				}
				else
				{
					if( ! is_numeric( $object ) && ! is_bool( $object ) )
					{
						$object = json_encode( $object );
					}

					if( empty( $object ) )
					{
						$object = 'false';
					}

					if( empty( $passed ) )
					{
						$output .= "var {$key} = {$object};\n";
					}
					else
					{
						$output .= "{$passed}['{$key}'] = {$object};\n";
					}
				}
			}

			if( $print )
			{
				$output .=  "\n /* ]]> */</script>\n\n";
				echo $output;
			}

			return $output;
		}


		/**
		 * Helper function that prints all the javascript templates
		 *
		 * @return void
		 */
		static public function print_templates()
		{
			foreach( self::$templates as $key => $template )
			{
				echo "\n<script type='text/html' id='avia-tmpl-{$key}'>\n";
				echo	$template;
				echo "\n</script>\n\n";
			}

			//reset the array
			self::$templates = array();
		}

		/**
		 * Creates a new javascript template to be called
		 *
		 * @param string $key
		 * @param string $html
		 */
		static public function register_template( $key, $html )
		{
			self::$templates[ $key ] = $html;
		}

		/**
		 * Fetches all "public" post types.
		 *
		 * @return array $post_types
		 */
		static public function public_post_types()
		{
			$args = array(
						'public'	=> false,
						'name'		=> 'attachment',
						'show_ui'	=> false,
						'publicly_queryable' => false
					);

			$post_types = get_post_types( $args, 'names', 'NOT' );

			$post_types['page'] = 'page';
			$post_types = array_map( 'ucfirst', $post_types );

			/**
			 * @param array $post_types
			 * @return array
			 */
			$post_types = apply_filters( 'avia_public_post_types', $post_types );

			self::$cache['post_types'] = $post_types;

			return $post_types;
		}


		/**
		 * Fetches all taxonomies attached to public post types.
		 *
		 * @param array|false $post_types
		 * @param boolean $merged
		 * @return array
		 */
		static public function public_taxonomies( $post_types = false, $merged = false )
		{
			$taxonomies = array();

			if( ! $post_types )
			{
				$post_types = empty( self::$cache['post_types'] ) ? self::public_post_types() : self::$cache['post_types'];
			}

			if( ! is_array( $post_types ) )
			{
				$post_types = array( $post_types => ucfirst( $post_types ) );
			}

			foreach( $post_types as $type => $post )
			{
				$taxonomies[ $type ] = get_object_taxonomies( $type );
			}

			/**
			 * @param array $post_types
			 * @return array
			 */
			$taxonomies = apply_filters( 'avia_public_taxonomies', $taxonomies );

			self::$cache['taxonomies'] = $taxonomies;

			if( $merged )
			{
				$new = array();
				foreach( $taxonomies as $taxonomy )
				{
					foreach( $taxonomy as $tax )
					{
						$new[ $tax ] = ucwords( str_replace( '_', ' ', $tax ) );
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
		 * @param array $data				example input: array('modal'=>'true')
		 * @return string $data_string		example output: data-modal='true'
		 */
		static public function create_data_string( $data = array() )
		{
			$data_string = '';

			foreach( $data as $key => $value )
			{
				if( is_array( $value ) )
				{
					$value = implode( ', ', $value );
				}

				$data_string .= " data-{$key}='" . esc_attr( $value ) . "' ";
			}

			return $data_string;
		}

		/**
		 * Create a lower case version of a string and sanatize it to be valid classnames.
		 * Space separated strings are kept as seperate to allow several classes
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

			if( in_array( $context, array( 'id', 'class' ) ) )
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
			else if( in_array( $context, array( 'element_title' ) ) )
			{
				$trans = array(
    					'\s+'					=> $replace,
    					'ä'						=> 'ae',
    					'ö'						=> 'oe',
    					'ü'						=> 'ue',
    					'Ä'						=> 'Ae',
    					'Ö'						=> 'Oe',
    					'Ü'						=> 'Ue',
    					'ß'						=> 'ss',
    					'[^a-zA-Z0-9\-_ /]'		=> ''
					);
			}
			else
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
    			$string = preg_replace( '#' . $key . '#i', $val, $string );
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
				preg_match_all( '/[a-z0-9]/s', $new_link, $sc_found, PREG_OFFSET_CAPTURE );

				if( empty( $sc_found ) || ! is_array( $sc_found ) || empty( $sc_found[0]) || ( $sc_found[0][0][1] != 0 ) )
				{
					$new_link = $default;
				}
			}

			/**
			 * @since 4.8
			 * @param string $new_link
			 * @param string $link
			 * @param string $replace
			 * @param string $default
			 * @return string
			 */
			return apply_filters( 'avf_valid_href', $new_link, $link, $replace, $default );
		}


    	/**
		 * Helper function that fetches the active value of the builder. also adds a filter
		 *
		 * @deprecated since version 4.2.1
		 */
		static public function builder_status( $post_ID )
		{
			_deprecated_function( 'builder_status', '4.2.1', 'AviaBuilder::get_alb_builder_status()');

			$status = get_post_meta( $post_ID, '_aviaLayoutBuilder_active', true );
			$status = apply_filters( 'avf_builder_active', $status, $post_ID );

			return $status;
		}

    	/**
		 * Helper function that builds css styling strings which are applied to html elements.
		 * $new_key allows to replace $key with a different value.
		 *
		 * @since ????
		 * @param array|string $atts
		 * @param string|false $key
		 * @param string|false $new_key
		 * @param string $append_value
		 * @return string
		 */
		static public function style_string( $atts, $key = false, $new_key = false, $append_value = '' )
		{
			$style_string = '';

			//finish the style string by wrapping the arguments into a style string
			if( ( is_string( $atts ) || ! $atts ) && false == $key )
			{
				if( ! empty( $atts ) )
				{
					$style_string = "style='" . $atts . "'";
				}
			}
			else //otherwise build only the styling argument
			{
				if( empty( $new_key ) )
				{
					$new_key = $key;
				}

				if( isset( $atts[ $key ] ) && $atts[ $key ] !== '' )
				{
					switch( $new_key )
					{
						case 'background-image':
							if( false !== strpos( $atts[ $key ], 'url(' ) )
							{
								$style_string = $new_key . ':' . $atts[ $key ] . $append_value . '; ';
							}
							else
							{
								$style_string = $new_key . ':url(' . $atts[ $key ] . $append_value . '); ';
							}
							break;
						case 'background-repeat':
							if( $atts[ $key ] == 'stretch' )
							{
								$atts[ $key ] = 'no-repeat';
							}

							$style_string = $new_key . ':' . $atts[ $key ] . $append_value . '; ';
							break;
						default:
							$style_string = $new_key . ':' . $atts[ $key ] . $append_value . '; ';
							break;
					}
				}
			}

			return $style_string;
		}


        /**
		 * Helper function for css declaration with 4 values such as margin/padding/border-radius
		 *
		 * @param string $value			padding/margin/border-radius
		 */
		static public function css_4value_helper( $value = '' )
		{
			if( ! empty ( $value ) )
			{
				$css = '';
				$explode_value = explode( ',', $value );

				foreach( $explode_value as $v )
				{
					$css .= ! empty( $v ) ? $v . ' ' : '0 ';
				}

				return $css;
			}
		}

        /**
         * Helper function that builds background css styling strings
         * Useful when there are multiple background settings like an image and a gradient
         * Returns a string to be used with the background property, e.g. style="background: $string";
         *
		 * @param array $bg_image		= array('url','position','repeat','attachment')
		 * @param array $bg_gradient	= array('direction','color1','color2')
		 *									direction: vertical, horizontal, radial, diagonal_tb, diagonal_bt
         *
         */
        static public function css_background_string( $bg_image = array(), $bg_gradient = array() )
		{
            $background = array();

            // bg image
			if( ! empty( $bg_image ) )
			{
				if( $bg_image['0'] !== '' )
				{

                    $background['image_string'] = array();

					$background['image_string'][] = 'url("' . $bg_image['0'] . '")';

                    // bg image position
					if( array_key_exists( '1', $bg_image ) )
					{
                        $background['image_string'][] = $bg_image['1'];
                    }
					else
					{
                        $background['image_string'][] = 'center';
                    }

                    // bg image repeat
					if( array_key_exists( '2', $bg_image ) )
					{
                        $background['image_string'][] = $bg_image['2'];
                    }
					else
					{
                        $background['image_string'][] = 'no-repeat';
                    }

                    // bg image attachment
					if( array_key_exists( '3', $bg_image ) )
					{
                        $background['image_string'][] = $bg_image['3'];
                    }
                }

            }

            // bg image css string
			if( ! empty ( $background['image_string'] ) )
			{
                $background['image_string'] = implode( ' ', $background['image_string'] );
            }

            // gradient
			if( ! empty( $bg_gradient ) && count ( $bg_gradient ) == 3 )
			{
				if( $bg_gradient['0'] !== '' )
				{
                    $background['gradient_string'] = array();

					switch( $bg_gradient['0'] )
					{
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
					if( ! empty( $background['gradient_string'] ) )
					{
						$background['gradient_string'][] .= $bg_gradient['1'] . ', ' . $bg_gradient['2'] . ')';
                    }
                }
            }

            // bg gradient css string
			if( ! empty( $background['gradient_string'] ) )
			{
                $background['gradient_string'] = implode( ' ', $background['gradient_string'] );
            }

			if( ! empty( $background ) )
			{
                $background = implode(', ', $background );

                return $background;
            }

			return false;
		}

		/**
		 *
		 * @since < 4.0
		 * @return string
		 */
        static public function backend_post_type()
		{
			global $post, $typenow, $current_screen;

			$posttype = '';

			//we have a post so we can just get the post type from that
			if( $post && $post->post_type )
			{
				$posttype = $post->post_type;
			}
			//check the global $typenow - set in admin.php
			else if( $typenow )
			{
				$posttype = $typenow;
			}
			//check the global $current_screen object - set in sceen.php
			else if( $current_screen && $current_screen->post_type )
			{
				$posttype = $current_screen->post_type;
			}
			//lastly check the post_type querystring
			else if( isset( $_REQUEST['post_type'] ) )
			{
				$posttype = sanitize_key( $_REQUEST['post_type'] );
			}

			return $posttype;
		}

		/**
		 * Returns an array of ALB font size classes for responsive design.
		 * Font Styles are compressed to necessary sizes and prepared for output in footer.
		 *
		 * @since 4.8.4
		 * Adding to header would be possible but as we have to take care of shortcodes outside content.
		 * This makes it complicate to figure out what has been put to header CSS and this might cause problems.
		 * Therefore we leave it in footer which is valid w3c since HTML 5.2 !!
		 *
		 *
		 * @since < 4.0
		 * @since 4.8				added $default - needed for custom element template overrides
		 * @param array $atts
		 * @param array $default
		 * @return array
		 */
		static public function av_mobile_sizes( $atts = array(), &$default = array() )
		{
			$result = array(
						'av_font_classes'		=> '',
						'av_title_font_classes'	=> '',
						'av_font_classes_1'		=> '',
						'av_font_classes_2'		=> '',
						'av_display_classes'	=> '',
						'av_column_classes'		=> ''
					);

			$fonts 		= array( 'av-medium-font-size', 'av-small-font-size', 'av-mini-font-size' );
			$title_fonts= array( 'av-medium-font-size-title', 'av-small-font-size-title', 'av-mini-font-size-title' );
			$fonts_1 	= array( 'av-medium-font-size-1', 'av-small-font-size-1', 'av-mini-font-size-1' );
			$fonts_2	= array( 'av-medium-font-size-2', 'av-small-font-size-2', 'av-mini-font-size-2' );
			$displays	= array( 'av-desktop-hide', 'av-medium-hide', 'av-small-hide', 'av-mini-hide' );
			$columns	= array( 'av-medium-columns', 'av-small-columns', 'av-mini-columns' );

			if( empty( $atts ) )
			{
				$atts = array();
			}

			foreach( $atts as $key => $attribute )
			{
				if( in_array( $key, $fonts ) && $attribute != '' )
				{
					$result['av_font_classes'] .= " {$key}-overwrite";
					$result['av_font_classes'] .= " {$key}-{$attribute}";

					if( $attribute != 'hidden' )
					{
						self::$mobile_styles['av_font_classes'][ $key ][ $attribute ] = $attribute;
					}
				}

				if( in_array( $key, $title_fonts ) && $attribute != '' )
				{
					$newkey = str_ireplace( '-title', '', $key );

					$result['av_title_font_classes'] .= " {$newkey}-overwrite";
					$result['av_title_font_classes'] .= " {$newkey}-{$attribute}";

					if( $attribute != 'hidden' )
					{
						self::$mobile_styles['av_font_classes'][ $newkey ][ $attribute ] = $attribute;
					}
				}

				if( ( in_array( $key, $fonts_1 ) || in_array( $key, $fonts_2 ) ) && $attribute != '' )
				{
					$ext = in_array( $key, $fonts_1 ) ? '1' : '2';

					$newkey = str_ireplace( "-$ext", '', $key );

					$result[ 'av_font_classes_' . $ext ] .= " {$newkey}-overwrite";
					$result[ 'av_font_classes_' . $ext ] .= " {$newkey}-{$attribute}";

					if( $attribute != 'hidden' )
					{
						self::$mobile_styles['av_font_classes'][ $newkey ][ $attribute ] = $attribute;
					}
				}

				if( in_array( $key, $displays ) && $attribute != '' )
				{
					$result['av_display_classes'] .= " {$key}";
				}

				if( in_array( $key, $columns ) && $attribute != '' )
				{
					$result['av_column_classes'] .= " {$key}-overwrite";
					$result['av_column_classes'] .= " {$key}-{$attribute}";
				}
			}

			if( empty( $default ) )
			{
				return $result;
			}

			/**
			 * Remove from defaults as not needed and to avoid backward conflicts
			 */
			$att_keys = array_merge( $fonts, $title_fonts, $fonts_1, $fonts_2, $displays, $columns );

			foreach( $att_keys as $att_key )
			{
				unset( $default[ $att_key ] );
			}

			return $result;
		}

		/**
		 * Return CSS for media queries in footer area. This is valid w3c since HTML 5.2
		 * Also see comment to av_mobile_sizes.
		 *
		 * @since < 4.0
		 * @return string
		 */
		static public function av_print_mobile_sizes()
		{
			$print = '';

			//rules are created dynamically, otherwise we would need to predefine more than 500 css rules of which probably only 2-3 would be used per page
			$media_queries = apply_filters( 'avf_mobile_font_size_queries', array(

							'av-medium-font-size' 	=> 'only screen and (min-width: 768px) and (max-width: 989px)',
							'av-small-font-size' 	=> 'only screen and (min-width: 480px) and (max-width: 767px)',
							'av-mini-font-size' 	=> 'only screen and (max-width: 479px)'
						));


			if( isset( self::$mobile_styles['av_font_classes'] ) && is_array( self::$mobile_styles['av_font_classes'] ) )
			{
				$print .= "<style type='text/css'>\n";

				foreach( $media_queries as $key => $query )
				{
					if( isset( self::$mobile_styles['av_font_classes'][ $key ] ) )
					{
						$print .= "@media {$query} { \n";

						foreach( self::$mobile_styles['av_font_classes'][ $key ] as $size )
						{
							$print .= ".responsive #top #wrap_all .{$key}-{$size}{font-size:{$size}px !important;} \n";
						}

						$print .= "} \n";
					}
				}

				$print .= "</style>\n";
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
		static public function date_query_dates( array $query, $start_date, $end_date = '', $format = 'yy/mm/dd', $relation = '' )
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
		 * Returns a WP date query arg for WP_Query class.
		 *
		 * @since 4.8.7
		 * @param array $query
		 * @param array $args
		 * @return array
		 */
		static public function date_query( array $query, array $args )
		{
			if( ! isset( $args['date_filter'] ) || ! in_array( $args['date_filter'], array( 'date_filter', 'period_filter' ) ) )
			{
				return $query;
			}

			if( 'date_filter' == $args['date_filter'] )
			{
				$date_filter_start = isset( $args['date_filter_start'] ) ? $args['date_filter_start'] : '';
				$date_filter_end = isset( $args['date_filter_end'] ) ? $args['date_filter_end'] : '';
				$date_filter_format = isset( $args['date_filter_format'] ) ? $args['date_filter_format'] : 'yy/mm/dd';

				return AviaHelper::date_query_dates( $query, $date_filter_start, $date_filter_end, $date_filter_format );
			}

			$p1 = isset( $args['period_filter_unit_1'] ) ? $args['period_filter_unit_1'] : '';
			$p2 = isset( $args['period_filter_unit_2'] ) ? $args['period_filter_unit_2'] : '';

			if( empty( $p2 ) )
			{
				return $query;
			}

			if( ! is_numeric( $p1 ) )
			{
				$p1 = 1;
			}

			$query[] = array(
							'column' => 'post_date',
							'after'  => "{$p1} {$p2} ago",
						);

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

		/**
		 * Scans an array and returns key and value unslashed.
		 *
		 * @since 4.8
		 * @param array $data
		 * @return array
		 */
		static public function unslash_array( array $data )
		{
			$unslashed = array();

			foreach( $data as $key => $value )
			{
				$unslashed[ wp_unslash( $key ) ] = wp_unslash( $value );
			}

			return $unslashed;
		}


		/**
		 * Removes the aviaTB prefix in option key and unlashes key and value -
		 * can be found in raw attribute array returned in a js callback from modal popup window
		 *
		 * @since 4.8
		 * @param array $attr
		 * @return array
		 */
		static public function clean_attributes_array( array $attr )
		{
			$new_attr = array();

			foreach( $attr as $key => &$value )
			{
				$key = str_replace( 'aviaTB', '', $key );
				$new_attr[ $key ] = $value;
			}

			unset( $value );

			return AviaHelper::unslash_array( $new_attr );
		}

		/**
		 * Wrapper to avoid isset() and return a valid value from array or default if empty()
		 *
		 * @since 4.8.4
		 * @param array $date
		 * @param string $key
		 * @param mixed $default
		 * @param mixed|string $not_empty				'not_empty' to check for not empty value
		 * @return mixed
		 */
		static public function array_value( array &$data, $key, $default = '', $not_empty = false )
		{
			if( ! isset( $data[ $key ] ) )
			{
				return $default;
			}

			$value = $data[ $key ];
			if( $not_empty != 'not_empty' )
			{
				return $value;
			}


			return ! empty( $value ) ? $value : $default;
		}

	}

}
