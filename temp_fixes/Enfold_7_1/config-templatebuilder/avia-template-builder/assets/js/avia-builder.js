/* this prevents dom flickering, needs to be outside of dom.ready event.also adds several extra classes for better browser support */
document.documentElement.className += ' js_active ';
document.documentElement.className += 'ontouchstart' in document.documentElement ? ' avia_mobile ' : ' avia_desktop ';
(function()
{
    var prefix = ['-webkit-','-o-','-moz-', '-ms-', ""];
    for( var i in prefix )
    {
        if( prefix[i]+'transform' in document.documentElement.style )
		{
			document.documentElement.className += " avia_transform ";
			break;
		}
    }
})();

//global logging helper
function avia_log( text, type )
{
	if( typeof console === 'undefined' )
	{
		return;
	}

	if( typeof type === 'undefined' )
	{
		type = "log";
	}

	if( type === false )
	{
		console.log( text );
	}
	else
	{
		type = "AVIA-" + type.toUpperCase();
		console.log("["+type+"] " +text);
	}
}
//global newline helper
function avia_nl2br (str, is_xhtml)
{
	var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>';
	return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

/**
 * jQuery.isNumeric() is deprecated with 3.3
 * This is the replacement implementation
 *
 * As of jQuery 3.0, isNumeric is limited to strings and numbers (primitives or objects) that can be coerced to finite numbers (gh-2662)
 * parseFloat NaNs numeric-cast false positives ("") ...but misinterprets leading-number strings, particularly hex literals ("0x...")
 * subtraction forces infinities to NaN
 *
 * @since 4.8
 * @param {mixed} obj
 * @returns {boolean}
 */
function avia_isNumeric( obj )
{
	//	Fix for Android < 2.3 from jQuery has been skipped (release date 2010)
	var type = typeof obj;
	return ( type === "number" || type === "string" ) && ! isNaN( obj - parseFloat( obj ) );
}

//main builder js
(function($)
{
	"use strict";

	$.AviaBuilder = function ()
	{
		//html container
		this.body_container		= $('body').eq( 0 );

		// the canvas we use to display the interface
        this.canvas				= $('#aviaLayoutBuilder');

        // box wrapping the canvas
        this.canvasParent		= this.canvas.parents('.postbox').eq( 0 );

        //wether the layout builder is currently active or the wordpress default editor is
        this.activeStatus		= this.canvasParent.find('#aviaLayoutBuilder_active');

        //list of available shortcode buttons
        this.shortcodes 		= $.AviaBuilder.shortcodes || {};

        //if safemode is true the wordpress default editor will not show the shortcodes
        this.safemode			= avia_globals.builderMode || false;

        //wether tinymce is available
        this.tiny_active		= typeof window.tinyMCE == 'undefined' ? false : true;

        //tinymce version
        this.tiny_version		= this.tiny_active ? window.tinyMCE.majorVersion : false;

        //wordpress tiny_mce editor
        this.classic_editor 	= $('#postdivrich');

        //wrapper arroudn tinymce editor
        this.classic_editor_wrap= $('#postdivrich_wrap');

        //button to switch between wordpress editor and avia builder
        this.switch_button      = this.classic_editor_wrap.find('.avia-builder-button');

        //fallback html textarea for the wordpress tinymce editor
        this.classic_textarea 	= $('#content.wp-editor-area');

        //field that stores all our content
        this.secureContent		= $('#_aviaLayoutBuilderCleanData');

        //textarea elements for each builder element that holds the actual shortcode + content
        this.datastorage		= 'textarea[data-name="text-shortcode"]';

        //wrapper around all the shortcode buttons
        this.shortcode_wrap 	= $('.shortcode_button_wrap');

		//sort shortcode button dropdown container
		this.sort_button_wrap	= $('#avia-sort-list-dropdown');

		//functions to support adding, editing, ....
		this.element_templates = new $.AviaElementTemplates( this );

        //wether we are in target insert mode or not, if set the var holds an element that gets inserted into the canvas on click
        this.targetInsert		= false;

        //if we only insert one item (cut&move: deprecated) or multiple
        this.only_one_insert	= false;

        //var that holds the function to update the editor once updateTextarea() was executed, since updating the tinymce field requires too much resources on big sites
        this.update_timout	= false;

		//supress auto switching TinyMCE/HTML editor style for content editor when switching between ALB and standard editor
		//(used by Gutenberg, caused page reload with Edge browser when triggering HTML button)
		this.disable_autoswitch_editor_style = false;

		this.builder_drag_drop_container = 'body';

		//	function when a modal is saved (hooked by custom elements to reroute output)
		this.save_modal_handler = this.send_to_datastorage;

		//	function when a modal is closed (hooked by custom elements)
		this.close_modal_handler = null;

		// Stores last selected tab and toggle state in modal popup (main popup and subitem popup). Limited to last used element - reset when element changes.
		// After a page reload only checks if same type of element is clicked to prevent a reset.
		// false if session storage is blocked by user
		this.modal_popup_state_default = {
								post_id:			0,
								shortcode:			'',
								tab_text:			'',
								toggle_text:		'',
								group_shortcode:	'',
								group_tab_text:		'',
								group_toggle_text:	''
							};
		this.modal_popup_state = $.extend( {}, this.modal_popup_state_default );

        //activate the plugin
        this.set_up();

		/**
		 * Allow 3rd party like Gutenberg add on to chain properly
		 */
		this.body_container.trigger( 'AviaBuilder_after_set_up', this );

		/**
		 * Dispatch event for pure js hooks
		 *
		 * @since 6.0
		 */
		const opt = {
						'bubbles':		true,
						'cancelable':	true,
						'detail':		{
											builder: this
										}
					};

		this.body_container[0].dispatchEvent( new CustomEvent( 'AviaBuilderAfterSetUp', opt ) );
    };

   	$.AviaBuilder.prototype = {

		/**
		* Sets up the whole interface
		*
		*/
		set_up: function()
		{
			this.place_top();
			this.convertTextToInterface();
			this.add_behavior();
			this.init_modal_popup_state();

			//	force to open ALB on new element (hides editor button with CSS)
			if( this.body_container.hasClass( 'avia-force-alb' ) )
			{
				if( this.activeStatus.val() != 'active' )
				{
					this.switch_button.trigger( 'click' );
				}
			}
		},

		/**
		 * Clear all element unique ID's in shortcode
		 *
		 * @since 4.8
		 * @param {string} shortcode
		 * @returns {string}
		 */
		clear_av_uid: function( shortcode )
		{
			return shortcode.replace( /av_uid='[^']*'|av_uid="[^"]*"/ig, "av_uid=''" );
		},

		/**
		* Moves the editor to the fist position and triggers post box saving, in case it is not already at the first pos :)
		*
		*/
		place_top: function()
		{
            var meta_box_container = $('#normal-sortables'),
                meta_boxes          = meta_box_container.find('.postbox');

            if(this.canvasParent.length && meta_boxes.index(this.canvasParent) !== 0)
            {
               this.canvasParent.prependTo(meta_box_container);

			   // should no longer be necessary. was used to fix an error when saving order and .sortable was not initialized
			   // better solution was to make post.js a dependency of the builder
               if(!meta_box_container.is('.ui-sortable'))
               {
	               window.postboxes.init();
               }

               /**
				* Some plugins cause problem by adding their own postboxes to the post editor and
				* the object has not been correctly initialised
				* see https://kriesi.at/support/topic/uncaught-error-cannot-call-methods-on-sortable-prior-to-initialization/#post-516758
				*/
			   if( 'undefined' == typeof window.postboxes || 'function' != typeof window.postboxes.save_order )
			   {
				   setTimeout( function() { window.postboxes.save_order(pagenow); }, 500 );
			   }
			   else
			   {
					window.postboxes.save_order(pagenow);
			   }
            }
		},


		/**
		* all event binding goes here
		*
		*/
		add_behavior: function()
		{
			var obj = this,
				$body = $('body');

			//switch between normal text editor and advanced layout editor
			this.switch_button.on( 'click', function(e)
			{
                e.preventDefault();
				obj.switch_layout_mode(e);
			});

			//bind sort shortcode buttons actions
			this.sort_button_wrap.on( 'click', '.avia-sort-list-element', function(e)
			{
				e.preventDefault();

				var current = $(this),
					init_trigger = obj.sort_button_wrap.data( 'init_sort' ),
					sort_link = current.find( 'a' ),
					sorting = sort_link.data( 'sorting' ),
					sorting_title = sort_link.attr( 'title' ),
					sort_list_label = obj.sort_button_wrap.find( '.avia-sort-list-label' ),
                    block_expand_support = $('body').is('.avia-block-editor-expand.block-editor-page'),
                    builder_expanded = $('.avia-expanded').length,
                    shortcode_wrap = block_expand_support && builder_expanded ? $('.avia-fixed-controls .shortcode_button_wrap') : obj.shortcode_wrap,
					shortcode_buttons = shortcode_wrap.find('.shortcode_insert_button');

                // the class has to be removed in order reactive dragging, check ~ line 1074 ~
                shortcode_buttons.removeClass('ui-draggable');

				if( 'string' == typeof sorting_title )
				{
					obj.sort_button_wrap.attr( 'title', sorting_title );
				}

				if( sort_link.hasClass( 'sort_active' ) && sorting == init_trigger )
				{
					return false;
				}

				obj.sort_button_wrap.find( '.avia-sort-list-element a' ).removeClass( 'sort_active' );
				sort_link.addClass( 'sort_active' );
				sort_list_label.html(sort_link.html());

				obj.sort_shortcode_buttons( sorting );

				if( sorting == init_trigger )
				{
					return false;
				}

				obj.activate_element_dragging( shortcode_wrap, '' );
				shortcode_wrap.trigger( 'avia_shortcode_buttons_sorted' );

				var senddata = {
							action: 'avia_alb_shortcode_buttons_order',
							sorting: sorting,
							avia_request: true,
							_ajax_nonce: $('#avia-loader-nonce').val()
						};

				$.ajax({
						type: "POST",
						url: ajaxurl,
						dataType: 'json',
						cache: false,
						data: senddata,
						post_type: $('.avia-builder-main-wrap').data('post_type'),
						success: function(response, textStatus, jqXHR)
						{
							if( response.success == true )
							{
								$('#avia-loader-nonce').val( response._ajax_nonce);
								obj.sort_button_wrap.data( 'init_sort', sorting );
							}
						},
						error: function(errorObj) {
//									console.log( 'avia_alb_shortcode_buttons_order error: ', errorObj );
								},
						complete: function(test) {
								}
					});
			}),

			this.InitialTriggerSortShortcodeButtons();

			this.element_templates.init();

			//add a new element to the AviaBuilder canvas
			this.shortcode_wrap.on( 'click', '.shortcode_insert_button', function()
			{
				var current			= $(this),
					parent 			= $(this).parents('.shortcode_button_wrap'),
					execute			= this.hash.replace('#',''),
					target			= "instant_insert", // this.className.indexOf('avia-target-insert') !== -1 ? "target_insert" : "instant_insert",
					already_active	= this.className.indexOf('avia-active-insert') !== -1 ? true : false;

				//	Do not allow when editing custom elements or item elements
				if( parent.hasClass( 'avia-edit-elements-clicked' ) || current.hasClass( 'avia-custom-element-item' ) )
				{
					return false;
				}

				if( ! current.is(".av-shortcode-disabled") )
				{
					obj.shortcodes.fetchShortcodeEditorElement(execute, target, obj);
				}
				return false;
			});

			//remove element from canvas
			this.canvas.on('click', 'a.avia-delete:not(.av-no-drag-drop a, .av-special-delete)', function()
			{
				obj.shortcodes.deleteItem(this, obj);
				return false;
			});


			//change size of column element
			this.canvas.on('click', 'a.avia-change-col-size:not(.avia-change-cell-size, .av-no-drag-drop a)', function()
			{
				obj.shortcodes.changeSize(this, obj);
				return false;
			});


			// add new cell for layout row
			this.canvas.on('click', 'a.avia-add-cell:not( .av-no-drag-drop a )', function()
			{
				obj.shortcodes.addCell(this, obj);
				return false;
			});

			// add new cell for layout row
			this.canvas.on('click', 'a.avia-set-cell-size:not( .av-no-drag-drop a )', function()
			{
				obj.shortcodes.setCellSize(this, obj);
				return false;
			});




			//toggle item visibility
			$body.on('click', '.avia-toggle-visibility, .avia-layout-element-hidden', function(e)
			{
				e.preventDefault();

				var parent 		= $(this).parents('.av_drag').eq( 0 ),
					storage 	= parent.find( '>.avia_inner_shortcode>' + obj.datastorage ).eq( 0 ),
					key 		= 'av_element_hidden_in_editor',
					new_val		= parent.is('.av-layout-element-closed') ? "0" : "1",
					shortcode	= storage.val();

					parent.toggleClass('av-layout-element-closed');

				var new_shortcode = obj.set_shortcode_single_value(shortcode, key, new_val, storage);

				storage.val(new_shortcode);
				obj.updateTextarea();

			});


			// edit item via modal window ( click button or div area )
			$body.on( 'click', '.avia-edit-element', function( e )
			{
				var clicked = $(this);
				var parent = clicked.parents('.avia_sortable_element').eq( 0 );

				if( ! parent.length )
				{
					parent = clicked.parents('.avia_layout_column').eq( 0 );
					if( ! parent.length )
					{
						parent = clicked.parents('.avia_layout_section').eq( 0 );
					}
				}

				var params = parent.data();
				var no_visual_updates = parent.hasClass( 'avia-no-visual-updates' );
				var container = parent;
				var update_template = typeof parent.data('update_element_template') != 'undefined' && parent.data('update_element_template') == 'yes' ? parent : [];
				if( update_template.length == 0 )
				{
					update_template = parent.find( '[data-update_element_template="yes"]' ).first();
				}

				params.modal_class = 'avia-base-shortcode';
				if( clicked.closest( '.avia_layout_builder_custom_elements' ).length > 0 )
				{
					params.modal_class += ' avia-modal-edit-custom-element';
				}
				else
				{
					params.modal_class += ' avia-modal-edit-alb-element';
				}

				params.obj_clicked	= clicked;
				params.template_changed = parent.hasClass( 'element_template_changed' );
				params.scope		= obj;
				params.on_load		= parent.data('modal_on_load');
				params.before_save	= parent.data('before_save');
				params.on_save		= obj.save_modal_handler;
				params.before_close	= obj.close_modal_handler;
				params.save_param	= parent;
				params.ajax_param = {
									extract: true,
									shortcode: parent.find( '>.avia_inner_shortcode>' + obj.datastorage ).eq( 0 ).val(),
									outer_sc_params: [],
									sc_params: [],
									allowed: params.allowedShortcodes,
									_ajax_nonce: $('#avia-loader-nonce').val()
								};

				var preview_scale_markup = '';
				if( ! isNaN(params.preview_scale))
				{
					preview_scale_markup = "<span class='avia-modal-preview-scale'>"+window.avia_preview.scale+" "+params.preview_scale+"%</span>";
				}

				if( params.preview ) //check for preview window
				{
					var bg_colors = "<a href='#' style='background:#fff;'></a><a href='#' style='background:#f1f1f1;'></a><a href='#' style='background:#222;'></a>";
					params.modal_class += " modal-preview-active modal-preview-"+params.preview;
					params.on_load = params.on_load != "" ? params.on_load + ", modal_preview_script" : "modal_preview_script";
					params.attach_content = "<div class='avia-modal-preview'><div class='avia-modal-preview-header'><h3 class='avia-modal-title'>"+window.avia_preview.title+"</h3><div class='avia_loading'></div></div><div class='avia-modal-preview-content' data-preview-scale='"+params.preview_scale+"'></div><div class='avia-modal-preview-footer'><span>"+window.avia_preview.background+"</span>"+bg_colors+preview_scale_markup+"</div></div>";
				}

				var modal = new $.AviaModal( params );

				//	Reload HTML if needed to show locked options
				if( parent.hasClass( 'element_template_changed' ) && update_template.length > 0 )
				{
					obj.body_container.one( 'AviaBuilder_interface_loaded_response', function( e, response )
					{
						if( typeof response != 'string' )
						{
							return;
						}

						var new_html = $( response );
						var new_templ = typeof new_html.data( 'update_element_template' ) != 'undefined' && new_html.data( 'update_element_template' ) == 'yes' ? new_html : [];
						if( new_templ.length == 0 )
						{
							new_templ = new_html.find( '[data-update_element_template="yes"]' ).first();
						}

						if( new_templ.length > 0 )
						{
							update_template.replaceWith( new_templ );

							//	Special case for layout elements
							if( no_visual_updates )
							{
								if( typeof new_templ.data('initial_el_bg') != 'undefined' )
								{
									container.find('.avia-element-bg-color').first().attr( 'style', new_templ.data('initial_el_bg') );
								}

								if( typeof new_templ.data('initial_layout_element_bg') != 'undefined' )
								{
									container.find('.avia-layout-element-bg').first().attr( 'style', new_templ.data('initial_layout_element_bg') );
								}
							}
						}
					});

					obj.convertTextToInterface( params.ajax_param.shortcode, true );
				}

				parent.removeClass( 'element_template_changed' );

				return false;
			});


			//edit item in modal window via sub modal window
			$body.on('click', '.avia-modal-group-element-inner', function()
			{
				if( $( this ).closest( '.avia-modal-group-wrapper' ).hasClass( 'avia-modal-locked' ) )
				{
					return false;
				}

				var clicked = $(this),
					modal = clicked.closest( '.avia-modal' ),
					parent = clicked.parents('.avia-modal-group-element').eq( 0 ),
					params = parent.data(),
					obj_modal = modal.data( 'avia_modal_object' );

				var update_template = typeof parent.data('update_element_template') != 'undefined' && parent.data('update_element_template') == 'yes' ? parent : [];
				if( update_template.length == 0 )
				{
					update_template = parent.find( '[data-update_element_template="yes"]' ).first();
				}

				if( ( 'undefined' !== typeof parent.data('modal_open') ) && ( 'no' == parent.data('modal_open') ) )
				{
					//	reroute click event to another button/element in this modal group
					if( ( 'undefined' !== typeof parent.data('trigger_button') ) && ( '' != parent.data('trigger_button').trim() ) )
					{
						parent.closest( '.avia-modal-group-wrapper ' ).find( '.' + parent.data('trigger_button').trim() ).trigger( 'click' );
					}
					return false;
				}

				params.modal_class = 'avia-modal-group-shortcode';

				if( modal.hasClass( 'avia-modal-edit-custom-element' ) )
				{
					params.modal_class += ' avia-modal-edit-custom-element';
					params.modal_class += modal.hasClass( 'avia-edit-item-template' ) ? ' avia-edit-item-template' : ' avia-edit-base-template';
				}
				else
				{
					params.modal_class += ' avia-modal-edit-alb-element';
				}

				params.obj_clicked	= clicked;
				params.template_changed = parent.hasClass( 'element_template_changed' );
				params.scope		= obj;
				params.on_load		= parent.data('modal_on_load');
				params.before_save	= parent.data('before_save');
				params.on_save		= obj.save_modal_handler;
				params.before_close	= obj.close_modal_handler;
				params.save_param	= parent;
				params.shortcodehandler = parent.data('shortcodehandler');

				//	get current final output for use as default in creating custom elements
				var value_array = obj_modal.get_final_values();

				params.ajax_param = {
									subelement: true,
									extract: true,
									shortcode: parent.find( obj.datastorage ).eq( 0 ).val(),
									outer_sc_params: value_array,
									sc_params: [],
									_ajax_nonce: $('#avia-loader-nonce').val()
								};

				var modal = new $.AviaModal(params);

				//	Reload HTML if needed to show locked options
				if( parent.hasClass( 'element_template_changed' ) && update_template.length > 0 )
				{
					obj.body_container.one( 'AviaBuilder_modal_group_loaded_response', function( e, response )
					{
						if( typeof response != 'string' )
						{
							return;
						}

						var new_html = $( response );
						var new_templ = typeof new_html.data( 'update_element_template' ) != 'undefined' && new_html.data( 'update_element_template' ) == 'yes' ? new_html : [];
						if( new_templ.length == 0 )
						{
							new_templ = new_html.find( '[data-update_element_template="yes"]' ).first();
						}

						if( new_templ.length > 0 )
						{
							update_template.replaceWith( new_templ );
						}
					});

					obj.convertToModalGroupInterface( params.ajax_param.shortcode, params.shortcodehandler, obj );
				}

				return false;
			});

			//delete sub items in modal window
			$body.on('click', '.avia-attach-modal-element-delete', function(e)
			{
				obj.shortcodes.deleteModalSubItem(this, e);
			});

			//add sub item modal window
			$body.on('click', '.avia-attach-modal-element-add', function(e)
			{
				obj.shortcodes.appendModalSubItem(this, e);
			});

			//add sub item modal window
			$body.on('click', '.avia-attach-modal-element-clone', function(e)
			{
				obj.shortcodes.appendModalSubItem( this, e , "clone");
			});


			//copy item
			this.canvas.on('click', 'a.avia-clone:not( .av-no-drag-drop a , .av-special-clone)', function()
			{
				obj.cloneElement(this, obj);
				return false;
			});

			//recalc shortcode when select elements change
			this.canvas.on('change', 'select.avia-recalc-shortcode', function()
			{
				var container = $(this).parents( '.avia_sortable_element' ).eq( 0 );
				obj.recalc_shortcode(container);
				return false;
			});


			//re activate sorting and dropping after undo and redo changes
			this.canvas.on( 'avia-history-update', function()
			{
				obj.activate_element_dragging( this.canvasParent, "" );
				obj.activate_element_dropping( this.canvasParent, "" );
			});

			//	hide/show shortcodes when an element is added/removed to canvas
			$body.on( 'av-element-to-editor av-element-deleted-from-editor av-element-dropped-in-editor', function( e )
			{
				if( ! $body.hasClass( 'post-type-alb_elements' ) )
				{
					return;
				}

				var elements = obj.canvas.find( '.avia_sortable_element, .avia_layout_column, .av_section, .avia_layout_row' );
				if( elements.length >= 1 )
				{
					obj.canvasParent.addClass( 'element-added' );
				}
				else
				{
					obj.canvasParent.removeClass( 'element-added' );
				}

			});


		},

		init_modal_popup_state: function()
		{
			let form = [];

			if( this.body_container.hasClass( 'block-editor-page' ) )
			{
				form = $( '#metaboxes form.metabox-base-form');
			}
			else
			{
				form = $( 'form[id="post"]' );
			}

			if( form.length == 0 )
			{
				this.modal_popup_state = false;
				return;
			}

			var post_id = form.find( '#post_ID' ).val();
			var stored = this.get_modal_popup_state();

			if( false === stored )
			{
				return;
			}

			if( 'undefined' == typeof stored || 'undefined' == typeof stored.post_id || stored.post_id != post_id )
			{
				this.modal_popup_state = $.extend( {}, this.modal_popup_state_default );
				this.modal_popup_state.post_id = post_id;
			}
			else
			{
				this.modal_popup_state = stored;
			}

			this.save_modal_popup_state();
			return;
		},

		clear_modal_popup_state: function( shortcode, is_group_element )
		{
			if( false === this.modal_popup_state )
			{
				return;
			}

			var current = this.modal_popup_state;
			this.modal_popup_state = $.extend( {}, this.modal_popup_state_default );
			this.modal_popup_state.post_id = current.post_id;

			if( is_group_element )
			{
				this.modal_popup_state.shortcode = current.shortcode;
				this.modal_popup_state.tab_text = current.tab_text;
				this.modal_popup_state.toggle_text = current.toggle_text;
				this.modal_popup_state.group_shortcode = shortcode;
			}
			else
			{
				this.modal_popup_state.shortcode = shortcode;
			}

			this.save_modal_popup_state();

			return this.modal_popup_state;
		},

		set_modal_popup_state: function( context, value, modal_instance )
		{
			if( false === this.modal_popup_state )
			{
				return;
			}

			this.modal_popup_state[ context ] = value;
			this.save_modal_popup_state();

			var group_element = modal_instance.options.save_param.hasClass( 'avia-modal-group-element' );
			var last_element_class = group_element ? 'avia-modal-group-last-open' : 'avia-modal-element-last-open';

			//	Remember last selected element - this is only availale when page is not reloaded
			$( 'body' ).find( '.' + last_element_class ).removeClass( last_element_class );
			modal_instance.options.save_param.addClass( last_element_class );
		},

		get_modal_popup_state: function()
		{
			var stored = null;

			if( typeof sessionStorage === 'undefined' )
			{
				return stored;
			}

			//	FF throws error when all cookies blocked !!
			try
			{
				stored = sessionStorage.getItem( 'aviaModalPopupState' );
				stored = ( null == stored ) ? {} : JSON.parse( stored );
			}
			catch( err )
			{
				this.modal_popup_state = false;
				return this.modal_popup_state;
			}

			return stored;
		},

		save_modal_popup_state: function()
		{
			if( typeof sessionStorage === 'undefined' )
			{
				return;
			}

			if( false === this.modal_popup_state )
			{
				return;
			}

			var value = JSON.stringify( this.modal_popup_state );

			try
			{
				sessionStorage.setItem( 'aviaModalPopupState', value );
			}
			catch( err )
			{
				avia_log( 'Info - Session Storage: Browser memory limit reached, blocked or not supported. We are not able to save the state of the last open options tabs in modal popup windows of ALB elements.' );
				avia_log( err );
			}
		},

		sort_shortcode_buttons: function( sort_order )
		{
			var block_expand_support = $('body').is('.avia-block-editor-expand.block-editor-page'),
                builder_expanded = $('.avia-expanded').length,
                shortcode_wrap = block_expand_support && builder_expanded ? $('.avia-fixed-controls .shortcode_button_wrap') : this.shortcode_wrap,
                tabs = shortcode_wrap.find( '.avia-tab' );

			tabs.each( function( i ) {
						var tab = $(this);
						var buttons = tab.find( 'a' );
						tab.find( 'a' ).remove();

						buttons.sort( function( e1, e2 ) {
								var comp_a = '';
								var comp_b = '';
								var result = 0;
								var a = $(e1);
								var b = $(e2);

								switch( sort_order )
								{
									case 'name_asc':
										comp_a = a.data( 'sort_name' );
										if( 'undefined' == typeof comp_a )
										{
											result = 1;
											break;
										}
										comp_b = b.data( 'sort_name' );
										if( 'undefined' == typeof comp_b )
										{
											result = 1;
											break;
										}

										if( comp_a < comp_b )
										{
											result = -1;
										}
										else if( comp_a == comp_b )
										{
											result = 0;
										}
										else
										{
											result = 1;
										}
										break;
									case 'name_desc':
										comp_a = a.data( 'sort_name' );
										if( 'undefined' == typeof comp_a )
										{
											result = 1;
											break;
										}
										comp_b = b.data( 'sort_name' );
										if( 'undefined' == typeof comp_b )
										{
											result = 1;
											break;
										}

										if( comp_a < comp_b )
										{
											result = 1;
										}
										else if( comp_a == comp_b )
										{
											result = 0;
										}
										else
										{
											result = -1;
										}
										break;
									case 'usage':
										comp_a = a.data( 'sort_usage' );
										if( 'undefined' == typeof comp_a )
										{
											result = 1;
											break;
										}
										comp_b = b.data( 'sort_usage' );
										if( 'undefined' == typeof comp_b )
										{
											result = 1;
											break;
										}

										var comp_a_name = a.data( 'sort_name' );
										var comp_b_name = b.data( 'sort_name' );

										if( comp_a < comp_b )
										{
											result = 1;
										}
										else if( comp_a == comp_b )
										{
											if( 'undefined' == typeof comp_a_name )
											{
												result = 1;
											}
											else if( 'undefined' == typeof comp_b_name )
											{
												result = -1;
											}
											else if( comp_a_name < comp_b_name )
											{
												result = -1;
											}
											else if( comp_a_name == comp_b_name )
											{
												result = 0;
											}
											else
											{
												result = 1;
											}
										}
										else
										{
											result = -1;
										}
										break;
									case 'order':		//	should have uinque values from building the html
									default:
										comp_a = a.data( 'sort_order' );
										if( 'undefined' == typeof comp_a )
										{
											result = 1;
											break;
										}
										comp_b = b.data( 'sort_order' );
										if( 'undefined' == typeof comp_b )
										{
											result = 1;
											break;
										}

										if( comp_a < comp_b )
										{
											result = -1;
										}
										else if( comp_a == comp_b )
										{
											result = 0;
										}
										else
										{
											result = 1;
										}
										break;
								}

								return result;
							});

						tab.html( buttons );
				});

		},

		InitialTriggerSortShortcodeButtons: function ()
		{
			var trigger = this.sort_button_wrap.data( 'init_sort' );

			if( 'string' != typeof trigger )
			{
				trigger = 'order';
			}

			this.sort_button_wrap.find( 'a[data-sorting="' + trigger + '"]').trigger('click');
		},

		//	array of js element templates to replace or add
		replace_js_templates: function( templates )
		{
			var body = this.body_container;

			$.each( templates, function( id, template )
			{
				var script = $( id ).first();
				if( script.length > 0 )
				{
					script.replaceWith( template );
				}
				else
				{
					body.append( template );
				}
			});
		},

		/*version compare helper function for the drag and drop fix below.*/
		cmpVersion: function(a, b) {
		    var i, cmp, len, re = /(\.0)+[^\.]*$/;
		    a = (a + '').replace(re, '').split('.');
		    b = (b + '').replace(re, '').split('.');
		    len = Math.min(a.length, b.length);
		    for( i = 0; i < len; i++ ) {
		        cmp = parseInt(a[i], 10) - parseInt(b[i], 10);
		        if( cmp !== 0 ) {
		            return cmp;
		        }
		    }
		    return a.length - b.length;
		},


		// ------------------------------------------------------------------------------------------------------------
		// main interface drag and drop implementation
		// ------------------------------------------------------------------------------------------------------------

		activate_element_dragging: function(passed_scope, exclude)
		{
			// temp fix for ui.draggable version 1.10.3 which positions element wrong. 1.11 contains the fix
			// http://stackoverflow.com/questions/5791886/jquery-draggable-shows-helper-in-wrong-place-when-scrolled-down-page
			var fix_active 	= this.cmpVersion($.ui.draggable.version, "1.10.9") <= 0 ? true : false,
				$win 		= $(window);

			//exclude safari from fix
			if (navigator.userAgent.indexOf('Safari') !== -1 || navigator.userAgent.indexOf('Chrome') !== -1) fix_active = false;

			if(fix_active) avia_log('drag and drop positioning fix active');

			//drag
			var obj		= this,
				scope  	= passed_scope || this.canvasParent,
				params 	=
				{
					appendTo: this.builder_drag_drop_container,
					handle: '>.menu-item-handle:not( .av-no-drag-drop .menu-item-handle )',
					helper: "clone",
					scroll: true,
					cancel: '#aviaLayoutBuilder .avia_sorthandle a, input, textarea, button, select, option',
					zIndex: 20000, /*must be bigger than fullscreen overlay in fixed pos*/
					cursorAt: { left: 20 },
					start: function( event, ui )
					{
						var current = $(event.target);

						//	Do not allow drag drop when editing custom elements or item element
						var container = current.closest( '.shortcode_button_wrap' );
						if( container.hasClass( 'avia-edit-elements-clicked' ) || current.hasClass( 'avia-custom-element-item' ) )
						{
							return false;
						}

						//reduce elements opacity so user got a visual feedback on what he is editing
						current.css({opacity:0.4});

						//remove all previous hover elements
						$('.avia-hover-active').removeClass('avia-hover-active');

						//add a class to the container element that highlights all possible drop targets
						obj.canvas.addClass('avia-select-target-' + current.data('dragdrop-level'));


					},

					drag: function(event,ui)
					{
      					if(fix_active) ui.position.top -=  parseInt($win.scrollTop());
					},

					stop: function(event, ui )
					{
						//return opacity of element to normal
						$(event.target).css({opacity:1});

						//remove hover class from all elements
						$('.avia-hover-active').removeClass('avia-hover-active');

						//reset highlight on container class
						obj.canvas.removeClass('avia-select-target-1 avia-select-target-2 avia-select-target-3 avia-select-target-4');
					}
				};


			if(typeof exclude == "undefined") exclude = ":not(.ui-draggable)";
			scope.find('.av_drag'+exclude).draggable(params);

			params.cursorAt = { left: 33, top:33 };
			params.handle   = false;
			scope.find('.shortcode_insert_button').not('.ui-draggable, .av-shortcode-disabled').draggable(params);
		},



		activate_element_dropping: function(passed_scope, exclude)
		{
			//drag
			var obj		= this,
				scope  	= passed_scope || this.canvasParent,
				params 	=
				{
					tolerance: 'pointer',
					greedy: true,

					over: function(event, ui)
					{
						var dropable = $(this);

						if(obj.droping_allowed(ui.helper, dropable))
						{
							dropable.addClass('avia-hover-active');
						}
					},

					out: function(event, ui)
					{
						$(this).removeClass('avia-hover-active');
					},

					drop: function(event, ui)
					{
						// this = the target that we dropped the draggable onto
						var dropable = $(this);

						//check if the previous check for droping_allowed returend true, otherwise do nothing
						if(!dropable.is('.avia-hover-active')) return false;

						//get all items within the dropable and check their position so we know where exactly to add the dragable
						var elements = dropable.find('>.av_drag'), offset = {}, method = 'after', toEl = false, position_array = [], last_pos, max_height;

						//avia_log("dragging:" + ui.draggable.find('h2').text() +" to position: "+ui.offset.top + "/" +ui.offset.left);

						//iterate over all elements and check their positions
						for (var i=0; i < elements.length; i++)
						{
							var current = elements.eq(i);
							offset = current.offset();

							if(offset.top < ui.offset.top)
							{
								toEl = current;
								last_pos = offset;
								//save all items before the draggable to a position array so we can check if the right positioning is important
								if(!position_array["top_"+offset.top])
								{ 
									max_height = 0;
									position_array["top_"+offset.top] = [];
								}
								max_height = max_height > current.outerHeight() +offset.top ? max_height : current.outerHeight() +offset.top;
								position_array["top_"+offset.top].push({left: offset.left, top: offset.top, index: i, height: current.outerHeight(), maxheight: current.outerHeight() +offset.top});

								//avia_log(current.find('h2').text() + " element offset:" +offset.top + "/" +offset.left);
							}
							else
							{
								break;
							}
						}

						//if we got multiple matches that all got the same top position we also need to check for the left position
						if(last_pos && position_array["top_"+last_pos.top].length > 1 && max_height -40 > ui.offset.top)
						{
							var real_element = false;

							//avia_log("checking right positions:");

							for (var i=0; i < position_array["top_"+last_pos.top].length; i++)
							{
								//console.log(position_array["top_"+last_pos.top][i]);

								if(position_array["top_"+last_pos.top][i].left < ui.offset.left)
								{
									real_element = position_array["top_"+last_pos.top][i].index;
								}
								else
								{
									break;
								}
							}

							//if we got an index get that element from the list, else delete the toEL var because we need to append the draggable to the start and the next check will do that for us
							if(real_element === false)
							{
								//avia_log("No right pos element found, using first element");
								real_element = position_array["top_"+last_pos.top][0].index;
								method = 'before';
							}

							toEl = elements.eq(real_element);
						}


						//if no element with higher offset were found there either are no at all or the new position is at the top so we change the params accordingly
						if(toEl === false)
						{
							//avia_log('no el found');
							toEl = dropable;
							method = 'prepend';
						}

						//avia_log( ui.draggable.find('h2').text() + " dragable top:" +ui.offset.top + "/" +ui.offset.left);

						//if the draggable and the new el are the same do nothing
						if(toEl[0] == ui.draggable[0])
						{
							 //avia_log("same element selected: stopping script");
							 return;
						}

						//if we got a hash on the draggable we are not dragging an existing element but a new one via shortcode button so we need to fetch an empty shortcode template
						if(ui.draggable[0].hash)
						{
							var shortcode 	= ui.draggable.get(0).hash.replace('#',''),
								template 	= $($("#avia-tmpl-"+shortcode).html());

							ui.draggable = template;
						}

        				//before finaly moving the element, save the former parent of the draggable to a var so we can check later if we need to update that parent as well
						var formerParent = ui.draggable.parents('.av_drag').last();


						//move the real dragable element to the new position
						toEl[method](ui.draggable);


						//avia_log("Appended to: " + toEl.find('h2').text());

						//if the element got a former parent we need to update that as well
						if(formerParent.length)
						{
							obj.updateInnerTextarea(false, formerParent);
						}


						//get the element that the new element was inserted into. This has to be the parrent of the current toEL since we usualy insert the new element outside of toEL with the "after" method
						//if method != 'after' the element was inserted with prepend directly to the toEL and toEL should therefore also the insertedInto element

						var insertedInto = method == 'after' ? toEl.parents('.av_drop') : toEl;

						if(insertedInto.data('dragdrop-level') !== 0)
						{
							//avia_log("Inner update necessary. Level:" + insertedInto.data('dragdrop-level'));
							obj.updateTextarea();//<-- actually only necessary because of column first class. optimize that so we can remove the costly function of updating all elements
      						obj.updateInnerTextarea(ui.draggable);
						}

						//everything is fine, now do the re sorting and textfield updating
						obj.updateTextarea();

						//if we were in target mode deactivate that
						obj.targetInsertInactive();

						//apply dragging and dropping in case we got a new element
						if(typeof template != "undefined")
						{
							obj.body_container.trigger('av-builder-new-element-added', ui.draggable );

							obj.canvas.removeClass('ui-droppable').droppable('destroy');
							obj.activate_element_dragging();
							obj.activate_element_dropping();
						}

						obj.do_history_snapshot();
						//avia_log("-----------------------------");

						$( 'body' ).trigger( 'av-element-dropped-in-editor' );

						//	added 5.5 as pure js does not support jQuery trigger properly
						let body = document.getElementsByTagName( 'body' );
						if( body.length > 0 )
						{
							const opt = {
								'bubbles':		true,
								'cancelable':	true
							};

							const event = new CustomEvent( 'av-alb-element-dropped-in-editor', opt );
							body[0].dispatchEvent( event );
						}
					}

				};

			if(typeof exclude == "undefined")
			{
				exclude = ":not(.ui-droppable)";
			}

			//if exclude is set to destroy remove all dropables and then re-apply
			if("destroy" == exclude)
			{
				scope.find('.av_drop').droppable('destroy');
				exclude = "";
			}

			scope.find('.av_drop'+exclude).droppable(params);
		},

		//compares the drop levels of the 2 elments. if the dragable has a higher drop level it may be dropped upon the droppable
		droping_allowed: function(dragable, droppable)
		{
			if(dragable.data('dragdrop-level') > droppable.data('dragdrop-level'))
			{
				return true;
			}

			return false;
		},


		/**
		* Switches between the wordpress editor and the AviaBuilder editor
		*
		*/
		switch_layout_mode: function(event)
		{
			var self = this,
				editor = this.tiny_active ? window.tinyMCE.get( 'content' ) : false;

			if( self.switch_button.is( '.av-builder-button-disabled' ) )
			{
				return false;
			}

			if( this.activeStatus.val() != 'active' )
			{
				if( false === this.disable_autoswitch_editor_style )
				{
					$( '#content-html' ).trigger( 'click' );
				}

				self.body_container.addClass( 'avia-advanced-editor-enabled' );
				self.body_container.removeClass( 'wp-default-editor-enabled' );
				self.classic_editor_wrap.addClass( 'avia-hidden-editor' );
				self.switch_button.addClass( 'avia-builder-active' ).text(self.switch_button.data( 'active-button' ) );
				self.activeStatus.val( 'active' );
				self.canvasParent.removeClass( 'avia-hidden' );

				setTimeout(function()
				{
					if( false === this.disable_autoswitch_editor_style )
					{
						$( '#content-tmce' ).trigger( 'click' );
					}
					self.convertTextToInterface();

					/*
					if(self.safemode && this.safemode == 'safe' && self.secureContent.val() != "")
					{
						if(editor) editor.setContent(self.secureContent.val(), {format:'html'});
						self.classic_textarea.val(self.secureContent.val());
					}
					*/

					self.body_container.trigger( 'AviaBuilder_after_switch_layout_mode', self.switch_button, self.activeStatus.val() );
				}, 100 );
			}
			else
			{
				this.body_container.removeClass( 'avia-advanced-editor-enabled' );
				this.body_container.addClass( 'wp-default-editor-enabled' );
				this.classic_editor_wrap.removeClass( 'avia-hidden-editor' );
				this.switch_button.removeClass( 'avia-builder-active' ).text( this.switch_button.data( 'inactive-button' ) );
				this.activeStatus.val( "" );
				this.canvasParent.addClass( 'avia-hidden' );
				this.canvas.addClass( 'preloading' ).find( '>*:not(.avia-controll-bar, .avia-insert-area)' ).remove();

				$( window ).trigger( 'scroll' );


				if( this.safemode && this.safemode == 'safe' && this.secureContent.val().indexOf( '[' ) !== -1 )
				{
					avia_log( 'Switching to Classic Editor. Template Builder is in safe mode and will empty the textarea so user cant edit shortcode directly' );
					if( editor )
					{
						editor.setContent( "", {format:'html'} );
					}
					this.classic_textarea.val( "" );
				}

				this.body_container.trigger( 'AviaBuilder_after_switch_layout_mode', this.switch_button, this.activeStatus.val() );
			}

			return false;
		},


		/**
		* Send element(s) to the AviaBuilder Canvas
		* Gets executed on page load to display all elements and when a single item is fetched via AJAX or HTML5 Storage
		*/
		sendToAdvancedEditor: function( text, location )
		{
			var add = $(text);

			if( 'undefined' != typeof location && 'prepend' == location )
			{
				this.canvas.prepend( add );
			}
			else
			{
				this.canvas.append( add );
			}

			this.activate_element_dragging();
			this.activate_element_dropping();

			this.body_container.trigger('av-element-to-editor');
		},


		/**
		* Updates the Textarea that holds the shortcode + values when located in a nested enviroment like columns
		*/
		updateInnerTextarea: function( element, container )
		{
		    //if we dont have a container passed but an element try to fetch the outer most possible container that wraps that element: A section
            if(typeof container == "undefined")
			{
                container = $(element).parents('.avia_layout_section').eq( 0 );
            }

            //if we got no section and no container yet check if the container is a column
            if(!container.length)
			{
                container = $(element).parents('.avia_layout_column').eq( 0 );
			}

            //stil no container? no need for an inner update
            if(!container.length)
			{
                return;
			}


            //if we are in a section iterate over all columns inside and set the value before setting the section value
            if(container.is('.avia_layout_section'))
            {
            	var columns = container.find('.avia_layout_column_no_cell');
                for (var i = 0; i < columns.length; i++)
    			{
    				this.updateInnerTextarea(false, $(columns[i]));
    			}

            	columns = container.find('.avia_layout_cell');
                for (i = 0; i < columns.length; i++)
    			{
    				this.updateInnerTextarea(false, $(columns[i]));
    			}

    			columns = container.find('.avia_layout_tab');
                for (i = 0; i < columns.length; i++)
    			{
    				this.updateInnerTextarea( false, $(columns[i]) );
    			}


    			var main_storage	= container.find('>.avia_inner_shortcode >' + this.datastorage),
                    content_fields	= container.find('>.avia_inner_shortcode > div ' +this.datastorage + ':not(.avia_layout_column .avia_sortable_element '+this.datastorage+', .avia_layout_cell .avia_layout_column ' +this.datastorage +' , .avia_layout_tab .avia_layout_column ' +this.datastorage +' )'),
                    content			= "",
				    currentName		= container.data('shortcodehandler'),
				    open_tag        = main_storage.val().match(new RegExp("\\["+currentName+"[^]*?\\]"));



    				for (var i = 0; i < content_fields.length; i++)
        			{
        				content	+= $(content_fields[i]).val();
        			}


        			content = open_tag[0]+"\n\n" + content + "[/"+ currentName +"]";
        			main_storage.val(content);
            }

            if(container.is('.avia_layout_cell'))
            {
            	var main_storage	= container.find('>.avia_inner_shortcode >' + this.datastorage),
                    content_fields	= container.find('>.avia_inner_shortcode > div ' +this.datastorage + ':not(.avia_layout_column_no_cell .avia_sortable_element '+this.datastorage+')'),
                    content			= "",
				    currentSize		= container.data('width'),
				    open_tag        = main_storage.val().match(new RegExp("\\["+currentSize+"[^]*?\\]"));

				for (var i = 0; i < content_fields.length; i++)
    			{
    				content	+= $(content_fields[i]).val();
    			}

    			content = open_tag[0]+"\n\n" + content + "[/"+ currentSize +"]";
    			main_storage.val(content);
            }

            if( container.is('.avia_layout_tab') )
            {
            	var main_storage	= container.find('>.avia_inner_shortcode >' + this.datastorage),
                    content_fields	= container.find('>.avia_inner_shortcode > div ' + this.datastorage + ':not(.avia_layout_column_no_cell .avia_sortable_element '+this.datastorage+')'),
                    content			= "",
				    currentTag		= container.hasClass( 'avia-slideshow-section-tab' ) ? 'av_slide_sub_section' : 'av_tab_sub_section',
				    open_tag        = main_storage.val().match( new RegExp( "\\[" + currentTag + "[^]*?\\]" ) );

				for( var i = 0; i < content_fields.length; i++ )
    			{
    				content	+= $(content_fields[i]).val();
    			}

    			content = open_tag[0] + "\n\n" + content + "[/" + currentTag + "]";
    			main_storage.val(content);
            }


            if(container.is('.avia_layout_column:not(.avia_layout_cell, .avia_layout_tab)'))
            {
                var main_storage	= container.find('>.avia_inner_shortcode >' + this.datastorage),
                    content_fields	= container.find('.avia_sortable_element ' + this.datastorage),
                    content			= "",
				    currentSize		= container.data('width'),
				    currentFirst	= container.is('.avia-first-col') ? " first" : "",
				    open_tag        = main_storage.val().match(new RegExp("\\["+currentSize+"[^]*?\\]"));

				for (var i = 0; i < content_fields.length; i++)
    			{
    				content	+= $(content_fields[i]).val();
    			}

    			content = open_tag[0]+"\n\n" + content + "[/"+ currentSize +"]";
    			//content = "["+currentSize+currentFirst+"]\n\n" + content + "[/"+ currentSize +"]";

    			main_storage.val(content);
            }


		},


		/**
		* Updates the Textarea that holds the shortcode + values when element is on the first level and not nested
		*/
		updateTextarea: function( scope, builder_selector, clean_data_selector, recursiveCall )
		{
			//check if the user uses the layout builder (activeStatus.val() will be active) or the shortcode editor.
			//if its the shortcode editor then the user is using a nested modal field and we dont want a textarea update
			if( this.activeStatus.val() != 'active' )
			{
				return;
			}

			var builderSelector = '.avia_layout_builder';
			if( 'string' == typeof builder_selector && builder_selector.trim() != '' )
			{
				builderSelector = builder_selector;
			}

		    if( ! scope )
		    {
		        var obj = this;
		        //if this was called without predefined scope iterate over all sections and calculate the columns withs in there, afterwards calculate the column outside
		        $( builderSelector ).find('.avia_layout_section').each(function()
		        {
		        	var col_in_section 	= $(this).find('>.avia_inner_shortcode > div > .avia_inner_shortcode'),
		        		col_in_cell	= $(this).find(' .avia_layout_cell .avia_layout_column_no_cell > .avia_inner_shortcode');

						// bugfix: section tabs do not recognise single tabs and add columns across tabs -> breaks layout
					var single_section_tabs = $(this).find('.avia_layout_tab');
					single_section_tabs.each( function()
					{
						var tab_area = $(this);
						var cells = tab_area.find('.avia_layout_column_no_cell > .avia_inner_shortcode');
						obj.updateTextarea( cells, builder_selector, clean_data_selector, true );
					});

//		        	var	coll_in_tab		= $(this).find(' .avia_layout_tab .avia_layout_column_no_cell > .avia_inner_shortcode');

//		        	if(coll_in_tab.length)
//		        	{
//                    	obj.updateTextarea(coll_in_tab);
//		        	}

						// -----> END bugfix: section tabs do not recognise

		        	if( col_in_cell.length )
		        	{
                    	obj.updateTextarea( col_in_cell, builder_selector, clean_data_selector, true );
		        	}

		        	if( col_in_section.length )
		        	{
                    	obj.updateTextarea( col_in_section, builder_selector, clean_data_selector, true );
		        	}
		        });

                scope = $(builderSelector + ' > div > .avia_inner_shortcode');
		    }

			var content_fields 	= scope.find( '>' + this.datastorage ),
				content 		= "",
				sizeCount		= 0,
				currentField,
				currentContent,
				currentParent,
				currentSize,
				sizes			= {
									'av_one_full'		:   1		,
									'av_four_fifth'		:   0.8		,
									'av_three_fourth'	:   0.75	,
									'av_two_third'		:   0.66	,
									'av_three_fifth'	:   0.6		,
									'av_one_half'		:   0.5		,
									'av_two_fifth'		:   0.4		,
									'av_one_third'		:   0.33	,
									'av_one_fourth'		:   0.25	,
									'av_one_fifth'		:	0.2
								};



			for( var i = 0; i < content_fields.length; i++ )
			{
			    currentField	= $(content_fields[i]);
			    currentParent	= currentField.parents('.avia_layout_column_no_cell').eq( 0 );
				currentContent	= currentField.val();

				//if we are checking a column we need to make sure to add/remove the first class
				if( currentParent.length )
				{
					currentSize = currentParent.data('width');
					sizeCount += sizes[currentSize];

					if(sizeCount > 1 || i == 0)
					{
						if(!currentParent.is('.avia-first-col'))
						{
							currentParent.addClass('avia-first-col');
							currentContent = currentContent.replace(new RegExp("^\\[" + currentSize), "[" + currentSize + " first");
							currentField.val(currentContent);
						}
						sizeCount = sizes[currentSize];
					}
					else if(currentParent.is('.avia-first-col'))
					{
						currentParent.removeClass('avia-first-col');
						currentContent = currentContent.replace(" first", "");
						currentField.val(currentContent);
					}
				}
				else
				{
					sizeCount = 1;
				}

				content += currentContent;
			}

			//	reroute content to a different textarea
			if( 'string' === typeof clean_data_selector && clean_data_selector.trim() != '' )
			{
				$( clean_data_selector ).val( content );
				return;
			}

			var editor = this.tiny_active ? window.tinyMCE.get('content') : undefined;

			if( typeof editor != "undefined" )
			{
				clearTimeout( this.update_timout );

				this.update_timout = setTimeout(function()
				{
					editor.setContent( window.switchEditors.wpautop( content ), {format:'html'} ); //<-- slows the whole process considerably
				}, 500 );
			}

			this.classic_textarea.val( content ).trigger( 'av_update' );
			this.secureContent.val( content ).trigger( 'av_secureContent_update', recursiveCall );
		},

		// create a snapshot for the undoredo function. timeout it so javascript has enough time to remove animation classes and hover states
		do_history_snapshot: function( timeout )
		{
			var self = this;

			if( ! timeout )
			{
				timeout = 150;
			}

			setTimeout( function()
			{
				self.canvas.trigger('avia-storage-update');
			}, timeout );
		},

		/**
		 * Used to update html in a modal group element when template for that element had been changed in popup.
		 * In that case the template is reloaded for modal popup and after that we update the canvas to show locked options.
		 *
		 * @since 4.8
		 */
		convertToModalGroupInterface: function( text, shortcodehandler, obj )
		{
			if( typeof text == 'undefined' )
			{
				return;
			}

			$.ajax({
					type: "POST",
					url: ajaxurl,
					dataType: 'json',
					cache: false,
					data:
					{
						action: 'avia_ajax_modal_group_to_interface',
						text: text,
						shortcode: shortcodehandler,
						avia_request: true,
						post_type: $('.avia-builder-main-wrap').data('post_type'),
						_ajax_nonce: $('#avia-loader-nonce').val()
					},
					success: function( response )
					{
						if( response.success == true )
						{
							obj.body_container.trigger( 'AviaBuilder_modal_group_loaded_response', [ response.html ] );
						}
					}

			});

		},

		/**
		* takes some text in shortcode format (eg: [avia_textblock]this is test[/avia_textblock]) and converts it to an editable element on
		* the AviaBuilder canvas. only executed at page load or when the editor is switched from default wordpress to avia builder.
		*
		* @since 4.8 also used to update html when element template changed (and necessary for element to show locked options)
		*/
		convertTextToInterface: function( text, trigger_event )
		{
			trigger_event = trigger_event === true;

			if( ! trigger_event )
			{
				if( this.activeStatus.val() != "active" )
				{
					this.body_container.addClass('wp-default-editor-enabled');
					this.body_container.removeClass( 'avia-advanced-editor-enabled' );
					return;
				}

				this.body_container.addClass('avia-advanced-editor-enabled');
				this.body_container.removeClass( 'wp-default-editor-enabled' );
			}

			var obj = this;

			if( typeof text == "undefined" )
			{
				text = this.secureContent.val(); //entity-test: val() to html()

				if(text.indexOf('[') === -1)
				{
					text = this.classic_textarea.val(); //entity-test: val() to html()
					if( this.tiny_active )
					{
						text = window.switchEditors._wp_Nop(text);
					}

					/**
					 * With WP 4.9 we get an empty
					 * <span style="display: inline-block; width: 0px; overflow: hidden; line-height: 0;" data-mce-type="bookmark" class="mce_SELRES_start"></span>
					 * which breaks our backend
					 */
					text = text.replace( /<\s*?span\b[^>]*mce_SELRES_start[^>]*>(.*?)<\/span\b[^>]*>/gi, '' );

					this.secureContent.val(text);
				}
			}

			// if(this.tiny_active) text = window.switchEditors._wp_Nop(text); // moved up 5 lines in order to fix this: https://github.com/AviaThemes/wp-themes/issues/573


			//sends the request. calls the the wp_ajax_avia_ajax_fetch_shortcode php function
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data:
				{
					action: 'avia_ajax_text_to_interface',
					text: text,
					avia_request: true,
					post_id: $('.avia-builder-main-wrap').data('post_id'),
					post_type: $('.avia-builder-main-wrap').data('post_type'),
					_ajax_nonce: $('#avia-loader-nonce').val()
				},
				success: function(response)
				{
					if( trigger_event )
					{
						obj.body_container.trigger( 'AviaBuilder_interface_loaded_response', [ response ] );
						return;
					}

					$('#content-tmce').trigger('click');
					obj.sendToAdvancedEditor(response);
					//obj.updateTextarea(); //dont update textarea on load, only when elements got edited
					obj.canvas.removeClass('preloading');
					obj.do_history_snapshot();

					obj.body_container.trigger('AviaBuilder_interface_loaded');
				}
			});


		},

		/**
		* activates the target mode. highlights all avialable targets like top/bottom insert and columns
		*/
		targetInsertActive: function(response, target_class)
		{
            if(!target_class)
            {
            	target_class = 'avia-hover-target avia-select-target-' + $(response).data('dragdrop-level');
            }

			$('.avia-hover').removeClass('avia-hover');
			this.canvas.addClass(target_class);
			this.targetInsert = response;
		},

		/**
		* deactivates the target mode.
		*/
		targetInsertInactive: function()
		{
			$('.shortcode_button_wrap').find('.avia-active-insert').removeClass('avia-active-insert');
			this.canvas.removeClass('avia-hover-target avia-select-target-1 avia-select-target-2 avia-select-target-3 avia-select-target-4');
			this.targetInsert = "";
			this.only_one_insert = false;
		},

		/**
		* ones the target mode is active for NEW items via targetInsertActive,
		* this function is executed as soon as the user clicks on a valid element for insertion.
		* The new element is then added to the AviaBuilder Canvas
		*/
		doTargetInsert: function(element, obj, insertMethod)
		{
		    var current 	= $(element),
		    	test_insert = typeof obj.targetInsert == 'object' ? obj.targetInsert : $(obj.targetInsert);

		    //stop if we try to insert element with low level into higher level
		    if(!obj.droping_allowed(test_insert, current)) return;


			if(typeof insertMethod == "undefined") insertMethod = "append";

			if(insertMethod == "append")
			{
				current.append(obj.targetInsert);
			}

			if(insertMethod == "prepend")
			{
				current.prepend(obj.targetInsert);
			}

			//if we are modifying an inner object update the inner textarea. inner objects are not jQuery elements and therefore got no length
			if(!element.length)
			{
				obj.updateInnerTextarea(element);
			}


			var section = current.parents('.avia_layout_section').eq( 0 );
			if(section.length)
			{
				obj.updateTextarea();
				obj.updateInnerTextarea(false, section);
			}

			if(this.only_one_insert != false)
			{
				obj.updateInnerTextarea(false, this.only_one_insert);
				obj.targetInsertInactive();
			}

			obj.activate_element_dragging();
			obj.activate_element_dropping();
			obj.updateTextarea();
			obj.do_history_snapshot();
		},

		/**
		* function that changes the currently active shortcode by pressing the left and right buttons
		*/
		changeTargetingShortcode: function(keycode)
		{
			var direction 	= keycode == 37 ? -1 : 1,
				container	= $('.shortcode_button_wrap').last(),
				buttons		= container.find('.avia-target-insert'),
				active		= buttons.filter('.avia-active-insert'),
				index		= buttons.index(active),
				next		= buttons.eq( index + direction );

			if(!next.length)
			{
				next = direction == 1 ? buttons.first() : buttons.last();
			}

			next.trigger('click');

			if(!next.is(':visible'))
			{
				var tabcontainer = container.find('.avia-tab'),
					current_cont = next.parent(),
					tabIndex	 = tabcontainer.index(current_cont),
					tabButton	 = container.find('.avia-tab-title-container a').eq( tabIndex ).trigger('click');
			}

			return false;
		},

		/**
		* function that gets executed if a user clicks the clone icon. the current element is cloned and appended after the item that was clicked on
		*/
		cloneElement: function(element, obj)
		{
			var el = $(element),
				parent = el.parents('.avia_sortable_element').eq( 0 ),
				layoutCell = false;

			if( ! parent.length )
			{
				parent = el.parents('.avia_layout_column').eq( 0 );
			}

			if( ! parent.length )
			{
				parent = el.parents('.avia_layout_section').eq( 0 );
			}

			//check if its a layout cell and if we can add one to the row
			if( parent.is('.avia_layout_cell') )
			{
				var counter = parent.parents('.avia_layout_row').eq( 0 ).find('.avia_layout_cell').length;
				if( typeof $.AviaBuilder.layoutRow.newCellOrder[counter] != "undefined" )
				{
					layoutCell = true;
				}
				else
				{
					return false;
				}
			}

			//make sure that the elments actual html code matches the value so cloning works properly
			parent.find('textarea').each(function()
			{
				this.innerHTML = this.value;
			});

			var cloned = parent.clone();

			//	clear av_uid for all cloned elements
			cloned.find( 'textarea' ).each( function()
			{
				var el = $( this );
				var shortcode = el.val();
				shortcode = $.avia_builder.clear_av_uid( shortcode );
				el.val( shortcode );
				this.innerHTML = shortcode;
			});

			//remove all previous drag/drop classes so we can apply new ones
			cloned.removeClass('ui-draggable ui-droppable').find('.ui-draggable, .ui-droppable').removeClass('ui-draggable ui-droppable');
			cloned.insertAfter(parent);

			var wrap = parent.parents('.avia_layout_section, .avia_layout_column');

			if( layoutCell )
			{
				$.AviaBuilder.shortcodes.recalcCell(element, obj);
			}

			if( parent.is('.avia_layout_column') || parent.is('.avia_layout_section') || wrap.length )
			{
			    if( wrap.length )
				{
					obj.updateTextarea();
				}

			    obj.updateInnerTextarea(parent);
			}

			obj.body_container.trigger( 'av-builder-new-element-added', cloned );

			obj.activate_element_dragging();
			obj.activate_element_dropping();
			obj.updateTextarea();
			obj.do_history_snapshot();
		},


		/**
		* function that gets executed if an element on the AviaBuilder Canvas has no popup modal but is managed via directly attached
		* form elements (eg: sidebar with dropdown)
		*
		* As these elements have no modal popup they cannot support custom elements.
		*/
		recalc_shortcode: function(element_container)
		{
			var recalcs		= element_container.find('select.avia-recalc-shortcode'),
				currentEl	= false,
				values		= [];

				for (var i = 0; i < recalcs.length; i++)
				{
					currentEl = $(recalcs[i]);
					values[currentEl.data("attr")] = currentEl.val();
				}

				this.send_to_datastorage( values, element_container );
		},


		/**
		* function that gets executed once we know how a shortcode should look like and need to insert it into the textarea that holds the shortcode
		* called by either recalc_shortcode or on modal box saving
		*
		* also checks for elements with the data attribute: "data-update_with". if such an attribute happens to have the same name as a shortcode
		* arg or content key the elements html is updated.
		* if a data-update_template is available instead of just inserting the new value insert the new value into the update template and then add the template
		*
		* also checks for elements with the data attribute: "data-update_class_with". if such an attribute happens to have the same name as a shortcode
		* arg or content key the elements classname is updated
		*/
		send_to_datastorage: function( values, element_container, return_it, element_containers )
		{
			var selector_string = element_container.is('.avia-modal-group-element') ? this.datastorage : '>.avia_inner_shortcode>'+ this.datastorage,
				saveTo = element_container.find(selector_string).eq( 0 ),
				column = element_container.parents('.avia_layout_column').eq( 0 ),
				section = element_container.parents('.avia_layout_section').eq( 0 ),
				shortcode = element_container.data('shortcodehandler'),
				output = "",
				tags = {};

			avia_log(values, false);

			//if we got a string value passed insert the string, otherwise calculate the shortcode
			if(typeof values == 'string')
			{
				output = values;
			}
			else
			{
				var return_val = this.update_builder_html(element_container, values);

				output = return_val.output;
				tags = return_val.tags;
			}


			//for modal preview we only need to return the value
			if( return_it == "return" )
			{
				return output;
			}

			//if we are working inside a section only update the shortcode open tag
			if( element_container.is('.avia_layout_section') || element_container.is('.avia_layout_column') )
			{
			    saveTo.val(saveTo.val().replace(new RegExp("^\\["+shortcode+".*?\\]"), tags.open));
			}
			else //if we are not editing a section update everything
			{
			    saveTo.val(output);
			}

			if(section.length)
			{
				this.updateInnerTextarea(false, section);
			}
			else if(column.length)
			{
				this.updateInnerTextarea(false, column);
			}

			//	we edit a custom elements
			if( 'string' == typeof element_containers && element_containers.trim() != '' )
			{
				element_containers = element_containers.split(',');
				this.updateTextarea( null, element_containers[0], element_containers[1] );
				return output;
			}

			this.updateTextarea();

			this.do_history_snapshot();
			element_container.trigger('update');

			//triggers the change event for submodal items with live preview
			saveTo.trigger('av-update-preview-instant');

			//	added 5.5 as pure js does not support jQuery trigger properly
			const opt = {
					'bubbles':		true,
					'cancelable':	true
				};

			const event = new CustomEvent( 'av-alb-canvas-updated', opt );
			element_container[0].dispatchEvent( event );

			return output;
		},


		set_shortcode_single_value: function(shortcode, key, value)
		{
			var find = "\\[.*?("+key+"=(\'|\").*?[\'|\"]).*?\\]";
			var regex = new RegExp(find);
			var match = shortcode.match(regex); //match: 0 = shortcode opener, 1 = key value pair, 2: value wrapped in ' or "

			if(match && typeof match[1] !== "undefined" ) // we got the value, replace it
			{
				var replace_regex = new RegExp(match[1]);
				shortcode = shortcode.replace(replace_regex, key + "=" + match[2] + value + match[2]);
			}
			else // we got no value, add it
			{
				var insert			= " " + key + "='" + value + "' ",
					closing_pos 	= shortcode.indexOf(']'),
					self_closing 	= shortcode.indexOf('/]');

					if(self_closing + 1 == closing_pos) closing_pos = self_closing;

				    shortcode = shortcode.substr(0, closing_pos) + insert + shortcode.substr( closing_pos );
			}

			return shortcode;

		},

		update_builder_html: function( element_container, values, force_content_close )
		{
			var output = "",
				key,
				subkey,
				new_key,
				old_val;

					// filter keys for the "aviaTB" string prefix and re-modify the key that was edited in the php html helper class
					for (key in values)
					{
						if (values.hasOwnProperty(key))
						{
							new_key = key.replace(/aviaTB/g,"");
							if(new_key != key)
							{
								old_val = typeof values[new_key] !== "undefined" ? values[new_key] + "," : "";
								values[new_key] = old_val ? old_val + values[key] : values[key];
								delete values[key];
							}
						}
					}

					// replace all single quotes  with "real" single quotes so we dont break the shortcode. not necessary in the content field
					for (key in values)
					{
						if (values.hasOwnProperty(key))
						{
							if( 'content' != key )
							{
								if( typeof values[key] == "string" )
								{
									values[key] = values[key].replace(/'(.+?)'/g,'‘$1’').replace(/'/g,'’');
									//	Add a unique id to new created elements
									if( ( 'av_uid' == key ) && ( '' == values[key].trim() ) )
									{
										values[key] = 'av-' + ( new Date().getTime()).toString(36);
									}
								}
								else if(typeof values[key] == "object")
								{
									for (subkey in values[key])
									{
										values[key][subkey] = values[key][subkey].replace(/'(.+?)'/g,'‘$1’').replace(/'/g,'’');
									}
								}

							}
						}
					}

					var shortcode		= element_container.data('shortcodehandler'),
						visual_updates	= element_container.find("[data-update_with]"),
						class_updates	= element_container.find("[data-update_class_with]"),
						closing_tag		= element_container.data("closing_tag"),
						visual_key 		= "",
						visual_el		= "",
						visual_template	= "",
						visual_update_object = '',
						update_html		= "",
						replace_val		= "",
						forceEmptyUpdateValue = element_container.hasClass( 'avia-force-empty-update-value' );

						//	check if element must have a closing tag (independent if a content exists)
						force_content_close = ( 'undefined' == typeof force_content_close ) ? false : force_content_close;
						if( true !== force_content_close )
						{
							if( ( 'string' == typeof closing_tag ) && ( 'yes' == closing_tag ) )
							{
								force_content_close = true;
							}
						}

						if( ! element_container.is( '.avia-no-visual-updates' ) )
						{
							//reset classnames
							class_updates.attr( 'class', '' );

							//update elements on the AviaBuilder Canvas like text elements to reflect those changes instantly
							visual_updates.each(function()
							{
								visual_el	= $(this);
								visual_key	= visual_el.data('update_with');
								visual_template = visual_el.data('update_template');
								visual_update_object = visual_el.data('update_object');

								if( forceEmptyUpdateValue && 'undefined' == typeof values[visual_key] )
								{
									values[visual_key] = '';
								}

								if( typeof values[visual_key] === "string" || typeof values[visual_key] === "number" || typeof values[visual_key] === "object" )
								{
									replace_val = values[visual_key];

									//apply autop to content
									if(visual_key === "content")
									{
										if(typeof window.switchEditors != 'undefined')
										{
											replace_val = window.switchEditors.wpautop(values[visual_key]);
										}
										else
										{
											//if visual editor is disabled convert newlines to br for the canvas preview
											replace_val = avia_nl2br(values[visual_key]);
										}
									}

									//in case an object is passed as replacement only fetch the first entry as replacement value by default
									if(typeof replace_val === "object")
									{
										if( visual_update_object && ( 'all-elements' == visual_update_object ) )
										{
											var str = '';
											$.each( replace_val, function( index, val ){
															if( index > 0 )
															{
																str += ', ';
															}
															str += val;
														});
											replace_val = str;
										}
										else
										{
											replace_val = replace_val[0];
										}

									}

									//check for a template
									if( visual_template )
									{
										var tmpl_replace_val = replace_val;
										var data = visual_el.data( 'update_with_keys' );
										if( 'undefined' != typeof data && 'undefined' != typeof data[ replace_val ] )
										{
											tmpl_replace_val = data[ replace_val ];
										}

										update_html = visual_template.replace( "{{" + visual_key + "}}", tmpl_replace_val );

										if( visual_template.includes( 'tmpl-hide-on-empty' ) && tmpl_replace_val.trim() == '' )
										{
											update_html = '';
										}
									}
									else
									{
										update_html = replace_val + '';		// force convert of numbers

										if( update_html.indexOf( '###avia64###:' ) !== -1 )
										{
											//	encode base64 string
											update_html = update_html.replace( '###avia64###:', '' );
											update_html = atob( update_html );
										}
									}

									//update all elements
									visual_el.html( update_html );
								}
							});


							//update element classnames on the AviaBuilder Canvas to reflect visual changes instantly
							class_updates.each(function()
							{
								visual_el	= $(this);
								visual_key	= visual_el.data('update_class_with').split(',');

								for( var i = 0; i < visual_key.length; i++ )
								{
									if(typeof values[visual_key[i]] === "string")
									{
										visual_el.get(0).className += ' avia-' + visual_key[i] + '-' + values[visual_key[i]];
									}
									else
									{
										if( typeof visual_key[i] == 'string' )
										{
											var fixed_cls = visual_key[i].match( /___(.*)___/ );
											if( fixed_cls != null && fixed_cls.length > 1 )
											{
												visual_el.get(0).className += ' ' + fixed_cls[1];
											}
										}
									}
								}
							});
						}
						else //special rule visuals for layout elements
						{
							var locked = element_container.find('.avia_data_locked_container').first();

							if( typeof values.id != 'undefined' && values.id != null ) // set the section or grid cell id
							{
								var insert_id_title = values.id == "" ? "" : ": " + values.id;
								element_container.find(".avia-element-title-id").eq( 0 ).text( insert_id_title );
							}

							if( element_container.find( ".avia-element-bg-color" ).eq( 0 ).length ) // set the bg color indicator
							{
								var insert_bg_indicator = '';
								var background = typeof locked.data( 'locked_background' ) == 'undefined' ? values.background : locked.data( 'locked_background' );

                                if( background == 'bg_color' )
								{
									if( typeof locked.data( 'locked_custom_bg' ) != 'undefined' )
									{
										insert_bg_indicator = locked.data( 'locked_custom_bg' );
									}
									else if( typeof locked.data( 'locked_background_color' ) != 'undefined' )
									{
										insert_bg_indicator = locked.data( 'locked_background_color' );
									}
									else
									{
										insert_bg_indicator = values.custom_bg || values.background_color || "";
									}
                                }
                                else if( background == 'bg_gradient' )
								{
									var gradient_color1 = typeof locked.data( 'locked_background_gradient_color1' ) == 'undefined' ? values.background_gradient_color1 : locked.data( 'locked_background_gradient_color1' );
									var gradient_color2 = typeof locked.data( 'locked_background_gradient_color2' ) == 'undefined' ? values.background_gradient_color2 : locked.data( 'locked_background_gradient_color2' );

                                    if( gradient_color1 !== "" && gradient_color2 !== "" )
									{
                                        insert_bg_indicator = "linear-gradient(" + gradient_color1 + ", " + gradient_color2 + ")";
                                    }
                                }

								element_container.find(".avia-element-bg-color").eq( 0 ).css("background",insert_bg_indicator);
							}

							var tab_title = typeof values.tab_title != 'undefined' && values.tab_title != null ? values.tab_title : null;
							if( typeof locked.data( 'locked_tab_title' ) != 'undefined' )
							{
								tab_title = locked.data( 'locked_tab_title' );
							}

							if( tab_title != null ) // set the tab title of a tab element
							{
								var insert_tab_title 	= values.tab_title == "" ? "" : ": " + tab_title,
									tab_index			= element_container.data('av-tab-section-content'),
									parent				= element_container.parents('.avia_tab_section').eq( 0 );

									parent.find( '.avia_tab_section_titles [data-av-tab-section-title="' + tab_index + '"] .av-tab-custom-title' ).html( insert_tab_title );
							}

							var layout_image = typeof values.src != 'undefined' && values.src != null ? values.src : null;
							if( typeof locked.data( 'locked_src' ) != 'undefined' )
							{
								layout_image = locked.data( 'locked_src' ) != '' ?  locked.data( 'locked_src' ) : null;
							}

							if( layout_image != null ) // set the bg image
							{
								var layout_pos = values.position || values.background_position || "",
									layout_repeat = values.repeat || values.background_repeat || "",
									layout_extra = "",
									layout_style = {};

								if( typeof locked.data( 'locked_position' ) != 'undefined' )
								{
									layout_pos = locked.data( 'locked_position' );
								}

								if( typeof locked.data( 'locked_background_position' ) != 'undefined' )
								{
									layout_pos = locked.data( 'locked_background_position' );
								}

								if( typeof locked.data( 'locked_repeat' ) != 'undefined' )
								{
									layout_repeat = locked.data( 'locked_repeat' );
								}

								if( typeof locked.data( 'locked_background_repeat' ) != 'undefined' )
								{
									layout_repeat = locked.data( 'locked_background_repeat' );
								}

								if( layout_repeat == "stretch" )
								{
									layout_repeat = "no-repeat";
									layout_extra = "cover";
								}

								if( layout_repeat == "contain" )
								{
									layout_repeat = "no-repeat";
									layout_extra = "contain";
								}

								layout_style = {"background": "transparent url(" + layout_image + ") " + layout_repeat + " " + layout_pos };
								if( layout_extra )
								{
									layout_style['backgroundSize'] = layout_extra;
								}

								element_container.find( ".avia-layout-element-bg" ).last().css( layout_style );
							}
						}

						//remove fake argumens that were only used for nicer look of backend
						for( key in values )
						{
							if( values.hasOwnProperty( key ) && key.indexOf( '_fakeArg' ) !== -1 )
							{
								delete values[key];
							}
						}

						//create the shortcode string out of the arguments and save it to the data storage textarea
						var tags = {},
							return_val = {};

						return_val.output = this.createShortcode( values, shortcode, tags, force_content_close );
						return_val.tags = tags;
						return_val.values = values;

						return return_val;
		},



		/**
		* Function that gets executed by send_to_datastorage or from tinyMCE shortcode wand button onSave callback.
		* Creates the actual shortcode string out of the arguments and content
		*/
		createShortcode: function( values, shortcode, tag, force_content_close )
		{
			var key,
				output = "",
				attr = "",
				content = "",
				i,
				array_seperator = ",",
				line_break = "\n";

			if( ! tag )
			{
				tag = {};
			}

			//create content var
			if(typeof values.content != 'undefined')
			{
				//check if the content var is an array of items
				if(typeof values.content == 'object')
				{
					//if its an array check if its an array of sub-shortcodes eg (contact form fields), if so switch the array_separator to line break
					if( values.content[0].indexOf('[') != -1)
					{
						array_seperator = line_break;
					}

					//trim spaces and line breaks from the array
					for( i = 0; i < values.content.length; i++ )
					{
						values.content[i] = values.content[i].trim();
					}

					//join the array into a single string
					content = values.content.join( array_seperator );
				}
				else
				{
					content = values.content ;
				}

				//	hack: fix bug that this option is added to subitems when saving the main shortcode
				if( content.indexOf('[') != -1)
				{
					content = content.replace( /show_locked_options_fakeArg=''/gi, '' );
				}

				content = line_break + content + line_break ;
				delete values.content;
			}

			//create attr string
			for( key in values )
			{
				if( values.hasOwnProperty( key ) )
				{
					if( isNaN( key ) ) /*if the key is an integer like zero we probably need to deal with the "first" value from columns or cells. in that case dont add the key, only the value*/
					{
						//	ignore attr only needed for better backend styling or internal use
						if( key.indexOf('_fakeArg') != -1 )
						{
							continue;
						}

						if( typeof values[key] === 'object' && values[key] !== null )
						{
							values[key] = values[key].join(',');
						}

			        	attr += key + "='" + values[key] + "' ";
					}
					else
					{
						attr += values[key] + " ";
					}
			    }
			}

			tag.open = "[" + shortcode + " " + attr.trim() + "]";
			output = tag.open;

			if(content || (typeof force_content_close !== 'undefined' && force_content_close == true))
			{
				if( content.trim() == "" )
				{
					content = "";
				}

				tag.close = "[/"+shortcode+"]";
				output += content + tag.close;
			}

			output += line_break + line_break;

			return output;
		}
	};

	$( function()
	{
    	$.avia_builder = new $.AviaBuilder();
	});

})(jQuery);


(function($)
{
	"use strict";

    $.AviaBuilder.shortcodes = $.AviaBuilder.shortcodes || {};

    $.AviaBuilder.shortcodes.fetchShortcodeEditorElement = function(shortcode, insert_target, obj)
	{
		var template = $("#avia-tmpl-"+shortcode);

		if(template.length)
		{
			if(insert_target == 'instant_insert')
			{
				obj.sendToAdvancedEditor(template.html());
				obj.updateTextarea();
				obj.do_history_snapshot(0);
			}
			else
			{
				obj.targetInsertActive(template.html());
			}

			return;
		}
	},

	$.AviaBuilder.shortcodes.deleteItem = function(clicked, obj, hide_timer)
	{
		var $_clicked = $(clicked),
			item      = $_clicked.parents('.avia_sortable_element').eq( 0 ), parent = false, removeCell = false, item_hide = 200, force_drop_init = false;

		if(typeof hide_timer != 'undefined') item_hide = hide_timer;

		//check if it is a column
		if(!item.length)
		{
			item = $_clicked.parents('.avia_layout_column').eq( 0 );
			parent = $_clicked.parents('.avia_layout_section').eq( 0 ).find('>.avia_inner_shortcode');

			//check if it is a section
			if(!item.length)
			{
			    item = $_clicked.parents('.avia_layout_section').eq( 0 );
			    parent = false;
			}

		}
		else
		{
			parent = $_clicked.parents('.avia_inner_shortcode').eq( 0 );
		}

		if(item.length && item.is('.avia_layout_cell'))
		{
			if(parent.find('.avia_layout_cell').length > 1)
			{
				removeCell = true;
				item_hide = 0;
			}
			else
			{
				return false;
			}
		}

		obj.targetInsertInactive();

		item.hide(item_hide, function()
		{
			if(removeCell)
			{
				$.AviaBuilder.shortcodes.removeCell(clicked, obj);
			}

			item.remove();
			if(parent && parent.length)
			{
				obj.updateInnerTextarea(parent);
				var parent_container = parent.parents('.avia_layout_section').eq( 0 ),
					parent_cell		 = parent.find('.avia_layout_cell').eq( 0 );



				if(parent_container.length || parent_cell.length)
				{
					//if the section is empty -> bugfix for column delete that renders the section unusable
					if( parent_container.length && parent_container.find(".avia_inner_shortcode .avia_inner_shortcode " + obj.datastorage).val() == 'undefined')
					{
						obj.activate_element_dropping(parent_container, "destroy");
					}


/*					todo: apply fix for layouts to grid cells as well

					if( parent_cell.length && String(parent_cell.find(".avia_inner_shortcode .avia_inner_shortcode " + obj.datastorage).val()) == 'undefined')
					{
						obj.activate_element_dropping(parent_cell, "destroy");
					}
*/

				}
			}

			obj.updateTextarea();

			//bugfix for column delete that renders the canvas undropbable for unknown reason
			if(obj.secureContent.val() == "") { obj.activate_element_dropping(obj.canvasParent, "destroy"); }


			obj.do_history_snapshot();

			$( 'body' ).trigger( 'av-element-deleted-from-editor' );
		});
	},

	$.AviaBuilder.shortcodes.deleteModalSubItem = function(clicked, e)
	{
		e.stopImmediatePropagation();

		var $_clicked = $(clicked),
			item      = $_clicked.parents('.avia-modal-group-element').eq( 0 ),
			container = item.parents('.avia-modal-group').eq( 0 );

		container.trigger('av-item-delete', [item]);

		item.slideUp(200, function()
		{
			item.remove();

			//trigger update for preview
			container.find('textarea[data-name="text-shortcode"]').eq( 0 ).trigger('av-update-preview-instant');

		});
	},

	$.AviaBuilder.shortcodes.appendModalSubItem = function(clicked, e, action)
	{
		e.preventDefault();

		var $_clicked	= $(clicked),
			wrap		= $_clicked.parents('.avia-modal-group-wrapper').eq( 0 ),
			parent		= wrap.find('.avia-modal-group'),
			template	= wrap.find('.avia-tmpl-modal-element'),
			newTemplate	= "";

			if(action != "clone")
			{
				newTemplate = $(template.html()).appendTo(parent).css({display:"none"});
			}
			else
			{
				newTemplate = wrap.find('.avia-modal-group-element').last().clone().appendTo(parent).css({display:"none"});
			}

			newTemplate.slideDown(200);

			parent.trigger('av-item-add', [newTemplate]);

			//trigger update for preview
			newTemplate.find('textarea[data-name="text-shortcode"]').trigger('av-update-preview-instant');
	},




	$.AviaBuilder.shortcodes.changeSize = function(clicked, obj)
	{
		var item		= $(clicked),
			container	= item.parents('.avia_layout_column').eq( 0 ),
			section     = container.parents('.avia_layout_section').eq( 0 ),
			currentSize	= container.data('width'),
			nextSize	= [],
			direction	= item.is('.avia-bigger') ? 1 : -1,
			sizeString	= container.find('.avia-col-size'),
			dataStorage	= container.find('> .avia_inner_shortcode > '+obj.datastorage),
			dataString	= dataStorage.val(),
			sizes		= [
							['av_one_full'	,		'1/1'],
							['av_four_fifth',		'4/5'],
							['av_three_fourth',		'3/4'],
							['av_two_third',		'2/3'],
							['av_three_fifth',		'3/5'],
							['av_one_half',			'1/2'],
							['av_two_fifth',		'2/5'],
							['av_one_third',		'1/3'],
							['av_one_fourth',		'1/4'],
							['av_one_fifth',		'1/5']
						];



		for( var i = 0; i < sizes.length; i++ )
		{
		    if(sizes[i][0] == currentSize)
		    {
		    	nextSize =  sizes[i - direction];
		    }
		}

		if(typeof nextSize != 'undefined')
		{
			dataString = dataString.replace(new RegExp("^\\[" + currentSize, 'g'), "[" + nextSize[0]);
			dataString = dataString.replace(new RegExp( currentSize + "\\]", 'g'), nextSize[0] + "]");

			dataStorage.val(dataString);
			container.removeClass(currentSize).addClass(nextSize[0]);

			//make sure to also set the data attr so html() functions fetch the correct value
			container.attr( 'data-shortcodehandler', nextSize[0] ).data( 'shortcodehandler', nextSize[0] );
			container.attr('data-width',nextSize[0]).data('width',nextSize[0]);
			container.attr('data-modal_ajax_hook',nextSize[0]).data('modal_ajax_hook',nextSize[0]);
			container.attr('data-allowed-shortcodes',nextSize[0]).data('allowed-shortcodes',nextSize[0]);

			sizeString.text(nextSize[1]);

			obj.updateTextarea();
            if(section.length){ obj.updateInnerTextarea(false, section); obj.updateTextarea(); }
            obj.do_history_snapshot(0);
		}


	};


	/*function necessary for row/cell management*/

	$.AviaBuilder.shortcodes.addCell =  function(clicked, obj)
	{
		$.AviaBuilder.layoutRow.modifyCellCount(clicked, obj, 0);
	};

	$.AviaBuilder.shortcodes.removeCell =  function(clicked, obj)
	{
		$.AviaBuilder.layoutRow.modifyCellCount(clicked, obj, -2);
	};

	$.AviaBuilder.shortcodes.recalcCell =  function(clicked, obj)
	{
		$.AviaBuilder.layoutRow.modifyCellCount(clicked, obj, -1);
	};

	$.AviaBuilder.shortcodes.setCellSize =  function(clicked, obj)
	{
		$.AviaBuilder.layoutRow.setCellSize(clicked, obj);
	};


	$.AviaBuilder.layoutRow = {

		cellSize: [
							['av_cell_one_full'	,		'1/1', 1   ],
							['av_cell_four_fifth',		'4/5', 0.8 ],
							['av_cell_three_fourth',	'3/4', 0.75],
							['av_cell_two_third',		'2/3', 0.66],
							['av_cell_three_fifth',		'3/5', 0.6 ],
							['av_cell_one_half',		'1/2', 0.5 ],
							['av_cell_two_fifth',		'2/5', 0.4 ],
							['av_cell_one_third',		'1/3', 0.33],
							['av_cell_one_fourth',		'1/4', 0.25],
							['av_cell_one_fifth',		'1/5', 0.2 ]
						],

		newCellOrder: [
							['av_cell_one_full'	,		'1/1'],
							['av_cell_one_half',		'1/2'],
							['av_cell_one_third',		'1/3'],
							['av_cell_one_fourth',		'1/4'],
							['av_cell_one_fifth',		'1/5']
						],

		cellSizeVariations: {

		4:{
			1:[ 'av_cell_one_fourth', 	'av_cell_one_fourth', 'av_cell_one_fourth' 	, 'av_cell_one_fourth'	],
			2:[ 'av_cell_one_fifth', 	'av_cell_one_fifth' , 'av_cell_one_fifth' 	, 'av_cell_two_fifth'	],
			3:[ 'av_cell_one_fifth',	'av_cell_one_fifth' , 'av_cell_two_fifth' 	, 'av_cell_one_fifth'	],
			4:[ 'av_cell_one_fifth',	'av_cell_two_fifth' , 'av_cell_one_fifth' 	, 'av_cell_one_fifth'	],
			5:[ 'av_cell_two_fifth',	'av_cell_one_fifth' , 'av_cell_one_fifth' 	, 'av_cell_one_fifth'	]
		},
		3:{
			1:[ 'av_cell_one_third', 	'av_cell_one_third' , 	'av_cell_one_third'		],
			2:[ 'av_cell_one_fourth', 	'av_cell_one_fourth' , 	'av_cell_one_half'		],
			3:[ 'av_cell_one_fourth', 	'av_cell_one_half' , 	'av_cell_one_fourth'	],
			4:[ 'av_cell_one_half', 	'av_cell_one_fourth' , 	'av_cell_one_fourth'	],
			5:[ 'av_cell_one_fifth', 	'av_cell_one_fifth' , 	'av_cell_three_fifth'	],
			6:[ 'av_cell_one_fifth', 	'av_cell_three_fifth' , 'av_cell_one_fifth'		],
			7:[ 'av_cell_three_fifth', 	'av_cell_one_fifth' , 	'av_cell_one_fifth'		],
			8:[ 'av_cell_one_fifth', 	'av_cell_two_fifth' , 	'av_cell_two_fifth'		],
			9:[ 'av_cell_two_fifth', 	'av_cell_one_fifth' , 	'av_cell_two_fifth'		],
			10:[ 'av_cell_two_fifth', 	'av_cell_two_fifth' , 	'av_cell_one_fifth'		]
		},
		2:{
			1:[ 'av_cell_one_half', 	'av_cell_one_half' 		],
			2:[ 'av_cell_two_third', 	'av_cell_one_third' 	],
			3:[ 'av_cell_one_third', 	'av_cell_two_third' 	],
			4:[ 'av_cell_one_fourth', 	'av_cell_three_fourth'	],
			5:[ 'av_cell_three_fourth', 'av_cell_one_fourth'	],
			6:[ 'av_cell_one_fifth', 	'av_cell_four_fifth' 	],
			7:[ 'av_cell_four_fifth', 	'av_cell_one_fifth' 	],
			8:[ 'av_cell_two_fifth', 	'av_cell_three_fifth' 	],
			9:[ 'av_cell_three_fifth', 	'av_cell_two_fifth' 	]
			}
		},


		modifyCellCount: function(clicked, obj, direction)
		{
			var item	= $(clicked),
				row		= item.parents('.avia_layout_row').eq( 0 ),
				cells	= row.find('.avia_layout_cell'),
				counter = cells.length + direction,
				newEl	= $.AviaBuilder.layoutRow.newCellOrder[counter];

				if(typeof newEl != "undefined")
				{
					if(counter != cells.length ) //remove or recalculate
					{
						$.AviaBuilder.layoutRow.changeMultipleCellSize(cells, newEl, obj);
					}
					else
					{
						$.AviaBuilder.layoutRow.changeMultipleCellSize(cells, newEl, obj);
						$.AviaBuilder.layoutRow.insertCell(row, newEl, obj);
						obj.activate_element_dropping();
			        }

			        obj.updateInnerTextarea(false, row);
			        obj.updateTextarea();
			        obj.do_history_snapshot(0);
		        }
		},

		insertCell: function(row, newEl, obj)
		{
			var dataStorage		= row.find('> .avia_inner_shortcode'),
				shortcodeClass 	= newEl[0].replace('av_cell_', 'avia_sc_cell_').replace('_one_full',''),
				template 		= $($("#avia-tmpl-"+shortcodeClass).html());

			dataStorage.append(template);
		},

		changeMultipleCellSize: function(cells, newEl, obj, multi)
		{
			var new_size		= newEl,
				key				= "";

			cells.each(function(i)
			{
				if(multi)
				{
					key = newEl[i];
					for (var x in $.AviaBuilder.layoutRow.cellSize)
					{
						if(key == $.AviaBuilder.layoutRow.cellSize[x][0])
						{
							new_size = $.AviaBuilder.layoutRow.cellSize[x];
						}
					}

				}

				$.AviaBuilder.layoutRow.changeSingleCellSize( $( this ), new_size, obj );
			});
		},


		changeSingleCellSize: function($el, nextSize, obj)
		{
			var sizeString	= $el.find('> .avia_sorthandle > .avia-col-size'),
				currentSize	= $el.data('width'),
				dataStorage	= $el.find('> .avia_inner_shortcode > '+obj.datastorage),
				dataString	= dataStorage.val();

				dataString = dataString.replace(new RegExp("^\\[" + currentSize, 'g'), "[" + nextSize[0]);
				dataString = dataString.replace(new RegExp( currentSize + "\\]", 'g'), nextSize[0] + "]");

				dataStorage.val(dataString);
				$el.removeClass(currentSize).addClass(nextSize[0]);
				$el.attr('data-width',nextSize[0]).data('width',nextSize[0]); //make sure to also set the data attr so html() functions fetch the correct value
				$el.attr('data-shortcodehandler',nextSize[0]).data('shortcodehandler',nextSize[0]); //make sure to also set the data attr so html() functions fetch the correct value
				$el.attr('data-allowed-shortcodes',nextSize[0]).data('allowed-shortcodes',nextSize[0]); //make sure to also set the data attr so html() functions fetch the correct value
				sizeString.text(nextSize[1]);
		},

		setCellSize: function(clicked, obj)
		{
			var item		= $(clicked),
				row     	= item.parents('.avia_layout_row').eq( 0 ),
				all			= row.find('.avia_layout_cell'),
				rowCount	= all.length,
				variations = this.cellSizeVariations[rowCount],
				htmlString = "",
				label = "",
				labeltext = "",
				mclass		= "highscreen";

			if(variations)
			{
				htmlString += "<form>";

				for (var i in variations)
				{
					label = "";
					labeltext = "";

					for (var x in variations[i])
					{
						for(var z in this.cellSize)
						{
							if(this.cellSize[z][0] == variations[i][x]) labeltext = this.cellSize[z][1];
						}
						label += "<span class='av-modal-label-"+variations[i][x]+"'>"+labeltext+"</span>";
					}

					htmlString += "<div class='avia-layout-row-modal'><label class='avia-layout-row-modal-label'>";
					htmlString += "<input type='radio' name='layout' value='"+i+"' /><span class='av-layout-row-inner-label'>"+label+"</span></label></div>";
				}

				htmlString += "</form>";
			}
			else
			{
				htmlString += "<p>" +avia_modal_L10n.no_layout + "<br/>";
				if(rowCount == 1)
				{
					htmlString += avia_modal_L10n.add_one_cell;
				}
				else
				{
					htmlString += avia_modal_L10n.remove_one_cell;
				}
				htmlString += "</p>";
				mclass = "flexscreen";
			}

			new $.AviaModalNotification(
            {
                msg:htmlString,
                modal_class: mclass,
                modal_title: avia_modal_L10n.select_layout,
                button:'save',
                scope: this,
                on_save: this.saveModal,
                save_param: {obj: obj, variations: variations, row: row, all: all}
            });
		},


		saveModal: function(values, save_param)
		{
			var index = (values && values.layout) ? values.layout : false;

			if(!index) return;

			$.AviaBuilder.layoutRow.changeMultipleCellSize(save_param.all, save_param.variations[index], save_param.obj, true);
			save_param.obj.updateInnerTextarea(false, save_param.row);
    		save_param.obj.updateTextarea();
    		save_param.obj.do_history_snapshot(0);
		}

	};

})(jQuery);
