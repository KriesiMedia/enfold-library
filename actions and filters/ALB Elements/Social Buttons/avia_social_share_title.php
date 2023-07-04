/**
 * Use the "avia_social_share_title" filter to change Social Share title on blog post created without ALB
 *
 **/

function new_share_title( $title , $args ) 
{
    $output = 'Share this post on:';
    return $output;
}

add_filter( 'avia_social_share_title','new_share_title', 10, 2 );
