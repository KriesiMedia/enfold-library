<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

/*
* Please do not modify this file on your child theme to add scripts to Footer (before closing of Body tag) section.
* Refer to this post - https://kriesi.at/documentation/enfold/add-custom-js-or-php-script/#add-a-script-to-footer-section and use functions.php file instead
*/

global $avia_config;

		/**
		 * Fixes a bug started with WP 5.8.1
		 */
		 if( ! isset( $the_id ) )
		 {
			 $the_id = avia_get_the_id();
		 }

		/**
		 * Fired before footer output starts
		 *
		 * @since ???
		 */
		do_action( 'ava_before_footer' );

		$blank = isset( $avia_config['template'] ) ? $avia_config['template'] : '';

		//	reset wordpress query in case we modified it
		wp_reset_query();

		//	get link to previous and next post/portfolio entry
		$avia_post_nav = avia_post_nav();

		//	set in header.php
		$footer_option = $avia_config['footer_option'];

		if( 'nofooterarea' != $footer_option && 'curtain_footer' == $avia_config['footer_behavior'] )
		{
			$data = is_numeric( $avia_config['footer_media'] ) ? " data-footer_max_height='{$avia_config['footer_media']}'" : '';

			echo '<div class="av-curtain-footer-container"' . $data . '>';
		}

		/**
		 * Fired after a possible footer container has been added and actual footer output starts
		 *
.		 * @since 4.8.6.3
		 */
		do_action( 'ava_before_footer_output' );

		/*
		 * Check if we should display a page content as footer
		 */
		if( ! $blank && in_array( $footer_option, array( 'page_in_footer_socket', 'page_in_footer' ) ) )
		{
			/**
			 * Allows 3rd parties to change page id's, e.g. translation plugins
			 */
			$post = AviaCustomPages()->get_custom_page_object( 'footer_page', '' );

			if( ( $post instanceof WP_Post ) && ( $post->ID != $the_id ) )
			{
				/**
				 * Make sure that footerpage is set to fullwidth
				 */
				$old_avia_config = $avia_config;

				$avia_config['layout']['current'] = array(
											'content'	=> 'av-content-full alpha',
											'sidebar'	=> 'hidden',
											'meta'		=> '',
											'entry'		=> '',
											'main'		=> 'fullsize'
										);

				$builder_stat = ( 'active' == Avia_Builder()->get_alb_builder_status( $post->ID ) );
				$avia_config['conditionals']['is_builder'] = $builder_stat;
				$avia_config['conditionals']['is_builder_template'] = $builder_stat;

				/**
				 * @used_by			config-bbpress\config.php
				 * @since 4.5.6.1
				 * @param WP_Post $post
				 * @param int $the_id
				 */
				do_action( 'ava_before_page_in_footer_compile', $post, $the_id );

				$content = Avia_Builder()->compile_post_content( $post );

				/**
				 * Remove leading <p> tag that is added by 'the_content' filter
				 * https://kriesi.at/support/topic/small-gutenburg-issue/
				 *
				 * @since 4.8.8
				 */
				$content = trim( $content );
				if( 0 === strpos( $content, '<p>' ) )
				{
					$content = substr( $content, 3 );
				}

				$avia_config = $old_avia_config;

				/**
				 * @since 4.7.4.1
				 * @param string
				 * @param WP_Post $post
				 * @param int $the_id
				 */
				$extra_class = apply_filters( 'avf_page_as_footer_extra_classes', 'footer-page-content footer_color', $post, $the_id );

				/**
				 * Wrap footer page in case we need extra CSS changes
				 *
				 * @since 4.7.4.1
				 */
				echo '<div class="' . $extra_class . '" id="footer-page">';
				echo	$content;
				echo '</div>';
			}
		}

		/**
		 * Check if we should display a footer
		 */
		if( ! $blank && $footer_option != 'nofooterarea' )
		{
			if( in_array( $footer_option, array( 'all', 'nosocket' ) ) )
			{
				//get columns
				$columns = avia_get_option('footer_columns');
		?>
				<div class='container_wrap footer_color' id='footer'>

					<div class='container'>

						<?php
						do_action('avia_before_footer_columns');

						//create the footer columns by iterating
				        switch( $columns )
				        {
				        	case 1:
								$class = '';
								break;
				        	case 2:
								$class = 'av_one_half';
								break;
				        	case 3:
								$class = 'av_one_third';
								break;
				        	case 4:
								$class = 'av_one_fourth';
								break;
				        	case 5:
								$class = 'av_one_fifth';
								break;
				        	case 6:
								$class = 'av_one_sixth';
								break;
							default:
								$class = '';
								break;
				        }

				        $firstCol = "first el_before_{$class}";

						//display the footer widget that was defined at appearenace->widgets in the wordpress backend
						//if no widget is defined display a dummy widget, located at the bottom of includes/register-widget-area.php
						for( $i = 1; $i <= $columns; $i++ )
						{
							$class2 = ''; // initialized to avoid php notices
							if( $i != 1 )
							{
								$class2 = " el_after_{$class} el_before_{$class}";
							}

							echo "<div class='flex_column {$class} {$class2} {$firstCol}'>";

							if( ! ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( 'Footer - Column ' . $i ) ) )
							{
								avia_dummy_widget( $i );
							}

							echo '</div>';

							$firstCol = '';
						}

						do_action( 'avia_after_footer_columns' );

	?>

					</div>

				<!-- ####### END FOOTER CONTAINER ####### -->
				</div>

	<?php   } //endif   array( 'all', 'nosocket' ) ?>


	<?php

			//copyright
			$copyright = do_shortcode( avia_get_option( 'copyright', '&copy; ' . __( 'Copyright', 'avia_framework' ) . "  - <a href='" . home_url( '/' ) . "'>" . get_bloginfo('name') . '</a>' ) );

			// you can filter and remove the backlink with an add_filter function
			// from your themes (or child themes) functions.php file if you dont want to edit this file
			// you can also remove the kriesi.at backlink by adding [nolink] to your custom copyright field in the admin area
			// you can also just keep that link. I really do appreciate it ;)
			$kriesi_at_backlink = kriesi_backlink( get_option( THEMENAMECLEAN . "_initial_version" ), 'Enfold' );


			if( $copyright && strpos( $copyright, '[nolink]' ) !== false )
			{
				$kriesi_at_backlink = '';
				$copyright = str_replace( '[nolink]', '', $copyright );
			}

			/**
			 * @since 4.5.7.2
			 * @param string $copyright
			 * @param string $copyright_option
			 * @return string
			 */
			$copyright_option = avia_get_option( 'copyright' );
			$copyright = apply_filters( 'avf_copyright_info', $copyright, $copyright_option );

			if( in_array( $footer_option, array( 'all', 'nofooterwidgets', 'page_in_footer_socket' ) ) )
			{

			?>

				<footer class='container_wrap socket_color' id='socket' <?php avia_markup_helper( array( 'context' => 'footer' ) ); ?>>
                    <div class='container'>

                        <span class='copyright'><?php echo $copyright . $kriesi_at_backlink; ?></span>

                        <?php
                        	if( avia_get_option( 'footer_social', 'disabled' ) != 'disabled' )
                            {
                            	$social_args = array( 'outside'=>'ul', 'inside'=>'li', 'append' => '' );
								echo avia_social_media_icons( $social_args, false );
                            }

							$avia_theme_location = 'avia3';
							$avia_menu_class = $avia_theme_location . '-menu';

							$args = array(
										'theme_location'	=> $avia_theme_location,
										'menu_id'			=> $avia_menu_class,
										'container_class'	=> $avia_menu_class,
										'items_wrap'        => '<ul role="menu" class="%2$s" id="%1$s">%3$s</ul>',
										'fallback_cb'		=> '',
										'depth'				=> 1,
										'echo'				=> false,
										'walker'			=> new avia_responsive_mega_menu( array( 'megamenu' => 'disabled' ) )
									);

                            $menu = wp_nav_menu( $args );

                            if( $menu )
							{
								echo "<nav class='sub_menu_socket' " . avia_markup_helper( array( 'context' => 'nav', 'echo' => false ) ) . '>';
								echo	$menu;
								echo '</nav>';
							}
                        ?>

                    </div>

	            <!-- ####### END SOCKET CONTAINER ####### -->
				</footer>


			<?php
			} //end nosocket check - array( 'all', 'nofooterwidgets', 'page_in_footer_socket' )

		} //end blank & nofooterarea check

		/**
		 * Fired before a possible footer container is closed
		 *
.		 * @since 4.8.6.3
		 */
		do_action( 'ava_after_footer_output' );

		if( 'nofooterarea' != $footer_option && 'curtain_footer' == $avia_config['footer_behavior'] )
		{
			echo '</div>';	//	class="av-curtain-footer-container"
		}

		?>
		<!-- end main -->
		</div>

		<?php

		/**
		 * Fired after footer container is closed
		 *
.		 * @since 4.8.6.3
		 */
		do_action( 'ava_after_footer' );

		//display link to previous and next portfolio entry
		echo	$avia_post_nav;

		echo "<!-- end wrap_all --></div>";


		if( isset( $avia_config['fullscreen_image'] ) )
		{ ?>
			<!--[if lte IE 8]>
			<style type="text/css">
			.bg_container {
			-ms-filter:"progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $avia_config['fullscreen_image']; ?>', sizingMethod='scale')";
			filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $avia_config['fullscreen_image']; ?>', sizingMethod='scale');
			}
			</style>
			<![endif]-->
		<?php
			echo "<div class='bg_container' style='background-image:url(" . $avia_config['fullscreen_image'] . ");'></div>";
		}
	?>


<a href='#top' title='<?php _e( 'Scroll to top', 'avia_framework' ); ?>' id='scroll-top-link' <?php echo av_icon_string( 'scrolltop' ); ?> tabindex='-1'><span class="avia_hidden_link_text"><?php _e( 'Scroll to top', 'avia_framework' ); ?></span></a>

<div id="fb-root"></div>

<?php

	/* Always have wp_footer() just before the closing </body>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to reference JavaScript files.
	 */
	wp_footer();

?>
</body>
</html>
