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
				this.wpHooks();
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

			wpHooks()
			{
				//	to avoid checking for wp.hooks calling filters or actions we create dummy functions here
				if( window['wp'] && wp.hooks )
				{
					return;
				}

				if( ! window['wp'] )
				{
					window['wp'] = { hooks: {} };
				}
				else
				{
					window['wp'].hooks = {};
				}

				let obj = window['wp'].hooks;

				obj.applyFilters = this.wpHooks_applyFilters;
				obj.doAction = this.wpHooks_applyFilters;
				obj.hasFilter = this.wpHooks_hasFilters;
				obj.hasAction = this.wpHooks_hasFilters;
			}

			wpHooks_applyFilters( handle, value )
			{
				return value;
			}

			wpHooks_hasFilters( handle, namespace )
			{
				return false;
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

