<?php
/*
Plugin Name: Avia Special Character Converter Plugin
Plugin URI: www.kriesi.at
Description: Replaces special characters that break layout or Enfold Advanced Layout Editor
Version: 1.0.0
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
		 *
		 * @since 1.0.0
		 * @var array 
		 */
		protected $translate;


		/**
		 * 
		 * @since 1.0.0
		 */
		public function __construct() 
		{
			$this->translate = array(
						'###lt###'		=> '<',
						'###gt###'		=> '>',
						'###amp###'		=> '&',
						'###91###'		=> '[',
						'###93###'		=> ']',
						'###quot###'	=> '"',
						'###34###'		=> "'"
			);

			add_filter( 'the_content', array( $this, 'handler_the_content' ), 9999999, 1 );
			add_filter( 'avf_text_to_preview', array( $this, 'handler_the_content' ), 9999999, 1 );
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
			 * 
			 * @since 1.0.0
			 * Add additional special characters to translate
			 */
			$this->translate = apply_filters( 'avia_special_characters_translations', $this->translate );

			$search = array_keys( $this->translate );
			$replace = array_values( $this->translate );

			$new_content = str_replace( $search, $replace, $content );

			return $new_content;
		}

	}

	$avia_special_characters = new avia_special_characters();

}	//	class exists