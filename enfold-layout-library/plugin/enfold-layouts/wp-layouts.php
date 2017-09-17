<?php

/**
 * Plugin Name:       Enfold Layouts
 * Plugin URI:        http://kriesi.at
 * Description:       Create a Library of Layouts and Import between your Installations
 * Version:           1.0.1
 * Author:            Basilis Kanonidis for Kriesi.at
 * Author URI:        http://creativeg.gr
 * License:           GPL-2.0+
 * Text Domain:       wp-layouts
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once 'includes/definitions.php';

require_once WP_LAYOUTS_INCLUDES . '/functions.php';
require_once WP_LAYOUTS_INCLUDES . '/code-layouts-cpt.php';

/**
 * Need for WP version < 4.4.0.
 */
if( ! defined( 'REST_API_VERSION' ) ) {
    require_once WP_LAYOUTS_INCLUDES . '/api.php';
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/classes/class-wp-layouts-activator.php
 */
function activate_wp_layout() {
	require_once WP_LAYOUTS_INCLUDES . '/classes/class-wp-layouts-activator.php';
	WP_Layouts_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/classes/class-wp-layouts-deactivator.php
 */
function deactivate_wp_layout() {
	require_once WP_LAYOUTS_INCLUDES . '/classes/class-wp-layouts-deactivator.php';
	WP_Layouts_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_layout' );
register_deactivation_hook( __FILE__, 'deactivate_wp_layout' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WP_LAYOUTS_INCLUDES . '/classes/class-wp-layouts.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wp_layouts() {

	$plugin = new WP_Layouts();
	$plugin->run();

}
run_wp_layouts();
