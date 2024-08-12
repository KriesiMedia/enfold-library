// -------------------------------------------------------------------------------------------
// Toggle shortcode javascript
// -------------------------------------------------------------------------------------------
(function($)
{
	"use strict";

	$.fn.avia_sc_toggle = function(options)
	{
		var defaults =
		{
			single: '.single_toggle',
			heading: '.toggler',
			content: '.toggle_wrap',
			sortContainer:'.taglist'
		};

		var win = $(window),
			options = $.extend(defaults, options);

		return this.each(function()
		{
			var container = $(this).addClass('enable_toggles'),
				toggles = $(options.single, container),
				heading = $(options.heading, container),
				allContent = $(options.content, container),
				sortLinks = $(options.sortContainer + " a", container),
				preview = $( '#av-admin-preview' );



			//	needed to avoid scroll to top when opening and closing toggles - can be removed when other solution found (readded @since 4.8.4.1)
			var activeStyle = '',
				headingStyle = '';

			heading.each(function(i)
			{
				var thisheading = $(this),
					content = thisheading.hasClass( 'av-title-below' ) ? thisheading.prev( options.content, container ) : thisheading.next( options.content, container ),
					slideSpeed = parseInt( thisheading.data( 'slide-speed' ) );

				slideSpeed = isNaN( slideSpeed ) ? 200 : slideSpeed;

				function scroll_to_viewport()
				{
				    //check if toggle title is in viewport. if not scroll up
				    var el_offset = content.offset().top,
				        scoll_target = el_offset - 50 - parseInt($('html').css('margin-top'),10);

				    if(win.scrollTop() > el_offset)
				    {
				        $('html:not(:animated),body:not(:animated)').animate( {scrollTop: scoll_target}, slideSpeed );
				    }
				}

				if( content.css('visibility') != "hidden" )
				{
					thisheading.addClass('activeTitle').attr('style',activeStyle);
				}

				thisheading.on('keydown', function(objEvent)
				{
					// user presses 'enter' or space https://www.w3.org/WAI/ARIA/apg/patterns/accordion/#keyboardinteraction
					if( objEvent.keyCode === 13 || objEvent.keyCode === 32 )  // if user presses 'enter' or space
					{
						thisheading.trigger('click');
					}
				});

				thisheading.on( 'click', function()
				{
					if( content.css('visibility') != "hidden" )
					{
						content.slideUp( slideSpeed, function()
						{
							content.removeClass('active_tc').attr({style:''});
							win.trigger('av-height-change');
							win.trigger('av-content-el-height-changed', this );

							if( preview.length == 0 )
							{
								location.replace( thisheading.data('fake-id') + "-closed" );
							}
						});

						thisheading.removeClass('activeTitle').attr('style', headingStyle);
					}
					else
					{
						if( container.is('.toggle_close_all') )
						{
							allContent.not(content).slideUp( slideSpeed, function()
							{
								$(this).removeClass('active_tc').attr({style:''});
								scroll_to_viewport();
							});

							heading.removeClass('activeTitle').attr('style',headingStyle);
						}

						content.addClass('active_tc');

						setTimeout(function()
						{
							content.slideDown( slideSpeed, function()
							{
								if( ! container.is('.toggle_close_all') )
								{
									scroll_to_viewport();
								}

								win.trigger( 'av-height-change' );
								win.trigger( 'av-content-el-height-changed', this );
							});

						}, 1);

						thisheading.addClass('activeTitle').attr( 'style', activeStyle );

						if( preview.length == 0 )
						{
							location.replace(thisheading.data('fake-id'));
						}
					}

				});
			});

			sortLinks.on( 'click', function(e)
			{
				e.preventDefault();

				var show = toggles.filter('[data-tags~="' + $(this).data('tag') + '"]'),
					hide = toggles.not('[data-tags~="' + $(this).data('tag') + '"]');

				sortLinks.removeClass('activeFilter');
				$(this).addClass('activeFilter');
				heading.filter('.activeTitle').trigger('click');
				show.slideDown();
				hide.slideUp();
			});

			function changeTitleAria()
			{
				heading.each( function( i )
				{
					let $heading = $(this),
						title_open = $heading.attr( 'data-title-open' ),
						title = $heading.attr( 'data-title' ),
						aria_collapsed = $heading.attr( 'data-aria_collapsed' ),
						aria_expanded = $heading.attr( 'data-aria_expanded' ),
						content = $heading.hasClass( 'av-title-below' ) ? $heading.prev( options.content, container ) : $heading.next( options.content, container ),
						titleHasHtml = false,
						currentTitle = $heading.contents()[0].data;

					if( ! title )
					{
						title = '***';
					}

					if( ! aria_collapsed )
					{
						aria_collapsed = 'Expand Toggle';
					}

					if( ! aria_expanded )
					{
						aria_expanded = 'Collapse Toggle';
					}

					//	limitation - html markup breaks exchange logic because contents()[0] ends with first markup
					titleHasHtml = title.indexOf( '<' ) >= 0;

					if( content.css('visibility') != "hidden" )
					{
						if( title_open && ! titleHasHtml && currentTitle != title_open )
						{
							$heading.contents()[0].data = title_open;
						}
						$heading.attr( 'aria-expanded', 'true' );
						$heading.attr( 'aria-label', aria_expanded );
						content.attr( { 'aria-hidden': 'false', tabindex: 0 } );
					}
					else
					{
						if( title_open && ! titleHasHtml && currentTitle != title )
						{
							$heading.contents()[0].data = title;
						}

						$heading.attr( 'aria-expanded', 'false' );
						$heading.attr( 'aria-label', aria_collapsed );
						content.attr( { 'aria-hidden': 'true', tabindex: -1 } );
					}
				});

				setTimeout( function(){ changeTitleAria(); }, 150 );
			}

			function trigger_default_open(hash)
			{
				if( ! hash && window.location.hash )
				{
					hash = window.location.hash;
				}

				if( ! hash )
				{
					return;
				}

				var open = heading.filter('[data-fake-id="' + hash + '"]');

				if( open.length )
				{
					if( ! open.is('.activeTitle') )
					{
						open.trigger('click');
					}

					window.scrollTo(0, container.offset().top - 70);
				}
			}

			changeTitleAria();
			trigger_default_open(false);

			$('a').on( 'click', function()
			{
				var hash = $(this).attr('href');
				if(typeof hash != "undefined" && hash)
				{
					hash = hash.replace( /^.*?#/,'' );
					trigger_default_open( '#' + hash );
				}
			});

		});
	};

}(jQuery));

