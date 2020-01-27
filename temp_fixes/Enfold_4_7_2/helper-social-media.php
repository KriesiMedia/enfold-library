<?php
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

######################################################################
# social icon builder
######################################################################

if( ! function_exists( 'avia_social_media_icons' ) )
{
	/**
	 * Wrapper function for social media icons
	 * 
	 * @since < 4.0
	 * @param array $args
	 * @param boolean $echo
	 * @param array $post_data
	 * @return string
	 */
	function avia_social_media_icons( $args = array(), $echo = true, $post_data = array() )
	{
		$icons = new avia_social_media_icons( $args, $post_data );
		
		$html = $icons->html();
		if( $echo ) 
		{	
			echo $html;
		}
		
		return $html;
	}
}



if( ! class_exists( 'avia_social_media_icons' ) )
{
	class avia_social_media_icons
	{
		/**
		 * @since < 4.0
		 * @var array 
		 */
		protected $args;
		
		/**
		 * @since < 4.0
		 * @var array 
		 */
		protected $post_data;
		
		/**
		 * @since < 4.0
		 * @var array 
		 */
		protected $icons;
		
		/**
		 * @since < 4.0
		 * @var string 
		 */
		protected $html;
		
		/**
		 * @since < 4.0
		 * @var int 
		 */
		protected $counter;

	
		/**
		 * Initialize the variables necessary for all social media links
		 * 
		 * @since < 4.0
		 * @param array $args
		 * @param array $post_data
		 */
		public function __construct( $args = array(), $post_data = array() )
		{
			$default_arguments = array( 'outside'=>'ul', 'inside'=>'li', 'class' => 'social_bookmarks', 'append' => '' );
			$this->args = array_merge( $default_arguments, $args );

			$this->post_data = $post_data;
			$this->icons = apply_filters( 'avia_filter_social_icons', avia_get_option( 'social_icons' ) );
			$this->html = '';
			$this->counter = 1;
		}
		
		/**
		 * @since 4.5.6
		 */
		public function __destruct() 
		{
			unset( $this->args );
			unset( $this->post_data );
			unset( $this->icons );
		}

		/**
		 * Builds the html string for a single item, with a few options for special items like rss feeds
		 * 
		 * @since < 4.0
		 * @param array $icon
		 * @return string
		 */
		protected function build_icon( $icon )
		{
			global $avia_config;
					
			$display_name = ucfirst( $icon['social_icon'] );
			if( ! empty( $avia_config['font_icons'][ $icon['social_icon'] ]['display_name'] ) )
			{
				$display_name = $avia_config['font_icons'][ $icon['social_icon'] ]['display_name'];
			}
			
			$aria_label = sprintf( __( 'Link to %s', 'avia_framework' ), $display_name );
			
			/**
			 * special cases
			 */
			switch( $icon['social_icon'] )
			{
				case 'rss':  
					if( empty( $icon['social_icon_link'] ) )
					{
						$icon['social_icon_link'] = get_bloginfo( 'rss2_url' );
						$aria_label .= ' ' . __( ' this site', 'avia_framework' );
					}
					break;
				case 'twitter':
				case 'dribbble':
				case 'vimeo':
				case 'behance':
					if( strpos( $icon['social_icon_link'], 'http' ) === false && ! empty( $icon['social_icon_link'] ) )
					{
						/**
						 * Protocoll changed with 4.5.6 to https. Allow to filter in case http is needed
						 * 
						 * @since 4.5.6
						 * @return string
						 */
						$protocol = apply_filters( 'avf_social_media_icon_protocol', 'https', $icon );
						$icon['social_icon_link'] = "{$protocol}://{$icon['social_icon']}.com/{$icon['social_icon_link']}/";
					}
					break;
			}

			if( empty( $icon['social_icon_link'] ) ) 
			{
				$icon['social_icon_link'] = '#';
			}
			
			//dont add target blank to relative urls or urls to the same domain
			$blank = ( strpos( $icon['social_icon_link'], 'http') === false || strpos( $icon['social_icon_link'], home_url() ) === 0 ) ? '' : ' target="_blank"';
			
			/**
			 * @since 4.5.6
			 * @return string
			 */
			$aria_label = apply_filters( 'avf_social_media_icon_aria_label_value', $aria_label, $icon );
			if( ! empty( $aria_label ) )
			{
				$aria_label = 'aria-label="' . esc_attr( $aria_label ) . '"';
			}
			
			$html  = '';
			$html .= "<{$this->args['inside']} class='{$this->args['class']}_{$icon['social_icon']} av-social-link-{$icon['social_icon']} social_icon_{$this->counter}'>";
			$html .=	"<a {$blank} {$aria_label} href='" . esc_url( $icon['social_icon_link'] ) . "' " . av_icon_string( $icon['social_icon'], false ) . " title='{$display_name}'>";
			$html .=		"<span class='avia_hidden_link_text'>{$display_name}</span>";
			$html .=	'</a>';
			
			$html .= "</{$this->args['inside']}>";

			$html = avia_targeted_link_rel( $html );
			return $html;
		}

		/**
		 * Builds the html, based on the available icons
		 * 
		 * @since < 4.0
		 * @return string
		 */
		public function html()
		{
			$this->html = '';
			
			if( ! empty( $this->icons ) )
			{
				$this->html .= "<{$this->args['outside']} class='noLightbox {$this->args['class']} icon_count_" . count( $this->icons ) . "'>";

				foreach ( $this->icons as $icon )
				{
					if( ! empty( $icon['social_icon'] ) )
					{
						$this->html .= $this->build_icon( $icon );
						$this->counter ++;
					}
				}

				$this->html .= $this->args['append'];
				$this->html .= "</{$this->args['outside']}>";
			}

			return $this->html;
		}
	}
}




######################################################################
# share link builder
######################################################################


if( ! class_exists( 'avia_social_share_links' ) )
{
	class avia_social_share_links
	{
		/**
		 * @since < 4.0
		 * @var array 
		 */
		protected $args;
		
		/**
		 * @since < 4.0
		 * @var array 
		 */
		protected $options;
		
		/**
		 * @since < 4.0
		 * @var string 
		 */
		protected $title;
		
		/**
		 * @since < 4.0
		 * @var array 
		 */
		protected $links;
		
		/**
		 * @since < 4.0
		 * @var string 
		 */
		protected $html;
		
		/**
		 * @since < 4.0
		 * @var int 
		 */
		protected $counter;
		
		/**
		 *
		 * @since 4.5.7.1
		 * @var array 
		 */
		protected $post_data;
		
		
		/**
		 * Initialize the variables necessary for all social media links
		 * 
		 * @since < 4.0
		 * @param array $args
		 * @param array|false $options
		 * @param string|false $title
		 */
		public function __construct( $args = array(), $options = false, $title = false )
		{
			
			$default_arguments = array
			(
				'facebook' 	=> array(
								'encode'		=> true, 
								'encode_urls'	=> false, 
								'pattern'		=> 'https://www.facebook.com/sharer.php?u=[permalink]&amp;t=[title]'
							),
				'twitter' 	=> array(
								'encode'		=> true, 
								'encode_urls'	=> false, 
								'pattern'		=> 'https://twitter.com/share?text=[title]&url=[shortlink]'
							),
				'whatsapp'	=> array(
								'encode'		=> true, 
								'encode_urls'	=> false, 
								'pattern'		=> 'https://api.whatsapp.com/send?text=[permalink]', 
								'label'			=> __( 'Share on WhatsApp', 'avia_framework' ) 
							),
				'pinterest' => array(
								'encode'		=> true, 
								'encode_urls'	=> true, 
								'pattern'		=> 'https://pinterest.com/pin/create/button/?url=[permalink]&amp;description=[title]&amp;media=[thumbnail]'
							),
				'linkedin' 	=> array(
								'encode'		=> true, 
								'encode_urls'	=> false, 
								'pattern'		=> 'https://linkedin.com/shareArticle?mini=true&amp;title=[title]&amp;url=[permalink]' 
							),
				'tumblr' 	=> array(
								'encode'		=> true, 
								'encode_urls'	=> true, 
								'pattern'		=> 'https://www.tumblr.com/share/link?url=[permalink]&amp;name=[title]&amp;description=[excerpt]'
							),
				'vk' 		=> array(
								'encode'		=> true, 
								'encode_urls'	=> false, 
								'pattern'		=> 'https://vk.com/share.php?url=[permalink]'
							),
				'reddit' 	=> array(
								'encode'		=> true, 
								'encode_urls'	=> false, 
								'pattern'		=> 'https://reddit.com/submit?url=[permalink]&amp;title=[title]'
							),
				'mail' 		=> array(
								'encode'		=> true, 
								'encode_urls'	=> false, 
								'pattern'		=> 'mailto:?subject=[title]&amp;body=[permalink]', 
								'label'			=> __( 'Share by Mail', 'avia_framework' ) 
							),
				'yelp' 		=> $this->default_yelp_link( $args, $options, $title )
			);
			
			$this->args = apply_filters( 'avia_social_share_link_arguments', array_merge( $default_arguments, $args ) );
			
			$this->options = ( empty( $options ) ) ? avia_get_option() : $options;
			$this->title = $title !== false ? $title : __( 'Share this entry', 'avia_framework' );
			$this->links = array();
			$this->html = '';
			$this->counter = 0;
			$this->post_data = array();
			
			/**
			 * Create a valid options entry for theme icons in case a new checkbox option for an icon has been created and 
			 * options have not been saved - in this case we hide it.
			 * Was introduced with adding yelp 4.7.2.1, can be removed in a future release.
			 * 
			 * This allows to add icons via filter and show them
			 */
			if( ! isset( $this->options['share_yelp'] ) )
			{
				$this->options['share_yelp'] = 'disabled';
			}
			
			$this->build_share_links();
		}
		
		/**
		 * @since 4.5.6
		 */
		public function __destruct() 
		{
			unset( $this->args );
			unset( $this->options );
			unset( $this->links );
			unset( $this->post_data );
		}
		
		
		/**
		 * Initialize and filter the default yelp array.
		 * If $options = false or no link provided in $options fallback to
		 * link in theme options or yelp homepage
		 * 
		 * @since 4.6.4
		 * @param array $args
		 * @param array|false $options
		 * @param string|false $title
		 * @return array
		 */
		protected function default_yelp_link( $args, $options, $title ) 
		{
			/**
			 * Fallback URL used if no url specified in theme options "Social Profiles" or in custom social share button
			 * 
			 * @since 4.7.1.1
			 * @param string
			 * @return string
			 */
			$pattern = apply_filters( 'avf_default_yelp_url', 'https://www.yelp.com' );
			
			$icons = apply_filters( 'avia_filter_social_icons', avia_get_option( 'social_icons' ) );
			foreach( $icons as $icon ) 
			{
				if( 'yelp' == $icon['social_icon'] )
				{
					if( ! empty( $icon['social_icon_link'] ) )
					{
						$pattern = esc_url( $icon['social_icon_link'] );
					}
					
					break;
				}
			}
			
			if( is_array( $options ) && ( 'disabled' != $options['share_yelp'] ) && ( 'disabled' != $options['yelp_link'] ) )
			{
				$pattern = esc_url( $options['yelp_link'] );
			}
			
			$default = array(
						'encode'		=> false, 
						'encode_urls'	=> false, 
						'pattern'		=> $pattern, 
						'label'			=> __( 'Visit us on Yelp','avia_framework' ) 
					);
			
			/**
			 * Filter the default yelp array
			 * e.g. allow on blogposts is_single() to change link to post specific yelp page
			 * 
			 * @since 4.6.4
			 * @param array $default
			 * @param array $args
			 * @param array|false $options
			 * @param string|false $title
			 * @return array
			 */
			$default = apply_filters( 'avf_default_yelp_link', $default, $args, $options, $title );
			
			return $default;
		}
		
		/**
		 * Filter social icons that are disabled in the backend. everything that is left will be displayed.
		 * That way the user can hook into the 'avia_social_share_link_arguments' filter above and add new social icons 
		 * without the need to add a new backend option
		 * 
		 * @since < 4.0
		 */
		protected function build_share_links()
		{
			$replace = array();
			
			$thumb 					= wp_get_attachment_image_src( get_post_thumbnail_id(), 'masonry' );
			$replace['permalink'] 	= ! isset( $this->post_data['permalink'] ) ? get_permalink() : $this->post_data['permalink'];
			$replace['title'] 		= ! isset( $this->post_data['title'] ) ? get_the_title() : $this->post_data['title'];
			$replace['excerpt'] 	= ! isset( $this->post_data['excerpt'] ) ? get_the_excerpt() : $this->post_data['excerpt'];
			$replace['shortlink']	= ! isset( $this->post_data['shortlink'] ) ? wp_get_shortlink() : $this->post_data['shortlink'];
			$replace['thumbnail']	= is_array( $thumb ) && isset( $thumb[0] ) ? $thumb[0] : '';
			$replace['thumbnail']	= ! isset( $this->post_data['thumbnail'] ) ? $replace['thumbnail'] : $this->post_data['thumbnail'];
			
			$replace = apply_filters( 'avia_social_share_link_replace_values', $replace );
			$charset = get_bloginfo( 'charset' );
			
			foreach( $this->args as $key => $share )
			{
				$share_key  = 'share_' . $key;
				$url 		= $share['pattern'];
				
				if( isset( $this->options[ $share_key ] ) && $this->options[ $share_key ] == 'disabled' ) 
				{
					continue;
				}
				
				foreach( $replace as $replace_key => $replace_value )
				{
					if( ! empty( $share['encode'] ) && $replace_key != 'shortlink' && $replace_key != 'permalink' ) 
					{
						$replace_value = rawurlencode( html_entity_decode( $replace_value, ENT_QUOTES, $charset ) );
					}
					
					if( ! empty( $share['encode_urls'] ) && ( $replace_key == 'shortlink' || $replace_key == 'permalink') ) 
					{
						$replace_value = rawurlencode( html_entity_decode( $replace_value, ENT_QUOTES, $charset ) );
					}
					
					$url = str_replace( "[{$replace_key}]", $replace_value, $url );
				}
				
				$this->args[ $key ]['url'] = $url;
				$this->counter ++;
			}
		}
		
		/**
		 * Builds the html, based on the available urls
		 * 
		 * @since < 4.0
		 * @return string
		 */
		public function html()
		{
			global $avia_config;
			
			$this->html = '';
			
			if( $this->counter == 0 ) 
			{
				return $this->html;
			}
			
			$this->html .=	"<div class='av-share-box'>";
			if( $this->title )
			{

				$default_heading = 'h5';
				$args = array(
							'heading'		=> $default_heading,
							'extra_class'	=> ''
						);

				$extra_args = array( $this, 'title' );

				/**
				 * @since 4.5.7.1
				 * @return array
				 */
				$args = apply_filters( 'avf_customize_heading_settings', $args, __CLASS__, $extra_args );				
				
				$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
				$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';
				
				$this->html .= 		"<{$heading} class='av-share-link-description av-no-toc {$css}'>";
				$this->html .=			apply_filters( 'avia_social_share_title', $this->title , $this->args );
				$this->html .= 		"</{$heading}>";
			}
			$this->html .= 		"<ul class='av-share-box-list noLightbox'>";
			
			foreach( $this->args as $key => $share )
			{
				if( empty( $share['url'] ) ) 
				{
					continue;
				}
				
				$icon = isset( $share['icon'] ) ? $share['icon'] : $key;
				
				$source = ucfirst( $key );
				if( ! empty( $avia_config['font_icons'][ $key ]['display_name'] ) )
				{
					$source = $avia_config['font_icons'][ $key ]['display_name'];
				}
				
				$name = isset( $share['label'] )? $share['label']: __( 'Share on','avia_framework') . ' ' . $source;
				$blank = strpos( $share['url'], 'mailto' ) !== false ? '' : 'target="_blank"';
				
				/**
				 * @since 4.5.6
				 * @return string
				 */
				$aria_label = apply_filters( 'avf_social_share_links_aria_label_value', $name, $key, $share, $this );
				if( ! empty( $aria_label ) )
				{
					$aria_label = 'aria-label="' . esc_attr( $aria_label ) . '"';
				}
				
				$this->html .=		"<li class='av-share-link av-social-link-{$key}' >";
				$this->html .=			"<a {$blank} {$aria_label} href='" . esc_url( $share['url'] ) . "' " . av_icon_string( $icon, false ) . " title='' data-avia-related-tooltip='{$name}'>";
				$this->html .=				"<span class='avia_hidden_link_text'>{$name}</span>";
				$this->html .=			'</a>';
				$this->html .=		'</li>';
			}
			
			$this->html .= 		'</ul>';
			$this->html .=	'</div>';
			
			$this->html = avia_targeted_link_rel( $this->html );
			return $this->html;
		}
		
	}
}

if( ! function_exists('avia_social_share_links') )
{
	/**
	 * Wrapper function for social share links
	 * 
	 * @param array $args
	 * @param type $options
	 * @param type $title
	 * @param type $echo
	 * @return string
	 */
	function avia_social_share_links( $args = array(), $options = false, $title = false, $echo = true )
	{
		$icons = new avia_social_share_links( $args, $options, $title );
		
		$html = $icons->html();
		if( $echo ) 
		{	
			echo $html;
		}
		
		return $html;
	}
}


