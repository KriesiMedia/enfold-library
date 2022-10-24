<?php
/**
 * This file holds the avia_form class which is needed to build contact and other forms for the website
 *
 * @todo: improve backend so users can build forms on the fly, add aditional elements like selects, checkboxes and radio buttons
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright ( c ) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 */
if( ! defined( 'AVIA_FW' ) )	{	exit( 'No direct script access allowed' );	}


/**
 * AVIA Form

 * A simple class that is able to build and submit contact forms with the help of simple arrays that are passed to the form
 * It is build in a way that ajax sending is easily possible, but also works without javascript
 *
 */

if( ! class_exists( 'avia_form' ) )
{
	class avia_form
	{
		/**
		 * This array holds some default parameters for each form that gets created
		 * @var array
		 */
		var $form_params;

		/**
		 * This array holds the form elements that where set by the create elements function
		 * @var array
		 */
		var $form_elements;

		/**
		 * This string holds the fnal html output
		 * @var string
		 */
		var $output = '';


		/**
		 * This string holds the html output for elements that gets merged with the final output in case an error occurred or no submission took place
		 * @var string
		 */
		var $elements_html = '';


		/**
		 * This variable holds the information if we should display the form or not. it has to be displayed if an error occurs wihle validating or if no submission took place yet
		 * @var bool
		 */
		var $submit_form = true;

		/**
		 * Flag that indicates a php validation error of form elements (needed to show an error message in frontend as we only return the result field)
		 *
		 * @since 4.6.4
		 * @var boolean
		 */
		protected $validation_error;

		/**
		 * This variable holds the information if we should check the form elements or not
		 * @var bool
		 */
		var $do_checks = true;

		/**
		 * Array that holds the auto responder field
		 * @var array
		 */
		var $autoresponder = array();

		/**
		 * Static var that counts the numbers of forms and if one is submitted makes sure that the others arent checked
		 * @var int
		 */
		static $form_id = 1;

		/**
         * Stores the length of the field names and $_POST variable length
         * @var int
         */
        var $length = 20;

        /**
         * Stores the width of the current row of elements
         * @var int
         */
        var $width = 1;

        /**
         * Show the element names either as label or as placeholder attribute
         * @var int
         */
        var $placeholder = false;

        /**
         * array that translates the passed width to a numeric value
         * @var array
         */
        var $width_translate = array('fullwidth'=>1, 'element_half' => 0.5, 'element_fourth' => 0.25, 'element_third' => 0.3, 'element_two_third' => 0.6, 'element_three_fourth' => 0.75);

        /*overwrite the send function*/
        var $execute = '';

        /*error message that can be displayed in front of form*/
        var $error_msg;

		/**
		 *
		 * @since 4.5.7.2
		 * @var string
		 */
		protected $button_html_before;

		/**
		 *
		 * @since 4.5.7.2
		 * @var string
		 */
		protected $button_html_after;


		/**
		 * Message that is shown in the response area after submitting with ajax
		 *
		 * @since 4.5.7.2
		 * @var string
		 */
		public $submit_error;

		/**
         * Constructor
         *
         * The constructor sets up the default params
         * @param array $params array with default form information such as submit button label, heading and success message
         */
		public function __construct( $params )
		{
			add_filter( 'avf_safe_string_trans', array( $this, 'remove_invalid_chars' ), 10, 3 );

			$this->form_params = $params;
			if( ! isset( $this->form_params['el-id'] ) )
			{
				$this->form_params['el-id'] = '';
			}

			$this->formID = avia_form::$form_id ++;
			$this->form_params['avia_formID'] = $this->formID;

			$this->form_elements = array();

			$this->id_sufix = isset( $params['multiform'] ) ? '_' . $this->formID : '';
			$this->placeholder = ! empty( $params['placeholder'] ) ? true : false;
			$this->error_msg = '';
			$this->submit_error = '';
			$this->button_html_before = '';
			$this->button_html_after = '';
			$this->validation_error = false;

			$extraClass  = isset( $params['form_class'] ) ? $params['form_class'] : '';
			$redirect    = isset( $params['redirect'] ) ? "data-avia-redirect='{$params['redirect']}'" : '';

			$form_class  = apply_filters( 'avf_ajax_form_class', 'avia_ajax_form', $this->formID, $this->form_params );
			$form_class .= $this->placeholder ? ' av-form-labels-hidden ' : ' av-form-labels-visible ';
			$form_data = '';

			$aria_label = ! empty( $this->form_params['aria_label'] ) ? " aria-label='{$this->form_params['aria_label']}' " : '';

			if( isset( $this->form_params['form_data'] ) )
			{
				foreach( $this->form_params['form_data'] as $datakey => $dataval )
				{
					$form_data .= " data-{$datakey}='{$dataval}'" ;
				}
			}

			$this->output  = '<form action="' . $params['action'] . '" method="post" ' . $form_data . ' class="' . $form_class . ' ' . $extraClass . '" data-avia-form-id="' . $this->formID . '" ' . $redirect . ' ' . $this->form_params['el-id'] . $aria_label . '>';
			$this->output .=	$params['heading'];
			$this->output .=	'<fieldset>';

			$this->length = apply_filters( 'avf_form_el_name_length', 30, $this->formID, $this->form_params );
			$this->length = (int) $this->length;

			if( ! isset( $_POST ) || ! count( $_POST ) || empty( $_POST[ 'avia_generated_form' . $this->formID ] ) )
			{
				$this->submit_form = false; //dont submit the form
				$this->do_checks   = false; //dont do any checks on the form elements
			}

			if( ! empty( $params['custom_send'] ) )
			{
				$this->execute = $params['custom_send'];
			}
			else
			{
				$this->execute = array( $this, 'send' );
			}

			$this->submit_attr = apply_filters( 'avf_contact_form_submit_button_attr', '', $this->formID, $this->form_params );
		}


		/**
		 * @since 4.6.4
		 */
		public function __destruct()
		{
			unset( $this->form_params );
			unset( $this->form_elements );
			unset( $this->autoresponder );
			unset( $this->width_translate );
		}


		/**
         * remove additional characters with the save_string filter function which won't work if used for the field names
         */
        public function remove_invalid_chars( $trans, $string, $replace )
        {
            $trans['\.'] = '';
            return $trans;
        }

        /**
         * get a custom for button or captcha element based on the current $width
         */
        protected function auto_width()
        {
        	$class = '';

        	if( $this->width <= 0.75 )
			{
				$class = 'form_element_fourth';
			}

        	if( $this->width <= 0.6 )
			{
				$class = 'form_element_third';

			}

        	if( $this->width <= 0.5 )
			{
				$class = 'form_element_half';
			}

        	if( $this->width <= 0.3 )
			{
				$class = 'form_element_two_third';
			}

        	if( $this->width <= 0.25 )
			{
				$class = 'form_element_three_fourth';
			}

        	if( ! empty( $class ) )
			{
				$this->width = 1;
			}

        	return $class;
        }


		/**
         * create_elements
         *
         * The create_elements method iterates over a set of elements passed and creates the according form element in the frontend
         * @param array $elements array with elements that should eb created
         */
		public function create_elements( $elements )
		{
			$this->form_elements = $elements;
			$iterations = 0;
			$width = '';
			$counter = 0;
			$el_count = count( $elements ) - 1;

			foreach( $elements as $key => $element )
			{
				$counter ++;
				if( isset( $element['id'] ) )
				{
					$key = $element['id'];
				}

				if( isset( $element['type'] ) && method_exists( $this, $element['type'] ) )
				{
					$element_id = avia_backend_safe_string( 'avia_' . $key, '_', true );

					if( $element_id == 'avia_' || ! empty( $this->form_params['numeric_names'] ) )
					{
						$iterations ++;
						$element_id = 'avia_' . $iterations;
					}

					$element_id = avia_backend_truncate( $element_id, $this->length, '_', '', false, '', false );

					if( empty( $element['class'] ) )
					{
						$element['class'] = '';
					}

					if( in_array( $element['type'], array( 'headline', 'empty_line' ) ) )
					{
						$element['width'] = 'fullwidth';
					}
					else if( empty( $element['width'] ) )
					{
						$element['width'] = 'fullwidth';
					}

					$add = $this->width_translate[ $element['width'] ];

					if( $element['type'] != 'decoy' && $element['type'] != 'captcha' )
					{
						$this->width += $add;
						if( $this->width > 1 )
						{
							$this->width = $add;
							$element['class'] .= ' first_form ';
						}
					}

					$element['class'] .= ! empty( $element['width'] ) ? ' form_element form_' . $element['width'] : '';

					if( $el_count - $counter === 0 )
					{
						$element['class'] .= ' av-last-visible-form-element';
					}

					$element = apply_filters( 'avf_form_el_filter', $element, $this->formID, $this->form_params );
					$this->{$element['type']}( $element_id . $this->id_sufix, $element );
				}
			}
		}


		/**
         * display_form
         *
         * Checks if an error occurred and if the user tried to send, if thats the case, and if sending worked display a success message, otherwise display the whole form
		 *
		 * @param boolean $return
		 * @return string
         */
		public function display_form( $return = false )
		{
			$success = '<div id="ajaxresponse' . $this->id_sufix . '" class="ajaxresponse ajaxresponse' . $this->id_sufix . ' hidden"></div>';

			$call_instance = $this->execute[0];
			$call_function = $this->execute[1];


			$form = $this->elements_html;
			if( empty( $this->button_html ) )
			{
				$this->button( false, array() ); // generate a default button if none are defined via the form builder
				$form .= $this->button_html;
			}

			/**
			 * Changed with 4.5.7.2 because returning false on submit broke output of success panel in contact form.
			 * Logic kept for mailchimp.
			 */
			if( $this->submit_form )
			{
				$submit_result = $call_instance->$call_function( $this );

				if( false === $submit_result && ! empty( $this->error_msg ) )
				{
					//	Output error message prior to form ( - mailchimp error )
					$this->output .= $this->error_msg;
					$this->output .= $form;
				}
				else
				{
					//	Output only the messages and hide form
					if( ! empty( $this->error_msg ) )
					{
						$this->submit_error = $this->error_msg + '<br /><br />' . $this->submit_error;
					}

					$class = ! empty( $this->submit_error ) ? ' avia-form-error ' : '';

					$success =		'<div id="result_ajax_response' . $this->id_sufix . '" class="ajaxresponse ajaxresponse' . $this->id_sufix . $class . '">';

					if( false === $submit_result )
					{
						$success .= ! empty( $this->submit_error ) ? $this->submit_error : __( 'Form could not be submitted - an error occurred. Please reload page and try again.', 'avia_framework' );
					}
					else
					{
						 $success .= $this->form_params['success'];
					}

					 $success .=	'</div>';
				}
			}
			else
			{
				$this->output .= $this->error_msg;
				$this->output .= $form;

				if( $this->validation_error )
				{
					$success  = '<div id="result_ajax_response' . $this->id_sufix . '" class="ajaxresponse ajaxresponse' . $this->id_sufix . ' avia-form-error">';
					$success .=		__( 'Sorry, but the form could not be submitted - an error was found in your input. Please reload the page and try again.', 'avia_framework' );
					$success .= '</div>';
				}
			}

			$this->output .= '</fieldset>';

			if( ! empty( $this->form_params['captcha'] ) && in_array( $this->form_params['captcha'], array( 'recaptcha_v2', 'recaptcha_v3' ) ) )
			{
				/**
				 * Add an overlay if Google reCaptcha had been activated and user disbled it with cookie
				 *
				 * @since 4.5.7.2
				 * @return string
				 */
				$msg = __( 'This contact form is deactivated because you refused to accept Google reCaptcha service which is necessary to validate any messages sent by the form.', 'avia_framework' );
				$msg = apply_filters( 'avf_contact_form_recaptcha_disabled_msg', $msg );

				$this->output .=	'<div class="avia-disabled-form">';
				$this->output .=		$msg;
				$this->output .=	'</div>';
			}

			$this->output .= '</form>' . $success;


			if( ! $return )
			{
				echo $this->output;
			}

			return $this->output;
		}


		/**
         * The html method creates custom html output for descriptions headings etc
		 *
         * @param string $id			holds the key of the element
         * @param array $element		data array of the element that should be created
         */
		protected function html( $id, array $element )
		{
			if( ! empty( $element['content'] ) )
			{
				$this->elements_html .= "<div id='{$id}' class='av-form-text'>{$element['content']}</div>";
				$this->width = 1;
			}
		}

		/**
		 *
		 * @since < 4.0
		 * @param string|false $id
		 * @param array $element
		 * @return string
		 */
		protected function button( $id = '', array $element = array() )
		{
			if( ! empty( $this->button_html ) )
			{
				return;
			}

			$submit_label = isset( $element['label'] ) ? $element['label'] : $this->form_params['submit'];
			$class = isset( $element['class'] ) ? $element['class'] : $this->auto_width();
			if( ! empty( $class ) )
			{
				$class .= ' modified_width';
			}
			if( ! empty( $element['disabled'] ) )
			{
				$class .= ' av-hidden-submit';
			}

			$this->button_html = $this->button_html_before;
			$this->button_html_before = '';

			$this->button_html .=	'<p class="form_element ' . $class . '">';
			$this->button_html .=		'<input type="hidden" value="1" name="avia_generated_form' . $this->formID . '" />';
			$this->button_html .=		'<input type="submit" value="' . $submit_label . '" class="button" ' . $this->submit_attr . ' data-sending-label="' . __( 'Sending', 'avia_framework' ) . '"/>';
			$this->button_html .=	'</p>';

			$this->button_html .= $this->button_html_after;
			$this->button_html_after = '';

			if( $id )
			{
				$this->elements_html .= $this->button_html;
			}
			else
			{
				return $this->button_html;
			}
		}

		/**
		 *
		 * @since 4.8.6.4
		 * @param string $id
		 * @param array $element
		 */
		protected function headline( $id, array $element )
		{
			if( isset( $element['element_display'] ) && 'e-mail' == $element['element_display'] )
			{
				return;
			}

			$this->elements_html .= "<{$element['headline_tag']} id='element_{$id}' class='{$element['class']} av-form-headline '>";
			$this->elements_html .=		esc_html( $element['label'] );
			$this->elements_html .= "</{$element['headline_tag']}>";
		}

		/**
		 *
		 * @since 4.8.6.4
		 * @param string $id
		 * @param array $element
		 */
		protected function empty_line( $id, array $element )
		{
			if( isset( $element['element_display'] ) && 'e-mail' == $element['element_display'] )
			{
				return;
			}

			$this->elements_html .= "<p id='element_{$id}' class='{$element['class']} av-form-empty-line '> </p>";
		}


		/**
		 *
		 * @param type $id
		 * @param array $element
		 */
		protected function number( $id, array $element )
		{
			$this->text( $id, $element, 'number' );
		}

		/**
         * The text method creates input elements with type text, and prefills them with $_POST values if available.
         * The method also checks against various input validation cases
		 *
         * @param string $id holds the key of the element
         * @param array $element data array of the element that should be created
         */

		protected function text( $id, array $element, $type = 'text' )
		{
			$p_class = $required = $element_class = $value = $extra = '';

			// if($element['check'] == 'is_email') $type = 'email'; //cant use this because of ie8 + 9

			if( ! empty( $element['check'] ) )
			{
				$extra = '*';
				$required = ' <abbr class="required" title="' . __( 'required', 'avia_framework' ) . '">*</abbr>';
				$element_class = $element['check'];
				$p_class = $this->check_element( $id, $element );
			}

			if( isset( $_POST[ $id ] ) )
			{
				$value = esc_html( urldecode( $_POST[ $id ] ) );
			}
			else if( ! empty( $element['value'] ) )
			{
				$value = $element['value'];
			}

			$this->elements_html .= "<p class='{$p_class} {$element['class']}' id='element_$id'>";
			$label = '<label for="' . $id . '">' . $element['label'] . $required . '</label>';
			$placeholder = '';

			if( $this->placeholder )
			{
//				$label = '';
				$placeholder = " placeholder='{$element['label']}{$extra}'" ;
			}

			$form_el = ' <input name="' . $id . '" class="text_input ' . $element_class . '" type="' . $type . '" id="' . $id . '" value="' . $value . '" ' . $placeholder . '/>';


			if( isset( $this->form_params['label_first'] ) )
			{
				$this->elements_html .= $label . $form_el;
			}
			else
			{
				$this->elements_html .= $form_el . $label;
			}

			$this->elements_html .= '</p>';
		}

        /**
         * The text method creates input elements with type datepicker, and prefills them with $_POST values if available.
         * The method also checks against various input validation cases
		 *
         * @param string $id holds the key of the element
         * @param array $element data array of the element that should be created
         */
        protected function datepicker( $id, array $element )
        {
            global $wp_locale;

            $p_class = $required = $element_class = $value = $extra = '';
			$date_format = apply_filters( 'avf_datepicker_dateformat', 'dd / mm / yy' );

			$placeholder_text = 'DD / MM / YY';

            if( ! empty( $element['check'] ) )
            {
                $required = ' <abbr class="required" title="' . __( 'required', 'avia_framework' ) . '">*</abbr>';
                $element_class = $element['check'];
                $p_class = $this->check_element( $id, $element );
            }

            if( isset( $_POST[ $id ] ) )
			{
				$value = esc_html( urldecode($_POST[ $id ] ) );
			}
			else if( ! empty( $element['value'] ) )
			{
				$value = $element['value'];
			}

			if( $this->placeholder )
			{
					//	empty label - keep default placeholder
				if( ! empty( $element['label'] ) )
				{
					$placeholder_text = $element['label'];
				}
				if( ! empty( $element['check'] ) )
				{
					$extra = '*';
				}
			}

			$placeholder = apply_filters( 'avf_datepicker_date_placeholder', $placeholder_text . $extra );


            $this->elements_html .= "<p class='{$p_class} {$element['class']}' id='element_$id'>";
            $form_el = ' <input name="' . $id . '" class="avia_datepicker text_input ' . $element_class.'" type="text" id="' . $id . '" value="' . $value . '" placeholder="' . $placeholder . '" />';
            $label = '<label for="' . $id . '">' . $element['label'] . $required . '</label>';

			if( $this->placeholder )
			{
//				$label = '';
			}

            if( isset( $this->form_params['label_first'] ) )
            {
                $this->elements_html .= $label.$form_el;
            }
            else
            {
                $this->elements_html .= $form_el.$label;
            }

            $this->elements_html .= '</p>';


            // wp_enqueue_style('jquery-ui-datepicker'); <-- removed and added own styling to frontend css styles
            wp_enqueue_script( 'jquery-ui-datepicker' );

            $args = array(
                'closeText'         => __( 'Close', 'avia_framework' ),
                'currentText'       => __( 'Today', 'avia_framework' ),
                'nextText'			=> __( 'Next', 'avia_framework' ),
				'prevText'			=> __( 'Prev', 'avia_framework' ),
                'monthNames'        => array_values( $wp_locale->month ),
                'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
                'dayNames'          => array_values( $wp_locale->weekday ),
                'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
                'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
                'dateFormat'        => $date_format,
                'firstDay'          => get_option( 'start_of_week' ),
                'isRTL'             => $wp_locale->is_rtl()
            );

            wp_localize_script( 'jquery-ui-datepicker', 'AviaDatepickerTranslation', $args );

            add_action( 'wp_footer', array( $this, 'helper_print_datepicker_script' ), 99999999 );
        }

		/**
		 *
		 */
        public function helper_print_datepicker_script()
        {
            echo "\n<script type='text/javascript'>\n";
            echo 'jQuery( function(){ jQuery(".avia_datepicker").datepicker({
            	beforeShow: function(input, inst) {
			       jQuery("#ui-datepicker-div").addClass(this.id);
			       inst.dpDiv.addClass("avia-datepicker-div");
			   },
                showButtonPanel: true,
                closeText: AviaDatepickerTranslation.closeText,
                currentText: AviaDatepickerTranslation.currentText,
                nextText: AviaDatepickerTranslation.nextText,
                prevText: AviaDatepickerTranslation.prevText,
                monthNames: AviaDatepickerTranslation.monthNames,
                monthNamesShort: AviaDatepickerTranslation.monthNamesShort,
                dayName: AviaDatepickerTranslation.dayNames,
                dayNamesShort: AviaDatepickerTranslation.dayNamesShort,
                dayNamesMin: AviaDatepickerTranslation.dayNamesMin,
                dayNames: AviaDatepickerTranslation.dayNames,
                dateFormat: AviaDatepickerTranslation.dateFormat,
                firstDay: AviaDatepickerTranslation.firstDay,
                isRTL: AviaDatepickerTranslation.isRTL,
                changeMonth: true,
				changeYear: true,
				yearRange: "c-80:c+10"
            }); });';

			echo "\n</script>\n";
        }

		/**
         * The text method creates input elements with type checkbox, and prefills them with $_POST values if available.
         * The method also checks against various input validation cases
		 *
         * @param string $id holds the key of the element
         * @param array $element data array of the element that should be created
         */
		protected function checkbox( $id, array $element )
		{
			$p_class = $required = $element_class = $checked = '';

			if( ! empty( $element['av_contact_preselect'] ) )
			{
				$checked = 'checked="checked"';
			}
			else if( ! empty( $element['check'] ) )
			{
				if( ! empty( $_POST[ $id ] ) )
				{
					$checked = 'checked="checked"';
				}

				$required = ' <abbr class="required" title="' . __( 'required', 'avia_framework' ) . '">*</abbr>';
				$element_class = $element['check'];
				$p_class = $this->check_element( $id, $element );
			}
			if( empty( $_POST[ $id ] ) )
			{
				$_POST[ $id ] = 'false';
			}

			$this->elements_html .= "<p class='{$p_class} {$element['class']}' id='element_{$id}'>";
			$this->elements_html .=		'<input ' . $checked . ' name="' . $id . '" class="input_checkbox ' . $element_class . '" type="checkbox" id="' . $id . '" value="true"/><label class="input_checkbox_label" for="' . $id . '">' . $element['label'] . $required . '</label>';
			$this->elements_html .= '</p>';
		}


		/**
         * The select method creates a dropdown element with type select, and prefills them with $_POST values if available.
         * The method also checks against various input validation cases
		 *
         * @param string $id holds the key of the element
         * @param array $element data array of the element that should be created
         */
		protected function select( $id, array $element )
		{
			if( empty( $element['options'] ) )
			{
				return;
			}

			if( ! is_array( $element['options'] ) )
			{
				$element['options'] = str_replace( array( "\,", ',,' ), "&#44;", $element['options'] );
				$element['options'] = explode( ',', $element['options'] );
			}

			$p_class = $required = $element_class = $prefilled_value = $select = $extra  = '';

			if( ! empty( $element['check'] ) )
			{
				$extra = '*';
				$required = ' <abbr class="required" title="' . __( 'required', 'avia_framework' ) . '">*</abbr>';
				$element_class = $element['check'];
				$p_class = $this->check_element( $id, $element );
			}

			if( isset( $_POST[ $id ] ) )
			{
				$prefilled_value = esc_html( urldecode( $_POST[ $id ] ) );
			}
			else if( ! empty( $element['value'] ) )
			{
				$prefilled_value = $element['value'];
			}

			if( $this->placeholder )
			{
				$label = array( $element['label'] . $extra . '|' );
				$element['options'] = array_merge( $label, $element['options'] );
			}

			foreach( $element['options'] as $i => $option )
			{
				$key = $value = trim( $option );
				$suboptions =  explode( '|', $option );

				if( is_array( $suboptions ) && isset( $suboptions[1] ) )
				{
					$key = trim( $suboptions[1] );
					$value = trim( $suboptions[0] );
				}

				$active = $value == $prefilled_value ? 'selected="selected"' : '';
				$disabled = ( $this->placeholder && 0 === $i ) ? 'class="av-placeholder" disabled="disabled"' : '';
				
				//	bug to show label on pageload for dropdown
				if( $this->placeholder && empty( $element['multi_select'] ) )
				{
					$active = ( 0 === $i ) ? 'selected="selected"' : '';
				}

				$select .= "<option {$disabled} {$active} value='{$key}'>{$value}</option>";
			}

			$multi = '';
			if( ! empty( $element['multi_select'] ) )
			{
				$multi = 'multiple';
				$element_class .= ' av-multi-select';
			}

			$this->elements_html .= "<p class='{$p_class} {$element['class']}' id='element_{$id}'>";
			$form_el = '<select ' . $multi . ' name="' . $id . '" class="select ' . $element_class . '" id="' . $id . '">' . $select . '</select>';
			$label = '<label for="' . $id . '">' . $element['label'] . $required . '</label>';

			if( $this->placeholder )
			{
//				$label = '';
			}

			if( isset( $this->form_params['label_first'] ) )
			{
				$this->elements_html .= $label . $form_el;
			}
			else
			{
				$this->elements_html .= $form_el . $label;
			}

			$this->elements_html .= '</p>';
		}


		/**
         * The textarea method creates textarea elements, and prefills them with $_POST values if available.
         * The method also checks against various input validation cases
		 *
         * @param string $id holds the key of the element
         * @param array $element data array of the element that should be created
         */
		protected function textarea( $id, array $element )
		{
			$p_class = $required = $element_class = $value = $extra = '';

			if( ! empty( $element['check'] ) )
			{
				$extra = '*';
				$required = ' <abbr class="required" title="' . __( 'required', 'avia_framework' ) . '">*</abbr>';
				$element_class = $element['check'];
				$p_class = $this->check_element( $id, $element );
			}

			if( isset( $_POST[ $id ] ) )
			{
				$value = esc_html( urldecode( $_POST[ $id ] ) );
			}
			else if( ! empty( $element['value'] ) )
			{
				$value = $element['value'];
			}

			$label = '<label for="' . $id . '" class="textare_label hidden textare_label_' . $id . '">' . $element['label'] . $required . '</label>';
			$placeholder = '';

			if( $this->placeholder )
			{
//				$label = '';
				$placeholder = " placeholder='{$element['label']}{$extra}'" ;
			}


			$this->elements_html .= "<p class='{$p_class} {$element['class']}' id='element_$id'>";
			$this->elements_html .=		$label;
			$this->elements_html .=		'<textarea ' . $placeholder . ' name="'.$id.'" class="text_area ' . $element_class . '" cols="40" rows="7" id="' . $id . '" >' . $value . '</textarea>';
			$this->elements_html .= '</p>';
		}



		/**
         * The decoy method creates input elements with type text but with an extra class that hides them
		 * The method is used to fool bots into filling the form element. Upon submission we check if the element contains any value, if so we dont submit the form
		 *
         * @param string $id holds the key of the element
         * @param array $element data array of the element that should be created
         */
		protected function decoy( $id, array $element )
		{
			$p_class = $required = $element_class = '';

			if( ! empty( $element['check'] ) )
			{
				$this->check_element( $id, $element );
			}

			$this->elements_html .= '<p class="hidden"><input type="text" name="' . $id . '" class="hidden ' . $element_class . '" id="' . $id . '" value="" /></p>';
		}

		/**
		 * Adds a Google reCAPTCHA div used by js to identify actions needed
		 *
		 * @since 4.5.7.2
		 * @param string $id
		 * @param array $element
		 */
		protected function grecaptcha( $id, array $element )
		{
			if( ! isset( $element['context'] ) || 'av_contact_form' != $element['context'] )
			{
				return;
			}

			if( Avia_Google_reCAPTCHA()->is_loading_prohibited() )
			{
				return;
			}

			$element = shortcode_atts( array(
												'class'				=> '',
												'container_class'	=> '',
												'custom_class'		=> '',
												'context'			=> 'av_contact_form',
												'token_input'		=> 'av_recaptcha_token',
												'version'			=> '',
												'theme'				=> '',
												'size'				=> '',
												'score'				=> '',
												'text_to_preview'	=> false,
												'value'				=> ''
											), $element, 'google_recaptcha_form_params' );

			if( '' == $element['theme'] )
			{
				$element['theme'] = Avia_Google_reCAPTCHA()->get_theme();
			}

			if( '' == $element['size'] )
			{
				$element['size'] = 'normal';
			}

			if( '' == $element['score'] )
			{
				$element['score'] = Avia_Google_reCAPTCHA()->get_score();
			}

			$data = '';
			foreach( $element as $key => $value )
			{
				if( in_array( $key , array( 'type', 'class' ) ) )
				{
					continue;
				}

				$data .= ' data-' . $key . '="' . esc_attr( $value ) . '"';
			}

			$output  = '';
			$output .=	"<div id='{$id}' class='av-recaptcha-area {$element['class']} {$element['container_class']}' {$data}>";

			if( true === $element['text_to_preview'] )
			{
				if( 'avia_recaptcha_v2' == $element['version'] )
				{
					$image = AVIA_IMG_URL . "misc/grecaptcha-check-{$element['theme']}-{$element['size']}.png";
					$title = __( 'A non functional image only preview for checkbox of Google reCAPTCHA', 'avia_framework' );
					$alt =  __( 'Checkbox for Google reCAPTCHA', 'avia_framework' );
					$output .=	"<img src='{$image}' alt='{$alt}' title='{$title}' />";
				}
				else if( 'avia_recaptcha_v3' == $element['version'] )
				{
					$badge = Avia_Google_reCAPTCHA()->get_display_badge_html();
					$badge = str_replace( 'hidden', '', $badge );
					$this->button_html_after .=	$badge;
				}
			}
			else
			{
				$output .=	Avia_Google_reCAPTCHA()->get_display_badge_html();
			}

			$output .=	'</div>';

			$this->elements_html .= $output;
		}


		/**
         * The captcha method creates input element that needs to be filled  correctly to send the form
		 *
         * @param string $id holds the key of the element
         * @param array $element data array of the element that should be created
         */
		protected function captcha( $id, array $element )
		{
			$p_class = $required = $element_class = $value = $valueVer = '';

			if( ! empty( $element['check'] ) )
			{
				$required = ' <abbr class="required" title="' . __( 'required', 'avia_framework' ) . '">*</abbr>';
				$element_class = $element['check'];
				$p_class .= $this->check_element( $id, $element );
			}

			$p_class .= ' ' . $this->auto_width() . ' ' . $element['class'];

			if( ! empty( $_POST[ $id ] ) )
			{
				$value = esc_html( urldecode( $_POST[ $id ] ) );
			}

			if( ! empty( $_POST[ $id . '_verifier' ] ) )
			{
				$valueVer = esc_html( urldecode( $_POST[ $id . '_verifier' ] ) );
			}

			if( ! $valueVer )
			{
				$valueVer = str_replace( '0', '4', str_replace( '9', '7', rand( 123456789, 999999999 ) ) );
			}

			$reverse = strrev( $valueVer );
			$enter = $valueVer[ $reverse[0] ];
			$number_1 = rand( 0, $enter );
			$number_2 = $enter - $number_1;

			$this->elements_html .= "<p class='{$p_class}' id='element_{$id}'>";
			$this->elements_html .=		"<span class='value_verifier_label'>{$number_1} + {$number_2} = ?</span>";
			$this->elements_html .=		'<input name="' . $id . '_verifier" type="hidden" id="' . $id . '_verifier" value="' . $valueVer . '"/>';

			$form_el = '<input name="' . $id . '" class="text_input ' . $element_class . '" type="text" id="' . $id . '" value="' . $value . '"/>';
			$label = '<label for="' . $id . '">' . $element['label'] . $required . '</label>';

			if( isset( $this->form_params['label_first'] ) )
			{
				$this->elements_html .= $label . $form_el;
			}
			else
			{
				$this->elements_html .= $form_el . $label;
			}

			$this->elements_html .= '</p>';
		}



		/**
         * The hidden method creates input elements with type hidden, and prefills them with values if available.
		 *
         * @param string $id holds the key of the element
         * @param array $element data array of the element that should be created
         */
		protected function hidden( $id, array $element )
		{
			$this->elements_html .= '<input type="hidden" name="' . $id . '" id="' . $id . '" value="' . $element['value'] . '" />';
		}


		/**
         * Send the form
         *
         * The send method tries to send the form. It builds the necessary email and submits it via wp_mail
		 *
		 * @param array $self_instance					deprecated ????
		 * @return boolean
         */
		public function send( $self_instance )
		{
			$new_post = array();
			foreach( $_POST as $key => $post )
			{
				$new_post[ str_replace( 'avia_', '', $key ) ] = $post;
			}

			$mymail = empty( $this->form_params['myemail'] ) ? $new_post['myemail'] : $this->form_params['myemail'];
			$myblogname = empty( $this->form_params['myblogname'] ) ? $new_post['myblogname'] : $this->form_params['myblogname'];

			if( empty( $new_post[ 'subject_' . $this->formID ] ) && ! empty( $this->form_params['subject'] ) )
			{
				$new_post[ 'subject_' . $this->formID ] = $this->form_params['subject'];
			}

			$subject = empty( $new_post[ 'subject_'.$this->formID ] ) ? __( 'New Message', 'avia_framework' ) . ' (' . __( 'sent by contact form at', 'avia_framework' ) . ' ' . $myblogname . ')'  : $new_post[ 'subject_' . $this->formID ];

			$default_from = parse_url( home_url() );

			/**
			 * Hook to stop execution here and do something different with the data
			 * Set $this->submit_error to display a custom submit message
			 *
			 * @used_by						av_google_recaptcha						999999
			 * @since < 4.0
			 * @param boolean
			 * @param array $new_post
			 * @param array $form_params
			 * @param avia_form $this
			 * @return boolean|null				null on submit error|false to stop sending
			 */
			$proceed = apply_filters( 'avf_form_send', true, $new_post, $this->form_params, $this );

			if( ! $proceed )
			{
				if( is_null( $proceed ) )
				{
					return false;
				}
				else
				{
					return true;
				}
			}

			//set the email adress
			$from = 'no-reply@wp-message.com';
			$usermail = false;

			if( ! empty( $default_from['host'] ) )
			{
				$from = 'no-reply@' . $default_from['host'];
			}

			if( ! empty( $this->autoresponder[0] ) )
			{
				$from = $_POST[ $this->autoresponder[0] ];
				$usermail = true;
			}
			else
			{
				$email_variations = array( 'e-mail', 'email', 'mail' );

				foreach( $email_variations as $key )
				{
					foreach( $new_post as $current_key => $current_post )
					{
						if( strpos( $current_key, $key ) !== false )
						{
							$from = $new_post[ $current_key ];
							$usermail = true;
							break;
						}

					}

					if( $usermail )
					{
						break;
					}
				}
			}

			$to = urldecode( $mymail );
			$delimiter = ( strpos( $to, ',' ) === false && strpos( $to, ' ' ) !== false ) ? ' ' : ',';
			$to = array_filter( array_map( 'trim', explode( $delimiter, $to ) ) );
			$to = apply_filters( 'avf_form_sendto', $to, $new_post, $this->form_params );

			$from = urldecode( $from );
			$send_from = ! empty( $this->form_params['myfrom'] ) ? urldecode( $this->form_params['myfrom'] ) : $from;
			$from_filtered = apply_filters( 'avf_form_from', $send_from, $new_post, $this->form_params );

			$subject = urldecode( $subject );
			$subject = apply_filters( 'avf_form_subject', $subject, $new_post, $this->form_params );

			$subject = htmlspecialchars_decode( $subject );

			$message = '';
			$iterations = 0;

			foreach( $this->form_elements as $key => $element )
			{
				if( isset( $element['id'] ) )
				{
					$key = $element['id'];
				}

				$key = avia_backend_safe_string( $key, '_', true );

				if( empty( $key ) || ! empty( $this->form_params['numeric_names'] ) )
				{
					$iterations ++;
					$key = $iterations;
				}

				// substract 5 characters from the string length because we removed the avia_ prefix with 5 characters at the beginning of the send() function
				$key = avia_backend_truncate( $key, $this->length - 5, '_', '', false, '', false );

				$key .= $this->id_sufix;

				//	add other elements with static content
				if( 'empty_line' == $element['type'] )
				{
					if( ! ( isset( $element['element_display'] ) && 'frontend' == $element['element_display'] ) )
					{
						$new_post[ $key ] = 'xxx';
					}
				}

				if( 'headline' == $element['type'] )
				{
					if( ! ( isset( $element['element_display'] ) && 'frontend' == $element['element_display'] ) )
					{
						$new_post[ $key ] = $element['label'];
					}
				}

				if( ! empty( $new_post[ $key ] ) )
				{
					if( $element['type'] != 'hidden' && $element['type'] != 'decoy' )
					{
						$form_field = '';
						$label = trim( $element['label'] ) != '' ? $element['label'] . ': ' : '';

						if( $element['type'] == 'textarea' )
						{
							$form_field .= ' <br />';
							if( ! empty( $label ) )
							{
								$form_field .= $label . '<br />';
								$label = '';
							}
						}

						/**
						 * @since 4.8.7
						 * @param string $label
						 * @param array $new_post
						 * @param array $this->form_elements
						 * @param array $this->form_params
						 * @param array $element
						 * @param string $key
						 * @return string
						 */
						$label = apply_filters( 'avf_form_mail_label', $label, $new_post, $this->form_elements, $this->form_params, $element, $key );

						/**
						 * @since ???
						 * @param string $field_value
						 * @param array $new_post
						 * @param array $this->form_elements
						 * @param array $this->form_params
						 * @param array $element
						 * @param string $key
						 * @return string
						 */
						$field_value = apply_filters( 'avf_form_mail_field_values', nl2br( urldecode( $new_post[ $key ] ) ), $new_post, $this->form_elements, $this->form_params, $element, $key );

						if( 'empty_line' == $element['type'] )
						{
							$form_field .= '<br />';
						}
						else if( 'headline' == $element['type'] )
						{
							$form_field .= "<{$element['headline_tag']}>{$field_value}</{$element['headline_tag']}>";
						}
						else
						{
							$form_field .= $label . $field_value . ' <br />';
						}

						if( $element['type'] == 'textarea' )
						{
							$form_field .= ' <br />';
						}

						$message .= apply_filters( 'avf_form_mail_form_field', $form_field, $new_post, $this->form_elements, $this->form_params, $element, $key, $field_value );
					}
				}
			}

			$use_wpmail = apply_filters( 'avf_form_use_wpmail', true, $new_post, $this->form_params );

			$mail_array = array();

			// $header_array['MIME-Version'] = '1.0';
			$mail_array['Content-type'] = 'text/html; charset=utf-8';

			if( $use_wpmail )
			{
				$mail_array['From'] = "{$from_filtered} <{$from_filtered}>";
			}
			else
			{
				$mail_array['From'] = $from_filtered;
			}

			if( $usermail )
			{
				$mail_array['Reply-To'] = $from;
			}

			$mail_array['To'] = $to;
			$mail_array['Subject'] = $subject;
			$mail_array['Message'] = stripslashes( $message );

			/**
			 * Allows bulk changing of mail content
			 *
			 * @since 4.6.4
			 * @param array $header_array
			 * @param array $new_post
			 * @param array $this->form_params
			 * @param avia_form $this
			 * @param string $from
			 * @param string $from_filtered
			 * @return array
			 */
			$mail_array = apply_filters( 'avf_contact_form_incoming_mail', $mail_array, $new_post, $this->form_params, $this, $from, $from_filtered );

			/**
			 * For backwards comp. we extract again and call deprecated filters - can be removed in future versions
			 */
			if( isset( $mail_array['To'] ) )
			{
				$to = $mail_array['To'];
			}
			if( isset( $mail_array['Message'] ) )
			{
				$message = $mail_array['Message'];
			}

			/**
			 * Add keys to remove from $mail_array when forming the header string
			 *
			 * @since 4.6.4
			 * @param array
			 * @param string $context
			 * @return array
			 */
			$skip_mail_array_keys = apply_filters( 'avf_skip_mail_header_keys', array( 'To', 'Subject', 'Message' ), 'incoming_mail' );

			$header = '';
			foreach( $mail_array as $header_key => $header_value )
			{
				if( in_array( $header_key, $skip_mail_array_keys ) )
				{
					continue;
				}

				$header .= $header_key . ': ' . $header_value . " \r\n";
			}

			$header = apply_filters_deprecated( 'avf_form_mail_header', array( $header, $new_post, $this->form_params, $from, $from_filtered ), '4.6.4', 'avf_contact_form_incoming_mail', __( 'Allows stack modifications', 'avia_framework' ) );
			$mail_array['To'] = apply_filters_deprecated( 'avf_form_copy', array( $to, $new_post, $this->form_params ), '4.6.4', 'avf_contact_form_incoming_mail', __( 'Allows stack modifications', 'avia_framework' ) );
			$mail_array['Message'] = apply_filters_deprecated( 'avf_form_message', array( $message, $new_post, $this->form_params ), '4.6.4', 'avf_contact_form_incoming_mail', __( 'Allows stack modifications', 'avia_framework' ) );

			foreach( $mail_array['To'] as $send_to_mail )
			{
				//if a demo email is mistakenly used change it to the admin url
				if( strpos( $send_to_mail, '@kriesi.at' ) !== false && isset( $_SERVER['HTTP_HOST'] ) && $_SERVER['HTTP_HOST'] != 'www.kriesi.at' )
				{
					if( ! defined( 'AV_TESTSERVER' ) )
					{
						$send_to_mail = get_option( 'admin_email' );
					}
				}

				if( $use_wpmail )
				{
					wp_mail( $send_to_mail, $mail_array['Subject'], $mail_array['Message'], $header );
				}
				else
				{
					mail( $send_to_mail, $mail_array['Subject'], $mail_array['Message'], $header );
				}
			}

			/**
			 * Allow to add a custom autoresponder - must return complete HTML.
			 * The returned string is packed in a div and only HTML structure of string is validated.
			 *
			 * @since v4.0.6
			 */
			$custom_autoresponder = apply_filters( 'avf_form_custom_autoresponder', '', $message, $this, $new_post );

			if( $usermail && ( ! empty( $this->form_params['autoresponder'] ) || ! empty( $custom_autoresponder ) ) )
			{
				$mail_array = array();

				// $header_array['MIME-Version'] = '1.0';
				$mail_array['Content-type'] = 'text/html; charset=utf-8';

				$delimiter = ( strpos( $this->form_params['autoresponder_email'], ',' ) === false && strpos( $this->form_params['autoresponder_email'], ' ' ) !== false ) ? ' ' : ',';
				$autoresponder_email = array_filter( array_map( 'trim', explode( $delimiter, $this->form_params['autoresponder_email'] ) ) );
				if( is_array( $autoresponder_email ) )
				{
					$autoresponder_email = $autoresponder_email[0];
				}

				/**
				 * @since 4.4.2
				 */
				$autoresponder_email = apply_filters_deprecated( 'avf_form_autoresponder_email_from', array( $autoresponder_email, $new_post, $this->form_params ), '4.6.4', 'avf_contact_form_autoresponder_mail', __( 'Allows stack modifications', 'avia_framework' ) );

				/**
				 * @since 4.5.7.2
				 */
				$from_prefix = get_bloginfo( 'name' );
				$from_prefix = apply_filters( 'avf_form_autoresponder_email_from_prefix', $from_prefix, $new_post, $this->form_params );
				$from_prefix = htmlspecialchars_decode( $from_prefix );

				if( $use_wpmail )
				{
					$auto_from = "{$from_prefix} <" . urldecode( $autoresponder_email ) . '>';
				}
				else
				{
					$auto_from = urldecode( $autoresponder_email );
				}

				$mail_array['From'] = $auto_from;

				if( ! empty( $this->form_params['autoresponder_reply_to'] ) )
				{
					$mail_array['Reply-To'] = $this->form_params['autoresponder_reply_to'];
				}

				/**
				 * @since 4.4.2
				 */
				$autoresponder_to = $from;
				$autoresponder_to = apply_filters_deprecated( 'avf_form_autoresponder_from', array( $autoresponder_to, $new_post, $this->form_params ), '4.4.2', 'avf_form_autoresponder_to', __( 'Inconsistent usage of filter name.', 'avia_framework' ) );
				$mail_array['To'] = apply_filters_deprecated( 'avf_form_autoresponder_to', array( $autoresponder_to, $new_post, $this->form_params ), '4.6.4', 'avf_contact_form_autoresponder_mail', __( 'Allows stack modifications', 'avia_framework' ) );

				$mail_array['Subject'] = $this->form_params['autoresponder_subject'];

				if( ! empty( $custom_autoresponder ) )
				{
					$message = '<div class="avia_custom_autoresponder">' . wp_kses_post( balanceTags( $custom_autoresponder, true ) ) . '</div>';
				}
				else
				{
					$mess  = nl2br( $this->form_params['autoresponder'] );
					$mess .= '<br /><br /><br /><strong>' . __( 'Your Message:','avia_framework' ) . ' </strong><br /><br />';
					$mess .= $message;

					if( ! empty( $this->form_params['autoresponder_after'] ) )
					{
						$mess .= '<br />';
						$mess .= nl2br( $this->form_params['autoresponder_after'] ) . '<br /><br />';
					}

					$message = apply_filters_deprecated( 'avf_form_autorespondermessage', array( $mess ), '4.6.4', 'avf_contact_form_autoresponder_mail', __( 'Allows stack modifications', 'avia_framework' ) );
				}

				$mail_array['Message'] = $message;

				/**
				 * Allows bulk changing of mail content
				 *
				 * @since 4.6.4
				 * @param array $header_array
				 * @param array $new_post
				 * @param array $this->form_params
				 * @param avia_form $this
				 * @return array
				 */
				$mail_array = apply_filters( 'avf_contact_form_autoresponder_mail', $mail_array, $new_post, $this->form_params, $this );

				/**
				 * Add keys to remove from $mail_array when forming the header string
				 *
				 * @since 4.6.4
				 * @param array
				 * @param string $context
				 * @return array
				 */
				$skip_mail_array_keys = apply_filters( 'avf_skip_mail_header_keys', array( 'To', 'Subject', 'Message' ), 'autoresponder_mail' );

				$header = '';
				foreach( $mail_array as $header_key => $header_value )
				{
					if( in_array( $header_key, $skip_mail_array_keys ) )
					{
						continue;
					}

					$header .= $header_key . ': ' . $header_value . " \r\n";
				}

				if( $use_wpmail )
				{
					wp_mail( $mail_array['To'], $mail_array['Subject'], $mail_array['Message'], $header );
				}
				else
				{
					mail( $mail_array['To'], $mail_array['Subject'], $mail_array['Message'], $header );
				}
			}

			unset( $_POST );
			return true;
			//return wp_mail( $to, $subject, $message , $header);

		}

		/**
         * Check the value of an element
         * The check_element method creates checks if the submitted value of a post element is valid
		 *
         * @param string $id holds the key of the element
         * @param array $element data array of the element that should be created
		 * @return string					'valid' | 'error'
         */
		protected function check_element( $id, array $element )
		{
			if( isset( $_POST ) && count( $_POST ) && isset( $_POST[ $id ] ) && $this->do_checks )
			{
				switch( $element['check'] )
				{
					case 'is_empty':
						if( ! empty( $_POST[ $id ] ) || $_POST[ $id ] === '0' )
						{
							return 'valid';
						}
						break;
					case 'must_empty':
						if( isset( $_POST[ $id ] ) && $_POST[ $id ] == '')
						{
							return 'valid';
						}
						break;
					case 'is_email':
						$this->autoresponder[] = $id;
						if( preg_match( "!^[\w|\.|\-]+@\w[\w|\.|\-]*\.[a-zA-Z]{2,20}$!", urldecode( $_POST[ $id ] ) ) )
						{
							return 'valid';
						}
						break;
					case 'is_ext_email':
						$this->autoresponder[] = $id;
						if( preg_match( "!^[\w\.\-ÄÖÜäöü]+@\w[\w\.\-ÄÖÜäöü]*\.[a-zA-Z]{2,20}$!", urldecode( $_POST[ $id ] ) ) )
						{
							return 'valid';
						}
						break;
					case 'is_special_email':
						$this->autoresponder[] = $id;
						/**
						 * see https://de.wikipedia.org/wiki/E-Mail-Adresse#Der_Lokalteil_(Local_Part)
						 */
						if( preg_match( "/^[a-zA-Z0-9.!#$%&'*+\-\/=?^_`{|}~ÄÖÜäöü]+@\w[\w\.\-ÄÖÜäöü]*\.[a-zA-Z]{2,20}$/", urldecode( $_POST[ $id ] ) ) )
						{
							return 'valid';
						}
						break;
					case 'is_number':
						if( preg_match("!^-?\s*(0|[1-9]\d*)([\.,]\d+)?$!", urldecode( $_POST[ $id ] ) ) )
						{
							return 'valid';
						}
						break;
					case 'is_positiv_number':
						if( preg_match( "!^[0-9]\d*([\.|\,]\d+)?$!", urldecode( $_POST[ $id ] ) ) )
						{
							return 'valid';
						}
						break;
					case 'is_phone':
						if( preg_match( "!^(\d|\s|\-|\/|\(|\)|\[|\]|e|x|t|ension|\.|\+|\_|\,|\:|\;){3,}$!", urldecode( $_POST[ $id ] ) ) )
						{
							return 'valid';
						}
						break;
					case 'is_url':
						if( preg_match( "!^(https?|ftp)://(-\.)?([^\s/?\.#-]+\.?)+(/[^\s]*)?$!", urldecode( $_POST[ $id ] ) ) )
						{
							return 'valid';
						}
						break;
					case 'captcha':
						$ver = $_POST[ $id . '_verifier' ];
						$reverse = strrev( $ver );

						if( $ver[ $reverse[0] ] == $_POST[ $id ] )
						{
							unset( $_POST[ $id ], $_POST[ $id . '_verifier'] );
							return 'valid';
						}
						break;

				} //end switch

				$this->submit_form = false;
				$this->validation_error = true;
				return 'error';
			}
		}
	}
}
