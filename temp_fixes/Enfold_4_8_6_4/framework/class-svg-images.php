<?php
/**
 * Base class to handle svg image support
 * 
 * 
 * @since x.x.x
 * @added_by Günter
 */
if( ! defined( 'AVIA_FW' ) ) { exit( 'No direct script access allowed' ); }


if( ! class_exists( 'aviaSVGImages' ) )
{
	class aviaSVGImages 
	{
		/**
		 *
		 * @since x.x.x
		 * @var aviaSVGImages 
		 */
		static protected $_instance = null;
		
		/**
		 * Cache loaded svg files content
		 * 
		 * @since x.x.x
		 * @var array
		 */
		protected $cache;
		
		/**
		 * Return the instance of this class
		 * 
		 * @since x.x.x
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
		 * @since x.x.x
		 */
		protected function __construct() 
		{
			$this->cache = array();
			
			add_filter( 'upload_mimes', array( $this, 'handler_upload_mimes' ), 999 );
			
			//	WP 4.7.1 and 4.7.2 fix
			add_filter( 'wp_check_filetype_and_ext', array( $this, 'handler_wp_fix_check_filetype_and_ext' ), 10, 4 );
		}
		
		/**
		 * @since x.x.x
		 */
		public function __destruct() 
		{
			unset( $this->cache );
		}
		
		/**
		 * Activate svg mime type.
		 * If a plugin activates it, we do not remove this setting.
		 * 
		 * @since x.x.x
		 * @param array $mimes
		 * @return array
		 */
		public function handler_upload_mimes( $mimes = array() ) 
		{
			/**
			 * @since x.x.x
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
		 * @since x.x.x
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
		 * @since x.x.x
		 * @param string $filename
		 * @return boolean
		 */
		public function is_svg( $filename ) 
		{
			$filetype = wp_check_filetype( $filename );
			
			return strpos( $filetype['ext'], 'svg' ) !== false;
		}

		/**
		 * Check if we have a svg file we can access and load into cache.
		 * If file is in media library we return the attachment_id.
		 * If not, we try to read the content of the file.
		 * 
		 * @since x.x.x
		 * @param string $url
		 * @param int $attachment_id			0 if file does not exist in media library
		 * @return boolean
		 */
		public function exists_svg_file( $url, &$attachment_id ) 
		{
			$curlSession = false;
			
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
					
					$svg = ( file_exists( $url ) ) ?  file_get_contents( $url ) : false;
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
		 * @since x.x.x
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
		 * @since x.x.x
		 * @param int $attachment_id
		 * @param string $url
		 * @param string $preserve_aspect_ratio
		 * @param string $return					'html' | 'boolean'
		 * @return string|boolean
		 */
		public function get_html( $attachment_id, $url, $preserve_aspect_ratio = '', $return = 'html' ) 
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
			
			$svg = $this->set_preserveAspectRatio( $this->cache[ $key ], $preserve_aspect_ratio );
			
			/**
			 * @since x.x.x
			 * @param int $attachment_id
			 * @param string $url
			 * @param string $preserve_aspect_ratio
			 * @param aviaSVGImages $this	
			 * @return string|false
			 */
			return apply_filters( 'avf_svg_images_get_html', $svg, $attachment_id, $url, $preserve_aspect_ratio, $this );
		}
		
		/**
		 * Prepare svg with rendered $preserveAspectRatio.
		 * Checks for a svg tag and returns starting with first tag.
		 * If this check fails an empty string is retunred.
		 * 
		 * @since x.x.x
		 * @param string $svg
		 * @param string $preserveAspectRatio
		 * @return string
		 */
		function set_preserveAspectRatio( $svg, $preserveAspectRatio = '' ) 
		{
			$svg = trim( $svg );
			
			$pos = strpos( $svg, '<svg ' );
			
			if( false === $pos )
			{
				return;
			}
			
			//	remove everything before first svg tag 
			if( $pos > 0 )
			{
				$svg = substr( $svg, $pos );
			}
			
			//	extract attributes of first svg (nesting of svg breaks regex)
			$matches = array();
			if( ! preg_match( '#^(<)([a-z0-9\-._:]+)((\s)+(.*?))?((>)([\s\S]*?)((<)\/\2(>))|(\s)*\/?(>))$#im', $svg, $matches, PREG_OFFSET_CAPTURE ) ) 
			{
				return '';
			}
			
			$atts = $matches[3][0];
			$start = $matches[3][1];
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
		 * Returns the alignment of the svg
		 * 
		 * @since x.x.x
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
		 * @since x.x.x
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
		 * @since x.x.x
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
			 * @since x.x.x
			 * @param string $preserve
			 * @param string $header_pos
			 * @return string
			 */
			return apply_filters( 'avf_svg_images_header_logo_aspect_ratio', $preserve, $header_pos );
		}
		
		/**
		 * Checks is a string starts with http://, https:// or localhost/
		 * 
		 * @since x.x.x
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
	 * @since x.x.x
	 * @return aviaSVGImages
	 */
	function avia_SVG() 
	{
		return aviaSVGImages::instance();
	}
	
	//	activate class
	avia_SVG();
}
