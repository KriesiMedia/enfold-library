<?php

/**
 * Customize your Autoresponder E-Mail
 * ===================================
 * 
 * Copy the following snippet in functions.php and modify HTML template to your need
 * 
 * @param string $out				initialy empty, return empty if you want to use default set autoresponder text
 * @param string $user_input			
 * @param avia_form $contact_form
 * @param array $contact_form_input
 * @return string					valid HTML string including $message for E-Mail output
 * 
 * @version 1.0.0
 * @requires Enfold 4.0.6
 */
function custom_autoresponse_email( $out, $user_input, avia_form $contact_form, array $contact_form_input )
{
	ob_start();
	
	/**
	 * Enter your HTML template or include one
	 */
?>
<div>
<h2>Thank you for contacting us.</h2>
<p>We will answer your request as soon as possible.</p>	
<p>Best regards<br />
Your Enfold Support Team
</p>
</div>
<?php

	$out .= ob_get_contents();
	ob_end_clean();

	$out .= '<div>';
	$out .=		"<br /><br /><strong>" . 'Your Message:' . " </strong><br /><br />" . $user_input;
	$out .= '</div>';
	return $out;
}

add_filter( 'avf_form_custom_autoresponder', 'custom_autoresponse_email', 10, 4 );


