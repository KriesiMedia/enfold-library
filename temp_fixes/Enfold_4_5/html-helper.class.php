<?php
/**
* Central Template builder class
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if ( ! class_exists( 'AviaHtmlHelper' ) ) 
{

	class AviaHtmlHelper 
	{
		static $metaData 		= array(); //required for meta key storing when creating a metabox. necessary to set already stored std values
		static $elementValues 	= array(); //all element values in an id=>value array so we can check dependencies
		static $elementHidden 	= array(); //all elements that didnt pass the dependency test and are hidden
		static $imageCount      = 0; //count all image elements to assign the right attachment id
		static $cache      		= array(); //cache for entries that are already fetched
		
		static function render_metabox($element)
		{
			//query the metadata of the current post and check if a key is set, if not set the default value to the standard value, otherwise to the key value
			if(!isset(self::$metaData[$element['current_post']]))
			{
				self::$metaData[$element['current_post']] = get_post_custom($element['current_post']);
			}
			
			if(isset(self::$metaData[$element['current_post']][$element['id']]))
			{
				$element['std'] = self::$metaData[$element['current_post']][$element['id']][0];
			}
			
			return self::render_element($element);
		}
		
		
		
		static function render_multiple_elements($elements, $parent_class = false)
		{
			$output = "";
			
			foreach ($elements as $element)
			{
				$output .= self::render_element($element, $parent_class);
			}
			
			return $output;
		}
		
		static function ajax_modify_id($element)
		{
			// check if its an ajax request. if so prepend a string to ensure that the ids are unique. 
			// If there are multiple modal windows called prepend the string multiple times
			if(isset($_POST['ajax_fetch'])) 
			{ 
				$prepends = isset($_POST['instance']) ? $_POST['instance'] : 0;
				$element['ajax'] = true; 
				
				for($i = 0; $i < $prepends; $i++)
				{
					$element['id'] = "aviaTB" . $element['id'];
				}
			}
			return $element;
		}
		
		
	
		static function render_element($element, $parent_class = false)
		{
		
			$defaults		= array('id'=>'', 'name'=>'', 'label' => '', 'std' => '', 'class' =>'', 'container_class'=>'', 'desc' =>'', 'required'=>array(), 'target'=>array(), 'shortcode_data'=>array(), 'builder_active' => '');
			$element		= array_merge($defaults, $element);
			$output			= "";
			
			if( $element['builder_active'] )
			{
				$output .= "<div class='avia-conditional-elements avia-conditional-elements-builder-active'>";	
			}
			
			self::$elementValues[$element['id']] = $element['std']; //save the values into a unique array in case we need it for dependencies
			
			//create default data und class string and checks the dependencies of an object
			extract(self::check_dependencies($element));
			
			
			// check if its an ajax request. if so prepend a string to ensure that the ids are unique. 
			// If there are multiple modal windows called prepend the string multiple times
			$element = self::ajax_modify_id($element);
			
			
			$id_string 		 = empty($element['id']) ? "" : "id='".$element['id']."-form-container'";
			$class_string 	.= empty($element['container_class']) ? "" : $element['container_class']; 
			$description_class = empty($element['description_class']) ? "" : $element['description_class']; 
			
			$target_string = "";
			if(!empty($element['target']))
			{
				$data['target-element'] = $element['target'][0];
				$data['target-property'] = $element['target'][1];
				$target_string = AviaHelper::create_data_string($data);
				$class_string .= " avia-attach-targeting ";
			}

			if(!empty($element['fetchTMPL'])) $class_string .= " avia-attach-templating ";
			

			if(empty($element['nodescription']) )
			{
			$output .= "<div class='avia_clearfix avia-form-element-container ".$class_string." avia-element-".$element['type']."' ".$id_string." ".$data_string." ".$target_string.">";
			
				if( !empty($element['name']) ||!empty($element['desc']))  
				{
					$output .= "<div class='avia-name-description {$description_class}'>";
					if( !empty($element['name'])) $output .= "<strong>".$element['name']."</strong>";
					if( !empty($element['desc'])) 
					{
						if(!empty($element['type']) && $element['type'] != 'checkbox')
						{
							$output .= "<div>".$element['desc']."</div>";
						}
						else
						{
							$output .= "<label for='".$element['id']."'>".$element['desc']."</label>";
						}
					}
					
					$output .= "</div>";
				}
			
				$output .= "<div class='avia-form-element ".$element['class']."'>";
				//$output .= self::{$element['type']}($element, $parent_class);
				
				$output .= call_user_func(array('self', $element['type']), $element, $parent_class);
				
				if(!empty($element['fetchTMPL']))
				{
					$output .= "<div class='template-container'></div>";
				}
				
				$output .= "</div>";
			
				
			
			$output .= "</div>";
			}
			else
			{
				//$output .= self::{$element['type']}($element, $parent_class);
				$output .= call_user_func(array('self', $element['type']), $element, $parent_class);
			}
			
			if( $element['builder_active'] )
			{
				$output .= "<div class='av-builder-active-overlay'></div>";
				$output .= "<div class='av-builder-active-overlay-content'>";
				$output .= __('This element only works with activated advanced layout builder','avia_framework')."</div>";
				$output .= "</div>";
			}
			
			return $output;
		}
		
		
		/*
		* Helper function that checks dependencies between objects based on the $element['required'] array
		*
		* If the array is set it needs to have exactly 3 entries.
		* The first entry describes which element should be monitored by the current element. eg: "content"
		* The second entry describes the comparison parameter. eg: "equals, not, is_larger, is_smaller ,contains"
		* The third entry describes the value that we are comparing against.
		*
		* Example: if the required array is set to array('content','equals','Hello World'); then the current
		* element will only be displayed if the element with id "content" has exactly the value "Hello World"
		* 
		*/
		static function check_dependencies($element)
		{	
			$params = array('data_string' => "", 'class_string' => "");
		
			if(!empty($element['required']))
			{
				$data['check-element'] 		= $element['required'][0];
				$data['check-comparison'] 	= $element['required'][1];
				$data['check-value'] 		= $element['required'][2];
				$params['data_string'] 		= AviaHelper::create_data_string($data);
				$return = false;
				
				//required element must not be hidden. otherwise hide this one by default
				if(!isset(self::$elementHidden[$data['check-element']]))
				{
					if(isset(self::$elementValues[$data['check-element']]))
					{
						$value1		= self::$elementValues[$data['check-element']];
						$value2		= $data['check-value'];
						
						switch($data['check-comparison'])
						{
							case 'equals': 			if($value1 == $value2) $return = true; break;
							case 'not': 			if($value1 != $value2) $return = true; break;
							case 'is_larger': 		if($value1 >  $value2) $return = true; break;
							case 'is_smaller': 		if($value1 <  $value2) $return = true; break;
							case 'contains': 		if(strpos($value1,$value2) !== false) $return = true; break;
							case 'doesnt_contain': 	if(strpos($value1,$value2) === false) $return = true; break;
							case 'is_empty_or': 	if(empty($value1) || $value1 == $value2) $return = true; break;
							case 'not_empty_and': 	if(!empty($value1) && $value1 != $value2) $return = true; break;
							case 'parent_in_array':			//	$value1 = "value,id" or "value"
										$sep = strpos( $value1, ',' );
										$val = ( false !== $sep ) ? substr( $value1, 0, $sep ) : $value1;
										if( ! empty( $val ) )
										{
											$return = in_array( $val, explode( ' ', $value2 ) );
										}
										break;
							case 'parent_not_in_array';		//	$value1 = "value,id" or "value"
										$sep = strpos( $value1, ',' );
										$val = ( false !== $sep ) ? substr( $value1, 0, $sep ) : $value1;
										if( ! empty( $val ) )
										{
											$return = ! in_array( $val, explode( ' ', $value2 ) );
										}
										break;
						}
					}
				}
				
				if(!$return)
				{
					$params['class_string'] = ' avia-hidden ';
					self::$elementHidden[$element['id']] = true;
				}
			}
			
			return $params;
		}
		
		
		
		
		/**
         * Creates a wrapper around a set of elements. This set can be cloned with javascript
         * @param array $element the array holds data like id, class and some js settings
         * @return string $output the string returned contains the html code generated within the method
         */
         
		static function modal_group($element, $parent_class)
		{
			$iterations = count($element['std']);
			$class = isset($element['class']) ? $element['class'] : "";
			
			$output = "";
			$output .= "<div class='avia-modal-group-wrapper {$class}' >";
			
			if(!empty($element['creator'])) $output .= self::render_element($element['creator']);
			
			$output .= "<div class='avia-modal-group' id='".$element['id']."' >";
			
			for ($i = 0; $i < $iterations; $i++)
			{
				if(isset($_POST['extracted_shortcode']) && isset($_POST['extracted_shortcode'][$i]))
				{
					$element['shortcode_data'] = $_POST['extracted_shortcode'][$i]['attr'];
				}
			
				$output .= self::modal_group_sub($element, $parent_class, $i);
			}
			
			
			$label = isset($element['add_label']) ? $element['add_label'] : __('Add','avia_framework' );
			
			
			//since adding the clone event we display only custom labels and not only the "+" symbol
			//$label_class = isset($element['add_label']) ? "avia-custom-label" : "";
			$label_class = "avia-custom-label";
			
			
			$output .= "</div>";
			
			if(!isset($element['disable_manual']))
			{
				$output .= "<a class='avia-attach-modal-element-add avia-add {$label_class}'>".$label."</a>";
				
				
				if(!isset($element['disable_cloning']))
				{
					$clone_label = isset($element['clone_label']) ? $element['clone_label'] : __('Copy and add last entry','avia_framework' );
					
					$output .= "<a class='avia-attach-modal-element-clone avia-clone {$label_class}'>".$clone_label."</a>";
				}
			}
			
			//go the new wordpress way and instead of ajax-loading new items, prepare an empty js template
			$output .= '	<script type="text/html" class="avia-tmpl-modal-element">';
			$output .= self::modal_group_sub($element, $parent_class);
			$output .= '	</script>';
			$output .= "</div>";

			return $output;
		}
		
		/**
		 * 
		 * @param array						$element
		 * @param aviaShortcodeTemplate		$parent_class
		 * @param int|false					$i					false, if we need a new empty template to clone if user clicks "Add New"
		 * @return string
		 */
		static function modal_group_sub($element, $parent_class, $i = false)
		{
			$output = "";
			
			$args = array();
			$content = NULL;
			
			//iterate over the subelements and set user selected values or leave the predefined default values
			foreach($element['subelements'] as $key => $subelement)
			{
				/**
				 * New WP way: we add an "empty" template filled with predefined default values that we can clone if user wants to add a new item,
				 * if we have already existing items overwrite default values with user selected values
				 */
				if( false !== $i )
				{
					if(isset($element['std']) && isset($subelement['id']) && is_array($element['std']) && isset($element['std'][$i][$subelement['id']]))
					{
						$subelement['std'] = $element['std'][$i][$subelement['id']];
					}
				}
				
				if(isset($subelement['id']))
				{
					if($subelement['id'] == 'content')
					{
						$content = $subelement['std'];
					}
					else
					{
						$args[$subelement['id']] = $subelement['std'];
					}
				}
			}
			
			if($i !== false && is_array($element['shortcode_data']))
			{
				$args = array_merge($element['shortcode_data'], $args);
			}
	
		
			$params['args'] = $args;
			$params['content'] = $content;

			
			$defaults = array('class'=>'', 'innerHtml'=>'');
			$params = array_merge($defaults, $params);
			$params = $parent_class->editor_sub_element($params);
			extract($params);
			
			$data['modal_title'] 		= $element['modal_title'];
			$data['modal_open']			= isset( $element['modal_open'] ) ? $element['modal_open'] : 'yes';
			$data['trigger_button']			= isset( $element['trigger_button'] ) ? $element['trigger_button'] : '';
			$data['shortcodehandler'] 	= $parent_class->config['shortcode_nested'][0];
			$data['closing_tag']		= $parent_class->is_nested_self_closing( $parent_class->config['shortcode_nested'][0] ) ? 'no' : 'yes';
			$data['modal_ajax_hook'] 	= $parent_class->config['shortcode_nested'][0];
			$data['modal_on_load'] 		= array();
			
			if(!empty($element['modal_on_load']))
			{
				$data['modal_on_load'] 	= array_merge($data['modal_on_load'], $element['modal_on_load']);
			}
			
			if(!empty($parent_class->config['modal_on_load']))
			{
				$data['modal_on_load'] 	= array_merge($data['modal_on_load'], $parent_class->config['modal_on_load']);
			}
			
			$dataString  	= AviaHelper::create_data_string($data);
				
			
			$output .= "<div class='avia-modal-group-element ' {$dataString}>";
			$output .= "<a class='avia-attach-modal-element-move avia-move-handle'>".__('Move','avia_framework' )."</a>";
			$output .= "<a class='avia-attach-modal-element-delete avia-delete'>".__('Delete','avia_framework' )."</a>";
			$output .= "<div class='avia-modal-group-element-inner'>";
			$output .= $params['innerHtml'];
			$output .= "</div>";
			
			$output .= "<textarea data-name='text-shortcode' cols='20' rows='4' name='{$element['id']}'>".ShortcodeHelper::create_shortcode_by_array($parent_class->config['shortcode_nested'][0], $content, $args)."</textarea>";
			$output .= "</div>";
			return $output;
		}
		
		
		
		
		
		/**
         * 
         * Empty Element - The heading method renders a text and description only that might allow to describe some following functionallity
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function heading( $element )
		{	
			return;
		}
		
		/**
         * Tab Container Elements 
         */
         
		static function tab_container( $element )
		{	
			$output = "<div class='avia-modal-tab-container'>";
			return $output;
		}
		
		static function tab( $element )
		{	
			$output  = "<div class='avia-modal-tab-container-inner' data-tab-name='".$element['name']."'>";
			return $output;
		}
		
		static function close_div( $element )
		{	
			$output = "</div>";
			return $output;
		}
		
		
		
		
		
		
		/**
         * 
         * Empty Element - The heading method renders a text and description only that might allow to describe some following functionallity
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function hr( $element )
		{	
			$output = "<div class='avia-builder-hr'></div>";
			return $output;
		}
		
		/**
         * 
         * Checks if a certain option has a required value and if not shows a message and makes options within unclickable
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function condition( $element )
		{	
			//array('option' => 'header_position', 'compare' => "equal_or_empty", "value"=>"header_top")
			
			if(isset( $element['condition']['option'] ))
			{
				$option = avia_get_option($element['condition']['option']);
			}
			else
			{
				$key = $element['condition']['element'];
				$option = isset($element['shortcode_data'][$key]) ? $element['shortcode_data'][$key] : "";
			}
			
			$value  	= $element['condition']['value'];
			
			$result 	= false;
			$class		= "av_option_hidden";
			$overlay	= "<div class='av_conditional_overlay_content'>".$element['notice']."</div>";
			switch($element['condition']['compare'])
			{
				case "equals" : if($option === $value) $result = true; break;
				case "equal_or_empty" : if($option === $value || $option == "") $result = true; break;
				case "contains" : if(strpos($option, $value) === false) $result = true; break;
				case "not" : if($option !== $value) $result = true; break;
			}
			
			
			if($result) 
			{	
				$class = "av_option_visible"; $overlay = "";
			}
			
			$output = "<div class='avia-conditional-elements {$class}'><div class='av_conditional_overlay'></div>{$overlay}";
			return $output;
		}
		
		static function condition_end( $element )
		{	
			$output = "</div>";
			return $output;
		}
		
		
		
		/**
         * 
         * The tiny_mce method renders a tiny mce text field, also known as the wordpress visual editor
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function tiny_mce($element)
		{
			//tinymce only allows ids in the range of [a-z] so we need to filter them. 
			$element['id']  = preg_replace('![^a-zA-Z_]!', "", $element['id']);
			
			
			/* monitor this: seems only ajax elements need the replacement */
			$user_ID = get_current_user_id();
			
			if(get_user_meta($user_ID, 'rich_editing', true) == "true" && isset($element['ajax']))
			{
				global $wp_version;
				
				//replace new lines with brs, otherwise the editor will mess up. this was fixed with wp 4.3 so only do that in old versions
				if ( version_compare( $wp_version, "4.3", '<' ) ) 
				{
					$element['std'] = str_replace("\n",'<br>',$element['std']);
				}
			}
			
			
			ob_start();
	        wp_editor( $element['std'], $element['id'] , array('editor_class' => 'avia_advanced_textarea avia_tinymce', 'media_buttons' => true ) );
	        $output = ob_get_clean();
	        
	        return $output;
		}
		
	
	
		/**
         * 
         * The input method renders one or more input type:text elements, based on the definition of the $elements array
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function input($element)
		{
			$output = '<input type="text" class="'.$element['class'].'" value="'.nl2br($element['std']).'" id="'.$element['id'].'" name="'.$element['id'].'"/>';
			return $output;
		}
		
		
		
		/**
         * 
         * The input method renders one or more input type:text elements, based on the definition of the $elements array
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function multi_input($element)
		{
			$output = "";
			$count = 0;
			$element['std'] = explode(",", $element['std'] );
			$value = "";
			$checked = count(array_unique($element['std'], SORT_STRING));
			
			
			$output .= "<div class='av-multi-input-table'>";
			foreach($element['multi'] as $multi => $label)
			{
				$output .= "<div class='av-multi-input-cell'>";
				$value   = isset($element['std'][$count]) ? $element['std'][$count] : $value;
				$disable = ($checked === 1 && $count !== 0 && !empty($element['sync'])) ? 'disabled' : "";
				
				$output .= !empty($label) ? "<div class='av-multi-input-label'>{$label}</div>" : "";
				$output .= '<input '.$disable.' type="text" class="'.$element['class'].'" value="'.nl2br($value).'" id="'.$element['id'].'_'.$multi.'" name="'.$element['id'].'"/>';
				$output .= "</div>";
				
				$count ++;	
			}
			$output .= "</div>";
			
			if(isset($element['sync']))
			{
				$checked = $checked === 1 ? "checked='checked'" : "";
				$label	 = __('Apply the same value to all?','avia_framework');
				$output .= "<label class='av-multi-input-label-sync'>";
				$output .= '<input '.$checked.' type="checkbox" class="'.$element['class'].'_sync" value="true" id="'.$element['id'].'_sync" name="'.$element['id'].'_sync"/>';
				$output .= $label."</label>";
			}
			
			
			return $output;
		}

		
		/**
         * 
         * The hidden method renders a single input type:hidden element
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function hidden( $element )
		{			
			$output  = '<input type="hidden" value="'.$element['std'].'" id="'.$element['id'].'" name="'.$element['id'].'"/>';
			return $output;
		}
		
		/**
         * 
         * The checkbox method renders a single input type:checkbox element
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         * @todo: fix: checkboxes at metaboxes currently dont work
         */
  		static function checkbox( $element )
		{	
			
			$checked = "";
			if( $element['std'] != "" ) { $checked = 'checked = "checked"'; }
	
			$output   = '<input '.$checked.' type="checkbox" class="'.$element['class'].'" ';
			$output  .= 'value="'.$element['id'].'" id="'.$element['id'].'" name="'.$element['id'].'"/>';
			
			return $output;
		}
		
		/**
         * 
         * The textarea method renders a single textarea element
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function textarea( $element )
		{	
			$output  = '<textarea rows="5" cols="30" class="'.$element['class'].'" id="'.$element['id'].'" name="'.$element['id'].'">';
			$output .= rtrim($element['std']).'</textarea>';
			return $output;
		}
		
		
		/**
         * 
         * The iconfont method renders a single icon-select element based on a font
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function iconfont( $element )
		{	
			$output = "";
			
			if(!empty($element['chars']) && is_array($element['chars']))
			{
				$chars = $element['chars'];
			}
			else
			{
				$chars 	= avia_font_manager::load_charlist();
			}
			
			//get either the passed font or the default font
			$std_font = isset($element['shortcode_data']['font']) ? $element['shortcode_data']['font'] : key(AviaBuilder::$default_iconfont);
			
			$output .= "<div class='avia_icon_select_container avia-attach-element-container'>";
		
			$run = 0;
			$active_font = "";
			foreach($chars as $font => $charset)
			{
				$run ++;
				asort($charset);
				
				if($run === 1)
				{
					//if the el value is empty set it to the first char
					if(empty($element['std'])) $element['std'] = key($charset);
					$standard = avia_font_manager::get_display_char($element['std'], $std_font);
				}
				
				$output .= "<div class='av-iconselect-heading'>Font: {$font}</div>";
				foreach($charset as $key => $char)
				{	
					$char = avia_font_manager::try_decode_icon($char);
					$charcode_prefix 	= "Charcode: \\";
					$active_char 		= "";
					
					if($char == $standard && !empty($std_font) && $std_font == $font)
					{
						$active_char = "avia-active-element";
						$active_font = $font;
					}
					
					$output .= "<span title='{$charcode_prefix}{$key}' data-element-nr='{$key}' data-element-font='{$font}' class='avia-attach-element-select avia_icon_preview avia-font-{$font} {$active_char}'>{$char}</span>";
				}
			}
			
			//default icon value
			$output .= self::hidden($element);
			
			//fake character value needed for backend editor
			$element['id'] = $element['id']."_fakeArg";
			$element['std'] = empty($standard) ? "" : $standard;
			$output .= self::hidden($element);
			
			//font value needed for backend and editor
			$element['id'] = "font";
			$element['std'] = $active_font;
			$element = self::ajax_modify_id($element);
			$output .= self::hidden($element);
			
			
			$output .= "</div>";

			return $output;
		}
		

		
		
		
		/**
         * 
         * The colorpicker method renders a colorpicker element that allows you to select a color of your choice
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function colorpicker($element)
		{
			$data = "";
			if( !empty( $element['rgba'] )) $data = "data-av-rgba='1'";
			
			$output = '<input type="text" class="av-colorpicker '.$element['class'].'" value="'.$element['std'].'" id="'.$element['id'].'" name="'.$element['id'].'" '.$data.' />';
			
			return $output;
		}
		
		/**
         * 
         * The datepicker method renders a datepicker element that allows you to select a date of your choice
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function datepicker($element)
		{
			$output = '<input type="text" class="av-datepicker av-no-autoselect '.$element['class'].'" value="'.$element['std'].'" id="'.$element['id'].'" name="'.$element['id'].'"/>';
			return $output;
		}
		
		
		/**
         * 
         * The linkpicker method renders a linkpicker element that allows you to select a link to a post type or taxonomy type of your choice
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         *
         * @todo: currently only one linkpicker per modal window possible
         */
		static function linkpicker($element)
		{	
			//fallback for previous default input link elements: convert a https://kriesi.at value to a manually entry
			if(strpos($element['std'], 'http://') === 0) $element['std'] = 'manually,'.$element['std'];
			
		
			//necessary for installations with thousands of posts
			@ini_set("memory_limit","256M");
			
			$original = $element;
			$new_std = explode(',', $element['std'], 2);
	
			$pt = array_flip(AviaHelper::public_post_types());
			$ta = array_flip(AviaHelper::public_taxonomies(false, true));
			
			if(isset($new_std[1])) $original['std'] = $new_std[1];
			
			$allowed_pts = isset($original['posttype']) ? $original['posttype'] : $pt;
			$allowed_tas = isset($original['taxtype']) ? $original['taxtype'] : $ta;
			
			if( in_array('single', $element['subtype'] ) )
			{
				foreach( $pt as $key => $type )
				{
					if( in_array( $type, $allowed_pts ) )
					{
						$html = AviaHtmlHelper::select_hierarchical_post_types( $element, $type );
						
						if( false === $html )
						{
							$original['subtype'] = $type;
							$html = self::select( $original ); 
						}
						
						if( ! empty( $html ) )
						{ 
							AviaHelper::register_template( $original['id'] . '-' . $type, $html ); 
						} 
						else 
						{ 
							unset($pt[$key] ); 
						}
					}
					else
					{
						unset($pt[$key]);
					}
				}
			}
			
			if(in_array('taxonomy', $element['subtype']))
			{
				foreach($ta as $key => $type)
				{
					if(in_array($type, $allowed_tas))
					{
						$html = AviaHtmlHelper::select_hierarchical_taxonomy( $element, $type );

						if( false === $html )
						{
							$original['subtype'] = 'cat';
							$original['taxonomy'] = $type;

							$html = self::select( $original ); 
						}
						
						if( ! empty( $html ) )
						{
							AviaHelper::register_template( $original['id'] . '-' . $type, $html ); 
						} 
						else 
						{ 
							unset( $ta[ $key ] ); 
						}
					}
					else
					{
						unset($ta[$key]);
					}
				}
			}
			
			if(isset($new_std[1])) $element['std'] = $new_std[1];

			$original['subtype'] = array(); 
			foreach($element['subtype'] as $value => $key) //register templates
			{
				switch($key)
				{
					case "manually": 
					
					if($new_std[0] != $key) $element['std'] = "http://";
					
					$original['subtype'][$value] = $key;
					$html = self::input($element); 
					AviaHelper::register_template($original['id'].'-'.$key, $html);
					break;
					
					case "single": 
					$original['subtype'][$value] = $pt;
					break;
					
					case "taxonomy": 
					$original['subtype'][$value] = $ta;
					break;
					
					default: $original['subtype'][$value] = $key; break;
					
				}
			}
			
			if(!empty($element['ajax'])) // if we got an ajax request we also need to call the printing since the default wordpress hook is already executed
			{
				AviaHelper::print_templates();
			}
		
			$original['std'] = $new_std[0];
			unset($original['multiple']);
			$output = self::select($original);
			return $output;
		}
		
		
		/**
         * 
         * The image method renders an image upload button that allows the user to select an image from the media uploader and insert it
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function image($element)
		{
			if(empty($element['data']))
			{
				$fetch = isset($element['fetch']) ? $element['fetch'] : "url";
				$state = isset($element['state']) ? $element['state'] : "avia_insert_single";
				
				if(empty($element['show_options']))
				{
					$class = $fetch == "id" ? "avia-media-img-only-no-sidebars" : "avia-media-img-only";
				}
				else if($element['show_options'] == true)
				{
					$class = "avia-media-img-only";
				}
				
				
				$element['data'] =  array(	'target' => $element['id'], 
											'title'  => $element['title'], 
											'type'   => $element['type'], 
											'button' => $element['button'],
											'class'  => 'media-frame '.$class.' '.$element['container_class'] ,
											'frame'  => 'select',
											'state'  => $state,
											'fetch'  => $fetch,
											'save_to'=> 'hidden'
									);
			}
			
			if(isset($element['modal_class'])) $element['data']['class'] .= " ".$element['modal_class'];
			
			
			$data 	= AviaHelper::create_data_string($element['data']);
			$class 	= 'button aviabuilder-image-upload avia-builder-image-insert '.$element['class'];
			$output = '	<a href="#" class="'.$class.'" '.$data.' title="'.esc_attr($element['title']).'">'.$element['title'].'</a>';
			
			if(isset($element['delete'])) $output .= '<a href="#" class="button avia-delete-gallery-button" title="'.esc_attr($element['delete']).'">'.$element['delete'].'</a>';
				
			
			$attachmentids 	= !empty($element['shortcode_data']['attachment']) ? explode(',', $element['shortcode_data']['attachment']) : array();
			$attachmentid 	= !empty($attachmentids[self::$imageCount]) ? $attachmentids[self::$imageCount] : '';
			
			$attachmentsizes = ! empty( $element['shortcode_data']['attachment_size'] ) ? explode( ',', $element['shortcode_data']['attachment_size'] ) : array();	
			$attachmentsize = ! empty( $attachmentsizes[ self::$imageCount ] ) ? $attachmentsizes[ self::$imageCount ] : "";		
						
			//get image based on id if possible - use the force_id_fetch param in conjunction with the secondary_img when you need a secondary image based on id and not on url like pattern overlay. size of a secondary image can not be stored. tab section element is a working example
			if(!empty($attachmentid) && !empty($attachmentsize) && empty($element['force_id_fetch']))
			{
				$fake_img 	= wp_get_attachment_image( $attachmentid, $attachmentsize);
				$url		= wp_get_attachment_image_src( $attachmentid, $attachmentsize);
				$url		= !empty($url[0]) ? $url[0] : "";
			}
			else if(isset($fetch) && $fetch == "id")
			{
				$fake_img 	= wp_get_attachment_image( $element['std'], 'thumbnail');
				$url		= wp_get_attachment_image_src( $element['std'], 'thumbnail');
				$url		= !empty($url[0]) ? $url[0] : "";
			}
			else
			{
				$fake_img 	= '<img src="'.$element['std'].'" />';
				$url		= $element['std'];
			}
	
					
			if($element['type'] != 'video')
			{
				$output .= self::display_image($url);			
			}
			
			//$output .= self::$element['data']['save_to']($element);
			$output .= call_user_func(array('self', $element['data']['save_to']), $element);
			
			//fake img for multi_image element
			if(isset($fetch))
			{
			
				$fake_img_id = str_replace ( str_replace('aviaTB','',$element['id']) ,'img_fakeArg', $element['id']);
				$img_id_field = str_replace ( str_replace('aviaTB','',$element['id']) ,'attachment', $element['id']);
				$img_size_field = str_replace ( str_replace('aviaTB','',$element['id']) ,'attachment_size', $element['id']);

				$output .= '<input type="hidden" class="hidden-image-url '.$element['class'].'" value="'.htmlentities($fake_img, ENT_QUOTES, get_bloginfo( 'charset' )).'" id="'.$fake_img_id.'" name="'.$fake_img_id.'"/>';
				
				if($fetch == 'url' && empty($element['secondary_img']))
				{
				
					$output .= '<input type="hidden" class="hidden-attachment-id '.$element['class'].'" value="'.$attachmentid.'" id="'.$img_id_field.'" name="'.$img_id_field.'"/>';
					$output .= '<input type="hidden" class="hidden-attachment-size '.$element['class'].'" value="'.$attachmentsize.'" id="'.$img_size_field.'" name="'.$img_size_field.'"/>';
				}
			}

			if(empty($element['force_id_fetch']))
			{
				if($element['type'] != 'video') self::$imageCount++;
			}
			return $output;
		}
		
		/**
         * 
         * The gallery method renders an image upload button that allows the user to select an image from the media uploader and insert it
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function gallery($element)
		{
			if(empty($element['data']))
			{
				$element['data'] = array(	'target' => $element['id'], 
											'title'  => $element['title'], 
											'type'   => $element['type'], 
											'button' => $element['button'],
											'class'  => 'media-frame avia-media-gallery-insert '.$element['container_class'],
											'frame'  => 'post',
											'state'  => 'gallery-library',
											'state_edit' => 'gallery-edit',
											'fetch'  => 'id',
											'save_to'=> 'hidden'
										);
			}

			return AviaHtmlHelper::image($element);
		}
		
		/**
         * 
         * The video method renders a video upload button that allows the user to select an video from the media uploader and insert it
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function video($element)
		{
			if(empty($element['data']))
			{
				$element['data'] = array(	'target' => $element['id'], 
											'title'  => $element['title'], 
											'type'   => $element['type'], 
											'button' => $element['button'],
											'class'  => 'media-frame avia-blank-insert '.$element['container_class'] ,
											'state'  => 'avia_insert_video',
											'frame'  => 'select',
											'fetch'  => 'url',
											'save_to'=> 'input'
										);
			}

			return AviaHtmlHelper::image($element);
		}
		
		/**
         * The audio method renders an audio upload button that allows the user to select one or more audio files from the media uploader 
		 * and insert it
		 * 
         * @param array $element		the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string				the string returned contains the html code generated within the method
         */
		public static function audio_player( $element )
		{
			if( empty($element['data'] ) )
			{			
				$fetch = isset($element['fetch']) ? $element['fetch'] : "template_audio";
				$state = isset($element['state']) ? $element['state'] : "avia_insert_multi_audio";
				
				$class = $fetch == "template" ? "avia-media-img-only-no-sidebars" : "avia-media-img-only";
				
				$element['data'] =  array(	
									'target'		=> $element['id'], 
									'title'			=> $element['title'], 
									'type'			=> $element['type'], 
									'button'		=> $element['button'],
									'class'			=> 'media-frame avia-media-audio-insert ' . $element['container_class'],
									'frame'			=> 'post',
									'state'			=> 'playlist-library',
									'state_edit'	=> 'playlist-edit',
									'fetch'			=> $fetch,
									'save_to'		=> 'html',
									'media_type'	=> 'audio'
									);
			}
			
			$data 	= AviaHelper::create_data_string($element['data']);
			$class 	= 'button aviabuilder-image-upload avia-builder-image-insert avia-builder-audio-edit '.$element['class'];
			$output = '	<a href="#" class="'.$class.'" '.$data.' title="'.esc_attr($element['title']).'">
						<span class="wp-media-buttons-icon"></span>'.$element['title'].'</a>';
						
			return $output;
		}
		
		
		
		
		/**
         * 
         * The multi_image method allows us to insert many images into a modal template at once
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function multi_image($element)
		{
			if(empty($element['data']))
			{
				$fetch = isset($element['fetch']) ? $element['fetch'] : "template";
				$state = isset($element['state']) ? $element['state'] : "avia_insert_multi";
				
				if( empty( $element['show_options'] ) )
				{
					$class = $fetch == "template" ? "avia-media-img-only-no-sidebars" : "avia-media-img-only";
				}
				else if( $element['show_options'] == true )
				{
					$class = "avia-media-img-only";
				}
				
				$element['data'] =  array(	'target' => $element['id'], 
											'title'  => $element['title'], 
											'type'   => $element['type'], 
											'button' => $element['button'],
											'class'  => 'media-frame '.$class.' '.$element['container_class'] ,
											'frame'  => 'select',
											'state'  => $state,
											'fetch'  => $fetch,
									);
			}
			
			$data 	= AviaHelper::create_data_string($element['data']);
			$class 	= 'button aviabuilder-image-upload avia-builder-image-insert '.$element['class'];
			$output = '	<a href="#" class="'.$class.'" '.$data.' title="'.esc_attr($element['title']).'">
						<span class="wp-media-buttons-icon"></span>'.$element['title'].'</a>';
						
			
			return $output;
		}
		
		
		/**
		 * Return an indented dropdown list for hierarchical post types
		 * 
		 * @since 4.2.7
		 * @added_by Günter
		 * @param array $element
		 * @param string $post_type
		 * @return string|false
		 */
		static public function select_hierarchical_post_types( array $element, $post_type = 'page' )
		{
			$defaults = array(
								'id'				=> '',
								'std'				=> array( '', 0 ),
								'label'				=> false,			//	for option group
								'class'				=> '',
								'hierarchical'		=> 'yes',			//	'yes' | 'no'
								'post_status'		=> 'publish',		//	array or seperated by comma
								'option_none_text'	=> '',				//	text to display for "Nothing selected"
								'option_none_value'	=> '',				//	value for 'option_none_text'
								'option_no_change'	=> ''				//	value for 'no change' - set to -1 by WP default
							);

			$element = array_merge( $defaults, $element );

			
			/**
			 * return, if element should not display a hierarchical structure
			 */
			if( 'no' == $element['hierarchical'] )
			{
				return false;
			}
			
			/**
			 * wp_dropdown_pages() does not support multiple selection by default.
			 * Would need to overwrite Walker_PageDropdown to add this feature.
			 * 
			 * Can be done in future if necessary.
			 */
			if( isset( $element['multiple'] ) )
			{
				return false;
			}

			$post_type_object = get_post_type_object( $post_type );

			if ( ! ( $post_type_object instanceof WP_Post_Type && $post_type_object->hierarchical ) )
			{
				return false;
			}
			
			/**
			 * If too many entries limit output and only show non hierarchical
			 * 
			 * @since 4.2.7
			 */
			$limit = apply_filters( 'avf_dropdown_post_number', 4000, $post_type, $element, 'alb_select_hierarchical' );
			$count = wp_count_posts( $post_type );
			if( ! isset( $count->publish ) || ( $count->publish > $limit ) )
			{
				return false;
			}
			
			/**
			 * Make sure we have no spaces
			 */
			$post_status = is_array( $element['post_status'] ) ? $element['post_status'] : explode( ',', (string) $element['post_status'] );
			$element['post_status'] = array_map( function( $value ) { $value = trim($value); return $value;}, $post_status );
			
			
			$new_std = explode( ',', $element['std'], 2 );
			$selected = ( ( $new_std[0] == $post_type ) && isset( $new_std[1] ) ) ? $new_std[1] : 0;
			
			$data_string = "";
			if( isset( $element['data'] ) ) 
			{
				foreach( $element['data'] as $key => $data )
				{
					$data_string .= " data-" . $key . "='" . $data . "'";
				}
			}
			
			$multi = $multi_class = '';
			if( isset( $element['multiple'] ) ) 
			{
				$multi_class = " avia_multiple_select";
				$multi = ' multiple="multiple" size="' . $element['multiple'] . '" ';
			}

			$dropdown_args = array(
							'post_type'				=> $post_type,
							'exclude_tree'			=> false,
							'selected'				=> $selected,
							'name'					=> $element['id'],
							'id'					=> $element['id'],
							'show_option_none'		=> $element['option_none_text'],
							'option_none_value'		=> $element['option_none_value'],
							'show_option_no_change' => $element['option_no_change'],
							'sort_column'			=> 'post_title',
							'echo'					=> 0,
							'class'					=> $element['class'] . $multi_class,	
							'post_status'			=> $element['post_status']
					//		'depth'					=> 0, 
					//		'child_of'				=> 0,
					//		'value_field'			=> 'ID',	
							);
			
			/**
			 * Allow to add info for non public post status
			 */
			add_filter( 'list_pages', __CLASS__ . '::handler_wp_list_pages', 10, 2 );
			
			$html = wp_dropdown_pages( $dropdown_args );
			
			remove_filter( 'list_pages', __CLASS__ . '::handler_wp_list_pages', 10, 2 );

			$html = str_replace( '<select', '<select ' . $multi . $data_string, $html );
			
			return $html;
		}		
		
		
		/**
		 * Add post status in case of non public 
		 * 
		 * @since 4.2.7
		 * @added_by Günter
		 * @param string $title
		 * @param object $page
		 * @return string
		 */
		static public function handler_wp_list_pages( $title, $page )
		{
			if( $page instanceof WP_Post || ( isset( $page->ID ) && isset( $page->post_status ) ) )
			{
				if( 'publish' != $page->post_status )
				{
					$title .= ' (' . ucfirst( get_post_status( $page->ID ) ) . ')';
				}
			}
			
			return $title;
		}

		/**
		 * Return an indented dropdown list of terms for hierarchical $taxonomy
		 * 
		 * @since 4.2.7
		 * @added_by Günter
		 * @param array $element
		 * @param string $taxonomy
		 * @return string|false
		 */
		static public function select_hierarchical_taxonomy( array $element, $taxonomy = 'category' )
		{
			$defaults = array(
								'id'				=> '',
								'std'				=> array( '', 0 ),
								'label'				=> false,			//	for option group
								'class'				=> '',
								'hierarchical'		=> 'yes',			//	'yes' | 'no'
								'option_none_text'	=> '',				//	text to display for "Nothing selected"
								'option_none_value'	=> '',				//	value for 'option_none_text'
								'option_no_change'	=> ''				//	value for 'no change' - set to -1 by WP default
							);

			$element = array_merge( $defaults, $element );
			
			/**
			 * return, if element should not display a hierarchical structure
			 */
			if( 'no' == $element['hierarchical'] )
			{
				return false;
			}
			
			/**
			 * wp_dropdown_pages() does not support multiple selection by default.
			 * Would need to overwrite Walker_CategoryDropdown to add this feature.
			 * 
			 * Can be done in future if necessary.
			 */
			if( isset( $element['multiple'] ) )
			{
				return false;
			}
			
			$obj_ta = get_taxonomy( $taxonomy );
			
			if ( ! $obj_ta instanceof WP_Taxonomy )
			{
				return false;
			}
			
			$new_std = explode( ',', $element['std'], 2 );
			$selected = ( ( $new_std[0] == $taxonomy ) && isset( $new_std[1] ) ) ? $new_std[1] : 0;
			
			$data_string = "";
			if( isset( $element['data'] ) ) 
			{
				foreach( $element['data'] as $key => $data )
				{
					$data_string .= " data-" . $key . "='" . $data . "'";
				}
			}
			
			$multi = $multi_class = '';
			if( isset( $element['multiple'] ) ) 
			{
				$multi_class = " avia_multiple_select";
				$multi = ' multiple="multiple" size="' . $element['multiple'] . '" ';
			}
			
			$args = array(
						'taxonomy'				=> $taxonomy,
						'hierarchical'			=> true,
						'depth'					=> 20,
						'selected'				=> $selected,
						'name'					=> $element['id'],
						'id'					=> $element['id'],
						'show_option_none'		=> $element['option_none_text'],
						'option_none_value'		=> $element['option_none_value'],
						'show_option_no_change' => $element['option_no_change'],
						'orderby'				=> 'name',
						'order'					=> 'ASC',
						'echo'					=> false,
						'class'					=> $element['class'] . $multi_class,
						'hide_empty'			=> false,
						'show_count'			=> true,
						'hide_if_empty'			=> false,
//						'child_of'				=> 0,
//						'exclude'				=> '',
//						'include'				=> '',
//						'tab_index'				=> 0,
//						'value_field'			=> 'term_id',
					);
			
			$html = wp_dropdown_categories( $args );

			$html = str_replace( '<select', '<select ' . $multi . $data_string, $html );
			
			return $html;
		}


		/**
         * 
         * The select method renders a single select element: it either lists custom values, all wordpress pages or all wordpress categories
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
        
		static function select( $element )
		{	
			$select 	= __('Select','avia_framework' );
			$parents 	= array();
			$fake_val 	= "";
			$entries	= array();
			
			if($element['subtype'] == 'cat')
			{
				$add_taxonomy = "";
				
				if(!empty($element['taxonomy'])) $add_taxonomy = "&taxonomy=".$element['taxonomy'];
			
				$entries = get_categories('title_li=&orderby=name&hide_empty=0'.$add_taxonomy);
				
				//sort entries so subentries are displayed with indentation
				
		        foreach($entries as $key => $entry)
		        {
			    	if( !empty($entry->parent) )
			    	{
				    	$parents[$entry->parent][$entry->term_id] = $entry;
				    	unset($entries[$key]);
			    	}
		        }
			}
			else if(!is_array($element['subtype']))
			{
				global $wpdb;
				$table_name = $wpdb->prefix . "posts";
	 			$limit 		= apply_filters( 'avf_dropdown_post_number', 4000, $element['subtype'], $element, 'alb_select' );
	    		
	    		if( isset( AviaHtmlHelper::$cache['entry_' . $limit] ) && isset( AviaHtmlHelper::$cache['entry_' . $limit][$element['subtype']] ) )
	    		{
	    			$entries = AviaHtmlHelper::$cache['entry_' . $limit][$element['subtype']];
	    		}
	    		else
	    		{	
					$post_status = ( ! empty( $element['post_status'] ) ) ? $element['post_status'] : 'publish';
					$post_status = explode( ',', $post_status );
					$post_status = array_map( function( $value ) { $value = trim($value); return "'{$value}'";}, $post_status );
					$post_status = implode( ', ',$post_status );
					
					$prepare_sql = "SELECT distinct ID, post_title, post_status FROM {$table_name} WHERE post_status IN ( {$post_status} ) AND post_type = '".$element['subtype']."' ORDER BY post_title ASC LIMIT {$limit}";
					$prepare_sql = apply_filters('avf_dropdown_post_query', $prepare_sql, $table_name, $limit, $element);
					$entries 	= $wpdb->get_results($prepare_sql);
					
					/**
					 * Allow to filter the page titles
					 * 
					 * @since 4.2.7
					 */
					add_filter( 'list_pages', __CLASS__ . '::handler_wp_list_pages', 10, 2 );
			
					foreach ( $entries as &$entry ) 
					{
						$entry->post_title = apply_filters( 'list_pages', $entry->post_title, $entry );
					}
				
					unset( $entry );
					
					remove_filter( 'list_pages', __CLASS__ . '::handler_wp_list_pages', 10, 2 );
					
					AviaHtmlHelper::$cache['entry_' . $limit][$element['subtype']] = $entries;
	    		}	
	    		//$entries 	= $wpdb->get_results( "SELECT ID, post_title FROM {$table_name} WHERE post_status = 'publish' AND post_type = '".$element['subtype']."' ORDER BY post_title ASC LIMIT {$limit}" );
				//$entries = get_posts(array('numberposts' => apply_filters( 'avf_dropdown_post_number', 200 ), 'post_type' => $element['subtype'], 'post_status'=> 'publish', 'orderby'=> 'post_date', 'order'=> 'ASC'));
			}
			else
			{	
				$select = 'Select...';
				$entries = $element['subtype'];
				$add_entries = array();
				
				if(isset($element['folder']))
				{	
					$add_file_array = avia_backend_load_scripts_by_folder(AVIA_BASE.$element['folder']);
	
					if(is_array($add_file_array))
					{
						foreach($add_file_array as $file)
						{
							$skip = false;
						
							if(!empty($element['exclude']))
							{
								foreach($element['exclude'] as $exclude) {
        							if (stripos($file,$exclude) !== false) $skip = true;
    							}
							}
							
							if(strpos($file, '.') !== 0 && $skip == false)
							{
								$add_entries[$element['folderlabel'].$file] = "{{AVIA_BASE_URL}}".$element['folder'].$file; 							}
							}
					
					
						if(isset($element['group']))
						{
							$entries[$element['group']] = $add_entries;
						}
						else
						{
							$entries = array_merge($entries, $add_entries);
						}
					}
					
				}
			}
			
			if( empty( $entries ) ) 
			{
				return;
			}
			
			$data_string = "";
			if(isset($element['data'])) 
			{
				foreach ($element['data'] as $key => $data)
				{
					$data_string .= "data-".$key."='".$data."'";
				}
			}
			
			$multi = $multi_class = "";
			if(isset($element['multiple'])) 
			{
				$multi_class = " avia_multiple_select";
				$multi = 'multiple="multiple" size="'.$element['multiple'].'"';
				$element['std'] = explode(',', $element['std']);
			}
			
			$id_string = empty($element['id']) ? "" : "id='".$element['id']."'";
			$name_string = empty($element['id']) ? "" : "name='".$element['id']."'";
			
			$output = '<select '.$multi.' class="'.$element['class'].'" '. $id_string .' '. $name_string . ' '.$data_string.'> ';
			
			
			if(isset($element['with_first'])) { $output .= '<option value="">'.$select .'</option>  '; $fake_val = $select; }
			
			$real_entries = array();
			foreach ($entries as $key => $entry)
			{
				if(!is_array($entry))
				{
					$real_entries[$key] = $entry;
				}
				else
				{
					$real_entries['option_group_'.$key] = $key;
				
					foreach($entry as $subkey => $subentry)
					{
						$real_entries[$subkey] = $subentry;
					}
					
					$real_entries['close_option_group_'.$key] = "close";
				}
			}
			
			$entries = $real_entries;
			$output .= AviaHtmlHelper::create_select_option($element, $entries, $fake_val, $parents, 0);				
				
				
			
			$output .= '</select>';

			
			return $output;
		}
		
		static function create_select_option($element, $entries, $fake_val, $parents, $level)
		{	
			$output = "";
			
			foreach ($entries as $key => $entry)
			{
				if($element['subtype'] == 'cat')
				{
					if(isset($entry->term_id))
					{
						$id = $entry->term_id;
						$title = $entry->name;
					}
				}
				else if(!is_array($element['subtype']))
				{
					$id = $entry->ID;
					$title = $entry->post_title;
				}
				else
				{
					$id = $entry;
					$title = $key;				
				}
				
				if(!empty($title) || (isset($title) && $title === 0))
				{
					if(empty($fake_val)) $fake_val = $title;
					$selected = "";
					if ($element['std'] == $id || (is_array($element['std']) && in_array($id, $element['std']))) 
					{ 
						$selected = "selected='selected'"; 
						$fake_val = $title;
					}
					
					$indent = "";
					for($i = 0; $i < $level; $i++)
					{
						$indent .= "- ";
					}
					
					if(strpos ( $title , 'option_group_') === 0) 
					{
						$output .= "<optgroup label='". $id."'>";
					}
					else if(strpos ( $title , 'close_option_group_') === 0) 
					{
						$output .= "</optgroup>";
					}
					else
					{
						$output .= "<option $selected value='". $id."'>". $indent.$title."</option>";
					}
					
					if(!empty($parents) && !empty($parents[$id]))
					{
						$level ++;
						$output .= AviaHtmlHelper::create_select_option($element, $parents[$id], $fake_val, $parents, $level);
						$level --;
					}
					
					
				}
			}	
			return $output;	
		}
		
		
		
		/**
         * 
         * The gmap_adress method renders an address input field that allows to fetch long/lat coordinates via google api
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static public function gmap_adress( $element )
		{	
			$defaults = array(
							'address'	=> '', 
							'postcode'	=> '',
							'city'		=> '',
							'state'		=> '',
							'country'	=> '', 
							'long'		=> '', 
							'lat'		=> ''
						);
			
			$subvalues = isset($element['shortcode_data']) ? $element['shortcode_data'] : array();
			$values = array_merge( $defaults, $subvalues );
			$visibility = "";
			
			if( ! empty($subvalues['long'] ) || ! empty( $subvalues['lat'] ) ) 
			{
				$visibility = "av-visible";
			}
			
			$output  = '';
			$output .= '<label class="av-gmap-field av-gmap-addres">'.__('Street address','avia_framework').' <input type="text" class="'.$element['class'].'" value="'.nl2br($values['address']).'" id="address" name="address"/></label>';
			
			$output .= '<label class="av-gmap-field av-gmap-addres av_half av_first">' . __( 'Postcode', 'avia_framework' ) . ' <input type="text" class="' . $element['class'] . '" value="' . nl2br($values['postcode']) . '" id="postcode" name="postcode"/></label>';
			$output .= '<label class="av-gmap-field av-gmap-addres av_half">'.__('City','avia_framework').' <input type="text" class="'.$element['class'].'" value="'.nl2br($values['city']).'" id="city" name="city"/></label>';
			
			$output .= '<label class="av-gmap-field av-gmap-addres av_half av_first">' . __( 'State', 'avia_framework' ) . ' <input type="text" class="' . $element['class'] . '" value="' . nl2br($values['state']) . '" id="state" name="state"/></label>';
			$output .= '<label class="av-gmap-field av-gmap-addres av_half">'.__('Country','avia_framework').' <input type="text" class="'.$element['class'].'" value="'.nl2br($values['country']).'" id="country" name="country"/></label>';
			
			$class 	 = 'button button-primary avia-js-google-coordinates av-google-fetch-button'.$element['class'];
			$output .= '<a href="#" class="'.$class.'" title="">'.__('Enter Address, then fetch coordinates','avia_framework').'</a>';
			$output .= '<div class="av-gmap-coordinates '.$visibility.'">';
			
			$output .= '<label class="av-gmap-field av_half av_first">'.__('Longitude','avia_framework').' <input type="text" class="'.$element['class'].'" value="'.nl2br($values['long']).'" id="long" name="long"/></label>';
			
			$output .= '<label class="av-gmap-field av_half">'.__('Latitude','avia_framework').' <input type="text" class="'.$element['class'].'" value="'.nl2br($values['lat']).'" id="lat" name="lat"/></label>';
			
			
			$output .= '</div>';
			
			return $output;
		}
		

		
		/**
         * 
         * The radio method renders one or more input type:radio elements, based on the definition of the $elements array
         * @param array $element the array holds data like type, value, id, class, description which are necessary to render the whole option-section
         * @return string $output the string returned contains the html code generated within the method
         */
		static function radio( $element )
		{	
			$output = "";

			$counter = 1;
			foreach($element['options'] as $key => $radiobutton)
			{
				$checked = "";
				$extra_class = "";
				if( $element['std'] == $key ) { $checked = 'checked = "checked"'; }

				$fields  = '<input '.$checked.' type="radio" class="radio_'.$key.'" ';
                $fields  .= 'value="'.$key.'" id="'.$element['id'].$counter.'" name="'.$element['id'].'"/>';

                $fields  .= '<label for="'.$element['id'].$counter.'">';

				if( isset($element['images']) &&  !empty($element['images'][$key]) ) {
                    $fields  .= "<img class='radio_image' src='".$element['images'][$key]."' />";
                    $extra_class = 'avia-image-radio';
                }

                $fields .= '<span class="labeltext">'.$radiobutton.'</span>';
                $fields  .= '</label>';

                $output  .= '<span class="avia_radio_wrap '.$extra_class.'">';
                $output  .= $fields;
				$output  .= '</span>';
				
				$counter++;
			}	
				
			return $output;

		}
		
		
		
		static function mailchimp_list( $element )
		{
			$api 		= $element['api'];
			$new_list 	= array();
			
			foreach( $api->fields as $list_id => $fields)
			{
				foreach($fields as $field)
				{
					$list_item = array();
					$list_item['id'] 		= $field->merge_id;
					$list_item['label'] 	= $field->name;
					$list_item['type'] 		= $field->type;
					$list_item['value'] 	= $field->default_value;
					$list_item['disabled'] 	= empty($field->show) ? "true" : "";
					$list_item['check'] 	= "";
								
					
					if($field->required == 1)
					{
						$list_item['check'] = "is_empty";
						if($field->type == 'email')  { $list_item['check'] = "is_email"; $list_item['type'] = "text";}
						if($field->type == 'number')  $list_item['check'] = "is_number";
					}
					
					if(isset( $field->options ))
					{
						$list_item['options'] = implode(",", $field->options->choices );
					}						
					
					$new_list[$list_id][$field->merge_id] = $list_item;
				}
				
				//add the default subscribe button
				if(!empty($new_list[$list_id]))
				{
					$new_list[$list_id]['av-button'] = array(
						'id' 		=> "av-button",
						'label' 	=> __( "Subscribe" , "avia_framework" ),
						'type'		=> "button",
						'value' 	=> "",
						'check' 	=> "",
					);
				}
				
			}
			
			$output  = "";
			$output  .= self::select( $element );	
			$output  .= "<script type='text/javascript' > var av_mailchimp_list = ".json_encode($new_list)."; ";
			$output  .= "</script>";
			
			return $output;
		}
		
		
		
		
		
		
		
		
		static function table ( $element , $parent)
		{
			$values = !empty($_POST['extracted_shortcode']) ? $_POST['extracted_shortcode'] : false;
			
			$prepared = array();
			$rows = $columns = 3;
			
			//prepare values based on the sc array
			if($values)
			{
    			foreach($values as $value)
    			{
    				switch($value['tag'])
    				{
    					case 'av_cell': $prepared['cells'][] = array('content' => stripslashes($value['content']), 'col_style' => $value['attr']['col_style']);  break;
    					case 'av_row': $prepared['rows'][] = array('row_style' => $value['attr']['row_style']);  break;
    				
    				}
    			}
			}
			
			if($prepared)
			{
				$rows = count($prepared['rows']);
				$columns = count($prepared['cells']) / $rows;
			}
			
			$params = array('class'=>'', 'parent_class' => $parent);
			
			$output  = "";
			$output .= "<div class='avia-table-builder-wrapper'>";
			
			$output .= "	<div class='avia-table-builder-add-buttons'>";
			$output .= "		<a class='avia-attach-table-row button button-primary button-large'>".__('Add Table Row','avia_framework' )."</a>";
			$output .= "		<a class='avia-attach-table-col button button-primary button-large'>".__('Add Table Column','avia_framework' )."</a>";
			$output .= "	</div>";
			
			$output .= "	<div class='avia-table'>";
			
			
			$output .= self::table_row(false, $columns,  array('class'=>'avia-table-col-style avia-attach-table-col-style avia-noselect', 'col_option' => true, 'no-edit' => true), $element, $prepared);
			
			for($i = 1; $i <= $rows; $i++)
			{
				if($prepared)
				{
					$params['row_style'] = $prepared['rows'][$i-1]['row_style'];
				}
				
				$output .= self::table_row($i, $columns, $params, $element, $prepared);
			}

			$output .= self::table_row(false, $columns,  array('class'=>'avia-template-row'), $element , $prepared);
			$output .= self::table_row(false, $columns,  array('class'=>'avia-delete-row avia-noselect', 'no-edit' => true), $element);
				
			$output .= "	</div>";
			$output .= "</div>";
			return $output;
		}
		
		static function table_row($row, $columns, $params, $element, $prepared = array())
		{
			$up 	= __('move up', 'avia_framework');
			$down 	= __('move down', 'avia_framework');
			$left 	= __('move left', 'avia_framework');
			$right 	= __('move right', 'avia_framework');
		
		
			$defaults = array('class'=>'', 'content'=>'', 'row_style'=>'');
			$params = array_merge($defaults, $params);
			$extraclass = "";
			$output  = "";
			$output .= "	<div class='avia-table-row  {$params['class']} {$params['row_style']}'>";
			
			$output .= "	<div class='avia-table-cell avia-table-cell-style avia-attach-table-row-style avia-noselect'>";
			
			if(empty($params['no-edit']))
			{
				$output .= "<div class='avia-move-table-row-container'>
							<div class='avia-move-table-row'>
							<a href='#' class='av-table-pos-button av-table-up' data-direction='up' title='{$up}'>{$up}</a>
							<a href='#' class='av-table-pos-button av-table-down' data-direction='down' title='{$down}'>{$down}</a>
							</div></div>";
			}
			
			$output .= self::select(array('std'=>$params['row_style'], 'subtype'=>$element['row_style'], 'id'=>'row_style', 'class'=>''));
			$output .= "	</div>";
			
			for($j = 1; $j <= $columns; $j++)
			{
				if($prepared)
				{
					if(!$row) $row = 1;
				
					$rows = count($prepared['rows']);
					$columns = count($prepared['cells']) / $rows;
					$key = (($row - 1) * $columns) + ($j -1);
					
					if($params['class'] == 'avia-template-row')
					{
						$params['content'] = "";
					}
					else
					{
						$params['content'] = $prepared['cells'][$key]['content'];
					}
					$extraclass = $prepared['cells'][$key]['col_style'];
				}
				
				if(isset($params['col_option']))
				{
					$params['content']   = "<div class='avia-move-table-col'>
											<a href='#' class='av-table-pos-button av-table-left'  data-direction='left' title='{$left}'>{$left}</a>
											<a href='#' class='av-table-pos-button av-table-right' data-direction='right' title='{$right}'>{$right}</a>
											</div>";
											
					$params['content']  .= self::select(array('std'=>$extraclass, 'subtype'=>$element['column_style'], 'id'=>'column_style', 'class'=>''));
				}
				
				if(isset($params['parent_class']) && $params['row_style'] == "avia-button-row" && strpos($params['content'], "[") !== false)
				{
				
					$params['parent_class']->builder->text_to_interface($params['content']);
					$values = end($_POST['extracted_shortcode']);
					$params['content'] = $params['parent_class']->builder->shortcode_class[$params['parent_class']->builder->shortcode[$values['tag']]]->prepare_editor_element( $values['content'], $values['attr'] );
					
				}
				
				$output .= "	<div class='avia-table-cell ".$extraclass."'>";
				$output .= "		<div class='avia-table-content'>";
				$output .= 	stripslashes($params['content']);
				$output .= "		</div>";
				
				if(empty($params['no-edit']) && empty($values))
				{
					$output .= "		<textarea class='avia-table-data-container' name='content'>";
					$output .= 	stripslashes($params['content']);;
					$output .= 			"</textarea>";
				}
				$output .= "	</div>";
			}
			
			$output .= "	<div class='avia-table-cell avia-table-cell-delete avia-attach-delete-table-row avia-noselect'>";
			$output .= "	</div>";
			
			
			
			$output .= "	</div>";
			return $output;
		}
		
		
		static function display_image($img = "")
		{
			$final = array();
			
			if(preg_match('/^.*\.(jpg|jpeg|png|gif)$/i', $img))
			{
				$final[] = '<img src="'.$img.'" />';
			}
			else if(!empty($img))
			{
				$args = array('post_type' => 'attachment','numberposts' => -1, 'include'=> $img, 'orderby' => 'post__in');
				$attachments = get_posts( $args );
				
				foreach ( $attachments as $attachment ) 
				{
					$final[] = wp_get_attachment_link($attachment->ID, 'thumbnail', false, false);
				}				
			}
		
			$output = "";
			$hidden = "avia-hidden";
			
			$output .= "<div class='avia-builder-prev-img-container-wrap'>";
			$output .= "<div class='avia-builder-prev-img-container'>";
			if(!empty($final))
			{
				if(count($final) == 1) $hidden = "";
				
				foreach ( $final as $img ) 
				{
					$output .= "<span class='avia-builder-prev-img'>{$img}</span>";
				}
			}
			
			$output .= "</div>";
			$output .= "</div>";
			$output .= "<a href='#delete' class='avia-delete-image {$hidden}'>".__('Remove Image', 'avia_framework' )."</a>";
			return $output;
		}
		
		
		
		
		static function number_array($from = 0, $to = 100, $steps = 1, $array = array(), $label = "", $value_prefix = "", $value_postfix = "")
		{
			for ($i = $from; $i <= $to; $i += $steps) {
			    $array[$i.$label] = $value_prefix.$i.$value_postfix;
			}
		
			return $array;
		}




		static function linking_options()
		{
		    if(current_theme_supports('avia_rel_nofollow_for_links'))
			{
			    $linkoptions = array(
					__('Open in same window',  'avia_framework' ) =>'',
					__('Open in same window and use rel=nofollow',  'avia_framework' ) =>'nofollow',
					__('Open in new window',  'avia_framework' ) =>'_blank',
					__('Open in new window and use rel=nofollow',  'avia_framework' ) =>'_blank nofollow'
			    );
			}
			else
			{
			    $linkoptions = array(
					__('Open in same window',  'avia_framework' ) =>'',
					__('Open in new window',  'avia_framework' ) =>'_blank'
			    );
			}

		    return $linkoptions;
		}
		
        /**
		 * Returns an option array of all registered post types
		 * 
		 * @param array $args
		 * @return array
		 */
		static function get_registered_post_type_array( $args = array() )
        {
            $post_types = get_post_types( $args, 'objects' );
            $post_type_option = array();

            if( ! empty( $post_types ) )
            {
                foreach( $post_types as $post_type )
                {
					/**
					 * Fixes a bug with non unique labels  
					 */
					if( ! isset( $post_type_option[ $post_type->label ] ) )
					{
						$post_type_option[ $post_type->label ] = $post_type->name;
					}
					else
					{
						$post_type_option[ "{$post_type->label} ({$post_type->name})" ] = $post_type->name;
					}
                }
            }
            
            $post_type_option = apply_filters( 'avf_registered_post_type_array', $post_type_option, $args );

            return $post_type_option;
        }


	} // end class

} // end if !class_exists









