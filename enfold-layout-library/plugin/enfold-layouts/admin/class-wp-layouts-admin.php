<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Layouts
 * @subpackage WP_Layouts/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    WP_Layouts
 * @subpackage WP_Layouts/admin
 * @author     Author Name <author@name.com>
 */
class WP_Layouts_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $wp_layout    The ID of this plugin.
	 */
	private $wp_layout;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $wp_layout	The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $wp_layoutld, $version ) {
		$this->wp_layouts = $wp_layoutld;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $current_screen;
		if( 'wp_layout' === $current_screen->post_type ){
			wp_enqueue_style( $this->wp_layouts, plugin_dir_url( __FILE__ ) . 'css/wp-layouts-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// NOTE: At the moment doen't need.
		/*global $current_screen;		
		if( 'wp_layout' === $current_screen->post_type ){
			wp_enqueue_script( $this->wp_layouts, plugin_dir_url( __FILE__ ) . 'js/wp-layouts-admin.js', array( 'jquery' ), $this->version, false );
		}*/
	}

}
