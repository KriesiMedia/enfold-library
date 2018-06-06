<?php
/*
Plugin Name: Enfold WooCommerce Privacy Extension
Plugin URI: www.kriesi.at
Description: Extends Enfolds support for WooCommerce privacy by adding checkboxes for Register, Login and Review forms. Message for these boxes can be set independently from standard Enfold privacy login messages.
Version: 1.0.0
Author: Guenter for www.kriesi.at
Author URI: www.kriesi.at
Text Domain: avia_wc_privacy_ext

@requires:	PHP 5.6   
@requires:  WP 4.9.6
@requires:	Enfold 4.4.2
*/

/*  
 * Copyright 2018
*/

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $enfold_wc_privacy_globals;

$enfold_wc_privacy_globals = array(
		'plugin_version'			=>	'1.0.0',
		'theme_name'				=>	'Enfold',
		'min_version'				=>	'4.4.2',
		'plugin_path'				=>	str_replace( basename( __FILE__ ), '', __FILE__ ),
		'plugin_base_name'			=>	plugin_basename( __FILE__ ),
		'plugin_url'				=>	trailingslashit( plugins_url( '', plugin_basename( __FILE__ ) ) ),
		'can_use'					=>	'undefined',
		'base_plugin_class'			=>	'Woocommerce',
		'base_plugin_min_version'	=>	'3.4.1'
	);

/**
 * Main plugin class
 * 
 * @since 1.0.0
 */
final class Enfold_WC_Privacy_Plugin
{
	/**
	 * Holds the instance of this class
	 * 
	 * @since 1.0.0
	 * @var Enfold_WC_Privacy_Plugin
	 */
	private static $_instance = null;
	
	
	/**
	 *
	 * @since 1.0.0
	 * @var Av_WC_Privacy 
	 */
	protected $av_wc_privacy;

		
	/**
	 * Return the instance of this plugin
	 * 
	 * @since 1.0.0
	 * @return Enfold_WC_Privacy_Plugin
	 */
	public static function instance()
	{
		if( is_null( Enfold_WC_Privacy_Plugin::$_instance ) )
		{
			Enfold_WC_Privacy_Plugin::$_instance = new Enfold_WC_Privacy_Plugin();
		}
		
		return Enfold_WC_Privacy_Plugin::$_instance;
	}
	
		
	/**
	 * Load plugin data only when correct theme and base plugin activated
	 * 
	 * @since 1.0.0
	 */
	private function __construct() 
	{
		$this->av_wc_privacy = null;
		
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
		unset( $this->av_wc_privacy );
	}

	/**
	 * Check if we can use functions of this plugin
	 * 
	 * @since 1.0.0
	 * @return string							'yes'|'no'
	 */
	public function can_use_plugin()
	{
		global $enfold_wc_privacy_globals;

		
		if( in_array( $enfold_wc_privacy_globals['can_use'], array( 'yes', 'no' ) ) )
		{
			return $enfold_wc_privacy_globals['can_use'];
		}

		$my_theme = wp_get_theme();
		if( false !== $my_theme->parent() )
		{
			$my_theme = $my_theme->parent();
		}

		if( version_compare( $my_theme->get( 'Version' ),  $enfold_wc_privacy_globals['min_version'], '<' ) )
		{
			$enfold_wc_privacy_globals['can_use'] = 'no';
			return $enfold_wc_privacy_globals['can_use'];
		}

		$enfold_wc_privacy_globals['can_use'] = 'yes';
		return $enfold_wc_privacy_globals['can_use'];
	}
	
	/**
	 * Only when plugin WooCommerce has been loaded, we load our classes
	 * 
	 * @since 1.0.0
	 */
	public function handler_wp_plugins_loaded()
	{
		global $enfold_wc_privacy_globals;
		
		if( ! class_exists( $enfold_wc_privacy_globals['base_plugin_class'] ) )
		{
			return;
		}
		
		if( version_compare( WC()->version, $enfold_wc_privacy_globals['base_plugin_min_version'], '<' ) )
		{
			return;
		}
		
		require_once $enfold_wc_privacy_globals['plugin_path'] . 'classes/class-av-wc-privacy.php';
		
		$this->av_wc_privacy = new Av_WC_Privacy();
		$this->av_wc_privacy->plugin_path = $enfold_wc_privacy_globals['plugin_path'];
		$this->av_wc_privacy->plugin_base_name = $enfold_wc_privacy_globals['plugin_base_name'];
		
	}
}

/**
 * Get a reference to the only object
 * 
 * @since 1.0.0
 * @return Enfold_CF7_Integration_Plugin
 */
function Enfold_WC_Privacy()
{
	return Enfold_WC_Privacy_Plugin::instance();
}
	
/**
 * Activate the plugin class
 */
Enfold_WC_Privacy();

