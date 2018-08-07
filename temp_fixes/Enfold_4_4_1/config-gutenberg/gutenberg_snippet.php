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
		public function __construct() 
		{
			$this->edit_link_target = array();
			
			/**
			 * Default link filters - we change them a little
			 */
			remove_action( 'admin_init', 'gutenberg_add_edit_link_filters' );
			
			
			add_filter( 'page_row_actions', array( $this, 'handler_add_edit_link' ), 10, 2 );
			add_filter( 'post_row_actions', array( $this, 'handler_add_edit_link' ), 10, 2 );
			add_filter('get_edit_post_link', array( $this, 'handler_edit_post_link' ), 999, 3 );
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
				$this->edit_link_target = apply_filters( 'avf_edit_post_link_target', $this->edit_link_target );
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
			
			$edit_url = get_edit_post_link( $post->ID, 'raw' );
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
			if( ! $post instanceof WP_Post || ( 'raw' == $context ) )
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

