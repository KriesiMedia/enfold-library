<?php

//Auszug aus Enfold.php von Borlabs Cookie
function modifyVideoOutput( $output, $atts, $content, $shortcodename, $meta, $video_html_raw )
{
    if( ! empty( $atts['src'] ) ) 
	{
		$style = '';
		$class = '';
		
		if( $atts['attachment'] )
		{
			$fallback = wp_get_attachment_image_src( $atts['attachment'], $atts['attachment_size']  );
			if( is_array( $fallback ) )
			{
				$fallback_img = $fallback[0];
				$style .= " background-image:url(\"{$fallback_img}\");";
				
				if( false !== strpos( $fallback_img, 'https://vimeo.com' ) )
				{
					$class .= 'av-video-vimeo';
				}
				else if( false !== strpos( $fallback_img, 'https://www.youtube.com' ) )
				{
					$class .= 'av-video-youtube';
				}
				else
				{
					$class .= 'av-video-custom';
				}
			}
		}

		if( ! empty( $atts['format'] ) && $atts['format'] == 'custom' ) 
		{
			$height = intval( $atts['height'] );
			$width = intval( $atts['width'] );
			$ratio = ( 100 / $width ) * $height;
			$style .= " padding-bottom:{$ratio}%;";
		}

		if( ! empty( $style ) )
		{
			$style = "style='{$style}'";
		}

		if( ! empty( $atts['conditional_play'] ) && $atts['conditional_play'] === 'lightbox') 
		{
			// Nothing for now - can not be supported
		} 
		// Hier verändern die das originale Elternelement !
		else 
		{
			$output = "<div class='avia-video avia-video-{$atts['format']} {$class}' {$style}>'{$video_html_raw}</div>";
		}
    }
	
    return $output;
}

//	we hook after Borlabs and extend their modifications again
add_filter( 'avf_sc_video_output', 'modifyVideoOutput', 110, 6 );



