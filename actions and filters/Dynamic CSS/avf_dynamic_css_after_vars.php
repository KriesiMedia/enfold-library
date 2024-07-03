<?php
/**
 * Examples to add additional custom code to dynamic css outside :root selector defining enfold vars()
 *
 * see filter avf_dynamic_css_additional_vars to add e.g. custom vars inside the :root selector defining enfold vars()
 *
 * @param string $output
 * @return string
 */
function my_avf_dynamic_css_after_vars( $output = '' )
{
	$output .= "\n";

	/**
	 * Override a defined var for a specific page id
	 */
	$output .= "html .page-id-1319{\n";
	$output .=		"--enfold-font-size-theme-h1: 60px;\n";
	$output .= "}\n";


	/**
	 * Override a defined var based on a media query
	 */
	$output .= "\n";
	$output .= "@media only screen and (max-width: 767px) {\n";

	$output .=		":root {\n";
	$output .=			"--enfold-font-size-theme-h1: 45px;\n";
	$output .=		"}\n";

	$output .= "}\n";

	$output .= "\n";

	return $output;
}


add_filter( 'avf_dynamic_css_after_vars', 'my_avf_dynamic_css_after_vars', 10, 1 );
