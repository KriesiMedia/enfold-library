<?php
/**
 * Blog Posts
 *
 * Displays Posts from your Blog
 *
 * This class does not support post css files
 * ==========================================
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if ( ! class_exists( 'avia_sc_blog', false ) )
{
	class avia_sc_blog extends aviaShortcodeTemplate
	{
		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'yes';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Blog Posts', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-blog.png';
			$this->config['order']			= 40;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_blog';
			$this->config['tooltip']		= __( 'Displays Posts from your Blog', 'avia_framework' );
			$this->config['preview']		= false;
			$this->config['disabling_allowed'] = 'manually';
			$this->config['disabled']		= array(
												'condition'	=> ( avia_get_option( 'disable_blog' ) == 'disable_blog' ),
												'text'		=> __( 'This element is disabled in your theme options. You can enable it in Enfold &raquo; Performance', 'avia_framework' )
											);
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}

		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-blog', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/blog/blog{$min_css}.css", array( 'avia-layout' ), $ver );
			wp_enqueue_style( 'avia-module-postslider', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/postslider/postslider{$min_css}.css", array( 'avia-layout' ), $ver );
		}


		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @return void
		 */
		protected function popup_elements()
		{

			//if the element is disabled
			if( true === $this->config['disabled']['condition'] )
			{
				$this->elements = array(

					array(
								'type'			=> 'template',
								'template_id'	=> 'element_disabled',
								'args'			=> array(
														'desc'	=> $this->config['disabled']['text']
													)
							),
						);

				return;
			}


			$this->elements = array(

				array(
						'type' 	=> 'tab_container',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),
						array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'content_blog' ),
													$this->popup_key( 'content_filter' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Styling', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'styling_appearance' ),
													$this->popup_key( 'styling_pagination' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type' 	=> 'toggle_container',
							'nodescription' => true
						),

						array(
								'type'			=> 'template',
								'template_id'	=> 'lazy_loading_toggle',
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'screen_options_toggle',
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'developer_options_toggle',
								'args'			=> array( 'sc' => $this )
							),

					array(
							'type' 	=> 'toggle_container_close',
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array(
												'sc'	=> $this
											)
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)

				);

		}

		/**
		 * Create and register templates for easier maintainance
		 *
		 * @since 4.6.4
		 */
		protected function register_dynamic_templates()
		{

			/**
			 * Content Tab
			 * ===========
			 */

			$desc  = __( 'Should the full entry be displayed or just a small excerpt?', 'avia_framework' ) . '<br /><br />';
			$desc .= sprintf( __( 'Pages/Posts/.. built with ALB will only display &quot;Excerpt With Read More Link&quot; to avoid a possible breaking of layout. Use %s this filter %s to change it.', 'avia_framework' ), '<a href="https://github.com/KriesiMedia/enfold-library/blob/master/actions%20and%20filters/ALB%20Elements/Blog%20Posts/avf_blog_content_in_loop.php" target="_blank" rel="noopener noreferrer">', '</a>' );

			$c = array(

						array(
							'name' 		=> __( 'Content To Display', 'avia_framework' ),
							'desc' 		=> __( 'Do you want to display blog posts or entries from a custom taxonomy?', 'avia_framework' ),
							'id' 		=> 'blog_type',
							'type' 		=> 'select',
							'std'		=> 'posts',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Display blog posts', 'avia_framework' )						=> 'posts',
												__( 'Display entries from a custom taxonomy', 'avia_framework' )	=> 'taxonomy')
											),

						array(
							'name' 		=> __( 'Which Categories Should Be Used For The Blog', 'avia_framework' ),
							'desc' 		=> __( 'You can select multiple categories here. The page will then show posts from only those categories', 'avia_framework' ),
							'id' 		=> 'categories',
							'type' 		=> 'select',
							'multiple'	=> 6,
							'lockable'	=> true,
							'required' 	=> array( 'blog_type', 'equals', 'posts' ),
							'subtype' 	=> 'cat'
						),

						array(
							'name'		=> __( 'Which Entries Should Be Used', 'avia_framework' ),
							'desc'		=> __( 'Select which entries should be displayed by selecting a taxonomy', 'avia_framework' ),
							'id'		=> 'link',
							'type'		=> 'linkpicker',
							'multiple'	=> 6,
							'std'		=> 'category',
							'fetchTMPL'	=> true,
							'lockable'	=> true,
							'required'	=> array( 'blog_type', 'equals', 'taxonomy' ),
							'subtype'	=> array( __( 'Display Entries from:', 'avia_framework' ) => 'taxonomy' )
						),

						array(
							'name'		=> __( 'Multiple Categories/Terms Relation', 'avia_framework' ),
							'desc'		=> __( 'Select to use an OR or AND relation. In AND an entry must be in all selected categories/terms to be displayed. Defaults to OR', 'avia_framework' ),
							'id'		=> 'term_rel',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'OR', 'avia_framework' )	=> '',
												__( 'AND', 'avia_framework' )	=> 'AND'
											)
						),

						array(
							'name'		=> __( 'Blog Style', 'avia_framework' ),
							'desc'		=> __( 'Choose the default blog layout here', 'avia_framework' ),
							'id'		=> 'blog_style',
							'type'		=> 'select',
							'std'		=> 'single-big',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Multi Author Blog (displays Gravatar of the article author beside the entry and feature images above)', 'avia_framework')	=> 'multi-big',
												__( 'Single Author, small preview Pic (no author picture is displayed, feature image is small)', 'avia_framework')				=> 'single-small',
												__( 'Single Author, big preview Pic (no author picture is displayed, feature image is big)', 'avia_framework')					=> 'single-big',
												__( 'Grid Layout', 'avia_framework')														=> 'blog-grid',
												__( 'List Layout - Simple (Title and meta information only)', 'avia_framework' )			=> 'bloglist-simple',
												__( 'List Layout - Compact (Title and icon only)', 'avia_framework' )						=> 'bloglist-compact',
												__( 'List Layout - Excerpt (Title, meta information and excerpt only)', 'avia_framework' )	=> 'bloglist-excerpt',
//												__( 'No Sidebar', 'avia_framework' )	=> 'fullsize'
											)
						),

						array(
							'name' 	=> __( 'Blog Content Length', 'avia_framework' ),
							'desc' 	=> $desc,
							'id' 	=> 'content_length',
							'type' 	=> 'select',
							'std' 	=> 'content',
							'lockable'	=> true,
							'required'	=> array( 'blog_style', 'doesnt_contain', 'blog' ),
							'subtype'	=> array(
												__( 'Full Content', 'avia_framework' )					=> 'content',
												__( 'Excerpt', 'avia_framework' )						=> 'excerpt',
												__( 'Excerpt With Read More Link', 'avia_framework' )	=> 'excerpt_read_more'
											)
						),

						array(
							'name' 	=> __( 'Define Blog Grid Layout', 'avia_framework' ),
							'desc' 	=> __( 'Do you want to display a read more link?', 'avia_framework' ),
							'id' 	=> 'contents',
							'type' 	=> 'select',
							'std' 	=> 'excerpt',
							'lockable'	=> true,
							'required'	=> array( 'blog_style', 'equals', 'blog-grid' ),
							'subtype'	=> array(
												__( 'Title and Excerpt', 'avia_framework' )						=> 'excerpt',
												__( 'Title and Excerpt + Read More Link', 'avia_framework' )	=> 'excerpt_read_more',
												__( 'Only Title', 'avia_framework' )							=> 'title',
												__( 'Only Title + Read More Link', 'avia_framework' )			=> 'title_read_more',
												__( 'Only excerpt', 'avia_framework' )							=> 'only_excerpt',
												__( 'Only excerpt + Read More Link', 'avia_framework' )			=> 'only_excerpt_read_more',
												__( 'No Title and no excerpt', 'avia_framework' )				=> 'no'
											)
                            )

				);

			if( current_theme_supports( 'add_avia_builder_post_type_option' ) )
			{
				$element = array(
								'type'			=> 'template',
								'template_id'	=> 'avia_builder_post_type_option',
								'lockable'		=> true,
								'required'		=> array( 'blog_type', 'equals', 'taxonomy' ),
							);

				array_splice( $c, 2, 0, array( $element ) );
			}

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Select Entries', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_blog' ), $template );

			$c = array(
						array(
							'type'			=> 'template',
							'template_id' 	=> 'date_query',
							'lockable'		=> true,
							'period'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id' 	=> 'page_element_filter',
							'lockable'		=> true
						),

						array(
							'name'		=> __( 'Offset Number', 'avia_framework' ),
							'desc'		=> __( 'The offset determines where the query begins pulling posts. Useful if you want to remove a certain number of posts because you already query them with another blog or magazine element.', 'avia_framework' ),
							'id'		=> 'offset',
							'type'		=> 'select',
							'std'		=> '0',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( __( 'Deactivate offset', 'avia_framework') => '0', __( 'Do not allow duplicate posts on the entire page (set offset automatically)', 'avia_framework' ) => 'no_duplicates' ) )
						),

						array(
							'name'		=> __( 'Conditional Display', 'avia_framework' ),
							'desc'		=> __( 'When should the element be displayed?', 'avia_framework' ),
							'id'		=> 'conditional',
							'type'		=> 'select',
							'std'	=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Always display the element', 'avia_framework' )	=> '',
												__( 'Remove element if the user navigated away from page 1 to page 2,3,4 etc ', 'avia_framework' ) => 'is_subpage'
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Filter', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_filter' ), $template );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name' 	=> __( 'Blog List Width', 'avia_framework' ),
							'desc' 	=> __( 'Define the width of the list', 'avia_framework' ),
							'id' 	=> 'bloglist_width',
							'type' 	=> 'select',
							'std' 	=> '',
							'lockable'	=> true,
							'required'	=> array( 'blog_style', 'contains', 'bloglist' ),
							'subtype'	=> array(
												__( 'Auto', 'avia_framework' )				=> '',
												__( 'Force Fullwidth', 'avia_framework' )	=> 'force_fullwidth'
											)
						),

						array(
							'name' 	=> __( 'Blog Grid Columns', 'avia_framework' ),
							'desc' 	=> __( 'How many columns do you want to display?', 'avia_framework' ),
							'id' 	=> 'columns',
							'type' 	=> 'select',
							'std' 	=> '3',
							'lockable'	=> true,
							'required'	=> array( 'blog_style', 'equals', 'blog-grid' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 5, 1 )
						),

						array(
							'name' 	=> __( 'Preview Image Size', 'avia_framework' ),
							'desc' 	=> __( 'Set the image size of the preview images', 'avia_framework' ),
							'id' 	=> 'preview_mode',
							'type' 	=> 'select',
							'std' 	=> 'auto',
							'lockable'	=> true,
							'required'	=> array( 'blog_style', 'doesnt_contain', 'bloglist' ),
							'subtype'	=> array(
												__( 'Set the preview image size automatically based on column or layout width', 'avia_framework' )	=> 'auto',
												__( 'Choose the preview image size manually (select thumbnail size)', 'avia_framework' )			=> 'custom'
											)
						),

						array(
							'name' 	=> __( 'Select custom preview image size', 'avia_framework' ),
							'desc' 	=> __( 'Choose image size for Preview Image', 'avia_framework' ),
							'id' 	=> 'image_size',
							'type' 	=> 'select',
							'std' 	=> 'portfolio',
							'lockable'	=> true,
							'required'	=> array( 'preview_mode', 'equals', 'custom' ),
							'subtype'	=> AviaHelper::get_registered_image_sizes( array( 'logo' ) )
						),
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Appearance', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_appearance' ), $template );


			$c = array(
						array(
							'name' 	=> __( 'Post Number', 'avia_framework' ),
							'desc' 	=> __( 'How many items should be displayed per page?', 'avia_framework' ),
							'id' 	=> 'items',
							'type' 	=> 'select',
							'std' 	=> '3',
							'lockable'	=> true,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( 'All' => '-1' ) )
						),

						array(
							'name' 	=> __( 'Pagination', 'avia_framework' ),
							'desc' 	=> __( 'Should a pagination be displayed? Pagination might not work as expected when there is more than one blog posts element on a page, a post or on the blog page.', 'avia_framework' ),
							'id' 	=> 'paginate',
							'type' 	=> 'select',
							'std' 	=> 'yes',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'yes', 'avia_framework' )	=> 'yes',
												__( 'no', 'avia_framework' )	=> 'no'
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Pagination', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_pagination' ), $template );

		}

		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 * @param array $params			holds the default values for $content and $args.
		 * @return array				usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_element( $params )
		{
			$params = parent::editor_element( $params );
			$params['content'] = null; //remove to allow content elements

			return $params;
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			global $avia_config, $more;

			$default = array(
						'blog_style'		=> '',
						'bloglist_width'	=> '',
						'columns'			=> 3,
						'blog_type'			=> 'posts',
						'items'				=> '16',
						'paginate'			=> 'yes',
						'categories'		=> '',
						'preview_mode'		=> 'auto',
						'image_size'		=> 'portfolio',
						'taxonomy'			=> 'category',
						'post_type'			=> get_post_types(),
						'term_rel'			=> 'IN',
						'contents'			=> 'excerpt',
						'content_length'	=> 'content',
						'offset'			=> '0',
						'conditional'		=> '',
						'date_filter'		=> '',
						'date_filter_start'	=> '',
						'date_filter_end'	=> '',
						'date_filter_format'	=> 'mm / dd / yy',
						'period_filter_unit_1'	=> '',
						'period_filter_unit_2'	=> '',
						'page_element_filter'	=> '',
						'lazy_loading'		=> 'disabled',
						'img_scrset'		=> ''
				);

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$screen_sizes = AviaHelper::av_mobile_sizes( $atts );	//return $av_font_classes, $av_title_font_classes and $av_display_classes
			extract( $screen_sizes );

			if( isset( $atts['img_scrset'] ) && 'disabled' == $atts['img_scrset'] )
			{
				Av_Responsive_Images()->force_disable( 'disabled' );
			}

			if( empty( $atts['categories'] ) )
			{
				$atts['categories'] = '';
			}

			if( isset( $atts['link'] ) && isset( $atts['blog_type'] ) && $atts['blog_type'] == 'taxonomy' )
			{
				$atts['link'] = explode( ',', $atts['link'], 2 );
				$atts['taxonomy'] = $atts['link'][0];

				if( ! empty( $atts['link'][1] ) )
				{
					$atts['categories'] = $atts['link'][1];
				}
				else if( ! empty( $atts['taxonomy'] ) )
				{
					$term_args = array(
									'taxonomy'		=> $atts['taxonomy'],
									'hide_empty'	=> true
								);
					/**
					 * To display private posts you need to set 'hide_empty' to false,
					 * otherwise a category with ONLY private posts will not be returned !!
					 *
					 * You also need to add post_status 'private' to the query params of filter avia_post_slide_query.
					 *
					 * @since 4.4.2
					 * @added_by Günter
					 * @param array $term_args
					 * @param array $atts
					 * @param boolean $ajax
					 * @return array
					 */
					$term_args = apply_filters( 'avf_av_blog_term_args', $term_args, $atts, $content );

					$taxonomy_terms_obj = AviaHelper::get_terms( $term_args );

					foreach( $taxonomy_terms_obj as $taxonomy_term )
					{
						$atts['categories'] .= $taxonomy_term->term_id . ',';
					}
				}
			}

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			
			if( $atts['term_rel'] != 'AND' )
			{
				$atts['term_rel'] = 'IN';
			}

			$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
			if( ! $page )
			{
				$page = 1;
			}

			/**
			 * Skip blog queries, if element will not be displayed
			 */
			if( $atts['conditional'] == 'is_subpage' && $page != 1 )
			{
				return '';
			}

			if( $atts['blog_style'] == 'blog-grid' )
			{
				$atts['el_id'] = $meta['custom_el_id'];
				$atts['class'] = $meta['el_class'];
				$atts['type']  = 'grid';
				$atts = array_merge( $atts, $screen_sizes );

				/**
				 * @since 4.5.5
				 * @return array
				 */
				$atts = apply_filters( 'avf_post_slider_args', $atts, $this->config['shortcode'], $this );

				//using the post slider with inactive js will result in displaying a nice post grid
				$slider = new avia_post_slider( $atts );

				$old_page = null;
				$is_single = is_single();

				if( 'yes' == $atts['paginate'] )
				{
					if( $is_single && isset( $_REQUEST['av_sc_blog_page'] ) && is_numeric( $_REQUEST['av_sc_blog_page'] ) )
					{
						$old_page = get_query_var( 'paged' );
						set_query_var( 'paged', $_REQUEST['av_sc_blog_page'] );
					}
				}

				$slider->query_entries();

				if( 'yes' == $atts['paginate'] && $is_single )
				{
					add_filter( 'avf_pagination_link_method', array( $this, 'handler_pagination_link_method'), 10, 3 );
				}

				$html = $slider->html();

				if( 'yes' == $atts['paginate'] && $is_single )
				{
					remove_filter( 'avf_pagination_link_method', array( $this, 'handler_pagination_link_method'), 10 );
				}

				if( ! is_null( $old_page ) )
				{
					if( $old_page != 0 )
					{
						set_query_var( 'paged', $old_page );
					}
					else
					{
						remove_query_arg( 'paged' );
					}
				}

				Av_Responsive_Images()->force_disable( 'reset' );

				return $html;
			}

			$old_page = null;
			$is_single = is_single();

			if( 'yes' == $atts['paginate'] )
			{
				if( $is_single && isset( $_REQUEST['av_sc_blog_page'] ) && is_numeric( $_REQUEST['av_sc_blog_page'] ) )
				{
					$old_page = get_query_var( 'paged' );
					set_query_var( 'paged', $_REQUEST['av_sc_blog_page'] );
				}
			}

			$this->query_entries( $atts );

			if( 'yes' == $atts['paginate'] && $is_single )
			{
				add_filter( 'avf_pagination_link_method', array( $this, 'handler_pagination_link_method' ), 10, 3 );
			}

			$avia_config['blog_style'] = $atts['blog_style'];
			$avia_config['preview_mode'] = $atts['preview_mode'];
			$avia_config['image_size'] = $atts['image_size'];
			$avia_config['blog_content'] = $atts['content_length'];
			$avia_config['remove_pagination'] = $atts['paginate'] === 'yes' ? false : true;
			$avia_config['alb_html_lazy_loading'] = $atts['lazy_loading'];

			/**
			 * Force supress of pagination if element will be hidden on foillowing pages
			 */
			if( $atts['conditional'] == 'is_subpage' && $page == 1 )
			{
				$avia_config['remove_pagination'] = true;
			}

			$more = 0;

			ob_start(); //start buffering the output instead of echoing it
			get_template_part( 'includes/loop', 'index' );
			$output = ob_get_clean();

			unset( $avia_config['alb_html_lazy_loading'] );
			wp_reset_query();

			if( 'yes' == $atts['paginate'] && $is_single )
			{
				remove_filter( 'avf_pagination_link_method', array( $this, 'handler_pagination_link_method'), 10 );
			}

			if( ! is_null( $old_page ) )
			{
				if( $old_page != 0 )
				{
					set_query_var( 'paged', $old_page );
				}
				else
				{
					remove_query_arg( 'paged' );
				}
			}

			avia_set_layout_array();

			if( $output )
			{
				$extraclass = function_exists( 'avia_blog_class_string' ) ? avia_blog_class_string() : '';
				$extraclass .= $atts['bloglist_width'] == 'force_fullwidth' ? ' av_force_fullwidth' : '';
				$extraclass .= ! empty( $meta['custom_class'] ) ? ' ' . $meta['custom_class'] : '';
				$markup = avia_markup_helper( array( 'context' => 'blog', 'echo' => false, 'custom_markup' => $meta['custom_markup'] ) );

				$output = "<div {$meta['custom_el_id']} class='av-alb-blogposts template-blog {$extraclass} {$av_display_classes}' {$markup}>{$output}</div>";
			}

			$html = Av_Responsive_Images()->make_content_images_responsive( $output );

			Av_Responsive_Images()->force_disable( 'reset' );

			return $html;
		}

		/**
		 *
		 * @since < 4.0
		 * @param array $params
		 */
		protected function query_entries( array $params )
		{
			global $avia_config;

			$query = array();

			if( ! empty( $params['categories'] ) && is_string( $params['categories'] ) )
			{
				//get the categories
				$terms 	= explode( ',', $params['categories'] );
			}

			$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
			if( ! $page || $params['paginate'] == 'no' )
			{
				$page = 1;
			}

			if( $params['offset'] == 'no_duplicates' )
			{
				$params['offset'] = 0;
				$no_duplicates = true;
			}

			if( empty( $params['blog_type'] ) || $params['blog_type'] == 'posts' )
			{
				$params['post_type'] = 'post';
			}
			if( empty( $params['post_type'] ) )
			{
				$params['post_type'] = get_post_types();
			}
			if( is_string( $params['post_type'] ) )
			{
				$params['post_type'] = explode( ',', $params['post_type'] );
			}

			//wordpress 4.4 offset fix
			if( $params['offset'] == 0 )
			{
				$params['offset'] = false;
			}
			else
			{
				//if the offset is set the paged param is ignored. therefore we need to factor in the page number
				$params['offset'] = $params['offset'] + ( ( $page -1 ) * $params['items'] );
			}

			$date_query = AviaHelper::date_query( array(), $params );

			//if we find categories perform complex query, otherwise simple one
			if( isset( $terms[0] ) && ! empty( $terms[0] ) && ! is_null( $terms[0] ) && $terms[0] != 'null' && ! empty( $params['taxonomy'] ) )
			{
				$query = array(
							'paged'			=> $page,
							'posts_per_page' => $params['items'],
							'offset'		=> $params['offset'],
							'post__not_in'	=> ( ! empty( $no_duplicates ) ) ? $avia_config['posts_on_current_page'] : array(),
							'post_type'		=> $params['post_type'],
							'date_query'	=> $date_query,
							'tax_query'		=> array(
												array(
														'taxonomy' 	=> $params['taxonomy'],
														'field' 	=> 'id',
														'terms' 	=> $terms,
														'operator' 	=> count( $terms ) == 1 ? 'IN' : $params['term_rel']
													)
												)
							);
			}
			else
			{
				$query = array(
							'paged'				=> $page,
							'posts_per_page'	=> $params['items'],
							'offset'			=> $params['offset'],
							'post__not_in'		=> ( ! empty( $no_duplicates ) ) ? $avia_config['posts_on_current_page'] : array(),
							'post_type'			=> $params['post_type'],
							'date_query'		=> $date_query
							);
			}

			if( 'skip_current' == $params['page_element_filter'] )
			{
				$query['post__not_in'] = isset( $query['post__not_in'] ) ? $query['post__not_in'] : [];
				$query['post__not_in'][] = get_the_ID();
			}

			/**
			 *
			 * @since < 4.0
			 * @param array $query
			 * @param array $params
			 * @return array
			 */
			$query = apply_filters( 'avia_blog_post_query', $query, $params );

			$results = query_posts( $query );

			// store the queried post ids in
			if( have_posts() )
			{
				while( have_posts() )
				{
					the_post();
					$avia_config['posts_on_current_page'][] = get_the_ID();
				}
			}
		}

		/**
		 * Using this element not in a page ( = is_single() ) returns a wrong pagination
		 *
		 * @since 4.5.6
		 * @param string $method
		 * @param type $pages
		 * @param type $wrapper
		 * @return string
		 */
		public function handler_pagination_link_method( $method, $pages, $wrapper )
		{
			if( is_single() || ( 'get_pagenum_link' == $method ) )
			{
				$method = 'avia_sc_blog::add_blog_pageing';
			}

			return $method;
		}

		/**
		 * Called when this element not in a page ( = is_single() ).
		 * Add our custom page parameter.
		 *
		 * @since 4.5.6
		 * @param int $page
		 * @return string
		 */
		static public function add_blog_pageing( $page )
		{
			$link = get_pagenum_link( 1 );

			if( $page != 1 )
			{
				$link = add_query_arg( array( 'av_sc_blog_page' => $page ), $link );
			}
			else
			{
				$link = remove_query_arg( 'av_sc_blog_page', $link );
			}

			return $link;
		}

	}

}
