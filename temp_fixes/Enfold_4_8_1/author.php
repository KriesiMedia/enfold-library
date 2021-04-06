<?php
	if( ! defined( 'ABSPATH' ) ){	die();	}
	
	global $avia_config, $more;

	/*
	* get_header is a basic wordpress function, used to retrieve the header.php file in your theme directory.
	*/
	get_header();

	$description = is_tag() ? tag_description() : category_description();
	$author_id = get_query_var( 'author' );
	
	/**
	 * Filter author data
	 * 
	 * @param string
	 * @param int $author_id
	 * @param string $context		added with 4.7.5.1
	 * @return string
	 */
	$name = apply_filters( 'avf_author_name', get_the_author_meta( 'display_name', $author_id ), $author_id, 'author.php' );
	
	$heading_s = __( 'Entries by', 'avia_framework' ) . ' ' . $name;
	
	echo avia_title( array( 'title' => avia_which_archive(), 'subtitle' => $description, 'link' => false ) );

	do_action( 'ava_after_main_title' );
	
	?>

		<div class='container_wrap container_wrap_first main_color <?php avia_layout_class( 'main' ); ?>'>

			<div class='container template-blog template-author '>

				<main class='content <?php avia_layout_class( 'content' ); ?> units' <?php avia_markup_helper( array( 'context' => 'content' ) );?>>

                    <div class='page-heading-container clearfix'>
                    <?php

                        get_template_part( 'includes/loop', 'about-author' );

                    ?>
                    </div>


                    <?php
                    echo "<h4 class='extra-mini-title widgettitle'>{$heading_s}</h4>";



                    /* Run the loop to output the posts.
                    * If you want to overload this in a child theme then include a file
                    * called loop-index.php and that will be used instead.
                    */


                    $more = 0;
                    get_template_part( 'includes/loop', 'author' );
                    ?>

				<!--end content-->
				</main>

				<?php

				//get the sidebar
				$avia_config['currently_viewing'] = 'blog';
				get_sidebar();

				?>

			</div><!--end container-->

		</div><!-- close default .container_wrap element -->

<?php 
	get_footer();
