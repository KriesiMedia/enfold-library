<?php
/**
 * The 'Element Templates' class handles a hierarchical structure of styling templates to provide an easy way for a consistent layout of elements.
 * Derived templates inherit settings from parents and can override values of parent template(s) if not locked.
 *
 * As templates is an often used term (in WP and Enfold Page Templates) UI will be using "Custom Elements"
 *
 * @author		Günter
 * @since		4.8
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'aviaElementTemplates' ) )
{
	class aviaElementTemplates
	{
		const POST_TYPE = 'alb_elements';
		const TAXONOMY = 'alb_elements_entries';

		/**
		 * Holds the instance of this class
		 *
		 * @since 4.8
		 * @var aviaElementTemplates
		 */
		static private $_instance = null;

		/**
		 * Flag if this feature has been enabled
		 *
		 * @since 4.8
		 * @var boolean|null
		 */
		protected $enabled;

		/**
		 * Filterable post type
		 *
		 * @since 4.8
		 * @var string
		 */
		protected $el_post_type;

		/**
		 * Filterable taxonomy
		 *
		 * @since 4.8
		 * @var string
		 */
		protected $el_taxonomy;

		/**
		 * For performance reason only
		 *
		 * @since 4.8
		 * @var boolean|null
		 */
		protected $is_edit_element_page;

		/**
		 * For performance reason only
		 *
		 * @since 4.8
		 * @var boolean|null
		 */
		protected $is_element_overview_page;

		/**
		 * Cache queried templates
		 *	[post_id]	=> array(
		 *						'shortcode'		=> string
		 *						'attr'			=> array (  key => value  )
		 *					)
		 * @since 4.8
		 * @var array
		 */
		protected $template_cache;

		/**
		 * Temp array used on update post to avoid double building of shortcode array
		 *		- [0]['template_id']
		 *
		 * @since 4.8
		 * @var array
		 */
		protected $cached_update_sc_array;

		/**
		 * Save the filter value - set in "posts_where" filter hook
		 *
		 * @since 4.8
		 * @var string
		 */
		protected $filter_category;

		/**
		 * Backend editor buttons info
		 *
		 * @since 4.8
		 * @var array
		 */
		protected $editor_elements;

		/**
		 * Message to inform user about expired nonce
		 *
		 * @since 4.8
		 * @var string
		 */
		protected $nonce_message;

		/**
		 * Message to inform user cannot perform requested action
		 *
		 * @since 4.8
		 * @var string
		 */
		protected $no_capability;

		/**
		 * Tab name for condensed tab
		 *
		 * @since 4.8
		 * @var string|null
		 */
		protected $custom_element_tab_name;

		/**
		 * Store filtered option value how to handle custom element for modal group subitems
		 *
		 * @since 4.8
		 * @var string			'first' | 'individually' | 'none'
		 */
		protected $subitem_custom_element_handling;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.8
		 * @return aviaElementTemplates
		 */
		static public function instance()
		{
			if( is_null( aviaElementTemplates::$_instance ) )
			{
				aviaElementTemplates::$_instance = new aviaElementTemplates();
			}

			return aviaElementTemplates::$_instance;
		}

		/**
		 *
		 * @since 4.8
		 */
		protected function __construct()
		{
			$this->enabled = null;
			$this->el_post_type = null;
			$this->el_taxonomy = null;
			$this->is_edit_element_page = null;
			$this->is_element_overview_page = null;
			$this->template_cache = array();
			$this->cached_update_sc_array = array();
			$this->filter_category = 'all';
			$this->editor_elements = array();

			$this->nonce_message = __( 'Session has expired. Your requested action could not be executed. Please try again or reload the page.', 'avia_framework' );
			$this->no_capability = __( 'Sorry, you have not enough rights to perform requested action.', 'avia_framework' );

			/**
			 * @since 4.8
			 */
			$this->custom_element_tab_name = apply_filters( 'avf_custom_element_tab_name', __( 'Custom Elements', 'avia_framework' ) );

			$this->subitem_custom_element_handling = null;

			$this->register_post_types();

			$this->activate_filters();
		}


		/**
		 * @since 4.8
		 */
		public function __destruct()
		{
			unset( $this->template_cache );
			unset( $this->cached_update_sc_array );
			unset( $this->editor_elements );
		}

		/**
		 * Returns the filtered post type for ALB Element Templates
		 *
		 * @since 4.8
		 * @return string
		 */
		public function get_post_type()
		{
			if( is_null( $this->el_post_type ) )
			{
				/**
				 * @since 4.8
				 * @param string
				 * @return string
				 */
				$this->el_post_type = apply_filters( 'avf_custom_element_post_type', aviaElementTemplates::POST_TYPE );
			}

			return $this->el_post_type;
		}

		/**
		 * Returns the filtered taxonomy for ALB Element Templates
		 *
		 * @since 4.8
		 * @return string
		 */
		public function get_taxonomy()
		{
			if( is_null( $this->el_taxonomy ) )
			{
				/**
				 * @since 4.8
				 * @param string
				 * @return string
				 */
				$this->el_taxonomy = apply_filters( 'avf_custom_element_taxonomy', aviaElementTemplates::TAXONOMY );
			}

			return $this->el_taxonomy;
		}


		/**
		 * Attach to filters
		 *
		 * @since 4.8
		 */
		protected function activate_filters()
		{
//			add_action( 'init', array( $this, 'handler_wp_register_post_types' ), 1 );		//	hook after cpt portfolio

			//	must hook here to avoid endless loop when saving post
			add_filter( 'wp_insert_post_parent', array( $this, 'handler_wp_insert_post_parent' ), 1000, 4 );

			add_filter( 'admin_body_class', array( $this, 'handler_admin_body_class' ) );

			add_filter( 'page_row_actions', array( $this, 'handler_page_row_actions' ), 10, 2 );
			add_filter( 'display_post_states', array( $this, 'handler_display_post_states' ), 150, 2 );

			add_filter( 'manage_edit-alb_elements_columns', array( $this, 'handler_edit_alb_elements_columns'), 10 );
			add_action( 'manage_pages_custom_column', array( $this, 'handler_pages_custom_column'), 10, 2 );
			add_action( 'manage_posts_custom_column', array( $this, 'handler_pages_custom_column'), 10, 2 );

			//	add additional dropdown filter boxes - see \wp-admin\includes\class-wp-posts-list-table.php
			add_action( 'restrict_manage_posts', array( $this, 'handler_wp_restrict_manage_posts' ), 10, 2 );

			//	modify the query
			add_filter( 'posts_where' , array( $this, 'handler_wp_posts_where' ), 10, 2 );
			add_filter( 'posts_join', array( $this, 'handler_wp_posts_join' ), 10, 2 );

			add_action( 'add_meta_boxes', array( $this, 'handler_add_meta_boxes' ), 1000 );
			add_action( 'admin_bar_menu', array( $this, 'handler_admin_bar_menu' ), 100 );

			add_action( 'ava_menu_page_added', array( $this, 'handler_ava_menu_page_added' ), 10, 4 );
			add_filter( 'avf_builder_button_params', array( $this, 'handler_avf_builder_button_params' ), 10, 1 );

			add_action( 'post_submitbox_start', array( $this, 'handler_wp_post_submitbox_start' ), 10 );

			add_action( 'admin_action_duplicate_element_template', array( $this, 'handler_admin_action_duplicate_element_template' ), 10 );

			//	ajax callbacks
			add_action( 'wp_ajax_avia_alb_element_check_title', array( $this, 'handler_ajax_element_check_title' ) );
			add_action( 'wp_ajax_avia_alb_element_template_cpt_actions', array( $this, 'handler_ajax_element_template_cpt_actions' ) );
			add_action( 'wp_ajax_avia_alb_element_template_update_content', array( $this, 'handler_ajax_element_template_update_content' ) );
			add_action( 'wp_ajax_avia_alb_element_template_delete', array( $this, 'handler_ajax_element_template_delete' ) );
		}

		/**
		 * Check post content for parent element id
		 *
		 * @since 4.8
		 * @param int $post_parent
		 * @param int $post_ID
		 * @param array $new_postarr
		 * @param array $postarr
		 */
		public function handler_wp_insert_post_parent( $post_parent, $post_ID, $new_postarr, $postarr )
		{
			if( 0 == $post_ID || $postarr['post_type'] != $this->get_post_type() )
			{
				return $post_parent;
			}

			$shortcode = trim( $postarr['post_content'] );

			if( empty( $shortcode ) )
			{
				return $post_parent;
			}

			$this->cached_update_sc_array = $this->get_element_template_info_from_content( $shortcode );

			return $this->cached_update_sc_array[0]['template_id'];
		}

		/**
		 * Add extra classes
		 *
		 * @since 4.8
		 * @param string $classes
		 * @return string
		 */
		public function handler_admin_body_class( $classes )
		{
			if( ! $this->element_templates_enabled() )
			{
				$classes .= ' avia-custom-elements-disabled';
				return $classes;
			}

			$classes .= ' avia-custom-elements-enabled';

			$modal = avia_get_option( 'alb_locked_modal_options', '' );

			switch( $modal )
			{
				case '':
					$classes .= ' avia-modal-hide-locked-input-fields avia-modal-hide-element-show-locked-options';
					break;
				case 'hide_non_admin':
					if( ! current_user_can( 'manage_options' ) )
					{
						$classes .= ' avia-modal-hide-locked-input-fields avia-modal-hide-element-show-locked-options';
					}
					break;
			}

			$handling = $this->subitem_custom_element_handling();

			switch( $handling )
			{
				case 'none':
					$classes .= ' avia-subitem-element-handling avia-subitem-no-element avia-subitem-one-element';
					break;
				case 'individually':
					$classes .= ' avia-subitem-element-handling avia-subitem-individual-element';
					break;
				case 'first':
				default:
					$classes .= ' avia-subitem-element-handling avia-subitem-one-element';
					break;
			}

			$hierarchical = avia_get_option( 'custom_el_hierarchical_templates' );

			switch( $hierarchical )
			{
				case 'hierarchical':
					$classes .= ' avia-custom-elements-hierarchical';
					break;
				case '':
				default:
					$classes .= ' avia-custom-elements-non-hierarchical';
					break;
			}

			global $post;

			if( ! $post instanceof WP_Post || $post->post_type != $this->get_post_type() )
			{
				return $classes;
			}

			$terms = get_the_terms( $post->ID, $this->get_taxonomy() );

			if( false === $terms )
			{
				$classes .= ' avia-no-terms';
			}
			else if( is_array( $terms ) )
			{
				foreach( $terms as $term )
				{
					$classes .= ' avia-term-' . $term->slug;
				}
			}

			if( $this->allow_cpt_screens( 'body_class' ) )
			{
				$classes .= ' avia-allow-cpt-screen';
			}

			return $classes;
		}

		/**
		 * Add a message to backend
		 *
		 * @since 4.8
		 * @param array $params
		 * @return array
		 */
		public function handler_avf_builder_button_params( array $params )
		{
			if( ! $this->element_templates_enabled() )
			{
				return $params;
			}

			if( ! $this->is_edit_element_page() )
			{
				return $params;
			}

			$params['noteclass'] = 'av-notice-element-templates-cache';

			if( $this->allow_cpt_screens( 'builder_button_params' ) )
			{
				$params['note']  = '<strong>' . __( 'Attention: It is not recommended to edit custom element templates here !', 'avia_framework' ) . '</strong><br />';
				$params['note'] .= __( 'Not all features provided are supported here and this might break layout. Please edit your elements using the &quot;Edit Element&quot; button from an advanced layout builder page.', 'avia_framework' );
			}
			else
			{
				$params['note']  = '<strong>' . __( 'Editing a custom element template is not allowed from this screen.', 'avia_framework' ) . '</strong><br />';
				$params['note'] .= __( 'Please edit your elements using the &quot;Edit Element&quot; button from an advanced layout builder page.', 'avia_framework' );
			}

			return $params;
		}


		/**
		 * Add ALB Elements as submenu to Theme Options Page
		 *
		 * @since 4.8
		 * @param string $top_level
		 * @param avia_adminpages $this
		 * @param string $the_title
		 * @param string $menu_title
		 */
		public function handler_ava_menu_page_added( $top_level, avia_adminpages $adminpages, $the_title, $menu_title )
		{
			if( ! $this->element_templates_enabled() )
			{
				return;
			}

			if( ! $this->allow_cpt_screens() )
			{
				return;
			}

			$obj = get_post_type_object( $this->get_post_type() );

			if( ! $obj instanceof WP_Post_Type )
			{
				return;
			}

			/**
			 * Possible WP Bug (WP 5.5.1 - Enfold 4.8)
			 *
			 * Main menu has capability 'manage_options'.
			 * If user has less capabilty than the added menus from here are shown but user cannot access the page because WP rechecks capability of main menu.
			 *
			 * In this case we have to add our own main menu with less cap.
			 */
			$cap_new = $this->get_capability( 'new' );
			$cap_edit = $this->get_capability( 'edit' );

			if( ! current_user_can( 'manage_options' ) && ( 'manage_options' != $cap_new || 'manage_options' != $cap_edit ) )
			{
				$top_level = 'edit.php?post_type=' . $this->get_post_type();
				$cap = 'manage_options' != $cap_new ? $cap_new : $cap_edit;

				add_menu_page(
							$the_title . __( ' Elements', 'avia_framework' ),	// page title
							$menu_title . __( ' Elements', 'avia_framework' ),	// menu title
							$cap,												// capability
							$top_level,											// menu slug (and later also database options key)
							'',													// executing function
							"dashicons-admin-home",
							26
						);
			}

			add_submenu_page(
						$top_level,										//$parent_slug
						$obj->label,									//$page_title
						$obj->label,									//$menu_title
						$this->get_capability( 'new' ),					//$capability
						'edit.php?post_type=' . $this->get_post_type()
				);

			add_submenu_page(
						$top_level,											//$parent_slug
						$obj->label,										//$page_title
						$obj->labels->new_item,								//$menu_title
						$this->get_capability( 'edit' ),					//$capability
						'post-new.php?post_type=' . $this->get_post_type()
				);

		}

		/**
		 * Add a link to "New" in admin bar
		 *
		 * @since 4.8
		 * @param WP_Admin_Bar $wp_admin_bar
		 * @return WP_Admin_Bar
		 */
		public function handler_admin_bar_menu( WP_Admin_Bar $wp_admin_bar )
		{
			if( ! $this->element_templates_enabled() )
			{
				return;
			}

			$allow_cpt_screen_opt = current_theme_supports( 'avia-custom-elements-cpt-screen' ) ? avia_get_option( 'custom_el_cpt_screen' ) : '';

			/**
			 * @since 4.8
			 * @param boolean
			 * @param string $context				'menu_page' | 'admin_bar_new
			 * @return boolean
			 */
			$allow_cpt_screen = apply_filters( 'avf_custom_elements_cpt_screen', 'allow_cpt_screen' == $allow_cpt_screen_opt, 'admin_bar_new' );

			if( ! $allow_cpt_screen )
			{
				return;
			}

			$obj = get_post_type_object( $this->get_post_type() );

			if( ! $obj instanceof WP_Post_Type )
			{
				return;
			}

			if( ! current_user_can( $this->get_capability( 'new' ) ) )
			{
				return;
			}

			$args = array(
						'id'		=> 'new-z' . $this->get_post_type(),
						'title'		=> $obj->labels->singular_name,
						'href'		=> 'post-new.php?post_type=' . $this->get_post_type(),
						'parent'	=> 'new-content'
					);

			$wp_admin_bar->add_node( $args );

			return $wp_admin_bar;
		}

		/**
		 * Remove slug metabox
		 *
		 * @since 4.8
		 */
		public function handler_add_meta_boxes()
		{
			if( $this->is_edit_element_page() )
			{
				remove_meta_box( 'slugdiv', $this->get_post_type(), 'normal' );
			}
		}

		/**
		 * Add a duplicate element link to WP actions meta box
		 *
		 * @since 4.8
		 */
		public function handler_wp_post_submitbox_start()
		{
			global $post;

			if( ! $this->is_edit_element_page() || ! current_user_can( $this->get_capability( 'new' ) ) )
			{
				return;
			}

			$shortcode = Avia_Builder()->get_posts_alb_content( $post->ID );

			if( empty( $shortcode ) )
			{
				return;
			}

			/**
			 * Currently copying to another shortcode is not supported (makes only sense for columns)
			 *
			 * Might be added in a future release if requested
			 */
			$sc_array = $this->get_element_template_info_from_content( $shortcode );
//			$sc = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $sc_array[0]['shortcode'] ] ];
//			$duplicates = isset( $sc->config['duplicate_template'] ) ? $sc->config['duplicate_template'] : array( $sc_array[0]['shortcode'] => '' );

			$duplicates = array( $sc_array[0]['shortcode'] => '' );

			$notify_url = wp_nonce_url( admin_url( 'edit.php?post_type=' . $this->get_post_type() . '&action=duplicate_element_template&post=' . absint( $post->ID ) ), 'avia-duplicate-element-template_' . $post->ID );
			$notify_text = __( 'Copy to a new draft', 'avia_framework' );
			$html = '';

			foreach( $duplicates as $shortcode => $name )
			{
				$url = $notify_url;
				$text = $notify_text;

				if( ! empty( $name ) )
				{
					$url = add_query_arg( 'new_shortcode', $shortcode, $url );
					$text .= ' ' . $name;
				}

				$html .= '<div id="duplicate-action">';
				$html .=	'<a class="submitduplicate duplication" href="' . esc_url( $url ) . '">';
				$html .=		esc_html( $text );
				$html .=	'</a>';
				$html .= '</div>';
			}

			echo $html;
		}


		/**
		 * - Remove not wanted links in overview table
		 * - Add a link to duplicate
		 *
		 * @since 4.8
		 * @param array $actions
		 * @param WP_Post $post
		 * @return array
		 */
		public function handler_page_row_actions( array $actions, $post )
		{
			if( ! $post instanceof WP_Post || $post->post_type != $this->get_post_type() )
			{
				return $actions;
			}

			//	Quick Edit Link
			unset( $actions['inline hide-if-no-js'] );

			if( ! current_user_can( $this->get_capability( 'new' ) ) )
			{
				return $actions;
			}

			$shortcode = Avia_Builder()->get_posts_alb_content( $post->ID );

			if( empty( $shortcode ) )
			{
				return $actions;
			}

			$notify_url = wp_nonce_url( admin_url( 'edit.php?post_type=' . $this->get_post_type() . '&action=duplicate_element_template&amp;post=' . $post->ID ), 'avia-duplicate-element-template_' . $post->ID );

			$duplicate  = '<a href="' . $notify_url . '" aria-label="' . esc_attr__( 'Make a duplicate from this element template', 'avia_framework' ) . '" rel="permalink">';
			$duplicate .=		esc_html__( 'Duplicate', 'avia_framework' );
			$duplicate .= '</a>';

			$actions['duplicate'] = $duplicate;

			return $actions;
		}

		/**
		 * Remove info set by theme
		 *
		 * @since 4.8
		 * @param array $post_states
		 * @param WP_Post $post
		 * @return array
		 */
		public function handler_display_post_states( array $post_states, $post = null )
		{
			if( ! $post instanceof WP_Post || $post->post_type != $this->get_post_type() )
			{
				return $post_states;
			}

			unset( $post_states['avia_alb'] );
			unset( $post_states['wp_editor'] );

			return $post_states;
		}

		/**
		 * Add custom columns
		 *
		 * @since 4.8
		 * @param array $columns
		 * @return array
		 */
		public function handler_edit_alb_elements_columns( $columns )
		{
			$newcolumns = array(
								'cb'			=> '',
								'title'			=> '',
								'alb_element'	=> __( 'ALB Element', 'avia_framework' ),
								'alb_tooltip'	=> __( 'Excerpt (= Tooltip)', 'avia_framework' ),
								'alb_author'	=> __( 'Author', 'avia_framework' )
							);

			return array_merge( $newcolumns, $columns );;
		}

		/**
		 * Add custom values to columns
		 *
		 * @since 4.8
		 * @param string $column
		 * @param int $post_id
		 */
		public function handler_pages_custom_column( $column, $post_id )
		{
			global $post;

			switch( $column )
			{
				case 'alb_element':
					echo get_the_term_list( $post_id, $this->get_taxonomy(), '', ', ', '' );

					$content = Avia_Builder()->get_posts_alb_content( $post->ID );
					if( empty( trim( $content) ) )
					{
						break;
					}

					$sc_array = $this->get_element_template_info_from_content( $content );

					if( array_key_exists( 'select_element_template', $sc_array[0]['attr'] ) && ( 'item' == $sc_array[0]['attr']['select_element_template'] ) )
					{
						$main = false;

						$term = get_term_by( 'slug', $sc_array[0]['shortcode'], $this->get_taxonomy() );
						if( $term instanceof WP_Term )
						{
							$link = get_term_link( $term, $this->get_taxonomy() );

							if( ! is_wp_error( $link ) )
							{
								$main = '<a href="' . esc_url( $link ) . '" rel="tag">' . $term->name . '</a>';
							}
						}

						if( false === $main )
						{
							$sc = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $sc_array[0]['shortcode'] ] ];
							$main = $sc->config['name'];
						}

						echo ' ( ' . $main . ' )';
					}
					break;

				case 'alb_tooltip':
					echo '<p class="avia-element-tooltip">' . esc_html( trim( $post->post_excerpt ) ) . '</p>';
					break;

				case 'alb_author':
					$name = get_the_author_meta( 'display_name', $post->post_author );
					if( empty( $name ) )
					{
						$name = get_the_author_meta( 'user_login', $post->post_author );
					}

					echo '<a href="' . esc_url( get_author_posts_url( $post->post_author ) ) . '">' . esc_html( $name ) . '</a>';
					break;
			}

		}

		/**
		 * Add our custom select boxes to top of the table before filter button
		 *
		 * @since 4.8
		 * @param string $post_type
		 * @return void
		 */
		public function handler_wp_restrict_manage_posts( $post_type )
		{
			if ( $post_type != $this->get_post_type() )
			{
				return;
			}

			$taxonomies = get_object_taxonomies( $post_type, 'names' );

			if( empty( $taxonomies ) )
			{
				return;
			}

			$terms = get_terms( $taxonomies, array( 'orderby' =>  'name', 'oder' => 'ASC' ) );

			if( ! is_array( $terms ) || empty( $terms ) )
			{
				return;
			}

			$last_filter = ( ! is_numeric( $this->filter_category ) ) ? 'all' : (int) $this->filter_category ;

			echo	'<label class="screen-reader-text" for="av_elements_filter_elements">' . __( 'Filter by Elements', 'avia_framework' ) . '</label>';
			echo	'<select id="av_elements_filter_elements" class="postform" name="av_elements_filter_elements">';
			echo		'<option value="all" ' . selected( $last_filter, 'all', false ) . '>'. __( 'All ALB Elements', 'avia_framework' ) . '</option>';

			foreach ( $terms as $term )
			{
				echo	'<option value="' . $term->term_id . '" ' . selected( $last_filter, $term->term_id, false ) . '>'. $term->name . '</option>';
			}

			echo '<select>';

			$args = array(
					'has_published_posts'	=> array( $this->get_post_type() )
				);

			$users = get_users( $args );
			$uids = array();

			foreach( $users  as $user )
			{
				$uids[] = $user->ID;
			}

			$sel_user = isset( $_REQUEST['av_elements_filter_user'] ) ? $_REQUEST['av_elements_filter_user'] : 0;
			$sel_user = is_numeric( $sel_user ) && ( (int) $sel_user >= 0 ) ? (int) $sel_user : 0;

			$args = array(
					'show_option_all'	=> __( 'All users', 'avia_framework' ),
					'orderby'			=> 'display_name',
					'order'				=> 'ASC',
					'include'			=> $uids,
					'name'				=> 'av_elements_filter_user',
					'id'				=> 'av_elements_filter_user',
					'class'				=> 'postform',
					'selected'			=> $sel_user
				);

			wp_dropdown_users( $args );
		}

		/**
		 * We add our filter parameters from the select boxes to the WP query
		 *
		 * @since v1.0.0
		 *
		 * @param string $where
		 * @param WP_Query $query
		 * @return string
		 */
		public function handler_wp_posts_where( $where, WP_Query $query )
		{
			global $wpdb;

			if ( ! $this->is_element_overview_page() )
			{
				return $where;
			}

			if( ( ! isset( $_REQUEST['filter_action'] ) ) || ( 'filter' != strtolower( $_REQUEST['filter_action'] ) ) )
			{
				return $where;
			}

			$filter_category = isset( $_REQUEST['av_elements_filter_elements'] ) ? $_REQUEST['av_elements_filter_elements'] : 'all';
			$this->filter_category = ( ! is_numeric( $filter_category ) ) ? 'all' : (int) $filter_category;

			$filter_user = isset( $_REQUEST['av_elements_filter_user'] ) ? $_REQUEST['av_elements_filter_user'] : 0;

			if( is_numeric( $filter_user ) && (int) $filter_user > 0 )
			{
				$filter_user = (int) $filter_user;
				$where .=	" AND  ( {$wpdb->posts}.post_author = {$filter_user} ) ";
			}

			return $where;
		}

		/**
		 * We add our filter parameters from the select boxes to the WP query
		 *
		 * @since v1.0.0
		 * @global type $wpdb
		 *
		 * @param string $join
		 * @param WP_Query $query
		 * @return string
		 */
		public function handler_wp_posts_join( $join, WP_Query $query )
		{
			global $wpdb;

			if ( ! $this->is_element_overview_page() )
			{
				return $join;
			}

			if( ( ! isset( $_REQUEST['filter_action'] ) ) || ( 'filter' != strtolower( $_REQUEST['filter_action'] ) ) )
			{
				return $join;
			}

			if( 'all' != $this->filter_category )
			{
				$join .=	"	INNER JOIN {$wpdb->term_relationships} AS tr_category
										ON ({$wpdb->posts}.ID = tr_category.object_id)
								INNER JOIN {$wpdb->term_taxonomy} AS tt_category
										ON (tr_category.term_taxonomy_id = tt_category.term_taxonomy_id) AND (tt_category.term_id = {$this->filter_category} )
							";
			}

			return $join;
		}

		/**
		 * Request to duplicate an element template.
		 * Called from links in element overview page or from single element action meta box
		 * Reroutes to new created element for editing
		 *
		 * @since 4.8
		 */
		public function handler_admin_action_duplicate_element_template()
		{
			if ( empty( $_REQUEST['post'] ) )
			{
				wp_die( esc_html__( 'No element template to duplicate has been supplied!', 'avia_framework' ) );
			}

			$element_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : '';

			check_admin_referer( 'avia-duplicate-element-template_' . $element_id );

			$element = $this->get_post( $element_id );

			if( ! $element instanceof WP_Post || $element->post_type != $this->get_post_type() )
			{
				wp_die( esc_html__( 'Invalid element template to duplicate has been supplied or template has been deleted.', 'avia_framework' ) );
			}

			$postarr = array(
					'post_title'	=> $element->post_title . ' ' . __( '(Copy)', 'avia_framework' ),
					'post_content'	=> $element->post_content,
					'post_excerpt'	=> $element->post_excerpt,
					'post_author'	=> get_current_user_id(),
					'post_status'	=> 'draft',
					'post_type'		=> $element->post_type,
					'post_parent'	=> $element->post_parent
				);

			$duplicate_id = wp_insert_post( $postarr );

			if( $duplicate_id instanceof WP_Error || $duplicate_id == 0 )
			{
				wp_die( esc_html__( 'Creating a copy of element template has failed. Please try again', 'avia_framework' ) );
			}

			$meta = Avia_Builder()->get_alb_meta_key_names( $element_id, 'save' );

			if( is_array( $meta ) )
			{
				foreach( $meta as $key )
				{
					$value = get_post_meta( $element_id, $key, true );
					update_post_meta( $duplicate_id, $key, $value );
				}
			}

			$terms = get_the_terms( $element_id, $this->get_taxonomy() );

			if( is_array( $terms ) )
			{
				foreach( $terms as $term )
				{
					wp_set_object_terms( $duplicate_id, $term->term_id, $this->get_taxonomy(), false );
				}
			}

			// Redirect to the edit screen for the new draft page.
			wp_redirect( admin_url( 'post.php?action=edit&post=' . $duplicate_id ) );
			exit;
		}

		/**
		 * Checks if the title for a custom element template is unique
		 *
		 * @since 4.8
		 */
		public function handler_ajax_element_check_title()
		{
			header( 'Content-Type: application/json' );

			$response = array(
						'_ajax_nonce'	=> wp_create_nonce( 'avia_nonce_loader' ),
						'success'		=> false,
						'message'		=> ''
					);

			if( ! $this->current_user_can_manage() )
			{
				$response['message'] = $this->no_capability;
				echo json_encode( $response );
				exit;
			}

			$return = check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce', false );

			/**
			 * Return error and allow to resend data
			 */
			if( false === $return )
			{
				$response['success'] = false;
				$response['expired_nonce'] = true;
				echo json_encode( $response );
				exit;
			}

			$title = isset( $_REQUEST['title'] ) ? $_REQUEST['title'] : '';
			$title_new = $this->sanitize_element_title( $title );

			$id = post_exists( $title_new, '', '', $this->get_post_type() );

			$message = array();

			if( $id != 0 )
			{
				$message[] = __( 'This element title already exists.', 'avia_framework' );
			}

			if( $title != $title_new )
			{
				$message[] = __( 'Title will be changed to:', 'avia_framework' ) . ' ' . $title_new;
			}

			$response['message'] = implode( '<br />', $message );

			$response['success'] = true;
			echo json_encode( $response );
			exit;
		}

		/**
		 * Callback handler for Custom Elements CPT actions from modal popup in ALB editor:
		 *
		 *		'new_custom_element':		Creates a new element template based on a clean ALB element
		 *		'update_element_post_data':	Updates title and tooltip for an existing element
		 *		'new_element_from_alb':		Creates a new custom element template based on the settings of an ALB element (supports base and item elements)
		 *
		 * Logic for subitem custom elements subitem_custom_element_handling():
		 *		'':					One template is used for all subitems, creating and editing is managed in base element
		 *		'individually':		Each subitem can have a different template, templates must be created manually and selected for each subitem
		 *		'none':				No templates at all are allowed for subitems
		 *
		 * @since 4.8
		 */
		public function handler_ajax_element_template_cpt_actions()
		{
			header( 'Content-Type: application/json' );

			$response = array(
						'_ajax_nonce'	=> wp_create_nonce( 'avia_nonce_loader' ),
						'success'		=> false,
						'message'		=> ''
					);

			if( ! $this->current_user_can_manage() )
			{
				$response['message'] = $this->no_capability;
				echo json_encode( $response );
				exit;
			}

			$return = check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce', false );

			/**
			 * Return error and allow to resend data
			 */
			if( false === $return )
			{
				$response['expired_nonce'] = $this->nonce_message;
				echo json_encode( $response );
				exit;
			}

			$msg1 = __( 'Custom Element could not be created/updated.', 'avia_framework' ) . ' ';
			$msg2 = __( 'Not enough information provided.', 'avia_framework' );

			$subaction = isset( $_REQUEST['subaction'] ) ? $_REQUEST['subaction'] : '';
			$params = isset( $_REQUEST['modal_params'] ) ? $_REQUEST['modal_params'] : array();

			if( ! is_array( $params ) || empty( $params ) || ! in_array( $subaction, array( 'new_custom_element', 'update_element_post_data', 'new_element_from_alb' ) ) )
			{
				$response['message'] = $msg1 . $msg2;
				echo json_encode( $response );
				exit;
			}

			$sc = isset( $params['add_new_element_shortcode'] ) ? $params['add_new_element_shortcode'] : '';
			$sc = explode( ';', $sc );
			$is_item = false;

			//	shortcode_handler || shortcode_item;shortcode_handler
			if( isset( $sc[1] ) && ! empty( $sc[1] ) )
			{
				$shortcode_handler = trim( $sc[1] );
				$item_sc = $sc[0];
				$is_item = true;
			}
			else
			{
				$shortcode_handler = $sc[0];
				$item_sc = '';
			}

			if( empty( $shortcode_handler ) || ! array_key_exists( $shortcode_handler, Avia_Builder()->shortcode ) )
			{
				$response['message'] = $msg1 . $msg2;
				echo json_encode( $response );
				exit;
			}

			if( $is_item && $this->subitem_custom_element_handling() != 'individually' )
			{
				$response['message'] = $msg1 . $msg2;
				echo json_encode( $response );
				exit;
			}

			$shortcode = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $shortcode_handler ] ];

			if( $is_item )
			{
				if( ! isset( $shortcode->config['shortcode_nested'][0] ) || $shortcode->config['shortcode_nested'][0] != $item_sc )
				{
					$response['message'] = $msg1 . __( 'Illegal shortcode.', 'avia_framework' );
					echo json_encode( $response );
					exit;
				}
			}

			$element_id = isset( $params['element_id'] ) ? $params['element_id'] : 0;
			$template = null;
			$content = '';
			$tree = array();
			$js_templates = array();

			$title = isset( $params['base_element_title'] ) ? $params['base_element_title'] : $shortcode->config['name'] . ' ' . __( 'Copy', 'avia_framework' );
			$title = $this->sanitize_element_title( $title );
			$tooltip = isset( $params['base_element_tooltip'] ) ? $params['base_element_tooltip'] : $shortcode->config['tooltip'];
			$author = get_current_user_id();

			if( 'new_custom_element' == $subaction )
			{
				$this->add_element_template_options( $shortcode );

				if( $this->is_editable_modal_group_element( $shortcode ) && 'first' == $this->subitem_custom_element_handling() )
				{
					//	Create the item template for this element
					$outer_attr = $shortcode->get_default_sc_args();
					$item_attr = $shortcode->get_default_modal_group_args();
					$item_content = $this->create_custom_element_shortcode( $shortcode, $item_attr, $outer_attr, true );
					$item_tree = ShortcodeHelper::build_shortcode_tree( $item_content );
					$tt = ! empty( $tooltip ) ? '(' . $tooltip . ')' : '';

					$this->cached_update_sc_array = $this->get_element_template_info_from_content( $item_content );

					$postarr = array(
								'post_author'	=> $author,
								'post_title'	=> sprintf( __( 'Item for: %s', 'avia_framework' ), $title ),
								'post_content'	=> $item_content,
								'post_excerpt'	=> sprintf( __( 'An item for %s %s', 'avia_framework' ), $title, $tt ),
								'post_status'	=> 'publish',
								'post_type'		=> $this->get_post_type(),
								'post_parent'	=> $this->cached_update_sc_array[0]['template_id']
							);

					$item_id = wp_insert_post( $postarr );

					if( ! is_numeric( $item_id ) || 0 == $item_id )
					{
						$response['message'] = $msg1;
						echo json_encode( $response );
						exit;
					}

					Avia_Builder()->save_posts_alb_content( $item_id, $item_content );
					Avia_Builder()->set_alb_builder_status( 'active', $item_id );
					Avia_Builder()->save_shortcode_tree( $item_id, $item_tree );

					//	assign correct category to element template
					$this->updated_post_content( $item_content, $item_id, $item_tree, true );

					$item_args = $this->cached_update_sc_array[0]['attr'];
					$item_raw_content = $this->cached_update_sc_array[0]['raw_content'];

					$js_templates = $this->js_scripts_from_array( $shortcode, $item_args, $item_raw_content, $item_id );

					$outer_attr['one_element_template'] = $item_id;
					$outer_attr['content'] = array( $item_attr );
					$outer_attr['content'][0]['element_template'] = $item_id;

					$content = $this->create_custom_element_shortcode( $shortcode, $outer_attr, array(), false );
				}
				else
				{
					if( $is_item )
					{
						$this->set_as_item_template( $shortcode );
					}

					$content = $shortcode->prepare_editor_element( false, array(), 'element', 'shortcode_only' );
				}

				$tree = ShortcodeHelper::build_shortcode_tree( $content );
			}
			else if( 'update_element_post_data' == $subaction )
			{
				//	check for correct shortcode
				$this->load_template_cache( $element_id );
				$check_sc = ! $is_item ? $shortcode_handler : $item_sc;

				$template = $this->get_template( $element_id, $check_sc );

				if( false === $template )
				{
					$response['message'] = $msg1 . ' ' . __( 'Custom Element and stored Custom Element do not match.', 'avia_framework' );
					echo json_encode( $response );
					exit;
				}
			}
			else if( 'new_element_from_alb' == $subaction )
			{
				$this->add_element_template_options( $shortcode );

				$ajax_param = isset( $_REQUEST['ajax_param'] ) ? $_REQUEST['ajax_param'] : array();
				$outer_attr = isset( $ajax_param['outer_sc_params'] ) ? AviaHelper::clean_attributes_array( $ajax_param['outer_sc_params'] ) : array();
				$attr = isset( $ajax_param['sc_params'] ) ? AviaHelper::clean_attributes_array( $ajax_param['sc_params'] ) : array();

				if( ! $is_item && $this->is_editable_modal_group_element( $shortcode ) )
				{
					if( in_array( $this->subitem_custom_element_handling(), array( 'first', 'none' ) ) )
					{
						//	only keep first subitem (hidden by CSS if necessary)
						if( isset( $attr['content'] ) && is_array( $attr['content'] ) && ! empty( $attr['content'] ) )
						{
							$attr['content'] = array( $attr['content'][0] );
						}
					}
				}

				$content = $this->create_custom_element_shortcode( $shortcode, $attr, $outer_attr, $is_item );
				$tree = ShortcodeHelper::build_shortcode_tree( $content );
			}

			$modal_title = sprintf( __( ' - Customize: %s', 'avia_framework' ), $title );
			$modal_subitem_title = '';
			$update_modal_title = $shortcode->config['name'] . $modal_title;

			if( $is_item )
			{
				$modal_title =  sprintf( __( ' - Customize Item: %s', 'avia_framework' ), $title );
				$modal_subitem_title = sprintf( __( ' - Customize: %s', 'avia_framework' ), $title );
				$update_modal_title = $shortcode->config['name'] . $modal_subitem_title;
			}

			$this->cached_update_sc_array = is_null( $template ) ? $this->get_element_template_info_from_content( $content ) : $template['sc_array'];

			if( 'update_element_post_data' == $subaction )
			{
				$postarr = array(
						'ID'			=> $element_id,
						'post_title'	=> $title,
						'post_excerpt'	=> $tooltip,
						'post_status'	=> 'publish',
						'post_type'		=> $this->get_post_type(),
						'post_parent'	=> $this->cached_update_sc_array[0]['template_id']
					);

				$post_id = wp_update_post( $postarr );
			}
			else
			{
				$postarr = array(
							'post_author'	=> $author,
							'post_title'	=> $title,
							'post_content'	=> $content,
							'post_excerpt'	=> $tooltip,
							'post_status'	=> 'publish',
							'post_type'		=> $this->get_post_type(),
							'post_parent'	=> $this->cached_update_sc_array[0]['template_id']
						);

				$post_id = wp_insert_post( $postarr );
			}

			if( ! is_numeric( $post_id ) || 0 == $post_id )
			{
				$response['message'] = $msg;
				echo json_encode( $response );
				exit;
			}

			if( in_array( $subaction, array( 'new_custom_element', 'new_element_from_alb' ) ) )
			{
				Avia_Builder()->save_posts_alb_content( $post_id, $content );
				Avia_Builder()->set_alb_builder_status( 'active', $post_id );
				Avia_Builder()->save_shortcode_tree( $post_id, $tree );

				//	assign correct category to element template
				$this->updated_post_content( $content, $post_id, $tree, true );
			}

			//	Prepare return values
			$config = $shortcode->config;

			if( ! isset( $config['class'] ) )
			{
				$config['class'] = '';
			}

			$config['name'] = $title;
			$config['tooltip'] = $tooltip;
			$config['class'] .= ' avia-custom-element-button';
			$config['class'] .= $is_item ? ' avia-custom-element-item' : ' avia-custom-element-base';
			$config['btn_data'] = array(
									'shortcode_handler'		=> $is_item ? $item_sc : $shortcode_handler,
									'base_shortcode'		=> $is_item ? $shortcode_handler : '',
									'is_item'				=> $is_item ? 'true' : 'false',
									'modal_title'			=> $modal_title,
									'modal_subitem_title'	=> $modal_subitem_title,
									'element_title'			=> $title,
									'element_tooltip'		=> $tooltip
								);
			$config['btn_id'] = 'avia-element-btn-' . $post_id;

			$templ_id = $config['php_class'] . '_' . $post_id;

			$args = ! $is_item ? $this->cached_update_sc_array[0]['template_attr'] : $this->cached_update_sc_array[0]['attr'];
			$raw_content = $this->cached_update_sc_array[0]['raw_content'];

			$response['tab'] = $config['tab'];
			$response['default_tab'] = Avia_Builder()->default_sc_btn_tab_name;
			$response['icons'][ '#' . $config['btn_id'] ] = $this->create_shortcode_button( $config, 500, $templ_id, $post_id );

			$response['js_templates'] = array_merge( $this->js_scripts_from_array( $shortcode, $args, $raw_content, $post_id ), $js_templates );

			$response['no_tab_message'] = __( 'Sorry, custom element icon section does not exist currently. Please save page content and reload page to see icon for the new element.', 'avia_framework' );


			if( $subaction == 'update_element_post_data' )
			{
				$response['message'] = sprintf( __( 'Custom element &quot;%s&quot; successfully updated.', 'avia_framework' ), $title );
			}
			else
			{
				$response['message'] = sprintf( __( 'Custom element &quot;%s&quot; successfully created. Continue with setting the options for this element.', 'avia_framework' ), $title );
			}

			$response['change_info'] = array(
									'element_id'	=> $post_id,
									'title'			=> $title,
									'tooltip'		=> $tooltip,
									'modal_title'	=> $update_modal_title
							);

			$response['success'] = true;
			echo json_encode( $response );
			exit;
		}

		/**
		 * Update an existing element template.
		 *
		 * @since 4.8
		 */
		public function handler_ajax_element_template_update_content()
		{
			header( 'Content-Type: application/json' );

			$response = array(
						'_ajax_nonce'	=> wp_create_nonce( 'avia_nonce_loader' ),
						'success'		=> false,
						'message'		=> ''
					);

			if( ! $this->current_user_can_manage() )
			{
				$response['message'] = $this->no_capability;
				echo json_encode( $response );
				exit;
			}

			$return = check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce', false );

			/**
			 * Return error and allow to resend data
			 */
			if( false === $return )
			{
				$response['expired_nonce'] = $this->nonce_message;
				echo json_encode( $response );
				exit;
			}

			$msg1 = __( 'Custom Element could not be updated.', 'avia_framework' );

			$element_id = isset( $_REQUEST['element_id'] ) && is_numeric( $_REQUEST['element_id'] ) ? (int) $_REQUEST['element_id'] : 0;
			$shortcode = isset( $_REQUEST['shortcode'] ) ? $_REQUEST['shortcode'] : '';
			$args = isset( $_REQUEST['params'] ) && is_array( $_REQUEST['params'] ) ? $_REQUEST['params'] : array();

			if( empty( $element_id ) || empty( $shortcode ) || empty( $args ) )
			{
				$response['message'] = $msg1 . ' ' . __( 'Not enough information provided.', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			//	always clear unique id's for custom elements
			$shortcode = Avia_Builder()->element_manager()->clear_element_ids_in_content( wp_unslash( $shortcode ) );

			$sc_array = $this->get_element_template_info_from_content( $shortcode );

			$is_item = $sc_array[0]['shortcode'] != $sc_array[0]['template_sc'];
			$class_sc = ! $is_item ? $sc_array[0]['template_sc'] : $sc_array[0]['shortcode'];

			$this->load_template_cache( $element_id );

			$template = $this->get_template( $element_id, $sc_array[0]['template_sc'] );

			if( false === $template )
			{
				$response['message'] = $msg1 . ' ' . __( 'Custom Element shortcode edited and stored shortcode do not match.', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			if( ! array_key_exists( $class_sc, Avia_Builder()->shortcode ) )
			{
				$response['message'] = $msg1 . ' ' . __( 'Shortcode does no longer exist or has been deactivated.', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			$shortcode_class = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $class_sc ] ];
			$js_templates = array();

			//	Special case: First Subitem used as custom element template for all subitems
			if( $this->is_editable_modal_group_element( $shortcode_class ) && 'first' == $this->subitem_custom_element_handling() )
			{
				$item_template = false;

				//	try to get template
				if( array_key_exists( 'one_element_template', $args ) && ! empty( $args['one_element_template'] ) )
				{
					$this->load_template_cache( $args['one_element_template'] );
					$item_template = $this->get_template( $args['one_element_template'], $sc_array[0]['content'][0]['shortcode'] );
				}

				//	first subitem defines the setting for all
				if( is_array( $sc_array[0]['content'] ) && ! empty( $sc_array[0]['content'] ) )
				{
					$item_attr = $sc_array[0]['content'][0]['attr'];
					$item_attr['content'] = $sc_array[0]['content'][0]['raw_content'];
					$item_attr['element_template'] = ( false !== $item_template ) ? $item_template['sc_array'][0]['template_id'] : '';
				}
				else
				{
					$item_attr = $shortcode_class->get_default_modal_group_args();
				}

				$outer_attr = ( false === $item_template ) ? $shortcode_class->get_default_sc_args() : $item_template['sc_array'][0]['attr'];
				$item_content = $this->create_custom_element_shortcode( $shortcode_class, $item_attr, $outer_attr, true, true );
				$item_tree = ShortcodeHelper::build_shortcode_tree( $item_content );

				$this->cached_update_sc_array = $this->get_element_template_info_from_content( $item_content );

				//	fallback situation or template has been deleted - recreate a new template
				if( false === $item_template )
				{
					$tt = ! empty( $template['post']->post_excerpt ) ? " ({$template['post']->post_excerpt})" : '';

					$postarr = array(
								'post_author'	=> $template['post']->post_author,
								'post_title'	=> sprintf( __( 'Item for: %s', 'avia_framework' ), $template['post']->post_title ),
								'post_content'	=> $item_content,
								'post_excerpt'	=> sprintf( __( 'An item for %s', 'avia_framework' ), $template['post']->post_title . $tt ),
								'post_status'	=> 'publish',
								'post_type'		=> $this->get_post_type(),
								'post_parent'	=> $this->cached_update_sc_array[0]['template_id']
							);

					$item_id = wp_insert_post( $postarr );
				}
				else
				{
					$postarr = array(
								'ID'			=> $args['one_element_template'],
								'post_content'	=> $item_content,
								'post_status'	=> 'publish',
								'post_type'		=> $this->get_post_type(),
								'post_parent'	=> $this->cached_update_sc_array[0]['template_id']
							);

					$item_id = wp_update_post( $postarr );
				}

				if( ! is_numeric( $item_id ) || 0 == $item_id )
				{
					$response['message'] = $msg1;
					echo json_encode( $response );
					exit;
				}

				Avia_Builder()->save_posts_alb_content( $item_id, $item_content );
				Avia_Builder()->set_alb_builder_status( 'active', $item_id );
				Avia_Builder()->save_shortcode_tree( $item_id, $item_tree );

				//	assign correct category to element template
				$this->updated_post_content( $item_content, $item_id, $item_tree, true );

				$item_args = $this->cached_update_sc_array[0]['attr'];
				$item_raw_content = $this->cached_update_sc_array[0]['raw_content'];

				$js_templates = $this->js_scripts_from_array( $shortcode_class, $item_args, $item_raw_content, $item_id );

				$sub_content = array();

				//	Set all items to same custom element
				if( is_array( $sc_array[0]['content'] ) && ! empty( $sc_array[0]['content'] ) )
				{
					foreach( $sc_array[0]['content'] as &$sub )
					{
						$attr = $sub['attr'];
						$attr['element_template'] = $item_id;
						$attr['content'] = $sub['raw_content'];

						$sub_content[] = $attr;
					}
				}

				$args['one_element_template'] = $item_id;

				$attr = $args;
				$attr['content'] = $sub_content;

				$shortcode = $this->create_custom_element_shortcode( $shortcode_class, $attr, array(), false, true );

				$sc_array = $this->get_element_template_info_from_content( $shortcode );
			}

			$this->cached_update_sc_array = $sc_array;

			$postarr = array(
						'ID'			=> $element_id,
						'post_content'	=> $shortcode,
						'post_status'	=> 'publish',
						'post_type'		=> $this->get_post_type(),
						'post_parent'	=> $this->cached_update_sc_array[0]['template_id']
					);

			$post_id = wp_update_post( $postarr );

			if( ! is_numeric( $post_id ) || 0 == $post_id )
			{
				$response['message'] = $msg1 . ' ' . __( 'Database error on saving.', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			$tree = ShortcodeHelper::build_shortcode_tree( $shortcode );

			Avia_Builder()->save_posts_alb_content( $post_id, $shortcode );
			Avia_Builder()->set_alb_builder_status( 'active', $post_id );
			Avia_Builder()->save_shortcode_tree( $post_id, $tree );

			$args = ! $is_item ? $this->cached_update_sc_array[0]['template_attr'] : $this->cached_update_sc_array[0]['attr'];
			$raw_content = $this->cached_update_sc_array[0]['raw_content'];

			$response['js_templates'] = array_merge( $this->js_scripts_from_array( $shortcode_class, $args, $raw_content, $post_id ), $js_templates );

			$response['message'] = sprintf( __( 'Custom Element &quot;%s&quot; was updated.', 'avia_framework' ), $template['post']->post_title );
			$response['success'] = true;
			echo json_encode( $response );
			exit;
		}

		/**
		 * Delete the element and depending on theme option setting also subitem element
		 *
		 * @since 4.8
		 */
		public function handler_ajax_element_template_delete()
		{
			header( 'Content-Type: application/json' );

			$response = array(
						'_ajax_nonce'	=> wp_create_nonce( 'avia_nonce_loader' ),
						'success'		=> false,
						'message'		=> ''
					);

			if( ! $this->current_user_can_manage() )
			{
				$response['message'] = $this->no_capability;
				echo json_encode( $response );
				exit;
			}

			$return = check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce', false );

			/**
			 * Return error and allow to resend data
			 */
			if( false === $return )
			{
				$response['expired_nonce'] = $this->nonce_message;
				echo json_encode( $response );
				exit;
			}

			$element_id = isset( $_REQUEST['element_id'] ) && is_numeric( $_REQUEST['element_id'] ) ? (int) $_REQUEST['element_id'] : 0;
			$title = isset( $_REQUEST['title'] ) ? $_REQUEST['title'] : __( 'Unknown element name', 'avia_framework' );
			$shortcode = isset( $_REQUEST['shortcode'] ) ? $_REQUEST['shortcode'] : '';
			$base_shortcode = isset( $_REQUEST['baseShortcode'] ) ? $_REQUEST['baseShortcode'] : '';
			$is_item = isset( $_REQUEST['isItem'] ) && 'true' === $_REQUEST['isItem'];

			$msg1 = sprintf( __( 'Custom Element &quot;%s&quot; could not be deleted.', 'avia_framework' ), $title );
			$subitem_msg = '';

			if( empty( $element_id ) || empty( $shortcode ) || ( $is_item && empty( $base_shortcode ) ) )
			{
				$response['message'] = $msg1 . ' ' . __( 'Not enough information provided.', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			$this->load_template_cache( $element_id );
			$template = $this->get_template( $element_id, $shortcode );

			if( false === $template )
			{
				$response['message'] = $msg1 . ' ' . __( 'Custom Element shortcode to delete and stored shortcode do not match.', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			if( $is_item && 'first' == $this->subitem_custom_element_handling() )
			{
				$response['message'] = $msg1 . ' ' . __( 'Custom subitem elements cannot be deleted when &quot;All subitems use the same custom element template&quot; is selected in theme options. This is the default setting.', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			//	check if we need to delete subitem
			if( ! $is_item && 'first' == $this->subitem_custom_element_handling())
			{
				$sub_id = isset( $template['sc_array'][0]['template_attr']['one_element_template'] ) ? $template['sc_array'][0]['template_attr']['one_element_template'] : '';
				$sub_deleted = false;

				if( ! empty( $sub_id ) && is_numeric( $sub_id ) )
				{
					$sub_deleted = wp_delete_post( $sub_id, true );

					if( $sub_deleted instanceof WP_Post )
					{
						$subitem_msg .= ' ' . __( 'Custom element subitem settings have also been deleted.', 'avia_framework' );
					}
				}
			}

			$deleted = wp_delete_post( $element_id, true );

			if( ! $deleted instanceof WP_Post )
			{
				$response['message'] = $msg1 . ' ' . __( 'Internal database error occured deleting the custom element.', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			$response['message'] = sprintf( __( 'Custom Element &quot;%s&quot; was deleted.', 'avia_framework' ), $title ) . $subitem_msg;
			$response['success'] = true;
			echo json_encode( $response );
			exit;
		}

		/**
		 * Register the ALB Element Template Post Type and Categories
		 *
		 * @since 4.8
		 */
		public function register_post_types()
		{
			if( ! $this->element_templates_enabled() )
			{
				return;
			}

			$labels = array(
						'name'					=> __( 'ALB Custom Elements', 'avia_framework' ),
						'singular_name'			=> __( 'ALB Custom Element', 'avia_framework' ),
						'add_new'				=> __( 'Add New Custom Element', 'avia_framework' ),
						'add_new_item'			=> __( 'Add New ALB Custom Element Template', 'avia_framework' ),
						'edit_item'				=> __( 'Edit ALB Custom Element Template', 'avia_framework' ),
						'new_item'				=> __( 'New ALB Custom Element', 'avia_framework' ),
						'view_item'				=> __( 'View ALB Custom Element Template', 'avia_framework' ),
						'search_items'			=> __( 'Search ALB Custom Element Templates', 'avia_framework' ),
						'not_found'				=> __( 'No ALB Custom Element Template found', 'avia_framework' ),
						'not_found_in_trash'	=> __( 'No ALB Custom Element Templates found in Trash', 'avia_framework' ),
						'parent_item_colon'		=> ''
					);

			$args = array(
						'labels'			=> $labels,
						'public'			=> true,
						'show_ui'			=> true,
						'show_in_menu'		=> false,
						'capability_type'	=> 'post',
						'hierarchical'		=> true,
						'rewrite'			=> false,
						'query_var'			=> true,
						'show_in_nav_menus'	=> false,
						'show_in_rest'		=> false,				//	set to false to disallow block editor
						'taxonomies'		=> array(),
						'supports'			=> array( 'title', 'excerpt', 'editor', 'revisions' ),
						'menu_icon'			=> 'dashicons-images-alt2',
						'can_export'		=> true,
					);

			/**
			 * @since 4.8
			 * @param array $args
			 * @return array
			 */
			$args = apply_filters( 'avf_custom_elements_cpt_args', $args );

			register_post_type( $this->get_post_type() , $args );


			$tax_args = array(
							'hierarchical'			=> true,
							'label'					=> __( 'ALB Custom Element Template Categories', 'avia_framework' ),
							'singular_label'		=> __( 'ALB Custom Element Template Category', 'avia_framework' ),
							'rewrite'				=> false,
							'show_ui'				=> false,			//	hide in admin menu and meta box
							'show_in_quick_edit'	=> false,
							'query_var'				=> true,
							'show_in_rest'			=> false			//	set to false to disallow block editor
						);

			/**
			 * @since 4.8
			 * @param array $tax_args
			 * @return array
			 */
			$tax_args = apply_filters( 'avf_custom_elements_cpt_tax_args', $tax_args );

			register_taxonomy( $this->get_taxonomy(), array( $this->get_post_type() ), $tax_args );
		}

		/**
		 * Returns, if the feature has been enabled in theme options
		 *
		 * @since 4.8
		 * @return boolean
		 */
		public function element_templates_enabled()
		{
			if( is_null( $this->enabled ) )
			{
				$enabled = avia_get_option( 'alb_element_templates' ) != '';

				/**
				 * @used_by			avia_WPML
				 * @since 4.8
				 * @param boolean
				 * @return boolean
				 */
				$this->enabled = apply_filters( 'avf_element_templates_enabled', $enabled );
			}

			return $this->enabled;
		}

		/**
		 * Returns the filtered string for a capability to show menus to edit elements.
		 *
		 * @since 4.8
		 * @param string $which				'new' | 'edit'
		 * @return string
		 */
		public function get_capability( $which = 'new' )
		{
			$option = avia_get_option( 'alb_element_templates' );

			$cap = 'admins_only' == $option ? 'manage_options' : 'edit_posts';

			/**
			 * Filter the user capability to create and edit ALB Element Templates
			 * Make sure to return a valid capability.
			 *
			 * @since 4.8
			 * @param string $cap
			 * @param string $which				'new' | 'edit'
			 * @param string $option
			 * @return string
			 */
			return apply_filters( 'avf_custom_elements_user_capability', $cap, $which, $option );
		}

		/**
		 * Check if current user has capability to manage custom elements
		 *
		 * @since 4.8
		 * @return boolean
		 */
		public function current_user_can_manage()
		{
			$option = avia_get_option( 'alb_element_templates' );

			if( '' == $option )
			{
				return false;
			}

			return current_user_can( $this->get_capability() );
		}

		/**
		 * Check if user allows CPT screens in backend for custom element templates.
		 * This is only intended for developers. Also plugins like WPML might use CPT screens for translating.
		 *
		 * @since 4.8
		 * @param string $context				'menu_page' | 'body_class' | 'builder_button_params'
		 * @return boolean
		 */
		public function allow_cpt_screens( $context = 'menu_page' )
		{
			$allow_cpt_screen_opt = current_theme_supports( 'avia-custom-elements-cpt-screen' ) ? avia_get_option( 'custom_el_cpt_screen' ) : '';

			/**
			 * @since 4.8
			 * @param boolean
			 * @param string $context				'menu_page' | 'admin_bar_new
			 * @return boolean
			 */
			$allow_cpt_screen = apply_filters( 'avf_custom_elements_cpt_screen', 'allow_cpt_screen' == $allow_cpt_screen_opt, $context );

			return $allow_cpt_screen;
		}


		/**
		 * Checks if shortcode is an editable element template base
		 *
		 * @since 4.8
		 * @param aviaShortcodeTemplate|array $sc
		 * @return boolean
		 */
		public function is_editable_base_element( $sc )
		{
			if( $sc instanceof aviaShortcodeTemplate )
			{
				return isset( $sc->config['base_element'] ) && 'yes' == $sc->config['base_element'];
			}
			else if( is_array( $sc ) )
			{
				return isset( $sc['base_element'] ) && 'yes' == $sc['base_element'];
			}

			return false;
		}

		/**
		 * Checks if shortcode supports an editable modal group element - and returns a possible index.
		 *
		 * @since 4.8
		 * @param aviaShortcodeTemplate $sc
		 * @param string $result					'bool' | 'index'
		 * @return boolean|int
		 */
		public function is_editable_modal_group_element( aviaShortcodeTemplate $sc, $result = 'bool' )
		{
			$return = false;

			foreach( $sc->elements as $key => &$element )
			{
				if( isset( $element['type'] ) && 'modal_group' == $element['type'] )
				{
					$return = isset( $element['editable_item'] ) && true === $element['editable_item'];
					if( $return && $result == 'index' )
					{
						$return = $key;
					}
					break;
				}
			}

			unset( $element );

			return $return;
		}

		/**
		 * Checks if looking for a locked value should be skipped.
		 * When editiong modal group subitems depending on theme option "Custom Elements For Subitems" it might be necessary not to lock options.
		 *
		 * @since 4.8
		 * @param aviaShortcodeTemplate $sc
		 * @return boolean
		 */
		public function skip_modal_popup_locked_value_check( aviaShortcodeTemplate $sc, array $templates_info )
		{
			if( ! $this->is_editable_base_element( $sc ) || ! isset( $sc->config['shortcode_nested'] ) || ! is_array( $sc->config['shortcode_nested'] ) )
			{
				return false;
			}

			if( ! isset( $templates_info['shortcode'] ) || ! in_array( $templates_info['shortcode'], $sc->config['shortcode_nested'] ) )
			{
				return false;
			}

			if( 'individually' == $this->subitem_custom_element_handling() )
			{
				return false;
			}

			if( ( isset( $_REQUEST['edit_element'] ) && ( 'true' === $_REQUEST['edit_element'] ) ) || ( isset( $_REQUEST['post_type'] ) && ( Avia_Element_Templates()->get_post_type() == $_REQUEST['post_type'] ) ) )
			{
				return true;
			}

			return false;
		}

		/**
		 * Checks if we have a custom element post type
		 *
		 * @since 4.8
		 * @param string $post_type
		 * @return boolean
		 */
		public function is_element_post_type( $post_type )
		{
			return $post_type == $this->get_post_type();
		}

		/**
		 * Checks if we are on the list table overview page for ALB Element templates
		 *
		 * @since 4.8
		 * @return boolean
		 */
		public function is_element_overview_page()
		{
			if( is_bool( $this->is_element_overview_page ) )
			{
				return $this->is_element_overview_page;
			}

			if( ! function_exists( 'get_current_screen' ) )
			{
				return false;
			}

			$this->is_element_overview_page = false;

			$screens = array(
							$this->get_post_type()
						);

			$post_type = '';

				//	WP bug: we cannot rely on QuickEdit, that screen object exists
			$screen = get_current_screen();

			if( empty( $screen ) || ( ! isset( $screen->post_type ) ) )
			{
				if( isset( $_REQUEST['action'] ) && ( 'inline-save' != $_REQUEST['action'] ) )
				{
					return $this->is_element_overview_page;
				}

				if( ! isset( $_REQUEST['post_type'] ) )
				{
					return $this->is_element_overview_page;
				}
				$post_type = $_REQUEST['post_type'];
			}
			else
			{
				$post_type = $screen->post_type;
			}

			if( ! in_array( $post_type, $screens ) )
			{
				return $this->is_element_overview_page;
			}

			$this->is_element_overview_page = true;

			return $this->is_element_overview_page;
		}

		/**
		 * Checks if we are on the edit screen for the element (new or edit).
		 *
		 * @since 4.8
		 * @return boolean
		 */
		public function is_edit_element_page()
		{
			if( is_bool( $this->is_edit_element_page ) )
			{
				return $this->is_edit_element_page;
			}

			$this->is_edit_element_page = false;

			if( ! is_admin() && ! wp_doing_ajax() )
			{
				return $this->is_edit_element_page;
			}

			if( function_exists( 'get_current_screen' ) )
			{
				$screen = get_current_screen();

				if( ! $screen instanceof WP_Screen )
				{
					return $this->is_edit_element_page;
				}

				if( $screen->base == 'post' && $screen->post_type == $this->get_post_type() )
				{
					$this->is_edit_element_page = true;
				}

				return $this->is_edit_element_page;
			}

			/**
			 * Fallback if called too early
			 * ============================
			 */
//			error_log( 'aviaElementTemplates::is_edit_element_page is called too early. Might return wrong result. Make sure to call when function get_current_screen() is available.');

			$this->is_edit_element_page = null;

			if( strpos( $_SERVER['REQUEST_URI'], 'post-new.php' ) !== false )
			{
				if( isset( $_REQUEST['post_type'] ) && $this->get_post_type() == $_REQUEST['post_type'] )
				{
					return true;
				}
			}
			else if( strpos( $_SERVER['REQUEST_URI'], 'post.php' ) !== false )
			{
				if( isset( $_REQUEST['action'] ) && 'edit' == $_REQUEST['action'] && isset( $_REQUEST['post'] ) )
				{
					$post = $this->get_post( $_REQUEST['post'] );

					if( $post instanceof WP_Post && $this->get_post_type() == $post->post_type )
					{
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Checks if we need to add custom element templates options in modal popup editor
		 *
		 * @since 4.8
		 * @param boolean
		 */
		public function popup_editor_needs_template_options()
		{
			if( ( isset( $_REQUEST['edit_element'] ) && ( 'true' === $_REQUEST['edit_element'] ) ) )
			{
				return true;
			}

			if( isset( $_REQUEST['post_type'] ) && ( Avia_Element_Templates()->get_post_type() == $_REQUEST['post_type'] ) )
			{
				return true;
			}

			return false;
		}

		/**
		 * Return filtered option how custom element templates are handled for modal group subitems
		 *
		 * @since 4.8
		 * @return string 				'first' | 'individually' | 'none'
		 */
		public function subitem_custom_element_handling()
		{
			if( ! is_null( $this->subitem_custom_element_handling ) )
			{
				return $this->subitem_custom_element_handling;
			}

			if( ! $this->element_templates_enabled() )
			{
				$this->subitem_custom_element_handling = 'individually';
				return $this->subitem_custom_element_handling;
			}

			/**
			 * @since 4.8
			 * @param string
			 * @return string		'first' | 'individually' | 'none'
			 */
			$handling = apply_filters( 'avf_custom_element_subtype_handling', avia_get_option( 'custom_el_subitem_handling' ) );

			if( empty( $handling ) || ! in_array( $handling, array( 'first', 'individually', 'none' ) ) )
			{
				$handling = 'first';
			}

			$this->subitem_custom_element_handling = $handling;

			return $this->subitem_custom_element_handling;
		}

		/**
		 * Add the container to allow edit element templates in ALB environment
		 *
		 * @since 4.8
		 * @return string
		 */
		public function add_alb_editor_content()
		{
			$output = '';

			if( $this->element_templates_enabled() && ! $this->is_edit_element_page() )
			{
				$output .=	'<div class="avia_layout_builder_custom_elements">';
				$output .=		'<div id="aviaALBCustomElements" class="avia-style avia_alb_custom_elements" data-dragdrop-level="0">';
				$output .=		'</div>';
				$output .=		'<span class="aviaALBCustomElementShortcodeHeadline">';
				$output .=			__( 'Custom Element Shortcode (only used when editing a CET in modal popup):', 'avia_framework' );
				$output .=		'</span>';
				$output .=		'<textarea id="aviaALBCleanDataCustomElements" name="aviaALBCleanDataCustomElements"></textarea>';
				$output .=	'</div>';
			}

			return $output;
		}


		/**
		 * Add tabs for managing element templates
		 *
		 * @since 4.8
		 * @param string $tabs_title
		 * @param string $tabs_content
		 * @param int $tab_count
		 * @param array $metabox_element
		 * @return void
		 */
		public function add_visual_editor_custom_element_content( &$tabs_title, &$tabs_content, &$tab_count, &$metabox_element )
		{
			if( Avia_Builder()->disable_drag_drop !== false )
			{
				return;
			}

			if( ! $this->element_templates_enabled() )
			{
				return;
			}

			//	In this case we handle templates like normal shortcode (only templates are added )
			if( $this->is_edit_element_page() )
			{
				return;
			}

			$this->editor_elements = $this->get_all_element_template_shortcode_buttons();
			$tabs = array();
			if( empty( $this->editor_elements ) )
			{
				//	Use only one tab - regardless of theme options settings
				$tabs[ $this->custom_element_tab_name ] = array();
			}
			else
			{
				$tab_order = isset( $metabox_element['tab_order'] ) ? $metabox_element['tab_order'] : array();
				$tabs = array_fill_keys( $tab_order, array() );
			}

			foreach( $this->editor_elements as $element )
			{
				if( ! $this->is_editable_base_element( $element['sc_class'] ) )
				{
					continue;
				}

				$shortcode = $element['sc_class']->config;

				if( empty( $shortcode['tinyMCE']['tiny_only'] ) )
				{
					if( ! isset( $shortcode['tab'] ) )
					{
						$shortcode['tab'] = Avia_Builder()->default_sc_btn_tab_name;
					}

					$tabs[ $shortcode['tab'] ][] = $element;
				}
			}

			$condensed = $this->condense_tabs( $tabs );
			$class_dropdown = $condensed ? 'avia-condensed' : '';

			$params = array(
							'args'	=> array( 'icon' =>  'ue86e' )
						);
			$icon = av_backend_icon( $params );
			$init_sc = 'alb';

			$shortcode_label = array(
								'alb'		=> __( 'ALB Elements:', 'avia_framework' ),
								'custom' 	=> __( 'Custom Elements:', 'avia_framework' ),
							);

			$sel_list  = '';

			$sel_list .=	'<div id="avia-select-shortcode-type-dropdown" class="avia-fake-tab avia-select-shortcode-type-dropdown-container ' . $class_dropdown . '" data-init_sc_type="' . $init_sc . '">';
			$sel_list .=		'<ul class="avia-select-shortcode-type-select">';
			$sel_list .=			'<li class="avia-select-shortcode-type-list-wrap">';
			$sel_list .=				'<strong>';
			$sel_list .=					'<span class="avia-font-entypo-fontello avia_icon_char">' . $icon['display_char'] . '</span><span class="avia-sc-type-label">'. $shortcode_label[ $init_sc ] .'</span>';
			$sel_list .=				'</strong>';
			$sel_list .=				'<ul class="avia-select-shortcode-type-list-main">';
			$sel_list .=					'<li class="avia-shortcode-type-list-element">';
			$sel_list .=						'<a href="#" class="avia-fake-tab shortcode-type-active avia-alb " data-sc_type="alb" title="' . esc_attr( __( 'Select default ALB Elements', 'avia_framework' ) ) . '">' . $shortcode_label['alb'] . '</a>';
			$sel_list .=					'</li>';
			$sel_list .=					'<li class="avia-shortcode-type-list-element">';
			$sel_list .=						'<a href="#" class="avia-fake-tab avia-custom" data-sorting="name_asc" data-sc_type="custom" title="' . esc_attr( __( 'Select Custom Elements', 'avia_framework' ) ) . '">' . $shortcode_label['custom'] . '</a>';
			$sel_list .=					'</li>';
			$sel_list .=				'</ul>';
			$sel_list .=			'</li>';
			$sel_list .=		'</ul>';
			$sel_list .=	'</div>';


			$tabs_title = $sel_list . $tabs_title;

			$first = true;

			foreach( $tabs as $key => $tab )
			{
				$tab_count ++;

				$extra = $first && ! $condensed ? 'avia-needs-margin' : '';
				$extra .= $condensed ? ' avia-condensed-tab' : '';
				$first = false;

				$tabs_title .= "<a class='avia-custom-element-tab {$extra}' href='#avia-tab-{$tab_count}'>{$key}</a>";
				$tabs_content .= "<div class='avia-tab av-custom-element-tab avia-tab-{$tab_count}' data-custom_content_name='{$key}'>";

				if( empty( $tab ) )
				{
					$tabs_content .= '</div>';
					continue;
				}

				usort( $tab, array( $this, 'sortByOrder' ) );

				$sort_order = 0;
				foreach( $tab as $element )
				{
					if( empty( $element['sc_class']->config['invisible'] ) )
					{
						$sort_order ++;
						$shortcode = $element['sc_class']->config;

						if( $element['is_item'] )
						{
							$name = $shortcode['name'];
							$shortcode['name'] = ! empty( $element['name_item'] ) ? $element['name_item'] : __( 'Item for:', 'avia_framework' ) . ' ' . $name;
							$shortcode['tooltip'] = ! empty( $element['tooltip_item'] ) ? $element['tooltip_item'] : sprintf( __( 'Single item for %s:', 'avia_framework' ), $name ) . ' ' . $shortcode['tooltip'];
						}

						if( ! empty( $element['title'] ) )
						{
							$shortcode['name'] = $element['title'];
						}

						if( ! empty( $element['desc'] ) )
						{
							$shortcode['tooltip'] = $element['desc'];
						}

						if( ! isset( $shortcode['class'] ) )
						{
							$shortcode['class'] = '';
						}

						$shortcode['class'] .= ' avia-custom-element-button';
						$shortcode['class'] .= $element['is_item'] ? ' avia-custom-element-item' : ' avia-custom-element-base';
						$shortcode['btn_data'] = array(
												'shortcode_handler'		=> $element['shortcode'],
												'base_shortcode'		=> $element['base_sc'],
												'is_item'				=> $element['is_item'] ? 'true' : 'false',
												'modal_title'			=> $element['modal_title'],
												'modal_subitem_title'	=> $element['modal_subitem_title'],
												'element_title'			=> $element['title'],
												'element_tooltip'		=> $element['desc']
											);
						$shortcode['btn_id'] = 'avia-element-btn-' . $element['id'];

						$templ_id = $shortcode['php_class'] . '_' . $element['id'];

						$tabs_content .= $this->create_shortcode_button( $shortcode, $sort_order, $templ_id, $element['id'] );
					}
				}

				$tabs_content .= '</div>';
			}

			$select_class = '';
			$initial = 'base_elements_only';

			if( in_array( $this->subitem_custom_element_handling(), array( 'first', 'none' ) ) )
			{
				$select_class = ' avia_hidden';
			}

			$tt = 'title="' . __( 'Click button, then hover over the elements to show edit action buttons for each element', 'avia_framework' ) . '"';

			$footer  = '';
			$footer .= '<div class="av-custom-element-footer av-custom-element-buttons">';
			$footer .=	'<div class="av-custom-element-button element-button-add-new button button-primary">' . esc_html( __( 'Add New Custom Element', 'avia_framework' ) ) . '</div>';
			$footer .=	'<div class="av-custom-element-button element-button-edit button button-primary" ' . $tt . '>' . esc_html( __( 'Edit Custom Elements', 'avia_framework' ) ) . '</div>';
			$footer .=	'<div class="av-custom-element-button element-button-end-edit button button-primary">' . esc_html( __( 'End Edit Custom Elements', 'avia_framework' ) ) . '</div>';
			$footer .=	'<div class="av-custom-element-select-buttons avia-form-element avia-style' . $select_class . '">';
			$footer .=		'<select name="av-filter-sc-element-types" title="' . __( 'Filter the custom element shortcode buttons for editing', 'avia_framework' ) . '" data-initial_select="' . $initial . '">';
			$footer .=			'<option value="">' . __( 'Show all elements', 'avia_framework' ) . '</option>';
			$footer .=			'<option value="base_elements_only">' . __( 'Base elements only', 'avia_framework' ) . '</option>';
			$footer .=			'<option value="item_elements_only">' . __( 'Item elements only', 'avia_framework' ) . '</option>';
			$footer .=		'</select>';
			$footer .=	'</div>';
			$footer .= '</div>';

			if( $this->current_user_can_manage() )
			{
				$tabs_content .= $footer;
			}
		}

		/**
		 * Sort the shortcode buttons
		 *
		 * @since 4.8
		 * @param array $a
		 * @param array $b
		 * @return boolean
		 */
		protected function sortByOrder( array $a, array $b )
		{
			if( empty( $a['sc_class']->config['order'] ) )
			{
				$a['sc_class']->config['order'] = 10;
			}

			if( empty( $b['sc_class']->config['order'] ) )
			{
				$b['sc_class']->config['order'] = 10;
			}

   			return $b['sc_class']->config['order'] >= $a['sc_class']->config['order'] ? 1 : -1;
		}

		/**
		 * Create JS templates
		 *
		 * @since 4.8
		 */
		public function js_template_editor_elements()
		{
			foreach( $this->editor_elements as $element )
			{
				$content = $element['info'][0]['raw_content'];
				$args = ! $element['is_item'] ? $element['info'][0]['template_attr'] : $element['info'][0]['attr'];

				$scripts = $this->js_scripts_from_array( $element['sc_class'], $args, $content, $element['id'] );

				foreach( $scripts as $script )
				{
					echo $script;
				}
			}

			$data = array(
						'modal_title'			=> __( 'Create A New Custom Element Template', 'avia_framework' ),
						'modal_button'			=> __( 'Create Element', 'avia_framework' ),
						'modal_title_update'	=> __( 'Update Custom Element Template Data', 'avia_framework' ),
						'modal_button_update'	=> __( 'Update Element', 'avia_framework' )
					);

			$data = AviaHelper::create_data_string( $data );

			echo "\n<script type='text/html' id='avia-tmpl-add-new-element-modal-content' {$data}>\n";
			echo	$this->add_new_modal_popup_content();
			echo "\n</script>\n\n";
		}

		/**
		 * Updates the element template setting to reflect the latest theme options setting in subelements.
		 * This should avoid breaking existing elements in pages if user switches options.
		 *
		 * @since 4.8
		 * @param array $elements
		 * @param array $elementValues
		 * @param aviaShortcodeTemplate $shortcode
		 */
		public function prepare_popup_subitem_elements( array &$elements, array &$elementValues, aviaShortcodeTemplate $shortcode )
		{
			if( 'individually' == $this->subitem_custom_element_handling() )
			{
				return;
			}

			$index = $this->is_editable_modal_group_element( $shortcode, 'index' );

			if( false === $index )
			{
				return;
			}

			$template_id = '';

			foreach( $elements as &$element )
			{
				$break = false;
				if( isset( $element['id'] ) && 'one_element_template' == $element['id'] )
				{
					switch( $this->subitem_custom_element_handling() )
					{
						case 'first':
							if( ! empty( $element['std'] ) )
							{
								$template_id = $element['std'];
							}
							$break = true;
							break;
						case 'none':
							$element['std'] = '';
							$break = true;
							break;
					}
				}

				if( $break )
				{
					break;
				}
			}

			unset( $element );

			$std = &$elements[ $index ]['std'];

			if( is_array( $std ) && ! empty( $std ) )
			{
				foreach( $std as &$value )
				{
					$value['element_template'] = $template_id;
				}

				unset( $value );
			}

			foreach( $elements[ $index ]['subelements'] as &$subelement )
			{
				if( isset( $subelement['id'] ) && 'element_template' == $subelement['id'] )
				{
					$subelement['std'] = $template_id;
					break;
				}
			}

			unset( $subelement );
		}

		/**
		 * Change custom element template settings when opening a modal popup depending on
		 * theme options settings for subitems
		 *
		 * @since 4.8
		 * @param string $shortcode
		 * @param array $elements
		 * @param boolean $template_changed
		 */
		public function popup_editor_adjust_subitems_settings( &$shortcode, array &$elements, $template_changed )
		{
			if( ! $this->element_templates_enabled() )
			{
				return;
			}

			if( 'individually' == $this->subitem_custom_element_handling() )
			{
				return;
			}

			//	if the ajax request told us that we are fetching the subitem we rely on a correct setting of the element_template value
			if( ! empty( $_POST['params']['subelement'] ) )
			{
				return;
			}

			//	editing from custom post type screen no need to change anything (content should be correct) - used by e.g. WPML to translate
			if( $this->is_edit_element_page() )
			{
				return;
			}

			$sc_array = $this->get_element_template_info_from_content( $shortcode );

			//	fallback
			if( ! array_key_exists( $sc_array[0]['shortcode'], Avia_Builder()->shortcode ) )
			{
				return;
			}

			$shortcode_class = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $sc_array[0]['shortcode'] ] ];

			if( ! $this->is_editable_base_element( $shortcode_class ) || ! $this->is_editable_modal_group_element( $shortcode_class ) )
			{
				return;
			}

			//	when editing subitem we can leave unchanged
			if( $sc_array[0]['shortcode'] != $sc_array[0]['template_sc'] )
			{
				return;
			}

			$new_one_template_id = '';

			if( 'first' == $this->subitem_custom_element_handling() )
			{
				if( ( isset( $_REQUEST['element_post_type'] ) && $_REQUEST['element_post_type'] == $this->get_post_type() ) || ( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == $this->get_post_type() ) )
				{
					//	editing a custom element template - no hierarchical templates allowed (hidden with CSS)
					$new_one_template_id = isset( $sc_array[0]['template_attr']['one_element_template'] ) ? $sc_array[0]['template_attr']['one_element_template'] : '';
				}
				else
				{
					//	editing an element on a page/post/....
					$this->load_template_cache( $sc_array[0]['template_attr']['element_template'] );
					$template = $this->get_template( $sc_array[0]['template_attr']['element_template'], $sc_array[0]['shortcode'] );

					if( false === $template )
					{
						$new_one_template_id = '';
					}
					else
					{
						$new_one_template_id = $template['sc_array'][0]['template_attr']['one_element_template'];
					}
				}
			}

			$sc_array[0]['template_attr']['one_element_template'] = $new_one_template_id;

			$content = array();

			if( is_array( $sc_array[0]['content'] ) )
			{
				foreach( $sc_array[0]['content'] as &$subitem )
				{
					$subitem['attr']['element_template'] = $new_one_template_id;

					$sub = $subitem['attr'];
					$sub['content'] = isset( $subitem['content'] ) ? $subitem['content'] : '';

					$content[] = $sub;
				}

				unset( $subitem );
			}

			$outer = $sc_array[0]['template_attr'];
			$outer['content'] = $content;

			$shortcode = $this->create_custom_element_shortcode( $shortcode_class, $outer, array(), false, true );
		}

		/**
		 * Creates a shortcode button for the backend canvas to add or edit a custom element
		 *
		 * @since 4.8
		 * @param array $shortcode
		 * @param int $sort_order
		 * @param string $templ_id
		 * @param int $element_id
		 * @return string
		 */
		protected function create_shortcode_button( array $shortcode, $sort_order, $templ_id, $element_id )
		{
			$edit = av_backend_icon( array( 'args' => array( 'icon' => 'ue836', 'font' => 'entypo-fontello' ) ) );
			$delete = av_backend_icon( array( 'args' => array( 'icon' => 'ue813', 'font' => 'entypo-fontello' ) ) );
			$clone = av_backend_icon( array( 'args' => array( 'icon' => 'ue83c', 'font' => 'entypo-fontello' ) ) );

			/**
			 * Add a 4th action button
			 *
			 * @used_by			avia_WPML				10
			 * @since 4.8
			 */
			$additional = apply_filters( 'avf_cet_additional_sc_action_btn', '', $element_id );

			$icons  = '';
			$icons .=	'<div class="avia-custom-elements-actions-overlay avia-font-' . $edit['font'] . '" data-element_id="' . $element_id . '" data-el_template="element_' . $templ_id . '" data-template="' . $templ_id . '">';
			$icons .=		'<div class="element-sc-action-button element-edit" title="' . esc_html__( 'Edit Custom Element', 'avia_framework' ) . '"><span>' . $edit['display_char'] . '</span></div>';
			$icons .=		'<div class="element-sc-action-button element-delete" title="' . esc_html__( 'Delete Custom Element', 'avia_framework' ) . '"><span>' . $delete['display_char'] . '</span></div>';
			$icons .=		'<div class="element-sc-action-button element-clone" title="' . esc_html__( 'Clone Custom Element', 'avia_framework' ) . '"><span>' . $clone['display_char'] . '</span></div>';
			$icons .=		$additional;
			$icons .=		'<div class="avia-sc-button-loading avia_loading"></div>';
			$icons .=	'</div>';

			$btn = Avia_Builder()->create_shortcode_button( $shortcode, $sort_order, $templ_id );

			return str_replace( '</a>', $icons . '</a>', $btn );
		}

		/**
		 * Returns a script array for "Element Template Edit" and "Use as ALB element"
		 *
		 *		template_id => script
		 *
		 * @since 4.8
		 * @param aviaShortcodeTemplate $shortcode
		 * @param array $args
		 * @param type $content
		 * @param type $element_id
		 * @return array
		 */
		protected function js_scripts_from_array( aviaShortcodeTemplate $shortcode, array $args, $content, $element_id )
		{
			$scripts = array();

			//	Create base custom element for editing
			$args[ aviaElementManager::ELEMENT_UID ] = '';

			$data = array(
					'element_post_type'	=> $this->get_post_type(),
					'element_id'		=> $element_id
				);

			$template = $shortcode->prepare_editor_element( $content, $args, true, false, $data );
			if( ! is_array( $template ) )
			{
				$id = "element_{$shortcode->config['php_class']}_{$element_id}";
				$scripts[ "#avia-tmpl-{$id}" ] = Avia_Builder()->js_template_script( $template, $id );
			}

			//	For using as an "ALB base element" we have to set template to this id
			$args['element_template'] = $element_id;

			$template = $shortcode->prepare_editor_element( $content, $args, true );
			if( ! is_array( $template ) )
			{
				$id = "{$shortcode->config['php_class']}_{$element_id}";
				$scripts[ "#avia-tmpl-{$id}" ] = Avia_Builder()->js_template_script( $template, $id );
			}

			return $scripts;
		}

		/**
		 * Prepares shortcode to create an item template and set the default value(s)
		 *
		 * @since 4.8
		 * @param aviaShortcodeTemplate $sc
		 * @param array $std					array of default settings for subitems (array of array)
		 */
		protected function set_as_item_template( aviaShortcodeTemplate $sc, array $std = array() )
		{
			if( ! $this->element_templates_enabled() )
			{
				return;
			}

			foreach( $sc->elements as &$element )
			{
				if( ! isset( $element['type'] ) || ! isset( $element['id'] ) )
				{
					continue;
				}

				if( $element['type'] == 'modal_group' )
				{
					if( ! isset( $element['std'] ) || ! is_array( $element['std'] ) )
					{
						$element['std'] = array();
					}

					if( ! empty( $std ) )
					{
						$element['std'] = $std;
					}
					else if( empty( $element['std'] ) )
					{
						$element['std'][] = $sc->get_default_modal_group_args();
					}
					else
					{
						$element['std'] = array( $element['std'][0] );
					}
				}
				else if( $element['id'] == 'select_element_template' )
				{
					$element['std'] = 'item';
				}
			}

			unset( $element );
		}

		/**
		 * Extends the predefined elements to support editable elements modal popup.
		 * This function does not make any checks. Make sure to call it only when the additional
		 * elements are needed.
		 *
		 * Element must define:
		 *
		 *		'lockable'		=> true,
		 *		'editable_el'	=> true			//	for modal_group elements to define item templates
		 *
		 * Only on edit ALB elements screen:
		 *		- Adds a checkbox below to allow disabling override
		 *		- Adds a selectbox to allow creating templates for 'modal_group' elements
		 *
		 * @since 4.8
		 * @param aviaShortcodeTemplate $sc
		 */
		public function add_element_template_options( aviaShortcodeTemplate $sc )
		{
			if( ! $this->element_templates_enabled() )
			{
				return;
			}

			$sc->elements = $this->element_template_options( $sc->elements );
		}

		/**
		 * Extends the predefined elements for subitems to support editable elements modal popup.
		 * See function aviaElementTemplates::add_element_template_options() for more details.
		 * Wrapper for aviaElementTemplates::add_subitem_element_template_options()
		 *
		 * @since 4.8
		 * @param array $sub_elements
		 * @return array
		 */
		public function add_subitem_element_template_options( array $sub_elements )
		{
			if( ! $this->element_templates_enabled() )
			{
				return $sub_elements;
			}

			return $this->element_template_options( $sub_elements, true );
		}

		/**
		 * Recursive: Scans the element array and subelement array
		 *
		 * @since 4.8
		 * @param array $elements
		 * @param boolean $subelements
		 * @return array
		 */
		protected function element_template_options( array $elements, $subelements = false )
		{
			$new_els = array();
			$hide = $subelements ? ' avia-hide-on-edit-base-template' : ' avia-hide-on-edit-item-template';

			foreach( $elements as $element )
			{
				if( ! isset( $element['container_class'] ) )
				{
					$element['container_class'] = '';
				}

				if( isset( $element['id'] ) )
				{
					if( ! isset( $element['type'] ) || $element['type'] != 'modal_group' )
					{
						$element['container_class'] .= $hide;
					}
				}

				$actions = $this->get_element_options_actions( $element );

				if( empty( $actions ) )
				{
					$new_els[] = $element;
					continue;
				}

				if( in_array( 'editable_item', $actions ) )
				{
					$subtype = array(
									__( 'Base element template', 'avia_framework' )	=> '',
									__( 'Item element template', 'avia_framework' )	=> 'item'
								);

					$select = array(
									'name'				=> __( 'Create an editable template', 'avia_framework' ),
									'desc'				=> __( 'Select what template you want to create. If you create an item template any settings for base template you make here will be ignored.', 'avia_framework' ),
									'id'				=> 'select_element_template',
									'type'				=> 'select',
									'std'				=> '',
									'container_class'	=> 'av-elements-item-select',
									'subtype'			=> $subtype
								);

					$new_els[] = $select;

					$element['container_class'] .= ' av-select-element-template';
					$element['subelements'] = $this->element_template_options( $element['subelements'], true );

					if( ! in_array( 'lockable', $actions ) )
					{
						$new_els[] = $element;
					}
				}

				if( in_array( 'lockable', $actions ) )
				{
					$desc = '';

					if( isset( $element['name'] ) && ! empty( $element['name'] ) )
					{
						$desc = $element['name'];
					}
					else if( isset( $element['desc'] ) && ! empty( $element['desc'] ) )
					{
						$desc = $element['desc'];
						
						//	currently possible for checkboxes
						if( is_array( $desc ) )
						{
							$desc = __( 'this option', 'avia_framework' );
						}
					}

					if( strlen( $desc ) > 50 )
					{
						$desc = explode( "\n", wordwrap( $desc, 50 ) );
						$desc = $desc[0] . '...';
					}

					if( ! empty( $desc ) )
					{
						$desc = '&quot;' . $desc . '&quot;';
					}

					$element['container_class'] .= ' av-lock-element-before';

					$new_els[] = $element;
					
					$desc = array(
								'checked'	=> sprintf( __( 'Unlock %s', 'avia_framework' ), $desc ),
								'unchecked'	=> sprintf( __( 'Lock %s', 'avia_framework' ), $desc )
							);

					$lock = array(
									'desc'				=> $desc,
									'id'                => $element['id'] . '__locked',
									'type'              => 'checkbox',
									'container_class'   => 'av-lock-element-checkbox av-multi-checkbox' . $hide,
									'std'               => '',
									'tooltip'			=> __( 'Do not allow to change this setting when using this template', 'avia_framework' ),
									'required'			=> isset( $element['required'] ) ? $element['required'] : array(),
									'lockable_check'	=> $element['id']
								);

					$new_els[] = $lock;
				}
			}

			return $new_els;
		}

		/**
		 * Checks for element settings
		 *
		 * @since 4.8
		 * @param array $element
		 * @return array
		 */
		protected function get_element_options_actions( array $element )
		{
			$action = array();

			if( isset( $element['lockable'] ) && $element['lockable'] === true )
			{
				$action[] = 'lockable';
			}

			if( isset( $element['editable_item'] ) && $element['editable_item'] === true )
			{
				$action[] = 'editable_item';
			}

			return $action;
		}

		/**
		 * - Assign the correct shortcode category to the post.
		 *   Creates the category, if it does not exist.
		 *
		 * @since 4.8
		 * @param string $content
		 * @param int $post_id
		 * @param array $tree
		 * @param boolean $force_update
		 */
		public function updated_post_content( $content, $post_id, array $tree, $force_update = false )
		{
			if( $force_update !== true )
			{
				if( ! isset( $_REQUEST['post_type'] ) || $_REQUEST['post_type'] != $this->get_post_type() )
				{
					return;
				}
			}

			$term_id = 0;
			$el_type = '';

			if( ! empty( $tree ) )
			{
				$shortcode = $tree[0]['tag'];
				$sc = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $shortcode ] ];
				$term = get_term_by( 'slug', $this->cached_update_sc_array[0]['template_sc'], $this->get_taxonomy() );

				if( $shortcode == $this->cached_update_sc_array[0]['template_sc'] )
				{
					$el_type = ! $this->is_editable_modal_group_element( $sc ) ? 'base_element': 'modal_base_element';
				}
				else
				{
					$el_type = 'modal_sub_element';
				}

				if( ! $term instanceof WP_Term )
				{
					if( $shortcode == $this->cached_update_sc_array[0]['template_sc'] )
					{
						$term = isset( $sc->config['name_template'] ) ? $sc->config['name_template'] : $sc->config['name'];
						$slug = $shortcode;
						$description = isset( $sc->config['tooltip_template'] ) ? $sc->config['tooltip_template'] :$sc->config['tooltip'];
					}
					else
					{
						$term .= isset( $sc->config['name_item'] ) ? $sc->config['name_item'] : $sc->config['name'] . ' ' . __( 'Item', 'avia_framework' );
						$slug = $this->cached_update_sc_array[0]['template_sc'];
						$description = isset( $sc->config['tooltip_item'] ) ? $sc->config['tooltip_item'] : __( 'Item:', 'avia_framework' ) . ' ' . $sc->config['tooltip_item'];
					}

					$args = array(
								'slug'			=> $slug,
								'description'	=> $description
							);

					$taxonomy = $this->get_taxonomy();

					$result = wp_insert_term( $term, $taxonomy, $args );

					$term_id = ( is_array( $result ) && isset( $result['term_id'] ) ) ? (int) $result['term_id'] : 0;
				}
				else
				{
					$term_id = $term->term_id;
				}
			}

			wp_set_object_terms( $post_id, $term_id, $this->get_taxonomy(), false );

			//	store info for a quick reference for lists or 3rd party plugins like WPML
			update_post_meta( $post_id, '_av_element_template_type', $el_type );
		}

		/**
		 * Returns an array of custom element templates that have a basic shortcode button
		 * (modal group shortcodes are filtered)
		 *
		 * @since 4.8
		 * @return array
		 */
		protected function get_all_element_template_shortcode_buttons()
		{
			$list = array();

			$args = array(
						'post_type'			=> $this->get_post_type(),
						'post_status'		=> array( 'publish', 'pending', 'draft', 'trash' ),
						'posts_per_page'	=> -1,
						'orderby'			=> array( 'title' => 'ASC' )
					);


			$q = new WP_Query( $args );

			$elements = $q->posts;

			if( empty( $elements ) )
			{
				return $list;
			}

			foreach( $elements as $key => $element )
			{
				$sc = trim( Avia_Builder()->get_posts_alb_content( $element->ID ) );

				if( empty( $sc ) )
				{
					continue;
				}

				$info = $this->get_element_template_info_from_content( $sc );
				$shortcode = $info[0]['template_sc'];
				$is_item = false;
				$base_sc = '';
				$modal_title = sprintf( __( ' - Customize: %s', 'avia_framework' ), $element->post_title );
				$modal_subitem_title = '';

				//	check for a subitem shortcode
				if( ! array_key_exists( $shortcode, Avia_Builder()->shortcode ) )
				{
					$is_item = true;
					$base_sc = $info[0]['shortcode'];
					$modal_title =  sprintf( __( ' - Customize Item: %s', 'avia_framework' ), $element->post_title );
					$modal_subitem_title = sprintf( __( ' - Customize: %s', 'avia_framework' ), $element->post_title );

					if( ! array_key_exists( $base_sc, Avia_Builder()->shortcode ) )
					{
						continue;
					}

					$sc_class = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $base_sc ] ];

					if( ! $this->is_editable_modal_group_element( $sc_class ) )
					{
						continue;
					}
				}
				else
				{
					$sc_class = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $shortcode ] ];
				}

				$list[] = array(
								'title'		=> $element->post_title,
								'id'		=> $element->ID,
								'desc'		=> $element->post_excerpt,
								'content'	=> $sc,
								'info'		=> $info,
								'sc_class'	=> $sc_class,
								'shortcode'	=> $shortcode,
								'is_item'	=> $is_item,
								'base_sc'	=> $base_sc,
								'modal_title'			=> $modal_title,
								'modal_subitem_title'	=> $modal_subitem_title
							);
			}

			return $list;
		}

		/**
		 * Returns the input elements for a modal popup to create a new custom element template.
		 * Can be rendered as "content" to modal popur.
		 *
		 * @since 4.8
		 * @return string
		 */
		protected function add_new_modal_popup_content()
		{
			$sc = array();
			$tooltips = array();

			//	Make sure to have same ordering as in builder canvas
			foreach( Avia_Builder()->tabs as $tab => &$tab_content )
			{
				$sc[ $tab ] = array();
			}

			unset( $tab_content );

			foreach( Avia_Builder()->shortcode_class as $shortcode )
			{
				if( ! $this->is_editable_base_element( $shortcode ) )
				{
					continue;
				}

				if( ! empty( $shortcode->config['tinyMCE']['tiny_only'] ) )
				{
					continue;
				}

				$tab = isset( $shortcode->config['tab'] ) ? $shortcode->config['tab'] : Avia_Builder()->default_sc_btn_tab_name;
				$sc[ $tab ][ $shortcode->config['name'] ] = $shortcode;
			}

			foreach( $sc as $tab => $shortcodes )
			{
				if( empty( $shortcodes ) )
				{
					unset( $sc[ $tab ] );
				}
				else
				{
					ksort( $shortcodes );
					$sc[ $tab ] = $shortcodes;
				}
			}

			$subtype = array();

			foreach( $sc as $tab => $shortcodes )
			{
				foreach( $shortcodes as $sc_name => $shortcode )
				{
					$subtype[ $tab ][ $sc_name ] = $shortcode->config['shortcode'];
					$tooltips[ $shortcode->config['shortcode'] ] = $shortcode->config['tooltip'];

					if( 'individually' != $this->subitem_custom_element_handling() )
					{
						continue;
					}

					if( $this->is_editable_modal_group_element( $shortcode ) )
					{
						$name = ! empty( $shortcode->config['name_item'] ) ? $shortcode->config['name_item'] : sprintf( __( 'Item for %s: ', 'avia_framework' ),  $shortcode->config['name'] );
						$tooltip = ! empty( $shortcode->config['tooltip_item'] ) ? $shortcode->config['tooltip_item'] : sprintf( __( 'Item for %s:', 'avia_framework' ),  $shortcode->config['name'] ) . ' ' . $shortcode->config['tooltip'];

						$subtype[ $tab ][ "--- {$name}" ] = $shortcode->config['shortcode_nested'][0] . ';' . $shortcode->config['shortcode'];
						$tooltips[ $shortcode->config['shortcode_nested'][0] ] = $tooltip;
					}
				}
			}

			$elements = array(

					array(
							'name'		=> __( 'Create a new element', 'avia_framework' ),
							'desc'		=> __( 'Select the base element for your new custom element.', 'avia_framework' ),
							'id'		=> 'add_new_element_shortcode',
							'type'		=> 'select',
							'class'		=> 'av_add_new_element_shortcode',
							'std'		=> '',
							'with_first'	=> true,
							'subtype'	=> $subtype,
							'tooltips'	=> $tooltips
						),

				);

			$elements = array_merge( $elements, $this->modal_popup_element_post_infos() );
			
			$desc  = '<br />';
			$desc .= __( 'A custom element is a new element with predefined option values based on an existing element.', 'avia_framework' ) . '<br/><br/>';
			$desc .= __( 'When creating a new custom element you can set default values for each option. You can also lock an option so it will no longer be possible to change it once you add a new instance of the new custom element to the page canvas.', 'avia_framework' ) . ' ';
			$desc .= __( 'If you edit a locked value of a custom element, it will automatically change for all elements on your site.', 'avia_framework' ) . '<br/><br/>';
			$desc .= __( 'Learn more about custom elements in the', 'avia_framework' ) . ' <a href="https://kriesi.at/documentation/enfold/custom-element-templates/" target="_blank" rel="noopener noreferrer">' . __( 'documentation', 'avia_framework' ) . '.</a>';

			$elements[] = array(
					'name'		=> __( 'What is a custom element?', 'avia_framework' ),
					'desc'		=> $desc,
					'id'		=> 'add_new_element_shortcode_heading',
					'description_class' => 'av-builder-note av-notice',
					'type'		=> 'heading',
					'std'		=> '',
				);


			$content = AviaHtmlHelper::render_multiple_elements( $elements );

			return $content;
		}

		/**
		 * Returnns the input fields bound to the element post type
		 *
		 * @since 4.8
		 * @param array $std
		 * @return array
		 */
		public function modal_popup_element_post_infos( $std = array() )
		{
			$title = isset( $std['post_title'] ) ? trin( $std['post_title'] ) : '';
			$tooltip = isset( $std['post_excerpt'] ) ? trin( $std['post_excerpt'] ) : '';


			$elements = array(
					array(
							'name'		=> __( 'Name of Element', 'avia_framework' ),
							'desc'		=> __( 'Enter a custom name for this element. At least 4 characters and limited to 30 characters. Allowed characters are Whitespace, a-zA-Z0-9-_/', 'avia_framework' ),
							'id'		=> 'base_element_title',
							'type'		=> 'input',
							'container_class' => 'avia-no-special-character-msg',
							'class'		=> 'avia-elements-check-title',
							'std'		=> $title,
							'attr'		=> ' placeholder="' . __( 'Add your Custom Element Name', 'avia_framework' ) . '" ',
							'required'	=> array( 'add_new_element_shortcode', 'not', '' )
						),

					array(
							'name'		=> __( 'Tooltip', 'avia_framework' ),
							'desc'		=> __( 'Enter a description text - will be displayed when you hover above the custom element icons in ALB or in selectboxes for custom elements', 'avia_framework' ),
							'id'		=> 'base_element_tooltip',
							'type'		=> 'textarea',
							'container_class' => 'avia-no-special-character-msg',
							'std'		=> $tooltip,
							'required'	=> array( 'add_new_element_shortcode', 'not', '' )
						)
				);

			return $elements;
		}


		/**
		 * Returns the subtype array with available element templates for modal popup select box.
		 * The current elemnt is put in ( xxx ) and will not be possible to select.
		 *
		 * @since 4.8
		 * @param aviaShortcodeTemplate $shortcode
		 * @param boolean $item_element
		 * @return array
		 */
		public function get_extended_modal_subtypes_array( aviaShortcodeTemplate $shortcode, $item_element )
		{
			$max_level = avia_get_option( 'custom_el_hierarchical_templates' ) != 'hierarchical' ? 0 : -1;

			$subtypes = array(
							__( 'No Custom Element used - all options available', 'avia_framework' )	=> '',
						);

			$tooltips = array(
							''	=> __( 'All theme options are available and can be changed', 'avia_framework' ),
						);
			
			
			//	limit query of hierarchical structure to speed up page load in frontend
			$load = true;
			if( ! is_admin() )
			{
				$load = false;
			}
			else if( defined( 'DOING_AUTOSAVE' ) && true === DOING_AUTOSAVE )
			{
				$load = false;
			}
			else if( defined( 'DOING_CRON' ) && true === DOING_CRON )
			{
				$load = false;
			}
			/**
			 * 
			 * @used_by					avia_WPML						10	
			 * 
			 * @since 4.8.1.1
			 * @param boolean $load
			 * @param aviaShortcodeTemplate $shortcode
			 * @param boolean $item_element
			 * @return boolean						true to load
			 */
			else if( true === apply_filters( 'avf_custom_element_load_modal_subtypes_array', false, $shortcode, $item_element ) )
			{
				$load = true;
			}
			else if( defined( 'DOING_AJAX' ) && true === DOING_AJAX )
			{
				//	backend actions for custom elements
				if( ! in_array( $_REQUEST['action'], array( 'avia_alb_element_check_title', 'avia_alb_element_template_cpt_actions', 'avia_alb_element_template_update_content', 'avia_alb_element_template_delete' ) ) )
				{
					if( ! isset( $_REQUEST['avia_request'] ) || $_REQUEST['avia_request'] != 'true' )
					{
						$load = false;
					}
				}
			}
			
			if( ! $load )
			{
				return array(
						'subtypes'	=> $subtypes,
						'tooltips'	=> $tooltips
					);
			}

			$terms = $shortcode->config['shortcode'];
			if( $item_element && isset( $shortcode->config['shortcode_nested'][0] ) )
			{
				$terms = $shortcode->config['shortcode_nested'][0];
			}

			$args = array(
						'post_type'		=> $this->get_post_type(),
						'post_status'	=> array( 'publish', 'pending', 'draft', 'trash' ),
						'tax_query'		=> array(
												array(
													'taxonomy' => $this->get_taxonomy(),
													'field'    => 'slug',
													'terms'    => $terms,
												)
											),
						'orderby'		=> array(
											'parent'	=> 'ASC',
											'title'		=> 'ASC'
										),
						'post_parent'	=> 0,
						'posts_per_page' => -1
					);


			$results = $this->query_modal_templates( $args, 0, 0, $max_level );


			foreach( $results as $key => $result )
			{
				$subtypes[ $result['title'] ] = $result['id'];
				$tooltips[ $result['id'] ] = $result['desc'];
			}

			return array(
						'subtypes'	=> $subtypes,
						'tooltips'	=> $tooltips
					);
		}

		/**
		 * Recursive function to indent list elements
		 *
		 * @since 4.8
		 * @param array $args
		 * @param int $parent
		 * @param int $level
		 * @param int $max_level			-1 for unlimited
		 * @return array
		 */
		protected function query_modal_templates( array $args, $parent, $level, $max_level = 0 )
		{
			$list = array();

			$args['post_parent'] = $parent;

			$q = new WP_Query( $args );

			$elements = $q->posts;

			if( empty( $elements ) )
			{
				return array();
			}

			foreach( $elements as $key => $element )
			{
				$indent = ( $level == 0 ) ? '' : str_repeat( '--', $level ) . '> ';

				$same_id = false;;
				//	check for current edited post id
				if( isset( $_REQUEST['edit_element'] ) && $_REQUEST['edit_element'] == 'true' )
				{
					$same_id = isset( $_REQUEST['element_id'] ) && $_REQUEST['element_id'] == $element->ID;
				}
				else if( isset( $_REQUEST['post_id'] ) && $_REQUEST['post_id'] == $element->ID )
				{
					$same_id = true;
				}

				if( $same_id )
				{
					$entry = array(
								'title'	=> '( ' . $indent . $element->post_title . ' )',
								'id'	=> - $element->ID,
								'desc'	=> $element->post_excerpt
							);
				}
				else
				{
					$entry = array(
								'title'	=> $indent . $element->post_title,
								'id'	=> $element->ID,
								'desc'	=> $element->post_excerpt
							);
				}

				switch( $element->post_status )
				{
					case 'publish':
						break;
					case 'pending':
						$entry['title'] .= ' - *** ' . __( 'pending review', 'avia_framework' );
						break;
					case 'draft':
						$entry['title'] .= ' - *** ' . __( 'draft', 'avia_framework' );
						break;
					case 'trash':
						$entry['title'] .= ' - *** ' . __( 'trashed', 'avia_framework' );
						break;
					default:
						$entry['title'] .= ' - *** ' . $element->post_status;
				}

				$list[] = $entry;

				if( $max_level < 0 || $level < $max_level )
				{
					$children = $this->query_modal_templates( $args, $element->ID, $level + 1 );

					if( ! empty( $children ) )
					{
						$list = array_merge( $list, $children );
					}
				}
			}

			return $list;
		}

		/**
		 * Scan shortcode for a requested template and loads all templates into local object cache.
		 * Returns the ID of the top template. Called from popup editor - needs to handle only one shortcode (basic or item)
		 *
		 * @since 4.8
		 * @param string $shortcode
		 * @return array
		 */
		public function load_shortcode_templates( $shortcode )
		{
			$info = array(
							'template_id'	=> 0,
							'shortcode'		=> '',
							'queue'			=> null
						);

			if( ! $this->element_templates_enabled() )
			{
				return $info;
			}

			$sc_array = $this->get_element_template_info_from_content( $shortcode );

			if( 0 == $sc_array[0]['template_id'] )
			{
				return $info;
			}

			$this->load_template_cache( $sc_array[0]['template_id'] );

			/**
			 * Check that template has the correct element base
			 */
			if( $sc_array[0]['template_id'] != 0 )
			{
				if( $this->get_template( $sc_array[0]['template_id'], $sc_array[0]['template_sc'] ) !== false )
				{
					$info['template_id'] = $sc_array[0]['template_id'];
					$info['shortcode'] = $sc_array[0]['template_sc'];
					$info['queue'] = $this->create_templates_queue( $info );
					return $info;
				}
			}

			return $info;
		}

		/**
		 * Returns a valid hierarchical queue of templates ( parent -> children )
		 * Can be used to iterate over the templates to find first locked element.
		 * Loads templates into cache if necessary.
		 *
		 * @since 4.8
		 * @param array $templates_info
		 * @return array
		 */
		public function create_templates_queue( array $templates_info )
		{
			$queue = array();

			if( ! $this->element_templates_enabled() )
			{
				return $queue;
			}

			while( true )
			{
				//	build queue with untranslated element templates
				$cache = $this->get_template( $templates_info['template_id'], $templates_info['shortcode'], false );
				if( false === $cache )
				{
					break;
				}

				$queue[] = $templates_info['template_id'];
				$templates_info['template_id'] = $cache['sc_array'][0]['template_id'];
			}

			return array_reverse( $queue );
		}

		/**
		 * Returns the template if the template has the correct shortcode
		 *
		 * @since 4.8
		 * @param int $template_id
		 * @param string $shortcode
		 * @param boolean $translate		set to false to check original id
		 * @return false|array
		 */
		protected function get_template( $template_id, $shortcode, $translate = true )
		{
			/**
			 * @used_by				avia_WPML					10
			 * @since 4.8
			 * @param string|int $template_id
			 * @return string|int
			 */
			$get_id = true === $translate ? apply_filters( 'avf_custom_element_template_id', $template_id ): $template_id;

			if( 0 == $get_id || ! array_key_exists( $get_id, $this->template_cache ) || false === $this->template_cache[ $get_id ] )
			{
				//	if translated and translation does not exist try to return requested element as fallback
				if( true === $translate && $get_id != $template_id )
				{
					return $this->get_template( $template_id, $shortcode, false );
				}

				return false;
			}

			$cache = $this->template_cache[ $get_id ];

			return $cache['sc_array'][0]['template_sc'] == $shortcode ? $cache : false;
		}

		/**
		 * Returns the name of the custom element template (= post_title)
		 *
		 * @since 4.8
		 * @param int $element_id
		 * @param string|false
		 */
		public function get_element_template_name( $template_id )
		{
			$this->load_template_cache( $template_id );

			if( array_key_exists( $template_id, $this->template_cache ) && false !== $this->template_cache[ $template_id ] )
			{
				if( $this->template_cache[ $template_id ]['post'] instanceof WP_Post )
				{
					return $this->template_cache[ $template_id ]['post']->post_title;
				}
			}

			return false;
		}

		/**
		 * Check in template hierarchie for locked parameter settings.
		 *  - $element['locked'] is empty  => value for $param_id is returned
		 *  - $element['locked'] 1 value   => string is returned
		 *  - $element['locked'] more      => json encoded string with array of values for each id
		 *
		 * Make sure to call this function only when the first template has the same base element.
		 *
		 * @since 4.8
		 * @param array $templates_info
		 * @param string $param_id
		 * @param array $element			by ref for performance reasons only
		 * @param string $return_content	'raw' | 'array' what to return for content
		 * @return mixed|null				null, if no locked value exists, string or json encoded string
		 */
		public function locked_value( array $templates_info, $param_id, array &$element = array(), $return_content = 'raw' )
		{
			if( ! $this->element_templates_enabled() )
			{
				return null;
			}

			if( empty( $templates_info['queue'] ) )
			{
				return null;
			}

			if( false === $this->get_template( $templates_info['template_id'], $templates_info['shortcode'] ) )
			{
				return null;
			}

			$locked_ids = isset( $element['locked'] ) ? $element['locked'] : array();

			$is_lock_checkbox = false === strpos( $param_id, '__locked' ) ? false : true;

			// iterate queue till first lock checkbox set
			foreach( $templates_info['queue'] as $id )
			{
				$templates_info['template_id'] = $id;

				if( $is_lock_checkbox )
				{
					$param_ids = $param_id;
				}
				else
				{
					$param_ids = empty( $locked_ids ) ? $param_id : $locked_ids;
				}

				$val = $this->find_locked_value( $templates_info, $param_ids, $is_lock_checkbox, $return_content );
				if( ! is_null( $val ) )
				{
					return $val;
				}
			}

			return null;
		}

		/**
		 * Check in template hierarchie for the last set value for the option (if element allows a default to copy).
		 * If parameter is locked, then the locked value is returned.
		 *
		 * Make sure to call this function only when the first template has the same base element.
		 *
		 * @since 4.8
		 * @param array $templates_info
		 * @param string $param_id
		 * @param array $element			by ref for performance reasons only
		 * @param array $locked
		 * @return mixed
		 */
		public function default_value( array $templates_info, $param_id, array &$element = array(), $locked = array() )
		{
			$value = isset( $element['std'] ) ? $element['std'] : '';

			if( ! $this->element_templates_enabled() )
			{
				return $value;
			}

			if( isset( $element['tmpl_set_default'] ) && false === $element['tmpl_set_default'] )
			{
				return $value;
			}

			//	currently we do not allow to override default for this element
			if( isset( $element['modal_group'] ) && false === $element['modal_group'] )
			{
				return $value;
			}

			if( empty( $templates_info['queue'] ) )
			{
				return $value;
			}

			if( false === $this->get_template( $templates_info['template_id'], $templates_info['shortcode'] ) )
			{
				return $value;
			}

			//	lock checkbox settings are not used as default
			if( false !== strpos( $param_id, '__locked' ) )
			{
				return $value;
			}

			if( array_key_exists( $param_id, $locked ) )
			{
				return $locked[ $param_id ];
			}


			$cache = $this->get_template( end( $templates_info['queue'] ), $templates_info['shortcode'] );

			if( false === $cache )
			{
				return $value;
			}

			$attr = $cache['sc_array'][0]['template_attr'];

			return isset( $attr[ $param_id ] ) ? $attr[ $param_id ] : '';
		}

		/**
		 * Adds the template class to the custom class. Checks first in $atts, then in $default
		 *
		 * Make sure to call this function after loading the locked values !!!
		 *
		 * @since 4.8
		 * @param array $atts
		 * @param array $meta
		 * @param array $default
		 */
		public function add_template_class( array &$meta, array &$atts, array &$default )
		{
			if( ! $this->element_templates_enabled() )
			{
				return;
			}

			if( isset( $atts['template_class'] ) && ! empty( $atts['template_class'] ) )
			{
				$meta['el_class'] .= ' ' . $atts['template_class'];
				return;
			}

			if( isset( $default['template_class'] ) && ! empty( $default['template_class'] ) )
			{
				$meta['el_class'] .= ' ' . $default['template_class'];
				return;
			}

			return;
		}

		/**
		 * Override shortcode attribute values with locked values from templates.
		 * Missing attributes are filled from $default.
		 * The list of locked attributes is returned in $locked;
		 *
		 * @since 4.8
		 * @param array $attr
		 * @param aviaShortcodeTemplate $sc_templ
		 * @param string $shortcode
		 * @param array $default
		 * @param array $locked					element ids that have been locked
		 * @param string|false $content
		 */
		public function set_locked_attributes( array &$attr, aviaShortcodeTemplate $sc_templ, $shortcode, array &$default = array(), array &$locked = array(), &$content = null )
		{
			if( ! $this->element_templates_enabled() )
			{
				return;
			}

			if( ! isset( $attr['element_template'] ) || empty( $attr['element_template'] ) )
			{
				return;
			}

			$this->load_template_cache( $attr['element_template'] );

			$templates_info = array(
										'template_id'	=> $attr['element_template'],
										'shortcode'		=> $shortcode
									);

			$templates_info['queue'] = $this->create_templates_queue( $templates_info );

			$sc_el = &$sc_templ->elements;

			if( isset( $sc_templ->config['shortcode_nested'] ) && is_array( $sc_templ->config['shortcode_nested'] ) && in_array( $shortcode, $sc_templ->config['shortcode_nested'] ) )
			{
				/**
				 * We have a modal_group element -> search for subelements
				 */
				foreach( $sc_templ->elements as &$element )
				{
					if( $element['type'] != 'modal_group' )
					{
						continue;
					}

					if( isset( $element['subelements'] ) && is_array( $element['subelements'] ) )
					{
						$sc_el = &$element['subelements'];
						break;
					}
				}

				unset( $element );
			}

			foreach( $sc_el as &$element )
			{
				if( ! isset( $element['id'] ) )
				{
					continue;
				}

				if( ! array_key_exists( $element['id'], $attr ) )
				{
					if( ! array_key_exists( $element['id'], $default ) )
					{
						$default[ $element['id'] ] = isset( $element['std'] ) ? $element['std'] : '';
					}

					$attr[ $element['id'] ] = $default[ $element['id'] ];
				}

				$val = $this->locked_value( $templates_info, $element['id'], $element, 'raw' );
				if( ! is_null( $val ) )
				{
					if( ! isset( $element['locked'] ) || empty( $element['locked'] ) )
					{
						$attr[ $element['id'] ] = $val;
						$locked[ $element['id'] ] = $val;

						if( 'content' == $element['id'] && ! is_null( $content ) )
						{
							$content = $val;
						}
					}
					else
					{
						$el_ids = is_array( $element['locked'] ) ? $element['locked'] : array( $element['locked'] );
						$val_array = count( $el_ids ) > 1 ? json_decode( $val ) : $val;

						//	$element['locked'] contains only 1 value -> string returned
						if( ! is_array( $val_array ) )
						{
							$val_array = array( $val_array );
						}

						foreach( $el_ids as $key => $el_id )
						{
							if( isset( $val_array[ $key ] ) )
							{
								$v = isset( $val_array[ $key ] ) ? $val_array[ $key ] : '';
								$attr[ $el_id ] = $v;
								$locked[ $el_id ] = $v;
							}
						}
					}
				}
			}

			unset( $element );

			// remove temporary added content again
			unset( $default['content'] );
			unset( $attr['content'] );

		}

		/**
		 * Checks in a template for locked value and returns it (checkbox "locked" selected).
		 * The lock checkbox value is also returned.
		 *
		 *
		 * @since 4.8
		 * @param array $templates_info
		 * @param string|array $param_ids
		 * @param boolean $is_lock_checkbox
		 * @param string $return_content			'raw' | 'array' what to return for content
		 * @return string|null						null, if no locked value exists | string | json encoded array
		 */
		protected function find_locked_value( array $templates_info, $param_ids, $is_lock_checkbox = false, $return_content = 'raw' )
		{
			$cache = $this->get_template( $templates_info['template_id'], $templates_info['shortcode'] );

			if( false === $cache )
			{
				return null;
			}

			if( empty( $param_ids ) )
			{
				return null;
			}

			if( ! is_array( $param_ids ) )
			{
				$param_ids = array( $param_ids );
			}

			$attr = $cache['sc_array'][0]['template_attr'];

			if( $templates_info['shortcode'] == $cache['sc_array'][0]['shortcode'] )
			{
				$raw_content = isset( $cache['sc_array'][0]['raw_content'] ) ? $cache['sc_array'][0]['raw_content'] : '';
			}
			else
			{
				$raw_content = isset( $cache['sc_array'][0]['content'][0]['raw_content'] ) ? $cache['sc_array'][0]['content'][0]['raw_content'] : '';
			}

			if( $is_lock_checkbox )
			{
				//	only first id is checked
				if( array_key_exists( $param_ids[0], $attr ) && $attr[ $param_ids[0] ] != '' )
				{
					return $attr[ $param_ids[0] ];
				}
			}
			else
			{
				//	first index must be lock_checkbox id
				$lock_param_id = $param_ids[0] . '__locked';

				if( array_key_exists( $lock_param_id, $attr ) && $attr[ $lock_param_id ] != '' )
				{
					$values = array();

					//	Options should exist in template as you have to check a lock checkbox and must save the popup - '' is only fallback
					foreach( $param_ids as $param_id )
					{
						if( 'content' == $param_id )
						{
							if( 'raw' == $return_content )
							{
								$values[] = $raw_content;
							}
							else
							{
								if( ! array_key_exists( $param_id, $attr ) )
								{
									$values[] = array();
								}
								else if( is_array( $attr[ $param_id ] ) )
								{
									$values[] = $attr[ $param_id ];
								}
								else
								{
									$values[] = array( $attr[ $param_id ] );
								}
							}
						}
						else
						{
							$values[] = array_key_exists( $param_id, $attr ) ? $attr[ $param_id ] : '';
						}
					}

					return count( $values ) > 1 ? json_encode( $values ) : $values[0];
				}
			}

			return null;
		}

		/**
		 * Return the shortcode array, in [0] needed element template id is set for quick reference
		 * and the attributes from shortcode
		 * Also extracts info in case first modal group should be used as template for all other group elements
		 *
		 * @since 4.8
		 * @param string $shortcode			shortcode string
		 * @return array
		 */
		public function get_element_template_info_from_content( $shortcode )
		{
			$template_id = 0;
			$template_id_first = 0;
			$sc = '';
			$sc_first = '';
			$is_item = false;
			$is_first_item = false;

			$sc_array = ShortcodeHelper::shortcode2array( wp_unslash( $shortcode ) );

			//	fix if shortcode has no attributes (returns string)
			if( ! is_array( $sc_array[0]['attr'] ) )
			{
				$sc_array[0]['attr'] = array();
			}


			if( isset( $sc_array[0]['attr'] )  && is_array( $sc_array[0]['attr'] ) )
			{
				if( array_key_exists( 'element_template', $sc_array[0]['attr'] ) && is_numeric( $sc_array[0]['attr']['element_template'] ) )
				{
					$template_id = (int) $sc_array[0]['attr']['element_template'];
				}

				$sc = $sc_array[0]['shortcode'];

				//	check for item template
				if( array_key_exists( 'select_element_template', $sc_array[0]['attr'] ) && ( 'item' == $sc_array[0]['attr']['select_element_template'] ) )
				{
					$template_id = 0;

					if( isset( $sc_array[0]['content'][0]['attr']['element_template'] ) && is_numeric( $sc_array[0]['content'][0]['attr']['element_template'] ) )
					{
						$template_id = (int) $sc_array[0]['content'][0]['attr']['element_template'];
					}

					$sc = $sc_array[0]['content'][0]['shortcode'];
					$is_item = true;
				}

				//	check for first template base for all
				if( array_key_exists( 'select_element_template', $sc_array[0]['attr'] ) && ( 'first' == $sc_array[0]['attr']['select_element_template'] ) )
				{
					$template_id_first = 0;

					if( isset( $sc_array[0]['content'][0]['attr']['element_template'] ) && is_numeric( $sc_array[0]['content'][0]['attr']['element_template'] ) )
					{
						$template_id_first = (int) $sc_array[0]['content'][0]['attr']['element_template'];
					}

					$sc_first = $sc_array[0]['content'][0]['shortcode'];
					$is_first_item = true;
				}
			}

			$sc_array[0]['template_id'] = $template_id;
			$sc_array[0]['template_sc'] = $sc;

			$sc_array[0]['template_id_first'] = $is_first_item ? $template_id_first : null;
			$sc_array[0]['template_sc_first'] = $is_first_item ? $sc_first : null;

			if( ! $is_item )
			{
				$sc_array[0]['template_attr'] = $sc_array[0]['attr'];
				$sc_array[0]['template_attr']['content'] = $sc_array[0]['content'];
			}
			else
			{
				$sc_array[0]['template_attr'] = $sc_array[0]['content'][0]['attr'];
				$sc_array[0]['template_attr']['content'] = $sc_array[0]['content'][0]['content'];
			}

			if( $is_first_item )
			{
				$sc_array[0]['template_attr_first'] = $sc_array[0]['content'][0]['attr'];
				$sc_array[0]['template_attr_first']['content'] = $sc_array[0]['content'][0]['content'];
			}

			return $sc_array;
		}

		/**
		 * Recursive function - load ALB Element Templates in cache and make sure that all referenced
		 * parent templates are loaded as well. There is no check, if the parent template has the same
		 * base element (could have been changed).
		 *
		 * It is recommended to call this function whenever you try to access templates.
		 *
		 * @since 4.8
		 * @param int $template_id
		 * @param boolean $force_original			force to load original untranslated element template when using translation plugins like WPML
		 */
		protected function load_template_cache( $template_id, $force_original = false )
		{
			if( 0 == $template_id || empty( $template_id ) || ! is_numeric( $template_id ) )
			{
				return;
			}

			if( array_key_exists( $template_id, $this->template_cache ) )
			{
				//	$template_id points to a deleted or non alb_elements post ( = error condition !! )
				if( false === $this->template_cache[ $template_id ] )
				{
					return;
				}

				//	break a possible infinite loop (error situation)
				if( $this->template_cache[ $template_id ]['post']->post_parent == $template_id )
				{
					if( defined( 'WP_DEBUG' ) && WP_DEBUG )
					{
						error_log( 'error: infinite loop stopped in aviaElementTemplates::load_template_cache post ID:' . $template_id );
					}

					$this->template_cache[ $template_id ]['post']->post_parent = 0;
					return;
				}

				$this->load_template_cache( $this->template_cache[ $template_id ]['post']->post_parent );
				return;
			}

			$post = $this->get_post( $template_id, $force_original );

			if( ! $post instanceof WP_Post )
			{
				$this->template_cache[ $template_id ] = false;
				return;
			}

			if( $post->post_type != $this->get_post_type() || $post->post_status != 'publish' )
			{
				$this->template_cache[ $post->ID ] = false;
				return;
			}

			$this->template_cache[ $post->ID ]['post'] = $post;
			$this->template_cache[ $post->ID ]['sc_array'] = $this->get_element_template_info_from_content( Avia_Builder()->get_posts_alb_content( $post->ID ) );

			$this->load_template_cache( $this->template_cache[ $post->ID ]['post']->post_parent );

			//	WPML returns translated CET - but we might need original also
			if( $post->ID != $template_id )
			{
				$this->load_template_cache( $template_id, true );
			}
		}

		/**
		 * Limit allowed characters for element title and length
		 *
		 * @since 4.8
		 * @param string $title
		 * @param int $length
		 * @return string
		 */
		protected function sanitize_element_title( $title = '', $length = 30 )
		{
			$title_new = AviaHelper::save_string( trim( $title ), ' ', '', 'element_title' );

			if( strlen( $title_new ) > $length )
			{
				$title_new = substr( $title_new, 0, $length );
			}

			return $title_new;
		}

		/**
		 * Get ALB Layout Builder Metabox title
		 *
		 * @since 4.8
		 * @return string
		 */
		public function alb_metabox_title()
		{
			if( strpos( $_SERVER['REQUEST_URI'], 'post-new.php' ) !== false )
			{
				return ' - ' .  __( 'Create New Element Template', 'avia_framework' );
			}

			if( ! isset( $_REQUEST['post'] ) )
			{
				return '';
			}

			$post = $this->get_post( $_REQUEST['post'] );

			if( ! $post instanceof WP_Post || $post->post_type != $this->get_post_type() )
			{
				return '';
			}

			$terms = get_the_terms( $post->ID, $this->get_taxonomy() );
			if( ! is_array( $terms ) )
			{
				return '';
			}

			$title = array();

			//	Should only return 1 element
			foreach( $terms as $term )
			{
				$title[] = $term->name;
			}

			$prefix = __( 'Edit Element Template:', 'avia_framework' );

			/**
			 * @used_by					avia_WPML					10
			 * @since 4.8
			 * @param string $prefix
			 * @return string
			 */
			$prefix = apply_filters( 'avf_alb_metabox_title_prefix_cet', $prefix );

			$title = $prefix . ' ' . implode( ', ', $title );

			$sc_array = $this->get_element_template_info_from_content( Avia_Builder()->get_posts_alb_content( $post->ID ) );

			if( array_key_exists( 'select_element_template', $sc_array[0]['attr'] ) && ( 'item' == $sc_array[0]['attr']['select_element_template'] ) )
			{
				$shortcode = $sc_array[0]['shortcode'];

				$sc = Avia_Builder()->shortcode_class[ Avia_Builder()->shortcode[ $shortcode ] ];

				$title .= ' (' . $sc->config['name'] . ')';
			}

			return ' - ' . $title;
		}

		/**
		 * Wrapper function for default WP function get_post that is not hooked by e.g. WPML
		 *
		 * @since 4.8
		 * @param int $post_id
		 * @param boolean $force_original			force to load requested ID and not a translated
		 * @return WP_Post|false
		 */
		protected function get_post( $post_id, $force_original = false )
		{
			$args = array(
						'numberposts'		=> 1,
						'include'			=> array( $post_id ),
						'post_type'			=> $this->get_post_type(),
						'suppress_filters'	=> false
					);

			/**
			 * Allows e.g. WPML to reroute to translated object
			 */
			if( false === $force_original )
			{
				$posts = get_posts( $args );
				$post = is_array( $posts ) && count( $posts ) > 0 ? $posts[0] : false;
			}
			else
			{
				$post = get_post( $post_id );
			}

			return $post instanceof WP_Post ? $post : false;
		}

		/**
		 * Creates a custom element template shortcode based on the attr arrays.
		 * Settings can e.g. come from an existing ALB element edited in canvas.
		 * Locked values override the default settings.
		 * Element templates are reset to "Base Element" when $skip_locking === false (= we create a new independent custom element)
		 *
		 * @since 4.8
		 * @param aviaShortcodeTemplate $shortcode
		 * @param array $attr
		 * @param array $outer_attr
		 * @param boolean $is_item
		 * @param boolean $skip_locking			in case of update locking overrides updated values !!!
		 * @return string
		 */
		protected function create_custom_element_shortcode( aviaShortcodeTemplate $shortcode, array $attr, array $outer_attr, $is_item, $skip_locking = false )
		{
			$item_self_closing = $shortcode->has_modal_group_template() && $shortcode->is_nested_self_closing( $shortcode->config['shortcode_nested'][0] );

			if( ! $is_item )
			{
				$content = '';

				if( isset( $attr['content'] ) )
				{
					if( ! is_array( $attr['content'] ) )
					{
						$content = $attr['content'];
					}
					else
					{
						$entries = array();

						//	content can be array of shortcode strings or array of attributes array
						foreach( $attr['content'] as $entry )
						{
							if( ! is_array( $entry ) )
							{
								$entries[] = $entry;
								continue;
							}

							//	create shortcode strings so we can get locked content
							$item_content = '';

							if( isset( $entry['content'] ) )
							{
								$item_content = $entry['content'];
								unset( $entry['content'] );
							}

							if( $item_self_closing )
							{
								$item_content = null;
							}

							$entries[] = ShortcodeHelper::create_shortcode_by_array( $shortcode->config['shortcode_nested'][0], $item_content, $entry );
						}

						$content = implode( "\n", $entries );
					}
				}

				if( ! $skip_locking )
				{
					$default = $shortcode->get_default_sc_args();
					$locked = array();
					$this->set_locked_attributes( $attr, $shortcode, $shortcode->config['shortcode'], $default, $locked, $content );

					//	reset to base template
					$attr['element_template'] = '';

					if( $this->is_editable_modal_group_element( $shortcode ) )
					{
						$items = ShortcodeHelper::shortcode2array( $content, 1 );
						$item_def = $shortcode->get_default_modal_group_args();
						$item_attr = array();

						foreach( $items as &$item )
						{
							Avia_Element_Templates()->set_locked_attributes( $item['attr'], $shortcode, $shortcode->config['shortcode_nested'][0], $item_def, $locked, $item['content'] );

							$item['attr']['content'] = $item['content'];

							//	reset to base template
							$item['attr']['element_template'] = '';

							$item_attr[] = $item['attr'];
						}

						unset( $item );

						$attr['content'] = $item_attr;
						$attr['select_element_template'] = '';
					}
					else
					{
						$attr['content'] = $content;
					}
				}
				else
				{
					$attr['content'] = $content;
				}
			}
			else
			{
				if( ! $skip_locking )
				{
					$content = isset( $attr['content'] ) ? $attr['content'] : '';
					$default = $shortcode->get_default_modal_group_args();
					$locked = array();

					Avia_Element_Templates()->set_locked_attributes( $attr, $shortcode, $shortcode->config['shortcode_nested'][0], $default, $locked, $content );

					$attr['content'] = $content;

					//	reset to base template
					$attr['element_template'] = '';
				}

				$outer_attr['content'] = array( $attr );
				$outer_attr['select_element_template'] = 'item';

				$attr = $outer_attr;
			}

			$result = ShortcodeHelper::create_shortcode_from_attributes_array( $shortcode, $attr );

			//	always clear unique id's for custom elements
			$result = Avia_Builder()->element_manager()->clear_element_ids_in_content( $result );

			return $result;
		}

		/**
		 * Checks if we need to use 1 tab only for all custom element templates.
		 * In case we have 1 empty tab only we return this as condensed.
		 *
		 * @since 4.8
		 * @param array $tabs
		 * @return boolean
		 */
		protected function condense_tabs( array &$tabs )
		{
			$with_content = array();
			$no_content = array();

			foreach( $tabs as $key => $buttons )
			{
				if( ! empty( $buttons ) )
				{
					$with_content[] = $key;
				}
				else
				{
					$no_content[] = $key;
				}
			}

			switch( count( $with_content ) )
			{
				case 0:
					$tabs = array( $this->custom_element_tab_name => array() );
					return true;
				case 1:
					$tabs = array( $this->custom_element_tab_name => $tabs[ $with_content[0] ] );
					return true;
			}

			foreach( $no_content as $key )
			{
				unset( $tabs[ $key ] );
			}

			if( 'group' == avia_get_option( 'custom_el_shortcode_buttons' ) )
			{
				return false;
			}

			/**
			 * Maximum number of custom element shortcode buttons to use one tab only
			 *
			 * @since 4.8
			 */
			$limit = apply_filters( 'avf_max_custom_elements_one_tab_only', 300 );

			$count = 0;
			foreach( $tabs as $key => $buttons )
			{
				$count += count( $buttons );
			}

			if( $count >= $limit )
			{
				return false;
			}

			$condensed = array();

			foreach( $tabs as $key => $buttons )
			{
				$condensed = array_merge( $condensed, $buttons );
			}

			$tabs = array( $this->custom_element_tab_name => $condensed );

			return true;
		}
	}

	/**
	 * Returns the main instance of aviaElementTemplates to prevent the need to use globals
	 *
	 * @since 4.8
	 * @return aviaElementTemplates
	 */
	function Avia_Element_Templates()
	{
		return aviaElementTemplates::instance();
	}

	/**
	 * Activate filter and action hooks
	 */
	Avia_Element_Templates();
}
