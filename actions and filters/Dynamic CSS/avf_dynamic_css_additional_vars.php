<?php
/**
 * Examples to add additional custom code to dynamic css inside the :root selector defining enfold vars()
 *
 * @param string $output
 * @return string
 */
function my_vars_outputs( $output )
{
	/**
	 * Example provided by Guenni007
	 * @link https://kriesi.at/support/topic/how-to-use-avf_dynamic_css_additional_vars-and-avf_dynamic_css_after_vars/#post-1441458
	 */
	$output .= "--my-variable-font-size-theme-h3: min(max(18px, calc(1.125rem + ((1vw - 3.2px) * 1.1864))), 32px); min-height: 0vw;\n";
	$output .= "--your-font-size-theme-h4: 20px;\n";

	return $output;
}

add_filter( 'avf_dynamic_css_additional_vars', 'my_vars_outputs' );
