<?php
/**
 * This is an example code how to customize type options for ALB modal popup
 *
 * @since 4.8.9
 */

/**
 * 1. Create a file e.g. your_path/class-custom-html-helper.php
 * 2. Into this file add the AviaHtmlHelper class to replace the original class which will not be loaded
 * 3. Lets say you want to add a custom type input2
 * 4. Lets say you want to override existing type input
 */

if( ! defined( 'ABSPATH' ) )	{	exit;	}		// Exit if accessed directly

if( ! class_exists( 'AviaHtmlHelper' ) )
{
	class AviaHtmlHelper extends \aviaBuilder\base\aviaModalElements
	{
		/**
		 * The custom input2 method contains your new code
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function input2( array $element )
		{
			//	enter your code here for the new element
		}

		/**
		 * The custom input method overrides the original input method
		 *
		 * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
		 * @return string				the string returned contains the html code generated within the method
		 */
		static public function input( array $element )
		{
			//
			//
			//

			/**
			 * enter your code here to be used instead
			 * you may call parent class if you only need to modify minor html:
			 *
			 * $html = parent::input( $element );
			 *
			 */
		}


	} // end class

} // end if ! class_exists


/**
 * 5. In e.g. functions.php use filter to deactivate default config-templatebuilder\avia-template-builder\php\class-html-helper.php
 *
 * @param boolean $loaded
 * @param string $file
 * @return boolean
 */
function custom_avf_load_builder_core_files( $loaded, $file )
{
	// check for ...\php\class-html-helper.php
	if( false === strpos( $file, 'class-html-helper.php' ) )
	{
		return $loaded;
	}

	// load your custom file instead (  e.g.  your_path/class-custom-html-helper.php )
	require_once( 'your_path/class-custom-html-helper.php' );

	//	supress unnecessary loading of original file
	return true;
}

add_filter( 'avf_load_builder_core_files', 'custom_avf_load_builder_core_files', 10, 2 );