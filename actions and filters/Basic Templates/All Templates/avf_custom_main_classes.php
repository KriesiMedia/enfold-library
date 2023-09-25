<?php
/**
 * Add custom classes to <main> tag of page templates
 *
 * @since 5.6.7
 * @param string $class_string
 * @param string $context				e.g. index.php, 404.php, page.php, .....
 * @return string
 */
function custom_avf_custom_main_classes( $class_string = '', $context = '' )
{
	//	fallback check
	if( ! is_string( $class_string ) )
	{
		$class_string = '';
	}

	// e.g. add custom class to index.php
	if( 'index.php' == $context )
	{
		$class_string .= ' my-custom-class';
	}

	return $class_string;
}


add_filter( 'avf_custom_main_classes', 'custom_avf_custom_main_classes', 10, 2 );

