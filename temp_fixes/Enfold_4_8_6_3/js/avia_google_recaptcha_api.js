/* 
 * AVIA GOOGLE reCAPTCHA API
 * =========================
 * 
 * This file holds javascript functions needed in frontend for the functionallity of the Google reCaptcha API and widget
 * Handles conditional loading of Google reCaptcha API script.
 * 
 * As first step we limit this to contact forms (context av_contact_form) - but it can be extended in future.
 *
 * @author		Günter for Christian "Kriesi" Budschedl
 * @copyright	Copyright ( c ) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 * @since		Enfold 4.5.7.2
 */


(function($)
{
	var obj_avia_recaptcha = null;
	
	var avia_recaptcha = function()
	{
		if( 'undefined' == typeof AviaReCAPTCHA_front )
		{
			return;
		}
		
		this.body = $( 'body' );
		if( ! this.body.hasClass( 'av-recaptcha-enabled' ) )
		{
			return;
		}
		
		this.self = this;
		this.errors = [];				//	errors visible for all users
		this.error_report = [];			//	errors only visible if class 'av-recaptcha-extended-errors' is set in body (usual only for admins)
		this.monitor_V3_request = false;
		this.document = $( document );
		
		this.version = AviaReCAPTCHA_front.version;
		this.site_key2 = AviaReCAPTCHA_front.site_key2;
		this.site_key3 = AviaReCAPTCHA_front.site_key3;
		this.api = AviaReCAPTCHA_front.api;
		this.theme = AviaReCAPTCHA_front.theme;
		this.score = AviaReCAPTCHA_front.score;
		this.size = 'normal';
		this.cannot_use = AviaReCAPTCHA_front.cannot_use;
		this.init_error_msg = AviaReCAPTCHA_front.init_error_msg;
		this.v3_timeout_pageload = AviaReCAPTCHA_front.v3_timeout_pageload;
		this.v3_timeout_verify = AviaReCAPTCHA_front.v3_timeout_verify;
		this.v2_timeout_verify = AviaReCAPTCHA_front.v2_timeout_verify;
		this.invalid_version = AviaReCAPTCHA_front.invalid_version;
		this.validate_first = AviaReCAPTCHA_front.validate_first;
		this.validate_submit = AviaReCAPTCHA_front.validate_submit;
		this.verify_nonce = AviaReCAPTCHA_front.verify_nonce;
		this.verify_msg = AviaReCAPTCHA_front.verify_msg;
		this.connection_error = AviaReCAPTCHA_front.connection_error;
		this.no_token = AviaReCAPTCHA_front.no_token;
		this.api_load_error = AviaReCAPTCHA_front.api_load_error;
		
		var obj = this.self;
		
		this.body.find( '.av-recaptcha-area' ).each( function( index, element ) {
										obj.prepare_recaptcha_area( index, element, obj );
									});
		
		//	Remember: To allow Google to monitor V3 we do not return now when no recaptcha_areas  
		this.recaptcha_areas = this.body.find( '.av-recaptcha-area' );
		
		if( ! this.verifySettings() )
		{
			this.showErrors( 'remove_buttons' );
			return;
		}
		
		this.loadMainAPI();
	};
	
	avia_recaptcha.prototype = {
		
		verifySettings: function()
		{
			/**
			* Provide a fallback to V2 in case any parameter does not fit
			*/
			if( -1 == $.inArray( this.version, [ 'avia_recaptcha_v2', 'avia_recaptcha_v3'] ) )
			{
				this.version = 'avia_recaptcha_v2';
			}
		
			if( 'avia_recaptcha_v3' == this.version && ( '' == this.site_key3 ) )
			{
				this.version = 'avia_recaptcha_v2';
			}
			
			if( '' == this.site_key2 )
			{
				this.errors.push( this.cannot_use );
				this.error_report.push( this.init_error_msg );
				
				return false;
			}
			
			return true;
		},
		
		prepare_recaptcha_area: function( index, element, self )
		{
			var container = $( element );
			var token_input = ( 'undefined' != typeof container.data( 'token_input' ) ) ? container.data( 'token_input' ) : 'avia_token_verify';
			var token_input_version = token_input + '-version';
			var version = ( 'undefined' != typeof container.data( 'version' ) ) ? container.data( 'version' ) : '';
			var context = ( 'undefined' != typeof container.data( 'context' ) ) ? container.data( 'context' ) : 'av_contact_form';
			var badge_msg = container.find( '.av-google-badge-message' ).first();
			
			if( 'av_contact_form' != context )
			{
				container.removeClass( 'av-recaptcha-area' ).addClass( 'av-recaptcha-area-unsupported' );
				return;
			}
			
			if( -1 == $.inArray( version, [ 'avia_recaptcha_v2', 'avia_recaptcha_v3' ] ) )
			{
				version = 'avia_recaptcha_v2';
			}
			
			if( ( 'avia_recaptcha_v3' == version ) && ( 'avia_recaptcha_v2' == self.version ) )
			{
				version = 'avia_recaptcha_v2';
			}
			
			var id = this.unique_id( index );
			
			container.data( 'token_input', token_input );
			container.data( 'version', version );
			container.data( 'context', context );
			container.data( 'unique_recaptcha_id', id );
			container.addClass( id );
			
			container.before( '<input class="av-recaptcha-verify-token is_empty ' + id + '" type="hidden" value="" name="' + token_input + '">' );
			container.before( '<input class="av-recaptcha-verify-token-version ' + id + '" type="hidden" value="" name="' + token_input_version + '">' );
			container.before( '<div class="av-recaptcha-error"></div>' );
			
			var form = container.closest( 'form' );
			form.addClass( version );
			var submit = form.find( 'input[type=submit]' ); 
			
			if( badge_msg.length > 0 )
			{
				if( 'avia_recaptcha_v3' == version )
				{
					badge_msg.detach().insertAfter( submit ).removeClass( 'hidden' );
					self.body.addClass( 'av-google-badge-hide' );
				}
				else
				{
					badge_msg.remove();
				}
			}
			else 
			{
				if( 'avia_recaptcha_v3' == version )
				{
					self.body.addClass( 'av-google-badge-visible' );
				}
			}
			
			var new_submit = submit.clone();
			
			new_submit.data( 'version', version );
			new_submit.data( 'context', context );
			new_submit.data( 'unique_recaptcha_id', id );
			new_submit.addClass( id );
			
			new_submit.attr( 'type', 'button' ).addClass( 'av-recaptcha-submit ' + id);
			if( 'avia_recaptcha_v2' == version )
			{
				new_submit.attr( 'title', this.validate_first );
				new_submit.addClass( 'avia_button_inactive' );
			}
			else
			{
				new_submit.attr( 'title', this.validate_submit );
			}
			
			//	added 4.8.6.4 https://kriesi.at/support/topic/google-analytics-on-click-bug/
			var analytics_ckeck = new_submit.attr( 'onclick' );
			if( 'undefined' != typeof analytics_ckeck )
			{
				analytics_ckeck = analytics_ckeck.toLowerCase();
				if( analytics_ckeck.indexOf( 'gtag(' ) >= 0 )
				{
					new_submit.attr( 'onclick', null );
				}
			}
			
			submit.before( new_submit );
			
			submit.addClass( 'avia_button_inactive av-recaptcha-submit-real' );
			submit.hide();
			
			form.find( '.av-recaptcha-submit' ).on( 'click', $.proxy( self.reCaptchaSubmitButton, self ) );
		},
		
		loadMainAPI: function()
		{
			if( this.recaptcha_areas.length == 0 && this.version != 'avia_recaptcha_v3' )
			{
				return false;
			}
			
			var src = this.api;
			var defer = true;
			var obj = this;
			
			switch( this.version )
			{
				case 'avia_recaptcha_v2':
					src += '?onload=av_recaptcha_main_api_loaded&render=explicit';
					break;
				case 'avia_recaptcha_v3':
					src += '?onload=av_recaptcha_main_api_loaded&render=' + this.site_key3;
					break;
			}
			
						//find a current google recaptcha link and remove it, then append the new one
			$( 'script[src*="recaptcha/api.js"]' ).remove();
			$( '#av-recaptcha-api-script' ).remove();
		
			var	script 		= document.createElement( 'script' );
				script.id	= 'av-recaptcha-api-script';
				script.type = 'text/javascript';	
				script.src 	= src;
				script.onerror = function(){ obj.main_api_loading_error(); };
//				script.onload = function(){ };
				if( defer )
				{
					script.defer = true;
				}

			document.body.appendChild(script);
			
//			console.log( 'loading: ' + this.version );
			return true;
		},
		
		main_api_loading_error: function()
		{
//			console.log( 'main_api_loading_error' );
			
			this.errors.push( this.cannot_use );
			this.error_report.push( this.api_load_error );
			this.showErrors( 'remove_buttons' );
		},
		
		main_api_loaded: function()
		{
			if( 'avia_recaptcha_v3' == this.version )
			{
				var obj = this.self;
				grecaptcha.ready(function() {
//					console.log( 'V3 API page_load' );
								obj.monitor_V3_request = setTimeout( function(){ obj.monitorV3( 'pageload' ); }, 5000 );
								grecaptcha.execute( obj.site_key3, { action: 'contact_forms_pageload' } ).then( function( token )
																{
																	obj.verifyCallback( token, 'avia_recaptcha_v3', 'page_load' );
																} );
						});
			}
			
			this.show_reCaptchas();
			
			return true;
		},
		
		/**
		 * Fix bugs in V3:
		 *		- a wrong sitekey does not return or throw any error - only a message in console
		 *		- loosing internet connection only shows a unhandled exception null
		 */
		monitorV3: function( location )
		{
//			console.log( 'monitorLoadedV3' );
			
			this.monitor_V3_request = false;
			
			this.errors.push( this.cannot_use );
			
			switch( location )
			{
				case 'pageload':
					this.error_report.push( this.v3_timeout_pageload );
					break;
				case 'verify_submit':
					this.error_report.push( this.v3_timeout_verify );
					break;
				default:
					this.error_report.push( 'v3_timeout - unknown location' );
			}
			
			this.showErrors( 'remove_buttons' );
		},
		
		verifyCallback: function( token, version, context )
		{
//			console.log( 'token: ', token, token.length );
			
			//	v3 fix: wrong sitekey interrupts program flow, if it returns sitekey is valid.
			if( 'avia_recaptcha_v3' == version && this.monitor_V3_request !== false )
			{
				clearTimeout( this.monitor_V3_request );
				this.monitor_V3_request = false;
			}
			
			if( 'avia_recaptcha_v3' == version && 'page_load' == context )
			{
				return;
			}
			
			var container = null;
			var score = null;
			var action = null;
			
			if( 'avia_recaptcha_v2' == version )
			{
				//	We do not get any feedback which reCAPTCHA was clicked when multiple on a page
				container = this.locate_clicked_V2_recaptcha( token );
			}
			else if( 'avia_recaptcha_v3' == version )
			{
				var btn = $( 'input.av-recaptcha-verify-v3' );
				if( btn.length > 0 )
				{
					container = btn.first().closest( 'form' ).find( 'div.' + btn.data( 'unique_recaptcha_id' ) ).first();
					if( container.length > 0 )
					{
						score = container.data( 'score' );
						action = context;
					}
				}
				btn.removeClass( 'av-recaptcha-verify-v3' );
			}
			
			if( null == container )
			{
				this.errors.push( this.cannot_use );
				this.error_report.push( 'verifyCallback: No containeer found to clicked submit button' );
				
				this.showErrors( 'remove_buttons' );
				return;
			}
			
			this.serverCallback( token, version, container, score, action );
		},
		
		locate_clicked_V2_recaptcha: function( token )
		{
			var clicked = null;
			this.recaptcha_areas.each( function( index, element ){
							var container = $(element);
							if( 'avia_recaptcha_v2' != container.data( 'version' ) )
							{
								return true;
							}
							
							var result = grecaptcha.getResponse( container.data( 'recaptcha_widet_id' ) );
							if( token == result )
							{
								clicked = container;
								return false;
							}
						});
						
			return clicked;
		},
		
		serverCallback: function( token, version, container, score, action )
		{
			var obj = this.self;
			var id = container.data( 'unique_recaptcha_id' );
			var button = $( 'input.av-recaptcha-submit.' + id );
			var orig_msg = button.val();
			var alert_msg = '';
			var form = button.closest( 'form' );
			var msg_container = form.find( '.av-recaptcha-error' );
			
			$.ajax({
					type: "POST",
					url: avia_framework_globals.ajaxurl,
					dataType: 'json',
					cache: false,
					data: 
					{
						action: 'avia_recaptcha_verify_frontend',
						version: version,
						token: token,
						score: null != score ? score : -1,
						recaptcha_action: null != action ? action : '',
						_wpnonce: this.verify_nonce,
					},
					beforeSend: function()
					{
						button.addClass( 'av-sending-button' );
						button.val( this.verify_msg );
					},
					error: function()
					{
						alert_msg = this.connection_error;
					},
					success: function(response)
					{
						if( response.success !== true )
						{
							alert_msg = response.alert;
							
							if( 'avia_recaptcha_v3' == version && false !== response.score_failed )
							{
								if( true !== response.score_failed )
								{
									alert_msg += '<br />' + response.score_failed;
								}
								
								button.addClass( 'avia_recaptcha_v3_redirected_v2' );
								button.closest('form').removeClass( 'avia_recaptcha_v3' ).addClass( 'avia_recaptcha_v2 avia_recaptcha_v2_forced' );
								obj.show_reCaptchas( 'force_v2' );
							}
							
							return;
						}
						
						if( response.transient != '' )
						{
							$( 'input.av-recaptcha-verify-token.' + id).val(response.transient);
							button.addClass( 'av-recaptcha-is-verified' );
						}
						
						button.removeClass( 'avia_button_inactive' );
					},
					complete: function(response)
					{	
						if( 'avia_recaptcha_v2' == version )
						{
							grecaptcha.reset( container.data( 'recaptcha_widet_id' ));
							if( alert_msg == '' )
							{
								container.hide();
								button.attr( 'title', '' );
							}
							
							if( button.hasClass( 'av-recaptcha-is-verified' ) && button.hasClass( 'avia_recaptcha_v3_redirected_v2' ) )
							{
								button.trigger( 'click' );
							}
						}
						else if( 'avia_recaptcha_v3' == version )
						{
							if( button.hasClass( 'av-recaptcha-is-verified' ) )
							{
								button.trigger( 'click' );
							}
						}
						
						button.removeClass( 'av-sending-button' );
						button.val( orig_msg );

						if( alert_msg != '' )
						{
							msg_container.addClass( 'av-err-content' ).removeClass( 'av-recaptcha-severe-error' );
							msg_container.html( alert_msg );
						}
						else
						{
							msg_container.removeClass( 'av-err-content av-recaptcha-severe-error' );
							msg_container.html( '' );
						}
					}
				});	
		},
		
		errorCallback: function()
		{
//			console.log( 'errorCallback: ' );
			
			this.errors.push( this.v2_timeout_verify );
			this.showErrors();
		},
		
		show_reCaptchas: function( force_recaptcha )
		{
			var obj = this.self;
			
			this.recaptcha_areas.each( function( index, element ) {
										obj.show_reCaptcha( index, element, obj, force_recaptcha );
									});
		},
		
		show_reCaptcha: function( index, element, self, force_recaptcha )
		{
			var container = $( element );
			var form = container.closest( 'form' );
			var version = container.data( 'version' );
			var token_input_version = form.find( '.av-recaptcha-verify-token-version' );
			
			if( force_recaptcha == 'force_v2' )
			{
				version = 'avia_recaptcha_v2';
				container.data( 'version', version );
				var new_submit = form.find( '.av-recaptcha-submit' );
				new_submit.attr( 'title', this.validate_first );
			}
			
			token_input_version.val( version );
			
			if( 'avia_recaptcha_v3' == version )
			{
				return;
			}
			
			var widget_id = container.data( 'recaptcha_widet_id' );
			
			if( 'undefined' != typeof widget_id )
			{
				return;
			}
			
			widget_id = grecaptcha.render( container.get( 0 ), {
										'sitekey': self.site_key2,
										'callback': av_recaptcha_verifyCallback_v2,
										'expired-callback': av_recaptcha_expiredCallback,
										'error-callback': av_recaptcha_errorCallback,
										'theme': container.data( 'theme' ),
										'size':container.data( 'size' )
									});
									
			container.data( 'recaptcha_widet_id', widget_id );
		},
		
		reCaptchaSubmitButton: function( e )
		{
			e.preventDefault();
			
			var obj = this.self;
			var btn = $(e.target);
			var form = btn.closest( 'form' );
			var container = form.find( '.av-recaptcha-area' );
			var real_submit = form.find( '.av-recaptcha-submit-real' );
			var version = container.data( 'version' );
			var token = container.find( '.av-recaptcha-verify-token' );
			
			if( 'avia_recaptcha_v2' == version )
			{
				if( btn.hasClass( 'avia_button_inactive' ) )
				{
					if( '' != btn.attr( 'title' ) )
					{
						alert( btn.attr( 'title' ) );
					}
					return false;
				}
				
				if( '' == token.val() )
				{
					this.errors.push( this.cannot_use );
					this.error_report.push( this.no_token );
				
					this.showErrors( 'remove_buttons' );
					return false;
				}
				
				real_submit.removeClass( 'avia_button_inactive' );
				btn.hide();
				real_submit.show();
				real_submit.trigger( 'click' );
				
				return;
			}
			
			if( 'avia_recaptcha_v3' != version )
			{
				this.error_report.push( this.invalid_version );
				this.showErrors();
				return;
			}
			
			if( btn.hasClass( 'av-recaptcha-is-verified' ) )
			{
				if( '' == token.val() )
				{
					this.errors.push( this.cannot_use );
					this.error_report.push( this.no_token );
				
					this.showErrors( 'remove_buttons' );
					return false;
				}
				
				real_submit.removeClass( 'avia_button_inactive' );
				btn.hide();
				real_submit.show();
				real_submit.trigger( 'click' );
				
				return;
			}
			
			btn.addClass( 'av-recaptcha-verify-v3 avia_button_inactive av-sending-button' );
				
			grecaptcha.ready(function() {
							if( false === obj.monitor_V3_request )
							{
								obj.monitor_V3_request = setTimeout( function(){ obj.monitorV3( 'verify_submit' ); }, 3000 );
							}
							grecaptcha.execute( obj.site_key3, { action: 'verify_submit' } ).then( function( token )
															{
																obj.verifyCallback( token, 'avia_recaptcha_v3', 'verify_submit' );
															} );
						});
		},
		
		showErrors: function( action )
		{
			var obj = this.self;
			
			if( 0 == this.recaptcha_areas.length )
			{
				obj.errors = [];
				obj.error_report = [];
				return;
			}
		
			this.recaptcha_areas.each( function( index, element ) {
								var container = $( element );
								var form = container.closest( 'form' );
								var msg = form.find( '.av-recaptcha-error' );
								msg.addClass( 'av-recaptcha-severe-error' ).removeClass( 'av-err-content' );

								var output1 = obj.errors.join( '<br />' ).trim();
								var output2 = '';

								if( obj.body.hasClass( 'av-recaptcha-extended-errors' ) )
								{
									output2 = obj.error_report.join( '<br />' ).trim();
									if( output2 != '' && output1 != '' )
									{
										output2 = '<br />' + output2;
									}
								}

								msg.html( output1 + output2 );
								
								if( 'remove_buttons' == action )
								{
									form.find( '.av-recaptcha-submit, .av-recaptcha-submit-real' ).remove();
								}
						});
						
			obj.errors = [];
			obj.error_report = [];
		},
		

		unique_id: function( index )
		{
			var body = $( 'body' );
			var id = 'av-verify-recaptcha-';
			var cnt = 0;
			var first = true;
			var unique = id + index;

			do
			{
				if( ! first )
				{
					unique = id + cnt;
				}
				
				if( 0 == body.find( '[data-unique_recaptcha_id="' + unique + '"]' ).length  )
				{
					return unique;
				}
				
				first = false;
				cnt ++;
			}while( true )
		}
	};
	
	av_recaptcha_main_api_loaded = function()
	{
		obj_avia_recaptcha.main_api_loaded();
	};
	
	av_recaptcha_verifyCallback_v2 = function( token )
	{
		//	needed to get access to grecaptcha.getResponse() to check for clicked submit button
		setTimeout( function(){
								obj_avia_recaptcha.verifyCallback( token, 'avia_recaptcha_v2', 'verify_token' );
							}, 50 );
	};
	
	av_recaptcha_errorCallback = function( token )
	{
//		console.log( 'av_recaptcha_errorCallback' );
		obj_avia_recaptcha.errorCallback( token );
	};
	
	av_recaptcha_expiredCallback = function()
	{
//		console.log( 'av_recaptcha_expiredCallback' );
	};

	
	if( null == obj_avia_recaptcha )
	{
		obj_avia_recaptcha = new avia_recaptcha();
	}
	
})(jQuery);	

