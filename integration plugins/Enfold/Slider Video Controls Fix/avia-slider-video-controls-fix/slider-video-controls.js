/* Fix Video Controls */

(function ($)
{
    "use strict";

    $.AviaSliderVideoControls = function (slider)
    {
        this.slideshow = slider;
        this.slider = this.slideshow.$slider;
        this.slides = null;
        this.active_slide = null;
        this.permacaption = null;
        this.click_overlay = null;
        this.slide_arrows = null;
        this.goto_buttons = null;

        this.heading_styles = {
            'font-weight': $('body h2').css('font-weight'),
            'font-family': $('body h2').css('font-family'),
        }

        this.body_styles = {
            'font-weight': $('body p').css('font-weight'),
            'font-family': $('body p').css('font-family'),
        }

        this.kickoff();
    }

    $.AviaSliderVideoControls.prototype = {
        kickoff()
        {
            var self = this;

            this.slider.on('_kickOff', function () 
            {
                self.slides = self.slideshow.$slides;
                self.active_slide = $(self.slides[self.slideshow.current]);
                self.permacaption = self.slideshow.permaCaption;
                self.click_overlay = self.active_slide.find('.av-click-overlay');
                self.slide_arrows = self.slideshow.slide_arrows;
                self.goto_buttons = self.slideshow.gotoButtons;

                self.set_stack_order();
                self.toggle_click_overlay();
                self.register_listeners();
            });
        },

        /**
         * Add event listener to the slider and toggle the visibility of av-click-overlay element when necessary
         *
         * @returns {void}
         */
        toggle_click_overlay()
        {
            var self = this;

            this.slider.on('mouseover', function (event)
            {
                var active_target = $(event.target),
                    active_slide = self.active_slide,
                    mejs_inner = active_slide.find('.mejs-inner'),
                    mejs_el = active_slide.find('.mejs-mediaelement'),
                    mejs_volume_slider = active_slide.find('.mejs-volume-slider');

                self.click_overlay = self.active_slide.find('.av-click-overlay');

                if(mejs_inner.length == 0)
                {
                    mejs_inner = mejs_el;
                }

                // permanent caption has to be appended to the current slide to get access to mejs controls
                if (self.permacaption.length && mejs_inner.find('>.av-slideshow-caption').length == 0) 
                {
                    var permanent_caption_title = self.permacaption.find('.avia-caption-title'),
                        permanent_caption_content = self.permacaption.find('.avia-caption-content'),
                        permanent_caption_buttons = self.permacaption.find('.avia-slideshow-button');

                    permanent_caption_title.add(permanent_caption_content).add(permanent_caption_buttons).css({
                        'visibility': 'visible',
                        'animation': 'none',
                        'transition': 'none',
                        'opacity': 1,
                    });

                    permanent_caption_title.css(self.heading_styles);
                    permanent_caption_content.add(permanent_caption_buttons).css(self.body_styles);

                    if (mejs_inner.length > 0) 
                    {
                        setTimeout(function ()
                        {
                            self.permacaption.appendTo(mejs_inner);
                            self.permacaption.css('z-index', 3);
                        }, 100);
                    }
                }

                // hide the click overlay when user is trying to access a button (caption buttons, controls)
                if (!self.is_overlay(active_target)) 
                {
                    if (self.click_overlay.is(':visible'))
                    {
                        self.click_overlay.hide();
                    }
                }
                else 
                {
                    if (!self.click_overlay.is(':visible') && !mejs_volume_slider.is(':visible'))
                    {
                        self.click_overlay.show();
                    }
                }
            });
        },

        set_stack_order()
        {
            var self = this;

            this.slider.find('.av-video-slide').each(function () 
            {
                var slide = $(this),
                    click_overlay = slide.find('.av-click-overlay'),
                    section_overlay = slide.find('.av-section-color-overlay'),
                    slide_caption = slide.find('.av-slideshow-caption'),
                    mejs = slide.find('.mejs-container'),
                    mejs_el = slide.find('.mejs-mediaelement'),
                    mejs_inner = mejs.find('.mejs-inner'),
                    mejs_controls = mejs.find('.mejs-controls');

                [mejs_el, section_overlay, click_overlay, slide_caption, mejs_controls].map(function (el, i) 
                {
                    var appendto = mejs_inner.length ? mejs_inner : mejs_el;
                    
                    if (el.length) 
                    {                   
                        if (el.is('av-slideshow-caption'))
                        {
                            var slide_caption_title = el.find('.avia-caption-title'),
                                slide_caption_content = el.find('.avia-caption-content');

                            slide_caption_title.css(self.heading_styles);
                            slide_caption_content.css(self.body_styles);
                        }

                        if (el.not('.mejs-mediaelement'))
                        {
                            el.prependTo(appendto);
                        }

                        el.css('z-index', i + 1);
                    }
                });
            });
        },

        register_listeners()
        {
            var self = this;

            if (this.slider.isMobile) return;

            this.slider.on('avia_slider_navigate_slide avia_slider_first_slide avia_slider_last_slide avia-transition-done', function () 
            {
                self.active_slide = $(self.slides[self.slideshow.current]);
                self.click_overlay = self.active_slide.find('.av-click-overlay');

                self.move_permacaption_back();
            });

            // bring permanent caption back to its original position
            this.slide_arrows.add(self.goto_buttons).on('click', function ()
            {
                self.move_permacaption_back();
            });

            this.permacaption.on('click', function (e)
            {
                var active_target = $(e.target);

                if(self.is_overlay(active_target))
                {
                    self.click_overlay = self.slider.find('.active-slide:not(".av-slideshow-caption")').find('.av-click-overlay');
                    self.click_overlay.trigger('click');
                }
            });
        },

        move_permacaption_back()
        {
            var self = this,
                slider = $(this.slider);

            if (slider.find('>.av-slideshow-caption').length) return;

            this.permacaption.css("z-index", '');
            this.permacaption.appendTo(self.slider);
        },

        is_overlay(target)
        {
            // hide the click overlay when user is trying to access a button (caption buttons, controls)
            if (target.is('.mejs-volume-button') ||
                target.is(":button") ||
                target.is('.avia-slideshow-button')) 
            {
                return false;
            }

            return true;
        }
    }
})(jQuery);

function avia_slider_video_controls_fix(avia_slider)
{
    jQuery(document).ready(function () 
    {
        new jQuery.AviaSliderVideoControls(avia_slider);
    });
}
