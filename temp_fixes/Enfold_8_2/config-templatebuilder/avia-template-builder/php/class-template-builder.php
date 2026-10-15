<?php
/**
* Central Template builder class
*/
if( ! defined( 'ABSPATH' ) ) { exit; }		// Don't load directly

if( ! class_exists( 'AviaBuilder', false ) )
{
	class AviaBuilder extends aviaBuilder\base\object_properties
	{
		const VERSION = '6.0';

		/**
		 * Revision of the builder css and js files, appended to the version string of every
		 * asset the builder enqueues.
		 *
		 * The version otherwise only moves with the theme version, while the files are sent
		 * with a week of browser caching. A fix patched into an existing release therefore
		 * does not reach anyone who has opened the builder recently - their browser keeps
		 * serving the copy it already has, because the url did not change.
		 *
		 * Bump this whenever a builder asset is changed without a new theme version. It can
		 * go back to 1 when the theme version itself moves.
		 */
		const ASSET_REVISION = 8;

		/**
		 * Holds the instance of this class
		 *
		 * @since 4.2.1
		 * @var AviaBuilder
		 */
		static private $_instance = null;

		/**
		 * Holds parent theme version number to avoid calling WP base function
		 *
		 * @since 5.2
		 * @var string
		 */
		protected $theme_version;

		/**
		 * Flag if ALB is in use or only loaded as frame to support AviaBuilder()
		 *
		 * @since 7.0
		 * @var boolean
		 */
		protected $is_loaded;

		/**
		 * @since 5.6			changed to safe as default
		 * @var string			'safe' | 'debug'
		 */
		public static $mode = 'safe';

		/**
		 *
		 * @var array
		 */
		public static $path = array();

		/**
		 *
		 * @var array
		 */
		public static $resources_to_load = array();

		/**
		 *
		 * @var array
		 */
		public static $default_iconfont = array();

		/**
		 *
		 * @var array
		 */
		public static $full_el = array();

		/**
		 *
		 * @var array
		 */
		public static $full_el_no_section = array();

		/**
		 * Define all supported post types
		 *
		 * @since 4.3
		 * @var array|null
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
		 * State of the select box in admin area for the post/page/...
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
		 * Contains usage count for each shortcode in blog
		 *		shortcode  =>  count
		 *
		 * @var array
		 * @since 4.6.4
		 */
		protected $shortcode_usage_blog;

		/**
		 *
		 * @var AviaSaveBuilderTemplate
		 */
		protected $builderTemplate;

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
		 * Stores preview post ID
		 *
		 * @since 6.0
		 * @var int
		 */
		protected $text_to_preview_post_id;

		/**
		 * Stores preview post ID
		 *
		 * @since 6.0
		 * @var int
		 */
		protected $text_to_interface_post_id;

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
		 * Contains registered admin scripts for shortcodes to be enqueued after main admin scripts
		 *
		 * @since 4.5.7.1
		 * @var array
		 */
		protected $registered_admin_scripts;

		/**
		 * Contains registered admin CSS for shortcodes to be enqueued after main admin CSS
		 *
		 * @since 4.5.7.1
		 * @var array
		 */
		protected $registered_admin_styles;

		/**
		 * Array of characters that might break backend or frontend when used in modal options
		 * @since 4.7.6.4
		 * @var array
		 */
		protected $critical_modal_charecters;

		/**
		 * Customizable string for default tab name for shortcode buttons
		 *
		 * @since 4.8
		 * @var string
		 */
		public $default_sc_btn_tab_name;

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
			$this->theme_version = avia_get_theme_version();
			$this->is_loaded = false;
			$this->paths = array();
			$this->shortcode_class = array();
			$this->shortcode = array();
			$this->shortcode_usage_blog = array();
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
			$this->supported_post_types = null;
			$this->supported_post_status = array( 'publish', 'private', 'future', 'draft', 'pending' );
			$this->may_be_disabled_automatically = array();
			$this->disabled_assets = array();
			$this->in_text_to_preview = false;
			$this->text_to_preview_post_id = 0;
			$this->text_to_interface_post_id = 0;
			$this->wp_head_done = false;
			$this->wp_sidebar_started = false;
			$this->wp_footer_started = false;
			$this->registered_admin_scripts = array();
			$this->registered_admin_styles = array();

			/**
			 * Characters that break backend or frontend if used in shortcode options of ALB modal popup
			 * We offer plugin https://github.com/KriesiMedia/enfold-library/tree/master/integration%20plugins/Enfold/Special%20Character%20Translation as a workaround
			 *
			 * @since 4.7.6.4
			 * @param array
			 * @return array				empty array to supress warnings
			 */
			$this->critical_modal_charecters = apply_filters( 'avf_critical_modal_charecters', array( '<', '>', '[', ']' ) );

			/**
			 * Filter shortcode tab button name used if no tab defined in shortcode button or to be used as default
			 *
			 * @since 4.8
			 * @param string
			 * @return string
			 */
			$this->default_sc_btn_tab_name = apply_filters( 'avf_default_sc_btn_tab_name', __( 'Miscellaneous', 'avia_framework' ) );

			$this->paths['pluginPath'] = trailingslashit( dirname( dirname( __FILE__ ) ) );
			$this->paths['pluginDir'] = trailingslashit( basename( $this->paths['pluginPath'] ) );

			/**
			 * Override when plugin is integrated in theme
			 *
			 * @since ???
			 * @used_by enfold\config-templatebuilder\config.php		1
			 * @param string $base_url
			 * @retrurn string
			 */
			$this->paths['pluginUrlRoot'] = apply_filters( 'avia_builder_plugins_url',  plugins_url() . '/' . $this->paths['pluginDir'] );

			$this->paths['pluginUrl'] = $this->paths['pluginUrlRoot'] . 'avia-template-builder/';
			$this->paths['assetsURL'] = trailingslashit( $this->paths['pluginUrl'] ) . 'assets/';
			$this->paths['assetsPath'] = trailingslashit( $this->paths['pluginPath'] ) . 'assets/';
			$this->paths['imagesURL'] = trailingslashit( $this->paths['pluginUrl'] ) . 'images/';

			/**
			 * Element icons live apart from the bitmaps in images/ because they are read from
			 * disk and inlined into the page, not linked. Both the URL and the path are kept:
			 * the URL is what an element still stores in config['icon'], so every icon stays
			 * openable in a browser, and the path is what the loader reads.
			 *
			 * @since 8.0
			 */
			$this->paths['iconsURL'] = trailingslashit( $this->paths['pluginUrl'] ) . 'icons/';
			$this->paths['iconsPath'] = trailingslashit( $this->paths['pluginPath'] ) . 'icons/';
			$this->paths['configPath'] = apply_filters( 'avia_builder_config_path', $this->paths['pluginPath'] . 'config/' );
			AviaBuilder::$path = $this->paths;

			/**
			 *
			 * @since ???
			 * @since 7.0						added 'svg_entypo-fontello'
			 * @param array $font_list
			 * @return array
			 */
			AviaBuilder::$default_iconfont = apply_filters( 'avf_default_iconfont', array(

																	'svg_entypo-fontello' =>
																			array(
																				'append'	=> '',
																				'include' 	=> $this->paths['assetsPath'] . 'fonts/svg_entypo-fontello',
																				'folder'  	=> $this->paths['assetsURL'] . 'fonts/svg_entypo-fontello',
																				'config'	=> 'charmap.php',
																				'json'		=> 'config.json',
																				'full_path'	=> 'true' //tells the script to not prepend the wp_upload dir path to these urls
																			),

																	'entypo-fontello-enfold' =>
																			array(
																				'append'	=> '',
																				'include' 	=> $this->paths['assetsPath'] . 'fonts/entypo-fontello-enfold',
																				'folder'  	=> $this->paths['assetsURL'] . 'fonts/entypo-fontello-enfold',
																				'config'	=> 'charmap.php',
																				'json'		=> 'config.json',
																				'svg_files'	=> 'charmap-svg.php',
																				'full_path'	=> 'true'	//tells the script to not prepend the wp_upload dir path to these urls
																			),

																	'entypo-fontello' =>
																			array(
																				'append'	=> '',
																				'include' 	=> $this->paths['assetsPath'] . 'fonts/entypo-fontello',
																				'folder'  	=> $this->paths['assetsURL'] . 'fonts/entypo-fontello',
																				'config'	=> 'charmap.php',
																				'json'		=> 'config.json',
																				'svg_files'	=> 'charmap-svg.php',
//																				'compat'	=> 'charmap-compat.php', //needed to make the theme compatible with the old version of the font
																				'full_path'	=> 'true',	//tells the script to not prepend the wp_upload dir path to these urls
																				'is_activ'	=> 'yes'	//allows to deactivate font, disables loading - 'yes' | 'no'
																			)

																) );

			//	PHP 8.0 throws TypeError if null returned
			if( ! is_array( AviaBuilder::$default_iconfont ) )
			{
				AviaBuilder::$default_iconfont = array();
			}



			/**
			 * This adds lazy load feature to ALB
			 *
			 * Does not work as long as class avia_font_manager is part of ALB. When moved to framework it is possible to use it
			 * =================================================================================================================
			 *
			 * @since 7.0   DO NOT USE - NOT FULLY TESTED
			 */
			if( ! current_theme_supports( 'alb_lazy_load' ) || true )
			{
				add_action( 'load-post.php', array( $this, 'handler_load_post_screen' ) , 5 );
				add_action( 'load-post-new.php', array( $this, 'handler_load_post_screen' ) , 5 );

				add_action( 'init', array( $this, 'handler_wp_init' ), 5 );
				add_action( 'admin_init', array( $this, 'handler_wp_admin_init' ), 1 );
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_asset_check' ), 20 );

				add_action( 'wp_head', array( $this, 'handler_wp_head' ), 99999999 );
				add_action( 'get_sidebar', array( $this, 'handler_get_sidebar' ), 1, 1 );
				add_action( 'get_footer', array( $this, 'handler_get_footer' ), 1, 1 );


				//save and restore meta information if user restores a revision
				add_action( 'wp_creating_autosave', array( $this, 'avia_builder_creating_autosave' ), 10, 1 );
				add_action( '_wp_put_post_revision', array( $this, 'avia_builder_put_revision' ), 10, 1 );
				add_action( 'wp_restore_post_revision', array( $this, 'avia_builder_restore_revision' ), 10, 2 );

				add_filter( 'avia_builder_metabox_filter', array( $this, 'handler_alb_metabox_filter' ), 10, 1 );

				add_action('dbx_post_sidebar', array( $this, 'handler_wp_dbx_post_sidebar' ), 10, 1 );
			}
			else
			{
				error_log( '***************  ALB is loaded only base class - additional ALB code and shortcodes in admin_init or wp hook !!!! **************************** ' );

				add_action( 'init', array( $this, 'handler_wp_init_lazy_load' ), 5 );
				add_action( 'admin_init', array( $this, 'handler_wp_admin_init_lazy_load' ), 1 );
				add_action( 'wp_enqueue_scripts', array( $this, 'handler_wp_lazy_load' ), 20 );
			}
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
			unset( $this->shortcode_usage_blog );
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
			unset( $this->registered_admin_scripts );
			unset( $this->registered_admin_styles );
			unset( $this->critical_modal_charecters );
		}

		/**
		 * In admin area we need to load core files of ALB because dashboard menus is hooked to add
		 * additional menus (e.g. Custom Elements, Custom Layouts)
		 *
		 * @since 7.0
		 */
		public function handler_wp_init_lazy_load()
		{
			error_log( 'handler_wp_init_lazy_load' );

			if( ! is_admin() )
			{
				return;
			}

			$this->load_core_files();
		}

		/**
		 * Handler processed in backend and with ajax call
		 * $post is NOT set at this point
		 *
		 * We have to load all ALB stuff in backend as long as ALB is not disabled at all.
		 * In this case we have to load it for options page
		 *
		 * @since 7.0
		 */
		public function handler_wp_admin_init_lazy_load()
		{
			error_log( 'handler_wp_admin_init_lazy_load' );

			$this->load_shortcode_library();
			$this->handler_wp_admin_init();
			$this->init();

			$this->is_loaded = true;

			add_action( 'load-post.php', array( $this, 'admin_init' ) , 5 );
			add_action( 'load-post-new.php', array( $this, 'admin_init' ) , 5 );

			//save and restore meta information if user restores a revision
			add_action( 'wp_creating_autosave', array( $this, 'avia_builder_creating_autosave' ), 10, 1 );
			add_action( '_wp_put_post_revision', array( $this, 'avia_builder_put_revision' ), 10, 1 );
			add_action( 'wp_restore_post_revision', array( $this, 'avia_builder_restore_revision' ), 10, 2 );

			add_filter( 'avia_builder_metabox_filter', array( $this, 'handler_alb_metabox_filter' ), 10, 1 );

			add_action( 'dbx_post_sidebar', array( $this, 'handler_wp_dbx_post_sidebar' ), 10, 1 );
		}

		/**
		 * Called in frontend and with REST API
		 * Perform a "late loading" of template builder functions and hooks
		 *
		 * @since 7.0
		 */
		public function handler_wp_lazy_load()
		{
			global $post;

			error_log( 'handler_wp_lazy_load' );

			if( is_admin() )
			{
				error_log( 'handler_wp_lazy_load - is_admin() -> skipped' );
				return;
			}

			$is_rest = ( defined( 'REST_REQUEST' ) && true === REST_REQUEST );
			$is_alb = $this->get_alb_builder_status( $post->ID ) == 'active';

			/**
			 * This is for a first implementation - shortcodes might exist on all pages
			 * We need to load ALB unconditionally at this stage of implementation (therefore "true")
			 *
			 * @since 7.0
			 */
			if( $is_alb || $is_rest || true )
			{
				$this->load_core_files();
				$this->load_shortcode_library();
				$this->init();
				$this->frontend_asset_check();
				$this->is_loaded = true;
			}
			else
			{
				//	loads icon font on non ALB pages
				add_action( 'wp_head', array( $this, 'load_shortcode_assets' ), 2000 );

				$this->is_loaded = false;
			}

			add_action( 'wp_head', array( $this, 'handler_wp_head' ), 99999999 );
			add_action( 'get_sidebar', array( $this, 'handler_get_sidebar' ), 1, 1 );
			add_action( 'get_footer', array( $this, 'handler_get_footer' ), 1, 1 );
		}

		/**
		 * Handles "old" way - loads complete ALB
		 *
		 * @since 7.0
		 */
		public function handler_wp_init()
		{
			$this->load_core_files();
			$this->load_shortcode_library();
			$this->init();

			$this->is_loaded = true;
		}

		/**
		 * @since 4.6.4
		 */
		public function handler_wp_admin_init()
		{
			if( isset( $_REQUEST['avia_export'] ) && isset( $_REQUEST['avia_generate_alb_templates_file'] ) )
			{
				$this->get_AviaSaveBuilderTemplate();
			}
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

			/**
			 *	WP5.2 reports a problem: Class ‘ShortcodeHelper’ not found
			 */
			if( class_exists( 'ShortcodeHelper', false ) )
			{
				ShortcodeHelper::$shortcode_index = 0;
			}
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
		public function handler_wp_dbx_post_sidebar( $post )
		{
			if( ! $this->alb_magic_wand_button )
			{
				return;
			}

			if( ! $this->alb_nonce_added )
			{
				$nonce = wp_create_nonce ('avia_nonce_loader');

				echo '<input type="hidden" name="avia-loader-nonce" id="avia-loader-nonce" value="' . $nonce . '" />';

				$this->alb_nonce_added = true;
			}
		}

		/**
		 * Returns if ALB is fully loaded including shortcodes
		 *
		 * @since 7.0
		 * @return boolean
		 */
		public function is_alb_loaded()
		{
			return $this->is_loaded;
		}

		/**
		 * Load all functions that are needed for both front and backend
		 */
		protected function init()
	 	{
			if( isset( $_GET['avia_mode'] ) )
			{
				AviaBuilder::$mode = esc_attr( $_GET['avia_mode'] );
			}

			//activate the element manager
			$this->element_manager();

			$this->createShortcode();

			$this->addActions();
			AviaStoragePost::generate_post_type();

			//hook into the media uploader. we always need to call this for several hooks to be active
			new AviaMedia();

			//on ajax call load the functions that are usually only loaded on new post and edit post screen
			if( AviaHelper::is_ajax() )
			{
				if( empty( $_POST['avia_request'] ) )
				{
					return;
				}

				$this->admin_init();
			}

			//activate asset manager
			$this->asset_manager();
	 	}

		/**
		 * Handler for load-post.php / load-post-new.php - those fire for every post type using the
		 * standard edit screen (e.g. WooCommerce products, or any unrelated CPT), not just ones ALB
		 * supports. Without this check the builder's admin UI (including the resizable element panel)
		 * loaded unconditionally on any edit screen.
		 *
		 * Post types that are not ALB supported but get one of our metaboxes (e.g. "Layout" for CPT with
		 * 'avia_layout_settings' support) also need it - MetaBoxBuilder is created in admin_init().
		 *
		 * @since 8.2
		 * @since 8.2.1				post type on save (POST) and metabox post types
		 */
		public function handler_load_post_screen()
		{
			$post_type = $this->get_post_screen_post_type();

			if( ! in_array( $post_type, $this->get_supported_post_types(), true ) )
			{
				if( ! in_array( $post_type, $this->get_metabox_post_types(), true ) )
				{
					return;
				}
			}

			$this->admin_init();
		}

		/**
		 * Returns the post type of the post.php / post-new.php request.
		 * load-post.php fires before post.php reads the post ID - on save (POST) the ID is only in $_POST['post_ID'].
		 * Same order as wp-admin/post.php.
		 *
		 * @since 8.2.1
		 * @return string
		 */
		protected function get_post_screen_post_type()
		{
			$post_id = 0;

			if( isset( $_GET['post'] ) )
			{
				$post_id = (int) $_GET['post'];
			}
			else if( isset( $_POST['post_ID'] ) )
			{
				$post_id = (int) $_POST['post_ID'];
			}

			if( $post_id > 0 )
			{
				$post_type = get_post_type( $post_id );

				if( false !== $post_type )
				{
					return $post_type;
				}
			}

			return isset( $_REQUEST['post_type'] ) ? sanitize_key( $_REQUEST['post_type'] ) : 'post';
		}

		/**
		 * Returns all post types that get at least one of our metaboxes.
		 * Uses the same config and filter as MetaBoxBuilder::get_params().
		 *
		 * @since 8.2.1
		 * @return array
		 */
		protected function get_metabox_post_types()
		{
			require( $this->paths['configPath'] . 'meta.php' );

			if( empty( $boxes ) || ! is_array( $boxes ) )
			{
				return [];
			}

			/**
			 * see MetaBoxBuilder::get_params()
			 */
			$boxes = apply_filters( 'avia_builder_metabox_filter', $boxes );

			$post_types = [];

			foreach( $boxes as $box )
			{
				if( isset( $box['page'] ) && is_array( $box['page'] ) )
				{
					$post_types = array_merge( $post_types, $box['page'] );
				}
			}

			return array_unique( $post_types );
		}

		/**
		 * Handler - load functions that are only needed on add/edit post screen
		 */
		public function admin_init()
	 	{
			$this->addAdminFilters();
			$this->addAdminActions();
			$this->loadTextDomain();
			$this->call_classes();
			$this->apply_editor_wrap();
	 	}

		/**
		 * Load all the required core files
		 *
		 * @since ???
		 */
		protected function load_core_files()
		{
			$sources = array(
							'traits/trait-modal-iconfont-helper.php',
							'traits/trait-sc-named-colors.php',
							'traits/trait-sc-button-styles.php',
							'traits/trait-sc-slideshow-ui-controls.php',
							'traits/trait-sc-text-reveal.php',

							'base-classes/class-css-media-queries.php',
							'base-classes/class-element-styling-base.php',
							'base-classes/class-element-styling-rules.php',
							'base-classes/class-element-styling-responsive.php',
							'base-classes/class-sub-item-query-base.php',
							'base-classes/class-popup-templates-base.php',
							'base-classes/class-popup-templates-callback.php',
							'base-classes/class-popup-templates-responsive.php',
							'base-classes/class-popup-templates-plugins.php',

							'base-classes/class-modal-base.php',
							'base-classes/class-modal-elements.php',

							'class-pointer.php',
							'class-shortcode-helper.php',
							'class-shortcode-parser.php',
							'class-element-manager.php',
							'class-generic-helper.php',
							'class-html-helper.php',
							'class-svg-shapes.php',
							'class-element-icons.php',
							'class-builder-notes.php',
							'class-front-templates.php',
							'class-meta-box.php',
							'class-default-comparator.php',
							'class-default-marker.php',
							'class-section-normaliser.php',
							'class-fresh-shortcode.php',
							'class-collection-defaults.php',
							'class-shortcode-template.php',
							'class-media.php',
							'class-tiny-button.php',
							'class-save-buildertemplate.php',
							'class-storage-post.php',
							'class-font-manager.php',
							'class-asset-manager.php',
							'class-element-styling.php',
							'class-post-css-management.php',
							'class-popup-templates.php',
							'class-element-templates.php',
							'class-custom-layout.php',
							'class-dynamic-content.php'
					);

			$path = $this->paths['pluginPath'] . 'php/';
			$files = array();

			foreach( $sources as $key => $source )
			{
				$files[ $key ] = $path . $source;
			}

			/**
			 * Allow to filter core files loading
			 *
			 * @since 4.8.4
			 * @param array $files
			 * @param array $sources
			 * @return array
			 */
			$core_files = apply_filters( 'avf_before_load_builder_core_files', $files, $sources );

			foreach( $core_files as $file )
			{
				/**
				 * Allows to load a custom file that overrides base core file immediatly before original
				 * file is loaded and suppress loading of php file
				 *
				 * Make sure to use same namespace, classname and extending base !!!
				 * =================================================================
				 *
				 * @since 4.8.9
				 * @param boolean $loaded
				 * @param string $file
				 * @return boolean
				 */
				if( false !== apply_filters( 'avf_load_single_builder_core_file', false, $file ) )
				{
					continue;
				}

				require_once( $file );
			}

			//activate iconfont manager
			new avia_font_manager();
			avia_font_manager::add_extra_iconfonts( AviaBuilder::$default_iconfont );

			global $avia_config;

			if( isset( $avia_config['font_icons'] ) && is_array( $avia_config['font_icons'] ) )
			{
				avia_font_manager::add_icon_shortcuts( $avia_config['font_icons'] );
			}


			/**
			 * @since 4.8.9
			 */
			do_action( 'ava_builder_core_files_loaded' );
		}

		/**
		 * This auoloads all the shortcodes located in php/shortcodes and any other folders that were added by filter
		 *
		 * @since ????
		 */
		protected function load_shortcode_library()
		{
			/**
			 * autoload files in shortcodes folder and any other folders that were added by filter
			 *
			 * @param array $paths
			 * @return array
			 */
			$sc_paths = apply_filters( 'avia_load_shortcodes', array( $this->paths['pluginPath'] . 'php/shortcodes/' ) );


			foreach( $sc_paths as $path )
			{
				//include modules (eg files within folders with the same name)
				foreach( glob( $path . '*', GLOB_ONLYDIR ) as $folder )
				{
					$php_file = trailingslashit( $folder ) . basename( $folder ) . '.php';

					if( file_exists( $php_file ) )
					{
						/**
						 * Allows to load a custom file that overrides base core file immediatly before original
						 * file is loaded and suppress loading of php file
						 *
						 * Make sure to use same namespace, classname and extending base !!!
						 * =================================================================
						 *
						 * @since 4.8.9
						 * @param boolean $loaded
						 * @param string $php_file
						 * @return boolean
						 */
						if( false !== apply_filters( 'avf_load_single_shortcode_file', false, $php_file ) )
						{
							continue;
						}

						require_once( $php_file );
					}
				}

				//include single files
				foreach( glob( $path . '*.php' ) as $php_file )
				{
					/**
					 * Allows to load a custom file that overrides base core file immediatly before original
					 * file is loaded and suppress loading of php file
					 *
					 * Make sure to use same namespace, classname and extending base !!!
					 * =================================================================
					 *
					 * @since 4.8.9
					 * @param boolean $loaded
					 * @param string $php_file
					 * @return boolean
					 */
					if( false !== apply_filters( 'avf_load_single_shortcode_file', false, $php_file ) )
					{
						continue;
					}

					require_once( $php_file );
				}
			}

			/**
			 * @since 4.8.9
			 */
			do_action( 'ava_builder_shortcode_files_loaded' );
		}

		/**
		 * Add filters to various wordpress filter hooks
		 */
		protected function addAdminFilters()
		{
			/**
			 * lock drag and drop?
			 *
			 * @used_by			functions-enfold.php	avia_disable_alb_drag_drop()		30
			 * @param boolean
			 * @return boolean
			 */
			$this->disable_drag_drop = apply_filters( 'avf_allow_drag_drop', false );

			// add_filter('tiny_mce_before_init', array($this, 'tiny_mce_helper')); // remove span tags from tinymce - currently disabled, doesnt seem to be necessary
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		}

		/**
		 * Add Admin Actions to some wordpress action hooks
		 */
		protected function addAdminActions()
		{
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'handler_admin_dequeue_scripts' ), 99999 );
			add_action( 'admin_print_scripts', array( $this,'load_shortcode_assets' ), 2000 );
			add_action( 'print_media_templates', array( $this, 'js_template_editor_elements' ) );	//create js templates for AviaBuilder Canvas Elements
			add_action( 'avia_save_post_meta_box', array( $this, 'meta_box_save' ) );				//hook into meta box saving and store the status of the template builder and the shortcodes that are used

			add_filter( 'avf_before_save_alb_post_data', array( $this, 'handler_before_save_alb_post_data' ), 10, 2 );	//	hook to balance shortcode for non ALB pages

			//custom ajax actions
			add_action( 'wp_ajax_avia_ajax_text_to_interface', array( $this, 'text_to_interface' ) );
			add_action( 'wp_ajax_avia_ajax_modal_group_to_interface', array( $this, 'modal_group_text_to_interface' ) );
			add_action( 'wp_ajax_avia_ajax_text_to_preview', array( $this, 'text_to_preview' ) );
			add_action( 'wp_ajax_avia_ajax_text_to_preview_svg_dividers', array( $this, 'text_to_preview_svg_dividers' ) );
			add_action( 'wp_ajax_avia_alb_shortcode_buttons_order', array( $this, 'handler_alb_shortcode_buttons_order' ), 10 );

			/**
			 * Registers wp_ajax_avia_dismiss_builder_note.
			 *
			 * The class is a lazy singleton, and on an ajax request nothing renders a note -
			 * so without this call its constructor never runs, the action is never added and
			 * closing a note silently fails to save.
			 */
			Avia_Builder_Notes();
		}


		/**
		 * Add Actions for the frontend
		 */
		protected function addActions()
		{

			// Enable shortcodes in widget areas
			add_filter( 'widget_text', 'do_shortcode' );

			//default wordpress hooking
			add_action( 'wp_head', array( $this, 'load_shortcode_assets' ), 2000 );
			add_filter( 'template_include' , array( $this, 'template_include' ), 20000 );
		}

		/**
		 * Automatically load assests like fonts into your frontend
		 */
		public function load_shortcode_assets()
		{
			$output = '';
			$output .= avia_font_manager::load_font();

			/* if the builder is decoupled from the theme then make sure to only load iconfonts if they are actually necessary. in enfolds case it is

			foreach( AviaBuilder::$resources_to_load as $element )
			{
				if( $element['type'] == 'iconfont' )
				{
					$output .= avia_font_manager::load_font();
				}
			}
			*/

			echo $output;

			//output preview css paths
			if( is_admin() )
			{
				echo $this->load_preview_css( $output );
			}
		}

		/**
		 * Adds registered shortcode admin script handles to array. Allows to enqueue them on edit pages only.
		 * Avoids problems with e.g. WooCommerce that manipulates the enqueue array to preload scripts.
		 *
		 * @since 4.7.5.2
		 * @param string $handle
		 */
		public function add_registered_admin_script( $handle )
		{
			if( ! in_array( $handle, $this->registered_admin_scripts ) )
			{
				$this->registered_admin_scripts[] = $handle;
			}
		}

		/**
		 * Adds registered shortcode admin script handles to array. Allows to enqueue them on edit pages only.
		 * Avoids problems with e.g. WooCommerce that manipulates the enqueue array to preload scripts.
		 *
		 * @since 4.7.5.2
		 * @param string $handle
		 */
		public function add_registered_admin_style( $handle )
		{
			if( ! in_array( $handle, $this->registered_admin_styles ) )
			{
				$this->registered_admin_styles[] = $handle;
			}
		}

		/**
		 * load css and js files when in editable mode
		 */
		public function admin_scripts_styles()
		{
			global $wp_version;

			$ver = $wp_version . '-' . Avia_Builder()->get_theme_version() . '.' . AviaBuilder::ASSET_REVISION;
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );

			#js
			wp_enqueue_script( 'avia_builder_js', $this->paths['assetsURL'] . "js/avia-builder{$min_js}.js", array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-droppable', 'jquery-ui-datepicker', 'wp-color-picker', 'media-editor', 'post' ), $ver, true );
			wp_enqueue_script( 'avia_element_js', $this->paths['assetsURL'] . "js/avia-element-behavior{$min_js}.js" , array( 'avia_builder_js' ), $ver, true );
			wp_enqueue_script( 'avia_modal_js', $this->paths['assetsURL'] . "js/avia-modal{$min_js}.js" , array( 'jquery', 'avia_element_js', 'wp-color-picker' ), $ver, true );
			wp_enqueue_script( 'avia_modal_defaults_js', $this->paths['assetsURL'] . "js/avia-modal-defaults{$min_js}.js" , array( 'jquery', 'avia_modal_js' ), $ver, true );
			wp_enqueue_script( 'avia_modal_collections_js', $this->paths['assetsURL'] . "js/avia-modal-collections{$min_js}.js" , array( 'jquery', 'avia_modal_js', 'avia_modal_defaults_js' ), $ver, true );
			wp_enqueue_script( 'avia_modal_appliers_js', $this->paths['assetsURL'] . "js/avia-modal-appliers{$min_js}.js" , array( 'jquery', 'avia_modal_js' ), $ver, true );
			wp_enqueue_script( 'avia_modal_collection_reset_js', $this->paths['assetsURL'] . "js/avia-modal-collection-reset{$min_js}.js" , array( 'jquery', 'avia_modal_appliers_js' ), $ver, true );
			wp_enqueue_script( 'avia_modal_reset_js', $this->paths['assetsURL'] . "js/avia-modal-reset{$min_js}.js" , array( 'jquery', 'avia_modal_js', 'avia_modal_defaults_js', 'avia_modal_collections_js', 'avia_modal_appliers_js', 'avia_modal_collection_reset_js' ), $ver, true );
			wp_enqueue_script( 'avia_custom_elements_js', $this->paths['assetsURL'] . "js/avia-custom-elements{$min_js}.js" , array( 'jquery', 'avia_builder_js', 'avia_modal_js' ), $ver, true );
			wp_enqueue_script( 'avia_modal_dynamic_js', $this->paths['assetsURL'] . "js/avia-modal-dynamic{$min_js}.js" , array( 'jquery', 'avia_builder_js', 'avia_modal_js' ), $ver, true );

			wp_enqueue_script( 'avia_history_js', $this->paths['assetsURL'] . "js/avia-history{$min_js}.js" , array( 'avia_element_js' ), $ver, true );
			wp_enqueue_script( 'avia_tooltip_js', $this->paths['assetsURL'] . "js/avia-tooltip{$min_js}.js" , array( 'avia_element_js' ), $ver, true );
			wp_enqueue_script( 'avia_panel_resize_js', $this->paths['assetsURL'] . "js/avia-panel-resize{$min_js}.js" , array( 'avia_element_js' ), $ver, true );
			wp_enqueue_script( 'avia_panel_search_js', $this->paths['assetsURL'] . "js/avia-panel-search{$min_js}.js" , array( 'avia_element_js' ), $ver, true );
			wp_enqueue_script( 'avia_panel_fullscreen_js', $this->paths['assetsURL'] . "js/avia-panel-fullscreen{$min_js}.js" , array( 'avia_element_js' ), $ver, true );

			wp_localize_script( 'avia_panel_resize_js', 'AviaBuilderPanelL10n', array(
						'resize_panel'			=> __( 'Resize element panel', 'avia_framework' ),
						'search_placeholder'	=> __( 'Search elements', 'avia_framework' ),
						'search_label'			=> __( 'Search elements', 'avia_framework' ),
						'no_results'			=> __( 'No elements match your search', 'avia_framework' ),
						'enter_fullscreen'		=> __( 'Fullscreen', 'avia_framework' ),
						'exit_fullscreen'		=> __( 'Exit fullscreen', 'avia_framework' )
					) );

			foreach( $this->registered_admin_scripts as $script )
			{
				wp_enqueue_script( $script );
			}

			#css
			wp_enqueue_style( 'avia-modal-style' , $this->paths['assetsURL'] . "css/avia-modal{$min_css}.css", false, $ver );
			wp_enqueue_style( 'avia-builder-style' , $this->paths['assetsURL'] . "css/avia-builder{$min_css}.css", false, $ver );
			wp_enqueue_style( 'avia-custom-elements-style' , $this->paths['assetsURL'] . "css/avia-custom-elements{$min_css}.css", false, $ver );
			wp_enqueue_style( 'wp-color-picker' );

			$this->add_share_defaults_style();

			/**
			 * @since 4.2.3 we support columns in rtl order (before they were ltr only). To be backward comp. with old sites use this filter.
			 */
			if( is_rtl() && ( 'yes' == apply_filters( 'avf_rtl_column_support', 'yes' ) ) )
			{
				wp_enqueue_style( 'avia-builder-rtl-style' , $this->paths['assetsURL'] . "css/avia-builder-rtl{$min_css}.css", false, $ver );
			}

			foreach( $this->registered_admin_styles as $style )
			{
				wp_enqueue_style( $style );
			}

			/**
			 * Load shortcode.css to avoid duplicating CSS for svg dividers
			 *
			 * @since 4.8.4
			 */
			$template_url = get_template_directory_uri();
			wp_enqueue_style( 'avia-scs', $template_url . '/css/shortcodes.css', array( 'avia-builder-style' ), $ver );


			#localize strings for javascript
			include_once( $this->paths['configPath'] . 'javascript_strings.php' );

			if( ! empty( $strings ) )
			{
				foreach( $strings as $key => $string )
				{
					wp_localize_script( $key, str_replace( '_js', '_L10n', $key ), $string );
				}
			}

		}

		/**
		 * @since 4.6.7.4-beta-2
		 */
		public function handler_admin_dequeue_scripts()
		{
			$screen = get_current_screen();

			if( ! $screen instanceof WP_Screen )
			{
				return;
			}

			if( $screen->base == 'post' )
			{
				/**
				 * @since WP 5.5 we need to dequeue this script as it blocks space bar
				 *
				 * Bug reported with RevSlider - but could be found with every other plugin which enqueues \wp-admin\js\updates.min.js
				 * https://github.com/KriesiMedia/wp-themes/issues/2949#issuecomment-681851213
				 */
				wp_dequeue_script( 'updates' );
			}
		}

		/**
		 *
		 * @param string $icon_font
		 * @param string $css
		 * @return string
		 */
		public function load_preview_css( $icon_font = '', $css = '' )
		{
			$output = '';
			$template_url = get_template_directory_uri();
			$child_theme_url = get_stylesheet_directory_uri();
			$avia_dyn_stylesheet_url = false;
			$builder_assets_url = trailingslashit( $this->paths['pluginUrlRoot'] ) . 'avia-template-builder/assets';
			$ver = Avia_Builder()->get_theme_version() . '.' . AviaBuilder::ASSET_REVISION;
			$min_js = avia_minify_extension( 'js' );
			$min_css = avia_minify_extension( 'css' );


			global $avia, $avia_config;

			$safe_name = avia_backend_safe_string( $avia->base_data['prefix'] );
			$safe_name = apply_filters( 'avf_dynamic_stylesheet_filename', $safe_name );

	        if( get_option( 'avia_stylesheet_exists' . $safe_name ) == 'true' )
	        {
	            $avia_upload_dir = wp_upload_dir();

				/**
				 * Change the default dynamic upload url
				 *
				 * @since 4.4
				 * @param string $path
				 * @return string
				 */
				$avia_dyn_upload_path = apply_filters( 'avf_dyn_stylesheet_dir_url',  $avia_upload_dir['baseurl'] . $avia_config['dynamic_files_upload_folder'] );
				$avia_dyn_upload_path = trailingslashit( $avia_dyn_upload_path );

	            if( is_ssl() )
				{
					$avia_dyn_upload_path = str_replace( 'http://', 'https://', $avia_dyn_upload_path );
				}

				/**
				 * Change the default dynamic stylesheet name
				 *
				 * @since 4.4
				 */
	            $avia_dyn_stylesheet_url = apply_filters( 'avf_dyn_stylesheet_file_url', $avia_dyn_upload_path . $safe_name . '.css' );
	        }

	        $google_fonts = array(
								avia_get_option( 'google_webfont' ),
								avia_get_option( 'default_font' )
							);

	        foreach( $google_fonts as $font )
	        {
	        	$font_weight = '';

				if( strpos( $font, ':' ) !== false )
				{
					$data = explode( ':', $font );
					$font = $data[0];
					$font_weight = $data[1];
				}

		        if( strpos( $font, 'websave' ) === false )
				{
					$avia->style->add_google_font( $font, $font_weight );
				}
	        }

			//if no user defined css is available load all the default frontend css
			if( empty( $css ) )
			{
				$css = array(
							includes_url( '/js/mediaelement/mediaelementplayer-legacy.min.css' )	=> 1,
							includes_url( '/js/mediaelement/wp-mediaelement.css?ver=4.9.4' )		=> 1,

							"{$template_url}/css/grid{$min_css}.css"						=> 1,
							"{$template_url}/css/base{$min_css}.css"						=> 1,
							"{$template_url}/css/layout{$min_css}.css"						=> 1,
							"{$template_url}/css/shortcodes{$min_css}.css"					=> 1,
							"{$template_url}/css/avia-snippet-fold-unfold{$min_css}.css"	=> 1,
							"{$template_url}/js/aviapopup/magnific-popup{$min_css}.css"		=> 1,
							"{$template_url}/css/rtl{$min_css}.css"							=> is_rtl(),
							"{$child_theme_url}/style.css"									=> $template_url != $child_theme_url,
						);

				// iterate over template builder modules and load the default css in there as well.
				// hakish approach that might need refinement if we improve the backend preview
				$path = trailingslashit( dirname( $this->paths['pluginPath'] ) ) . 'avia-shortcodes/';

				foreach( glob( $path . '*', GLOB_ONLYDIR ) as $folder )
				{
					$css_file = trailingslashit( $folder ) . basename( $folder ) . "{$min_css}.css";
					$css_url = trailingslashit( $this->paths['pluginUrlRoot'] ) . 'avia-shortcodes/' . basename( $folder ) . '/' . basename( $folder ) . "{$min_css}.css";

					if( file_exists( $css_file ) )
					{
						$css[ $css_url ] = 1;
					}
				}

				//custom user css, overwriting our styles
				$css[ "{$template_url}/css/custom.css" ] = 1;
				$css[ $avia_dyn_stylesheet_url ] = $avia_dyn_stylesheet_url;
				$css[ "{$template_url}/css/admin-preview{$min_css}.css" ] = 1;

				/**
				 * @param array $css
				 * @return array
				 */
				$css = apply_filters( 'avf_preview_window_css_files', $css );
			}

			//module css
			if( is_array( $css ) )
			{
				foreach( $css as $url => $load )
				{
					if( $load )
					{
						$output .= "<link rel='stylesheet' href='{$url}?ver={$ver}' type='text/css' media='all' />";
					}
				}
			}

			$output .= "<script type='text/javascript' src='" . includes_url( '/js/jquery/jquery.js' ). "?ver={$ver}'></script>";
			$output .= "<script type='text/javascript' src='{$builder_assets_url}/js/avia-admin-preview{$min_js}.js?ver={$ver}'></script>";
			$output .= $avia->style->link_google_font();

			/**
			 * !!!!!!!!!   Hakish approach that might need refinement if we add more elements to support js in the backend preview
			 *
			 * @since 4.8.6.3
			 * @param boolean $allow
			 * @return boolean|mixed		anything except true will disable js support
			 */
			if( true === apply_filters( 'avf_preview_window_js_support', true ) )
			{
				$monitor_elements = array(
								array( 'plugin' => 'avia_sc_toggle', 'selector' => '.togglecontainer' ),
								array( 'plugin' => 'avia_sc_progressbar', 'selector' => '.avia-progress-bar-container' ),
								array( 'plugin' => 'aviaIconCircles', 'selector' => '.av-icon-circles-container' ),
								array( 'plugin' => 'aviaCountdown', 'selector' => '.av-countdown-timer' ),

								array( 'plugin' => 'aviaChart', 'selector' => '.avia-chart-container' ),
								array( 'plugin' => 'aviaLottieAnimation', 'selector' => '.av-lottie-animation-container', 'object' => 'avia_js_shortcodes' ),
								array( 'plugin' => 'aviaImageDiff', 'selector' => '.avia-image-diff-container', 'object' => 'avia_js_shortcodes' ),
								array( 'plugin' => 'aviaFoldUnfoldSection', 'selector' => '.avia-fold-unfold-section', 'object' => 'avia_js_shortcodes' ),
					);


				$output .= '<script type="text/javascript">';
				$output .=		'/* <![CDATA[ */ ';
				$output .=			'var avia_monitor_preview_elements=' . json_encode( $monitor_elements ) . ';';
				$output .=		'/* ]]> */ ';
				$output .= '</script>';


				$output .= "<script type='text/javascript' src='{$template_url}/js/avia-js{$min_js}.js?ver={$ver}'></script>";
				$output .= "<script type='text/javascript' src='{$template_url}/js/avia{$min_js}.js?ver={$ver}'></script>";
				$output .= "<script type='text/javascript' src='{$template_url}/js/shortcodes{$min_js}.js?ver={$ver}'></script>";
				$output .= "<script type='text/javascript' src='{$template_url}/js/avia-snippet-fold-unfold{$min_js}.js?ver={$ver}'></script>";

				$url = trailingslashit( $this->paths['pluginUrlRoot'] ) . "avia-shortcodes/toggles/toggles{$min_js}.js";
				$output .= "<script type='text/javascript' src='{$url}?ver={$ver}'></script>";

				$url = trailingslashit( $this->paths['pluginUrlRoot'] ) . "avia-shortcodes/numbers/numbers{$min_js}.js";
				$output .= "<script type='text/javascript' src='{$url}?ver={$ver}'></script>";

				$url = trailingslashit( $this->paths['pluginUrlRoot'] ) . "avia-shortcodes/progressbar/progressbar{$min_js}.js";
				$output .= "<script type='text/javascript' src='{$url}?ver={$ver}'></script>";

				$url = trailingslashit( $this->paths['pluginUrlRoot'] ) . "avia-shortcodes/icon_circles/icon_circles{$min_js}.js";
				$output .= "<script type='text/javascript' src='{$url}?ver={$ver}'></script>";

				$url = trailingslashit( $this->paths['pluginUrlRoot'] ) . "avia-shortcodes/countdown/countdown{$min_js}.js";
				$output .= "<script type='text/javascript' src='{$url}?ver={$ver}'></script>";

				$url = trailingslashit( $this->paths['pluginUrlRoot'] ) . "avia-shortcodes/chart/chart-js{$min_js}.js";
				$output .= "<script type='text/javascript' src='{$url}?ver={$ver}'></script>";

				$url = trailingslashit( $this->paths['pluginUrlRoot'] ) . "avia-shortcodes/chart/chart{$min_js}.js";
				$output .= "<script type='text/javascript' src='{$url}?ver={$ver}'></script>";

				/*
				 * The player ships minified under its plain name, and there is no .min copy - asking
				 * for one left the preview without a player, and every Lottie preview threw.
				 */
				$url = trailingslashit( get_template_directory_uri() ) . 'config-lottie-animations/assets/lottie-player/dotlottie-player.js';
				$output .= "<script type='text/javascript' src='{$url}?ver={$ver}'></script>";

				$url = trailingslashit( $this->paths['pluginUrlRoot'] ) . "avia-shortcodes/lottie_animation/lottie_animation{$min_js}.js";
				$output .= "<script type='text/javascript' src='{$url}?ver={$ver}'></script>";

				$url = trailingslashit( $this->paths['pluginUrlRoot'] ) . "avia-shortcodes/image_diff/image_diff{$min_js}.js";
				$output .= "<script type='text/javascript' src='{$url}?ver={$ver}'></script>";
			}
			/**
			 * End of js support for backend preview
			 * =====================================
			 */

			$error = __( 'It seems you are currently adding some HTML markup or other special characters. Once all HTML tags are closed the preview will be available again. If this message persists please check your input for special characters and try to remove them.', 'avia_framework' );
			$html  = '<script type="text/javascript">var avia_preview = ' . json_encode( array( 'error' => $error, 'paths' => $output . $icon_font, 'title' => __( 'Element Preview', 'avia_framework' ) , 'background' => __( 'Set preview background:', 'avia_framework' ), 'scale' => __( 'Scaled to:', 'avia_framework' ) ) )  . "; \n";
			$html .= '</script>';

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
		 * Set Mode for debugging
		 *
		 * @since ???
		 * @param string $status			'safe' | 'debug'
		 **/
		public function setMode( $status = 'safe' )
	 	{
			/**
			 * Enable Avia Layout Builder Debug via theme option
			 *
			 * @link http://www.kriesi.at/documentation/enfold/enable-advanced-layout-builder-debug/
			 * @since 5.6
			 * @since 7.0						moved from functions.php
			 */
			$debug = avia_get_option( 'alb_developer_debug_mode' );

			if( false !== strpos( $debug, 'debug' ) && current_user_can( 'manage_options' ) )
			{
				$status = 'debug';
			}
			else if( 'debug' == $debug )
			{
				$status =  'debug';
			}

			/**
			 * @since ???
			 * @param string $status
			 * @return string					'safe' | 'debug'
			 */
			AviaBuilder::$mode = apply_filters( 'avia_builder_mode', $status );
		}

		/**
		 * Creates and returns instance of AviaSaveBuilderTemplate
		 *
		 * @since 4.6.4
		 * @since 7.1.5					added  $ignore_scripts
		 * @param boolean $ignore_scripts
		 * @return AviaSaveBuilderTemplate
		 */
		public function get_AviaSaveBuilderTemplate( $ignore_scripts = false )
		{
			if( ! $this->builderTemplate instanceof AviaSaveBuilderTemplate )
			{
				$this->builderTemplate = new AviaSaveBuilderTemplate( $this, $ignore_scripts );
			}

			return $this->builderTemplate;
		}

		/**
		 * Returns the array of critical characters for ALB modal popup options
		 *
		 * @since 4.7.6.4
		 * @return array
		 */
		public function critical_modal_charecters()
		{
			return $this->critical_modal_charecters;
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
			if( is_null( $this->supported_post_types ) )
			{
				$supported_post_types = [
									'page',
									'post',
									'portfolio',
									'product'
								];

				if( class_exists( 'aviaElementTemplates', false ) )
				{
					$supported_post_types[] = Avia_Element_Templates()->get_post_type();
				}

				if( class_exists( 'aviaCustomLayout', false ) )
				{
					$supported_post_types[] = Avia_Custom_Layout()->get_post_type();
				}

				$add = trim( avia_get_option( 'alb_active_post_types' ) );

				if( ! empty( $add ) )
				{
					$add = str_replace( "\r", "\n", $add );
					$add = explode( "\n", $add );
					$add = array_map( function ( $value ) { return trim( $value ); }, $add );

					$supported_post_types = array_merge( $supported_post_types, $add );
				}

				$this->supported_post_types = array_filter( $supported_post_types );
			}

			/**
			 *
			 * @since 4.3
			 * @param array $this->supported_post_types
			 * @return array
			 */
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
		 * Returns option settings
		 * Reduced in 4.6.4 to a single option for all developer input fields and only 2 options:
		 *		- hide input fields but use entered input
		 *		- show input fields
		 *
		 * @since 4.5.7.2
		 * @param string $setting
		 * @return string|false				depends on which setting
		 */
		public function get_developer_settings( $setting = '' )
		{
			$option_value = avia_get_option( 'alb_developer_options', '' );

			if( $option_value == 'alb_developer_options')
			{
				$option_value = 'hide';
			}
			else
			{
				$option_value = 'show';
			}


			$value = '';

			switch( $setting )
			{
				case 'custom_css':
					$value = 'show' == $option_value ? 'developer_options' : $option_value;
					break;
				case 'custom_id':
					$value = 'show' == $option_value ? 'developer_id_attribute' : $option_value;
					break;
				case 'heading_tags':
					$value = 'show' == $option_value ? 'developer_seo_heading_tags' : $option_value;
					break;
				case 'aria_label':
					$value = 'show' == $option_value ? 'developer_aria_label' : $option_value;
					break;
				case 'alb_desc_id':
					$value = 'show' == $option_value ? 'developer_alb_desc_id' : $option_value;
					break;
				case 'id_deeplink':
					$value = 'show' == $option_value ? 'developer_id_deeplink' : $option_value;
					break;
				default:
					$value = false;
			}


			/**
			 * In 4.6.4 option  Do not show, ignore ... has been removed
			 * You can use this filter to implement a fallback to the old solution.
			 * https://github.com/KriesiMedia/enfold-library/blob/master/actions%20and%20filters/ALB%20Elements/Editing%20ALB%20elements/avf_alb_get_developer_settings.php
			 *
			 * @since 4.5.7.2
			 * @param string|false $value
			 * @param string $setting
			 * @param string $option_value		added 4.6.4
			 * @return string|false
			 */
			return apply_filters( 'avf_alb_get_developer_settings', $value, $setting, $option_value );
		}

		/**
		 * Returns the stored theme version number
		 *
		 * @since 5.2
		 * @return string
		 */
		public function get_theme_version()
		{
			return $this->theme_version;
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
		 * @param boolean $save_to_revision boolean
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
		 * Store information in user meta data
		 *
		 * @since 4.6.4
		 * @param string $info
		 * @param mixed $value
		 * @return boolean
		 */
		public function set_backend_user_info( $info = '', $value = '' )
		{
			if( ! is_user_logged_in() )
			{
				return false;
			}

			$user_id = get_current_user_id();

			$meta = get_user_meta( $user_id, 'avia_alb_meta', true );
			if( ! is_array( $meta ) )
			{
				$meta = array();
			}

			$result = true;

			switch( $info )
			{
				case 'sc_sort_order':
					$meta[ $info ] = $value;
					break;
				default:
					$result = false;
					break;
			}

			if( $result )
			{
				update_user_meta( $user_id, 'avia_alb_meta', $meta );
			}

			return $result;
		}

		/**
		 * Returns user info stored in user meta
		 *
		 * @since 4.6.4
		 * @param string $info		'sc_sort_order'
		 * @return mixed|false
		 */
		public function get_backend_user_info( $info = '' )
		{
			if( ! is_user_logged_in() )
			{
				return false;
			}

			$user_id = get_current_user_id();

			$meta = get_user_meta( $user_id, 'avia_alb_meta', true );

			$result = false;
			switch( $info )
			{
				case 'sc_sort_order':
					return isset( $meta[ $info ] ) ? $meta[ $info ] : false;
				default:
					break;
			}

			return $result;
		}

		/**
		 * Callback to store user selection of sort order for alb shortcodes in editor
		 *
		 * @since 4.6.4
		 */
		public function handler_alb_shortcode_buttons_order()
		{
			header( 'Content-Type: application/json' );

			$return = check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce', false );
			if( ! current_user_can( 'edit_posts' ) )
			{
				$return = false;
			}

				// response output
			$response = array( '_ajax_nonce' => wp_create_nonce( 'avia_nonce_loader' ) );

			/**
			 * Return error and allow to resend data
			 */
			if( false === $return )
			{
				$response['success'] = false;
				$response['expired_nonce'] = true;
				echo json_encode( $response );
				exit;
			}

			$sorting = isset( $_REQUEST['sorting'] ) ? $_REQUEST['sorting'] : 'order';
			$this->set_backend_user_info( 'sc_sort_order', $sorting );

			$response['success'] = true;
			echo json_encode( $response );
			exit;
		}

		/**
		 * Set fullwidth elements that need to interact with section shortcode
		 *
		 * @deprecated 4.7.6.4
		 */
		public function setFullwidthElements( $elements = array() )
	 	{
			_deprecated_function( 'AviaBuilder::setFullwidthElements', '4.7.6.4', 'AviaBuilder::addFullwidthElements and $config[\'is_fullwidth\']' );

		 	$elements = apply_filters_deprecated( 'avf_fwd_elements', array( $elements ), '4.7.6.4', 'no replacement', 'use AviaBuilder::addFullwidthElements and $config[\'is_fullwidth\']' );

			AviaBuilder::$full_el_no_section = array_unique( array_merge( AviaBuilder::$full_el_no_section, $elements ) );
			AviaBuilder::$full_el = array_unique( array_merge( array( 'av_section' ), $elements ) );

		}

		/**
		 * Add to fullwidth elements that need to interact with section shortcode
		 *
		 * @since 4.7.6.4
		 * @param aviaShortcodeTemplate $shortcode
		 * @return void
		 */
		public function addFullwidthElements( aviaShortcodeTemplate $shortcode )
		{
			if( ! isset( $shortcode->config['is_fullwidth'] ) || 'yes' !== $shortcode->config['is_fullwidth'] )
			{
				return;
			}

			if( ! in_array( $shortcode->config['shortcode'], AviaBuilder::$full_el_no_section ) && 'av_section' != $shortcode->config['shortcode'] )
			{
				AviaBuilder::$full_el_no_section[] = $shortcode->config['shortcode'];
			}

			if( ! in_array( $shortcode->config['shortcode'], AviaBuilder::$full_el ) )
			{
				AviaBuilder::$full_el[] = $shortcode->config['shortcode'];
			}
		}

		/**
		 * calls external classes that are needed for the script to operate
		 */
		public function call_classes()
		{
			//create the meta boxes
			new MetaBoxBuilder( $this->paths['configPath'] );

			// save button
			$this->get_AviaSaveBuilderTemplate();

			//activate helper function hooks
			AviaHelper::backend();

			$min_js = avia_minify_extension( 'js' );

			/**
			 * Create the linebreak button
			 */
			$tiny_lb = array(
						'id'				=> 'av_builder_linebreak',
						'title'				=> __( 'Permanent Line Break', 'avia_framework' ),
						'access_key'		=> apply_filters( 'avf_access_key_tinymce', 'ctrl+alt+n', 'av_builder_linebreak' ),
						'content_open'		=> '\r\n<br class="avia-permanent-lb" />',
						'image'				=> $this->paths['imagesURL'] . 'tiny_line_break.png',
						'js_plugin_file'	=> $this->paths['assetsURL'] . "js/avia-tinymce-linebreak{$min_js}.js",
						'qtag_content_open'	=> '\r\n<br class="avia-permanent-lb" />',
						'qtag_display'		=> __( 'Line Break', 'avia_framework' ),
						'shortcodes'		=> array()
					);

			new avia_tinyMCE_button( $tiny_lb );

			//create tiny mce button
			$tiny = array(
						'id'				=> 'avia_builder_button',
						'title'				=> __( 'Insert Theme Shortcode', 'avia_framework' ),
						'image'				=> $this->paths['imagesURL'] . 'tiny-button.png',
						'js_plugin_file'	=> $this->paths['assetsURL'] . "js/avia-tinymce-buttons{$min_js}.js",
						'shortcodes'		=> array_map( array( $this, 'fetch_configs' ), $this->shortcode_class )
					);

			//if we are using tinymce 4 or higher change the javascript file
			global $tinymce_version;

			if( version_compare( $tinymce_version[0], 4, '>=' ) )
			{
				$tiny['js_plugin_file'] = $this->paths['assetsURL'] . "js/avia-tinymce-buttons-4{$min_js}.js";
			}

			/**
			 * @used_by					aviaDynamicContent::handler_avf_alb_magic_wand_button				10
			 * @since 6.0
			 * @param array $tiny
			 * @param array $this->shortcode_class
			 * @return array|null
			 */
			$tiny = apply_filters( 'avf_alb_magic_wand_button', $tiny, $this->shortcode_class );

			if( is_array( $tiny ) && ! empty( $tiny ) )
			{
				new avia_tinyMCE_button( $tiny );
				$this->alb_magic_wand_button = true;
			}

			//activate iconfont manager
//			new avia_font_manager();

		    //fetch all Wordpress pointers that help the user to use the builder
			include( $this->paths['configPath'] . 'pointers.php' );

			$myPointers = new AviaPointer( $pointers );
		}

		/**
		 *
		 * @since 4.2.3
		 * @return aviaAssetManager
		 */
		public function asset_manager()
		{
			if( empty( $this->asset_manager_class ) )
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
		 * allows to filter which shortcode assets to display in the frontend. waits for the 'wp' hook so the post id is already available
		 *
		 * @since 4.3
		 */
		public function frontend_asset_check()
		{
		 	//before creating shortcodes allow to filter the assets that are loaded in the frontend
		 	//pass shortcode names like 'av_video' or 'av_audio' in an array
		 	$this->disabled_assets = apply_filters( 'avf_disable_frontend_assets', $this->disabled_assets );

			/**
			 * Enable additional elements needed for parser info page
			 */
			if( isset( $_REQUEST['avia_alb_parser'] ) && ( 'show' == $_REQUEST['avia_alb_parser'] ) && current_user_can( 'edit_post', get_the_ID() ) )
			{
				if( class_exists( 'ShortcodeParser', false ) )
				{
					$this->disabled_assets = ShortcodeParser::enable_used_assets( $this->disabled_assets );
				}
			}
		}

		/**
		 * Array mapping helper that returns the config arrays of a shortcode
		 *
		 * @param array $array
		 * @return array
		 */
		public function fetch_configs( $array )
		{
			return $array->config;
		}

        /**
		 * Adds an extra class to the body if the builder is active
		 *
		 * @param string $classes
		 * @return string
		 **/
        public function admin_body_class( $classes )
        {
	        global $post_ID, $wp_version, $post;


			/**
			 * Filter to force usage of ALB for a specific post. This will hide the switch button
			 *
			 * @used_by					aviaCustomLayout::admin_body_class				500
			 * @since 6.0
			 * @param boolean $force_alb
			 * @param WP_Post $post
			 * @return boolean
			 */
			$force_alb = apply_filters( 'avf_force_alb_usage', false, $post );

			if( false !== $force_alb )
			{
				$classes .= ' avia-force-alb';
			}

			if( ! empty( $post_ID ) && $this->get_alb_builder_status( $post_ID ) )
			{
				$classes .= ' avia-advanced-editor-enabled';
			}
			else
			{
				$classes .= ' wp-default-editor-enabled';
			}

			/**
			 * Where the element panel is shown in the fullscreen builder - a side panel or the classic top toolbar.
			 *
			 * @since 8.0
			 * @param string $layout			'sidebar' | 'toolbar'
			 * @return string
			 */
			$panel_layout = apply_filters( 'avf_alb_element_panel_layout', avia_get_option( 'alb_element_panel_layout', 'sidebar' ) );

			$classes .= 'toolbar' == $panel_layout ? ' avia-alb-panel-toolbar' : ' avia-alb-panel-sidebar';

			if( version_compare( $wp_version, '5.5', '>=' ) )
			{
				$classes .= ' avia-wp55-fix ';
			}
			else if( version_compare( $wp_version, '5.4', '>=' ) )
			{
				$classes .= ' avia-wp54-fix ';
			}

			if( $this->disable_drag_drop == true )
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
			foreach( get_declared_classes() as $class )
			{
			    if( is_subclass_of( $class, 'aviaShortcodeTemplate' ) )
			    {
					$allow = false;
					$children[] = $class;
					$this->shortcode_class[ $class ] = new $class( $this );
					$shortcode = $this->shortcode_class[ $class ]->config['shortcode'];

					$this->addFullwidthElements( $this->shortcode_class[ $class ] );

					//check if the shortcode is allowed. if so init the shortcode, otherwise unset the item
					if( empty( ShortcodeHelper::$manually_allowed_shortcodes ) && empty( ShortcodeHelper::$manually_disallowed_shortcodes ) )
					{
						$allow = true;
					}

					if( ! $allow && ! empty( ShortcodeHelper::$manually_allowed_shortcodes ) && in_array( $shortcode, ShortcodeHelper::$manually_allowed_shortcodes ) )
					{
						$allow = true;
					}

					if( ! $allow && ! empty( ShortcodeHelper::$manually_disallowed_shortcodes ) && ! in_array( $shortcode, ShortcodeHelper::$manually_disallowed_shortcodes ) )
					{
						$allow = true;
					}

					if( $allow )
					{
						$this->shortcode_class[ $class ]->init();
						$this->shortcode[ $this->shortcode_class[ $class ]->config['shortcode'] ] = $class;

						//save if the asset may be disabled automatically
						if( ! empty( $this->shortcode_class[ $class ]->config['disabling_allowed'] ) && $this->shortcode_class[ $class ]->config['disabling_allowed'] !== 'manually' )
						{
							$this->may_be_disabled_automatically[] = $this->shortcode_class[$class]->config['shortcode'] ;
						}

						//save shortcode as allowed by default. if we only want to display the shortcode in tinymce remove it from the list but keep the class instance alive
						if( empty( $this->shortcode_class[ $class ]->config['tinyMCE']['tiny_only'] ) )
						{
							ShortcodeHelper::$allowed_shortcodes[] = $this->shortcode_class[ $class ]->config['shortcode'];
						}

						//save nested shortcodes if they exist
						if( isset( $this->shortcode_class[ $class ]->config['shortcode_nested'] ) )
						{
							ShortcodeHelper::$nested_shortcodes = array_merge( ShortcodeHelper::$nested_shortcodes, $this->shortcode_class[ $class ]->config['shortcode_nested'] );
						}
					}
					else
					{
					   unset( $this->shortcode_class[ $class ] );
					}
			    }
			}

			/**
			 * Fallback: Make sure 'av_section' is included in AviaBuilder::$full_el;
			 */
			if( ! in_array( 'av_section', AviaBuilder::$full_el ) )
			{
				AviaBuilder::$full_el[] = 'av_section';
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

			/**
			 * Fires after all ALB shortcode elements have been instantiated and the
			 * parent/children containment map has been built - i.e. the element registry
			 * ( $shortcode_class, $shortcode, $shortcode_parents ) is fully populated.
			 *
			 * Added as a stable integration point for the optional AI Builder schema
			 * extractor. Safe no-op when no listener is attached.
			 *
			 * @since 7.1.6
			 * @param AviaBuilder $this
			 */
			do_action( 'ava_builder_shortcodes_registered', $this );
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
			 if( empty( $tag ) || ! is_string( $tag ) )
			{
				return false;
			}

			$match = array();

			$regex = "\[\/?([\w|-]+)";			//	gets opening and closing tag till first space after tag
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
		 * Returns the parent (main) shortcode class for a given item shortcode.
		 * Usually this is index 0, but in the rare case an item shortcode is bound to different shortcodes you can specify an index.
		 * But you need to find the correct index needed before calling this function.
		 *
		 * @since 4.8.4
		 * @param string $item_sc_name
		 * @param int $index
		 * @return aviaShortcodeTemplate|false
		 */
		public function get_parent_shortcode_class( $item_sc_name, $index = 0 )
		{
			if( isset( $this->shortcode_parents[ $item_sc_name ] ) && isset( $this->shortcode_parents[ $item_sc_name ][ $index ] ) )
			{
				return $this->get_shortcode_class( $this->shortcode_parents[ $item_sc_name ][ $index ] );
			}

			return false;
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
		 * Create JS templates
		 */
		public function js_template_editor_elements()
		{
			foreach( $this->shortcode_class as $shortcode )
			{
				if( Avia_Element_Templates()->is_edit_element_page() )
				{
					Avia_Element_Templates()->add_element_template_options( $shortcode );
				}

				$template = $shortcode->prepare_editor_element( false, array(), true );

				if( ! is_array( $template ) )
				{
					echo $this->js_template_script( $template, $shortcode->config['php_class'] );
				}
			}

			Avia_Element_Templates()->js_template_editor_elements();
		}

		/**
		 * Returns script tag for an ALB template to be inserted in canvas
		 *
		 * @since 4.8
		 * @param string $template
		 * @param string $id_fragment
		 * @return string
		 */
		public function js_template_script( $template, $id_fragment )
		{
			//	ensure we have empty unique id's in js templates
			$template = Avia_Builder()->element_manager()->clear_element_ids_in_content( $template );

			$script = '';

			$script .=	"\n<script type='text/html' id='avia-tmpl-{$id_fragment}'>\n";
			$script .=		$template;
			$script .=	"\n</script>\n\n";

			return $script;
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

				if( Avia_Element_Templates()->is_element_post_type( $data['post_type'] ) )
				{
					$content = wp_unslash( $_POST['_aviaLayoutBuilderCleanData'] );
					$_POST['_aviaLayoutBuilderCleanData'] = $this->element_manager->clear_element_ids_in_content( $content );
					$data['post_content'] = $_POST['_aviaLayoutBuilderCleanData'];
					$_POST['post_content'] = $_POST['_aviaLayoutBuilderCleanData'];
				}
				else
				{
					$_POST['_aviaLayoutBuilderCleanData'] = $this->element_manager->set_element_ids_in_content( $_POST['_aviaLayoutBuilderCleanData'], $post_id );
				}
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

			foreach( $boxes as $key => $box )
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
				 * Usefull for special elements that we might need to render outside of the default loop like fullscreen slideshows.
				 *
				 * We always save this tree so we can be sure to have the correct state of the page when we load this post meta.
				 */
				$tree = ShortcodeHelper::build_shortcode_tree( $_POST['content'] );
				$this->save_shortcode_tree( $post_id, $tree );

				$this->element_manager->updated_post_content( $_POST['content'], $post_id );

				if( 'active' == $builder_stat )
				{
					Avia_Element_Templates()->updated_post_content( $_POST['content'], $post_id, $tree );
				}

				/**
				 * Now we can save the postmeta data to revision post
				 */
				$this->save_alb_revision_data( $post_id );
            }
		}

		/**
		 * checks if a dynamic template exists and uses that template instead of the default page template
		 *
		 * @param string $original_template
		 * @return string|null
		 */
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

    	   	if( ( $post_id && is_singular() ) || isset( $avia_config['builder_redirect_id'] ) )
    	   	{
				if( ! empty( $avia_config['builder_redirect_id'] ) )
				{
					$post_id = $avia_config['builder_redirect_id'];
				}

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

					if( 'default' == $template_file || empty( $template_file ) )
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
    	   	   if( isset( $avia_config['builder_redirect_id'] ) )
    	   	   {
    	   	   		if( $template = locate_template( 'page.php', false ) )
    	   	   		{
    	   	   			return $template;
    	   	   		}
    	   	   }
    	   	}

    	   	return $original_template;
    	}

		/**
		 *
		 *
		 */
		public function apply_editor_wrap()
		{
			//fetch the config array
			include( $this->paths['configPath'] . 'meta.php' );

			$slug = '';
			$pages = array();

			//check to which pages the avia builder is applied
			foreach( $elements as $element )
			{
				if( is_array( $element['type'] ) && $element['type'][1] == 'visual_editor' )
				{
					$slug = $element['slug'];
					break;
				}
			}

			foreach( $boxes as $box )
			{
				if( is_array( $box ) && isset( $box['id'] ) && $box['id'] == $slug )
				{
					$pages = $box['page'];
				}
			}

    		global $typenow;

    		if( ! empty( $pages ) && in_array( $typenow, $pages ) )
    		{
		    	//html modification of the admin area: wrap
				add_action( 'edit_form_after_title', array( $this, 'wrap_default_editor' ), 100000, 2 );
				add_action( 'edit_form_after_editor', array( $this, 'close_default_editor_wrap' ), 1, 1 );
			}
		}

		/**
		 * Adds the ALB switch button
		 *
		 * @param WP_Post|null $post
		 * @param string $close_div			'' | 'close'
		 */
		public function wrap_default_editor( $post = null, $close_div = '' )
		{
			global $post_ID;

			$status = $this->get_alb_builder_status( $post_ID );



			/**
			 * note_key makes a note dismissable. Leave it empty for a note that explains
			 * why something is disabled - the user must not be able to hide those.
			 */
			$params = array(
							'disabled'		=> false,
							'note'			=> '',
							'noteclass'		=> '',
							'note_key'		=> '',
							'button_class'	=> '',
							'visual_label'	=> __( 'Advanced Layout Editor', 'avia_framework' ),
							'default_label'	=> __( 'Default Editor', 'avia_framework' )
						);

			/**
			 * Filter button layout parameteres
			 *
			 * @since 4.7.4.1			added params $post, $close_div
			 * @param array $params
			 * @param WP_Post|null $post
			 * @param string $close_div
			 */
            $params = apply_filters( 'avf_builder_button_params', $params, $post, $close_div );


            if( $params['disabled'] )
			{
				$status = false;
			}

			$active_builder = $status == 'active' ? $params['default_label'] : $params['visual_label'];
			$editor_class   = $status == 'active' ? 'class="avia-hidden-editor"' : '';
			$button_class   = $status == 'active' ? 'avia-builder-active' : '';

			echo "<div id='postdivrich_wrap' {$editor_class}>";

			if( $this->disable_drag_drop == false )
			{
				echo '<a id="avia-builder-button" href="#" class="avia-builder-button button-primary ' . $button_class . ' ' . $params['button_class'] . '" data-active-button="' . $params['default_label'] . '" data-inactive-button="' . $params['visual_label'] . '">' . $active_builder . '</a>';
			}

			/**
			 * Allow to add additional buttons
			 *
			 * @since 4.7.4.1
			 * @param array $params
			 * @param WP_Post|null $post
			 * @param string $close_div
			 */
			do_action( 'ava_after_layout_builder_button', $params, $post, $close_div );

			if( $params['note'] )
			{
				//	returns '' when the note carries a key this user has already closed
				$note_key = isset( $params['note_key'] ) ? $params['note_key'] : '';

				echo Avia_Builder_Notes()->get_note_html( $params['note'], $params['noteclass'], $note_key );
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
			echo '</div>';
		}


		/**
		 * Function called by the metabox class that creates the interface in your wordpress backend
		 *
		 * @param array $element
		 */
		public function visual_editor( $element )
		{
			global $post, $post_ID;

			$output = '';
			$tabs_content = '';
			$tabs_title = '';
			$i = 0;

			/**
			 * @used_by				Avia_Gutenberg						10
			 *
			 * @since 4.5.1
			 * @param string $output
			 * @param array $element
			 * @return string
			 */
			$output = apply_filters( 'avf_builder_metabox_editor_before', $output, $element );

			/**
			 * Disable fix for tinyMCE bullet list indent/detent for special pages if it makes problems
			 *
			 * @since 4.6.0
			 * @return boolean
			 */
			$fix = apply_filters( 'avf_alb_tinymce_ignore_indent_fix', false );
			$fix_class = false !== $fix ? 'avia-ignore-tiny-indent-fix' : '';

			$output .= '<div class="avia-builder-main-wrap ' . $fix_class . '" data-post_type="' . $post->post_type . '" data-post_id="' . $post->ID . '">';

			/**
			 *
			 * @used_by  aviaShortcodeTemplate::add_backend_button		10
			 *
			 * @since <4.0
			 * @param array
			 * @return array
			 */
			$this->shortcode_buttons = apply_filters( 'avia_show_shortcode_button', array() );

			if( ! empty( $this->shortcode_buttons ) && $this->disable_drag_drop == false )
			{
				$this->tabs = isset( $element['tab_order'] ) ? array_flip( $element['tab_order'] ) : array();

				foreach( $this->tabs as &$empty_tabs )
				{
					$empty_tabs = array();
				}

				foreach( $this->shortcode_buttons as $shortcode )
				{
					if( empty( $shortcode['tinyMCE']['tiny_only'] ) )
					{
						if( ! isset( $shortcode['tab'] ) )
						{
							$shortcode['tab'] = $this->default_sc_btn_tab_name;
						}

						$this->tabs[ $shortcode['tab'] ][] = $shortcode;
					}
				}

				foreach( $this->tabs as $key => $tab )
				{
					if( empty( $tab ) )
					{
						continue;
					}

					usort( $tab, array( $this, 'sortByOrder' ) );

					$i ++;
					$tabs_title .= "<a class='avia-alb-tab' href='#avia-tab-{$i}'>{$key}</a>";

					$tabs_content .= "<div class='avia-tab av-alb-base-element-tab avia-tab-{$i}'>";

					$sort_order = 0;
					foreach( $tab as $shortcode )
					{
						if( empty( $shortcode['invisible'] ) )
						{
							$sort_order ++;
							$tabs_content .= $this->create_shortcode_button( $shortcode, $sort_order );
						}
					}

					$tabs_content .= '</div>';
				}
			}

			//	Add shortcode buttons for element templates
			Avia_Element_Templates()->add_visual_editor_custom_element_content( $tabs_title, $tabs_content, $i, $element );

			$active_builder = $this->get_alb_builder_status( $post_ID );

			$extra = AviaBuilder::$mode != true ? '' : 'avia_mode_' . AviaBuilder::$mode;
			$hotekey_info = htmlentities( $element['desc'], ENT_QUOTES, get_bloginfo( 'charset' ) );


			$output .= '<div class="shortcode_button_wrap avia-tab-container">';
			$output .=		'<div class="avia-tab-title-container">' . $tabs_title . '</div>';
			$output .=		$tabs_content;
			$output .= '</div>';

			$output .= '<input type="hidden" value="' . $active_builder . '" name="aviaLayoutBuilder_active" id="aviaLayoutBuilder_active" />';


			$icon = avia_font_manager::get_frontend_icon( 'arrow-combo', 'svg_entypo-fontello', [ 'aria-hidden' => 'true', 'title' => '', 'desc' => '' ] );
			$icon_class = avia_font_manager::get_frontend_icon_classes( $icon['font'], 'string' );

			$user_info = $this->get_backend_user_info( 'sc_sort_order' );
			$init_sort = ( ! empty( $user_info ) ) ? $user_info : 'order';

			$sorting_label = array(
								'order' 	=> __( 'Default', 'avia_framework' ),
								'name_asc' 	=> __( 'By name (ascending)', 'avia_framework' ),
								'name_desc' => __( 'By name (descending)', 'avia_framework' ),
								'usage' 	=> __( 'By usage', 'avia_framework' ),
							);


			$output .=	'<div id="avia-sort-list-dropdown" class="avia-sort-list-container" data-init_sort="' . $init_sort . '">';
			$output .=		'<ul class="avia-sort-list-select">';
			$output .=			'<li class="avia-sort-list-wrap">';
			$output .=				'<strong data-avia-panel-tooltip="' . esc_attr( __( 'Sort elements', 'avia_framework' ) ) . '">';
			$output .=					"<span class='avia-font-entypo-fontello {$icon_class}' {$icon['attr']}>";
			$output .=						$icon['svg'];
			$output .=					'</span> ';
			$output .=					__( 'Sorting', 'avia_framework' ) . ': <span class="avia-sort-list-label">' . $sorting_label[$init_sort] . '</span>';
			$output .=				'</strong>';
			$output .=				'<ul class="avia-sort-list-main">';
			$output .=					'<li class="avia-sort-list-element">';
			$output .=						'<a href="#" class="sort_active" data-sorting="order" title="' . esc_attr( __( 'Sort shortcode buttons by theme default sorting', 'avia_framework' ) ) . '">' . __( 'Default', 'avia_framework' ) . '</a>';
			$output .=					'</li>';
			$output .=					'<li class="avia-sort-list-element">';
			$output .=						'<a href="#" class="" data-sorting="name_asc" title="' . esc_attr( __( 'Sort shortcode buttons by name ascending', 'avia_framework' ) ) . '">' . __( 'By name (ascending)', 'avia_framework' ) . '</a>';
			$output .=					'</li>';
			$output .=					'<li class="avia-sort-list-element">';
			$output .=						'<a href="#" class="" data-sorting="name_desc" title="' . esc_attr( __( 'Sort shortcode buttons by name descending', 'avia_framework' ) ) . '">' . __( 'By name (descending)', 'avia_framework' ) . '</a>';
			$output .=					'</li>';
			$output .=					'<li class="avia-sort-list-element">';
			$output .=						'<a href="#" class="" data-sorting="usage" title="' . esc_attr( __( 'Sort shortcode buttons by usage', 'avia_framework' ) ) . '">' . __( 'By usage', 'avia_framework' ) . '</a>';
			$output .=					'</li>';
			$output .=				'</ul>';
			$output .=			'</li>';
			$output .=		'</ul>';
			$output .=	'</div>';

			/**
			 * Gives the builder the whole screen - see avia-panel-fullscreen.js for what that means in
			 * each editor. Outside the drag and drop check on purpose: room to work is not a drag feature,
			 * and the button is as useful to someone who has drag and drop switched off.
			 */
			$fullscreen_label = __( 'Fullscreen', 'avia_framework' );

			$output .=	'<a href="#fullscreen" class="avia-alb-fullscreen" role="button" aria-pressed="false" data-avia-panel-tooltip="' . esc_attr( $fullscreen_label ) . '">';
			$output .=		esc_html( $fullscreen_label );
			$output .=	'</a>';

			if( $this->disable_drag_drop == false )
			{
				$output .= '<a href="#info" class="avia-hotkey-info" data-avia-help-tooltip="' . $hotekey_info . '" data-avia-panel-tooltip="' . esc_attr( __( 'Keyboard shortcuts', 'avia_framework' ) ) . '">' . __( 'Information', 'avia_framework' ) . '</a>';
				$output .= $this->builderTemplate->create_save_button();
			}

			$output .= "<div class='layout-builder-wrap {$extra}'>";

			if( $this->disable_drag_drop == false )
			{
				$output .=		"<div class='avia-controll-bar'></div>";
			}

			$output .=		"<div id='aviaLayoutBuilder' class='avia-style avia_layout_builder avia_connect_sort preloading av_drop' data-dragdrop-level='0'>";
			$output .=		'</div>';


			$clean_data = $this->get_posts_alb_content( $post_ID );
			// $clean_data = htmlentities($clean_data, ENT_QUOTES, get_bloginfo( 'charset' )); //entity-test: added htmlentities


			$output .=		"<textarea id='_aviaLayoutBuilderCleanData' name='_aviaLayoutBuilderCleanData'>{$clean_data}</textarea>";
			$output .=		'<input type="hidden" name="avia-loader-nonce" id="avia-loader-nonce" value="' . wp_create_nonce( 'avia_nonce_loader' ) . '" />';

			$output .=		Avia_Element_Templates()->add_alb_editor_content();

			$output .= '</div>';

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

		/**
		 * Create a shortcode button
		 *
		 * @since < 4.0
		 * @param array $shortcode
		 * @param int $sort_order
		 * @param string $editor_js_tmpl_id				added 4.8
		 * @return string
		 */
		public function create_shortcode_button( $shortcode, $sort_order, $editor_js_tmpl_id = '' )
		{
			if( empty( $this->shortcode_usage_blog ) )
			{
				$this->shortcode_usage_blog = Avia_Builder()->element_manager()->get_elements_count( 'blog' );
			}

			$class = '';
			$disabled = false;
			$tooltip = '';

			$usage = isset( $this->shortcode_usage_blog[ $shortcode['shortcode'] ] ) ? $this->shortcode_usage_blog[ $shortcode['shortcode'] ] : 0;


			/**
			 * @used_by enfold\includes\helper-assets.php:: function av_disable_button_in_backend		10
			 *
			 * @since 4.3
			 * @param array $shortcode
			 * @return array
			 */
			$shortcode = apply_filters( 'avf_shortcode_insert_button_backend', $shortcode );

			//disable element based on post type
			if( ! empty( $shortcode['posttype'] ) && $shortcode['posttype'][0] != AviaHelper::backend_post_type() )
			{
				$tooltip = $shortcode['posttype'][1];
				$disabled = true;
			}

			//disable element based on condition
			if( ! empty( $shortcode['disabled'] ) && $shortcode['disabled']['condition'] === true )
			{
				$tooltip = $shortcode['disabled']['text'];
				$disabled = true;
			}

			/**
			 * Allow to enable/disable a shortcode button
			 *
			 * @used_by								aviaDynamicContent      10
			 * @since 6.0
			 * @param boolean $disabled
			 * @param array $shortcode
			 * @return boolean
			 */
			$disabled = apply_filters( 'avf_shortcode_insert_button_backend_disabled', $disabled, $shortcode );

			if( true === $disabled )
			{
				$class .= 'av-shortcode-disabled ';
				$shortcode['tooltip'] = $tooltip;
			}


			$icon = isset( $shortcode['icon'] ) ? Avia_Element_Icons()->get_html( $shortcode['icon'], $shortcode['name'] ) : '';

			$data  = ! empty( $shortcode['tooltip'] ) ? ' data-avia-tooltip="' . esc_attr( $shortcode['tooltip'] ) . '" ' : '';
			$data .= ! empty( $shortcode['drag-level'] ) ? " data-dragdrop-level='{$shortcode['drag-level']}' " : '';
			$data .= " data-sort_order='{$sort_order}' ";
			$data .= ' data-sort_name="' . esc_attr( $shortcode['name'] ) . '" ';
			$data .= " data-sort_usage='{$usage}' ";

			if( isset( $shortcode['btn_data'] ) && ! empty( $shortcode['btn_data'] ) )
			{
				$data .= ' ' . AviaHelper::create_data_string( $shortcode['btn_data'] );
			}

			$class .= isset( $shortcode['class'] ) ? $shortcode['class'] : '';
            $class .= ! empty( $shortcode['target'] ) ? " {$shortcode['target']}" : '';

			$href = empty( $editor_js_tmpl_id ) ? $shortcode['php_class'] : $editor_js_tmpl_id;
			$id = isset( $shortcode['btn_id'] ) ? "id='{$shortcode['btn_id']}'" : '';

			$link   = '';
			$link  .= "<a {$id} {$data} href='#{$href}' class='shortcode_insert_button {$class}' >{$icon}<span>{$shortcode['name']}</span></a>";

			return $link;
		}


		/**
		 * Helper function to sort the shortcode buttons
		 *
		 * @param array $a
		 * @param array $b
		 * @return int
		 */
		protected function sortByOrder( array $a, array $b )
		{
			if( empty( $a['order'] ) )
			{
				$a['order'] = 10;
			}

			if( empty( $b['order'] ) )
			{
				$b['order'] = 10;
			}

   			return $b['order'] >= $a['order'] ? 1 : -1;
		}


		public function modal_group_text_to_interface()
		{
			header( 'Content-Type: application/json' );

			$return = check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce', false );
			if( ! current_user_can( 'edit_posts' ) )
			{
				$return = false;
			}

				// response output
			$response = array(
							'_ajax_nonce'	=> wp_create_nonce( 'avia_nonce_loader' ),
							'success'		=> true,
							'html'			=> ''
						);

			/**
			 * Return error and allow to resend data
			 */
			if( false === $return )
			{
				$response['success'] = false;
				$response['expired_nonce'] = true;

				echo json_encode( $response );
				exit;
			}

			if( ! isset( $_REQUEST['text'] ) || empty( $_REQUEST['text'] ) )
			{
				echo json_encode( $response );
				exit;
			}

			if( ! isset( $_REQUEST['shortcode'] ) || empty( $_REQUEST['shortcode'] ) )
			{
				echo json_encode( $response );
				exit;
			}

			$sc_array = ShortcodeHelper::shortcode2array( wp_unslash( $_REQUEST['text'] ) );

			$parent = $this->shortcode_parents[ $sc_array[0]['shortcode'] ];

			$params['class'] = '';
			$params['innerHtml'] = '';
			$params['args'] = $sc_array[0]['attr'];
			$params['content'] = $sc_array[0]['raw_content'];

			$params = $this->shortcode_class[ $this->shortcode[ $parent[0] ] ]->editor_sub_element( $params );

			$response['html'] = $params['innerHtml'];

			echo json_encode( $response );
			exit;
		}

		/**
		 *
		 *
		 * @param string|null $text
		 * @return string|void
		 */
		public function text_to_interface( $text = null )
		{
			if( ! current_user_can( 'edit_posts' ) )
			{
				die();
			}

			if( isset( $_REQUEST['params']['_ajax_nonce'] ) )
			{
				$_REQUEST['_ajax_nonce'] = $_REQUEST['params']['_ajax_nonce'];
			}

			check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce' );

			global $shortcode_tags;

			$allowed = false;

			//isset when avia_ajax_text_to_interface is executed (avia_builder.js)
			if( isset( $_POST['text'] ) )
			{
				$text = $_POST['text'];
			}

			if( isset( $_POST['post_id'] ) )
			{
				$this->text_to_interface_post_id = $_POST['post_id'];
			}

			//only build pattern with a subset of shortcodes
			if( isset( $_POST['params'] ) && isset( $_POST['params']['allowed'] ) )
			{
				$allowed = explode( ',', $_POST['params']['allowed'] );
			}

			//build the shortcode pattern to check if the text that we want to check uses any of the builder shortcodes
			ShortcodeHelper::build_pattern( $allowed );

			$text_nodes = preg_split( "/" . ShortcodeHelper::$pattern . "/s", $text );

			foreach( $text_nodes as $node )
			{
	            if( strlen( trim( $node ) ) == 0 || strlen( trim( strip_tags( $node) ) ) == 0 )
	            {
	               //$text = preg_replace("/(".preg_quote($node, '/')."(?!\[\/))/", '', $text);
	            }
	            else
	            {
	               $text = preg_replace( "/(" . preg_quote( $node, '/' ) . "(?!\[\/))/", '[av_textblock]$1[/av_textblock]', $text );
	            }
	        }

			$text = $this->do_shortcode_backend( $text );

			$this->text_to_interface_post_id = 0;

			if( isset( $_POST['text'] ) )
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
		 */
		public function text_to_preview()
		{
			if( ! current_user_can( 'edit_posts' ) )
			{
				die();
			}

			check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce' );

			$text = isset( $_REQUEST['text'] ) ? stripslashes( $_REQUEST['text'] ) : '';
			$this->text_to_preview_post_id = isset( $_REQUEST['text_to_preview_post_id'] ) ? $_REQUEST['text_to_preview_post_id'] : 0;

			$this->in_text_to_preview = true;

			//	get inline style tag - since HTML 5.2 w3c compliant - but we are in backend anyway
			$preview = AviaPostCss()->alb_preview_callback( $text, $this->text_to_preview_post_id );
			$preview .= do_shortcode( $text );

			/**
			 * Allow third party to modify content
			 *
			 * @since 4.3.1
			 * @param string $preview
			 * @param string $text
			 * @return string
			 */
			$output = apply_filters( 'avf_text_to_preview', $preview, $text );

			$this->text_to_preview_post_id = 0;
			$this->in_text_to_preview = false;

			echo $output;
			exit();
		}

		/**
		 * Ajax callback to return preview output for svg dividers in modal popup.
		 * Returns a list of preview windows for each divider
		 *
		 * @since 4.8.4
		 * @param string|null $text
		 */
		public function text_to_preview_svg_dividers()
		{
			header( 'Content-Type: application/json' );

			$return = check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce', false );
			if( ! current_user_can( 'edit_posts' ) )
			{
				$return = false;
			}

				// response output
			$response = array( '_ajax_nonce' => wp_create_nonce( 'avia_nonce_loader' ) );

			/**
			 * Return error and allow to resend data
			 */
			if( false === $return )
			{
				$response['success'] = false;
				$response['expired_nonce'] = true;
				echo json_encode( $response );
				exit;
			}

			$text = isset( $_REQUEST['text'] ) ? stripslashes( $_REQUEST['text'] ) : '';
			$post_id = isset( $_REQUEST['text_to_preview_post_id'] ) ? $_REQUEST['text_to_preview_post_id'] : 0;
			$svg_list = isset( $_REQUEST['svg_list'] ) ? $_REQUEST['svg_list'] : array();

			$this->in_text_to_preview = true;

			//	get inline style tag and preview container - since HTML 5.2 w3c compliant - but we are in backend anyway
			$preview_list = AviaPostCss()->alb_preview_svg_dividers_callback( $text, $post_id, $svg_list );

			$this->in_text_to_preview = false;

			if( false === $preview_list )
			{
				$response['success'] = false;
			}
			else
			{
				$response['success'] = true;
				$response['preview_list'] = $preview_list;
			}

			echo json_encode( $response );
			exit;
		}

		/**
		 * Returns if we are in ajax callback for modal popup preview
		 *
		 * @since 4.5.4
		 * @return boolean
		 */
		public function in_text_to_preview_mode()
		{
			return $this->in_text_to_preview;
		}

		/**
		 * Returns the ID of post when in text to preview mode
		 *
		 * @since 6.0
		 * @return int
		 */
		public function get_text_to_preview_post_id()
		{
			return $this->text_to_preview_post_id;
		}

		/**
		 * Returns if we are in ajax callback for text to interface (= building the canvas)
		 *
		 * @since 6.0
		 * @return boolean
		 */
		public function in_text_to_interface_mode()
		{
			return $this->text_to_interface_post_id > 0;
		}

		/**
		 * Returns the ID of post when in text to interface mode
		 *
		 * @since 6.0
		 * @return int
		 */
		public function get_text_to_interface_post_id()
		{
			return $this->text_to_interface_post_id;
		}

		/**
		 *
		 * @param string $text
		 * @return string
		 */
		public function do_shortcode_backend( $text )
		{
			return preg_replace_callback( "/" . ShortcodeHelper::$pattern . "/s", array( $this, 'do_shortcode_tag' ), $text );
		}

		/**
		 *
		 *
		 * @param string $m
		 * @return string
		 */
		public function do_shortcode_tag( $m )
		{
	        global $shortcode_tags;

			// allow [[foo]] syntax for escaping a tag
			if ( $m[1] == '[' && $m[6] == ']' )
			{
				return substr( $m[0], 1, -1 );
			}

			//check for enclosing tag or self closing
			$values['closing'] = strpos( $m[0], '[/' . $m[2] . ']' );
			$values['content'] = $values['closing'] !== false ? $m[5] : null;
	        $values['tag'] = $m[2];
	        $values['attr'] = shortcode_parse_atts( stripslashes( $m[3] ) );

	        if( is_array( $values['attr'] ) )
	        {
		        $charset = get_bloginfo( 'charset' );
		        foreach( $values['attr'] as &$attr )
		        {
		        	$attr =	htmlentities( $attr, ENT_QUOTES, $charset );
		        }
			}
			else
			{
				 $values['attr'] = array();
			}

	        if( isset( $_POST['params']['extract'] ) )
	        {
	        	//if we open a modal window also check for nested shortcodes
	        	if( $values['content'] )
				{
					$values['content'] = $this->do_shortcode_backend( $values['content'] );
				}

	        	$_POST['extracted_shortcode'][] = $values;

	        	return $m[0];
	        }

			if( in_array( $values['tag'], ShortcodeHelper::$allowed_shortcodes ) )
			{
				return $this->shortcode_class[ $this->shortcode[ $values['tag'] ] ]->prepare_editor_element( $values['content'], $values['attr'] );
			}
			else
			{
				return $m[0];
			}
		}



		/**
		 * Tells the canvas which share buttons the site offers by default.
		 *
		 * A Social Buttons element set to follow the blog draws whatever is switched on under Blog
		 * Layout, and that answer is the same for every such element on the page - so it is worked out
		 * once here rather than by each element in turn, and written as a rule the stylesheet applies.
		 * Every element then draws one set of icons instead of two, and the page carries one copy of
		 * the answer instead of one per element.
		 *
		 * Nothing is enqueued when the site has no share buttons switched on, which is also the case
		 * where an element following the blog correctly shows none.
		 *
		 * @since 8.0
		 * @return void
		 */
		protected function add_share_defaults_style()
		{
			$icons = avia_font_manager::get_icon_shortcuts();

			if( ! is_array( $icons ) )
			{
				return;
			}

			$options = avia_get_option();

			if( ! is_array( $options ) )
			{
				return;
			}

			$selectors = array();

			foreach( $icons as $key => $icon )
			{
				if( 0 !== strpos( $key, 'svg__' ) )
				{
					continue;
				}

				/*
				 * A network counts as offered only if it was saved as such. One that was never saved is
				 * off, not on - which is the rule the frontend follows when it draws these buttons, and
				 * reading it the other way would show every network the theme knows on an element whose
				 * owner had chosen none. See class-social-media-icons.php, which says as much.
				 */
				$share_key = 'share_' . $key;

				if( ! isset( $options[ $share_key ] ) || ( 'disabled' == $options[ $share_key ] ) )
				{
					continue;
				}

				$selectors[] = '.avia_inner_shortcode [data-update_class_with="buttons"]:not(.avia-buttons-custom) span[data-update_class_with="share_' . $key . '"]';
			}

			if( empty( $selectors ) )
			{
				return;
			}

			wp_add_inline_style( 'avia-builder-style', implode( ',', $selectors ) . '{display:block}' );
		}

		/**
		 * this helper function tells the tiny_mce_editor to remove any span tags that dont have a classname (list insert on ajax tinymce tend do add them)
		 * see more: http://martinsikora.com/how-to-make-tinymce-to-output-clean-html
		 */

		public function tiny_mce_helper( $mceInit )
		{
			$mceInit['extended_valid_elements'] = empty( $mceInit['extended_valid_elements'] ) ? '' : $mceInit['extended_valid_elements'] . ',';
			$mceInit['extended_valid_elements'] = 'span[!class]';

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
			 * @param array $meta_keys
			 * @param int $post_id
			 * @param string $context		'save' | 'restore'
			 * @return array
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
				$builder_meta_data = get_metadata( 'post', $revision_id, $meta_field, true );

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
			$first_el = isset( ShortcodeHelper::$tree[0] ) ? ShortcodeHelper::$tree[0] : false;
			$last_el = ! empty( ShortcodeHelper::$tree ) ? end( ShortcodeHelper::$tree ) : false;

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

				$out .=		'</div>';
				$out .=	"</div>{$cm} <!-- section close by builder template -->";
			}

			// global fix for https://kriesi.at/support/topic/footer-disseapearing/#post-427764
			if( is_array( $last_el ) && is_string( $last_el['tag'] ) && in_array( $last_el['tag'], AviaBuilder::$full_el_no_section ) )
			{
				avia_sc_section::$close_overlay = '';
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
