/**
 * Use the "avia_widget_time" filter to adjust time and date of Enfold widgets
 *
 **/
 
 /* Following code removes time and displays date only */
add_filter('avia_widget_time', 'change_avia_date_format', 10, 2);
function change_avia_date_format($date, $time_format) {
  $time_format = get_option('date_format');
  return $time_format;
}

/* Following code removes both date and time */ 
add_filter('avia_widget_time', 'change_avia_date_format', 10, 2);
function change_avia_date_format($date, $function) {
  return false;
}
