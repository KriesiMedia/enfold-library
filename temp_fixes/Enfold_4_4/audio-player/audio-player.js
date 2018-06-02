// -------------------------------------------------------------------------------------------
// 
// AVIA Player
// 
// -------------------------------------------------------------------------------------------
(function($)
{ 
	"use strict";
	
	var autostarted = false,
		container = null,
	
		monitorStart = function( container )
			{
				var play_pause	= container.find('.av-player-player-container .mejs-playpause-button');
				
				if( play_pause.length == 0 )
				{
					setTimeout( function(){
							monitorStart( container );
						}, 200 );
				}
				
				if( ! play_pause.hasClass('mejs-pause') )
				{
					play_pause.trigger( 'click' );
				}
				
			};
	
	$.fn.aviaPlayer = function( options )
	{	
		if( ! this.length ) return; 

		return this.each(function()
		{
			var _self 			= {};
			
			_self.container		= $( this );
			_self.stopLoop		= false;
			
			_self.container.find('audio').on('play', function() {
									if( _self.stopLoop )
									{
										this.pause();
										_self.stopLoop = false;
									}

							});
			
			if( _self.container.hasClass( 'avia-playlist-no-loop' ) )
			{
				_self.container.find('audio').on('ended', function() {
						//	find the last track in the playlist so that when the last track ends we can pause the audio object
						var lastTrack	= _self.container.find('.wp-playlist-tracks .wp-playlist-item:last a');

						if ( this.currentSrc === lastTrack.attr('href') ) {
							_self.stopLoop = true;
						}

					});
				}
			
			/**
			 * Limit autostart to the first player with this option set only
			 * 
			 * DOM is not loaded completely and we have no event when player is loaded.
			 * We check for play button and perform a click
			 */
			if( _self.container.hasClass( 'avia-playlist-autoplay' ) && ! autostarted )
			{
				if( ( _self.container.css('display') == 'none') || ( _self.container.css("visibility") == "hidden" ) )
				{
					return;
				}
				
				autostarted = true;
				setTimeout( function(){
							monitorStart( _self.container, _self );
						}, 200 );
			}
			
		});
	};
}(jQuery));