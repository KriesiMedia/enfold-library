
(function($)
{
	"use strict";

	if(typeof avia_globals == "undefined") return;

	var av_key = "avia_builder_button";	// $this->button['id']
	
    tinymce.create('tinymce.plugins.'+av_key, 													
    {  
    	//init: register the modal open function to the tinymce editor
        init : function(editor, url) 
        {  
        	var _self = this;
        
        	editor.addButton( av_key, {
        		type: 'menubutton',
        		text: "",
                title : avia_globals.sc[av_key].title,
                image : avia_globals.sc[av_key].image,
                icons : av_key,
            	menu: _self.createMenuValues(editor)
           });
        
        
			editor.addCommand("openAviaModal", function (ui, params) 
			{
				var modal = new $.AviaModal(params);
				return false;
			});
        },  
        
        
        createMenuValues: function(editor)
        {
			var _self			= this,
				final_options	= [],
				open_modal  	= $.AviaModal.openInstance || [],
				shortcode_array = avia_globals.sc[av_key].config,
				submenu 		= [],
				sub_count 		= 0;
			
			//dont create tab submenus if modal window is open. only for elements that dont have any tabs (inline elements) and elements that have the tinymce.tiny_always flag
            		
        	//get all tabs
        	for(var i in shortcode_array)
        	{
        		if(open_modal.length == 0 || ( typeof shortcode_array[i].tinyMCE != 'undefined' && typeof shortcode_array[i].tinyMCE.tiny_always != 'undefined'))
				{
        			submenu[shortcode_array[i].tab] = [];
        		}
        	}
        	
			//create sub menus
        	for(var title in submenu)
        	{
        		if(title != 'undefined')
        		{
        			sub_count++;
        			final_options.push({text: title, menu: []});
        		}
        	}
        	
        	//add items to sub menus. based on the config tinymce array add an instant insert or modal popup button
        	for(var z in shortcode_array)
        	{
        		//set a default
            	shortcode_array[z].tinyMCE = shortcode_array[z].tinyMCE || {};
        	
        		//only render subset of elements if modal window is open
        		if(open_modal.length == 0 || !shortcode_array[z].tab || typeof shortcode_array[z].tinyMCE.tiny_always != 'undefined')
        		{	
        			var current_menu = final_options,
        				paramText	 = "",
        				paramOnclick = "";
        		
        			for(var title in final_options)
		        	{
		        		if(title != 'undefined')
		        		{
		        			if(final_options[title].text == shortcode_array[z].tab)
		        			{
		        				current_menu = final_options[title].menu;
		        			}
		        		}
		        	}
        			
        			paramText 		= shortcode_array[z].tinyMCE.name || shortcode_array[z].name;
        			paramOnclick 	= (typeof shortcode_array[z].tinyMCE.instantInsert != 'undefined') ? _self.instantInsert : _self.modalInsert;
        			
        			
            		current_menu.push({text: paramText, onclick: paramOnclick, av_shortcode: shortcode_array[z], scope: editor });
        		}
        	}
        	
        	//remove empty sub menus
        	for(var title in final_options)
        	{
        		if(title != 'undefined' && typeof final_options[title].menu != "undefined")
        		{
        			if(final_options[title].menu.length === 0) delete(final_options[title]);
        		}
        	}

			return final_options;
        },
        
        instantInsert: function () 
        {
        	var shortcode = this.settings.av_shortcode;
			
            this.settings.scope.execCommand("mceInsertContent", false, window.switchEditors.wpautop(shortcode.tinyMCE.instantInsert))
        },
        
        modalInsert: function (menu, shortcode) 
        {
        	var shortcode = this.settings.av_shortcode,
        		modalData = $.extend({}, {modal_class:'', before_save:'' }, shortcode.modal_data);
        		
			if(typeof shortcode.modal_on_load == "object") {
				shortcode.modal_on_load = $.map(shortcode.modal_on_load, function(modal){
					return modal;
				});
			}
		
        	if(typeof shortcode.modal_on_load != "undefined" && typeof shortcode.modal_on_load != "string") shortcode.modal_on_load = shortcode.modal_on_load.join(', ');
        	
			var scope = this.settings.scope;
			
            tinyMCE.activeEditor.execCommand("openAviaModal", false, 
            {
				modal_class: 		modalData.modal_class,
				before_save:  		modalData.before_save,
				shortcodehandler: 	shortcode.shortcode, 
				modal_title: 		shortcode.name, 
				modal_ajax_hook: 	shortcode.shortcode ,
                scope: 				tinyMCE.activeEditor,
				ajax_param: 		{extract: true, shortcode: "", _ajax_nonce: $('#avia-loader-nonce').val()},
                on_load: 			shortcode.modal_on_load,
				on_save: 			function(values)
				{	
					if(typeof values != "string")
					{
						//cleanup values: remove aviaTB addtion to the arguments, cleanup fake args and prepare object for insertion by creating the shortcode string
						var new_key, old_val;
						for (var el_key in values)
						{
							if (values.hasOwnProperty(el_key)) 
							{
								if(el_key.indexOf('_fakeArg') !== -1)
								{
									delete values[el_key];
								}
								else
								{
									new_key = el_key.replace(/aviaTB/g,"");
									if(new_key != el_key)
									{
										old_val = typeof values[new_key] !== "undefined" ? values[new_key] + "," : "";
										values[new_key] = old_val ? old_val + values[el_key] : values[el_key];
										delete values[el_key];
									}
								}
							}
						}
						
						//if a specific template was passed return that one, otherwise use the default shortcode builder
						if(shortcode.tinyMCE.templateInsert)
						{
							var newInsert = shortcode.tinyMCE.templateInsert;
						
							for (var el_key in values)
							{
								newInsert = newInsert.replace("{{"+el_key+"}}", values[el_key]);
							}
							
							values = newInsert;
						}
						else
						{
							values = window.switchEditors.wpautop( $.avia_builder.createShortcode( values, shortcode.shortcode ) );
						}
					}
	
					scope.execCommand("mceInsertContent", false, values);
				}
            });
               
        }
        
    });  
    
    tinymce.PluginManager.add(av_key, tinymce.plugins[av_key]);
    
})(jQuery);	 

