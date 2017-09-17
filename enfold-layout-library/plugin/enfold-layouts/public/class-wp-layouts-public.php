<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    WP_Layouts
 * @subpackage WP_Layouts/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    WP_Layouts
 * @subpackage WP_Layouts/public
 * @author     Author Name <author@name.com>
 */
class WP_Layouts_Public {

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
	 * @param    string    $wp_layout 	The name of the plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $wp_layout, $version ) {
		$this->wp_layouts = $wp_layout;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		// NOTE: At the moment doesn't need.
		// wp_enqueue_style( $this->wp_layouts, plugin_dir_url( __FILE__ ) . 'css/wp-layouts-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// NOTE: At the moment doesn't need.
		// wp_enqueue_script( $this->wp_layouts, plugin_dir_url( __FILE__ ) . 'js/wp-layouts-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {

		require_once WP_LAYOUTS_INCLUDES . '/api-endpoints.php';

	    $controller = new WP_REST_WP_Layouts_Controller;

	    if( is_subclass_of( $controller, 'WP_REST_Controller' ) ) {
            $controller->register_routes();
        }
        
	}

}
