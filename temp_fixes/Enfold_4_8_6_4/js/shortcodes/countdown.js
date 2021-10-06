// -------------------------------------------------------------------------------------------
// 
// AVIA Countdown
// 
// @since 4.5.6  Countdown is UTC based to reflect different browser timezones
// 
// -------------------------------------------------------------------------------------------
(function($)
{ 
	"use strict";
	
	var _units	= ['years','months','weeks','days','hours','minutes','seconds'],
		_second = 1000,
		_minute = _second * 60,
		_hour 	= _minute * 60,
		_day 	= _hour * 24,
		_week	= _day * 7,
		
		getDaysInMonth = function( month, year )
		{
			//	January is 1 based
			return new Date( year, month, 0 ).getDate();
		},
		
		splitStartDate = function( date )
		{
			var result = {
						year: date.getFullYear(),
						month: date.getMonth() + 1,
						day: date.getDate(),
						hours: date.getHours(),
						minutes: date.getMinutes(),
						seconds: date.getSeconds()
					};
					
			return result;
		},
		
		getYears = function( start, endDate )
		{
			var diff = endDate.getFullYear() - start.year;
			
			if( diff > 0 )
			{
				//	take care of leap year
				var check = new Date( start.year + diff, start.month - 1, start.day, start.hours, start.minutes, start.seconds );
				if( check > endDate )
				{
					diff --;
				}
			}
			
			return ( diff >= 0 ) ? diff : 0;
		},
		
		getMonths = function( start, endDate )
		{
			var endMonth = endDate.getMonth() + 1;
			var diff = endMonth - start.month;
			if( diff < 0 )
			{
				diff = 12 - start.month + endMonth;
			}
			
			if( diff > 0 )
			{
				//	take care of leap year
				var check = new Date( start.year, start.month - 1 + diff, start.day, start.hours, start.minutes, start.seconds );
				if( check > endDate )
				{
					diff --;
				}
			}
			
			return ( diff >= 0 ) ? diff : 0;
		},
		
		getDays = function( start, endDate )
		{
			var endDay = endDate.getDate();
			var diff = endDay - start.day;
			if( diff < 0 )
			{
				diff = getDaysInMonth( start.month, start.year ) - start.day + endDay;
			}
			
			if( diff > 0 )
			{
				var check = new Date( start.year, start.month - 1, start.day + diff, start.hours, start.minutes, start.seconds );
				if( check > endDate )
				{
					diff --;
				}
			}
			
			return ( diff >= 0 ) ? diff : 0;
		},
		
		getBetween = function( startDate, endDate )
		{
			var start = splitStartDate( startDate ),
				result = {
							years: 0,
							year_months: 0,
							month_months: 0,
							days: 0
						};
				
		
			result.years = getYears( start, endDate );
			start.year += result.years;
			
			result.year_months = getMonths( start, endDate );
			start.month += result.year_months;
			
			result.days = getDays( start, endDate );
			start.day += result.days;
			
			result.month_months = result.years * 12 + result.year_months;
			
			return result;
		},
		
		ticker = function( _self )
		{
			var tmLoc = new Date(),
				_now = new Date( tmLoc.getTime() + tmLoc.getTimezoneOffset() * 60000 ),		//	get UTC time
				_timestamp = _self.end - _now;
			
			if( _timestamp <= 0 )
			{
				clearInterval( _self.countdown );
				return;
			}	
			
			_self.time.years	= 0;
			_self.time.months	= 0;
			_self.time.weeks   	= Math.floor( _timestamp / _week );
			_self.time.days 	= Math.floor( ( _timestamp % _week ) / _day );
			_self.time.hours	= Math.floor( ( _timestamp % _day ) / _hour ); 
			_self.time.minutes 	= Math.floor( ( _timestamp % _hour ) / _minute ); 
			_self.time.seconds 	= Math.floor( ( _timestamp % _minute ) / _second );
			
			var between = getBetween( _now, _self.end );
			
			switch( _self.data.maximum )
			{
				case 1: 
					_self.time.seconds = Math.floor( _timestamp / _second ); 
					break;
				case 2: 
					_self.time.minutes = Math.floor( _timestamp / _minute ); 
					break;
				case 3: 
					_self.time.hours = Math.floor( _timestamp / _hour );
					break;
				case 4: 
					_self.time.days = Math.floor( _timestamp / _day );
					break;
				case 6:
					_self.time.days = between.days;
					_self.time.months = between.month_months;
					break;
				case 7:
					_self.time.days = between.days;
					_self.time.months = between.year_months;
					_self.time.years  = between.years;
					break;
			}
			
			for( var i in _self.time )
			{	
				if( typeof _self.update[i] == "object" )
				{
					if(_self.firstrun || _self.oldtime[i] != _self.time[i])
					{
						var labelkey = ( _self.time[i] === 1 ) ? "single" : "multi"; 
					
						_self.update[i].time_container.text(_self.time[i]);
						_self.update[i].label_container.text(_self.update[i][labelkey]);
					}
				}
			}
			
			//show ticker
			if( _self.firstrun ) 
			{
				_self.container.addClass('av-countdown-active');
			}
			
			_self.oldtime = $.extend( {}, _self.time );
			_self.firstrun = false;
		};
		
	
	$.fn.aviaCountdown = function( options )
	{	
		if( ! this.length ) 
		{
			return;
		} 

		return this.each( function()
		{
			var _self = {};
			
			_self.update = {};
			_self.time = {};			
			_self.oldtime = {};			
			_self.firstrun = true;			
			_self.container = $(this);
			_self.data = _self.container.data();
			_self.end = new Date( _self.data.year, _self.data.month, _self.data.day, _self.data.hour, _self.data.minute );
			
			if( _self.data.timezone != '0' )
			{
				_self.end = new Date( _self.end.getTime() - _self.data.timezone * 60000 );
			}
			
			for( var i in _units )
			{
				_self.update[_units[i]] = {
										time_container:  _self.container.find('.av-countdown-' + _units[i] + ' .av-countdown-time'),
										label_container: _self.container.find('.av-countdown-' + _units[i] + ' .av-countdown-time-label')
									};
				
				if( _self.update[_units[i]].label_container.length )
				{
					_self.update[_units[i]].single = _self.update[_units[i]].label_container.data('label');
					_self.update[_units[i]].multi  = _self.update[_units[i]].label_container.data('label-multi');
				}
			}
			
			ticker( _self );
			_self.countdown = setInterval( function(){ ticker( _self ); }, 1000 );
		});
	};
	
}(jQuery));
