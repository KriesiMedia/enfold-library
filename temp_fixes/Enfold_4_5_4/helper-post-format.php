<?php
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


/*
 * 	The loop-index.php file is responsible to display wordpress blog posts
 *	Since this theme supports post formats (different styling and behaviour of certain posts, for example galleries, tweets etc)
 *	the output of the  loop-index.php file is filtered before it is passed to the users browser.
 *
 *	The filtering takes place in the functions defined in this file
 */



// ========================= default post format ============================

add_filter( 'post-format-standard', 'avia_default_title_filter', 10, 1 );

// ========================= gallery post format ============================

add_filter( 'post-format-gallery', 	 'avia_gallery_slideshow_filter', 10, 1 );

// ========================= video post format ==============================

add_filter( 'post-format-video', 'avia_video_slideshow_filter', 10, 1 );

// ========================= image post format ==============================

add_filter( 'post-format-image', 'avia_image_slideshow_filter', 10, 1 );

// ========================= link post format ===============================

add_filter( 'post-format-link', 'avia_link_content_filter', 10, 1 );

// ========================= blockquote post format =========================

add_filter( 'post-format-quote', 'avia_quote_content_filter', 10, 1 );

// ========================= audio post format =========================

add_filter( 'post-format-audio', 'avia_audio_content_filter', 10, 1 );



// =============================================================================================================================




/**
 *   The avia_default_title_filter creates the default title for your posts.
 *   This function is used by most post types
 */
if(!function_exists('avia_default_title_filter'))
{
	function avia_default_title_filter($current_post)
	{
		if(!empty($current_post['title']))
		{
			$heading = is_singular() ? "h1" : "h2";
	
			$output  = "";
			//$output .= "<{$heading} class='post-title entry-title ". avia_offset_class('meta', false). "'>";
			$output .= "<{$heading} class='post-title entry-title' ".avia_markup_helper(array('context' => 'entry_title','echo'=>false)).">";
			$output .= "	<a href='".get_permalink()."' rel='bookmark' title='". __('Permanent Link:','avia_framework')." ".$current_post['title']."'>".$current_post['title'];
			$output .= "			<span class='post-format-icon minor-meta'></span>";
			$output .= "	</a>";
			$output .= "</{$heading}>";
	
			$current_post['title'] = $output;
		}

		return $current_post;
	}
}

/**
 *  The avia_audio_content_filter checks if a audio embed is provided and extracts it
 *  If no slideshow is set, it checks if the content holds a video url, removes it and uses it for the slideshow
 *  The filter also sets the height of the slideshow to fullsize, and even on overview posts all slides are displayed
 */
if(!function_exists('avia_audio_content_filter'))
{
	function avia_audio_content_filter($current_post)
	{
		preg_match("!\[audio.+?\]\[\/audio\]!", $current_post['content'], $match_audio);

		if(!empty($match_audio))
		{
			$current_post['before_content'] = do_shortcode($match_audio[0]);
			$current_post['content'] = str_replace($match_audio[0], "", $current_post['content']);
		}

		return avia_default_title_filter($current_post);
	}
}



/**
 *  The avia_gallery_slideshow_filter checks if a gallery is set for an entry with post type gallery
 *  and extracts the gallery, then displays it at the top of the entry
 *
 *  The filter also sets the height of the slideshow to fullsize, and even on overview posts all slides are displayed
 */
if(!function_exists('avia_gallery_slideshow_filter'))
{
	function avia_gallery_slideshow_filter($current_post)
	{
		//search for the first av gallery or gallery shortcode
		preg_match("!\[(?:av_)?gallery.+?\]!", $current_post['content'], $match_gallery);

		if(!empty($match_gallery))
		{
			$gallery = $match_gallery[0];

			if(strpos($gallery, 'av_') === false)   $gallery = str_replace("gallery", 'av_gallery', $gallery);
			if(strpos($gallery, 'style') === false) $gallery = str_replace("]", " style='big_thumb' preview_size='gallery']", $gallery);


			$current_post['before_content'] = do_shortcode($gallery);
			$current_post['content'] = str_replace($match_gallery[0], "", $current_post['content']);
			$current_post['slider'] = "";
		}

		return avia_default_title_filter($current_post);
	}
}



/**
 *  The avia_video_slideshow_filter checks if a video slideshow is set for an entry with post type gallery
 *  If no slideshow is set, it checks if the content holds a video url, removes it and uses it for the slideshow
 *  The filter also sets the height of the slideshow to fullsize, and even on overview posts all slides are displayed
 */
if(!function_exists('avia_video_slideshow_filter'))
{
	function avia_video_slideshow_filter($current_post)
	{
		//replace empty url strings with an embed code
	 	$current_post['content'] = preg_replace( '|^\s*(https?://[^\s"]+)\s*$|im', "[embed]$1[/embed]", $current_post['content'] );

		//extrect embed and av_video codes from the content. if any were found execute them and prepend them to the post
		preg_match("!\[embed.+?\]|\[av_video.+?\]!", $current_post['content'], $match_video);

		if(!empty($match_video))
		{
			global $wp_embed;
			$video = $match_video[0];
			$current_post['before_content'] = do_shortcode($wp_embed->run_shortcode($video));
			$current_post['content'] = str_replace($match_video[0], "", $current_post['content']);
			$current_post['slider'] = "";
		}

		return avia_default_title_filter($current_post);
	}
}


/**
 *  The avia_image_slideshow_filter checks if an image is set for an entry with post type image
 *  If no image is set, it checks if the content holds an image, removes it and uses it for the slideshow
 *  The filter also sets the height of the slideshow to fullsize, and even on overview posts all slides are displayed
 */
if(!function_exists('avia_image_slideshow_filter'))
{
	function avia_image_slideshow_filter($current_post)
	{

		$prepend_image = get_the_post_thumbnail(get_the_ID(), 'large');
		$image = "";

		if(!$prepend_image)
		{
			$image		= avia_regex($current_post['content'],'image');
			if(is_array($image))
			{
				$image = $image[0];
				$prepend_image = '<div class="avia-post-format-image"><img src="'.$image.'" alt="" title ="" /></div>';
			}
			else
			{
				$image		= avia_regex($current_post['content'],'<img />',"");
				if(is_array($image))
				{
					$prepend_image = '<div class="avia-post-format-image">'.$image[0]."</div>";
				}
			}
		}
		else
		{
			
			$large_image = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'extra_large' );
			$prepend_image = '<div class="avia-post-format-image"><a href="'.$large_image[0].'">'.$prepend_image."</a></div>";
		}


		if( ! empty( $prepend_image ) && is_string( $prepend_image ) )
		{
			if( $image ) 
			{
				$current_post['content'] = str_replace( $image, '', $current_post['content'] );
			}
			
			$current_post['before_content'] = $prepend_image;
			$current_post['slider']  = "";
		}

		/**
		 * Backwards comp. to checkbox prior v4.5.3 (now selectbox with '' or '1')
		 */
		$hide_featured_image = get_post_meta( get_the_ID(), '_avia_hide_featured_image', true );
		$hide_featured_image = empty( $hide_featured_image ) ? false : true;
		if( is_single( get_the_ID() ) && $hide_featured_image ) 
		{
			$current_post['before_content'] = '';
		}
		
		return avia_default_title_filter($current_post);
	}
}


/**
 *  The avia_link_content_filter checks if the beginning of the post is a url. If thats the case this url will be aplied to the title.
 *  Otherwise the theme will search for the first URL within the content and apply this URL
 */
if(!function_exists('avia_link_content_filter'))
{
	function avia_link_content_filter($current_post)
	{
		//retrieve the link for the post
		$link 		= "";
		$newlink    = false;
		$pattern1 	= '$^\b(https?|ftp|file)://[-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|]$i';
		$pattern2 	= "!^\<a.+?<\/a>!";
		$pattern3 	= "!\<a.+?<\/a>!";

		//if the url is at the begnning of the content extract it
		preg_match($pattern1, $current_post['content'] , $link);
		if(!empty($link[0]))
		{
			$link = $link[0];
			$markup = avia_markup_helper(array('context' => 'entry_title','echo'=>false));
			$current_post['title'] = "<a href='$link' rel='bookmark' title='".__('Link to:','avia_framework')." ".the_title_attribute('echo=0')."' $markup>".get_the_title()."</a>";
			$current_post['content'] = preg_replace("!".str_replace("?", "\?", $link)."!", "", $current_post['content'], 1);
		}
		else
		{
			preg_match($pattern2, $current_post['content'] , $link);
			if(!empty($link[0]))
			{
				$link = $link[0];
				$current_post['title'] = $link;
				$current_post['content'] = preg_replace("!".str_replace("?", "\?", $link)."!", "", $current_post['content'], 1);
				
				$newlink = get_url_in_content( $link );
			}
			else
			{
				preg_match($pattern3,  $current_post['content'] , $link);
				if(!empty($link[0]))
				{
					$current_post['title'] = $link[0];
					
					$newlink = get_url_in_content( $link[0] );
					
				}
			}
		}

		if($link)
		{
			if(is_array($link)) $link = $link[0];
			if($newlink) $link = $newlink;
			
			$heading = is_singular() ? "h1" : "h2";

			//$current_post['title'] = "<{$heading} class='post-title entry-title ". avia_offset_class('meta', false). "'>".$current_post['title']."</{$heading}>";
			$current_post['title'] = "<{$heading} class='post-title entry-title' ".avia_markup_helper(array('context' => 'entry_title','echo'=>false)).">".$current_post['title']."</{$heading}>";
			
			//needs to be set for masonry
			$current_post['url'] = $link;
		}
		else
		{
			$current_post = avia_default_title_filter($current_post);
		}

		return $current_post;
	}
}



/**
 *  Function for posts of type quote: title is wrapped in blockquote tags instead of h1
 */
if(!function_exists('avia_quote_content_filter'))
{
	function avia_quote_content_filter($current_post)
	{
		if(!empty($current_post['title']))
		{
			//$current_post['title'] 		= "<div class='". avia_offset_class('meta', false). "'><blockquote class='first-quote'>".$current_post['title']."</blockquote></div>";
			$current_post['title'] 		= "<blockquote class='first-quote' ".avia_markup_helper(array('context' => 'entry_title','echo'=>false)).">".$current_post['title']."</blockquote>";
		}
		return $current_post;
	}
}
