<?php
/**
 * Helper for WooCommerce Product Slider
 *
 * @since ???
 * @since 4.8.9		moved to this file from avia_sc_productslider
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! function_exists( 'WC' ) || ! WC() instanceof WooCommerce )
{
	return;
}

if ( ! class_exists( 'avia_product_slider' ) )
{
	class avia_product_slider extends \aviaBuilder\base\aviaSubItemQueryBase
	{
		use \aviaBuilder\traits\scSlideshowUIControls;

		/**
		 *
		 * @var int
		 */
		static protected $slide = 0;

		/**
		 *
		 * @since 4.7.6.4
		 * @var int
		 */
		protected $current_page;

		/**
		 * Number of queried entires
		 *
		 * @since 5.0
		 * @var int
		 */
		protected $items;

		/**
		 * @since ???
		 * @since 4.8.9					added $sc_context
		 * @param array $atts
		 * @param aviaShortcodeTemplate $sc_context
		 */
		public function __construct( $atts = array(), aviaShortcodeTemplate $sc_context = null )
		{
			parent::__construct( $atts, $sc_context, avia_product_slider::default_args() );

			$this->current_page = 1;
			$this->items = 0;

			if( $this->config['items'] < 0 )
			{
				$this->config['paginate'] = 'no';
			}
		}

		/**
		 * @since 4.5.7.2
		 */
		public function __destruct()
		{
			unset( $this->config );
		}

		/**
		 * Return defaults array
		 *
		 * @since 4.8
		 * @deprecated 4.8.9
		 * @return array
		 */
		static public function get_defaults()
		{
			_deprecated_function( 'avia_product_slider::get_defaults', '4.8.9', 'Use avia_product_slider::default_args instead. Will be removed in a future version.');

			return avia_product_slider::default_args();
		}

		/**
		 * Returns the defaults array.
		 *
		 * ATTENTION: Backwards compatibilty different behaviour !!!
		 * ==========
		 *
		 * Allows shortcodes using this class to get the default values used before,
		 * merge them into shortcode generated defaults
		 *
		 * @since 4.8.9
		 * @param array $sc_defaults
		 * @return array
		 */
		static public function default_args( array $sc_defaults = array() )
		{
			$default = array(
						'type'					=> 'slider',			// 'slider' | 'grid' | 'list'
						'style'					=> '',					// no_margin
						'columns'				=> '4',
						'image_size'			=> '',
						'items'					=> '16',
						'wc_prod_visible'		=>	'',
						'wc_prod_hidden'		=>	'',
						'wc_prod_featured'		=>	'',
						'wc_prod_additional_filter'		=> '',
						'taxonomy'				=> 'product_cat',
						'post_type'				=> 'product',
						'contents'				=> 'excerpt',
						'control_layout'		=> '',
						'slider_navigation'		=> 'av-navigate-arrows av-navigate-dots',
						'nav_visibility_desktop' => '',
						'nav_arrow_color'		=> '',
						'nav_arrow_bg_color'	=> '',
						'nav_dots_color'		=> '',
						'nav_dot_active_color'	=> '',
						'animation'				=> 'fade',
						'transition_speed'		=> '',				//	in ms - empty for default
						'autoplay'				=> 'no',
						'interval'				=> 5,
						'autoplay_stopper'		=> '',
						'manual_stopper'		=> '',
						'bg_slider'				=> 'false',
						'keep_padding'			=> false,			//	needed in js $.AviaSlider
						'hoverpause'			=> true,			//	needed in js $.AviaSlider
						'paginate'				=> 'no',
						'class'					=> '',
						'sort'					=> '',
						'prod_order'			=> '',
						'offset'				=> 0,
						'link_behavior'			=> '',
						'show_images'			=> 'yes',
						'categories'			=> array(),
						'av_display_classes'	=> '',
						'el_id'					=> '',			//	must contain id="...."
						'custom_class'			=> ''
					);

			$default = array_merge( $default, $sc_defaults );

			/**
			 * @since 4.8.9
			 * @param array $default
			 * @return array
			 */
			return apply_filters( 'avf_avia_product_slider_defaults', $default );
		}

		/**
		 * Create custom stylings
		 *
		 * Attention: Due to paging we cannot add any backgrouund images to selectors !!!!
		 * =========
		 *
		 * @since 4.8.9
		 * @param array $result
		 * @return array
		 */
		public function get_element_styles( array $result )
		{
			extract( $result );

			if( 'list' == $this->config['type'] )
			{
				switch( $this->config['columns'] )
				{
					case '1':
						$grid = 'av_fullwidth';
						break;
					case '2':
						$grid = 'av_one_half';
						break;
					case '3':
						$grid = 'av_one_third';
						break;
					case '4':
						$grid = 'av_one_fourth';
						break;
					case '5':
						$grid = 'av_one_fifth';
						break;
					default:
						$grid = 'av_fullwidth';
						break;
				}

				$classes = array(
								'avia-product-slider-container',
								$element_id,
								$grid,
								'flex_column',
								'av-catalogue-column',
								$this->config['custom_class']
							);
			}
			else
			{
				$classes = array(
								'avia-product-slider-container',
								$element_id,
								'template-shop',
								'avia-content-slider',
								"avia-content-{$this->config['type']}-active",
								"shop_columns_{$this->config['columns']}",
								$this->config['class']
							);

				$classes[] = $this->config['columns'] % 2 ? 'avia-content-slider-odd' : 'avia-content-slider-even';
			}

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes( 'container', $this->config['class'] );

			$element_styling->add_responsive_classes( 'container', 'hide_element', $this->config );

			$ui_args = array(
						'element_id'		=> $element_id,
						'element_styling'	=> $element_styling,
						'atts'				=> $this->config,
						'autoplay_option'	=> 'yes',
						'context'			=> __CLASS__,
					);

			$this->addSlideshowAttributes( $ui_args );

			$selectors = array(
						'container'			=> ".avia-product-slider-container.{$element_id}",
						//	override selectors - too weak
						'slide-arrows'		=> "#top .av-slideshow-ui.{$element_id} .avia-slideshow-arrows a",
						'nav-dots'			=> "#top .av-slideshow-ui.{$element_id} .avia-slideshow-dots a:not(.active)",
						'nav-dots-active'	=> "#top .av-slideshow-ui.{$element_id} .avia-slideshow-dots a.active"
					);

			$element_styling->add_selectors( $selectors );

			//	save data for later HTML output
			$this->element_id = $element_id;
			$this->element_styles = $element_styling;

			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Create slider HTML
		 *
		 * @since ????
		 * @return string
		 */
		public function html()
		{
			global $woocommerce_loop;

			extract( $this->config );

			avia_product_slider::$slide ++;
			$post_loop_count = 1;
			$loop_counter = 1;
			$autoplay = $autoplay == 'no' ? false : true;
			$woocommerce_loop['columns'] = $columns;

			//	Add filter to change default WC image size
			add_filter( 'avf_wc_before_shop_loop_item_title_img_size', array( $this, 'handler_wc_image_size_slider' ), 1000, 1 );


			$style_tag = $this->element_styles->get_style_tag( $this->element_id );
			$container_class = $this->element_styles->get_class_string( 'container' );
			$av_display_classes = $this->element_styles->responsive_classes_string( 'hide_element', $this->config );
			$data_slideshow_options = $this->element_styles->get_data_attributes_json_string( 'container', 'slideshow-options' );

			ob_start();

			if( have_posts() )
			{
				echo $style_tag;
				echo "<div {$el_id} class='{$container_class} avia-product-slider" . avia_product_slider::$slide . "' {$data_slideshow_options}>";

				if( $sort == 'dropdown' )
				{
					avia_woocommerce_frontend_search_params();
				}

				echo 	"<div class='avia-content-slider-inner'>";

				if( $type == 'grid' )
				{
					echo '<ul class="products">';
				}

				while( have_posts() )
				{
					the_post();

					if( $loop_counter == 1 && $type == 'slider' )
					{
						echo '<ul class="products slide-entry-wrap">';
					}

					if( function_exists( 'wc_get_template_part' ) )
					{
						wc_get_template_part( 'content', 'product'  );
					}
					else
					{
						woocommerce_get_template_part( 'content', 'product' );
					}

					$loop_counter ++;
					$post_loop_count ++;

					if( $loop_counter > $columns )
					{
						$loop_counter = 1;
					}

					if( $loop_counter == 1 && $type == 'slider' )
					{
						echo '</ul>';
					}

				} // end of the loop.

				if( $loop_counter != 1 || $type == 'grid' )
				{
					echo '</ul>';
				}

				echo 	'</div>';

				if( $post_loop_count -1 > $columns && $type == 'slider' )
				{
					echo $this->slide_navigation_arrows();

					if( 'av-control-hidden' != $this->config['control_layout'] && false !== strpos( $this->config['slider_navigation'], 'av-navigate-dots' )  )
					{
						echo $this->slide_navigation_dots();
					}
				}

				echo '</div>';
			}
			else
			{
				if( function_exists( 'woocommerce_product_subcategories' ) )
				{
					if ( ! woocommerce_product_subcategories( array( 'before' => '<ul class="products">', 'after' => '</ul>' ) ) )
					{
						echo '<p>' . __( 'No products found which match your selection.', 'avia_framework' ) . '</p>';
					}
				}
			}

			echo '<div class="clear"></div>';

			$output = ob_get_clean();

			remove_filter( 'avf_wc_before_shop_loop_item_title_img_size', array( $this, 'handler_wc_image_size_slider' ), 1000 );

			if( $paginate == 'yes' && $avia_pagination = avia_pagination( '', 'nav', 'avia-element-paging', $this->current_page ) )
			{
				$output .= "<div class='pagination-wrap pagination-slider {$av_display_classes}'>{$avia_pagination}</div>";
			}

			/**
			 * @since WC 3.3.0 we have to reset WC loop counter otherwise layout might break
			 */
			if( function_exists( 'wc_reset_loop' ) )
			{
				wc_reset_loop();
			}

			wp_reset_query();

			return $output;
		}

		/**
		 * Create List Style HTML
		 *
		 * @since ????
		 * @return string
		 */
		public function html_list()
		{
			global $wp_query;

			extract( $this->config );

			avia_product_slider::$slide ++;
			$extraClass = 'first';
			$post_loop_count = 0;
			$loop_counter = 0;
			$posts_per_col = ceil( $wp_query->post_count / $columns );

			ob_start();

			$style_tag = $this->element_styles->get_style_tag( $this->element_id );
			$container_class = $this->element_styles->get_class_string( 'container' );
			$av_display_classes = $this->element_styles->responsive_classes_string( 'hide_element', $this->config );

			if( have_posts() )
			{
				while( have_posts() )
				{
					the_post();

					$post_loop_count ++;
					$loop_counter ++;
					if( $loop_counter === 1 )
					{
						echo $style_tag;
						echo "<div {$el_id} class='{$container_class} {$extraClass} avia-product-slider" . avia_product_slider::$slide . "'>";
						echo	'<div class="av-catalogue-container av-catalogue-container-woo">';
						echo		'<ul class="av-catalogue-list">';
						$extraClass = '';
					}

					$_pf = new WC_Product_Factory();
					$product = $_pf->get_product( get_the_ID() );

					if( false === $product )
					{
						continue;
					}

					$ajax_class = 'add_to_cart_button product_type_simple';
					$title = get_the_title();
					$content = strip_tags( get_the_excerpt() );
					$price = $product->get_price_html();
					$rel = '';
					$product_id = method_exists( $product , 'get_id' ) ? $product->get_id() : $product->id;
					$product_type = method_exists( $product , 'get_type' ) ? $product->get_type() : $product->product_type;

					/**
					 * Choose product types that link to single product pages when clicked and not ajax add to cart
					 * (currently only class avia_sc_productlist supports this option)
					 *
					 * @since 4.5.4
					 * @return array
					 */
					$force_product_page_array = apply_filters( 'avf_slider_add_to_cart_via_product_page', array( 'variable' ), $this );

					if( empty( $link_behavior ) || in_array( $product_type, $force_product_page_array ) )
					{
						$cart_url = get_the_permalink();
						$ajax_class = '';
					}
					else
					{
						$cart_url = $product->add_to_cart_url();
						$ajax_class = $product->is_purchasable() ? 'add_to_cart_button ajax_add_to_cart' : '';
						$rel = $product->is_purchasable() ? "rel='nofollow'" : '';
					}

					$image = get_the_post_thumbnail( $product_id, 'square', array( 'class' => "av-catalogue-image av-cart-update-image av-catalogue-image-{$show_images}" ) );

					$text  = $image;
					$text .= '<div class="av-catalogue-item-inner">';
					$text .=	'<div class="av-catalogue-title-container">';
					$text .=		"<div class='av-catalogue-title av-cart-update-title'>{$title}</div>";
					$text .=		"<div class='av-catalogue-price av-cart-update-price'>{$price}</div>";
					$text .=	'</div>';
					$text .=	"<div class='av-catalogue-content'>{$content}</div>";
					$text .= '</div>';

					/**
					 * Allows to call e.g.
					 *		do_action( 'woocommerce_product_thumbnails' );
					 *
					 * @since 4.9
					 * @param array $this->config
					 * @param aviaShortcodeTemplate $this->sc_context
					 */
					do_action( 'avf_product_slider_html_list_before_item', $this->config, $this->sc_context );

					echo '<li>';

					//copied from templates/loop/add-to-cart.php - class and rel attr changed, as well as text

					echo apply_filters( 'woocommerce_loop_add_to_cart_link',
								sprintf( '<a %s href="%s" data-product_id="%s" data-product_sku="%s" class="av-catalogue-item %s product_type_%s product-nr-%d">%s</a>',
									$rel,
									esc_url( $cart_url ),
									esc_attr( $product_id ),
									esc_attr( $product->get_sku() ),
									$ajax_class,
									esc_attr( $product_type ),
									$post_loop_count,
									$text
								),
							$product );

					echo '</li>';

					if( $loop_counter == $posts_per_col || $post_loop_count == $wp_query->post_count )
					{
						echo		'</ul>';
						echo	'</div>';
						echo '</div>';

						$loop_counter = 0;
					}

				} // end of the loop.
			}

			$output = ob_get_clean();

			if( $paginate == 'yes' && $avia_pagination = avia_pagination( '', 'nav', 'avia-element-paging', $this->current_page ) )
			{
				$output .= "<div class='pagination-wrap pagination-slider {$av_display_classes} '>{$avia_pagination}</div>";
			}

			/**
			 * @since WC 3.3.0 we have to reset WC loop counter otherwise layout might break
			 */
			if( function_exists( 'wc_reset_loop' ) )
			{
				wc_reset_loop();
			}

			wp_reset_query();

			return $output;
		}

		/**
		 * Create arrows to scroll slides
		 *
		 * @since 4.8.3			reroute to aviaFrontTemplates
		 * @return string
		 */
		protected function slide_navigation_arrows()
		{
			$args = array(
						'context'		=> get_class(),
						'params'		=> $this->config
					);

			return aviaFrontTemplates::slide_navigation_arrows( $args );
		}

		/**
		 * Create navigation dots
		 *
		 * @since 5.0
		 * @return string
		 */
		protected function slide_navigation_dots()
		{
			$args = array(
						'total_entries'		=> $this->items,
						'container_entries'	=> $this->config['columns'],
						'context'			=> get_class(),
						'params'			=> $this
					);

			return aviaFrontTemplates::slide_navigation_dots( $args );
		}

		/**
		 * Fetch new entries
		 *
		 * @param array $params
		 */
		public function query_entries( $params = array() )
		{
			global $woocommerce, $avia_config;

			$query = array();
			if( empty( $params ) )
			{
				$params = $this->config;
			}

			if( ! empty( $params['categories'] ) )
			{
				//get the product categories
				$terms 	= explode( ',', $params['categories'] );
			}

			$this->current_page = ( $params['paginate'] == 'no' || $params['type'] == 'slider' ) ? 1:  avia_get_current_pagination_number( 'avia-element-paging' );

			//if we find no terms for the taxonomy fetch all taxonomy terms
			if( empty( $terms[0] ) || is_null( $terms[0] ) || $terms[0] === 'null' )
			{
				$term_args = array(
								'taxonomy'		=> $params['taxonomy'],
								'hide_empty'	=> true
							);
				/**
				 * To display private posts you need to set 'hide_empty' to false,
				 * otherwise a category with ONLY private posts will not be returned !!
				 *
				 * You also need to add post_status 'private' to the query params with filter avia_product_slide_query.
				 *
				 * @since 4.4.2
				 * @added_by Günter
				 * @param array $term_args
				 * @param array $params
				 * @return array
				 */
				$term_args = apply_filters( 'avf_av_productslider_term_args', $term_args, $params );

				$allTax = AviaHelper::get_terms( $term_args );

				$terms = array();
				foreach( $allTax as $tax )
				{
					$terms[] = $tax->term_id;
				}
			}

			if( $params['sort'] == 'dropdown' )
			{
				$avia_config['woocommerce']['default_posts_per_page'] = $params['items'];
				$ordering = $woocommerce->query->get_catalog_ordering_args();
				$order = $ordering['order'];
				$orderBY = $ordering['orderby'];

				if( ! empty( $avia_config['shop_overview_products_overwritten'] ) && $params['items'] != -1 )
				{
					$params['items'] = $avia_config['shop_overview_products'];
				}
			}
			else
			{
				$avia_config['woocommerce']['disable_sorting_options'] = true;

				$chk_sort = ( empty( $params['sort'] ) || $params['sort'] == '0' ) ? '' : $params['sort'];
				$ordering = avia_wc_get_product_query_order_args( $chk_sort, $params['prod_order'] );

				$order = $ordering['order'];
				$orderBY = $ordering['orderby'];
			}


            if( $params['offset'] == 'no_duplicates' )
            {
                $params['offset'] = 0;
                $no_duplicates = true;
            }

            if( $params['offset'] == 0 )
			{
				$params['offset'] = false;
			}


			// Meta query - replaced by Tax query in WC 3.0.0
			$meta_query = array();
			$tax_query = array();

			avia_wc_set_out_of_stock_query_params( $meta_query, $tax_query, $params['wc_prod_visible'] );
			avia_wc_set_hidden_prod_query_params( $meta_query, $tax_query, $params['wc_prod_hidden'] );
			avia_wc_set_featured_prod_query_params( $meta_query, $tax_query, $params['wc_prod_featured'] );

			if( 'use_additional_filter' == $params['wc_prod_additional_filter'] )
			{
				avia_wc_set_additional_filter_args( $meta_query, $tax_query );
			}

			$avia_config['woocommerce']['disable_sorting_options'] = true;

			//	sets filter hooks !!
			$ordering_args = avia_wc_get_product_query_order_args( $orderBY, $order );

			if( ! empty( $terms ) )
			{
				$tax_query[] =  array(
									'taxonomy' 	=>	$params['taxonomy'],
									'field' 	=>	'id',
									'terms' 	=>	$terms,
									'operator' 	=>	'IN'
							);
			}

			$query = array(
						'post_type'				=> $params['post_type'],
						'post_status'			=> 'publish',
						'ignore_sticky_posts'	=> 1,
						'paged'					=> $this->current_page,
						'offset'            	=> $params['offset'],
						'post__not_in'			=> ( ! empty( $no_duplicates ) ) ? $avia_config['posts_on_current_page'] : array(),
						'posts_per_page'		=> $params['items'],
						'orderby'				=> $ordering_args['orderby'],
						'order'					=> $ordering_args['order'],
						'meta_query'			=> $meta_query,
						'tax_query'				=> $tax_query
					);


			if ( ! empty( $ordering_args['meta_key'] ) )
			{
	 			$query['meta_key'] = $ordering_args['meta_key'];
	 		}

			/**
			 * @used_by			currently unused
			 *
			 * @since < 4.0
			 * @param array $query
			 * @param array $params
			 * @param array $ordering_args
			 * @return array
			 */
			$query = apply_filters( 'avia_product_slide_query', $query, $params, $ordering_args );

			$result = query_posts( $query );
			$this->items = count( $result );

			// store the queried post ids
            if( have_posts() )
            {
                while( have_posts() )
                {
                    the_post();
                    $avia_config['posts_on_current_page'][] = get_the_ID();
                }
            }

				//	remove all filters
			avia_wc_clear_catalog_ordering_args_filters();
			$avia_config['woocommerce']['disable_sorting_options'] = false;
		}

		/**
		 * Returns the selected image size
		 *
		 * @since 4.8
		 * @param string $size
		 * @return string
		 */
		public function handler_wc_image_size_slider( $size )
		{
			return ! empty( $this->config['image_size'] ) ? $this->config['image_size'] : $size;
		}
	}
}
