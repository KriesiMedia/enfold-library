<?php
/*
Plugin Name: Avia Special Character Converter Plugin
Plugin URI: www.kriesi.at
Description: Replaces special characters that break layout or Enfold Advanced Layout Editor
Version: 1.1.2
Author: Guenter for www.kriesi.at
Author URI: www.kriesi.at
Text Domain: avia_special_characters

@requires:	PHP 5.3   (anonymous functions)
@requires:  WP 4.7
*/

/*
 * Copyright 2018
*/

if ( ! defined( 'ABSPATH' ) ) {   exit;  } // Exit if accessed directly


if( ! class_exists( 'avia_special_characters' ) )
{

	class avia_special_characters
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 1.1
		 * @var avia_special_characters
		 */
		static private $_instance = null;

		/**
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $translate;

		/**
		 * Return the instance of this class
		 *
		 * @since 1.1
		 * @return avia_special_characters
		 */
		static public function instance()
		{
			if( is_null( avia_special_characters::$_instance ) )
			{
				avia_special_characters::$_instance = new avia_special_characters();
			}

			return avia_special_characters::$_instance;
		}

		/**
		 *
		 * @since 1.0.0
		 */
		protected function __construct()
		{
			$this->translate = array(
						'###lt###'		=> '<',
						'###gt###'		=> '>',
						'###91###'		=> '[',
						'###93###'		=> ']'
			);

			add_filter( 'the_content', array( $this, 'handler_the_content' ), 9999999, 1 );
			add_filter( 'avf_text_to_preview', array( $this, 'handler_the_content' ), 9999999, 1 );

			/**
			 * Allow shortcode attributes to translate special characters (e.g. for attribute content used in js)
			 * 
			 * @since 1.1.2
			 */
			add_filter( 'avf_sc_attr_value', array( $this, 'handler_the_content' ), 9999999, 1 );

			/**
			 * @since 1.1
			 */
			add_filter( 'avf_form_subject', array( $this, 'handler_the_content' ), 9999999, 1 );
			add_filter( 'avf_form_mail_form_field', array( $this, 'handler_the_content' ), 9999999, 1 );
			add_filter( 'avf_contact_form_autoresponder_mail', array( $this, 'handler_avf_autoresponder_mail' ), 9999999, 4 );
		}

		/**
		 *
		 * @since 1.0.0
		 */
		public function __destruct()
		{
			unset( $this->translate );
		}

		/**
		 * Replace the special characters
		 *
		 * @since 1.0.0
		 * @param string $content
		 * @return string
		 */
		public function handler_the_content( $content )
		{
			/**
			 * Add additional special characters to translate
			 *
			 * @since 1.0.0
			 * @param array $this->translate
			 * @return array
			 */
			$this->translate = apply_filters( 'avia_special_characters_translations', $this->translate );

			$search = array_keys( $this->translate );
			$replace = array_values( $this->translate );

			$new_content = str_replace( $search, $replace, $content );

			return $new_content;
		}

		/**
		 *
		 * @since 1.1.1
		 * @param array $mail_array
		 * @param array $new_post
		 * @param array $form_params
		 * @param avia_form $form
		 * @return array
		 */
		public function handler_avf_autoresponder_mail( array $mail_array, array $new_post, array $form_params, avia_form $form )
		{
			$check = [ 'Subject', 'Message' ];

			foreach( $check as $key )
			{
				if( isset( $mail_array[ $key ] ) )
				{
					$mail_array[ $key ] = $this->handler_the_content( $mail_array[ $key ] );
				}
			}

			return $mail_array;
		}
	}

	/**
	 * Returns the main instance of avia_special_characters to prevent the need to use globals
	 *
	 * @since 1.1
	 * @return avia_special_characters
	 */
	function AviaSpecialCharacters()
	{
		return avia_special_characters::instance();
	}

	AviaSpecialCharacters();

}	//	class exists
