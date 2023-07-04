/**
 * Use "avf_logo" filter to change logo.
 *
 **/
 
/* Following code changes logo based on page ID */

function av_change_logo( $logo )
{
    if( is_page(59) )
    {
        $logo = "https://your-domain.com/secondary-logo.png";
    }

    return $logo;
}

add_filter( 'avf_logo','av_change_logo');
