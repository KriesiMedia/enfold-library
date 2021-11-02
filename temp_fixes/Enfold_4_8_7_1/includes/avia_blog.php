<?php
/**
 * Blog Layout Tab
 * ===============
 *
 * @since 4.8.2
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $avia_pages, $avia_elements;

/**
 * This is only a temporary solution - will be moved to a class later
 */
$avia_config['social_icon_array'] = array(
						'500px'			=> 'five_100_px',
						'Behance'		=> 'behance',
						'Dribbble'		=> 'dribbble',
						'Facebook'		=> 'facebook',
						'Flickr'		=> 'flickr',
						'Instagram'		=> 'instagram',
						'LinkedIn'		=> 'linkedin',
						'Pinterest'		=> 'pinterest',
						'Reddit'		=> 'reddit',
						'Skype'			=> 'skype',
						'Soundcloud'	=> 'soundcloud',
						'Tumblr'		=> 'tumblr',
						'Twitter'		=> 'twitter',
						'Vimeo'			=> 'vimeo',
						'Vk'			=> 'vk',
						'Xing'			=> 'xing',
						'Yelp'			=> 'yelp',
						'YouTube'		=> 'youtube',
						'WhatsApp'		=> 'whatsapp',
						__( 'Special: RSS (add RSS URL, leave blank if you want to use default WordPress RSS feed)', 'avia_framework' ) => 'rss',
						__( 'Special: Email Icon (add your own URL to link to a contact form)', 'avia_framework' ) => 'mail',
					);

/**
 * @since ????
 * @param array $social_icon_array
 * @return array
 */
$avia_config['social_icon_array'] = apply_filters( 'avf_social_icons_options', $avia_config['social_icon_array'] );



$avia_config['social_share_array'] = array(
						'Facebook'		=> 'facebook',
						'Twitter'		=> 'twitter',
						'WhatsApp'		=> 'whatsapp',
						'Pinterest'		=> 'pinterest',
						'Reddit'		=> 'reddit',
						'LinkedIn'		=> 'linkedin',
						'Tumblr'		=> 'tumblr',
						'Vk'			=> 'vk',
						'Yelp'			=> 'yelp',
						__( 'Special: Email Icon (add your own URL to link to a contact form)', 'avia_framework' ) => 'mail'
					);

/**
 * @since 4.8.4.1
 * @param array $avia_config['social_share_array']
 * @return array
 */
$avia_config['social_share_array'] = apply_filters( 'avf_social_share_array_options', $avia_config['social_share_array'] );


$avia_config['social_profile_array'] = array_diff( $avia_config['social_icon_array'], $avia_config['social_share_array'] );



$avia_elements[] = array(
			'slug'		=> 'blog',
			'name'		=> __( 'Blog Styling', 'avia_framework' ),
			'desc'		=> __( 'Choose the blog styling here.', 'avia_framework' ),
			'id'		=> 'blog_global_style',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
			'subtype'	=> array(
							__( 'Default (Business)', 'avia_framework' )	=> '',
							__( 'Elegant', 'avia_framework' )				=> 'elegant-blog',
							__( 'Modern Business', 'avia_framework' )		=> 'elegant-blog modern-blog',
								)
		);

$avia_elements[] = array(
			'slug'		=> 'blog',
			'name'		=> __( 'Blog Layout', 'avia_framework' ),
			'desc'		=> __( 'Choose the default blog layout here.', 'avia_framework' ) . '<br/><br/>' . __( 'You can either choose a predefined layout or build your own blog layout with the advanced layout editor', 'avia_framework' ),
			'id'		=> 'blog_style',
			'type'		=> 'select',
			'std'		=> 'single-small',
			'no_first'	=> true,
			'subtype'	=> array(
							__( 'Multi Author Blog (displays Gravatar of the article author beside the entry and feature images above)', 'avia_framework' )	=> 'multi-big',
							__( 'Single Author, small preview Pic (no author picture is displayed, feature image is small)', 'avia_framework' )		=> 'single-small',
							__( 'Single Author, big preview Pic (no author picture is displayed, feature image is big)', 'avia_framework' )			=> 'single-big',
							__( 'Grid Layout', 'avia_framework' )																					=> 'blog-grid',
							__( 'List Layout - Simple (Title and meta information only)', 'avia_framework' )										=> 'bloglist-simple',
							__( 'List Layout - Compact (Title and icon only)', 'avia_framework' )													=> 'bloglist-compact',
							__( 'List Layout - Excerpt (Title, meta information and excerpt only)', 'avia_framework' )								=> 'bloglist-excerpt',
							__( 'Use the advance layout editor to build your own blog layout (simply edit the page you have chosen in Enfold->Theme Options as a blog page)', 'avia_framework' )	=> 'custom',
			)
		);


$avia_elements[] = array(
			'slug'          => 'blog',
			'type'          => 'visual_group_start',
			'id'            => 'avia_blog_post_options_start',
			'nodescription' => true
		);


$avia_elements[] =	array(
			'slug'		=> 'blog',
			'name'		=> __( 'Single Post Options', 'avia_framework' ),
			'desc'		=> __( 'Here you can set options that affect your single blog post layout', 'avia_framework' ),
			'id'		=> 'blog_widgetdescription',
			'type'		=> 'heading',
			'nodescription'	=> true
		);

$avia_elements[] = array(
			'slug'		=> 'blog',
			'name'		=> __( 'Single Post Navigation', 'avia_framework' ),
			'desc'		=> __( 'Select to disable or enable the post navigation that links to the next/previous post on single entries. Setting is also used for portfolio. Use filter avf_post_nav_settings to customize.', 'avia_framework' ),
			'id'		=> 'disable_post_nav',
			'type'		=> 'select',
			'no_first'	=> true,
			'std'		=> '',
			'subtype'	=> array(
								__( 'Enable post navigation', 'avia_framework' )	=> '',
								__( 'Disable post navigation', 'avia_framework' )	=> 'disable_post_nav',
								__( 'Loop post navigation', 'avia_framework' )		=> 'loop_post_nav'
							)
		);

$avia_elements[] =	array(
			'slug'		=> 'blog',
			'name'		=> __( 'Single Post Style', 'avia_framework' ),
			'desc'		=> __( 'Choose the single post style here.', 'avia_framework' ),
			'id'		=> 'single_post_style',
			'type'		=> 'select',
			'std'		=> 'single-big',
			'no_first'	=> true,
			'subtype'	=> array(
								__( 'Single post with small preview image (featured image)', 'avia_framework' )		=> 'single-small',
								__( 'Single post with big preview image (featured image)', 'avia_framework' )		=> 'single-big',
								__( 'Multi Author Blog (displays Gravatar of the article author beside the entry and feature images above)', 'avia_framework' )	=> 'multi-big'
							)
		);



$avia_elements[] =	array(
			'slug'		=> 'blog',
			'name'		=> __( 'Related Entries', 'avia_framework' ),
			'desc'		=> __( 'Choose if and how you want to display your related entries. (Related entries are based on tags. If a post does not have any tags then no related entries will be shown)', 'avia_framework' ),
			'id'		=> 'single_post_related_entries',
			'type'		=> 'select',
			'std'		=> 'av-related-style-tooltip',
			'no_first'	=> true,
			'subtype'	=> array(
							__( 'Show thumbnails and display post title by tooltip', 'avia_framework' )	=> 'av-related-style-tooltip',
							__( 'Show thumbnail and post title by default', 'avia_framework' )			=> 'av-related-style-full',
							__( 'Disable related entries', 'avia_framework' )							=> 'disabled'
						)
		);

$avia_elements[] =	array(
			'slug'		=> 'blog',
			'name'		=> __( 'Blog meta elements', 'avia_framework' ),
			'desc'		=> __( 'You can choose to hide some of the default Blog elements here:', 'avia_framework' ),
			'id'		=> 'blog_widgetdescription_meta',
			'type'		=> 'heading',
			'nodescription'	=> true
		);


$avia_elements[] = array(
			'slug'	=> 'blog',
			'name' 	=> __( 'Blog Post Author', 'avia_framework' ),
			'desc' 	=> __( 'Check to display', 'avia_framework' ),
			'id' 	=> 'blog-meta-author',
			'type' 	=> 'checkbox',
			'std'	=> 'true',
			'class' => 'av_3col av_col_1'
		);


$avia_elements[] = array(
			'slug'	=> 'blog',
			'name' 	=> __( 'Blog Post Comment Count', 'avia_framework' ),
			'desc' 	=> __( 'Check to display', 'avia_framework' ),
			'id' 	=> 'blog-meta-comments',
			'type' 	=> 'checkbox',
			'std'	=> 'true',
			'class' => 'av_3col av_col_2'
		);

$avia_elements[] = array(
			'slug'	=> 'blog',
			'name' 	=> __( 'Blog Post Category', 'avia_framework' ),
			'desc' 	=> __( 'Check to display', 'avia_framework' ),
			'id' 	=> 'blog-meta-category',
			'type' 	=> 'checkbox',
			'std'	=> 'true',
			'class' => 'av_3col av_col_2'
		);

$avia_elements[] = array(
			'slug'		=> 'blog',
			'name' 	=> __( 'Blog Post Date', 'avia_framework' ),
			'desc' 	=> __( 'Check to display', 'avia_framework' ),
			'id' 	=> 'blog-meta-date',
			'type' 	=> 'checkbox',
			'std'	=> 'true',
			'class' => 'av_3col av_col_1'
		);


$avia_elements[] = array(
			'slug'	=> 'blog',
			'name' 	=> __( 'Blog Post Allowed HTML Tags', 'avia_framework' ),
			'desc' 	=> __( 'Check to display', 'avia_framework' ),
			'id' 	=> 'blog-meta-html-info',
			'type' 	=> 'checkbox',
			'std'	=> 'true',
			'class' => 'av_3col av_col_2'
		);

$avia_elements[] = array(
			'slug'	=> 'blog',
			'name' 	=> __( 'Blog Post Tags', 'avia_framework' ),
			'desc' 	=> __( 'Check to display', 'avia_framework' ),
			'id' 	=> 'blog-meta-tag',
			'type' 	=> 'checkbox',
			'std'	=> 'true',
			'class' => 'av_3col av_col_3'
		);


$avia_elements[] = array(
			'slug'          => 'blog',
			'type'          => 'visual_group_end',
			'id'            => 'avia_blog_post_options_end',
			'nodescription' => true
		);

$avia_elements[] = array(
			'slug'          => 'blog',
			'type'          => 'visual_group_start',
			'id'            => 'avia_share_links_start',
			'nodescription' => true
		);



$desc = __( 'Check to display', 'avia_framework' );
$link = __( 'Link', 'avia_framework' );

if( ! empty( $avia_config['social_share_array'] ) )
{
	$avia_elements[] =	array(
				'slug'		=> 'blog',
				'name'		=> __( 'Share links at the bottom of your blog post', 'avia_framework' ),
				'desc'		=> __( 'The theme allows you to display share links to various social networks at the bottom of your blog posts. Check which links you want to display:', 'avia_framework' ),
				'id'		=> 'blog_social_share',
				'type'		=> 'heading',
				'nodescription'	=> true
			);

	$count = 0;

	foreach( $avia_config['social_share_array'] as $name => $id )
	{
		$classind = ( $count % 3 ) + 1;

		if( strlen( $name  ) > 15 )
		{
			if( 'mail' == $id )
			{
				$name = __( 'E-Mail', 'avia_framework' );
			}
			else
			{
				$name = ucfirst( $id ) . ' ' . $link;
			}
		}

		$avia_elements[] = array(
					'slug'	=> 'blog',
					'name' 	=> $name,
					'desc' 	=> $desc,
					'id' 	=> 'share_' . $id,
					'type' 	=> 'checkbox',
					'std'	=> '',
					'class' => 'av_3col av_col_' . $classind,

				);

		$count++;
	}
}

if( ! empty( $avia_config['social_profile_array'] ) )
{
	$prof_desc  = sprintf( __( 'If you added Social Profile Links at %s Theme Options -&gt; Social Profiles %s the theme allows you to display these profile links at the bottom of your blog posts.', 'avia_framework' ), '<a href="#goto_social" target="_blank">', '</a>' );
	$prof_desc .= __( 'Be sure you added a link, otherwise your selection will be ignored.', 'avia_framework' );

	$avia_elements[] =	array(
				'slug'		=> 'blog',
				'name'		=> __( 'Profile links at the bottom of your blog post', 'avia_framework' ),
				'desc'		=> $prof_desc,
				'id'		=> 'blog_social_profile',
				'type'		=> 'heading',
				'nodescription'	=> true
			);

	$count = 0;

	foreach( $avia_config['social_profile_array'] as $name => $id )
	{
		$classind = ( $count % 3 ) + 1;

		if( strlen( $name  ) > 15 )
		{
			$name = strtoupper( $id ) . ' ' . $link;
		}

		$avia_elements[] = array(
					'slug'	=> 'blog',
					'name' 	=> $name,
					'desc' 	=> $desc,
					'id' 	=> 'share_' . $id,
					'type' 	=> 'checkbox',
					'std'	=> '',
					'class' => 'av_3col av_col_' . $classind,

				);

		$count++;
	}

}


$avia_elements[] =	array(
			'slug'		=> 'blog',
			'name'		=> __( 'Share Button Bar Style', 'avia_framework' ),
			'desc'		=> __( 'Select how to display the share buttons bar', 'avia_framework' ),
			'id'		=> 'single_post_share_buttons_style',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
			'subtype'	=> array(
								__( 'Rectangular', 'avia_framework' )			=> '',
								__( 'Rectangular minimal', 'avia_framework' )	=> 'minimal',
								__( 'Block square', 'avia_framework' )			=> 'av-social-sharing-box-square',
								__( 'Rounded rectangular', 'avia_framework' )	=> 'av-social-sharing-box-rounded',
								__( 'Buttons', 'avia_framework' )				=> 'av-social-sharing-box-buttons',
								__( 'Circle', 'avia_framework' )				=> 'av-social-sharing-box-circle',
								__( 'Icon', 'avia_framework' )					=> 'av-social-sharing-box-icon',
								__( 'Icon simple', 'avia_framework' )			=> 'av-social-sharing-box-icon-simple',
						)
		);

$avia_elements[] =	array(
			'slug'		=> 'blog',
			'name'		=> __( 'Share Button Bar Alignment', 'avia_framework' ),
			'desc'		=> __( 'Select alignment of the share buttons bar', 'avia_framework' ),
			'id'		=> 'single_post_share_buttons_alignment',
			'type'		=> 'select',
			'std'		=> '',
			'no_first'	=> true,
			'required'	=> array( 'single_post_share_buttons_style', '{contains_array}av-social-sharing-box-square;av-social-sharing-box-circle;av-social-sharing-box-icon;av-social-sharing-box-icon-simple' ),
			'subtype'	=> array(
								__( 'Left', 'avia_framework' )		=> '',
								__( 'Centered', 'avia_framework' )	=> 'av-social-sharing-center',
								__( 'Right', 'avia_framework' )		=> 'av-social-sharing-right',
						)
		);

$avia_elements[] = array(
			'slug'          => 'blog',
			'type'          => 'visual_group_end',
			'id'            => 'avia_share_links_end',
			'nodescription' => true
		);



