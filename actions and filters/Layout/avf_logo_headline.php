/**
 * Use "avf_logo_headline" filter to change logo's HTML tag 
 *
 **/
 
/* Following code changes logo's tag to H1 on homepage */

function change_logo_to_h1() 
{
  if( is_home() ) 
  { 
    return "h1";  
  }
}

add_filter( 'avf_logo_headline', 'change_logo_to_h1', 10);
