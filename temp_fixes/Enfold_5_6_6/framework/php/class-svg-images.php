<?php
/**
 * Base class to handle svg image support
 *
 *
 * @since 4.8.7
 * @added_by Günter
 */
if( ! defined( 'AVIA_FW' ) ) { exit( 'No direct script access allowed' ); }


if( ! class_exists( 'aviaSVGImages', false ) )
{
	class aviaSVGImages extends aviaFramework\base\object_properties
	{
		/**
		 *
		 * @since 4.8.7
		 * @var aviaSVGImages
		 */
		static protected $_instance = null;

		/**
		 * Cache loaded svg files content
		 *
		 * @since 4.8.7
		 * @var array
		 */
		protected $cache;

		/**
		 * Cache loaded svg metadata content for aria
		 *
		 * @since 5.6.5
		 * @var array
		 */
		protected $aria_cache;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.8.7
		 * @return aviaSVGImages
		 */
		static public function instance()
		{
			if( is_null( aviaSVGImages::$_instance ) )
			{
				aviaSVGImages::$_instance = new aviaSVGImages();
			}

			return aviaSVGImages::$_instance;
		}

		/**
		 * @since 4.8.7
		 */
		protected function __construct()
		{
			$this->cache = [];
			$this->aria_cache = [];

			add_filter( 'upload_mimes', array( $this, 'handler_upload_mimes' ), 999 );

			//	WP 4.7.1 and 4.7.2 fix
			add_filter( 'wp_check_filetype_and_ext', array( $this, 'handler_wp_fix_check_filetype_and_ext' ), 10, 4 );
		}

		/**
		 * @since 4.8.7
		 */
		public function __destruct()
		{
			unset( $this->cache );
			unset( $this->aria_cache );
		}

		/**
		 * Activate svg mime type.
		 * If a plugin activates it, we do not remove this setting.
		 *
		 * @since 4.8.7
		 * @param array $mimes
		 * @return array
		 */
		public function handler_upload_mimes( $mimes = array() )
		{
			/**
			 * Disallow upload of svg files for non admins
			 *
			 * @since 4.8.7
			 * @param boolean $allow_upload
			 * @return boolean            true to allow upload
			 */
			$allow_upload = apply_filters( 'avf_upload_svg_images', current_user_can( 'manage_options' ) );

			if( true === $allow_upload )
			{
				$mimes['svg'] = 'image/svg+xml';
				$mimes['svgz'] = 'image/svg+xml';
			}

			return $mimes;
		}

		/**
		 * Mime Check fix for WP 4.7.1 / 4.7.2
		 * Issue was fixed in 4.7.3 core.
		 *
		 * @since 4.8.7
		 * @param array $checked
		 * @param string $file
		 * @param string $filename
		 * @param array $mimes
		 * @return array
		 */
		public function handler_wp_fix_check_filetype_and_ext( $checked, $file, $filename, $mimes )
		{
			global $wp_version;

			if ( $wp_version !== '4.7.1' || $wp_version !== '4.7.2' )
			{
				return $checked;
			}

			$filetype = wp_check_filetype( $filename, $mimes );

			return array(
						'ext'				=> $filetype['ext'],
						'type'				=> $filetype['type'],
						'proper_filename'	=> $checked['proper_filename']
					);
		}

		/**
		 * Check if a filename is a svg file
		 *
		 * @since 4.8.7
		 * @param string $filename
		 * @return boolean
		 */
		public function is_svg( $filename )
		{
			$mimes = array(
						'svg'	=> 'image/svg+xml',
						'svgz'	=> 'image/svg+xml'
					);

			$filetype = wp_check_filetype( $filename, $mimes );

			return strpos( $filetype['ext'], 'svg' ) !== false;
		}

		/**
		 * Check if we have a svg file we can access and load into cache.
		 * If file is in media library we return the attachment_id.
		 * If not, we try to read the content of the file.
		 *
		 * @since 4.8.7
		 * @param string $url
		 * @param int $attachment_id			0 if file does not exist in media library
		 * @param string $filter_front			'filter' | 'raw'
		 * @return boolean
		 */
		public function exists_svg_file( $url, &$attachment_id, $filter_front = 'filter' )
		{
			$curlSession = false;

			/**
			 * Supress to load svg inline - return anything !== false
			 * Keep in mind, that custom CSS will not target svg content !!
			 *
			 * @since 4.8.7.1
			 * @param boolean $no_inline
			 * @param string $url
			 * @param int $attachment_id
			 * @return boolean
			 */
			$no_inline = apply_filters( 'avf_no_inline_svg', false, $url, $attachment_id );

			if( false !== $no_inline && 'filter' == $filter_front )
			{
				return false;
			}

			try
			{
				if( ! is_numeric( $attachment_id ) || $attachment_id <= 0 )
				{
					$attachment_id = Av_Responsive_Images()->attachment_url_to_postid( $url );
					if( ! is_numeric( $attachment_id ) )
					{
						$attachment_id = 0;
					}
				}

				if( $this->get_html( $attachment_id, $url, '', 'boolean' ) )
				{
					return true;
				}

				if( $attachment_id > 0 )
				{
					$filename = get_attached_file( $attachment_id );
					if( false === $filename || ! $this->is_svg( $filename ) )
					{
						throw new Exception();
					}

					$svg = ( file_exists( $filename ) ) ? file_get_contents( $filename ) : false;
					if( false === $svg )
					{
						throw new Exception();
					}

					$this->add_to_cache( $attachment_id, $svg );
				}
				else if( false !== $this->is_url( $url ) )
				{
					if( ! $this->is_svg( $url ) )
					{
						throw new Exception();
					}

					if( ! function_exists( 'curl_init' ) )
					{
						throw new Exception();
					}

					$curlSession = curl_init();

					if( false === $curlSession )
					{
						throw new Exception();
					}

					curl_setopt( $curlSession, CURLOPT_URL, $url );
					curl_setopt( $curlSession, CURLOPT_BINARYTRANSFER, true );
					curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, true );

					/**
					 * https://kriesi.at/support/topic/long-page-loading-ends-with-50x-due-to-false-svg-logo-url/
					 *
					 * @since 5.6.7
					 */
					curl_setopt( $curlSession, CURLOPT_CONNECTTIMEOUT, 1 );
					curl_setopt( $curlSession, CURLOPT_TIMEOUT, 1 );

					$svg = curl_exec( $curlSession );

					curl_close( $curlSession );
					$curlSession = false;

					if( false === $svg )
					{
						throw new Exception();
					}

					$this->add_to_cache( $url, $svg );
				}
				else
				{
					if( ! $this->is_svg( $url ) )
					{
						throw new Exception();
					}

					//	check if we can find the file in local file structure
					$new_file = $url;
					if( ! file_exists( $new_file ) )
					{
						$new_file = ABSPATH . ltrim( $url, '/\\' );
						if( ! file_exists( $new_file ) )
						{
							$new_file = false;
						}
					}

					if( false === $new_file )
					{
						throw new Exception();
					}

					$svg = file_get_contents( $new_file );
					if( false === $svg )
					{
						throw new Exception();
					}

					$this->add_to_cache( $url, $svg );
				}
			}
			catch( Exception $ex )
			{
				if( $curlSession !== false )
				{
					curl_close( $curlSession );
				}

				$attachment_id = 0;
				return false;
			}

			return true;
		}

		/**
		 * Adds content of an svg to cache
		 *
		 * @since 4.8.7
		 * @param int $key
		 * @param string $svg_content
		 */
		protected function add_to_cache( $key, $svg_content )
		{
			$this->cache[ $key ] = trim( $svg_content );
		}

		/**
		 * Returns the html content of a svg file
		 * or a boolean value if we only want to check cache
		 *
		 * @since 4.8.7
		 * @since 5.6.5						added $title
		 * @param int $attachment_id
		 * @param string $url
		 * @param string $preserve_aspect_ratio
		 * @param string $return					'html' | 'boolean'
		 * @param string $fallback_title
		 * @return string|boolean
		 */
		public function get_html( $attachment_id, $url, $preserve_aspect_ratio = '', $return = 'html', $fallback_title = '' )
		{
			$key = is_numeric( $attachment_id ) && $attachment_id > 0 ? $attachment_id : $url;

			if( ! isset( $this->cache[ $key ] ) )
			{
				return false;
			}

			if( 'boolean' == $return )
			{
				return true;
			}

			$svg_original = $this->cache[ $key ];
			$svg = $this->set_preserveAspectRatio( $svg_original, $preserve_aspect_ratio );

			if( is_numeric( $key ) )
			{
				$this->set_aria_attributes( $svg, $key, $fallback_title );
			}

			/**
			 * @since 4.8.7
			 * @since 4.8.8					added $svg_original
			 * @param string $svg
			 * @param int $attachment_id
			 * @param string $url
			 * @param string $preserve_aspect_ratio
			 * @param aviaSVGImages $this
			 * @param string $svg_original
			 * @return string|false
			 */
			return apply_filters( 'avf_svg_images_get_html', $svg, $attachment_id, $url, $preserve_aspect_ratio, $this, $svg_original );
		}

		/**
		 * @since 4.8.7
		 * @param int $attachment_id
		 * @return bool|array
		 */
		public function get_meta_data( $attachment_id )
		{
			if( ! $this->exists_svg_file( '', $attachment_id, 'raw' ) )
			{
				return false;
			}

			if( ! isset( $this->cache[ $attachment_id ] ) )
			{
				return false;
			}

			$svg = trim( $this->cache[ $attachment_id ] );

			$matches = $this->split_svg_attributes( $svg );
			if( ! is_array( $matches ) )
			{
				return false;
			}

			$atts = $matches[1][0];
			$start = $matches[1][1];
			$len = strlen( $atts );

			$match_size = array();

			preg_match( '#viewBox=(["\'])([a-zA-Z0-9 ]*)(["\'])#im', $atts, $match_size, PREG_OFFSET_CAPTURE );

			//	check if value remains unchanged
			if( ! empty( $match_size ) && isset( $match_size[2] ) && isset( $match_size[2][0] ) )
			{
				return array( 'viewbox' => $match_size[2][0] );
			}

			return false;
		}

		/**
		 * Prepare svg with rendered $preserveAspectRatio.
		 * Checks for a svg tag and returns starting with first tag.
		 * If this check fails an empty string is retunred.
		 *
		 * @since 4.8.7
		 * @param string $svg
		 * @param string $preserveAspectRatio
		 * @return string
		 */
		protected function set_preserveAspectRatio( $svg, $preserveAspectRatio = '' )
		{
			$matches = $this->split_svg_attributes( $svg );
			if( ! is_array( $matches ) )
			{
				return '';
			}

			$atts = $matches[1][0];
			$start = $matches[1][1];
			$len = strlen( $atts );
			$match_preserve = array();

			preg_match( '#preserveAspectRatio=(["\'])([a-zA-Z ]*)(["\'])#im', $atts, $match_preserve, PREG_OFFSET_CAPTURE );

			//	check if value remains unchanged
			if( ! empty( $match_preserve ) && isset( $match_preserve[2] ) && isset( $match_preserve[2][0] ) && $match_preserve[2][0] == $preserveAspectRatio )
			{
				return $svg;
			}

			//	no preserveAspectRatio needed
			if( empty( $match_preserve ) && empty( $preserveAspectRatio ) )
			{
				return $svg;
			}

			$ratio = ! empty( $preserveAspectRatio ) ? 'preserveAspectRatio="' . $preserveAspectRatio . '"' : '';

			if( empty( $match_preserve ) )
			{
				$new_atts = $atts . ' ' . $ratio;
			}
			else
			{
				$new_atts = str_replace( $match_preserve[0][0], $ratio, $atts );
			}

			$new_svg = substr_replace( $svg, $new_atts, $start, $len );

			return $new_svg;
		}

		/**
		 * Add aria attributes to svg container
		 * https://accessibilityinsights.io/info-examples/web/svg-img-alt/
		 * https://dequeuniversity.com/rules/axe/4.1/svg-img-alt
		 *
		 * @since 5.6.5
		 * @param string $svg
		 * @param int $attachment_id
		 * @param string $fallback_title
		 */
		protected function set_aria_attributes( &$svg, $attachment_id, $fallback_title = '' )
		{
			$pos = strpos( $svg, '<svg ' );

			if( false === $pos )
			{
				return;
			}

			/*
			 *
			 * @since 5.6.5
			 * @param boolean $ignore
			 * @param string $svg
			 * @param int $attachment_id
			 * @return boolean
			 */
			if( false !== apply_filters( 'avf_ignore_svg_aria_attributes', false, $svg, $attachment_id ) )
			{
				return;
			}

			if( ! is_numeric( $attachment_id ) || $attachment_id <= 0 )
			{
				return;
			}

			if( ! isset( $this->aria_cache[ $attachment_id ] ) )
			{
				$this->aria_cache[ $attachment_id ]['role'] = 'graphics-document';

				$title = get_the_title( $attachment_id );
				$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

				if( ! empty( $title ) )
				{
					$t = $title;
				}
				else if( ! empty( $alt ) )
				{
					$t = $alt;
				}
				else if( ! empty( $fallback_title ) )
				{
					$t = $fallback_title;
				}
				else
				{
					$t = __( 'SVG Image', 'avia_framework' );
				}

				$this->aria_cache[ $attachment_id ]['title'] = esc_attr( $t );

				if( ! empty( $alt ) )
				{
					$this->aria_cache[ $attachment_id ]['alt'] = esc_attr( $alt );
				}
			}

			$atts = $this->aria_cache[ $attachment_id ];

			/**
			 * @since 5.6.5
			 * @param array $atts
			 * @param int $attachment_id
			 * @param string $svg
			 */
			$atts = apply_filters( 'avf_set_svg_aria_attributes', $atts, $attachment_id, $fallback_title );

			$matches = $this->split_svg_attributes( $svg );
			if( is_array( $matches ) )
			{
				$svg_atts = $matches[1][0];

				foreach( $atts as $key => $value )
				{
					if( false === stripos( $svg_atts, " {$key}=" ) )
					{
						continue;
					}

					if( 0 === stripos( $svg_atts, "{$key}=" ) )
					{
						continue;
					}

					unset( $atts[ $key ] );
				}
			}

			$aria = '';

			foreach( $atts as $key => $value )
			{
				$aria .= $key . '="' . $value . '" ';
			}

			$svg = substr_replace( $svg, " {$aria} ", $pos + 4, 0 );
		}

		/**
		 * Split first svg and return the preg_match array and a modified svg string
		 *
		 * @since 4.8.8
		 * @param string $svg
		 * @return array|false
		 */
		protected function split_svg_attributes( &$svg )
		{
			$svg = trim( $svg );

			$pos = strpos( $svg, '<svg ' );

			if( false === $pos )
			{
				return false;
			}

			//	remove everything before first svg tag
			if( $pos > 0 )
			{
				$svg = substr( $svg, $pos );
			}

			/**
			 * extract attributes of first svg - splitting tag in multipe lines is allowed
			 *
			 * https://kevin.deldycke.com/2007/03/ultimate-regular-expression-for-html-tag-parsing-with-php/
			 */
			$regex = "/<\/?\w+((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/i";
			$matches = array();
			if( ! preg_match( $regex, $svg, $matches, PREG_OFFSET_CAPTURE ) )
			{
				return false;
			}

			if( ! isset( $matches[1] ) )
			{
				return false;
			}

			return $matches;
		}

		/**
		 * Returns the alignment of the svg
		 *
		 * @since 4.8.7
		 * @param string $align
		 * @return string
		 */
		public function get_alignment( $align = 'center center' )
		{
			switch( $align )
			{
				case 'left top':
					$att = 'xMinYMin';
					break;
				case 'left center':
					$att = 'xMinYMid';
					break;
				case 'left bottom':
					$att = 'xMinYMax';
					break;
				case 'center top':
					$att = 'xMidYMin';
					break;
				case 'center center':
					$att = 'xMidYMid';
					break;
				case 'center bottom':
					$att = 'xMidYMax';
					break;
				case 'right top':
					$att = 'xMaxYMin';
					break;
				case 'right center':
					$att = 'xMaxYMid';
					break;
				case 'right bottom':
					$att = 'xMaxYMax';
					break;
				case 'none':
					$att = 'none';
					break;
				default:
					$att = 'xMidYMid';
					break;
			}

			return $att;
		}

		/**
		 * Get attribute to slice or scale image
		 *
		 * @since 4.8.7
		 * @param string $behaviour			'slice' | 'meet'
		 * @return string
		 */
		public function get_display_mode( $behaviour = 'meet' )
		{
			switch( $behaviour )
			{
				case 'slice':
					$att = 'slice';
					break;
				case 'meet':
				default:
					$att = 'meet';
					break;
			}

			return $att;
		}

		/**
		 * Return the preserveAspectRatio attribute value according to header settings
		 *
		 * @since 4.8.7
		 * @return string
		 */
		public function get_header_logo_aspect_ratio()
		{
			$header_pos = avia_get_option( 'header_layout' );
			$preserve = '';

			if( false !== strpos( $header_pos, 'logo_left' ) )
			{
				$preserve = $this->get_alignment( 'left center');
			}
			else if( false !== strpos( $header_pos, 'logo_right' ) )
			{
				$preserve = $this->get_alignment( 'right center');
			}
			else if( false !== strpos( $header_pos, 'logo_center' ) )
			{
				$preserve = $this->get_alignment( 'center center');
			}

			if( ! empty( $preserve ) )
			{
				$preserve .= ' ' . $this->get_display_mode();
			}

			/**
			 *
			 * @since 4.8.7
			 * @param string $preserve
			 * @param string $header_pos
			 * @return string
			 */
			return apply_filters( 'avf_svg_images_header_logo_aspect_ratio', $preserve, $header_pos );
		}

		/**
		 * Checks is a string starts with http://, https:// or localhost/
		 *
		 * @since 4.8.7
		 * @param string $test_url
		 * @return boolean
		 */
		protected function is_url( $test_url )
		{
			if( false !== stripos( $test_url, 'http://' ) || false !== stripos( $test_url, 'https://' ) || false !== stripos( $test_url, 'localhost/' ) )
			{
				return true;
			}

			return false;
		}

	}


	/**
	 * Returns the main instance of aviaSVGImages to prevent the need to use globals.
	 *
	 * @since 4.8.7
	 * @return aviaSVGImages
	 */
	function avia_SVG()
	{
		return aviaSVGImages::instance();
	}

	//	activate class
	avia_SVG();
}
