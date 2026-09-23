<?php
/**
 * This file holds various ajax functions that hook into wordpress admin-ajax.php script with the generic "wp_".$_POST['action'] hook
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright (c) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 */
if( ! defined( 'AVIA_FW' ) )   {   exit( 'No direct script access allowed' );   }


if( ! function_exists( 'ajax_decode_deep' ) )
{
	/**
	 * Helper that decodes ajax submitted forms
	 *
	 * @param array|string $value
	 */
	function ajax_decode_deep( $value )
	{
		$charset = get_bloginfo('charset');
		$value = is_array( $value ) ? array_map( 'ajax_decode_deep', $value ) : stripslashes( htmlentities( urldecode( $value ), ENT_QUOTES, $charset ) );

		return $value;
	}
}


if( ! function_exists( 'avia_ajax_modify_set' ) )
{
	/**
	 * modifies the option array based on an ajax request and returns the modified option array to the browser
	 * If the add method is set the function also returns the element that should be added so jquery can inject it to the dom
	 */
	function avia_ajax_modify_set()
	{
		$check = 'avia_nonce_save_backend';

		if( $_POST['context'] == 'metabox' )
		{
			$check = 'avia_nonce_save_metabox';
		}

		check_ajax_referer( $check );

		//security improvement. only allow certain permissions to execute this function
		if( ! current_user_can( 'edit_posts' ) )
		{
			die( -1 );
		}

		if( isset( $_POST['ajax_decode'] ) )
		{
			$_POST = ajax_decode_deep( $_POST );
		}

		//add a new set
		if( $_POST['method'] == 'add' )
		{
			$html = new avia_htmlhelper();
			$sets = new avia_database_set();

			if( isset( $_POST['context'] ) )
			{
				//change the output context for meta boxes and custom sets
				$html->context = $_POST['context'];
				if( $_POST['context'] =='metabox' )
				{
					include( AVIA_BASE . '/includes/admin/register-admin-metabox.php' );

					$sets->elements = $elements;
				}
			}

			$element = $sets->get( $_POST['elementSlug'] );

			if( $element )
			{
				if( isset( $_POST['context'] ) && $_POST['context'] == 'custom_set' )
				{
					$element['slug'] = $_POST['optionSlug'];
					$element['id'] = $_POST['optionSlug'] . $element['id'];

					$sets->add_element_to_db( $element, $_POST );
				}

				if( isset( $_POST['std'] ) )
				{
					$element['std'][0] = $_POST['std'];
				}

				if( isset( $_POST['apply_all'] ) )
				{
					$element['apply_all'] = $_POST['apply_all'];
				}

				$element['ajax_request'] = 1;

				if( isset( $_POST['activate_filter'] ) )
				{
					add_filter( 'avia_ajax_render_element_filter', $_POST['activate_filter'], 10, 2 );
				}

				$element = apply_filters( 'avia_ajax_render_element_filter', $element, $_POST );

				//render element for output
				echo '{avia_ajax_element}' . $html->render_single_element( $element ) . '{/avia_ajax_element}';
			}
		}

		die();
	}

	//hook into wordpress admin.php
	add_action( 'wp_ajax_avia_ajax_modify_set', 'avia_ajax_modify_set' );
}


if( ! function_exists( 'avia_ajax_fetch_all' ) )
{
	/**
	 * helper function for the gallery that fetches all image atachment ids of a post
	 *
	 * @param array $element
	 * @param array $sent_data
	 * @return array
	 */
	function avia_ajax_fetch_all( $element, $sent_data )
	{
		$post_id = $sent_data['apply_all'];

		$args = array(
					'post_type'		=> 'attachment',
					'numberposts'	=> -1,
					'post_status'	=> null,
					'post_parent'	=> $post_id
				);
		$attachments = get_posts( $args );

		if( $attachments && is_array( $attachments ) )
		{
			$counter = 0;
			$element['ajax_request'] = count( $attachments );
			foreach( $attachments as $attachment )
			{
				$element['std'][ $counter ]['slideshow_image'] = $attachment->ID;
				$counter++;
			}
		}

		return $element;
	}
}


if( ! function_exists( 'avia_ajax_save_options_page' ) )
{
	/**
	 * Receives the values entered into the option page form elements. All values are submitted via ajax (js/avia_option_pages.js).
	 *	- checks if the user is allowed to edit the options
	 *	- double explodes the post array( by "&" creates option set, by "=" the key/value pairs )
	 *	- stores in the database options table
	 *
	 * Supports multiple options pages ( $_POST['slug'] contains the key for the page )
	 */
	function avia_ajax_save_options_page()
	{
		global $avia;

		//check if user is allowed to save and if its his intention with a nonce check
		check_ajax_referer( 'avia_nonce_save_backend' );

		//security improvement. only allow certain permissions to execute this function
		if( ! current_user_can( 'manage_options' ) )
		{
			die( -1 );

		}

		//if we got no post data or no database key abort the script
		if( ! isset( $_POST['data'] ) || ! isset( $_POST['prefix'] ) || ! isset( $_POST['slug'] ) )
		{
			die();
		}

		$optionkey = $_POST['prefix'];

		$data_sets = explode( '&', $_POST['data'] );
		$store_me = avia_ajax_save_options_create_array( $data_sets );

		$current_options = get_option( $optionkey, array() );
		if( ! is_array( $current_options ) )
		{
			$current_options = array();
		}

		$current_options[ $_POST['slug'] ] = $store_me;

		//	clean up
		foreach( $current_options as $slug => $options )
		{
			if( ! is_array( $options ) )
			{
				unset( $current_options[ $slug ] );
			}
		}

		/**
		 * Allows to manipulate the options array before saving to database
		 * e.g. remove plugin specific options added to modify with theme options
		 *
		 * @since 4.7.4.1
		 * @param array $current_options
		 * @return array
		 */
		$current_options = apply_filters( 'avf_before_save_options_page_array', $current_options );

		//	hook in case we want to do somethin with the new options
		do_action( 'avia_ajax_save_options_page', $current_options );

		//	remove old option set and save those key/value pairs in the database
		update_option( $optionkey, $current_options );

		//	flush rewrite rules for custom post types
		update_option( 'avia_rewrite_flush', 1 );

		//	hook in case we want to do somethin after saving
		do_action( 'avia_ajax_after_save_options_page', $current_options );

		die( 'avia_save' );
	}

	//	hook into wordpress admin.php
	add_action( 'wp_ajax_avia_ajax_save_options_page', 'avia_ajax_save_options_page' );
}


if( ! function_exists( 'avia_ajax_save_options_create_array' ) )
{
	/**
	 * Creates an array with unlimited depth with the key/value pairs passed from the ajax script
	 *
	 * @since ???
	 * @param array $data_sets				exploded string that was passed by an ajax script
	 * @param boolean $global_post_array
	 * @return array
	 */
	function avia_ajax_save_options_create_array( array $data_sets, $global_post_array = false )
	{
		$result = array();
		$charset = get_bloginfo( 'charset' );

		//iterate over the data sets that were passed
		foreach( $data_sets as $key => $set )
		{
			$temp_set = array();

			//if a post array was passed set the array
			if( $global_post_array )
			{
				$temp_set[0] = $key;
				$temp_set[1] = $set;
				$set = $temp_set;
			}
			else //if an ajax data array was passed create the array by exploding the key/value pair
			{
				//create key/value pairs
				$set = explode( '=', $set );
			}

			//escape and convert the value
			$set[1] = stripslashes( $set[1] );
			$set[1] = htmlentities( urldecode( $set[1]), ENT_QUOTES, $charset );

			/*
			 *  check if the element is a group element.
			 *  If so create an array by exploding the string and then iterating over the results and using them as array keys
			 */
			if( $set[0] != '' ) //values with two colons are reserved for js controlling and saving is not needed
			{
				if( strpos( $set[0], '-__-' ) !== false )
				{
					$set[0] = explode( '-__-', $set[0] );

					//http://stackoverflow.com/questions/20259773/nested-numbering-to-array-keys
					avia_ajax_helper_set_nested_value( $result, $set[0], $set[1] );
				}
				else
				{
					$result[ $set[0] ] = $set[1];
				}
			}
		}

		return $result;
	}
}


if( ! function_exists( 'avia_ajax_helper_set_nested_value' ) )
{
	/**
	 * http://stackoverflow.com/questions/20259773/nested-numbering-to-array-keys
	 *
	 * @param array $array
	 * @param array $index
	 * @param mixed $value
	 */
	function avia_ajax_helper_set_nested_value( array &$array, $index, $value )
	{
		$node = &$array;

		foreach( $index as $path )
		{
			$node = &$node[ $path ];
		}

		$node = $value;
	}
}


if( ! function_exists( 'avia_ajax_reset_options_page' ) )
{
	/**
	 * This function resets the whole admin backend, the page is reloaded on success by javascript.
	 *
	 * @since 4.6.4: added filter parameters $_POST['avia_filter']
	 */
	function avia_ajax_reset_options_page()
	{
		//check if user is allowed to reset and if its his intention with a nonce check
		check_ajax_referer( 'avia_nonce_reset_backend' );

		//security improvement. only allow certain permissions to execute this function
		if( ! current_user_can( 'manage_options' ) )
		{
			die( -1 );
		}

		global $avia, $wpdb;

//		$slugs = array( $avia->option_prefix, $avia->option_prefix.'_dynamic_elements', $avia->option_prefix.'_dynamic_pages' );

		$slugs = array( $avia->option_prefix );

		$default_options = array();

		//get all option keys of the framework
		/*
		foreach($avia->option_pages as $option_page)
				{
					if($option_page['slug'] == $option_page['parent'])
					{
						$slugs[$avia->option_prefix.'_'.$option_page['slug']] = true;
					}
				}
		*/

		$button_id = isset( $_POST['avia_id'] ) ? $_POST['avia_id'] : '';
		$filter = ! empty( $_POST['avia_filter'] ) ? (array) $_POST['avia_filter'] : array();

		/**
		 * Modify the filter array to filter or skip settings
		 *
		 * @since 4.6.4
		 * @param.array $filter
		 * @param string $button_id
		 * @return array
		 */
		$filter = apply_filters( 'avf_settings_reset_options_filter_array', $filter, $button_id );

		if( empty( $filter ) )
		{
			//iterate over all option keys and delete them
			foreach( $slugs as $key )
			{
				delete_option( $key );
			}
		}
		else
		{
			$avia_import = avia_ajax_load_importer_classes();

			if( $avia_import instanceof avia_wp_import )
			{
				$default_options = array();
				$default_import = array();

				/**
				 * Create default import array so we can reuse existing code
				 */
				foreach( $avia->subpages as $parent => $slugs )
				{
					$default_options[ $parent ] = array();
					$default_import[ $parent ] = array();

					foreach( $slugs as $slug )
					{
						foreach( $avia->option_page_data as $element )
						{
							if( ! isset( $element['slug'] ) || ( $element['slug'] != $slug ) )
							{
								continue;
							}

							if( ! isset( $element['id'] ) )
							{
								continue;
							}

							//	Skip non existing options
							if( ! isset( $avia->options[ $parent ][ $element['id'] ] ) )
							{
								continue;
							}

							$default_import[ $parent ][ $element['id'] ] = $element;
							$default_options[ $parent ][ $element['id'] ] = isset( $element['std'] ) ? $element['std'] : '';
						}
					}
				}

				$default_options = $avia_import->filter_imported_options( $default_options, $default_import, $filter );

				update_option( $avia->option_prefix, $default_options );
			}
		}

		//flush rewrite rules for custom post types
		update_option( 'avia_rewrite_flush', 1 );

		/**
		 * Allows to hook in case user wants to execute code afterwards
		 *
		 * @since ????
		 * @param array $default_options			added 4.8
		 */
		do_action( 'avia_ajax_reset_options_page', $default_options );

		//end php execution and return avia_reset to the javascript
		die( 'avia_reset' );
	}

	//hook into wordpress admin.php
	add_action( 'wp_ajax_avia_ajax_reset_options_page', 'avia_ajax_reset_options_page' );
}


if( ! function_exists( 'avia_ajax_get_image' ) )
{
	/**
	 * This function gets an attachment image based on its id and returns the image url to the javascript. Needed for advanced image uploader
	 */
	function avia_ajax_get_image()
	{
		#backend single post/page/portfolio item: add multiple preview pictures. get a preview picture via ajax request and display it

		$attachment_id = (int) $_POST['attachment_id'];
		$attachment = get_post( $attachment_id );
		$mime_type = $attachment->post_mime_type;

		if( strpos( $mime_type, 'flash' ) !== false || substr( $mime_type, 0, 5 ) == 'video' )
		{
			$output = $attachment->guid;
		}
		else
		{
			$output = wp_get_attachment_image( $attachment_id, array( 100, 100 ) );
		}

		die( $output );
	}

	//hook into wordpress admin.php
	add_action( 'wp_ajax_avia_ajax_get_image', 'avia_ajax_get_image' );
}


if( ! function_exists( 'avia_ajax_get_gallery' ) )
{
	function avia_ajax_get_gallery()
	{
		#backend single post/page/portfolio item: add multiple preview pictures. get a preview picture via ajax request and display it

		$postId = (int) $_POST['attachment_id'];
		$output = '';
		$image_url_array = array();

		$attachments = get_children( array(
							'post_parent'		=> $postId,
							'post_status'		=> 'inherit',
							'post_type'			=> 'attachment',
							'post_mime_type'	=> 'image',
							'order'				=> 'ASC',
							'orderby'			=> 'menu_order ID'
					));

		foreach( $attachments as $key => $attachment )
		{
			$image_url_array[] = avia_image_by_id( $attachment->ID, array( 'width' => 80, 'height' => 80 ) );
		}

		if( isset( $image_url_array[0] ) )
		{
			foreach( $image_url_array as $key => $img )
			{
				$output .= "<div class='avia_gallery_thumb'><div class='avia_gallery_thumb_inner'>{$img}</div></div>";
			}

			$output  .= '<div class="avia_clear"></div>';
		}

		die( $output );
	}

	//hook into wordpress admin.php
	add_action('wp_ajax_avia_ajax_get_gallery', 'avia_ajax_get_gallery');
}

if( ! function_exists( 'avia_ajax_switch_menu_walker' ) )
{
	/**
	 * This function is a clone of the admin-ajax.php files case:"add-menu-item" with modified walker.
	 * We call this function by hooking into wordpress generic "wp_".$_POST['action'] hook.
	 * To execute this script rather than the default add-menu-items a javascript overwrites default
	 * request with the request for this script
	 */
	function avia_ajax_switch_menu_walker()
	{
		if ( ! current_user_can( 'edit_theme_options' ) )
		{
			die( '-1' );
		}

		check_ajax_referer( 'add-menu_item', 'menu-settings-column-nonce' );

		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

		$item_ids = wp_save_nav_menu_items( 0, $_POST['menu-item'] );
		if ( is_wp_error( $item_ids ) )
		{
			die( '-1' );
		}

		foreach( (array) $item_ids as $menu_item_id )
		{
			$menu_obj = get_post( $menu_item_id );
			if ( ! empty( $menu_obj->ID ) )
			{
				$menu_obj = wp_setup_nav_menu_item( $menu_obj );
				$menu_obj->label = $menu_obj->title; // don't show "(pending)" in ajax-added items
				$menu_items[] = $menu_obj;
			}
		}

		if ( ! empty( $menu_items ) )
		{
			$args = array(
						'after'			=> '',
						'before'		=> '',
						'link_after'	=> '',
						'link_before'	=> '',
						'walker'		=> new avia_backend_walker,
					);

			echo walk_nav_menu_tree( $menu_items, 0, (object) $args );
		}

		die( 'end' );
	}

	//hook into wordpress admin.php
	add_action('wp_ajax_avia_ajax_switch_menu_walker', 'avia_ajax_switch_menu_walker');
}

if( ! function_exists( 'avia_ajax_import_data' ) )
{
	/**
	 * This function handles the ajax call to download and import the demos
	 *
	 * @since < 4.5
	 * @since 4.8.2 support for download demo files from external server added
	 */
	function avia_ajax_import_data()
	{
		//check if user is allowed to save and if its his intention with a nonce check
		check_ajax_referer( 'avia_nonce_import_dummy_data' );


		//security improvement. only allow certain permissions to execute this function
		if( ! current_user_can( 'manage_options' ) )
		{
			die( -1 );
		}


		if( ! isset( $_REQUEST['subaction'] ) )
		{
			exit;
		}

		$demo_full_name = ! empty( $_REQUEST['demo_full_name'] ) ? stripslashes( $_REQUEST['demo_full_name'] ) : sanitize_file_name( (string) ( $_REQUEST['demo_name'] ?? '' ) );

		if( 'download_demos' == $_REQUEST['subaction'] )
		{
			require_once AVIA_PHP . 'inc-avia-download-demo.php';

			$msg = 'avia_downloaded-' . sprintf( __( 'Alright!<br/>Download worked for demo %s. <br/>You can import the demo content now.', 'avia_framework' ), $demo_full_name );
		}
		else if( 'import_demos' == $_REQUEST['subaction'] )
		{
			require_once AVIA_PHP . 'inc-avia-importer.php';

			$msg = 'avia_import-' . sprintf( __( 'Alright!<br/>Import worked out for demo %s, no problems whatsoever. <br/>The page will now be reloaded to reflect the changes', 'avia_framework' ), $demo_full_name );
		}
		else
		{
			exit;
		}

		die( $msg );
	}

	//hook into wordpress admin.php
	add_action( 'wp_ajax_avia_ajax_import_data', 'avia_ajax_import_data' );
}


if( ! function_exists( 'avia_ajax_delete_demo_files' ) )
{
	/**
	 * Delete downloaded demo files from user server
	 *
	 * @since 4.8.2
	 * @added_by Günter
	 */
	function avia_ajax_delete_demo_files()
	{
		//check if user is allowed to save and if its his intention with a nonce check
		check_ajax_referer( 'avia_nonce_import_dummy_data' );


		//security improvement. only allow certain permissions to execute this function
		if( ! current_user_can( 'manage_options' ) )
		{
			die( -1 );
		}

		//	The folder to delete is always derived from the ( sanitized ) demo name on the server and
		//	is confined to the demo folder, so a delete can never target an arbitrary path.
		$demo_name = ! empty( $_REQUEST['demo_name'] ) ? sanitize_file_name( (string) $_REQUEST['demo_name'] ) : '';
		$demo_full_name = ! empty( $_REQUEST['demo_full_name'] ) ? stripslashes( $_REQUEST['demo_full_name'] ) : $demo_name;

		$delete_dir = avia_demo_import_dir( $demo_name );
		if( '' === $delete_dir )
		{
			exit;
		}

		avia_backend_delete_folder( $delete_dir );

		if( is_dir( $delete_dir ) )
		{
			$msg = 'avia_error-' . sprintf( __( 'Downloaded files for demo %s could not be deleted.', 'avia_framework' ), $demo_full_name );
		}
		else
		{
			$msg = 'avia_demo_deleted-' . sprintf( __( 'Alright!<br/>Downloaded files for demo %s deleted', 'avia_framework' ), $demo_full_name );
		}

		die( $msg );
	}

	add_action( 'wp_ajax_avia_ajax_delete_demo_files', 'avia_ajax_delete_demo_files' );
}


if( ! function_exists( 'avia_ajax_import_parent_data' ) )
{
	/**
	 * Imports the parent theme data
	 *
	 */
	function avia_ajax_import_parent_data()
	{
		//check if user is allowed to save and if its his intention with a nonce check
		check_ajax_referer( 'avia_nonce_import_parent_settings' );


		//security improvement. only allow certain permissions to execute this function
		if( ! current_user_can( 'manage_options' ) )
		{
			die( -1 );
		}

		if( is_child_theme() )
		{
			global $avia;

			$theme = wp_get_theme();
			$parent = wp_get_theme( $theme->get('Template') );
			$parent_option_prefix = 'avia_options_' . avia_backend_safe_string( $parent->get('Name') );

			$parent_options = get_option( $parent_option_prefix );

			if( ! is_array( $parent_options ) || empty( $parent_options ) )
			{
				die( __( 'No Parent Theme Options Found. There is nothing to import', 'avia_framework' ) );
			}

			update_option( $avia->option_prefix, $parent_options );
		}
		else
		{
			die( __( 'No Parent Theme found', 'avia_framework' ) );
		}

		die( 'avia_import' );
	}

	//hook into wordpress admin.php
	add_action( 'wp_ajax_avia_ajax_import_parent_settings', 'avia_ajax_import_parent_data' );
}


if( ! function_exists( 'avia_ajax_verify_input' ) )
{
	/**
	 * Callback for a verify input button
	 */
	function avia_ajax_verify_input()
	{
		header( 'Content-Type: application/json' );

		//check if user is allowed to save and if its his intention with a nonce check
		check_ajax_referer( 'avia_nonce_save_backend' );

		//security improvement. only allow certain permissions to execute this function
		if( ! current_user_can( 'manage_options' ) )
		{
			die( -1 );
		}


		$response['success'] = true;
		$response['html'] = '';

		$result = '';
		$callback = '';

		global $avia;
		foreach( $avia->option_page_data as $option )
		{
			if( isset($option['id'] ) && $option['id'] == $_POST['key'] && isset( $option['ajax'] ) )
			{
				$callback = $option['ajax'];
				break;
			}
		}

		if( function_exists( $callback ) )
		{
			$js_callback_value = isset( $_POST['js_value'] ) ? $_POST['js_value'] : null;
			$result = $callback( $_POST['value'] , true, $js_callback_value );

			if( ! is_array( $result ) )
			{
				$response['html'] = $result;
			}
			else
			{
				$response = array_merge( $response, $result );
			}
		}

		echo json_encode( $response );
		exit;
	}

	//hook into wordpress admin.php
	add_action( 'wp_ajax_avia_ajax_verify_input', 'avia_ajax_verify_input' );
}


if( ! function_exists( 'avia_ajax_import_alb_templates_file' ) )
{
	/**
	 * imports the config file
	 */
    function avia_ajax_import_alb_templates_file()
    {
		header( 'Content-Type: application/json' );

		check_ajax_referer( 'avia_nonce_save_backend' );

		//security improvement. only allow certain permissions to execute this function
		if( ! current_user_can( 'manage_options' ) )
		{
			die( -1 );
		}

		$response['success'] = false;

		//check if capability is ok
        $cap = avia_file_upload_capability( 'alb_templates' );
        if( ! current_user_can( $cap ) )
        {
			$response['msg'] = __( "You do not have the required permissions to use this feature.", 'avia_framework' );
			echo json_encode( $response );
			exit;
		}

		$button_id = isset( $_POST['avia_id'] ) ? $_POST['avia_id'] : '';
        $attachment = isset( $_POST['values'] ) ? $_POST['values'] : false;

		if( false === $attachment || ! is_array( $attachment ) )
		{
			$response['msg'] = __( 'Illegal call to import Layout Builder Template file.', 'avia_framework' );
			echo json_encode( $response );
			exit;
		}

        $path = realpath( get_attached_file( $attachment['id'] ) );
        $templates = @file_get_contents( $path );

		if( $templates )
        {
			$builder_template = Avia_Builder()->get_AviaSaveBuilderTemplate();

			try
			{
				$response['msg'] = $builder_template->import_saved_templates( $templates );
				$response['success'] = true;
			}
			catch( Exception $ex )
			{
				$response['msg'] = $ex->getMessage();
			}
		}

		echo json_encode( $response );
		exit;
	}

	add_action( 'wp_ajax_avia_ajax_import_alb_templates_file', 'avia_ajax_import_alb_templates_file' );
}


if( ! function_exists( 'avia_ajax_import_config_file' ) )
{
	/**
	 * imports the config file
	 *
	 */
    function avia_ajax_import_config_file()
    {
        global $avia;

        //check if referer is ok
		check_ajax_referer( 'avia_nonce_save_backend' );


        //check if capability is ok
        $cap = avia_file_upload_capability( 'theme_settings' );


        if( ! current_user_can( $cap ) )
        {
            exit( __( "You do not have the required permissions to use this feature.", 'avia_framework' ) );
        }

		$button_id = isset( $_POST['avia_id'] ) ? $_POST['avia_id'] : '';
        $attachment = isset( $_POST['values'] ) ? $_POST['values'] : false;

		if( false === $attachment || ! is_array( $attachment ) )
		{
			exit( __( 'Illegal call to import settings file.', 'avia_framework' ) );
		}

        $path = realpath( get_attached_file( $attachment['id'] ) );
        $options = @file_get_contents( $path );

        if( $options )
        {
			$avia_import = avia_ajax_load_importer_classes();

			if( $avia_import instanceof avia_wp_import )
			{
				//	Imported settings are plain option data - never allow objects to be instantiated.
				$options_raw = base64_decode( $options );

				/**
				 * Exports since 8.2 are gzip compressed to keep the file small (avoids hosting/WAF
				 * request body limits on upload). Older export files are still plain serialized data,
				 * so fall back to that format when decompression fails.
				 *
				 * @since 8.2
				 */
				$options_decompressed = @gzuncompress( $options_raw );
				$options = unserialize( false !== $options_decompressed ? $options_decompressed : $options_raw, array( 'allowed_classes' => false ) );
				$database_option = array();
				$filter = ! empty( $_POST['avia_filter'] ) ? (array) $_POST['avia_filter'] : array();

				/**
				 * Modify the filter array to filter or skip settings
				 *
				 * @since 4.6.4
				 * @param.array $filter
				 * @param string $button_id
				 * @param.array $options
				 * @return array
				 */
				$filter = apply_filters( 'avf_settings_import_filter_array', $filter, $button_id, $options );

				if( is_array( $options ) )
				{
					foreach( $avia->option_pages as $page )
					{
						if( ! isset( $options[ $page['parent'] ] ) )
						{
							//	we have an option page that does not exist in import options
							if( ! isset( $database_option[ $page['parent'] ] ) )
							{
								$database_option[ $page['parent'] ] = array();
							}
						}
						else
						{
							$database_option[ $page['parent'] ] = $avia_import->extract_default_values( $options[ $page['parent'] ], $page, $avia->subpages );
						}
					}

					if( ! empty( $filter ) )
					{
						$database_option = $avia_import->filter_imported_options( $database_option, $options, $filter );
					}

					if( ! empty( $database_option ) )
					{
						update_option( $avia->option_prefix, $database_option );
					}
				}

				// currently no deletion. seems counter intuitive atm. also since the file upload button will only show txt files user can switch between settings easily
				// wp_delete_attachment($attachment['id'], true);

				/**
				 * @used_by					aviaPostCssManagement::handler_ava_reset_css_files()	100
				 * @since 6.0.6
				 */
				do_action( 'avia_ajax_after_save_options_page', $database_option );
			}
			else
			{
				exit( __( 'Internal error: Importer class could not be loaded - no settings could be imported.', 'avia_framework' ) );
			}
		}

		exit( 'avia_config_file_imported' );
	}

	add_action( 'wp_ajax_avia_ajax_import_config_file', 'avia_ajax_import_config_file' );
}


if( ! function_exists( 'avia_ajax_load_importer_classes' ) )
{
	/**
	 * Loads classes needed for import
	 *
	 * @since 4.6.4
	 * @return avia_wp_import
	 */
	function avia_ajax_load_importer_classes()
	{
		$avia_import = false;

		@ini_set( 'max_execution_time', 1500 );

		if( ! class_exists( 'WP_Import', false ) )
		{
			if( ! defined( 'WP_LOAD_IMPORTERS' ) )
			{
				define( 'WP_LOAD_IMPORTERS', true );
			}

			$class_wp_import = AVIA_PHP . 'wordpress-importer/wordpress-importer.php';
			if( file_exists( $class_wp_import ) )
			{
				require_once( $class_wp_import );
			}
		}

		if( class_exists( 'WP_Import', false ) )
		{
			$class_avia_import = AVIA_PHP . 'wordpress-importer/avia-import-class.php';
			if( file_exists( $class_avia_import ) )
			{
				require_once( $class_avia_import );
				$avia_import = new avia_wp_import();
			}
		}

		return $avia_import;
	}
}

if ( ! function_exists( 'avia_ajax_save_video_thumbnails_locally' ) )
{
	/**
	 * Retrieve video thumbnails and save them in the media library
	 *
	 * @since 5.3
	 */
	function avia_ajax_save_video_thumbnails_locally()
	{
		global $avia_config;

		check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce', false );

		//security improvement. only allow certain permissions to execute this function
		if( ! current_user_can( 'edit_posts' ) )
		{
			die( -1 );
		}

		$attachments = [];
		$wp_upload_dir = wp_upload_dir();

		$video_url = isset( $_REQUEST['video_url'] ) ? $_REQUEST['video_url'] : '';
		$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : 0;

		$video_provider = '';
		$video_thumb_hq = '';

		try
		{
			if( false !== stripos( $video_url, 'youtube' ) )
			{
				$video_provider = 'youtube';

				if( false !== strpos( $video_url, '/shorts/' ) )
				{
					$video_id = explode( '/shorts/', $video_url );
					$video_id = isset( $video_id[1] ) ? strtok( $video_id[1], '?&#' ) : '';
				}
				else
				{
					$query_string = (string) parse_url( $video_url, PHP_URL_QUERY );
					parse_str( $query_string, $query_args );
					$video_id = isset( $query_args['v'] ) ? $query_args['v'] : '';
				}

				if( empty( $video_id ) )
				{
					$error_message = __( 'Error: Video ID is missing', 'avia_framework' );
					throw new Exception( $error_message, 400 );
				}

				$video_thumb_id = 'https://img.youtube.com/vi/' . $video_id;
				$video_thumb_hq = $video_thumb_id . '/maxresdefault.jpg';
				$video_thumb = $video_thumb_id . '/0.jpg';
			}
			else if( false !== stripos( $video_url, 'vimeo' ) )
			{
				$video_provider = 'vimeo';
				$video_path = (string) parse_url( $video_url, PHP_URL_PATH );
				$start = strripos( $video_path, '/' );
				$video_id = substr( $video_path, $start + 1 );

				if( empty( $video_id ) )
				{
					$error_message = __( 'Error: Video ID is missing', 'avia_framework' );
					throw new Exception( $error_message, 400 );
				}

				$vimeo_api_url = 'https://vimeo.com/api/v2/video/' . $video_id . '.json';

				$vimeo_api = wp_remote_get( $vimeo_api_url );

				if( is_wp_error( $vimeo_api ) || ! is_array( $vimeo_api ) )
				{
					$error_message = __( 'Error: Cannot connect to Vimeo to read thummbnail info.', 'avia_framework' );
					throw new Exception( $error_message, 500 );
				}

				$body = wp_remote_retrieve_body( $vimeo_api );

				$data = json_decode( $body );
				$video_thumb = $data[0]->thumbnail_large;
			}
			else
			{
				$error_message = __( 'Currently only Youtube and Vimeo are supported.', 'avia_framework' );
				throw new Exception( $error_message, 400 );
			}

			/**
			 * @since 5.3
			 * @param string $video_thumb_path
			 * @return String							must contain leading /
			 */
			$video_thumb_path = apply_filters( 'avf_video_thumbnails_path', '/avia_video_thumbnails' );

			//	WP uses $wp_upload_dir['basedir'] to create relative path for attachment metadata in database - do not change \ to /  !!!
			$video_thumb_base_dir = $wp_upload_dir['basedir'] . $avia_config['dynamic_files_upload_folder'] . $video_thumb_path;

			if( ! file_exists( $video_thumb_base_dir ) )
			{
				$temp = str_replace( '\\', '/', $video_thumb_base_dir );
				if( ! avia_backend_create_folder( $temp ) )
				{
					$error_message = sprintf( __( 'Could not create directory [%s] to store downloaded thumbnails.', 'avia_framework' ), $temp );
					throw new Exception( $error_message, 500 );
				}
			}

			$video_attach_title = $video_provider . '-' . $video_id;

			$qa = [
					'post_type'					=> 'attachment',
					'title'						=> $video_attach_title,
					'post_status'				=> 'all',
					'posts_per_page'			=> 1,
					'no_found_rows'				=> true,
					'update_post_term_cache'	=> false,
					'update_post_meta_cache'	=> false,
					'orderby'					=> 'post_date ID',
					'order'						=> 'ASC'
				];

			$attachment_query = new WP_Query( $qa );

			if( ! empty( $attachment_query->post ) )
			{
				$error_message = sprintf( __( 'Video thumbnail already exist in the media library (Attachment ID: %d)', 'avia_framework' ), $attachment_query->post->ID );
				throw new Exception( $error_message, 409 );
			}

			$video_thumb_dir = $video_thumb_base_dir . '/' . $video_provider . '/' . strval( $video_id );

			$temp = str_replace( '\\', '/', $video_thumb_dir );
			if ( ! avia_backend_create_folder( $temp ) )
			{
				$error_message = __( 'Failed to create folder for thumbnails:', 'avia_framework' ) . " [{$video_thumb_dir}]";
				throw new Exception( $error_message, 400 );
			}

			$image_thumb = null;

			if( 'youtube' == $video_provider )
			{
				$image_thumb_found = wp_remote_get( $video_thumb_hq );

				if( ! is_wp_error( $image_thumb_found ) && is_array( $image_thumb_found ) && 200 == $image_thumb_found['response']['code'] )
				{
					$image_thumb = $image_thumb_found;
					$video_thumb = $video_thumb_hq;
				}
			}

			if( is_null( $image_thumb ) )
			{
				$image_thumb = wp_remote_get( $video_thumb );

				if( is_wp_error( $image_thumb ) || ! is_array( $image_thumb ) )
				{
					$error_message = sprintf( __( 'Error: Cannot connect to %s to read thummbnail info.', 'avia_framework' ), $video_provider );
					throw new Exception( $error_message, 500 );
				}

				if( 200 != $image_thumb['response']['code'] )
				{
					throw new Exception( $image_thumb['response']['message'], $image_thumb['response']['code'] );
				}
			}

			$image_type = wp_remote_retrieve_header( $image_thumb, 'content-type' );
			$image_ext = $video_provider == 'vimeo' ? '.' . str_replace( 'image/', '', $image_type ) : '.jpg';

			$image_path = trailingslashit( $video_thumb_dir ) . $video_id . $image_ext;
			$db_image_path = ltrim( str_replace( $wp_upload_dir['basedir'], '', $image_path ), ' \\/' );
			$guid = str_replace( $wp_upload_dir['basedir'], $wp_upload_dir['baseurl'], $image_path );

			$image = file_put_contents( str_replace( '\\', '/', $image_path ), wp_remote_retrieve_body( $image_thumb ) );

			if( false === $image )
			{
				$error_message = sprintf( __( 'Error: Unable to save downloaded thumbnail to [%s].', 'avia_framework' ), str_replace( '\\', '/', $image_path ) );
				throw new Exception( $error_message, 500 );
			}

			$attachment = array(
							'guid'				=> str_replace( '\\', '/', $guid ),
							'post_mime_type'	=> $image_type,
							'post_title'		=> $video_attach_title,
							'post_content'		=> '',
							'post_status'		=> 'inherit'
						);

			$video_attach_id = wp_insert_attachment( $attachment, str_replace( '\\', '/', $db_image_path ) );
			$video_attach_data = wp_generate_attachment_metadata( $video_attach_id, $image_path );
			$video_attach_update = wp_update_attachment_metadata( $video_attach_id, $video_attach_data );

			if( $video_attach_update )
			{
				$video_attach_data['id'] = $video_attach_id;
				$attachments[] = $video_attach_data;
			}
		}
		catch( Exception $ex )
		{
			$error = new WP_Error( '-2', $ex->getMessage() );
			wp_send_json_error( $error, $ex->getCode() );
			exit;
		}

		$message  = __( 'Video thumbnail is now available in the media library:', 'avia_framework' ) . '<br />';
		$message .= sprintf( __( 'Attachment ID: %d', 'avia_framework' ), $video_attach_id ) . '<br />';
		$message .= sprintf( __( 'Original source: %s', 'avia_framework' ), $video_thumb );


		echo wp_send_json( array( 'status' => 'attachment created', 'result' => $attachments, 'message' => $message ), 200 );

		exit;
	}

	add_action( 'wp_ajax_avia_save_video_thumbnails_locally', 'avia_ajax_save_video_thumbnails_locally' );
}
