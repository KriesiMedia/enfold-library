
(function($)
{
	"use strict";
	
	$.AviaTabSection = function () 
	{
		//html container
		this.body_container		= $('body:eq(0)');
		
		// the canvas we use to display the interface
        this.canvas				= $('#aviaLayoutBuilder');
        
        this.builder 			= $.avia_builder;
        
        //activate the plugin
        this.set_up();
    };
	
	$.AviaTabSection.prototype = {
		
		/**
		* Sets up the whole interface
		*
		*/
		set_up: function()
		{
			this.canvas.on('click', '.avia-add-tab', $.proxy( this.add_tab, this) );
			this.canvas.on('click', '.av-admin-section-tab', $.proxy( this.change_active_tab, this) );
			this.canvas.on('click', '.avia-tab-delete', $.proxy( this.delete_current_tab, this) );
			this.canvas.on('click', '.avia-tab-clone', $.proxy( this.clone_tab, this) );
			
			this.body_container.on('av-element-to-editor' , $.proxy( this.activate_sorting, this) );
			this.body_container.on('av-builder-new-element-added' , $.proxy( this.activate_sorting, this) );
			
		},
		
		add_tab: function(e)
		{
			e.preventDefault();
			
			var clicked 	= $(e.currentTarget),
				parent 		= clicked.parents('.avia_tab_section:eq(0)'),
				innerSc		= parent.find('>.avia_inner_shortcode:eq(0)'),
				tab_header	= parent.find('.avia_tab_section_titles .av-admin-section-tab:last'),
				new_tab		= parent.find('.av-admin-section-tab:eq(0)').clone().removeClass('av-admin-section-tab-active'),
				el_tmpl		= $('#avia-tmpl-avia_sc_tab_sub_section').html().replace( 'av-admin-section-tab-content-active', '' );
				
				new_tab.find('.av-tab-custom-title').text("");
				new_tab.insertAfter(tab_header);
				innerSc.append(el_tmpl);
				
				this.resort_tab_ids( parent );
				
				this.builder.activate_element_dropping();
		        this.builder.updateInnerTextarea(false, parent); 
		        this.builder.updateTextarea();
		        this.builder.do_history_snapshot(0);
				
		},
		
		change_active_tab: function(e)
		{	
			e.preventDefault();
			
			var clicked 	= $(e.currentTarget),
				parent		= clicked.parents('.avia_tab_section:eq(0)'),
				new_open	= clicked.data('av-tab-section-title'),
				tab_content	= parent.find("[data-av-tab-section-content='"+new_open+"']"),
				already_open= tab_content.is('.av-admin-section-tab-content-active'),
				storage 	= parent.find('>.avia_inner_shortcode>'+ this.builder.datastorage + ':eq(0)'),
				key 		= 'av_admin_tab_active',
				shortcode	= storage.val();
				
			
			if(!already_open)
			{	
				var new_shortcode 	= this.builder.set_shortcode_single_value(shortcode, key, new_open, storage);
					
				storage.val(new_shortcode);
				this.builder.updateTextarea();
				
					
				parent.find('.av-admin-section-tab-active, .av-admin-section-tab-content-active').removeClass('av-admin-section-tab-active');
				parent.find('.av-admin-section-tab-content-active').removeClass('av-admin-section-tab-content-active');
				
				clicked.addClass('av-admin-section-tab-active');
				tab_content.addClass('av-admin-section-tab-content-active');	
			}
			else //open the edit settings for the tab if its already open
			{
				if(!$(e.target).is('.ui-sortable-handle'))
				{
					tab_content.find('> .avia_sorthandle .avia-edit-element').trigger('click');
				}
			}
		},
		
		delete_current_tab: function(e)
		{
			e.preventDefault();
			
			//check if its the only tab
			var clicked 	= $(e.currentTarget),
				parent		= clicked.parents('.avia_tab_section:eq(0)'),
				tab_content	= clicked.parents('.avia_layout_tab:eq(0)'),
				tabs		= parent.find('.av-admin-section-tab'),
				to_delete	= tab_content.data('av-tab-section-content'),
				tab_remove	= parent.find("[data-av-tab-section-title='"+to_delete+"']"),
				next_open	= tab_remove.next('.av-admin-section-tab').length >= 1 ? tab_remove.next('.av-admin-section-tab') : tab_remove.prev('.av-admin-section-tab');
			
			if(tabs.length >= 2) //delete only if we got more than 1 tab
			{
				tab_content.addClass('av-removed-next').css('display','none');
				tab_remove.remove(); //remove the tab
				this.builder.shortcodes.deleteItem(e.currentTarget, this.builder, 0); //remove the content
				
				this.resort_tab_ids( parent );
				
				next_open.trigger('click'); //click for setting the correct open tab
			}
		},	
		
		clone_tab: function(e)
		{
			e.preventDefault();
			
			var clicked 	= $(e.currentTarget),
				parent 		= clicked.parents('.avia_tab_section:eq(0)'),
				tab_content	= clicked.parents('.avia_layout_tab:eq(0)'),
				tab_active	= parent.find('.av-admin-section-tab-active'),
				new_tab		= tab_active.clone().removeClass('av-admin-section-tab-active'),
				el_tmpl		= tab_content.clone().removeClass('av-admin-section-tab-content-active');
				
				el_tmpl.find('.ui-draggable').removeClass('ui-draggable');
				el_tmpl.find('.ui-droppable').removeClass('ui-droppable');
				
				
				new_tab.insertAfter(tab_active);
				el_tmpl.insertAfter(tab_content);
				
				this.resort_tab_ids( parent );
				
				this.builder.activate_element_dropping();
				this.builder.activate_element_dragging();
		        this.builder.updateInnerTextarea(false, parent); 
		        this.builder.updateTextarea();
		        this.builder.do_history_snapshot(0);
		        
		        new_tab.trigger('click');
		},	
		
		activate_sorting: function(e, element)
		{
			var 	_self 	= this,
					params	= {
					handle: '.av-admin-section-tab-move-handle',
					items: '.av-admin-section-tab',
					placeholder: "av-admin-section-tab-placeholder",
					tolerance: "pointer",
					scroll: false,
					forcePlaceholderSize:true,
					start: function( event, ui ) 
					{
					},
					update: function(event, ui) 
					{
						var parent 		= ui.item.parents('.avia_tab_section:eq(0)'),
							titles 		= ui.item.parents('.avia_tab_section_titles:eq(0)'),
							sortables 	= titles.find('.av-admin-section-tab'),
							old_index	= ui.item.data('av-tab-section-title'),
							new_index	= sortables.index(ui.item),
							modifier	= old_index > new_index ? 1 : 0,
							tab_content = parent.find("[data-av-tab-section-content='"+old_index+"']"),
							storage 	= parent.find('>.avia_inner_shortcode>'+ _self.builder.datastorage + ':eq(0)');
							
							if(new_index === 0)
							{
								tab_content.insertAfter(storage);
							}
							else
							{
								var appendTo = parent.find(".avia_layout_tab:eq(" + (new_index - modifier )+ ")");
								tab_content.insertAfter(appendTo);
							}
							
							_self.resort_tab_ids( parent );
							
							_self.builder.updateInnerTextarea(false, parent); 
							_self.builder.updateTextarea();
							_self.builder.do_history_snapshot(0);
							
					},
					stop: function( event, ui ) 
					{
						//obj.canvas.removeClass('avia-start-sorting');
					}
				};
			
			//if an element was passed remove all previous classes since it migh be a clone
			if(element) $(element).find('.av-tab-section-sortable').removeClass('av-tab-section-sortable');
			
			
			$('.avia_tab_section_titles:not(.av-tab-section-sortable)').each(function()
			{
				$(this).addClass('av-tab-section-sortable').sortable(params);
			});
		},
		
		resort_tab_ids: function( tab_section )
		{
			var tabs 	= tab_section.find('.av-admin-section-tab'),
				content	= tab_section.find('.avia_layout_tab:not(.av-removed-next)');
				
				
			tabs.each(function(i)
			{
				var index	= i +1, current = $(this);
				current.find('.av-tab-nr').text(index);
				current.attr('data-av-tab-section-title', index);
				current.data('av-tab-section-title', index);
			});
			
			content.each(function(i)
			{
				var index	= i +1, current = $(this);
				current.attr('data-av-tab-section-content', index);
				current.data('av-tab-section-content', index);
			});	
				
		}
	};
	
	
	$(document).ready(function () 
	{
    	$.avia_tab_section = new $.AviaTabSection();
	});
	
		
})(jQuery);	 
