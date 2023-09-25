<?php
	if( ! defined( 'ABSPATH' ) ){ die(); }

	global $avia_config, $more;

	/**
	 * get_header is a basic wordpress function, used to retrieve the header.php file in your theme directory.
	 */
	get_header();

	$title = __( 'Blog - Latest News', 'avia_framework' ); //default blog title
	$t_link = home_url( '/' );
	$t_sub = '';

	if( avia_get_option( 'frontpage' ) && $blogpage_id = avia_get_option( 'blogpage' ) )
	{
		$title 	= get_the_title( $blogpage_id ); //if the blog is attached to a page use this title
		$t_link = get_permalink( $blogpage_id );
		$t_sub =  avia_post_meta( $blogpage_id, 'subtitle' );
	}

	if( ! empty( $blogpage_id ) && get_post_meta( $blogpage_id, 'header', true ) != 'no' )
	{
		echo avia_title( array( 'heading' => 'strong', 'title' => $title, 'link' => $t_link, 'subtitle' => $t_sub ) );
	}

	do_action( 'ava_after_main_title' );

	/**
	 * @since 5.6.7
	 * @param string $main_class
	 * @param string $context					file name
	 * @return string
	 */
	$main_class = apply_filters( 'avf_custom_main_classes', 'av-main-' . basename( __FILE__, '.php' ), basename( __FILE__ ) );

	?>

		<div class='container_wrap container_wrap_first main_color <?php avia_layout_class( 'main' ); ?> <?php echo avia_blog_class_string(); ?>'>

			<div class='container template-blog '>

				<main class='content <?php avia_layout_class( 'content' ); ?> units <?php echo $main_class; ?>' <?php avia_markup_helper( array( 'context' => 'content' ) );?>>

                    <?php

					$avia_config['blog_style'] = apply_filters( 'avf_blog_style', avia_get_option( 'blog_style','multi-big' ), 'blog' );
					if( $avia_config['blog_style'] == 'blog-grid' )
					{
						$atts = array(
									'type'		=> 'grid',
									'items'		=> get_option( 'posts_per_page' ),
									'columns'	=> 3,
									'class'		=> 'avia-builder-el-no-sibling',
									'paginate'	=> 'yes'
								);

						/**
						 * @since 4.5.5
						 * @return array
						 */
						$atts = apply_filters( 'avf_post_slider_args', $atts, 'index' );

						$blog = new avia_post_slider( $atts );
						$blog->query_entries();
						echo '<div class="entry-content-wrapper">' . $blog->html() . '</div>';
					}
					else
					{
						/* Run the loop to output the posts.
						* If you want to overload this in a child theme then include a file
						* called loop-index.php and that will be used instead.
						*/
						$more = 0;
						get_template_part( 'includes/loop', 'index' );
					}
                    ?>

				<!--end content-->
				</main>

				<?php
				wp_reset_query();

				//get the sidebar
				$avia_config['currently_viewing'] = 'blog';
				if( is_front_page() )
				{
					$avia_config['currently_viewing'] = 'frontpage';
				}

				get_sidebar();

				?>

			</div><!--end container-->

		</div><!-- close default .container_wrap element -->

<?php
		get_footer();
