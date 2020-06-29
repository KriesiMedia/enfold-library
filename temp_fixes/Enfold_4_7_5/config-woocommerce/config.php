<?php
/**
 * WooCommerce Integration
 * =======================
 * 
 * @since < 4.0
 * @since 4.5.6		modifications for sorting integrations with WC 3.5.7 (backwards comp. with config-356.php)
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


function avia_woocommerce_enabled()
{
	// if( !function_exists( 'wc_get_template_part' ) && class_exists( 'woocommerce' )) return "deprecated";
	if ( class_exists( 'WooCommerce' ) ){ return true; }
	return false;
}

global $avia_config;

//product thumbnails
$avia_config['imgSize']['shop_thumbnail'] 	= array('width'=>120, 'height'=>120);
$avia_config['imgSize']['shop_catalog'] 	= array('width'=>450, 'height'=>450);
$avia_config['imgSize']['shop_single'] 		= array('width'=>450, 'height'=>999, 'crop' => false);

avia_backend_add_thumbnail_size($avia_config);

include('admin-options.php');
include('admin-import.php');
include( 'woocommerce-mod-css-dynamic.php' );

add_theme_support( 'woocommerce' );


function av_add_deprecated_notice()
{
	echo '<div class="notice notice-error">';
	echo	'<p>' .  __('Attention! Please update WooCommerce to the latest version to properly display your products', 'avia_framework') . '</p>';
	echo '</div>';
}



//check if the plugin is enabled, otherwise stop the script
if(avia_woocommerce_enabled() !== true) { 

	if(avia_woocommerce_enabled() == "deprecated")
	{
		add_action('admin_notices', 'av_add_deprecated_notice');
	}
	
	return false; 
}


/**
 * Checks if WooCommerce version is >= $version
 * 
 * @since < 4.0
 * @param string $version
 * @return boolean
 */
function avia_woocommerce_version_check( $version  ) 
{
	global $woocommerce;
	
	if( version_compare( $woocommerce->version, $version, ">=" ) ) 
	{
		return true;
	}
    
	return false;
}



//register my own styles, remove wootheme stylesheet
if( ! is_admin() )
{
	add_action( 'init', 'avia_woocommerce_register_assets' );
}



/**
 * Wrapper function as WC deprecated function get_woocommerce_term_meta with 3.6
 * 
 * @since 4.5.6.1
 * @param int $term_id
 * @param string $key
 * @param bool $single
 * @return mixed
 */
function avia_get_woocommerce_term_meta( $term_id, $key, $single = true )
{
	if( ! avia_woocommerce_version_check( '3.6' ) )
	{
		return get_woocommerce_term_meta( $term_id, $key, $single );
	}
	
	return function_exists( 'get_term_meta' ) ? get_term_meta( $term_id, $key, $single ) : get_metadata( 'woocommerce_term', $term_id, $key, $single );
}


function avia_woocommerce_register_assets()
{
	wp_enqueue_style( 'avia-woocommerce-css', AVIA_BASE_URL.'config-woocommerce/woocommerce-mod.css');
	if( version_compare( WC()->version, '2.7.0', '<' ) )
	{
		wp_enqueue_script( 'avia-woocommerce-js', AVIA_BASE_URL.'config-woocommerce/woocommerce-mod-v26.js', array('jquery'), 1, true);
	}
	else
	{
		wp_enqueue_script( 'avia-woocommerce-js', AVIA_BASE_URL.'config-woocommerce/woocommerce-mod.js', array('jquery'), 1, true);
	}
	
}





global $woocommerce;

if(version_compare($woocommerce->version, "2.1", "<"))
{
	define('WOOCOMMERCE_USE_CSS', false);
}
else
{
	add_filter( 'woocommerce_enqueue_styles', 'avia_woocommerce_enqueue_styles' );
	function avia_woocommerce_enqueue_styles($styles)
	{
		$styles = array();
		return $styles;
	}
}


if ( class_exists( 'WC_Bookings' ) )
{
	require_once( 'config-woocommerce-bookings/config.php' ); //compatibility with woocommerce plugin
}








######################################################################
# config
######################################################################

//add avia_framework config defaults

$avia_config['shop_overview_column']  = get_option('avia_woocommerce_column_count');  // columns for the overview page
$avia_config['shop_overview_products']= get_option('avia_woocommerce_product_count'); // products for the overview page

$avia_config['shop_single_column'] 	 	 = 4;			// columns for related products and upsells
$avia_config['shop_single_column_items'] = 4;	// number of items for related products and upsells
$avia_config['shop_overview_excerpt'] = false;		// display excerpt

if(!$avia_config['shop_overview_column']) $avia_config['shop_overview_column'] = 3;

/**
 * Setup product gallery support depending on user settings and available WooCommerce galleries
 */
if( ! function_exists( 'avia_woocommerce_product_gallery_support_setup' ) )
{
	if ( did_action( 'woocommerce_init' ) )
	{
		avia_woocommerce_product_gallery_support_setup();
	}
	else
	{
		add_action( 'woocommerce_init', 'avia_woocommerce_product_gallery_support_setup', 10);
	}
	
	function avia_woocommerce_product_gallery_support_setup() 
	{
		if( ! avia_woocommerce_version_check( '3.0.0' ) )
		{
			return;
		}
		
		$options = avia_get_option();
		
		//	Fallback, if options have not been saved
		if( ! array_key_exists( 'product_gallery', $options ) || ( 'wc_30_gallery' != $options['product_gallery'] ) )
		{
			$options['product_gallery'] = '';
		}
		
		if( 'wc_30_gallery' == $options['product_gallery'] )
		{
			add_theme_support( 'wc-product-gallery-zoom' );
				//	uncomment the following line if you want default WooCommerce lightbox - else Enfold lightbox will be used
//			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );
			add_theme_support( 'avia-wc-30-product-gallery-feature' );
		}
		
		return;
	}
}

######################################################################
# Allow to add WC structured data on template builder page
######################################################################
#

add_action( 'get_footer', 'avia_activate_wc_structured_data', 10, 1 );


if( ! function_exists( 'avia_activate_wc_structured_data' ) )
{
	/**
	 * 
	 * @param type $name
	 */
	function avia_activate_wc_structured_data( $name )
	{
		global $product;
		
		if( ! avia_woocommerce_version_check( '3.0.0') )
		{
			return;
		}
		
		//	Currently only on single product page with template builder required
		if( ! is_product() ||  ! $product instanceof WC_Product )
		{
			return;
		}
		
		/**
		 * Check necessary data in \woocommerce\includes\class-wc-structured-data.php	 
		 */
		if( ! did_action( 'woocommerce_before_main_content' ) )
		{
			WC()->structured_data->generate_website_data();
		}
		
		if( ! ( did_action( 'woocommerce_shop_loop' ) || did_action( 'woocommerce_single_product_summary' ) ) )
		{
			WC()->structured_data->generate_product_data();
		}
		
			//	not needed on single product page
		if( ! did_action( 'woocommerce_breadcrumb' ) )
		{
//			WC()->structured_data->generate_breadcrumblist_data();
		}
		if( ! did_action( 'woocommerce_review_meta' ) )
		{
//			WC()->structured_data->generate_review_data();
		}
		if( ! did_action( 'woocommerce_email_order_details' ) )
		{
//			WC()->structured_data->generate_order_data();
		}
	}
}

######################################################################
# Create the correct template html structure
######################################################################

//remove woo defaults
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
remove_action( 'woocommerce_pagination', 'woocommerce_catalog_ordering', 20 );
remove_action( 'woocommerce_pagination', 'woocommerce_pagination', 10 );
remove_action( 'woocommerce_before_single_product', array($woocommerce, 'show_messages'), 10);



//add theme actions && filter
add_action( 'woocommerce_after_shop_loop_item_title', 'avia_woocommerce_overview_excerpt', 10);
add_filter( 'loop_shop_columns', 'avia_woocommerce_loop_columns');
add_filter( 'loop_shop_per_page', 'avia_woocommerce_product_count' );

//single page adds
add_action( 'avia_add_to_cart', 'woocommerce_template_single_add_to_cart', 30, 2 );



/*update woocommerce v2*/

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 ); /*remove result count above products*/
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 ); /*remove woocommerce ordering dropdown*/
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 ); //remove rating
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 ); //remove woo pagination



######################################################################
# FUNCTIONS
######################################################################

#
# set the shop page id, otherwise avia_get_the_ID() can return a wrong id on the shop page
#
add_filter( 'avf_avia_get_the_ID', 'avia_set_shop_page_id', 10, 1 );

function avia_set_shop_page_id( $id )
{
    if( is_shop() ) 
	{
		$id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : woocommerce_get_page_id( 'shop' );
	}
	
    return $id;
}

if( ! function_exists( 'avia_woocommerce_thumbnail' ) )
{
	/**
	 * removes the default post image from shop overview pages and replaces it with this image
	 */
	add_action( 'woocommerce_before_shop_loop_item_title', 'avia_woocommerce_thumbnail', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );

	function avia_woocommerce_thumbnail()
	{
		global $product;

		if( function_exists( 'wc_get_rating_html' ) )
		{
			$rating = wc_get_rating_html( $product->get_average_rating() );
		}
		else
		{
			$rating = $product->get_rating_html(); //get rating
		}

		$id = get_the_ID();
		$size = 'shop_catalog';

		$image = get_the_post_thumbnail( $id , $size );
		
		//	try to get fallback image
		if( empty( $image ) )
		{
			$image_url = wc_placeholder_img_src( $size );
			if( ! empty( $image_url ) )
			{
				$image = '<img src="' . $image_url . '" height="450" width="450" alt="' . __( 'Placeholder image', 'avia_framework' ) . '">';
			}
		}
		
		$html  = "<div class='thumbnail_container'>";
		$html .=	avia_woocommerce_gallery_first_thumbnail( $id , $size );
		$html .=	$image;
			
		if( ! empty( $rating ) ) 
		{
			$html .= "<span class='rating_container'>{$rating}</span>";
		}
		if( $product->get_type() == 'simple' )
		{
			$html .= "<span class='cart-loading'></span>";
		}
			
		$html .= '</div>';
		
		echo $html;
	}
}

if( ! function_exists( 'avia_woocommerce_gallery_first_thumbnail' ) )
{
	/**
	 * 
	 * @param int $id
	 * @param string $size
	 * @param boolean $id_only
	 * @return string|int
	 */
	function avia_woocommerce_gallery_first_thumbnail( $id, $size, $id_only = false )
	{
		$image = '';
		$active_hover = get_post_meta( $id, '_product_hover', true );

		if( ! empty( $active_hover ) )
		{
			$product_gallery = get_post_meta( $id, '_product_image_gallery', true );

			if( ! empty( $product_gallery ) )
			{
				$gallery = explode( ',',$product_gallery );
				$image_id = $gallery[0];

				//return id only
				if( ! empty( $id_only ) ) 
				{
					return $image_id;
				}

				$image = wp_get_attachment_image( $image_id, $size, false, array( 'class' => "attachment-$size avia-product-hover" ) );
			}

			return $image;
		}
	}
}




#
# add ajax cart / options buttons to the product
#

add_action( 'woocommerce_after_shop_loop_item', 'avia_add_cart_button', 16);
function avia_add_cart_button()
{
	global $product, $avia_config;

	if ($product->get_type() == 'bundle' ){
		$product = new WC_Product_Bundle($product->get_id());
	}

	$extraClass  = "";

	ob_start();
	woocommerce_template_loop_add_to_cart();
	$output = ob_get_clean();

	if(!empty($output))
	{
		$pos = strpos($output, ">");

		if ($pos !== false) {
		    $output = substr_replace($output,"><span ".av_icon_string('cart')."></span> ", $pos , strlen(1));
		}
	}


	if($product->get_type() == 'variable' && empty($output))
	{
		$output = '<a class="add_to_cart_button button product_type_variable" href="'.get_permalink($product->get_id()).'"><span '.av_icon_string("details").'></span> '.__("Select options","avia_framework").'</a>';
	}

	if(in_array($product->get_type(), array('subscription', 'simple', 'bundle')))
	{
		$output .= '<a class="button show_details_button" href="'.get_permalink($product->get_id()).'"><span '.av_icon_string("details").'></span>  '.__("Show Details","avia_framework").'</a>';
	}
	else
	{
		$extraClass  = "single_button";
	}

	if(empty($extraClass)) $output .= " <span class='button-mini-delimiter'></span>";


	if($output && !post_password_required() && '' == avia_get_option('product_layout',''))
	{
		echo "<div class='avia_cart_buttons $extraClass'>$output</div>";
	}
}





#
# wrap products on overview pages into an extra div for improved styling options. adds "product_on_sale" class if prodct is on sale
#

add_action( 'woocommerce_before_shop_loop_item', 'avia_shop_overview_extra_div', 5);
function avia_shop_overview_extra_div()
{
	global $product;
	$product_class = $product->is_on_sale() ? "product_on_sale" : "";
	$product_class.= " av-product-class-".avia_get_option('product_layout');

	echo "<div class='inner_product main_color wrapped_style noLightbox $product_class'>";
}

add_action( 'woocommerce_after_shop_loop_item',  'avia_close_div', 1000);
function avia_close_div()
{
	echo "</div>";
}


#
# wrap product titles and sale number on overview pages into an extra div for improved styling options
#

add_action( 'woocommerce_before_shop_loop_item_title', 'avia_shop_overview_extra_header_div', 20);
function avia_shop_overview_extra_header_div()
{
	echo "<div class='inner_product_header'><div class='avia-arrow'></div>";
	echo 	"<div class='inner_product_header_table'>";
	echo 		"<div class='inner_product_header_cell'>";
}

add_action( 'woocommerce_after_shop_loop_item_title',  'avia_close_div', 1000);
add_action( 'woocommerce_after_shop_loop_item_title',  'avia_close_div', 1001);
add_action( 'woocommerce_after_shop_loop_item_title',  'avia_close_div', 1002);


#
# remove on sale badge from usual location and add it to the bottom of the product
#
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);


#
# create the shop navigation with account links, as well as cart and checkout, called as fallback function by the wp_nav_menu function in header.php
#
function avia_shop_nav($args)
{
	$output = "";
	$url = avia_collect_shop_urls();

	$output .= "<ul>";

	if( is_user_logged_in() )
	{
		$current = $sub1 = $sub2 = $sub3 = "";
		if(is_account_page()) $current = "current-menu-item";
		if(is_page(get_option('woocommerce_change_password_page_id'))) $sub1 = "current-menu-item";
		if(is_page(get_option('woocommerce_edit_address_page_id'))) $sub2 = "current-menu-item";
		if(is_page(get_option('woocommerce_view_order_page_id'))) $sub3 = "current-menu-item";


		$output .= "<li class='$current account_overview_link'><a href='".$url['account_overview']."'>".__('My Account', 'avia_framework')."</a>";
			$output .= "<ul>";
			$output .= "<li class='$sub1 account_change_pw_link'><a href='".$url['account_change_pw']."'>".__('Change Password', 'avia_framework')."</a></li>";
			$output .= "<li class='$sub2 account_edit_adress_link'><a href='".$url['account_edit_adress']."'>".__('Edit Address', 'avia_framework')."</a></li>";
			$output .= "<li class='$sub3 account_view_order_link'><a href='".$url['account_view_order']."'>".__('View Order', 'avia_framework')."</a></li>";
			$output .= "</ul>";
		$output .= "</li>";
		$output .= "<li class='account_logout_link'><a href='".$url['logout']."'>".__('Log Out', 'avia_framework')."</a></li>";
	}
	else
	{
		$sub1 = $sub2 = "";
		if(is_page(get_option('woocommerce_myaccount_page_id')))
		{
			if(isset($_GET['account_visible']) && $_GET['account_visible'] == 'register') $sub1 = "current-menu-item";
			if(isset($_GET['account_visible']) && $_GET['account_visible'] == 'login') $sub2 = "current-menu-item";
		}

		$url_param = strpos($url['account_overview'], '?') === false ? "?" : "&";

		if (get_option('woocommerce_enable_myaccount_registration') =='yes')
		{
			$output .= "<li class='register_link $sub1'><a href='".$url['account_overview'].$url_param."account_visible=register'>".__('Register', 'avia_framework')."</a></li>";
		}

		$output .= "<li class='login_link $sub2'><a href='".$url['account_overview'].$url_param."account_visible=login'>".__('Log In', 'avia_framework')."</a></li>";
	}

	$output .= "</ul>";

	if($args['echo'] == true)
	{
		echo $output;
	}
	else
	{
		return $output;
	}
}


#
# helper function that collects all the necessary urls for the shop navigation
#

function avia_collect_shop_urls()
{
	global $woocommerce;

	$url['cart']				= $woocommerce->cart->get_cart_url();
	$url['checkout']			= $woocommerce->cart->get_checkout_url();
	$url['account_overview'] 	= get_permalink(get_option('woocommerce_myaccount_page_id'));
	$url['account_edit_adress']	= get_permalink(get_option('woocommerce_edit_address_page_id'));
	$url['account_view_order']	= get_permalink(get_option('woocommerce_view_order_page_id'));
	$url['account_change_pw'] 	= get_permalink(get_option('woocommerce_change_password_page_id'));
	$url['logout'] 				= wp_logout_url(home_url('/'));

	return $url;
}




#
# check which page is displayed and if the automatic sidebar menu for subpages should be prevented
#
add_filter( 'avf_sidebar_menu_filter', 'avia_woocommerce_sidebar_filter');

function avia_woocommerce_sidebar_filter($menu)
{
	$id = avia_get_the_ID();
	if(is_cart() || is_checkout() || get_option('woocommerce_thanks_page_id') == $id){$menu = "";}
	return $menu;
}


#
# check if a single product is displayed and always set the sidebar styling to that of a right sidebar
#
add_filter( 'avf_sidebar_position', 'avia_woocommerce_sidebar_pos');

function avia_woocommerce_sidebar_pos($sidebar)
{
	if(is_product())
	{
		$sidebar = "sidebar_right";
	}

	return $sidebar;
}



function avia_add_to_cart($post, $product )
{
	echo "<div class='avia_cart avia_cart_".$product->get_type()."'>";
	do_action( 'avia_add_to_cart', $post, $product );
	echo "</div>";
}



#
# replace thumbnail image size with full size image on single pages
#
/*

add_filter( 'single_product_small_thumbnail_size', 'avia_woocommerce_thumb_size');

function avia_woocommerce_thumb_size()
{
	return 'shop_single';
}
*/




#
# if we are viewing a woocommerce page modify the breadcrumb nav
#

if(!function_exists('avia_woocommerce_breadcrumb'))
{
	add_filter('avia_breadcrumbs_trail','avia_woocommerce_breadcrumb', 10, 2 );

	function avia_woocommerce_breadcrumb( $trail, $args )
	{
		global $avia_config;

		if(is_woocommerce())
		{
			$front_id	= avia_get_option('frontpage');
			$home 		= isset( $trail[0] ) ? $trail[0] : '';
			$last 		= array_pop($trail);
			$shop_id 	= function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : woocommerce_get_page_id( 'shop' );
			$taxonomy 	= "product_cat";
			
			// on the shop frontpage simply display the shop name, rather than shop name + "All Products"
			if(is_shop())
			{
				if(!empty($shop_id) && $shop_id  != -1)  $trail = array_merge( $trail, avia_breadcrumbs_get_parents( $shop_id ) );
				$last = "";

				if(is_search())
				{
					$last = __('Search results for:','avia_framework').' '.esc_attr($_GET['s']);
				}
			}

			// on the product page single page modify the breadcrumb to read [home] [if available:parent shop pages] [shop] [if available:parent categories] [category] [title]
			if(is_product())
			{
				//fetch all product categories and search for the ones with parents. if none are avalaible use the first category found
				$product_category = $parent_cat = array();
				$temp_cats = get_the_terms(get_the_ID(), $taxonomy);

				if( is_array( $temp_cats ) && ! empty( $temp_cats ) )
				{
					foreach( $temp_cats as $key => $cat )
					{
						if($cat->parent != 0 && !in_array($cat->term_taxonomy_id, $parent_cat))
						{
							$product_category[] = $cat;
							$parent_cat[] = $cat->parent;
						}
					}

					//if no categories with parents use the first one
					if(empty($product_category)) $product_category[] = reset($temp_cats);

				}
				//unset the trail and build our own
				unset($trail);

				$trail = ( empty( $home ) ) ? array() : array( 0 => $home );
				if(!empty($shop_id) && $shop_id  != -1)    $trail = array_merge( $trail, avia_breadcrumbs_get_parents( $shop_id ) );
				if(!empty($parent_cat)) $trail = array_merge( $trail, avia_breadcrumbs_get_term_parents( $parent_cat[0] , $taxonomy ) );
				if(!empty($product_category)) $trail[] = '<a href="' . get_term_link( $product_category[0]->slug, $taxonomy ) . '" title="' . esc_attr( $product_category[0]->name ) . '">' . $product_category[0]->name . '</a>';

			}


			// add the [shop] trail to category/tag pages: [home] [if available:parent shop pages] [shop] [if available:parent categories] [category/tag]
			if(is_product_category() || is_product_tag())
			{
				if(!empty($shop_id) && $shop_id  != -1)
				{
					$shop_trail = avia_breadcrumbs_get_parents( $shop_id ) ;
					array_splice($trail, 1, 0, $shop_trail);
				}
			}

			if(is_product_tag())
			{
				$last = __("Tag",'avia_framework').": ".$last;
			}

			if( ! empty( $last ) ) 
			{
				$trail['trail_end'] = $last;
			}
			
			/**
			 * Allow to remove "Shop" in breadcrumb when shop page is frontpage
			 * 
			 * @since 4.2.7
			 */
			$trail_count = count( $trail );
			if( ( $front_id == $shop_id ) && ! empty( $home ) && ( $trail_count > 1 ) )
			{
				$hide = apply_filters( 'avf_woocommerce_breadcrumb_hide_shop', 'hide', $trail, $args );
				if( 'hide' == $hide )
				{
					$title = get_the_title( $shop_id );
					
					for( $i = 1; $i < $trail_count; $i++ )
					{
						if( false !== strpos( $trail[ $i ], $title ) )
						{
							unset( $trail[ $i ] );
							break;
						}
					}
					
					$trail = array_merge( $trail );
				}
			}
		}

		return $trail;
	}

}



#
# creates the avia framework container arround the shop pages
#
add_action( 'woocommerce_before_main_content', 'avia_woocommerce_before_main_content', 10);


function avia_woocommerce_before_main_content()
{
	global $avia_config;

	if(!isset($avia_config['shop_overview_column'])) $avia_config['shop_overview_column'] = "auto";
	
	$id = get_option('woocommerce_shop_page_id');
	$layout = get_post_meta($id, 'layout', true);
	
	if(!empty($layout))
	{
        	$avia_config['layout']['current'] = $avia_config['layout'][$layout];
        	$avia_config['layout']['current']['main'] = $layout;
	}

    	$avia_config['layout'] = apply_filters('avia_layout_filter', $avia_config['layout'], $id);

	$title_args = array();

	if(is_woocommerce())
	{
		$t_link = "";

		if(is_shop()) $title  = get_option('woocommerce_shop_page_title');

		$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : woocommerce_get_page_id( 'shop' );
		if($shop_id && $shop_id != -1)
		{
			if(empty($title)) $title = get_the_title($shop_id);
			$t_link = get_permalink($shop_id);
		}

		if(empty($title)) $title  = __("Shop",'avia_framework');

		if(is_product_category() || is_product_tag())
        {
            global $wp_query;
            $tax = $wp_query->get_queried_object();
            $title = $tax->name;
            $t_link = '';
        }

		$title_args = array('title' => $title, 'link' => $t_link);
	}

	if( get_post_meta(get_the_ID(), 'header', true) != 'no') echo avia_title($title_args);
	
	
	if(is_singular()) { 
		
		$result = 'sidebar_right';
		$avia_config['layout']['current'] = $avia_config['layout'][$result];
		$avia_config['layout']['current']['main'] = $result;
		
	}
	$sidebar_setting = avia_layout_class( 'main' , false );
	echo "<div class='container_wrap container_wrap_first main_color {$sidebar_setting} template-shop shop_columns_".$avia_config['shop_overview_column']."'>";

		echo "<div class='container'>";

		if(!is_singular()) { $avia_config['overview'] = true; }
}

#
# closes the avia framework container arround the shop pages
#

add_action( 'woocommerce_after_main_content', 'avia_woocommerce_after_main_content', 10);
function avia_woocommerce_after_main_content()
{

	global $avia_config;
	$avia_config['currently_viewing'] = "shop";

			//reset all previous queries
			wp_reset_query();

			//get the sidebar
			if(!is_singular())
			get_sidebar();

	//	echo "</div>"; // end container - gets already closed at the top of footer.php

		echo "</div>"; // end tempate-shop content
		echo "</div>"; // close default .container_wrap element
}


add_action( 'avf_custom_sidebar', 'avia_woocommerce_custom_sidebar', 10);
function avia_woocommerce_custom_sidebar($sidebar)
{
	if(is_shop())
	{
		$the_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : woocommerce_get_page_id( 'shop' );
		$sidebar = get_post_meta($the_id, 'sidebar', true);	
	}
	
	return $sidebar;
}




#
# wrap an empty product search into extra div
#
add_action( 'woocommerce_before_main_content', 'avia_woocommerce_404_search', 9111);
function avia_woocommerce_404_search()
{
	global $wp_query;

	if( (is_search() || is_archive()) && empty($wp_query->found_posts) )
	{
		echo "<div class='template-page template-search template-search-none content ".avia_layout_class( 'content', false )." units'>";
		echo "<div class='entry entry-content-wrapper' id='search-fail'>";
	}
}

add_action( 'woocommerce_after_main_content', 'avia_woocommerce_404_search_close', 1);
function avia_woocommerce_404_search_close()
{
	global $wp_query;

	if( (is_search() || is_shop() || is_archive()) && empty($wp_query->found_posts) )
	{
		get_template_part('includes/error404');
		echo "</div>";
		echo "</div>"; // close default .container_wrap element
	}
}




#
# modifies the class of a page so we can display single login and single register
#
add_filter( 'avia_layout_class_filter_main', 'avia_register_login_class');

function avia_register_login_class($layout)
{
	if(isset($_GET['account_visible']))
	{
		if($_GET['account_visible'] == 'register') $layout .= " template-register";
		if($_GET['account_visible'] == 'login') $layout .= " template-login";
	}

	return $layout;
}







#
# creates the avia framework content container arround the shop loop
#
add_action( 'woocommerce_before_shop_loop', 'avia_woocommerce_before_shop_loop', 1);

function avia_woocommerce_before_shop_loop()
{

	global $avia_config;
	if(isset($avia_config['dynamic_template'])) return;
    $markup = avia_markup_helper(array('context' => 'content','echo'=>false,'post_type'=>'products'));
	echo "<main class='template-shop content ".avia_layout_class( 'content' , false)." units' $markup><div class='entry-content-wrapper'>";
}

#
# closes the avia framework content container arround the shop loop
#
add_action( 'woocommerce_after_shop_loop', 'avia_woocommerce_after_shop_loop', 10);

function avia_woocommerce_after_shop_loop()
{
			global $avia_config;
			if(isset($avia_config['dynamic_template'])) return;
			if(isset($avia_config['overview'] )) echo avia_pagination('', 'nav');
			echo "</div></main>"; //end content
}



#
# echo the excerpt
#
function avia_woocommerce_overview_excerpt()
{
	global $avia_config;

	if(!empty($avia_config['shop_overview_excerpt']))
	{
		echo "<div class='product_excerpt'>";
		the_excerpt();
		echo "</div>";
	}
}



#
# creates the preview images based on page/category image
#
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
remove_action( 'woocommerce_product_archive_description', 'woocommerce_product_archive_description', 10 );

add_action( 'woocommerce_before_shop_loop', 'avia_woocommerce_overview_banner_image', 10);
add_action( 'woocommerce_before_shop_loop', 'woocommerce_taxonomy_archive_description', 11 );
//add_action( 'woocommerce_before_shop_loop', 'woocommerce_product_archive_description', 12 ); //causes warning


function avia_woocommerce_overview_banner_image()
{
	global $avia_config;
	if(avia_is_dynamic_template() || is_paged() || is_search() ) return false;

	$image_size = "entry_with_sidebar";
	$layout = avia_layout_class( 'main' , false );
	if($layout == 'fullsize') $image_size = 'entry_without_sidebar';

	if(is_shop())
	{
		$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : woocommerce_get_page_id( 'shop' );
		if($shop_id != -1)
		{
			$image = get_the_post_thumbnail($shop_id, $image_size);
			if($image) echo "<div class='page-thumb'>{$image}</div>";
		}
	}
	
	

	if(is_product_category())
	{
		global $wp_query;
		$image	= "";
		if(isset($wp_query->query_vars['taxonomy']))
		{
			$term = get_term_by( 'slug', get_query_var($wp_query->query_vars['taxonomy']), $wp_query->query_vars['taxonomy']);

			if(!empty($term->term_id))
			{
				$attachment_id 	= avia_get_woocommerce_term_meta($term->term_id, 'thumbnail_id');
				$style 			= avia_get_woocommerce_term_meta($term->term_id, 'av_cat_styling');
				
				if(!empty($attachment_id) && empty($style))
				{
					$image = wp_get_attachment_image( $attachment_id, $image_size, false, array('class'=>'category_thumb'));
					if($image) echo "<div class='page-thumb'>{$image}</div>";
				}
				
			}
		}
	}

}



add_action( 'ava_after_main_container', 'avia_woocommerce_big_cat_banner', 11 );


function avia_woocommerce_big_cat_banner()
{
	if(is_product_category())
	{
		global $wp_query, $avia_config;
		
		if(isset($wp_query->query_vars['taxonomy']))
		{	
			$term = get_term_by( 'slug', get_query_var($wp_query->query_vars['taxonomy']), $wp_query->query_vars['taxonomy']);
			if( ! empty( $term->term_id ) )
			{
				$description 	= term_description() ;
				$style		= avia_get_woocommerce_term_meta( $term->term_id, 'av_cat_styling' );
				$attachment_id 	= avia_get_woocommerce_term_meta( $term->term_id, 'thumbnail_id' );
				
				$overlay 	= avia_get_woocommerce_term_meta( $term->term_id, 'av-banner-overlay' );
				$font 		= avia_get_woocommerce_term_meta( $term->term_id, 'av-banner-font' );
				$opacity 	= avia_get_woocommerce_term_meta( $term->term_id, 'av-banner-overlay-opacity' );
				
				if(!empty($style))
				{
					remove_action( 'woocommerce_before_shop_loop', 'woocommerce_taxonomy_archive_description', 11 );
					echo avia_woocommerce_parallax_banner($attachment_id, $overlay, $opacity, $description, $font);
					$avia_config['woo-banner'] = true;
				}
			}
		}
	}
}



add_action( 'ava_after_main_container', 'avia_woocommerce_shop_banner', 11 );

function avia_woocommerce_shop_banner()
{
	global $avia_config;
	
	if(is_shop() || (is_product_category() && avia_get_option('shop_banner_global') == "shop_banner_global") && !isset($avia_config['woo-banner']))
	{
		$options = avia_get_option();
		
		if( isset( $options['shop_banner'] )  && ( $options['shop_banner'] == 'av-active-shop-banner' ) )
		{
			$bg 		= $options['shop_banner_image'];
			$overlay 	= $options['shop_banner_overlay_color'];
			$opacity 	= $options['shop_banner_overlay_opacity'];
			$description= wpautop($options['shop_banner_message']);
			$font 		= $options['shop_banner_message_color'];
			
			echo avia_woocommerce_parallax_banner($bg, $overlay, $opacity, $description, $font);
		}
	}
}


function avia_woocommerce_parallax_banner( $bg, $overlay, $opacity, $description, $font )
{
	
	if( is_numeric( $bg ) ) 
	{
		$bg = wp_get_attachment_image_src($bg, 'extra_large');
		$bg = ( is_array( $bg ) && $bg[0] != '' ) ? $bg[0] : '';
	}
	
	if( $font ) 
	{
		$font = "style='color:{$font};'";
	}
	
	if( $bg ) 
	{
		$bg = "background-image: url(".$bg.");";
	}
	
	$output = '';
					
	$output .='<div id="av_product_description" class="avia-section main_color avia-section-large avia-no-border-styling avia-full-stretch av-parallax-section av-section-color-overlay-active avia-bg-style-parallax container_wrap fullsize" data-section-bg-repeat="stretch" '.$font.'>';
	$output .='<div class="av-parallax avia-full-stretch" data-avia-parallax-ratio="0.3">';
	$output .='<div class="av-parallax-inner av-parallax-woo" style="'.$bg.' main_color background-attachment: scroll; background-position: 50% 50%; background-repeat: no-repeat;">';
	$output .='</div>';
	$output .='</div>';
	
	
	$output .='<div class="av-section-color-overlay-wrap">';
	if(!empty($overlay))
	{
		$output .='<div class="av-section-color-overlay" style="opacity: '.$opacity.'; background-color: '.$overlay.'; "></div>';
	}
	
	$output .='<div class="container">';
	$output .='<main class="template-page content av-content-full alpha units">';
	if($description) $output .= "<h1>".$description."</h1>";
	$output .='</main></div></div></div>';
	
	return $output;
}


#
# creates the title + description for overview pages
#
function avia_woocommerce_advanced_title()
{

	global $wp_query;
	$titleClass 	= "";
	$image		 	= "";


	if(!empty($attachment_id))
	{
		$titleClass .= "title_container_image ";
		$image		= wp_get_attachment_image( $attachment_id, 'thumbnail', false, array('class'=>'category_thumb'));
	}

	echo "<div class='extralight-border title_container shop_title_container $titleClass'>";
	//echo avia_breadcrumbs();
	woocommerce_catalog_ordering();
	echo $image;
}









#
# modify shop overview column count
#
function avia_woocommerce_loop_columns()
{
	global $avia_config;
	return $avia_config['shop_overview_column'];
}


#
# modify shop overview product count
#

function avia_woocommerce_product_count()
{
	global $avia_config;
	return $avia_config['shop_overview_products'];
}


#
# filter cross sells on the cart page. display 4 on fullwidth pages and 3 on carts with sidebar
#

add_filter('woocommerce_cross_sells_total', 'avia_woocommerce_cross_sale_count');
add_filter('woocommerce_cross_sells_columns', 'avia_woocommerce_cross_sale_count');

function avia_woocommerce_cross_sale_count($count)
{
	return 4;
}

#
# move cross sells below the shipping
#

remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' , 10);




#
# display tabs and related items within the summary wrapper
#
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_action(    'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 1 );



#
# display upsells and related products within dedicated div with different column and number of products
#
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products',20);
remove_action( 'woocommerce_after_single_product', 'woocommerce_output_related_products',10);
add_action( 'woocommerce_after_single_product_summary', 'avia_woocommerce_output_related_products', 20);

function avia_woocommerce_output_related_products($items = false, $columns = false)
{
	global $avia_config;
	$output = "";
	
	if(!$items) 	$items 	 = $avia_config['shop_single_column_items'];
	if(!$columns) 	$columns = $avia_config['shop_single_column'];

	ob_start();
	woocommerce_related_products(array('posts_per_page'=>$items, 'columns'=>$columns)); // X products, X columns
	$content = ob_get_clean();
	if($content)
	{
		$output .= "<div class='product_column product_column_".$columns."'>";
		//$output .= "<h3>".(__('Related Products', 'avia_framework'))."</h3>";
		$output .= $content;
		$output .= "</div>";
	}

	$avia_config['woo_related'] = $output;
	return $output;

}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
remove_action( 'woocommerce_after_single_product', 'woocommerce_upsell_display',10);
add_action( 'woocommerce_after_single_product_summary', 'avia_woocommerce_output_upsells', 21); // needs to be called after the "related product" function to inherit columns and product count

function avia_woocommerce_output_upsells($items = false, $columns = false)
{
	global $avia_config;

	$output = "";
	
	if(!$items) 	$items 	 = $avia_config['shop_single_column_items'];
	if(!$columns) 	$columns = $avia_config['shop_single_column'];
	
	ob_start();
	woocommerce_upsell_display($items,$columns); // 4 products, 4 columns
	$content = ob_get_clean();
	if($content)
	{
		$output .= "<div class='product_column product_column_".$columns."'>";
		//$output .= "<h3>".(__('You may also like', 'avia_framework'))."</h3>";
		$output .= $content;
		$output .= "</div>";
	}

	$avia_config['woo_upsells'] = $output;
	return $output;

}

add_action( 'woocommerce_after_single_product_summary', 'avia_woocommerce_display_output_upsells', 30); //display the related products and upsells

function avia_woocommerce_display_output_upsells()
{
	global $avia_config;

	$sells = isset( $avia_config['woo_upsells'] ) ? $avia_config['woo_upsells'] : '';
	$related = isset( $avia_config['woo_related'] ) ? $avia_config['woo_related'] : '';

	$products = $sells . $related;

	if( ! empty( $products ) )
	{

		$output  = "</div></div></div>";
		$output .= '<div id="av_section_1" class="avia-section alternate_color avia-section-small  container_wrap fullsize"><div class="container"><div class="template-page content  twelve alpha units">';
		$output .= $products;

		echo $output;
	}
}


if( ! function_exists( 'avia_before_get_sidebar_template_builder' ) && avia_woocommerce_enabled() )
{
	/**
	 * Single Product page on ALB: we need to change sidebar - otherwise we have blog or page resulting in a wrong output
	 * 
	 * @since 4.5.5
	 */
	function avia_before_get_sidebar_template_builder()
	{
		global $avia_config;
		
		if( is_product() ) 
		{
			$avia_config['currently_viewing'] = 'shop_single';
		}
		else if( is_page ( wc_get_page_id( 'shop' ) ) )
		{
			$avia_config['currently_viewing'] = 'shop';
		}
	}
	
	add_action( 'ava_before_get_sidebar_template_builder', 'avia_before_get_sidebar_template_builder', 10 );
}
		

#
# wrap single product image in an extra div
#
add_action( 'woocommerce_before_single_product_summary', 'avia_add_image_div', 2);
add_action( 'woocommerce_before_single_product_summary',  'avia_close_image_div', 20);

if(!function_exists('avia_add_image_div'))
{
	function avia_add_image_div()
	{
		$nolightbox = '';
		$icon = '';
		
		if( avia_woocommerce_version_check( '3.0.0' ) )
		{
			if( current_theme_supports( 'wc-product-gallery-lightbox' ) )
			{
				$nolightbox = 'noLightbox';
			}
			else if( current_theme_supports( 'avia-wc-30-product-gallery-feature' ) )
			{
				$nolightbox = 'noHover';
				$icon = '<div class="avia-wc-30-product-gallery-lightbox" '.av_icon_string('search').' ></div>';
			}
		}
		
		echo '<div class="' . $nolightbox . ' single-product-main-image alpha">' . $icon;
	}
}



if(!function_exists('avia_close_image_div'))
{
	function avia_close_image_div()
	{
		global $avia_config;
		if(is_product()) {
		$avia_config['currently_viewing'] = "shop_single";
		get_sidebar();
		}
		echo "</div>";
	}
}



#
# wrap single product summary in an extra div
#
add_action( 'woocommerce_before_single_product_summary', 'avia_add_summary_div', 25);
add_action( 'woocommerce_after_single_product_summary',  'avia_close_div', 3);

if(!function_exists('avia_add_summary_div'))
{
	function avia_add_summary_div()
	{
		echo "<div class='single-product-summary'>";
	}
}

//remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );

if(avia_woocommerce_version_check('3.0.0')) // in woocommerce 3.0.0
{
	add_action('woocommerce_product_thumbnails', 'avia_product_gallery_thumbnail_opener', 19);
	add_action('woocommerce_product_thumbnails',  'avia_close_div', 21);
}

if(!function_exists('avia_product_gallery_thumbnail_opener'))
{
	function avia_product_gallery_thumbnail_opener()
	{
		echo "<div class='thumbnails'>";
	}
}


if( ! function_exists( 'avia_woocommerce_frontend_search_params' ) )
{
	add_action( 'woocommerce_before_shop_loop', 'avia_woocommerce_frontend_search_params', 20 );

	/**
	 * Displays a front end interface for modifying the shoplist query parameters like sorting order, product count etc
	 * 
	 * @since < 4.0
	 */
	function avia_woocommerce_frontend_search_params()
	{
		global $avia_config;

		if( ! empty( $avia_config['woocommerce']['disable_sorting_options'] ) ) 
		{
			return;
		}

		$product_order['default'] = __( 'Default', 'avia_framework' );
		$product_order['menu_order'] = __( 'Custom', 'avia_framework' );
		$product_order['title'] = __( 'Name', 'avia_framework' );
		$product_order['price'] = __( 'Price', 'avia_framework' );
		$product_order['date'] = __( 'Date', 'avia_framework' );
		$product_order['popularity'] = __( 'Popularity (sales)', 'avia_framework' );
		$product_order['rating'] = __( 'Average rating', 'avia_framework' );
		$product_order['relevance'] = __( 'Relevance', 'avia_framework' );
		$product_order['rand'] = __( 'Random', 'avia_framework' );
		$product_order['id'] = __( 'Product ID', 'avia_framework' );
		
		/**
		 * 
		 * @since 4.5.6.2
		 * @return array
		 */
		$product_order = apply_filters( 'avf_wc_product_order_dropdown_frontend', $product_order );

		$product_sort['asc'] = __( 'Click to order products ascending', 'avia_framework' );
		$product_sort['desc'] = __( 'Click to order products descending', 'avia_framework' );

		$per_page_string = __( 'Products per page', 'avia_framework' );
		

		$per_page = get_option( 'avia_woocommerce_product_count' );
		if( ! $per_page ) 
		{
			$per_page = get_option( 'posts_per_page' );
		}
		
		/**
		 * ALB elements can return all elements = -1
		 */
		if( ! empty( $avia_config['woocommerce']['default_posts_per_page'] ) && is_numeric( $avia_config['woocommerce']['default_posts_per_page'] ) ) 
		{
			if( $avia_config['woocommerce']['default_posts_per_page'] > 0 )
			{
				$per_page = $avia_config['woocommerce']['default_posts_per_page'];
			}
		}
		
		parse_str( $_SERVER['QUERY_STRING'], $params );
		
		
		if( ! isset( $params['product_order'] ) )
		{
			$po_key = 'default';
		}
		else
		{
			$po_key = $params['product_order'];
		}
		
		if( ! isset( $params['product_sort'] ) )
		{
			$ps_key = ! empty( $avia_config['woocommerce']['product_sort'] ) ? $avia_config['woocommerce']['product_sort'] : 'asc';
		}
		else
		{
			$ps_key = $params['product_sort'];
		}
		
		if( 'default' == $po_key )
		{
			unset( $params['product_sort'] );
		}
		
		$params['avia_extended_shop_select'] = 'yes';

//		$po_key = ! empty( $avia_config['woocommerce']['product_order'] ) ? $avia_config['woocommerce']['product_order'] : $params['product_order'];
//		$ps_key = ! empty( $avia_config['woocommerce']['product_sort'] ) ? $avia_config['woocommerce']['product_sort'] : $params['product_sort'];
		$pc_key = ! empty( $avia_config['woocommerce']['product_count'] ) ? $avia_config['woocommerce']['product_count'] : $per_page;

		$ps_key = strtolower( $ps_key );
		
		$show_sort = ! in_array( $po_key, array( 'rand', 'popularity', 'rating', 'default' ) );
		
		$nofollow = 'rel="nofollow"';

		//generate markup
		$output  =	'';
		$output .=	"<div class='product-sorting'>";
		$output .=		"<ul class='sort-param sort-param-order'>";
		$output .=			"<li><span class='currently-selected'>" . __( 'Sort by', 'avia_framework' ) . " <strong>{$product_order[$po_key]}</strong></span>";
		$output .=				"<ul>";
		
		foreach ( $product_order as $order_key => $order_text ) 
		{
			$query_string = 'default' == $order_key ? avia_woo_build_query_string( $params, 'product_order', $order_key, 'product_sort' ) : avia_woo_build_query_string( $params, 'product_order', $order_key );
					
			$output .=				'<li' . avia_woo_active_class( $po_key, $order_key ) . '>';
			$output .=					"<a href='{$query_string}' {$nofollow}>";
			$output .=						"<span class='avia-bullet'></span>{$order_text}";
			$output .=					'</a>';
			$output .=				'</li>';
		}
		
		$output .=				'</ul>';
		$output .=			'</li>';
		$output .=		'</ul>';

		if( $show_sort )
		{
			$output .=	"<ul class='sort-param sort-param-sort'>";
			$output .=		'<li>';

			if( $ps_key == 'desc' )
			{
			$output .=			"<a title='{$product_sort['asc']}' class='sort-param-asc'  href='" . avia_woo_build_query_string($params, 'product_sort', 'asc' ) . "' {$nofollow}>{$product_sort['desc']}</a>";
			}
			if( $ps_key == 'asc' )
			{
			$output .=			"<a title='{$product_sort['desc']}' class='sort-param-desc' href='" . avia_woo_build_query_string($params, 'product_sort', 'desc' ) . "' {$nofollow}>{$product_sort['asc']}</a>";
			}
		
			$output .=		'</li>';
			$output .=	'</ul>';
		}
		
		if( ! isset( $avia_config['woocommerce']['default_posts_per_page'] ) || ( $avia_config['woocommerce']['default_posts_per_page'] > 0 ) )
		{
			$output .=	"<ul class='sort-param sort-param-count'>";
			$output .=		"<li><span class='currently-selected'>".__("Display",'avia_framework')." <strong>".$pc_key." ".$per_page_string."</strong></span>";
			$output .=			'<ul>';
			$output .=				"<li" . avia_woo_active_class( $pc_key, $per_page ) . "><a href='" . avia_woo_build_query_string( $params, 'product_count', $per_page ) . "' {$nofollow}>		<span class='avia-bullet'></span>{$per_page} {$per_page_string}</a></li>";
			$output .=				"<li" . avia_woo_active_class( $pc_key, $per_page*2 ) . "><a href='" . avia_woo_build_query_string( $params, 'product_count', $per_page * 2 ) . "' {$nofollow}>	<span class='avia-bullet'></span>" . ( $per_page * 2 ) . " {$per_page_string}</a></li>";
			$output .=				"<li" . avia_woo_active_class( $pc_key, $per_page*3 ) . "><a href='" . avia_woo_build_query_string( $params, 'product_count', $per_page * 3 ) . "' {$nofollow}>	<span class='avia-bullet'></span>" . ( $per_page * 3 ) . " {$per_page_string}</a></li>";
			$output .=			'</ul>';
			$output .=		'</li>';
			$output .=	'</ul>';
		}


		$output .= '</div>';
		echo $output;
	}
}


if( ! function_exists( 'avia_woocommerce_ajax_search_params' ) )
{
	/**
	 * Add support for WC product display settings
	 * 
	 * @since 4.7.3.1
	 * @param array $params
	 * @return array
	 */
	function avia_woocommerce_ajax_search_params( $params = array() )
	{
		if( ! avia_woocommerce_enabled() )
		{
			return $params;
		}
		
		if( ! avia_woocommerce_version_check( '3.0.0') )
		{
			return $params;
		}
		
		/**
		 * 
		 * @since 4.7.3.1
		 * @param string $visibility			'show'|'hide'|'' for WC default
		 * @param string $context
		 * @return string						'show'|'hide'|'' for WC default
		 */
		$products_visibility = apply_filters( 'avf_ajax_search_woocommerce_params', '', 'out_of_stock' );
		
		/**
		 * 
		 * @since 4.7.3.1
		 * @param string $visibility			'show'|'hide'|'' for WC default
		 * @param string $context
		 * @return string						'show'|'hide'|'' for all
		 */
		$prod_hidden = apply_filters( 'avf_ajax_search_woocommerce_params', '', 'hidden_products' );
		
		/**
		 * 
		 * @since 4.7.3.1
		 * @param string $visibility			'show'|'hide'|'' for WC default
		 * @param string $context
		 * @return string						'show'|'hide'|'' for all
		 */
		$prod_featured = apply_filters( 'avf_ajax_search_woocommerce_params', '', 'featured_products' );
		
		// Meta query - replaced by Tax query in WC 3.0.0
		$meta_query = array();
		$tax_query = array();

		avia_wc_set_out_of_stock_query_params( $meta_query, $tax_query, $products_visibility );
		avia_wc_set_hidden_prod_query_params( $meta_query, $tax_query, $prod_hidden );
		avia_wc_set_featured_prod_query_params( $meta_query, $tax_query, $prod_featured );
		
		if( empty( $tax_query ) )
		{
			return $params;
		}
		
		if( ! isset( $params['tax_query'] ) || ! is_array( $params['tax_query'] ) )
		{
			$params['tax_query'] = array();
		}
			
		foreach( $tax_query as $value ) 
		{
			$params['tax_query'][] = $value;
		}
		
		return $params;
	}
	
	add_filter( 'avf_ajax_search_query', 'avia_woocommerce_ajax_search_params', 20, 1 );
}


if( ! function_exists( 'avia_woo_active_class' ) )
{
	/**
	 * Helper function to create the active list class
	 * 
	 * @param string $key1
	 * @param string $key2
	 * @return string
	 */
	function avia_woo_active_class( $key1, $key2 )
	{
		return ( $key1 == $key2 ) ? " class='current-param'" : '';
	}
}


if( ! function_exists( 'avia_woo_build_query_string' ) )
{
	/**
	 * helper function to build the query strings for the catalog ordering menu
	 * 
	 * @since < 4.0
	 * @param array $params
	 * @param string $overwrite_key
	 * @param string $overwrite_value
	 * @param string $remove_key
	 * @return string
	 */
	function avia_woo_build_query_string( $params = array(), $overwrite_key = '', $overwrite_value = '', $remove_key = '' )
	{
		if( ! empty( $overwrite_key ) )
		{
			$params[ $overwrite_key ] = $overwrite_value;
		}
		
		if( ! empty( $remove_key ) )
		{
			unset( $params[ $remove_key ] );
		}
		
		$paged = ( array_key_exists( 'product_count', $params ) ) ? 'paged=1&' : '';
		
		return "?" . $paged . http_build_query( $params );
	}
}


if( ! function_exists( 'avia_woocommerce_overwrite_catalog_ordering' ) )
{
	add_action( 'woocommerce_get_catalog_ordering_args', 'avia_woocommerce_overwrite_catalog_ordering', 20, 1 );

	/**
	 * Overwrite the query parameters from WooCommerce
	 * 
	 * @since < 4.0
	 * @param array $args
	 * @return string
	 */
	function avia_woocommerce_overwrite_catalog_ordering( $args )
	{
		global $avia_config;

		if( empty( $avia_config['woocommerce'] ) ) 
		{
			$avia_config['woocommerce'] = array();
		}
		
		if( ! empty( $avia_config['woocommerce']['disable_sorting_options'] ) ) 
		{
			return $args;
		}
		
		/**
		 * WC added shortcodes that use this filter (e.g. products).
		 * We only need to alter the query when we have our select boxes.
		 * 
		 * LINITATION: It is not possible to mix shop overview (= shop) and other shortcodes because we cannot distinguish when this filter is called !!! 
		 */
		if( ! isset( $_REQUEST['avia_extended_shop_select'] ) || ( 'yes' != $_REQUEST['avia_extended_shop_select'] ) )
		{
			$avia_config['woocommerce']['product_sort'] = strtolower( $args['order'] );
			$avia_config['woocommerce']['product_order'] = strtolower( $args['orderby'] );
		
			return $args;
		}
		
		//check the folllowing get parameters and session vars. if they are set overwrite the defaults
		$check = array( 'product_order', 'product_count', 'product_sort' );
		
		foreach( $check as $key )
		{
			if( isset( $_GET[ $key ] ) )
			{
				$_SESSION['avia_woocommerce'][ $key ] = esc_attr( $_GET[ $key ] );
			}
			if( isset( $_SESSION['avia_woocommerce'][ $key ] ) ) 
			{
				$avia_config['woocommerce'][ $key ] = $_SESSION['avia_woocommerce'][ $key ];
			}
		}

		// if user wants to use new product order remove the old sorting parameter
		if( isset( $_GET['product_order'] ) && ! isset( $_GET['product_sort'] ) && isset( $_SESSION['avia_woocommerce']['product_sort'] ) )
		{
			unset( $_SESSION['avia_woocommerce']['product_sort'], $avia_config['woocommerce']['product_sort'] );
		}
		
		$orderby = '';
		$order = '';
		
		/**
		 * Set the product sorting
		 */
		$product_sort = '';
		if( isset( $avia_config['woocommerce']['product_sort'] ) )
		{
			$product_sort = strtoupper( $avia_config['woocommerce']['product_sort'] );
			switch ( $product_sort ) 
			{
				case 'DESC': 
				case 'ASC':
					break;
				default: 
					$product_sort = 'ASC'; 
					break;
			}
		}
		
		/**
		 * Set the product order with default sortings
		 */
		$product_order = isset( $avia_config['woocommerce']['product_order'] ) ? $avia_config['woocommerce']['product_order'] :'';
		switch ( $product_order ) 
		{
			case 'id':
			case 'relevance':
			case 'date': 
				$orderby = $product_order;
				$order = ! empty( $product_sort ) ? $product_sort : 'DESC'; 
				break;
			case 'menu_order':
			case 'title' :
			case 'price' :
				$orderby = $product_order;  
				$order = ! empty( $product_sort ) ? $product_sort : 'ASC';
				break;
			case 'rand':
			case 'popularity':
			case 'rating':
				$orderby = $product_order;
				break;
			case 'default':
			default: 
				$orderby = '';
				break;
		}
		
		WC()->query->remove_ordering_args();
		
		$old_disable_sorting_options = isset( $avia_config['woocommerce']['disable_sorting_options'] ) ? $avia_config['woocommerce']['disable_sorting_options'] : null;
		$avia_config['woocommerce']['disable_sorting_options'] = true;
		
		$new_args = WC()->query->get_catalog_ordering_args( $orderby, $order );

		if( ! is_null( $old_disable_sorting_options) )
		{
			$avia_config['woocommerce']['disable_sorting_options'] = $old_disable_sorting_options;
		}
		else
		{
			unset( $avia_config['woocommerce']['disable_sorting_options'] );
		}
		
		
		/**
		 * set the product count
		 */
		if( isset( $avia_config['woocommerce']['product_count'] ) && is_numeric( $avia_config['woocommerce']['product_count'] ) )
		{
			$avia_config['shop_overview_products_overwritten'] = true;
			$avia_config['shop_overview_products'] = (int) $avia_config['woocommerce']['product_count'];
		}
		
		$avia_config['woocommerce']['product_order'] = strtolower( $new_args['orderby'] );
		$avia_config['woocommerce']['product_sort'] = strtolower( $new_args['order'] );
		
		return $new_args;
	}


}

//remove produt information on password protected products
if(!function_exists('avia_woocommerce_remove_hooks'))
{
	add_action('woocommerce_before_single_product', 'avia_woocommerce_remove_hooks');

	function avia_woocommerce_remove_hooks()
	{
		/*remove content from password protected products*/
		if(post_password_required())
		{
			add_action( 'woocommerce_after_single_product_summary', 'avia_woocommerce_echo_password', 1 );
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 1 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
			remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
			remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
			remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		}
	}
}

if(!function_exists('avia_woocommerce_echo_password'))
{
	add_action('ava_woocomemrce_password_protection_remove_hooks', 'avia_woocommerce_remove_hooks');

	function avia_woocommerce_echo_password()
	{
		/*remove content from password protected products*/
		if(post_password_required())
		{
			echo get_the_password_form();
		}
	}
}

if( ! function_exists( 'avia_woocommerce_product_gallery_support' ) )
{
	if ( did_action( 'woocommerce_init' ) )
	{
		avia_woocommerce_product_gallery_support();
	}
	else
	{
		add_action( 'woocommerce_init', 'avia_woocommerce_product_gallery_support', 50 );
	}
	
	function avia_woocommerce_product_gallery_support() 
	{
		if( avia_woocommerce_version_check( '3.0.0' ) && current_theme_supports( 'avia-wc-30-product-gallery-feature' ) ) 
		{
			remove_action( 'woocommerce_product_thumbnails', 'avia_product_gallery_thumbnail_opener', 19 );
			remove_action( 'woocommerce_product_thumbnails', 'avia_close_div', 21 );
		} 
		else 
		{
			add_filter( 'woocommerce_single_product_image_thumbnail_html','avia_woocommerce_gallery_thumbnail_description', 10, 4 );
		}
	}
}

/*
	single page big image and thumbnails are using the same filter now. therefore we need to make sure that the images get the correct size by storing once the 
	woocommerce_product_thumbnails action has been called
*/

add_action('woocommerce_product_thumbnails', 'avia_woocommerce_set_single_page_image_size');

if(!function_exists('avia_woocommerce_set_single_page_image_size'))
{
	function avia_woocommerce_set_single_page_image_size()
	{
		global $avia_config;
		
		if(!isset($avia_config['avwc-single-page-size']))
		{
			$avia_config['avwc-single-page-size'] = "shop_thumbnail";
		}
	}
}


if(!function_exists('avia_woocommerce_gallery_thumbnail_description'))
{
	function avia_woocommerce_gallery_thumbnail_description($img, $attachment_id, $post_id = "", $image_class = "" )
	{
			global $avia_config;
		
			$image_size = isset($avia_config['avwc-single-page-size']) ? $avia_config['avwc-single-page-size'] : 'shop_single';
		
			$image_link = wp_get_attachment_url( $attachment_id );

			if(!$image_link) return $img;

			$image = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', $image_size ) );
			$image_title = esc_attr(get_post_field('post_content', $attachment_id));
			$img = sprintf( '<a href="%s" class="%s" title="%s"  rel="prettyPhoto[product-gallery]">%s</a>', $image_link, $image_class, $image_title, $image );

		return $img;
	}
}


if( ! function_exists( 'avia_title_args_woopage' ) )
{
	add_filter( 'avf_title_args', 'avia_title_args_woopage', 10, 2 );
	
	/**
	 * 
	 * @param array $args
	 * @param int $id
	 * @return array
	 */
	function avia_title_args_woopage( $args, $id )
	{
		if( is_single() && is_product() )
		{
			$args['heading'] = "strong";
		}

		return $args;
	}
}


/*
Function that is able to overwrite the default "shop" page used by woocommerce so the template builder can be used
Will only be executed if the user has switched the "shop" page to advanced layout builder. Default products are no longer displayed 
and the user needs to add a product grid element

Can be activated by adding 

add_theme_support( 'avia_custom_shop_page' );

to your functions.php file
*/
if(!function_exists('avia_woocommerce_default_page'))
{
	add_filter( 'pre_get_posts', 'avia_woocommerce_default_page' );
		
	function avia_woocommerce_default_page($query)
	{
	    if(current_theme_supports('avia_custom_shop_page'))
	    {
		    if( isset( $_REQUEST['s'] ) )
			{
				return $query;
			}
		    
		    if(!$query->is_admin && $query->is_main_query() && !$query->is_tax && $query->is_archive && $query->is_post_type_archive)
		    {
		    	$vars = $query->query_vars;
		    
		    	if(isset($vars['post_type']) && 'product' == $vars['post_type'] )
		    	{
		    		$shop_page_id 	= wc_get_page_id( 'shop' );
		    		$builder_active = Avia_Builder()->get_alb_builder_status($shop_page_id);
		    		
		    		if($builder_active == "active")
		    		{
		    			$query->set( 'post_type', 'page' );
		    			$query->set( 'p', $shop_page_id  );
		    			$query->set( 'meta_query', array() );
		    			
		    			$query->is_singular = true;
		    			$query->is_page 	= true;
		    			$query->is_archive  = false;
		    			$query->is_post_type_archive  = false;
		    			$query->query = array('p'=>$shop_page_id, 'post_type' => 'page');
		    		}
		    	}
		    }
		}  
		
	return $query;
		
	}
}



if(!function_exists('avia_woocommerce_disable_editor'))
{
	add_filter( 'avf_builder_button_params', 'avia_woocommerce_disable_editor' );
    
    
    function avia_woocommerce_disable_editor($params)
    {
    	if(!current_theme_supports('avia_custom_shop_page'))
    	{
    		global $post_ID;
    		$shop_page_id = wc_get_page_id( 'shop' );
    		
    		if($post_ID == $shop_page_id)
    		{
    			$disabled = __('(disabled)', 'avia_framework');
    		
    			$params['visual_label'] 	= $params['visual_label']  . " ".$disabled;
    			$params['default_label'] 	= $params['default_label'] . " ".$disabled;
    			$params['button_class'] 	= "av-builer-button-disabled";
    			$params['disabled'] 		= true;
    			$params['note'] 			= __('This page is set as the default WooCommerce Shop Overview and therefore does not support the Enfold advanced layout editor', 'avia_framework')." <br/><a href='https://kriesi.at/documentation/enfold/custom-woocommerce-shop-overview/' target='_blank' rel='noopener noreferrer'>(".__('Learn more').")</a>";
    			
    		}
    	}
    	
    	
    	if(avia_backend_get_post_type() == "product")
    	{
	    	$params['noteclass'] = "av-notice av-only-active";	
	    	$params['note'] = __('Please note that the Advanced Layout Builder for products will not work with all WooCommerce Extensions', 'avia_framework');
    	}
    	
    	
    	
    	
    	return $params;
    }

}

if(!function_exists('avia_woocommerce_disable_editor_option'))
{
	add_filter( 'avf_builder_active', 'avia_woocommerce_disable_editor_option' , 10 , 2);
    
    function avia_woocommerce_disable_editor_option($params, $post_id)
    {
    	if(!current_theme_supports('avia_custom_shop_page'))
    	{
    		if($post_id == wc_get_page_id( 'shop' ))
    		{
    			$params = false;
    		}
    	}
    	
    	return $params;
    }

}






#
# place the cart button according to the header layout (top/sidebar)
#

if(!function_exists('avia_woocommerce_cart_placement'))
{
	add_action( 'init', 'avia_woocommerce_cart_placement', 10);
	
	function avia_woocommerce_cart_placement()
	{
		$position 	= avia_get_option('header_position',  'header_top') == "header_top" ? "ava_main_header" : "ava_inside_main_menu";
		if(avia_get_option('cart_icon') == "always_display_menu") 
		{
			$position = "ava_inside_main_menu";
			if( strpos( avia_get_option('header_layout'), 'bottom_nav_header') !== false && avia_get_option('header_position') == 'header_top')
			{
				$position = "ava_before_bottom_main_menu";
			}
		}
		
		add_action( $position, 'avia_woocommerce_cart_dropdown', 10);
	}
}

	




#
# permanent display of cart button
#

	

if(!function_exists('avia_woocommerce_cart_pos'))
{
	add_filter( 'avf_header_classes', 'avia_woocommerce_cart_pos' , 10 , 3);
    
    function avia_woocommerce_cart_pos($class, $necessary, $prefix)
    {
    	if($prefix == 'html_') // only for the html tag
    	{
	    	global $woocommerce;
	    	$cart = $woocommerce->cart->get_cart();
	    	$cart_pos = avia_get_option('cart_icon');
	    	
	    	if( $cart_pos == "always_display" || ( ! empty( $cart ) ) )
	    	{
				$class[] = "visible_cart";
			}
			
			if($cart_pos == "always_display_menu")
			{
				$class[] = "cart_at_menu";
			}
    	}
    	
    	return $class;
    }

}



function avia_woocommerce_cart_dropdown()
{
	global $woocommerce, $avia_config;
	$cart_subtotal = $woocommerce->cart->get_cart_subtotal();
	$link = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : $woocommerce->cart->get_cart_url();
	$id = "";
	$added = wc_get_notices('success');
	$trigger = !empty($added) ? "av-display-cart-on-load" : "";
	
	if(avia_get_option('cart_icon') == "always_display_menu")
	{
		$id = 'id="menu-item-shop"';
	}	

	$output = "";
	$output .= "<ul {$id} class = 'menu-item cart_dropdown {$trigger}' data-success='".__('was added to the cart', 'avia_framework')."'><li class='cart_dropdown_first'>";
	$output .= "<a class='cart_dropdown_link' href='".$link."'><span ".av_icon_string('cart')."></span><span class='av-cart-counter'>0</span><span class='avia_hidden_link_text'>".__('Shopping Cart','avia_framework')."</span></a><!--<span class='cart_subtotal'>".$cart_subtotal."</span>-->";
	$output .= "<div class='dropdown_widget dropdown_widget_cart'><div class='avia-arrow'></div>";
	$output .= '<div class="widget_shopping_cart_content"></div>';
	$output .= "</div>";
	$output .= "</li></ul>";
	
	
	echo $output;
	
}

/*
	after importing demo pages make sure that if we got multiple shop/my account/etc pages (happens if the user used default woocommerce setup)
	to remove the duplicates and set the theme options properly	
*/

add_action('avia_after_import_hook', 'avia_woocommerce_set_pages');
// add_action('ava_after_main_container', 'avia_woocommerce_set_pages');

function avia_woocommerce_set_pages()
{
	global $wpdb;
	
	$pages = array(
			'shop' => array(
				'title'   => 'Shop',
				'slug'    => 'shop',
			),
			'cart' => array(
				'title'   => 'Cart',
				'slug'    => 'cart',
			),
			'checkout' => array(
				'title'   => 'Checkout',
				'slug'    => 'checkout',
			),
			'myaccount' => array(
				'title'   => 'My Account',
				'slug'    => 'my-account',
			)
		);
	
	/*query string to get multiple posts with the same name*/	
	$pagequery = "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='page'";
	
	
	foreach ($pages as $page)
	{
		$entries = $wpdb->get_results( $wpdb->prepare( $pagequery , $page['title'] )); 
	
		if(!empty($entries))
		{
			$keep	= 0;
			$delete	= array();
			
			//we got one post of that name. the user did not yet activate woocommerce setup or no page with that name was imported
			if(count($entries) === 1)
			{
				$keep = $entries[0]->ID;
			}
			else //we got 2 or more entries. keep the one with the highest id as woocommerce setting and delete the other ones
			{	
				foreach($entries as $entry)
				{
					if($entry->ID > $keep)
					{
						if($keep) $delete[] = $keep;
						$keep = $entry->ID;
					}
					else
					{
						$delete[] = $entry->ID;
					}
				}
			} 
			
			//delete the not required posts
			foreach($delete as $delete_id)
			{
				wp_delete_post( $delete_id, true );
			}
			
			if($keep > 0)
			{
				//store the value of the $keep as the default woo setting
				$setting = str_replace("-", "", $page['slug']);
				update_option('woocommerce_' . $setting . '_page_id' , $keep);
				
				//modify the page slug and remove any numbers if necessary
				$update_post = array(
				      'ID' 			=> $keep,
				      'post_name' 	=> $page['slug']
				  );
				
				wp_update_post( $update_post );
			}
		}
	}		
}

/**
 * Helper functions for template builder elements - Product grids, slideshows, ......
 * ==================================================================================
 * 
 */
if( ! function_exists( 'avia_wc_set_out_of_stock_query_params' ) )
{
	
	/**
	 * Returns the query parameters for the "product out of stock" feature for selecting the products
	 * 
	 * @param array $meta_query
	 * @param array $tax_query
	 * @param string $products_visibility					'show'|'hide'|'' for WC default
	 */
	function avia_wc_set_out_of_stock_query_params( array &$meta_query, array &$tax_query, $products_visibility = '' )
	{
		/**
		 * Backwards compatibility WC < 3.0.0
		 */
		if( ! avia_woocommerce_version_check( '3.0.0') )
		{
			$meta_query[] = WC()->query->visibility_meta_query();
			$meta_query[] = WC()->query->stock_status_meta_query();
			$meta_query   = array_filter( $meta_query );
		}
		else
		{
			switch( $products_visibility )
			{
				case 'show':
					$hide = 'no';
					break;
				case 'hide':
					$hide = 'yes';
					break;
				default:
					$hide = get_option( 'woocommerce_hide_out_of_stock_items', 'no' );
			}

			if( 'yes' == $hide )
			{
				$outofstock_term = get_term_by( 'name', 'outofstock', 'product_visibility' );
				if( $outofstock_term instanceof WP_Term )
				{
					$tax_query[] = array(
									'taxonomy'	=>	'product_visibility',
									'field'		=>	'term_taxonomy_id',
									'terms'		=>	array( $outofstock_term->term_taxonomy_id ),
									'operator'	=>	'NOT IN'
								);
				}
			}
		}
	}
}

if( ! function_exists( 'avia_wc_set_hidden_prod_query_params' ) )
{
	/**
	 * Returns the query parameters for the catalog visibility "hidden" feature for selecting the products.
	 * 
	 * @since 4.1.3
	 * @param array $meta_query
	 * @param array $tax_query
	 * @param string $catalog_visibility					'show'|'hide'|'' for all
	 */
	function avia_wc_set_hidden_prod_query_params( array &$meta_query, array &$tax_query, $catalog_visibility = '' )
	{
		if( avia_woocommerce_version_check( '3.0.0') )
		{
			switch( $catalog_visibility )
			{
				case 'show':
					$operator = 'IN';
					break;
				case 'hide':
					$operator = 'NOT IN';
					break;
				default:
					$operator = '';
			}

			if( in_array( $operator, array( 'IN', 'NOT IN' ) ) )
			{
				$hidden_term = get_term_by( 'name', 'exclude-from-catalog', 'product_visibility' );
				if( $hidden_term instanceof WP_Term )
				{
					$tax_query[] = array(
									'taxonomy'	=>	'product_visibility',
									'field'		=>	'term_taxonomy_id',
									'terms'		=>	array( $hidden_term->term_taxonomy_id ),
									'operator'	=>	$operator
								);
				}
			}
		}
	}
}

if( ! function_exists( 'avia_wc_set_featured_prod_query_params' ) )
{
	/**
	 * Returns the query parameters for the catalog visibility "hidden" feature for selecting the products.
	 * 
	 * @since 4.1.3
	 * @param array $meta_query
	 * @param array $tax_query
	 * @param string $catalog_visibility					'show'|'hide'|'' for all
	 */
	function avia_wc_set_featured_prod_query_params( array &$meta_query, array &$tax_query, $catalog_visibility = '' )
	{
		if( avia_woocommerce_version_check( '3.0.0') )
		{
			switch( $catalog_visibility )
			{
				case 'show':
					$operator = 'IN';
					break;
				case 'hide':
					$operator = 'NOT IN';
					break;
				default:
					$operator = '';
			}

			if( in_array( $operator, array( 'IN', 'NOT IN' ) ) )
			{
				$featured_term = get_term_by( 'name', 'featured', 'product_visibility' );
				if( $featured_term instanceof WP_Term )
				{
					$tax_query[] = array(
									'taxonomy'	=>	'product_visibility',
									'field'		=>	'term_taxonomy_id',
									'terms'		=>	array( $featured_term->term_taxonomy_id ),
									'operator'	=>	$operator
								);
				}
			}
		}
	}
}

if( ! function_exists( 'avia_wc_set_additional_filter_args' ) )
{
	/**
	 * Add additional filters from user selections in widget like
	 *		- minimum / maximum price filter
	 * 
	 * @since 4.5.5
	 * @param array $meta_query
	 * @param array $tax_query
	 */
	function avia_wc_set_additional_filter_args( array &$meta_query, array &$tax_query )
	{
		/**
		 * Filter for Minimum / Maximum Price
		 */
		$args = array();
		if( isset( $_REQUEST['min_price'] ) && is_numeric( $_REQUEST['min_price'] ) )
		{
			$args['min_price'] = $_REQUEST['min_price'];
		}
		if( isset( $_REQUEST['max_price'] ) && is_numeric( $_REQUEST['max_price'] ) )
		{
			$args['max_price'] = $_REQUEST['max_price'];
		}
		
		if( ! empty( $args ) )
		{
			$meta_query[] = wc_get_min_max_price_meta_query( $args );
		}
		
		/**
		 * Additional filters - see woocommerce\includes\class-wc-query.php::get_tax_query()
		 * ==================
		 */
		$product_visibility_terms  = wc_get_product_visibility_term_ids();
		$product_visibility_not_in = array( is_search() && $main_query ? $product_visibility_terms['exclude-from-search'] : $product_visibility_terms['exclude-from-catalog'] );

		/**
		 * Filter for rating
		 */
		if ( isset( $_REQUEST['rating_filter'] ) ) 
		{
			$rating_filter = array_filter( array_map( 'absint', explode( ',', $_REQUEST['rating_filter'] ) ) );
			$rating_terms  = array();
			for ( $i = 1; $i <= 5; $i ++ ) 
			{
				if ( in_array( $i, $rating_filter, true ) && isset( $product_visibility_terms[ 'rated-' . $i ] ) ) 
				{
					$rating_terms[] = $product_visibility_terms[ 'rated-' . $i ];
				}
			}
			if ( ! empty( $rating_terms ) ) 
			{
				$tax_query[] = array(
					'taxonomy'      => 'product_visibility',
					'field'         => 'term_taxonomy_id',
					'terms'         => $rating_terms,
					'operator'      => 'IN',
					'rating_filter' => true,
				);
			}
		}
		
		/**
		 * Filter for additional attribute filters
		 */
		$layered_nav_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
		foreach ( $layered_nav_chosen_attributes as $taxonomy => $data ) 
		{
				$tax_query[] = array(
					'taxonomy'         => $taxonomy,
					'field'            => 'slug',
					'terms'            => $data['terms'],
					'operator'         => 'and' === $data['query_type'] ? 'AND' : 'IN',
					'include_children' => false,
				);
			}
	}
}


if( ! function_exists( 'avia_wc_get_product_query_order_args' ) )
{
	/**
	 * Returns the ordering args, either the default catalog settings or the user selected.
	 * Calls standard WC function to set filter hooks for order by
	 * and removes previously set filter hooks
	 *  
	 * @since < 4.0 
	 * @modified 4.5.6
	 * @param string $order_by
	 * @param string $order
	 * @return array
	 */
	function avia_wc_get_product_query_order_args( $order_by = '', $order = '' )
	{
		$def_orderby = avia_wc_get_default_catalog_order_by();
					
		$order_by = empty( $order_by ) ? $def_orderby['orderby'] : $order_by;
		$order = empty( $order ) ? $def_orderby['order'] : $order;
					
				//	remove and set filter hooks !!
		WC()->query->remove_ordering_args();
		$ordering_args = WC()->query->get_catalog_ordering_args( $order_by, $order );
		
		return $ordering_args;
	}
}


if( ! function_exists( 'avia_wc_get_default_catalog_order_by' ) )
{
	/**
	 * Returns the default settings for catalog order by and clears any set filter hook by this function
	 *
	 * ATTENTION: Though WC sets $order to ASC by default we replace it DESC as e.g. "most recent" retrurns ASC what is wrong (added with 4.5.6.1)
	 * =========
	 *
	 *
	 * @return array
	 */
	function avia_wc_get_default_catalog_order_by()
	{
		//	does not always return correct values !!!
//		$args = WC()->query->get_catalog_ordering_args();
		
		$orderby_value = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

			// Get order + orderby args from string
		$orderby_value = explode( '-', $orderby_value );
		$orderby       = esc_attr( $orderby_value[0] );
		$order         = ! empty( $orderby_value[1] ) ? $orderby_value[1] : 'DESC';
		
		$args    = array();

		$args['orderby']  = strtolower( $orderby );
		$args['order']    = ( 'DESC' === strtoupper( $order ) ) ? 'DESC' : 'ASC';
		$args['meta_key'] = '';		

		return $args;
	}
}


if( ! function_exists( 'avia_wc_clear_catalog_ordering_args_filters' ) )
{
	/**
	 * Remove all filters set by a call to WC()->query->get_catalog_ordering_args();
	 */
	function avia_wc_clear_catalog_ordering_args_filters()
	{
		WC()->query->remove_ordering_args();
	}
}



add_filter( 'woocommerce_product_is_visible', 'avia_wc_product_is_visible', 10, 2 );
if( ! function_exists( 'avia_wc_product_is_visible' ) )
{
	/**
	 * Allows to change the default visibility for products in catalog.
	 * 
	 * WC checks this in the loop when showing products on a catalog page - as we allow user to show/hide products out of stock in various
	 * builder elements we have to force the display even if visibility is false
	 * 
	 * @param boolean $visible
	 * @param int $product_id
	 * @return boolean
	 */
	function avia_wc_product_is_visible( $visible, $product_id )
	{
		global $avia_config;
		
		if( ! isset( $avia_config['woocommerce']['catalog_product_visibility'] ) )
		{
			return $visible;
		}
		
		switch( $avia_config['woocommerce']['catalog_product_visibility'] )
		{
			case 'show_all':
				return true;
			case 'hide_out_of_stock':
				$product = wc_get_product( $product_id );
				if( ! $product instanceof WC_Product )
				{
					return $visible;
				}
				return $product->is_in_stock();
			case 'use_default':
			default:
				return $visible;
		}
	}
}


if( ! function_exists( 'avia_wc_remove_inline_terms' ) )
{
	
	/**
	 * If a template builder page with a fullwidth el is used for terms and conditions the terms and conditions are not displayed properly. we need to filter that.
	 * in case the user uses a template builder page do not display the inline terms. returning an empty string will just show the link to the TOS page
	 */
	add_filter('woocommerce_format_content', 'avia_wc_remove_inline_terms', 10, 2);
	
	function avia_wc_remove_inline_terms( $apply_filters, $raw_string ) 
	{
		if( is_checkout() ) {
			
			$id = wc_get_page_id( 'terms' );
			
			if(get_post_meta($id, '_aviaLayoutBuilder_active', true) == "active")
			{
				return '';
			}
		}
		
		return $apply_filters;
	}
}


add_filter( 'woocommerce_get_settings_checkout' , 'avia_wc_filter_terms_page_selection', 10 , 2);


if( ! function_exists( 'avia_wc_filter_terms_page_selection' ) )
{
	/**
	 * Filter the content description for TOS page selection
	 */
	function avia_wc_filter_terms_page_selection($settings)
	{
		foreach($settings as $key => $setting)
		{
			if( isset( $setting['id'] ) && ( $setting['id'] == "woocommerce_terms_page_id" ) )
			{
				$settings[$key]['desc'] .= "<br><br>".__('Attention! Pages built with the Enfold Advanced Template Builder will not be displayed at the bottom of the checkout page but only with a link.', 'avia_framework');
				break;
			}
		}
		
		return $settings;
	}
}

/**
 * Force WC images in widgets to have Enfold default image size
 * 
 * @since 4.4.2
 * @added_by Günter
 */
add_action( 'woocommerce_widget_product_item_start', 'avia_wc_widget_product_item_start', 10, 1 );
add_filter( 'woocommerce_product_get_image', 'avia_wc_widget_product_image_size', 10, 6 );
add_action( 'woocommerce_widget_product_item_end', 'avia_wc_widget_product_item_end', 10, 1 );

global $avia_wc_product_widget_active;
$avia_wc_product_widget_active = false;

if( ! function_exists( 'avia_wc_widget_product_item_start' ) )
{
	/**
	 * Set a global variable to limit changeing to widget areas only
	 * 
	 * @since 4.4.2
	 * @added_by Günter
	 * @param array $args
	 * @return array
	 */
	function avia_wc_widget_product_item_start( $args )
	{
		global $avia_wc_product_widget_active;

		/**
		 * @since 4.4.2
		 * @return boolean
		 */
		if( false !== apply_filters( 'avf_wc_widget_product_image_size_ignore', false, $args ) )
		{
			return;
		}

		$avia_wc_product_widget_active = true;
	}
}

if( ! function_exists( 'avia_wc_widget_product_image_size' ) )
{
	/**
	 * Modify default WC behaviour.
	 * Based on the function WC_Product::get_image
	 * 
	 * @since 4.4.2
	 * @param string $image
	 * @param WC_Product $product
	 * @param string $size
	 * @param array $attr
	 * @param boolean $placeholder
	 * @param string $image1
	 * @return string
	 */
	function avia_wc_widget_product_image_size( $image, $product, $size, $attr, $placeholder, $image1 )
	{
		global $avia_wc_product_widget_active, $avia_config;

		if( ! $avia_wc_product_widget_active )
		{
			return $image;
		}

		/**
		 * @since 4.4.2
		 * @return string
		 */
		$size = apply_filters( 'avf_wc_widget_product_image_size', 'widget', $product, $size, $attr, $placeholder );

		if ( has_post_thumbnail( $product->get_id() ) ) 
		{
			$image = get_the_post_thumbnail( $product->get_id(), $size, $attr );
		} 
		elseif ( ( $parent_id = wp_get_post_parent_id( $product->get_id() ) ) && has_post_thumbnail( $parent_id ) )  // @phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
		{
			$image = get_the_post_thumbnail( $parent_id, $size, $attr );
		} 
		elseif ( $placeholder ) 
		{
			$image = wc_placeholder_img( $size );
		} 
		else 
		{
			$image = '';
		}
		
		return $image;
	}
}

if( ! function_exists( 'avia_wc_widget_product_item_end' ) )
{
	/**
	 * Reset a global variable to limit changeing to widget areas only
	 * 
	 * @since 4.4.2
	 * @param array $args
	 */
	function avia_wc_widget_product_item_end( $args )
	{
		global $avia_wc_product_widget_active;
		
		$avia_wc_product_widget_active = false;
	}
}


/**
 * Fix problem with ALB pages used as "Terms and Conditions" page on checkout.
 * WC loads page content above the checkbox with js. With ALB this breaks and might also lead to styling problems.
 * Therefore we link to an external page.
 * 
 * Up to WC 3.4.5 no hooks are provided to fix this in php. Therefore we have to add a js snippet.
 * 
 * @since 4.4.2
 * @added_by Günter
 */
if( ! is_admin() && avia_woocommerce_version_check( '3.4.0' ) )
{
	add_action( 'woocommerce_checkout_terms_and_conditions', 'avia_wc_checkout_terms_and_conditions' );
	
	if( ! function_exists( 'avia_wc_checkout_terms_and_conditions' ) )
	{
		function avia_wc_checkout_terms_and_conditions()
		{
			$terms_id = wc_get_page_id('terms');
			if( 'active' == Avia_Builder()->get_alb_builder_status( $terms_id ) )
			{
				add_action( 'wp_footer', 'avia_woocommerce_fix_checkout_term_link' );
			}
		}
	}
	if( ! function_exists( 'avia_woocommerce_fix_checkout_term_link' ) )
	{
		function avia_woocommerce_fix_checkout_term_link()
		{
			$i = 1;
			?>
	<script>
	(function($) {
		// wait until everything completely loaded all assets
		$(window).on('load', function() {
			// remove the click event
			$( document.body ).off( 'click', 'a.woocommerce-terms-and-conditions-link' );
		});
	}(jQuery));
	</script>
	<?php
		}
	}
	
}

if( ! function_exists( 'avia_woocommerce_shortcode_current_post' ) )
{
	
	/**
	 * Shop page might have another query for products and global $post might be a product
	 * 
	 * @since 4.5.6
	 * @param null|WP_Post $current_post
	 * @return null|WP_Post
	 */
	function avia_woocommerce_shortcode_current_post( $current_post )
	{
		if( ! avia_woocommerce_enabled() )
		{
			return $current_post;
		}
		
		if( ! is_shop() )
		{
			return $current_post;
		}
		
		$post = get_post( wc_get_page_id( 'shop' ) );
		
		return $post;
	}
	
	add_filter( 'avf_shortcode_handler_prepare_current_post', 'avia_woocommerce_shortcode_current_post', 10, 1 );
}
