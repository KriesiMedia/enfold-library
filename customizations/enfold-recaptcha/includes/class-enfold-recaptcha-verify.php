<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       kriesi.at
 * @since      1.0.0
 *
 * @package    Enfold_Recaptcha
 * @subpackage Enfold_Recaptcha/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Enfold_Recaptcha
 * @subpackage Enfold_Recaptcha/public
 * @author     Enfold <ismael@kriesi.at>
 */
class Enfold_Recaptcha_Verify {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The google reCAPTCHA keys.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array   
	 */
	private $sitekeys;

	/**
	 * The google reCAPTCHA site verify url.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string   
	 */
	private $verify_url;

	/**
	 * Are you human?
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string   
	 */
	public $is_human = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->verify_url = 'https://www.google.com/recaptcha/api/siteverify';
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_filter( 'avf_option_page_data_init', array( $this, 'register_admin_options' ), 10, 1 );
		add_filter( 'avf_ajax_form_class', array( $this, 'modify_form_class' ), 10, 1 );

		add_action( 'after_setup_theme', array( $this, 'get_api_keys' ), 10 );
		
		add_filter( 'avf_contact_form_submit_button_attr', array( $this, 'modify_button_attributes' ), 10, 3 );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'register_recaptcha_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_recaptcha_scripts' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_api_script' ), 10 );
		
		add_action( 'wp_ajax_avia_ajax_recaptcha_verify', array( $this, 'verify_token' ), 10 );
		add_action( 'wp_ajax_nopriv_avia_ajax_recaptcha_verify', array( $this, 'verify_token' ), 10 );

		add_filter( 'avf_form_send', array( $this, 'is_human' ), 10, 1 );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function register_recaptcha_styles() {
		wp_register_style(
			'avia-recaptcha',
			plugin_dir_url( __FILE__ ) . 'css/avia-recaptcha.css',
			array( 'avia-dynamic' ),
			$this->version,
			false
		);

		wp_enqueue_style( 'avia-recaptcha' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function register_recaptcha_scripts() {
		$notice = $this->get_notice();

		wp_register_script(
			'avia-recaptcha',
			plugin_dir_url( __FILE__ ) . 'js/avia-recaptcha.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_enqueue_script( 'avia-recaptcha' );

		wp_localize_script(
			'avia-recaptcha',
			'avia_recaptcha',
			array(
				'nonce' => wp_create_nonce( 'avia_recaptcha_verify' ),
		) );

		wp_add_inline_script( 'avia-recaptcha', '
			var notices = document.getElementsByClassName( "g-recaptcha-notice" );	
			
			for ( var i = 0; i < notices.length; i++ ) {
				var text = document.createTextNode( "'. $notice .'" );	
				notices[ i ].appendChild( text );
			}
		' );	
	}

	public function get_api_keys() {
		$secret_key = avia_get_option( 'recaptcha_enfold_skey' );
		$public_key = avia_get_option( 'recaptcha_enfold_pkey' );

		if( empty( $secret_key ) || empty( $public_key ) ) {
			return new WP_Error( 'enfold-recaptcha-sitekeys-missing', __( "The recaptcha keys key are missing.", "avia_framework" ) );
		}

		$this->sitekeys = array(
			'public' => $public_key,
			'secret' => $secret_key
		);
	}

	public function get_public_key() {
		if ( empty( $this->sitekeys ) || ! is_array( $this->sitekeys ) ) {
			return false;
		}
	
		return $this->sitekeys['public'];
	}

	public function get_secret_key() {
		if ( empty( $this->sitekeys ) || ! is_array( $this->sitekeys ) ) {
			return false;
		}

		return $this->sitekeys['secret'];
	}

	public function get_notice() {
		$notice = avia_get_option( 'recaptcha_enfold_notice' );
		return $notice;
	}

	public function is_recaptcha_active() {
		$secret_key = $this->get_secret_key();
		$public_key = $this->get_public_key();
		return $public_key && $secret_key;
	}

	public function modify_form_class( $class ) {
		if( ! $this->is_recaptcha_active() ) {
			return false;
		}

		$class .= ' avia-form-recaptcha';

		return $class;
	}

	public function modify_button_attributes( $atts, $id, $params ) {
		if ( empty( $this->sitekeys ) || ! is_array( $this->sitekeys ) ) {
			return false;
		}

		$public_key = $this->get_public_key();
		$notice = $this->get_notice();
		
		$atts .= ' disabled=disabled';
		$atts .= ' data-notice=' . urlencode( $notice );
		$atts .= ' data-sitekey=' . $public_key;
		$atts .= ' data-theme=light';
		$atts .= ' data-size=normal';
		$atts .= ' data-tabindex=' . $id;
		$atts .= ' data-callback=aviaRecaptchaSuccess';
		
		return $atts;
	} 

	public function register_admin_options( $elements ) {
		$elements[] =	array(
			"name" => __("Google ReCAPTCHA V2",'avia_framework'),
			"desc" => __("Add a Google reCAPTCHA widget on the theme's contact form element.",'avia_framework'),
			"id" => "recaptcha_enfold",
			"std" => "",
			"slug"	=> "google",
			"type" => "heading",
			"nodescription"=>true);

		$elements[] =	array(
			"slug"	=> "google",
			"name" 	=> __("Site Key", 'avia_framework'),
			"desc" 	=> __('Enter the  public key here.', 'avia_framework'),
			"id" 	=> "recaptcha_enfold_pkey",
			"type" 	=> "text"
			);

		$elements[] =	array(
			"slug"	=> "google",
			"name" 	=> __("Secret Key", 'avia_framework'),
			"desc" 	=> __("Enter the secret key here.", 'avia_framework'),
			"id" 	=> "recaptcha_enfold_skey",
			"type" 	=> "text"
			);

		$elements[] =	array(
			"slug"	=> "google",
			"name" 	=> __("Widget Notice", 'avia_framework'),
			"desc" 	=> __('Text to display before the widget validation.', 'avia_framework'),
			"id" 	=> "recaptcha_enfold_notice",
			"type" 	=> "text",
			"std" => __('Verification required.', 'avia_framework'),
			);

		return $elements;
	}	

    public function register_api_script()
    {
        $prefix = is_ssl() ? "https" : "http";	
		$api_url = $prefix . '://www.google.com/recaptcha/api.js';
		$api_url = add_query_arg( array(
			'onload' => 'aviaRecaptchaRender',
			'render' => 'explicit',
		), $api_url );

		wp_register_script( 'avia-recaptcha-api', $api_url, array( 'avia-default' ), '1.0.0', false);
		wp_enqueue_script( 'avia-recaptcha-api' );
	}

	public function verify_token()
	{
		$is_humanoid = false;
		$g_recaptcha_token = $this->get_recaptcha_response();

		if( empty( $g_recaptcha_token ) ) {
			return $is_humanoid;
		}

		if( ! $this->is_recaptcha_active() ) {
			return $is_humanoid;
		}

		$verify_url = $this->verify_url;
		$secret_key = $this->get_secret_key();

		$response = wp_safe_remote_post( $verify_url, array(
			'body' => array(
				'secret' => $secret_key,
				'response' => $g_recaptcha_token,
				'remoteip' => $_SERVER['REMOTE_ADDR'],
			),
		) );
		
		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			return $is_humanoid;
		}

		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response, true );

		$is_humanoid = isset( $response['success'] ) && true == $response['success'];

		$this->is_human = $is_humanoid;

		wp_die($is_humanoid);
	}

	public function is_human( $human ) {
		$human = $this->is_human;
		return $human;
	}

	public function get_recaptcha_response() {
		if ( isset( $_POST['g_recaptcha_token'] ) ) {
			return $_POST['g_recaptcha_token'];
		}
	
		return false;
	}
}

