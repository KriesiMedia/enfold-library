/**
 * Use the "avf_which_archive_output" filter to adjust title for achives in title bar
 *
 **/
 
/* Following code removes "Archive for" on tag archive page */  

function avia_new_tag_archive()
{
	if (is_tag())
	{
		$output = __('','avia_framework')." ".single_tag_title('',false);
	}

	return $output;
}

add_filter('avf_which_archive_output','avia_new_tag_archive');
