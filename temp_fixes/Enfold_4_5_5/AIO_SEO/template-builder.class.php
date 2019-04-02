<?php
/**
* Central Template builder class
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if ( !class_exists( 'AviaBuilder' ) ) {

	class AviaBuilder
	{
		const VERSION = '0.9.5';
		
		/**
		 * Holds the instance of this class
		 * 
		 * @since 4.2.1
		 * @var AviaBuilder 
		 */
		static private $_instance = null;
		
		/**
		 *
		 * @var string			'safe' | 'debug' 
		 */
		public static $mode = "";
		
		public static $path = array();
		public static $resources_to_load = array();
		public static $default_iconfont = "";
		public static $full_el = array();
		public static $full_el_no_section = array();
		
		/**
		 * Define all supported post types
		 * 
		 * @since 4.3
		 * @var array 
		 */
		protected $supported_post_types;
		
		/**
		 * Define all supported post types
		 * 
		 * @since 4.4.1
		 * @var array 
		 */
		protected $supported_post_status;
		
		/**
		 *
		 * @var array 
		 */
		public $paths;
		
		/**
		 * [Class name] => class
		 * 
		 * @var array 
		 */
		public $shortcode_class;
		
		/**
		 * Back reference to class name of shortcode
		 * [shortcode] => ClassName
		 * 
		 * @since 4.2.1
		 * @var array 
		 */
		public $shortcode;
		
		/**
		 * Back reference to shortcode for children (nested or layout_children)
		 * The shortcode class must not exist. Needed to identify and repair the element structure
		 * 
		 *		child_shortcode =>  array( parent_shortcode, .... )
		 * 
		 * @since 4.2.1
		 * @var array 
		 */
		public $shortcode_parents;

		/**
		 *
		 * @since 4.2.1
		 * @var ShortcodeParser 
		 */
		protected $shortcode_parser;
		
		/**
		 * State of the selectbox in admin area for the post/page/...
		 * 
		 * @since 4.2.1
		 * @var string			'disabled' | 'check_only' | 'auto_repair'
		 */
		protected $posts_shortcode_parser_state;

		/**
		 *
		 * @since 4.3
		 * @var aviaElementManager 
		 */
		protected $element_manager;

		/**
		 *
		 * @since 4.3
		 * @var aviaAssetManager 
		 */
		protected $asset_manager_class;
		
		
		/**
		 * Tabs in backend for categorizing shortcode buttons in ALB
		 * 
		 * @var array 
		 */
		public $tabs;
		
		
		/**
		 * Backend ALB shortcode buttons 
		 * 
		 * @var array 
		 */
		public $shortcode_buttons;
		
		
		/**
		 *
		 * @var AviaSaveBuilderTemplate
		 */
		public $builderTemplate;
		
		/**
		 *
		 * @var boolean 
		 */
		public $disable_drag_drop;

		
		/**
		 * Holds the status of the ALB for the current post
		 * 
		 * @since 4.2.1
		 * @var string			'active' | ''
		 */
		protected $alb_builder_status;
		
		/**
		 * Stores the balanced post content of a non ALB post to allow building the shortcode tree
		 * 
		 * @since 4.2.1
		 * @var string 
		 */
		public $post_content;
		
		/**
		 * Revision post id to save our postmeta fields
		 * 
		 * @since 4.2.1
		 * @var int 
		 */
		protected $revision_id;

		
		/**
		 * Flag if the ALB magic wand button had been added to tinyMCE buttons
		 * 
		 * @since 4.2.4
		 * @var boolean 
		 */
		protected $alb_magic_wand_button;

		/**
		 * Flag to add the nonce input field on non alb supported pages that provide the ALB magic wand shortcode button
		 * 
		 * @since 4.2.4
		 * @var boolean 
		 */
		protected $alb_nonce_added;
		
		/**
		 * Array that contains all the names of shortcodes that can be dissabled automatically
		 * 
		 * @kriesi
		 * @since 4.3
		 * @var array 
		 */
		public $may_be_disabled_automatically;
		
		/**
		 * Array that contains all the names of shortcodes with disabled assets that should not be loaded on the frontend
		 * 
		 * @kriesi
		 * @since 4.3
		 * @var array 
		 */
		public $disabled_assets;
		
		/**
		 * Flag if we have an ajax callback to prepare modal preview 
		 * 
		 * @since 4.5.4
		 * @var boolean
		 */
		protected $in_text_to_preview;
		
		/**
		 * Flag if action wp_head has been executed
		 * 
		 * @since 4.5.5
		 * @var boolean 
		 */
		public $wp_head_done;
		
		/**
		 * Flag if action get_sidebar has been executed
		 * 
		 * @since 4.5.5
		 * @var boolean 
		 */
		public $wp_sidebar_started;
		
		/**
		 * Flag if action get_footer has been executed
		 * 
		 * @since 4.5.5
		 * @var boolean 
		 */
		public $wp_footer_started;


		/**
		 * Return the instance of this class
		 * 
		 * @since 4.2.1
		 * @return AviaBuilder
		 */
		static public function instance()
		{
			if( is_null( AviaBuilder::$_instance ) )
			{
				AviaBuilder::$_instance = new AviaBuilder();
			}
			
			return AviaBuilder::$_instance;
		}


		/**
		 * Initializes plugin variables and sets up WordPress hooks/actions.
		 *
		 * @return void
		 */
		protected function __construct()
		{
			$this->paths = array();
			$this->shortcode_class = array();
			$this->shortcode = array();
			$this->shortcode_parents = array();
			$this->shortcode_parser = null;
			$this->posts_shortcode_parser_state = '';
			$this->element_manager = null;
			$this->asset_manager_class = null;
			$this->tabs = array();
			$this->shortcode_buttons = array();
			$this->builderTemplate = null;
			$this->disable_drag_drop = false;
			$this->alb_builder_status = 'unknown';
			$this->post_content = '';
			$this->revision_id = 0;
			$this->alb_magic_wand_button = false;
			$this->alb_nonce_added = false;
			$this->supported_post_types = array( 'post', 'portfolio', 'page', 'product' );
			$this->supported_post_status = array( 'publish', 'private', 'future', 'draft', 'pending' );
			$this->may_be_disabled_automatically = array();
			$this->disabled_assets = array();
			$this->in_text_to_preview = false;
			$this->wp_head_done = false;
			$this->wp_sidebar_started = false;
			$this->wp_footer_started = false;
			
			$this->paths['pluginPath'] 	= trailingslashit( dirname( dirname(__FILE__) ) );
			$this->paths['pluginDir'] 	= trailingslashit( basename( $this->paths['pluginPath'] ) );
			$this->paths['pluginUrlRoot'] 	= apply_filters('avia_builder_plugins_url',  plugins_url().'/'.$this->paths['pluginDir']);
			$this->paths['pluginUrl'] 	= $this->paths['pluginUrlRoot'] . "avia-template-builder/";
			$this->paths['assetsURL']	= trailingslashit( $this->paths['pluginUrl'] ) . 'assets/';
			$this->paths['assetsPath']	= trailingslashit( $this->paths['pluginPath'] ) . 'assets/';
			$this->paths['imagesURL']	= trailingslashit( $this->paths['pluginUrl'] ) . 'images/';
			$this->paths['configPath']	= apply_filters('avia_builder_config_path', $this->paths['pluginPath'] .'config/');
				
			AviaBuilder::$path = $this->paths;
			AviaBuilder::$default_iconfont = apply_filters('avf_default_iconfont', array( 'entypo-fontello' => 
																						array(
																						'append'	=> '',
																						'include' 	=> $this->paths['assetsPath'].'fonts',
																						'folder'  	=> $this->paths['assetsURL'].'fonts',
																						'config'	=> 'charmap.php',
																						'compat'	=> 'charmap-compat.php', //needed to make the theme compatible with the old version of the font
																						'full_path'	=> 'true' //tells the script to not prepend the wp_upload dir path to these urls
																						)
																					));
		
			add_action('load-post.php', array(&$this, 'admin_init') , 5 );
			add_action('load-post-new.php', array(&$this, 'admin_init') , 5 );
			
			add_action('init', array(&$this, 'loadLibraries') , 5 ); 
			add_action('init', array(&$this, 'init') , 10 );
			add_action('wp', array(&$this, 'frontend_asset_check') , 5 );
			
			add_action( 'wp_head', array( $this, 'handler_wp_head'), 99999999 );
			add_action( 'get_sidebar', array( $this, 'handler_get_sidebar'), 1, 1 );
			add_action( 'get_footer', array( $this, 'handler_get_footer'), 1, 1 );
			
			
			//save and restore meta information if user restores a revision
			add_action( 'wp_creating_autosave', array( $this, 'avia_builder_creating_autosave' ), 10, 1 );
			add_action( '_wp_put_post_revision', array( $this, 'avia_builder_put_revision' ), 10, 1 );
	        add_action( 'wp_restore_post_revision', array( $this, 'avia_builder_restore_revision' ), 10, 2 );

			add_filter( 'avia_builder_metabox_filter', array( $this, 'handler_alb_metabox_filter'), 10, 1 );
			
			
			add_action('dbx_post_sidebar', array( $this, 'handler_wp_dbx_post_sidebar'), 10, 1 );

		}
		
		/**
		 * 
		 * @since 4.2.1
		 */
		public function __destruct() 
		{
			unset( $this->paths );
			unset( $this->shortcode_class );
			unset( $this->shortcode );
			unset( $this->shortcode_parents );
			unset( $this->shortcode_parser );
			unset( $this->element_manager );
			unset( $this->asset_manager_class );
			unset( $this->tabs );
			unset( $this->shortcode_buttons );
			unset( $this->builderTemplate );
			unset( $this->supported_post_types );
			unset( $this->supported_post_status );
			unset( $this->may_be_disabled_automatically );
			unset( $this->disabled_assets );
		}
		
		/**
		 * Flag that wp_head has been executed (hooks with very low priority so other plugins may perform a precompile
		 * and that does not break our shortcode tree count
		 * 
		 * @since 4.5.5
		 */
		public function handler_wp_head()
		{
			$this->wp_head_done = true;
			ShortcodeHelper::$shortcode_index = 0;
		}
		
		/**
		 * Flag that get_sidebar has been executed (hooks with very high priority)
		 * This allows us to ignore changing of post id's after loop has finished and we can leave the last shortcode tree
		 * 
		 * @since 4.5.5
		 * @param string $name
		 */
		public function handler_get_sidebar( $name )
		{
			$this->wp_sidebar_started = true;
		}
		
		/**
		 * Flag that get_footer has been executed (hooks with very high priority)
		 * This allows us to ignore changing of post id's after loop has finished and we can leave the last shortcode tree
		 * 
		 * @since 4.5.5
		 * @param string $name
		 */
		public function handler_get_footer( $name )
		{
			$this->wp_footer_started = true;
		}
		

		/**
		 * After all metaboxes have been added we check if hidden input field avia_nonce_loader had been set.
		 * If not we have to add it. If user adds a shortcode (like tabs) with magic wand that need to call backend 
		 * check_ajax_referrer cannot proceed in backend because this value is missing
		 * 
		 * @added_by Günter
		 * @since 4.2.4
		 * @param WP_Post $post
		 */
		public function handler_wp_dbx_post_sidebar( WP_Post $post )
		{
			if( ! $this->alb_magic_wand_button )
			{
				return;
			}
			
			if( ! $this->alb_nonce_added )
			{
				$nonce =	wp_create_nonce ('avia_nonce_loader');
				echo		'<input type="hidden" name="avia-loader-nonce" id="avia-loader-nonce" value="' . $nonce . '" />';
				
				$this->alb_nonce_added = true;
			}
		}

		
		/**
		 *Load all functions that are needed for both front and backend
		 **/
		public function init()
	 	{
	 		if(isset($_GET['avia_mode']))
			{
				AviaBuilder::$mode = esc_attr($_GET['avia_mode']);
			}
			
	 		//activate the element manager
			$this->element_manager();
			
			$this->createShortcode();
			
			$this->addActions();
            AviaStoragePost::generate_post_type();           
			
			//hook into the media uploader. we always need to call this for several hooks to be active
			new AviaMedia();
			
			//on ajax call load the functions that are usually only loaded on new post and edit post screen
			if(AviaHelper::is_ajax()) 
			{
				if(empty( $_POST['avia_request'] )) return;
                $this->admin_init();
	 	    } 
	 		
	 		//activate asset manager
			$this->asset_manager();
	 		

	 	}
		
		
		/**
		 *Load functions that are only needed on add/edit post screen
		 **/
		public function admin_init()
	 	{
			$this->addAdminFilters();
			$this->addAdminActions();
			$this->loadTextDomain();
			$this->call_classes();
			$this->apply_editor_wrap();
	 	}
	 	
		/**
		 *Load all the required library files.
		 **/
		public function loadLibraries() 
		{			
			require_once( $this->paths['pluginPath'].'php/pointer.class.php' );
			require_once( $this->paths['pluginPath'].'php/shortcode-helper.class.php' ); 
			require_once( $this->paths['pluginPath'].'php/shortcode-parser.class.php' ); 
			require_once( $this->paths['pluginPath'].'php/element-manager.class.php' );
			require_once( $this->paths['pluginPath'].'php/generic-helper.class.php' );
			require_once( $this->paths['pluginPath'].'php/html-helper.class.php' );
			require_once( $this->paths['pluginPath'].'php/meta-box.class.php' );
			require_once( $this->paths['pluginPath'].'php/shortcode-template.class.php' );
			require_once( $this->paths['pluginPath'].'php/media.class.php' );
			require_once( $this->paths['pluginPath'].'php/tiny-button.class.php' );
			require_once( $this->paths['pluginPath'].'php/save-buildertemplate.class.php' );
			require_once( $this->paths['pluginPath'].'php/storage-post.class.php' );
			require_once( $this->paths['pluginPath'].'php/font-manager.class.php' );
			require_once( $this->paths['pluginPath'].'php/asset-manager.class.php' );
			
			
			//autoload files in shortcodes folder and any other folders that were added by filter
			$folders = apply_filters('avia_load_shortcodes', array($this->paths['pluginPath'].'php/shortcodes/'));
			$this->autoloadLibraries($folders);
		}
		
		/**
		 * PHP include all files from a number of folders which are passed as an array
		 * This auoloads all the shortcodes located in php/shortcodes and any other folders that were added by filter
		 **/
		protected function autoloadLibraries($paths)
		{
			foreach ($paths as $path)
			{
				//include modules (eg files within folders with the same name)
				foreach(glob($path.'*', GLOB_ONLYDIR) as $folder)
				{
					$php_file = trailingslashit($folder) . basename($folder) . ".php";
					
					if(file_exists( $php_file ))
					{
						include( $php_file );
					}
				}
				
				//include single files
				foreach(glob($path.'*.php') as $file)
				{
					require_once( $file ); 
				}
			}
		}
		
		
		/**
		 *Add filters to various wordpress filter hooks
		 **/
		protected function addAdminFilters() 
		{
			//lock drag and drop?
			$this->disable_drag_drop = apply_filters('avf_allow_drag_drop', false);
			
			// add_filter('tiny_mce_before_init', array($this, 'tiny_mce_helper')); // remove span tags from tinymce - currently disabled, doesnt seem to be necessary
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		}
		
		/**
		 *Add Admin Actions to some wordpress action hooks
		 **/
		protected function addAdminActions() {
		
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_styles' ) );
			add_action( 'admin_print_scripts', array($this,'load_shortcode_assets'), 2000);
		    add_action( 'print_media_templates', array($this, 'js_template_editor_elements' )); //create js templates for AviaBuilder Canvas Elements
		    add_action( 'avia_save_post_meta_box', array($this, 'meta_box_save' )); //hook into meta box saving and store the status of the template builder and the shortcodes that are used

			add_filter( 'avf_before_save_alb_post_data', array( $this, 'handler_before_save_alb_post_data' ), 10, 2 );	//	hook to balance shortcode for non ALB pages
		    			
			//custom ajax actions
			add_action('wp_ajax_avia_ajax_text_to_interface', array($this,'text_to_interface'));
			add_action('wp_ajax_avia_ajax_text_to_preview', array($this,'text_to_preview'));
			
		}

		
		/**
		 *Add Actions for the frontend
		 **/
		protected function addActions() {
		
			// Enable shortcodes in widget areas
			add_filter('widget_text', 'do_shortcode');
			
			//default wordpress hooking
			add_action('wp_head', array($this,'load_shortcode_assets'), 2000);
			add_filter( 'template_include' , array($this, 'template_include'), 20000); 
		}
		
		/**
		 *Automatically load assests like fonts into your frontend
		 **/
		public function load_shortcode_assets()
		{
			$output = "";
			$output .= avia_font_manager::load_font();
			
			/* if the builder is decoupled from the theme then make sure to only load iconfonts if they are actually necessary. in enfolds case it is
				
			foreach(AviaBuilder::$resources_to_load as $element)
			{
				if($element['type'] == 'iconfont')
				{
					$output .= avia_font_manager::load_font();
				}
			}
			*/
			
			echo $output;
			
			//output preview css paths
			if(is_admin()) echo $this->load_preview_css( $output );
		}
		
	
		/**
		 *load css and js files when in editable mode
		 **/
		public function admin_scripts_styles()
		{
			$ver = AviaBuilder::VERSION;
			
			#js
			wp_enqueue_script('avia_builder_js', $this->paths['assetsURL'].'js/avia-builder.js', array('jquery','jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-droppable','jquery-ui-datepicker','wp-color-picker','media-editor','post'), $ver, TRUE );
			wp_enqueue_script('avia_element_js' , $this->paths['assetsURL'].'js/avia-element-behavior.js' , array('avia_builder_js'), $ver, TRUE );
			wp_enqueue_script('avia_modal_js' , $this->paths['assetsURL'].'js/avia-modal.js' , array('jquery', 'avia_element_js', 'wp-color-picker'), $ver, TRUE );
			wp_enqueue_script('avia_history_js' , $this->paths['assetsURL'].'js/avia-history.js' , array('avia_element_js'), $ver, TRUE );
			wp_enqueue_script('avia_tooltip_js' , $this->paths['assetsURL'].'js/avia-tooltip.js' , array('avia_element_js'), $ver, TRUE );

			
			#css
			wp_enqueue_style( 'avia-modal-style' , $this->paths['assetsURL'].'css/avia-modal.css');
			wp_enqueue_style( 'avia-builder-style' , $this->paths['assetsURL'].'css/avia-builder.css');
			wp_enqueue_style( 'wp-color-picker' );
			
			/**
			 * @since 4.2.3 we support columns in rtl order (before they were ltr only). To be backward comp. with old sites use this filter.
			 */
			if( is_rtl() && ( 'yes' == apply_filters( 'avf_rtl_column_support', 'yes' ) ) )
			{
				wp_enqueue_style( 'avia-builder-rtl-style' , $this->paths['assetsURL'].'css/avia-builder-rtl.css');
			}
			
			#localize strings for javascript
			include_once($this->paths['configPath']."javascript_strings.php");

			if(!empty($strings))
			{
				foreach($strings as $key => $string)
				{
					wp_localize_script( $key, str_replace('_js', '_L10n', $key), $string );
				}
			}
			
			
		}
		
		
		public function load_preview_css( $icon_font = "", $css = "" )
		{
			$output				= "";
			$template_url 		= get_template_directory_uri();
			$child_theme_url 	= get_stylesheet_directory_uri();
			$avia_dyn_stylesheet_url = false;
			$ver = AviaBuilder::VERSION;
			
			global $avia;
			
			$safe_name = avia_backend_safe_string($avia->base_data['prefix']);
			$safe_name = apply_filters('avf_dynamic_stylesheet_filename', $safe_name);
	
	        if( get_option( 'avia_stylesheet_exists' . $safe_name ) == 'true' )
	        {
	            $avia_upload_dir = wp_upload_dir();
				
				/**
				 * Change the default dynamic upload url
				 * 
				 * @since 4.4
				 */
				$avia_dyn_upload_path = apply_filters('avf_dyn_stylesheet_dir_url',  $avia_upload_dir['baseurl'] . '/dynamic_avia' );
				$avia_dyn_upload_path = trailingslashit( $avia_dyn_upload_path );
				
	            if( is_ssl() ) 
				{
					$avia_dyn_upload_path = str_replace("http://", "https://", $avia_dyn_upload_path );
				}
				
				/**
				 * Change the default dynamic stylesheet name
				 * 
				 * @since 4.4
				 */
	            $avia_dyn_stylesheet_url = apply_filters( 'avf_dyn_stylesheet_file_url', $avia_dyn_upload_path . $safe_name . '.css' );
	        }
	        
	        $google_fonts = array(avia_get_option('google_webfont'), avia_get_option('default_font'));
	        
	        foreach($google_fonts as $font)
	        {
	        	$font_weight = "";

				if(strpos($font, ":") !== false)
				{
					$data = explode(':',$font);
					$font = $data[0];
					$font_weight = $data[1];
				}
	        
		        if(strpos($font, 'websave') === false)
				{
					$avia->style->add_google_font($font, $font_weight);
				}
	        }
	        
			//if no user defined css is available load all the default frontend css
			if(empty($css))
			{
				$css = array(
					
					includes_url('/js/mediaelement/mediaelementplayer-legacy.min.css') => 1, 
					includes_url('/js/mediaelement/wp-mediaelement.css?ver=4.9.4') => 1, 
					
					$template_url."/css/grid.css" => 1,
					$template_url."/css/base.css" => 1,
					$template_url."/css/layout.css" => 1,
					$template_url."/css/shortcodes.css" => 1,
					$template_url."/js/aviapopup/magnific-popup.css" => 1,
					$template_url."/css/rtl.css" => is_rtl(),
					$child_theme_url."/style.css" => $template_url != $child_theme_url,
				);
				
				// iterate over template builder modules and load the default css in there as well. 
				// hakish approach that might need refinement if we improve the backend preview
				$path = trailingslashit (dirname( $this->paths['pluginPath'])) . "avia-shortcodes/";
				
				foreach(glob($path.'*', GLOB_ONLYDIR) as $folder)
				{
					$css_file 	= trailingslashit($folder) . basename($folder) . ".css";
					$css_url	= trailingslashit($this->paths['pluginUrlRoot']) . "avia-shortcodes/" . basename($folder) ."/". basename($folder) . ".css";
					
					if(file_exists( $css_file ))
					{
						$css[ $css_url ] = 1;
					}
				}

				
				//custom user css, overwriting our styles
				$css[$template_url."/css/custom.css"] = 1;
				$css[$avia_dyn_stylesheet_url] = $avia_dyn_stylesheet_url;
				$css[$template_url."/css/admin-preview.css"] = 1;
				
				$css = apply_filters('avf_preview_window_css_files' , $css );
			}
			
			//module css
			if(is_array($css))
			{
				foreach($css as $url => $load)
				{
					if($load) $output .= "<link rel='stylesheet' href='".$url."?ver=".$ver."' type='text/css' media='all' />";
				}
			}
			
			$output .= "<script type='text/javascript' src='".includes_url('/js/jquery/jquery.js')."?ver=".$ver."'></script>";
			$output .= "<script type='text/javascript' src='".$template_url."/js/avia-admin-preview.js?ver=".$ver."'></script>";
			$output .= $avia->style->link_google_font();



			$error = __('It seems you are currently adding some HTML markup or other special characters. Once all HTML tags are closed the preview will be available again. If this message persists please check your input for special characters and try to remove them.','avia_framework');
			$html  = "<script type='text/javascript'>var avia_preview = " . json_encode( array( "error" => $error, "paths" => $output.$icon_font, 'title' => __('Element Preview','avia_framework') , 'background' => __('Set preview background:','avia_framework'), 'scale' => __('Scaled to:','avia_framework')) )  . "; \n";
			$html .= "</script>";
		
			return $html;
		}

		/**
		 *multilanguage activation
		 **/
		public function loadTextDomain() 
		{
			load_plugin_textdomain( 'avia_framework', false, $this->paths['pluginDir'] . 'lang/');
		}
		
		/**
		 *safe mode or debugging
		 **/
		public function setMode($status = "")
	 	{
			AviaBuilder::$mode = apply_filters('avia_builder_mode', $status);
		}
		
		
		/**
		 * Returns the instance of ShortcodeParser
		 * 
		 * @since 4.2.1
		 * @return ShortcodeParser
		 */
		public function get_shortcode_parser()
		{
			if( is_null( $this->shortcode_parser ) )
			{
				$this->shortcode_parser = new ShortcodeParser( $this->get_posts_shortcode_parser_state() );
			}
			
			return $this->shortcode_parser;
		}
		
		/**
		 * Returns a filtered array of supported post types
		 * 
		 * @since 4.3
		 * @return array
		 */
		public function get_supported_post_types()
		{
			return apply_filters( 'avf_alb_supported_post_types', $this->supported_post_types );
		}
		
		/**
		 * Returns a filtered array of supported post status
		 * 
		 * @since 4.4.1
		 * @return array
		 */
		public function get_supported_post_status()
		{
			return apply_filters( 'avf_alb_supported_post_status', $this->supported_post_status );
		}

		/**
		 * Returns the state for the shortcode parser for the current or given post
		 * 
		 * @since 4.2.1
		 * @param int|null $post_id
		 * @param string $default 
		 * @return string					'disabled' | 'check_only' | 'auto_repair'
		 */
		public function get_posts_shortcode_parser_state( $post_id = null, $default = 'disabled' )
		{
			
			if( is_null( $post_id ) || ! is_numeric( $post_id ) )
			{
				if( ! empty( $this->posts_shortcode_parser_state ) )
				{
					return $this->posts_shortcode_parser_state;
				}
				
				$post_id =  isset( $_POST['post_ID'] ) ? (int)$_POST['post_ID'] : get_the_ID();
			}
			
			if( ! in_array( $default, array( 'disabled', 'check_only', 'auto_repair' ) ) )
			{
				$default = 'disabled';
			}
			
			if( false !== $post_id )
			{
				$this->posts_shortcode_parser_state = get_post_meta( $post_id, '_avia_sc_parser_state', true );
			}
			
			if( empty( $this->posts_shortcode_parser_state ) )
			{
				$this->posts_shortcode_parser_state = $default;
			}
			
			return $this->posts_shortcode_parser_state;
		}
		
		
		/**
		 * Updates the state for the shortcode parser for the current or given post and
		 * returns the actual value stored in DB
		 * 
		 * @since 4.2.1
		 * @param string $state					'disabled' | 'check_only' | 'auto_repair'
		 * @param int|null $post_id
		 * @param $save_to_revision boolean
		 * @return string
		 */
		public function set_posts_shortcode_parser_state( $state = '', $post_id = null, $save_to_revision = false )
		{
			if( ! in_array( $state, array( 'disabled', 'check_only', 'auto_repair' ) ) )
			{
				$state = 'check_only';
			}
			
			$this->posts_shortcode_parser_state = $state;
			
			if( is_null( $post_id ) || ! is_numeric( $post_id ) )
			{
				$post_id =  isset( $_POST['post_ID'] ) ? (int)$_POST['post_ID'] : get_the_ID();
			}
			
			if( false !== $post_id )
			{
				if( $save_to_revision === false )
				{
					update_post_meta( $post_id, '_avia_sc_parser_state', $this->posts_shortcode_parser_state );
				}
				else
				{
					update_metadata( 'post', $post_id, '_avia_sc_parser_state', $this->posts_shortcode_parser_state );
				}
			}
			
			return $this->posts_shortcode_parser_state;
		}
		
		/**
		 * Returns the state of the ALB for a given post id.
		 * If null, checks for $_POST['aviaLayoutBuilder_active'] or the current post id.
		 * 
		 * @since 4.2.1
		 * @param int|null $post_id
		 * @param string $default				'active' | ''
		 * @return string
		 */
		public function get_alb_builder_status( $post_id = null, $default = '' )
		{
			if( is_null( $post_id ) )
			{
				/**
				 * Check if we are on an edit page
				 */
				if( isset( $_POST['aviaLayoutBuilder_active'] ) )
				{
					$builder_status = $_POST['aviaLayoutBuilder_active'];
					if( ! in_array( $builder_status, array( 'active', '' ) ) )
					{
						$builder_status = $default;
					}
					
					return $builder_status;
				}
				
				/**
				 * If set, return the saved value
				 */
				if( 'unknown' != $this->alb_builder_status )
				{
					return $this->alb_builder_status;
				}
			}
			
			$id = ! is_null( $post_id ) ? $post_id : get_the_ID();
			
			if( false === $id )
			{
				return $default;
			}
			
			$status = get_post_meta( $id, '_aviaLayoutBuilder_active', true );
			
			/**
			 * Allows to filter the status
			 * 
			 * @used_by			enfold\config-woocommerce\config.php		10
			 */
			$status = apply_filters( 'avf_builder_active', $status, $id );
			
			return $status;
		}
		
		/**
		 * Set the builder status for the current or a given post id
		 * 
		 * @since 4.2.1
		 * @param string $status				'active' | ''
		 * @param int|null $post_id
		 * @param string $default				'active' | ''		
		 * @param type $save_to_revision boolean 
		 * @return boolean
		 */
		public function set_alb_builder_status( $status = '', $post_id = null, $default = '', $save_to_revision = false ) 
		{
			if( ! in_array( $status, array( 'active', '' ) ) )
			{
				$status = $default;
			}
			
			$id = ! is_null( $post_id ) ? $post_id : get_the_ID();
			
			if( is_null( $post_id ) )
			{
				$this->alb_builder_status = $status;
			}
			
			if( false === $id )
			{
				return false;
			}
			
			if( $save_to_revision === false )
			{
				update_post_meta( $id, '_aviaLayoutBuilder_active', $status );
			}
			else
			{
				update_metadata( 'post', $id, '_aviaLayoutBuilder_active', $status );
			}
			
			return true;
		}
		
		
		/**
		 * Returns the ALB shortcodes for the requested post
		 * 
		 * @since 4.2.3
		 * @param int $post_id
		 * @return string
		 */
		public function get_posts_alb_content( $post_id )
		{
			/**
			 * @since 4.5.5
			 * @return string
			 */
			return apply_filters( 'avf_posts_alb_content', get_post_meta( $post_id, '_aviaLayoutBuilderCleanData', true ), $post_id );
		}
		
		/**
		 * Returns the correct content for a page/post/...
		 * ALB content, post content (or in future releases any ALB structure)
		 * 
		 * @since 4.3
		 * @param int $post_id
		 * @param WP_Post $post 
		 * @return string|false
		 */
		public function get_post_content( $post_id, $post = null )
		{
			$builder_stat = $this->get_alb_builder_status( $post_id );
						
			if( 'active' == $builder_stat )
			{
				return $this->get_posts_alb_content( $post_id );
			}
			
			if( is_null( $post ) || ( ! $post instanceof WP_Post ) )
			{
				$post = get_post( $post_id );
			}
			
			if( $post instanceof WP_Post )
			{
				return $post->post_content;
			}
			
			return false;
		}

		/**
		 * Updates the ALB or normal post content depending on ALB status
		 * 
		 * @since 4.3
		 * @param int $post_id
		 * @param string $content
		 */
		public function update_post_content( $post_id, $content )
		{
			$builder_stat = $this->get_alb_builder_status( $post_id );
						
			if( 'active' == $builder_stat )
			{
				$this->save_posts_alb_content( $post_id, $content );
				
				/**
				 * @used_by			currently unused
				 * @since 4.5.2
				 */
				do_action( 'ava_builder_updated_posts_alb_content', $post_id, $content );
			}
			else
			{
				$new_data = array(
								'ID'           => $post_id,
								'post_content' => $content
							);

				wp_update_post( $new_data );
			}
		}

		/**
		 * Save the ALB shortcodes for the given post in the post meta field
		 * 
		 * @since 4.2.3
		 * @param int $post_id
		 * @param string $content
		 * @param $save_to_revision boolean 
		 * @return boolean
		 */
		public function save_posts_alb_content( $post_id, $content, $save_to_revision = false )
		{
			if( $save_to_revision === false )
			{
				return update_post_meta( $post_id, '_aviaLayoutBuilderCleanData', $content );
			}
			else 
			{
				return update_metadata( 'post', $post_id, '_aviaLayoutBuilderCleanData', $content );
			}
		}
		
		/**
		 * Returns the shortcode tree postmeta
		 * 
		 * @since 4.5.1
		 * @param int $post_id
		 * @return array
		 */
		public function get_shortcode_tree( $post_id )
		{
			$tree = get_post_meta( $post_id, '_avia_builder_shortcode_tree', true );
			return is_array( $tree ) ? $tree : array();
		}
		
		/**
		 * Saves the shortcode tree in the postmeta
		 * 
		 * @since 4.5.1
		 * @param int $post_id
		 * @param array $tree
		 * @param $save_to_revision boolean
		 * @return boolean
		 */
		public function save_shortcode_tree( $post_id, array $tree, $save_to_revision = false )
		{
			if( $save_to_revision === false )
			{
				return update_post_meta( $post_id, '_avia_builder_shortcode_tree', $tree );
			}
			
			return update_metadata( 'post', $post_id, '_avia_builder_shortcode_tree', $tree );
		}
		
		/**
		 * Allows to set the post revision ID manually
		 * (needed when posts are saved via REST API - since WP 5.0 and Gutenberg)
		 * 
		 * @since 4.5.1
		 * @param int $post_id
		 */
		public function set_revision_id( $post_id )
		{
			$this->revision_id = $post_id;
		}

		/**
		 *set fullwidth elements that need to interact with section shortcode
		 **/
		public function setFullwidthElements($elements = array())
	 	{
		 	$elements = apply_filters('avf_fwd_elements', $elements);
		 	
			AviaBuilder::$full_el_no_section = $elements;
			AviaBuilder::$full_el = array_merge(array('av_section'), $elements);
		}
		
		
		
		/**
		 *calls external classes that are needed for the script to operate
		 **/
		public function call_classes()
		{
			//create the meta boxes
			new MetaBoxBuilder($this->paths['configPath']);

			// save button
			$this->builderTemplate = new AviaSaveBuilderTemplate($this);
			
			//activate helper function hooks
			AviaHelper::backend();
			
			/**
			 * Create the linebreak button
			 */
			$tiny_lb = array(
				'id'					=> 'av_builder_linebreak',
				'title'					=> __( 'Permanent Line Break', 'avia_framework' ),
				'access_key'			=> apply_filters( 'avf_access_key_tinymce', 'ctrl+alt+n', 'av_builder_linebreak' ),
				'content_open'			=> '\r\n<br class="avia-permanent-lb" />',
				'image'					=> $this->paths['imagesURL'] . 'tiny_line_break.png',
				'js_plugin_file'		=> $this->paths['assetsURL'] . 'js/avia-tinymce-linebreak.js',
				'qtag_content_open'		=> '\r\n<br class="avia-permanent-lb" />',
				'qtag_display'			=> __( 'Line Break', 'avia_framework' ),
				'shortcodes'			=> array()
			);
			
			new avia_tinyMCE_button( $tiny_lb );
			
			//create tiny mce button
			$tiny = array(
				'id'				=> 'avia_builder_button',
				'title'				=> __('Insert Theme Shortcode','avia_framework' ),
				'image'				=> $this->paths['imagesURL'].'tiny-button.png',
				'js_plugin_file'	=> $this->paths['assetsURL'].'js/avia-tinymce-buttons.js',
				'shortcodes'		=> array_map(array($this, 'fetch_configs'), $this->shortcode_class)
			);
			
			//if we are using tinymce 4 or higher change the javascript file
			global $tinymce_version;
			
			if(version_compare($tinymce_version[0], 4, ">="))
			{
				$tiny['js_plugin_file'] = $this->paths['assetsURL'].'js/avia-tinymce-buttons-4.js';
			}

			new avia_tinyMCE_button($tiny);
			$this->alb_magic_wand_button = true;
			
			//activate iconfont manager
			new avia_font_manager();
						
		    //fetch all Wordpress pointers that help the user to use the builder
			include($this->paths['configPath']."pointers.php");
			$myPointers = new AviaPointer($pointers);
		}
		
		/**
		 * 
		 * @since 4.2.3
		 * @return aviaAssetManager
		 */
		public function asset_manager()
		{
			if(empty($this->asset_manager_class))
			{
				//activate asset_manager
				$this->asset_manager_class = new aviaAssetManager( $this );
			}
			
			return $this->asset_manager_class;
		}
		
		/**
		 * Get instance of the element manager
		 * 
		 * @since 4.3
		 * @return aviaElementManager
		 */
		public function element_manager()
		{
			if( empty( $this->element_manager ) )
			{
				//activate element_manager
				$this->element_manager = new aviaElementManager();
			}
			
			return $this->element_manager;
		}
		
		
		/**
		 * allows to filter which shortcode assets to display in the frontend. waits for the "wp" hook so the post id is already available
		 * 
		 * @since 4.3
		 */
		public function frontend_asset_check()
		{
		 	//before creating shortcodes allow to filter the assets that are loaded in the frontend
		 	//pass shortcode names like "av_video" or "av_audio" in an array
		 	$this->disabled_assets = apply_filters( 'avf_disable_frontend_assets', $this->disabled_assets );
			
			/**
			 * Enable additional elements needed for parser info page
			 */
			if( isset( $_REQUEST['avia_alb_parser'] ) && ( 'show' == $_REQUEST['avia_alb_parser'] ) && current_user_can( 'edit_post', get_the_ID() ) )
			{
				if( class_exists( 'ShortcodeParser' ) )
				{
					$this->disabled_assets = ShortcodeParser::enable_used_assets( $this->disabled_assets );
				}
			}
		}

		
		/**
		 *array mapping helper that returns the config arrays of a shortcode
		 **/
		 
		public function fetch_configs($array)
		{
			return $array->config;
		}
        
        /**
		 *class that adds an extra class to the body if the builder is active	
		 **/
        public function admin_body_class($classes)
        {
	        global $post_ID;
	        
	        if(!empty($post_ID) && $this->get_alb_builder_status($post_ID))
	        {
	        	$classes .= ' avia-advanced-editor-enabled ';
	        }
	        
	        if($this->disable_drag_drop == true)
	        {
		        $classes .= ' av-no-drag-drop ';
	        }
			
			/**
			 * @since 4.2.3 we support columns in rtl order (before they were ltr only). To be backward comp. with old sites use this filter.
			 */
			if( is_rtl() && ( 'yes' == apply_filters( 'avf_rtl_column_support', 'yes' ) ) )
	        {
				$classes .= ' rtl ';
			}
			
	        return $classes;
        }
        

	 	
	 	/**
		 *automatically load all child classes of the aviaShortcodeTemplate class and create an instance
		 **/
	 	public function createShortcode()
	 	{
		 			 	
	 		$children  = array();
			foreach(get_declared_classes() as $class)
			{
			    if(is_subclass_of($class, 'aviaShortcodeTemplate'))
			    {
			    	 $allow = false;
			    	 $children[] = $class;
			    	 $this->shortcode_class[$class] = new $class($this);
			    	 $shortcode = $this->shortcode_class[$class]->config['shortcode'];
			    	 
			    	 //check if the shortcode is allowed. if so init the shortcode, otherwise unset the item
			    	 if( empty(ShortcodeHelper::$manually_allowed_shortcodes) && empty(ShortcodeHelper::$manually_disallowed_shortcodes) ) $allow = true;
			    	 if( !$allow && !empty(ShortcodeHelper::$manually_allowed_shortcodes) && in_array($shortcode, ShortcodeHelper::$manually_allowed_shortcodes)) $allow = true;
			    	 if( !$allow && !empty(ShortcodeHelper::$manually_disallowed_shortcodes) && !in_array($shortcode, ShortcodeHelper::$manually_disallowed_shortcodes)) $allow = true;
			    	 
			    	 
			    	 if($allow)
			    	 {
			    	 	$this->shortcode_class[$class]->init(); 
			    	 	$this->shortcode[$this->shortcode_class[$class]->config['shortcode']] = $class;
			    	 	
			    	 	
			    	 	//save if the asset may be disabled automatically
			    	 	if(!empty( $this->shortcode_class[$class]->config['disabling_allowed']) && $this->shortcode_class[$class]->config['disabling_allowed'] !== "manually") 
			    	 	{
				    	 	$this->may_be_disabled_automatically[] = $this->shortcode_class[$class]->config['shortcode'] ;
			    	 	}
			    	 	
			    	 	//save shortcode as allowed by default. if we only want to display the shortcode in tinymce remove it from the list but keep the class instance alive
			    	 	if(empty($this->shortcode_class[$class]->config['tinyMCE']['tiny_only']))
			    	 	{
			    			ShortcodeHelper::$allowed_shortcodes[] = $this->shortcode_class[$class]->config['shortcode'];
			    		}
			    		
			    		//save nested shortcodes if they exist
			    		if(isset($this->shortcode_class[$class]->config['shortcode_nested'])) 
			    		{
			    			ShortcodeHelper::$nested_shortcodes = array_merge(ShortcodeHelper::$nested_shortcodes, $this->shortcode_class[$class]->config['shortcode_nested']);
			    	 	}
			    	 }
			    	 else
			    	 {
			    	 	unset($this->shortcode_class[$class]);
			    	 }
			    }
			}
			
			/**
			 * Initialise reference to parent and children shortcode(s) so we know the default structure of the elements.
			 * Nested shortcodes are merged with layout_children.
			 */
			foreach( $this->shortcode_class as $class => &$sc ) 
			{
				$nested = isset( $sc->config['shortcode_nested'] ) && is_array( $sc->config['shortcode_nested'] ) ? $sc->config['shortcode_nested'] : array();
				$sc->config['layout_children'] = array_unique( array_merge( $nested, $sc->config['layout_children'] ) );
				
				foreach( $sc->config['layout_children'] as $child_sc ) 
				{
					if( ! isset( $this->shortcode_parents[ $child_sc ] ) )
					{	
						$this->shortcode_parents[ $child_sc ] = array();
					}
					$this->shortcode_parents[ $child_sc ][] = $sc->config['shortcode'];
				}
			}
			
			unset( $sc );
	 	}
		
		/**
		 * Gets an opening or closing tag or a start fragment and returns the shortcode
		 * 
		 * @since 4.2.1
		 * @param type $tag
		 * @return false|string
		 */
		public function extract_shortcode_from_tag( $tag = '' ) 
		{
			 if( empty( $tag) || ! is_string( $tag ) )
			{
				return false;
			}
			
			$match = array();
			
			$regex = '\[\/?([\w|-]+)';			//	gets opening and closing tag till first space after tag
			preg_match_all( "/" . $regex . "/s", $tag, $match, PREG_OFFSET_CAPTURE );
			
			if( empty( $match ) )
			{
				return false;
			}
			
			return $match[1][0][0];
		}

		
		/**
		 * Gets an opening or closing shortcode tag (or the beginning part of it, extracts the shortcode and returns the shortcode class
		 * 
		 * @since 4.2.1
		 * @param string $tag						a valid shortcode tag
		 * @return aviaShortcodeTemplate|false
		 */
		public function get_sc_class_from_tag( $tag = '' )
		{
			$sc_name = $this->extract_shortcode_from_tag( $tag );
			if( false === $sc_name )
			{
				return false;
			}
			
			return $this->get_shortcode_class( $sc_name );
		}
		
		
		/**
		 * Returns the shortcode class
		 * 
		 * @since 4.2.1
		 * @param string $sc_name
		 * @return aviaShortcodeTemplate|false
		 */
		public function get_shortcode_class( $sc_name )
		{
			return ( isset( $this->shortcode[ $sc_name ] ) && isset( $this->shortcode_class[ $this->shortcode[ $sc_name ] ] ) ) ? $this->shortcode_class[ $this->shortcode[ $sc_name ] ] : false;
		}
		

		/**
		 * Returns the array with the parents shortcodes 
		 * 
		 * @since 4.2.1
		 * @param string $tag
		 * @return array
		 */
		public function get_sc_parents_from_tag( $tag = '' )
		{
			$sc_name = $this->extract_shortcode_from_tag( $tag );
			if( false === $sc_name )
			{
				return array();
			}
			
			return ( isset( $this->shortcode_parents[ $sc_name ] ) ) ? $this->shortcode_parents[ $sc_name ] : array();
		}

		
		/**
		 *create JS templates
		 **/
		public function js_template_editor_elements()
		{
			foreach($this->shortcode_class as $shortcode)
			{
				$class 	= $shortcode->config['php_class'];
				$template = $this->shortcode_class[$class]->prepare_editor_element();
				
				if(is_array($template)) continue;
				
				echo "\n<script type='text/html' id='avia-tmpl-{$class}'>\n";
				echo $template;
				echo "\n</script>\n\n";
			}

		}
		
		
		/**
		 * Balance the shortcode in the post content of a non ALB page.
		 * @since 4.3: Also check page content for ALB shortcodes and add a unique id to shortcodes
		 * 
		 * @since 4.2.1
		 * @param array $data
		 * @param array $postarr
		 * @return array
		 */
		public function handler_before_save_alb_post_data( array $data, array $postarr )
		{
			/**
			 * Get current ALB values and save to post meta
			 */
			$builder_stat = $this->get_alb_builder_status();
			$this->set_alb_builder_status( $builder_stat );
			
			$parser_state = isset( $_POST['_avia_sc_parser_state'] ) ?  $_POST['_avia_sc_parser_state'] : '';
			$parser_state = $this->set_posts_shortcode_parser_state( $parser_state );
			
			$post_id = (int)$postarr['ID'];
			
			/**
			 * Check the hidden container, balance the shortcodes and add missing id's
			 */
			if( isset( $_POST['_aviaLayoutBuilderCleanData'] ) ) 
			{
				$this->get_shortcode_parser()->set_builder_save_location( 'clean_data' );
				$_POST['_aviaLayoutBuilderCleanData'] = ShortcodeHelper::clean_up_shortcode( $_POST['_aviaLayoutBuilderCleanData'], 'balance_only' );
				$_POST['_aviaLayoutBuilderCleanData'] = $this->element_manager->set_element_ids_in_content( $_POST['_aviaLayoutBuilderCleanData'], $post_id );
			}				
				
			if( 'active' == $builder_stat )
			{
				return $data;
			}
			
			/**
			 * Normal pages we only balance the shortcodes but do not modify the otber content to keep all user stylings 
			 */
			$this->post_content = isset( $data['post_content'] ) ? trim( $data['post_content'] ) : '';
			$this->get_shortcode_parser()->set_builder_save_location( 'content' );
			$this->post_content = ShortcodeHelper::clean_up_shortcode( $this->post_content, 'balance_only' );
			
			/**
			 * Scan content and add missing unique id'S
			 */
			$this->post_content = $this->element_manager->set_element_ids_in_content( $this->post_content, $post_id );
			
			$data['post_content'] = $this->post_content;
			
			return $data;
		}
		
		/**
		 * Remove Boxes not needed, e.g. Enfold Parser Metabox 
		 * 
		 * @since 4.2.1
		 * @param array $boxes
		 * @return array
		 */
		public function handler_alb_metabox_filter( array $boxes )
		{
			if( 'debug' == AviaBuilder::$mode )
			{
				return $boxes;
			}
			
			foreach ( $boxes as $key => $box ) 
			{
				if( 'avia_sc_parser' == $box['id'] )
				{
					unset( $boxes[ $key ] );
				}
			}
			
			$boxes = array_merge( $boxes );
			return $boxes;
		}
		
		/**
		 * Save builder relevant data of the post in backend - $_POST['content'] has already been saved at this point.
		 * 
		 *		- Save status of builder (open/closed)
		 *		- Save and balance shortcodes in _aviaLayoutBuilderCleanData
		 *		- Create the shortcode tree
		 **/
		public function meta_box_save()
		{
			
			if( isset( $_POST['post_ID'] ) )
			{
				/**
				 * New states have been saved already in handler_before_save_alb_post_data
				 */
				$builder_stat = $this->get_alb_builder_status();
				$parser_state = $this->get_posts_shortcode_parser_state();
				$post_id = (int) $_POST['post_ID'];
				
				
				/**
				 * Save the hidden container already checked and updated in handler_before_save_alb_post_data
				 */
                if( isset( $_POST['_aviaLayoutBuilderCleanData'] ) ) 
                {
					$this->save_posts_alb_content( $post_id, $_POST['_aviaLayoutBuilderCleanData'] );
                }				
				
				/**
				 * Copy balanced shortcodes to content field so the shortcode tree can be built
				 * 
				 */
                if( 'active' == $builder_stat )
                {
					$this->get_shortcode_parser()->set_builder_save_location( 'content' );
					if( isset( $_POST['_aviaLayoutBuilderCleanData'] ) )
					{
						$_POST['content'] = ShortcodeHelper::clean_up_shortcode( $_POST['_aviaLayoutBuilderCleanData'], 'content' );
					}
					else
					{
						/**
						 *	_aviaLayoutBuilderCleanData should be set by default, so this is only a fallback
						 */
						$_POST['content'] = ShortcodeHelper::clean_up_shortcode( $_POST['content'], 'content' );
					}
                }
				else
				{
					$_POST['content'] = $this->post_content;
				}

				
                //extract all ALB shortcodes from the post array and store them so we know what we are dealing with when the user opens a page. 
                //usesfull for special elements that we might need to render outside of the default loop like fullscreen slideshows
//				$matches = array();
//			    preg_match_all("/".ShortcodeHelper::get_fake_pattern()."/s", $_POST['content'], $matches, PREG_OFFSET_CAPTURE );
				
			
				/**
				 * Extract all ALB shortcodes from the post array and store them so we know what we are dealing with when the user opens a page.
				 * Usesfull for special elements that we might need to render outside of the default loop like fullscreen slideshows.
				 * 
				 * We always save this tree so we can be sure to have the correct state of the page when we load this post meta.
				 */
				$tree = ShortcodeHelper::build_shortcode_tree( $_POST['content'] );
				$this->save_shortcode_tree( $post_id, $tree);
				
				$this->element_manager->updated_post_content( $_POST['content'], $post_id );
				
				/**
				 * Now we can save the postmeta data to revision post
				 */
				$this->save_alb_revision_data( $post_id );
            }
		}
		
		
		
		/**
		 *function that checks if a dynamic template exists and uses that template instead of the default page template
		 **/
    	public function template_include( $original_template )
    	{	
    		global $avia_config;
    	
    	   	$post_id = @get_the_ID();
    	   	
    	   	if( is_feed() )
			{
				return;
			}
			
			if( is_embed() )
			{
				return $original_template;
			}
		

    	   	if(($post_id && is_singular()) || isset($avia_config['builder_redirect_id']))
    	   	{
				if(!empty($avia_config['builder_redirect_id'])) $post_id = $avia_config['builder_redirect_id'];
    	   	
				ShortcodeHelper::$tree = $this->get_shortcode_tree( $post_id );
				
				$builder_template = locate_template( 'template-builder.php', false );
				
				/**
				 * Redirect to default ALB template if we need to show parser debug info (implemented with default shortcode content)
				 */
				if( isset( $_REQUEST['avia_alb_parser'] ) && ( 'show' == $_REQUEST['avia_alb_parser'] ) && ( '' != $builder_template ) )
				{
					$avia_config['conditionals']['is_builder'] = true;
					$avia_config['conditionals']['is_builder_template'] = true;
					return $builder_template;
				}
				
				if( ( 'active' == $this->get_alb_builder_status( $post_id ) ) && ( '' != $builder_template ) )
				{
					$avia_config['conditionals']['is_builder'] = true;

					//only redirect if no custom template is set
					$template_file = get_post_meta( $post_id, '_wp_page_template', true );

					if( "default" == $template_file || empty( $template_file ) )
					{
						$avia_config['conditionals']['is_builder_template'] = true;
						return $builder_template;
					}
				}
				else 
				{
					/**
					 * In case we are in preview mode we have to rebuild the shortcode tree so the user can see the real result of the page
					 */
					if( is_preview() )
					{
						global $post;
						
						/**
						 * If user views a preview we must use the content because WordPress doesn't update the post meta field
						 */
						setup_postdata( $post );
						$content = apply_filters( 'avia_builder_precompile', get_the_content() );
		
						/**
						 * In preview we must update the shortcode tree to reflect the current page structure.
						 * Prior make sure that shortcodes are balanced and save this in post_content so we have 
						 * the updated content when displaying the page.
						 */
						$this->get_shortcode_parser()->set_builder_save_location( 'preview' );
						$post->post_content = ShortcodeHelper::clean_up_shortcode( $content, 'balance_only' );
						ShortcodeHelper::$tree = ShortcodeHelper::build_shortcode_tree( $post->post_content );
					}
				}
    	   	   
    	   	   //if a custom page was passed and the template builder is not active redirect to the default page template
    	   	   if(isset($avia_config['builder_redirect_id']))
    	   	   {
    	   	   		if($template = locate_template('page.php', false))
    	   	   		{
    	   	   			return $template;
    	   	   		}
    	   	   }
    	   	}
    	   	
    	   	return $original_template;
    	}
    	
    	public function apply_editor_wrap()
    	{
    		//fetch the config array
    		include($this->paths['configPath']."meta.php");
    		
    		$slug = "";
    		$pages = array();
    		//check to which pages the avia builder is applied
    		foreach($elements as $element)
    		{
    			if(is_array($element['type']) && $element['type'][1] == 'visual_editor')
    			{
    				$slug = $element['slug']; break;
    			}
    		}
    		
    		foreach($boxes as $box)
    		{
    			if($box['id'] == $slug)
    			{
    				$pages = $box['page'];
    			}
    		}
    		global $typenow;
    		
    		if(!empty($pages) && in_array($typenow, $pages))
    		{	    
		    	//html modification of the admin area: wrap
		    	add_action( 'edit_form_after_title', array( $this, 'wrap_default_editor' ), 100000, 2 ); 
		    	add_action( 'edit_form_after_editor', array( $this, 'close_default_editor_wrap' ), 1, 1 ); 
		    }
    	}
    	
				
		/**
		 * Adds the ALB switch button
		 * 
		 * @param WP_Post $post
		 * @param string $close_div			'' | 'close'
		 */
		public function wrap_default_editor( $post = null, $close_div = '' )
		{
            global $post_ID;
			
            $status         = $this->get_alb_builder_status( $post_ID );
            $params 		= apply_filters('avf_builder_button_params', 
            			    	array(	'disabled'		=>false, 
           				    			'note' 			=> "", 
           				    			'noteclass'		=> "",
               			    			'button_class'	=>'',
               			    			'visual_label'  => __( 'Advanced Layout Editor', 'avia_framework' ),
               			    			'default_label' => __( 'Default Editor', 'avia_framework' )
               						)
               					);
               					 
            
            if($params['disabled']) { $status = false; }
            $active_builder = $status == "active" ? $params['default_label'] : $params['visual_label'];		
            $editor_class   = $status == "active" ? "class='avia-hidden-editor'" : "";
            $button_class   = $status == "active" ? "avia-builder-active" : "";
                
            
            
            echo "<div id='postdivrich_wrap' {$editor_class}>";
            
            if($this->disable_drag_drop == false)
			{
				echo '<a id="avia-builder-button" href="#" class="avia-builder-button button-primary '.$button_class.' '.$params['button_class'].'" data-active-button="'.$params['default_label'].'" data-inactive-button="'.$params['visual_label'].'">'.$active_builder.'</a>';
            }
			
            if( $params['note'] ) 
			{
				echo "<div class='av-builder-note ".$params['noteclass']."'>".$params['note']."</div>";
			}
			
			if( 'close' == $close_div )
			{
				echo '</div>';
			}
		}
		
		/**
		 * 
		 * @param WP_Post $post
		 */
		public function close_default_editor_wrap( $post = null )
		{
			echo "</div>";
		}
		
		
		/**
		 * function called by the metabox class that creates the interface in your wordpress backend
		 **/
		public function visual_editor($element)
		{
			$output = '';
			$tabs_output = '';
			$title  = '';
			$i = 0;
			
			/**
			 * @used_by				Avia_Gutenberg						10
			 * 
			 * @since 4.5.1
			 */
			$output = apply_filters( 'avf_builder_metabox_editor_before', $output, $element );
			
			
			$output .= '<div class="avia-builder-main-wrap">';
					
			$this->shortcode_buttons = apply_filters('avia_show_shortcode_button', array());	
			
			
			if(!empty($this->shortcode_buttons) && $this->disable_drag_drop == false)
			{
				$this->tabs = isset($element['tab_order']) ? array_flip($element['tab_order']) : array();
				foreach($this->tabs as &$empty_tabs) $empty_tabs = array();
				
				
				foreach ($this->shortcode_buttons as $shortcode)
				{
					if(empty($shortcode['tinyMCE']['tiny_only']))
					{
						if(!isset($shortcode['tab'])) $shortcode['tab'] = __("Custom Elements",'avia_framework' );
						
						$this->tabs[$shortcode['tab']][] = $shortcode;
					}
				}

				foreach($this->tabs as $key => $tab)
				{
					if(empty($tab)) continue;
					
					usort($tab,array($this, 'sortByOrder'));
				
					$i ++;
					$title .= "<a href='#avia-tab-$i'>".$key."</a>";
					
					$tabs_output .= "<div class='avia-tab avia-tab-$i'>";
					
					foreach ($tab as $shortcode)
					{
						if(empty($shortcode['invisible']))
						{
							$tabs_output .= $this->create_shortcode_button($shortcode);
						}
					}
					
					$tabs_output .= "</div>";
				}
			}
			
			global $post_ID;
			$active_builder  = $this->get_alb_builder_status( $post_ID );
			
			
			$extra = AviaBuilder::$mode != true ? "" : "avia_mode_".AviaBuilder::$mode;
			$hotekey_info = htmlentities($element['desc'], ENT_QUOTES, get_bloginfo( 'charset' ));
			
			$output .= '<div class="shortcode_button_wrap avia-tab-container"><div class="avia-tab-title-container">'.$title.'</div>' . $tabs_output . '</div>';
			$output .= '<input type="hidden" value="'.$active_builder.'" name="aviaLayoutBuilder_active" id="aviaLayoutBuilder_active" />';
			
			if($this->disable_drag_drop == false)
			{
			$output .= '<a href="#info" class="avia-hotkey-info" data-avia-help-tooltip="'.$hotekey_info.'">'.__('Information', 'avia_framework' ).'</a>';
			$output .= $this->builderTemplate->create_save_button();
			}
			
			$output .= "<div class='layout-builder-wrap {$extra}'>";
			
			if($this->disable_drag_drop == false)
			{
				$output .= "	<div class='avia-controll-bar'></div>";
			}
			
			$output .= "	<div id='aviaLayoutBuilder' class='avia-style avia_layout_builder avia_connect_sort preloading av_drop' data-dragdrop-level='0'>";
			$output .= "	</div>";
			
			
			$clean_data = $this->get_posts_alb_content( $post_ID );
			// $clean_data = htmlentities($clean_data, ENT_QUOTES, get_bloginfo( 'charset' )); //entity-test: added htmlentities
			
			
			$output .= "	<textarea id='_aviaLayoutBuilderCleanData' name='_aviaLayoutBuilderCleanData'>".$clean_data."</textarea>"; 
			$nonce	= 			wp_create_nonce ('avia_nonce_loader');
			$output .= '		<input type="hidden" name="avia-loader-nonce" id="avia-loader-nonce" value="'.$nonce.'" />';
			$output .= "</div>";
			
			$this->alb_nonce_added = true;
			
			$output .= '</div>     <!-- class="avia-builder-main-wrap" -->';
			
			return $output;
		}	
		
		/**
		 * Function called by the metabox class that creates the interface in your wordpress backend - 
		 * Output the Shortcode Parser Select and Info Panel below the normal Texteditor and above the ALB Editor
		 * 
		 * @since 4.2.1
		 * @param array $element
		 * @return string
		 */
		public function parser_select_panel( $element )
		{
			global $post_ID;
			
			$parser_state = $this->get_posts_shortcode_parser_state();
			$link = get_permalink( $post_ID );
			
			$args = array( 'avia_alb_parser' => 'show' );
			$link = add_query_arg( $args, $link );
			
			
			$out =	'';
			$out .=		'<div class="avia-builder-parser-section">';
//			$out .=			'<div class="avia-builder-parser-label">';
//			$out .=				'<label for="av_select_sc_parser">';
//			$out .=					__( 'Enfold Shortcode Parser:', 'avia_framework' );
//			$out .=				'</label>';
//			$out .=			'</div>';
			$out .=			'<div class="avia-builder-parser-select avia-form-element avia-style">';
			$out .=				'<select id="av_select_sc_parser" name="_avia_sc_parser_state" class="avia-style">';
			$out .=					'<option value="disabled" ' . selected( 'disabled', $parser_state, false ) . '>' . __( 'Disabled - No checks are done on update', 'avia_framework' ) . '</option>';
			$out .=					'<option value="check_only" ' . selected( 'check_only', $parser_state, false ) . '>' . __( 'Check enabled on update - checks the structure only', 'avia_framework' ) . '</option>';
			$out .=					'<option value="auto_repair" ' . selected( 'auto_repair', $parser_state, false ) . '>' . __( 'Auto Repair Function enabled - Repairs errors in shortcode structure during update', 'avia_framework' ) . '</option>';
			$out .=				'</select>';
			$out .=			'</div>';
			$out .=			'<div class="avia-builder-parser-info-button">';
			$out .=				'<a href="' . $link . '" class="button-primary" target="_blank">' . __( 'Show Parser Info', 'avia_framework') . '</a>';
			$out .=			'</div>';
			$out .=			'<div class="avia-builder-parser-message">';
			$out .=				$this->get_shortcode_parser()->display_dashboard_info();
			$out .=			'</div>';
			$out .=		'</div>';
			
			return $out;
		}


		/*create a shortcode button*/
		protected function create_shortcode_button($shortcode)
		{
			$class  = "";
			$shortcode = apply_filters('avf_shortcode_insert_button_backend', $shortcode);
			
			//disable element based on post type
			if(!empty($shortcode['posttype']) && $shortcode['posttype'][0] != AviaHelper::backend_post_type())
			{
				$shortcode['tooltip'] = $shortcode['posttype'][1];
				$class .= "av-shortcode-disabled ";
			}
			
			//disable element based on condition
			if(!empty($shortcode['disabled']) && $shortcode['disabled']['condition'] === true)
			{
				$shortcode['tooltip'] = $shortcode['disabled']['text'];
				$class .= "av-shortcode-disabled ";
			}
			
			
			
			
			$icon   = isset($shortcode['icon']) ? '<img src="'.$shortcode['icon'].'" alt="'.$shortcode['name'].'" />' : "";
			$data   = !empty($shortcode['tooltip']) ? " data-avia-tooltip='".$shortcode['tooltip']."' " : "";
			$data  .= !empty($shortcode['drag-level']) ? " data-dragdrop-level='".$shortcode['drag-level']."' " : "";
			$class .= isset($shortcode['class']) ? $shortcode['class'] : "";
            $class .= !empty($shortcode['target']) ? " ".$shortcode['target'] : "";

			
			
			
			
			$link   = "";
			$link  .= "<a {$data} href='#".$shortcode['php_class']."' class='shortcode_insert_button ".$class."' >".$icon.'<span>'.$shortcode['name']."</span></a>";
			
			return $link;
		}
		
		
		/*helper function to sort the shortcode buttons*/
		protected function sortByOrder($a, $b) 
		{
			if(empty($a['order'])) $a['order'] = 10;
			if(empty($b['order'])) $b['order'] = 10;
			
   			return $b['order'] >= $a['order'];
		}

		
		
		
		public function text_to_interface($text = NULL)
		{
			if(!current_user_can('edit_posts')) die();
			if(isset($_REQUEST['params']['_ajax_nonce'])) $_REQUEST['_ajax_nonce'] = $_REQUEST['params']['_ajax_nonce'];
			
			check_ajax_referer('avia_nonce_loader', '_ajax_nonce' );
			
			global $shortcode_tags;
			
			$allowed = false;
			
			if(isset($_POST['text'])) $text = $_POST['text']; //isset when avia_ajax_text_to_interface is executed (avia_builder.js)
			if(isset($_POST['params']) && isset($_POST['params']['allowed'])) $allowed = explode(',',$_POST['params']['allowed']); //only build pattern with a subset of shortcodes
			
			//build the shortcode pattern to check if the text that we want to check uses any of the builder shortcodes
			ShortcodeHelper::build_pattern($allowed);
			$text_nodes = preg_split("/".ShortcodeHelper::$pattern."/s", $text);
			
			
			foreach( $text_nodes as $node ) 
			{				
	            if( strlen( trim( $node ) ) == 0 || strlen( trim( strip_tags( $node) ) ) == 0) 
	            {
	               //$text = preg_replace("/(".preg_quote($node, '/')."(?!\[\/))/", '', $text);
	            }
	            else
	            {
	               $text = preg_replace("/(".preg_quote($node, '/')."(?!\[\/))/", '[av_textblock]$1[/av_textblock]', $text);
	            }
	        }
	        
			$text = $this->do_shortcode_backend($text);
			
			if(isset($_POST['text']))
			{
				echo $text;
				exit();
			}
			else
			{
				return $text;
			}
		}
		
		/**
		 * Ajax callback to return preview output in modal popup
		 * 
		 * @param string $text
		 */
		public function text_to_preview( $text = NULL )
		{
			if( ! current_user_can( 'edit_posts' ) ) 
			{
				die();
			}
			
			check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce' );
			
			$text = ''; 
			if( isset( $_POST['text'] ) ) 
			{
				$text = stripslashes( $_POST['text'] );
			}
	        
			$this->in_text_to_preview = true;
			
			$preview = do_shortcode( $text );
			
			/**
			 * Allow third party to modify content
			 * 
			 * @since 4.3.1
			 */
			$preview = apply_filters( 'avf_text_to_preview', $preview, $text );
			
			$this->in_text_to_preview = false;
			
			echo $preview;
			exit();
		}
		
		/**
		 * Returns if we are in ajax callback for preview
		 * 
		 * @since 4.5.4
		 * @return boolean
		 */
		public function in_text_to_preview_mode()
		{
			return $this->in_text_to_preview;
		}

		



		public function do_shortcode_backend($text)
		{
			return preg_replace_callback( "/".ShortcodeHelper::$pattern."/s", array($this, 'do_shortcode_tag'), $text );
		}

		
		public function do_shortcode_tag( $m ) 
		{
	        global $shortcode_tags;
			
	        // allow [[foo]] syntax for escaping a tag
	        if ( $m[1] == '[' && $m[6] == ']' ) {
	                return substr($m[0], 1, -1);
	        }
			
			//check for enclosing tag or self closing
			$values['closing'] 	= strpos($m[0], '[/'.$m[2].']');
			$values['content'] 	= $values['closing'] !== false ? $m[5] : NULL;
	        $values['tag']		= $m[2];
	        $values['attr']		= shortcode_parse_atts( stripslashes($m[3]) );
	        
	       
	        
	        
	        if(is_array($values['attr']))
	        {
		        $charset = get_bloginfo( 'charset' );
		        foreach($values['attr'] as &$attr)
		        {
		        	$attr =	htmlentities($attr, ENT_QUOTES, $charset);
		        }
			}
			else
			{
				 $values['attr'] = array();
			}
			
			 
			
	        if(isset($_POST['params']['extract']))
	        {
	        	//if we open a modal window also check for nested shortcodes
	        	if($values['content']) $values['content'] = $this->do_shortcode_backend($values['content']);
	        	
	        	$_POST['extracted_shortcode'][] = $values;
	        	
	        	return $m[0];
	        }
			
			if(in_array($values['tag'], ShortcodeHelper::$allowed_shortcodes))
			{
				return $this->shortcode_class[$this->shortcode[$values['tag']]]->prepare_editor_element( $values['content'], $values['attr'] );
			}
			else
			{
				return $m[0];
			}
		}
		
		
		
		/**
		 * this helper function tells the tiny_mce_editor to remove any span tags that dont have a classname (list insert on ajax tinymce tend do add them)
		 * see more: http://martinsikora.com/how-to-make-tinymce-to-output-clean-html
		 */
		 
		public function tiny_mce_helper($mceInit)
		{
			$mceInit['extended_valid_elements'] = empty($mceInit['extended_valid_elements']) ? "" : $mceInit['extended_valid_elements'] .","; 
			$mceInit['extended_valid_elements'] = "span[!class]";
			return $mceInit;
		}
		
		/**
		 * Return the postmeta metakey names.
		 * Can be filtered for specific posts.
		 * 
		 * @since 4.2.1
		 * @param int $post_id
		 * @param string $context			'save' | 'restore'
		 * @return array
		 */
		public function get_alb_meta_key_names( $post_id, $context )
		{
			$meta_keys = array(
							'_aviaLayoutBuilder_active',
							'_aviaLayoutBuilderCleanData', 
							'_avia_builder_shortcode_tree',
							'_alb_shortcode_status_content',
							'_alb_shortcode_status_clean_data',
							'_alb_shortcode_status_preview',
							'_avia_sc_parser_state',
							'_av_alb_posts_elements_state',
							'_av_el_mgr_version'
						);
			
			/**
			 * @used_by			enfold\includes\admin\register-portfolio.php				10
			 * 
			 * @since 4.2.1
			 */
			$meta_keys = apply_filters( 'avf_alb_meta_field_names', $meta_keys, $post_id, $context );
			
			if( ! is_array( $meta_keys) )
			{
				$meta_keys = array();
			}
			
			return $meta_keys;
		}

				
		/**
		 * Helper function that restores the post meta if user restores a revision
		 * see: https://lud.icro.us/post-meta-revisions-wordpress
		 */
		public function avia_builder_restore_revision( $post_id, $revision_id )
		{
			
			$meta_fields = $this->get_alb_meta_key_names( $revision_id, 'restore' );

			foreach( $meta_fields as $meta_field )
			{
				$builder_meta_data  = get_metadata( 'post', $revision_id, $meta_field, true );

				if ( ! empty( $builder_meta_data ) )
				{
					update_post_meta( $post_id, $meta_field, $builder_meta_data );
				}
				else
				{
					delete_post_meta( $post_id, $meta_field );
				}
			}
		}

			
		/**
		 * An autosave post is being updated
		 * 
		 * @since 4.2.5
		 * @added_by Günter
		 * @param array $post
		 */
		public function avia_builder_creating_autosave( array $post )
		{
			if( ! isset( $_REQUEST['aviaLayoutBuilder_active'] ) )
			{
				return;
			}
			
			$this->revision_id = $post['ID'];
			
			$this->do_alb_autosave( stripslashes( $post['post_content'] ) );
		}

		/**
		 * A revision or a new autosave is created
		 * 
		 * @since 4.2.1
		 * @added_by Günter
		 * @param int $revision_id
		 */
		public function avia_builder_put_revision( $revision_id )
		{
			if( ! isset( $_REQUEST['aviaLayoutBuilder_active'] ) )
			{
				return;
			}
			
			$this->revision_id = $revision_id;
			
			if( isset( $_POST['content'] ) )
			{
				$this->do_alb_autosave( $_POST['content'] );
			}
		}
		
		/**
		 * Create default revision entries for autosave or preview 
		 * 
		 * @since 4.2.5
		 * @added_by Günter
		 * @param string $content
		 */
		protected function do_alb_autosave( $content )
		{
			/**
			 * Copy all metadata from original post
			 */
			$this->save_alb_revision_data();
			
			
			/**
			 * Now we need to update the internal data to reflect new situation as we are in an autosave
			 * or preview (which saves the content to the only autosave post)
			 */
			$tree = ShortcodeHelper::build_shortcode_tree( $content );
			
			update_metadata( 'post', $this->revision_id, '_aviaLayoutBuilder_active', $_REQUEST['aviaLayoutBuilder_active'] );
			update_metadata( 'post', $this->revision_id, '_aviaLayoutBuilderCleanData', $content );
			update_metadata( 'post', $this->revision_id, '_avia_builder_shortcode_tree', $tree );
		}

		

		/**
		 * A revision had been saved and we have updated our internal data - 
		 * now save our meta data to restore when user reverts to a revision
		 * 
		 * @since 4.2.1
		 * @param int $post_id
		 */
		public function save_alb_revision_data( $post_id = 0 )
		{
			if( $this->revision_id <= 0 )
			{
				return;
			}
			
			if( $post_id <= 0 )
			{
				$post_id = get_the_ID();
			}
			
			if( false === $post_id )
			{
				return;
			}
			
			$meta_fields = $this->get_alb_meta_key_names( $post_id, 'save' );
			
			foreach( $meta_fields as $meta_field )
			{
				$builder_meta_data = get_post_meta( $post_id, $meta_field, true );
				
				if ( ! empty( $builder_meta_data ) )
				{
					update_metadata( 'post', $this->revision_id, $meta_field, $builder_meta_data );
				}
			}
		}
		
		/**
		 * Gets a post, executes shortcodes and returns the content
		 * This function is intended to be called from elements that render post content during output of a page
		 * It is important, that this function returns the complete HTML as needed on a blank page
		 * e.g. displaying a page as footer
		 * 
		 * The content of this function is based on enfold\template-builder.php
		 * 
		 * @since 4.2.3
		 * @param WP_Post $wp_post
		 * @return string
		 */
		public function compile_post_content( WP_Post $wp_post )
		{
			/**
			 * Save values to be able to restore in case we have recursive calls
			 */
			$old_tree = ShortcodeHelper::$tree;
			$old_shortcode_index = ShortcodeHelper::$shortcode_index;
			
			
			$out = '';
			
			$builder_stat = $this->get_alb_builder_status( $wp_post->ID );
			
			if( ( 'active' == $builder_stat ) && ! is_preview() )
			{
				/**
				 * Filter the content for content builder elements
				 */
				$content = apply_filters( 'avia_builder_precompile', $this->get_posts_alb_content( $wp_post->ID ) );
				ShortcodeHelper::$tree = $this->get_shortcode_tree( $wp_post->ID );
			}
			else
			{
				/**
				 * Non ALB page and
				 * also if user views a preview we must use the content because WordPress doesn't update the post meta field
				 */
				$content = apply_filters( 'avia_builder_precompile', $wp_post->post_content );

				
				/**
				 * Update the shortcode tree to reflect the current page structure.
				 * Prior make sure that shortcodes are balanced.
				 */
				$this->get_shortcode_parser()->set_builder_save_location( 'none' );
				$content = ShortcodeHelper::clean_up_shortcode( $content, 'balance_only' );
				ShortcodeHelper::$tree = ShortcodeHelper::build_shortcode_tree( $content );
			}
			
			ShortcodeHelper::$shortcode_index = 0;
			
			/**
			 * check first builder element. if its a section or a fullwidth slider we dont need to create the default opening divs here
			 */
			$first_el = isset(ShortcodeHelper::$tree[0]) ? ShortcodeHelper::$tree[0] : false;
			$last_el  = ! empty( ShortcodeHelper::$tree )   ? end( ShortcodeHelper::$tree ) : false;
			
			if( ! $first_el || ! in_array( $first_el['tag'], AviaBuilder::$full_el ) )
			{
			   $out .= avia_new_section( array( 'close' => false, 'main_container' => true, 'class' => 'main_color container_wrap_first' ) );
			}
	
			$content = apply_filters( 'the_content', $content );
			$content = apply_filters( 'avf_template_builder_content', $content );
			
			$out .= $content;
			
			//only close divs if the user didnt add fullwidth slider elements at the end. also skip sidebar if the last element is a slider
			if( ! $last_el || ! in_array( $last_el['tag'], AviaBuilder::$full_el_no_section ) )
			{
				$cm = avia_section_close_markup();

				$out .=		"</div>";
				$out .=	"</div>$cm <!-- section close by builder template -->";
			}
			
			// global fix for https://kriesi.at/support/topic/footer-disseapearing/#post-427764
			if( in_array( $last_el['tag'], AviaBuilder::$full_el_no_section ) )
			{
				avia_sc_section::$close_overlay = "";
			}

			$out .= avia_sc_section::$close_overlay;
			
			$out .= '		</div><!--end builder template-->';
			$out .= '</div><!-- close default .container_wrap element -->';

			ShortcodeHelper::$tree = $old_tree;
			ShortcodeHelper::$shortcode_index = $old_shortcode_index;
			
			return $out;
		}
	
	} // end class
	
	/**
	 * Returns the main instance of AviaBuilder to prevent the need to use globals
	 * 
	 * @since 4.2.1
	 * @return AviaBuilder
	 */
	function Avia_Builder() 
	{
		return AviaBuilder::instance();
	}

} // end if !class_exists
