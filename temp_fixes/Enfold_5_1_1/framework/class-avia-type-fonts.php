<?php
/**
 * Character Fonts (Type Fonts) handling class
 *
 * Needs a file METADATA.pb in the folder together with the font files.
 * Content of this file must follow the structure of Google Fonts METADATA.pb
 * see https://github.com/google/fonts/tree/master/apache/roboto
 *
 * @author		Günter
 * @since		4.3
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( 'AviaTypeFonts' ) )
{
	class AviaTypeFonts extends aviaFontManagementBase
	{
		/**
		 *
		 * @since 4.3
		 * @var array
		 */
		protected $select_options_font_list;

		/**
		 * All format definitions added to CSS
		 *
		 * @since 4.3
		 * @var array
		 */
		protected $css_formats;

		/**
		 * Helps to identify Google fonts details from filename description
		 *
		 * @since 4.3
		 * @var array
		 */
		protected $google_fonts_def;

		/**
		 *
		 * @since 4.3
		 */
		public function __construct()
		{
			$this->select_options_font_list = array();
			$this->css_formats = array();
			$this->google_fonts_def = array();

			$upload = wp_upload_dir();

			$upload['fonts']	= 'avia_fonts/type_fonts';

			parent::__construct( $upload );


			/**
			 * font file handling by ajax function
			 *
			 * Must be set in this class to allow correct callback of integrate_uploaded_font
			 */
			add_action( 'wp_ajax_avia_ajax_add_zipped_type_font', array( $this, 'handler_add_zipped_fonts' ) );
			add_action( 'wp_ajax_avia_ajax_remove_zipped_type_font', array( $this, 'handler_remove_zipped_font' ) );

			add_action( 'admin_print_styles', array( $this, 'handler_wp_print_styles' ) );
			add_filter( 'avf_create_dynamic_stylesheet', array( $this, 'handler_create_dynamic_stylesheet' ), 10, 3 );
		}

		/**
		 * @since 4.3
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->select_options_font_list );
			unset( $this->css_formats );
			unset( $this->google_fonts_def );
		}

		/**
		 * Initialise base class and derived class members
		 * Added filters to allow easy manipulation by third party
		 *
		 * @since 4.3
		 */
		protected function init()
		{
			/**
			 * @since 4.3
			 * @param array $defs
			 * @param string $context
			 * @return array				def_ext =>  array  (  weight,   style  )
			 */
			$this->google_fonts_def = apply_filters( 'avf_google_fonts_style_definitions',
										array(
											'hairline'			=> array( 100, 'normal' ),
											'hairlineitalic'	=> array( 100, 'italic' ),
											'thin'				=> array( 100, 'normal' ),
											'thinitalic'		=> array( 100, 'italic' ),
											'extralight'		=> array( 200, 'normal' ),
											'extralightitalic'	=> array( 200, 'italic' ),
											'light'				=> array( 300, 'normal' ),
											'lightitalic'		=> array( 300, 'italic' ),
											'regular'			=> array( 400, 'normal' ),
											'italic'			=> array( 400, 'italic' ),
											'medium'			=> array( 500, 'normal' ),
											'mediumitalic'		=> array( 500, 'italic' ),
											'semibold'			=> array( 600, 'normal' ),
											'semibolditalic'	=> array( 600, 'italic' ),
											'bold'				=> array( 700, 'normal' ),
											'bolditalic'		=> array( 700, 'italic' ),
											'extrabold'			=> array( 800, 'normal' ),
											'extrabolditalic'	=> array( 800, 'italic' ),
											'black'				=> array( 900, 'normal' ),
											'blackitalic'		=> array( 900, 'italic' )

									), 'AviaTypeFonts' );


			/**
			 * Allowed file ext to include (add css formats also !!!)
			 *
			 * @since 4.3
			 * @param array $defs
			 * @param string $context
			 * @return array
			 */
			$ext = array( 'ttf', 'eot', 'woff', 'woff2', 'svg' );
			$this->file_ext = apply_filters( 'avf_font_manager_file_ext', array_merge( $this->file_ext, $ext ), 'AviaTypeFonts' );


			/**
			 * Filter available formats and define the order
			 *
			 * @since 4.3
			 * @since 5.1.2				order of ext defines the order of CSS output of urls
			 * @param array $defs
			 * @param string $context
			 * @return array				array (   file_ext  =>  format_string  )
			 */
			$this->css_formats = apply_filters( 'avf_font_manager_file_formats',
							array(
								'woff2'	=> "format('woff2')",
								'woff'	=> "format('woff')",
								'ttf'	=> "format('truetype')",
								'svg'	=> "format('svg')",
								'eot'	=> "format('embedded-opentype')"
							), 'AviaTypeFonts' );

			/**
			 * Contains all infos for fonts and their filename - not necessary,
			 * but overwrites the default name conventions when exist
			 *
			 * @since 4.3
			 * @param array $defs
			 * @param string $context
			 * @return array
			 */
			$this->include_files[] = 'metadata.pb';
			$this->include_files = apply_filters( 'avf_font_manager_include_files', $this->include_files, 'AviaTypeFonts' );

			$this->multiple_fonts = true;
			$this->font_option_key = 'avia_custom_type_fonts';

		}

		/**
		 * Called when the font(s) have been extracted to the temp upload directory. Only necessary files have been copied.
		 * We have to handle single and multiple fonts.
		 *
		 * In case of single fonts $this->font_name holds the correct font name, else we have to check the subdirectory names.
		 * If metadata.pb exists, content of this file overwrites everything.
		 *
		 * @since 4.3
		 * @param boolean $config_only
		 */
		protected function integrate_uploaded_fonts( $config_only = false )
		{

			foreach( $this->subfolders as $folder => $value )
			{
				if( '*' == $folder )
				{
					$temp_path = trailingslashit( $this->upload_paths['tempdir'] );
					$font_name = $this->font_name;
				}
				else
				{
					$temp_path = trailingslashit( trailingslashit( $this->upload_paths['tempdir'] ) . strtolower( $folder ) );
					$font_name = str_replace( array( '-', '_' ), ' ', $folder );
				}

				if( ! $this->integrate_single_font( $temp_path, $font_name ) )
				{
					 $this->not_uploaded_files[] = $font_name;
				}
			}

			if( 0 == count( $this->not_uploaded_files ) )
			{
				$this->response['redirect'] = __( 'All fonts could be installed successfully.<br /><br />To show them in the option fields we will save your option settings and reload the page.', 'avia_framework' );
			}
		}

		/**
		 * Integrates a single font entry.
		 *
		 * @since 4.3
		 * @param string $temp_path
		 * @param string $font_name
		 * @return boolean
		 */
		protected function integrate_single_font( $temp_path, $font_name )
		{
			$font_info = false;

			/**
			 * Check for a metadata file that overwrites everything
			 */
			$info_file = $temp_path . 'metadata.pb';
			if( file_exists( $info_file ) )
			{
				$font_info = $this->read_metadata_file( $info_file, $temp_path );
			}

			if( false === $font_info )
			{
				$font_info = $this->scan_font_files( $temp_path, $font_name );
			}

			if( false === $font_info['key'] )
			{
				avia_backend_delete_folder( $temp_path );
				$this->response['error'] .= sprintf( __( 'An error occurred with the font %s and we are not able to integrate the font', 'avia_framework' ), $font_name ) . "<br /><br />";
				return false;
			}

			if( ! $this->rename_temp_folder( $temp_path, $font_info['key'] ) )
			{
				avia_backend_delete_folder( $temp_path );
				$this->response['error'] .= sprintf( __( 'An error occurred creating the folder for the font %s in uploads directory. We are not able to integrate the font', 'avia_framework' ), $font_name ) . "<br /><br />";
				return false;
			}

			$this->add_font( $font_info );

			$this->response['id'] = empty( $this->response['id'] ) ? $font_info['key'] : $this->response['id'] . ',' . $font_info['key'];

			if( empty( $this->response['result'] ) )
			{
				$this->response['result'] = '';
			}
			$this->response['result'] .= $this->backend_font_info( $font_info );

			return true;
		}

		/**
		 * Needs the structure of METADATA.pb as defined in https://github.com/google/fonts/blob/master/apache
		 * Returns correctly filled arrays or false, if file cannot be used
		 *
		 * @since 4.3
		 * @param string $filename			complete path to metadata file
		 * @param string $path				path where files are
		 * @return array|false
		 */
		protected function read_metadata_file( $filename, $path )
		{
			$meta = array();

			$content = file_get_contents( $filename );

			$regex = 'fonts\s*{([^{]*)}';		//	find all info inside {.......} = definition for font

			$matches = array();
			preg_match_all( "/" . $regex . "/s", $content, $matches );


			if( is_array( $matches ) && is_array( $matches[1] ) && ( ! empty( $matches[1] ) ) )
			{
				$matches = $matches[1];
			}
			else
			{
				return false;
			}

			/**
			 * We take the first valid name as key - or filename if user has started filename with $this->custom_font_prefix
			 */
			$first_name = '';
			$first_file = '';

			/**
			 * Split file info and store in array
			 */
			foreach( $matches as $file_info )
			{
	//			$file_info = str_replace( array( '{', '}' ), '', $file_info );

				$lines = preg_split( '/\n|\r/', $file_info, -1, PREG_SPLIT_NO_EMPTY );

				$info = array(
							'name'		=> '',
							'style'		=> 'normal',
							'weight'	=> 400,
							'filename'	=> '',
							'full_name'	=> '',
							'files'		=> array()				//	'ttf' => 'filename'
						);

				foreach ( $lines as $line )
				{
					$line = explode( ':', $line );

					$key = trim( str_replace( array( '"', '\'' ), '', $line[0] ) );
					if( empty( $key ) || ! isset( $info[ $key ] ) )
					{
						continue;
					}

					$info[ $key ] = isset( $line[1] ) ? trim( str_replace( array( '"', '\'' ), '', $line[1] ) ) : '';
					if( 'filename' == $key )
					{
						$info[ $key ] = strtolower( $info[ $key ] );
					}
				}

				if( empty( $info['filename'] ) )
				{
					continue;
				}

				$orig_file = trailingslashit( $path ) . $info['filename'];

				if( ! file_exists( $orig_file ) )
				{
					continue;
				}

				/**
				 * Set the font name
				 */
				if( ( $info['name'] != '' ) && ( '' == $first_name ) )
				{
					$first_name = $info['name'];
				}

				$path_parts = pathinfo( $orig_file );

				if( '' == $first_file )
				{
					$first_file = $path_parts['filename'];
				}

				/**
				 * Get all allowed font files
				 */
				$fs = glob( trailingslashit( $path ) . $path_parts['filename'] . '.*' );
				foreach ( $fs as $f )
				{
					$fp = pathinfo( $f );
					if( in_array( $fp['extension'], $this->file_ext ) )
					{
						$info['files'][ $fp['extension'] ] = $fp['basename'];
					}
				}

				$meta[] = $info;
			}

			if( '' == $first_name )
			{
				$first_name = ucfirst( $first_file );
			}

			/**
			 * Set first entry to
			 */
			foreach ( $meta as $key => &$value )
			{
				if( ( 0 == $key ) || ( '' == $value['name'] ) )
				{
					$value['name'] = $first_name;
				}

				if( '' == $value['full_name'] )
				{
					$value['full_name'] = $value['name'];
				}
			}

			unset( $value );

			$meta['key'] = AviaHelper::save_string( strtolower( $first_name ), '-' );
			return $meta;
		}

		/**
		 * Scans the files in given directory and creates the metadata info array for DB
		 *
		 * @since 4.3
		 * @param string $temp_path
		 * @param string $font_name
		 * @return array|false
		 */
		protected function scan_font_files( $temp_path, $font_name )
		{
			$meta = array();
			$fonts = array();

			$files = scandir( $temp_path );
			if( false === $files )
			{
				return false;
			}

			$files = array_diff( $files, array( '..', '.' ) );
			foreach( $files as $file )
			{
				$split = pathinfo( $file );

				if( isset( $fonts[ $split['filename'] ] ) )
				{
					$fonts[ $split['filename'] ][ $split['extension'] ] = $split['basename'];
				}
				else
				{
					$fonts[ $split['filename'] ] = array( $split['extension'] =>  $split['basename'] );
				}
			}

			foreach( $fonts as $font => $files )
			{
				$pos = strrpos( $font, '-' );
				$style = ( $pos !== false ) ? substr( $font, $pos + 1 ) : '';

				$style_info = isset( $this->google_fonts_def[ $style ] ) ? $this->google_fonts_def[ $style ] : array( 400, 'normal' );

				$filename = isset( $files['ttf'] ) ? $files['ttf'] : '';
				if( empty( $filename ) )
				{
					foreach ($files as $fn )
					{
						$filename = $fn;
						break;
					}
				}

				$meta[] = array(
							'name'		=> $font_name,
							'style'		=> $style_info[1],
							'weight'	=> $style_info[0],
							'filename'	=> $filename,
							'full_name'	=> $font_name,
							'files'		=> $files				//	'ttf' => 'filename'
						);

			}

			$meta['key'] = AviaHelper::save_string( strtolower( $font_name ), '-' );
			return $meta;
		}

		/**
		 * Add a font to the options array of uploaded fonts
		 *
		 * @since 4.3
		 * @param array $font_info
		 */
		protected function add_font( array $font_info )
		{
			$fonts = $this->get_font_list();

			$fonts[ $font_info['key'] ] = array(
												'folder' 		=> trailingslashit( $this->upload_paths['fonts'] ) . $font_info['key'],
												'config' 		=> $font_info,
												'origin_url'	=> trailingslashit( $this->upload_paths['baseurl'] ),
												'origin_dir'	=> trailingslashit( $this->upload_paths['basedir'] )
											);

			/**
			 * Sort array by font name
			 */
			$names = array();
			foreach ( $fonts as $key => $value )
			{
				$names[ $key ] = $value['config'][0]['name'];
			}

			asort( $names );

			$sorted = array();
			foreach( $names as $key => $name )
			{
				$sorted[ $key ] = $fonts[ $key ];
			}

			$this->update_font_list( $sorted );
		}

		/**
		 * Remove the font from font list and from server - remove the directory
		 *
		 * @since 4.3
		 * @param string $font_id
		 * @return boolean
		 */
		protected function remove_font( $font_id )
		{
			$fonts = $this->get_font_list();

			if( ! isset( $fonts[ $font_id ] ) )
			{
				return true;
			}

			avia_backend_delete_folder( $fonts[ $font_id ]['origin_dir'] . $fonts[ $font_id ]['folder'] );
			unset( $fonts[ $font_id ] );

			$this->update_font_list( $fonts );
			return true;
		}

		/**
		 * Add additional info about added fonts and a delete link below standard upload button
		 *
		 * @since 4.3
		 * @param string $output
		 * @param array $element
		 * @return string
		 */
		public function handler_add_font_manager_upload( $output, array $element )
		{
			if( $element['id'] != 'typefont_upload' )
			{
				return $output;
			}

			$fonts = $this->get_font_list();

			$output .=	"<div class='avia_typefont_manager' data-id='{$element['id']}'>";

			foreach( $fonts as $font_info )
			{
				$output .= $this->backend_font_info( $font_info['config'] );
			}

			$output .=	"</div>";

			return $output;
		}

		/**
		 * Returns the HTML div for a custom type font to display below upload button list
		 *
		 * @since 4.3
		 * @param array $font_info
		 * @return string
		 */
		protected function backend_font_info( array $font_info )
		{
			$fonts = array();
			$font_key = '';
			$font_name = '';

			foreach( $font_info as $key => $info )
			{
				if( 'key' === $key )
				{
					$font_key = $info;
					continue;
				}

				if( 0 === $key )
				{
					$font_name = $info['name'];
				}

				if( ! isset( $fonts[ $info['style'] ] ) )
				{
					$fonts[ $info['style'] ] = array();
				}

				$fonts[ $info['style'] ][] = $info['weight'];
			}

			foreach( $fonts as $style => $values )
			{
				$values = array_unique( $values );
				sort( $values, SORT_NATURAL | SORT_FLAG_CASE );
				$fonts[ $style ] = $values;
			}

			$html = '';

			$html .=	"<div class='avia-available-font' data-font='" . $font_key . "'>";

			$html .=		'<div class="avia-font-headline">';
			$html .=			'<span class="avia-font-name">' . __( 'Font: ', 'avia_framework' ) . $font_name . '</span>';
			$html .=			'<span class="avia-font-face">  ( font-family: ' . $font_key . ' )</span>';
			$html .=		'</div>';

			$html .=		'<div class="avia-font-styles">';

			foreach( $fonts as $style => $values )
			{
				$html .=			'<div class="avia-font-styles-line">';
				$html .=				'<span class="avia-font-style">' . $style . ':  </span>';
				$html .=				'<span class="avia-font-style-values">' . implode( ', ', $values ) . '</span>';
				$html .=			'</div>';
			}

			$html .=		'</div>';

			$html .=		'<div class="avia-font-delete">';
			$html .=			"<a href='#delete-{$font_key}' data-delete='{$font_key}' class='avia-del-font'>" . __( 'Delete', 'avia_framework' ) . "</a>";
			$html .=		'</div>';

			$html .=	'</div>';

			return $html;
		}

		/**
		 * Returns a list of Google Fonts that can be merged in the select box for selecting fonts
		 *
		 * For enhancements see: https://fonts.google.com/
		 * @since 4.3
		 * @return array
		 */
		public function google_fonts_select_list()
		{

			$fonts = array(
							'Advent Pro'		=> 'Advent Pro:100,400,600',
							'Alice'				=> 'Alice',
							'Allerta'			=> 'Allerta',
							'Arvo'				=> 'Arvo',
							'Arimo'				=> 'Arimo:400,700',
							'Antic'				=> 'Antic',
							'Audiowide'			=> 'Audiowide',

							'Bad Script'		=> 'Bad Script',
							'Baumans'			=> 'Baumans',
							'Bangers'			=> 'Bangers',
							'Barlow'			=> 'Barlow:400,500,600,700',
							'Bitter'			=> 'Bitter',

							'Cabin'				=> 'Cabin',
							'Cardo'				=> 'Cardo',
							'Carme'				=> 'Carme',
							'Caveat'			=> 'Caveat:400,700',
							'Coda'				=> 'Coda',
							'Codystar'			=> 'Codystar:300,400',
							'Cormorant Garamond'	=> 'Cormorant Garamond:300,400,700',
							'Comfortaa'			=> 'Comfortaa:300,400,700',
							'Coustard'			=> 'Coustard',
							'Gruppo'			=> 'Gruppo',

							'Damion'			=> 'Damion',
							'Dancing Script'	=> 'Dancing Script',
							'Dosis'				=> 'Dosis:200,300,400,500,600,700,800',
							'Droid Sans'		=> 'Droid Sans',
							'Droid Serif'		=> 'Droid Serif',

							'EB Garamond'		=> 'EB Garamond',
							'Exo'				=> 'Exo:900,700,400,100',

							'Finger Paint'		=> 'Finger Paint',
							'Fira Sans'			=> 'Fira Sans:100,400,700',
							'Fjord One'			=> 'Fjord One',
							'Flamenco'			=> 'Flamenco:400,300',

							'Great Vibes'		=> 'Great Vibes',

							'Heebo'				=> 'Heebo:100,400,700',
							'Herr Von Muellerhoff'	=> 'Herr Von Muellerhoff',

							'IBM Plex Serif'	=> 'IBM Plex Serif:300,400,700',
							'Inconsolata'		=> 'Inconsolata',

							'Josefin Sans'		=> 'Josefin Sans',
							'Josefin Slab'		=> 'Josefin Slab',

							'Kameron'			=> 'Kameron',
							'Karla'				=> 'Karla:400,700',
							'Kreon'				=> 'Kreon',

							'Lato'				=> 'Lato:300,400,700',
							'Lobster'			=> 'Lobster',
							'Lora'				=> 'Lora',
							'League Script'		=> 'League Script',

							'Mate SC'			=> 'Mate SC',
							'Marck Script'		=> 'Marck Script',
							'Mako'				=> 'Mako',
							'Megrim'			=> 'Megrim',
							'Merienda'			=> 'Merienda:400,700',
							'Merriweather'		=> 'Merriweather:300,400,700',
							'Metrophobic'		=> 'Metrophobic',
							'Molengo'			=> 'Molengo',
							'Montserrat'		=> 'Montserrat',
							'Muli'				=> 'Muli',

							'Nixie One'			=> 'Nixie One',
							'Nobile'			=> 'Nobile',
							'News Cycle'		=> 'News Cycle',

							'Open Sans'			=> 'Open Sans:400,600',
							'Open Sans Condensed'	=> 'Open Sans Condensed:300,700',
							'Orbitron'			=> 'Orbitron',
							'Oregano'			=> 'Oregano',
							'Oswald'			=> 'Oswald',

							'Pacifico'			=> 'Pacifico',
							'Parisienne'		=> 'Parisienne',
							'Petit Formal Script'	=> 'Petit Formal Script',
							'Pinyon Script'		=> 'Pinyon Script',
							'Playfair Display'	=> 'Playfair Display:400,700',
							'Podkova'			=> 'Podkova',
							'Poiret One'		=> 'Poiret One',
							'Poly'				=> 'Poly',
							'Press Start 2P (Retro Pixelfont)'	=> 'Press Start 2P',
							'PT Sans'			=> 'PT Sans',

							'Quattrocento'		=> 'Quattrocento',
							'Questrial'			=> 'Questrial',
							'Quicksand'			=> 'Quicksand:400,600',

							'Raleway'			=> 'Raleway',
							'Righteous'			=> 'Righteous',
							'Roboto'			=> 'Roboto:100,400,700',

							'Sacramento'		=> 'Sacramento',
							'Salsa'				=> 'Salsa',
							'Signika Negative'	=> 'Signika Negative',
							'Source Serif Pro'	=> 'Source Serif Pro:400,600,700',
							'Sunshiney'			=> 'Sunshiney',
							'Special Elite'		=> 'Special Elite',


							'Tangerine'			=> 'Tangerine',
							'Tenor Sans'		=> 'Tenor Sans',

							'Varela Round'		=> 'Varela Round',

							'Work Sans'			=> 'Work Sans:700,400,100',

							'Yellowtail'		=> 'Yellowtail',
				);


			/**
			 * Kept for backwards compatibility - will be replaced by next filter
			 */
			$fonts = apply_filters('avf_google_heading_font', $fonts );

			/**
			 * @since 4.3
			 * @return array
			 */
			$fonts = apply_filters('avf_available_google_fonts', $fonts );

			return $fonts;
		}

		/**
		 * Returns a list of websafe fonts that can be merged in the select box for selecting fonts
		 * Keys are extended by -websave at the end to be able to recognise them before the array is returned
		 *
		 * For enhancements see https://www.cssfontstack.com/
		 *
		 * @since 4.3
		 * @since 4.9		removed '-' in values
		 * @return array
		 */
		public function websafe_fonts_select_list()
		{
			$fonts = array(
							'Arial'				=> 'Arial',
							'Arial Black'		=> 'Arial Black',
							'Arial Narrow'		=> 'Arial Narrow',
							'Helvetica'			=> 'Helvetica',
							'Helvetica Neue'	=> 'Helvetica Neue,Helvetica',
							'Lucida Sans'		=> 'Lucida Sans,Lucida Grande,Lucida Sans Unicode',
							'Tahoma'			=> 'Tahoma',
							'Trebuchet MS'		=> 'Trebuchet MS',
							'Verdana'			=> 'Verdana',

							'Georgia'			=> 'Georgia',
							'Lucida Bright'		=> 'Lucida Bright',
							'Palatino'			=> 'Palatino',
							'Times New Roman'	=> 'Times New Roman',

							'Courier New'		=> 'Courier New',
							'Lucida Sans Typewriter'	=> 'Lucida Sans Typewriter',

							'Copperplate'		=> 'Copperplate',
							'Papyrus'			=> 'Papyrus',

							'Brush Script MT'	=> 'Brush Script MT'
					);

			/**
			 * @since 4.3
			 * @param array $fonts
			 * @return array
			 */
			$fonts = apply_filters( 'avf_available_websafe_fonts', $fonts );

			foreach( $fonts as $key => $value )
			{
				if( false === ( strpos( $value, '-websafe' ) ) )
				{
					$value .= '-websave';
				}

				$fonts[ $key ] = $value;
			}

			return $fonts;
		}

		/**
		 * Returns a list of custom uploaded fonts that can be merged in the select box for selecting fonts
		 * Keys are extended by -customfont at the end to be able to recognise them before the array is returned
		 *
		 * @since 4.3
		 * @return array
		 */
		public function custom_font_select_list()
		{
			$fonts = array();

			$uploaded = $this->get_font_list();

			/**
			 * Sort alphabetically
			 */
			$keys = array_keys( $uploaded );
			sort( $keys, SORT_STRING );

			foreach( $keys as $font_key )
			{
				/**
				 * framework\php\class-htmlhelper.php function select() eliminates fonts with the same name (= key).
				 * To avoid this we add the uploaded font weights to the name of the font.
				 */
				$weight = array();
				foreach( $uploaded[ $font_key ]['config'] as $key => $desc )
				{
					if( isset( $desc['weight'] ) )
					{
						$weight[] = $desc['weight'];
					}
				}
				$weight = array_unique( $weight );
				sort( $weight );

				$name = isset( $uploaded[ $font_key ]['config'][0]['name'] ) ? $uploaded[ $font_key ]['config'][0]['name'] : $font_key;
				$name .= ' (' . implode( ', ', $weight ) . ')';
				$fonts[  $name ] = $font_key;
			}

			/**
			 * @since 4.3
			 * @param array $fonts
			 * @return array
			 */
			$fonts = apply_filters( 'avf_available_custom_fonts', $fonts );

			foreach( $fonts as $key => &$value )
			{
				if( false === ( strpos( $value, '-custom' ) ) )
				{
					$value .= '-custom';
				}
			}

			return $fonts;
		}

		/**
		 * Returns the options array for a font select box. Adds optgroup so the user knows what type of font he selects.
		 *
		 * @since 4.3
		 * @param array $source				'all' | 'websafe' | 'custom' | 'google'
		 * @param string $cache				'cache' | 'no_cache'
		 * @param string|array $default		'add_default' | 'no_default' | array ( 'value' => 'key'  )
		 * @return array
		 */
		public function get_font_select_options_list( array $source = array( 'all' ), $cache = 'cache', $default = 'add_default' )
		{
			if( ! empty( $this->select_options_font_list ) && ( 'cache' == $cache ) )
			{
				return $this->select_options_font_list;
			}

			$font_types = apply_filters( 'avf_font_select_options_order', array(  'websafe', 'google', 'custom' ) );

			$options = array();

			if( 'add_default' == $default )
			{
				$options[ __( 'Default', 'avia_framework' ) ] = '';
			}
			else if( is_array( $default ) && ! empty( $default ) )
			{
				$options = $default;
			}

			foreach( $font_types as $font_type )
			{
				$intersect = array_intersect( array( 'all', $font_type ), $source );

				if( ! empty( $intersect ) )
				{
					$fonts = array();

					switch( $font_type )
					{
						case 'custom':
							$fonts = $this->custom_font_select_list();
							$optgroup = __( 'Uploaded Fonts (located at Enfold -> Import/Export)', 'avia_framework' );
							break;
						case 'websafe';
							$fonts = $this->websafe_fonts_select_list();
							$optgroup = __( 'Websafe Fonts', 'avia_framework' );
							break;
						case 'google';
							$fonts = $this->google_fonts_select_list();
							$optgroup = __( 'Google Fonts', 'avia_framework' );
							break;
					}

					if( ! empty( $fonts ) )
					{
						$options[ $optgroup ] = $fonts;
					}
				}
			}

			if( 'cache' == $cache )
			{
				$this->select_options_font_list = $options;
			}

			return $options;
		}

		/**
		 * Returns the CSS for a single font.
		 * $font must have the stucture from an entry of $this->add_font
		 *
		 * @since 4.3
		 * @param array $font_info
		 * @return sting
		 */
		public function get_css_custom_font_face( array $font_info )
		{
			$font = $font_info['config'];

			$css = '';

			foreach( $font as $key => $info )
			{
				if( ! is_numeric( $key ) )
				{
					continue;
				}

				$urls = array();

				/**
				 * @since 5.2.1   sort fonts to match $this->css_formats order
				 */
				$font_files = $info['files'];
				$sorted_fonts = array();

				foreach( $this->css_formats as $ext_key => $ext_format )
				{
					if( array_key_exists( $ext_key, $font_files ) )
					{
						$sorted_fonts[ $ext_key ] = $font_files[ $ext_key ];
					}
				}

				if( empty( $sorted_fonts ) )
				{
					$sorted_fonts = $font_files;
				}

				foreach( $sorted_fonts as $ext => $file )
				{
					if( ! array_key_exists( $ext, $this->css_formats ) )
					{
						continue;
					}

					$url = trailingslashit( $font_info['origin_url'] . $font_info['folder'] ) . $file;
					$orig_url = $url;

					/**
					 * Fixes a problem with WMPL and blocked CrossOrigin Requests
					 * https://kriesi.at/support/topic/cross-origin-request-blocked-fonts-not-loading-on-english-wpml/
					 *
					 * @since 4.8.8
					 */
					if( current_theme_supports( 'custom_uploaded_fonts_relative_url' ) )
					{
						$home_url = home_url();

						$local = strpos( $home_url, '//localhost' );
						if( false === $local )
						{
							$local = strpos( $home_url, '\\\\localhost' );
						}

						//	localhost must only replace localhost and not root directory
						if( $local !== false )
						{
							$home_url = substr( $home_url, 0, $local + 11 );
						}

						$url = '/' . ltrim( str_replace( $home_url, '', $url ), '/\\' );
					}

					/**
					 * Modify URL to uploaded font
					 *
					 * @since 4.8.8
					 * @param string $url
					 * @param string $orig_url
					 * @param string $file
					 * @param array $info
					 * @return string
					 */
					$url = apply_filters( 'avf_custom_uploaded_fonts_url', $url, $orig_url, $file, $info );

					switch( $ext )
					{
						case 'eot':
							$iefix = $url . "?#iefix";		//	IE6-IE8
							$urls[] = "   url('{$url}') {$this->css_formats[ $ext ]}";
							$urls[] = "   url('{$iefix}') {$this->css_formats[ $ext ]}";
							break;
						case 'svg':
							$urls[] = "   url('{$url}#{$font['key']}') {$this->css_formats[ $ext ]}";
							break;
						default:
							$urls[] = "   url('{$url}') {$this->css_formats[ $ext ]}";
							break;
					}
				}

				if( empty( $urls ) )
				{
					continue;
				}

				/**
				 * Allow to change default behaviour of browsers when loading external fonts
				 * https://developers.google.com/web/updates/2016/02/font-display
				 *
				 * @since 4.5.6
				 * @param string $font_display
				 * @param string $font_name
				 * @return string			auto | block | swap | fallback | optional
				 */
				$font_display = apply_filters( 'avf_font_display', avia_get_option( 'custom_font_display', '' ), $font['key'] );
				$font_display = empty( $font_display ) ? 'auto' : $font_display;

				$css .= "@font-face { \r\n";

				$css .= "     font-family: '{$font['key']}';\r\n";

				$css .= "     src: ";
				$css .=		implode( ",\r\n          ", $urls );
				$css .= ";\r\n";

				$css .= "     font-style:  {$info['style']};\r\n";
				$css .= "     font-weight: {$info['weight']};\r\n";
				$css .= "     font-display: {$font_display};\r\n";

				$css .= "}\r\n";
			}

			return $css;
		}

		/**
		 * Output styles for custom uploaded fonts
		 *
		 * @since 4.3
		 */
		public function handler_wp_print_styles()
		{
			$uploaded = $this->get_font_list();

			$out = '<style type="text/css">' . "\r\n";

			foreach( $uploaded as $font_info )
			{
				$out .= $this->get_css_custom_font_face( $font_info );
			}

			$out .= '</style>' . "\r\n";

			echo $out;
		}

		/**
		 * Output styles for custom uploaded fonts to dynamic stylesheet
		 *
		 * @param string $output
		 * @param avia_style_generator $style_generator
		 * @param string $context					'before' | 'after'
		 * @return string
		 */
		public function handler_create_dynamic_stylesheet( $output, avia_style_generator $style_generator, $context = '' )
		{
			if( ! in_array( $context, array( 'before' ) ) )
			{
				return $output;
			}

			$uploaded = $this->get_font_list();

			$output .= "\r\n";

			foreach( $uploaded as $font_info )
			{
				$output .= $this->get_css_custom_font_face( $font_info );
			}

			$output .= "\r\n";

			return $output;
		}

		/**
		 * Splits the selected font string (option value from font select box)
		 *
		 * @since 4.3
		 * @param string $selection
		 * @return array
		 */
		public function split_font_info( $selection )
		{
			/**
			 * Split font sizes for Google fonts
			 */
			$info = explode( ':', $selection );

			$font = array(
					'family'	=> trim( $info[0] ),
					'weight'	=> isset( $info[1] ) ? trim( $info[1] ) : ''
				);

			return $font;
		}

		/**
		 * Returns the type of font selected from a select box
		 *
		 * @since 4.3
		 * @param array $font_info
		 * @return string					'websave' | 'custom' | 'google'
		 */
		public function get_selected_font_type( array $font_info )
		{
			if( strpos( $font_info['family'], '-websave' ) !== false )
			{
				return 'websave';
			}

			if( strpos( $font_info['family'], '-custom' ) !== false )
			{
				return 'custom';
			}

			return 'google';
		}

		/**
		 * Removes any font type mark from first font name, removes ",' and returns the fontname in lowercase
		 *
		 * @since 4.3
		 * @param array $font_info
		 * @return array
		 */
		public function set_font_family( array $font_info )
		{
			$type = $this->get_selected_font_type( $font_info );

			switch( $type )
			{
				case 'websave':
				case 'custom':
					$replace = '-' . $type;
					break;
				default:
					return $font_info;
			}

			/**
			 * In case we have several fonts we only take the first one
			 */
			$font_family = explode( ',', $font_info['family'] );
			$font_family = strtolower( $font_family[0] );

			$font_family = str_replace( array( '"', "'" ), '', $font_family );
			$font_family = str_replace( $replace, '', $font_family );
			$font_info['family'] = $font_family;

			return $font_info;
		}
	}

}
