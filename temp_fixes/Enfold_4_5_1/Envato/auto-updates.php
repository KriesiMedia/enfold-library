<?php 
/**
 * Base class for theme updates
 * 
 * @since 4.4.3 - supports the new Envato API 3.0
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( 'avia_auto_updates' ) )
{

	if( ! current_theme_supports( 'avia_manual_updates_only' ) )
	{
		add_action( 'admin_init', array( 'avia_auto_updates', 'init' ), 1 );
		
		//since the avia framework is not included via hook there need to be some static functions since at the time of admin_init those hooks are already executed
		add_action( 'avf_option_page_init', array( 'avia_auto_updates', 'add_updates_tab' ), 1, 1 ); 
		add_action( 'avf_option_page_data_init', array( 'avia_auto_updates', 'option_page_data' ), 10, 1 );
	}
	
	class avia_auto_updates
	{
		/**
		 * Envato author name for the theme/plugin
		 * 
		 * @var string 
		 */
		protected $author;
		
		/**
		 * Envato user name (will be deprecated with API 3.0)
		 * 
		 * @var string 
		 */
		protected $username;
		
		/**
		 * Envato API key for the theme (will be deprecated with API 3.0)
		 * @var string 
		 */
		protected $apikey;
		
		/**
		 * Envato Personal Token Key
		 * 
		 * @since 4.4.3
		 * @var string 
		 */
		protected $personal_token;
		
		/**
		 *
		 * @since 4.4.3
		 * @var string			'' | timestamp of last check 
		 */
		protected $envato_token_state;

		/**
		 * Current theme name
		 * 
		 * @var string 
		 */
		protected $themename;
		
		
		/**
		 * @since < 4.4.3
		 */
		public function __construct()
		{
			/**
			 * will become deprecated and can be removed in future
			 */
			$this->username 	= trim( avia_get_option( 'updates_username' ) );
			$this->apikey		= trim( avia_get_option( 'updates_api_key' ) );
			
			$this->author 		= "Kriesi";
			$this->personal_token = trim( avia_get_option( 'updates_envato_token' ) );	
			$this->envato_token_state = trim( avia_get_option( 'updates_envato_token_state' ) );
			
			$this->themename 	= self::get_themename();
			$this->includes();
			$this->hooks();
		}
		
		/**
		 * @since < 4.4.3
		 */
		protected function hooks()
		{
			add_action( 'update_bulk_theme_complete_actions', array( $this, 'update_complete' ), 10, 2 );	
			add_action( 'upgrader_process_complete', array( $this,'re_insert_custom_css' ) );
			add_action( 'load-update.php', array( $this, 'temp_save_custom_css' ), 20 );
			
			$this->temp_save_custom_css();
		}
				
		/**
		 * Include classes. We stay backwards compatible with the old API but request users to enter the personal token
		 * 
		 * @since < 4.4.3
		 */
		protected function includes()
		{
			require_once( 'class-avia-theme-updater.php' );
			
			$args = array(
							'authors'			=> $this->author,
							'personal_token'	=> ! empty( $this->envato_token_state ) ? $this->personal_token : '',
						);
			AviaThemeUpdater( $args );
				
			if( empty( $this->personal_token ) && ! empty( $this->username ) && ! empty( $this->apikey ) )
			{
				/**
				 * backwards comp. for old API - can be removed in some future 
				 * support for new API added in 4.4.3
				 */
				require_once( "class-pixelentity-theme-update.php" );
				PixelentityThemeUpdate::init( $this->username , $this->apikey, $this->author );
			}
		}
		
		/**
		 * 
		 * @since < 4.4.3
		 * @param array $updates
		 * @param WP_Theme $info
		 * @return array
		 */
		public function update_complete( $updates, $info )
		{
			if(strtolower( $info->get('Name') ) == strtolower( $this->themename ) )
			{
				$updates = array('theme_updates' => '<a target="_parent" href="'.admin_url('admin.php?page=avia').'">Go Back to '.THEMENAME.' Theme Panel</a>');
			}
			return $updates;
		}
		
		/**
		 * 
		 * @since < 4.4.3
		 */
		public function re_insert_custom_css()
		{
			if(isset($this->custom_css_md5) && $this->custom_css_md5 == "1877fc72c3a2a4e3f1299ccdb16d0513") return;
			
			if(isset($this->custom_css))
			{
				$self_update = "<strong>Attention:</strong> We detected some custom styling rules in your custom.css file but could not restore it. Please open the file yourself and add the following content:<br/>
			    		  <textarea class='avia-custom-rules' style='width:90%; min-height:200px;'>".$this->custom_css_content."</textarea>";
			    
			    if (is_writeable($this->custom_css))
			    {	  
					$handle = @fopen( $this->custom_css, 'w' );
					
					if ($handle && fwrite($handle, $this->custom_css_content)) {
				        echo "<strong>Attention:</strong> We detected some custom styling rules in your custom.css file and restored it ;)";
				    }
				    else
				    {
				    	echo $self_update;
				    }
			    }
			    else
			    {
			    	echo $self_update;
			    }
				
			}
			
		}
		
		/**
		 * 
		 * @since < 4.4.3
		 */
		public function temp_save_custom_css()
		{
			if(empty($_GET['themes']) || $_GET['themes'] != strtolower( $this->themename ) ) return;
		
			$css_path = AVIA_BASE.'css/custom.css';
		
			if(file_exists($css_path) && is_readable($css_path))
			{
				$size = filesize($css_path);
				if($size > 0)
				{
					$handle = @fopen( $css_path, 'r' );
				    if ($handle)
				    {
				    	$this->custom_css_content = fread($handle, $size);
				    	$this->custom_css_md5 = md5($this->custom_css_content);
				    	$this->custom_css = $css_path;
				    	fclose($handle);
				    }
				}
			}
		}
		
		/**
		 * 
		 * @since < 4.4.3
		 * @param array $avia_pages
		 * @return array
		 */
		public static function add_updates_tab( $avia_pages )
		{
			$title = __("Theme Update",'avia_framework');
			if( false !== self::check_for_theme_update() ) 
			{
				$title .= "<span class='avia-update-count'>1</span>"; 
				add_filter('avia_filter_backend_menu_title', array('avia_auto_updates','sidebar_menu_title'));
			}
			$avia_pages[] = apply_filters('avf_update_theme_tab', array( 'slug' => 'update', 'parent'=>'avia', 'icon'=>"update.png", 'title' =>  $title ));
			
			return $avia_pages;
		}
		
		/**
		 * 
		 * @param string $title
		 * @return string
		 */
		public static function sidebar_menu_title( $title )
		{
			$title .= '<span class="update-plugins count-1"><span class="plugin-count">1</span></span>';
			return $title;
		}
		
		/**
		 * 
		 * @since < 4.4.3
		 * @return array|false
		 */
		public static function check_for_theme_update()
		{
			$updates = get_site_transient('update_themes');
			$version = self::get_version();
			
			if( ! empty( $updates ) && ! empty( $updates->response ) )
			{
				$theme = wp_get_theme();
				$name = $theme->get_template();
						
				if( array_key_exists( $name, $updates->response ) )
				{
					if( version_compare( $updates->response[ $name ]['new_version'], $version, '!=' ) )
					{		
						return $updates->response[ $name ];
					}
				}
			}
			
			return false;
		}
		
		/**
		 * 
		 * @since < 4.4.3
		 * @param array $avia_elements
		 * @return array
		 */
		public static function option_page_data( $avia_elements )
		{
			$desc = __( "If you want to get update notifications for your theme and if you want to be able to update your theme from your WordPress backend you need to enter your Envato Private Token below.", 'avia_framework' );
			$desc .= '<br /><br />';
			$desc .= sprintf( __( 'A detailed description for generating this token can be found %s here %s', 'avia_framework' ), '<a href="https://kriesi.at/documentation/enfold/how-to-install-enfold-theme/#theme-registration" target="_blank">', '</a>' );
			
			$avia_elements[] = array(	
						"name"			=> __( "Update your Theme from the WordPress Dashboard", 'avia_framework' ),
						"desc"			=> $desc,
						"std"			=> "",
						"slug"			=> "update",
						"type"			=> "heading",
						"nodescription"	=> true
				);
								
			
			$avia_elements[] =	array(
						"slug"				=> "update",
						"name"				=> __( "Enter a valid Envato private token", 'avia_framework' ),
						"desc"				=> "",
						"id"				=> "updates_envato_token",
						"type"				=> "verification_field",
						"ajax"				=> "av_envato_token_check",
						"class"				=> "av_full_description",
						"button-label"		=> __( 'Check the private token', 'avia_framework' ),
						"button-relabel"	=> __( 'Revalidate or remove the token', 'avia_framework' ),
						"std"				=> '',
						'force_callback'	=> true
					);
			
			$avia_elements[] =	array(	
						"slug"			=> "update",
						"std"			=> "",
						"name"			=> __( "Last verify state - hidden - used for internal use only", 'avia_framework' ),
						"desc"			=> '',
						"id"			=> "updates_envato_token_state",
						"type"			=> "hidden",
//						'readonly'		=> true
					);
			
			/**
			 * deprecated - can be removed in future releases
			 */
			$avia_elements[] =	array(	
						"slug"			=> "update",
						"std"			=> "",
						"name"			=> __( "Your Themeforest User Name (will be deprecated and removed in future)  - hidden", 'avia_framework' ),
						"desc"			=> '',
						"id"			=> "updates_username",
						"type"			=> "hidden",
//						'readonly'		=> true
					);
						
			/**
			 * deprecated - can be removed in future releases
			 */
			$avia_elements[] =	array(	
						"slug"			=> "update",
						"std"			=> "",
						"name"			=> __( "Your Themeforest API Key (will be deprecated and removed in future)  - hidden", 'avia_framework' ),
//						"desc"			=> "Enter the API Key of your Account here. <br/>You can <a target='_blank' href='".AVIA_IMG_URL."layout/FIND_API.jpg'>find your API Key here</a>",
						"desc"			=> '',
						"id"			=> "updates_api_key",
						"type"			=> "hidden",
//						'readonly'		=> true
					);
			
			$avia_elements[] =	array(	
						"slug"			=> "update",
						"std"			=> "",
						"name"			=> __( "Envato Reply - hidden - used for internal use only", 'avia_framework' ),
						"desc"			=> '',
						"id"			=> "updates_envato_info",
						"type"			=> "hidden",
//						'readonly'		=> true
					);
			
			$avia_elements[] =	array(	
						"slug"			=> "update",
						"std"			=> "",
						"name"			=> "",
						"desc"			=> false,
						"id"			=> "update_notification",
						"use_function" 	=> true,
						"type"			=> "avia_backend_display_update_notification"
					);				
		
			return $avia_elements;
		}
		
		
		/**
		 * @since < 4.4.3
		 * @return string
		 */
		public static function backend_html()
		{
			/**
			 * will be deprecated in future
			 */
			$username 	= trim(avia_get_option('updates_username'));
			$apikey		= trim(avia_get_option('updates_api_key'));
			$old_keys_valid	= ! empty( $username ) && ! empty( $apikey );
			
			$updates_envato_token = trim( avia_get_option( 'updates_envato_token' ) );
			$updates_envato_token_state	= trim( avia_get_option( 'updates_envato_token_state' ) );
			$keys_valid = ! empty( $updates_envato_token ) && ! empty( $updates_envato_token_state );
			
			
			$output 	= '';
			$version 	= self::get_version();
			$themename 	= self::get_themename();
			$parent_string = is_child_theme() ? "Parent Theme (". ucfirst( $themename ).")" : ucfirst( $themename )." Theme";
			
			$update = self::check_for_theme_update();
					
			if( ( ! $keys_valid )  && ( ! $old_keys_valid ) )
			{
				$output .=	"<div class='avia_backend_theme_updates'>";
				$output .=		"<h3>" . __( 'Theme Updates', 'avia_framework' ) . "</h3>";
				$output .=		sprintf( __( "Once you have entered and verified your Envato Personal Token Key WordPress will check for updates every 12 Hours and notify you here, if one is available <br/><br/> Your current %s Version Number is <strong>%s</strong>", '' ), $parent_string, $version );

				if( false !== $update )
				{
					$output .= ' - ' . sprintf( __( 'a new version %s is available.', 'avia_framework' ), $update['new_version'] );
				}
				$output .=	'</div>';
			}
			else if( false !== $update )
			{
				$target  	= network_admin_url('update-core.php?action=do-theme-upgrade');
				$new		= $update['new_version'];
				
				ob_start();
				wp_nonce_field('upgrade-core');
				$nonce = ob_get_clean();
				
				
				
				$output .=	"<div class='avia_backend_theme_updates'>";
				$output .=		"<h3>" . __( 'Update Available!', 'avia_framework' ) . "</h3>";
				
				$output .=		sprintf( __( "A new Version (%s) of your %s is available! You are using Version %s. <br/>See what's new in <a href='https://kriesi.at/documentation/enfold/enfold-changelog/' target='_blank'>change log</a>. Do you want to update?<br/><br/>", '' ), $new, $parent_string, $version );
				//$output .= "";
				$output .=		'<span class="avia_style_wrap">';
				$output .=			'<a href="#" data-avia-popup="avia-tmpl-theme-update" class="avia_button">' . __( 'Update Now!', 'avia_framework' ) . '</a>';
				$output .=		'</span>';
				$output .=	'</div>';
				
				$form = '<form method="post" action="'.$target.'" name="upgrade-themes" class="upgrade">
								<input type="hidden" name="checked[]" value="'.$themename.'" />
								'.$nonce.'
								<input type="hidden" name="_wp_http_referer" value="/wp-admin/update-core.php?action=do-theme-upgrade" />
								<p>
									<strong>Attention: Any modifications made to the <u>Theme Files</u> will be lost when updating. If you did change any files (Custom CSS rules or PHP file modifications for example) make sure to create a theme backup.</strong><br/><br/>Your backend settings, posts and pages wont be affected by the update.<br/>
								</p>
								<p class="avia-popup-button-container">
									<input id="upgrade-themes" class="button" type="submit" value="Update Theme" name="upgrade"/>
									<input id="upgrade-themes-close" class="button button-2nd script-close-avia-popup" type="submit" value="Don\'t Update" name="close"/>
								</p>
							</form>';
				
				$output .= "<script type='text/html' id='avia-tmpl-theme-update'>\n{$form}\n</script>\n\n";	
			}
			else
			{
				$target  	= network_admin_url('update-core.php?force-check=1');
			
				$output .=	"<div class='avia_backend_theme_updates'>";
				$output .=		"<h3>" . __( 'Theme Updates', 'avia_framework' ) . "</h3>";
				$output .=		sprintf( __( "No Updates available. You are running the latest version! (%s)", 'avia_framework' ), $version );
				$output .=		"<br/><br/> <a href='{$target}'>" . __( 'Check Manually', 'avia_framework' ) . "</a>";
				$output .=	'</div>';
			}
			
			if( empty( $updates_envato_token ) )
			{
				return $output;
			}
			
			$log = AviaThemeUpdater()->get_updater_log();
			
			$has_errors = false;
			$last_package = null;
			
			foreach( $log as $entry ) 
			{
				if( ! empty( $entry['errors'] ) )
				{
					$has_errors = true;
				}
				
				if( isset( $entry['package_errors'] ) )
				{
					$last_package = $entry;
				}
			}
			
			/**
			 * Enable WP_DEBUG or theme support to show complete log for debugging purpose
			 */
			$show_all_entries = ( defined('WP_DEBUG') &&  WP_DEBUG ) || current_theme_supports( 'avia_envato_extended_log' );
			
			
			if( empty( $log ) || ( ! $has_errors && ! $show_all_entries ) )
			{
				$output  .=	"<div class='avia_backend_theme_updates_log av-updates-sucessful'>";
				if( empty( $log ) )
				{
					$output .=		__( 'There has been no check within the last month.', 'avia_framework' );
				}
				else
				{
					$output .=		sprintf( __( 'Last sucessful check was on %s.', 'avia_framework' ), $entry['time'] );
				}
				
				$output .=	'</div>';
				return $output;
			}
		
			
			$class = $has_errors ? 'av-updates-error' : 'av-updates-sucessful';
			$output  .=	"<div class='avia_backend_theme_updates_log {$class}'>";
			
			$show_entries = $show_all_entries ? count( $log ) : 1;
			$show_entries = apply_filters( 'avf_updater_show_entries', $show_entries, count( $log ) );
			
			$cut_log = ( count( $log ) > $show_entries ) ? array_slice( $log, - $show_entries ) : $log;
			
/*
			if( defined('WP_DEBUG') &&  WP_DEBUG )
			{
				$output  .=	'<div class="avia_log_line avia_log_line_debug_mode">';
				$output .=		__( 'Only in debug mode all log entries are shown. You can use filter avf_updater_show_entries if you want to show more than the last one only on production sites.', 'avia_framework' );
				$output .=	'</div>';
			}
*/
			
			foreach( $cut_log as $entry ) 
			{
				if( ! empty( $entry['errors'] ) )
				{
					$output  .=	'<div class="avia_log_line avia_log_line_error">';
					$output .=		sprintf( __( 'Errors occured checking on %s:', 'avia_framework' ), $entry['time'] );
					$output .=		'<ul>';
					foreach ( $entry['errors'] as $value ) 
					{
						$output .=		'<li>' . $value . '</li>';
					}
					$output .=		'</ul>';
					$output .=	'</div>';
				}
				else if( ! empty( $entry['info'] ) )
				{
					$output  .=	'<div class="avia_log_line avia_log_line_success">';
					$output .=		sprintf( __( 'Info - %s:', 'avia_framework' ), $entry['time'] ) . ' ' . $entry['info'];
					$output .=	'</div>';
				}
				else
				{
					$output  .=	'<div class="avia_log_line avia_log_line_success">';
					$output .=		sprintf( __( 'Sucessful check on %s.', 'avia_framework' ), $entry['time'] );
					$output .=	'</div>';
				}
			}
			
			/**
			 * Always show packages that have returned an error in last update check
			 */
			if( is_array( $last_package ) && ! empty( $last_package['package_errors'] ) )
			{
				$output	.=	'<div class="avia_log_line avia_log_line_error">';
				$output .=		sprintf( __( 'Errors occured for package(s): %s', 'avia_framework' ), $last_package['package_errors'] );
				$output .=	'</div>';
			}
			
/*
			if( count( $log ) != $show_errors )
			{
				$left = count( $log ) - $show_errors;
				$output  .=	'<div class="avia_log_line avia_log_line_error">';
				$output .=		sprintf( __( 'There are %d older log entrie(s). If you want to see more use filter avf_updater_show_errors (or set WP_DEBUG to true).', 'avia_framework' ), $left );
				$output .=	'</div>';
			}
*/
			
			$output .=	'</div>';
			
			return $output;
		}
		
		/**
		 * Returns theme name depending on $which.
		 * Defaults to child theme name if activated
		 * 
		 * @since < 4.4.3
		 * @param string $which				'parent' | 'child'
		 * @return string
		 */
		public static function get_themename( $which = 'child' )
		{
			$theme = wp_get_theme();
			
			if( is_child_theme() && ( 'child' == $which ) )
			{
				$theme = wp_get_theme( $theme->get('Template') );
			}
			
			return $theme->get_template();
		}
		
		/**
		 * @since < 4.4.3
		 * @return string
		 */
		public static function get_version()
		{
			$theme = wp_get_theme();
			
			if(is_child_theme())
			{
				$theme = wp_get_theme( $theme->get('Template') );
			}
			
			return $theme->get('Version');
		}
		
		
		/**
		 * 
		 */
		public static function init() 
		{
			new avia_auto_updates();
		}
	}

}


if( ! function_exists( 'avia_backend_display_update_notification' ) )
{
	/**
	 * wrapper function so that the html helper class can use the auto update class
	 * 
	 * @since < 4.4.3
	 * @return string
	 */
	function avia_backend_display_update_notification()
	{
		return avia_auto_updates::backend_html();
	}
}


if( ! function_exists( 'av_envato_token_check' ) )
{
	/**
	 * Callback function:
	 *		- ajax callback from verification button
	 *		- php callback when creating output on option page
	 * 
	 * @since 4.4.3
	 * @param string $value
	 * @param boolean $ajax
	 * @return string
	 */
	function av_envato_token_check( $value, $ajax = true )
	{
		$api = AviaThemeUpdater();
		return $api->backend_html( $value, $ajax );
	}

}