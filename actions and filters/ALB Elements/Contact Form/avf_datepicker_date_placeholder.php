/**
 * Use the "avf_datepicker_date_placeholder" filter changes placeholder of Date input in contact form
 *
 **/
 
add_filter('avf_datepicker_date_placeholder', 'new_date_placeholder');
function new_date_placeholder() {
$placeholder = "MM / DD / YY";
return $placeholder;
}
