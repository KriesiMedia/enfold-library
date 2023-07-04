<?php
/**
 * Use the "avf_portfolio_sort_first_label" filter changes first label (All) in Portfolio Grid element
 *
 **/
 

function new_portfolio_first_label() 
{
    $first_item_name = " NEW ALL TEXT ";
    return $first_item_name;
}

add_filter( 'avf_portfolio_sort_first_label','new_portfolio_first_label' );
