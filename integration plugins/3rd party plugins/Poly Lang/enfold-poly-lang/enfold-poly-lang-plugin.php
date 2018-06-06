<?php
/*
Plugin Name: Enfold Polylang Addon
Plugin URI: www.kriesi.at
Description: Extends Enfolds support for Polylang plugin
Version: 1.0.0
Author: Guenter for www.kriesi.at
Author URI: www.kriesi.at
Text Domain: avia_polylang_addon

@requires:	PHP 5.6   
@requires:  WP 4.9.6
@requires:	Enfold 4.4
*/

/*  
 * Copyright 2018
*/

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

final class Enfold_PolyLang_Addon_Plugin
{
	
	public function __construct() 
	{
		add_filter( 'avf_footer_page_id', array( $this, 'handler_avf_footer_page_id' ), 10, 2 );
	}
	
	/**
	 * Translate the ID for a "page as footer"
	 * 
	 * @param int $footer_page_id
	 * @param int $post_id
	 * @return int
	 */
	public function handler_avf_footer_page_id( $footer_page_id, $post_id )
	{
		if( function_exists( 'pll_current_language' ) ) 
		{
			$slug = pll_current_language( 'slug' );
			$footer_page_id = pll_get_post( $footer_page_id, $slug );
		}
		
		return $footer_page_id;
	}
}

new Enfold_PolyLang_Addon_Plugin();