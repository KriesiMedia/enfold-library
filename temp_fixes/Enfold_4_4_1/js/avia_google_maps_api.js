// -------------------------------------------------------------------------------------------
// 
// AVIA GOOGLE MAPS API - loads the google maps api asynchronously 
// 
// afterwards applies the map to the container
// 
// -------------------------------------------------------------------------------------------


(function($)
{
    "use strict";

	$.AviaMapsAPI  =  function(options, container)
	{
		if(typeof window.av_google_map == 'undefined')
		{
			$.avia_utilities.log('Map creation stopped, var av_google_map not found'); 
			return;
		}
	
		// gather container and map data
		this.container	= container;
		this.$container	= $( container );
		this.$body  	= $('body');
		this.$mapid		= this.$container.data('mapid'); 
		this.$data		= window.av_google_map[this.$mapid];
		this.retina 	= window.devicePixelRatio > 1;
		
		// set up the whole api object
		this._init( options );
	};
	
	$.AviaMapsAPI.apiFiles = 
	{
		loading: false, 
		finished: false, 
		src: ''
	};
	
  	$.AviaMapsAPI.prototype =
    {
    	_init: function()
    	{
			if( 'undefined' == typeof avia_framework_globals.gmap_maps_loaded || avia_framework_globals.gmap_maps_loaded == '' )
			{
						//	this is only a fallback setting 
				$.AviaMapsAPI.apiFiles.src = 'https://maps.googleapis.com/maps/api/js?v=3.30&callback=aviaOnGoogleMapsLoaded';
				if( typeof avia_framework_globals.gmap_api != 'undefined' && avia_framework_globals.gmap_api != "" )
				{
					$.AviaMapsAPI.apiFiles.src += "&key=" + avia_framework_globals.gmap_api;
				}
			}
			else
			{
				$.AviaMapsAPI.apiFiles.src = avia_framework_globals.gmap_maps_loaded;
			}
			
    		this._bind_execution();
    		this._getAPI();
    	},
    	
    	_getAPI: function( )
		{	
			//make sure the api file is loaded only once
			if((typeof window.google == 'undefined' || typeof window.google.maps == 'undefined') && $.AviaMapsAPI.apiFiles.loading == false)
			{	
				$.AviaMapsAPI.apiFiles.loading = true;
				var script 	= document.createElement('script');
					script.id = 'av-google-maps-api';
					script.type = 'text/javascript';	
					script.src 	= $.AviaMapsAPI.apiFiles.src;

      			document.body.appendChild(script);
			}
			else if((typeof window.google != 'undefined' && typeof window.google.maps != 'undefined') || $.AviaMapsAPI.apiFiles.loading == false)
			//else if($.AviaMapsAPI.apiFiles.finished === true)
			{
				this._applyMap();
			}
		},
		
		_bind_execution: function()
		{
			this.$body.on('av-google-maps-api-loaded', $.proxy( this._applyMap, this) );
		},
		
		_applyMap: function()
		{
			if(typeof this.map != 'undefined') return;
			if(!this.$data.marker || !this.$data.marker[0] || !this.$data.marker[0].lat || !this.$data.marker[0].long)
			{
				$.avia_utilities.log('Latitude or Longitude missing', 'map-error'); 
				return;
			}
			
			var _self = this,
				mobile_drag = $.avia_utilities.isMobile ? this.$data.mobile_drag_control : true,
				zoomValue 	= this.$data.zoom == "auto" ? 10 : this.$data.zoom;
		
			var mapTypeControl = false;
			var mapTypeId = google.maps.MapTypeId.ROADMAP;
			var mapTypeControlOptions = google.maps.MapTypeControlStyle.DROPDOWN_MENU;
			
			switch( this.$data.maptype_control )
			{
				case 'dropdown':
					mapTypeControl = true;
					mapTypeControlOptions = google.maps.MapTypeControlStyle.DROPDOWN_MENU;
					break;
				case 'horizontal':
					mapTypeControl = true;
					mapTypeControlOptions = google.maps.MapTypeControlStyle.HORIZONTAL_BAR;
					break;
				case 'default':
					mapTypeControl = true;
					mapTypeControlOptions = google.maps.MapTypeControlStyle.DEFAULT;
					break;
				default:
					mapTypeControl = false;
					mapTypeControlOptions = google.maps.MapTypeControlStyle.DROPDOWN_MENU;
					break;
			}
			
			switch( this.$data.maptype_id )
			{
				case 'SATELLITE':
					mapTypeId = google.maps.MapTypeId.SATELLITE;
					break;
				case 'HYBRID':
					mapTypeId = google.maps.MapTypeId.HYBRID;
					break;
				case 'TERRAIN':
					mapTypeId = google.maps.MapTypeId.TERRAIN;
					break;
				default:
					mapTypeId = google.maps.MapTypeId.ROADMAP;
			}
			
			if( 'undefined' == typeof this.$data.scrollwheel ) {this.$data.scrollwheel = false; }
			if( 'undefined' == typeof this.$data.gestureHandling ) {this.$data.gestureHandling = 'cooperative' };
			if( 'undefined' == typeof this.$data.backgroundColor ) {this.$data.backgroundColor = 'transparent' };
			if( 'undefined' == typeof this.$data.styles ) {this.$data.styles = [{featureType: "poi", elementType: "labels", stylers: [ { visibility: "off" }] }] };
			
			this.mapVars = {
				mapMaker: false, //mapmaker tiles are user generated content maps. might hold more info but also be inaccurate
				backgroundColor: this.$data.backgroundColor,
				streetViewControl: this.$data.streetview_control,
				zoomControl: this.$data.zoom_control,
				//draggable: mobile_drag,
				gestureHandling: this.$data.gestureHandling,
				scrollwheel: this.$data.scrollwheel,
				zoom: zoomValue,
				mapTypeControl: mapTypeControl,
				mapTypeControlOptions: {style:mapTypeControlOptions},
				mapTypeId: mapTypeId,
				center: new google.maps.LatLng(this.$data.marker[0].lat, this.$data.marker[0].long),
				styles: this.$data.styles
			};

			this.map = new google.maps.Map(this.container, this.mapVars);
			this.$container.removeClass('av_gmaps_show_delayed av_gmaps_show_unconditionally');
			this.$container.addClass('av_gmaps_map_attached');
		
			this._applyMapStyle();
			
			if(this.$data.zoom == "auto")
			{
				this._setAutoZoom();
			}
			
			google.maps.event.addListenerOnce(this.map, 'tilesloaded', function() {	
				_self._addMarkers();
			});
			
			//	must triggr 'resize' because if more then 1 map on page only the first is shown after confirm
			var new_map = this.map;
			setTimeout( function(){
							google.maps.event.trigger(new_map, 'resize');
						}, 100 );
		},
		
		_setAutoZoom: function()
		{
			var bounds = new google.maps.LatLngBounds();
			
			for (var key in this.$data.marker) 
			{
				bounds.extend( new google.maps.LatLng (this.$data.marker[key].lat , this.$data.marker[key].long) );
			}
			
			this.map.fitBounds(bounds);
		},
		
		_applyMapStyle: function()
		{
			var stylers = [], style = [], mapType, style_color = "";
			
			if(this.$data.hue != "") stylers.push({hue: this.$data.hue});
			if(this.$data.saturation != "") stylers.push({saturation: this.$data.saturation});
			
			if(stylers.length)
			{
				style = [{
					      featureType: "all",
					      elementType: "all",
					      stylers: stylers
					    }, {
					      featureType: "poi",
					      stylers: [
						  	{ visibility: "off" }
					      ]
					    }];
					    
				
				if(this.$data.saturation == "fill")
				{
					   
					style_color = this.$data.hue || "#242424";
					
					var c = style_color.substring(1);      // strip #
					var rgb = parseInt(c, 16);   // convert rrggbb to decimal
					var r = (rgb >> 16) & 0xff;  // extract red
					var g = (rgb >>  8) & 0xff;  // extract green
					var b = (rgb >>  0) & 0xff;  // extract blue
					
					var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b; // per ITU-R BT.709
					var lightness = 1;
					var street_light = 2;
					
					if (luma > 60) {
					    lightness = -1;
					    street_light = 3;
					}
					if (luma > 220) {
					    lightness = -2;
					    street_light = -2;
					}
					
				style = [
{"featureType":"all","elementType":"all","stylers":[{"color":style_color},{"lightness":0}]},
{"featureType":"all","elementType":"labels.text.fill","stylers":[{"color":style_color},{"lightness":(25 * street_light)}]},
{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":style_color},{"lightness":3}]},
{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},
{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":style_color},{"lightness":30}]},
{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":style_color},{"lightness":30},{"weight":1.2}]},
{"featureType":"landscape","elementType":"geometry","stylers":[{visibility: 'simplified'},{"color":style_color},{"lightness":3}]},
{"featureType":"poi","elementType":"geometry","stylers":[{ "visibility": "off" }]},
{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":style_color},{"lightness":-3}]},
{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":style_color},{"lightness":2},{"weight":0.2}]},
{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":style_color},{"lightness":-3}]},
{"featureType":"road.local","elementType":"geometry","stylers":[{"color":style_color},{"lightness":-3}]},
{"featureType":"transit","elementType":"geometry","stylers":[{"color":style_color},{"lightness":-3}]},
{"featureType":"water","elementType":"geometry","stylers":[{"color":style_color},{"lightness":-20}]}
						];
				}	
				
				mapType = new google.maps.StyledMapType(style, { name:"av_map_style" });
				this.map.mapTypes.set('av_styled_map', mapType);
				this.map.setMapTypeId('av_styled_map');
			}
		},
		
		_addMarkers: function()
		{
			for (var key in this.$data.marker) 
			{	
				var _self = this;
				
				(function(key, _self) 
				{
					setTimeout(function()
					{
							var marker = "";
							
							if(!_self.$data.marker[key] || !_self.$data.marker[key].lat || !_self.$data.marker[key].long)
							{
								$.avia_utilities.log('Latitude or Longitude for single marker missing', 'map-error'); 
								return;
							}
							
							_self.$data.LatLng = new google.maps.LatLng(_self.$data.marker[key].lat, _self.$data.marker[key].long);
							
							var markerArgs = {
			        		  flat: false,
						      position: _self.$data.LatLng,
						      animation: google.maps.Animation.BOUNCE,
						      map: _self.map,
						      title: _self.$data.marker[key].address,
						      optimized: false
						    };
						    
						    //set a custom marker image if available. also set the size and reduce the marker on retina size so its sharp
						    if(_self.$data.marker[key].icon && _self.$data.marker[key].imagesize)
						    { 
						    	var size = _self.$data.marker[key].imagesize, half = "", full = "";
						    	
						    	if(_self.retina && size > 40) size = 40;			//retina downsize to at least half the px size
						    	half = new google.maps.Point(size / 2, size ) ; 	//used to position the marker
						    	full = new google.maps.Size(size , size ) ; 		//marker size
						    	markerArgs.icon = new google.maps.MarkerImage(_self.$data.marker[key].icon, null, null, half, full);
						    }
							
			        		marker = new google.maps.Marker(markerArgs);
			        		
			        		setTimeout(function(){ marker.setAnimation(null); _self._infoWindow(_self.map, marker, _self.$data.marker[key]); },500);
			        		
		        	},200 * (parseInt(key,10) + 1));
		        		
		        }(key, _self));
    		}
		},
		
		_infoWindow: function(map, marker, data)
		{
			var info = $.trim(data.content);
			
			if(info != "")
			{
				var infowindow = new google.maps.InfoWindow({
					content: info
				});
				
				google.maps.event.addListener(marker, 'click', function() {
				    infowindow.open(map,marker);
				});
				
				if(data.tooltip_display) infowindow.open(map,marker);
			}
		}
		
    	
    };

    //simple wrapper to call the api. makes sure that the api data is not applied twice
    $.fn.aviaMaps = function( options )
    {
    	return this.each(function()
    	{	
    		var self = $.data( this, 'aviaMapsApi' );
    		
    		if(!self)
    		{
    			self = $.data( this, 'aviaMapsApi', new $.AviaMapsAPI( options, this ) );
    		}
    	});
    };
    
    
    
    //this function is executed once the api file is loaded
	window.aviaOnGoogleMapsLoaded = function(){ 
							$('body').trigger('av-google-maps-api-loaded'); 
							$.AviaMapsAPI.apiFiles.finished = true; 
						};

    
	$('body').trigger('avia-google-maps-api-script-loaded');
    
})( jQuery );


