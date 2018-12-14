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
class Enfold_Recaptcha_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_filter( 'avf_option_page_data_init', array( $this, 'register_admin_option' ), 10, 1 );

		add_action( 'wp_enqueue_scripts', array( $this, 'register_recaptcha_scripts' ), 10 );

		add_action( 'wp_ajax_avia_ajax_recaptcha', array( $this, 'avia_ajax_recaptcha_callback' ), 10 );
		add_action( 'wp_ajax_nopriv_avia_ajax_recaptcha', array( $this, 'avia_ajax_recaptcha_callback' ), 10 );

		add_action('wp_footer', array( $this, 'avia_recaptcha_script' ), 10 );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Enfold_Recaptcha_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Enfold_Recaptcha_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/enfold-recaptcha-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Enfold_Recaptcha_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Enfold_Recaptcha_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/enfold-recaptcha-public.js', array( 'jquery' ), $this->version, false );

	}

	public function register_admin_option( $elements ) {

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
			"name" 	=> __("Placeholder", 'avia_framework'),
			"desc" 	=> __('Text to display after the captcha widget or before the widget validation.', 'avia_framework'),
			"id" 	=> "recaptcha_enfold_desc",
			"type" 	=> "textarea",
			"std" => __('Verification required.', 'avia_framework'),
			);

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

		return $elements;
	}	
 
    public function register_recaptcha_scripts()
    {
        $prefix  = is_ssl() ? "https" : "http";
        $api_url = $prefix.'://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit';
 
        wp_register_script( 'avia-recaptcha-api', $api_url, array( 'avia-default' ), '1.0.0', false);
        wp_enqueue_script(  'avia-recaptcha-api' );
	}
	
	public function avia_ajax_recaptcha_callback()
	{
		$gcaptcha = $_POST['recaptcha'];
	
		if( isset( $gcaptcha ) ) {
			$secret_key = avia_get_option( 'recaptcha_enfold_skey' );
			
			if( empty( $secret_key ) || ! $secret_key ) {
				return new WP_Error( 'enfold-recaptcha-secret-missing', __( "The recaptcha secret key is missing.", "avia_framework" ) );
			}

			$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret_key."&response=".$gcaptcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
			$response = json_decode( $verify, true);
	
			if( $response['success'] ) {
				echo 'success';
			} else {
				echo 'error';
			}
		} else {
			echo 'invalid captcha';
		}
	
		die();
	}

	function avia_recaptcha_script(){
		$public_key = avia_get_option( 'recaptcha_enfold_pkey' );
		$desc = avia_get_option( 'recaptcha_enfold_desc' );

		if( empty( $public_key ) || ! $public_key ) {
			return new WP_Error( 'enfold-recaptcha-public-missing', __( "The recaptcha public key is missing.", "avia_framework" ) );
		}

		?>
		<script type="text/javascript">
			var key = '<?php echo $public_key; ?>';
			var form = jQuery(".avia_ajax_form");
			var button = jQuery(form).find(".button");
			var parent = button.parent(".form_element");
			var captcha = jQuery("<p style='display: inline-block' data-callback='onloadCallback' data-sitekey='<?php echo $public_key; ?>' id='recaptcha-container'></p>");
			var answer = jQuery("<p class='answer'><?php echo $desc; ?></p>");
			var publickey = captcha.data('sitekey');
		
			var createCaptcha = function() {
				button.attr("disabled", "disabled");
				button.css("display", 'none');
				form.attr('action', '');
				captcha.insertBefore(parent);
				answer.insertAfter(parent);
			};
			   
			createCaptcha();
	 
			var onloadCallback = function() {
				grecaptcha.render('recaptcha-container', {
					'sitekey' : key,
					'callback' : 'onSuccessfullCallback'
				});
			};
	 
			var onSuccessfullCallback = function(success) {
				captcha.attr( 'data-capkey', grecaptcha.getResponse() );
				console.log(captcha.data('capkey'));
				onVerifyCallback(captcha.data('capkey'));
			};
	   
			var onVerifyCallback = function( gcaptchakey ) {
				jQuery.ajax({
					type: "POST",
					url: avia_framework_globals.ajaxurl,
					data: {
						recaptcha: gcaptchakey,
						action: 'avia_ajax_recaptcha'
					},
					success: function(response) {
						console.log('success', response);
						if(response == 'success') {
							form.attr('action', window.location.href.replace("#", ""));
							button.removeAttr('disabled');
							button.css("display", 'block');
							jQuery('.answer').remove();
						}
					},
					error: function() {
						console.log('error');
					},
					complete: function() {
						console.log('complete');
					}
				});
			}
		</script>
		<?php
	}
}
