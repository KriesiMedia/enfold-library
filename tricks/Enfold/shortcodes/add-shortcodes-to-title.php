<?php
/* 
 * CURRENTLY NOT MERGED !!!!!
 * ==========================
 *
 * The following snippets shows an example how to use shortcodes with titles (and have a correct markup).
 *
 * KEEP IN MIND: Caching plugins, SEO and translating plugins will have problems using dynamic titles.
 * =============
 *
 * To pass post_id parameter from get_the_title() to shortcode use the following syntax :
 *		{{{-post_id-}}}	=> $post_id
 *
 * Example:
 *	This is a shortcode title [your_sc post_id="{{{-post_id-}}}"]
 * 
 *
 * @needs: Enfold 4.6.4
 */

/**
 * Activate the beta feature
 *
 */
add_theme_support( 'avia_title_shortcode_processing' );

/**
 * Example shortcode handler
 * 
 * @param array $atts
 * @param string $content
 * @param string $shortcodename
 * @return string
 */
function sc_handler_avia_test_head( $atts, $content = '', $shortcodename = '' )
{
	$atts = shortcode_atts( array(
					'post_id'	=> ''		//	these values depend on your shortcode, only as an example here
				), $atts, $shortcodename );
	
	
	$text = 'my sc text';
	
	if( ! empty( $atts['post_id'] ) )
	{
		$text .= ' (id= ' . $atts['post_id'] . ')';
	}
	
	return $text;
}

add_shortcode( 'sc_avia_test_head', 'sc_handler_avia_test_head' );


/**
 * Called right before executing shortcode
 * 
 * @param string $title
 * @param string $original_title
 * @param int|mixed $post_id
 * @return string
 */
function my_avf_the_title_before_shortcode( $title, $original_title, $post_id )
{
	/**
	 * Do any modifications you want to title here
	 */
	
	return $title;
}

add_filter( 'avf_the_title_before_shortcode', 'my_avf_the_title_before_shortcode', 10, 3 );
