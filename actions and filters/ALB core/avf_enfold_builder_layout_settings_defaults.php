<?php
/**
 * Page Layout Settings Defaults
 *
 * Change the default value of the page's Layout settings. 
 * Layout > Sidebar Settings
 * Layout > Footer Settings
 * Layout > Title Bar Settings
 * Layout > Header visibility and transparency
 * Documentation: http://www.kriesi.at/documentation/enfold/adjust-default-values-of-the-pages-layout-settings/
 *
 **/


/** Add the following filter in the child theme's functions.php file. **/

add_action( 'after_setup_theme', 'ava_enfold_builder_layout_mod' );

function ava_enfold_builder_layout_mod()
{
	add_filter('avf_builder_elements', 'avf_enfold_builder_layout_settings_mod');
}


function avf_enfold_builder_layout_settings_mod($elements)
{
	$counter = 0;
    foreach($elements as $element)
    {
		// Layout > Sidebar Settings
		if($element['id'] == 'layout')  {
			/**
			 *
			 * Available Options
			 * No Sidebar  = fullsize
			 * Left Sidebar = sidebar_left
			 * Right Sidebar = sidebar_right
			 * MOD: Set the Layout > Sidebar Settings to "No Sidebar"
			 *
			**/
            $elements[$counter]['std'] = 'fullsize';
        }

		// Layout > Footer Settings
		if($element['id'] == 'footer')  {
			/**
			 *
			 * Available Options
			 * Display only the socket (no footer widgets  = nosocket
			 * Display only the footer widgets (no socket) = nofooterwidgets
			 * Don\'t display the socket & footer widgets = nofooterarea
			 * MOD: Set the Layout > Footer Settings to "Display only the footer widgets (no socket)"
			 *
			**/
            $elements[$counter]['std'] = 'nofooterwidgets';
        }

		// Layout > Title Bar Settings
        if($element['id'] == 'header_title_bar')  {
			/**
			 *
			 * Available Options
			 * Display title and breadcrumbs  = title_bar_breadcrumb
			 * Display only title = title_bar
			 * Display only breadcrumbs = breadcrumbs_only
			 * Hide both = hidden_title_bar
			 * MOD: Set the Layout > Title Bar Settings to "Hide both"
			 *
			**/
            $elements[$counter]['std'] = 'hidden_title_bar';
        }

		// Layout > Header visibility and transparency
		if($element['id'] == 'header_transparency')  {
			/**
			 *
			 * Available Options
			 * No transparency =
			 * Transparent Header = 'header_transparent'
			 * Transparent Header with border = 'header_transparent header_with_border'
			 * Transparent & Glassy Header = 'header_transparent header_glassy '
			 * Header is invisible and appears once the users scrolls down = 'header_transparent header_scrolldown '
			 * Hide Header on this page = 'header_transparent header_hidden '
			 * MOD: Set the Layout >  Header visibility and transparency settings to "Hide both"Header is invisible and appears once the users scrolls down"
			 *
			**/
            $elements[$counter]['std'] = 'header_transparent header_scrolldown ';
        }

        $counter++;
    }
	return $elements;
}

