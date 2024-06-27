<?php
/**
 *
 * @since 6.0
 * @param array $args
 * @param string $id				holds the key of the element
 * @param array $element			data array of the element that should be created
 * @return array
 */
function my_avf_form_datepicker_args( $args, $id, $element )
{
	//	e.g. change yearRange to 1900 till current date + 10 years
	$args['yearRange'] = '1900:c+10';

	return $args;
}


add_filter( 'avf_form_datepicker_args', 'my_avf_form_datepicker_args', 10, 3 );
