<?php

/**
 * Enfold hooks into this filter and changes output of fields:  
 * 
 *		add_filter( 'comment_form_fields', 'av_comment_field_order_reset' );
 * 
 * ---> Remove filter in child theme functions.php does not work because too early
 * 
 * In this code we show some examples how to use the filter
 *
 * Other default entries:
 * 
 * author
 * email
 * url
 * cookies
 * 
 * @since 4.5.6.1
 * @param array $defaults
 * @return array
 */
function my_comment_form_fields( $defaults )
{
	//	To move comment box back to first position as in default WP
   $comment = isset( $defaults['comment'] ) ? $defaults['comment'] : '';
   unset( $defaults['comment'] );
   $new = array_merge( array( 'comment' => $comment ), $defaults );
   return $new;
   
   // To make your own order and ensure that element exists before accessing  e.g.
   $new = array(
				'comment' => isset( $defaults['comment'] ) ? $defaults['comment'] : '',
				'author' => isset( $defaults['author'] ) ? $defaults['author'] : '',
				'email' => isset( $defaults['email'] ) ? $defaults['email'] : '',
				'url' => isset( $defaults['url'] ) ? $defaults['url'] : '',
				'cookies' => isset( $defaults['cookies'] ) ? $defaults['cookies'] : '',
			);
   
    return $new;
}
add_filter( 'comment_form_fields', 'my_comment_form_fields', 80, 1 );
