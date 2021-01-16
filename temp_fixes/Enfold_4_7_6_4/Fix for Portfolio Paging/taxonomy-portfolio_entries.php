<?php
	if ( ! defined('ABSPATH') ){ die(); }
	
	global $avia_config;

	/*
	 * get_header is a basic wordpress function, used to retrieve the header.php file in your theme directory.
	 */
	 get_header();

 	 echo avia_title( array( 'title' => avia_which_archive() ) );
 	 
 	 do_action( 'ava_after_main_title' );
	 ?>

		<div class='container_wrap container_wrap_first main_color <?php avia_layout_class( 'main' );?>'>

			<div class='container'>

				<main class='template-page template-portfolio content  <?php avia_layout_class( 'content' ); ?> units' <?php avia_markup_helper( array( 'context' => 'content', 'post_type' => 'portfolio' ) );?>>

                    <div class="entry-content-wrapper clearfix">

                        <div class="category-term-description">
                            <?php echo term_description(); ?>
                        </div>

                    <?php

						$grid = new avia_post_grid( 
										array(	
											'linking'			=> '',
											'columns'			=> '3',
											'contents'			=> 'title',
											'sort'				=> 'no',
											'paginate'			=> 'yes',
											'set_breadcrumb'	=> false,
										));

						$grid->use_global_query();
						echo $grid->html( '' );

                    ?>
                    </div>

                <!--end content-->
                </main>
				<?php

				//get the sidebar
				$avia_config['currently_viewing'] = 'portfolio';
				get_sidebar();

				?>

			</div><!--end container-->

		</div><!-- close default .container_wrap element -->

<?php 
	get_footer();
		
		