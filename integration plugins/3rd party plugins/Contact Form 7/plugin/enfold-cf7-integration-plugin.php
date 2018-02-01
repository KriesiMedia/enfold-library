<?php
/*
Plugin Name: Enfold Contact Form 7 Integration Plugin
Plugin URI: www.kriesi.at
Description: Extends Enfolds stylesheets to support elements of Contact Form 7 plugin, minimum supported version 5.0. You have to save your theme options after activating the plugin to update Enfold internal stylesheets.
Version: 1.0.0
Author: Guenter for www.kriesi.at
Author URI: www.kriesi.at
Text Domain: avia_framework

@requires:	PHP 5.6   
@requires:  WP 4.8.1
@requires:	Enfold 4.2
@requires:	Contact Form 7 5.0
*/

/*  
 * Copyright 2018
*/

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


global $enfold_cf7_integration_globals;

$enfold_cf7_integration_globals = array(
		'plugin_version'			=>	'1.0.0',
		'theme_name'				=>	'Enfold',
		'min_version'				=>	'4.2',
		'plugin_path'				=>	str_replace( basename( __FILE__ ), '', __FILE__ ),
		'plugin_url'				=>	trailingslashit( plugins_url( '', plugin_basename( __FILE__ ) ) ),
		'can_use'					=>	'undefined',
		'base_plugin_class'			=>	'WPCF7',
		'base_plugin_min_version'	=>	'5.0'
	);



/**
 * Main plugin class
 * 
 * @since 1.0.0
 */
final class Enfold_CF7_Integration_Plugin
{
	/**
	 * Holds the instance of this class
	 * 
	 * @since 1.0.0
	 * @var Enfold_CF7_Integration_Plugin
	 */
	private static $_instance = null;
	
	
	/**
	 *
	 * @since 1.0.0
	 * @var Enfold_CF7_Integration 
	 */
	protected $cf7;


	/**
	 * Return the instance of this plugin
	 * 
	 * @since 1.0.0
	 * @return Enfold_CF7_Integration_Plugin
	 */
	public static function instance()
	{
		if( is_null( Enfold_CF7_Integration_Plugin::$_instance ) )
		{
			Enfold_CF7_Integration_Plugin::$_instance = new Enfold_CF7_Integration_Plugin();
		}
		
		return Enfold_CF7_Integration_Plugin::$_instance;
	}
			
	/**
	 * Load plugin data only when correct theme and base plugin activated
	 * 
	 * @since 1.0.0
	 */
	private function __construct() 
	{
		$this->cf7 = null;
		
		if( $this->can_use_plugin() != 'yes' )
		{
			return;
		}
		
		add_action( 'plugins_loaded', array( $this, 'handler_wp_plugins_loaded') );
	}
	
	
	/**
	 * @since 1.0.0
	 */
	public function __destruct() 
	{
		unset( $this->cf7 );
	}

	
	/**
	 * Check if we can use functions of this plugin
	 * 
	 * @since 1.0.0
	 * @return string							'yes'|'no'
	 */
	public function can_use_plugin()
	{
		global $enfold_cf7_integration_globals;

		
		if( in_array( $enfold_cf7_integration_globals['can_use'], array( 'yes', 'no' ) ) )
		{
			return $enfold_cf7_integration_globals['can_use'];
		}

		$my_theme = wp_get_theme();
		if( false !== $my_theme->parent() )
		{
			$my_theme = $my_theme->parent();
		}

		if( $my_theme->get( 'Name' ) != $enfold_cf7_integration_globals['theme_name'] )
		{
			$enfold_cf7_integration_globals['can_use'] = 'no';
			return $enfold_cf7_integration_globals['can_use'];
		}

		if( version_compare( $my_theme->get( 'Version' ),  $enfold_cf7_integration_globals['min_version'], '<' ) )
		{
			$enfold_cf7_integration_globals['can_use'] = 'no';
			return $enfold_cf7_integration_globals['can_use'];
		}

		$enfold_cf7_integration_globals['can_use'] = 'yes';
		return $enfold_cf7_integration_globals['can_use'];
	}
	
	/**
	 * Only when plugin contact form 7 has been loaded, we load our classes
	 * 
	 * @since 1.0.0
	 */
	public function handler_wp_plugins_loaded()
	{
		global $enfold_cf7_integration_globals;
		
		if( ! class_exists( $enfold_cf7_integration_globals['base_plugin_class'] ) )
		{
			return;
		}
		
		if( version_compare( WPCF7_VERSION, $enfold_cf7_integration_globals['base_plugin_min_version'], '<' ) )
		{
			return;
		}
		
		require_once $enfold_cf7_integration_globals['plugin_path'] . 'classes/class-enfold-cf7-integration.php';
		
		$this->cf7 = new Enfold_CF7_Integration();
	}
	
}

/**
 * Get a reference to the only object
 * 
 * @since 1.0.0
 * @return Enfold_CF7_Integration_Plugin
 */
function Enfold_CF7_Integration_Plugin()
{
	return Enfold_CF7_Integration_Plugin::instance();
}
	
/**
 * Activate the plugin class
 */
Enfold_CF7_Integration_Plugin();