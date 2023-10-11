<?php
/*
Plugin Name: Local Google Web Fonts
Description: Code to host Google Fonts locally
Version: 1.0
*/
/* Start Adding Functions Below this Line */

/*** the entries are exemplary and should be replaced by the fonts you like to implement
* you have to put the wanted fonts directly into the fonts folder without the container 
* the css comes to the css folder  
***/

function local_google_fonts() {
	wp_enqueue_style( 'open-sans-fonts', plugin_dir_url( __FILE__ ) . 'assets/css/open-sans.css' );
	wp_enqueue_style( 'open-sans-condensed-fonts', plugin_dir_url( __FILE__ ) . 'assets/css/open-sans-condensed.css' );
	wp_enqueue_style( 'bad-script-fonts', plugin_dir_url( __FILE__ ) . 'assets/css/bad-script.css' );
}

add_action( 'wp_enqueue_scripts', 'local_google_fonts' );
