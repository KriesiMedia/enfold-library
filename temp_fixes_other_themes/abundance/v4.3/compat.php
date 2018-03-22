<?php
//Since the avid framework uses some functions that are very different to the default use of wordpress (for example post thumbnails) the functions here are provided for better compatibility with external plugins



if(!function_exists('avia_backend_compatibility_featured_image'))
{
	/*
	* This function saves the first slideshow image as featured image so plugins that make use of that feature image are able to retrieve it
	*/

	function avia_backend_compatibility_featured_image($post_id, $result)
	{
		if(isset($result['slideshow'][0]['slideshow_image']))
		{
			$attachmend_id = $result['slideshow'][0]['slideshow_image'];

			if($attachmend_id == "" || ($attachmend_id != "" && ! is_numeric($attachmend_id)))
			{
				delete_post_meta($post_id, '_thumbnail_id');
			}

			if(is_numeric($attachmend_id))
			{
				update_post_meta($post_id, '_thumbnail_id', $attachmend_id);
			}
		}
		else if( in_array(get_post_type($post_id), array('post','page')) )
		{
			delete_post_meta($post_id, '_thumbnail_id');
		}
	}

	add_action('avia_meta_box_save_post','avia_backend_compatibility_featured_image',10,2);
}


if(!function_exists('avia_backend_compatibility_custom_field_filter'))
{
	/*
	* This function checks if the current custom field is the slideshow custom field and overwrites the first element, in case it is empty and a feature image is set
	*/

	function avia_backend_compatibility_custom_field_filter($custom_fields, $post_id)
	{
		if(empty($custom_fields))
		{
			$custom_fields = array(
					'slideshow'		=>	array(
									0	=>	array( 'slideshow_image' =>	'')
								)
				);
		}

		if(isset($custom_fields['slideshow']) && is_array($custom_fields['slideshow']) && isset($custom_fields['slideshow'][0]['slideshow_image']))
		{
			$post_thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );

			if($custom_fields['slideshow'][0]['slideshow_image'] == "" && $post_thumbnail_id)
			{
				$custom_fields['slideshow'][0]['slideshow_image'] = $post_thumbnail_id;
			}
		}
		return $custom_fields;
	}

	add_filter('avia_meta_box_filter_custom_fields','avia_backend_compatibility_custom_field_filter',10,2);
	add_filter('avia_post_meta_filter','avia_backend_compatibility_custom_field_filter',10,2);
}

if(!function_exists('avia_get_post_by_title'))
{
	function avia_get_post_by_title($post_title)
	{

		global $wpdb;
		$post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='avia_framework_post'", $post_title ));

		if ( $post )
		{
			$return = get_post($post, 'ARRAY_A');
			return $return;
		}

		return null;
	}
}