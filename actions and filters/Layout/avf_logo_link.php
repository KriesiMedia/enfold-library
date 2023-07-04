/**
 * Use "avf_logo_link" filter to change logo's URL
 *
 **/
 
/* Following code changes logo URL based on page ID */

function av_change_logo_link($link)
{
    if( is_page(59) )
    {
        $link = "https://your-domain.com";
    }

    return $link;
}

add_filter( 'avf_logo_link','av_change_logo_link' );
