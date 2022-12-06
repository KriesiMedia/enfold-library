<?php
/**
 * Example Snippet
 * ===============
 *
 * Szenario:
 * ---------
 *
 * Get responsive Cell Padding Settings of a gridrow and add them to a global json array
 * that can be accessed in frontend via jQuery
 *
 * Can be used for any ALB element.
 *
 * @added_by Günter
 * @since 5.3
 */

/**
 * Get the attributes from the element
 *
 * $args is passed by reference => all changes applied are returned
 * @param array $args
 *					0 => string $out
 *					1 => aviaShortcodeTemplate $this
 *					2 => array $atts
 *					3 => string $content
 *					4 => string $shortcodename
 *					5 => boolean $fake
 *					6 => array $meta
 *
 */
function custom_get_shortcode_atts_for_js( array &$args )
{
	global $grid_json;

	if( 'av_layout_row' != $args[4] )
	{
		return;
	}

	if( ! isset( $args[2]['id'] ) || $args[2]['id'] != 'my-custom-grid' )
	{
		return;
	}

	$grid_id = $args[2]['id'];

	if( ! is_array( $grid_json ) )
	{
		$grid_json = [];
	}

	$cells = ShortcodeHelper::shortcode2array( $args[3] );

	$cells_atts = [];

	foreach( $cells as $cell )
	{
		$cell_atts = [];

		$atts = $cell['attr'];
		$cell_class = $atts['custom_class'];		//	add a unique custom class to every cell

		$cell_atts['padding'] = AviaHelper::multi_value_result_lockable( $atts['padding'] )['fill_with_0_val'];
		$cell_atts['av-desktop-padding'] = AviaHelper::multi_value_result_lockable( $atts['av-desktop-padding'] )['fill_with_0_val'];
		$cell_atts['av-medium-padding'] = AviaHelper::multi_value_result_lockable( $atts['av-medium-padding'] )['fill_with_0_val'];
		$cell_atts['av-small-padding'] = AviaHelper::multi_value_result_lockable( $atts['av-small-padding'] )['fill_with_0_val'];
		$cell_atts['av-mini-padding'] = AviaHelper::multi_value_result_lockable( $atts['av-mini-padding'] )['fill_with_0_val'];

		$cells_atts[ $cell_class ] = $cell_atts;
	}

	$grid_json[ $grid_id ] = $cells_atts;
}

/**
 * Add to HTML and a js snippet to access data
 *
 * @global type $grid_json
 */
function custom_handle_shortcode_atts()
{
	global $grid_json;

	if( empty( $grid_json ) || ! is_array( $grid_json ) )
	{
		return;
	}

	$json = json_encode( $grid_json );

	$output = "
<script type='text/javascript' id='av-script-custom-shortcode-atts' data-custom-shortcode-atts='{$json}'>

	(function($)
	{
		var container = $('#av-script-custom-shortcode-atts'),
			data = container.data('custom-shortcode-atts');

		console.log( 'data-object:', data );

		$.each( data, function( gridrow_id, gridrow_cells )
		{
			console.log( 'gridrow_id: ' + gridrow_id, gridrow_cells );

			$.each( gridrow_cells, function( cell_id, cell_atts )
			{
				console.log( 'cell_id: ' + cell_id, cell_atts );

				//	do something with cell_atts ......
			});
		});
	}(jQuery));

</script>
";

	echo $output;
}


add_filter( 'avf_in_shortcode_handler_prepare_content', 'custom_get_shortcode_atts_for_js', 10, 1 );
add_action( 'wp_footer', 'custom_handle_shortcode_atts' );


