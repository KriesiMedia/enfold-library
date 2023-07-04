/**
 * Use the "avf_slideshow_title" filter to adjust slide show titles 
 *
 **/
 
 /* Following code replaces slideshow title */ 
function my_new_title_tag()
{ 
    $output = "My new title goes here";
    return $output;
}

add_filter('avf_slideshow_title','my_new_title_tag');
