(function($)
{
	"use strict";
	$.AviaElementBehavior = $.AviaElementBehavior || {};
	$.AviaElementBehavior.wp_media = $.AviaElementBehavior.wp_media || [];
	
 	$.AviaModal = function (options) {
        
        var defaults = {
        	
        		scope: this,					//@obj pass the "this" var of the invoking function to apply the correct callback later
        		modal_title: "",				//@string modal window title
        		modal_class: "",				//@string modal window class
        		modal_content: false,			//@string modal window content. if not specified ajax function will execute
        		modal_ajax_hook: "",			//@string name of php ajax hook that will execute the content fetching function
        		on_save: function(){},			//@function modal window callback function when the save button is hit
        		on_load: function(){},			//@function modal window callback function when the modal is open and finished loading
        		before_save: '',		        //@function modal window callback function when the save button is hit and the data is collected but before the final save is executed
        		save_param: {},					//@obj parameters that are passed to the callback function in addition to the form values
        		ajax_param: "",					//@string parameters that are passed to the ajax content fetching function
        		button: "save"					//@string parameter that tells the modal window which button to generate
        };
        
		$.AviaModal.openInstance.unshift(this); 

		this.instanceNr	= $.AviaModal.openInstance.length;
		this.options	= $.extend({}, defaults, options);
		this.namespace	= '.AviaModal'+ this.instanceNr;
		this.body		= $('body').addClass('avia-noscroll');
		this.wrap		= $('#wpwrap');
		this.doc		= $(document);
		this.modal		= $('<div class="avia-modal avia-style"></div>');
		this.backdrop	= $('<div class="avia-modal-backdrop"></div>');
		
		this.has_modal_popup_state = ( $.avia_builder.activeStatus.val() == 'active' && typeof this.options.save_param.hasClass == 'function' );
       
		this.set_up();

    };
    
    $.AviaModal.openInstance = [];
    
   	$.AviaModal.prototype = 
   	{
   		set_up: function()
   		{
			var obj = this;
			
			$('body').one( 'avia_modal_finished', function(){
						//	ensure DOM is ready
						setTimeout( function(){
								obj.set_to_last_state();
							}, 100 );
						
					});
			
   			this.create_html();
   			this.add_behavior();
   			this.modify_binding_order();
   			this.propagate_modal_open();
			
   		},
   		
   		add_behavior: function()
   		{
   			var obj = this;
   			
			//save modal (execute callback)
			this.modal.on('click', '.avia-modal-save', function()
			{
				obj.execute_callback();
				return false;
			});
			
			//close modal 
			this.backdrop.add(".avia-attach-close-event",this.modal).on('click', function()
			{
				obj.close();
				return false;
			});
			
			// close modal by pressing escape key. modify_binding_order makes sure that this is fired first. 
			// bind event on keydown instead of keyup cause it will probably not interfere with other plugins
			//fire save event on ENTER (13)
			this.doc.on('keydown'+this.namespace, function(e) 
			{
				if(obj.media_overlay_closed() && obj.link_overlay_closed())
				{
					if (e.keyCode == 13 && !(e.target.tagName && e.target.tagName.toLowerCase() == "textarea"))
					{
						setTimeout( function(){ obj.execute_callback(); }, 100); 
						e.stopImmediatePropagation();
					}
					if (e.keyCode == 27)
					{ 
						setTimeout( function(){ obj.close(); }, 100); 
						e.stopImmediatePropagation();
					}
				}
			});
   		},
   		
   		modify_binding_order: function()
   		{
   			var data = jQuery.hasData( document ) && jQuery._data( document ),
   				lastItem = data.events.keydown.pop();
   				data.events.keydown.unshift(lastItem);
   		},
   			
   		
   		create_html: function()
   		{
   		
   			var content	= this.options.modal_content ? this.options.modal_content : '',
   				attach	= this.options.attach_content ? this.options.attach_content : "",
   				loading = this.options.modal_content ? "" : ' preloading ',
   				title	= '<h3 class="avia-modal-title">'+this.options.modal_title+'</h3>',
   				output  = '<div class="avia-modal-inner">';
   				output += '<div class="avia-modal-inner-header">'+title+'<a href="#close" class="avia-modal-close avia-attach-close-event">X</a></div>';
   				output += '<div class="avia-modal-inner-content '+loading+'">'+content+'</div>';
   				output += attach;
   				output += '<div class="avia-modal-inner-footer">';
				
   				if(this.options.button === "save")
   				{
   					output += '<a href="#save" class="avia-modal-save button button-primary button-large">' + avia_modal_L10n.save + '</a>';
   				}
   				else if(this.options.button === "close")
   				{
   					output += '<a href="#close" class="avia-attach-close-event button button-primary button-large">' + avia_modal_L10n.close + '</a>';
   				}
   				else
   				{
   					output += this.options.button;
   				}
   				
   				output += '</div></div>';

   			//set specific modal class
   			if(this.options.modal_class) 
   			{
   				this.modal.addClass(this.options.modal_class);
   			}
   			
   			this.wrap.append(this.modal).append(this.backdrop); //changed to this.wrap instead of this.body to prevent bug with link editor popup
   			this.modal.html(output);
   			
   			//set modal margin and z-index for nested modals
   			var multiplier 	= this.instanceNr - 1,
   				z_old		= parseInt(this.modal.css('zIndex'),10);
   			
   			this.modal.css({margin: (30 * multiplier), zIndex: (z_old + multiplier + 1 )});
   			this.backdrop.css({zIndex: (z_old + multiplier)});
   			
   			
   			if(!this.options.modal_content)
   			{
   				this.fetch_ajax_content();
   			}
   			else
   			{
   				this.on_load_callback();
   			}			
   		},
		
		set_to_last_state: function()
		{
			var builder = $.avia_builder;
			
			if( ! this.has_modal_popup_state )
			{
				return;
			}
			
			var state = builder.modal_popup_state;
			if( false === state )
			{
				return;
			}
			
			//	Checks for last selected tab and last opened toggle
			var data_container = this.options.save_param;
			
			var current_shortcode = data_container.data( 'shortcodehandler' );
			var group_element = data_container.hasClass( 'avia-modal-group-element' );
			var last_element_class = group_element ? 'avia-modal-group-last-open' : 'avia-modal-element-last-open';
			var last_used = $( 'body' ).find( '.' + last_element_class );
			
			//	After page reload we use last state as we do not know which element was last clicked
			if( last_used.length > 0 )
			{
				if( ! data_container.hasClass( last_element_class ) )
				{
					builder.clear_modal_popup_state( current_shortcode, group_element );
					return;
				}
			}
			
			var state_shortcode = group_element ? state.group_shortcode : state.shortcode;
			var state_tab_text = group_element ? state.group_tab_text : state.tab_text;
			var state_toggle_text = group_element ? state.group_toggle_text : state.toggle_text;
			
			if( current_shortcode != state_shortcode )
			{
				builder.clear_modal_popup_state( current_shortcode, group_element );
				return;
			}
			
			var tab_container = this.modal.find( '.avia-modal-tab-container' );
			var tabs = tab_container.find( '.avia-modal-tab-titles a' );
			var tab_text = '';
			tabs.each( function(i)
			{
				var tab = $(this);
				tab_text = tab.html();
				if( tab_text == state_tab_text )
				{
					tab.trigger( 'click' );
					return false;
				}
			});
			
			if( '' == tab_text )
			{
				return;
			}
			
			var tab_content = tab_container.find( '.avia-modal-tab-container-inner[data-tab-name="' + tab_text + '"]' );
			
			if( '' == state_toggle_text )
			{
				return;
			}
			
			var toggle_content = tab_content.find( '.avia-modal-toggle-container-inner[data-toggle-name="' + state_toggle_text + '"]' );
			if( 0 == toggle_content.length )
			{
				return;
			}
			
			var toggle = toggle_content.closest( '.avia-modal-toggle-visibility-wrap').find( 'a.avia-modal-toggle-title' );
			if( ! toggle.hasClass( 'active-modal-toggles' ) )
			{
				toggle.trigger( 'click' );
			}
		},
   		
   		set_focus: function()
   		{
			var field = this.modal.find('select, input[type=text], input[type=checkbox], textarea, radio').filter(':eq(0)');
			if( ! field.is('.av-no-autoselect') ) 
			{
				field.focus();
			}
   		},
   		
   		fetch_ajax_content: function()
   		{
   			var obj = this, inner = obj.modal.find('.avia-modal-inner-content');
   		
	   			$.ajax({
					type: "POST",
					url: ajaxurl,
					data: 
					{
						action: 'avia_ajax_' + this.options.modal_ajax_hook,
						params: this.options.ajax_param,
						ajax_fetch: true,
						instance: this.instanceNr,
						avia_request: true
					},
					wpColorPicker: function()
					{
						$.AviaModal.openInstance[0].close();
						new $.AviaModalNotification({mode:'error', msg:avia_modal_L10n.ajax_error});
					},
					success: function(response)
					{
						if(response == 0)
						{
							$.AviaModal.openInstance[0].close();
							new $.AviaModalNotification({mode:'error', msg:avia_modal_L10n.login_error});
						}
						else if(response == "-1") // nonce timeout
						{
                            $.AviaModal.openInstance[0].close();
                            new $.AviaModalNotification({mode:'error', msg:avia_modal_L10n.timeout});
						}
						else
						{
							inner.html(response);
							obj.on_load_callback();
						}
					},
					complete: function(response)
					{	
						inner.removeClass('preloading');
					}
				});
   		},
   		
   		on_load_callback: function()
   		{	
   			var callbacks = this.options.on_load,
   				execute, index = 0;
   				
   			if(typeof callbacks == 'string')
   			{
   				execute = callbacks.split(", ");
   				for(index in execute)
   				{
   					if(typeof $.AviaModal.register_callback[execute[index]] != 'undefined')
   					{
   						$.AviaModal.register_callback[execute[index]].call(this);
   					}
   					else
   					{
   						avia_log('modal_on_load function "$.AviaModal.register_callback.'+execute[index]+'" not defined','error');
   						avia_log('Make sure that the modal_on_load function defined in your Shortcodes config array exists','help');
   					}
   				}
   				
   			}
   			else if(typeof callbacks == 'function')
   			{
   				callbacks.call();
   			}
   			
   			this.set_focus();
   			this.propagate_modal_content();
   		},
   		
   		close: function()
   		{
   			$.AviaModal.openInstance.shift(); //remove the first entry from the openInstance array 
   		
   			this.doc.trigger('avia_modal_before_close', [ this ]); 
   			this.doc.trigger('avia_modal_before_close_instance'+this.namespace, [ this ]); 
   			
   			this.modal.remove();
   			this.backdrop.remove();
   			this.doc.trigger('avia_modal_close', [ this ]).off('keydown'+this.namespace); 
   			
   			if($.AviaModal.openInstance.length == 0)
   			{
   				this.body.removeClass('avia-noscroll');
   			}
   		},
   		
   		convert_values: function(a)
   		{	
   			var o = {};
   			$.each(a, function() 
   			{
   			
		       if (typeof o[this.name] !== 'undefined') 
		       {		       
		           if (!o[this.name].push) 
		           {
		               o[this.name] = [o[this.name]];
		           }
		           o[this.name].push(this.value || '');
		       } 
		       else 
		       {
		           o[this.name] = this.value || '';
		       }
		   });
		   
		   return o;
   		},
   		
   		get_final_values: function()
   		{
	   		var values = this.modal.find('input, select, radio, textarea').serializeArray(), 
   				value_array = this.convert_values(values);
   				
   				//filter function for the value array in case we got a special shortcode like tables
   				if(typeof $.AviaModal.register_callback[this.options['before_save']] != 'undefined')
   				{
   					value_array = $.AviaModal.register_callback[this.options['before_save']].call(this.options.scope, value_array, this.options.save_param);
   				}
   			
   			return value_array;
   		},
   		
   		execute_callback: function()
   		{	
   			var value_array 	= this.get_final_values();		
   			var close_allowed 	= this.options['on_save'].call(this.options.scope, value_array, this.options.save_param);
   			
   			if(close_allowed !== false)
   			{
   				this.close();
   		    }
   		},
   		
   		media_overlay_closed: function()
   		{
   			return $.AviaElementBehavior.wp_media.length ? false : true;
   		},
   		
   		link_overlay_closed: function() //check if the tinymce link editor for wordpress (Insert/edit link button) is closed
   		{
   			var link_overlay = $('#wp-link-wrap:visible');
   			return link_overlay.length ? false : true;
   		},
   		
   		propagate_modal_open: function()
   		{
   			this.body.trigger('avia_modal_open', this);
   		},
   		
   		propagate_modal_content: function()
   		{
   			this.body.trigger('avia_modal_finished', this);
   		}
   	
   	};



	//wrapper for small modal notifications
	
	$.AviaModalNotification = function(options)
	{
		var defaults = {
		
        		modal_content: "<div class='avia-form-element-container'>" + options.msg + "</div>",				
        		modal_class: "flexscreen",				
        		modal_title: "<span class='avia-msg-"+ options.mode +"'>" + avia_modal_L10n[options.mode] + "</span>",
        		button: "close"		
        };
		
		this.options = $.extend({}, defaults, options);
		return new $.AviaModal(this.options);
	};
   	
   	

   	
   	//allowed callbacks once the popup opens
   	
   	$.AviaModal.register_callback = $.AviaModal.register_callback || {};
   	
   	//gets overwritten by the tab toggle function
   	$.AviaModal.register_callback.modal_start_sorting = function(passed_scope)
	{
		var scope	= passed_scope || this.modal,
			target	= scope.find('.avia-modal-group'),
			params	= {
					handle: '.avia-attach-modal-element-move',
					items: '.avia-modal-group-element',
					placeholder: "avia-modal-group-element-highlight",
					tolerance: "pointer",
					//axis: 'y',
					forcePlaceholderSize:true,
					start: function( event, ui ) 
					{
						$('.avia-modal-group-element-highlight').height(ui.item.outerHeight()).width(ui.item.outerWidth());
					},
					update: function(event, ui) 
					{
						//obj.updateTextarea();
					},
					stop: function( event, ui ) 
					{
						//obj.canvas.removeClass('avia-start-sorting');
					}
				};
			
			target.find('.avia-modal-group-element, .avia-insert-area').disableSelection();	
			target.sortable(params);
			
			
	};
   	
   	
   	
   	$.AviaModal.register_callback.modal_load_colorpicker = function()
	{
		var palettes = ['#000000','#ffffff','#B02B2C','#edae44','#eeee22','#83a846','#7bb0e7','#745f7e','#5f8789','#d65799','#4ecac2'];
		if( 'undefined' != typeof avia_globals.color_palettes )
		{
			palettes = avia_globals.color_palettes;
		}
		
		var picerOpts 		= {
				palettes: palettes,
				change: function(event, ui)
				{
					$(this).trigger('av-update-preview');
				},
				clear: function() {
	            	$(this).trigger('keyup');
				}
			},
			self			= this, 
			scope			= this.modal,
			colorpicker		= scope.find('.av-colorpicker').avia_wpColorPicker(picerOpts), 
			picker_button	= scope.find('.wp-color-result');
			//picker_button.off();
			
			//
						
			colorpicker.click(function(e)
			{
				var picker  = $(this),
					parent 	= $(this).parents('.wp-picker-container:eq(0)'),
					button 	= parent.find('.wp-color-result'),
					iris	= parent.find('.wp-picker-holder .iris-picker');
					
				if(!button.hasClass('wp-picker-open')) button.addClass('wp-picker-open');
				if(iris.css('display') != "block") iris.css({display:'block'});
				scope.find('.wp-picker-open').not(button).trigger('click');
				
				$( 'body' ).one( 'click', function(e)
				{
					if(iris.css('display') == "block") iris.css({display:'none'});
					if(button.hasClass('wp-picker-open')) button.removeClass('wp-picker-open');
				} );
				
			});
			
			picker_button.click(function(e)
			{
				//var parent 	= $(this).parents('.wp-picker-container:eq(0)'),
				//	picker  = parent.find('.av-colorpicker').trigger('click');
					
			
				if(typeof e.originalEvent != "undefined")
				{
					var open = scope.find('.wp-picker-open').not(this).trigger('click');
				}
				
			});
			
			//fixes the error caused by removing the modal window from the dom. unbinding the events and recalling the iris function both seems to be necessary
			$(document).one('avia_modal_before_close_instance'+self.namespace, function()
			{
				picker_button.off().remove();
				colorpicker.off().remove();
				colorpicker.iris();
			});
			
	};
   	
   	
   	$.AviaModal.register_callback.modal_load_datepicker = function()
	{
		var scope			= this.modal,
			datepickers		= scope.find('.av-datepicker'),
			supported		= [ 'showButtonPanel', 
								'closeText', 
								'currentText', 
								'nextText', 
								'prevText', 
								'monthNames', 
								'monthNamesShort', 
								'dayNames', 
								'dayNamesShort', 
								'dayNamesMin', 
								'dateFormat', 
								'firstDay', 
								'isRTL', 
								'changeMonth', 
								'changeYear', 
								'yearRange', 
								'minDate', 
								'maxDate' 
								],
			array_structur	= [
								'monthNames', 
								'monthNamesShort', 
								'dayNames', 
								'dayNamesShort', 
								'dayNamesMin'
								];
		
						    
		datepickers.each(function()
		{		
			var	input = $(this),
				settings = input.data(),
				options = {};
				
			$.each( supported, function( index, value ){
				var lc = value.toLowerCase(),
					val = '';
					
				if( 'undefined' == typeof settings[lc] )
				{
					return;
				}
				
				if( $.inArray( value, array_structur ) >= 0 )
				{
					val = settings[lc].split(',');
					val.map( function(s) { return s.trim() });
				}
				else
				{
					val = settings[lc];
				}
				
				options[ value ] = val;
			});
			
			options.beforeShow = function(input, inst) 
									{
										inst.dpDiv.addClass("avia-datepicker-div");
										if( 'undefined' != typeof settings.container_class )
										{
											inst.dpDiv.addClass( settings.container_class );
										}
									};
			
			input.datepicker( options );
		});
		
	};
	
	$.AviaModal.register_callback.modal_load_multi_input = function()
	{
		var scope			= this.modal,
			containers		= scope.find('.avia-element-multi_input');
			
			containers.each(function()
			{
				var container 	= $(this),
					input_first	= container.find('input[type="text"]:first'),
					follow_ups	= container.find('input[type="text"]:not(:first)'),
					sync		= container.find('input[type="checkbox"]'),
					values		= "";
					
					if(sync.length)
					{
						input_first.on('keyup', function()
						{
							if(sync.is(':checked')) follow_ups.attr('value', input_first.val());
						});
						
						sync.on('change', function()
						{
							if(!sync.is(':checked'))
							{
								follow_ups.prop("disabled", false);
							}
							else
							{
								follow_ups.prop("disabled", true);
								follow_ups.attr('value', input_first.val());
							}
						});
					}
			});
			
			
	};
	
	
	$.AviaModal.register_callback.modal_load_tabs = function()
	{
		var scope			= this.modal,
			modal_instance	= this,
			tabcontainer	= scope.find( '.avia-modal-tab-container' ),
			tabs			= tabcontainer.find( '.avia-modal-tab-container-inner' ),
			title_container = $( '<div class="avia-modal-tab-titles"></div>' ).prependTo( tabcontainer ),
			active 			= "active-modal-tab",
			group_element	= null;
	
			if( this.has_modal_popup_state )
			{
				group_element = this.options.save_param.hasClass( 'avia-modal-group-element' );
			}
			
			tabs.each( function(i)
			{
				var current 	= $(this),
					tab_title 	= current.data('tab-name'),
					title_link  = $("<a href='#'>"+tab_title+"</a>").appendTo(title_container);
					
					if( i === 0 )
					{
						title_link.addClass(active);
						tabs.css({display:"none"});
						current.css({display:"block"});
					}
					
					title_link.on( 'click', function(e)
					{
						var clicked = $(this);
						
						if( modal_instance.has_modal_popup_state )
						{
							var option = group_element ? 'group_tab_text' : 'tab_text';
							$.avia_builder.set_modal_popup_state( option, tab_title, modal_instance );
						}
						
						//hide prev
						title_container.find('a').removeClass(active);
						tabs.css({display:"none"});
						
						//show current
						clicked.addClass(active);
						current.css({display:"block"});
						
						//prevent default
						return false;
					});	
				
			});
	};
	

	$.AviaModal.register_callback.modal_load_toggles = function()
	{	
		var scope			= this.modal,
			modal_instance	= this,
			tabcontainer	= scope.find( '.avia-modal-toggle-container' ).addClass( 'avia-modal-toggle-ready' ),
			group_element	= null,
			active 			= "active-modal-toggles",
			svg_arrow		= '<svg class="avia-modal-toggle-arrow" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><g><path fill="none" d="M0,0h24v24H0V0z"></path></g><g><path d="M7.41,8.59L12,13.17l4.59-4.58L18,10l-6,6l-6-6L7.41,8.59z"></path></g></svg>';
			
			if( this.has_modal_popup_state )
			{
				group_element = this.options.save_param.hasClass( 'avia-modal-group-element' );
			}
			
			tabcontainer.each( function(i)
			{
				var current_container 	= $(this),
					all_closed	 		= current_container.data( 'toggles-closed' ),
					layout				= current_container.data( 'toggles-layout' ),
					tabs				= current_container.find( '.avia-modal-toggle-container-inner' ),
					initial_is_open		= false;
				
				all_closed = 'string' == typeof( all_closed ) &&  $.inArray( all_closed, ['yes', 'no'] ) >= 0 ? all_closed : 'no';
				if( all_closed == 'yes' )
				{
					initial_is_open = true;
				}
				
				layout = 'string' == typeof( layout ) &&  $.inArray( layout, ['', 'section_headers', 'no_section_headers'] ) >= 0 ? layout : '';
				
				tabs.each( function(i)
				{
					var current 	= $(this),
						tab_title 	= current.data('toggle-name'),
						tab_desc 	= current.data('toggle-desc'),
						tab_content = current.find('.avia-form-element-container').not( '.avia-hidden' ),
						title_link  = $("<a class='avia-modal-toggle-title' href='#'>"+svg_arrow+tab_title+"<span>"+tab_desc+"</span></a>").insertBefore(current),
						modal		= current.closest( '.avia-modal' );
						
					if( ! initial_is_open && tab_content.length > 0 && layout == '' )
					{
						tabs.css({display:"none"});
						title_link.addClass( active );
						current.css({display:"block"});
						initial_is_open = true;
					}
						
					title_link.on( 'click', function(e)
					{
						e.preventDefault();
						
						var clicked = $(this),
							is_active = clicked.hasClass( active ) ? true : false,
							state_option = group_element ? 'group_toggle_text' : 'toggle_text';
							
						if( modal_instance.has_modal_popup_state )
						{
							$.avia_builder.set_modal_popup_state( state_option, tab_title, modal_instance );
						}
						
						if( layout != '' )
						{
							return;
						}

						//hide prev
						current_container.find('a').removeClass(active);
						tabs.css({display:"none"});

						if( ! is_active )
						{
							//show current
							clicked.addClass(active);
							current.css({display:"block"});
						}

					});	
	
					modal.on( 'change', function( e )
					{	
						setTimeout( function()
						{
							var is_visible = true;
							var visibles = current.find( '.avia-form-element-container' ).not( '.avia-hidden' );
							var wrap = current.closest( '.avia-modal-toggle-visibility-wrap' );

							//	check if element is hidden by a highest parent element
							visibles.each(function(i)
							{
								var cur = $(this);
								var parent = cur.parent();
								while( ! parent.hasClass( 'avia-modal-toggle-container-inner' ) )
								{
									if( parent.hasClass( 'avia-form-element-container' ) )
									{
										is_visible = ! parent.hasClass( 'avia-hidden' );
									}

									parent = parent.parent();
								}

								if( is_visible )
								{
									return false;
								}
							});

							if( is_visible && visibles.length == 0 )
							{
								is_visible = false;
							}

							if( ! is_visible )
							{
								wrap.addClass( 'avia-hidden' );
							}
							else
							{
								wrap.removeClass( 'avia-hidden' );
							}

						}, 500 );

					});

					setTimeout( function(){ modal.trigger( 'change' ); }, 100 );

				});
			});
			
	};
	
	
	$.AviaModal.register_callback.modal_load_iconswitcher = function()
	{	
		var scope			= this.modal,
			tabcontainer	= scope.find('.avia-modal-iconswitcher-container').addClass('avia-modal-switcher-ready'),
			active 			= "active-modal-icon-switcher";
			

			tabcontainer.each(function(i)
			{
				
				var current_container 	= $(this),
					title_container		= null,
					tabs				= current_container.find('.avia-modal-iconswitcher-container-inner'),
					titles				= $('<div class="avia-modal-iconswitcher-titles"></div>'),
					desc				= current_container.find('.avia-iconswitcher-name-description');
					
				if( desc.length == 0 )
				{
					title_container = titles.prependTo(current_container);
				}
				else
				{
					title_container = titles.insertAfter( desc );
				}
				
				tabs.each(function(i)
				{
					var current 	= $(this),
						tab_title 	= current.data('switcher-name'),
						tab_icon 	= current.data('switcher-icon'),
						title_link  = $("<a class='avia-modal-iconswitcher-title' href='#'><span><img src='"+tab_icon+"' /></span><strong>"+tab_title+"</strong></a>").appendTo(title_container);
						
					if( i === 0 )
					{
						tabs.css({display:"none"});
						title_link.addClass(active);
						current.css({display:"block"});

					}
						
					title_link.on('click', function(e)
					{
						var clicked = $(this);

						//hide prev
						current_container.find('a').removeClass(active);
						tabs.css({display:"none"});

						//show current
						clicked.addClass(active);
						current.css({display:"block"});

						//prevent default
						return false;
					});	
					
				});

			});
			
	};
	
	
	
	
	$.AviaModal.register_callback.modal_load_mailchimp = function()
	{	
		// var that contains all list data: av_mailchimp_list
		var scope			= this.modal,
			list			= scope.find('.avia-element-mailchimp_list select'),
			group			= scope.find('.avia-modal-group'),
			items			= group.find('.avia-modal-group-element'),
			single			= scope.find('.avia-tmpl-modal-element').html(),
			shortcode_name  = "av_mailchimp_field",
			value			= list.val(),
			generated_lists = [],
			key,
			insert_item 	= function(current, where)
			{
				var shortcode	= "",
					textarea	= "",
					insert		= $(single);
				
				
				//sanitize data in dropdown to circumvent inserting world list with "'" or other invalid values
				if(current.label)
				{
					current.label = current.label.replace(/'/g, "&lsquo;");
				}
				
				if(current.options)
				{
					current.options = current.options.replace(/'/g, "&lsquo;");
				}
				
				if(current.value)
				{
					current.value = current.value.replace(/'/g, "&lsquo;");
				}
				
				textarea = insert.find('textarea');
				shortcode = $.avia_builder.createShortcode(current, shortcode_name, {}, false );
				textarea.html(shortcode);
				$.avia_builder.update_builder_html(insert, current, false );
				
				if(where == "prepend")
				{
					group.prepend(insert);
				}
				else
				{
					group.append(insert);
				}
			};
			
			
			//if the list is empty remove all fields
			if(value == "")
			{
				group.html("");
			}
			else
			{
				//when opening the also check if the current list is up to date. remove any deprecated items and add new ones if necessary
				if( av_mailchimp_list[value] )
				{	
					var currentList = av_mailchimp_list[value],
						searchFor	= {};
					
					//remove deprecated items
					items.each(function()
					{
						var this_item 	= $(this),
							this_id		= this_item.find('[data-update_class_with="id"]').attr('class'),
							this_key	= this_id.replace("avia-id-", "");
							
							if(!isNaN(this_key))
							{
								this_key = parseInt( this_key , 10);
								
								if(!currentList[this_key]) // remove if deprecated
								{
									this_item.remove();
								}
								else //upate if the "check" condition has changed
								{
									var value_textarea	 = this_item.find('textarea'),
										shortcode_string = value_textarea.val(),
										regex			 = new RegExp(/check=['|"](.*?)['|"]/),
										shortcode_val	 = regex.exec(shortcode_string);
										
										if( shortcode_val[1] != currentList[this_key]['check'] )
										{
											shortcode_string = shortcode_string.replace(regex, "check='"+currentList[this_key]['check']+"'");
											this_item.find('[data-update_class_with="check"]').removeClass().addClass('avia-check-' + currentList[this_key]['check']);
											
											if(currentList[this_key]['check'] != "")
											{
												regex = new RegExp(/disabled=['|"](.*?)['|"]/);
												shortcode_string = shortcode_string.replace(regex, "disabled=''");
												this_item.find('[data-update_class_with="disabled"]').removeClass();
											}
										}
										
										value_textarea.html( shortcode_string );
								}
							}
					});
					
					
					// add new items
					for(key in currentList)
					{
						searchFor = group.find('.avia-id-' + currentList[key]['id']);
						
						if( !searchFor.length )
						{
							insert_item(currentList[key], 'prepend');
						}
					}
					
				}
			}
			
			//when the user changed the dropdown menu
			list.on('change', function()
			{
				if(value != "")
				{
					//store the current setup so that if the user changes between items it always displays the last edited version
					generated_lists[value] = group.html();
				}
				
				group.html("");			
				
				value = list.val();
				
				if( generated_lists[value] )
				{
					group.append(generated_lists[value]);
				}
				else if( av_mailchimp_list[value] )
				{
					for(key in av_mailchimp_list[value])
					{
						insert_item(av_mailchimp_list[value][key])
					}
					
				}
				
			});
			
			
	};
   	
   	

   	//once a modal with tinyMCE editor is opened execute the following function
	$.AviaModal.register_callback.modal_load_tiny_mce = function(textareas)
	{
		textareas = textareas || this.modal.find('.avia-modal-inner-content .avia_tinymce');
		
		var _self	 = this,	
			modal    = textareas.parents('.avia-modal:eq(0)'),
			save_btn = modal.find('.avia-modal-save'),
			$doc	 = $(document),
			no_indent_fix = $( '#avia_builder' ).find('.avia-builder-main-wrap').first().hasClass( 'avia-ignore-tiny-indent-fix' );

		textareas.each(function()
		{
			var el_id		= this.id,
				current 	= $(this), 
				parent		= current.parents('.wp-editor-wrap:eq(0)'),
				textarea	= parent.find('textarea.avia_tinymce'),
				switch_btn	= parent.find('.wp-switch-editor').removeAttr("onclick"),
				settings	= {
									id: this.id , 
									buttons: "strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close",
									menubar :	false
							},
				tinyVersion = false,
				executeAdd  = "mceAddEditor",
				executeRem	= "mceRemoveEditor",
				open		= true;
			
			
			if(window.tinyMCE) tinyVersion = window.tinyMCE.majorVersion;
			
			// add quicktags for text editor
			quicktags(settings);
			QTags._buttonsInit(); //workaround since dom ready was triggered already and there would be no initialization
			
			// modify behavior for html editor
			switch_btn.on('click', function()
			{
				var button = $(this);

				if(button.is('.switch-tmce'))
				{
					parent.removeClass('html-active').addClass('tmce-active');
					window.tinyMCE.execCommand(executeAdd, true, el_id);

					/**
					 * fixes problem with caption shortcode that manipulates the HTML and adds some custom temp structure on adding content to editor
					 * see  wp-includes\js\tinymce\plugins\wpeditimage\plugin.js function parseShortcode 
					 * 
					 * Check in future releases, that tinyMCE events "beforeGetContent" and "BeforeSetContent" and "PostProcess" do not require some special format to execute. 
					 * Currently  event.format !== 'raw' is used
					 */
					var text = textarea.val();
					var result = text.match( /\[caption /i );
					var format = ( result ) ? 'wpeditimage' : 'raw';
					
					/**
					 * Fixes a problem with lists: indent/dedent with tab and button does not work because there must be no space between list tags when rendering content
					 */
					var text_p = window.switchEditors.wpautop( text );
					
					if( ! no_indent_fix )
					{
						text_p = text_p.replace( />[.\s]+<li>/g, '><li>' );
						text_p = text_p.replace( />[.\s]+<li /g, '><li ' );
						text_p = text_p.replace( />[.\s]+<\/ul>/g, '></ul>' );
						text_p = text_p.replace( />[.\s]+<\/ol>/g, '></ol>' );
						text_p = text_p.replace( /<\/ul>[.\s]+<\/li>/g, '</ul></li>' );
						text_p = text_p.replace( /<\/ol>[.\s]+<\/li>/g, '</ol></li>' );
					}
					
					window.tinyMCE.get(el_id).setContent( text_p, {format: format });
					
					//trigger updates for preview window
					tinymce.activeEditor.on('keyup change', function(e) 
					{	
						var content_to_send = textarea.val();
						if(window.tinyMCE.get(el_id))
						{	
							/*fixes the problem with galleries and more tag that got an image representation of the shortcode*/
							content_to_send = window.tinyMCE.get(el_id).getContent();
						}
						
						//trigger tinymce update event and send the actual content that is located in the textarea
						textarea.trigger("av-update-preview-tinymce", window.switchEditors._wp_Nop( content_to_send ) );
					});
					
				}
				else
				{
					var the_value = textarea.val();
					if(window.tinyMCE.get(el_id))
					{	
						/*fixes the problem with galleries and more tag that got an image representation of the shortcode*/
						the_value = window.tinyMCE.get(el_id).getContent();
					}
					
					parent.removeClass('tmce-active').addClass('html-active');
					window.tinyMCE.execCommand(executeRem, true, el_id);
					textarea.val( window.switchEditors._wp_Nop( the_value ) );
				}
			});
			
			
			//activate the visual editor
			switch_btn.filter('.switch-tmce').trigger('click');
			
			//make sure that when the save button is pressed the textarea gets updated and sent to the editor
			save_btn.on('click', function()
			{
				switch_btn.filter('.switch-html').trigger('click');
			});
			
			

			//make sure that the instance is removed if the modal was closed in any way
			$doc.on('avia_modal_before_close' + _self.namespace + "tiny_close", function(e, modal)
			{
				if(_self.namespace == modal.namespace)
				{
					if(window.tinyMCE) window.tinyMCE.execCommand(executeRem, true, el_id); 
					$doc.off('avia_modal_before_close'  + _self.namespace + "tiny_close"); 
				}
			});
			
		});
	};
	
	
	
	//helper function that makes hotspots dragable and saves their values
   	$.AviaModal.register_callback.modal_hotspot_helper = function()
	{
		//check if we got a hotspot element. if not return
		var _self = {}, methods = {};
		_self.hotspot =	this.modal.find('.av-hotspot-container');
		
		if(! _self.hotspot.length ) return;
		
		//container that wraps around the image
		_self.image_container 	=	_self.hotspot.find('.avia-builder-prev-img-container-wrap');
		
		//container that is used to insert and track hotspots
		_self.hotspot_container =	$('<div class="av-hotspot-holder"></div>').appendTo(_self.image_container);
		
		//modal group with options to click on that open submodals
		_self.modal_group 		=	_self.hotspot.siblings('.avia-element-modal_group');
		
		
		
		//html for hotspot
		_self.hotspot_html 		= "<div class='av-image-hotspot'><div class='av-image-hotspot_inner'>{count}</div></div>";
   		
   		//all the functions needed for the hotspot tool
   		methods = {
   		
   			/*iterate over each modal groub subel and create a hotspot*/
   			init: function()
   			{
   				//fetch all existing modal group elements
   				methods.find_sub_els();
   			
   				//generate a hotspot for each element
   				_self.modal_group_els.each(function(i)
   				{
   					var $sub_el = $(this);
   					methods.create_hotspot($sub_el, i);
   				});
   				
   				methods.general_behavior();
   				
   			},
   			
   			find_sub_els: function()
   			{
   				//the modal group elements
				_self.modal_group_els 	=	_self.modal_group.find('.avia-modal-group-element');
   			},
   			
   			/*create hotspot and add individual behavior*/
   			create_hotspot: function($sub_el, i)
   			{
				var hotspot = $(_self.hotspot_html.replace("{count}", (i+1) )).appendTo(_self.hotspot_container),
					pos		= $sub_el.find("[data-hotspot_pos]").data('hotspot_pos').split(",");
					
					if(pos[1]){
						hotspot.css({top: pos[0] + "%", left: pos[1] + "%"});
					}
					
					methods.hotspot_behavior(hotspot, $sub_el);
   			},
   			
   			/*connect hotspot and modalsub element by using data method, make hotspot draggable*/
   			hotspot_behavior: function(hotspot, $sub_el)
   			{
   				//connect hotspot and modalsub element
   				$sub_el.data('hotspot', hotspot);
   				hotspot.data('modal_sub_el', $sub_el);
   				
   				//make hotspot draggable
   				hotspot.draggable({
					containment: "parent", 
					scroll: false,
					grid: [ 5, 5 ],
					stop: methods.update_hotspot
				});
   			},
   			
   			/*add behavior that connects hotspot and modal subelements*/
   			general_behavior: function()
   			{
   				/*trigger click event*/
   				_self.hotspot_container.on('click', '.av-image-hotspot', function()
   				{
   					var el = $(this).data('modal_sub_el');
   					if(el) el.find('.avia-modal-group-element-inner').trigger('click');
   				});
   				
   			
   				/*highlight the modal sub el when hotspot is hovered*/
   				_self.hotspot_container.on('mouseenter', '.av-image-hotspot', function()
   				{
   					var el = $(this).data('modal_sub_el');
   					if(el) el.addClass('av-highlight-subel');
   				});
   				
   				_self.hotspot_container.on('mouseleave', '.av-image-hotspot', function()
   				{
   					var el = $(this).data('modal_sub_el');
   					if(el) el.removeClass('av-highlight-subel');
   				});
   				
   				/*highlight the hotspot when modal sub el is hovered*/
   				_self.modal_group.on('mouseenter', '.avia-modal-group-element', function()
   				{
   					var el = $(this).data('hotspot');
   					if(el) el.addClass('active_tooltip');
   				});
   				
   				_self.modal_group.on('mouseleave', '.avia-modal-group-element', function()
   				{
   					var el = $(this).data('hotspot');
   					if(el) el.removeClass('active_tooltip');
   				});
   				
   				/*add and remove items*/
   				_self.modal_group.on('av-item-add', 	methods.add_hotspot );
   				_self.modal_group.on('av-item-delete', 	methods.delete_hotspot );
   				_self.modal_group.on('av-item-moved', 	methods.update_hotspot_numbers );
   				

   			},
   			
   			add_hotspot: function(event, item)
   			{
   				methods.create_hotspot(item, 0);
   				methods.update_hotspot_numbers();
   			},
   			
   			delete_hotspot: function(event, item)
   			{
   				var hotspot = item.data('hotspot');
   				if(hotspot) { hotspot.remove(); setTimeout(methods.update_hotspot_numbers, 350); }
   			},
   			
   			update_hotspot_numbers: function()
   			{
   				methods.find_sub_els();
   				
   				_self.modal_group_els.each(function(i)
   				{
   					var el = $(this).data('hotspot');
   					if(el) el.find('.av-image-hotspot_inner').text(i+1);
   				});
   				
   			},
   			
   			/*calculates % based position and applies it to the hotspot*/
   			update_hotspot: function(event, hotspot)
   			{
   				var image_el = _self.image_container.find('img');
   				if(!image_el.length) return;
   				
   				var image_dimensions  	= {width: image_el.width(), height: image_el.height()},
   					hotspot_pixel_pos 	= hotspot.position,
   					hotspot_percent_pos = {top:0, left:0};
   				
   				//calculate % position
   				hotspot_percent_pos.left = hotspot.position.left / (image_dimensions.width / 100);
   				hotspot_percent_pos.top = hotspot.position.top / (image_dimensions.height / 100);
   				
   				//round to 1 decimal
   				hotspot_percent_pos.left = Math.round( hotspot_percent_pos.left * 10 ) / 10;
   				hotspot_percent_pos.top  = Math.round( hotspot_percent_pos.top * 10  ) / 10;
   				
				//set the helper to this value
				hotspot.helper.css({top: hotspot_percent_pos.top + "%", left: hotspot_percent_pos.left + "%"});
				
				methods.update_shortcode(hotspot_percent_pos, hotspot.helper);
   			},
   			
   			/*fetches the shortcode of the modal sub element and changes it by replacing the old hotspot_pos value with the new one*/
   			update_shortcode: function(hotspot_percent_pos, hotspot)
   			{
   				var shortcode_container = hotspot.data('modal_sub_el'),
   					shortcode_storage	= shortcode_container.find('textarea'),
   					shortcode			= shortcode_storage.val();
   				
   				//test if the hotspot_pos parameter is located in the shortcode and replace it. if not available add it
   				if (shortcode.indexOf('hotspot_pos') > -1) 
   				{
   					shortcode = shortcode.replace(/hotspot_pos=['|"].*?['|"]/g,"hotspot_pos='"+hotspot_percent_pos.top+","+hotspot_percent_pos.left+"'");
   				}
   				else
   				{
   					shortcode = shortcode.replace(/av_image_spot/,"av_image_spot hotspot_pos='"+hotspot_percent_pos.top+","+hotspot_percent_pos.left+"'");
   				}
   				
   				shortcode_storage.val(shortcode).html(shortcode);
   			}
   		};
   		
   		methods.init();
   		
   	};
	
	
	//script that generates the preview
	$.AviaModal.register_callback.modal_preview_script = function()
	{
		var _self	 			= this,
			preview_heading		= _self.modal.find('.avia-modal-preview-header'),
			iframe_container	= _self.modal.find('.avia-modal-preview-content'),
			preview_footer		= _self.modal.find('.avia-modal-preview-footer'),
			preview_bg_stored	= _self.modal.find('#aviaTBadmin_preview_bg'),
			preview_scale       = "avia-preview-scale-" + iframe_container.attr('data-preview-scale'),
			iframe				= false,
			elements			= _self.modal.find('input, select, radio, textarea'),
			res					= window.avia_preview.paths,
			errorMsg			= window.avia_preview.error,
			delay				= 400,
			timeout				= false,
			xhr					= false,
			newframe			= false,
			iframe_content		= false,
			methods				= {};
				
		methods = 
		{	
			change_preview_bg: function(e)
			{				
				e.preventDefault();
								
				var color = e.currentTarget.style.background;
				
				iframe_container.css('background',color);
				preview_bg_stored.val(color);
			},
		
			update_iframe_with_delay: function(e, content)
			{
				clearTimeout(timeout);
			
				timeout = setTimeout( function()
				{
					if(e.type == "av-update-preview-tinymce")
					{
						e.currentTarget.value = content;
					}
				
					methods.update_iframe();
					
				}, delay);
			},
		
			update_iframe: function()
			{
				var value_array = _self.get_final_values();
				var shortcode = _self.options['on_save'].call(_self.options.scope, value_array, _self.options.save_param.clone(), 'return');
				
				preview_heading.addClass('loading');
				
				xhr = $.ajax({
					type: "POST",
					url: ajaxurl,
					data: 
					{
						action: 'avia_ajax_text_to_preview',
						text: shortcode,
						avia_request: true,
						_ajax_nonce: $('#avia-loader-nonce').val()
					},
					success: function(response)
					{
						methods.set_frame_content( response );
					},
					complete: function()
					{
						preview_heading.removeClass('loading');
					}
				});
				
			},
			
			set_frame_content: function( response )
			{
				
				if( response.indexOf("[" + _self.options.ajax_param.allowed) !== -1 || response.indexOf(_self.options.ajax_param.allowed + "]") !== -1 )
				{
					response = "<div class='avia-preview-error'>" + errorMsg + "</div>";
				}
				
				if(newframe == false)
				{
					newframe = document.createElement('iframe');
					iframe_container.html("").append(newframe);
				
					response = "<html class='responsive'><head>" +res+ "</head><body id='top'><div id='wrap_all'><div id='av-admin-preview' class='entry-content-wrapper main_color all_colors "+preview_scale+"'>" +response+ "</div></div></body></html>";
					
					newframe.contentWindow.contents = response;
					newframe.src = 'javascript:window["contents"]'; 
					newframe.onload = function()
					{
						iframe_content = $(newframe).contents().find("#av-admin-preview");
					};
				}
				else
				{
					if(iframe_content.length) //check if the frame still exists. window might be closed already
					{
						iframe_content.html(response);
					}
				}
			}
			
		};
		
		methods.set_frame_content("");
		methods.update_iframe();
		
		//preset bg color
		if(preview_bg_stored.val() != "") { iframe_container.css('background',preview_bg_stored.val()); }
		
		
		_self.modal.on('av-update-preview-instant change', 'input, select, radio, textarea', methods.update_iframe);
		_self.modal.on('av-update-preview keyup', 'input, select, radio, textarea', methods.update_iframe_with_delay);
		_self.modal.on('av-update-preview-tinymce', 'textarea', methods.update_iframe_with_delay);
		preview_footer.on('click', 'a', methods.change_preview_bg);
		
	};
	
})(jQuery);	 








/**
 *
 * Modified version of the Codestar WP Color Picker v1.1.0
 *
 * Copyright 2015 Codestar <info@codestarlive.com>
 * GNU GENERAL PUBLIC LICENSE (http://www.gnu.org/licenses/gpl-2.0.txt)
 *
 */
;(function ( $, window, document, undefined ) {
  'use strict';

  // adding alpha support for Automattic Color.js toString function.
  if( typeof Color.fn.toString !== undefined ) {

    Color.fn.toString = function () {

      // check for alpha
      if ( this._alpha < 1 ) {
        return this.toCSS('rgba', this._alpha).replace(/\s+/g, '');
      }

      var hex = parseInt( this._color, 10 ).toString( 16 );

      if ( this.error ) { return ''; }

      // maybe left pad it
      if ( hex.length < 6 ) {
        for (var i = 6 - hex.length - 1; i >= 0; i--) {
          hex = '0' + hex;
        }
      }

      return '#' + hex;

    };

  }

  $.avia_ParseColorValue = function( val ) {

    var value = val.replace(/\s+/g, ''),
        alpha = ( value.indexOf('rgba') !== -1 ) ? parseFloat( value.replace(/^.*,(.+)\)/, '$1') * 100 ) : 100,
        rgba  = ( alpha < 100 ) ? true : false;

    return { value: value, alpha: alpha, rgba: rgba };

  };

  $.fn.avia_wpColorPicker = function( default_options ) {
  	
    return this.each(function() {
		
      var $this = $(this);

      // check for rgba enabled/disable
      if( $this.data('av-rgba') == true ) {

        // parse value
        var picker = $.avia_ParseColorValue( $this.val() );

        // wpColorPicker core
        var new_settings = {

          // wpColorPicker: clear
          clear: function() {
            $this.trigger('keyup');
          },

          // wpColorPicker: change
          change: function( event, ui ) {

            var ui_color_value = ui.color.toString();

            $this.closest('.wp-picker-container').find('.av-alpha-slider-offset').css('background-color', ui_color_value);
            $this.val(ui_color_value).trigger('change');
            
            $(this).trigger('av-update-preview');

          },

          // wpColorPicker: create
          create: function() {

            // set variables for alpha slider
            var a8cIris       = $this.data('a8cIris'),
                $container    = $this.closest('.wp-picker-container'),
                $irisP		  = $container.find('.iris-picker').addClass('av-iris-picker-rgba'),

                // appending alpha wrapper
                $alpha_wrap   = $('<div class="av-alpha-wrap">' +
                                  '<div class="av-alpha-slider"></div>' +
                                  '<div class="av-alpha-slider-offset"></div>' +
                                  '<div class="av-alpha-text"></div>' +
                                  '</div>').appendTo( $irisP ),

                $alpha_slider = $alpha_wrap.find('.av-alpha-slider'),
                $alpha_text   = $alpha_wrap.find('.av-alpha-text'),
                $alpha_offset = $alpha_wrap.find('.av-alpha-slider-offset');
			
			$irisP.height( $irisP.height() + 37 );
			
            // alpha slider
            $alpha_slider.slider({

              // slider: slide
              slide: function( event, ui ) {

                var slide_value = parseFloat( ui.value / 100 );

                // update iris data alpha && wpColorPicker color option && alpha text
                a8cIris._color._alpha = slide_value;
                $this.wpColorPicker( 'color', a8cIris._color.toString() );
                $alpha_text.text( ( slide_value < 1 ? slide_value : '' ) );

              },

              // slider: create
              create: function() {

                var slide_value = parseFloat( picker.alpha / 100 ),
                    alpha_text_value = slide_value < 1 ? slide_value : '';

                // update alpha text && checkerboard background color
                $alpha_text.text(alpha_text_value);
                $alpha_offset.css('background-color', picker.value);

                // wpColorPicker clear for update iris data alpha && alpha text && slider color option
                $container.on('click', '.wp-picker-clear', function() {

                  a8cIris._color._alpha = 1;
                  $alpha_text.text('');
                  $alpha_slider.slider('option', 'value', 100).trigger('slide');

                });

                // wpColorPicker default button for update iris data alpha && alpha text && slider color option
                $container.on('click', '.wp-picker-default', function() {

                  var default_picker = $.avia_ParseColorValue( $this.data('default-color') ),
                      default_value  = parseFloat( default_picker.alpha / 100 ),
                      default_text   = default_value < 1 ? default_value : '';

                  a8cIris._color._alpha = default_value;
                  $alpha_text.text(default_text);
                  $alpha_slider.slider('option', 'value', default_picker.alpha).trigger('slide');

                });

              },

              // slider: options
              value: picker.alpha,
              step: 1,
              min: 1,
              max: 100

            });
          }

        };
        
        var final_options = $.extend( true, {}, new_settings, default_options );
        $this.wpColorPicker( final_options );
        

      } else {

        // wpColorPicker default picker
        $this.wpColorPicker( default_options );

      }

    });

  };


})( jQuery, window, document );
