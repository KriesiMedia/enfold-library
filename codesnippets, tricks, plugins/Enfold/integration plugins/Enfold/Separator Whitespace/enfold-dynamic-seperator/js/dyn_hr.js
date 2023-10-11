/**
 * Place the following code at the bottom of enfold\js\shortcodes.js or in any other js file that is loaded in frontend
 *
 */

(function($)
{	
    "use strict";

    $(document).ready(function()
    {	
		//load hr - seperator and whitespace element
        if($.fn.aviaSeperator)
        {
        	$('.hr.avia-hr').aviaSeperator();
    	}
	});
	
}(jQuery));

// -------------------------------------------------------------------------------------------
// 
// AVIA Seperator and Whitespace Element
// 
// -------------------------------------------------------------------------------------------
(function($)
{ 
	"use strict";
	
	/**
	 * Check, if width of a custom seperator exceeds surrounding container width -> in that case reduce width to maximum container width
	 * currently assumed that data( 'av_custom_width' ) is a pixel value ( % does not make problems)
	 * 
	 * @param {object} _self
	 */
	var CalcSepWidth = function( _self )
	{
		_self.hr.hide();
		_self.icon.hide();
		
		var max_width = Math.trunc( _self.container.width() );
		
		var new_value = '';
		if( _self.standard_width <= max_width )
		{
			new_value = _self.av_custom_width;
		} 
		else if( _self.icon.length == 0 )
		{
			new_value = '100%';
		}
		else
		{
			new_value = ( Math.trunc( ( max_width - _self.icon_width ) / 2.0 ) - _self.spacing ) + 'px';
		}
		
		_self.hr.width( new_value );
		_self.hr.show();
		_self.icon.show();
	};
	
	$.fn.aviaSeperator = function( options )
	{	
		if( ! this.length ) return; 
		
		return this.each(function()
		{
			var _self				= {};
				_self.container		= $(this);
				_self.hr			= _self.container.find( '> .hr-inner' );
				_self.icon			= _self.container.find( '> .av-seperator-icon' );
				_self.av_custom_width	= _self.container.data( 'av_custom_width' );
			
			if( ! _self.container.hasClass( 'hr-custom' ) )
			{
				return;
			}
			
			if( '' == _self.av_custom_width )
			{
				return;
			}
			
			if( ( _self.hr.length == 0 ) && ( _self.icon.length == 0 ) )
			{
				return;
			}
			
			_self.spacing = Math.trunc( _self.hr.first().outerWidth( true ) - _self.hr.first().width() );
			_self.hr_width = Math.trunc( _self.hr.first().width() );
			_self.icon_width = ( _self.icon.length != 0 ) ? Math.trunc( _self.icon.outerWidth( true ) ) : 0;
			_self.standard_width = _self.icon_width + 2 * ( _self.hr_width + _self.spacing );
			
			$(window).on( 'debouncedresize', function(){
						CalcSepWidth( _self );
						
						//	if multiple tabs - force a recalc (otherwise we might have a wrong calculation)
						setTimeout( function( ){
							CalcSepWidth( _self );
							}, 700);
							
					});
					
			CalcSepWidth( _self );
			
			//	if multiple tabs - force a recalc (otherwise we might have a wrong calculation)
			setTimeout( function(){
							CalcSepWidth( _self );
							}, 700);
							
		});
	};
	
	
}(jQuery));
