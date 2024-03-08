/**
 * Contains plain js basic and helpers classes
 *
 * @since 5.6
 */

/**
 * Global namespace
 *
 * @since 5.6
 */
var aviaJS = aviaJS || {};

(function()
{
	"use strict";

	if( ! aviaJS.aviaJSHelpers )
	{
		class aviaJSHelpers
		{
			constructor()
			{
				if( window['wp'] && wp.hooks && window['Cookiebot'] )
				{
					wp.hooks.addFilter( 'aviaCookieConsent_allow_continue', 'avia-cookiebot', this.cookiebot );
				}
			}

			//	based on _.js debounce()
			debounce( callback, wait, immediate )
			{
				var timeout;

				return function()
				{
					var context = this,
						args = arguments;

					var later = function()
					{
						timeout = null;
						if( ! immediate )
						{
							callback.apply(context, args);
						}
					};

					var callNow = immediate && ! timeout;

					clearTimeout( timeout );
					timeout = setTimeout( later, wait );
					if( callNow )
					{
						callback.apply( context, args );
					}
				};
			}

			cookiebot( allow_continue )
			{
				/**
				 * Solution provided by Jan Thiel
				 * see https://kriesi.at/support/topic/cookiebot-support-feature-request-with-patch/
				 * see https://github.com/KriesiMedia/Enfold-Feature-Requests/issues/91
				 *
				 * Check if user has accepted marketing cookies in Cookiebot
				 */
				var cookiebot_consent = Cookiebot.consent.marketing;
				if( cookiebot_consent !== true )
				{
					allow_continue = false;

					// Reload page if user accepts marketing cookies to allow script to load
					window.addEventListener('CookiebotOnAccept', function (e)
					{
						if( Cookiebot.consent.marketing )
						{
							location.reload();
						}
					}, false );
				}

				return allow_continue;
			}

		}

		aviaJS.aviaJSHelpers = new aviaJSHelpers();
	}

	if( ! aviaJS.aviaPlugins )
	{
		class aviaPlugins
		{
			plugins = [];
			defaultPlugin = {
				classFactory:	null,
				selector:		''
			};

			constructor()
			{
				this.plugins = [];
			}

			register( classFactory, selector )
			{
				if( 'function' != typeof classFactory )
				{
					return false;
				}

				let newPlugin = Object.assign( {}, this.defaultPlugin );

				if( 'string' != typeof selector )
				{
					selector = 'body';
				}

				newPlugin.classFactory = classFactory;
				newPlugin.selector = selector;

				this.plugins.push( newPlugin );

				this.check_bind();
			}

			check_bind()
			{
				if( document.readyState === 'complete' )
				{
					// The page is already fully loaded
					this.bind_plugins();
				}
				else
				{
					document.addEventListener( 'readystatechange', this.bind_plugins.bind( this ) );
				}
			}

			bind_plugins( e )
			{
				if( document.readyState !== 'complete' )
				{
					return;
				}

				let plugins = this.plugins;
				this.plugins = [];

				for( let plugin of plugins )
				{
					let elements = document.querySelectorAll( plugin.selector );

					for( let element of elements )
					{
						plugin.classFactory( element );
					}
				}
			}
		}

		aviaJS.aviaPlugins = new aviaPlugins();
	}

})();

