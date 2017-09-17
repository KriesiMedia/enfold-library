<?php
if ( ! defined( 'WP_LAYOUTS_VERSION' ) ) { define( 'WP_LAYOUTS_VERSION', '1.0.0' ); }
if ( ! defined( 'WP_LAYOUTS_SLUG' ) ) { define( 'WP_LAYOUTS_SLUG', 'wp-layouts' ); }
if ( ! defined( 'WP_LAYOUTS_ROOT' ) ) { define( 'WP_LAYOUTS_ROOT', substr( plugin_dir_path( __DIR__, 1 ), 0, -1 ) ); }
if ( ! defined( 'WP_LAYOUTS_INCLUDES' ) ) { define( 'WP_LAYOUTS_INCLUDES', WP_LAYOUTS_ROOT . '/includes' ); }
if ( ! defined( 'WP_LAYOUTS_REST_API' ) ) { define( 'WP_LAYOUTS_REST_API', WP_LAYOUTS_INCLUDES . '/api' ); }