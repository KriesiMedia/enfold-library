Disable Enfold Google ReCAPTCHA using Real Cookie Banner (RCB) Content Blocker.

Related thread: https://kriesi.at/support/topic/add-support-for-enfold-recaptcha-real-cookie-banner/

- In the RCB panel, go to the Services (Cookies) tab and click the Add service button.

- Create a new service from scratch and input the following service configuration.

 Name: Google Services
 Status: Enabled
 Group: Statistics
 Provider: Google
 Purpose: Control Google Services (ReCAPTCHA)
 Privacy policy link: https://policies.google.com/privacy?hl=en-GB
 Legal basis: Consent (Opt-in)
 
-  Enable the "This service does not set any technical cookies on the client of the visitor, but e.g. integrates a script"

- In the Technical handling section, place the following scripts in their respective fields.

Code executed on opt-in:

<script type="module">
	Cookies.set('enableAviaRecaptchaRCB', 1, { expires : 30, path: '/' });
</script>


Code executed on opt-out:

<script type="module">
	Cookies.remove('enableAviaRecaptchaRCB', 1, { path: '/' });
</script>


Code executed on page load:

<script type="module" src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>

<script type="module">
(function ($)
{
    function disableRecaptchaJS()
    {
		var recaptchaEnabled = $("body").is(".av-recaptcha-enabled");
        var recaptchaFront = $("#avia_google_recaptcha_front_script-js");
		var recaptchaAPI = $("#avia_google_recaptcha_api_script")
		var recaptchaArea = $(".av-recaptcha-area");
        
        if (recaptchaEnabled)
        {
            	recaptchaFront.remove();
            	recaptchaAPI.remove();
	    	recaptchaArea.remove();
		
		$('#top').removeClass('av-recaptcha-enabled av-recaptcha-extended-errors');
        } 
		
		return false;
    	}
    
    	$(document).ready(function() 
		{
			if(Cookies.get('enableAviaRecaptchaRCB')) return;

        	disableRecaptchaJS();
    	});
})(jQuery);
</script>
