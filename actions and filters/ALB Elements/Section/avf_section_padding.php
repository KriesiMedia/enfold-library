<?php
/**
 * User Request: https://github.com/KriesiMedia/Enfold-Feature-Requests/issues/96
 *
 * @since 5.7.1
 */

/**
 * You need to add your own styling e.g. to Theme options -> Quick CSS field
 *
 * e.g.
 *		.avia-section-custom .content,
 *		.avia-section-custom .sidebar {
 *			padding-top: 130px;
 *			padding-bottom: 130px;
 *		}
 *
 * @param array $paddings
 * @return array
 */
function my_avf_section_padding( $paddings = [] )
{
	$paddings['Your custom section padding'] = 'custom';

	return $paddings;
}

add_filter( 'avf_section_padding', 'my_avf_section_padding', 10, 1 );


/**
 * Change default value for new section to e.g. 'custom'
 *
 * @param string $default
 * @return string
 */
function my_avf_section_padding_default( $default = 'default' )
{
	$default = 'custom';

	return $default;
}

add_filter( 'avf_section_padding_default', 'my_avf_section_padding_default', 10, 1 );
