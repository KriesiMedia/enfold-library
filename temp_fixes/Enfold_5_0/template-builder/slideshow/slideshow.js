/* ======================================================================================================================================================
Avia Slideshow
======================================================================================================================================================
*/

(function($)
{
    "use strict";

	$.AviaSlider = function( options, slider )
	{
		var self = this;

		this.$win = $( window );
		this.$slider = $( slider );

		this.isMobile = $.avia_utilities.isMobile;
		this.isTouchDevice = $.avia_utilities.isTouchDevice,

		this._prepareSlides(options);

		//default preload images then init slideshow
	    $.avia_utilities.preload({
			container: this.$slider,
			single_callback: function(){ self._init( options ); }
		});
	};

	$.AviaSlider.defaults =
	{
		//interval between autorotation switches
		interval: 5,

		//start autorotation active or not
		autoplay: false,

		//stop on last slide - shortcut for stopinfiniteloop - kept for backwards comp. With 5.0 added extended support also for manual rotation
		autoplay_stopper: false,

		// @since 5.0		'endless' | 'once'
		loop_autoplay: 'once',

		// @since 5.0		'manual-endless' | 'manual-once'
		loop_manual: 'manual-endless',

		//set if the loop will stop at the last/first slide or if the slides will loop infinite
		//set to false for infinite loop, "last" to stop at the last slide or "first" to stop at the first slide
		stopinfiniteloop: false,

		//	true to ignore all user navigation
		noNavigation: false,

		//fade or slide animation
		animation: 'slide',

		//transition speed when switching slide
		transitionSpeed: 900,

		//easing method for the transition
		easing: 'easeInOutQuart',

		//slide wrapper
		wrapElement: '>ul',

		//slide element
		slideElement: '>li',

		//pause if mouse cursor is above item
		hoverpause: false,

		//attach images as background
		bg_slider: false,

		//delay of milliseconds to wait before showing the next slide
		show_slide_delay: 0,

		//if slider animation is set to "fade" the fullfade property sets the crossfade behaviour
		fullfade: false,

		//set to true to keep padding (e.g. for featured image slideshow, carousel slideshow
		keep_padding: false,

        //enable carousel mode with multiple visible slides
		carousel: 'no',

		// how many slides are displayed at once in the carousel
        carouselSlidesToShow: 3,

		// TODO: how many slides are scrolled in the carousel
		carouselSlidesToScroll: 1,

		// responsive carousel
		carouselResponsive: new Array()

	};

  	$.AviaSlider.prototype =
    {
    	_init: function( options )
    	{
            // set slider options
			this.options = this._setOptions( options );

			//slidewrap
			this.$sliderUl = this.$slider.find(this.options.wrapElement);

			// slide elements
			this.$slides = this.$sliderUl.find(this.options.slideElement);

			//	slide navigaton arrows wrap
			this.slide_arrows = this.$slider.find( '.avia-slideshow-arrows' );

			// goto dots
			this.gotoButtons = this.$slider.find( '.avia-slideshow-dots a' );

			//perma caption
			this.permaCaption = this.$slider.find( '>.av-slideshow-caption' );

			// slide count
			this.itemsCount = this.$slides.length;

			// current image index
			this.current = 0;

			// current carousel index
			this.currentCarousel = 0;

			// carousel slide width
			this.slideWidthCarousel = '240';

			//loop count
			this.loopCount = 0;

			// control if the slicebox is animating
			this.isAnimating = false;

			// css browser prefix like -webkit-, -moz-
			this.browserPrefix = $.avia_utilities.supports('transition');

			// css3 animation?
			this.cssActive = this.browserPrefix !== false ? true : false;

			// css3D animation?
			this.css3DActive = document.documentElement.className.indexOf('avia_transform3d') !== -1 ? true : false;

			//if we have a bg slider no images were preloaded yet. in that case start preloading and attaching images
			if( this.options.bg_slider == true )
			{
				//create array that holds all image urls to preload
				this.imageUrls = [];

				//create a preloader icon to indicate loading
				this.loader = $.avia_utilities.loading( this.$slider );

				//preload the images ony by one
				this._bgPreloadImages();

			}
			else //if it was a default slider all images are already loaded and we can start showing the slider
			{
				//kickoff the slider: bind functions, show first slide, if active start the autorotation timer
				this._kickOff();
			}

			if( this.options.carousel === 'yes' )
			{
				this.options.animation = 'carouselslide';
			}
    	},

    	//set the slider options by first merging the default options and the passed options, then checking the slider element if any data attributes overwrite the option set
    	_setOptions: function( options )
		{
			var jsonOptions = this.$slider.data( 'slideshow-options' );

			//	since 5.0 - render options via json to clean up html - $.data returns parsed object
			if( 'object' == typeof jsonOptions )
			{
				var newOptions = $.extend( {}, $.AviaSlider.defaults, options, jsonOptions );

				if( 'undefined' != typeof newOptions.transition_speed )
				{
					newOptions.transitionSpeed = newOptions.transition_speed;
				}

				return newOptions;
			}

			var newOptions = $.extend( true, {}, $.AviaSlider.defaults, options ),
				htmlData = this.$slider.data();

			//overwrite passed option set with any data properties on the html element
			for( var i in htmlData )
			{
				//	data attribute is transformed to lower case, but js is case sensitive - transform key
				var key = ( 'transition_speed' != i ) ? i :'transitionSpeed';

				if( typeof htmlData[ i ] === "string" || typeof htmlData[ i ] === "number" || typeof htmlData[ i ] === "boolean" )
				{
					newOptions[ key ] = htmlData[ i ];
				}

				if( 'undefined' != typeof newOptions.autoplay_stopper && newOptions.autoplay_stopper == 1 )
				{
					newOptions.autoplay_stopper = true;
				}
			}

			return newOptions;
		},

		_prepareSlides: function(options)
		{
			//if its a mobile device find all video slides that need to be altered
			if( this.isMobile )
			{
				var alter = this.$slider.find('.av-mobile-fallback-image');
				alter.each(function()
				{
					var current  = $(this).removeClass('av-video-slide').data({'avia_video_events': true, 'video-ratio':0}),
						fallback = current.data('mobile-img'),
						fallback_link = current.data('fallback-link'),
						appendTo = current.find('.avia-slide-wrap');

					current.find('.av-click-overlay, .mejs-mediaelement, .mejs-container').remove();

					if(!fallback)
					{
						$('<p class="av-fallback-message"><span>Please set a mobile device fallback image for this video in your wordpress backend</span></p>').appendTo(appendTo);
					}

					if(options && options.bg_slider)
					{
						current.data('img-url', fallback);

						//if we got a fallback link we need to either replace the default link on mobile devices, or if there is no default link change the wrapping <div> to an <a>
						if(fallback_link != "")
						{
							if(appendTo.is('a'))
							{
								appendTo.attr('href', fallback_link);
							}
							else
							{
								appendTo.find('a').remove();
								appendTo.replaceWith(function(){
									var cur_slide = $(this);
								    return $("<a>").attr({'data-rel': cur_slide.data('rel'), 'class': cur_slide.attr('class'), 'href': fallback_link} ).append( $(this).contents() );
								});

								appendTo = current.find('.avia-slide-wrap');
							}

							if($.fn.avia_activate_lightbox)
							{
								current.parents('#main').avia_activate_lightbox();
							}
						}
					}
					else
					{
						var image = '<img src="'+fallback+'" alt="" title="" />';
						var lightbox = false;

						if( 'string' == typeof fallback_link && fallback_link.trim() != '' )
						{
							if( appendTo.is('a') )
							{
								appendTo.attr('href', fallback_link);
							}
							else
							{
								var rel = fallback_link.match(/\.(jpg|jpeg|gif|png)$/i) != null ? ' rel="lightbox" ' : '';
								image = '<a href="' + fallback_link.trim() + '"' + rel + '>' + image + '</a>';
							}
							lightbox = true;
						}

						current.find('.avia-slide-wrap').append(image);

						if( lightbox && $.fn.avia_activate_lightbox)
						{
							current.parents('#main').avia_activate_lightbox();
						}
					}

				});
			}

		},

		//start preloading the background images
		_bgPreloadImages: function(callback)
    	{
    		this._getImageURLS();

    		this._preloadSingle(0, function()
    		{
    			this._kickOff();
				this._preloadNext(1);
    		});
    	},

    	//if we are using a background image slider, fetch the images from a data attribute and preload them one by one
    	_getImageURLS: function()
    	{
    		var _self = this;

    		//collect url strings of the images to preload
			this.$slides.each( function(i)
			{
				_self.imageUrls[i] = [];
				_self.imageUrls[i]['url'] = $(this).data("img-url");

				//if no image is passed we can set the slide to loaded
				if(typeof _self.imageUrls[i]['url'] == 'string')
				{
					_self.imageUrls[i]['status'] = false;
				}
				else
				{
					_self.imageUrls[i]['status'] = true;
				}
			});
    	},

    	_preloadSingle: function(key, callback)
		{
			var _self = this,
				objImage = new Image();

			if( typeof _self.imageUrls[key]['url'] == 'string' )
			{
				$(objImage).on('load error', function()
				{
					_self.imageUrls[key]['status'] = true;
					_self.$slides.eq(key).css('background-image','url(' + _self.imageUrls[key]['url'] + ')');

					if( typeof callback == 'function' )
					{
						callback.apply( _self, [objImage, key] );
					}
				});

				if(_self.imageUrls[key]['url'] != "")
				{
					objImage.src = _self.imageUrls[key]['url'];
				}
				else
				{
					$(objImage).trigger('error');
				}
			}
			else
			{
				if( typeof callback == 'function' )
				{
					callback.apply( _self, [objImage, key] );
				}
			}
		},

		_preloadNext: function(key)
		{
			if(typeof this.imageUrls[key] != "undefined")
    		{
				this._preloadSingle(key, function()
	    		{
					this._preloadNext(key + 1);
	    		});
    		}
		},


    	//bind click events of slide controlls to the public functions
    	_bindEvents: function()
    	{
    		var self = this,
    			win  = $( window );

    		this.$slider.on( 'click', '.next-slide', this.next.bind( this ) );
    		this.$slider.on( 'click', '.prev-slide', this.previous.bind( this ) );
    		this.$slider.on( 'click', '.goto-slide', this.go2.bind( this ) );

    		if( this.options.hoverpause )
    		{
    			this.$slider.on( 'mouseenter', this.pause.bind( this ) );
    			this.$slider.on( 'mouseleave', this.resume.bind( this ) );
    		}

			//	activate permanent caption link of image
			if( this.permaCaption.length )
			{
				this.permaCaption.on( 'click', this._routePermaCaptionClick );
				this.$slider.on( 'avia_slider_first_slide avia_slider_last_slide avia_slider_navigate_slide', this._setPermaCaptionPointer.bind( this ) );
			}

			if( this.options.stopinfiniteloop && this.options.autoplay )
			{
				if( this.options.stopinfiniteloop == 'last' )
				{
					this.$slider.on( 'avia_slider_last_slide', this._stopSlideshow.bind( this ) );
				}
				else if( this.options.stopinfiniteloop == 'first' )
				{
					this.$slider.on( 'avia_slider_first_slide', this._stopSlideshow.bind( this ) );
				}
			}

			if( this.options.carousel === 'yes' )
			{
				// recalculate carousel dimensions on viewport size change
				// use on desktop only, debouncedresize fires on scroll on mobile
				if( ! this.isMobile )
				{
					win.on( 'debouncedresize', this._buildCarousel.bind( this ) );
				}
			}
			else
			{
                win.on( 'debouncedresize.aviaSlider', this._setSize.bind( this ) );
			}

			if( ! this.options.noNavigation )
			{
				//if its a desktop browser add arrow navigation, otherwise add touch nav (also for touch devices)
				if( ! this.isMobile )
				{
					this.$slider.avia_keyboard_controls();
				}

				if( this.isMobile || this.isTouchDevice )
				{
					this.$slider.avia_swipe_trigger();
				}
			}

			self._attach_video_events();
    	},

    	//kickoff the slider by binding all functions to slides and buttons, show the first slide and start autoplay
    	_kickOff: function()
    	{
    		var self = this,
    			first_slide = self.$slides.eq(0),
    			video = first_slide.data('video-ratio');

    		// bind events to to the controll buttons
			self._bindEvents();
			self._set_slide_arrows_visibility();

    		this.$slider.removeClass('av-default-height-applied');

    		//show the first slide. if its a video set the correct size, otherwise make sure to remove the % padding
    		if( video )
    		{
    			self._setSize( true );
    		}
    		else
    		{
	    		if( this.options.keep_padding != true )
	    		{
    				self.$sliderUl.css('padding',0);
					self.$win.trigger('av-height-change');
				}
    		}

    		self._setCenter();

    		if( this.options.carousel === 'no' )
			{
				first_slide.addClass( 'next-active-slide' );
                first_slide.css( {visibility:'visible', opacity:0} ).avia_animate( {opacity:1}, function()
                {
                    var current = $(this).addClass( 'active-slide' );

                    if( self.permaCaption.length )
                    {
                        self.permaCaption.addClass( 'active-slide' );
                    }
                });
			}

			self.$slider.trigger( 'avia_slider_first_slide' );


    		// start autoplay if active
			if( self.options.autoplay )
			{
				self._startSlideshow();
			}

			// prepare carousel if active
			if( self.options.carousel === 'yes' )
			{
				self._buildCarousel();
			}

			self.$slider.trigger( '_kickOff' );
    	},

		_set_slide_arrows_visibility: function()
		{
			//	special use case - hardcoded as only used in timeline
			if( this.options.carousel == 'yes' )
			{
				if( 0 == this.currentCarousel )
				{
					this.slide_arrows.removeClass( 'av-visible-prev' );
					this.slide_arrows.addClass( 'av-visible-next' );
				}
				else if( this.currentCarousel + this.options.carouselSlidesToShow >= this.itemsCount )
				{
					this.slide_arrows.addClass( 'av-visible-prev' );
					this.slide_arrows.removeClass( 'av-visible-next' );
				}
				else
				{
					this.slide_arrows.addClass( 'av-visible-prev' );
					this.slide_arrows.addClass( 'av-visible-next' );
				}

				return;
			}

			if( 'endless' == this.options.loop_autoplay || 'manual-endless' == this.options.loop_manual )
			{
				this.slide_arrows.addClass( 'av-visible-prev' );
				this.slide_arrows.addClass( 'av-visible-next' );
			}
			else if( 0 == this.current )
			{
				this.slide_arrows.removeClass( 'av-visible-prev' );
				this.slide_arrows.addClass( 'av-visible-next' );
			}
			else if( this.current + 1 >= this.itemsCount )
			{
				this.slide_arrows.addClass( 'av-visible-prev' );
				this.slide_arrows.removeClass( 'av-visible-next' );
			}
			else
			{
				this.slide_arrows.addClass( 'av-visible-prev' );
				this.slide_arrows.addClass( 'av-visible-next' );
			}
		},

		_buildCarousel: function()
		{
            var self = this,
    		stageWidth = this.$slider.outerWidth(),
    		slidesWidth = parseInt(stageWidth / this.options.carouselSlidesToShow),
            windowWidth = window.innerWidth || $(window).width();

			// responsive carousel
			if( this.options.carouselResponsive &&
                this.options.carouselResponsive.length &&
                this.options.carouselResponsive !== null )
			{

				for( var breakpoint in this.options.carouselResponsive )
				{
					var breakpointValue = this.options.carouselResponsive[breakpoint]['breakpoint'];
					var newSlidesToShow = this.options.carouselResponsive[breakpoint]['settings']['carouselSlidesToShow'];

					if( breakpointValue >= windowWidth )
					{
                        slidesWidth = parseInt(stageWidth / newSlidesToShow);
                        this.options.carouselSlidesToShow = newSlidesToShow;
					}
				}
			}

            // set width and height for each slide
            this.slideWidthCarousel = slidesWidth;

            this.$slides.each(function(i)
			{
                $(this).width(slidesWidth);
            });

            // set width for the UL
			var slideTrackWidth = slidesWidth * this.itemsCount;
			this.$sliderUl.width(slideTrackWidth).css( 'transform', 'translateX(0px)' );

			// hide nav if not needed
			if( this.options.carouselSlidesToShow >= this.itemsCount )
			{
				this.$slider.find('.av-timeline-nav').hide();
			}
		},

    	//calculate which slide should be displayed next and call the executing transition function
    	_navigate: function( dir, pos )
		{
			if( this.isAnimating || this.itemsCount < 2 || ! this.$slider.is( ':visible' ) )
			{
				return false;
			}

			this.isAnimating = true;

			// current item's index
			this.prev = this.current;

			// if position is passed
			if( pos !== undefined )
			{
				this.current = pos;
				dir = this.current > this.prev ? 'next' : 'prev';
			}

            // if not check the boundaries
			else if( dir === 'next' )
			{
				this.current = this.current < this.itemsCount - 1 ? this.current + 1 : 0;

				if( this.current === 0 && this.options.autoplay_stopper && this.options.autoplay )
				{
					this.isAnimating = false;
					this.current = this.prev;
					this._stopSlideshow();
					return false;
				}

				//	check if we can rotate
				if( 0 === this.current )
				{
					if( 'endless' != this.options.loop_autoplay && 'manual-endless' != this.options.loop_manual )
					{
						this.isAnimating = false;
						this.current = this.prev;
						return false;
					}
				}
			}
			else if( dir === 'prev' )
			{
				this.current = this.current > 0 ? this.current - 1 : this.itemsCount - 1;

				//	check if we can rotate
				if( this.itemsCount - 1 === this.current )
				{
					if( 'endless' != this.options.loop_autoplay && 'manual-endless' != this.options.loop_manual )
					{
						this.isAnimating = false;
						this.current = this.prev;
						return false;
					}
				}
			}

			//set goto button
			this.gotoButtons.removeClass( 'active' ).eq( this.current ).addClass( 'active' );
			this._set_slide_arrows_visibility();

			//set slideshow size if carousel not in use
            if( this.options.carousel === 'no' )
			{
                this._setSize();
			}

            //if we are using a background slider make sure that the image is loaded. if not preload it, then show the slide
			if( this.options.bg_slider == true )
			{
				if( this.imageUrls[this.current]['status'] == true )
				{
					this['_' + this.options.animation].call(this, dir);
				}
				else
				{
					this.loader.show();
					this._preloadSingle(this.current, function()
    				{
    					this['_' + this.options.animation].call( this, dir );
    					this.loader.hide();
    				});
				}
			}
			else //no background loader -> images are already loaded
			{
				//call the executing function. for example _slide, or _fade. since the function call is absed on a var we can easily extend the slider with new animations
				this['_' + this.options.animation].call( this, dir );
			}

			if( this.current == 0 )
			{
				this.loopCount++;
				this.$slider.trigger( 'avia_slider_first_slide' );
			}
			else if( this.current == this.itemsCount - 1 )
			{
				this.$slider.trigger( 'avia_slider_last_slide' );
			}
			else
			{
				this.$slider.trigger( 'avia_slider_navigate_slide' );
			}
		},

		//if the next slide has a different height than the current change the slideshow height
		_setSize: function(instant)
		{
			//if images are attached as bg images the slider has a fixed height
			if( this.options.bg_slider == true )
			{
				return;
			}

			var self    		= this,
				slide 			= this.$slides.eq(this.current),
				img 			= slide.find('img'),
				current			= Math.floor(this.$sliderUl.height()),
				ratio			= slide.data('video-ratio'),
				setTo   		= ratio ? this.$sliderUl.width() / ratio : Math.floor(slide.height()),
				video_height 	= slide.data('video-height'), //forced video height %. needs to be set only once
				video_toppos 	= slide.data('video-toppos'); //forced video top position

			this.$sliderUl.height(current).css('padding',0); //make sure to set the slideheight to an actual value

			if(setTo != current)
			{
				if(instant == true)
				{
					this.$sliderUl.css({height:setTo});
					this.$win.trigger('av-height-change');
				}
				else
				{
					this.$sliderUl.avia_animate({height:setTo}, function()
					{
						self.$win.trigger('av-height-change');
					});
				}
			}

			this._setCenter();

			if(video_height && video_height!= "set")
			{
				slide.find('iframe, embed, video, object, .av_youtube_frame').css({height: video_height + '%', top: video_toppos + '%'});
				slide.data('video-height','set');
			}
		},

		_setCenter: function()
		{
			//if the image has a min width and is larger than the slider center it
			//positon img based on caption. right caption->left pos, left caption -> right pos
			var slide 		= this.$slides.eq(this.current),
				img 		= slide.find('img'),
				min_width 	= parseInt(img.css('min-width'),10),
				slide_width	= slide.width(),
				caption		= slide.find('.av-slideshow-caption'),
				css_left 	= ((slide_width - min_width) / 2);

			if(caption.length)
			{
				if(caption.is('.caption_left'))
				{
					css_left = ((slide_width - min_width) / 1.5);
				}
				else if(caption.is('.caption_right'))
				{
					css_left = ((slide_width - min_width) / 2.5);
				}
			}

			if(slide_width >= min_width)
			{
				css_left = 0;
			}

			img.css({left:css_left});
		},

		_carouselmove : function(){

        //    var offset = (this.options.carouselSlidesToScroll*this.slideWidthCarousel)*this.currentCarousel;
			var offset = this.slideWidthCarousel * this.currentCarousel;
            this.$sliderUl.css('transform', 'translateX(-' + offset + 'px)');
		},

		_carouselslide: function(dir)
		{
			console.log( '_carouselslide:', dir, this.currentCarousel );

    		if( dir === 'next' )
			{
				if (this.options.carouselSlidesToShow + this.currentCarousel < this.itemsCount)
				{
                    this.currentCarousel++;
                    this._carouselmove();
				}
			}
			else if( dir === 'prev' )
			{
    			if( this.currentCarousel > 0 )
				{
                    this.currentCarousel--;
                    this._carouselmove();
				}
			}

			this._set_slide_arrows_visibility();

            this.isAnimating = false;
		},

		_slide: function(dir)
		{
			var dynamic			= false, //todo: pass by option if a slider is dynamic
				modifier		= dynamic == true ? 2 : 1,
				sliderWidth		= this.$slider.width(),
				direction		= dir === 'next' ? -1 : 1,
				property  		= this.browserPrefix + 'transform',
				reset			= {},
				transition = {},
				transition2 = {},
				trans_val 		= ( sliderWidth * direction * -1 ),
				trans_val2 		= ( sliderWidth * direction ) / modifier;

			//do a css3 animation
			if(this.cssActive)
			{
				property = this.browserPrefix + 'transform';

				//do a translate 3d transformation if available, since it uses hardware acceleration
				if(this.css3DActive)
				{
					reset[property]  = "translate3d(" + trans_val + "px, 0, 0)";
					transition[property]  = "translate3d(" + trans_val2 + "px, 0, 0)";
					transition2[property] = "translate3d(0,0,0)";
				}
				else //do a 2d transform. still faster than a position "left" change
				{
					reset[property]  = "translate(" + trans_val + "px,0)";
					transition[property]  = "translate(" + trans_val2 + "px,0)";
					transition2[property] = "translate(0,0)";
				}
			}
			else
			{
				reset.left = trans_val;
				transition.left = trans_val2;
				transition2.left = 0;
			}

			if(dynamic)
			{
				transition['z-index']  = "1";
				transition2['z-index']  = "2";
			}

			this._slide_animate(reset, transition, transition2);
		},

		_slide_up: function(dir)
		{
			var dynamic			= true, //todo: pass by option if a slider is dynamic
				modifier		= dynamic == true ? 2 : 1,
				sliderHeight	= this.$slider.height(),
				direction		= dir === 'next' ? -1 : 1,
				property  		= this.browserPrefix + 'transform',
				reset			= {},
				transition = {},
				transition2 = {},
				trans_val 		= ( sliderHeight * direction * -1),
				trans_val2 		= ( sliderHeight * direction) / modifier;

			//do a css3 animation
			if(this.cssActive)
			{
				property  = this.browserPrefix + 'transform';

				//do a translate 3d transformation if available, since it uses hardware acceleration
				if(this.css3DActive)
				{
					reset[property]  = "translate3d( 0," + trans_val + "px, 0)";
					transition[property]  = "translate3d( 0," + trans_val2 + "px, 0)";
					transition2[property] = "translate3d(0,0,0)";
				}
				else //do a 2d transform. still faster than a position "left" change
				{
					reset[property]  = "translate( 0," + trans_val + "px)";
					transition[property]  = "translate( 0," + trans_val2 + "px)";
					transition2[property] = "translate(0,0)";					}
			}
			else
			{
				reset.top = trans_val;
				transition.top = trans_val2;
				transition2.top = 0;
			}

			if(dynamic)
			{
				transition['z-index']  = "1";
				transition2['z-index']  = "2";
			}
			this._slide_animate(reset, transition, transition2);
		},


		//slide animation: do a slide transition by css3 transform if possible. if not simply do a position left transition
		_slide_animate: function( reset , transition , transition2 )
		{
			var self			= this,
				displaySlide 	= this.$slides.eq(this.current),
				hideSlide		= this.$slides.eq(this.prev);

			hideSlide.trigger('pause');
			if( ! displaySlide.data('disableAutoplay') )
			{
				if(displaySlide.hasClass('av-video-lazyload') && !displaySlide.hasClass('av-video-lazyload-complete'))
				{
					displaySlide.find('.av-click-to-play-overlay').trigger('click');
				}
				else
				{
					displaySlide.trigger('play');
				}
			}

			displaySlide.css({visibility:'visible', zIndex:4, opacity:1, left:0, top:0});
			displaySlide.css(reset);

			hideSlide.avia_animate(transition, this.options.transitionSpeed, this.options.easing);

			var after_slide = function()
			{
				self.isAnimating = false;
				displaySlide.addClass( 'active-slide' );
				hideSlide.css( {visibility:'hidden'} ).removeClass( 'active-slide next-active-slide' );
				self.$slider.trigger( 'avia-transition-done' );
			};

			if( self.options.show_slide_delay > 0 )
			{
				setTimeout( function()
				{
					displaySlide.addClass( 'next-active-slide' );
					displaySlide.avia_animate( transition2, self.options.transitionSpeed, self.options.easing, after_slide );
				}, self.options.show_slide_delay );
			}
			else
			{
				displaySlide.addClass( 'next-active-slide' );
				displaySlide.avia_animate( transition2, self.options.transitionSpeed, self.options.easing, after_slide );
			}
		},

		//simple fade transition of the slideshow
		_fade: function()
		{
			var self			= this,
				displaySlide 	= this.$slides.eq(this.current),
				hideSlide		= this.$slides.eq(this.prev),
				properties		= {visibility:'visible', zIndex:3, opacity:0},
				fadeCallback 	= function()
				{
					self.isAnimating = false;
					displaySlide.addClass( 'active-slide' );
					hideSlide.css({visibility:'hidden', zIndex:2}).removeClass( 'active-slide next-active-slide' );
					self.$slider.trigger( 'avia-transition-done' );
				};

			hideSlide.trigger('pause');

			if( ! displaySlide.data('disableAutoplay') )
			{
				if(displaySlide.hasClass('av-video-lazyload') && ! displaySlide.hasClass('av-video-lazyload-complete'))
				{
					displaySlide.find('.av-click-to-play-overlay').trigger('click');
				}
				else
				{
					displaySlide.trigger('play');
				}
			}

			displaySlide.addClass( 'next-active-slide' );

			if( self.options.fullfade == true )
			{
				hideSlide.avia_animate( {opacity:0}, 200, 'linear', function()
				{
					displaySlide.css( properties ).avia_animate( {opacity:1}, self.options.transitionSpeed, 'linear', fadeCallback );
				});
			}
			else
			{
				if( self.current === 0 )
				{
					hideSlide.avia_animate( {opacity:0}, self.options.transitionSpeed/2, 'linear' );
					displaySlide.css(properties).avia_animate( {opacity:1}, self.options.transitionSpeed/2, 'linear', fadeCallback );
				}
				else
				{
					displaySlide.css(properties).avia_animate( {opacity:1}, self.options.transitionSpeed/2, 'linear', function()
					{
						hideSlide.avia_animate({opacity:0}, 200, 'linear', fadeCallback );
					});
				}
			}
		},

		/************************************************************************
		Video functions
		*************************************************************************/

		//bind events to the video that tell the slider to autorotate once a video has been played
		_attach_video_events: function()
		{
			var self = this,
				$html = $('html');

			self.$slides.each( function(i)
			{
				var currentSlide 	= $(this),
					caption			= currentSlide.find('.caption_fullwidth, .av-click-overlay'),
					mejs			= currentSlide.find('.mejs-mediaelement'),
					lazyload		= currentSlide.hasClass('av-video-lazyload') ? true : false;


				if( currentSlide.data('avia_video_events') != true )
				{
					currentSlide.data('avia_video_events', true);

					currentSlide.on('av-video-events-bound', { slide: currentSlide, wrap: mejs , iteration: i , self: self, lazyload: lazyload }, onReady);

					currentSlide.on('av-video-ended', { slide: currentSlide , self: self}, onFinish);

					currentSlide.on('av-video-play-executed', function(){ setTimeout( function(){  self.pause(); }, 100 ); } );

					caption.on('click', { slide: currentSlide }, toggle);

					// also if the player was loaded before the _bindEvents function was bound trigger it manually
					if( currentSlide.is('.av-video-events-bound') )
					{
						currentSlide.trigger('av-video-events-bound');
					}

					//if we are on the first slide and autoplay is enabled and lazy loading is enabled we need to simulate a click event to the lazy load container
					if( lazyload && i === 0 && ! currentSlide.data('disableAutoplay') )
					{
						currentSlide.find('.av-click-to-play-overlay').trigger('click');
					}
				}
			});


			//function that takes care of events once the video is loaded for the first time.
			//needs to take into account 2 different scenarios: normally embedded videos or lazyloaded videos that start on user interaction/autoplay
			function onReady( event )
			{
				//autostart for first slide
				if(event.data.iteration === 0)
				{
					event.data.wrap.css('opacity',0);

					if( ! event.data.self.isMobile && ! event.data.slide.data('disableAutoplay') )
					{
						event.data.slide.trigger('play');
					} 

					setTimeout( function(){ event.data.wrap.avia_animate({opacity:1}, 400); }, 50 );
				}
				else if( $html.is('.avia-msie') && ! event.data.slide.is('.av-video-service-html5') )
				{
					/*
					* Internet Explorer fires the ready event for external videos once they become visible
					* as oposed to other browsers which always fire immediately.
					*/
					if( ! event.data.slide.data('disableAutoplay') )
					{
						event.data.slide.trigger('play');
					}
				}


				//make sure that the html5 element does not play if autoply is enabled but its not the first slide.
				//the autoplay attribute on the video element might cause this
				if( event.data.slide.is('.av-video-service-html5') && event.data.iteration !== 0 )
				{
					event.data.slide.trigger('pause');
				}

				//make sure that lazyloaded videos always get started once a user clicks them
				if( event.data.lazyload)
				{
					event.data.slide.addClass('av-video-lazyload-complete');
					event.data.slide.trigger('play');
				}
			}

			function onFinish( event )
			{
				//if the video is not looped resume the slideshow
				if( ! event.data.slide.is('.av-single-slide') && ! event.data.slide.is('.av-loop-video') )
				{
					event.data.slide.trigger('reset');
					self._navigate( 'next' );
					self.resume();
				}

				//safari 8 workaround for self hosted videos which wont loop by default
				if( event.data.slide.is('.av-loop-video') && event.data.slide.is('.av-video-service-html5') )
				{
					if( $html.is('.avia-safari-8') )
					{
						setTimeout( function()
						{
							event.data.slide.trigger('play');
						}, 1 );
					}
				}
			}

			function toggle( event )
			{
				if( event.target.tagName != "A" )
				{
					event.data.slide.trigger('toggle');
				}
			}
		},


		/************************************************************************
		Slideshow control functions
		*************************************************************************/

		_timer: function(callback, delay, first)
		{
		    var self = this,
				start,
				remaining = delay;

			self.timerId = 0;

		    this.pause = function()
			{
		        window.clearTimeout(self.timerId);
		        remaining -= new Date() - start;
		    };

		    this.resume = function()
			{
		        start = new Date();
		        self.timerId = window.setTimeout(callback, remaining);
		    };

		    this.destroy = function()
		    {
		    	window.clearTimeout(self.timerId);
		    };

		    this.resume(true);
		},

		//start autorotation
		_startSlideshow: function()
		{
			var self = this;

			this.isPlaying = true;

			this.slideshow = new this._timer( function()
			{
				self._navigate( 'next' );

				if( self.options.autoplay )
				{
					self._startSlideshow();
				}
			}, ( this.options.interval * 1000 ) );
		},

		//stop autorotation
		_stopSlideshow: function()
		{
			if( this.options.autoplay )
			{
				this.slideshow.destroy();
				this.isPlaying = false;
				this.options.autoplay = false;
			}

			this.options.autoplay = false;
			this.options.loop_autoplay = 'once';
			this.$slider.removeClass( 'av-slideshow-autoplay' ).addClass( 'av-slideshow-manual' );
			this.$slider.removeClass( 'av-loop-endless' ).addClass( 'av-loop-once' );
		},

		//	check if we have a link for the image and set cursor
		_setPermaCaptionPointer: function( e )
		{
			if( ! this.permaCaption.length )
			{
				return;
			}

			var withLink = $( this.$slides[this.current] ).find( 'a' ).length;
			this.permaCaption.css( 'cursor', withLink ? 'pointer' : 'default' );
		},

		//	route perma caption to actual link of image, else allow bubble (e.g. for buttons)
		_routePermaCaptionClick: function( e )
		{
			var active_slide_link = $(this).siblings( '.avia-slideshow-inner' ).find( '>.active-slide a' );

			if( active_slide_link.length )
			{
				e.preventDefault();

				//	jQuery trigger does not work !!!
				active_slide_link[0].click();
			}
		},

		// public method: shows next image
		next: function(e)
		{
			e.preventDefault();
			this._stopSlideshow();
			this._navigate( 'next' );
		},

		// public method: shows previous image
		previous: function(e)
		{
			e.preventDefault();
			this._stopSlideshow();
			this._navigate( 'prev' );
		},

		// public method: goes to a specific image
		go2: function( pos )
		{
			//if we didnt pass a number directly lets asume someone clicked on a link that triggered the goto transition
			if(isNaN(pos))
			{
				//in that case prevent the default link behavior and set the slide number to the links hash
				pos.preventDefault();
				pos = pos.currentTarget.hash.replace('#','');
			}

			pos -= 1;

			if( pos === this.current || pos >= this.itemsCount || pos < 0 )
			{
				return false;
			}

			this._stopSlideshow();
			this._navigate( false, pos );

		},

		// public method: starts the slideshow
		// any call to next(), previous() or goto() will stop the slideshow autoplay
		play: function()
		{
			if( !this.isPlaying )
			{
				this.isPlaying = true;

				this._navigate( 'next' );
				this.options.autoplay = true;
				this._startSlideshow();
			}

		},

		// public methos: pauses the slideshow
		pause: function()
		{
			if( this.isPlaying )
			{
				this.slideshow.pause();
			}
		},

		// publiccmethos: resumes the slideshow
		resume: function()
		{
			if( this.isPlaying )
			{
				this.slideshow.resume();
			}
		},

		// public methos: destroys the instance
		destroy: function( callback )
		{
			this.slideshow.destroy( callback );
		}
    };

    //simple wrapper to call the slideshow. makes sure that the slide data is not applied twice
    $.fn.aviaSlider = function( options )
    {
    	return this.each(function()
    	{
    		var self = $.data( this, 'aviaSlider' );

    		if( ! self )
    		{
    			self = $.data( this, 'aviaSlider', new $.AviaSlider( options, this ) );
    		}
    	});
    };

})( jQuery );
