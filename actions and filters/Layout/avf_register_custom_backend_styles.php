<?php
/**
 * You can use “avf_register_custom_backend_styles” filter introduced in Enfold 4.2.2 to add more 
 * pre-defined color schemes to Enfold theme options. You can use enfold/includes/admin/register-backend-styles.php 
 * file as a reference and create your own file and add it to your child theme and then add following code to functions.php 
 * file of your child theme. 
 *
 * Note: You should return full path of the file.
 **/
function avia_custom_backend_styles()
{
    return get_stylesheet_directory() . '/custom-backend-styles.php';
}

add_filter( 'avf_register_custom_backend_styles', 'avia_custom_backend_styles' );
