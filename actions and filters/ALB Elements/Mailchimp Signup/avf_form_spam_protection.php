<?php

/**
 * Customize your extended spam protection for contact form and mailchimp
 * ======================================================================
 * 
 * Copy the following snippet in functions.php and modify to your need 
 * 
 * @param array $spam_protect			see code below for parameters
 * @param array $atts
 * @param object $caller				'avia_sc_contact' | 'avia_sc_mailchimp'
 * @return array
 * 
 * @version 1.0.0
 * @requires Enfold 4.1.3
 */
function my_spam_protection( array $spam_protect, array $atts, $caller )
{
	/**
	 * If you defined an ID for a form, check to adjust values 
	 */
//	if( $atts['id'] != 'your_custom_id' )
//	{
//		return $spam_protect;
//	}
	
	/**
	 * Use spam protection in fromtend - avoids sending the form when the expire timeout is reached
	 */
//	$spam_protect['frontend'] = 'yes';			//	'no' | 'yes'
	
	/**
	 * Use spam protection in backend - do not send form data when the expire timeout is reached and block multiple sending of the same form
	 */
//	$spam_protect['backend'] = 'yes';			//	'no' | 'yes'
	
	/**
	 * Use spam protection in fromtend - avoids sending the form when the expire timeout is reached
	 */
//	$spam_protect['expire'] = 15;			//	minutes a form can be submitted and will be accepted, <= 0 for unlimited (will be set to 24h by default)
	
	return $spam_protect;
}

add_filters( 'avf_form_spam_protection', 'my_spam_protection' );
