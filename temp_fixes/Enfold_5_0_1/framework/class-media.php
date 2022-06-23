<?php
/**
 * This file holds various functions that modify the wordpress media uploader
 * and list view of media gallery
 *
 * It utilizes custom posts to create a gallerie for each upload field.
 * Kudos to woothemes for this great idea :)
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright (c) Christian Budschedl
 * @link		http://kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 */
 if( ! defined( 'AVIA_FW' ) )	{	exit( 'No direct script access allowed' );	}


if( ! class_exists( 'avia_media' ) )
{
	/**
	 * The avia media class is a set of static class methods that help to create the hidden image containing posts
	 *
	 * @package 	AviaFramework
	 */
	class avia_media
	{
		/**
		 * The avia media generate_post_type function builds the hidden posts necessary for image saving on options pages
		 */
		static public function generate_post_type()
		{
			register_post_type( 'avia_framework_post', array(
									'labels'			=> array( 'name' => 'Avia Framework' ),
									'show_ui'			=> false,
									'query_var'			=> true,
									'capability_type'	=> 'post',
									'hierarchical'		=> false,
									'rewrite'			=> false,
									'supports'			=> array( 'editor', 'title' ),
									'can_export'		=> true,
									'public'			=> true,
									'show_in_nav_menus'	=> false
						) );
		}


		/**
		 * The avia media get_custom_post function gets a custom post based on a post title. if no post cold be found it creates one
		 * @param string $post_title the title of the post
		 *
		 * @package 	AviaFramework
		 */
		static public function get_custom_post( $post_title )
		{
			$save_title = avia_backend_safe_string( $post_title );

			$args = array(
						'post_type'			=> 'avia_framework_post',
						'post_title'		=> 'avia_' . $save_title,
						'post_status'		=> 'draft',
						'comment_status'	=> 'closed',
						'ping_status'		=> 'closed'
					);

			$avia_post = avia_media::get_post_by_title( $args['post_title'] );

			if( ! isset( $avia_post['ID'] ) )
			{
				$avia_post_id = wp_insert_post( $args );
			}
			else
			{
				$avia_post_id = $avia_post['ID'];
			}

			return $avia_post_id;
		}

		/**
		 * The avia media get_post_by_title function gets a custom post based on a post title.
		 *
		 * @package 	AviaFramework
		 * @param string $post_title the title of the post
		 * @return array|null
		 */
		static public function get_post_by_title( $post_title )
		{
			global $wpdb;

			$post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='avia_framework_post'", $post_title ) );

			if( $post )
			{
				return get_post( $post, 'ARRAY_A');
			}

			return null;
		}

		/**
		 * The avia add_media_label_header function hooks into wordpress galery tabs and injects a new button label
		 * this label can be found by the frameworks javascript and it then overwrites the default "insert into post" text
		 *
		 * @since 4.8.2					added esc_attr() to prevent code injection (reported by “Julien Legras and Guillaume André of Synacktiv (https://synacktiv.com)”)
		 * @param array $_default_tabs the default tabs
		 * @package 	AviaFramework
		 */
		static public function add_media_label_header( $_default_tabs )
		{
			//change the label of the insert button
			if( isset( $_GET['avia_label'] ) )
			{
				echo "<input class='avia_insert_button_label' type='hidden' value='" . html_entity_decode( $_GET['avia_label'] ) . "' />";
			}

			/**
			 * activate the gallery mode
			 */
			if( isset( $_GET['avia_gallery_mode'] ) )
			{
				echo "<input class='avia_gallery_mode_active' type='hidden' value='" . esc_attr( $_GET['avia_gallery_mode'] ) . "' />";

				if( isset( $_default_tabs['library'] ) )
				{
					unset($_default_tabs['library'] );
				}

				if( isset( $_default_tabs['type_url'] ) )
				{
					unset( $_default_tabs['type_url'] );
				}
			}

			//remove the default insert method and replace it with the better image id based method
			if( isset( $_GET['avia_idbased'] ) )
			{
				echo "<input class='avia_idbased' type='hidden' value='" . esc_attr( $_GET['avia_idbased'] ) . "' />";
			}

			return $_default_tabs;
		}

		/**
		 *
		 * @param string $form_action_url
		 * @param string $type
		 * @return string
		 */
		static public function url_filter( $form_action_url, $type )
		{
			if( isset( $_REQUEST[ 'avia_idbased'] ) )
			{
				$form_action_url = $form_action_url . "&amp;avia_idbased=" . $_REQUEST['avia_idbased'];
			}

			if( isset( $_REQUEST['avia_gallery_mode'] ) )
			{
				$form_action_url = $form_action_url . "&amp;avia_gallery_mode=" . $_REQUEST['avia_gallery_mode'];
			}

			if( isset($_REQUEST['avia_label'] ) )
			{
				$form_action_url = $form_action_url . "&amp;avia_label=" . $_REQUEST['avia_label'];
			}

			return $form_action_url;
		}
	}
}

add_action( 'init', array( 'avia_media', 'generate_post_type' ) );
add_filter( 'media_upload_tabs', array( 'avia_media', 'add_media_label_header' ) );
add_filter( 'media_upload_form_url', array( 'avia_media', 'url_filter' ), 10, 2 );




if( ! class_exists( 'avia_media_gallery' ) )
{
	/**
	 * The avia media gallery class is used for the new media gallery
	 *
	 * @package 	AviaFramework
	 */
	class avia_media_gallery
	{
		/**
		 * The url filter function attaches the avia_gallery_parameter to all urls within the form
		 * based on that parameter we can perform php checks if the current gallery is a advanced gallery
		 */
		static public function url_filter( $form_action_url, $type )
		{
			if( isset( $_REQUEST['avia_gallery_active'] ) )
			{
				$form_action_url = $form_action_url . "&amp;avia_gallery_active=" . $_REQUEST['avia_gallery_active'];
			}

			return $form_action_url;
		}

		/*
		* 	register the stylesheet that hides default insert buttons and adds styles for the additional insert buttons
		*/
		static public function register_stylesheet( $current_hook )
		{
			if( isset( $_REQUEST['avia_gallery_active'] ) && $current_hook == 'media-upload-popup' )
			{
				wp_enqueue_style( 'avia_gallery_mode', AVIA_CSS_URL . 'conditional_load/avia_gallery_mode.css' );
			}
		}

		/**
		 *
		 * @param array $form_fields
		 * @param mixed $post
		 * @return array
		 */
		static public function add_buttons( $form_fields, $post )
		{
			if( isset( $_REQUEST['avia_gallery_active'] ) || isset( $_REQUEST['fetch'] ) )
			{
				$label = __( 'Add to Gallery', 'avia_framework' );

				if( isset( $_REQUEST['avia_gallery_label'] ) )
				{
					$label = $_REQUEST['avia_gallery_label'];
				}

				$form_fields['avia-send-to-editor'] = array(
									'label'	=> '',
									'input'	=> 'html',
									'html'	=> '<a href="#" data-attachment-id="' . $post->ID . '" class="button avia_send_to_gallery">' . $label . '</a>',
								);
			}

			return $form_fields;
		}

		/**
		 *
		 * @param array $default_tabs
		 * @return array
		 */
		static public function remove_unused_tab( $default_tabs )
		{
			if( isset( $_REQUEST['avia_gallery_active'] ) )
			{
				$default_tabs = array(
							'type'		=> 'From Computer',
							'gallery'	=> 'Gallery',
							'library'	=> 'Media Library'
						);
			}

			return $default_tabs;
		}

		/**
		 * filter for the thickbox - only display insert video tab
		 *
		 * @param array $default_tabs
		 * @return array
		 */
		static public function video_tab( $default_tabs )
		{
			if( isset($_REQUEST['tab'] ) && 'avia_video_insert' == $_REQUEST['tab'] )
			{
				$default_tabs = array( 'avia_video_insert' => 'Insert Video' );
			}

			return $default_tabs;
		}

		/**
		 * creates the insert form
		 */
		static public function create_video_tab()
		{
			wp_iframe( array( 'avia_media_gallery', 'media_avia_create_video_insert' ) );
		}

		static public function media_avia_create_video_insert()
		{

			$video_description = '<p class="help">Enter the URL to the Video. <br/> A list of all supported Video Services can be found <a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank" rel="noopener noreferrer">here</a>. Youtube videos will display additional info like title, share link, related videos, ...
								<br/> <br/>
								Working examples:<br/>
								<strong>https://vimeo.com/1084537</strong><br/>
								<strong>https://www.youtube.com/watch?v=G0k3kHtyoqc</strong><br/>
								</p>';

			$video_description = apply_filters( 'avia_filter_video_insert_label', $video_description );

			$output = "<form>";
			$output .='<h3 class="media-title">Insert media from another website</h3>';
			$output .= '<div id="media-items">';
			$output .= '<div class="media-item media-blank">';
			$output .=	'<table class="describe avia_video_insert_table"><tbody>
						<tr>
							<th valign="top" scope="row" class="label" style="width:130px;">
								<span class="alignleft"><label for="src">' . __('Enter Video URL', 'avia_framework') . '</label></span>
								<span class="alignright"><abbr id="status_img" title="required" class="required">*</abbr></span>
							</th>
							<td class="field">
								<input id="src" name="src" value="" type="text" aria-required="true"  />
								' . $video_description . '
							</td>
						</tr>
						<tr>
							<td></td>
							<td class="avia_button_container">
								<input type="button" class="button" id="avia_insert_video" value="' . esc_attr__( 'Insert Video', 'avia_framework' ) . '" />
							</td>
						</tr>';
			$output .= '</tbody></table>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '</form>';

			echo $output;
		}

		/**
		 * Save filedata on upload
		 *
		 * @since 4.8.8			based on code provided by Guenni007 - Weber Günter
		 * @param int $meta_id
		 * @param int $post_id
		 * @param string $meta_key
		 * @param string $meta_value
		 */
		static public function add_filesize_metadata_to_images( $meta_id, $post_id, $meta_key, $meta_value )
		{
			if( '_wp_attachment_metadata' == $meta_key )
			{
				$file = get_attached_file( $post_id );
				update_post_meta( $post_id, 'filesize', filesize( $file ) );
			}
		}

		/**
		 * Add the columns
		 *
		 * @since 4.8.8			based on code provided by Guenni007 - Weber Günter
		 * @since 5.0.2			extended with attachment ID
		 * @param array $posts_columns
		 * @return type
		 */
		static public function add_columns( array $posts_columns )
		{
			/**
			 * Supress attachment ID
			 *
			 * @since 5.0.2
			 * @param boolean $add_id
			 * @return true|mixed
			 */
			if( true === apply_filters( 'avf_media_list_view_id', true ) )
			{
				$posts_columns['post_attachments_id'] = __( 'ID', 'avia_framework' );
			}

			$posts_columns['filesize'] = __( 'File Size', 'avia_framework' );
			$posts_columns['dimensions'] = __( 'Dimension w * h in px', 'avia_framework' );

			return $posts_columns;
		}

		/**
		 * Populate the columns
		 *
		 * @since 4.8.8			based on code provided by Guenni007 - Weber Günter
		 * @since 5.0.2			extended with attachment ID
		 * @param string $column_name
		 * @param int $post_id
		 */
		static public function add_columns_value( $column_name, $post_id )
		{
			if( 'filesize' == $column_name )
			{
				$file_size = get_post_meta( $post_id, 'filesize', true );

				if( ! $file_size )
				{
					$file = get_attached_file( $post_id );
					$file_size = filesize( $file );

					update_post_meta( $post_id, 'filesize', $file_size );
				}

				echo size_format( $file_size, 2 );
			}
			else if( 'dimensions' == $column_name )
			{
				$svg_meta = avia_SVG()->get_meta_data( $post_id );

				if( is_array( $svg_meta ) && ! empty( $svg_meta['viewbox'] ) )
				{
					echo __( 'viewBox', 'avia_framework' ) . '<br />' . $svg_meta['viewbox'];
				}
				else
				{
					$meta = wp_get_attachment_metadata( $post_id );

					if( isset( $meta['width'] ) )
					{
						echo $meta['width'] . ' x ' . $meta['height'];
					}
					else
					{
						echo '---';
					}
				}
			}
			else if( 'post_attachments_id' == $column_name )
			{
				echo $post_id;
			}

			return;
		}

		/**
		 * Make file-size column and attachment ID sortable
		 *
		 * @since 4.8.8			based on code provided by Guenni007 - Weber Günter
		 * @since 5.0.2			extended with attachment ID
		 * @param array $columns
		 * @return array
		 */
		static public function make_file_size_column_sortable( $columns )
		{
			$columns['filesize'] = 'filesize';
			$columns['post_attachments_id'] = 'post_attachments_id';

			return $columns;
		}

		/**
		 * File-Size Column / attachment ID sorting logic (query modification)
		 *
		 * @since 4.8.8			based on code provided by Guenni007 - Weber Günter
		 * @since 5.0.2			extended with attachment ID
		 * @param WP_Query $query
		 */
		static public function file_size_sorting_logic( $query )
		{
			global $pagenow;

			if( ! ( is_admin() && 'upload.php' == $pagenow && $query->is_main_query() && ! empty( $_REQUEST['orderby'] ) ) )
			{
				return;
			}

			switch( $_REQUEST['orderby'] )
			{
				case 'filesize':
					$query->set( 'orderby', 'meta_value_num' );
					$query->set( 'meta_key', 'filesize' );
					break;
				case 'post_attachments_id':
					$query->set( 'orderby', 'ID' );
					break;
				default:
					return;
			}

			if( isset( $_REQUEST['order'] ) && 'desc' == $_REQUEST['order'] )
			{
				$query->set( 'order', 'DESC' );
			}
			else
			{
				$query->set( 'order', 'ASC' );
			}
		}
	}
}



add_action( 'media_upload_avia_video_insert', array( 'avia_media_gallery', 'create_video_tab' ) );
add_filter( 'media_upload_tabs', array( 'avia_media_gallery', 'video_tab' ), 11 );
add_filter( 'media_upload_tabs', array( 'avia_media_gallery', 'remove_unused_tab' ) );
add_filter( 'attachment_fields_to_edit', array( 'avia_media_gallery', 'add_buttons' ), 10, 2 );
add_filter( 'attachment_fields_to_edit', array( 'avia_media_gallery', 'add_buttons' ), 10, 2 );
add_filter( 'media_upload_form_url', array( 'avia_media_gallery', 'url_filter' ), 10, 2 );
add_filter( 'admin_enqueue_scripts', array( 'avia_media_gallery', 'register_stylesheet' ), 10 );


add_action( 'added_post_meta', array( 'avia_media_gallery', 'add_filesize_metadata_to_images' ), 10, 4 );
add_filter( 'manage_media_columns', array( 'avia_media_gallery', 'add_columns' ), 10, 1 );
add_action( 'manage_media_custom_column', array( 'avia_media_gallery', 'add_columns_value' ), 10, 2 );


/**
 * As sites might have large galleries that need filesize postmeta to be indexed we block this feature by default
 * Only images that are uploaded after this release or viewed in listview will have set this postmeta field
 *
 * @since 4.8.8
 * @return boolean $allow_sort
 * @return boolean
 */
if( false !== apply_filters( 'avf_media_gallery_sortable_filesize', false ) )
{
	add_filter( 'manage_upload_sortable_columns', array( 'avia_media_gallery', 'make_file_size_column_sortable' ), 10, 1 );
	add_action( 'pre_get_posts', array( 'avia_media_gallery', 'file_size_sorting_logic' ), 10, 1 );
}

