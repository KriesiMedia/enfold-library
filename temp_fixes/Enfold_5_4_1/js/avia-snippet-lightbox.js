(function($)
{
    "use strict";

	// -------------------------------------------------------------------------------------------
	// Ligthbox activation
	// -------------------------------------------------------------------------------------------

	$.avia_utilities = $.avia_utilities || {};

	$.avia_utilities.av_popup = {
			type: 				'image',
			mainClass: 			'avia-popup mfp-zoom-in',
			tLoading: 			'',
			tClose: 			'',
			removalDelay: 		300, //delay removal by X to allow out-animation
			closeBtnInside: 	true,
			closeOnContentClick:false,
			midClick: 			true,
			autoFocusLast: 		false, // false, prevents issues with accordion slider
			fixedContentPos: 	$('html').hasClass('av-default-lightbox-no-scroll'), // allows scrolling when lightbox is open but also removes any jumping because of scrollbar removal
			iframe: {
			    patterns: {
			        youtube: {
			            index: 'youtube.com/watch',
			            id: function(url) {

				            //fetch the id
			                var m = url.match(/[\\?\\&]v=([^\\?\\&]+)/),
								id,
								params;

			                if( !m || !m[1] )
							{
								return null;
							}

							id = m[1];

			                //fetch params
			                params = url.split('/watch');
			                params = params[1];

			                return id + params;
			            },
			            src: '//www.youtube.com/embed/%id%'
			        },
					vimeo: {
						index: 'vimeo.com/',
						id: function(url) {

							//fetch the id
							var m = url.match(/(https?:\/\/)?(www.)?(player.)?vimeo.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/), id, params;
							if( !m || !m[5] )
							{
								return null;
							}

							id = m[5];

							//fetch params
							params = url.split('?');
							params = params[1];

							return id + '?' + params;
						},
						src: '//player.vimeo.com/video/%id%'
					}
			    }
			},
			image: {
			    titleSrc: function( item )
						{
							var title = item.el.attr('title');
							if( ! title )
							{
								title = item.el.find('img').attr('title');
							}
							if( ! title )
							{
								title = item.el.parent().next( '.wp-caption-text' ).html();
							}
							if( typeof title != "undefined" )
							{
								return title;
							}

							if( ! $( 'body' ).hasClass( 'avia-mfp-show-alt-text' ) )
							{
								return '';
							}

							//	@since 4.7.6.2 check for alt attribute
							var alt = item.el.attr('alt');
							if( typeof alt != "undefined" )
							{
								return alt;
							}

							alt = item.el.find('img').attr('alt');
							if( typeof alt != "undefined" )
							{
								return alt;
							}

							return '';
						}
			},

			gallery: {
				// delegate: 	options.autolinkElements,
				tPrev:		'',
				tNext:		'',
				tCounter:	'%curr% / %total%',
				enabled:	true,
				preload:	[1,1] // Will preload 1 - before current, and 1 after the current image
			},

			callbacks:
			{
				beforeOpen: function()
				{
					//add custom css class for different styling
					if( this.st.el && this.st.el.data('fixed-content') )
					{
						this.fixedContentPos = true;
					}
				},

				open: function()
				{
					//overwrite default prev + next function. Add timeout for  crossfade animation
					$.magnificPopup.instance.next = function()
					{
						var self = this;
						self.wrap.removeClass('mfp-image-loaded');
						setTimeout(function() { $.magnificPopup.proto.next.call(self); }, 120);
					};

					$.magnificPopup.instance.prev = function()
					{
						var self = this;
						self.wrap.removeClass('mfp-image-loaded');
						setTimeout(function() { $.magnificPopup.proto.prev.call(self); }, 120);
					};

					//add custom css class for different styling
					if( this.st.el && this.st.el.data('av-extra-class') )
					{
						this.wrap.addClass( this.currItem.el.data('av-extra-class') );
					}

					this.wrap.avia_swipe_trigger( {prev:'.mfp-arrow-left', next:'.mfp-arrow-right'} );
				},

				markupParse: function( template, values, item )
				{
					if( typeof values.img_replaceWith == 'undefined' || typeof values.img_replaceWith.length == 'undefined' || values.img_replaceWith.length == 0 )
					{
						return;
					}

					var img = $( values.img_replaceWith[0] );

					if( typeof img.attr( 'alt' ) != 'undefined' )
					{
						return;
					}

					var alt = item.el.attr( 'alt' );
					if( typeof alt == "undefined" )
					{
						alt = item.el.find('img').attr('alt');
					}

					if( typeof alt != "undefined" )
					{
						img.attr( 'alt', alt );
					}

					return;
				},

				imageLoadComplete: function()
				{
					var self = this;
					setTimeout(function() { self.wrap.addClass('mfp-image-loaded'); }, 16);
				},
				change: function()
				{
				    if( this.currItem.el )
				    {
					    var current = this.currItem.el;

					    this.content.find( '.av-extra-modal-content, .av-extra-modal-markup' ).remove();

					    if( current.data('av-extra-content') )
					    {
						    var extra = current.data('av-extra-content');
						    this.content.append( "<div class='av-extra-modal-content'>" + extra + "</div>" );
					    }

					    if( current.data('av-extra-markup') )
					    {
						    var markup = current.data('av-extra-markup');
						    this.wrap.append( "<div class='av-extra-modal-markup'>" + markup + "</div>"  );
					    }
				    }
				}
			}
		};

	$.fn.avia_activate_lightbox = function(variables)
	{

		var defaults = {
			groups			:	['.avia-slideshow', '.avia-gallery', '.av-horizontal-gallery', '.av-instagram-pics', '.portfolio-preview-image', '.portfolio-preview-content', '.isotope', '.post-entry', '.sidebar', '#main', '.main_menu', '.woocommerce-product-gallery'],
			autolinkElements:   'a.lightbox, a[rel^="prettyPhoto"], a[rel^="lightbox"], a[href$=jpg], a[href$=webp], a[href$=png], a[href$=gif], a[href$=jpeg], a[href*=".jpg?"], a[href*=".png?"], a[href*=".gif?"], a[href*=".jpeg?"], a[href$=".mov"] , a[href$=".swf"] , a:regex(href, .vimeo\.com/[0-9]) , a[href*="youtube.com/watch"] , a[href*="screenr.com"], a[href*="iframe=true"]',
			videoElements	: 	'a[href$=".mov"] , a[href$=".swf"] , a:regex(href, .vimeo\.com/[0-9]) , a[href*="youtube.com/watch"] , a[href*="screenr.com"], a[href*="iframe=true"]',
			exclude			:	'.noLightbox, .noLightbox a, .fakeLightbox, .lightbox-added, a[href*="dropbox.com"]'
		},

		options = $.extend({}, defaults, variables),

		active = ! $('html').is('.av-custom-lightbox');

		if( ! active)
		{
			return this;
		}

		return this.each(function()
		{
			var container	= $(this),
				videos		= $(options.videoElements, this).not(options.exclude).addClass('mfp-iframe'), /*necessary class for the correct lightbox markup*/
				ajaxed		= ! container.is('body') && ! container.is('.ajax_slide');
				for( var i = 0; i < options.groups.length; i++ )
				{
					container.find(options.groups[i]).each(function()
					{
						var links = $(options.autolinkElements, this);

						if( ajaxed )
						{
							links.removeClass('lightbox-added');
						}

						links.not(options.exclude).addClass('lightbox-added').magnificPopup($.avia_utilities.av_popup);
					});
				}

		});
	};
})(jQuery);
