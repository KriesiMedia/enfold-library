<?php

/**
 * This snippet moves the Goolge V3 reCAPTCHA message text before the privacy checkbox for contact forms
 * 
 * @since 4.6.4
 */

function move_v3_google_message()
{
echo
"
<script type='text/javascript'>
(function($)
{
    'use strict';
	
	if( ! $('body').hasClass('av-recaptcha-enabled') )
	{
		return;
	}
	
	$(function() {
	
			var google_terms = $( 'fieldset div.av-google-badge-message');
			google_terms.each( function( index  ){
						var term = $(this);
						var privacy = term.closest('fieldset').find('.av_form_privacy_check');
						if( privacy.length > 0 )
						{
							term.removeClass('hidden');
							privacy.first().before( term );
						}
				});
		
	});
	
})( jQuery );
</script>
";
	
}

add_filter( 'wp_footer', 'move_v3_google_message', 999999 );



