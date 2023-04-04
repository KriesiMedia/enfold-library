<?php
/*
 * Snippet to remove or modify existing snippets or add a new custom widget
 *
 * @since 5.5
 */

function custom_avf_widget_loader_widget_classes( array $default_widgets )
{
	$namespace = '\\aviaFramework\widgets\\';

	//	create a folder widgets in enfold-child
	$path = trailingslashit( get_stylesheet_directory() ) . 'widgets/';

	//	remove portfoliobox widget (= a default widget)
	unset( $default_widgets['portfoliobox'] );

	// add your child theme modified portfoliobox widget
	$default_widgets['portfoliobox'] = array(
												'class'	=> $namespace . 'avia_portfoliobox',
												'file'	=> $path . 'class-avia-portfoliobox.php'
											);

	//	add a new custom widget, wrap in namespace aviaFramework\widgets
	$default_widgets['your-widget'] = array(
												'class'	=> $namespace . 'your_widget',
												'file'	=> $path . 'class-your-widget.php'
											);

	return $default_widgets;
}

add_filter( 'avf_widget_loader_widget_classes', 'custom_avf_widget_loader_widget_classes', 10, 1 );



/*
 * Snippet to replace Newsbox widget with your custom widget
 *
 * @since 5.5
 */

function custom_avf_widget_loader_widget_classes_newsbox( array $default_widgets )
{
	$namespace = '\\aviaFramework\widgets\\';

	//	create a folder widgets in enfold-child:     enfold-child/widgets
	$path = trailingslashit( get_stylesheet_directory() ) . 'widgets/';


	// Replace link to original file with your child theme modified newsbox widget
	$default_widgets['newsbox'] = array(
												'class'	=> $namespace . 'avia_newsbox',
												'file'	=> $path . 'class-avia-newsbox.php'
											);

	
	return $default_widgets;
}

add_filter( 'avf_widget_loader_widget_classes', 'custom_avf_widget_loader_widget_classes_newsbox', 10, 1 );

