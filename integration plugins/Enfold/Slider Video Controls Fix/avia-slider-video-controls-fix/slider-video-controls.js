function avia_slider_video_controls_fix(avia_slider)
{
    var $ = jQuery,
        body_font = $('body').css('font-family'),
        heading_font = $('body h2').css('font-family');

    avia_slider.$slider.find('.av-video-slide').each(function () 
    {
        var current = $(this),
            current_wrap = current.find('.avia-slide-wrap'),
            slideshow = current.parents('.avia-slideshow'),
            slideshow_arrows = slideshow.find('.avia-slideshow-arrows a'),
            goto_buttons = slideshow.find('.avia-slideshow-dots a'),
            permanent_caption = slideshow.find('>.av-slideshow-caption'),
            permanent_caption_title = permanent_caption.find('.avia-caption-title'),
            permanent_caption_content = permanent_caption.find('.avia-caption-content'),
            click_overlay = current.find('.av-click-overlay'),
            section_overlay = current.find('.av-section-color-overlay'),
            slide_caption = current.find('.av-slideshow-caption'),
            slide_caption_title = slide_caption.find('.avia-caption-title'),
            slide_caption_content = slide_caption.find('.avia-caption-content'),
            mejs_el = current.find('.mejs-mediaelement'),
            mejs = current.find('.mejs-container'),
            mejs_inner = current.find('.mejs-inner'),
            mejs_controls = current.find('.mejs-controls'),
            mejs_volume_slider = current.find('.mejs-volume-slider');

        var is_video_slide = current.is('.av-video-slide'),
            is_mejs = mejs_el.length > 0;

        // check if there is a mejs element and if it is still a video slide
        if (is_mejs == false || !is_video_slide) return;

        if (is_video_slide) 
        {
            var video_iframe = mejs_el.find('>iframe');

            // if (video_iframe.length > 0)
            // {
            //     mejs_inner = mejs_el;
            //     video_iframe.css('z-index', 1);
            // }

            if (section_overlay.length)
            {
                section_overlay.prependTo(mejs_inner);
                section_overlay.css('z-index', 1);
            }

            if (click_overlay.length) 
            {
                click_overlay.prependTo(mejs_inner);
                click_overlay.css('z-index', 2);
            }

            if (slide_caption.length)
            {
                // mejs element applies its own default text, so we have to apply the font inline
                slide_caption_title.css('font-family', heading_font);
                slide_caption_content.css('font-family', body_font);
                slide_caption.prependTo(mejs_inner);
                slide_caption.css('z-index', 3);
            }
        }

        mejs_controls.css('z-index', 5);

        slideshow.on('mouseover', function (event)
        {
            var target = $(event.target);
            var active_slide = $(this).find('.active-slide:not(".av-slideshow-caption")');
            
            mejs_inner = active_slide.find('.mejs-inner');
            
            if (active_slide.is('.av-video-slide') == false || avia_slider.isAnimating) return;

            // permanent caption has to be appended to the current slide to get access to mejs controls
            if (permanent_caption.length && mejs_inner.find('>.av-slideshow-caption').length == 0) 
            {
                permanent_caption_title.add(permanent_caption_content).css({
                    'visibility': 'visible',
                    'animation': 'none',
                    'opacity': 1
                });

                permanent_caption_title.css('font-family', heading_font);
                permanent_caption_content.css('font-family', body_font);
                
                if(mejs_inner.length > 0) 
                {
                    setTimeout(function ()
                    {
                        permanent_caption.appendTo(mejs_inner);
                        permanent_caption.css('z-index', 1);
                    }, 100);
                }
            }

            if (target.is('.mejs-volume-button') || target.is(":button")) 
            {
                if (click_overlay.is(':visible'))
                {
                    click_overlay.hide();
                }
            }
            else 
            {
                if (!click_overlay.is(':visible') && !mejs_volume_slider.is(':visible'))
                {
                    click_overlay.show();
                }
            }
        });

        if (avia_slider.isMobile == false) 
        {
            // bring permanent caption back to its original position
            slideshow_arrows.add(goto_buttons).on('click', function ()
            {
                permanent_caption.appendTo(slideshow);
            });

            avia_slider.$slider.on('avia_slider_navigate_slide', function() 
            {
                permanent_caption.appendTo(slideshow);
            });
            
            permanent_caption.on('click', function() {
                click_overlay.trigger('click');
            });
        }
    });
}
