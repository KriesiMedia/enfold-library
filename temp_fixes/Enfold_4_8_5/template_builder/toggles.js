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
				sortLinks = $(options.sortContainer + " a", container);

			//	needed to avoid scroll to top when opening and closing toggles - can be removed when other solution found (readded @since 4.8.4.1)
			var activeStyle = '',
				headingStyle = '';

			heading.each(function(i)
			{
				var thisheading = $(this),
					content = thisheading.next(options.content, container);

				function scroll_to_viewport()
				{
				    //check if toggle title is in viewport. if not scroll up
				    var el_offset = content.offset().top,
				        scoll_target = el_offset - 50 - parseInt($('html').css('margin-top'),10);

				    if(win.scrollTop() > el_offset)
				    {
				        $('html:not(:animated),body:not(:animated)').animate({scrollTop: scoll_target},200);
				    }
				}

				if(content.css('visibility') != "hidden")
				{
					thisheading.addClass('activeTitle').attr('style',activeStyle);
				}

				thisheading.on('keydown', function(objEvent)
				{
					if(objEvent.keyCode === 13)  // if user presses 'enter'
					{
						thisheading.trigger('click');
					}
				});

				thisheading.on( 'click', function()
				{
					if(content.css('visibility') != "hidden")
					{
						content.slideUp( 200, function()
						{
							content.removeClass('active_tc').attr({style:''});
							win.trigger('av-height-change');
							win.trigger('av-content-el-height-changed', this );

							location.replace(thisheading.data('fake-id') + "-closed");
						});

						thisheading.removeClass('activeTitle').attr('style',headingStyle);

					}
					else
					{
						if(container.is('.toggle_close_all'))
						{
							allContent.not(content).slideUp(200, function()
							{
								$(this).removeClass('active_tc').attr({style:''});
								scroll_to_viewport();
							});
							
							heading.removeClass('activeTitle').attr('style',headingStyle);
						}

						content.addClass('active_tc');

						setTimeout(function()
						{
							content.slideDown(200, function()
							{
								if(!container.is('.toggle_close_all'))
								{
									scroll_to_viewport();
								}

								win.trigger('av-height-change');
								win.trigger('av-content-el-height-changed', this );
							});

						}, 1);

						thisheading.addClass('activeTitle').attr('style',activeStyle);
						location.replace(thisheading.data('fake-id'));
					}
				});
			});


			sortLinks.on( 'click', function(e)
			{
				e.preventDefault();

				var show = toggles.filter('[data-tags~="'+$(this).data('tag')+'"]'),
					hide = toggles.not('[data-tags~="'+$(this).data('tag')+'"]');

				sortLinks.removeClass('activeFilter');
				$(this).addClass('activeFilter');
				heading.filter('.activeTitle').trigger('click');
				show.slideDown();
				hide.slideUp();
			});


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

				var open = heading.filter('[data-fake-id="'+hash+'"]');

				if( open.length )
				{
					if( !open.is('.activeTitle') )
					{
						open.trigger('click');
					}

					window.scrollTo(0, container.offset().top - 70);
				}
			}

			trigger_default_open(false);

			$('a').on( 'click', function()
			{
				var hash = $(this).attr('href');
				if(typeof hash != "undefined" && hash)
				{
					hash = hash.replace(/^.*?#/,'');
					trigger_default_open('#'+hash);
				}
			});

		});
	};

}(jQuery));

