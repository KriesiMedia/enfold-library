/**
 * Sticky Posts Query
 *
 * Use the "avia_post_slide_query" filter to alter the query or enable the sticky posts feature for the post slider and blog grid layout.
 * Related thread: http://www.kriesi.at/support/topic/sticky-posts-in-b-og-grid/
 *
 **/


/** Add the following snippet in the  functions.php file. **/

add_filter('avia_post_slide_query', 'avf_enfold_post_slide_sticky_posts_query', 10, 2);
function avf_enfold_post_slide_sticky_posts_query($query, $params) {
	$include = array();
	$sticky = get_option( 'sticky_posts' );

	$args = array(
	  'taxonomy' => $params['taxonomy'],
	  'post__not_in' => $sticky,
	);
	$posts = get_posts( $args );

	foreach($posts as $post) {
		$include[] = $post->ID;
	}

	$include = array_merge($sticky, $include);

	// convert values of the $include from string to int
	function sti($n)
	{
		settype($n, 'int');
		return $n ;
	}

	$include = array_map("sti", $include);

	$query['post__in'] = $include;
	$query['posts_per_page'] = 6;
	$query['orderby'] = 'post__in'; // sort items based on the post__in value
	return $query;
}
