(function($)
{
	"use strict";

	$( function()
	{
		//global variables that are used on several ocassions
		$.avia_utilities = $.avia_utilities || {};

		if( 'undefined' == typeof $.avia_utilities.isMobile )
		{
			if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) && 'ontouchstart' in document.documentElement)
			{
				$.avia_utilities.isMobile = true;
			}
			else
			{
				$.avia_utilities.isMobile = false;
			}
		}

		//activate fixed bg fallback for mobile
		if( $.fn.avia_mobile_fixed )
		{
			$( '.avia-bg-style-fixed' ).avia_mobile_fixed();
		}

		//calculate the browser height and append a css rule to the head
		if( $.fn.avia_browser_height )
		{
			$( '.av-minimum-height, .avia-fullscreen-slider, .av-cell-min-height' ).avia_browser_height();
		}

		//calculate the element height in a layout container
		if( $.fn.avia_container_height )
		{
			$( '.av-column-min-height-pc' ).avia_container_height();
		}

		//calculate the height of each video section
		if( $.fn.avia_video_section )
		{
			$( '.av-section-with-video-bg' ).avia_video_section();
		}

		//creates team social icon tooltip
		new $.AviaTooltip({'class': "avia-tooltip", data: "avia-tooltip", delay:0, scope: "body"});

		//creates icon element tooltip
		new $.AviaTooltip({'class': "avia-tooltip avia-icon-tooltip", data: "avia-icon-tooltip", delay:0, scope: "body"});

		$.avia_utilities.activate_shortcode_scripts();

		//layer slider height helper
		if( $.fn.layer_slider_height_helper )
		{
			$( '.avia-layerslider' ).layer_slider_height_helper();
		}

		//"ajax" portfolio
		if( $.fn.avia_portfolio_preview )
		{
			$( '.grid-links-ajax' ).avia_portfolio_preview();
		}

		// activate the masonry script: sorting/loading/etc
		if( $.fn.avia_masonry )
		{
			$( '.av-masonry' ).avia_masonry();
		}

		//activate the accordion
		if( $.fn.aviaccordion )
		{
			$( '.aviaccordion' ).aviaccordion();
		}


		//activate the accordion
		if( $.fn.avia_textrotator )
		{
			$( '.av-rotator-container' ).avia_textrotator();
		}

		//activates the tab section and slideshow section shortcode
		if( $.fn.avia_sc_tab_section )
		{
			$( '.av-tab-section-container' ).avia_sc_tab_section();
		}

		//activates the hor gallery  shortcode
		if( $.fn.avia_hor_gallery )
		{
			$( '.av-horizontal-gallery' ).avia_hor_gallery();
		}

		//	activate columns and cells with a link
		if( $.fn.avia_link_column )
		{
			$( '.avia-link-column' ).avia_link_column();
		}

		if( $.fn.avia_delayed_animation_in_container )
		{
			$( '.av-animation-delay-container' ).avia_delayed_animation_in_container();
		}
	});


// -------------------------------------------------------------------------------------------
// ACTIVATE ALL SHORTCODES
// -------------------------------------------------------------------------------------------

	$.avia_utilities = $.avia_utilities || {};
	$.avia_utilities.activate_shortcode_scripts = function( container )
	{
		if( typeof container == 'undefined' )
		{
			container = 'body';
		}

		//activates the form shortcode
		if( $.fn.avia_ajax_form )
		{
			$( '.avia_ajax_form:not( .avia-disable-default-ajax )', container ).avia_ajax_form();
		}

		activate_waypoints( container );

		//activate the video api
		if( $.fn.aviaVideoApi )
		{
			$( '.avia-slideshow iframe[src*="youtube.com"], .av_youtube_frame, .av_vimeo_frame, .avia-slideshow video' ).aviaVideoApi({}, 'li');
		}

	    //activates the toggle shortcode
		if( $.fn.avia_sc_toggle )
		{
			$( '.togglecontainer', container ).avia_sc_toggle();
		}

		//activates the tabs shortcode
		if( $.fn.avia_sc_tabs )
		{
			$( '.top_tab', container ).avia_sc_tabs();
			$( '.sidebar_tab', container ).avia_sc_tabs({sidebar:true});
		}

		//activates behavior and animation for gallery
		if( $.fn.avia_sc_gallery )
		{
			$( '.avia-gallery', container ).avia_sc_gallery();
		}

		//activates animated number shortcode
		if( $.fn.avia_sc_animated_number )
		{
			$( '.avia-animated-number', container ).avia_sc_animated_number();
		}

		//animation for elements that are not connected like icon shortcode
		if( $.fn.avia_sc_animation_delayed )
		{
			$( '.av_font_icon', container ).avia_sc_animation_delayed({delay:100});
			$( '.avia-image-container', container ).avia_sc_animation_delayed({delay:100});
			$( '.av-hotspot-image-container', container ).avia_sc_animation_delayed({delay:100});
			$( '.av-animated-generic', container ).avia_sc_animation_delayed({delay:100});

			//	@since 5.0 - avoid conflict with already existing classes
			$( '.av-animated-when-visible', container ).avia_sc_animation_delayed({delay:100});
			$( '.av-animated-when-almost-visible', container ).avia_sc_animation_delayed({delay:100});
			$( '.av-animated-when-visible-95', container ).avia_sc_animation_delayed({delay:100});
		}

		//activates animation for iconlist
		if( $.fn.avia_sc_iconlist )
		{
			$( '.avia-icon-list.av-iconlist-big.avia-iconlist-animate', container ).avia_sc_iconlist();
		}

		//activates animation for progress bar
		if( $.fn.avia_sc_progressbar )
		{
			$( '.avia-progress-bar-container', container ).avia_sc_progressbar();
		}

		//activates animation for testimonial
		if( $.fn.avia_sc_testimonial )
		{
			$( '.avia-testimonial-wrapper', container ).avia_sc_testimonial();
		}

		//activate the fullscreen slider
		if( $.fn.aviaFullscreenSlider )
		{
			$( '.avia-slideshow.av_fullscreen', container ).aviaFullscreenSlider();
		}

		//activate the aviaslider
		if( $.fn.aviaSlider )
		{
			$( '.avia-slideshow:not(.av_fullscreen)', container ).aviaSlider();

			//content slider
        	$( '.avia-content-slider-active', container ).aviaSlider({wrapElement: '.avia-content-slider-inner', slideElement: '.slide-entry-wrap', fullfade: true});

			//testimonial slider
			$( '.avia-slider-testimonials', container ).aviaSlider({wrapElement: '.avia-testimonial-row', slideElement: '.avia-testimonial', fullfade: true});
        }

		//load magazine sorting
		if( $.fn.aviaMagazine )
		{
			$( '.av-magazine-tabs-active', container ).aviaMagazine();
		}

		//load image hotspot
		if( $.fn.aviaHotspots )
		{
			$( '.av-hotspot-image-container', container ).aviaHotspots();
		}

		//load countdown
		if( $.fn.aviaCountdown )
		{
			$( '.av-countdown-timer', container ).aviaCountdown();
		}

		//load audio player
		if( $.fn.aviaPlayer )
		{
			$( '.av-player', container ).aviaPlayer();
		}

		//	load icon circles
		if( $.fn.aviaIconCircles )
		{
			$('.av-icon-circles-container').aviaIconCircles();
		}

		//	load icon grid
		if( $.fn.avia_sc_icongrid )
		{
			$('.avia-icon-grid-container').avia_sc_icongrid();
		}

    };


	function activate_waypoints( container )
	{
		//activates simple css animations of the content once the user scrolls to an elements
		if( $.fn.avia_waypoints )
		{
			if( typeof container == 'undefined' )
			{
				container = 'body';
			}

			$( '.avia_animate_when_visible', container ).avia_waypoints();			//  'bottom-in-view'
			$( '.avia_animate_when_almost_visible', container ).avia_waypoints( { offset: '80%'} );

			//	@since 5.0 - avoid conflict with already existing classes
			$( '.av-animated-when-visible', container ).avia_waypoints();
			$( '.av-animated-when-almost-visible', container ).avia_waypoints( { offset: '80%'} );
			$( '.av-animated-when-visible-95', container ).avia_waypoints( { offset: '95%'} );

			//	@since 5.3 - new offsets added to support filter 'avf_alb_element_animation'
			$( '.av-animated-when-visible-15', container ).avia_waypoints( { offset: '15%'} );
			$( '.av-animated-when-visible-25', container ).avia_waypoints( { offset: '25%'} );
			$( '.av-animated-when-visible-30', container ).avia_waypoints( { offset: '30%'} );
			$( '.av-animated-when-visible-40', container ).avia_waypoints( { offset: '40%'} );
			$( '.av-animated-when-visible-50', container ).avia_waypoints( { offset: '50%'} );
			$( '.av-animated-when-visible-60', container ).avia_waypoints( { offset: '60%'} );
			$( '.av-animated-when-visible-70', container ).avia_waypoints( { offset: '70%'} );
			$( '.av-animated-when-visible-75', container ).avia_waypoints( { offset: '75%'} );
			$( '.av-animated-when-visible-80', container ).avia_waypoints( { offset: '80%'} );		//	av-animated-when-almost-visible
			$( '.av-animated-when-visible-85', container ).avia_waypoints( { offset: '85%'} );

			var disable_mobile = $( 'body' ).hasClass( 'avia-mobile-no-animations' );

			if( container == 'body' && disable_mobile )
			{
				container = '.avia_desktop body';
			}

			$( '.av-animated-generic', container ).avia_waypoints( { offset: '95%'} );
		}
	};


// -------------------------------------------------------------------------------------------



	// -------------------------------------------------------------------------------------------
	// Helper to allow fixed bgs on mobile
	// -------------------------------------------------------------------------------------------
	$.fn.avia_mobile_fixed = function(options)
	{
		var isMobile = $.avia_utilities.isMobile;

		if( ! isMobile )
		{
			return;
		}

		return this.each( function()
		{
			var current				= $(this).addClass('av-parallax-section'),
				$background 		= current.attr('style'),
				$attachment_class 	= current.data('section-bg-repeat'),
				template			= "";

				if($attachment_class == 'stretch' || $attachment_class == 'no-repeat' )
				{
					$attachment_class = " avia-full-stretch";
				}
				else
				{
					$attachment_class = "";
				}

				template = "<div class='av-parallax " + $attachment_class + "' data-avia-parallax-ratio='0.0' style = '" + $background + "' ></div>";

				current.prepend(template);
				current.attr('style','');
		});
	};



	// -------------------------------------------------------------------------------------------
	//  shortcode javascript for delayed animation even when non connected elements are used
	// -------------------------------------------------------------------------------------------
	$.fn.avia_sc_animation_delayed = function(options)
	{
		var global_timer = 0,
			delay = options.delay || 50,
			max_timer = 10,
			new_max = setTimeout( function(){ max_timer = 20; }, 500);

		return this.each(function()
		{
			var elements = $(this);

			//trigger displaying of thumbnails
			elements.on( 'avia_start_animation', function()
			{
				var element = $(this);

				if( global_timer < max_timer )
				{
					global_timer ++;
				}

				setTimeout( function()
				{
					element.addClass('avia_start_delayed_animation');
					if( global_timer > 0 )
					{
						global_timer --;
					}

				}, ( global_timer * delay ) );

			});
		});
	};

	/*delayd animations when used within tab sections or similar elements. this way they get animated each new time a tab is shown*/
	$.fn.avia_delayed_animation_in_container = function( options )
	{
		return this.each( function()
		{
			var elements = $(this);

			elements.on( 'avia_start_animation_if_current_slide_is_active', function()
			{
				var current = $(this),
					animate = current.find( '.avia_start_animation_when_active' );

				animate.addClass( 'avia_start_animation' ).trigger( 'avia_start_animation' );
			});

			elements.on( 'avia_remove_animation', function()
			{
				var current = $(this),
					animate = current.find( '.avia_start_animation_when_active, .avia_start_animation' );

				animate.removeClass( 'avia_start_animation avia_start_delayed_animation' );
			});
		});
	};


	// -------------------------------------------------------------------------------------------
	// Section Height Helper
	// -------------------------------------------------------------------------------------------
	$.fn.avia_browser_height = function()
	{
		if( ! this.length )
		{
			return this;
		}

		var win			= $(window),
			html_el		= $('html'),
			headFirst	= $( 'head' ).first(),
			subtract	= $('#wpadminbar, #header.av_header_top:not(.html_header_transparency #header), #main>.title_container'),
			css_block	= $("<style type='text/css' id='av-browser-height'></style>").appendTo( headFirst ),
			sidebar_menu= $('.html_header_sidebar #top #header_main'),
			full_slider	= $('.html_header_sidebar .avia-fullscreen-slider.avia-builder-el-0.avia-builder-el-no-sibling').addClass('av-solo-full'),
			pc_heights	= [ 25, 50, 75 ],
			calc_height = function()
			{
				var css			= '',
					wh100 		= win.height(),
					ww100 		= win.width(),
					wh100_mod 	= wh100,
					whCover		= (wh100 / 9) * 16,
					wwCover		= (ww100 / 16) * 9,
					solo		= 0;

				if( sidebar_menu.length )
				{
					solo = sidebar_menu.height();
				}

				subtract.each( function()
				{
					wh100_mod -= this.offsetHeight - 1;
				});

				var whCoverMod = ( wh100_mod / 9 ) * 16;

				//fade in of section content with minimum height once the height has been calculated
				css += ".avia-section.av-minimum-height .container{opacity: 1; }\n";

				//various section heights (100-25% as well as 100% - header/adminbar in case its the first builder element)
				css += ".av-minimum-height-100:not(.av-slideshow-section) .container, .avia-fullscreen-slider .avia-slideshow, #top.avia-blank .av-minimum-height-100 .container, .av-cell-min-height-100 > .flex_cell{height:" + wh100 + "px;}\n";

				css += ".av-minimum-height-100.av-slideshow-section .container { height:unset; }\n";
				css += ".av-minimum-height-100.av-slideshow-section {min-height:" + wh100 + "px;}\n";


				$.each( pc_heights, function( index, value )
				{
					var wh = Math.round( wh100 * ( value / 100.0 ) );
					css += ".av-minimum-height-" + value + ":not(.av-slideshow-section) .container, .av-cell-min-height-" + value + " > .flex_cell	{height:" + wh + "px;}\n";
					css += ".av-minimum-height-" + value + ".av-slideshow-section {min-height:" + wh + "px;}\n";
				});

				css += ".avia-builder-el-0.av-minimum-height-100:not(.av-slideshow-section) .container, .avia-builder-el-0.avia-fullscreen-slider .avia-slideshow, .avia-builder-el-0.av-cell-min-height-100 > .flex_cell{height:" + wh100_mod + "px;}\n";

				css += "#top .av-solo-full .avia-slideshow {min-height:" + solo + "px;}\n";

				//fullscreen video calculations
				if( ww100 / wh100 < 16 / 9 )
				{
					css += "#top .av-element-cover iframe, #top .av-element-cover embed, #top .av-element-cover object, #top .av-element-cover video{width:" + whCover + "px; left: -" + ( whCover - ww100 ) / 2 + "px;}\n";
				}
				else
				{
					css += "#top .av-element-cover iframe, #top .av-element-cover embed, #top .av-element-cover object, #top .av-element-cover video{height:" + wwCover + "px; top: -"+( wwCover - wh100 ) / 2 + "px;}\n";
				}

				if( ww100 / wh100_mod < 16 / 9 )
				{
					css += "#top .avia-builder-el-0 .av-element-cover iframe, #top .avia-builder-el-0 .av-element-cover embed, #top .avia-builder-el-0 .av-element-cover object, #top .avia-builder-el-0 .av-element-cover video{width:" + whCoverMod + "px; left: -" + ( whCoverMod - ww100 ) / 2 + "px;}\n";
				}
				else
				{
					css += "#top .avia-builder-el-0 .av-element-cover iframe, #top .avia-builder-el-0 .av-element-cover embed, #top .avia-builder-el-0 .av-element-cover object, #top .avia-builder-el-0 .av-element-cover video{height:" + wwCover + "px; top: -" + ( wwCover - wh100_mod ) / 2 + "px;}\n";
				}

				//ie8 needs different insert method
				try
				{
					css_block.text( css );
				}
				catch(err)
				{
					css_block.remove();
					css_block = $( "<style type='text/css' id='av-browser-height'>" + css + "</style>" ).appendTo( headFirst );
				}

				setTimeout(function()
				{
					win.trigger( 'av-height-change' ); /*broadcast the height change*/
				}, 100 );
			};

		this.each( function( index )
		{
			var height = $( this ).data( 'av_minimum_height_pc' );
			if( 'number' != typeof height )
			{
				return this;
			}

			height = parseInt( height );

			if( ( -1 == $.inArray( height, pc_heights ) ) && ( height != 100 ) )
			{
				pc_heights.push( height );
			}

			return this;
		});

		win.on( 'debouncedresize', calc_height );
		calc_height();
	};

	// -------------------------------------------------------------------------------------------
	// Layout container height helper
	// -------------------------------------------------------------------------------------------
	$.fn.avia_container_height = function()
	{
		if( ! this.length )
		{
			return this;
		}

		var win = $( window ),
			calc_height = function()
			{
				var column = $( this ),
					jsonHeight = column.data( 'av-column-min-height' ),
					minHeight = parseInt( jsonHeight['column-min-pc'], 10 ),
					container = null,
					containerHeight = 0,
					columMinHeight = 0;

				if( isNaN( minHeight ) || minHeight == 0 )
				{
					return;
				}

				//	try to find a layout container, else take browser height
				container = column.closest( '.avia-section' );
				if( ! container.length )
				{
					container = column.closest( '.av-gridrow-cell' );
				}
				if( ! container.length )
				{
					//	tab section and slideshow section
					container = column.closest( '.av-layout-tab' );
				}

				containerHeight = container.length ? container.outerHeight() : win.height();

				columMinHeight = containerHeight * ( minHeight / 100.0 );

				if( ! jsonHeight['column-equal-height'] )
				{
					column.css( 'min-height', columMinHeight + 'px');
					column.css( 'height', 'auto');
				}
				else
				{
					column.css( 'height', columMinHeight + 'px');
				}

				setTimeout( function()
				{
					win.trigger( 'av-height-change' ); /*broadcast the height change*/
				}, 100 );
			};

		this.each( function( index )
		{
			var column = $( this ),
				jsonHeight = column.data( 'av-column-min-height' );

			if( 'object' != typeof jsonHeight )
			{
				return this;
			}

			win.on( 'debouncedresize', calc_height.bind( column ) );
			calc_height.call( column );

			return this;
		});

	};

	// -------------------------------------------------------------------------------------------
	// Video Section helper
	// -------------------------------------------------------------------------------------------
	$.fn.avia_video_section = function()
	{
		if(!this.length) return;

		var elements	= this.length, content = "",
			win			= $(window),
			headFirst	= $( 'head' ).first(),
			css_block	= $("<style type='text/css' id='av-section-height'></style>").appendTo( headFirst ),
			calc_height = function(section, counter)
			{
				if(counter === 0) { content = "";}

				var css			= "",
					the_id		= '#' +section.attr('id'),
					wh100 		= section.height(),
					ww100 		= section.width(),
					aspect		= section.data('sectionVideoRatio').split(':'),
					video_w		= aspect[0],
					video_h		= aspect[1],
					whCover		= (wh100 / video_h ) * video_w,
					wwCover		= (ww100 / video_w ) * video_h;

				//fullscreen video calculations
				if(ww100/wh100 < video_w/video_h)
				{
					css += "#top "+the_id+" .av-section-video-bg iframe, #top "+the_id+" .av-section-video-bg embed, #top "+the_id+" .av-section-video-bg object, #top "+the_id+" .av-section-video-bg video{width:"+whCover+"px; left: -"+(whCover - ww100)/2+"px;}\n";
				}
				else
				{
					css += "#top "+the_id+" .av-section-video-bg iframe, #top "+the_id+" .av-section-video-bg embed, #top "+the_id+" .av-section-video-bg object, #top "+the_id+" .av-section-video-bg video{height:"+wwCover+"px; top: -"+(wwCover - wh100)/2+"px;}\n";
				}

				content = content + css;

				if(elements == counter + 1)
				{
					//ie8 needs different insert method
					try{
						css_block.text(content);
					}
					catch(err){
						css_block.remove();
						css_block = $("<style type='text/css' id='av-section-height'>"+content+"</style>").appendTo( headFirst );
					}
				}
			};


		return this.each(function(i)
		{
			var self = $(this);

			win.on( 'debouncedresize', function(){ calc_height(self, i); });
			calc_height(self, i);
		});

	};


	/**
	 * Column or cell with a link
	 *
	 * @returns {jQuery}
	 */
	$.fn.avia_link_column = function()
	{
		return this.each( function()
		{
			$(this).on( 'click', function(e)
			{
				//	if event is bubbled from an <a> link, do not activate link of column/cell
				if( 'undefined' !== typeof e.target && 'undefined' !== typeof e.target.href )
				{
					return;
				}

				var	column = $(this),
					url = column.data('link-column-url'),
					target = column.data('link-column-target'),
					link = window.location.hostname+window.location.pathname;

				if( 'undefined' === typeof url || 'string' !== typeof url )
				{
					return;
				}

				if( 'undefined' !== typeof target || '_blank' == target )
				{
//					in FF and other browsers this opens a new window and not only a new tab
//					window.open( url, '_blank', 'noopener noreferrer' );
					var a = document.createElement('a');
					a.href = url;
					a.target = '_blank';
					a.rel = 'noopener noreferrer';
					a.click();
					return false;
				}
				else
				{
					//	allow smoothscroll feature when on same page and hash exists - trigger only works for current page
					if( column.hasClass('av-cell-link') || column.hasClass('av-column-link') )
					{
						var reader = column.hasClass('av-cell-link') ? column.prev('a.av-screen-reader-only').first() : column.find('a.av-screen-reader-only').first();

						url = url.trim();
						if( ( 0 == url.indexOf("#") ) || ( ( url.indexOf( link ) >= 0 ) && ( url.indexOf("#") > 0 ) ) )
						{
							reader.trigger('click');

							//	fix a bug with tabsection not changeing tab
							if( 'undefined' == typeof target || '_blank' != target )
							{
								window.location.href = url;
							}

							return;
						}
					}

					window.location.href = url;
				}

				e.preventDefault();
				return;
			});
		});
	};


	// -------------------------------------------------------------------------------------------
	// HELPER FUNCTIONS
	// -------------------------------------------------------------------------------------------


	//waipoint script when something comes into viewport
	$.fn.avia_waypoints = function( options_passed )
	{
		if( ! $('html').is('.avia_transform') )
		{
			return;
		}

		var defaults = {
					offset: 'bottom-in-view',
					triggerOnce: true
				},
			options  = $.extend( {}, defaults, options_passed ),
			isMobile = $.avia_utilities.isMobile;

		return this.each( function()
		{
			var element = $(this),
				force_animate = element.hasClass( 'animate-all-devices' ),
				mobile_no_animations = $( 'body' ).hasClass( 'avia-mobile-no-animations' );

			setTimeout( function()
			{
				if( isMobile && mobile_no_animations && ! force_animate )
				{
					element.addClass( 'avia_start_animation' ).trigger('avia_start_animation');
				}
				else
				{
					element.waypoint( function( direction )
					{
						var current = $(this.element),
							parent = current.parents('.av-animation-delay-container').eq( 0 );

						if( parent.length )
						{
							current.addClass( 'avia_start_animation_when_active' ).trigger( 'avia_start_animation_when_active' );
						}

						if( ! parent.length || ( parent.length && parent.is( '.__av_init_open' ) ) || ( parent.length && parent.is( '.av-active-tab-content' ) ) )
						{
							current.addClass( 'avia_start_animation' ).trigger( 'avia_start_animation' );
						}
					}, options );
				}
			}, 100 );

		});
	};


	// window resize script
	var $event = $.event, $special, resizeTimeout;

	$special = $event.special.debouncedresize = {
		setup: function() {
			$( this ).on( "resize", $special.handler );
		},
		teardown: function() {
			$( this ).off( "resize", $special.handler );
		},
		handler: function( event, execAsap ) {
			// Save the context
			var context = this,
				args = arguments,
				dispatch = function() {
					// set correct event type
					event.type = "debouncedresize";
					$event.dispatch.apply( context, args );
				};

			if ( resizeTimeout ) {
				clearTimeout( resizeTimeout );
			}

			execAsap ?
				dispatch() :
				resizeTimeout = setTimeout( dispatch, $special.threshold );
		},
		threshold: 150
	};

})( jQuery );



/*utility functions*/


(function($)
{
	"use strict";

	$.avia_utilities = $.avia_utilities || {};

	/************************************************************************
	gloabl loading function
	*************************************************************************/
	$.avia_utilities.loading = function(attach_to, delay){

		var loader = {

			active: false,

			show: function()
			{
				if(loader.active === false)
				{
					loader.active = true;
					loader.loading_item.css({display:'block', opacity:0});
				}

				loader.loading_item.stop().animate({opacity:1});
			},

			hide: function()
			{
				if(typeof delay === 'undefined'){ delay = 600; }

				loader.loading_item.stop().delay( delay ).animate({opacity:0}, function()
				{
					loader.loading_item.css({display:'none'});
					loader.active = false;
				});
			},

			attach: function()
			{
				if(typeof attach_to === 'undefined'){ attach_to = 'body';}

				loader.loading_item = $('<div class="avia_loading_icon"><div class="av-siteloader"></div></div>').css({display:"none"}).appendTo(attach_to);
			}
		};

		loader.attach();
		return loader;
	};

	/************************************************************************
	gloabl play/pause visualizer function
	*************************************************************************/
	$.avia_utilities.playpause = function(attach_to, delay){

		var pp = {

			active: false,
			to1: "",
			to2: "",
			set: function(status)
			{
				pp.loading_item.removeClass('av-play av-pause');
				pp.to1 = setTimeout(function(){ pp.loading_item.addClass('av-' + status); },10);
				pp.to2 = setTimeout(function(){ pp.loading_item.removeClass('av-' + status); },1500);
			},

			attach: function()
			{
				if(typeof attach_to === 'undefined'){ attach_to = 'body';}

				pp.loading_item = $('<div class="avia_playpause_icon"></div>').css({display:"none"}).appendTo(attach_to);
			}
		};

		pp.attach();
		return pp;
	};



	/************************************************************************
	preload images, as soon as all are loaded trigger a special load ready event
	*************************************************************************/
	$.avia_utilities.preload = function(options_passed)
	{
		new $.AviaPreloader(options_passed);
	};

	$.AviaPreloader  =  function(options)
	{
	    this.win 		= $(window);
	    this.defaults	=
		{
			container:			'body',
			maxLoops:			10,
			trigger_single:		true,
			single_callback:	function(){},
			global_callback:	function(){}

		};
		this.options 	= $.extend({}, this.defaults, options);
		this.preload_images = 0;

		this.load_images();
	};

	$.AviaPreloader.prototype  =
	{
		load_images: function()
		{
			var _self = this;

			if(typeof _self.options.container === 'string'){ _self.options.container = $(_self.options.container); }

			_self.options.container.each(function()
			{
				var container		= $(this);

				container.images	= container.find('img');
				container.allImages	= container.images;

				_self.preload_images += container.images.length;
				setTimeout(function(){ _self.checkImage(container); }, 10);
			});
		},

		checkImage: function(container)
		{
			var _self = this;

			container.images.each(function()
			{
				if(this.complete === true)
				{
					container.images = container.images.not(this);
					_self.preload_images -= 1;
				}
			});

			if(container.images.length && _self.options.maxLoops >= 0)
			{
				_self.options.maxLoops-=1;
				setTimeout( function(){ _self.checkImage( container ); }, 500 );
			}
			else
			{
				_self.preload_images = _self.preload_images - container.images.length;
				_self.trigger_loaded(container);
			}
		},

		trigger_loaded: function(container)
		{
			var _self = this;

			if(_self.options.trigger_single !== false)
			{
				_self.win.trigger('avia_images_loaded_single', [container]);
				_self.options.single_callback.call(container);
			}

			if(_self.preload_images === 0)
			{
				_self.win.trigger('avia_images_loaded');
				_self.options.global_callback.call();
			}

		}
	};

	/************************************************************************
	CSS Easing transformation table
	*************************************************************************/
	/*
	Easing transform table from jquery.animate-enhanced plugin
	http://github.com/benbarnett/jQuery-Animate-Enhanced
	*/
	$.avia_utilities.css_easings = {
			linear:			'linear',
			swing:			'ease-in-out',
			bounce:			'cubic-bezier(0.0, 0.35, .5, 1.3)',
			easeInQuad:     'cubic-bezier(0.550, 0.085, 0.680, 0.530)' ,
			easeInCubic:    'cubic-bezier(0.550, 0.055, 0.675, 0.190)' ,
			easeInQuart:    'cubic-bezier(0.895, 0.030, 0.685, 0.220)' ,
			easeInQuint:    'cubic-bezier(0.755, 0.050, 0.855, 0.060)' ,
			easeInSine:     'cubic-bezier(0.470, 0.000, 0.745, 0.715)' ,
			easeInExpo:     'cubic-bezier(0.950, 0.050, 0.795, 0.035)' ,
			easeInCirc:     'cubic-bezier(0.600, 0.040, 0.980, 0.335)' ,
			easeInBack:     'cubic-bezier(0.600, -0.280, 0.735, 0.04)' ,
			easeOutQuad:    'cubic-bezier(0.250, 0.460, 0.450, 0.940)' ,
			easeOutCubic:   'cubic-bezier(0.215, 0.610, 0.355, 1.000)' ,
			easeOutQuart:   'cubic-bezier(0.165, 0.840, 0.440, 1.000)' ,
			easeOutQuint:   'cubic-bezier(0.230, 1.000, 0.320, 1.000)' ,
			easeOutSine:    'cubic-bezier(0.390, 0.575, 0.565, 1.000)' ,
			easeOutExpo:    'cubic-bezier(0.190, 1.000, 0.220, 1.000)' ,
			easeOutCirc:    'cubic-bezier(0.075, 0.820, 0.165, 1.000)' ,
			easeOutBack:    'cubic-bezier(0.175, 0.885, 0.320, 1.275)' ,
			easeInOutQuad:  'cubic-bezier(0.455, 0.030, 0.515, 0.955)' ,
			easeInOutCubic: 'cubic-bezier(0.645, 0.045, 0.355, 1.000)' ,
			easeInOutQuart: 'cubic-bezier(0.770, 0.000, 0.175, 1.000)' ,
			easeInOutQuint: 'cubic-bezier(0.860, 0.000, 0.070, 1.000)' ,
			easeInOutSine:  'cubic-bezier(0.445, 0.050, 0.550, 0.950)' ,
			easeInOutExpo:  'cubic-bezier(1.000, 0.000, 0.000, 1.000)' ,
			easeInOutCirc:  'cubic-bezier(0.785, 0.135, 0.150, 0.860)' ,
			easeInOutBack:  'cubic-bezier(0.680, -0.550, 0.265, 1.55)' ,
			easeInOutBounce:'cubic-bezier(0.580, -0.365, 0.490, 1.365)',
			easeOutBounce:	'cubic-bezier(0.760, 0.085, 0.490, 1.365)'
		};

	/************************************************************************
	check if a css feature is supported and save it to the supported array
	*************************************************************************/
	$.avia_utilities.supported	= {};
	$.avia_utilities.supports	= (function()
	{
		var div		= document.createElement('div'),
			vendors	= ['Khtml', 'Ms','Moz','Webkit'];  // vendors	= ['Khtml', 'Ms','Moz','Webkit','O'];

		return function(prop, vendor_overwrite)
		{
			if ( div.style[prop] !== undefined  ) { return ""; }
			if (vendor_overwrite !== undefined) { vendors = vendor_overwrite; }

			prop = prop.replace(/^[a-z]/, function(val)
			{
				return val.toUpperCase();
			});

			var len	= vendors.length;
			while(len--)
			{
				if ( div.style[vendors[len] + prop] !== undefined )
				{
					return "-" + vendors[len].toLowerCase() + "-";
				}
			}

			return false;
		};

	}());

	/************************************************************************
	animation function
	*************************************************************************/
	$.fn.avia_animate = function(prop, speed, easing, callback)
	{
		if(typeof speed === 'function') {callback = speed; speed = false; }
		if(typeof easing === 'function'){callback = easing; easing = false;}
		if(typeof speed === 'string'){easing = speed; speed = false;}

		if(callback === undefined || callback === false){ callback = function(){}; }
		if(easing === undefined || easing === false)	{ easing = 'easeInQuad'; }
		if(speed === undefined || speed === false)		{ speed = 400; }

		if($.avia_utilities.supported.transition === undefined)
		{
			$.avia_utilities.supported.transition = $.avia_utilities.supports('transition');
		}



		if($.avia_utilities.supported.transition !== false )
		{
			var prefix		= $.avia_utilities.supported.transition + 'transition',
				cssRule		= {},
				cssProp		= {},
				thisStyle	= document.body.style,
				end			= (thisStyle.WebkitTransition !== undefined) ? 'webkitTransitionEnd' : (thisStyle.OTransition !== undefined) ? 'oTransitionEnd' : 'transitionend';

			//translate easing into css easing
			easing = $.avia_utilities.css_easings[easing];

			//create css transformation rule
			cssRule[prefix]	=  'all '+(speed/1000)+'s '+easing;
			//add namespace to the transition end trigger
			end = end + ".avia_animate";

			//since jquery 1.10 the items passed need to be {} and not [] so make sure they are converted properly
			for (var rule in prop)
			{
				if (prop.hasOwnProperty(rule)) { cssProp[rule] = prop[rule]; }
			}
			prop = cssProp;



			this.each(function()
			{
				var element	= $(this), css_difference = false, rule, current_css;

				for (rule in prop)
				{
					if (prop.hasOwnProperty(rule))
					{
						current_css = element.css(rule);

						if(prop[rule] != current_css && prop[rule] != current_css.replace(/px|%/g,""))
						{
							css_difference = true;
							break;
						}
					}
				}

				if(css_difference)
				{
					//if no transform property is set set a 3d translate to enable hardware acceleration
					if(!($.avia_utilities.supported.transition+"transform" in prop))
					{
						prop[$.avia_utilities.supported.transition+"transform"] = "translateZ(0)";
					}

					var endTriggered = false;

					element.on(end,  function(event)
					{
						if(event.target != event.currentTarget) return false;

						if(endTriggered == true) return false;
						endTriggered = true;

						cssRule[prefix] = "none";

						element.off(end);
						element.css(cssRule);
						setTimeout(function(){ callback.call(element); });
					});


					//desktop safari fallback if we are in another tab to trigger the end event
					setTimeout(function(){
						if(!endTriggered && !avia_is_mobile && $('html').is('.avia-safari') ) {
							element.trigger(end);
							$.avia_utilities.log('Safari Fallback '+end+' trigger');
						}
					}, speed + 100);

					setTimeout(function(){ element.css(cssRule);},10);
					setTimeout(function(){ element.css(prop);	},20);
				}
				else
				{
					setTimeout(function(){ callback.call(element); });
				}

			});
		}
		else // if css animation is not available use default JS animation
		{
			this.animate(prop, speed, easing, callback);
		}

		return this;
	};

})( jQuery );



// -------------------------------------------------------------------------------------------
// keyboard controls
// -------------------------------------------------------------------------------------------

(function($)
{
	"use strict";

	/************************************************************************
	keyboard arrow nav
	*************************************************************************/
	$.fn.avia_keyboard_controls = function(options_passed)
	{
		var defaults	=
		{
			37: '.prev-slide',	// prev
			39: '.next-slide'	// next
		},

		methods		= {

			mousebind: function(slider)
			{
				slider.on('mouseenter', function(){
					slider.mouseover	= true;  })
				.on('mouseleave', function(){
					slider.mouseover	= false; }
				);
			},

			keybind: function(slider)
			{
				$(document).on('keydown', function(e)
				{
					if(slider.mouseover && typeof slider.options[e.keyCode] !== 'undefined')
					{
						var item;

						if(typeof slider.options[e.keyCode] === 'string')
						{
							item = slider.find(slider.options[e.keyCode]);
						}
						else
						{
							item = slider.options[e.keyCode];
						}

						if(item.length)
						{
							item.trigger('click', ['keypress']);
							return false;
						}
					}
				});
			}
		};


		return this.each(function()
		{
			var slider			= $(this);
			slider.options		= $.extend({}, defaults, options_passed);
			slider.mouseover	= false;

			methods.mousebind(slider);
			methods.keybind(slider);

		});
	};


	/************************************************************************
	swipe nav
	*************************************************************************/
	$.fn.avia_swipe_trigger = function( passed_options )
	{
		var win = $(window),
			isMobile = $.avia_utilities.isMobile,
			isTouchDevice = $.avia_utilities.isTouchDevice,
			defaults =
			{
				prev: '.prev-slide',
				next: '.next-slide',
				event: {
					prev: 'click',
					next: 'click'
				}
			},

			methods =
			{
				activate_touch_control: function(slider)
				{
					var i,
						differenceX,
						differenceY;

					slider.touchPos = {};
					slider.hasMoved = false;

					slider.on( 'touchstart', function(event)
					{
						slider.touchPos.X = event.originalEvent.touches[0].clientX;
						slider.touchPos.Y = event.originalEvent.touches[0].clientY;
					});

					slider.on( 'touchend', function(event)
					{
						slider.touchPos = {};

						if( slider.hasMoved )
						{
							event.preventDefault();
						}

						slider.hasMoved = false;
					});

					slider.on( 'touchmove', function(event)
					{
						if( ! slider.touchPos.X )
						{
							slider.touchPos.X = event.originalEvent.touches[0].clientX;
							slider.touchPos.Y = event.originalEvent.touches[0].clientY;
						}
						else
						{
							differenceX = event.originalEvent.touches[0].clientX - slider.touchPos.X;
							differenceY = event.originalEvent.touches[0].clientY - slider.touchPos.Y;

							//check if user is scrolling the window or moving the slider
							if( Math.abs( differenceX ) > Math.abs( differenceY ) )
							{
								event.preventDefault();

								if( slider.touchPos !== event.originalEvent.touches[0].clientX )
								{
									if( Math.abs(differenceX) > 50 )
									{
										i = differenceX > 0 ? 'prev' : 'next';

										if( typeof slider.options[i] === 'string' )
										{
											slider.find( slider.options[i] ).trigger( slider.options.event[i], ['swipe'] );
										}
										else
										{
											slider.options[i].trigger( slider.options.event[i], ['swipe'] );
										}

										slider.hasMoved = true;
										slider.touchPos = {};
										return false;
									}
								}
							}
						}
					});

				}
			};

		return this.each( function()
		{
			if( isMobile || isTouchDevice )
			{
				var slider = $(this);
				slider.options = $.extend( {}, defaults, passed_options );
				methods.activate_touch_control( slider );
			}
		});
	};

}(jQuery));

/**
 * jQuery 3.x:  JQMIGRATE: easing function “jQuery.easing.swing” should use only first argument
 * https://stackoverflow.com/questions/39355019/jqmigrate-easing-function-jquery-easing-swing-should-use-only-first-argument
 * https://github.com/gdsmith/jquery.easing/blob/master/jquery.easing.js
 *
 * @since 4.8.4
 * @param {jQuery} $
 */
(function($)
{
	// Preserve the original jQuery "swing" easing as "jswing"
	if (typeof $.easing !== 'undefined') {
		$.easing['jswing'] = $.easing['swing'];
	}

	var pow = Math.pow,
		sqrt = Math.sqrt,
		sin = Math.sin,
		cos = Math.cos,
		PI = Math.PI,
		c1 = 1.70158,
		c2 = c1 * 1.525,
		c3 = c1 + 1,
		c4 = ( 2 * PI ) / 3,
		c5 = ( 2 * PI ) / 4.5;

	// x is the fraction of animation progress, in the range 0..1
	function bounceOut(x) {
		var n1 = 7.5625,
			d1 = 2.75;
		if ( x < 1/d1 ) {
			return n1*x*x;
		} else if ( x < 2/d1 ) {
			return n1*(x-=(1.5/d1))*x + .75;
		} else if ( x < 2.5/d1 ) {
			return n1*(x-=(2.25/d1))*x + .9375;
		} else {
			return n1*(x-=(2.625/d1))*x + .984375;
		}
	}

	$.extend( $.easing,
	{
		def: 'easeOutQuad',
		swing: function (x) {
			return $.easing[$.easing.def](x);
		},
		easeInQuad: function (x) {
			return x * x;
		},
		easeOutQuad: function (x) {
			return 1 - ( 1 - x ) * ( 1 - x );
		},
		easeInOutQuad: function (x) {
			return x < 0.5 ?
				2 * x * x :
				1 - pow( -2 * x + 2, 2 ) / 2;
		},
		easeInCubic: function (x) {
			return x * x * x;
		},
		easeOutCubic: function (x) {
			return 1 - pow( 1 - x, 3 );
		},
		easeInOutCubic: function (x) {
			return x < 0.5 ?
				4 * x * x * x :
				1 - pow( -2 * x + 2, 3 ) / 2;
		},
		easeInQuart: function (x) {
			return x * x * x * x;
		},
		easeOutQuart: function (x) {
			return 1 - pow( 1 - x, 4 );
		},
		easeInOutQuart: function (x) {
			return x < 0.5 ?
				8 * x * x * x * x :
				1 - pow( -2 * x + 2, 4 ) / 2;
		},
		easeInQuint: function (x) {
			return x * x * x * x * x;
		},
		easeOutQuint: function (x) {
			return 1 - pow( 1 - x, 5 );
		},
		easeInOutQuint: function (x) {
			return x < 0.5 ?
				16 * x * x * x * x * x :
				1 - pow( -2 * x + 2, 5 ) / 2;
		},
		easeInSine: function (x) {
			return 1 - cos( x * PI/2 );
		},
		easeOutSine: function (x) {
			return sin( x * PI/2 );
		},
		easeInOutSine: function (x) {
			return -( cos( PI * x ) - 1 ) / 2;
		},
		easeInExpo: function (x) {
			return x === 0 ? 0 : pow( 2, 10 * x - 10 );
		},
		easeOutExpo: function (x) {
			return x === 1 ? 1 : 1 - pow( 2, -10 * x );
		},
		easeInOutExpo: function (x) {
			return x === 0 ? 0 : x === 1 ? 1 : x < 0.5 ?
				pow( 2, 20 * x - 10 ) / 2 :
				( 2 - pow( 2, -20 * x + 10 ) ) / 2;
		},
		easeInCirc: function (x) {
			return 1 - sqrt( 1 - pow( x, 2 ) );
		},
		easeOutCirc: function (x) {
			return sqrt( 1 - pow( x - 1, 2 ) );
		},
		easeInOutCirc: function (x) {
			return x < 0.5 ?
				( 1 - sqrt( 1 - pow( 2 * x, 2 ) ) ) / 2 :
				( sqrt( 1 - pow( -2 * x + 2, 2 ) ) + 1 ) / 2;
		},
		easeInElastic: function (x) {
			return x === 0 ? 0 : x === 1 ? 1 :
				-pow( 2, 10 * x - 10 ) * sin( ( x * 10 - 10.75 ) * c4 );
		},
		easeOutElastic: function (x) {
			return x === 0 ? 0 : x === 1 ? 1 :
				pow( 2, -10 * x ) * sin( ( x * 10 - 0.75 ) * c4 ) + 1;
		},
		easeInOutElastic: function (x) {
			return x === 0 ? 0 : x === 1 ? 1 : x < 0.5 ?
				-( pow( 2, 20 * x - 10 ) * sin( ( 20 * x - 11.125 ) * c5 )) / 2 :
				pow( 2, -20 * x + 10 ) * sin( ( 20 * x - 11.125 ) * c5 ) / 2 + 1;
		},
		easeInBack: function (x) {
			return c3 * x * x * x - c1 * x * x;
		},
		easeOutBack: function (x) {
			return 1 + c3 * pow( x - 1, 3 ) + c1 * pow( x - 1, 2 );
		},
		easeInOutBack: function (x) {
			return x < 0.5 ?
				( pow( 2 * x, 2 ) * ( ( c2 + 1 ) * 2 * x - c2 ) ) / 2 :
				( pow( 2 * x - 2, 2 ) *( ( c2 + 1 ) * ( x * 2 - 2 ) + c2 ) + 2 ) / 2;
		},
		easeInBounce: function (x) {
			return 1 - bounceOut( 1 - x );
		},
		easeOutBounce: bounceOut,
		easeInOutBounce: function (x) {
			return x < 0.5 ?
				( 1 - bounceOut( 1 - 2 * x ) ) / 2 :
				( 1 + bounceOut( 2 * x - 1 ) ) / 2;
		}
	});

}(jQuery));
