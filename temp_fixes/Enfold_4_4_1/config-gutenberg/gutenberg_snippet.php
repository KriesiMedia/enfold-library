<?php
/* 
 * Copy the following at the end of functions.php of child theme or parent theme.
 * 
 * Can be removed when directory enfold/config-gutenberg exists after updating the theme.
 */


if( ! class_exists( 'Avia_Gutenberg' ) && defined( 'GUTENBERG_VERSION' ) && is_admin() )
{
	
	class Avia_Gutenberg 
	{
		/**
		 * Holds the instance of this class
		 * 
		 * @since 4.4.2
		 * @var AviaBuilder 
		 */
		static private $_instance = null;
		
		
		/**
		 * Stores the link target for page/post/... 
		 * Target can be:		'classic-editor' | 'gutenberg'
		 * 
		 * @since 4.4.2
		 * @var array			keys:	'page'|'post'   
		 */
		protected $edit_link_target;
		
		
		/**
		 * Return the instance of this class
		 * 
		 * @since 4.4.2
		 * @return Avia_Gutenberg
		 */
		static public function instance()
		{
			if( is_null( Avia_Gutenberg::$_instance ) )
			{
				Avia_Gutenberg::$_instance = new Avia_Gutenberg();
			}
			
			return Avia_Gutenberg::$_instance;
		}
		
		/**
		 * Initializes plugin variables and sets up WordPress hooks/actions.
		 * 
		 * @since 4.4.2
		 */
		protected function __construct() 
		{
			$this->edit_link_target = array();
			
			/**
			 * Default link filters - we change them a little
			 */
			remove_action( 'admin_init', 'gutenberg_add_edit_link_filters' );
			
			add_filter( 'page_row_actions', array( $this, 'handler_add_edit_link' ), 10, 2 );
			add_filter( 'post_row_actions', array( $this, 'handler_add_edit_link' ), 10, 2 );
			add_filter( 'get_edit_post_link', array( $this, 'handler_edit_post_link' ), 999, 3 );
			
			/**
			 * Adjust admin bar links frontend and backend. We hook after theme handler.
			 */
			add_action( 'admin_bar_menu', array( $this, 'handler_admin_bar_menu' ), 999, 1 );
		}
		
		/**
		 * @since 4.4.2
		 */
		public function __destruct() 
		{
			unset( $this->edit_link_target );
		}
		
		
		/**
		 * Returns the link target for a given post - defaults to 'classic-editor'
		 * 
		 * @since 4.4.2
		 * @param WP_Post $post
		 * @return string		'classic-editor' | 'gutenberg'
		 */
		protected function get_edit_link_target( WP_Post $post )
		{
			if( empty( $this->edit_link_target ) )
			{
				/**
				 * This might be filled by an option in future releases
				 */
				$this->edit_link_target = array(
									'page'		=>	'classic-editor',
									'post'		=>	'classic-editor',
									'portfolio'	=>	'classic-editor'
								);
				
				/**
				 * @since 4.4.2
				 */
				$this->edit_link_target = apply_filters( 'avf_gutenberg_edit_post_link_target', $this->edit_link_target );
			}
			
			return ( isset( $this->edit_link_target[ $post->post_type ] ) ) ? $this->edit_link_target[ $post->post_type ] : 'classic-editor';
		}

		/**
		 * Registers an additional link in the post/page screens to edit any post/page in
		 * the Classic editor.
		 * 
		 * Modified function gutenberg_add_edit_link( $actions, $post ) 
		 * 
		 * @since 4.4.2
		 * @param array $actions	
		 * @param WP_Post $post
		 * @return array
		 */
		public function handler_add_edit_link( $actions, $post )
		{
			if ( ! gutenberg_can_edit_post( $post ) ) 
			{
				return $actions;
			}
			
			$edit_url = get_edit_post_link( $post->ID, 'av_gutenberg' );
			$classic_url = add_query_arg( 'classic-editor', '', $edit_url );
			
			$title = _draft_or_post_title( $post->ID );
			
			$classic_action = array(
						'edit' => sprintf(
										'<a href="%s" aria-label="%s">%s</a>',
										esc_url( $classic_url ),
										esc_attr( sprintf(
												/* translators: %s: post title */
												__( 'Edit &#8220;%s&#8221; in the Classic Editor and/or Advanced Layout Builder', 'avia_framework' ),
												$title
											) ),
										__( 'Classic Editor and Advanced Layout Builder', 'avia_framework' )
								),
						);
			
			$gutenberg_action = array(
						'classic' => sprintf(
										'<a href="%s" aria-label="%s">%s</a>',
										esc_url( $edit_url ),
										esc_attr( sprintf(
												/* translators: %s: post title */
												__( 'Edit &#8220;%s&#8221; in the Gutenberg editor', 'avia_framework' ),
												$title
											) ),
										__( 'Gutenberg Editor', 'avia_framework' )
								),
						);
			
			/**
			 * Filter the actions
			 * 
			 * @since 4.4.2
			 */
			$classic_action = apply_filters( 'avf_gutenberg_edit_post_action', $classic_action, $actions, $post );
			$gutenberg_action = apply_filters( 'avf_gutenberg_edit_post_action', $gutenberg_action, $actions, $post );
			
			/**
			 * Replace the standard edit action
			 */
			$actions['edit'] = $classic_action['edit'];
			
			/**
			 * Insert Gutenberg action after the Classic Edit action.
			 */
			$edit_offset = array_search( 'edit', array_keys( $actions ), true );
			$actions = array_merge(
							array_slice( $actions, 0, $edit_offset + 1 ),
							$gutenberg_action,
							array_slice( $actions, $edit_offset + 1 )
						);

			return $actions;
		}
		
		
		/**
		 * Change edit post link to selected target
		 * 
		 * @since 4.4.2
		 * @param string $link
		 * @param int $id
		 * @param string $context
		 * @return string
		 */
		public function handler_edit_post_link( $link, $id, $context )
		{
			$post = get_post( $id );
			if( ! $post instanceof WP_Post || ( 'av_gutenberg' == $context ) )
			{
				return $link;
			}
			
			
			
			$target = $this->get_edit_link_target( $post );
			
			if( 'classic-editor' == $target )
			{
				$link = add_query_arg( 'classic-editor', '', $link );
			}
			
			return $link;
		}
		
		
		/**
		 * Adjust admin bar for classic editor. We hook after theme handler.
		 * 
		 * @since 4.4.2
		 * @param WP_Admin_Bar $wp_admin_bar		(passed by reference)
		 * @return WP_Admin_Bar
		 */
		public function handler_admin_bar_menu( WP_Admin_Bar $wp_admin_bar )
		{
				
			if( ! current_user_can( 'manage_options' ) ) 
			{
				return;
			}
			
			/**
			 * Adjust "Edit Page" link in frontend
			 */
			if( ! is_admin() )
			{
				$viewed_id = avia_get_the_ID();
				$set_front_id = avia_get_option( 'frontpage' );
				$post = get_post( $viewed_id );
				
				if( $post instanceof WP_Post )
				{
					/**
					 * If the page/post/... does not contain gutenberg we must create a link to classic editor
					 */
					$is_gutenberg = gutenberg_post_has_blocks( $post );
					$is_alb = ( Avia_Builder()->get_alb_builder_status( $viewed_id ) == 'active' );

					$edit_url = get_edit_post_link( $post->ID, 'av_gutenberg' );
					
					if( ! $is_gutenberg )
					{
						$edit_url = add_query_arg( 'classic-editor', '', $edit_url );
					}

					if( is_front_page() &&  ( $viewed_id == $set_front_id ) )
					{
						if( $is_gutenberg )
						{
							$title = __( 'Edit Frontpage ( Gutenberg )', 'avia_framework' );
						}
						else if( $is_alb )
						{
							$title = __( 'Edit Frontpage ( Advanced Layout Builder )', 'avia_framework' );
						}
						else
						{
							$title = __( 'Edit Frontpage ( Classic editor )', 'avia_framework' );
						}
						
						$menu = array(
									'id'	=> 'edit',
									'title'	=> $title,
									'href'	=> $edit_url,
									'meta'	=> array( 'target' => 'blank' )
								);

						$wp_admin_bar->add_menu( $menu );
					}
					else
					{
						$obj = get_post_type_object( $post->post_type );
						
						if( $is_gutenberg )
						{
							$title = sprintf( __( 'Edit %s ( Gutenberg )', 'avia_framework' ), $obj->labels->singular_name );
						}
						else if( $is_alb )
						{
							$title = sprintf( __( 'Edit %s ( Advanced Layout Builder )', 'avia_framework' ), $obj->labels->singular_name );
						}
						else
						{
							$title = sprintf( __( 'Edit %s ( Classic editor )', 'avia_framework' ), $obj->labels->singular_name );
						}
						
						$menu = array(
									'id'	=> 'edit',
									'title'	=> $title,
									'href'	=> $edit_url,
									'meta'	=> array( 'target' => 'blank' )
								);

						$wp_admin_bar->add_menu( $menu );
					}
				}
			}
	
			/**
			 * Adjust the "New" dropdown
			 */
			$nodes = $wp_admin_bar->get_nodes();
			
			$new_nodes = array();
	
			foreach( $nodes as $key => $node ) 
			{
				if( 0 !== strpos( $key, 'new-' ) )
				{
					continue;
				}
				
				if( 'new-content' == $key )
				{
					continue;
				}
				
				$post_type = str_replace( 'new-', '', $key );
				
				$wp_admin_bar->remove_node( $key );
				
				if ( ! gutenberg_can_edit_post_type( $post_type ) )
				{
					$new_nodes[] = $node;
					continue;
				}
				
				$classic = clone $node;

				$node->title .= ' ( ' . __( 'Gutenberg', 'avia_framework' ) . ' )';
				$new_nodes[] = $node;
				
				$classic->id .= '-classic';
				$classic->title .= ' ( ' . __( 'Classic editor/Advanced Layout Builder', 'avia_framework' ) . ' )';
				$classic->href = add_query_arg( 'classic-editor', '', $classic->href );
				$new_nodes[] = $classic;
			}
			
			/**
			 * Save reordered menus
			 */
			foreach( $new_nodes as $key => $node ) 
			{
				$wp_admin_bar->add_menu( $node );
			}
		}
		
	}
	
	/**
	 * Returns the main instance of Avia_Gutenberg to prevent the need to use globals
	 * 
	 * @since 4.4.2
	 * @return AviaBuilder
	 */
	function AviaGutenberg()
	{
		return Avia_Gutenberg::instance();
	}
	
	/**
	 * Activate class
	 */
	AviaGutenberg();
	
}	//	end ! class_exists( 'Avia_Config_LayerSlider' )

