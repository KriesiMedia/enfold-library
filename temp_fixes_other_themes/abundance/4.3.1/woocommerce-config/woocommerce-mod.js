jQuery(document).ready(function($) {

	cart_improvement_functions();
	cart_button_animation();
	cart_dropdown_improvement();
	avia_select_unify('select:not(.checkout select)');
	avia_cloud_zoom('.cloudzoom_active .images .woocommerce-product-gallery__image:first a', '.cloudzoom_active .thumbnails');
	
	
	function avia_apply_quant_btn()
	{
		jQuery(".quantity input[type=number]").each(function() {
		var number = $(this),
				max = parseFloat( number.attr( 'max' ) ),
				min = parseFloat( number.attr( 'min' ) ),
				step = parseInt( number.attr( 'step' ), 10 ),
				newNum = jQuery(jQuery('<div />').append(number.clone(true)).html().replace('number','text')).insertAfter(number);
				number.remove();
	
			setTimeout(function(){
				if(newNum.next('.plus').length == 0) {
					var minus = jQuery('<input type="button" value="-" class="minus">').insertBefore(newNum),
							plus    = jQuery('<input type="button" value="+" class="plus">').insertAfter(newNum);
		
					minus.on('click', function(){
						var the_val = parseInt( newNum.val(), 10 ) - step;
						the_val = the_val < 0 ? 0 : the_val;
						the_val = the_val < min ? min : the_val;
						newNum.val(the_val).trigger("change");
					});
					plus.on('click', function(){
						var the_val = parseInt( newNum.val(), 10 ) + step;
						the_val = the_val > max ? max : the_val;
						newNum.val(the_val).trigger("change");
		
					});
				}
			},10);
		
		});
	}
	
	avia_apply_quant_btn();
	
	//if the cart gets updated via ajax (woocommerce 2.6 and higher) we need to re apply the +/- buttons
	$( document ).on( 'updated_cart_totals', avia_apply_quant_btn );
	
	
	setTimeout(first_load_amount, 10);
	$('body').on('added_to_cart', update_cart_dropdown);
	
	
});


//updates the shopping cart in the sidebar, hooks into the added_to_cart event whcih is triggered by woocommerce
function update_cart_dropdown()
{
	setTimeout(function(){
				var menu_cart 		= jQuery('.cart_dropdown'),
					dropdown_cart 	= menu_cart.find('.dropdown_widget_cart:eq(0)'),
					subtotal 		= menu_cart.find('.cart_subtotal'),
					subtotal_new 	= dropdown_cart.find('.total .amount');				
					subtotal.html(subtotal_new.html());
		}, 500);
}

//function that pre fills the amount value of the cart
function first_load_amount()
{
	var counter = 0, 
		limit = 5, 
		ms = 300,
		check = function()
		{
			var new_total = jQuery('.cart_dropdown .dropdown_widget_cart:eq(0) .total .amount');
			
			if(new_total.length)
			{
				update_cart_dropdown();
			}
			else
			{
				counter++;
				if(counter < limit)
				{
					setTimeout(check, ms);
				}
				
				if(counter == 2)
				{
					var cur_total  = jQuery('.cart_dropdown:eq(0) .cart_subtotal:eq(0) .amount'),
						symbol_pos = isNaN(cur_total.text().charAt(0)) ? "before" : "after",
						symbol	   = cur_total.text().replace(/\d|\.|,|-|_/g,'');
					
					symbol_pos == "after" ? cur_total.html("0" + symbol)	: cur_total.html(symbol + "0");
				}
				
			}
		};
		
		check();
}





// little fixes and modifications to the dom
function cart_improvement_functions()
{
	//single products are added via ajax //doesnt work currently
	//jQuery('.summary .cart .button[type=submit]').addClass('add_to_cart_button product_type_simple');
	
	//downloadable products are now added via ajax as well
	jQuery('.product_type_downloadable, .product_type_virtual').addClass('product_type_simple');
	
	//clicking tabs dont activate smoothscrooling
	jQuery('.woocommerce-tabs .tabs a').addClass('no-scroll');
	
	//connect thumbnails on single product page via lightbox
	jQuery('.prev_image_container>.images a').attr('rel','product_images[grouped]'); 
	
	//equal height and width for thumbnail container
	var thumbContainer = jQuery('.thumbnail_container');
	
	thumbContainer.each(function()
	{
		var container = jQuery(this);
			container.css('min-height', container.height());
	});
}


//improve layout of select dropdowns
function avia_select_unify(select_el)
{
	var selects = jQuery(select_el).not('#rating');

	 //unify select dropdowns
    selects.each(function()
    {
    	var el = jQuery(this);
    	
    	if(el.css('display') == 'none') return;
    	
    	el.wrap('<span class="avia_style_wrap" />').wrap('<span class="avia_select_unify" />').after('<span class="avia_select_fake_val"></span>');
    	el.css('opacity',0).next('.avia_select_fake_val').text(el.find('option:selected').text());
   
	 
	    jQuery(document).on('change', this, function()
	    {
	    	el.next('.avia_select_fake_val').text(el.find('option:selected').text());
	    });
	    
     });
}


//small function that improves shoping cart button behaviour
function cart_button_animation()
{
	var containers 	= jQuery('.thumbnail_container');
	
	containers.each(function()
	{
		var container = jQuery(this), buttons = container.find('.button');
			container.containerHeight = container.height()/2;
		
		buttons.css({opacity:0, visibility:'visible', top: container.containerHeight});
		container.hover(
		function()
		{
			if(container.containerHeight < 20) 
			{ 
				container.containerHeight = container.height()/2; buttons.css({top: container.containerHeight}); 
			}
						
			if(buttons.length > 1)
			{
				
				buttons.each(function(i)
				{
					var button = jQuery(this);
					
					if(i == 0)
					{
						var newPos = container.containerHeight - button.outerHeight()/2;
							button.stop().animate({top:newPos - 3, opacity:1})
						
					}
					else
					{
						var newPos = container.containerHeight + button.outerHeight()/2;
							button.stop().animate({top:newPos + 3, opacity:1})
					}
				
				});
				
			}
			else
			{
				buttons.stop().animate({opacity:1});
			}
		},
		function()
		{
			buttons.stop().animate({opacity:0, top: container.containerHeight});
		});
	});
}



//small function that improves shoping cart hover behaviour in the menu
function cart_dropdown_improvement()
{
	var dropdown = jQuery('.cart_dropdown'), subelement = dropdown.find('.dropdown_widget').css({display:'none', opacity:0});
	
	dropdown.hover(
	function(){ subelement.css({display:'block'}).stop().animate({opacity:1}); },
	function(){ subelement.stop().animate({opacity:0}, function(){ subelement.css({display:'none'}); }); }
	);
}







//enhances product images with the cloudzoom feature
function avia_cloud_zoom(target, thumbnails)
{
	var image_target 		= jQuery(target),
		thumb_container 	= jQuery(thumbnails),
		image_thumbnails 	= thumb_container.find('a'),
		rel 				= 'adjustX: 38, adjustY:0, zoomWidth:613';
		
//	Removed because broken layout since WC 2.7 gallery
//	==================================================
//	
//	if(image_thumbnails.length)
//	{
//		var clone 				= image_target.clone().prependTo(thumb_container);
//			image_thumbnails 	= thumb_container.find('a');
//	}
		
	
	image_target.addClass('cloud-zoom').attr('rel', rel).CloudZoom();
				
	image_thumbnails.bind('click', function()
	{
		var image = jQuery(this).clone(false);
		image.insertAfter(image_target);	
		image_target.remove();
		jQuery('.mousetrap').remove();
		image_target = image;
		image_target.addClass('cloud-zoom').attr('rel', rel).CloudZoom();
		return false;
	});	
		
}















/*plugins*/

//////////////////////////////////////////////////////////////////////////////////
// Cloud Zoom V1.0.2
// (c) 2010 by R Cecco. <http://www.professorcloud.com>
// MIT License
//
// Please retain this copyright header in all versions of the software
//////////////////////////////////////////////////////////////////////////////////
(function ($) {


    function format(str) {
        for (var i = 1; i < arguments.length; i++) {
            str = str.replace('%' + (i - 1), arguments[i]);
        }
        return str;
    }

    function CloudZoom(jWin, opts) {
        var sImg = $('img', jWin);
		var	img1;
		var	img2;
        var zoomDiv = null;
		var	$mouseTrap = null;
		var	lens = null;
		var	$tint = null;
		var	softFocus = null;
		var	$ie6Fix = null;
		var	zoomImage;
        var controlTimer = 0;      
        var cw, ch;
        var destU = 0;
		var	destV = 0;
        var currV = 0;
        var currU = 0;      
        var filesLoaded = 0;
        var mx,
            my; 
        var ctx = this, zw;
        // Display an image loading message. This message gets deleted when the images have loaded and the zoom init function is called.
        // We add a small delay before the message is displayed to avoid the message flicking on then off again virtually immediately if the
        // images load really fast, e.g. from the cache. 
        //var	ctx = this;
        
        setTimeout(function () {
            //						 <img src="/images/loading.gif"/>
            if ($mouseTrap === null) {
                var w = jWin.width();
                jWin.parent().append(format('<div style="width:%0px;position:absolute;top:75%;left:%1px;text-align:center" class="cloud-zoom-loading" >Loading...</div>', w / 3, (w / 2) - (w / 6))).find(':last').css('opacity', 0.5);
            }
        }, 200);


        var ie6FixRemove = function () {

            if ($ie6Fix !== null) {
                $ie6Fix.remove();
                $ie6Fix = null;
            }
        };

        // Removes cursor, tint layer, blur layer etc.
        this.removeBits = function () {
            //$mouseTrap.unbind();
            if (lens) {
                lens.remove();
                lens = null;             
            }
            if ($tint) {
                $tint.remove();
                $tint = null;
            }
            if (softFocus) {
                softFocus.remove();
                softFocus = null;
            }
            ie6FixRemove();

            $('.cloud-zoom-loading', jWin.parent()).remove();
        };


        this.destroy = function () {
            jWin.data('zoom', null);

            if ($mouseTrap) {
                $mouseTrap.unbind();
                $mouseTrap.remove();
                $mouseTrap = null;
            }
            if (zoomDiv) {
                zoomDiv.remove();
                zoomDiv = null;
            }
            //ie6FixRemove();
            this.removeBits();
            // DON'T FORGET TO REMOVE JQUERY 'DATA' VALUES
        };


        // This is called when the zoom window has faded out so it can be removed.
        this.fadedOut = function () {
            
			if (zoomDiv) {
                zoomDiv.remove();
                zoomDiv = null;
            }
			 this.removeBits();
            //ie6FixRemove();
        };

        this.controlLoop = function () {
            if (lens) {
                var x = (mx - sImg.offset().left - (cw * 0.5)) >> 0;
                var y = (my - sImg.offset().top - (ch * 0.5)) >> 0;
               
                if (x < 0) {
                    x = 0;
                }
                else if (x > (sImg.outerWidth() - cw)) {
                    x = (sImg.outerWidth() - cw);
                }
                if (y < 0) {
                    y = 0;
                }
                else if (y > (sImg.outerHeight() - ch)) {
                    y = (sImg.outerHeight() - ch);
                }

                lens.css({
                    left: x,
                    top: y
                });
                lens.css('background-position', (-x) + 'px ' + (-y) + 'px');

                destU = (((x) / sImg.outerWidth()) * zoomImage.width) >> 0;
                destV = (((y) / sImg.outerHeight()) * zoomImage.height) >> 0;
                currU += (destU - currU) / opts.smoothMove;
                currV += (destV - currV) / opts.smoothMove;

                zoomDiv.css('background-position', (-(currU >> 0) + 'px ') + (-(currV >> 0) + 'px'));              
            }
            controlTimer = setTimeout(function () {
                ctx.controlLoop();
            }, 30);
        };

        this.init2 = function (img, id) {

            filesLoaded++;
            //console.log(img.src + ' ' + id + ' ' + img.width);	
            if (id === 1) {
                zoomImage = img;
            }
            //this.images[id] = img;
            if (filesLoaded === 2) {
                this.init();
            }
        };

        /* Init function start.  */
        this.init = function () {
            // Remove loading message (if present);
            $('.cloud-zoom-loading', jWin.parent()).remove();


/* Add a box (mouseTrap) over the small image to trap mouse events.
		It has priority over zoom window to avoid issues with inner zoom.
		We need the dummy background image as IE does not trap mouse events on
		transparent parts of a div. background-image:url(\".\")
		*/
            $mouseTrap = jWin.parent().append(format("<div class='mousetrap' style=';z-index:999;position:absolute;width:%0px;height:%1px;left:%2px;top:%3px;\'></div>", sImg.outerWidth(), sImg.outerHeight(), 0, 0)).find(':last');


            //////////////////////////////////////////////////////////////////////			
            /* Do as little as possible in mousemove event to prevent slowdown. */
            $mouseTrap.bind('mousemove', this, function (event) {
                // Just update the mouse position
                mx = event.pageX;
                my = event.pageY;
                
            });
            //////////////////////////////////////////////////////////////////////					
            $mouseTrap.bind('mouseleave', this, function (event) {
                clearTimeout(controlTimer);
                //event.data.removeBits();                
				if(lens) { lens.fadeOut(299); }
				if($tint) { $tint.fadeOut(299); }
				if(softFocus) { softFocus.fadeOut(299); }
				zoomDiv.fadeOut(300, function () {
                    ctx.fadedOut();
                });																
                return false;
            });
            //////////////////////////////////////////////////////////////////////			
            $mouseTrap.bind('mouseenter', this, function (event) {
				mx = event.pageX;
                my = event.pageY;
                zw = event.data;
                if (zoomDiv) {
                    zoomDiv.stop(true, false);
                    zoomDiv.remove();
                }

                var xPos = opts.adjustX,
                    yPos = opts.adjustY;
                             
                var siw = sImg.outerWidth();
                var sih = sImg.outerHeight();

                var w = opts.zoomWidth;
                var h = opts.zoomHeight;
                if (opts.zoomWidth == 'auto') {
                    w = siw;
                }
                if (opts.zoomHeight == 'auto') {
                    h = sih;
                }
                //$('#info').text( xPos + ' ' + yPos + ' ' + siw + ' ' + sih );
                var appendTo = jWin.parent(); // attach to the wrapper			
                switch (opts.position) {
                case 'top':
                    yPos -= h; // + opts.adjustY;
                    break;
                case 'right':
                    xPos += siw; // + opts.adjustX;					
                    break;
                case 'bottom':
                    yPos += sih; // + opts.adjustY;
                    break;
                case 'left':
                    xPos -= w; // + opts.adjustX;					
                    break;
                case 'inside':
                    w = siw;
                    h = sih;
                    break;
                    // All other values, try and find an id in the dom to attach to.
                default:
                    appendTo = $('#' + opts.position);
                    // If dom element doesn't exit, just use 'right' position as default.
                    if (!appendTo.length) {
                        appendTo = jWin;
                        xPos += siw; //+ opts.adjustX;
                        yPos += sih; // + opts.adjustY;	
                    } else {
                        w = appendTo.innerWidth();
                        h = appendTo.innerHeight();
                    }
                }

                zoomDiv = appendTo.append(format('<div id="cloud-zoom-big" class="cloud-zoom-big" style="display:none;position:absolute;left:%0px;top:%1px;width:%2px;height:%3px;background-image:url(\'%4\');z-index:99;"></div>', xPos, yPos, w, h, zoomImage.src)).find(':last');

                // Add the title from title tag.
                if (sImg.attr('title') && opts.showTitle) {
                    zoomDiv.append(format('<div class="cloud-zoom-title">%0</div>', sImg.attr('title'))).find(':last').css('opacity', opts.titleOpacity);
                }

                // Fix ie6 select elements wrong z-index bug. Placing an iFrame over the select element solves the issue...		
                if ($.browser.msie && $.browser.version < 7) {
                    $ie6Fix = $('<iframe frameborder="0" src="#"></iframe>').css({
                        position: "absolute",
                        left: xPos,
                        top: yPos,
                        zIndex: 99,
                        width: w,
                        height: h
                    }).insertBefore(zoomDiv);
                }

                zoomDiv.fadeIn(500);

                if (lens) {
                    lens.remove();
                    lens = null;
                } /* Work out size of cursor */
                cw = (sImg.outerWidth() / zoomImage.width) * zoomDiv.width();
                ch = (sImg.outerHeight() / zoomImage.height) * zoomDiv.height();

                // Attach mouse, initially invisible to prevent first frame glitch
                lens = jWin.append(format("<div class = 'cloud-zoom-lens' style='display:none;z-index:98;position:absolute;width:%0px;height:%1px;'></div>", cw, ch)).find(':last');

                $mouseTrap.css('cursor', lens.css('cursor'));

                var noTrans = false;

                // Init tint layer if needed. (Not relevant if using inside mode)			
                if (opts.tint) {
                    lens.css('background', 'url("' + sImg.attr('src') + '")');
                    $tint = jWin.append(format('<div style="display:none;position:absolute; left:0px; top:0px; width:%0px; height:%1px; background-color:%2;" />', sImg.outerWidth(), sImg.outerHeight(), opts.tint)).find(':last');
                    $tint.css('opacity', opts.tintOpacity);                    
					noTrans = true;
					$tint.fadeIn(500);

                }
                if (opts.softFocus) {
                    lens.css('background', 'url("' + sImg.attr('src') + '")');
                    softFocus = jWin.append(format('<div style="position:absolute;display:none;top:2px; left:2px; width:%0px; height:%1px;" />', sImg.outerWidth() - 2, sImg.outerHeight() - 2, opts.tint)).find(':last');
                    softFocus.css('background', 'url("' + sImg.attr('src') + '")');
                    softFocus.css('opacity', 0.5);
                    noTrans = true;
                    softFocus.fadeIn(500);
                }

                if (!noTrans) {
                    lens.css('opacity', opts.lensOpacity);										
                }
				if ( opts.position !== 'inside' ) { lens.fadeIn(500); }

                // Start processing. 
                zw.controlLoop();

                return; // Don't return false here otherwise opera will not detect change of the mouse pointer type.
            });
        };

        img1 = new Image();
        $(img1).load(function () {
            ctx.init2(this, 0);
        });
        img1.src = sImg.attr('src');

        img2 = new Image();
        $(img2).load(function () {
            ctx.init2(this, 1);
        });
        img2.src = jWin.attr('href');
    }

    $.fn.CloudZoom = function (options) {
    
    
        // IE6 background image flicker fix
        try {
            document.execCommand("BackgroundImageCache", false, true);
        } catch (e) {}
        this.each(function () {
			var	relOpts, opts;
			// Hmm...eval...slap on wrist.
			eval('var	a = {' + $(this).attr('rel') + '}');
			relOpts = a;
            if ($(this).is('.cloud-zoom')) {
                $(this).css({
                    'position': 'relative',
                    'display': 'block'
                });
                $('img', $(this)).css({
                    'display': 'block'
                });
                // Wrap an outer div around the link so we can attach things without them becoming part of the link.
                // But not if wrap already exists.
                if ($(this).parent().attr('id') != 'wrap') {
                    $(this).wrap('<div id="wrap" style="top:0px;z-index:9999;position:relative;"></div>');
                }
                opts = $.extend({}, $.fn.CloudZoom.defaults, options);
                opts = $.extend({}, opts, relOpts);
                $(this).data('zoom', new CloudZoom($(this), opts));

            } else if ($(this).is('.cloud-zoom-gallery')) {
                opts = $.extend({}, relOpts, options);
                $(this).data('relOpts', opts);
                $(this).bind('click', $(this), function (event) {
                    var data = event.data.data('relOpts');
                    // Destroy the previous zoom
                    $('#' + data.useZoom).data('zoom').destroy();
                    // Change the biglink to point to the new big image.
                    $('#' + data.useZoom).attr('href', event.data.attr('href'));
                    // Change the small image to point to the new small image.
                    $('#' + data.useZoom + ' img').attr('src', event.data.data('relOpts').smallImage);
                    // Init a new zoom with the new images.				
                    $('#' + event.data.data('relOpts').useZoom).CloudZoom();
                    return false;
                });
            }
        });
        return this;
    };

    $.fn.CloudZoom.defaults = {
        zoomWidth: 'auto',
        zoomHeight: 'auto',
        position: 'right',
        tint: false,
        tintOpacity: 0.5,
        lensOpacity: 0.5,
        softFocus: false,
        smoothMove: 3,
        showTitle: true,
        titleOpacity: 0.5,
        adjustX: 0,
        adjustY: 0
    };

})(jQuery);