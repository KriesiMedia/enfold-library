/*
 * Plugin to integrate ALB in Gutenberg page
 *
 * To stay backwards compatible Gutenberg addon creates a similar structure to classic editor.
 * TinyMCE is placed after Gutenberg section and outside the ALB metabox which includes a hidden switch layout button
 * and we trigger the "old" hidden switch editor style button.
 *
 * In this way we reduce the changes to some CSS and we add some trigger events for callback.
 *
 *
 * @since 4.5.1
 * @added_by Günter
 */

(function($)
{
	'use strict';

	$.AviaBuilderGutenberg = function()
	{
		//	Flag to check if we can use the object
		this.init = false;

		//	Instance of builder attached to
		this.aviaBuilder = null;

		//	Body container
		this.body_container = $('body').eq( 0 );
		this.body_container.addClass('avia-gutenberg-not-init');

		//	Metabox container
		this.metabox = $('#avia_alb_actions');

		//	Layout switch button, copied to header (remains hidden in metabox to be able to restore if gutenberg removes it dynamically from header)
		this.gutenberg_switch_button_source = this.metabox.find('.avia-builder-button');

		this.permalink_container = null;

		//	Monitor autosave so we can save changes to ALB content (metboxes are not sent to server on autosave WP 5.0)
		this.auto_save_observer = null;

		//	We save the last autosave time to reduce calls to server
		this.last_autosave = 0;
		this.minimum_no_autosave = 60;		//	in seconds

		if( $.avia_builder == typeof 'undefined' )
		{
			this.body_container.on( 'AviaBuilder_after_set_up', this.set_up.bind( this ) );
		}
		else
		{
			this.set_up();
		}

	};

	$.AviaBuilderGutenberg.prototype =
	{
		/**
		 *
		 * @returns {undefined}
		 */
		set_up: function()
		{
			this.aviaBuilder = $.avia_builder;
			this.aviaBuilder.disable_autoswitch_editor_style = true;

			//	overwrite, because HTML structure has changed (we moved editor outside meta box)
			if( 'undefined' != typeof( this.aviaBuilder.switch_button.length ) && this.aviaBuilder.switch_button.length > 0 )
			{
				this.aviaBuilder.switch_button.off('click');
			}
			this.aviaBuilder.switch_button = $('#postdivrich_wrap_builder_meta').find('.avia-builder-button');

			this.permalink_container = $('#avia_builder #titlediv #edit-slug-box');

			this.wait_for_gutenberg_init();
			this.attach_handlers();
		},

		wait_for_gutenberg_init: function()
		{
			var header_container = this.get_gutenberg_header_settings_container();
			var self = this;

			if( header_container.length == 0 )
			{
				//	DOM is not ready
				setTimeout(function(){
						self.wait_for_gutenberg_init();
					}, 100 );

				return;
			}

			this.copy_layout_switch_button();
			this.monitor_save_infos();

			/**
			 * FF fix:	When post is saved in ALB, switch to normal and an immediate pagereload leaves hidden input field on "normal" -> breaks output
			 *			Also the other way round.
			 */
			if( navigator.userAgent.indexOf("Firefox") != -1 )
			{
				var fix = false;
				var html = this.aviaBuilder.switch_button.html();

				if( this.aviaBuilder.activeStatus.val() == 'active')
				{
					fix = this.aviaBuilder.switch_button.data('active-button') != html;
				}
				else
				{
					fix = this.aviaBuilder.switch_button.data('inactive-button') != html;
				}

				if( fix )
				{
					console.log('FF fix executed');

					this.aviaBuilder.switch_button.trigger('click');
				}
			}

			//	Force to open ALB on new element (hides editor button with CSS)
			if( this.aviaBuilder.body_container.hasClass( 'avia-force-alb' ) )
			{
				if( this.aviaBuilder.activeStatus.val() != 'active' )
				{
					this.aviaBuilder.switch_button.trigger( 'click' );
				}
			}

			this.aviaBuilder.builder_drag_drop_container = $('body.block-editor-page .block-editor #avia_builder').closest('.edit-post-layout__metaboxes');

			/**
			 * Make sure we have only a single shortcode block when an ALB page
			 * (When opening existing non block pages we get a broken tinyMCE)
			 */
			if( this.aviaBuilder.activeStatus.val() == 'active' )
			{
				var content = this.aviaBuilder.secureContent.val();
				this.set_gutenberg_post_content( content );

				for( var i = 0; i < tinymce.editors.length; i++ )
				{
					var editorInstance = tinymce.editors[i];
					var id = editorInstance.id.toLowerCase().trim();
					tinymce.remove( '#' + id );
				}

				this.select_document_tab();
			}

			this.attach_metabox_toggle_fix();

			this.init = true;
			this.body_container.removeClass( 'avia-gutenberg-not-init' );
		},

		copy_layout_switch_button: function()
		{
			var btn = this.get_layout_switch_button();
			if( false !== btn )
			{
				return btn;
			}

			this.gutenberg_switch_button_source.hide();
			var new_button = this.gutenberg_switch_button_source.clone();

			var container = this.get_gutenberg_header_settings_container();
			container.prepend( new_button );

			new_button.show().attr( 'id', '' );
			new_button.on( 'click', this.switch_layout_mode_gutenberg_btn.bind( this ) );

			return new_button;
		},

		/**
		 * Is changed dynamically during publishing a new post - we need to query DOM
		 */
		get_gutenberg_header_settings_container: function()
		{
			//	with WP 6.6 class was changed to editor-header__settings - we add our class to allow our code and CSS to work
			var container = $( '.edit-post-header__settings' ).first();

			if( ! container.length )
			{
				container = $( '.editor-header__settings' ).first();
				container.addClass( 'edit-post-header__settings' );
			}

			return container;
		},

		get_layout_switch_button: function()
		{
			var btn = this.get_gutenberg_header_settings_container().find( '.avia-builder-button' ).first();
			return btn.length > 0 ? btn : false;
		},

		get_gutenberg_save_draft_button: function()
		{
			var btn = this.get_gutenberg_header_settings_container().find( '.editor-post-save-draft' ).first();
			return btn.length > 0 ? btn : false;
		},

		get_gutenberg_publish_button: function()
		{
			var btn = this.get_gutenberg_header_settings_container().find( '.editor-post-publish-button' ).first();
			return btn.length > 0 ? btn : false;
		},

		get_gutenberg_switch_to_draft_button: function()
		{
			var btn = this.get_gutenberg_header_settings_container().find( '.editor-post-switch-to-draft' ).first();
			return btn.length > 0 ? btn : false;
		},

		get_gutenberg_publish_new_post_button: function()
		{
			var btn = this.get_gutenberg_header_settings_container().find( '.editor-post-publish-panel__toggle' ).first();
			return btn.length > 0 ? btn : false;
		},

		get_gutenberg_title: function()
		{
			var title = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' );
			return title;
		},

		set_gutenberg_title: function( value )
		{
			var value = 'string' == typeof value ? value.trim() : '';
			wp.data.dispatch( 'core/editor' ).editPost( {
								title: value
							} );
		},

		get_alb_title: function()
		{
			var title = this.body_container.find( '.avia_meta_box_visual_editor #titlewrap input' ).first();
			return title.length > 0 ? title.val() : false;
		},

		set_alb_title: function( value, focus )
		{
			var title = this.body_container.find( '.avia_meta_box_visual_editor #titlewrap input' ).first();
			var label = this.body_container.find( '.avia_meta_box_visual_editor #titlewrap #title-prompt-text' );
			var value = 'string' == typeof value ? value.trim() : '';
			title.val( value );

			//	remove placeholder
			if( label.length > 0 )
			{
				if( value.trim() == '' )
				{
					label.removeClass( 'screen-reader-text' );
				}
				else
				{
					label.addClass( 'screen-reader-text' );
				}
			}
			else
			{
				//	this is a fallback to the beginning of block editor - no idea when label was added
				if( ( value != '' ) && ( 'undefined' != typeof focus ) && ( focus == 'focus' ) )
				{
					setTimeout( function()
					{
						title.trigger( 'focus' );
					}, 300 );
				}
			}
		},

		can_publish: function()
		{
			if( ! this.init )
			{
				return false;
			}

			var publish = this.get_gutenberg_publish_button();
			if( false !== publish )
			{
				return true;
			}

			var new_post = this.get_gutenberg_publish_new_post_button();
			if( false !== new_post )
			{
				if( 'undefined' == typeof new_post.attr( 'disabled' ) )
				{
					return true;
				}
			}

			return false;
		},

		is_new_post: function()
		{
			var draft = this.get_gutenberg_save_draft_button();
			if( false === draft )
			{
				return true;
			}

			if( 'undefined' == typeof draft.attr( 'disabled' ) )
			{
				return true;
			}

			var new_post = this.get_gutenberg_publish_new_post_button();
			return false !== new_post;
		},

		attach_gutenberg_title_event_handler: function()
		{
			var title = $( '.gutenberg__editor .edit-post-visual-editor .editor-post-title__block textarea.editor-post-title__input' ).first();
			var self = this;

			if( title.length == 0 )
			{
				setTimeout(function(){
						self.attach_gutenberg_title_event_handler();
					}, 100 );

				return;
			}

			title.on( 'keyup', this.gutenberg_title_keyup.bind( this ) );
		},

		attach_edit_permalink_event_handler: function()
		{
			this.body_container.find( '.av-edit-alb-permalink' ).on( 'click', this.alb_edit_permalink.bind( this ) );
		},

		attach_alb_permalink_href_handler: function()
		{
			this.body_container.find( '.avia_meta_box_visual_editor #edit-slug-box #sample-permalink > a' ).on( 'click', this.alb_permalink_href_handler.bind( this ) );
		},

		attach_metabox_toggle_fix: function()
		{
			if( this.body_container.hasClass( 'block-editor-page' ) && this.body_container.hasClass( 'avia-advanced-editor-enabled' ) )
			{
				this.body_container.find( '.postbox-header .handlediv' ).on( 'click', this.metabox_toggle_fix );
			}
		},

		attach_handlers: function()
		{
			var self = this;

			this.body_container.on( 'AviaBuilder_after_switch_layout_mode', this.layout_mode_changed.bind( this ) );
			this.body_container.find( '.avia_meta_box_visual_editor #titlewrap input' ).on( 'keyup', this.alb_title_key_up.bind( this ) );
			this.attach_gutenberg_title_event_handler();
			this.aviaBuilder.secureContent.on( 'av_secureContent_update change', this.alb_secureContent_changed.bind( this ) );

			//	Needs to be overwritten because we moved the editor
			var obj = this.aviaBuilder;
			obj.switch_button.on( 'click', function(e){
												e.preventDefault();
												obj.switch_layout_mode(e);
										});

			this.attach_edit_permalink_event_handler();
			this.attach_alb_permalink_href_handler();

			setTimeout(function()
            {
                self.attach_alb_sticky_element_tab_handler();
            }, 500);
		},

		/**
		 * Triggers a clickevent on the standard ALB switch button
		 */
		switch_layout_mode_gutenberg_btn: function(e)
		{
			e.preventDefault();

			if( this.aviaBuilder.activeStatus.val() != 'active' )
			{
				if( ! this.set_alb_secure_content() )
				{
					return;
				}

				/**
				 * Problem with tinyMCE: We need to remove our content editor if not the last one
				 * otherwise toolbar of visual editor and selectors get broken.
				 */
				for( var i = 0; i < tinymce.editors.length; i++ )
				{
					var editorInstance = tinymce.editors[i];
					var id = editorInstance.id.toLowerCase().trim();

					if( ( 'content' == id ) && ( i != tinymce.editors.length - 1 ) )
					{
						tinymce.remove( '#' + id );
					}
				}

				this.set_alb_title( this.get_gutenberg_title(), 'focus' );


				//	this class is set when editing the pretty permalink, which needs a switchback to Gutenberg editor
				this.body_container.removeClass('avia-edit-permalink');
				this.alb_sync_permalink();
				this.select_document_tab();
			}
			else
			{
				this.set_gutenberg_title( this.get_alb_title() );
				var content = this.aviaBuilder.secureContent.val();
				this.set_gutenberg_post_content( content );
			}

			this.aviaBuilder.switch_button.trigger( 'click' );
		},

		select_document_tab: function()
		{
			//	Make sure we have Document tab selected
			var doc_button = this.body_container.find( '.edit-post-sidebar .components-panel__header button.edit-post-sidebar__panel-tab' ).eq( 0 );
			if( ! doc_button.hasClass( 'is-active' ) )
			{
				doc_button.trigger( 'click' );
			}
		},

	    attach_alb_sticky_element_tab_handler: function ()
        {
            var builder = $('#avia_builder');

            function debounce(method, delay)
            {
                clearTimeout(method._tId);
                method._tId = setTimeout(function ()
                {
                    method();
                }, delay);
            }

            document.querySelector('.interface-interface-skeleton__content').addEventListener('scroll', function (e)
            {
                var scrollpos = $(this).scrollTop();

                debounce(function ()
                {
                    if (scrollpos > 110)
                    {
                        builder.addClass('avia-sticky-fixed-controls');
                    } else
                    {
                        builder.removeClass('avia-sticky-fixed-controls');
                    }
                });
            });
        },

		alb_edit_permalink: function(e)
		{
			e.preventDefault();

			this.body_container.addClass( 'avia-edit-permalink' );
			this.get_layout_switch_button().trigger( 'click' );
		},

		alb_sync_permalink: function()
		{
					//	Update the pretty permalink below post title
			var permalink = wp.data.select( 'core/editor' ).getPermalink();
			var post = wp.data.select( 'core/editor' ).getCurrentPost();
			var safelink = post.link;
			safelink += ( safelink.indexOf('?') == -1 ) ? '?preview=true' : '&preview=true';
			var id = 'wp-preview-' + post.id;

			var link = this.permalink_container.find( '#sample-permalink > a' ).first();
			if( link.length > 0 )
			{
				link.attr( 'href', safelink).attr( 'target', id );
				link.html( permalink );
			}
			else		//	new posts have no permalink
			{
				var html =		'<strong>Permalink:</strong>';
				html +=			'<span id="sample-permalink">';
				html +=				'<a href="' + safelink + '" target="' + id + '">' + permalink + '</a>';
				html +=			'</span>';
				html +=			'<span id="av-edit-alb-permalink-buttons">';
				html +=				'<button type="button" class="av-edit-alb-permalink button button-small hide-if-no-js" aria-label="Edit permalink">Edit</button>';
				html +=			'</span>';

				this.permalink_container.html( html );
				this.attach_edit_permalink_event_handler();
				this.attach_alb_permalink_href_handler();
			}
		},

		alb_permalink_href_handler: function( target )
		{
			this.alb_sync_permalink();
		},

		metabox_toggle_fix: function( e )
		{
			/**
			 * Fixes a problem, that WP attaches event handler twice. Closes and opens the metabox again.
			 *
			 * See wp-admin\js\postbox.js
			 */
			var btn = $( this ),
				p = btn.closest( '.postbox' ),
				ariaExpandedValue;

			p.toggleClass( 'closed' );
			ariaExpandedValue = ! p.hasClass( 'closed' );

			btn.attr( 'aria-expanded', ariaExpandedValue );
		},

		/**
		 * Callback when layout mode change is finished or when we need to sync buttons e.g. after saving a new post
		 *
		 * @param {event} e				standard ALB switch editor button click
		 * @param (jQuery) button
		 * @param {string} state		'active' | ''
		 */
		layout_mode_changed: function(e, button, state )
		{
			e.preventDefault();

			var btn = this.get_layout_switch_button();
			if( false !== btn )
			{
				btn.text( button.text );
			}

			this.gutenberg_switch_button_source.text( button.text );
		},

		alb_secureContent_changed: function( context, recursiveCall )
		{
			/**
			 * Added as tabsection and slideshow section break backend due to recursive calls processing inner content
			 * @since 5.0
			 */
			if( recursiveCall )
			{
				return;
			}

			var content = this.aviaBuilder.secureContent.val();

			//	Fix WP 5.5: if empty content and title is edited in chrome title field looses focus
			if( ! ( typeof context == 'string' && context == 'title' && content.trim() == '' ) )
			{
				this.set_gutenberg_post_content( content );
			}

			//	Blockeditor sometimes switches to block tab - reset
			this.select_document_tab();
		},

		gutenberg_title_keyup: function(e)
		{
			var title = this.get_gutenberg_title();
			this.set_alb_title( title );
		},

		alb_title_key_up: function( e )
		{
			var element = e.target;

			var title = $(element).val();
			this.set_gutenberg_title( title );
			this.alb_secureContent_changed( 'title' );
		},

		set_gutenberg_post_content: function( content )
		{
			content = content.trim();

			var editor = wp.data.dispatch( 'core/block-editor' );
			var blocks = wp.data.select( 'core/block-editor' ).getBlocks();
			var shortcode = null;

			if( blocks.length > 0 )
			{
				$.each( blocks, function( index, block ){
							if( ( block.name == 'core/shortcode' ) && ( content.length > 0 ) )
							{
								if( shortcode == null )
								{
									shortcode = block;
									return;
								}
							}

							editor.removeBlock( block.clientId );
					});
			}

			if( shortcode == null )
			{
//				var new_block = wp.blocks.createBlock( 'core/freeform', { content: content } );
//				var sav = editor.savePost();

				var new_block = wp.blocks.createBlock( 'core/shortcode', { text: content } );
				editor.insertBlocks( new_block );
			}
			else
			{
				editor.updateBlockAttributes( shortcode.clientId, { text: content } );
			}
		},

		set_alb_secure_content: function()
		{
			var alb = this.aviaBuilder.secureContent.val();

			this.aviaBuilder.canvas.children().remove();

			/**
			 * If we have already an existing ALB content we ignore any modifications from user via Gutenberg
			 */
			if( alb.trim() != '' )
			{
				return true;
			}

			var blocks = wp.data.select( 'core/block-editor' ).getBlocks();
			var content = [];
			var skipped_blocks = 0;

			$.each( blocks, function( index, block ){

								var new_c = '';
								if( 'undefined' != typeof block.attributes.content )
								{
									new_c = block.attributes.content;
								}
								else if( 'undefined' != typeof block.attributes.text )
								{
									new_c = block.attributes.text;
								}
								else
								{
									skipped_blocks++;
								}

								if( new_c != '' )
								{
									content.push( new_c );
								}
						});

			if( ( skipped_blocks == 0 ) && ( content.length == 0 ) )
			{
				return true;
			}

			if( skipped_blocks > 0 )
			{
				var confirm = window.confirm( avia_gutenberg_i18.switch_block_msg );
				if( ! confirm )
				{
					return false;
				}
			}

			var text = content.join( '\n\n' );

			var editor = this.aviaBuilder.tiny_active ? window.tinyMCE.get('content') : false;
			if(editor)
			{
				var text_html = content.join( '<br /><br />' );
				editor.setContent(text_html, {format:'html'});
			}

			this.aviaBuilder.secureContent.val(text);
			this.aviaBuilder.classic_textarea.val(text);

			return true;
		},

		monitor_save_infos: function()
		{
			if( null == this.auto_save_observer )
			{
				var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
				var container = this.get_gutenberg_header_settings_container()[0];

				this.auto_save_observer = new MutationObserver( this.check_for_is_saving.bind( this ) );
				this.auto_save_observer.observe(container, { childList: true, subtree: true } );
			}
		},

		check_for_is_saving: function( mutations )
		{
			var self = this;

			$.each( mutations, function(key, mutation) {

						if( 'undefined' == typeof( mutation.type ) )
						{
							return;
						}

						if( ('childList' != mutation.type ) && ('subtree' != mutation.type ) )
						{
							return;
						}

						for( var i = 0; i < mutation.addedNodes.length; i++)
						{
							var node = $(mutation.addedNodes[i]);

							if( node.hasClass( 'editor-post-saved-state') && node.hasClass( 'is-saving') )
							{
								/**
								 * For portfolio we have to trigger tinyMCE switch to copy content to textarea
								 */
								if( self.body_container.hasClass('post-type-portfolio') )
								{
									var editor_container = self.body_container.find('#wp-_preview_text-wrap').first();
									if( ( editor_container.length > 0 ) && editor_container.hasClass('tmce-active') )
									{
										var textarea = editor_container.find('#_preview_text');
										var the_value = textarea.val();
										if( window.tinyMCE.get('_preview_text') )
										{
											/*fixes the problem with galleries and more tag that got an image representation of the shortcode*/
											the_value = window.tinyMCE.get('_preview_text').getContent();
										}
										textarea.val( the_value );		//	= defaault behaviour of tinyMCE
									}
								}
								/**
								 * Fix: Block editor does not save the metaboxes on autosave (WP 5.0-beta-3)
								 */
								if( node.hasClass( 'is-autosaving' ) )
								{
									self.alb_perform_autosave( self );
								}
							}
						}
					});
		},

		alb_perform_autosave: function( self )
		{
			/**
			 * Blockeditor makes an autosave approx. all 10 seconds - we reduce this to minimum_no_autosave seconds for our data
			 *
			 */
			if( 0 != this.last_autosave )
			{
				if( this.last_autosave + 1000 * this.minimum_no_autosave  > Date.now() )
				{
					return;
				}
			}
			this.last_autosave = Date.now();

			var post = wp.data.select( 'core/editor' ).getCurrentPost();

			var senddata = {
						action:		'avia_gutenberg_autosave_metaboxes',
						post_id:	post.id
					};

			var fields  = $('form.metabox-location-normal, form.metabox-location-side').serializeArray();

			$.each( fields, function( i, field ) {
								senddata[field.name] = field.value;
						});

			$.ajax({
					type: "POST",
					url: ajaxurl,
					dataType: 'json',
					cache: false,
					data:	senddata,
							success	: function(response, textStatus, jqXHR) {

										if( 'undefined' != typeof response.avia_gutenberg_nonce )
										{
											self.body_container.find('#avia_builder input[name="avia_gutenberg_nonce"]').val( response.avia_gutenberg_nonce );
										}

										if( ! response.success && 'undefined' != typeof response.expired_nonce )
										{
											self.alb_perform_autosave( self );
										}
									},
							error: function(errorObj) {
	//								console.log( 'alb_perform_autosave error: ', errorObj );
								},
							complete: function(test) {
								}

				});
		}
	};

	$( function ()
	{
		/**
		 * Only load this class on a Gutenberg Page
		 */
		if( $( 'body' ).hasClass( 'block-editor-page' ) )
    	{
			$.avia_builder_gutenberg = new $.AviaBuilderGutenberg();
		}
	});

}) (jQuery);
