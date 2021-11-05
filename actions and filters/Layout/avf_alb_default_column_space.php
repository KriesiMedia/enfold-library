<?php
/**
 * Change theme default column space from 6%
 * You MUST save theme options whenever you change the return value of this filter to clear post-css files
 *
 * Possible return values:
 *
 * '' ..... theme default value of 6%
 * any float value
 *
 *
 * @since 4.8.7.1
 * @param string $space			by default this is ''
 * @return string|float
 */
function my_custom_column_spacing( $space )
{
	//	e.g. set space between columns to 2%
	$space = 2.0;

	return $space;
}

add_filter( 'avf_alb_default_column_space', 'my_custom_column_spacing', 999, 1 );

