<?php

/* 
 * Filter to customize the sorting options for frontend dropdown
 * 
 * Filter is located: config-woocommerce\config.php    function avia_woocommerce_frontend_search_params
 */
function my_wc_product_order_dropdown_frontend( array $product_order )
{
	
	/**
	 * Remove // for those entries you want to remove
	 * 
	 */
	
//	unset( $product_order['default'] );
//	unset( $product_order['menu_order'] );
//	unset( $product_order['title'] );
//	unset( $product_order['price'] );
//	unset( $product_order['date'] );
//	unset( $product_order['popularity'] );
//	unset( $product_order['rating'] );
//	unset( $product_order['relevance'] );
//	unset( $product_order['rand'] );
//	unset( $product_order['id'] );
	
	/**
	 * To rename remove // and use the following as an example
	 */
	
//	$product_order['default'] = 'my_custom_value';
	
	return $product_order;
}

add_filter( 'avf_wc_product_order_dropdown_frontend', 'my_wc_product_order_dropdown_frontend', 10, 1 );
