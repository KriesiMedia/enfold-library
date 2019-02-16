<?php
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


global $avia_config, $post_loop_count;

$post_loop_count= 1;
$post_class 	= "post-entry-".avia_get_the_id();



// check if we got posts to display:
if (have_posts()) :

	while (have_posts()) : the_post();

		/**
		 * initialise for each entry
		 */
		ShortcodeHelper::$tree = Avia_Builder()->get_shortcode_tree( get_the_ID() );
		ShortcodeHelper::$shortcode_index = 0;
?>

		<article class='post-entry post-entry-type-page <?php echo $post_class; ?>' <?php avia_markup_helper(array('context' => 'entry')); ?>>

			<div class="entry-content-wrapper clearfix">
                <?php
                echo '<header class="entry-content-header">';
                    $thumb = get_the_post_thumbnail(get_the_ID(), $avia_config['size']);

                    if($thumb) echo "<div class='page-thumb'>{$thumb}</div>";
                echo '</header>';

                //display the actual post content
                echo '<div class="entry-content" '.avia_markup_helper(array('context' => 'entry_content','echo'=>false)).'>';
                    the_content(__('Read more','avia_framework').'<span class="more-link-arrow"></span>');
                echo '</div>';

                echo '<footer class="entry-footer">';
                wp_link_pages(array('before' =>'<div class="pagination_split_post">',
                                        'after'  =>'</div>',
                                        'pagelink' => '<span>%</span>'
                                        ));
                echo '</footer>';
                
                do_action('ava_after_content', get_the_ID(), 'page');
                ?>
			</div>

		</article><!--end post-entry-->


<?php
	$post_loop_count++;
	endwhile;
	else:
?>

    <article class="entry">
        <header class="entry-content-header">
            <h1 class='post-title entry-title'><?php _e('Nothing Found', 'avia_framework'); ?></h1>
        </header>

        <?php get_template_part('includes/error404'); ?>

        <footer class="entry-footer"></footer>
    </article>

<?php

	endif;

