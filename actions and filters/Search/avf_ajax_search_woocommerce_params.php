<?php

/**
 * 
 * @since 4.7.3.1
 * @param string $param				'show' | 'hide' | '' for WC default or all
 * @param string $context			'out_of_stock' | 'hidden_products' | 'featured_products'
 * @return string
 */
function custom_avf_ajax_search_woocommerce_params( $param, $context )
{
	switch( $context )
	{
		case 'out_of_stock':
			$param = 'hide';			//	hide out of stock products in search
			break;
		case 'hidden_products':
			$param = 'show';			//	show only hidden products
			break;
		case 'featured_products':
			$param = 'show';			//	show only featured products
			break;
	}
	
	return $param;
}

add_filter( 'avf_ajax_search_woocommerce_params', 'custom_avf_ajax_search_woocommerce_params', 10, 2 );
