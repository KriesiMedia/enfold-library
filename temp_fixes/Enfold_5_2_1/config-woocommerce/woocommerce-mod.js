jQuery( function($)
{
	cart_improvement_functions();
	cart_dropdown_improvement();
	track_ajax_add_to_cart();

	if( $.fn.avia_sc_slider )
	{
		$(".shop_slider_yes ul").avia_sc_slider( { appendControlls: false, group: true, slide: '.product', arrowControll: true, autorotationInterval: 'parent' } );
	}


	//make woocommerce 3.6 gallery search icon clickable and open lightbox
	$( 'body.single-product' ).on( 'click', '.single-product-main-image .avia-wc-30-product-gallery-lightbox', function( e )
	{
		e.preventDefault();
		var clicked = $(this),
			container = clicked.parents('.single-product-main-image'),
			img = container.find('.flex-active-slide a.lightbox-added').eq(0);

		//if no gallery is used we need to find the original image size differently
		if(img.length == 0)
		{
			img = container.find('a.lightbox-added').eq(0);
		}

		img.trigger('click');
	});


	product_add_to_cart_click();


	function avia_apply_quant_btn()
	{
		$( ".quantity input[type=number]" ).each( function()
		{
			var number = $(this),
				current_val = number.val(),
				cloned = number.clone( true );

			//	WC 4.0 renders '' for grouped products
			if( ( 'undefined' == typeof( current_val ) ) || ( '' == ( current_val + '' ).trim() ) )
			{
				var placeholder = cloned.attr( 'placeholder' );
				placeholder = ( ( 'undefined' == typeof( placeholder ) ) || ( '' == ( placeholder + '' ).trim() ) ) ? 1 : placeholder;
				cloned.attr( 'value', placeholder );
			}

			var	max = parseFloat( number.attr( 'max' ) ),
				min = parseFloat( number.attr( 'min' ) ),
				step = parseInt( number.attr( 'step' ), 10 ),
				newNum = cloned.insertAfter( number );
				newNum.addClass('no-spin-num');
				number.remove();

			setTimeout(function()
			{
				var minus = null,
					plus = null;

				if( newNum.next( '.plus' ).length === 0 )
				{
					minus = $( '<input type="button" value="-" class="minus">' ).insertBefore( newNum ),
					plus = $( '<input type="button" value="+" class="plus">' ).insertAfter( newNum );
				}
				else
				{
					minus = newNum.prev( '.minus' );
					plus = newNum.next( '.plus' );
				}

				minus.on( 'click', function()
				{
					var the_val = parseInt( newNum.val(), 10 );
					if( isNaN( the_val ) )
					{
						the_val = 0;
					}
					the_val -= step;

					the_val = the_val < 0 ? 0 : the_val;
					the_val = the_val < min ? min : the_val;
					newNum.val(the_val).trigger( "change" );
				});

				plus.on( 'click', function()
				{
					var the_val = parseInt( newNum.val(), 10 );
					if( isNaN( the_val ) )
					{
						the_val = 0;
					}
					the_val += step;

					the_val = the_val > max ? max : the_val;
					newNum.val(the_val).trigger( "change" );

				});

			}, 10 );

		});
	}

	avia_apply_quant_btn();

	//if the cart gets updated via ajax (woocommerce 2.6 and higher) we need to re apply the +/- buttons
	$( document ).on( 'updated_cart_totals', avia_apply_quant_btn );

	setTimeout(first_load_amount, 10);
	$('body').on( 'added_to_cart', update_cart_dropdown );
	$('body').on( 'wc_fragments_refreshed', avia_cart_dropdown_changed );


	// small fix for the hover menu for woocommerce sort buttons since it does no seem to work on mobile devices.
	// even if no event is actually bound the css dropdown works. if the binding is removed dropdown does no longer work.
	$('.avia_mobile .sort-param').on('touchstart', function(){});

});


/**
 * The ajax cart dropdown counter needs to be changed on cart page when user removes items or changes amount -
 * we have to check for changed amount of products in cart dropdown to update the cart counter
 * (reacts on remove items and changes to quantity)
 */
function avia_cart_dropdown_changed()
{
	var the_html		= jQuery('html'),
	    cart			= jQuery('body').is('.woocommerce-cart'),
		cart_counter	= jQuery('.cart_dropdown .av-cart-counter'),
	    menu_cart		= jQuery('.cart_dropdown'),
		counter			= 0,
		wc30			= jQuery( 'body' ).hasClass( 'avia-woocommerce-30' );

	if( ! cart )
	{
		return;
	}

	if( ! wc30 )
	{
		menu_cart.find('.cart_list li .quantity').each(function()
		{
			counter += parseInt( jQuery(this).text(), 10 );
		});
	}
	else
	{
		counter = parseInt( cart_counter.text(), 10 );
	}

	if( counter == 0 )
	{
		cart_counter.removeClass('av-active-counter').text(counter);
		setTimeout( function() { the_html.removeClass('html_visible_cart'); }, 200);
	}
	else if( ( cart_counter.length > 0 ) && ( counter > 0 ) )
	{
		setTimeout( function()
		{
			cart_counter.addClass('av-active-counter').text(counter);
			the_html.addClass('html_visible_cart');
		}, 10 );
	}

	return;
}


//updates the shopping cart in the sidebar, hooks into the added_to_cart event which is triggered by woocommerce
function update_cart_dropdown( event )
{
	var the_html		= jQuery('html'),
		menu_cart 		= jQuery('.cart_dropdown'),
		cart_counter	= jQuery('.cart_dropdown .av-cart-counter'),
		empty 			= menu_cart.find('.empty'),
		msg_success		= menu_cart.data('success'),
		product 		= jQuery.extend({name:"Product", price:"", image:""}, avia_clicked_product),
		counter			= 0,
		wc30			= jQuery( 'body' ).hasClass( 'avia-woocommerce-30' );

		//	removed by theme option ?
		if( cart_counter.length == 0 )
		{
			return;
		}

			//	trigger changed in WC 3.0.0 - must check for event explecit
		if( ( empty.length > 0 ) && ( 'undefined' != typeof event ) )
		{
			the_html.addClass( 'html_visible_cart' );
		}

		if( typeof event !== 'undefined' )
		{
			var header		 = jQuery('.html_header_sticky #header_main .cart_dropdown_first, .html_header_sidebar #header_main .cart_dropdown_first'),
				oldTemplates = jQuery('.added_to_cart_notification').trigger('avia_hide'),
				template 	 = jQuery("<div class='added_to_cart_notification'><span class='avia-arrow'></span><div class='added-product-text'><strong>\"" + product.name +"\"</strong> "+ msg_success+ "</div> " + product.image +"</div>").css( 'opacity', 0 );

			if(!header.length) header = 'body';

			template.on('mouseenter avia_hide', function()
			{
				template.animate({opacity:0, top: parseInt(template.css('top'), 10) + 15 }, function()
				{
					template.remove();
				});

			}).appendTo(header).animate({opacity:1},500);

			setTimeout(function()
			{
				template.trigger('avia_hide');
			}, 2500);
		}


		if( 'object' == typeof event && 'added_to_cart' == event.type && wc30 )
		{
			return;
		}

			//	with WC 3.0.0 DOM is not ready - wrong calculation of counter (last element missing)
		setTimeout( function()
		{
			if( ! wc30 )
			{
				menu_cart.find('.cart_list li .quantity').each(function()
				{
					counter += parseInt( jQuery(this).text(), 10 );
				});
			}
			else
			{
				counter = cart_counter.text();
			}

			if( ( cart_counter.length > 0 ) && ( counter > 0) )
			{
				setTimeout( function()
				{
					cart_counter.addClass( 'av-active-counter' ).text( counter );
				}, 10 );
			}
		}, 300 );
}


var avia_clicked_product = {};
function track_ajax_add_to_cart()
{
	jQuery('body').on( 'click','.add_to_cart_button', function(e)
	{
		var productContainer = jQuery(this).parents('.product').eq(0),
			product = {};

		product.name = productContainer.find( '.woocommerce-loop-product__title' ).text();
		product.image = productContainer.find( '.thumbnail_container img' );
		product.price = productContainer.find( '.price .amount' ).last().text();

		//lower than woocommerce 3.0.0
		if( product.name === "" )
		{
			product.name = productContainer.find('.inner_product_header h3').text();
		}

		/*fallbacks*/
		if( productContainer.length === 0 )
		{
			productContainer = jQuery(this);
			product.name	 = productContainer.find('.av-cart-update-title').text();
			product.image	 = productContainer.find('.av-cart-update-image');
			product.price	 = productContainer.find('.av-cart-update-price').text();
		}

		if(product.image.length)
		{
			product.image = "<img class='added-product-image' src='" + product.image.get(0).src + "' title='' alt='' />";
		}
		else
		{
			product.image = "";
		}

		avia_clicked_product = product;
	});
}


//function that pre fills the amount value of the cart
function first_load_amount()
{
	var counter = 0,
		limit = 15,
		ms = 500,
		check = function()
		{
			var new_total = jQuery( '.cart_dropdown .dropdown_widget_cart' ).eq( 0 ).find( '.total .amount' );

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
			}
		};

	check();

	//display the cart for a short moment on page load if a product was added but no notice was delivered (eg template builder page)
	if( jQuery('.av-display-cart-on-load').length && jQuery('.woocommerce-message').length === 0 )
	{
		var dropdown = jQuery('.cart_dropdown');
		setTimeout( function(){ dropdown.trigger('mouseenter'); }, 500 );
		setTimeout( function(){ dropdown.trigger('mouseleave'); }, 2500 );
	}
}

function product_add_to_cart_click()
{
	var jbody = jQuery( 'body' ),
		catalogue = jQuery( '.av-catalogue-item' ),
		loader = false;

	if( catalogue.length )
	{
		loader = jQuery.avia_utilities.loading();
	}

	jbody.on('click', '.add_to_cart_button', function(e)
	{
		var button = jQuery(this);
		button.parents('.product').eq( 0 ).addClass('adding-to-cart-loading').removeClass('added-to-cart-check');

		if(button.is('.av-catalogue-item'))
		{
			loader.show();
		}

		var $html = jQuery('html');
		if( ! $html.hasClass( 'html_visible_cart' ) )
		{
			$html.addClass( 'html_visible_cart' );
		}

		//e.preventDefault();
	});

	jbody.on( 'added_to_cart', function()
	{
		jQuery( '.adding-to-cart-loading').removeClass('adding-to-cart-loading' ).addClass( 'added-to-cart-check' );

		if( loader !== false )
		{
			loader.hide();
		}
	});

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
	jQuery('.single-product-main-image>.images a').attr('rel','product_images[grouped]');
}


//small function that improves shoping cart hover behaviour in the menu
function cart_dropdown_improvement()
{
	var dropdown = jQuery('.cart_dropdown'),
		icon = dropdown.find( '.cart_dropdown_link' ),
		subelement = dropdown.find('.dropdown_widget').css({display:'none', opacity:0});

	icon.on( 'focus', function()
	{
		dropdown.trigger( 'mouseenter' );
	}).on( 'blur', function()
	{
		dropdown.trigger( 'mouseleave' );
	});

	dropdown.on( 'mouseenter', function()
	{
		subelement.css({display:'block'}).stop().animate({opacity:1});
	}).on( 'mouseleave', function()
	{
		subelement.stop().animate({opacity:0}, function(){ subelement.css({display:'none'}); });
	});
}
