// -------------------------------------------------------------------------------------------
// Tab Shortcode
// -------------------------------------------------------------------------------------------

(function($)
{ 
	"use strict";

	$.fn.avia_sc_tabs= function(options)
	{
		var defaults =
		{
			heading: '.tab',
			content:'.tab_content',
			active:'active_tab',
			sidebar: false
		};
	
		var win = $(window),
			options = $.extend(defaults, options);
	
		return this.each(function()
		{
			var container 	= $(this),
				tab_titles 	= $('<div class="tab_titles"></div>').prependTo(container),
				tabs 		= $(options.heading, container),
				content 	= $(options.content, container),
				newtabs 	= false,
				oldtabs 	= false;
	
			newtabs = tabs.clone();
			oldtabs = tabs.addClass('fullsize-tab').attr('aria-hidden', true );
			tabs = newtabs;
	
			tabs.prependTo(tab_titles).each(function(i)
			{
				var tab = $(this), the_oldtab = false;
	
				if(newtabs) the_oldtab = oldtabs.filter(':eq('+i+')');
	
				tab.addClass('tab_counter_'+i).on('click', function()
				{
					open_content(tab, i, the_oldtab);
					return false;
				});
				
				tab.on('keydown', function(objEvent)
				{
					if (objEvent.keyCode === 13) { // if user presses 'enter'
								tab.trigger('click');
							}
				});
	
				if(newtabs)
				{
					the_oldtab.on('click', function()
					{
						open_content(the_oldtab, i, tab);
						return false;
					});
					
					the_oldtab.on('keydown', function(objEvent)
					{
						if (objEvent.keyCode === 13) { // if user presses 'enter'
									the_oldtab.trigger('click');
								}
					});
				}
			});
	
			set_size();
			trigger_default_open(false);
			win.on("debouncedresize", set_size);
			
	        $('a').on('click',function(){
	            var hash = $(this).attr('href');
	            if(typeof hash != "undefined" && hash)
	            {
	                hash = hash.replace(/^.*?#/,'');
	                trigger_default_open('#'+hash);
	            }
	        });
	
			function set_size()
			{
				if(!options.sidebar) return;
				content.css({'min-height': tab_titles.outerHeight() + 1});
			}
	
			function open_content(tab, i, alternate_tab)
			{
				if(!tab.is('.'+options.active))
				{
					$('.'+options.active, container).removeClass(options.active);
					$('.'+options.active+'_content', container).attr('aria-hidden', true).removeClass(options.active+'_content');
	
					tab.addClass(options.active);
	
					var new_loc = tab.data('fake-id');
					if(typeof new_loc == 'string') location.replace(new_loc);
	
					if(alternate_tab) alternate_tab.addClass(options.active);
					var active_c = content.filter(':eq('+i+')').addClass(options.active+'_content').attr('aria-hidden', false);
	
					if(typeof click_container != 'undefined' && click_container.length)
					{
						sidebar_shadow.height(active_c.outerHeight());
					}
					
					//check if tab title is in viewport. if not scroll up
					var el_offset = active_c.offset().top,
						scoll_target = el_offset - 50 - parseInt($('html').css('margin-top'),10);
					
					if(win.scrollTop() > el_offset)
					{
						$('html:not(:animated),body:not(:animated)').scrollTop(scoll_target);
					}
				}
				
				win.trigger( 'av-content-el-height-changed', tab );
			}
	
			function trigger_default_open(hash)
			{
				if(!hash && window.location.hash) hash = window.location.hash;
	            		if(!hash) return;
	            		
				var open = tabs.filter('[data-fake-id="'+hash+'"]');
	
				if(open.length)
				{
					if(!open.is('.active_tab')) open.trigger('click');
					window.scrollTo(0, container.offset().top - 70);
				}
			}
	
		});
	};

	
}(jQuery));