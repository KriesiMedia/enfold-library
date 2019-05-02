/**
 * Use the "avia_post_nav_entries" filter to adjust post navigation
 *
 **/


/* Following code removes post nav */ 
add_filter('avia_post_nav_entries','avia_remove_post_nav', 10, 1);
function avia_remove_post_nav()
{
return false;
}
