<?php
/*
Plugin Name: Enfold Dynamic Seperator/Whitspace Plugin
Plugin URI: www.kriesi.at
Description: Extends the Advanced Layout builder with a Seperator and Whitespace element that allows to use a fixed width for the seperator that becomes responsive when container element gets too small. Based on the default Separator / Whitespace element.
Version: 1.0.0
Author: Guenter for www.kriesi.at
Author URI: www.kriesi.at
Text Domain: avia_framework

@requires:	PHP 5.3   
@requires:  WP 4.8.1
@requires:	Enfold 4.1.2
*/

/*  
 * Copyright 2017
*/
if ( ! defined( 'ABSPATH' ) ) {   exit;  } // Exit if accessed directly

global $enfold_dyn_sw_globals;

$enfold_dyn_sw_globals = array(
		'plugin_version'	=>	'1.0.0',
		'theme_name'		=>	'Enfold',
		'min_version'		=>	'4.1.2',
		'plugin_path'		=>	str_replace( basename( __FILE__ ), '', __FILE__ ),
		'plugin_url'		=>	trailingslashit( plugins_url( '', plugin_basename( __FILE__ ) ) ),
		'can_use'			=>	'undefined'
	);


/**
 * Define basic functions for this plugin
 * 
 * @since 1.0.0
 */
class enfold_dyn_seperators
{
	/**
	 * Check if we can use functions of this plugin
	 * 
	 * @return false|'yes'|'no'
	 */
	static public function can_use_plugin()
	{
		global $enfold_dyn_sw_globals;
		
		if( in_array( $enfold_dyn_sw_globals['can_use'], array('yes', 'no' ) ) )
		{
			return $enfold_dyn_sw_globals['can_use'];
		}
		
		$my_theme = wp_get_theme();
		if( false !== $my_theme->parent() )
		{
			$my_theme = $my_theme->parent();
		}
		
		if( $my_theme->get( 'Name' ) != $enfold_dyn_sw_globals['theme_name'] )
		{
			$enfold_dyn_sw_globals['can_use'] = 'no';
			return $enfold_dyn_sw_globals['can_use'];
		}
		
		if( version_compare( $my_theme->get( 'Version' ),  $enfold_dyn_sw_globals['min_version'], '<' ) )
		{
			$enfold_dyn_sw_globals['can_use'] = 'no';
			return $enfold_dyn_sw_globals['can_use'];
		}
		
		$enfold_dyn_sw_globals['can_use'] = 'yes';
		return $enfold_dyn_sw_globals['can_use'];
	}
}

add_action( 'init', 'enfold_dsw_register_script' , 100 );
add_action( 'wp_enqueue_scripts', 'enfold_dsw_enqueue_scripts' , 100 );
add_filter('avia_load_shortcodes', 'enfold_dsw_include_shortcode_template', 15, 1);

/**
 * 
 * @since 1.0.0
 * @param array $paths
 * @return array
 */
function enfold_dsw_include_shortcode_template( $paths )
{
	global $enfold_dyn_sw_globals;
	
	if( ! is_array( $paths ) )
	{
		$paths = array();
	}

    array_unshift( $paths, $enfold_dyn_sw_globals['plugin_path'].'shortcodes/' );

	return $paths;
}

/**
 * 
 * @global array $enfold_dyn_sw_globals
 */
function enfold_dsw_register_script()
{	
	global $enfold_dyn_sw_globals;

	wp_register_script( 'enfold_dyn_sw_script', $enfold_dyn_sw_globals['plugin_url'] . 'js/dyn_hr.js', array( 'jquery' ), $enfold_dyn_sw_globals['plugin_version'] );
}

/**
 * @since 1.0.0
 */
function enfold_dsw_enqueue_scripts()
{
	if( is_admin() )
	{
		return;
	}
	
	if( 'yes' == enfold_dyn_seperators::can_use_plugin() )
	{
		wp_enqueue_script( 'enfold_dyn_sw_script' );
	}
}