<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       kriesi.at
 * @since      1.0.0
 *
 * @package    Enfold_Recaptcha
 * @subpackage Enfold_Recaptcha/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Enfold_Recaptcha
 * @subpackage Enfold_Recaptcha/includes
 * @author     Enfold <ismael@kriesi.at>
 */
class Enfold_Recaptcha_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'enfold-recaptcha',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
