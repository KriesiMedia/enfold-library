(function($)
{	
    "use strict";
	
	//fix back forward cache issue: http://stackoverflow.com/questions/11979156/mobile-safari-back-button
    $(window).bind("pageshow", function(event) {
	    if (event.originalEvent.persisted) {
	     avia_site_preloader();
	    }
	});

	
    $(document).ready(function()
    {	    
	    
        var aviabodyclasses = AviaBrowserDetection('html');

		$.avia_utilities = $.avia_utilities || {};
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) && 'ontouchstart' in document.documentElement)
    	{
    		$.avia_utilities.isMobile =  true;
    	}
    	else
    	{
    		$.avia_utilities.isMobile =  false;
    	}

		//activates the hamburger mobile menu
		avia_hamburger_menu();
		
        // decreases header size when user scrolls down
        avia_header_size();
        
        // set sidebar main menu option
        avia_sidebar_menu();
        
        //activates the sticky submenu
		avia_sticky_submenu();        
		
        //show scroll top but1ton
        avia_scroll_top_fade();
        
        //site preloader script
        avia_site_preloader();
        
        //calculate width of content
        aviaCalcContentWidth();
        
        //creates search tooltip
        new $.AviaTooltip({"class": 'avia-search-tooltip',data: 'avia-search-tooltip', event:'click', position:'bottom', scope: "body", attach:'element', within_screen: true});

        //creates relate posts tooltip
        new $.AviaTooltip({"class": 'avia-related-tooltip', data: 'avia-related-tooltip', scope: ".related_posts, .av-share-box", attach:'element', delay:0});

        //creates ajax search
        new $.AviaAjaxSearch({scope:'#header'});

		// actiavte portfolio sorting
		if($.fn.avia_iso_sort)
		$('.grid-sort-container').avia_iso_sort();

		//activates the mega menu javascript
		if($.fn.aviaMegamenu)
		$(".main_menu .menu").aviaMegamenu({modify_position:true});
				
		
		$.avia_utilities.avia_ajax_call();
		
		
    });

	$.avia_utilities = $.avia_utilities || {};
	
	$.avia_utilities.avia_ajax_call = function(container)
	{
		if(typeof container == 'undefined'){ container = 'body';};
		
		
		$('a.avianolink').on('click', function(e){ e.preventDefault(); });
        $('a.aviablank').attr('target', '_blank');

        //activates the prettyphoto lightbox
        $(container).avia_activate_lightbox();
        
        //scrollspy for main menu. must be located before smoothscrolling
		if($.fn.avia_scrollspy)
		{
			if(container == 'body')
			{
				$('body').avia_scrollspy({target:'.main_menu .menu li > a'});
			}
			else
			{
				$('body').avia_scrollspy('refresh');
			}
		}
		
		
		
		//smooth scrooling
		if($.fn.avia_smoothscroll)
		$('a[href*="#"]', container).avia_smoothscroll(container);

		avia_small_fixes(container);

		avia_hover_effect(container);

		avia_iframe_fix(container);

		//activate html5 video player
		if($.fn.avia_html5_activation && $.fn.mediaelementplayer)
		$(".avia_video, .avia_audio", container).avia_html5_activation({ratio:'16:9'});

	}
	
	// -------------------------------------------------------------------------------------------
	// Error log helper
	// -------------------------------------------------------------------------------------------
	
	$.avia_utilities.log = function(text, type, extra)
	{
		if(typeof console == 'undefined'){return;} if(typeof type == 'undefined'){type = "log"} type = "AVIA-" + type.toUpperCase(); 
		console.log("["+type+"] "+text); if(typeof extra != 'undefined') console.log(extra); 
	}



	// -------------------------------------------------------------------------------------------
	// keep track of the browser and content width
	// -------------------------------------------------------------------------------------------


	
	function aviaCalcContentWidth()
	{
			
	var win			= $(window),
		width_select= $('html').is('.html_header_sidebar') ? "#main" : "#header",
		outer		= $(width_select),
		outerParent = outer.parents('div:eq(0)'),
		the_main	= $(width_select + ' .container:first'),
		css_block	= "",
		calc_dimensions = function()
		{	
			var css			= "",
				w_12 		= Math.round( the_main.width() ),
				w_outer		= Math.round( outer.width() ),
				w_inner		= Math.round( outerParent.width() );
			
			//css rules for mega menu	
			css += " #header .three.units{width:"	+ ( w_12 * 0.25)+	"px;}";
			css += " #header .six.units{width:"		+ ( w_12 * 0.50)+	"px;}";
			css += " #header .nine.units{width:"	+ ( w_12 * 0.75)+	"px;}";
			css += " #header .twelve.units{width:"	+( w_12 )		+	"px;}";
			
			//css rules for tab sections
			css += " .av-framed-box .av-layout-tab-inner .container{width:"	+( w_inner )+	"px;}";
			css += " .html_header_sidebar .av-layout-tab-inner .container{width:"	+( w_outer )+	"px;}";
			css += " .boxed .av-layout-tab-inner .container{width:"	+( w_outer )+	"px;}";
			
			//css rules for submenu container
			css += " .av-framed-box#top .av-submenu-container{width:"	+( w_inner )+	"px;}";

			//ie8 needs different insert method
			try{
				css_block.text(css); 
			}
			catch(err){
				css_block.remove();
				css_block = $("<style type='text/css' id='av-browser-width-calc'>"+css+"</style>").appendTo('head:first');
			}
			
		};
		
		
		
		if($('.avia_mega_div').length > 0 || $('.av-layout-tab-inner').length > 0 || $('.av-submenu-container').length > 0)
		{
			css_block = $("<style type='text/css' id='av-browser-width-calc'></style>").appendTo('head:first')
			win.on( 'debouncedresize', calc_dimensions);
			calc_dimensions();
		}
	}





	// -------------------------------------------------------------------------------------------
	// modified SCROLLSPY by bootstrap
	// -------------------------------------------------------------------------------------------

	
	  function AviaScrollSpy(element, options)
	  {
	  	var self = this;
	  
		    var process = $.proxy(self.process, self)
		      , refresh = $.proxy(self.refresh, self)
		      , $element = $(element).is('body') ? $(window) : $(element)
		      , href
		    self.$body = $('body')
		    self.$win = $(window)
		    self.options = $.extend({}, $.fn.avia_scrollspy.defaults, options)
		    self.selector = (self.options.target
		      || ((href = $(element).attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
		      || '')
		    
		   	self.activation_true = false;
		   	
		    if(self.$body.find(self.selector + "[href*='#']").length)
		    {
		    	self.$scrollElement = $element.on('scroll.scroll-spy.data-api', process);
		    	self.$win.on('av-height-change', refresh);
		    	self.$body.on('av_resize_finished', refresh);
		    	self.activation_true = true;
		    	self.checkFirst();
		    	
		    	setTimeout(function()
	  			{
		    		self.refresh()
		    		self.process()
		    		
		    	},100);
		    }
	    
	  }
	
	  AviaScrollSpy.prototype = {
	
	      constructor: AviaScrollSpy
		, checkFirst: function () {
		
			var current = window.location.href.split('#')[0],
				matching_link = this.$body.find(this.selector + "[href='"+current+"']").attr('href',current+'#top');
		}
	    , refresh: function () {
	    
	    if(!this.activation_true) return;
	    
	        var self = this
	          , $targets
	
	        this.offsets = $([])
	        this.targets = $([])
	
	        $targets = this.$body
	          .find(this.selector)
	          .map(function () {
	            var $el = $(this)
	              , href = $el.data('target') || $el.attr('href')
	              , hash = this.hash
	              , hash = hash.replace(/\//g, "")
	              , $href = /^#\w/.test(hash) && $(hash)
	             
	            return ( $href
	              && $href.length
	              && [[ $href.position().top + (!$.isWindow(self.$scrollElement.get(0)) && self.$scrollElement.scrollTop()), href ]] ) || null
	          })
	          .sort(function (a, b) { return a[0] - b[0] })
	          .each(function () {
	            self.offsets.push(this[0])
	            self.targets.push(this[1])
	          })
	          
	      }
	
	    , process: function () {
	    	
	    	if(!this.offsets) return;
	    	if(isNaN(this.options.offset)) this.options.offset = 0;
	    	
	        var scrollTop = this.$scrollElement.scrollTop() + this.options.offset
	          , scrollHeight = this.$scrollElement[0].scrollHeight || this.$body[0].scrollHeight
	          , maxScroll = scrollHeight - this.$scrollElement.height()
	          , offsets = this.offsets
	          , targets = this.targets
	          , activeTarget = this.activeTarget
	          , i

	        if (scrollTop >= maxScroll) {
	          return activeTarget != (i = targets.last()[0])
	            && this.activate ( i )
	        }
			
	        for (i = offsets.length; i--;) {
	          activeTarget != targets[i]
	            && scrollTop >= offsets[i]
	            && (!offsets[i + 1] || scrollTop <= offsets[i + 1])
	            && this.activate( targets[i] )
	        }
	      }
	
	    , activate: function (target) {
	        var active
	          , selector
	
	        this.activeTarget = target
	
	        $(this.selector)
	          .parent('.' + this.options.applyClass)
	          .removeClass(this.options.applyClass)
	
	        selector = this.selector
	          + '[data-target="' + target + '"],'
	          + this.selector + '[href="' + target + '"]'
	
	
	
	        active = $(selector)
	          .parent('li')
	          .addClass(this.options.applyClass)
			  
	        if (active.parent('.sub-menu').length)  {
	          active = active.closest('li.dropdown_ul_available').addClass(this.options.applyClass)
	        }
	
	        active.trigger('activate')
	      }
	
	  }
	
	
	 /* AviaScrollSpy PLUGIN DEFINITION
	  * =========================== */
	
	  $.fn.avia_scrollspy = function (option) {
	    return this.each(function () {
	      var $this = $(this)
	        , data = $this.data('scrollspy')
	        , options = typeof option == 'object' && option
	      if (!data) $this.data('scrollspy', (data = new AviaScrollSpy(this, options)))
	      if (typeof option == 'string') data[option]()
	    })
	  }
	
	  $.fn.avia_scrollspy.Constructor = AviaScrollSpy
	
	  $.fn.avia_scrollspy.calc_offset = function()
	  {
		  var 	offset_1 = (parseInt($('.html_header_sticky #main').data('scroll-offset'), 10)) || 0,
		  		offset_2 = ($(".html_header_sticky:not(.html_top_nav_header) #header_main_alternate").outerHeight()) || 0,
		  		offset_3 = ($(".html_header_sticky.html_header_unstick_top_disabled #header_meta").outerHeight()) || 0,
		  		offset_4 =  1,
		  		offset_5 = parseInt($('html').css('margin-top'),10) || 0,
		  		offset_6 = parseInt($('.av-frame-top ').outerHeight(),10) || 0;
		  
		  return offset_1 + offset_2 + offset_3 + offset_4 + offset_5 + offset_6;
	  }
	
	  $.fn.avia_scrollspy.defaults = 
	  {
	    offset: $.fn.avia_scrollspy.calc_offset(),
	    applyClass: 'current-menu-item'
	  }
	  

	function avia_site_preloader()
	{
		var win = $(window), preloader_active = $('html.av-preloader-active'), pre_wrap;
		
		if( preloader_active.length )
		{	
			var hide = function()
			{
				pre_wrap.avia_animate({opacity:0}, function()
	    		{
	    			preloader_active.removeClass('av-preloader-active');
	    		});
			};
			
			
			pre_wrap = $('.av-siteloader-wrap');
			setTimeout(function()
			{
				$.avia_utilities.preload({container: preloader_active, global_callback: hide });
				
				//fallback
				setTimeout(function()
				{
					if(preloader_active.is('.av-preloader-active')) 
					{ 
						hide(); 
						$.avia_utilities.log('Hide Preloader (Fallback)');
					}
				
				},4000);
				
				if( pre_wrap.is('.av-transition-enabled') )
				{
					var comp = new RegExp(location.host), 
						exclude = " .no-transition, .mfp-iframe, .lightbox-added, .grid-links-ajax a, #menu-item-search a";
					
					preloader_active.on('click', 'a:not('+exclude+')',function(e)
					{	
						if(!e.metaKey && !e.ctrlKey && !e.altKey && !e.shiftKey)
						{	
							var link = this; 
							if(comp.test(link.href) && link.href.split('#')[0] != location.href.split('#')[0] && link.target == "")
							{
								if(link.href.indexOf('mailto:') == -1 && link.href.indexOf('add-to-cart=') == -1 )
								{
							       	e.preventDefault();
							       	preloader_active.addClass('av-preloader-active av-preloader-reactive');
									pre_wrap.avia_animate({opacity:1}, function()
									{
										window.location = link.href;
									});
								}
							}
						}
					});
				}
				
				
			}, 500);
		}
	}


    // -------------------------------------------------------------------------------------------
    // detect browser and add class to body
    // -------------------------------------------------------------------------------------------

    function AviaBrowserDetection(outputClassElement)
    {
        if(typeof($.browser) !== 'undefined')
        {
            var bodyclass = '', version = $.browser.version ? parseInt($.browser.version) : "";

            if($.browser.msie){
                bodyclass += 'avia-msie';
            }else if($.browser.webkit){
                bodyclass += 'avia-webkit';
            }else if($.browser.mozilla)
            {
                bodyclass += 'avia-mozilla';
            }

            if($.browser.version) bodyclass += ' ' + bodyclass + '-' + version + ' ';
            if($.browser.name)    bodyclass += ' avia-' + $.browser.name + ' avia-' +$.browser.name +'-' + version + ' ';

            if($.browser.ipad){
                bodyclass += ' avia-ipad ';
            }else if($.browser.iphone){
                bodyclass += ' avia-iphone ';
            }else if($.browser.android){
                bodyclass += ' avia-android ';
            }else if($.browser.win){
                bodyclass += ' avia-windows ';
            }else if($.browser.mac){
                bodyclass += ' avia-mac ';
            }else if($.browser.linux){
                bodyclass += ' avia-linux ';
            }
        }

        if(outputClassElement) $(outputClassElement).addClass(bodyclass)
        
        return bodyclass;
    }

    // -------------------------------------------------------------------------------------------
	// html 5 videos
	// -------------------------------------------------------------------------------------------
    $.fn.avia_html5_activation = function(options)
	{	
		var defaults =
		{
			ratio: '16:9'
		};

		var options  = $.extend(defaults, options),
			isMobile = $.avia_utilities.isMobile;
		
		// if(isMobile) return;
		
		this.each(function()
		{
		var fv 			= $(this),
	      	id_to_apply = '#' + fv.attr('id'),
	      	posterImg 	= fv.attr('poster');
		

		fv.mediaelementplayer({
		    // if the <video width> is not specified, this is the default
		    defaultVideoWidth: 480,
		    // if the <video height> is not specified, this is the default
		    defaultVideoHeight: 270,
		    // if set, overrides <video width>
		    videoWidth: -1,
		    // if set, overrides <video height>
		    videoHeight: -1,
		    // width of audio player
		    audioWidth: 400,
		    // height of audio player
		    audioHeight: 30,
		    // initial volume when the player starts
		    startVolume: 0.8,
		    // useful for <audio> player loops
		    loop: false,
		    // enables Flash and Silverlight to resize to content size
		    enableAutosize: false,
		    // the order of controls you want on the control bar (and other plugins below)
		    features: ['playpause','progress','current','duration','tracks','volume'],
		    // Hide controls when playing and mouse is not over the video
		    alwaysShowControls: false,
		    // force iPad's native controls
		    iPadUseNativeControls: false,
		    // force iPhone's native controls
		    iPhoneUseNativeControls: false,
		    // force Android's native controls
		    AndroidUseNativeControls: false,
		    // forces the hour marker (##:00:00)
		    alwaysShowHours: false,
		    // show framecount in timecode (##:00:00:00)
		    showTimecodeFrameCount: false,
		    // used when showTimecodeFrameCount is set to true
		    framesPerSecond: 25,
		    // turns keyboard support on and off for this instance
		    enableKeyboard: true,
		    // when this player starts, it will pause other players
		    pauseOtherPlayers: false,
		    poster: posterImg,
		    success: function (mediaElement, domObject) { 
         	
				setTimeout(function()
				{
					if (mediaElement.pluginType == 'flash') 
					{	
						mediaElement.addEventListener('canplay', function() { fv.trigger('av-mediajs-loaded'); }, false);
					}
					else
					{
				        fv.trigger('av-mediajs-loaded').addClass('av-mediajs-loaded');
					}
				         
				     mediaElement.addEventListener('ended', function() {  fv.trigger('av-mediajs-ended'); }, false);  
				     
				},10);
		         
		    },
		    // fires when a problem is detected
		    error: function () { 
		
		    },
		    
		    // array of keyboard commands
		    keyActions: []
			});
				
			});
		}



 	// -------------------------------------------------------------------------------------------
	// hover effect for images
	// -------------------------------------------------------------------------------------------
    function avia_hover_effect(container)
    {
    	//hover overlay for mobile device doesnt really make sense. in addition it often slows down the click event
    	if($.avia_utilities.isMobile) return;
    
		var overlay = "", cssTrans = $.avia_utilities.supports('transition');
		
		if(container == 'body')
    	{
    		var elements = $('#main a img').parents('a').not('.noLightbox, .noLightbox a, .avia-gallery-thumb a, .ls-wp-container a, .noHover, .noHover a, .av-logo-container .logo a').add('#main .avia-hover-fx');
    	}
    	else
    	{
    		var elements = $('a img', container).parents('a').not('.noLightbox, .noLightbox a, .avia-gallery-thumb a, .ls-wp-container a, .noHover, .noHover a, .av-logo-container .logo a').add('.avia-hover-fx', container);
    	}

	   elements.each(function(e)
       {
            var link = $(this), 
            	current = link.find('img:first');

            if(current.hasClass('alignleft')) link.addClass('alignleft').css({float:'left', margin:0, padding:0});
            if(current.hasClass('alignright')) link.addClass('alignright').css({float:'right', margin:0, padding:0});
            if(current.hasClass('aligncenter')) link.addClass('aligncenter').css({float:'none','text-align':'center', margin:0, padding:0});

            if(current.hasClass('alignnone'))
            {
               link.addClass('alignnone').css({margin:0, padding:0});;
               if(!link.css('display') || link.css('display') == 'inline') { link.css({display:'inline-block'}); }
            }
            
            if(!link.css('position') || link.css('position') == 'static') { link.css({position:'relative', overflow:'hidden'}); }
            
            var url		 	= link.attr('href'),
				span_class	= "overlay-type-video",
				opa			= link.data('opacity') || 0.7,
				overlay_offset = 5,
				overlay 	= link.find('.image-overlay');
            	
            	if(url)
				{
					if( url.match(/(jpg|gif|jpeg|png|tif)/) ) span_class = "overlay-type-image";
					if(!url.match(/(jpg|gif|jpeg|png|\.tif|\.mov|\.swf|vimeo\.com|youtube\.com)/) ) span_class = "overlay-type-extern";
				}
				
				if(!overlay.length)
				{
					overlay = $("<span class='image-overlay "+span_class+"'><span class='image-overlay-inside'></span></span>").appendTo(link);
				}
            	
            	link.on('mouseenter', function(e)
				{
					var current = link.find('img:first'),
						_self	= current.get(0),
						outerH 	= current.outerHeight(),
						outerW 	= current.outerWidth(),
						pos		= current.position(),
						linkCss = link.css('display'),
						overlay = link.find('.image-overlay');
					
					if(outerH > 100)
					{
						
						if(!overlay.length)
						{
							overlay = $("<span class='image-overlay "+span_class+"'><span class='image-overlay-inside'></span></span>").appendTo(link);
							
						}
						//can be wrapped into if !overlay.length statement if chrome fixes fade in problem
						if(link.height() == 0) { link.addClass(_self.className); _self.className = ""; }
						if(!linkCss || linkCss == 'inline') { link.css({display:'block'}); }
						//end wrap
						
						overlay.css({left:(pos.left - overlay_offset) + parseInt(current.css("margin-left"),10), top:pos.top + parseInt(current.css("margin-top"),10)})
							   .css({overflow:'hidden',display:'block','height':outerH,'width':(outerW + (2*overlay_offset))});
							   
						if(cssTrans === false ) overlay.stop().animate({opacity:opa}, 400);
					}
					else
					{
						overlay.css({display:"none"});
					}
		
				}).on('mouseleave', elements, function(){
		
					if(overlay.length)
					{
						if(cssTrans === false ) overlay.stop().animate({opacity:0}, 400);
					}
				});
        });
    }








// -------------------------------------------------------------------------------------------
// Smooth scrooling when clicking on anchor links
// todo: maybe use https://github.com/ryanburnette/scrollToBySpeed/blob/master/src/scrolltobyspeed.jquery.js in the future
// -------------------------------------------------------------------------------------------

	(function($)
	{
		$.fn.avia_smoothscroll = function(apply_to_container)
		{
			if(!this.length) return;
				
			var the_win = $(window),
				$header = $('#header'),
				$main 	= $('.html_header_top.html_header_sticky #main').not('.page-template-template-blank-php #main'),
				$meta 	= $('.html_header_top.html_header_unstick_top_disabled #header_meta'),
				$alt  	= $('.html_header_top:not(.html_top_nav_header) #header_main_alternate'),
				menu_above_logo = $('.html_header_top.html_top_nav_header'),
				shrink	= $('.html_header_top.html_header_shrinking').length,
				frame	= $('.av-frame-top'),
				fixedMainPadding = 0,
				isMobile = $.avia_utilities.isMobile,
				sticky_sub = $('.sticky_placeholder:first'), 
				calc_main_padding= function()
				{
					if($header.css('position') == "fixed")
					{
						var tempPadding  		= parseInt($main.data('scroll-offset'),10) || 0,
							non_shrinking		= parseInt($meta.outerHeight(),10) || 0,
							non_shrinking2		= parseInt($alt.outerHeight(),10) || 0; 
						
						if(tempPadding > 0 && shrink) 
						{
							tempPadding = (tempPadding / 2 ) + non_shrinking + non_shrinking2;
						}
						else
						{
							tempPadding = tempPadding + non_shrinking + non_shrinking2;
						}
						
						tempPadding += parseInt($('html').css('margin-top'),10);
						fixedMainPadding = tempPadding; 
					}
					else
					{
						fixedMainPadding = parseInt($('html').css('margin-top'),10);
					}
					
					if(frame.length){
						fixedMainPadding += frame.height();
					}
					
					if(menu_above_logo.length)
					{
						//if menu is above logo and we got a sticky height header
						fixedMainPadding = $('.html_header_sticky #header_main_alternate').height() + parseInt($('html').css('margin-top'),10);
					}
					
					if(isMobile)
					{
						fixedMainPadding = 0;
					}
					
				};
			
			if(isMobile) shrink = false;
			
			calc_main_padding();
			the_win.on("debouncedresize av-height-change",  calc_main_padding);

			var hash = window.location.hash.replace(/\//g, "");
			
			//if a scroll event occurs at pageload and an anchor is set and a coresponding element exists apply the offset to the event
			if (fixedMainPadding > 0 && hash && apply_to_container == 'body' && hash.charAt(1) != "!" && hash.indexOf("=") === -1)
			{
				var scroll_to_el = $(hash), modifier = 0;
				
				if(scroll_to_el.length)
				{
					the_win.on('scroll.avia_first_scroll', function()
					{	
						setTimeout(function(){ //small delay so other scripts can perform necessary resizing
							if(sticky_sub.length && scroll_to_el.offset().top > sticky_sub.offset().top) { modifier = sticky_sub.outerHeight() - 3; }
							the_win.off('scroll.avia_first_scroll').scrollTop( scroll_to_el.offset().top - fixedMainPadding - modifier );
							
						},10); 
				    });
			    }
			}
			
			return this.each(function()
			{
				$(this).click(function(e) {

				   var newHash  = this.hash.replace(/\//g, ""),
				   	   clicked  = $(this),
				   	   data		= clicked.data();
					
				   if(newHash != '' && newHash != '#' && newHash != '#prev' && newHash != '#next' && !clicked.is('.comment-reply-link, #cancel-comment-reply-link, .no-scroll'))
				   {
					   var container = "", originHash = "";
					   
					   if("#next-section" == newHash)
					   {
					   		originHash  = newHash;
					   		container   = clicked.parents('.container_wrap:eq(0)').nextAll('.container_wrap:eq(0)');
					   		newHash		= '#' + container.attr('id') ;
					   }
					   else
					   {
					   		container = $(this.hash.replace(/\//g, ""));
					   }
					   
					   

						if(container.length)
						{
							var cur_offset = the_win.scrollTop(),
								container_offset = container.offset().top,
								target =  container_offset - fixedMainPadding,
								hash = window.location.hash,
								hash = hash.replace(/\//g, ""),
								oldLocation=window.location.href.replace(hash, ''),
								newLocation=this,
								duration= data.duration || 1200,
								easing= data.easing || 'easeInOutQuint';
							
							if(sticky_sub.length && container_offset > sticky_sub.offset().top) { target -= sticky_sub.outerHeight() - 3;}
							
							// make sure it's the same location
							if(oldLocation+newHash==newLocation || originHash)
							{
								if(cur_offset != target) // if current pos and target are the same dont scroll
								{
									if(!(cur_offset == 0 && target <= 0 )) // if we are at the top dont try to scroll to top or above
									{
										the_win.trigger('avia_smooth_scroll_start'); 
										
										// animate to target and set the hash to the window.location after the animation
										$('html:not(:animated),body:not(:animated)').animate({ scrollTop: target }, duration, easing, function() {
										
											// add new hash to the browser location
											//window.location.href=newLocation;
											if(window.history.replaceState)
											window.history.replaceState("", "", newHash);
										});
									}
								}
								// cancel default click action
								e.preventDefault();
							}
						}
					}
				});
			});
		};
	})(jQuery);


	// -------------------------------------------------------------------------------------------
	// iframe fix for firefox and ie so they get proper z index
	// -------------------------------------------------------------------------------------------
	function avia_iframe_fix(container)
	{
		var iframe 	= jQuery('iframe[src*="youtube.com"]:not(.av_youtube_frame)', container),
			youtubeEmbed = jQuery('iframe[src*="youtube.com"]:not(.av_youtube_frame) object, iframe[src*="youtube.com"]:not(.av_youtube_frame) embed', container).attr('wmode','opaque');

			iframe.each(function()
			{
				var current = jQuery(this),
					src 	= current.attr('src');

				if(src)
				{
					if(src.indexOf('?') !== -1)
					{
						src += "&wmode=opaque";
					}
					else
					{
						src += "?wmode=opaque";
					}

					current.attr('src', src);
				}
			});
	}

	// -------------------------------------------------------------------------------------------
	// small js fixes for pixel perfection :)
	// -------------------------------------------------------------------------------------------
	function avia_small_fixes(container)
	{
		if(!container) container = document;

		//make sure that iframes do resize correctly. uses css padding bottom iframe trick
		var win		= jQuery(window),
			iframes = jQuery('.avia-iframe-wrap iframe:not(.avia-slideshow iframe):not( iframe.no_resize):not(.avia-video iframe)', container),
			adjust_iframes = function()
			{
				iframes.each(function(){

					var iframe = jQuery(this), parent = iframe.parent(), proportions = 56.25;

					if(this.width && this.height)
					{
						proportions = (100/ this.width) * this.height;
						parent.css({"padding-bottom":proportions+"%"});
					}
				});
			};

			adjust_iframes();

	}

	// -------------------------------------------------------------------------------------------
	// Ligthbox activation
	// -------------------------------------------------------------------------------------------

	(function($)
	{
		$.fn.avia_activate_lightbox = function(variables)
		{
			
			var defaults = {
				groups			:	['.avia-slideshow', '.avia-gallery', '.av-instagram-pics', '.portfolio-preview-image', '.portfolio-preview-content', '.isotope', '.post-entry', '.sidebar', '#main', '.main_menu'], 
				autolinkElements:   'a.lightbox, a[rel^="prettyPhoto"], a[rel^="lightbox"], a[href$=jpg], a[href$=png], a[href$=gif], a[href$=jpeg], a[href*=".jpg?"], a[href*=".png?"], a[href*=".gif?"], a[href*=".jpeg?"], a[href$=".mov"] , a[href$=".swf"] , a:regex(href, .vimeo\.com/[0-9]) , a[href*="youtube.com/watch"] , a[href*="screenr.com"], a[href*="iframe=true"]',
				videoElements	: 	'a[href$=".mov"] , a[href$=".swf"] , a:regex(href, .vimeo\.com/[0-9]) , a[href*="youtube.com/watch"] , a[href*="screenr.com"], a[href*="iframe=true"]',
				exclude			:	'.noLightbox, .noLightbox a, .fakeLightbox, .lightbox-added, a[href*="dropbox.com"]',
			},
			
			options = $.extend({}, defaults, variables),
			
			av_popup = {
				type: 				'image',
				mainClass: 			'avia-popup mfp-zoom-in',
				tLoading: 			'',
				tClose: 			'',
				removalDelay: 		300, //delay removal by X to allow out-animation
				closeBtnInside: 	true,
				closeOnContentClick:false,
				midClick: 			true,
				fixedContentPos: 	false, // allows scrolling when lightbox is open but also removes any jumping because of scrollbar removal
				
				image: {
				    titleSrc: function(item){
					    var title = item.el.attr('title');
					    if(!title) title = item.el.find('img').attr('title');
					    if(!title) title = item.el.parent().next('.wp-caption-text').html();
					    if(typeof title == "undefined") return "";
					    return title;
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
						$.magnificPopup.instance.next = function() {
							var self = this;
							self.wrap.removeClass('mfp-image-loaded');
							setTimeout(function() { $.magnificPopup.proto.next.call(self); }, 120);
						}
						$.magnificPopup.instance.prev = function() {
							var self = this;
							self.wrap.removeClass('mfp-image-loaded');
							setTimeout(function() { $.magnificPopup.proto.prev.call(self); }, 120);
						}
						
						//add custom css class for different styling
						if( this.st.el && this.st.el.data('av-extra-class') )
						{
							this.wrap.addClass( this.currItem.el.data('av-extra-class') );
						}
						
						
					},
					imageLoadComplete: function() 
					{	
						var self = this;
						setTimeout(function() { self.wrap.addClass('mfp-image-loaded'); }, 16);
					},
					change: function() {
					    
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
					},
				}
			},
			
			active = !$('html').is('.av-custom-lightbox');
			
			if(!active) return this;
			
			return this.each(function()
			{
				var container	= $(this),
					videos		= $(options.videoElements, this).not(options.exclude).addClass('mfp-iframe'), /*necessary class for the correct lightbox markup*/
					ajaxed		= !container.is('body') && !container.is('.ajax_slide');
					
					for (var i = 0; i < options.groups.length; i++) 
					{
						container.find(options.groups[i]).each(function() 
						{ 
							var links = $(options.autolinkElements, this);
						
							if(ajaxed) links.removeClass('lightbox-added');
							links.not(options.exclude).addClass('lightbox-added').magnificPopup(av_popup);
						});
					}
				
			});
		}
	})(jQuery);








// -------------------------------------------------------------------------------------------
// Avia Menu
// -------------------------------------------------------------------------------------------

(function($)
{
	$.fn.aviaMegamenu = function(variables)
	{
		var defaults =
		{
			modify_position:true,
			delay:300
		};

		var options = $.extend(defaults, variables),
		win			= $(window);
		
			

		return this.each(function()
		{
			var the_html	= $('html:first'),
				main		= $('#main .container:first'),
				left_menu	= the_html.filter('.html_menu_left, .html_logo_center').length,
				isMobile 	= $.avia_utilities.isMobile,
				menu = $(this),
				menuItems = menu.find(">li:not(.ignore_menu)"),
				megaItems = menuItems.find(">div").parent().css({overflow:'hidden'}),
				menuActive = menu.find('>.current-menu-item>a, >.current_page_item>a'),
				dropdownItems = menuItems.find(">ul").parent(),
				parentContainer = menu.parent(),
				mainMenuParent = menu.parents('.main_menu').eq(0),
				parentContainerWidth = parentContainer.width(),
				delayCheck = {},
				mega_open = [];
				

			if(!menuActive.length){ menu.find('.current-menu-ancestor:eq(0) a:eq(0), .current_page_ancestor:eq(0) a:eq(0)').parent().addClass('active-parent-item')}
			if(!the_html.is('.html_header_top')) { options.modify_position = false; }
			
			
			
			menuItems.on('click' ,'a', function(e)
			{
				if(this.href == window.location.href + "#" || this.href == window.location.href + "/#")
				e.preventDefault();
			});

			menuItems.each(function()
			{
				var item = $(this),
					pos = item.position(),
					megaDiv = item.find("div:first").css({opacity:0, display:"none"}),
					normalDropdown = "";

				//check if we got a mega menu
				if(!megaDiv.length)
				{
					normalDropdown = item.find(">ul").css({display:"none"});
				}

				//if we got a mega menu or dropdown menu add the arrow beside the menu item
				if(megaDiv.length || normalDropdown.length)
				{
					var link = item.addClass('dropdown_ul_available').find('>a');
					link.append('<span class="dropdown_available"></span>');

					//is a mega menu main item doesnt have a link to click use the default cursor
					if(typeof link.attr('href') != 'string' || link.attr('href') == "#"){ link.css('cursor','default').click(function(e){ e.preventDefault(); }); }
				}


				//correct position of mega menus
				if(options.modify_position && megaDiv.length)
				{	
					item.on('mouseenter', function(){ calc_offset(item, pos, megaDiv, parentContainerWidth) });
				}



			});
			
			
			function calc_offset(item, pos, megaDiv, parentContainerWidth)
			{	
				pos = item.position();
				
				if(!left_menu)
					{
						if(pos.left + megaDiv.width() < parentContainerWidth)
						{
							megaDiv.css({right: -megaDiv.outerWidth() + item.outerWidth()  });
							//item.css({position:'static'});
						}
						else if(pos.left + megaDiv.width() > parentContainerWidth)
						{
							megaDiv.css({right: -mainMenuParent.outerWidth() + (pos.left + item.outerWidth() ) });
						}
					}
					else
					{
						if(megaDiv.width() > pos.left + item.outerWidth())
						{
							megaDiv.css({left: (pos.left* -1)});
						}
						else if(pos.left + megaDiv.width() > parentContainerWidth)
						{
							megaDiv.css({left: (megaDiv.width() - pos.left) * -1 });
						}
					}
			}

			function megaDivShow(i)
			{
				if(delayCheck[i] == true)
				{
					var item = megaItems.filter(':eq('+i+')').css({overflow:'visible'}).find("div:first"),
						link = megaItems.filter(':eq('+i+')').find("a:first");
						mega_open["check"+i] = true;

						item.stop().css('display','block').animate({opacity:1},300);

						if(item.length)
						{
							link.addClass('open-mega-a');
						}
				}
			}

			function megaDivHide (i)
			{
				if(delayCheck[i] == false)
				{
					megaItems.filter(':eq('+i+')').find(">a").removeClass('open-mega-a');

					var listItem = megaItems.filter(':eq('+i+')'),
						item = listItem.find("div:first");


					item.stop().css('display','block').animate({opacity:0},300, function()
					{
						$(this).css('display','none');
						listItem.css({overflow:'hidden'});
						mega_open["check"+i] = false;
					});
				}
			}

			if(isMobile)
			{
				megaItems.each(function(i){

					$(this).bind('click', function()
					{
						if(mega_open["check"+i] != true) return false;
					});
				});
			}


			//bind event for mega menu
			megaItems.each(function(i){

				$(this).hover(

					function()
					{
						delayCheck[i] = true;
						setTimeout(function(){megaDivShow(i); },options.delay);
					},

					function()
					{
						delayCheck[i] = false;
						setTimeout(function(){megaDivHide(i); },options.delay);
					}
				);
			});


			// bind events for dropdown menu
			dropdownItems.find('li').addBack().each(function()
			{
				var currentItem = $(this),
					sublist = currentItem.find('ul:first'),
					showList = false;

				if(sublist.length)
				{
					sublist.css({display:'block', opacity:0, visibility:'hidden'});
					var currentLink = currentItem.find('>a');

					currentLink.bind('mouseenter', function()
					{
						sublist.stop().css({visibility:'visible'}).animate({opacity:1});
					});

					currentItem.bind('mouseleave', function()
					{
						sublist.stop().animate({opacity:0}, function()
						{
							sublist.css({visibility:'hidden'});
						});
					});

				}

			});

		});
	};
})(jQuery);




// -------------------------------------------------------------------------------------------
//Portfolio sorting
// -------------------------------------------------------------------------------------------

    $.fn.avia_iso_sort = function(options)
	{
		return this.each(function()
		{
			var the_body		= $('body'),
				container		= $(this),
				portfolio_id	= container.data('portfolio-id'),
				parentContainer	= container.parents('.entry-content-wrapper, .avia-fullwidth-portfolio'),
				filter			= parentContainer.find('.sort_width_container[data-portfolio-id="' + portfolio_id + '"]').find('#js_sort_items').css({visibility:"visible", opacity:0}),
				links			= filter.find('a'),
				imgParent		= container.find('.grid-image'),
				isoActive		= false,
				items			= $('.post-entry', container);

			function applyIso()
			{
				container.addClass('isotope_activated').isotope({
					layoutMode : 'fitRows', itemSelector : '.flex_column'
				});
				
				container.isotope( 'on', 'layoutComplete', function()
				{
					container.css({overflow:'visible'});
					the_body.trigger('av_resize_finished');
				}); 
				
				isoActive = true;
				setTimeout(function(){ parentContainer.addClass('avia_sortable_active'); }, 0);
			};

			links.bind('click',function()
			{
				var current		= $(this),
			  		selector	= current.data('filter'),
			  		linktext	= current.html(),
			  		activeCat	= parentContainer.find('.av-current-sort-title');

			  		if(activeCat.length) activeCat.html(linktext);
			  		
					links.removeClass('active_sort');
					current.addClass('active_sort');
					container.attr('id', 'grid_id_'+selector);

					parentContainer.find('.open_container .ajax_controlls .avia_close').trigger('click');
					//container.css({overflow:'hidden'})
					container.isotope({ layoutMode : 'fitRows', itemSelector : '.flex_column' , filter: '.'+selector});

					return false;
			});

			// update columnWidth on window resize
			$(window).on( 'debouncedresize', function()
			{
			  	applyIso();
			});

			$.avia_utilities.preload({container: container, single_callback:  function()
				{
					filter.animate({opacity:1}, 400); applyIso();

					//call a second time to for the initial resizing
					setTimeout(function(){ applyIso(); });

					imgParent.css({height:'auto'}).each(function(i)
					{
						var currentLink = $(this);

						setTimeout(function()
						{
							currentLink.animate({opacity:1},1500);
						}, (100 * i));
					});
				}
			});

		});
	};

	
	
	
	function avia_sticky_submenu()
	{
		var win 		= $(window),
			html 		= $('html:first'),
			header  	= $('.html_header_top.html_header_sticky #header'),
			html_margin = parseInt( $('html:first').css('margin-top'), 10),
			setWitdth	= $('.html_header_sidebar #main, .boxed #main'),
			menus		= $('.av-submenu-container'),
			bordermod	= html.is('.html_minimal_header') ? 0 : 1,
			fixed_frame	= $('.av-frame-top').height(),
			calc_margin	= function()
			{
				html_margin = parseInt( html.css('margin-top'), 10);
				if(!$('.mobile_menu_toggle:visible').length)
				{
					$('.av-open-submenu').removeClass('av-open-submenu');
				}
				
				menus.filter('.av-sticky-submenu').each(function()
				{
					$(this).next('.sticky_placeholder').height($(this).height());
				});
				
			},
			calc_values	= function()
			{
				var content_width = setWitdth.width();
				html_margin = parseInt( html.css('margin-top'), 10);
				menus.width(content_width);
			},
			check 		= function(placeholder, no_timeout)
			{
				var menu_pos	= this.offset().top,
					top_pos 	= placeholder.offset().top,
					scrolled	= win.scrollTop(),
					modifier 	= html_margin, fixed = false;
										
					if(header.length) 
					{
						modifier += header.outerHeight() + parseInt( header.css('margin-top'), 10);
					}	
					
					if(fixed_frame)
					{
						modifier += fixed_frame;
					}
					
					if(scrolled + modifier > top_pos)
					{
						if(!fixed)
						{
							this.css({top: modifier - bordermod, position: 'fixed'}); fixed = true
						}
					}
					else
					{
						this.css({top: 'auto', position: 'absolute'}); fixed = false
					}
					
			},
			toggle = function(e)
			{
				e.preventDefault();
				
				var clicked = $(this), 
					menu 	= clicked.siblings('.av-subnav-menu');
				
					if(menu.hasClass('av-open-submenu'))
					{
						menu.removeClass('av-open-submenu');
					}
					else
					{
						menu.addClass('av-open-submenu');
					}
			};
		
		win.on("debouncedresize av-height-change",  calc_margin ); calc_margin();
			
		if(setWitdth.length)
		{
			win.on("debouncedresize av-height-change",  calc_values );
			calc_values();
		}
		
		
		menus.each(function()
        {
             var menu = $(this), sticky = menu.filter('.av-sticky-submenu'),  placeholder = menu.next('.sticky_placeholder'), mobile_button = menu.find('.mobile_menu_toggle');
             
             
             if(sticky.length) win.on( 'scroll',  function(){ window.requestAnimationFrame( $.proxy( check, sticky, placeholder) )} );

             if(mobile_button.length)
             {
                mobile_button.on( 'click',  toggle );
             }
        });
		
		
		html.on('click', '.av-submenu-hidden .av-open-submenu li a', function()
		{
			var current = $(this);
			
			var list_item = current.siblings('ul, .avia_mega_div');
			if(list_item.length)
			{
				if(list_item.hasClass('av-visible-sublist'))
				{
				    list_item.removeClass('av-visible-sublist');
				}
				else
				{
				    list_item.addClass('av-visible-sublist');
				}
				return false;
			}
		});
		
		$('.avia_mobile').on('click', '.av-menu-mobile-disabled li a', function()
		{
			var current = $(this);
			var list_item = current.siblings('ul');
			if(list_item.length)
			{
				if(list_item.hasClass('av-visible-mobile-sublist'))
				{
				    
				}
				else
				{
					$('.av-visible-mobile-sublist').removeClass('av-visible-mobile-sublist');
				    list_item.addClass('av-visible-mobile-sublist');
				    return false;
				}
				
			}
		});
		
		
		
	}
	
	
	
	function avia_sidebar_menu()
	{
		var win				= $(window),
			main			= $('#main'),
			sb_header		= $('.html_header_sidebar #header_main'),
            sidebar			= $('.html_header_sidebar #header.av_conditional_sticky');
            
        if(!sb_header.length) return;
        // main.css({"min-height":sb_header.outerHeight()});
		
	
            
        if(!sidebar.length) return;
        
        var innerSidebar	= $('#header_main'),
       	 	wrap			= $('#wrap_all'),
       	 	fixed_frame		= $('.av-frame-top').height() * 2,
       	 	subtract 		= parseInt($('html').css('margin-top'), 10),
            calc_values 	= function()
            {	
            	if(innerSidebar.outerHeight() + fixed_frame < win.height()) 
				{ 	
					sidebar.addClass('av_always_sticky'); 
				}
				else
				{
					sidebar.removeClass('av_always_sticky'); 
				}
				
				wrap.css({'min-height': win.height() - subtract});
            };
        
        calc_values(); 
        win.on("debouncedresize av-height-change",  calc_values);
	}
	
	function av_change_class($element, change_method, class_name)
	{	
		if($element[0].classList)
		{
			if(change_method == "add") 
			{
				$element[0].classList.add(class_name);
			}
			else
			{
				$element[0].classList.remove(class_name);
			}
		}
		else
		{
			if(change_method == "add") 
			{
				$element.addClass(class_name);
			}
			else
			{
				$element.removeClass(class_name);
			}
		}
	}
	


    //check if the browser supports element rotation
    function avia_header_size()
    {
        var win				= $(window),
            header          = $('.html_header_top.html_header_sticky #header'),
            unsticktop		= $('.av_header_unstick_top');
            
        if(!header.length && !unsticktop.length) return;
        
        var logo            = $('#header_main .container .logo img, #header_main .container .logo a'),
            elements        = $('#header_main .container:not(#header_main_alternate>.container), #header_main .main_menu ul:first-child > li > a:not(.avia_mega_div a, #header_main_alternate a), #header_main #menu-item-shop .cart_dropdown_link'),
            el_height       = $(elements).filter(':first').height(),
            isMobile        = $.avia_utilities.isMobile,
            scroll_top		= $('#scroll-top-link'),
            transparent 	= header.is('.av_header_transparency'),
            shrinking		= header.is('.av_header_shrinking'),
            topbar_height	= header.find('#header_meta').outerHeight(),
            set_height      = function()
            {	
                var st = win.scrollTop(), newH = 0, st_real = st;
				
				if(unsticktop) st -= topbar_height; 
				if(st < 0) st = 0;
				
				if(shrinking && !isMobile)
                {
	                if(st < el_height/2)
	                {
	                    newH = el_height - st;
	                    if(st <= 0){
							newH = el_height;
					    }
	                    
	                    av_change_class(header, 'remove', 'header-scrolled');
	                    //header.removeClass('header-scrolled');
	                }
	                else
	                {
	                    newH = el_height/2;
	                    //header.addClass('header-scrolled');
	                    av_change_class(header, 'add', 'header-scrolled');
	                }
	                
	                if(st - 30 < el_height)
	                {
	                    av_change_class(header, 'remove', 'header-scrolled-full');
	                }
	                else
	                {
	                    av_change_class(header, 'add', 'header-scrolled-full');
	                }
	                
	                
	                elements.css({'height': newH + 'px', 'lineHeight': newH + 'px'});
                	logo.css({'maxHeight': newH + 'px'});
                }
                
                if(unsticktop.length)
            	{
                	if( st <= 0)
                	{
	                	if(st_real <= 0) st_real = 0;
                		unsticktop.css({"margin-top":"-"+st_real+"px"});
					}
					else
					{
                		unsticktop.css({"margin-top":"-"+topbar_height+"px"});
					}
            	}
                
                if(transparent)
                {	
                	if(st > 50)
                	{	
                		//header.removeClass('av_header_transparency');
                		av_change_class(header, 'remove', 'av_header_transparency');
                	}
                	else
                	{
                		//header.addClass('av_header_transparency');
                		av_change_class(header, 'add', 'av_header_transparency');
                	}
                }

               
            };

            if($('body').is('.avia_deactivate_menu_resize')) shrinking = false;
            
            if(!transparent && !shrinking && !unsticktop.length) return;
            
			win.on( 'debouncedresize',  function(){ el_height = $(elements).attr('style',"").filter(':first').height(); set_height(); } );
            win.on( 'scroll',  function(){ window.requestAnimationFrame( set_height )} );
            set_height();
    }


   function avia_scroll_top_fade()
   {
   		 var win 		= $(window),
   		 	 timeo = false,
   		 	 scroll_top = $('#scroll-top-link'),
   		 	 set_status = function()
             {
             	var st = win.scrollTop();

             	if(st < 500)
             	{
             		scroll_top.removeClass('avia_pop_class');
             	}
             	else if(!scroll_top.is('.avia_pop_class'))
             	{
             		scroll_top.addClass('avia_pop_class');
             	}
             };

   		 win.on( 'scroll',  function(){ window.requestAnimationFrame( set_status )} );
         set_status();
	}
	
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
			set_list_container_height = function()
			{
				//necessary for ios since the height is usually not 100% but 100% - menu bar which can be requested by window.innerHeight
				if($.avia_utilities.isMobile)
				{
					overlay_scroll.outerHeight(window.innerHeight);
				}
			},
			create_list	  	= function( items , append_to )
			{
				if(!items) return;
				
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
				
					link   = cur_menu.clone(clone_events).attr('style','');
					
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
								mega_title_link = mega_title.find('a').attr('href') || "#",
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
							if( mega_title_set && ( mega_current.length > 0 ) ) new_li.addClass('av-width-submenu').find('>a').append('<span class="av-submenu-indicator">');
							create_list( current_megas , new_ul);
						});

					}
					
				});
				
				burger_wrap.trigger( 'avia_burger_list_created' );
				return list;
			};
		
		var burger_ul, burger;
		
		//prevent scrolling of outer window when scrolling inside
		$('body').on( 'mousewheel DOMMouseScroll touchmove', '.av-burger-overlay-scroll', function (e) { 
			
			var height = this.offsetHeight,
				scrollHeight = this.scrollHeight,
				direction = e.originalEvent.wheelDelta;
			
			if(scrollHeight != this.clientHeight)
			{
				if( (this.scrollTop >= (scrollHeight - height) && direction < 0) || (this.scrollTop <= 0 && direction > 0) ) {
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
				
	        if ( top <= 0 ) 
	        {
	            if(direction == "up") e.preventDefault();
	            
	        } else if ( currentScroll >= totalScroll ) 
	        {
	            if(direction == "down") e.preventDefault();
	        }
		});
		
		$(window).on( 'debouncedresize', function (e) 
		{ 
			//	close burger menu when returning to desktop
			if(burger && burger.length)
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
			if(menu_in_logo_container.length) return;
		
			var menu2 = $('#header .main_menu').clone(true);
				menu2.find('.menu-item:not(.menu-item-avia-special)').remove();
				menu2.insertAfter(logo_container.find('.logo'));
				
			//check if we got social icons and append it to the secondary menu	
			var social = $('#header .social_bookmarks').clone(true);
			if(!social.length) social = $('.av-logo-container .social_bookmarks').clone(true);
			
			if( social.length )
			{
				menu2.find('.avia-menu').addClass('av_menu_icon_beside');
				menu2.append(social);
			}
			
			//re select the burger menu if we added a new one
			burger_wrap = $('.av-burger-menu-main a');
		}());
		
			
			
		burger_wrap.click(function(e)
		{	
			if(animating) return;
			burger 		= $(this).find('.av-hamburger'),
			animating 	= true;
			
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
				
				if($.fn.avia_smoothscroll){
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




	$.AviaAjaxSearch  =  function(options)
	{
	   var defaults = {
            delay: 300,                //delay in ms until the user stops typing.
            minChars: 3,               //dont start searching before we got at least that much characters
            scope: 'body'

        };

        this.options = $.extend({}, defaults, options);
        this.scope   = $(this.options.scope);
        this.timer   = false;
        this.lastVal = "";
		
        this.bind_events();
	};


	$.AviaAjaxSearch.prototype =
    {
        bind_events: function()
        {
            this.scope.on('keyup', '#s:not(".av_disable_ajax_search #s")' , $.proxy( this.try_search, this));
        },

        try_search: function(e)
        {
            clearTimeout(this.timer);

            //only execute search if chars are at least "minChars" and search differs from last one
            if(e.currentTarget.value.length >= this.options.minChars && this.lastVal != $.trim(e.currentTarget.value))
            {
                //wait at least "delay" miliseconds to execute ajax. if user types again during that time dont execute
                this.timer = setTimeout($.proxy( this.do_search, this, e), this.options.delay);
            }
        },

        do_search: function(e)
        {
            var obj          = this,
                currentField = $(e.currentTarget).attr( "autocomplete", "off" ),
                form         = currentField.parents('form:eq(0)'),
                results      = form.find('.ajax_search_response'),
                loading      = $('<div class="ajax_load"><span class="ajax_load_inner"></span></div>'),
                action 		 = form.attr('action'),
                values       = form.serialize();
                values      += '&action=avia_ajax_search';

           	//check if the form got get parameters applied and also apply them
           	if(action.indexOf('?') != -1)
           	{
           		action  = action.split('?');
           		values += "&" + action[1];
           	}

            if(!results.length) results = $('<div class="ajax_search_response"></div>').appendTo(form);

            //return if we already hit a no result and user is still typing
            if(results.find('.ajax_not_found').length && e.currentTarget.value.indexOf(this.lastVal) != -1) return;

            this.lastVal = e.currentTarget.value;

            $.ajax({
				url: avia_framework_globals.ajaxurl,
				type: "POST",
				data:values,
				beforeSend: function()
				{
					loading.insertAfter(currentField);
				},
				success: function(response)
				{
				    if(response == 0) response = "";
                    results.html(response);
				},
				complete: function()
				{
				    loading.remove();
				}
			});
        }
    };










	$.AviaTooltip  =  function(options)
	{
	   var defaults = {
            delay: 1500,                //delay in ms until the tooltip appears
            delayOut: 300,             	//delay in ms when instant showing should stop
            delayHide: 0,             	//delay hiding of tooltip in ms
            "class": "avia-tooltip",   	//tooltip classname for css styling and alignment
            scope: "body",             	//area the tooltip should be applied to
            data:  "avia-tooltip",     	//data attribute that contains the tooltip text
            attach:"body",          	//either attach the tooltip to the "mouse" or to the "element" // todo: implement mouse, make sure that it doesnt overlap with screen borders
            event: 'mouseenter',       	//mousenter and leave or click and leave
            position:'top',             //top or bottom
            extraClass:'avia-tooltip-class', //extra class that is defined by a tooltip element data attribute
            permanent: false, 			// always display the tooltip?
            within_screen: false		// if the tooltip is displayed outside the screen adjust its position
            
        };
		
        this.options = $.extend({}, defaults, options);
        this.body    = $('body');
        this.scope   = $(this.options.scope);
        this.tooltip = $('<div class="'+this.options['class']+' avia-tt"><span class="avia-arrow-wrap"><span class="avia-arrow"></span></span></div>');
        this.inner   = $('<div class="inner_tooltip"></div>').prependTo(this.tooltip);
        this.open    = false;
        this.timer   = false;
        this.active  = false;
		
        this.bind_events();
	};

	$.AviaTooltip.openTTs = [];
    $.AviaTooltip.prototype =
    {
        bind_events: function()
        {
	        var perma_tooltips		= '.av-permanent-tooltip [data-'+this.options.data+']',
	        	default_tooltips	= '[data-'+this.options.data+']:not( .av-permanent-tooltip [data-'+this.options.data+'])';
	        
	        this.scope.on('av_permanent_show', perma_tooltips, $.proxy( this.display_tooltip, this) );
	        $(perma_tooltips).addClass('av-perma-tooltip').trigger('av_permanent_show');
	        
	        
	        
			this.scope.on(this.options.event + ' mouseleave', default_tooltips, $.proxy( this.start_countdown, this) );
	        
            if(this.options.event != 'click')
            {
                this.scope.on('mouseleave', default_tooltips, $.proxy( this.hide_tooltip, this) );
            }
            else
            {
                this.body.on('mousedown', $.proxy( this.hide_tooltip, this) );
            }	
				       
        },

        start_countdown: function(e)
        {
            clearTimeout(this.timer);

            if(e.type == this.options.event)
            {
                var delay = this.options.event == 'click' ? 0 : this.open ? 0 : this.options.delay;

                this.timer = setTimeout($.proxy( this.display_tooltip, this, e), delay);
            }
            else if(e.type == 'mouseleave')
            {
                this.timer = setTimeout($.proxy( this.stop_instant_open, this, e), this.options.delayOut);
            }
            e.preventDefault();
        },

        reset_countdown: function(e)
        {
            clearTimeout(this.timer);
            this.timer = false;
        },

        display_tooltip: function(e)
        {
            var _self		= this,
            	target 		= this.options.event == "click" ? e.target : e.currentTarget,
            	element 	= $(target),
                text    	= element.data(this.options.data),
                newTip  	= element.data('avia-created-tooltip'),
            	extraClass 	= element.data('avia-tooltip-class'),
                attach  	= this.options.attach == 'element' ? element : this.body,
                offset  	= this.options.attach == 'element' ? element.position() : element.offset(),
                position	= element.data('avia-tooltip-position'),
                align		= element.data('avia-tooltip-alignment'),
                force_append= false;
           
            text = $.trim(text);
            
            if(element.is('.av-perma-tooltip'))
            {
	            offset = {top:0, left:0 };
	        	attach = element;
				force_append = true;
            }
            
			if(text == "") return;
			if(position == "" || typeof position == 'undefined') position = this.options.position;
			if(align == "" || typeof align == 'undefined') align = 'center';
			
			if(typeof newTip != 'undefined')
			{
				newTip = $.AviaTooltip.openTTs[newTip];
			}
			else
			{
				this.inner.html(text); 
				newTip = this.tooltip.clone();
				
				if(this.options.attach == 'element' && force_append !== true)
				{
					newTip.insertAfter(attach);
				}
				else
				{
					newTip.appendTo(attach);
				}
                
                if(extraClass != "") newTip.addClass(extraClass);
			}
			
            this.open = true;
            this.active = newTip;

            if((newTip.is(':animated:visible') && e.type == 'click') || element.is('.'+this.options['class']) || element.parents('.'+this.options['class']).length != 0) return;


            var animate1 = {}, animate2	= {}, pos1 = "", pos2 = "";
			
			if(position == "top" || position == "bottom")
			{
				switch(align)
				{
					case "left": pos2 = offset.left; break;
					case "right": pos2 = offset.left + element.outerWidth() - newTip.outerWidth();  break;
					default: pos2 = (offset.left + (element.outerWidth() / 2)) - (newTip.outerWidth() / 2); break;
				}	
				
				if(_self.options.within_screen) //used to keep search field inside screen
				{
					var boundary = element.offset().left + (element.outerWidth() / 2) - (newTip.outerWidth() / 2) + parseInt(newTip.css('margin-left'),10);
					if(boundary < 0)
					{
						pos2 = pos2 - boundary;
					}
					
				}
				
				
			}
			else
			{
				switch(align)
				{
					case "top": pos1 = offset.top; break;
					case "bottom": pos1 = offset.top + element.outerHeight() - newTip.outerHeight();  break;
					default: pos1 = (offset.top + (element.outerHeight() / 2)) - (newTip.outerHeight() / 2); break;
				}	
			}
	
			switch(position)
			{
				case "top": 
				pos1 = offset.top - newTip.outerHeight();
                animate1 = {top: pos1 - 10, left: pos2};
                animate2 = {top: pos1};
				break;
				case "bottom": 	
				pos1 = offset.top + element.outerHeight();
				animate1 = {top: pos1 + 10, left: pos2};
				animate2 = {top: pos1};
				break;
				case "left": 
				pos2 = offset.left  - newTip.outerWidth();
				animate1 = {top: pos1, left: pos2 -10};
            	animate2 = {left: pos2};	
				break;
				case "right": 	
				pos2 = offset.left + element.outerWidth();
				animate1 = {top: pos1, left: pos2 + 10};
            	animate2 = {left: pos2};	
				break;
			}
			
			animate1['display'] = "block";
			animate1['opacity'] = 0;
			animate2['opacity'] = 1;
			

            newTip.css(animate1).stop().animate(animate2,200);
            newTip.find('input, textarea').focus();
            $.AviaTooltip.openTTs.push(newTip);
            element.data('avia-created-tooltip', $.AviaTooltip.openTTs.length - 1);

        },

        hide_tooltip: function(e)
        {
            var element 	= $(e.currentTarget) , newTip, animateTo, 
            	position	= element.data('avia-tooltip-position'),
                align		= element.data('avia-tooltip-alignment');
                
            if(position == "" || typeof position == 'undefined') position = this.options.position;
			if(align == "" || typeof align == 'undefined') align = 'center';

            if(this.options.event == 'click')
            {
                element = $(e.target);

                if(!element.is('.'+this.options['class']) && element.parents('.'+this.options['class']).length == 0)
                {
                    if(this.active.length) { newTip = this.active; this.active = false;}
                }
            }
            else
            {
                newTip = element.data('avia-created-tooltip');
                newTip = typeof newTip != 'undefined' ? $.AviaTooltip.openTTs[newTip] : false;
            }

            if(newTip)
            {
            	var animate = {opacity:0};
            	
            	switch(position)
            	{
            		case "top": 	
						animate['top'] = parseInt(newTip.css('top'),10) - 10;	
					break;
					case "bottom": 	
						animate['top'] = parseInt(newTip.css('top'),10) + 10;	
					break;
					case "left": 	
						animate['left'] = parseInt(newTip.css('left'), 10) - 10;
					break;
					case "right": 	
						animate['left'] = parseInt(newTip.css('left'), 10) + 10;
					break;
            	}
            	
                newTip.animate(animate, 200, function()
                {
                    newTip.css({display:'none'});
                });
            }
        },

        stop_instant_open: function(e)
        {
            this.open = false;
        }
    };


})( jQuery );






/*!
 * Isotope PACKAGED v3.0.2
 *
 * Licensed GPLv3 for open source use
 * or Isotope Commercial License for commercial use
 *
 * http://isotope.metafizzy.co
 * Copyright 2016 Metafizzy
 */

!function(t,e){"function"==typeof define&&define.amd?define("jquery-bridget/jquery-bridget",["jquery"],function(i){return e(t,i)}):"object"==typeof module&&module.exports?module.exports=e(t,require("jquery")):t.jQueryBridget=e(t,t.jQuery)}(window,function(t,e){"use strict";function i(i,s,a){function u(t,e,n){var o,s="$()."+i+'("'+e+'")';return t.each(function(t,u){var h=a.data(u,i);if(!h)return void r(i+" not initialized. Cannot call methods, i.e. "+s);var d=h[e];if(!d||"_"==e.charAt(0))return void r(s+" is not a valid method");var l=d.apply(h,n);o=void 0===o?l:o}),void 0!==o?o:t}function h(t,e){t.each(function(t,n){var o=a.data(n,i);o?(o.option(e),o._init()):(o=new s(n,e),a.data(n,i,o))})}a=a||e||t.jQuery,a&&(s.prototype.option||(s.prototype.option=function(t){a.isPlainObject(t)&&(this.options=a.extend(!0,this.options,t))}),a.fn[i]=function(t){if("string"==typeof t){var e=o.call(arguments,1);return u(this,t,e)}return h(this,t),this},n(a))}function n(t){!t||t&&t.bridget||(t.bridget=i)}var o=Array.prototype.slice,s=t.console,r="undefined"==typeof s?function(){}:function(t){s.error(t)};return n(e||t.jQuery),i}),function(t,e){"function"==typeof define&&define.amd?define("ev-emitter/ev-emitter",e):"object"==typeof module&&module.exports?module.exports=e():t.EvEmitter=e()}("undefined"!=typeof window?window:this,function(){function t(){}var e=t.prototype;return e.on=function(t,e){if(t&&e){var i=this._events=this._events||{},n=i[t]=i[t]||[];return n.indexOf(e)==-1&&n.push(e),this}},e.once=function(t,e){if(t&&e){this.on(t,e);var i=this._onceEvents=this._onceEvents||{},n=i[t]=i[t]||{};return n[e]=!0,this}},e.off=function(t,e){var i=this._events&&this._events[t];if(i&&i.length){var n=i.indexOf(e);return n!=-1&&i.splice(n,1),this}},e.emitEvent=function(t,e){var i=this._events&&this._events[t];if(i&&i.length){var n=0,o=i[n];e=e||[];for(var s=this._onceEvents&&this._onceEvents[t];o;){var r=s&&s[o];r&&(this.off(t,o),delete s[o]),o.apply(this,e),n+=r?0:1,o=i[n]}return this}},t}),function(t,e){"use strict";"function"==typeof define&&define.amd?define("get-size/get-size",[],function(){return e()}):"object"==typeof module&&module.exports?module.exports=e():t.getSize=e()}(window,function(){"use strict";function t(t){var e=parseFloat(t),i=t.indexOf("%")==-1&&!isNaN(e);return i&&e}function e(){}function i(){for(var t={width:0,height:0,innerWidth:0,innerHeight:0,outerWidth:0,outerHeight:0},e=0;e<h;e++){var i=u[e];t[i]=0}return t}function n(t){var e=getComputedStyle(t);return e||a("Style returned "+e+". Are you running this code in a hidden iframe on Firefox? See http://bit.ly/getsizebug1"),e}function o(){if(!d){d=!0;var e=document.createElement("div");e.style.width="200px",e.style.padding="1px 2px 3px 4px",e.style.borderStyle="solid",e.style.borderWidth="1px 2px 3px 4px",e.style.boxSizing="border-box";var i=document.body||document.documentElement;i.appendChild(e);var o=n(e);s.isBoxSizeOuter=r=200==t(o.width),i.removeChild(e)}}function s(e){if(o(),"string"==typeof e&&(e=document.querySelector(e)),e&&"object"==typeof e&&e.nodeType){var s=n(e);if("none"==s.display)return i();var a={};a.width=e.offsetWidth,a.height=e.offsetHeight;for(var d=a.isBorderBox="border-box"==s.boxSizing,l=0;l<h;l++){var f=u[l],c=s[f],m=parseFloat(c);a[f]=isNaN(m)?0:m}var p=a.paddingLeft+a.paddingRight,y=a.paddingTop+a.paddingBottom,g=a.marginLeft+a.marginRight,v=a.marginTop+a.marginBottom,_=a.borderLeftWidth+a.borderRightWidth,I=a.borderTopWidth+a.borderBottomWidth,z=d&&r,x=t(s.width);x!==!1&&(a.width=x+(z?0:p+_));var S=t(s.height);return S!==!1&&(a.height=S+(z?0:y+I)),a.innerWidth=a.width-(p+_),a.innerHeight=a.height-(y+I),a.outerWidth=a.width+g,a.outerHeight=a.height+v,a}}var r,a="undefined"==typeof console?e:function(t){console.error(t)},u=["paddingLeft","paddingRight","paddingTop","paddingBottom","marginLeft","marginRight","marginTop","marginBottom","borderLeftWidth","borderRightWidth","borderTopWidth","borderBottomWidth"],h=u.length,d=!1;return s}),function(t,e){"use strict";"function"==typeof define&&define.amd?define("desandro-matches-selector/matches-selector",e):"object"==typeof module&&module.exports?module.exports=e():t.matchesSelector=e()}(window,function(){"use strict";var t=function(){var t=Element.prototype;if(t.matches)return"matches";if(t.matchesSelector)return"matchesSelector";for(var e=["webkit","moz","ms","o"],i=0;i<e.length;i++){var n=e[i],o=n+"MatchesSelector";if(t[o])return o}}();return function(e,i){return e[t](i)}}),function(t,e){"function"==typeof define&&define.amd?define("fizzy-ui-utils/utils",["desandro-matches-selector/matches-selector"],function(i){return e(t,i)}):"object"==typeof module&&module.exports?module.exports=e(t,require("desandro-matches-selector")):t.fizzyUIUtils=e(t,t.matchesSelector)}(window,function(t,e){var i={};i.extend=function(t,e){for(var i in e)t[i]=e[i];return t},i.modulo=function(t,e){return(t%e+e)%e},i.makeArray=function(t){var e=[];if(Array.isArray(t))e=t;else if(t&&"number"==typeof t.length)for(var i=0;i<t.length;i++)e.push(t[i]);else e.push(t);return e},i.removeFrom=function(t,e){var i=t.indexOf(e);i!=-1&&t.splice(i,1)},i.getParent=function(t,i){for(;t!=document.body;)if(t=t.parentNode,e(t,i))return t},i.getQueryElement=function(t){return"string"==typeof t?document.querySelector(t):t},i.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},i.filterFindElements=function(t,n){t=i.makeArray(t);var o=[];return t.forEach(function(t){if(t instanceof HTMLElement){if(!n)return void o.push(t);e(t,n)&&o.push(t);for(var i=t.querySelectorAll(n),s=0;s<i.length;s++)o.push(i[s])}}),o},i.debounceMethod=function(t,e,i){var n=t.prototype[e],o=e+"Timeout";t.prototype[e]=function(){var t=this[o];t&&clearTimeout(t);var e=arguments,s=this;this[o]=setTimeout(function(){n.apply(s,e),delete s[o]},i||100)}},i.docReady=function(t){var e=document.readyState;"complete"==e||"interactive"==e?setTimeout(t):document.addEventListener("DOMContentLoaded",t)},i.toDashed=function(t){return t.replace(/(.)([A-Z])/g,function(t,e,i){return e+"-"+i}).toLowerCase()};var n=t.console;return i.htmlInit=function(e,o){i.docReady(function(){var s=i.toDashed(o),r="data-"+s,a=document.querySelectorAll("["+r+"]"),u=document.querySelectorAll(".js-"+s),h=i.makeArray(a).concat(i.makeArray(u)),d=r+"-options",l=t.jQuery;h.forEach(function(t){var i,s=t.getAttribute(r)||t.getAttribute(d);try{i=s&&JSON.parse(s)}catch(a){return void(n&&n.error("Error parsing "+r+" on "+t.className+": "+a))}var u=new e(t,i);l&&l.data(t,o,u)})})},i}),function(t,e){"function"==typeof define&&define.amd?define("outlayer/item",["ev-emitter/ev-emitter","get-size/get-size"],e):"object"==typeof module&&module.exports?module.exports=e(require("ev-emitter"),require("get-size")):(t.Outlayer={},t.Outlayer.Item=e(t.EvEmitter,t.getSize))}(window,function(t,e){"use strict";function i(t){for(var e in t)return!1;return e=null,!0}function n(t,e){t&&(this.element=t,this.layout=e,this.position={x:0,y:0},this._create())}function o(t){return t.replace(/([A-Z])/g,function(t){return"-"+t.toLowerCase()})}var s=document.documentElement.style,r="string"==typeof s.transition?"transition":"WebkitTransition",a="string"==typeof s.transform?"transform":"WebkitTransform",u={WebkitTransition:"webkitTransitionEnd",transition:"transitionend"}[r],h={transform:a,transition:r,transitionDuration:r+"Duration",transitionProperty:r+"Property",transitionDelay:r+"Delay"},d=n.prototype=Object.create(t.prototype);d.constructor=n,d._create=function(){this._transn={ingProperties:{},clean:{},onEnd:{}},this.css({position:"absolute"})},d.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},d.getSize=function(){this.size=e(this.element)},d.css=function(t){var e=this.element.style;for(var i in t){var n=h[i]||i;e[n]=t[i]}},d.getPosition=function(){var t=getComputedStyle(this.element),e=this.layout._getOption("originLeft"),i=this.layout._getOption("originTop"),n=t[e?"left":"right"],o=t[i?"top":"bottom"],s=this.layout.size,r=n.indexOf("%")!=-1?parseFloat(n)/100*s.width:parseInt(n,10),a=o.indexOf("%")!=-1?parseFloat(o)/100*s.height:parseInt(o,10);r=isNaN(r)?0:r,a=isNaN(a)?0:a,r-=e?s.paddingLeft:s.paddingRight,a-=i?s.paddingTop:s.paddingBottom,this.position.x=r,this.position.y=a},d.layoutPosition=function(){var t=this.layout.size,e={},i=this.layout._getOption("originLeft"),n=this.layout._getOption("originTop"),o=i?"paddingLeft":"paddingRight",s=i?"left":"right",r=i?"right":"left",a=this.position.x+t[o];e[s]=this.getXValue(a),e[r]="";var u=n?"paddingTop":"paddingBottom",h=n?"top":"bottom",d=n?"bottom":"top",l=this.position.y+t[u];e[h]=this.getYValue(l),e[d]="",this.css(e),this.emitEvent("layout",[this])},d.getXValue=function(t){var e=this.layout._getOption("horizontal");return this.layout.options.percentPosition&&!e?t/this.layout.size.width*100+"%":t+"px"},d.getYValue=function(t){var e=this.layout._getOption("horizontal");return this.layout.options.percentPosition&&e?t/this.layout.size.height*100+"%":t+"px"},d._transitionTo=function(t,e){this.getPosition();var i=this.position.x,n=this.position.y,o=parseInt(t,10),s=parseInt(e,10),r=o===this.position.x&&s===this.position.y;if(this.setPosition(t,e),r&&!this.isTransitioning)return void this.layoutPosition();var a=t-i,u=e-n,h={};h.transform=this.getTranslate(a,u),this.transition({to:h,onTransitionEnd:{transform:this.layoutPosition},isCleaning:!0})},d.getTranslate=function(t,e){var i=this.layout._getOption("originLeft"),n=this.layout._getOption("originTop");return t=i?t:-t,e=n?e:-e,"translate3d("+t+"px, "+e+"px, 0)"},d.goTo=function(t,e){this.setPosition(t,e),this.layoutPosition()},d.moveTo=d._transitionTo,d.setPosition=function(t,e){this.position.x=parseInt(t,10),this.position.y=parseInt(e,10)},d._nonTransition=function(t){this.css(t.to),t.isCleaning&&this._removeStyles(t.to);for(var e in t.onTransitionEnd)t.onTransitionEnd[e].call(this)},d.transition=function(t){if(!parseFloat(this.layout.options.transitionDuration))return void this._nonTransition(t);var e=this._transn;for(var i in t.onTransitionEnd)e.onEnd[i]=t.onTransitionEnd[i];for(i in t.to)e.ingProperties[i]=!0,t.isCleaning&&(e.clean[i]=!0);if(t.from){this.css(t.from);var n=this.element.offsetHeight;n=null}this.enableTransition(t.to),this.css(t.to),this.isTransitioning=!0};var l="opacity,"+o(a);d.enableTransition=function(){if(!this.isTransitioning){var t=this.layout.options.transitionDuration;t="number"==typeof t?t+"ms":t,this.css({transitionProperty:l,transitionDuration:t,transitionDelay:this.staggerDelay||0}),this.element.addEventListener(u,this,!1)}},d.onwebkitTransitionEnd=function(t){this.ontransitionend(t)},d.onotransitionend=function(t){this.ontransitionend(t)};var f={"-webkit-transform":"transform"};d.ontransitionend=function(t){if(t.target===this.element){var e=this._transn,n=f[t.propertyName]||t.propertyName;if(delete e.ingProperties[n],i(e.ingProperties)&&this.disableTransition(),n in e.clean&&(this.element.style[t.propertyName]="",delete e.clean[n]),n in e.onEnd){var o=e.onEnd[n];o.call(this),delete e.onEnd[n]}this.emitEvent("transitionEnd",[this])}},d.disableTransition=function(){this.removeTransitionStyles(),this.element.removeEventListener(u,this,!1),this.isTransitioning=!1},d._removeStyles=function(t){var e={};for(var i in t)e[i]="";this.css(e)};var c={transitionProperty:"",transitionDuration:"",transitionDelay:""};return d.removeTransitionStyles=function(){this.css(c)},d.stagger=function(t){t=isNaN(t)?0:t,this.staggerDelay=t+"ms"},d.removeElem=function(){this.element.parentNode.removeChild(this.element),this.css({display:""}),this.emitEvent("remove",[this])},d.remove=function(){return r&&parseFloat(this.layout.options.transitionDuration)?(this.once("transitionEnd",function(){this.removeElem()}),void this.hide()):void this.removeElem()},d.reveal=function(){delete this.isHidden,this.css({display:""});var t=this.layout.options,e={},i=this.getHideRevealTransitionEndProperty("visibleStyle");e[i]=this.onRevealTransitionEnd,this.transition({from:t.hiddenStyle,to:t.visibleStyle,isCleaning:!0,onTransitionEnd:e})},d.onRevealTransitionEnd=function(){this.isHidden||this.emitEvent("reveal")},d.getHideRevealTransitionEndProperty=function(t){var e=this.layout.options[t];if(e.opacity)return"opacity";for(var i in e)return i},d.hide=function(){this.isHidden=!0,this.css({display:""});var t=this.layout.options,e={},i=this.getHideRevealTransitionEndProperty("hiddenStyle");e[i]=this.onHideTransitionEnd,this.transition({from:t.visibleStyle,to:t.hiddenStyle,isCleaning:!0,onTransitionEnd:e})},d.onHideTransitionEnd=function(){this.isHidden&&(this.css({display:"none"}),this.emitEvent("hide"))},d.destroy=function(){this.css({position:"",left:"",right:"",top:"",bottom:"",transition:"",transform:""})},n}),function(t,e){"use strict";"function"==typeof define&&define.amd?define("outlayer/outlayer",["ev-emitter/ev-emitter","get-size/get-size","fizzy-ui-utils/utils","./item"],function(i,n,o,s){return e(t,i,n,o,s)}):"object"==typeof module&&module.exports?module.exports=e(t,require("ev-emitter"),require("get-size"),require("fizzy-ui-utils"),require("./item")):t.Outlayer=e(t,t.EvEmitter,t.getSize,t.fizzyUIUtils,t.Outlayer.Item)}(window,function(t,e,i,n,o){"use strict";function s(t,e){var i=n.getQueryElement(t);if(!i)return void(u&&u.error("Bad element for "+this.constructor.namespace+": "+(i||t)));this.element=i,h&&(this.$element=h(this.element)),this.options=n.extend({},this.constructor.defaults),this.option(e);var o=++l;this.element.outlayerGUID=o,f[o]=this,this._create();var s=this._getOption("initLayout");s&&this.layout()}function r(t){function e(){t.apply(this,arguments)}return e.prototype=Object.create(t.prototype),e.prototype.constructor=e,e}function a(t){if("number"==typeof t)return t;var e=t.match(/(^\d*\.?\d*)(\w*)/),i=e&&e[1],n=e&&e[2];if(!i.length)return 0;i=parseFloat(i);var o=m[n]||1;return i*o}var u=t.console,h=t.jQuery,d=function(){},l=0,f={};s.namespace="outlayer",s.Item=o,s.defaults={containerStyle:{position:"relative"},initLayout:!0,originLeft:!0,originTop:!0,resize:!0,resizeContainer:!0,transitionDuration:"0.4s",hiddenStyle:{opacity:0,transform:"scale(0.001)"},visibleStyle:{opacity:1,transform:"scale(1)"}};var c=s.prototype;n.extend(c,e.prototype),c.option=function(t){n.extend(this.options,t)},c._getOption=function(t){var e=this.constructor.compatOptions[t];return e&&void 0!==this.options[e]?this.options[e]:this.options[t]},s.compatOptions={initLayout:"isInitLayout",horizontal:"isHorizontal",layoutInstant:"isLayoutInstant",originLeft:"isOriginLeft",originTop:"isOriginTop",resize:"isResizeBound",resizeContainer:"isResizingContainer"},c._create=function(){this.reloadItems(),this.stamps=[],this.stamp(this.options.stamp),n.extend(this.element.style,this.options.containerStyle);var t=this._getOption("resize");t&&this.bindResize()},c.reloadItems=function(){this.items=this._itemize(this.element.children)},c._itemize=function(t){for(var e=this._filterFindItemElements(t),i=this.constructor.Item,n=[],o=0;o<e.length;o++){var s=e[o],r=new i(s,this);n.push(r)}return n},c._filterFindItemElements=function(t){return n.filterFindElements(t,this.options.itemSelector)},c.getItemElements=function(){return this.items.map(function(t){return t.element})},c.layout=function(){this._resetLayout(),this._manageStamps();var t=this._getOption("layoutInstant"),e=void 0!==t?t:!this._isLayoutInited;this.layoutItems(this.items,e),this._isLayoutInited=!0},c._init=c.layout,c._resetLayout=function(){this.getSize()},c.getSize=function(){this.size=i(this.element)},c._getMeasurement=function(t,e){var n,o=this.options[t];o?("string"==typeof o?n=this.element.querySelector(o):o instanceof HTMLElement&&(n=o),this[t]=n?i(n)[e]:o):this[t]=0},c.layoutItems=function(t,e){t=this._getItemsForLayout(t),this._layoutItems(t,e),this._postLayout()},c._getItemsForLayout=function(t){return t.filter(function(t){return!t.isIgnored})},c._layoutItems=function(t,e){if(this._emitCompleteOnItems("layout",t),t&&t.length){var i=[];t.forEach(function(t){var n=this._getItemLayoutPosition(t);n.item=t,n.isInstant=e||t.isLayoutInstant,i.push(n)},this),this._processLayoutQueue(i)}},c._getItemLayoutPosition=function(){return{x:0,y:0}},c._processLayoutQueue=function(t){this.updateStagger(),t.forEach(function(t,e){this._positionItem(t.item,t.x,t.y,t.isInstant,e)},this)},c.updateStagger=function(){var t=this.options.stagger;return null===t||void 0===t?void(this.stagger=0):(this.stagger=a(t),this.stagger)},c._positionItem=function(t,e,i,n,o){n?t.goTo(e,i):(t.stagger(o*this.stagger),t.moveTo(e,i))},c._postLayout=function(){this.resizeContainer()},c.resizeContainer=function(){var t=this._getOption("resizeContainer");if(t){var e=this._getContainerSize();e&&(this._setContainerMeasure(e.width,!0),this._setContainerMeasure(e.height,!1))}},c._getContainerSize=d,c._setContainerMeasure=function(t,e){if(void 0!==t){var i=this.size;i.isBorderBox&&(t+=e?i.paddingLeft+i.paddingRight+i.borderLeftWidth+i.borderRightWidth:i.paddingBottom+i.paddingTop+i.borderTopWidth+i.borderBottomWidth),t=Math.max(t,0),this.element.style[e?"width":"height"]=t+"px"}},c._emitCompleteOnItems=function(t,e){function i(){o.dispatchEvent(t+"Complete",null,[e])}function n(){r++,r==s&&i()}var o=this,s=e.length;if(!e||!s)return void i();var r=0;e.forEach(function(e){e.once(t,n)})},c.dispatchEvent=function(t,e,i){var n=e?[e].concat(i):i;if(this.emitEvent(t,n),h)if(this.$element=this.$element||h(this.element),e){var o=h.Event(e);o.type=t,this.$element.trigger(o,i)}else this.$element.trigger(t,i)},c.ignore=function(t){var e=this.getItem(t);e&&(e.isIgnored=!0)},c.unignore=function(t){var e=this.getItem(t);e&&delete e.isIgnored},c.stamp=function(t){t=this._find(t),t&&(this.stamps=this.stamps.concat(t),t.forEach(this.ignore,this))},c.unstamp=function(t){t=this._find(t),t&&t.forEach(function(t){n.removeFrom(this.stamps,t),this.unignore(t)},this)},c._find=function(t){if(t)return"string"==typeof t&&(t=this.element.querySelectorAll(t)),t=n.makeArray(t)},c._manageStamps=function(){this.stamps&&this.stamps.length&&(this._getBoundingRect(),this.stamps.forEach(this._manageStamp,this))},c._getBoundingRect=function(){var t=this.element.getBoundingClientRect(),e=this.size;this._boundingRect={left:t.left+e.paddingLeft+e.borderLeftWidth,top:t.top+e.paddingTop+e.borderTopWidth,right:t.right-(e.paddingRight+e.borderRightWidth),bottom:t.bottom-(e.paddingBottom+e.borderBottomWidth)}},c._manageStamp=d,c._getElementOffset=function(t){var e=t.getBoundingClientRect(),n=this._boundingRect,o=i(t),s={left:e.left-n.left-o.marginLeft,top:e.top-n.top-o.marginTop,right:n.right-e.right-o.marginRight,bottom:n.bottom-e.bottom-o.marginBottom};return s},c.handleEvent=n.handleEvent,c.bindResize=function(){t.addEventListener("resize",this),this.isResizeBound=!0},c.unbindResize=function(){t.removeEventListener("resize",this),this.isResizeBound=!1},c.onresize=function(){this.resize()},n.debounceMethod(s,"onresize",100),c.resize=function(){this.isResizeBound&&this.needsResizeLayout()&&this.layout()},c.needsResizeLayout=function(){var t=i(this.element),e=this.size&&t;return e&&t.innerWidth!==this.size.innerWidth},c.addItems=function(t){var e=this._itemize(t);return e.length&&(this.items=this.items.concat(e)),e},c.appended=function(t){var e=this.addItems(t);e.length&&(this.layoutItems(e,!0),this.reveal(e))},c.prepended=function(t){var e=this._itemize(t);if(e.length){var i=this.items.slice(0);this.items=e.concat(i),this._resetLayout(),this._manageStamps(),this.layoutItems(e,!0),this.reveal(e),this.layoutItems(i)}},c.reveal=function(t){if(this._emitCompleteOnItems("reveal",t),t&&t.length){var e=this.updateStagger();t.forEach(function(t,i){t.stagger(i*e),t.reveal()})}},c.hide=function(t){if(this._emitCompleteOnItems("hide",t),t&&t.length){var e=this.updateStagger();t.forEach(function(t,i){t.stagger(i*e),t.hide()})}},c.revealItemElements=function(t){var e=this.getItems(t);this.reveal(e)},c.hideItemElements=function(t){var e=this.getItems(t);this.hide(e)},c.getItem=function(t){for(var e=0;e<this.items.length;e++){var i=this.items[e];if(i.element==t)return i}},c.getItems=function(t){t=n.makeArray(t);var e=[];return t.forEach(function(t){var i=this.getItem(t);i&&e.push(i)},this),e},c.remove=function(t){var e=this.getItems(t);this._emitCompleteOnItems("remove",e),e&&e.length&&e.forEach(function(t){t.remove(),n.removeFrom(this.items,t)},this)},c.destroy=function(){var t=this.element.style;t.height="",t.position="",t.width="",this.items.forEach(function(t){t.destroy()}),this.unbindResize();var e=this.element.outlayerGUID;delete f[e],delete this.element.outlayerGUID,h&&h.removeData(this.element,this.constructor.namespace)},s.data=function(t){t=n.getQueryElement(t);var e=t&&t.outlayerGUID;return e&&f[e]},s.create=function(t,e){var i=r(s);return i.defaults=n.extend({},s.defaults),n.extend(i.defaults,e),i.compatOptions=n.extend({},s.compatOptions),i.namespace=t,i.data=s.data,i.Item=r(o),n.htmlInit(i,t),h&&h.bridget&&h.bridget(t,i),i};var m={ms:1,s:1e3};return s.Item=o,s}),function(t,e){"function"==typeof define&&define.amd?define("isotope/js/item",["outlayer/outlayer"],e):"object"==typeof module&&module.exports?module.exports=e(require("outlayer")):(t.Isotope=t.Isotope||{},t.Isotope.Item=e(t.Outlayer))}(window,function(t){"use strict";function e(){t.Item.apply(this,arguments)}var i=e.prototype=Object.create(t.Item.prototype),n=i._create;i._create=function(){this.id=this.layout.itemGUID++,n.call(this),this.sortData={}},i.updateSortData=function(){if(!this.isIgnored){this.sortData.id=this.id,this.sortData["original-order"]=this.id,this.sortData.random=Math.random();var t=this.layout.options.getSortData,e=this.layout._sorters;for(var i in t){var n=e[i];this.sortData[i]=n(this.element,this)}}};var o=i.destroy;return i.destroy=function(){o.apply(this,arguments),this.css({display:""})},e}),function(t,e){"function"==typeof define&&define.amd?define("isotope/js/layout-mode",["get-size/get-size","outlayer/outlayer"],e):"object"==typeof module&&module.exports?module.exports=e(require("get-size"),require("outlayer")):(t.Isotope=t.Isotope||{},t.Isotope.LayoutMode=e(t.getSize,t.Outlayer))}(window,function(t,e){"use strict";function i(t){this.isotope=t,t&&(this.options=t.options[this.namespace],this.element=t.element,this.items=t.filteredItems,this.size=t.size)}var n=i.prototype,o=["_resetLayout","_getItemLayoutPosition","_manageStamp","_getContainerSize","_getElementOffset","needsResizeLayout","_getOption"];return o.forEach(function(t){n[t]=function(){return e.prototype[t].apply(this.isotope,arguments)}}),n.needsVerticalResizeLayout=function(){var e=t(this.isotope.element),i=this.isotope.size&&e;return i&&e.innerHeight!=this.isotope.size.innerHeight},n._getMeasurement=function(){this.isotope._getMeasurement.apply(this,arguments)},n.getColumnWidth=function(){this.getSegmentSize("column","Width")},n.getRowHeight=function(){this.getSegmentSize("row","Height")},n.getSegmentSize=function(t,e){var i=t+e,n="outer"+e;if(this._getMeasurement(i,n),!this[i]){var o=this.getFirstItemSize();this[i]=o&&o[n]||this.isotope.size["inner"+e]}},n.getFirstItemSize=function(){var e=this.isotope.filteredItems[0];return e&&e.element&&t(e.element)},n.layout=function(){this.isotope.layout.apply(this.isotope,arguments)},n.getSize=function(){this.isotope.getSize(),this.size=this.isotope.size},i.modes={},i.create=function(t,e){function o(){i.apply(this,arguments)}return o.prototype=Object.create(n),o.prototype.constructor=o,e&&(o.options=e),o.prototype.namespace=t,i.modes[t]=o,o},i}),function(t,e){"function"==typeof define&&define.amd?define("masonry/masonry",["outlayer/outlayer","get-size/get-size"],e):"object"==typeof module&&module.exports?module.exports=e(require("outlayer"),require("get-size")):t.Masonry=e(t.Outlayer,t.getSize)}(window,function(t,e){var i=t.create("masonry");return i.compatOptions.fitWidth="isFitWidth",i.prototype._resetLayout=function(){this.getSize(),this._getMeasurement("columnWidth","outerWidth"),this._getMeasurement("gutter","outerWidth"),this.measureColumns(),this.colYs=[];for(var t=0;t<this.cols;t++)this.colYs.push(0);this.maxY=0},i.prototype.measureColumns=function(){if(this.getContainerWidth(),!this.columnWidth){var t=this.items[0],i=t&&t.element;this.columnWidth=i&&e(i).outerWidth||this.containerWidth}var n=this.columnWidth+=this.gutter,o=this.containerWidth+this.gutter,s=o/n,r=n-o%n,a=r&&r<1?"round":"floor";s=Math[a](s),this.cols=Math.max(s,1)},i.prototype.getContainerWidth=function(){var t=this._getOption("fitWidth"),i=t?this.element.parentNode:this.element,n=e(i);this.containerWidth=n&&n.innerWidth},i.prototype._getItemLayoutPosition=function(t){t.getSize();var e=t.size.outerWidth%this.columnWidth,i=e&&e<1?"round":"ceil",n=Math[i](t.size.outerWidth/this.columnWidth);n=Math.min(n,this.cols);for(var o=this._getColGroup(n),s=Math.min.apply(Math,o),r=o.indexOf(s),a={x:this.columnWidth*r,y:s},u=s+t.size.outerHeight,h=this.cols+1-o.length,d=0;d<h;d++)this.colYs[r+d]=u;return a},i.prototype._getColGroup=function(t){if(t<2)return this.colYs;for(var e=[],i=this.cols+1-t,n=0;n<i;n++){var o=this.colYs.slice(n,n+t);e[n]=Math.max.apply(Math,o)}return e},i.prototype._manageStamp=function(t){var i=e(t),n=this._getElementOffset(t),o=this._getOption("originLeft"),s=o?n.left:n.right,r=s+i.outerWidth,a=Math.floor(s/this.columnWidth);a=Math.max(0,a);var u=Math.floor(r/this.columnWidth);u-=r%this.columnWidth?0:1,u=Math.min(this.cols-1,u);for(var h=this._getOption("originTop"),d=(h?n.top:n.bottom)+i.outerHeight,l=a;l<=u;l++)this.colYs[l]=Math.max(d,this.colYs[l])},i.prototype._getContainerSize=function(){this.maxY=Math.max.apply(Math,this.colYs);var t={height:this.maxY};return this._getOption("fitWidth")&&(t.width=this._getContainerFitWidth()),t},i.prototype._getContainerFitWidth=function(){for(var t=0,e=this.cols;--e&&0===this.colYs[e];)t++;return(this.cols-t)*this.columnWidth-this.gutter},i.prototype.needsResizeLayout=function(){var t=this.containerWidth;return this.getContainerWidth(),t!=this.containerWidth},i}),function(t,e){"function"==typeof define&&define.amd?define("isotope/js/layout-modes/masonry",["../layout-mode","masonry/masonry"],e):"object"==typeof module&&module.exports?module.exports=e(require("../layout-mode"),require("masonry-layout")):e(t.Isotope.LayoutMode,t.Masonry)}(window,function(t,e){"use strict";var i=t.create("masonry"),n=i.prototype,o={_getElementOffset:!0,layout:!0,_getMeasurement:!0};for(var s in e.prototype)o[s]||(n[s]=e.prototype[s]);var r=n.measureColumns;n.measureColumns=function(){this.items=this.isotope.filteredItems,r.call(this)};var a=n._getOption;return n._getOption=function(t){return"fitWidth"==t?void 0!==this.options.isFitWidth?this.options.isFitWidth:this.options.fitWidth:a.apply(this.isotope,arguments)},i}),function(t,e){"function"==typeof define&&define.amd?define("isotope/js/layout-modes/fit-rows",["../layout-mode"],e):"object"==typeof exports?module.exports=e(require("../layout-mode")):e(t.Isotope.LayoutMode)}(window,function(t){"use strict";var e=t.create("fitRows"),i=e.prototype;return i._resetLayout=function(){this.x=0,this.y=0,this.maxY=0,this._getMeasurement("gutter","outerWidth")},i._getItemLayoutPosition=function(t){t.getSize();var e=t.size.outerWidth+this.gutter,i=this.isotope.size.innerWidth+this.gutter;0!==this.x&&e+this.x>i&&(this.x=0,this.y=this.maxY);var n={x:this.x,y:this.y};return this.maxY=Math.max(this.maxY,this.y+t.size.outerHeight),this.x+=e,n},i._getContainerSize=function(){return{height:this.maxY}},e}),function(t,e){"function"==typeof define&&define.amd?define("isotope/js/layout-modes/vertical",["../layout-mode"],e):"object"==typeof module&&module.exports?module.exports=e(require("../layout-mode")):e(t.Isotope.LayoutMode)}(window,function(t){"use strict";var e=t.create("vertical",{horizontalAlignment:0}),i=e.prototype;return i._resetLayout=function(){this.y=0},i._getItemLayoutPosition=function(t){t.getSize();var e=(this.isotope.size.innerWidth-t.size.outerWidth)*this.options.horizontalAlignment,i=this.y;return this.y+=t.size.outerHeight,{x:e,y:i}},i._getContainerSize=function(){return{height:this.y}},e}),function(t,e){"function"==typeof define&&define.amd?define(["outlayer/outlayer","get-size/get-size","desandro-matches-selector/matches-selector","fizzy-ui-utils/utils","isotope/js/item","isotope/js/layout-mode","isotope/js/layout-modes/masonry","isotope/js/layout-modes/fit-rows","isotope/js/layout-modes/vertical"],function(i,n,o,s,r,a){return e(t,i,n,o,s,r,a)}):"object"==typeof module&&module.exports?module.exports=e(t,require("outlayer"),require("get-size"),require("desandro-matches-selector"),require("fizzy-ui-utils"),require("isotope/js/item"),require("isotope/js/layout-mode"),require("isotope/js/layout-modes/masonry"),require("isotope/js/layout-modes/fit-rows"),require("isotope/js/layout-modes/vertical")):t.Isotope=e(t,t.Outlayer,t.getSize,t.matchesSelector,t.fizzyUIUtils,t.Isotope.Item,t.Isotope.LayoutMode)}(window,function(t,e,i,n,o,s,r){function a(t,e){return function(i,n){for(var o=0;o<t.length;o++){var s=t[o],r=i.sortData[s],a=n.sortData[s];if(r>a||r<a){var u=void 0!==e[s]?e[s]:e,h=u?1:-1;return(r>a?1:-1)*h}}return 0}}var u=t.jQuery,h=String.prototype.trim?function(t){return t.trim()}:function(t){return t.replace(/^\s+|\s+$/g,"")},d=e.create("isotope",{layoutMode:"masonry",isJQueryFiltering:!0,sortAscending:!0});d.Item=s,d.LayoutMode=r;var l=d.prototype;l._create=function(){this.itemGUID=0,this._sorters={},this._getSorters(),e.prototype._create.call(this),this.modes={},this.filteredItems=this.items,this.sortHistory=["original-order"];for(var t in r.modes)this._initLayoutMode(t)},l.reloadItems=function(){this.itemGUID=0,e.prototype.reloadItems.call(this)},l._itemize=function(){for(var t=e.prototype._itemize.apply(this,arguments),i=0;i<t.length;i++){var n=t[i];n.id=this.itemGUID++}return this._updateItemsSortData(t),t},l._initLayoutMode=function(t){var e=r.modes[t],i=this.options[t]||{};this.options[t]=e.options?o.extend(e.options,i):i,this.modes[t]=new e(this)},l.layout=function(){return!this._isLayoutInited&&this._getOption("initLayout")?void this.arrange():void this._layout()},l._layout=function(){var t=this._getIsInstant();this._resetLayout(),this._manageStamps(),this.layoutItems(this.filteredItems,t),this._isLayoutInited=!0},l.arrange=function(t){this.option(t),this._getIsInstant();var e=this._filter(this.items);this.filteredItems=e.matches,this._bindArrangeComplete(),this._isInstant?this._noTransition(this._hideReveal,[e]):this._hideReveal(e),this._sort(),this._layout()},l._init=l.arrange,l._hideReveal=function(t){this.reveal(t.needReveal),this.hide(t.needHide)},l._getIsInstant=function(){var t=this._getOption("layoutInstant"),e=void 0!==t?t:!this._isLayoutInited;return this._isInstant=e,e},l._bindArrangeComplete=function(){function t(){e&&i&&n&&o.dispatchEvent("arrangeComplete",null,[o.filteredItems])}var e,i,n,o=this;this.once("layoutComplete",function(){e=!0,t()}),this.once("hideComplete",function(){i=!0,t()}),this.once("revealComplete",function(){n=!0,t()})},l._filter=function(t){var e=this.options.filter;e=e||"*";for(var i=[],n=[],o=[],s=this._getFilterTest(e),r=0;r<t.length;r++){var a=t[r];if(!a.isIgnored){var u=s(a);u&&i.push(a),u&&a.isHidden?n.push(a):u||a.isHidden||o.push(a)}}return{matches:i,needReveal:n,needHide:o}},l._getFilterTest=function(t){return u&&this.options.isJQueryFiltering?function(e){return u(e.element).is(t)}:"function"==typeof t?function(e){return t(e.element)}:function(e){return n(e.element,t)}},l.updateSortData=function(t){var e;t?(t=o.makeArray(t),e=this.getItems(t)):e=this.items,this._getSorters(),this._updateItemsSortData(e)},l._getSorters=function(){var t=this.options.getSortData;for(var e in t){var i=t[e];this._sorters[e]=f(i)}},l._updateItemsSortData=function(t){for(var e=t&&t.length,i=0;e&&i<e;i++){var n=t[i];n.updateSortData()}};var f=function(){function t(t){if("string"!=typeof t)return t;var i=h(t).split(" "),n=i[0],o=n.match(/^\[(.+)\]$/),s=o&&o[1],r=e(s,n),a=d.sortDataParsers[i[1]];
return t=a?function(t){return t&&a(r(t))}:function(t){return t&&r(t)}}function e(t,e){return t?function(e){return e.getAttribute(t)}:function(t){var i=t.querySelector(e);return i&&i.textContent}}return t}();d.sortDataParsers={parseInt:function(t){return parseInt(t,10)},parseFloat:function(t){return parseFloat(t)}},l._sort=function(){var t=this.options.sortBy;if(t){var e=[].concat.apply(t,this.sortHistory),i=a(e,this.options.sortAscending);this.filteredItems.sort(i),t!=this.sortHistory[0]&&this.sortHistory.unshift(t)}},l._mode=function(){var t=this.options.layoutMode,e=this.modes[t];if(!e)throw new Error("No layout mode: "+t);return e.options=this.options[t],e},l._resetLayout=function(){e.prototype._resetLayout.call(this),this._mode()._resetLayout()},l._getItemLayoutPosition=function(t){return this._mode()._getItemLayoutPosition(t)},l._manageStamp=function(t){this._mode()._manageStamp(t)},l._getContainerSize=function(){return this._mode()._getContainerSize()},l.needsResizeLayout=function(){return this._mode().needsResizeLayout()},l.appended=function(t){var e=this.addItems(t);if(e.length){var i=this._filterRevealAdded(e);this.filteredItems=this.filteredItems.concat(i)}},l.prepended=function(t){var e=this._itemize(t);if(e.length){this._resetLayout(),this._manageStamps();var i=this._filterRevealAdded(e);this.layoutItems(this.filteredItems),this.filteredItems=i.concat(this.filteredItems),this.items=e.concat(this.items)}},l._filterRevealAdded=function(t){var e=this._filter(t);return this.hide(e.needHide),this.reveal(e.matches),this.layoutItems(e.matches,!0),e.matches},l.insert=function(t){var e=this.addItems(t);if(e.length){var i,n,o=e.length;for(i=0;i<o;i++)n=e[i],this.element.appendChild(n.element);var s=this._filter(e).matches;for(i=0;i<o;i++)e[i].isLayoutInstant=!0;for(this.arrange(),i=0;i<o;i++)delete e[i].isLayoutInstant;this.reveal(s)}};var c=l.remove;return l.remove=function(t){t=o.makeArray(t);var e=this.getItems(t);c.call(this,t);for(var i=e&&e.length,n=0;i&&n<i;n++){var s=e[n];o.removeFrom(this.filteredItems,s)}},l.shuffle=function(){for(var t=0;t<this.items.length;t++){var e=this.items[t];e.sortData.random=Math.random()}this.options.sortBy="random",this._sort(),this._layout()},l._noTransition=function(t,e){var i=this.options.transitionDuration;this.options.transitionDuration=0;var n=t.apply(this,e);return this.options.transitionDuration=i,n},l.getFilteredItemElements=function(){return this.filteredItems.map(function(t){return t.element})},d});

/*!
 * Packery layout mode PACKAGED v2.0.0
 * sub-classes Packery
 */

!function(a,b){"function"==typeof define&&define.amd?define("packery/js/rect",b):"object"==typeof module&&module.exports?module.exports=b():(a.Packery=a.Packery||{},a.Packery.Rect=b())}(window,function(){function a(b){for(var c in a.defaults)this[c]=a.defaults[c];for(c in b)this[c]=b[c]}a.defaults={x:0,y:0,width:0,height:0};var b=a.prototype;return b.contains=function(a){var b=a.width||0,c=a.height||0;return this.x<=a.x&&this.y<=a.y&&this.x+this.width>=a.x+b&&this.y+this.height>=a.y+c},b.overlaps=function(a){var b=this.x+this.width,c=this.y+this.height,d=a.x+a.width,e=a.y+a.height;return this.x<d&&b>a.x&&this.y<e&&c>a.y},b.getMaximalFreeRects=function(b){if(!this.overlaps(b))return!1;var c,d=[],e=this.x+this.width,f=this.y+this.height,g=b.x+b.width,h=b.y+b.height;return this.y<b.y&&(c=new a({x:this.x,y:this.y,width:this.width,height:b.y-this.y}),d.push(c)),e>g&&(c=new a({x:g,y:this.y,width:e-g,height:this.height}),d.push(c)),f>h&&(c=new a({x:this.x,y:h,width:this.width,height:f-h}),d.push(c)),this.x<b.x&&(c=new a({x:this.x,y:this.y,width:b.x-this.x,height:this.height}),d.push(c)),d},b.canFit=function(a){return this.width>=a.width&&this.height>=a.height},a}),function(a,b){if("function"==typeof define&&define.amd)define("packery/js/packer",["./rect"],b);else if("object"==typeof module&&module.exports)module.exports=b(require("./rect"));else{var c=a.Packery=a.Packery||{};c.Packer=b(c.Rect)}}(window,function(a){function b(a,b,c){this.width=a||0,this.height=b||0,this.sortDirection=c||"downwardLeftToRight",this.reset()}var c=b.prototype;c.reset=function(){this.spaces=[];var b=new a({x:0,y:0,width:this.width,height:this.height});this.spaces.push(b),this.sorter=d[this.sortDirection]||d.downwardLeftToRight},c.pack=function(a){for(var b=0;b<this.spaces.length;b++){var c=this.spaces[b];if(c.canFit(a)){this.placeInSpace(a,c);break}}},c.columnPack=function(a){for(var b=0;b<this.spaces.length;b++){var c=this.spaces[b],d=c.x<=a.x&&c.x+c.width>=a.x+a.width&&c.height>=a.height-.01;if(d){a.y=c.y,this.placed(a);break}}},c.rowPack=function(a){for(var b=0;b<this.spaces.length;b++){var c=this.spaces[b],d=c.y<=a.y&&c.y+c.height>=a.y+a.height&&c.width>=a.width-.01;if(d){a.x=c.x,this.placed(a);break}}},c.placeInSpace=function(a,b){a.x=b.x,a.y=b.y,this.placed(a)},c.placed=function(a){for(var b=[],c=0;c<this.spaces.length;c++){var d=this.spaces[c],e=d.getMaximalFreeRects(a);e?b.push.apply(b,e):b.push(d)}this.spaces=b,this.mergeSortSpaces()},c.mergeSortSpaces=function(){b.mergeRects(this.spaces),this.spaces.sort(this.sorter)},c.addSpace=function(a){this.spaces.push(a),this.mergeSortSpaces()},b.mergeRects=function(a){var b=0,c=a[b];a:for(;c;){for(var d=0,e=a[b+d];e;){if(e==c)d++;else{if(e.contains(c)){a.splice(b,1),c=a[b];continue a}c.contains(e)?a.splice(b+d,1):d++}e=a[b+d]}b++,c=a[b]}return a};var d={downwardLeftToRight:function(a,b){return a.y-b.y||a.x-b.x},rightwardTopToBottom:function(a,b){return a.x-b.x||a.y-b.y}};return b}),function(a,b){"function"==typeof define&&define.amd?define("packery/js/item",["outlayer/outlayer","./rect"],b):"object"==typeof module&&module.exports?module.exports=b(require("outlayer"),require("./rect")):a.Packery.Item=b(a.Outlayer,a.Packery.Rect)}(window,function(a,b){var c=document.documentElement.style,d="string"==typeof c.transform?"transform":"WebkitTransform",e=function(){a.Item.apply(this,arguments)},f=e.prototype=Object.create(a.Item.prototype),g=f._create;f._create=function(){g.call(this),this.rect=new b};var h=f.moveTo;return f.moveTo=function(a,b){var c=Math.abs(this.position.x-a),d=Math.abs(this.position.y-b),e=this.layout.dragItemCount&&!this.isPlacing&&!this.isTransitioning&&1>c&&1>d;return e?void this.goTo(a,b):void h.apply(this,arguments)},f.enablePlacing=function(){this.removeTransitionStyles(),this.isTransitioning&&d&&(this.element.style[d]="none"),this.isTransitioning=!1,this.getSize(),this.layout._setRectSize(this.element,this.rect),this.isPlacing=!0},f.disablePlacing=function(){this.isPlacing=!1},f.removeElem=function(){this.element.parentNode.removeChild(this.element),this.layout.packer.addSpace(this.rect),this.emitEvent("remove",[this])},f.showDropPlaceholder=function(){var a=this.dropPlaceholder;a||(a=this.dropPlaceholder=document.createElement("div"),a.className="packery-drop-placeholder",a.style.position="absolute"),a.style.width=this.size.width+"px",a.style.height=this.size.height+"px",this.positionDropPlaceholder(),this.layout.element.appendChild(a)},f.positionDropPlaceholder=function(){this.dropPlaceholder.style[d]="translate("+this.rect.x+"px, "+this.rect.y+"px)"},f.hideDropPlaceholder=function(){this.layout.element.removeChild(this.dropPlaceholder)},e}),function(a,b){"function"==typeof define&&define.amd?define("packery/js/packery",["get-size/get-size","outlayer/outlayer","./rect","./packer","./item"],b):"object"==typeof module&&module.exports?module.exports=b(require("get-size"),require("outlayer"),require("./rect"),require("./packer"),require("./item")):a.Packery=b(a.getSize,a.Outlayer,a.Packery.Rect,a.Packery.Packer,a.Packery.Item)}(window,function(a,b,c,d,e){function f(a,b){return a.position.y-b.position.y||a.position.x-b.position.x}function g(a,b){return a.position.x-b.position.x||a.position.y-b.position.y}function h(a,b){var c=b.x-a.x,d=b.y-a.y;return Math.sqrt(c*c+d*d)}c.prototype.canFit=function(a){return this.width>=a.width-1&&this.height>=a.height-1};var i=b.create("packery");i.Item=e;var j=i.prototype;j._create=function(){b.prototype._create.call(this),this.packer=new d,this.shiftPacker=new d,this.isEnabled=!0,this.dragItemCount=0;var a=this;this.handleDraggabilly={dragStart:function(){a.itemDragStart(this.element)},dragMove:function(){a.itemDragMove(this.element,this.position.x,this.position.y)},dragEnd:function(){a.itemDragEnd(this.element)}},this.handleUIDraggable={start:function(b,c){c&&a.itemDragStart(b.currentTarget)},drag:function(b,c){c&&a.itemDragMove(b.currentTarget,c.position.left,c.position.top)},stop:function(b,c){c&&a.itemDragEnd(b.currentTarget)}}},j._resetLayout=function(){this.getSize(),this._getMeasurements();var a,b,c;this._getOption("horizontal")?(a=1/0,b=this.size.innerHeight+this.gutter,c="rightwardTopToBottom"):(a=this.size.innerWidth+this.gutter,b=1/0,c="downwardLeftToRight"),this.packer.width=this.shiftPacker.width=a,this.packer.height=this.shiftPacker.height=b,this.packer.sortDirection=this.shiftPacker.sortDirection=c,this.packer.reset(),this.maxY=0,this.maxX=0},j._getMeasurements=function(){this._getMeasurement("columnWidth","width"),this._getMeasurement("rowHeight","height"),this._getMeasurement("gutter","width")},j._getItemLayoutPosition=function(a){if(this._setRectSize(a.element,a.rect),this.isShifting||this.dragItemCount>0){var b=this._getPackMethod();this.packer[b](a.rect)}else this.packer.pack(a.rect);return this._setMaxXY(a.rect),a.rect},j.shiftLayout=function(){this.isShifting=!0,this.layout(),delete this.isShifting},j._getPackMethod=function(){return this._getOption("horizontal")?"rowPack":"columnPack"},j._setMaxXY=function(a){this.maxX=Math.max(a.x+a.width,this.maxX),this.maxY=Math.max(a.y+a.height,this.maxY)},j._setRectSize=function(b,c){var d=a(b),e=d.outerWidth,f=d.outerHeight;(e||f)&&(e=this._applyGridGutter(e,this.columnWidth),f=this._applyGridGutter(f,this.rowHeight)),c.width=Math.min(e,this.packer.width),c.height=Math.min(f,this.packer.height)},j._applyGridGutter=function(a,b){if(!b)return a+this.gutter;b+=this.gutter;var c=a%b,d=c&&1>c?"round":"ceil";return a=Math[d](a/b)*b},j._getContainerSize=function(){return this._getOption("horizontal")?{width:this.maxX-this.gutter}:{height:this.maxY-this.gutter}},j._manageStamp=function(a){var b,d=this.getItem(a);if(d&&d.isPlacing)b=d.rect;else{var e=this._getElementOffset(a);b=new c({x:this._getOption("originLeft")?e.left:e.right,y:this._getOption("originTop")?e.top:e.bottom})}this._setRectSize(a,b),this.packer.placed(b),this._setMaxXY(b)},j.sortItemsByPosition=function(){var a=this._getOption("horizontal")?g:f;this.items.sort(a)},j.fit=function(a,b,c){var d=this.getItem(a);d&&(this.stamp(d.element),d.enablePlacing(),this.updateShiftTargets(d),b=void 0===b?d.rect.x:b,c=void 0===c?d.rect.y:c,this.shift(d,b,c),this._bindFitEvents(d),d.moveTo(d.rect.x,d.rect.y),this.shiftLayout(),this.unstamp(d.element),this.sortItemsByPosition(),d.disablePlacing())},j._bindFitEvents=function(a){function b(){d++,2==d&&c.dispatchEvent("fitComplete",null,[a])}var c=this,d=0;a.once("layout",b),this.once("layoutComplete",b)},j.resize=function(){this.isResizeBound&&this.needsResizeLayout()&&(this.options.shiftPercentResize?this.resizeShiftPercentLayout():this.layout())},j.needsResizeLayout=function(){var b=a(this.element),c=this._getOption("horizontal")?"innerHeight":"innerWidth";return b[c]!=this.size[c]},j.resizeShiftPercentLayout=function(){var b=this._getItemsForLayout(this.items),c=this._getOption("horizontal"),d=c?"y":"x",e=c?"height":"width",f=c?"rowHeight":"columnWidth",g=c?"innerHeight":"innerWidth",h=this[f];if(h=h&&h+this.gutter){this._getMeasurements();var i=this[f]+this.gutter;b.forEach(function(a){var b=Math.round(a.rect[d]/h);a.rect[d]=b*i})}else{var j=a(this.element)[g]+this.gutter,k=this.packer[e];b.forEach(function(a){a.rect[d]=a.rect[d]/k*j})}this.shiftLayout()},j.itemDragStart=function(a){if(this.isEnabled){this.stamp(a);var b=this.getItem(a);b&&(b.enablePlacing(),b.showDropPlaceholder(),this.dragItemCount++,this.updateShiftTargets(b))}},j.updateShiftTargets=function(a){this.shiftPacker.reset(),this._getBoundingRect();var b=this._getOption("originLeft"),d=this._getOption("originTop");this.stamps.forEach(function(a){var e=this.getItem(a);if(!e||!e.isPlacing){var f=this._getElementOffset(a),g=new c({x:b?f.left:f.right,y:d?f.top:f.bottom});this._setRectSize(a,g),this.shiftPacker.placed(g)}},this);var e=this._getOption("horizontal"),f=e?"rowHeight":"columnWidth",g=e?"height":"width";this.shiftTargetKeys=[],this.shiftTargets=[];var h,i=this[f];if(i=i&&i+this.gutter){var j=Math.ceil(a.rect[g]/i),k=Math.floor((this.shiftPacker[g]+this.gutter)/i);h=(k-j)*i;for(var l=0;k>l;l++)this._addShiftTarget(l*i,0,h)}else h=this.shiftPacker[g]+this.gutter-a.rect[g],this._addShiftTarget(0,0,h);var m=this._getItemsForLayout(this.items),n=this._getPackMethod();m.forEach(function(a){var b=a.rect;this._setRectSize(a.element,b),this.shiftPacker[n](b),this._addShiftTarget(b.x,b.y,h);var c=e?b.x+b.width:b.x,d=e?b.y:b.y+b.height;if(this._addShiftTarget(c,d,h),i)for(var f=Math.round(b[g]/i),j=1;f>j;j++){var k=e?c:b.x+i*j,l=e?b.y+i*j:d;this._addShiftTarget(k,l,h)}},this)},j._addShiftTarget=function(a,b,c){var d=this._getOption("horizontal")?b:a;if(!(0!==d&&d>c)){var e=a+","+b,f=-1!=this.shiftTargetKeys.indexOf(e);f||(this.shiftTargetKeys.push(e),this.shiftTargets.push({x:a,y:b}))}},j.shift=function(a,b,c){var d,e=1/0,f={x:b,y:c};this.shiftTargets.forEach(function(a){var b=h(a,f);e>b&&(d=a,e=b)}),a.rect.x=d.x,a.rect.y=d.y};var k=120;j.itemDragMove=function(a,b,c){function d(){f.shift(e,b,c),e.positionDropPlaceholder(),f.layout()}var e=this.isEnabled&&this.getItem(a);if(e){b-=this.size.paddingLeft,c-=this.size.paddingTop;var f=this,g=new Date;this._itemDragTime&&g-this._itemDragTime<k?(clearTimeout(this.dragTimeout),this.dragTimeout=setTimeout(d,k)):(d(),this._itemDragTime=g)}},j.itemDragEnd=function(a){function b(){d++,2==d&&(c.element.classList.remove("is-positioning-post-drag"),c.hideDropPlaceholder(),e.dispatchEvent("dragItemPositioned",null,[c]))}var c=this.isEnabled&&this.getItem(a);if(c){clearTimeout(this.dragTimeout),c.element.classList.add("is-positioning-post-drag");var d=0,e=this;c.once("layout",b),this.once("layoutComplete",b),c.moveTo(c.rect.x,c.rect.y),this.layout(),this.dragItemCount=Math.max(0,this.dragItemCount-1),this.sortItemsByPosition(),c.disablePlacing(),this.unstamp(c.element)}},j.bindDraggabillyEvents=function(a){this._bindDraggabillyEvents(a,"on")},j.unbindDraggabillyEvents=function(a){this._bindDraggabillyEvents(a,"off")},j._bindDraggabillyEvents=function(a,b){var c=this.handleDraggabilly;a[b]("dragStart",c.dragStart),a[b]("dragMove",c.dragMove),a[b]("dragEnd",c.dragEnd)},j.bindUIDraggableEvents=function(a){this._bindUIDraggableEvents(a,"on")},j.unbindUIDraggableEvents=function(a){this._bindUIDraggableEvents(a,"off")},j._bindUIDraggableEvents=function(a,b){var c=this.handleUIDraggable;a[b]("dragstart",c.start)[b]("drag",c.drag)[b]("dragstop",c.stop)};var l=j.destroy;return j.destroy=function(){l.apply(this,arguments),this.isEnabled=!1},i.Rect=c,i.Packer=d,i}),function(a,b){"function"==typeof define&&define.amd?define(["isotope/js/layout-mode","packery/js/packery"],b):"object"==typeof module&&module.exports?module.exports=b(require("isotope-layout/js/layout-mode"),require("packery")):b(a.Isotope.LayoutMode,a.Packery)}(window,function(a,b){var c=a.create("packery"),d=c.prototype,e={_getElementOffset:!0,_getMeasurement:!0};for(var f in b.prototype)e[f]||(d[f]=b.prototype[f]);var g=d._resetLayout;d._resetLayout=function(){this.packer=this.packer||new b.Packer,this.shiftPacker=this.shiftPacker||new b.Packer,g.apply(this,arguments)};var h=d._getItemLayoutPosition;d._getItemLayoutPosition=function(a){return a.rect=a.rect||new b.Rect,h.call(this,a)};var i=d.needsResizeLayout;d.needsResizeLayout=function(){return this._getOption("horizontal")?this.needsVerticalResizeLayout():i.call(this)};var j=d._getOption;return d._getOption=function(a){return"horizontal"==a?void 0!==this.options.isHorizontal?this.options.isHorizontal:this.options.horizontal:j.apply(this.isotope,arguments)},c});

/*!
Waypoints - 3.1.1
Copyright © 2011-2015 Caleb Troughton
Licensed under the MIT license.
https://github.com/imakewebthings/waypoints/blog/master/licenses.txt
*/
!function(){"use strict";function t(o){if(!o)throw new Error("No options passed to Waypoint constructor");if(!o.element)throw new Error("No element option passed to Waypoint constructor");if(!o.handler)throw new Error("No handler option passed to Waypoint constructor");this.key="waypoint-"+e,this.options=t.Adapter.extend({},t.defaults,o),this.element=this.options.element,this.adapter=new t.Adapter(this.element),this.callback=o.handler,this.axis=this.options.horizontal?"horizontal":"vertical",this.enabled=this.options.enabled,this.triggerPoint=null,this.group=t.Group.findOrCreate({name:this.options.group,axis:this.axis}),this.context=t.Context.findOrCreateByElement(this.options.context),t.offsetAliases[this.options.offset]&&(this.options.offset=t.offsetAliases[this.options.offset]),this.group.add(this),this.context.add(this),i[this.key]=this,e+=1}var e=0,i={};t.prototype.queueTrigger=function(t){this.group.queueTrigger(this,t)},t.prototype.trigger=function(t){this.enabled&&this.callback&&this.callback.apply(this,t)},t.prototype.destroy=function(){this.context.remove(this),this.group.remove(this),delete i[this.key]},t.prototype.disable=function(){return this.enabled=!1,this},t.prototype.enable=function(){return this.context.refresh(),this.enabled=!0,this},t.prototype.next=function(){return this.group.next(this)},t.prototype.previous=function(){return this.group.previous(this)},t.invokeAll=function(t){var e=[];for(var o in i)e.push(i[o]);for(var n=0,r=e.length;r>n;n++)e[n][t]()},t.destroyAll=function(){t.invokeAll("destroy")},t.disableAll=function(){t.invokeAll("disable")},t.enableAll=function(){t.invokeAll("enable")},t.refreshAll=function(){t.Context.refreshAll()},t.viewportHeight=function(){return window.innerHeight||document.documentElement.clientHeight},t.viewportWidth=function(){return document.documentElement.clientWidth},t.adapters=[],t.defaults={context:window,continuous:!0,enabled:!0,group:"default",horizontal:!1,offset:0},t.offsetAliases={"bottom-in-view":function(){return this.context.innerHeight()-this.adapter.outerHeight()},"right-in-view":function(){return this.context.innerWidth()-this.adapter.outerWidth()}},window.Waypoint=t}(),function(){"use strict";function t(t){window.setTimeout(t,1e3/60)}function e(t){this.element=t,this.Adapter=n.Adapter,this.adapter=new this.Adapter(t),this.key="waypoint-context-"+i,this.didScroll=!1,this.didResize=!1,this.oldScroll={x:this.adapter.scrollLeft(),y:this.adapter.scrollTop()},this.waypoints={vertical:{},horizontal:{}},t.waypointContextKey=this.key,o[t.waypointContextKey]=this,i+=1,this.createThrottledScrollHandler(),this.createThrottledResizeHandler()}var i=0,o={},n=window.Waypoint,r=window.onload;e.prototype.add=function(t){var e=t.options.horizontal?"horizontal":"vertical";this.waypoints[e][t.key]=t,this.refresh()},e.prototype.checkEmpty=function(){var t=this.Adapter.isEmptyObject(this.waypoints.horizontal),e=this.Adapter.isEmptyObject(this.waypoints.vertical);t&&e&&(this.adapter.off(".waypoints"),delete o[this.key])},e.prototype.createThrottledResizeHandler=function(){function t(){e.handleResize(),e.didResize=!1}var e=this;this.adapter.on("resize.waypoints",function(){e.didResize||(e.didResize=!0,n.requestAnimationFrame(t))})},e.prototype.createThrottledScrollHandler=function(){function t(){e.handleScroll(),e.didScroll=!1}var e=this;this.adapter.on("scroll.waypoints",function(){(!e.didScroll||n.isTouch)&&(e.didScroll=!0,n.requestAnimationFrame(t))})},e.prototype.handleResize=function(){n.Context.refreshAll()},e.prototype.handleScroll=function(){var t={},e={horizontal:{newScroll:this.adapter.scrollLeft(),oldScroll:this.oldScroll.x,forward:"right",backward:"left"},vertical:{newScroll:this.adapter.scrollTop(),oldScroll:this.oldScroll.y,forward:"down",backward:"up"}};for(var i in e){var o=e[i],n=o.newScroll>o.oldScroll,r=n?o.forward:o.backward;for(var s in this.waypoints[i]){var a=this.waypoints[i][s],l=o.oldScroll<a.triggerPoint,h=o.newScroll>=a.triggerPoint,p=l&&h,u=!l&&!h;(p||u)&&(a.queueTrigger(r),t[a.group.id]=a.group)}}for(var c in t)t[c].flushTriggers();this.oldScroll={x:e.horizontal.newScroll,y:e.vertical.newScroll}},e.prototype.innerHeight=function(){return this.element==this.element.window?n.viewportHeight():this.adapter.innerHeight()},e.prototype.remove=function(t){delete this.waypoints[t.axis][t.key],this.checkEmpty()},e.prototype.innerWidth=function(){return this.element==this.element.window?n.viewportWidth():this.adapter.innerWidth()},e.prototype.destroy=function(){var t=[];for(var e in this.waypoints)for(var i in this.waypoints[e])t.push(this.waypoints[e][i]);for(var o=0,n=t.length;n>o;o++)t[o].destroy()},e.prototype.refresh=function(){var t,e=this.element==this.element.window,i=this.adapter.offset(),o={};this.handleScroll(),t={horizontal:{contextOffset:e?0:i.left,contextScroll:e?0:this.oldScroll.x,contextDimension:this.innerWidth(),oldScroll:this.oldScroll.x,forward:"right",backward:"left",offsetProp:"left"},vertical:{contextOffset:e?0:i.top,contextScroll:e?0:this.oldScroll.y,contextDimension:this.innerHeight(),oldScroll:this.oldScroll.y,forward:"down",backward:"up",offsetProp:"top"}};for(var n in t){var r=t[n];for(var s in this.waypoints[n]){var a,l,h,p,u,c=this.waypoints[n][s],d=c.options.offset,f=c.triggerPoint,w=0,y=null==f;c.element!==c.element.window&&(w=c.adapter.offset()[r.offsetProp]),"function"==typeof d?d=d.apply(c):"string"==typeof d&&(d=parseFloat(d),c.options.offset.indexOf("%")>-1&&(d=Math.ceil(r.contextDimension*d/100))),a=r.contextScroll-r.contextOffset,c.triggerPoint=w+a-d,l=f<r.oldScroll,h=c.triggerPoint>=r.oldScroll,p=l&&h,u=!l&&!h,!y&&p?(c.queueTrigger(r.backward),o[c.group.id]=c.group):!y&&u?(c.queueTrigger(r.forward),o[c.group.id]=c.group):y&&r.oldScroll>=c.triggerPoint&&(c.queueTrigger(r.forward),o[c.group.id]=c.group)}}for(var g in o)o[g].flushTriggers();return this},e.findOrCreateByElement=function(t){return e.findByElement(t)||new e(t)},e.refreshAll=function(){for(var t in o)o[t].refresh()},e.findByElement=function(t){return o[t.waypointContextKey]},window.onload=function(){r&&r(),e.refreshAll()},n.requestAnimationFrame=function(e){var i=window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||t;i.call(window,e)},n.Context=e}(),function(){"use strict";function t(t,e){return t.triggerPoint-e.triggerPoint}function e(t,e){return e.triggerPoint-t.triggerPoint}function i(t){this.name=t.name,this.axis=t.axis,this.id=this.name+"-"+this.axis,this.waypoints=[],this.clearTriggerQueues(),o[this.axis][this.name]=this}var o={vertical:{},horizontal:{}},n=window.Waypoint;i.prototype.add=function(t){this.waypoints.push(t)},i.prototype.clearTriggerQueues=function(){this.triggerQueues={up:[],down:[],left:[],right:[]}},i.prototype.flushTriggers=function(){for(var i in this.triggerQueues){var o=this.triggerQueues[i],n="up"===i||"left"===i;o.sort(n?e:t);for(var r=0,s=o.length;s>r;r+=1){var a=o[r];(a.options.continuous||r===o.length-1)&&a.trigger([i])}}this.clearTriggerQueues()},i.prototype.next=function(e){this.waypoints.sort(t);var i=n.Adapter.inArray(e,this.waypoints),o=i===this.waypoints.length-1;return o?null:this.waypoints[i+1]},i.prototype.previous=function(e){this.waypoints.sort(t);var i=n.Adapter.inArray(e,this.waypoints);return i?this.waypoints[i-1]:null},i.prototype.queueTrigger=function(t,e){this.triggerQueues[e].push(t)},i.prototype.remove=function(t){var e=n.Adapter.inArray(t,this.waypoints);e>-1&&this.waypoints.splice(e,1)},i.prototype.first=function(){return this.waypoints[0]},i.prototype.last=function(){return this.waypoints[this.waypoints.length-1]},i.findOrCreate=function(t){return o[t.axis][t.name]||new i(t)},n.Group=i}(),function(){"use strict";function t(t){this.$element=e(t)}var e=window.jQuery,i=window.Waypoint;e.each(["innerHeight","innerWidth","off","offset","on","outerHeight","outerWidth","scrollLeft","scrollTop"],function(e,i){t.prototype[i]=function(){var t=Array.prototype.slice.call(arguments);return this.$element[i].apply(this.$element,t)}}),e.each(["extend","inArray","isEmptyObject"],function(i,o){t[o]=e[o]}),i.adapters.push({name:"jquery",Adapter:t}),i.Adapter=t}(),function(){"use strict";function t(t){return function(){var i=[],o=arguments[0];return t.isFunction(arguments[0])&&(o=t.extend({},arguments[1]),o.handler=arguments[0]),this.each(function(){var n=t.extend({},o,{element:this});"string"==typeof n.context&&(n.context=t(this).closest(n.context)[0]),i.push(new e(n))}),i}}var e=window.Waypoint;window.jQuery&&(window.jQuery.fn.waypoint=t(window.jQuery)),window.Zepto&&(window.Zepto.fn.waypoint=t(window.Zepto))}();


/*
 * jQuery Browser Plugin 0.0.6
 * https://github.com/gabceb/jquery-browser-plugin
 *
 * Original jquery-browser code Copyright 2005, 2013 jQuery Foundation, Inc. and other contributors
 * http://jquery.org/license
 *
 * Modifications Copyright 2014 Gabriel Cebrian
 * https://github.com/gabceb
 *
 * Released under the MIT license
 */
 !function(a,b){"use strict";var c,d;if(a.uaMatch=function(a){a=a.toLowerCase();var b=/(opr)[\/]([\w.]+)/.exec(a)||/(chrome)[ \/]([\w.]+)/.exec(a)||/(version)[ \/]([\w.]+).*(safari)[ \/]([\w.]+)/.exec(a)||/(webkit)[ \/]([\w.]+)/.exec(a)||/(opera)(?:.*version|)[ \/]([\w.]+)/.exec(a)||/(msie) ([\w.]+)/.exec(a)||a.indexOf("trident")>=0&&/(rv)(?::| )([\w.]+)/.exec(a)||a.indexOf("compatible")<0&&/(mozilla)(?:.*? rv:([\w.]+)|)/.exec(a)||[],c=/(ipad)/.exec(a)||/(iphone)/.exec(a)||/(android)/.exec(a)||/(windows phone)/.exec(a)||/(win)/.exec(a)||/(mac)/.exec(a)||/(linux)/.exec(a)||/(cros)/i.exec(a)||[];return{browser:b[3]||b[1]||"",version:b[2]||"0",platform:c[0]||""}},c=a.uaMatch(b.navigator.userAgent),d={},c.browser&&(d[c.browser]=!0,d.version=c.version,d.versionNumber=parseInt(c.version)),c.platform&&(d[c.platform]=!0),(d.android||d.ipad||d.iphone||d["windows phone"])&&(d.mobile=!0),(d.cros||d.mac||d.linux||d.win)&&(d.desktop=!0),(d.chrome||d.opr||d.safari)&&(d.webkit=!0),d.rv){var e="msie";c.browser=e,d[e]=!0}if(d.opr){var f="opera";c.browser=f,d[f]=!0}if(d.safari&&d.android){var g="android";c.browser=g,d[g]=!0}d.name=c.browser,d.platform=c.platform,a.browser=d}(jQuery,window);
 
/*Vimeo Frogaloop API for videos*/
var Froogaloop=function(){function e(a){return new e.fn.init(a)}function g(a,c,b){if(!b.contentWindow.postMessage)return!1;a=JSON.stringify({method:a,value:c});b.contentWindow.postMessage(a,h)}function l(a){var c,b;try{c=JSON.parse(a.data),b=c.event||c.method}catch(e){}"ready"!=b||k||(k=!0);if(!/^https?:\/\/player.vimeo.com/.test(a.origin))return!1;"*"===h&&(h=a.origin);a=c.value;var m=c.data,f=""===f?null:c.player_id;c=f?d[f][b]:d[b];b=[];if(!c)return!1;void 0!==a&&b.push(a);m&&b.push(m);f&&b.push(f);
return 0<b.length?c.apply(null,b):c.call()}function n(a,c,b){b?(d[b]||(d[b]={}),d[b][a]=c):d[a]=c}var d={},k=!1,h="*";e.fn=e.prototype={element:null,init:function(a){"string"===typeof a&&(a=document.getElementById(a));this.element=a;return this},api:function(a,c){if(!this.element||!a)return!1;var b=this.element,d=""!==b.id?b.id:null,e=c&&c.constructor&&c.call&&c.apply?null:c,f=c&&c.constructor&&c.call&&c.apply?c:null;f&&n(a,f,d);g(a,e,b);return this},addEvent:function(a,c){if(!this.element)return!1;
var b=this.element,d=""!==b.id?b.id:null;n(a,c,d);"ready"!=a?g("addEventListener",a,b):"ready"==a&&k&&c.call(null,d);return this},removeEvent:function(a){if(!this.element)return!1;var c=this.element,b=""!==c.id?c.id:null;a:{if(b&&d[b]){if(!d[b][a]){b=!1;break a}d[b][a]=null}else{if(!d[a]){b=!1;break a}d[a]=null}b=!0}"ready"!=a&&b&&g("removeEventListener",a,c)}};e.fn.init.prototype=e.fn;window.addEventListener?window.addEventListener("message",l,!1):window.attachEvent("onmessage",l);return window.Froogaloop=
window.$f=e}();


// http://paulirish.com/2011/requestanimationframe-for-smart-animating/ + http://my.opera.com/emoller/blog/2011/12/20/requestanimationframe-for-smart-er-animating
// requestAnimationFrame polyfill by Erik Möller. fixes from Paul Irish and Tino Zijdel. can be removed if IE9 is no longer supported or all parallax scripts are gone MIT license
(function(){var lastTime=0;var vendors=['ms','moz','webkit','o'];for(var x=0;x<vendors.length&&!window.requestAnimationFrame;++x){window.requestAnimationFrame=window[vendors[x]+'RequestAnimationFrame'];window.cancelAnimationFrame=window[vendors[x]+'CancelAnimationFrame']||window[vendors[x]+'CancelRequestAnimationFrame']}if(!window.requestAnimationFrame)window.requestAnimationFrame=function(callback,element){var currTime=new Date().getTime();var timeToCall=Math.max(0,16-(currTime-lastTime));var id=window.setTimeout(function(){callback(currTime+timeToCall)},timeToCall);lastTime=currTime+timeToCall;return id};if(!window.cancelAnimationFrame)window.cancelAnimationFrame=function(id){clearTimeout(id)}}());

jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ? 
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
};





