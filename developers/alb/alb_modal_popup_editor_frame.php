<?php
/* 
 * This file provides a frame to implement the modal ALB popup editor with the new toggle sectons.
 * Elements inside are only examples.
 * 
 * This in sot a working class !!!
 * 
 * IMPORTANT:
 * ==========
 * 
 * Add the following code to shortcode_insert_button() or constructor
 * 
 *		$this->config['version']		= '1.0';
 * 
 * @since 4.6.4
 * 
 * 
 * Use action 'ava_popup_register_dynamic_templates' to register additional templates needed by several of your ALB elements to avoid checking if a template exists.
 * To override a core template register one with the same name.
 */

abstract class frame_class extends aviaShortcodeTemplate
{
	
		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @return void
		 */
		function popup_elements()
		{
			
			$this->elements = array(
						
				array(
						'type' 	=> 'tab_container', 
						'nodescription' => true
					),
						
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content' , 'avia_framework' ),
						'nodescription' => true
					),
				
//	Access global stored custom templates:
//	======================================
//	
//					array(
//							'type' 	=> 'template',
//							'template_id'	=> 'toggle_container',
//							'templates_include'	=> array( 
//													$this->popup_key( 'group_layout_link' ),
//													$this->popup_key( 'group_layout_text' ),
//												),
//							'nodescription' => true
//						),
//				
//				
//	Access local stored custom templates:
//	=====================================
//	
//						array(
//								'type' 	=> 'template',
//								'template_id'	=> 'toggle',
//								'title'			=> __( 'Animation' , 'avia_framework' ),
//								'content'		=> $this->popup_templates['advanced_animation'],
//								'nodescription' => true
//							),
//				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Layout' , 'avia_framework' ),
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Styling' , 'avia_framework' ),
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced' , 'avia_framework' ),
						'nodescription' => true
					),
				
					array(
							'type' 	=> 'toggle_container',
							'nodescription' => true
						),
				
						array(	
								'type'			=> 'template',
								'template_id'	=> 'screen_options_toggle'
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
			
			$this->register_modal_group_templates();
			
			/**
			 * Content Tab
			 * ===========
			 */
			
			/**
			 * Create a global stored dynamic template
			 */
			$c = array(
						array(
							'name'	=> __( 'Add/Edit List items', 'avia_framework' ),
							'desc'	=> __( 'Here you can add, remove and edit the items of your item list.', 'avia_framework' ),
							'type'	=> 'modal_group',
							'id'	=> 'content',
							'modal_title'	=> __( 'Edit List Item', 'avia_framework' ),
							'std'	=> array(
											array(
												'title'		=> __( 'List Title 1', 'avia_framework' ), 
												'icon'		=> '43', 
												'content'	=> __( 'Enter content here', 'avia_framework' )
											),
											array(
												'title'		=> __( 'List Title 2', 'avia_framework' ), 
												'icon'		=> '25', 
												'content'	=> __( 'Enter content here', 'avia_framework' )
											),
											array(
												'title'		=> __( 'List Title 3', 'avia_framework' ), 
												'icon'		=> '64', 
												'content'	=> __( 'Enter content here', 'avia_framework' )
											),
										),

							'subelements'	=> $this->create_item_modal()
						)
				);
			
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Select Icon', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_iconfont' ), $template );
			
			
			/**
			 * Create a local stored dynamic template - only available within the shortcode
			 */
			
			$c = array(
						array(
							'name' 	=> __( 'Animation', 'avia_framework' ),
							'desc' 	=> __( 'Should the icons appear in an animated way?', 'avia_framework' ),
							'id' 	=> 'animation',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype' => array(
											__( 'Animation activated', 'avia_framework' )	=> '',
											__( 'Animation deactivated', 'avia_framework' )	=> 'deactivated',
										)
						),
				);
			
			$this->popup_templates['advanced_animation'] = $c;
		}
		
		/**
		 * Creates the modal popup for a single timeline entry
		 * 
		 * @since 4.6.4
		 * @return array
		 */
		protected function create_item_modal()
		{
			$elements = array(
				
				array(
						'type' 	=> 'tab_container', 
						'nodescription' => true
					),
						
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content' , 'avia_framework' ),
						'nodescription' => true
					),
				
//					array(
//							'type' 	=> 'template',
//							'template_id'	=> 'toggle_container',
//							'templates_include'	=> array( 
//													$this->popup_key( 'group_layout_link' ),
//													$this->popup_key( 'group_layout_text' ),
//												),
//							'nodescription' => true
//						),
				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Layout' , 'avia_framework' ),
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Styling' , 'avia_framework' ),
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced' , 'avia_framework' ),
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)
				
				);
			
			return $elements;
		}
		
		/**
		 * Register all templates for the modal group popup
		 * 
		 * @since 4.6.4
		 */
		protected function register_modal_group_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */
			$c = array(
						array(
							'name'  => __( 'Font Icon', 'avia_framework' ),
							'desc'  => __( 'Select an Icon below', 'avia_framework' ),
							'id'    => 'icon',
							'type'  => 'iconfont',
							'std'   => ''
						),
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Select Icon', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_iconfont' ), $template );
			
		}

	
	
	
}
