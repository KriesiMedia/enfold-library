<?php
/**
 * This class is based on the new ENVATO 3.0 API and handles the automatic theme update
 * 
 * @since 4.4.3
 * @added_by Günter
 * 
 * to debug: set_site_transient('update_themes',null);
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'Avia_Theme_Updater' ) )
{
	class Avia_Theme_Updater 
	{
		/**
		 * Holds the instance of this class
		 * 
		 * @since 4.4.3
		 * @var Avia_Theme_Updater 
		 */
		static private $_instance = null;
		
		/**
		 * Envato author name(s) for the themes/plugins to check
		 * 
		 * @var string|array 
		 */
		protected $authors;
		
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
		 * @var Avia_Envato_Base_API 
		 */
		protected $envato_api;
		
		/**
		 * function wp_update_themes calls filter pre_set_site_transient_update_themes twice during theme version check.
		 * As we have a rate limiting by Envato we cache the result here. We also cache the results in a transient.
		 * 
		 * @since 4.4.3
		 * @var array 
		 */
		protected $envato_results_cache;
		
		/**
		 * Optionname that saves state of last update - especially if there are Rate Limiting errors
		 * 
		 * @since 4.4.3
		 * @var string 
		 */
		protected $transient_logfile_name;
		
		/**
		 * Optionname that saves last update from envato to avoid multiple calls within a given period
		 * 
		 * @since 4.5.2
		 * @var string 
		 */
		protected $transient_cache_name;

		/**
		 * Return the instance of this class
		 * 
		 * @since 4.4.3
		 * @param array $args
		 * @return Avia_Theme_Updater
		 */
		static public function instance( array $args = array() )
		{
			if( is_null( Avia_Theme_Updater::$_instance ) )
			{
				Avia_Theme_Updater::$_instance = new Avia_Theme_Updater( $args );
			}
			
			return Avia_Theme_Updater::$_instance;
		}
		
		/**
		 * @since 4.4.3
		 * @param array $args
		 */
		protected function __construct( array $args ) 
		{
			$this->authors = array();
			$this->personal_token = '';
			$this->envato_api = null;
			$this->envato_results_cache = null;
			$this->transient_logfile_name = '';
			$this->transient_cache_name = '';
			
			$this->init( $args );
			
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'handler_pre_set_site_transient_update_themes' ), 10, 1 );
		}
		
		/**
		 * @since 4.4.3
		 */
		public function __destruct() 
		{		
			unset( $this->authors );
			unset( $this->envato_api );
			unset( $this->envato_results_cache );
		}
		
		/**
		 * Allows a late initialisation of the object
		 * 
		 * @since 4.4.3
		 * @param array $args
		 */
		public function init( array $args ) 
		{
			if( isset( $args['authors'] ) )
			{
				$this->authors = $args['authors'];
			
				if( ! is_array( $this->authors ) )
				{
					$this->authors = array( $this->authors );
				}
			}
			
			$old_token = $this->personal_token;
			
			if( isset( $args['personal_token'] ) )
			{
				$this->personal_token = $args['personal_token'];
			}
	
			if( $old_token != $this->personal_token )
			{
				unset( $this->envato_api );
				$this->envato_api = null;
			}
		}
		
		
		/**
		 * 
		 * @since 4.4.3
		 * @param string $new_token
		 * @return Avia_Envato_Base_API|false
		 */
		protected function get_envato_api( $new_token = '' )
		{
			$token = ( empty( $new_token ) ) ? $this->personal_token : $new_token;
			
			if( empty( $token ) )
			{
				return false;
			}
			
			if( ! class_exists( 'Avia_Envato_Base_API' ) )
			{
				require_once( 'class-avia-envato-base-api.php' );
			}
			
			if( ! empty( $new_token ) )
			{
				return new Avia_Envato_Base_API( $new_token );
			}
			
			if( empty( $this->envato_api ) )
			{
				$this->envato_api = new Avia_Envato_Base_API( $this->personal_token );
			}
			
			return $this->envato_api;
		}


		/**
		 * Checks for theme update and adds theme package to update array
		 * 
		 * @since 4.4.3
		 * @param array $updates
		 * @return array
		 */
		public function handler_pre_set_site_transient_update_themes( $updates )
		{
			/**
			 * Filter for theme updater transient
			 */
			if( ! isset( $updates->checked ) ) 
			{
				return $updates;
			}
			
			$this->authors = apply_filters( 'avf_theme_updater_authors', $this->authors );
			$this->personal_token = apply_filters( 'avf_theme_updater_personal_token', $this->personal_token );

			$api = $this->get_envato_api();
			if( ! $api instanceof Avia_Envato_Base_API )
			{
				/**
				 * We have no Envato token -> clear all saved log data and cache
				 */
				$this->clear_updater_log();
				$this->clear_cache();
				
				return $updates;
			}
			
			if( current_theme_supports( 'avia_envato_extended_log' ) )
			{
				$this->add_to_updater_log( new Avia_Envato_Exception( __( 'Theme update check started', 'avia_framework' ) ) );
			}
			
			/**
			 * If we have already cached the result from Envato we can take this and avoid multiple requests for the same data
			 */
			if( $this->get_cache() )
			{
				foreach( $this->envato_results_cache as $theme => $update ) 
				{
					$updates->response[ $theme ] = $update;
				}
				
				if( current_theme_supports( 'avia_envato_extended_log' ) )
				{
					$this->add_to_updater_log( new Avia_Envato_Exception( __( 'Cache used', 'avia_framework' ) ) );
				}
				
				return $updates;
			}
			
			if( current_theme_supports( 'avia_envato_extended_log' ) )
			{
				$this->add_to_updater_log( new Avia_Envato_Exception( __( 'No cache, Envato API request started', 'avia_framework' ) ) );
			}
			 
			try
			{
				$purchases = $api->get_purchases();
			
				$installed = function_exists( 'wp_get_themes' ) ? wp_get_themes() : get_themes();
				$filtered = array();
			
				/**
				 * Attention:	In case there are multiple installs of the same theme in different folders
				 * =========	only the last folder is kept for update. As this is a rare situation in
				 *				production sites we can ignore this.
				 */
				foreach( $installed as $theme ) 
				{
					if( ! in_array( $theme->{'Author Name'}, $this->authors ) ) 
					{
						continue;
					}

					$filtered[ $theme->Name ] = $theme;
				}
			}
			catch ( Avia_Envato_Exception $ex )
			{
				$this->add_to_updater_log( $api );
				return $updates;
			}
			
			$errors_occured = false;
			$package_errors = array();
			
			foreach( $purchases as $purchase ) 
			{
				$theme_name = isset( $purchase['item']['wordpress_theme_metadata']['theme_name'] ) ? $purchase['item']['wordpress_theme_metadata']['theme_name'] : '';
				if( ! empty( $theme_name ) && isset( $filtered[ $theme_name ] ) ) 
				{
					/**
					 * Found the theme - check if we need to update
					 */
					$current = $filtered[ $theme_name ];
					if( version_compare( $current->Version, $purchase['item']['wordpress_theme_metadata']['version'], '<' ) ) 
					{
						try
						{
							$url = $api->get_wp_download_url( $purchase['item']['id'] );
							$update = array(
											'url'			=> $purchase['item']['url'],
											'new_version'	=> $purchase['item']['wordpress_theme_metadata']['version'],
											'package'		=> $url
										);
											
							$updates->response[ $current->Stylesheet ] = $update;
							$this->add_to_cache( $current->Stylesheet, $update );
							
							if( current_theme_supports( 'avia_envato_extended_log' ) )
							{
								$this->add_to_updater_log( new Avia_Envato_Exception( sprintf( __( 'Successfull download package for %s - %s', 'avia_framework' ), $current->Stylesheet, $update['new_version'] ) ) );
							}
						} 
						catch( Avia_Envato_Exception $ex ) 
						{
							$errors_occured = true;
							$package_errors[] = $current->Name . ' - ' . $purchase['item']['wordpress_theme_metadata']['version'];
							continue;
						}
					}
				}
			}
			
			/**
			 * In case of an error we should try again to get the results.
			 * As we had troubles with too many requests we keep what we have and try again
			 * when cache is expired.
			 */
			if( $errors_occured )
			{
//				$this->clear_local_cache();
			}
			
			$this->update_cache();
			
			$this->add_to_updater_log( $api, $package_errors );
			return $updates;
		}
		
		/**
		 * Output the HTML below the verify input field
		 * Keep backwards comp with old API - but do not allow to enter new values
		 * 
		 * @since 4.4.3
		 * @param string $new_token
		 * @param boolean $ajax
		 * @return string
		 */
		public function backend_html( $new_token, $ajax )
		{
			$new_token = trim( $new_token );
			
			$data = array(
					'updates_envato_token'			=> trim( avia_get_option( 'updates_envato_token' ) ),
					'updates_envato_token_state'	=> trim( avia_get_option( 'updates_envato_token_state' ) ),
					'updates_username'				=> trim( avia_get_option( 'updates_username' ) ),
					'updates_api_key'				=> trim( avia_get_option( 'updates_api_key' ) ),
					'updates_envato_info'			=> trim( avia_get_option( 'updates_envato_info' ) )
				);
			
			if( $ajax )
			{
				$data = $this->verify_token( $data, $new_token );
			}
			
			$notice = '';
			$deprecated = '';
			
			if( ! empty( $new_token ) )
			{
				$default = array(
								'purchases'	=> '',
								'username'	=> '',
								'email'		=> '',
								'errors'	=> ''
							);
					
				$arr_info = json_decode( $data['updates_envato_info'] );
				$arr_info = wp_parse_args( $arr_info, $default );


				$purchases = ! empty( $arr_info['purchases'] ) ? __( 'Your purchases', 'avia_framework' ) : __( 'Purchases could not be accessed', 'avia_framework' );
				$username = ! empty( $arr_info['username'] ) ? __( 'Your username: ', 'avia_framework' ) . $arr_info['username'] : __( 'Username could not be accessed (needed for your information only)', 'avia_framework' );
				$email = ! empty( $arr_info['email'] ) ? __( 'Your E-Mail: ', 'avia_framework' ) . $arr_info['email']  : __( 'E-Mail could not be accessed (needed for your information only)', 'avia_framework' );
							
				$error_msg = '';
				if( ! empty( $arr_info['errors'] ) )
				{
					$error_msg .=	'<p>';
					$error_msg .=		__( 'Following errors occured:', 'avia_framework' );
					$error_msg .=	'</p>';
					$error_msg .=	'<ul>';
					foreach ( $arr_info['errors'] as $value ) 
					{
						$error_msg .=	'<li>' . $value . '</li>';
					}
					$error_msg .=	'</ul>';
				}
					
				if( ! empty( $data['updates_envato_token_state'] ) )
				{
					$notice .=	'<div class="av-text-notice">';
					$notice .=		'<p>';
					$notice .=			sprintf( __( 'We checked the token on %s and we were able to connect to Envato and could access the following information:', 'avia_framework' ), $data['updates_envato_token_state'] ); 
					$notice .=		'</p>';
					$notice .=		'<ul>';
					$notice .=			'<li>' . $purchases . '</li>';
					$notice .=			'<li>' . $username . '</li>';
					$notice .=			'<li>' . $email . '</li>';
					$notice .=		'</ul>';
					$notice .=		$error_msg;
					$notice .=	'</div>';
					
					$notice .=	'<div class="av-verification-cell av-privacy-token-notice">';
					$notice .=		__( 'If you ever edit the restrictions of your personal token please re-vailidate it again to test if it works properly', 'avia_framework' );
					$notice .=	'</div>';
				}
				else
				{
					$notice .=	'<div class="av-text-notice av-notice-error">';
					$notice .=		'<p>';
					$notice .=			sprintf( __( 'Last time we checked the token we were not able to connected to Envato:', 'avia_framework' ), $data['updates_envato_token_state'] ); 
					$notice .=		'</p>';
					$notice .=		'<ul>';
					$notice .=			'<li>' . $purchases . '</li>';
					$notice .=			'<li>' . $username . '</li>';
					$notice .=			'<li>' . $email . '</li>';
					$notice .=		'</ul>';
					$notice .=		$error_msg;
					$notice .=	'</div>';
				}
			}
			
			/**
			 * Backwards compatibility (can be removed in future when Envato API < 3.0 is deprecated):
			 * 
			 * Add a message to switch to new API and show old API access info.
			 * 
			 * @since 4.4.3
			 */
			if( empty( $new_token ) && ! empty( $data['updates_username'] ) && ! empty( $data['updates_api_key'] ) )
			{
				$old_api = '';
				$info = '';
			
				$old_api .=		'<p class="av-text-notice av-notice-error av-notice-noborder">';
				$old_api .=			__( 'Attention: The old Envato API is deprecated and will be shut down soon. In order to be able to use automated theme updates please  generate a new valid API token and enter it above. Your themeforest username and your old API key will then be removed from your installation since they are no longer required.', 'avia_framework' );
				$old_api .=		'</p>';

				$info .=	'<div class="avia_section avia_text">';
				$info .=		'<h4>' . __( 'Your Themeforest User Name:', 'avia_framework' ) . '</h4>';
				$info .=		'<div class="avia_control_container">';
				$info .=			'<div class="avia_control">';
				$info .=				'<div class="avia_style_wrap">';
				$info .=					'<input class="" value="' . $data['updates_username'] . '" readonly="readonly" type="text">';
				$info .=				'</div>';
				$info .=			'</div>';
				$info .=		'</div>';
				$info .=	'</div>';

				$info .=	'<div class="avia_section avia_text">';
				$info .=		'<h4>' . __( 'Your Themeforest API Key', 'avia_framework' ) . '</h4>';
				$info .=		'<div class="avia_control_container">';
				$info .=			'<div class="avia_control">';
				$info .=				'<div class="avia_style_wrap">';
				$info .=					'<input class="" value="' . $data['updates_api_key'] . '" readonly="readonly" type="text">';
				$info .=				'</div>';
				$info .=			'</div>';
				$info .=		'</div>';
				$info .=	'</div>';
				
				
				$deprecated .=	'<div class="avia_section avia_envato_deprecated-section">';
				$deprecated .=		$old_api;
				$deprecated .=		$info;
				$deprecated .=	'</div>';
			}
			
			$output  = 'avia_trigger_save ';
			$output .=	'<div class="av-verification-response-wrapper">';
			$output .=		$notice;
			$output .=		$deprecated;
			$output .=	'</div>';
			
			if( $ajax )
			{
				$response['html'] = $output;
				unset( $data['updates_envato_token'] );
				$response['update_input_fields'] = $data;
			}
			else
			{
				$response = $output;
			}
			
			return $response;
		}
	
		/**
		 * Check the given token with Envato API. Tries to access:
		 *		- purchases
		 *		- username
		 *		- email
		 * 
		 * @since 4.4.3
		 * @param array $data
		 * @param string $new_token
		 * @return array
		 */
		protected function verify_token( array $data, $new_token )
		{
			$old_token = $data['updates_envato_token'];
			
			if( '' == $new_token )
			{
				$this->clear_updater_log();
				
				$data['updates_envato_token_state'] = '';
				$data['updates_envato_info'] = '';
				return $data;
			}
			
			if( $old_token != $new_token )
			{
				$this->clear_updater_log();
			}
			
			$api = $this->get_envato_api( $new_token );
			
			$info = array();
			
			try
			{
				$purchases = $api->get_purchases();
				$info['purchases'] = 'success';
			}
			catch ( Avia_Envato_Exception $ex )
			{
				$info['purchases'] = '';
			}
			
			try
			{
				$info['username'] = $api->get_userdata( 'username' );
			}
			catch ( Avia_Envato_Exception $ex )
			{
				$info['username'] = '';
			}
			
			try
			{
				$info['email'] = $api->get_userdata( 'email' );
			}
			catch ( Avia_Envato_Exception $ex )
			{
				$info['email'] = '';
			}
			
			$errors = $api->get_errors();
			if( $errors instanceof WP_Error )
			{
				$info['errors'] = $errors->get_error_messages();
			}
			
			$data['updates_envato_token'] = $new_token;
			$data['updates_envato_token_state'] = ! empty( $info['purchases'] ) ? date( 'Y/m/d H:i' ) : '';
			$data['updates_envato_info']  = json_encode( $info );
			
			/**
			 * Remove deprecated data
			 */
			$data['updates_username'] = '';
			$data['updates_api_key'] = '';
			
			return $data;
		}
		
		
		/**
		 * Returns the transient logfile name depending on theme name
		 * 
		 * @since 4.4.3
		 * @return string
		 */
		public function get_logfile_name()
		{
			if( empty( $this->transient_logfile_name ) )
			{
				$this->transient_logfile_name = apply_filters( 'avf_theme_updater_transient_logfile_name', '_av_' . avia_auto_updates::get_themename( 'parent' ) . '_updater_log' );
			}
			
			return Avia_Theme_Updater::validate_transient( $this->transient_logfile_name );
		}
		
		/**
		 * Returns the transient logfile name depending on theme name
		 * 
		 * @since 4.5.2
		 * @return string
		 */
		public function get_cache_name()
		{
			if( empty( $this->transient_cache_name ) )
			{
				$this->transient_cache_name = apply_filters( 'avf_theme_updater_transient_cache_name', '_av_' . avia_auto_updates::get_themename( 'parent' ) . '_updater_cache' );
			}
			
			return Avia_Theme_Updater::validate_transient( $this->transient_cache_name );
		}
		
		
		/**
		 * Tries to read cache from transient and stores it in local array
		 * 
		 * @since 4.5.2
		 * @return boolean				true, if cache exists
		 */
		protected function get_cache()
		{
			static $force_check_executed = false;
			
			if( is_array( $this->envato_results_cache ) )
			{
				return true;
			}
			
			/**
			 * From theme option page we want to force a check
			 */
			if( isset( $_REQUEST['force-check'] ) && ! $force_check_executed )
			{
				$force_check_executed = true;
				return false;
			}
			
			$transient = $this->get_cache_name();
			$cache = get_transient( $transient );
			
			if( false === $cache || ! is_array( $cache ) )
			{
				return false;
			}
			
			$this->envato_results_cache = $cache;
			return true;
		}
		
		/**
		 * Add update info to internal cache array
		 * 
		 * @since 4.5.2
		 * @param string $theme_name
		 * @param array $update
		 */
		protected function add_to_cache( $theme_name, $update )
		{
			if( ! is_array( $this->envato_results_cache ) )
			{
				$this->envato_results_cache = array();
			}
			
			$this->envato_results_cache[ $theme_name ] = $update;
		}
		
		/**
		 * Clears the local cache
		 * 
		 * @since 4.5.2
		 */
		protected function clear_local_cache()
		{
			unset( $this->envato_results_cache );
			$this->envato_results_cache = null;
		}
		
		/**
		 * Saves cache to transient
		 * 
		 * @since 4.5.2
		 * @param boolean $set_locale
		 */
		protected function update_cache( $set_locale = false )
		{
			/**
			 * We block update requests in any case to limit Envato API calls for download URL's !!
			 */
			$cache = is_array( $this->envato_results_cache ) ? $this->envato_results_cache : array();
			
			if( $set_locale )
			{
				$this->envato_results_cache = $cache;
			}
			
			$transient = $this->get_cache_name();
			$timeout = apply_filters( 'avf_updater_cache_timeout', 12 * HOUR_IN_SECONDS );
			
			return set_transient( $transient, $cache, $timeout );
		}
		
		/**
		 * Clears local and saved cache
		 * 
		 * @since 4.5.2
		 */
		protected function clear_cache()
		{
			$this->clear_local_cache();
			$this->update_cache();
		}

		/**
		 * Returns the stored transient or an empty array
		 * 
		 * @since 4.4.3
		 * @return array
		 */
		public function get_updater_log()
		{
			$transient = $this->get_logfile_name();
			$log = get_transient( $transient );
			return ( false !== $log ) ? $log : array();
		}
		
		/**
		 * Updates the transient
		 * 
		 * @since 4.4.3
		 * @param array $log
		 * @return boolean
		 */
		protected function update_updater_log( array $log )
		{
			$transient = $this->get_logfile_name();
			$timeout = apply_filters( 'avf_updater_log_timeout', MONTH_IN_SECONDS );
			
			return set_transient( $transient, $log, $timeout );
		}

		/**
		 * Reset transient to an empty array
		 * 
		 * @since 4.4.3
		 * @return boolean
		 */
		protected function clear_updater_log()
		{
			return $this->update_updater_log( array() );
		}
				
		
		/**
		 * Adds an update message to the queue and removes the oldest if necessary
		 * 
		 * @since 4.4.3
		 * @param Avia_Envato_Base_API|Avia_Envato_Exception  $info
		 * @param array $package_errors
		 * @param string $clear_errors									'clear_errors' | 'no_clear_errors'
		 * @return boolean
		 */
		protected function add_to_updater_log( $info, $package_errors = null, $clear_errors = 'clear_errors' )
		{
			$log = $this->get_updater_log();
			
			$entries = ! current_theme_supports( 'avia_envato_extended_log' ) ? 20 : 500;
			$max_entries = apply_filters( 'avf_updater_log_max_entries', $entries );
			
			if( count( $log ) >= $max_entries )
			{
				$log = array_slice( $log, count( $log ) - $max_entries + 1 );
			}
			
			if( $info instanceof Avia_Envato_Base_API )
			{
				$entry = array(
								'time'		=> date( 'Y/m/d H:i' ),
								'errors'	=> array(),
							);
				
				$errors = $info->get_errors();
				if( $errors instanceof WP_Error )
				{
					$entry['errors'] = $errors->get_error_messages();
				}
				
				if( is_array( $package_errors ) )
				{
					$entry['package_errors'] = trim( implode( ', ', $package_errors ) );
				}
				
				$log[] = $entry;

				if( 'clear_errors' == $clear_errors )
				{
					$info->clear_errors();
				}
			}
			else if( $info instanceof Avia_Envato_Exception )
			{
				$log[] = array(
							'time'		=> date( 'Y/m/d H:i' ),
							'info'		=> $info->getMessage()
						);
			}
			
			return $this->update_updater_log( $log );
		}
		
		/**
		 * Helper function to validate transient ID's.
		 * 
		 * @since 4.4.3
		 * @param string $transient
		 * @return string
		 */
		static public function validate_transient( $transient = '' ) 
		{
		  return preg_replace( '/[^A-Za-z0-9\_\-]/i', '', str_replace( ':', '_', $transient ) );
		}
	
	}

	/**
	 * Get the only instance of this class
	 * 
	 * @since 4.4.3
	 * @return Avia_Theme_Updater
	 */
	function AviaThemeUpdater( array $args = array() )
	{
		return Avia_Theme_Updater::instance( $args );
	}
}

if( ! class_exists( 'Avia_Envato_Exception' ) )
{
	/**
	 * Simple base class to allow use of try / catch blocks
	 * 
	 * @since 4.4.3
	 */
	class Avia_Envato_Exception extends Exception 
	{
		
		/**
		 * 
		 * @since 4.4.3
		 * @param string $message
		 * @param int $code
		 * @param \Throwable $previous
		 */
		public function __construct( $message = "", $code = 0, $previous = null ) 
		{
			parent::__construct( $message, $code, $previous );
		}
		
	}
}


