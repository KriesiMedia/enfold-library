<?php
/**
 * Helper for masonry 
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_masonry' ) )
{
	class avia_masonry
	{
		/**
		 *
		 * @var int
		 */
		static  $element = 0;
		
		/**
		 *
		 * @var array 
		 */
		protected $atts;
		
		/**
		 * 
		 * @var WP_Query 
		 */
		protected $entries;
		
		/**
		 *
		 * @var array 
		 */
		protected $screen_options;
		
		
		/**
		 *
		 * @since 4.2.4
		 * @var array 
		 */
		protected $loop;

		
		/**
		 * 
		 */
		public function __construct($atts = array())
		{
			self::$element += 1;
			
			$this->entries = null;
			$this->loop = array();
			$this->screen_options = AviaHelper::av_mobile_sizes( $atts );
			
			$this->atts = shortcode_atts(array(	'ids'	=> false,
												'action'=> false,
												'link' 	=> 'category',
												'post_type'=> get_post_types(),
												'items' => 24,
												'size'	=> 'fixed',
												'gap'	=> '1px',
												'overlay_fx' 		=> 'active',
												'offset'			=> 0,
												'container_links'	=> true,
												'container_class'	=> "",
												'paginate'			=> 'paginate',
												'caption_elements' 	=> 'title excerpt',
												'caption_display' 	=> 'always',
												'caption_styling'	=> '',
												'wc_prod_visible'	=> '',
												'prod_order_by'		=> '',
												'prod_order'		=> '',
												'sort'				=> 'no',
												'columns'			=> 'automatic',
												'auto_ratio' 		=> 1.7, //equals a 16:9 ratio
												'set_breadcrumb' 	=> true, //no shortcode option for this, modifies the breadcrumb nav, must be false on taxonomy overview
												'custom_markup'		=> '',
												'query_orderby' 	=> 'date',
		                                 		'query_order' 		=> 'DESC',
		                                 		'color'				=> '',
		                                 		'custom_bg'			=> '',
                                                'custom_class'	    => '',
		                                 		'orientation'		=> '',
		                                 		), $atts, 'av_masonry_entries');
		 	
		 	
		 	if($this->atts['caption_elements'] == 'none')
		 	{
			 	$this->atts['caption_styling'] = "";
		 	}
		 	                                		
		  	$this->atts = apply_filters('avf_masonry_settings', $this->atts, self::$element);
		}
		
		/**
		 * 
		 * @since 4.2.4
		 */
		public function __destruct() 
		{
			unset( $this->atts );
			unset( $this->entries );
			unset( $this->loop );
			unset( $this->screen_options );
		}
		
		
		//ajax function to load additional items
		static function load_more()
		{
			if(check_ajax_referer('av-masonry-nonce', 'avno'));
			
			//increase the post items by one to fetch an additional item. this item is later removed by the javascript but it tells the script if there are more items to load or not
			$_POST['items'] = empty($_POST['items']) ? 1 : $_POST['items'] + 1;
		
			$masonry  	= new avia_masonry($_POST);
			$ajax 		= true;
			
			if(!empty($_POST['ids']))
			{
				$masonry->query_entries_by_id(array(), $ajax);
			}
			else
			{
				$masonry->extract_terms();
				$masonry->query_entries(array(), $ajax);
			}
			
			
			$output = $masonry->html( );
					
			echo '{av-masonry-loaded}'.$output;
			exit();
		}
		
		
		function extract_terms()
		{
			if(isset($this->atts['link']))
			{
				$this->atts['link'] = explode(',', $this->atts['link'], 2 );
				$this->atts['taxonomy'] = $this->atts['link'][0];

				if(isset($this->atts['link'][1]))
				{
					$this->atts['categories'] = $this->atts['link'][1];
				}
				else
				{
					$this->atts['categories'] = array();
				}
			}
		}
		
		function sort_buttons()
		{
			$sort_terms = get_terms( $this->atts['taxonomy'] , array('hide_empty'=>true) );
			
			$current_page_terms	= array();
			$term_count 		= array();
			$display_terms 		= is_array($this->atts['categories']) ? $this->atts['categories'] : array_filter(explode(',',$this->atts['categories']));

			foreach ($this->loop as $entry)
			{
				if($current_item_terms = get_the_terms( $entry['ID'], $this->atts['taxonomy'] ))
				{
					if(!empty($current_item_terms))
					{
						foreach($current_item_terms as $current_item_term)
						{
							if(empty($display_terms) || in_array($current_item_term->term_id, $display_terms))
							{
								$current_page_terms[$current_item_term->term_id] = $current_item_term->term_id;

								if(!isset($term_count[$current_item_term->term_id] ))
								{
									$term_count[$current_item_term->term_id] = 0;
								}

								$term_count[$current_item_term->term_id] ++;
							}
						}
					}
				}
			}
			
			
			$hide 	= count($display_terms) <= 1 ? "hidden" : "";
			$output = "";
			
			if(empty($hide))
			{
				$output  = "<div class='av-masonry-sort main_color av-sort-".$this->atts['sort']."' data-masonry-id='".self::$element."' >";
				//$output .= "<div class='container'>";
				
				$first_item_name = apply_filters('avf_masonry_sort_first_label', __('All','avia_framework' ), $this->atts);
				$first_item_html = '<span class="inner_sort_button"><span>'.$first_item_name.'</span><small class="avia-term-count"> '.count($this->loop).' </small></span>';
				
				$output .= apply_filters('avf_masonry_sort_heading', "", $this->atts);
				
				if(strpos($this->atts['sort'], 'tax') !== false) $output .= "<div class='av-current-sort-title'>{$first_item_html}</div>";
				
				$sort_loop = "";
				$allowed_terms = array();
				
				foreach($sort_terms as $term)
				{
					$show_item = in_array($term->term_id, $current_page_terms) ? 'avia_show_sort' : 'avia_hide_sort';
	                
	                if(!isset($term_count[$term->term_id])) $term_count[$term->term_id] = 0;
					
					$term->slug = str_replace('%', '', $term->slug);
					
					if( empty($display_terms) || in_array($term->term_id, $display_terms))
					{
						$allowed_terms[] = $term->slug.'_sort';
					}
					
					$sort_loop .= 	"<span class='text-sep {$term->slug}_sort_sep {$show_item}'>/</span>";
					$sort_loop .= 	'<a href="#" data-filter="'.$term->slug.'_sort" class="'.$term->slug.'_sort_button '.$show_item.'" ><span class="inner_sort_button">';
					$sort_loop .= 		"<span>".esc_html(trim($term->name))."</span>";
					$sort_loop .= 		"<small class='avia-term-count'> ".$term_count[$term->term_id]." </small></span>";
					$sort_loop .= 	"</a>";
				}
				
				$allowed_terms = json_encode($allowed_terms);
				$output .= "<div class='av-sort-by-term {$hide} ' data-av-allowed-sort='{$allowed_terms}' >";
				$output .= '<a href="#" data-filter="all_sort" class="all_sort_button active_sort">'.$first_item_html.'</a>';
				$output .= $sort_loop;
				$output .= "</div></div>";
			}
			
			return $output;

			
		}
		
		//get the categories for each post and create a string that serves as classes so the javascript can sort by those classes
		function sort_array($the_id)
		{
			$sort_classes 	= array("all_sort");
			$item_terms 	= get_the_terms( $the_id, $this->atts['taxonomy']);

			if(is_object($item_terms) || is_array($item_terms))
			{
				foreach ($item_terms as $term)
				{
					$term->slug = str_replace('%', '', $term->slug);
					$sort_classes[] = $term->slug.'_sort ';
				}
			}

			return $sort_classes;
		}

		
		
		function html()
		{
			if(empty($this->loop)) return;
			
			extract($this->screen_options); //return $av_font_classes, $av_title_font_classes, $av_display_classes and $av_column_classes
			
			$output 	= "";
			$items		= "";
			$size 		= strpos($this->atts['size'], 'fixed') !== false ? 'fixed' : "flex";
			$auto 		= strpos($this->atts['size'], 'masonry') !== false ? true : false;
			$manually	= strpos($this->atts['size'], 'manually') !== false ? true : false;
			$defaults 	= array('ID'=>'', 
								'thumb_ID'=>'', 
								'title' =>'', 
								'url' => '',  
								'class' => array(),  
								'date' => '', 
								'excerpt' => '', 
								'data' => '', 
								'attachment'=> array(), 
								'attachment_overlay' => array(),
								'bg' => "", 
								'before_content'=>'', // if set replaces the whole bg part 
								'text_before'=>'', 
								'text_after'=>'', 
								'img_before'=>'');
			
			
			$style = "";
			
			if( !empty( $this->atts['color'] ) )
			{
				$style .= AviaHelper::style_string( $this->atts, 'custom_bg', 'background-color' );
				$style  = AviaHelper::style_string( $style );
			}
			
			$orientation = $this->atts['size'] == "fixed" ? $this->atts['orientation'] : ""; 
            $custom_class = "";
            if(isset($this->atts['custom_class'])) {
                $custom_class = $this->atts['custom_class'];
            }
			
			$output .= "<div id='av-masonry-".self::$element."' class='av-masonry {$custom_class} noHover av-{$size}-size av-{$this->atts['gap']}-gap av-hover-overlay-{$this->atts['overlay_fx']} av-masonry-col-{$this->atts['columns']} av-caption-{$this->atts['caption_display']} av-caption-style-{$this->atts['caption_styling']} {$this->atts['container_class']} {$orientation} {$av_display_classes} {$av_column_classes}' {$style} >";
			
			$output .= $this->atts['sort'] != "no" ? $this->sort_buttons() : "";
			
			$output .= "<div class='av-masonry-container isotope av-js-disabled ' >";
			$all_sorts  = array();
			$sort_array = array();
			foreach($this->loop as $entry)
			{
				extract(array_merge($defaults, $entry));
				$img_html		= "";
				$img_style		= "";
				if($this->atts['sort'] != "no")
				{
					$sort_array		= $this->sort_array($entry['ID']);
				}
				$class_string 	= implode(' ', $class).' '.implode(' ', $sort_array);
				$all_sorts 		= array_merge($all_sorts, $sort_array);
				
				if(!empty($attachment))
				{
                    $alt = get_post_meta($thumb_ID, '_wp_attachment_image_alt', true);
                    $alt = !empty($alt) ? esc_attr($alt) : '';
                    $title = trim( esc_attr( get_the_title( $thumb_ID ) ) );
					$outer_title = empty( $title ) ? '' : ' title="' . $title . '" ';
					
					if(isset($attachment[0]))
					{
						if($size == 'flex')  
						{
							$img_html  = '<img src="'.$attachment[0].'" title="'.$title.'" alt="'.$alt.'" />';
							$outer_title = '';
						}
						
						if($size == 'fixed') $img_style = 'style="background-image: url('.$attachment[0].');"';
						$class_string .= " av-masonry-item-with-image";
					}
					else 
					{
						$outer_title = '';
					}
					
					if(isset($attachment_overlay[0]))
					{
						$over_html  = '<img src="'.$attachment_overlay[0].'" title="'.$title.'" alt="'.$alt.'" />';
						$over_style = 'style="background-image: url('.$attachment_overlay[0].');"';
						$img_before = '<div class="av-masonry-image-container av-masonry-overlay" '.$over_style.'>'.$over_html.'</div>';
					}
					
					$bg = '<div class="av-masonry-outerimage-container">'.$img_before.'<div class="av-masonry-image-container" ' . $img_style . $outer_title . '>' . $img_html . '</div></div>';
				}
				else
				{
					$class_string .= " av-masonry-item-no-image";
				}
				
				
				if($size == 'fixed')
				{
					if(!empty($attachment) || !empty($before_content))
					{
						if($auto)
							$class_string .= $this->ratio_check_by_image_size($attachment);
							
						if($manually)
							$class_string .= $this->ratio_check_by_tag($entry['tags']);	
					}
				}
				
				$linktitle = "";
				
                if($post_type == 'attachment' && strpos($html_tags[0], 'a href=') !== false)
                {
                    $linktitle = 'title="'.esc_attr($description).'"';
                }
                else if(strpos($html_tags[0], 'a href=') !== false)
                {
                    $linktitle = 'title="'.esc_attr($the_title).'"';
                }
                $markup = ($post_type == 'attachment') ? avia_markup_helper(array('context' => 'image_url','echo'=>false, 'id'=>$entry['ID'], 'custom_markup'=>$this->atts['custom_markup'])) : avia_markup_helper(array('context' => 'entry','echo'=>false, 'id'=>$entry['ID'], 'custom_markup'=>$this->atts['custom_markup']));

				$items .= 	"<{$html_tags[0]} id='av-masonry-".self::$element."-item-".$entry['ID']."' data-av-masonry-item='".$entry['ID']."' class='{$class_string}' {$linktitle} {$markup}>";
				$items .= 		"<div class='av-inner-masonry-sizer'></div>"; //responsible for the size
				$items .=		"<figure class='av-inner-masonry main_color'>";
				$items .= 			$bg;
				
				//title and excerpt
				if($this->atts['caption_elements'] != 'none' || !empty($text_add))
				{
					$items .=	"<figcaption class='av-inner-masonry-content site-background'><div class='av-inner-masonry-content-pos'><div class='av-inner-masonry-content-pos-content'><div class='avia-arrow'></div>".$text_before;
					
					if(strpos($this->atts['caption_elements'], 'title') !== false){
                        $markup = avia_markup_helper(array('context' => 'entry_title','echo'=>false, 'id'=>$entry['ID'], 'custom_markup'=>$this->atts['custom_markup']));
						$items .=	"<h3 class='av-masonry-entry-title entry-title' {$markup}>{$the_title}</h3>";
					}

					if(strpos($this->atts['caption_elements'], 'excerpt') !== false && !empty($content)){
                        $markup = avia_markup_helper(array('context' => 'entry_content','echo'=>false, 'id'=>$entry['ID'], 'custom_markup'=>$this->atts['custom_markup']));
						$items .=	"<div class='av-masonry-entry-content entry-content' {$markup}>{$content}</div>";
					}
					$items .=	$text_after."</div></div></figcaption>";
				}
				$items .= 		"</figure>";
				$items .= 	"</{$html_tags[1]}><!--end av-masonry entry-->";					
			}
			
			//if its an ajax call return the items only without container
			if(isset($this->atts['action']) && $this->atts['action'] == 'avia_ajax_masonry_more')
			{
				
				return $items;
			}
			
			// if its no ajax load prepend an empty invisible element as the first element. this is used for calculating the correct width of a default element.
			// in theory this is not necessary because the masonry can detect that with an extra js parameter but sorting becomes slugish if that param is set
			
			$all_sort_string = implode(' ', array_unique($all_sorts));
			$items = "<div class='av-masonry-entry isotope-item av-masonry-item-no-image {$all_sort_string}'></div>".$items;
			
			$output .= $items;
			$output .= 	"</div>";
			
			
			//append pagination
			if($this->atts['paginate'] == "pagination" && $avia_pagination = avia_pagination($this->entries->max_num_pages, 'nav')) 
			{
				$output .= "<div class='av-masonry-pagination av-masonry-pagination-{$this->atts['paginate']}'>{$avia_pagination}</div>";
			}
			else if($this->atts['paginate'] == "load_more" && $this->entries->max_num_pages > 1 )
			{
				$output .= $this->load_more_button();
			}
			
			$output .= "</div>";
			
			return $output;
		}
		
				
		function load_more_button()
		{
			$data_string  = AviaHelper::create_data_string($this->atts);
			$data_string .= " data-avno='".wp_create_nonce( 'av-masonry-nonce' )."'";
			$output  = "";
			$output .= 		"<a class='av-masonry-pagination av-masonry-load-more' href='#load-more' {$data_string}>".__('Load more','avia_framework')."</a>";
			
			return $output;
		}	
		
		function ratio_check_by_image_size($attachment)
		{
			$img_size = ' av-grid-img';
			
			if(!empty($attachment[1]) && !empty($attachment[2]))
			{
				if($attachment[1] > $attachment[2]) //landscape
				{
					//only consider it landscape if its 1.7 times wider than high
					if($attachment[1] / $attachment[2] > $this->atts['auto_ratio']) $img_size = ' av-landscape-img';
				}
				else //same check with portrait
				{
					if($attachment[2] / $attachment[1] > $this->atts['auto_ratio']) $img_size = ' av-portrait-img';
				}
			}
			
			return $img_size;
		}
		
		function ratio_check_by_tag($tags)
		{
			$img_size = '';
			
			if(is_array($tags))
			{	
				$tag_values = apply_filters('avf_ratio_check_by_tag_values', array('portrait' => 'portrait', 'landscape' => 'landscape'));

				if(in_array($tag_values['portrait'], $tags)) { $img_size .= ' av-portrait-img'; }
				if(in_array($tag_values['landscape'], $tags)){ $img_size .= ' av-landscape-img'; }
			}
			
			if(empty($img_size))  $img_size = ' av-grid-img';
			
			return $img_size;
			
		}
		
		
		function prepare_loop_from_entries( $ajax = false )
		{
			$this->loop = array();
			if(empty($this->entries) || empty($this->entries->posts)) return;
			$tagTax = "post_tag"; 
			$date_format = get_option('date_format');
			
			
			foreach($this->entries->posts as $key => $entry)
			{ 	
				$overlay_img = $custom_url			= false;
				$img_size	 						= 'masonry';
				$author = apply_filters('avf_author_name', get_the_author_meta('display_name', $entry->post_author), $entry->post_author);
                		
				$this->loop[$key]['text_before']	= "";
				$this->loop[$key]['text_after']		= "";
				$this->loop[$key]['ID'] = $id		= $entry->ID;
				$this->loop[$key]['post_type'] 		= $entry->post_type;
				$this->loop[$key]['thumb_ID'] 		= get_post_thumbnail_id($id);
				$this->loop[$key]['the_title'] 		= get_the_title($id);
				$this->loop[$key]['url']			= get_permalink($id);
				$this->loop[$key]['date'] 			= "<span class='av-masonry-date meta-color updated'>".get_the_time($date_format, $id)."</span>";
				$this->loop[$key]['author'] 		= "<span class='av-masonry-author meta-color vcard author'><span class='fn'>". __('by','avia_framework') .' '. $author."</span></span>";
				$this->loop[$key]['class'] 			= get_post_class("av-masonry-entry isotope-item", $id); 
				$this->loop[$key]['content']		= strip_tags( $entry->post_excerpt );
                $this->loop[$key]['description']	= !empty($entry->post_content) ? $entry->post_content : $entry->post_excerpt;
				
				if(empty($this->loop[$key]['content']))
				{
					if($ajax)
					{
						$entry->post_content = preg_replace("!\[.*?\]!", "", $entry->post_content);
					}
					
					$this->loop[$key]['content'] 	= avia_backend_truncate($entry->post_content, apply_filters( 'avf_masonry_excerpt_length' , 60) , apply_filters( 'avf_masonry_excerpt_delimiter' , " "), "…", true, '');
				}
				
				$this->loop[$key]['content'] = nl2br( trim($this->loop[$key]['content']) );
				
				//post type specific
				switch($entry->post_type)
				{
					case 'post': 
					
					$post_format 		= get_post_format($id) ? get_post_format($id) : 'standard';
					$this->loop[$key]	= apply_filters( 'post-format-'.$post_format, $this->loop[$key] );
					$this->loop[$key]['text_after'] .= $this->loop[$key]['date'];
					$this->loop[$key]['text_after'] .= '<span class="av-masonry-text-sep text-sep-author">/</span>';
					$this->loop[$key]['text_after'] .= $this->loop[$key]['author'];
					
						switch($post_format)
						{
							case 'quote' :
							case 'link' :
							case 'image' :
							case 'gallery' :
								if(!$this->loop[$key]['thumb_ID']) 
								{
									$this->loop[$key]['text_before'] = av_icon_display($post_format);
								}
							break;
							
							case 'audio' :
							case 'video' :
								if(!$this->loop[$key]['thumb_ID']) 
								{
									$this->loop[$key]['text_before'] = av_icon_display($post_format);
								}
								else
								{
									$this->loop[$key]['text_before'] = av_icon_display($post_format, 'av-masonry-media');
								}
							break;
						}
					
					
					
					break;
					
					case 'portfolio':
					
					//set portfolio breadcrumb navigation
					if($this->atts['set_breadcrumb'] && is_page()) $_SESSION["avia_{$entry->post_type}"] = get_the_ID();
					
					//check if the user has set up a custom link
					if(!post_password_required($id)){
						$custom_link = get_post_meta( $id ,'_portfolio_custom_link', true) != "" ? get_post_meta( $id ,'_portfolio_custom_link_url', true) : false;
						if($custom_link) $this->loop[$key]['url'] = $custom_link;
					}
					break;
					
					
					case 'attachment':
					
					$custom_url = get_post_meta( $id, 'av-custom-link', true );
					$this->loop[$key]['thumb_ID'] = $id;
					$this->loop[$key]['content']		= $entry->post_excerpt;
					
					if($custom_url)
					{
						$this->loop[$key]['url'] = $custom_url;
					}
					else
					{
						$this->loop[$key]['url'] = wp_get_attachment_image_src($id, apply_filters('avf_avia_builder_masonry_lightbox_img_size','large'));
						$this->loop[$key]['url'] = reset($this->loop[$key]['url']);
					}

					
					break; 
					
					case 'product':
					//check if woocommerce is enabled in the first place so we can use woocommerce functions
					if(function_exists('avia_woocommerce_enabled') && avia_woocommerce_enabled())
					{
						$tagTax 		= "product_tag"; 
						$product 		= function_exists('wc_get_product') ? wc_get_product($id) : get_product( $id );
						$overlay_img 	= avia_woocommerce_gallery_first_thumbnail($id, $img_size, true);

						$this->loop[$key]['text_after'] .= '<span class="av-masonry-price price">'.$product->get_price_html()."</span>";
						if($product->is_on_sale( )) $this->loop[$key]['text_after'] .= '<span class="onsale">'.__( 'Sale!', 'avia_framework' ).'</span>';
					}
					break; 
				}
				
				
				//check if post is password protected
				if(post_password_required($id))
				{
					$this->loop[$key]['content'] 		= "";
					$this->loop[$key]['class'][]		= "entry-protected";
					$this->loop[$key]['thumb_ID'] 		= "";
					$this->loop[$key]['text_before'] 	= av_icon_display('closed');
					$this->loop[$key]['text_after']		= $this->loop[$key]['date'];
				}
				
				
				
				//set the html tags. depending on the link settings use either an a tag or a div tag
				if(!empty($this->atts['container_links']) || !empty($custom_url))
				{
					$this->loop[$key]['html_tags'] = array('a href="'.$this->loop[$key]['url'].'"','a'); //opening and closing tag for the masonry container
				}
				else
				{
					$this->loop[$key]['html_tags'] = array('div','div');
				}
				
				
				//get post tags
				$this->loop[$key]['tags']		= wp_get_post_terms($id, $tagTax, array( 'fields' => 'slugs' ));
				
				//check if the image got landscape as well as portrait class applied. in that case use a bigger image size
				if(strlen($this->ratio_check_by_tag($this->loop[$key]['tags'])) > 20) $img_size = 'extra_large';
				
				//get attachment data
				$this->loop[$key]['attachment'] = !empty($this->loop[$key]['thumb_ID']) ? wp_get_attachment_image_src($this->loop[$key]['thumb_ID'], $img_size) : "";
				
				//get overlay attachment in case the overlay is set
				$this->loop[$key]['attachment_overlay'] = !empty($overlay_img) ? wp_get_attachment_image_src($overlay_img, $img_size) : "";
				
				//apply filter for other post types, in case we want to use them and display additional/different information
				$this->loop[$key] = apply_filters('avf_masonry_loop_prepare', $this->loop[$key], $this->entries);
			}
		}
		
		
		//fetch new entries
		public function query_entries($params = array(), $ajax = false)
		{
			
			global $avia_config;

			if(empty($params)) $params = $this->atts;

			if(empty($params['custom_query']))
            {
				$query = array();
				$avialable_terms = array();
				
				if(!empty($params['categories']))
				{
					//get the portfolio categories
					$terms 	= explode(',', $params['categories']);
				}

				$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
				if(!$page || $params['paginate'] == 'no') $page = 1;

				
				//if we find no terms for the taxonomy fetch all taxonomy terms
				if(empty($terms[0]) || is_null($terms[0]) || $terms[0] === "null")
				{
					$terms = array();
					$allTax = get_terms( $params['taxonomy'] );
					foreach($allTax as $tax)
					{
						if( is_object($tax) )
						{
							$terms[] = $tax->term_id;
						}
					}
				}
				
				
				
				if(!empty($params['taxonomy']))
				{
					$allTax = get_terms( $params['taxonomy'] );
					foreach($allTax as $tax)
					{
						if( is_object($tax) )
						{
							$avialable_terms[] = $tax->term_id;
						}
					}
				}
				
				
				//check if any of the terms passed are valid. if not all existing terms are used
				$valid_terms = array();
				foreach($terms as $term)
				{
					if(in_array($term, $avialable_terms))
					{
						$valid_terms[] = $term;
					}
				}
				
				if(!empty($valid_terms))
				{
					$terms = $valid_terms;
					$this->atts['categories'] = implode(",", $terms);
				}
				else
				{
					$terms = $avialable_terms;
					$this->atts['categories'] = implode(",", $terms);
				}
				
				if(empty($params['post_type'])) $params['post_type'] = get_post_types();
				if(is_string($params['post_type'])) $params['post_type'] = explode(',', $params['post_type']);


				//wordpress 4.4 offset fix. only necessary for ajax loading, therefore we ignore the page param
				if( $params['offset'] == 0 )
				{
					$params['offset'] = false;
				}
					 
			
				
				// Meta query - replaced by Tax query in WC 3.0.0
				$meta_query = array();
				$tax_query = array();

				// check if taxonomy are set to product or product attributes
				$tax = get_taxonomy( $params['taxonomy'] );
				
				if( class_exists( 'WooCommerce' ) && is_object( $tax ) && isset( $tax->object_type ) && in_array( 'product', (array) $tax->object_type ) )
				{
					$avia_config['woocommerce']['disable_sorting_options'] = true;
					
					avia_wc_set_out_of_stock_query_params( $meta_query, $tax_query, $params['wc_prod_visible'] );
					
						//	sets filter hooks !!
					$ordering_args = avia_wc_get_product_query_order_args( $params['prod_order_by'], $params['prod_order'] );
							
					$params['query_orderby'] = $ordering_args['orderby'];
					$params['query_order'] = $ordering_args['order'];
				}

				if( ! empty( $terms ) )
				{
					$tax_query[] =  array(
										'taxonomy' 	=>	$params['taxonomy'],
										'field' 	=>	'id',
										'terms' 	=>	$terms,
										'operator' 	=>	'IN'
								);
				}

				$query = array(	'orderby'		=>	$params['query_orderby'],
								'order'			=>	$params['query_order'],
								'paged'			=>	$page,
								'post_type'		=>	$params['post_type'],
								'post_status'	=>	'publish',
								'offset'		=>	$params['offset'],
								'posts_per_page' =>	$params['items'],
								'meta_query'	=>	$meta_query,
								'tax_query'		=>	$tax_query
							);



				if($params['query_orderby'] == 'rand' && isset($_POST['loaded']))
				{
					$query['post__not_in'] = $_POST['loaded'];
					$query['offset'] = false;
				}											
					
			}
			else
			{
				$query = $params['custom_query'];
			}


			/**
			 * @used_by			avia_remove_bbpress_post_type_from_query		10		(bbPress)
			 *					avia_translate_ids_from_query					10		(WPML)
			 *					avia_events_modify_recurring_event_query		10		(Tribe Events Pro)
			 */
			$query = apply_filters('avia_masonry_entries_query', $query, $params);

			$this->entries = new WP_Query( $query );
			
			/**
			 * @used_by			avia_events_modify_recurring_event_query		10		(Tribe Events Pro)
			 * 
			 * @added_by Günter
			 * @since 4.2.4
			 */
			do_action( 'ava_after_masonry_entries_query' );
			
			$this->prepare_loop_from_entries( $ajax );
			
			if( function_exists( 'WC' ) )
			{
				avia_wc_clear_catalog_ordering_args_filters();
				$avia_config['woocommerce']['disable_sorting_options'] = false;
			}

		}
		
		
		public function query_entries_by_id($params = array(), $ajax = false)
		{
			global $avia_config;

			if(empty($params)) $params = $this->atts;
			
			$ids = is_array($this->atts['ids']) ? $this->atts['ids'] : array_filter(explode(',',$this->atts['ids']));
			
			$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
			if(!$page) $page = 1;
			
			if( $params['offset'] == 0 )
			{
				$params['offset'] = false;
			}
			
			$query = array(
				'post__in' => $ids,
				'post_status' => 'inherit',
				'post_type' => 'attachment',
				'post_mime_type' => 'image',
				'paged' 	=> $page,
				'order' => 'ASC',
				'offset'	=> $params['offset'],
				'posts_per_page' => $params['items'],
				'orderby' => 'post__in'
			);
			
			/**
			 * @used_by			avia_remove_bbpress_post_type_from_query		10		(bbPress)
			 *					avia_translate_ids_from_query					10		(WPML)
			 *					avia_events_modify_recurring_event_query		10		(Tribe Events Pro)
			 */
			$query = apply_filters('avia_masonry_entries_query', $query, $params);

			$this->entries = new WP_Query( $query );
			
			/**
			 * @used_by			avia_events_modify_recurring_event_query		10		(Tribe Events Pro)
			 * 
			 * @added_by Günter
			 * @since 4.2.4
			 */
			do_action( 'ava_after_masonry_entries_query' );
			
			$this->prepare_loop_from_entries( $ajax );
			
			
		}
	}
}


