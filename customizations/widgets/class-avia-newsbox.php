<?php
namespace aviaFramework\widgets;

use WP_Query;

/**
 * AVIA NEWSBOX CUSTOMIZED
 *
 * Widget that creates a list of latest news entries
 * Displays categories and portfolio categories
 *
 * @since 5.5
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }

if( ! class_exists( __NAMESPACE__ . '\avia_newsbox' ) )
{
	class avia_newsbox extends \aviaFramework\widgets\base\Avia_Widget
	{
		/**
		 *
		 * @var string
		 */
		protected $avia_term;

		/**
		 *
		 * @var string
		 */
		protected $avia_post_type;

		/**
		 *
		 * @var string
		 */
		protected $avia_new_query;

		/**
		 * @since 4.9						added parameters $id_base, ... $control_options
		 * @param string $id_base
		 * @param string $name
		 * @param array $widget_options
		 * @param array $control_options
		 */
		public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() )
		{
			if( empty( $id_base ) )
			{
				$id_base = 'newsbox';
			}

			if( empty( $name ) )
			{
				$name = THEMENAME . ' ' . __( 'Latest News (modified)', 'avia_framework' );
			}
			else
			{
				$name .= ' ' . __( '(modified)', 'avia_framework' );
			}

			if( empty( $widget_options ) )
			{
				$widget_options = array(
								'classname'				=> 'newsbox',
								'description'			=> __( 'A Sidebar widget to display latest post entries in your sidebar. Also displays post categories (and portfolio categories)', 'avia_framework' ),
								'show_instance_in_rest'	=> true,
								'customize_selective_refresh' => false
							);
			}

			parent::__construct( $id_base, $name, $widget_options, $control_options );

			$this->defaults = array(
								'title'		=> '',
								'count'		=> '',
								'excerpt'	=> '',
								'cat'		=> ''
							);

			$this->avia_term = '';
			$this->avia_post_type = '';
			$this->avia_new_query = '';
		}

		/**
		 * Output the widget in frontend
		 *
		 * @since 4.9
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance )
		{
			global $avia_config;

			$instance = $this->parse_args_instance( $instance );

			extract( $args, EXTR_SKIP );

			echo $before_widget;

			$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
			$count = empty( $instance['count'] ) ? '' : $instance['count'];
			$cat = empty( $instance['cat'] ) ? '' : $instance['cat'];
			$excerpt = empty( $instance['excerpt'] ) ? '' : $instance['excerpt'];
			$image_size = isset( $avia_config['widget_image_size'] ) ? $avia_config['widget_image_size'] : 'widget';

			/**
			 * @since 4.5.4
			 * @param string $image_size
			 * @param array $args
			 * @param array $instance
			 * @return string
			 */
			$image_size = apply_filters( 'avf_newsbox_image_size', $image_size, $args, $instance );

			if( ! empty( $title ) )
			{
				echo $before_title . $title . $after_title;
			}

			if( empty( $this->avia_term ) )
			{
				$additional_loop = new WP_Query( "cat={$cat}&posts_per_page={$count}" );
			}
			else
			{
				$catarray = explode( ',', $cat );

				if( empty( $catarray[0] ) )
				{
					$new_query = array(
									'posts_per_page'	=> $count,
									'post_type'			=> $this->avia_post_type
								);
				}
				else
				{
					if( $this->avia_new_query )
					{
						$new_query = $this->avia_new_query;
					}
					else
					{
						$new_query = array(
										'posts_per_page'	=> $count,
										'tax_query'			=> array(
																array(
																	'taxonomy'	=> $this->avia_term,
																	'field'		=> 'id',
																	'terms'		=> explode( ',', $cat ),
																	'operator'	=> 'IN'
																)
															)
														);
					}
				}

				$additional_loop = new WP_Query( $new_query );
			}

			if( $additional_loop->have_posts() )
			{
				echo '<ul class="news-wrap image_size_' . $image_size . '">';

				while( $additional_loop->have_posts() )
				{
					$additional_loop->the_post();

					$format = '';

					if( empty( $this->avia_post_type ) )
					{
						$format = $this->avia_post_type;
					}

					if( empty( $format ) )
					{
						$format = get_post_format();
					}

					if( empty( $format ) )
					{
						$format = 'standard';
					}

					$the_id = get_the_ID();
					$link = get_post_meta( $the_id , '_portfolio_custom_link', true ) != '' ? get_post_meta( $the_id ,'_portfolio_custom_link_url', true ) : get_permalink();

					echo '<li class="news-content post-format-'.$format.'">';

					//check for preview images:
					$image = '';

					if( ! current_theme_supports( 'force-post-thumbnails-in-widget' ) )
					{
						$slides = avia_post_meta( get_the_ID(), 'slideshow', true );

						if( $slides != '' && ! empty( $slides[0]['slideshow_image'] ) )
						{
							$image = avia_image_by_id( $slides[0]['slideshow_image'], $image_size, 'image' );
						}
					}

					if( current_theme_supports( 'post-thumbnails' ) && ! $image )
					{
						$image = get_the_post_thumbnail( $the_id, $image_size );
					}

					$time_format = apply_filters( 'avia_widget_time', get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), 'avia_newsbox' );

					echo '<a class="news-link" title="' . get_the_title() . '" href="' . $link . '">';

					$nothumb = ( ! $image ) ? 'no-news-thumb' : '';

					echo "<span class='news-thumb {$nothumb}'>";
					echo	$image;
					echo '</span>';

					if( empty( $avia_config['widget_image_size'] ) || 'display title and excerpt' != $excerpt )
					{
						echo '<strong class="news-headline">' . get_the_title();

						if( empty( $this->avia_term ) )
						{
							$cats = get_the_category();

							if( count( $cats ) > 0 )
							{
								$names = [];

								foreach ( $cats as $cat )
								{
									$names[] = $cat->cat_name;
								}

								echo '<br /><span class="news-time news-cats">' . __( 'in:', 'avia_framework' ) . ' ' . implode( ', ', $names ) . '</span>';
							}
						}
						else
						{
							$terms = get_the_terms( $the_id, $this->avia_term );

							if( is_array( $terms ) && count( $terms ) > 0 )
							{
								$names = [];

								foreach ( $terms as $term )
								{
									$names[] = $term->name;
								}

								echo '<br /><span class="news-time news-cats">' . __( 'in:', 'avia_framework' ) . ' ' . implode( ', ', $names ) . '</span>';
							}
						}

						if( $time_format )
						{
							echo '<span class="news-time">' . get_the_time( $time_format ) . '</span>';
						}

						echo '</strong>';
					}

					echo '</a>';

					if( 'display title and excerpt' == $excerpt )
					{
						echo '<div class="news-excerpt">';

						if( ! empty( $avia_config['widget_image_size'] ) )
						{
							echo '<a class="news-link-inner" title="' . get_the_title() . '" href="' . $link . '">';
							echo	'<strong class="news-headline">' . get_the_title() . '</strong>';
							echo '</a>';

							if( $time_format )
							{
								echo '<span class="news-time">' . get_the_time( $time_format ) . '</span>';
							}
						}

						the_excerpt();

						echo '</div>';
					}

					echo '</li>';
				}

				echo '</ul>';
				wp_reset_postdata();
			}

			echo $after_widget;
		}

		/**
		 * Update widget options
		 *
		 * @param array $new_instance
		 * @param array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance )
		{
			$instance = $this->parse_args_instance( $old_instance );

			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['count'] = strip_tags( $new_instance['count'] );
			$instance['excerpt'] = strip_tags( $new_instance['excerpt'] );

			if( ! empty( $new_instance['cat'] ) )
			{
				$instance['cat'] = is_array( $new_instance['cat'] ) ? implode( ',', $new_instance['cat'] ) : strip_tags( $new_instance['cat'] );
			}

			return $instance;
		}

		/**
		 * Output the form in backend
		 *
		 * @param array $instance
		 */
		public function form( $instance )
		{
			$instance = $this->parse_args_instance( $instance );

			$title = strip_tags( $instance['title'] );
			$count = strip_tags( $instance['count'] );
			$excerpt = strip_tags( $instance['excerpt'] );

			$elementCat = array(
						'name'		=> __( 'Which categories should be used for the portfolio?', 'avia_framework' ),
						'desc'		=> __( 'You can select multiple categories here', 'avia_framework' ),
						'id'		=> $this->get_field_name( 'cat' ) . '[]',
						'type'		=> 'select',
						'std'		=> strip_tags( $instance['cat'] ),
						'class'		=> '',
						'multiple'	=> 6,
						'subtype'	=> 'cat'
					);

			//check if a different taxonomy than the default is set
			if( ! empty( $this->avia_term ) )
			{
				$elementCat['taxonomy'] = $this->avia_term;
			}

			$html = new \avia_htmlhelper();
	?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'avia_framework' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'How many entries do you want to display: ', 'avia_framework' ); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'count ' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>">
					<?php
					$list = '';
					for ($i = 1; $i <= 20; $i++ )
					{
						$selected = '';
						if( $count == $i )
						{
							$selected = 'selected="selected"';
						}

						$list .= "<option {$selected} value='{$i}'>{$i}</option>";
					}
					$list .= '</select>';
					echo $list;
					?>
			</p>

			<p><label for="<?php echo $this->get_field_id( 'cat' ); ?>"><?php _e( 'Choose the categories you want to display (multiple selection possible):', 'avia_framework' ); ?>
			<?php echo $html->select( $elementCat ); ?>
			</label></p>

			<p>
				<label for="<?php echo $this->get_field_id( 'excerpt' ); ?>"><?php _e( 'Display title only or title &amp; excerpt', 'avia_framework' ); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'excerpt' ); ?>" name="<?php echo $this->get_field_name( 'excerpt' ); ?>">
					<?php
					$list = '';
					$answers = array(
								'show title only'			=>	__( 'show title only', 'avia_framework' ),
								'display title and excerpt'	=>	__( 'display title and excerpt', 'avia_framework' )
								);

					foreach ( $answers as $key => $answer )
					{
						$selected = '';
						if( $key == $excerpt )
						{
							$selected = 'selected="selected"';
						}

						$list .= "<option {$selected} value='{$key}'>{$answer}</option>";
					}
					$list .= '</select>';
					echo $list;
					?>
			</p>
<?php
		}
	}
}

