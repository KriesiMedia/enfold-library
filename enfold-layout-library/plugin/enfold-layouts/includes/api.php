<?php
/**
 * Version number for our API.
 *
 * @var string
 */
define( 'REST_API_VERSION', '2.0' );

/** Compatibility shims for PHP functions */
include_once( WP_LAYOUTS_REST_API . '/compat.php' );

/** Core HTTP Request API */
if ( file_exists( WP_LAYOUTS_REST_API . '/class-wp-http-response.php' ) ) {
	include_once( WP_LAYOUTS_REST_API . '/class-wp-http-response.php' );
} else {
	// Compatibility with WP 4.3 and below
	include_once( WP_LAYOUTS_REST_API . '/wp-includes/class-wp-http-response.php' );
}

/** Main API functions */
include_once( WP_LAYOUTS_REST_API . '/functions.php' );

/** WP_REST_Server class */
include_once( WP_LAYOUTS_REST_API . '/wp-includes/rest-api/class-wp-rest-server.php' );

/** WP_REST_Response class */
include_once( WP_LAYOUTS_REST_API . '/wp-includes/rest-api/class-wp-rest-response.php' );

/** WP_REST_Request class */
require_once( WP_LAYOUTS_REST_API . '/wp-includes/rest-api/class-wp-rest-request.php' );

/** REST functions */
include_once( WP_LAYOUTS_REST_API . '/wp-includes/rest-api/rest-functions.php' );

/** REST filters */
include_once( WP_LAYOUTS_REST_API . '/filters.php' );