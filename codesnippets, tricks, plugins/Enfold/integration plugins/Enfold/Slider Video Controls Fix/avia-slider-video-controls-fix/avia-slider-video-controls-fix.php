<?php

/**
 *
 * @link              kriesi.at
 * @since             5.0.0
 * @package           Avia_Slider_Video_Controls_Fix
 *
 * @wordpress-plugin
 * Plugin Name:       Avia Slider Video Controls Fix
 * Plugin URI:        kriesi.at
 * Description:       Make the video controls inside the sliders accessible.
 * Version:           1.0.0
 * Author:            Kriesi
 * Author URI:        kriesi.at
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       avia_framework
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('AVIA_SLIDER_VIDEO_CONTROLS_FIX', '1.0.0');

/**
 * Fix slider video controls.
 */
class AviaSliderVideoControlsFix
{
    public function __construct() {
		add_filter( 'body_class', [ $this, 'body_class' ] );	
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('avia-slider-videos-controls-fix', plugins_url('slider-video-controls.js', __FILE__), ['jquery'], '1', true);
    }

	public function body_class($classes) {
		return array_merge($classes, array( 'avia-slider-video-controls-fix-support' ));
	}
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function avia_slider_video_controls_fix()
{
	new AviaSliderVideoControlsFix();
}

if (! wp_installing()) {
    add_action('plugins_loaded', 'avia_slider_video_controls_fix');
}
