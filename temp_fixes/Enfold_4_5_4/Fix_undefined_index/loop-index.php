<?php
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


global $avia_config, $post_loop_count;


if(empty($post_loop_count)) $post_loop_count = 1;
$blog_style = !empty($avia_config['blog_style']) ? $avia_config['blog_style'] : avia_get_option('blog_style','multi-big');
if(is_single()) $blog_style = avia_get_option('single_post_style','single-big');

$blog_global_style = avia_get_option('blog_global_style',''); //alt: elegant-blog

$blog_disabled = ( avia_get_option('disable_blog') == 'disable_blog' ) ? true : false;
if($blog_disabled)
{
	if (current_user_can('edit_posts'))
	{
		$msg = 	'<strong>'.__('Admin notice for:' )."</strong><br>".
						__('Blog Posts', 'avia_framework' )."<br><br>".
						__('This element was disabled in your theme settings. You can activate it here:' )."<br>".
					   '<a target="_blank" href="'.admin_url('admin.php?page=avia#goto_performance').'">'.__("Performance Settings",'avia_framework' )."</a>";
		
		$content 	= "<span class='av-shortcode-disabled-notice'>{$msg}</span>";
		
		echo $content;
	}
	
	 return;
}




$initial_id = avia_get_the_ID();

// check if we got posts to display:
if (have_posts()) :

	while (have_posts()) : the_post();

		/**
		 * initialise for each entry
		 */
		ShortcodeHelper::$tree = Avia_Builder()->get_shortcode_tree( get_the_ID() );
		ShortcodeHelper::$shortcode_index = 0;
		
	/*
     * get the current post id, the current post class and current post format
 	 */
	$url = "";
	$current_post = array();
	$current_post['post_loop_count']= $post_loop_count;
	$current_post['the_id']	   	= get_the_ID();
	$current_post['parity']	   	= $post_loop_count % 2 ? 'odd' : 'even';
	$current_post['last']      	= count($wp_query->posts) == $post_loop_count ? " post-entry-last " : "";
	$current_post['post_type']	= get_post_type($current_post['the_id']);
	$current_post['post_class'] 	= "post-entry-".$current_post['the_id']." post-loop-".$post_loop_count." post-parity-".$current_post['parity'].$current_post['last']." ".$blog_style;
	$current_post['post_class']	.= ($current_post['post_type'] == "post") ? '' : ' post';
	$current_post['post_format'] 	= get_post_format() ? get_post_format() : 'standard';
	$current_post['post_layout']	= avia_layout_class('main', false);
	$blog_content = !empty($avia_config['blog_content']) ? $avia_config['blog_content'] : "content";
	
	/*If post uses builder change content to exerpt on overview pages*/
    if( Avia_Builder()->get_alb_builder_status( $current_post['the_id'] ) && !is_singular($current_post['the_id']) && $current_post['post_type'] == 'post')
    {
	   $current_post['post_format'] = 'standard';
	   $blog_content = "excerpt_read_more";
    }
	

	/*
     * retrieve slider, title and content for this post,...
     */
    $size = strpos($blog_style, 'big') ? (strpos($current_post['post_layout'], 'sidebar') !== false) ? 'entry_with_sidebar' : 'entry_without_sidebar' : 'square';
    
    if(!empty($avia_config['preview_mode']) && !empty($avia_config['image_size']) && $avia_config['preview_mode'] == 'custom') $size = $avia_config['image_size'];
	
	/**
	 * @since 4.5.4
	 * @return string 
	 */
	$current_post['slider'] = apply_filters( 'avf_post_featured_image_link', get_the_post_thumbnail( $current_post['the_id'], $size ), $current_post, $size );
	
	/**
	 * Backwards comp. to checkbox prior v4.5.3 (now selectbox with '' or '1')
	 */
	$hide_featured_image = empty( get_post_meta( $current_post['the_id'], '_avia_hide_featured_image', true ) ) ? false : true;
	if( is_single( $initial_id ) && $hide_featured_image ) 
	{
		$current_post['slider'] = '';
	}
	
	$current_post['title']   	= get_the_title();
	$current_post['content'] 	= $blog_content == "content" ? get_the_content(__('Read more','avia_framework').'<span class="more-link-arrow"></span>') : get_the_excerpt();
	$current_post['content'] 	= $blog_content == "excerpt_read_more" ? $current_post['content'].'<div class="read-more-link"><a href="'.get_permalink().'" class="more-link">'.__('Read more','avia_framework').'<span class="more-link-arrow"></span></a></div>' : $current_post['content'];
	$current_post['before_content'] = "";

	/*
     * ...now apply a filter, based on the post type... (filter function is located in includes/helper-post-format.php)
     */
	$current_post	= apply_filters( 'post-format-'.$current_post['post_format'], $current_post );
	$with_slider    = empty($current_post['slider']) ? "" : "with-slider";
	/*
     * ... last apply the default wordpress filters to the content
     */
     
    
	$current_post['content'] = str_replace(']]>', ']]&gt;', apply_filters('the_content', $current_post['content'] ));

	/*
	 * Now extract the variables so that $current_post['slider'] becomes $slider, $current_post['title'] becomes $title, etc
	 */
	extract($current_post);








	/*
	 * render the html:
	 */

	echo "<article class='".implode(" ", get_post_class('post-entry post-entry-type-'.$post_format . " " . $post_class . " ".$with_slider))."' ".avia_markup_helper(array('context' => 'entry','echo'=>false)).">";
		
		
		
        //default link for preview images
        $link = !empty($url) ? $url : get_permalink();
        
        //preview image description
        $desc = get_post( get_post_thumbnail_id() );
        if(is_object($desc))  $desc = $desc -> post_excerpt;
		$featured_img_desc = ( $desc != "" ) ? $desc : the_title_attribute( 'echo=0' );

        //on single page replace the link with a fullscreen image
        if(is_singular())
        {
            $link = avia_image_by_id(get_post_thumbnail_id(), 'large', 'url');
        }

        if ( !in_array( $blog_style, array('bloglist-simple', 'bloglist-compact', 'bloglist-excerpt') ) ) {
            //echo preview image
            if (strpos($blog_global_style, 'elegant-blog') === false) {
                if (strpos($blog_style, 'big') !== false) {
                    if ($slider) $slider = '<a href="' . $link . '" title="' . $featured_img_desc . '">' . $slider . '</a>';
                    if ($slider) echo '<div class="big-preview ' . $blog_style . '">' . $slider . '</div>';
                }

                if (!empty($before_content))
                    echo '<div class="big-preview ' . $blog_style . '">' . $before_content . '</div>';
            }
        }
		
        echo "<div class='blog-meta'>";

        $blog_meta_output = "";
        $icon =  '<span class="iconfont" '.av_icon_string($post_format).'></span>';

            if(strpos($blog_style, 'multi') !== false)
            {
                $gravatar = "";
                $link = get_post_format_link($post_format);
                if($post_format == 'standard')
                {
                	$author_name = apply_filters('avf_author_name', get_the_author_meta('display_name', $post->post_author), $post->post_author);
					$author_email = apply_filters('avf_author_email', get_the_author_meta('email', $post->post_author), $post->post_author);
                	
					$gravatar_alt = esc_html($author_name);
					$gravatar = get_avatar($author_email, '81', "blank", $gravatar_alt);
					$link = get_author_posts_url($post->post_author);
                }

                $blog_meta_output = "<a href='{$link}' class='post-author-format-type'><span class='rounded-container'>".$gravatar.$icon."</span></a>";
            }
            else if(strpos($blog_style, 'small')  !== false)
            {
                $blog_meta_output = "<a href='{$link}' class='small-preview' title='{$featured_img_desc}'>".$slider.$icon."</a>";
            }

        echo apply_filters('avf_loop_index_blog_meta', $blog_meta_output);

        echo "</div>";

        echo "<div class='entry-content-wrapper clearfix {$post_format}-content'>";
            echo '<header class="entry-content-header">';

                if ($blog_style == 'bloglist-compact') {
                    $format = get_post_format();
                    echo "<span class=' fallback-post-type-icon' ".av_icon_string($format)."></span>";
                }

            	$close_header 	= "</header>"; 
            	
            	$content_output  =  '<div class="entry-content" '.avia_markup_helper(array('context' => 'entry_content','echo'=>false)).'>';
				$content_output .=  $content;
				$content_output .=  '</div>';
            	
            	
            	$taxonomies  = get_object_taxonomies(get_post_type($the_id));
                $cats = '';
                $excluded_taxonomies = array_merge( get_taxonomies( array( 'public' => false ) ), array('post_tag','post_format') );
				$excluded_taxonomies = apply_filters('avf_exclude_taxonomies', $excluded_taxonomies, get_post_type($the_id), $the_id);

                if(!empty($taxonomies))
                {
                    foreach($taxonomies as $taxonomy)
                    {
                        if(!in_array($taxonomy, $excluded_taxonomies))
                        {
                            $cats .= get_the_term_list($the_id, $taxonomy, '', ', ','').' ';
                        }
                    }
                }
            	
            	
            	
            	//elegant blog
            	//prev: if( $blog_global_style == 'elegant-blog' )
            	if( strpos($blog_global_style, 'elegant-blog') !== false )
            	{
	            	$cat_output = "";
	            	
	            	if(!empty($cats))
                    {
                        $cat_output .= '<span class="blog-categories minor-meta">';
                        $cat_output .= $cats;
                        $cat_output .= '</span>';
                        $cats = "";
                    }

                if ( in_array( $blog_style, array('bloglist-compact','bloglist-excerpt') ) ) {
                 echo $title;
                }
                else {

                    // The wrapper div prevents the Safari reader from displaying the content twice  ¯\_(ツ)_/¯
                    echo '<div class="av-heading-wrapper">';
                    if (strpos($blog_global_style, 'modern-blog') === false){
                        echo $cat_output.$title;
                    }
                    else {
                        echo $title.$cat_output;
                    }
                    echo '</div>';
                }


                    echo $close_header;
					$close_header = "";


                   if ( !in_array( $blog_style, array('bloglist-simple', 'bloglist-compact', 'bloglist-excerpt') ) ) {

                       echo '<span class="av-vertical-delimiter"></span>';

                       //echo preview image
                       if (strpos($blog_style, 'big') !== false) {
                           if ($slider) $slider = '<a href="' . $link . '" title="' . $featured_img_desc . '">' . $slider . '</a>';
                           if ($slider) echo '<div class="big-preview ' . $blog_style . '">' . $slider . '</div>';
                       }


                       if (!empty($before_content))
                           echo '<div class="big-preview ' . $blog_style . '">' . $before_content . '</div>';


                       echo $content_output;
                   }
					
					$cats = "";
					$title = "";
					$content_output = "";
				}
				
				
				
				
				echo $title;

                if ($blog_style !== 'bloglist-compact') :
				
                    echo "<span class='post-meta-infos'>";

                    echo "<time class='date-container minor-meta updated' >".get_the_time(get_option('date_format'))."</time>";
                    echo "<span class='text-sep text-sep-date'>/</span>";



                        if ( get_comments_number() != "0" || comments_open() ){

                        echo "<span class='comment-container minor-meta'>";
                        comments_popup_link(  "0 ".__('Comments','avia_framework'),
                                              "1 ".__('Comment' ,'avia_framework'),
                                              "% ".__('Comments','avia_framework'),'comments-link',
                                              "".__('Comments Disabled','avia_framework'));
                        echo "</span>";
                        echo "<span class='text-sep text-sep-comment'>/</span>";
                        }


                        if(!empty($cats))
                        {
                            echo '<span class="blog-categories minor-meta">'.__('in','avia_framework')." ";
                            echo $cats;
                            echo '</span><span class="text-sep text-sep-cat">/</span>';
                        }


                        echo '<span class="blog-author minor-meta">'.__('by','avia_framework')." ";
                        echo '<span class="entry-author-link" >';
                        echo '<span class="vcard author"><span class="fn">';
                        the_author_posts_link();
                        echo '</span></span>';
                        echo '</span>';
                        echo '</span>';

                        if ($blog_style == 'bloglist-simple') {
                            echo '<div class="read-more-link"><a href="'.get_permalink().'" class="more-link">'.__('Read more','avia_framework').'<span class="more-link-arrow"></span></a></div>';
                        }

                    echo '</span>';

                endif; // display meta-infos on all layouts except bloglist-compact

            echo $close_header;


            // echo the post content
            if ( $blog_style == 'bloglist-excerpt'){
                the_excerpt();
                echo '<div class="read-more-link"><a href="'.get_permalink().'" class="more-link">'.__('Read more','avia_framework').'<span class="more-link-arrow"></span></a></div>';
            }

            if ( !in_array( $blog_style, array('bloglist-simple', 'bloglist-compact', 'bloglist-excerpt') ) ) {
                echo $content_output;
            }


            echo '<footer class="entry-footer">';

            $avia_wp_link_pages_args = apply_filters('avf_wp_link_pages_args', array(
                                                                                    'before' =>'<nav class="pagination_split_post">'.__('Pages:','avia_framework'),
                                                                                    'after'  =>'</nav>',
                                                                                    'pagelink' => '<span>%</span>',
                                                                                    'separator'        => ' ',
                                                                                    ));

            wp_link_pages($avia_wp_link_pages_args);

            if(is_single() && !post_password_required())
            {
            	//tags on single post
            	if(has_tag())
            	{
                	echo '<span class="blog-tags minor-meta">';
                	the_tags('<strong>'.__('Tags:','avia_framework').'</strong><span> ');
                	echo '</span></span>';
            	}
            	
            	//share links on single post
            	avia_social_share_links();
   
            }
            
            do_action('ava_after_content', $the_id, 'post');

            echo '</footer>';

        echo "<div class='post_delimiter'></div>";
        echo "</div>";
        echo "<div class='post_author_timeline'></div>";
        echo av_blog_entry_markup_helper($current_post['the_id']);
	echo "</article>";

	$post_loop_count++;
	endwhile;
	else:

?>

    <article class="entry">
        <header class="entry-content-header">
            <h1 class='post-title entry-title'><?php _e('Nothing Found', 'avia_framework'); ?></h1>
        </header>

        <p class="entry-content" <?php avia_markup_helper(array('context' => 'entry_content')); ?>><?php _e('Sorry, no posts matched your criteria', 'avia_framework'); ?></p>

        <footer class="entry-footer"></footer>
    </article>

<?php

	endif;

	if(empty($avia_config['remove_pagination'] ))
	{
		echo "<div class='{$blog_style}'>".avia_pagination('', 'nav')."</div>";
	}
