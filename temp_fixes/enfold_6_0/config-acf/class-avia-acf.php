<?php
/**
 * This class handles integration of Advanced Custom Fields to support dynamic content
 *
 * @author guenter
 * @since 6.0
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_ACF', false ) )
{
	class avia_ACF
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 6.0
		 * @var avia_ACF
		 */
		static private $_instance = null;

		/**
		 * Supported types of ACF fields that are displayed in selectbox
		 * (can be extended with time to come)
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $acf_types_supported;

		/**
		 * Array of all defined ACF fields
		 *
		 *		'post_name'	=> WP_Post
		 *
		 * @since 6.0
		 * @var null|array
		 */
		protected $acf_fields;

		/**
		 * Cached ACF fields defined for a post
		 *
		 *		post_id  =>  array ( field keys )
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $post_acf_fields_cache;

		/**
		 * Cached ACF select dropdown group (only contains ACF for the post
		 *
		 *		post_id  =>  array ( selectbox_entries )
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $post_group_fields_cache;

		/**
		 * Return the instance of this class
		 *
		 * @since 6.0
		 * @return avia_ACF
		 */
		static public function instance()
		{
			if( is_null( avia_ACF::$_instance ) )
			{
				avia_ACF::$_instance = new avia_ACF();
			}

			return avia_ACF::$_instance;
		}

		/**
		 *
		 * @since 6.0
		 */
		public function __construct()
		{
			$this->acf_types_supported = $this->get_acf_types_supported();
			$this->acf_fields = null;
			$this->post_acf_fields_cache = [];
			$this->post_group_fields_cache = [];

			add_filter( 'avf_query_wp_default_custom_fields', [ $this, 'handler_avf_query_wp_default_custom_fields' ], 10, 3 );
			add_filter( 'avf_register_dynamic_data_sources', [ $this, 'handler_avf_register_dynamic_data_sources' ], 10, 2 );
			add_filter( 'avf_dynamic_dropdown_array', [ $this, 'handler_avf_dynamic_dropdown_array' ], 10, 3 );
			add_filter( 'avf_dynamic_filter_select_list', [ $this, 'handler_avf_dynamic_filter_select_list' ], 10, 4 );
			add_filter( 'avf_create_sc_data_string', [ $this, 'handler_avf_create_sc_data_string' ], 10, 6 );
			add_filter( 'avf_replace_pseudo_shortcode', [ $this, 'handler_avf_replace_pseudo_shortcode' ], 10, 2 );
			add_filter( 'avf_sc_av_dynamic_el', [ $this, 'handler_avf_sc_av_dynamic_el' ], 10, 6 );

			//	hook into ACF
			add_filter( 'acf/post_type/available_supports', [ $this, 'handler_acf_post_type_available_supports' ], 10, 2 );
		}

		/**
		 *
		 * @since 6.0
		 */
		public function __destruct()
		{
			unset( $this->acf_types_supported );
			unset( $this->acf_fields );
			unset( $this->post_acf_fields_cache );
			unset( $this->post_group_fields_cache );
		}

		/**
		 * Add support for backend editor
		 *
		 * @since 6.0
		 * @param array $acf_available_supports
		 * @param array $acf_post_type_rendered
		 * @return array
		 */
		public function handler_acf_post_type_available_supports( $acf_available_supports, $acf_post_type_rendered )
		{
			global $acf_post_type, $pagenow;

			if( 'post-new.php' == $pagenow )
			{
				//	enable this by default - we need to do that in global variable
				$acf_post_type['supports'][] = 'avia_layout_settings';
			}

			$acf_available_supports['avia_layout_settings'] = __( 'Enfold Layouts', 'avia_framework' );

			return $acf_available_supports;
		}

		/**
		 * Query meta data, filter ACF fields and return the default WP custom fields only
		 * Store supported ACF fields to add later to select list
		 *
		 * @since 6.0
		 * @param array $wp_custom_fields
		 * @return array|null
		 */
		public function handler_avf_query_wp_default_custom_fields( array $wp_custom_fields = [], $filter = true, $limit = 0 )
		{
			if( ! is_array( $this->acf_fields ) )
			{
				$this->acf_fields = [];

				/**
				* Query all ACF fields to remove from WP custom field list
				*/
				$args = [
						'post_type'              => 'acf-field',
						'posts_per_page'         => -1,
						'orderby'                => 'post_title',
						'order'                  => 'ASC',
						'suppress_filters'       => true, // DO NOT allow WPML to modify the query
						'cache_results'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
						'post_status'            => [ 'publish', 'trash' ]
					];

				$posts = get_posts( $args );

				if( is_array( $posts ) )
				{
					foreach( $posts as $key => $post )
					{
						$content = unserialize( $post->post_content );

						//	only save fields we support
						if( ! in_array( $content['type'], $this->acf_types_supported ) )
						{
							continue;
						}

						$this->acf_fields[ $post->post_name ] = $post;
					}
				}
			}

			if( empty( $this->acf_fields ) )
			{
				return $wp_custom_fields;
			}

			//	remove fields in custom fields array if we support the type of acf field
			$delete = [];

			foreach( $this->acf_fields as $key => $field )
			{
				$delete[] = $field->post_excerpt;
				$delete[] = '_' . $field->post_excerpt;
			}

			$wp_custom_fields = array_diff( $wp_custom_fields, $delete );

			return $wp_custom_fields;
		}

		/**
		 * Add ACF specific options
		 *
		 * @since 6.0
		 * @param array $data
		 * @param string $context				'groups' | 'fields' | 'data_formats' | 'shortcodes'
		 * @return array
		 */
		public function handler_avf_register_dynamic_data_sources( array $data, $context )
		{
			if( empty( $this->acf_fields ) )
			{
				return $data;
			}

			switch( $context )
			{
				case 'groups':
					$data['acf_fields'] = __( 'ACF - Advanced Custom Fields', 'avia_framework' );
					break;
				case 'fields':
					$fields = [];

					$fields['acf_field_shortcode'] = __( 'Theme ACF shortcode', 'avia_framework' );

					foreach( $this->acf_fields as $key => $field )
					{
						$content = unserialize( $field->post_content );

						$fields[ "acf_{$key}:{$field->post_title}" ] = "{$field->post_title} ( {$content['type']} )";
					}

					$data['acf_fields'] = $fields;
					break;
				case 'shortcodes';
					$data['acf_field_shortcode'] = '{av_dynamic_el src="acf" key="%metakey%"}';
					break;
			}

			return $data;
		}

		/**
		 * Add acf fields to dynamic dropdown array if there is a limitation of content
		 *
		 * @since 6.0
		 * @param array $dynamic
		 * @param string $context
		 * @param mixed $args
		 * @return array
		 */
		public function handler_avf_dynamic_dropdown_array( array $dynamic, $context, $args )
		{
			if( empty( $dynamic ) || in_array( 'acf_fields', $dynamic ) )
			{
				return $dynamic;
			}

			$check = [
					'Avia_Popup_Templates::custom_field_image',
					'Avia_Popup_Templates::custom_field_gallery',
					'Avia_Popup_Templates::linkpicker_toggle',
					'avia_sc::link',
					'avia_sc::input'
				];

			if( ! in_array( $context, $check ) )
			{
				return $dynamic;
			}

			$dynamic[] = 'acf_fields';

			return $dynamic;
		}

		/**
		 * Limit the ACF fields to those used for the post
		 *
		 * @since 6.0
		 * @param array $group_fields
		 * @param string $group_key
		 * @param WP_Post|null $source_post
		 * @param array $element
		 * @return array
		 */
		public function handler_avf_dynamic_filter_select_list( array $group_fields, $group_key, $source_post, array $element = [] )
		{
			if( 'acf_fields' != $group_key || ! $source_post instanceof WP_Post )
			{
				return $group_fields;
			}

			//	do not filter for custom layout post type posts if no "Underlying Entry ID" post is set
			if( Avia_Custom_Layout()->is_edit_custom_layout_page() && Avia_Custom_Layout()->get_post_type() == $source_post->post_type )
			{
				return $group_fields;
			}

			if( ! isset( $this->post_acf_fields_cache[ $source_post->ID ] ) )
			{
				$field_objs = get_field_objects( $source_post->ID, false, false );
				$field_keys = [];

				if( is_array( $field_objs ) )
				{
					foreach( $field_objs as $field_obj )
					{
						$field_keys[] = $field_obj['key'];
					}
				}

				$this->post_acf_fields_cache[ $source_post->ID ] = $field_keys;
			}

			$acf_fields = $this->post_acf_fields_cache[ $source_post->ID ];

			//	On new posts we have to keep all possible acf fields
			if( empty( $acf_fields ) )
			{
				return $group_fields;
			}

			if( isset( $this->post_group_fields_cache[ $source_post->ID ] ) )
			{
				return $this->post_group_fields_cache[ $source_post->ID ];
			}

			foreach( $group_fields as $group_key => $group_field )
			{
				//	key = [acf_field_xxxx:info]
				$check = explode( ':', $group_key );

				if( empty( $check[1] ) )
				{
					continue;
				}

				$check_key = substr( $check[0], 4 );

				if( ! in_array( $check_key, $acf_fields ) )
				{
					unset( $group_fields[ $group_key ] );
				}
			}

			$this->post_group_fields_cache[ $source_post->ID ] = $group_fields;

			return $group_fields;
		}

		/**
		 * Allow 3rd party to modify defaults or react to own groups or fields
		 *
		 * @since 6.0
		 * @param string $data
		 * @param string $group_key
		 * @param string $field_key
		 * @param array $shortcodes
		 * @param string $context			'pseudo_code' | 'shortcode'
		 * @param array $element
		 * @return string
		 */
		public function handler_avf_create_sc_data_string( $data, $group_key, $field_key, $shortcodes, $context, $element )
		{
			if( 'acf_fields' != $group_key )
			{
				return $data;
			}

			if( ! isset( $shortcodes[ $field_key ] ) )
			{
				if( 'pseudo_code' == $context )
				{
					$data = "{{$field_key}}";
				}
				else
				{
					$data = '{av_dynamic_el src="acf" key="' . $field_key . '"}';
				}
			}
			else
			{
				$data = $shortcodes[ $field_key ];
			}

			return $data;
		}

		/**
		 *
		 * @since 6.0
		 * @param string $content
		 * @param string $format				'' | 'auto' | 'undefined'
		 * @return string
		 */
		public function handler_avf_replace_pseudo_shortcode( $content, $format )
		{
			if( false === strpos( $content, '{acf_' ) )
			{
				return $content;
			}

			$content = preg_replace_callback( '!{(acf_[^}]*)}!mi', array( $this, 'cb_replace_pseudo_sc_post' ), $content );

			if( in_array( $format, [ 'auto', 'undefined' ] ) )
			{
				$content = str_replace( 'src="acf"', 'src="acf" format="auto"', $content );
			}

			return $content;
		}

		/**
		 * Callback to replace shorthand pseudo shortcode with real shortcode
		 *
		 * @since 6.0
		 * @param array $matches
		 * @return string
		 */
		protected function cb_replace_pseudo_sc_post( array $matches )
		{
			if( ! isset( $matches[1] ) )
			{
				return $matches[0];
			}

			return '[av_dynamic_el src="acf" key="' . $matches[1] . '"]';
		}

		/**
		 * Create HTML output of ACF field
		 *
		 * @since 6.0
		 * @param string $html
		 * @param array $atts
		 * @param string $content
		 * @param string $shortcodename
		 * @param WP_Post $source_post
		 * @param aviaDynamicContent $dyn_content
		 * @return string
		 */
		public function handler_avf_sc_av_dynamic_el( $html, $atts, $content, $shortcodename, $source_post, $dyn_content )
		{
			if( 'acf' != $atts['src'] )
			{
				return $html;
			}

			$split = explode( ':', $atts['key'] );

			//	illegal string
			if( empty( $split[0] ) )
			{
				return $html;
			}

			$field_post = $this->get_field_post( $split[0] );

			if( ! $field_post instanceof WP_Post )
			{
				return $html;
			}

			if( ! isset( $atts['format'] ) )
			{
				$atts['format'] = '';
			}

			$field_id = $field_post->post_name;
			$info = unserialize( $field_post->post_content );

			switch( $info['type'] )
			{
				case 'text':
				case 'textarea':
				case 'number':
				case 'range':
				case 'email':
				case 'password':
					$html .= get_field( $field_id, $source_post->ID, true );
					break;
				case 'image':
					//	unformatted always returns attachment ID
					$id = get_field( $field_id, $source_post->ID, false );

					if( 'auto' == $atts['format'] )
					{
						$src = get_field( $field_id, $source_post->ID, true );
						if( ! empty( $src ) )
						{
							//	check "Return Format"
							if( is_array( $src ) )
							{
								//	Image array
								$src = $src['url'];
							}
							else if( is_numeric( $src ) )
							{
								//	Image ID
								$src = wp_get_attachment_url( $src );
							}

							if( ! empty( $src ) )
							{
								$html .= '<div class="avia-image av-acf-image attachment-' . $id . '">';
								$html .=	'<img src="'  . esc_attr( $src ) . '">';
								$html .= '</div>';
							}
						}
					}
					else
					{
						$html .= $id;
					}
					break;
				case 'gallery':
					//	unformatted returns array of attachment ID
					$ids = get_field( $field_id, $source_post->ID, false );

					if( ! empty( $ids ) && 'auto' == $atts['format'] )
					{
						/**
						 * Filter image sizes to display gallery images
						 *
						 * @since 6.0
						 * @param string $img_size
						 * @param array $ids
						 * @param array $atts
						 * @param WP_Post $source_post
						 * @param aviaDynamicContent $dyn_content
						 */
						$img_size = apply_filters( 'avf_acf_gallery_image_size', 'thumbnail', $ids, $atts, $source_post, $dyn_content );
						$img_class = "attachment-{$img_size}";
						$urls = [];

						foreach( $ids as $id )
						{
							$urls[] = wp_get_attachment_image_url( $id, $img_size );
						}

						foreach( $urls as $index => $url )
						{
							if( false !== $url )
							{
								$html .= '<div class="avia-image av-acf-gallery attachment-' . $ids[ $index ] . ' ' . $img_class . '">';
								$html .=	'<img src="'  . esc_attr( $url ) . '">';
								$html .= '</div>';
							}
						}
					}
					else
					{
						if( is_array( $ids ) && ! empty( $ids ) )
						{
							$html .= implode( ',', $ids );
						}
					}
					break;
				case 'url':
					$value = get_field( $field_id, $source_post->ID, true );
					$html .= $this->anchor_tag( $value, $value, $atts['format'], 'acf-url' );
					break;
				case 'link':
					//	we get complete info for link in unformatted
					$value = get_field( $field_id, $source_post->ID, false );

					if( ! empty ( $value['url'] ) )
					{
						$title = ! empty( $value['title'] ) ? $value['title'] : $value['url'];
						$target = ! empty( $value['target'] ) ? $value['target'] : '';
						$html .= $this->anchor_tag( $value['url'], $title, $atts['format'], 'acf-link', $target );
					}
					break;
				case 'post_object':
				case 'page_link':
					//	returns the post id's to selected object
					$ids = get_field( $field_id, $source_post->ID, false );

					if( empty( $ids ) )
					{
						return $html;
					}

					if( ! is_array( $ids ) )
					{
						$ids = [ $ids ];
					}

					$urls = [];
					$titles = [];
					foreach( $ids as $id )
					{
						$urls[] = get_permalink( $id );
						$titles[] = get_the_title( $id );
					}

					if( empty( $atts['format'] ) )
					{
						if( false !== $urls[0] )
						{
							$html .= $urls[0];
						}

						return $html;
					}

					$links = [];
					foreach( $urls as $index => $url )
					{
						if( false !== $url )
						{
							$links[] = $this->anchor_tag( $url, $titles[ $index ], $atts['format'], "acf-{$info['type']}" );
						}
					}

					if( ! empty( $links ) )
					{
						$html .= implode( ', ', $links );
					}
					break;
				case 'date_picker':
				case 'date_time_picker':
				case 'time_picker':
				case 'color_picker':
					//	use format defined by afc
					$html .= get_field( $field_id, $source_post->ID, true );
					break;
			}

			return $html;
		}

		/**
		 * Returns a HTML <a> tag or link as plain text
		 *
		 * @since 6.0
		 * @param string $url
		 * @param string $title
		 * @param string $format					'' | 'auto'
		 * @param string $extra_class
		 * @param string $target
		 * @return string
		 */
		protected function anchor_tag( $url, $title, $format = '', $extra_class = '', $target = '' )
		{
			$html = '';

			if( empty( $url ) )
			{
				return $html;
			}

			if( 'auto' == $format )
			{
				$class = empty( $extra_class ) ? 'av-acf-link' : "av-acf-link {$extra_class}";

				if( empty( $title ) )
				{
					$title = $url;
				}

				$target = ! empty( $target ) ? ' target="' . $target . '" ' : '';

				$html .= '<a class="' . $class . '" href="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '"' . $target . '>' . esc_html( $title ) . '</a>';
			}
			else
			{
				$html .= $url;
			}

			return $html;
		}

		/**
		 * Search the acf fields in local list and return it
		 *
		 * @since 6.0
		 * @param string $key
		 * @return WP_Post|false
		 */
		protected function get_field_post( $key )
		{
			$key = trim( $key );

			//	we might have acf_field......
			if( 0 === strpos( $key, 'acf_' ) )
			{
				$key = substr( $key, 4 );
			}

			/**
			 * We only query custom fields in backend - in frontend we need to load here
			 *
			 * @since 6.0.1
			 */
			if( ! is_array( $this->acf_fields ) )
			{
				$this->handler_avf_query_wp_default_custom_fields();
			}

			//	check for field.....
			if( isset( $this->acf_fields[ $key ] ) )
			{
				return $this->acf_fields[ $key ];
			}

			//	we check if we have a meta key
			foreach( $this->acf_fields as $field )
			{
				if( $field->post_excerpt == $key )
				{
					return $field;
				}
			}

			return false;
		}

		/**
		 * Filter the acf field types shown in dropdown
		 *
		 * @since 6.0
		 * @return array
		 */
		public function get_acf_types_supported()
		{
			if( empty( $this->acf_types_supported ) )
			{
				$acf_types_supported = [
									'text',
									'textarea',
									'number',
									'range',
									'email',
									'url',
									'password',

									'image',
									'gallery',		//	pro
									'link',

									'post_object',
									'page_link',

									'date_picker',
									'date_time_picker',
									'time_picker',
									'color_picker'
								];

				/**
				 * @since 6.0
				 * @param array $acf_types_supported
				 * @return array
				 */
				$this->acf_types_supported = apply_filters( 'avf_acf_field_types_supported', $acf_types_supported );
			}

			return $this->acf_types_supported;
		}
	}

	/**
	 * Returns the main instance of avia_ACF to prevent the need to use globals
	 *
	 * @since 6.0
	 * @return avia_ACF
	 */
	function Avia_ACF()
	{
		return avia_ACF::instance();
	}

	/**
	 * Activate filter and action hooks
	 */
	Avia_ACF();

}
