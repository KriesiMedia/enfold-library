<?php
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

	
/**
 * File was added with version 4.4 and holds functions that are related to the user privacy
 *
 * @since 4.4
 * @since 4.5.7.2 extended by Günter for new GDPR/DSGVO rules to allow must opt in
 * 
 * @added_by Kriesi
 *
 * Shortcodes used: 
 * 
 * [av_privacy_link page_id="your custom page id"]your custom page title[/av_privacy_link] -> returns a link to the wordpress privacy policy page (requires WP 4.9.6) or a custom page
 * [av_privacy_cookie_info] -> displays stored cookie info
 * 
 * [av_privacy_allow_cookies] -> to remember hiding of message bar and refuse all other cookies
 * [av_privacy_accept_essential_cookies] -> opt in for use of other cookies - if opt out no other cookies are stored
 * [av_privacy_google_tracking] -> to disable google tracking
 * [av_privacy_google_webfonts] -> to disable google webfonts
 * [av_privacy_google_maps] -> to disable google maps
 * [av_privacy_video_embeds] -> to disable video embeds
 * [av_privacy_google_recaptcha] -> to disable google recaptcha
 * 
 * [av_privacy_accept_button] -> adds an "Accept Cookies" button
 * [av_privacy_accept_all_button] -> adds an "Accept all cookies and services" button
 * [av_privacy_do_not_accept_button] -> adds a "Do Not Accept Cookies" button
 * [av_privacy_modal_popup_button] -> adds a button to open the modal cookie and privacy popup
 * 
 */

if( ! class_exists( 'av_privacy_class' ) )
{
	class av_privacy_class
	{
		/**
		 * Holds the instance of this class
		 * 
		 * @since 4.4.1
		 * @var av_privacy_class 
		 */
		static private $_instance = null;
		
		/**
		 *
		 * @since 4.4.1
		 * @var string 
		 */
		static protected $default_privacy_message = '';
		
		/**
		 * Array of custom cookies from theme options
		 * 
		 * @since 4.5.7.2
		 * @var array 
		 */
		protected $custom_cookies;

		/**
		 * @since 4.4
		 * @var array 
		 */
		protected $toggles;
		
		/**
		 * Array of cookies containing a human readable description for frontend shortcode
		 * 
		 * @since 4.5.7.2
		 * @var array 
		 */
		protected $cookie_infos;
		
		
		/**
		 * Array of Cookies that should be kept for admins to allow debugging, .....
		 * 
		 * @since 4.5.7.2
		 * @var array 
		 */
		protected $admin_keep_cookies;
		
		
		/**
		 * Return the instance of this class
		 * 
		 * @since 4.4.1
		 * @return av_privacy_class
		 */
		static public function instance()
		{
			if( is_null( av_privacy_class::$_instance ) )
			{
				av_privacy_class::$_instance = new av_privacy_class();
			}
			
			return av_privacy_class::$_instance;
		}
		
		
		/**
		 * @since 4.4
		 */
		protected function __construct()
		{
			$this->custom_cookies = null;
			$this->toggles = array();
			$this->cookie_infos = array();
			$this->admin_keep_cookies = null;
			
			//shortcode related stuff
			add_shortcode( 'av_privacy_allow_cookies', array( $this, 'av_privacy_allow_cookies' ) );
			add_shortcode( 'av_privacy_accept_essential_cookies', array( $this, 'av_privacy_accept_essential_cookies' ) );
			add_shortcode( 'av_privacy_google_tracking', array( $this, 'av_privacy_disable_google_tracking' ) );
			add_shortcode( 'av_privacy_google_webfonts', array( $this, 'av_privacy_disable_google_webfonts' ) );
			add_shortcode( 'av_privacy_google_maps', array( $this, 'av_privacy_disable_google_maps' ) );
			add_shortcode( 'av_privacy_video_embeds', array( $this, 'av_privacy_disable_video_embeds' ) );
			add_shortcode( 'av_privacy_google_recaptcha', array( $this, 'av_privacy_disable_google_recaptcha' ) );
			add_shortcode( 'av_privacy_custom_cookie', array( $this, 'av_privacy_disable_custom_cookie' ) );
			
			add_shortcode( 'av_privacy_accept_button', array( $this, 'av_privacy_accept_button' ) );
			add_shortcode( 'av_privacy_accept_all_button', array( $this, 'av_privacy_accept_all_button' ) );
			add_shortcode( 'av_privacy_do_not_accept_button', array( $this, 'av_privacy_do_not_accept_button' ) );
			add_shortcode( 'av_privacy_modal_popup_button', array( $this, 'av_privacy_modal_popup_button' ) );
			
			add_shortcode( 'av_privacy_link', array( $this, 'av_privacy_policy_link' ) );
			add_shortcode( 'av_privacy_cookie_info', array( $this, 'av_privacy_cookie_info' ) );
			
			
			add_filter( 'avia_header_class_filter', array( $this, 'handler_avia_header_class_filter' ), 10, 1 );
			add_action( 'wp_footer', array( $this, 'av_cookie_consent_bar' ), 3 );
			add_action( 'wp_footer', array( $this, 'footer_script' ), 1000 );
			
			add_action( 'init', array( $this, 'handler_wp_init' ), 1 );
			add_action( 'init', array( $this, 'handler_register_scripts' ), 20 );
			add_action( 'wp_loaded', array( $this, 'handler_manage_cookies' ), 999999 );
			add_action( 'wp_enqueue_scripts', array( $this, 'handler_wp_enqueue_scripts' ), 50 );
		
		}
		
		/**
		 * 
		 * @since 4.4.1
		 */
		public function __destruct() 
		{
			unset( $this->custom_cookies );
			unset( $this->toggles );
			unset( $this->cookie_infos );
			unset( $this->admin_keep_cookies );
		}
		
		/**
		 * 
		 * @added_by Günter
		 * @since 4.6.1
		 */
		public function handler_wp_init()
		{
			//hook into commentform if enabled in backend
			if( avia_get_option( 'privacy_message_commentform_active' ) == 'privacy_message_commentform_active' )
			{
				add_filter( 'comment_form_fields', array( $this, 'av_privacy_comment_checkbox' )  );
				add_filter( 'preprocess_comment', array( $this, 'av_privacy_verify_comment_checkbox' )  );
			}
			
			//hook into contactform if enabled in backend
			if( avia_get_option( 'privacy_message_contactform_active' ) == 'privacy_message_contactform_active' )
			{
				add_filter( 'avf_sc_contact_form_elements', array( $this, 'av_privacy_contactform_checkbox'), 10, 2  );
			}
			
			//hook into mailchimpform if enabled in backend
			if( avia_get_option( 'privacy_message_mailchimp_active' ) == 'privacy_message_mailchimp_active' )
			{
				add_filter( 'avf_sc_mailchimp_form_elements', array( $this, 'av_privacy_mailchimp_checkbox' ), 10, 2 );
			}
			
			//hook into login/registration forms if enabled in backend
			if( avia_get_option( 'privacy_message_login_active' ) == 'privacy_message_login_active' )
			{
				add_action( 'login_form', array( $this, 'av_privacy_login_extra' ), 10 );
				add_filter( 'wp_authenticate_user', array( $this,'av_authenticate_user_acc' ), 99999, 2 );
			}
			
			//hook into registration forms if enabled in backend
			if( avia_get_option( 'privacy_message_registration_active' ) == 'privacy_message_registration_active' )
			{
				add_action( 'register_form', array( $this, 'av_privacy_register_extra' ), 10 );
				add_filter( 'registration_errors', array( $this, 'av_registration_errors' ), 99999, 3 );
			}
		}
		
		/**
		 * @since 4.5.7.2
		 * @added_by Günter
		 */
		public function handler_register_scripts()
		{
			$vn = avia_get_theme_version();
			$template_url = get_template_directory_uri();
			
			wp_register_style( 'avia-cookie-css', $template_url . '/css/avia-snippet-cookieconsent.css', array( 'avia-layout' ), $vn, 'screen' );
			wp_register_script( 'avia-cookie-js' , $template_url . '/js/avia-snippet-cookieconsent.js', array( 'avia-default' ), $vn, true );
		}
		
		/**
		 * Manages cookies on PHP side. Also tries to remove cookies.
		 * Limitation is that cookies only can be removed when exact path is known (path /xy is different from /xy/).
		 * As we are not able to read a path for a cookie we only can try to remove from "/".
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 */
		public function handler_manage_cookies()
		{

			/**
			 * In backend users must accept cookies. For ajax calls we have frontend script to remove not needed cookies
			 */
			if( is_admin() )
			{
				return;
			}
			
			$option = $this->get_cookie_consent_message_bar_option();
			if( in_array( $option, array( 'message_bar', 'disabled' ) ) )
			{
				return;
			}
			
			$cookie_accepted = isset( $_COOKIE['aviaCookieConsent'] );
			$cookie_allow_hide_bar = isset( $_COOKIE['aviaPrivacyRefuseCookiesHideBar'] );
			$cookie_essential_enabled = isset( $_COOKIE['aviaPrivacyEssentialCookiesEnabled'] );
			
			/**
			 * Check if we updated 
			 *		'||v1.0' from <= 4.5.7 to 4.6.2
			 */
			if( $cookie_accepted && ! ( $cookie_allow_hide_bar && $cookie_essential_enabled ) )
			{
				/**
				 * If user already accepted cookie we add our essential cookies so user can continue to use site without need to opt in for these explicit
				 */
				$cookie_accepted_value = $_COOKIE['aviaCookieConsent'];
				
				$sep = strrpos( $cookie_accepted_value, '||v' );
				
				if( false === $sep )
				{
					setcookie( 'aviaPrivacyRefuseCookiesHideBar', true, time() + YEAR_IN_SECONDS, '/' );
					setcookie( 'aviaPrivacyEssentialCookiesEnabled', true, time() + YEAR_IN_SECONDS, '/' );
				}
			}
			
			/**
			 * When we have silent accept cookies and user did not accept yet we add a cookie for that. Allows in frontend to activate
			 * necessary services and default setting for toggles.
			 */
			if( '' == $this->get_opt_in_setting() )
			{
				if( $cookie_accepted )
				{
					setcookie( 'aviaCookieSilentConsent', false, time() - 3600, '/' );
				}
				else
				{
					setcookie( 'aviaCookieSilentConsent', true, time() + YEAR_IN_SECONDS, '/' );
					return;
				}
			}
			
			/**
			 * if opt in changed to 'needs_opt_in' we must reset cookies and force user to accept cookies again
			 */
			$cookie_must_opt_in_setting = isset( $_COOKIE['aviaPrivacyMustOptInSetting'] );
			$user_must_opt_in = 'needs_opt_in' == $this->get_opt_in_setting();
			$opt_in_type_not_changed = ! $user_must_opt_in || ( $user_must_opt_in && $cookie_must_opt_in_setting );
			
			if( $this->user_has_opt_in() && $opt_in_type_not_changed )
			{
				if( ! $user_must_opt_in )
				{
					setcookie( 'aviaPrivacyMustOptInSetting', false, time()-3600, '/' );
				}
				
				return;
			}
			
			
			
			$keep_cookies = array();
			if( $opt_in_type_not_changed && $cookie_accepted && $cookie_allow_hide_bar )
			{
				$keep_cookies[] = 'aviaCookieConsent'; 
				$keep_cookies[] = 'aviaPrivacyRefuseCookiesHideBar';
				$keep_cookies[] = 'aviaPrivacyMustOptInSetting';
			}
			else if( $opt_in_type_not_changed && $cookie_accepted )
			{
				$keep_cookies[] = 'aviaCookieConsent';
				$keep_cookies[] = 'aviaPrivacyMustOptInSetting';
			}
			
			if( is_user_logged_in() )
			{
				$keep_cookies = array_merge( $keep_cookies, $this->get_admin_keep_cookies() );
			}
			
			$keep_cookies = array_map( function ( $value ) { return strtolower( trim( $value) ); }, $keep_cookies );
			
			foreach( $_COOKIE as $cookie => $value ) 
			{
				$cookie_lc = strtolower( $cookie );
				
				if( in_array( $cookie_lc, $keep_cookies ) )
				{
					continue;
				}
				
				$remove = true;
				
				foreach ( $keep_cookies as $keep ) 
				{
					if( false === strpos( $keep, '*' ) )
					{
						continue;
					}
					
					$keep = str_replace( '*', '', $keep );
					$pos = strpos( $cookie_lc, $keep );
					if( false !== $pos && 0 == $pos )
					{
						$remove = false;
						break;
					}
				}
				
				if( $remove )
				{
					setcookie( $cookie, false, time()-3600, '/' );
				}
			}		
			
		}

		/**
		 * @since 4.5.7.2
		 * @added_by Günter
		 */
		public function handler_wp_enqueue_scripts()
		{
			if( 'disabled' == $this->get_cookie_consent_message_bar_option() )
			{
				return;
			}
		
			wp_enqueue_script( 'avia-cookie-js' );
			wp_enqueue_style( 'avia-cookie-css' );
			
			$cookies = $this->get_cookie_infos();
			if( ! empty( $cookies ) )
			{
				wp_localize_script( 'avia-cookie-js', 'AviaPrivacyCookieConsent', $cookies );
			}
			
			$args = apply_filters( 'avf_privacy_additional_frontend_data', array(
					'cookie_refuse_button_alert'	=> avia_get_option( 'cookie_refuse_button_alert', '' ),
					'no_cookies_found'				=> __( 'No accessable cookies found in domain', 'avia_framework' ),
					'admin_keep_cookies'			=> $this->get_admin_keep_cookies(),
					'remove_custom_cookies'			=> $this->get_custom_cookies(),
					'no_lightbox'					=> __( "We need a lightbox to show the modal popup. Please enable the built in lightbox in Theme Options Tab or include your own modal window plugin.\n\nYou need to connect this plugin in JavaScript with callback wrapper functions - see avia_cookie_consent_modal_callback in file enfold\js\avia-snippet-cookieconsent.js ", 'avia_framework' ),
				) );
			
			wp_localize_script( 'avia-cookie-js', 'AviaPrivacyCookieAdditionalData', $args );
		}

		/**
		 * Returns the request state for the cookie consent message bar option
		 * (not the real state in frontend - could be hidden because user already accepted cookies)
		 * With 4.6.3 option was split in 2 other options:
		 *	- cookie_consent
		 *	- cookie_consent_no_bar
		 *	- cookie_message_bar_only
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @return string			'disabled' | 'show_bar' | 'hide_bar' | 'message_bar'
		 */
		public function get_cookie_consent_message_bar_option()
		{
			if( 'cookie_consent' != avia_get_option( 'cookie_consent' ) )
			{
				return 'disabled';
			}
			
			$val = avia_get_option( 'cookie_message_bar_only', '' );
			if( false !== stripos( $val, 'cookie_message_bar_only' ) )
			{
				return 'message_bar';
			}
			
			if( 'cookie_consent_no_bar' == avia_get_option( 'cookie_consent_no_bar', '' ) )
			{
				return 'hide_bar';
			}
			
			return 'show_bar';
		}
		
		/**
		 * Returns the option cookie_default_settings
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @return string			'' | 'can_opt_out' | 'needs_opt_in'
		 */
		public function get_opt_in_setting()
		{
			$value = avia_get_option( 'cookie_default_settings' );
			$value = in_array( $value, array( 'can_opt_out', 'needs_opt_in', '' ) ) ? $value : '';
			
			return $value;
		}

		/**
		 * Returns a filterable default privacy message for checkboxes
		 * 
		 * @since 4.4.1
		 * @added_by Günter
		 * @return string
		 */
		static public function get_default_privacy_message()
		{
			if( empty( av_privacy_class::$default_privacy_message ) )
			{
				av_privacy_class::$default_privacy_message = apply_filters( 'avf_default_privacy_message',	__( 'I agree to the terms and conditions laid out in the [av_privacy_link]Privacy Policy[/av_privacy_link]', 'avia_framework' ) );
			}
			
			return av_privacy_class::$default_privacy_message;
		}
		
		/**
		 * Returns the theme option array for custom cookies to monitor
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @return array
		 */
		public function get_custom_cookies()
		{
			global $avia;
			
			if( ! isset( $avia->options ) || ! is_array( $avia->options ) )
			{
				return array();
			}
			
			$custom_cookies = array();
			
			if( ! is_array( $this->custom_cookies ) )
			{
				$this->custom_cookies = array();
				
				$cookies = avia_get_option( 'custom_cookies', array() );
				
				/**
				 * @since 4.5.7.2
				 * @param array $cookies
				 * @return array
				 */
				$cookies = apply_filters( 'avf_privacy_custom_cookies_array', $cookies );
				
				foreach( $cookies as $key => $cookie ) 
				{
					if( empty( $cookie['cookie_name'] ) || in_array( $cookie['cookie_name'], $custom_cookies ) )
					{
						continue;
					}
					
					$custom_cookies[] = $cookie['cookie_name'];
					
					$this->custom_cookies[ $key ] = $cookie;
					$this->custom_cookies[ $key ]['avia_cookie_name'] = "aviaPrivacyCustomCookie{$cookie['cookie_name']}Disabled";
				}
			}
			
			return $this->custom_cookies;
		}

		/**
		 * Returns an array with human readable info for a given theme cookie
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @return array
		 */
		public function get_cookie_infos()
		{
			if( ! empty( $this->cookie_infos ) )
			{
				return $this->cookie_infos;
			}
			
			$infos = array(
						'?'										=> __( 'Usage unknown', 'avia_framework' ),
						'aviaCookieSilentConsent'				=> __( 'Cookies are used, even if user does not accept them. Will be removed if user accepts cookies.', 'avia_framework' ),
						'aviaCookieConsent'						=> __( 'Use and storage of Cookies has been accepted - restrictions set in other cookies', 'avia_framework' ),
						'aviaPrivacyRefuseCookiesHideBar'		=> __( 'Hide cookie message bar on following page loads and refuse cookies if not allowed - aviaPrivacyEssentialCookiesEnabled must be set', 'avia_framework' ),
						'aviaPrivacyEssentialCookiesEnabled'	=> __( 'Allow storage of site essential cookies and other cookies and use of features if not opt out', 'avia_framework' ),
						'aviaPrivacyVideoEmbedsDisabled'		=> __( 'Do not allow video embeds', 'avia_framework' ),
						'aviaPrivacyGoogleTrackingDisabled'		=> __( 'Do not allow Google Analytics', 'avia_framework' ),
						'aviaPrivacyGoogleWebfontsDisabled'		=> __( 'Do not allow Google Webfonts', 'avia_framework' ),
						'aviaPrivacyGoogleMapsDisabled'			=> __( 'Do not allow Google Maps', 'avia_framework' ),
						'aviaPrivacyGoogleReCaptchaDisabled'	=> __( 'Do not allow Google reCaptcha', 'avia_framework' ),
						'aviaPrivacyMustOptInSetting'			=> __( 'Settings are for users that must opt in for cookies and services', 'avia_framework' ),
						'PHPSESSID'								=> __( 'Keeps track of your session', 'avia_framework' ),
						'XDEBUG_SESSION'						=> __( 'PHP Debugger session cookie', 'avia_framework' ),
					);
			
			$custom_cookies = $this->get_custom_cookies();
			
			foreach( $custom_cookies as $custom_cookie )
			{
				$desc = $infos['?'];
				
				if( ! empty( $custom_cookie['cookie_info_desc'] ) )
				{
					$infos[ $custom_cookie['cookie_name'] ] = $custom_cookie['cookie_info_desc'];
					$desc = $custom_cookie['cookie_info_desc'];
				}
				
				$infos[ $custom_cookie['avia_cookie_name'] ] = sprintf( __( 'needed to remove cookie %s (%s)', 'avia_framework' ), $custom_cookie['cookie_name'], $desc );
			}
			
			/**
			 * @since 4.5.7.2
			 * @param array $infos
			 * @param av_privacy_class $this
			 * @return array
			 */
			$this->cookie_infos = apply_filters( 'avf_privacy_cookie_infos', $infos, $this );
			
			if( ! is_array( $this->cookie_infos ) )
			{
				$this->cookie_infos = array();
			}
			
			return $this->cookie_infos;
		}
		
		/**
		 * Returns an array of cookies that should be kept for admins
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @return array
		 */
		public function get_admin_keep_cookies()
		{
			if( ! is_array( $this->admin_keep_cookies ) )
			{
				$this->admin_keep_cookies = array( 'PHPSESSID', 'wp-*', 'wordpress*', 'XDEBUG*' );
				
				/**
				 * @since 4.5.7.2
				 * @param array $this->admin_keep_cookies
				 * @return array
				 */
				$this->admin_keep_cookies = apply_filters( 'avf_admin_keep_cookies', $this->admin_keep_cookies );
			}
			
			return $this->admin_keep_cookies;
		}

		/**
		 * Toggle that allows to set/unset a cookie that can then be used for privacy options
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @param string $cookie
		 * @param string $content
		 * @param string $save_state			'checked' | 'unchecked'
		 * @param string $default				'checked' | 'unchecked'
		 * @return string
		 */
		protected function av_privacy_toggle( $cookie, $content, $save_state = 'unchecked', $default = 'unchecked' )
		{	
			$output = '';
	
			$this->toggles[ $cookie ] = true;
			
			$extra_class = 'unchecked' == $save_state ? "av-cookie-save-{$save_state}" : 'av-cookie-save-checked';
			$extra_class .= 'unchecked' == $default ? " av-cookie-default-{$default}" : ' av-cookie-default-checked';
			$checked = 'unchecked' == $default ? '' : 'checked="checked"';
			
			$disabled = '';
			$message = '';
			
			if( 'disabled' == $this->get_cookie_consent_message_bar_option() )
			{
				$extra_class .= ' av-cookie-sc-disabled';
				$disabled = 'disabled="disabled"';
				$message .= __( 'Please enable cookie consent messages in backend to use this feature.', 'avia_framework' );
			}
			
			$output .=	'<div class="av-switch-' . $cookie . ' av-toggle-switch av-cookie-disable-external-toggle ' . $extra_class . '">';
			$output .=		'<label>';
			$output .=			'<input type="checkbox" ' . $checked . ' id="' . $cookie . '" class="' . $cookie . ' " name="' . $cookie . '" ' . $disabled . '>';
			$output .=			'<span class="toggle-track"></span>';
			$output .=			'<span class="toggle-label-content">' . $content . '</span>';
			$output .=		'</label>';
			if( ! empty( $message ) )
			{
				$output .=	"<p><strong>{$message}</strong></p>";
			}
			$output .=	'</div>';
			
			return $output;
		}
		
		/**
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_allow_cookies ( $atts = array(), $content = '', $shortcodename = '' )
		{
			$default = __( 'Check to enable permanent hiding of message bar and refuse all cookies if you do not opt in. We need 2 cookies to store this setting. Otherwise you will be prompted again when opening a new browser window or new a tab.', 'avia_framework' );
			$content = ! empty( $content ) ?  $content : $default;
			$cookie  = 'aviaPrivacyRefuseCookiesHideBar';
			$default = 'needs_opt_in' == $this->get_opt_in_setting() && current_theme_supports( 'avia_privacy_basic_cookies_unchecked' ) ? 'unchecked' : 'checked';
			
			return $this->av_privacy_toggle( $cookie , $content, 'checked', $default );
		}
		
		/**
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_accept_essential_cookies( $atts = array(), $content = '', $shortcodename = '' )
		{
			$content = ! empty( $content ) ?  $content : __( 'Click to enable/disable essential site cookies.', 'avia_framework' );
			$cookie  = 'aviaPrivacyEssentialCookiesEnabled';
			$default = 'needs_opt_in' == $this->get_opt_in_setting() && current_theme_supports( 'avia_privacy_basic_cookies_unchecked' ) ? 'unchecked' : 'checked';
			
			return $this->av_privacy_toggle( $cookie , $content, 'checked', $default );
		}

		/**
		 * Shortcode that allows to disable google analytics tracking
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_disable_google_tracking( $atts = array(), $content = '', $shortcodename = '' )
		{	
			$content = ! empty( $content ) ?  $content : __( 'Click to enable/disable Google Analytics tracking.', 'avia_framework' );
			$cookie  = "aviaPrivacyGoogleTrackingDisabled";
			$default = 'needs_opt_in' == $this->get_opt_in_setting() ? 'unchecked' : 'checked';
			$browser = 'data-disabled_by_browser="' . esc_attr( __( 'Please enable this feature in your browser settings and reload the page.', 'avia_framework' ) ) . '"';
			
			$html = $this->av_privacy_toggle( $cookie , $content, 'unchecked', $default );
			$html = str_replace( '<div class', "<div {$browser} class", $html );
			
			return $html;
		}
		
		
		/**
		 * Shortcode that allows to disable google webfonts loading
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_disable_google_webfonts( $atts = array(), $content = '', $shortcodename = '' )
		{	
			$content = ! empty( $content ) ?  $content : __( 'Click to enable/disable Google Webfonts.', 'avia_framework' );
			$cookie  = "aviaPrivacyGoogleWebfontsDisabled";
			$default = 'needs_opt_in' == $this->get_opt_in_setting() ? 'unchecked' : 'checked';
			
			return $this->av_privacy_toggle( $cookie , $content, 'unchecked', $default );
		}
		
		/**
		 * Shortcode that allows to disable google maps loading
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_disable_google_maps( $atts = array(), $content = '', $shortcodename = '' )
		{	
			$content = ! empty( $content ) ?  $content : __( 'Click to enable/disable Google Maps.', 'avia_framework' );
			$cookie  = "aviaPrivacyGoogleMapsDisabled";
			$default = 'needs_opt_in' == $this->get_opt_in_setting() ? 'unchecked' : 'checked';
			
			return $this->av_privacy_toggle( $cookie , $content, 'unchecked', $default );
		}
		
		
		/**
		 * Shortcode that allows to disable video embeds
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_disable_video_embeds( $atts = array(), $content = '', $shortcodename = '' )
		{	
			$content = ! empty( $content ) ?  $content : __( 'Click to enable/disable video embeds.', 'avia_framework' );
			$cookie  = "aviaPrivacyVideoEmbedsDisabled";
			$default = 'needs_opt_in' == $this->get_opt_in_setting() ? 'unchecked' : 'checked';
			
			return $this->av_privacy_toggle( $cookie , $content, 'unchecked', $default );
		}
		
		/**
		 * Shortcode that allows to disable Google reCaptcha
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_disable_google_recaptcha( $atts = array(), $content = '', $shortcodename = '' )
		{
			$content = ! empty( $content ) ?  $content : __( 'Click to enable/disable Google reCaptcha.', 'avia_framework' );
			$cookie  = "aviaPrivacyGoogleReCaptchaDisabled";
			$default = 'needs_opt_in' == $this->get_opt_in_setting() ? 'unchecked' : 'checked';
			
			return $this->av_privacy_toggle( $cookie , $content, 'unchecked', $default );
		}
		
		/**
		 * Shortcode that allows to disable a custom defined cookie in theme options
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_disable_custom_cookie( $atts = array(), $content = '', $shortcodename = '' )
		{
			$atts = shortcode_atts( array(
								'cookie_name'	=> ''
							), $atts, $shortcodename );
			
			$cookies = $this->get_custom_cookies();
			$found = null;
			
			foreach( $cookies as $cookie )
			{
				if( $cookie['cookie_name'] == $atts['cookie_name'] )
				{
					$found = $cookie;
					break;
				}
			}
			
			if( is_null( $found ) )
			{
				return '';
			}
			
			$default_content = ! empty( $content ) ?  $content : sprintf( __( 'Click to enable/disable %s.', 'avia_framework' ), $atts['cookie_name'] );
			$msg = ! empty( $found['cookie_content'] ) ?  $found['cookie_content'] : $default_content;
			$default = 'needs_opt_in' == $this->get_opt_in_setting() ? 'unchecked' : 'checked';
			
			return $this->av_privacy_toggle( $found['avia_cookie_name'] , $msg, 'unchecked', $default );
		}

		/**
		 * Shortcode to accept selected cookies and services button
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_accept_button( $atts = array(), $content = '', $shortcodename = '' )
		{	
			$atts = shortcode_atts( array( 
								'wrapper_class'	=> '',
								'id'			=> '',
								'class'			=> ''
							), $atts, $shortcodename );
			
			$out = '';
			$tag = 'a';
			
			if( 'disabled' == $this->get_cookie_consent_message_bar_option() )
			{
				$tag = 'div';
				$content = __( 'Disabled:', 'avia_framework' ) . ' ' . $content;
			}
			
			$content = ! empty( $content ) ? $content : __( 'Accept use of cookies', 'avia_framework' );
			$class = 'avia-button avia-cookie-consent-button avia-color-theme-color av-extra-cookie-btn avia-cookie-close-bar ' . $atts['class'];
			$id = ! empty( $atts['id'] ) ? " id='{$atts['id']}'" : '';
			 
			$out .=	"<div class='avia-cookie-close-bar-wrap {$atts['wrapper_class']}'>";
			$out .=		"<{$tag} href='#' class='{$class}' $id>{$content}</{$tag}>";
			$out .= '</div>';
			
			return $out;
		}
		
		/**
		 * Shortcode to accept all cookies and services button
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_accept_all_button( $atts = array(), $content = '', $shortcodename = '' )
		{	
			$atts = shortcode_atts( array( 
								'wrapper_class'	=> '',
								'id'			=> '',
								'class'			=> ''
							), $atts, $shortcodename );
			
			$out = '';
			$tag = 'a';
			
			if( 'disabled' == $this->get_cookie_consent_message_bar_option() )
			{
				$tag = 'div';
				$content = __( 'Disabled:', 'avia_framework' ) . ' ' . $content;
			}
			
			$content = ! empty( $content ) ? $content : __( 'Accept use of all cookies and services', 'avia_framework' );
			$class = 'avia-button avia-cookie-consent-button avia-color-theme-color av-extra-cookie-btn avia-cookie-close-bar avia-cookie-select-all ' . $atts['class'];
			$id = ! empty( $atts['id'] ) ? " id='{$atts['id']}'" : '';
			 
			$out .=	"<div class='avia-cookie-close-bar-wrap {$atts['wrapper_class']}'>";
			$out .=		"<{$tag} href='#' class='{$class}' $id>{$content}</{$tag}>";
			$out .= '</div>';
			
			return $out;
		}
		
		/**
		 * Shortcode for do not accept cookie button ( Do not accept and hide notification )
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_do_not_accept_button( $atts = array(), $content = '', $shortcodename = '' )
		{	
			$atts = shortcode_atts( array( 
								'wrapper_class'	=> '',
								'id'			=> '',
								'class'			=> ''
							), $atts, $shortcodename );
			
			$out = '';
			$tag = 'a';
			
			$content = ! empty( $content ) ? $content : __( 'Do not allow to use cookies', 'avia_framework' );
			$class = 'avia-button avia-cookie-consent-button avia-color-theme-color-subtle av-extra-cookie-btn avia-cookie-hide-notification ' . $atts['class'];
			$id = ! empty( $atts['id'] ) ? " id='{$atts['id']}'" : '';
			
			if( 'disabled' == $this->get_cookie_consent_message_bar_option() )
			{
				$tag = 'div';
				$content = __( 'Disabled:', 'avia_framework' ) . ' ' . $content;
			}
			 
			$out .=	"<div class='avia-cookie-hide-notification-wrap {$atts['wrapper_class']}'>";
			$out .=		"<{$tag} href='#' class='{$class}' $id>{$content}</{$tag}>";
			$out .= '</div>';
			
			return $out;
		}
		
		/**
		 * Shortcode for button to open modal privacy popup
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_modal_popup_button( $atts = array(), $content = '', $shortcodename = '' )
		{	
			$atts = shortcode_atts( array( 
								'wrapper_class'	=> '',
								'id'			=> '',
								'class'			=> ''
							), $atts, $shortcodename );
			
			$out = '';
			$tag = 'a';
			
			$content = ! empty( $content ) ? $content : __( 'Learn more about our privacy policy', 'avia_framework' );
			$class = 'avia-button avia-cookie-consent-button av-extra-cookie-btn avia-cookie-info-btn ' . $atts['class'];
			$id = ! empty( $atts['id'] ) ? " id='{$atts['id']}'" : '';
			
			if( 'disabled' == $this->get_cookie_consent_message_bar_option() )
			{
				$tag = 'div';
				$content = __( 'Disabled:', 'avia_framework' ) . ' ' . $content;
			}
			
			$out .=	"<div class='av-privacy-popup-button-wrap {$atts['wrapper_class']}'>";
			$out .=		"<{$tag} href='#' class='{$class}' $id>{$content}</{$tag}>";
			$out .= '</div>';
			
			return $out;
		}
		
		
		/**
		 * Shortcode for a link to the privacy policy page. Requires wp 4.9.6.
		 * 
		 * @since 4.4
		 * @since 4.4.1				custom page id added
		 * @added_by Kriesi
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_policy_link( $atts = array(), $content = '', $shortcodename = '' )
		{	
			$atts = shortcode_atts(	array(		
							'page_id'	=> ''
						), $atts, $shortcodename );
			
			$url = false;
			
			if( ! empty( $atts['page_id'] ) )
			{
				$url = get_permalink( $atts['page_id'] );
			}
			
			if( false === $url )
			{
				$page_id = get_option( 'wp_page_for_privacy_policy' );
				$url	 = get_permalink( $page_id );
			}
			
			if( false === $url )
			{
				$link = $content;
			}
			else
			{
				$content = ! empty( $content ) ? $content : get_the_title( $page_id );
				$link	 = "<a href='{$url}' target='_blank'>{$content}</a>";
			}
			
			return $link;
		}
		
		/**
		 * Shortcode to output info about used cookies. Adds a container that is filled by js on actual cookies set.
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @return string
		 */
		public function av_privacy_cookie_info( $atts = array(), $content = '', $shortcodename = '' )
		{
			$atts = shortcode_atts( array( 
								'id'			=> '',
								'class'			=> ''
							), $atts, $shortcodename );
			
			$out = '';
			
			$id = ! empty( $atts['id'] ) ? " id='{$atts['id']}'" : '';
			
			$out .=	"<div {$id} class='avia-cookie-privacy avia-cookie-privacy-cookie-info avia-cookie-privacy-cookie-info-container {$atts['class']}'>";
			$out .= '</div>';
			
			return $out;
		}

		/**
		 * Javascript that gets appended to pages that got a privacy shortcode toggle
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return void
		 */
		public function footer_script()
		{
			if( empty( $this->toggles ) ) 
			{
				return;
			}
			
			$output  = '';
			$output .= "
			<script type='text/javascript'>
			
				function av_privacy_cookie_setter( cookie_name ) 
				{
					var cookie_check = jQuery('html').hasClass('av-cookies-needs-opt-in') || jQuery('html').hasClass('av-cookies-can-opt-out');
					var toggle = jQuery('.' + cookie_name);
				
					toggle.each(function()
					{
						var container = jQuery(this).closest('.av-toggle-switch');
						if( cookie_check && ! document.cookie.match(/aviaCookieConsent/) )
						{
							this.checked = container.hasClass( 'av-cookie-default-checked' );
						}
						else if( cookie_check && document.cookie.match(/aviaCookieConsent/) && ! document.cookie.match(/aviaPrivacyEssentialCookiesEnabled/) && cookie_name != 'aviaPrivacyRefuseCookiesHideBar' )
						{
							if( cookie_name == 'aviaPrivacyEssentialCookiesEnabled' )
							{
								this.checked = false;
							}
							else
							{
								this.checked = container.hasClass( 'av-cookie-default-checked' );
							}
						}
						else
						{
							if( container.hasClass('av-cookie-save-checked') )
							{
								this.checked = document.cookie.match(cookie_name) ? true : false;
							}
							else
							{
								this.checked = document.cookie.match(cookie_name) ? false : true;
							}
						}
					});
					
					jQuery('.' + 'av-switch-' + cookie_name).addClass('active');
					
					toggle.on('click', function()
					{
						/* sync if more checkboxes exist because user added them to normal page content */
						var check = this.checked;
						jQuery('.' + cookie_name).each( function()
						{
							this.checked = check;
						});
						
						var silent_accept_cookie = document.cookie.match(/aviaCookieSilentConsent/);
						
						if( ! silent_accept_cookie && cookie_check && ! document.cookie.match(/aviaCookieConsent/) || sessionStorage.getItem( 'aviaCookieRefused' ) )
						{
							return;
						}
						
						var container = jQuery(this).closest('.av-toggle-switch');
						var action = '';
						if( container.hasClass('av-cookie-save-checked') )
						{
							action = this.checked ? 'save' : 'remove';
						}
						else
						{
							action = this.checked ? 'remove' : 'save';
						}
						
						if('remove' == action)
						{
							document.cookie = cookie_name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
						}
						else
						{
							var theDate = new Date();
							var oneYearLater = new Date( theDate.getTime() + 31536000000 );
							document.cookie = cookie_name + '=true; Path=/; Expires='+oneYearLater.toGMTString()+';';
						}
					});
				};
			";
			
			foreach( $this->toggles as $toggles => $val )
			{
				$output .= " av_privacy_cookie_setter('{$toggles}'); ";
			}
			
			$output .= "</script>";
			
			$output = preg_replace( '/\r|\n|\t/', '', $output );
			echo $output;
		}
		
		
		/**
		 * Appends a checkbox to the comment form that needs to be checked in order to comment
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @param array $comment_field
		 * @return array
		 */
		public function av_privacy_comment_checkbox( $comment_field = array() )
		{
			$args = array(
							'id'			=> 'comment-form-av-privatepolicy',
							'content'		=> avia_get_option( 'privacy_message' ),
							'extra_class'	=> ''
					);
			
			$comment_field['comment-form-av-privatepolicy'] = $this->privacy_checkbox_field( $args );
			
			return $comment_field ;
		}
		
		/**
		 * Creates the checkbox html 
		 * 
		 * To be able to support 3rd party plugins with custom login forms that do not use standard WP hooks we
		 * add hidden field fake-comment-form-av-privatepolicy we can check if our form was included at all. 
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @param array $args 
		 * @return string
		 */
		public function privacy_checkbox_field( array $args = array() )
		{
			$args = wp_parse_args( $args, array(
							'id'			=> '',
							'content'		=> '',			//	shortcode are executed in this function
							'extra_class'	=> '',
							'attributes'	=> ''
					) );
			
			extract( $args );
			
			if( empty( $id ) )
			{
				return '';
			}
			
			if( empty( $content ) ) 
			{
				$content = av_privacy_class::get_default_privacy_message();
			}
			
			$content = do_shortcode( $content );
			
			$output = '<p class="form-av-privatepolicy ' . $id . ' ' . $extra_class . '" style="margin: 10px 0;">
						<input id="' . $id . '" name="' . $id . '" type="checkbox" value="yes" ' . $attributes . '>
						<label for="' . $id . '">' . $content . '</label>
						<input type="hidden" name="fake-' . $id . '" value="fake-val">
					  </p>';
			
			return $output ;
		}
		
		/**
		 * Checks if the user accepted the privacy policy. If not tell him that he has to if he wants to comment
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		public function av_privacy_verify_comment_checkbox( $commentdata ) 
		{
		    if ( ! is_user_logged_in() && isset( $_POST['fake-comment-form-av-privatepolicy'] ) && ! isset( $_POST['comment-form-av-privatepolicy'] ) )
		    {
			    $error_message = apply_filters( 'avf_privacy_comment_checkbox_error_message', __( 'Error: You must agree to our privacy policy to comment on this site...' , 'avia_framework' ) );
			    wp_die( $error_message );
		    }
		
		    return $commentdata;
		}
		
		/**
		 * Adds a checkbox field to contact forms
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		public function av_privacy_contactform_checkbox( $fields , $atts )
		{
			$content = avia_get_option('privacy_message_contact');
			if( empty( $content ) )
			{
				$content = av_privacy_class::get_default_privacy_message();
			}
      		$content = do_shortcode( $content );
      		
			$fields['av_privacy_agreement'] = array(
				'label' 	=> $content,
				'type' 		=> 'checkbox',
				'options' 	=> '',
				'check' 	=> 'is_empty',
				'width' 	=> '',
				'av_uid' 	=> '',
				'class'		=> 'av_form_privacy_check av_contact_privacy_check',
			);
			
			return $fields ;
		}

		/**
		 * Adds a checkbox field to mailchimp forms. bit more complicated than appending since we need to add the checkbox before the button
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		public function av_privacy_mailchimp_checkbox( $fields , $atts )
		{
			$keys = array_keys($fields);
			foreach($keys as $pos => $key)
			{
				if(strpos($key, 'av-button') === 0) break;
			}
			
			$content = avia_get_option('privacy_message_mailchimp');
			if( empty( $content ) )
			{
				$content = av_privacy_class::get_default_privacy_message();
			}
      		$content = do_shortcode( $content );
			
			$new_fields['av_privacy_agreement'] = array(
				'label' 	=> $content,
				'type' 		=> 'checkbox',
				'options' 	=> '',
				'check' 	=> 'is_empty',
				'width' 	=> '',
				'av_uid' 	=> '',
				'class'		=> 'av_form_privacy_check av_mailchimp_privacy_check',
			);
			
			$fields = array_merge(
	            array_slice($fields, 0, $pos),
	            $new_fields,
	            array_slice($fields, $pos)
	        );
			
			return $fields ;
		}

		
		/**
		 * Adds a checkbox field to the registration form
		 * 
		 * @since 4.4.1
		 * @added_by Günter
		 */
		public function av_privacy_register_extra()
		{
			$args = array(
							'id'			=> 'registration-form-av-privatepolicy',
							'content'		=> avia_get_option( 'privacy_message_registration' ),
							'extra_class'	=> ''
					);
			
			
			echo $this->privacy_checkbox_field( $args );
		}
		
		/**
		 * Adds a checkbox field to the login form
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 */
		public function av_privacy_login_extra()
		{
			$args = array(
							'id'			=> 'login-form-av-privatepolicy',
							'content'		=> avia_get_option( 'privacy_message_login' ),
							'extra_class'	=> 'forgetmenot'
					);
			
			
			echo $this->privacy_checkbox_field( $args );
		}
		
		/**
		 * Authenticate the extra checkbox in the user login screen
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		public function av_authenticate_user_acc( $user, $password )
		{
			//	Check if our checkbox was displayed at all
			if ( ! isset(  $_REQUEST['fake-login-form-av-privatepolicy'] ) )
			{
				return $user;
			}
					
			// See if the checkbox #login_accept was checked
		    if( isset( $_REQUEST['login-form-av-privatepolicy'] ) ) 
			{
		        // Checkbox on, allow login
		        return $user;
		    } 
			else 
			{
		        // Did NOT check the box, do not allow login
				$error_message = apply_filters( 'avf_privacy_login_checkbox_error_message', __( 'You must acknowledge and agree to the privacy policy' , 'avia_framework' ) );
				
		        $error = new WP_Error();
		        $error->add('did_not_accept', $error_message );
		        return $error;
		    }
		}
		
		/**
		 * Authenticate the extra checkbox in the registration login screen
		 * 
		 * @since 4.4.1
		 * @added_by Günter
		 * @param WP_Error $errors
		 * @param string $sanitized_user_login
		 * @param string $user_email
		 * @return \WP_Error
		 */
		public function av_registration_errors( WP_Error $errors, $sanitized_user_login, $user_email )
		{
			//	Check if our checkbox was displayed at all
			if ( ! isset(  $_REQUEST['fake-registration-form-av-privatepolicy'] ) )
			{
				return $errors;
			}
			
			// See if the checkbox #login_accept was checked
		    if ( isset( $_REQUEST['registration-form-av-privatepolicy'] ) ) 
			{
		        // Checkbox on, allow login
		        return $errors;
		    } 
			else 
			{
		        // Did NOT check the box, do not allow login
				$error_message = apply_filters( 'avf_privacy_registration_checkbox_error_message', __( 'You must acknowledge and agree to the privacy policy to register' , 'avia_framework' ) );
				
		        $error = new WP_Error();
		        $error->add('did_not_accept', $error_message );
		        return $error;
		    }
			
			return $errors;
		}
		
		/**
		 * Creates a modal window informing the user about the use of cookies on the site
		 * Sets a cookie when the confirm button is clicked, and hides the box.
		 * Resets the cookie once either of the text in the box is changed in theme options
		 *
		 * @author tinabillinger
		 * @since 4.3
		 * @since 4.5.7.2 modified by Günter - moved from functions-enfold.php
		 */
		public function av_cookie_consent_bar()
		{
			$option = $this->get_cookie_consent_message_bar_option();
			if( 'disabled' == $option )
			{
				return;
			}
			
			$output = '';

			$message = do_shortcode( avia_get_option( 'cookie_content' ) );
			$buttons = avia_get_option( 'msg_bar_buttons', array() );
			$buttontext = avia_get_option( 'cookie_buttontext' );		//	backwards comp.

			$position = avia_get_option( 'cookie_position' );
			$body_layout = avia_get_option( 'color-body_style' );
			$style = '';
			$container_class = '';
			
			switch( $option )
			{
				case 'hide_bar':
					$container_class .= 'cookiebar-hidden-permanent';
					break;
			}
			
			if( $body_layout == 'av-framed-box' ) 
			{
				$frame_width = avia_get_option( 'color-frame_width', 0 );

				$atts = array(
							'width'		=> 'calc(100% - ' . ( $frame_width * 2 ) . 'px)',
							'left'		=> $frame_width,
							'bottom'	=> $frame_width,
							'top'		=> $frame_width,
							'left'		=> $frame_width,
							'right'		=> $frame_width,
						);

				if( $position == 'top' || $position == 'bottom' ) 
				{
					$style .= AviaHelper::style_string( $atts, 'width', 'width', '' );
					$style .= AviaHelper::style_string( $atts, 'left', 'left', 'px' );
				}

				if( $position == 'top-left' ) 
				{
					$style .= AviaHelper::style_string( $atts, 'left', 'left', 'px' );
					$style .= AviaHelper::style_string( $atts, 'top', 'top', 'px' );
				}

				if( $position == 'top-right' ) 
				{
					$style .= AviaHelper::style_string( $atts, 'right', 'right', 'px' );
					$style .= AviaHelper::style_string( $atts, 'top', 'top', 'px' );
				}

				if( $position == 'bottom-right' ) 
				{
					$style .= AviaHelper::style_string( $atts, 'right', 'right', 'px' );
					$style .= AviaHelper::style_string( $atts, 'bottom', 'bottom', 'px' );
				}

				if( $position == 'bottom-left' ) 
				{
					$style .= AviaHelper::style_string( $atts, 'left', 'left', 'px' );
					$style .= AviaHelper::style_string( $atts, 'bottom', 'bottom', 'px' );
				}

				if( $position == 'top' ) 
				{
					$style .= AviaHelper::style_string( $atts, 'top', 'top', 'px' );
				}
				else if( $position == 'bottom' ) 
				{
					$style .= AviaHelper::style_string( $atts, 'bottom', 'bottom', 'px' );
				}

				$style  = AviaHelper::style_string( $style );
			}
			
			$cookie_contents = $message;
			$link = '';
			if( avia_get_option( 'cookie_infolink' ) == 'cookie_infolink' )
			{
				//	this is for backwards comp only prior 4.3
				$linktext = avia_get_option( 'cookie_linktext' );
				$linksource = avia_get_option( 'cookie_linksource' );
				$cookie_contents .= $linktext;

				$link .= avia_targeted_link_rel( "<a class='avia_cookie_infolink' href='{$linksource}' target='_blank'>{$linktext}</a>" );
			}

			$cookie_contents .= $buttontext;	//	this is for backwards comp only prior 4.3

			foreach( $buttons as $button ) 
			{
				$cookie_contents .= $button['msg_bar_button_label'];
			}

			/**
			 * allows to invalidate cookie setting for hiding when anything changes in message bar text.
			 * @since 4.6.2 we add '||v1.0' to allow upgrading already accepted cookies from 4.5.7 to new needed structure need-opt-in
			 */
			$cookie_contents = md5( $cookie_contents ) . '||v1.0';
			$data = "data-contents='{$cookie_contents}'";
			
			if( '' != avia_get_option( 'cookie_auto_reload' ) )
			{
				$reload  =	'<div class="av-cookie-auto-reload-container">';
				$reload .=		'<h2>' . __( 'Reloading the page', 'avia_framework' ) . '</h2>';
				$reload .=		'<p>';
				$reload .=			__( 'To reflect your cookie selections we need to reload the page.', 'avia_framework' );
				$reload .=		'</p>';
				$reload .=	'</div>';
				
				/**
				 * @since 4.6.3
				 * @param string $reload 
				 * return string
				 */
				$reload = apply_filters( 'avf_auto_reload_message', $reload );
				
				$output .=	'<div class="avia-privacy-reload-tooltip-link-container">';
				$output .=		'<a class="avia-privacy-reload-tooltip-link" aria-hidden="true" href="#" rel="nofollow" data-avia-privacy-reload-tooltip="' . esc_attr( $reload ) . '"></a>';
				$output .=	'</div>';
			}

			$output .=	"<div class='avia-cookie-consent cookiebar-hidden {$container_class} avia-cookiemessage-{$position}' {$data} {$style}>";
			$output .=		'<div class="container">';
			$output .=			"<p class='avia_cookie_text'>{$message}</p>";
			$output .=			$link;
			
			$i = 0;
			$extra_info = '';
			$settings_button = false;
			
			foreach( $buttons as $button )
			{
				$i++;
				if( 'info_modal' == $button['msg_bar_button_action'] )
				{
					$settings_button = true;
				}
				
				$output .= $this->msg_bar_button_html( $button, $i );
			}
			
			if( 'page_load' == avia_get_option( 'modal_popup_window_action' ) && ! $settings_button )
			{
				//	We need a settings button to open the modal in frontend
				$i++;
				$button = array(
								'msg_bar_button_label'		=> __( 'Settings', 'avia_framework' ), 
								'msg_bar_button_action'		=> 'info_modal',
						);
				
				$class = 'hidden';
				
				$output .= $this->msg_bar_button_html( $button, $i, $class );
			}
			
			$output .=		'</div>';
			$output .=	'</div>';
			
			//$post = get_post( 4214);
			//$content = Avia_Builder()->compile_post_content( $post );
			$heading = __( 'Cookie and Privacy Settings', 'avia_framework' );
			$contents = $this->get_default_modal_popup_content();

			if( avia_get_option( 'cookie_info_custom_content' ) == 'cookie_info_custom_content' )
			{
				$heading  = str_replace( "'", "&apos;", avia_get_option( 'cookie_info_content_heading', $heading ) );
				$contents = avia_get_option( 'cookie_info_content', array() );
			}
			
			/**
			 * Fallback fix if essential toggle shortcodes are missing (can happen on custom modal popup prior 4.6).
			 * In this case we add them and hide with CSS. We assume that users accept Cookies - which is the default - 
			 * Users can refuse cookies with an own action button.
			 */
			$essential_sc = array(
								'av_privacy_allow_cookies' => 'aviaPrivacyRefuseCookiesHideBar', 
								'av_privacy_accept_essential_cookies' => 'aviaPrivacyEssentialCookiesEnabled' 
							);
			$hidden_toggles = '';
			
			foreach( $contents as $c_key => $content_block ) 
			{
				foreach( $essential_sc as $sc_value => $cookie ) 
				{
					if( false === strpos( $content_block['content'], '[' . $sc_value ) )
					{
						continue;
					}
					
					unset( $essential_sc[ $sc_value ] );
				}
				
				if( empty( $essential_sc ) )
				{
					break;
				}
			}
			
			if( ! empty( $essential_sc ) )
			{
				$hidden_toggles = array();
				$hidden_cookies = array();
				
				foreach( $essential_sc as $sc_value => $cookie )
				{
					$hidden_toggles[] = do_shortcode( '[' . $sc_value . ']' );
					$hidden_cookies[] = $cookie;
				}
				
				if( ! empty( $hidden_cookies ) )
				{
					$hidden_cookies = 'data-hidden_cookies="' . implode( ',', $hidden_cookies ) . '"';
				}
				else
				{
					$hidden_cookies = '';
				}
				
				$hidden_toggles = '<div class="av-hidden-escential-sc" ' . $hidden_cookies . '>' . implode( '', $hidden_toggles ) . '</div>';
			}

			$content  = '';
			foreach( $contents as $content_block )
			{
				$tablabel = str_replace( "'", "&apos;", $content_block['label'] );
				$content .= "[av_tab title='{$tablabel}' icon_select='no' icon='ue81f' font='entypo-fontello']";
				$content .= $content_block['content'];
				$content .= "[/av_tab]";
			}
			
			$sc_content = '';
			$sc_content .= "[av_heading tag='h3' padding='10' heading='{$heading}' color='' style='blockquote modern-quote' custom_font='' size='' subheading_active='' subheading_size='15' custom_class='' admin_preview_bg='' av-desktop-hide='' av-medium-hide='' av-small-hide='' av-mini-hide='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' margin='10px,0,0,0'][/av_heading]";
			$sc_content .= '<br />';
			$sc_content .= "[av_hr class='custom' height='50' shadow='no-shadow' position='left' custom_border='av-border-thin' custom_width='100%' custom_border_color='' custom_margin_top='0px' custom_margin_bottom='0px' icon_select='no' custom_icon_color='' icon='ue808' font='entypo-fontello' av_uid='av-jhe1dyat' admin_preview_bg='rgb(255, 255, 255)']";
			$sc_content .= '<br />';
			$sc_content .= "[av_tab_container position='sidebar_tab sidebar_tab_left' boxed='noborder_tabs' initial='1' av_uid='av-jhds1skt']";
			$sc_content .= '<br />';
			$sc_content .= $content;
			$sc_content .= '<br />';
			$sc_content .= "[/av_tab_container]";
			
			$sc_content = do_shortcode( $sc_content );
			
			$sc_content .= '<div class="avia-cookie-consent-modal-buttons-wrap">';
			
			$buttons = avia_get_option( 'modal_popup_window_buttons', array() );
			if( ! empty( $buttons ) )
			{
				//	if no button, then we have an empty label as first button - remove all buttons with empty label
				foreach( $buttons as $index => $button )
				{
					if( empty( $button['modal_popup_button_label'] ) )
					{
						unset( $buttons[ $index ] );
					}
				}
			}
			
			$class = 'avia-cookie-consent-modal-button';
			foreach( $buttons as $button )
			{
				$i++;
				
				$new_button = array();
				
				foreach( $button as $key => $value ) 
				{
					$new_key = str_replace( 'modal_popup_', 'msg_bar_', $key );
					$new_button[ $new_key ] = $value;
				}
				
				$sc_content .= $this->msg_bar_button_html( $new_button, $i, $class );
			}
			
			$sc_content .= '</div>';
			$sc_content .= $hidden_toggles;
			
			$show_close = count( $buttons ) > 0 ? 'avia-hide-popup-close' : '';
			
			$output .= "<div id='av-consent-extra-info' class='av-inline-modal main_color {$show_close}'>{$sc_content}</div>";
			
			$badge = avia_get_option( 'cookie_consent_badge' );
			if( '' != $badge )
			{
				$class = 'av-consent-badge-' . implode( '-', explode( ' ', $badge ) );
				$output .=	'<div id="av-cookie-consent-badge" href="#" title="' . __( 'Open Message Bar','avia_framework' ) . '" aria-hidden="true" ' . av_icon_string( 'closed' ) . ' class="' . $class . '">';
				$output .=		'<span class="avia_hidden_link_text">' . __( 'Open Message Bar','avia_framework' ) . '</span>';
				$output .=	'</div>';
			}
			
			$output .= '</div>';
			
			echo $output;
		}
		
		/**
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param array $button
		 * @param int $count
		 * @param string $class
		 * @return string
		 */
		protected function msg_bar_button_html( array $button, $count, $class = '' )
		{
			$option = $this->get_cookie_consent_message_bar_option();
			
			$html = '';
			
			$btn_class = 'av-extra-cookie-btn ' . $class;
			$label = ! empty( $button['msg_bar_button_label'] ) ? $button['msg_bar_button_label'] : '×';
			$tooltip = ! empty( $button['msg_bar_button_tooltip'] ) ? ' title="' . esc_attr( $button['msg_bar_button_tooltip'] ) . '" ' : '';
			$link  = ! empty( $button['msg_bar_button_link'] ) && $button['msg_bar_button_action'] == 'link' ? $button['msg_bar_button_link'] : '#';

			switch( $button['msg_bar_button_action'] )
			{
				case 'hide_notification':
					$btn_class .= 'message_bar' != $option ? ' avia-cookie-hide-notification' : '  avia-cookie-close-bar ';
					break;
				case 'info_modal':
					$btn_class .= ' avia-cookie-info-btn ';
					break;
				case 'link':
					$btn_class .= ' avia-cookie-link-btn ';
					break;
				case 'select_all':
					$btn_class = ' avia-cookie-close-bar avia-cookie-select-all ' . $class;
					break;
				case '':
				default:
					$btn_class = ' avia-cookie-close-bar ' . $class;
					break;
			}

			$html .=	"<a href='{$link}' class='avia-button avia-color-theme-color-highlight avia-cookie-consent-button avia-cookie-consent-button-{$count} {$btn_class}' {$tooltip}>{$label}</a>";

			return $html;
		}

		/**
		 * Add an additional class to identify that user has to accept cookies (forced opt in for EU DSGVO) and other frontend behaviour
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param string $classes
		 * @return string
		 */
		public function handler_avia_header_class_filter( $classes )
		{
			switch( $this->get_cookie_consent_message_bar_option() )
			{
				case 'message_bar':
					$classes .= ' av-cookies-consent-message-bar-only';
					return $classes;
				case 'hide_bar':
					$classes .= ' av-cookies-consent-hide-message-bar';
					break;
				case 'show_bar':
					$classes .= ' av-cookies-consent-show-message-bar';
					break;
				case 'disabled':
				default:
					$classes .= ' av-cookies-no-cookie-consent';
					return $classes;
			}
			
			switch( $this->get_opt_in_setting() )
			{
				case 'needs_opt_in':
					$classes .= ' av-cookies-needs-opt-in av-cookies-user-needs-accept-button';
					break;
				case 'can_opt_out':
					$classes .= ' av-cookies-can-opt-out av-cookies-user-needs-accept-button';
					break;
				default:
					$classes .= ' av-cookies-can-opt-out av-cookies-user-silent-accept';
					break;
			}
			
			if( 'page_load' == avia_get_option( 'modal_popup_window_action' ) )
			{
				$classes .= ' avia-cookie-consent-modal-show-immediately';
			}
			
			switch( avia_get_option( 'cookie_auto_reload' ) )
			{
				case 'reload_accept':
					$classes .= ' avia-cookie-reload-accept';
					break;
				case 'reload_no_accept':
					$classes .= ' avia-cookie-reload-no-accept';
					break;
				case 'reload_both':
					$classes .= ' avia-cookie-reload-no-accept avia-cookie-reload-accept';
					break;
			}
			
			if( ! current_theme_supports( 'avia_privacy_ignore_browser_settings' ) )
			{
				$classes .= ' avia-cookie-check-browser-settings';
			}
			
			return $classes;
		}
		
		/**
		 * Returns the default content for the modal privacy popup window
		 * 
		 * @since 4.5.7.2
		 * @param string $filter_options		'filter' | 'no_filter'
		 * @return array
		 */
		public function get_default_modal_popup_content( $filter_options = 'filter' )
		{
			$contents = array();
			
			$contents[] = array(	
						'label'		=> __( 'How we use cookies', 'avia_framework' ), 
						'content'	=> __( 'We may request cookies to be set on your device. We use cookies to let us know when you visit our websites, how you interact with us, to enrich your user experience, and to customize your relationship with our website. <br><br>Click on the different category headings to find out more. You can also change some of your preferences. Note that blocking some types of cookies may impact your experience on our websites and the services we are able to offer.', 'avia_framework' )
					);
			
			$c = '';
			$c .= __( 'These cookies are strictly necessary to provide you with services available through our website and to use some of its features.', 'avia_framework' );
			$c .= '<br /><br />';
			$c .= __( 'Because these cookies are strictly necessary to deliver the website, refuseing them will have impact how our site functions. You always can block or delete cookies by changing your browser settings and force blocking all cookies on this website. But this will always prompt you to accept/refuse cookies when revisiting our site.', 'avia_framework' );
			$c .= '<br /><br />';
			$c .= __( 'We fully respect if you want to refuse cookies but to avoid asking you again and again kindly allow us to store a cookie for that. You are free to opt out any time or opt in for other cookies to get a better experience. If you refuse cookies we will remove all set cookies in our domain.', 'avia_framework' );
			$c .= '<br /><br />';
			$c .= __( 'We provide you with a list of stored cookies on your computer in our domain so you can check what we stored. Due to security reasons we are not able to show or modify cookies from other domains. You can check these in your browser security settings.', 'avia_framework' );
			$c .= '<br /><br />';
			$c .= '[av_privacy_allow_cookies]';
			$c .= '<br /><br />';
			$c .= '[av_privacy_accept_essential_cookies]';
			
			$contents[] = array(	
						'label'		=> __( 'Essential Website Cookies', 'avia_framework' ), 
						'content'	=> $c
					);

			$analtics_check = ( 'filter' == $filter_options ) ? avia_get_option( 'analytics' ) : 'yes';			
			if( ! empty( $analtics_check ) )
			{
				$c = '';
				$c .= __( 'These cookies collect information that is used either in aggregate form to help us understand how our website is being used or how effective our marketing campaigns are, or to help us customize our website and application for you in order to enhance your experience.', 'avia_framework' );
				$c .= '<br><br>';
				$c .= __( 'If you do not want that we track your visist to our site you can disable tracking in your browser here:', 'avia_framework' );
				$c .= '<br>';
				$c .= __( ' [av_privacy_google_tracking]', 'avia_framework' );
						
				$contents[] = array(	
						'label'		=> __( 'Google Analytics Cookies', 'avia_framework' ), 
						'content'	=> $c
					);
			}
			
			$c = '';
			$c .= __( 'We also use different external services like Google Webfonts, Google Maps,  and external Video providers.', 'avia_framework' ) . ' ';
			$c .= __( 'Since these providers may collect personal data like your IP address we allow you to block them here. Please be aware that this might heavily reduce the functionality and appearance of our site.', 'avia_framework' ) . ' ';
			$c .= __( 'Changes will take effect once you reload the page.', 'avia_framework' );
			$c .= '<br><br>';
			$c .= __( 'Google Webfont Settings:', 'avia_framework' );
			$c .= '<br>';
			$c .= '[av_privacy_google_webfonts]';
			$c .= '<br><br>';
			$c .= __( 'Google Map Settings:', 'avia_framework' );
			$c .= '<br>';
			$c .= '[av_privacy_google_maps]';
			$c .= __( 'Google reCaptcha Settings:', 'avia_framework' );
			$c .= '<br>';
			$c .= '[av_privacy_google_recaptcha]';
			$c .= '<br><br>';
			$c .= __( 'Vimeo and Youtube video embeds:', 'avia_framework' );
			$c .= '<br>';
			$c .= '[av_privacy_video_embeds]';
			
			
			$contents[] = array(	
						'label'		=> __( 'Other external services', 'avia_framework' ), 
						'content'	=> $c
					);
			
			$custom_cookies = $this->get_custom_cookies();
			if( ! empty( $custom_cookies ) )
			{
				$c = '';
				$c .= __( 'The following cookies are also needed - You can choose if you want to allow them:', 'avia_framework' );
				$c .= '<br><br>';
				
				foreach( $custom_cookies as $custom_cookie ) 
				{
					$c .= "[av_privacy_custom_cookie cookie_name='{$custom_cookie['cookie_name']}']";
					$c .= '<br><br>';
				}
				
				$contents[] = array(	
							'label'		=> __( 'Other cookies', 'avia_framework' ), 
							'content'	=> $c
						);
			
			}
			
			$wp_privacy_page = ( 'filter' == $filter_options ) ? get_option('wp_page_for_privacy_policy') : 'yes';
			if( ! empty( $wp_privacy_page ) )
			{
				$contents[] = array(	
						'label'		=> __( 'Privacy Policy', 'avia_framework' ), 
						'content'	=> __( 'You can read about our cookies and privacy settings in detail on our Privacy Policy Page. <br><br> [av_privacy_link]', 'avia_framework' )
					);

			}
			
			if( current_theme_supports( 'avia_privacy_show_cookie_info' ) )
			{
				$c = '';
				$c .= __( 'The following cookies are currently in use. Due to browser security we are only able to show cookies of your domain.', 'avia_framework' );
				$c .= '<br>';
				$c .= __( 'For other domain cookies please check your browser settings or use a debug tool. Due to browser security we cannot access all information needed to remove a cookie so we might not be able to remove all cookies.', 'avia_framework' );
				$c .= '<br>';
				$c .= '[av_privacy_cookie_info]';

				$contents[] = array(	
							'label'		=> __( 'Stored Site Cookies', 'avia_framework' ), 
							'content'	=> $c
						);
			}

			return $contents;
		}
		
		/**
		 * Checks if user has opt in for essential cookies and additional deactivate service cookies
		 * 
		 * @since 4.5.7.2
		 * @added_by Günter
		 * @param string|array $additional_deactivate_cookies
		 * @return boolean
		 */
		public function user_has_opt_in( $additional_deactivate_cookies = array() )
		{
			$option = av_privacy_helper()->get_cookie_consent_message_bar_option();
			if( in_array( $option, array( 'message_bar', 'disabled' ) ) )
			{
				return true;
			}
			
			if( ! is_array( $additional_deactivate_cookies ) )
			{
				$additional_deactivate_cookies = is_string( $additional_deactivate_cookies ) ? array( $additional_deactivate_cookies ) : array();
			}
			
			$cookie_accepted = isset( $_COOKIE['aviaCookieConsent'] );
			$cookie_allow_hide_bar = isset( $_COOKIE['aviaPrivacyRefuseCookiesHideBar'] );
			$cookie_allow_cookies = isset( $_COOKIE['aviaPrivacyEssentialCookiesEnabled'] );
			
			if( ! ( $cookie_accepted && $cookie_allow_hide_bar && $cookie_allow_cookies ) )
			{
				return false;
			}
			
			foreach( $additional_deactivate_cookies as $cookie ) 
			{
				if( isset( $_COOKIE[ $cookie ] ) )
				{
					return false;
				}
			}
			
			return true;
		}
	}
}




/**
 * Returns the single instance of class av_privacy_helper - avoids the use of globals
 * 
 * @return av_privacy_class
 */
function av_privacy_helper()
{
	return av_privacy_class::instance();
}

add_action( 'init', 'av_privacy_helper', 20 );
