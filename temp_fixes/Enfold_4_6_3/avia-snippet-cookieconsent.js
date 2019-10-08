
var avia_cookie_consent_modal_callback = avia_cookie_consent_modal_callback || 
		{
			init: null,
			open: null,
			close: null
		}; 

		
( function($) {

	"use strict";
	
	$(document).ready( function() 
	{
		//	FF throws error when all cookies blocked !!
		var sessionBlocked = false;
		try
		{
			var test = sessionStorage.getItem( 'aviaCookieRefused' );
		}
		catch(e)
		{
			sessionBlocked = true;
		}
		
		var $_html = $('html');
		var aviaCookieConsentBar = $('.avia-cookie-consent');
		var aviaCookieConsentBadge = $('#av-cookie-consent-badge');
		var accept_btn = $('.avia-cookie-consent-button.avia-cookie-close-bar');
		var newCookieContents = aviaCookieConsentBar.attr('data-contents');
		var oldCookieContents = aviaGetCookie( 'aviaCookieConsent' );
		var aviaCookieRefused = ! sessionBlocked ? sessionStorage.getItem( 'aviaCookieRefused' ) : null;
		var forceHideMessageBar = aviaGetCookie('aviaPrivacyRefuseCookiesHideBar');
		var cookie_paths = set_cookie_paths();		// object Cookie name: path  - filled from custom cookies
		var reload_tooltip = $_html.find( 'a.avia-privacy-reload-tooltip-link' ).first();
		
		if( reload_tooltip.length > 0 )
		{
			new $.AviaTooltip({"class": 'avia-privacy-reload-tooltip', data: 'avia-privacy-reload-tooltip', event:'click', position:'top', scope: "body", attach:'element', within_screen: true});
		}
		
		if( 'undefined' != typeof $.avia_utilities.av_popup && 'function' != typeof avia_cookie_consent_modal_callback.init )
		{
			avia_cookie_consent_modal_callback.init = avia_magnificPopup_init;
			avia_cookie_consent_modal_callback.open = null;
			avia_cookie_consent_modal_callback.close = avia_magnificPopup_close;
		}
		
		check_fallback_cookie_setting();
		
		if( $_html.hasClass( 'avia-cookie-check-browser-settings' ) )
		{
			check_doNotTrack();
		}
		
		if( newCookieContents != oldCookieContents )
		{
			oldCookieContents = null;
		}
		
		var msgbar_changed = oldCookieContents == null;		//	message bar content changed - we must show message bar again
		
		if( ! $_html.hasClass( 'av-cookies-consent-message-bar-only' ) )
		{
			if( ! forceHideMessageBar )
			{
				msgbar_changed = true;	//	user disabled hiding message bar and cookies via toggle - we must show it again.
			}

			if( aviaCookieRefused )
			{
				msgbar_changed = false;		//	do not ask again as long as in same session
			}
		
			if( ! ( oldCookieContents || aviaCookieRefused ) || msgbar_changed )
			{
				aviaCookieConsentBar.removeClass('cookiebar-hidden');		
			}
		}
		else if( msgbar_changed )
		{
			aviaCookieConsentBar.removeClass('cookiebar-hidden');
		}
		
		/**
		 * If user refuses cookies and silent accept of cookies is selected we need to remove this if we are in same session
		 */
		if( ! sessionBlocked && sessionStorage.getItem( 'aviaCookieRefused' ) )
		{
			aviaSetCookie( 'aviaCookieSilentConsent', false, -1 );
		}
		
		accept_btn.on( 'click', function(e) {
					e.preventDefault();
					
					var button = $(this);
					
					if( button.hasClass( 'avia-cookie-select-all' ) )
					{
						aviaSetCookieToggles( 'select_all' );
					}
					
					aviaSetCookie( 'aviaCookieConsent', newCookieContents, 365 );
					aviaSetCookie( 'aviaCookieSilentConsent', false, -1 );
					aviaCookieConsentBar.addClass('cookiebar-hidden');
					aviaCookieConsentBadge.addClass('avia_pop_class');
					if( button.hasClass('avia-cookie-consent-modal-button') )
					{
						if( 'function' == typeof avia_cookie_consent_modal_callback.close )
						{
							avia_cookie_consent_modal_callback.close( this );
						}
					}
					
					if( $_html.hasClass( 'av-cookies-consent-message-bar-only' ) )
					{
						return;
					}
					
					if( $_html.hasClass( 'av-cookies-needs-opt-in' ) )
					{
						aviaSetCookie( 'aviaPrivacyMustOptInSetting', true, 365 );
					}
					else
					{
						aviaSetCookie( 'aviaPrivacyMustOptInSetting', false, -60 );
					}
					
					aviaSetCookieToggles( 'set' );
					
					if( $_html.hasClass( 'avia-cookie-reload-accept' ) )
					{
						if( reload_tooltip.length > 0 )
						{
							reload_tooltip.closest( '.avia-privacy-reload-tooltip-link-container' ).addClass( 'av-display-tooltip' );
							reload_tooltip.trigger( 'click' );
						}
						location.reload( true );
					}
			});
			
		//	hide and dismiss button
		$('.avia-cookie-consent-button.avia-cookie-hide-notification').on( 'click', function(e) {
					e.preventDefault();
					
					var button = $(this);
					
					if( 'undefined' != typeof AviaPrivacyCookieAdditionalData.cookie_refuse_button_alert && '' != AviaPrivacyCookieAdditionalData.cookie_refuse_button_alert.trim() )
					{
						if( ! window.confirm( AviaPrivacyCookieAdditionalData.cookie_refuse_button_alert ) )
						{
							return;
						}
					}
					
					if( button.hasClass( 'avia-cookie-consent-modal-button' ) )
					{
						if( 'function' == typeof avia_cookie_consent_modal_callback.close )
						{
							avia_cookie_consent_modal_callback.close( this );
						}
					}
					
					aviaSetCookieToggles( 'reset' );
					if( ! sessionBlocked )
					{
						sessionStorage.setItem( 'aviaCookieRefused', newCookieContents );
					}
					aviaCookieConsentBar.addClass( 'cookiebar-hidden' );
					aviaCookieConsentBadge.addClass( 'avia_pop_class' );
					
					if( $_html.hasClass( 'avia-cookie-reload-no-accept' ) )
					{
						if( reload_tooltip.length > 0 )
						{
							reload_tooltip.closest( '.avia-privacy-reload-tooltip-link-container' ).addClass( 'av-display-tooltip' );
							reload_tooltip.trigger( 'click' );
						}
						location.reload( true );
					}
			});
        
		//	info button
		if( 'function' == typeof avia_cookie_consent_modal_callback.init )
		{
			var options = {
					activate: '.avia-cookie-consent-button.avia-cookie-info-btn',
					source:   '#av-consent-extra-info'
				};
				
			avia_cookie_consent_modal_callback.init( options );
			
			$( '.avia-cookie-consent-button.avia-cookie-info-btn' ).on( 'click', function(e) {
						if( 'function' == typeof avia_cookie_consent_modal_callback.open )
						{
							avia_cookie_consent_modal_callback.open( this );
						}
				});
		}
		else
		{
			$('.avia-cookie-consent-button.avia-cookie-info-btn').on( 'click', function(e) {
						e.preventDefault();
						
						var def_msg = "We need a lightbox to show the modal popup. Please enable the built in lightbox in Theme Options Tab or include your own modal window plugin.\n\nYou need to connect this plugin in JavaScript with callback wrapper functions - see avia_cookie_consent_modal_callback in file enfold\js\avia-snippet-cookieconsent.js";
						var msg = 'string' == typeof AviaPrivacyCookieAdditionalData.no_lightbox ? AviaPrivacyCookieAdditionalData.no_lightbox : def_msg;
						
						alert( msg );
				});
		}
		
		//	Badge 
		aviaCookieConsentBadge.on( 'click', function(e) {
					e.preventDefault();
					
					aviaCookieConsentBar.removeClass('cookiebar-hidden');
					aviaCookieConsentBadge.removeClass('avia_pop_class');
			});
			
		function avia_magnificPopup_init( options )
		{
			var new_options = {
				type:'inline',
				midClick: true, // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href.
				closeOnBgClick: false,
				enableEscapeKey: false,
				items:{
					src: '#av-consent-extra-info',
					type:'inline'
				}
			};
			
			new_options = $.extend( {}, $.avia_utilities.av_popup, new_options );
			$( '.avia-cookie-info-btn' ).magnificPopup( new_options );
		}
		
		function avia_magnificPopup_close()
		{
			$.magnificPopup.close();
		}
		
		function set_cookie_paths()
		{
			var paths = {};
			if( 'undefined' != typeof AviaPrivacyCookieAdditionalData.remove_custom_cookies )
			{
				$.each( AviaPrivacyCookieAdditionalData.remove_custom_cookies, function( i, custom_cookie ) {
								var path = custom_cookie.cookie_path.trim();
								paths[ custom_cookie.cookie_name ] = ( '' != path ) ? path : '/';
						});
			}
			
			return paths;
		}

		function aviaSetCookie( CookieName, CookieValue, CookieDays ) 
		{
			var path = '/';
			if( 'string' == typeof cookie_paths[ CookieName ] )
			{
				path = cookie_paths[ CookieName ];
			}
					
			if( CookieDays ) 
			{
				var date = new Date();
				date.setTime( date.getTime() + ( CookieDays * 24 * 60 * 60 * 1000 ) );
				var expires = "; expires=" + date.toGMTString();
			}
			else 
			{
				var expires = "";
			}
			
			document.cookie = CookieName + "=" + CookieValue + expires + "; path=" + path;
		}
		
		function aviaGetCookieNames( CookieName, check )
		{
			check = ( 'undefined' == typeof check ) ? 'equals' : check.trim();
			
			var cookiesFound = [];
			var docCookiesArr = document.cookie.split( ';' );
			
			for( var i = 0; i < docCookiesArr.length; i++ ) 
			{
				var thisCookie = docCookiesArr[i];
				var result = thisCookie.split( '=' );
				var cookie_name = result[0].trim();
				var found = false;
				
				switch (check)
				{
					case 'starts_with':
						found = cookie_name.startsWith( CookieName );
						break;
					case 'contains':
						if( ! cookie_name.startsWith( 'aviaPrivacyCustomCookie' ) )
						{
							found = cookie_name.indexOf( CookieName ) != -1;
						}
						break;
					case 'equals':
					case '':
					default:
						found = cookie_name == CookieName;
						break;
				}
				
				if ( found ) 
				{
					cookiesFound.push( cookie_name );
				}
			}
				
			return cookiesFound;
		}

		function aviaGetCookie( CookieName )
		{
			var docCookiesArr = document.cookie.split( ';' );
			
			for( var i = 0; i < docCookiesArr.length; i++ ) 
			{
				var thisCookie = docCookiesArr[i];
				var result = thisCookie.split( '=' );
				var cookie_name = result[0].trim();
				
				if ( cookie_name == CookieName ) 
				{
					cookie_name += '=';
					var savedContents = thisCookie.replace( cookie_name, '' );
					savedContents = savedContents.trim();
					return savedContents;
                }
            }

			return null;
		}
		
		function aviaSetCookieToggles( action )
		{
			var toggles = $( '.av-toggle-switch.av-cookie-disable-external-toggle' );
					
			toggles.each( function() {
					var toggle = $(this);
					var input = toggle.find( 'input' );
					var cookie = input.attr( 'name' );
					
					var value = true;
					var days = 360;
					
					if( action == 'select_all' )
					{
						if( ! input.prop( 'checked' ) )
						{
							input.trigger( 'click' );
						}
						
						return;
					}
					else if( action == 'set' )
					{
						if( input.prop( 'checked' ) && toggle.hasClass( 'av-cookie-save-unchecked' ) || false == input.prop( 'checked' ) && toggle.hasClass( 'av-cookie-save-checked' ) )
						{
							value = false;
							days = -60;
						}
					}
					else	//	reset
					{
						var hidden = input.closest( '.av-hidden-escential-sc' );
						if( 0 == hidden.length )
						{
							input.prop( 'checked', false );
						}
						
						value = false;
						days = -60;
					}
					
					aviaSetCookie( cookie, value, days );
				});

			if( action == 'reset' )
			{
				aviaSetCookie( 'aviaCookieConsent', false, -60 );
				aviaSetCookie( 'aviaPrivacyMustOptInSetting', false, -60 );
			}
			else if( action == 'set' )
			{
				if( ! sessionBlocked )
				{
					sessionStorage.removeItem( 'aviaCookieRefused' );
				}
			}
		}
		
		function monitor_cookies()
		{
			if( $_html.hasClass( 'av-cookies-consent-message-bar-only' ) )
			{
				return;
			}
			
			var accepted = document.cookie.match(/aviaCookieConsent/) != null;
			var allow_hide_bar = document.cookie.match(/aviaPrivacyRefuseCookiesHideBar/) != null;
			var allow_cookies = document.cookie.match(/aviaPrivacyEssentialCookiesEnabled/) != null;
			var keep_cookies = [];
			
			if( ! ( accepted && allow_hide_bar && allow_cookies ) )
			{
				if( accepted && allow_hide_bar )
				{
					keep_cookies.push( 'aviaCookieConsent', 'aviaPrivacyRefuseCookiesHideBar', 'aviaPrivacyMustOptInSetting' );
				}
				else if( accepted )
				{
					keep_cookies.push( 'aviaCookieConsent', 'aviaPrivacyMustOptInSetting' );
				}

				remove_all_cookies( keep_cookies );
			}
			else
			{
				remove_custom_cookies();
			}
			
			update_cookie_info_box();
			
			window.setTimeout( monitor_cookies, 300 );
		}
		
		function remove_custom_cookies()
		{
			if( 'undefined' == typeof AviaPrivacyCookieAdditionalData.remove_custom_cookies )
			{
				return;
			}
			
			$.each( AviaPrivacyCookieAdditionalData.remove_custom_cookies, function( i, custom_cookie ) {
								var disable = aviaGetCookie( custom_cookie.avia_cookie_name );
								if( disable != null )
								{
									var remove_cookies = aviaGetCookieNames( custom_cookie.cookie_name, custom_cookie.cookie_compare_action );
									for( var i = 0; i < remove_cookies.length; i++ ) 
									{
										var cookie_name = remove_cookies[i];
										aviaSetCookie( cookie_name, false, -60 );
									}
								}
						});
		}
		
		function remove_all_cookies( keep_cookies )
		{
			if( null != aviaGetCookie( 'aviaCookieSilentConsent' ) )
			{
				return;
			}
			
			if( ! $.isArray( keep_cookies ) )
			{
				keep_cookies = [];
			}
			
			keep_cookies.push('aviaCookieSilentConsent');
			
			if( $('body').hasClass( 'logged-in' ) && 'undefined' != typeof AviaPrivacyCookieAdditionalData.admin_keep_cookies )
			{
				$.merge( keep_cookies, AviaPrivacyCookieAdditionalData.admin_keep_cookies );
			}
			
			keep_cookies = keep_cookies.map( function( item ) { return item.trim().toLowerCase(); } );
			var cookie_array = document.cookie.split(';').map( function( item ) { return item.trim(); } );
			
			$.each( cookie_array, function( i, cookie ){
					
							if( '' == cookie )
							{
								return;
							}
							
							var values = cookie.split( '=' );
							var name = values[0].trim();
							var test_name = name.toLowerCase();
							
							if( $.inArray( test_name, keep_cookies ) >= 0 )
							{
								return;
							}
							
							var remove = true;
							$.each( keep_cookies, function( i, keep_cookie ){
									if( keep_cookie.indexOf( '*' ) >= 0 )
									{
										var new_val = keep_cookie.replace( '*', '' );
										if( test_name.startsWith( new_val ) )
										{
											remove = false;
											return false;
										}
									}
								});
								
							if( remove )
							{
								aviaSetCookie( name, false, -60 );
								return;
							}
						});
		}
		
		function update_cookie_info_box()
		{
			if( 'undefined' == typeof AviaPrivacyCookieConsent )
			{
				return;
			}
			
			var infobox = $( '.avia-cookie-privacy-cookie-info' );
			if( 0 == infobox.length )
			{
				return;
			}
			
			var info_array = [];
			var html = '';
			var cookies = document.cookie.split(';');
			cookies.sort( function( a, b ){
							var a = a.split( '=' );
							var b = b.split( '=' );
							
							if( a[0] < b[0] )
							{
								return -1;
							}
							else if( a[0] > b[0] )
							{
								return 1;
							}
							
							return 0;
					});
			
			$.each( cookies, function( i, cookie ){
						if( '' == cookie.trim() )
						{
							return;
						}
						
						var values = cookie.split( '=' );
						var name = values[0].trim();
						var value = 'undefined' != typeof values[1] ? values[1].trim() : '';
						var info = '';
						
						if( name in AviaPrivacyCookieConsent )
						{
							info += AviaPrivacyCookieConsent[name];
						}
						else if( '?' in AviaPrivacyCookieConsent )
						{
							info += AviaPrivacyCookieConsent['?'];
						}
						else
						{
							info += 'Usage unknown';
						}
						
						var out = '<strong>' + name + '</strong> ( ' + value + ' ) - ' + info;
						info_array.push( out );
					});
					
			if( info_array.length > 0 )
			{
				html += '<ul>';

				$.each( info_array, function( i, value ){
								html += '<li>' + value + '</li>';
							});

				html += '</ul>';
			}
			else
			{
				var msg = 'string' == typeof AviaPrivacyCookieAdditionalData.no_cookies_found ? AviaPrivacyCookieAdditionalData.no_cookies_found : 'No accessable cookies found in domain';
				html += '<p><strong>' + msg + '</strong></p>';
			}
			
			infobox.html( html );
		}
		
		function check_fallback_cookie_setting()
		{
			var hidden = $('#av-consent-extra-info').find('.av-hidden-escential-sc');
			if( hidden.length == 0 )
			{
				return;
			}
			
			if( oldCookieContents == null )
			{
				return;
			}
			
			var data = hidden.data('hidden_cookies');
			if( 'undefined' == typeof data )
			{
				return;
			}
			
			/**
			 * If we have hidden toggles and user accepted cookies already we add the hidden cookies
			 */
			var hidden_cookies = data.split( ',' );
			
			$.each( hidden_cookies, function( i, value ){
						if( null == aviaGetCookie( value ) )
						{
							hidden.find('input.' + value ).trigger( 'click' );
						}
					});
			
		}
		
		function check_doNotTrack()
		{
			if( window.doNotTrack || navigator.doNotTrack || navigator.msDoNotTrack ) 
			{
				// The browser supports Do Not Track!
				if (window.doNotTrack == "1" || navigator.doNotTrack == "yes" || navigator.doNotTrack == "1" || navigator.msDoNotTrack == "1" || ( 'function' == typeof window.external.msTrackingProtectionEnabled && window.external.msTrackingProtectionEnabled() ) )
				{
					var input = $( 'input.aviaPrivacyGoogleTrackingDisabled' );
					if( input.length > 0 )
					{
						if( null == aviaGetCookie( 'aviaPrivacyGoogleTrackingDisabled' ) )
						{
							input.trigger( 'click' );
						}
						
						var container = input.closest( '.av-toggle-switch' );
						var message = container.data( 'disabled_by_browser' ).trim();
						container.addClass('av-cookie-sc-disabled');
						container.append( '<p><strong>' + message + '</strong></p>');
						input.attr( 'disabled', 'disabled' );
					}
				}
			}
		}
		
		update_cookie_info_box();
		monitor_cookies();
		
		if( $_html.hasClass( 'avia-cookie-consent-modal-show-immediately' ) && ! $_html.hasClass( 'av-cookies-consent-message-bar-only' ) )
		{
			if( ! aviaCookieConsentBar.hasClass( 'cookiebar-hidden' ) )
			{
				$('.avia-cookie-info-btn').trigger('click');
			}
		}
		
		if( aviaCookieConsentBadge.length > 0 )
		{
			if( aviaCookieConsentBar.hasClass( 'cookiebar-hidden' ) )
			{
				aviaCookieConsentBadge.addClass( 'avia_pop_class' );
			}
			else
			{
				aviaCookieConsentBadge.removeClass( 'avia_pop_class' );
			}
		}
	});

})( jQuery );
