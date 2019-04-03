<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }	

/*
Description:  	Allows for asset generation/inclusion like css files and js. Also allows to combine files.
				Basic idea: allows us to enqueue scripts and styles and before the files get enqueued individually, 
				we try to generate a compressed version and enqueue this one instead.

Author: 		Kriesi
Since:			4.2.4
*/
	

if ( !class_exists( 'aviaAssetManager' ) ) {

	class aviaAssetManager
	{
		//the database prefix we use to store the script name
		var $db_prefix = "aviaAsset_";
		
		//which files to include by default (all, none, all theme files, only module files)
		var $which_files 	= array('css' => 'avia-module', 'js' => 'avia-module');
		
		//type of files to compress
		var $compress_files = array('css' => true, 'js' => true);
		
		//files to exclude
		var $exclude_files	= array('css' => array('admin-bar','dashicons'), 'js' => array('jquery-core','admin-bar','comment-reply'));
		
		//collect all files to deregister
		var $deregister_files = array('css' => array(), 'js' => array());
		
		//files that will be removed from queue and instead ONLY printed to the head of the page, no matter if they are actually set to be minified
		var $force_print_to_head = array('css' => array('layerslider','layerslider-front'), 'js' => array("avia-compat"));
		
		//files that will be printed to the head in addition to staying enqueued if they are enqueed. Helps with above the fold render blocking
		var $additional_print_to_head = array('css' => array(), 'js' => array());
		
		//file that will be added to the compression, even if the $whichfiles setting does not include them
		var $force_include = array('css' => array(), 'js' => array('wp-embed')); 
		
		//if active the files get regenerated at each page request
		var $testmode 		= false;
		
		public function __construct( $builder ) 
		{
			// allow to change the files that should be merged:
			// 'none' 		 	=> merging is deactivated
			// 'avia-module' 	=> only module files
			// 'avia'			=> all framework files
			// 'all'			=> all enqueued files
			$this->which_files  = apply_filters( 'avf_merge_assets'  , $this->which_files  );
			
			// allow to change the files that should be compressed:
			// true and false allowed
			$this->compress_files  = apply_filters( 'avf_compress_assets'  , $this->compress_files  );
			
			// files that are always excluded like admin bar files
			// files that are processed are added to this list as well, in case another file should be generated
			$this->exclude_files  = apply_filters( 'avf_exclude_assets'  , $this->exclude_files  );
			
			//filter to add file to the list that are force included inline in the head if they are loaded on the page
			$this->force_print_to_head  = apply_filters( 'avf_force_print_asset_to_head'  , $this->force_print_to_head  );
			
			//filter that allows you to tell the script to also print an asset instead of only enqueuing it
			$this->additional_print_to_head  = apply_filters( 'avf_also_print_asset'  , $this->additional_print_to_head  );
			
			//force include an asset, even if the $whichfiles setting does not include it
			$this->force_include  = apply_filters( 'avf_force_include_asset'  , $this->force_include  );


			//before enqueuing the css and js files check if we can serve a compressed version (only frontend)
			add_action('wp_enqueue_scripts', 	array(&$this, 'try_minifying_scripts') , 999999 );
			
			add_action('wp_print_footer_scripts', 	array(&$this, 'minimize_footer_scripts') , 9 );
			
			//if we got any compressed/combined scripts remove the default registered files
			//add_action('wp_enqueue_scripts', 	array(&$this, 'try_deregister_scripts') , 9999999 );
						
			//print assets that are set for printing
			add_action('wp_head', 	array(&$this, 'inline_print_assets') , 20 );
			
		}
		
		
		//default calling of the merging/compression script. the "merge" function can in theory also be called from outside
		public function try_minifying_scripts()
		{
			// check if we got a compressed version that includes all the elements that we need. 
			// generate a new version if the theme version changes or the css files that are included change
			// compare by hash if a new version is required
			
			
			//compresses css files and stores them in a file called avia-merged-styles-HASH_ID.css
			$this->merge('css', 'avia-merged-styles');
			
			//compresses JS files and stores them in a file called avia-head-scripts-HASH_ID.js/avia-footer-scripts-HASH_ID.js// - footer scripts attr: (group 1)
			$this->merge('js',  'avia-head-scripts',   array('groups'=>0));
			
			
		}
		
		//extra hook that allows us to minimze scripts that were added to be printed in wp_footer
		public function minimize_footer_scripts()
		{
			$this->merge('js',  'avia-footer-scripts', array('groups'=>1));
		}


		//function that checks if we can merge a group of files based on the passed params
		public function merge( $file_type , $file_group_name , $conditions = array())
		{
			if( ! isset( $this->which_files[ $file_type ] ) || $this->which_files[$file_type] == "none" )
			{
				return;
			}
			
			//hook into all enqueued styles
			global $wp_styles, $wp_scripts;
			
			//get the data of the file we would generate
			$enqueued	= ($file_type == "css") ? $wp_styles : $wp_scripts;
			$data 		= $this->get_file_data( $file_type , $file_group_name , $enqueued , $conditions );
						
			//check if we got a db entry with this hash. if so, no further merging needed and we can remove the registered files from the enque array
			$generated_files = get_option( $this->db_prefix.$file_group_name );
			if(!is_array($generated_files)) $generated_files = array();
			
			//if the file does not exist try to generate it
			if( empty($generated_files[$data['hash']]) || $this->testmode )
			{
				$generated_files[$data['hash']] = $this->generate_file( $file_type, $data , $enqueued);
			}
			
			//if the file exists and was properly generated at one time in the past, enque the new file and remove all the others. otherwise do nothing
			if($generated_files[$data['hash']] && $generated_files[$data['hash']] !== "error-generating-file")
			{
				if(is_array($data['remove']))
				{
					foreach($data['remove'] as $remove)
					{
						//for future iterations, exlude all files we used here
						$this->exclude_files[$file_type][] = $remove['name'];
						
						//if we know the file content deregister it, otherwise dont do it. might be that the file was not readable
						if($remove['file_content'] !== false)
						{
							$this->deregister_files[$file_type][] = $remove['name'];
						}
					}
				}
				
				$avia_dyn_file_url = $this->get_file_url($data, $file_type);
				
				//if file exists enque it
				if($file_type == 'css')
				{
					wp_enqueue_style( $file_group_name , $avia_dyn_file_url, array(), NULL, 'all' );
				}
				else
				{
					$footer = isset($conditions['groups']) ? $conditions['groups'] : true;
					wp_enqueue_script( $file_group_name , $avia_dyn_file_url, array(), NULL, $footer );
				}
				
			}
			else 
			//if the file was not generated because it was empty there is a chance that it is empty because we want all the script to be printed inline
			//therefore we need to make sure that the force_print_to_head array is checked
			{
				if(is_array($data['remove']))
				{
					foreach($data['remove'] as $remove)
					{
						if($remove['print'] === true)
						{
							//for future iterations, exlude all files we used here
							$this->exclude_files[$file_type][] = $remove['name'];
							$this->deregister_files[$file_type][] = $remove['name'];
						}
					}
				}
			}
			
			
			
			//store that we tried to generate the file but it did not work. therefore no more future tries but simple enqueuing of the single files
			if( empty( $generated_files[$data['hash']] ) )
			{
				$generated_files[$data['hash']] = 'error-generating-file';
				update_option( $this->db_prefix.$file_group_name , $generated_files );
			}
			
			//deregister everything that was compressed
			$this->try_deregister_scripts( $file_group_name );
			
		}
		
		public function get_file_url($data, $file_type)
		{
			$avia_upload_dir = wp_upload_dir();
			if(is_ssl()) $avia_upload_dir['baseurl'] = str_replace("http://", "https://", $avia_upload_dir['baseurl']);

			$url = $avia_upload_dir['baseurl'] . '/dynamic_avia/'.$data['hash'].'.'.$file_type;
			
			return $url;
		}

		
		// returns a file data array with hash, version number and scripts we need to dequeue. 
		// the hash we generate consists of parent theme version, child theme version and files to include. if any of this changes we create a new file
		public function get_file_data( $file_type , $file_group_name, $enqueued , $conditions)
		{			
			$data = array('hash' => '' , 'version' => '' , 'remove' => array(), 'file_group_name' => $file_group_name);
			
			//generate the version number
			$theme 	 		= wp_get_theme();
			$data['version']= $theme->get( 'Version' );
			
			if( false !== $theme->parent() )
			{
				$theme 	 		 = $theme->parent();
				$data['version'] = $theme->get( 'Version' ) . '-' . $data['version'];
			}
			
			//set up the to_do array which has the proper dependencies
			$enqueued->all_deps( $enqueued->queue );
			
			
			//stored files in the db
			$stored_assets 	= get_option( $this->db_prefix.$file_type."_filecontent" );
			if(empty($stored_assets)) $stored_assets = array();
			
			
			//generate the name string for all the files included. store the data of those files so we can properly include them later and then dequeue them
			foreach( $enqueued->to_do as $enqueued_index => $file )
			{
				$force_print = in_array($file, $this->force_print_to_head[$file_type]);
				
				// check which files to include based on the $which_files setting (all, none, modules only, all framework files)
				if( ('all' == $this->which_files[$file_type] ) || 
					('avia-module' == $this->which_files[$file_type] && strpos($file, 'avia-module') !== false ) ||
					('avia' == $this->which_files[$file_type] && strpos($file, 'avia') !== false ) ||
					( $force_print ) ||
					( in_array( $file, $this->force_include[ $file_type ] ) ) 
				   ) 
					{
						//dont use excluded files like admin bar or already used files
						if(in_array($file, $this->exclude_files[$file_type])) continue;
						
						//dont use print stylesheets
						if($enqueued->registered[$file]->args == 'print') continue;
						
						//if a group condition is set check if the file matches
						if( isset($conditions['groups']) && $enqueued->groups[$file] != $conditions['groups'] && !$force_print) continue;
						
						//the file string we need to generate the final hash
						if(!$force_print) $data['hash'] .= $file;
					
						//set up correct path
						//all the files we need to remove from the worpdress queue once we verified that a compressed version is available
						$key = $file."-".$file_type;
						$data['remove'][$key] = array(
							'name' => $file,
							'url'  => $enqueued->registered[$file]->src,
							'path' => $this->set_path($enqueued->registered[$file]->src),
							'print'=> $force_print,
							'type' => $file_type,
							'file_content' => "" //only gets generated on new asset file creation or when a file is not stored in the db and required
						);
						
						/**
						 * @used_by			currently unused
						 * @since 4.5.6
						 * @return array
						 */
						$data = apply_filters( 'avf_asset_mgr_get_file_data', $data, $enqueued_index, $file_type, $file_group_name, $enqueued, $conditions );
						
						
						//check if the file already exists in our database of stored files. if not add it for future re-use
						if( !isset( $stored_assets[$key] ) || $this->testmode)
						{
							$db_update = true;
							$data['remove'][$key]['file_content'] = $this->get_file_content( $data['remove'][$key]['path'] , $file_type , $data['remove'][$key]['url']);
							$stored_assets[$key] = $data['remove'][$key];
						}
		
						//activate to test if we print all assets to body
						//$this->additional_print_to_head[$file_type][] = $file;
					}
			}
			
			
			if(isset($db_update))
			{
				update_option( $this->db_prefix.$file_type."_filecontent" , $stored_assets );
			}
			
			//clean up the todo list
			$enqueued->to_do = array();
			
			//generate a unique hash based on file name string and version number
			$data['hash'] 	= $file_group_name .'-'. md5( $data['hash'] . $data['version'] );
			
			return $data;
		}
		
		
		/**
		 * Return the path to the directory where compressed files are stored excluding / at end
		 * 
		 * @since 4.2.6
		 * @added_by Günter
		 * @return string	
		 */
		public function get_dyn_stylesheet_dir_path()
		{
			$wp_upload_dir  = wp_upload_dir();
		    $stylesheet_dir = $wp_upload_dir['basedir'] . '/dynamic_avia';
		    $stylesheet_dir = str_replace( '\\', '/', $stylesheet_dir );
		    $stylesheet_dir = apply_filters( 'avia_dyn_stylesheet_dir_path',  $stylesheet_dir );
			
			return $stylesheet_dir;
		}
		
		
		//retrieve the content of a css or js file, compress it and return it
		public function get_file_content( $path , $file_type , $fallback_url = "")
		{
			$new_content = false;
			
			//try to retrieve the data by accessing the server
			if( ! empty( $path ) )
			{
				/**
				 * @used_by				currently unused
				 * @since 4.5.6
				 * @return string
				 */
				$check_path = trailingslashit( ABSPATH ) . $path;
				$check_path = apply_filters( 'avf_compress_file_content_path', $check_path, $path , $file_type , $fallback_url );
				
				$new_content = file_get_contents( $check_path );
			}
			
			//we got a file that we cant read, lets try to access it via remote get
			if(gettype($new_content) == "boolean" && $new_content === false)
			{
				if( empty( $fallback_url ) )
				{
					return '';
				}
				
				/**
				 * @used_by				currently unused
				 * @since 4.5.6
				 * @return string
				 */
				$check_fallback_url = apply_filters( 'avf_compress_file_content_fallback_url', $fallback_url, $path , $file_type );
				
				$response = wp_remote_get( esc_url_raw( $check_fallback_url ) );
				
				if( ! is_wp_error( $response ) && ( $response['response']['code'] === 200 ) )
				{
					$new_content = wp_remote_retrieve_body( $response );
				}
			}
			
			//if we still did not retrieve the proper content we dont need to compress the output
			if($new_content !== false)
			{
				$new_content = $this->compress_content($new_content , $file_type, $path);
			}
			else
			{
				$new_content = '';
			}
			
			return $new_content;
		}

	

		//generates the merged and compressed file
		public function generate_file( $file_type , $data , $enqueued)
		{
			$file_created = false;
			
			//try to create a new folder if necessary
			$stylesheet_dir = $this->get_dyn_stylesheet_dir_path();
		    $isdir = avia_backend_create_folder($stylesheet_dir);
			
			//check if we got a folder (either created one or there already was one). if we got one proceed
			if(!$isdir) return false;
			
			$content 	= "";
			
			//iterate over existing styles and save the content so we can add it to the compressed file
			if(is_array($data['remove']))
			{
				$stored_assets = get_option( $this->db_prefix.$file_type."_filecontent");
				
				foreach($data['remove'] as $key => $remove)
				{
					if($remove['path'] != "")
					{
						if(!$remove['print'])
						{
							$content .= $stored_assets[$key]['file_content'];
						}
					}
				}
			}
			
			//create a new file if we got any content
			if(trim($content) != "")
			{
				$file_path		= trailingslashit($stylesheet_dir).$data['hash'].".".$file_type;
				$file_created 	= avia_backend_create_file($file_path, $content);
				
				//double check if the file can be accessed
				if(is_readable($file_path))
				{
					$handle = fopen($file_path, "r");
					$filecontent = fread($handle, filesize($file_path));
					fclose( $handle );
					
					$file 		= $this->get_file_url($data, $file_type);
					$request 	= wp_remote_get($file);
					
					$file_created = false;
					if( ( ! $request instanceof WP_Error ) && is_array( $request ) && isset( $request['body'] ) )
					{
						$request['body'] = trim($request['body']);
						$filecontent = trim($filecontent);
					
						//if the content does not match the file is not accessible
						if($filecontent == $request['body'])
						{
							$file_created = true;
						}
					}
				}
			}
			
			//file creation failed
			if(!$file_created) return false;
			
			//file creation succeeded, store the url of the file
			$generated_files = get_option( $this->db_prefix.$data['file_group_name'] );
			if(!is_array($generated_files)) $generated_files = array();
			$generated_files[$data['hash']] = true;
			
			update_option( $this->db_prefix.$data['file_group_name'] , $generated_files );
			
			//if everything worked out return the new file hash, otherwise return false
			return true;
		}
		
		
		
		//function that removes whitespace and comments, fixes relative urls etc
		public function compress_content( $content , $file_type , $path)
		{
			if('css' == $file_type)
			{
				$content = $this->rel_to_abs_url($content, $path);
								
				if($this->compress_files[$file_type]) 
				{
					$content = $this->css_strip_whitespace($content);
				}
			}
			else 
			{
				if($this->compress_files[$file_type])
				{
					if(version_compare(phpversion(), '5.3', '>=')) 
					{
						include_once 'external/JSqueeze.php';
						
						$jz = new JSqueeze();
						
						$content = $jz->squeeze(
						    $content,
						    true,   // $singleLine
						    false,  // $keepImportantComments
						    false   // $specialVarRx
						);
					}
				}
			}
			
			return $content;
		}
		
		#################
		//switch relative urls in the stylesheet to absolute urls
		public function rel_to_abs_url($content, $path)
		{
			
			// test drive for the regexp : https://regexr.com/3kq8q
			// @since 4.4.2 supports UNICODE characters &#8216; - &#8221;
			
			$this->base_url = trailingslashit(dirname( get_site_url(NULL, $path) ));
			$reg_exUrl 		= '/url\s*?\([\"|\'|\s|\/|\x{2018}|\x{2019}|\x{201C}|\x{201D}]*([^\:]+?)[\"|\'|\s|\x{2018}|\x{2019}|\x{201C}|\x{201D}]*\)/imu';
			
			$content = preg_replace_callback($reg_exUrl, array($this, '_url_callback'), $content);
			
			return $content;
		}
		
		
				//callback function. todo once wp switches to 5.3: make it anonymous again
				//remove ../../ from urls and iterate into higher folder from the baseurl
				public function _url_callback( $match )
				{
					/**
					 * Check if user enters URL to the root directory (starts with / or \ for windows systems)
					 * e.g. /wp-content/themes/enfold/imgages/xxx.jpg
					 *
					 * $match[0]: url( path_to_file )
					 * $match[1]: path_to_file, { ", ', &#8216; - &#8221; } removed and trailing / or \ removed
					 */
					$start = strpos( $match[0], $match[1] );
					$check = substr( $match[0], $start - 1, 1 );
					if( in_array( $check, array( '/', '\\' ) ) )
					{
						return "url('" . trailingslashit( get_home_url() ) . trim( $match[1] ) . "')";
					}
					
					/**
					 * Check if we have already an absolute url
					 * (localhost is a special url - starts with //localhost
					 */
					$base_url = str_replace( array( 'http:', 'https:' ), '', get_home_url() );
					if( ( false !== stripos( $match[1], 'http://' ) ) || ( false !== stripos( $match[1], 'https://' ) ) || ( false !== stripos( $match[0], $base_url ) ) )
					{
						return $match[0];
					}
					else if ( false !== stripos( $match[0], '//localhost' ) )
					{
						return $match[0];
					}
					
					$current_base 	= $this->base_url;
					$segments 		= explode("../", $match[1]);
					$seg_count		= count($segments) - 1;
					
					for($i = $seg_count; $i > 0; $i--)
					{
						$current_base = dirname($current_base);
					}
					
					$new_url = trailingslashit($current_base) . end($segments);
					
					return "url('".$new_url."')";
				}
		
		#################
		
		
		
		public function css_strip_whitespace($css)
		{
		  $replace = array(
		    "#/\*.*?\*/#s" => "",  // Strip C style comments.
		    "#\s\s+#"      => " ", // Strip excess whitespace.
		    "#\t#"		   => ""
		  );
		  $search = array_keys($replace);
		  $css = preg_replace($search, $replace, $css);
		
		  $replace = array(
		    ": "  => ":",
		    "; "  => ";",
		    " {"  => "{",
		    " }"  => "}",
		    ", "  => ",",
		    "{ "  => "{",
		    "{ "  => "{",
		    ";\n"  => ";", // Put all rules from one selector into one line
		    ";}"  => "}", // Strip optional semicolons.
		    ",\n" => ",", // Don't wrap multiple selectors.
		    "\n}" => "}", // Don't wrap closing braces.
		    "{\n" => "{", // Don't wrap the first rule of a selector.
		    //"} "  => "}\n", // Put each rule on it's own line.
		    "\n"  => "", //replace all newlines
		  );
		  
		  $search = array_keys($replace);
		  $css = str_replace($search, $replace, $css);
		
		  return trim($css);
		}
		
		//remove all db keys starting with the $this->db_prefix - this way all files will be generated new on next pageload
		//clean up the generated files in the folder
		public function reset_db_asset_list()
		{
			global $wpdb;
			$results		= $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE option_name LIKE '{$this->db_prefix}%'", OBJECT );
			$stylesheet_dir = $this->get_dyn_stylesheet_dir_path();
			
			foreach($results as $result)
			{
				//delete db option
				delete_option($result->option_name);
				
				
				// remove files. by default they are NOT deleted to not cause any issues with caching plugins.
				// an option in the backend allows to change that setting
				$file_group_name 	= str_replace($this->db_prefix, "", $result->option_name);
				$delete_files 		= apply_filters( 'avf_delete_assets'  , false ); 
				
				//fallback: if the folder gets to large we will clean up older files nevertheless
				$files_to_delete    = array();
				
				foreach(glob($stylesheet_dir.'/'.$file_group_name."*") as $file)
				{
					$files_to_delete[ filemtime($file) ] = $file;
				}
				
				//set the number of files to remove. if delete files is enabled remove all, otherwise only the oldest 5
				$remove_files = 0;
				if(count($files_to_delete) > 100) $remove_files = 5;
				if($delete_files) $remove_files = count($files_to_delete);

				foreach($files_to_delete as $creation => $file)
				{
					if($remove_files > 0)
					{
						unlink($file);
						$remove_files--;
					}
				}
			}
		}
		
		
		//dequeue and deregister scripts
		//update: instead of removing the scripts just remove the src attribute. this way the file does not get loaded but dependencies stay intact
		public function try_deregister_scripts( $attach_data_to = false )
		{
			global $wp_styles, $wp_scripts;
			
			foreach($this->deregister_files as $file_type => $files)
			{
				//get the data of the file we would generate
				$enqueued	= ($file_type == "css") ? $wp_styles : $wp_scripts;
				
				foreach($files as $remove)
				{
					$enqueued->registered[$remove]->src = "";
					
					//the extra data needs to be attached to the compressed file in order to be printed properly
					if($attach_data_to && isset($enqueued->registered[$remove]->extra))
					{
						//first copy the data attribute to our enqueued file
						if(isset($enqueued->registered[$remove]->extra['data']))
						{
							if(!isset($enqueued->registered[$attach_data_to]->extra['data']))
							{
								$enqueued->registered[$attach_data_to]->extra['data'] = "";
							}
							
							$enqueued->registered[$attach_data_to]->extra['data'] .= $enqueued->registered[$remove]->extra['data'];
							// reset data $enqueued->registered[$remove]->extra['data'] = "";
						}
						
						//now merge the before and after arrays
						$before_after = array('before','after');
						
						foreach($before_after as $state)
						{
							if(isset($enqueued->registered[$remove]->extra[$state]))
							{
								if(!isset($enqueued->registered[$attach_data_to]->extra[$state]))
								{
									$enqueued->registered[$attach_data_to]->extra[$state] = array();
								}
								
								
								$a = $enqueued->registered[$attach_data_to]->extra[$state];
								$b = $enqueued->registered[$remove]->extra[$state]; 
								
								$enqueued->registered[$attach_data_to]->extra[$state] = array_merge($a, $b);
							}
						}
					}
				}
				
				/* deprecated
				foreach($files as $remove)
				{
					if($file_type == 'css')
					{
						wp_dequeue_style( $remove );
						wp_deregister_style( $remove );
					}
					else
					{
						wp_dequeue_script( $remove );
						wp_deregister_script( $remove );
					}
				}*/
			}

		}
		
		public function set_path( $registered_path )
		{
			$path =  str_replace(site_url(), "", $registered_path);
			
			if(strpos($path, "//") === 0) //if the path starts with // - eg: used by plugins like woocommerce
			{
				$remove = explode("//", site_url());
				$path = str_replace("//" . $remove[1], "", $registered_path);
			}
			
			$path = ltrim($path, "/");
			
			return $path;
			
		}
		
		
		public function inline_print_assets()
		{

			global $wp_styles, $wp_scripts;
			
			$assets_to_print = array_merge_recursive($this->force_print_to_head, $this->additional_print_to_head);
			$output = "";
			
			foreach($assets_to_print as $file_type => $assets)
			{
				// skip if no assets are set to be printed
				if(empty($assets)) continue;
				
				$stored_assets 	= get_option( $this->db_prefix.$file_type."_filecontent");
				$enqueued		= ($file_type == "css") ? $wp_styles : $wp_scripts;
				$print 			= "";
				
				foreach($assets as $asset)
				{
					//skip if the file is not enqueued
					if(!in_array($asset, $enqueued->queue)) continue;
					
					$print .= $stored_assets[$asset."-".$file_type]['file_content'];
				}
				
				if(!empty($print) && $file_type == "css")
				{
					$output.= '<style type="text/css" media="screen">'.$print.'</style>';
				}
				
				if(!empty($print) && $file_type == "js")
				{
					$output.= '<script type="text/javascript">'.$print.'</script>';
				}
				
			}
			
			if(!empty($output))
			{
				$output = "\n<!-- To speed up the rendering and to display the site as fast as possible to the user we include some styles and scripts for above the fold content inline -->\n" . $output;
				echo $output;
			}
			
			
		}
		
		
		

	} // end class

} // end if !class_exists
