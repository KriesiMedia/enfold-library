<?php
/*
Plugin Name: Enfold Privacy Addon Plugin
Plugin URI: www.kriesi.at
Description: Extends Enfolds privacy support. Adds a shortcode for creating a button to open the modal privacy popup window, allows to customize WP Cookie Consent Checkbox for standard WP comment fields
Version: 1.0.0
Author: Guenter for www.kriesi.at
Author URI: www.kriesi.at
Text Domain: avia_privacy_addon

@requires:	PHP 5.6   
@requires:  WP 4.9.6
@requires:	Enfold 4.4.2
*/

/*  
 * Copyright 2018
*/

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $enfold_privacy_addon_globals;

$enfold_privacy_addon_globals = array(
		'plugin_version'			=>	'1.0.0',
		'theme_name'				=>	'Enfold',
		'min_version'				=>	'4.1.2',
		'plugin_path'				=>	str_replace( basename( __FILE__ ), '', __FILE__ ),
		'plugin_url'				=>	trailingslashit( plugins_url( '', plugin_basename( __FILE__ ) ) ),
		'can_use'					=>	'undefined',
	);

/**
 * Main plugin class
 * 
 * @since 1.0.0
 */
final class Enfold_Privacy_Addon_Plugin
{
	/**
	 * Holds the instance of this class
	 * 
	 * @since 1.0.0
	 * @var Enfold_Privacy_Addon_Plugin
	 */
	private static $_instance = null;
	
	
	/**
	 *
	 * @since 1.0.0
	 * @var Av_WC_Privacy 
	 */
	protected $av_privcy_addon;

		
	/**
	 * Return the instance of this plugin
	 * 
	 * @since 1.0.0
	 * @return Enfold_Privacy_Addon_Plugin
	 */
	public static function instance()
	{
		if( is_null( Enfold_Privacy_Addon_Plugin::$_instance ) )
		{
			Enfold_Privacy_Addon_Plugin::$_instance = new Enfold_Privacy_Addon_Plugin();
		}
		
		return Enfold_Privacy_Addon_Plugin::$_instance;
	}
	
		
	/**
	 * Load plugin data only when correct theme and base plugin activated
	 * 
	 * @since 1.0.0
	 */
	private function __construct() 
	{
		$this->av_privcy_addon = null;
		
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
		unset( $this->av_privcy_addon );
	}

	/**
	 * Check if we can use functions of this plugin
	 * 
	 * @since 1.0.0
	 * @return string							'yes'|'no'
	 */
	public function can_use_plugin()
	{
		global $enfold_privacy_addon_globals;

		
		if( in_array( $enfold_privacy_addon_globals['can_use'], array( 'yes', 'no' ) ) )
		{
			return $enfold_privacy_addon_globals['can_use'];
		}

		$my_theme = wp_get_theme();
		if( false !== $my_theme->parent() )
		{
			$my_theme = $my_theme->parent();
		}

		if( version_compare( $my_theme->get( 'Version' ),  $enfold_privacy_addon_globals['min_version'], '<' ) )
		{
			$enfold_privacy_addon_globals['can_use'] = 'no';
			return $enfold_privacy_addon_globals['can_use'];
		}

		$enfold_privacy_addon_globals['can_use'] = 'yes';
		return $enfold_privacy_addon_globals['can_use'];
	}
	
	/**
	 * Only when plugin contact form 7 has been loaded, we load our classes
	 * 
	 * @since 1.0.0
	 */
	public function handler_wp_plugins_loaded()
	{
		global $enfold_privacy_addon_globals;
		
		require_once $enfold_privacy_addon_globals['plugin_path'] . 'classes/class-av-privacy-addon.php';
		
		$this->av_privcy_addon = new Av_Privacy_AddOn();
		$this->av_privcy_addon->plugin_path = $enfold_privacy_addon_globals['plugin_path'];
	}
	
	
}

/**
 * Get a reference to the only object
 * 
 * @since 1.0.0
 * @return Enfold_CF7_Integration_Plugin
 */
function Enfold_Privacy_Addon()
{
	return Enfold_Privacy_Addon_Plugin::instance();
}
	
/**
 * Activate the plugin class
 */
Enfold_Privacy_Addon();

