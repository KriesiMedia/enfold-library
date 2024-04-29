<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $avia_config, $post_loop_count;


if( 'disable_blog' == avia_get_option( 'disable_blog' ) )
{
	if( current_user_can( 'edit_posts' ) )
	{
		$msg  = '<strong>' . __( 'Admin notice for:', 'avia_framework' ) . '</strong><br>';
		$msg .= __( 'Blog Posts', 'avia_framework' ) . '<br><br>';
		$msg .= __( 'This element was disabled in your theme settings. You can activate it here:', 'avia_framework' ) . '<br>';
		$msg .= '<a target="_blank" href="' . admin_url( 'admin.php?page=avia#goto_performance') . '">' . __( 'Performance Settings', 'avia_framework' ) . '</a>';

		$content = "<span class='av-shortcode-disabled-notice'>{$msg}</span>";

		echo $content;
	}

	 return;
}


if( empty( $post_loop_count ) )
{
	$post_loop_count = 1;
}

$blog_style = ! empty( $avia_config['blog_style'] ) ? $avia_config['blog_style'] : avia_get_option( 'blog_style', 'multi-big' );

if( is_single() )
{
	$blog_style = avia_get_option( 'single_post_style', 'single-big' );
}

$blog_global_style = avia_get_option( 'blog_global_style', '' ); //alt: elegant-blog

$initial_id = avia_get_the_ID();

// check if we got posts to display:
if( have_posts() )
{
	while( have_posts() )
	{
		the_post();

		/**
		 * get the current post id, the current post class and current post format
		 */
		$url = '';
		$current_post = array();
		$current_post['post_loop_count'] = $post_loop_count;
		$current_post['the_id'] = get_the_ID();
		$current_post['parity'] = $post_loop_count % 2 ? 'odd' : 'even';
		$current_post['last'] = count( $wp_query->posts ) == $post_loop_count ? ' post-entry-last ' : '';
		$current_post['post_type'] = get_post_type( $current_post['the_id'] );
		$current_post['post_class'] = "post-entry-{$current_post['the_id']} post-loop-{$post_loop_count} post-parity-{$current_post['parity']} {$current_post['last']} {$blog_style}";
		$current_post['post_class'] .= ( $current_post['post_type'] == 'post' ) ? '' : ' post';
		$current_post['post_format'] = get_post_format() ? get_post_format() : 'standard';
		$current_post['post_layout'] = avia_layout_class( 'main', false );
		$blog_content = ! empty( $avia_config['blog_content'] ) ? $avia_config['blog_content'] : 'content';

		/**
		 * If post uses builder we must change content to excerpt on overview pages to avoid circular calling of shortcodes when used e.g. in ALB blog element
		 *
		 * @since 5.3			extended to check for all ALB supported post types
		 */
		if( 'active' == Avia_Builder()->get_alb_builder_status( $current_post['the_id'] ) && ! is_singular( $current_post['the_id'] ) && in_array( $current_post['post_type'], Avia_Builder()->get_supported_post_types() ) )
		{
		   $current_post['post_format'] = 'standard';
		   $blog_content = 'excerpt_read_more';
		}

		/**
		 * Allows especially for ALB posts to change output to 'content'
		 * Supported since 4.5.5
		 *
		 * @since 4.5.5
		 * @param string $blog_content
		 * @param array $current_post
		 * @param string $blog_style
		 * @param string $blog_global_style
		 * @return string
		 */
		$blog_content = apply_filters( 'avf_blog_content_in_loop', $blog_content, $current_post, $blog_style, $blog_global_style );


		/*
		 * retrieve slider, title and content for this post,...
		 */
		$size = strpos( $blog_style, 'big' ) ? ( ( strpos( $current_post['post_layout'], 'sidebar' ) !== false ) ? 'entry_with_sidebar' : 'entry_without_sidebar' ) : 'square';

		if( ! empty( $avia_config['preview_mode'] ) && ! empty( $avia_config['image_size'] ) && $avia_config['preview_mode'] == 'custom' )
		{
			$size = $avia_config['image_size'];
		}

		/**
		 * Change default theme image
		 *
		 * @since 4.5.4
		 * @param string $image_link
		 * @param array $current_post
		 * @param string $size
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

		$current_post['title'] = get_the_title();

		/**
		 * Allow 3rd party to hook and return a plugin specific content.
		 * This returned content replaces Enfold's standard content building procedure.
		 *
		 * @since 4.5.7.2
		 * @param string
		 * @param string $context
		 * @return string
		 */
		$current_post['content'] = apply_filters( 'avf_the_content', '', 'loop_index' );
		if( '' == $current_post['content'] )
		{
			$current_post['content'] = $blog_content == 'content' ? get_the_content( __( 'Read more', 'avia_framework' ) . '<span class="more-link-arrow"></span>' ) : get_the_excerpt();
			$current_post['content'] = $blog_content == 'excerpt_read_more' ? $current_post['content'] . '<div class="read-more-link"><a href="' . get_permalink() . '" class="more-link">' . __( 'Read more', 'avia_framework' ) . '<span class="more-link-arrow"></span></a></div>' : $current_post['content'];
			$current_post['before_content'] = '';

			/*
			 * ...now apply a filter, based on the post type... (filter function is located in includes/helper-post-format.php)
			 */
			$current_post = apply_filters( 'post-format-' . $current_post['post_format'], $current_post );
			$with_slider = empty( $current_post['slider'] ) ? '' : 'with-slider';

			/*
			 * ... last apply the default wordpress filters to the content
			 */
			$current_post['content'] = str_replace( ']]>', ']]&gt;', apply_filters( 'the_content', $current_post['content'] ) );
		}

		/*
		 * Now extract the variables so that $current_post['slider'] becomes $slider, $current_post['title'] becomes $title, etc
		 */
		extract( $current_post );


		/**
		 * render the html:
		 */
		echo '<article class="' . implode( ' ', get_post_class( "post-entry post-entry-type-{$post_format} {$post_class} {$with_slider}" ) ) . '" ' . avia_markup_helper( array( 'context' => 'entry', 'echo' => false ) ) . '>';

		//	default link for preview images
		$link = ! empty( $url ) ? $url : get_permalink();

		//	prepare responsive lightbox images
		$link_lightbox = false;
		$lightbox_attr = '';

		/**
		 * Allows to ignore any link setting on featured image
		 * @link https://kriesi.at/support/topic/remove-featured-image-expand-in-posts/
		 *
		 * @since 5.7.1
		 * @param boolean $ignore_image_links
		 * @param array $current_post
		 * @return boolean
		 */
		$ignore_image_links = apply_filters( 'avf_post_ingnore_featured_image_link', false, $current_post );

		//preview image description
		$desc = '';
		$thumb_post = get_post( get_post_thumbnail_id() );
		if( $thumb_post instanceof WP_Post )
		{
			if( '' != trim( $thumb_post->post_excerpt ) )
			{
				//	return 'Caption' from media gallery
				$desc = $thumb_post->post_excerpt;
			}
			else if( '' != trim( $thumb_post->post_title ) )
			{
				//	return 'Title' from media gallery
				$desc = $thumb_post->post_title;
			}
			else if( '' != trim( $thumb_post->post_content ) )
			{
				//	return 'Description' from media gallery
				$desc = $thumb_post->post_content;
			}
		}

		$desc = trim( $desc );
		if( '' == $desc )
		{
			$desc = trim( the_title_attribute( 'echo=0' ) );
		}

		/**
		 * Allows to change the title attribute text for the featured image.
		 * If '' is returned, then no title attribute is added.
		 *
		 * @since 4.6.4
		 * @param string $desc
		 * @param string $context				'loop_index'
		 * @param WP_Post $thumb_post
		 */
		$featured_img_title = apply_filters( 'avf_featured_image_title_attr', $desc, 'loop_index', $thumb_post );

		$featured_img_title = '' != trim( $featured_img_title ) ? ' title="' . esc_attr( $featured_img_title ) . '" ' : '';

		//on single page replace the link with a fullscreen image
		if( is_singular() )
		{
			if( ! $thumb_post instanceof WP_Post )
			{
				$link = '';
			}
			else
			{
				$link = avia_image_by_id( $thumb_post->ID, 'large', 'url' );

				$lightbox_img = AviaHelper::get_url( 'lightbox', $thumb_post->ID, true );
				$lightbox_attr = Av_Responsive_Images()->html_attr_image_src( $lightbox_img, false );
				$link_lightbox = true;
			}
		}

		if( ! in_array( $blog_style, array( 'bloglist-simple', 'bloglist-compact', 'bloglist-excerpt' ) ) )
		{
			//echo preview image
			if( strpos( $blog_global_style, 'elegant-blog' ) === false )
			{
				if( strpos( $blog_style, 'big' ) !== false )
				{
					if( $slider && false === $ignore_image_links )
					{
						if( $link_lightbox )
						{
							$slider = '<a ' . $lightbox_attr . ' ' . $featured_img_title . '>' . $slider . '</a>';
						}
						else
						{
							$slider = '<a href="' . $link . '" ' . $featured_img_title . '>' . $slider . '</a>';
						}
					}

					if( $slider )
					{
						echo '<div class="big-preview ' . $blog_style . '" ' . avia_markup_helper( array( 'context' => 'image', 'echo' => false ) ) . '>' . $slider . '</div>';
					}
				}

				if( ! empty( $before_content ) )
				{
					echo '<div class="big-preview ' . $blog_style . '">' . $before_content . '</div>';
				}
			}
		}

		echo '<div class="blog-meta">';

			$blog_meta_output = '';
			$icon = '<span class="iconfont" ' . av_icon_string( $post_format ) . '></span>';

			if( strpos( $blog_style, 'multi' ) !== false )
			{
				$gravatar = '';
				$pf_link = get_post_format_link( $post_format );

				if( $post_format == 'standard' )
				{
					$author_name = apply_filters( 'avf_author_name', get_the_author_meta( 'display_name', $post->post_author ), $post->post_author );
					$author_email = apply_filters( 'avf_author_email', get_the_author_meta('email', $post->post_author), $post->post_author );

					$gravatar_alt = esc_html( $author_name );
					$gravatar = get_avatar( $author_email, '81', 'blank', $gravatar_alt );
					$pf_link = get_author_posts_url( $post->post_author );
				}

				$blog_meta_output = "<a href='{$pf_link}' class='post-author-format-type'><span class='rounded-container'>" . $gravatar . $icon . '</span></a>';
			}
			else if( strpos( $blog_style, 'small' ) !== false )
			{
				if( false === $ignore_image_links )
				{
					if( $link_lightbox )
					{
						$attr = $lightbox_attr;
					}
					else
					{
						$attr = "href='{$link}'";
					}

					$blog_meta_output = "<a {$attr} class='small-preview' {$featured_img_title} " . avia_markup_helper( array( 'context' => 'image', 'echo' => false ) ). ">{$slider}{$icon}</a>";
				}
				else
				{
					$blog_meta_output = "<div class='small-preview no-image-link' {$featured_img_title} " . avia_markup_helper( array( 'context' => 'image', 'echo' => false ) ). ">{$slider}</div>";
				}
			}

			echo apply_filters( 'avf_loop_index_blog_meta', $blog_meta_output );

		echo '</div>';

		echo "<div class='entry-content-wrapper clearfix {$post_format}-content'>";
			echo '<header class="entry-content-header">';

				if( $blog_style == 'bloglist-compact' )
				{
					$format = get_post_format();
					echo '<span class="fallback-post-type-icon" ' . av_icon_string( $format ) . '></span>';
				}

				$close_header = '</header>';

				$content_output  = '<div class="entry-content" ' . avia_markup_helper( array( 'context' => 'entry_content', 'echo' => false ) ) . '>';
				$content_output .=		$content;
				$content_output .= '</div>';


				$taxonomies = get_object_taxonomies( get_post_type( $the_id ) );
				$cats = '';

				$excluded_taxonomies = array_merge( get_taxonomies( array( 'public' => false ) ), array( 'post_tag', 'post_format' ) );

				/**
				 *
				 * @since ????
				 * @since 4.8.8						added $context
				 * @param array $excluded_taxonomies
				 * @param string $post_type
				 * @param int $the_id
				 * @param string $context
				 * @return array
				 */
				$excluded_taxonomies = apply_filters( 'avf_exclude_taxonomies', $excluded_taxonomies, get_post_type( $the_id ), $the_id, 'loop-index' );

				if( ! empty( $taxonomies ) )
				{
					foreach( $taxonomies as $taxonomy )
					{
						if( ! in_array( $taxonomy, $excluded_taxonomies ) )
						{
							$cats .= get_the_term_list( $the_id, $taxonomy, '', ', ', '' ) . ' ';
						}
					}
				}

				//elegant blog
				//prev: if( $blog_global_style == 'elegant-blog' )
				if( strpos( $blog_global_style, 'elegant-blog' ) !== false )
				{
					$cat_output = '';

					if( ! empty( $cats ) )
					{
						$cat_output .= '<span class="blog-categories minor-meta">';
						$cat_output .=		trim( $cats );
						$cat_output .= '</span>';
						$cats = '';
					}

					if ( in_array( $blog_style, array( 'bloglist-compact', 'bloglist-excerpt' ) ) )
					{
						echo $title;
					}
					else
					{
						// The wrapper div prevents the Safari reader from displaying the content twice  ¯\_(ツ)_/¯
						echo '<div class="av-heading-wrapper">';

						if( strpos( $blog_global_style, 'modern-blog' ) === false )
						{
							echo $cat_output . $title;
						}
						else
						{
							echo $title . $cat_output;
						}

						echo '</div>';
					}

					echo $close_header;
					$close_header = '';

					if( ! in_array( $blog_style, array( 'bloglist-simple', 'bloglist-compact', 'bloglist-excerpt' ) ) )
					{
						echo '<span class="av-vertical-delimiter"></span>';

						//echo preview image
						if( strpos( $blog_style, 'big' ) !== false )
						{
							if( $slider && false === $ignore_image_links )
							{
								if( $link_lightbox )
								{
									$slider = '<a ' . $lightbox_attr . ' ' . $featured_img_title . '>' . $slider . '</a>';
								}
								else
								{
									$slider = '<a href="' . $link . '" ' . $featured_img_title . '>' . $slider . '</a>';
								}
							}

							if( $slider )
							{
								echo '<div class="big-preview ' . $blog_style . '" ' . avia_markup_helper( array( 'context' => 'image', 'echo' => false ) ) . '>' . $slider . '</div>';
							}
						}

						if( ! empty( $before_content ) )
						{
							echo '<div class="big-preview ' . $blog_style . '">' . $before_content . '</div>';
						}

						echo $content_output;
					}

					$cats = '';
					$title = '';
					$content_output = '';
				}

				echo $title;

				if( $blog_style !== 'bloglist-compact' )
				{
					echo '<span class="post-meta-infos">';

						$meta_info = array();

						/**
						 * @since 4.8.8
						 * @param string $hide_meta_only
						 * @param string $context
						 * @return string
						 */
						$meta_seperator = apply_filters( 'avf_post_metadata_seperator', '<span class="text-sep">/</span>', 'loop-index' );

						if( 'blog-meta-date' == avia_get_option( 'blog-meta-date' ) )
						{
							$meta_time  = '<time class="date-container minor-meta updated" ' . avia_markup_helper( array( 'context' => 'entry_time', 'echo' => false ) ) . '>';

							/**
							 * Modify date displayed for meta data of blog
							 *
							 * @used_by                enfold\config-events-calendar\config.php  avia_events_modify_event_publish_date()    10
							 * @since 5.3
							 * @param string $published_time
							 * @param int $current_post['the_id']
							 * @param string $date_format
							 * @return string
							 */
							$meta_time .=		apply_filters( 'avf_loop_index_meta_time', get_the_time( get_option( 'date_format' ) ), $current_post['the_id'], get_option( 'date_format' ) );

							$meta_time .= '</time>';

							$meta_info['date'] = $meta_time;
						}

						if( 'blog-meta-comments' == avia_get_option( 'blog-meta-comments' ) )
						{
							if( get_comments_number() != '0' || comments_open() )
							{
								$meta_comment = '<span class="comment-container minor-meta">';

								ob_start();
								comments_popup_link(
												"0 " . __( 'Comments', 'avia_framework' ),
												"1 " . __( 'Comment' , 'avia_framework' ),
												"% " . __( 'Comments', 'avia_framework' ),
												'comments-link',
												__( 'Comments Disabled', 'avia_framework' )
											);

								$meta_comment .= ob_get_clean();
								$meta_comment .= '</span>';

								$meta_info['comment'] = $meta_comment;
							}
						}

						if( 'blog-meta-category' == avia_get_option( 'blog-meta-category' ) )
						{
							if( ! empty( $cats ) )
							{
								$meta_cats  = '<span class="blog-categories minor-meta">' . __( 'in', 'avia_framework') . ' ';
								$meta_cats .=	trim( $cats );
								$meta_cats .= '</span>';

								$meta_info['categories'] = $meta_cats;
							}
						}

						/**
						 * Allow to change theme options setting for certain posts
						 *
						 * @since 4.8.8
						 * @param boolean $show_author_meta
						 * @param string $context
						 * @return boolean
						 */
						if( true === apply_filters( 'avf_show_author_meta', 'blog-meta-author' == avia_get_option( 'blog-meta-author' ), 'loop-index' ) )
						{
							$meta_author  = '<span class="blog-author minor-meta">' . __( 'by', 'avia_framework' ) . ' ';
							$meta_author .=		'<span class="entry-author-link" ' . avia_markup_helper( array( 'context' => 'author_name', 'echo' => false ) ) . '>';
							$meta_author .=			'<span class="author">';
							$meta_author .=				'<span class="fn">';
							$meta_author .=					get_the_author_posts_link();
							$meta_author .=				'</span>';
							$meta_author .=			'</span>';
							$meta_author .=		'</span>';
							$meta_author .= '</span>';

							$meta_info['author'] = $meta_author;
						}

						if( ! is_single() )
						{
							/**
							 * Allow to show/hide tags meta data of post. Overrule default behaviour prior 5.4
							 *
							 * @since 5.4
							 * @param boolean $show_meta_tags
							 * @param string $context
							 * @return boolean
							 */
							$show_meta_tags = apply_filters( 'avf_show_tags_meta', false, 'loop-index' );

							if( $show_meta_tags && has_tag() )
							{
								$meta_tags  = '<span class="blog-tags-header minor-meta">';
								$meta_tags .=		get_the_tag_list( __( 'Tags:', 'avia_framework' ) . ' <span> ', ', ', '</span>' );
								$meta_tags .= '</span>';

								$meta_info['tags'] = $meta_tags;
							}
						}

						/**
						 * Modify the post metadata array
						 *
						 * @since 4.8.8
						 * @param array $meta_info
						 * @param string $context
						 * @return array
						 */
						$meta_info = apply_filters( 'avf_post_metadata_array', $meta_info, 'loop-index' );

						echo implode( $meta_seperator, $meta_info );

						if( $blog_style == 'bloglist-simple' )
						{
							echo '<div class="read-more-link"><a href="' . get_permalink() . '" class="more-link">' . __( 'Read more', 'avia_framework' ) . '<span class="more-link-arrow"></span></a></div>';
						}

					echo '</span>';

				} // display meta-infos on all layouts except bloglist-compact

				echo $close_header;


				// echo the post content
				if ( $blog_style == 'bloglist-excerpt' )
				{
					the_excerpt();

					echo '<div class="read-more-link">';
					echo	'<a href="' . get_permalink() . '" class="more-link">' . __( 'Read more', 'avia_framework' );
					echo		'<span class="more-link-arrow"></span>';
					echo	'</a>';
					echo '</div>';
				}

				if ( ! in_array( $blog_style, array( 'bloglist-simple', 'bloglist-compact', 'bloglist-excerpt' ) ) )
				{
					echo $content_output;
				}

				echo '<footer class="entry-footer">';

					$avia_wp_link_pages_args = apply_filters( 'avf_wp_link_pages_args', array(
																		'before'	=> '<nav class="pagination_split_post">' . __( 'Pages:', 'avia_framework' ),
																		'after'		=> '</nav>',
																		'pagelink'	=> '<span>%</span>',
																		'separator'	=> ' ',
																	) );

					wp_link_pages( $avia_wp_link_pages_args );

					if( is_single() && ! post_password_required() )
					{
						//tags on single post
						if( 'blog-meta-tag' == avia_get_option( 'blog-meta-tag' ) && has_tag() )
						{
							echo '<span class="blog-tags minor-meta">';
									the_tags( '<strong>' . __( 'Tags:', 'avia_framework' ) . '</strong><span> ' );
							echo '</span></span>';
						}

						//share links on single post
						avia_social_share_links_single_post();
					}

					do_action( 'ava_after_content', $the_id, 'post' );

			echo '</footer>';

			echo "<div class='post_delimiter'></div>";

		echo '</div>';

		echo '<div class="post_author_timeline"></div>';
		echo av_blog_entry_markup_helper( $current_post['the_id'] );

		echo '</article>';

		$post_loop_count++;
	}
}
else
{
	$default_heading = 'h1';
	$args = array(
				'heading'		=> $default_heading,
				'extra_class'	=> ''
			);

	/**
	 * @since 4.5.5
	 * @return array
	 */
	$args = apply_filters( 'avf_customize_heading_settings', $args, 'loop_index::nothing_found', array() );

	$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
	$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';
?>
	<article class="entry">
		<header class="entry-content-header">
<?php
			echo "<{$heading} class='post-title entry-title {$css}'>" . __( 'Nothing Found', 'avia_framework' ) . "</{$heading}>";
?>
		</header>

		<p class="entry-content" <?php avia_markup_helper( array( 'context' => 'entry_content' ) ); ?>><?php _e( 'Sorry, no posts matched your criteria', 'avia_framework' ); ?></p>

		<footer class="entry-footer"></footer>
	</article>

<?php
}

if( empty( $avia_config['remove_pagination'] ) )
{
	echo "<div class='{$blog_style}'>" . avia_pagination( '', 'nav' ) . '</div>';
}

