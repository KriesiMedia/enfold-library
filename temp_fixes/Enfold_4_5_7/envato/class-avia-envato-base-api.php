<?php
/**
 * This class is based on the new ENVATO 3.0 API.
 * The current implementation only supports personal token - might be necessary to extend in future releases.
 * Based on the class Avia_Envato_API of plugin "Avia Support Envato Extension" written by Günter for kriesi.at
 * 
 * @since 4.4.3
 * @added_by Günter
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'Avia_Envato_Base_API' ) )
{
	class Avia_Envato_Base_API 
	{
		const PERSONAL_TOKEN_TYPE =		'Bearer';
		
		/**
		 *
		 * @since 4.4.3
		 * @var string 
		 */
		protected $personal_token;
		
		/**
		 *
		 * @since 4.5.4
		 * @var string 
		 */
		protected $envato_theme_name;
		
		/**
		 *
		 * @since 4.5.4
		 * @var string 
		 */
		protected $theme_version;

		/**
		 * Default array with HTTP values for wp_remote_get
		 * 
		 * @since 4.4.3
		 * @var array 
		 */
		protected $default_http;
	
		/**
		 *
		 * @since 4.4.3
		 * @var WP_Error 
		 */
		protected $errors;
		
		/**
		 * Flag if transient cache should be used
		 * Can be filtered in set_cache and get_cache and changed in function set_cache_params
		 * 
		 * @since 4.4.3
		 * @var boolean 
		 */
		protected $cache;

		
		/**
		 * Timeout for transient cache in seconds (300 = 5 minutes)
		 * Can be filtered in set_cache and changed in function set_cache_params
		 * 
		 * @since 4.4.3
		 * @var int 
		 */
		protected $cache_timeout;

		
		/**
		 * 
		 * @since 4.4.3
		 * @param string $personal_token
		 * @param string $theme_name
		 * @param string $theme_version
		 */
		public function __construct( $personal_token = '', $theme_name = '', $theme_version = '' ) 
		{
			$this->personal_token = $personal_token;
			$this->envato_theme_name = $theme_name;
			$this->theme_version = $theme_version;
			$this->default_http = array();
			$this->errors = new WP_Error();
			
			$this->cache = true;
			$this->cache_timeout = 300;
		}
		
		/**
		 * @since 4.4.3
		 */
		public function __destruct() 
		{
			unset( $this->default_http );
			unset( $this->errors );
		}
		
		/**
		 * 
		 * @since 4.4.3
		 * @param string $personal_token
		 */
		public function set_personal_token( $personal_token )
		{
			$this->personal_token = $personal_token;
		}
		
		/**
		 * 
		 * @since 4.4.3
		 * @param boolean $throw
		 * @return string
		 * @throws Avia_Envato_Exception
		 */
		public function get_personal_token( $throw = true )
		{
			if( ! empty( $this->personal_token ) )
			{
				return $this->personal_token;
			}
			
			if( true !== $throw )
			{
				return '';
			}
			
			$this->errors->add( 'init', __( 'A personal token is required to get access to the Envato API', 'avia_framework' ) );
			
			throw new Avia_Envato_Exception();
		}
		
		/**
		 * Change the transient parameters - render the parameters you want to change
		 * 
		 * @since 4.4.3
		 * @param array $args
		 */
		public function set_cache_params( array $args )
		{
			$allowed = array( 'cache', 'cache_timeout' );
			
			foreach( $args as $name => $value ) 
			{
				if( in_array( $name, $allowed ) )
				{
					$this->{$name} = $value;
				}
			}
		}

		/**
		 * Returns the error object if errors exist
		 * 
		 * @since 4.4.3
		 * @return WP_Error|false
		 */
		public function get_errors()
		{
			return ( count( $this->errors->errors ) > 0 ) ? $this->errors : false;
		}
		
		/**
		 * 
		 * @since 4.4.3
		 */
		public function clear_errors()
		{
			$this->errors = new WP_Error();
		}
		
		
		/**
		 * Adds the error object to local error object
		 * 
		 * @since 4.4.3
		 * @param WP_Error $error
		 */
		protected function add_error_object( WP_Error $error )
		{
			$codes = $error->get_error_codes();
			
			foreach ( $codes as $code ) 
			{
				$messages = $error->get_error_messages( $code );
				
				foreach ( $messages as $message ) 
				{
					$this->errors->add( $code, $message );
				}
			}
		}
		
			
		/**
		 * Called, when status code != 200'
		 * Saves the information to local error object
		 * 
		 * Error in body:
		 *		'error'					=>	text
		 *		'error_description'		=>	text
		 * 
		 * @since 4.4.3
		 * @param array $response
		 * @param array|null $body
		 * @param string $prefix
		 */
		protected function add_envato_api_error( array $response, $body = array(), $prefix = '' )
		{
			$code = wp_remote_retrieve_response_code( $response );
			$message = wp_remote_retrieve_response_message( $response );
			
			if( 401 == $code )
			{
				$this->errors->add( 'Envato API Error', $prefix . ' ' . __( 'Your private token is invalid.', 'avia_framework' ) );
				return;
			}
			
			if( 429 == $code )
			{
				$headers = wp_remote_retrieve_headers( $response );
				$time = isset( $headers['Retry-After'] ) ? $headers['Retry-After'] : 0;
				if( ! empty( $time ) && is_numeric( $time ) )
				{
					$report = $prefix . ' ' . sprintf( __( 'Envato Rate Limit exceeded - Requests are blocked for %d seconds.', 'avia_framework' ), $time );
				}
				else 
				{
					$report = $prefix . ' ' . __( 'Envato Rate Limit for requests exceeded.', 'avia_framework' );
				}
				
				$report .= ' ' . __( 'We are unable to get the download URL for your products.', 'avia_framework' );
				
				$this->errors->add( 'Envato API Error', $report );
				return;
			}
			
			$message = sprintf( __( 'Errorcode %s returned by Envato: %s', 'avia_framework' ), $code, $message );
			if( ! empty( $prefix ) )
			{
				$message = $prefix . ' ' . $message;
			}
			
			if( ! is_array( $body ) || ! isset( $body['error'] ) )
			{
				$this->errors->add( 'Envato API Error', $message );
				return;
			}
			
			if( 'invalid_grant' == $body['error'] )
			{
				$message = __( 'The valid access time to Envato has expired. Please login again with your Envato Username. Thank you.', 'avia_framework' );
				$this->errors->add( 'Envato Login', $message );
				return;
			}
			
			unset( $body['error'] );
			
			if( empty( $body ) )
			{
				$this->errors->add( 'Envato API Error', $message );
				return;
			}
			
			foreach( $body as $key => $value ) 
			{
				$body[ $key ]  = $key . ': ' . $value;
			}
			
			$message .= ':<br />- ' . implode( '<br />- ', $body );
			
			if( 404 == $code )
			{
				$message .= ':<br />- ' . __( 'Possible cause: your download limit might be exceeded - please try again later.', 'avia_framework' );
			}
			
			$this->errors->add( 'Envato API Error', $message );
			
			return;
		}
	

		/**
		 * Set a default header
		 * 
		 * @since 4.4.3
		 * @return array
		 */
		protected function get_default_http_header()
		{
			global $wp_version;
			
			if( empty( $this->default_http ) )
			{
				/**
				 * Include additional info in user agent
				 */
				$extended_info = '';
				if( ! empty( $this->envato_theme_name ) && ! empty( $this->theme_version ) )
				{
					$extended_info .= "; {$this->envato_theme_name}/{$this->theme_version }";
				}
			
				$this->default_http = array(
					'user-agent'	=>	'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) . $extended_info,
					'timeout'		=> apply_filters( 'avf_envato_get_default_http_timeout', 300 ),
					'httpversion'	=>	'1.0',
					'blocking'		=>	true,
					'headers'		=>	array(
	//										'Content-length'	=>		0,
											'Content-type'		=>		'application/json; charset=utf-8'
											),
					'body'			=>	null,
					'cookies'		=>	array()
				);
			}
			
			/**
			 * @since 4.4.3
			 */
			return apply_filters( 'avf_envato_default_http_header', $this->default_http );
		}
		
		/**
		 * Sets the token header for Envato call
		 * 
		 * @since 4.4.3
		 * @param string $access			envato | personal_token
		 * @param array $http
		 * @return array
		 * @throws Avia_Envato_Exception
		 */
		protected function set_token_http_header( $access = 'personal_token', array $http = array() )
		{
			$orig_access = $access;
			$orig_http = $http;
			
			if( ! in_array( $access, array( 'envato', 'personal_token' ) ) )
			{
				$access = 'personal_token';
			}
			
			$http_default = $this->get_default_http_header();
			
			$new_http = wp_parse_args( $http, $http_default );
			
			if( 'personal_token' == $access )
			{
				$pt = $this->get_personal_token();
				$new_http['headers']['Authorization'] =	self::PERSONAL_TOKEN_TYPE . ' ' . $pt;
			}
			else
			{
				$this->errors->add( 'init', __( 'Currently only personal_token requests supported', 'avia_framework' ) );
				throw new Avia_Envato_Exception();
			}
			
			/**
			 * @since 4.4.3
			 */
			return apply_filters( 'avf_envato_api_header', $new_http, $orig_access, $orig_http );
		}
		
		/**
		 * Returns a list of all purchases of a user
		 * 
		 * @since 4.4.3
		 * @param string $user_name
		 * @return array
		 * @throws Avia_Envato_Exception
		 */
		public function get_purchases()
		{
			try
			{
				$http_args = $this->set_token_http_header( 'personal_token' );
				$url = 'https://api.envato.com/v3/market/buyer/list-purchases';
				
				$pt = $this->get_personal_token();
				$transient = '_purchases_' . $pt;
				
				$response = $this->envato_remote_get( $url, $http_args, $transient );
				
				$code = wp_remote_retrieve_response_code( $response );
				$body = json_decode( $response['body'], true );
				
				if( 200 != $code )
				{
					$this->add_envato_api_error( $response, $body, __( 'Purchases:', 'avia_framework' ) ); 
					throw new Avia_Envato_Exception( '', $code );
				}
				
				if( ! isset( $body['results'] ) )
				{
					$this->errors->add( 'Envato wrong datastructure', __( 'Purchases: Envato returned a wrong datastructure accessing your purchases. Unable to check for updates: ', 'avia_framework' ) . print_r( $body, true ) );
					throw new Avia_Envato_Exception();
				}
				
				return $body['results'];
			}
			catch ( Avia_Envato_Exception $ex ) 
			{
				$this->errors->add( 'Additional info', __( 'Purchases: A problem occured accessing your purchases. Unable to check for updates.', 'avia_framework' ) );
				throw $ex;
			}
		}
		
		/**
		 * Gets an array of envato products and gets the current version available
		 * 
		 * @since 4.5.3
		 * @param array $products
		 * @return array
		 */
		public function get_product_infos( array $products )
		{
			$errors = false;
			
			foreach( $products as $theme => $product ) 
			{
				$products[ $theme ]['item']['wordpress_theme_metadata']['version'] = false;
				
				try 
				{
					$http_args = $this->set_token_http_header( 'personal_token' );
//					$url = 'https://api.envato.com/v3/market/catalog/item-version?id=' . $product['item']['id'];
					$url = 'https://api.envato.com/v3/market/catalog/item?id=' . $product['item']['id'];
					
					$response = $this->envato_remote_get( $url, $http_args );
				
					$code = wp_remote_retrieve_response_code( $response );
					$body = json_decode( $response['body'], true );
					
					if( 200 != $code )
					{
						$this->add_envato_api_error( $response, $body, __( 'Get Product Version:', 'avia_framework' ) ); 
						throw new Avia_Envato_Exception( '', $code );
					}
				
					if( ! isset( $body['wordpress_theme_metadata']['version'] ) )
					{
						$this->errors->add( 'Envato wrong datastructure', __( 'Get Product Version: Envato returned a wrong datastructure. Unable to get latest version to check for updates: ', 'avia_framework' ) . print_r( $body, true ) );
						throw new Avia_Envato_Exception();
					}
					
					$products[ $theme ]['item']['wordpress_theme_metadata'] = $body['wordpress_theme_metadata'];
					$products[ $theme ]['item']['url'] = $body['url'];
				} 
				catch ( Avia_Envato_Exception $ex ) 
				{
					$errors = true;
				}
			}
			
			return $products;
		}

		/**
		 * Returns the URL to the WP theme download file
		 * 
		 * @since 4.4.3
		 * @param string $item_id
		 * @return string
		 * @throws Avia_Envato_Exception
		 */
		public function get_wp_download_url( $item_id )
		{
			try 
			{	
				$http_args = $this->set_token_http_header( 'personal_token' );
				$url = 'https://api.envato.com/v3/market/buyer/download?item_id=' . $item_id;
				
				$response = $this->envato_remote_get( $url, $http_args );
				
				$code = wp_remote_retrieve_response_code( $response );
				$body = json_decode( $response['body'], true );
		
				if( 200 != $code )
				{
					$this->add_envato_api_error( $response, $body, __( 'Download Package URL:', 'avia_framework' ) ); 
					throw new Avia_Envato_Exception( '', $code );
				}
				
				if( ! isset( $body['wordpress_theme'] ) )
				{
					$this->errors->add( 'Envato wrong datastructure', __( 'Download Package URL: Envato returned a wrong datastructure. Unable to get url for updates: ', 'avia_framework' ) . print_r( $body, true ) );
					throw new Avia_Envato_Exception();
				}
				
				return $body['wordpress_theme'];
			} 
			catch ( Avia_Envato_Exception $ex ) 
			{
				$this->errors->add( 'Additional info', __( 'Download Package URL: A problem occured accessing your download link. Unable to perform update.', 'avia_framework' ) );
				throw $ex;
			}
		}
		
		/**
		 * Returns the username or email for a given token
		 * 
		 * @since 4.4.3
		 * @param string $which			'username' | 'email'
		 * @return string
		 * @throws Avia_Envato_Exception
		 */
		public function get_userdata( $which )
		{
			try 
			{
				if( ! in_array( $which, array( 'username', 'email' ) ) )
				{
					$this->errors->add( 'init', __( 'Wrong parameter $which for get_userdata:', 'avia_framework' ) . $which );
					throw new Avia_Envato_Exception();
				}
				
				$http_args = $this->set_token_http_header( 'personal_token' );
				$url = 'https://api.envato.com/v1/market/private/user/' . $which . '.json';
				
				$response = $this->envato_remote_get( $url, $http_args );
				
				$code = wp_remote_retrieve_response_code( $response );
				$body = json_decode( $response['body'], true );
		
				if( 200 != $code )
				{
					$this->add_envato_api_error( $response, $body, $which ); 
					throw new Avia_Envato_Exception( '', $code );
				}
				
				if( ! isset( $body[ $which ] ) )
				{
					$this->errors->add( 'Envato wrong datastructure', $which . ': ' . __( 'Envato returned a wrong datastructure:', 'avia_framework' ) . ' '  . print_r( $body, true ) );
					throw new Avia_Envato_Exception();
				}
				
				return $body[ $which ];
			} 
			catch ( Avia_Envato_Exception $ex ) 
			{
				throw $ex;
			}
		}

		
		/**
		 * Performs the call to the envato API.
		 * Checks for a cached data if exist prior to perforing API call and caches tbe result if selected.
		 * 
		 * @since 4.4.3
		 * @param string $url
		 * @param array $http_args
		 * @param string $transient
		 * @return string
		 * @throws Avia_Envato_Exception
		 */
		protected function envato_remote_get( $url, array $http_args, $transient = '' )
		{
			$response = $this->get_cache( $transient );
			if( false === $response )
			{
				$response = wp_remote_get( $url, $http_args );
				if( is_wp_error( $response ) )
				{
					$this->add_error_object( $response );
					throw new Avia_Envato_Exception();
				}
				
				if( 200 == wp_remote_retrieve_response_code( $response ) )
				{
					$saved = $this->set_cache( $transient, $response );
					if( ( false === $saved ) && ! empty( $transient ) && defined( 'WP_DEBUG' ) && WP_DEBUG )
					{
						error_log( 'Internal WP error: could not save transient: ' . $transient );
					}
				}
			}
			
			return $response;
		}
		
		/**
		 * Store data in cache
		 * 
		 * @param string $transient
		 * @param mixed $data
		 * @return boolean|'no_cache'
		 */
		protected function set_cache( $transient, $data )
		{
			if( empty( $transient ) )
			{
				return 'no_cache';
			}
			
			if( false === apply_filters( 'avf_envato_base_set_cache', $this->cache, $transient, $data ) )
			{
				return 'no_cache';
			}
			
			$data = apply_filters( 'avf_envato_base_set_cache_value', $data, $transient );
			$timeout = apply_filters( 'avf_envato_base_set_cache_timeout', $this->cache_timeout, $transient, $data );
			
			$transient = Avia_Theme_Updater::validate_transient( $transient );
			
			return set_transient( $transient, $data, $timeout );
		}
		
		/**
		 * Returns the cached value
		 * 
		 * @since 4.4.3
		 * @param string $transient
		 * @return mixed|boolean
		 */
		protected function get_cache( $transient )
		{
			if( empty( $transient ) )
			{
				return false;
			}
			
			if( false === apply_filters( 'avf_envato_base_get_cache', $this->cache, $transient ) )
			{
				return false;
			}
			
			$transient = Avia_Theme_Updater::validate_transient( $transient );
			return get_transient( $transient );
		}
	
		/**
		 * Clear cache before expireing
		 * 
		 * @since 4.4.3
		 * @param string $transient
		 * @return boolean
		 */
		protected function clear_cache( $transient = '' ) 
		{
			$transient = Avia_Theme_Updater::validate_transient( $transient );
			
			return delete_transient( $transient );
		}
		
	}

}

