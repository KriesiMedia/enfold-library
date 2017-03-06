<?php
/* ======================= BREADCRUMB SHORTCODE   =======================
   ======================= (copy from functions-enfold.php ======================= */
add_shortcode( 'av_bread_crumb', 'avia_title_child' );
//advanced title + breadcrumb function
if(!function_exists('avia_title_child'))
{
	function avia_title_child($args = false, $id = false)
	{
		global $avia_config;

		if(!$id) $id = avia_get_the_id();
		
		$header_settings = avia_header_setting();
		if($header_settings['header_title_bar'] == 'hidden_title_bar') return "";
		
		$defaults 	 = array(

			'title' 		=> get_the_title($id),
			'subtitle' 		=> "", //avia_post_meta($id, 'subtitle'),
			'link'			=> get_permalink($id),
			//'html'			=> "<div class='{class} title_container'><div class='container'><{heading} class='main-title entry-title'>{title}</{heading}>{additions}</div></div>",
			'html'			=> "{additions}",
			//'class'			=> 'stretch_full container_wrap alternate_color '.avia_is_dark_bg('alternate_color', true),
			'breadcrumb'	=> true,
			'additions'		=> "",
			'heading'		=> 'h1' //headings are set based on this article: http://yoast.com/blog-headings-structure/
		);

		if ( is_tax() || is_category() || is_tag() )
		{
			global $wp_query;

			$term = $wp_query->get_queried_object();
			$defaults['link'] = get_term_link( $term );
		}
		else if(is_archive())
		{
			$defaults['link'] = "";
		}
		
		
		// Parse incomming $args into an array and merge it with $defaults
		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters('avf_title_args', $args, $id);

		//disable breadcrumb if requested
		if($header_settings['header_title_bar'] == 'title_bar') $args['breadcrumb'] = false;
		
		//disable title if requested
		if($header_settings['header_title_bar'] == 'breadcrumbs_only') $args['title'] = '';


		// OPTIONAL: Declare each item in $args as its own variable i.e. $type, $before.
		extract( $args, EXTR_SKIP );

		if(empty($title)) $class .= " empty_title ";
        $markup = avia_markup_helper(array('context' => 'avia_title','echo'=>false));
		if(!empty($link) && !empty($title)) $title = "<a href='".$link."' rel='bookmark' title='".__('Permanent Link:','avia_framework')." ".esc_attr( $title )."' $markup>".$title."</a>";
		if(!empty($subtitle)) $additions .= "<div class='title_meta meta-color'>".wpautop($subtitle)."</div>";
		if($breadcrumb) $additions .= avia_breadcrumbs(array('separator' => ' &raquo; ', 'richsnippet' => true, 'before' => ''));


		$html = str_replace('{class}', $class, $html);
		$html = str_replace('{title}', $title, $html);
		$html = str_replace('{additions}', $additions, $html);
		$html = str_replace('{heading}', $heading, $html);



		if(!empty($avia_config['slide_output']) && !avia_is_dynamic_template($id) && !avia_is_overview())
		{
			$avia_config['small_title'] = $title;
		}
		else
		{
			return $html;
		}
	}
}
/* ======================= END BREADCRUMB SHORTCODE ======================= */
