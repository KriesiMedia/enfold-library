<?php

/**
 * Enfold hooks into this filter and changes output of fields:  
 * 
 *		add_filter( 'comment_form_fields', 'av_comment_field_order_reset' );
 * 
 * ---> Remove filter in child theme functions.php does not work because too early
 * 
 * In this code we move comment box back to first position as in default WP
 * 
 * @since 4.5.6.1
 * @param array $defaults
 * @return array
 */
function my_comment_form_fields( $defaults )
{
   $comment = $defaults['comment'];
   unset( $defaults['comment'] );

   $new = array_merge( array( 'comment' => $comment ), $defaults );

   return $new;
}

add_filter( 'comment_form_fields', 'my_comment_form_fields', 80, 1 );
