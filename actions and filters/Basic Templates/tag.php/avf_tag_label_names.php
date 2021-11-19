<?php
/*
 * @param string $label_name
 * @return string
 */
function custom_tag_label_names( $label_name )
{
	$label_name = __( 'Tag Archive for:', 'avia_framework' ) . ' <span>' . single_tag_title( '', false ) . '</span>';

	return $label_name;
}

add_filter( 'avf_tag_label_names', 'custom_tag_label_names' );

