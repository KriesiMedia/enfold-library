<?php
/*    
* Load Style.css file of child theme after minification so it overrides minification scripts 
*
* Related thread: https://kriesi.at/support/topic/add-style-css-from-child-to-merge-files/
*
*/ 

/* Dequeue Style.css file of child theme */ 
add_action( 'wp_enqueue_scripts', 'av_dequeue_child_stylecss', 20 );
function av_dequeue_child_stylecss() {
    if(is_child_theme()){
        wp_dequeue_style( 'avia-style' );
    }
}

/* Enqueue Style.css after minification scripts */ 
add_action( 'wp_enqueue_scripts', 'av_reenqueue_child_stylecss', 9999999 );
function av_reenqueue_child_stylecss() 
{
    if (is_child_theme()){
        wp_enqueue_style( 'avia-style', get_stylesheet_uri(), true, filemtime( get_stylesheet_directory() . '/style.css' ), 'all');
    }
}
