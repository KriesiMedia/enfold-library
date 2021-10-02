(function($) 
{
	"use strict";

	$( function() 
	{
		$('.avia_auto_toc').each(function(){

			var $toc_section = $(this).attr('id');
			var $levels = 'h1';
			var $levelslist = new Array();
			var $excludeclass = '';

			var $toc_container = $(this).find('.avia-toc-container');

			if( $toc_container.length )
			{
				var $levels_attr = $toc_container.attr('data-level');
				var $excludeclass_attr = $toc_container.attr('data-exclude');

				if( typeof $levels_attr != 'undefined' ) 
				{
					$levels = $levels_attr;
				}

				if( typeof $excludeclass_attr != 'undefined' ) 
				{
					$excludeclass = $excludeclass_attr.trim();
				}
			}

			$levelslist = $levels.split(',');

			$('.entry-content-wrapper').find( $levels ).each( function() 
			{
				var headline = $( this );
				
				if( headline.hasClass('av-no-toc') )
				{
					return;
				}
				
				if( $excludeclass != '' && ( headline.hasClass( $excludeclass ) || headline.parent().hasClass( $excludeclass ) ) )
				{
					return;
				}
				
				var $h_id = headline.attr('id');
				var $tagname = headline.prop( 'tagName' ).toLowerCase();
				var $txt = headline.text();
				var $pos = $levelslist.indexOf($tagname);
				
				if( typeof $h_id == 'undefined' )
				{
					var $new_id = av_pretty_url( $txt );
					headline.attr( 'id', $new_id );
					$h_id = $new_id;
				}
				
				var $list_tag = '<a href="#' + $h_id + '" class="avia-toc-link avia-toc-level-' + $pos + '"><span>' + $txt + '</span></a>';
				$toc_container.append( $list_tag );
			});

            // Smooth Scrolling
			$( ".avia-toc-smoothscroll .avia-toc-link" ).on( 'click', function(e)
			{
				e.preventDefault();
				
				var $target = $(this).attr('href');
				var $offset = 50;

				// calculate offset if there is a sticky header
				var $sticky_header = $('.html_header_top.html_header_sticky #header');

				if( $sticky_header.length ) 
				{
					$offset = $sticky_header.outerHeight() + 50;
				}

				$('html,body').animate( { scrollTop: $($target).offset().top - $offset } );
			});
        });
    });


    function av_pretty_url(text) 
	{
		return text.toLowerCase()
					.replace( /[^a-z0-9]+/g, "-" )
					.replace( /^-+|-+$/g, "-" )
					.replace( /^-+|-+$/g, '' );
    }

})( jQuery );
