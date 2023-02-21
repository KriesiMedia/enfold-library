/*
 * Implements the hamburger menu behaviour
 *
 *
 * @since 4.8		moved from avia.js to own file as some user request to customize this feature
 */
(function($)
{
    "use strict";

    $(function()
    {
		$.avia_utilities = $.avia_utilities || {};

		if( 'undefined' == typeof $.avia_utilities.isMobile )
		{
			if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) && 'ontouchstart' in document.documentElement )
			{
				$.avia_utilities.isMobile = true;
			}
			else
			{
				$.avia_utilities.isMobile = false;
			}
		}

		//activates the hamburger mobile menu
		avia_hamburger_menu();

		$(window).trigger( 'resize' );

	});

	$.avia_utilities = $.avia_utilities || {};

	function avia_hamburger_menu()
	{
		var header		= $('#header'),
			header_main	= $('#main .av-logo-container'), //check if we got a top menu that is above the header
			menu		= $('#avia-menu'),
			burger_wrap = $('.av-burger-menu-main a'),
			htmlEL  	= $('html').eq(0),
			overlay		= $('<div class="av-burger-overlay"></div>'),
			overlay_scroll	= $('<div class="av-burger-overlay-scroll"></div>').appendTo(overlay),
			inner_overlay 	= $('<div class="av-burger-overlay-inner"></div>').appendTo(overlay_scroll),
			bgColor 	  	= $('<div class="av-burger-overlay-bg"></div>').appendTo(overlay),
			animating 	  	= false,
			first_level	  	= {},
			logo_container	= $('.av-logo-container .inner-container'),
			menu_in_logo_container = logo_container.find('.main_menu'),
			cloneFirst		= htmlEL.is('.html_av-submenu-display-click.html_av-submenu-clone, .html_av-submenu-display-hover.html_av-submenu-clone'),
			menu_generated 	= false,
			cloned_menu_cnt = 0;

		if( ! htmlEL.hasClass( 'html_av-submenu-hidden' ) )
		{
			htmlEL.addClass( 'html_av-submenu-visible' );
		}

		/**
		 * Check for alternate mobile menu
		 */
		var alternate = $('#avia_alternate_menu');
		if( alternate.length > 0 )
		{
			menu = alternate;
		}

		var	set_list_container_height = function()
			{
				//necessary for ios since the height is usually not 100% but 100% - menu bar which can be requested by window.innerHeight
				if($.avia_utilities.isMobile)
				{
					overlay_scroll.outerHeight(window.innerHeight);
				}
			},
			create_list = function( items , append_to )
			{
				if( ! items )
				{
					return;
				}

				var list, link, current, subitems, megacolumns, sub_current, sub_current_list, new_li, new_ul;

				items.each(function()
				{
					current  = $(this);
					subitems = current.find(' > .sub-menu > li'); //find sublists of a regular defined menu
					if( subitems.length == 0 )
					{
						subitems = current.find(' > .children > li'); //find sublists of a fallback menu
					}
					megacolumns = current.find( '.avia_mega_div > .sub-menu > li.menu-item' );

					//	href = '#': we have a custom link that should not link to something - is also in use by megamenu for titles
					var cur_menu = current.find('>a');
					var clone_events = true;

					if( cur_menu.length )
					{
						if( cur_menu.get(0).hash == '#' || 'undefined' == typeof cur_menu.attr('href') || cur_menu.attr('href') == '#' )
						{
							// eventhandler conflict 'click' by megamenu (returns false) - ignore all handlers
							if( subitems.length > 0 || megacolumns.length > 0 )
							{
								clone_events = false;
							}
						}
					}

					link = cur_menu.clone(clone_events).attr('style','');

						//	megamenus can have '' as url in top menu - allow click event in burger
					if( 'undefined' == typeof cur_menu.attr('href') )
					{
						link.attr( 'href', '#' );
					}

					new_li = $('<li>').append( link );

					//	Copy user set classes for menu items - these must not start with menu-item, page-item, page_item (used by default classes)
					var cls = [];
					if( 'undefined' != typeof current.attr('class') )
					{
						cls = current.attr('class').split(/\s+/);
						$.each( cls, function( index, value ){
										if( ( value.indexOf('menu-item') != 0 ) && ( value.indexOf('page-item') < 0 ) && ( value.indexOf('page_item') != 0 ) && ( value.indexOf('dropdown_ul') < 0 ) )
										{
											//	'current-menu-item' is also copied !!
											new_li.addClass( value );
										}
										return true;
									});
					}

					if( 'undefined' != typeof current.attr('id') && '' != current.attr('id') )
					{
						new_li.addClass(current.attr('id'));
					}
					else
					{
						//	fallback menu has no id -> try to find page id in class
						$.each( cls, function( index, value ){
										if( value.indexOf('page-item-') >= 0 )
										{
											new_li.addClass(value);
											return false;
										}
								});
					}

					append_to.append(new_li);

					if(subitems.length)
					{
						new_ul = $('<ul class="sub-menu">').appendTo(new_li);

						if(cloneFirst && ( link.get(0).hash != '#' && link.attr('href') != '#' ))
						{
							new_li.clone(true).prependTo(new_ul);
						}

						new_li.addClass('av-width-submenu').find('>a').append('<span class="av-submenu-indicator">');

						create_list( subitems , new_ul);
					}
					else if(megacolumns.length)	//if we got no normal sublists try megamenu columns and sublists
					{
						new_ul = $('<ul class="sub-menu">').appendTo(new_li);

						if(cloneFirst && ( link.get(0).hash != '#' && link.attr('href') != '#' ))
						{
							new_li.clone(true).prependTo(new_ul);
						}

						megacolumns.each(function(iteration)
						{
							var megacolumn		= $(this),
								mega_current  	= megacolumn.find( '> .sub-menu' ),		//	can be 0 if only a column is used without submenus
								mega_title 		= megacolumn.find( '> .mega_menu_title' ),
								mega_title_link = mega_title.find('a').attr('href') || "#",
								current_megas 	= mega_current.length > 0 ? mega_current.find('>li') : null,
								mega_title_set  = false,
								mega_link 		= new_li.find('>a'),
								hide_enty		= '';

							//	ignore columns that have no actual link and no subitems
							if( ( current_megas === null ) || ( current_megas.length == 0 ) )
							{
								if( mega_title_link == '#' )
								{
									hide_enty = ' style="display: none;"';
								}
							}

							if(iteration == 0) new_li.addClass('av-width-submenu').find('>a').append('<span class="av-submenu-indicator">');

							//if we got a title split up submenu items into multiple columns
							if(mega_title.length && mega_title.text() != "")
							{
								mega_title_set  = true;

								//if we are within the first iteration we got a new submenu, otherwise we start a new one
								if(iteration > 0)
								{
									var check_li = new_li.parents('li').eq(0);

									if(check_li.length) new_li = check_li;

									new_ul = $('<ul class="sub-menu">').appendTo(new_li);
								}


								new_li = $('<li' + hide_enty + '>').appendTo(new_ul);
								new_ul = $('<ul class="sub-menu">').appendTo(new_li);

								$('<a href="'+mega_title_link+'"><span class="avia-bullet"></span><span class="avia-menu-text">' +mega_title.text()+ '</span></a>').insertBefore(new_ul);
								mega_link = new_li.find('>a');

								//	Clone if we have submenus
								if(cloneFirst && ( mega_current.length > 0 ) && ( mega_link.length && mega_link.get(0).hash != '#' && mega_link.attr('href') != '#' ))
								{
									new_li.clone(true).addClass('av-cloned-title').prependTo(new_ul);
								}

							}

								//	do not append av-submenu-indicator if no submenus (otherwise link action is blocked !!!)
							if( mega_title_set && ( mega_current.length > 0 ) )
							{
								new_li.addClass('av-width-submenu').find('>a').append('<span class="av-submenu-indicator">');
							}

							create_list( current_megas , new_ul);
						});

					}

				});

				burger_wrap.trigger( 'avia_burger_list_created' );
				return list;
			};

		var burger_ul, burger;

		//prevent scrolling of outer window when scrolling inside
		$('body').on( 'mousewheel DOMMouseScroll touchmove', '.av-burger-overlay-scroll', function (e)
		{
			var height = this.offsetHeight,
				scrollHeight = this.scrollHeight,
				direction = e.originalEvent.wheelDelta;

			if(scrollHeight != this.clientHeight)
			{
				if( ( this.scrollTop >= (scrollHeight - height) && direction < 0) || (this.scrollTop <= 0 && direction > 0) )
				{
			      e.preventDefault();
			    }
		    }
		    else
		    {
				e.preventDefault();
		    }
		});

		//prevent scrolling for the rest of the screen
		$(document).on( 'mousewheel DOMMouseScroll touchmove', '.av-burger-overlay-bg, .av-burger-overlay-active .av-burger-menu-main', function (e)
		{
			e.preventDefault();
		});

		//prevent scrolling on mobile devices
		var touchPos = {};

		$(document).on('touchstart', '.av-burger-overlay-scroll', function(e)
		{
			touchPos.Y = e.originalEvent.touches[0].clientY;
		});

		$(document).on('touchend', '.av-burger-overlay-scroll', function(e)
		{
			touchPos = {};
		});

		//prevent rubberband scrolling http://blog.christoffer.me/six-things-i-learnt-about-ios-safaris-rubber-band-scrolling/
		$(document).on( 'touchmove', '.av-burger-overlay-scroll', function (e)
		{
			if(!touchPos.Y)
			{
				touchPos.Y = e.originalEvent.touches[0].clientY;
			}

			var	differenceY = e.originalEvent.touches[0].clientY - touchPos.Y,
				element 	= this,
				top 		= element.scrollTop,
				totalScroll = element.scrollHeight,
				currentScroll = top + element.offsetHeight,
				direction	  = differenceY > 0 ? "up" : "down";

			$('body').get(0).scrollTop = touchPos.body;

	        if( top <= 0 )
	        {
	            if( direction == "up" )
				{
					e.preventDefault();
				}

	        }
			else if( currentScroll >= totalScroll )
	        {
	            if( direction == "down" )
				{
					e.preventDefault();
				}
	        }
		});

		$(window).on( 'debouncedresize', function (e)
		{
			var close = true;

			//	@since 4.8.3 we support portrait/landscape screens to switch mobile menu
			if( $.avia_utilities.isMobile && htmlEL.hasClass( 'av-mobile-menu-switch-portrait' ) && htmlEL.hasClass( 'html_text_menu_active' ) )
			{
				var height = $( window ).height();
				var width = $( window ).width();

				if( width <= height )
				{
					//	in portrait mode we only need to remove added class
					htmlEL.removeClass( 'html_burger_menu' );
				}
				else
				{
					//	in landscape mode
					var switch_width = htmlEL.hasClass( 'html_mobile_menu_phone' ) ? 768 : 990;
					if( height < switch_width )
					{
						htmlEL.addClass( 'html_burger_menu' );
						close = false;
					}
					else
					{
						htmlEL.removeClass( 'html_burger_menu' );
					}
				}
			}


			//	close burger menu when returning to desktop
			if( close && burger && burger.length )
			{
				if( ! burger_wrap.is(':visible') )
				{
					burger.filter(".is-active").parents('a').eq(0).trigger('click');
				}
			}

			set_list_container_height();
		});

		//close overlay on overlay click
		$('.html_av-overlay-side').on( 'click', '.av-burger-overlay-bg', function (e)
		{
			e.preventDefault();
			burger.parents('a').eq(0).trigger('click');
		});

		 //close overlay when smooth scrollign begins
		$(window).on('avia_smooth_scroll_start', function()
		{
			if(burger && burger.length)
			{
				burger.filter(".is-active").parents('a').eq(0).trigger('click');
			}
		});


		//toogle hide/show for submenu items
		$('.html_av-submenu-display-hover').on( 'mouseenter', '.av-width-submenu', function (e)
		{
			$(this).children("ul.sub-menu").slideDown('fast');
		});

		$('.html_av-submenu-display-hover').on( 'mouseleave', '.av-width-submenu', function (e)
		{
			$(this).children("ul.sub-menu").slideUp('fast');
		});

		$('.html_av-submenu-display-hover').on( 'click', '.av-width-submenu > a', function (e)
		{
			e.preventDefault();
			e.stopImmediatePropagation();
		});

			//	for mobile we use same behaviour as submenu-display-click
		$('.html_av-submenu-display-hover').on( 'touchstart', '.av-width-submenu > a', function (e)
		{
			var menu = $(this);
			toggle_submenu( menu, e );
		});


		//toogle hide/show for submenu items
		$('.html_av-submenu-display-click').on( 'click', '.av-width-submenu > a', function (e)
		{
			var menu = $(this);
			toggle_submenu( menu, e );
		});


		//	close mobile menu if click on active menu item
		$('.html_av-submenu-display-click, .html_av-submenu-visible').on( 'click', '.av-burger-overlay a', function (e)
		{
			var loc = window.location.href.match(/(^[^#]*)/)[0];
			var cur = $(this).attr('href').match(/(^[^#]*)/)[0];

			if( cur == loc )
			{
				e.preventDefault();
				e.stopImmediatePropagation();

				burger.parents('a').eq(0).trigger('click');
				return false;
			}

			return true;
		});


		function toggle_submenu( menu, e )
		{
			e.preventDefault();
			e.stopImmediatePropagation();

			var parent  = menu.parents('li').eq(0);

			parent.toggleClass('av-show-submenu');

			if(parent.is('.av-show-submenu'))
			{
				parent.children("ul.sub-menu").slideDown('fast');
			}
			else
			{
				parent.children("ul.sub-menu").slideUp('fast');
			}
		};


		(function normalize_layout()
		{
			//if we got the menu outside of the main menu container we need to add it to the container as well
			if( menu_in_logo_container.length )
			{
				return;
			}

			var menu2 = $('#header .main_menu').clone(true),
				ul = menu2.find('ul.av-main-nav'),
				id = ul.attr('id');

			if( 'string' == typeof id && '' != id.trim() )
			{
				ul.attr('id', id + '-' + cloned_menu_cnt++ );
			}
			menu2.find('.menu-item:not(.menu-item-avia-special)').remove();
			menu2.insertAfter(logo_container.find('.logo').first());

			//check if we got social icons and append it to the secondary menu
			var social = $('#header .social_bookmarks').clone(true);
			if( ! social.length )
			{
				social = $('.av-logo-container .social_bookmarks').clone(true);
			}

			if( social.length )
			{
				menu2.find('.avia-menu').addClass('av_menu_icon_beside');
				menu2.append(social);
			}

			//re select the burger menu if we added a new one
			burger_wrap = $('.av-burger-menu-main a');
		}());



		burger_wrap.on('click', function(e)
		{
			if( animating )
			{
				return;
			}

			burger = $(this).find('.av-hamburger'),
			animating = true;

			if(!menu_generated)
			{
				menu_generated = true;
				burger.addClass("av-inserted-main-menu");

				burger_ul = $('<ul>').attr({id:'av-burger-menu-ul', class:''});
				var first_level_items = menu.find('> li:not(.menu-item-avia-special)'); //select all first level items that are not special items
				var	list = create_list( first_level_items , burger_ul);

				burger_ul.find('.noMobile').remove(); //remove any menu items with the class noMobile so user can filter manually if he wants
				burger_ul.appendTo(inner_overlay);
				first_level = inner_overlay.find('#av-burger-menu-ul > li');

				if($.fn.avia_smoothscroll)
				{
					$('a[href*="#"]', overlay).avia_smoothscroll(overlay);
				}
			}

			if(burger.is(".is-active"))
			{
				burger.removeClass("is-active");
				htmlEL.removeClass("av-burger-overlay-active-delayed");

				overlay.animate({opacity:0}, function()
	    		{
	    			overlay.css({display:'none'});
					htmlEL.removeClass("av-burger-overlay-active");
					animating = false;
	    		});

 			}
			else
			{
				set_list_container_height();

				var offsetTop = header_main.length ? header_main.outerHeight() + header_main.position().top : header.outerHeight() + header.position().top;

				overlay.appendTo($(e.target).parents('.avia-menu'));

				burger_ul.css({padding:( offsetTop ) + "px 0px"});

				first_level.removeClass('av-active-burger-items');

				burger.addClass("is-active");
				htmlEL.addClass("av-burger-overlay-active");
				overlay.css({display:'block'}).animate({opacity:1}, function()
				{
					animating = false;
				});

				setTimeout(function()
				{
					htmlEL.addClass("av-burger-overlay-active-delayed");
				}, 100);

				first_level.each(function(i)
				{
					var _self = $(this);
					setTimeout(function()
					{
						_self.addClass('av-active-burger-items');
					}, (i + 1) * 125);
				});

			}

			e.preventDefault();
		});
	}

})( jQuery );
