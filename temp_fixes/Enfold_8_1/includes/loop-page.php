<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


global $avia_config, $post_loop_count;

$post_loop_count = 1;
$post_class = 'post-entry-' . avia_get_the_id();

$more_link_arrow = class_exists( 'avia_font_manager', false ) ? avia_font_manager::html_more_link_arrow() : '<span class="more-link-arrow"></span>';


// check if we got posts to display:
if( have_posts() )
{
	while( have_posts() )
	{
		the_post();

		$aria_label = 'aria-label="' . __( 'Page Content for:', 'avia_framework' ) . ' ' . esc_attr( get_the_title() ) . '"';

		/**
		 * @since 6.0.3
		 * @param string $aria_label
		 * @param string $context
		 * @param WP_Post|null $current_post
		 * @return string
		 */
		$aria_label = apply_filters( 'avf_aria_label_for_header', $aria_label, __FILE__, get_post() );

?>
		<article class='post-entry post-entry-type-page <?php echo $post_class; ?>' <?php echo $aria_label; ?> <?php avia_markup_helper( array( 'context' => 'entry' ) ); ?>>

			<div class="entry-content-wrapper clearfix">
                <?php
				echo '<header class="entry-content-header">';

					$thumb = get_the_post_thumbnail( get_the_ID(), $avia_config['size'] );

					if( $thumb )
					{
						echo "<div class='page-thumb'>{$thumb}</div>";
					}

				echo '</header>';

				//display the actual post content
				echo '<div class="entry-content" '.avia_markup_helper( array( 'context' => 'entry_content','echo' => false ) ) . '>';
					the_content( __( 'Read more', 'avia_framework' ) . $more_link_arrow );
				echo '</div>';

				echo '<footer class="entry-footer">';

					wp_link_pages( array(
									'before'	=> '<div class="pagination_split_post">',
									'after'		=> '</div>',
									'pagelink'	=> '<span>%</span>'
								));

				echo '</footer>';

				do_action( 'ava_after_content', get_the_ID(), 'page' );
                ?>
			</div>

		</article><!--end post-entry-->


<?php

		/**
		 * If comments are open or we have at least one comment, load up the comment template.
		 *
		 * @since 4.7.5.1
		 */
		if( comments_open() || get_comments_number() > 0 )
		{
			comments_template();
		}

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
	$args = apply_filters( 'avf_customize_heading_settings', $args, 'loop_page::nothing_found', array() );

	$heading = ! empty( $args['heading'] ) ? $args['heading'] : $default_heading;
	$css = ! empty( $args['extra_class'] ) ? $args['extra_class'] : '';

	$aria_label = 'aria-label="' . __( 'No Page Found', 'avia_framework' ) . '"';

	/**
	 * @since 6.0.3
	 * @param string $aria_label
	 * @param string $context
	 * @param array $nothing_found
	 * @return string
	 */
	$aria_label = apply_filters( 'avf_aria_label_for_header', $aria_label, __FILE__, [] );

?>

    <article class="entry" <?php echo $aria_label; ?>>
        <header class="entry-content-header">
            <?php echo "<{$heading} class='post-title entry-title {$css}'>" . __( 'Nothing Found', 'avia_framework' ) . "</{$heading}>"; ?>
        </header>

        <?php get_template_part( 'includes/error404' ); ?>

        <footer class="entry-footer"></footer>
    </article>

<?php

}
