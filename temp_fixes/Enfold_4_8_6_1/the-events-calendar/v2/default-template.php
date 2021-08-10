<?php

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


global $avia_config;
use Tribe\Events\Views\V2\Template_Bootstrap;
	/*
	 * get_header is a basic wordpress function, used to retrieve the header.php file in your theme directory.
	 */
	 get_header();

	 $title = tribe_is_month() ? __( 'Calendar of Events', 'avia_framework' ) : tribe_get_events_title( false );
 	 $args = array(
				'title'	=> $title,
				'link'	=> ''
			);

 	 if( ! is_singular() || get_post_meta( get_the_ID(), 'header', true ) != 'no' )
	 {
		 echo avia_title( $args );
	 }

 	 do_action( 'ava_after_main_title' );

	 ?>

		<div class='container_wrap container_wrap_first main_color fullsize'>

			<div class='container'>

				<main class='template-page template-event-page content av-content-full units' <?php avia_markup_helper(array('context' => 'content','post_type'=>'page'));?>>

					 <div id="tribe-events-pg-template">

                 	<?php

					echo tribe( Template_Bootstrap::class )->get_view_html();

					?>

					</div> <!-- #tribe-events-pg-template -->

				<!--end content-->
				</main>

			</div><!--end container-->

		</div><!-- close default .container_wrap element -->

<?php
		get_footer();
