<?php
/**
 * 2 classes to manage social media icons and share entry links
 * ============================================================
 *
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_social_media_icons' ) )
{
	/**
	 * Social Media Icon builder
	 *
	 * @since ???
	 */
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
			$default_arguments = array(
							'outside'	=> 'ul',
							'inside'	=> 'li',
							'class'		=> 'social_bookmarks',
							'append'	=> ''
						);

			$this->args = array_merge( $default_arguments, $args );

			$this->post_data = $post_data;
			$this->icons = apply_filters( 'avia_filter_social_icons', avia_get_option( 'social_icons' ) );
			$this->html = '';
			$this->counter = 1;

			$this->validate_urls();
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
		 * Returns the array of social icons defined in theme options (or filtered on creating class)
		 *
		 * @since 4.8.3
		 * @return array
		 */
		public function get_icons()
		{
			return $this->icons;
		}

		/**
		 * Returns the social profile info from theme options settings
		 *
		 * @since 4.8.3
		 * @param string $key
		 * @return array|false
		 */
		public function get_icon( $key )
		{
			foreach( $this->icons as $icon )
			{
				if( isset( $icon['social_icon'] ) && $key == $icon['social_icon'] )
				{
					return $icon;
				}
			}

			return false;
		}

		/**
		 * Handle special URL cases
		 *
		 * @since 4.8.3
		 */
		protected function validate_urls()
		{
			foreach ( $this->icons as &$icon )
			{
				switch( $icon['social_icon'] )
				{
					case 'rss':
						if( empty( $icon['social_icon_link'] ) )
						{
							$icon['social_icon_link'] = get_bloginfo( 'rss2_url' );
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
			}

			unset( $icon );
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

			if( 'rss' == $icon['social_icon'] )
			{
				$aria_label .= ' ' . __( ' this site', 'avia_framework' );
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

			/**
			 * Change tooltip titls for icon
			 *
			 * @since 4.8.7
			 * @param string $display_name
			 * @param array $icon
			 * @return string
			 */
			$display_name = apply_filters( 'avf_social_media_icon_display_name', $display_name, $icon );

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




if( ! class_exists( 'avia_social_share_links' ) )
{
	/**
	 * Share link builder class
	 *
	 * @since ???
	 * @since 4.8.3			extended to support link to social profiles and custom profile link set in ALB element "Social Buttons"
	 */
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
		 *
		 * @since 4.8.3
		 * @var avia_social_media_icons
		 */
		protected $social_media_icons;


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

			$default_arguments = array(

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
								'label'			=> __( 'Share on WhatsApp', 'avia_framework' ),
								'label_profile'	=> __( 'Link to WhatsApp', 'avia_framework' )
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
				'telegram' 		=> $this->default_telegram_link( $args, $options, $title ),

				'mail' 		=> array(
								'encode'		=> true,
								'encode_urls'	=> false,
								'pattern'		=> 'mailto:?subject=[title]&amp;body=[permalink]',
								'label'			=> __( 'Share by Mail', 'avia_framework' )
							),
				'yelp' 		=> $this->default_yelp_link( $args, $options, $title ),

				'five_100_px' => array(
								'encode'		=> false,
								'encode_urls'	=> false,
								'profile_only'	=> true,
								'label_profile'	=> __( 'Link to 500px', 'avia_framework' )
							),
				'behance'	=> array(
								'encode'		=> false,
								'encode_urls'	=> false,
								'profile_only'	=> true
							),
				'dribbble'	=> array(
								'encode'		=> false,
								'encode_urls'	=> false,
								'profile_only'	=> true
							),
				'flickr'	=> array(
								'encode'		=> false,
								'encode_urls'	=> false,
								'profile_only'	=> true
							),
				'instagram'	=> array(
								'encode'		=> false,
								'encode_urls'	=> false,
								'profile_only'	=> true
							),
				'skype'		=> array(
								'encode'		=> false,
								'encode_urls'	=> false,
								'profile_only'	=> true
							),
				'soundcloud' => array(
								'encode'		=> false,
								'encode_urls'	=> false,
								'profile_only'	=> true
							),
				'vimeo'		=> array(
								'encode'		=> false,
								'encode_urls'	=> false,
								'profile_only'	=> true
							),
				'xing'		=> array(
								'encode'		=> false,
								'encode_urls'	=> false,
								'profile_only'	=> true
							),
				'youtube'	=> array(
								'encode'		=> false,
								'encode_urls'	=> false,
								'profile_only'	=> true
							)
			);

			$this->args = apply_filters( 'avia_social_share_link_arguments', array_merge( $default_arguments, $args ) );

			$this->options = ( empty( $options ) ) ? avia_get_option() : $options;
			$this->title = $title !== false ? $title : __( 'Share this entry', 'avia_framework' );
			$this->links = array();
			$this->html = '';
			$this->counter = 0;
			$this->post_data = array();
			$this->social_media_icons = new avia_social_media_icons();

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

			if( ! isset( $this->options['share_telegram'] ) )
			{
				$this->options['share_telegram'] = 'disabled';
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
			unset( $this->social_media_icons );
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
		 * Initialize and filter the default Telegram array.
		 * If $options = false or no link provided in $options fallback to
		 * link in theme options or telegram homepage
		 *
		 * @since 5.1.3
		 * @param array $args
		 * @param array|false $options
		 * @param string|false $title
		 * @return array
		 */
		protected function default_telegram_link( $args, $options, $title )
		{
			/**
			 * Fallback URL used if no url specified in theme options "Social Profiles" or in custom social share button
			 *
			 * @since 5.1.3
			 * @param string
			 * @return string
			 */
			$pattern = apply_filters( 'avf_default_telegram_url', 'https://telegram.org/' );

			$icons = apply_filters( 'avia_filter_social_icons', avia_get_option( 'social_icons' ) );
			foreach( $icons as $icon )
			{
				if( 'telegram' == $icon['social_icon'] )
				{
					if( ! empty( $icon['social_icon_link'] ) )
					{
						$pattern = esc_url( $icon['social_icon_link'] );
					}

					break;
				}
			}

			if( is_array( $options ) && ( 'disabled' != $options['share_telegram'] ) && ( 'disabled' != $options['telegram_link'] ) )
			{
				$pattern = esc_url( $options['telegram_link'] );
			}

			$default = array(
						'encode'		=> true,
						'encode_urls'	=> false,
						'pattern'		=> 'https://t.me/share/url?url=[permalink]&amp;text=[title]'
					);

			/**
			 * Filter the default telegram array
			 * e.g. allow on blogposts is_single() to change link to post specific Telegram page
			 *
			 * @since 5.1.3
			 * @param array $default
			 * @param array $args
			 * @param array|false $options
			 * @param string|false $title
			 * @return array
			 */
			$default = apply_filters( 'avf_default_telegram_link', $default, $args, $options, $title );

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
			$thumb = wp_get_attachment_image_src( get_post_thumbnail_id(), 'masonry' );

			$replace['permalink'] = ! isset( $this->post_data['permalink'] ) ? get_permalink() : $this->post_data['permalink'];
			$replace['title'] = ! isset( $this->post_data['title'] ) ? get_the_title() : $this->post_data['title'];
			$replace['excerpt'] = ! isset( $this->post_data['excerpt'] ) ? get_the_excerpt() : $this->post_data['excerpt'];
			$replace['shortlink'] = ! isset( $this->post_data['shortlink'] ) ? wp_get_shortlink() : $this->post_data['shortlink'];

			$replace['thumbnail'] = is_array( $thumb ) && isset( $thumb[0] ) ? $thumb[0] : '';
			$replace['thumbnail'] = ! isset( $this->post_data['thumbnail'] ) ? $replace['thumbnail'] : $this->post_data['thumbnail'];

			$replace = apply_filters( 'avia_social_share_link_replace_values', $replace );
			$charset = get_bloginfo( 'charset' );

			foreach( $this->args as $key => $share )
			{
				$share_key = 'share_' . $key;
				$profile_key = $key . '_profile';

				$this->args[ $key ]['url'] = false;
				$this->args[ $key ]['profile'] = false;

				/*
				 * 'avia_social_share_link_arguments' filter might add share links that do not exist in "Blog Layout" section as checkbox.
				 * Therefore we only check for unchecked if the checkbox exists.
				 */
				if( isset( $this->options[ $share_key ] ) && $this->options[ $share_key ] == 'disabled' )
				{
					continue;
				}

				$url = isset( $share['pattern'] ) ? $share['pattern'] : '';

				if( ! empty( $url ) )
				{
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

					$this->args[ $key ]['url'] = ! empty( $url ) ? $url : false;
				}

				//	set profile link
				if( isset( $this->options[ $profile_key ] ) && ! empty( $this->options[ $profile_key ] ) )
				{
					$this->args[ $key ]['profile'] = trim( $this->options[ $profile_key ] );
				}
				else
				{
					$icon = $this->social_media_icons->get_icon( $key );
					if( false !== $icon )
					{
						$this->args[ $key ]['profile'] = $icon['social_icon_link'];
					}
				}

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

			$this->html .=	'<div class="av-share-box">';
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

			$this->html .= 		'<ul class="av-share-box-list noLightbox">';


			$buttons = $this->sort_buttons( $this->args );

			foreach( $buttons as $key => $share )
			{
				$select_profile = false;
				if( isset( $this->options['btn_action'] ) && 'profile' == $this->options['btn_action'] )
				{
					$select_profile = true;
				}
				else if( isset( $share['profile_only'] ) && true === $share['profile_only'] )
				{
					$select_profile = true;
				}

				if( $select_profile )
				{
					$url = isset( $share['profile'] ) ? $share['profile'] : false;
				}
				else
				{
					$url = isset( $share['url'] ) ? $share['url'] : false;
				}

				if( empty( $url ) )
				{
					continue;
				}

				$icon = isset( $share['icon'] ) ? $share['icon'] : $key;

				$source = ucfirst( $key );
				if( ! empty( $avia_config['font_icons'][ $key ]['display_name'] ) )
				{
					$source = $avia_config['font_icons'][ $key ]['display_name'];
				}


				$label_key = $select_profile ? 'label_profile' : 'label';

				if( isset( $share[ $label_key ] ) && ! empty( $share[ $label_key ] ) )
				{
					$name = $share[ $label_key ];
				}
				else if( $select_profile )
				{
					$name = __( 'Link to','avia_framework') . ' ' . $source;
				}
				else
				{
					$name = __( 'Share on','avia_framework') . ' ' . $source;
				}

				$name = esc_attr( $name );

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
				$this->html .=			"<a {$blank} {$aria_label} href='" . esc_url( $url ) . "' " . av_icon_string( $icon, false ) . " title='' data-avia-related-tooltip='{$name}'>";
				$this->html .=				"<span class='avia_hidden_link_text'>{$name}</span>";
				$this->html .=			'</a>';
				$this->html .=		'</li>';
			}

			$this->html .= 		'</ul>';
			$this->html .=	'</div>';

			$this->html = avia_targeted_link_rel( $this->html );
			return $this->html;
		}

		/**
		 * Sort buttons using a filter
		 *
		 * @since 4.8.3
		 * @param array $buttons
		 * @return array
		 */
		protected function sort_buttons( array $buttons )
		{
			/**
			 * Return an array with button keys in the desired order. These buttons are placed first, then the other follow as defined in $this->args
			 *
			 * @since 4.8.3
			 * @param array $sort_array
			 * @param array $this->options
			 * @param avia_social_share_links $this
			 * @return array|false
			 */
			$sort_buttons = apply_filters( 'avf_social_share_buttons_order', [], $this->options, $this );

			if( ! is_array( $sort_buttons ) || empty( $sort_buttons ) )
			{
				return $buttons;
			}

			$new_array = [];

			foreach( $sort_buttons as $key )
			{
				if( ! isset( $buttons[ $key ] ) )
				{
					continue;
				}

				$new_array[ $key ] = $buttons[ $key ];
				unset( $buttons[ $key ] );
			}

			return array_merge( $new_array, $buttons );
		}

	}
}

if( ! function_exists( 'avia_social_share_links' ) )
{
	/**
	 * Wrapper function for social share links
	 *
	 * @param array $args
	 * @param array|false $options
	 * @param string|false $title
	 * @param boolean $echo
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

if( ! function_exists( 'avia_social_share_links_single_post' ) )
{
	/**
	 * Wrapper function for social share links on a single post page
	 *
	 * @since 4.8.3
	 * @return string
	 */
	function avia_social_share_links_single_post( $echo = true )
	{
		$custom_class = array();

		$style = avia_get_option( 'single_post_share_buttons_style' );
		$alignment = avia_get_option( 'single_post_share_buttons_alignment' );

		if( '' == $style )
		{
			$style = 'av-social-sharing-box-default';
		}
		else if( 'minimal' == $style )
		{
			$style = 'av-social-sharing-box-minimal';
		}

		$custom_class[] = $style;

		if( ! in_array( $style, array( 'av-social-sharing-box-default', 'av-social-sharing-box-minimal', 'av-social-sharing-box-icon', 'av-social-sharing-box-icon-simple' ) ) )
		{
			$custom_class[] = 'av-social-sharing-box-color-bg';
		}

		if( in_array( $style, array( 'av-social-sharing-box-square', 'av-social-sharing-box-circle', 'av-social-sharing-box-icon', 'av-social-sharing-box-icon-simple' ) ) )
		{
			$custom_class[] = 'av-social-sharing-box-same-width';
			$custom_class[] = $alignment;
		}
		else
		{
			$custom_class[] = 'av-social-sharing-box-fullwidth';
		}

		$custom_class = implode( ' ', $custom_class );



		$html  = "<div class='av-social-sharing-box {$custom_class}'>";
		$html .=	avia_social_share_links( array(), false, false, false );
		$html .= '</div>';

		if( $echo )
		{
			echo $html;
		}

		return $html;
	}
}
