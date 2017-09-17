<?php

class WP_REST_WP_Layouts_Controller extends WP_REST_Controller {

	/**
     * Register the routes for the objects of the controller
     */
    public function register_routes() {

		register_rest_route( 'wp/v2', '/wp-layouts/layouts/(?P<slug>[\w-]+)', array(
	        array(
	            'methods' => WP_REST_Server::READABLE,
	            'callback' => array( $this, 'get_slug_layouts' ),
	            'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::READABLE ),
	        ),
	        'schema' => array( $this, 'get_public_item_schema' ),
	    ));

        register_rest_route( 'wp/v2', '/wp-layouts/layout/(?P<layout_id>[\d-]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_layout_contents' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::READABLE ),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ));

        register_rest_route( 'wp/v2', '/wp-layouts/code/(?P<slug>[\w-]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_slug_layouts_code' ),
                'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::READABLE ),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ));
	}

	public function get_slug_layouts( $request ){
        $return = array();
        $params = $request->get_params();
		if( isset( $params['slug'] ) && ! empty( $params['slug'] ) ){
			$return = array( 'layouts_data' => wp_layout_get_layouts_info( $params['slug']  ) );
		}
        return $return;
	}

    public function get_layout_contents( $request ){
        $return = array();
        $params = $request->get_params();
        if( isset( $params['layout_id'] ) && ! empty( $params['layout_id'] ) ){
            $return = array( 'layout_content' => wp_layouts_get_layout_contents( $params['layout_id'] ) );
        }
        return $return;
    }

    public function get_slug_layouts_code( $request ) {
        $return = false;
        $params = $request->get_params();
        if( isset( $params['slug'] ) && ! empty( $params['slug'] ) ){
            echo wp_layouts_get_layout_code( $params['slug'] );
            echo "\n\n\n";
            exit;
        }
        if( false === $return ){
            return new WP_Error( __( 'Failed to display layouts data.', WP_LAYOUTS_SLUG ) );
        }
    }

}