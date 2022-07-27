if( ! function_exists( 'avia_check_sticky_posts' ) )
{
	add_filter('avia_post_slide_query', 'avia_check_sticky_posts', 10, 2);

	/**
	 * Fetch sticky entries
	 *
	 * @since > 5.0
	 * @param array $query
	*/
	function avia_check_sticky_posts($query, $params)
	{
		$paged = $query['paged'];

		// unset posts_per_page and paged var to retrieve all stickies
		$query = array_diff_key(
			$query, 
			array_flip(['posts_per_page', 'paged'])
		);

		$query['fields'] = 'ids';

		// do the initial WP_Query and return all posts ids
		$entries = new WP_Query($query);
		$stickies = [];
		$entries_sorted = [];
		$has_sticky = false;

		foreach ($entries->posts as $entry) {
			if (is_sticky($entry)) {		
				$has_sticky = true;	
				array_push($stickies, $entry);
				continue;
			}

			array_push($entries_sorted, $entry);
		}

		// if there is at least one sticky post, set query parameters accordingly
		if ($has_sticky) {			
			// remove unnecessary parameters from the main query
			$query = array_diff_key(
				$query, 
				array_flip(['post_type', 'meta_query', 'date_query'])
			);

			$query['post__in'] = array_merge($stickies, $entries_sorted);
			$query['orderby'] = 'post__in';
		}

		$query['posts_per_page'] = $params['items'];
		$query['paged'] = $paged;
		$query['fields'] = 'all';
		
		return $query;
	}
}
