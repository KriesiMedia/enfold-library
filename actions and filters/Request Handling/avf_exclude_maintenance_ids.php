/**
 * Allows excluding pages in Maintenance mode. Return the page IDs to be excluded in an array.
 *
 * @since 5.1
 */

add_filter('avf_exclude_maintenance_ids', function($ids) {
   return [59];
});
