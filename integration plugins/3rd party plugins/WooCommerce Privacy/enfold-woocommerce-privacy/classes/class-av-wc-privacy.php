<?php
/**
 * Class handles adding options, privacy checkboxes to WooCommerce forms and checking
 *
 * @author Günter
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


class Av_WC_Privacy 
{
	/**
	 *
	 * @since 1.0.0
	 * @var string 
	 */
	public $plugin_path;
	
	/**
	 * @since 1.0.0
	 */
	public function __construct() 
	{
		$this->plugin_path = '';
		
		add_action( 'init', array( $this, 'handler_wp_load_textdomains' ), 1 );
		add_filter( 'avf_option_page_data_init', array( $this, 'handler_option_page_data_init' ), 10, 1 );
		
		add_action( 'after_setup_theme', array( $this, 'handler_wp_after_setup_theme'), 10 );
	}
	
	/**
	 * @since 1.0.0
	 */
	public function __destruct() 
	{
		
	}
	
	/**
	 * Add hooks depending on option settings
	 * 
	 * @since 1.0.0
	 */
	public function handler_wp_after_setup_theme()
	{
		if( avia_get_option( 'shop_privacy_message_registration_active' ) == 'shop_privacy_message_registration_active' )
		{
			add_filter( 'woocommerce_checkout_fields', array( $this, 'handler_wc_checkout_fields' ), 10, 1 );
			add_action( 'woocommerce_register_form', array( $this, 'handler_wc_register_form' ), 10, 0 );
			add_filter( 'woocommerce_process_registration_errors', array( $this, 'handler_wc_process_registration_errors' ), 10, 4 );
		}
		
		if( avia_get_option( 'shop_privacy_message_login_active' ) == 'shop_privacy_message_login_active' )
		{
			add_action( 'woocommerce_login_form_end', array( $this, 'handler_wc_login_form_end' ), 10, 0 );
			add_filter( 'woocommerce_process_login_errors', array( $this, 'handler_wc_process_login_errors' ), 10, 3 );
		}
		
		if( ! is_user_logged_in() && ( avia_get_option( 'shop_privacy_message_reviews_active' ) == 'shop_privacy_message_reviews_active' ) )
		{
			add_filter( 'woocommerce_product_review_comment_form_args', array( $this, 'handler_wc_product_review_comment_form_args' ), 999, 1 );
			add_filter( 'preprocess_comment', array( $this, 'handler_wc_check_product_review_checkbox' ), 999, 1 );
		}

	}
	
	/**
	 * Localisation
	 * 
	 * @since v1.0.0
	 **/
	public function handler_wp_load_textdomains()
	{
		$language_path = trailingslashit( $this->plugin_path ) . 'languages';	
		load_plugin_textdomain( 'avia_wc_privacy_ext', false, $language_path );
	}
	
	
	/**
	 * Add shop specific options
	 * 
	 * @since 1.0.0
	 * @param array $avia_elements
	 * @return array
	 */
	public function handler_option_page_data_init( array $avia_elements )
	{
		$shop_msg  = __( "In case you deal with any EU customers/visitors these options allow you to make your shop GDPR compliant", 'avia_wc_privacy_ext' ) . '<br />';
		$shop_msg .= __( "The following default text will be applied if you leave the textfields empty:", 'avia_wc_privacy_ext' ) . '<br />';
		$shop_msg .= '<p><strong>' . av_privacy_class::get_default_privacy_message() . '</strong></p>';

		$avia_elements[] =	array(
							"name"	=> __( 'Privacy Policy for Shop', 'avia_wc_privacy_ext' ),
							"desc"	=> $shop_msg,
							"id"	=> "shop_gdpr_overveiw",
							"std"	=> "",
							"slug"	=> "shop",
							"type"	=> "heading",
							"nodescription"	=> true
						);

		$avia_elements[] =	array(
							"slug"	=> "shop",
							"name" 	=> __( "Append a privacy policy message to your shop login forms?", 'avia_wc_privacy_ext' ),
							"desc" 	=> __( "Check to append a message to the shops login forms.", 'avia_wc_privacy_ext' ),
							"id" 	=> "shop_privacy_message_login_active",
							"type" 	=> "checkbox",
							"std"	=> false,
						);

		$avia_elements[] =	array(
							"slug"	=> "shop",
							"name" 	=> __( "Message below login forms", 'avia_wc_privacy_ext' ),
							"desc" 	=> __( "A short message that can be displayed below shop login forms, along with a checkbox, that lets the user know that he has to agree to your privacy policy in order to allow login. See default text above if you leave empty.", 'avia_wc_privacy_ext' ),
							"id" 	=> "shop_privacy_message_login",
							"type" 	=> "textarea",
							"class" => "av_small_textarea",
							"std" 	=> '',
							"required" => array( "shop_privacy_message_login_active", 'shop_privacy_message_login_active' ),
						);

		$avia_elements[] =	array(
							"slug"	=> "shop",
							"name" 	=> __( "Append a privacy policy message to your shop register forms?", 'avia_wc_privacy_ext' ),
							"desc" 	=> __( "Check to append a message to the shops registrations forms.", 'avia_wc_privacy_ext' ),
							"id" 	=> "shop_privacy_message_registration_active",
							"type" 	=> "checkbox",
							"std"	=> false,
						);

		$avia_elements[] =	array(
							"slug"	=> "shop",
							"name" 	=> __( "Message below registration forms", 'avia_wc_privacy_ext' ),
							"desc" 	=> __( "A short message that can be displayed below shop registration forms, along with a checkbox, that lets the user know that he has to agree to your privacy policy in order to allow registration. See default text above if you leave empty.", 'avia_wc_privacy_ext' ),
							"id" 	=> "shop_privacy_message_registration",
							"type" 	=> "textarea",
							"class" => "av_small_textarea",
							"std" 	=> '',
							"required" => array( "shop_privacy_message_registration_active", 'shop_privacy_message_registration_active' ),
						);

		$avia_elements[] =	array(
							"slug"	=> "shop",
							"name" 	=> __( "Append a privacy policy message to your product review forms?", 'avia_wc_privacy_ext' ),
							"desc" 	=> __( "Check to append a message to the single product review forms.", 'avia_wc_privacy_ext' ),
							"id" 	=> "shop_privacy_message_reviews_active",
							"type" 	=> "checkbox",
							"std"	=> false,
						);

		$avia_elements[] =	array(
							"slug"	=> "shop",
							"name" 	=> __( "Message below product review forms", 'avia_wc_privacy_ext' ),
							"desc" 	=> __( "A short message that can be displayed below single product review forms, along with a checkbox, that lets the user know that he has to agree to your privacy policy in order to allow posting the review. See default text above if you leave empty.", 'avia_wc_privacy_ext' ),
							"id" 	=> "shop_privacy_message_reviews",
							"type" 	=> "textarea",
							"class" => "av_small_textarea",
							"std" 	=> '',
							"required" => array( "shop_privacy_message_reviews_active", 'shop_privacy_message_reviews_active' ),
						);

		
		return $avia_elements;
	}




	/**
	 * Add a privacy checkbox for registration on checkout forms
	 * 
	 * @since 4.4.1
	 * @added_by Günter
	 * @param array $fields 
	 * @return array
	 */
	function handler_wc_checkout_fields( array $fields  )
	{
		$content = do_shortcode( avia_get_option( 'shop_privacy_message_registration' ) );
		if( empty( $content ) ) 
		{
			$content = do_shortcode( avia_get_option( 'privacy_message' ) );
		}
		
		$fields['account']['av_confirm_privacy'] = array(
				'type'			=> 'checkbox',
				'label'			=> $content,
				'required'		=> true,
			);
		
		return $fields;
	}


	/**
	 * Add a privacy checkbox on register forms
	 * 
	 * @since 4.4.1
	 * @added_by Günter
	 */
	function handler_wc_register_form()
	{	
		$content = do_shortcode( avia_get_option( 'shop_privacy_message_registration' ) );
		if( empty( $content ) ) 
		{
			$content = do_shortcode( avia_get_option( 'privacy_message' ) );
		}
		
?>		
		<p class="form-row">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox inline">
				<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="av_shop_privacy_confirm_registration" type="checkbox" id="av_confirm_privacy" value="av_shop_privacy_confirm_registration" /> <span><?php echo $content; ?></span>
			</label>
		</p>
<?php
	}



	/**
	 * Check privacy checkbox on register form
	 * 
	 * @since 4.4.1
	 * @added_by Günter
	 * @param WP_Error $validation_error
	 * @param string $username
	 * @param string $password
	 * @param string $email
	 * @return \WP_Error
	 */
	function handler_wc_process_registration_errors( WP_Error $validation_error, $username, $password, $email )
	{
		if( ! isset( $_REQUEST['av_shop_privacy_confirm_registration'] ) || ( $_REQUEST['av_shop_privacy_confirm_registration'] != 'av_shop_privacy_confirm_registration' ) )
		{
			$reg_error = apply_filters( 'avf_shop_privacy_register_error_message', __( 'You must acknowledge and agree to the shop privacy policy to register' , 'avia_wc_privacy_ext' ) );
			$validation_error->add( 'privacy', $reg_error );
		}
		
		return $validation_error;
	}

	/**
	 * Add a privacy checkbox on login forms
	 * 
	 * @since 4.4.1
	 * @added_by Günter
	 */
	function handler_wc_login_form_end()
	{
		$content = do_shortcode( avia_get_option( 'shop_privacy_message_login' ) );
		if( empty( $content ) ) 
		{
			$content = do_shortcode( avia_get_option( 'privacy_message' ) );
		}
		
?>		
		<p class="form-row">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox inline">
				<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="av_shop_privacy_confirm_login" type="checkbox" id="av_confirm_privacy" value="av_shop_privacy_confirm_login" /> <span><?php echo $content; ?></span>
			</label>
		</p>
<?php
	}


	/**
	 * Check privacy checkbox on login form
	 * 
	 * @since 4.4.1
	 * @added_by Günter
	 * @param WP_Error $validation_error
	 * @param string $username
	 * @param string $password
	 * @return \WP_Error
	 */
	function handler_wc_process_login_errors( WP_Error $validation_error, $username, $password )
	{
		if( ! isset( $_REQUEST['av_shop_privacy_confirm_login'] ) || ( $_REQUEST['av_shop_privacy_confirm_login'] != 'av_shop_privacy_confirm_login' ) )
		{
			$reg_error = apply_filters( 'avf_shop_privacy_login_error_message', __( 'You must acknowledge and agree to the shop privacy policy to login' , 'avia_wc_privacy_ext' ) );
			$validation_error->add( 'privacy', $reg_error );
		}
		
		return $validation_error;
	}


	/**
	 * Add a checkbox to review comment form
	 * 
	 * @since 4.4.1
	 * @added_by Günter
	 * @param array $comment
	 * @return array
	 */
	function handler_wc_product_review_comment_form_args( array $comment = array() )
	{
		$args = array(
							'id'			=> 'review-form-av-privatepolicy',
							'content'		=> avia_get_option( 'shop_privacy_message_reviews' ),
							'extra_class'	=> '',
							'attributes'	=> 'aria-required="true" required=""'
					);
			
		$comment['fields']['review-form-av-privatepolicy'] = av_privacy_helper()->privacy_checkbox_field( $args );
			
		return $comment;
	}

	/**
	 * Check for product review checkbox
	 * 
	 * @since 4.4.1
	 * @added_by Günter
	 * @param array $comment_data
	 * @return array
	 */
	function handler_wc_check_product_review_checkbox( $comment_data )
	{
		if( is_user_logged_in() || ! isset( $_REQUEST['fake-review-form-av-privatepolicy'] ) )
		{
			return $comment_data;
		}
		
		if( isset( $_REQUEST['review-form-av-privatepolicy'] ) )
		{
			return $comment_data;
		}
		
		/*
		 * This is a fallback only - check should be handled in frontend by woocommerce attributes already
		 */
		$error_message = apply_filters( 'avf_review_form_privacy_checkbox_error_message', __( 'You must acknowledge and agree to the privacy policy of our shop to be able to post reviews.' , 'avia_wc_privacy_ext' ) );
		wp_die( esc_html( $error_message ) );
		exit;
	}
	
}	
