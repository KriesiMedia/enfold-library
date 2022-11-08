<?php
/**
 * The following snippet show how to add dynamic data to our ALB chart element.
 * ============================================================================
 *
 * @link https://kriesi.at/documentation/enfold/chart-element/
 * @since 5.3
 */

/**
 * Filter dataset datapoints and replace with dynamic datapoints.
 * Is called in a loop for every dataset defined in the ALB element.
 *
 * @since 5.3
 * @param array $data			data for dataset provided by ALB Element
 * @param int $index			0 based index of dataset
 * @param array $attr			shortcode attributes array
 * @return array
 */
function my_chart_dataset_data_points( $data, $index, $attr )
{
	//	check if this is a chart to modify (we check only for id="my-chart-1", but any extended logic can be used)
	$id = $attr['id'];

	if( $id != 'my-chart-1' )
	{
		//	return unmodified data
		return $data;
	}

	/**
	 * your_provider_dataset_data_points is a function that connects to your data provider for dataset $index,
	 * places the datapoints in an array and returns it.
	 */
	$data_array = your_provider_dataset_data_points( $index );

	return $data_array;
}

add_filter( 'avf_chart_dataset_data', 'my_chart_dataset_data_points', 10, 3 );


/**
 * Filter dataset label and replace with dynamic label.
 * Is called in a loop for every dataset defined in the ALB element.
 *
 * @since 5.3
 * @param string $dataset_label
 * @param int $index			0 based index of dataset
 * @param array $attr			shortcode attributes array
 * @return array
 */
function my_chart_dataset_label( $dataset_label, $index, $attr )
{
	//	check if this is a chart to modify (we check only for id="my-chart-1", but any extended logic can be used)
	$id = $attr['id'];

	if( $id != 'my-chart-1' )
	{
		//	return unmodified data
		return $dataset_label;
	}

	/**
	 * your_provider_dataset_data_label is a function that connects to your data provider for dataset $index,
	 * gets the label for the dataset and returns it.
	 */
	$label = your_provider_dataset_data_label( $index );

	return $label;
}

add_filter( 'avf_chart_dataset_label', 'my_chart_dataset_label', 10, 3 );


/**
 * Filter the x-axis labels and replace with dynamic labels.
 *
 * @since 5.3
 * @param array $chart_labels
 * @param array $attr			shortcode attributes array
 * @return array
 */
function my_x_axis_chart_labels( $chart_labels, $attr )
{
	//	check if this is a chart to modify (we check only for id="my-chart-1", but any extended logic can be used)
	$id = $attr['id'];

	if( $id != 'my-chart-1' )
	{
		//	return unmodified data
		return $chart_labels;
	}

	/**
	 * your_provider_chart_x_axis_labels is a function that connects to your data provider,
	 * gets the x-axis labels and returns them in an array.
	 *
	 * e.g.:   array( 'Point1', 'Point2', 'Point3' )
	 */
	$labels = your_provider_chart_x_axis_labels( $attr );

	return $labels;
}

add_filter( 'avf_chart_labels', 'my_x_axis_chart_labels', 10, 2 );

/**
 * Filter the complete config array for the chart. See https://www.chartjs.org/docs/latest/. Make sure not to break the structure.
 * Use \stdClass for js {} and array for js [].
 *
 * @since 5.3
 * @param \stdClass $config
 * @param array $attr
 * @param array $meta
 * @return \stdClass
 */
function my_chartjs_config_object( $config, $attr, $meta )
{
	//	check if this is a chart to modify (we check only for id="my-chart-1", but any extended logic can be used)
	$id = $attr['id'];

	if( $id != 'my-chart-1' )
	{
		//	return unmodified data
		return $config;
	}

	//	add your custom settings in your function my_chart_modify_config
	$new_config = my_chart_modify_config( $config, $attr, $meta );

	return $new_config;
}

add_filter( 'avf_chartjs_config_object', 'my_chartjs_config_object', 10, 3 );

