<?php 
/**
 * Fixed Footer
 *
 * To force the footer to the bottom of the screen because for short pages.
 * Related thread: http://www.kriesi.at/support/topic/force-footer-to-the-bottom-of-the-screen/
 *
 **/

function my_fixed_footer() {
?>
    <style type="text/css">
        #top {
            position: relative;
            min-height: 100%;
        }

        #footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
        }
    </style>
<php
 }
add_action( 'wp_head', 'my_fixed_footer' );
