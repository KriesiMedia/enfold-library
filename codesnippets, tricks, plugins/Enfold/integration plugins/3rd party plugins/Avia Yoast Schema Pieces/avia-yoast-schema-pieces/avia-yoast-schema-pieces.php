<?php

/**
 * The plugin bootstrap file
 *
 * @link              kriesi.at
 * @since             5.0.0
 * @package           Avia_Yoast_Schema_Pieces
 *
 * @wordpress-plugin
 * Plugin Name:       Avia Yoast Schema Pieces
 * Plugin URI:        kriesi.at
 * Description:       Appends schema pieces to WordPress SEO's main schema graph.
 * Version:           1.0.0
 * Author:            Kriesi
 * Author URI:        kriesi.at
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       avia-yoast-schema-pieces
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AVIA_YOAST_SCHEMA_PIECES_VERSION', '1.0.0' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-avia-yoast-schema-pieces-core.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function avia_yoast_schema_pieces() 
{	
	if (! defined('WPSEO_VERSION') || ! defined('WPSEO_PATH')) {
		return;
	}

	new AviaYoastSchemaPiecesCore();
}

add_action('plugins_loaded', 'avia_yoast_schema_pieces');


