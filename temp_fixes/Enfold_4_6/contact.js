// -------------------------------------------------------------------------------------------
// contact form ajax
// -------------------------------------------------------------------------------------------

(function($)
{
	$.fn.avia_ajax_form = function(variables)
	{	
		var defaults =
		{
			sendPath: 'send.php',
			responseContainer: '.ajaxresponse'
		};

		var options = $.extend(defaults, variables);

		return this.each(function()
		{
			var form = $(this),
				form_sent = false,
				send =
				{
					formElements: form.find('textarea, select, input[type=text], input[type=checkbox], input[type=hidden]'),
					validationError:false,
					button : form.find('input:submit'),
					dataObj : {}
				},

				responseContainer = form.next(options.responseContainer+":eq(0)");

				send.button.on('click', checkElements);
				
				
				//change type of email forms on mobile so the e-mail keyboard with @ sign is used
				if($.avia_utilities.isMobile)
				{
					send.formElements.each(function(i)
					{
						var currentElement = $(this), 
							is_email = currentElement.hasClass('is_email');
							
						if(is_email) currentElement.attr('type','email');
					});
				}
			

			function checkElements( e )
			{
				// reset validation var and send data
				send.validationError = false;
				send.datastring = 'ajax=true';

				//	Get in js added element (e.g. from reCAPTCHA)
				send.formElements = form.find('textarea, select, input[type=text], input[type=checkbox], input[type=hidden], input[type=email]');
				
				send.formElements.each(function(i)
				{
					var currentElement = $(this),
						surroundingElement = currentElement.parent(),
						value = currentElement.val(),
						name = currentElement.attr('name'),
					 	classes = currentElement.attr('class'),
					 	nomatch = true;

					 	if(currentElement.is(':checkbox'))
					 	{
					 		if(currentElement.is(':checked')) { value = true; } else { value = ''; }
					 	}
					 	
					 	send.dataObj[name] = encodeURIComponent(value);

					 	if(classes && classes.match(/is_empty/))
						{	
							if(value == '' || value == null)
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("error");
								send.validationError = true;
							}
							else
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("valid");
							}
							nomatch = false;
						}

						if(classes && classes.match(/is_email/))
						{
							if( ! value.match(/^[\w|\.|\-]+@\w[\w|\.|\-]*\.[a-zA-Z]{2,20}$/))
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("error");
								send.validationError = true;
							}
							else
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("valid");
							}
							nomatch = false;
						}
						
						if(classes && classes.match(/is_ext_email/))
						{
							//  also allowed would be: ! # $ % & ' * + - / = ? ^ _ ` { | } ~
							if( ! value.match( /^[\w|\.|\-|ÄÖÜäöü]+@\w[\w|\.|\-|ÄÖÜäöü]*\.[a-zA-Z]{2,20}$/ ) )
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("error");
								send.validationError = true;
							}
							else
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("valid");
							}
							nomatch = false;
						}
						
						if(classes && classes.match(/is_phone/))
						{
							if( ! value.match(/^(\d|\s|\-|\/|\(|\)|\[|\]|e|x|t|ension|\.|\+|\_|\,|\:|\;){3,}$/))
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("error");
								send.validationError = true;
							}
							else
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("valid");
							}
							nomatch = false;
						}

						if(classes && classes.match(/is_number/))
						{
							if(!($.isNumeric(value)) || value == "")
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("error");
								send.validationError = true;
							}
							else
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("valid");
							}
							nomatch = false;
						}

						if(classes && classes.match(/captcha/) && ! classes.match(/recaptcha/) )
						{
							var verifier 	= form.find("#" + name + "_verifier").val(),
								lastVer		= verifier.charAt(verifier.length-1),
								finalVer	= verifier.charAt(lastVer);

							if(value != finalVer)
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("error");
								send.validationError = true;
							}
							else
							{
								surroundingElement.removeClass("valid error ajax_alert").addClass("valid");
							}
							nomatch = false;
						}

						if(nomatch && value != '')
						{
							surroundingElement.removeClass("valid error ajax_alert").addClass("valid");
						}
				});

				if(send.validationError == false)
				{
					if(form.data('av-custom-send'))
					{
						mailchimp_send();	
					}
					else
					{
						send_ajax_form();
					}
				}
				
				return false;
			}
			
			
			function send_ajax_form()
			{
				if(form_sent){ return false; }
				
				if( send.button.hasClass( 'avia_button_inactive' ) )
				{
					return false;
				}

				form_sent = true;
				send.button.addClass('av-sending-button');
				send.button.val(send.button.data('sending-label'));
				
				var redirect_to = form.data('avia-redirect') || false,
				    action = form.attr('action'),
				    label = form.is( '.av-form-labels-style' );

				    if( label ) return;
				
				responseContainer.load(action+' '+options.responseContainer, send.dataObj, function()
				{
					if(redirect_to && action != redirect_to)
					{
						form.attr('action', redirect_to);
						location.href = redirect_to;
						// form.submit();
					}
					else
					{
						responseContainer.removeClass('hidden').css({display:"block"});
						form.slideUp(400, function(){responseContainer.slideDown(400, function(){ $('body').trigger('av_resize_finished'); }); send.formElements.val('');});
					}
				});
			}
			
			
			function mailchimp_send()
			{
				if(form_sent){ return false; }

				form_sent = true;
				
				var original_label = send.button.val();

				send.button.addClass('av-sending-button');
				send.button.val(send.button.data('sending-label'));
				send.dataObj.ajax_mailchimp = true;
				
				var redirect_to 		= form.data('avia-redirect') || false,
					action				= form.attr('action'),
					error_msg_container = form.find('.av-form-error-container'),
					form_id 			= form.data('avia-form-id'); 
				
				$.ajax({
					url: action,
					type: "POST",
					data:send.dataObj,
					beforeSend: function()
					{
						if(error_msg_container.length)
						{
							error_msg_container.slideUp(400, function()
							{
								error_msg_container.remove();
								$('body').trigger('av_resize_finished');
							});
						}
					},
					success: function(responseText)
					{
						var response	= jQuery("<div>").append(jQuery.parseHTML(responseText)),
							error		= response.find('.av-form-error-container');	
						
						if(error.length)
						{
							form_sent = false;
							form.prepend(error);
							error.css({display:"none"}).slideDown(400, function()
							{
								$('body').trigger('av_resize_finished');
							});

							send.button.removeClass('av-sending-button');
							send.button.val(original_label);
						}
						else
						{
							if(redirect_to && action != redirect_to)
							{
								form.attr('action', redirect_to);
								location.href = redirect_to;
								// form.submit();
							}
							else
							{
								var success_text = response.find(options.responseContainer + "_" + form_id);
								
								responseContainer.html(success_text).removeClass('hidden').css({display:"block"});
								
								form.slideUp(400, function()
								{
									responseContainer.slideDown(400, function()
									{ 
										$('body').trigger('av_resize_finished'); 
									});
									
								send.formElements.val('');
							});
							}
						}
						
					},
					error: function()
					{
						
					},
					complete: function()
					{
					    
					}
				});

			}
			
			
		});
	};
})(jQuery);




