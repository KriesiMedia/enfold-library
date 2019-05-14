<?php

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


function avia_bbpress_enabled()
{
	if (class_exists( 'bbPress' )) { return true; }
	return false;
}

//check if the plugin is enabled, otherwise stop the script
if(!avia_bbpress_enabled()) { return false; }


global $avia_config;


//register my own styles
add_filter('bbp_default_styles', 'avia_bbpress_deregister_default_assets', 10, 1);
function avia_bbpress_deregister_default_assets($styles)
{
	return array();
}

if(!is_admin()){ add_action('bbp_enqueue_scripts', 'avia_bbpress_register_assets',15); }

function avia_bbpress_register_assets()
{
	global $bbp;
	wp_enqueue_style( 'avia-bbpress', AVIA_BASE_URL.'config-bbpress/bbpress-mod.css');
}



//add classnames to topic class for even nicer icon display
add_filter('bbp_get_topic_class', 'avia_bbpress_add_topic_class');
function avia_bbpress_add_topic_class($classes)
{
	$voices = bbp_get_topic_voice_count() > 1 ? "multi" : "single";

	$classes[] = 'topic-voices-'.$voices;
	return $classes;
}



//remove forum and single topic summaries at the top of the page
add_filter('bbp_get_single_forum_description', 'avia_bbpress_filter_form_message',10,2 );
add_filter('bbp_get_single_topic_description', 'avia_bbpress_filter_form_message',10,2 );



add_filter( 'avia_style_filter', 'avia_bbpress_forum_colors', 10, 1 );
/* add some color modifications to the forum table items */
function avia_bbpress_forum_colors( array $config )
{
	
	return $config;
}


function avia_bbpress_filter_form_message( $retstr, $args )
{
	//removes forum summary, voices count etc
	return false;
}



/*modify default breadcrumb to work better with bb forums*/

if(!function_exists('avia_fetch_bb_trail'))
{
	//fetch bb trail and set the bb breadcrum output to false
	function avia_fetch_bb_trail($trail, $breadcrumbs, $r)
	{
		global $avia_config;
		$avia_config['bbpress_trail'] = $breadcrumbs;
		
		return false;
	}
	
	add_filter('bbp_get_breadcrumb','avia_fetch_bb_trail',10,3);
}

if(!function_exists('avia_bbpress_breadcrumb'))
{
	//if we are viewing a forum page set the avia breadcrumb output to match the forum breadcrumb output
	function avia_bbpress_breadcrumb($trail)
	{ 
		global $avia_config;
	
		if((isset($avia_config['currently_viewing']) && $avia_config['currently_viewing'] == 'forum') || get_post_type() === "forum" || get_post_type() === "topic")
		{
			$bc = bbp_get_breadcrumb();
			
			if(isset($avia_config['bbpress_trail'] )) 
			{ 
				$trail_zero = $trail[0];
				$trail = $avia_config['bbpress_trail'] ;
				$trail[0] = $trail_zero;
			}
			
			if((bbp_is_single_user_edit() || bbp_is_single_user()))
			{
				$user_info = get_userdata(bbp_get_displayed_user_id());
				$title = __("Profile for User:","avia_framework")." ".$user_info->display_name;
				array_pop($trail);
				$trail[] = $title;
			}
		}			
		return $trail;
	}
	
	
	add_filter('avia_breadcrumbs_trail','avia_bbpress_breadcrumb');
}




	register_sidebar(array(
		'name' => 'Forum',
		'before_widget' => '<div id="%1$s" class="widget clearfix %2$s">', 
		'after_widget' => '<span class="seperator extralight-border"></span></div>', 
		'before_title' => '<h3 class="widgettitle">', 
		'after_title' => '</h3>',
		'id' => 'av_forum' 
	));
	
/*
	
	add_filter('bbp_view_widget_title', 'avia_widget_title');
	add_filter('bbp_login_widget_title', 'avia_widget_title');
	add_filter('bbp_forum_widget_title', 'avia_widget_title');
	add_filter('bbp_topic_widget_title', 'avia_widget_title');
	add_filter('bbp_replies_widget_title', 'avia_widget_title');
*/


if(!function_exists('avia_remove_bbpress_post_type_options'))
{
	function avia_remove_bbpress_post_type_options($post_type_option, $args)
	{
	    if(!empty($post_type_option))
	    {
	        foreach($post_type_option as $key => $post_type)
	        {
	            if($post_type == 'forum' || $post_type == 'topic' || $post_type == 'reply')
	            {
	                unset($post_type_option[$key]);
	            }
	        }
	    }
	
	    return $post_type_option;
	}
	
	add_filter('avf_registered_post_type_array', 'avia_remove_bbpress_post_type_options', 10, 2);
}


if(!function_exists('avia_remove_bbpress_post_type_from_query'))
{
	function avia_remove_bbpress_post_type_from_query($query, $params)
	{
	    if(!empty($query['post_type']) && is_array($query['post_type']))
	    {
	        foreach($query['post_type'] as $key => $post_type)
	        {
	            if($post_type == 'forum' || $post_type == 'topic' || $post_type == 'reply')
	            {
	                unset($query['post_type'][$key]);
	            }
	        }
	    }
	
	    return $query;
	}
	
	add_filter('avia_masonry_entries_query', 'avia_remove_bbpress_post_type_from_query', 10, 2);
	add_filter('avia_post_grid_query', 'avia_remove_bbpress_post_type_from_query', 10, 2);
	add_filter('avia_post_slide_query', 'avia_remove_bbpress_post_type_from_query', 10, 2);
	add_filter('avia_blog_post_query', 'avia_remove_bbpress_post_type_from_query', 10, 2);
	add_filter('avf_magazine_entries_query', 'avia_remove_bbpress_post_type_from_query', 10, 2);
	add_filter('avf_accordion_entries_query', 'avia_remove_bbpress_post_type_from_query', 10, 2);
}



if( ! function_exists( 'avia_bbpress_avf_post_nav_settings' ) )
{
	/**
	 * Remove bbPress pottpes from post nav links
	 * 
	 * @since 4.5.6
	 * @param array $settings
	 * @return array
	 */
	function avia_bbpress_avf_post_nav_settings( array $settings )
	{
		if( in_array( $settings['type'], array( 'topic',  'reply' ) ) )
		{
			$settings['skip_output'] = true;
		}
		
		return $settings;
	}
	
	add_filter( 'avf_post_nav_settings', 'avia_bbpress_avf_post_nav_settings', 10, 1 );
}


if( ! function_exists( 'avia_bbpress_before_page_in_footer_compile' ) )
{
	/**
	 * BBPress alters the content filter on its pages. We need to reset this to allow our shortcodes to run
	 * 
	 * @since 4.5.6.1
	 * @param WP_Post $footer_page
	 * @param int $post_id
	 */
	function avia_bbpress_before_page_in_footer_compile( WP_Post $footer_page, $post_id )
	{
		$current = get_post( $post_id );
		$forum_page = false;
		
		if( bbp_is_single_user() || bbp_is_single_user_edit() || bbp_is_user_home() || bbp_is_user_home_edit()  )
		{
			$forum_page = true;
		}
		else if( bbp_is_topics_created() || bbp_is_replies_created() || bbp_is_favorites() || bbp_is_subscriptions() )
		{
			$forum_page = true;
		}
		else if( bbp_is_forum_archive() )
		{
			$forum_page = true;
		}
		else
		{
			if( ! $current instanceof WP_Post )
			{
				return;
			}
		}
		
		$bbp_post_types = array(
								bbp_get_forum_post_type(),
								bbp_get_topic_post_type(),
								bbp_get_reply_post_type()
							);
		
		if( $forum_page || in_array( $current->post_type, $bbp_post_types ) )
		{
			bbp_restore_all_filters( 'the_content' );
		}
	}
	
	add_action( 'ava_before_page_in_footer_compile', 'avia_bbpress_before_page_in_footer_compile', 10, 2 );
}

