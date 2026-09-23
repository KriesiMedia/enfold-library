<?php
if( ! defined( 'AVIA_FW' ) )	{	exit( 'No direct script access allowed' );	}
/**
 * This file holds the class that creates the options export file for the wordpress importer
 *
 * @since 4.8.2
 *
 * For demo developers: To generate the php file needed add to wp-config.php or functions.php:
 *
 *	- define( 'AVIA_GENERATE_DEMO_PHP_FILE', true );
 *
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright (c) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.1
 * @package 	AviaFramework
 */

/**
 *
 */
if( ! class_exists( 'avia_wp_export', false ) )
{
	class avia_wp_export
	{

		/**
		 *
		 * @var avia_superobject
		 */
		protected $avia_superobject;

		/**
		 *
		 * @var array
		 */
		protected $subpages;

		/**
		 *
		 * @var array
		 */
		protected $options;

		/**
		 *
		 * @var string
		 */
		protected $db_prefix;

		/**
		 *
		 * @param avia_superobject $avia_superobject
		 */
		public function __construct( $avia_superobject )
		{
			if( ! isset( $_GET['avia_export'] ) )
			{
				return;
			}

			if( defined('DOING_AJAX') && DOING_AJAX )
			{
				return;
			}

			/**
			 * WordFence vulnerability report to limit to logged in users (check for current_user_can())
			 *
			 * @since x.x.x
			 */
			if( ! current_user_can( 'manage_options' ) )
			{
				return;
			}

			$this->avia_superobject = $avia_superobject;
			$this->subpages = $avia_superobject->subpages;
			$this->options  = apply_filters( 'avia_filter_global_options_export', $avia_superobject->options );
			$this->db_prefix = $avia_superobject->option_prefix;

			/**
			 * Never ship API keys and secrets in an exported settings/demo file - they end up in a
			 * downloadable ( and, after import, publicly stored ) file. Opt back in with the filter
			 * if you knowingly want a personal backup that keeps your credentials.
			 *
			 * @since 8.1
			 * @param bool $include_secrets
			 * @return bool
			 */
			if( false === apply_filters( 'avf_export_include_secrets', false ) )
			{
				$this->options = $this->redact_secret_options( $this->options );
			}

			add_action( 'admin_init', array( $this, 'initiate' ), 200 );
		}


		/**
		 * Blanks credential option values ( API keys, secret keys, verified-key caches, tracking code )
		 * anywhere in the options array so they are never written to an exported file.
		 *
		 * @since 8.1
		 * @param array $options
		 * @param array|null $secret_ids		internal - the id lookup, built on the first call
		 * @return array
		 */
		protected function redact_secret_options( $options, $secret_ids = null )
		{
			if( null === $secret_ids )
			{
				/**
				 * Option ids whose values are removed from any export.
				 *
				 * @since 8.1
				 * @param string[] $ids
				 * @return string[]
				 */
				$ids = apply_filters( 'avf_export_secret_option_ids', array(
							'mailchimp_api', 'mailchimp_verified_key',
							'gmap_api', 'gmap_verified_key',
							'avia_recaptcha_pkey_v2', 'avia_recaptcha_skey_v2', 'recaptcha_verified_keys_v2',
							'avia_recaptcha_pkey_v3', 'avia_recaptcha_skey_v3', 'recaptcha_verified_keys_v3',
							'avia_turnstile_pkey', 'avia_turnstile_skey', 'avia_turnstile_verify_state',
							'analytics'
						) );

				$secret_ids = array_flip( $ids );
			}

			if( ! is_array( $options ) )
			{
				return $options;
			}

			foreach( $options as $key => $value )
			{
				if( is_array( $value ) )
				{
					$options[ $key ] = $this->redact_secret_options( $value, $secret_ids );
				}
				else if( isset( $secret_ids[ $key ] ) && '' !== (string) $value )
				{
					$options[ $key ] = '';
				}
			}

			return $options;
		}


		/**
		 * @since 4.6.4
		 */
		public function __destruct()
		{
			unset( $this->avia_superobject );
			Unset( $this->subpages );
			unset( $this->options );
		}

		/**
		 * Performs the export
		 */
		public function initiate()
		{

			/**
			 * Returns array of file to export:
			 *		array(
			 *				'name'		=>  ....
			 *				'content'	=>	....
			 *			)
			 *
			 * @used_by			aviaSaveBuilderTemplate				10
			 * @since 4.6.4
			 * @return array|null
			 */
			$export_file = apply_filters( 'avf_generate_export_file', null );
			if( is_array( $export_file ) )
			{
				$name = isset( $export_file['name'] ) ? $export_file['name'] : 'unknown';
				$content = isset( $export_file['content'] ) ? $export_file['content'] : '';

				//	generate downlaod file and exit !!
				$this->generate_export_file( $content, $name );
				exit();
			}

			//get the first subkey of the saved options array
			foreach( $this->subpages as $subpage_key => $subpage )
			{
				$export[ $subpage_key ] = $this->export_array_generator( $this->avia_superobject->option_page_data, $this->options[ $subpage_key ], $subpage );
			}

			//export of options
			$export_serialized = serialize( $export );

			//	check to generate export file for download and exit or php file output
			$download_config = true;
			if( ! isset( $_GET[ 'avia_generate_config_file'] ) )
			{
				$download_config = false;
			}

			if( $download_config && defined( 'AVIA_GENERATE_DEMO_PHP_FILE' ) && true === AVIA_GENERATE_DEMO_PHP_FILE )
			{
				$download_config = false;
			}

			if( $download_config )
			{
				/**
				 * Compress the downloadable Theme Settings export file so it stays small enough to avoid
				 * hosting/WAF request body limits (e.g. HTTP 406 from ModSecurity on async-upload.php)
				 * that a large plain base64 export can trigger.
				 *
				 * @since 8.2
				 */
				$export_download = base64_encode( gzcompress( $export_serialized, 9 ) );

				//	generate downlaod file and exit !!
				$this->generate_export_file( $export_download );
				exit();
			}

			$export = base64_encode( $export_serialized );

			$widget_settings = $this->export_widgets();
			$widget_settings = base64_encode( serialize( $widget_settings ) );

			$fonts = $this->export_option( 'avia_builder_fonts' );

			$nav_menu_locations = $this->export_nav_menu_locations();

			$info = sprintf( __( 'this is a base64 encoded option set created for the demo %s. If you choose to import the demo files with the help of the framework importer these options will also be imported', '' ), THEMENAME );

			$content = '';

//			$content .= "<?php \n\n";
//			$content .= "/*  {$info}  */\n\n";

			$content .= '$options = "';
			$content .=			$export;
			$content .= '";' . "\n";

//			echo '<pre>'."\n";
//			echo '$dynamic_pages = "';
//			print_r( $export_dynamic_pages );
//			echo '";</pre>';
//
//			echo '<pre>'."\n";
//			echo '$dynamic_elements = "';
//			print_r( $export_dynamic_elements );
//			echo '";</pre>';

			$content .= "\n";
			$content .= '$widget_settings = "';
			$content .=			$widget_settings;
			$content .= '";' . "\n";

			if( ! empty( $fonts ) )
			{
				$content .= "\n";
				$content .= '$fonts = "';
				$content .=			$fonts;
				$content .= '";' . "\n";
			}

			if( ! empty( $nav_menu_locations ) )
			{
				$content .= "\n";
				$content .= '$nav_menu_locations = "';
				$content .=			$nav_menu_locations;
				$content .= '";' . "\n";
			}

			if( isset( $_GET['layerslider'] ) )
			{
				$content .= "\n";
				$content .= '$layerslider = "';
				$content .=			$_GET['layerslider'];
				$content .= '";' . "\n";
			}

			//	generate downlaod file and exit !!
			$this->generate_export_file( $content, 'demo-file', 'txt' );
			exit();
		}

		/**
		 * Generates the output file
		 *
		 * @param string $export_data
		 * @param string $which
		 * @param string $ext
		 */
		protected function generate_export_file( $export_data, $which = 'theme-settings', $ext = 'txt' )
		{
			$today = getdate();
			$today_str = $today['year'] . '-' . $today['mon'] . '-' . $today['mday'];

			$export_file = THEMENAME . '-' . $which . '-' . $today_str . '.' . $ext;

			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=' . urlencode( $export_file ) );
			header( 'Content-Type: application/force-download' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Type: application/download' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			print $export_data;

			die();
		}

		/**
		 * Exports the menus assigned to the theme locations.
		 *
		 * Allows a demo to use any menu name - prior to 8.0 a demo had to name the menus
		 * like the theme locations (e.g. "Main Menu") to get them assigned on import.
		 *
		 * Term ids are not stable when the demo is imported, therefore we add slug and name
		 * of the menu to be able to identify it on the users site.
		 *
		 * @since 8.0
		 * @return string					base64 encoded, empty string if no menu is assigned
		 */
		protected function export_nav_menu_locations()
		{
			$locations = get_theme_mod( 'nav_menu_locations' );

			if( empty( $locations ) || ! is_array( $locations ) )
			{
				return '';
			}

			$export = array();

			foreach( $locations as $location => $term_id )
			{
				$menu = wp_get_nav_menu_object( $term_id );

				if( empty( $menu->term_id ) )
				{
					continue;
				}

				$export[ $location ] = array(
									'term_id'	=> (int) $menu->term_id,
									'slug'		=> $menu->slug,
									'name'		=> $menu->name
								);
			}

			if( empty( $export ) )
			{
				return '';
			}

			return base64_encode( serialize( $export ) );
		}

		/**
		 *
		 * @param string $option_name
		 * @return string
		 */
		protected function export_option( $option_name )
		{
			$option = get_option( $option_name  );

			if(!empty($option))
			{
				$option = base64_encode( serialize( $option ) );
			}

			return $option;
		}

		/**
		 *
		 * @return array
		 */
		protected function export_widgets()
		{
			global $wp_registered_widgets;

			$options = array();
			$saved_widgets = array();

			//get all registered widget option names
			foreach( $wp_registered_widgets as $registered )
			{
				if( isset( $registered['callback'] ) && isset( $registered['callback'][0] ) && isset( $registered['callback'][0]->option_name ) )
				{
					$options[] = $registered['callback'][0]->option_name;
				}
			}

			//check if the database options got anything stored but the default value _multiwidget
			foreach( $options as $key )
			{
				$widget = get_option( $key, array() );
				$treshhold = 1;

				if( array_key_exists( '_multiwidget', $widget ) )
				{
					$treshhold = 2;
				}

				if( $treshhold <= count( $widget ) )
				{
					$saved_widgets[ $key ] = $widget;
				}
			}

			//get sidebar positions
			$saved_widgets['sidebars_widgets'] = get_option('sidebars_widgets');

			return $saved_widgets;

		}

		/**
		 *
		 * @param array $elements
		 * @param array $options
		 * @param string $subpage
		 * @param boolean $grouped
		 * @return array
		 */
		protected function export_array_generator( $elements, $options, $subpage, $grouped = false )
		{

			$export = array();

			//iterate over all option page elements
			foreach( $elements as $element )
			{
				if( ( in_array( $element['slug'], $subpage ) || $grouped ) && isset( $element['id'] ) && isset( $options[ $element['id']] ) )
				{
					if( $element['type'] != 'group' )
					{
						if( isset( $element['subtype'] ) && ! is_array( $element['subtype'] ) )
						{
							//pass id-value and subtype
							$taxonomy = false;
							if( isset( $element['taxonomy'] ) )
							{
								$taxonomy = $element['taxonomy'];
							}

							$value = avia_backend_get_post_page_cat_name_by_id( $options[ $element['id'] ] , $element['subtype'], $taxonomy );
						}
						else
						{
							$value = $options[ $element['id'] ];
						}

						if( isset( $value ) )
						{
							$element['std'] = $value;
							$export[ $element['id'] ] = $element;
						}
					}
					else
					{
						$iterations = count( $options[ $element['id'] ] );
						$export[ $element['id'] ] = $element;

						for( $i = 0; $i < $iterations; $i++ )
						{
							$export[ $element['id'] ]['std'][ $i ] = $this->export_array_generator( $element['subelements'], $options[ $element['id'] ][ $i ], $subpage, true );
						}
					}
				}
			}

			return $export;
		}

	}
}


