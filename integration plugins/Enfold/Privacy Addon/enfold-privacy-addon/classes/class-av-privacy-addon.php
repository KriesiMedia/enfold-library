<?php
/**
 * Class handles 
 *	- adding options to customize WP Cookie Consent Checkbox for standard WP comment fields
 *	- a shortcode display a button that allows to show the modal privacy popup from any place
 *			[av_privacy_popup wrapper_class='' id='' class='']button text[/av_privacy_popup]
 *
 * @author Günter
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


class Av_Privacy_AddOn 
{

	/**
	 *
	 * @since 1.0.0
	 * @var string 
	 */
	public $plugin_path;
	
	/**
	 *
	 * @since 1.0.0
	 * @var string 
	 */
	public $plugin_base_name;
	
	/**
	 * 
	 * @since 1.0.0
	 */
	public function __construct() 
	{
		$this->plugin_path = '';
		$this->plugin_base_name= '';
		
		add_action( 'init', array( $this, 'handler_wp_load_textdomains' ), 1 );
		add_filter( 'avf_option_page_data_init', array( $this, 'handler_option_page_data_init' ), 10, 1 );
		
		add_shortcode( 'av_privacy_popup', array( $this, 'handler_sc_privacy_popup' ) );
			
		/**
		 * Hook to change WP default checkbox "Save my name, email, and website in this browser for the next time I comment"
		 */
		add_filter( 'comment_form_default_fields', array( $this, 'handler_wp_comment_form_default_fields' ), 10, 1 );
	}
	
	/**
	 * 
	 */
	public function __destruct() 
	{
		
	}
	
	
	/**
	 * Localisation
	 * 
	 * @since 1.0.0
	 **/
	public function handler_wp_load_textdomains()
	{
		$pos = strrpos( $this->plugin_base_name, '/' );
		if( $pos === false )
		{
			$pos = strrpos( $this->plugin_base_name, '\\' );
		}
		
		$language_path = ( $pos === false ) ? 'languages' : trailingslashit ( substr( $this->plugin_base_name, 0, $pos + 1 ) ) . 'languages';		
		load_plugin_textdomain( 'avia_privacy_addon', false, $language_path );
	}
	
		
	/**
	 * Add WP consent specific options
	 * 
	 * @since 1.0.0
	 * @param array $avia_elements
	 * @return array
	 */
	public function handler_option_page_data_init( array $avia_elements )
	{
		$eu_msg  = __( "In case you deal with any EU customers/visitors this options allow you to make your site GDPR compliant.", 'avia_privacy_addon' );

		$avia_elements[] =	array(
					"name"	=> __( 'WordPress Cookie Consent Checkbox', 'avia_privacy_addon' ),
					"desc"	=> $eu_msg,
					"id"	=> "gdpr_wp_comments",
					"std"	=> "",
					"slug"	=> "cookie",
					"type"	=> "heading",
					"nodescription"	=> true
				);
		
		$avia_elements[] =	array(
					"slug"	=> "cookie",
					"name" 	=> __( "Show standard WordPress comment form checkbox ?", 'avia_privacy_addon' ),
					"desc" 	=> __( "Check to show the checkbox &quot;Save my name, email, and website in this browser for the next time I comment&quot; added by WP since 4.9.6", 'avia_privacy_addon' ),
					"id" 	=> "privacy_message_wp_comments_active",
					"type" 	=> "checkbox",
					"std"	=> false,
				);
 
		$avia_elements[] =	array(
					"slug"	=> "cookie",
					"name" 	=> __( "Message to display", 'avia_privacy_addon'),
					"desc" 	=> __( "Enter the text you want to display to the user - leave blank for WP default message.", 'avia_privacy_addon' ),
					"id" 	=> "wp_comments_privacy_message",
					"type" 	=> "textarea",
					"class" => "av_small_textarea",
					"std" 	=> '',
					"required" => array( "privacy_message_wp_comments_active", 'privacy_message_wp_comments_active' ),
				);
		
		
		$avia_elements[] = array(
					"name"	=> __( "Link to Modal Window with Privacy and Cookie Info", 'avia_privacy_addon' ),
					"desc"	=> __( "You can add a button to open this window any time by using the following shortcode (all parameters are optional):<br /><p><strong>[av_privacy_popup button_text='your custom text' wrapper_class='' id='' class=''] </strong>or</p><p><strong>[av_privacy_popup wrapper_class='' id='' class='']your button text[/av_privacy_popup]</strong></p>", 'avia_privacy_addon' ),
					"std"	=> "",
					"slug"	=> "cookie",
					"type"	=> "heading",
					"nodescription"	=> true
				);
		
		return $avia_elements;
	}
	
	/**
	 * Returns a button to open the privacy popup window
	 * 
	 * @since 1.0.0
	 * @added_by Günter
	 * @param array $atts
	 * @param string $content
	 * @param string $shortcodename
	 */
	public function handler_sc_privacy_popup( $atts = array(), $content = "", $shortcodename = "" )
	{
		$atts = shortcode_atts( array( 
							'wrapper_class'	=> '',
							'id'			=> '',
							'class'			=> ''
						), $atts, $shortcodename );

		$out = '';

		$content = ! empty( $content ) ? $content : __( 'Learn more about our privacy policy', 'avia_privacy_addon' );

		$class = 'avia-button avia-cookie-consent-button avia-cookie-info-btn ' . $atts['class'];
		$id = ! empty( $atts['id'] ) ? " id='{$atts['id']}'" : '';

		$out .=	"<div class='av-privacy-popup-button-wrap {$atts['wrapper_class']}'>";
		$out .=		"<a href='#' class='{$class}' $id>{$content}</a>";
		$out .= '</div>';

		return $out;
	}

	/**
	 * Allow to modify the standard WP checkbox Save my name, email, and website in this browser for the next time I comment
	 * added by WP to a comment forms since WP 4.9.6
	 * 
	 * @since 1.0.0
	 * @added_by Günter
	 * @param array $fields
	 * @return array
	 */
	public function handler_wp_comment_form_default_fields( array $fields )
	{
		if( avia_get_option( 'privacy_message_wp_comments_active' ) != 'privacy_message_wp_comments_active' )
		{
			unset( $fields['cookies'] );
			return $fields;
		}

		if( empty( avia_get_option( 'wp_comments_privacy_message' ) ) )
		{
			$fields['cookies'] = str_replace( array( 'checked="checked"', "checked='checked'" ), '', $fields['cookies'] );
			return $fields;
		}

		//	rebuild the default structure with custom user message
		$out = '';
		$out .= '<p class="comment-form-cookies-consent">';
		$out .=		'<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes" />';
		$out .=		'<label for="wp-comment-cookies-consent">' . esc_html( avia_get_option( 'wp_comments_privacy_message') ) . '</label>;';
		$out .=	'</p>';

		$fields['cookies'] = $out;

		return $fields;
	}

	
}
