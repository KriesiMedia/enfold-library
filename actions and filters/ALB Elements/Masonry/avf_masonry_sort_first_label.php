/**
 * Use the "avf_masonry_sort_first_label" filter changes the first label (All) in Masonry element
 *
 **/
function new_masonry_first_label() 
{
    $first_item_name = "NEW ALL TEXT ";
    return $first_item_name;
}

add_filter('avf_masonry_sort_first_label','new_masonry_first_label');
