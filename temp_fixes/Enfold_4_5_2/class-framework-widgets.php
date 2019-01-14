<?php  if ( ! defined('AVIA_FW')) exit('No direct script access allowed');
/**
 * This file holds several widgets exclusive to the framework
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright (c) Christian Budschedl
 * @link		http://Kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 */


if ( ! class_exists( 'Avia_Widget' ) )
{
	abstract class Avia_Widget extends WP_Widget
	{
		
		/**
		 *
		 * @since 4.3.2
		 * @var array
		 */
		protected $field_names;
		
		public function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) 
		{
			parent::__construct( $id_base, $name, $widget_options, $control_options );
			
			$this->field_names = array();
		}
		
				
		/**
		 * @since 4.3.2
		 */
		public function __destruct() 
		{
			if( method_exists( $this, 'parent::__destruct' ) )
			{
				parent::__destruct();
			}
			
			unset( $this->field_names );
		}
		
		/**
		 * Returns an array that contains all default instance members filled with default values
		 * 
		 * @since 4.3.2
		 * @param array $instance
		 * @return array
		 */
		abstract protected function parse_args_instance( array $instance );
		
		
		/**
		 * Returns an array of all default fields
		 * 
		 * @since 4.3.2
		 * @return array
		 */
		protected function get_field_names()
		{
			if( empty( $this->field_names ) )
			{
				$fields = $this->parse_args_instance( array() );
				$this->field_names = array_keys( $fields );
			}
			
			return $this->field_names;
		}
		
		/**
		 * Output the <option> tag for a series of numbers and set the selected attribute
		 * 
		 * @since 4.3.2
		 * @added_by günter
		 * @param int $start
		 * @param int $end
		 * @param string $selected
		 */
		static public function number_options( $start = 1, $end = 50, $selected = 1 )
		{
			$options = array();
			
			for( $i = $start; $i <= $end; $i++ )
			{
				$options[ $i ] = $i;
			}
			
			return Avia_Widget::options_from_array( $options, $selected );
		}
		
		/**
		 * Output the <option> tag for a key - value array and set the selected attribute
		 * 
		 * @since 4.3.2 
		 * @added_by günter
		 * @param array $options
		 * @param type $selected
		 * @return string
		 */
		static public function options_from_array( array $options, $selected )
		{
			$out = '';
			
			foreach( $options as $key => $value ) 
			{
				$out .= '<option value="' . $key . '" ' . selected( $key, $selected ) . '>' . esc_html( $value ) . '</option>';
			}
			return $out;
		}
	
	}
	
}




/**
 * AVIA FACEBOOK WIDGET
 */

if ( ! class_exists( 'avia_fb_likebox' ) )
{
	class avia_fb_likebox extends Avia_Widget 
	{
		const AJAX_NONCE = 'avia_fb_likebox_nonce';
		const FB_SCRIPT_ID = 'facebook-jssdk';
		
		/**
		 *
		 * @var int 
		 */
		static protected $script_loaded = 0;
	
		



		/**
		 * 
		 */
		public function __construct() 
		{
			//Constructor
			$widget_ops = array(
						'classname'		=> 'avia_fb_likebox', 
						'description'	=> __( 'A widget that displays a facebook Likebox to a facebook page of your choice', 'avia_framework' ) 
						);
			
			parent::__construct( 'avia_fb_likebox', THEMENAME.' Facebook Likebox', $widget_ops );
			
			add_action( 'init', array( $this, 'handler_wp_register_scripts' ), 500 );
			add_action( 'wp_enqueue_scripts', array( $this, 'handler_wp_enqueue_scripts' ), 500 );
			
		}
		
		
		/**
		 * @since 4.3.2
		 */
		public function __destruct() 
		{
			parent::__destruct();
		}
		
		/**
		 * 
		 * @since 4.3.2
		 */
		public function handler_wp_register_scripts()
		{
			$vn = avia_get_theme_version();
			
			wp_register_script( 'avia_facebook_front_script' , AVIA_JS_URL . 'conditional_load/avia_facebook_front.js', array( 'jquery' ), $vn, true );
		}

		/**
		 * @since 4.3.2
		 */
		public function handler_wp_enqueue_scripts()
		{
			$instances = $this->get_settings();
			if( count( $instances ) > 0 )
			{
				$need_js = array( 'confirm_link' );
				
				foreach( $instances as $instance ) 
				{
					if( isset( $instance['fb_link'] ) && in_array( $instance['fb_link'], $need_js ) )
					{
						wp_enqueue_script( 'avia_facebook_front_script' );
						break;
					}
				}
			}
		}

		/**
		 * 
		 * @since 4.3.2
		 * @param array $instance
		 * @return array
		 */
		protected function parse_args_instance( array $instance )
		{
			$new_instance = wp_parse_args( $instance, array( 
												'url'				=> 'https://www.facebook.com/kriesi.at', 
												'title'				=> __( 'Follow us on Facebook', 'avia_framework' ),
												'fb_link'			=> '',
												'fb_banner'			=> '',
												'page_title'		=> '',
												'fb_logo'			=> '',
												'content'			=> '',
												'add_info'			=> __( 'Join our Facebook community', 'avia_framework' ),
												'confirm_button'	=> __( 'Click to load facebook widget', 'avia_framework' ),
												'page_link_text'	=> __( 'Open facebook page now', 'avia_framework' )
											) );
			
			return $new_instance;
		}
		

		/**
		 * Outputs the widget
		 * 
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance )
		{
			$instance = $this->parse_args_instance( $instance );

			extract( $args, EXTR_SKIP );
			extract( $instance, EXTR_SKIP );
			
			if( empty( $url ) )
			{
				return;
			}
			
			/**
			 * Allow to change the conditional display setting - e.g. if user is opt in and allows to connect directly
			 * 
			 * @since 4.4
			 * @param string $google_link			'' | 'confirm_link' | 'page_only'
			 * @param string $context
			 * @param mixed $object
			 * @param array $args
			 * @param array $instance
			 * @return string
			 */
			 
			$original_fb_link = $fb_link;
			$fb_link = apply_filters( 'avf_conditional_setting_external_links', $fb_link, __CLASS__, $this, $args, $instance );
			if( ! in_array( $fb_link, array( '', 'confirm_link', 'page_only' ) ) )
			{
			   $fb_link = $original_fb_link;
			}
			
			$title = apply_filters( 'widget_title', $title );
		
			echo $before_widget;
			
			if ( ! empty( $title ) ) 
			{ 
				echo $before_title . $title . $after_title; 
			};
			
			$banner_bg = "";
			
			if( ! empty( $fb_link ) )
			{
				if( ! empty( $fb_banner ) && ! empty( $fb_link ) )
				{	
					$banner_bg = 'style="background-image:url(' . $fb_banner . ');"';
				}
				
				echo '<div class="av_facebook_widget_main_wrap" ' . $banner_bg . '>';
			
				echo	'<div class="av_facebook_widget_page_title_container">';
				echo		'<span class="av_facebook_widget_title">';
				echo			'<a href="'.$url.'" target="_blank" title="' . esc_html( $page_title ) . '">' . esc_html( $page_title )."</a>";
				echo		'</span>';
				echo		'<span class="av_facebook_widget_content">';
				echo			esc_html( $content );
				echo		'</span>';
				echo	'</div>';
			
			
				$html_logo = '';
			
				if( ! empty( $fb_logo ) )
				{
					$html_logo .=		'<div class="av_facebook_widget_logo_image">';
					$html_logo .=			'<img src="' . $fb_logo . '" alt="' . __( 'Logo image', 'avia_framework' ) . '">';
					$html_logo .=		'</div>';
				}
				
				echo '<div class="av_facebook_widget_main_wrap_shadow"></div>';
				echo	'<div class="av_facebook_widget_logo av_widget_img_text_confirm">';
				
				echo $html_logo;
				
				echo	'</div>';
			
				$data = "";
				if( 'confirm_link' == $fb_link )
				{
					$data  = ' data-fbhtml="' . htmlentities( $this->html_facebook_page( $url ), ENT_QUOTES, get_bloginfo( 'charset' ) ) . '"';
					$data .= ' data-fbscript="' . htmlentities( $this->get_fb_page_js_src(), ENT_QUOTES, get_bloginfo( 'charset' ) ) . '"';
					$data .= ' data-fbscript_id="' . avia_fb_likebox::FB_SCRIPT_ID . '"';
				}
				
				$btn_text = ( 'confirm_link' == $fb_link ) ? $confirm_button : $page_link_text;
				$icon = "<span class='av_facebook_widget_icon' " . av_icon_string('facebook') . "></span>";
				echo	'<a href="'.$url.'" target="_blank" class="av_facebook_widget_button av_facebook_widget_' . $fb_link . '"' . $data . '>' .$icon . esc_html( $btn_text ) . '</a>';
				
				if( ! empty( $fb_link ) )
				{
					echo	'<div class="av_facebook_widget_add_info">';
					echo		'<div class="av_facebook_widget_add_info_inner">';
					echo			'<span class="av_facebook_widget_add_info_inner_wrap">';
					echo				esc_html( $add_info );
					echo			'</span>';
					echo			'<div class="av_facebook_widget_imagebar">';
					echo			'</div>';
					echo		'</div>';
					echo	'</div>';
				}
				
				echo '</div>';		//	class="av_facebook_widget_main_wrap"
			}
			
			if( empty( $fb_link ) )
			{
					echo $this->html_facebook_page( $url );
					add_action( 'wp_footer', array( $this,'handler_output_fb_page_script' ), 10 );
			}
			
			echo $after_widget;
		}
		
		/**
		 * Create the HTML for the facebook page widget
		 * 
		 * @since 4.3.2
		 * @param string $url 
		 * @return string
		 */
		protected function html_facebook_page( $url )
		{
			$extraClass = '';
			$style = '';
			
//			$height 	= 151;						//	remainings from original widget ?????
//			$faces 		= "true";
//			$extraClass = "";
//			$style 		= "";
//			
//			
//			if( strpos( $height, "%" ) !== false )
//			{
//				$extraClass = "av_facebook_widget_wrap_positioner";
//				$style		= "style='padding-bottom:{$height}%'";
//				$height		= "100%";
//			}
					
			$html = '';
			$html .=	"<div class='av_facebook_widget_wrap {$extraClass}' {$style}>";
			$html .=		'<div class="fb-page" data-width="500" data-href="' . $url . '" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true" data-show-posts="false">';
			$html .=			'<div class="fb-xfbml-parse-ignore"></div>';
			$html .=		'</div>';
			$html .=	"</div>";
			
			return $html;
		}

		/**
		 * 
		 * @since 4.3.2
		 */
		public function handler_output_fb_page_script()
		{
			if( self::$script_loaded >= 1 ) 
			{
				return;
			}
			
			self::$script_loaded = 1;
			
			$script = '
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "' . $this->get_fb_page_js_src() . '";
  fjs.parentNode.insertBefore(js, fjs);
}(document, "script", "' . avia_fb_likebox::FB_SCRIPT_ID . '"));</script>';
			
			echo $script;
		}

		
		/**
		 * Return the js function
		 * @since 4.3.2
		 * @return string
		 */
		protected function get_fb_page_js_src()
		{
			$langcode = get_locale();
			
			/**
			 * Change language code for facebook page widget
			 * 
			 * @used_by		enfold\config-wpml\config.php				10
			 * @since 4.3.2
			 */
			$langcode = apply_filters( 'avf_fb_widget_lang_code', $langcode, 'fb-page' );
			
			$src = '//connect.facebook.net/'. $langcode .'/sdk.js#xfbml=1&version=v2.7';

			return $src;
		}


		/**
		 * 
		 * @param array $new_instance
		 * @param array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance )
		{
			$instance = $this->parse_args_instance( $old_instance );
			$fields = $this->get_field_names();
			
			foreach( $new_instance as $key => $value ) 
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = strip_tags( $value );
				}
			}
			
			return $instance;
		}

		
		/**
		 * Outputs Widgetform in backend
		 * 
		 * @param array $instance
		 */
		public function form( $instance ) 
		{
			$instance = $this->parse_args_instance( $instance );
			$fields = $this->get_field_names();
			
			foreach( $instance as $key => $value ) 
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = esc_attr( $value );
				}
			}
			
			extract( $instance );
			
			$html = new avia_htmlhelper();
			
			$banner_element = array(
								'name'		=> __( 'Banner image', 'avia_framework' ),
								'desc'		=> __( 'Upload a banner image or enter the URL', 'avia_framework' ),
								'id'		=> $this->get_field_id( 'fb_banner'),
								'id_name'	=> $this->get_field_name( 'fb_banner' ),
								'std'		=> $fb_banner,
								'type'		=> 'upload',
								'label'		=> __('Use image as banner', 'avia_framework')
							);

			$logo_element = array(
								'name'		=> __( 'Logo', 'avia_framework' ),
								'desc'		=> __( 'Upload a logo or enter the URL', 'avia_framework' ),
								'id'		=> $this->get_field_id( 'fb_logo'),
								'id_name'	=> $this->get_field_name( 'fb_logo' ),
								'std'		=> $fb_logo,
								'type'		=> 'upload',
								'label'		=> __('Use image as logo', 'avia_framework')
							);
			
	?>
		<div class="avia_widget_form avia_widget_conditional_form avia_fb_likebox_form <?php echo $fb_link;?>">
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'avia_framework'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('Enter the url to the Page. Please note that it needs to be a link to a <strong>facebook fanpage</strong>. Personal profiles are not allowed!', 'avia_framework'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo $url; ?>" /></label>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'fb_link' ); ?>"><?php _e( 'Link to facebook', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'fb_link' ); ?>" name="<?php echo $this->get_field_name( 'fb_link' ); ?>" class="widefat avia-coditional-widget-select">
					<option value="" <?php selected( '', $fb_link ) ?>><?php _e( 'Show facebook page widget &quot;Share/Like&quot; directly', 'avia_framework' ); ?></option>
					<option value="confirm_link" <?php selected( 'confirm_link', $fb_link ) ?>><?php _e( 'User must accept to show facebook page widget &quot;Share/Like&quot;', 'avia_framework' ); ?></option>
					<option value="page_only" <?php selected( 'page_only', $fb_link ) ?>><?php _e( 'Only open the facebook page - no data are sent', 'avia_framework' ); ?></option>
				</select>
			</p>
			
			<p class="av-confirm_link">
				<label for="<?php echo $this->get_field_id('confirm_button'); ?>"><?php _e('Button text confirm link to facebook:', 'avia_framework'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('confirm_button'); ?>" name="<?php echo $this->get_field_name('confirm_button'); ?>" type="text" value="<?php echo $confirm_button; ?>" /></label>
			</p>
			
			<p class="av-page_only">
				<label for="<?php echo $this->get_field_id('page_link_text'); ?>"><?php _e('Direct link to FB-page text:', 'avia_framework'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('page_link_text'); ?>" name="<?php echo $this->get_field_name('page_link_text'); ?>" type="text" value="<?php echo $page_link_text; ?>" /></label>
			</p>

			<div class="avia_fb_likebox_upload avia-fb-banner av-widgets-upload">
				<?php echo $html->render_single_element( $banner_element );?>
			</div>
				
			<p  class="av-page-title">
				<label for="<?php echo $this->get_field_id('page_title'); ?>"><?php _e('Facebook Page Title:', 'avia_framework'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('page_title'); ?>" name="<?php echo $this->get_field_name('page_title'); ?>" type="text" value="<?php echo $page_title; ?>" placeholder="<?php _e('Enter some info to the page', 'avia_framework'); ?>" /></label>
			</p>
			
			<div class="avia_fb_likebox_upload avia-fb-logo av-widgets-upload">
				<?php echo $html->render_single_element( $logo_element );?>	
			</div>
			
			<p class="av-content">
				<label for="<?php echo $this->get_field_id('content'); ?>"><?php _e('Static like count:', 'avia_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('content'); ?>" name="<?php echo $this->get_field_name('content'); ?>" rows="5" placeholder="<?php _e('2k+ likes', 'avia_framework'); ?>" value='<?php echo $content; ?>' />
				</label>
			</p>
			
			<p class="av-add_info">
				<label for="<?php echo $this->get_field_id('add_info'); ?>"><?php _e('Additional Information:', 'avia_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('add_info'); ?>" name="<?php echo $this->get_field_name('add_info'); ?>" rows="5" placeholder="<?php _e('Info displayed above the fake user profiles', 'avia_framework'); ?>" value='<?php echo $add_info; ?>' />
				</label>
			</p>
		</div>
	<?php
			
		}
	}
}










/**
 * AVIA TWEETBOX
 *
 * Widget that creates a list of latest tweets
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */



/*
Twitter widget only for compatibility reasons with older themes present. no onger used since API will be shut down by twitter
*/
if (!class_exists('avia_tweetbox'))
{
	class avia_tweetbox extends WP_Widget {

		function __construct() {
			//Constructor
			$widget_ops = array('classname' => 'tweetbox', 'description' => 'A widget to display your latest twitter messages' );
			parent::__construct( 'tweetbox', THEMENAME.' Twitter Widget', $widget_ops );
		}

		function widget($args, $instance) {
			// prints the widget

			extract($args, EXTR_SKIP);
			echo $before_widget;

			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
			$count = empty($instance['count']) ? '' : $instance['count'];
			$username = empty($instance['username']) ? '' : $instance['username'];
			$exclude_replies = empty($instance['exclude_replies']) ? '' : $instance['exclude_replies'];
			$time = empty($instance['time']) ? 'no' : $instance['time'];
			$display_image = empty($instance['display_image']) ? 'no' : $instance['display_image'];

			if ( !empty( $title ) ) { echo $before_title . "<a href='http://twitter.com/$username/' title='".strip_tags($title)."'>".$title ."</a>". $after_title; };

			$messages = tweetbox_get_tweet($count, $username, $widget_id, $time, $exclude_replies, $display_image);
			echo $messages;

			echo $after_widget;


		}

		function update($new_instance, $old_instance) {
			//save the widget
			$instance = $old_instance;
			foreach($new_instance as $key=>$value)
			{
				$instance[$key]	= strip_tags($new_instance[$key]);
			}

			delete_transient(THEMENAME.'_tweetcache_id_'.$instance['username'].'_'.$this->id_base."-".$this->number);
			return $instance;
		}

		function form($instance) {
			//widgetform in backend

			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Latest Tweets', 'count' => '3', 'username' => avia_get_option('twitter') ) );
			$title = 			isset($instance['title']) ? strip_tags($instance['title']): "";
			$count = 			isset($instance['count']) ? strip_tags($instance['count']): "";
			$username = 		isset($instance['username']) ? strip_tags($instance['username']): "";
			$exclude_replies = 	isset($instance['exclude_replies']) ? strip_tags($instance['exclude_replies']): "";
			$time = 			isset($instance['time']) ? strip_tags($instance['time']): "";
			$display_image = 	isset($instance['display_image']) ? strip_tags($instance['display_image']): "";
	?>
			<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'avia_framework'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('username'); ?>">Enter your twitter username:
			<input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo esc_attr($username); ?>" /></label></p>

			<p>
				<label for="<?php echo $this->get_field_id('count'); ?>">How many entries do you want to display: </label>
				<select class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>">
					<?php
					$list = "";
					for ($i = 1; $i <= 20; $i++ )
					{
						$selected = "";
						if($count == $i) $selected = 'selected="selected"';

						$list .= "<option $selected value='$i'>$i</option>";
					}
					$list .= "</select>";
					echo $list;
					?>


			</p>

			<p>
				<label for="<?php echo $this->get_field_id('exclude_replies'); ?>">Exclude @replies: </label>
				<select class="widefat" id="<?php echo $this->get_field_id('exclude_replies'); ?>" name="<?php echo $this->get_field_name('exclude_replies'); ?>">
					<?php
					$list = "";
					$answers = array('yes','no');
					foreach ($answers as $answer)
					{
						$selected = "";
						if($answer == $exclude_replies) $selected = 'selected="selected"';

						$list .= "<option $selected value='$answer'>$answer</option>";
					}
					$list .= "</select>";
					echo $list;
					?>


			</p>

			<p>
				<label for="<?php echo $this->get_field_id('time'); ?>">Display time of tweet</label>
				<select class="widefat" id="<?php echo $this->get_field_id('time'); ?>" name="<?php echo $this->get_field_name('time'); ?>">
					<?php
					$list = "";
					$answers = array('yes','no');
					foreach ($answers as $answer)
					{
						$selected = "";
						if($answer == $time) $selected = 'selected="selected"';

						$list .= "<option $selected value='$answer'>$answer</option>";
					}
					$list .= "</select>";
					echo $list;
					?>


			</p>

			<p>
				<label for="<?php echo $this->get_field_id('display_image'); ?>">Display Twitter User Avatar</label>
				<select class="widefat" id="<?php echo $this->get_field_id('display_image'); ?>" name="<?php echo $this->get_field_name('display_image'); ?>">
					<?php
					$list = "";
					$answers = array('yes','no');
					foreach ($answers as $answer)
					{
						$selected = "";
						if($answer == $display_image) $selected = 'selected="selected"';

						$list .= "<option $selected value='$answer'>$answer</option>";
					}
					$list .= "</select>";
					echo $list;
					?>
			</p>



		<?php
		}
	}
}

if(!function_exists('tweetbox_get_tweet'))
{
	function tweetbox_get_tweet($count, $username, $widget_id, $time='yes', $exclude_replies='yes', $avatar = 'yes')
	{
			$filtered_message = "";
			$output = "";
			$iterations = 0;

			$cache = get_transient(THEMENAME.'_tweetcache_id_'.$username.'_'.$widget_id);

			if($cache)
			{
				$tweets = get_option(THEMENAME.'_tweetcache_'.$username.'_'.$widget_id);
			}
			else
			{
				//$response = wp_remote_get( 'http://api.twitter.com/1/statuses/user_timeline.xml?screen_name='.$username );
				$response = wp_remote_get( 'http://api.twitter.com/1/statuses/user_timeline.xml?include_rts=true&screen_name='.$username );
				if (!is_wp_error($response))
				{
					$xml = @simplexml_load_string($response['body']);
					//follower: (int) $xml->status->user->followers_count

					if( empty( $xml->error ) )
				    {
				    	if ( isset($xml->status[0]))
				    	{

				    	    $tweets = array();
				    	    foreach ($xml->status as $tweet)
				    	    {
				    	    	if($iterations == $count) break;

				    	    	$text = (string) $tweet->text;
				    	    	if($exclude_replies == 'no' || ($exclude_replies == 'yes' && $text[0] != "@"))
				    	    	{
				    	    		$iterations++;
				    	    		$tweets[] = array(
				    	    			'text' => tweetbox_filter( $text ),
				    	    			'created' =>  strtotime( $tweet->created_at ),
				    	    			'user' => array(
				    	    				'name' => (string)$tweet->user->name,
				    	    				'screen_name' => (string)$tweet->user->screen_name,
				    	    				'image' => (string)$tweet->user->profile_image_url,
				    	    				'utc_offset' => (int) $tweet->user->utc_offset[0],
				    	    				'follower' => (int) $tweet->user->followers_count

				    	    			));
				    			}
				    		}

				    		set_transient(THEMENAME.'_tweetcache_id_'.$username.'_'.$widget_id, 'true', 60*30);
				    		update_option(THEMENAME.'_tweetcache_'.$username.'_'.$widget_id, $tweets);
				    	}
				    }
				}
			}



			if(!isset($tweets[0]))
			{
				$tweets = get_option(THEMENAME.'_tweetcache_'.$username.'_'.$widget_id);
			}

		    if(isset($tweets[0]))
		    {
		    	$time_format = apply_filters( 'avia_widget_time', get_option('date_format')." - ".get_option('time_format'), 'tweetbox' );

		    	foreach ($tweets as $message)
		    	{
		    		$output .= '<li class="tweet">';
		    		if($avatar == "yes") $output .= '<div class="tweet-thumb"><a href="http://twitter.com/'.$username.'" title=""><img src="'.$message['user']['image'].'" alt="" /></a></div>';
		    		$output .= '<div class="tweet-text avatar_'.$avatar.'">'.$message['text'];
		    		if($time == "yes") $output .= '<div class="tweet-time">'.date_i18n( $time_format, $message['created'] + $message['user']['utc_offset']).'</div>';
		    		$output .= '</div></li>';
				}
		    }


			if($output != "")
			{
				$filtered_message = "<ul class='tweets'>$output</ul>";
			}
			else
			{
				$filtered_message = "<ul class='tweets'><li>No public Tweets found</li></ul>";
			}

			return $filtered_message;
	}
}

if(!function_exists('tweetbox_filter'))
{
	function tweetbox_filter($text) {
	    // Props to Allen Shaw & webmancers.com & Michael Voigt
	    $text = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\">$1</a>", $text);
	    $text = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\">$1</a>", $text);
	    $text = preg_replace("/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i","<a href=\"mailto://$1\" class=\"twitter-link\">$1</a>", $text);
	    $text = preg_replace("/#([\p{L}\p{Mn}]+)/u", "<a class=\"twitter-link\" href=\"http://search.twitter.com/search?q=\\1\">#\\1</a>", $text);
	    $text = preg_replace("/@([\p{L}\p{Mn}]+)/u", "<a class=\"twitter-link\" href=\"http://twitter.com/\\1\">@\\1</a>", $text);

	    return $text;
	}
}









/**
 * AVIA NEWSBOX
 *
 * Widget that creates a list of latest news entries
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */

if (!class_exists('avia_newsbox'))
{
	class avia_newsbox extends WP_Widget {

		var $avia_term = '';
		var $avia_post_type = '';
		var $avia_new_query = '';

		function __construct()
		{
			$widget_ops = array('classname' => 'newsbox', 'description' => __('A Sidebar widget to display latest post entries in your sidebar', 'avia_framework') );

			parent::__construct( 'newsbox', THEMENAME.' Latest News', $widget_ops );
		}

		function widget($args, $instance)
		{
			global $avia_config;

			extract($args, EXTR_SKIP);
			echo $before_widget;

			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
			$count = empty($instance['count']) ? '' : $instance['count'];
			$cat = empty($instance['cat']) ? '' : $instance['cat'];
			$excerpt = empty($instance['excerpt']) ? '' : $instance['excerpt'];
			$image_size = isset($avia_config['widget_image_size']) ? $avia_config['widget_image_size'] : 'widget';

			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };


			if(empty($this->avia_term))
			{
				$additional_loop = new WP_Query("cat=".$cat."&posts_per_page=".$count);
			}
			else
			{
				$catarray = explode(',', $cat);


				if(empty($catarray[0]))
				{
					$new_query = array("posts_per_page"=>$count,"post_type"=>$this->avia_post_type);
				}
				else
				{
					if($this->avia_new_query)
					{
						$new_query = $this->avia_new_query;
					}
					else
					{
						$new_query = array(	"posts_per_page"=>$count, 'tax_query' => array(
														array( 'taxonomy' => $this->avia_term,
															   'field' => 'id',
															   'terms' => explode(',', $cat),
															   'operator' => 'IN')
															  )
														);
					}
				}

				$additional_loop = new WP_Query($new_query);
			}

			if($additional_loop->have_posts()) :



			echo '<ul class="news-wrap image_size_'.$image_size.'">';
			while ($additional_loop->have_posts()) : $additional_loop->the_post();

			$format = "";
			if(empty($this->avia_post_type)) 	$format = $this->avia_post_type;
			if(empty($format)) 					$format = get_post_format();
	     	if(empty($format)) 					$format = 'standard';
			
			$the_id = get_the_ID();
			$link = get_post_meta( $the_id  ,'_portfolio_custom_link', true) != "" ? get_post_meta( $the_id ,'_portfolio_custom_link_url', true) : get_permalink();
			
			
			echo '<li class="news-content post-format-'.$format.'">';

			//check for preview images:
			$image = "";

			if(!current_theme_supports('force-post-thumbnails-in-widget'))
			{
				$slides = avia_post_meta(get_the_ID(), 'slideshow', true);

				if( $slides != "" && !empty( $slides[0]['slideshow_image'] ) )
				{
					$image = avia_image_by_id($slides[0]['slideshow_image'], $image_size, 'image');
				}
			}

			if(current_theme_supports( 'post-thumbnails' ) && !$image )
			{
				$image = get_the_post_thumbnail( $the_id, $image_size );
			}

			$time_format = apply_filters( 'avia_widget_time', get_option('date_format')." - ".get_option('time_format'), 'avia_newsbox' );


			echo "<a class='news-link' title='".get_the_title()."' href='".$link."'>";

			$nothumb = (!$image) ? 'no-news-thumb' : '';

			echo "<span class='news-thumb $nothumb'>";
			echo $image;
			echo "</span>";
			if(empty($avia_config['widget_image_size']) || 'display title and excerpt' != $excerpt)
			{
				echo "<strong class='news-headline'>".get_the_title();
				
				if($time_format)
				{
					echo "<span class='news-time'>".get_the_time($time_format)."</span>";	
				}
				
				echo "</strong>";
			}
			echo "</a>";

			if( 'display title and excerpt' == $excerpt )
			{
				echo "<div class='news-excerpt'>";

				if(!empty($avia_config['widget_image_size']))
				{
					echo "<a class='news-link-inner' title='".get_the_title()."' href='".$link."'>";
					echo "<strong class='news-headline'>".get_the_title()."</strong>";
					echo "</a>";
					if($time_format)
					{
						echo "<span class='news-time'>".get_the_time($time_format)."</span>";	
					}

				}
				the_excerpt();
				echo "</div>";
			}

			echo '</li>';


			endwhile;
			echo "</ul>";
			wp_reset_postdata();
			endif;


			echo $after_widget;

		}


		function update($new_instance, $old_instance)
		{
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['count'] = strip_tags($new_instance['count']);
			$instance['excerpt'] = strip_tags($new_instance['excerpt']);
			$instance['cat'] = implode(',',$new_instance['cat']);
			return $instance;
		}



		function form($instance)
		{
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => '', 'cat' => '', 'excerpt'=>'' ) );
			$title = strip_tags($instance['title']);
			$count = strip_tags($instance['count']);
			$excerpt = strip_tags($instance['excerpt']);


			$elementCat = array("name" 	=> __("Which categories should be used for the portfolio?", 'avia_framework'), 
								"desc" 	=> __("You can select multiple categories here", 'avia_framework'),
					            "id" 	=> $this->get_field_name('cat')."[]",
					            "type" 	=> "select",
					            "std"   => strip_tags($instance['cat']),
					            "class" => "",
	            				"multiple"=>6,
					            "subtype" => "cat");
			//check if a different taxonomy than the default is set
			if(!empty($this->avia_term))
			{
				$elementCat['taxonomy'] = $this->avia_term;
			}




			$html = new avia_htmlhelper();

	?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'avia_framework'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>

			<p>
				<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('How many entries do you want to display: ', 'avia_framework'); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>">
					<?php
					$list = "";
					for ($i = 1; $i <= 20; $i++ )
					{
						$selected = "";
						if($count == $i) $selected = 'selected="selected"';

						$list .= "<option $selected value='$i'>$i</option>";
					}
					$list .= "</select>";
					echo $list;
					?>


			</p>

			<p><label for="<?php echo $this->get_field_id('cat'); ?>"><?php _e('Choose the categories you want to display (multiple selection possible):', 'avia_framework'); ?>
			<?php echo $html->select($elementCat); ?>
			</label></p>

			<p>
				<label for="<?php echo $this->get_field_id('excerpt'); ?>"><?php _e('Display title only or title &amp; excerpt', 'avia_framework'); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id('excerpt'); ?>" name="<?php echo $this->get_field_name('excerpt'); ?>">
					<?php
					$list = "";
					$answers = array(
								'show title only'			=>	__( 'show title only', 'avia_framework' ),
								'display title and excerpt'	=>	__('display title and excerpt', 'avia_framework')
								);
					
					foreach ( $answers as $key => $answer )
					{
						$selected = "";
						if( $key == $excerpt ) $selected = 'selected="selected"';

						$list .= "<option $selected value='$key'>$answer</option>";
					}
					$list .= "</select>";
					echo $list;
					?>


			</p>


	<?php
		}
	}
}


/**
 * AVIA PORTFOLIOBOX
 *
 * Widget that creates a list of latest portfolio entries. Basically the same widget as the newsbox with some minor modifications, therefore it just extends the Newsbox
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */

if (!class_exists('avia_portfoliobox'))
{
	class avia_portfoliobox extends avia_newsbox
	{
		function __construct()
		{
			$this->avia_term = 'portfolio_entries';
			$this->avia_post_type = 'portfolio';
			$this->avia_new_query = ''; //set a custom query here


			$widget_ops = array('classname' => 'newsbox', 'description' => __('A Sidebar widget to display latest portfolio entries in your sidebar', 'avia_framework') );

			WP_Widget::__construct( 'portfoliobox', THEMENAME.' Latest Portfolio', $widget_ops );
		}
	}
}



/**
 * AVIA SOCIALCOUNT
 *
 * Widget that retrieves, stores and displays the number of twitter and rss followers
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */

if (!class_exists('avia_socialcount'))
{
	class avia_socialcount extends WP_Widget {

		function __construct() {
			//Constructor
			$widget_ops = array('classname' => 'avia_socialcount', 'description' => __('A widget to display a link to your twitter profile and rss feed', 'avia_framework') );
			parent::__construct( 'avia_socialcount', THEMENAME.' RSS Link and Twitter Account', $widget_ops );
		}

		function widget($args, $instance) {
			// prints the widget

			extract($args, EXTR_SKIP);
			$twitter = empty($instance['twitter']) ? '' : $instance['twitter'];
			$rss 	 = empty($instance['rss'])     ? '' : $instance['rss'];
			$rss = preg_replace('!https?:\/\/feeds.feedburner.com\/!','',$rss);


			if(!empty($twitter) || !empty($rss))
			{
				$addClass = "asc_multi_count";
				if(!isset($twitter) || !isset($rss)) $addClass = 'asc_single_count';

				echo $before_widget;
				$output = "";
				if(!empty($twitter))
				{
					$link = 'http://twitter.com/'.$twitter.'/';
					$before = apply_filters('avf_social_widget', "", 'twitter');
					$output .= "<a href='$link' class='asc_twitter $addClass'>{$before}<strong class='asc_count'>".__('Follow','avia_framework')."</strong><span>".__('on Twitter','avia_framework')."</span></a>";
					
				}

				if($rss)
				{
					$output .= "<a href='$rss' class='asc_rss $addClass'>".apply_filters('avf_social_widget',"", 'rss')."<strong class='asc_count'>".__('Subscribe','avia_framework')."</strong><span>".__('to RSS Feed','avia_framework')."</span></a>";
				}

				echo $output;
				echo $after_widget;
			}
		}



		function update($new_instance, $old_instance) {
			//save the widget
			$instance = $old_instance;
			foreach($new_instance as $key=>$value)
			{
				$instance[$key]	= strip_tags($new_instance[$key]);
			}

			return $instance;
		}

		function form($instance) {
			//widgetform in backend

			$instance = wp_parse_args( (array) $instance, array('rss' => avia_get_option('feedburner'), 'twitter' => avia_get_option('twitter') ) );
			$twitter = empty($instance['twitter']) ? '' :  strip_tags($instance['twitter']);
			$rss 	 = empty($instance['rss'])     ? '' :  strip_tags($instance['rss']);
	?>
			<p>
			<label for="<?php echo $this->get_field_id('twitter'); ?>"><?php _e('Twitter Username:', 'avia_framework'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('twitter'); ?>" name="<?php echo $this->get_field_name('twitter'); ?>" type="text" value="<?php echo esc_attr($twitter); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('rss'); ?>"><?php _e('Enter your feed url:', 'avia_framework'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('rss'); ?>" name="<?php echo $this->get_field_name('rss'); ?>" type="text" value="<?php echo esc_attr($rss); ?>" /></label></p>



		<?php
		}
	}
}




/**
 * AVIA ADVERTISING WIDGET
 *
 * Widget that retrieves, stores and displays the number of twitter and rss followers
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */


//multiple images
if (!class_exists('avia_partner_widget'))
{
	class avia_partner_widget extends WP_Widget {

		function __construct() {

			$this->add_cont = 2;
			//Constructor
			$widget_ops = array('classname' => 'avia_partner_widget', 'description' => __('An advertising widget that displays 2 images with 125 x 125 px in size', 'avia_framework') );
			parent::__construct( 'avia_partner_widget', THEMENAME.' Advertising Area', $widget_ops );
		}

		function widget($args, $instance)
		{
			extract($args, EXTR_SKIP);
			echo $before_widget;

			global $kriesiaddwidget, $firsttitle;
			$kriesiaddwidget ++;

			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
			$image_url = empty($instance['image_url']) ? '<span class="avia_parnter_empty"><span>'.__('Advertise here','avia_framework').'</span></span>' : '<img class="rounded" src="'.$instance['image_url'].'" title="'.$title.'" alt="'.$title.'"/>';
			$ref_url = empty($instance['ref_url']) ? '#' : apply_filters('widget_comments_title', $instance['ref_url']);
			$image_url2 = empty($instance['image_url2']) ? '<span class="avia_parnter_empty"><span>'.__('Advertise here','avia_framework').'</span></span>' : '<img class="rounded" src="'.$instance['image_url2'].'" title="'.$title.'" alt="'.$title.'"/>';
			$ref_url2 = empty($instance['ref_url2']) ? '#' : apply_filters('widget_comments_title', $instance['ref_url2']);

			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
			echo '<a target="_blank" href="'.$ref_url.'" class="preloading_background  avia_partner1 link_list_item'.$kriesiaddwidget.' '.$firsttitle.'" >'.$image_url.'</a>';
			if($this->add_cont == 2) echo '<a target="_blank" href="'.$ref_url2.'" class="preloading_background avia_partner2 link_list_item'.$kriesiaddwidget.' '.$firsttitle.'" >'.$image_url2.'</a>';
			echo $after_widget;

			if($title == '')
			{
				$firsttitle = 'no_top_margin';
			}

		}


		function update($new_instance, $old_instance) {
			//save the widget
			$instance = $old_instance;
			foreach($new_instance as $key=>$value)
			{
				$instance[$key]	= strip_tags($new_instance[$key]);
			}
			return $instance;
		}



		function form($instance)
		{
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'image_url' => '', 'ref_url' => '', 'image_url2' => '', 'ref_url2' => '' ) );
			$title = strip_tags($instance['title']);
			$image_url = strip_tags($instance['image_url']);
			$ref_url = strip_tags($instance['ref_url']);
			$image_url2 = strip_tags($instance['image_url2']);
			$ref_url2 = strip_tags($instance['ref_url2']);
	?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'avia_framework'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('image_url'); ?>"><?php _e('Image URL:', 'avia_framework'); ?> <?php if($this->add_cont == 2) echo "(125px * 125px):"; ?>
			<input class="widefat" id="<?php echo $this->get_field_id('image_url'); ?>" name="<?php echo $this->get_field_name('image_url'); ?>" type="text" value="<?php echo esc_attr($image_url); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('ref_url'); ?>"><?php _e('Referal URL:', 'avia_framework'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('ref_url'); ?>" name="<?php echo $this->get_field_name('ref_url'); ?>" type="text" value="<?php echo esc_attr($ref_url); ?>" /></label></p>

			<?php if($this->add_cont == 2)
			{ ?>

					<p><label for="<?php echo $this->get_field_id('image_url2'); ?>"><?php _e('Image URL 2: (125px * 125px):', 'avia_framework'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('image_url2'); ?>" name="<?php echo $this->get_field_name('image_url2'); ?>" type="text" value="<?php echo esc_attr($image_url2); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('ref_url2'); ?>"><?php _e('Referal URL 2:', 'avia_framework'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('ref_url2'); ?>" name="<?php echo $this->get_field_name('ref_url2'); ?>" type="text" value="<?php echo esc_attr($ref_url2); ?>" /></label></p>

			<?php }?>

	<?php
		}
	}
}


if (!class_exists('avia_one_partner_widget'))
{
	//one image
	class avia_one_partner_widget extends avia_partner_widget
	{
		function __construct()
		{

			$this->add_cont = 1;

			$widget_ops = array('classname' => 'avia_one_partner_widget', 'description' => __('An advertising widget that displays 1 big image', 'avia_framework') );

			parent::__construct( 'avia_one_partner_widget', THEMENAME.' Big Advertising Area', $widget_ops );
		}
	}
}



/**
 * 
 *
 * Widget that retrieves, stores and displays the number of twitter and rss followers
 *
 * 
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */

if( ! class_exists( 'avia_combo_widget' ) )
{
	/**
	 * AVIA COMBO WIDGET
	 * 
	 * Widget that displays your popular posts, recent posts, recent comments and a tagcloud in a tabbed section
	 * 
	 * @package AviaFramework
	 * 
	 * @since 4.4.2 extended and modified by günter
	 */
	class avia_combo_widget extends Avia_Widget 
	{
		/**
		 * Constructor
		 */
		public function __construct() 
		{
			$widget_ops = array(
						'classname' => 'avia_combo_widget', 
						'description' => __( 'A widget that displays your popular posts, recent posts, recent comments and a tagcloud', 'avia_framework' ) 
					);
			
			parent::__construct( 'avia_combo_widget', THEMENAME.' Combo Widget', $widget_ops );
			
			/**
			 * Hook to enable 
			 */
			add_filter( 'avf_disable_frontend_assets', array( $this, 'handler_enable_shortcodes' ), 50, 1 );
		}

		/**
		 * 
		 * @since 4.4.2
		 */
		public function __destruct() 
		{
			parent::__destruct();
		}		
		
		/**
		 * 
		 * @since 4.4.2
		 * @param array $instance
		 * @return array
		 */
		protected function parse_args_instance( array $instance )
		{
			/**
			 * Backwards comp. only
			 * 
			 * @since 4.4.2 'count' was removed
			 */
			$fallback = isset( $instance['count'] );
			
			$new_instance = wp_parse_args( $instance, array( 
												'show_popular'		=> 4,
												'show_recent'		=> 4,
												'show_comments'		=> 4,
												'show_tags'			=> 45,
												'tab_1'				=> 'popular',
												'tab_2'				=> 'recent',
												'tab_3'				=> 'comments',
												'tab_4'				=> 'tagcloud',
											) );
			
			if( $fallback )
			{
				$new_instance['show_popular'] = $instance['count'];
				$new_instance['show_recent'] = $instance['count'];
				$new_instance['show_comments'] = $instance['count'];
				unset( $new_instance['count'] );
			}
			
			return $new_instance;
		}

		/**
		 * prints the widget
		 * 
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance )
		{
			$instance = $this->parse_args_instance( $instance );
			
			extract( $args );
			
			echo $before_widget;
			
			$used_tabs = 0;
			
			for( $tab_nr = 1; $tab_nr < 5; $tab_nr++ )
			{
				$key = 'tab_' . $tab_nr;
				
				if( empty( $instance[ $key ] ) )
				{
					continue;
				}
				
				if( ! in_array( $instance[ $key ], array( 'popular', 'recent', 'comments', 'tagcloud' ) ) )
				{
					continue;
				}
				
				$used_tabs++;
				$add_class = '';
				$add_class2 = '';
				
				if( 1 == $used_tabs )
				{
					echo "<div class='tabcontainer border_tabs top_tab tab_initial_open tab_initial_open__1'>";
					$add_class = ' first_tab active_tab ';
					$add_class2 = 'active_tab_content';
				}
				
				switch( $instance[ $key ] )
				{
					case 'popular':
							$args = array(
												'posts_per_page'	=> $instance['show_popular'],
												'orderby'			=> 'comment_count',
												'order'				=> 'desc'
											);
						
							echo '<div class="tab widget_tab_popular' . $add_class . '"><span>' . __( 'Popular', 'avia_framework' ) . '</span></div>';
							echo "<div class='tab_content {$add_class2}'>";
									avia_combo_widget::get_post_list( $args );
							echo "</div>";
							break;
					case 'recent':
							$args = array(
												'posts_per_page'	=> $instance['show_recent'],
												'orderby'			=> 'post_date',
												'order'				=> 'desc'
											);
							echo '<div class="tab widget_tab_recent' . $add_class . '"><span>'.__('Recent', 'avia_framework').'</span></div>';
							echo "<div class='tab_content {$add_class2}'>";
									avia_combo_widget::get_post_list( $args );
							echo "</div>";
							break;
					case 'comments':
							$args = array(
												'number'	=> $instance['show_comments'], 
												'status'	=> 'approve', 
												'order'		=> 'DESC'
											);
							echo '<div class="tab widget_tab_comments' . $add_class . '"><span>'.__('Comments', 'avia_framework').'</span></div>';
							echo "<div class='tab_content {$add_class2}'>";
									avia_combo_widget::get_comment_list( $args );
							echo "</div>";
							break;
					case 'tagcloud':
							$args = array(
												'number'	=> $instance['show_tags'], 
												'smallest'	=> 12, 
												'largest'	=> 12,
												'unit'		=> 'px'
											);
							echo '<div class="tab last_tab widget_tab_tags' . $add_class . '"><span>'.__('Tags', 'avia_framework').'</span></div>';
							echo "<div class='tab_content tagcloud {$add_class2}'>";
										wp_tag_cloud( $args );
							echo "</div>";
						break;
				}
			}
			
			if( $used_tabs > 0 )
			{
				echo "</div>";
			}
			
			echo $after_widget;
		}


		/**
		 * 
		 * @param array $new_instance
		 * @param array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance )
		{
			$instance = $this->parse_args_instance( $old_instance );
			$fields = $this->get_field_names();
			
			foreach( $new_instance as $key => $value ) 
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = strip_tags( $value );
				}
			}
			
			return $instance;
		}

		
		/**
		 * Widgetform in backend
		 * 
		 * @param array $instance
		 */
		public function form( $instance ) 
		{
			$instance = $this->parse_args_instance( $instance );
			
			extract( $instance );
			
			$tab_content = array(
						0				=> __( 'No content', 'avia_framework' ),
						'popular'		=> __( 'Popular posts', 'avia_framework' ),
						'recent'		=> __( 'Recent posts', 'avia_framework' ),
						'comments'		=> __( 'Newest comments', 'avia_framework' ),
						'tagcloud'		=> __( 'Tag cloud', 'avia_framework' ),
				
				);
	?>
			<p><label for="<?php echo $this->get_field_id( 'show_popular' ); ?>"><?php _e( 'Number of popular posts', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'show_popular' ); ?>" name="<?php echo $this->get_field_name( 'show_popular' ); ?>" class="widefat">
	<?php		
					echo Avia_Widget::number_options( 1, 30, $show_popular );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'show_recent' ); ?>"><?php _e( 'Number of recent posts', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'show_recent' ); ?>" name="<?php echo $this->get_field_name( 'show_recent' ); ?>" class="widefat">
	<?php		
					echo Avia_Widget::number_options( 1, 30, $show_recent );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'show_comments' ); ?>"><?php _e( 'Number of newest comments', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'show_comments' ); ?>" name="<?php echo $this->get_field_name( 'show_comments' ); ?>" class="widefat">
	<?php		
					echo Avia_Widget::number_options( 1, 30, $show_comments );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'show_tags' ); ?>"><?php _e( 'Number of tags for tag cloud', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'show_tags' ); ?>" name="<?php echo $this->get_field_name( 'show_tags' ); ?>" class="widefat">
	<?php		
					echo Avia_Widget::number_options( 1, 100, $show_tags );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'tab_1' ); ?>"><?php _e( 'Content of first tab', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'tab_1' ); ?>" name="<?php echo $this->get_field_name( 'tab_1' ); ?>" class="widefat">
	<?php		
					$tab_content_first = $tab_content;
					unset( $tab_content_first[0] );
					echo Avia_Widget::options_from_array( $tab_content_first, $tab_1 );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'tab_2' ); ?>"><?php _e( 'Content of next tab', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'tab_2' ); ?>" name="<?php echo $this->get_field_name( 'tab_2' ); ?>" class="widefat">
	<?php		
					echo Avia_Widget::options_from_array( $tab_content, $tab_2 );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'tab_3' ); ?>"><?php _e( 'Content of next tab', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'tab_3' ); ?>" name="<?php echo $this->get_field_name( 'tab_3' ); ?>" class="widefat">
	<?php		
					echo Avia_Widget::options_from_array( $tab_content, $tab_3 );
	?>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'tab_4' ); ?>"><?php _e( 'Content of next tab', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'tab_4' ); ?>" name="<?php echo $this->get_field_name( 'tab_4' ); ?>" class="widefat">
	<?php		
					echo Avia_Widget::options_from_array( $tab_content, $tab_4 );
	?>
				</select>
			</p>
	<?php
		}
		
		
		/**
		 * This widget needs tab.css and tab.js to work properly.
		 * 
		 * @since 4.4.2
		 * @added_by Günter
		 * @param array $disabled
		 * @return array
		 */
		public function handler_enable_shortcodes( array $disabled )
		{
			$settings = $this->get_settings();
			
			/**
			 * Search page might lead to no result and in this case we activate this widget manually
			 */
			if( ( count( $settings ) > 0 ) || is_search() )
			{
				unset( $disabled['av_tab_container'] );
			}
			
			return $disabled;
		}

		/**
		 * Get postlist by query args
		 * (up to 4.4.2 this was function avia_get_post_list( $avia_new_query , $excerpt = false)
		 * 
		 * @since 4.4.2
		 * @added_by Günter
		 * @param array $args
		 * @param type $excerpt
		 */
		static public function get_post_list( array $args , $excerpt = false )
		{
			global $avia_config;
			
			$image_size = isset( $avia_config['widget_image_size'] ) ? $avia_config['widget_image_size'] : 'widget';
			
			$additional_loop = new WP_Query($args);

			if( $additional_loop->have_posts() )
			{
				echo '<ul class="news-wrap">';
				
				while ( $additional_loop->have_posts() )
				{
					$additional_loop->the_post();

					$format = "";
					if( get_post_type() != 'post' ) 		
					{
						$format = get_post_type();
					}
					
					if( empty( $format ) ) 					
					{
						$format = get_post_format();
					}
					if( empty( $format ) ) 					
					{
						$format = 'standard';
					}

					echo '<li class="news-content post-format-' . $format . '">';

					//check for preview images:
					$image = "";

					if( ! current_theme_supports( 'force-post-thumbnails-in-widget' ) )
						{
						$slides = avia_post_meta( get_the_ID(), 'slideshow' );

						if( $slides != "" && ! empty( $slides[0]['slideshow_image'] ) )
						{
							$image = avia_image_by_id( $slides[0]['slideshow_image'], 'widget', 'image' );
						}
					}

					if( ! $image && current_theme_supports( 'post-thumbnails' ) )
					{
						$image = get_the_post_thumbnail( get_the_ID(), $image_size );
					}

					$time_format = apply_filters( 'avia_widget_time', get_option('date_format') . " - " . get_option('time_format'), 'avia_get_post_list' );

					$nothumb = ( ! $image) ? 'no-news-thumb' : '';

					echo "<a class='news-link' title='" . get_the_title() . "' href='" . get_permalink() . "'>";
					echo	"<span class='news-thumb $nothumb'>";
					echo		$image;
					echo	"</span>";
					echo	"<strong class='news-headline'>".avia_backend_truncate(get_the_title(), 55," ");
					echo		"<span class='news-time'>".get_the_time($time_format)."</span>";
					echo	"</strong>";
					echo "</a>";

					if( 'display title and excerpt' == $excerpt )
					{
						echo "<div class='news-excerpt'>";
								the_excerpt();
						echo "</div>";
					}

					echo '</li>';
				}
				
				echo "</ul>";
				wp_reset_postdata();
			}
		}
		
		/**
		 * Get commentlist by query args
		 * (up to 4.4.2 this was function avia_get_comment_list( $avia_new_query )
		 * 
		 * @since 4.4.2
		 * @added_by Günter
		 * @param array $args
		 */
		static public function get_comment_list( array $args )
		{
			$time_format = apply_filters( 'avia_widget_time', get_option( 'date_format' ) . " - " . get_option( 'time_format' ), 'avia_get_comment_list' );

			$comments = get_comments( $args );

			if( ! empty( $comments ) )
			{
				echo '<ul class="news-wrap">';
				
				foreach( $comments as $comment )
				{
					if( $comment->comment_author != 'ActionScheduler' )
					{
						$gravatar_alt = esc_html( $comment->comment_author );
						
						echo '<li class="news-content">';
						echo	"<a class='news-link' title='" . get_the_title( $comment->comment_post_ID ) . "' href='" . get_comment_link($comment) . "'>";
						echo		"<span class='news-thumb'>";
						echo			get_avatar( $comment, '48', '', $gravatar_alt );
						echo		"</span>";
						echo		"<strong class='news-headline'>" . avia_backend_truncate( $comment->comment_content, 55," " );

						if($time_format)
						{
							echo		"<span class='news-time'>" . get_comment_date( $time_format, $comment->comment_ID ) . " " . __( 'by', 'avia_framework' ) . " " . $comment->comment_author . "</span>";
						}
						echo		"</strong>";
						echo	"</a>";
						echo '</li>';
					}
				}
				
				echo "</ul>";
				wp_reset_postdata();
			}
		}
	}
}

/*-----------------------------------------------------------------------------------
get posts posts
-----------------------------------------------------------------------------------*/
if ( ! function_exists('avia_get_post_list'))
{
	function avia_get_post_list( $avia_new_query , $excerpt = false)
	{
		_deprecated_function( 'avia_get_post_list', '4.4.2', 'avia_combo_widget::get_post_list( $args )');
		
		$avia_new_query = wp_parse_args( $avia_new_query );
		avia_combo_widget::get_post_list( $avia_new_query, $excerpt );
	}
	
}

if (!function_exists('avia_get_comment_list'))
{
	function avia_get_comment_list( $avia_new_query )
	{
		_deprecated_function( 'avia_get_comment_list', '4.4.2', 'avia_combo_widget::get_comment_list( $args )');
		
		$avia_new_query = wp_parse_args( $avia_new_query );
		avia_combo_widget::get_comment_list( $avia_new_query);
	}

	
}



/*
	Google Maps Widget

	Copyright 2009  Clark Nikdel Powell  (email : taylor@cnpstudio.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( ! class_exists('avia_google_maps') )
{
	class avia_google_maps extends Avia_Widget 
	{

		/**
		 * 
		 */
		public function __construct() 
		{
			$widget_ops = array(
								'classname'		=> 'avia_google_maps', 
								'description'	=> __( 'Add a google map to your blog or site', 'avia_framework' ) 
							);
			
			parent::__construct( 'avia_google_maps', THEMENAME.' Google Maps Widget', $widget_ops );
			
//            add_action( 'admin_enqueue_scripts', array( $this,'handler_print_google_maps_scripts' ) );
		}
		
		
		/**
		 * @since 4.3.2
		 */
		public function __destruct() 
		{
			parent::__destruct();
		}
		
		/**
		 * 
		 * @since 4.3.2
		 * @param array $instance
		 * @return array
		 */
		protected function parse_args_instance( array $instance )
		{
			$SGMoptions = get_option( 'SGMoptions', array() ); // get options defined in admin page ????
			$SGMoptions =  wp_parse_args( $SGMoptions, array( 
											'zoom'				=>	'15',			// 1 - 19
											'type'				=>	'ROADMAP',		// ROADMAP, SATELLITE, HYBRID, TERRAIN
											'content'			=>	'',
										) );
			
			$new_instance = wp_parse_args( $instance, array( 
											'title'				=>	'',
											'lat'				=>	'0',
											'lng'				=>	'0',
											'zoom'				=>	$SGMoptions['zoom'],
											'type'				=>	$SGMoptions['type'],
											'directionsto'		=>	'',
											'content'			=>	$SGMoptions['content'],
											'width'				=>	'',
											'height'			=>	'',
											'street-address'	=>	'',
											'city'				=>	'',
											'state'				=>	'',
											'postcode'			=>	'',
											'country'			=>	'',
											'icon'				=>	'',
											'google_link'		=>	'',
											'confirm_button'	=>	__( 'Click to load Google Maps', 'avia_framework' ),
											'page_link_text'	=>	__( 'Open Google Maps in a new window', 'avia_framework' ),
											'google_fallback'	=>	''
										) );
			
			return $new_instance;
		}
		

		/**
		 * Output the content of the widget
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) 
		{
			$instance = $this->parse_args_instance( $instance );
			$fields = $this->get_field_names();
			
			foreach( $instance as $key => $value ) 
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = esc_attr( $value );
				}
			}
			
			extract( $args );
			extract( $instance );

			$street_address = $instance['street-address'];
			
			if( empty( $lat ) || empty( $lng ) )
			{
				return;
			}
			
			/**
			 * Allow to change the conditional display setting - e.g. if user is opt in and allows to connect directly
			 * 
			 * @since 4.4
			 * @param string $google_link			'' | 'confirm_link' | 'page_only'
			 * @param string $context
			 * @param mixed $object
			 * @param array $args
			 * @param array $instance
			 * @return string
			 */
			$original_google_link = $google_link;
			$google_link = apply_filters( 'avf_conditional_setting_external_links', $google_link, __CLASS__, $this, $args, $instance );
			if( ! in_array( $google_link, array( '', 'confirm_link', 'page_only' ) ) )
			{
				$google_link = $original_google_link;
			}
			
			$title = apply_filters('widget_title', $title );

			echo $before_widget;
			
			if( ! empty( $title ) ) 
			{ 
				echo $before_title . $title . $after_title; 
			}
			

			$html_fallback_url = '';	
			if( ! empty( $google_fallback ) )
			{
				$html_fallback_url .= 'background-image:url(' . $google_fallback . ');';
			}
			
			$html_overlay = '';
			if( ( 'confirm_link' == $google_link ) || ( 'page_only' == $google_link ) )
			{
				$button_class = empty( $html_fallback_url ) ? ' av_text_confirm_link_visible' : '';
				
				$text_overlay = '';
				if( 'confirm_link' == $google_link )
				{
					$html_overlay = '<a href="#" class="av_gmaps_confirm_link av_text_confirm_link' . $button_class . '">';
					$text_overlay =	esc_html( $confirm_button );
				}
				else
				{
					if( empty( $street_address ) )
					{
						$adress1 = $lat;
						$adress2 = $lng;
					}
					else
					{
						$adress1 = $street_address . ' ' . $postcode . ' ' . $city . ' ' . $state . ' ' . $country;
						$adress2 = '';
					}
					 
					$url = av_google_maps::api_destination_url( $adress1, $adress2 );
					$html_overlay = '<a class="av_gmaps_page_only av_text_confirm_link' . $button_class . '" href="' . $url . '" target="_blank">';
					$text_overlay = esc_html( $page_link_text );
				}
				
				$html_overlay .= '<span>' . $text_overlay . '</span></a>';
				
				/**
				 * @since 4.4.2
				 * @param string		output string
				 * @param object		context
				 * @param array
				 * @param array
				*/
				$filter_args = array(
						   $html_overlay,
						   $this,
						   $args,
						   $instance
				   );
				$html_overlay = apply_filters_ref_array( 'avf_google_maps_confirm_overlay', $filter_args );
			}
			
			$map_id = '';
			if( 'page_only' != $google_link )
			{
				/**
				 * Add map data to js
				 */
				$content = htmlspecialchars( $content, ENT_QUOTES );
				$content = str_replace( '&lt;', '<', $content );
				$content = str_replace( '&gt;', '>', $content );
				$content = str_replace( '&quot;', '"', $content );
				$content = str_replace( '&#039;', '"', $content );
//				$content = json_encode( $content );
				$content = wpautop( $content );
				
				$data = array(
							'hue'					=> '',
							'zoom'					=> $zoom,
							'saturation'			=> '',
							'zoom_control'			=> true,
//							'pan_control'			=> true,				not needed in > 4.3.2
							'streetview_control'	=> false,
							'mobile_drag_control'	=> true,
							'maptype_control'		=> 'dropdown',
							'maptype_id'			=> $type
				);
				
				$data['marker'] = array();
				
				$data['marker'][0] = array(
							'address'			=> $postcode . '  ' . $street_address,
							'city'				=> $city,
							'country'			=> $country,
							'state'				=> $state,
							'long'				=> $lng,
							'lat'				=> $lat,
							'icon'				=> $icon,
							'imagesize'			=> 40,
							'content'			=> $content,
					);
			
				/**
				 * Does not work since 4.4
				 */
				if( ! empty( $directionsto ) )
				{
					$data['marker'][0]['directionsto'] = $directionsto;
				}
				
				$add = empty( $google_link ) ? 'unconditionally' : 'delayed';
				
				/**
				 * Allow to filter Google Maps data array
				 * 
				 * @since 4.4
				 * @param array $data
				 * @param string context
				 * @param object
				 * @param array additional args
				 * @return array
				 */
				$data = apply_filters( 'avf_google_maps_data', $data, __CLASS__, $this, array( $args, $instance ) );
				
				$map_id = Av_Google_Maps()->add_map( $data, $add );
			}
				
			switch( $google_link )
			{
				case 'confirm_link':
					$show_class = 'av_gmaps_show_delayed';
					break;
				case 'page_only':
					$show_class = 'av_gmaps_show_page_only';
					break;
				case '':
				default:
					$show_class = 'av_gmaps_show_unconditionally';
					break;
			}
			
			if( empty( $html_fallback_url ) )
			{
				$show_class .= ' av-no-fallback-img';
			}
			
			$style = '';		// $this->define_height($height)
			if( ! empty( $height ) )
			{
				$height = str_replace( ';', '', $height );
				$style .= " height: {$height};";
			}
			if( ! empty( $width ) )
			{
				$width = str_replace( ';', '', $width );
				$style .= " width: {$width};";
			}
			if( ! empty( $html_fallback_url ) )
			{
				$html_fallback_url = str_replace( ';', '', $html_fallback_url );
				$style .= " {$html_fallback_url};";
			}

			if( ! empty( $style ) )
			{
				$style = "style='{$style}'";
			}
				
			echo '<div class="av_gmaps_widget_main_wrap av_gmaps_main_wrap">';
			
			if( empty( $map_id ) )
			{
				echo	"<div class='avia-google-map-container avia-google-map-widget {$show_class}' {$style}>";
			}
			else
			{
				echo	"<div id='{$map_id}' class='avia-google-map-container avia-google-map-widget {$show_class}' data-mapid='{$map_id}' {$style}>";
			}
			
			echo			$html_overlay;
			echo		'</div>';
			
			
			echo '</div>';
			
			echo $after_widget;
		}

		/**
		 * Process widget options to be saved
		 * 
		 * @param array $new_instance
		 * @param array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) 
		{
			$instance = $this->parse_args_instance( $old_instance );
			
			$fields = $this->get_field_names();
			
			foreach( $new_instance as $key => $value ) 
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = strip_tags( $value );
				}
			}
			
			return $instance;
		}


		/**
		 * output the options form on admin
		 * 
		 * @param array $instance
		 */
		public function form( $instance ) 
		{
			$instance = $this->parse_args_instance( $instance );
			$fields = $this->get_field_names();
			
			foreach( $instance as $key => $value ) 
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = esc_attr( $value );
				}
			}
			
			extract( $instance );
			
			$street_address = $instance['street-address'];
			
			$html = new avia_htmlhelper();
			
			$marker_icon_element = array(
								'name'		=> __( 'Custom Marker Icon', 'avia_framework' ),
								'desc'		=> __( 'Upload a custom marker icon or enter the URL', 'avia_framework' ),
								'id'		=> $this->get_field_id( 'icon'),
								'id_name'	=> $this->get_field_name( 'icon' ),
								'std'		=> $icon,
								'type'		=> 'upload',
								'label'		=> __('Use image as custom marker icon', 'avia_framework')
							);
				
			$fallback_element = array(
								'name'		=> __( 'Fallback image to replace Google Maps', 'avia_framework' ),
								'desc'		=> __( 'Upload a fallback image or enter the URL to an image to replace Google Maps or until Google Maps is loaded', 'avia_framework' ),
								'id'		=> $this->get_field_id( 'google_fallback'),
								'id_name'	=> $this->get_field_name( 'google_fallback' ),
								'std'		=> $google_fallback,
								'type'		=> 'upload',
								'label'		=> __('Use image as Google Maps fallback image', 'avia_framework')
							);

			?>
			<div class="avia_widget_form avia_widget_conditional_form avia_google_maps_form <?php echo $google_link;?>">
				<p>
					<label for="<?php print $this->get_field_id('title'); ?>"><?php _e('Title:','avia_framework'); ?></label>
					<input class="widefat" id="<?php print $this->get_field_id('title'); ?>" name="<?php print $this->get_field_name('title'); ?>" type="text" value="<?php print $title; ?>" />
				</p>
				<p>
				<?php _e('Enter the latitude and longitude of the location you want to display. Need help finding the latitude and longitude?', 'avia_framework'); ?> <a href="#" class="avia-coordinates-help-link button"><?php _e('Click here to enter an address.','avia_framework'); ?></a>
                </p>
				<div class="avia-find-coordinates-wrapper">
                    <p>
                        <label for="<?php print $this->get_field_id('street-address'); ?>"><?php _e('Street Address:','avia_framework'); ?></label>
                        <input class='widefat avia-map-street-address' id="<?php print $this->get_field_id('street-address'); ?>" name="<?php print $this->get_field_name('street-address'); ?>" type="text" value="<?php print $street_address; ?>" />
                    </p>
                    <p>
                        <label for="<?php print $this->get_field_id('city'); ?>"><?php _e('City:','avia_framework'); ?></label>
                        <input class='widefat avia-map-city' id="<?php print $this->get_field_id('city'); ?>" name="<?php print $this->get_field_name('city'); ?>" type="text" value="<?php print $city; ?>" />
                    </p>
                    <p>
                        <label for="<?php print $this->get_field_id('state'); ?>"><?php _e('State:','avia_framework'); ?></label>
                        <input class='widefat avia-map-state' id="<?php print $this->get_field_id('state'); ?>" name="<?php print $this->get_field_name('state'); ?>" type="text" value="<?php print $state; ?>" />
                    </p>
                    <p>
                        <label for="<?php print $this->get_field_id('postcode'); ?>"><?php _e('Postcode:','avia_framework'); ?></label>
                        <input class='widefat avia-map-postcode' id="<?php print $this->get_field_id('postcode'); ?>" name="<?php print $this->get_field_name('postcode'); ?>" type="text" value="<?php print $postcode; ?>" />
                    </p>
                    <p>
                        <label for="<?php print $this->get_field_id('country'); ?>"><?php _e('Country:','avia_framework'); ?></label>
                        <input class='widefat avia-map-country' id="<?php print $this->get_field_id('country'); ?>" name="<?php print $this->get_field_name('country'); ?>" type="text" value="<?php print $country; ?>" />
                    </p>
                    <p>
                        <a class="button avia-populate-coordinates"><?php _e('Fetch coordinates!','avia_framework'); ?></a>
                        <div class='avia-loading-coordinates'><?php _e('Fetching the coordinates. Please wait...','avia_framework'); ?></div>
                    </p>
                </div>
                <div class="avia-coordinates-wrapper">
					<p>
						<label for="<?php print $this->get_field_id('lat'); ?>"><?php _e('Latitude:','avia_framework'); ?></label>
						<input class='widefat avia-map-lat' id="<?php print $this->get_field_id('lat'); ?>" name="<?php print $this->get_field_name('lat'); ?>" type="text" value="<?php print $lat; ?>" />
					</p>
					<p>
						<label for="<?php print $this->get_field_id('lng'); ?>"><?php _e('Longitude:','avia_framework'); ?></label>
						<input class='widefat avia-map-lng' id="<?php print $this->get_field_id('lng'); ?>" name="<?php print $this->get_field_name('lng'); ?>" type="text" value="<?php print $lng; ?>" />
					</p>
                </div>
        		<p>
				<label for="<?php print $this->get_field_id('zoom'); ?>"><?php echo __('Zoom Level:','avia_framework').' <small>'.__('(1-19)','avia_framework').'</small>'; ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id('zoom'); ?>" name="<?php echo $this->get_field_name('zoom'); ?>">
					<?php
					$answers = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19);
					foreach( $answers as $answer )
					{
						?><option value="<?php echo $answer;?>" <?php selected( $answer, $zoom ); ?>><?php echo $answer;?></option><?php
					}?>
				</select>
				</p>
				<p>
				<label for="<?php print $this->get_field_id('type'); ?>"><?php _e('Map Type:','avia_framework'); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
					<?php
					$answers = array('ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN');
					foreach( $answers as $answer )
					{
						?><option value="<?php echo $answer;?>" <?php selected( $answer, $type ); ?>><?php echo $answer;?></option><?php
					}?>
				</select>
				</p>
				<p style="display:none;">
					<label for="<?php print $this->get_field_id('directionsto'); ?>"><?php _e('Display a Route by entering a address here. (If address is added Zoom will be ignored)','avia_framework'); ?>:</label>
					<input class="widefat" id="<?php print $this->get_field_id('directionsto'); ?>" name="<?php print $this->get_field_name('directionsto'); ?>" type="text" value="<?php print $directionsto; ?>" />
				</p>
				<p>
					<label for="<?php print $this->get_field_id('content'); ?>"><?php _e('Info Bubble Content:','avia_framework'); ?></label>
					<textarea rows="7" class="widefat" id="<?php print $this->get_field_id('content'); ?>" name="<?php print $this->get_field_name('content'); ?>"><?php print $content; ?></textarea>
				</p>
               
				<div class="avia_gm_marker_icon_upload avia_google_marker_icon av-widgets-upload">
					<?php echo $html->render_single_element( $marker_icon_element );?>
				</div>
                <p>
					<label for="<?php print $this->get_field_id('width'); ?>"><?php _e('Enter the width in px or &percnt; (100&percnt; width will be used if you leave this field empty)','avia_framework'); ?>:</label>
					<input class="widefat" id="<?php print $this->get_field_id('width'); ?>" name="<?php print $this->get_field_name('width'); ?>" type="text" value="<?php print $width; ?>" />
                </p>
                <p>
					<label for="<?php print $this->get_field_id('height'); ?>"><?php _e('Enter the height in px or &percnt;','avia_framework'); ?>:</label>
					<input class="widefat" id="<?php print $this->get_field_id('height'); ?>" name="<?php print $this->get_field_name('height'); ?>" type="text" value="<?php print $height; ?>" />
                </p>
				<p>
					<label for="<?php echo $this->get_field_id( 'google_link' ); ?>"><?php _e( 'Link to Google Maps', 'avia_framework' ); ?>:</label>
					<select id="<?php echo $this->get_field_id( 'google_link' ); ?>" name="<?php echo $this->get_field_name( 'google_link' ); ?>" class="widefat avia-coditional-widget-select">
						<option value="" <?php selected( '', $google_link ) ?>><?php _e( 'Show Google Maps immediatly', 'avia_framework' ); ?></option>
						<option value="confirm_link" <?php selected( 'confirm_link', $google_link ) ?>><?php _e( 'User must accept to show Google Maps', 'avia_framework' ); ?></option>
						<option value="page_only" <?php selected( 'page_only', $google_link ) ?>><?php _e( 'Only open Google Maps in new window', 'avia_framework' ); ?></option>
					</select>
				</p>
				
				<p class="av-confirm_link">
					<label for="<?php echo $this->get_field_id('confirm_button'); ?>"><?php _e('Button text confirm to load Google Maps:', 'avia_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('confirm_button'); ?>" name="<?php echo $this->get_field_name('confirm_button'); ?>" type="text" value="<?php echo $confirm_button; ?>" /></label>
				</p>
			
				<p class="av-page_only">
					<label for="<?php echo $this->get_field_id('page_link_text'); ?>"><?php _e('Direct link to Google Maps page:', 'avia_framework'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('page_link_text'); ?>" name="<?php echo $this->get_field_name('page_link_text'); ?>" type="text" value="<?php echo $page_link_text; ?>" /></label>
				</p>

				<div class="avia_gm_fallback_upload avia_google_fallback av-widgets-upload">
					<?php echo $html->render_single_element( $fallback_element );?>
				</div>
				
			</div>
			<?php
		}

		/**
		 * Output scripts in backend
		 */
        public function handler_print_google_maps_scripts()
        {
			return;
			
			$api_key = avia_get_option( 'gmap_api' );
			$api_url = av_google_maps::api_url( $api_key );
            
            wp_register_script( 'avia-google-maps-api', $api_url, array('jquery'), NULL, true );
            
            $load_google_map_api = apply_filters( 'avf_load_google_map_api', true, 'avia_google_map_widget' );
            
            if( $load_google_map_api ) 
			{
				wp_enqueue_script( 'avia-google-maps-api' );
			}

            $is_widget_edit_page = in_array( basename( $_SERVER['PHP_SELF'] ), array( 'widgets.php' ) );
            if( $is_widget_edit_page )
            {
	            wp_register_script( 'avia-google-maps-widget', AVIA_JS_URL . 'conditional_load/avia_google_maps_widget_admin.js', array( 'jquery','media-upload','media-views' ), '1.0.0', true);
	            wp_enqueue_script( 'avia-google-maps-widget' );

	            $args = array(
	                'toomanyrequests'	=> __( "Too many requests at once, please refresh the page to complete geocoding", 'avia_framework' ),
	                'latitude'			=> __( "Latitude and longitude for", 'avia_framework' ),
	                'notfound'			=> __( "couldn't be found by Google, please add them manually", 'avia_framework' ),
	                'insertaddress' 	=> __( "Please insert a valid address in the fields above", 'avia_framework' )
	            );

	            if( $load_google_map_api ) 
				{
					wp_localize_script( 'avia-google-maps-api', 'AviaMapTranslation', $args );
				}
            }
        }

		/**
		 * Returns the js script
		 * 
		 * @param string $lat
		 * @param string $lng
		 * @param string $zoom
		 * @param string $type
		 * @param string $content
		 * @param string $directionsto
		 * @param string $width
		 * @param string $height
		 * @param string $icon
		 * @return string
		 * 
		 * @deprecated since version 4.4	no longer needed
		 */
		protected function print_map( $lat, $lng, $zoom, $type, $content, $directionsto, $width, $height, $icon ) 
		{
			global $avia_config;
			
			_deprecated_function( 'print_map', '4.4', 'see class av_google_maps' );
			
			$output = "";
			$unique = uniqid();

			$prefix = isset($_SERVER['HTTPS'] ) ? "https" : "http";
			$width = ! empty( $width ) ? 'width:'.$width.';' : 'width:100%;';
			$height = ! empty( $height ) ? 'height:'.$height.';' : '';

			$content = htmlspecialchars( $content, ENT_QUOTES );
			$content = str_replace( '&lt;', '<', $content );
			$content = str_replace( '&gt;', '>', $content );
			$content = str_replace( '&quot;', '"', $content );
			$content = str_replace( '&#039;', '"', $content );
			$content = json_encode( $content );


			$directionsForm = "";
			if( empty( $avia_config['g_maps_widget_active'] ) )
			{
				$avia_config['g_maps_widget_active'] = 0;
			}

			if( apply_filters( 'avia_google_maps_widget_load_api', true, $avia_config[ 'g_maps_widget_active'] ) )
			{	
				$api_key = avia_get_option('gmap_api');
				$api_url = av_google_maps::api_url( $api_key );

				wp_register_script( 'avia-google-maps-api', $api_url, array( 'jquery' ), NULL, true );
				wp_enqueue_script( 'avia-google-maps-api' );
			}

			$avia_config['g_maps_widget_active'] ++;

			$output .= "<script type='text/javascript'>
				function makeMap_" . $avia_config['g_maps_widget_active'] . "() {\n";

			$avia_maps_config = "
				var directionsDisplay;
				directionsDisplay = new google.maps.DirectionsRenderer;
				var directionsService = new google.maps.DirectionsService;
				var map;
				var latlng = new google.maps.LatLng(" . $lat . ", " . $lng . ");
				var directionsto = '" . $directionsto . "';
				var myOptions = {
				  zoom:" . $zoom . ",
				  mapTypeControl:true,
				  mapTypeId:google.maps.MapTypeId." . $type . ",
				  mapTypeControlOptions:{style:google.maps.MapTypeControlStyle.DROPDOWN_MENU},
				  navigationControl:true,
				  navigationControlOptions:{style:google.maps.NavigationControlStyle.SMALL},
				  center:latlng
				};
				var map = new google.maps.Map(document.getElementById('avia_google_maps_$unique'), myOptions);

				if(directionsto.length > 5)
				{
				  directionsDisplay.setMap(map);
				  var request = {
					 origin:directionsto,
					 destination:latlng,
					 travelMode:google.maps.DirectionsTravelMode.DRIVING
				};
				  directionsService.route(request, function(response, status) {
					 if(status == google.maps.DirectionsStatus.OK) {
						directionsDisplay.setDirections(response)
					 }
				  })
				}
				else
				{
				  var contentString = " . $content . ";
				  var infowindow = new google.maps.InfoWindow({
					 content: contentString
				  });
				  var marker = new google.maps.Marker({
					 position: latlng,
					 map: map,
					 icon: '" . $icon . "',
					 title: ''
				  });

				  google.maps.event.addListener(marker, 'click', function() {
					  infowindow.open(map,marker);
				  });
				}";

			$output .= apply_filters( 'avia_google_maps_widget_config', $avia_maps_config, $lat, $lng, $directionsto, $zoom, $type, $unique, $content, $icon );

			$output .= "\n}\n\n
					jQuery(document).ready(function() {
						makeMap_" . $avia_config['g_maps_widget_active'] . "()
					});
				</script>
				<div id='avia_google_maps_$unique' style='$height $width' class='avia_google_map_container'></div>";

		   return $output;
		}


	} // SGMwidget widget
}


if( ! class_exists('avia_instagram_widget') )
{
	/**
	 * Extended and improved version.
	 * Adds a background caching of images on own server to avoid to access instagram to display the images
	 * 
	 * @since 4.3.1
	 * @by Günter
	 */
	class avia_instagram_widget extends Avia_Widget 
	{

		/**
		 *
		 * @since 4.3.1
		 * @var array 
		 */
		protected $upload_folders;

		/**
		 * Stores the expire time for cached images in seconds.
		 * Do not make intervall too short to avoid unnecessary requests.
		 * Also make it large enough to allow a complete update of all instances in that period.
		 * 
		 * @since 4.3.1
		 * @var int 
		 */
		protected $expire_time;

		/**
		 *
		 * @since 4.3.1
		 * @var boolean 
		 */
		protected $activate_cron;


		/**
		 * Holds all caching info for each widget instance.
		 * 
		 * @since 4.3.1
		 * @var array 
		 */
		protected $cache;


		/**
		 *
		 * @since 4.3.1
		 * @var array 
		 */
		protected $cached_file_sizes;


		/**
		 * 
		 */
		public function __construct() 
		{
			parent::__construct(
								'avia-instagram-feed',
								THEMENAME ." ". __( 'Instagram', 'avia_framework' ),
								array( 'classname' => 'avia-instagram-feed', 'description' => __( 'Displays your latest Instagram photos', 'avia_framework' ) )
							);

			$this->upload_folders = wp_upload_dir();

			if( is_ssl() )
			{
				$this->upload_folders['baseurl'] = str_replace( 'http://', 'https://', $this->upload_folders['baseurl'] );
			}

			$folder = apply_filters( 'avf_instagram_cache_folder_name', 'avia_instagram_cache' );

			$this->upload_folders['instagram_dir'] = trailingslashit( trailingslashit( $this->upload_folders['basedir'] ) . $folder );
			$this->upload_folders['instagram_url'] = trailingslashit( trailingslashit( $this->upload_folders['baseurl'] ) . $folder );

			$this->expire_time = HOUR_IN_SECONDS * 2;

			$this->expire_time = apply_filters_deprecated( 'null_instagram_cache_time', array( $this->expire_time ), '4.3.1', 'avf_instagram_file_cache_time', __( 'Adding possible file caching on server might need a longer period of time to invalidate cache.', 'avia_framework' ) );
			$this->expire_time = apply_filters( 'avf_instagram_file_cache_time', $this->expire_time );

			$this->activate_cron =  ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON );
			$this->activate_cron = apply_filters( 'avf_instagram_activate_cron', $this->activate_cron );

			$this->cache = $this->get_cache();

			$this->cached_file_sizes = array( 'thumbnail', 'small', 'large', 'original' );

			/**
			 * WP Cron job events
			 */
			if( $this->activate_cron )
			{
				add_action( 'av_instagram_scheduled_filecheck', array( $this, 'handler_scheduled_filecheck' ), 10 );
			}

			/**
			 * Makes sure to keep cron job alive as fallback
			 */
			if( is_admin() )
			{
				add_action( 'admin_init', array( $this, 'handler_init_filecheck' ), 99999 );
				add_action( 'delete_widget', array( $this, 'handler_delete_widget' ), 10, 3 );		
			}
			else 
			{
				add_action( 'init', array( $this, 'handler_init_filecheck' ), 99999 );
			}

		}

		/**
		 * 
		 * @since 4.3.1
		 */
		public function __destruct() 
		{
			parent::__destruct();
			
			unset( $this->upload_folders );
			unset( $this->cache );
			unset( $this->cached_file_sizes );
		}


		/**
		 * Returns the cache info array
		 * 
		 * @since 4.3.1
		 * @return array
		 */
		public function get_cache()
		{
			if( is_null( $this->cache ) )
			{
				$cache = get_option( 'avia_instagram_widgets_cache', '' );
				
				/**
				 * backwards comp only
				 */
				if( is_array( $cache ) )
				{
					$this->cache = $cache;
				}
				else if( ! is_string( $cache ) || empty( $cache ) )
				{
					$this->cache = null;
				}
				else
				{
					$cache = json_decode( $cache, true );
					$this->cache = is_array( $cache ) ? $cache : null;
				}

				if( empty( $this->cache ) )
				{
					$this->cache = array(
							'last_updated'		=> 0,			//	time() when last complete check has run
							'instances'			=> array()
						);
				}
			}

			return $this->cache;
		}


		/**
		 * Update the cache array in DB
		 * 
		 * @since 4.3.1
		 * @param array|null $cache
		 */
		public function update_cache( array $cache = null )
		{
			if( ! is_null( $cache) )
			{
				$this->cache = $cache;
			}
			
			$save = json_encode( $this->cache );
			update_option( 'avia_instagram_widgets_cache', $save );
		}


		/**
		 * Ensure a valid instance array filled with defaults
		 * 
		 * @since 4.3.1
		 * @param array $instance
		 * @return array
		 */
		protected function parse_args_instance( array $instance )
		{
			$instance = wp_parse_args( $instance, array( 
									'title'			=> __( 'Instagram', 'avia_framework' ), 
									'username'		=> '', 
									'cache'			=> apply_filters( 'avf_instagram_default_cache_location', '' ),		//	'' | 'server'
									'number'		=> 9,
									'columns'		=> 3,
									'size'			=> 'thumbnail', 
									'target'		=> 'lightbox' ,
									'link'			=> __( 'Follow Me!', 'avia_framework' ),
									'avia_key'		=> ''
								) 
							);

			return $instance;
		}

		/**
		 * Ensure a valid instance array filled with defaults
		 * 
		 * @since 4.3.1
		 * @param array $instance_cache
		 * @return array
		 */
		protected function parse_args_instance_cache( array $instance_cache )
		{
			$instance_cache = wp_parse_args( (array) $instance_cache, array( 
											'upload_folder'		=> '',				//	not the complete path, only the last folder name
											'path_error'		=> '',				//	Error message if upload_folder could not be created
											'instagram_error'	=> '',				
											'upload_errors'		=> false,			//	number of errors found when caching files to show
											'last_update'		=> 0,				//	time() of last update
											'cached_list'		=> array(),			//	in the order how to display the files and file info on server
											'instagram_list'	=> array()			//	returned info from instagramm
										));

			return $instance_cache;
		}


		/**
		 * Creates a unique key for the given instance for our cache array
		 * 
		 * @since 4.3.1
		 * @param array $instance
		 * @param string $id_widget
		 * @return string
		 */
		protected function create_avia_key( array $instance, $id_widget )
		{
			$k = 0;
			$key = str_replace( $this->id_base . '-', '', $id_widget ) . '-' . AviaHelper::save_string( $instance['title'], '-' );

			$orig_key = $key;
			while( array_key_exists( $key, $this->cache['instances'] ) )
			{
				$key = $orig_key . "-{$k}";
				$k++;
			}

			return $key;
		}


		/**
		 * Output the widget in frontend
		 * 
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) 
		{
			$instance = $this->parse_args_instance( $instance );

			$fields = $this->get_field_names();

				foreach( $instance as $key => $value ) 
				{
					if( in_array( $key, $fields ) )
					{
						$instance[ $key ] = esc_attr( $value );
					}
				}

			extract( $args, EXTR_SKIP );
			extract( $instance, EXTR_SKIP );
			
			/**
			 * Allow to change the conditional display setting - e.g. if user is opt in and allows to connect directly
			 * 
			 * @since 4.4
			 * @param string $google_link			'' | 'server'
			 * @param string $context
			 * @param mixed $object
			 * @param array $args
			 * @param array $instance
			 * @return string
			 */
			$original_cache = $cache;
			$cache = apply_filters( 'avf_conditional_setting_external_links', $cache, __CLASS__, $this, $args, $instance );
			if( ! in_array( $cache, array( '', 'server' ) ) )
			{
			   $cache = $original_cache;
			}

			$title = apply_filters( 'widget_title', $title, $args );

			/**
			 * Skip for non logged in users in frontend
			 */
			if( ( trim( $username ) == '' ) && ! is_user_logged_in() && ! current_user_can( 'edit_posts' ) )
			{
				return;
			}

			echo $before_widget;

			if ( ! empty( $title ) ) 
			{ 
				echo $before_title . $title . $after_title; 
			}

			do_action( 'aviw_before_widget', $instance );

			if( $username != '' ) 
			{
				$errors = array();
				$media_array = array();

				$instance_cache = isset( $this->cache['instances'][ $instance['avia_key'] ] ) ? $this->cache['instances'][ $instance['avia_key'] ] : null;

				if( ! is_null( $instance_cache ) )
				{
					if( ! empty( $instance_cache['instagram_error'] ) )
					{
						$errors =  array( $instance_cache['instagram_error'] );
					}
					if( ! empty( $instance_cache['upload_errors'] ) && ( 'server' == $instance['cache'] ) )
					{
						foreach( $instance_cache['cached_list'] as $img ) 
						{
							if( ! empty( $img['errors'] ) )
							{
								$errors = array_merge( $errors, $img['errors'] );
							}
						}
					}

					if( 'server' == $instance['cache'] )
					{
						$media_array = $instance_cache['cached_list'];

						$url = trailingslashit( trailingslashit( $this->upload_folders['instagram_url'] ) . $instance_cache['upload_folder'] );

						foreach( $media_array as $key => $media ) 
						{
							if( ! empty( $media['errors'] ) )
							{
								$errors = array_merge( $errors, $media['errors'] );
							}

							if( ! empty( $media[ $size ] ) )
							{
								$media_array[ $key ][ $size ] = $url . $media[ $size ];
							}
							if( ! empty( $media[ 'original' ] ) )
							{
								$media_array[ $key ]['original'] = $url . $media['original'];
							}
						}
					}
					else
					{
						$media_array = $instance_cache['instagram_list'];
					}
				}

				/**
				 * Only show error messages to admins and authors
				 */
				if( ! empty( $errors ) && is_user_logged_in() && current_user_can( 'edit_posts' ) ) 
				{
					$errors = array_map( 'esc_html__', $errors );

					$out = '';
					$out .= '<div class="av-instagram-errors">';

					$out .=		'<p class="av-instagram-errors-msg av-instagram-admin">' . esc_html__( 'Only visible for admins:', 'avia_framework' ) . '</p>';

					$out .=		'<p class="av-instagram-errors-msg av-instagram-admin">';
					$out .=			implode( '<br />', $errors );
					$out .=		'</p>';

					$out .= '</div>';

					echo $out;
				} 

				if( count( $media_array ) > 0 )
				{
					// filters for custom classes
					$ulclass 	= esc_attr( apply_filters( 'aviw_list_class', 'av-instagram-pics av-instagram-size-' . $size ) );
					$rowclass 	= esc_attr( apply_filters( 'aviw_row_class', 'av-instagram-row' ) );
					$liclass 	= esc_attr( apply_filters( 'aviw_item_class', 'av-instagram-item' ) );
					$aclass 	= esc_attr( apply_filters( 'aviw_a_class', '' ) );
					$imgclass 	= esc_attr( apply_filters( 'aviw_img_class', '' ) );

					?><div class="<?php echo esc_attr( $ulclass ); ?>"><?php

					$last_id  = end( $media_array );
					$last_id  = $last_id['id'];

					$rowcount = 0;	
					$itemcount = 0;
					foreach ( $media_array as $item ) 
					{
						if( empty( $item[ $size ] ) )
						{
							continue;
						}

						if( $rowcount == 0 )
						{
							echo "<div class='{$rowclass}'>";
						}

						$rowcount ++;
						$itemcount ++;

						$targeting = $target;
						if( $target == "lightbox" )
						{
							$targeting = "";
							$item['link'] = ! empty( $item['original'] ) ? $item['original'] : $item[ $size ];
						}

						echo '<div class="' . $liclass . '">';
						echo '<a href="' . esc_url( $item['link'] ) . '" target="' . esc_attr( $targeting ) . '"  class="' . $aclass . ' ' . $imgclass . '" title="' . esc_attr( $item['description'] ) . '" style="background-image:url(' . esc_url( $item[ $size ] ) . ');">';
						echo '</a></div>';

						if( $rowcount % $columns == 0 || $last_id == $item['id'] || ( $itemcount >= $number ) )
						{
							echo '</div>';
							$rowcount = 0;

							if( $itemcount >= $number )
							{
								break;
							}
						}
					}
					echo '</div>';
				}
				else
				{
					echo '<p class="av-instagram-errors-msg">' . esc_html__( 'No images available at the moment', 'avia_framework' ) . '</p>';
				}
			}
			else
			{
				echo '<p class="av-instagram-errors-msg av-instagram-admin">' . esc_html__( 'For admins only: Missing intagram user name !!', 'avia_framework' ) . '</p>';
			}

			if ( $link != '' ) 
			{
				?>
				<a class="av-instagram-follow avia-button" href="//instagram.com/<?php echo esc_attr( trim( $username ) ); ?>" rel="me" target="<?php echo esc_attr( $target ); ?>"><?php echo $link; ?></a><?php
			}

			do_action( 'aviw_after_widget', $instance );

			echo $after_widget;
		}


		/**
		 * Output the form in backend
		 * 
		 * @param array $instance
		 */
		public function form( $instance ) 
		{
			$instance = $this->parse_args_instance( $instance );
			$fields = $this->get_field_names();
			
			foreach( $instance as $key => $value ) 
			{
				if( in_array( $key, $fields ) )
				{
					switch( $key )
					{
						case 'number':
						case 'columns':
							$instance[ $key ] = absint( $value );
							break;
						default:
							$instance[ $key ] = esc_attr( $value );
							break;
					}
				}
			}
			
			extract( $instance );

			?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'avia_framework' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e( 'Username', 'avia_framework' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo $username; ?>" /></label></p>			
			<p><label for="<?php echo $this->get_field_id( 'cache' ); ?>"><?php _e( 'Location of your photos or videos', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'cache' ); ?>" name="<?php echo $this->get_field_name( 'cache' ); ?>" class="widefat">
					<option value="" <?php selected( '', $cache ) ?>><?php _e( 'Get from your instagram account (instagram server connection needed)', 'avia_framework' ); ?></option>
					<option value="server" <?php selected( 'server', $cache ) ?>><?php _e( 'Cache on your server - no instagram connection needed on pageload', 'avia_framework' ); ?></option>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of photos', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" class="widefat">
					<option value="1" <?php selected( 1, $number ) ?>>1</option>
					<option value="2" <?php selected( 2, $number ) ?>>2</option>
					<option value="3" <?php selected( 3, $number ) ?>>3</option>
					<option value="4" <?php selected( 4, $number ) ?>>4</option>
					<option value="5" <?php selected( 5, $number ) ?>>5</option>
					<option value="6" <?php selected( 6, $number ) ?>>6</option>
					<option value="7" <?php selected( 7, $number ) ?>>7</option>
					<option value="8" <?php selected( 8, $number ) ?>>8</option>
					<option value="9" <?php selected( 9, $number ) ?>>9</option>
					<option value="10" <?php selected( 10, $number ) ?>>10</option>
					<option value="11" <?php selected( 11, $number ) ?>>11</option>
					<option value="12" <?php selected( 12, $number ) ?>>12</option>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php _e( 'Number of columns', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'columns' ); ?>" name="<?php echo $this->get_field_name( 'columns' ); ?>" class="widefat">
					<option value="1" <?php selected( 1, $columns ) ?>>1</option>
					<option value="2" <?php selected( 2, $columns ) ?>>2</option>
					<option value="3" <?php selected( 3, $columns ) ?>>3</option>
					<option value="4" <?php selected( 4, $columns ) ?>>4</option>
					<option value="5" <?php selected( 5, $columns ) ?>>5</option>
					<option value="6" <?php selected( 6, $columns ) ?>>6</option>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'Thumbnail size', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" class="widefat">
					<option value="thumbnail" <?php selected( 'thumbnail', $size ) ?>><?php _e( 'Thumbnail', 'avia_framework' ); ?></option>
					<option value="small" <?php selected( 'small', $size ) ?>><?php _e( 'Small', 'avia_framework' ); ?></option>
					<option value="large" <?php selected( 'large', $size ) ?>><?php _e( 'Large', 'avia_framework' ); ?></option>
					<option value="original" <?php selected( 'original', $size ) ?>><?php _e( 'Original', 'avia_framework' ); ?></option>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'target' ); ?>"><?php _e( 'Open links in', 'avia_framework' ); ?>:</label>
				<select id="<?php echo $this->get_field_id( 'target' ); ?>" name="<?php echo $this->get_field_name( 'target' ); ?>" class="widefat">
					<option value="lightbox" <?php selected( 'lightbox', $target ) ?>><?php _e( 'Lightbox', 'avia_framework' ); ?></option>
					<option value="_self" <?php selected( '_self', $target ) ?>><?php _e( 'Current window (_self)', 'avia_framework' ); ?></option>
					<option value="_blank" <?php selected( '_blank', $target ) ?>><?php _e( 'New window (_blank)', 'avia_framework' ); ?></option>
				</select>
			</p>
			<p><label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php _e( 'Link text', 'avia_framework' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id( 'link' ); ?>" name="<?php echo $this->get_field_name( 'link' ); ?>" type="text" value="<?php echo $link; ?>" /></label></p>

			<?php

			if( ! $this->activate_cron )
			{
				echo '<p class="av-instagram-no-cron">';
				echo	__( 'WP Cron jobs are disabled. To assure a regular update of cached data and an optimal pageload in frontend and backend we recommend to activate this.', 'avia_framework' );
				echo '</p>';

				$timestamp = ( $this->cache['last_updated'] != 0 ) ? $this->cache['last_updated'] + $this->expire_time : false;
				$time = ( false !== $timestamp ) ? date( 'Y/m/d H:i a', $timestamp ) .  __( ' UTC', 'avia_framework' ) : __( 'No time available', 'avia_framework' );

				echo '<p class="av-instagram-next-update">';
				echo	__( 'The widget preloads and caches Instagram data for better performance.', 'avia_framework' )." ";
				echo	sprintf( __( 'Next update: %s', 'avia_framework' ), $time );
				echo '</p>';
			}
			else
			{
				$timestamp = wp_next_scheduled( 'av_instagram_scheduled_filecheck' );
				$time = ( false !== $timestamp ) ? date( "Y/m/d H:i", $timestamp ) .  __( ' UTC', 'avia_framework' ) : __( 'No time available', 'avia_framework' );

				echo '<p class="av-instagram-next-update">';
				echo	__( 'The widget preloads and caches Instagram data for better performance.', 'avia_framework' )." ";
				echo	sprintf( __( 'Next update: %s', 'avia_framework' ), $time );
				echo '</p>';
			}

			if( empty( $instance['avia_key'] ) )
			{
				return;
			}

			if( empty( $this->cache['instances'][ $instance['avia_key'] ] ) )
			{
				return;
			}

			$instance_cache = $this->cache['instances'][ $instance['avia_key'] ];
			$errors = array();

			if( ! empty( $instance_cache['instagram_error'] ) )
			{
				$errors = (array) $instance_cache['instagram_error'];
			}

			if( 'server' == $instance['cache'] )
			{
				foreach( $instance_cache['cached_list'] as $image ) 
				{
					if( ! empty( $image['errors'] ) )
					{
						$errors = array_merge( $errors, $image['errors'] );
					}
				}
			}

			if( ! empty( $errors ) )
			{
				$errors = array_map( 'esc_html__', $errors );

				$out  = '<div class="av-instagram-errors">';

				$out .=		'<p class="av-instagram-errors-msg av-instagram-error-headline">' . esc_html__( 'Errors found:', 'avia_framework' ) . '</p>';

				$out .=		'<p class="av-instagram-errors-msg">';
				$out .=			implode( '<br />', $errors );
				$out .=		'</p>';

				$out .= '</div>';

				echo $out;
			}

		}

		/**
		 * Update the form data
		 * 
		 * @param array $new_instance
		 * @param array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) 
		{
			$instance = $this->parse_args_instance( $old_instance );
			
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['username'] = trim( strip_tags( $new_instance['username'] ) );
			$instance['cache'] = ( $new_instance['cache'] == 'server' || $new_instance['cache'] == '' ) ? $new_instance['cache'] : apply_filters( 'avf_instagram_default_cache_location', 'server' );
			$instance['number'] = ! absint( $new_instance['number'] ) ? 9 : $new_instance['number'];
			$instance['columns'] = ! absint( $new_instance['columns'] ) ? 3 : $new_instance['columns'];
			$instance['size'] = ( $new_instance['size'] == 'thumbnail' || $new_instance['size'] == 'large' || $new_instance['size'] == 'small' || $new_instance['size'] == 'original' ) ? $new_instance['size'] : 'large';
			$instance['target'] = ( $new_instance['target'] == '_self' || $new_instance['target'] == '_blank'|| $new_instance['target'] == 'lightbox' ) ? $new_instance['target'] : '_self';
			$instance['link'] = strip_tags( $new_instance['link'] );


			/**
			 * We have a new widget (or an existing from an older theme version)
			 */
			if( empty( $instance['avia_key'] ) )
			{
				$key = $this->create_avia_key( $instance, $this->id );
				$instance['avia_key'] = $key;
				$this->cache['instances'][ $key ] = array();
				$this->update_cache();
			}

			$this->update_single_instance( $instance, $this->id );

			if( $this->activate_cron )
			{
				$this->restart_cron_job();
			}

			return $instance;
		}


		/**
		 * Get info from instagram
		 * based on https://gist.github.com/cosmocatalano/4544576
		 * 
		 * @param string $username
		 * 
		 * @return array|\WP_Error
		 */
		protected function scrape_instagram( $username ) 
		{
			$username = strtolower( $username );
			$username = str_replace( '@', '', $username );

			$remote = wp_remote_get( 'https://www.instagram.com/' . trim( $username ), array( 'sslverify' => false, 'timeout' => 60 ) );

			if ( is_wp_error( $remote ) )
			{
				return new WP_Error( 'site_down', __( 'Unable to communicate with Instagram.', 'avia_framework' ) );
			}

			if ( 200 != wp_remote_retrieve_response_code( $remote ) )
			{
				return new WP_Error( 'invalid_response', __( 'Instagram did not return a 200.', 'avia_framework' ) );
			}

			$shards = explode( 'window._sharedData = ', $remote['body'] );
			$insta_json = explode( ';</script>', $shards[1] );
			$insta_array = json_decode( $insta_json[0], true );

			if ( ! $insta_array )
			{
				return new WP_Error( 'bad_json', __( 'Instagram has returned invalid data.', 'avia_framework' ) );
			}

			if ( isset( $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] ) ) 
			{
				$images = $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
			} 
			else 
			{
				return new WP_Error( 'bad_json_2', __( 'Instagram has returned invalid data.', 'avia_framework' ) );
			}

			if ( ! is_array( $images ) )
			{
				return new WP_Error( 'bad_array', __( 'Instagram has returned invalid data.', 'avia_framework' ) );
			}

			$instagram = array();

			foreach ( $images as $image ) 
			{
				// see https://github.com/stevenschobert/instafeed.js/issues/549
				if ( $image['node']['is_video'] == true ) 
				{
					$type = 'video';
				} 
				else 
				{
					$type = 'image';
				}

				$caption = __( 'Instagram Image', 'avia_framework' );

				if ( ! empty( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'] ) ) 
				{
					$caption = wp_kses( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'], array() );
				}

				$instagram[] = array(
						'description'   => $caption,
						'link'		  	=> trailingslashit( '//instagram.com/p/' . $image['node']['shortcode'] ),
						'time'		  	=> $image['node']['taken_at_timestamp'],
						'comments'	  	=> $image['node']['edge_media_to_comment']['count'],
						'likes'		 	=> $image['node']['edge_liked_by']['count'],
						'thumbnail'	 	=> preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][0]['src'] ),
						'small'			=> preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][2]['src'] ),
						'large'			=> preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][4]['src'] ),
						'original'		=> preg_replace( '/^https?\:/i', '', $image['node']['display_url'] ),
						'type'		  	=> $type,
						'id'			=> $image['node']['id']
					);
			}

			$aviw_images_only = false;
			$aviw_images_only = apply_filters_deprecated( 'aviw_images_only', array( $aviw_images_only ), '4.3.1', 'avf_instagram_filter_files', __( 'Filter extended to filter images or videos', 'avia_framework' ) );

			/**
			 * Filter which type of elements will be displayed.
			 * Return an empty array to show all files.
			 * 
			 * Possible values:   'video' | 'image'
			 * 
			 * @since 4.3.1
			 * @return array 
			 */
			$show = $aviw_images_only ? array( 'image' ) : array();
			$show = apply_filters( 'avf_instagram_filter_files', $show, $username );

			if( ! empty( $show ) )
			{
				foreach( $instagram as $key => $media_item ) 
				{
					if( ! in_array( $media_item['type'], $show ) )
					{
						unset( $instagram[ $key ] );
					}
				}

				$instagram = array_merge( $instagram );
			}

			if ( empty( $instagram ) ) 
			{
				return new WP_Error( 'no_images', __( 'Instagram did not return any images.', 'avia_framework' ) );
			}

			return $instagram;
		}


		/**
		 * WP Cron handler for background uploads
		 * 
		 * @since 4.3.1
		 */
		public function handler_scheduled_filecheck()
		{
			if( defined( 'WP_DEBUG ') && WP_DEBUG )
			{
				error_log( '******************  In avia_instagram_widget::handler_scheduled_filecheck started' );
			}

			/**
			 * Create a scheduled event to prevent double checks running on parallel pageloads
			 */
			$this->schedule_cron_job( $this->expire_time * 2 );

			$settings = $this->get_settings();
			if( ! empty( $settings ) )
			{
				$this->check_all_instances();
			}

			$this->schedule_cron_job( $this->expire_time * 2 );

			$this->sync_data();

			$this->schedule_cron_job( $this->expire_time );

			if( defined( 'WP_DEBUG ') && WP_DEBUG )
			{
				error_log( '******************  In avia_instagram_widget::handler_scheduled_filecheck ended' );
			}
		}


		/**
		 * Synchronises directory and cache data structure.
		 * It might happen, that the update cronjob is running and user removes the last widget.
		 * This leads to an inconsistent cache and directory structure.
		 * 
		 * As user might have added new widgets again we have to sync cache with latest settings
		 * 
		 * @since 4.3.1
		 */
		public function sync_data()
		{
			if( defined( 'WP_DEBUG ') && WP_DEBUG )
			{
				error_log( '******************  In avia_instagram_widget::sync_data started' );
			}

			$settings = $this->get_settings();

			if( empty( $settings ) && empty( $this->cache['instances'] ) )
			{
				if( is_dir( $this->upload_folders['instagram_dir'] ) )
				{
					avia_backend_delete_folder( $this->upload_folders['instagram_dir'] );
					$this->cache['last_updated'] = time();
					$this->update_cache();
				}
				return;
			}

			$instance_infos = (array) $this->cache['instances'];

			/**
			 * Remove all entries from cache that have no more entry in settings
			 */
			$keys = array_keys( $instance_infos );
			$keys_to_keep = array();

			foreach ( $settings as $index => $setting )
			{
				if( in_array( $setting['avia_key'], $keys ) )
				{
					$keys_to_keep[] = $setting['avia_key'];
				}
			}

			$keys_to_remove = array_diff( $keys, $keys_to_keep );

			foreach( $keys_to_remove as $key ) 
			{
				$folder = $this->upload_folders['instagram_dir'] . $instance_infos[ $key ]['upload_folder'];
				avia_backend_delete_folder( $folder );
				unset( $this->cache['instances'][ $key ] );
			}

			/**
			 * Now we check that all directories belong to a cache entry
			 */
			$cache_dirs = is_dir( $this->upload_folders['instagram_dir'] ) ? scandir( $this->upload_folders['instagram_dir'] ) : false;
			if( ! is_array( $cache_dirs ) )
			{
				/**
				 * Something went wrong reading directory - folder does not exist, access denied, .....
				 * There is nothing we can do.
				 */
				return;
			}
			
			$cache_dirs = array_diff( $cache_dirs, array( '.', '..' ) );

			$ref_dirs = array();
			foreach( $this->cache['instances'] as $key => $instance_info ) 
			{
				$ref_dirs[] = $instance_info['upload_folder'];
			}

			$remove_dirs = array_diff( $cache_dirs, $ref_dirs );

			foreach( $remove_dirs as $remove_dir ) 
			{
				avia_backend_delete_folder( $this->upload_folders['instagram_dir'] . $remove_dir );
			}


			if( empty( $this->cache['instances'] ) )
			{
				avia_backend_delete_folder( $this->upload_folders['instagram_dir'] );
			}

			$this->cache['last_updated'] = time();
			$this->update_cache();
		}

		/**
		 * WP Cron is disabled - we have to load files during pageload in admin area
		 * 
		 * @since 4.3.1
		 */
		public function handler_init_filecheck()
		{
			$settings = $this->get_settings();
			if( empty( $settings ) )
			{
				/**
				 * Keep alive to allow to clean up in case when deleting a widget and check_all_instances() have run at same time.
				 * Due to internal WP caching this might have lead to inconsistent data structure.
				 */
				if( $this->activate_cron  )
				{
					$this->restart_cron_job();
				}
				return;
			}

			/**
			 * Fallback on version update - we need to switch to new data structure
			 * Can be removed in very very future versions.
			 * 
			 * @since 4.3.1
			 */
			$instance = array_shift( $settings );
			if( ! isset( $instance['avia_key'] ) || empty( $instance['avia_key'] ) )
			{
				$instances = $this->get_settings();
				foreach( $instances as $key => &$instance ) 
				{
					$key = $this->create_avia_key( $instance, $this->id_base . "-{$key}" );
					$instance['avia_key'] = $key;
					$this->cache['instances'][ $key ] = array();
				}
				unset( $instance );
				$this->save_settings( $instances );

				$this->cache['last_updated'] = 0;
				$this->update_cache();

				$this->check_all_instances();
			}

			if( $this->activate_cron  )
			{
				$this->restart_cron_job();
				return;
			}

			/**
			 * Check if we need to run an update
			 */
			if( $this->cache['last_updated'] + $this->expire_time > time() )
			{
				return;
			}

			/**
			 * Only run update in backend
			 */
			if( is_admin() )
			{
				$this->check_all_instances();
			}
		}


		/**
		 * Is called, when an instance of a widget is deleted - Both from active sidebars or inactive widget area.
		 * 
		 * @since 4.3.1
		 * @param string $widget_id
		 * @param string $sidebar_id
		 * @param string $id_base
		 */		
		public function handler_delete_widget( $widget_id, $sidebar_id, $id_base )
		{
			$id = str_replace( $id_base . '-', '', $widget_id );

			$settings = $this->get_settings();
			if( empty( $settings ) || empty( $settings[ $id ] ) )
			{
				return;
			}

			$instance = $settings[ $id ];

			$instance_info = isset( $this->cache['instances'][ $instance['avia_key'] ] ) ? $this->cache['instances'][ $instance['avia_key'] ] : array();
			if( empty( $instance_info ) )
			{
				return;
			}

			$instance = $this->parse_args_instance( $instance );
			$instance_info = $this->parse_args_instance_cache( $instance_info );

			if( count( $settings ) <= 1 )
			{
				avia_backend_delete_folder( $this->upload_folders['instagram_dir'] );
				$this->cache['instances'] = array();
			}
			else
			{
				$folder = $this->upload_folders['instagram_dir'] . $instance_info['upload_folder'];
				avia_backend_delete_folder( $folder );
				unset( $this->cache['instances'][ $instance['avia_key'] ] );
			}

			$this->update_cache();
		}


		/**
		 * This is a fallback function to ensure that the cron job is running
		 * 
		 * @since 4.3.1
		 */
		protected function restart_cron_job()
		{
		   $timestamp = wp_next_scheduled( 'av_instagram_scheduled_filecheck' );
		   if( false === $timestamp )
		   {
			   $this->schedule_cron_job( $this->expire_time );
			   return;
		   }

		   /**
			* This is a fallback to prevent a blocking of updates 
			*/
		   if( $timestamp > ( time() + $this->expire_time * 2 ) )
		   {
			   $this->schedule_cron_job( $this->expire_time * 2 );
		   }
		}

		/**
		 * Removes an existing cron job and creates a new one
		 * 
		 * @since 4.3.1
		 * @param int $delay_seconds
		 * @return boolean
		 */
		protected function schedule_cron_job( $delay_seconds = 0 )
		{	
			$timestamp = wp_next_scheduled( 'av_instagram_scheduled_filecheck' );
			if( false !== $timestamp )
			{
				wp_unschedule_hook( 'av_instagram_scheduled_filecheck' );
			}

			$timestamp = time() + $delay_seconds;

			$scheduled = wp_schedule_single_event( $timestamp, 'av_instagram_scheduled_filecheck' );

			return false !== $scheduled;
		}


		/**
		 * Scan all instances of this widget and update cache data
		 * 
		 * @since 4.3.1
		 */
		protected function check_all_instances()
		{
			$settings = $this->get_settings();

			foreach ( $settings as $key => $instance ) 
			{
				$id_widget = $this->id_base . "-{$key}";

				if( false === is_active_widget( false, $id_widget, $this->id_base, false ) )
				{
					continue;
				}

				$this->update_single_instance( $instance, $id_widget );
			}

			$this->cache['last_updated'] = time();
			$this->update_cache();
		}


		/**
		 * Updates the cache for the given instance.
		 * As a fallback for older versions the instance is updated and returned.
		 * 
		 * @since 4.3.1
		 * @param array $instance
		 * @param string $id_widget
		 * @return array 
		 */
		protected function update_single_instance( array $instance, $id_widget )
		{
			set_time_limit( 0 );

			$instance = $this->parse_args_instance( $instance );

			/**
			 * Fallback for old versions - update to new datastructure
			 */
			if( empty( $instance['avia_key'] ) )
			{
				$key = $this->create_avia_key( $instance, $id_widget );
				$instance['avia_key'] = $key;
				$this->cache['instances'][ $key ] = array();
			}

			$instance_cache = isset( $this->cache['instances'][ $instance['avia_key'] ] ) ? $this->cache['instances'][ $instance['avia_key'] ] : array();
			$instance_cache = $this->parse_args_instance_cache( $instance_cache );

			/**
			 * Create upload directory if not exist. Upload directory will be deleted when widget instance is removed.
			 */
			if( ( 'server' == $instance['cache'] ) && empty( $instance_cache['upload_folder'] ) && ! empty( $instance['username'] ) )
			{
				$id = str_replace( $this->id_base . '-', '', $id_widget );
				$f = empty( $instance['title'] ) ? $instance['username'] : $instance['title'];
				$folder_name = substr( AviaHelper::save_string( $id . '-' . $f, '-' ), 0, 30 );
				$folder = $this->upload_folders['instagram_dir'] . $folder_name;

				$created = avia_backend_create_folder( $folder, false, 'unique' );
				if( $created )
				{
					$split = pathinfo( $folder );
					$instance_cache['upload_folder'] = $split['filename'];
					$instance_cache['path_error'] = '';
					$instance_cache['cached_list'] = array();
				}
				else
				{
					$instance_cache['path_error'] = sprintf( __( 'Unable to create cache folder "%s". Files will be loaded directly from instagram', 'avia_framework' ), $folder );
				}
			}

			$username = $instance['username'];
			$number = $instance['number'];

			if( ! empty( $username) )
			{
				$media_array = $this->scrape_instagram( $username );

				if ( ! is_wp_error( $media_array ) ) 
				{
					$instance_cache['instagram_error'] = '';
					$instance_cache['instagram_list'] = array_slice( $media_array, 0, $number );

					if( 'server' == $instance['cache'] )
					{
						$instance_cache = $this->cache_files_in_upload_directory( $media_array, $instance, $instance_cache );
					}
				}
				else 
				{
					/**
					 * We only store error message but keep existing files for fallback so we do not break widget
					 */
					$instance_cache['instagram_error'] = $media_array->get_error_message();
				}
			}
			else
			{
				$instance_cache['instagram_error'] = __( 'You need to specify an instgram username.', 'avia_framework' );
				$instance_cache['instagram_list'] = array();
				$instance_cache['cached_list'] = array();
			}

			$instance_cache['last_update'] = time();

			$this->cache['instances'][ $instance['avia_key'] ] = $instance_cache;
			$this->update_cache();

			return $instance;
		}


		/**
		 * Updates the local stored files in upload directory
		 * Already downloaded files are not updated.
		 * If an error occurs, we try to download more files as fallback to provide requested number of files
		 * in frontend. 
		 * 
		 * No longer needed files are removed from cache.
		 * 
		 * @since 4.3.1
		 * @param array $instagram_files
		 * @param array $instance
		 * @param array $instance_cache
		 * @return array
		 */
		protected function cache_files_in_upload_directory( array $instagram_files, array $instance, array $instance_cache )
		{
			set_time_limit( 0 );

			$cached_files = $instance_cache['cached_list'];

			$new_cached_files = array();
			$no_errors = 0;

			foreach( $instagram_files as $instagram_file ) 
			{
				$id = $instagram_file['id'];

				$found = false;
				foreach( $cached_files as $key_cache => $cached_file ) 
				{
					if( $id == $cached_file['id'] )
					{
						/**
						 * If an error occured in a previous file load we try to reload all files again
						 */
						if( ! empty( $cached_file['errors'] ) )
						{
							$this->remove_single_cached_files( $cached_file, $instance_cache );
							unset( $cached_files[ $key_cache ] );
							break;
						}

						/**
						 * As a fallback (or if other sizes were added later) we check if the cached files exist
						 */
						$path = trailingslashit( $this->upload_folders['instagram_dir'] . $instance_cache['upload_folder'] );
						foreach( $this->cached_file_sizes as $size ) 
						{
							if( empty( $cached_file[ $size ] ) || ! file_exists( $path . $cached_file[ $size ] ) )
							{
								$this->remove_single_cached_files( $cached_file, $instance_cache );
								unset( $cached_files[ $key_cache ] );
								break;
							}
						}

						if( ! isset( $cached_files[ $key_cache ] ) )
						{
							break;
						}

						$ncf = $cached_file;

						$ncf['description'] = $instagram_file['description'];
						$ncf['link'] = $instagram_file['link'];
						$ncf['time'] = $instagram_file['time'];
						$ncf['comments'] = $instagram_file['comments'];
						$ncf['likes'] = $instagram_file['likes'];
						$ncf['type'] = $instagram_file['type'];

						$new_cached_files[] = $ncf;

						unset( $cached_files[ $key_cache ] );
						$found = true;
						break;
					}
				}

				if( ! $found )
				{
					$new_cached_files[] = $this->download_from_instagram( $instagram_file, $instance, $instance_cache );
				}

				$last = $new_cached_files[ count( $new_cached_files ) - 1 ];

				/**
				 * Check if we could cache the file in requested size - we might have got a warning from chmod 
				 */
				if( empty( $last['errors'] ) || ! empty( $last[ $instance['size'] ] ) )
				{
					$no_errors++;
				}

				/**
				 * Also break if we get too many errors
				 */
				if( $no_errors >= $instance['number'] || count( $new_cached_files ) > ( $instance['number'] * 2 ) )
				{
					break;
				}
			}

			/**
			 * Now we add all remaining cached files to fill up requested number of files
			 */
			if( $no_errors < $instance['number'] )
			{
				foreach( $cached_files as $key_cache => $cached_file )
				{
					$new_cached_files[] = $cached_file;
					if( empty( $cached_file['errors'] ) )
					{
						$no_errors++;
					}

					unset( $cached_files[ $key_cache ] );

					if( $no_errors >= $instance['number'] )
					{
						break;
					}
				}
			}

			/**
			 * Now we delete no longer needed files
			 */
			foreach( $cached_files as $key_cache => $cached_file )
			{
				$this->remove_single_cached_files( $cached_file, $instance_cache );
				unset( $cached_files[ $key_cache ] );
			}

			/**
			 * Save results and count errors
			 */
			$err_cnt = 0;
			$count = 1;

			foreach( $new_cached_files as $new_file ) 
			{
				if( ! empty( $new_file['errors'] ) )
				{
					$err_cnt++;
				}
				$count++;

				if( $count > $instance['number'] )
				{
					break;
				}
			}

			$instance_cache['upload_errors'] = ( 0 == $err_cnt ) ? false : $err_cnt;
			$instance_cache['cached_list'] = $new_cached_files;

			return $instance_cache;
		}

		/**
		 * Downloads the files from instagram and stores them in local cache
		 * 
		 * @since 4.3.1
		 * @param array $instagram_file
		 * @param array $instance
		 * @param array $instance_cache
		 * @return array
		 */
		protected function download_from_instagram( array $instagram_file, array $instance, array $instance_cache )
		{
			$new_cached_file = $instagram_file;
			$new_cached_file['errors'] = array();
			$instagram_schema = 'https:';

			$cache_path = trailingslashit( $this->upload_folders['instagram_dir'] . $instance_cache['upload_folder'] );

			foreach( $this->cached_file_sizes as $size ) 
			{
				$file_array = array();
				
				//	instagram returns link to file with ?......
				$fn = explode( '?', basename( $instagram_file[ $size ] ) );
				$file_array['name'] = $fn[0];

				// Download file to temp location - include file if called from frontend.
				if( ! function_exists( 'download_url' ) )
				{
					$s = trailingslashit( ABSPATH ) . 'wp-admin/includes/file.php';
					require_once $s;
				}

				$file_array['tmp_name'] = download_url( $instagram_schema . $instagram_file[ $size ] );

				// If error storing temporarily, return the error.
				if( is_wp_error( $file_array['tmp_name'] ) ) 
				{
					$new_cached_file[ $size ] = '';
					$new_cached_file['errors'] = array_merge( $new_cached_file['errors'], $file_array['tmp_name']->get_error_messages() );
					continue;
				}

				$new_file_name = $size . '_' . $file_array['name'];
				$new_name = $cache_path . $new_file_name;

				$moved = avia_backend_rename_file( $file_array['tmp_name'], $new_name );
				if( is_wp_error( $moved ) )
				{
					$new_cached_file[ $size ] = '';
					$new_cached_file['errors'] = array_merge( $new_cached_file['errors'], $moved->get_error_messages() );
					continue;
				}

				/**
				 * Try to change accessability of file
				 */
				if( ! chmod( $new_name, 0777 ) )
				{
					$new_cached_file['errors'][] = sprintf( __( 'Could not change user rights of file %s to 777 - file might not be visible in frontend.', 'avia_framework' ), $new_name );
				}

				$new_cached_file[ $size ] = $new_file_name;
			}

			return $new_cached_file;
		}


		/**
		 * Removes all cashed files from $cached_file_info
		 * 
		 * @since 4.3.1
		 * @param array $cached_file_info
		 * @param array $instance_cache
		 * @return array
		 */
		protected function remove_single_cached_files( array $cached_file_info, array $instance_cache )
		{
			$cache_path = trailingslashit( $this->upload_folders['instagram_dir'] . $instance_cache['upload_folder'] );

			foreach( $this->cached_file_sizes as $size ) 
			{
				if( ! empty( $cached_file_info[ $size ] ) )
				{
					$file = $cache_path . $cached_file_info[ $size ];

					if( file_exists( $file ) )
					{
						unlink( $file );
					}
					$cached_file_info[ $size ] = '';
				}
			}

			return $cached_file_info;
		}
	}
}



/**
 * AVIA TABLE OF CONTENTS WIDGET
 *
 * Widget that displays a 'table of contents' genereated from the headlines of the page it is viewed on
 *
 * @package AviaFramework
 * @author tinabillinger
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */

if (!class_exists('avia_auto_toc'))
{
	class avia_auto_toc extends WP_Widget {

		static $script_loaded = 0;

		function __construct() { 
			//Constructor
			$widget_ops = array('classname' => 'avia_auto_toc', 'description' => __('Widget that displays a table of contents genereated from the headlines of the page it is viewed on', 'avia_framework') );
			parent::__construct( 'avia_auto_toc', THEMENAME.' Table of Contents', $widget_ops );
		}

		function widget($args, $instance) {
			extract($args, EXTR_SKIP);

			if ($instance['single_only'] && ! is_single()) return false;

			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
            $indent_class = $instance['indent'] ? ' avia-toc-indent' : '';
            $smoothscroll_class = $instance['smoothscroll'] ? ' avia-toc-smoothscroll' : '';

			echo $before_widget;

			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };

			$exclude = "";
			if ( $instance['exclude'] !== '' ){
			    $exclude = 'data-exclude="'.$instance['exclude'].'"';
			}
			
			$instance['style'] = "elegant";
			
			echo '<div class="avia-toc-container avia-toc-style-'.$instance['style'].$indent_class.$smoothscroll_class.'" data-level="'.$instance['level'].'" '.$exclude.'></div>';

			echo $after_widget;
        }

        function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = trim(strip_tags($new_instance['title']));
			$instance['exclude'] = strip_tags($new_instance['exclude']);
			$instance['style'] = strip_tags($new_instance['style']);
			$instance['level'] = implode(',',$new_instance['level']);
			$instance['single_only'] = isset( $new_instance['single_only'] ) ? 1 : 0;
			$instance['indent'] = isset( $new_instance['indent'] ) ? 1 : 0;
			$instance['smoothscroll'] = isset( $new_instance['smoothscroll'] ) ? 1 : 0;

			return $instance;
		}

		function form( $instance ) {

			$instance = wp_parse_args( (array) $instance, array(
			        'exclude' => '',
			        'level' => 'h1',
			        'title' => '',
			        'style' => 'simple',
			        ) );

 		    $title = sanitize_text_field( $instance['title'] );
			$single_only = isset( $instance['single_only'] ) ? (bool) $instance['single_only'] : true;
			$indent = isset( $instance['indent'] ) ? (bool) $instance['indent'] : true;
			$smoothscroll = isset( $instance['smoothscroll'] ) ? (bool) $instance['smoothscroll'] : true;

			$levels = array(
			        'h1' => 'H1 Headlines',
			        'h2' => 'H2 Headlines',
			        'h3' => 'H3 Headlines',
			        'h4' => 'H4 Headlines',
			        'h5' => 'H5 Headlines',
			        'h6' => 'H6 Headlines'
			);

			$styles = array(
			        'simple' => 'Simple',
			        'elegant' => 'Elegant',
			);

	        ?>
			<p>
			<label for="<?php echo $this->get_field_id('Title'); ?>"><?php _e('Title:', 'avia_framework'); ?>
			    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
			</p>

			<p>
			<label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e('Exclude headlines by class:', 'avia_framework'); ?>
			    <input class="widefat" id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo esc_attr($instance['exclude']); ?>" /></label>
			    <small>Provice a classname without a dot</small>
			</p>

			<p>
			<label for="<?php echo $this->get_field_id('level'); ?>"><?php _e('Select headlines to include:', 'avia_framework'); ?><br/>
			    <select class="widefat" id="<?php echo $this->get_field_id('level'); ?>" name="<?php echo $this->get_field_name('level'); ?>[]" multiple="multiple">
                    <?php
   			         $selected_levels = explode(',', $instance['level']);

   			         foreach ( $levels as $k => $l) {
                            $selected = '';
                            if (in_array($k,$selected_levels)){
                                $selected = ' selected="selected"';
                            }
                            ?>
                            <option<?php echo $selected;?> value="<?php echo $k; ?>"><?php echo $l; ?></option>
                            <?php
                        }
                    ?>
                </select>
            </label>
			</p>
			<!--
			<p>
			<label for="<?php echo $this->get_field_id('style'); ?>"><?php _e('Select a style', 'avia_framework'); ?><br/>
			    <select class="widefat" id="<?php echo $this->get_field_id('style'); ?>" name="<?php echo $this->get_field_name('style'); ?>">
                    <?php

   			         foreach ( $styles as $sk => $sv) {

                            $selected = '';
                            if ($sk == $instance['style']){
                                $selected = ' selected="selected"';
                            }
                            ?>
                            <option<?php echo $selected;?> value="<?php echo $sk; ?>"><?php echo $sv; ?></option>
                            <?php
                        }
                    ?>
                </select>
            </label>
			</p>
			-->


            <p>
                <input class="checkbox" id="<?php echo $this->get_field_id('single_only'); ?>" name="<?php echo $this->get_field_name('single_only'); ?>" type="checkbox" <?php checked( $single_only ); ?> />
                <label for="<?php echo $this->get_field_id('single_only'); ?>"><?php _e('Display on Single Blog Posts only', 'avia_framework'); ?></label>
            </br>
                <input class="checkbox" id="<?php echo $this->get_field_id('indent'); ?>" name="<?php echo $this->get_field_name('indent'); ?>" type="checkbox" <?php checked( $indent ); ?> />
                <label for="<?php echo $this->get_field_id('indent'); ?>"><?php _e('Hierarchy Indentation', 'avia_framework'); ?></label>
            </br>
                <input class="checkbox" id="<?php echo $this->get_field_id('smoothscroll'); ?>" name="<?php echo $this->get_field_name('smoothscroll'); ?>" type="checkbox" <?php checked( $indent ); ?> />
                <label for="<?php echo $this->get_field_id('smoothscroll'); ?>"><?php _e('Enable Smooth Scrolling', 'avia_framework'); ?></label>
            </p>

		    <?php

        }

    }
}
