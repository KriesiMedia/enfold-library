<?php
/**
 * REST API functions.
 *
 * @package WordPress
 * @subpackage REST_API
 */

/**
 * Version number for our API.
 *
 * @var string
 */
define( 'REST_API_VERSION', '2.0' );

/** WP_REST_Server class */
require_once( WP_LAYOUTS_REST_API . '/rest-api/class-wp-rest-server.php' );

/** WP_REST_Response class */
require_once( WP_LAYOUTS_REST_API . '/rest-api/class-wp-rest-response.php' );

/** WP_REST_Request class */
require_once( WP_LAYOUTS_REST_API . '/rest-api/class-wp-rest-request.php' );

/** REST functions */
require_once( WP_LAYOUTS_REST_API . '/rest-api/rest-functions.php' );
