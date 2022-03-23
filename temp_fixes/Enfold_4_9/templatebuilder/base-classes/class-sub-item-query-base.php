<?php
namespace aviaBuilder\base;
/**
 * This base class is intended for shortcode helper objects that need a secondary query for subitems (like sliders).
 * It contains the base members and abstract methods necessary to store and handle support for styling sub items.
 *
 * @author		Günter
 * @since 4.8.8.1
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\aviaSubItemQueryBase' ) )
{
	abstract class aviaSubItemQueryBase
	{
		/**
		 * Unmodified rendered attributes on object construct
		 *
		 * @since 4.8.8.1
		 * @var array
		 */
		private $atts;

		/**
		 * @since 4.8.8.1
		 * @var array
		 */
		protected $default_atts;

		/**
		 * @since 4.8.8.1
		 * @var array
		 */
		protected $default_item_atts;

		/**
		 * @since 4.8.8.1
		 * @var aviaShortcodeTemplate
		 */
		protected $sc_context;

		/**
		 * Contains default settings for the slider.
		 * These settings extend default settings $default_atts from shortcode.
		 *
		 * @since 4.8.8.1
		 * @var array
		 */
		protected $outer_defaults;

		/**
		 * Contains the final configuration settings from the outer attributes
		 *
		 * @since 4.8.8.1
		 * @var array
		 */
		protected $config;

		/**
		 * Container element id for this element (e.g. slider)
		 * @since 4.8.8.1
		 * @var string
		 */
		protected $element_id;

		/**
		 * Base element styling object for this element (e.g. slider)
		 *
		 * @since 4.8.8.1
		 * @var aviaElementStyling
		 */
		protected $element_styles;

		/**
		 * @since 4.8.8.1
		 * @param array $atts
		 * @param \aviaShortcodeTemplate $sc_context
		 * @param array $outer_defaults
		 */
		protected function __construct( array $atts = array(), \aviaShortcodeTemplate $sc_context = null, array $outer_defaults = array() )
		{
			$this->atts = $atts;
			$this->default_atts = $sc_context != null && method_exists( $sc_context, 'get_default_sc_args' ) ? $sc_context->get_default_sc_args() : array();
			$this->default_item_atts = $sc_context != null && method_exists( $sc_context, 'get_default_modal_group_args' ) ? $sc_context->get_default_modal_group_args() : array();
			$this->sc_context = $sc_context;
			$this->outer_defaults = $outer_defaults;

			//	merge the outer attributes to final settings
			$this->config = array_merge( $this->outer_defaults, $this->default_atts, $atts );

			$this->element_id = '';
			$this->element_styles = null;
		}

		/**
		 * @since 4.8.8.1
		 */
		protected function __destruct()
		{
			unset( $this->atts );
			unset( $this->default_atts );
			unset( $this->default_item_atts );
			unset( $this->sc_context );
			unset( $this->outer_defaults );
			unset( $this->config );
			unset( $this->element_styles );
		}

		/**
		 * Returns the defaults array for this object
		 *
		 * @since 4.8.8.1
		 * @param array $args
		 * @return array
		 */
		abstract static public function default_args( array $args = array() );

		/**
		 * Create custom stylings.
		 * Callback when creating post css file and inline styles.
		 * Add all your subitem stylings to $this->element_styles here.
		 *
		 * @since 4.8.8.1
		 * @param array $result
		 * @return array
		 */
		abstract public function get_element_styles( array $result );

		/**
		 * Create the html.
		 * Callback from shortcode handler for final output of element
		 *
		 * @since 4.8.8.1
		 * @return string
		 */
		abstract public function html();

		/**
		 * Some attributes may change or only exist when being called from shortcode handler (like $meta values)
		 * Here we update them in base data structure.
		 *
		 * In case this needs special attention override this function.
		 *
		 * @since 4.8.9
		 * @param array $new_values
		 * @return array
		 */
		public function update_config( array $new_values = array() )
		{
			foreach( $new_values as $key => $value )
			{
				$this->config[ $key ] = $value;
			}

			return $this->config;
		}

		/**
		 * Return config array
		 *
		 * @since 4.8.9
		 * @return array
		 */
		public function get_config()
		{
			return $this->config;
		}
	}

}

