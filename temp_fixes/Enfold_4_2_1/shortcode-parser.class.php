<?php
/**
 * Handles parsing of shortcode structure and provides ability to repair invalid structure.
 * Also provides logging of errors and repair actions.
 *
 * @since 4.2.1
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'ShortcodeParser' ) )
{
	class ShortcodeParser 
	{
	
		/**
		 * Holds shortcodes that need to be closed - needed when checking and repairing shortcodes
		 * 
		 * @since 4.2.1
		 * @var array 
		 */
		protected $needed_close_tags;
		
		/**
		 * State of the selectbox in dashboard
		 * 
		 * @since 4.2.1
		 * @var string		'disabled' | 'check_only' | 'auto_repair'
		 */
		protected $parser_state;
		
		
		/**
		 *  Location to update the correct post meta holding the shortcode parser information
		 * 
		 * @since 4.2.1
		 * @var string			//	'content' | 'clean_data' | 'preview' / 'none'
		 */
		protected $builder_save_location;

		
		/**
		 * Error counter - found errors when parsing a text
		 * 
		 * @since 4.2.1
		 * @var array 
		 */
		protected $err_counters;
		
		/**
		 * Holds state, if shortcodes could be fixed after parsing
		 * 
		 * @since 4.2.1
		 * @var string				'valid' | 'invalid' | 'undefined' 
		 */
		protected $shortcode_state;
		
		
		/**
		 * Contains a list (= array) of messages:
		 * 
		 *			'type'		=>		'error' | 'warning' | 'update' | 'fatal_error' | 'success'
		 *			'dashboard'	=>		'yes' | 'no'
		 *			'format'	=>		'text' | 'html'
		 *			'message'	=>		message text that describes problem or action
		 *
		 * 	
		 * @since 4.2.1
		 * @var array 
		 */
		protected $parser_messages;

		/**
		 * 
		 * @since 4.2.1
		 * @param string $parser_state
		 */
		public function __construct( $parser_state = 'disabled' ) 
		{
			$this->needed_close_tags = array();
			$this->set_parser_state( $parser_state );
			$this->set_builder_save_location();
			$this->reset_parser_message();
		}
		
		/**
		 * @since 4.2.1
		 */
		public function __destruct() 
		{
			unset( $this->needed_close_tags );
			unset( $this->parser_messages );
			unset( $this->err_counters );
		}
		
		
		/**
		 * 
		 * @since 4.2.1
		 * @param string $new_state
		 */
		public function set_parser_state( $new_state = 'check_only' )
		{
			if( ! in_array( $new_state, array( 'disabled', 'check_only', 'auto_repair' ) ) )
			{
				$new_state = 'disabled';
			}
			
			$this->parser_state = $new_state;
		}
		
		/**
		 * 
		 * @since 4.2.1
		 * @return string
		 */
		public function get_parser_state()
		{
			return $this->parser_state;
		}
		
		/**
		 * 
		 * @since 4.2.1
		 * @param string $new_location			'content' | 'clean_data' | 'preview' / 'none'
		 */
		public function set_builder_save_location( $new_location = 'content' )		
		{
			if( ! in_array( $new_location, array( 'content', 'clean_data', 'preview', 'none' ) ) )
			{
				$new_location = 'content';
			}
			
			$this->builder_save_location = $new_location;
		}
		
		/**
		 * 
		 * @since 4.2.1
		 * @return string
		 */
		public function get_builder_save_location()
		{
			return $this->builder_save_location;
		}
		
		/**
		 * Clears all internal parser messages and counters
		 * 
		 * @since 4.2.1
		 */
		protected function reset_parser_message()
		{
			$this->err_counters = array(
							'warning'		=>	0,
							'error'			=>	0,
							'fatal_error'	=>	0
						);
			$this->parser_messages = array();
			$this->shortcode_state = 'undefined';
		}
		
		
		/**
		 * Adds a message to the message array and increments error counter when message is not for dashboard
		 * 
		 * @since 4.2.1
		 * @param array $message
		 */
		protected function add_parser_message( array $message ) 
		{

			$message = shortcode_atts( array(
									'type'		=>	'error',
									'dashboard'	=>	'no',
									'format'	=>	'text',
									'message'	=>	''
										), $message );
			
			if( ( 'yes' != $message['dashboard'] ) && in_array( $message['type'], array( 'warning', 'error', 'fatal_error' ) ) )
			{
				$this->err_counters[ $message['type'] ] ++;
			}
			
			if( 'yes' == $message['dashboard'] )
			{
				array_unshift( $this->parser_messages, $message );
			}
			else
			{
				$this->parser_messages[] = $message;
			}
		}
		
		
		/**
		 * Saves the created parser message into post meta field
		 * 
		 * @since 4.2.1
		 */
		protected function save_parser_message() 
		{
			
			switch( $this->get_builder_save_location() )
			{
				case 'content':
					$key = '_alb_shortcode_status_content';
					break;
				case 'clean_data':
					$key = '_alb_shortcode_status_clean_data';
					break;
				case 'preview':
					$key = '_alb_shortcode_status_preview';
					break;
				case 'none':
				default:
					$key = '';
					break;
			}
			
			if( empty( $key ) )
			{
				return;
			}
			
			$post_id = get_the_ID();
			if( false === $post_id )
			{			
				return;
			}	
			
			$messages = get_post_meta( $post_id, $key, true );
			
			if( ! is_array( $messages ) )
			{
				$messages = array();
			}
			
			$now = new DateTime();
			
			$new_entry = array(
					'time'				=>	$now->format('Y-m-d H:i:s'),
					'parser_state'		=>	$this->parser_state,
					'shortcode_state'	=>	$this->shortcode_state,
					'errors'			=>	$this->err_counters,
					'messages'			=>	$this->parser_messages
				);
			
			/**
			 * Allow to filter number of saved parser messages for a location
			 * 
			 * @used_by				currently unused
			 * @since 4.2.1
			 */
			$max_msg = apply_filters( 'avf_sc_parser_max_messages', 20, count( $messages ), $this->get_builder_save_location(), $post_id );
				
			if( (int) $max_msg <= 1 )
			{
				$messages = array();
			}
			else
			{
				$messages = array_slice( $messages , 0, ( (int) $max_msg ) - 1 );
			}
			
			array_unshift( $messages, $new_entry );
				
			update_post_meta( $post_id, $key, $messages );
		}
		
		/**
		 * 
		 * @since 4.2.1
		 * @param string $location		'content' | 'clean_data' | 'preview' | '_alb_shortcode_status_content' | '_alb_shortcode_status_clean_data' | '_alb_shortcode_status_preview'
		 * @return array
		 */
		protected function get_parser_message( $location = 'content' )
		{
			switch( $location )
			{
				case '_alb_shortcode_status_content':
				case '_alb_shortcode_status_clean_data':
				case '_alb_shortcode_status_preview':
					$key = $location;
					break;
				case 'content':
					$key = '_alb_shortcode_status_content';
					break;
				case 'clean_data':
					$key = '_alb_shortcode_status_clean_data';
					break;
				case 'preview':
					$key = '_alb_shortcode_status_preview';
					break;
				case 'none':
				default:
					$key = '';
					break;
			}
			
			if( empty( $key ) )
			{
				return array();
			}
			
			$post_id = get_the_ID();
			if( false === $post_id )
			{			
				return array();
			}	
			
			$messages = get_post_meta( $post_id, $key, true );
			
			if( ! is_array( $messages ) )
			{
				return array();
			}
			
			$now = new DateTime();
			
			/**
			 * Make sure we have all fields initialised
			 */
			foreach ( $messages as $index => $message ) 
			{
				$messages[ $index ] =  shortcode_atts( array(
									'time'				=>	$now->format('Y-m-d H:i:s'),
									'parser_state'		=>	$this->parser_state,
									'shortcode_state'	=>	'undefined',
									'errors'			=>	array(
																'warning'		=>	0,
																'error'			=>	0,
																'fatal_error'	=>	0
															),
									'messages'			=>	array()
							), $message );
				
				if( isset( $message['errors'] ) )
				{
					$messages[ $index ]['errors'] =  shortcode_atts( array(
																'warning'		=>	0,
																'error'			=>	0,
																'fatal_error'	=>	0
															), $message['errors'] );
							
				}
				
				foreach( $messages[ $index ]['messages'] as $key => $msg ) 
				{
					$messages[ $index ]['messages'][ $key ] = shortcode_atts( array(
													'type'		=>	'error',
													'dashboard'	=>	'no',
													'format'	=>	'text',
													'message'	=>	''
														), $msg );
				}
			}
			
			return $messages;
		}

		/**
		 * Returns the state of the shortcodes. As user might use "check_only" we can have an unfixed invalid shortcode.
		 * As fallback for old unsafed posts we return 'undefined'.
		 * Reads the info from post meta field
		 *  
		 * @since 4.2.1
		 * @return string				'valid' | 'invalid' | 'undefined' 
		 */
		public function get_shortode_state()
		{
			
			$active = Avia_Builder()->get_alb_builder_status( get_the_ID() );
			switch( $active )
			{
				case 'active':
					$key = '_alb_shortcode_status_clean_data';
					break;
				default:
					$key = '_alb_shortcode_status_content';
					break;
			}
			
			$messages = $this->get_parser_message( $key );
			
			if( empty( $messages ) )
			{
				return 'undefined';
			}
			
			return $messages[0]['shortcode_state'];
		}

		/**
		 * Perform calls to a shortcode parser with check and repair functions depending on shortcode parser flag setting.
		 * The check only flag returns the unmodified text. 
		 * 
		 * @since 4.2.1
		 * @param string $text
		 * @return string
		 */
		public function parse_shortcode( $text )	
		{	
			$this->reset_parser_message();
			
			if( in_array( $this->parser_state, array( 'disabled' ) ) )
			{
				$msg = array(
						'type'		=>	'warning',
						'dashboard'	=>	'yes',
						'message'	=>	__( 'Check of shortcodes was disabled.', 'avia_framework' )
							);
				$this->add_parser_message( $msg );
				
				$this->save_parser_message();
				
				return $text;
			}
			
			
			$original_text = $text;
			$reset_text = false;
			
			try 
			{
				/**
				 * Scan the text and make sure, that shortcodes have a valid structure by adding missing tags.
				 */
				$sc_array = $this->balance_shortcode( $text );
				$text = $this->update_text_from_array( $sc_array, $text );

				/**
				 * As we might have added or updated shortcodes ( in content ) we need to rebuild the array again to get the correct position index
				 */
				$sc_array = $this->balance_shortcode( $text );

				/**
				 * As we now have a valid shortcode structure the layout might have been broken, because we might have added layout elements (e.g. columns).
				 * Here we will fix it.
				 */
				$this->remove_escape_shortcodes_in_array( $sc_array );
				$this->recalculate_layout_array( $sc_array );
				$text =$this->update_text_from_array( $sc_array, $text );
			

				$builder_active = Avia_Builder()->get_alb_builder_status( get_the_ID() );
				if( "active" == $builder_active )
				{
					$text = $this->wrap_plain_textblocks( $text );
				}

				/**
				 * After applying all changes get the final result array
				 */
				$sc_array = $this->balance_shortcode( $text );

				/**
				 * After applying the changes we recheck that WP returns the same result array.
				 * If not, we return the text unchanged and give the user a message that shortcode might be broken in backend ????
				 */
				$wp_array = $this->get_wp_result( $text );
				$this->escape_shortcodes_in_array( $wp_array );
				$this->init_internal_sc_array_values( $wp_array );
			
				$equal = $this->check_wp_result_array( $wp_array, $sc_array );
				$this->shortcode_state = 'valid';
			
				if( ! $equal )
				{
					$text = $original_text;
					$reset_text = true;
					$this->shortcode_state = 'invalid';
				}

				/**
				 * Reset text
				 */
				if( $equal && in_array( $this->parser_state, array( 'check_only' ) ) )
				{
					$text = $original_text;
					$reset_text = true;
					if( ( $this->err_counters['error'] + $this->err_counters['fatal_error'] ) > 0 )
					{
						$this->shortcode_state = 'invalid';
					}
				}
				
			}
			catch( ShortcodeParser_FatalError $ex ) 
			{
				$text = $original_text;
				$reset_text = true;
				$this->shortcode_state = 'invalid';
			}
			
			$err_cnt = $this->err_counters['error'] + $this->err_counters['fatal_error'];
			if( ( $err_cnt + $this->err_counters['warning'] ) > 0 )
			{
				$msg_text = array();
				
				foreach ( $this->err_counters as $key => $err ) 
				{
					switch( $key )
					{
						case 'warning';
							$msg_text[] = sprintf( __( '%d warning(s)', 'avia_framework' ), $err );
							break;
						case 'error';
							$msg_text[] = sprintf( __( '%d error(s)', 'avia_framework' ), $err );
							break;
						case 'fatal_error';
							$msg_text[] = sprintf( __( '%d fatal error(s)', 'avia_framework' ), $err );
							break;
						default:
							break;
					}
				}
				
				$msg = array(
						'type'		=>	'error',
						'dashboard'	=>	'yes',
						'message'	=> implode( ', ', $msg_text ) . ' ' . __( 'were detected parsing the shortcodes.', 'avia_framework' )
							);
			}
			else
			{
				$msg = array(
						'type'		=>	'success',
						'dashboard'	=>	'yes',
						'message'	=>	__( 'No errors were detected parsing the shortcodes.', 'avia_framework' )
							);
			}
			
			if( $reset_text )
			{
				if( $err_cnt > 0 )
				{
					$msg['message'] .= ' ' . __( 'Original content had been kept - necessary changes were not applied.', 'avia_framework' );
				}
			}
			else if( in_array( $this->parser_state, array( 'auto_repair' ) ) )
			{
				if( $err_cnt > 0 )
				{
					$msg['message'] .= ' ' . __( 'Necessary changes were applied to fix all found problems.', 'avia_framework' );
				}
			}
			
			if( $this->shortcode_state == 'valid' )
			{
				$msg['type'] = 'success';
			}
			
			$this->add_parser_message( $msg );
			$this->save_parser_message();
			
			return $text;
        }
				
		
		/**
		 * Scans a given text for shortcodes (defaults to all registered shortcodes) and makes sure, that closing shortcodes are paired to avoid
		 * breaking of layout. Only checks for [/....] and adds missing shortcode tags in text creating empty content (-> [xxx][/xxx]).
		 * Also ignores escaped shortcode and shortcodes inside escaped content.
		 * 
		 * @since 4.2.1
		 * @param string $text
		 * @param array $shortcodes
		 * @return array
		 */
		protected function balance_shortcode( $text, array $shortcodes = array() )
		{
			global $shortcode_tags;
			
			$sc_found = array();
			
			if( '' == trim( $text ) )
			{
				return $sc_found;
			}
				
			/**
			 * We take all registered shortcodes by default
			 */
			if( empty( $shortcodes ) )
			{
				$shortcodes = array_keys( $shortcode_tags );
			}
			
			/**
			 * Now find all shortcode tags [foo  [/foo]
			 * 
			 */
			preg_match_all( "/" . ShortcodeHelper::get_fake_pattern( false, $shortcodes ) . "/s", $text, $sc_found, PREG_OFFSET_CAPTURE );
			
			if( empty( $sc_found ) || ! is_array( $sc_found ) || empty( $sc_found[0]) )
			{
				return array();
			}
			
			/**
			 * As we might need to add missing closing shortcode tags or check for escaped shortcodes we need the complete opening tags so 
			 * we can calculate the position where to insert or update in $text
			 */
			$sc_found_all = array();
			preg_match_all( "/" . ShortcodeHelper::get_fake_pattern( false, $shortcodes, 'complete' ) . "/s", $text, $sc_found_all, PREG_OFFSET_CAPTURE );
			
			$all = array();
			
			if( ! empty( $sc_found_all ) &&  is_array( $sc_found_all ) )
			{
				foreach ( $sc_found_all[0] as $code ) 
				{
					$all[ $code[1] ] = $code[0];
				}
			}
			
			/**
			 * Now we merge the complete tags into the first result.
			 */
			foreach ( $sc_found[0] as &$code ) 
			{
				if( isset( $all[ $code[1] ] ) )
				{
					$code['tag'] = $all[ $code[1] ];
					unset( $all[ $code[1] ] );
				}
			}
			
			unset( $code );
			
			/**
			 * If user entered all shortcodes correctly ( [foo ... ] ), the second regex should have found all and
			 * each entry of the first result must have a "tag" entry.
			 * 
			 * If not, this will fix it by adding the missing "]" right after the shortcode.
			 */
			$this->fix_invalid_shortcodes_in_array( $sc_found[0], $all );
			$sc_array = $this->balance_shortcode_array( $sc_found[0] );
			
			return $sc_array;
		}
		

		/**
		 * Fixes invalid shortcode entries so the WP shortcode parser and our internal created structure can be synchronised
		 * 
		 * e.g. User entered a registered shortcode without a closing square bracket ( something like [foo .... ).
		 * We fix this by adding the closing square bracket right after the code
		 *  
		 * @since 4.2.1
		 * @param array &$shortcodes
		 * @param array &$errors
		 */
		protected function fix_invalid_shortcodes_in_array( array &$shortcodes, array &$errors )
		{
			if( empty( $shortcodes ) && empty( $errors ) )
			{
				return;
			}
			
			/**
			 * Unclosed close shortcode tags remain in $errors, because they are not recognised by default shortcode pattern.
			 * Also self closing tags with /] are not recognised
			 * We have to insert them here to be able to correct them and correctly balance the shortcodes
			 */
			foreach ( $errors as $index => $tag ) 
			{
				$insert_at = -1;
				foreach ( $shortcodes as $key => $shortcode ) 
				{
					if( $shortcode[1] > $index )
					{
						$insert_at = $key;
						break;
					}
				}
				
				/**
				 * Capture all self closing tags [foo /] [foo/]:		\[{1,2}[\/]?.*?\/\]{1,2}
				 * Capture all characters till first blank or newline : \[{1,2}[\/]?.*?(?=[\s])
				 */
				$match = array();
				preg_match_all( "/\[{1,2}[\/]?.*?\/\]{1,2}|\[{1,2}[\/]?.*?(?=[\s])/s", $tag, $match, PREG_OFFSET_CAPTURE );
				
				
				/**
				 * Fallback - something we cannot deal with
				 */
				if( empty( $match ) || empty( $match[0] ) )
				{
					continue;
				}
				
				$new_tag = $match[0][0][0];
				
				if( false !== strpos( $new_tag, '/]') )
				{
					$new_tag_0 = str_replace( array( ']', '/' ), '', $new_tag );
					$new_tag_repaired = $new_tag;
				}
				else
				{
					$new_tag_0 = $new_tag_repaired = ( false === strpos( $new_tag, '[[' ) ) ? $new_tag . ']' : $new_tag . ']]';
				}
				
				$new = array(
					0			=>	$new_tag_0,
					1			=>	$index,
					'update'	=>	$new_tag,
					'tag'		=>	$new_tag_repaired
				);
				
				if( $insert_at < 0 )
				{
					$shortcodes[] = $new;
					
					if( in_array( $this->parser_state, array( 'auto_repair' ) ) )
					{
						$msg_tx = sprintf( __( 'Registered shortcode %s detected without closing ] at index %d. To fix it shortcode was added: %s', 'avia_framework' ), $new_tag, $index, $new_tag_repaired );
					}
					else
					{
						$msg_tx = sprintf( __( 'Registered shortcode %s detected without closing ] at index %d. To fix add shortcode: %s', 'avia_framework' ), $new_tag, $index, $new_tag_repaired );
					}
					
					$msg = array(
							'type'		=>	'error',
							'dashboard'	=>	'no',
							'message'	=>	$msg_tx
								);
					
					$this->add_parser_message( $msg );
				}
				else
				{
					array_splice( $shortcodes, $insert_at, 0, array( $new ) );
					
					if( in_array( $this->parser_state, array( 'auto_repair' ) ) )
					{
						$msg_tx = sprintf( __( 'Registered shortcode %s detected without closing ] at index %d. To fix it shortcode was be updated with %s', 'avia_framework' ), $new_tag, $index, $new_tag_repaired );
					}
					else
					{
						$msg_tx = sprintf( __( 'Registered shortcode %s detected without closing ] at index %d. To fix it update Shortcode with %s', 'avia_framework' ), $new_tag, $index, $new_tag_repaired );
					}
					
					$msg = array(
							'type'		=>	'error',
							'dashboard'	=>	'no',
							'message'	=>	$msg_tx
								);
					
					$this->add_parser_message( $msg );
				}
			}
			
			foreach ( $shortcodes as $key => $shortcode ) 
			{
				/**
				 * Check for a normal situation that complete tag has ]
				 */
				if( isset( $shortcode['tag'] ) )
				{
					$code = trim( $shortcode['tag'] );
					if( false !== strpos( $code, ']' ) )
					{
						continue;
					}
				}
				
				/**
				 * Error situation:
				 * 
				 * User entered a registered shortcode without ]. If tag is set, then we found [, else tag is not set.
				 */
				$code = trim( $shortcode[0] );
				$shortcodes[ $key ]['update'] = $code;
				$shortcodes[ $key ]['tag'] = ( false === strpos( $code, '[[' ) ) ? $code . ']' : $code . ']]';
				
				if( in_array( $this->parser_state, array( 'auto_repair' ) ) )
				{
					$msg_tx = sprintf( __( 'Registered shortcode %s detected without closing ] at index %d. Fixed by closing Shortcode without attributes.', 'avia_framework' ), $code, $shortcode[1] );
				}
				else
				{
					$msg_tx = sprintf( __( 'Registered shortcode %s detected without closing ] at index %d. To fix it close shortcode (check for attributes to include).', 'avia_framework' ), $code, $shortcode[1] );
				}
				$msg = array(
							'type'		=>	'error',
							'dashboard'	=>	'no',
							'message'	=>	$msg_tx
								);
					
				$this->add_parser_message( $msg );
			}
			
			return;
		}
		

		/**
		 * Scans the result array from preg_match_all( ShortcodeHelper::get_fake_pattern ) and inserts missing shortcode tags in the array.
		 * Inserts missing tags for a valid shortcode structure that can be merged to text later. Nested elements are supported.
		 * Opening and closing tags are inserted, whatever is necessary.
		 * 
		 * Only when no tags had been added, internal flags can be set properly that allow an easy comparison with WP result array later.
		 * 
		 * Shortcodes inside escaped shortcode areas are not changed and escaped shortcodes are marked.
		 * 
		 * Depending on $add_missing:
		 * 
		 *		'default':		Checks that all closing tags are paired
		 *		'forced':		Additionally all non closing tags are closed to get a valid XML structure (only works when a correct structured array is rendered) 
		 * 
		 * 
		 * 
		 * @since 4.2.1
		 * @param array $shortcodes			result from preg_match_all( ... , PREG_OFFSET_CAPTURE )
		 * @param string $add_missing		'default' | 'forced'
		 * @return array
		 */
		public function balance_shortcode_array( array $shortcodes, $add_missing = 'default' )
		{
			if( empty( $shortcodes ) )
			{
				return $shortcodes;
			}
			
			$this->escape_shortcodes_in_array( $shortcodes );
			
			/**
			 * Set all needed closing tags and get the position of the current existing closing tags
			 */
			$sc_close_array = $this->get_closing_tags_array( $shortcodes );
			
			
			/**
			 * Now filter for tags that are not self closing and do not have a
			 * closing tag at all in text (= wrong structured elements)
			 */
			$missing_close = array_flip( $this->needed_close_tags );
			foreach ( $sc_close_array as $key => $value ) 
			{
				$val = trim( str_replace( array( ']', '/]' ), '', $value[0] ) );
				if( array_key_exists( $val, $missing_close ) )
				{
					unset( $missing_close[ $val ] );
				}
			}

			
			/**
			 * Now we must add these forgotten closing tags to the opening tags
			 */
			foreach ( $shortcodes as $key => $shortcode ) 
			{
				$test = '[/' . $shortcode['sc'];
				
				if( array_key_exists( $test, $missing_close ) )
				{
					$shortcodes[ $key ]['add_sc'] = 'close';
				}
			}
			
			
			/**
			 * Now we scan all existing closing tags and check the opening tags. 
			 * We scan backwards because we also need to check for opening tags after the last closing tag
			 */
			$sc_checked = array();
			while( ! empty( $sc_close_array ) )
			{
				end( $sc_close_array );
				$key = key( $sc_close_array );
				$code = array_pop( $sc_close_array );
				
				$sc_open = trim( str_replace( array( '[/', ']' ), array( '[', '' ), $code[0] ) );
				$sc_close = trim( $code[0] );
				
				/**
				 * If we find a new closing shortcode tag, we also need to check for opening tags after the last closing tag to fix them with closing tags
				 */
				if( ! in_array( $sc_close, $sc_checked ) )
				{
					$last = count( $shortcodes );
					
					/**
					 * We have a single shortcode that needs to be closed - we must scan the complete shortcode array
					 */
					if( ! is_numeric( $key ) )
					{
						$key = $last;
					}
					
					for( $i = $key + 1; $i < $last; $i++ )
					{
						$check = $shortcodes[ $i ];
						
						if( isset( $check['escaped'] ) && ( 'yes' == $check['escaped'] ) )
						{
							continue;
						}
						
						/**
						 * Fix that [foo] is stored as [foo] but [foo is expected ( = opening tag without any attributes)
						 * Also fix old style [foo/]
						 */
						$check_sc = trim( str_replace( array( '/]', ']' ), array( '' ), $check[0] ) );
						
						if( $sc_open == $check_sc )
						{
							$shortcodes[ $i ]['add_sc'] = 'close';
						}
					}
					
					$sc_checked[] = $sc_close;
				}
				
				/**
				 * Check for corresponding opening/closing tags before the current closing tag
				 */
				$found_open = false;
				
				for( $i = $key - 1; $i >= 0; $i-- )
				{
					$check = $shortcodes[ $i ];
					
					if( isset( $check['escaped'] ) && ( 'yes' == $check['escaped'] ) )
					{
						continue;
					}

					/**
					 * Fix old style [foo/]
					 */
					$check_sc = trim( str_replace( '/]', '', $check[0] ) );
					
					/**
					 * Fix that [foo] is stored as [foo] but [foo is expected ( = opening tag without any attributes)
					 */
					if( false === strpos( $check_sc, '/' ) )
					{
						$check_sc = str_replace( ']', '', $check_sc );
					}
					
					if( $sc_open == $check_sc )
					{
						if( false === $found_open )
						{
							$found_open = $i;
						}
						else 
						{
							$shortcodes[ $i ]['add_sc'] = 'close';
						}
						continue;
					}
					
					if( $sc_close == $check_sc )
					{	
						if( false === $found_open )
						{
							$shortcodes[ $key ]['add_sc'] = 'open';
							$found_open = $key;
							break;
						}
						else 
						{
							break;
						}
					}
				}
				
				/**
				 * We started with a closing tag 
				 */
				if( false === $found_open )
				{
					$shortcodes[ $key ]['add_sc'] = 'open';
				}
			}
			
			/**
			 * Report summary of missing tags
			 */
			$err_tags = array();
			foreach ( $shortcodes as $key => $shortcode ) 
			{
				if( ! isset( $shortcode['add_sc'] ) )
				{
					continue;
				}
				
				if( 'open' == $shortcode['add_sc'] )
				{
					$err_tags[] = sprintf( __( 'Missing opening tag [%s] found for a closing tag at index %d.', 'avia_framework' ), $shortcode['sc'], $shortcode[1] );
				}
				else if( 'close' == $shortcode['add_sc'] )
				{
					$err_tags[] = sprintf( __( 'Missing closing tag [/%s] found for an opening tag at index %d.', 'avia_framework' ), $shortcode['sc'], $shortcode[1] );
				}
				else
				{
					$err_tags[] = sprintf( __( 'Error found for tag %s at index %d: add_sc = "%s".', 'avia_framework' ), $shortcode['tag'], $shortcode[1], $shortcode['add_sc'] );
				}
			}
			
			if( ! empty( $err_tags ) )
			{
				$msg = '';
				
				$msg .=		'<div class="av-parser-html-headline">';
				$msg .=			__( 'Summary of missing tags:', 'avia_framework' );
				$msg .=		'</div>';
				
				$msg .=		'<div class="av-parser-html-list">';
				$msg .=			'<ul>';

				foreach ( $err_tags as $value )
				{
					$msg .=			'<li>' . $value . '</li>';
				}
				
				$msg .=			'</ul>';
				$msg .=		'</div>';
				
				$arg = array(
							'type'		=>	'error',
							'dashboard'	=>	'no',
							'message'	=>	$msg
								);
					
				$this->add_parser_message( $arg );
			}
			
			$this->init_internal_sc_array_values( $shortcodes );
			
			/**
			 * Now we know where we have to insert the balancing shortcodes.
			 * But we have to pay attention to nested elements - in that case
			 * we have to include the adjacent parent/children tags or we have to insert empty child tags also.
			 * Otherwise we might break the backend when editing.
			 * 
			 * Nested shortcodes can be skipped, but layout_children have to be added.
			 */
			$this->check_nested_element_structures( $shortcodes );
			
			
			/**
			 * Now we have all errors and changes in $shortcodes
			 * We can try to apply them and add it to the report log
			 */
			
			$new_shortcodes = array();
			foreach ( $shortcodes as $sc ) 
			{
				if( ! ( isset( $sc['add_sc'] ) || isset( $sc['add_new_sc'] ) ) )
				{
					$new_shortcodes[] = $sc;
					continue;
				}
				
				$code = trim( str_replace( array( '[/', ']' ), array( '[', '' ), $sc[0] ) );
				
				$before = '';
				$after = '';
				
				/**
				 * We have to add a simple tag
				 */
				if( isset( $sc['add_sc'] ) )
				{
					switch( $sc['add_sc'] )
					{
						case 'open':
							$before .= $code . ']';
							break;
						case 'close':
							/**
							 * Do not add a close tag if we have an old style opening tag [foo/] or [foo /]
							 */
							if( isset( $sc['tag'] ) && ( false !== strpos( $sc['tag'], '/]' ) ) )
							{
								break;
							}
							
							$after .= str_replace( '[', '[/', $code ) . ']';
							break;
						default:
							break;
					}
				}
				
				/**
				 * We have to add tags needed by nested shortcodes
				 */
				if( isset( $sc['add_new_sc'] ) )
				{
					if( isset( $sc['add_new_tag_before'] ) )
					{
						$before = $sc['add_new_tag_before'] . $before;
					}
					
					if( isset( $sc['add_new_tag_after'] ) )
					{
						$after .= $sc['add_new_tag_after'];
					}
				}
				
				unset( $sc['add_sc'] );
				unset( $sc['add_new_sc'] );
				unset( $sc['add_new_tag_before'] );
				unset( $sc['add_new_tag_after'] );
				
				if( empty( $before ) && empty( $after ) )
				{
					$new_shortcodes[] = $sc;
					continue;
				}
				
				/**
				 * Add a shortcode before the current
				 */
				if( ! empty( $before ) )
				{
					$new_shortcodes[] = array( 
									0			=> $before,
									- $sc[1],
									'add_sc'	=> 'at_index',
									'tag'		=> $sc['tag']
							);
					
					if( in_array( $this->parser_state, array( 'auto_repair' ) ) )
					{
						$msg_tx = sprintf( __( 'Invalid shortcode was detected. Fixed by adding %s at index %d.', 'avia_framework' ), $before, $sc[1] );
					}
					else
					{
						$msg_tx = sprintf( __( 'Invalid shortcode was detected. To fix add %s at index %d.', 'avia_framework' ), $before, $sc[1] );
					}
					
					$msg = array(
							'type'		=>	'error',
							'dashboard'	=>	'no',
							'message'	=>	$msg_tx
								);
					
					$this->add_parser_message( $msg );
				}
				
				$new_shortcodes[] = $sc;
				
				/**
				 * Add a shortcode after the current
				 */
				if( ! empty( $after ) )
				{
					$new_entry = array( 
									0	=> $after
								);
					$new = $sc[1];
					if( isset( $sc['tag'] ) )
					{
						$new += strlen( $sc['tag'] );
						$new_entry[1] = - $new;
						$new_entry['add_sc'] = 'at_index';
					}
					else
					{
						//	fallback only if we do not have the complete opening tag
						$new_entry[1] = - $sc[1];
						$new_entry['add_sc'] = 'after_tag';
					}
					
					$new_entry['tag'] = $sc['tag'];
					$new_shortcodes[] = $new_entry;
					
					if( in_array( $this->parser_state, array( 'auto_repair' ) ) )
					{
						$msg_tx = sprintf( __( 'Invalid shortcode was detected. Fixed by adding %s at index %d.', 'avia_framework' ), $after, $new );
					}
					else
					{
						$msg_tx = sprintf( __( 'Invalid shortcode was detected. To fix add %s at index %d.', 'avia_framework' ), $after, $new );
					}
					
					$msg = array(
							'type'		=>	'error',
							'dashboard'	=>	'no',
							'message'	=>	$msg_tx
								);
					$this->add_parser_message( $msg );
				}
			}
			
			/**
			 * Now we got all missing tags in the array structure but we are not always able to set closing state -
			 * Problem is we might have to add nested structures like [foo][foo][/bar].
			 * 
			 * The result is only reliable when no shortcodes were added.
			 * 
			 * Subsequent call to this function after modifying the text are necessary.
			 */
			$this->init_internal_sc_array_values( $new_shortcodes );
			
			
			if( 'default' == $add_missing )
			{
				return $new_shortcodes;
			}

			/**
			 * Close all elements that are not self closing to generate a valid xml string	
			 * 
			 * ------>>>>	I M P O R T A N T: Nesting of same named shortcodes is not possible (and not supported by WP).
			 *				If this changes in future versions of WP, the following code has to be adjusted.
			 */
			$current = 0;
			
			while ( $current < count( $new_shortcodes ) )
			{
				$code = $new_shortcodes[ $current ];
				
				if( isset( $code['escaped'] ) && ( 'yes' == $code['escaped'] ) )
				{
					$current ++;
					continue;
				}
				
				$sc = trim( $code[0] );
			
				if( strpos( $sc, '/' ) !== false )
				{
					$current ++;
					continue;
				}
				
				$new_shortcodes[ $current ]['close'] = 'tag';
				$closing = str_replace( '[','[/', $sc ) . ']';
				
				$search = $current + 1;
				$auto_close = true;
				
				while ( $search < count( $new_shortcodes ) )
				{
					$code_search = $new_shortcodes[ $search ];
					
					if( isset( $code_search['escaped'] ) && ( 'yes' == $code_search['escaped'] ) )
					{
						$search ++;
						continue;
					}
					
					$sc_test = trim( $code_search[0] );
					if( $sc == $sc_test )
					{
						break;
					}
					
					if( $closing == $sc_test )
					{
						$auto_close = false;
						break;
					}
					
					$search ++;
				}
				
				if( $auto_close ) //if we got no closing tag add a temp one
				{
					$new_entry = array( $closing );
					$new = $code[1];
					if( isset( $code['tag'] ) )
					{
						$new_entry[] = - ( $code[1] + strlen( $code['tag'] ) );
						$new_entry['add_sc'] = 'open';
					}
					else
					{
						//	fallback only if we do not have the complete opening tag
						$new_entry[] = - $code[1];
						$new_entry['add_sc'] = 'close';
					}
					$new_entry['close'] = 'self';
					$new_shortcodes = array_merge( array_slice( $new_shortcodes, 0, $current + 1 ), array( $new_entry ), array_slice( $new_shortcodes, $current + 1 ) );  
				}
				
				$current ++;
			}
			
			return $new_shortcodes;
		}
		
		
		/**
		 * Gets an array of opening and closing shortcode tags extracted from a page content or _aviaLayoutBuilderCleanData.
		 * As WP ignores escaped shortcodes and all shortcodes inside escaped shortcode we mark any found tags as not needed.
		 * 
		 * see https://codex.wordpress.org/Shortcode_API#Escaping for correct syntax:
		 * 
		 *		- [[caption param="test"]]
		 *		- [[caption]My Caption[/caption]]
		 * 
		 * This might change (??) in future versions of WP, so keep an eye on that behaviour
		 * 
		 * Make sure to call ShortcodeParser::fix_invalid_shortcodes_in_array before calling this function
		 * 
		 * @since 4.2.1
		 * @param array $shortcodes
		 */
		protected function escape_shortcodes_in_array( array &$shortcodes )
		{
			$count_sc = count( $shortcodes );
			$current = 0;
			
			while ( $current < $count_sc )
			{
				$code = $shortcodes[ $current ];
				
				/**
				 * Continue till we find an escaped shortcode
				 */
				if( false === strpos( $code[0], '[[' ) )
				{
					$current++;
					continue;
				}
				
				/**
				 * Check for self closing escaped shortcode
				 */
				if( false !== strpos( $code['tag'], ']]' ) )
				{
					$shortcodes[ $current ]['escaped'] = 'yes';
					$current++;
					continue;
				}
				
				/**
				 * Now we need to scan for corresponding closing tag ignoring all shortcodes inside.
				 * If we do not find a closing tag we create a self closing tag
				 */
				$sc_open_esc = trim( $code[0] );
				$sc_open = str_replace( '[[', '[', $sc_open_esc );
				$sc_close = str_replace( '[[', '[/', $sc_open_esc ) . ']';
				$sc_close_esc = $sc_close . ']';
				
				$open = 0;
				$last = $current;
				for( $i = $current + 1; $i < $count_sc; $i++ )
				{
					$code_test = $shortcodes[ $i ];
					$sc_test = trim( $code_test[0] );
					
					if( $sc_open_esc == $sc_test )
					{
						$open++;
						continue;
					}
					
					if( $sc_close_esc == $sc_test )
					{
						if( $open > 0 )
						{
							$open--;
							continue;
						}
						$last = $i;
						break;
					}
				}
				
				if( $open > 0 )
				{
					/**
					 * We found an unclosed escaped shortcode - we replace the opening tag with a self closing escaped shortcode tag
					 */
					$shortcodes[ $current ]['update'] = $shortcodes[ $current ]['tag'];
					$shortcodes[ $current ]['tag'] .= ']';
					$shortcodes[ $current ]['escaped'] = 'yes';
					$current++;
					continue;
				}
				
				for( $i = $current; $i <= $last; $i++ )
				{
					$shortcodes[ $i ]['escaped'] = 'yes';
				}
				
				$current = $last + 1;
			}
			
			return $shortcodes;
		}
						
		/**
		 * Removes all escaped entries from array
		 * 
		 * @since 4.2.1
		 * @param array $shortcodes
		 */
		public function remove_escape_shortcodes_in_array( array &$shortcodes )
		{
			foreach( $shortcodes as $key => $shortcode ) 
			{
				if( isset( $shortcode['escaped'] ) && ( 'yes' == $shortcode['escaped'] ) )
				{
					unset( $shortcodes[ $key ] );
				}
			}
		}
				
		/**
		 * Removes shortcodes from array that are NOT in $sc_tags_to_keep.
		 * Make sure to remove all escaped entries and that the shortcode structure
		 * is balanced.
		 * 
		 * @since 4.2.1
		 * @param array $shortcodes
		 * @param array $sc_tags_to_keep
		 */
		public function filter_shortcodes_in_array( array &$shortcodes, array $sc_tags_to_keep )
		{
			foreach( $shortcodes as $key => $shortcode ) 
			{
				$sc = trim( str_replace( array( '[', ']', '/' ), '', $shortcode[0] ) );
				if( ! in_array( $sc, $sc_tags_to_keep) )
				{
					unset( $shortcodes[ $key ] );
				}
			}
			
		}
		
		
		/**
		 * Returns the surrounding parent class, if $shortcodes[ $key ] is a nested ALB shortcode
		 * In case we have an invalid structure (opening and closing missing) we return the first parent in list
		 * 
		 * @since 4.2.1
		 * @param array $shortcodes
		 * @param string $key
		 * @return aviaShortcodeTemplate|false
		 */
		protected function find_surrounding_parent_class( array &$shortcodes, $key )
		{						
			$builder = Avia_Builder();
			$shortcode = $shortcodes[ $key ];
			
			if( ! in_array( $shortcode['sc'], ShortcodeHelper::$nested_shortcodes ) )
			{
				return false;
			}
			
			$parents = $builder->get_sc_parents_from_tag( $shortcode['tag'] );
			
			if( empty( $parents ) )
			{
				return false;
			}
			
			$cnt_parents = count( $parents );
			if( 1 == $cnt_parents )
			{
				return $builder->get_shortcode_class( $parents[0] );
			}
			
			/**
			 * First we check if we find an opening tag
			 */
			$i = $key - 1;
			while( $i >= 0 )
			{
				$check = $shortcodes[ $i ];
				
				if( isset( $check['escaped'] ) && ( 'yes' == $check['escaped'] ) )
				{
					$i--;
					continue;
				}
						
				if( in_array( $check['sc'], $parents ) )
				{
					if( false === strpos( $check[0], '[/' ) )
					{
						return $builder->get_shortcode_class( $check['sc'] );
					}
					break;
				}
				
				$i--;
			}
			
			/**
			 * Now we have to check for a closing tag
			 */
			$i = $key + 1;
			while( $i < $cnt_parents )
			{
				$check = $shortcodes[ $i ];
				
				if( isset( $check['escaped'] ) && ( 'yes' == $check['escaped'] ) )
				{
					$i++;
					continue;
				}
				
				if( in_array( $check['sc'], $parents ) )
				{
					if( false !== strpos( $check[0], '[/' ) )
					{
						return $builder->get_shortcode_class( $check['sc'] );
					}
					break;
				}
				
				$i++;
			}
			
			/**
			 * We assume the first parent in list as we will add it when repairing
			 */
			return $builder->get_shortcode_class( $parents[0] );
		}

		/**
		 * Scan the shortcode array, create an array of required closing tags and return an array
		 * with currently existing closing tags
		 * 
		 * @since 4.2.1
		 * @param array $shortcodes
		 * @return array
		 */
		protected function &get_closing_tags_array( array &$shortcodes )
		{
			$builder = Avia_Builder();
			
			$this->needed_close_tags = array();
			$sc_close_array = array();
			
			foreach ( $shortcodes as $key => $shortcode ) 
			{
				$code = $builder->extract_shortcode_from_tag( $shortcode[0] );
				$shortcodes[ $key ]['sc'] = $code;
				
				if( isset( $shortcode['escaped'] ) && ( 'yes' == $shortcode['escaped'] ) )
				{
					continue;
				}
				
				/**
				 * Get all elements that must have a closing tag (when user enters a single shortcode and forgets to close it we do not find an end tag in our array !! )
				 * For shortcodes that have no class (= nested shortcodes) we currently have to assume a closing tag, otherwise we might break editing in backend. 
				 * Future implementation might add a callback to get this information
				 */
				$class = $builder->get_sc_class_from_tag( $shortcode['tag'] );
				$check = '[/' . $code;
				
				if( false === $class )
				{
					/**
					 * Nested shortcodes need not have a class or it is a non ALB shortcode
					 */
					$parent_class = $this->find_surrounding_parent_class( $shortcodes, $key );
					if( ( false !== $parent_class ) && ( ! $parent_class->is_nested_self_closing( $shortcodes[ $key ]['sc'] ) ) )
					{
						$this->needed_close_tags[] = $check;
					}
				}
				else if( ! $class->is_self_closing() )
				{
					$this->needed_close_tags[] = $check;
				}
				
				/**
				 * Get the positions of closing tags in array
				 */
				if( strpos( $shortcode[0], '[/' ) !== false )
				{
					$sc_close_array[ $key ] = $shortcode;
					
					/**
					 * This is a fallback for nested elements (no shortcode class) or elements that return is_self_closing incorrect
					 * or when user enters shortcode where we do not have any info about its structure
					 */
					$this->needed_close_tags[] = $check;
				}
			}
			
			/**
			 * Remove double entries and reindex
			 */
			$this->needed_close_tags = array_merge( array_unique( $this->needed_close_tags ) );
			
			return $sc_close_array;
		}
		
		
		/**
		 * Apply or update internal array values to make it easier for comparison and checking
		 * $this->needed_close_tags must be properly initialised with all needed close tags for this array
		 * 
		 * @since 4.2.1
		 * @param array $sc_array
		 */
		protected function init_internal_sc_array_values( array &$sc_array )
		{
			$builder = Avia_Builder();
			
			/**
			 * Set is_close_tag flag
			 */
			foreach ( $sc_array as $key => &$sc ) 
			{
				if( isset( $sc['escaped'] ) && ( 'yes' == $sc['escaped'] ) )
				{
					continue;
				}

				$sc['sc'] = $builder->extract_shortcode_from_tag( $sc[0] );
				$sc['close'] = in_array( '[/' . $sc['sc'], $this->needed_close_tags ) ? 'tag' : 'self';
				$sc['is_close_tag'] = ( 'self' == $sc['close'] ) || ( false !== strpos( $sc[0], '[/' ) ) ||  ( false !== strpos( $sc[0], '/]' ) );
			}

			unset( $sc );			
	
		}
		

		/**
		 * Recursivly scan the shortcodes and checks, that the layout elements have the first element attribute correctly set if necessary.
		 * Nesting of same named shortcodes is not supported !
		 * Escaped shortcodes must be removed before calling this function.
		 * 
		 * At this point we have to rely on a valid structure of the shortcodes and of the elements, because we only scan $sc_array
		 * 
		 * Width of elements must be given in float ( <= 1.0 ).
		 * 
		 * @since 4.2.1
		 * @param array $sc_array
		 * @param int $start_index
		 * @param int $end_index
		 */
		protected function recalculate_layout_array( array &$sc_array, $start_index = -1, $end_index = -1 )
		{	
			$builder = Avia_Builder();
			
			$width = 0.0;
			
			/**
			 * Start of recursion
			 */
			if( $start_index < 0 )
			{
				$start_index = 0;
				$end_index = count( $sc_array ) - 1;
				
				/**
				 * Extract plain shortcode
				 */
				foreach ( $sc_array as $key => &$sc ) 
				{
					$sc['sc'] = trim( str_replace( array( '[/', '/]', '[', ']' ), '', $sc[0] ) );
					$sc['is_close_tag'] = ( 'self' == $sc['close'] ) || ( false !== strpos( $sc[0], '[/' ) ) ||  ( false !== strpos( $sc[0], '/]' ) );
				}
				
				unset( $sc );
			}
			
			$i = $start_index;
			$code_open = null;
			$index_open = -1;
			
			while( $i <= $end_index )
			{
				$code_current = $sc_array[ $i ];
				
				/**
				 * Allow recursive calls to nested shortcodes inside
				 */
				if( is_null( $code_open ) )
				{
					$code_open = $code_current;
					$index_open = $i;
				}
				
				$class = $builder->get_sc_class_from_tag( $code_current['tag'] );
				
				if( false === $class )
				{
					/**
					 * Not an ALB class - we start a new line with this element
					 */
					$new_width = 1.0;
				}
				else
				{
					/**
					 * We get the width of the element
					 */
					$new_width = $class->get_element_width();
				}
				
				/**
				 * Push into new line, when new element does not fit into current line
				 */
				if( ( $width != 0.0 ) && ( ( $width + $new_width ) > 1.0 ) )
				{
					$width = 0.0;
				}
				
				$first = ( false !== $class ) ? trim( $class->config['first_in_row'] ) : '';
				
				/**
				 * Check if we have to set or clear a possible first attribute in shortcode
				 */
				if( $first != '')
				{
					$new_tag = '';
					$match = array();
					$search = '/(\s{1})(' . preg_quote( $first, '/' ) . ')([\s|\]|\/])/i';
					
					preg_match( $search, $code_current['tag'], $match );
				
					if( 0 == $width )
					{
						if( empty( $match ) )
						{
							$new_tag = str_replace( $code_current['sc'], $code_current['sc'] . ' ' . $first, $code_current['tag'] )  ;
						}
					}
					else
					{
						if( ! empty( $match ) )
						{
							$new_tag = preg_replace( $search, '${1}${3}', $code_current['tag'] );
						}
					}
				
					if( $new_tag != '' )
					{
						$sc_array[ $i ]['update'] = $sc_array[ $i ]['tag'];
						$sc_array[ $i ]['tag'] = $new_tag;
						
						if( in_array( $this->parser_state, array( 'auto_repair' ) ) )
						{
							$msg_tx = sprintf( __( 'Layout structure for first element had to be modified at index %d: %s', 'avia_framework' ), $sc_array[ $i ][1], $new_tag );
						}
						else
						{
							$msg_tx = sprintf( __( 'Layout structure for first element must be modified at index %d: %s', 'avia_framework' ), $sc_array[ $i ][1], $new_tag );
						}
								
						$msg = array(
							'type'		=>	'warning',
							'dashboard'	=>	'no',
							'message'	=>	$msg_tx
								);
					
						$this->add_parser_message( $msg );
					}
				}
				
				$width += $new_width;
				$i++;
				
				/**
				 * Self closing tag
				 */
				if( $code_current['is_close_tag'] )
				{
					$code_open = null;
					continue;
				}
				
				/**
				 * Scan for closing tag
				 */
				$index_close = false;
				for( $i_close = $i; $i_close <= $end_index; $i_close++ )
				{
					$check = $sc_array[ $i_close ];
					
					if( $check['sc'] != $code_open['sc'] )
					{
						continue;
					}
					
					$index_close = $i_close;
					break;
				}
				
				/**
				 * This is a fallback situation only, because the shortcode should be balanced.
				 * In this case we have to ignore further recursions and break the loop and current recursion.
				 */
				if( false === $index_close )
				{
					return;
				}
				
				/**
				 * We found at least 1 nested shortcode inside
				 */
				if( ( $index_open + 1 ) <  $index_close )
				{
					$this->recalculate_layout_array( $sc_array, $index_open + 1, $index_close - 1 );
				}
				
				$code_open = null;
				$i = $index_close + 1;
			}
		}

		/**
		 * Here we know where we have to insert the balancing shortcodes.
		 * But we have to pay attention to nested elements - in that case
		 * we have to include the adjacent parent/children tags or we have to insert empty tags also.
		 * Otherwise we might break the backend when editing.
		 * 
		 * Nested shortcodes can be skipped, but layout_children have to be added.
		 * 
		 * Here we move the opening tags/closing tags to the right entry and adjust the output string in the tag element
		 * so we have a valid element structure and can simply insert the tags later
		 * 
		 * Limitation: Nested shortcodes allow only one level ( means subelements may not have layout_children again ) !!
		 * 
		 * @since 4.2.1
		 * @param array $sc_array
		 */
		protected function check_nested_element_structures( array &$sc_array )
		{
			$builder = Avia_Builder();
			
			
			$s1 = ShortcodeHelper::$nested_shortcodes;
			$s2 = ShortcodeHelper::$allowed_shortcodes;
			$s3 = $builder->shortcode_parents;
			$s4 = $builder->shortcode;
			
			$a = isset( $s4['av_row'] ) ? $s4['av_row'] : 'unknown';
			$b = isset( $s4['av_cell'] ) ? $s4['av_cell'] : 'unknown';
			$c = isset( $s4['av_button'] ) ? $s4['av_button'] : 'unknown';
			

			foreach( $sc_array as $index => $sc ) 
			{
				$sc_class = $builder->get_sc_class_from_tag( $sc[0] );
				$scode_parents = $builder->get_sc_parents_from_tag( $sc[0] );
				
				if( isset( $sc['escaped'] ) && ( 'yes' == $sc['escaped'] ) )
				{
					continue;
				}
				
				if( ! isset( $sc['add_sc'] ) || ! in_array( $sc['add_sc'], array ( 'open', 'close' ) ) )
				{
					continue;
				}
				
				$sc_class = $builder->get_sc_class_from_tag( $sc[0] );
				$scode_parents = $builder->get_sc_parents_from_tag( $sc[0] );
				
				$parent_sc = array();
				$sub_sc = array();
				
				$type = '';
				
				/**
				 * Nested Subelement shortcodes do not need to have an existing class
				 */
				if( ( false === $sc_class ) || ( empty( $sc_class->config['layout_children'] ) && empty( $sc_class->config['shortcode_nested'] ) ) )
				{
					/**
					 * If no parent class exist, this is something we need not handle here
					 */
					if( ( false === $scode_parents ) || empty( $scode_parents ) )
					{
						continue;
					}
					
					/**
					 * Check if a possible subelement and we really have a subelement and not a 
					 * standalone nestable element (e.g. av_button in av_table)
					 */
					$p = $this->find_surrounding_parent_class( $sc_array, $index );
					if( ( false === $p ) || ! in_array( $p->config['shortcode'], $scode_parents ) )
					{
						continue;
					}
					
					if( 'no' == $p->config['auto_repair'] )
					{
						$msg = array(
							'type'		=>	'fatal_error',
							'dashboard'	=>	'no',
							'message'	=>	sprintf( __( 'Check missing shortcode tags - auto repair is not possible for %s at index %d.', 'avia_framework' ), $sc[0], $sc[1] )
								);
					
						$this->add_parser_message( $msg );
						
						throw new ShortcodeParser_FatalError();
					}
					
					$parent_sc = $scode_parents;
					$sub_sc[] = $sc['sc'];
					$type = 'sub';
				}
				else
				{
					/**
					 * Parent element
					 */
					$parent_sc[] = $sc['sc'];
					$sub_sc = array_merge( $sc_class->config['layout_children'], $sc_class->config['shortcode_nested'] );
					$type = 'parent';
				}

				if( 'parent' == $type )
				{
					$this->add_nested_parent_element( $sc_array, $parent_sc, $sub_sc, $index );
				}
				else
				{
					$this->add_nested_sub_element( $sc_array, $parent_sc, $sub_sc, $index );
				}
			}

		}
		
		/**
		 * We have a parent element and check for subelements that we have to include
		 * 
		 * @since 4.2.1
		 * @param array $sc_array
		 * @param array $parent_sc
		 * @param array $sub_sc
		 * @param int $index
		 */
		protected function add_nested_parent_element( array &$sc_array, array &$parent_sc, array &$sub_sc, $index )
		{
			$builder = Avia_Builder();
			
			$sc_to_add = $sc_array[ $index ]['sc'];
			
			$class = $builder->get_sc_class_from_tag( $sc_array[ $index ][0] );
			
			/**
			 * Fallback only - parent classes must be defined
			 */
			if( false === $class )
			{
				return;
			}
			
			$contains_text = $class->config['contains_text'];
			$contains_layout = $class->config['contains_layout'];
			$contains_content = $class->config['contains_content'];
			
			$i = $index;
			$add = $sc_array[ $index ]['add_sc'] == 'open' ? -1 : 1;
					
			$sub_count = 0;
			$sub_open = false;			//	if we are in a subelement
			$last_sub_index = -1;		//	index with last found subelement (open / close)
			
			$i += $add;
			
			while( $i >= 0 && $i <= ( count( $sc_array ) - 1 ) )
			{
				$current = $sc_array[ $i ];
				
				if( isset( $current['escaped'] ) && ( 'yes' == $current['escaped'] ) )
				{
					$i += $add;
					continue;
				}
				
				/**
				 * When we find same named shortcode we must break loop
				 */
				if( $sc_to_add == $current['sc'] )
				{
					break;
				}
				
				/**
				 * We find a subelement tag that we must include
				 */
				if( in_array( $current['sc'], $sub_sc ) )
				{
					$last_sub_index = $i;
					
					if( 'self' == $current['close'] || isset( $current['add_sc'] ) )
					{
						$sub_open = false;
						$sub_count++;
					}
					else
					{
						/**
						 * Depending on direction (-1 is backwards) change open/close flag for tag
						 */
						$sub_open = $current['is_close_tag'] ? $add < 0 : $add > 0;
						$sub_count += 0.5;
					}
					
					$i += $add;
					continue;
				}
				
				/**
				 * Inside a subelement we have to accept everything
				 */
				if( $sub_open )
				{
					$i += $add;
					continue;
				}
				
				/**
				 * No other elements allowed to include
				 */
				if( ( 'no' == $contains_layout ) && ( 'no' == $contains_content ) )
				{
					break;
				}
				
				/**
				 * At the moment our parent elements do not support other elements beside child elements
				 * In case we will have this in future we add a _deprecated_function note.
				 * 
				 * For the moment we break.
				 */
				_deprecated_function( 'add_nested_parent_element', '4.2.1', 'We do not support elements other than subelements in parent-children shortcodes' );
				break;
			}
			
			/**
			 * We found a subelement - move tag to insert to this element
			 */
			if( $last_sub_index >= 0 )
			{
				unset( $sc_array[ $index ]['add_sc'] );
				 
				$sc_array[ $last_sub_index ]['add_new_sc'] = 'yes';
				
				if( $add < 0 )
				{
					if( ! isset ( $sc_array[ $last_sub_index ]['add_new_tag_before'] ) )
					{
						$sc_array[ $last_sub_index ]['add_new_tag_before'] = '';
					}
					$sc_array[ $last_sub_index ]['add_new_tag_before'] = '[' . $sc_to_add . ']' . $sc_array[ $last_sub_index ]['add_new_tag_before'];
				}
				else
				{
					if( ! isset ( $sc_array[ $last_sub_index ]['add_new_tag_after'] ) )
					{
						$sc_array[ $last_sub_index ]['add_new_tag_after'] = '';
					}
					$sc_array[ $last_sub_index ]['add_new_tag_after'] .= '[/' . $sc_to_add . ']';
				}
				
				return;
			}
			
			/**
			 * When no subelement found, we only add one empty subelement.
			 */
			$sub = '';
			
			foreach ( $sub_sc as $sub_element ) 
			{
				$sub .= '[' . $sub_element . ']';
				if( in_array( '[/' . $sub_element, $this->needed_close_tags ) || ( ! $class->is_nested_self_closing( $sub_element ) ) )
				{
					$sub .= '[/' . $sub_element . ']';
				}
				break;
			}
				
			unset( $sc_array[ $index ]['add_sc'] );
			
			$sc_array[ $index ]['add_new_sc'] = 'yes';
			if( $add < 0 )
			{
				if( ! isset ( $sc_array[ $index ]['add_new_tag_before'] ) )
				{
					$sc_array[ $index ]['add_new_tag_before'] = '';
				}
				$sc_array[ $index ]['add_new_tag_before'] = '[' . $sc_to_add . ']' . $sub . $sc_array[ $index ]['add_new_tag_before'];
			}
			else
			{
				if( ! isset ( $sc_array[ $index ]['add_new_tag_after'] ) )
				{
					$sc_array[ $index ]['add_new_tag_after'] = '';
				}
				$sc_array[ $index ]['add_new_tag_after'] .= $sub . '[/' . $sc_to_add . ']';
			}
			
		}
			
		/**
		 * We have to close a subelement. Currently we assume that we can include all elements till nest subelement or surrounding parent element
		 * If we do not find a parent element we do not add a parent element shortcode. This must be done before.
		 * 
		 * @since 4.2.1
		 * @param array $sc_array
		 * @param array $parent_sc
		 * @param array $sub_sc
		 * @param int $index
		 */
		protected function add_nested_sub_element( array &$sc_array, array &$parent_sc, array &$sub_sc, $index )
		{
			$builder = Avia_Builder();
			
			$sc_to_add = $sc_array[ $index ]['sc'];
			
			$class = $builder->get_sc_class_from_tag( $sc_array[ $index ][0] );
			
			/**
			 * Nested subelements are not defined - currently we allow all
			 */
			if( false === $class )
			{
				$contains_text = 'yes';
				$contains_layout = 'yes';
				$contains_content = 'yes';
			}
			else
			{
				$contains_text = $class->config['contains_text'];
				$contains_layout = $class->config['contains_layout'];
				$contains_content = $class->config['contains_content'];
			}
			
			$i = $index;
			$add = $sc_array[ $index ]['add_sc'] == 'open' ? -1 : 1;
					
			$last_sub_index = -1;		//	index with last found subelement (open / close)
			
			$parent_class = $this->find_surrounding_parent_class( $sc_array, $index );
			if( false === $parent_class )
			{
				/**
				 * Fallback situation
				 */
				$parent_class = $builder->get_shortcode_class( $parent_sc[0] );
			}
			
			$i += $add;
			
			while( $i >= 0 && $i <= ( count( $sc_array ) - 1 ) )
			{
				$current = $sc_array[ $i ];
				
				if( isset( $current['escaped'] ) && ( 'yes' == $current['escaped'] ) )
				{
					$i += $add;
					continue;
				}
				
				/**
				 * We find a parent tag we can break but we must check, that it is an opening or closing tag depending on direction
				 */
				if( in_array( $current['sc'], $parent_sc ) )
				{
					if( ( $current['sc'] == $parent_class->config['shortcode'] ) && ( ( ( $add > 0 ) && ( $current['is_close_tag'] ) ) || ( ( $add < 0 ) && ( ! $current['is_close_tag'] ) ) ) )
					{
						$last_sub_index = $i;
					}
					else
					{
						$last_sub_index = -1;
					}
					break;
				}
				
				/**
				 * We find a next subelemet we can break
				 */
				if( in_array( $current['sc'], $sub_sc ) )
				{
					$last_sub_index = $i;
					break;
				}
				
				/**
				 * We are already in an allowed nested element, currently we accept anything inside.
				 * Might change later.
				 */
				$i += $add;
				continue;
			}
			
			/**
			 * We found elements to include - we wrap all inside and move tag to first element not to be included
			 */
			if( $last_sub_index >= 0 )
			{
				unset( $sc_array[ $index ]['add_sc'] );
				
				$sc_array[ $last_sub_index ]['add_new_sc'] = 'yes';
				
				if( $add > 0 )
				{
					if( ! isset ( $sc_array[ $last_sub_index ]['add_new_tag_before'] ) )
					{
						$sc_array[ $last_sub_index ]['add_new_tag_before'] = '';
					}
					$sc_array[ $last_sub_index ]['add_new_tag_before'] = '[/' . $sc_to_add . ']' . $sc_array[ $last_sub_index ]['add_new_tag_before'];
				}
				else
				{
					if( ! isset ( $sc_array[ $last_sub_index ]['add_new_tag_after'] ) )
					{
						$sc_array[ $last_sub_index ]['add_new_tag_after'] = '';
					}
					$sc_array[ $last_sub_index ]['add_new_tag_after'] .= '[' . $sc_to_add . ']';
				}				
				
				return;
			}
			
			/**
			 * When no elements found to wrap, we must leave the empty content. 
			 * This is actual a fallback situation in case user forgot to close shortcode and we find the next opening tag, because we need surrounding parent tags
			 */
			return;
		}
						

		

		/**
		 * Scans the text and wraps all plain textblocks in [av_textblock] for elements that do not support plain text
		 * 
		 * @since 4.2.1
		 * @param string $text
		 * @return string
		 */
		public function wrap_plain_textblocks( $text )
		{
			$text_nodes = preg_split("/". get_shortcode_regex() ."/s", $text );
			
			foreach( $text_nodes as $node ) 
			{				
	            if( strlen( trim( $node ) ) == 0 || strlen( trim( strip_tags( $node) ) ) == 0) 
	            {
	               //$text = preg_replace("/(".preg_quote($node, '/')."(?!\[\/))/", '', $text);
	            }
	            else
	            {
					$text = preg_replace( "/(" . preg_quote( $node, '/' ) . "(?!\[\/))/", '[av_textblock]$1[/av_textblock]', $text );
				   
					$msg_txt = substr( $node, 0, 30 );
					if( strlen( $node ) > 31 )
					{
						$msg_txt .= '.....';
					}
					
					if( in_array( $this->parser_state, array( 'auto_repair' ) ) )
					{
						$msg_tx = sprintf( __( 'Textblock shortcode created around a plain text content to keep it editable: %s', 'avia_framework' ), esc_html( $msg_txt ) );
					}
					else 
					{
						$msg_tx = sprintf( __( 'Check if you need to create a textblock shortcode around a plain text content to keep it editable: %s', 'avia_framework' ), esc_html( $msg_txt ) );
					}
									
					$msg = array(
							'type'		=>	'warning',
							'dashboard'	=>	'no',
							'message'	=>	$msg_tx
								);
					
					$this->add_parser_message( $msg );
	            }
	        }
			
			$text = preg_replace_callback( "/". get_shortcode_regex() . "/s", array( $this, 'wrap_nested_plain_textblocks' ), $text );
			
			return $text;
		}
		
		
		/**
		 * Callback function that recursivly calls wrap_plain_textblocks for nested shortcodes
		 * 
		 * @since 4.2.1
		 * @param array $m
		 * @return string
		 */
		public function wrap_nested_plain_textblocks( array $m )
		{
			/**
			 * Escaped shortcode ?
			 */
	        if ( $m[1] == '[' && $m[6] == ']' ) 
			{
				return $m[0];
	        }
			
			/**
			 * Self closing tag ?
			 */
			$closing = strpos( $m[0], '[/' . $m[2] . ']' );
			if( false === $closing )
			{
				return $m[0];
			}
			
			/**
			 * Empty content ?
			 */
			if( 0 == strlen( trim( strip_tags( $m[5] ) ) ) ) 
			{
				return $m[0];
			}
			
			$builder = Avia_Builder();
			
			$class = $builder->get_sc_class_from_tag( $m[0] );
			if( false === $class )
			{
				return $m[0];
			}
			
			if( 'yes' == $class->config['contains_text'] )
			{
				return $m[0];
			}
			
			$new_content = $this->wrap_plain_textblocks( $m[5] );
			
			$new = str_replace( $m[5], $new_content, $m[0] );
			
	        return $new;
		}
		

		/**
		 * Insert or update the shortcodes in text. $sc_array must have been built previously by a call to balance_shortcode
		 * 
		 * @since 4.2.1
		 * @param array $sc_array
		 * @param string $text
		 * @return string
		 */
		protected function update_text_from_array( array $sc_array, $text ) 
		{
			/**
			 * As we might have to insert or update shortcode tags in the text we scan the array backwards so we can use the positions retuned by preg_match
			 * to insert/update missing shortcode tags
			 */
			for( $i = count( $sc_array ) - 1; $i >= 0; $i-- )
			{
				$sc = $sc_array[ $i ];
				
				if( ! isset( $sc['add_sc'] ) && ! isset( $sc['update'] ) ) 
				{
					continue;
				}
				
				/**
				 * Update the shortcode tag
				 */
				if( isset( $sc['update'] ) )
				{
					$text = substr_replace( $text, $sc['tag'], $sc[1], strlen( $sc['update'] ) );
					continue;
				}
				
				$sc_ins = ( false !== strpos( $sc[0], '/' ) ) ? $sc[0] : $sc[0] . "\n\n";
				
				if( isset( $sc['add_sc'] ) && ( 'at_index' == $sc['add_sc'] ) )
				{
					/**
					 * Insert the shortcode tag 
					 */
					$text = substr_replace( $text, $sc_ins, -$sc[1], 0 );
				}
				else if( isset( $sc['add_sc'] ) && ( 'after_tag' == $sc['add_sc'] ) )
				{
					/**
					 * Fallback situation only - something went wrong with shortcode regex of complete tag
					 */
					$pos = strpos( $text, ']', ( - $sc[1] ) + 1 );
					if( false === $pos )
					{
						/**
						 * We have no chance to close the shortcode - we must ignore it and leave it unbalanced
						 */
						continue;
					}
					$text = substr_replace( $text, $sc[0], $pos, 0 );
				}
				
				$builder_active = Avia_Builder()->get_alb_builder_status( get_the_ID() );
		    	if( $builder_active != 'active' )
				{
					continue;
				}
				
				/**
				 * Check if we find plain text (= content) to previous/next shortcode tag -> we put in textblock to keep it editable
				 */
				if( false === strpos( $sc[0], '/' ) )
				{
					if( 0 == $sc[1] )
					{
						continue;
					}
					$end = ( - $sc[1] ) - 1;
					
					$sub = substr( $text, 0, $end + 1 );
					$anf = strrpos( $sub, ']' );
					$anf = ( false === $anf ) ? 0 : $anf + 1;
				}
				else
				{
					$anf = ( - $sc[1] ) + strlen( $sc[0] );
					if( $anf >= strlen( $text ) )
					{
						continue;
					}
					
					$end = strpos( $text, '[', $anf );
					$end = ( false === $end ) ? strlen( $text ) - 1 : $end - 1;
				}
				
				if( $anf >= $end )
				{
					continue;
				}
				
				$substring = substr( $text, $anf, $end - $anf + 1 );
				
				if( '' == trim( $substring ) )
				{
					continue;
				}
				
				$new = '[av_textblock]' . $substring . '[/av_textblock]' ."\n\n";
				$text = substr_replace( $text, $new, $anf, strlen( $substring ) );
				
				if( strlen( $substring ) > 30 )
				{
					$substring = substr( $substring, 0, 30 ) . ' ...';
				}
				
				if( in_array( $this->parser_state, array( 'auto_repair' ) ) )
				{
					$msg_tx = sprintf( __( 'Textblock shortcode created at index %d around a text content to keep it editable: %s', 'avia_framework' ), $anf, esc_html( $substring ) );
				}
				else 
				{
					$msg_tx = sprintf( __( 'Check to create a textblock shortcode at index %d around a text content to keep it editable: %s', 'avia_framework' ), $anf, esc_html( $substring ) );
				}
				
				$msg = array(
							'type'		=>	'warning',
							'dashboard'	=>	'no',
							'message'	=>	$msg_tx
								);
					
				$this->add_parser_message( $msg );
			}
			
			/**
			 * As we might have created subsequent av_textblock in content because of missing av_textblock tag we remove those again
			 */
			$text = preg_replace( '/\[\/av_textblock\]\s*\[av_textblock\]/s', '', $text );
			
			return $text;
		}
				


		/**
		 * Parses the text using standard WP regex for shortcodes. This returns an array of shortcodes as processed by WP.
		 * Nested shortcodes are supported.
		 * 
		 * @since 4.2.1
		 * @param string $text
		 * @return array
		 */
		public function get_wp_result( $text )
		{
			/**
			 * Get all shortcodes that WP would find in text. See get_shortcode_regex for a description of the returned result array.
			 */
			$wp_shortcodes = array();
			preg_match_all( "/" . get_shortcode_regex( ) . "/s", $text, $wp_shortcodes, PREG_OFFSET_CAPTURE );
			
			$wp_results = array();
			
			if( ! empty( $wp_shortcodes ) && ! empty( $wp_shortcodes[0] ) )
			{
				/**
				 * Parse the results recursivly to support nested shortcodes
				 */
				$wp_results = $this->parse_wp_match( $wp_shortcodes );
			}
			
			return $wp_results;
		}

		/**
		 * Recursive function that builds a "fake pattern" result array out of the WP preg_reg match of a given text.
		 * This allows us to compare that our modified shortcode structure returns the intended result and we can safely
		 * create our shortcode tree.
		 * 
		 * @param array $matches
		 * @param int $text_offset
		 * @return array
		 */
		protected function parse_wp_match( array $matches, $text_offset = 0 )
		{
			$result = array();
			
			foreach ( $matches[0] as $key => $found ) 
			{
				$code = $matches[2][ $key ];
				
				$full_tags = array();
				preg_match_all( "/" . '\[{1,2}[\/]?' . $code[0] . '[^\]]*\]{1,2}' . "/s", $matches[0][ $key ][0], $full_tags, PREG_OFFSET_CAPTURE );
			
				$fake_tag = '[' . $matches[1][ $key ][0] . $code[0];
				$open_tag = isset( $full_tags[0][0][0] ) ? $full_tags[0][0][0] : $fake_tag;
				$open_pos = $matches[0][ $key ][1];
				
				$close_tag = '';
				$close_pos = -1;
				
				$index_close = count( $full_tags[0] ) - 1;
				if( $index_close > 0 )
				{
					$close_tag = $full_tags[0][ $index_close ][0];
					$close_pos = $open_pos + $full_tags[0][ $index_close ][1];
				}
				
				$result[] = array(
						0		=>	$fake_tag,
						1		=>	$text_offset + $open_pos,
						'tag'	=>	$open_tag,
						'close'	=>	( $close_pos < 0 ) ? 'self' : 'tag'
					);
				
				/**
				 * If not empty, we have some content in a closing shortcode tag - recursivly check for nested shortcodes
				 */
				$check_string = trim( $matches[5][ $key ][0] );
				if( ! empty( $check_string ) )
				{
					$wp_shortcodes = array();
					preg_match_all( "/" . get_shortcode_regex( ) . "/s", $matches[5][ $key ][0], $wp_shortcodes, PREG_OFFSET_CAPTURE );
					
					/**
					 * If we find a shortcode inside we recursivly parse it, plain text we can ignore
					 */
					if( ! empty( $wp_shortcodes ) && ! empty( $wp_shortcodes[0] ) )
					{
						$found = $this->parse_wp_match( $wp_shortcodes, $text_offset + $matches[5][ $key ][1] );
						$result = array_merge( $result, $found );
					}
				}
				
				if( $close_pos >= 0 )
				{
					$result[] = array(
							0		=>	$close_tag,
							1		=>	$text_offset + $close_pos,
							'tag'	=>	$close_tag,
							'close'	=>	'self'
						);
				}
			}
			
			return $result;
		}
		

		/**
		 * Compares the extracted shortcode arrays. $wp_array uses the WP shortcode regex ( recursivly to support nested shortcodes )   
		 * This ensures that we can build a synchronous shortcode tree.
		 * 
		 * As the original text might have been changed because we added shortcodes we cannot compare the index.
		 * 
		 * @since 4.2.1
		 * @param array $wp_array
		 * @param array $sc_array
		 * @return boolean
		 */
		protected function check_wp_result_array( array $wp_array, array $sc_array )
		{
			/**
			 * Now we compare the result arrays - must be equal
			 */
			$wp_index = 0;
			$error = '';
			
			foreach ( $sc_array as $key => $sc ) 
			{
				if( ! isset( $wp_array[ $wp_index ] ) )
				{
					$error .=	'<div class="avia-error avia-error-alb-parser">';
					$error .=		'<span class="av-alb-missing-wp">' . sprintf( __( 'Missing in WP result - Document position %d:','avia_framework' ), $sc[1] ) . '</span>';
					$error .=		'<pre><code>';
					$error .=			print_r( $sc, true );
					$error .=		'</code></pre>';
					$error .=	'</div>';
					continue;
				}
				
				$wp = $wp_array[ $wp_index ];
				$wp_index ++;
				
				if( isset( $wp['escaped'] ) && isset( $sc['escaped'] ) && ( 'yes' == $wp['escaped'] ) && ( 'yes' == $sc['escaped'] ) )
				{
					continue;
				}
				
				if( ( $wp['tag'] == $sc['tag'] ) && ( $wp['close'] == $sc['close'] ) )
				{
					continue;
				}
				
				$error .=	'<div class="avia-error avia-error-alb-parser">';
				$error .=		'<span class="av-alb-diff-document">' . sprintf( __( 'Different results - Document position %d:','avia_framework' ), $sc[1] ) . '</span>';
				$error .=		'<pre><code>';
				$error .=			print_r( $sc, true );
				$error .=		'</code></pre>';
				$error .=		'<span class="av-alb-diff-wp">' . __( 'WP result:','avia_framework' ) . '</span>';
				$error .=		'<pre><code>';
				$error .=			print_r( $wp, true );
				$error .=		'</code></pre>';
				$error .=	'</div>';
			}
			
			$wp_length = count( $wp_array );
			while( $wp_index < $wp_length )
			{
				$wp = $wp_array[ $wp_index ];
				$wp_index++;
				
				$error .=	'<div class="avia-error avia-error-alb-parser">';
				$error .=		'<span class="av-alb-missing-doc">' . sprintf( __( 'Missing in document result - Document position %d:','avia_framework' ), $sc[1] ) . '</span>';
				$error .=		'<pre><code>';
				$error .=			print_r( $wp, true );
				$error .=		'</code></pre>';
				$error .=	'</div>';
			}
			
			if( ! empty( $error ) )
			{
				$msg = array(
							'type'		=>	'error',
							'format'	=>	'html',
							'message'	=>	$error
								);
					
				$this->add_parser_message( $msg );
			}
			
			return empty( $error );
		}
		
		
		/**
		 * Display parser info in dshboard below select box
		 * 
		 * @since 4.2.1
		 * @return string
		 */
		public function display_dashboard_info()
		{
			$out = '';
			
			$active = Avia_Builder()->get_alb_builder_status( get_the_ID() );
			switch( $active )
			{
				case 'active':
					$key = '_alb_shortcode_status_clean_data';
					break;
				default:
					$key = '_alb_shortcode_status_content';
					break;
			}
			
			$messages = $this->get_parser_message( $key );
			
			if( empty( $messages ) )
			{
				$out .=		'<div class="avia-parser-errors">';
				$out .=			'<span>' . __( 'No shortcode parser info available', 'avia_framework' ) . '</span>';
				$out .=		'</div>';
				return $out;
			}
			
			$message = $messages[0];
			
			$valid = 'valid' == $message['shortcode_state'];
			
			switch( $message['shortcode_state'] )
			{
				case 'valid':
				case 'invalid':
				case 'undefined';
					$class = $message['shortcode_state'];
					break;
				default:
					$class = 'undefined';
					break;
				
			}
			
			$out .=		'<div class="avia-parser-' . $class . '">';
			
			$count = 0;
			foreach ( $message['messages'] as $msg ) 
			{
				if( 'yes' == $msg['dashboard'] )
				{	
					if( $count == 0 )
					{
						if( ( 'undefined' == $message['shortcode_state'] ) && ( ( 'disabled' != $message['parser_state'] ) ) )
						{
							$out .= '<span>' . __( 'No internal information available for the shortcode state. Please update the post to get the information.', 'avia_framework' ) . '</span>';
							$out .= '<br/>';
						}
					}
					else
					{
						$out .= '<br/>';
					}
					$out .= '<span>' . str_replace( '[', '&#91;', $msg['message'] ) . '</span>';
					$count++;
				}
			}
			
			/**
			 * Create a default output, if no dashboard message created
			 */
			if( 0 == $count )
			{
				$err_cnt = $message['errors']['fatal_error'] + $message['errors']['error'];
				$text = '';
				
				if( $err_cnt > 0 )
				{
					$text .= ( $valid ) ? __( 'Errors could be repaired.', 'avia_framework' ) : __( 'Errors could not be repaired.', 'avia_framework' );
				}
				else
				{
					$text .= __( 'No errors found.', 'avia_framework' );
					if( ! $valid )
					{
						$text .= '<br/>' . __( 'Internal error occured - Shortcode state is reported to be invalid. Please report to our support staff.', 'avia_framework' );
					}
				}
				
				$cnt = array_sum( $message['errors'] );
				if( $cnt > 0 )
				{	
					$text .= '<br/>';
					$text .= sprintf( __( '%d fatal error(s), %d error(s) and %d warning(s) detected parsing the shortcodes.', 'avia_framework' ), $message['errors']['fatal_error'], $message['errors']['error'], $message['errors']['warning'] );
				}
				
				$out .= '<span>' . $text . '</span>';
			}
			
			$out .=		'</div>';
			return $out;
		}

		/**
		 * Returns shortcode tree and parser info wrapped in a tab shortcode ready for output in content area of a page
		 * 
		 * @since 4.2.1
		 * @return string
		 */
		public function display_parser_info()
		{
			$out = '';
			
			/**
			 * Add custom styling here
			 */
			$out .=		"[av_codeblock wrapper_element='' wrapper_element_attributes='' deactivate_shortcode='aviaTBdeactivate_shortcode' deactivate_wrapper='aviaTBdeactivate_wrapper' custom_class='']";
			$out .=		'
<style>
	.av-parser-msg-container{
		background-color: #fff;
	}
	.av-parser-msg-container.av-parser-error.av-parser-format-text{
		color: #ff0000;
		font-weight: 600;
	}
	.av-parser-msg-container.av-parser-warning.av-parser-format-text{
		color: #0330f5;
	}
	.av-parser-msg-container.av-parser-update.av-parser-format-text{
		color: #02da90;
		font-weight: 600;
	}
	.av-parser-msg-container.av-parser-fatal_error.av-parser-format-text{
		color: #e606ee;
		font-weight: 900;
	}
	.av-parser-msg-container.av-parser-success.av-parser-format-text{
		color: #1DC116;
		font-weight: 600;
	}
	
</style>';
			$out .=		"[/av_codeblock]";	
			
	//						'error' | 'warning' | 'update' | 'fatal_error' | 'success'		
			
			$head = __( 'Enfold Shortcode Parser Info Panel', 'avia_framework' );
			
			$out .=		"[av_heading heading='{$head}' tag='h2' style='blockquote modern-quote' size='' subheading_active='subheading_below' subheading_size='15' padding='10' color='' custom_font='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' custom_class='' admin_preview_bg='']";
			$out .=			__( 'Look up problems concerning the shortcode structure of this page/post that might break your frontend layout.', 'avia_framework' );
			$out .=		"[/av_heading]";
			
			$active = Avia_Builder()->get_alb_builder_status( get_the_ID() );
			switch( $active )
			{
				case 'active':
					$current = __( 'Enfold Advanced Layout Editor enabled', 'avia_framework' );
					$keys = array(
									'_alb_shortcode_status_clean_data',
									'_alb_shortcode_status_content',
									'_alb_shortcode_status_preview'
							);
					break;
				default:
					$current = __( 'Wordpress Default Editor enabled', 'avia_framework' );
					$keys = array(
									'_alb_shortcode_status_content',
									'_alb_shortcode_status_clean_data',
									'_alb_shortcode_status_preview'
							);
					break;
			}
			
			$out .=		"[av_heading heading='{$current}' tag='h5' style='' size='' subheading_active='' subheading_size='15' padding='10' color='' custom_font='' av-medium-font-size-title='' av-small-font-size-title='' av-mini-font-size-title='' av-medium-font-size='' av-small-font-size='' av-mini-font-size='' custom_class='' admin_preview_bg='']";
			$out .=		"[/av_heading]";
			
			$out .=		"[av_tab_container position='top_tab' boxed='border_tabs' initial='1' custom_class='']";
			
			foreach ( $keys as $key ) 
			{
				$out .=		$this->display_parser_detail_tab( $key );
			}
			
			
			$out .=			"[av_tab title='Shortcode Tree' icon_select='yes' icon='ue856' font='entypo-fontello']";
			
			$out .=				'<pre><code>';
			$out .=					print_r( ShortcodeHelper::$tree, true );
			$out .=				'</code></pre>';
			
			$out .=			"[/av_tab]";
			
			$out .=		"[/av_tab_container]";
			
			return $out;
		}
		
		
		/**
		 * Display a single tab content
		 * 
		 * @since 4.2.1
		 * @param string $key
		 * @return string
		 */
		protected function display_parser_detail_tab( $key )
		{
			$out = '';
			
			switch( $key )
			{
				case '_alb_shortcode_status_clean_data':
					$title = __( 'Advanced Layout Editor', 'avia_framework' );
					$icon = 'ue8d3';
					break;
				case '_alb_shortcode_status_preview':
					$title = __( 'Preview', 'avia_framework' );
					$icon = 'ue803';
					break;
				case '_alb_shortcode_status_content':
					$title = __( 'Wordpress Editor', 'avia_framework' );
					$icon = 'ue836';
				default:
					break;			
			}
			
			$messages = $this->get_parser_message( $key );
			
			$out .=			"[av_tab title='{$title}' icon_select='yes' icon='{$icon}' font='entypo-fontello']";
			
			if( ! empty( $messages ) )
			{
				$out .=		"[av_toggle_container initial='0' mode='toggle' sort='true' styling='' colors='' font_color='' background_color='' border_color='' custom_class='']";
				
				foreach ( $messages as $message ) 
				{
					switch( $message['parser_state'] )
					{
						case 'auto_repair':
							$tag = __( 'Auto Repair', 'avia_framework' );
							break;
						case 'check_only':
							$tag = __( 'Check Only', 'avia_framework' );
							break;
						default:
							$tag = $message['parser_state'];
							break;
					}

					$err_cnt = $message['errors']['fatal_error'] + $message['errors']['error'];
					
					$title = $message['time'] . ': ';
					
					if( in_array( $message['parser_state'], array( 'auto_repair', 'check_only' ) ) )
					{
						$title .= ( 0 == $err_cnt ) ? __( 'No errors detected', 'avia_framework' ) : sprintf( __( '%d error(s) detected', 'avia_framework' ), $err_cnt );
						$title .= ' (' . $tag . ')';
					}
					else
					{
						$title .= $tag;
					}
					
					$out .=		"[av_toggle title='{$title}' tags='{$tag}']";

					$count = 0;
					foreach ( $message['messages'] as $msg) 
					{
						if( ('yes' == $msg['dashboard'] ) && ( 0 != $err_cnt ) )
						{
							continue;
						}
						
						if( $count > 0 )
						{
							$out .=		"[av_hr class='custom' height='50' shadow='no-shadow' position='center' custom_border='av-border-thin' custom_width='50px' custom_border_color='' custom_margin_top='0px' custom_margin_bottom='0px' icon_select='yes' custom_icon_color='' icon='ue8bf' font='entypo-fontello' custom_class='' admin_preview_bg='']";
						}
						
						$count++;
						
						$out .=	'<div class="av-parser-msg-container av-parser-' . $msg['type'] . ' av-parser-format-' . $msg['format'] . '">';
						
						if( 'html' == $msg['format'] )
						{
							$out .= $msg['message'];
						}
						else
						{
							$out .= '<span class="av-parser-infotypetext">' . strtoupper( $msg['type'] ) . ':  </span>';
							$out .= '<span class="av-parser-infotext">' . str_replace( '[', '&#91;', $msg['message'] ) . '</span>';
						}
						
						$out .=	'</div>  <!-- end message container --> ';
					}

					$out .=		"[/av_toggle]";
				}
				
				$out .=		"[/av_toggle_container]";
			}
			else
			{
				$out .=		'<div class="av-parser-msg-container av-parser-success av-parser-format-text">';
				$out .=			'<span class="av-parser-infotext">' . __( 'No info available', 'avia_framework' ) . '</span>';
				$out .=		'</div>  <!-- end message container --> ';
			}

//			$out .=				"[av_hr class='custom' height='50' shadow='no-shadow' position='center' custom_border='av-border-thin' custom_width='50px' custom_border_color='' custom_margin_top='30px' custom_margin_bottom='0px' icon_select='yes' custom_icon_color='' icon='ue8bf' font='entypo-fontello' custom_class='' admin_preview_bg='']";
//			$out .=				'<pre><code>';
//			$out .=					str_replace( '[', '&#91;', print_r( $messages, true ) );
//			$out .=				'</code></pre>';			
				
			$out .=			"[/av_tab]";
			
			return $out;
		}
	
	}		//	end class ShortcodeParser
	
}

if( ! class_exists( 'ShortcodeParser_FatalError' ) )
{
	/**
	 * @since 4.2.1
	 */
	class ShortcodeParser_FatalError extends Exception 
	{
		/**
		 * 
		 * @since 4.2.1
		 * @param string $message
		 * @param int $code
		 * @param \Throwable $previous
		 */
		public function __construct( $message = '', $code = 0, $previous = null ) 
		{
			parent::__construct( $message, $code, $previous );
		}
		
	}
}
